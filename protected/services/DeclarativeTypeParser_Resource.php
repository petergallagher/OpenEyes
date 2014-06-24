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

class DeclarativeTypeParser_Resource extends DeclarativeTypeParser
{
	public function modelToResourceParse($object, $attribute, $data_class, $param=null)
	{
		$resource_class = 'services\\'.$data_class;
		return $this->mc->modelToResource($object->$attribute, new $resource_class(array('id' => $object->$attribute->id, 'last_modified' => strtotime($object->$attribute->last_modified_date))));
	}

	public function resourceToModelParse(&$model, $resource, $model_attribute, $res_attribute, $model_class, $param1, $save)
	{
		$_model_class_name = '\\'.$model_class;

		$model->setAttribute($model_attribute,$this->mc->resourceToModel($resource->$res_attribute, new $_model_class_name, $save));

		if ($model->hasBelongsToRelation($model_attribute)) {
			$model->setAttributeForBelongsToRelation($model_attribute);
		}
	}

	public function jsonToResourceParse($object, $attribute, $data_class, $model_class)
	{
		return $object->$attribute;
	}
}
