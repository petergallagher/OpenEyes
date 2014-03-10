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
class BaseActiveRecordVersionedTest extends CDbTestCase
{
	public $fixtures = array(
		'firms' => 'Firm',
		'users' => 'User',
		'user_versions' => ':user_version',
		'specialty' => 'Specialty',
		'specialty_versions' => ':specialty_version',
		'service_subspecialty_assignment' => 'ServiceSubspecialtyAssignment',
		'service_subspecialty_assignment_versions' => ':service_subspecialty_assignment_version',
		'patient' => 'Patient',
		'patient_versions' => ':patient_version',
		'episode' => 'Episode',
		'episode_versions' => ':episode_version',
		'patient_allergy_assignment' => 'PatientAllergyAssignment',
		'allergy' => 'Allergy',
		'address' => 'Address',
		'contact' => 'Contact',
		'disorder' => 'Disorder',
		'secondary_diagnosis' => 'SecondaryDiagnosis',
		'secondary_diagnosis_version' => ':secondary_diagnosis_version',
		'transaction' => 'Transaction',
		'patient_allergy_assignment_version' => ':patient_allergy_assignment_version',
		'firm_user_assignment' => 'FirmUserAssignment',
	);

	protected function setUp()
	{
		parent::setUp();

		$this->model = new User;

		Yii::app()->params['enable_transactions'] = true;
	}

	protected function tearDown()
	{
	}

	public function testDefaultVersionCreateStatus()
	{
		$this->assertTrue($this->model->versionCreateStatus);
	}

	public function testNoVersion()
	{
		$this->model->noVersion();

		$this->assertFalse($this->model->versionCreateStatus);

		$this->model->withVersion();
	}

	public function testWithVersion()
	{
		$this->model->noVersion();
		$this->model->withVersion();

		$this->assertTrue($this->model->versionCreateStatus);
	}

	public function testDefaultVersionRetrievalStatus()
	{
		$this->assertFalse($this->model->versionRetrievalStatus);
	}

	public function testFromVersion()
	{
		$model = $this->model->fromVersion();

		// fromVersion() clones the object so the original model shouldn't be touched
		$this->assertFalse($this->model->versionRetrievalStatus);
		$this->assertTrue($model->versionRetrievalStatus);
	}

	public function testNotFromVersion()
	{
		$model = $this->model->fromVersion();

		$model2 = $model->notFromVersion();

		// notFromVersion() clones the object so $model shouldn't be touched
		$this->assertTrue($model->versionRetrievalStatus);
		$this->assertFalse($model2->versionRetrievalStatus);
	}

	public function testGetTableSchemaNotFromVersion()
	{
		$schema = $this->model->tableSchema;

		$this->assertEquals('user',$schema->name);
	}

	public function testGetTableSchemaFromVersion()
	{
		$model = $this->model->fromVersion();
		$schema = $model->tableSchema;

		$this->assertEquals('user_version',$schema->name);
	}

	public function testGetPreviousVersion()
	{
		$user = User::model()->findByPk(1);

		$previous = $user->getPreviousVersion();

		$this->assertEquals($this->user_versions['user_version2']['transaction_id'],$previous->transaction_id);
		$this->assertEquals($this->user_versions['user_version2']['username'],$previous->username);
		$this->assertEquals($this->user_versions['user_version2']['first_name'],$previous->first_name);
		$this->assertEquals($this->user_versions['user_version2']['last_name'],$previous->last_name);
		$this->assertEquals($this->user_versions['user_version2']['email'],$previous->email);

		$previous = $previous->getPreviousVersion();

		$this->assertEquals($this->user_versions['user_version1']['transaction_id'],$previous->transaction_id);
		$this->assertEquals($this->user_versions['user_version1']['username'],$previous->username);
		$this->assertEquals($this->user_versions['user_version1']['first_name'],$previous->first_name);
		$this->assertEquals($this->user_versions['user_version1']['last_name'],$previous->last_name);
		$this->assertEquals($this->user_versions['user_version1']['email'],$previous->email);

		$previous = $previous->getPreviousVersion();

		$this->assertNull($previous);
	}

	public function testGetPreviousVersionByTransactionID()
	{
		$user = User::model()->findByPk(1);

		$previous = $user->getPreviousVersionByTransactionID(2);

		$this->assertEquals($this->user_versions['user_version2']['transaction_id'],$previous->transaction_id);
		$this->assertEquals($this->user_versions['user_version2']['username'],$previous->username);
		$this->assertEquals($this->user_versions['user_version2']['first_name'],$previous->first_name);
		$this->assertEquals($this->user_versions['user_version2']['last_name'],$previous->last_name);
		$this->assertEquals($this->user_versions['user_version2']['email'],$previous->email);
		
		$previous = $user->getPreviousVersionByTransactionID(3);

		$this->assertEquals($this->user_versions['user_version1']['transaction_id'],$previous->transaction_id);
		$this->assertEquals($this->user_versions['user_version1']['username'],$previous->username);
		$this->assertEquals($this->user_versions['user_version1']['first_name'],$previous->first_name);
		$this->assertEquals($this->user_versions['user_version1']['last_name'],$previous->last_name);
		$this->assertEquals($this->user_versions['user_version1']['email'],$previous->email);

		$previous = $user->getPreviousVersionByTransactionID(4);

		$this->assertNull($previous);
	}

	public function testHasTransactionID()
	{
		for ($i=1; $i<=10; $i++) {
			if (in_array($i,array(2,3))) {
				$this->assertTrue($this->model->hasTransactionID($i));
			} else {
				$this->assertFalse($this->model->hasTransactionID($i));
			}
		}
	}

	public function testGetPreviousVersions()
	{
		$user = User::model()->findByPk(1);

		$previous_versions = $user->getPreviousVersions();

		$this->assertCount(2, $previous_versions);

		$this->assertEquals($this->user_versions['user_version2']['transaction_id'],$previous_versions[0]->transaction_id);
		$this->assertEquals($this->user_versions['user_version2']['username'],$previous_versions[0]->username);
		$this->assertEquals($this->user_versions['user_version2']['first_name'],$previous_versions[0]->first_name);
		$this->assertEquals($this->user_versions['user_version2']['last_name'],$previous_versions[0]->last_name);
		$this->assertEquals($this->user_versions['user_version2']['email'],$previous_versions[0]->email);
		
		$this->assertEquals($this->user_versions['user_version1']['transaction_id'],$previous_versions[1]->transaction_id);
		$this->assertEquals($this->user_versions['user_version1']['username'],$previous_versions[1]->username);
		$this->assertEquals($this->user_versions['user_version1']['first_name'],$previous_versions[1]->first_name);
		$this->assertEquals($this->user_versions['user_version1']['last_name'],$previous_versions[1]->last_name);
		$this->assertEquals($this->user_versions['user_version1']['email'],$previous_versions[1]->email);
	}

	public function testGetVersionTableSchema()
	{
		$schema = $this->model->getVersionTableSchema();

		$this->assertInstanceOf('CMysqlTableSchema',$schema);
		$this->assertEquals('user_version',$schema->name);
	}

	public function testGetCommandBuilder()
	{
		$command_builder = $this->model->getCommandBuilder();

		$this->assertInstanceOf('OECommandBuilder',$command_builder);
	}

	public function testUpdateByPk_TransactionsOff_WithoutTransaction()
	{
		Yii::app()->params['enable_transactions'] = false;

		$this->model->updateByPk(1, array(
			'id' => 1,
		));

		$this->assertTrue(true);

		// Cleanup
		Yii::app()->params['enable_transactions'] = true;
	}

	public function testUpdateByPk_TransactionsOn_WithoutTransaction()
	{
		$this->setExpectedException('Exception', 'updateByPk() called without a transaction');

		$this->model->updateByPk(1, array(
			'id' => 1,
		));
	}

	public function testUpdateByPk_TransactionsOn_WithTransaction()
	{
		$transaction = Yii::app()->db->beginTransaction('Update','User');

		$this->model->updateByPk(1, array(
			'id' => 1,
		));

		$transaction->commit();

		$this->assertTrue(true);
	}

	public function testUpdateByPkWithVersioning()
	{
		$transaction = Yii::app()->db->beginTransaction('Update','User');

		$this->model->updateByPk(1, array(
			'username' => 'test1',
			'first_name' => 'test2',
			'last_name' => 'test3',
			'email' => 'test@test.aa',
		));

		$transaction->commit();

		$user = User::model()->findByPk(1);

		$this->assertEquals('test1',$user->username);
		$this->assertEquals('test2',$user->first_name);
		$this->assertEquals('test3',$user->last_name);
		$this->assertEquals('test@test.aa',$user->email);

		$previous_version = $user->getPreviousVersion();

		$this->assertEquals($this->users['user1']['username'],$previous_version->username);
		$this->assertEquals($this->users['user1']['first_name'],$previous_version->first_name);
		$this->assertEquals($this->users['user1']['last_name'],$previous_version->last_name);
		$this->assertEquals($this->users['user1']['email'],$previous_version->email);

		// Cleanup
		Yii::app()->db->createCommand("update user set username = '{$this->users['user1']['username']}', first_name = '{$this->users['user1']['first_name']}', last_name = '{$this->users['user1']['last_name']}', email = '{$this->users['user1']['email']}' where id = 1")->query();
		Yii::app()->db->createCommand("delete from user_version where version_id = $previous_version->version_id")->query();
	}

	public function testUpdateByPkWithoutVersioning()
	{
		$transaction = Yii::app()->db->beginTransaction('Update','User');

		$this->model->noVersion()->updateByPk(1, array(	
			'username' => 'test1',
			'first_name' => 'test2',
			'last_name' => 'test3',
			'email' => 'test@test.aa',
		));

		$transaction->commit();

		$user = User::model()->findByPk(1);

		$this->assertEquals('test1',$user->username);
		$this->assertEquals('test2',$user->first_name);
		$this->assertEquals('test3',$user->last_name);
		$this->assertEquals('test@test.aa',$user->email);
	 
		$previous_version = $user->getPreviousVersion();
	 
		$this->assertEquals($this->user_versions['user_version2']['username'],$previous_version->username);
		$this->assertEquals($this->user_versions['user_version2']['first_name'],$previous_version->first_name);
		$this->assertEquals($this->user_versions['user_version2']['last_name'],$previous_version->last_name);
		$this->assertEquals($this->user_versions['user_version2']['email'],$previous_version->email);

		// Cleanup
		Yii::app()->db->createCommand("update user set username = '{$this->users['user1']['username']}', first_name = '{$this->users['user1']['first_name']}', last_name = '{$this->users['user1']['last_name']}', email = '{$this->users['user1']['email']}' where id = 1")->query();
	}

	public function testUpdateAll_TransactionsOff_WithoutTransaction()
	{
		Yii::app()->params['enable_transactions'] = false;

		$this->model->updateAll(array(
			'active' => 1,
		));

		$this->assertTrue(true);

		// Cleanup
		Yii::app()->params['enable_transactions'] = true;
	}

	public function testUpdateAll_TransactionsOn_WithoutTransaction()
	{
		$this->setExpectedException('Exception', 'updateAll() called without a transaction');

		$this->model->updateAll(array(
			'active' => 1,
		));
	}

	public function testUpdateAll_TransactionsOn_WithTransaction()
	{
		$transaction = Yii::app()->db->beginTransaction('Update','User');

		$this->model->updateAll(array(
			'active' => 1,
		));

		$transaction->commit();

		$this->assertTrue(true);
	}

	public function testUpdateAllWithVersioning()
	{
		$transaction = Yii::app()->db->beginTransaction('Update','User');

		$this->model->updateAll(array(
				'username' => 'test1',
				'first_name' => 'test2',
				'last_name' => 'test3',
				'email' => 'test@test.aa',
			),
			'id >= 1 and id <= 3'
		);

		$transaction->commit();

		$user = User::model()->findByPk(1);

		$this->assertEquals('test1',$user->username);
		$this->assertEquals('test2',$user->first_name);
		$this->assertEquals('test3',$user->last_name);
		$this->assertEquals('test@test.aa',$user->email);
	
		$previous_version = $user->getPreviousVersion();
	
		$this->assertEquals($this->users['user1']['username'],$previous_version->username);
		$this->assertEquals($this->users['user1']['first_name'],$previous_version->first_name);
		$this->assertEquals($this->users['user1']['last_name'],$previous_version->last_name);
		$this->assertEquals($this->users['user1']['email'],$previous_version->email);

		$user = User::model()->findByPk(2);

		$this->assertEquals('test1',$user->username);
		$this->assertEquals('test2',$user->first_name);
		$this->assertEquals('test3',$user->last_name);
		$this->assertEquals('test@test.aa',$user->email);
	
		$previous_version = $user->getPreviousVersion();
	
		$this->assertEquals($this->users['user2']['username'],$previous_version->username);
		$this->assertEquals($this->users['user2']['first_name'],$previous_version->first_name);
		$this->assertEquals($this->users['user2']['last_name'],$previous_version->last_name);
		$this->assertEquals($this->users['user2']['email'],$previous_version->email);

		$user = User::model()->findByPk(3);

		$this->assertEquals('test1',$user->username);
		$this->assertEquals('test2',$user->first_name);
		$this->assertEquals('test3',$user->last_name);
		$this->assertEquals('test@test.aa',$user->email);
	
		$previous_version = $user->getPreviousVersion();
	
		$this->assertEquals($this->users['user3']['username'],$previous_version->username);
		$this->assertEquals($this->users['user3']['first_name'],$previous_version->first_name);
		$this->assertEquals($this->users['user3']['last_name'],$previous_version->last_name);
		$this->assertEquals($this->users['user3']['email'],$previous_version->email);

		$user = User::model()->findByPk(4);

		$this->assertEquals($this->users['admin']['username'],$user->username);
		$this->assertEquals($this->users['admin']['first_name'],$user->first_name);
		$this->assertEquals($this->users['admin']['last_name'],$user->last_name);
		$this->assertEquals($this->users['admin']['email'],$user->email);

		$this->assertNull($user->getPreviousVersion());

		// Cleanup

		Yii::app()->db->createCommand("update user set username = '{$this->users['user1']['username']}', first_name = '{$this->users['user1']['first_name']}', last_name = '{$this->users['user1']['last_name']}', email = '{$this->users['user1']['email']}' where id = 1")->query();
		Yii::app()->db->createCommand("update user set username = '{$this->users['user2']['username']}', first_name = '{$this->users['user2']['first_name']}', last_name = '{$this->users['user2']['last_name']}', email = '{$this->users['user2']['email']}' where id = 2")->query();
		Yii::app()->db->createCommand("update user set username = '{$this->users['user3']['username']}', first_name = '{$this->users['user3']['first_name']}', last_name = '{$this->users['user3']['last_name']}', email = '{$this->users['user3']['email']}' where id = 3")->query();

		Yii::app()->db->createCommand("delete from user_version where id in (2,3)")->query();
		Yii::app()->db->createCommand("delete from user_version where id = 1 and version_id > 2")->query();
	}

	public function testUpdateAllWithoutVersioning()
	{
		$transaction = Yii::app()->db->beginTransaction('Update','User');

		$this->model->noVersion()->updateAll(array(
				'username' => 'test1',
				'first_name' => 'test2',
				'last_name' => 'test3',
				'email' => 'test@test.aa',
			),
			'id >= 1 and id <= 3'
		);

		$transaction->commit();

		$user = User::model()->findByPk(1);

		$this->assertEquals('test1',$user->username);
		$this->assertEquals('test2',$user->first_name);
		$this->assertEquals('test3',$user->last_name);
		$this->assertEquals('test@test.aa',$user->email);
	
		$previous_version = $user->getPreviousVersion();
	
		$this->assertEquals($this->user_versions['user_version2']['username'],$previous_version->username);
		$this->assertEquals($this->user_versions['user_version2']['first_name'],$previous_version->first_name);
		$this->assertEquals($this->user_versions['user_version2']['last_name'],$previous_version->last_name);
		$this->assertEquals($this->user_versions['user_version2']['email'],$previous_version->email);

		$user = User::model()->findByPk(2);

		$this->assertEquals('test1',$user->username);
		$this->assertEquals('test2',$user->first_name);
		$this->assertEquals('test3',$user->last_name);
		$this->assertEquals('test@test.aa',$user->email);
	
		$this->assertNull($user->getPreviousVersion());
	
		$user = User::model()->findByPk(3);

		$this->assertEquals('test1',$user->username);
		$this->assertEquals('test2',$user->first_name);
		$this->assertEquals('test3',$user->last_name);
		$this->assertEquals('test@test.aa',$user->email);
	
		$this->assertNull($user->getPreviousVersion());
	
		$user = User::model()->findByPk(4);

		$this->assertEquals($this->users['admin']['username'],$user->username);
		$this->assertEquals($this->users['admin']['first_name'],$user->first_name);
		$this->assertEquals($this->users['admin']['last_name'],$user->last_name);
		$this->assertEquals($this->users['admin']['email'],$user->email);

		$this->assertNull($user->getPreviousVersion());

		// Cleanup

		Yii::app()->db->createCommand("update user set username = '{$this->users['user1']['username']}', first_name = '{$this->users['user1']['first_name']}', last_name = '{$this->users['user1']['last_name']}', email = '{$this->users['user1']['email']}' where id = 1")->query();
		Yii::app()->db->createCommand("update user set username = '{$this->users['user2']['username']}', first_name = '{$this->users['user2']['first_name']}', last_name = '{$this->users['user2']['last_name']}', email = '{$this->users['user2']['email']}' where id = 2")->query();
		Yii::app()->db->createCommand("update user set username = '{$this->users['user3']['username']}', first_name = '{$this->users['user3']['first_name']}', last_name = '{$this->users['user3']['last_name']}', email = '{$this->users['user3']['email']}' where id = 3")->query();
	}

	public function testSave_TransactionsOff_WithoutTransaction()
	{
		Yii::app()->params['enable_transactions'] = false;

		$user = User::model()->findByPk(1);
		$this->assertTrue($user->save());

		// Cleanup
		Yii::app()->params['enable_transactions'] = true;
	}

	public function testSave_TransactionsOn_WithoutTransaction()
	{
		$this->setExpectedException('Exception', 'save() called without a transaction');

		$user = User::model()->findByPk(1);
		$user->save();
	}

	public function testSave_TransactionsOn_WithTransaction()
	{
		$transaction = Yii::app()->db->beginTransaction('Update','User');

		$user = User::model()->findByPk(1);
		$user->save();

		$transaction->commit();

		$this->assertTrue(true);
	}

	public function testSaveDeniedOnVersionedModels()
	{
		$user = User::model()->findByPk(1);

		$previous_version = $user->getPreviousVersion();

		$this->setExpectedException('Exception', 'save() should not be called on versiond model instances.');

		$previous_version->save();
	}

	public function testDelete_TransactionsOff_WithoutTransaction()
	{
		Yii::app()->params['enable_transactions'] = false;

		$allergy = new Allergy;
		$allergy->name = 'testing';
		$allergy->save();

		$this->assertTrue($allergy->delete());

		// Cleanup
		Yii::app()->params['enable_transactions'] = true;
	}

	public function testDelete_TransactionsOn_WithoutTransaction()
	{
		$this->setExpectedException('Exception', 'delete() called without a transaction');

		$paa = PatientAllergyAssignment::model()->find();
		$paa->delete();
	}

	public function testDelete_TransactionsOn_WithTransaction()
	{
		$transaction = Yii::app()->db->beginTransaction('Delete','Allergy');

		$allergy = new Allergy;
		$allergy->name = 'testing';
		$allergy->save();

		$this->assertTrue($allergy->delete());

		$transaction->commit();
	}

	public function testDeleteDeniedOnVersionedModels()
	{
		$specialty = Specialty::model()->find();

		$previous_version = $specialty->getPreviousVersion();

		$this->setExpectedException('Exception', 'delete() should not be called on versiond model instances.');

		$previous_version->delete();
	}

	public function testResetScopeNoVersion()
	{
		$user = User::model()->findByPk(1);

		$user->noVersion();

		$this->assertFalse($user->getVersionCreateStatus());

		$user->resetScope();

		$this->assertTrue($user->getVersionCreateStatus());
	}

	public function testResetScopeFromVersionClone()
	{
		$user = User::model()->findByPk(1);

		$user = $user->fromVersion();

		$this->assertTrue($user->getVersionRetrievalStatus());

		$user->resetScope();

		$this->assertFalse($user->getVersionRetrievalStatus());
	}

	public function testGetFullTransactionList()
	{
		$user = User::model()->findByPk(1);

		$transaction_list = $user->getFullTransactionList();

		$this->assertCount(3, $transaction_list);

		$this->assertEquals('Current: by Joe Bloggs on 1 Jan 1900 at 00:00', $transaction_list[0]);
		$this->assertEquals('Edit by icabod icabod on 1 Jan 2013 at 12:00', $transaction_list[2]);
		$this->assertEquals('Edit by Jane Bloggs on 1 Jan 2012 at 12:00', $transaction_list[3]);

		$user = User::model()->findByPk(2);

		$transaction_list = $user->getFullTransactionList();

		$this->assertCount(1, $transaction_list);

		$this->assertEquals('Current: by Joe Bloggs on 1 Jan 1900 at 00:00', $transaction_list[0]);
	}

	public function testGetFullTransactionListForRelation_HasMany()
	{
		$patient = Patient::model()->findByPk(2);

		$transactions = $patient->getFullTransactionListForRelation('systemicDiagnoses');

		$this->assertCount(4, $transactions);

		$this->assertEquals('Current: Joe Bloggs on 1 Jan 1900 at 00:00', $transactions[0]);
		$this->assertEquals('Edit by Joe Bloggs on 1 Jan 1900 at 00:00', $transactions[99]);
		$this->assertEquals('Edit by Joe Bloggs on 1 Jan 1900 at 00:00', $transactions[98]);
		$this->assertEquals('Edit by Joe Bloggs on 1 Jan 1900 at 00:00', $transactions[97]);
	}

	public function testGetFullTransactionListForRelation_ManyMany()
	{
		$patient = Patient::model()->findByPk(1);

		$transactions = $patient->getFullTransactionListForRelation('allergies');

		$this->assertCount(4, $transactions);

		$this->assertEquals('Current: Joe Bloggs on 1 Jan 1901 at 00:00', $transactions[0]);
		$this->assertEquals('Edit by Joe Bloggs on 29 Jan 2012 at 13:37', $transactions[201]);
		$this->assertEquals('Edit by Joe Bloggs on 20 Jan 2012 at 13:37', $transactions[200]);
		$this->assertEquals('Edit by Joe Bloggs on 17 Jan 2012 at 13:37', $transactions[1]);
	}

	public function testGetTransactionText()
	{
		foreach (User::model()->findAll() as $user) {
			for ($i=0;$i<5;$i++) {
				$ts = rand();

				$this->assertEquals($user->fullName.' on '.date('j M Y',$ts).' at '.date('H:i',$ts),$user->getTransactionText($user->id,date('Y-m-d H:i:s',$ts)));
			}
		}
	}

	public function testGetRelated_BelongsTo_NotFromVersion()
	{
		$user = User::model()->findByPk(1);

		$contact = $user->contact;

		$this->assertEquals(1, $contact->id);
		$this->assertEquals('Jim', $contact->first_name);
		$this->assertEquals('Aylward', $contact->last_name);
		$this->assertEquals('07123 456789', $contact->primary_phone);
	}

	public function testGetRelated_BelongsTo_FromVersion()
	{
		$user = User::model()->findByPk(1);

		$previous_version = $user->getPreviousVersion();

		$contact = $previous_version->contact;

		$this->assertEquals(2, $contact->id);
		$this->assertEquals('Bob', $contact->first_name);
		$this->assertEquals('Collin', $contact->last_name);
		$this->assertEquals('07234 567890', $contact->primary_phone);
	}

	public function testGetRelated_HasOne_NotFromVersion()
	{
		$subspecialty = Subspecialty::model()->findByPk(1);

		$ssa = $subspecialty->serviceSubspecialtyAssignment;

		$this->assertEquals(1, $ssa->id);
		$this->assertEquals(1, $ssa->service_id);
		$this->assertEquals(1, $ssa->subspecialty_id);
	}

	public function testGetRelated_HasOne_FromVersion()
	{
		$subspecialty = Subspecialty::model()->findByPk(1);

		$ssa = $subspecialty->serviceSubspecialtyAssignment->getPreviousVersion();

		$this->assertEquals(1, $ssa->id);
		$this->assertEquals(2, $ssa->service_id);
		$this->assertEquals(1, $ssa->subspecialty_id);
	}

	public function testGetRelated_HasMany_NotFromVersion()
	{
		$patient = Patient::model()->findByPk(1);

		$episodes = $patient->episodes;

		$this->assertCount(2, $episodes);

		$this->assertEquals(1, $episodes[0]->id);
		$this->assertEquals(2, $episodes[1]->id);
	}

	public function testGetRelated_HasMany_FromVersion()
	{
		$patient = Patient::model()->findByPk(1)->getPreviousVersion();

		$episodes = $patient->episodes;

		$this->assertCount(1, $episodes);
		$this->assertEquals(1, $episodes[0]->id);
	}

	public function testGetRelated_ManyMany_NotFromVersion()
	{
		$patient = Patient::model()->findByPk(1);

		$allergies = $patient->allergies;

		$this->assertCount(3, $allergies);

		$this->assertEquals(1, $allergies[0]->id);
		$this->assertEquals(2, $allergies[1]->id);
		$this->assertEquals(3, $allergies[2]->id);
	}

	public function testGetRelated_ManyMany_FromVersion()
	{
		$patient = Patient::model()->findByPk(1)->getPreviousVersion();

		$allergies = $patient->allergies;

		$this->assertCount(3, $allergies);

		$this->assertEquals(1, $allergies[0]->id);
		$this->assertEquals(3, $allergies[1]->id);
		$this->assertEquals(5, $allergies[2]->id);
	}

	public function testGetRelated_CBelongsToRelation_NotVersionedModel()
	{
		$criteria = new CDbCriteria;
		$criteria->addCondition('name = :name');
		$criteria->params[':name'] = 'Female';

		$object = User::model()->getRelated_CBelongsToRelation(array(
				'blah',
				'Gender',
			),
			$criteria,
			123456
		);

		$this->assertInstanceOf('Gender', $object);
		$this->assertEquals(2, $object->id);
		$this->assertEquals('Female', $object->name);
	}

	public function testGetRelated_CBelongsToRelation_VersionedModel()
	{
		$criteria = new CDbCriteria;
		$criteria->addCondition('id = :id');
		$criteria->params[':id'] = 1;

		$object = User::model()->getRelated_CBelongsToRelation(array(
				'blah',
				'User',
			),
			$criteria,
			null
		);

		$this->assertInstanceOf('User', $object);
		$this->assertEquals(1, $object->id);
		$this->assertEquals('Joe', $object->first_name);
		$this->assertEquals('Bloggs', $object->last_name);
	}

	public function testGetRelated_CBelongsToRelation_VersionedModel_OldVersion()
	{
		$criteria = new CDbCriteria;
		$criteria->addCondition('id = :id');
		$criteria->params[':id'] = 1;

		$object = User::model()->getRelated_CBelongsToRelation(array(
				'blah',
				'User',
			),
			$criteria,
			3
		);

		$this->assertInstanceOf('User', $object);
		$this->assertEquals(1, $object->id);
		$this->assertEquals('Joe3', $object->first_name);
		$this->assertEquals('Bloggs3', $object->last_name);
	}

	public function testGetRelated_CHasOneRelation_NotVersionedModel()
	{
		$criteria = new CDbCriteria;
		$criteria->addCondition('name = :name');
		$criteria->params[':name'] = 'Female';

		$object = User::model()->getRelated_CHasOneRelation(array(
				'blah',
				'Gender',
			),
			$criteria,
			123456
		);

		$this->assertInstanceOf('Gender', $object);
		$this->assertEquals(2, $object->id);
		$this->assertEquals('Female', $object->name);
	}

	public function testGetRelated_CHasOneRelation_VersionedModel()
	{
		$criteria = new CDbCriteria;
		$criteria->addCondition('id = :id');
		$criteria->params[':id'] = 1;

		$object = User::model()->getRelated_CHasOneRelation(array(
				'blah',
				'User',
			),
			$criteria,
			null
		);

		$this->assertInstanceOf('User', $object);
		$this->assertEquals(1, $object->id);
		$this->assertEquals('Joe', $object->first_name);
		$this->assertEquals('Bloggs', $object->last_name);
	}

	public function testGetRelated_CHasOneRelation_VersionedModel_OldVersion()
	{
		$criteria = new CDbCriteria;
		$criteria->addCondition('id = :id');
		$criteria->params[':id'] = 1;

		$object = User::model()->getRelated_CHasOneRelation(array(
				'blah',
				'User',
			),
			$criteria,
			3
		);

		$this->assertInstanceOf('User', $object);
		$this->assertEquals(1, $object->id);
		$this->assertEquals('Joe3', $object->first_name);
		$this->assertEquals('Bloggs3', $object->last_name);
	}

	public function testGetRelated_CHasManyRelation()
	{
		$criteria = new CDbCriteria;
		$criteria->alias = 't';

		$objects = User::model()->getRelated_CHasManyRelation(array(
				'blah',
				'User',
			),
			$criteria,
			3
		);

		$this->assertCount(1, $objects);
		$this->assertEquals(1, $objects[0]->id);
		$this->assertEquals('Joe3', $objects[0]->first_name);
		$this->assertEquals('Bloggs3', $objects[0]->last_name);
	}

	public function testGetRelated_CHasManyRelation_NoTransaction()
	{
		$criteria = new CDbCriteria;
		$criteria->alias = 't';

		$objects = User::model()->getRelated_CHasManyRelation(array(
				'blah',
				'User',
			),
			$criteria,
			null
		);

		$this->assertCount(4, $objects);
		$this->assertEquals(1, $objects[0]->id);
		$this->assertEquals('Joe', $objects[0]->first_name);
		$this->assertEquals('Bloggs', $objects[0]->last_name);
		$this->assertEquals(2, $objects[1]->id);
		$this->assertEquals('Jane', $objects[1]->first_name);
		$this->assertEquals('Bloggs', $objects[1]->last_name);
		$this->assertEquals(3, $objects[2]->id);
		$this->assertEquals('icabod', $objects[2]->first_name);
		$this->assertEquals('icabod', $objects[2]->last_name);
		$this->assertEquals(4, $objects[3]->id);
		$this->assertEquals('Admin', $objects[3]->first_name);
		$this->assertEquals('User', $objects[3]->last_name);
	}

	public function testGetRelated_CManyManyRelation_VersionedModel_WithTransaction()
	{
		$criteria = new CDbCriteria;
		$criteria->alias = 't';

		$objects = Patient::model()->findByPk(1)->getRelated_CManyManyRelation(array(
				'allergies',
				'Allergy',
				'patient_allergy_assignment(patient_id, allergy_id)',
			),
			$criteria,
			200
		);

		$this->assertCount(3, $objects);

		$this->assertInstanceOf('Allergy', $objects[0]);
		$this->assertEquals(1, $objects[0]->id);
		$this->assertEquals('allergy 1', $objects[0]->name);

		$this->assertInstanceOf('Allergy', $objects[1]);
		$this->assertEquals(3, $objects[1]->id);
		$this->assertEquals('allergy 3', $objects[1]->name);

		$this->assertInstanceOf('Allergy', $objects[2]);
		$this->assertEquals(5, $objects[2]->id);
		$this->assertEquals('allergy 5', $objects[2]->name);
	}

	public function testGetRelated_CManyManyRelation_VersionedModel_NoTransaction()
	{
		$criteria = new CDbCriteria;
		$criteria->alias = 't';

		$objects = User::model()->findByPk(1)->getRelated_CManyManyRelation(array(
				'firms',
				'Firm',
				'firm_user_assignment(user_id, firm_id)',
			),
			$criteria,
			null
		);

		$this->assertCount(3, $objects);
		$this->assertInstanceOf('Firm', $objects[0]);
		$this->assertEquals(1, $objects[0]->id);
		$this->assertEquals('Aylward Firm', $objects[0]->name);

		$this->assertInstanceOf('Firm', $objects[1]);
		$this->assertEquals(2, $objects[1]->id);
		$this->assertEquals('Collin Firm', $objects[1]->name);

		$this->assertInstanceOf('Firm', $objects[2]);
		$this->assertEquals(3, $objects[2]->id);
		$this->assertEquals('Allan Firm', $objects[2]->name);
	}

	public function testRelationByTransactionID()
	{
		$patient = Patient::model()->findByPk(1);

		$allergies = $patient->relationByTransactionID('allergies',200);

		$this->assertCount(3, $allergies);

		$this->assertEquals(1, $allergies[0]->id);
		$this->assertEquals(3, $allergies[1]->id);
		$this->assertEquals(5, $allergies[2]->id);
	}

	public function testRelationByTransactionIDOrActive_WithTransactionID()
	{
		$patient = Patient::model()->findByPk(1);

		$allergies = $patient->relationByTransactionIDOrActive('allergies',200);

		$this->assertCount(3, $allergies);

		$this->assertEquals(1, $allergies[0]->id);
		$this->assertEquals(3, $allergies[1]->id);
		$this->assertEquals(5, $allergies[2]->id);
	}

	public function testRelationByTransactionIDOrActive_WithoutTransactionID()
	{
		$patient = Patient::model()->findByPk(1);

		$allergies = $patient->relationByTransactionIDOrActive('allergies',null);

		$this->assertCount(3, $allergies);

		$this->assertEquals(1, $allergies[0]->id);
		$this->assertEquals(2, $allergies[1]->id);
		$this->assertEquals(3, $allergies[2]->id);
	}

	public function testGetRelationCriteria_HasMany()
	{
		$patient = Patient::model()->findByPk(1);

		$criteria = $patient->getRelationCriteria('legacyepisodes', array(
				'CHasManyRelation',
				'Episode',
				'patient_id',
				'condition' => 'legacy=1',
		));

		$this->assertInstanceOf('CDbCriteria', $criteria);

		$this->assertEquals('*', $criteria->select);
		$this->assertEquals('(legacy=1) AND (patient_id = :pk)', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':pk']);
		$this->assertEquals('legacyepisodes', $criteria->alias);
	}

	public function testGetRelationCriteria_HasMany_CustomAlias()
	{
		$patient = Patient::model()->findByPk(1);

		$criteria = $patient->getRelationCriteria('episodes', array(
				'CHasManyRelation',
				'Episode',
				'patient_id',
				'condition' => '(patient_episode.legacy=0 or patient_episode.legacy is null)',
				'alias' => 'patient_episode',
		));

		$this->assertInstanceOf('CDbCriteria', $criteria);

		$this->assertEquals('*', $criteria->select);
		$this->assertEquals('((patient_episode.legacy=0 or patient_episode.legacy is null)) AND (patient_id = :pk)', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':pk']);
		$this->assertEquals('patient_episode', $criteria->alias);
	}

	public function testGetRelationCriteria_HasMany_Order()
	{
		$patient = Patient::model()->findByPk(1);

		$criteria = $patient->getRelationCriteria('previousOperations', array(
				'CHasManyRelation',
				'PreviousOperation',
				'patient_id',
				'order' => 'date',
		));

		$this->assertInstanceOf('CDbCriteria', $criteria);

		$this->assertEquals('*', $criteria->select);
		$this->assertEquals('patient_id = :pk', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':pk']);
		$this->assertEquals('previousOperations', $criteria->alias);
		$this->assertEquals('date', $criteria->order);
	}

	public function testGetRelationCriteria_HasMany_With()
	{
		$patient = Patient::model()->findByPk(1);

		$criteria = $patient->getRelationCriteria('ophthalmicDiagnoses', array(
				'CHasManyRelation',
				'SecondaryDiagnosis',
				'patient_id',
				'with' => array(
					'disorder' => array(
						'with' => 'specialty',
					),
				),
				'condition' => 'specialty.code = 130',
				'order' => 'date asc',
		));
		
		$this->assertInstanceOf('CDbCriteria', $criteria);
		
		$this->assertEquals('*', $criteria->select);
		$this->assertEquals('(specialty.code = 130) AND (patient_id = :pk)', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':pk']);
		$this->assertEquals('ophthalmicDiagnoses', $criteria->alias);
		$this->assertEquals('date asc', $criteria->order);
		$this->assertEquals(array('disorder' => array('with' => 'specialty')), $criteria->with);
	}

	public function testGetRelationCriteria_HasMany_Params()
	{
		$patient = Patient::model()->findByPk(1);

		$criteria = $patient->getRelationCriteria('ophthalmicDiagnoses', array(
				'CHasManyRelation',
				'SecondaryDiagnosis',
				'patient_id',
				'params' => array(':foo' => 'bar'),
		));

		$this->assertInstanceOf('CDbCriteria', $criteria);

		$this->assertEquals('*', $criteria->select);
		$this->assertEquals(1, $criteria->params[':pk']);
		$this->assertEquals('ophthalmicDiagnoses', $criteria->alias);
		$this->assertEquals('bar', $criteria->params[':foo']);
	}

	public function testGetRelationCriteria_HasMany_On()
	{
		$patient = Patient::model()->findByPk(1);
		
		$criteria = $patient->getRelationCriteria('ophthalmicDiagnoses', array(
				'CHasManyRelation',
				'SecondaryDiagnosis',
				'patient_id',
				'on' => 'rockpaper = :scissors',
		));

		$this->assertInstanceOf('CDbCriteria', $criteria);

		$this->assertEquals('*', $criteria->select);
		$this->assertEquals(1, $criteria->params[':pk']);
		$this->assertEquals('ophthalmicDiagnoses', $criteria->alias);
		$this->assertEquals('(rockpaper = :scissors) AND (patient_id = :pk)', $criteria->condition);
	}

	public function testGetRelationCriteria_HasMany_Limit()
	{
		$patient = Patient::model()->findByPk(1);
		
		$criteria = $patient->getRelationCriteria('ophthalmicDiagnoses', array(
				'CHasManyRelation',
				'SecondaryDiagnosis',
				'patient_id',
				'limit' => '123',
		));

		$this->assertInstanceOf('CDbCriteria', $criteria);

		$this->assertEquals('*', $criteria->select);
		$this->assertEquals(1, $criteria->params[':pk']);
		$this->assertEquals('ophthalmicDiagnoses', $criteria->alias);
		$this->assertEquals('123', $criteria->limit);
	}

	public function testGetRelationCriteria_HasMany_Offset()
	{
		$patient = Patient::model()->findByPk(1);
		
		$criteria = $patient->getRelationCriteria('ophthalmicDiagnoses', array(
				'CHasManyRelation',
				'SecondaryDiagnosis',
				'patient_id',
				'offset' => '987',
		));

		$this->assertInstanceOf('CDbCriteria', $criteria);

		$this->assertEquals('*', $criteria->select);
		$this->assertEquals(1, $criteria->params[':pk']);
		$this->assertEquals('ophthalmicDiagnoses', $criteria->alias);
		$this->assertEquals('987', $criteria->offset);
	}

	public function testGetRelationCriteria_BelongsTo()
	{
		$patient = Patient::model()->findByPk(1);

		$criteria = $patient->getRelationCriteria('contact', array(
				'CBelongsToRelation',
				'Contact',
				'contact_id',
		));
	 
		$this->assertInstanceOf('CDbCriteria', $criteria);
	 
		$this->assertEquals('*', $criteria->select);
		$this->assertEquals('id = :pk', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':pk']);
		$this->assertEquals('contact', $criteria->alias);
	}

	public function testGetRelationCriteria_BelongsTo_CustomAlias()
	{
		$patient = Patient::model()->findByPk(1);

		$criteria = $patient->getRelationCriteria('contact', array(
				'CBelongsToRelation',
				'Contact',
				'contact_id',
				'alias' => 'foo',
		));
	
		$this->assertInstanceOf('CDbCriteria', $criteria);
	
		$this->assertEquals('*', $criteria->select);
		$this->assertEquals('id = :pk', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':pk']);
		$this->assertEquals('foo', $criteria->alias);
	}

	public function testGetRelationCriteria_HasOne()
	{
		$patient = Patient::model()->findByPk(1);

		$criteria = $patient->getRelationCriteria('lastReferral', array(
				'CHasOneRelation',
				'Referral',
				'patient_id',
		));
	
		$this->assertInstanceOf('CDbCriteria', $criteria);
	
		$this->assertEquals('*', $criteria->select);
		$this->assertEquals('patient_id = :pk', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':pk']);
		$this->assertEquals('lastReferral', $criteria->alias);
	}

	public function testGetRelationCriteria_HasOne_Order()
	{
		$patient = Patient::model()->findByPk(1);

		$criteria = $patient->getRelationCriteria('lastReferral', array(
				'CHasOneRelation',
				'Referral',
				'patient_id',
				'order' => 'received_date desc',
		));
 
		$this->assertInstanceOf('CDbCriteria', $criteria);
 
		$this->assertEquals('*', $criteria->select);
		$this->assertEquals('patient_id = :pk', $criteria->condition);
		$this->assertEquals(1, $criteria->params[':pk']);
		$this->assertEquals('lastReferral', $criteria->alias);
		$this->assertEquals('received_date desc', $criteria->order);
	}

	public function testGetRelationCriteria_ManyMany()
	{
		$patient = Patient::model()->findByPk(1);

		$criteria = $patient->getRelationCriteria('allergies', array(
				'CManyManyRelation',
				'Allergy',
				'patient_allergy_assignment(patient_id, allergy_id)',
				'alias' => 'patient_allergies',
				'order' => 'patient_allergies.name',
		));
 
		$this->assertInstanceOf('CDbCriteria', $criteria);
 
		$this->assertEquals('*', $criteria->select);
		$this->assertEquals('', $criteria->condition);
		$this->assertEquals('join `patient_allergy_assignment` on `patient_allergy_assignment`.`allergy_id` = `patient_allergies`.`id` and `patient_allergy_assignment`.`patient_id` = :pk', $criteria->join);
		$this->assertEquals(1, $criteria->params[':pk']);
		$this->assertEquals('patient_allergies', $criteria->alias);
		$this->assertEquals('patient_allergies.name', $criteria->order);
	}

	public function testSetRelationCriteria_CBelongsToRelation()
	{
		$criteria = User::model()->findByPk(1)
			->setRelationCriteria_CBelongsToRelation(new CDbCriteria, array(
			'foo',
			'User',
			'id',
		));

		$this->assertEquals('id = :pk',$criteria->condition);
		$this->assertEquals(1, $criteria->params[':pk']);
	}

	public function testSetRelationCriteria_CHasOneRelation()
	{
		$criteria = User::model()->findByPk(1)
			->setRelationCriteria_CHasOneRelation(new CDbCriteria, array(
			'foo',
			'bar',
			'microwave_id',
		));

		$this->assertEquals('microwave_id = :pk',$criteria->condition);
		$this->assertEquals(1, $criteria->params[':pk']);
	}

	public function testSetRelationCriteria_CHasManyRelation()
	{
		$criteria = User::model()->findByPk(1)
			->setRelationCriteria_CHasManyRelation(new CDbCriteria, array(
			'foo',
			'bar',
			'microwave_id',
		));

		$this->assertEquals('microwave_id = :pk',$criteria->condition);
		$this->assertEquals(1, $criteria->params[':pk']);
	}

	public function testSetRelationCriteria_CManyManyRelation()
	{
		$criteria = new CDbCriteria;
		$criteria->alias = 'banana';
	
		$criteria = Patient::model()->findByPk(1)
			->setRelationCriteria_CManyManyRelation($criteria, array(
			'foo',
			'Allergy',
			'patient_allergy_assignment(patient_id, allergy_id)',
		));

		$this->assertEquals('',$criteria->condition);
		$this->assertEquals('join `patient_allergy_assignment` on `patient_allergy_assignment`.`allergy_id` = `banana`.`id` and `patient_allergy_assignment`.`patient_id` = :pk',$criteria->join);
		$this->assertEquals(1, $criteria->params[':pk']);
	}

	public function testSetRelationCriteria_CManyManyRelation_FromVersion()
	{
		$criteria = new CDbCriteria;
		$criteria->alias = 'fridge';

		$patient = Patient::model()->findByPk(1);
		$patient->transaction_id = 200;
		$patient->version_id = 201;

		$criteria = $patient->setRelationCriteria_CManyManyRelation($criteria, array(
			'foo',
			'Allergy',
			'patient_allergy_assignment(patient_id, allergy_id)',
		));

		$this->assertEquals('',$criteria->condition);
		$this->assertEquals('join `patient_allergy_assignment_version` on `patient_allergy_assignment_version`.`allergy_id` = `fridge`.`id` and `patient_allergy_assignment_version`.`patient_id` = :pk and `patient_allergy_assignment_version`.`transaction_id` = :transaction_id',$criteria->join);
		$this->assertEquals(200, $criteria->params[':transaction_id']);
		$this->assertEquals(1, $criteria->params[':pk']);
	}

	public function testSaveCreatesNewVersion()
	{
		$address = Address::model()->findByPk(1);

		$this->assertCount(0, Address::model()->fromVersion()->findAll('id=1'));

		$transaction = Yii::app()->db->beginTransaction('Update','Address');

		$address->save();

		$transaction->commit();

		$this->assertCount(1, Address::model()->fromVersion()->findAll('id=1'));

		$previous_version = Address::model()->fromVersion()->find('id=1');

		$this->assertEquals('flat 1', $previous_version->address1);
		$this->assertEquals('bleakley creek', $previous_version->address2);
		$this->assertEquals('flitchley', $previous_version->city);
		$this->assertEquals('ec1v 0dx', $previous_version->postcode);
		$this->assertEquals('london', $previous_version->county);

		// Cleanup
		Yii::app()->db->createCommand("delete from address_version where version_id = {$previous_version->version_id}")->query();
	}

	public function testSaveMoreThanOnceInTheSameTransactionOnlyGeneratesOneNewVersionRow()
	{
		$address = Address::model()->findByPk(1);

		$this->assertCount(0, Address::model()->fromVersion()->findAll('id=1'));

		$transaction = Yii::app()->db->beginTransaction('Update','Address');

		$address->save();
		$address->save();
		$address->save();

		$transaction->commit();

		$this->assertCount(1, Address::model()->fromVersion()->findAll('id=1'));

		$previous_version = Address::model()->fromVersion()->find('id=1');

		$this->assertEquals('flat 1', $previous_version->address1);
		$this->assertEquals('bleakley creek', $previous_version->address2);
		$this->assertEquals('flitchley', $previous_version->city);
		$this->assertEquals('ec1v 0dx', $previous_version->postcode);
		$this->assertEquals('london', $previous_version->county);

		// Cleanup
		Yii::app()->db->createCommand("delete from address_version where version_id = {$previous_version->version_id}")->query();
	}

	public function testGenerateHash()
	{
		$user = User::model()->findByPk(1);

		$this->assertEquals('42b298f8412f05f36086274dfab6f64699d989e3',$user->generateHash());
	}

	public function testDeleteCreatesNewVersion()
	{
		$address = Address::model()->findByPk(1);

		$this->assertCount(0, Address::model()->fromVersion()->findAll('id=1'));

		$transaction = Yii::app()->db->beginTransaction('Delete','Address');

		$address->delete();

		$transaction->commit();

		$this->assertCount(1, Address::model()->fromVersion()->findAll('id=1'));

		$previous_version = Address::model()->fromVersion()->find('id=1');

		$this->assertEquals('flat 1', $previous_version->address1);
		$this->assertEquals('bleakley creek', $previous_version->address2);
		$this->assertEquals('flitchley', $previous_version->city);
		$this->assertEquals('ec1v 0dx', $previous_version->postcode);
		$this->assertEquals('london', $previous_version->county);

		// Cleanup
		Yii::app()->db->createCommand("delete from address_version where version_id = {$previous_version->version_id}")->query();
		Yii::app()->db->createCommand()->insert('address',array(
			'id' => 1,
			'address1' => 'flat 1',
			'address2' => 'bleakley creek',
			'city' => 'flitchley',
			'postcode' => 'ec1v 0dx',
			'county' => 'london',
			'country_id' => 1,
			'email' => 'bleakley1@bleakley1.com',
			'contact_id' => 1,
		));
	}

	public function testRealDeleteCreatesDeleteTransaction()
	{
		$address = Address::model()->findByPk(1);

		$this->assertCount(0, Address::model()->fromVersion()->findAll('id=1'));

		$transaction = Yii::app()->db->beginTransaction('Delete','Address');

		$address->delete();

		$transaction->commit();

		$this->assertCount(1, Address::model()->fromVersion()->findAll('id=1'));

		$previous_version = Address::model()->fromVersion()->find('id=1');

		$this->assertNotNull($previous_version->deleted_transaction_id);

		// Cleanup
		Yii::app()->db->createCommand("delete from address_version where version_id = {$previous_version->version_id}")->query();
		Yii::app()->db->createCommand()->insert('address',array(
			'id' => 1,
			'address1' => 'flat 1',
			'address2' => 'bleakley creek',
			'city' => 'flitchley',
			'postcode' => 'ec1v 0dx',
			'county' => 'london',
			'country_id' => 1,
			'email' => 'bleakley1@bleakley1.com',
			'contact_id' => 1,
		));
	}

	public function testMultipleChangesToManyToManyListAreRetrievedFromHistoryCorrectly()
	{
		$patient = Patient::model()->noPas()->findByPk(2);

		$this->assertCount(7, $patient->systemicDiagnoses);
		$this->assertCount(2, $patient->getRelated('systemicDiagnoses',false,array(),99));
		$this->assertCount(4, $patient->getRelated('systemicDiagnoses',false,array(),98));
		$this->assertCount(6, $patient->getRelated('systemicDiagnoses',false,array(),97));
	}

	public function testDeleteByPk_TransactionsOff_WithoutTransaction()
	{
		Yii::app()->params['enable_transactions'] = false;

		$paa = new PatientAllergyAssignment;
		$paa->patient_id = 1;
		$paa->allergy_id = 1;
		$paa->save();

		$this->assertEquals(1, PatientAllergyAssignment::model()->deleteByPk($paa->id));

		// Cleanup
		Yii::app()->params['enable_transactions'] = true;
	}

	public function testDeleteByPk_TransactionsOn_WithoutTransaction()
	{
		$this->setExpectedException('Exception', 'deleteByPk() called without a transaction');

		PatientAllergyAssignment::model()->deleteByPk(1);
	}

	public function testDeleteByPk_TransactionsOn_WithTransaction()
	{
		$transaction = Yii::app()->db->beginTransaction('Delete','Allergy');

		$paa = new PatientAllergyAssignment;
		$paa->patient_id = 1;
		$paa->allergy_id = 1;
		$paa->save();

		$this->assertEquals(1, PatientAllergyAssignment::model()->deleteByPk($paa->id));

		$transaction->commit();
	}

	public function testDeleteAll_TransactionsOff_WithoutTransaction()
	{
		Yii::app()->params['enable_transactions'] = false;

		$paa = new PatientAllergyAssignment;
		$paa->patient_id = 1;
		$paa->allergy_id = 1;
		$paa->save();

		$this->assertEquals(1, PatientAllergyAssignment::model()->deleteAll('id=:id',array(':id' => $paa->id)));

		// Cleanup
		Yii::app()->params['enable_transactions'] = true;
	}

	public function testDeleteAll_TransactionsOn_WithoutTransaction()
	{
		$this->setExpectedException('Exception', 'deleteAll() called without a transaction');

		PatientAllergyAssignment::model()->deleteAll();
	}

	public function testDeleteAll_TransactionsOn_WithTransaction()
	{
		$transaction = Yii::app()->db->beginTransaction('Delete','Allergy');

		$paa = new PatientAllergyAssignment;
		$paa->patient_id = 1;
		$paa->allergy_id = 1;
		$paa->save();

		$this->assertEquals(1, PatientAllergyAssignment::model()->deleteAll('id=:id',array(':id'=>$paa->id)));

		$transaction->commit();
	}

	public function testDeleteAllByAttributes_TransactionsOff_WithoutTransaction()
	{
		Yii::app()->params['enable_transactions'] = false;

		$paa = new PatientAllergyAssignment;
		$paa->patient_id = 1;
		$paa->allergy_id = 1;
		$paa->save();

		$this->assertEquals(1, PatientAllergyAssignment::model()->deleteAllByAttributes(array('id' => $paa->id)));

		// Cleanup
		Yii::app()->params['enable_transactions'] = true;
	}

	public function testDeleteAllByAttributes_TransactionsOn_WithoutTransaction()
	{
		$this->setExpectedException('Exception', 'deleteAllByAttributes() called without a transaction');

		PatientAllergyAssignment::model()->deleteAllByAttributes(array('id'=>1));
	}

	public function testDeleteAllByAttributes_TransactionsOn_WithTransaction()
	{
		$transaction = Yii::app()->db->beginTransaction('Delete','Allergy');

		$paa = new PatientAllergyAssignment;
		$paa->patient_id = 1;
		$paa->allergy_id = 1;
		$paa->save();

		$this->assertEquals(1, PatientAllergyAssignment::model()->deleteAllByAttributes(array('id'=>$paa->id)));

		$transaction->commit();
	}
}
