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

class BaseActiveRecordVersioned extends BaseActiveRecord
{
	private $enable_version = true;
	public $version_id = null;

	/* Disable archiving on save() */

	public function noVersion()
	{
		$this->enable_version = false;

		return $this;
	}

	/* Re-enable archiving on save() */

	public function withVersion()
	{
		$this->enable_version = true;

		return $this;
	}

	public function updateByPk($pk, $attributes, $condition='', $params=array())
	{
		$transaction = $this->dbConnection->beginInternalTransaction();
		try {
			$this->versionToTable($this->commandBuilder->createPkCriteria($this->tableName(), $pk, $condition, $params));
			$result = parent::updateByPk($pk, $attributes, $condition, $params);
			$transaction->commit();
			return $result;
		} catch (Exception $e) {
			$transaction->rollback();
			throw $e;
		}
	}

	public function updateAll($attributes, $condition='', $params=array())
	{
		$transaction = $this->dbConnection->beginInternalTransaction();
		try {
			$this->versionToTable($this->commandBuilder->createCriteria($condition, $params));
			$result = parent::updateAll($attributes,$condition,$params);
			$transaction->commit();
			return $result;
		} catch (Exception $e) {
			$transaction->rollback();
			throw $e;
		}
	}

	public function deleteByPk($pk, $condition = '', $params = array())
	{
		$transaction = $this->dbConnection->beginInternalTransaction();
		try {
			$this->versionToTable($this->commandBuilder->createPkCriteria($this->tableName(), $pk, $condition, $params));
			$result = parent::deleteByPk($pk, $condition, $params);
			$transaction->commit();
			return $result;
		} catch (Exception $e) {
			$transaction->rollback();
			throw $e;
		}
	}

	public function deleteAll($condition = '', $params = array())
	{
		$transaction = $this->dbConnection->beginInternalTransaction();
		try {
			$this->versionToTable($this->commandBuilder->createCriteria($condition, $params));
			$result = parent::deleteAll($condition, $params);
			$transaction->commit();
			return $result;
		} catch (Exception $e) {
			$transaction->rollback();
			throw $e;
		}
	}

	public function deleteAllByAttributes($attributes, $condition = '', $params = array())
	{
		$transaction = $this->dbConnection->beginInternalTransaction();
		try {
			$this->versionToTable($this->commandBuilder->createColumnCriteria($this->tableName(), $attributes, $condition, $params));
			$result = parent::deleteAllByAttributes($attributes, $condition, $params);
			$transaction->commit();
			return $result;
		} catch (Exception $e) {
			$transaction->rollback();
			throw $e;
		}
	}

	public function save($runValidation=true, $attributes=null, $allow_overriding=false)
	{
		if ($this->version_id) {
			throw new Exception("save() should not be called on versiond model instances.");
		}

		return parent::save($runValidation, $attributes, $allow_overriding);
	}

	public function resetScope($resetDefault=true)
	{
		$this->enable_version = true;

		return parent::resetScope($resetDefault);
	}

	protected function versionToTable(CDbCriteria $criteria)
	{
		if (Yii::app()->params['enable_versioning'] && $this->enable_version) {
			$model_name = $this->dbConnection->quoteValue(CHtml::modelName($this));

			$table = $this->getTableSchema();
			$builder = $this->getCommandBuilder();

			$sql = "SELECT {$table->rawName}.* FROM {$table->rawName}";
			$sql=$builder->applyJoin($sql,$criteria->join);
			$sql=$builder->applyCondition($sql,$criteria->condition);
			$sql=$builder->applyOrder($sql,$criteria->order);
			$sql=$builder->applyLimit($sql,$criteria->limit,$criteria->offset);

			$command=$builder->dbConnection->createCommand($sql);
			$builder->bindValues($command,$criteria->params);

			$created_user_id = null;

			try {
				if (isset(Yii::app()->user)) {
					$created_user_id = Yii::app()->user->id;
				}
			} catch (Exception $e) {
			}

			if (!$created_user_id) {
				$created_user_id = 1;
			}
			
			foreach ($command->queryAll() as $row) {
				$id = $this->dbConnection->quoteValue($row['id']);
				$created_date = $this->dbConnection->quoteValue(date('Y-m-d H:i:s'));
				$record_last_modified_date = $this->dbConnection->quoteValue($row['last_modified_date']);
				$record_last_modified_user_id = $this->dbConnection->quoteValue($row['last_modified_user_id']);
				$record_data = $this->dbConnection->quoteValue(serialize($row));

				$sql="INSERT INTO `version_blob` (`record_class_name`,`record_id`,`created_date`,`created_user_id`,`record_last_modified_date`,`record_last_modified_user_id`,`record_data`) VALUES ($model_name,$id,$created_date,$created_user_id,$record_last_modified_date,$record_last_modified_user_id,$record_data)";
				$this->dbConnection->createCommand($sql)->execute();
			}
		}
	}
}
