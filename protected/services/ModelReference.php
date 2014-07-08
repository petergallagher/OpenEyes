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
 * Reference to a resource within the OE data model
 */
class ModelReference extends InternalReference
{
	protected $model;

	public function __construct(ModelService $service, $id, \BaseActiveRecord $model)
	{
		parent::__construct($service, $id);
		$this->model = $model;
	}

	/**
	 * @return int
	 */
	public function getLastModified()
	{
		return strtotime($this->readModel()->last_modified_date);
	}

	/**
	 * @return Resource
	 */
	public function fetch()
	{
		if (!$this->service->supportsOperation(InternalService::OP_READ)) {
			parent::fetch();
		}

		return $this->service->modelToResource($this->readModel());
	}

	/**
	 * @param Resource $resource
	 */
	public function update(Resource $resource)
	{
		if (!$this->service->supportsOperation(InternalService::OP_UPDATE)) {
			parent::update($resource);
		}

		$this->service->resourceToModel($resource, $this->readModel());
	}

	/**
	 * @return BaseActiveRecord
	 */
	private function readModel()
	{
		if (!($model = $this->model->findByPk($this->id))) {
			throw new NotFound(static::getServiceName() . " with ID '{$this->id}' not found");
		}

		return $model;
	}

	/**
	 * @return string
	 */
	public function getModelClass()
	{
		return \CHtml::modelName($this->model);
	}
}
