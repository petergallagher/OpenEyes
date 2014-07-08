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

	/**
	 * List FHIR 'supported' profiles for the system
	 *
	 * http://hl7.org/implement/standards/fhir/conformance-definitions.html#Conformance.profile
	 * From our point of view, this means profiles specific to each
	 * internal resource type.
	 *
	 * @return ResourceReference[]
	 */
	public function listFhirSupportedProfiles()
	{
		$refs = array();
		foreach ($this->resources as $class) {
			if (!\Yii::app()->fhirMarshal->isStandardType($class::getFhirType())) continue;
			$refs[] = new ExternalReference($class::getOeFhirProfile());
		}
		return $refs;
	}

	/**
	 * Describe FHIR REST server resources supported by the system
	 *
	 * http://hl7.org/implement/standards/fhir/conformance-definitions.html#Conformance.rest.resource
	 *
	 * @return array
	 */
	public function describeFhirServerResources()
	{
		$types = array();
		foreach ($this->resources as $resource_class) {
			$type = $resource_class::getFhirType();
			if (!\Yii::app()->fhirMarshal->isStandardType($type)) continue;

			$service_class = \Yii::app()->service->getInternalServiceClassForResource($resource_class);

			if (!isset($types[$type])) {
				$types[$type] = array('ops' => array(), 'sps' => array());
			}

			$types[$type]['ops'] = array_unique(array_merge($types[$type]['ops'], $service_class::getSupportedOperations()));
			$types[$type]['sps'] += $service_class::getSupportedSearchParams();
		}

		$resources = array();
		foreach ($types as $type => $data) {
			$res = (object)array(
				'type' => $type,
				'operation' => array(),
				'readHistory' => false,
				'updateCreate' => false,
				'searchParam' => array(),
			);

			if (in_array(InternalService::OP_READ, $data['ops'])) $data['ops'][] = 'vread';
			foreach ($data['ops'] as $op) {
				$res->operation[] = (object)array('code' => $op);
			}

			foreach ($data['sps'] as $sp_name => $sp_type) {
				if ($sp_name == 'id') $sp_name == '_id';
				$res->searchParam[] = (object)array("name" => $sp_name, "type" => $sp_type);
			}

			$resources[] = $res;
		}

		return $resources;
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
