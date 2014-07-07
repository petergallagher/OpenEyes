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

/**
 * Reference to an internal resource
 */
abstract class InternalReference extends ResourceReference
{
	protected $service;
	protected $id;

	/**
	 * @param InternalService $service
	 * @param $id
	 */
	public function __construct(InternalService $service, $id)
	{
		$this->service = $service;
		$this->id = $id;
	}

	/**
	 * @return InternalService
	 */
	public function getService()
	{
		return $this->service;
	}

	/**
	 * @return string
	 */
	public function getServiceName()
	{
		return $this->service->getServiceName();
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return int
	 */
	public function getVersionId()
	{
		return $this->getLastModified();
	}

	/**
	 * @return int
	 */
	public function getLastModified()
	{
		throw new ProcessingNotSupported("Read operation not supported");
	}

	/**
	 * @return Resource
	 */
	public function fetch()
	{
		throw new ProcessingNotSupported("Read operation not supported");
	}

	/**
	 * @param Resource $resource
	 */
	public function update(Resource $resource)
	{
		throw new ProcessingNotSupported("Update operation not supported");
	}

	/**
	 * @return bool
	 */
	public function delete()
	{
		throw new ProcessingNotSupported("Delete operation not supported");
	}

	/**
	 * @param StdClass $fhirObject
	 */
	public function fhirUpdate(\StdClass $fhirObject)
	{
		$this->update($this->service->fhirToResource($fhirObject));
	}

	/**
	 * @return StdClass
	 */
	public function toFhir()
	{
		return (object)array("reference" => \Yii::app()->service->referenceToFhirUrl($this));
	}
}
