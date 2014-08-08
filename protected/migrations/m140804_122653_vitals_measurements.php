<?php

class m140804_122653_vitals_measurements extends OEMigration
{
	public function up()
	{
		$this->createTable('measurement_pulse', array(
				'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
				'patient_measurement_id' => 'int(11) not null',
				'pulse' => 'tinyint(1) unsigned not null',
				'last_modified_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'last_modified_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'created_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'created_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'PRIMARY KEY (`id`)',
				'KEY `measurement_pulse_lmui_fk` (`last_modified_user_id`)',
				'KEY `measurement_pulse_cui_fk` (`created_user_id`)',
				'KEY `measurement_pulse_pmi_fk` (`patient_measurement_id`)',
				'CONSTRAINT `measurement_pulse_lmui_fk` FOREIGN KEY (`last_modified_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `measurement_pulse_cui_fk` FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `measurement_pulse_pmi_fk` FOREIGN KEY (`patient_measurement_id`) REFERENCES `patient_measurement` (`id`)',
			), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

		$this->versionExistingTable('measurement_pulse');

		$this->createTable('measurement_blood_pressure', array(
				'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
				'patient_measurement_id' => 'int(11) not null',
				'bp_systolic' => 'tinyint(1) unsigned not null',
				'bp_diastolic' => 'tinyint(1) unsigned not null',
				'last_modified_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'last_modified_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'created_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'created_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'PRIMARY KEY (`id`)',
				'KEY `measurement_blood_pressure_lmui_fk` (`last_modified_user_id`)',
				'KEY `measurement_blood_pressure_cui_fk` (`created_user_id`)',
				'KEY `measurement_blood_pressure_pmi_fk` (`patient_measurement_id`)',
				'CONSTRAINT `measurement_blood_pressure_lmui_fk` FOREIGN KEY (`last_modified_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `measurement_blood_pressure_cui_fk` FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `measurement_blood_pressure_pmi_fk` FOREIGN KEY (`patient_measurement_id`) REFERENCES `patient_measurement` (`id`)',
			), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

		$this->versionExistingTable('measurement_blood_pressure');

		$this->createTable('measurement_respiratory_rate', array(
				'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
				'patient_measurement_id' => 'int(11) not null',
				'rr' => 'tinyint(1) unsigned not null',
				'last_modified_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'last_modified_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'created_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'created_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'PRIMARY KEY (`id`)',
				'KEY `measurement_respiratory_rate_lmui_fk` (`last_modified_user_id`)',
				'KEY `measurement_respiratory_rate_cui_fk` (`created_user_id`)',
				'KEY `measurement_respiratory_rate_pmi_fk` (`patient_measurement_id`)',
				'CONSTRAINT `measurement_respiratory_rate_lmui_fk` FOREIGN KEY (`last_modified_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `measurement_respiratory_rate_cui_fk` FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `measurement_respiratory_rate_pmi_fk` FOREIGN KEY (`patient_measurement_id`) REFERENCES `patient_measurement` (`id`)',
			), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

		$this->versionExistingTable('measurement_respiratory_rate');

		$this->createTable('measurement_sao2', array(
				'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
				'patient_measurement_id' => 'int(11) not null',
				'sao2' => 'tinyint(1) unsigned not null',
				'last_modified_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'last_modified_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'created_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'created_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'PRIMARY KEY (`id`)',
				'KEY `measurement_sao2_lmui_fk` (`last_modified_user_id`)',
				'KEY `measurement_sao2_cui_fk` (`created_user_id`)',
				'KEY `measurement_sao2_pmi_fk` (`patient_measurement_id`)',
				'CONSTRAINT `measurement_sao2_lmui_fk` FOREIGN KEY (`last_modified_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `measurement_sao2_cui_fk` FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `measurement_sao2_pmi_fk` FOREIGN KEY (`patient_measurement_id`) REFERENCES `patient_measurement` (`id`)',
			), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

		$this->versionExistingTable('measurement_sao2');

		$this->createTable('measurement_pain_score', array(
				'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
				'patient_measurement_id' => 'int(11) not null',
				'pain_score' => 'tinyint(1) unsigned not null',
				'last_modified_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'last_modified_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'created_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'created_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'PRIMARY KEY (`id`)',
				'KEY `measurement_pain_score_lmui_fk` (`last_modified_user_id`)',
				'KEY `measurement_pain_score_cui_fk` (`created_user_id`)',
				'KEY `measurement_pain_score_pmi_fk` (`patient_measurement_id`)',
				'CONSTRAINT `measurement_pain_score_lmui_fk` FOREIGN KEY (`last_modified_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `measurement_pain_score_cui_fk` FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `measurement_pain_score_pmi_fk` FOREIGN KEY (`patient_measurement_id`) REFERENCES `patient_measurement` (`id`)',
			), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

		$this->versionExistingTable('measurement_pain_score');

		$this->createTable('measurement_glucose_level', array(
				'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
				'patient_measurement_id' => 'int(11) not null',
				'glucose_level' => 'tinyint(1) unsigned not null',
				'last_modified_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'last_modified_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'created_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'created_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'PRIMARY KEY (`id`)',
				'KEY `measurement_glucose_level_lmui_fk` (`last_modified_user_id`)',
				'KEY `measurement_glucose_level_cui_fk` (`created_user_id`)',
				'KEY `measurement_glucose_level_pmi_fk` (`patient_measurement_id`)',
				'CONSTRAINT `measurement_glucose_level_lmui_fk` FOREIGN KEY (`last_modified_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `measurement_glucose_level_cui_fk` FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `measurement_glucose_level_pmi_fk` FOREIGN KEY (`patient_measurement_id`) REFERENCES `patient_measurement` (`id`)',
			), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

		$this->versionExistingTable('measurement_glucose_level');

		$this->createTable('measurement_weight', array(
				'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
				'patient_measurement_id' => 'int(11) not null',
				'weight' => 'tinyint(1) unsigned not null',
				'last_modified_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'last_modified_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'created_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'created_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'PRIMARY KEY (`id`)',
				'KEY `measurement_weight_lmui_fk` (`last_modified_user_id`)',
				'KEY `measurement_weight_cui_fk` (`created_user_id`)',
				'KEY `measurement_weight_pmi_fk` (`patient_measurement_id`)',
				'CONSTRAINT `measurement_weight_lmui_fk` FOREIGN KEY (`last_modified_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `measurement_weight_cui_fk` FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `measurement_weight_pmi_fk` FOREIGN KEY (`patient_measurement_id`) REFERENCES `patient_measurement` (`id`)',
			), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

		$this->versionExistingTable('measurement_weight');

		$this->createTable('measurement_height', array(
				'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
				'patient_measurement_id' => 'int(11) not null',
				'height' => 'tinyint(1) unsigned not null',
				'last_modified_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'last_modified_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'created_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'created_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'PRIMARY KEY (`id`)',
				'KEY `measurement_height_lmui_fk` (`last_modified_user_id`)',
				'KEY `measurement_height_cui_fk` (`created_user_id`)',
				'KEY `measurement_height_pmi_fk` (`patient_measurement_id`)',
				'CONSTRAINT `measurement_height_lmui_fk` FOREIGN KEY (`last_modified_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `measurement_height_cui_fk` FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `measurement_height_pmi_fk` FOREIGN KEY (`patient_measurement_id`) REFERENCES `patient_measurement` (`id`)',
			), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

		$this->versionExistingTable('measurement_height');

		$this->createTable('measurement_temperature', array(
				'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
				'patient_measurement_id' => 'int(11) not null',
				'temperature' => 'tinyint(1) unsigned not null',
				'last_modified_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'last_modified_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'created_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'created_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'PRIMARY KEY (`id`)',
				'KEY `measurement_temperature_lmui_fk` (`last_modified_user_id`)',
				'KEY `measurement_temperature_cui_fk` (`created_user_id`)',
				'KEY `measurement_temperature_pmi_fk` (`patient_measurement_id`)',
				'CONSTRAINT `measurement_temperature_lmui_fk` FOREIGN KEY (`last_modified_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `measurement_temperature_cui_fk` FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `measurement_temperature_pmi_fk` FOREIGN KEY (`patient_measurement_id`) REFERENCES `patient_measurement` (`id`)',
			), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

		$this->versionExistingTable('measurement_temperature');

		$this->insert('measurement_type',array('class_name' => 'MeasurementBloodPressure'));
		$this->insert('measurement_type',array('class_name' => 'MeasurementPainScore'));
		$this->insert('measurement_type',array('class_name' => 'MeasurementPulse'));
		$this->insert('measurement_type',array('class_name' => 'MeasurementRespiratoryRate'));
		$this->insert('measurement_type',array('class_name' => 'MeasurementSPO2'));
		$this->insert('measurement_type',array('class_name' => 'MeasurementGlucoseLevel'));
		$this->insert('measurement_type',array('class_name' => 'MeasurementWeight'));
		$this->insert('measurement_type',array('class_name' => 'MeasurementHeight'));
		$this->insert('measurement_type',array('class_name' => 'MeasurementTemperature'));

		$this->addColumn('patient_measurement','timestamp','datetime not null');
		$this->addColumn('patient_measurement_version','timestamp','datetime not null');

		$this->dbConnection->createCommand("update patient_measurement set timestamp = created_date")->execute();
		$this->dbConnection->createCommand("update patient_measurement_version set timestamp = created_date")->execute();
	}

	public function down()
	{
		$this->dropColumn('patient_measurement_version','timestamp');
		$this->dropColumn('patient_measurement','timestamp');

		$this->delete('measurement_type',"class_name in ('MeasurementBloodPressure','MeasurementPainScore','MeasurementPulse','MeasurementRespiratoryRate','MeasurementSPO2','MeasurementGlucoseLevel','MeasurementAVPUScore','MeasurementWeight','MeasurementHeight','MeasurementTemperature')");

		$this->dropTable('measurement_temperature_version');
		$this->dropTable('measurement_temperature');

		$this->dropTable('measurement_height_version');
		$this->dropTable('measurement_height');

		$this->dropTable('measurement_weight_version');
		$this->dropTable('measurement_weight');

		$this->dropTable('measurement_glucose_level_version');
		$this->dropTable('measurement_glucose_level');

		$this->dropTable('measurement_pain_score_version');
		$this->dropTable('measurement_pain_score');

		$this->dropTable('measurement_sao2_version');
		$this->dropTable('measurement_sao2');

		$this->dropTable('measurement_pulse_version');
		$this->dropTable('measurement_pulse');

		$this->dropTable('measurement_blood_pressure_version');
		$this->dropTable('measurement_blood_pressure');

		$this->dropTable('measurement_respiratory_rate_version');
		$this->dropTable('measurement_respiratory_rate');
	}
}
