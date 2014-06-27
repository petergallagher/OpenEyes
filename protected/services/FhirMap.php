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

class FhirMap extends \CApplicationComponent
{
	public $resources = array();

	/**
	 * @param string $fhir_type
	 * @param string|null $prefix
	 * @return string|null Null if the resource type is supported but no specific service was found
	 */
	public function getOeResourceTypeByPrefix($fhir_type, $prefix)
	{
		$candidates = $this->getOeResourceTypeCandidates($fhir_type);

		foreach ($candidates as $type) {
			if ($type::getFhirPrefix() == $prefix) return $type;
		}

		return null;
	}

	/**
	 * @param string $fhir_type
	 * @param array $profiles
	 * @return string|null Null if the resource type is supported but no specific service was found
	 */
	public function getOeResourceTypeByProfile($fhir_type, array $profiles)
	{
		$candidates = $this->getOeResourceTypeCandidates($fhir_type);

		// Unambiguous resource type
		if (count($candidates) == 1) {
			$type = reset($candidates);
			if (!$type::getFhirPrefix()) return $type;
		}

		foreach ($candidates as $type) {
			if (in_array($type::getOeFhirProfile(), $profiles)) return $type;
		}

		return null;
	}

	private function getOeResourceTypeCandidates($fhir_type)
	{
		$candidates = array_filter(
			$this->resources,
			function ($local_type) use ($fhir_type) {
				return $local_type::getFhirType() == $fhir_type;
			}
		);

		if (!$candidates) {
			throw new NotImplemented("Unsupported resource type: '{$fhir_type}'");
		}

		return $candidates;
	}
}
