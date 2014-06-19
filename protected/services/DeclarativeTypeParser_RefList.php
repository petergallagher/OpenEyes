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

class DeclarativeTypeParser_RefList extends DeclarativeTypeParser
{
	public function modelToResourceParse($object, $relation, $field, $ref_class=null)
	{
		$refs = array();

		foreach ($object->$relation as $ref_assignment_model) {
			$refs[] = \Yii::app()->service->$ref_class($ref_assignment_model->$field);
		}

		return $refs;
	}

	public function resourceToModelParse(&$model, $resource, $model_assignment_relation, $res_attribute, $model_assignment_field, $param1)
	{
		$model_relations = $model->getRelations();

		$assignment_model = $model_relations[$model_assignment_relation][1];
		$assignment_field = $model_relations[$model_assignment_relation][2];

		$assignments = array();

		foreach ($resource->$res_attribute as $ref) {
			$assignment = new $assignment_model;
			$assignment->$assignment_field = $model->getId();
			$assignment->$model_assignment_field = $ref->getId();

			$assignments[] = $assignment;
		}

		$model->setAttribute($model_assignment_relation,$assignments);
	}
}
