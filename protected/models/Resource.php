<?php
/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */
class Resource
{
	const BELONGS_TO = 0;
	const HAS_MANY = 1;

	public $id;
	public $created_user_id;
	public $created_user;
	public $created_date;
	public $last_modified_user_id;
	public $last_modified_user;
	public $last_modified_date;

	public function __construct($object)
	{
		$relations = array();

		foreach ($object->relations() as $relation) {
			if ($relation[0] == 'CBelongsToRelation') {
				$relations[$relation[2]] = $relation[1];
			}
		}

		foreach ($object->getAttributes() as $key => $value) {
			$this->$key = $value;

			if (preg_match('/_id$/',$key)) {
				$relation_name = substr($key,0,strlen($key)-3);

				if (isset($relations[$key])) {
					if ($value && $related_object = $relations[$key]::model()->findByPk($value)) {
						if (method_exists($related_object,'getResourceName')) {
							$this->{$relation_name} = $related_object->resourceName;
						} else if ($related_object->hasAttribute('name')) {
							$this->{$relation_name} = $related_object->name;
						}
					} else {
						$this->{$relation_name} = null;
					}
				}
			}
		}

		foreach ($this->relations() as $relation_name => $relation) {
			$model = $relation[1];
			$resource = $model.'Resource';

			switch ($relation[0]) {
				case self::BELONGS_TO:
					if ($related_object = $model::model()->findByPk($this->{$relation[2]})) {
						$this->{$relation_name} = new $resource($related_object);
					}
					break;
				case self::HAS_MANY:
					$this->{$relation_name} = array();

					foreach ($model::model()->findAll($relation[2].' = ?',array($object->primaryKey)) as $related_object) {
						$this->{$relation_name}[] = new $resource($related_object);
					}
					break;
			}
		}
	}

	public function relations()
	{
		return array();
	}

	public function __set($key, $value)
	{
		if (!isset($this->$key)) {
			throw new Exception("Invalid property for ".get_class($this).": $key");
		}

		parent::__set($key, $value);
	}
}
