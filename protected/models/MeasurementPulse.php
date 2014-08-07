<?php
class MeasurementPulse extends Measurement
{
	public function tableName()
	{
		return 'measurement_pulse';
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
