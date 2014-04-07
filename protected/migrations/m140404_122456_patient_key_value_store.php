<?php

class m140404_122456_patient_key_value_store extends CDbMigration
{
	public function up()
	{
		$this->createTable('patient_metadata_key', array(
				'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
				'key_name' => 'varchar(64) NOT NULL',
				'key_label' => 'varchar(64) NOT NULL',
				'select' => 'tinyint(1) unsigned not null',
				'select_empty' => 'varchar(64) NOT NULL',
				'required' => 'tinyint(1) unsigned not null',
				'display_order' => 'tinyint(1) unsigned not null',
				'last_modified_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'last_modified_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'created_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'created_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'PRIMARY KEY (`id`)',
				'KEY `patient_metadata_key_last_modified_user_id_fk` (`last_modified_user_id`)',
				'KEY `patient_metadata_key_created_user_id_fk` (`created_user_id`)',
				'CONSTRAINT `patient_metadata_key_last_modified_user_id_fk` FOREIGN KEY (`last_modified_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `patient_metadata_key_created_user_id_fk` FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`)',
			), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

		$this->createTable('patient_metadata_key_option', array(
				'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
				'key_id' => 'int(10) unsigned not null',
				'option_value' => 'varchar(64) NOT NULL',
				'display_order' => 'tinyint(1) unsigned not null',
				'last_modified_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'last_modified_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'created_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'created_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'PRIMARY KEY (`id`)',
				'KEY `patient_metadata_key_option_last_modified_user_id_fk` (`last_modified_user_id`)',
				'KEY `patient_metadata_key_option_created_user_id_fk` (`created_user_id`)',
				'KEY `patient_metadata_key_option_key_id_fk` (`key_id`)',
				'CONSTRAINT `patient_metadata_key_option_last_modified_user_id_fk` FOREIGN KEY (`last_modified_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `patient_metadata_key_option_created_user_id_fk` FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `patient_metadata_key_option_key_id_fk` FOREIGN KEY (`key_id`) REFERENCES `patient_metadata_key` (`id`)',
			), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

		$this->createTable('patient_metadata_value', array(
				'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
				'patient_id' => 'int(10) unsigned NOT NULL',
				'key_name' => 'varchar(64) NOT NULL',
				'key_value' => 'varchar(64) NOT NULL',
				'last_modified_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'last_modified_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'created_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'created_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'PRIMARY KEY (`id`)',
				'KEY `patient_metadata_value_last_modified_user_id_fk` (`last_modified_user_id`)',
				'KEY `patient_metadata_value_created_user_id_fk` (`created_user_id`)',
				'KEY `patient_metadata_value_patient_id_fk` (`patient_id`)',
				'CONSTRAINT `patient_metadata_value_last_modified_user_id_fk` FOREIGN KEY (`last_modified_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `patient_metadata_value_created_user_id_fk` FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `patient_metadata_value_patient_id_fk` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`id`)',
			), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');
	}

	public function down()
	{
		$this->dropTable('patient_metadata_key_option');
		$this->dropTable('patient_metadata_key_option');
		$this->dropTable('patient_metadata_key');
	}
}
