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

class DeclarativeTypeParser_Reference extends DeclarativeTypeParser
{
	public function modelToResourceParse($object, $attribute, $data_class, $param=null)
	{
		if ($object_property = DeclarativeTypeParser::expandObjectAttribute($object, $attribute)) {
			return \Yii::app()->service->$data_class($object_property);
		}

		return null;
	}

	public function resourceToModelParse(&$model, $resource, $model_attribute, $res_attribute, $model_class, $param1)
	{
		if ($resource->$res_attribute) {
			if (method_exists($resource->$res_attribute,'getId')) {
				$id_value = $resource->$res_attribute->getId();
			} else {
				$id_value = $resource->$res_attribute->id;
			}
		} else {
			$id_value = null;
		}

		$model->setAttribute($model_attribute, $id_value);
	}

	public function jsonToResourceParse($object, $attribute, $data_class, $model_class)
	{
		return \Yii::app()->service->{$object->$attribute->service}($object->$attribute->id);
	}
}
