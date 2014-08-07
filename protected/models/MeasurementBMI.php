<?php
class MeasurementBMI extends Measurement
{
	public function tableName()
	{
		return 'measurement_bmi';
	}

	public function rules()
	{
		return array(
			array('bmi','numerical','integerOnly'=>false,'min' => 10,'max'=>50),
		);
	}

	public function getValueField()
	{
		return 'bmi';
	}

	public function getSuffix()
	{
		return 'kg/m^2';
	}
}
