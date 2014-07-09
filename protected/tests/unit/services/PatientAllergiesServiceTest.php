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
		$this->assertEquals($patient->allergyAssignments[0]->id,$resource->allergies[0]->getId());
		$this->assertEquals('allergy 1',$resource->allergies[0]->name);

		$this->assertInstanceOf('services\PatientAllergy',$resource->allergies[1]);
		$this->assertEquals($patient->allergyAssignments[1]->id,$resource->allergies[1]->getId());
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
		unset($resource->allergies[1]);

		return $resource;
	}

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
		$resource = \Yii::app()->service->PatientAllergies(1)->fetch();

		$total_patients = count(\Patient::model()->findAll());
		$total_allergies = count(\Allergy::model()->findAll());
		$total_alas = count(\PatientAllergyAssignment::model()->findAll());

		$ps = new PatientAllergiesService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'));

		$this->assertEquals($total_patients, count(\Patient::model()->findAll()));
		$this->assertEquals($total_allergies, count(\Allergy::model()->findAll()));
		$this->assertEquals($total_alas, count(\PatientAllergyAssignment::model()->findAll()));
	}

	public function testResourceToModel_Save_Update_ModelIsCorrect()
	{
		$resource = $this->getModifiedResource(1);

		$resource->allergies[1] = new PatientAllergy;
		$resource->allergies[1]->name = 'allergy 1';

		$resource->allergies[2] = new PatientAllergy;
		$resource->allergies[2]->name = 'allergy 2';

		$ps = new PatientAllergiesService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'));

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(3,$patient->allergyAssignments);

		$this->assertInstanceOf('PatientAllergyAssignment',$patient->allergyAssignments[0]);
		$this->assertEquals($resource->allergies[0]->getId(),$patient->allergyAssignments[0]->id);
		$this->assertInstanceOf('Allergy',$patient->allergyAssignments[0]->allergy);
		$this->assertEquals('allergy 3',$patient->allergyAssignments[0]->allergy->name);

		$this->assertInstanceOf('PatientAllergyAssignment',$patient->allergyAssignments[1]);
		$this->assertInstanceOf('Allergy',$patient->allergyAssignments[1]->allergy);
		$this->assertEquals('allergy 1',$patient->allergyAssignments[1]->allergy->name);

		$this->assertInstanceOf('PatientAllergyAssignment',$patient->allergyAssignments[2]);
		$this->assertInstanceOf('Allergy',$patient->allergyAssignments[2]->allergy);
		$this->assertEquals('allergy 2',$patient->allergyAssignments[2]->allergy->name);
	}

	public function testResourceToModel_Save_Update_DBIsCorrect()
	{
		$resource = $this->getModifiedResource(1);

		$resource->allergies[1] = new PatientAllergy;
		$resource->allergies[1]->name = 'allergy 1';

		$resource->allergies[2] = new PatientAllergy;
		$resource->allergies[2]->name = 'allergy 2';

		$ps = new PatientAllergiesService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(3,$patient->allergyAssignments);

		$this->assertInstanceOf('PatientAllergyAssignment',$patient->allergyAssignments[0]);
		$this->assertEquals($resource->allergies[0]->getId(),$patient->allergyAssignments[0]->id);
		$this->assertInstanceOf('Allergy',$patient->allergyAssignments[0]->allergy);
		$this->assertEquals('allergy 3',$patient->allergyAssignments[0]->allergy->name);

		$this->assertInstanceOf('PatientAllergyAssignment',$patient->allergyAssignments[1]);
		$this->assertInstanceOf('Allergy',$patient->allergyAssignments[1]->allergy);
		$this->assertEquals('allergy 1',$patient->allergyAssignments[1]->allergy->name);

		$this->assertInstanceOf('PatientAllergyAssignment',$patient->allergyAssignments[2]);
		$this->assertInstanceOf('Allergy',$patient->allergyAssignments[2]->allergy);
		$this->assertEquals('allergy 2',$patient->allergyAssignments[2]->allergy->name);
	}

	public function testJsonToResource()
	{
		$json = '{"allergies":[{"name":"allergy 1","id":null,"last_modified":null},{"name":"allergy 2","id":null,"last_modified":null}],"no_allergies_date":null,"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800}}';

		$ps = new PatientAllergiesService;
		$resource = $ps->jsonToResource($json);

		$this->assertInstanceOf('services\PatientAllergies',$resource);
		$this->assertCount(2,$resource->allergies);

		$this->assertInstanceOf('services\PatientAllergy',$resource->allergies[0]);
		$this->assertEquals('allergy 1',$resource->allergies[0]->name);

		$this->assertInstanceOf('services\PatientAllergy',$resource->allergies[1]);
		$this->assertEquals('allergy 2',$resource->allergies[1]->name);
	}

	public function testJsonToModel_NoSave_NoNewRows()
	{
		$json = '{"allergies":[{"name":"allergy 1","id":null,"last_modified":null},{"name":"allergy 2","id":null,"last_modified":null}],"no_allergies_date":null,"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800}}';

		$total_patients = count(\Patient::model()->findAll());
		$total_allergies = count(\Allergy::model()->findAll());
		$total_alas = count(\PatientAllergyAssignment::model()->findAll());

		$ps = new PatientAllergiesService;
		$patient = $ps->jsonToModel($json, new \Patient, false);

		$this->assertEquals($total_patients, count(\Patient::model()->findAll()));
		$this->assertEquals($total_allergies, count(\Allergy::model()->findAll()));
		$this->assertEquals($total_alas, count(\PatientAllergyAssignment::model()->findAll()));
	}

	public function testJsonToModel_NoSave_ModelIsCorrect()
	{
		$json = '{"allergies":[{"name":"allergy 1","id":null,"last_modified":null},{"name":"allergy 2","id":null,"last_modified":null}],"no_allergies_date":null,"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800}}';

		$ps = new PatientAllergiesService;
		$patient = $ps->jsonToModel($json, new \Patient, false);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->allergyAssignments);

		$this->assertInstanceOf('PatientAllergyAssignment',$patient->allergyAssignments[0]);
		$this->assertInstanceOf('Allergy',$patient->allergyAssignments[0]->allergy);
		$this->assertEquals('allergy 1',$patient->allergyAssignments[0]->allergy->name);

		$this->assertInstanceOf('PatientAllergyAssignment',$patient->allergyAssignments[1]);
		$this->assertInstanceOf('Allergy',$patient->allergyAssignments[1]->allergy);
		$this->assertEquals('allergy 2',$patient->allergyAssignments[1]->allergy->name);
	}

	public function testJsonToModel_Save_ModelCountsCorrect()
	{
		$json = '{"allergies":[{"name":"allergy 1","id":null,"last_modified":null},{"name":"allergy 2","id":null,"last_modified":null}],"no_allergies_date":null,"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800}}';

		$total_patients = count(\Patient::model()->findAll());
		$total_allergies = count(\Allergy::model()->findAll());
		$total_alas = count(\PatientAllergyAssignment::model()->findAll());

		$ps = new PatientAllergiesService;
		$patient = $ps->jsonToModel($json, $this->patients('patient4'));

		$this->assertEquals($total_patients, count(\Patient::model()->findAll()));
		$this->assertEquals($total_allergies, count(\Allergy::model()->findAll()));
		$this->assertEquals($total_alas+2, count(\PatientAllergyAssignment::model()->findAll()));
	}

	public function testJsonToModel_Save_ModelIsCorrect()
	{
		$json = '{"allergies":[{"name":"allergy 1","id":null,"last_modified":null},{"name":"allergy 2","id":null,"last_modified":null}],"no_allergies_date":null,"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800}}';

		$ps = new PatientAllergiesService;
		$patient = $ps->jsonToModel($json, $this->patients('patient4'));

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->allergyAssignments);

		$this->assertInstanceOf('PatientAllergyAssignment',$patient->allergyAssignments[0]);
		$this->assertInstanceOf('Allergy',$patient->allergyAssignments[0]->allergy);
		$this->assertEquals('allergy 1',$patient->allergyAssignments[0]->allergy->name);

		$this->assertInstanceOf('PatientAllergyAssignment',$patient->allergyAssignments[1]);
		$this->assertInstanceOf('Allergy',$patient->allergyAssignments[1]->allergy);
		$this->assertEquals('allergy 2',$patient->allergyAssignments[1]->allergy->name);
	}

	public function testJsonToModel_Save_DBIsCorrect()
	{
		$json = '{"allergies":[{"name":"allergy 1","id":null,"last_modified":null},{"name":"allergy 2","id":null,"last_modified":null}],"no_allergies_date":null,"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800}}';

		$ps = new PatientAllergiesService;
		$patient = $ps->jsonToModel($json, $this->patients('patient4'));

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->allergyAssignments);

		$this->assertInstanceOf('PatientAllergyAssignment',$patient->allergyAssignments[0]);
		$this->assertInstanceOf('Allergy',$patient->allergyAssignments[0]->allergy);
		$this->assertEquals('allergy 1',$patient->allergyAssignments[0]->allergy->name);

		$this->assertInstanceOf('PatientAllergyAssignment',$patient->allergyAssignments[1]);
		$this->assertInstanceOf('Allergy',$patient->allergyAssignments[1]->allergy);
		$this->assertEquals('allergy 2',$patient->allergyAssignments[1]->allergy->name);
	}
}
