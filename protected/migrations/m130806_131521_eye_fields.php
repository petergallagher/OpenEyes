<?php

class m130806_131521_eye_fields extends CDbMigration
{
	public function up()
	{
		$this->addColumn('eye','created_user_id','int(10) unsigned NOT NULL DEFAULT 1');
		$this->update('eye',array('created_user_id'=>1));
		$this->addForeignKey('eye_created_user_id_fk','eye','created_user_id','user','id');

		$this->addColumn('eye','last_modified_user_id','int(10) unsigned NOT NULL DEFAULT 1');
		$this->update('eye',array('last_modified_user_id'=>1));
		$this->addForeignKey('eye_last_modified_user_id_fk','eye','last_modified_user_id','user','id');

		$this->addColumn('eye','last_modified_date',"datetime NOT NULL DEFAULT '1900-01-01 00:00:00'");
		$this->addColumn('eye','created_date',"datetime NOT NULL DEFAULT '1900-01-01 00:00:00'");
	}

	public function down()
	{
		$this->dropColumn('eye','created_date');
		$this->dropColumn('eye','last_modified_date');
		$this->dropForeignKey('eye_last_modified_user_id_fk','eye');
		$this->dropColumn('eye','last_modified_user_id');
		$this->dropForeignKey('eye_created_user_id_fk','eye');
		$this->dropColumn('eye','created_user_id');
	}
}
