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

class DeclarativeTypeParser_ReferenceTest extends \CDbTestCase
{
	public $fixtures = array(
		'patients' => 'Patient',
	);

	public function testModelToResourceParse()
	{
		$object = new \stdClass;
		$object->test = 1;

		$a = 1;
		$p = new DeclarativeTypeParser_Reference($a);

		$ref = $p->modelToResourceParse($object, 'test', 'Patient');

		$this->assertInstanceOf('services\PatientReference', $ref);
		$this->assertEquals(1, $ref->getId());
	}

	public function testModelToResourceParse_Null()
	{
		$object = new \stdClass;
		$object->test = 0;

		$a = 1;
		$p = new DeclarativeTypeParser_Reference($a);

		$ref = $p->modelToResourceParse($object, 'test', 'Patient');

		$this->assertNull($ref);
	}

	public function testResourceToModelParse_GetIdMethod()
	{
		$model = $this->getMockBuilder('services\Address')
			->disableOriginalConstructor()
			->setMethods(array('setAttribute'))
			->getMock();

		$model->expects($this->once())
			->method('setAttribute')
			->with('air',1);

		$a = 1;
		$p = new DeclarativeTypeParser_Reference($a);

		$resource = new \stdClass;
		$resource->patient = \Yii::app()->service->Patient(1);

		$p->resourceToModelParse($model, $resource, 'air', 'patient', null, null, false);
	}

	public function testResourceToModelParse_NoIdMethod()
	{
		$model = $this->getMockBuilder('services\Address')
			->disableOriginalConstructor()
			->setMethods(array('setAttribute'))
			->getMock();

		$model->expects($this->once())
			->method('setAttribute')
			->with('air',2);

		$a = 1;
		$p = new DeclarativeTypeParser_Reference($a);

		$resource = new \stdClass;
		$resource->patient = (object)array('id' => 2);

		$p->resourceToModelParse($model, $resource, 'air', 'patient', null, null, false);
	}

	public function testResourceToModelParse_NullResAttribute()
	{
		$model = $this->getMockBuilder('services\Address')
			->disableOriginalConstructor()
			->setMethods(array('setAttribute'))
			->getMock();

		$model->expects($this->once())
			->method('setAttribute')
			->with('air',null);

		$a = 1;
		$p = new DeclarativeTypeParser_Reference($a);

		$resource = new \stdClass;
		$resource->patient = null;

		$p->resourceToModelParse($model, $resource, 'air', 'patient', null, null, false);
	}

	public function testJsonToResourceParse()
	{
		$a = 1;
		$p = new DeclarativeTypeParser_Reference($a);

		$object = (object)array(
			'attr' => (object)array(
				'service' => 'Patient',
				'id' => 1,
			),
		);

		$res = $p->jsonToResourceParse($object, 'attr', null, null);

		$this->assertInstanceOf('services\PatientReference',$res);
		$this->assertEquals(1,$res->getId());
	}
}
