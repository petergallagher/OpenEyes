<?php
class MeasurementPainScore extends Measurement
{
	public function tableName()
	{
		return 'measurement_pain_score';
	}

	public function getValueField()
	{
		return 'pain_score';
	}
}
