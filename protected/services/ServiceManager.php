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

class ServiceManager extends \CApplicationComponent
{
	public $internal_services = array();

	private $services = array();

	/**
	 * @param string $name
	 * @return Service
	 */
	public function __get($name)
	{
		if (!($service = $this->getService($name))) {
			throw new \Exception("Service '{$name}' not defined");
		}
		return $service;
	}

	public function __call($name, $args)
	{
		$id = array_shift($args);
		return $this->getReference($name, $id);
	}

	/**
	 * @param string $name
	 * @return Service|null
	 */
	public function getService($name)
	{
		if (!array_key_exists($name, $this->services)) {
			foreach ($this->internal_services as $service_class) {
				if ($service_class::getServiceName() == $name) {
					$service = $service_class::load();
					if (!$service instanceof InternalService) {
						throw new \Exception("Invalid internal service class: '{$service_class}'");
					}

					$this->services[$name] = $service;
					break;
				}
			}
			if (!isset($this->services[$name])) $this->services[$name] = null;
		}
		return $this->services[$name];
	}

	/**
	 * @param string $service_name
	 * @param scalar $id
	 */
	public function getReference($service_name, $id)
	{
		return $this->{$service_name}->getReference($id);
	}

	/**
	 * @param string $resource_class
	 * @return InternalService
	 */
	public function getInternalServiceForResource($resource_class)
	{
		foreach ($this->internal_services as $service_class) {
			if ($service_class::getResourceClass() == $resource_class) {
				return $this->getService($service_class::getServiceName());
			}
		}

		throw new \Exception("No internal service found for resource class '{$resource_class}'");
	}

	/**
	 * @param string $resource_class
	 * @return string
	 */
	public function getInternalServiceClassForResource($resource_class)
	{
		foreach ($this->internal_services as $service_class) {
			if ($service_class::getResourceClass() == $resource_class) return $service_class;
		}

		throw new \Exception("No internal service found for resource class '{$resource_class}'");
	}

	/**
	 * Find an internal service for the specified FHIR resource type, using tags to differentiate if necessary
	 *
	 * @param string[] $profiles
	 * @return InternalService|null Null if resource type is supported but no service matched the profile
	 */
	public function getFhirService($fhir_type, array $profiles)
	{
		$resource_class = \Yii::app()->fhirMap->getOeResourceTypeByProfile($fhir_type, $profiles);
		if (!$resource_class) return null;

		return $this->getInternalServiceForResource($resource_class);
	}

	/**
	 * Convert a FHIR resource type and ID to an internal service reference
	 *
	 * @param string $fhir_type
	 * @param string $fhir_id
	 * @return InternalReference|null Null if resource type is supported but no mapping found
	 */
	public function fhirIdToReference($fhir_type, $fhir_id)
	{
		if (!preg_match('/^(?:(\w+)-)?(\d+)$/', $fhir_id, $m)) return null;
		list (, $prefix, $id) = $m;

		$resource_class = \Yii::app()->fhirMap->getOeResourceTypeByPrefix($fhir_type, $prefix);
		if (!$resource_class) return null;

		return $this->getInternalServiceForResource($resource_class)->getReference($id);
	}

	/**
	 * Convert an internal service name and ID to a FHIR relative URL
	 *
	 * @param InternalService $service
	 * @param int $id
	 * @return string
	 */
	public function serviceAndIdToFhirUrl(InternalService $service, $id)
	{
		$resource_class = $service::getResourceClass();

		if (!($fhir_type = $resource_class::getFhirType())) {
			throw new \Exception("No FHIR resource type configured for service '{$service::getServiceName()}'");
		}

		$prefix = $resource_class::getFhirPrefix() ? ($resource_class::getFhirPrefix() . '-') : '';

		return "{$fhir_type}/{$prefix}{$id}";
	}

	/**
	 * Convert an internal reference to a FHIR relative URL
	 *
	 * @param InternalReference $ref
	 * @return string
	 */
	public function referenceToFhirUrl(InternalReference $ref)
	{
		return $this->serviceAndIdToFhirUrl($ref->getService(), $ref->getId());
	}
}
