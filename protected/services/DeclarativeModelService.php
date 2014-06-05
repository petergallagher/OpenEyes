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

class DeclarativeModelService extends ModelService
{
	/**
	 * @param BaseActiveRecord $model
	 * @return Resource
	 */
	public function modelToResource($model)
	{
		if (!isset($this::$resource_map[get_class($model)])) {
			throw new Exception("Unknown object type: ".get_class($model));
		}

		$class = static::getResourceClass();

		$resource = new $class(array('id' => $model->id, 'last_modified' => strtotime($model->last_modified_date)));

		$this->parseModelProperties($model, get_class($model), $resource);

		return $resource;
	}

	public function parseModelProperties($model, $model_map, &$resource)
	{
		foreach ($this::$resource_map[$model_map] as $key => $def) {
			if (is_string($def)) {
				$resource->$key = $model->$def;
			} else if (isset($def['relation']) && isset($def['field'])) {
				$relation = $def['relation'];
				$field = $def['field'];
				$resource->$key = $model->$relation->$field;
			} else if (isset($def['data_model'])) {
				if (isset($def['relation'])) {
					$relation = $def['relation'];
					$related_data = $model->$relation->$key;
				} else {
					$related_data = $model->$key;
				}

				$data_model = 'services\\'.$def['data_model'];

				switch($def['type']) {
					case 'list':
						$items = array();

						if ($related_data) {
							foreach ($related_data as $item) {
								$object = new $data_model(array());
								$items[] = $this->parseModelProperties($item, $def['data_model'], $object);
							}
						}

						$resource->$key = $items;
						break;
					case 'date':
						$resource->$key = new Date($model->{$def['field']});
						break;
					default:
						throw new Exception("Unknown data model type: {$def['type']}");
				}
			} else if (isset($def['reference'])) {
				$reference = $def['reference'];
				$name = $def['name'];
				$resource->$name = \Yii::app()->service->$reference($model->$key);
			} else if (isset($def['condition'])) {
				switch ($def['condition']) {
					case 'equals':
						$resource->$key = $model->{$def['field']} == $def['value'];
						break;
					default:
						throw new Exception("Unknown condition type: {$def['condition']}");
				}
			}
		}

		return $resource;
	}
}
