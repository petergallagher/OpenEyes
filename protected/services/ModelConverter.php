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

class ModelConverter
{
	protected $map;

	public function __construct($map)
	{
		$this->map = $map;
	}

	public function modelToResource($object, &$resource)
	{
		return $this->parse($object, get_class($object), $resource);
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
						$data_list = $this->expandObjectAttribute($object, $def[1], $res_attribute);
						$data_class = 'services\\'.$def[2];

						$resource->$res_attribute = empty($data_list) ?
							array() :
							array_map(array(
									'self','modelToResource'
								),
								$data_list,
								array_fill(0, count($data_list), new $data_class)
							);
						break;
					case DeclarativeModelService::TYPE_REF:
						$data_class = $def[2];

						$resource->$res_attribute = \Yii::app()->service->$data_class($object->{$def[1]});
						break;
					case DeclarativeModelService::TYPE_OBJECT:
						$data = $this->expandObjectAttribute($object, $def[1], $res_attribute);
						$data_class = 'services\\'.$def[2];

						if (is_object($data)) {
							$resource->$res_attribute = $this->modelToResource($data, new $data_class);
						} else {
							$resource->$res_attribute = new $data_class($data);
						}
						break;
					case DeclarativeModelService::TYPE_CONDITION:
						switch ($def[2]) {
							case 'equals':
								$resource->$res_attribute = $object->{$def[1]} == $def[3];
								break;
							default:
								throw new Exception("Unknown condition type: {$def[2]}");
						}

						break;
					default:
						throw new \Exception("Unknown declarative type: {$def[0]}");
				}
			} else {
				$resource->$res_attribute = $this->expandObjectAttribute($object, $def, $res_attribute);
			}
		}

		return $resource;
	}

	protected function expandObjectAttribute($object, $ar_attribute, $res_attribute)
	{
		if ($dot = strpos($ar_attribute,'.')) {
			$relation = substr($ar_attribute,0,$dot);
			$attribute = substr($ar_attribute,$dot+1,strlen($ar_attribute));

			return $object->$relation ? $object->$relation->$attribute : null;
		}

		return $object->$ar_attribute;
	}
}
