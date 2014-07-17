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

	/**
	 * Merges and adds a package.
	 *
	 * @param string $name the name of the package.
	 * @param array $definition the definition array of the package.
	 * @see CClientScript::packages.
	 * @return CClientScript the CClientScript object itself (to support method chaining, available since version 1.1.10).
	 */
	public function addPackage($name,$definition)
	{
		$existingPackage = @$this->packages[$name] ?: array();
		$definition = CMap::mergeArray($definition, $existingPackage);
		$this->packages[$name]=$definition;
		return $this;
	}

	/**
	 * Creates a package. Will not add referenced asset files that do not exist on the filesystem.
	 * @param  string $name the name of the package
	 * @param  array $definition the definition array of the package.
	 * @return array The formatted package array definition
	 */
	public function createPackage($name,$definition)
	{
		$path = Yii::getPathOfAlias($definition['basePath']);

		foreach(array('css','js') as $type) {
			foreach($definition[$type] as $i => $file) {
				if (!file_exists($path.DIRECTORY_SEPARATOR.$file)) {
					unset($definition[$type][$i]);
				}
			}
		}

		return $definition;
	}
}
