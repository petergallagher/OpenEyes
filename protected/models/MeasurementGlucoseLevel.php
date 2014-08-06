<?php
class MeasurementGlucoseLevel extends Measurement
{
	public function tableName()
	{
		return 'measurement_glucose_level';
	}

	public function getValueField()
	{
		return 'glucose_level';
	}
}
