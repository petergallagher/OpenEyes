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

class ModelMap
{
	protected $map;

	public function __construct($map=null)
	{
		if (!is_array($map)) {
			throw new \Exception("Map not specified");
		}

		$this->map = $map;

		$this->verifyMap();
	}

	protected function verifyMap()
	{
		foreach ($this->map as $model_class => $map) {
			if (!isset($map['fields'])) {
				throw new \Exception("No fields defined in map for $model_class");
			}

			foreach ($map['fields'] as $res_attribute => $def) {
				if (is_array($def)) {
					if ($pos = strpos($def[1],'.')) {
						$relation = substr($def[1],0,$pos);

						if (!isset($map['related_objects'][$relation]) && !isset($map['reference_objects'][$relation])) {
							throw new \Exception("Relation '$relation' used in field definitions for $model_class but not declared as a related object or a reference object.");
						}
					}
				} else {
					if ($pos = strpos($def,'.')) {
						$relation = substr($def,0,$pos);

						if (!isset($map['related_objects'][$relation]) && !isset($map['reference_objects'][$relation])) {
							throw new \Exception("Relation '$relation' used in field definitions for $model_class but not declared as a related object or a reference object.");
						}
					}
				}
			}
		}
	}

	public function getFieldsForClass($class_name)
	{
		if (!isset($this->map[$class_name]['fields'])) {
			throw new \Exception("Unknown object type: $class_name");
		}

		return $this->map[$class_name]['fields'];
	}

	public function getRelatedObjectsForClass($class_name)
	{
		return @$this->map[$class_name]['related_objects'];
	}

	public function getReferenceObjectForClass($class_name, $relation_name)
	{
		return @$this->map[$class_name]['reference_objects'][$relation_name];
	}
}
