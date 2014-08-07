<?php
class MeasurementGlucoseLevel extends Measurement
{
	public function tableName()
	{
		return 'measurement_glucose_level';
	}

	public function rules()
	{
		return array(
			array('glucose_level','numerical','integerOnly'=>true),
		);
	}

	public function getValueField()
	{
		return 'glucose_level';
	}
}
