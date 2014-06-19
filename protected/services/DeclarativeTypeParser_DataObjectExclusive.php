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
		$data = $this->mc->expandObjectAttribute($object, $attribute);
		$data_class = 'services\\'.$data_class;

		if (is_object($data)) {
			return $this->mc->modelToResource($data, new $data_class);
		} else {
			return is_null($data) ? null : new $data_class($data);
		}
	}

	public function resourceToModelParse(&$model, $resource, $model_attribute, $res_attribute, $param1, $model_class, &$related_objects)
	{
		if ($pos = strpos($model_attribute,'.')) {
			$related_object_name = substr($model_attribute,0,$pos);
			$related_object_attribute = substr($model_attribute,$pos+1,strlen($model_attribute));

			if (is_object($resource->$res_attribute)) {
				$related_objects[$related_object_name][$related_object_attribute] = $this->mc->resourceToModel($resource->$res_attribute, new $model_class, false);
			} else {
				$related_objects[$related_object_name][$related_object_attribute] = null;
			}
		} else {
			throw new \Exception("Unhandled");
		}				
	}

	public function resourceToModel_RelatedObjects(&$model, $model_attribute, $copy_attribute, $related_objects, $save)
	{
		if ($pos = strpos($model_attribute,'.')) {
			$related_object_name = substr($model_attribute,0,$pos);
			$related_object_attribute = substr($model_attribute,$pos+1,strlen($model_attribute));

			if ($related_objects[$related_object_name][$related_object_attribute] && isset($copy_attribute)) {
				$related_objects[$related_object_name][$related_object_attribute]->{$copy_attribute} = $model->{$copy_attribute};
			}
		} else {
			throw new \Exception("Unhandled");
		}

		if ($save) {
			if ($old_object = $model->$related_object_name->$related_object_attribute) {
				$this->mc->deleteModel($old_object);
			}
		}

		if ($model->$related_object_name && $related_objects[$related_object_name][$related_object_attribute]) {
			$model->$related_object_name->$related_object_attribute = $related_objects[$related_object_name][$related_object_attribute];
			$save && $this->mc->saveModel($model->$related_object_name->$related_object_attribute);
		}
	}
}
