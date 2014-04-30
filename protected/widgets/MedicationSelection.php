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

class MedicationSelection extends BaseFieldWidget
{
	public $element;
	public $no_medications_text = 'No medications have been entered for this patient.';
	public $relation = 'medications';
	public $input_name = 'medications';
	public $placeholder = 'or search forumlary';
	public $medication = null;
	public $i;
	public $edit = true;

	public $layoutColumns = array(
		'label' => 2,
		'field' => 4,
	);

	public function init()
	{
		if (!$this->medication) {
			parent::init();
		}
	}

	public function run()
	{
		if ($this->medication) {
			$this->render('_MedicationSelection_medication_row',array(
					'_medication' => $this->medication,
					'_i' => $this->i,
					'_input_name' => $this->input_name,
					'_edit' => $this->edit,
				));
		} else {
			$this->render(get_class($this));
		}
	}
}
