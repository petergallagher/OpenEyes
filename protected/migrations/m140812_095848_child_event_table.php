<?php

class m140812_095848_child_event_table extends OEMigration
{
	public function up()
	{
		$this->createOETable(
			"child_event",
			array(
				"id" => "pk",
				"parent_event_id" => "INT(10) UNSIGNED NOT NULL",
				"child_event_id" => "INT(10) UNSIGNED NOT NULL",
				'constraint child_event_parent_event_id_fk foreign key (parent_event_id) references event (id)',
				'constraint child_event_child_event_id_fk foreign key (child_event_id) references event (id)',
			),
			true
		);
	}

	public function down()
	{
		$this->dropTable("child_event");
		$this->dropTable("child_event_version");
	}

}