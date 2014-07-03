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

class PatientSocialHistoryServiceTest extends \CDbTestCase
{
	public $fixtures = array(
		'patients' => 'Patient',
		'social_history' => 'SocialHistory',
	);

	public function testModelToResource()
	{
		$patient = $this->patients('patient1');

		$ps = new PatientSocialHistoryService;

		$resource = $ps->modelToResource($patient);

		$this->assertInstanceOf('services\PatientSocialHistory',$resource);

		$this->assertEquals('Unemployed',$resource->occupation);
		$this->assertEquals('HGV',$resource->driving_status);
		$this->assertEquals('Ex smoker',$resource->smoking_status);
		$this->assertEquals('Lives in sheltered housing',$resource->accommodation);
		$this->assertEquals('this is a comment',$resource->comments);
		$this->assertEquals('Forklifts',$resource->type_of_job);
		$this->assertEquals('Yes',$resource->carer);
		$this->assertEquals(100,$resource->alcohol_intake);
		$this->assertEquals('Yes',$resource->substance_misuse);
	}

	public function getResource()
	{
		$resource = new PatientSocialHistory;

		$resource->occupation = 'Model train driver';
		$resource->driving_status = 'Awesome';
		$resource->smoking_status = 'Tons';
		$resource->accommodation = 'Travelodge';
		$resource->comments = 'this is a really unnecessarily long comment';
		$resource->type_of_job = 'shady';
		$resource->carer = 'No';
		$resource->alcohol_intake = '99999999';
		$resource->substance_misuse = 'No';

		return $resource;
	}

	public function testResourceToModel_NoSave_NoNewRecords()
	{
		$resource = $this->getResource();

		$total_sh = count(\SocialHistory::model()->findAll());

		$ps = new PatientSocialHistoryService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient2'), false);

		$this->assertEquals($total_sh, count(\SocialHistory::model()->findAll()));
	}

	public function testResourceToModel_NoSave_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientSocialHistoryService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient2'), false);

		$this->assertInstanceOf('Patient',$patient);

		$this->assertInstanceOf('SocialHistory',$patient->socialHistory);
		$this->assertInstanceOf('SocialHistoryAccommodation',$patient->socialHistory->accommodation);
		$this->assertEquals('Travelodge',$patient->socialHistory->accommodation->name);
		$this->assertInstanceOf('SocialHistoryCarer',$patient->socialHistory->carer);
		$this->assertEquals('No',$patient->socialHistory->carer->name);
		$this->assertInstanceOf('SocialHistoryDrivingStatus',$patient->socialHistory->driving_status);
		$this->assertEquals('Awesome',$patient->socialHistory->driving_status->name);
		$this->assertInstanceOf('SocialHistoryOccupation',$patient->socialHistory->occupation);
		$this->assertEquals('this is a really unnecessarily long comment',$patient->socialHistory->comments);
		$this->assertEquals('shady',$patient->socialHistory->type_of_job);
		$this->assertEquals('99999999',$patient->socialHistory->alcohol_intake);
		$this->assertInstanceOf('SocialHistorySubstanceMisuse',$patient->socialHistory->substance_misuse);
		$this->assertEquals('No',$patient->socialHistory->substance_misuse->name);
	}

	public function testResourceToModel_Save_Create_ModelCountsCorrect()
	{
		$resource = $this->getResource();

		$total_ph = count(\SocialHistory::model()->findAll());

		$ps = new PatientSocialHistoryService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient3'));

		$this->assertEquals($total_ph+1, count(\SocialHistory::model()->findAll()));
	}

	public function testResourceToModel_Save_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientSocialHistoryService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient2'));

		$this->assertInstanceOf('Patient',$patient);

		$this->assertInstanceOf('SocialHistory',$patient->socialHistory);
		$this->assertInstanceOf('SocialHistoryAccommodation',$patient->socialHistory->accommodation);
		$this->assertEquals('Travelodge',$patient->socialHistory->accommodation->name);
		$this->assertInstanceOf('SocialHistoryCarer',$patient->socialHistory->carer);
		$this->assertEquals('No',$patient->socialHistory->carer->name);
		$this->assertInstanceOf('SocialHistoryDrivingStatus',$patient->socialHistory->driving_status);
		$this->assertEquals('Awesome',$patient->socialHistory->driving_status->name);
		$this->assertInstanceOf('SocialHistoryOccupation',$patient->socialHistory->occupation);
		$this->assertEquals('this is a really unnecessarily long comment',$patient->socialHistory->comments);
		$this->assertEquals('shady',$patient->socialHistory->type_of_job);
		$this->assertEquals('99999999',$patient->socialHistory->alcohol_intake);
		$this->assertInstanceOf('SocialHistorySubstanceMisuse',$patient->socialHistory->substance_misuse);
		$this->assertEquals('No',$patient->socialHistory->substance_misuse->name);
	}

	public function testResourceToModel_Save_Create_DBIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientSocialHistoryService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient3'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);

		$this->assertInstanceOf('SocialHistory',$patient->socialHistory);
		$this->assertInstanceOf('SocialHistoryAccommodation',$patient->socialHistory->accommodation);
		$this->assertEquals('Travelodge',$patient->socialHistory->accommodation->name);
		$this->assertInstanceOf('SocialHistoryCarer',$patient->socialHistory->carer);
		$this->assertEquals('No',$patient->socialHistory->carer->name);
		$this->assertInstanceOf('SocialHistoryDrivingStatus',$patient->socialHistory->driving_status);
		$this->assertEquals('Awesome',$patient->socialHistory->driving_status->name);
		$this->assertInstanceOf('SocialHistoryOccupation',$patient->socialHistory->occupation);
		$this->assertEquals('this is a really unnecessarily long comment',$patient->socialHistory->comments);
		$this->assertEquals('shady',$patient->socialHistory->type_of_job);
		$this->assertEquals('99999999',$patient->socialHistory->alcohol_intake);
		$this->assertInstanceOf('SocialHistorySubstanceMisuse',$patient->socialHistory->substance_misuse);
		$this->assertEquals('No',$patient->socialHistory->substance_misuse->name);
	}

	public function testResourceToModel_Save_Update_Modified_ModelCountsCorrect()
	{
		$resource = $this->getResource();

		$total_sh = count(\SocialHistory::model()->findAll());

		$ps = new PatientSocialHistoryService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'));

		$this->assertEquals($total_sh, count(\SocialHistory::model()->findAll()));
	}

	public function testResourceToModel_Save_Update_NotModified_ModelCountsCorrect()
	{
		$resource = \Yii::app()->service->PatientSocialHistory(1)->fetch();

		$total_sh = count(\SocialHistory::model()->findAll());

		$ps = new PatientSocialHistoryService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'));

		$this->assertEquals($total_sh, count(\SocialHistory::model()->findAll()));
	}

	public function testResourceToModel_Save_Update_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientSocialHistoryService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'));

		$this->assertInstanceOf('Patient',$patient);

		$this->assertInstanceOf('SocialHistory',$patient->socialHistory);
		$this->assertInstanceOf('SocialHistoryAccommodation',$patient->socialHistory->accommodation);
		$this->assertEquals('Travelodge',$patient->socialHistory->accommodation->name);
		$this->assertInstanceOf('SocialHistoryCarer',$patient->socialHistory->carer);
		$this->assertEquals('No',$patient->socialHistory->carer->name);
		$this->assertInstanceOf('SocialHistoryDrivingStatus',$patient->socialHistory->driving_status);
		$this->assertEquals('Awesome',$patient->socialHistory->driving_status->name);
		$this->assertInstanceOf('SocialHistoryOccupation',$patient->socialHistory->occupation);
		$this->assertEquals('this is a really unnecessarily long comment',$patient->socialHistory->comments);
		$this->assertEquals('shady',$patient->socialHistory->type_of_job);
		$this->assertEquals('99999999',$patient->socialHistory->alcohol_intake);
		$this->assertInstanceOf('SocialHistorySubstanceMisuse',$patient->socialHistory->substance_misuse);
		$this->assertEquals('No',$patient->socialHistory->substance_misuse->name);
	}

	public function testResourceToModel_Save_Update_DBIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientSocialHistoryService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'));

		$this->assertInstanceOf('Patient',$patient);

		$this->assertInstanceOf('SocialHistory',$patient->socialHistory);
		$this->assertInstanceOf('SocialHistoryAccommodation',$patient->socialHistory->accommodation);
		$this->assertEquals('Travelodge',$patient->socialHistory->accommodation->name);
		$this->assertInstanceOf('SocialHistoryCarer',$patient->socialHistory->carer);
		$this->assertEquals('No',$patient->socialHistory->carer->name);
		$this->assertInstanceOf('SocialHistoryDrivingStatus',$patient->socialHistory->driving_status);
		$this->assertEquals('Awesome',$patient->socialHistory->driving_status->name);
		$this->assertInstanceOf('SocialHistoryOccupation',$patient->socialHistory->occupation);
		$this->assertEquals('this is a really unnecessarily long comment',$patient->socialHistory->comments);
		$this->assertEquals('shady',$patient->socialHistory->type_of_job);
		$this->assertEquals('99999999',$patient->socialHistory->alcohol_intake);
		$this->assertInstanceOf('SocialHistorySubstanceMisuse',$patient->socialHistory->substance_misuse);
		$this->assertEquals('No',$patient->socialHistory->substance_misuse->name);
	}

	public function testJsonToResource()
	{
		$json = '{"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800},"occupation":"Unemployed","driving_status":"HGV","smoking_status":"Ex smoker","accommodation":"Lives in sheltered housing","comments":"this is a comment","type_of_job":"Forklifts","carer":"Yes","alcohol_intake":"100","substance_misuse":"Yes"}';

		$ps = new PatientSocialHistoryService;
		$resource = $ps->jsonToResource($json);

		$this->assertInstanceOf('services\PatientSocialHistory',$resource);

		$this->assertEquals('Unemployed',$resource->occupation);
		$this->assertEquals('HGV',$resource->driving_status);
		$this->assertEquals('Ex smoker',$resource->smoking_status);
		$this->assertEquals('Lives in sheltered housing',$resource->accommodation);
		$this->assertEquals('this is a comment',$resource->comments);
		$this->assertEquals('Forklifts',$resource->type_of_job);
		$this->assertEquals('Yes',$resource->carer);
		$this->assertEquals(100,$resource->alcohol_intake);
		$this->assertEquals('Yes',$resource->substance_misuse);
	}

	public function testJsonToModel_NoSave_NoNewRows()
	{
		$json = '{"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800},"occupation":"Unemployed","driving_status":"HGV","smoking_status":"Ex smoker","accommodation":"Lives in sheltered housing","comments":"this is a comment","type_of_job":"Forklifts","carer":"Yes","alcohol_intake":"100","substance_misuse":"Yes"}';

		$total_sh = count(\SocialHistory::model()->findAll());

		$ps = new PatientSocialHistoryService;
		$patient = $ps->jsonToModel($json, $this->patients('patient3'), false);

		$this->assertEquals($total_sh, count(\SocialHistory::model()->findAll()));
	}

	public function testJsonToModel_NoSave_ModelIsCorrect()
	{
		$json = '{"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800},"occupation":"Unemployed","driving_status":"HGV","smoking_status":"Ex smoker","accommodation":"Lives in sheltered housing","comments":"this is a comment","type_of_job":"Forklifts","carer":"Yes","alcohol_intake":"100","substance_misuse":"Yes"}';

		$ps = new PatientSocialHistoryService;
		$patient = $ps->jsonToModel($json, $this->patients('patient3'), false);

		$this->assertInstanceOf('Patient',$patient);

		$this->assertInstanceOf('SocialHistory',$patient->socialHistory);
		$this->assertInstanceOf('SocialHistoryAccommodation',$patient->socialHistory->accommodation);
		$this->assertEquals('Lives in sheltered housing',$patient->socialHistory->accommodation->name);
		$this->assertInstanceOf('SocialHistoryCarer',$patient->socialHistory->carer);
		$this->assertEquals('Yes',$patient->socialHistory->carer->name);
		$this->assertInstanceOf('SocialHistoryDrivingStatus',$patient->socialHistory->driving_status);
		$this->assertEquals('HGV',$patient->socialHistory->driving_status->name);
		$this->assertInstanceOf('SocialHistoryOccupation',$patient->socialHistory->occupation);
		$this->assertEquals('this is a comment',$patient->socialHistory->comments);
		$this->assertEquals('Forklifts',$patient->socialHistory->type_of_job);
		$this->assertEquals(100,$patient->socialHistory->alcohol_intake);
		$this->assertInstanceOf('SocialHistorySubstanceMisuse',$patient->socialHistory->substance_misuse);
		$this->assertEquals('Yes',$patient->socialHistory->substance_misuse->name);
	}

	public function testJsonToModel_Save_Create_ModelCountsCorrect()
	{
		$json = '{"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800},"occupation":"Unemployed","driving_status":"HGV","smoking_status":"Ex smoker","accommodation":"Lives in sheltered housing","comments":"this is a comment","type_of_job":"Forklifts","carer":"Yes","alcohol_intake":"100","substance_misuse":"Yes"}';

		$total_sh = count(\SocialHistory::model()->findAll());

		$ps = new PatientSocialHistoryService;
		$patient = $ps->jsonToModel($json, $this->patients('patient3'));

		$this->assertEquals($total_sh+1, count(\SocialHistory::model()->findAll()));
	}

	public function testJsonToModel_Save_Create_ModelIsCorrect()
	{
		$json = '{"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800},"occupation":"Unemployed","driving_status":"HGV","smoking_status":"Ex smoker","accommodation":"Lives in sheltered housing","comments":"this is a comment","type_of_job":"Forklifts","carer":"Yes","alcohol_intake":"100","substance_misuse":"Yes"}';

		$ps = new PatientSocialHistoryService;
		$patient = $ps->jsonToModel($json, $this->patients('patient3'));

		$this->assertInstanceOf('Patient',$patient);

		$this->assertInstanceOf('SocialHistory',$patient->socialHistory);
		$this->assertInstanceOf('SocialHistoryAccommodation',$patient->socialHistory->accommodation);
		$this->assertEquals('Lives in sheltered housing',$patient->socialHistory->accommodation->name);
		$this->assertInstanceOf('SocialHistoryCarer',$patient->socialHistory->carer);
		$this->assertEquals('Yes',$patient->socialHistory->carer->name);
		$this->assertInstanceOf('SocialHistoryDrivingStatus',$patient->socialHistory->driving_status);
		$this->assertEquals('HGV',$patient->socialHistory->driving_status->name);
		$this->assertInstanceOf('SocialHistoryOccupation',$patient->socialHistory->occupation);
		$this->assertEquals('this is a comment',$patient->socialHistory->comments);
		$this->assertEquals('Forklifts',$patient->socialHistory->type_of_job);
		$this->assertEquals(100,$patient->socialHistory->alcohol_intake);
		$this->assertInstanceOf('SocialHistorySubstanceMisuse',$patient->socialHistory->substance_misuse);
		$this->assertEquals('Yes',$patient->socialHistory->substance_misuse->name);
	}

	public function testJsonToModel_Save_Create_DBIsCorrect()
	{
		$json = '{"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800},"occupation":"Unemployed","driving_status":"HGV","smoking_status":"Ex smoker","accommodation":"Lives in sheltered housing","comments":"this is a comment","type_of_job":"Forklifts","carer":"Yes","alcohol_intake":"100","substance_misuse":"Yes"}';

		$ps = new PatientSocialHistoryService;
		$patient = $ps->jsonToModel($json, $this->patients('patient3'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);

		$this->assertInstanceOf('SocialHistory',$patient->socialHistory);
		$this->assertInstanceOf('SocialHistoryAccommodation',$patient->socialHistory->accommodation);
		$this->assertEquals('Lives in sheltered housing',$patient->socialHistory->accommodation->name);
		$this->assertInstanceOf('SocialHistoryCarer',$patient->socialHistory->carer);
		$this->assertEquals('Yes',$patient->socialHistory->carer->name);
		$this->assertInstanceOf('SocialHistoryDrivingStatus',$patient->socialHistory->driving_status);
		$this->assertEquals('HGV',$patient->socialHistory->driving_status->name);
		$this->assertInstanceOf('SocialHistoryOccupation',$patient->socialHistory->occupation);
		$this->assertEquals('this is a comment',$patient->socialHistory->comments);
		$this->assertEquals('Forklifts',$patient->socialHistory->type_of_job);
		$this->assertEquals(100,$patient->socialHistory->alcohol_intake);
		$this->assertInstanceOf('SocialHistorySubstanceMisuse',$patient->socialHistory->substance_misuse);
		$this->assertEquals('Yes',$patient->socialHistory->substance_misuse->name);
	}

	public function testJsonToModel_Save_Update_ModelCountsCorrect()
	{
		$json = '{"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800},"occupation":"Unemployed","driving_status":"HGV","smoking_status":"Ex smoker","accommodation":"Lives in sheltered housing","comments":"this is a comment","type_of_job":"Forklifts","carer":"Yes","alcohol_intake":"100","substance_misuse":"Yes"}';

		$total_sh = count(\SocialHistory::model()->findAll());

		$ps = new PatientSocialHistoryService;
		$patient = $ps->jsonToModel($json, $this->patients('patient2'));

		$this->assertEquals($total_sh, count(\SocialHistory::model()->findAll()));
	}

	public function testJsonToModel_Save_Update_ModelIsCorrect()
	{
		$json = '{"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800},"occupation":"Unemployed","driving_status":"HGV","smoking_status":"Ex smoker","accommodation":"Lives in sheltered housing","comments":"this is a comment","type_of_job":"Forklifts","carer":"Yes","alcohol_intake":"100","substance_misuse":"Yes"}';

		$ps = new PatientSocialHistoryService;
		$patient = $ps->jsonToModel($json, $this->patients('patient2'));

		$this->assertInstanceOf('Patient',$patient);

		$this->assertInstanceOf('SocialHistory',$patient->socialHistory);
		$this->assertInstanceOf('SocialHistoryAccommodation',$patient->socialHistory->accommodation);
		$this->assertEquals('Lives in sheltered housing',$patient->socialHistory->accommodation->name);
		$this->assertInstanceOf('SocialHistoryCarer',$patient->socialHistory->carer);
		$this->assertEquals('Yes',$patient->socialHistory->carer->name);
		$this->assertInstanceOf('SocialHistoryDrivingStatus',$patient->socialHistory->driving_status);
		$this->assertEquals('HGV',$patient->socialHistory->driving_status->name);
		$this->assertInstanceOf('SocialHistoryOccupation',$patient->socialHistory->occupation);
		$this->assertEquals('this is a comment',$patient->socialHistory->comments);
		$this->assertEquals('Forklifts',$patient->socialHistory->type_of_job);
		$this->assertEquals(100,$patient->socialHistory->alcohol_intake);
		$this->assertInstanceOf('SocialHistorySubstanceMisuse',$patient->socialHistory->substance_misuse);
		$this->assertEquals('Yes',$patient->socialHistory->substance_misuse->name);
	}

	public function testJsonToModel_Save_Update_DBIsCorrect()
	{
		$json = '{"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800},"occupation":"Unemployed","driving_status":"HGV","smoking_status":"Ex smoker","accommodation":"Lives in sheltered housing","comments":"this is a comment","type_of_job":"Forklifts","carer":"Yes","alcohol_intake":"100","substance_misuse":"Yes"}';

		$ps = new PatientSocialHistoryService;
		$patient = $ps->jsonToModel($json, $this->patients('patient2'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);

		$this->assertInstanceOf('SocialHistory',$patient->socialHistory);
		$this->assertInstanceOf('SocialHistoryAccommodation',$patient->socialHistory->accommodation);
		$this->assertEquals('Lives in sheltered housing',$patient->socialHistory->accommodation->name);
		$this->assertInstanceOf('SocialHistoryCarer',$patient->socialHistory->carer);
		$this->assertEquals('Yes',$patient->socialHistory->carer->name);
		$this->assertInstanceOf('SocialHistoryDrivingStatus',$patient->socialHistory->driving_status);
		$this->assertEquals('HGV',$patient->socialHistory->driving_status->name);
		$this->assertInstanceOf('SocialHistoryOccupation',$patient->socialHistory->occupation);
		$this->assertEquals('this is a comment',$patient->socialHistory->comments);
		$this->assertEquals('Forklifts',$patient->socialHistory->type_of_job);
		$this->assertEquals(100,$patient->socialHistory->alcohol_intake);
		$this->assertInstanceOf('SocialHistorySubstanceMisuse',$patient->socialHistory->substance_misuse);
		$this->assertEquals('Yes',$patient->socialHistory->substance_misuse->name);
	}
}
