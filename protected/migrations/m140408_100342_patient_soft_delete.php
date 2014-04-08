<?php

class m140408_100342_patient_soft_delete extends CDbMigration
{
	public function up()
	{
		$this->addColumn('patient','deleted','tinyint(1) unsigned not null');
	}

	public function down()
	{
		$this->dropColumn('patient','deleted');
	}
}
