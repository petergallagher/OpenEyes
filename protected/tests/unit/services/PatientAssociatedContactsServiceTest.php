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

class PatientAssociatedContactsServiceTest extends \CDbTestCase
{
	public $fixtures = array(
		'patients' => 'Patient',
		'contacts' => 'Contact',
		'pcas' => 'PatientContactAssignment',
		'cls' => 'ContactLocation',
		'institutions' => 'Institution',
		'sites' => 'Site',
	);

	public function testModelToResource_JustContact()
	{
		$patient = $this->patients('patient1');

		$ps = new PatientAssociatedContactsService;

		$resource = $ps->modelToResource($patient);

		$this->assertInstanceOf('services\PatientAssociatedContacts',$resource);
		$this->assertCount(3,$resource->contacts);

		$this->assertInstanceOf('services\PatientAssociatedContact',$resource->contacts[0]);
		$this->assertEquals('Dr',$resource->contacts[0]->title);
		$this->assertEquals('Zhivago',$resource->contacts[0]->family_name);
		$this->assertEquals('Yuri',$resource->contacts[0]->given_name);
		$this->assertEquals('999',$resource->contacts[0]->primary_phone);
		$this->assertNull($resource->contacts[0]->site_ref);
		$this->assertNull($resource->contacts[0]->institution_ref);

		$this->assertInstanceOf('services\PatientAssociatedContact',$resource->contacts[1]);
		$this->assertEquals('Mr',$resource->contacts[1]->title);
		$this->assertEquals('Inc',$resource->contacts[1]->family_name);
		$this->assertEquals('Apple',$resource->contacts[1]->given_name);
		$this->assertEquals('01010101',$resource->contacts[1]->primary_phone);
		$this->assertNull($resource->contacts[1]->site_ref);
		$this->assertNull($resource->contacts[1]->institution_ref);

		$this->assertInstanceOf('services\PatientAssociatedContact',$resource->contacts[2]);
		$this->assertEquals('Ti',$resource->contacts[2]->title);
		$this->assertEquals('Tiss',$resource->contacts[2]->family_name);
		$this->assertEquals('Prac',$resource->contacts[2]->given_name);
		$this->assertEquals('0303032332',$resource->contacts[2]->primary_phone);
		$this->assertNull($resource->contacts[2]->site_ref);
		$this->assertNull($resource->contacts[2]->institution_ref);
	}

	public function testModelToResource_ContactsWithLocations()
	{
		$patient = $this->patients('patient2');

		$ps = new PatientAssociatedContactsService;

		$resource = $ps->modelToResource($patient);

		$this->assertInstanceOf('services\PatientAssociatedContacts',$resource);
		$this->assertCount(3,$resource->contacts);

		$this->assertInstanceOf('services\PatientAssociatedContact',$resource->contacts[0]);
		$this->assertEquals('Dr',$resource->contacts[0]->title);
		$this->assertEquals('Zhivago',$resource->contacts[0]->family_name);
		$this->assertEquals('Yuri',$resource->contacts[0]->given_name);
		$this->assertEquals('999',$resource->contacts[0]->primary_phone);
		$this->assertInstanceOf('services\SiteReference',$resource->contacts[0]->site_ref);
		$this->assertEquals(1,$resource->contacts[0]->site_ref->getId());
		$this->assertNull($resource->contacts[0]->institution_ref);

		$this->assertInstanceOf('services\PatientAssociatedContact',$resource->contacts[1]);
		$this->assertEquals('Mr',$resource->contacts[1]->title);
		$this->assertEquals('Inc',$resource->contacts[1]->family_name);
		$this->assertEquals('Apple',$resource->contacts[1]->given_name);
		$this->assertEquals('01010101',$resource->contacts[1]->primary_phone);
		$this->assertInstanceOf('services\SiteReference',$resource->contacts[1]->site_ref);
		$this->assertEquals(2,$resource->contacts[1]->site_ref->getId());
		$this->assertNull($resource->contacts[1]->institution_ref);

		$this->assertInstanceOf('services\PatientAssociatedContact',$resource->contacts[2]);
		$this->assertEquals('Ti',$resource->contacts[2]->title);
		$this->assertEquals('Tiss',$resource->contacts[2]->family_name);
		$this->assertEquals('Prac',$resource->contacts[2]->given_name);
		$this->assertEquals('0303032332',$resource->contacts[2]->primary_phone);
		$this->assertInstanceOf('services\InstitutionReference',$resource->contacts[2]->institution_ref);
		$this->assertEquals(2,$resource->contacts[2]->institution_ref->getId());
		$this->assertNull($resource->contacts[2]->site_ref);
	}

	public function testModelToResource_JustContacts_and_ContactsWithLocations()
	{
		$patient = $this->patients('patient3');

		$ps = new PatientAssociatedContactsService;

		$resource = $ps->modelToResource($patient);

		$this->assertInstanceOf('services\PatientAssociatedContacts',$resource);
		$this->assertCount(3,$resource->contacts);

		$this->assertInstanceOf('services\PatientAssociatedContact',$resource->contacts[0]);
		$this->assertEquals('Dr',$resource->contacts[0]->title);
		$this->assertEquals('Zhivago',$resource->contacts[0]->family_name);
		$this->assertEquals('Yuri',$resource->contacts[0]->given_name);
		$this->assertEquals('999',$resource->contacts[0]->primary_phone);
		$this->assertNull($resource->contacts[0]->site_ref);
		$this->assertNull($resource->contacts[0]->institution_ref);

		$this->assertInstanceOf('services\PatientAssociatedContact',$resource->contacts[1]);
		$this->assertEquals('Mr',$resource->contacts[1]->title);
		$this->assertEquals('Inc',$resource->contacts[1]->family_name);
		$this->assertEquals('Apple',$resource->contacts[1]->given_name);
		$this->assertEquals('01010101',$resource->contacts[1]->primary_phone);
		$this->assertInstanceOf('services\SiteReference',$resource->contacts[1]->site_ref);
		$this->assertEquals(2,$resource->contacts[1]->site_ref->getId());
		$this->assertNull($resource->contacts[1]->institution_ref);

		$this->assertInstanceOf('services\PatientAssociatedContact',$resource->contacts[2]);
		$this->assertEquals('Ti',$resource->contacts[2]->title);
		$this->assertEquals('Tiss',$resource->contacts[2]->family_name);
		$this->assertEquals('Prac',$resource->contacts[2]->given_name);
		$this->assertEquals('0303032332',$resource->contacts[2]->primary_phone);
		$this->assertInstanceOf('services\InstitutionReference',$resource->contacts[2]->institution_ref);
		$this->assertEquals(2,$resource->contacts[2]->institution_ref->getId());
		$this->assertNull($resource->contacts[2]->site_ref);
	}

	public function getResource()
	{
		$resource = new PatientAssociatedContacts(1);

		$contact1 = new PatientAssociatedContact;
		$contact1->title = 'Dr';
		$contact1->given_name = 'Hunter';
		$contact1->family_name = 'Thompson';
		$contact1->primary_phone = '02223321145';
		$contact1->site_ref = \Yii::app()->service->Site(1);

		$contact2 = new PatientAssociatedContact;
		$contact2->title = 'Dr';
		$contact2->given_name = 'Hughie';
		$contact2->family_name = 'Louie';
		$contact2->primary_phone = '3024302149';
		$contact2->institution_ref = \Yii::app()->service->Institution(1);

		$contact3 = new PatientAssociatedContact;
		$contact3->title = 'Dr';
		$contact3->given_name = 'Ted';
		$contact3->family_name = 'Baker';
		$contact3->primary_phone = '123123123';

		$resource->contacts = array($contact1,$contact2,$contact3);

		return $resource;
	}

	public function testResourceToModel_NoSave_NoNewRecords()
	{
		$resource = $this->getResource();

		$total_pcas = count(\PatientContactAssignment::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());

		$ps = new PatientAssociatedContactsService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'), false);

		$this->assertEquals($total_pcas, count(\PatientContactAssignment::model()->findAll()));
		$this->assertEquals($total_contacts, count(\Contact::model()->findAll()));
	}

	public function testResourceToModel_NoSave_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientAssociatedContactsService;
		$patient = $ps->resourceToModel($resource, new \Patient, false);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(3,$patient->contactAssignments);

		$this->assertNull($patient->contactAssignments[0]->contact);
		$this->assertInstanceOf('ContactLocation',$patient->contactAssignments[0]->location);
		$this->assertInstanceOf('Contact',$patient->contactAssignments[0]->location->contact);
		$this->assertEquals('Dr',$patient->contactAssignments[0]->location->contact->title);
		$this->assertEquals('Hunter',$patient->contactAssignments[0]->location->contact->first_name);
		$this->assertEquals('Thompson',$patient->contactAssignments[0]->location->contact->last_name);
		$this->assertEquals(1,$patient->contactAssignments[0]->location->site_id);
		$this->assertNull($patient->contactAssignments[0]->location->institution_id);

		$this->assertNull($patient->contactAssignments[1]->contact);
		$this->assertInstanceOf('ContactLocation',$patient->contactAssignments[1]->location);
		$this->assertInstanceOf('Contact',$patient->contactAssignments[1]->location->contact);
		$this->assertEquals('Dr',$patient->contactAssignments[1]->location->contact->title);
		$this->assertEquals('Hughie',$patient->contactAssignments[1]->location->contact->first_name);
		$this->assertEquals('Louie',$patient->contactAssignments[1]->location->contact->last_name);
		$this->assertInstanceOf('ContactLocation',$patient->contactAssignments[1]->location);
		$this->assertEquals(1,$patient->contactAssignments[1]->location->institution_id);
		$this->assertNull($patient->contactAssignments[1]->location->site_id);

		$this->assertNull($patient->contactAssignments[2]->location);
		$this->assertInstanceOf('Contact',$patient->contactAssignments[2]->contact);
		$this->assertEquals('Dr',$patient->contactAssignments[2]->contact->title);
		$this->assertEquals('Ted',$patient->contactAssignments[2]->contact->first_name);
		$this->assertEquals('Baker',$patient->contactAssignments[2]->contact->last_name);
	}

	public function testResourceToModel_Save_Create_ModelCountsCorrect()
	{
		$resource = $this->getResource();

		$total_pcas = count(\PatientContactAssignment::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());

		$ps = new PatientAssociatedContactsService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'));

		$this->assertEquals($total_pcas, count(\PatientContactAssignment::model()->findAll()));
		$this->assertEquals($total_contacts+3, count(\Contact::model()->findAll()));
	}

	public function testResourceToModel_Save_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientAssociatedContactsService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'));

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(3,$patient->contactAssignments);

		$this->assertNull($patient->contactAssignments[0]->contact);
		$this->assertInstanceOf('ContactLocation',$patient->contactAssignments[0]->location);
		$this->assertInstanceOf('Contact',$patient->contactAssignments[0]->location->contact);
		$this->assertEquals('Dr',$patient->contactAssignments[0]->location->contact->title);
		$this->assertEquals('Hunter',$patient->contactAssignments[0]->location->contact->first_name);
		$this->assertEquals('Thompson',$patient->contactAssignments[0]->location->contact->last_name);
		$this->assertEquals(1,$patient->contactAssignments[0]->location->site_id);
		$this->assertNull($patient->contactAssignments[0]->location->institution_id);

		$this->assertNull($patient->contactAssignments[1]->contact);
		$this->assertInstanceOf('ContactLocation',$patient->contactAssignments[1]->location);
		$this->assertInstanceOf('Contact',$patient->contactAssignments[1]->location->contact);
		$this->assertEquals('Dr',$patient->contactAssignments[1]->location->contact->title);
		$this->assertEquals('Hughie',$patient->contactAssignments[1]->location->contact->first_name);
		$this->assertEquals('Louie',$patient->contactAssignments[1]->location->contact->last_name);
		$this->assertInstanceOf('ContactLocation',$patient->contactAssignments[1]->location);
		$this->assertEquals(1,$patient->contactAssignments[1]->location->institution_id);
		$this->assertNull($patient->contactAssignments[1]->location->site_id);

		$this->assertNull($patient->contactAssignments[2]->location);
		$this->assertInstanceOf('Contact',$patient->contactAssignments[2]->contact);
		$this->assertEquals('Dr',$patient->contactAssignments[2]->contact->title);
		$this->assertEquals('Ted',$patient->contactAssignments[2]->contact->first_name);
		$this->assertEquals('Baker',$patient->contactAssignments[2]->contact->last_name);
	}

/*
	public function testResourceToModel_Save_Create_DBIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientService;
		$patient = $ps->resourceToModel($resource);
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

	public function getModifiedResource($id)
	{
		$resource = \Yii::app()->service->Patient($id)->fetch();

		$resource->nhs_num = 'x0000';
		$resource->hos_num = 'x0001';
		$resource->title = 'x0002';
		$resource->family_name = 'x0003';
		$resource->given_name = 'x0004';
		$resource->gender_ref = \Yii::app()->service->Gender(\Gender::model()->find('name=?',array('Female'))->id);
		$resource->birth_date = '1988-04-04';
		$resource->primary_phone = '0101010101';
		$resource->gp_ref = \Yii::app()->service->Gp(1);
		$resource->prac_ref = \Yii::app()->service->Practice(1);
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
		$resource = $this->getModifiedResource(1);

		$total_patients = count(\Patient::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());
		$total_countries = count(\Country::model()->findAll());
		$total_genders = count(\Gender::model()->findAll());

		$ps = new PatientService;
		$patient = $ps->resourceToModel($resource);

		$this->assertEquals($total_patients, count(\Patient::model()->findAll()));
		$this->assertEquals($total_contacts, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses, count(\Address::model()->findAll()));
		$this->assertEquals($total_countries, count(\Country::model()->findAll()));
		$this->assertEquals($total_genders, count(\Gender::model()->findAll()));
	}

	public function testResourceToModel_Save_Update_DBIsCorrect()
	{
		$resource = $this->getModifiedResource(1);

		$ps = new PatientService;
		$patient = $ps->resourceToModel($resource);
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
		$json = '{"nhs_num":"54321","hos_num":"12345","title":"Mr","family_name":"Aylward","given_name":"Jim","gender_ref":{"service":"Gender","id":1},"birth_date":"1970-01-01","date_of_death":null,"primary_phone":"07123 456789","addresses":[{"date_start":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"date_end":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"correspond":false,"transport":false,"use":null,"line1":"flat 1","line2":"bleakley creek","city":"flitchley","state":"london","zip":"ec1v 0dx","country":"United States"}],"care_providers":[],"gp_ref":{"service":"Gp","id":2},"prac_ref":{"service":"Practice","id":5},"cb_refs":[],"id":null,"last_modified":null}';

		$ps = new PatientService;
		$resource = $ps->jsonToResource($json);

		$this->assertEquals('54321',$resource->nhs_num);
		$this->assertEquals('12345',$resource->hos_num);
		$this->assertEquals('Mr',$resource->title);
		$this->assertEquals('Aylward',$resource->family_name);
		$this->assertEquals('Jim',$resource->given_name);
		$this->assertEquals('Male',$resource->getGender());
		$this->assertEquals('1970-01-01',$resource->birth_date);
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

	public function jsonToModel_NoSave_NoNewRows()
	{
		$json = '{"nhs_num":"54321","hos_num":"12345","title":"Mr","family_name":"Aylward","given_name":"Jim","gender_ref":{"service":"Gender","id":1},"birth_date":"1970-01-01","date_of_death":null,"primary_phone":"07123 456789","addresses":[{"date_start":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"date_end":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"correspond":false,"transport":false,"use":null,"line1":"flat 1","line2":"bleakley creek","city":"flitchley","state":"london","zip":"ec1v 0dx","country":"United States"}],"care_providers":[],"gp_ref":{"service":"Gp","id":1},"prac_ref":{"service":"Practice","id":1},"cb_refs":[],"id":null,"last_modified":null}';

		$total_patients = count(\Patient::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());
		$total_countries = count(\Country::model()->findAll());
		$total_genders = count(\Gender::model()->findAll());

		$ps = new PatientService;
		$patient = $ps->jsonToModel($json, false);

		$this->assertEquals($total_patients, count(\Patient::model()->findAll()));
		$this->assertEquals($total_contacts, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses, count(\Address::model()->findAll()));
		$this->assertEquals($total_countries, count(\Country::model()->findAll()));
		$this->assertEquals($total_genders, count(\Gender::model()->findAll()));
	}

	public function testJsonToModel_NoSave_ModelIsCorrect()
	{
		$json = '{"nhs_num":"54321","hos_num":"12345","title":"Mr","family_name":"Aylward","given_name":"Jim","gender_ref":{"service":"Gender","id":1},"birth_date":"1970-01-01","date_of_death":null,"primary_phone":"07123 456789","addresses":[{"date_start":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"date_end":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"correspond":false,"transport":false,"use":null,"line1":"flat 1","line2":"bleakley creek","city":"flitchley","state":"london","zip":"ec1v 0dx","country":"United States"}],"care_providers":[],"gp_ref":{"service":"Gp","id":1},"prac_ref":{"service":"Practice","id":1},"cb_refs":[],"id":null,"last_modified":null}';

		$ps = new PatientService;
		$patient = $ps->jsonToModel($json, false);

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
		$json = '{"nhs_num":"54321","hos_num":"12345","title":"Mr","family_name":"Aylward","given_name":"Jim","gender_ref":{"service":"Gender","id":1},"birth_date":"1970-01-01","date_of_death":null,"primary_phone":"07123 456789","addresses":[{"date_start":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"date_end":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"correspond":false,"transport":false,"use":null,"line1":"flat 1","line2":"bleakley creek","city":"flitchley","state":"london","zip":"ec1v 0dx","country":"United States"}],"care_providers":[],"gp_ref":{"service":"Gp","id":1},"prac_ref":{"service":"Practice","id":1},"cb_refs":[],"id":null,"last_modified":null}';

		$total_patients = count(\Patient::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());
		$total_countries = count(\Country::model()->findAll());
		$total_genders = count(\Gender::model()->findAll());

		$ps = new PatientService;
		$patient = $ps->jsonToModel($json);
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertEquals($total_patients+1, count(\Patient::model()->findAll()));
		$this->assertEquals($total_contacts+1, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses+1, count(\Address::model()->findAll()));
		$this->assertEquals($total_countries, count(\Country::model()->findAll()));
		$this->assertEquals($total_genders, count(\Gender::model()->findAll()));
	}

	public function testJsonToModel_Save_Create_DBIsCorrect()
	{
		$json = '{"nhs_num":"54321","hos_num":"12345","title":"Mr","family_name":"Aylward","given_name":"Jim","gender_ref":{"service":"Gender","id":1},"birth_date":"1970-01-01","date_of_death":null,"primary_phone":"07123 456789","addresses":[{"date_start":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"date_end":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"correspond":false,"transport":false,"use":null,"line1":"flat 1","line2":"bleakley creek","city":"flitchley","state":"london","zip":"ec1v 0dx","country":"United States"}],"care_providers":[],"gp_ref":{"service":"Gp","id":1},"prac_ref":{"service":"Practice","id":1},"cb_refs":[],"id":null,"last_modified":null}';

		$ps = new PatientService;
		$patient = $ps->jsonToModel($json);
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
		$json = '{"nhs_num":"x0001","hos_num":"x0002","title":"x0003","family_name":"x0004","given_name":"x0005","gender_ref":{"service":"Gender","id":2},"birth_date":"1996-04-20","date_of_death":null,"primary_phone":"03333 343434","addresses":[{"date_start":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"date_end":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"correspond":false,"transport":true,"use":null,"line1":"flat 1","line2":"bleakley creek","city":"flitchley","state":"london","zip":"ec1v 0dx","country":"United States"}],"care_providers":[],"gp_ref":{"service":"Gp","id":1},"prac_ref":{"service":"Practice","id":1},"cb_refs":[],"id":1,"last_modified":null}';

		$total_patients = count(\Patient::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());
		$total_countries = count(\Country::model()->findAll());
		$total_genders = count(\Gender::model()->findAll());

		$ps = new PatientService;
		$patient = $ps->jsonToModel($json);
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertEquals($total_patients, count(\Patient::model()->findAll()));
		$this->assertEquals($total_contacts, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses, count(\Address::model()->findAll()));
		$this->assertEquals($total_countries, count(\Country::model()->findAll()));
		$this->assertEquals($total_genders, count(\Gender::model()->findAll()));
	}

	public function testJsonToModel_Save_Update_DBIsCorrect()
	{
		$json = '{"nhs_num":"x0001","hos_num":"x0002","title":"x0003","family_name":"x0004","given_name":"x0005","gender_ref":{"service":"Gender","id":2},"birth_date":"1996-04-20","date_of_death":null,"primary_phone":"03333 343434","addresses":[{"date_start":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"date_end":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"correspond":false,"transport":true,"use":null,"line1":"L1","line2":"L2","city":"L3","state":"L4","zip":"L5","country":"United Kingdom"}],"care_providers":[],"gp_ref":{"service":"Gp","id":1},"prac_ref":{"service":"Practice","id":1},"cb_refs":[],"id":1,"last_modified":null}';

		$ps = new PatientService;
		$patient = $ps->jsonToModel($json);
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
		$patient = $ps->resourceToModel($resource);
	}
	*/
}
