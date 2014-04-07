<?php

class m140407_140837_language_display_order extends CDbMigration
{
	public function up()
	{
		$this->addColumn('language','display_order','tinyint(1) unsigned not null');
		$this->getDbConnection()->createCommand("update language set display_order = id")->query();
	}

	public function down()
	{
		$this->dropColumn('language','display_order');
	}
}
