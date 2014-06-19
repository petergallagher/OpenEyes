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

class DeclarativeTypeParser_DataObject extends DeclarativeTypeParser
{
	public function modelToResourceParse($object, $attribute, $data_class, $param=null)
	{
		$data = $this->mc->expandObjectAttribute($object, $attribute);
		$data_class = 'services\\'.$data_class;

		if (is_object($data)) {
			return $this->mc->modelToResource($data, new $data_class);
		} else {
			return is_null($data) ? null : new $data_class($data);
		}
	}

	public function resourceToModelParse(&$model, $resource, $model_attribute, $res_attribute, $param1, $model_class)
	{
		if (strpos($model_attribute,'.')) {
			list($related_object_name,$related_object_attribute) = explode('.',$model_attribute);

			$model->setRelatedObject($related_object_name, $related_object_attribute, is_object($resource->$res_attribute)
				? $this->mc->resourceToModel($resource->$res_attribute, new $model_class, false)
				: null
			);
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

		if ($model->expandAttribute($related_object_name) && $model->getRelatedObject($related_object_name,$related_object_attribute)) {
			$model->setAttribute($related_object_name.'.'.$related_object_attribute, $model->getRelatedObject($related_object_name,$related_object_attribute));
			$save && $this->mc->saveModel($model->expandAttribute($model,$related_object_name.'.'.$related_object_attribute));
		}
	}

	public function jsonToResourceParse($object, $attribute, $data_class, $model_class)
	{
		$data_class = 'services\\'.$data_class;

		return $data_class::fromObject($object->$attribute);
	}
}
