<?php

class m140801_111606_element_type_active extends CDbMigration
{
	public function up()
	{
		$this->addColumn('element_type','active','tinyint(1) unsigned not null default 1');
		$this->addColumn('element_type_version','active','tinyint(1) unsigned not null default 1');
	}

	public function down()
	{
		$this->dropColumn('element_type','active');
		$this->dropColumn('element_type_version','active');
	}
}
