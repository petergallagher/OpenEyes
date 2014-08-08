<?php

class m140808_145832_rename_blood_glucose_measurement extends CDbMigration
{
	public function up()
	{
		$this->update('measurement_type',array('class_name' => 'MeasurementBloodGlucose'),"class_name = 'MeasurementGlucoseLevel'");

		$this->dropForeignKey('measurement_glucose_level_lmui_fk','measurement_glucose_level');
		$this->dropForeignKey('measurement_glucose_level_cui_fk','measurement_glucose_level');
		$this->dropForeignKey('measurement_glucose_level_pmi_fk','measurement_glucose_level');

		$this->renameTable('measurement_glucose_level','measurement_blood_glucose');
		$this->renameTable('measurement_glucose_level_version','measurement_blood_glucose_version');

		$this->addForeignKey('measurement_blood_glucose_lmui_fk','measurement_blood_glucose','last_modified_user_id','user','id');
		$this->addForeignKey('measurement_blood_glucose_cui_fk','measurement_blood_glucose','created_user_id','user','id');
		$this->addForeignKey('measurement_blood_glucose_pmi_fk','measurement_blood_glucose','patient_measurement_id','patient_measurement','id');

		$this->renameColumn('measurement_blood_glucose','glucose_level','blood_glucose');
		$this->renameColumn('measurement_blood_glucose_version','glucose_level','blood_glucose');
	}

	public function down()
	{
		$this->renameColumn('measurement_blood_glucose','blood_glucose','glucose_level');
		$this->renameColumn('measurement_blood_glucose_version','blood_glucose','glucose_level');

		$this->dropForeignKey('measurement_blood_glucose_lmui_fk','measurement_blood_glucose');
		$this->dropForeignKey('measurement_blood_glucose_cui_fk','measurement_blood_glucose');
		$this->dropForeignKey('measurement_blood_glucose_pmi_fk','measurement_blood_glucose');

		$this->renameTable('measurement_blood_glucose','measurement_glucose_level');
		$this->renameTable('measurement_blood_glucose_version','measurement_glucose_level_version');

		$this->addForeignKey('measurement_glucose_level_lmui_fk','measurement_glucose_level','last_modified_user_id','user','id');
		$this->addForeignKey('measurement_glucose_level_cui_fk','measurement_glucose_level','created_user_id','user','id');
		$this->addForeignKey('measurement_glucose_level_pmi_fk','measurement_glucose_level','patient_measurement_id','patient_measurement','id');

		$this->update('measurement_type',array('class_name' => 'MeasurementGlucoseLevel'),"class_name = 'MeasurementBloodGlucose'");
	}
}
