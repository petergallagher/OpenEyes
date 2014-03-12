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
class OECommandBuilderTest extends CDbTestCase
{
	public function testCreateInsertFromTableCommand()
	{
		$table = Yii::app()->db->getSchema()->getTable('user');
		$table_version = Yii::app()->db->getSchema()->getTable('user_version');

		$columns = array();
		foreach (array_keys($table_version->columns) as $column) {
			if (!in_array($column,array('version_date','version_id','deleted_transaction_id'))) {
				$columns[] = $column;
			}
		}

		$query = 'INSERT INTO `user_version` (`'.implode('`,`',$columns).'`,`version_date`,`version_id`,`deleted_transaction_id`) SELECT `'.implode('`,`',$columns).'`, :oevalue1, :oevalue2, :oevalue3 FROM `user` join firm_user_assignment on firm_user_assignment.user_id = user.id WHERE id = :id LIMIT 10 OFFSET 12';

		$criteria = new CDbCriteria;
		$criteria->addCondition('id = :id');
		$criteria->params[':id'] = 1;
		$criteria->join = "join firm_user_assignment on firm_user_assignment.user_id = user.id";
		$criteria->limit = 10;
		$criteria->offset = 12;

		$schema = $this->getMock('CDbSchema',array('getDbConnection','loadTable'), array(), '', false);

		$db = $this->getMock('CDbConnection',array('createCommand'), array(), '', false);

		$command = $this->getMock('CDbCommand',array('bindValue'), array(), '', false);

		$schema->expects($this->once())->method('getDbConnection')->will($this->returnValue($db)); 
		$db->expects($this->once())->method('createCommand')->with($query)->will($this->returnValue($command));

		$command->expects($this->at(0))->method('bindValue')->with(':oevalue1',date('Y-m-d H:i:s'));
		$command->expects($this->at(1))->method('bindValue')->with(':oevalue2',null);
		$command->expects($this->at(2))->method('bindValue')->with(':oevalue3',null); 

		$builder = new OECommandBuilder($schema);
		$builder->createInsertFromTableCommand($table_version,$table,$criteria);
	}

	public function testCreateDeleteCommand()
	{
		$table = Yii::app()->db->getSchema()->getTable('user');
		$table_version = Yii::app()->db->getSchema()->getTable('user_version');

		$criteria = new CDbCriteria;
		$criteria->addCondition('id = :id');
		$criteria->params[':id'] = 1;
		$criteria->join = "join firm_user_assignment on firm_user_assignment.user_id = user.id";
		$criteria->limit = 10;
		$criteria->offset = 12;

		$builder = $this->getMock('OECommandBuilder',array(
				'createInsertFromTableCommand',
			),
			array(Yii::app()->db->getSchema()),
			'',
			true
		);

		$command = $this->getMock('CDbCommand',array(
				'execute',
			),
			array(),
			'',
			false
		);

		$command->expects($this->once())
			->method('execute')
			->will($this->returnValue(true));

		$builder->expects($this->once())
			->method('createInsertFromTableCommand')
			->with($table_version,$table,$criteria,null)
			->will($this->returnValue($command));

		$command = $builder->createDeleteCommand($table,$criteria);

		$this->assertEquals('DELETE FROM `user` join firm_user_assignment on firm_user_assignment.user_id = user.id WHERE id = :id LIMIT 10 OFFSET 12', $command->getText());
	}
}
