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

class FhirMapTest extends \PHPUnit_Framework_TestCase
{
	private $map;

	protected function setUp()
	{
		$this->map = new FhirMap;
		$this->map->resources = array(
			'services\FhirMapTest_UnambiguousResource',
			'services\FhirMapTest_AmbiguousResource1',
			'services\FhirMapTest_AmbiguousResource2',
		);
	}

	public function getOeResourceTypeByPrefixDataProvider()
	{
		return array(
			array('Foo', null, 'services\FhirMapTest_UnambiguousResource'),
			array('Foo', '1', null),
			array('Bar', null, null),
			array('Bar', '1', 'services\FhirMapTest_AmbiguousResource1'),
			array('Bar', '2', 'services\FhirMapTest_AmbiguousResource2'),
			array('Bar', '3', null),
			array('Baz', null, new NotImplemented("Unsupported resource type: 'Baz'")),
			array('Baz', '1', new NotImplemented("Unsupported resource type: 'Baz'")),
		);
	}

	/**
	 * @dataProvider getOeResourceTypeByPrefixDataProvider
	 */
	public function testGetOeResourceTypeByPrefix($fhir_type, $prefix, $oe_type)
	{
		if ($oe_type instanceof ServiceException) {
			$this->setExpectedException(get_class($oe_type), $oe_type->getMessage());
		}

		$this->assertEquals($oe_type, $this->map->getOeResourceTypeByPrefix($fhir_type, $prefix));
	}

	public function getOeResourceTypeByProfileDataProvider()
	{
		return array(
			array('Foo', array(), 'services\FhirMapTest_UnambiguousResource'),
			array('Foo', array('foo'), 'services\FhirMapTest_UnambiguousResource'),
			array('Foo', array('baz'), 'services\FhirMapTest_UnambiguousResource'),
			array('Foo', array('foo', 'baz'), 'services\FhirMapTest_UnambiguousResource'),
			array('Bar', array(), null),
			array('Bar', array('baz'), null),
			array('Bar', array('bar1'), 'services\FhirMapTest_AmbiguousResource1'),
			array('Bar', array('bar1', 'baz'), 'services\FhirMapTest_AmbiguousResource1'),
			array('Bar', array('bar2'), 'services\FhirMapTest_AmbiguousResource2'),
			array('Bar', array('bar2', 'baz'), 'services\FhirMapTest_AmbiguousResource2'),
		);
	}

	/**
	 * @dataProvider getOeResourceTypeByProfileDataProvider
	 */
	public function testGetOeResourceTypeByProfile($fhir_type, array $profiles, $oe_type)
	{
		if ($oe_type instanceof ServiceException) {
			$this->setExpectedException(get_class($oe_type), $oe_type->getMessage());
		}

		$this->assertEquals($oe_type, $this->map->getOeResourceTypeByProfile($fhir_type, $profiles));
	}
}

class FhirMapTest_UnambiguousResource extends Resource
{
	static protected $fhir_type = 'Foo';

	static public function getOeFhirProfile()
	{
		return 'foo';
	}
}

class FhirMapTest_AmbiguousResource1 extends Resource
{
	static protected $fhir_type = 'Bar';
	static protected $fhir_prefix = '1';

	static public function getOeFhirProfile()
	{
		return 'bar1';
	}
}

class FhirMapTest_AmbiguousResource2 extends Resource
{
	static protected $fhir_type = 'Bar';
	static protected $fhir_prefix = '2';

	static public function getOeFhirProfile()
	{
		return 'bar2';
	}
}
