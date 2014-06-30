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

class PatientMedicationsService extends DeclarativeModelService
{
	static protected $operations = array(self::OP_READ, self::OP_UPDATE, self::OP_CREATE, self::OP_SEARCH);

	static protected $search_params = array(
		'id' => self::TYPE_TOKEN,
	);

	static protected $primary_model = 'Patient';

	static protected $model_map = array(
		'Patient' => array(
			'fields' => array(
				'medications' => array(self::TYPE_LIST, 'medications', 'PatientMedication', 'Medication', array('patient_id' => 'primaryKey')),
				'previous_medications' => array(self::TYPE_LIST, 'previous_medications', 'PatientMedication', 'Medication', array('patient_id' => 'primaryKey')),
			),
		),
		'PatientMedication' => array(
			'ar_class' => 'Medication',
			'related_objects' => array(
				'patient' => array('patient_id', 'Patient', 'save' => 'no'),
			),
			'reference_objects' => array(
				'drug' => array('drug_id', 'Drug', array('name')),
				'route' => array('route_id', 'DrugRoute', array('name')),
				'option' => array('option_id', 'DrugRouteOption', array('name')),
				'frequency' => array('frequency_id', 'DrugFrequency', array('name')),
				'stop_reason' => array('stop_reason_id', 'MedicationStopReason', array('name')),
			),
			'fields' => array(
				'drug' => 'drug.name',
				'route' => 'route.name',
				'option' => 'option.name',
				'frequency' => 'frequency.name',
				'start_date' => array(self::TYPE_SIMPLEOBJECT, 'start_date', 'Date'),
				'end_date' => array(self::TYPE_SIMPLEOBJECT, 'end_date', 'Date'),
				'dose' => 'dose',
				'stop_reason' => 'stop_reason.name',
			),
		),
	);

	public function search(array $params)
	{
	}
}
