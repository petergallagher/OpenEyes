<?php

class m140226_122531_add_measurement_tables extends OEMigration
{
	public function safeUp()
	{
		$this->createOETable(
			'measurement_type',
			array(
				'id' => 'pk',
				'class_name' => 'string not null',
				'attachable' => 'boolean not null',
			)
		);

		$this->createOETable(
			'patient_measurement',
			array(
				'id' => 'pk',
				'patient_id' => 'integer unsigned not null',
				'measurement_type_id' => 'integer not null',
				'constraint patient_measurement_patient_id_fk foreign key (patient_id) references patient (id)',
				'constraint patient_measurement_measurement_type_id_fk foreign key (measurement_type_id) references measurement_type (id)',
			)
		);

		$this->createOETable(
			'measurement_reference',
			array(
				'id' => 'pk',
				'patient_measurement_id' => 'integer not null',
				'episode_id' => 'integer unsigned',
				'event_id' => 'integer unsigned',
				'origin' => 'boolean default false',
				'constraint measurement_reference_patient_measurement_id_fk foreign key (patient_measurement_id) references patient_measurement (id)',
				'constraint measurement_reference_episode_id_fk foreign key (episode_id) references episode (id)',
				'constraint measurement_reference_event_id_fk foreign key (episode_id) references event (id)',
			)
		);
	}

	public function safeDown()
	{
		$this->dropTable('measurement_reference');
		$this->dropTable('patient_measurement');
		$this->dropTable('measurement_type');
	}
}
