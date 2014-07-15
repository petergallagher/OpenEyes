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

class ServiceServiceTest extends \CDbTestCase
{
	public $fixtures = array(
		'services' => 'Service',
	);

	public function testModelToResource()
	{
		$option = $this->services('service1');

		$ps = new ServiceService;

		$resource = $ps->modelToResource($option);

		$this->verifyResource($resource, $option);
	}

	public function verifyResource($resource, $option)
	{
		foreach (array('name') as $field) {
			$this->assertEquals($this->services('service1')->$field,$resource->$field);
		}
	}

	public function getResource()
	{
		return \Yii::app()->service->Service(1)->fetch();
	}

	public function testResourceToModel_NoSave_NoNewRecords()
	{
		$resource = $this->getResource();

		$t = count(\Service::model()->findAll());

		$ps = new ServiceService;
		$option = $ps->resourceToModel($resource, new \Service, false);

		$this->assertEquals($t, count(\Service::model()->findAll()));
	}

	public function testResourceToModel_NoSave_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new ServiceService;
		$option = $ps->resourceToModel($resource, new \Service, false);

		$this->verifyOption($option, $resource);
	}

	public function verifyOption($option, $resource, $keys=array())
	{
		$this->assertInstanceOf('Service',$option);

		foreach (array('name') as $field) {
			$this->assertEquals($resource->$field,$option->$field);
		}
	}

	public function getNewResource()
	{
		$resource = $this->getResource();

		$resource->name = 'wabwabwab';

		return $resource;
	}

	public function testResourceToModel_Save_Create_ModelCountsCorrect()
	{
		$resource = $this->getNewResource();

		$t = count(\Service::model()->findAll());

		$ps = new ServiceService;
		$option = $ps->resourceToModel($resource, new \Service);

		$this->assertEquals($t+1, count(\Service::model()->findAll()));
	}

	public function verifyNewOption($option)
	{
		$this->assertInstanceOf('Service',$option);

		$this->assertEquals('wabwabwab',$option->name);
	}

	public function testResourceToModel_Save_Create_ModelIsCorrect()
	{
		$resource = $this->getNewResource();

		$ps = new ServiceService;
		$option = $ps->resourceToModel($resource, new \Service);

		$this->verifyNewOption($option);
	}

	public function testResourceToModel_Save_Create_DBIsCorrect()
	{
		$resource = $this->getNewResource();

		$ps = new ServiceService;
		$option = $ps->resourceToModel($resource, new \Service);
		$option = \Service::model()->findByPk($option->id);

		$this->verifyNewOption($option);
	}

	public function testResourceToModel_Save_Update_Modified_ModelCountsCorrect()
	{
		$resource = $this->getNewResource();

		$ts = count(\Service::model()->findAll());
		$tb = count(\OphTrOperationbooking_Operation_Booking::model()->findAll());

		$ps = new ServiceService;
		$option = $ps->resourceToModel($resource, $this->services('service1'));

		$this->assertEquals($ts, count(\Service::model()->findAll()));
		$this->assertEquals($tb, count(\OphTrOperationbooking_Operation_Booking::model()->findAll()));
	}

	public function testResourceToModel_Save_Update_ModelIsCorrect()
	{
		$resource = $this->getNewResource();

		$ps = new ServiceService;
		$option = $ps->resourceToModel($resource, $this->services('service1'));

		$this->assertEquals(1,$option->id);

		$this->verifyNewOption($option);
	}

	public function testResourceToModel_Save_Update_DBIsCorrect()
	{
		$resource = $this->getNewResource();

		$ps = new ServiceService;
		$option = $ps->resourceToModel($resource, $this->services('service1'));
		$option = \Service::model()->findByPk($option->id);

		$this->assertEquals(1,$option->id);

		$this->verifyNewOption($option);
	}

	public function testJsonToResource()
	{
		$option = $this->services('service1');
		$json = \Yii::app()->service->Service($option->id)->fetch()->serialise();

		$ps = new ServiceService;

		$resource = $ps->jsonToResource($json);

		$this->verifyResource($resource, $option);
	}

	public function testJsonToModel_NoSave_NoNewRecords()
	{
		$resource = $this->getResource();
		$json = $resource->serialise();

		$total_s = count(\Service::model()->findAll());

		$ps = new ServiceService;
		$option = $ps->jsonToModel($json, new \Service, false);

		$this->assertEquals($total_s, count(\Service::model()->findAll()));
	}

	public function testJsonToModel_NoSave_ModelIsCorrect()
	{
		$resource = $this->getResource();
		$json = $resource->serialise();

		$ps = new ServiceService;
		$option = $ps->jsonToModel($json, new \Service, false);

		$this->verifyOption($option, $resource);
	}

	public function testJsonToModel_Save_Create_ModelCountsCorrect()
	{
		$resource = $this->getNewResource();
		$json = $resource->serialise();

		$total_s = count(\Service::model()->findAll());

		$ps = new ServiceService;
		$option = $ps->jsonToModel($json, new \Service);

		$this->assertEquals($total_s+1, count(\Service::model()->findAll()));
	}

	public function testJsonToModel_Save_Create_ModelIsCorrect()
	{
		$resource = $this->getNewResource();
		$json = $resource->serialise();

		$ps = new ServiceService;
		$option = $ps->jsonToModel($json, new \Service);

		$this->verifyNewOption($option);
	}

	public function testJsonToModel_Save_Create_DBIsCorrect()
	{
		$resource = $this->getNewResource();
		$json = $resource->serialise();

		$ps = new ServiceService;
		$option = $ps->jsonToModel($json, new \Service);
		$option = \Service::model()->findByPk($option->id);

		$this->verifyNewOption($option);
	}

	public function testJsonToModel_Save_Update_Modified_ModelCountsCorrect()
	{
		$resource = $this->getNewResource();
		$json = $resource->serialise();

		$total_s = count(\Service::model()->findAll());

		$ps = new ServiceService;
		$option = $ps->jsonToModel($json, $this->services('service1'));

		$this->assertEquals($total_s, count(\Service::model()->findAll()));
	}

	public function testJsonToModel_Save_Update_ModelIsCorrect()
	{
		$resource = $this->getNewResource();
		$json = $resource->serialise();

		$ps = new ServiceService;
		$option = $ps->jsonToModel($json, $this->services('service1'));

		$this->assertEquals(1,$option->id);

		$this->verifyNewOption($option);
	}

	public function testJsonToModel_Save_Update_DBIsCorrect()
	{
		$resource = $this->getNewResource();
		$json = $resource->serialise();

		$ps = new ServiceService;
		$option = $ps->jsonToModel($json, $this->services('service1'));
		$option = \Service::model()->findByPk($option->id);

		$this->assertEquals(1,$option->id);

		$this->verifyNewOption($option);
	}
}
