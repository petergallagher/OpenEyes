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

class DeclarativeTypeParser_List extends DeclarativeTypeParser
{
	public function modelToResourceParse($object, $attribute, $data_class, $param=null)
	{
		$data_list = DeclarativeTypeParser::expandObjectAttribute($object, $attribute);
		$_data_class = 'services\\'.$data_class;

		$data_items = array();

		foreach ($data_list as $data_item) {
			$data_items[] = $this->mc->modelToResourceParse($data_item, $data_class, new $_data_class);
		}

		return $data_items;
	}

	public function resourceToModelParse(&$model, $resource, $model_attribute, $res_attribute, $param1, $model_class)
	{
		if ((strpos($model_attribute,'.')) !== FALSE) {
			list($related_object_name, $related_object_attribute) = explode('.',$model_attribute);
		} else {
			list($related_object_name, $related_object_attribute) = array($model_attribute,null);
		}

		foreach ($resource->$res_attribute as $item) {
			$model->addToRelatedObjectArray($related_object_name,$related_object_attribute,$this->mc->resourceToModel($item, new $model_class, false));
		}
	}

	public function resourceToModel_RelatedObjects(&$model, $model_attribute, $copy_attribute, $save)
	{
		if ((strpos($model_attribute,'.')) !== FALSE) {
			list($related_object_name, $related_object_attribute) = explode('.',$model_attribute);
		} else {
			list($related_object_name, $related_object_attribute) = array($model_attribute,null);
		}

		$copy_attribute && $model->relatedObjectCopyAttributeFromModel($related_object_name,$related_object_attribute,$copy_attribute);

		$attribute = $related_object_attribute ? $related_object_name.'.'.$related_object_attribute : $related_object_name;

		$model->setAttribute($attribute, $this->filterListItems($model->expandAttribute($related_object_name), $related_object_attribute, $model->getRelatedObject($related_object_name,$related_object_attribute), $save));
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
					$current_item_res = $this->mc->modelToResource($current_item, new $class_name);
					$new_item_res = $this->mc->modelToResource($item, new $class_name);

					if ($current_item_res->isEqual($new_item_res)) {
						$found = true;
						$items_to_keep[] = $current_item;
						$matched_ids[] = $current_item->id;
					}
				}
			}

			if (!$found) {
				$items_to_keep[] = $item;

				$save && $this->mc->saveModel($item);
			}
		}

		if ($save) {
			if ($object->$relation) {
				foreach ($object->$relation as $current_item) {
					if (!in_array($current_item->id,$matched_ids)) {
						$this->mc->deleteModel($current_item);
					}
				}
			}
		}

		return $items_to_keep;
	}
}
