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

class ModelConverterTest extends \CDbTestCase
{
	public $fixtures = array(
		'patients' => 'Patient',
		'contacts' => 'Contact',
		'addresses' => 'Address',
		'countries' => 'Country',
		'practices' => 'Practice',
		'gps' => 'Gp',
	);

	public function testModelToResource_DirectKeys()
	{
		$patient = new \Patient;

		foreach (array('hos_num','nhs_num','dob','date_of_death') as $field) {
			if (strstr($field,'date')) {
				$patient->$field = date('Y-m-d',rand(1000,time()));
			} else {
				$patient->$field = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, rand(3,10));
			}
		}

		$map = array(
			'Patient' => array(
				'fields' => array(
					'nhs_num' => 'nhs_num',
					'hos_num' => 'hos_num',
					'birth_date' => 'dob',
					'date_of_death' => 'date_of_death',
				),
			)
		);

		$ps = new PatientService;
		$ps->map = new ModelMap($map);

		$op = new ModelConverter($ps);

		$resource = $op->modelToResource($patient, new Patient(array()));

		$this->assertInstanceOf('services\\Patient',$resource);
		$this->assertEquals($patient->hos_num, $resource->hos_num);
		$this->assertEquals($patient->nhs_num, $resource->nhs_num);
		$this->assertEquals($patient->dob, $resource->birth_date);
		$this->assertEquals($patient->date_of_death, $resource->date_of_death);
	}

	public function testModelToResource_RelationKeys()
	{
		$patient = new \Patient;
		$contact = new \Contact;

		foreach (array('title','last_name','first_name','primary_phone') as $field) {
			$contact->$field = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, rand(3,10));
		}

		$patient->contact = $contact;

		$map = array(
			'Patient' => array(
				'related_objects' => array(
					'contact' => array('contact_id', 'Contact'),
				),
				'fields' => array(
					'title' => 'contact.title',
					'family_name' => 'contact.last_name',
					'given_name' => 'contact.first_name',
					'primary_phone' => 'contact.primary_phone',
				),
			)
		);

		$ps = new PatientService;
		$ps->map = new ModelMap($map);

		$op = new ModelConverter($ps);

		$resource = $op->modelToResource($patient, new Patient(array()));

		$this->assertInstanceOf('services\\Patient',$resource);
		$this->assertEquals($contact->title, $resource->title);
		$this->assertEquals($contact->last_name, $resource->family_name);
		$this->assertEquals($contact->first_name, $resource->given_name);
		$this->assertEquals($contact->primary_phone, $resource->primary_phone);
	}

	public function testModelToResource_Lists()
	{
		$patient = new \Patient;
		$contact = new \Contact;
		$address = new \Address;

		foreach (array('address1','address2','city','postcode','county') as $field) {
			$address->$field = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, rand(3,10));
		}

		$address->country_id = 1;

		$contact->addresses = array($address);
		$patient->contact = $contact;

		$map = array(
			'Patient' => array(
				'related_objects' => array(
					'contact' => array('contact_id', 'Contact'),
				),
				'fields' => array(
					'addresses' => array(DeclarativeModelService::TYPE_LIST, 'contact.addresses', 'PatientAddress', 'Address', 'contact_id'),
				),
			),
			'PatientAddress' => array(
				'ar_model' => 'Address',
				'related_objects' => array(
					'contact' => array('contact_id', 'Contact'),
				),
				'reference_objects' => array(
					'country' => array('country_id', 'Country', array('name')),
				),
				'fields' => array(
					'line1' => 'address1',
					'line2' => 'address2',
					'city' => 'city',
					'state' => 'county',
					'zip' => 'postcode',
					'country' => 'country.name',
				),
			),
		);

		$ps = new PatientService;
		$ps->map = new ModelMap($map);

		$op = new ModelConverter($ps);

		$resource = $op->modelToResource($patient, new Patient(array()));

		$this->assertInstanceOf('services\\Patient',$resource);
		$this->assertCount(1, $resource->addresses);
		$this->assertInstanceOf('services\\PatientAddress',$resource->addresses[0]);
		$this->assertEquals($address->address1, $resource->addresses[0]->line1);
		$this->assertEquals($address->address2, $resource->addresses[0]->line2);
		$this->assertEquals($address->city, $resource->addresses[0]->city);
		$this->assertEquals($address->county, $resource->addresses[0]->state);
		$this->assertEquals($address->postcode, $resource->addresses[0]->zip);
		$this->assertEquals($address->country->name, $resource->addresses[0]->country);
	}

	public function testModelToResource_References()
	{
		$patient = new \Patient;

		$patient->gp_id = 17;
		$patient->practice_id = 41;

		$map = array(
			'Patient' => array(
				'fields' => array(
					'gp_ref' => array(DeclarativeModelService::TYPE_REF, 'gp_id', 'Gp'),
					'prac_ref' => array(DeclarativeModelService::TYPE_REF, 'practice_id', 'Practice'),
				),
			)
		);

		$ps = new PatientService;
		$ps->map = new ModelMap($map);

		$op = new ModelConverter($ps);

		$resource = $op->modelToResource($patient, new Patient(array()));

		$this->assertInstanceOf('services\\Patient',$resource);

		$this->assertInstanceOf('services\\GpReference',$resource->gp_ref);
		$this->assertEquals($patient->gp_id, $resource->gp_ref->getId());
		$this->assertEquals('Gp', $resource->gp_ref->getServiceName());

		$this->assertInstanceOf('services\\PracticeReference',$resource->prac_ref);
		$this->assertEquals($patient->practice_id, $resource->prac_ref->getId());
		$this->assertEquals('Practice', $resource->prac_ref->getServiceName());
	}

	public function testModelToResource_DateObjects()
	{
		$patient = new \Patient;
		$contact = new \Contact;
		$address = new \Address;

		$address->date_start = '2012-01-01';
		$address->date_end = '2013-04-05';

		$contact->addresses = array($address);
		$patient->contact = $contact;

		$map = array(
			'Patient' => array(
				'related_objects' => array(
					'contact' => array('contact_id', 'Contact'),
				),
				'fields' => array(
					'addresses' => array(DeclarativeModelService::TYPE_LIST, 'contact.addresses', 'PatientAddress', 'Address'),
				),
			),
			'PatientAddress' => array(
				'ar_model' => 'Address',
				'related_objects' => array(
					'contact' => array('contact_id', 'Contact'),
				),
				'fields' => array(
					'date_start' => array(DeclarativeModelService::TYPE_SIMPLEOBJECT, 'date_start', 'Date'),
					'date_end' => array(DeclarativeModelService::TYPE_SIMPLEOBJECT, 'date_end', 'Date'),
				),
			),
		);

		$ps = new PatientService;
		$ps->map = new ModelMap($map);

		$op = new ModelConverter($ps);

		$resource = $op->modelToResource($patient, new Patient(array()));

		$this->assertInstanceOf('services\\Patient',$resource);
		$this->assertCount(1, $resource->addresses);
		$this->assertInstanceOf('services\\PatientAddress',$resource->addresses[0]);

		$this->assertInstanceOf('services\\Date',$resource->addresses[0]->date_start);
		$this->assertEquals(strtotime($address->date_start),$resource->addresses[0]->date_start->getTimestamp());
		$this->assertInstanceOf('services\\Date',$resource->addresses[0]->date_end);
		$this->assertEquals(strtotime($address->date_end),$resource->addresses[0]->date_end->getTimestamp());
	}

	public function testModelToResource_ConditionalBooleans()
	{
		$patient = new \Patient;
		$contact = new \Contact;
		$address = new \Address;
	
		$address->address_type_id = 3;

		$contact->addresses = array($address);
		$patient->contact = $contact;

		$map = array(
			'Patient' => array(
				'related_objects' => array(
					'contact' => array('contact_id', 'Contact'),
				),
				'fields' => array(
					'addresses' => array(DeclarativeModelService::TYPE_LIST, 'contact.addresses', 'PatientAddress', 'Address'),
				),
			),
			'PatientAddress' => array(
				'ar_model' => 'Address',
				'fields' => array(
					'correspond' => array(DeclarativeModelService::TYPE_CONDITION, 'address_type_id', 'equals', \AddressType::CORRESPOND),
					'transport' => array(DeclarativeModelService::TYPE_CONDITION, 'address_type_id', 'equals', \AddressType::TRANSPORT),
				),
			),
		);

		$ps = new PatientService;
		$ps->map = new ModelMap($map);

		$op = new ModelConverter($ps);

		$resource = $op->modelToResource($patient, new Patient(array()));

		$this->assertInstanceOf('services\\Patient',$resource);
		$this->assertCount(1, $resource->addresses);
		$this->assertInstanceOf('services\\PatientAddress',$resource->addresses[0]);

		$this->assertTrue($resource->addresses[0]->correspond);
		$this->assertFalse($resource->addresses[0]->transport);

		$address->address_type_id = 4;

		$resource = $op->modelToResource($patient, new Patient(array()));

		$this->assertInstanceOf('services\\Patient',$resource);
		$this->assertCount(1, $resource->addresses);
		$this->assertInstanceOf('services\\PatientAddress',$resource->addresses[0]);

		$this->assertFalse($resource->addresses[0]->correspond);
		$this->assertTrue($resource->addresses[0]->transport);
	}

	public function testModelToResource_FullPatient()
	{
		$patient = $this->patients('patient1');
		$contact = $this->contacts('contact1');
		$address = $this->addresses('address1');

		$contact->addresses = array($address);
		$patient->contact = $contact;

		$patient->gp_id = 2;
		$patient->practice_id = 5;

		$map = PatientService::getModelMap();

		$ps = new PatientService;
		$ps->map = new ModelMap($map);

		$op = new ModelConverter($ps);

		$resource = $op->modelToResource($patient, new Patient(array()));

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
		$this->assertEquals('flat 1', $resource->addresses[0]->line1);
		$this->assertEquals('bleakley creek', $resource->addresses[0]->line2);
		$this->assertEquals('flitchley', $resource->addresses[0]->city);
		$this->assertEquals('london', $resource->addresses[0]->state);
		$this->assertEquals('ec1v 0dx', $resource->addresses[0]->zip);
		$this->assertEquals('United States',$resource->addresses[0]->country);

		$this->assertInstanceOf('services\\Date',$resource->addresses[0]->date_start);
		$this->assertEquals(strtotime('2013-01-03'),$resource->addresses[0]->date_start->getTimestamp());
		$this->assertInstanceOf('services\\Date',$resource->addresses[0]->date_end);
		$this->assertEquals(strtotime('2014-05-05'),$resource->addresses[0]->date_end->getTimestamp());
		$this->assertFalse($resource->addresses[0]->correspond);
		$this->assertFalse($resource->addresses[0]->transport);

		$this->assertInstanceOf('services\\GpReference',$resource->gp_ref);
		$this->assertEquals($patient->gp_id, $resource->gp_ref->getId());
		$this->assertEquals('Gp', $resource->gp_ref->getServiceName());

		$this->assertInstanceOf('services\\PracticeReference',$resource->prac_ref);
		$this->assertEquals($patient->practice_id, $resource->prac_ref->getId());
		$this->assertEquals('Practice', $resource->prac_ref->getServiceName());
	}

	public function testResourceToModel_NoSave_NoNewRecords()
	{
		$gender = \Yii::app()->service->Gender(1);

		$date = new Date;

		$address = new Address;
		$address->date_start = $date;
		$address->date_end = $date;
		$address->line1 = 'flat 1';
		$address->line2 = 'bleakley creek';
		$address->city = 'flitchley';
		$address->state = 'london';
		$address->zip = 'ec1v 0dx';
		$address->country = 'United States';
		$address->correspond = false;
		$address->transport = false;

		$resource = new Patient;
		$resource->nhs_num = '54321';
		$resource->hos_num = '12345';
		$resource->title = 'Mr';
		$resource->family_name = 'Aylward';
		$resource->given_name = 'Jim';
		$resource->gender_ref = $gender;
		$resource->birth_date = new Date('1970-01-01');
		$resource->primary_phone = '07123 456789';
		$resource->addresses = array($address);
		$resource->gp_ref = \Yii::app()->service->Gp(2);
		$resource->prac_ref = \Yii::app()->service->Practice(1);

		$total_patients = count(\Patient::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());
		$total_countries = count(\Country::model()->findAll());
		$total_genders = count(\Gender::model()->findAll());

		$mc = new ModelConverter(new PatientService);

		$patient = $mc->resourceToModel($resource, new \Patient, false);

		$this->assertEquals($total_patients, count(\Patient::model()->findAll()));
		$this->assertEquals($total_contacts, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses, count(\Address::model()->findAll()));
		$this->assertEquals($total_countries, count(\Country::model()->findAll()));
		$this->assertEquals($total_genders, count(\Gender::model()->findAll()));
	}

	public function testResourceToModel_NoSave_ModelIsCorrect()
	{
		$gender = \Yii::app()->service->Gender(1);

		$date = new Date;

		$address = new Address;
		$address->date_start = $date;
		$address->date_end = $date;
		$address->line1 = 'flat 1';
		$address->line2 = 'bleakley creek';
		$address->city = 'flitchley';
		$address->state = 'london';
		$address->zip = 'ec1v 0dx';
		$address->country = 'United States';
		$address->correspond = false;
		$address->transport = false;

		$resource = new Patient;
		$resource->nhs_num = '54321';
		$resource->hos_num = '12345';
		$resource->title = 'Mr';
		$resource->family_name = 'Aylward';
		$resource->given_name = 'Jim';
		$resource->gender_ref = $gender;
		$resource->birth_date = new Date('1970-01-01');
		$resource->primary_phone = '07123 456789';
		$resource->addresses = array($address);
		$resource->gp_ref = \Yii::app()->service->Gp(2);
		$resource->prac_ref = \Yii::app()->service->Practice(1);

		$mc = new ModelConverter(new PatientService);
		$patient = $mc->resourceToModel($resource, new \Patient, false);

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
		$this->assertEquals('bleakley creek', $resource->addresses[0]->line2);
		$this->assertEquals('flitchley', $resource->addresses[0]->city);
		$this->assertEquals('london', $resource->addresses[0]->state);
		$this->assertEquals('ec1v 0dx', $resource->addresses[0]->zip);
		$this->assertEquals('United States', $resource->addresses[0]->country);

		$this->assertEquals(2, $patient->gp_id);
		$this->assertEquals(1, $patient->practice_id);
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

	public function testResourceToModel_Save_ModelCountsCorrect()
	{
		$resource = $this->getResource();

		$total_patients = count(\Patient::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());
		$total_countries = count(\Country::model()->findAll());
		$total_genders = count(\Gender::model()->findAll());

		$mc = new ModelConverter(new PatientService);
		$patient = $mc->resourceToModel($resource, new \Patient);

		$this->assertEquals($total_patients+1, count(\Patient::model()->findAll()));
		$this->assertEquals($total_contacts+1, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses+1, count(\Address::model()->findAll()));
		$this->assertEquals($total_countries, count(\Country::model()->findAll()));
		$this->assertEquals($total_genders, count(\Gender::model()->findAll()));
	}

	public function testResourceToModel_Save_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$mc = new ModelConverter(new PatientService);
		$patient = $mc->resourceToModel($resource, new \Patient);

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

	public function testResourceToModel_Save_DBIsCorrect()
	{
		$resource = $this->getResource();

		$mc = new ModelConverter(new PatientService);
		$patient = $mc->resourceToModel($resource, new \Patient);
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

	public function testModelToResource_EmptyReference()
	{
		$map = array(
			'Patient' => array(
				'fields' => array(
					'gp_ref' => array(DeclarativeModelService::TYPE_REF, 'gp_id', 'Gp')
				),
			),
		);

		$ps = new PatientService;
		$ps->map = new ModelMap($map);

		$c = new ModelConverter($ps);

		$res = $c->modelToResource(new \Patient, new Patient);

		$this->assertNull($res->gp_ref);
	}
}
