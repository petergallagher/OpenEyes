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

class PatientAllergiesServiceTest extends \CDbTestCase
{
	public $fixtures = array(
		'patients' => 'Patient',
		'allergies' => 'Allergy',
		'alas' => 'PatientAllergyAssignment',
	);

	public function testModelToResource_Allergies()
	{
		$patient = $this->patients('patient1');

		$ps = new PatientAllergiesService;

		$resource = $ps->modelToResource($patient);

		$this->assertInstanceOf('services\PatientAllergies',$resource);
		$this->assertCount(2,$resource->allergies);

		$this->assertInstanceOf('services\PatientAllergy',$resource->allergies[0]);
		$this->assertEquals('allergy 1',$resource->allergies[0]->name);

		$this->assertInstanceOf('services\PatientAllergy',$resource->allergies[1]);
		$this->assertEquals('allergy 2',$resource->allergies[1]->name);

		$this->assertNull($resource->no_allergies_date);
	}

	public function testModelToResource_NoAllergiesDate()
	{
		$patient = $this->patients('patient2');

		$ps = new PatientAllergiesService;

		$resource = $ps->modelToResource($patient);

		$this->assertInstanceOf('services\PatientAllergies',$resource);
		$this->assertEmpty($resource->allergies);

		$this->assertInstanceOf('services\Date',$resource->no_allergies_date);
	}

	public function getResource()
	{
		$resource = new PatientAllergies(3);

		$allergy1 = new PatientAllergy;
		$allergy1->name = 'allergy 2';

		$allergy2 = new PatientAllergy;
		$allergy2->name = 'allergy 3';

		$resource->allergies = array($allergy1,$allergy2);

		return $resource;
	}

	public function testResourceToModel_NoSave_NoNewRecords()
	{
		$resource = $this->getResource();

		$total_patients = count(\Patient::model()->findAll());
		$total_allergies = count(\Allergy::model()->findAll());
		$total_alas = count(\PatientAllergyAssignment::model()->findAll());

		$ps = new PatientAllergiesService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient3'), false);

		$this->assertEquals($total_patients, count(\Patient::model()->findAll()));
		$this->assertEquals($total_allergies, count(\Allergy::model()->findAll()));
		$this->assertEquals($total_alas, count(\PatientAllergyAssignment::model()->findAll()));
	}

	public function testResourceToModel_NoSave_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientAllergiesService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient3'), false);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->allergyAssignments);

		$this->assertInstanceOf('PatientAllergyAssignment',$patient->allergyAssignments[0]);
		$this->assertInstanceOf('Allergy',$patient->allergyAssignments[0]->allergy);
		$this->assertEquals('allergy 2',$patient->allergyAssignments[0]->allergy->name);

		$this->assertInstanceOf('PatientAllergyAssignment',$patient->allergyAssignments[1]);
		$this->assertInstanceOf('Allergy',$patient->allergyAssignments[1]->allergy);
		$this->assertEquals('allergy 3',$patient->allergyAssignments[1]->allergy->name);
	}

	public function testResourceToModel_Save_Create_ModelCountsCorrect()
	{
		$resource = $this->getResource();

		$total_patients = count(\Patient::model()->findAll());
		$total_allergies = count(\Allergy::model()->findAll());
		$total_alas = count(\PatientAllergyAssignment::model()->findAll());

		$ps = new PatientAllergiesService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient3'));

		$this->assertEquals($total_patients, count(\Patient::model()->findAll()));
		$this->assertEquals($total_allergies, count(\Allergy::model()->findAll()));
		$this->assertEquals($total_alas+2, count(\PatientAllergyAssignment::model()->findAll()));
	}

	public function testResourceToModel_Save_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientAllergiesService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient3'));

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->allergyAssignments);

		$this->assertInstanceOf('PatientAllergyAssignment',$patient->allergyAssignments[0]);
		$this->assertInstanceOf('Allergy',$patient->allergyAssignments[0]->allergy);
		$this->assertEquals('allergy 2',$patient->allergyAssignments[0]->allergy->name);

		$this->assertInstanceOf('PatientAllergyAssignment',$patient->allergyAssignments[1]);
		$this->assertInstanceOf('Allergy',$patient->allergyAssignments[1]->allergy);
		$this->assertEquals('allergy 3',$patient->allergyAssignments[1]->allergy->name);
	}

	public function testResourceToModel_Save_Create_DBIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientAllergiesService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient3'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->allergyAssignments);

		$this->assertInstanceOf('PatientAllergyAssignment',$patient->allergyAssignments[0]);
		$this->assertInstanceOf('Allergy',$patient->allergyAssignments[0]->allergy);
		$this->assertEquals('allergy 2',$patient->allergyAssignments[0]->allergy->name);

		$this->assertInstanceOf('PatientAllergyAssignment',$patient->allergyAssignments[1]);
		$this->assertInstanceOf('Allergy',$patient->allergyAssignments[1]->allergy);
		$this->assertEquals('allergy 3',$patient->allergyAssignments[1]->allergy->name);
	}

	public function getModifiedResource($id)
	{
		$resource = \Yii::app()->service->PatientAllergies($id)->fetch();

		$resource->allergies[0]->name = 'allergy 3';
		$resource->allergies[1]->name = 'allergy 2';
		unset($resource->allergies[2]);

		return $resource;
	}

/*
	public function testResourceToModel_Save_Update_Modified_ModelCountsCorrect()
	{
		$resource = $this->getModifiedResource(1);

		$total_patients = count(\Patient::model()->findAll());
		$total_allergies = count(\Allergy::model()->findAll());
		$total_alas = count(\PatientAllergyAssignment::model()->findAll());

		$ps = new PatientAllergiesService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'));

		$this->assertEquals($total_patients, count(\Patient::model()->findAll()));
		$this->assertEquals($total_allergies, count(\Allergy::model()->findAll()));
		$this->assertEquals($total_alas-1, count(\PatientAllergyAssignment::model()->findAll()));
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
		$json = '{"contacts":[{"title":"Dr","family_name":"Zhivago","given_name":"Yuri","primary_phone":"999","institution_ref":null,"site_ref":null,"id":null,"last_modified":null},{"title":"Mr","family_name":"Inc","given_name":"Apple","primary_phone":"01010101","institution_ref":null,"site_ref":{"service":"Site","id":"2"},"id":null,"last_modified":null},{"title":"Ti","family_name":"Tiss","given_name":"Prac","primary_phone":"0303032332","institution_ref":{"service":"Institution","id":"2"},"site_ref":null,"id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"3","last_modified":-2208988800}}';

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

	public function jsonToModel_NoSave_NoNewRows()
	{
		$json = '{"contacts":[{"title":"Dr","family_name":"Zhivago","given_name":"Yuri","primary_phone":"999","institution_ref":null,"site_ref":null,"id":null,"last_modified":null},{"title":"Mr","family_name":"Inc","given_name":"Apple","primary_phone":"01010101","institution_ref":null,"site_ref":{"service":"Site","id":"2"},"id":null,"last_modified":null},{"title":"Ti","family_name":"Tiss","given_name":"Prac","primary_phone":"0303032332","institution_ref":{"service":"Institution","id":"2"},"site_ref":null,"id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"3","last_modified":-2208988800}}';

		$total_patients = count(\Patient::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_pcas = count(\PatientContactAssignment::model()->findAll());
		$total_sites = count(\Site::model()->findAll());
		$total_institutions = count(\Institution::model()->findAll());

		$ps = new PatientAssociatedContactsService;
		$patient = $ps->jsonToModel($json, false);

		$this->assertEquals($total_patients, count(\Patient::model()->findAll()));
		$this->assertEquals($total_contacts, count(\Contact::model()->findAll()));
		$this->assertEquals($total_pcas, count(\PatientContactAssignment::model()->findAll()));
		$this->assertEquals($total_sites, count(\Site::model()->findAll()));
		$this->assertEquals($total_institutions, count(\Institution::model()->findAll()));
	}

	public function testJsonToModel_NoSave_ModelIsCorrect()
	{
		$json = '{"contacts":[{"title":"Dr","family_name":"Zhivago","given_name":"Yuri","primary_phone":"999","institution_ref":null,"site_ref":null,"id":null,"last_modified":null},{"title":"Mr","family_name":"Inc","given_name":"Apple","primary_phone":"01010101","institution_ref":null,"site_ref":{"service":"Site","id":"2"},"id":null,"last_modified":null},{"title":"Ti","family_name":"Tiss","given_name":"Prac","primary_phone":"0303032332","institution_ref":{"service":"Institution","id":"2"},"site_ref":null,"id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"3","last_modified":-2208988800}}';

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

	public function testJsonToModel_Save_Create_ModelCountsCorrect()
	{
		$json = '{"contacts":[{"title":"Dr","family_name":"Zhivago2","given_name":"Yuri2","primary_phone":"999","institution_ref":null,"site_ref":null,"id":null,"last_modified":null},{"title":"Mr","family_name":"Inc","given_name":"Apple","primary_phone":"01010101","institution_ref":null,"site_ref":{"service":"Site","id":"2"},"id":null,"last_modified":null},{"title":"Ti","family_name":"Tiss","given_name":"Prac","primary_phone":"0303032332","institution_ref":{"service":"Institution","id":"1"},"site_ref":null,"id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"3","last_modified":-2208988800}}';

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
		$json = '{"contacts":[{"title":"Dr","family_name":"Zhivago2","given_name":"Yuri2","primary_phone":"999","institution_ref":null,"site_ref":null,"id":null,"last_modified":null},{"title":"Mr","family_name":"Inc","given_name":"Apple","primary_phone":"01010101","institution_ref":null,"site_ref":{"service":"Site","id":"2"},"id":null,"last_modified":null},{"title":"Ti","family_name":"Tiss","given_name":"Prac","primary_phone":"0303032332","institution_ref":{"service":"Institution","id":"1"},"site_ref":null,"id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"3","last_modified":-2208988800}}';

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
		$this->assertEquals(1,$patient->contactAssignments[2]->location->institution_id);
		$this->assertNull($patient->contactAssignments[2]->contact);
	}

	public function testJsonToModel_Save_Update_ModelCountsCorrect()
	{
		$json = '{"contacts":[{"title":"Dr","family_name":"Zhivago","given_name":"Yuri","primary_phone":"999","institution_ref":null,"site_ref":null,"id":null,"last_modified":null},{"title":"Mr","family_name":"Inc","given_name":"Apple","primary_phone":"01010101","institution_ref":null,"site_ref":{"service":"Site","id":"2"},"id":null,"last_modified":null},{"title":"Ti","family_name":"Tiss","given_name":"Prac","primary_phone":"0303032332","institution_ref":{"service":"Institution","id":"1"},"site_ref":null,"id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"3","last_modified":-2208988800}}';

		$total_patients = count(\Patient::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_pcas = count(\PatientContactAssignment::model()->findAll());
		$total_sites = count(\Site::model()->findAll());
		$total_institutions = count(\Institution::model()->findAll());

		$ps = new PatientAssociatedContactsService;
		$patient = $ps->jsonToModel($json, $this->patients('patient1'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertEquals($total_patients, count(\Patient::model()->findAll()));
		$this->assertEquals($total_contacts+2, count(\Contact::model()->findAll()));
		$this->assertEquals($total_pcas, count(\PatientContactAssignment::model()->findAll()));
		$this->assertEquals($total_sites, count(\Site::model()->findAll()));
		$this->assertEquals($total_institutions, count(\Institution::model()->findAll()));
	}

	public function testJsonToModel_Save_Update_DBIsCorrect()
	{
		$json = '{"contacts":[{"title":"Dr","family_name":"Zhivago","given_name":"Yuri","primary_phone":"999","institution_ref":null,"site_ref":null,"id":null,"last_modified":null},{"title":"Mr","family_name":"Inc","given_name":"Apple","primary_phone":"01010101","institution_ref":null,"site_ref":{"service":"Site","id":"2"},"id":null,"last_modified":null},{"title":"Ti","family_name":"Tiss","given_name":"Prac","primary_phone":"0303032332","institution_ref":{"service":"Institution","id":"1"},"site_ref":null,"id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"3","last_modified":-2208988800}}';

		$ps = new PatientAssociatedContactsService;
		$patient = $ps->jsonToModel($json, $this->patients('patient1'));
		$patient = \Patient::model()->findByPk($patient->id);

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
		$this->assertEquals(1,$patient->contactAssignments[2]->location->institution_id);
		$this->assertNull($patient->contactAssignments[2]->contact);
	}
	*/
}
