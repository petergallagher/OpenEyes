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

class GpServiceTest extends \CDbTestCase
{
	public $fixtures = array(
		'gps' => 'Gp',
		'contacts' => 'Contact',
		'addresses' => 'Address',
	);

	public function testModelToResource()
	{
		$gp = $this->gps('gp1');
		$contact = $this->contacts('contact7');
		$address = $this->addresses('address4');

		$contact->addresses = array($address);
		$gp->contact = $contact;

		$gs = new GpService;

		$resource = $gs->modelToResource($gp);

		$this->assertEquals('AII2E2F',$resource->gnc);
		$this->assertEquals('AA1134',$resource->obj_prof);
		$this->assertEquals('Dr',$resource->title);
		$this->assertEquals('Zhivago',$resource->family_name);
		$this->assertEquals('Yuri',$resource->given_name);
		$this->assertEquals('999',$resource->primary_phone);

		$this->assertInstanceOf('services\Address',$resource->address);
		$this->assertEquals('Staplegun',$resource->address->line1);
		$this->assertEquals('Staplegun Creek',$resource->address->line2);
		$this->assertEquals('Stapleton',$resource->address->city);
		$this->assertEquals('staple',$resource->address->state);
		$this->assertEquals('st44 pl3',$resource->address->zip);
		$this->assertEquals('United States',$resource->address->country);
		$this->assertEquals(1, $resource->getId());
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

		$resource = new Gp;
		$resource->gnc = '1337';
		$resource->obj_prof = '33344';
		$resource->title = 'Lord';
		$resource->family_name = 'Bub';
		$resource->given_name = 'Jope';
		$resource->primary_phone = '123124';
		$resource->address = $address;

		return $resource;
	}

	public function testResourceToModel_NoSave_NoNewRecords()
	{
		$resource = $this->getResource();

		$total_gps = count(\Gp::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());

		$gs = new GpService;
		$gp = $gs->resourceToModel($resource, false);

		$this->assertEquals($total_gps, count(\Gp::model()->findAll()));
		$this->assertEquals($total_contacts, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses, count(\Address::model()->findAll()));
	}

	public function testResourceToModel_NoSave_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$gs = new GpService;
		$gp = $gs->resourceToModel($resource, false);

		$this->assertInstanceOf('\Gp',$gp);
		$this->assertEquals('1337',$gp->nat_id);
		$this->assertEquals('33344',$gp->obj_prof);
		$this->assertEquals('Lord',$gp->contact->title);
		$this->assertEquals('Bub',$gp->contact->last_name);
		$this->assertEquals('Jope',$gp->contact->first_name);
		$this->assertEquals('123124',$gp->contact->primary_phone);

		$this->assertInstanceOf('\Address',$gp->contact->address);
		$this->assertEquals('1 blah town',$gp->contact->address->address1);
		$this->assertEquals('blahville',$gp->contact->address->address2);
		$this->assertEquals('blahcity',$gp->contact->address->city);
		$this->assertEquals('blahstate',$gp->contact->address->county);
		$this->assertEquals('blahzip',$gp->contact->address->postcode);
		$this->assertInstanceOf('Country',$gp->contact->address->country);
		$this->assertEquals('United States',$gp->contact->address->country->name);
	}

	public function testResourceToModel_Save_ModelCountsCorrect()
	{
		$resource = $this->getResource();

		$total_gps = count(\Gp::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());

		$gs = new GpService;
		$gp = $gs->resourceToModel($resource);

		$this->assertEquals($total_gps+1, count(\Gp::model()->findAll()));
		$this->assertEquals($total_contacts+1, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses+1, count(\Address::model()->findAll()));
	}

	public function testResourceToModel_Save_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$gs = new GpService;
		$gp = $gs->resourceToModel($resource);

		$this->assertInstanceOf('\Gp',$gp);
		$this->assertEquals('1337',$gp->nat_id);
		$this->assertEquals('33344',$gp->obj_prof);
		$this->assertEquals('Lord',$gp->contact->title);
		$this->assertEquals('Bub',$gp->contact->last_name);
		$this->assertEquals('Jope',$gp->contact->first_name);
		$this->assertEquals('123124',$gp->contact->primary_phone);

		$this->assertInstanceOf('\Address',$gp->contact->address);
		$this->assertEquals('1 blah town',$gp->contact->address->address1);
		$this->assertEquals('blahville',$gp->contact->address->address2);
		$this->assertEquals('blahcity',$gp->contact->address->city);
		$this->assertEquals('blahstate',$gp->contact->address->county);
		$this->assertEquals('blahzip',$gp->contact->address->postcode);
		$this->assertInstanceOf('Country',$gp->contact->address->country);
		$this->assertEquals('United States',$gp->contact->address->country->name);
	}

	public function testResourceToModel_Save_DBIsCorrect()
	{
		$resource = $this->getResource();

		$gs = new GpService;
		$gp = $gs->resourceToModel($resource);
		$gp = \Gp::model()->findByPk($gp->id);

		$this->assertInstanceOf('\Gp',$gp);
		$this->assertEquals('1337',$gp->nat_id);
		$this->assertEquals('33344',$gp->obj_prof);
		$this->assertEquals('Lord',$gp->contact->title);
		$this->assertEquals('Bub',$gp->contact->last_name);
		$this->assertEquals('Jope',$gp->contact->first_name);
		$this->assertEquals('123124',$gp->contact->primary_phone);

		$this->assertInstanceOf('\Address',$gp->contact->address);
		$this->assertEquals('1 blah town',$gp->contact->address->address1);
		$this->assertEquals('blahville',$gp->contact->address->address2);
		$this->assertEquals('blahcity',$gp->contact->address->city);
		$this->assertEquals('blahstate',$gp->contact->address->county);
		$this->assertEquals('blahzip',$gp->contact->address->postcode);
		$this->assertInstanceOf('Country',$gp->contact->address->country);
		$this->assertEquals('United States',$gp->contact->address->country->name);
	}

	public function testJsonToResource()
	{
		$json = '{"gnc":"AII2E2F","obj_prof":"AA1134","title":"Dr","family_name":"Zhivago","given_name":"Yuri","primary_phone":"999","address":{"use":null,"line1":"Staplegun","line2":"Staplegun Creek","city":"Stapleton","state":"staple","zip":"st44 pl3","country":"United States"},"id":"1","last_modified":-2208988800}';

		$gs = new GpService;
		$resource = $gs->jsonToResource($json);

		$this->assertEquals('AII2E2F',$resource->gnc);
		$this->assertEquals('AA1134',$resource->obj_prof);
		$this->assertEquals('Dr',$resource->title);
		$this->assertEquals('Zhivago',$resource->family_name);
		$this->assertEquals('Yuri',$resource->given_name);
		$this->assertEquals('999',$resource->primary_phone);

		$this->assertInstanceOf('services\Address',$resource->address);
		$this->assertEquals('Staplegun',$resource->address->line1);
		$this->assertEquals('Staplegun Creek',$resource->address->line2);
		$this->assertEquals('Stapleton',$resource->address->city);
		$this->assertEquals('staple',$resource->address->state);
		$this->assertEquals('st44 pl3',$resource->address->zip);
		$this->assertEquals('United States',$resource->address->country);
		$this->assertEquals(1, $resource->getId());
	}

	public function jsonToModel_NoSave_NoNewRows()
	{
		$json = '{"gnc":"AII2E2F","obj_prof":"AA1134","title":"Dr","family_name":"Zhivago","given_name":"Yuri","primary_phone":"999","address":{"use":null,"line1":"Staplegun","line2":"Staplegun Creek","city":"Stapleton","state":"staple","zip":"st44 pl3","country":"United States"},"id":"1","last_modified":-2208988800}';

		$total_gps = count(\Gp::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());

		$gs = new GpService;
		$gp = $gs->jsonToModel($json, false);

		$this->assertEquals($total_gps, count(\Gp::model()->findAll()));
		$this->assertEquals($total_contacts, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses, count(\Address::model()->findAll()));
	}

	public function testJsonToModel_NoSave_ModelIsCorrect()
	{
		$json = '{"gnc":"AII2E2F","obj_prof":"AA1134","title":"Dr","family_name":"Zhivago","given_name":"Yuri","primary_phone":"999","address":{"use":null,"line1":"Staplegun","line2":"Staplegun Creek","city":"Stapleton","state":"staple","zip":"st44 pl3","country":"United States"},"id":"1","last_modified":-2208988800}';

		$gs = new GpService;
		$gp = $gs->jsonToModel($json, false);

		$this->assertInstanceOf('\Gp',$gp);
		$this->assertEquals('AII2E2F',$gp->nat_id);
		$this->assertEquals('AA1134',$gp->obj_prof);
		$this->assertEquals('Dr',$gp->contact->title);
		$this->assertEquals('Zhivago',$gp->contact->last_name);
		$this->assertEquals('Yuri',$gp->contact->first_name);
		$this->assertEquals('999',$gp->contact->primary_phone);

		$this->assertInstanceOf('\Contact',$gp->contact);
		$this->assertInstanceOf('\Address',$gp->contact->address);
		$this->assertEquals('Staplegun',$gp->contact->address->address1);
		$this->assertEquals('Staplegun Creek',$gp->contact->address->address2);
		$this->assertEquals('Stapleton',$gp->contact->address->city);
		$this->assertEquals('staple',$gp->contact->address->county);
		$this->assertEquals('st44 pl3',$gp->contact->address->postcode);
		$this->assertInstanceOf('\Country',$gp->contact->address->country);
		$this->assertEquals('United States',$gp->contact->address->country->name);
	}

	public function testJsonToModel_Save_ModelCountsCorrect()
	{
		$json = '{"gnc":"AII2E2F","obj_prof":"AA1134","title":"Dr","family_name":"Zhivago","given_name":"Yuri","primary_phone":"999","address":{"use":null,"line1":"Staplegun","line2":"Staplegun Creek","city":"Stapleton","state":"staple","zip":"st44 pl3","country":"United States"},"id":"1","last_modified":-2208988800}';

		$total_gps = count(\Gp::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());

		$gs = new GpService;
		$gp = $gs->jsonToModel($json);
		$gp = \Gp::model()->findByPk($gp->id);

		$this->assertEquals($total_gps+1, count(\Gp::model()->findAll()));
		$this->assertEquals($total_contacts+1, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses+1, count(\Address::model()->findAll()));
	}

	public function testJsonToModel_Save_DBIsCorrect()
	{
		$json = '{"gnc":"AII2E2F","obj_prof":"AA1134","title":"Dr","family_name":"Zhivago","given_name":"Yuri","primary_phone":"999","address":{"use":null,"line1":"Staplegun","line2":"Staplegun Creek","city":"Stapleton","state":"staple","zip":"st44 pl3","country":"United States"},"id":"1","last_modified":-2208988800}';

		$gs = new GpService;
		$gp = $gs->jsonToModel($json);
		$gp = \Gp::model()->findByPk($gp->id);

		$this->assertInstanceOf('\Gp',$gp);
		$this->assertEquals('AII2E2F',$gp->nat_id);
		$this->assertEquals('AA1134',$gp->obj_prof);
		$this->assertEquals('Dr',$gp->contact->title);
		$this->assertEquals('Zhivago',$gp->contact->last_name);
		$this->assertEquals('Yuri',$gp->contact->first_name);
		$this->assertEquals('999',$gp->contact->primary_phone);

		$this->assertInstanceOf('\Contact',$gp->contact);
		$this->assertInstanceOf('\Address',$gp->contact->address);
		$this->assertEquals('Staplegun',$gp->contact->address->address1);
		$this->assertEquals('Staplegun Creek',$gp->contact->address->address2);
		$this->assertEquals('Stapleton',$gp->contact->address->city);
		$this->assertEquals('staple',$gp->contact->address->county);
		$this->assertEquals('st44 pl3',$gp->contact->address->postcode);
		$this->assertInstanceOf('\Country',$gp->contact->address->country);
		$this->assertEquals('United States',$gp->contact->address->country->name);
	}
}
