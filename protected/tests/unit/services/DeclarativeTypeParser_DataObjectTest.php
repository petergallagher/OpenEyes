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

class DeclarativeTypeParser_DataObjectTest extends \CDbTestCase
{
	public function testModelToResourceParse_ObjectDataType()
	{
		$mc = $this->getMockBuilder('services\ModelConverter')
			->disableOriginalConstructor()
			->setMethods(array('expandObjectAttribute','modelToResource'))
			->getMock();

		$object = (object)array(
			'one' => 'legs'
		);

		$mc->expects($this->once())
			->method('expandObjectAttribute')
			->with('pencil','sharpener')
			->will($this->returnValue($object));

		$mc->expects($this->once())
			->method('modelToResource')
			->with($object, new Address)
			->will($this->returnValue('cheddar'));

		$p = new DeclarativeTypeParser_DataObject($mc);

		$this->assertEquals('cheddar',$p->modelToResourceParse('pencil','sharpener','Address'));
	}

	public function testModelToResourceParse_Null()
	{
		$mc = $this->getMockBuilder('services\ModelConverter')
			->disableOriginalConstructor()
			->setMethods(array('expandObjectAttribute','modelToResource'))
			->getMock();

		$mc->expects($this->once())
			->method('expandObjectAttribute')
			->with('pencil','sharpener')
			->will($this->returnValue(null));

		$p = new DeclarativeTypeParser_DataObject($mc);

		$this->assertNull($p->modelToResourceParse('pencil','sharpener','Address'));
	}

	public function testModelToResourceParse_NewInstanceFromArray()
	{
		$mc = $this->getMockBuilder('services\ModelConverter')
			->disableOriginalConstructor()
			->setMethods(array('expandObjectAttribute','modelToResource'))
			->getMock();

		$data = array(
			'line1' => 'bloop',
			'line2' => 'bleep',
		);

		$mc->expects($this->once())
			->method('expandObjectAttribute')
			->with('pencil','sharpener')
			->will($this->returnValue($data));

		$p = new DeclarativeTypeParser_DataObject($mc);

		$this->assertEquals(new Address($data), $p->modelToResourceParse('pencil','sharpener','Address'));
	}

	public function testResourceToModelParse_IsObject()
	{
		$mc = $this->getMockBuilder('services\ModelConverter')
			->disableOriginalConstructor()
			->setMethods(array('resourceToModel'))
			->getMock();

		$mc->expects($this->once())
			->method('resourceToModel')
			->with(new \stdClass, new \Address, false)
			->will($this->returnValue('crane'));

		$model = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->disableOriginalConstructor()
			->setMethods(array('setRelatedObject'))
			->getMock();

		$model->expects($this->once())
			->method('setRelatedObject')
			->with('one','two','crane');

		$p = new DeclarativeTypeParser_DataObject($mc);

		$resource = (object)array(
			'sticks' => new \stdClass
		);

		$p->resourceToModelParse($model, $resource, 'one.two', 'sticks', null, 'Address', false);
	}

	public function testResourceToModelParse_NotObject()
	{
		$mc = $this->getMockBuilder('services\ModelConverter')
			->disableOriginalConstructor()
			->setMethods(array('resourceToModel'))
			->getMock();

		$model = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->disableOriginalConstructor()
			->setMethods(array('setRelatedObject'))
			->getMock();

		$model->expects($this->once())
			->method('setRelatedObject')
			->with('one','two',null);

		$p = new DeclarativeTypeParser_DataObject($mc);

		$resource = (object)array(
			'sticks' => 'test'
		);

		$p->resourceToModelParse($model, $resource, 'one.two', 'sticks', null, 'Address', false);
	}

	public function testResourceToModelParse_UnhandledAttributeType()
	{
		$this->setExpectedException('Exception','Unhandled');
		$a = 1;
		$p = new DeclarativeTypeParser_DataObject($a);
		$p->resourceToModelParse($a, null, 'one', 'sticks', null, 'Address', false);
	}

	public function testResourceToModel_RelatedObjects_CopyAttribute()
	{
		$model = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->disableOriginalConstructor()
			->setMethods(array('relatedObjectCopyAttributeFromModel','expandAttribute'))
			->getMock();

		$model->expects($this->once())
			->method('relatedObjectCopyAttributeFromModel')
			->with('bodger','badger','away');

		$p = new DeclarativeTypeParser_DataObject($model);
		$p->resourceToModel_RelatedObjects($model, 'bodger.badger', 'away', null);
	}

	public function testResourceToModel_RelatedObjects_NoCopyAttribute()
	{
		$model = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->disableOriginalConstructor()
			->setMethods(array('relatedObjectCopyAttributeFromModel','expandAttribute'))
			->getMock();

		$model->expects($this->never())
			->method('relatedObjectCopyAttributeFromModel');

		$p = new DeclarativeTypeParser_DataObject($model);
		$p->resourceToModel_RelatedObjects($model, 'bodger.badger', false, null);
	}

	public function testResourceToModel_RelatedObjects_UnhandledAttributeType()
	{
		$a = 1;
		$this->setExpectedException('Exception','Unhandled');
		$p = new DeclarativeTypeParser_DataObject($a);
		$p->resourceToModel_RelatedObjects($a, 'bodger', false, null);
	}

	public function testResourceToModel_RelatedObjects_False_True_NoSetAttribute()
	{
		$model = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->disableOriginalConstructor()
			->setMethods(array('relatedObjectCopyAttributeFromModel', 'expandAttribute', 'getRelatedObject', 'setAttribute'))
			->getMock();

		$model->expects($this->once())
			->method('expandAttribute')
			->with('bodger')
			->will($this->returnValue(false));

		$model->expects($this->any())
			->method('getRelatedObject')
			->with('bodger','badger')
			->will($this->returnValue(true));

		$model->expects($this->never()) 
			->method('setAttribute');

		$mc = $this->getMockBuilder('services\ModelConverter')
			->disableOriginalConstructor()
			->setMethods(array('expandObjectAttribute','modelToResource'))
			->getMock();

		$mc->expects($this->never())
			->method('saveModel');

		$p = new DeclarativeTypeParser_DataObject($mc);
		$p->resourceToModel_RelatedObjects($model, 'bodger.badger', 'away', null);
	}

	public function testResourceToModel_RelatedObjects_True_False_NoSetAttribute()
	{
		$model = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->disableOriginalConstructor()
			->setMethods(array('relatedObjectCopyAttributeFromModel', 'expandAttribute', 'getRelatedObject', 'setAttribute'))
			->getMock();

		$model->expects($this->once())
			->method('expandAttribute')
			->with('bodger')
			->will($this->returnValue(true));

		$model->expects($this->once())
			->method('getRelatedObject')
			->with('bodger','badger')
			->will($this->returnValue(false));

		$model->expects($this->never())
			->method('setAttribute');

		$mc = $this->getMockBuilder('services\ModelConverter')
			->disableOriginalConstructor()
			->setMethods(array('expandObjectAttribute','modelToResource'))
			->getMock();

		$mc->expects($this->never())
			->method('saveModel');

		$p = new DeclarativeTypeParser_DataObject($mc);
		$p->resourceToModel_RelatedObjects($model, 'bodger.badger', 'away', null);
	}

	public function testResourceToModel_RelatedObjects_BothTrue_SetAttribute()
	{
		$model = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->disableOriginalConstructor()
			->setMethods(array('relatedObjectCopyAttributeFromModel', 'expandAttribute', 'getRelatedObject', 'setAttribute'))
			->getMock();

		$model->expects($this->once())
			->method('expandAttribute')
			->with('bodger')
			->will($this->returnValue(true));

		$model->expects($this->exactly(2))
			->method('getRelatedObject')
			->with('bodger','badger')
			->will($this->returnValue('never far away'));

		$model->expects($this->once()) 
			->method('setAttribute')
			->with('bodger.badger','never far away');

		$mc = $this->getMockBuilder('services\ModelConverter')
			->disableOriginalConstructor()
			->setMethods(array('expandObjectAttribute','modelToResource'))
			->getMock();

		$mc->expects($this->never())
			->method('saveModel');

		$p = new DeclarativeTypeParser_DataObject($mc);
		$p->resourceToModel_RelatedObjects($model, 'bodger.badger', 'away', null);
	}

	public function testResourceToModel_RelatedObjects_BothTrue_Save()
	{
		$model = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->disableOriginalConstructor()
			->setMethods(array('relatedObjectCopyAttributeFromModel', 'expandAttribute', 'getRelatedObject', 'setAttribute'))
			->getMock();

		$model->expects($this->any())
			->method('expandAttribute')
			->will($this->returnValue(new \Address));

		$model->expects($this->exactly(2))
			->method('getRelatedObject')
			->with('bodger','badger')
			->will($this->returnValue('never far away'));

		$model->expects($this->once())
			->method('setAttribute')
			->with('bodger.badger','never far away');

		$mc = $this->getMockBuilder('services\ModelConverter')
			->disableOriginalConstructor()
			->setMethods(array('expandObjectAttribute','modelToResource','saveModel'))
			->getMock();

		$mc->expects($this->once())
			->method('saveModel')
			->with(new \Address);

		$p = new DeclarativeTypeParser_DataObject($mc);
		$p->resourceToModel_RelatedObjects($model, 'bodger.badger', 'away', true);
	}

	public function testJsonToResourceParse_HasData()
	{
		$a = 1;
		$p = new DeclarativeTypeParser_DataObject($a);

		$obj = (object)array(
			'one' => 'two'
		);

		$this->assertEquals('three', $p->jsonToResourceParse($obj, 'one', 'testJsonToResourceParse_HasData_DataClass', null));
	}

	public function testJsonToResourceParse_HasNoData()
	{
		$a = 1;
		$p = new DeclarativeTypeParser_DataObject($a);

		$obj = (object)array(
			'one' => null
		);

		$this->assertNull($p->jsonToResourceParse($obj, 'one', 'test', 'test'));
	}
}

class testJsonToResourceParse_HasData_DataClass
{
	static public function fromObject($param)
	{
		return 'three';
	}
}
