<?php

class m140731_152011_family_history_changes extends OEMigration
{
	public function up()
	{
		$this->createTable('family_history_relative_side',array(
				'id' => 'int(10) unsigned NOT NULL AUTO_INCREMENT',
				'relative_id' => 'int(10) unsigned not null',
				'side_id' => 'int(10) unsigned not null',
				'last_modified_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'last_modified_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'created_user_id' => 'int(10) unsigned NOT NULL DEFAULT 1',
				'created_date' => 'datetime NOT NULL DEFAULT \'1901-01-01 00:00:00\'',
				'PRIMARY KEY (`id`)',
				'KEY `family_history_relative_side_lmui_fk` (`last_modified_user_id`)',
				'KEY `family_history_relative_side_cui_fk` (`created_user_id`)',
				'KEY `family_history_relative_side_ri_fk` (`relative_id`)',
				'KEY `family_history_relative_side_si_fk` (`side_id`)',
				'CONSTRAINT `family_history_relative_side_lmui_fk` FOREIGN KEY (`last_modified_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `family_history_relative_side_cui_fk` FOREIGN KEY (`created_user_id`) REFERENCES `user` (`id`)',
				'CONSTRAINT `family_history_relative_side_ri_fk` FOREIGN KEY (`relative_id`) REFERENCES `family_history_relative` (`id`)',
				'CONSTRAINT `family_history_relative_side_si_fk` FOREIGN KEY (`side_id`) REFERENCES `family_history_side` (`id`)',
			), 'ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

		$this->versionExistingTable('family_history_relative_side');

		$this->addColumn('family_history_side','always_show','tinyint(1) unsigned not null default 1');
		$this->addColumn('family_history_side_version','always_show','tinyint(1) unsigned not null default 1');

		$this->update('family_history_side',array('always_show' => 0),"name in ('Maternal','Paternal')");

		foreach ($this->dbConnection->createCommand()->select("*")->from("family_history_relative")->order("id asc")->queryAll() as $r) {
			foreach ($this->dbConnection->createCommand()->select("*")->from("family_history_side")->where("name in ('Maternal','Paternal')")->order("id asc")->queryAll() as $s) {
				if ($s['name'] == 'Maternal') {
					if (in_array($r['name'],array('Father','Brother','Sister','Uncle','Aunt','Cousin','Grandfather'))) {
						continue;
					}
				}

				if ($s['name'] == 'Paternal') {
					if (in_array($r['name'],array('Mother','Brother','Sister','Uncle','Aunt','Cousin','Grandmother'))) {
						continue;
					}
				}

				$this->insert('family_history_relative_side',array(
					'relative_id' => $r['id'],
					'side_id' => $s['id'],
				));
			}
		}
	}

	public function down()
	{
		$this->dropColumn('family_history_side','always_show');
		$this->dropColumn('family_history_side_version','always_show');

		$this->dropTable('family_history_relative_side_version');
		$this->dropTable('family_history_relative_side');
	}
}
