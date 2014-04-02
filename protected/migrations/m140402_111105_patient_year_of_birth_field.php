<?php

class m140402_111105_patient_year_of_birth_field extends CDbMigration
{
	public function up()
	{
		$this->addColumn('patient','yob','varchar(4) null');
	}

	public function down()
	{
		$this->dropColumn('patient','yob');
	}
}
