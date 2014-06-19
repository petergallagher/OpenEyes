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

class ModelConverter_Model
{
	protected $model;

	public function __construct($model, $extra_fields=null)
	{
		$this->model = $model;

		is_array($extra_fields) && $this->setAttributes($extra_fields);
	}

	public function getClass()
	{
		return \CHtml::modelName($this->model);
	}

	public function getRelations()
	{
		return $this->model->relations();
	}

	public function getModel()
	{
		return $this->model;
	}

	public function save()
	{
		if (!$this->model->save()) {
			throw new ValidationFailure("Validation failure on " . $this->class.": ".print_r($this->model->errors,true), $this->model->errors);
		}
	}

	public function expandAttribute($attributes, &$object=null)
	{
		if (!is_array($attributes)) {
			$attributes = explode('.',$attributes);
		}

		if (is_null($object)) {
			$object = &$this->model;
		}

		$attribute = array_shift($attributes);

		if (count($attributes) >0) {
			if (!$object->$attribute) {
				return false;
			}

			return $this->expandAttribute($attributes, $object->$attribute);
		}

		return $object->$attribute;
	}

	public function setAttribute($attributes, $value, $force=true, &$object=null)
	{
		if (!is_array($attributes)) {
			$attributes = explode('.',$attributes);
		}

		if (is_null($object)) {
			$object = &$this->model;
		}

		$attribute = array_shift($attributes);

		if (count($attributes) >0) {
			if (!$object->$attribute) {
				return false;
			}

			return $this->setAttribute($attributes, $value, $force, $object->$attribute);
		}

		if ($force || !$object->$attribute) {
			$object->$attribute = $value;
		}
	}

	public function setAttributes($attributes)
	{
		foreach ($attributes as $key => $value) {
			$this->setAttribute($key, $value);
		}
	}
}
