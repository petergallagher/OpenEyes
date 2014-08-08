<?php

class m140808_151902_o2_delivery_measurement extends OEMigration
{
	public function up()
	{
		$this->insert('measurement_type',array('class_name' => 'MeasurementO2'));

		$this->createTable('measurement_o2', array(
				'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
				'patient_measurement_id' => 'int(11) null',
				'o2' => 'tinyint(1) unsigned not null',
				'last_modified_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'last_modified_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'created_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'created_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'PRIMARY KEY (`id`)',
				'KEY `measurement_o2_lmui_fk` (`last_modified_user_id`)',
				'KEY `measurement_o2_cui_fk` (`created_user_id`)',
				'CONSTRAINT `measurement_o2_lmui_fk` FOREIGN KEY (`last_modified_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `measurement_o2_cui_fk` FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`)',
			), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');
		
		$this->versionExistingTable('measurement_o2');
	}

	public function down()
	{
		$this->dropTable('measurement_o2_version');
		$this->dropTable('measurement_o2');

		$this->delete('measurement_type',"class_name = 'MeasurementO2'");
	}
}
