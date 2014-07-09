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

class PatientServiceTest extends \CDbTestCase
{
	public $fixtures = array(
		'patients' => 'Patient',
		'contacts' => 'Contact',
		'addresses' => 'Address',
		'countries' => 'Country',
		'practices' => 'Practice',
		'gps' => 'Gp',
		'cbs' => 'CommissioningBody',
	);

	public function testModelToResource()
	{
		$patient = $this->patients('patient1');
		$contact = $this->contacts('contact1');
		$address = $this->addresses('address1');

		$contact->addresses = array($address);
		$patient->contact = $contact;

		$patient->gp_id = 2;
		$patient->practice_id = 5;

		$ps = new PatientService;

		$resource = $ps->modelToResource($patient);

		$this->assertEquals(1,$resource->getId());
		$this->assertEquals($patient->contact->id,$resource->contact_id);
		$this->assertEquals('54321',$resource->nhs_num);
		$this->assertEquals('12345',$resource->hos_num);
		$this->assertEquals('Mr',$resource->title);
		$this->assertEquals('Aylward',$resource->family_name);
		$this->assertEquals('Jim',$resource->given_name);
		$this->assertInstanceOf('services\GenderReference', $resource->gender_ref);
		$this->assertEquals('Male',$resource->getGender());
		$this->assertEquals('1970-01-01',$resource->birth_date->toModelValue());
		$this->assertEquals('07123 456789',$resource->primary_phone);

		$this->assertCount(1, $resource->addresses);
		$this->assertInstanceOf('services\PatientAddress', $resource->addresses[0]);
		$this->assertEquals($address->id, $resource->addresses[0]->getId());
		$this->assertEquals('flat 1', $resource->addresses[0]->line1);
		$this->assertEquals('bleakley creek', $resource->addresses[0]->line2);
		$this->assertEquals('flitchley', $resource->addresses[0]->city);
		$this->assertEquals('london', $resource->addresses[0]->state);
		$this->assertEquals('ec1v 0dx', $resource->addresses[0]->zip);
		$this->assertEquals('United States',$resource->addresses[0]->country);

		$this->assertInstanceOf('services\\Date',$resource->addresses[0]->date_start);
		$this->assertInstanceOf('services\\Date',$resource->addresses[0]->date_end);
		$this->assertFalse($resource->addresses[0]->correspond);
		$this->assertFalse($resource->addresses[0]->transport);

		$this->assertInstanceOf('services\\GpReference',$resource->gp_ref);
		$this->assertEquals($patient->gp_id, $resource->gp_ref->getId());
		$this->assertEquals('Gp', $resource->gp_ref->getServiceName());

		$this->assertInstanceOf('services\\PracticeReference',$resource->prac_ref);
		$this->assertEquals($patient->practice_id, $resource->prac_ref->getId());
		$this->assertEquals('Practice', $resource->prac_ref->getServiceName());
	}

	public function getResource()
	{
		$gender = \Yii::app()->service->Gender(1);

		$date = new Date;

		$address = new Address;
		$address->date_start = $date;
		$address->date_end = $date;
		$address->line1 = '1 some road';
		$address->line2 = 'some place';
		$address->city = 'somewhere';
		$address->state = 'someton';
		$address->zip = 'som3 0ne';
		$address->country = 'United Kingdom';
		$address->correspond = false;
		$address->transport = false;

		$resource = new Patient;
		$resource->nhs_num = '1919';
		$resource->hos_num = '4545';
		$resource->title = 'Mr';
		$resource->family_name = 'Krinkle';
		$resource->given_name = 'Henry';
		$resource->gender_ref = $gender;
		$resource->birth_date = new Date('1994-04-23');
		$resource->primary_phone = '02332 3241959';
		$resource->addresses = array($address);
		$resource->gp_ref = \Yii::app()->service->Gp(1);
		$resource->prac_ref = \Yii::app()->service->Practice(1);

		return $resource;
	}

	public function testResourceToModel_NoSave_NoNewRecords()
	{
		$resource = $this->getResource();

		$total_patients = count(\Patient::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());
		$total_countries = count(\Country::model()->findAll());
		$total_genders = count(\Gender::model()->findAll());

		$ps = new PatientService;
		$patient = $ps->resourceToModel($resource, new \Patient, false);

		$this->assertEquals($total_patients, count(\Patient::model()->findAll()));
		$this->assertEquals($total_contacts, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses, count(\Address::model()->findAll()));
		$this->assertEquals($total_countries, count(\Country::model()->findAll()));
		$this->assertEquals($total_genders, count(\Gender::model()->findAll()));
	}

	public function testResourceToModel_NoSave_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientService;
		$patient = $ps->resourceToModel($resource, new \Patient, false);

		$this->assertEquals('1919',$patient->nhs_num);
		$this->assertEquals('4545',$patient->hos_num);
		$this->assertEquals('Mr',$patient->title);
		$this->assertEquals('Krinkle',$patient->last_name);
		$this->assertEquals('Henry',$patient->first_name);
		$this->assertInstanceOf('Gender', $patient->gender);
		$this->assertEquals('Male',$patient->gender->name);
		$this->assertEquals('1994-04-23',$patient->dob);
		$this->assertEquals('02332 3241959',$patient->contact->primary_phone);

		$this->assertCount(1, $patient->contact->addresses);
		$this->assertInstanceOf('Address', $patient->contact->addresses[0]);
		$this->assertEquals('1 some road',$patient->contact->addresses[0]->address1);
		$this->assertEquals('some place',$patient->contact->addresses[0]->address2);
		$this->assertEquals('somewhere',$patient->contact->addresses[0]->city);
		$this->assertEquals('someton',$patient->contact->addresses[0]->county);
		$this->assertEquals('som3 0ne',$patient->contact->addresses[0]->postcode);
		$this->assertInstanceOf('Country', $patient->contact->addresses[0]->country);
		$this->assertEquals('United Kingdom', $patient->contact->addresses[0]->country->name);

		$this->assertEquals(1, $patient->gp_id);
		$this->assertEquals(1, $patient->practice_id);
	}

	public function testResourceToModel_Save_Create_ModelCountsCorrect()
	{
		$resource = $this->getResource();

		$total_patients = count(\Patient::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());
		$total_countries = count(\Country::model()->findAll());
		$total_genders = count(\Gender::model()->findAll());

		$ps = new PatientService;
		$patient = $ps->resourceToModel($resource, new \Patient);

		$this->assertEquals($total_patients+1, count(\Patient::model()->findAll()));
		$this->assertEquals($total_contacts+1, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses+1, count(\Address::model()->findAll()));
		$this->assertEquals($total_countries, count(\Country::model()->findAll()));
		$this->assertEquals($total_genders, count(\Gender::model()->findAll()));
	}

	public function testResourceToModel_Save_Create_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientService;
		$patient = $ps->resourceToModel($resource, new \Patient);

		$this->assertInstanceOf('\Patient',$patient);

		$this->assertEquals('1919',$patient->nhs_num);
		$this->assertEquals('4545',$patient->hos_num);
		$this->assertEquals('Mr',$patient->title);
		$this->assertEquals('Krinkle',$patient->last_name);
		$this->assertEquals('Henry',$patient->first_name);
		$this->assertInstanceOf('Gender', $patient->gender);
		$this->assertEquals('Male',$patient->gender->name);
		$this->assertEquals('1994-04-23',$patient->dob);
		$this->assertEquals('02332 3241959',$patient->contact->primary_phone);

		$this->assertCount(1, $patient->contact->addresses);
		$this->assertInstanceOf('Address', $patient->contact->addresses[0]);
		$this->assertEquals('1 some road',$patient->contact->addresses[0]->address1);
		$this->assertEquals('some place',$patient->contact->addresses[0]->address2);
		$this->assertEquals('somewhere',$patient->contact->addresses[0]->city);
		$this->assertEquals('someton',$patient->contact->addresses[0]->county);
		$this->assertEquals('som3 0ne',$patient->contact->addresses[0]->postcode);
		$this->assertInstanceOf('Country', $patient->contact->addresses[0]->country);
		$this->assertEquals('United Kingdom', $patient->contact->addresses[0]->country->name);

		$this->assertEquals(1, $patient->gp_id);
		$this->assertEquals(1, $patient->practice_id);
	}

	public function testResourceToModel_Save_Create_DBIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientService;
		$patient = $ps->resourceToModel($resource, new \Patient);
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertEquals('1919',$patient->nhs_num);
		$this->assertEquals('4545',$patient->hos_num);
		$this->assertEquals('Mr',$patient->title);
		$this->assertEquals('Krinkle',$patient->last_name);
		$this->assertEquals('Henry',$patient->first_name);
		$this->assertInstanceOf('Gender', $patient->gender);
		$this->assertEquals('Male',$patient->gender->name);
		$this->assertEquals('1994-04-23',$patient->dob);
		$this->assertEquals('02332 3241959',$patient->contact->primary_phone);

		$this->assertCount(1, $patient->contact->addresses);
		$this->assertInstanceOf('Address', $patient->contact->addresses[0]);
		$this->assertEquals('1 some road',$patient->contact->addresses[0]->address1);
		$this->assertEquals('some place',$patient->contact->addresses[0]->address2);
		$this->assertEquals('somewhere',$patient->contact->addresses[0]->city);
		$this->assertEquals('someton',$patient->contact->addresses[0]->county);
		$this->assertEquals('som3 0ne',$patient->contact->addresses[0]->postcode);
		$this->assertInstanceOf('Country', $patient->contact->addresses[0]->country);
		$this->assertEquals('United Kingdom', $patient->contact->addresses[0]->country->name);

		$this->assertEquals(1, $patient->gp_id);
		$this->assertEquals(1, $patient->practice_id);
	}

	public function getModifiedResource()
	{
		$resource = new Patient;

		$resource->nhs_num = 'x0000';
		$resource->hos_num = 'x0001';
		$resource->title = 'x0002';
		$resource->family_name = 'x0003';
		$resource->given_name = 'x0004';
		$resource->gender_ref = \Yii::app()->service->Gender(\Gender::model()->find('name=?',array('Female'))->id);
		$resource->birth_date = new Date('1988-04-04');
		$resource->primary_phone = '0101010101';
		$resource->gp_ref = \Yii::app()->service->Gp(1);
		$resource->prac_ref = \Yii::app()->service->Practice(1);
		$resource->addresses = array(new PatientAddress);
		$resource->addresses[0]->line1 = 'L1';
		$resource->addresses[0]->line2 = 'L2';
		$resource->addresses[0]->city = 'L3';
		$resource->addresses[0]->state = 'L4';
		$resource->addresses[0]->zip = 'L5';
		$resource->addresses[0]->country = 'United Kingdom';
		$resource->addresses[0]->correspond = 1;
		$resource->addresses[0]->transport = 0;

		return $resource;
	}

	public function testResourceToModel_Save_Update_ModelCountsCorrect()
	{
		$resource = $this->getModifiedResource();
		$model = \Patient::model()->findByPk(1);

		$resource->contact_id = $model->contact_id;

		$total_patients = count(\Patient::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());
		$total_countries = count(\Country::model()->findAll());
		$total_genders = count(\Gender::model()->findAll());

		$ps = new PatientService;
		$patient = $ps->resourceToModel($resource, $model);

		$this->assertEquals($total_patients, count(\Patient::model()->findAll()));
		$this->assertEquals($total_contacts, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses, count(\Address::model()->findAll()));
		$this->assertEquals($total_countries, count(\Country::model()->findAll()));
		$this->assertEquals($total_genders, count(\Gender::model()->findAll()));
	}

	public function testResourceToModel_Save_Update_DBIsCorrect()
	{
		$resource = $this->getModifiedResource();
		$model = \Patient::model()->findByPk(1);

		$resource->contact_id = $model->contact_id;

		$ps = new PatientService;
		$patient = $ps->resourceToModel($resource, $model);
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertEquals('x0000',$patient->nhs_num);
		$this->assertEquals('x0001',$patient->hos_num);
		$this->assertEquals('x0002',$patient->title);
		$this->assertEquals('x0003',$patient->last_name);
		$this->assertEquals('x0004',$patient->first_name);
		$this->assertInstanceOf('Gender', $patient->gender);
		$this->assertEquals('Female',$patient->gender->name);
		$this->assertEquals('1988-04-04',$patient->dob);
		$this->assertEquals('0101010101',$patient->contact->primary_phone);
		$this->assertEquals(1,$patient->gp_id);
		$this->assertEquals(1,$patient->practice_id);

		$this->assertCount(1, $patient->contact->addresses);
		$this->assertInstanceOf('Address', $patient->contact->addresses[0]);
		$this->assertEquals('L1', $patient->contact->addresses[0]->address1);
		$this->assertEquals('L2', $patient->contact->addresses[0]->address2);
		$this->assertEquals('L3', $patient->contact->addresses[0]->city);
		$this->assertEquals('L4', $patient->contact->addresses[0]->county);
		$this->assertEquals('L5', $patient->contact->addresses[0]->postcode);
		$this->assertInstanceOf('\Country', $patient->contact->addresses[0]->country);
		$this->assertEquals('United Kingdom', $patient->contact->addresses[0]->country->name);
		$this->assertEquals(\AddressType::CORRESPOND, $patient->contact->addresses[0]->address_type_id);
	}

	public function testJsonToResource()
	{
		$json = '{"nhs_num":"54321","hos_num":"12345","title":"Mr","family_name":"Aylward","given_name":"Jim","gender_ref":{"service":"Gender","id":1},"birth_date":{"date":"1970-01-01","timezone_type":3,"timezone":"Europe\/London"},"date_of_death":null,"primary_phone":"07123 456789","addresses":[{"date_start":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"date_end":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"correspond":false,"transport":false,"use":null,"line1":"flat 1","line2":"bleakley creek","city":"flitchley","state":"london","zip":"ec1v 0dx","country":"United States"}],"care_providers":[],"gp_ref":{"service":"Gp","id":2},"prac_ref":{"service":"Practice","id":5},"cb_refs":[],"contact_id":1}';

		$ps = new PatientService;
		$resource = $ps->jsonToResource($json);

		$this->assertEquals('54321',$resource->nhs_num);
		$this->assertEquals('12345',$resource->hos_num);
		$this->assertEquals('Mr',$resource->title);
		$this->assertEquals('Aylward',$resource->family_name);
		$this->assertEquals('Jim',$resource->given_name);
		$this->assertEquals('Male',$resource->getGender());
		$this->assertEquals('1970-01-01',$resource->birth_date->toModelValue());
		$this->assertEquals('07123 456789',$resource->primary_phone);

		$this->assertCount(1, $resource->addresses);
		$this->assertInstanceOf('services\PatientAddress', $resource->addresses[0]);
		$this->assertEquals('flat 1', $resource->addresses[0]->line1);
		$this->assertEquals('bleakley creek', $resource->addresses[0]->line2);
		$this->assertEquals('flitchley', $resource->addresses[0]->city);
		$this->assertEquals('london', $resource->addresses[0]->state);
		$this->assertEquals('ec1v 0dx', $resource->addresses[0]->zip);
		$this->assertEquals('United States', $resource->addresses[0]->country);

		$this->assertInstanceOf('services\\Date',$resource->addresses[0]->date_start);
		$this->assertInstanceOf('services\\Date',$resource->addresses[0]->date_end);
		$this->assertFalse($resource->addresses[0]->correspond);
		$this->assertFalse($resource->addresses[0]->transport);

		$this->assertInstanceOf('services\\GpReference',$resource->gp_ref);
		$this->assertEquals(2, $resource->gp_ref->getId());
		$this->assertEquals('Gp', $resource->gp_ref->getServiceName());

		$this->assertInstanceOf('services\\PracticeReference',$resource->prac_ref);
		$this->assertEquals(5, $resource->prac_ref->getId());
		$this->assertEquals('Practice', $resource->prac_ref->getServiceName());
	}

	public function testJsonToModel_NoSave_NoNewRows()
	{
		$json = '{"nhs_num":"54321","hos_num":"12345","title":"Mr","family_name":"Aylward","given_name":"Jim","gender_ref":{"service":"Gender","id":1},"birth_date":{"date":"1970-01-01","timezone_type":3,"timezone":"Europe/London"},"date_of_death":null,"primary_phone":"07123 456789","addresses":[{"date_start":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"date_end":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"correspond":false,"transport":false,"use":null,"line1":"flat 1","line2":"bleakley creek","city":"flitchley","state":"london","zip":"ec1v 0dx","country":"United States"}],"care_providers":[],"gp_ref":{"service":"Gp","id":1},"prac_ref":{"service":"Practice","id":1},"cb_refs":[],"contact_id":1}';

		$total_patients = count(\Patient::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());
		$total_countries = count(\Country::model()->findAll());
		$total_genders = count(\Gender::model()->findAll());

		$ps = new PatientService;
		$patient = $ps->jsonToModel($json, new \Patient, false);

		$this->assertEquals($total_patients, count(\Patient::model()->findAll()));
		$this->assertEquals($total_contacts, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses, count(\Address::model()->findAll()));
		$this->assertEquals($total_countries, count(\Country::model()->findAll()));
		$this->assertEquals($total_genders, count(\Gender::model()->findAll()));
	}

	public function testJsonToModel_NoSave_ModelIsCorrect()
	{
		$json = '{"nhs_num":"54321","hos_num":"12345","title":"Mr","family_name":"Aylward","given_name":"Jim","gender_ref":{"service":"Gender","id":1},"birth_date":{"date":"1970-01-01","timezone_type":3,"timezone":"Europe/London"},"date_of_death":null,"primary_phone":"07123 456789","addresses":[{"date_start":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"date_end":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"correspond":false,"transport":false,"use":null,"line1":"flat 1","line2":"bleakley creek","city":"flitchley","state":"london","zip":"ec1v 0dx","country":"United States"}],"care_providers":[],"gp_ref":{"service":"Gp","id":1},"prac_ref":{"service":"Practice","id":1},"cb_refs":[],"contact_id":1}';

		$ps = new PatientService;
		$patient = $ps->jsonToModel($json, new \Patient, false);

		$this->assertEquals('54321',$patient->nhs_num);
		$this->assertEquals('12345',$patient->hos_num);
		$this->assertEquals('Mr',$patient->title);
		$this->assertEquals('Aylward',$patient->last_name);
		$this->assertEquals('Jim',$patient->first_name);
		$this->assertInstanceOf('Gender', $patient->gender);
		$this->assertEquals('Male',$patient->gender->name);
		$this->assertEquals('1970-01-01',$patient->dob);
		$this->assertEquals('07123 456789',$patient->contact->primary_phone);

		$this->assertCount(1, $patient->contact->addresses);
		$this->assertInstanceOf('Address', $patient->contact->addresses[0]);
		$this->assertEquals('flat 1', $patient->contact->addresses[0]->address1);
		$this->assertEquals('bleakley creek', $patient->contact->addresses[0]->address2);
		$this->assertEquals('flitchley', $patient->contact->addresses[0]->city);
		$this->assertEquals('london', $patient->contact->addresses[0]->county);
		$this->assertEquals('ec1v 0dx', $patient->contact->addresses[0]->postcode);
		$this->assertInstanceOf('\Country', $patient->contact->addresses[0]->country);
		$this->assertEquals('United States', $patient->contact->addresses[0]->country->name);

		$this->assertEquals(1, $patient->gp_id);
		$this->assertEquals(1, $patient->practice_id);
	}

	public function testJsonToModel_Save_Create_ModelCountsCorrect()
	{
		$json = '{"nhs_num":"54321","hos_num":"12345","title":"Mr","family_name":"Aylward","given_name":"Jim","gender_ref":{"service":"Gender","id":1},"birth_date":{"date":"1970-01-01","timezone_type":3,"timezone":"Europe/London"},"date_of_death":null,"primary_phone":"07123 456789","addresses":[{"date_start":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"date_end":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"correspond":false,"transport":false,"use":null,"line1":"flat 1","line2":"bleakley creek","city":"flitchley","state":"london","zip":"ec1v 0dx","country":"United States"}],"care_providers":[],"gp_ref":{"service":"Gp","id":1},"prac_ref":{"service":"Practice","id":1},"cb_refs":[],"contact_id":1030}';

		$total_patients = count(\Patient::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());
		$total_countries = count(\Country::model()->findAll());
		$total_genders = count(\Gender::model()->findAll());

		$ps = new PatientService;
		$patient = $ps->jsonToModel($json, new \Patient);
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertEquals($total_patients+1, count(\Patient::model()->findAll()));
		$this->assertEquals($total_contacts+1, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses+1, count(\Address::model()->findAll()));
		$this->assertEquals($total_countries, count(\Country::model()->findAll()));
		$this->assertEquals($total_genders, count(\Gender::model()->findAll()));
	}

	public function testJsonToModel_Save_Create_DBIsCorrect()
	{
		$json = '{"nhs_num":"54321","hos_num":"12345","title":"Mr","family_name":"Aylward","given_name":"Jim","gender_ref":{"service":"Gender","id":1},"birth_date":{"date":"1970-01-01","timezone_type":3,"timezone":"Europe/London"},"date_of_death":null,"primary_phone":"07123 456789","addresses":[{"date_start":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"date_end":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"correspond":false,"transport":false,"use":null,"line1":"flat 1","line2":"bleakley creek","city":"flitchley","state":"london","zip":"ec1v 0dx","country":"United States"}],"care_providers":[],"gp_ref":{"service":"Gp","id":1},"prac_ref":{"service":"Practice","id":1},"cb_refs":[],"contact_id":1030}';

		$ps = new PatientService;
		$patient = $ps->jsonToModel($json, new \Patient);
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertEquals('54321',$patient->nhs_num);
		$this->assertEquals('12345',$patient->hos_num);
		$this->assertEquals('Mr',$patient->title);
		$this->assertEquals('Aylward',$patient->last_name);
		$this->assertEquals('Jim',$patient->first_name);
		$this->assertInstanceOf('Gender', $patient->gender);
		$this->assertEquals('Male',$patient->gender->name);
		$this->assertEquals('1970-01-01',$patient->dob);
		$this->assertEquals('07123 456789',$patient->contact->primary_phone);

		$this->assertCount(1, $patient->contact->addresses);
		$this->assertInstanceOf('Address', $patient->contact->addresses[0]);
		$this->assertEquals('flat 1', $patient->contact->addresses[0]->address1);
		$this->assertEquals('bleakley creek', $patient->contact->addresses[0]->address2);
		$this->assertEquals('flitchley', $patient->contact->addresses[0]->city);
		$this->assertEquals('london', $patient->contact->addresses[0]->county);
		$this->assertEquals('ec1v 0dx', $patient->contact->addresses[0]->postcode);
		$this->assertInstanceOf('\Country', $patient->contact->addresses[0]->country);
		$this->assertEquals('United States', $patient->contact->addresses[0]->country->name);

		$this->assertEquals(1, $patient->gp_id);
		$this->assertEquals(1, $patient->practice_id);
	}

	public function testJsonToModel_Save_Update_ModelCountsCorrect()
	{
		$json = '{"nhs_num":"x0001","hos_num":"x0002","title":"x0003","family_name":"x0004","given_name":"x0005","gender_ref":{"service":"Gender","id":2},"birth_date":{"date":"1996-04-20","timezone_type":3,"timezone":"Europe/London"},"date_of_death":null,"primary_phone":"03333 343434","addresses":[{"date_start":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"date_end":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"correspond":false,"transport":true,"use":null,"line1":"flat 1","line2":"bleakley creek","city":"flitchley","state":"london","zip":"ec1v 0dx","country":"United States"}],"care_providers":[],"gp_ref":{"service":"Gp","id":1},"prac_ref":{"service":"Practice","id":1},"cb_refs":[],"contact_id":1}';

		$total_patients = count(\Patient::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());
		$total_countries = count(\Country::model()->findAll());
		$total_genders = count(\Gender::model()->findAll());

		$model = \Patient::model()->findByPk(1);

		$ps = new PatientService;
		$patient = $ps->jsonToModel($json, $model);
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertEquals($total_patients, count(\Patient::model()->findAll()));
		$this->assertEquals($total_contacts, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses, count(\Address::model()->findAll()));
		$this->assertEquals($total_countries, count(\Country::model()->findAll()));
		$this->assertEquals($total_genders, count(\Gender::model()->findAll()));
	}

	public function testJsonToModel_Save_Update_DBIsCorrect()
	{
		$json = '{"nhs_num":"x0001","hos_num":"x0002","title":"x0003","family_name":"x0004","given_name":"x0005","gender_ref":{"service":"Gender","id":2},"birth_date":{"date":"1996-04-20","timezone_type":3,"timezone":"Europe/London"},"date_of_death":null,"primary_phone":"03333 343434","addresses":[{"date_start":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"date_end":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"correspond":false,"transport":true,"use":null,"line1":"L1","line2":"L2","city":"L3","state":"L4","zip":"L5","country":"United Kingdom"}],"care_providers":[],"gp_ref":{"service":"Gp","id":1},"prac_ref":{"service":"Practice","id":1},"cb_refs":[],"contact_id":1}';

		$model = \Patient::model()->findByPk(1);

		$ps = new PatientService;
		$patient = $ps->jsonToModel($json, $model);
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertEquals('x0001',$patient->nhs_num);
		$this->assertEquals('x0002',$patient->hos_num);
		$this->assertEquals('x0003',$patient->title);
		$this->assertEquals('x0004',$patient->last_name);
		$this->assertEquals('x0005',$patient->first_name);
		$this->assertInstanceOf('Gender', $patient->gender);
		$this->assertEquals('Female',$patient->gender->name);
		$this->assertEquals('1996-04-20',$patient->dob);
		$this->assertEquals('03333 343434',$patient->contact->primary_phone);

		$this->assertCount(1, $patient->contact->addresses);
		$this->assertInstanceOf('Address', $patient->contact->addresses[0]);
		$this->assertEquals('L1', $patient->contact->addresses[0]->address1);
		$this->assertEquals('L2', $patient->contact->addresses[0]->address2);
		$this->assertEquals('L3', $patient->contact->addresses[0]->city);
		$this->assertEquals('L4', $patient->contact->addresses[0]->county);
		$this->assertEquals('L5', $patient->contact->addresses[0]->postcode);
		$this->assertEquals(\AddressType::TRANSPORT, $patient->contact->addresses[0]->address_type_id);
		$this->assertInstanceOf('\Country', $patient->contact->addresses[0]->country);
		$this->assertEquals('United Kingdom', $patient->contact->addresses[0]->country->name);

		$this->assertEquals(1, $patient->gp_id);
		$this->assertEquals(1, $patient->practice_id);
	}

	public function testResourceToModel_Condition_Both_True_Exception()
	{
		$resource = $this->getResource();

		$resource->addresses[0]->correspond = true;
		$resource->addresses[0]->transport = true;

		$this->setExpectedException('Exception', 'Unable to differentiate condition as more than one attribute is true.');

		$ps = new PatientService;
		$patient = $ps->resourceToModel($resource, new \Patient);
	}

	public function testResourceToModel_Use_Transport_Address()
	{
		$resource = $this->getResource();
		$resource->addresses[0]->correspond = false;
		$resource->addresses[0]->transport = true;

		$ps = new PatientService;
		$patient = $ps->resourceToModel($resource, new \Patient, false);

		$this->assertCount(1, $patient->contact->addresses);
		$this->assertInstanceOf('Address', $patient->contact->addresses[0]);
		$this->assertInstanceOf('AddressType', $patient->contact->addresses[0]->type);
		$this->assertEquals('Transport', $patient->contact->addresses[0]->type->name);
	}

	public function testResourceToModel_Default_To_Home_Address()
	{
		$resource = $this->getResource();
		$resource->addresses[0]->correspond = false;
		$resource->addresses[0]->transport = false;

		$ps = new PatientService;
		$patient = $ps->resourceToModel($resource, new \Patient, false);

		$this->assertCount(1, $patient->contact->addresses);
		$this->assertInstanceOf('Address', $patient->contact->addresses[0]);
		$this->assertInstanceOf('AddressType', $patient->contact->addresses[0]->type);
		$this->assertEquals('Home', $patient->contact->addresses[0]->type->name);
	}

	public function testResourceToModel_Save_Commissioning_Body_Refs_NoSave_ModelCountsCorrect()
	{
		$resource = $this->getResource();
		$resource->cb_refs = array(\Yii::app()->service->CommissioningBody(1));

		$cb_refs = count(\CommissioningBodyPatientAssignment::model()->findAll());

		$ps = new PatientService;
		$patient = $ps->resourceToModel($resource, new \Patient, false);

		$this->assertEquals($cb_refs, count(\CommissioningBodyPatientAssignment::model()->findAll()));
	}

	public function testResourceToModel_Save_Commissioning_Body_Refs_NoSave_ModelIsCorrect()
	{
		$resource = $this->getResource();
		$resource->cb_refs = array(\Yii::app()->service->CommissioningBody(1));

		$ps = new PatientService;
		$patient = $ps->resourceToModel($resource, new \Patient, false);

		$this->assertCount(1, $patient->commissioningbody_assignments);
		$this->assertEquals('Apple', $patient->commissioningbody_assignments[0]->commissioning_body->name);
	}

	public function testResourceToModel_Save_Commissioning_Body_Refs_Save_ModelCountsCorrect()
	{
		$resource = $this->getResource();
		$resource->cb_refs = array(\Yii::app()->service->CommissioningBody(1));

		$ps = new PatientService;
		$patient = $ps->resourceToModel($resource, new \Patient);

		$this->assertEquals(1, count(\CommissioningBodyPatientAssignment::model()->findAll('patient_id=?',array($patient->id))));
	}

	public function testResourceToModel_Save_Commissioning_Body_Refs_Save_ModelIsCorrect()
	{
		$resource = $this->getResource();
		$resource->cb_refs = array(\Yii::app()->service->CommissioningBody(1));

		$ps = new PatientService;
		$patient = $ps->resourceToModel($resource, new \Patient);
		$patient = \Patient::model()->findByPk($patient->id);
		
		$this->assertCount(1, $patient->commissioningbody_assignments);
		$this->assertEquals('Apple', $patient->commissioningbody_assignments[0]->commissioning_body->name);
	}
}
