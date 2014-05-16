<?php

class m140516_131542_version_table_changes extends CDbMigration
{
	public function up()
	{
		$this->addColumn('allergy_version','display_order','int(10) unsigned not null');
		$this->addColumn('language_version','display_order','tinyint(1) unsigned not null');
		$this->renameColumn('patient_version','gender','gender_id');
		$this->alterColumn('patient_version','gender_id','int(10) unsigned null');
		$this->addColumn('patient_version','yob','varchar(4) null');
		$this->addColumn('patient_version','deleted','tinyint(1) unsigned not null');
		$this->alterColumn('protected_file_version','name','varchar(255) not null');
	}

	public function down()
	{
		$this->alterColumn('protected_file_version','name','varchar(64) not null');
		$this->dropColumn('patient_version','deleted');
		$this->dropColumn('patient_version','yob');
		$this->alterColumn('patient_version','gender_id','varchar(1) null');
		$this->renameColumn('patient_version','gender_id','gender');
		$this->dropColumn('language_version','display_order');
		$this->dropColumn('allergy_version','display_order');
	}
}
