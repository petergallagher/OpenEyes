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

class DeclarativeTypeParser_ElementsTest extends \CDbTestCase
{
	public $fixtures = array(
		'sites' => 'Site',
		'institutions' => 'Institution',
		'countries' => 'Country',
	);

	public function testModelToResourceParse_Fields()
	{
		$element = new DeclarativeTypeParser_ElementsTest_ModelClass;
		$element->address1 = 'test1';
		$element->address2 = 'test2';

		$_element = new DeclarativeTypeParser_ElementsTest_ElementClass;
		$_element->address1 = 'test1';
		$_element->address2 = 'test2';

		$object = (object)array(
			'data' => array($element)
		);

		$p = $this->getMockBuilder('services\DeclarativeTypeParser_Elements')
			->disableOriginalConstructor()
			->setMethods(array('createResourceObjectFromModel'))
			->getMock();

		$p->expects($this->once())
			->method('createResourceObjectFromModel')
			->will($this->returnValue(new DeclarativeTypeParser_ElementsTest_ElementClass));

		$this->assertEquals(array($_element), $p->modelToResourceParse($object, 'data', null));
	}

	public function testModelToResourceParse_Relations_BelongsTo()
	{
		$element = new DeclarativeTypeParser_ElementsTest_Model2Class;
		$element->country_id = 1;

		$_element = new DeclarativeTypeParser_ElementsTest_Element2Class;
		$_element->country = 'United States';

		$object = (object)array(
			'data' => array($element)
		);

		$p = $this->getMockBuilder('services\DeclarativeTypeParser_Elements')
			->disableOriginalConstructor()
			->setMethods(array('createResourceObjectFromModel'))
			->getMock();

		$p->expects($this->once())
			->method('createResourceObjectFromModel')
			->will($this->returnValue(new DeclarativeTypeParser_ElementsTest_Element2Class));

		$this->assertEquals(array($_element), $p->modelToResourceParse($object, 'data', null));
	}

	public function testModelToResourceParse_Relations_HasMany()
	{
		$element = new DeclarativeTypeParser_ElementsTest_Model3Class;
		$element->sites = array(
			$this->sites('site1'),
			$this->sites('site2'),
		);

		$_element = new DeclarativeTypeParser_ElementsTest_Element3Class;
		$_element->sites = array(
			\Yii::app()->service->Site(1),
			\Yii::app()->service->Site(2),
		);

		$object = (object)array(
			'data' => array($element)
		);

		$p = $this->getMockBuilder('services\DeclarativeTypeParser_Elements')
			->disableOriginalConstructor()
			->setMethods(array('createResourceObjectFromModel'))
			->getMock();

		$p->expects($this->once())
			->method('createResourceObjectFromModel')
			->will($this->returnValue(new DeclarativeTypeParser_ElementsTest_Element3Class));

		$this->assertEquals(array($_element), $p->modelToResourceParse($object, 'data', null));
	}

	public function testModelToResourceParse_Relations_UnhandledRelationType()
	{
		$element = new DeclarativeTypeParser_ElementsTest_Model4Class;
		$element->sites = array(
			$this->sites('site1'),
			$this->sites('site2'),
		);

		$_element = new DeclarativeTypeParser_ElementsTest_Element4Class;
		$_element->sites = array(
			\Yii::app()->service->Site(1),
			\Yii::app()->service->Site(2),
		);

		$object = (object)array(
			'data' => array($element)
		);

		$p = $this->getMockBuilder('services\DeclarativeTypeParser_Elements')
			->disableOriginalConstructor()
			->setMethods(array('createResourceObjectFromModel'))
			->getMock();

		$p->expects($this->once())
			->method('createResourceObjectFromModel')
			->will($this->returnValue(new DeclarativeTypeParser_ElementsTest_Element4Class));

		$this->setExpectedException('Exception','Unhandled relation type: CManyManyRelation');

		$this->assertEquals(array($_element), $p->modelToResourceParse($object, 'data', null));
	}

	public function testModelToResourceParse_RelationFields()
	{
		$element = new DeclarativeTypeParser_ElementsTest_Model5Class;
		$element->site = \Site::model()->findByPk(1);

		$_element = new DeclarativeTypeParser_ElementsTest_Element5Class;
		$_element->name = 'City Road';
		$_element->short_name = 'City Road';

		$object = (object)array(
			'data' => array($element)
		);

		$p = $this->getMockBuilder('services\DeclarativeTypeParser_Elements')
			->disableOriginalConstructor()
			->setMethods(array('createResourceObjectFromModel'))
			->getMock();

		$p->expects($this->once())
			->method('createResourceObjectFromModel')
			->will($this->returnValue(new DeclarativeTypeParser_ElementsTest_Element5Class));

		$this->assertEquals(array($_element), $p->modelToResourceParse($object, 'data', null));
	} 

	public function testModelToResourceParse_References()
	{
		$element = new DeclarativeTypeParser_ElementsTest_Model6Class;
		$element->site_id = 1;

		$_element = new DeclarativeTypeParser_ElementsTest_Element6Class;
		$_element->site_ref = \Yii::app()->service->Site(1);

		$object = (object)array(
			'data' => array($element)
		);

		$p = $this->getMockBuilder('services\DeclarativeTypeParser_Elements')
			->disableOriginalConstructor()
			->setMethods(array('createResourceObjectFromModel'))
			->getMock();

		$p->expects($this->once())
			->method('createResourceObjectFromModel')
			->will($this->returnValue(new DeclarativeTypeParser_ElementsTest_Element6Class));

		$this->assertEquals(array($_element), $p->modelToResourceParse($object, 'data', null));
	}

	public function testModelToResourceParse_FieldValue_Date()
	{
		$a = 1;
		$p = new DeclarativeTypeParser_Elements($a);
		$value = $p->modelToResourceParse_FieldValue('2012-01-01');
		$this->assertInstanceOf('services\Date',$value);
	}

	public function testModelToResourceParse_FieldValue_DateTime()
	{
		$a = 1;
		$p = new DeclarativeTypeParser_Elements($a);
		$value = $p->modelToResourceParse_FieldValue('2012-01-01 12:00:00');
		$this->assertInstanceOf('services\DateTime',$value);
	}

	public function testModelToResourceParse_FieldValue_ReturnValue()
	{
		$a = 1;
		$p = new DeclarativeTypeParser_Elements($a);
		$value = $p->modelToResourceParse_FieldValue('test');
		$this->assertEquals('test',$value);
	}

	public function testModelToResourceParse_Relation_BelongsTo_Set()
	{
		$a = 1;
		$p = new DeclarativeTypeParser_Elements($a);

		$obj = new \Address;
		$obj->country = $this->countries('uk');

		$relations = array(
			'country' => array('CBelongsToRelation','Country','country_id'),
		);

		$this->assertEquals($this->countries('uk'), $p->modelToResourceParse_Relation($obj, 'country', $relations));
	}

	public function testModelToResourceParse_Relation_BelongsTo_Null()
	{
		$a = 1;
		$p = new DeclarativeTypeParser_Elements($a);

		$obj = new \Address;
		$obj->country = null;

		$relations = array(
			'country' => array('CBelongsToRelation','Country','country_id'),
		);

		$this->assertNull($p->modelToResourceParse_Relation($obj, 'country', $relations));
	}

	public function testModelToResourceParse_Relation_HasMany()
	{
		$a = 1;
		$p = new DeclarativeTypeParser_Elements($a);

		$obj = new \Institution;
		$obj->sites = array(
			$this->sites('site1'),
			$this->sites('site2'),
		);

		$relations = array(
			'sites' => array('CHasManyRelation', 'Site', 'institution_id'),
		);

		$refs = array(
			\Yii::app()->service->Site(1),
			\Yii::app()->service->Site(2),
		);

		$this->assertEquals($refs, $p->modelToResourceParse_Relation($obj, 'sites', $relations));
	}

	public function testModelToResourceParse_Relation_UnhandledRelationType()
	{
		$a = 1;
		$p = new DeclarativeTypeParser_Elements($a);

		$obj = new \Institution;

		$relations = array(
			'sites' => array('CManyManyRelation', 'Site', 'institution_id'),
		);

		$this->setExpectedException('Exception', 'Unhandled relation type: CManyManyRelation');

		$p->modelToResourceParse_Relation($obj, 'sites', $relations);
	}

	public function testModelToResourceParse_Relation_RecurseForElementDataObject()
	{
		// Not sure this is possible atm
	}

	public function testModelToResourceParse_RelationFields_Method()
	{
		$model_element = new DeclarativeTypeParser_ElementsTest_Model5Class;
		$model_element->site = $this->sites('site1');

		$resource_element = new DeclarativeTypeParser_ElementsTest_Element5Class;

		$a = 1;
		$p = new DeclarativeTypeParser_Elements($a);

		$p->modelToResourceParse_RelationFields($resource_element, $model_element);

		$this->assertEquals('City Road',$resource_element->name);
		$this->assertEquals('City Road',$resource_element->short_name);
	}

	public function testModelToResourceParse_References_Method()
	{
		$model_element = new DeclarativeTypeParser_ElementsTest_Model6Class;
		$model_element->site_id = $this->sites('site1')->id;

		$resource_element = new DeclarativeTypeParser_ElementsTest_Element6Class;

		$a = 1;
		$p = new DeclarativeTypeParser_Elements($a);

		$p->modelToResourceParse_RelationFields($resource_element, $model_element);

		$this->assertInstanceOf('services\SiteReference',$resource_element->site_ref);
		$this->assertEquals(1,$resource_element->site_ref->getId());
	}
}

class DeclarativeTypeParser_ElementsTest_ModelClass extends \Address { public function relations() { return array(); } }
class DeclarativeTypeParser_ElementsTest_ElementClass extends \services\ElementDataObject { public function fields() { return array('address1', 'address2'); } }
class DeclarativeTypeParser_ElementsTest_Model2Class extends \Address { public function relations() { return array('country' => array(self::BELONGS_TO, 'Country', 'country_id')); } }
class DeclarativeTypeParser_ElementsTest_Element2Class extends \services\ElementDataObject { public function relations() { return array('country'); } } 
class DeclarativeTypeParser_ElementsTest_Model3Class extends \Address { public function relations() { return array('sites' => array(self::HAS_MANY, 'Site', 'blah_id')); } }
class DeclarativeTypeParser_ElementsTest_Element3Class extends \services\ElementDataObject { public function relations() { return array('sites'); } }
class DeclarativeTypeParser_ElementsTest_Model4Class extends \Address { public function relations() { return array('sites' => array(self::MANY_MANY, 'Site', 'blah_id')); } }
class DeclarativeTypeParser_ElementsTest_Element4Class extends \services\ElementDataObject { public function relations() { return array('sites'); } }
class DeclarativeTypeParser_ElementsTest_Model5Class extends \Address { public function relations() { return array('site' => array(self::BELONGS_TO, 'Site', 'site_id')); } }
class DeclarativeTypeParser_ElementsTest_Element5Class extends \services\ElementDataObject { public function relation_fields() { return array('site' => array('name','short_name')); } }
class DeclarativeTypeParser_ElementsTest_Model6Class extends \Site { public $site_id; public function relations() { return array('site' => array(self::BELONGS_TO, 'Site', 'site_id')); } }
class DeclarativeTypeParser_ElementsTest_Element6Class extends \services\ElementDataObject { public $site_ref; public function references() { return array('site'); }}
class DeclarativeTypeParser_ElementsTest_Model7Class extends \Site { public $site_id; public function relations() { return array('site' => array(self::BELONGS_TO, 'Site', 'site_id')); } }
class DeclarativeTypeParser_ElementsTest_Element7Class extends \services\ElementDataObject { public function references() { return array('site'); }}
