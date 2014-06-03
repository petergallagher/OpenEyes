<?php

class m140220_235959_referential_repairs extends OEMigration
{
	public function up()
	{
		$this->update('drug',array('default_frequency_id' => null),"default_frequency_id = 0");
		$this->update('drug',array('default_duration_id' => null),"default_duration_id = 0");
		$this->update('drug',array('default_route_id' => null),"default_route_id = 0");

		$proc_ids = array();
		foreach ($this->dbConnection->createCommand()->select("id")->from("proc")->queryAll() as $row) {
			$proc_ids[] = $row['id'];
		}

		if (!empty($proc_ids)) {
			$this->delete('proc_opcs_assignment',"proc_id not in (".implode(',',$proc_ids).")");
		}

		$this->renameColumn('disorder_tree','id','disorder_id');

		$this->addColumn('disorder_tree','id','int(10) unsigned NOT NULL');

		foreach ($this->dbConnection->createCommand()->select("*")->from("disorder_tree")->queryAll() as $i => $row) {
			$this->update('disorder_tree',array('id' => $i+1),"disorder_id = {$row['disorder_id']} and lft = {$row['lft']} and rght = {$row['rght']}");
		}

		$this->addPrimaryKey("id","disorder_tree","id");
		$this->alterColumn('disorder_tree','id','int(10) unsigned NOT NULL AUTO_INCREMENT');

		$this->addColumn('allergy','active','boolean not null default true');
		$this->addColumn('anaesthetic_agent','active','boolean not null default true');
		$this->addColumn('anaesthetic_complication','active','boolean not null default true');
		$this->addColumn('anaesthetic_delivery','active','boolean not null default true');
		$this->addColumn('anaesthetic_type','active','boolean not null default true');
		$this->addColumn('anaesthetist','active','boolean not null default true');
		$this->addColumn('benefit','active','boolean not null default true');
		$this->addColumn('complication','active','boolean not null default true');
		$this->addColumn('contact_label','active','boolean not null default true');
		$this->addColumn('country','active','boolean not null default true');
		$this->addColumn('disorder','active','boolean not null default true');
		$this->addColumn('drug','active','boolean not null default true');
		$this->addColumn('drug_duration','active','boolean not null default true');
		$this->addColumn('drug_form','active','boolean not null default true');
		$this->addColumn('drug_frequency','active','boolean not null default true');
		$this->addColumn('drug_route','active','boolean not null default true');
		$this->addColumn('drug_route_option','active','boolean not null default true');
		$this->addColumn('drug_set','active','boolean not null default true');
		$this->addColumn('drug_type','active','boolean not null default true');
		$this->addColumn('firm','active','boolean not null default true');
		$this->addColumn('institution','active','boolean not null default true');
		$this->addColumn('nsc_grade','active','boolean not null default true');
		$this->addColumn('opcs_code','active','boolean not null default true');
		$this->addColumn('operative_device','active','boolean not null default true');
		$this->addColumn('patient_oph_info_cvi_status','active','boolean not null default true');
		$this->addColumn('proc','active','boolean not null default true');
		$this->addColumn('site','active','boolean not null default true');
		$this->addColumn('specialty_type','active','boolean not null default true');
		$this->addColumn('subspecialty_subsection','active','boolean not null default true');

		$this->update('drug', array('active' => new CDbExpression('not (discontinued)')));
		$this->dropColumn('drug', 'discontinued');

		$null_ids = array();

		$limit = 10000;
		$offset = 0;

		while (1) {
			$data = $this->dbConnection->createCommand()->select("id,data")->from("audit")->where("data is not null and data != :blank",array(":blank" => ""))->order("id asc")->limit($limit)->offset($offset)->queryAll();

			if (empty($data)) break;

			foreach ($data as $row) {
				if (@unserialize($row['data'])) {
					$null_ids[] = $row['id'];

					if (count($null_ids) >= 1000) {
						$this->resetData($null_ids);
						$null_ids = array();
					}
				}
			}

			$offset += $limit;
		}

		if (!empty($null_ids)) {
			$this->resetData($null_ids);
		}

		$this->update('audit',array('data' => null),"data = ''");
	}

	public function resetData($null_ids)
	{
		$this->update('audit',array('data' => null),"id in (".implode(",",$null_ids).")");
	}

	public function down()
	{
		$this->addColumn('drug', 'discontinued', 'tinyint(1) unsigned not null');
		$this->update('drug', array('discontinued' => new CDbExpression('not (active)')));

		$this->dropColumn('allergy','active');
		$this->dropColumn('anaesthetic_agent','active');
		$this->dropColumn('anaesthetic_complication','active');
		$this->dropColumn('anaesthetic_delivery','active');
		$this->dropColumn('anaesthetic_type','active');
		$this->dropColumn('anaesthetist','active');
		$this->dropColumn('benefit','active');
		$this->dropColumn('complication','active');
		$this->dropColumn('contact_label','active');
		$this->dropColumn('country','active');
		$this->dropColumn('disorder','active');
		$this->dropColumn('drug','active');
		$this->dropColumn('drug_duration','active');
		$this->dropColumn('drug_form','active');
		$this->dropColumn('drug_frequency','active');
		$this->dropColumn('drug_route','active');
		$this->dropColumn('drug_route_option','active');
		$this->dropColumn('drug_set','active');
		$this->dropColumn('drug_type','active');
		$this->dropColumn('firm','active');
		$this->dropColumn('institution','active');
		$this->dropColumn('nsc_grade','active');
		$this->dropColumn('opcs_code','active');
		$this->dropColumn('operative_device','active');
		$this->dropColumn('patient_oph_info_cvi_status','active');
		$this->dropColumn('proc','active');
		$this->dropColumn('site','active');
		$this->dropColumn('specialty_type','active');
		$this->dropColumn('subspecialty_subsection','active');

		$this->alterColumn('disorder_tree','id','int(10) unsigned NOT NULL');
		$this->dropPrimaryKey('id','disorder_tree');

		$this->dropColumn('disorder_tree','id');

		$this->renameColumn('disorder_tree','disorder_id','id');
	}
}
