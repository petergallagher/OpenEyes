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

class DeclarativeTypeParser_OrTest extends \CDbTestCase
{
	public function testModelToResourceParse()
	{
		$object = new \stdClass;

		$item = new \stdClass;
		$item->model = 'E39';

		$a = 1;
		$p = new DeclarativeTypeParser_Or($a);

		for ($i=0;$i<2;$i++) {
			for ($j=0;$j<2;$j++) {
				for ($k=0;$k<2;$k++) {
					$object->test1 = (boolean)$i ? $item : null;
					$object->test2 = (boolean)$j ? $item : null;
					$object->test3 = (boolean)$k ? $item : null;

					$this->assertEquals((($i || $j || $k) ? 'E39' : null), $p->modelToResourceParse($object, 'model', array('test1','test2','test3'), null));
				}
			}
		}
	}

	public function testResourceToModelParse_AllNull_Then()
	{
		$map = $this->getMockBuilder('services\ModelMap')
			->disableOriginalConstructor()
			->setMethods(array('getRuleForOrClause'))
			->getMock();

		$rule = array(
			DeclarativeModelService::RULE_TYPE_ALLNULL,
			array('fee','fii','foo'),
			'then' => 'country',
			'else' => 'blah',
		);

		$map->expects($this->once())
			->method('getRuleForOrClause')
			->with('Address', 'iphone')
			->will($this->returnValue($rule));

		$model = $this->getMockBuilder('services\Address')
			->disableOriginalConstructor()
			->setMethods(array('setAttribute','getClass'))
			->getMock();

		$resource = new \stdClass;
		$resource->iphone = '5S';
		$resource->fee = null;
		$resource->fii = null;
		$resource->foo = null;

		$model->expects($this->once())
			->method('setAttribute')
			->with('country.name',$resource->iphone);

		$model->expects($this->once())
			->method('getClass')
			->will($this->returnValue('Address'));

		$mc = new ModelConverter($map);

		$p = new DeclarativeTypeParser_Or($mc);

		$p->resourceToModelParse($model, $resource, 'name', 'iphone', null, null);
	}

	public function testResourceToModelParse_AllNull_Else()
	{
		$map = $this->getMockBuilder('services\ModelMap')
			->disableOriginalConstructor()
			->setMethods(array('getRuleForOrClause'))
			->getMock();

		$rule = array(
			DeclarativeModelService::RULE_TYPE_ALLNULL,
			array('fee','fii','foo'),
			'then' => 'blah',
			'else' => 'country',
		);

		$map->expects($this->once())
			->method('getRuleForOrClause')
			->with('Address', 'iphone')
			->will($this->returnValue($rule));

		$model = $this->getMockBuilder('services\Address')
			->disableOriginalConstructor()
			->setMethods(array('setAttribute','getClass'))
			->getMock();

		$resource = new \stdClass;
		$resource->iphone = '5S';
		$resource->fee = null;
		$resource->fii = true;
		$resource->foo = null;

		$model->expects($this->once())
			->method('setAttribute')
			->with('country.name',$resource->iphone);

		$model->expects($this->once())
			->method('getClass')
			->will($this->returnValue('Address'));

		$mc = new ModelConverter($map);

		$p = new DeclarativeTypeParser_Or($mc);

		$p->resourceToModelParse($model, $resource, 'name', 'iphone', null, null);
	}

	public function testResourceToModelParse_UnknownRuleType()
	{
		$map = $this->getMockBuilder('services\ModelMap')
			->disableOriginalConstructor()
			->setMethods(array('getRuleForOrClause'))
			->getMock();

		$rule = array(
			1,
			array('fee','fii','foo'),
			'then' => 'blah',
			'else' => 'country',
		);

		$map->expects($this->once())
			->method('getRuleForOrClause')
			->with('Address', 'iphone')
			->will($this->returnValue($rule));

		$model = $this->getMockBuilder('services\Address')
			->disableOriginalConstructor()
			->setMethods(array('setAttribute','getClass'))
			->getMock();

		$resource = new \stdClass;
		$resource->iphone = '5S';
		$resource->fee = null;
		$resource->fii = true;
		$resource->foo = null;

		$model->expects($this->once())
			->method('getClass')
			->will($this->returnValue('Address'));

		$mc = new ModelConverter($map);

		$p = new DeclarativeTypeParser_Or($mc);

		$this->setExpectedException('Exception', 'Unknown rule type: 1');

		$p->resourceToModelParse($model, $resource, 'name', 'iphone', null, null);
	}
}
