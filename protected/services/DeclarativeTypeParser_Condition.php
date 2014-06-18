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

class DeclarativeTypeParser_Condition extends DeclarativeTypeParser
{
	public function modelToResourceParse($object, $attribute, $condition_type, $condition_value=null)
	{
		switch ($condition_type) {
			case 'equals':
				return $object->$attribute == $condition_value;
				break;
			default:
				throw new Exception("Unknown condition type: $condition_type");
		}
	}

	public function resourceToModelParse(&$model, $resource, $model_attribute, $res_attribute, $param1, $model_class, &$conditional_values_set)
	{
		if ($resource->$res_attribute) {
			if (!in_array($model_attribute, $conditional_values_set)) {
				$model->{$model_attribute} = $model_class;
				$conditional_values_set[] = $model_attribute;
			} else {
				throw new \Exception("Unable to differentiate condition as more than one attribute is true.");
			}
		}
	}
}
