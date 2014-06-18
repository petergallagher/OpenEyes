<?php
/**
 * (C) OpenEyes Foundation, 2014
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (C) 2014, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

namespace services;

class ModelConverter
{
	protected $map;

	public function __construct($map)
	{
		if ($map instanceof ModelMap) {
			$this->map = $map;
		} else {
			$this->map = new ModelMap($map);
		}
	}

	public function modelToResource($object, &$resource)
	{
		return $this->modelToResourceParse($object, get_class($object), $resource);
	}

	public function modelToResourceParse($object, $object_class_name, &$resource)
	{
		foreach ($this->map->getFieldsForClass($object_class_name) as $res_attribute => $def) {
			if (is_array($def)) {
				$class = 'services\\'.$def[0];
				$parser = new $class($this);
				$resource->$res_attribute = $parser->modelToResourceParse($object, $def[1], $def[2], @$def[3]);
			} else {
				$resource->$res_attribute = $this->expandObjectAttribute($object, $def);
			}
		}

		return $resource;
	}

	public function expandObjectAttribute($object, $object_field)
	{
		if (!is_array($object_field)) {
			$object_field = explode('.',$object_field);
		}

		$field = array_shift($object_field);

		if (count($object_field) >0) {
			if (!$object->$field) {
				return false;
			}

			return $this->expandObjectAttribute($object->$field, $object_field);
		}

		return $object->$field;
	}

	public function resourceToModel($resource, $model, $save=true, $extra_fields=false)
	{
		if (is_array($extra_fields)) {
			foreach ($extra_fields as $key => $value) {
				$model->$key = $value;
			}
		}

		$model_class_name = get_class($model);
		$model_relations = $model->relations();

		if ($class_related_objects = $this->map->getRelatedObjectsForClass($model_class_name)) {
			foreach ($class_related_objects as $relation_name => $def) {
				$class_name = '\\'.$def[1];

				if (is_array($def[0])) {
					$allnull = true;

					foreach ($def[2] as $attribute) {
						if ($resource->$attribute !== null) {
							$allnull = false;
						}
					}

					$target = $allnull ? $def[0][1] : $def[0][0];

					if ($pos = strpos($target,'.')) {
						$target = substr($target,0,$pos);

						if (!$model->$target->$relation_name) {
							$model->$target->$relation_name = new $class_name;
						}
					} else {
						if (!$model->$relation_name) {
							$model->$relation_name = new $class_name;
						}
					}
				} else {
					if (!$model->$relation_name) {
						$model->$relation_name = new $class_name;
					}

					$model->$relation_name = $this->applyRulesForNewRelatedObject($model_class_name, $model, $relation_name, $resource);
				}
			}
		}

		$related_objects = array();
		$reference_object_attributes = array();
		$conditional_values_set = array();

		foreach ($this->map->getFieldsForClass($model_class_name) as $res_attribute => $def) {
			if (is_array($def)) {
				switch ($def[0]) {
					case DeclarativeModelService::TYPE_RESOURCE:
						$_model_class_name = '\\'.$def[2];

						$new_model = new $_model_class_name;

						$model->{$def[1]} = $this->resourceToModel($resource->$res_attribute, $new_model, $save);
						if (isset($model_relations[$def[1]]) && $model_relations[$def[1]][0] == 'CBelongsToRelation') {
							$model->{$model_relations[$def[1]][2]} = $model->{$def[1]}->id;
						}
						break;
					case DeclarativeModelService::TYPE_LIST:
						if (($pos = strpos($def[1],'.')) !== FALSE) {
							$related_object_name = substr($def[1],0,$pos);
							$related_object_attribute = substr($def[1],$pos+1,strlen($def[1]));
						} else {
							$related_object_name = $def[1];
							$related_object_attribute = null;
						}

						foreach ($resource->$res_attribute as $item) {
							$related_objects[$related_object_name][$related_object_attribute][] = $this->resourceToModel($item, new $def[3], false);
						}
						break;
					case DeclarativeModelService::TYPE_REF:
						if ($resource->$res_attribute) {
							if (method_exists($resource->$res_attribute,'getId')) {
								$id_value = $resource->$res_attribute->getId();
							} else {
								$id_value = $resource->$res_attribute->id;
							}
						} else {
							$id_value = null;
						}

						if ($pos = strpos($def[1],'.')) {
							$related_object_name = substr($def[1],0,$pos);
							$related_object_attribute = substr($def[1],$pos+1,strlen($def[1]));

							if ($model->$related_object_name) {
								$model->$related_object_name->$related_object_attribute = $id_value;
							}
						} else {
							$model->{$def[1]} = $id_value;
						}
						break;
					case DeclarativeModelService::TYPE_SIMPLEOBJECT:
						if (is_object($resource->$res_attribute) && method_exists($resource->$res_attribute,'toModelValue')) {
							$model->{$def[1]} = $resource->$res_attribute->toModelValue();
						} else {
							$data_class = 'services\\'.$def[2];
							$model->{$def[1]} = is_null($resource->$res_attribute) ? null : $data_class::fromObject($resource->$res_attribute)->toModelValue();
						}
						break;
					case DeclarativeModelService::TYPE_DATAOBJECT:
					case DeclarativeModelService::TYPE_DATAOBJECT_EXCLUSIVE:
						if ($pos = strpos($def[1],'.')) {
							$related_object_name = substr($def[1],0,$pos);
							$related_object_attribute = substr($def[1],$pos+1,strlen($def[1]));

							if (is_object($resource->$res_attribute)) {
								$related_objects[$related_object_name][$related_object_attribute] = $this->resourceToModel($resource->$res_attribute, new $def[3], false);
							} else {
								$related_objects[$related_object_name][$related_object_attribute] = null;
							}
						} else {
							throw new \Exception("Unhandled");
						}
						break;
					case DeclarativeModelService::TYPE_CONDITION:
						if ($resource->$res_attribute) {
							if (!in_array($def[1], $conditional_values_set)) {
								$model->{$def[1]} = $def[3];
								$conditional_values_set[] = $def[1];
							} else {
								throw new \Exception("Unable to differentiate condition as more than one attribute is true.");
							}
						}
						break;
					case DeclarativeModelService::TYPE_REF_LIST:
						$model_assignment_relation = $def[1];
						$model_assignment_field = $def[2];

						$assignment_model = $model_relations[$model_assignment_relation][1];
						$assignment_field = $model_relations[$model_assignment_relation][2];

						$assignments = array();

						foreach ($resource->$res_attribute as $ref) {
							$assignment = new $assignment_model;
							$assignment->$assignment_field = $resource->id;
							$assignment->$model_assignment_field = $ref->getId();

							$assignments[] = $assignment;
						}

						$model->$model_assignment_relation = $assignments;
						break;
					case DeclarativeModelService::TYPE_OR:
						$rule = $this->map->getRuleForOrClause($model_class_name, $res_attribute);

						switch ($rule[0]) {
							case DeclarativeModelService::RULE_TYPE_ALLNULL:
								$allnull = true;

								foreach ($rule[1] as $attribute) {
									if ($resource->$attribute !== null) {
										$allnull = false;
									}
								}

								$target = $allnull ? $rule['then'] : $rule['else'];

								if ($pos = strpos($target,'.')) {
									$related_object_name = substr($target,0,$pos);
									$related_object_attribute = substr($target,$pos+1,strlen($target)+1);

									$_related_object = $model->$related_object_name->$related_object_attribute;
									$_related_object->{$def[1]} = $resource->$res_attribute;
									$model->$related_object_name->$related_object_attribute = $_related_object;
								} else {
									$model->$target->{$def[1]} = $resource->$res_attribute;
								}
								break;
							default:
								throw new \Exception("Unknown rule type: {$rule[0]}");
						}
						break;
					default:
						throw new \Exception("Unknown declarative type: {$def[0]}");
				}
			} else {
				if (($pos = strpos($def,'.')) !== FALSE) {
					$relation_name = substr($def,0,$pos);
					$related_object_attribute = substr($def,$pos+1,strlen($def));

					if (isset($class_related_objects[$relation_name])) {
						if (!$related_object = $model->$relation_name) {
							throw new \Exception("Model has nothing for relation: $relation_name");
						}

						$related_object->$related_object_attribute = $resource->$res_attribute;
						$model->$relation_name = $related_object;
					} else {
						$reference_object_attributes[$relation_name][$related_object_attribute] = $resource->$res_attribute;

						$def = $this->map->getReferenceObjectForClass($model_class_name, $relation_name);

						if (array_keys($reference_object_attributes[$relation_name]) == $def[2]) {
							// All required properties for matching the reference item have been set, so now we can associate it with the model
							$criteria = new \CDbCriteria;

							foreach ($reference_object_attributes[$relation_name] as $key => $value) {
								$criteria->compare($key, $value);
							}

							$related_object_class = '\\'.$def[1];

							if (!$related_object = $related_object_class::model()->find($criteria)) {
								$related_object = new $related_object_class;

								foreach ($reference_object_attributes[$relation_name] as $key => $value) {
									$related_object->$key = $value;
								}

								$save && $this->saveModel($related_object);
							}

							$model->{$def[0]} = $related_object->primaryKey;
							$model->$relation_name = $related_object;
						}
					}
				} else {
					$model->$def = $resource->$res_attribute;
				}
			}
		}

		if ($class_related_objects) {
			foreach ($class_related_objects as $relation_name => $def) {
				if (is_array($def[0])) {
					$allnull = true;

					foreach ($def[2] as $attribute) {
						if ($resource->$attribute !== null) {
							$allnull = false;
						}
					}

					$target = $allnull ? $def[0][1] : $def[0][0];

					if ($pos = strpos($target,'.')) {
						$_target = substr($target,0,$pos);
						$_attribute = substr($target,$pos+1,strlen($target));

						$save && $this->saveModel($model->$_target->$relation_name);

						if (!$model->$_target->$_attribute) {
							$model->$_target->$_attribute = $model->$_target->$relation_name->primaryKey;
						}
					} else {
						$save && $this->saveModel($model->$relation_name);

						if (!$model->$target) {
							$model->$target = $model->$relation_name->primaryKey;
						}
					}
				} else {
					if ($model->$relation_name) {
						$save && $this->saveModel($model->$relation_name);

						if (!$model->{$def[0]}) {
							$model->{$def[0]} = $model->$relation_name->primaryKey;
						}
					}
				}
			}
		}

		foreach ($this->map->getFieldsForClass($model_class_name) as $res_attribute => $def) {
			if (is_array($def)) {
				switch ($def[0]) {
					case DeclarativeModelService::TYPE_LIST:
						if ($pos = strpos($def[1],'.')) {
							$related_object_name = substr($def[1],0,$pos);
							$related_object_attribute = substr($def[1],$pos+1,strlen($def[1]));
						} else {
							$related_object_name = $def[1];
							$related_object_attribute = null;
						}

						if (isset($def[4])) {
							// Set extra fields on list items (currently used to set Address.contact_id)
							foreach ($related_objects[$related_object_name][$related_object_attribute] as $i => $item) {
								if (is_array($def[4])) {
									foreach ($def[4] as $_key => $_value) {
										$related_objects[$related_object_name][$related_object_attribute][$i]->$_key = $model->$_value;
									}
								} else {
									$related_objects[$related_object_name][$related_object_attribute][$i]->{$def[4]} = $model->{$def[4]};
								}
							}
						}

						if ($related_object_attribute) {
							$model->$related_object_name->$related_object_attribute = $this->filterListItems($model->$related_object_name, $related_object_attribute, $related_objects[$related_object_name][$related_object_attribute], $save);
						} else {
							$model->$related_object_name = $this->filterListItems($model->$related_object_name, $related_object_attribute, $related_objects[$related_object_name][$related_object_attribute], $save);
						}
						break;
					case DeclarativeModelService::TYPE_DATAOBJECT:
					case DeclarativeModelService::TYPE_DATAOBJECT_EXCLUSIVE:
						if ($pos = strpos($def[1],'.')) {
							$related_object_name = substr($def[1],0,$pos);
							$related_object_attribute = substr($def[1],$pos+1,strlen($def[1]));

							if ($related_objects[$related_object_name][$related_object_attribute] && isset($def[4])) {
								$related_objects[$related_object_name][$related_object_attribute]->{$def[4]} = $model->{$def[4]};
							}
						} else {
							throw new \Exception("Unhandled");
						}

						if ($save && $def[0] == DeclarativeModelService::TYPE_DATAOBJECT_EXCLUSIVE) {
							if ($old_object = $model->$related_object_name->$related_object_attribute) {
								$this->deleteModel($old_object);
							}
						}

						if ($model->$related_object_name && $related_objects[$related_object_name][$related_object_attribute]) {
							$model->$related_object_name->$related_object_attribute = $related_objects[$related_object_name][$related_object_attribute];
							$save && $this->saveModel($model->$related_object_name->$related_object_attribute);
						}

						break;
				}
			}
		}

		$save && $this->saveModel($model);

		return $model;
	}

	protected function filterListItems($object, $relation, $items, $save)
	{
		$items_to_keep = array();
		$matched_ids = array();

		$data = $relation ? $object->$relation : $object;

		foreach ($items as $item) {
			$found = false;

			if ($data) {
				foreach ($data as $current_item) {
					$class_name = 'services\\'.get_class($current_item);
					$current_item_res = $this->modelToResource($current_item, new $class_name);
					$new_item_res = $this->modelToResource($item, new $class_name);

					if ($current_item_res->isEqual($new_item_res)) {
						$found = true;
						$items_to_keep[] = $current_item;
						$matched_ids[] = $current_item->id;
					}
				}
			}

			if (!$found) {
				$items_to_keep[] = $item;

				$save && $this->saveModel($item);
			}
		}

		if ($save) {
			if ($object->$relation) {
				foreach ($object->$relation as $current_item) {
					if (!in_array($current_item->id,$matched_ids)) {
						$this->deleteModel($current_item);
					}
				}
			}
		}

		return $items_to_keep;
	}

	/*
	 * Save model object and throw a service layer exception on failure
	 *
	 * @param BaseActiveRecord $model
	 */
	protected function saveModel(\BaseActiveRecord $model)
	{
		if (!$model->save()) {
			throw new ValidationFailure("Validation failure on " . get_class($model).": ".print_r($model->errors,true), $model->errors);
		}
	}

	/*
	 * Delete model and throw an exception on failure
	 *
	 * @param BaseActiveRecord $model
	 */
	protected function deleteModel(\BaseActiveRecord $model)
	{
		if (!$model->delete()) {
			throw new \Exception("Unable to delete: " . get_class($model), $model->errors);
		}
	}

	protected function applyRulesForNewRelatedObject($class_name, $model, $relation_name, $resource)
	{
		if ($rules = $this->map->getRulesForRelatedObject($class_name, $relation_name)) {
			foreach ($rules as $rule) {
				switch ($rule[0]) {
					case DeclarativeModelService::RULE_TYPE_NULLIFNULL:
						$allnull = true;

						foreach ($rule[1] as $attribute) {
							if ($resource->$attribute !== null) {
								$allnull = false;
							}
						}

						if ($allnull) return null;
						break;
					default:
						throw new \Exception("Unknown related object rule type: {$rule[0]}");
				}
			}
		}

		return $model->$relation_name;
	}
}
