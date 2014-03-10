<?php

class m140310_161908_move_email_to_contact extends CDbMigration
{
	public function up()
	{
		$this->execute('alter table contact add email varchar(63) default null after last_name');
		$this->execute('alter table contact_version add email varchar(63) default null after last_name');

		$this->execute('update contact c set email = (select email from address a where a.contact_id = c.id and a.email is not null and a.email != "" limit 1)');

		$this->execute('alter table address drop email');
		$this->execute('alter table address_version drop email');
	}

	public function down()
	{
		$this->execute('alter table address_version add email varchar(255) default null after country_id');
		$this->execute('alter table address add email varchar(255) default null after country_id');

		$this->execute('update address a set email = (select email from contact c where c.id = a.contact_id)');

		$this->execute('alter table contact_version drop email');
		$this->execute('alter table contact drop email');
	}
}
