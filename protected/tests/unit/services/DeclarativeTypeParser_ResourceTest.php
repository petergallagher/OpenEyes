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

class DeclarativeTypeParser_ResourceTest extends \CDbTestCase
{
	public $fixtures = array(
		'patients' => 'Patient',
	);

	public function testModelToResourceParse()
	{
		$mc = $this->getMockBuilder('services\ModelConverter')
			->disableOriginalConstructor()
			->setMethods(array('modelToResource'))
			->getMock();

		$item = (object)array(
			'id' => 123,
			'last_modified_date' => '2012-01-01 13:37:00',
		);

		$object = new \stdClass;
		$object->attr = $item;

		$mc->expects($this->once())
			->method('modelToResource')
			->with($item, new Address(array('id' => 123, 'last_modified' => strtotime('2012-01-01 13:37:00'))))
			->will($this->returnValue('tested'));

		$p = new DeclarativeTypeParser_Resource($mc);

		$this->assertEquals('tested', $p->modelToResourceParse($object, 'attr', 'Address', null));
	}

	public function testResourceToModelParse_HasRelation()
	{
		$mc = $this->getMockBuilder('services\ModelConverter')
			->disableOriginalConstructor()
			->setMethods(array('resourceToModel'))
			->getMock();

		$resource = (object)array(
			'cats' => 'furry',
			'twotwo' => 'furball',
		);

		$mc->expects($this->once())
			->method('resourceToModel')
			->with('furball',new \Address,false)
			->will($this->returnValue('starwars'));

		$model = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->disableOriginalConstructor()
			->setMethods(array('setAttribute','hasBelongsToRelation','setAttributeForBelongsToRelation'))
			->getMock();

		$model->expects($this->once())
			->method('setAttribute')
			->with('oneone','starwars');

		$model->expects($this->once())
			->method('hasBelongsToRelation')
			->with('oneone')
			->will($this->returnValue(true));

		$model->expects($this->once())
			->method('setAttributeForBelongsToRelation')
			->with('oneone');

		$p = new DeclarativeTypeParser_Resource($mc);
		$p->resourceToModelParse($model, $resource, 'oneone', 'twotwo', 'Address', null, false);
	}

	public function testResourceToModelParse_HasNoRelation()
	{
		$mc = $this->getMockBuilder('services\ModelConverter')
			->disableOriginalConstructor()
			->setMethods(array('resourceToModel'))
			->getMock();

		$resource = (object)array(
			'cats' => 'furry',
			'twotwo' => 'furball',
		);

		$mc->expects($this->once())
			->method('resourceToModel')
			->with('furball',new \Address,false)
			->will($this->returnValue('starwars'));

		$model = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->disableOriginalConstructor()
			->setMethods(array('setAttribute','hasBelongsToRelation','setAttributeForBelongsToRelation'))
			->getMock();

		$model->expects($this->once())
			->method('setAttribute')
			->with('oneone','starwars');

		$model->expects($this->once())
			->method('hasBelongsToRelation')
			->with('oneone')
			->will($this->returnValue(false));

		$model->expects($this->never())
			->method('setAttributeForBelongsToRelation');

		$p = new DeclarativeTypeParser_Resource($mc);
		$p->resourceToModelParse($model, $resource, 'oneone', 'twotwo', 'Address', null, false);
	}

	public function testJsonToResourceParse()
	{
		$object = new \stdClass;
		$object->test = 'tost';

		$a = 1;
		$p = new DeclarativeTypeParser_Resource($a);
		$this->assertEquals('tost', $p->jsonToResourceParse($object, 'test', null, null));
	}
}
