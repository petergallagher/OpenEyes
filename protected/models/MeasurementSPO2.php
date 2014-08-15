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
			array('spo2','numerical','integerOnly'=>true,'min'=>60,'max'=>100),
		);
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
