<?php

class m140401_144530_fix_patient_gender extends CDbMigration
{
	public function up()
	{
		$this->update('patient',array('gender' => '1'),"gender = 'M'");
		$this->update('patient',array('gender' => '2'),"gender = 'F'");

		$this->alterColumn('patient','gender','int(10) unsigned null');
		$this->update('patient',array('gender'=>null),"gender = 0");
		$this->renameColumn('patient','gender','gender_id');

		$this->createIndex('patient_gender_fk','patient','gender_id');
		$this->addForeignKey('patient_gender_fk','patient','gender_id','gender','id');
	}

	public function down()
	{
		$this->dropForeignKey('patient_gender_fk','patient');
		$this->dropIndex('patient_gender_fk','patient');

		$this->renameColumn('patient','gender_id','gender');
		$this->alterColumn('patient','gender','varchar(1) null');

		$this->update('patient',array('gender' => null),"gender = ''");
		$this->update('patient',array('gender' => 'M'),"gender = '1'");
		$this->update('patient',array('gender' => 'F'),"gender = '2'");
	}
}
