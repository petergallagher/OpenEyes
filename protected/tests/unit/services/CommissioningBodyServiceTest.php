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

class CommissioningBodyServiceTest extends \CDbTestCase
{
	public $fixtures = array(
		'commisioning_bodies' => 'CommissioningBody',
		'contacts' => 'Contact',
		'addresses' => 'Address',
		'countries' => 'Country',
	);

	public function testModelToResource()
	{
		$cb = $this->commisioning_bodies(0);
		$contact = $this->contacts('contact8');
		$address = $this->addresses('address5');

		$contact->addresses = array($address);
		$cb->contact = $contact;

		$cs = new CommissioningBodyService;

		$resource = $cs->modelToResource($cb);

		$this->assertEquals(1, $resource->getId());
		$this->assertEquals('Apple',$resource->name);
		$this->assertEquals('AAPL',$resource->code);
		$this->assertEquals('Clinical Commissioning Group',$resource->type);

		$this->assertInstanceOf('services\Address',$resource->address);
		$this->assertEquals('1 Infinite Loop',$resource->address->line1);
		$this->assertEquals('',$resource->address->line2);
		$this->assertEquals('Cupertino',$resource->address->city);
		$this->assertEquals('California',$resource->address->state);
		$this->assertEquals('1AA PL3',$resource->address->zip);
		$this->assertEquals('United States',$resource->address->country);
	}

	public function getResource()
	{
		$address = new Address;
		$address->line1 = '1 blah town';
		$address->line2 = 'blahville';
		$address->city = 'blahcity';
		$address->state = 'blahstate';
		$address->zip = 'blahzip';
		$address->country = 'United States';

		$resource = new CommissioningBody;
		$resource->name = 'Frogtown';
		$resource->code = 'FROGGER';
		$resource->type = 'Clinical Commissioning Group';
		$resource->address = $address;

		return $resource;
	}

	public function testResourceToModel_NoSave_NoNewRecords()
	{
		$resource = $this->getResource();

		$total_cbs = count(\CommissioningBody::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());

		$cs = new CommissioningBodyService;
		$cb = $cs->resourceToModel($resource, new \CommissioningBody, false);

		$this->assertEquals($total_cbs, count(\CommissioningBody::model()->findAll()));
		$this->assertEquals($total_contacts, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses, count(\Address::model()->findAll()));
	}

	public function testResourceToModel_NoSave_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$cs = new CommissioningBodyService;
		$cb = $cs->resourceToModel($resource, new \CommissioningBody, false);

		$this->assertInstanceOf('\CommissioningBody',$cb);
		$this->assertEquals('Frogtown',$cb->name);
		$this->assertEquals('FROGGER',$cb->code);

		$this->assertInstanceOf('\CommissioningBodyType',$cb->type);
		$this->assertEquals('Clinical Commissioning Group',$cb->type->name);

		$this->assertInstanceOf('\Contact',$cb->contact);
		$this->assertInstanceOf('\Address',$cb->contact->address);
		$this->assertEquals('1 blah town',$cb->contact->address->address1);
		$this->assertEquals('blahville',$cb->contact->address->address2);
		$this->assertEquals('blahcity',$cb->contact->address->city);
		$this->assertEquals('blahstate',$cb->contact->address->county);
		$this->assertEquals('blahzip',$cb->contact->address->postcode);
		$this->assertInstanceOf('\Country',$cb->contact->address->country);
		$this->assertEquals('United States',$cb->contact->address->country->name);
	}

	public function testResourceToModel_Save_Create_ModelCountsCorrect()
	{
		$resource = $this->getResource();

		$total_cbs = count(\CommissioningBody::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());

		$cs = new CommissioningBodyService;
		$cb = $cs->resourceToModel($resource, new \CommissioningBody);

		$this->assertEquals($total_cbs+1, count(\CommissioningBody::model()->findAll()));
		$this->assertEquals($total_contacts+1, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses+1, count(\Address::model()->findAll()));
	}

	public function testResourceToModel_Save_Create_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$cs = new CommissioningBodyService;
		$cb = $cs->resourceToModel($resource, new \CommissioningBody);

		$this->assertInstanceOf('\CommissioningBody',$cb);
		$this->assertEquals('Frogtown',$cb->name);
		$this->assertEquals('FROGGER',$cb->code);

		$this->assertInstanceOf('\CommissioningBodyType',$cb->type);
		$this->assertEquals('Clinical Commissioning Group',$cb->type->name);

		$this->assertInstanceOf('\Contact',$cb->contact);
		$this->assertInstanceOf('\Address',$cb->contact->address);
		$this->assertEquals('1 blah town',$cb->contact->address->address1);
		$this->assertEquals('blahville',$cb->contact->address->address2);
		$this->assertEquals('blahcity',$cb->contact->address->city);
		$this->assertEquals('blahstate',$cb->contact->address->county);
		$this->assertEquals('blahzip',$cb->contact->address->postcode);
		$this->assertInstanceOf('\Country',$cb->contact->address->country);
		$this->assertEquals('United States',$cb->contact->address->country->name);
	}

	public function testResourceToModel_Save_Create_DBIsCorrect()
	{
		$resource = $this->getResource();

		$cs = new CommissioningBodyService;
		$cb = $cs->resourceToModel($resource, new \CommissioningBody);
		$cb = \CommissioningBody::model()->findByPk($cb->id);

		$this->assertInstanceOf('\CommissioningBody',$cb);
		$this->assertEquals('Frogtown',$cb->name);
		$this->assertEquals('FROGGER',$cb->code);

		$this->assertInstanceOf('\CommissioningBodyType',$cb->type);
		$this->assertEquals('Clinical Commissioning Group',$cb->type->name);

		$this->assertInstanceOf('\Contact',$cb->contact);
		$this->assertInstanceOf('\Address',$cb->contact->address);
		$this->assertEquals('1 blah town',$cb->contact->address->address1);
		$this->assertEquals('blahville',$cb->contact->address->address2);
		$this->assertEquals('blahcity',$cb->contact->address->city);
		$this->assertEquals('blahstate',$cb->contact->address->county);
		$this->assertEquals('blahzip',$cb->contact->address->postcode);
		$this->assertInstanceOf('\Country',$cb->contact->address->country);
		$this->assertEquals('United States',$cb->contact->address->country->name);
	}

	public function getModifiedResource($id)
	{
		$resource = \Yii::app()->service->CommissioningBody($id)->fetch();

		$resource->name = 'x0001';
		$resource->code = 'x0002';
		$resource->address->line1 = 'x0007';
		$resource->address->line2 = 'x0008';
		$resource->address->city = 'x0009';
		$resource->address->state = 'x0010';
		$resource->address->zip = 'x0011';
		$resource->address->country = 'United Kingdom';

		return $resource;
	}

	public function testResourceToModel_Save_Update_ModelCountsCorrect()
	{
		$resource = $this->getModifiedResource(1);

		$total_cbs = count(\CommissioningBody::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());

		$cs = new CommissioningBodyService;
		$cb = $cs->resourceToModel($resource, \CommissioningBody::model()->findByPk(1));

		$this->assertEquals($total_cbs, count(\CommissioningBody::model()->findAll()));
		$this->assertEquals($total_contacts, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses, count(\Address::model()->findAll()));
	}

	public function testResourceToModel_Save_Update_ModelIsCorrect()
	{
		$resource = $this->getModifiedResource(1);

		$cs = new CommissioningBodyService;
		$cb = $cs->resourceToModel($resource, \CommissioningBody::model()->findByPk(1));

		$this->assertInstanceOf('\CommissioningBody',$cb);
		$this->assertEquals('x0001',$cb->name);
		$this->assertEquals('x0002',$cb->code);

		$this->assertInstanceOf('\Address',$cb->contact->address);
		$this->assertEquals('x0007',$cb->contact->address->address1);
		$this->assertEquals('x0008',$cb->contact->address->address2);
		$this->assertEquals('x0009',$cb->contact->address->city);
		$this->assertEquals('x0010',$cb->contact->address->county);
		$this->assertEquals('x0011',$cb->contact->address->postcode);
		$this->assertInstanceOf('Country',$cb->contact->address->country);
		$this->assertEquals('United Kingdom',$cb->contact->address->country->name);
	}

	public function testResourceToModel_Save_Update_DBIsCorrect()
	{
		$resource = $this->getModifiedResource(1);

		$cs = new CommissioningBodyService;
		$cb = $cs->resourceToModel($resource, \CommissioningBody::model()->findByPk(1));
		$cb = \CommissioningBody::model()->findByPk($cb->id);

		$this->assertInstanceOf('\CommissioningBody',$cb);
		$this->assertEquals('x0001',$cb->name);
		$this->assertEquals('x0002',$cb->code);

		$this->assertInstanceOf('\Address',$cb->contact->address);
		$this->assertEquals('x0007',$cb->contact->address->address1);
		$this->assertEquals('x0008',$cb->contact->address->address2);
		$this->assertEquals('x0009',$cb->contact->address->city);
		$this->assertEquals('x0010',$cb->contact->address->county);
		$this->assertEquals('x0011',$cb->contact->address->postcode);
		$this->assertInstanceOf('Country',$cb->contact->address->country);
		$this->assertEquals('United Kingdom',$cb->contact->address->country->name);
	}

	public function testJsonToResource()
	{
		$json = '{"code":"AAPL","name":"Apple","address":{"use":null,"line1":"1 Infinite Loop","line2":"","city":"Cupertino","state":"California","zip":"1AA PL3","country":"United States"},"id":"1","last_modified":-2208988800,"type":"Clinical Commissioning Group"}';

		$cs = new CommissioningBodyService;
		$resource = $cs->jsonToResource($json);

		$this->assertEquals(1, $resource->getId());
		$this->assertEquals('Apple',$resource->name);
		$this->assertEquals('AAPL',$resource->code);
		$this->assertEquals('Clinical Commissioning Group',$resource->type);

		$this->assertInstanceOf('services\Address',$resource->address);
		$this->assertEquals('1 Infinite Loop',$resource->address->line1);
		$this->assertEquals('',$resource->address->line2);
		$this->assertEquals('Cupertino',$resource->address->city);
		$this->assertEquals('California',$resource->address->state);
		$this->assertEquals('1AA PL3',$resource->address->zip);
		$this->assertEquals('United States',$resource->address->country);
	}

	public function jsonToModel_NoSave_NoNewRows()
	{
		$json = '{"code":"AAPL","name":"Apple","address":{"use":null,"line1":"1 Infinite Loop","line2":"","city":"Cupertino","state":"California","zip":"1AA PL3","country":"United States"},"id":null,"last_modified":-2208988800,"type":"Clinical Commissioning Group"}';

		$total_cbs = count(\CommissioningBody::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());

		$cs = new GpService;
		$cb = $cs->jsonToModel($json, false);

		$this->assertEquals($total_cbs, count(\CommissioningBody::model()->findAll()));
		$this->assertEquals($total_contacts, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses, count(\Address::model()->findAll()));
	}

	public function testJsonToModel_NoSave_ModelIsCorrect()
	{
		$json = '{"code":"AAPL","name":"Apple","address":{"use":null,"line1":"1 Infinite Loop","line2":"","city":"Cupertino","state":"California","zip":"1AA PL3","country":"United States"},"id":null,"last_modified":-2208988800,"type":"Clinical Commissioning Group"}';

		$cs = new CommissioningBodyService;
		$cb = $cs->jsonToModel($json, new \CommissioningBody, false);

		$this->assertInstanceOf('\CommissioningBody',$cb);
		$this->assertEquals('AAPL',$cb->code);
		$this->assertEquals('Apple',$cb->name);

		$this->assertInstanceOf('\Contact',$cb->contact);
		$this->assertInstanceOf('\Address',$cb->contact->address);
		$this->assertEquals('1 Infinite Loop',$cb->contact->address->address1);
		$this->assertEquals('',$cb->contact->address->address2);
		$this->assertEquals('Cupertino',$cb->contact->address->city);
		$this->assertEquals('California',$cb->contact->address->county);
		$this->assertEquals('1AA PL3',$cb->contact->address->postcode);
		$this->assertInstanceOf('\Country',$cb->contact->address->country);
		$this->assertEquals('United States',$cb->contact->address->country->name);
	}

	public function testJsonToModel_Save_Create_ModelCountsCorrect()
	{
		$json = '{"code":"x0001","name":"x0002","address":{"use":null,"line1":"x0003","line2":"x0004","city":"x0004","state":"x0006","zip":"x0007","country":"United Kingdom"},"id":null,"last_modified":-2208988800,"type":"Clinical Commissioning Group"}';

		$total_cbs = count(\CommissioningBody::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());

		$cs = new CommissioningBodyService;
		$cb = $cs->jsonToModel($json, new \CommissioningBody);

		$this->assertEquals($total_cbs+1, count(\CommissioningBody::model()->findAll()));
		$this->assertEquals($total_contacts+1, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses+1, count(\Address::model()->findAll()));
	}

	public function testJsonToModel_Save_Create_ModelIsCorrect()
	{
		$json = '{"code":"x0001","name":"x0002","address":{"use":null,"line1":"x0003","line2":"x0004","city":"x0005","state":"x0006","zip":"x0007","country":"United Kingdom"},"id":null,"last_modified":-2208988800,"type":"Clinical Commissioning Group"}';

		$cs = new CommissioningBodyService;
		$cb = $cs->jsonToModel($json, new \CommissioningBody);

		$this->assertInstanceOf('\CommissioningBody',$cb);
		$this->assertEquals('x0001',$cb->code);
		$this->assertEquals('x0002',$cb->name);

		$this->assertInstanceOf('\Contact',$cb->contact);
		$this->assertInstanceOf('\Address',$cb->contact->address);
		$this->assertEquals('x0003',$cb->contact->address->address1);
		$this->assertEquals('x0004',$cb->contact->address->address2);
		$this->assertEquals('x0005',$cb->contact->address->city);
		$this->assertEquals('x0006',$cb->contact->address->county);
		$this->assertEquals('x0007',$cb->contact->address->postcode);
		$this->assertInstanceOf('\Country',$cb->contact->address->country);
		$this->assertEquals('United Kingdom',$cb->contact->address->country->name);
	}

	public function testJsonToModel_Save_Create_DBIsCorrect()
	{
		$json = '{"code":"x0001","name":"x0002","address":{"use":null,"line1":"x0003","line2":"x0004","city":"x0005","state":"x0006","zip":"x0007","country":"United Kingdom"},"id":null,"last_modified":-2208988800,"type":"Clinical Commissioning Group"}';

		$cs = new CommissioningBodyService;
		$cb = $cs->jsonToModel($json, new \CommissioningBody);
		$cb = \CommissioningBody::model()->findByPk($cb->id);

		$this->assertInstanceOf('\CommissioningBody',$cb);
		$this->assertEquals('x0001',$cb->code);
		$this->assertEquals('x0002',$cb->name);

		$this->assertInstanceOf('\Contact',$cb->contact);
		$this->assertInstanceOf('\Address',$cb->contact->address);
		$this->assertEquals('x0003',$cb->contact->address->address1);
		$this->assertEquals('x0004',$cb->contact->address->address2);
		$this->assertEquals('x0005',$cb->contact->address->city);
		$this->assertEquals('x0006',$cb->contact->address->county);
		$this->assertEquals('x0007',$cb->contact->address->postcode);
		$this->assertInstanceOf('\Country',$cb->contact->address->country);
		$this->assertEquals('United Kingdom',$cb->contact->address->country->name);
	}

	public function testJsonToModel_Save_Update_ModelCountsCorrect()
	{
		$json = '{"code":"x0001","name":"x0002","address":{"use":null,"line1":"x0003","line2":"x0004","city":"x0005","state":"x0006","zip":"x0007","country":"United Kingdom"},"id":"1","last_modified":-2208988800,"type":"Clinical Commissioning Group"}';

		$total_cbs = count(\CommissioningBody::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());

		$cs = new CommissioningBodyService;
		$cb = $cs->jsonToModel($json, \CommissioningBody::model()->findByPk(1));
		$cb = \CommissioningBody::model()->findByPk($cb->id);

		$this->assertEquals($total_cbs, count(\CommissioningBody::model()->findAll()));
		$this->assertEquals($total_contacts, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses, count(\Address::model()->findAll()));
	}

	public function testJsonToModel_Save_Update_ModelIsCorrect()
	{
		$json = '{"code":"x0001","name":"x0002","address":{"use":null,"line1":"x0003","line2":"x0004","city":"x0005","state":"x0006","zip":"x0007","country":"United Kingdom"},"id":"1","last_modified":-2208988800,"type":"Clinical Commissioning Group"}';

		$cs = new CommissioningBodyService;
		$cb = $cs->jsonToModel($json, \CommissioningBody::model()->findByPk(1));

		$this->assertInstanceOf('\CommissioningBody',$cb);
		$this->assertEquals('x0001',$cb->code);
		$this->assertEquals('x0002',$cb->name);

		$this->assertInstanceOf('\Contact',$cb->contact);
		$this->assertInstanceOf('\Address',$cb->contact->address);
		$this->assertEquals('x0003',$cb->contact->address->address1);
		$this->assertEquals('x0004',$cb->contact->address->address2);
		$this->assertEquals('x0005',$cb->contact->address->city);
		$this->assertEquals('x0006',$cb->contact->address->county);
		$this->assertEquals('x0007',$cb->contact->address->postcode);
		$this->assertInstanceOf('\Country',$cb->contact->address->country);
		$this->assertEquals('United Kingdom',$cb->contact->address->country->name);
	}

	public function testJsonToModel_Save_Update_DBIsCorrect()
	{
		$json = '{"code":"x0001","name":"x0002","address":{"use":null,"line1":"x0003","line2":"x0004","city":"x0005","state":"x0006","zip":"x0007","country":"United Kingdom"},"id":"1","last_modified":-2208988800,"type":"Clinical Commissioning Group"}';

		$cs = new CommissioningBodyService;
		$cb = $cs->jsonToModel($json, \CommissioningBody::model()->findByPk(1));
		$cb = \CommissioningBody::model()->findByPk($cb->id);

		$this->assertInstanceOf('\CommissioningBody',$cb);
		$this->assertEquals('x0001',$cb->code);
		$this->assertEquals('x0002',$cb->name);

		$this->assertInstanceOf('\Contact',$cb->contact);
		$this->assertInstanceOf('\Address',$cb->contact->address);
		$this->assertEquals('x0003',$cb->contact->address->address1);
		$this->assertEquals('x0004',$cb->contact->address->address2);
		$this->assertEquals('x0005',$cb->contact->address->city);
		$this->assertEquals('x0006',$cb->contact->address->county);
		$this->assertEquals('x0007',$cb->contact->address->postcode);
		$this->assertInstanceOf('\Country',$cb->contact->address->country);
		$this->assertEquals('United Kingdom',$cb->contact->address->country->name);
	}
}
