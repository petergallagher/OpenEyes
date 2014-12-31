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

class DisorderTree extends BaseActiveRecordVersioned
{
	public function tableName()
	{
		return 'disorder_tree';
	}

	public function rules()
	{
		return array(
			array('parent_id, disorder_id', 'safe'),
			array('disorder_id', 'required'),
		);
	}

	public function relations()
	{
		return array(
			'disorder' => array(self::BELONGS_TO, 'Disorder', 'disorder_id'),
			'children' => array(self::HAS_MANY, 'DisorderTree', 'parent_id'),
		);
	}

	public function attributeLabels()
	{
		return array(
			'parent_id' => 'Parent',
			'disorder_id' => 'Disorder',
		);
	}

	public function delete()
	{
		foreach ($this->children as $child) {
			if (!$child->delete()) {
				throw new Exception("Unable to delete disorder tree item: ".print_r($child->errors,true));
			}
		}

		return parent::delete();
	}
}
