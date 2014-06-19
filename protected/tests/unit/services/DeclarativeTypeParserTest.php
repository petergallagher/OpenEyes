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

class DeclarativeTypeParserTest extends \CDbTestCase
{
	public function testExpandObjectAttribute_Direct()
	{
		$obj = (object)array(
			'one' => 'one',
			'two' => 'rubbedybubbedy',
		);

		$this->assertEquals('one', DeclarativeTypeParser::expandObjectAttribute($obj,'one'));
		$this->assertEquals('rubbedybubbedy', DeclarativeTypeParser::expandObjectAttribute($obj,'two'));
	}

	public function testExpandObjectAttribute_OneLevel()
	{
		$obj = (object)array(
			'one' => (object)array(
				'two' => 'rubbedybubbedy'
			)
		);

		$this->assertEquals('rubbedybubbedy', DeclarativeTypeParser::expandObjectAttribute($obj,'one.two'));
	}

	public function testExpandObjectAttribute_MultiLevel()
	{
		$obj = (object)array(
			'one' => (object)array(
				'two' => (object)array(
					'three' => (object)array(
						'four' => (object)array(
							'five' => 'rubbedybubbedy'
						)
					)
				)
			)
		);

		$this->assertEquals('rubbedybubbedy', DeclarativeTypeParser::expandObjectAttribute($obj,'one.two.three.four.five'));
	}

	public function testSetObjectAttribute_Direct()
	{
		$obj = new \stdClass;

		DeclarativeTypeParser::setObjectAttribute($obj, 'two', 'bubbedyboo');

		$this->assertEquals('bubbedyboo',$obj->two);
	}

	public function testSetObjectAttribute_Direct_DefaultToForce()
	{
		$obj = (object)array(
			'two' => 'rubrubrub'
		);

		DeclarativeTypeParser::setObjectAttribute($obj, 'two', 'bubbedyboo');

		$this->assertEquals('bubbedyboo',$obj->two);
	}

	public function testSetObjectAttribute_Direct_NoForce()
	{
		$obj = (object)array(
			'two' => 'rubrubrub'
		);
		
		DeclarativeTypeParser::setObjectAttribute($obj, 'two', 'bubbedyboo', false);

		$this->assertEquals('rubrubrub',$obj->two);
	}

	public function testSetObjectAttribute_OneLevel()
	{
		$obj = (object)array(
			'one' => new \stdClass
		);

		DeclarativeTypeParser::setObjectAttribute($obj, 'one.two', 'bubbedyboo');

		$this->assertEquals('bubbedyboo',$obj->one->two);
	}

	public function testSetObjectAttribute_OneLevel_DefaultToForce()
	{
		$obj = (object)array(
			'one' => (object)array(
				'two' => 'two',
			),
		);

		DeclarativeTypeParser::setObjectAttribute($obj, 'one.two', 'bubbedyboo');

		$this->assertEquals('bubbedyboo',$obj->one->two);
	}

	public function testSetObjectAttribute_OneLevel_NoForce()
	{
		$obj = (object)array(
			'one' => (object)array(
				'two' => 'zooberon',
			),
		);

		DeclarativeTypeParser::setObjectAttribute($obj, 'one.two', 'bubbedyboo', false);

		$this->assertEquals('zooberon',$obj->one->two);
	}

	public function testSetObjectAttribute_MultiLevel()
	{
		$obj = (object)array(
			'one' => (object)array(
				'two' => (object)array(
					'three' => (object)array(
						'four' => new \stdClass
					)
				)
			)
		);

		DeclarativeTypeParser::setObjectAttribute($obj, 'one.two.three.four.five', 'bubbedyboo');

		$this->assertEquals('bubbedyboo',$obj->one->two->three->four->five);
	}

	public function testSetObjectAttribute_MultiLevel_DefaultToForce()
	{
		$obj = (object)array(
			'one' => (object)array(
				'two' => (object)array(
					'three' => (object)array(
						'four' => (object)array(
							'five' => 'rubbedybubbedy'
						)
					)
				)
			)
		);

		DeclarativeTypeParser::setObjectAttribute($obj, 'one.two.three.four.five', 'bubbedyboo');

		$this->assertEquals('bubbedyboo',$obj->one->two->three->four->five);
	}

	public function testSetObjectAttribute_MultiLevel_NoForce()
	{
		$obj = (object)array(
			'one' => (object)array(
				'two' => (object)array(
					'three' => (object)array(
						'four' => (object)array(
							'five' => 'rubbedybubbedy'
						)
					)
				)
			)
		);

		DeclarativeTypeParser::setObjectAttribute($obj, 'one.two.three.four.five', 'bubbedyboo', false);

		$this->assertEquals('rubbedybubbedy',$obj->one->two->three->four->five);
	}

	public function testSetObjectAttributes_Direct()
	{
		$obj = new \stdClass;

		DeclarativeTypeParser::setObjectAttributes($obj, array('one' => 'boo', 'two' => 'baa', 'three' => 'bee'));

		$this->assertEquals('boo',$obj->one);
		$this->assertEquals('baa',$obj->two);
		$this->assertEquals('bee',$obj->three);
	}

	public function testSetObjectAttributes_WithMethod()
	{
		$mock = $this->getMockBuilder('services\ModelConverter_ModelWrapper')
			->disableOriginalConstructor()
			->setMethods(array('setAttribute'))
			->getMock();

		$mock->expects($this->at(0))
			->method('setAttribute')
			->with($this->equalTo('one'),$this->equalTo('boo'));

		$mock->expects($this->at(1))
			->method('setAttribute')
			->with($this->equalTo('two'),$this->equalTo('baa'));

		$mock->expects($this->at(2))
			->method('setAttribute')
			->with($this->equalTo('three'),$this->equalTo('bee'));

		DeclarativeTypeParser::setObjectAttributes($mock, array('one' => 'boo', 'two' => 'baa', 'three' => 'bee'));
	}

	public function testAttributesAllNull_AllNull()
	{
		$obj = (object)array(
			'one' => null,
			'two' => null,
			'three' => null,
			'four' => 'BAA',
		);

		$this->assertTrue(DeclarativeTypeParser::attributesAllNull($obj,array('one','two','three')));
	}

	public function testAttributesAllNull_NotAllNull()
	{
		$obj = (object)array(
			'one' => null,
			'two' => null,
			'three' => null,
			'four' => 'BAA',
		);
	 
		$this->assertFalse(DeclarativeTypeParser::attributesAllNull($obj,array('one','two','three','four')));
	}
}
