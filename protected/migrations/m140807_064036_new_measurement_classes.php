<?php

class m140807_064036_new_measurement_classes extends CDbMigration
{
	public function up()
	{
		$this->insert('measurement_type',array('class_name'=>'MeasurementBMI'));
		$this->insert('measurement_type',array('class_name'=>'MeasurementAirwayClass'));
	}

	public function down()
	{
		$this->delete('measurement_type',"class_name in ('MeasurementBMI','MeasurementAirwayClass')");
	}
}
