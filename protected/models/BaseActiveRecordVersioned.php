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
	private $fetch_from_version = false;
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

	/**
	 * Return current versioning status
	 */
	public function getVersionCreateStatus()
	{
		return $this->enable_version;
	}

	/* Fetch from version */

	public function fromVersion()
	{
		// Clone to avoid singleton problems
		$model = clone $this;
		$model->fetch_from_version = true;

		return $model;
	}

	/* Disable fetch from version */

	public function notFromVersion()
	{
		// Clone to avoid singleton problems
		$model = clone $this;
		$model->fetch_from_version = false;

		return $model;
	}

	/**
	 * Fetch current version retrieval status
	 */
	public function getVersionRetrievalStatus()
	{
		return $this->fetch_from_version;
	}

	/**
	 * Returns true if this is the active version of the record
	 */
	public function isActiveVersion()
	{
		return !$this->version_id;
	}

	/**
	 * Returns true if the current instance of the model is an old version
	 */
	public function getTableSchema()
	{
		if ($this->fetch_from_version) {
			return $this->getDbConnection()->getSchema()->getTable($this->tableName().'_version');
		}

		return parent::getTableSchema();
	}

	public function getPreviousVersion()
	{
		if (!$this->id) {
			throw new Exception("Model has not been initialised");
		}

		$condition = 'id = :id';
		$params = array(':id' => $this->id);

		if ($this->version_id) {
			$condition .= ' and version_id < :version_id';
			$params[':version_id'] = $this->version_id;
		}

		return $this->model()->fromVersion()->find(array(
			'condition' => $condition,
			'params' => $params,
			'order' => 'version_id desc',
		));

		return $model;
	}

	/**
	 * Get previous version by transaction ID
	 */
	public function getPreviousVersionByTransactionID($transaction_id)
	{
		if (!$this->id) {
			throw new Exception("Model has not been initialised");
		}

		return $this->model()->fromVersion()->find(array(
			'condition' => 'id = :id and transaction_id = :transaction_id',
			'params' => array(
				':id' => $this->id,
				':transaction_id' => $transaction_id,
			),
		));
	}

	/**
	 * Return true if the model has any historic data for a specific transaction ID
	 */
	public function hasTransactionID($transaction_id)
	{
		return (boolean)$this->model()->fromVersion()->find(array(
			'condition' => 'transaction_id = :transaction_id',
			'params' => array(
				':transaction_id' => $transaction_id,
			),
		));
	}

	/* Return all previous versions ordered by most recent */

	public function getPreviousVersions()
	{
		$condition = 'id = :id';
		$params = array(':id' => $this->id);

		if ($this->version_id) {
			$condition .= ' and version_id = :version_id';
			$params[':version_id'] = $this->version_id;
		}

		return $this->model()->fromVersion()->findAll(array(
			'condition' => $condition,
			'params' => $params,
			'order' => 'version_id desc',
		));
	}

	public function getVersionTableSchema()
	{
		return Yii::app()->db->getSchema()->getTable($this->tableName().'_version');
	}

	public function getCommandBuilder()
	{
		return new OECommandBuilder($this->getDbConnection()->getSchema());
	}

	public function updateByPk($pk,$attributes,$condition='',$params=array())
	{
		$table = $this->getTableSchema();

		$transaction = Yii::app()->db->getCurrentTransaction() === null ? Yii::app()->db->beginTransaction() : false;

		try {
			if (!$this->enable_version || $this->versionToTableByPk($pk,$condition,$params)) {
				$result = parent::updateByPk($pk,$attributes,$condition,$params);

				if ($transaction) {
					// No big deal if $result is 0, it just means the row was unchanged so no new version row is required
					$result ? $transaction->commit() : $transaction->rollback();
				}

				return $result;
			}
		} catch (Exception $e) {
			if ($transaction) {
				$transaction->rollback();
			}
			throw $e;
		}

		if ($transaction) {
			$transaction->rollback();
		}

		return false;
	}

	public function updateAll($attributes,$condition='',$params=array())
	{
		$transaction = Yii::app()->db->getCurrentTransaction() === null ? Yii::app()->db->beginTransaction() : false;

		try {
			if (!$this->enable_version || $this->versionAllToTable($condition,$params)) {
				$result = parent::updateAll($attributes,$condition,$params);

				if ($transaction && $result) {
					$transaction->commit();
				}

				return $result;
			}
		} catch (Exception $e) {
			if ($transaction) {
				$transaction->rollback();
			}
			throw $e;
		}

		if ($transaction) {
			$transaction->rollback();
		}

		return false;
	}

	private function versionToTableByPk($pk, $condition, $params=array())
	{
		$builder = $this->getCommandBuilder();
		$table = $this->getTableSchema();
		$table_version = $this->getVersionTableSchema();

		$criteria = $builder->createPkCriteria($table,$pk,$condition,$params);

		$command = $builder->createInsertFromTableCommand($table_version,$table,$criteria);

		return $command->execute();
	}

	private function versionAllToTable($condition, $params=array())
	{
		$builder = $this->getCommandBuilder();
		$table = $this->getTableSchema();
		$table_version = $this->getVersionTableSchema();

		$criteria = $builder->createCriteria($condition, $params);

		$command = $builder->createInsertFromTableCommand($table_version,$table,$criteria);

		return $command->execute();
	}

	public function save($runValidation=true, $attributes=null, $allow_overriding=false)
	{
		if ($this->version_id) {
			throw new Exception("save() should not be called on versiond model instances.");
		}

		return parent::save($runValidation, $attributes, $allow_overriding);
	}

	public function delete()
	{
		if ($this->version_id) {
			throw new Exception("delete() should not be called on versiond model instances.");
		}

		return parent::delete();
	}

	public function resetScope($resetDefault=true)
	{
		$this->enable_version = true;
		$this->fetch_from_version = false;

		return parent::resetScope($resetDefault);
	}

	/**
	 * Get the list of transactions for this object
	 */
	public function getFullTransactionList()
	{
		$transactions = array();

		$model = get_class($this);
		$active = $model::model()->findByPk($this->id);

		$transactions[0] = 'Current: by '.User::model()->findByPk($active->last_modified_user_id)->fullName.' on '.$active->NHSDate('last_modified_date').' at '.substr($active->last_modified_date,11,5);

		foreach ($active->getPreviousVersions() as $previous_version) {
			if ($previous_version->transaction_id) {
				$transactions[$previous_version->transaction_id] = 'Edit by '.User::model()->findByPk($previous_version->last_modified_user_id)->fullName.' on '.$previous_version->NHSDate('last_modified_date').' at '.substr($previous_version->last_modified_date,11,5);
			}
		}

		return $transactions;
	}

	/**
	 * Remap relations to version table queries
	 */
	public function getRelated($name,$refresh=false,$params=array())
	{
		if (!$this->isActiveVersion()) {
			return $this->handleVersionRelation($name);
		}

		return parent::getRelated($name,$refresh,$params);
	}

	/**
	 * Takes a relation defined on the model and returns a query to derive the same data from the version tables
	 */
	private function handleVersionRelation($name)
	{
		$relations = $this->relations();

		$relation = $relations[$name];

		foreach ($relation as $i => $value) {
			if (!is_int($i) && !in_array($i,array('condition','on','params','order'))) {
				throw new Exception("Unhandled relation property: $i");
			}
		}

		$criteria = new CDbCriteria;

		isset($relation['condition']) && $criteria->addCondition($relation['condition']);
		isset($relation['params']) && $criteria->params = $relation['params'];
		isset($relation['on']) && $criteria->addCondition($relation['on']);
		isset($relation['order']) && $criteria->order = $relation['order'];
		isset($relation['limit']) && $criteria->limit = $relation['limit'];
		isset($relation['offset']) && $criteria->offset = $relation['offset'];

		switch ($relation[0]) {
			case 'CBelongsToRelation':
				$related = $relation[1]::model()->findByPk($this->{$relation[2]});

				if (method_exists($related,'getPreviousVersionByTransactionID')) {
					if ($previous_version = $related->getPreviousVersionByTransactionID($this->transaction_id)) {
						return $previous_version;
					}
				}

				return $related;

			case 'CHasOneRelation':
				$criteria = new CDbCriteria;

				$criteria->addCondition($relation[2].' = :'.$relation[2]);
				$criteria->params[':'.$relation[2]] = $this->{$this->tableSchema->primaryKey};

				$related = $relation[1]::model()->find($criteria);

				if (method_exists($related,'getPreviousVersionByTransactionID')) {
					if ($previous_version = $related->getPreviousVersionByTransactionID($this->transaction_id)) {
						return $previous_version;
					}
				}

				return $related;

			case 'CHasManyRelation':
				$criteria = new CDbCriteria;

				$criteria->addCondition($relation[2].' = :'.$relation[2]);
				$criteria->params[':'.$relation[2]] = $this->{$this->tableSchema->primaryKey};

				if ($relation[1]::model()->hasTransactionID($this->transaction_id)) {
					$criteria->addCondition('transaction_id = :transaction_id');
					$criteria->params[':transaction_id'] = $this->transaction_id;

					return $relation[1]::model()->fromVersion()->findAll($criteria);
				}

				return $relation[1]::model()->findAll($criteria);

			case 'CManyManyRelation':
				$criteria = new CDbCriteria;

				if (preg_match('/^(.*?)\((.*?),[\s\t]*(.*?)\)$/',$relation[2],$m)) {
					if (Yii::app()->db->getSchema()->getTable($m[1].'_version') && $this->tableHasTransactionID($m[1].'_version', $this->transaction_id)) {
						$criteria->join = "join `{$m[1]}_version` on `{$m[1]}_version`.`{$m[3]}` = `t`.`".$relation[1]::model()->tableSchema->primaryKey."` and `{$m[1]}_version`.`{$m[2]}` = :{$m[2]} and `{$m[1]}_version`.`transaction_id` = :transaction_id";
						$criteria->params[':transaction_id'] = $this->transaction_id;
					} else {
						$criteria->join = "join `{$m[1]}` on `{$m[1]}`.`{$m[3]}` = `t`.`".$relation[1]::model()->tableSchema->primaryKey."` and `{$m[1]}`.`{$m[2]}` = :{$m[2]}";
					}

					$criteria->params[':'.$m[2]] = $this->{$this->tableSchema->primaryKey};
				}

				return $relation[1]::model()->findAll($criteria);
		}

		return parent::getRelated($name);
	}

	/**
	 * Returns true if the table has any data for the given transaction id
	 */
	public function tableHasTransactionID($table, $transaction_id)
	{
		return (boolean)Yii::app()->db->createCommand()
			->select("transaction_id")
			->from($table)
			->where("transaction_id = :transaction_id",array(
				":transaction_id" => $transaction_id,
			))
			->queryScalar();
	}
}
