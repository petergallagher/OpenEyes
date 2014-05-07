<?php

class m140507_160514_allergies_admin extends CDbMigration
{
	public function up()
	{
		$this->addColumn('allergy', 'display_order', 'int(10) unsigned');
	}

	public function down()
	{
		$this->dropColumn('allergy', 'display_order');
	}

}