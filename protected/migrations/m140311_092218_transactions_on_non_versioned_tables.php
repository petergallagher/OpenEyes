<?php

class m140311_092218_transactions_on_non_versioned_tables extends CDbMigration
{
	public $tables = array('audit','audit_action','audit_ipaddr','audit_model','audit_module','audit_server','audit_type','audit_useragent','authassignment','authitem','authitem_type','authitemchild','episode_summary','episode_summary_item','eye','gender','import_source');

	public function up()
	{
		foreach ($this->tables as $table) {
			$this->addColumn($table,'hash','varchar(40) not null');
			$this->addColumn($table,'transaction_id','int(10) unsigned null');
			$this->createIndex($table.'_transaction_id_fk',$table,'transaction_id');
			$this->addForeignKey($table.'_transaction_id_fk',$table,'transaction_id','transaction','id');
			$this->addColumn($table,'conflicted','tinyint(1) unsigned not null');
		}
	}

	public function down()
	{
		foreach ($this->tables as $table) {
			$this->dropColumn($table,'hash');
			$this->dropForeignKey($table.'_transaction_id_fk',$table);
			$this->dropIndex($table.'_transaction_id_fk',$table);
			$this->dropColumn($table,'transaction_id');
			$this->dropColumn($table,'conflicted');
		}
	}
}
