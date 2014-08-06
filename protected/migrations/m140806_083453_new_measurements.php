<?php

class m140806_083453_new_measurements extends OEMigration
{
	public function up()
	{
		$this->createTable('measurement_bmi', array(
				'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
				'patient_measurement_id' => 'int(11) not null',
				'bmi' => 'tinyint(1) unsigned not null',
				'last_modified_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'last_modified_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'created_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'created_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'PRIMARY KEY (`id`)',
				'KEY `measurement_bmi_lmui_fk` (`last_modified_user_id`)',
				'KEY `measurement_bmi_cui_fk` (`created_user_id`)',
				'KEY `measurement_bmi_pmi_fk` (`patient_measurement_id`)',
				'CONSTRAINT `measurement_bmi_lmui_fk` FOREIGN KEY (`last_modified_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `measurement_bmi_cui_fk` FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `measurement_bmi_pmi_fk` FOREIGN KEY (`patient_measurement_id`) REFERENCES `patient_measurement` (`id`)',
			), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

		$this->versionExistingTable('measurement_bmi');

		$this->createTable('measurement_airway_class', array(
				'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
				'patient_measurement_id' => 'int(11) not null',
				'airway_class' => 'tinyint(1) unsigned not null',
				'last_modified_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'last_modified_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'created_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'created_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'PRIMARY KEY (`id`)',
				'KEY `measurement_airway_class_lmui_fk` (`last_modified_user_id`)',
				'KEY `measurement_airway_class_cui_fk` (`created_user_id`)',
				'KEY `measurement_airway_class_pmi_fk` (`patient_measurement_id`)',
				'CONSTRAINT `measurement_airway_class_lmui_fk` FOREIGN KEY (`last_modified_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `measurement_airway_class_cui_fk` FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `measurement_airway_class_pmi_fk` FOREIGN KEY (`patient_measurement_id`) REFERENCES `patient_measurement` (`id`)',
			), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

		$this->versionExistingTable('measurement_airway_class');
	}

	public function down()
	{
		$this->dropTable('measurement_airway_class_version');
		$this->dropTable('measurement_airway_class');

		$this->dropTable('measurement_bmi_version');
		$this->dropTable('measurement_bmi');
	}
}
