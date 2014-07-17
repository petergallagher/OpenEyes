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

class ModuleTestCase extends \CDbTestCase
{
	public function compareResourceFields($resource, $model, $fields)
	{
		foreach ($fields as $field) {
			if (is_object($model->$field)) {
				$this->assertEquals($model->$field->name,$resource->$field);
			} else if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/',$model->$field)) {
				$this->assertInstanceOf('services\DateTime',$resource->$field);
				$this->assertEquals($model->$field,$resource->$field->toModelValue());
			} else if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/',$model->$field)) {
				$this->assertInstanceOf('services\Date',$resource->$field);
				$this->assertEquals($model->$field,$resource->$field->toModelValue());
			} else if (is_null($model->$field)) {
				$this->assertNull($resource->$field);
			} else {
				$this->assertEquals($model->$field,$resource->$field);
			}
		}
	}

	public function compareModelFields($model, $resource, $fields)
	{
		foreach ($fields as $field) {
			if ($resource->$field instanceof Date || $resource->$field instanceof DateTime) {
				$this->assertEquals($resource->$field->toModelValue(),$model->$field);
			} else if (is_object($model->$field)) {
				$this->assertEquals($resource->$field,$model->$field->name);
			} else if (is_null($resource->$field)) {
				$this->assertNull($model->$field);
			} else {
				$this->assertEquals($resource->$field,$model->$field);
			}
		}
	}

	public function getReferenceClassName($class_name)
	{
		if (preg_match('/^([A-Z][a-z]{2}[A-Z][a-z][A-Z][a-z]+)_/',$class_name,$m) ||
				preg_match('/^Element_([A-Z][a-z]{2}[A-Z][a-z][A-Z][a-z]+)_/',$class_name,$m)) {
			if (\Yii::app()->db->createCommand()->select("id")->from('event_type')->where("class_name = :class_name",array(":class_name" => $m[1]))->queryRow()) {
				return "OEModule\\{$m[1]}\\services\\{$class_name}Reference";
			}
		}

		return "services\\{$class_name}Reference";
	}

	public function compareResourceReferences($resource, $model, $references)
	{
		$relations = $model->relations();

		foreach ($references as $relation) {
			if (is_null($model->{$relation."_id"})) {
				$this->assertNull($resource->{$relation."_ref"});
			} else {
				$class = $this->getReferenceClassName($relations[$relation][1]);

				$this->assertInstanceOf($this->getReferenceClassName($relations[$relation][1]),$resource->{$relation."_ref"});
				$this->assertEquals($model->{$relation."_id"},$resource->{$relation."_ref"}->getId());
			}
		}
	}

	public function compareModelReferences($model, $resource, $references)
	{
		$relations = $model->relations();

		foreach ($references as $relation) {
			if (is_null($resource->{$relation."_ref"})) {
				$this->assertNull($model->{$relation."_id"});
			} else {
				$this->assertEquals($resource->{$relation."_ref"}->getId(),$model->{$relation."_id"});
				$this->assertEquals($resource->{$relation."_ref"}->getId(),$model->{$relation}->id);
			}
		}
	}
}
