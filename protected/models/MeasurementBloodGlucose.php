<?php
class MeasurementBloodGlucose extends Measurement
{
	public function tableName()
	{
		return 'measurement_blood_glucose';
	}

	public function rules()
	{
		return array(
			array('blood_glucose','numerical','integerOnly'=>true, 'min' => 0, 'max' => 20),
		);
	}

	public function getValueField()
	{
		return 'blood_glucose';
	}
}
