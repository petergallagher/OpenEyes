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

class DeclarativeTypeParser_Elements extends DeclarativeTypeParser
{
	public function modelToResourceParse($object, $attribute, $data_class, $param=null)
	{
		$element_list = DeclarativeTypeParser::expandObjectAttribute($object, $attribute);

		$resource_items = array();

		foreach ($element_list as $element) {
			$data_class = 'OEModule\\'.$element->event->eventType->class_name.'\\services\\'.\CHtml::modelName($element);
			$service_class = '\\'.$data_class.'Service';

			$service = new $service_class;
			$converter = new ModelConverter($service);

			$data_items[] = $converter->modelToResourceParse($element, \CHtml::modelName($element), new $data_class);
		}

		return $data_items;
	}

	public function setElementRelatedObjects(&$resource_item, $element)
	{
	}

	public function resourceToModelParse(&$model, $resource, $model_attribute, $res_attribute, $param1, $model_class, $save)
	{
		/*
		if ((strpos($model_attribute,'.')) !== FALSE) {
			list($related_object_name, $related_object_attribute) = explode('.',$model_attribute);
		} else {
			list($related_object_name, $related_object_attribute) = array($model_attribute,null);
		}

		$model->setRelatedObject($related_object_name, $related_object_attribute, array());

		if ($resource->$res_attribute) {
			foreach ($resource->$res_attribute as $item) {
				$model->addToRelatedObjectArray($related_object_name,$related_object_attribute,$this->mc->resourceToModel($item, new $model_class, false));
			}
		}
		*/
	}

	public function resourceToModel_RelatedObjects(&$model, $model_attribute, $copy_attribute, $save)
	{
		/*
		"resourceToModel_RelatedObjects for ".get_class($model)."\n";

		if ((strpos($model_attribute,'.')) !== FALSE) {
			list($related_object_name, $related_object_attribute) = explode('.',$model_attribute);
		} else {
			list($related_object_name, $related_object_attribute) = array($model_attribute,null);
		}

		$copy_attribute && $model->relatedObjectCopyAttributeFromModel($related_object_name,$related_object_attribute,$copy_attribute);

		$attribute = $related_object_attribute ? $related_object_name.'.'.$related_object_attribute : $related_object_name;

		$model->setAttribute($attribute, $this->filterListItems($model->expandAttribute($related_object_name), $related_object_attribute, $model->getRelatedObject($related_object_name,$related_object_attribute), $save));
		*/
	}

	public function jsonToResourceParse($object, $attribute, $data_class, $model_class)
	{
		/*
		$data_class = 'services\\'.$data_class;

		$data_items = array();

		foreach ($object->$attribute as $data_item) {
			$data_items[] = $this->mc->jsonToResourceParse($data_item, $model_class, new $data_class);
		}

		return $data_items;
		*/
	}

/*
	public function saveListItem($item)
	{
		if ($related_objects = $this->mc->service->map->getRelatedObjectsForClass(get_class($item))) {
			foreach ($related_objects as $relation => $def) {
				if (@$def['save'] != 'no') {
					if ($item->$relation) {
						foreach ($this->mc->service->map->getRelatedObjectRelatedObjectsForClass(get_class($item), $relation) as $related_def) {
							$this->mc->saveModel($item->$relation->{$related_def[1]});
							$item->$relation->{$related_def[0]} = $item->$relation->{$related_def[1]}->primaryKey;
						}

						$this->mc->saveModel($item->$relation);

						$parent_attribute = preg_replace('/^.*\./','',$def[0]);
						$item->$parent_attribute = $item->$relation->primaryKey;
					}
				}
			}
		}

		$this->mc->saveModel($item);
	}
	*/
}
