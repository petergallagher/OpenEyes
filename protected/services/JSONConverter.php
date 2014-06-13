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

class JSONConverter
{
	protected $map;

	public function __construct($map)
	{
		$this->map = $map;
	}

	public function jsonToResource($json, $object_class_name, &$resource)
	{
		if (!$object = @json_decode($json)) {
			throw new Exception("Unable to parse JSON: $json");
		}

		return $this->jsonToResourceParse($object, $object_class_name, $resource);
	}

	protected function jsonToResourceParse($object, $object_class_name, &$resource)
	{
		if (!isset($this->map[$object_class_name])) {
			throw new \Exception("Unknown object type: $object_class_name");
		}

		foreach ($this->map[$object_class_name]['fields'] as $res_attribute => $def) {
			if (is_array($def)) {
				switch ($def[0]) {
					case DeclarativeModelService::TYPE_RESOURCE:
						$resource->$res_attribute = $object->$res_attribute;
						break;
					case DeclarativeModelService::TYPE_LIST:
						$data_class = 'services\\'.$def[2];
						$model_class = $def[3];

						$data_items = array();

						foreach ($object->$res_attribute as $data_item) {
							$data_items[] = $this->jsonToResourceParse($data_item, $model_class, new $data_class);
						}

						$resource->$res_attribute = $data_items;
						break;
					case DeclarativeModelService::TYPE_REF:
						$resource->$res_attribute = \Yii::app()->service->{$object->$res_attribute->service}($object->$res_attribute->id);
						break;
					case DeclarativeModelService::TYPE_OBJECT:
						$data_class = 'services\\'.$def[2];
						$resource->$res_attribute = $data_class::fromObject($object->$res_attribute);
						break;
					case DeclarativeModelService::TYPE_CONDITION:
						$resource->$res_attribute = $object->$res_attribute;
						break;
					case DeclarativeModelService::TYPE_REF_LIST:
						$refs = array();

						foreach ($object->$res_attribute as $ref) {
							$refs[] = \Yii::app()->service->{$def->service}($ref->id);
						}

						$resource->$res_attribute = $refs;
						break;
					default:
						throw new \Exception("Unknown declarative type: {$def[0]}");
				}
			} else {
				$resource->$res_attribute = $object->$res_attribute;
			}
		}

		return $resource;
	}

	public function jsonToModel($json, $model_class_name, $save=true, $extra_fields=false)
	{
		if (!$object = json_decode($json)) {
			throw new Exception("Invalid JSON encountered: $json");
		}

		return $this->jsonToModelParse($object, $model_class_name, $save=true, $extra_fields=false);
	}

	protected function jsonToModelParse($object, $model_class_name, $save=true, $extra_fields=false)
	{
		$_model_class_name = '\\'.$model_class_name;

		if (!isset($object->id) || (!$model = $_model_class_name::model()->findByPk($object->id))) {
			$model = new $_model_class_name;
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
						$model->{$def[1]} = $this->jsonToModelParse($object->$res_attribute, $def[2], $save);
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

							foreach ($object->$res_attribute as $item) {
								$related_objects[$related_object_name][$related_object_attribute][] = $this->jsonToModelParse($item, $def[3], false);
							}
						} else {
							throw new \Exception("Unhandled");
						}
						break;
					case DeclarativeModelService::TYPE_REF:
						$model->{$def[1]} = $object->$res_attribute->id;
						break;
					case DeclarativeModelService::TYPE_OBJECT:
						$data_class = 'services\\'.$def[2];
						$model->{$def[1]} = $data_class::fromObject($object->$res_attribute)->toModelValue();
						break;
					case DeclarativeModelService::TYPE_CONDITION:
						if ($object->$res_attribute) {
							$model->{$def[1]} = $def[3];
						}
						break;
					case DeclarativeModelService::TYPE_REF_LIST:
						$model_assignment_relation = $def[1];
						$model_assignment_field = $def[2];

						$assignment_model = $model_relations[$model_assignment_relation][1];
						$assignment_field = $model_relations[$model_assignment_relation][2];

						$assignments = array();

						foreach ($object->$res_attribute as $ref) {
							$assignment = new $assignment_model;
							$assignment->$assignment_field = $object->id;
							$assignment->$model_assignment_field = $ref->id;

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

						$related_object->$related_object_attribute = $object->$res_attribute;
						$model->$relation_name = $related_object;
					} else {
						$reference_object_attributes[$relation_name][$related_object_attribute] = $object->$res_attribute;

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
					$model->$def = $object->$res_attribute;
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
			if (is_array($def) && $def[0] == DeclarativeModelService::TYPE_LIST) {
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
					$class_name = get_class($current_item);
					$current_item_res = $mc->modelToResourceParse($current_item, new $class_name);

					if ($current_item_res->isEqual($item)) {
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
			throw new ValidationFailure("Validation failure on " . get_class($model), $model->errors);
		}
	}

	/*
	 * Delete model and throw an exception on failure
	 *
	 * @param BaseActiveRecord $model
	 */
	protected function deleteModel(\BaseActiveRecord $model)
	{
		if (!$model->save()) {
			throw new \Exception("Unable to delete: " . get_class($model), $model->errors);
		}
	}
}
