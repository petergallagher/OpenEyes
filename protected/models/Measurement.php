<?php

abstract class Measurement extends BaseActiveRecordVersioned
{
	private $patient_measurement;

	public function getMeasurementType()
	{
		return MeasurementType::model()->findByClassName(get_class($this));
	}

	public function getPatientMeasurement()
	{
		if (!isset($this->patient_measurement)) {
			if($this->isNewRecord) {
				$this->patient_measurement = new PatientMeasurement();
				$this->patient_measurement->measurement_type_id = $this->getMeasurementType()->id;
			} else {
				$this->patient_measurement = PatientMeasurement::model()->findByPk($this->patient_measurement_id);
			}
		}
		return $this->patient_measurement;
	}

	public function getPatient_id()
	{
		return $this->getPatientMeasurement()->patient_id;
	}

	public function setPatient_id($id)
	{
		$this->getPatientMeasurement()->patient_id = $id;
	}

	/**
	 * Attach this measurement to an Episode or Event
	 *
	 * @param Episode|Event $entity
	 * @param boolean $origin
	 * @return MeasurementReference
	 */
	public function attach($entity, $origin = false)
	{
		$ref = new MeasurementReference;
		$ref->patient_measurement_id = $this->getPatientMeasurement()->id;
		$ref->origin = $origin;

		if ($entity instanceof Episode) {
			$ref->episode_id = $entity->id;
		} elseif ($entity instanceof Event) {
			$ref->event_id = $entity->id;
		} else {
			throw new Exception("Can only attach measurements to Episodes or Events, was passed an object of type " . get_class($entity));
		}

		$ref->save();
		return $ref;
	}

	/**
	 * Return true if the entity is the origin of the measurement
	 *
	 * @param Episode|Event $entity
	 * @return boolean
	 */
	public function isOrigin($entity)
	{
		$criteria = new CDbCriteria;
		$criteria->addCondition('patient_measurement_id = :pid');
		$criteria->params[':pid'] = $this->getPatientMeasurement()->id;
		$criteria->addCondition('origin = 1');

		if ($entity instanceof Episode) {
			$criteria->addCondition('episode_id = :eid');
			$criteria->params[':eid'] = $entity->id;
		} elseif ($entity instanceof Event) {
			$criteria->addCondition('event_id = :eid');
			$criteria->params[':eid'] = $entity->id;
		} else {
			throw new Exception("Unsupported entity type: ".get_class($entity));
		}

		return (boolean)MeasurementReference::model()->find($criteria);
	}

	public function afterDelete()
	{
		$pm = $this->getPatientMeasurement();

		$criteria = new CDbCriteria;
		$criteria->addCondition('patient_measurement_id = :pid');
		$criteria->params[':pid'] = $pm->id;

		MeasurementReference::model()->deleteAll($criteria);

		if (!$pm->delete()) {
			return false;
		}

		return parent::afterDelete();
	}

	/**
	 * Dissociative the entity from the measurement
	 *
	 * @param Episode|Event $entity
	 */
	public function dissociate($entity)
	{
		$criteria = new CDbCriteria;
		$criteria->addCondition('patient_measurement_id = :pid');
		$criteria->params[':pid'] = $this->getPatientMeasurement()->id;

		if ($entity instanceof Episode) {
			$criteria->addCondition('episode_id = :eid');
			$criteria->params[':eid'] = $entity->id;
		} elseif ($entity instanceof Event) {
			$criteria->addCondition('event_id = :eid');
			$criteria->params[':eid'] = $entity->id;
		} else {
			throw new Exception("Unsupported entity type: ".get_class($entity));
		}
		
		MeasurementReference::model()->delete($criteria);
	}

	protected function afterValidate()
	{
		$this->getPatientMeasurement()->validate();

		foreach ($this->getPatientMeasurement()->getErrors() as $attribute => $errors) {
			foreach ($errors as $error) {
				$this->addError($attribute, $error);
			}
		}

		parent::afterValidate();
	}

	protected function beforeSave()
	{
		if (!parent::beforeSave() || !$this->getPatientMeasurement()->save()) return false;

		$this->patient_measurement_id = $this->getPatientMeasurement()->id;
		return true;
	}

	static public function isMeasurementClass($class_name)
	{
		$a = new $class_name;
		return ($a instanceof self);
	}

	public function getValue()
	{
		return $this->{$this->valueField};
	}

	public function getSuffix()
	{
		return false;
	}

	public function getValueText()
	{
		return $this->suffix ? $this->value.' '.$this->suffix : $this->value;
	}

	public function setValue($value)
	{
		$this->{$this->valueField} = $value;
	}
}
