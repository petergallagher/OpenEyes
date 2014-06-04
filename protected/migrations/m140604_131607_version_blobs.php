<?php

class m140604_131607_version_blobs extends CDbMigration
{
	public function up()
	{
		$this->createTable('version_blob', array(
				'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
				'record_class_name' => 'varchar(64) not null',
				'record_id' => 'int(10) unsigned not null',
				'created_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'created_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'record_last_modified_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'record_last_modified_user_id' => 'int(10) unsigned NOT NULL',
				'record_data' => 'text not null',
				'PRIMARY KEY (`id`)',
				'KEY `version_blob_record_class_name_fk` (`record_class_name`)',
				'KEY `version_blob_record_id_fk` (`record_id`)',
				'KEY `version_blob_cd_fk` (`created_date`)',
				'KEY `version_blob_cui_fk` (`created_user_id`)',
				'KEY `version_blob_lmd_fk` (`record_last_modified_date`)',
				'KEY `version_blob_lmui_fk` (`record_last_modified_user_id`)',
				'CONSTRAINT `version_blob_cui_fk` FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`)',
		), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin');
	}

	public function down()
	{
		$this->dropTable('version_blob');
	}
}
