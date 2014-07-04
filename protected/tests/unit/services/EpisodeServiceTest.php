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

class EpisodeServiceTest extends \CDbTestCase
{
	public $fixtures = array(
		'patients' => 'Patient',
		'episodes' => 'Episode',
		'disorders' => 'Disorder',
		'specialties' => 'Specialty',
		'subspecialties' => 'Subspecialty',
		'ssa' => 'ServiceSubspecialtyAssignment',
		'firms' => 'Firm',
	);

	public function testModelToResource()
	{
		$episode = $this->episodes('episode5');

		$ps = new EpisodeService;

		$resource = $ps->modelToResource($episode);

		$this->assertInstanceOf('services\Episode',$resource);

		$this->assertInstanceOf('services\PatientReference',$resource->patient_ref);
		$this->assertEquals(1,$resource->patient_ref->getId());
		$this->assertEquals('Aylward Firm',$resource->firm);
		$this->assertEquals('Subspecialty 1',$resource->subspecialty);
		$this->assertInstanceOf('services\Date',$resource->start_date);
		$this->assertNull($resource->end_date);
		$this->assertEquals('New',$resource->status);
		$this->assertEquals(0,$resource->legacy);
		$this->assertEquals(0,$resource->deleted);
		$this->assertEquals('Left',$resource->eye);
		$this->assertEquals('Posterior vitreous detachment',$resource->disorder);
		$this->assertEquals(0,$resource->support_services);
	}

	public function getResource()
	{
		$episode = new Episode;

		$episode->patient_ref = \Yii::app()->service->Patient(1);
		$episode->firm = 'Aylward Firm';
		$episode->subspecialty = 'Subspecialty 2';
		$episode->status = 'Post-op';
		$episode->start_date = new Date('2013-05-04');
		$episode->eye = 'Right';
		$episode->disorder = 'Myopia';

		return $episode;
	}

	public function testResourceToModel_NoSave_NoNewRecords()
	{
		$resource = $this->getResource();

		$total_e = count(\Episode::model()->findAll());

		$ps = new EpisodeService;
		$episode = $ps->resourceToModel($resource, new \Episode, false);

		$this->assertEquals($total_e, count(\Episode::model()->findAll()));
	}

	public function testResourceToModel_NoSave_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new EpisodeService;
		$episode = $ps->resourceToModel($resource, new \Episode, false);

		$this->assertInstanceOf('Episode',$episode);
		$this->assertInstanceOf('Patient',$episode->patient);
		$this->assertEquals(1,$episode->patient->id);
		$this->assertEquals(1,$episode->patient_id);
		$this->assertInstanceOf('Firm',$episode->firm);
		$this->assertEquals('Aylward Firm',$episode->firm->name);
		$this->assertEquals(\Firm::model()->find('name=? and service_subspecialty_assignment_id=?',array('Aylward Firm',2))->id,$episode->firm_id);
		$this->assertInstanceOf('ServiceSubspecialtyAssignment',$episode->firm->serviceSubspecialtyAssignment);
		$this->assertInstanceOf('Subspecialty',$episode->firm->serviceSubspecialtyAssignment->subspecialty);
		$this->assertEquals('Subspecialty 2',$episode->firm->serviceSubspecialtyAssignment->subspecialty->name);
		$this->assertInstanceOf('EpisodeStatus',$episode->status);
		$this->assertEquals('Post-op',$episode->status->name);
		$this->assertEquals(\EpisodeStatus::model()->find('name=?',array('Post-op'))->id,$episode->episode_status_id);
		$this->assertEquals('2013-05-04',$episode->start_date);
		$this->assertNull($episode->end_date);
		$this->assertInstanceOf('Eye',$episode->eye);
		$this->assertEquals('Right',$episode->eye->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Right'))->id,$episode->eye_id);
		$this->assertInstanceOf('Disorder',$episode->diagnosis);
		$this->assertEquals('Myopia',$episode->diagnosis->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Myopia'))->id,$episode->disorder_id);
	}

/*
	public function testResourceToModel_Save_Create_ModelCountsCorrect()
	{
		$resource = $this->getResource();

		$total_sd = count(\SecondaryDiagnosis::model()->findAll());

		$ps = new PatientOphthalmicDiagnosesService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient3'));

		$this->assertEquals($total_sd+2, count(\SecondaryDiagnosis::model()->findAll()));
	}

	public function testResourceToModel_Save_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientOphthalmicDiagnosesService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient3'));

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->ophthalmicDiagnoses);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->ophthalmicDiagnoses[0]);
		$this->assertInstanceOf('Disorder',$patient->ophthalmicDiagnoses[0]->disorder);
		$this->assertEquals('Myopia',$patient->ophthalmicDiagnoses[0]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Myopia'))->id,$patient->ophthalmicDiagnoses[0]->disorder_id);
		$this->assertInstanceOf('Eye',$patient->ophthalmicDiagnoses[0]->eye);
		$this->assertEquals('Both',$patient->ophthalmicDiagnoses[0]->eye->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Both'))->id,$patient->ophthalmicDiagnoses[0]->eye_id);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->ophthalmicDiagnoses[1]);
		$this->assertInstanceOf('Disorder',$patient->ophthalmicDiagnoses[1]->disorder);
		$this->assertEquals('Retinal lattice degeneration',$patient->ophthalmicDiagnoses[1]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Retinal lattice degeneration'))->id,$patient->ophthalmicDiagnoses[1]->disorder_id);
		$this->assertInstanceOf('Eye',$patient->ophthalmicDiagnoses[1]->eye);
		$this->assertEquals('Right',$patient->ophthalmicDiagnoses[1]->eye->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Right'))->id,$patient->ophthalmicDiagnoses[1]->eye_id);
	}

	public function testResourceToModel_Save_Create_DBIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientOphthalmicDiagnosesService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient3'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->ophthalmicDiagnoses);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->ophthalmicDiagnoses[0]);
		$this->assertInstanceOf('Disorder',$patient->ophthalmicDiagnoses[0]->disorder);
		$this->assertEquals('Myopia',$patient->ophthalmicDiagnoses[0]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Myopia'))->id,$patient->ophthalmicDiagnoses[0]->disorder_id);
		$this->assertInstanceOf('Eye',$patient->ophthalmicDiagnoses[0]->eye);
		$this->assertEquals('Both',$patient->ophthalmicDiagnoses[0]->eye->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Both'))->id,$patient->ophthalmicDiagnoses[0]->eye_id);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->ophthalmicDiagnoses[1]);
		$this->assertInstanceOf('Disorder',$patient->ophthalmicDiagnoses[1]->disorder);
		$this->assertEquals('Retinal lattice degeneration',$patient->ophthalmicDiagnoses[1]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Retinal lattice degeneration'))->id,$patient->ophthalmicDiagnoses[1]->disorder_id);
		$this->assertInstanceOf('Eye',$patient->ophthalmicDiagnoses[1]->eye);
		$this->assertEquals('Right',$patient->ophthalmicDiagnoses[1]->eye->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Right'))->id,$patient->ophthalmicDiagnoses[1]->eye_id);
	}

	public function testResourceToModel_Save_Create_NonOphthalmic_Exception()
	{
		$resource = $this->getResource();

		$resource->diagnoses[0]->disorder = 'Diabetes mellitus type 1';

		$this->setExpectedException('Exception','PatientOphthalmicDiagnoses passed a resource containing non-ophthalmic diagnoses');

		$ps = new PatientOphthalmicDiagnosesService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient3'));
	}

	public function testResourceToModel_Save_Update_Modified_ModelCountsCorrect()
	{
		$resource = $this->getResource();

		$total_sd = count(\SecondaryDiagnosis::model()->findAll());

		$ps = new PatientOphthalmicDiagnosesService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient2'));

		$this->assertEquals($total_sd-1, count(\SecondaryDiagnosis::model()->findAll()));
	}

	public function testResourceToModel_Save_Update_NotModified_ModelCountsCorrect()
	{
		$resource = \Yii::app()->service->PatientOphthalmicDiagnoses(2)->fetch();

		$total_sd = count(\SecondaryDiagnosis::model()->findAll());

		$ps = new PatientOphthalmicDiagnosesService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient2'));

		$this->assertEquals($total_sd, count(\SecondaryDiagnosis::model()->findAll()));
	}

	public function testResourceToModel_Save_Update_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientOphthalmicDiagnosesService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient2'));

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->ophthalmicDiagnoses);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->ophthalmicDiagnoses[0]);
		$this->assertInstanceOf('Disorder',$patient->ophthalmicDiagnoses[0]->disorder);
		$this->assertEquals('Myopia',$patient->ophthalmicDiagnoses[0]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Myopia'))->id,$patient->ophthalmicDiagnoses[0]->disorder_id);
		$this->assertInstanceOf('Eye',$patient->ophthalmicDiagnoses[0]->eye);
		$this->assertEquals('Both',$patient->ophthalmicDiagnoses[0]->eye->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Both'))->id,$patient->ophthalmicDiagnoses[0]->eye_id);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->ophthalmicDiagnoses[1]);
		$this->assertInstanceOf('Disorder',$patient->ophthalmicDiagnoses[1]->disorder);
		$this->assertEquals('Retinal lattice degeneration',$patient->ophthalmicDiagnoses[1]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Retinal lattice degeneration'))->id,$patient->ophthalmicDiagnoses[1]->disorder_id);
		$this->assertInstanceOf('Eye',$patient->ophthalmicDiagnoses[1]->eye);
		$this->assertEquals('Right',$patient->ophthalmicDiagnoses[1]->eye->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Right'))->id,$patient->ophthalmicDiagnoses[1]->eye_id);
	}

	public function testResourceToModel_Save_Update_DBIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientOphthalmicDiagnosesService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient2'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->ophthalmicDiagnoses);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->ophthalmicDiagnoses[0]);
		$this->assertInstanceOf('Disorder',$patient->ophthalmicDiagnoses[0]->disorder);
		$this->assertEquals('Myopia',$patient->ophthalmicDiagnoses[0]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Myopia'))->id,$patient->ophthalmicDiagnoses[0]->disorder_id);
		$this->assertInstanceOf('Eye',$patient->ophthalmicDiagnoses[0]->eye);
		$this->assertEquals('Both',$patient->ophthalmicDiagnoses[0]->eye->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Both'))->id,$patient->ophthalmicDiagnoses[0]->eye_id);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->ophthalmicDiagnoses[1]);
		$this->assertInstanceOf('Disorder',$patient->ophthalmicDiagnoses[1]->disorder);
		$this->assertEquals('Retinal lattice degeneration',$patient->ophthalmicDiagnoses[1]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Retinal lattice degeneration'))->id,$patient->ophthalmicDiagnoses[1]->disorder_id);
		$this->assertInstanceOf('Eye',$patient->ophthalmicDiagnoses[1]->eye);
		$this->assertEquals('Right',$patient->ophthalmicDiagnoses[1]->eye->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Right'))->id,$patient->ophthalmicDiagnoses[1]->eye_id);
	}

	public function testResourceToModel_Save_Update_NotOphthalmic_Exception()
	{
		$resource = $this->getResource();

		$resource->diagnoses[0]->disorder = 'Diabetes mellitus type 1';

		$this->setExpectedException('Exception','PatientOphthalmicDiagnoses passed a resource containing non-ophthalmic diagnoses');

		$ps = new PatientOphthalmicDiagnosesService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient2'));
	}

	public function testJsonToResource()
	{
		$json = '{"diagnoses":[{"disorder":"Myopia","side":"Left","id":null,"last_modified":null},{"disorder":"Retinal lattice degeneration","side":false,"id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800}}';

		$ps = new PatientOphthalmicDiagnosesService;
		$resource = $ps->jsonToResource($json);

		$this->assertInstanceOf('services\PatientOphthalmicDiagnoses',$resource);
		$this->assertCount(2,$resource->diagnoses);

		$this->assertInstanceOf('services\PatientDiagnosis',$resource->diagnoses[0]);
		$this->assertEquals('Myopia',$resource->diagnoses[0]->disorder);
		$this->assertEquals('Left',$resource->diagnoses[0]->side);

		$this->assertInstanceOf('services\PatientDiagnosis',$resource->diagnoses[1]);
		$this->assertEquals('Retinal lattice degeneration',$resource->diagnoses[1]->disorder);
		$this->assertFalse($resource->diagnoses[1]->side);
	}

	public function testJsonToModel_NoSave_NoNewRows()
	{
		$json = '{"diagnoses":[{"disorder":"Myopia","side":"Left","id":null,"last_modified":null},{"disorder":"Retinal lattice degeneration","side":false,"id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800}}';

		$total_sd = count(\SecondaryDiagnosis::model()->findAll());

		$ps = new PatientOphthalmicDiagnosesService;
		$patient = $ps->jsonToModel($json, $this->patients('patient3'), false);

		$this->assertEquals($total_sd, count(\SecondaryDiagnosis::model()->findAll()));
	}

	public function testJsonToModel_NoSave_ModelIsCorrect()
	{
		$json = '{"diagnoses":[{"disorder":"Myopia","side":"Left","id":null,"last_modified":null},{"disorder":"Retinal lattice degeneration","side":false,"id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800}}';

		$ps = new PatientOphthalmicDiagnosesService;
		$patient = $ps->jsonToModel($json, new \Patient, false);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->ophthalmicDiagnoses);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->ophthalmicDiagnoses[0]);
		$this->assertInstanceOf('Disorder',$patient->ophthalmicDiagnoses[0]->disorder);
		$this->assertEquals('Myopia',$patient->ophthalmicDiagnoses[0]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Myopia'))->id,$patient->ophthalmicDiagnoses[0]->disorder_id);
		$this->assertInstanceOf('Eye',$patient->ophthalmicDiagnoses[0]->eye);
		$this->assertEquals('Left',$patient->ophthalmicDiagnoses[0]->eye->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Left'))->id,$patient->ophthalmicDiagnoses[0]->eye_id);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->ophthalmicDiagnoses[1]);
		$this->assertInstanceOf('Disorder',$patient->ophthalmicDiagnoses[1]->disorder);
		$this->assertEquals('Retinal lattice degeneration',$patient->ophthalmicDiagnoses[1]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Retinal lattice degeneration'))->id,$patient->ophthalmicDiagnoses[1]->disorder_id);
		$this->assertNull($patient->ophthalmicDiagnoses[1]->eye);
		$this->assertNull($patient->ophthalmicDiagnoses[1]->eye_id);
	}

	public function testJsonToModel_Save_Create_ModelCountsCorrect()
	{
		$json = '{"diagnoses":[{"disorder":"Myopia","side":"Left","id":null,"last_modified":null},{"disorder":"Retinal lattice degeneration","side":false,"id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800}}';

		$total_sd = count(\SecondaryDiagnosis::model()->findAll());

		$ps = new PatientOphthalmicDiagnosesService;
		$patient = $ps->jsonToModel($json, $this->patients('patient4'));

		$this->assertEquals($total_sd+2, count(\SecondaryDiagnosis::model()->findAll()));
	}

	public function testJsonToModel_Save_Create_ModelIsCorrect()
	{
		$json = '{"diagnoses":[{"disorder":"Myopia","side":"Left","id":null,"last_modified":null},{"disorder":"Retinal lattice degeneration","side":false,"id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800}}';

		$ps = new PatientOphthalmicDiagnosesService;
		$patient = $ps->jsonToModel($json, $this->patients('patient4'));

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->ophthalmicDiagnoses);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->ophthalmicDiagnoses[0]);
		$this->assertInstanceOf('Disorder',$patient->ophthalmicDiagnoses[0]->disorder);
		$this->assertEquals('Myopia',$patient->ophthalmicDiagnoses[0]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Myopia'))->id,$patient->ophthalmicDiagnoses[0]->disorder_id);
		$this->assertInstanceOf('Eye',$patient->ophthalmicDiagnoses[0]->eye);
		$this->assertEquals('Left',$patient->ophthalmicDiagnoses[0]->eye->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Left'))->id,$patient->ophthalmicDiagnoses[0]->eye_id);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->ophthalmicDiagnoses[1]);
		$this->assertInstanceOf('Disorder',$patient->ophthalmicDiagnoses[1]->disorder);
		$this->assertEquals('Retinal lattice degeneration',$patient->ophthalmicDiagnoses[1]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Retinal lattice degeneration'))->id,$patient->ophthalmicDiagnoses[1]->disorder_id);
		$this->assertNull($patient->ophthalmicDiagnoses[1]->eye);
		$this->assertNull($patient->ophthalmicDiagnoses[1]->eye_id);
	}

	public function testJsonToModel_Save_Create_DBIsCorrect()
	{
		$json = '{"diagnoses":[{"disorder":"Myopia","side":"Left","id":null,"last_modified":null},{"disorder":"Retinal lattice degeneration","side":false,"id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800}}';

		$ps = new PatientOphthalmicDiagnosesService;
		$patient = $ps->jsonToModel($json, $this->patients('patient4'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->ophthalmicDiagnoses);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->ophthalmicDiagnoses[0]);
		$this->assertInstanceOf('Disorder',$patient->ophthalmicDiagnoses[0]->disorder);
		$this->assertEquals('Myopia',$patient->ophthalmicDiagnoses[0]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Myopia'))->id,$patient->ophthalmicDiagnoses[0]->disorder_id);
		$this->assertInstanceOf('Eye',$patient->ophthalmicDiagnoses[0]->eye);
		$this->assertEquals('Left',$patient->ophthalmicDiagnoses[0]->eye->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Left'))->id,$patient->ophthalmicDiagnoses[0]->eye_id);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->ophthalmicDiagnoses[1]);
		$this->assertInstanceOf('Disorder',$patient->ophthalmicDiagnoses[1]->disorder);
		$this->assertEquals('Retinal lattice degeneration',$patient->ophthalmicDiagnoses[1]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Retinal lattice degeneration'))->id,$patient->ophthalmicDiagnoses[1]->disorder_id);
		$this->assertNull($patient->ophthalmicDiagnoses[1]->eye);
		$this->assertNull($patient->ophthalmicDiagnoses[1]->eye_id);
	}

	public function testJsonToModel_Save_Create_NonOphthalmic_Exception()
	{
		$json = '{"diagnoses":[{"disorder":"Diabetes mellitus type 1","side":"Left","id":null,"last_modified":null},{"disorder":"Essential hypertension","side":false,"id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800}}';

		$this->setExpectedException('Exception','PatientOphthalmicDiagnoses passed a resource containing non-ophthalmic diagnoses');

		$ps = new PatientOphthalmicDiagnosesService;
		$patient = $ps->jsonToModel($json, $this->patients('patient3'));
	}

	public function testJsonToModel_Save_Update_ModelCountsCorrect()
	{
		$json = '{"diagnoses":[{"disorder":"Myopia","side":"Left","id":null,"last_modified":null},{"disorder":"Retinal lattice degeneration","side":false,"id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800}}';

		$total_sd = count(\SecondaryDiagnosis::model()->findAll());

		$ps = new PatientOphthalmicDiagnosesService;
		$patient = $ps->jsonToModel($json, $this->patients('patient2'));

		$this->assertEquals($total_sd-1, count(\SecondaryDiagnosis::model()->findAll()));
	}

	public function testJsonToModel_Save_Update_ModelIsCorrect()
	{
		$json = '{"diagnoses":[{"disorder":"Myopia","side":"Left","id":null,"last_modified":null},{"disorder":"Retinal lattice degeneration","side":false,"id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800}}';

		$ps = new PatientOphthalmicDiagnosesService;
		$patient = $ps->jsonToModel($json, $this->patients('patient4'));

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->ophthalmicDiagnoses);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->ophthalmicDiagnoses[0]);
		$this->assertInstanceOf('Disorder',$patient->ophthalmicDiagnoses[0]->disorder);
		$this->assertEquals('Myopia',$patient->ophthalmicDiagnoses[0]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Myopia'))->id,$patient->ophthalmicDiagnoses[0]->disorder_id);
		$this->assertInstanceOf('Eye',$patient->ophthalmicDiagnoses[0]->eye);
		$this->assertEquals('Left',$patient->ophthalmicDiagnoses[0]->eye->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Left'))->id,$patient->ophthalmicDiagnoses[0]->eye_id);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->ophthalmicDiagnoses[1]);
		$this->assertInstanceOf('Disorder',$patient->ophthalmicDiagnoses[1]->disorder);
		$this->assertEquals('Retinal lattice degeneration',$patient->ophthalmicDiagnoses[1]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Retinal lattice degeneration'))->id,$patient->ophthalmicDiagnoses[1]->disorder_id);
		$this->assertNull($patient->ophthalmicDiagnoses[1]->eye);
		$this->assertNull($patient->ophthalmicDiagnoses[1]->eye_id);
	}

	public function testJsonToModel_Save_Update_DBIsCorrect()
	{
		$json = '{"diagnoses":[{"disorder":"Myopia","side":"Left","id":null,"last_modified":null},{"disorder":"Retinal lattice degeneration","side":false,"id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800}}';

		$ps = new PatientOphthalmicDiagnosesService;
		$patient = $ps->jsonToModel($json, $this->patients('patient4'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->ophthalmicDiagnoses);

		$this->assertInstanceOf('SecondaryDiagnosis',$patient->ophthalmicDiagnoses[0]);
		$this->assertInstanceOf('Disorder',$patient->ophthalmicDiagnoses[0]->disorder);
		$this->assertEquals('Myopia',$patient->ophthalmicDiagnoses[0]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Myopia'))->id,$patient->ophthalmicDiagnoses[0]->disorder_id);
		$this->assertInstanceOf('Eye',$patient->ophthalmicDiagnoses[0]->eye);
		$this->assertEquals('Left',$patient->ophthalmicDiagnoses[0]->eye->name);		$this->assertEquals(\Eye::model()->find('name=?',array('Left'))->id,$patient->ophthalmicDiagnoses[0]->eye_id);
		$this->assertInstanceOf('SecondaryDiagnosis',$patient->ophthalmicDiagnoses[1]);
		$this->assertInstanceOf('Disorder',$patient->ophthalmicDiagnoses[1]->disorder);
		$this->assertEquals('Retinal lattice degeneration',$patient->ophthalmicDiagnoses[1]->disorder->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Retinal lattice degeneration'))->id,$patient->ophthalmicDiagnoses[1]->disorder_id);
		$this->assertNull($patient->ophthalmicDiagnoses[1]->eye);
		$this->assertNull($patient->ophthalmicDiagnoses[1]->eye_id);
	}

	public function testJsonToModel_Save_Update_NonOphthalmic_Exception()
	{
		$json = '{"diagnoses":[{"disorder":"Diabetes mellitus type 1","side":"Left","id":null,"last_modified":null},{"disorder":"Retinal lattice degeneration","side":false,"id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800}}';

		$this->setExpectedException('Exception','PatientOphthalmicDiagnoses passed a resource containing non-ophthalmic diagnoses');

		$ps = new PatientOphthalmicDiagnosesService;
		$patient = $ps->jsonToModel($json, $this->patients('patient2'));
	}
	*/
}
