<?php

class m141231_132551_disorder_tree_parents extends CDbMigration
{
	public function up()
	{
		$this->addColumn('disorder_tree','parent_id','int(10) unsigned null');
		$this->addColumn('disorder_tree_version','parent_id','int(10) unsigned null');

		$lowest_id = $this->dbConnection->createCommand()->select("min(id)")->from("disorder_tree")->queryScalar();

		$parents = array();

		foreach ($this->dbConnection->createCommand()->select("*")->from("disorder_tree")->queryAll() as $row) {
			$parent_id = $this->dbConnection->createCommand()->select("max(id)")->from("disorder_tree")->where("lft < :l and rght > :r",array(":l" => $row['lft'], ":r" => $row['rght']))->queryScalar();

			$this->update('disorder_tree',array('parent_id' => $parent_id),"id = {$row['id']}");
		}
	}

	public function down()
	{
		$this->dropColumn('disorder_tree','parent_id');
		$this->dropColumn('disorder_tree_version','parent_id');
	}
}
