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

class DeclarativeTypeParser_ConditionTest extends \CDbTestCase
{
	public function setUp()
	{
		$a = 'a';
		$this->p = new DeclarativeTypeParser_Condition($a);
	}

	public function testModelToResourceParse_Equals_True()
	{
		$obj = (object)array(
			'two' => 'razzledazzle'
		);

		$this->assertTrue($this->p->modelToResourceParse($obj, 'two', 'equals', 'razzledazzle'));
	}

	public function testModelToResourceParse_Equals_False()
	{
		$obj = (object)array(
			'two' => 'razzledazzle'
		);

		$this->assertFalse($this->p->modelToResourceParse($obj, 'two', 'equals', 'r4zzled4zzle'));
	}

	public function testModelToResourceParse_UnknownConditionType()
	{
		$obj = (object)array(
			'two' => 'razzledazzle'
		);

		$this->setExpectedException('Exception',"Unknown condition type: bamboo");

		$this->p->modelToResourceParse($obj, 'two', 'bamboo', 'r4zzled4zzle');
	}

	public function testResourceToModelParse_ConditionalAttributeNotSet()
	{
		$mock = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->disableOriginalConstructor()
			->setMethods(array('hasConditionalAttribute','setAttribute','addConditionalAttribute'))
			->getMock();

		$mock->expects($this->once())
			->method('hasConditionalAttribute')
			->with('carrot')
			->will($this->returnValue(false));

		$mock->expects($this->once())
			->method('setAttribute')
			->with('carrot','stick');

		$mock->expects($this->once())
			->method('addConditionalAttribute')
			->with('carrot');

		$resource = (object)array(
			'carrot' => 'orange'
		);

		$this->p->resourceToModelParse($mock, $resource, 'carrot', 'carrot', null, 'stick', false);
	}

	public function testResourceToModelParse_ConditionalAttributeAlreadySet()
	{
		$mock = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->disableOriginalConstructor()
			->setMethods(array('hasConditionalAttribute','setAttribute','addConditionalAttribute'))
			->getMock();

		$mock->expects($this->once())
			->method('hasConditionalAttribute')
			->with('carrot')
			->will($this->returnValue(true));

		$this->setExpectedException('Exception','Unable to differentiate condition as more than one attribute is true.');

		$resource = (object)array(
			'carrot' => 'orange'
		);

		$this->p->resourceToModelParse($mock, $resource, 'carrot', 'carrot', null, 'stick', false);
	}

	public function testJsonToResourceParse()
	{
		$obj = (object)array(
			'one' => 'frog'
		);

		$this->assertEquals('frog', $this->p->jsonToResourceParse($obj,'one',null,null));
	}
}
