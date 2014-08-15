<?php

class m140815_142933_blood_glucose_is_decimal extends CDbMigration
{
	public function up()
	{
		$this->alterColumn('measurement_blood_glucose','blood_glucose','decimal(3,1) not null');
		$this->alterColumn('measurement_blood_glucose_version','blood_glucose','decimal(3,1) not null');
	}

	public function down()
	{
		$this->alterColumn('measurement_blood_glucose','blood_glucose','tinyint(1) unsigned not null');
		$this->alterColumn('measurement_blood_glucose_version','blood_glucose','tinyint(1) unsigned not null');
	}
}
