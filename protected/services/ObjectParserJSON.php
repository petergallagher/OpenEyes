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

class ObjectParserJSON extends ObjectParser
{
	public function parseJSON($json, $object_class_name, &$resource)
	{
		if (!$object = @json_decode($json)) {
			throw new Exception("Unable to parse JSON: $json");
		}

		return $this->parse($object, $object_class_name, $resource);
	}

	protected function parseListType($data_list, $data_class, $model_class)
	{
		return empty($data_list) ? array() : array_map(array('self','parse'), $data_list, array_fill(0, count($data_list), $model_class), array_fill(0, count($data_list), new $data_class(array())));
	}

	protected function parseReferenceType($object, $ar_attribute, $res_attribute, $data_class)
	{
		return \Yii::app()->service->{$object->$res_attribute->service}($object->$res_attribute->id);
	}

	protected function parseObjectType($data, $data_class)
	{
		return $data_class::fromObject($data);
	}

	protected function parseConditionType($object, $ar_attribute, $res_attribute, $condition_type, $condition_value)
	{
		return $object->$res_attribute;
	}

	protected function expandObjectAttribute($object, $ar_attribute, $res_attribute)
	{
		return $object->$res_attribute;
	}
}
