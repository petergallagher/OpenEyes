<?php
class MeasurementBloodPressure extends Measurement
{
	public function tableName()
	{
		return 'measurement_blood_pressure';
	}

	public function getValueField()
	{
		return array('bp_systolic','bp_diastolic');
	}

	public function __toString()
	{
		return $this->bp_systolic.'/'.$this->bp_diastolic;
	}

	public function setValue($params, $second=false)
	{
		if ($second) {
			$this->bp_systolic = $params;
			$this->bp_diastolic = $second;
		} else {
			$this->bp_systolic = $params[0];
			$this->bp_diastolic = $params[0];
		}
	}
}
