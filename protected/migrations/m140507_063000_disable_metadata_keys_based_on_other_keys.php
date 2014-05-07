<?php

class m140507_063000_disable_metadata_keys_based_on_other_keys extends CDbMigration
{
	public function up()
	{
		$this->addColumn('patient_metadata_key','hide_fields','varchar(64) null');
		$this->addColumn('patient_metadata_key','hide_fields_values','varchar(64) null');
	}

	public function down()
	{
		$this->dropColumn('patient_metadata_key','hide_fields_values');
		$this->dropColumn('patient_metadata_key','hide_fields');
	}
}
