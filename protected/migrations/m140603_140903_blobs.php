<?php

class m140603_140903_blobs extends CDbMigration
{
	public function up()
	{
		$this->createTable('patient_version', array(
				'id' => 'int(10) unsigned NOT NULL',
				'version_id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
				'blob' => 'text not null',
				'PRIMARY KEY (`version_id`)',
		), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin');
	}

	public function down()
	{
		$this->dropTable('patient_version');
	}
}
