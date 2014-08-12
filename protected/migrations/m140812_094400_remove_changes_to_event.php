<?php

class m140812_094400_remove_changes_to_event extends CDbMigration
{
	public function up()
	{
		$this->dropForeignKey('event_type_parent_id_fk','event_type');
		$this->dropIndex('event_type_parent_id_fk','event_type');
		$this->dropColumn('event_type','parent_id');

		$this->dropForeignKey('event_parent_id_fk','event');
		$this->dropIndex('event_parent_id_fk','event');
		$this->dropColumn('event','parent_id');
	}

	public function down()
	{
		$this->addColumn('event_type','parent_id','int(10) unsigned NULL');
		$this->createIndex('event_type_parent_id_fk','event_type','parent_id');
		$this->addForeignKey('event_type_parent_id_fk','event_type','parent_id','event_type','id');

		$this->addColumn('event','parent_id','int(10) unsigned NULL');
		$this->createIndex('event_parent_id_fk','event','parent_id');
		$this->addForeignKey('event_parent_id_fk','event','parent_id','event','id');
	}

}