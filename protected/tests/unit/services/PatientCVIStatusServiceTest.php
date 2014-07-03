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

class PatientCVIStatusServiceTest extends \CDbTestCase
{
	public $fixtures = array(
		'patients' => 'Patient',
		'cvis' => 'PatientOphInfo',
	);

	public function testModelToResource()
	{
		$patient = $this->patients('patient2');

		$ps = new PatientCVIStatusService;
		$resource = $ps->modelToResource($patient);

		$this->assertInstanceOf('services\PatientCVIStatus',$resource);
		$this->assertEquals('Not Certified',$resource->cvi_status);
		$this->assertInstanceOf('services\Date',$resource->date);
	}

	public function getResource()
	{
		$resource = new PatientCVIStatus;
		$resource->cvi_status = 'Sight Impaired';
		$resource->date = new Date('2012-01-04 13:37:00');

		return $resource;
	}

	public function testResourceToModel_NoSave_NoNewRecords()
	{
		$resource = $this->getResource();

		$total_patients = count(\Patient::model()->findAll());
		$total_cvi_status = count(\PatientOphInfo::model()->findAll());

		$ps = new PatientCVIStatusService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient4'), false);

		$this->assertEquals($total_patients, count(\Patient::model()->findAll()));
		$this->assertEquals($total_cvi_status, count(\PatientOphInfo::model()->findAll()));
	}

	public function testResourceToModel_NoSave_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientCVIStatusService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient4'), false);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertInstanceOf('PatientOphInfo',$patient->cvi_status);
		$this->assertInstanceOf('PatientOphInfoCviStatus',$patient->cvi_status->cvi_status);
		$this->assertEquals('Sight Impaired',$patient->cvi_status->cvi_status->name);
		$this->assertEquals('2012-01-04',$patient->cvi_status->cvi_status_date);
	}

	public function testResourceToModel_Save_Create_ModelCountsCorrect()
	{
		$resource = $this->getResource();

		$total_patients = count(\Patient::model()->findAll());
		$total_cvi_status = count(\PatientOphInfo::model()->findAll());

		$ps = new PatientCVIStatusService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient4'));

		$this->assertEquals($total_patients, count(\Patient::model()->findAll()));
		$this->assertEquals($total_cvi_status+1, count(\PatientOphInfo::model()->findAll()));
	}

	public function testResourceToModel_Save_Create_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientCVIStatusService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient4'));

		$this->assertInstanceOf('Patient',$patient);
		$this->assertInstanceOf('PatientOphInfo',$patient->cvi_status);
		$this->assertInstanceOf('PatientOphInfoCviStatus',$patient->cvi_status->cvi_status);
		$this->assertEquals('Sight Impaired',$patient->cvi_status->cvi_status->name);
		$this->assertEquals('2012-01-04',$patient->cvi_status->cvi_status_date);
	}

	public function testResourceToModel_Save_Create_DBIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientCVIStatusService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient4'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertInstanceOf('PatientOphInfo',$patient->cvi_status);
		$this->assertInstanceOf('PatientOphInfoCviStatus',$patient->cvi_status->cvi_status);
		$this->assertEquals('Sight Impaired',$patient->cvi_status->cvi_status->name);
		$this->assertEquals('2012-01-04',$patient->cvi_status->cvi_status_date);
	}

	public function testResourceToModel_Save_Update_ModelCountsCorrect()
	{
		$resource = $this->getResource();

		$total_patients = count(\Patient::model()->findAll());
		$total_cvi_status = count(\PatientOphInfo::model()->findAll());

		$ps = new PatientCVIStatusService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'));

		$this->assertEquals($total_patients, count(\Patient::model()->findAll()));
		$this->assertEquals($total_cvi_status, count(\PatientOphInfo::model()->findAll()));
	}

	public function testResourceToModel_Save_Update_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientCVIStatusService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'));

		$this->assertInstanceOf('Patient',$patient);
		$this->assertInstanceOf('PatientOphInfo',$patient->cvi_status);
		$this->assertInstanceOf('PatientOphInfoCviStatus',$patient->cvi_status->cvi_status);
		$this->assertEquals('Sight Impaired',$patient->cvi_status->cvi_status->name);
		$this->assertEquals('2012-01-04',$patient->cvi_status->cvi_status_date);
	}

	public function testResourceToModel_Save_Update_DBIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientCVIStatusService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertInstanceOf('PatientOphInfo',$patient->cvi_status);
		$this->assertInstanceOf('PatientOphInfoCviStatus',$patient->cvi_status->cvi_status);
		$this->assertEquals('Sight Impaired',$patient->cvi_status->cvi_status->name);
		$this->assertEquals('2012-01-04',$patient->cvi_status->cvi_status_date);
	}

	public function getModifiedResource($id)
	{
		$resource = \Yii::app()->service->PatientCVIStatus($id)->fetch();

		$resource->cvi_status = 'Severely Sight Impaired';
		$resource->date = new Date('2014-03-03');

		return $resource;
	}

	public function testResourceToModel_Save_Modified_ModelCountsCorrect()
	{
		$resource = $this->getModifiedResource(1);

		$total_patients = count(\Patient::model()->findAll());
		$total_cvi_status = count(\PatientOphInfo::model()->findAll());

		$ps = new PatientCVIStatusService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'));

		$this->assertEquals($total_patients, count(\Patient::model()->findAll()));
		$this->assertEquals($total_cvi_status, count(\PatientOphInfo::model()->findAll()));
	}

	public function testResourceToModel_Save_NotModified_ModelCountsCorrect()
	{
		$resource = \Yii::app()->service->PatientCVIStatus(1)->fetch();

		$total_patients = count(\Patient::model()->findAll());
		$total_cvi_status = count(\PatientOphInfo::model()->findAll());

		$ps = new PatientCVIStatusService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'));

		$this->assertEquals($total_patients, count(\Patient::model()->findAll()));
		$this->assertEquals($total_cvi_status, count(\PatientOphInfo::model()->findAll()));
	}

	public function testResourceToModel_Save_Modified_ModelIsCorrect()
	{
		$resource = $this->getModifiedResource(1);

		$ps = new PatientCVIStatusService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'));

		$this->assertInstanceOf('Patient',$patient);
		$this->assertInstanceOf('PatientOphInfo',$patient->cvi_status);
		$this->assertInstanceOf('PatientOphInfoCviStatus',$patient->cvi_status->cvi_status);
		$this->assertEquals('Severely Sight Impaired',$patient->cvi_status->cvi_status->name);
		$this->assertEquals('2014-03-03',$patient->cvi_status->cvi_status_date);
	}

	public function testResourceToModel_Save_Modified_DBIsCorrect()
	{
		$resource = $this->getModifiedResource(1);

		$ps = new PatientCVIStatusService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'));
		$patient = \Patient::model()->findByPk($patient->id);
	 
		$this->assertInstanceOf('Patient',$patient);
		$this->assertInstanceOf('PatientOphInfo',$patient->cvi_status);
		$this->assertInstanceOf('PatientOphInfoCviStatus',$patient->cvi_status->cvi_status);
		$this->assertEquals('Severely Sight Impaired',$patient->cvi_status->cvi_status->name);
		$this->assertEquals('2014-03-03',$patient->cvi_status->cvi_status_date);
	}

	public function testJsonToResource()
	{
		$json = '{"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800},"cvi_status":"Not Certified","date":{"date":"2014-06-27 14:41:04","timezone_type":3,"timezone":"Europe\/London"}}';

		$ps = new PatientCVIStatusService;
		$resource = $ps->jsonToResource($json);

		$this->assertInstanceOf('services\PatientCVIStatus',$resource);
		$this->assertEquals('Not Certified',$resource->cvi_status);
		$this->assertInstanceOf('services\Date',$resource->date);
	}

	public function testJsonToModel_NoSave_NoNewRows()
	{
		$json = '{"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800},"cvi_status":"Not Certified","date":{"date":"2014-06-27 14:41:04","timezone_type":3,"timezone":"Europe\/London"}}';

		$total_patients = count(\Patient::model()->findAll());
		$total_cvi_status = count(\PatientOphInfo::model()->findAll());

		$ps = new PatientCVIStatusService;
		$patient = $ps->jsonToModel($json, $this->patients('patient2'), false);

		$this->assertEquals($total_patients, count(\Patient::model()->findAll()));
		$this->assertEquals($total_cvi_status, count(\PatientOphInfo::model()->findAll()));
	}

	public function testJsonToModel_NoSave_ModelIsCorrect()
	{
		$json = '{"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800},"cvi_status":"Not Certified","date":{"date":"2014-06-27 14:41:04","timezone_type":3,"timezone":"Europe\/London"}}';

		$ps = new PatientCVIStatusService;
		$patient = $ps->jsonToModel($json, $this->patients('patient2'), false);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertInstanceOf('PatientOphInfo',$patient->cvi_status);
		$this->assertInstanceOf('PatientOphInfoCviStatus',$patient->cvi_status->cvi_status);
		$this->assertEquals('Not Certified',$patient->cvi_status->cvi_status->name);
		$this->assertEquals('2014-06-27',$patient->cvi_status->cvi_status_date);
	}

	public function testJsonToModel_Save_Create_ModelCountsCorrect()
	{
		$json = '{"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800},"cvi_status":"Not Certified","date":{"date":"2014-06-27 14:41:04","timezone_type":3,"timezone":"Europe\/London"}}';

		$total_patients = count(\Patient::model()->findAll());
		$total_cvi_status = count(\PatientOphInfo::model()->findAll());

		$ps = new PatientCVIStatusService;
		$patient = $ps->jsonToModel($json, $this->patients('patient4'));

		$this->assertEquals($total_patients, count(\Patient::model()->findAll()));
		$this->assertEquals($total_cvi_status+1, count(\PatientOphInfo::model()->findAll()));
	}

	public function testJsonToModel_Save_Create_ModelIsCorrect()
	{
		$json = '{"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800},"cvi_status":"Not Certified","date":{"date":"2014-06-27 14:41:04","timezone_type":3,"timezone":"Europe\/London"}}';

		$ps = new PatientCVIStatusService;
		$patient = $ps->jsonToModel($json, $this->patients('patient4'));

		$this->assertInstanceOf('Patient',$patient);
		$this->assertInstanceOf('PatientOphInfo',$patient->cvi_status);
		$this->assertInstanceOf('PatientOphInfoCviStatus',$patient->cvi_status->cvi_status);
		$this->assertEquals('Not Certified',$patient->cvi_status->cvi_status->name);
		$this->assertEquals('2014-06-27',$patient->cvi_status->cvi_status_date);
	}

	public function testJsonToModel_Save_Create_DBIsCorrect()
	{
		$json = '{"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800},"cvi_status":"Not Certified","date":{"date":"2014-06-27 14:41:04","timezone_type":3,"timezone":"Europe\/London"}}';

		$ps = new PatientCVIStatusService;
		$patient = $ps->jsonToModel($json, $this->patients('patient4'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertInstanceOf('PatientOphInfo',$patient->cvi_status);
		$this->assertInstanceOf('PatientOphInfoCviStatus',$patient->cvi_status->cvi_status);
		$this->assertEquals('Not Certified',$patient->cvi_status->cvi_status->name);
		$this->assertEquals('2014-06-27',$patient->cvi_status->cvi_status_date);
	}

	public function testJsonToModel_Save_Update_ModelCountsCorrect()
	{
		$json = '{"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800},"cvi_status":"Not Certified","date":{"date":"2014-06-27 14:41:04","timezone_type":3,"timezone":"Europe\/London"}}';

		$total_patients = count(\Patient::model()->findAll());
		$total_cvi_status = count(\PatientOphInfo::model()->findAll());

		$ps = new PatientCVIStatusService;
		$patient = $ps->jsonToModel($json, $this->patients('patient1'));

		$this->assertEquals($total_patients, count(\Patient::model()->findAll()));
		$this->assertEquals($total_cvi_status, count(\PatientOphInfo::model()->findAll()));
	}

	public function testJsonToModel_Save_Update_ModelIsCorrect()
	{
		$json = '{"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800},"cvi_status":"Not Certified","date":{"date":"2014-06-27 14:41:04","timezone_type":3,"timezone":"Europe\/London"}}';

		$ps = new PatientCVIStatusService;
		$patient = $ps->jsonToModel($json, $this->patients('patient1'));

		$this->assertInstanceOf('Patient',$patient);
		$this->assertInstanceOf('PatientOphInfo',$patient->cvi_status);
		$this->assertInstanceOf('PatientOphInfoCviStatus',$patient->cvi_status->cvi_status);
		$this->assertEquals('Not Certified',$patient->cvi_status->cvi_status->name);
		$this->assertEquals('2014-06-27',$patient->cvi_status->cvi_status_date);
	}

	public function testJsonToModel_Save_Update_DBIsCorrect()
	{
		$json = '{"id":null,"last_modified":null,"patient_id":{"id":"2","last_modified":-2208988800},"cvi_status":"Not Certified","date":{"date":"2014-06-27 14:41:04","timezone_type":3,"timezone":"Europe\/London"}}';

		$ps = new PatientCVIStatusService;
		$patient = $ps->jsonToModel($json, $this->patients('patient1'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertInstanceOf('PatientOphInfo',$patient->cvi_status);
		$this->assertInstanceOf('PatientOphInfoCviStatus',$patient->cvi_status->cvi_status);
		$this->assertEquals('Not Certified',$patient->cvi_status->cvi_status->name);
		$this->assertEquals('2014-06-27',$patient->cvi_status->cvi_status_date);
	}
}
