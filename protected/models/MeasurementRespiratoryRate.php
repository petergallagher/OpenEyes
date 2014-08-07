<?php
class MeasurementRespiratoryRate extends Measurement
{
	public function tableName()
	{
		return 'measurement_respiratory_rate';
	}

	public function rules()
	{
		return array(
			array('rr','numerical','integerOnly'=>true,'min' => 5, 'max' => 60),
		);
	}

	public function getValueField()
	{
		return 'rr';
	}

	public function getSuffix()
	{
		return 'insp/min';
	}
}
