<?php

class m140730_101621_patient_fuzzy_age extends CDbMigration
{
	public function up()
	{
		$this->addColumn('patient','mob','varchar(2) null');
		$this->addColumn('patient_version','mob','varchar(2) null');
	}

	public function down()
	{
		$this->dropColumn('patient','mob');
		$this->dropColumn('patient_version','mob');
	}
}
