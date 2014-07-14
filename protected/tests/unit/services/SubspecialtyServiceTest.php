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

class SubspecialtyServiceTest extends \CDbTestCase
{
	public $fixtures = array(
		'types' => 'SpecialtyType',
		'specialties' => 'Specialty',
		'subspecialties' => 'Subspecialty',
	);

	public function testModelToResource()
	{
		$subspecialty = $this->subspecialties('subspecialty1');

		$ps = new SubspecialtyService;

		$resource = $ps->modelToResource($subspecialty);

		$this->verifyResource($resource, $subspecialty);
	}

	public function verifyResource($resource, $subspecialty)
	{
		$this->assertInstanceOf('services\SpecialtyReference',$resource->specialty_ref);
		$this->assertEquals($subspecialty->specialty_id,$resource->specialty_ref->getId());

		foreach (array('name','ref_spec') as $field) {
			$this->assertEquals($this->subspecialties('subspecialty1')->$field,$resource->$field);
		}
	}

	public function getResource()
	{
		return \Yii::app()->service->Subspecialty(1)->fetch();
	}

	public function testResourceToModel_NoSave_NoNewRecords()
	{
		$resource = $this->getResource();

		$t = count(\Subspecialty::model()->findAll());

		$ps = new SubspecialtyService;
		$subspecialty = $ps->resourceToModel($resource, new \Subspecialty, false);

		$this->assertEquals($t, count(\Subspecialty::model()->findAll()));
	}

	public function testResourceToModel_NoSave_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new SubspecialtyService;
		$subspecialty = $ps->resourceToModel($resource, new \Subspecialty, false);

		$this->verifySpecialty($subspecialty, $resource);
	}

	public function verifySpecialty($subspecialty, $resource, $keys=array())
	{
		$this->assertInstanceOf('Subspecialty',$subspecialty);

		$this->assertEquals($resource->specialty_ref->getId(),$subspecialty->specialty_id);

		foreach (array('name','ref_spec') as $field) {
			$this->assertEquals($resource->$field,$subspecialty->$field);
		}
	}

	public function getNewResource()
	{
		$resource = $this->getResource();

		$resource->name = 'wabwabwab';
		$resource->ref_spec = 'x01';
		$resource->specialty_ref = \Yii::app()->service->Specialty(2);

		return $resource;
	}

	public function testResourceToModel_Save_Create_ModelCountsCorrect()
	{
		$resource = $this->getNewResource();

		$t = count(\Subspecialty::model()->findAll());

		$ps = new SubspecialtyService;
		$subspecialty = $ps->resourceToModel($resource, new \Subspecialty);

		$this->assertEquals($t+1, count(\Subspecialty::model()->findAll()));
	}

	public function verifyNewSpecialty($subspecialty)
	{
		$this->assertInstanceOf('Subspecialty',$subspecialty);

		$this->assertEquals(2,$subspecialty->specialty_id);
		$this->assertEquals('wabwabwab',$subspecialty->name);
		$this->assertEquals('x01',$subspecialty->ref_spec);
	}

	public function testResourceToModel_Save_Create_ModelIsCorrect()
	{
		$resource = $this->getNewResource();

		$ps = new SubspecialtyService;
		$subspecialty = $ps->resourceToModel($resource, new \Subspecialty);

		$this->verifyNewSpecialty($subspecialty);
	}

	public function testResourceToModel_Save_Create_DBIsCorrect()
	{
		$resource = $this->getNewResource();

		$ps = new SubspecialtyService;
		$subspecialty = $ps->resourceToModel($resource, new \Subspecialty);
		$subspecialty = \Subspecialty::model()->findByPk($subspecialty->id);

		$this->verifyNewSpecialty($subspecialty);
	}

	public function testResourceToModel_Save_Update_Modified_ModelCountsCorrect()
	{
		$resource = $this->getNewResource();

		$ts = count(\Subspecialty::model()->findAll());
		$tb = count(\OphTrOperationbooking_Operation_Booking::model()->findAll());

		$ps = new SubspecialtyService;
		$subspecialty = $ps->resourceToModel($resource, $this->subspecialties('subspecialty1'));

		$this->assertEquals($ts, count(\Subspecialty::model()->findAll()));
		$this->assertEquals($tb, count(\OphTrOperationbooking_Operation_Booking::model()->findAll()));
	}

	public function testResourceToModel_Save_Update_ModelIsCorrect()
	{
		$resource = $this->getNewResource();

		$ps = new SubspecialtyService;
		$subspecialty = $ps->resourceToModel($resource, $this->subspecialties('subspecialty1'));

		$this->assertEquals(1,$subspecialty->id);

		$this->verifyNewSpecialty($subspecialty);
	}

	public function testResourceToModel_Save_Update_DBIsCorrect()
	{
		$resource = $this->getNewResource();

		$ps = new SubspecialtyService;
		$subspecialty = $ps->resourceToModel($resource, $this->subspecialties('subspecialty1'));
		$subspecialty = \Subspecialty::model()->findByPk($subspecialty->id);

		$this->assertEquals(1,$subspecialty->id);

		$this->verifyNewSpecialty($subspecialty);
	}

	public function testJsonToResource()
	{
		$subspecialty = $this->subspecialties('subspecialty1');
		$json = \Yii::app()->service->Subspecialty($subspecialty->id)->fetch()->serialise();

		$ps = new SubspecialtyService;

		$resource = $ps->jsonToResource($json);

		$this->verifyResource($resource, $subspecialty);
	}

	public function testJsonToModel_NoSave_NoNewRecords()
	{
		$resource = $this->getResource();
		$json = $resource->serialise();

		$total_s = count(\Subspecialty::model()->findAll());

		$ps = new SubspecialtyService;
		$subspecialty = $ps->jsonToModel($json, new \Subspecialty, false);

		$this->assertEquals($total_s, count(\Subspecialty::model()->findAll()));
	}

	public function testJsonToModel_NoSave_ModelIsCorrect()
	{
		$resource = $this->getResource();
		$json = $resource->serialise();

		$ps = new SubspecialtyService;
		$subspecialty = $ps->jsonToModel($json, new \Subspecialty, false);

		$this->verifySpecialty($subspecialty, $resource);
	}

	public function testJsonToModel_Save_Create_ModelCountsCorrect()
	{
		$resource = $this->getNewResource();
		$json = $resource->serialise();

		$total_s = count(\Subspecialty::model()->findAll());

		$ps = new SubspecialtyService;
		$subspecialty = $ps->jsonToModel($json, new \Subspecialty);

		$this->assertEquals($total_s+1, count(\Subspecialty::model()->findAll()));
	}

	public function testJsonToModel_Save_Create_ModelIsCorrect()
	{
		$resource = $this->getNewResource();
		$json = $resource->serialise();

		$ps = new SubspecialtyService;
		$subspecialty = $ps->jsonToModel($json, new \Subspecialty);

		$this->verifyNewSpecialty($subspecialty);
	}

	public function testJsonToModel_Save_Create_DBIsCorrect()
	{
		$resource = $this->getNewResource();
		$json = $resource->serialise();

		$ps = new SubspecialtyService;
		$subspecialty = $ps->jsonToModel($json, new \Subspecialty);
		$subspecialty = \Subspecialty::model()->findByPk($subspecialty->id);

		$this->verifyNewSpecialty($subspecialty);
	}

	public function testJsonToModel_Save_Update_Modified_ModelCountsCorrect()
	{
		$resource = $this->getNewResource();
		$json = $resource->serialise();

		$total_s = count(\Subspecialty::model()->findAll());

		$ps = new SubspecialtyService;
		$subspecialty = $ps->jsonToModel($json, $this->subspecialties('subspecialty1'));

		$this->assertEquals($total_s, count(\Subspecialty::model()->findAll()));
	}

	public function testJsonToModel_Save_Update_ModelIsCorrect()
	{
		$resource = $this->getNewResource();
		$json = $resource->serialise();

		$ps = new SubspecialtyService;
		$subspecialty = $ps->jsonToModel($json, $this->subspecialties('subspecialty1'));

		$this->assertEquals(1,$subspecialty->id);

		$this->verifyNewSpecialty($subspecialty);
	}

	public function testJsonToModel_Save_Update_DBIsCorrect()
	{
		$resource = $this->getNewResource();
		$json = $resource->serialise();

		$ps = new SubspecialtyService;
		$subspecialty = $ps->jsonToModel($json, $this->subspecialties('subspecialty1'));
		$subspecialty = \Subspecialty::model()->findByPk($subspecialty->id);

		$this->assertEquals(1,$subspecialty->id);

		$this->verifyNewSpecialty($subspecialty);
	}
}
