<?php

/**
 * This is the model class for table "patient_measurement".
 *
 * The followings are the available columns in table 'patient_measurement':
 * @property string $id
 * @property string $patient_id
 * @property string $measurement_type_id
 * @property integer $deleted
 *
 * The followings are the available model relations:
 * @property MeasurementReference[] $measurementReferences
 * @property OphinvisualfieldsFieldMeasurement[] $ophinvisualfieldsFieldMeasurements
 * @property OphinvisualfieldsFieldMeasurementVersion[] $ophinvisualfieldsFieldMeasurementVersions
 * @property MeasurementType $measurementType
 * @property Patient $patient
 */
abstract class Measurement extends BaseActiveRecordVersioned
{

	private $patient_measurement;

	public function beforeValidate()
	{
		// TODO: Merge errors from patient measurement;
		return parent::beforeValidate() && $this->patient_measurement->validate();
	}

	public function beforeSave()
	{
		return parent::beforeSave() && $this->patient_measurement->save();
	}

	public getPatient_id()
	{
		return $this->getPatientMeasurement()->patient_id;
	}

	public setPatient_id($id)
	{
		$this->getPatientMeasurement()->patient_id = $id;
	}

	public getPatientMeasurement()
	{
		if(!$this->patient_measurement) {
			if(isnew) {
				$this->patient_measurement = new PatientMeasurement();
				$this->patient_measurement->measurement_type_id = $this->getMeasurementType()->id;
			} else {
        		$this->patient_measurement = PatientMeasurement::model()->findByPk($this->patient_measurement_id);
			}
		}
		return $this->patient_measurement;
	}

	public getPatient()
	{
		return $this->getPatientMeasurement()->patient;
	}

	public function getMeasurementType()
	{
		return MeasurementType::model()->findByClassName(get_class($this));
	}
}
