<?php

class m140806_085840_measurements_in_decimal extends CDbMigration
{
	public function up()
	{
		$this->alterColumn('measurement_weight','weight','float not null');
		$this->alterColumn('measurement_height','height','float not null');
	}

	public function down()
	{
		$this->alterColumn('measurement_weight','weight','tinyint(1) unsigned not null');
		$this->alterColumn('measurement_height','height','tinyint(1) unsigned not null');
	}
}
