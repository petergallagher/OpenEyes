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

		$model_relations = $model->relations();

		if ($class_related_objects = $this->map->getRelatedObjectsForClass(get_class($model))) {
			$this->processRelatedObjects($model, $resource, $class_related_objects);
		}

		$related_objects = array();
		$reference_object_attributes = array();
		$this->conditional_values_set = array();

		foreach ($this->map->getFieldsForClass(get_class($model)) as $res_attribute => $def) {
			if (is_array($def)) {
				$class = 'services\\'.$def[0];
				$parser = new $class($this);
				$parser->resourceToModelParse($model, $resource, $def[1], $res_attribute, $def[2], @$def[3], $related_objects);
			} else {
				$this->mapResourceAttributeToModel($model, $resource->$res_attribute, $def, $class_related_objects);
			}
		}

		if ($class_related_objects && $save) {
			$this->processRelatedObjects($model, $resource, $class_related_objects, true);
		}

		foreach ($this->map->getFieldsForClass(get_class($model)) as $res_attribute => $def) {
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

	protected function mapResourceAttributeToModel(&$model, $resource_value, $attribute_def, $class_related_objects)
	{
		if (($pos = strpos($attribute_def,'.')) !== FALSE) {
			$relation_name = substr($attribute_def,0,$pos);
			$related_object_attribute = substr($attribute_def,$pos+1,strlen($attribute_def));

			if (isset($class_related_objects[$relation_name])) {
				$this->setObjectAttribute($model, $relation_name.'.'.$related_object_attribute, $resource_value);
			} else {
				$reference_object_attributes[$relation_name][$related_object_attribute] = $resource_value;

				$reference_def = $this->map->getReferenceObjectForClass(get_class($model), $relation_name);

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
			$model->$attribute_def = $resource_value;
		}
	}

	protected function processRelatedObjects(&$model, $resource, $class_related_objects, $save=false)
	{
		foreach ($class_related_objects as $relation_name => $def) {
			$class_name = '\\'.$def[1];

			list($attribute, $related_object_value) = $this->processRelatedObjectRules($def, $resource);

			if ($pos = strpos($attribute,'.')) {
				$object_relation = substr($attribute,0,$pos).'.'.$relation_name;
			} else {
				$object_relation = $relation_name;
			}

			if ($save && $related_object = $this->expandObjectAttribute($model, $object_relation)) {
				$this->saveModel($related_object);

				$this->setObjectAttribute($model, $attribute, $related_object->id);
			} else {
				$this->setObjectAttribute($model, $object_relation, $related_object_value, false);
			}
		}
	}

	protected function processRelatedObjectRules($related_object_def, $resource)
	{
		$attribute = $related_object_def[0];
		$class_name = '\\'.$related_object_def[1];
		$related_object_value = new $class_name;

		if (isset($related_object_def['rules'])) {
			foreach ($related_object_def['rules'] as $rule) {
				switch ($rule[0]) {
					case DeclarativeModelService::RULE_TYPE_NULLIFNULL:
						if ($this->attributesAllNull($resource, $rule[1])) {
							$related_object_value = null;
						}
						break;
					case DeclarativeModelService::RULE_TYPE_ATTRIBUTE_IFNULL:
						if ($this->attributesAllNull($resource, $rule[1])) {
							$attribute = $rule[2];
						}
						break;
					default:
						throw new \Exception("Unknown related object rule type: {$rule[0]}");
				}
			}
		}

		return array($attribute, $related_object_value);
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
