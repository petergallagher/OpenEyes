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

class MeasurementBloodPressureWidget extends BaseMeasurementWidget
{
	public $field_systolic = 'blood_pressure_m_systolic';
	public $field_diastolic = 'blood_pressure_m_diastolic';
	public $name_systolic;
	public $name_diastolic;
	public $value_systolic = '';
	public $value_diastolic = '';

	public function init()
	{
		$this->name_systolic = CHtml::modelName($this->element).'['.$this->field_systolic.']';
		$this->name_diastolic = CHtml::modelName($this->element).'['.$this->field_diastolic.']';

		if ($this->element->{$this->field_systolic}) {
			$this->value_systolic = $this->element->{$this->field_systolic};
		}

		if ($this->element->{$this->field_diastolic}) {
			$this->value_diastolic = $this->element->{$this->field_diastolic};
		}

		// if the widget has javascript, load it in
		if (file_exists("protected/widgets/js/".get_class($this).".js")) {
			$this->assetFolder = Yii::app()->getAssetManager()->publish('protected/widgets/js');
		}
	}

	public function run()
	{
		$this->render(get_class($this));
	}
}
