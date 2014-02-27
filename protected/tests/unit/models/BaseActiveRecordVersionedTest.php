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
	);

	protected function setUp()
	{
		parent::setUp();

		$this->model = new User;
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

	public function testUpdateByPkWithVersioning()
	{
		$this->model->updateByPk(1, array(
			'username' => 'test1',
			'first_name' => 'test2',
			'last_name' => 'test3',
			'email' => 'test@test.aa',
		));

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
		$this->model->noVersion()->updateByPk(1, array(	
			'username' => 'test1',
			'first_name' => 'test2',
			'last_name' => 'test3',
			'email' => 'test@test.aa',
		));
	 
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

	public function testUpdateAllWithVersioning()
	{
		$this->model->updateAll(array(
				'username' => 'test1',
				'first_name' => 'test2',
				'last_name' => 'test3',
				'email' => 'test@test.aa',
			),
			'id >= 1 and id <= 3'
		);

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
		$this->model->noVersion()->updateAll(array(
				'username' => 'test1',
				'first_name' => 'test2',
				'last_name' => 'test3',
				'email' => 'test@test.aa',
			),
			'id >= 1 and id <= 3'
		);

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

	public function testSaveDeniedOnVersionedModels()
	{
		$user = User::model()->findByPk(1);

		$previous_version = $user->getPreviousVersion();

		$this->setExpectedException('Exception', 'save() should not be called on versiond model instances.');

		$previous_version->save();
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

	public function testGetRelated_BelongsTo_NotFromVersion()
	{
		$user = User::model()->findByPk(1);

		$contact = $user->contact;

		$this->assertEquals(1, $contact->id);
		$this->assertEquals('Enoch', $contact->first_name);
	}

	public function testGetRelated_BelongsTo_FromVersion()
	{
		$user = User::model()->findByPk(1);

		$previous_version = $user->getPreviousVersion();

		$contact = $previous_version->contact;

		$this->assertEquals(2, $contact->id);
		$this->assertEquals('User', $contact->first_name);
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
		$this->assertEquals(2, $allergies[1]->id);
		$this->assertEquals(3, $allergies[2]->id);
	}
}
