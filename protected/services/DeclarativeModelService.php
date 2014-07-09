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

class DeclarativeModelService extends ModelService
{
	const TYPE_LIST = 'DeclarativeTypeParser_List';
	const TYPE_REF = 'DeclarativeTypeParser_Reference';
	const TYPE_SIMPLEOBJECT = 'DeclarativeTypeParser_SimpleObject';
	const TYPE_DATAOBJECT = 'DeclarativeTypeParser_DataObject';
	const TYPE_DATAOBJECT_EXCLUSIVE = 'DeclarativeTypeParser_DataObjectExclusive';
	const TYPE_CONDITION = 'DeclarativeTypeParser_Condition';
	const TYPE_RESOURCE = 'DeclarativeTypeParser_Resource';
	const TYPE_REF_LIST = 'DeclarativeTypeParser_RefList';
	const TYPE_ELEMENTS = 'DeclarativeTypeParser_Elements';

	/**
	 * @param BaseActiveRecord $model
	 * @return Resource
	 */
	public function modelToResource($model)
	{
		$resource = parent::modelToResource($model);

		$mc = new ModelConverter($this);

		return $mc->modelToResource($model, $resource);
	}

	/**
	 * @param string $json
	 * @return Resource
	 */
	public function jsonToResource($json)
	{
		$resource = parent::jsonToResource($json);

		$jc = new JSONConverter($this);

		return $jc->jsonToResource($json, $this::$primary_model, $resource);
	}

	/**
	 * @param object $resource
	 * @return object $model
	 */
	public function resourceToModel($resource, $model, $save=true)
	{
		$mc = new ModelConverter($this);

		return $mc->resourceToModel($resource, $model, $save);
	}

	/**
	 * @param string $json
	 * @return Resource
	 */
	public function jsonToModel($json, $model, $save=true)
	{
		$jc = new JSONConverter($this);

		return $jc->jsonToModel($json, $model, $save);
	}

	/**
	 * @param string $relation_name
	 * @param Array $related_object_def
	 * @param Resource $resource
	 *
	 * Allows overriding the attribute for a specific relation in the service class
	 */
	public function getRelatedObjectAttribute($relation_name, $related_object_def, $resource)
	{
		return $related_object_def[0];
	}

	/**
	 * @param string $relation_name
	 * @param Array $related_object_def
	 * @param Resource $resource
	 *
	 * Allows overriding the value for a specific relation in the service class (eg nulling it under certain conditions)
	 */
	public function getRelatedObjectValue($model, $relation_name, $related_object_def, $resource)
	{
		$class_name = '\\'.$related_object_def[1];

		if (isset($resource->{$related_object_def[0]})) {
			$id = $resource->{$related_object_def[0]};

			if ($value = $class_name::model()->findByPk($id)) {
				return $value;
			}
		}

		return new $class_name;
	}

	/**
	 * @param ActiveRecord $model
	 * @param Resource $resource
	 *
	 * Allows overriding the setting of reference objects where they're a bit too complicated for simple representation with
	 * the declarative DSL. Currently this is used for obtaining the firm for an episode, which is based on the firm name
	 * and subspecialty assignment
	 */
	public function getComplexReferenceObjects(&$model, $resource)
	{
	}

	public function expandModelAttribute($model, $attribute)
	{
		return DeclarativeTypeParser::expandObjectAttribute($model, $attribute);
	}

	public function setModelAttributeFromResource(&$model, $attribute, $resource_value)
	{
		$model->setAttribute($attribute, $resource_value);
	}

	public function setUpRelatedObjects(&$model, $resource)
	{
		foreach ($model->getRelatedObjectDefinitions() as $relation_name => $def) {
			$attribute = $this->getRelatedObjectAttribute($relation_name, $def, $resource);
			$related_object_value = $this->getRelatedObjectValue($model, $relation_name, $def, $resource);

			$object_relation = ($pos = strpos($attribute,'.')) ? substr($attribute,0,$pos).'.'.$relation_name : $relation_name;

			$model->setAttribute($object_relation, $related_object_value, false);
		}
	}

	public function setUpListItem($item, $model_class)
	{
		if ($id = method_exists($item,'getId') ? $item->getId() : @$item->id) {
			return $model_class::model()->findByPk($id);
		} else {
			return new $model_class;
		}
	}
}
