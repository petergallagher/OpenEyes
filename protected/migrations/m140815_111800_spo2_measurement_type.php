<?php

class m140815_111800_spo2_measurement_type extends CDbMigration
{
	public function up()
	{
		$this->insert('measurement_type',array('class_name' => 'MeasurementSPO2'));
	}

	public function down()
	{
		$this->delete('measurement_type',"class_name = 'MeasurementSPO2'");
	}
}
