<?php

class m140410_095809_patient_search_fields extends CDbMigration
{
	public function up()
	{
		$this->createTable('patient_search_field', array(
				'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
				'name' => 'varchar(64) NOT NULL',
				'display_order' => 'tinyint(1) unsigned not null',
				'focus' => 'tinyint(1) unsigned not null',
				'last_modified_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'last_modified_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'created_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'created_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'PRIMARY KEY (`id`)',
				'KEY `patient_search_field_last_modified_user_id_fk` (`last_modified_user_id`)',
				'KEY `patient_search_field_created_user_id_fk` (`created_user_id`)',
				'CONSTRAINT `patient_search_field_last_modified_user_id_fk` FOREIGN KEY (`last_modified_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `patient_search_field_created_user_id_fk` FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`)',
			), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

		$this->insert('patient_search_field',array('id' => 1, 'name' => 'hos_num', 'display_order' => 1, 'focus' => 1));
		$this->insert('patient_search_field',array('id' => 2, 'name' => 'nhs_num', 'display_order' => 2));
		$this->insert('patient_search_field',array('id' => 3, 'name' => 'first_name', 'display_order' => 3));
		$this->insert('patient_search_field',array('id' => 4, 'name' => 'last_name', 'display_order' => 4));
	}

	public function down()
	{
		$this->dropTable('patient_search_field');
	}
}
