<?php
class MeasurementAirwayClass extends Measurement
{
	public function tableName()
	{
		return 'measurement_airway_class';
	}

	public function getValueField()
	{
		return 'airway_class';
	}
}
