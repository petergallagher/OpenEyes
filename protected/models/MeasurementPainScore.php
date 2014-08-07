<?php
class MeasurementPainScore extends Measurement
{
	public function tableName()
	{
		return 'measurement_pain_score';
	}

	public function rules()
	{
		return array(
			array('pain_score','numerical','integerOnly'=>true,'min'=>1,'max'=>10),
		);
	}

	public function getValueField()
	{
		return 'pain_score';
	}
}
