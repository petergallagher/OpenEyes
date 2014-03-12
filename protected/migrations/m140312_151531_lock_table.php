<?php

class m140312_151531_lock_table extends CDbMigration
{
	public function up()
	{
		$this->execute("
CREATE TABLE `lock` (
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`item_table` varchar(255) NOT NULL,
	`item_id` int(10) unsigned NOT NULL,
	`last_modified_user_id` int(10) unsigned NOT NULL DEFAULT '1',
	`last_modified_date` datetime NOT NULL DEFAULT '1900-01-01 00:00:00',
	`created_user_id` int(10) unsigned NOT NULL DEFAULT '1',
	`created_date` datetime NOT NULL DEFAULT '1900-01-01 00:00:00',
	PRIMARY KEY (`id`),
	UNIQUE KEY `lock_item` (`item_table`,`item_id`),
	KEY `lock_created_user_id_fk` (`created_user_id`),
	KEY `lock_last_modified_user_id_fk` (`last_modified_user_id`),
	CONSTRAINT `lock_created_user_id_fk` FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`),
	CONSTRAINT `lock_last_modified_user_id_fk` FOREIGN KEY (`last_modified_user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
		");
	}

	public function down()
	{
		$this->dropTable('lock');
	}
}
