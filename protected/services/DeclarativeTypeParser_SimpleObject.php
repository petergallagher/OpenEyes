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

class DeclarativeTypeParser_SimpleObject extends DeclarativeTypeParser
{
	public function modelToResourceParse($object, $attribute, $data_class, $param=null)
	{
		$data = DeclarativeTypeParser::expandObjectAttribute($object, $attribute);
		$data_class = 'services\\'.$data_class;

		if (is_object($data)) {
			return $this->mc->modelToResource($data, new $data_class);
		} else {
			return is_null($data) ? null : new $data_class($data);
		}
	}

	public function resourceToModelParse(&$model, $resource, $model_attribute, $res_attribute, $model_class, $param1, $save)
	{
		if (is_object($resource->$res_attribute) && method_exists($resource->$res_attribute,'toModelValue')) {
			$model->setAttribute($model_attribute,$resource->$res_attribute->toModelValue());
		} else {
			$data_class = 'services\\'.$model_class;
			$model->setAttribute($model_attribute,
				is_null($resource->$res_attribute)
				? null
				: ($resource->$res_attribute ? $data_class::fromObject($resource->$res_attribute)->toModelValue() : null)
			);
		}
	}

	public function jsonToResourceParse($object, $attribute, $data_class, $model_class)
	{
		$data_class = 'services\\'.$data_class;

		return $object->$attribute ? $data_class::fromObject($object->$attribute) : null;
	}
}
