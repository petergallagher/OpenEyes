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

		$episode->patient_ref = \Yii::app()->service->Patient(4);
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
		$this->assertEquals(4,$episode->patient->id);
		$this->assertEquals(4,$episode->patient_id);
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

	public function testResourceToModel_Save_Create_ModelCountsCorrect()
	{
		$resource = $this->getResource();

		$total_e = count(\Episode::model()->findAll());

		$ps = new EpisodeService;
		$episode = $ps->resourceToModel($resource, new \Episode);

		$this->assertEquals($total_e+1, count(\Episode::model()->findAll()));
	}

	public function testResourceToModel_Save_Create_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new EpisodeService;
		$episode = $ps->resourceToModel($resource, new \Episode);

		$this->assertInstanceOf('Episode',$episode);
		$this->assertInstanceOf('Patient',$episode->patient);
		$this->assertEquals(4,$episode->patient->id);
		$this->assertEquals(4,$episode->patient_id);
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

	public function testResourceToModel_Save_Create_DBIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new EpisodeService;
		$episode = $ps->resourceToModel($resource, new \Episode);

		$this->assertInstanceOf('Episode',$episode);
		$this->assertInstanceOf('Patient',$episode->patient);
		$this->assertEquals(4,$episode->patient->id);
		$this->assertEquals(4,$episode->patient_id);
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

	public function testResourceToModel_Save_Create_CantCreateEpisodeInSameSubspecialty()
	{
		$resource = $this->getResource();
		$resource->patient_ref = \Yii::app()->service->Patient(1);

		$this->setExpectedException('Exception','There is already an open Subspecialty 2 episode for this patient.');

		$ps = new EpisodeService;
		$episode = $ps->resourceToModel($resource, new \Episode);
	}

	public function testResourceToModel_Save_Create_CantCreateLegacyEpisodeIfOneAlreadyExists()
	{
		$resource = $this->getResource();
		$resource->firm = null;
		$resource->subspecialty = null;
		$resource->legacy = 1;
		$resource->patient_ref = \Yii::app()->service->Patient(3);

		$this->setExpectedException('Exception','There is already a legacy episode for this patient.');

		$ps = new EpisodeService;
		$episode = $ps->resourceToModel($resource, new \Episode);
	}

	public function testResourceToModel_Save_Create_CantCreateSupportServicesEpisodeIfOneAlreadyExists()
	{
		$resource = $this->getResource();
		$resource->firm = null;
		$resource->subspecialty = null;
		$resource->support_services = 1;
		$resource->patient_ref = \Yii::app()->service->Patient(3);

		$this->setExpectedException('Exception','There is already a support services episode for this patient.');

		$ps = new EpisodeService;
		$episode = $ps->resourceToModel($resource, new \Episode);
	}

	public function testResourceToModel_Save_Create_CantNoSubspecialtyFirmIfAlreadyExists()
	{
		$resource = $this->getResource();
		$resource->firm = $this->firms('firm6')->name;
		$resource->subspecialty = null;
		$resource->patient_ref = \Yii::app()->service->Patient(3);

		$this->setExpectedException('Exception','There is already an open No subspecialty firm episode for this patient.');

		$ps = new EpisodeService;
		$episode = $ps->resourceToModel($resource, new \Episode);
	}

	public function testResourceToModel_Save_Update_Modified_ModelCountsCorrect()
	{
		$resource = $this->getResource();

		$total_e	= count(\Episode::model()->findAll());

		$ps = new EpisodeService;
		$episode = $ps->resourceToModel($resource, $this->episodes('episode6'));

		$this->assertEquals($total_e, count(\Episode::model()->findAll()));
	}

	public function testResourceToModel_Save_Update_NotModified_ModelCountsCorrect()
	{
		$resource = \Yii::app()->service->Episode(2)->fetch();

		$total_e	= count(\Episode::model()->findAll());

		$ps = new EpisodeService;
		$episode = $ps->resourceToModel($resource, $this->episodes('episode2'));

		$this->assertEquals($total_e, count(\Episode::model()->findAll()));
	}

	public function testResourceToModel_Save_Update_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new EpisodeService;
		$episode = $ps->resourceToModel($resource, $this->episodes('episode2'));

		$this->assertInstanceOf('Episode',$episode);
		$this->assertInstanceOf('Patient',$episode->patient);
		$this->assertEquals(4,$episode->patient->id);
		$this->assertEquals(4,$episode->patient_id);
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

	public function testResourceToModel_Save_Update_DBIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new EpisodeService;
		$episode = $ps->resourceToModel($resource, $this->episodes('episode2'));
		$episode = \Episode::model()->findByPk($episode->id);

		$this->assertInstanceOf('Episode',$episode);
		$this->assertInstanceOf('Patient',$episode->patient);
		$this->assertEquals(4,$episode->patient->id);
		$this->assertEquals(4,$episode->patient_id);
		$this->assertInstanceOf('Firm',$episode->firm);
		$this->assertEquals('Aylward Firm',$episode->firm->name);
		$this->assertEquals(\Firm::model()->find('name=? and service_subspecialty_assignment_id=?',array('Aylward Firm',2))->id,$episode->firm_id);
		$this->assertInstanceOf('ServiceSubspecialtyAssignment',$episode->firm->serviceSubspecialtyAssignment);
		$this->assertInstanceOf('Subspecialty',$episode->firm->serviceSubspecialtyAssignment->subspecialty);
		$this->assertEquals('Subspecialty 2',$episode->firm->serviceSubspecialtyAssignment->subspecialty->name);
		$this->assertInstanceOf('EpisodeStatus',$episode->status);
		$this->assertEquals('Post-op',$episode->status->name);
		$this->assertEquals(\EpisodeStatus::model()->find('name=?',array('Post-op'))->id,$episode->episode_status_id);
		$this->assertEquals('2013-05-04 00:00:00',$episode->start_date);
		$this->assertNull($episode->end_date);
		$this->assertInstanceOf('Eye',$episode->eye);
		$this->assertEquals('Right',$episode->eye->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Right'))->id,$episode->eye_id);
		$this->assertInstanceOf('Disorder',$episode->diagnosis);
		$this->assertEquals('Myopia',$episode->diagnosis->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Myopia'))->id,$episode->disorder_id);
	}

	public function testJsonToResource()
	{
		$json = \Yii::app()->service->Episode(5)->fetch()->serialise();

		$ps = new EpisodeService;
		$resource = $ps->jsonToResource($json);

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

	public function testJsonToModel_NoSave_NoNewRows()
	{
		$json = \Yii::app()->service->Episode(5)->fetch()->serialise();

		$total_e = count(\Episode::model()->findAll());

		$ps = new EpisodeService;
		$episode = $ps->jsonToModel($json, new \Episode, false);

		$this->assertEquals($total_e, count(\Episode::model()->findAll()));
	}

	public function testJsonToModel_NoSave_ModelIsCorrect()
	{
		$json = \Yii::app()->service->Episode(5)->fetch()->serialise();

		$ps = new EpisodeService;
		$episode = $ps->jsonToModel($json, new \Episode, false);

		$this->assertInstanceOf('Episode',$episode);
		$this->assertInstanceOf('Patient',$episode->patient);
		$this->assertEquals(1,$episode->patient->id);
		$this->assertEquals(1,$episode->patient_id);
		$this->assertInstanceOf('Firm',$episode->firm);
		$this->assertEquals('Aylward Firm',$episode->firm->name);
		$this->assertEquals(\Firm::model()->find('name=? and service_subspecialty_assignment_id=?',array('Aylward Firm',1))->id,$episode->firm_id);
		$this->assertInstanceOf('ServiceSubspecialtyAssignment',$episode->firm->serviceSubspecialtyAssignment);
		$this->assertInstanceOf('Subspecialty',$episode->firm->serviceSubspecialtyAssignment->subspecialty);
		$this->assertEquals('Subspecialty 1',$episode->firm->serviceSubspecialtyAssignment->subspecialty->name);
		$this->assertInstanceOf('EpisodeStatus',$episode->status);
		$this->assertEquals('New',$episode->status->name);
		$this->assertEquals(\EpisodeStatus::model()->find('name=?',array('New'))->id,$episode->episode_status_id);
		$this->assertEquals(substr($this->episodes('episode5')->start_date,0,10),$episode->start_date);
		$this->assertNull($episode->end_date);
		$this->assertInstanceOf('Eye',$episode->eye);
		$this->assertEquals('Left',$episode->eye->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Left'))->id,$episode->eye_id);
		$this->assertInstanceOf('Disorder',$episode->diagnosis);
		$this->assertEquals('Posterior vitreous detachment',$episode->diagnosis->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Posterior vitreous detachment'))->id,$episode->disorder_id);
	}

	public function testJsonToModel_Save_Create_ModelCountsCorrect()
	{
		$resource = \Yii::app()->service->Episode(5)->fetch();
		$resource->patient_ref = \Yii::app()->service->Patient(2);

		$json = $resource->serialise();

		$total_e = count(\Episode::model()->findAll());

		$ps = new EpisodeService;
		$episode = $ps->jsonToModel($json, new \Episode);

		$this->assertEquals($total_e+1, count(\Episode::model()->findAll()));
	}

	public function testJsonToModel_Save_Create_ModelIsCorrect()
	{
		$resource = \Yii::app()->service->Episode(5)->fetch();
		$resource->patient_ref = \Yii::app()->service->Patient(2);

		$json = $resource->serialise();

		$ps = new EpisodeService;
		$episode = $ps->jsonToModel($json, new \Episode);

		$this->assertInstanceOf('Episode',$episode);
		$this->assertInstanceOf('Patient',$episode->patient);
		$this->assertEquals(2,$episode->patient->id);
		$this->assertEquals(2,$episode->patient_id);
		$this->assertInstanceOf('Firm',$episode->firm);
		$this->assertEquals('Aylward Firm',$episode->firm->name);
		$this->assertEquals(\Firm::model()->find('name=? and service_subspecialty_assignment_id=?',array('Aylward Firm',1))->id,$episode->firm_id);
		$this->assertInstanceOf('ServiceSubspecialtyAssignment',$episode->firm->serviceSubspecialtyAssignment);
		$this->assertInstanceOf('Subspecialty',$episode->firm->serviceSubspecialtyAssignment->subspecialty);
		$this->assertEquals('Subspecialty 1',$episode->firm->serviceSubspecialtyAssignment->subspecialty->name);
		$this->assertInstanceOf('EpisodeStatus',$episode->status);
		$this->assertEquals('New',$episode->status->name);
		$this->assertEquals(\EpisodeStatus::model()->find('name=?',array('New'))->id,$episode->episode_status_id);
		$this->assertEquals(substr($this->episodes('episode5')->start_date,0,10),$episode->start_date);
		$this->assertNull($episode->end_date);
		$this->assertInstanceOf('Eye',$episode->eye);
		$this->assertEquals('Left',$episode->eye->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Left'))->id,$episode->eye_id);
		$this->assertInstanceOf('Disorder',$episode->diagnosis);
		$this->assertEquals('Posterior vitreous detachment',$episode->diagnosis->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Posterior vitreous detachment'))->id,$episode->disorder_id);
	}

	public function testJsonToModel_Save_Create_DBIsCorrect()
	{
		$resource = \Yii::app()->service->Episode(5)->fetch();
		$resource->patient_ref = \Yii::app()->service->Patient(2);

		$json = $resource->serialise();

		$ps = new EpisodeService;
		$episode = $ps->jsonToModel($json, new \Episode);
		$episode = \Episode::model()->findByPk($episode->id);

		$this->assertInstanceOf('Episode',$episode);
		$this->assertInstanceOf('Patient',$episode->patient);
		$this->assertEquals(2,$episode->patient->id);
		$this->assertEquals(2,$episode->patient_id);
		$this->assertInstanceOf('Firm',$episode->firm);
		$this->assertEquals('Aylward Firm',$episode->firm->name);
		$this->assertEquals(\Firm::model()->find('name=? and service_subspecialty_assignment_id=?',array('Aylward Firm',1))->id,$episode->firm_id);
		$this->assertInstanceOf('ServiceSubspecialtyAssignment',$episode->firm->serviceSubspecialtyAssignment);
		$this->assertInstanceOf('Subspecialty',$episode->firm->serviceSubspecialtyAssignment->subspecialty);
		$this->assertEquals('Subspecialty 1',$episode->firm->serviceSubspecialtyAssignment->subspecialty->name);
		$this->assertInstanceOf('EpisodeStatus',$episode->status);
		$this->assertEquals('New',$episode->status->name);
		$this->assertEquals(\EpisodeStatus::model()->find('name=?',array('New'))->id,$episode->episode_status_id);
		$this->assertEquals(substr($this->episodes('episode5')->start_date,0,10).' 00:00:00',$episode->start_date);
		$this->assertNull($episode->end_date);
		$this->assertInstanceOf('Eye',$episode->eye);
		$this->assertEquals('Left',$episode->eye->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Left'))->id,$episode->eye_id);
		$this->assertInstanceOf('Disorder',$episode->diagnosis);
		$this->assertEquals('Posterior vitreous detachment',$episode->diagnosis->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Posterior vitreous detachment'))->id,$episode->disorder_id);
	}

	public function testJsonToModel_Save_Create_CantCreateEpisodeInSameSubspecialty()
	{
		$resource = $this->getResource();
		$resource->patient_ref = \Yii::app()->service->Patient(1);

		$json = $resource->serialise();

		$this->setExpectedException('Exception','There is already an open Subspecialty 2 episode for this patient.');

		$ps = new EpisodeService;
		$episode = $ps->jsonToModel($json, new \Episode);
	}

	public function testJsonToModel_Save_Create_CantCreateLegacyEpisodeIfOneAlreadyExists()
	{
		$resource = $this->getResource();
		$resource->firm = null;
		$resource->subspecialty = null;
		$resource->legacy = 1;
		$resource->patient_ref = \Yii::app()->service->Patient(3);

		$json = $resource->serialise();

		$this->setExpectedException('Exception','There is already a legacy episode for this patient.');

		$ps = new EpisodeService;
		$episode = $ps->jsonToModel($json, new \Episode);
	}

	public function testJsonToModel_Save_Create_CantCreateSupportServicesEpisodeIfOneAlreadyExists()
	{
		$resource = $this->getResource();
		$resource->firm = null;
		$resource->subspecialty = null;
		$resource->support_services = 1;
		$resource->patient_ref = \Yii::app()->service->Patient(3);

		$json = $resource->serialise();

		$this->setExpectedException('Exception','There is already a support services episode for this patient.');

		$ps = new EpisodeService;
		$episode = $ps->jsonToModel($json, new \Episode);
	}

	public function testJsonToModel_Save_Create_CantNoSubspecialtyFirmIfAlreadyExists()
	{
		$resource = $this->getResource();
		$resource->firm = $this->firms('firm6')->name;
		$resource->subspecialty = null;
		$resource->patient_ref = \Yii::app()->service->Patient(3);

		$json = $resource->serialise();

		$this->setExpectedException('Exception','There is already an open No subspecialty firm episode for this patient.');

		$ps = new EpisodeService;
		$episode = $ps->jsonToModel($json, new \Episode);
	}

	public function testJsonToModel_Save_Update_ModelCountsCorrect()
	{
		$resource = $this->getResource();
		$resource->patient_ref = \Yii::app()->service->Patient(3);

		$json = $resource->serialise();

		$total_e = count(\Episode::model()->findAll());

		$ps = new EpisodeService;
		$episode = $ps->jsonToModel($json, $this->episodes('episode3'));

		$this->assertEquals($total_e, count(\Episode::model()->findAll()));
	}

	public function testJsonToModel_Save_Update_ModelIsCorrect()
	{
		$resource = $this->getResource();
		$resource->patient_ref = \Yii::app()->service->Patient(3);

		$json = $resource->serialise();

		$ps = new EpisodeService;
		$episode = $ps->jsonToModel($json, $this->episodes('episode3'));

		$this->assertInstanceOf('Episode',$episode);
		$this->assertInstanceOf('Patient',$episode->patient);
		$this->assertEquals(3,$episode->patient->id);
		$this->assertEquals(3,$episode->patient_id);
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

	public function testJsonToModel_Save_Update_DBIsCorrect()
	{
		$resource = $this->getResource();
		$resource->patient_ref = \Yii::app()->service->Patient(3);

		$json = $resource->serialise();

		$ps = new EpisodeService;
		$episode = $ps->jsonToModel($json, $this->episodes('episode3'));
		$episode = \Episode::model()->findByPk($episode->id);

		$this->assertInstanceOf('Episode',$episode);
		$this->assertInstanceOf('Patient',$episode->patient);
		$this->assertEquals(3,$episode->patient->id);
		$this->assertEquals(3,$episode->patient_id);
		$this->assertInstanceOf('Firm',$episode->firm);
		$this->assertEquals('Aylward Firm',$episode->firm->name);
		$this->assertEquals(\Firm::model()->find('name=? and service_subspecialty_assignment_id=?',array('Aylward Firm',2))->id,$episode->firm_id);
		$this->assertInstanceOf('ServiceSubspecialtyAssignment',$episode->firm->serviceSubspecialtyAssignment);
		$this->assertInstanceOf('Subspecialty',$episode->firm->serviceSubspecialtyAssignment->subspecialty);
		$this->assertEquals('Subspecialty 2',$episode->firm->serviceSubspecialtyAssignment->subspecialty->name);
		$this->assertInstanceOf('EpisodeStatus',$episode->status);
		$this->assertEquals('Post-op',$episode->status->name);
		$this->assertEquals(\EpisodeStatus::model()->find('name=?',array('Post-op'))->id,$episode->episode_status_id);
		$this->assertEquals('2013-05-04 00:00:00',$episode->start_date);
		$this->assertNull($episode->end_date);
		$this->assertInstanceOf('Eye',$episode->eye);
		$this->assertEquals('Right',$episode->eye->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Right'))->id,$episode->eye_id);
		$this->assertInstanceOf('Disorder',$episode->diagnosis);
		$this->assertEquals('Myopia',$episode->diagnosis->term);
		$this->assertEquals(\Disorder::model()->find('term=?',array('Myopia'))->id,$episode->disorder_id);
	}
}
