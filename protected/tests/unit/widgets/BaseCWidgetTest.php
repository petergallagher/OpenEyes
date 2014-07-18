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

class MyWidget extends BaseCWidget {

	protected $prop1;
	protected $prop2;

	public function init(ClientScript $clientScript = null)
	{
		$this->prop1 = 'prop1';
		$this->prop2 = 'prop2';
		parent::init($clientScript);
	}
}

class BaseCWidgetTest extends CTestCase
{
	public function setUp()
	{

	}

	private function getMockWidget()
	{
		return $this->getMockBuilder('MyWidget')
			->setMethods(array('getViewFile','renderFile'))
			->getMock();

		return $widget;
	}

	// View contents should be returned
	public function testRenderReturn()
	{
		$returnValue = '<h1>rich</h1>';

		$widget = $this->getMockWidget();

		$widget->expects($this->once())
			->method('getViewFile')
			->will($this->returnValue('view.php'));

		$widget->expects($this->once())
			->method('renderFile')
			->will($this->returnValue($returnValue));

		$widget->init();
		$output = $widget->render('view_name', null, true);

		$this->assertEquals($output, $returnValue,
			'If the return param is set to true, content should be returned from the method call');
	}

	// Widget properties should be merged with view data
	public function testRenderData()
	{
		$data = array(
			'foo' => 'bar',
			'prop2' => 'cat'
		);
		$returnValue = '<h1>rich</h1>';

		$widget = $this->getMockWidget();

		$widget->expects($this->once())
			->method('getViewFile')
			->will($this->returnValue('view.php'));

		$widget->expects($this->once())
			->method('renderFile')
			->with(
				$this->equalTo('view.php'),
				$this->callback(function($arg) {

					$keysExist = (
						array_key_exists('prop1', $arg) &&
						array_key_exists('prop2', $arg) &&
						array_key_exists('foo', $arg)
					);

					// Here we test that data passed as a param does not overwrite class properties.
					$valsMatch = (
						$arg['prop1'] === 'prop1' &&
						$arg['prop2'] === 'prop2' &&
						$arg['foo'] === 'bar'
					);

					return ($keysExist && $valsMatch);
				}),
				$this->equalTo(false)
			);

		$widget->init();
		$widget->render('view_name', $data);
	}

	// public function testRegisterAssets()
	// {
	// 	$widget = new MyWidget();

	// 	$clientScript = $this->getMockBuilder('ClientScript')
	// 		->setMethods(array(
	// 			'addPackage',
	// 			'registerPackage'
	// 		))
	// 		->getMock();

	// 	$clientScript->expects($this->once())
	// 		->method('addPackage');

	// 	$clientScript->expects($this->once())
	// 		->method('cleanPackage')
	// 		->with(
	// 			$this->equalTo(array(
	// 				'js' => array('js/MyWidget.js'),
	// 				'basePath' => $widget->assetBasePath
	// 			))
	// 		);

	// 	$widget->init($clientScript);
	// }
}
