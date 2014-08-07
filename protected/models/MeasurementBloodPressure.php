<?php
class MeasurementBloodPressure extends Measurement
{
	public function tableName()
	{
		return 'measurement_blood_pressure';
	}

	public function rules()
	{
		return array(
			array('bp_systolic','numerical','integerOnly'=>true,'min' => 0,'max'=>240),
			array('bp_diastolic','numerical','integerOnly'=>true,'min' => 0,'max' => 150),
		);
	}

	public function getValueField()
	{
		return array('bp_systolic','bp_diastolic');
	}

	public function getValueText()
	{
		return $this->bp_systolic.'/'.$this->bp_diastolic.' mmHg';
	}

	public function getValue()
	{
		return array(
			'bp_systolic' => $this->bp_systolic,
			'bp_diastolic' => $this->bp_diastolic,
		);
	}

	public function setValue($params, $second=false)
	{
		if ($second) {
			$this->bp_systolic = $params;
			$this->bp_diastolic = $second;
		} else {
			$this->bp_systolic = $params['bp_systolic'];
			$this->bp_diastolic = $params['bp_diastolic'];
		}
	}
}
