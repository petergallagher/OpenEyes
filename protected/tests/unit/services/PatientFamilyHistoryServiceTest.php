<?php
/**
 * (C) OpenEyes Foundation, 2014
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (C) 2014, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

namespace services;

class PatientFamilyHistoryServiceTest extends \CDbTestCase
{
	public $fixtures = array(
		'patients' => 'Patient',
		'family_history' => 'FamilyHistory',
	);

	public function testModelToResource()
	{
		$patient = $this->patients('patient1');

		$ps = new PatientFamilyHistoryService;

		$resource = $ps->modelToResource($patient);

		$this->assertInstanceOf('services\PatientFamilyHistory',$resource);
		$this->assertCount(3,$resource->history);

		$this->assertInstanceOf('services\PatientFamilyHistoryItem',$resource->history[0]);
		$this->assertEquals('Uncle',$resource->history[0]->relative);
		$this->assertEquals('Paternal',$resource->history[0]->side);
		$this->assertEquals('Maculopathy',$resource->history[0]->condition);
		$this->assertEquals('Was quite ill',$resource->history[0]->comments);

		$this->assertInstanceOf('services\PatientFamilyHistoryItem',$resource->history[1]);
		$this->assertEquals('Brother',$resource->history[1]->relative);
		$this->assertEquals('N/A',$resource->history[1]->side);
		$this->assertEquals('Diabetes',$resource->history[1]->condition);
		$this->assertEquals('Very ill',$resource->history[1]->comments);

		$this->assertInstanceOf('services\PatientFamilyHistoryItem',$resource->history[2]);
		$this->assertEquals('Aunt',$resource->history[2]->relative);
		$this->assertEquals('Maternal',$resource->history[2]->side);
		$this->assertEquals('Cataract',$resource->history[2]->condition);
		$this->assertEquals('',$resource->history[2]->comments);
	}

	public function getResource()
	{
		$resource = new PatientFamilyHistory;

		$history1 = new PatientFamilyHistoryItem;
		$history1->relative = 'Cousin';
		$history1->side = 'Maternal';
		$history1->condition = 'Other';
		$history1->comments = 'i am writing tests';

		$history2 = new PatientFamilyHistoryItem;
		$history2->relative = 'Grandmother';
		$history2->side = 'Unknown';
		$history2->condition = 'Cataract';
		$history2->comments = 'i am writing more tests';

		$resource->history = array($history1,$history2);

		return $resource;
	}

	public function testResourceToModel_NoSave_NoNewRecords()
	{
		$resource = $this->getResource();

		$total_fh = count(\FamilyHistory::model()->findAll());

		$ps = new PatientFamilyHistoryService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient2'), false);

		$this->assertEquals($total_fh, count(\FamilyHistory::model()->findAll()));
	}

	public function testResourceToModel_NoSave_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientFamilyHistoryService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient2'), false);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->familyHistory);

		$this->assertInstanceOf('FamilyHistory',$patient->familyHistory[0]);
		$this->assertInstanceOf('FamilyHistoryRelative',$patient->familyHistory[0]->relative);
		$this->assertEquals('Cousin',$patient->familyHistory[0]->relative->name);
		$this->assertEquals(\FamilyHistoryRelative::model()->find('name=?',array('Cousin'))->id,$patient->familyHistory[0]->relative_id);
		$this->assertInstanceOf('FamilyHistorySide',$patient->familyHistory[0]->side);
		$this->assertEquals('Maternal',$patient->familyHistory[0]->side->name);
		$this->assertEquals(\FamilyHistorySide::model()->find('name=?',array('Maternal'))->id,$patient->familyHistory[0]->side_id);
		$this->assertInstanceOf('FamilyHistoryCondition',$patient->familyHistory[0]->condition);
		$this->assertEquals('Other',$patient->familyHistory[0]->condition->name);
		$this->assertEquals(\FamilyHistoryCondition::model()->find('name=?',array('Other'))->id,$patient->familyHistory[0]->condition_id);
		$this->assertEquals('i am writing tests',$patient->familyHistory[0]->comments);

		$this->assertInstanceOf('FamilyHistory',$patient->familyHistory[1]);
		$this->assertInstanceOf('FamilyHistoryRelative',$patient->familyHistory[1]->relative);
		$this->assertEquals('Grandmother',$patient->familyHistory[1]->relative->name);
		$this->assertEquals(\FamilyHistoryRelative::model()->find('name=?',array('Grandmother'))->id,$patient->familyHistory[1]->relative_id);
		$this->assertInstanceOf('FamilyHistorySide',$patient->familyHistory[1]->side);
		$this->assertEquals('Unknown',$patient->familyHistory[1]->side->name);
		$this->assertEquals(\FamilyHistorySide::model()->find('name=?',array('Unknown'))->id,$patient->familyHistory[1]->side_id);
		$this->assertInstanceOf('FamilyHistoryCondition',$patient->familyHistory[1]->condition);
		$this->assertEquals('Cataract',$patient->familyHistory[1]->condition->name);
		$this->assertEquals(\FamilyHistoryCondition::model()->find('name=?',array('Cataract'))->id,$patient->familyHistory[1]->condition_id);
		$this->assertEquals('i am writing more tests',$patient->familyHistory[1]->comments);
	}

	public function testResourceToModel_Save_Create_ModelCountsCorrect()
	{
		$resource = $this->getResource();

		$total_fh = count(\FamilyHistory::model()->findAll());

		$ps = new PatientFamilyHistoryService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient2'));

		$this->assertEquals($total_fh+2, count(\FamilyHistory::model()->findAll()));
	}

	public function testResourceToModel_Save_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientFamilyHistoryService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient2'));

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->familyHistory);

		$this->assertInstanceOf('FamilyHistory',$patient->familyHistory[0]);
		$this->assertInstanceOf('FamilyHistoryRelative',$patient->familyHistory[0]->relative);
		$this->assertEquals('Cousin',$patient->familyHistory[0]->relative->name);
		$this->assertEquals(\FamilyHistoryRelative::model()->find('name=?',array('Cousin'))->id,$patient->familyHistory[0]->relative_id);
		$this->assertInstanceOf('FamilyHistorySide',$patient->familyHistory[0]->side);
		$this->assertEquals('Maternal',$patient->familyHistory[0]->side->name);
		$this->assertEquals(\FamilyHistorySide::model()->find('name=?',array('Maternal'))->id,$patient->familyHistory[0]->side_id);
		$this->assertInstanceOf('FamilyHistoryCondition',$patient->familyHistory[0]->condition);
		$this->assertEquals('Other',$patient->familyHistory[0]->condition->name);
		$this->assertEquals(\FamilyHistoryCondition::model()->find('name=?',array('Other'))->id,$patient->familyHistory[0]->condition_id);
		$this->assertEquals('i am writing tests',$patient->familyHistory[0]->comments);

		$this->assertInstanceOf('FamilyHistory',$patient->familyHistory[1]);
		$this->assertInstanceOf('FamilyHistoryRelative',$patient->familyHistory[1]->relative);
		$this->assertEquals('Grandmother',$patient->familyHistory[1]->relative->name);
		$this->assertEquals(\FamilyHistoryRelative::model()->find('name=?',array('Grandmother'))->id,$patient->familyHistory[1]->relative_id);
		$this->assertInstanceOf('FamilyHistorySide',$patient->familyHistory[1]->side);
		$this->assertEquals('Unknown',$patient->familyHistory[1]->side->name);
		$this->assertEquals(\FamilyHistorySide::model()->find('name=?',array('Unknown'))->id,$patient->familyHistory[1]->side_id);
		$this->assertInstanceOf('FamilyHistoryCondition',$patient->familyHistory[1]->condition);
		$this->assertEquals('Cataract',$patient->familyHistory[1]->condition->name);
		$this->assertEquals(\FamilyHistoryCondition::model()->find('name=?',array('Cataract'))->id,$patient->familyHistory[1]->condition_id);
		$this->assertEquals('i am writing more tests',$patient->familyHistory[1]->comments);
	}

	public function testResourceToModel_Save_Create_DBIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientFamilyHistoryService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient2'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->familyHistory);

		$this->assertInstanceOf('FamilyHistory',$patient->familyHistory[0]);
		$this->assertInstanceOf('FamilyHistoryRelative',$patient->familyHistory[0]->relative);
		$this->assertEquals('Cousin',$patient->familyHistory[0]->relative->name);
		$this->assertEquals(\FamilyHistoryRelative::model()->find('name=?',array('Cousin'))->id,$patient->familyHistory[0]->relative_id);
		$this->assertInstanceOf('FamilyHistorySide',$patient->familyHistory[0]->side);
		$this->assertEquals('Maternal',$patient->familyHistory[0]->side->name);
		$this->assertEquals(\FamilyHistorySide::model()->find('name=?',array('Maternal'))->id,$patient->familyHistory[0]->side_id);
		$this->assertInstanceOf('FamilyHistoryCondition',$patient->familyHistory[0]->condition);
		$this->assertEquals('Other',$patient->familyHistory[0]->condition->name);
		$this->assertEquals(\FamilyHistoryCondition::model()->find('name=?',array('Other'))->id,$patient->familyHistory[0]->condition_id);
		$this->assertEquals('i am writing tests',$patient->familyHistory[0]->comments);

		$this->assertInstanceOf('FamilyHistory',$patient->familyHistory[1]);
		$this->assertInstanceOf('FamilyHistoryRelative',$patient->familyHistory[1]->relative);
		$this->assertEquals('Grandmother',$patient->familyHistory[1]->relative->name);
		$this->assertEquals(\FamilyHistoryRelative::model()->find('name=?',array('Grandmother'))->id,$patient->familyHistory[1]->relative_id);
		$this->assertInstanceOf('FamilyHistorySide',$patient->familyHistory[1]->side);
		$this->assertEquals('Unknown',$patient->familyHistory[1]->side->name);
		$this->assertEquals(\FamilyHistorySide::model()->find('name=?',array('Unknown'))->id,$patient->familyHistory[1]->side_id);
		$this->assertInstanceOf('FamilyHistoryCondition',$patient->familyHistory[1]->condition);
		$this->assertEquals('Cataract',$patient->familyHistory[1]->condition->name);
		$this->assertEquals(\FamilyHistoryCondition::model()->find('name=?',array('Cataract'))->id,$patient->familyHistory[1]->condition_id);
		$this->assertEquals('i am writing more tests',$patient->familyHistory[1]->comments);
	}

	public function testResourceToModel_Save_Update_Modified_ModelCountsCorrect()
	{
		$resource = $this->getResource();

		$total_fh = count(\FamilyHistory::model()->findAll());

		$ps = new PatientFamilyHistoryService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'));

		$this->assertEquals($total_fh-1, count(\FamilyHistory::model()->findAll()));
	}

	public function testResourceToModel_Save_Update_NotModified_ModelCountsCorrect()
	{
		$resource = \Yii::app()->service->PatientFamilyHistory(1)->fetch();

		$total_fh = count(\FamilyHistory::model()->findAll());

		$ps = new PatientFamilyHistoryService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'));

		$this->assertEquals($total_fh, count(\FamilyHistory::model()->findAll()));
	}

	public function testResourceToModel_Save_Update_ModelIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientFamilyHistoryService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'));

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->familyHistory);

		$this->assertInstanceOf('FamilyHistory',$patient->familyHistory[0]);
		$this->assertInstanceOf('FamilyHistoryRelative',$patient->familyHistory[0]->relative);
		$this->assertEquals('Cousin',$patient->familyHistory[0]->relative->name);
		$this->assertEquals(\FamilyHistoryRelative::model()->find('name=?',array('Cousin'))->id,$patient->familyHistory[0]->relative_id);
		$this->assertInstanceOf('FamilyHistorySide',$patient->familyHistory[0]->side);
		$this->assertEquals('Maternal',$patient->familyHistory[0]->side->name);
		$this->assertEquals(\FamilyHistorySide::model()->find('name=?',array('Maternal'))->id,$patient->familyHistory[0]->side_id);
		$this->assertInstanceOf('FamilyHistoryCondition',$patient->familyHistory[0]->condition);
		$this->assertEquals('Other',$patient->familyHistory[0]->condition->name);
		$this->assertEquals(\FamilyHistoryCondition::model()->find('name=?',array('Other'))->id,$patient->familyHistory[0]->condition_id);
		$this->assertEquals('i am writing tests',$patient->familyHistory[0]->comments);

		$this->assertInstanceOf('FamilyHistory',$patient->familyHistory[1]);
		$this->assertInstanceOf('FamilyHistoryRelative',$patient->familyHistory[1]->relative);
		$this->assertEquals('Grandmother',$patient->familyHistory[1]->relative->name);
		$this->assertEquals(\FamilyHistoryRelative::model()->find('name=?',array('Grandmother'))->id,$patient->familyHistory[1]->relative_id);
		$this->assertInstanceOf('FamilyHistorySide',$patient->familyHistory[1]->side);
		$this->assertEquals('Unknown',$patient->familyHistory[1]->side->name);
		$this->assertEquals(\FamilyHistorySide::model()->find('name=?',array('Unknown'))->id,$patient->familyHistory[1]->side_id);
		$this->assertInstanceOf('FamilyHistoryCondition',$patient->familyHistory[1]->condition);
		$this->assertEquals('Cataract',$patient->familyHistory[1]->condition->name);
		$this->assertEquals(\FamilyHistoryCondition::model()->find('name=?',array('Cataract'))->id,$patient->familyHistory[1]->condition_id);
		$this->assertEquals('i am writing more tests',$patient->familyHistory[1]->comments);
	}

	public function testResourceToModel_Save_Update_DBIsCorrect()
	{
		$resource = $this->getResource();

		$ps = new PatientFamilyHistoryService;
		$patient = $ps->resourceToModel($resource, $this->patients('patient1'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->familyHistory);

		$this->assertInstanceOf('FamilyHistory',$patient->familyHistory[0]);
		$this->assertInstanceOf('FamilyHistoryRelative',$patient->familyHistory[0]->relative);
		$this->assertEquals('Cousin',$patient->familyHistory[0]->relative->name);
		$this->assertEquals(\FamilyHistoryRelative::model()->find('name=?',array('Cousin'))->id,$patient->familyHistory[0]->relative_id);
		$this->assertInstanceOf('FamilyHistorySide',$patient->familyHistory[0]->side);
		$this->assertEquals('Maternal',$patient->familyHistory[0]->side->name);
		$this->assertEquals(\FamilyHistorySide::model()->find('name=?',array('Maternal'))->id,$patient->familyHistory[0]->side_id);
		$this->assertInstanceOf('FamilyHistoryCondition',$patient->familyHistory[0]->condition);
		$this->assertEquals('Other',$patient->familyHistory[0]->condition->name);
		$this->assertEquals(\FamilyHistoryCondition::model()->find('name=?',array('Other'))->id,$patient->familyHistory[0]->condition_id);
		$this->assertEquals('i am writing tests',$patient->familyHistory[0]->comments);

		$this->assertInstanceOf('FamilyHistory',$patient->familyHistory[1]);
		$this->assertInstanceOf('FamilyHistoryRelative',$patient->familyHistory[1]->relative);
		$this->assertEquals('Grandmother',$patient->familyHistory[1]->relative->name);
		$this->assertEquals(\FamilyHistoryRelative::model()->find('name=?',array('Grandmother'))->id,$patient->familyHistory[1]->relative_id);
		$this->assertInstanceOf('FamilyHistorySide',$patient->familyHistory[1]->side);
		$this->assertEquals('Unknown',$patient->familyHistory[1]->side->name);
		$this->assertEquals(\FamilyHistorySide::model()->find('name=?',array('Unknown'))->id,$patient->familyHistory[1]->side_id);
		$this->assertInstanceOf('FamilyHistoryCondition',$patient->familyHistory[1]->condition);
		$this->assertEquals('Cataract',$patient->familyHistory[1]->condition->name);
		$this->assertEquals(\FamilyHistoryCondition::model()->find('name=?',array('Cataract'))->id,$patient->familyHistory[1]->condition_id);
		$this->assertEquals('i am writing more tests',$patient->familyHistory[1]->comments);
	}

	public function testJsonToResource()
	{
		$json = '{"history":[{"relative":"Cousin","side":"Maternal","condition":"Other","comments":"i am writing tests","id":null,"last_modified":null},{"relative":"Grandmother","side":"Unknown","condition":"Cataract","comments":"i am writing more tests","id":null,"last_modified":null}],"id":null,"last_modified":null}';

		$ps = new PatientFamilyHistoryService;
		$resource = $ps->jsonToResource($json);

		$this->assertInstanceOf('services\PatientFamilyHistory',$resource);
		$this->assertCount(2,$resource->history);

		$this->assertInstanceOf('services\PatientFamilyHistoryItem',$resource->history[0]);
		$this->assertEquals('Cousin',$resource->history[0]->relative);
		$this->assertEquals('Maternal',$resource->history[0]->side);
		$this->assertEquals('Other',$resource->history[0]->condition);
		$this->assertEquals('i am writing tests',$resource->history[0]->comments);

		$this->assertInstanceOf('services\PatientFamilyHistoryItem',$resource->history[1]);
		$this->assertEquals('Grandmother',$resource->history[1]->relative);
		$this->assertEquals('Unknown',$resource->history[1]->side);
		$this->assertEquals('Cataract',$resource->history[1]->condition);
		$this->assertEquals('i am writing more tests',$resource->history[1]->comments);
	}

	public function testJsonToModel_NoSave_NoNewRows()
	{
		$json = '{"history":[{"relative":"Cousin","side":"Maternal","condition":"Other","comments":"i am writing tests","id":null,"last_modified":null},{"relative":"Grandmother","side":"Unknown","condition":"Cataract","comments":"i am writing more tests","id":null,"last_modified":null}],"id":null,"last_modified":null}';

		$total_fh = count(\FamilyHistory::model()->findAll());

		$ps = new PatientFamilyHistoryService;
		$patient = $ps->jsonToModel($json, $this->patients('patient2'), false);

		$this->assertEquals($total_fh, count(\FamilyHistory::model()->findAll()));
	}

	public function testJsonToModel_NoSave_ModelIsCorrect()
	{
		$json = '{"history":[{"relative":"Cousin","side":"Maternal","condition":"Other","comments":"i am writing tests","id":null,"last_modified":null},{"relative":"Grandmother","side":"Unknown","condition":"Cataract","comments":"i am writing more tests","id":null,"last_modified":null}],"id":null,"last_modified":null}';

		$ps = new PatientFamilyHistoryService;
		$patient = $ps->jsonToModel($json, $this->patients('patient2'), false);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->familyHistory);

		$this->assertInstanceOf('FamilyHistory',$patient->familyHistory[0]);
		$this->assertInstanceOf('FamilyHistoryRelative',$patient->familyHistory[0]->relative);
		$this->assertEquals('Cousin',$patient->familyHistory[0]->relative->name);
		$this->assertEquals(\FamilyHistoryRelative::model()->find('name=?',array('Cousin'))->id,$patient->familyHistory[0]->relative_id);
		$this->assertInstanceOf('FamilyHistorySide',$patient->familyHistory[0]->side);
		$this->assertEquals('Maternal',$patient->familyHistory[0]->side->name);
		$this->assertEquals(\FamilyHistorySide::model()->find('name=?',array('Maternal'))->id,$patient->familyHistory[0]->side_id);
		$this->assertInstanceOf('FamilyHistoryCondition',$patient->familyHistory[0]->condition);
		$this->assertEquals('Other',$patient->familyHistory[0]->condition->name);
		$this->assertEquals(\FamilyHistoryCondition::model()->find('name=?',array('Other'))->id,$patient->familyHistory[0]->condition_id);
		$this->assertEquals('i am writing tests',$patient->familyHistory[0]->comments);

		$this->assertInstanceOf('FamilyHistory',$patient->familyHistory[1]);
		$this->assertInstanceOf('FamilyHistoryRelative',$patient->familyHistory[1]->relative);
		$this->assertEquals('Grandmother',$patient->familyHistory[1]->relative->name);
		$this->assertEquals(\FamilyHistoryRelative::model()->find('name=?',array('Grandmother'))->id,$patient->familyHistory[1]->relative_id);
		$this->assertInstanceOf('FamilyHistorySide',$patient->familyHistory[1]->side);
		$this->assertEquals('Unknown',$patient->familyHistory[1]->side->name);
		$this->assertEquals(\FamilyHistorySide::model()->find('name=?',array('Unknown'))->id,$patient->familyHistory[1]->side_id);
		$this->assertInstanceOf('FamilyHistoryCondition',$patient->familyHistory[1]->condition);
		$this->assertEquals('Cataract',$patient->familyHistory[1]->condition->name);
		$this->assertEquals(\FamilyHistoryCondition::model()->find('name=?',array('Cataract'))->id,$patient->familyHistory[1]->condition_id);
		$this->assertEquals('i am writing more tests',$patient->familyHistory[1]->comments);
	}

	public function testJsonToModel_Save_Create_ModelCountsCorrect()
	{
		$json = '{"history":[{"relative":"Cousin","side":"Maternal","condition":"Other","comments":"i am writing tests","id":null,"last_modified":null},{"relative":"Grandmother","side":"Unknown","condition":"Cataract","comments":"i am writing more tests","id":null,"last_modified":null}],"id":null,"last_modified":null}';

		$total_fh = count(\FamilyHistory::model()->findAll());

		$ps = new PatientFamilyHistoryService;
		$patient = $ps->jsonToModel($json, $this->patients('patient2'));

		$this->assertEquals($total_fh+2, count(\FamilyHistory::model()->findAll()));
	}

	public function testJsonToModel_Save_Create_ModelIsCorrect()
	{
		$json = '{"history":[{"relative":"Cousin","side":"Maternal","condition":"Other","comments":"i am writing tests","id":null,"last_modified":null},{"relative":"Grandmother","side":"Unknown","condition":"Cataract","comments":"i am writing more tests","id":null,"last_modified":null
}],"id":null,"last_modified":null}';

		$ps = new PatientFamilyHistoryService;
		$patient = $ps->jsonToModel($json, $this->patients('patient2'));

		$this->assertInstanceOf('FamilyHistory',$patient->familyHistory[0]);
		$this->assertInstanceOf('FamilyHistoryRelative',$patient->familyHistory[0]->relative);
		$this->assertEquals('Cousin',$patient->familyHistory[0]->relative->name);
		$this->assertEquals(\FamilyHistoryRelative::model()->find('name=?',array('Cousin'))->id,$patient->familyHistory[0]->relative_id);
		$this->assertInstanceOf('FamilyHistorySide',$patient->familyHistory[0]->side);
		$this->assertEquals('Maternal',$patient->familyHistory[0]->side->name);
		$this->assertEquals(\FamilyHistorySide::model()->find('name=?',array('Maternal'))->id,$patient->familyHistory[0]->side_id);
		$this->assertInstanceOf('FamilyHistoryCondition',$patient->familyHistory[0]->condition);
		$this->assertEquals('Other',$patient->familyHistory[0]->condition->name);
		$this->assertEquals(\FamilyHistoryCondition::model()->find('name=?',array('Other'))->id,$patient->familyHistory[0]->condition_id);
		$this->assertEquals('i am writing tests',$patient->familyHistory[0]->comments);
		
		$this->assertInstanceOf('FamilyHistory',$patient->familyHistory[1]);
		$this->assertInstanceOf('FamilyHistoryRelative',$patient->familyHistory[1]->relative);
		$this->assertEquals('Grandmother',$patient->familyHistory[1]->relative->name);
		$this->assertEquals(\FamilyHistoryRelative::model()->find('name=?',array('Grandmother'))->id,$patient->familyHistory[1]->relative_id);
		$this->assertInstanceOf('FamilyHistorySide',$patient->familyHistory[1]->side);
		$this->assertEquals('Unknown',$patient->familyHistory[1]->side->name);
		$this->assertEquals(\FamilyHistorySide::model()->find('name=?',array('Unknown'))->id,$patient->familyHistory[1]->side_id);
		$this->assertInstanceOf('FamilyHistoryCondition',$patient->familyHistory[1]->condition);
		$this->assertEquals('Cataract',$patient->familyHistory[1]->condition->name);
		$this->assertEquals(\FamilyHistoryCondition::model()->find('name=?',array('Cataract'))->id,$patient->familyHistory[1]->condition_id);
		$this->assertEquals('i am writing more tests',$patient->familyHistory[1]->comments);
	}

	public function testJsonToModel_Save_Create_DBIsCorrect()
	{
		$json = '{"history":[{"relative":"Cousin","side":"Maternal","condition":"Other","comments":"i am writing tests","id":null,"last_modified":null},{"relative":"Grandmother","side":"Unknown","condition":"Cataract","comments":"i am writing more tests","id":null,"last_modified":null}],"id":null,"last_modified":null}';

		$ps = new PatientFamilyHistoryService;
		$patient = $ps->jsonToModel($json, $this->patients('patient2'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('FamilyHistory',$patient->familyHistory[0]);
		$this->assertInstanceOf('FamilyHistoryRelative',$patient->familyHistory[0]->relative);
		$this->assertEquals('Cousin',$patient->familyHistory[0]->relative->name);
		$this->assertEquals(\FamilyHistoryRelative::model()->find('name=?',array('Cousin'))->id,$patient->familyHistory[0]->relative_id);
		$this->assertInstanceOf('FamilyHistorySide',$patient->familyHistory[0]->side);
		$this->assertEquals('Maternal',$patient->familyHistory[0]->side->name);
		$this->assertEquals(\FamilyHistorySide::model()->find('name=?',array('Maternal'))->id,$patient->familyHistory[0]->side_id);
		$this->assertInstanceOf('FamilyHistoryCondition',$patient->familyHistory[0]->condition);
		$this->assertEquals('Other',$patient->familyHistory[0]->condition->name);
		$this->assertEquals(\FamilyHistoryCondition::model()->find('name=?',array('Other'))->id,$patient->familyHistory[0]->condition_id);
		$this->assertEquals('i am writing tests',$patient->familyHistory[0]->comments);

		$this->assertInstanceOf('FamilyHistory',$patient->familyHistory[1]);
		$this->assertInstanceOf('FamilyHistoryRelative',$patient->familyHistory[1]->relative);
		$this->assertEquals('Grandmother',$patient->familyHistory[1]->relative->name);
		$this->assertEquals(\FamilyHistoryRelative::model()->find('name=?',array('Grandmother'))->id,$patient->familyHistory[1]->relative_id);
		$this->assertInstanceOf('FamilyHistorySide',$patient->familyHistory[1]->side);
		$this->assertEquals('Unknown',$patient->familyHistory[1]->side->name);
		$this->assertEquals(\FamilyHistorySide::model()->find('name=?',array('Unknown'))->id,$patient->familyHistory[1]->side_id);
		$this->assertInstanceOf('FamilyHistoryCondition',$patient->familyHistory[1]->condition);
		$this->assertEquals('Cataract',$patient->familyHistory[1]->condition->name);
		$this->assertEquals(\FamilyHistoryCondition::model()->find('name=?',array('Cataract'))->id,$patient->familyHistory[1]->condition_id);
		$this->assertEquals('i am writing more tests',$patient->familyHistory[1]->comments);
	}

	public function testJsonToModel_Save_Update_ModelCountsCorrect()
	{
		$json = '{"history":[{"relative":"Cousin","side":"Maternal","condition":"Other","comments":"i am writing tests","id":null,"last_modified":null},{"relative":"Grandmother","side":"Unknown","condition":"Cataract","comments":"i am writing more tests","id":null,"last_modified":null}],"id":null,"last_modified":null}';

		$total_fh = count(\FamilyHistory::model()->findAll());

		$ps = new PatientFamilyHistoryService;
		$patient = $ps->jsonToModel($json, $this->patients('patient1'));

		$this->assertEquals($total_fh-1, count(\FamilyHistory::model()->findAll()));
	}

	public function testJsonToModel_Save_Update_ModelIsCorrect()
	{
		$json = '{"history":[{"relative":"Cousin","side":"Maternal","condition":"Other","comments":"i am writing tests","id":null,"last_modified":null},{"relative":"Grandmother","side":"Unknown","condition":"Cataract","comments":"i am writing more tests","id":null,"last_modified":null}],"id":null,"last_modified":null}';

		$ps = new PatientFamilyHistoryService;
		$patient = $ps->jsonToModel($json, $this->patients('patient1'));

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->familyHistory);

		$this->assertInstanceOf('FamilyHistory',$patient->familyHistory[0]);
		$this->assertInstanceOf('FamilyHistoryRelative',$patient->familyHistory[0]->relative);
		$this->assertEquals('Cousin',$patient->familyHistory[0]->relative->name);
		$this->assertEquals(\FamilyHistoryRelative::model()->find('name=?',array('Cousin'))->id,$patient->familyHistory[0]->relative_id);
		$this->assertInstanceOf('FamilyHistorySide',$patient->familyHistory[0]->side);
		$this->assertEquals('Maternal',$patient->familyHistory[0]->side->name);
		$this->assertEquals(\FamilyHistorySide::model()->find('name=?',array('Maternal'))->id,$patient->familyHistory[0]->side_id);
		$this->assertInstanceOf('FamilyHistoryCondition',$patient->familyHistory[0]->condition);
		$this->assertEquals('Other',$patient->familyHistory[0]->condition->name);
		$this->assertEquals(\FamilyHistoryCondition::model()->find('name=?',array('Other'))->id,$patient->familyHistory[0]->condition_id);
		$this->assertEquals('i am writing tests',$patient->familyHistory[0]->comments);

		$this->assertInstanceOf('FamilyHistory',$patient->familyHistory[1]);
		$this->assertInstanceOf('FamilyHistoryRelative',$patient->familyHistory[1]->relative);
		$this->assertEquals('Grandmother',$patient->familyHistory[1]->relative->name);
		$this->assertEquals(\FamilyHistoryRelative::model()->find('name=?',array('Grandmother'))->id,$patient->familyHistory[1]->relative_id);
		$this->assertInstanceOf('FamilyHistorySide',$patient->familyHistory[1]->side);
		$this->assertEquals('Unknown',$patient->familyHistory[1]->side->name);
		$this->assertEquals(\FamilyHistorySide::model()->find('name=?',array('Unknown'))->id,$patient->familyHistory[1]->side_id);
		$this->assertInstanceOf('FamilyHistoryCondition',$patient->familyHistory[1]->condition);
		$this->assertEquals('Cataract',$patient->familyHistory[1]->condition->name);
		$this->assertEquals(\FamilyHistoryCondition::model()->find('name=?',array('Cataract'))->id,$patient->familyHistory[1]->condition_id);
		$this->assertEquals('i am writing more tests',$patient->familyHistory[1]->comments);
	}

	public function testJsonToModel_Save_Update_DBIsCorrect()
	{
		$json = '{"history":[{"relative":"Cousin","side":"Maternal","condition":"Other","comments":"i am writing tests","id":null,"last_modified":null},{"relative":"Grandmother","side":"Unknown","condition":"Cataract","comments":"i am writing more tests","id":null,"last_modified":null}],"id":null,"last_modified":null}';

		$ps = new PatientFamilyHistoryService;
		$patient = $ps->jsonToModel($json, $this->patients('patient1'));
		$patient = \Patient::model()->findByPk($patient->id);

		$this->assertInstanceOf('Patient',$patient);
		$this->assertCount(2,$patient->familyHistory);

		$this->assertInstanceOf('FamilyHistory',$patient->familyHistory[0]);
		$this->assertInstanceOf('FamilyHistoryRelative',$patient->familyHistory[0]->relative);
		$this->assertEquals('Cousin',$patient->familyHistory[0]->relative->name);
		$this->assertEquals(\FamilyHistoryRelative::model()->find('name=?',array('Cousin'))->id,$patient->familyHistory[0]->relative_id);
		$this->assertInstanceOf('FamilyHistorySide',$patient->familyHistory[0]->side);
		$this->assertEquals('Maternal',$patient->familyHistory[0]->side->name);
		$this->assertEquals(\FamilyHistorySide::model()->find('name=?',array('Maternal'))->id,$patient->familyHistory[0]->side_id);
		$this->assertInstanceOf('FamilyHistoryCondition',$patient->familyHistory[0]->condition);
		$this->assertEquals('Other',$patient->familyHistory[0]->condition->name);
		$this->assertEquals(\FamilyHistoryCondition::model()->find('name=?',array('Other'))->id,$patient->familyHistory[0]->condition_id);
		$this->assertEquals('i am writing tests',$patient->familyHistory[0]->comments);

		$this->assertInstanceOf('FamilyHistory',$patient->familyHistory[1]);
		$this->assertInstanceOf('FamilyHistoryRelative',$patient->familyHistory[1]->relative);
		$this->assertEquals('Grandmother',$patient->familyHistory[1]->relative->name);
		$this->assertEquals(\FamilyHistoryRelative::model()->find('name=?',array('Grandmother'))->id,$patient->familyHistory[1]->relative_id);
		$this->assertInstanceOf('FamilyHistorySide',$patient->familyHistory[1]->side);
		$this->assertEquals('Unknown',$patient->familyHistory[1]->side->name);
		$this->assertEquals(\FamilyHistorySide::model()->find('name=?',array('Unknown'))->id,$patient->familyHistory[1]->side_id);
		$this->assertInstanceOf('FamilyHistoryCondition',$patient->familyHistory[1]->condition);
		$this->assertEquals('Cataract',$patient->familyHistory[1]->condition->name);
		$this->assertEquals(\FamilyHistoryCondition::model()->find('name=?',array('Cataract'))->id,$patient->familyHistory[1]->condition_id);
		$this->assertEquals('i am writing more tests',$patient->familyHistory[1]->comments);
	}
}
