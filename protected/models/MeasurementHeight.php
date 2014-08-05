<?php
class MeasurementHeight extends Measurement
{
	public function tableName()
	{
		return 'measurement_height';
	}

	public function getValueField()
	{
		return 'height';
	}
}
