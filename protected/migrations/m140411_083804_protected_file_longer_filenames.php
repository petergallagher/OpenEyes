<?php

class m140411_083804_protected_file_longer_filenames extends CDbMigration
{
	public function up()
	{
		$this->alterColumn('protected_file','name','varchar(255) not null');
	}

	public function down()
	{
		$this->alterColumn('protected_file','name','varchar(64) not null');
	}
}
