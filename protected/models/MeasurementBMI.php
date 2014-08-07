<?php
class MeasurementBMI extends Measurement
{
	public function tableName()
	{
		return 'measurement_bmi';
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
