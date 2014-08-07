<?php
class MeasurementWeight extends Measurement
{
	public function tableName()
	{
		return 'measurement_weight';
	}

	public function rules()
	{
		return array(
			array('weight','numerical','integerOnly'=>false,'min'=>0.5,'max'=>500),
		);
	}

	public function getValueField()
	{
		return 'weight';
	}

	public function getSuffix()
	{
		return 'kg';
	}

	public function toLb()
	{
		return $this->weight * 2.20462;
	}

	public function getLbText()
	{
		return number_format($this->toLb(),1).' lbs';
	}
}
