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
		$this->map = $map;
	}

	public function modelToResource($object, &$resource)
	{
		return $this->modelToResourceParse($object, get_class($object), $resource);
	}

	protected function modelToResourceParse($object, $object_class_name, &$resource)
	{
		if (!isset($this->map[$object_class_name])) {
			throw new \Exception("Unknown object type: $object_class_name");
		}

		foreach ($this->map[$object_class_name]['fields'] as $res_attribute => $def) {
			if (is_array($def)) {
				switch ($def[0]) {
					case DeclarativeModelService::TYPE_RESOURCE:
						$resource_class = 'services\\'.$def[2];
						$resource->$res_attribute = $this->modelToResource($object->{$def[1]}, new $resource_class(array('id' => $object->{$def[1]}->id, 'last_modified' => strtotime($object->{$def[1]}->last_modified_date))));
						break;
					case DeclarativeModelService::TYPE_LIST:
						$data_list = $this->expandObjectAttribute($object, $def[1]);
						$data_class = 'services\\'.$def[2];

						$data_items = array();

						foreach ($data_list as $data_item) {
							$data_items[] = $this->modelToResource($data_item, new $data_class);
						}

						$resource->$res_attribute = $data_items;
						break;
					case DeclarativeModelService::TYPE_REF:
						if ($object->{$def[1]}) {
							$data_class = $def[2];
							$resource->$res_attribute = \Yii::app()->service->$data_class($object->{$def[1]});
						} else {
							$resource->$res_attribute = null;
						}
						break;
					case DeclarativeModelService::TYPE_DATAOBJECT:
					case DeclarativeModelService::TYPE_SIMPLEOBJECT:
						$data = $this->expandObjectAttribute($object, $def[1]);
						$data_class = 'services\\'.$def[2];

						if (is_object($data)) {
							$resource->$res_attribute = $this->modelToResource($data, new $data_class);
						} else {
							$resource->$res_attribute = is_null($data) ? null : new $data_class($data);
						}
						break;
					case DeclarativeModelService::TYPE_CONDITION:
						switch ($def[2]) {
							case 'equals':
								$resource->$res_attribute = $object->{$def[1]} == $def[3];
								break;
							default:
								throw new Exception("Unknown condition type: {$def[2]}");
						}
						break;
					case DeclarativeModelService::TYPE_REF_LIST:
						$model_assignment_relation = $def[1];
						$model_assignment_field = $def[2];
						$ref_class = $def[3];

						$refs = array();

						foreach ($object->$model_assignment_relation as $ref_assignment_model) {
							$refs[] = \Yii::app()->service->$ref_class($ref_assignment_model->$model_assignment_field);
						}

						$resource->$res_attribute = $refs;
						break;
					default:
						throw new \Exception("Unknown declarative type: {$def[0]}");
				}
			} else {
				$resource->$res_attribute = $this->expandObjectAttribute($object, $def);
			}
		}

		return $resource;
	}

	protected function expandObjectAttribute($object, $ar_attribute)
	{
		if ($dot = strpos($ar_attribute,'.')) {
			$relation = substr($ar_attribute,0,$dot);
			$attribute = substr($ar_attribute,$dot+1,strlen($ar_attribute));

			return $object->$relation ? $object->$relation->$attribute : null;
		}

		return $object->$ar_attribute;
	}

	public function resourceToModel($resource, $model_class_name, $save=true, $extra_fields=false)
	{
		$_model_class_name = '\\'.$model_class_name;

		if (get_class($resource) == 'stdClass') {
			if (!isset($resource->id) || (!$model = $_model_class_name::model()->findByPk($resource->id))) {
				$model = new $_model_class_name;
			}
		} else {
			if (!method_exists($resource,'getId') || (!$model = $_model_class_name::model()->findByPk($resource->getId()))) {
				$model = new $_model_class_name;
			}
		}

		if (is_array($extra_fields)) {
			foreach ($extra_fields as $key => $value) {
				$model->$key = $value;
			}
		}

		$model_relations = $model->relations();

		if (isset($this->map[$model_class_name]['related_objects'])) {
			foreach ($this->map[$model_class_name]['related_objects'] as $relation_name => $def) {
				if (!$model->$relation_name) {
					$class_name = '\\'.$def[1];
					$model->$relation_name = new $class_name;
				}
			}
		}

		$related_objects = array();
		$reference_object_attributes = array();

		foreach ($this->map[$model_class_name]['fields'] as $res_attribute => $def) {
			if (is_array($def)) {
				switch ($def[0]) {
					case DeclarativeModelService::TYPE_RESOURCE:
						$model->{$def[1]} = $this->resourceToModel($resource->$res_attribute, $def[2], $save);
						if (isset($model_relations[$def[1]]) && $model_relations[$def[1]][0] == 'CBelongsToRelation') {
							$model->{$model_relations[$def[1]][2]} = $model->{$def[1]}->id;
						}
						break;
					case DeclarativeModelService::TYPE_LIST:
						if (($pos = strpos($def[1],'.')) !== FALSE) {
							$items = array();

							$related_object_name = substr($def[1],0,$pos);
							$related_object_attribute = substr($def[1],$pos+1,strlen($def[1]));

							$extra_fields = array();

							foreach ($resource->$res_attribute as $item) {
								$related_objects[$related_object_name][$related_object_attribute][] = $this->resourceToModel($item, $def[3], false);
							}
						} else {
							throw new \Exception("Unhandled");
						}
						break;
					case DeclarativeModelService::TYPE_REF:
						if ($resource->$res_attribute) {
							if (method_exists($resource->$res_attribute,'getId')) {
								$model->{$def[1]} = $resource->$res_attribute->getId();
							} else {
								$model->{$def[1]} = $resource->$res_attribute->id;
							}
						} else {
							$model->{$def[1]} = null;
						}
						break;
					case DeclarativeModelService::TYPE_SIMPLEOBJECT:
						if (method_exists($resource->$res_attribute,'toModelValue')) {
							$model->{$def[1]} = $resource->$res_attribute->toModelValue();
						} else {
							$data_class = 'services\\'.$def[2];
							$model->{$def[1]} = is_null($resource->$res_attribute) ? null : $data_class::fromObject($resource->$res_attribute)->toModelValue();
						}
						break;
					case DeclarativeModelService::TYPE_DATAOBJECT:
						if ($pos = strpos($def[1],'.')) {
							$related_object_name = substr($def[1],0,$pos);
							$related_object_attribute = substr($def[1],$pos+1,strlen($def[1]));

							$related_objects[$related_object_name][$related_object_attribute] = $this->resourceToModel($resource->$res_attribute, $def[3], false);
						} else {
							throw new \Exception("Unhandled");
						}
						break;
					case DeclarativeModelService::TYPE_CONDITION:
						if ($resource->$res_attribute) {
							$model->{$def[1]} = $def[3];
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
					default:
						throw new \Exception("Unknown declarative type: {$def[0]}");
				}
			} else {
				if (($pos = strpos($def,'.')) !== FALSE) {
					$relation_name = substr($def,0,$pos);
					$related_object_attribute = substr($def,$pos+1,strlen($def));

					if (isset($this->map[$model_class_name]['related_objects'][$relation_name])) {
						if (!$related_object = $model->$relation_name) {
							throw new \Exception("Model has nothing for relation: $relation_name");
						}

						$related_object->$related_object_attribute = $resource->$res_attribute;
						$model->$relation_name = $related_object;
					} else {
						$reference_object_attributes[$relation_name][$related_object_attribute] = $resource->$res_attribute;

						$def = $this->map[$model_class_name]['reference_objects'][$relation_name];

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

		if (isset($this->map[$model_class_name]['related_objects'])) {
			foreach ($this->map[$model_class_name]['related_objects'] as $relation_name => $def) {
				$save && $this->saveModel($model->$relation_name);

				if (!$model->{$def[0]}) {
					$model->{$def[0]} = $model->$relation_name->primaryKey;
				}
			}
		}

		foreach ($this->map[$model_class_name]['fields'] as $res_attribute => $def) {
			if (is_array($def)) {
				switch ($def[0]) {
					case DeclarativeModelService::TYPE_LIST:
						if ($pos = strpos($def[1],'.')) {
							$related_object_name = substr($def[1],0,$pos);
							$related_object_attribute = substr($def[1],$pos+1,strlen($def[1]));

							if (isset($def[4])) {
								// Set extra fields on list items (currently used to set Address.contact_id)
								foreach ($related_objects[$related_object_name][$related_object_attribute] as $i => $item) {
									$related_objects[$related_object_name][$related_object_attribute][$i]->{$def[4]} = $model->{$def[4]};
								}
							}
						} else {
							throw new \Exception("Unhandled");
						}

						$model->$related_object_name->$related_object_attribute = $this->filterListItems($model->$related_object_name, $related_object_attribute, $related_objects[$related_object_name][$related_object_attribute], $save);
						break;
					case DeclarativeModelService::TYPE_DATAOBJECT:
						if ($pos = strpos($def[1],'.')) {
							$related_object_name = substr($def[1],0,$pos);
							$related_object_attribute = substr($def[1],$pos+1,strlen($def[1]));

							if (isset($def[4])) {
								$related_objects[$related_object_name][$related_object_attribute]->{$def[4]} = $model->{$def[4]};
							}
						} else {
							throw new \Exception("Unhandled");
						}

						$model->$related_object_name->$related_object_attribute = $related_objects[$related_object_name][$related_object_attribute];

						$save && $this->saveModel($model->$related_object_name->$related_object_attribute);
						break;
				}
			}
		}

		$save && $this->saveModel($model);

		return $model;
	}

	protected function filterListItems($object, $relation, $items, $save)
	{
		$mc = new ModelConverter($this->map);

		$items_to_keep = array();
		$matched_ids = array();

		foreach ($items as $item) {
			$found = false;

			if ($object->$relation) {
				foreach ($object->$relation as $current_item) {
					$class_name = 'services\\'.get_class($current_item);
					$current_item_res = $mc->modelToResource($current_item, new $class_name);
					$new_item_res = $mc->modelToResource($item, new $class_name);

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
}
