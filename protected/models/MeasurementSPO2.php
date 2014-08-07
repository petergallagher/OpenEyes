<?php
class MeasurementSPO2 extends Measurement
{
	public function tableName()
	{
		return 'measurement_spo2';
	}

	public function rules()
	{
		return array(
			array('sao2','numerical','integerOnly'=>true,'min'=>0,'max'=>100),
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
