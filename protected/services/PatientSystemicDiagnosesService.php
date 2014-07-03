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

class PatientSystemicDiagnosesService extends DeclarativeModelService
{
	static protected $operations = array(self::OP_READ, self::OP_UPDATE, self::OP_CREATE, self::OP_SEARCH);

	static protected $search_params = array(
		'id' => self::TYPE_TOKEN,
	);

	static protected $primary_model = 'Patient';

	static protected $model_map = array(
		'Patient' => array(
			'fields' => array(
				'diagnoses' => array(self::TYPE_LIST, 'systemicDiagnoses', 'PatientDiagnosis', 'SecondaryDiagnosis', array('patient_id' => 'primaryKey')),
			),
		),
		'PatientDiagnosis' => array(
			'ar_class' => 'SecondaryDiagnosis',
			'related_objects' => array(
				'patient' => array('patient_id', 'Patient', 'save' => 'no'),
			),
			'reference_objects' => array(
				'eye' => array('eye_id', 'Eye', array('name')),
				'disorder' => array('disorder_id', 'Disorder', array('term')),
			),
			'fields' => array(
				'disorder' => 'disorder.term',
				'side' => 'eye.name',
			),
		),
	);

	public function search(array $params)
	{
	}

	public function resourceToModel($resource, $model, $save=true)
	{
		if (!empty($resource->diagnoses)) {
			foreach ($resource->diagnoses as $diagnosis) {
				if ($disorder = \Disorder::model()->find('term=?',array($diagnosis->disorder))) {
					if ($disorder->specialty_id) {
						throw new \Exception('PatientSystemicDiagnoses passed a resource containing ophthalmic diagnoses');
					}
				}
			}
		}

		return parent::resourceToModel($resource, $model, $save);
	}

	public function jsonToModel($json, $model, $save=true)
	{
		if (!$object = @json_decode($json)) {
			throw new \Exception('Invalid JSON encountered in jsonToModel');
		}

		if (!empty($object->diagnoses)) {
			foreach ($object->diagnoses as $diagnosis) {
				if ($disorder = \Disorder::model()->find('term=?',array($diagnosis->disorder))) {
					if ($disorder->specialty_id) {
						throw new \Exception('PatientSystemicDiagnoses passed a resource containing ophthalmic diagnoses');
					}
				}
			}
		}

		return parent::jsonToModel($json, $model, $save);
	}
}
