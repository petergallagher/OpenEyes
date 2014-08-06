<?php
class MeasurementRespiratoryRate extends Measurement
{
	public function tableName()
	{
		return 'measurement_respiratory_rate';
	}

	public function getValueField()
	{
		return 'rr';
	}

	public function getValueText()
	{
		return $this->getValue().' insp/min';
	}
}
