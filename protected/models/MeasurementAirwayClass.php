<?php
class MeasurementAirwayClass extends Measurement
{
	public function tableName()
	{
		return 'measurement_airway_class';
	}

	public function rules()
	{
		return array(
			array('airway_class','numerical','integerOnly' => true,'min' => 1,'max' => 4),
		);
	}

	public function getValueField()
	{
		return 'airway_class';
	}
}
