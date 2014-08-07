<?php
class MeasurementPulse extends Measurement
{
	public function tableName()
	{
		return 'measurement_pulse';
	}

	public function rules()
	{
		return array(
			array('pulse','numerical','integerOnly'=>true,'min'=>30,'max'=>200),
		);
	}

	public function getValueField()
	{
		return 'pulse';
	}

	public function getSuffix()
	{
		return 'bpm';
	}
}
