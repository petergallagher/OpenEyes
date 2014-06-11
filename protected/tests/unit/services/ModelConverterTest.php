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
	);

	public function testParse_DirectKeys()
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
				'nhs_num' => 'nhs_num',
				'hos_num' => 'hos_num',
				'birth_date' => 'dob',
				'date_of_death' => 'date_of_death',
			)
		);

		$op = new ModelConverter($map);

		$resource = $op->modelToResource($patient, new Patient(array()));

		$this->assertInstanceOf('services\\Patient',$resource);
		$this->assertEquals($patient->hos_num, $resource->hos_num);
		$this->assertEquals($patient->nhs_num, $resource->nhs_num);
		$this->assertEquals($patient->dob, $resource->birth_date);
		$this->assertEquals($patient->date_of_death, $resource->date_of_death);
	}

	public function testParse_RelationKeys()
	{
		$patient = new \Patient;
		$contact = new \Contact;

		foreach (array('title','last_name','first_name','primary_phone') as $field) {
			$contact->$field = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, rand(3,10));
		}

		$patient->contact = $contact;

		$map = array(
			'Patient' => array(
				'title' => 'contact.title',
				'family_name' => 'contact.last_name',
				'given_name' => 'contact.first_name',
				'primary_phone' => 'contact.primary_phone',
			)
		);

		$op = new ModelConverter($map);

		$resource = $op->modelToResource($patient, new Patient(array()));

		$this->assertInstanceOf('services\\Patient',$resource);
		$this->assertEquals($contact->title, $resource->title);
		$this->assertEquals($contact->last_name, $resource->family_name);
		$this->assertEquals($contact->first_name, $resource->given_name);
		$this->assertEquals($contact->primary_phone, $resource->primary_phone);
	}

	public function testParse_Lists()
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
				'addresses' => array(DeclarativeModelService::TYPE_LIST, 'contact.addresses', 'PatientAddress', 'Address'),
			),
			'Address' => array(
				'line1' => 'address1',
				'line2' => 'address2',
				'city' => 'city',
				'state' => 'county',
				'zip' => 'postcode',
				'country' => 'country.name',
			),
		);

		$op = new ModelConverter($map);

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

	public function testParse_References()
	{
		$patient = new \Patient;

		$patient->gp_id = 17;
		$patient->practice_id = 41;

		$map = array(
			'Patient' => array(
				'gp_ref' => array(DeclarativeModelService::TYPE_REF, 'gp_id', 'Gp'),
				'prac_ref' => array(DeclarativeModelService::TYPE_REF, 'practice_id', 'Practice'),
			)
		);

		$op = new ModelConverter($map);

		$resource = $op->modelToResource($patient, new Patient(array()));

		$this->assertInstanceOf('services\\Patient',$resource);

		$this->assertInstanceOf('services\\GpReference',$resource->gp_ref);
		$this->assertEquals($patient->gp_id, $resource->gp_ref->getId());
		$this->assertEquals('Gp', $resource->gp_ref->getServiceName());

		$this->assertInstanceOf('services\\PracticeReference',$resource->prac_ref);
		$this->assertEquals($patient->practice_id, $resource->prac_ref->getId());
		$this->assertEquals('Practice', $resource->prac_ref->getServiceName());
	}

	public function testParse_DateObjects()
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
				'addresses' => array(DeclarativeModelService::TYPE_LIST, 'contact.addresses', 'PatientAddress', 'Address'),
			),
			'Address' => array(
				'date_start' => array(DeclarativeModelService::TYPE_OBJECT, 'date_start', 'Date'),
				'date_end' => array(DeclarativeModelService::TYPE_OBJECT, 'date_end', 'Date'),
			),
		);

		$op = new ModelConverter($map);

		$resource = $op->modelToResource($patient, new Patient(array()));

		$this->assertInstanceOf('services\\Patient',$resource);
		$this->assertCount(1, $resource->addresses);
		$this->assertInstanceOf('services\\PatientAddress',$resource->addresses[0]);

		$this->assertInstanceOf('services\\Date',$resource->addresses[0]->date_start);
		$this->assertEquals(strtotime($address->date_start),$resource->addresses[0]->date_start->getTimestamp());
		$this->assertInstanceOf('services\\Date',$resource->addresses[0]->date_end);
		$this->assertEquals(strtotime($address->date_end),$resource->addresses[0]->date_end->getTimestamp());
	}

	public function testParse_ConditionalBooleans()
	{
		$patient = new \Patient;
		$contact = new \Contact;
		$address = new \Address;
	
		$address->address_type_id = 3;

		$contact->addresses = array($address);
		$patient->contact = $contact;

		$map = array(
			'Patient' => array(
				'addresses' => array(DeclarativeModelService::TYPE_LIST, 'contact.addresses', 'PatientAddress', 'Address'),
			),
			'Address' => array(
				'correspond' => array(DeclarativeModelService::TYPE_CONDITION, 'address_type_id', 'equals', \AddressType::CORRESPOND),
				'transport' => array(DeclarativeModelService::TYPE_CONDITION, 'address_type_id', 'equals', \AddressType::TRANSPORT),
			),
		);
		
		$op = new ModelConverter($map);

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

	public function testParse_FullPatient()
	{
		$patient = $this->patients('patient1');
		$contact = $this->contacts('contact1');
		$address = $this->addresses('address1');

		$contact->addresses = array($address);
		$patient->contact = $contact;

		$patient->gp_id = 2;
		$patient->practice_id = 5;

		$map = PatientService::getModelMap();

		$op = new ModelConverter($map);

		$resource = $op->modelToResource($patient, new Patient(array()));

		$this->assertEquals('54321',$resource->nhs_num);
		$this->assertEquals('12345',$resource->hos_num);
		$this->assertEquals('Mr',$resource->title);
		$this->assertEquals('Aylward',$resource->family_name);
		$this->assertEquals('Jim',$resource->given_name);
		$this->assertInstanceOf('services\Gender', $resource->gender);
		$this->assertEquals('Male',$resource->gender->name);
		$this->assertEquals('1970-01-01',$resource->birth_date);
		$this->assertEquals('07123 456789',$resource->primary_phone);

		$this->assertCount(1, $resource->addresses);
		$this->assertInstanceOf('services\PatientAddress', $resource->addresses[0]);
		$this->assertEquals($resource->addresses[0]->line1, 'flat 1');
		$this->assertEquals($resource->addresses[0]->line2, 'bleakley creek');
		$this->assertEquals($resource->addresses[0]->city, 'flitchley');
		$this->assertEquals($resource->addresses[0]->state, 'london');
		$this->assertEquals($resource->addresses[0]->zip, 'ec1v 0dx');
		$this->assertInstanceOf('services\Country', $resource->addresses[0]->country);
		$this->assertEquals($resource->addresses[0]->country->name, 'United States');

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

	public function testresourceToModel()
	{
		$patient = $this->patients('patient1');
		$contact = $this->contacts('contact1');
		$address = $this->addresses('address1');

		$contact->addresses = array($address);
		$patient->contact = $contact;

		$patient->gp_id = 2;
		$patient->practice_id = 5;

		$map = PatientService::getModelMap();

		$op = new ModelConverter($map);

		$resource = $op->modelToResource($patient, new Patient(array()));

		$model = $op->resourceToModel($resource, 'Patient', false);

		$this->assertEquals('54321',$model->nhs_num);
		$this->assertEquals('12345',$model->hos_num);
		$this->assertEquals('Mr',$patient->title);
		$this->assertEquals('Aylward',$patient->last_name);
		$this->assertEquals('Jim',$patient->first_name);
		$this->assertInstanceOf('Gender', $patient->gender);
		$this->assertEquals('Male',$patient->gender->name);
		$this->assertEquals('1970-01-01',$patient->dob);
		$this->assertEquals('07123 456789',$patient->contact->primary_phone);

		$this->assertCount(1, $patient->contact->addresses);
		$this->assertInstanceOf('Address', $patient->contact->addresses[0]);
		$this->assertEquals($patient->contact->addresses[0]->address1, 'flat 1');
		$this->assertEquals($patient->contact->addresses[0]->address2, 'bleakley creek');
		$this->assertEquals($patient->contact->addresses[0]->city, 'flitchley');
		$this->assertEquals($patient->contact->addresses[0]->county, 'london');
		$this->assertEquals($patient->contact->addresses[0]->postcode, 'ec1v 0dx');
		$this->assertInstanceOf('Country', $patient->contact->addresses[0]->country);
		$this->assertEquals($patient->contact->addresses[0]->country->name, 'United States');

		$this->assertEquals($resource->gp_ref->getId(), $patient->gp_id);
		$this->assertEquals($resource->prac_ref->getId(), $patient->practice_id);
	}
}
