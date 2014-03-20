<?php

class m140221_123246_versioning_transactions extends CDbMigration
{
	public $tables = array('address_type','address','allergy','anaesthetic_agent','anaesthetic_complication','anaesthetic_delivery','anaesthetic_type','anaesthetist','benefit','commissioning_body_patient_assignment','commissioning_body_practice_assignment','commissioning_body_service_type','commissioning_body_service','commissioning_body_type','commissioning_body','common_ophthalmic_disorder','common_previous_operation','common_systemic_disorder','complication','contact_label','contact_location','contact_metadata','contact','country','disorder_tree','disorder','drug_allergy_assignment','drug_duration','drug_form','drug_frequency','drug_route_option','drug_route','drug_set_item_taper','drug_set_item','drug_set','drug_type','drug','element_type','episode_status','episode','ethnic_group','event_group','event_issue','event_type','event','family_history_condition','family_history_relative','family_history_side','family_history','firm_user_assignment','firm','gp','institution','issue','language','medication','nsc_grade','opcs_code','operative_device','patient_allergy_assignment','patient_contact_assignment','patient_oph_info_cvi_status','patient_oph_info','patient_shortcode','patient','period','person','practice','previous_operation','priority','proc_opcs_assignment','proc_subspecialty_assignment','proc_subspecialty_subsection_assignment','proc','procedure_additional','procedure_benefit','procedure_complication','protected_file','referral_episode_assignment','referral_type','referral','secondary_diagnosis','service_subspecialty_assignment','service','setting_field_type','setting_firm','setting_installation','setting_institution','setting_metadata','setting_site','setting_specialty','setting_subspecialty','setting_user','site_subspecialty_anaesthetic_agent_default','site_subspecialty_anaesthetic_agent','site_subspecialty_drug','site_subspecialty_operative_device','site','specialty_type','specialty','subspecialty_subsection','subspecialty','user_firm_preference','user_firm_rights','user_firm','user_service_rights','user_site','user');

	public function up()
	{
		$this->createTable('transaction_table', array(
				'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
				'name' => 'varchar(64) not null',
				'last_modified_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'last_modified_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'created_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'created_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'PRIMARY KEY (`id`)',
				'KEY `transaction_table_last_modified_user_id_fk` (`last_modified_user_id`)',
				'KEY `transaction_table_created_user_id_fk` (`created_user_id`)',
				'CONSTRAINT `transaction_table_last_modified_user_id_fk` FOREIGN KEY (`last_modified_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `transaction_table_created_user_id_fk` FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`)',
			), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

		$this->createTable('transaction_operation', array(
				'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
				'name' => 'varchar(64) not null',
				'last_modified_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'last_modified_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'created_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'created_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'PRIMARY KEY (`id`)',
				'KEY `transaction_operation_last_modified_user_id_fk` (`last_modified_user_id`)',
				'KEY `transaction_operation_created_user_id_fk` (`created_user_id`)',
				'CONSTRAINT `transaction_operation_last_modified_user_id_fk` FOREIGN KEY (`last_modified_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `transaction_operation_created_user_id_fk` FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`)',
			), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

		$this->insert('transaction_operation',array('id'=>1,'name'=>'Create'));
		$this->insert('transaction_operation',array('id'=>2,'name'=>'Update'));
		$this->insert('transaction_operation',array('id'=>3,'name'=>'Delete'));

		$this->createTable('transaction_model', array(
				'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
				'name' => 'varchar(64) not null',
				'last_modified_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'last_modified_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'created_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'created_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'PRIMARY KEY (`id`)',
				'KEY `transaction_model_last_modified_user_id_fk` (`last_modified_user_id`)',
				'KEY `transaction_model_created_user_id_fk` (`created_user_id`)',
				'CONSTRAINT `transaction_model_last_modified_user_id_fk` FOREIGN KEY (`last_modified_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `transaction_model_created_user_id_fk` FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`)',
			), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

		$this->createTable('server', array(
				'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
				'ip' => 'varchar(16) NOT NULL',
				'last_modified_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'last_modified_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'created_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'created_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'PRIMARY KEY (`id`)',
				'KEY `server_last_modified_user_id_fk` (`last_modified_user_id`)',
				'KEY `server_created_user_id_fk` (`created_user_id`)',
				'CONSTRAINT `server_last_modified_user_id_fk` FOREIGN KEY (`last_modified_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `server_created_user_id_fk` FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`)'
			), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

		$this->createTable('transaction', array(
				'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
				'server_id' => 'int(10) unsigned NULL',
				'operation_id' => 'int(10) unsigned NULL',
				'model_class_id' => 'int(10) unsigned NULL',
				'model_id' => 'int(10) unsigned NULL',
				'patient_id' => 'int(10) unsigned NULL',
				'modified_data' => 'tinyint(1) unsigned NOT NULL',
				'last_modified_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'last_modified_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'created_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'created_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'PRIMARY KEY (`id`)',
				'KEY `transaction_last_modified_user_id_fk` (`last_modified_user_id`)',
				'KEY `transaction_created_user_id_fk` (`created_user_id`)',
				'KEY `transaction_operation_id_fk` (`operation_id`)',
				'KEY `transaction_model_class_id_fk` (`model_class_id`)',
				'KEY `transaction_server_id_fk` (`server_id`)',
				'KEY `transaction_patient_id_fk` (`patient_id`)',
				'CONSTRAINT `transaction_last_modified_user_id_fk` FOREIGN KEY (`last_modified_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `transaction_created_user_id_fk` FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `transaction_operation_id_fk` FOREIGN KEY (`operation_id`) REFERENCES `transaction_operation` (`id`)',
				'CONSTRAINT `transaction_model_class_id_fk` FOREIGN KEY (`model_class_id`) REFERENCES `transaction_model` (`id`)',
				'CONSTRAINT `transaction_server_id_fk` FOREIGN KEY (`server_id`) REFERENCES `server` (`id`)',
				'CONSTRAINT `transaction_patient_id_fk` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`id`)',
			), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

		$this->createTable('transaction_table_assignment', array(
				'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
				'transaction_id' => 'int(10) unsigned not null',
				'table_id' => 'int(10) unsigned not null',
				'display_order' => 'tinyint(1) unsigned not null',
				'last_modified_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'last_modified_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'created_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'created_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'PRIMARY KEY (`id`)',
				'KEY `transaction_ta_last_modified_user_id_fk` (`last_modified_user_id`)',
				'KEY `transaction_ta_created_user_id_fk` (`created_user_id`)',
				'KEY `transaction_ta_transaction_id_fk` (`transaction_id`)',
				'KEY `transaction_ta_table_id_fk` (`table_id`)',
				'CONSTRAINT `transaction_ta_last_modified_user_id_fk` FOREIGN KEY (`last_modified_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `transaction_ta_created_user_id_fk` FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `transaction_ta_transaction_id_fk` FOREIGN KEY (`transaction_id`) REFERENCES `transaction` (`id`)',
				'CONSTRAINT `transaction_ta_table_id_fk` FOREIGN KEY (`table_id`) REFERENCES `transaction_table` (`id`)',
			), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

		foreach ($this->tables as $table) {
			$this->addColumn($table,'hash','varchar(40) not null');
			$this->addColumn($table,'transaction_id','int(10) unsigned null');
			$this->createIndex($table.'_tid',$table,'transaction_id');
			$this->addForeignKey($table.'_tid',$table,'transaction_id','transaction','id');

			$this->addColumn($table.'_version','hash','varchar(40) not null');
			$this->addColumn($table.'_version','transaction_id','int(10) unsigned null');
			$this->addColumn($table.'_version','deleted_transaction_id','int(10) unsigned null');
			$this->createIndex($table.'_vtid',$table.'_version','transaction_id');
			$this->addForeignKey($table.'_vtid',$table.'_version','transaction_id','transaction','id');
			$this->createIndex($table.'_dtid',$table.'_version','deleted_transaction_id');
			$this->addForeignKey($table.'_dtid',$table.'_version','deleted_transaction_id','transaction','id');
		}
	}

	public function down()
	{
		foreach ($this->tables as $table) {
			$this->dropColumn($table,'hash');
			$this->dropForeignKey($table.'_tid',$table);
			$this->dropIndex($table.'_tid',$table);
			$this->dropColumn($table,'transaction_id');

			$this->dropColumn($table.'_version','hash');
			$this->dropForeignKey($table.'_vtid',$table.'_version');
			$this->dropIndex($table.'_vtid',$table.'_version');
			$this->dropColumn($table.'_version','transaction_id');
			$this->dropForeignKey($table.'_dtid',$table.'_version');
			$this->dropIndex($table.'_dtid',$table.'_version');
			$this->dropColumn($table.'_version','deleted_transaction_id');
		}

		$this->dropTable('transaction_table_assignment');
		$this->dropTable('transaction');
		$this->dropTable('server');
		$this->dropTable('transaction_model');
		$this->dropTable('transaction_operation');
		$this->dropTable('transaction_table');
	}
}
