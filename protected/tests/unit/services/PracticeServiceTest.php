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

class PracticeServiceTest extends \CDbTestCase
{
	public $fixtures = array(
		'practices' => 'Practice',
		'contacts' => 'Contact',
		'addresses' => 'Address',
		'countries' => 'Country',
	);

	public function testModelToResource()
	{
		$practice = $this->practices('practice1');
		$contact = $this->contacts('contact1');
		$address = $this->addresses('address1');

		$contact->addresses = array($address);
		$practice->contact = $contact;

		$ps = new PracticeService;

		$resource = $ps->modelToResource($practice);

		$this->assertInstanceOf('services\Practice',$resource);
		$this->assertEquals(1,$resource->getId());
		$this->assertEquals('AA1',$resource->code);
		$this->assertEquals('0202 20202020',$resource->primary_phone);
	}

	public function getResource()
	{
		$address = new Address;
		$address->line1 = '1 some road';
		$address->line2 = 'some place';
		$address->city = 'somewhere';
		$address->state = 'someton';
		$address->zip = 'som3 0ne';
		$address->country = 'United Kingdom';

		$resource = new Practice;
		$resource->code = '5512A';
		$resource->primary_phone = '29892384934 3424';
		$resource->address = $address;

		return $resource;
	}

	public function testResourceToModel_NoSave_NoNewRecords()
	{
		$resource = $this->getResource();

		$total_practices = count(\Practice::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());
		$total_countries = count(\Country::model()->findAll());

		$ps = new PracticeService;
		$practice = $ps->resourceToModel($resource, false);

		$this->assertEquals($total_practices, count(\Practice::model()->findAll()));
		$this->assertEquals($total_contacts, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses, count(\Address::model()->findAll()));
		$this->assertEquals($total_countries, count(\Country::model()->findAll()));
	}

	public function testResourceToModel_NoSave_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PracticeService;
		$practice = $ps->resourceToModel($resource, false);

		$this->assertInstanceOf('Practice',$practice);
		$this->assertEquals('5512A',$practice->code);
		$this->assertEquals('29892384934 3424',$practice->phone);

		$this->assertInstanceOf('Address', $practice->contact->address);
		$this->assertEquals('1 some road',$practice->contact->address->address1);
		$this->assertEquals('some place',$practice->contact->address->address2);
		$this->assertEquals('somewhere',$practice->contact->address->city);
		$this->assertEquals('someton',$practice->contact->address->county);
		$this->assertEquals('som3 0ne',$practice->contact->address->postcode);
		$this->assertInstanceOf('Country', $practice->contact->address->country);
		$this->assertEquals('United Kingdom', $practice->contact->address->country->name);
	}

	public function testResourceToModel_Save_Create_ModelCountsCorrect()
	{
		$resource = $this->getResource();

		$total_practices = count(\Practice::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());
		$total_countries = count(\Country::model()->findAll());

		$ps = new PracticeService;
		$practice = $ps->resourceToModel($resource);

		$this->assertEquals($total_practices+1, count(\Practice::model()->findAll()));
		$this->assertEquals($total_contacts+1, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses+1, count(\Address::model()->findAll()));
		$this->assertEquals($total_countries, count(\Country::model()->findAll()));
	}

	public function testResourceToModel_Save_Create_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PracticeService;
		$practice = $ps->resourceToModel($resource);

		$this->assertInstanceOf('\Practice',$practice);

		$this->assertEquals('5512A',$practice->code);
		$this->assertEquals('29892384934 3424',$practice->phone);

		$this->assertInstanceOf('Address', $practice->contact->address);
		$this->assertEquals('1 some road',$practice->contact->address->address1);
		$this->assertEquals('some place',$practice->contact->address->address2);
		$this->assertEquals('somewhere',$practice->contact->address->city);
		$this->assertEquals('someton',$practice->contact->address->county);
		$this->assertEquals('som3 0ne',$practice->contact->address->postcode);
		$this->assertInstanceOf('Country', $practice->contact->address->country);
		$this->assertEquals('United Kingdom', $practice->contact->address->country->name);
	}

	public function testResourceToModel_Save_Create_DBIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PracticeService;
		$practice = $ps->resourceToModel($resource);
		$practice = \Practice::model()->findByPk($practice->id);

		$this->assertInstanceOf('\Practice',$practice);

		$this->assertEquals('5512A',$practice->code);
		$this->assertEquals('29892384934 3424',$practice->phone);

		$this->assertInstanceOf('Address', $practice->contact->address);
		$this->assertEquals('1 some road',$practice->contact->address->address1);
		$this->assertEquals('some place',$practice->contact->address->address2);
		$this->assertEquals('somewhere',$practice->contact->address->city);
		$this->assertEquals('someton',$practice->contact->address->county);
		$this->assertEquals('som3 0ne',$practice->contact->address->postcode);
		$this->assertInstanceOf('Country', $practice->contact->address->country);
		$this->assertEquals('United Kingdom', $practice->contact->address->country->name);
	}

	public function getModifiedResource($id)
	{
		$resource = \Yii::app()->service->Practice($id)->fetch();

		$resource->code = 'x0001';
		$resource->primary_phone = 'x0002';

		$resource->address->line1 = 'L1';
		$resource->address->line2 = 'L2';
		$resource->address->city = 'L3';
		$resource->address->state = 'L4';
		$resource->address->zip = 'L5';
		$resource->address->country = 'United Kingdom';

		return $resource;
	}

	public function testResourceToModel_Save_Update_ModelCountsCorrect()
	{
		$resource = $this->getModifiedResource(1);

		$total_practices = count(\Practice::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());
		$total_countries = count(\Country::model()->findAll());

		$ps = new PracticeService;
		$practice = $ps->resourceToModel($resource);

		$this->assertEquals($total_practices, count(\Practice::model()->findAll()));
		$this->assertEquals($total_contacts, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses, count(\Address::model()->findAll()));
		$this->assertEquals($total_countries, count(\Country::model()->findAll()));
	}

	public function testResourceToModel_Save_Update_DBIsCorrect()
	{
		$resource = $this->getModifiedResource(1);

		$ps = new PracticeService;
		$practice = $ps->resourceToModel($resource);
		$practice = \Practice::model()->findByPk($practice->id);

		$this->assertInstanceOf('Practice',$practice);
		$this->assertEquals('x0001',$practice->code);
		$this->assertEquals('x0002',$practice->phone);

		$this->assertInstanceOf('Address', $practice->contact->address);
		$this->assertEquals('L1', $practice->contact->address->address1);
		$this->assertEquals('L2', $practice->contact->address->address2);
		$this->assertEquals('L3', $practice->contact->address->city);
		$this->assertEquals('L4', $practice->contact->address->county);
		$this->assertEquals('L5', $practice->contact->address->postcode);
		$this->assertInstanceOf('\Country', $practice->contact->address->country);
		$this->assertEquals('United Kingdom', $practice->contact->address->country->name);
	}

	public function testJsonToResource()
	{
		$json = '{"code":"x0001","primary_phone":"x0002","address":{"use":null,"line1":"x0003","line2":"x0004","city":"x0005","state":"x0006","zip":"x0007","country":"United Kingdom"},"id":"1","last_modified":-2208988800}';

		$ps = new PracticeService;
		$resource = $ps->jsonToResource($json);

		$this->assertInstanceOf('services\Practice',$resource);
		$this->assertEquals('x0001',$resource->code);
		$this->assertEquals('x0002',$resource->primary_phone);

		$this->assertInstanceOf('services\Address', $resource->address);
		$this->assertEquals('x0003', $resource->address->line1);
		$this->assertEquals('x0004', $resource->address->line2);
		$this->assertEquals('x0005', $resource->address->city);
		$this->assertEquals('x0006', $resource->address->state);
		$this->assertEquals('x0007', $resource->address->zip);
		$this->assertEquals('United Kingdom', $resource->address->country);
	}

	public function jsonToModel_NoSave_NoNewRows()
	{
		$json = '{"code":"x0001","primary_phone":"x0002","address":{"use":null,"line1":"x0003","line2":"x0004","city":"x0005","state":"x0006","zip":"x0007","country":"United Kingdom"},"id":null,"last_modified":-2208988800}';

		$total_practices = count(\Practice::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());
		$total_countries = count(\Country::model()->findAll());

		$ps = new PracticeService;
		$practice = $ps->jsonToModel($json, false);

		$this->assertEquals($total_practices, count(\Practice::model()->findAll()));
		$this->assertEquals($total_contacts, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses, count(\Address::model()->findAll()));
		$this->assertEquals($total_countries, count(\Country::model()->findAll()));
	}

	public function testJsonToModel_NoSave_ModelIsCorrect()
	{
		$json = '{"code":"x0001","primary_phone":"x0002","address":{"use":null,"line1":"x0003","line2":"x0004","city":"x0005","state":"x0006","zip":"x0007","country":"United Kingdom"},"id":null,"last_modified":-2208988800}';

		$ps = new PracticeService;
		$practice = $ps->jsonToModel($json, false);

		$this->assertInstanceOf('Practice',$practice);
		$this->assertEquals('x0001',$practice->code);
		$this->assertEquals('x0002',$practice->phone);

		$this->assertInstanceOf('Address', $practice->contact->address);
		$this->assertEquals('x0003', $practice->contact->address->address1);
		$this->assertEquals('x0004', $practice->contact->address->address2);
		$this->assertEquals('x0005', $practice->contact->address->city);
		$this->assertEquals('x0006', $practice->contact->address->county);
		$this->assertEquals('x0007', $practice->contact->address->postcode);
		$this->assertInstanceOf('\Country', $practice->contact->address->country);
		$this->assertEquals('United Kingdom', $practice->contact->address->country->name);
	}

	public function testJsonToModel_Save_Create_ModelCountsCorrect()
	{
		$json = '{"code":"x0001","primary_phone":"x0002","address":{"use":null,"line1":"x0003","line2":"x0004","city":"x0005","state":"x0006","zip":"x0007","country":"United Kingdom"},"id":null,"last_modified":-2208988800}';

		$total_practices = count(\Practice::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());
		$total_countries = count(\Country::model()->findAll());

		$ps = new PracticeService;
		$practice = $ps->jsonToModel($json);

		$this->assertEquals($total_practices+1, count(\Practice::model()->findAll()));
		$this->assertEquals($total_contacts+1, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses+1, count(\Address::model()->findAll()));
		$this->assertEquals($total_countries, count(\Country::model()->findAll()));
	}

	public function testJsonToModel_Save_Create_DBIsCorrect()
	{
		$json = '{"code":"x0001","primary_phone":"x0002","address":{"use":null,"line1":"x0003","line2":"x0004","city":"x0005","state":"x0006","zip":"x0007","country":"United Kingdom"},"id":null,"last_modified":-2208988800}';

		$ps = new PracticeService;
		$practice = $ps->jsonToModel($json);
		$practice = \Practice::model()->findByPk($practice->id);

		$this->assertInstanceOf('Practice',$practice);
		$this->assertEquals('x0001',$practice->code);
		$this->assertEquals('x0002',$practice->phone);

		$this->assertInstanceOf('Address', $practice->contact->address);
		$this->assertEquals('x0003', $practice->contact->address->address1);
		$this->assertEquals('x0004', $practice->contact->address->address2);
		$this->assertEquals('x0005', $practice->contact->address->city);
		$this->assertEquals('x0006', $practice->contact->address->county);
		$this->assertEquals('x0007', $practice->contact->address->postcode);
		$this->assertInstanceOf('\Country', $practice->contact->address->country);
		$this->assertEquals('United Kingdom', $practice->contact->address->country->name);
	}

	public function testJsonToModel_Save_Update_ModelCountsCorrect()
	{
		$json = '{"code":"x0001","primary_phone":"x0002","address":{"use":null,"line1":"x0003","line2":"x0004","city":"x0005","state":"x0006","zip":"x0007","country":"United Kingdom"},"id":"1","last_modified":-2208988800}';

		$total_practices = count(\Practice::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());
		$total_countries = count(\Country::model()->findAll());

		$ps = new PracticeService;
		$practice = $ps->jsonToModel($json);
		$practice = \Practice::model()->findByPk($practice->id);

		$this->assertEquals($total_practices, count(\Practice::model()->findAll()));
		$this->assertEquals($total_contacts, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses, count(\Address::model()->findAll()));
		$this->assertEquals($total_countries, count(\Country::model()->findAll()));
	}

	public function testJsonToModel_Save_Update_DBIsCorrect()
	{
		$json = '{"code":"x0001","primary_phone":"x0002","address":{"use":null,"line1":"x0003","line2":"x0004","city":"x0005","state":"x0006","zip":"x0007","country":"United Kingdom"},"id":"1","last_modified":-2208988800}';

		$ps = new PracticeService;
		$practice = $ps->jsonToModel($json);
		$practice = \Practice::model()->findByPk($practice->id);

		$this->assertInstanceOf('Practice',$practice);
		$this->assertEquals('x0001',$practice->code);
		$this->assertEquals('x0002',$practice->phone);

		$this->assertInstanceOf('Address', $practice->contact->address);
		$this->assertEquals('x0003', $practice->contact->address->address1);
		$this->assertEquals('x0004', $practice->contact->address->address2);
		$this->assertEquals('x0005', $practice->contact->address->city);
		$this->assertEquals('x0006', $practice->contact->address->county);
		$this->assertEquals('x0007', $practice->contact->address->postcode);
		$this->assertInstanceOf('\Country', $practice->contact->address->country);
		$this->assertEquals('United Kingdom', $practice->contact->address->country->name);
	}
}
