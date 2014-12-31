<?php

class m141231_161628_disorder_tree_parent_id_fk extends CDbMigration
{
	public function up()
	{
		$this->dbConnection->createCommand("update disorder_tree set parent_id = null where parent_id = 0")->query();

		$this->createIndex('disorder_tree_parent_id_fk','disorder_tree','parent_id');
		$this->addForeignKey('disorder_tree_parent_id_fk','disorder_tree','parent_id','disorder_tree','id');
	}

	public function down()
	{
		$this->dropForeignKey('disorder_tree_parent_id_fk','disorder_tree');
		$this->dropIndex('disorder_tree_parent_id_fk','disorder_tree');
	}
}
