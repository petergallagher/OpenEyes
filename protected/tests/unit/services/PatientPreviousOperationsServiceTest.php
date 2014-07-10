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

class PatientPreviousOperationsServiceTest extends \CDbTestCase
{
	public $fixtures = array(
		'patients' => 'Patient',
		'previous_operations' => 'PreviousOperation',
	);

	public function testModelToResource()
	{
		$patient = $this->patients('patient1');

		$ps = new PatientPreviousOperationsService;

		$resource = $ps->modelToResource($patient);

		$this->assertInstanceOf('services\PatientPreviousOperations',$resource);
		$this->assertCount(3,$resource->operations);

		$this->assertInstanceOf('services\PatientPreviousOperation',$resource->operations[0]);
		$this->assertEquals($patient->previousOperations[0]->id,$resource->operations[0]->getId());
		$this->assertEquals('Legs removed',$resource->operations[0]->operation);
		$this->assertInstanceOf('services\Date',$resource->operations[0]->date);
		$this->assertFalse($resource->operations[0]->side);

		$this->assertInstanceOf('services\PatientPreviousOperation',$resource->operations[1]);
		$this->assertEquals($patient->previousOperations[1]->id,$resource->operations[1]->getId());
		$this->assertEquals('Left arm removed',$resource->operations[1]->operation);
		$this->assertInstanceOf('services\Date',$resource->operations[1]->date);
		$this->assertEquals('Left',$resource->operations[1]->side);

		$this->assertInstanceOf('services\PatientPreviousOperation',$resource->operations[2]);
		$this->assertEquals($patient->previousOperations[2]->id,$resource->operations[2]->getId());
		$this->assertEquals('Eye replaced with bionic implant',$resource->operations[2]->operation);
		$this->assertInstanceOf('services\Date',$resource->operations[2]->date);
		$this->assertEquals('Both',$resource->operations[2]->side);
	}

	public function testModelToResource_Empty()
	{
		$patient = $this->patients('patient2');

		$ps = new PatientPreviousOperationsService;

		$resource = $ps->modelToResource($patient);

		$this->assertInstanceOf('services\PatientPreviousOperations',$resource);
		$this->assertEmpty($resource->operations);
	}

	public function getResource()
	{
		$resource = new PatientPreviousOperations(2);

		$operation1 = new PatientPreviousOperation;
		$operation1->operation = 'Nose lasered';
		$operation1->side = 'Left';
		$operation1->date = new Date('2013-04-04');

		$operation2 = new PatientPreviousOperation;
		$operation2->operation = 'Ears lasered';
		$operation2->side = 'Right';
		$operation2->date = new Date('2014-01-17');

		$operation3 = new PatientPreviousOperation;
		$operation3->operation = 'Belly button lasered';
		$operation3->side = false;
		$operation3->date = new Date('2012-11-22');

		$resource->operations = array($operation1,$operation2,$operation3);

		return $resource;
	}

	public function testResourceToModel_NoSave_NoNewRecords()
	{
		$resource = $this->getResource();

		$total_pos = count(\PreviousOperation::model()->findAll());

		$ps = new PatientPreviousOperationsService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient2'), false);

		$this->assertEquals($total_pos, count(\PreviousOperation::model()->findAll()));
	}

	public function testResourceToModel_NoSave_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientPreviousOperationsService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient2'), false);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(3,$patient->previousOperations);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[0]);
		$this->assertEquals('Nose lasered',$patient->previousOperations[0]->operation);
		$this->assertInstanceOf('Eye',$patient->previousOperations[0]->side);
		$this->assertEquals('Left',$patient->previousOperations[0]->side->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Left'))->id,$patient->previousOperations[0]->side_id);
		$this->assertEquals('2013-04-04',$patient->previousOperations[0]->date);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[1]);
		$this->assertEquals('Ears lasered',$patient->previousOperations[1]->operation);
		$this->assertInstanceOf('Eye',$patient->previousOperations[1]->side);
		$this->assertEquals('Right',$patient->previousOperations[1]->side->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Right'))->id,$patient->previousOperations[1]->side_id);
		$this->assertEquals('2014-01-17',$patient->previousOperations[1]->date);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[2]);
		$this->assertEquals('Belly button lasered',$patient->previousOperations[2]->operation);
		$this->assertNull($patient->previousOperations[2]->side);
		$this->assertNull($patient->previousOperations[2]->side_id);
	}

	public function testResourceToModel_Save_Create_ModelCountsCorrect()
	{
		$resource = $this->getResource();

		$total_pos = count(\PreviousOperation::model()->findAll());

		$ps = new PatientPreviousOperationsService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient2'));

		$this->assertEquals($total_pos+3, count(\PreviousOperation::model()->findAll()));
	}

	public function testResourceToModel_Save_Create_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientPreviousOperationsService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient2'));

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(3,$patient->previousOperations);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[0]);
		$this->assertEquals('Nose lasered',$patient->previousOperations[0]->operation);
		$this->assertInstanceOf('Eye',$patient->previousOperations[0]->side);
		$this->assertEquals('Left',$patient->previousOperations[0]->side->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Left'))->id,$patient->previousOperations[0]->side_id);
		$this->assertEquals('2013-04-04',$patient->previousOperations[0]->date);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[1]);
		$this->assertEquals('Ears lasered',$patient->previousOperations[1]->operation);
		$this->assertInstanceOf('Eye',$patient->previousOperations[1]->side);
		$this->assertEquals('Right',$patient->previousOperations[1]->side->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Right'))->id,$patient->previousOperations[1]->side_id);
		$this->assertEquals('2014-01-17',$patient->previousOperations[1]->date);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[2]);
		$this->assertEquals('Belly button lasered',$patient->previousOperations[2]->operation);
		$this->assertNull($patient->previousOperations[2]->side);
		$this->assertNull($patient->previousOperations[2]->side_id);
	}

	public function testResourceToModel_Save_Create_DBIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientPreviousOperationsService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient2'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(3,$patient->previousOperations);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[0]);
		$this->assertEquals('Belly button lasered',$patient->previousOperations[0]->operation);
		$this->assertNull($patient->previousOperations[0]->side);
		$this->assertNull($patient->previousOperations[0]->side_id);
		$this->assertEquals('2012-11-22',$patient->previousOperations[0]->date);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[1]);
		$this->assertEquals('Nose lasered',$patient->previousOperations[1]->operation);
		$this->assertInstanceOf('Eye',$patient->previousOperations[1]->side);
		$this->assertEquals('Left',$patient->previousOperations[1]->side->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Left'))->id,$patient->previousOperations[1]->side_id);
		$this->assertEquals('2013-04-04',$patient->previousOperations[1]->date);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[2]);
		$this->assertEquals('Ears lasered',$patient->previousOperations[2]->operation);
		$this->assertInstanceOf('Eye',$patient->previousOperations[2]->side);
		$this->assertEquals('Right',$patient->previousOperations[2]->side->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Right'))->id,$patient->previousOperations[2]->side_id);
		$this->assertEquals('2014-01-17',$patient->previousOperations[2]->date);
	}

	public function getModifiedResource()
	{
		$resource = \Yii::app()->service->PatientPreviousOperations(1)->fetch();

		$resource->operations[0]->operation = 'Nose lasered';
		$resource->operations[0]->side = 'Left';
		$resource->operations[0]->date = new Date('2013-04-04');

		$resource->operations[1]->operation = 'Ears lasered';
		$resource->operations[1]->side = 'Right';
		$resource->operations[1]->date = new Date('2014-01-17');

		$resource->operations[2]->operation = 'Belly button lasered';
		$resource->operations[2]->side = false;
		$resource->operations[2]->date = new Date('2012-11-22');

		$operation4 = new PatientPreviousOperation;
		$operation4->operation = 'Wings clipped';
		$operation4->side = 'Both';
		$operation4->date = new Date('2014-01-11');

		$resource->operations[] = $operation4;

		return $resource;
	}

	public function testResourceToModel_Save_Update_Modified_ModelCountsCorrect()
	{
		$resource = $this->getModifiedResource();

		$total_pos = count(\PreviousOperation::model()->findAll());

		$ps = new PatientPreviousOperationsService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'));

		$this->assertEquals($total_pos+1, count(\PreviousOperation::model()->findAll()));
	}

	public function testResourceToModel_Save_Update_NotModified_ModelCountsCorrect()
	{
		$resource = \Yii::app()->service->PatientPreviousOperations(1)->fetch();

		$total_pos = count(\PreviousOperation::model()->findAll());

		$ps = new PatientPreviousOperationsService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'));

		$this->assertEquals($total_pos, count(\PreviousOperation::model()->findAll()));
	}

	public function testResourceToModel_Save_Update_ModelIsCorrect()
	{
		$resource = $this->getModifiedResource();

		$ps = new PatientPreviousOperationsService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'));

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(4,$patient->previousOperations);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[0]);
		$this->assertEquals(2,$patient->previousOperations[0]->id);
		$this->assertEquals('Nose lasered',$patient->previousOperations[0]->operation);
		$this->assertInstanceOf('Eye',$patient->previousOperations[0]->side);
		$this->assertEquals('Left',$patient->previousOperations[0]->side->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Left'))->id,$patient->previousOperations[0]->side_id);
		$this->assertEquals('2013-04-04',$patient->previousOperations[0]->date);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[1]);
		$this->assertEquals(1,$patient->previousOperations[1]->id);
		$this->assertEquals('Ears lasered',$patient->previousOperations[1]->operation);
		$this->assertInstanceOf('Eye',$patient->previousOperations[1]->side);
		$this->assertEquals('Right',$patient->previousOperations[1]->side->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Right'))->id,$patient->previousOperations[1]->side_id);
		$this->assertEquals('2014-01-17',$patient->previousOperations[1]->date);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[2]);
		$this->assertEquals(3,$patient->previousOperations[2]->id);
		$this->assertEquals('Belly button lasered',$patient->previousOperations[2]->operation);
		$this->assertNull($patient->previousOperations[2]->side);
		$this->assertNull($patient->previousOperations[2]->side_id);
		$this->assertEquals('2012-11-22',$patient->previousOperations[2]->date);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[3]);
		$this->assertEquals('Wings clipped',$patient->previousOperations[3]->operation);
		$this->assertInstanceOf('Eye',$patient->previousOperations[3]->side);
		$this->assertEquals('Both',$patient->previousOperations[3]->side->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Both'))->id,$patient->previousOperations[3]->side_id);
		$this->assertEquals('2014-01-11',$patient->previousOperations[3]->date);
	}

	public function testResourceToModel_Save_Update_DBIsCorrect()
	{
		$resource = $this->getModifiedResource();

		$ps = new PatientPreviousOperationsService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient3'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(4,$patient->previousOperations);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[1]);
		$this->assertEquals(2,$patient->previousOperations[1]->id);
		$this->assertEquals('Nose lasered',$patient->previousOperations[1]->operation);
		$this->assertInstanceOf('Eye',$patient->previousOperations[1]->side);
		$this->assertEquals('Left',$patient->previousOperations[1]->side->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Left'))->id,$patient->previousOperations[1]->side_id);
		$this->assertEquals('2013-04-04',$patient->previousOperations[1]->date);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[3]);
		$this->assertEquals(1,$patient->previousOperations[3]->id);
		$this->assertEquals('Ears lasered',$patient->previousOperations[3]->operation);
		$this->assertInstanceOf('Eye',$patient->previousOperations[3]->side);
		$this->assertEquals('Right',$patient->previousOperations[3]->side->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Right'))->id,$patient->previousOperations[3]->side_id);
		$this->assertEquals('2014-01-17',$patient->previousOperations[3]->date);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[0]);
		$this->assertEquals(3,$patient->previousOperations[0]->id);
		$this->assertEquals('Belly button lasered',$patient->previousOperations[0]->operation);
		$this->assertNull($patient->previousOperations[0]->side);
		$this->assertNull($patient->previousOperations[0]->side_id);
		$this->assertEquals('2012-11-22',$patient->previousOperations[0]->date);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[2]);
		$this->assertEquals('Wings clipped',$patient->previousOperations[2]->operation);
		$this->assertInstanceOf('Eye',$patient->previousOperations[2]->side);
		$this->assertEquals('Both',$patient->previousOperations[2]->side->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Both'))->id,$patient->previousOperations[2]->side_id);
		$this->assertEquals('2014-01-11',$patient->previousOperations[2]->date);
	}

	public function testJsonToResource()
	{
		$json = \Yii::app()->service->PatientPreviousOperations(1)->fetch()->serialise();

		$ps = new PatientPreviousOperationsService;
		$resource = $ps->jsonToResource($json);

		$this->assertInstanceOf('services\PatientPreviousOperations',$resource);
		$this->assertCount(3,$resource->operations);

		$this->assertInstanceOf('services\PatientPreviousOperation',$resource->operations[0]);
		$this->assertEquals(2,$resource->operations[0]->getId());
		$this->assertEquals('Legs removed',$resource->operations[0]->operation);
		$this->assertInstanceOf('services\Date',$resource->operations[0]->date);
		$this->assertFalse($resource->operations[0]->side);

		$this->assertInstanceOf('services\PatientPreviousOperation',$resource->operations[1]);
		$this->assertEquals(1,$resource->operations[1]->getId());
		$this->assertEquals('Left arm removed',$resource->operations[1]->operation);
		$this->assertInstanceOf('services\Date',$resource->operations[1]->date);
		$this->assertEquals('Left',$resource->operations[1]->side);

		$this->assertInstanceOf('services\PatientPreviousOperation',$resource->operations[2]);
		$this->assertEquals(3,$resource->operations[2]->getId());
		$this->assertEquals('Eye replaced with bionic implant',$resource->operations[2]->operation);
		$this->assertInstanceOf('services\Date',$resource->operations[2]->date);
		$this->assertEquals('Both',$resource->operations[2]->side);
	}

	public function testJsonToModel_NoSave_NoNewRows()
	{
		$json = '{"operations":[{"operation":"Legs removed","date":{"date":"2012-01-02 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"side":false,"id":null,"last_modified":null},{"operation":"Left arm removed","date":{"date":"2013-04-04 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"side":"Left","id":null,"last_modified":null},{"operation":"Eye replaced with bionic implant","date":{"date":"2014-01-22 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"side":"Both","id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800}}';

		$total_pos = count(\PreviousOperation::model()->findAll());

		$ps = new PatientPreviousOperationsService;
		$patient = $ps->jsonToModel($json, $this->patients('patient2'), false);

		$this->assertEquals($total_pos, count(\PreviousOperation::model()->findAll()));
	}

	public function testJsonToModel_NoSave_ModelIsCorrect()
	{
		$json = '{"operations":[{"operation":"Legs removed","date":{"date":"2012-01-02 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"side":false,"id":null,"last_modified":null},{"operation":"Left arm removed","date":{"date":"2013-04-04 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"side":"Left","id":null,"last_modified":null},{"operation":"Eye replaced with bionic implant","date":{"date":"2014-01-22 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"side":"Both","id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800}}';

		$ps = new PatientPreviousOperationsService;
		$patient = $ps->jsonToModel($json, $this->patients('patient2'), false);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(3,$patient->previousOperations);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[0]);
		$this->assertEquals('Legs removed',$patient->previousOperations[0]->operation);
		$this->assertNull($patient->previousOperations[0]->side);
		$this->assertEquals('2012-01-02',$patient->previousOperations[0]->date);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[1]);
		$this->assertEquals('Left arm removed',$patient->previousOperations[1]->operation);
		$this->assertInstanceOf('Eye',$patient->previousOperations[1]->side);
		$this->assertEquals('Left',$patient->previousOperations[1]->side->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Left'))->id,$patient->previousOperations[1]->side_id);
		$this->assertEquals('2013-04-04',$patient->previousOperations[1]->date);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[2]);
		$this->assertEquals('Eye replaced with bionic implant',$patient->previousOperations[2]->operation);
		$this->assertInstanceOf('Eye',$patient->previousOperations[2]->side);
		$this->assertEquals('Both',$patient->previousOperations[2]->side->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Both'))->id,$patient->previousOperations[2]->side_id);
		$this->assertEquals('2014-01-22',$patient->previousOperations[2]->date);
	}

	public function testJsonToModel_Save_Create_ModelCountsCorrect()
	{
		$json = '{"operations":[{"operation":"Legs removed","date":{"date":"2012-01-02 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"side":false,"id":null,"last_modified":null},{"operation":"Left arm removed","date":{"date":"2013-04-04 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"side":"Left","id":null,"last_modified":null},{"operation":"Eye replaced with bionic implant","date":{"date":"2014-01-22 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"side":"Both","id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800}}';

		$total_pos = count(\PreviousOperation::model()->findAll());

		$ps = new PatientPreviousOperationsService;
		$patient = $ps->jsonToModel($json, $this->patients('patient2'));

		$this->assertEquals($total_pos+3, count(\PreviousOperation::model()->findAll()));
	}

	public function testJsonToModel_Save_Create_ModelIsCorrect()
	{
		$json = '{"operations":[{"operation":"Legs removed","date":{"date":"2012-01-02 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"side":false,"id":null,"last_modified":null},{"operation":"Left arm removed","date":{"date":"2013-04-04 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"side":"Left","id":null,"last_modified":null},{"operation":"Eye replaced with bionic implant","date":{"date":"2014-01-22 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"side":"Both","id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800}}';

		$ps = new PatientPreviousOperationsService;
		$patient = $ps->jsonToModel($json, $this->patients('patient2'));

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(3,$patient->previousOperations);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[0]);
		$this->assertEquals('Legs removed',$patient->previousOperations[0]->operation);
		$this->assertNull($patient->previousOperations[0]->side);
		$this->assertEquals('2012-01-02',$patient->previousOperations[0]->date);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[1]);
		$this->assertEquals('Left arm removed',$patient->previousOperations[1]->operation);
		$this->assertInstanceOf('Eye',$patient->previousOperations[1]->side);
		$this->assertEquals('Left',$patient->previousOperations[1]->side->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Left'))->id,$patient->previousOperations[1]->side_id);
		$this->assertEquals('2013-04-04',$patient->previousOperations[1]->date);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[2]);
		$this->assertEquals('Eye replaced with bionic implant',$patient->previousOperations[2]->operation);
		$this->assertInstanceOf('Eye',$patient->previousOperations[2]->side);
		$this->assertEquals('Both',$patient->previousOperations[2]->side->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Both'))->id,$patient->previousOperations[2]->side_id);
		$this->assertCount(3,$patient->contactAssignments);
	}

	public function testJsonToModel_Save_Create_DBIsCorrect()
	{
		$json = '{"operations":[{"operation":"Legs removed","date":{"date":"2012-01-02 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"side":false,"id":null,"last_modified":null},{"operation":"Left arm removed","date":{"date":"2013-04-04 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"side":"Left","id":null,"last_modified":null},{"operation":"Eye replaced with bionic implant","date":{"date":"2014-01-22 00:00:00","timezone_type":3,"timezone":"Europe\/London"},"side":"Both","id":null,"last_modified":null}],"id":null,"last_modified":null,"patient_id":{"id":"1","last_modified":-2208988800}}';

		$ps = new PatientPreviousOperationsService;
		$patient = $ps->jsonToModel($json, $this->patients('patient2'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(3,$patient->previousOperations);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[0]);
		$this->assertEquals('Legs removed',$patient->previousOperations[0]->operation);
		$this->assertNull($patient->previousOperations[0]->side);
		$this->assertEquals('2012-01-02',$patient->previousOperations[0]->date);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[1]);
		$this->assertEquals('Left arm removed',$patient->previousOperations[1]->operation);
		$this->assertInstanceOf('Eye',$patient->previousOperations[1]->side);
		$this->assertEquals('Left',$patient->previousOperations[1]->side->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Left'))->id,$patient->previousOperations[1]->side_id);
		$this->assertEquals('2013-04-04',$patient->previousOperations[1]->date);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[2]);
		$this->assertEquals('Eye replaced with bionic implant',$patient->previousOperations[2]->operation);
		$this->assertInstanceOf('Eye',$patient->previousOperations[2]->side);
		$this->assertEquals('Both',$patient->previousOperations[2]->side->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Both'))->id,$patient->previousOperations[2]->side_id);
		$this->assertCount(3,$patient->contactAssignments);
	}

	public function testJsonToModel_Save_Update_ModelCountsCorrect()
	{
		$resource = \Yii::app()->service->PatientPreviousOperations(1)->fetch();

		$resource->operations[1]->operation = 'Left arm removed2';
		$resource->operations[2]->operation = 'Eye replaced with bionic implant2';

		$json = $resource->serialise();

		$total_pos = count(\PreviousOperation::model()->findAll());

		$ps = new PatientPreviousOperationsService;
		$patient = $ps->jsonToModel($json, $this->patients('patient1'));

		$this->assertEquals($total_pos, count(\PreviousOperation::model()->findAll()));
	}

	public function testJsonToModel_Save_Update_ModelIsCorrect()
	{
		$resource = \Yii::app()->service->PatientPreviousOperations(1)->fetch();

		$resource->operations[1]->operation = 'Left arm removed2';
		$resource->operations[1]->side = 'Right';
		$resource->operations[1]->date = new Date('2012-04-04');
		$resource->operations[2]->operation = 'Eye replaced with bionic implant2';
		$resource->operations[2]->side = 'Left';
		$resource->operations[2]->date = new Date('2012-11-13');

		$json = $resource->serialise();

		$ps = new PatientPreviousOperationsService;
		$patient = $ps->jsonToModel($json, $this->patients('patient1'));

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(3,$patient->previousOperations);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[0]);
		$this->assertEquals($resource->operations[0]->getId(),$patient->previousOperations[0]->id);
		$this->assertEquals('Legs removed',$patient->previousOperations[0]->operation);
		$this->assertNull($patient->previousOperations[0]->side);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[1]);
		$this->assertEquals($resource->operations[1]->getId(),$patient->previousOperations[1]->id);
		$this->assertEquals('Left arm removed2',$patient->previousOperations[1]->operation);
		$this->assertInstanceOf('Eye',$patient->previousOperations[1]->side);
		$this->assertEquals('Right',$patient->previousOperations[1]->side->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Right'))->id,$patient->previousOperations[1]->side_id);
		$this->assertEquals('2012-04-04',$patient->previousOperations[1]->date);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[2]);
		$this->assertEquals($resource->operations[2]->getId(),$patient->previousOperations[2]->id);
		$this->assertEquals('Eye replaced with bionic implant2',$patient->previousOperations[2]->operation);
		$this->assertInstanceOf('Eye',$patient->previousOperations[2]->side);
		$this->assertEquals('Left',$patient->previousOperations[2]->side->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Left'))->id,$patient->previousOperations[2]->side_id);
		$this->assertEquals('2012-11-13',$patient->previousOperations[2]->date);
	}

	public function testJsonToModel_Save_Update_DBIsCorrect()
	{
		$resource = \Yii::app()->service->PatientPreviousOperations(1)->fetch();

		$resource->operations[1]->operation = 'Left arm removed2';
		$resource->operations[1]->side = 'Right';
		$resource->operations[1]->date = new Date('2012-04-04');
		$resource->operations[2]->operation = 'Eye replaced with bionic implant2';
		$resource->operations[2]->side = 'Left';
		$resource->operations[2]->date = new Date('2012-11-13');

		$json = $resource->serialise();

		$ps = new PatientPreviousOperationsService;
		$patient = $ps->jsonToModel($json, $this->patients('patient1'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(3,$patient->previousOperations);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[1]);
		$this->assertEquals($resource->operations[1]->getId(),$patient->previousOperations[1]->id);
		$this->assertEquals('Left arm removed2',$patient->previousOperations[1]->operation);
		$this->assertInstanceOf('Eye',$patient->previousOperations[1]->side);
		$this->assertEquals('Right',$patient->previousOperations[1]->side->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Right'))->id,$patient->previousOperations[1]->side_id);
		$this->assertEquals('2012-04-04',$patient->previousOperations[1]->date);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[2]);
		$this->assertEquals($resource->operations[2]->getId(),$patient->previousOperations[2]->id);
		$this->assertEquals('Eye replaced with bionic implant2',$patient->previousOperations[2]->operation);
		$this->assertInstanceOf('Eye',$patient->previousOperations[2]->side);
		$this->assertEquals('Left',$patient->previousOperations[2]->side->name);
		$this->assertEquals(\Eye::model()->find('name=?',array('Left'))->id,$patient->previousOperations[2]->side_id);
		$this->assertEquals('2012-11-13',$patient->previousOperations[2]->date);

		$this->assertInstanceOf('PreviousOperation',$patient->previousOperations[0]);
		$this->assertEquals($resource->operations[0]->getId(),$patient->previousOperations[0]->id);
		$this->assertEquals('Legs removed',$patient->previousOperations[0]->operation);
		$this->assertNull($patient->previousOperations[0]->side);
	}
}
