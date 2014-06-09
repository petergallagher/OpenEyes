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

class JSONConverter
{
	protected $map;

	public function __construct($map)
	{
		$this->map = $map;
	}

	public function jsonToResource($json, $object_class_name, &$resource)
	{
		if (!$object = @json_decode($json)) {
			throw new Exception("Unable to parse JSON: $json");
		}

		return $this->parse($object, $object_class_name, $resource);
	}

	protected function parse($object, $object_class_name, &$resource)
	{
		if (!isset($this->map[$object_class_name])) {
			throw new \Exception("Unknown object type: $object_class_name");
		}

		foreach ($this->map[$object_class_name] as $res_attribute => $def) {
			if (is_array($def)) {
				switch ($def[0]) {
					case DeclarativeModelService::TYPE_LIST:
						$data_class = 'services\\'.$def[2];
						$model_class = $def[3];

						$resource->$res_attribute = empty($object->$res_attribute) ? 
							array() : 
							array_map(array(
									'self','parse'
								),
								$object->$res_attribute,
								array_fill(0, count($object->$res_attribute), $model_class),
								array_fill(0, count($object->$res_attribute), new $data_class)
							);
						break;
					case DeclarativeModelService::TYPE_REF:
						$resource->$res_attribute = \Yii::app()->service->{$object->$res_attribute->service}($object->$res_attribute->id);
						break;
					case DeclarativeModelService::TYPE_OBJECT:
						$data_class = 'services\\'.$def[2];
						$resource->$res_attribute = $data_class::fromObject($object->$res_attribute);
						break;
					case DeclarativeModelService::TYPE_CONDITION:
						$resource->$res_attribute = $object->$res_attribute;
						break;
					default:
						throw new \Exception("Unknown declarative type: {$def[0]}");
				}
			} else {
				$resource->$res_attribute = $object->$res_attribute;
			}
		}

		return $resource;
	}
}
