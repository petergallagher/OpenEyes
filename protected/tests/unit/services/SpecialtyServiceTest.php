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

class SpecialtyServiceTest extends \CDbTestCase
{
	public $fixtures = array(
		'types' => 'SpecialtyType',
		'specialties' => 'Specialty',
	);

	public function testModelToResource()
	{
		$specialty = $this->specialties('example');

		$ps = new SpecialtyService;

		$resource = $ps->modelToResource($specialty);

		$this->verifyResource($resource, $specialty);
	}

	public function verifyResource($resource, $specialty)
	{
		$this->assertInstanceOf('services\SpecialtyTypeReference',$resource->specialty_type_ref);
		$this->assertEquals($specialty->specialty_type_id,$resource->specialty_type_ref->getId());

		foreach (array('name','code','default_title','default_is_surgeon','adjective','abbreviation') as $field) {
			$this->assertEquals($this->specialties('example')->$field,$resource->$field);
		}
	}

	public function getResource()
	{
		return \Yii::app()->service->Specialty(1)->fetch();
	}

	public function testResourceToModel_NoSave_NoNewRecords()
	{
		$resource = $this->getResource();

		$t = count(\Specialty::model()->findAll());

		$ps = new SpecialtyService;
		$specialty = $ps->resourceToModel($resource, new \Specialty, false);

		$this->assertEquals($t, count(\Specialty::model()->findAll()));
	}

	public function testResourceToModel_NoSave_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new SpecialtyService;
		$specialty = $ps->resourceToModel($resource, new \Specialty, false);

		$this->verifySpecialty($specialty, $resource);
	}

	public function verifySpecialty($specialty, $resource, $keys=array())
	{
		$this->assertInstanceOf('Specialty',$specialty);

		$this->assertEquals($resource->specialty_type_ref->getId(),$specialty->specialty_type_id);

		foreach (array('name','code','default_title','default_is_surgeon','adjective','abbreviation') as $field) {
			$this->assertEquals($resource->$field,$specialty->$field);
		}
	}

	public function getNewResource()
	{
		$resource = $this->getResource();

		$resource->name = 'wabwabwab';
		$resource->code = 133;
		$resource->default_title = 'Lord';
		$resource->default_is_surgeon = 1;
		$resource->adjective = 'foo';
		$resource->abbreviation = 'TF';
		$resource->specialty_type_ref = \Yii::app()->service->SpecialtyType(2);

		return $resource;
	}

	public function testResourceToModel_Save_Create_ModelCountsCorrect()
	{
		$resource = $this->getNewResource();

		$t = count(\Specialty::model()->findAll());

		$ps = new SpecialtyService;
		$specialty = $ps->resourceToModel($resource, new \Specialty);

		$this->assertEquals($t+1, count(\Specialty::model()->findAll()));
	}

	public function verifyNewSpecialty($specialty)
	{
		$this->assertInstanceOf('Specialty',$specialty);

		$this->assertEquals(2,$specialty->specialty_type_id);
		$this->assertEquals('wabwabwab',$specialty->name);
		$this->assertEquals('133',$specialty->code);
		$this->assertEquals('Lord',$specialty->default_title);
		$this->assertEquals(1,$specialty->default_is_surgeon);
		$this->assertEquals('foo',$specialty->adjective);
		$this->assertEquals('TF',$specialty->abbreviation);
	}

	public function testResourceToModel_Save_Create_ModelIsCorrect()
	{
		$resource = $this->getNewResource();

		$ps = new SpecialtyService;
		$specialty = $ps->resourceToModel($resource, new \Specialty);

		$this->verifyNewSpecialty($specialty);
	}

	public function testResourceToModel_Save_Create_DBIsCorrect()
	{
		$resource = $this->getNewResource();

		$ps = new SpecialtyService;
		$specialty = $ps->resourceToModel($resource, new \Specialty);
		$specialty = \Specialty::model()->findByPk($specialty->id);

		$this->verifyNewSpecialty($specialty);
	}

	public function testResourceToModel_Save_Update_Modified_ModelCountsCorrect()
	{
		$resource = $this->getNewResource();

		$ts = count(\Specialty::model()->findAll());
		$tb = count(\OphTrOperationbooking_Operation_Booking::model()->findAll());

		$ps = new SpecialtyService;
		$specialty = $ps->resourceToModel($resource, $this->specialties('example'));

		$this->assertEquals($ts, count(\Specialty::model()->findAll()));
		$this->assertEquals($tb, count(\OphTrOperationbooking_Operation_Booking::model()->findAll()));
	}

	public function testResourceToModel_Save_Update_ModelIsCorrect()
	{
		$resource = $this->getNewResource();

		$ps = new SpecialtyService;
		$specialty = $ps->resourceToModel($resource, $this->specialties('example'));

		$this->assertEquals(1,$specialty->id);

		$this->verifyNewSpecialty($specialty);
	}

	public function testResourceToModel_Save_Update_DBIsCorrect()
	{
		$resource = $this->getNewResource();

		$ps = new SpecialtyService;
		$specialty = $ps->resourceToModel($resource, $this->specialties('example'));
		$specialty = \Specialty::model()->findByPk($specialty->id);

		$this->assertEquals(1,$specialty->id);

		$this->verifyNewSpecialty($specialty);
	}

	public function testJsonToResource()
	{
		$specialty = $this->specialties('example');
		$json = \Yii::app()->service->Specialty($specialty->id)->fetch()->serialise();

		$ps = new SpecialtyService;

		$resource = $ps->jsonToResource($json);

		$this->verifyResource($resource, $specialty);
	}

	public function testJsonToModel_NoSave_NoNewRecords()
	{
		$resource = $this->getResource();
		$json = $resource->serialise();

		$total_s = count(\Specialty::model()->findAll());

		$ps = new SpecialtyService;
		$specialty = $ps->jsonToModel($json, new \Specialty, false);

		$this->assertEquals($total_s, count(\Specialty::model()->findAll()));
	}

	public function testJsonToModel_NoSave_ModelIsCorrect()
	{
		$resource = $this->getResource();
		$json = $resource->serialise();

		$ps = new SpecialtyService;
		$specialty = $ps->jsonToModel($json, new \Specialty, false);

		$this->verifySpecialty($specialty, $resource);
	}

	public function testJsonToModel_Save_Create_ModelCountsCorrect()
	{
		$resource = $this->getNewResource();
		$json = $resource->serialise();

		$total_s = count(\Specialty::model()->findAll());

		$ps = new SpecialtyService;
		$specialty = $ps->jsonToModel($json, new \Specialty);

		$this->assertEquals($total_s+1, count(\Specialty::model()->findAll()));
	}

	public function testJsonToModel_Save_Create_ModelIsCorrect()
	{
		$resource = $this->getNewResource();
		$json = $resource->serialise();

		$ps = new SpecialtyService;
		$specialty = $ps->jsonToModel($json, new \Specialty);

		$this->verifyNewSpecialty($specialty);
	}

	public function testJsonToModel_Save_Create_DBIsCorrect()
	{
		$resource = $this->getNewResource();
		$json = $resource->serialise();

		$ps = new SpecialtyService;
		$specialty = $ps->jsonToModel($json, new \Specialty);
		$specialty = \Specialty::model()->findByPk($specialty->id);

		$this->verifyNewSpecialty($specialty);
	}

	public function testJsonToModel_Save_Update_Modified_ModelCountsCorrect()
	{
		$resource = $this->getNewResource();
		$json = $resource->serialise();

		$total_s = count(\Specialty::model()->findAll());

		$ps = new SpecialtyService;
		$specialty = $ps->jsonToModel($json, $this->specialties('example'));

		$this->assertEquals($total_s, count(\Specialty::model()->findAll()));
	}

	public function testJsonToModel_Save_Update_ModelIsCorrect()
	{
		$resource = $this->getNewResource();
		$json = $resource->serialise();

		$ps = new SpecialtyService;
		$specialty = $ps->jsonToModel($json, $this->specialties('example'));

		$this->assertEquals(1,$specialty->id);

		$this->verifyNewSpecialty($specialty);
	}

	public function testJsonToModel_Save_Update_DBIsCorrect()
	{
		$resource = $this->getNewResource();
		$json = $resource->serialise();

		$ps = new SpecialtyService;
		$specialty = $ps->jsonToModel($json, $this->specialties('example'));
		$specialty = \Specialty::model()->findByPk($specialty->id);

		$this->assertEquals(1,$specialty->id);

		$this->verifyNewSpecialty($specialty);
	}
}
