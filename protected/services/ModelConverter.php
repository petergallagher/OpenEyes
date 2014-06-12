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

		foreach ($this->map[$object_class_name] as $res_attribute => $def) {
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
						$data_class = $def[2];

						$resource->$res_attribute = \Yii::app()->service->$data_class($object->{$def[1]});
						break;
					case DeclarativeModelService::TYPE_OBJECT:
						$data = $this->expandObjectAttribute($object, $def[1]);
						$data_class = 'services\\'.$def[2];

						if (is_object($data)) {
							$resource->$res_attribute = $this->modelToResource($data, new $data_class);
						} else {
							$resource->$res_attribute = new $data_class($data);
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

		if (!method_exists($resource,'getId') || (!$model = $_model_class_name::model()->findByPk($resource->getId()))) {
			$model = new $_model_class_name;
		}

		if (is_array($extra_fields)) {
			foreach ($extra_fields as $key => $value) {
				$model->$key = $value;
			}
		}

		$related_resources = array();

		$model_relations = $model->relations();

		foreach ($this->map[$model_class_name] as $res_attribute => $def) {
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
							$this->addToRelatedObjects(substr($def[1],0,$pos), $res_attribute, array(
									'ar_attribute' => substr($def[1],$pos+1,strlen($def[1])),
									'ar_model' => $def[3],
									'res_model' => $def[2],
									'copy_field' => @$def[4]
								), $related_objects);
						}
						break;
					case DeclarativeModelService::TYPE_REF:
						$model->{$def[1]} = $resource->$res_attribute->getId();
						break;
					case DeclarativeModelService::TYPE_OBJECT:
						$model->{$def[1]} = $resource->$res_attribute->toModelValue();
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
					$this->addToRelatedObjects(substr($def,0,$pos), $res_attribute, substr($def,$pos+1,strlen($def)), $related_objects);
				} else {
					$model->$def = $resource->$res_attribute;
				}
			}
		}

		$save && $this->saveModel($model);

		foreach ($model->relations() as $relation_name => $relation_def) {
			if (isset($related_objects[$relation_name])) {
				if (!$related_object = $model->$relation_name) {
					$related_object = new $relation_def[1];
				}

				if (isset($related_objects[$relation_name]['keys'])) {
					foreach ($related_objects[$relation_name]['keys'] as $res_attribute => $ar_attribute) {
						$related_object->$ar_attribute = $resource->$res_attribute;
					}
				}

				if (isset($related_objects[$relation_name]['objects'])) {
					foreach ($related_objects[$relation_name]['objects'] as $res_attribute => $def) {
						$data_items = array();
						$matched_ids = array();

						foreach ($resource->$res_attribute as $data_item) {
							$found = false;
							if ($related_object->{$def['ar_attribute']}) {
								foreach ($related_object->{$def['ar_attribute']} as $existing_item) {
									$class_name = get_class($data_item);
									$existing_item_res = $this->modelToResource($existing_item, new $class_name);

									if ($existing_item_res->isEqual($data_item)) {
										$found = true;
										$data_items[] = $existing_item;
										$matched_ids[] = $existing_item->id;
									}
								}
							}
							if (!$found) {
								$data_items[] = $this->resourceToModel($data_item, $def['ar_model'], $save, $def['copy_field'] ? array($def['copy_field'] => $model->{$def['copy_field']}) : false);
							}
						}

						$related_object->{$def['ar_attribute']} = $data_items;

						if ($save) {
							if ($related_object->{$def['ar_attribute']}) {
								foreach ($related_object->{$def['ar_attribute']} as $existing_item) {
									if (!in_array($existing_item->id,$matched_ids)) {
										$this->deleteModel($existing_item);
									}
								}
							}
						}
					}
				}

				$save && $this->saveModel($related_object);

				$model->$relation_name = $related_object;
			}
		}

		return $model;
	}

	protected function addToRelatedObjects($relation_name, $res_attribute, $ar_attribute, &$related_objects)
	{
		if (!isset($related_objects[$relation_name])) {
			$related_objects[$relation_name] = array();
		}

		if (is_string($ar_attribute)) {
			$related_objects[$relation_name]['keys'][$res_attribute] = $ar_attribute;
		} else {
			$related_objects[$relation_name]['objects'][$res_attribute] = $ar_attribute;
		}
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
