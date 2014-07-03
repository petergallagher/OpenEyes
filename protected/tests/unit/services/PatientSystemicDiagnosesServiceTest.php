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

class PatientSystemicDiagnosesServiceTest extends \CDbTestCase
{
	public $fixtures = array(
		'patients' => 'Patient',
		'sds' => 'SecondaryDiagnosis',
		'disorders' => 'Disorder',
	);

	public function testModelToResource()
	{
		$patient = $this->patients('patient2');

		$ps = new PatientSystemicDiagnosesService;

		$resource = $ps->modelToResource($patient);

		$this->assertInstanceOf('services\PatientSystemicDiagnoses',$resource);
		$this->assertCount(2,$resource->diagnoses);

		$this->assertInstanceOf('services\PatientDiagnosis',$resource->diagnoses[0]);
		$this->assertEquals('Diabetes mellitus type 1',$resource->diagnoses[0]->disorder);
		$this->assertEquals('Left',$resource->diagnoses[0]->side);

		$this->assertInstanceOf('services\PatientDiagnosis',$resource->diagnoses[1]);
		$this->assertEquals('Essential hypertension',$resource->diagnoses[1]->disorder);
		$this->assertFalse($resource->diagnoses[1]->side);
	}

	public function getResource()
	{
		$resource = new PatientSystemicDiagnoses(1);

		$d1 = new PatientDiagnosis;
		$d1->disorder = 'Essential hypertension';
		$d1->side = 'Both';

		$d2 = new PatientDiagnosis;
		$d2->disorder = 'Myocardial infarction';
		$d2->side = false;

		$resource->diagnoses = array($d1,$d2);

		return $resource;
	}

	public function testResourceToModel_NoSave_NoNewRecords()
	{
		$resource = $this->getResource();

		$total_sd = count(\SecondaryDiagnosis::model()->findAll());

		$ps = new PatientSystemicDiagnosesService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient3'), false);

		$this->assertEquals($total_sd, count(\SecondaryDiagnosis::model()->findAll()));
	}

	public function testResourceToModel_NoSave_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientSystemicDiagnosesService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient3'), false);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->systemicDiagnoses);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->systemicDiagnoses[0]);
		$this->assertInstanceOf('Disorder',$patient->systemicDiagnoses[0]->disorder);
		$this->assertEquals('Essential hypertension',$patient->systemicDiagnoses[0]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Essential hypertension'))->id,$patient->systemicDiagnoses[0]->disorder_id);
		$this->assertInstanceOf('Eye',$patient->systemicDiagnoses[0]->eye);
		$this->assertEquals('Both',$patient->systemicDiagnoses[0]->eye->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Both'))->id,$patient->systemicDiagnoses[0]->eye_id);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->systemicDiagnoses[1]);
		$this->assertInstanceOf('Disorder',$patient->systemicDiagnoses[1]->disorder);
		$this->assertEquals('Myocardial infarction',$patient->systemicDiagnoses[1]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Myocardial infarction'))->id,$patient->systemicDiagnoses[1]->disorder_id);
		$this->assertNull($patient->systemicDiagnoses[1]->eye);
		$this->assertNull($patient->systemicDiagnoses[1]->eye_id);
	}

	public function testResourceToModel_Save_Create_ModelCountsCorrect()
	{
		$resource = $this->getResource();

		$total_sd = count(\SecondaryDiagnosis::model()->findAll());

		$ps = new PatientSystemicDiagnosesService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient3'));

		$this->assertEquals($total_sd+2, count(\SecondaryDiagnosis::model()->findAll()));
	}

	public function testResourceToModel_Save_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientSystemicDiagnosesService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient3'));

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->systemicDiagnoses);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->systemicDiagnoses[0]);
		$this->assertInstanceOf('Disorder',$patient->systemicDiagnoses[0]->disorder);
		$this->assertEquals('Essential hypertension',$patient->systemicDiagnoses[0]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Essential hypertension'))->id,$patient->systemicDiagnoses[0]->disorder_id);
		$this->assertInstanceOf('Eye',$patient->systemicDiagnoses[0]->eye);
		$this->assertEquals('Both',$patient->systemicDiagnoses[0]->eye->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Both'))->id,$patient->systemicDiagnoses[0]->eye_id);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->systemicDiagnoses[1]);
		$this->assertInstanceOf('Disorder',$patient->systemicDiagnoses[1]->disorder);
		$this->assertEquals('Myocardial infarction',$patient->systemicDiagnoses[1]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Myocardial infarction'))->id,$patient->systemicDiagnoses[1]->disorder_id);
		$this->assertNull($patient->systemicDiagnoses[1]->eye);
		$this->assertNull($patient->systemicDiagnoses[1]->eye_id);
	}

	public function testResourceToModel_Save_Create_DBIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientSystemicDiagnosesService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient3'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->systemicDiagnoses);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->systemicDiagnoses[0]);
		$this->assertInstanceOf('Disorder',$patient->systemicDiagnoses[0]->disorder);
		$this->assertEquals('Essential hypertension',$patient->systemicDiagnoses[0]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Essential hypertension'))->id,$patient->systemicDiagnoses[0]->disorder_id);
		$this->assertInstanceOf('Eye',$patient->systemicDiagnoses[0]->eye);
		$this->assertEquals('Both',$patient->systemicDiagnoses[0]->eye->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Both'))->id,$patient->systemicDiagnoses[0]->eye_id);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->systemicDiagnoses[1]);
		$this->assertInstanceOf('Disorder',$patient->systemicDiagnoses[1]->disorder);
		$this->assertEquals('Myocardial infarction',$patient->systemicDiagnoses[1]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Myocardial infarction'))->id,$patient->systemicDiagnoses[1]->disorder_id);
		$this->assertNull($patient->systemicDiagnoses[1]->eye);
		$this->assertNull($patient->systemicDiagnoses[1]->eye_id);
	}

	public function testResourceToModel_Save_Create_NonSystemic_Exception()
	{
		$resource = $this->getResource();

		$resource->diagnoses[0]->disorder = 'Myopia';

		$this->setExpectedException('Exception','PatientSystemicDiagnoses passed a resource containing ophthalmic diagnoses');

		$ps = new PatientSystemicDiagnosesService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient3'));
	}

	public function testResourceToModel_Save_Update_Modified_ModelCountsCorrect()
	{
		$resource = $this->getResource();

		$total_sd = count(\SecondaryDiagnosis::model()->findAll());

		$ps = new PatientSystemicDiagnosesService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient2'));

		$this->assertEquals($total_sd, count(\SecondaryDiagnosis::model()->findAll()));
	}

	public function testResourceToModel_Save_Update_NotModified_ModelCountsCorrect()
	{
		$resource = \Yii::app()->service->PatientSystemicDiagnoses(2)->fetch();

		$total_sd = count(\SecondaryDiagnosis::model()->findAll());

		$ps = new PatientSystemicDiagnosesService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient2'));

		$this->assertEquals($total_sd, count(\SecondaryDiagnosis::model()->findAll()));
	}

	public function testResourceToModel_Save_Update_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientSystemicDiagnosesService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient2'));

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->systemicDiagnoses);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->systemicDiagnoses[0]);
		$this->assertInstanceOf('Disorder',$patient->systemicDiagnoses[0]->disorder);
		$this->assertEquals('Essential hypertension',$patient->systemicDiagnoses[0]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Essential hypertension'))->id,$patient->systemicDiagnoses[0]->disorder_id);
		$this->assertInstanceOf('Eye',$patient->systemicDiagnoses[0]->eye);
		$this->assertEquals('Both',$patient->systemicDiagnoses[0]->eye->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Both'))->id,$patient->systemicDiagnoses[0]->eye_id);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->systemicDiagnoses[1]);
		$this->assertInstanceOf('Disorder',$patient->systemicDiagnoses[1]->disorder);
		$this->assertEquals('Myocardial infarction',$patient->systemicDiagnoses[1]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Myocardial infarction'))->id,$patient->systemicDiagnoses[1]->disorder_id);
		$this->assertNull($patient->systemicDiagnoses[1]->eye);
		$this->assertNull($patient->systemicDiagnoses[1]->eye_id);
	}

	public function testResourceToModel_Save_Update_DBIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientSystemicDiagnosesService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient2'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->systemicDiagnoses);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->systemicDiagnoses[0]);
		$this->assertInstanceOf('Disorder',$patient->systemicDiagnoses[0]->disorder);
		$this->assertEquals('Essential hypertension',$patient->systemicDiagnoses[0]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Essential hypertension'))->id,$patient->systemicDiagnoses[0]->disorder_id);
		$this->assertInstanceOf('Eye',$patient->systemicDiagnoses[0]->eye);
		$this->assertEquals('Both',$patient->systemicDiagnoses[0]->eye->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Both'))->id,$patient->systemicDiagnoses[0]->eye_id);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->systemicDiagnoses[1]);
		$this->assertInstanceOf('Disorder',$patient->systemicDiagnoses[1]->disorder);
		$this->assertEquals('Myocardial infarction',$patient->systemicDiagnoses[1]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Myocardial infarction'))->id,$patient->systemicDiagnoses[1]->disorder_id);
		$this->assertNull($patient->systemicDiagnoses[1]->eye);
		$this->assertNull($patient->systemicDiagnoses[1]->eye_id);
	}

	public function testResourceToModel_Save_Update_NotSystemic_Exception()
	{
		$resource = $this->getResource();

		$resource->diagnoses[0]->disorder = 'Myopia';

		$this->setExpectedException('Exception','PatientSystemicDiagnoses passed a resource containing ophthalmic diagnoses');

		$ps = new PatientSystemicDiagnosesService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient2'));
	}

	public function testJsonToResource()
	{
		$json = '{"diagnoses":[{"disorder":"Diabetes mellitus type 1","side":"Left","id":null,"last_modified":null},{"disorder":"Essential hypertension","side":false,"id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800}}';

		$ps = new PatientSystemicDiagnosesService;
		$resource = $ps->jsonToResource($json);

		$this->assertInstanceOf('services\PatientSystemicDiagnoses',$resource);
		$this->assertCount(2,$resource->diagnoses);

		$this->assertInstanceOf('services\PatientDiagnosis',$resource->diagnoses[0]);
		$this->assertEquals('Diabetes mellitus type 1',$resource->diagnoses[0]->disorder);
		$this->assertEquals('Left',$resource->diagnoses[0]->side);

		$this->assertInstanceOf('services\PatientDiagnosis',$resource->diagnoses[1]);
		$this->assertEquals('Essential hypertension',$resource->diagnoses[1]->disorder);
		$this->assertFalse($resource->diagnoses[1]->side);
	}

	public function testJsonToModel_NoSave_NoNewRows()
	{
		$json = '{"diagnoses":[{"disorder":"Diabetes mellitus type 1","side":"Left","id":null,"last_modified":null},{"disorder":"Essential hypertension","side":false,"id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800}}';

		$total_sd = count(\SecondaryDiagnosis::model()->findAll());

		$ps = new PatientSystemicDiagnosesService;
		$patient = $ps->jsonToModel($json, $this->patients('patient3'), false);

		$this->assertEquals($total_sd, count(\SecondaryDiagnosis::model()->findAll()));
	}

	public function testJsonToModel_NoSave_ModelIsCorrect()
	{
		$json = '{"diagnoses":[{"disorder":"Diabetes mellitus type 1","side":"Left","id":null,"last_modified":null},{"disorder":"Essential hypertension","side":false,"id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800}}';

		$ps = new PatientSystemicDiagnosesService;
		$patient = $ps->jsonToModel($json, new \Patient, false);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->systemicDiagnoses);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->systemicDiagnoses[0]);
		$this->assertInstanceOf('Disorder',$patient->systemicDiagnoses[0]->disorder);
		$this->assertEquals('Diabetes mellitus type 1',$patient->systemicDiagnoses[0]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Diabetes mellitus type 1'))->id,$patient->systemicDiagnoses[0]->disorder_id);
		$this->assertInstanceOf('Eye',$patient->systemicDiagnoses[0]->eye);
		$this->assertEquals('Left',$patient->systemicDiagnoses[0]->eye->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Left'))->id,$patient->systemicDiagnoses[0]->eye_id);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->systemicDiagnoses[1]);
		$this->assertInstanceOf('Disorder',$patient->systemicDiagnoses[1]->disorder);
		$this->assertEquals('Essential hypertension',$patient->systemicDiagnoses[1]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Essential hypertension'))->id,$patient->systemicDiagnoses[1]->disorder_id);
		$this->assertNull($patient->systemicDiagnoses[1]->eye);
		$this->assertNull($patient->systemicDiagnoses[1]->eye_id);
	}

	public function testJsonToModel_Save_Create_ModelCountsCorrect()
	{
		$json = '{"diagnoses":[{"disorder":"Diabetes mellitus type 1","side":"Left","id":null,"last_modified":null},{"disorder":"Essential hypertension","side":false,"id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800}}';

		$total_sd = count(\SecondaryDiagnosis::model()->findAll());

		$ps = new PatientSystemicDiagnosesService;
		$patient = $ps->jsonToModel($json, $this->patients('patient4'));

		$this->assertEquals($total_sd+2, count(\SecondaryDiagnosis::model()->findAll()));
	}

	public function testJsonToModel_Save_Create_ModelIsCorrect()
	{
		$json = '{"diagnoses":[{"disorder":"Diabetes mellitus type 1","side":"Left","id":null,"last_modified":null},{"disorder":"Essential hypertension","side":false,"id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800}}';

		$ps = new PatientSystemicDiagnosesService;
		$patient = $ps->jsonToModel($json, $this->patients('patient4'));

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->systemicDiagnoses);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->systemicDiagnoses[0]);
		$this->assertInstanceOf('Disorder',$patient->systemicDiagnoses[0]->disorder);
		$this->assertEquals('Diabetes mellitus type 1',$patient->systemicDiagnoses[0]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Diabetes mellitus type 1'))->id,$patient->systemicDiagnoses[0]->disorder_id);
		$this->assertInstanceOf('Eye',$patient->systemicDiagnoses[0]->eye);
		$this->assertEquals('Left',$patient->systemicDiagnoses[0]->eye->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Left'))->id,$patient->systemicDiagnoses[0]->eye_id);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->systemicDiagnoses[1]);
		$this->assertInstanceOf('Disorder',$patient->systemicDiagnoses[1]->disorder);
		$this->assertEquals('Essential hypertension',$patient->systemicDiagnoses[1]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Essential hypertension'))->id,$patient->systemicDiagnoses[1]->disorder_id);
		$this->assertNull($patient->systemicDiagnoses[1]->eye);
		$this->assertNull($patient->systemicDiagnoses[1]->eye_id);
	}

	public function testJsonToModel_Save_Create_DBIsCorrect()
	{
		$json = '{"diagnoses":[{"disorder":"Diabetes mellitus type 1","side":"Left","id":null,"last_modified":null},{"disorder":"Essential hypertension","side":false,"id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800}}';

		$ps = new PatientSystemicDiagnosesService;
		$patient = $ps->jsonToModel($json, $this->patients('patient4'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->systemicDiagnoses);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->systemicDiagnoses[0]);
		$this->assertInstanceOf('Disorder',$patient->systemicDiagnoses[0]->disorder);
		$this->assertEquals('Diabetes mellitus type 1',$patient->systemicDiagnoses[0]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Diabetes mellitus type 1'))->id,$patient->systemicDiagnoses[0]->disorder_id);
		$this->assertInstanceOf('Eye',$patient->systemicDiagnoses[0]->eye);
		$this->assertEquals('Left',$patient->systemicDiagnoses[0]->eye->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Left'))->id,$patient->systemicDiagnoses[0]->eye_id);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->systemicDiagnoses[1]);
		$this->assertInstanceOf('Disorder',$patient->systemicDiagnoses[1]->disorder);
		$this->assertEquals('Essential hypertension',$patient->systemicDiagnoses[1]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Essential hypertension'))->id,$patient->systemicDiagnoses[1]->disorder_id);
		$this->assertNull($patient->systemicDiagnoses[1]->eye);
		$this->assertNull($patient->systemicDiagnoses[1]->eye_id);
	}

	public function testJsonToModel_Save_Create_NonSystemic_Exception()
	{
		$json = '{"diagnoses":[{"disorder":"Myopia","side":"Left","id":null,"last_modified":null},{"disorder":"Essential hypertension","side":false,"id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800}}';

		$this->setExpectedException('Exception','PatientSystemicDiagnoses passed a resource containing ophthalmic diagnoses');

		$ps = new PatientSystemicDiagnosesService;
		$patient = $ps->jsonToModel($json, $this->patients('patient3'));
	}

	public function testJsonToModel_Save_Update_ModelCountsCorrect()
	{
		$json = '{"diagnoses":[{"disorder":"Diabetes mellitus type 1","side":"Left","id":null,"last_modified":null},{"disorder":"Essential hypertension","side":false,"id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800}}';

		$total_sd = count(\SecondaryDiagnosis::model()->findAll());

		$ps = new PatientSystemicDiagnosesService;
		$patient = $ps->jsonToModel($json, $this->patients('patient2'));

		$this->assertEquals($total_sd, count(\SecondaryDiagnosis::model()->findAll()));
	}

	public function testJsonToModel_Save_Update_ModelIsCorrect()
	{
		$json = '{"diagnoses":[{"disorder":"Diabetes mellitus type 1","side":"Left","id":null,"last_modified":null},{"disorder":"Essential hypertension","side":false,"id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800}}';

		$ps = new PatientSystemicDiagnosesService;
		$patient = $ps->jsonToModel($json, $this->patients('patient4'));

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->systemicDiagnoses);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->systemicDiagnoses[0]);
		$this->assertInstanceOf('Disorder',$patient->systemicDiagnoses[0]->disorder);
		$this->assertEquals('Diabetes mellitus type 1',$patient->systemicDiagnoses[0]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Diabetes mellitus type 1'))->id,$patient->systemicDiagnoses[0]->disorder_id);
		$this->assertInstanceOf('Eye',$patient->systemicDiagnoses[0]->eye);
		$this->assertEquals('Left',$patient->systemicDiagnoses[0]->eye->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Left'))->id,$patient->systemicDiagnoses[0]->eye_id);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->systemicDiagnoses[1]);
		$this->assertInstanceOf('Disorder',$patient->systemicDiagnoses[1]->disorder);
		$this->assertEquals('Essential hypertension',$patient->systemicDiagnoses[1]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Essential hypertension'))->id,$patient->systemicDiagnoses[1]->disorder_id);
		$this->assertNull($patient->systemicDiagnoses[1]->eye);
		$this->assertNull($patient->systemicDiagnoses[1]->eye_id);
	}

	public function testJsonToModel_Save_Update_DBIsCorrect()
	{
		$json = '{"diagnoses":[{"disorder":"Diabetes mellitus type 1","side":"Left","id":null,"last_modified":null},{"disorder":"Essential hypertension","side":false,"id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800}}';

		$ps = new PatientSystemicDiagnosesService;
		$patient = $ps->jsonToModel($json, $this->patients('patient4'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->systemicDiagnoses);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->systemicDiagnoses[0]);
		$this->assertInstanceOf('Disorder',$patient->systemicDiagnoses[0]->disorder);
		$this->assertEquals('Diabetes mellitus type 1',$patient->systemicDiagnoses[0]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Diabetes mellitus type 1'))->id,$patient->systemicDiagnoses[0]->disorder_id);
		$this->assertInstanceOf('Eye',$patient->systemicDiagnoses[0]->eye);
		$this->assertEquals('Left',$patient->systemicDiagnoses[0]->eye->name);		$this->assertEquals(\Eye::model()->find('name=?',array('Left'))->id,$patient->systemicDiagnoses[0]->eye_id);
		$this->assertInstanceOf('SecondaryDiagnosis',$patient->systemicDiagnoses[1]);
		$this->assertInstanceOf('Disorder',$patient->systemicDiagnoses[1]->disorder);
		$this->assertEquals('Essential hypertension',$patient->systemicDiagnoses[1]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Essential hypertension'))->id,$patient->systemicDiagnoses[1]->disorder_id);
		$this->assertNull($patient->systemicDiagnoses[1]->eye);
		$this->assertNull($patient->systemicDiagnoses[1]->eye_id);
	}

	public function testJsonToModel_Save_Update_NonSystemic_Exception()
	{
		$json = '{"diagnoses":[{"disorder":"Myopia","side":"Left","id":null,"last_modified":null},{"disorder":"Essential hypertension","side":false,"id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800}}';

		$this->setExpectedException('Exception','PatientSystemicDiagnoses passed a resource containing ophthalmic diagnoses');

		$ps = new PatientSystemicDiagnosesService;
		$patient = $ps->jsonToModel($json, $this->patients('patient2'));
	}
}
