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
	const TYPE_LIST = 0;
	const TYPE_REF = 1;
	const TYPE_OBJECT = 2;
	const TYPE_CONDITION = 3;

	/**
	 * @param BaseActiveRecord $model
	 * @return Resource
	 */
	public function modelToResource($model)
	{
		$resource = parent::modelToResource($model);

		$this->parseModelProperties($model, get_class($model), $resource);

		return $resource;
	}

	public function parseModelProperties($model, $model_class_name, &$resource)
	{
		if (!isset($this::$model_map[$model_class_name])) {
			throw new \Exception("Unknown object type: $model_class_name");
		}

		foreach ($this::$model_map[$model_class_name] as $key => $def) {
			if (is_array($def)) {
				switch ($def[0]) {
					case self::TYPE_LIST:
						$data_model = 'services\\'.$def[2];
						$data_list = $this->expandAttribute($model,$def[1]);
						$resource->$key = array_map(array('self','parseModelProperties'), $data_list, array_fill(0, count($data_list), $def[2]), array_fill(0, count($data_list), new $data_model(array())));
						break;
					case self::TYPE_REF:
						$resource->$key = \Yii::app()->service->$def[2]($model->{$def[1]});
						break;
					case self::TYPE_OBJECT:
						$object_model = 'services\\'.$def[2];
						$attribute = $this->expandAttribute($model,$def[1]);
						if (is_object($attribute)) {
							$resource->$key = $this->parseModelProperties($attribute, $def[2], new $object_model(array()));
						} else {
							$resource->$key = new $object_model($this->expandAttribute($model,$def[1]));
						}
						break;
					case self::TYPE_CONDITION:
						switch ($def[2]) {
							case 'equals':
								$resource->$key = $model->{$def[1]} == $def[3];
								break;
							default:
								throw new \Exception("Unhandled condition type: {$def[2]}");
						}
						break;
					default:
						throw new \Exception("Unknown declarative type: ".$def[0]);
				}
			} else {
				$resource->$key = $this->expandAttribute($model,$def);
			}
		}

		return $resource;
	}

	private function expandAttribute($model, $attribute)
	{
		if ($dot = strpos($attribute,'.')) {
			$relation = substr($attribute,0,$dot);
			$attribute = substr($attribute,$dot+1,strlen($attribute));

			return $model->$relation->$attribute;
		}

		return $model->$attribute;
	}

	/**
	 * @param string $json
	 * @return Resource
	 */
	public function jsonToResource($json)
	{
		$resource = parent::jsonToResource($json);

		$model = json_decode($json);

		return $this->parseJsonProperties($model, $this::$primary_model, $resource);
	}

	private function parseJsonProperties($model, $model_class_name, &$resource)
	{
		foreach ($this::$model_map[$model_class_name] as $key => $def) {
			if (is_array($def)) {
				switch ($def[0]) {
					case self::TYPE_LIST:
						$data_model = 'services\\'.$def[2];
						$resource->$key = array_map(array('self','parseJsonProperties'), $model->$key, array_fill(0, count($model->$key), $def[2]), array_fill(0, count($model->$key), new $data_model(array())));
						break;
					case self::TYPE_REF:
						$resource->$key = \Yii::app()->service->{$model->$key->service}($model->$key->id);
						break;
					case self::TYPE_OBJECT:
						$object_model = 'services\\'.$def[2];
						$resource->$key = $object_model::fromObject($model->$key);
						break;
					case self::TYPE_CONDITION:
						$resource->$key = $model->$key;
						break;
				}
			} else {
				$resource->$key = $model->$key;
			}
		}

		return $resource;
	}
}
