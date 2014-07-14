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

class ModelConverter_ModelWrapperTest extends \CDbTestCase
{
	public $fixtures = array(
		'countries' => 'Country',
		'addresses' => 'Address',
	);

	public function setUp()
	{
		$this->map = $this->getMockBuilder('services\ModelMap')
			->disableOriginalConstructor()
			->setMethods(array('getModelDefaultsForClass','getRelatedObjectsForClass','getReferenceObjectForClass'))
			->getMock();
	}

	public function testConstructor_ExtraFields()
	{
		$model = new \stdClass;

		$w = new ModelConverter_ModelWrapper($this->map, $model, array(
				'test' => 'train',
				'testing' => 'worked'
		));

		$this->assertEquals('train',$w->getModel()->test);
		$this->assertEquals('worked',$w->getModel()->testing);
	}

	public function testConstructor_WithRelatedObjectDefinitions()
	{
		$this->map->expects($this->once())
			->method('getRelatedObjectsForClass')
			->with('stdClass')
			->will($this->returnValue('tree'));

		$model = new \stdClass;

		$w = new ModelConverter_ModelWrapper($this->map, $model);

		$this->assertEquals('tree', $w->getRelatedObjectDefinitions());
	}

	public function testConstructor_WithNoRelatedObjectDefinitions()
	{
		$this->map->expects($this->once())
			->method('getRelatedObjectsForClass')
			->with('stdClass')
			->will($this->returnValue(null));

		$model = new \stdClass;

		$w = new ModelConverter_ModelWrapper($this->map, $model);

		$this->assertEquals(array(), $w->getRelatedObjectDefinitions());
	}

	public function testConstructor_SetDefaults()
	{
		$this->map->expects($this->once())
			->method('getRelatedObjectsForClass')
			->with('stdClass');

		$this->map->expects($this->once())
			->method('getModelDefaultsForClass')
			->with('stdClass')
			->will($this->returnValue(array(
					'one' => 'blah',
					'two' => 'blee',
					'three' => 'blar',
			)));

		$model = new \stdClass;

		$w = new ModelConverter_ModelWrapper($this->map, $model);

		$this->assertEquals('blah',$w->getModel()->one);
		$this->assertEquals('blee',$w->getModel()->two);
		$this->assertEquals('blar',$w->getModel()->three);
	}

	public function testGetId()
	{
		$model = new \stdClass;
		$model->id = 'something';

		$w = new ModelConverter_ModelWrapper($this->map, $model);

		$this->assertEquals('something',$w->getId());
	}

	public function testGetClass()
	{
		$model = new \stdClass;
		
		$w = new ModelConverter_ModelWrapper($this->map, $model);
	 
		$this->assertEquals('stdClass',$w->getClass());

		$model = new Address;
		
		$w = new ModelConverter_ModelWrapper($this->map, $model);
	 
		$this->assertEquals('services_Address',$w->getClass());
	}

	public function testGetRelations()
	{
		$address = new \Address;

		$w = new ModelConverter_ModelWrapper($this->map, $address);

		$this->assertEquals($address->relations(), $w->getRelations());
	}

	public function testGetModel()
	{
		$address = new \Address;
		$address->address1 = 'somewhere';

		$w = new ModelConverter_ModelWrapper($this->map, $address);

		$this->assertEquals($address, $w->getModel());
	}

	public function testIsRelatedObject()
	{
		$address = new \Address;

		$this->map->expects($this->once())
			->method('getRelatedObjectsForClass')
			->with('Address')
			->will($this->returnValue(array(
				'related' => 'somethignsometihiofd',
			)));

		$w = new ModelConverter_ModelWrapper($this->map, $address);

		$this->assertTrue($w->isRelatedObject('related'));
		$this->assertFalse($w->isRelatedObject('notrelated'));
	}

	public function testIsRelatedObject_ThroughChildren()
	{
		$address = new \Address;

		$this->map->expects($this->once())
			->method('getRelatedObjectsForClass')
			->with('Address')
			->will($this->returnValue(array(
				'related' => array('children' => array(
					'related2' => array('children' => array(
						'related3' => 'blah'
					))
				))
			)));

		$w = new ModelConverter_ModelWrapper($this->map, $address);

		$this->assertTrue($w->isRelatedObject('related'));
		$this->assertTrue($w->isRelatedObject('related.related2'));
		$this->assertTrue($w->isRelatedObject('related.related2.related3'));
		$this->assertFalse($w->isRelatedObject('notrelated'));
	}

	public function testSave_Errors()
	{
		$address = $this->getMockBuilder('Address')
			->disableOriginalConstructor()
			->setMethods(array('save','getErrors'))
			->getMock();

		$address->expects($this->once())
			->method('save')
			->will($this->returnValue(false));

		$address->expects($this->any())
			->method('getErrors')
			->will($this->returnValue(array(
				'address1' => 'Address1 is required',
			)));

		$w = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->setConstructorArgs(array($this->map,$address))
			->setMethods(array('saveAssignmentRelations'))
			->getMock();

		$this->setExpectedException("Exception","Validation failure on ".get_class($address).": Array\n(\n".str_repeat(' ',4)."[address1] => Address1 is required\n)\n");

		$w->save();
	}

	public function testSave_OK()
	{
		$address = $this->getMockBuilder('Address')
			->disableOriginalConstructor()
			->setMethods(array('save'))
			->getMock();

		$address->expects($this->once())
			->method('save')
			->will($this->returnValue(true));

		$w = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->setConstructorArgs(array($this->map,$address))
			->setMethods(array('saveAssignmentRelations'))
			->getMock();

		$w->expects($this->once())
			->method('saveAssignmentRelations');

		$w->save();
	}

	public function testHasConditionalAttribute()
	{
		$w = new ModelConverter_ModelWrapper($this->map, new \Address);

		$this->assertFalse($w->hasConditionalAttribute('foo'));

		$w->addConditionalAttribute('fee');
		$w->addConditionalAttribute('fii');
		$w->addConditionalAttribute('foo');

		$this->assertTrue($w->hasConditionalAttribute('fee'));
		$this->assertTrue($w->hasConditionalAttribute('fii'));
		$this->assertTrue($w->hasConditionalAttribute('foo'));
		$this->assertFalse($w->hasConditionalAttribute('faa'));
	}

	public function testAddConditionalAttribute()
	{
		$w = new ModelConverter_ModelWrapper($this->map, new \Address);
	 
		$this->assertFalse($w->hasConditionalAttribute('foo'));
		
		$w->addConditionalAttribute('fee');
		$w->addConditionalAttribute('fii');
		$w->addConditionalAttribute('foo');
		
		$this->assertTrue($w->hasConditionalAttribute('fee'));
		$this->assertTrue($w->hasConditionalAttribute('fii'));
		$this->assertTrue($w->hasConditionalAttribute('foo'));
		$this->assertFalse($w->hasConditionalAttribute('faa'));
	}

	public function testSetRelatedObject()
	{
		$w = new ModelConverter_ModelWrapper($this->map, new \Address);

		$w->setRelatedObject('one','two','three');

		$this->assertEquals('three', $w->getRelatedObject('one','two'));
	}

	public function testGetRelatedObject()
	{
		$w = new ModelConverter_ModelWrapper($this->map, new \Address);

		$w->setRelatedObject('one','two','three');

		$this->assertEquals('three', $w->getRelatedObject('one','two'));
	}

	public function testAddToRelatedObjectArray()
	{
		$w = new ModelConverter_ModelWrapper($this->map, new \Address);

		$w->addToRelatedObjectArray('fast','M3','one');
		$w->addToRelatedObjectArray('fast','M3','two');
		$w->addToRelatedObjectArray('fast','M3','nine');

		$this->assertEquals(array('one','two','nine'),$w->getRelatedObject('fast','M3'));
	}

	public function testRelatedObjectCopyAttributeFromModel_NotSet()
	{
		$address = new \Address;
		$address->address1 = 'testing123';

		$w = new ModelConverter_ModelWrapper($this->map, $address);

		$w->relatedObjectCopyAttributeFromModel('fast','M5','address1');

		$this->assertNull($w->getRelatedObject('fast','M5'));
	}

	public function testRelatedObjectCopyAttributeFromModel_NotArray()
	{
		$address = new \Address;
		$address->address1 = 'testing123';

		$w = new ModelConverter_ModelWrapper($this->map, $address);

		$w->setRelatedObject('fast','M5',new \stdClass);
		$w->relatedObjectCopyAttributeFromModel('fast','M5','address1');

		$this->assertEquals('testing123', $w->getRelatedObject('fast','M5')->address1);
	}

	public function testRelatedObjectCopyAttributeFromModel_Array_AttributeNotArray()
	{
		$address = new \Address;
		$address->address1 = 'testing123';
	 
		$w = new ModelConverter_ModelWrapper($this->map, $address);

		$w->setRelatedObject('fast','M5',array(new \stdClass,new \stdClass,new \stdClass));
		$w->relatedObjectCopyAttributeFromModel('fast','M5','address1');

		$related_object = $w->getRelatedObject('fast','M5');

		$this->assertEquals('testing123', $related_object[0]->address1);
		$this->assertEquals('testing123', $related_object[1]->address1);
		$this->assertEquals('testing123', $related_object[2]->address1);
	}

	public function testRelatedObjectCopyAttributeFromModel_Array_AttributeIsArray()
	{
		$address = new \Address;
		$address->address1 = 'testing123';
		$address->address2 = '456test';
		$address->city = 'testytesttest667';
 
		$w = new ModelConverter_ModelWrapper($this->map, $address);

		$w->setRelatedObject('fast','M5',array(new \stdClass,new \stdClass,new \stdClass));
		$w->relatedObjectCopyAttributeFromModel('fast','M5',array('address1'=>'address1','address2'=>'address2','city'=>'city'));

		$related_object = $w->getRelatedObject('fast','M5');

		$this->assertEquals('testing123', $related_object[0]->address1);
		$this->assertEquals('456test', $related_object[0]->address2);
		$this->assertEquals('testytesttest667', $related_object[0]->city);

		$this->assertEquals('testing123', $related_object[1]->address1);
		$this->assertEquals('456test', $related_object[1]->address2);
		$this->assertEquals('testytesttest667', $related_object[1]->city);

		$this->assertEquals('testing123', $related_object[2]->address1);
		$this->assertEquals('456test', $related_object[2]->address2);
		$this->assertEquals('testytesttest667', $related_object[2]->city);
	}

	public function testExpandAttribute()
	{
		$address = new \Address;
		$address->country_id = 1;

		$w = new ModelConverter_ModelWrapper($this->map, $address);

		$this->assertEquals('United States',$w->expandAttribute('country.name'));
	}

	public function testSetAttribute_Force()
	{
		$address = new \Address;
		$address->address1 = 'testing';

		$w = new ModelConverter_ModelWrapper($this->map, $address);

		$w->setAttribute('address1','blahdeblah');

		$this->assertEquals('blahdeblah',$w->getModel()->address1);
	}

	public function testSetAttribute_NoForce_NotSet()
	{
		$address = new \Address;
	 
		$w = new ModelConverter_ModelWrapper($this->map, $address);

		$w->setAttribute('address1','blahdeblah',false);
	 
		$this->assertEquals('blahdeblah',$w->getModel()->address1);
	}

	public function testSetAttribute_NoForce_Set()
	{
		$address = new \Address;
		$address->address1 = 'testing';
 
		$w = new ModelConverter_ModelWrapper($this->map, $address);

		$w->setAttribute('address1','blahdeblah',false);

		$this->assertEquals('testing',$w->getModel()->address1);
	}

	public function testSetAttributes()
	{
		$address = new \Address;

		$w = new ModelConverter_ModelWrapper($this->map, $address);

		$w->setAttributes(array(
			'address1' => 'monitor',
			'address2' => 'fan',
			'city' => 'keyboard',
		));

		$this->assertEquals('monitor',$w->getModel()->address1);
		$this->assertEquals('fan',$w->getModel()->address2);
		$this->assertEquals('keyboard',$w->getModel()->city);
	}

	public function testAddReferenceObjectAttribute()
	{
		$w = new ModelConverter_ModelWrapper($this->map, new \Address);

		$w->addReferenceObjectAttribute('one','two','three');

		$this->assertEquals('three',$w->getReferenceObject('one','two'));
	}

	public function testGetReferenceObject()
	{
		$w = new ModelConverter_ModelWrapper($this->map, new \Address);
	 
		$w->addReferenceObjectAttribute('one','two','three');
	 
		$this->assertEquals('three',$w->getReferenceObject('one','two'));
	}

	public function testHaveAllKeysForReferenceObject_True()
	{
		$this->map->expects($this->once())
			->method('getReferenceObjectForClass')
			->with('Address','relate')
			->will($this->returnValue(array('blah','blah',array('one','two','three'))));

		$w = new ModelConverter_ModelWrapper($this->map, new \Address);

		$w->addReferenceObjectAttribute('relate','one','something');
		$w->addReferenceObjectAttribute('relate','two','something');
		$w->addReferenceObjectAttribute('relate','three','something');

		$this->assertTrue($w->haveAllKeysForReferenceObject('relate'));
	}

	public function testHaveAllKeysForReferenceObject_False()
	{
		$this->map->expects($this->once())
			->method('getReferenceObjectForClass')
			->with('Address','relate')
			->will($this->returnValue(array('blah','blah',array('one','two','three'))));
	 
		$w = new ModelConverter_ModelWrapper($this->map, new \Address);

		$w->addReferenceObjectAttribute('relate','one','something');
		$w->addReferenceObjectAttribute('relate','two','something');
		$w->addReferenceObjectAttribute('relate','threef','something');

		$this->assertFalse($w->haveAllKeysForReferenceObject('relate'));
	}

	public function testAssociateReferenceObjectWithModel_New()
	{
		$this->map->expects($this->once())
			->method('getReferenceObjectForClass')
			->with('Address','country')
			->will($this->returnValue(array('country_id','Address',array('one','two','three'))));

		$w = new ModelConverter_ModelWrapper($this->map, new \Address);

		$w->addReferenceObjectAttribute('country','address1','blah1');
		$w->addReferenceObjectAttribute('country','address2','blah2');
		$w->addReferenceObjectAttribute('country','city','blah3');

		$related_object = $w->associateReferenceObjectWithModel('country');

		$this->assertInstanceOf('Address',$related_object);
		$this->assertEquals('blah1',$related_object->address1);
		$this->assertEquals('blah2',$related_object->address2);
		$this->assertEquals('blah3',$related_object->city);
		$this->assertEquals('',$related_object->id);
	}

	public function testAssociateReferenceObjectWithModel_NotNew()
	{
		$this->map->expects($this->once())
			->method('getReferenceObjectForClass')
			->with('Address','country')
			->will($this->returnValue(array('country_id','Address',array('one','two','three'))));

		$w = new ModelConverter_ModelWrapper($this->map, new \Address);
		
		$address = \Address::model()->findByPk(1);

		$w->addReferenceObjectAttribute('country','address1',$address->address1);
		$w->addReferenceObjectAttribute('country','address2',$address->address2);
		$w->addReferenceObjectAttribute('country','city',$address->city);
		
		$related_object = $w->associateReferenceObjectWithModel('country');
	 
		$this->assertInstanceOf('Address',$related_object);
		$this->assertEquals($address->address1,$related_object->address1);
		$this->assertEquals($address->address2,$related_object->address2);
		$this->assertEquals($address->city,$related_object->city);
		$this->assertEquals(1,$related_object->id);
	}

	public function testHasBelongsToRelation()
	{
		$w = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->disableOriginalConstructor()
			->setMethods(array('getRelations'))
			->getMock();

		$w->expects($this->any())
			->method('getRelations')
			->will($this->returnValue(array('one' => 'blah','two' => 'blee','three' => 'bler')));

		$this->assertTrue($w->hasBelongsToRelation('one'));
		$this->assertTrue($w->hasBelongsToRelation('two'));
		$this->assertTrue($w->hasBelongsToRelation('three'));
		$this->assertFalse($w->hasBelongsToRelation('four'));
	}

	public function testSetAttributeForBelongsToRelation()
	{
		$address = \Address::model()->findByPk(1);

		$w = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->setConstructorArgs(array($this->map,$address))
			->setMethods(array('getRelations','setAttribute'))
			->getMock();
	 
		$w->expects($this->once())
			->method('getRelations')
			->will($this->returnValue(array('country' => array('blah','blah2','blah3'))));

		$w->expects($this->once())
			->method('setAttribute')
			->with('blah3',1);

		$w->setAttributeForBelongsToRelation('country');
	}

	public function testSetReferenceListForRelation()
	{
		$address = \Address::model()->findByPk(1);

		$w = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->setConstructorArgs(array($this->map,$address))
			->setMethods(array('getRelations','setAttribute'))
			->getMock();

		$w->expects($this->once())
			->method('getRelations')
			->will($this->returnValue(array('gp' => array('blah','Patient','gp_id'))));

		$patient1 = new \Patient;
		$patient1->gp_id = 1;
		$patient2 = new \Patient;
		$patient2->gp_id = 2;
		$patient3 = new \Patient;
		$patient3->gp_id = 3;
		$patient4 = new \Patient;
		$patient4->gp_id = 4;

		$w->expects($this->once())
			->method('setAttribute')
			->with('gp',array(
				$patient1,
				$patient2,
				$patient3,
				$patient4
		));

		$w->setReferenceListForRelation('gp','gp_id',array(
			\Yii::app()->service->Patient(1),
			\Yii::app()->service->Patient(2),
			\Yii::app()->service->Patient(3),
			\Yii::app()->service->Patient(4),
		));
	}
}
