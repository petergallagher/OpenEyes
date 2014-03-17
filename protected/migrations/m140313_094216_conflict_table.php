<?php

class m140313_094216_conflict_table extends CDbMigration
{
	public function up()
	{
		$this->execute("
CREATE TABLE `conflict` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`resolved_transaction_id` int(10) unsigned NULL,
	`last_modified_user_id` int(10) unsigned NOT NULL DEFAULT '1',
	`last_modified_date` datetime NOT NULL DEFAULT '1900-01-01 00:00:00',
	`created_user_id` int(10) unsigned NOT NULL DEFAULT '1',
	`created_date` datetime NOT NULL DEFAULT '1900-01-01 00:00:00',
	PRIMARY KEY (`id`),
	KEY `conflict_created_user_id_fk` (`created_user_id`),
	KEY `conflict_last_modified_user_id_fk` (`last_modified_user_id`),
	KEY `conflict_resolved_transaction_id_fk` (`resolved_transaction_id`),
	CONSTRAINT `conflict_created_user_id_fk` FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`),
	CONSTRAINT `conflict_last_modified_user_id_fk` FOREIGN KEY (`last_modified_user_id`) REFERENCES `user` (`id`),
	CONSTRAINT `conflict_resolved_transaction_id_fk` FOREIGN KEY (`resolved_transaction_id`) REFERENCES `transaction` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
		");

		$this->execute("
CREATE TABLE `transaction_conflict_assignment` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`transaction_id` int(10) unsigned NOT NULL,
	`conflict_id` int(10) unsigned NOT NULL,
	`last_modified_user_id` int(10) unsigned NOT NULL DEFAULT '1',
	`last_modified_date` datetime NOT NULL DEFAULT '1900-01-01 00:00:00',
	`created_user_id` int(10) unsigned NOT NULL DEFAULT '1',
	`created_date` datetime NOT NULL DEFAULT '1900-01-01 00:00:00',
	PRIMARY KEY (`id`),
	KEY `tca_created_user_id_fk` (`created_user_id`),
	KEY `tca_last_modified_user_id_fk` (`last_modified_user_id`),
	KEY `tca_transaction_id_fk` (`transaction_id`),
	KEY `tca_conflict_id_fk` (`conflict_id`),
	UNIQUE KEY `tca_transaction_conflict_id_fk` (`transaction_id`,`conflict_id`),
	CONSTRAINT `tca_created_user_id_fk` FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`),
	CONSTRAINT `tca_last_modified_user_id_fk` FOREIGN KEY (`last_modified_user_id`) REFERENCES `user` (`id`),
	CONSTRAINT `tca_transaction_id_fk` FOREIGN KEY (`transaction_id`) REFERENCES `transaction` (`id`),
	CONSTRAINT `tca_conflict_id_fk` FOREIGN KEY (`conflict_id`) REFERENCES `conflict` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
		");
	}

	public function down()
	{
		$this->dropTable('transaction_conflict_assignment');
		$this->dropTable('conflict');
	}
}
