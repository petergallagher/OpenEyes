<?php

class m140410_121216_patient_search_result_fields extends CDbMigration
{
	public function up()
	{
		$this->createTable('patient_search_result_field', array(
				'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
				'name' => 'varchar(64) NOT NULL',
				'label' => 'varchar(64) NOT NULL',
				'display_order' => 'tinyint(1) unsigned not null',
				'last_modified_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'last_modified_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'created_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'created_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'PRIMARY KEY (`id`)',
				'KEY `patient_search_result_field_last_modified_user_id_fk` (`last_modified_user_id`)',
				'KEY `patient_search_result_field_created_user_id_fk` (`created_user_id`)',
				'CONSTRAINT `patient_search_result_field_last_modified_user_id_fk` FOREIGN KEY (`last_modified_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `patient_search_result_field_created_user_id_fk` FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`)',
			), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

		$this->insert('patient_search_result_field',array('id'=>1,'name'=>'hos_num','display_order'=>1));
		$this->insert('patient_search_result_field',array('id'=>2,'name'=>'nhs_num','display_order'=>2));
		$this->insert('patient_search_result_field',array('id'=>3,'name'=>'title','display_order'=>3));
		$this->insert('patient_search_result_field',array('id'=>4,'name'=>'first_name','display_order'=>4));
		$this->insert('patient_search_result_field',array('id'=>5,'name'=>'last_name','display_order'=>5));
		$this->insert('patient_search_result_field',array('id'=>6,'name'=>'dob','display_order'=>6));
		$this->insert('patient_search_result_field',array('id'=>7,'name'=>'gender','display_order'=>7));
	}

	public function down()
	{
		$this->dropTable('patient_search_result_field');
	}
}
