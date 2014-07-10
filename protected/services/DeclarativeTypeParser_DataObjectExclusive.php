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

class DeclarativeTypeParser_DataObjectExclusive extends DeclarativeTypeParser
{
	public function modelToResourceParse($object, $attribute, $data_class, $param=null)
	{
		$data = DeclarativeTypeParser::expandObjectAttribute($object, $attribute);
		$data_class = 'services\\'.$data_class;

		if (is_object($data)) {
			return $this->mc->modelToResource($data, new $data_class(array('id' => isset($data->id) ? $data->id : null)));
		} else {
			return is_null($data) ? null : new $data_class($data);
		}
	}

	public function resourceToModelParse(&$model, $resource, $model_attribute, $res_attribute, $param1, $model_class, $save)
	{
		if (strpos($model_attribute,'.')) {
			list($related_object_name,$related_object_attribute) = explode('.',$model_attribute);

			if (is_object($resource->$res_attribute)) {
				if (method_exists($resource->$res_attribute,'getId')) {
					$id = $resource->$res_attribute->getId();
				} else {
					$id = @$resource->$res_attribute->id;
				}

				if ($id) {
					if (!$model_item = $model_class::model()->findByPk($id)) {
						throw new \Exception("$model_class not found: $id");
					}
				} else {
					$model_item = new $model_class;
				}

				$related_object = $this->mc->resourceToModel($resource->$res_attribute, $model_item, false);
			} else {
				$related_object = null;
			}

			$model->setRelatedObject($related_object_name, $related_object_attribute, $related_object);
		} else {
			throw new \Exception("Unhandled");
		}				
	}

	public function resourceToModel_RelatedObjects(&$model, $model_attribute, $copy_attribute, $save)
	{
		if (strpos($model_attribute,'.')) {
			list($related_object_name,$related_object_attribute) = explode('.',$model_attribute);

			$copy_attribute && $model->relatedObjectCopyAttributeFromModel($related_object_name, $related_object_attribute, $copy_attribute);
		} else {
			throw new \Exception("Unhandled");
		}

		$related_object = $model->getRelatedObject($related_object_name,$related_object_attribute);

		if ($save) {
			if ($old_object = $model->expandAttribute($related_object_name.'.'.$related_object_attribute)) {
				if (!is_object($related_object) || $old_object->id != $related_object->id) {
					$this->mc->deleteModel($old_object);
				}
			}
		}

		if ($model->expandAttribute($related_object_name) && $related_object) {
			$model->setAttribute($related_object_name.'.'.$related_object_attribute, $related_object);
			$save && $this->mc->saveModel($model->expandAttribute($related_object_name.'.'.$related_object_attribute));
		}
	}

	public function jsonToResourceParse($object, $attribute, $data_class, $model_class)
	{
		$data_class = 'services\\'.$data_class;

		return $object->$attribute ? $data_class::fromObject($object->$attribute) : null;
	}
}
