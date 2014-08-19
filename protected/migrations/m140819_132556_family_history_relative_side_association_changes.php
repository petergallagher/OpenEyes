<?php

class m140819_132556_family_history_relative_side_association_changes extends CDbMigration
{
	public function up()
	{
		foreach (array(
			'Grandmother' => 'Paternal',
			'Grandfather' => 'Maternal',
			) as $relative => $side) {
			$_relative = $this->dbConnection->createCommand()->select("*")->from("family_history_relative")->where("name = :n",array(":n" => $relative))->queryRow();
			$_side = $this->dbConnection->createCommand()->select("*")->from("family_history_side")->where("name = :n",array(":n" => $side))->queryRow();

			$this->insert('family_history_relative_side',array('relative_id' => $_relative['id'],'side_id' => $_side['id']));
		}
	}

	public function down()
	{
		foreach (array(
			'Grandmother' => 'Paternal',
			'Grandfather' => 'Maternal',
			) as $relative => $side) {

			$_relative = $this->dbConnection->createCommand()->select("*")->from("family_history_relative")->where("name = :n",array(":n" => $relative))->queryRow();
			$_side = $this->dbConnection->createCommand()->select("*")->from("family_history_side")->where("name = :n",array(":n" => $side))->queryRow();

			$this->delete('family_history_relative_side',"relative_id = {$_relative['id']} and side_id = {$_side['id']}");
		}
	}
}
