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

class DeclarativeTypeParser_Elements extends DeclarativeTypeParser
{
	public function modelToResourceParse($object, $attribute, $data_class, $param=null)
	{
		$element_list = DeclarativeTypeParser::expandObjectAttribute($object, $attribute);

		if (is_array($element_list)) {
			$data_items = array();

			foreach ($element_list as $model_element) {
				$data_items[] = $this->modelToResourceParse_ParseObject($model_element);
			}

			return $data_items;
		}

		return $this->modelToResourceParse_ParseObject($element_list);
	}

	public function modelToResourceParse_ParseObject($model_element)
	{
		$data_class = $this->getServiceClassFromModelClass($model_element);

		$relations = $model_element->relations();

		$resource_element = $this->createResourceObjectFromModel($data_class, $model_element);

		foreach ($resource_element->fields() as $field) {
			$resource_element->$field = $this->modelToResourceParse_FieldValue($model_element->$field);
		}

		foreach ($resource_element->lookup_relations() as $relation) {
			$resource_element->$relation = $model_element->$relation ? $model_element->$relation->name : null;
		}

		foreach ($resource_element->dataobject_relations() as $relation) {
			$resource_element->$relation = $this->modelToResourceParse_Relation($model_element, $relation, $relations);
		}

		$this->modelToResourceParse_RelationFields($resource_element, $model_element);
		$this->modelToResourceParse_References($resource_element, $model_element, $relations);

		foreach ($resource_element->references() as $field) {
			$reference_class = $relations[$field][1];
			$resource_element->{$field."_ref"} = $model_element->{$field."_id"} ? \Yii::app()->service->$reference_class($model_element->{$field."_id"}) : null;
		}

		return $resource_element;
	}

	public function modelToResourceParse_FieldValue($value)
	{
		if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/',$value)) {
			return new Date($value);
		} else if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/',$value)) {
			return new DateTime($value);
		}

		return $value;
	}

	public function modelToResourceParse_Relation($model_element, $relation, $relations)
	{
		if (!isset($relations[$relation])) {
			throw new \Exception("relation $relation is not defined on element class ".\CHtml::modelName($model_element));
		}

		switch ($relations[$relation][0]) {
			case 'CHasOneRelation':
				return $model_element->$relation ? $this->modelToResourceParse($model_element, $relation, null) : null;
			case 'CHasManyRelation':
				$list = array();

				$recur = false;
				$data_class = null;

				foreach ($model_element->$relation as $item) {
					if (!$data_class) {
						$data_class = $this->getServiceClassFromModelClass($item);

						$data_item = new $data_class;

						if ($data_item instanceof ElementDataObject) {
							$recur = true;
							break;
						}
					}

					$item_class = \CHtml::modelName($item);
					$list[] = \Yii::app()->service->$item_class($item->primaryKey);
				}

				return $recur
					?	$this->modelToResourceParse($model_element, $relation, null)
					: $list;

			default:
				throw new \Exception("Unhandled relation type: ".$relations[$relation][0]);
		}
	}

	public function modelToResourceParse_RelationFields(&$resource_element, $model_element)
	{
		foreach ($resource_element->relation_fields() as $relation => $fields) {
			if ($model_element->$relation) {
				foreach ($fields as $field) {
					$resource_element->$field = $this->modelToResourceParse_FieldValue($model_element->$relation->$field);
				}
			}
		}
	}

	public function modelToResourceParse_References(&$resource_element, $model_element, $relations)
	{
		foreach ($resource_element->references() as $field) {
			$reference_class = $relations[$field][1];
			$resource_element->{$field."_ref"} = \Yii::app()->service->$reference_class($model_element->{$field."_id"});
		}
	}

	public function resourceToModelParse(&$model, $resource, $model_attribute, $res_attribute, $param1, $model_class, $save)
	{
		$elements = array();

		if ($resource instanceof \stdClass) {
			// Convert json->stdClas object to a resource object
			$resource->$res_attribute = $this->jsonToResourceParse($resource, $res_attribute, null, null);
		}

		if (is_array($resource->$res_attribute)) {
			foreach ($resource->$res_attribute as $resource_element) {
				if ($resource_element instanceof ModelReference) {
					$model_class = $resource_element->getModelClass();
					$elements[] = $model_class::model()->findByPk($resource_element->getId());
					continue;
				}

				$elements[] = $this->resourceToModelParse_ParseObject($resource_element, $resource, $res_attribute);
			}

			if ($model instanceof ModelConverter_ModelWrapper) {
				$model->setAttribute('_elements',$elements);
			}

			return $elements;
		}

		return $this->resourceToModelParse_ParseObject($resource->$res_attribute, $resource, $res_attribute);
	}

	public function resourceToModelParse_ParseObject($resource_element, $resource, $res_attribute)
	{
		$data_class = $resource_element->_class_name;

		if ($resource_element->getId()) {
			if (!$model_element = $data_class::model()->findByPk($resource_element->getId())) {
				throw new \Exception("$data_class not found: ".$resource_element->getId());
			}
		} else {
			if ($data_class === null) {
				throw new \Exception("data_class cannot be null");
			}

			$model_element = new $data_class;
		}

		$relations = $model_element->relations();

		foreach ($resource_element->fields() as $field) {
			$model_element->$field = $this->resourceToModelParse_FieldValue($resource_element->$field);
		}

		foreach ($resource_element->lookup_relations() as $index => $relation) {
			$related_class = $relations[$relation][1];
			$attribute = isset($relations[$relation]['order']) ? $relations[$relation]['order'] : 'name';
			$model_element->$relation = $resource_element->$relation ? $related_class::model()->find($attribute.'=?',array($resource_element->$relation)) : null;
			$model_element->{$relations[$relation][2]} = $model_element->$relation ? $model_element->$relation->primaryKey : null;
		}

		$this->resourceToModelParse_Relations($model_element, $resource_element, $resource, $res_attribute, $relations);
		$this->resourceToModelParse_RelationFields($model_element, $resource_element);
		$this->resourceToModelParse_References($model_element, $resource_element);

		return $model_element;
	}

	public function resourceToModelParse_FieldValue($resource_value)
	{
		if ($resource_value instanceof Date) {
			return $resource_value->format('Y-m-d');
		} else if ($resource_value instanceof DateTime) {
			return $resource_value->format('Y-m-d H:i:s');
		}

		return $resource_value;
	}

	public function resourceToModelParse_Relations(&$model_element, $resource_element, $resource, $res_attribute, $relations)
	{
		foreach ($resource_element->dataobject_relations() as $index => $relation) {
			if (!isset($relations[$relation])) {
				throw new \Exception("relation $relation is not defined on element class ".\CHtml::modelName($model_element));
			}

			switch ($relations[$relation][0]) {
				case 'CHasOneRelation':
					$a = 1;
					$model_element->$relation = $resource_element->$relation ? $this->resourceToModelParse($a, $resource_element, null, $relation, null, null, null) : null;
					break;
				case 'CHasManyRelation':
					$a = 1;
					$model_element->$relation = $resource->$res_attribute ? $this->resourceToModelParse($a, $resource_element, null, $relation, null, null, null) : null;
					break;
				default:
					throw new \Exception("Unhandled relation type: ".$relations[$relation][0]);
			}
		}
	}

	public function resourceToModelParse_RelationFields(&$model_element, $resource_element)
	{
		$relations = $model_element->relations();

		foreach ($resource_element->relation_fields() as $relation => $fields) {
			$class_name = $relations[$relation][1];
			$attribute = $relations[$relation][2];

			if (!$object = $model_element->$relation) {
				$object = new $class_name;
				$object->$attribute = $model_element->primaryKey;
			}

			foreach ($fields as $field) {
				$object->$field = $this->resourceToModelParse_FieldValue($resource_element->$field);
			}

			$model_element->$relation = $object;
		}
	}

	public function resourceToModelParse_References(&$model_element, $resource_element)
	{
		$relations = $model_element->relations();

		foreach ($resource_element->references() as $field) {
			$reference_class = $relations[$field][1];

			if ($id = method_exists($resource_element->{$field."_ref"},'getId') ? $resource_element->{$field."_ref"}->getId() : @$resource_element->{$field."_ref"}->id) {
				$model_element->$field = $reference_class::model()->findByPk($id);
			} else {
				$model_element->$field = null;
			}
			$model_element->{$field."_id"} = $model_element->$field ? $model_element->$field->primaryKey : null;
		}
	}

	public function resourceToModel_AfterSave(&$model, $resource)
	{
		$elements = $model->expandAttribute('_elements');

		foreach ($elements as $i => $element) {
			$element->event_id = $model->getId();

			$this->mc->saveModel($element);

			if (method_exists($this->mc->service,'resourceToModel_AfterSave_'.\CHtml::modelName($element))) {
				$this->mc->service->{"resourceToModel_AfterSave_".\CHtml::modelName($element)}($element);
			}

			$relations = $element->relations();

			foreach ($resource->elements[$i]->relation_fields() as $relation => $fields) {
				$attribute = $relations[$relation][2];

				if ($object = $element->$relation) {
					$object->$attribute = $element->primaryKey;

					$this->mc->saveModel($object);
				}
			}

			foreach ($resource->elements[$i]->dataobject_relations() as $relation) {
				if ($relations[$relation][0] == 'CHasOneRelation') {
					$attribute = $relations[$relation][2];

					if ($object = $element->$relation) {
						$object->$attribute = $element->primaryKey;

						$this->mc->saveModel($object);
					}
				}
			}
		}
	}

	public function jsonToResourceParse($object, $attribute, $data_class, $model_class)
	{
		$element_list = DeclarativeTypeParser::expandObjectAttribute($object, $attribute);

		$resource_items = array();

		foreach ($element_list as $element) {
			$resource_items[] = $this->jsonToResourceParse_TranslateObject($element);
		}

		return $resource_items;
	}

	protected function jsonToResourceParse_TranslateObject($object)
	{
		$data_class = $this->getServiceClassFromModelClass($object->_class_name);

		$_object = $this->createResourceObjectFromModel($data_class, $object);

		foreach ($object as $key => $value) {
			if (in_array($key,array('id','_class_name'))) continue;

			if (preg_match('/_ref$/',$key)) {
				$_object->$key = $value ? \Yii::app()->service->{$value->service}($value->id) : null;
			} else if ($value instanceof \stdClass) {
				if (array_keys((array)$value) == array('date','timezone_type','timezone')) {
					$timezone = new \DateTimeZone($value->timezone);
					if (strlen($value->date) == 10) {
						$_object->$key = new Date($value->date,$timezone);
					} else {
						$_object->$key = new DateTime($value->date,$timezone);
					}
				} else {
					$_object->$key = $this->jsonToResourceParse_TranslateObject($value);
				}
			} else if (is_array($value)) {
				$_child_items = array();

				foreach ($value as $_child_object) {
					if (array_keys((array)$_child_object) == array('service','id')) {
						$_child_items[] = \Yii::app()->service->{$_child_object->service}($_child_object->id);
					} else {
						$_child_items[] = $this->jsonToResourceParse_TranslateObject($_child_object);
					}
				}

				$_object->$key = $_child_items;
			} else {
				$_object->$key = $value;
			}
		}

		return $_object;
	}
}
