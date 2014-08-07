<?php
class MeasurementSAO2 extends Measurement
{
	public function tableName()
	{
		return 'measurement_sao2';
	}

	public function rules()
	{
		return array(
			array('sao2','numerical','integerOnly'=>true,'min'=>60,'max'=>100),
		);
	}

	public function getValueField()
	{
		return 'sao2';
	}

	public function getSuffix()
	{
		return '%';
	}
}
