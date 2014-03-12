<?php

/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */
class BaseActiveRecordVersionedSoftDeleteTest extends CDbTestCase
{
	public $transactions;
	public $fixtures = array(
		'users' => 'User',
		'drugs' => 'Drug',
		'drug_types' => 'DrugType',
		'drug_forms' => 'DrugForm',
		'drug_frequencies' => 'DrugFrequency',
		'drug_durations' => 'DrugDuration',
		'drug_routes' => 'DrugRoute',
		'countries' => 'Country',
	);

	protected function setUp()
	{
		parent::setUp();

		$this->transactions = Yii::app()->params['enable_transactions'];

		Yii::app()->params['enable_transactions'] = true;
	}

	protected function tearDown()
	{
		Yii::app()->params['enable_transactions'] = $this->transactions;
	}

	public function testDeleteWithNotDeletedField()
	{
		$transaction = Yii::app()->db->beginTransaction('Delete','User');

		$user = User::model()->findByPk(2);
		$user->delete();

		$transaction->commit();

		$user = User::model()->findByPk(2);

		$this->assertEquals(0, $user->active);

		// Cleanup
		$transaction = Yii::app()->db->beginTransaction('Undelete','User');

		$user->undelete();
		Yii::app()->db->createCommand("delete from user_version where id = 2")->query();

		$transaction->commit();
	}

	public function testDeleteWithDeletedField()
	{
		$transaction = Yii::app()->db->beginTransaction('Delete','Drug');

		$drug = Drug::model()->findByPk(1);
		$drug->delete();

		$transaction->commit();

		$drug = Drug::model()->findByPk(1);

		$this->assertEquals(1, $drug->discontinued);
		
		// Cleanup
		$transaction = Yii::app()->db->beginTransaction('Undelete','Drug');

		$drug->undelete();
		Yii::app()->db->createCommand("delete from drug_version where id = 1")->query();
		
		$transaction->commit();
	}

	public function testDeleteWithInheritedDeletedField()
	{
		$transaction = Yii::app()->db->beginTransaction('Delete','Country');

		$country = Country::model()->findByPk(1);
		$country->delete();

		$transaction->commit();

		$country = Country::model()->findByPk(1);

		$this->assertEquals(1, $country->deleted);

		// Cleanup
		$transaction = Yii::app()->db->beginTransaction('Undelete','Country');

		$country->undelete();
		Yii::app()->db->createCommand("delete from country_version where id = 1")->query();

		$transaction->commit();
	}

	public function testDeleteWithTransactionsEnabledThrowsExceptionIfNoTransaction()
	{
		$this->setExpectedException('Exception', 'delete() called without a transaction');

		$country = Country::model()->findByPk(1);
		$country->delete();
	}

	public function testUndelete()
	{
		$transaction = Yii::app()->db->beginTransaction('Delete','Country');

		$country = Country::model()->findByPk(1);
		$country->delete();

		$transaction->commit();

		$transaction = Yii::app()->db->beginTransaction('Undelete','Country');

		$country->undelete();

		$transaction->commit();

		$this->assertEquals(0, $country->deleted);

		// Cleanup
		Yii::app()->db->createCommand("delete from country_version where id = 1")->query();
	}

	public function testUndeleteWithTransactionsEnabledThrowsExceptionIfNoTransaction()
	{
		$this->setExpectedException('Exception', 'undelete() called without a transaction');

		$country = Country::model()->findByPk(1);
		$country->undelete();
	}

	public function testDeleteByPkWithNotDeletedField()
	{
		$transaction = Yii::app()->db->beginTransaction('Delete','User');

		$user = User::model()->deleteByPk(2);

		$transaction->commit();

		$user = User::model()->findByPk(2);

		$this->assertEquals(0, $user->active);

		// Cleanup
		$transaction = Yii::app()->db->beginTransaction('Undelete','User');

		$user->undelete();
		Yii::app()->db->createCommand("delete from user_version where id = 2")->query();

		$transaction->commit();
	}

	public function testDeleteByPkWithDeletedField()
	{
		$transaction = Yii::app()->db->beginTransaction('Delete','Drug');

		$drug = Drug::model()->deleteByPk(1);

		$transaction->commit();

		$drug = Drug::model()->findByPk(1);

		$this->assertEquals(1, $drug->discontinued);

		// Cleanup
		$transaction = Yii::app()->db->beginTransaction('Undelete','Drug');

		$drug->undelete();
		Yii::app()->db->createCommand("delete from drug_version where id = 1")->query();

		$transaction->commit();
	}

	public function testDeleteByPkWithInheritedDeletedField()
	{
		$transaction = Yii::app()->db->beginTransaction('Delete','Country');

		$country = Country::model()->deleteByPk(1);

		$transaction->commit();

		$country = Country::model()->findByPk(1);

		$this->assertEquals(1, $country->deleted);

		// Cleanup
		$transaction = Yii::app()->db->beginTransaction('Undelete','Country');

		$country->undelete();
		Yii::app()->db->createCommand("delete from country_version where id = 1")->query();

		$transaction->commit();
	}

	public function testDeleteByPkWithTransactionsEnabledThrowsExceptionIfNoTransaction()
	{
		$this->setExpectedException('Exception', 'deleteByPk() called without a transaction');

		$country = Country::model()->deleteByPk(1);
	}

	public function testDeleteAllWithNotDeletedField()
	{
		$transaction = Yii::app()->db->beginTransaction('Delete','User');

		$user = User::model()->deleteAll('id = :id1 or id = :id2',array(':id1' => 2, ':id2' => 3));

		$transaction->commit();

		$user1 = User::model()->findByPk(2);
		$user2 = User::model()->findByPk(3);

		$this->assertEquals(0, $user1->active);
		$this->assertEquals(0, $user2->active);

		// Cleanup
		$transaction = Yii::app()->db->beginTransaction('Undelete','User');

		$user1->undelete();
		$user2->undelete();
		Yii::app()->db->createCommand("delete from user_version where id in (2,3)")->query();

		$transaction->commit();
	}

	public function testDeleteAllWithDeletedField()
	{
		$transaction = Yii::app()->db->beginTransaction('Delete','Drug');

		$drug = Drug::model()->deleteAll('id = :id1 or id = :id2',array(':id1' => 1, ':id2' => 2));

		$transaction->commit();

		$drug1 = Drug::model()->findByPk(1);
		$drug2 = Drug::model()->findByPk(2);

		$this->assertEquals(1, $drug1->discontinued);
		$this->assertEquals(1, $drug2->discontinued);

		// Cleanup
		$transaction = Yii::app()->db->beginTransaction('Undelete','Drug');

		$drug1->undelete();
		$drug2->undelete();
		Yii::app()->db->createCommand("delete from drug_version where id in (1,2)")->query();

		$transaction->commit();
	}

	public function testDeleteAllWithInheritedDeletedField()
	{
		$transaction = Yii::app()->db->beginTransaction('Delete','Country');

		$country = Country::model()->deleteAll('id in (1,2,3)');

		$transaction->commit();

		$country1 = Country::model()->findByPk(1);
		$country2 = Country::model()->findByPk(2);
		$country3 = Country::model()->findByPk(3);

		$this->assertEquals(1, $country1->deleted);
		$this->assertEquals(1, $country2->deleted);
		$this->assertEquals(1, $country3->deleted);

		// Cleanup
		$transaction = Yii::app()->db->beginTransaction('Undelete','Country');

		$country1->undelete();
		$country2->undelete();
		$country3->undelete();
		Yii::app()->db->createCommand("delete from country_version where id in (1,2,3)")->query();

		$transaction->commit();
	}

	public function testDeleteAllWithTransactionsEnabledThrowsExceptionIfNoTransaction()
	{
		$this->setExpectedException('Exception', 'deleteAll() called without a transaction');

		$country = Country::model()->deleteAll();
	}

	public function testDeleteAllByAttributesWithNotDeletedField_NoCriteria()
	{
		$transaction = Yii::app()->db->beginTransaction('Delete','User');

		$user = User::model()->deleteAllByAttributes(array('id' => 2));

		$transaction->commit();

		$user = User::model()->findByPk(2);

		$this->assertEquals(0, $user->active);

		// Cleanup
		$transaction = Yii::app()->db->beginTransaction('Undelete','User');

		$user->undelete();
		Yii::app()->db->createCommand("delete from user_version where id	= 2")->query();

		$transaction->commit();
	}

	public function testDeleteAllByAttributesWithNotDeletedField_WithCriteria()
	{
		$transaction = Yii::app()->db->beginTransaction('Delete','User');

		$user = User::model()->deleteAllByAttributes(array('id' => 2), new CDbCriteria);

		$transaction->commit();

		$user = User::model()->findByPk(2);

		$this->assertEquals(0, $user->active);

		// Cleanup
		$transaction = Yii::app()->db->beginTransaction('Undelete','User');

		$user->undelete();
		Yii::app()->db->createCommand("delete from user_version where id	= 2")->query();

		$transaction->commit();
	}

	public function testDeleteAllByAttributesWithDeletedField_NoCriteria()
	{
		$transaction = Yii::app()->db->beginTransaction('Delete','Drug');

		$drug = Drug::model()->deleteAllByAttributes(array('id' => 1));

		$transaction->commit();

		$drug = Drug::model()->findByPk(1);

		$this->assertEquals(1, $drug->discontinued);

		// Cleanup
		$transaction = Yii::app()->db->beginTransaction('Undelete','Drug');

		$drug->undelete();
		Yii::app()->db->createCommand("delete from drug_version where id = 1")->query();

		$transaction->commit();
	}

	public function testDeleteAllByAttributesWithTransactionsEnabledThrowsExceptionIfNoTransaction()
	{
		$this->setExpectedException('Exception', 'deleteAllByAttributes() called without a transaction');

		$country = Country::model()->deleteAllByAttributes(array('id'=>1));
	}

	public function testDeleteAllByAttributesWithDeletedField_WithCriteria()
	{
		$transaction = Yii::app()->db->beginTransaction('Delete','Drug');

		$drug = Drug::model()->deleteAllByAttributes(array('id' => 1), new CDbCriteria);

		$transaction->commit();

		$drug = Drug::model()->findByPk(1);

		$this->assertEquals(1, $drug->discontinued);

		// Cleanup
		$transaction = Yii::app()->db->beginTransaction('Undelete','Drug');

		$drug->undelete();
		Yii::app()->db->createCommand("delete from drug_version where id = 1")->query();

		$transaction->commit();
	}

	public function testDeleteAllByAttributesWithInheritedDeletedField_NoCriteria()
	{
		$transaction = Yii::app()->db->beginTransaction('Delete','Drug');

		$drug = Drug::model()->deleteAllByAttributes(array('id' => 1));

		$transaction->commit();

		$drug = Drug::model()->findByPk(1);

		$this->assertEquals(1, $drug->discontinued);

		// Cleanup
		$transaction = Yii::app()->db->beginTransaction('Undelete','Drug');

		$drug->undelete();
		Yii::app()->db->createCommand("delete from drug_version where id = 1")->query();

		$transaction->commit();
	}	

	public function testDeleteAllByAttributesWithInheritedDeletedField_Criteria()
	{
		$transaction = Yii::app()->db->beginTransaction('Delete','Drug');

		$drug = Drug::model()->deleteAllByAttributes(array('id' => 1), new CDbCriteria);

		$transaction->commit();

		$drug = Drug::model()->findByPk(1);

		$this->assertEquals(1, $drug->discontinued);

		// Cleanup
		$transaction = Yii::app()->db->beginTransaction('Undelete','Drug');

		$drug->undelete();
		Yii::app()->db->createCommand("delete from drug_version where id = 1")->query();

		$transaction->commit();
	}

	public function testModelsThatInheritFromThisModelReturnDeletedItemsByDefault()
	{
		$users = User::model()->findAll(array('order' => 'id asc'));

		$this->assertCount(4, $users);
		$this->assertEquals(1, $users[0]->id);
		$this->assertEquals(2, $users[1]->id);
		$this->assertEquals(3, $users[2]->id);
		$this->assertEquals(4, $users[3]->id);
	}

	public function testNotDeleted()
	{
		$users = User::model()->notDeleted()->findAll(array('order' => 'id asc'));

		$this->assertCount(3, $users);
		$this->assertEquals(1, $users[0]->id);
		$this->assertEquals(2, $users[1]->id);
		$this->assertEquals(4, $users[2]->id);
	}

	public function testNotDeletedOrPk_SingleID()
	{
		$transaction = Yii::app()->db->beginTransaction('Delete','User');

		$user = User::model()->findByPk(1);
		$user->delete();

		$transaction->commit();

		$users = User::model()->notDeletedOrPk(1)->findAll(array('order' => 'id asc'));

		$this->assertCount(3, $users);
		$this->assertEquals(1, $users[0]->id);
		$this->assertEquals(2, $users[1]->id);
		$this->assertEquals(4, $users[2]->id);

		// Cleanup
		$transaction = Yii::app()->db->beginTransaction('Undelete','User');

		$user->undelete();
		Yii::app()->db->createCommand("delete from user_version where id = 1")->query();

		$transaction->commit();
	}

	public function testNotDeletedOrPk_Array()
	{
		$transaction = Yii::app()->db->beginTransaction('Delete','User');

		$user = User::model()->findByPk(1);
		$user->delete();

		$transaction->commit();

		$users = User::model()->notDeletedOrPk(array(1,3))->findAll(array('order' => 'id asc'));

		$this->assertCount(4, $users);
		$this->assertEquals(1, $users[0]->id);
		$this->assertEquals(2, $users[1]->id);
		$this->assertEquals(3, $users[2]->id);
		$this->assertEquals(4, $users[3]->id);

		// Cleanup
		$transaction = Yii::app()->db->beginTransaction('Undelete','User');

		$user->undelete();
		Yii::app()->db->createCommand("delete from user_version where id = 1")->query();

		$transaction->commit();
	}

	public function testNotDeletedOrPk_BlankValue()
	{
		$transaction = Yii::app()->db->beginTransaction('Delete','User');

		$user = User::model()->findByPk(1);
		$user->delete();

		$transaction->commit();

		$users = User::model()->notDeletedOrPk(null)->findAll(array('order' => 'id asc'));

		$this->assertCount(2, $users);
		$this->assertEquals(2, $users[0]->id);
		$this->assertEquals(4, $users[1]->id);

		// Cleanup
		$transaction = Yii::app()->db->beginTransaction('Undelete','User');

		$user->undelete();
		Yii::app()->db->createCommand("delete from user_version where id = 1")->query();

		$transaction->commit();
	}

	public function testActive()
	{
		$users = User::model()->active()->findAll(array('order' => 'id asc'));

		$this->assertCount(3, $users);
		$this->assertEquals(1, $users[0]->id);
		$this->assertEquals(2, $users[1]->id);
		$this->assertEquals(4, $users[2]->id);
	}

	public function testActiveOrPk_SingleID()
	{
		$transaction = Yii::app()->db->beginTransaction('Delete','User');

		$user = User::model()->findByPk(1);
		$user->delete();

		$transaction->commit();

		$users = User::model()->activeOrPk(1)->findAll(array('order' => 'id asc'));

		$this->assertCount(3, $users);
		$this->assertEquals(1, $users[0]->id);
		$this->assertEquals(2, $users[1]->id);
		$this->assertEquals(4, $users[2]->id);

		// Cleanup
		$transaction = Yii::app()->db->beginTransaction('Undelete','User');

		$user->undelete();
		Yii::app()->db->createCommand("delete from user_version where id = 1")->query();

		$transaction->commit();
	}

	public function testActiveOrPk_Array()
	{
		$transaction = Yii::app()->db->beginTransaction('Delete','User');

		$user = User::model()->findByPk(1);
		$user->delete();

		$transaction->commit();

		$users = User::model()->activeOrPk(array(1,3))->findAll(array('order' => 'id asc'));

		$this->assertCount(4, $users);
		$this->assertEquals(1, $users[0]->id);
		$this->assertEquals(2, $users[1]->id);
		$this->assertEquals(3, $users[2]->id);
		$this->assertEquals(4, $users[3]->id);

		// Cleanup
		$transaction = Yii::app()->db->beginTransaction('Undelete','User');

		$user->undelete();
		Yii::app()->db->createCommand("delete from user_version where id = 1")->query();

		$transaction->commit();
	}

	public function testActiveOrPk_BlankValue()
	{
		$transaction = Yii::app()->db->beginTransaction('Delete','User');

		$user = User::model()->findByPk(1);
		$user->delete();

		$transaction->commit();

		$users = User::model()->activeOrPk(null)->findAll(array('order' => 'id asc'));

		$this->assertCount(2, $users);
		$this->assertEquals(2, $users[0]->id);
		$this->assertEquals(4, $users[1]->id);

		// Cleanup
		$transaction = Yii::app()->db->beginTransaction('Undelete','User');

		$user->undelete();
		Yii::app()->db->createCommand("delete from user_version where id = 1")->query();

		$transaction->commit();
	}
}
