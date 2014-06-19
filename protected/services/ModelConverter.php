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
	public $map;

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

	public function expandObjectAttribute($object, $attributes)
	{
		if (!is_array($attributes)) {
			$attributes = explode('.',$attributes);
		}

		$attribute = array_shift($attributes);

		if (count($attributes) >0) {
			if (!$object->$attribute) {
				return false;
			}

			return $this->expandObjectAttribute($object->$attribute, $attributes);
		}

		return $object->$attribute;
	}

	public function setObjectAttribute(&$object, $attributes, $value, $force=true)
	{
		if (!is_array($attributes)) {
			$attributes = explode('.',$attributes);
		}

		$attribute = array_shift($attributes);

		if (count($attributes) >0) {
			if (!$object->$attribute) {
				return false;
			}

			return $this->setObjectAttribute($object->$attribute, $attributes, $value, $force);
		}

		if ($force || !$object->$attribute) {
			$object->$attribute = $value;
		}
	}

	public function setObjectAttributes(&$object, $attributes)
	{
		foreach ($attributes as $key => $value) {
			$object->$key = $value;
		}
	}

	public function attributesAllNull($object, $attributes)
	{
		foreach ($attributes as $attribute) {
			if ($object->$attribute !== null) {
				return false;
			}
		}

		return true;
	}

	public function resourceToModel($resource, $model, $save=true, $extra_fields=false)
	{
		is_array($extra_fields) &&
			$this->setObjectAttributes($model, $extra_fields);

		$model_class_name = get_class($model);
		$model_relations = $model->relations();

		if ($class_related_objects = $this->map->getRelatedObjectsForClass($model_class_name)) {
			$this->createdRelatedObjects($model, $resource, $class_related_objects);
		}

		$related_objects = array();
		$reference_object_attributes = array();
		$this->conditional_values_set = array();

		foreach ($this->map->getFieldsForClass($model_class_name) as $res_attribute => $def) {
			if (is_array($def)) {
				$class = 'services\\'.$def[0];
				$parser = new $class($this);
				$parser->resourceToModelParse($model, $resource, $def[1], $res_attribute, $def[2], @$def[3], $related_objects);
			} else {
				if (($pos = strpos($def,'.')) !== FALSE) {
					$relation_name = substr($def,0,$pos);
					$related_object_attribute = substr($def,$pos+1,strlen($def));

					if (isset($class_related_objects[$relation_name])) {
						$this->setObjectAttribute($model, $relation_name.'.'.$related_object_attribute, $resource->$res_attribute);
					} else {
						$reference_object_attributes[$relation_name][$related_object_attribute] = $resource->$res_attribute;

						$reference_def = $this->map->getReferenceObjectForClass($model_class_name, $relation_name);

						if (array_keys($reference_object_attributes[$relation_name]) == $reference_def[2]) {
							// All required properties for matching the reference item have been set, so now we can associate it with the model
							$criteria = new \CDbCriteria;

							foreach ($reference_object_attributes[$relation_name] as $key => $value) {
								$criteria->compare($key, $value);
							}

							$related_object_class = '\\'.$reference_def[1];

							if (!$related_object = $related_object_class::model()->find($criteria)) {
								$related_object = new $related_object_class;

								$this->setObjectAttributes($related_object, $reference_object_attributes[$relation_name]);

								$save && $this->saveModel($related_object);
							}

							$model->{$reference_def[0]} = $related_object->primaryKey;
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
					$allnull = $this->attributesAllNull($resource, $def[2]);
					$target = $allnull ? $def[0][1] : $def[0][0];

					$target_object = ($pos = strpos($target,'.')) ? substr($target,0,$pos) . '.' . $relation_name : $relation_name;
					$target_value = $this->expandObjectAttribute($model, $target_object.'.primaryKey');

					$save && $this->saveModel($this->expandObjectAttribute($model, $target_object));

					$target_object = ($pos = strpos($target,'.')) ? substr($target,0,$pos) . '.' . substr($target,$pos+1,strlen($target)) : $target;

					$this->setObjectAttribute($model, $target_object, $target_value, false);
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
				$class = 'services\\'.$def[0];
				$parser = new $class($this);
				if (method_exists($parser,'resourceToModel_RelatedObjects')) {
					$parser->resourceToModel_RelatedObjects($model, $def[1], $def[4], $related_objects, $save);
				}
			}
		}

		$save && $this->saveModel($model);

		return $model;
	}

	public function createdRelatedObjects(&$model, $resource, $class_related_objects)
	{
		foreach ($class_related_objects as $relation_name => $def) {
			$class_name = '\\'.$def[1];

			if (is_array($def[0])) {
				$allnull = $this->attributesAllNull($resource, $def[2]);
				$target = ($allnull ? $def[0][1] : $def[0][0]);
				$target = ($pos = strpos($target,'.')) ? substr($target,0,$pos) . '.' . $relation_name : $relation_name;

				$this->setObjectAttribute($model, $target, new $class_name, false);
			} else {
				$related_object_value = $this->processRelatedObjectRules($def, $resource, $class_name);

				$this->setObjectAttribute($model, $relation_name, $related_object_value, false);
			}
		}
	}

	protected function processRelatedObjectRules($related_object_def, $resource, $class_name)
	{
		if (isset($related_object_def['rules'])) {
			foreach ($related_object_def['rules'] as $rule) {
				switch ($rule[0]) {
					case DeclarativeModelService::RULE_TYPE_NULLIFNULL:
						if ($this->attributesAllNull($resource, $rule[1])) {
							return null;
						}
						break;
					default:
						throw new \Exception("Unknown related object rule type: {$rule[0]}");
				}
			}
		}

		return new $class_name;
	}

	/*
	 * Save model object and throw a service layer exception on failure
	 *
	 * @param BaseActiveRecord $model
	 */
	public function saveModel(\BaseActiveRecord $model)
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
	public function deleteModel(\BaseActiveRecord $model)
	{
		if (!$model->delete()) {
			throw new \Exception("Unable to delete: " . get_class($model), $model->errors);
		}
	}
}
