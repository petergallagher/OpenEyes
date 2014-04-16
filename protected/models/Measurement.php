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
abstract class Measurement extends BaseActiveRecordVersioned {

    protected $patient_measurement;
    
    public function getErrors() {
        return array_merge($this->getErrors(), $this->getPatientMeasurement()->getErrors());
    }
    
//    public function beforeValidate() {
//        return parent::beforeValidate() && $this->getPatientMeasurement()->validate();
//    }

    public function beforeSave() {
        $this->getPatientMeasurement()->patient_id = $this->getPatient_id();
        $psaved = parent::beforeSave();
        $saved = $this->getPatientMeasurement()->save();
        $this->patient_measurement_id = $this->getPatientMeasurement()->id;
        return $psaved && $saved;
    }

    public function getPatient_id() {
        return $this->getPatientMeasurement()->patient_id;
    }

    public function setPatient_id($id) {
        $this->getPatientMeasurement()->patient_id = $id;
    }

    public function getPatient_measurement_id() {
        return $this->getPatientMeasurement()->id;
    }

    public function getPatientMeasurement() {
        if (!$this->patient_measurement) {
            if ($this->isNewRecord) {
                $this->patient_measurement = new PatientMeasurement();
                $this->patient_measurement->measurement_type_id = $this->getMeasurementType()->id;
            } else {
                $this->patient_measurement = PatientMeasurement::model()->findByPk($this->patient_measurement_id);
            }
        }
        return $this->patient_measurement;
    }

    public function getPatient() {
        return $this->getPatientMeasurement()->patient;
    }

    public function getMeasurementType() {
        return MeasurementType::model()->find('class_name=:class_name', array(':class_name' => get_class($this)));
    }

}
