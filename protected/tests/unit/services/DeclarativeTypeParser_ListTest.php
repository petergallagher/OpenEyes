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

class DeclarativeTypeParser_ListTest extends \CDbTestCase
{
	public function testModelToResourceParse()
	{
		$data = array(
			(object)array('one'),
			(object)array('two'),
			(object)array('three'),
			(object)array('four')
		);

		$object = (object)array(
			'data' => $data
		);

		$mc = $this->getMockBuilder('services\ModelConverter')
			->disableOriginalConstructor()
			->setMethods(array('modelToResourceParse'))
			->getMock();

		foreach ($data as $i => $item) {
			$mc->expects($this->at($i))
				->method('modelToResourceParse')
				->with($item,'Address',new \services\Address)
				->will($this->returnValue($item));
		}

		$p = new DeclarativeTypeParser_List($mc);
		$this->assertEquals($data, $p->modelToResourceParse($object, 'data', 'Address'));
	}

	public function testResourceToModelParse_WithDot()
	{
		$mc = $this->getMockBuilder('services\ModelConverter')
			->disableOriginalConstructor()
			->setMethods(array('resourceToModel'))
			->getMock();

		$model = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->disableOriginalConstructor()
			->setMethods(array('addToRelatedObjectArray'))
			->getMock();

		$data = array(
			(object)array('one','id'=>null),
			(object)array('two','id'=>null),
			(object)array('three','id'=>null),
			(object)array('four','id'=>null)
		);

		foreach ($data as $i => $item) {
			$mc->expects($this->at($i))
				->method('resourceToModel')
				->with($item, new \Address, false)
				->will($this->returnValue($item));

			$model->expects($this->at($i))
				->method('addToRelatedObjectArray')
				->with('testing','iscool',$item);
		}

		$resource = (object)array(
			'data' => $data
		);

		$p = new DeclarativeTypeParser_List($mc);
		$p->resourceToModelParse($model, $resource, 'testing.iscool', 'data', null, 'Address', false);
	}

	public function testResourceToModelParse_WithoutDot()
	{
		$mc = $this->getMockBuilder('services\ModelConverter')
			->disableOriginalConstructor()
			->setMethods(array('resourceToModel'))
			->getMock();

		$model = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->disableOriginalConstructor()
			->setMethods(array('addToRelatedObjectArray'))
			->getMock();

		$data = array(
			(object)array('one','id'=>null),
			(object)array('two','id'=>null),
			(object)array('three','id'=>null),
			(object)array('four','id'=>null)
		); 
		
		foreach ($data as $i => $item) {
			$mc->expects($this->at($i))
				->method('resourceToModel')
				->with($item, new \Address, false)
				->will($this->returnValue($item));

			$model->expects($this->at($i))
				->method('addToRelatedObjectArray')
				->with('testing',null,$item);
		}
		
		$resource = (object)array(
			'data' => $data
		);

		$p = new DeclarativeTypeParser_List($mc);
		$p->resourceToModelParse($model, $resource, 'testing', 'data', null, 'Address', false);
	}

	public function testResourceToModel_RelatedObjects_WithDot_NoCopy()
	{
		$model = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->disableOriginalConstructor()
			->setMethods(array('relatedObjectCopyAttributeFromModel','setAttribute','expandAttribute','getRelatedObject'))
			->getMock();

		$model->expects($this->never())
			->method('relatedObjectCopyAttributeFromModel');

		$model->expects($this->once())
			->method('setAttribute')
			->with('testing.iscool','one');

		$model->expects($this->once())
			->method('expandAttribute')
			->with('testing')
			->will($this->returnValue('hedge'));

		$model->expects($this->once())
			->method('getRelatedObject')
			->with('testing','iscool')
			->will($this->returnValue('hog'));

		$p = $this->getMockBuilder('services\DeclarativeTypeParser_List')
			->disableOriginalConstructor()
			->setMethods(array('filterListItems'))
			->getMock();

		$p->expects($this->once())
			->method('filterListItems')
			->with('hedge','iscool','hog',null)
			->will($this->returnValue('one'));

		$p->resourceToModel_RelatedObjects($model, 'testing.iscool', false, null);
	}

	public function testResourceToModel_RelatedObjects_WithoutDot_NoCopy()
	{
		$model = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->disableOriginalConstructor()
			->setMethods(array('relatedObjectCopyAttributeFromModel','setAttribute','expandAttribute','getRelatedObject'))
			->getMock();

		$model->expects($this->never())
			->method('relatedObjectCopyAttributeFromModel');

		$model->expects($this->once())
			->method('setAttribute')
			->with('testing','one');

		$model->expects($this->once())
			->method('expandAttribute')
			->with('testing')
			->will($this->returnValue('hedge'));

		$model->expects($this->once())
			->method('getRelatedObject')
			->with('testing',null)
			->will($this->returnValue('hog'));

		$p = $this->getMockBuilder('services\DeclarativeTypeParser_List')
			->disableOriginalConstructor()
			->setMethods(array('filterListItems'))
			->getMock();

		$p->expects($this->once())
			->method('filterListItems')
			->with('hedge',null,'hog',null)
			->will($this->returnValue('one'));

		$p->resourceToModel_RelatedObjects($model, 'testing', false, null);
	}

	public function testResourceToModel_RelatedObjects_WithDot_Copy()
	{
		$model = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->disableOriginalConstructor()
			->setMethods(array('relatedObjectCopyAttributeFromModel','setAttribute','expandAttribute','getRelatedObject'))
			->getMock();

		$model->expects($this->once())
			->method('relatedObjectCopyAttributeFromModel')
			->with('testing','iscool','copyatt');

		$model->expects($this->once())
			->method('setAttribute')
			->with('testing.iscool','one');

		$model->expects($this->once())
			->method('expandAttribute')
			->with('testing')
			->will($this->returnValue('hedge'));

		$model->expects($this->once())
			->method('getRelatedObject')
			->with('testing','iscool')
			->will($this->returnValue('hog'));

		$p = $this->getMockBuilder('services\DeclarativeTypeParser_List')
			->disableOriginalConstructor()
			->setMethods(array('filterListItems'))
			->getMock();

		$p->expects($this->once())
			->method('filterListItems')
			->with('hedge','iscool','hog',null)
			->will($this->returnValue('one'));

		$p->resourceToModel_RelatedObjects($model, 'testing.iscool', 'copyatt', null);
	}

	public function testResourceToModel_RelatedObjects_WithoutDot_Copy()
	{
		$model = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->disableOriginalConstructor()
			->setMethods(array('relatedObjectCopyAttributeFromModel','setAttribute','expandAttribute','getRelatedObject'))
			->getMock();

		$model->expects($this->once())
			->method('relatedObjectCopyAttributeFromModel')
			->with('testing',null,'keyboard');

		$model->expects($this->once())
			->method('setAttribute')
			->with('testing','one');

		$model->expects($this->once())
			->method('expandAttribute')
			->with('testing')
			->will($this->returnValue('hedge'));

		$model->expects($this->once())
			->method('getRelatedObject')
			->with('testing',null)
			->will($this->returnValue('hog'));

		$p = $this->getMockBuilder('services\DeclarativeTypeParser_List')
			->disableOriginalConstructor()
			->setMethods(array('filterListItems'))
			->getMock();

		$p->expects($this->once())
			->method('filterListItems')
			->with('hedge',null,'hog',null)
			->will($this->returnValue('one'));

		$p->resourceToModel_RelatedObjects($model, 'testing', 'keyboard', null);
	}

	public function testResourceToModel_RelatedObjects_Save()
	{
		$model = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->disableOriginalConstructor()
			->setMethods(array('relatedObjectCopyAttributeFromModel','setAttribute','expandAttribute','getRelatedObject'))
			->getMock();

		$model->expects($this->once())
			->method('expandAttribute')
			->with('testing')
			->will($this->returnValue('hedge'));

		$model->expects($this->once())
			->method('getRelatedObject')
			->with('testing',null)
			->will($this->returnValue('hog'));

		$p = $this->getMockBuilder('services\DeclarativeTypeParser_List')
			->disableOriginalConstructor()
			->setMethods(array('filterListItems'))
			->getMock();

		$p->expects($this->once())
			->method('filterListItems')
			->with('hedge',null,'hog',true)
			->will($this->returnValue('one'));

		$p->resourceToModel_RelatedObjects($model, 'testing', 'keyboard', true);
	}

	public function testJsonToResourceParse()
	{
		$mc = $this->getMockBuilder('services\ModelConverter')
			->disableOriginalConstructor()
			->setMethods(array('jsonToResourceParse'))
			->getMock();

		$object = new \stdClass;
		$object->stuff = array(
			'one',
			'two',
			'three'
		);

		$mc->expects($this->at(0))
			->method('jsonToResourceParse')
			->with('one','Rhubarb',new Address)
			->will($this->returnValue('four'));

		$mc->expects($this->at(1))
			->method('jsonToResourceParse')
			->with('two')
			->will($this->returnValue('five'));

		$mc->expects($this->at(2))
			->method('jsonToResourceParse')
			->with('three')
			->will($this->returnValue('six'));

		$p = new DeclarativeTypeParser_List($mc);

		$this->assertEquals(array('four','five','six'), $p->jsonToResourceParse($object, 'stuff', 'Address', 'Rhubarb'));
	}
}
