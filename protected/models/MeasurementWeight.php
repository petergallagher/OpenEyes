<?php
class MeasurementWeight extends Measurement
{
	public function tableName()
	{
		return 'measurement_weight';
	}

	public function getValueField()
	{
		return 'weight';
	}
}
