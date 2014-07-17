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
	public $service;
	public $map;

	public function __construct($service, $map=null)
	{
		$this->service = $service;

		if ($map === null) {
			$this->map = new ModelMap($service::$model_map);
		} else if ($map instanceof ModelMap) {
			$this->map = $map;
		} else {
			$this->map = new ModelMap($map);
		}
	}

	public function modelToResource($object, &$resource)
	{
		return $this->modelToResourceParse($object, get_class($object), $resource);
	}

	protected function getParserByName($name)
	{
		$class = 'services\\'.$name;
		return new $class($this);
	}

	public function modelToResourceParse($object, $object_class_name, &$resource)
	{
		foreach ($this->map->getFieldsForClass($object_class_name) as $res_attribute => $def) {
			if (is_array($def)) {
				$resource->$res_attribute = $this->getParserByName($def[0])->modelToResourceParse($object, $def[1], @$def[2], @$def[3]);
			} else {
				$resource->$res_attribute = $this->service->expandModelAttribute($object, $def);
			}
		}

		return $resource;
	}

	public function resourceToModel($resource, $model, $save=true, $extra_fields=false)
	{
		$model = new ModelConverter_ModelWrapper($this->map, $model, $extra_fields);

		$this->service->setUpRelatedObjects($model, $resource);

		foreach ($this->map->getFieldsForClass($model->getClass()) as $res_attribute => $def) {
			if (is_array($def)) {
				$this->getParserByName($def[0])->resourceToModelParse($model, $resource, $def[1], $res_attribute, @$def[2], @$def[3], $save);
			} else {
				if ($this->isReferenceObjectAttribute($model, $def)) {
					$this->mapResourceReferenceObjectToModel($model, $def, $resource->$res_attribute);
				} else {
					$this->service->setModelAttributeFromResource($model, $def, $resource->$res_attribute);
				}
			}
		}

		$this->service->getComplexReferenceObjects($model, $resource);

		$save && $this->saveRelatedObjects($model, $resource);

		$parser = null;

		foreach ($this->map->getFieldsForClass($model->getClass()) as $res_attribute => $def) {
			if (is_array($def)) {
				if (!$parser || \CHtml::modelName($parser) != 'services_'.$def[0]) {
					if ($save && $parser && method_exists($parser,'resourceToModel_RelatedObjects_DeleteItems')) {
						$parser->resourceToModel_RelatedObjects_DeleteItems($last_def[3]);
					}
					$parser = $this->getParserByName($def[0]);
				}
				if (method_exists($parser,'resourceToModel_RelatedObjects')) {
					$parser->resourceToModel_RelatedObjects($model, $def[1], $def[4], $save);
				}

				$last_def = $def;
			}
		}


		if ($save && $parser && method_exists($parser,'resourceToModel_RelatedObjects_DeleteItems')) {
			$parser->resourceToModel_RelatedObjects_DeleteItems($def[3]);
		}

		if ($save) {
			$model->save();

			foreach ($this->map->getFieldsForClass($model->getClass()) as $res_attribute => $def) {
				if (is_array($def)) {
					$parser = $this->getParserByName($def[0]);

					if (method_exists($parser,'resourceToModel_AfterSave')) {
						$parser->resourceToModel_AfterSave($model, $resource);
					}
				}
			}

			if (method_exists($this->service,'resourceToModel_AfterSave')) {
				$this->service->resourceToModel_AfterSave($model);
			}
		}


		return $model->getModel();
	}

	protected function isReferenceObjectAttribute($model, $attribute_def)
	{
		if (substr_count($attribute_def,'.') >0) {
			$ex = explode('.',$attribute_def);

			$related_object_attribute = array_pop($ex);
			$relation_name = implode('.',$ex);

			return !$model->isRelatedObject($relation_name);
		}

		return false;
	}

	protected function mapResourceReferenceObjectToModel(&$model, $attribute_def, $resource_value)
	{
		$ex = explode('.',$attribute_def);
		$related_object_attribute = array_pop($ex);
		$relation_name = implode('.',$ex);

		$model->addReferenceObjectAttribute($relation_name, $related_object_attribute, $resource_value);

		if ($model->haveAllKeysForReferenceObject($relation_name)) {
			$model->associateReferenceObjectWithModel($relation_name);
		} else {
			$model->dissociateReferenceObjectFromModel($relation_name);
		}
	}

	protected function saveRelatedObjects(&$model, $resource)
	{
		foreach ($model->getRelatedObjectDefinitions() as $relation_name => $def) {
			if (@$def['save'] != 'no') {
				$attribute = $this->service->getRelatedObjectAttribute($relation_name, $def, $resource);
				$related_object_value = $this->service->getRelatedObjectValue($model, $relation_name, $def, $resource);

				$object_relation = ($pos = strpos($attribute,'.')) ? substr($attribute,0,$pos).'.'.$relation_name : $relation_name;

				if ($related_object = $model->expandAttribute($object_relation)) {
					if (isset($def[2])) {
						$related_object->{$def[2]} = $model->getId();
					}

					$this->saveModel($related_object);

					$attribute && $model->setAttribute($attribute, $related_object->id);
				}
			}
		}
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
