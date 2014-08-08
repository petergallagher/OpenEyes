<?php
class MeasurementO2 extends Measurement
{
	public function tableName()
	{
		return 'measurement_o2';
	}

	public function rules()
	{
		return array(
			array('o2','numerical','integerOnly'=>true),
		);
	}

	public function getValueField()
	{
		return 'o2';
	}

	public function getSuffix()
	{
		return 'L/min';
	}
}
