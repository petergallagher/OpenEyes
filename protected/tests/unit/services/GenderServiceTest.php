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

class GenderServiceTest extends \CDbTestCase
{
	public function tearDown()
	{
		\Yii::app()->db->createCommand("update gender set name = 'Male' where id = 1")->query();
	}

	public function testModelToResource()
	{
		$gender = \Gender::model()->find('name=?',array('Female'));

		$gs = new GenderService;

		$resource = $gs->modelToResource($gender);

		$this->assertInstanceOf('services\Gender',$resource);
		$this->assertEquals('Female',$resource->name);
	}

	public function getResource()
	{
		$resource = new Gender;
		$resource->name = 'Female';

		return $resource;
	}

	public function testResourceToModel_NoSave_NoNewRecords()
	{
		$resource = $this->getResource();

		$total_genders = count(\Gender::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());

		$gs = new GenderService;
		$gender = $gs->resourceToModel($resource, new \Gender, false);

		$this->assertEquals($total_genders, count(\Gender::model()->findAll()));
		$this->assertEquals($total_contacts, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses, count(\Address::model()->findAll()));
	}

	public function testResourceToModel_NoSave_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$gs = new GenderService;
		$gender = $gs->resourceToModel($resource, new \Gender, false);

		$this->assertInstanceOf('\Gender',$gender);
		$this->assertEquals('Female',$gender->name);
	}

	public function testResourceToModel_Save_Create_ModelCountsCorrect()
	{
		$resource = $this->getResource();

		$total_genders = count(\Gender::model()->findAll());

		$gs = new GenderService;
		$gender = $gs->resourceToModel($resource, new \Gender);

		$this->assertEquals($total_genders+1, count(\Gender::model()->findAll()));
	}

	public function testResourceToModel_Save_Create_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$gs = new GenderService;
		$gender = $gs->resourceToModel($resource, new \Gender);

		$this->assertInstanceOf('\Gender',$gender);
		$this->assertEquals('Female',$gender->name);
	}

	public function testResourceToModel_Save_Create_DBIsCorrect()
	{
		$resource = $this->getResource();

		$gs = new GenderService;
		$gender = $gs->resourceToModel($resource, new \Gender);
		$gender = \Gender::model()->findByPk($gender->id);

		$this->assertInstanceOf('\Gender',$gender);
		$this->assertEquals('Female',$gender->name);
	}

	public function getModifiedResource()
	{
		$resource = new Gender;
		$resource->name = 'Test';

		return $resource;
	}

	public function testResourceToModel_Save_Update_ModelCountsCorrect()
	{
		$resource = $this->getModifiedResource();
		$model = \Gender::model()->findByPk(1);

		$total_genders = count(\Gender::model()->findAll());

		$gs = new GenderService;
		$gender = $gs->resourceToModel($resource, $model);

		$this->assertEquals($total_genders, count(\Gender::model()->findAll()));
	}

	public function testResourceToModel_Save_Update_ModelIsCorrect()
	{
		$resource = $this->getModifiedResource();
		$model = \Gender::model()->findByPk(1);

		$gs = new GenderService;
		$gender = $gs->resourceToModel($resource, $model);

		$this->assertInstanceOf('\Gender',$gender);
		$this->assertEquals('Test',$gender->name);
	}

	public function testResourceToModel_Save_Update_DBIsCorrect()
	{
		$resource = $this->getModifiedResource();
		$model = \Gender::model()->findByPk(1);

		$gs = new GenderService;
		$gender = $gs->resourceToModel($resource, $model);
		$gender = \Gender::model()->findByPk(1);

		$this->assertInstanceOf('\Gender',$gender);
		$this->assertEquals('Test',$gender->name);
	}

	public function testJsonToResource()
	{
		$json = '{"name":"Male"}';

		$gs = new GenderService;
		$resource = $gs->jsonToResource($json);

		$this->assertInstanceOf('services\Gender',$resource);
		$this->assertEquals('Male',$resource->name);
	}

	public function testJsonToModel_NoSave_NoNewRows()
	{
		$json = '{"name":"Male"}';

		$total_genders = count(\Gender::model()->findAll());
		$total_contacts = count(\Contact::model()->findAll());
		$total_addresses = count(\Address::model()->findAll());

		$gs = new GenderService;
		$gender = $gs->jsonToModel($json, new \Gender, false);

		$this->assertEquals($total_genders, count(\Gender::model()->findAll()));
		$this->assertEquals($total_contacts, count(\Contact::model()->findAll()));
		$this->assertEquals($total_addresses, count(\Address::model()->findAll()));
	}

	public function testJsonToModel_NoSave_ModelIsCorrect()
	{
		$json = '{"name":"Male"}';

		$gs = new GenderService;
		$gender = $gs->jsonToModel($json, new \Gender, false);

		$this->assertInstanceOf('\Gender',$gender);
		$this->assertEquals('Male',$gender->name);
	}

	public function testJsonToModel_Save_Create_ModelCountsCorrect()
	{
		$json = '{"name":"Test"}';

		$total_genders = count(\Gender::model()->findAll());

		$gs = new GenderService;
		$gender = $gs->jsonToModel($json, new \Gender);

		$this->assertEquals($total_genders+1, count(\Gender::model()->findAll()));
	}

	public function testJsonToModel_Save_Create_ModelIsCorrect()
	{
		$json = '{"name":"Test"}';

		$gs = new GenderService;
		$gender = $gs->jsonToModel($json, new \Gender);

		$this->assertInstanceOf('\Gender',$gender);
		$this->assertEquals('Test',$gender->name);
	}

	public function testJsonToModel_Save_Create_DBIsCorrect()
	{
		$json = '{"name":"Test"}';

		$gs = new GenderService;
		$gender = $gs->jsonToModel($json, new \Gender);
		$gender = \Gender::model()->findByPk($gender->id);

		$this->assertInstanceOf('\Gender',$gender);
		$this->assertEquals('Test',$gender->name);
	}

	public function testJsonToModel_Save_Update_ModelCountsCorrect()
	{
		$json = '{"name":"Test"}';

		$total_genders = count(\Gender::model()->findAll());

		$model = \Gender::model()->findByPk(1);

		$gs = new GenderService;
		$gender = $gs->jsonToModel($json, $model);
		$gender = \Gender::model()->findByPk($gender->id);

		$this->assertEquals($total_genders, count(\Gender::model()->findAll()));
	}

	public function testJsonToModel_Save_Update_ModelIsCorrect()
	{
		$json = '{"name":"Test"}';

		$model = \Gender::model()->findByPk(1);

		$gs = new GenderService;
		$gender = $gs->jsonToModel($json, $model);

		$this->assertInstanceOf('\Gender',$gender);
		$this->assertEquals('Test',$gender->name);
	}

	public function testJsonToModel_Save_Update_DBIsCorrect()
	{
		$json = '{"name":"Test"}';

		$model = \Gender::model()->findByPk(1);

		$gs = new GenderService;
		$gender = $gs->jsonToModel($json, $model);
		$gender = \Gender::model()->findByPk($gender->id);

		$this->assertInstanceOf('\Gender',$gender);
		$this->assertEquals('Test',$gender->name);
	}
}
