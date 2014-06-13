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

		return $this->jsonToResourceParse($object, $object_class_name, $resource);
	}

	protected function jsonToResourceParse($object, $object_class_name, &$resource)
	{
		if (!isset($this->map[$object_class_name])) {
			throw new \Exception("Unknown object type: $object_class_name");
		}

		foreach ($this->map[$object_class_name]['fields'] as $res_attribute => $def) {
			if (is_array($def)) {
				switch ($def[0]) {
					case DeclarativeModelService::TYPE_RESOURCE:
						$resource->$res_attribute = $object->$res_attribute;
						break;
					case DeclarativeModelService::TYPE_LIST:
						$data_class = 'services\\'.$def[2];
						$model_class = $def[3];

						$data_items = array();

						foreach ($object->$res_attribute as $data_item) {
							$data_items[] = $this->jsonToResourceParse($data_item, $model_class, new $data_class);
						}

						$resource->$res_attribute = $data_items;
						break;
					case DeclarativeModelService::TYPE_REF:
						$resource->$res_attribute = \Yii::app()->service->{$object->$res_attribute->service}($object->$res_attribute->id);
						break;
					case DeclarativeModelService::TYPE_SIMPLEOBJECT:
					case DeclarativeModelService::TYPE_DATAOBJECT:
					case DeclarativeModelService::TYPE_DATAOBJECT_EXCLUSIVE:
						$data_class = 'services\\'.$def[2];
						$resource->$res_attribute = $data_class::fromObject($object->$res_attribute);
						break;
					case DeclarativeModelService::TYPE_CONDITION:
						$resource->$res_attribute = $object->$res_attribute;
						break;
					case DeclarativeModelService::TYPE_REF_LIST:
						$refs = array();

						foreach ($object->$res_attribute as $ref) {
							$refs[] = \Yii::app()->service->{$def->service}($ref->id);
						}

						$resource->$res_attribute = $refs;
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

	public function jsonToModel($json, $model_class_name, $save=true)
	{
		if (!$object = json_decode($json)) {
			throw new Exception("Invalid JSON encountered: $json");
		}

		$mc = new ModelConverter($this->map);

		return $mc->resourceToModel($object, $model_class_name, $save);
	}
}
