<?php

class m140403_143016_custom_model_attribute_labels extends CDbMigration
{
	public function up()
	{
		$this->createTable('model_attribute_label', array(
				'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
				'class' => 'varchar(64) NOT NULL',
				'attribute' => 'varchar(64) NOT NULL',
				'label' => 'varchar(64) NOT NULL',
				'last_modified_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'last_modified_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'created_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'created_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'PRIMARY KEY (`id`)',
				'KEY `model_attribute_label_last_modified_user_id_fk` (`last_modified_user_id`)',
				'KEY `model_attribute_label_created_user_id_fk` (`created_user_id`)',
				'CONSTRAINT `model_attribute_label_last_modified_user_id_fk` FOREIGN KEY (`last_modified_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `model_attribute_label_created_user_id_fk` FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`)',
			), 'ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');
	}

	public function down()
	{
		$this->dropTable('model_attribute_label');
	}
}
