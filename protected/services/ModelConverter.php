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
				$resource->$res_attribute = DeclarativeTypeParser::expandObjectAttribute($object, $def);
			}
		}

		return $resource;
	}

	public function resourceToModel($resource, $model, $save=true, $extra_fields=false)
	{
		$model = new ModelConverter_ModelWrapper($this->map, $model, $extra_fields);

		$this->processRelatedObjects($model, $resource);

		foreach ($this->map->getFieldsForClass($model->getClass()) as $res_attribute => $def) {
			if (is_array($def)) {
				$class = 'services\\'.$def[0];
				$parser = new $class($this);
				$parser->resourceToModelParse($model, $resource, $def[1], $res_attribute, $def[2], @$def[3], $save);
			} else {
				$this->mapResourceAttributeToModel($model, $resource->$res_attribute, $def, $save);
			}
		}

		$save && $this->processRelatedObjects($model, $resource, true);

		foreach ($this->map->getFieldsForClass($model->getClass()) as $res_attribute => $def) {
			if (is_array($def)) {
				$class = 'services\\'.$def[0];
				$parser = new $class($this);
				if (method_exists($parser,'resourceToModel_RelatedObjects')) {
					$parser->resourceToModel_RelatedObjects($model, $def[1], $def[4], $save);
				}
			}
		}

		$save && $model->save();

		return $model->getModel();
	}

	protected function mapResourceAttributeToModel(&$model, $resource_value, $attribute_def, $save)
	{
		if ((strpos($attribute_def,'.')) !== FALSE) {
			list($relation_name, $related_object_attribute) = explode('.',$attribute_def);

			if (!$model->isRelatedObject($relation_name)) {
				return $this->mapResourceReferenceObjectToModel($model, $relation_name, $related_object_attribute, $resource_value, $save);
			}
		}

		$model->setAttribute($attribute_def, $resource_value);
	}

	protected function mapResourceReferenceObjectToModel(&$model, $relation_name, $related_object_attribute, $resource_value, $save)
	{
		$model->addReferenceObjectAttribute($relation_name, $related_object_attribute, $resource_value);

		if ($model->haveAllKeysForReferenceObject($relation_name)) {
			if ($related_object = $model->associateReferenceObjectWithModel($relation_name)) {
				$save && $this->saveModel($related_object);
			}
		}
	}

	protected function processRelatedObjects(&$model, $resource, $save=false)
	{
		foreach ($model->getRelatedObjectDefinitions() as $relation_name => $def) {
			list($attribute, $related_object_value) = $this->processRelatedObjectRules($def, $resource);

			$object_relation = ($pos = strpos($attribute,'.')) ? substr($attribute,0,$pos).'.'.$relation_name : $relation_name;

			if ($save && $related_object = $model->expandAttribute($object_relation)) {
				$this->saveModel($related_object);

				$model->setAttribute($attribute, $related_object->id);
			} else {
				$model->setAttribute($object_relation, $related_object_value, false);
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
						if (DeclarativeTypeParser::attributesAllNull($resource, $rule[1])) {
							$related_object_value = null;
						}
						break;
					case DeclarativeModelService::RULE_TYPE_ATTRIBUTE_IFNULL:
						if (DeclarativeTypeParser::attributesAllNull($resource, $rule[1])) {
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
