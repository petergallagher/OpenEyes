<?php
class MeasurementSPO2 extends Measurement
{
	public function tableName()
	{
		return 'measurement_spo2';
	}

	public function getValueField()
	{
		return 'spo2';
	}

	public function getSuffix()
	{
		return '%';
	}
}
