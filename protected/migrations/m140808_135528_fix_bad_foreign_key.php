<?php

class m140808_135528_fix_bad_foreign_key extends CDbMigration
{
	public function up()
	{
		$this->dropForeignKey('measurement_reference_event_id_fk','measurement_reference');
		$this->dropForeignKey('measurement_reference_episode_id_fk','measurement_reference');
		$this->dropIndex('measurement_reference_event_id_fk','measurement_reference');
		$this->addForeignKey('measurement_reference_event_id_fk','measurement_reference','event_id','event','id');
		$this->addForeignKey('measurement_reference_episode_id_fk','measurement_reference','episode_id','episode','id');
	}

	public function down()
	{
		$this->dropForeignKey('measurement_reference_episode_id_fk','measurement_reference');
		$this->dropForeignKey('measurement_reference_event_id_fk','measurement_reference');
		$this->dropIndex('measurement_reference_event_id_fk','measurement_reference');
		$this->createIndex('measurement_reference_event_id_fk','measurement_reference','episode_id');
		$this->addForeignKey('measurement_reference_event_id_fk','measurement_reference','episode_id','event','id');
		$this->addForeignKey('measurement_reference_episode_id_fk','measurement_reference','episode_id','episode','id');
	}
}
