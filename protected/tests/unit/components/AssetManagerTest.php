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

class CustomBasePathAliasAssetManager extends AssetManager {
	const BASE_PATH_ALIAS = 'application.tests.fixtures.assets';
}

class AssetManagerTest extends PHPUnit_Framework_TestCase
{
	const BASE_PATH_ALIAS ='application.tests.assets';
	const BASE_URL = 'assets';

	public function setUp()
	{
		$this->globalInstance = Yii::app()->assetManager;

		// As YII uses $_SERVER['SCRIPT_FILENAME'] to build the base asset path, things will
		// certainly break when the unit tests are run, so we have to explicitly set the basepath
		// to something that is valid.
		$this->globalInstance->setBasePath(Yii::getPathOfAlias(self::BASE_PATH_ALIAS));
		$this->globalInstance->setBaseUrl(self::BASE_URL);
	}

	private static function getInstance()
	{
		$instance = new CustomBasePathAliasAssetManager();
		$instance->init();
		$instance->setBasePath(Yii::getPathOfAlias(self::BASE_PATH_ALIAS));
		$instance->setBaseUrl(self::BASE_URL);
		return $instance;
	}

	public function testInstanceCreated()
	{
		$this->assertTrue($this->globalInstance instanceof AssetManager,
			'Yii::app()->assetManager should be an instance of AssetManager');

		$this->assertTrue($this->globalInstance instanceof CAssetManager,
			'AssetManager should extend CAssetManager');

		$cacheBuster = PHPUnit_Framework_Assert::readAttribute($this->globalInstance, 'cacheBuster');
		$this->assertTrue($cacheBuster instanceof CacheBuster,
			'cacheBuster property on AssetManager instance should be an instance of CacheBuster');

		$clientScript = PHPUnit_Framework_Assert::readAttribute($this->globalInstance, 'clientScript');
		$this->assertTrue($clientScript instanceof ClientScript,
			'clientScript property on AssetManager instance should be an instance of ClientScript');
	}

	public function testGetPublishedPathOfAlias()
	{
		$instance = self::getInstance();

		// Test the published path matches the expected published path.
		$alias = CustomBasePathAliasAssetManager::BASE_PATH_ALIAS;
		$publishedPath = $instance->getPublishedPathOfAlias($alias);
		$expectedPublishedPath = $instance->publish(Yii::getPathOfAlias($alias));
		$this->assertEquals($publishedPath, $expectedPublishedPath,
			'The published path of specified alias should match the expected path');

		// Test the published path matches the expected published path *when no alias is specified*.
		$publishedPath = $instance->getPublishedPathOfAlias();
		$expectedPublishedPath = $instance->publish(Yii::getPathOfAlias($alias));
		$this->assertEquals($publishedPath, $expectedPublishedPath,
			'The published path should match the expected path when no alias is specified');
	}

	public function testCreateUrl()
	{
		$instance = self::getInstance();
		$alias = CustomBasePathAliasAssetManager::BASE_PATH_ALIAS;
		$path = 'img/cat.gif';

		// Test the url matches when specifying a path alias.
		$url = $instance->createUrl($path, $alias, false);
		$expectedUrl = $instance->getPublishedPathOfAlias($alias) .'/'. $path;
		$this->assertEquals($expectedUrl, $url,
			'The URL should match when specifying a path alias');

		// Test the url matches when *when no alias is specified*.
		$url = $instance->createUrl($path, null, false);
		$this->assertEquals($expectedUrl, $url,
			'The URL should match when no alias is specified');

		// Test the url matches when an alias path is prevented from being preprended to the path.
		$url = $instance->createUrl($path, false, false);
		$expectedUrl = Yii::app()->createUrl($path);
		$this->assertEquals($expectedUrl, $url,
			'The URL should match when an alias is prevented from being prepended to the path');

		// Test a cache buster string is appended to url.
		$path1 = $path;
		$path2 = $path1.'?cats=rule';

		$url1 = $instance->createUrl($path1, false, true);
		$url2 = $instance->createUrl($path2, false, true);

		$expectedUrl1 = Yii::app()->cacheBuster->createUrl(Yii::app()->createUrl($path));
		$expectedUrl2 = Yii::app()->cacheBuster->createUrl(Yii::app()->createUrl($path2));

		$this->assertEquals($expectedUrl1, $url1,
			'The URL, without query string params, should be cache busted');

		$this->assertEquals($expectedUrl2, $url2,
			'The URL, with query string params, should be cache busted');

		// Test default params.
		$url = $instance->createUrl($path);
		$expectedUrl = Yii::app()->cacheBuster->createUrl($instance->getPublishedPathOfAlias($alias).'/'.$path);
		$this->assertEquals($expectedUrl, $url,
			'The URL should match the expected format when no additional params are specified');
	}

	public function testGetPublishedPath()
	{
		$instance = self::getInstance();
		$alias = CustomBasePathAliasAssetManager::BASE_PATH_ALIAS;
		$path = 'img/cat.gif';

		// Test with specific params.
		$publishedPath = $instance->getPublishedPath($path, $alias);
		$expectedParts = array(
			Yii::getPathOfAlias('webroot'),
			$instance->publish(Yii::getPathOfAlias($alias), false, -1),
			$path
		);
		$expectedPath = implode(DIRECTORY_SEPARATOR, $expectedParts);
		$this->assertEquals($publishedPath, $expectedPath,
			'The published path should match the expected path when an alias is specified');

		// Test with default params.
		$publishedPath = $instance->getPublishedPath($path);
		$expectedParts = array(
			Yii::getPathOfAlias('webroot'),
			$instance->publish(Yii::getPathOfAlias($alias), false, -1),
			$path
		);
		$expectedPath = implode(DIRECTORY_SEPARATOR, $expectedParts);
		$this->assertEquals($publishedPath, $expectedPath,
			'The published path should match the expected path when no alias is specified');
	}

	public function testRegisterFiles()
	{
		/* Test that published files are registered with clientScript */

		$instance = self::getInstance();

		$clientScript = $this->getMockBuilder('ClientScript')
			->setMethods(array('registerCssFile', 'registerScriptFile'))
			->getMock();

		$instance->setClientScript($clientScript);

		$clientScript->expects($this->any())
			->method('registerCssFile')
			->with($instance->createUrl('css/style.css', null, false));

		$clientScript->expects($this->any())
			->method('registerScriptFile')
			->with($instance->createUrl('js/script.js', null, false));

		$instance->registerCssFile('css/style.css');
		$instance->registerScriptFile('js/script.js');
	}

	public function testReset()
	{
		/* Test that the reset method on clientScript is called */

		$instance = self::getInstance();

		$clientScript = $this->getMockBuilder('ClientScript')
			->setMethods(array('reset'))
			->getMock();

		$clientScript->expects($this->at(0))
			->method('reset');

		$instance->setClientScript($clientScript);
		$instance->registerCssFile('css/style.css');
		$instance->registerScriptFile('js/style.js');
		$instance->reset();
	}
}
