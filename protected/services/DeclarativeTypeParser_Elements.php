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

		$resource_items = array();

		foreach ($element_list as $element) {
			$data_class = $this->getServiceClassFromModelClass($element);

			$relations = $element->relations();

			if ($id = method_exists($element,'getId') ? $element->getId() : @$element->id) {
				$_element = new $data_class(array('id' => $id));
			} else {
				$_element = new $data_class;
			}

			$_element->_class_name = \CHtml::modelName($element);

			foreach ($_element->fields() as $field) {
				if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/',$element->$field)) {
					$_element->$field = new Date($element->$field);
				} else if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/',$element->$field)) {
					$_element->$field = new DateTime($element->$field);
				} else {
					$_element->$field = $element->$field;
				}
			}

			foreach ($_element->relations() as $relation) {
				if (!isset($relations[$relation])) {
					throw new \Exception("relation $relation is not defined on element class ".\CHtml::modelName($element));
				}

				switch ($relations[$relation][0]) {
					case 'CBelongsToRelation':
						$_element->$relation = $element->$relation ? $element->$relation->name : null;
						break;
					case 'CHasManyRelation':
						$list = array();

						$recur = false;
						$data_class = null;

						foreach ($element->$relation as $item) {
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

						if ($recur) {
							$_element->$relation = $this->modelToResourceParse($element, $relation, null);
						} else {
							$_element->$relation = $list;
						}
						break;
					default:
						throw new \Exception("Unhandled relation type: ".$relations[$relation][0]);
				}
			}

			foreach ($_element->relation_fields() as $relation => $fields) {
				if ($element->$relation) {
					foreach ($fields as $field) {
						$_element->$field = $element->$relation->$field;
					}
				}
			}

			foreach ($_element->references() as $field) {
				$reference_class = $relations[$field][1];
				$_element->{$field."_ref"} = \Yii::app()->service->$reference_class($element->{$field."_id"});
			}

			$data_items[] = $_element;
		}

		return $data_items;
	}

	public function resourceToModelParse(&$model, $resource, $model_attribute, $res_attribute, $param1, $model_class, $save)
	{
		$elements = array();

		if ($resource instanceof \stdClass) {
			// Convert json->stdClas object to a resource object
			$resource->$res_attribute = $this->jsonToResourceParse($resource, $res_attribute, null, null);
		}

		foreach ($resource->$res_attribute as $_element) {
			if ($_element instanceof ModelReference) {
				$model_class = $_element->getModelClass();
				$elements[] = $model_class::model()->findByPk($_element->getId());
				continue;
			}

			$data_class = $_element->_class_name;

			if ($_element->getId()) {
				if (!$element = $data_class::model()->findByPk($_element->getId())) {
					throw new \Exception("$data_class not found: ".$_element->getId());
				}
			} else {
				if ($data_class === null) {
					throw new \Exception("data_class cannot be null");
				}

				$element = new $data_class;
			}

			$this->resourceToModelParse_Fields($element, $_element);
			$this->resourceToModelParse_Relations($element, $_element, $resource, $res_attribute);
			$this->resourceToModelParse_RelationFields($element, $_element);
			$this->resourceToModelParse_References($element, $_element);

			$elements[] = $element;
		}

		if (\CHtml::modelName($model) == 'services_ModelConverter_ModelWrapper') {
			$model->setAttribute('_elements',$elements);
		}

		return $elements;
	}

	public function resourceToModelParse_Fields(&$model, &$element)
	{
		foreach ($element->fields() as $field) {
			if ($element->$field instanceof Date) {
				$model->$field = $element->$field->format('Y-m-d');
			} else if ($element->$field instanceof DateTime) {
				$model->$field = $element->$field->format('Y-m-d H:i:s');
			} else {
				$model->$field = $element->$field;
			}
		}
	}

	public function resourceToModelParse_Relations(&$model, &$element, $resource, $res_attribute)
	{
		$relations = $model->relations();

		foreach ($element->relations() as $index => $relation) {
			if (!isset($relations[$relation])) {
				throw new \Exception("relation $relation is not defined on element class ".\CHtml::modelName($model));
			}

			switch ($relations[$relation][0]) {
				case 'CBelongsToRelation':
					$related_class = $relations[$relation][1];
					$attribute = isset($relations[$relation]['order']) ? $relations[$relation]['order'] : 'name';
					$model->$relation = $element->$relation ? $related_class::model()->find($attribute.'=?',array($element->$relation)) : null;
					$model->{$relations[$relation][2]} = $model->$relation ? $model->$relation->primaryKey : null;
					break;
				case 'CHasManyRelation':
					$a = 1;
					$model->$relation = $resource->$res_attribute ? $this->resourceToModelParse($a, $element, null, $relation, null, null, null) : null;
					break;
				default:
					throw new \Exception("Unhandled relation type: ".$relations[$relation][0]);
			}
		}
	}

	public function resourceToModelParse_RelationFields(&$model, &$element)
	{
		$relations = $model->relations();

		foreach ($element->relation_fields() as $relation => $fields) {
			$class_name = $relations[$relation][1];
			$attribute = $relations[$relation][2];

			if (!$object = $model->$relation) {
				$object = new $class_name;
				$object->$attribute = $model->primaryKey;
			}

			foreach ($fields as $field) {
				$object->$field = $element->$field;
			}

			$model->$relation = $object;
		}
	}

	public function resourceToModelParse_References(&$model, &$element)
	{
		$relations = $model->relations();

		foreach ($element->references() as $field) {
			$reference_class = $relations[$field][1];

			if ($id = method_exists($element->{$field."_ref"},'getId') ? $element->{$field."_ref"}->getId() : @$element->{$field."_ref"}->id) {
				$model->$field = $reference_class::model()->findByPk($id);
			} else {
				$model->$field = null;
			}
			$model->{$field."_id"} = $model->$field ? $model->$field->primaryKey : null;
		}
	}

	public function resourceToModel_AfterSave($model, $resource)
	{
		$elements = $model->expandAttribute('_elements');

		foreach ($elements as $i => $element) {
			$element->event_id = $model->getId();

			$this->mc->saveModel($element);

			$relations = $element->relations();

			foreach ($resource->elements[$i]->relation_fields() as $relation => $fields) {
				$attribute = $relations[$relation][2];

				if ($object = $element->$relation) {
					$object->$attribute = $element->primaryKey;

					$this->mc->saveModel($object);
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

		if (@$object->id) {
			$_object = new $data_class(array('id' => $object->id));
		} else {
			$_object = new $data_class;
		}

		$_object->_class_name = $object->_class_name;

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
