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

class PatientMedications extends Resource
{
	public $medications;
	public $previous_medications;

	public function __construct($patient_id=null)
	{
		$params = array();

		if ($patient_id) {
			$params['patient_id'] = $patient_id;
		}

		parent::__construct($params);
	}

	public function addMedications($medications)
	{
		foreach ($medications as $medication) {
			if ($medication->isDiscontinued()) {
				if (!is_array($this->previous_medications)) {
					$this->previous_medications = array();
				}
				$this->previous_medications[] = $medication;
			} else {
				if (!is_array($this->medications)) {
					$this->medications = array();
				}
				$this->medications[] = $medication;
			}
		}
	}
}
