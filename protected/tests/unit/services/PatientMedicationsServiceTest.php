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

class PatientMedicationsServiceTest extends \CDbTestCase
{
	public $fixtures = array(
		'patients' => 'Patient',
		'medications' => 'Medication',
		'drugs' => 'Drug',
		'routes' => 'DrugRoute',
		'options' => 'DrugRouteOption',
		'frequencies' => 'DrugFrequency',
		'stop_reasons' => 'MedicationStopReason',
	);

	public function testModelToResource()
	{
		$patient = $this->patients('patient1');

		$ps = new PatientMedicationsService;

		$resource = $ps->modelToResource($patient);

		$this->assertInstanceOf('services\PatientMedications',$resource);
		$this->assertCount(1,$resource->medications);

		$this->assertInstanceOf('services\PatientMedication',$resource->medications[0]);
		$this->assertEquals('Abidec drops',$resource->medications[0]->drug);
		$this->assertEquals('IM',$resource->medications[0]->route);
		$this->assertEquals('Left',$resource->medications[0]->option);
		$this->assertEquals('bd',$resource->medications[0]->frequency);
		$this->assertEquals('loads',$resource->medications[0]->dose);
		$this->assertFalse($resource->medications[0]->stop_reason);
		$this->assertInstanceOf('services\Date',$resource->medications[0]->start_date);
		$this->assertNull($resource->medications[0]->end_date);

		$this->assertCount(1,$resource->previous_medications);

		$this->assertInstanceOf('services\PatientMedication',$resource->previous_medications[0]);
		$this->assertEquals('Acetazolamide 250mg tablets',$resource->previous_medications[0]->drug);
		$this->assertEquals('Eye',$resource->previous_medications[0]->route);
		$this->assertEquals('Right',$resource->previous_medications[0]->option);
		$this->assertEquals('2 hourly',$resource->previous_medications[0]->frequency);
		$this->assertEquals('much',$resource->previous_medications[0]->dose);
		$this->assertEquals('Started seeing halos and auras',$resource->previous_medications[0]->stop_reason);
		$this->assertInstanceOf('services\Date',$resource->previous_medications[0]->start_date);
		$this->assertInstanceOf('services\Date',$resource->previous_medications[0]->end_date);
	}

	public function getResource()
	{
		$resource = new PatientMedications(1);

		$drug1 = new PatientMedication;
		$drug1->drug = 'Acetazolamide 250mg modified release capsules';
		$drug1->route = 'Nose';
		$drug1->option = 'Right';
		$drug1->frequency = 'hourly';
		$drug1->start_date = new Date('2012-01-01');
		$drug1->end_date = new Date('2012-01-30');
		$drug1->stop_reason = 'Believed themself to be god';
		$drug1->dose = 'likesomuch';

		$drug2 = new PatientMedication;
		$drug2->drug = 'Acetazolamide 250mg modified release capsules';
		$drug2->route = 'Ocular muscle';
		$drug2->option = 'Left';
		$drug2->frequency = 'od';
		$drug2->start_date = new Date('2013-04-04');
		$drug2->end_date = null;
		$drug2->dose = 'one handful';

		$resource->addMedications(array($drug1,$drug2));

		return $resource;
	}

	public function testResourceToModel_NoSave_NoNewRecords()
	{
		$resource = $this->getResource();

		$total_medications = count(\Medication::model()->findAll());

		$ps = new PatientMedicationsService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'), false);

		$this->assertEquals($total_medications, count(\Medication::model()->findAll()));
	}

	public function testResourceToModel_NoSave_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientMedicationsService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient4'), false);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(1,$patient->previous_medications);

		$this->assertInstanceOf('Medication',$patient->previous_medications[0]);
		$this->assertInstanceOf('Drug',$patient->previous_medications[0]->drug);
		$this->assertEquals('Acetazolamide 250mg modified release capsules',$patient->previous_medications[0]->drug->name);
		$this->assertInstanceOf('DrugRoute',$patient->previous_medications[0]->route);
		$this->assertEquals('Nose',$patient->previous_medications[0]->route->name);
		$this->assertInstanceOf('DrugRouteOption',$patient->previous_medications[0]->option);
		$this->assertEquals('Right',$patient->previous_medications[0]->option->name);
		$this->assertInstanceOf('DrugFrequency',$patient->previous_medications[0]->frequency);
		$this->assertEquals('hourly',$patient->previous_medications[0]->frequency->name);
		$this->assertEquals('likesomuch',$patient->previous_medications[0]->dose);
		$this->assertInstanceOf('MedicationStopReason',$patient->previous_medications[0]->stop_reason);
		$this->assertEquals('Believed themself to be god',$patient->previous_medications[0]->stop_reason->name);
		$this->assertEquals('2012-01-01',$patient->previous_medications[0]->start_date);
		$this->assertEquals('2012-01-30',$patient->previous_medications[0]->end_date);

		$this->assertCount(1,$patient->medications);

		$this->assertInstanceOf('Medication',$patient->medications[0]);
		$this->assertInstanceOf('Drug',$patient->medications[0]->drug);
		$this->assertEquals('Acetazolamide 250mg modified release capsules',$patient->medications[0]->drug->name);
		$this->assertInstanceOf('DrugRoute',$patient->medications[0]->route);
		$this->assertEquals('Ocular muscle',$patient->medications[0]->route->name);
		$this->assertInstanceOf('DrugRouteOption',$patient->medications[0]->option);
		$this->assertEquals('Left',$patient->medications[0]->option->name);
		$this->assertInstanceOf('DrugFrequency',$patient->medications[0]->frequency);
		$this->assertEquals('od',$patient->medications[0]->frequency->name);
		$this->assertEquals('one handful',$patient->medications[0]->dose);
		$this->assertNull($patient->medications[0]->stop_reason);
		$this->assertEquals('2013-04-04',$patient->medications[0]->start_date);
		$this->assertNull($patient->medications[0]->end_date);
	}

	public function testResourceToModel_Save_Create_ModelCountsCorrect()
	{
		$resource = $this->getResource();

		$total_medications = count(\Medication::model()->findAll());

		$ps = new PatientMedicationsService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient4'));

		$this->assertEquals($total_medications+2, count(\Medication::model()->findAll()));
	}

	public function testResourceToModel_Save_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientMedicationsService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient4'));

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(1,$patient->previous_medications);

		$this->assertInstanceOf('Medication',$patient->previous_medications[0]);
		$this->assertInstanceOf('Drug',$patient->previous_medications[0]->drug);
		$this->assertEquals('Acetazolamide 250mg modified release capsules',$patient->previous_medications[0]->drug->name);
		$this->assertInstanceOf('DrugRoute',$patient->previous_medications[0]->route);
		$this->assertEquals('Nose',$patient->previous_medications[0]->route->name);
		$this->assertInstanceOf('DrugRouteOption',$patient->previous_medications[0]->option);
		$this->assertEquals('Right',$patient->previous_medications[0]->option->name);
		$this->assertInstanceOf('DrugFrequency',$patient->previous_medications[0]->frequency);
		$this->assertEquals('hourly',$patient->previous_medications[0]->frequency->name);
		$this->assertEquals('likesomuch',$patient->previous_medications[0]->dose);
		$this->assertInstanceOf('MedicationStopReason',$patient->previous_medications[0]->stop_reason);
		$this->assertEquals('Believed themself to be god',$patient->previous_medications[0]->stop_reason->name);
		$this->assertEquals('2012-01-01',$patient->previous_medications[0]->start_date);
		$this->assertEquals('2012-01-30',$patient->previous_medications[0]->end_date);

		$this->assertCount(1,$patient->medications);

		$this->assertInstanceOf('Medication',$patient->medications[0]);
		$this->assertInstanceOf('Drug',$patient->medications[0]->drug);
		$this->assertEquals('Acetazolamide 250mg modified release capsules',$patient->medications[0]->drug->name);
		$this->assertInstanceOf('DrugRoute',$patient->medications[0]->route);
		$this->assertEquals('Ocular muscle',$patient->medications[0]->route->name);
		$this->assertInstanceOf('DrugRouteOption',$patient->medications[0]->option);
		$this->assertEquals('Left',$patient->medications[0]->option->name);
		$this->assertInstanceOf('DrugFrequency',$patient->medications[0]->frequency);
		$this->assertEquals('od',$patient->medications[0]->frequency->name);
		$this->assertEquals('one handful',$patient->medications[0]->dose);
		$this->assertNull($patient->medications[0]->stop_reason);
		$this->assertEquals('2013-04-04',$patient->medications[0]->start_date);
		$this->assertNull($patient->medications[0]->end_date);
	}

	public function testResourceToModel_Save_Create_DBIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientMedicationsService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient4'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(1,$patient->previous_medications);

		$this->assertInstanceOf('Medication',$patient->previous_medications[0]);
		$this->assertInstanceOf('Drug',$patient->previous_medications[0]->drug);
		$this->assertEquals('Acetazolamide 250mg modified release capsules',$patient->previous_medications[0]->drug->name);
		$this->assertInstanceOf('DrugRoute',$patient->previous_medications[0]->route);
		$this->assertEquals('Nose',$patient->previous_medications[0]->route->name);
		$this->assertInstanceOf('DrugRouteOption',$patient->previous_medications[0]->option);
		$this->assertEquals('Right',$patient->previous_medications[0]->option->name);
		$this->assertInstanceOf('DrugFrequency',$patient->previous_medications[0]->frequency);
		$this->assertEquals('hourly',$patient->previous_medications[0]->frequency->name);
		$this->assertEquals('likesomuch',$patient->previous_medications[0]->dose);
		$this->assertInstanceOf('MedicationStopReason',$patient->previous_medications[0]->stop_reason);
		$this->assertEquals('Believed themself to be god',$patient->previous_medications[0]->stop_reason->name);
		$this->assertEquals('2012-01-01',$patient->previous_medications[0]->start_date);
		$this->assertEquals('2012-01-30',$patient->previous_medications[0]->end_date);

		$this->assertCount(1,$patient->medications);

		$this->assertInstanceOf('Medication',$patient->medications[0]);
		$this->assertInstanceOf('Drug',$patient->medications[0]->drug);
		$this->assertEquals('Acetazolamide 250mg modified release capsules',$patient->medications[0]->drug->name);
		$this->assertInstanceOf('DrugRoute',$patient->medications[0]->route);
		$this->assertEquals('Ocular muscle',$patient->medications[0]->route->name);
		$this->assertInstanceOf('DrugRouteOption',$patient->medications[0]->option);
		$this->assertEquals('Left',$patient->medications[0]->option->name);
		$this->assertInstanceOf('DrugFrequency',$patient->medications[0]->frequency);
		$this->assertEquals('od',$patient->medications[0]->frequency->name);
		$this->assertEquals('one handful',$patient->medications[0]->dose);
		$this->assertNull($patient->medications[0]->stop_reason);
		$this->assertEquals('2013-04-04',$patient->medications[0]->start_date);
		$this->assertNull($patient->medications[0]->end_date);
	}

	public function getModifiedResource($id)
	{
		$resource = \Yii::app()->service->PatientMedications($id)->fetch();

		$drug1 = new PatientMedication;
		$drug1->drug = 'Acetazolamide 250mg modified release capsules';
		$drug1->route = 'Nose';
		$drug1->option = 'Right';
		$drug1->frequency = 'hourly';
		$drug1->start_date = new Date('2012-01-01');
		$drug1->end_date = new Date('2012-01-30');
		$drug1->stop_reason = 'Believed themself to be god';
		$drug1->dose = 'likesomuch';

		$drug2 = new PatientMedication;
		$drug2->drug = 'Acetazolamide 250mg modified release capsules';
		$drug2->route = 'Ocular muscle';
		$drug2->option = 'Left';
		$drug2->frequency = 'od';
		$drug2->start_date = new Date('2013-04-04');
		$drug2->end_date = null;
		$drug2->dose = 'one handful';

		$resource->medications = array();
		$resource->previous_medications = array();
		$resource->addMedications(array($drug1,$drug2));

		return $resource;
	}

	public function testResourceToModel_Save_Update_Modified_ModelCountsCorrect()
	{
		$resource = $this->getModifiedResource(1);

		$total_medications = count(\Medication::model()->findAll());

		$ps = new PatientMedicationsService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'));

		$this->assertEquals($total_medications, count(\Medication::model()->findAll()));
	}

	public function testResourceToModel_Save_Update_NotModified_ModelCountsCorrect()
	{
		$resource = \Yii::app()->service->PatientMedications(1)->fetch();

		$total_medications = count(\Medication::model()->findAll());

		$ps = new PatientMedicationsService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'));

		$this->assertEquals($total_medications, count(\Medication::model()->findAll()));
	}

	public function testResourceToModel_Save_Update_ModelIsCorrect()
	{
		$resource = $this->getModifiedResource(1);

		$ps = new PatientMedicationsService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'));

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(1,$patient->previous_medications);

		$this->assertInstanceOf('Medication',$patient->previous_medications[0]);
		$this->assertInstanceOf('Drug',$patient->previous_medications[0]->drug);
		$this->assertEquals('Acetazolamide 250mg modified release capsules',$patient->previous_medications[0]->drug->name);
		$this->assertInstanceOf('DrugRoute',$patient->previous_medications[0]->route);
		$this->assertEquals('Nose',$patient->previous_medications[0]->route->name);
		$this->assertInstanceOf('DrugRouteOption',$patient->previous_medications[0]->option);
		$this->assertEquals('Right',$patient->previous_medications[0]->option->name);
		$this->assertInstanceOf('DrugFrequency',$patient->previous_medications[0]->frequency);
		$this->assertEquals('hourly',$patient->previous_medications[0]->frequency->name);
		$this->assertEquals('likesomuch',$patient->previous_medications[0]->dose);
		$this->assertInstanceOf('MedicationStopReason',$patient->previous_medications[0]->stop_reason);
		$this->assertEquals('Believed themself to be god',$patient->previous_medications[0]->stop_reason->name);
		$this->assertEquals('2012-01-01',$patient->previous_medications[0]->start_date);
		$this->assertEquals('2012-01-30',$patient->previous_medications[0]->end_date);

		$this->assertCount(1,$patient->medications);

		$this->assertInstanceOf('Medication',$patient->medications[0]);
		$this->assertInstanceOf('Drug',$patient->medications[0]->drug);
		$this->assertEquals('Acetazolamide 250mg modified release capsules',$patient->medications[0]->drug->name);
		$this->assertInstanceOf('DrugRoute',$patient->medications[0]->route);
		$this->assertEquals('Ocular muscle',$patient->medications[0]->route->name);
		$this->assertInstanceOf('DrugRouteOption',$patient->medications[0]->option);
		$this->assertEquals('Left',$patient->medications[0]->option->name);
		$this->assertInstanceOf('DrugFrequency',$patient->medications[0]->frequency);
		$this->assertEquals('od',$patient->medications[0]->frequency->name);
		$this->assertEquals('one handful',$patient->medications[0]->dose);
		$this->assertNull($patient->medications[0]->stop_reason);
		$this->assertEquals('2013-04-04',$patient->medications[0]->start_date);
		$this->assertNull($patient->medications[0]->end_date);
	}

	public function testResourceToModel_Save_Update_DBIsCorrect()
	{
		$resource = $this->getModifiedResource(1);

		$ps = new PatientMedicationsService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(1,$patient->previous_medications);

		$this->assertInstanceOf('Medication',$patient->previous_medications[0]);
		$this->assertInstanceOf('Drug',$patient->previous_medications[0]->drug);
		$this->assertEquals('Acetazolamide 250mg modified release capsules',$patient->previous_medications[0]->drug->name);
		$this->assertInstanceOf('DrugRoute',$patient->previous_medications[0]->route);
		$this->assertEquals('Nose',$patient->previous_medications[0]->route->name);
		$this->assertInstanceOf('DrugRouteOption',$patient->previous_medications[0]->option);
		$this->assertEquals('Right',$patient->previous_medications[0]->option->name);
		$this->assertInstanceOf('DrugFrequency',$patient->previous_medications[0]->frequency);
		$this->assertEquals('hourly',$patient->previous_medications[0]->frequency->name);
		$this->assertEquals('likesomuch',$patient->previous_medications[0]->dose);
		$this->assertInstanceOf('MedicationStopReason',$patient->previous_medications[0]->stop_reason);
		$this->assertEquals('Believed themself to be god',$patient->previous_medications[0]->stop_reason->name);
		$this->assertEquals('2012-01-01',$patient->previous_medications[0]->start_date);
		$this->assertEquals('2012-01-30',$patient->previous_medications[0]->end_date);

		$this->assertCount(1,$patient->medications);

		$this->assertInstanceOf('Medication',$patient->medications[0]);
		$this->assertInstanceOf('Drug',$patient->medications[0]->drug);
		$this->assertEquals('Acetazolamide 250mg modified release capsules',$patient->medications[0]->drug->name);
		$this->assertInstanceOf('DrugRoute',$patient->medications[0]->route);
		$this->assertEquals('Ocular muscle',$patient->medications[0]->route->name);
		$this->assertInstanceOf('DrugRouteOption',$patient->medications[0]->option);
		$this->assertEquals('Left',$patient->medications[0]->option->name);
		$this->assertInstanceOf('DrugFrequency',$patient->medications[0]->frequency);
		$this->assertEquals('od',$patient->medications[0]->frequency->name);
		$this->assertEquals('one handful',$patient->medications[0]->dose);
		$this->assertNull($patient->medications[0]->stop_reason);
		$this->assertEquals('2013-04-04',$patient->medications[0]->start_date);
		$this->assertNull($patient->medications[0]->end_date);
	}

	public function testJsonToResource()
	{
		$json = '{"medications":[{"drug":"Abidec drops","route":"IM","option":"Left","frequency":"bd","start_date":{"date":"2012-01-01 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"end_date":null,"dose":"loads","stop_reason":false,"id":null,"last_modified":null}],"previous_medications":[{"drug":"Acetazolamide 250mg tablets","route":"Eye","option":"Right","frequency":"2 hourly","start_date":{"date":"2013-03-03 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"end_date":{"date":"2013-06-06 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"dose":"much","stop_reason":"Started seeing halos and auras","id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800}}';

		$ps = new PatientMedicationsService;
		$resource = $ps->jsonToResource($json);

		$this->assertInstanceOf('services\PatientMedications',$resource);
		$this->assertCount(1,$resource->medications);

		$this->assertInstanceOf('services\PatientMedication',$resource->medications[0]);
		$this->assertEquals('Abidec drops',$resource->medications[0]->drug);
		$this->assertEquals('IM',$resource->medications[0]->route);
		$this->assertEquals('Left',$resource->medications[0]->option);
		$this->assertEquals('bd',$resource->medications[0]->frequency);
		$this->assertEquals('loads',$resource->medications[0]->dose);
		$this->assertFalse($resource->medications[0]->stop_reason);
		$this->assertInstanceOf('services\Date',$resource->medications[0]->start_date);
		$this->assertNull($resource->medications[0]->end_date);

		$this->assertCount(1,$resource->previous_medications);

		$this->assertInstanceOf('services\PatientMedication',$resource->previous_medications[0]);
		$this->assertEquals('Acetazolamide 250mg tablets',$resource->previous_medications[0]->drug);
		$this->assertEquals('Eye',$resource->previous_medications[0]->route);
		$this->assertEquals('Right',$resource->previous_medications[0]->option);
		$this->assertEquals('2 hourly',$resource->previous_medications[0]->frequency);
		$this->assertEquals('much',$resource->previous_medications[0]->dose);
		$this->assertEquals('Started seeing halos and auras',$resource->previous_medications[0]->stop_reason);
		$this->assertInstanceOf('services\Date',$resource->previous_medications[0]->start_date);
		$this->assertInstanceOf('services\Date',$resource->previous_medications[0]->end_date);
	}

	public function jsonToModel_NoSave_NoNewRows()
	{
		$json = '{"medications":[{"drug":"Abidec drops","route":"IM","option":"Left","frequency":"bd","start_date":{"date":"2012-01-01 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"end_date":null,"dose":"loads","stop_reason":false,"id":null,"last_modified":null}],"previous_medications":[{"drug":"Acetazolamide 250mg tablets","route":"Eye","option":"Right","frequency":"2 hourly","start_date":{"date":"2013-03-03 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"end_date":{"date":"2013-06-06 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"dose":"much","stop_reason":"Started seeing halos and auras","id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800}}';

		$total_medications = count(\Medication::model()->findAll());

		$ps = new PatientMedicationsService;
		$patient = $ps->jsonToModel($json, false);

		$this->assertEquals($total_medications, count(\Medication::model()->findAll()));
	}

	public function testJsonToModel_NoSave_ModelIsCorrect()
	{
		$json = '{"medications":[{"drug":"Abidec drops","route":"IM","option":"Left","frequency":"bd","start_date":{"date":"2012-01-01 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"end_date":null,"dose":"loads","stop_reason":false,"id":null,"last_modified":null}],"previous_medications":[{"drug":"Acetazolamide 250mg tablets","route":"Eye","option":"Right","frequency":"2 hourly","start_date":{"date":"2013-03-03 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"end_date":{"date":"2013-06-06 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"dose":"much","stop_reason":"Started seeing halos and auras","id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800}}';

		$ps = new PatientMedicationsService;
		$patient = $ps->jsonToModel($json, $this->patients('patient4'), false);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(1,$patient->previous_medications);

		$this->assertInstanceOf('Medication',$patient->previous_medications[0]);
		$this->assertInstanceOf('Drug',$patient->previous_medications[0]->drug);
		$this->assertEquals('Acetazolamide 250mg tablets',$patient->previous_medications[0]->drug->name);
		$this->assertInstanceOf('DrugRoute',$patient->previous_medications[0]->route);
		$this->assertEquals('Eye',$patient->previous_medications[0]->route->name);
		$this->assertInstanceOf('DrugRouteOption',$patient->previous_medications[0]->option);
		$this->assertEquals('Right',$patient->previous_medications[0]->option->name);
		$this->assertInstanceOf('DrugFrequency',$patient->previous_medications[0]->frequency);
		$this->assertEquals('2 hourly',$patient->previous_medications[0]->frequency->name);
		$this->assertEquals('much',$patient->previous_medications[0]->dose);
		$this->assertInstanceOf('MedicationStopReason',$patient->previous_medications[0]->stop_reason);
		$this->assertEquals('Started seeing halos and auras',$patient->previous_medications[0]->stop_reason->name);
		$this->assertEquals('2013-03-03',$patient->previous_medications[0]->start_date);
		$this->assertEquals('2013-06-06',$patient->previous_medications[0]->end_date);

		$this->assertCount(1,$patient->medications);

		$this->assertInstanceOf('Medication',$patient->medications[0]);
		$this->assertInstanceOf('Drug',$patient->medications[0]->drug);
		$this->assertEquals('Abidec drops',$patient->medications[0]->drug->name);
		$this->assertInstanceOf('DrugRoute',$patient->medications[0]->route);
		$this->assertEquals('IM',$patient->medications[0]->route->name);
		$this->assertInstanceOf('DrugRouteOption',$patient->medications[0]->option);
		$this->assertEquals('Left',$patient->medications[0]->option->name);
		$this->assertInstanceOf('DrugFrequency',$patient->medications[0]->frequency);
		$this->assertEquals('bd',$patient->medications[0]->frequency->name);
		$this->assertEquals('loads',$patient->medications[0]->dose);
		$this->assertNull($patient->medications[0]->stop_reason);
		$this->assertEquals('2012-01-01',$patient->medications[0]->start_date);
		$this->assertNull($patient->medications[0]->end_date);
	}

	public function testJsonToModel_Save_Create_ModelCountsCorrect()
	{
		$json = '{"medications":[{"drug":"Abidec drops","route":"IM","option":"Left","frequency":"bd","start_date":{"date":"2012-01-01 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"end_date":null,"dose":"loads","stop_reason":false,"id":null,"last_modified":null}],"previous_medications":[{"drug":"Acetazolamide 250mg tablets","route":"Eye","option":"Right","frequency":"2 hourly","start_date":{"date":"2013-03-03 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"end_date":{"date":"2013-06-06 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"dose":"much","stop_reason":"Started seeing halos and auras","id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800}}';

		$total_medications = count(\Medication::model()->findAll());

		$ps = new PatientMedicationsService;
		$patient = $ps->jsonToModel($json, $this->patients('patient4'));

		$this->assertEquals($total_medications+2, count(\Medication::model()->findAll()));
	}

	public function testJsonToModel_Save_Create_ModelIsCorrect()
	{
		$json = '{"medications":[{"drug":"Abidec drops","route":"IM","option":"Left","frequency":"bd","start_date":{"date":"2012-01-01 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"end_date":null,"dose":"loads","stop_reason":false,"id":null,"last_modified":null}],"previous_medications":[{"drug":"Acetazolamide 250mg tablets","route":"Eye","option":"Right","frequency":"2 hourly","start_date":{"date":"2013-03-03 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"end_date":{"date":"2013-06-06 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"dose":"much","stop_reason":"Started seeing halos and auras","id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800}}';

		$ps = new PatientMedicationsService;
		$patient = $ps->jsonToModel($json, $this->patients('patient4'));
		
		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(1,$patient->previous_medications);
		
		$this->assertInstanceOf('Medication',$patient->previous_medications[0]);
		$this->assertInstanceOf('Drug',$patient->previous_medications[0]->drug);
		$this->assertEquals('Acetazolamide 250mg tablets',$patient->previous_medications[0]->drug->name);
		$this->assertInstanceOf('DrugRoute',$patient->previous_medications[0]->route);
		$this->assertEquals('Eye',$patient->previous_medications[0]->route->name);
		$this->assertInstanceOf('DrugRouteOption',$patient->previous_medications[0]->option);
		$this->assertEquals('Right',$patient->previous_medications[0]->option->name);
		$this->assertInstanceOf('DrugFrequency',$patient->previous_medications[0]->frequency);
		$this->assertEquals('2 hourly',$patient->previous_medications[0]->frequency->name);
		$this->assertEquals('much',$patient->previous_medications[0]->dose);
		$this->assertInstanceOf('MedicationStopReason',$patient->previous_medications[0]->stop_reason);
		$this->assertEquals('Started seeing halos and auras',$patient->previous_medications[0]->stop_reason->name);
		$this->assertEquals('2013-03-03',$patient->previous_medications[0]->start_date);
		$this->assertEquals('2013-06-06',$patient->previous_medications[0]->end_date);

		$this->assertCount(1,$patient->medications);

		$this->assertInstanceOf('Medication',$patient->medications[0]);
		$this->assertInstanceOf('Drug',$patient->medications[0]->drug);
		$this->assertEquals('Abidec drops',$patient->medications[0]->drug->name);
		$this->assertInstanceOf('DrugRoute',$patient->medications[0]->route);
		$this->assertEquals('IM',$patient->medications[0]->route->name);
		$this->assertInstanceOf('DrugRouteOption',$patient->medications[0]->option);
		$this->assertEquals('Left',$patient->medications[0]->option->name);
		$this->assertInstanceOf('DrugFrequency',$patient->medications[0]->frequency);
		$this->assertEquals('bd',$patient->medications[0]->frequency->name);
		$this->assertEquals('loads',$patient->medications[0]->dose);
		$this->assertNull($patient->medications[0]->stop_reason);
		$this->assertEquals('2012-01-01',$patient->medications[0]->start_date);
		$this->assertNull($patient->medications[0]->end_date);
	}

	public function testJsonToModel_Save_Create_DBIsCorrect()
	{
		$json = '{"medications":[{"drug":"Abidec drops","route":"IM","option":"Left","frequency":"bd","start_date":{"date":"2012-01-01 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"end_date":null,"dose":"loads","stop_reason":false,"id":null,"last_modified":null}],"previous_medications":[{"drug":"Acetazolamide 250mg tablets","route":"Eye","option":"Right","frequency":"2 hourly","start_date":{"date":"2013-03-03 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"end_date":{"date":"2013-06-06 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"dose":"much","stop_reason":"Started seeing halos and auras","id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800}}';

		$ps = new PatientMedicationsService;
		$patient = $ps->jsonToModel($json, $this->patients('patient4'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(1,$patient->previous_medications);

		$this->assertInstanceOf('Medication',$patient->previous_medications[0]);
		$this->assertInstanceOf('Drug',$patient->previous_medications[0]->drug);
		$this->assertEquals('Acetazolamide 250mg tablets',$patient->previous_medications[0]->drug->name);
		$this->assertInstanceOf('DrugRoute',$patient->previous_medications[0]->route);
		$this->assertEquals('Eye',$patient->previous_medications[0]->route->name);
		$this->assertInstanceOf('DrugRouteOption',$patient->previous_medications[0]->option);
		$this->assertEquals('Right',$patient->previous_medications[0]->option->name);
		$this->assertInstanceOf('DrugFrequency',$patient->previous_medications[0]->frequency);
		$this->assertEquals('2 hourly',$patient->previous_medications[0]->frequency->name);
		$this->assertEquals('much',$patient->previous_medications[0]->dose);
		$this->assertInstanceOf('MedicationStopReason',$patient->previous_medications[0]->stop_reason);
		$this->assertEquals('Started seeing halos and auras',$patient->previous_medications[0]->stop_reason->name);
		$this->assertEquals('2013-03-03',$patient->previous_medications[0]->start_date);
		$this->assertEquals('2013-06-06',$patient->previous_medications[0]->end_date);

		$this->assertCount(1,$patient->medications);

		$this->assertInstanceOf('Medication',$patient->medications[0]);
		$this->assertInstanceOf('Drug',$patient->medications[0]->drug);
		$this->assertEquals('Abidec drops',$patient->medications[0]->drug->name);
		$this->assertInstanceOf('DrugRoute',$patient->medications[0]->route);
		$this->assertEquals('IM',$patient->medications[0]->route->name);
		$this->assertInstanceOf('DrugRouteOption',$patient->medications[0]->option);
		$this->assertEquals('Left',$patient->medications[0]->option->name);
		$this->assertInstanceOf('DrugFrequency',$patient->medications[0]->frequency);
		$this->assertEquals('bd',$patient->medications[0]->frequency->name);
		$this->assertEquals('loads',$patient->medications[0]->dose);
		$this->assertNull($patient->medications[0]->stop_reason);
		$this->assertEquals('2012-01-01',$patient->medications[0]->start_date);
		$this->assertNull($patient->medications[0]->end_date);
	}

	public function testJsonToModel_Save_Update_ModelCountsCorrect()
	{
		$json = '{"medications":[{"drug":"Abidec drops","route":"IM","option":"Left","frequency":"bd","start_date":{"date":"2012-01-01 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"end_date":null,"dose":"loads","stop_reason":false,"id":null,"last_modified":null}],"previous_medications":[{"drug":"Acetazolamide 250mg tablets","route":"Eye","option":"Right","frequency":"2 hourly","start_date":{"date":"2013-03-03 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"end_date":{"date":"2013-06-06 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"dose":"much","stop_reason":"Started seeing halos and auras","id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800}}';

		$total_medications = count(\Medication::model()->findAll());

		$ps = new PatientMedicationsService;
		$patient = $ps->jsonToModel($json, $this->patients('patient1'));

		$this->assertEquals($total_medications, count(\Medication::model()->findAll()));
	}

	public function testJsonToModel_Save_Update_ModelIsCorrect()
	{
		$json = '{"medications":[{"drug":"Abidec drops","route":"IM","option":"Left","frequency":"bd","start_date":{"date":"2012-01-01 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"end_date":null,"dose":"loads","stop_reason":false,"id":null,"last_modified":null}],"previous_medications":[{"drug":"Acetazolamide 250mg tablets","route":"Eye","option":"Right","frequency":"2 hourly","start_date":{"date":"2013-03-03 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"end_date":{"date":"2013-06-06 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"dose":"much","stop_reason":"Started seeing halos and auras","id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800}}';

		$ps = new PatientMedicationsService;
		$patient = $ps->jsonToModel($json, $this->patients('patient1'));

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(1,$patient->previous_medications);

		$this->assertInstanceOf('Medication',$patient->previous_medications[0]);
		$this->assertInstanceOf('Drug',$patient->previous_medications[0]->drug);
		$this->assertEquals('Acetazolamide 250mg tablets',$patient->previous_medications[0]->drug->name);
		$this->assertInstanceOf('DrugRoute',$patient->previous_medications[0]->route);
		$this->assertEquals('Eye',$patient->previous_medications[0]->route->name);
		$this->assertInstanceOf('DrugRouteOption',$patient->previous_medications[0]->option);
		$this->assertEquals('Right',$patient->previous_medications[0]->option->name);
		$this->assertInstanceOf('DrugFrequency',$patient->previous_medications[0]->frequency);
		$this->assertEquals('2 hourly',$patient->previous_medications[0]->frequency->name);
		$this->assertEquals('much',$patient->previous_medications[0]->dose);
		$this->assertInstanceOf('MedicationStopReason',$patient->previous_medications[0]->stop_reason);
		$this->assertEquals('Started seeing halos and auras',$patient->previous_medications[0]->stop_reason->name);
		$this->assertEquals('2013-03-03',$patient->previous_medications[0]->start_date);
		$this->assertEquals('2013-06-06',$patient->previous_medications[0]->end_date);

		$this->assertCount(1,$patient->medications);

		$this->assertInstanceOf('Medication',$patient->medications[0]);
		$this->assertInstanceOf('Drug',$patient->medications[0]->drug);
		$this->assertEquals('Abidec drops',$patient->medications[0]->drug->name);
		$this->assertInstanceOf('DrugRoute',$patient->medications[0]->route);
		$this->assertEquals('IM',$patient->medications[0]->route->name);
		$this->assertInstanceOf('DrugRouteOption',$patient->medications[0]->option);
		$this->assertEquals('Left',$patient->medications[0]->option->name);
		$this->assertInstanceOf('DrugFrequency',$patient->medications[0]->frequency);
		$this->assertEquals('bd',$patient->medications[0]->frequency->name);
		$this->assertEquals('loads',$patient->medications[0]->dose);
		$this->assertNull($patient->medications[0]->stop_reason);
		$this->assertEquals('2012-01-01',$patient->medications[0]->start_date);
		$this->assertNull($patient->medications[0]->end_date);
	}

	public function testJsonToModel_Save_Update_DBIsCorrect()
	{
		$json = '{"medications":[{"drug":"Abidec drops","route":"IM","option":"Left","frequency":"bd","start_date":{"date":"2012-01-01 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"end_date":null,"dose":"loads","stop_reason":false,"id":null,"last_modified":null}],"previous_medications":[{"drug":"Acetazolamide 250mg tablets","route":"Eye","option":"Right","frequency":"2 hourly","start_date":{"date":"2013-03-03 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"end_date":{"date":"2013-06-06 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"dose":"much","stop_reason":"Started seeing halos and auras","id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800}}';

		$ps = new PatientMedicationsService;
		$patient = $ps->jsonToModel($json, $this->patients('patient1'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(1,$patient->previous_medications);

		$this->assertInstanceOf('Medication',$patient->previous_medications[0]);
		$this->assertInstanceOf('Drug',$patient->previous_medications[0]->drug);
		$this->assertEquals('Acetazolamide 250mg tablets',$patient->previous_medications[0]->drug->name);
		$this->assertInstanceOf('DrugRoute',$patient->previous_medications[0]->route);
		$this->assertEquals('Eye',$patient->previous_medications[0]->route->name);
		$this->assertInstanceOf('DrugRouteOption',$patient->previous_medications[0]->option);
		$this->assertEquals('Right',$patient->previous_medications[0]->option->name);
		$this->assertInstanceOf('DrugFrequency',$patient->previous_medications[0]->frequency);
		$this->assertEquals('2 hourly',$patient->previous_medications[0]->frequency->name);
		$this->assertEquals('much',$patient->previous_medications[0]->dose);
		$this->assertInstanceOf('MedicationStopReason',$patient->previous_medications[0]->stop_reason);
		$this->assertEquals('Started seeing halos and auras',$patient->previous_medications[0]->stop_reason->name);
		$this->assertEquals('2013-03-03',$patient->previous_medications[0]->start_date);
		$this->assertEquals('2013-06-06',$patient->previous_medications[0]->end_date);

		$this->assertCount(1,$patient->medications);

		$this->assertInstanceOf('Medication',$patient->medications[0]);
		$this->assertInstanceOf('Drug',$patient->medications[0]->drug);
		$this->assertEquals('Abidec drops',$patient->medications[0]->drug->name);
		$this->assertInstanceOf('DrugRoute',$patient->medications[0]->route);
		$this->assertEquals('IM',$patient->medications[0]->route->name);
		$this->assertInstanceOf('DrugRouteOption',$patient->medications[0]->option);
		$this->assertEquals('Left',$patient->medications[0]->option->name);
		$this->assertInstanceOf('DrugFrequency',$patient->medications[0]->frequency);
		$this->assertEquals('bd',$patient->medications[0]->frequency->name);
		$this->assertEquals('loads',$patient->medications[0]->dose);
		$this->assertNull($patient->medications[0]->stop_reason);
		$this->assertEquals('2012-01-01',$patient->medications[0]->start_date);
		$this->assertNull($patient->medications[0]->end_date);
	}
}
