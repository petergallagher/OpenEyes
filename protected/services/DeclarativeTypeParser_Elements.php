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
	public function modelToResourceParse($object, $attribute, $module_class, $param=null)
	{
		$element_list = DeclarativeTypeParser::expandObjectAttribute($object, $attribute);

		$resource_items = array();

		foreach ($element_list as $element) {
			if (!$module_class) {
				$module_class = $element->event->eventType->class_name;
			}

			if (strstr(\CHtml::modelName($element),$module_class)) {
				$data_class = 'OEModule\\'.$module_class.'\\services\\'.\CHtml::modelName($element);
			} else {
				$data_class = '\\services\\'.\CHtml::modelName($element);
			}

			$relations = $element->relations();

			$_element = new $data_class;
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
								if (strstr(\CHtml::modelName($item),$module_class)) {
									$data_class = 'OEModule\\'.$module_class.'\\services\\'.\CHtml::modelName($item);
								} else {
									$data_class = '\\services\\'.\CHtml::modelName($item);
								}

								$data_item = new $data_class;

								if ($data_item instanceof ElementDataObject) {
									$recur = true;
									break;
								}
							}

							//if ($recur) break;

							$item_class = \CHtml::modelName($item);
							$list[] = \Yii::app()->service->$item_class($item->primaryKey);
						}

						if ($recur) {
							$_element->$relation = $this->modelToResourceParse($element, $relation, $module_class);
						} else {
							$_element->$relation = $list;
						}
						break;
					default:
						throw new \Exception("Unhandled relation type: ".$relations[$relation][0]);
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

		foreach ($resource->$res_attribute as $_element) {
			if ($_element instanceof ModelReference) {
				$model_class = $_element->getModelClass();
				$elements[] = $model_class::model()->findByPk($_element->getId());
				continue;
			}

			$data_class = $_element->_class_name;

			$element = new $data_class;

			$relations = $element->relations();

			foreach ($_element->fields() as $field) {
				if ($_element->$field instanceof Date) {
					$element->$field = $_element->$field->format('Y-m-d');
				} else if ($_element->$field instanceof DateTime) {
					$element->$field = $_element->$field->format('Y-m-d H:i:s');
				} else {
					$element->$field = $_element->$field;
				}
			}

			foreach ($_element->relations() as $index => $relation) {
				if (!isset($relations[$relation])) {
					throw new \Exception("relation $relation is not defined on element class ".\CHtml::modelName($element));
				}

				switch ($relations[$relation][0]) {
					case 'CBelongsToRelation':
						$related_class = $relations[$relation][1];
						$attribute = isset($relations[$relation]['order']) ? $relations[$relation]['order'] : 'name';
						$element->$relation = $_element->$relation ? $related_class::model()->find($attribute.'=?',array($_element->$relation)) : null;
						$element->{$relations[$relation][2]} = $element->$relation ? $element->$relation->primaryKey : null;
						break;
					case 'CHasManyRelation':
						$a = 1;
						$element->$relation = $resource->$res_attribute ? $this->resourceToModelParse($a, $_element, null, $relation, null, null, null) : null;
						break;
					default:
						throw new \Exception("Unhandled relation type: ".$relations[$relation][0]);
				}
			}

			foreach ($_element->references() as $field) {
				$reference_class = $relations[$field][1];

				$element->$field = $_element->{$field."_ref"} ? $reference_class::model()->findByPk($_element->{$field."_ref"}->getId()) : null;
				$element->{$field."_id"} = $element->$field ? $element->$field->primaryKey : null;
			}

			$elements[] = $element;
		}

		if (\CHtml::modelName($model) == 'services_ModelConverter_ModelWrapper') {
			$model->setAttribute('_elements',$elements);
		}

		return $elements;
	}

	public function resourceToModel_AfterSave($model)
	{
		$elements = $model->expandAttribute('_elements');

		foreach ($elements as $element) {
			$element->event_id = $model->getId();

			$this->mc->saveModel($element);
		}
	}

	public function jsonToResourceParse($object, $attribute, $data_class, $model_class)
	{
		/*
		$data_class = 'services\\'.$data_class;

		$data_items = array();

		foreach ($object->$attribute as $data_item) {
			$data_items[] = $this->mc->jsonToResourceParse($data_item, $model_class, new $data_class);
		}

		return $data_items;
		*/
	}

/*
	public function saveListItem($item)
	{
		if ($related_objects = $this->mc->service->map->getRelatedObjectsForClass(get_class($item))) {
			foreach ($related_objects as $relation => $def) {
				if (@$def['save'] != 'no') {
					if ($item->$relation) {
						foreach ($this->mc->service->map->getRelatedObjectRelatedObjectsForClass(get_class($item), $relation) as $related_def) {
							$this->mc->saveModel($item->$relation->{$related_def[1]});
							$item->$relation->{$related_def[0]} = $item->$relation->{$related_def[1]}->primaryKey;
						}

						$this->mc->saveModel($item->$relation);

						$parent_attribute = preg_replace('/^.*\./','',$def[0]);
						$item->$parent_attribute = $item->$relation->primaryKey;
					}
				}
			}
		}

		$this->mc->saveModel($item);
	}
	*/
}
