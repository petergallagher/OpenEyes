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

class ClientScript extends CClientScript
{

	/**
	 * Remove package scripts. Read through all scripts (and dependant scripts) defined
	 * in a package, and add them to the scriptMap to prevent outputting them in a response.
	 * @param  string $packageName Name of the package.
	 */
	/*
	public function removePackageScripts($packageName=null)
	{
		if (!$packageName) return;
		$package = $this->packages[$packageName];

		// Process dependencies first.
		if (isset($package['depends']) && $package['depends']) {
			foreach($package['depends'] as $dependantPackage) {
				$this->removePackageScripts($dependantPackage);
			}
		}

		// Now remove all css and js files defined in this package.
		foreach(array('js','css') as $type) {
			if (isset($package[$type])) {
				foreach($package[$type] as $file) {
					$this->scriptMap[basename($file)] = false;
				}
			}
		}
	}
	*/

	/**
	 * Renders the registered scripts.
	 * @param string $output the existing output that needs to be inserted with script tags
	 */
	/*
	public function render(&$output)
	{
		// Remove all core and core-dependant registered scripts for AJAX requests.
		if (Yii::app()->request->isAjaxRequest) {
			$this->removePackageScripts('core');
		}
		parent::render($output);
	}
	*/

	/**
	 * Extending unifyScripts in order to hook the cache buster in at the right
	 * point in the render method
	 */
	protected function unifyScripts()
	{
		parent::unifyScripts();

		$cacheBuster = Yii::app()->cacheBuster;

		// JS
		foreach ($this->scriptFiles as $pos => $scriptFiles) {
			foreach ($scriptFiles as $key => $scriptFile) {
				unset($this->scriptFiles[$pos][$key]);
				// Add cache buster string to url.
				$scriptUrl = $cacheBuster->createUrl($scriptFile);
				$this->scriptFiles[$pos][$scriptUrl] = $scriptFile;
			}
		}

		// CSS
		foreach ($this->cssFiles as $cssFile => $media) {
			unset($this->cssFiles[$cssFile]);
			// Add cache buster string to url.
			$cssFile = $cacheBuster->createUrl($cssFile);
			$this->cssFiles[$cssFile] = $media;
		}
	}
}
