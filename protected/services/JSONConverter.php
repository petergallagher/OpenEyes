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
	protected $service;

	public function __construct($service)
	{
		$this->service = $service;
	}

	public function jsonToResource($json, $object_class_name, &$resource)
	{
		if (!$object = @json_decode($json)) {
			throw new \Exception("Unable to parse JSON: $json");
		}

		return $this->jsonToResourceParse($object, $object_class_name, $resource);
	}

	public function jsonToResourceParse($object, $object_class_name, &$resource)
	{
		foreach ($this->service->map->getFieldsForClass($object_class_name) as $res_attribute => $def) {
			if (is_array($def)) {
				$class = 'services\\'.$def[0];
				$parser = new $class($this);
				$resource->$res_attribute = $parser->jsonToResourceParse($object,$res_attribute,@$def[2],@$def[3]);
			} else {
				$resource->$res_attribute = $object->$res_attribute;
			}
		}

		return $resource;
	}

	public function jsonToModel($json, $model_class_name, $save=true)
	{
		if (!$object = json_decode($json)) {
			throw new \Exception("Invalid JSON encountered: $json");
		}

		$mc = new ModelConverter($this->service);

		return $mc->resourceToModel($object, $model_class_name, $save);
	}
}
