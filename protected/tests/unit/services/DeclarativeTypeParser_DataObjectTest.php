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
}
