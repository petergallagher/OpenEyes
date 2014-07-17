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
		'referrals' => 'Referral',
		'patients' => 'Patient',
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

	public function testModelToResourceParse_Relation_HasOne()
	{
		$p = $this->getMockBuilder('services\DeclarativeTypeParser_Elements')
			->disableOriginalConstructor()
			->setMethods(array('modelToResourceParse'))
			->getMock();

		$obj = new \Patient;
		$obj->lastReferral = $this->referrals('referral1');

		$p->expects($this->once())
			->method('modelToResourceParse')
			->with($obj, 'lastReferral', null)
			->will($this->returnValue('frog'));

		$this->assertEquals('frog', $p->modelToResourceParse_Relation($obj, 'lastReferral', \Patient::model()->relations()));
	}

	public function testModelToResourceParse_Relation_HasOne_Null()
	{
		$p = $this->getMockBuilder('services\DeclarativeTypeParser_Elements')
			->disableOriginalConstructor()
			->setMethods(array('modelToResourceParse'))
			->getMock();

		$obj = new \Patient;
		$obj->lastReferral = null;

		$p->expects($this->never())
			->method('modelToResourceParse');

		$this->assertEquals(null, $p->modelToResourceParse_Relation($obj, 'lastReferral', \Patient::model()->relations()));
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

		$p->modelToResourceParse_References($resource_element, $model_element, $model_element->relations());

		$this->assertInstanceOf('services\SiteReference',$resource_element->site_ref);
		$this->assertEquals(1,$resource_element->site_ref->getId());
	}

	public function testResourceToModelParse_Fields()
	{
		$model = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->disableOriginalConstructor()
			->setMethods(array('getModelClass','setAttribute'))
			->getMock();

		$_element = new DeclarativeTypeParser_ElementsTest_ElementClass;
		$_element->_class_name = 'services\DeclarativeTypeParser_ElementsTest_ModelClass';
		$_element->address1 = 'test1';
		$_element->address2 = 'test2';

		$object = new DeclarativeTypeParser_ElementsTest_ElementClass;
		$object->data = array($_element);

		$element = new DeclarativeTypeParser_ElementsTest_ModelClass;
		$element->address1 = 'test1';
		$element->address2 = 'test2';

		$model->expects($this->once())
			->method('setAttribute')
			->with('_elements',array($element));

		$p = $this->getMockBuilder('services\DeclarativeTypeParser_Elements')
			->disableOriginalConstructor()
			->setMethods(array('createResourceObjectFromModel'))
			->getMock();

		$result = $p->resourceToModelParse($model, $object, null, 'data', null, null, false);

		$this->assertCount(1,$result);
		$this->assertEquals('test1',$result[0]->address1);
		$this->assertEquals('test2',$result[0]->address2);
	}

	public function testResourceToModelParse_Relations_BelongsTo()
	{
		$model = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->disableOriginalConstructor()
			->setMethods(array('getModelClass','setAttribute'))
			->getMock();

		$_element = new DeclarativeTypeParser_ElementsTest_Element2Class;
		$_element->_class_name = 'services\DeclarativeTypeParser_ElementsTest_Model2Class';
		$_element->country = \Country::model()->findByPk(1);

		$object = new DeclarativeTypeParser_ElementsTest_Element2Class;
		$object->data = array($_element);

		$element = new DeclarativeTypeParser_ElementsTest_Model2Class;
		$element->country_id = 1;
		$element->country = \Country::model()->findByPk(1);

		$model->expects($this->once())
			->method('setAttribute')
			->with('_elements',array($element));

		$p = $this->getMockBuilder('services\DeclarativeTypeParser_Elements')
			->disableOriginalConstructor()
			->setMethods(array('createResourceObjectFromModel'))
			->getMock();

		$result = $p->resourceToModelParse($model, $object, null, 'data', null, null, false);

		$this->assertCount(1,$result);
		$this->assertEquals(1,$result[0]->country_id);
		$this->assertEquals(\Country::model()->findByPk(1),$result[0]->country);
	}

	public function testResourceToModelParse_Relations_HasMany()
	{
		$model = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->disableOriginalConstructor()
			->setMethods(array('getModelClass','setAttribute'))
			->getMock();

		$_element = new DeclarativeTypeParser_ElementsTest_Element3Class;
		$_element->_class_name = 'services\DeclarativeTypeParser_ElementsTest_Model3Class';
		$_element->sites = array(
			\Yii::app()->service->Site(1),
			\Yii::app()->service->Site(2),
		);

		$object = new DeclarativeTypeParser_ElementsTest_Element3Class;
		$object->data = array($_element);

		$element = new DeclarativeTypeParser_ElementsTest_Model3Class;
		$element->sites = array(
			$this->sites('site1'),
			$this->sites('site2'),
		);

		$model->expects($this->once())
			->method('setAttribute')
			->with('_elements',array($element));

		$p = $this->getMockBuilder('services\DeclarativeTypeParser_Elements')
			->disableOriginalConstructor()
			->setMethods(array('createResourceObjectFromModel'))
			->getMock();

		$result = $p->resourceToModelParse($model, $object, null, 'data', null, null, false);

		$this->assertCount(1,$result);
		$this->assertCount(2,$result[0]->sites);
		$this->assertEquals($this->sites('site1'),$result[0]->sites[0]);
		$this->assertEquals($this->sites('site2'),$result[0]->sites[1]);
	}

	public function testResourceToModelParse_Relations_UnhandledRelationType()
	{
		$model = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->disableOriginalConstructor()
			->setMethods(array('getModelClass','setAttribute'))
			->getMock();

		$_element = new DeclarativeTypeParser_ElementsTest_Element4Class;
		$_element->_class_name = 'services\DeclarativeTypeParser_ElementsTest_Model4Class';
		$_element->sites = array(
			\Yii::app()->service->Site(1),
			\Yii::app()->service->Site(2),
		);

		$object = new DeclarativeTypeParser_ElementsTest_Element4Class;
		$object->data = array($_element);

		$p = $this->getMockBuilder('services\DeclarativeTypeParser_Elements')
			->disableOriginalConstructor()
			->setMethods(array('createResourceObjectFromModel'))
			->getMock();

		$this->setExpectedException('Exception','Unhandled relation type: CManyManyRelation');

		$result = $p->resourceToModelParse($model, $object, null, 'data', null, null, false);
	}

	public function testResourceToModelParse_RelationFields()
	{
		$model = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->setConstructorArgs(array(new ModelMap(PatientService::$model_map),new \Patient))
			->setMethods(array('getModelClass','setAttribute'))
			->getMock();

		$_element = new DeclarativeTypeParser_ElementsTest_Element8Class(array('id'=>1));
		$_element->_class_name = 'Patient';
		$_element->refno = '534';
		$_element->referrer = 'TEST';

		$object = new DeclarativeTypeParser_ElementsTest_Element8Class;
		$object->data = array($_element);

		$p = $this->getMockBuilder('services\DeclarativeTypeParser_Elements')
			->disableOriginalConstructor()
			->setMethods(array('createResourceObjectFromModel'))
			->getMock();

		$result = $p->resourceToModelParse($model, $object, null, 'data', null, null, false);

		$this->assertCount(1,$result);
		$this->assertInstanceOf('Patient',$result[0]);
		$this->assertEquals(1,$result[0]->id);
		$this->assertInstanceOf('Referral',$result[0]->lastReferral);
		$this->assertEquals(3,$result[0]->lastReferral->id);
		$this->assertEquals('534',$result[0]->lastReferral->refno);
		$this->assertEquals('TEST',$result[0]->lastReferral->referrer);
	}

	public function testResourceToModelParse_References()
	{
		$model = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->disableOriginalConstructor()
			->setMethods(array('getModelClass','setAttribute'))
			->getMock();

		$_element = new DeclarativeTypeParser_ElementsTest_Element6Class;
		$_element->_class_name = 'services\DeclarativeTypeParser_ElementsTest_Model6Class';
		$_element->site_ref = \Yii::app()->service->Site(1);

		$object = new DeclarativeTypeParser_ElementsTest_Element6Class;
		$object->data = array($_element);

		$element = new DeclarativeTypeParser_ElementsTest_Model6Class;
		$element->site_id = 1;
		$element->site = \Site::model()->findByPk(1);

		$model->expects($this->once())
			->method('setAttribute')
			->with('_elements',array($element));

		$p = $this->getMockBuilder('services\DeclarativeTypeParser_Elements')
			->disableOriginalConstructor()
			->setMethods(array('createResourceObjectFromModel'))
			->getMock();

		$result = $p->resourceToModelParse($model, $object, null, 'data', null, null, false);

		$this->assertCount(1,$result);
		$this->assertEquals(1,$result[0]->site_id);
		$this->assertEquals(\Site::model()->findByPk(1),$result[0]->site);
	}

	public function testResourceToModelParse_FieldValue_Date()
	{
		$a = 1;
		$p = new DeclarativeTypeParser_Elements($a);
		$this->assertEquals('2013-03-03',$p->resourceToModelParse_FieldValue(new Date('2013-03-03')));
	}

	public function testResourceToModelParse_FieldValue_DateTime()
	{
		$a = 1;
		$p = new DeclarativeTypeParser_Elements($a);
		$this->assertEquals('2013-03-03 13:33:37',$p->resourceToModelParse_FieldValue(new DateTime('2013-03-03 13:33:37')));
	}

	public function testResourceToModelParse_FieldValue_RawValue()
	{
		$a = 1;
		$p = new DeclarativeTypeParser_Elements($a);
		$this->assertEquals('test2183912',$p->resourceToModelParse_FieldValue('test2183912'));
	}

	public function testResourceToModelParse_Relations_Method_HasOne()
	{
		$resource_element = new DeclarativeTypeParser_ElementsTest_Element11Class;
		$resource_element->_class_name = 'services\DeclarativeTypeParser_ElementsTest_Model11Class';
		$resource_element->lastReferral = $this->referrals('referral1');

		$p = $this->getMockBuilder('services\DeclarativeTypeParser_Elements')
			->disableOriginalConstructor()
			->setMethods(array('resourceToModelParse'))
			->getMock();

		$model_element = new DeclarativeTypeParser_ElementsTest_Model11Class;

		$p->expects($this->once())
			->method('resourceToModelParse')
			->with(1,$resource_element,null,'lastReferral',null,null,null)
			->will($this->returnValue('boba phet'));

		$p->resourceToModelParse_Relations($model_element, $resource_element, null, null, $model_element->relations());

		$this->assertEquals('boba phet',$model_element->lastReferral);
	}

	public function testResourceToModelParse_Relations_Method_HasOne_Null()
	{
		$resource_element = new DeclarativeTypeParser_ElementsTest_Element11Class;
		$resource_element->_class_name = 'services\DeclarativeTypeParser_ElementsTest_Model11Class';
		$resource_element->lastReferral = null;

		$p = $this->getMockBuilder('services\DeclarativeTypeParser_Elements')
			->disableOriginalConstructor()
			->setMethods(array('resourceToModelParse'))
			->getMock();

		$model_element = new DeclarativeTypeParser_ElementsTest_Model11Class;

		$p->expects($this->never())
			->method('resourceToModelParse');

		$p->resourceToModelParse_Relations($model_element, $resource_element, null, null, $model_element->relations());

		$this->assertNull($model_element->lastReferral);
	}

	public function testResourceToModelParse_Relations_Method_HasMany()
	{
		$resource_element = new DeclarativeTypeParser_ElementsTest_Element3Class;
		$resource_element->_class_name = 'services\DeclarativeTypeParser_ElementsTest_Model3Class';
		$resource_element->sites = array(
			\Yii::app()->service->Site(1),
			\Yii::app()->service->Site(2),
		);

		$a = 1;
		$p = new DeclarativeTypeParser_Elements($a);

		$model_element = new DeclarativeTypeParser_ElementsTest_Model3Class;

		$p->resourceToModelParse_Relations($model_element, $resource_element, $resource_element, 'sites', $model_element->relations());

		$this->assertEquals(array($this->sites('site1'),$this->sites('site2')),$model_element->sites);
	}

	public function testResourceToModelParse_Relations_Method_UnhandledRelationType()
	{
		$resource_element = new DeclarativeTypeParser_ElementsTest_Element4Class;
		$resource_element->_class_name = 'services\DeclarativeTypeParser_ElementsTest_Model4Class';
		$resource_element->sites = array(
			\Yii::app()->service->Site(1),
			\Yii::app()->service->Site(2),
		);

		$a = 1;
		$p = new DeclarativeTypeParser_Elements($a);

		$model_element = new DeclarativeTypeParser_ElementsTest_Model4Class;

		$this->setExpectedException('Exception','Unhandled relation type: CManyManyRelation');

		$p->resourceToModelParse_Relations($model_element, $resource_element, $resource_element, 'sites', $model_element->relations());
	}

	public function testResourceToModelParse_RelationFields_Method()
	{
		$a = 1;
		$p = new DeclarativeTypeParser_Elements($a);

		$resource_element = new DeclarativeTypeParser_ElementsTest_Element8Class(array('id'=>1));
		$resource_element->_class_name = 'Patient';
		$resource_element->refno = '534';
		$resource_element->referrer = 'TEST';

		$model_element = $this->patients('patient1');
		$model_element->lastReferral->refno = '123';
		$model_element->lastReferral->referrer = '123';

		$p->resourceToModelParse_RelationFields($model_element, $resource_element);

		$this->assertInstanceOf('Referral',$model_element->lastReferral);
		$this->assertEquals(3,$model_element->lastReferral->id);
		$this->assertEquals('534',$model_element->lastReferral->refno);
		$this->assertEquals('TEST',$model_element->lastReferral->referrer);
	}

	public function testResourceToModelParse_References_Method()
	{
		$a = 1;
		$p = new DeclarativeTypeParser_Elements($a);

		$resource_element = new DeclarativeTypeParser_ElementsTest_Element6Class;
		$resource_element->_class_name = 'services\DeclarativeTypeParser_ElementsTest_Model6Class';
		$resource_element->site_ref = \Yii::app()->service->Site(1);

		$model_element = new DeclarativeTypeParser_ElementsTest_Model6Class;

		$p->resourceToModelParse_References($model_element, $resource_element);

		$this->assertEquals(1,$model_element->site_id);
		$this->assertEquals($this->sites('site1'),$model_element->site);
	}

	public function testResourceToModel_AfterSave_SaveElements()
	{
		$mc = $this->getMockBuilder('services\ModelConverter')
			->disableOriginalConstructor()
			->setMethods(array('saveModel'))
			->getMock();

		$address1 = new DeclarativeTypeParser_ElementsTest_Model9Class;
		$address1->address1 = 'one';
		$address2 = new DeclarativeTypeParser_ElementsTest_Model9Class;
		$address2->address1 = 'two';
		$address3 = new DeclarativeTypeParser_ElementsTest_Model9Class;
		$address3->address1 = 'three';

		$model = new DeclarativeTypeParser_ElementsTest_Model8Class;
		$model->id = 123;
		$model->_elements = array(clone $address1,clone $address2,clone $address3);

		$model = new ModelConverter_ModelWrapper(new ModelMap(PatientService::$model_map),$model);

		$address1->event_id = 123;
		$address2->event_id = 123;
		$address3->event_id = 123;

		$mc->expects($this->at(0))->method('saveModel')->with($address1);
		$mc->expects($this->at(1))->method('saveModel')->with($address2);
		$mc->expects($this->at(2))->method('saveModel')->with($address3);

		$resource_element = $this->getMockBuilder('services\DeclarativeTypeParser_ElementsTest_Element5Class')
			->setMethods(array('relation_fields'))
			->getMock();

		$resource_element->expects($this->any())
			->method('relation_fields')
			->will($this->returnValue(array()));

		$resource = new DeclarativeTypeParser_ElementsTest_Element9Class;
		$resource->elements = array($resource_element,$resource_element,$resource_element);

		$p = new DeclarativeTypeParser_Elements($mc);
		$p->resourceToModel_AfterSave($model, $resource);
	}

	public function testResourceToModel_AfterSave_SaveRelationFields()
	{
		$mc = $this->getMockBuilder('services\ModelConverter')
			->disableOriginalConstructor()
			->setMethods(array('saveModel'))
			->getMock();

		$patient = $this->patients('patient1');

		$address1 = new DeclarativeTypeParser_ElementsTest_Model10Class;
		$address1->lastReferral = $this->referrals('referral1');

		$model = new DeclarativeTypeParser_ElementsTest_Model8Class;
		$model->id = 123;
		$model->_elements = array($address1);

		$model = new ModelConverter_ModelWrapper(new ModelMap(PatientService::$model_map),$model);

		$resource_element = new DeclarativeTypeParser_ElementsTest_Element8Class;

		$resource = new DeclarativeTypeParser_ElementsTest_Element9Class;
		$resource->elements = array($resource_element);

		$mc->expects($this->at(1))
			->method('saveModel')
			->with($this->referrals('referral1'));

		$p = new DeclarativeTypeParser_Elements($mc);
		$p->resourceToModel_AfterSave($model, $resource); 
	}

	public function testJsonToResourceParse()
	{
		$p = $this->getMockBuilder('services\DeclarativeTypeParser_Elements')
			->disableOriginalConstructor()
			->setMethods(array('jsonToResourceParse_TranslateObject'))
			->getMock();

		$p->expects($this->at(0))
			->method('jsonToResourceParse_TranslateObject')
			->with('one');

		$p->expects($this->at(1))
			->method('jsonToResourceParse_TranslateObject')
			->with('two');

		$p->expects($this->at(2))
			->method('jsonToResourceParse_TranslateObject')
			->with('three');

		$object = new \stdClass;
		$object->data = array('one','two','three');

		$p->jsonToResourceParse($object, 'data', null, null);
	}
}

class DeclarativeTypeParser_ElementsTest_ModelClass extends \Address { public function relations() { return array(); } }
class DeclarativeTypeParser_ElementsTest_ElementClass extends \services\ElementDataObject { public function fields() { return array('address1', 'address2'); } }
class DeclarativeTypeParser_ElementsTest_Model2Class extends \Address { public function relations() { return array('country' => array(self::BELONGS_TO, 'Country', 'country_id')); } }
class DeclarativeTypeParser_ElementsTest_Element2Class extends \services\ElementDataObject { public function lookup_relations() { return array('country'); } } 
class DeclarativeTypeParser_ElementsTest_Model3Class extends \Address { public function relations() { return array('sites' => array(self::HAS_MANY, 'Site', 'blah_id')); } }
class DeclarativeTypeParser_ElementsTest_Element3Class extends \services\ElementDataObject { public function dataobject_relations() { return array('sites'); } }
class DeclarativeTypeParser_ElementsTest_Model4Class extends \Address { public function relations() { return array('sites' => array(self::MANY_MANY, 'Site', 'blah_id')); } }
class DeclarativeTypeParser_ElementsTest_Element4Class extends \services\ElementDataObject { public function dataobject_relations() { return array('sites'); } }
class DeclarativeTypeParser_ElementsTest_Model5Class extends \Address { public $site_id; public function relations() { return array('site' => array(self::BELONGS_TO, 'Site', 'site_id')); } }
class DeclarativeTypeParser_ElementsTest_Element5Class extends \services\ElementDataObject { public function relation_fields() { return array('site' => array('name','short_name')); } }
class DeclarativeTypeParser_ElementsTest_Model6Class extends \Site { public $site_id; public function relations() { return array('site' => array(self::BELONGS_TO, 'Site', 'site_id')); } }
class DeclarativeTypeParser_ElementsTest_Element6Class extends \services\ElementDataObject { public $site_ref; public function references() { return array('site'); }}
class DeclarativeTypeParser_ElementsTest_Model7Class extends \Site { public $site_id; public function relations() { return array('site' => array(self::BELONGS_TO, 'Site', 'site_id')); } }
class DeclarativeTypeParser_ElementsTest_Element7Class extends \services\ElementDataObject { public function references() { return array('site'); }}
class DeclarativeTypeParser_ElementsTest_Element8Class extends \services\ElementDataObject { public function relation_fields() { return array('lastReferral' => array('refno','referrer')); } }
class DeclarativeTypeParser_ElementsTest_Model8Class extends \Site { public $_elements; }
class DeclarativeTypeParser_ElementsTest_Model9Class extends \Address { public $event_id; }
class DeclarativeTypeParser_ElementsTest_Element9Class extends \services\ElementDataObject { public $elements; }
class DeclarativeTypeParser_ElementsTest_Model10Class extends \Patient { public $event_id; }
class DeclarativeTypeParser_ElementsTest_Model11Class extends \Patient { }
class DeclarativeTypeParser_ElementsTest_Element11Class extends \services\ElementDataObject { public function dataobject_relations() { return array('lastReferral'); } }
