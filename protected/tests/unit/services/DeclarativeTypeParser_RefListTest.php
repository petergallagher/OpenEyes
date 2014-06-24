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

class DeclarativeTypeParser_RefListTest extends \CDbTestCase
{
	public $fixtures = array(
		'patients' => 'Patient',
	);

	public function testModelToResourceParse()
	{
		$patient1 = (object)array('id_field' => 1);
		$patient2 = (object)array('id_field' => 2);
		$patient3 = (object)array('id_field' => 3);
		$patient4 = (object)array('id_field' => 4);

		$object = new \stdClass;
		$object->stuff = array(
			$patient1,
			$patient2,
			$patient3,
			$patient4,
		);

		$a = 1;
		$p = new DeclarativeTypeParser_RefList($a);

		$result = $p->modelToResourceParse($object, 'stuff', 'id_field', 'Patient');

		$this->assertTrue(is_array($result));
		$this->assertCount(4, $result);
		$this->assertInstanceOf('services\PatientReference', $result[0]);
		$this->assertEquals(1, $result[0]->getId());
		$this->assertInstanceOf('services\PatientReference', $result[1]);
		$this->assertEquals(2, $result[1]->getId());
		$this->assertInstanceOf('services\PatientReference', $result[2]);
		$this->assertEquals(3, $result[2]->getId());
		$this->assertInstanceOf('services\PatientReference', $result[3]);
		$this->assertEquals(4, $result[3]->getId());
	}

	public function testResourceToModelParse()
	{
		$model = $this->getMockBuilder('services\Address')
			->disableOriginalConstructor()
			->setMethods(array('setReferenceListForRelation'))
			->getMock();

		$model->expects($this->once())
			->method('setReferenceListForRelation')
			->with('mac','penguin','kernel');

		$a = 1;
		$p = new DeclarativeTypeParser_RefList($a);

		$resource = (object)array(
			'linux' => 'kernel'
		);

		$p->resourceToModelParse($model, $resource, 'mac', 'linux', 'penguin', null, false);
	}

	public function testJsonToResourceParse()
	{
		$a = 1;
		$p = new DeclarativeTypeParser_RefList($a);

		$object = (object)array(
			'stuff' => array(
				(object)array(
					'id' => 1,
				),
				(object)array(
					'id' => 2,
				),
				(object)array(
					'id' => 3,
				),
				(object)array(
					'id' => 4,
				),
			)
		);

		$refs = $p->jsonToResourceParse($object, 'stuff', null, 'Patient');

		$this->assertTrue(is_array($refs));
		$this->assertCount(4, $refs);
		$this->assertInstanceOf('services\PatientReference', $refs[0]);
		$this->assertEquals(1, $refs[0]->getId());
		$this->assertInstanceOf('services\PatientReference', $refs[1]);
		$this->assertEquals(2, $refs[1]->getId());
		$this->assertInstanceOf('services\PatientReference', $refs[2]);
		$this->assertEquals(3, $refs[2]->getId());
		$this->assertInstanceOf('services\PatientReference', $refs[3]);
		$this->assertEquals(4, $refs[3]->getId());
	}
}
