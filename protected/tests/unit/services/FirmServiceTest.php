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

class FirmServiceTest extends \CDbTestCase
{
	public $fixtures = array(
		'firms' => 'Firm',
		'subspecialties' => 'Subspecialty',
	);

	public function testModelToResource()
	{
		$firm = $this->firms('firm1');

		$gs = new FirmService;

		$resource = $gs->modelToResource($firm);

		$this->verifyResource($resource, $firm);
	}

	public function verifyResource($resource, $firm)
	{
		$this->assertInstanceOf('services\Firm',$resource);
		$this->assertEquals($firm->id,$resource->getId());

		foreach (array('name','pas_code','active') as $field) {
			$this->assertEquals($firm->$field,$resource->$field);
		}

		$this->assertEquals($firm->serviceSubspecialtyAssignment->subspecialty->name,$resource->subspecialty);
		$this->assertInstanceOf('services\UserReference',$resource->consultant_ref);
		$this->assertEquals($firm->consultant_id,$resource->consultant_ref->getId());
	}

	public function getResource()
	{
		$resource = new Firm;
		$resource->name = 'New firm';
		$resource->subspecialty = 'Subspecialty 2';
		$resource->pas_code = 'BLOB';
		$resource->active = 1;
		$resource->consultant_ref = \Yii::app()->service->User(2);

		return $resource;
	}

	public function testResourceToModel_NoSave_NoNewRecords()
	{
		$resource = $this->getResource();

		$f = count(\Firm::model()->findAll());

		$gs = new FirmService;
		$firm = $gs->resourceToModel($resource, new \Firm, false);

		$this->assertEquals($f, count(\Firm::model()->findAll()));
	}

	public function testResourceToModel_NoSave_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$gs = new FirmService;
		$firm = $gs->resourceToModel($resource, new \Firm, false);

		$this->verifyFirm($firm);
	}

	public function verifyFirm($firm)
	{
		$this->assertInstanceOf('\Firm',$firm);
		$this->assertEquals('New firm',$firm->name);
		$this->assertEquals(\Subspecialty::model()->find('name=?',array('Subspecialty 2'))->serviceSubspecialtyAssignment->id,$firm->service_subspecialty_assignment_id);
		$this->assertEquals('BLOB',$firm->pas_code);
		$this->assertEquals(1,$firm->active);
		$this->assertEquals(2,$firm->consultant_id);
	}

	public function testResourceToModel_Save_Create_ModelCountsCorrect()
	{
		$resource = $this->getResource();

		$total_firms = count(\Firm::model()->findAll());

		$gs = new FirmService;
		$firm = $gs->resourceToModel($resource, new \Firm);

		$this->assertEquals($total_firms+1, count(\Firm::model()->findAll()));
	}

	public function testResourceToModel_Save_Create_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$gs = new FirmService;
		$firm = $gs->resourceToModel($resource, new \Firm);

		$this->verifyFirm($firm);
	}

	public function testResourceToModel_Save_Create_DBIsCorrect()
	{
		$resource = $this->getResource();

		$gs = new FirmService;
		$firm = $gs->resourceToModel($resource, new \Firm);
		$firm = \Firm::model()->findByPk($firm->id);

		$this->verifyFirm($firm);
	}

	public function testResourceToModel_Save_Update_ModelCountsCorrect()
	{
		$resource = $this->getResource();
		$model = \Firm::model()->findByPk(1);

		$f = count(\Firm::model()->findAll());

		$gs = new FirmService;
		$firm = $gs->resourceToModel($resource, $model);

		$this->assertEquals($f, count(\Firm::model()->findAll()));
	}

	public function testResourceToModel_Save_Update_ModelIsCorrect()
	{
		$resource = $this->getResource();
		$model = \Firm::model()->findByPk(1);

		$gs = new FirmService;
		$firm = $gs->resourceToModel($resource, $model);

		$this->assertInstanceOf('\Firm',$firm);
		$this->assertEquals(1,$firm->id);

		$this->verifyFirm($firm);
	}

	public function testResourceToModel_Save_Update_DBIsCorrect()
	{
		$resource = $this->getResource();
		$model = \Firm::model()->findByPk(1);

		$gs = new FirmService;
		$firm = $gs->resourceToModel($resource, $model);
		$firm = \Firm::model()->findByPk($firm->id);

		$this->assertInstanceOf('\Firm',$firm);
		$this->assertEquals(1,$firm->id);

		$this->verifyFirm($firm);
	}

	public function testJsonToResource()
	{
		$firm = $this->firms('firm1');
		$json = \Yii::app()->service->Firm($firm->id)->fetch()->serialise();

		$gs = new FirmService;
		$resource = $gs->jsonToResource($json);

		$this->verifyResource($resource, $firm);
	}

	public function testJsonToModel_NoSave_NoNewRows()
	{
		$json = $this->getResource()->serialise();

		$f = count(\Firm::model()->findAll());

		$gs = new FirmService;
		$firm = $gs->jsonToModel($json, new \Firm, false);

		$this->assertEquals($f, count(\Firm::model()->findAll()));
	}

	public function testJsonToModel_NoSave_ModelIsCorrect()
	{
		$json = $this->getResource()->serialise();

		$gs = new FirmService;
		$firm = $gs->jsonToModel($json, new \Firm, false);

		$this->verifyFirm($firm);
	}

	public function testJsonToModel_Save_Create_ModelCountsCorrect()
	{
		$json = $this->getResource()->serialise();

		$f = count(\Firm::model()->findAll());

		$gs = new FirmService;
		$firm = $gs->jsonToModel($json, new \Firm);

		$this->assertEquals($f+1, count(\Firm::model()->findAll()));
	}

	public function testJsonToModel_Save_Create_ModelIsCorrect()
	{
		$json = $this->getResource()->serialise();

		$gs = new FirmService;
		$firm = $gs->jsonToModel($json, new \Firm);

		$this->verifyFirm($firm);
	}

	public function testJsonToModel_Save_Create_DBIsCorrect()
	{
		$json = $this->getResource()->serialise();

		$gs = new FirmService;
		$firm = $gs->jsonToModel($json, new \Firm);
		$firm = \Firm::model()->findByPk($firm->id);

		$this->verifyFirm($firm);
	}

	public function testJsonToModel_Save_Update_ModelCountsCorrect()
	{
		$json = $this->getResource()->serialise();

		$f = count(\Firm::model()->findAll());

		$gs = new FirmService;
		$firm = $gs->jsonToModel($json, $this->firms('firm1'));

		$this->assertEquals($f, count(\Firm::model()->findAll()));
	}

	public function testJsonToModel_Save_Update_ModelIsCorrect()
	{
		$json = $this->getResource()->serialise();

		$gs = new FirmService;
		$firm = $gs->jsonToModel($json, $this->firms('firm1'));

		$this->verifyFirm($firm);
	}

	public function testJsonToModel_Save_Update_DBIsCorrect()
	{
		$json = $this->getResource()->serialise();

		$gs = new FirmService;
		$firm = $gs->jsonToModel($json, $this->firms('firm1'));
		$firm = \Firm::model()->findByPk($firm->id);

		$this->verifyFirm($firm);
	}
}
