<?php

class m140402_094509_new_authitem_for_editing_patient_details extends CDbMigration
{
	public function up()
	{
		$this->insert('authitem',array('name' => 'OprnEditPatientDetails', 'type' => 0));
		$this->insert('authitemchild',array('parent' => 'TaskEditPatientData', 'child' => 'OprnEditPatientDetails'));
	}

	public function down()
	{
		$this->delete('authitemchild',"parent = 'TaskEditPatientData' and child = 'OprnEditPatientDetails'");
		$this->delete('authitem',"name = 'OprnEditPatientDetails'");
	}
}
