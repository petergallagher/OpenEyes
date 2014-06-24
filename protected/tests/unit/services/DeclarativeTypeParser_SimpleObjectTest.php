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

class DeclarativeTypeParser_SimpleObjectTest extends \CDbTestCase
{
	public $fixtures = array(
		'patients' => 'Patient',
	);

	public function testModelToResourceParse_Object()
	{
		$mc = $this->getMockBuilder('services\ModelConverter')
			->disableOriginalConstructor()
			->setMethods(array('modelToResource'))
			->getMock();

		$data = (object)array(
			'one' => 'two'
		);

		$mc->expects($this->once())
			->method('modelToResource')
			->with($data, new \services\Address)
			->will($this->returnValue('testedededed'));

		$object = (object)array(
			'attr' => $data
		);

		$p = new DeclarativeTypeParser_SimpleObject($mc);

		$this->assertEquals('testedededed', $p->modelToResourceParse($object, 'attr', 'Address'));
	}

	public function testModelToResourceParse_NotObject()
	{
		$mc = $this->getMockBuilder('services\ModelConverter')
			->disableOriginalConstructor()
			->setMethods(array('modelToResource'))
			->getMock();

		$data = array(
			'one' => 'two'
		);

		$mc->expects($this->never())
			->method('modelToResource');

		$object = (object)array(
			'attr' => $data
		);

		$p = new DeclarativeTypeParser_SimpleObject($mc);

		$this->assertEquals(new \services\Address($data), $p->modelToResourceParse($object, 'attr', 'Address'));
	}

	public function testResourceToModelParse_IsObject()
	{
		$model = $this->getMockBuilder('services\Address')
			->disableOriginalConstructor()
			->setMethods(array('setAttribute'))
			->getMock();

		$model->expects($this->once())
			->method('setAttribute')
			->with('ethernet','sunglasses');

		$a = 1;
		$p = new DeclarativeTypeParser_SimpleObject($a);

		$resource = (object)array(
			'r_att' => new DeclarativeTypeParser_SimpleObject_MockObject
		);

		$p->resourceToModelParse($model, $resource, 'ethernet', 'r_att', null, null, null);
	}

	public function testResourceToModelParse_Null()
	{
		$model = $this->getMockBuilder('services\Address')
			->disableOriginalConstructor()
			->setMethods(array('setAttribute'))
			->getMock();

		$model->expects($this->once())
			->method('setAttribute')
			->with('ethernet',null);

		$a = 1;
		$p = new DeclarativeTypeParser_SimpleObject($a);

		$resource = (object)array(
			'r_att' => '0'
		);

		$p->resourceToModelParse($model, $resource, 'ethernet', 'r_att', null, null, null);
	}

	public function testResourceToModelParse_NotNull()
	{
		$model = $this->getMockBuilder('services\Address')
			->disableOriginalConstructor()
			->setMethods(array('setAttribute'))
			->getMock();

		$model->expects($this->once())
			->method('setAttribute')
			->with('ethernet','sunglasses');

		$a = 1;
		$p = new DeclarativeTypeParser_SimpleObject($a);

		$resource = (object)array(
			'r_att' => array('one' => 'two'),
		);

		$p->resourceToModelParse($model, $resource, 'ethernet', 'r_att', 'DeclarativeTypeParser_SimpleObject_MockObject', null, null);
	}

	public function testJsonToResourceParse_Set()
	{
		$a = 1;
		$p = new DeclarativeTypeParser_SimpleObject($a);

		$object = (object)array(
			'rabbit' => 'testing'
		);

		$this->assertEquals(new DeclarativeTypeParser_SimpleObject_MockObject, $p->jsonToResourceParse($object, 'rabbit', 'DeclarativeTypeParser_SimpleObject_MockObject', null));
	}

	public function testJsonToResourceParse_NotSet()
	{
		$a = 1;
		$p = new DeclarativeTypeParser_SimpleObject($a);

		$object = (object)array(
			'rabbit' => null
		);

		$this->assertNull($p->jsonToResourceParse($object, 'rabbit', 'DeclarativeTypeParser_SimpleObject_MockObject', null));
	}
}

class DeclarativeTypeParser_SimpleObject_MockObject
{
	public function toModelValue()
	{
		return 'sunglasses';
	}

	static public function fromObject($param)
	{
		return new DeclarativeTypeParser_SimpleObject_MockObject;
	}
}
