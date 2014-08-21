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

class Records extends BaseFieldWidget
{
	public $form;
	public $element;
	public $edit = true;
	public $model;
	public $field;
	public $columns;
	public $no_items_text = 'No items have been entered.';
	public $add_button_text = 'Add item';
	public $validate_method;
	public $row_view;
	public $use_last_button_text = 'Use last item';
	public $headings = array('Date/time','Description');
	public $sort_table_after_save = false;
	public $include_timestamp = true;
	public $include_date = true;
	public $label = true;
	public $label_width = 3;

	public function init()
	{
		if (!$this->include_date) {
			$this->headings[0] = 'Time';
		}

		if (is_object($this->element) && $this->field) {
			if (is_object($this->element->{$this->field}) && $this->element->{$this->field} instanceof Measurement) {
				$this->value = $this->element->{$this->field}->getValue();
			} else {
				$this->value = $this->element->{$this->field};
			}
		}

		// if the widget has javascript, load it in
		if (file_exists("protected/widgets/js/".get_class($this).".js")) {
			$this->assetFolder = Yii::app()->getAssetManager()->publish('protected/widgets/js');
		}

		// Generate field ids
		foreach ($this->columns as &$column) {
			foreach($column['fields'] as &$field) {
				if (!isset($field['id'])) {
					$field['id'] = (is_object($this->element) ? CHtml::modelName($this->element).'_' : '').$field['field'];
				}
			}
		}
	}

	public function run()
	{
		$this->render(get_class($this));
	}
}
