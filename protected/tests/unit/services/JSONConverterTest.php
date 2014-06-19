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

class JSONConverterTest extends \CDbTestCase
{
	public $fixtures = array(
		'patients' => 'Patient',
		'contacts' => 'Contact',
		'addresses' => 'Address',
		'practices' => 'Practice',
	);

	public function testParse_DirectKeys()
	{
		$json = '{"nhs_num":"dJ6LM2","hos_num":"ZrQt","title":null,"family_name":null,"given_name":null,"gender":null,"birth_date":"8U4Knwvx","date_of_death":"1983-08-01","primary_phone":null,"addresses":[],"care_providers":[],"gp_ref":null,"prac_ref":null,"cb_refs":[],"id":null,"last_modified":null}';

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

		$op = new JSONConverter($map);

		$resource = $op->jsonToResource($json, 'Patient', new Patient(array()));

		$this->assertInstanceOf('services\\Patient',$resource);
		$this->assertEquals('ZrQt', $resource->hos_num);
		$this->assertEquals('dJ6LM2', $resource->nhs_num);
		$this->assertEquals('8U4Knwvx', $resource->birth_date);
		$this->assertEquals('1983-08-01', $resource->date_of_death);
	}

	public function testParse_RelationKeys()
	{
		$json = '{"nhs_num":null,"hos_num":null,"title":"SLXbgEH","family_name":"Gy39Y","given_name":"up2gH","gender":null,"birth_date":null,"date_of_death":null,"primary_phone":"KweBN","addresses":[],"care_providers":[],"gp_ref":null,"prac_ref":null,"cb_refs":[],"id":null,"last_modified":null}';

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

		$op = new JSONConverter($map);

		$resource = $op->jsonToResource($json, 'Patient', new Patient(array()));

		$this->assertInstanceOf('services\\Patient',$resource);
		$this->assertEquals('SLXbgEH', $resource->title);
		$this->assertEquals('Gy39Y', $resource->family_name);
		$this->assertEquals('up2gH', $resource->given_name);
		$this->assertEquals('KweBN', $resource->primary_phone);
	}

	public function testParse_Lists()
	{
		$json = '{"nhs_num":null,"hos_num":null,"title":null,"family_name":null,"given_name":null,"gender":null,"birth_date":null,"date_of_death":null,"primary_phone":null,"addresses":[{"date_start":null,"date_end":null,"correspond":false,"transport":false,"use":null,"line1":"CpPtQ","line2":"hAUwRF9","city":"WBRGT","state":"BxuEvQb","zip":"HoT9N3kj","country":"United States"}],"care_providers":[],"gp_ref":null,"prac_ref":null,"cb_refs":[],"id":null,"last_modified":null}';

		$map = array(
			'Patient' => array(
				'related_objects' => array(
					'contact' => array('contact_id', 'Contact'),
				),
				'fields' => array(
					'addresses' => array(DeclarativeModelService::TYPE_LIST, 'contact.addresses', 'PatientAddress', 'Address', 'contact_id'),
				),
			),
			'Address' => array(
				'related_objects' => array(
					'contact' => array('contact_id', 'Contact'),
				),
				'reference_objects' => array(
					'country' => array('country_id', 'Country'),
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

		$op = new JSONConverter($map);

		$resource = $op->jsonToResource($json, 'Patient', new Patient(array()));

		$this->assertInstanceOf('services\\Patient',$resource);
		$this->assertCount(1, $resource->addresses);
		$this->assertInstanceOf('services\\PatientAddress',$resource->addresses[0]);
		$this->assertEquals('CpPtQ', $resource->addresses[0]->line1);
		$this->assertEquals('hAUwRF9', $resource->addresses[0]->line2);
		$this->assertEquals('WBRGT', $resource->addresses[0]->city);
		$this->assertEquals('BxuEvQb', $resource->addresses[0]->state);
		$this->assertEquals('HoT9N3kj', $resource->addresses[0]->zip);
		$this->assertEquals('United States', $resource->addresses[0]->country);
	}

	public function testParse_References()
	{
		$json = '{"nhs_num":null,"hos_num":null,"title":null,"family_name":null,"given_name":null,"gender":null,"birth_date":null,"date_of_death":null,"primary_phone":null,"addresses":[],"care_providers":[],"gp_ref":{"service":"Gp","id":17},"prac_ref":{"service":"Practice","id":41},"cb_refs":[],"id":null,"last_modified":null}';

		$map = array(
			'Patient' => array(
				'fields' => array(
					'gp_ref' => array(DeclarativeModelService::TYPE_REF, 'gp_id', 'Gp'),
					'prac_ref' => array(DeclarativeModelService::TYPE_REF, 'practice_id', 'Practice'),
				),
			)
		);

		$op = new JSONConverter($map);

		$resource = $op->jsonToResource($json, 'Patient', new Patient(array()));

		$this->assertInstanceOf('services\\Patient',$resource);

		$this->assertInstanceOf('services\\GpReference',$resource->gp_ref);
		$this->assertEquals(17, $resource->gp_ref->getId());
		$this->assertEquals('Gp', $resource->gp_ref->getServiceName());

		$this->assertInstanceOf('services\\PracticeReference',$resource->prac_ref);
		$this->assertEquals(41, $resource->prac_ref->getId());
		$this->assertEquals('Practice', $resource->prac_ref->getServiceName());
	}

	public function testParse_DateObjects()
	{
		$json = '{"nhs_num":null,"hos_num":null,"title":null,"family_name":null,"given_name":null,"gender":null,"birth_date":null,"date_of_death":null,"primary_phone":null,"addresses":[{"date_start":{"date":"2012-01-01 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"date_end":{"date":"2013-04-05 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"correspond":false,"transport":false,"use":null,"line1":null,"line2":null,"city":null,"state":null,"zip":null,"country":null}],"care_providers":[],"gp_ref":null,"prac_ref":null,"cb_refs":[],"id":null,"last_modified":null}';

		$map = array(
			'Patient' => array(
				'related_objects' => array(
					'contact' => array('contact_id', 'Contact'),
				),
				'fields' => array(
					'addresses' => array(DeclarativeModelService::TYPE_LIST, 'contact.addresses', 'PatientAddress', 'Address'),
				),
			),
			'Address' => array(
				'fields' => array(
					'date_start' => array(DeclarativeModelService::TYPE_SIMPLEOBJECT, 'date_start', 'Date'),
					'date_end' => array(DeclarativeModelService::TYPE_SIMPLEOBJECT, 'date_end', 'Date'),
				),
			),
		);

		$op = new JSONConverter($map);

		$resource = $op->jsonToResource($json, 'Patient', new Patient(array()));

		$this->assertInstanceOf('services\\Patient',$resource);
		$this->assertCount(1, $resource->addresses);
		$this->assertInstanceOf('services\\PatientAddress',$resource->addresses[0]);

		date_default_timezone_set('Europe/London');

		$this->assertInstanceOf('services\\Date',$resource->addresses[0]->date_start);
		$this->assertEquals(strtotime('2012-01-01'),$resource->addresses[0]->date_start->getTimestamp());
		$this->assertInstanceOf('services\\Date',$resource->addresses[0]->date_end);
		$this->assertEquals(strtotime('2013-04-05'),$resource->addresses[0]->date_end->getTimestamp());
	}

	public function testParse_ConditionalBooleans()
	{
		$json = '{"nhs_num":null,"hos_num":null,"title":null,"family_name":null,"given_name":null,"gender":null,"birth_date":null,"date_of_death":null,"primary_phone":null,"addresses":[{"date_start":null,"date_end":null,"correspond":true,"transport":false,"use":null,"line1":null,"line2":null,"city":null,"state":null,"zip":null,"country":null}],"care_providers":[],"gp_ref":null,"prac_ref":null,"cb_refs":[],"id":null,"last_modified":null}';

		$map = array(
			'Patient' => array(
				'related_objects' => array(
					'contact' => array('contact_id', 'Contact'),
				),
				'fields' => array(
					'addresses' => array(DeclarativeModelService::TYPE_LIST, 'contact.addresses', 'PatientAddress', 'Address'),
				),
			),
			'Address' => array(
				'fields' => array(
					'correspond' => array(DeclarativeModelService::TYPE_CONDITION, 'address_type_id', 'equals', \AddressType::CORRESPOND),
					'transport' => array(DeclarativeModelService::TYPE_CONDITION, 'address_type_id', 'equals', \AddressType::TRANSPORT),
				),
			),
		);
		
		$op = new JSONConverter($map);

		$resource = $op->jsonToResource($json, 'Patient', new Patient(array()));

		$this->assertInstanceOf('services\\Patient',$resource);
		$this->assertCount(1, $resource->addresses);
		$this->assertInstanceOf('services\\PatientAddress',$resource->addresses[0]);

		$this->assertTrue($resource->addresses[0]->correspond);
		$this->assertFalse($resource->addresses[0]->transport);
	}

	public function testParse_FullPatient()
	{
		$json = '{"nhs_num":"54321","hos_num":"12345","title":"Mr","family_name":"Aylward","given_name":"Jim","gender_ref":{"service":"Gender","id":1},"birth_date":{"date":"1970-01-01","timezone_type":3,"timezone":"Europe/London"},"date_of_death":null,"primary_phone":"07123 456789","addresses":[{"date_start":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"date_end":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"correspond":false,"transport":false,"use":null,"line1":"flat 1","line2":"bleakley creek","city":"flitchley","state":"london","zip":"ec1v 0dx","country":"United States"}],"care_providers":[],"gp_ref":{"service":"Gp","id":2},"prac_ref":{"service":"Practice","id":5},"cb_refs":[],"id":null,"last_modified":null}';

		$map = PatientService::getModelMap();

		$op = new JSONConverter($map);

		$resource = $op->jsonToResource($json, 'Patient', new Patient(array()));

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
		$json = '{"nhs_num":"54321","hos_num":"12345","title":"Mr","family_name":"Aylward","given_name":"Jim","gender_ref":{"service":"Gender","id":1},"birth_date":{"date":"1970-01-01","timezone_type":3,"timezone":"Europe/London"},"date_of_death":null,"primary_phone":"07123 456789","addresses":[{"date_start":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"date_end":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"correspond":false,"transport":false,"use":null,"line1":"flat 1","line2":"bleakley creek","city":"flitchley","state":"london","zip":"ec1v 0dx","country":"United States"}],"care_providers":[],"gp_ref":{"service":"Gp","id":1},"prac_ref":{"service":"Practice","id":1},"cb_refs":[],"id":null,"last_modified":null}';

		$total_patients = count(\Patient::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());
		$total_countries = count(\Country::model()->findAll());
		$total_genders = count(\Gender::model()->findAll());

		$jc = new JSONConverter(PatientService::getModelMap());
		$patient = $jc->jsonToModel($json, new \Patient, false);

		$this->assertEquals($total_patients, count(\Patient::model()->findAll()));
		$this->assertEquals($total_contacts, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses, count(\Address::model()->findAll()));
		$this->assertEquals($total_countries, count(\Country::model()->findAll()));
		$this->assertEquals($total_genders, count(\Gender::model()->findAll()));
	}

	public function testJsonToModel_NoSave_ModelIsCorrect()
	{
		$json = '{"nhs_num":"54321","hos_num":"12345","title":"Mr","family_name":"Aylward","given_name":"Jim","gender_ref":{"service":"Gender","id":1},"birth_date":{"date":"1970-01-01","timezone_type":3,"timezone":"Europe/London"},"date_of_death":null,"primary_phone":"07123 456789","addresses":[{"date_start":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"date_end":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"correspond":false,"transport":false,"use":null,"line1":"flat 1","line2":"bleakley creek","city":"flitchley","state":"london","zip":"ec1v 0dx","country":"United States"}],"care_providers":[],"gp_ref":{"service":"Gp","id":1},"prac_ref":{"service":"Practice","id":1},"cb_refs":[],"id":null,"last_modified":null}';

		$jc = new JSONConverter(PatientService::getModelMap());
		$patient = $jc->jsonToModel($json, new \Patient, false);

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

	public function testJsonToModel_Save_ModelCountsCorrect()
	{
		$json = '{"nhs_num":"54321","hos_num":"12345","title":"Mr","family_name":"Aylward","given_name":"Jim","gender_ref":{"service":"Gender","id":1},"birth_date":{"date":"1970-01-01","timezone_type":3,"timezone":"Europe/London"},"date_of_death":null,"primary_phone":"07123 456789","addresses":[{"date_start":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"date_end":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"correspond":false,"transport":false,"use":null,"line1":"flat 1","line2":"bleakley creek","city":"flitchley","state":"london","zip":"ec1v 0dx","country":"United States"}],"care_providers":[],"gp_ref":{"service":"Gp","id":1},"prac_ref":{"service":"Practice","id":1},"cb_refs":[],"id":null,"last_modified":null}';

		$total_patients = count(\Patient::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());
		$total_countries = count(\Country::model()->findAll());
		$total_genders = count(\Gender::model()->findAll());

		$jc = new JSONConverter(PatientService::getModelMap());
		$patient = $jc->jsonToModel($json, new \Patient);
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertEquals($total_patients+1, count(\Patient::model()->findAll()));
		$this->assertEquals($total_contacts+1, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses+1, count(\Address::model()->findAll()));
		$this->assertEquals($total_countries, count(\Country::model()->findAll()));
		$this->assertEquals($total_genders, count(\Gender::model()->findAll()));
	}

	public function testJsonToModel_Save_DBIsCorrect()
	{
		$json = '{"nhs_num":"54321","hos_num":"12345","title":"Mr","family_name":"Aylward","given_name":"Jim","gender_ref":{"service":"Gender","id":1},"birth_date":{"date":"1970-01-01","timezone_type":3,"timezone":"Europe/London"},"date_of_death":null,"primary_phone":"07123 456789","addresses":[{"date_start":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"date_end":{"date":"2014-06-06 16:39:29","timezone_type":3,"timezone":"Europe\/London"},"correspond":false,"transport":false,"use":null,"line1":"flat 1","line2":"bleakley creek","city":"flitchley","state":"london","zip":"ec1v 0dx","country":"United States"}],"care_providers":[],"gp_ref":{"service":"Gp","id":1},"prac_ref":{"service":"Practice","id":1},"cb_refs":[],"id":null,"last_modified":null}';

		$jc = new JSONConverter(PatientService::getModelMap());
		$patient = $jc->jsonToModel($json, new \Patient);
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
}
