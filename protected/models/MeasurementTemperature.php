<?php
class MeasurementTemperature extends Measurement
{
	public function tableName()
	{
		return 'measurement_temperature';
	}

	public function rules()
	{
		return array(
			array('temperature','numerical','integerOnly' => true),
		);
	}

	public function getValueField()
	{
		return 'temperature';
	}

	public function getSuffix()
	{
		return '°C';
	}
}
