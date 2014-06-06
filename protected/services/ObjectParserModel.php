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

class ObjectParserModel extends ObjectParser
{
	public function parseObject($object, &$resource)
	{
		return $this->parse($object, get_class($object), $resource);
	}

	protected function parseListType($data_list, $data_class, $model_class)
	{
		return array_map(array('self','parseObject'), $data_list, array_fill(0, count($data_list), new $data_class(array())));
	}

	protected function parseReferenceType($object, $ar_attribute, $res_attribute, $data_class)
	{
		return \Yii::app()->service->$data_class($object->$ar_attribute);
	}

	protected function parseObjectType($data, $data_class)
	{
		if (is_object($data)) {
			return $this->parseObject($data, new $data_class(array()));
		} else {
			return new $data_class($data);
		}
	}

	protected function parseConditionType($object, $ar_attribute, $res_attribute, $condition_type, $condition_value)
	{
		switch ($condition_type) {
			case 'equals':
				return $object->$ar_attribute == $condition_value;
			default:
			throw new Exception("Unhandled condition type: $condition_type");
		}
	}

	protected function expandObjectAttribute($object, $ar_attribute, $res_attribute)
	{
		if ($dot = strpos($ar_attribute,'.')) {
			$relation = substr($ar_attribute,0,$dot);
			$attribute = substr($ar_attribute,$dot+1,strlen($ar_attribute));

			return $object->$relation->$attribute;
		}

		return $object->$ar_attribute;
	}
}
