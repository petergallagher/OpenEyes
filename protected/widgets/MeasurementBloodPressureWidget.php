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
	public $field = 'blood_pressure_m';
	public $bp_systolic;
	public $bp_diastolic;
	public $class_systolic;
	public $class_diastolic;

	public function init()
	{
		$this->suffix = MeasurementBloodPressure::model()->suffix;

		if ($this->element) {
			if ($this->element->{$this->field}) {
				$this->bp_systolic = $this->element->{$this->field}->bp_systolic;
			}

			if ($this->element->{$this->field}) {
				$this->bp_diastolic = $this->element->{$this->field}->bp_diastolic;
			}
		}

		if (@$this->htmlOptions['class']) {
			$this->class_systolic = $this->htmlOptions['class'].' bpSystolic';
			$this->class_diastolic = $this->htmlOptions['class'].' bpDiastolic';
		} else {
			$this->class_systolic = 'bpSystolic';
			$this->class_diastolic = 'bpDiastolic';
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
