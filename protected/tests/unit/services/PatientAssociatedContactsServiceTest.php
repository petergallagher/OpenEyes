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
		$this->assertEquals(1,$resource->contacts[0]->getId());
		$this->assertEquals($patient->contactAssignments[0]->contact_id,$resource->contacts[0]->contact_id);
		$this->assertFalse($resource->contacts[0]->location_id);
		$this->assertEquals('Dr',$resource->contacts[0]->title);
		$this->assertEquals('Zhivago',$resource->contacts[0]->family_name);
		$this->assertEquals('Yuri',$resource->contacts[0]->given_name);
		$this->assertEquals('999',$resource->contacts[0]->primary_phone);
		$this->assertNull($resource->contacts[0]->site_ref);
		$this->assertNull($resource->contacts[0]->institution_ref);

		$this->assertInstanceOf('services\PatientAssociatedContact',$resource->contacts[1]);
		$this->assertEquals(2,$resource->contacts[1]->getId());
		$this->assertEquals($patient->contactAssignments[1]->contact_id,$resource->contacts[1]->contact_id);
		$this->assertFalse($resource->contacts[1]->location_id);
		$this->assertEquals('Mr',$resource->contacts[1]->title);
		$this->assertEquals('Inc',$resource->contacts[1]->family_name);
		$this->assertEquals('Apple',$resource->contacts[1]->given_name);
		$this->assertEquals('01010101',$resource->contacts[1]->primary_phone);
		$this->assertNull($resource->contacts[1]->site_ref);
		$this->assertNull($resource->contacts[1]->institution_ref);

		$this->assertInstanceOf('services\PatientAssociatedContact',$resource->contacts[2]);
		$this->assertEquals(3,$resource->contacts[2]->getId());
		$this->assertEquals($patient->contactAssignments[2]->contact_id,$resource->contacts[2]->contact_id);
		$this->assertFalse($resource->contacts[2]->location_id);
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
		$this->assertEquals($patient->contactAssignments[0]->id,$resource->contacts[0]->getId());
		$this->assertEquals($patient->contactAssignments[0]->location->contact_id,$resource->contacts[0]->contact_id);
		$this->assertEquals($patient->contactAssignments[0]->location->id,$resource->contacts[0]->location_id);
		$this->assertEquals('Dr',$resource->contacts[0]->title);
		$this->assertEquals('Zhivago',$resource->contacts[0]->family_name);
		$this->assertEquals('Yuri',$resource->contacts[0]->given_name);
		$this->assertEquals('999',$resource->contacts[0]->primary_phone);
		$this->assertInstanceOf('services\SiteReference',$resource->contacts[0]->site_ref);
		$this->assertEquals(1,$resource->contacts[0]->site_ref->getId());
		$this->assertNull($resource->contacts[0]->institution_ref);

		$this->assertInstanceOf('services\PatientAssociatedContact',$resource->contacts[1]);
		$this->assertEquals($patient->contactAssignments[1]->id,$resource->contacts[1]->getId());
		$this->assertEquals($patient->contactAssignments[1]->location->contact_id,$resource->contacts[1]->contact_id);
		$this->assertEquals($patient->contactAssignments[1]->location->id,$resource->contacts[1]->location_id);
		$this->assertEquals('Mr',$resource->contacts[1]->title);
		$this->assertEquals('Inc',$resource->contacts[1]->family_name);
		$this->assertEquals('Apple',$resource->contacts[1]->given_name);
		$this->assertEquals('01010101',$resource->contacts[1]->primary_phone);
		$this->assertInstanceOf('services\SiteReference',$resource->contacts[1]->site_ref);
		$this->assertEquals(2,$resource->contacts[1]->site_ref->getId());
		$this->assertNull($resource->contacts[1]->institution_ref);

		$this->assertInstanceOf('services\PatientAssociatedContact',$resource->contacts[2]);
		$this->assertEquals($patient->contactAssignments[2]->id,$resource->contacts[2]->getId());
		$this->assertEquals($patient->contactAssignments[2]->location->contact_id,$resource->contacts[2]->contact_id);
		$this->assertEquals($patient->contactAssignments[2]->location->id,$resource->contacts[2]->location_id);
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

	public function testResourceToModel_Save_Create_DBIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientAssociatedContactsService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'));
		$patient = \Patient::model()->findByPk($patient->id);

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

	public function getModifiedResource($id)
	{
		$resource = \Yii::app()->service->PatientAssociatedContacts($id)->fetch();

		$resource->contacts[0]->family_name = 'Bobson';
		$resource->contacts[0]->site_ref = \Yii::app()->service->Site(1);
		$resource->contacts[0]->contact_id = null;

		$resource->contacts[1]->title = 'Dr';
		$resource->contacts[1]->site_ref = null;
		$resource->contacts[1]->location_id = null;

		$resource->contacts[2]->primary_phone = '1212121212';
		$resource->contacts[2]->site_ref = \Yii::app()->service->Site(2);
		$resource->contacts[2]->institution_ref = null;

		return $resource;
	}

	public function testResourceToModel_Save_Update_Modified_ModelCountsCorrect()
	{
		$resource = $this->getModifiedResource(3);

		$total_patients = count(\Patient::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_pcas = count(\PatientContactAssignment::model()->findAll());
		$total_sites = count(\Site::model()->findAll());
		$total_institutions = count(\Institution::model()->findAll());

		$ps = new PatientAssociatedContactsService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient3'));

		$this->assertEquals($total_patients, count(\Patient::model()->findAll()));
		$this->assertEquals($total_contacts+1, count(\Contact::model()->findAll()));
		$this->assertEquals($total_pcas, count(\PatientContactAssignment::model()->findAll()));
		$this->assertEquals($total_sites, count(\Site::model()->findAll()));
		$this->assertEquals($total_institutions, count(\Institution::model()->findAll()));
	}

	public function testResourceToModel_Save_Update_NotModified_ModelCountsCorrect()
	{
		$resource = \Yii::app()->service->PatientAssociatedContacts(3)->fetch();

		$total_patients = count(\Patient::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_pcas = count(\PatientContactAssignment::model()->findAll());
		$total_sites = count(\Site::model()->findAll());
		$total_institutions = count(\Institution::model()->findAll());

		$ps = new PatientAssociatedContactsService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient3'));

		$this->assertEquals($total_patients, count(\Patient::model()->findAll()));
		$this->assertEquals($total_contacts, count(\Contact::model()->findAll()));
		$this->assertEquals($total_pcas, count(\PatientContactAssignment::model()->findAll()));
		$this->assertEquals($total_sites, count(\Site::model()->findAll()));
		$this->assertEquals($total_institutions, count(\Institution::model()->findAll()));
	}

	public function testResourceToModel_Save_Update_ModelIsCorrect()
	{
		$resource = $this->getModifiedResource(3);

		$ps = new PatientAssociatedContactsService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient3'));

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(3,$patient->contactAssignments);

		$this->assertInstanceOf('PatientContactAssignment',$patient->contactAssignments[0]);
		$this->assertEquals($resource->contacts[0]->getId(),$patient->contactAssignments[0]->id);
		$this->assertNull($patient->contactAssignments[0]->contact);
		$this->assertInstanceOf('ContactLocation',$patient->contactAssignments[0]->location);
		$this->assertInstanceOf('Contact',$patient->contactAssignments[0]->location->contact);
		$this->assertEquals('Dr',$patient->contactAssignments[0]->location->contact->title);
		$this->assertEquals('Yuri',$patient->contactAssignments[0]->location->contact->first_name);
		$this->assertEquals('Bobson',$patient->contactAssignments[0]->location->contact->last_name);
		$this->assertEquals(1,$patient->contactAssignments[0]->location->site_id);
		$this->assertNull($patient->contactAssignments[0]->location->institution_id);

		$this->assertInstanceOf('PatientContactAssignment',$patient->contactAssignments[1]);
		$this->assertEquals($resource->contacts[1]->getId(),$patient->contactAssignments[1]->id);
		$this->assertNull($patient->contactAssignments[1]->location);
		$this->assertInstanceOf('Contact',$patient->contactAssignments[1]->contact);
		$this->assertEquals('Dr',$patient->contactAssignments[1]->contact->title);
		$this->assertEquals('Apple',$patient->contactAssignments[1]->contact->first_name);
		$this->assertEquals('Inc',$patient->contactAssignments[1]->contact->last_name);

		$this->assertInstanceOf('PatientContactAssignment',$patient->contactAssignments[2]);
		$this->assertEquals($resource->contacts[2]->getId(),$patient->contactAssignments[2]->id);
		$this->assertNull($patient->contactAssignments[2]->contact);
		$this->assertInstanceOf('ContactLocation',$patient->contactAssignments[0]->location);
		$this->assertInstanceOf('Contact',$patient->contactAssignments[2]->location->contact);
		$this->assertEquals('Ti',$patient->contactAssignments[2]->location->contact->title);
		$this->assertEquals('Prac',$patient->contactAssignments[2]->location->contact->first_name);
		$this->assertEquals('Tiss',$patient->contactAssignments[2]->location->contact->last_name);
		$this->assertNull($patient->contactAssignments[2]->location->institution_id);
		$this->assertEquals(2,$patient->contactAssignments[2]->location->site_id);
	}

	public function testResourceToModel_Save_Update_DBIsCorrect()
	{
		$resource = $this->getModifiedResource(3);

		$ps = new PatientAssociatedContactsService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient3'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(3,$patient->contactAssignments);

		$this->assertNull($patient->contactAssignments[0]->contact);
		$this->assertInstanceOf('ContactLocation',$patient->contactAssignments[0]->location);
		$this->assertInstanceOf('Contact',$patient->contactAssignments[0]->location->contact);
		$this->assertEquals('Dr',$patient->contactAssignments[0]->location->contact->title);
		$this->assertEquals('Yuri',$patient->contactAssignments[0]->location->contact->first_name);
		$this->assertEquals('Bobson',$patient->contactAssignments[0]->location->contact->last_name);
		$this->assertEquals(1,$patient->contactAssignments[0]->location->site_id);
		$this->assertNull($patient->contactAssignments[0]->location->institution_id);

		$this->assertNull($patient->contactAssignments[1]->location);
		$this->assertInstanceOf('Contact',$patient->contactAssignments[1]->contact);
		$this->assertEquals('Dr',$patient->contactAssignments[1]->contact->title);
		$this->assertEquals('Apple',$patient->contactAssignments[1]->contact->first_name);
		$this->assertEquals('Inc',$patient->contactAssignments[1]->contact->last_name);

		$this->assertNull($patient->contactAssignments[2]->contact);
		$this->assertInstanceOf('ContactLocation',$patient->contactAssignments[0]->location);
		$this->assertInstanceOf('Contact',$patient->contactAssignments[2]->location->contact);
		$this->assertEquals('Ti',$patient->contactAssignments[2]->location->contact->title);
		$this->assertEquals('Prac',$patient->contactAssignments[2]->location->contact->first_name);
		$this->assertEquals('Tiss',$patient->contactAssignments[2]->location->contact->last_name);
		$this->assertNull($patient->contactAssignments[2]->location->institution_id);
		$this->assertEquals(2,$patient->contactAssignments[2]->location->site_id);
	}

	public function testJsonToResource()
	{
		$json = '{"contacts":[{"title":"Dr","family_name":"Zhivago","given_name":"Yuri","primary_phone":"999","institution_ref":null,"site_ref":null,"contact_id":"7","location_id":false,"id":"1","last_modified":null},{"title":"Mr","family_name":"Inc","given_name":"Apple","primary_phone":"01010101","institution_ref":null,"site_ref":{"service":"Site","id":"2"},"contact_id":"8","location_id":false,"id":"2","last_modified":null},{"title":"Ti","family_name":"Tiss","given_name":"Prac","primary_phone":"0303032332","institution_ref":{"service":"Institution","id":"2"},"site_ref":null,"contact_id":"9","location_id":false,"id":"3","last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800}}';

		$ps = new PatientAssociatedContactsService;
		$resource = $ps->jsonToResource($json);

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
		$this->assertNull($resource->contacts[1]->institution_ref);
		$this->assertInstanceOf('services\SiteReference',$resource->contacts[1]->site_ref);
		$this->assertEquals(2,$resource->contacts[1]->site_ref->getId());

		$this->assertInstanceOf('services\PatientAssociatedContact',$resource->contacts[2]);
		$this->assertEquals('Ti',$resource->contacts[2]->title);
		$this->assertEquals('Tiss',$resource->contacts[2]->family_name);
		$this->assertEquals('Prac',$resource->contacts[2]->given_name);
		$this->assertEquals('0303032332',$resource->contacts[2]->primary_phone);
		$this->assertNull($resource->contacts[2]->site_ref);
		$this->assertInstanceOf('services\InstitutionReference',$resource->contacts[2]->institution_ref);
		$this->assertEquals(2,$resource->contacts[2]->institution_ref->getId());
	}

	public function testJsonToModel_NoSave_NoNewRows()
	{
		$json = '{"contacts":[{"title":"Dr","family_name":"Zhivago","given_name":"Yuri","primary_phone":"999","institution_ref":null,"site_ref":null,"contact_id":"7","location_id":false,"id":"1","last_modified":null},{"title":"Mr","family_name":"Inc","given_name":"Apple","primary_phone":"01010101","institution_ref":null,"site_ref":{"service":"Site","id":"2"},"contact_id":"8","location_id":false,"id":"2","last_modified":null},{"title":"Ti","family_name":"Tiss","given_name":"Prac","primary_phone":"0303032332","institution_ref":{"service":"Institution","id":"2"},"site_ref":null,"contact_id":"9","location_id":false,"id":"3","last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800}}';

		$total_patients = count(\Patient::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_pcas = count(\PatientContactAssignment::model()->findAll());
		$total_sites = count(\Site::model()->findAll());
		$total_institutions = count(\Institution::model()->findAll());

		$ps = new PatientAssociatedContactsService;
		$patient = $ps->jsonToModel($json, new \Patient, false);

		$this->assertEquals($total_patients, count(\Patient::model()->findAll()));
		$this->assertEquals($total_contacts, count(\Contact::model()->findAll()));
		$this->assertEquals($total_pcas, count(\PatientContactAssignment::model()->findAll()));
		$this->assertEquals($total_sites, count(\Site::model()->findAll()));
		$this->assertEquals($total_institutions, count(\Institution::model()->findAll()));
	}

	public function testJsonToModel_NoSave_ModelIsCorrect()
	{
		$json = \Yii::app()->service->PatientAssociatedContacts(3)->fetch()->serialise();

		$ps = new PatientAssociatedContactsService;
		$patient = $ps->jsonToModel($json, new \Patient, false);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(3,$patient->contactAssignments);

		$this->assertInstanceOf('PatientContactAssignment',$patient->contactAssignments[0]);
		$this->assertInstanceOf('Contact',$patient->contactAssignments[0]->contact);
		$this->assertEquals('Dr',$patient->contactAssignments[0]->contact->title);
		$this->assertEquals('Zhivago',$patient->contactAssignments[0]->contact->last_name);
		$this->assertEquals('Yuri',$patient->contactAssignments[0]->contact->first_name);
		$this->assertEquals('999',$patient->contactAssignments[0]->contact->primary_phone);
		$this->assertNull($patient->contactAssignments[0]->location);

		$this->assertInstanceOf('PatientContactAssignment',$patient->contactAssignments[1]);
		$this->assertInstanceOf('ContactLocation',$patient->contactAssignments[1]->location);
		$this->assertInstanceOf('Contact',$patient->contactAssignments[1]->location->contact);
		$this->assertEquals('Mr',$patient->contactAssignments[1]->location->contact->title);
		$this->assertEquals('Inc',$patient->contactAssignments[1]->location->contact->last_name);
		$this->assertEquals('Apple',$patient->contactAssignments[1]->location->contact->first_name);
		$this->assertEquals('01010101',$patient->contactAssignments[1]->location->contact->primary_phone);
		$this->assertEquals(2,$patient->contactAssignments[1]->location->site_id);
		$this->assertNull($patient->contactAssignments[1]->location->institution_id);
		$this->assertNull($patient->contactAssignments[1]->contact);

		$this->assertInstanceOf('PatientContactAssignment',$patient->contactAssignments[2]);
		$this->assertInstanceOf('ContactLocation',$patient->contactAssignments[2]->location);
		$this->assertInstanceOf('Contact',$patient->contactAssignments[2]->location->contact);
		$this->assertEquals('Ti',$patient->contactAssignments[2]->location->contact->title);
		$this->assertEquals('Tiss',$patient->contactAssignments[2]->location->contact->last_name);
		$this->assertEquals('Prac',$patient->contactAssignments[2]->location->contact->first_name);
		$this->assertEquals('0303032332',$patient->contactAssignments[2]->location->contact->primary_phone);
		$this->assertNull($patient->contactAssignments[2]->location->site_id);
		$this->assertEquals(2,$patient->contactAssignments[2]->location->institution_id);
		$this->assertNull($patient->contactAssignments[2]->contact);
	}

	public function stripNulls($array)
	{
		$_array = array();

		foreach ($array as $key => $value) {
			$_array[preg_replace('/^\0/','',$key)] = $value;
		}

		return $_array;
	}

	public function testJsonToModel_Save_Create_ModelCountsCorrect()
	{
		$resource = \Yii::app()->service->PatientAssociatedContacts(3)->fetch();

		$resource->contacts[0] = new PatientAssociatedContact(array_merge($this->stripNulls((array)$resource->contacts[0]),array('id'=>null)));
		$resource->contacts[1] = new PatientAssociatedContact(array_merge($this->stripNulls((array)$resource->contacts[1]),array('id'=>null)));
		$resource->contacts[2] = new PatientAssociatedContact(array_merge($this->stripNulls((array)$resource->contacts[2]),array('id'=>null)));

		$resource->contacts[0]->contact_id = null;
		$resource->contacts[1]->contact_id = null;
		$resource->contacts[2]->contact_id = null;

		$resource->contacts[0]->location_id = null;
		$resource->contacts[1]->location_id = null;
		$resource->contacts[2]->location_id = null;

		$json = $resource->serialise();

		$total_patients = count(\Patient::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_pcas = count(\PatientContactAssignment::model()->findAll());
		$total_sites = count(\Site::model()->findAll());
		$total_institutions = count(\Institution::model()->findAll());

		$ps = new PatientAssociatedContactsService;
		$patient = $ps->jsonToModel($json, $this->patients('patient4'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertEquals($total_patients, count(\Patient::model()->findAll()));
		$this->assertEquals($total_contacts+3, count(\Contact::model()->findAll()));
		$this->assertEquals($total_pcas+3, count(\PatientContactAssignment::model()->findAll()));
		$this->assertEquals($total_sites, count(\Site::model()->findAll()));
		$this->assertEquals($total_institutions, count(\Institution::model()->findAll()));
	}

	public function testJsonToModel_Save_Create_DBIsCorrect()
	{
		$resource = \Yii::app()->service->PatientAssociatedContacts(3)->fetch();

		$resource->contacts[0] = new PatientAssociatedContact(array_merge($this->stripNulls((array)$resource->contacts[0]),array('id'=>null)));
		$resource->contacts[1] = new PatientAssociatedContact(array_merge($this->stripNulls((array)$resource->contacts[1]),array('id'=>null)));
		$resource->contacts[2] = new PatientAssociatedContact(array_merge($this->stripNulls((array)$resource->contacts[2]),array('id'=>null)));

		$resource->contacts[0]->contact_id = null;
		$resource->contacts[1]->contact_id = null;
		$resource->contacts[2]->contact_id = null;

		$resource->contacts[0]->location_id = null;
		$resource->contacts[1]->location_id = null;
		$resource->contacts[2]->location_id = null;

		$resource->contacts[0]->given_name = 'Yuri2';
		$resource->contacts[0]->family_name = 'Zhivago2';

		$json = $resource->serialise();

		$ps = new PatientAssociatedContactsService;
		$patient = $ps->jsonToModel($json, $this->patients('patient4'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(3,$patient->contactAssignments);

		$this->assertInstanceOf('PatientContactAssignment',$patient->contactAssignments[0]);
		$this->assertInstanceOf('Contact',$patient->contactAssignments[0]->contact);
		$this->assertEquals('Dr',$patient->contactAssignments[0]->contact->title);
		$this->assertEquals('Zhivago2',$patient->contactAssignments[0]->contact->last_name);
		$this->assertEquals('Yuri2',$patient->contactAssignments[0]->contact->first_name);
		$this->assertEquals('999',$patient->contactAssignments[0]->contact->primary_phone);
		$this->assertNull($patient->contactAssignments[0]->location);

		$this->assertInstanceOf('PatientContactAssignment',$patient->contactAssignments[1]);
		$this->assertInstanceOf('ContactLocation',$patient->contactAssignments[1]->location);
		$this->assertInstanceOf('Contact',$patient->contactAssignments[1]->location->contact);
		$this->assertEquals('Mr',$patient->contactAssignments[1]->location->contact->title);
		$this->assertEquals('Inc',$patient->contactAssignments[1]->location->contact->last_name);
		$this->assertEquals('Apple',$patient->contactAssignments[1]->location->contact->first_name);
		$this->assertEquals('01010101',$patient->contactAssignments[1]->location->contact->primary_phone);
		$this->assertEquals(2,$patient->contactAssignments[1]->location->site_id);
		$this->assertNull($patient->contactAssignments[1]->location->institution_id);
		$this->assertNull($patient->contactAssignments[1]->contact);

		$this->assertInstanceOf('PatientContactAssignment',$patient->contactAssignments[2]);
		$this->assertInstanceOf('ContactLocation',$patient->contactAssignments[2]->location);
		$this->assertInstanceOf('Contact',$patient->contactAssignments[2]->location->contact);
		$this->assertEquals('Ti',$patient->contactAssignments[2]->location->contact->title);
		$this->assertEquals('Tiss',$patient->contactAssignments[2]->location->contact->last_name);
		$this->assertEquals('Prac',$patient->contactAssignments[2]->location->contact->first_name);
		$this->assertEquals('0303032332',$patient->contactAssignments[2]->location->contact->primary_phone);
		$this->assertNull($patient->contactAssignments[2]->location->site_id);
		$this->assertEquals(2,$patient->contactAssignments[2]->location->institution_id);
		$this->assertNull($patient->contactAssignments[2]->contact);
	}

	public function testJsonToModel_Save_Update_ModelCountsCorrect()
	{
		$resource = $this->getModifiedResource(1);

		$json = $resource->serialise();

		$total_patients = count(\Patient::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_pcas = count(\PatientContactAssignment::model()->findAll());
		$total_sites = count(\Site::model()->findAll());
		$total_institutions = count(\Institution::model()->findAll());

		$ps = new PatientAssociatedContactsService;
		$patient = $ps->jsonToModel($json, $this->patients('patient1'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertEquals($total_patients, count(\Patient::model()->findAll()));
		$this->assertEquals($total_contacts+1, count(\Contact::model()->findAll()));
		$this->assertEquals($total_pcas, count(\PatientContactAssignment::model()->findAll()));
		$this->assertEquals($total_sites, count(\Site::model()->findAll()));
		$this->assertEquals($total_institutions, count(\Institution::model()->findAll()));
	}

	public function testJsonToModel_Save_Update_DBIsCorrect()
	{
		$resource = $this->getModifiedResource(1);

		$json = $resource->serialise();

		$ps = new PatientAssociatedContactsService;
		$patient = $ps->jsonToModel($json, $this->patients('patient1'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(3,$patient->contactAssignments);

		$this->assertInstanceOf('PatientContactAssignment',$patient->contactAssignments[0]);
		$this->assertEquals($resource->contacts[0]->getId(),$patient->contactAssignments[0]->id);
		$this->assertNull($patient->contactAssignments[0]->contact);
		$this->assertInstanceOf('ContactLocation',$patient->contactAssignments[0]->location);
		$this->assertInstanceOf('Contact',$patient->contactAssignments[0]->location->contact);
		$this->assertEquals('Dr',$patient->contactAssignments[0]->location->contact->title);
		$this->assertEquals('Yuri',$patient->contactAssignments[0]->location->contact->first_name);
		$this->assertEquals('Bobson',$patient->contactAssignments[0]->location->contact->last_name);
		$this->assertEquals(1,$patient->contactAssignments[0]->location->site_id);
		$this->assertNull($patient->contactAssignments[0]->location->institution_id);

		$this->assertInstanceOf('PatientContactAssignment',$patient->contactAssignments[1]);
		$this->assertEquals($resource->contacts[1]->getId(),$patient->contactAssignments[1]->id);
		$this->assertNull($patient->contactAssignments[1]->location);
		$this->assertInstanceOf('Contact',$patient->contactAssignments[1]->contact);
		$this->assertEquals('Dr',$patient->contactAssignments[1]->contact->title);
		$this->assertEquals('Apple',$patient->contactAssignments[1]->contact->first_name);
		$this->assertEquals('Inc',$patient->contactAssignments[1]->contact->last_name);

		$this->assertInstanceOf('PatientContactAssignment',$patient->contactAssignments[2]);
		$this->assertEquals($resource->contacts[2]->getId(),$patient->contactAssignments[2]->id);
		$this->assertNull($patient->contactAssignments[2]->contact);
		$this->assertInstanceOf('ContactLocation',$patient->contactAssignments[0]->location);
		$this->assertInstanceOf('Contact',$patient->contactAssignments[2]->location->contact);
		$this->assertEquals('Ti',$patient->contactAssignments[2]->location->contact->title);
		$this->assertEquals('Prac',$patient->contactAssignments[2]->location->contact->first_name);
		$this->assertEquals('Tiss',$patient->contactAssignments[2]->location->contact->last_name);
		$this->assertNull($patient->contactAssignments[2]->location->institution_id);
		$this->assertEquals(2,$patient->contactAssignments[2]->location->site_id);
	}
}
