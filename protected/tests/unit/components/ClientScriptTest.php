<?php
/**
 * (C) OpenEyes Foundation, 2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (C) 2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

class ClientScriptTest extends PHPUnit_Framework_TestCase
{
	private $time;
	private $clientScript;
	private $cacheBuster;

	public function setUp()
	{
		$this->time = date('YmdH');

		$this->cacheBuster = new CacheBuster();
		$this->cacheBuster->init();
		$this->cacheBuster->time = $this->time;

		$this->clientScript = new ClientScript($this->cacheBuster);
		$this->clientScript->init();
	}

	public function tearDown()
	{
	}

	public function testCacheBustedUrls()
	{
		// We're testing that the unifyScripts() methods correctly adds cache-busted urls to
		// script and css files.

		$this->clientScript->registerScriptFile('script1.js');
		$this->clientScript->registerScriptFile('script2.js');
		$this->clientScript->registerCssFile('style1.css');
		$this->clientScript->registerCssFile('style2.css');

		$output = '';
		$this->clientScript->render($output);

		$this->assertTrue(strpos($output, "script1.js?{$this->time}") !== false,
			'script1.js should be cache-busted');

		$this->assertTrue(strpos($output, "script2.js?{$this->time}") !== false,
			'script2.js should be cache-busted');

		$this->assertTrue(strpos($output, "style1.css?{$this->time}") !== false,
			'style.css should be cache-busted');

		$this->assertTrue(strpos($output, "style2.css?{$this->time}") !== false,
			'style.css should be cache-busted');
	}

	public function testAddPackage()
	{
		$name = 'testpackage';

		$definition1 = array(
			'css' => array('style1.css'),
			'js' => array('script1.js'),
			'basePath' => 'application.assets',
			'depends' => array()
		);

		$definition2 = array(
			'css' => array('style2.css'),
			'js' => array('script2.js'),
			'basePath' => 'application.assets.new',
			'depends' => array('test')
		);

		$expected = array(
			"{$name}" => array(
				'css' => array('style1.css','style2.css'),
				'js' => array('script1.js','script2.js'),
				'basePath' => 'application.assets.new',
				'depends' => array('test')
			)
		);

		$this->clientScript->addPackage($name, $definition1);
		$this->clientScript->addPackage($name, $definition2);

		$this->assertEquals($this->clientScript->packages, $expected,
			'When adding a package, it should be merged with the existing package (if it exists)');
	}

	// The main purpose of cleanPackage() is to accept a package definition, and
	// return a new package definition with file references that actually exist on the filesytem.
	public function testCleanPackage()
	{
		$pathAlias = 'application.tests.fixtures.assets';
		$path = Yii::getPathOfAlias($pathAlias);

		$time = time();

		$styleName = "css/style{$time}.css";
		$stylePath = "{$path}/{$styleName}";

		$scriptName = "js/script{$time}.js";
		$scriptPath = "{$path}/{$scriptName}";

		file_put_contents($stylePath, '');
		file_put_contents($scriptPath, '');

		$definition = array(
			'css' => array(
				$styleName,
				'css/style'.($time+1).'.css'
			),
			'js' => array(
				$scriptName,
				'js/script'.($time+1).'.js'
			),
			'basePath' => $pathAlias
		);

		$expected = array(
			'css' => array($styleName),
			'js' => array($scriptName),
			'basePath' => $pathAlias
		);

		$package = $this->clientScript->cleanPackage($definition);

		$this->assertEquals($package, $expected,
			'The package returned from cleanPackage should only contain files that exist on the filesystem');

		unlink($stylePath);
		unlink($scriptPath);
	}

	public function resetRemovePackageScripts()
	{
		$this->markTestIncomplete('Tests not implemented yet');
	}
}

