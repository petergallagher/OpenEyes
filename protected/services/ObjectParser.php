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

abstract class ObjectParser
{
	protected $map;

	public function __construct($map)
	{
		$this->map = $map;
	}

	abstract protected function parseListType($data_list, $data_class, $model_class);
	abstract protected function parseReferenceType($object, $key, $attribute, $data_class);
	abstract protected function parseObjectType($data, $data_class);
	abstract protected function parseConditionType($object, $key, $model_field, $condition_type, $condition_value);
	abstract protected function expandObjectAttribute($object, $ar_attribute, $res_attribute);

	protected function parse($object, $object_class_name, &$resource)
	{
		if (!isset($this->map[$object_class_name])) {
			throw new \Exception("Unknown object type: $object_class_name");
		}

		foreach ($this->map[$object_class_name] as $res_attribute => $def) {
			if (is_array($def)) {
				switch ($type = array_shift($def)) {
					case DeclarativeModelService::TYPE_LIST:
						list($ar_attribute, $data_class, $model_class) = $def;
						$resource->$res_attribute = $this->parseListType($this->expandObjectAttribute($object, $ar_attribute, $res_attribute), 'services\\'.$data_class, $model_class);
						break;
					case DeclarativeModelService::TYPE_REF:
						list($ar_attribute, $data_class) = $def;
						$resource->$res_attribute = $this->parseReferenceType($object, $ar_attribute, $res_attribute, $data_class);
						break;
					case DeclarativeModelService::TYPE_OBJECT:
						list($ar_attribute, $data_class) = $def;
						$resource->$res_attribute = $this->parseObjectType($this->expandObjectAttribute($object, $ar_attribute, $res_attribute), 'services\\'.$data_class);
						break;
					case DeclarativeModelService::TYPE_CONDITION:
						list($ar_attribute, $condition_type, $condition_value) = $def;
						$resource->$res_attribute = $this->parseConditionType($object, $ar_attribute, $res_attribute, $condition_type, $condition_value);
						break;
					default:
						throw new \Exception("Unknown declarative type: $type");
				}
			} else {
				$resource->$res_attribute = $this->expandObjectAttribute($object, $def, $res_attribute);
			}
		}

		return $resource;
	}
}
