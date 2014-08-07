<?php
class MeasurementTemperature extends Measurement
{
	public function tableName()
	{
		return 'measurement_temperature';
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
