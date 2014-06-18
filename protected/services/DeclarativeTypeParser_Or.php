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

class DeclarativeTypeParser_Or extends DeclarativeTypeParser
{
	public function modelToResourceParse($object, $relation, $or_fields, $ref_class=null)
	{
		foreach ($or_fields as $or_field) {
			if ($or_value = $this->mc->expandObjectAttribute($object, $or_field)) {
				return $or_value->$relation;
			}
		}
	}

	public function resourceToModelParse(&$model, $resource, $model_attribute, $res_attribute, $model_class, $param1, &$param2)
	{
		$rule = $this->mc->map->getRuleForOrClause(get_class($model), $res_attribute);

		switch ($rule[0]) {
			case DeclarativeModelService::RULE_TYPE_ALLNULL:
				$allnull = true;

				foreach ($rule[1] as $attribute) {
					if ($resource->$attribute !== null) {
						$allnull = false;
					}
				}

				$target = ($allnull ? $rule['then'] : $rule['else']) . '.' . $model_attribute;

				$this->mc->setObjectAttribute($model, $target, $resource->$res_attribute);
				break;
			default:
				throw new \Exception("Unknown rule type: {$rule[0]}");
		}
	}
}
