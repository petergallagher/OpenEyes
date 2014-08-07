<?php

class m140807_153927_spo2_is_sao2 extends CDbMigration
{
	public function up()
	{
		$this->dropForeignKey('measurement_spo2_lmui_fk','measurement_spo2');
		$this->dropForeignKey('measurement_spo2_cui_fk','measurement_spo2');
		$this->dropForeignKey('measurement_spo2_pmi_fk','measurement_spo2');

		$this->renameTable('measurement_spo2','measurement_sao2');
		$this->renameTable('measurement_spo2_version','measurement_sao2_version');

		$this->renameColumn('measurement_sao2','spo2','sao2');
		$this->renameColumn('measurement_sao2_version','spo2','sao2');

		$this->addForeignKey('measurement_sao2_lmui_fk','measurement_sao2','last_modified_user_id','user','id');
		$this->addForeignKey('measurement_sao2_cui_fk','measurement_sao2','created_user_id','user','id');
		$this->addForeignKey('measurement_sao2_pmi_fk','measurement_sao2','patient_measurement_id','patient_measurement','id');

		$this->update('measurement_type',array('class_name' => 'MeasurementSAO2'),"class_name = 'MeasurementSPO2'");
	}

	public function down()
	{
		$this->dropForeignKey('measurement_sao2_lmui_fk','measurement_sao2');
		$this->dropForeignKey('measurement_sao2_cui_fk','measurement_sao2');
		$this->dropForeignKey('measurement_sao2_pmi_fk','measurement_sao2');

		$this->renameTable('measurement_sao2','measurement_spo2');
		$this->renameTable('measurement_sao2_version','measurement_spo2_version');

		$this->renameColumn('measurement_spo2','sao2','spo2');
		$this->renameColumn('measurement_spo2_version','sao2','spo2');

		$this->addForeignKey('measurement_spo2_lmui_fk','measurement_spo2','last_modified_user_id','user','id');
		$this->addForeignKey('measurement_spo2_cui_fk','measurement_spo2','created_user_id','user','id');
		$this->addForeignKey('measurement_spo2_pmi_fk','measurement_spo2','patient_measurement_id','patient_measurement','id');

		$this->update('measurement_type',array('class_name' => 'MeasurementSPO2'),"class_name = 'MeasurementSAO2'");
	}
}
