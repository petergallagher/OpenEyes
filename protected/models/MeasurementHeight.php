<?php
class MeasurementHeight extends Measurement
{
	public function tableName()
	{
		return 'measurement_height';
	}

	public function rules()
	{
		return array(
			array('height','numerical','integerOnly'=>true,'min'=>10,'max'=>280),
		);
	}

	public function getValueField()
	{
		return 'height';
	}

	public function getSuffix()
	{
		return 'cm';
	}

	public function toFtIn()
	{
		$ft = floor($this->height * 0.032808399);

		return array(
			'ft' => $ft,
			'in' => round(($this->height - ($ft / 0.032808399)) * 0.393700787),
		);
	}

	public function getFtInText()
	{
		$ft_in = $this->toFtIn();

		return $ft_in['ft']."'".$ft_in['in'].'"';
	}
}
