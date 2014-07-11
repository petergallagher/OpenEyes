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

/**
 * This AssetManager class provide convenient methods for getting published
 * asset paths and publishing/registering script/css files.
 *
 * @example
 * $assetManager = Yii::app()->getAssetManager();
 *
 * // Create a cache busted URL to an image in a shared folder:
 * $url = $assetManager->createUrl('shared/img/ajax-loader.gif');
 */
class AssetManager extends CAssetManager
{
	const BASE_PATH_ALIAS = 'application.assets';

	/**
	 * ClientScript component reference.
	 * @var ClientScript
	 */
	protected $clientScript;

	/**
	 * CacheBuster component reference.
	 * @var CacheBuster
	 */
	protected $cacheBuster;

	/**
	 * Initializes the component.
	 */
	public function init()
	{
		$this->setClientScript(Yii::app()->clientScript);
		$this->setCacheBuster(Yii::app()->cacheBuster);
		parent::init();
	}

	/**
	 * Set the ClientScript reference.
	 * @param ClientScript $clientScript The ClientScript instance.
	 */
	public function setClientScript(ClientScript $clientScript)
	{
		$this->clientScript = $clientScript;
	}

	/**
	 * Set the CacheBuster reference.
	 * @param CacheBuster $cacheBuster The CacheBuster instance.
	 */
	public function setCacheBuster(CacheBuster $cacheBuster)
	{
		$this->cacheBuster = $cacheBuster;
	}

	/**
	 * Returns the published asset path for a given asset directory alias.
	 * @param  string $alias The alias to the assets.
	 * @return string        The publish assets path.
	 */
	public function getPublishedPathOfAlias($alias = null)
	{
		return $this->publish(Yii::getPathOfAlias($alias ?: static::BASE_PATH_ALIAS), false, -1);
	}

	/**
	 * Creates an absolute URL to a published asset. Eg: '/path/to/hash/asset.gif?cachebusted'
	 * @param  string $path          The path to the asset. Eg: 'img/cat.gif'
	 * @param  string $basePathAlias The alias path to the base location of the asset.
	 * Eg: 'application.modules.mymodule.assets'
	 * @return string                The absolute path to the published asset.
	 */
	public function createUrl($path = null, $basePathAlias = null, $bustCache = true)
	{
		if ($basePathAlias !== false) {
			$basePath = $this->getPublishedPathOfAlias($basePathAlias).'/';
			$url = $basePath . $path;
		}
		else {
			$url = Yii::app()->createUrl($path);
		}

		if ($bustCache) {
			$url = $this->cacheBuster->createUrl($url);
		}

		return $url;
	}

	/**
	 * Returns the absolute filesystem path to the published asset.
	 * @param  string $path         Relative path to asset.
	 * @param  null|string $alias   Alias path to the base location of the asset.
	 * @return string The absolute path.
	 */
	public function getPublishedPath($path = '', $alias = null)
	{
		$parts = array(
			Yii::getPathOfAlias('webroot'),
			ltrim($this->getPublishedPathOfAlias($alias), '/'),
			$path
		);

		return implode(DIRECTORY_SEPARATOR, $parts);
	}

	/**
	 * Registers a published a script file.
	 * @param  string $script   The script path. Eg: 'js/script.js'
	 * @param  [type] $basePathAlias The alias for the basepath.
	 * Eg: 'application.modules.mymodule.assets'
	 */
	public function registerScriptFile($script = '', $basePathAlias = null)
	{
		$path = $this->createUrl($script, $basePathAlias, false);
		$this->clientScript->registerScriptFile($path);
	}

	/**
	 * Registers a published stylesheet.
	 * @param  string            $style          The style path. Eg: 'css/style.css'
	 * @param  null|string|false $basePathAlias  The alias for the basepath.
	 * Eg: 'application.modules.mymodule.assets'
	 */
	public function registerCssFile($style = '', $basePathAlias = null)
	{
		$path = $this->createUrl($style, $basePathAlias, false);
		$this->clientScript->registerCssFile($path);
	}

	public function reset()
	{
		$this->clientScript->reset();
	}
}
