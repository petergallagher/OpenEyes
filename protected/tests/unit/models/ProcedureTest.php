<?php

/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */
class ProcedureTest extends CDbTestCase
{
	public $model;
	public $fixtures = array(
		'procedures' => 'Procedure',
	);

	public function setUp()
	{
		parent::setUp();
		$this->model = new Procedure;
	}

	public function dataProvider_ProcedureSearch()
	{
		$foobar = array('id'=>'1','term'=>'Foobar Procedure','default_duration'=>60,'is_common'=>false);
		$test = array('id'=>2,'term'=>'Test Procedure','default_duration'=>20,'is_common'=>false);

		return array(
			array('Foo', array($foobar)),
			array('Foobar', array($foobar)),
			array('Fo', array($foobar)),
			array('UB', array($foobar)),
			array('Bla', array($foobar)),
			array('wstfgl', array($foobar)),
			array('barfoo', array($foobar)),
			array('Test', array($test)),
			array('Test Pro', array($test)),
			array('Te', array($test)),
			array('TP', array($test)),
			array('leh', array($test)),
			array('Pro', array($foobar, $test)),
		);
	}

	/**
	* @covers Procedure::attributeLabels
	* @todo		Implement testAttributeLabels().
	*/
	public function testAttributeLabels()
	{
		$expected = array(
			'id' => 'ID',
			'term' => 'Term',
			'short_format' => 'Short Format',
			'default_duration' => 'Default Duration',
		);

		$this->assertEquals($expected, $this->model->attributeLabels());
	}

	/**
	* @covers Procedure::search
	* @todo		Implement testSearch().
	*/
	public function testSearch()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}

	/**
	* @dataProvider dataProvider_ProcedureSearch
	*/
	public function testGetList_ValidTerms_ReturnsValidResults($term, $data)
	{
		$results = Procedure::getList($term);
		$this->assertEquals($data, $results);
	}

	public function testGetList_InvalidTerm_ReturnsEmptyResults()
	{
		$results = Procedure::getList('Qux');
		$this->assertEquals(array(), $results);
	}

	public function testGetList_RestrictBooked()
	{
		$this->assertEquals(
			array(array('id'=>'1','term'=>'Foobar Procedure','default_duration'=>60,'is_common'=>false)),
			Procedure::getList('Proc', 'booked')
		);
	}

	public function testGetList_RestrictUnbooked()
	{
		$this->assertEquals(
			array(array('id'=>2,'term'=>'Test Procedure','default_duration'=>20,'is_common'=>false)),
			Procedure::getList('Proc', 'unbooked')
		);
	}

	/**
	* @covers Procedure::getListBySubspecialty
	* @todo		Implement testGetListBySubspecialty().
	*/
	public function testGetListBySubspecialty() {
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete('This test has not been implemented yet.');
	}
}
