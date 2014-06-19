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

class ModelConverter_ModelWrapper
{
	protected $map;
	protected $model;
	protected $related_objects = array();
	protected $reference_object_attributes = array();
	protected $conditional_attributes = array();

	public function __construct($map, $model, $extra_fields=null)
	{
		$this->map = $map;
		$this->model = $model;

		is_array($extra_fields) && $this->setAttributes($extra_fields);

		$this->related_object_definitions = $this->map->getRelatedObjectsForClass($this->getClass());
	}

	public function getId()
	{
		return $this->model->id;
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

	public function getRelatedObjectDefinitions()
	{
		return $this->related_object_definitions ? $this->related_object_definitions : array();
	}

	public function isRelatedObject($relation_name)
	{
		return isset($this->related_object_definitions[$relation_name]);
	}

	public function save()
	{
		if (!$this->model->save()) {
			throw new ValidationFailure("Validation failure on " . $this->class.": ".print_r($this->model->errors,true), $this->model->errors);
		}
	}

	public function hasConditionalAttribute($attribute)
	{
		return in_array($attribute, $this->conditional_attributes);
	}

	public function addConditionalAttribute($attribute)
	{
		$this->conditional_attributes[] = $attribute;
	}

	public function setRelatedObject($related_object_one, $related_object_two, $value)
	{
		$this->related_objects[$related_object_one][$related_object_two] = $value;
	}

	public function getRelatedObject($related_object_one, $related_object_two)
	{
		return $this->related_objects[$related_object_one][$related_object_two];
	}

	public function addToRelatedObjectArray($related_object_one, $related_object_two, $item)
	{
		$this->related_objects[$related_object_one][$related_object_two][] = $item;
	}

	public function relatedObjectCopyAttributeFromModel($related_object_one, $related_object_two, $attribute)
	{
		if ($this->related_objects[$related_object_one][$related_object_two]) {
			if (is_array($this->related_objects[$related_object_one][$related_object_two])) {
				foreach ($this->related_objects[$related_object_one][$related_object_two] as $i => $item) {
					if (is_array($attribute)) {
						foreach ($attribute as $key => $value) {
							$this->related_objects[$related_object_one][$related_object_two][$i]->$key= $this->expandAttribute($value);
						}
					} else {
						$this->related_objects[$related_object_one][$related_object_two][$i]->$attribute = $this->expandAttribute($attribute);
					}
				}
			} else {
				$this->related_objects[$related_object_one][$related_object_two]->$attribute = $this->expandAttribute($attribute);
			}
		}
	}

	public function expandAttribute($attributes)
	{
		return DeclarativeTypeParser::expandObjectAttribute($this->model, $attributes);
	}

	public function setAttribute($attributes, $value, $force=true)
	{
		return DeclarativeTypeParser::setObjectAttribute($this->model, $attributes, $value, $force);
	}

	public function setAttributes($attributes)
	{
		return DeclarativeTypeParser::setObjectAttributes($this->model, $attributes);
	}

	public function addReferenceObjectAttribute($relation_name, $attribute, $value)
	{
		$this->reference_object_attributes[$relation_name][$attribute] = $value;
	}

	public function haveAllKeysForReferenceObject($relation_name)
	{
		list($reference_key, $reference_class, $required_keys) = $this->map->getReferenceObjectForClass($this->getClass(), $relation_name);

		foreach ($required_keys as $key) {
			if (!@$this->reference_object_attributes[$relation_name][$key]) {
				return false;
			}
		}

		return true;
	}

	public function associateReferenceObjectWithModel($relation_name)
	{
		list($reference_key, $reference_class, $required_keys) = $this->map->getReferenceObjectForClass($this->getClass(), $relation_name);

		$criteria = new \CDbCriteria;

		foreach ($this->reference_object_attributes[$relation_name] as $key => $value) {
			$criteria->compare($key, $value);
		}

		$reference_class = '\\'.$reference_class;

		if (!$related_object = $reference_class::model()->find($criteria)) {
			$related_object = new $reference_class;

			$this->setObjectAttributes($related_object, $this->reference_object_attributes[$relation_name]);
		}

		$this->setAttribute($reference_key, $related_object->primaryKey);
		$this->setAttribute($relation_name, $related_object);

		return $related_object;
	}

	protected function setObjectAttributes(&$object, $attributes)
	{
		foreach ($attributes as $key => $value) {
			$object->$key = $value;
		}
	}

	public function hasBelongsToRelation($relation_name)
	{
		$relations = $this->getRelations();
		return isset($relations[$relation_name]);
	}

	public function setAttributeForBelongsToRelation($relation_name)
	{
		$relations = $this->getRelations();

		$this->setAttribute($relations[$relation_name][2], $this->model->$relation_name->id);
	}

	public function setReferenceListForRelation($relation_name, $model_assignment_field, $ref_list)
	{
		$relations = $this->getRelations();

		list($junk, $assignment_model, $assignment_field) = $relations[$relation_name];

		$assignments = array();

		foreach ($ref_list as $ref) {
			$assignment = new $assignment_model;
			$assignment->$assignment_field = $this->getId();
			$assignment->$model_assignment_field = $ref->getId();

			$assignments[] = $assignment;
		}

		$this->setAttribute($relation_name, $assignments);
	}
}
