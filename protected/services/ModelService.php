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
 * Services that provide access to the OE data model
 */
abstract class ModelService extends InternalService
{
	/**
	 * Default set of operations for a model service, override if required
	 */
	static protected $operations = array(self::OP_READ, self::OP_UPDATE, self::OP_CREATE);

	/**
	 * The primary model class for this service
	 *
	 * @var string
	 */
	static protected $primary_model;

	static public function load(array $params = array())
	{
		return new static();
	}

	/**
	 * Get the class name of the reference type for this service's resources
	 *
	 * @return string
	 */
	static public function getReferenceClass()
	{
		$class = parent::getReferenceClass();
		return class_exists($class) ? $class : 'Service\\ModelReference';
	}

	/**
	 * @param scalar $id
	 * @return ModelReference
	 */
	public function getReference($id)
	{
		$ref_class = static::getReferenceClass();
		$model_class = static::$primary_model;
		return new $ref_class($this, $id, $model_class::model());
	}

	/**
	 * @param int $id
	 * @return Resource
	 */
	public function read($id)
	{
		if (!$this->supportsOperation(self::OP_READ)) {
			throw new ProcessingNotSupported("Read operation not supported");
		}

		return $this->modelToResource($this->readModel($id));
	}

	/**
	 * @param Resource $resource
	 * @return InternalReference
	 */
	public function create(Resource $resource)
	{
		if (!$this->supportsOperation(self::OP_CREATE)) {
			parent::create($resource);
		}

		$class = static::$primary_model;
		$model = new $class;
		$this->resourceToModel($resource, $model);

		return $this->getReference($model->id);
	}

	/**
	 * @param int $id
	 * @return BaseActiveRecord
	 */
	protected function readModel($id)
	{
		if (!($model = $this->model->findByPk($id))) {
			throw new NotFound(static::getServiceName() . " with ID '$id' not found");
		}

		return $model;
	}

	/**
	 * @param BaseActiveRecord $model
	 * @return Resource
	 */
	public function modelToResource($model)
	{
/*
		$res = parent::modelToResource($patient);
		$res->nhs_num = $patient->nhs_num;
		$res->hos_num = $patient->hos_num;
		$res->title = $patient->contact->title;
		$res->family_name = $patient->contact->last_name;
		$res->given_name = $patient->contact->first_name;
		$res->gender = $patient->gender;
		$res->birth_date = $patient->dob;
		$res->date_of_death = $patient->date_of_death;
		$res->primary_phone = $patient->contact->primary_phone;
		$res->addresses = array_map(array('services\PatientAddress', 'fromModel'), $patient->contact->addresses);

		if ($patient->gp_id) $res->gp_ref = \Yii::app()->service->Gp($patient->gp_id);
		if ($patient->practice_id) $res->prac_ref = \Yii::app()->service->Practice($patient->practice_id);
		foreach ($patient->commissioningbodies as $cb) {
			$res->cb_refs[] = \Yii::app()->service->CommissioningBody($cb->id);
		}
		$res->care_providers = array_merge(array_filter(array($res->gp_ref, $res->prac_ref)), $res->cb_refs);

		return $res;
*/

		if (!isset($this::$resource_map[get_class($model)])) {
			throw new Exception("Unknown object type: ".get_class($model));
		}

		$class = static::getResourceClass();

		$resource = new $class(array('id' => $model->id, 'last_modified' => strtotime($model->last_modified_date)));

		foreach ($this::$resource_map[get_class($model)] as $key => $def) {
			if (is_string($def)) {
				$resource->$key = $model->$def;
			} else if (isset($def['relation']) && isset($def['field'])) {
				$relation = $def['relation'];
				$field = $def['field'];
				$resource->$key = $model->$relation->$field;
			} else if (isset($def['data_model'])) {
				if (isset($def['relation'])) {
					$relation = $def['relation'];
					$related_data = $model->$relation->$key;
				} else {
					$related_data = $model->$key;
				}

				$resource->$key = array_map(array('services\\'.$def['data_model'], 'modelToResource'), $related_data);

			} else if (isset($def['reference'])) {
				$reference = $def['reference'];
				$name = $def['name'];
				$resource->$name = \Yii::app()->service->$reference($model->$key);
			}
		}

		return $resource;
	}

	/**
	 * @param Resource $resource
	 * @param BaseActiveRecord $model
	 */
	public function resourceToModel($resource, $model)
	{
		throw new ProcessingNotSupported("Can't write resources of type '" . get_class($resource) . "' to model layer");
	}

	/**
	 * Get an instance of the model class to fill in with search details
	 *
	 * @return BaseActiveRecord
	 */
	protected function getSearchModel()
	{
		$class = static::$primary_model;
		return new $class(null);
	}

	/**
	 * Get a list of resources from an AR data provider
	 *
	 * @param CActiveDataProvider $dataProvider
	 * @return Resource[]
	 */
	protected function getResourcesFromDataProvider(\CActiveDataProvider $provider)
	{
		$class = static::getResourceClass();
		$resources = array();
		foreach ($provider->getData() as $model) {
			$resources[] = $this->modelToResource($model);
		}
		return $resources;
	}

	/*
	 * Save model object and throw a service layer exception on failure
	 *
	 * @param BaseActiveRecord $model
	 */
	protected function saveModel(\BaseActiveRecord $model)
	{
		if (!$model->save()) {
			throw new ValidationFailure("Validation failure on " . get_class($model), $model->errors);
		}
	}
}
