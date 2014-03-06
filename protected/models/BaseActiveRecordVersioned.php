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
	public $version_date	= null;
	public $deleted_transaction_id = null;

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

	public function getPreviousVersions($unique_hash=false, $active_hash=null)
	{
		$condition = 'id = :id';
		$params = array(':id' => $this->id);

		if ($this->version_id) {
			$condition .= ' and version_id = :version_id';
			$params[':version_id'] = $this->version_id;
		}

		if (!$unique_hash) {
			return $this->model()->fromVersion()->findAll(array(
				'condition' => $condition,
				'params' => $params,
				'order' => 'version_id desc',
			));
		}

		$return = array();

		foreach ($this->model()->fromVersion()->findAll(array(
				'condition' => $condition,
				'params' => $params,
				'order' => 'version_id asc',
			)) as $item) {

			if ($item->hash != $active_hash) {
				$return[] = $item;
				$active_hash = $item->hash;
			}
		}

		return array_reverse($return);
	}

	public function getVersionTableSchema()
	{
		return Yii::app()->db->getSchema()->getTable($this->tableName().'_version');
	}

	public function getCommandBuilder()
	{
		return new OECommandBuilder($this->getDbConnection()->getSchema());
	}

	private function handleTransaction($callback_method, $callback_params, $version_method=null, $version_params=array())
	{
		empty($version_params) && $version_params = $callback_params;

		$transaction = Yii::app()->db->getCurrentTransaction() === null ? Yii::app()->db->beginTransaction() : false;

		Yii::app()->db->transaction->addTable($this->tableName());

		try {
			if (!$this->enable_version || ($version_method === null || call_user_func_array(array($this,$version_method),$version_params))) {
				$result = call_user_func_array('parent::'.$callback_method, $callback_params);

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

	public function updateByPk($pk,$attributes,$condition='',$params=array())
	{
		return $this->handleTransaction('updateByPk',array($pk,$attributes,$condition,$params),'versionToTableByPk',array($pk,$condition,$params));
	}

	public function updateAll($attributes,$condition='',$params=array())
	{
		return $this->handleTransaction('updateAll',array($attributes,$condition,$params),'versionAllToTable',array($condition,$params));
	}

	public function deleteByPk($pk,$condition='',$params=array())
	{
		return $this->handleTransaction('deleteByPk',array($pk,$condition,$params));
	}

	public function deleteAll($condition='',$params=array())
	{
		return $this->handleTransaction('deleteAll',array($condition,$params));
	}

	public function deleteAllByAttributes($attributes,$condition='',$params=array())
	{
		return $this->handleTransaction('deleteAllByAttributes',array($attributes,$condition,$params));
	}

	private function versionToTableByPk($pk, $condition='', $params=array())
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

	private function versionAllToTableByAttributes($attributes, $condition, $params=array())
	{
		$builder = $this->getCommandBuilder();
		$table = $this->getTableSchema();
		$table_version = $this->getVersionTableSchema();

		$criteria = $builder->createColumnCriteria($table,$attributes,$condition,$params);

		$command = $builder->createInsertFromTableCommand($table_version,$table,$criteria);

		return $command->execute();
	}

	public function save($runValidation=true, $attributes=null, $allow_overriding=false)
	{
		if ($this->version_id) {
			throw new Exception("save() should not be called on versiond model instances.");
		}

		if (!Yii::app()->db->getCurrentTransaction()) {
			$transaction = Yii::app()->db->beginTransaction();
		}

		$this->hash = $this->generateHash();

		if ($this->transaction_id == Yii::app()->db->transaction->id) {
			// Don't create a new version row if save is called again in the same transaction

			$this->noVersion();

			$result = parent::save($runValidation, $attributes, $allow_overriding);
			$this->withVersion();
		} else {
			$this->transaction_id = Yii::app()->db->transaction->id;

			$result = parent::save($runValidation, $attributes, $allow_overriding);
		}

		if (isset($transaction)) {
			try {
				$transaction->commit();
			} catch (Exception $e) {
				$transaction->rollback();
				throw $e;
			}
		}

		return $result;
	}

	/**
	 * Generates a SHA1 hash of all data items in the model excluding created_date, created_user_id, last_modified_date, last_modified_user_id
	 * @return string
	 */
	public function generateHash()
	{
		$attributes = $this->getAttributes();

		unset($attributes['id']);
		unset($attributes['hash']);
		unset($attributes['transaction_id']);
		unset($attributes['last_modified_user_id']);
		unset($attributes['last_modified_date']);
		unset($attributes['created_user_id']);
		unset($attributes['created_date']);

		return sha1(implode('',$attributes));
	}

	public function delete()
	{
		if ($this->version_id) {
			throw new Exception("delete() should not be called on versiond model instances.");
		}

		if (!Yii::app()->db->getCurrentTransaction()) {
			$transaction = Yii::app()->db->beginTransaction();
		}

		$result = parent::delete();

		if (isset($transaction)) {
			try {
				$transaction->commit();
			} catch (Exception $e) {
				$transaction->rollback();
				throw $e;
			}
		}

		return $result;
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
	public function getFullTransactionList($unique_hash=false)
	{
		$transactions = array();

		$model = get_class($this);
		$active = $model::model()->findByPk($this->id);

		$transactions[0] = 'Current: by '.User::model()->findByPk($active->last_modified_user_id)->fullName.' on '.$active->NHSDate('last_modified_date').' at '.substr($active->last_modified_date,11,5);

		foreach ($active->getPreviousVersions($unique_hash, $active->hash) as $previous_version) {
			if ($previous_version->transaction_id) {
				$transactions[$previous_version->transaction_id] = 'Edit by '.User::model()->findByPk($previous_version->last_modified_user_id)->fullName.' on '.$previous_version->NHSDate('last_modified_date').' at '.substr($previous_version->last_modified_date,11,5);
			}
		}

		return $transactions;
	}

	/**
	 * Get the list of transactions for items in the relation
	 */
	public function getFullTransactionListForRelation($relation)
	{
		$transactions = array();
		$ts = array();

		$_relation = $this->getRelationDefinition($relation);
		$transactions = $this->getVersionHistoryForRelation($relation);

		krsort($transactions);

		foreach ($transactions as $i => $transaction) {
			if (!isset($current)) {
				$current = 'Current: '.$transaction;
				unset($transactions[$i]);
			} else {
				$transactions[$i] = 'Edit by '.$transaction;
			}
		}

		return array($current) + $transactions;
	}

	/**
	 * Get a text description of the transaction item
	 */
	public function getTransactionText($user_id, $timestamp)
	{
		return User::model()->findByPk($user_id)->fullName.' on '.Helper::convertMySQL2NHS($timestamp).' at '.substr($timestamp,11,5);
	}

	/**
	 * Remap relations to version table queries
	 */
	public function getRelated($name,$refresh=false,$params=array(),$transaction_id=null)
	{
		if (!$this->isActiveVersion() || $transaction_id) {
			return $this->handleVersionRelation($name, $transaction_id);
		}

		return parent::getRelated($name,$refresh,$params);
	}

	/**
	 * Execute a relation on the current model for a specific transaction ID
	 */
	public function relationByTransactionID($relation_name, $transaction_id)
	{
		return $this->getRelated($relation_name,false,array(),$transaction_id);
	}

	/**
	 * Execute a relation on the current model for a specific transaction ID, or if the transaction ID is null just return the active relation
	 */
	public function relationByTransactionIDOrActive($relation_name, $transaction_id)
	{
		return $transaction_id ? $this->relationByTransactionID($relation_name, $transaction_id) : $this->{$relation_name};
	}

	/**
	 * Gets the definition of the specified model relation
	 */
	private function getRelationDefinition($name)
	{
		$relations = $this->relations();

		if (!isset($relations[$name])) {
			throw new Exception("Relation not found: $name");
		}

		return $relations[$name];
	}

	/**
	 * Gets the base criteria object for a relation query
	 */
	private function getRelationCriteria($relation)
	{
		foreach ($relation as $i => $value) {
			if (!is_int($i) && !in_array($i,array('condition','on','params','order','limit','offset','alias','with'))) {
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
		isset($relation['alias']) && $criteria->alias = $relation['alias'];
		isset($relation['with']) && $criteria->with = $relation['with'];

		$alias = $criteria->alias ? $criteria->alias : 't';

		switch ($relation[0]) {
			case 'CBelongsToRelation':
				$criteria->addCondition($relation[1]::model()->tableSchema->primaryKey.' = :pk');
				$criteria->params[':pk'] = $this->{$relation[2]};
				break;

			case 'CHasOneRelation':
				$criteria->addCondition($relation[2].' = :pk');
				$criteria->params[':pk'] = $this->{$this->tableSchema->primaryKey};
				break;

			case 'CHasManyRelation':
				$criteria->addCondition($relation[2].' = :pk');
				$criteria->params[':pk'] = $this->{$this->tableSchema->primaryKey};
				break;

			case 'CManyManyRelation':
				if (!preg_match('/^(.*?)\((.*?),[\s\t]*(.*?)\)$/',$relation[2],$m)) {
					throw new Exception("Unhandled MANY_MANY relation type: ".print_r($relation,true));
				}

				if ($this->tableHasTransactionID($m[1].'_version', $this->transaction_id)) {
					$criteria->join = "join `{$m[1]}_version` on `{$m[1]}_version`.`{$m[3]}` = `$alias`.`".$relation[1]::model()->tableSchema->primaryKey."` and `{$m[1]}_version`.`{$m[2]}` = :pk and `{$m[1]}_version`.`transaction_id` = :transaction_id";
					$criteria->params[':transaction_id'] = $this->transaction_id;
				} else {
					$criteria->join = "join `{$m[1]}` on `{$m[1]}`.`{$m[3]}` = `$alias`.`".$relation[1]::model()->tableSchema->primaryKey."` and `{$m[1]}`.`{$m[2]}` = :pk";
				}

				$criteria->params[':pk'] = $this->{$this->tableSchema->primaryKey};

				break;
		}

		return $criteria;
	}

	/**
	 * Takes a relation defined on the model and returns a query to derive the same data from the version tables
	 */
	private function handleVersionRelation($name, $transaction_id=null)
	{
		$relation = $this->getRelationDefinition($name);
		$criteria = $this->getRelationCriteria($relation);

		if (!$transaction_id) {
			$transaction_id = $this->transaction_id;
		}

		switch ($relation[0]) {
			case 'CBelongsToRelation':
			case 'CHasOneRelation':
				$related = $relation[1]::model()->find($criteria);

				if (method_exists($related,'getPreviousVersionByTransactionID')) {
					if ($previous_version = $related->getPreviousVersionByTransactionID($this->transaction_id)) {
						return $previous_version;
					}
				}

				return $related;

			case 'CHasManyRelation':
				if ($transaction_id) {
					$alias = $criteria->alias ? $criteria->alias : 't';

					$criteria->addCondition($alias.'.transaction_id <= :transaction_id');
					$criteria->params[':transaction_id'] = $transaction_id;

					$version_criteria = clone $criteria;
					$version_criteria->addCondition($alias.'.deleted_transaction_id is null or '.$alias.'.deleted_transaction_id > :transaction_id');

					return $this->deDupeByID(array_merge(
						$relation[1]::model()->findAll($criteria),
						$relation[1]::model()->fromVersion()->findAll($version_criteria)
					));

				} elseif ($relation[1]::model()->hasTransactionID($transaction_id)) {
					$criteria->addCondition('transaction_id = :transaction_id');
					$criteria->params[':transaction_id'] = $this->transaction_id;

					return $relation[1]::model()->fromVersion()->findAll($criteria);
				}

				return $relation[1]::model()->findAll($criteria);

			case 'CManyManyRelation':
				if ($transaction_id) {
					if (!preg_match('/^(.*?)\((.*?),[\s\t]*(.*?)\)$/',$relation[2],$m)) {
						throw new Exception("Unhandled MANY_MANY relation: ".print_r($relation,true));
					}

					$alias = $criteria->alias ? $criteria->alias : 't';
					$criteria->join = "join `{$m[1]}` on `{$m[1]}`.`{$m[3]}` = `$alias`.`".$relation[1]::model()->tableSchema->primaryKey."` and `{$m[1]}`.`{$m[2]}` = :pk and `{$m[1]}`.`transaction_id` <= :transaction_id";
					$criteria->params[':transaction_id'] = $transaction_id;

					$version_criteria = clone $criteria;
					$version_criteria->join = "join `{$m[1]}_version` on `{$m[1]}_version`.`{$m[3]}` = `$alias`.`".$relation[1]::model()->tableSchema->primaryKey."` and `{$m[1]}_version`.`{$m[2]}` = :pk and (`{$m[1]}_version`.`transaction_id` is null or `{$m[1]}_version`.transaction_id > :transaction_id )";

					return $this->deDupeByID(array_merge(
						$relation[1]::model()->findAll($criteria),
						$relation[1]::model()->fromVersion()->findAll($version_criteria)
					));
				}

				return $relation[1]::model()->findAll($criteria);

			default:
				throw new Exception("Unhandled relation type: ".$relation[0]);
		}

		return parent::getRelated($name);
	}

	/**
	 * Get all version items for a given relation
	 */
	public function getVersionHistoryForRelation($relation_name)
	{
		$relation = $this->getRelationDefinition($relation_name);
		$criteria = $this->getRelationCriteria($relation);

		$transactions = array();

		switch ($relation[0]) {
			case 'CHasOneRelation':
			case 'CBelongsToRelation':
				if ($item = $this->{$relation_name}) {
					$transactions[$item->transaction_id] = $this->getTransactionText($item->last_modified_user_id,$item->last_modified_date);
				}

				foreach ($relation[1]::model()->fromVersion()->findAll($criteria) as $item) {
					if (!isset($transactions[$item->transaction_id])) {
						$transactions[$item->transaction_id] = $this->getTransactionText($item->last_modified_user_id,$item->last_modified_date);
					}
					if (!is_null($item->deleted_transaction_id) && !isset($transactions[$item->deleted_transaction_id])) {
						$transactions[$item->deleted_transaction_id] = $this->getTransactionText($item->last_modified_user_id,$item->version_date);
					}
				}

				break;

			case 'CHasManyRelation':
				foreach ($this->{$relation_name} as $item) {
					if (!isset($transactions[$item->transaction_id])) {
						$transactions[$item->transaction_id] = $this->getTransactionText($item->last_modified_user_id,$item->last_modified_date);
					}
				}

				foreach ($relation[1]::model()->fromVersion()->findAll($criteria) as $item) {
					if (!isset($transactions[$item->transaction_id])) {
						$transactions[$item->transaction_id] = $this->getTransactionText($item->last_modified_user_id,$item->last_modified_date);
					}
					if (!is_null($item->deleted_transaction_id) && !isset($transactions[$item->deleted_transaction_id])) {
						$transactions[$item->deleted_transaction_id] = $this->getTransactionText($item->last_modified_user_id,$item->version_date);
					}
				}

				break;

			case 'CManyManyRelation':
				if (!preg_match('/^(.*?)\((.*?),[\s\t]*(.*?)\)$/',$relation[2],$m)) {
					throw new Exception("Unhandled MANY_MANY relation: ".print_r($relation,true));
				}

				$_table = Yii::app()->db->getSchema()->getTable($this->tableName());

				foreach (Yii::app()->db->createCommand()->select("*")->from($m[1])->where("{$m[2]} = :pk",array(":pk" => $this->{$_table->primaryKey}))->queryAll() as $row) {
					if (!isset($transactions[$row['transaction_id']])) {
						$transactions[$row['transaction_id']] = $this->getTransactionText($row['last_modified_user_id'],$row['last_modified_date']);
					}
				}

				foreach (Yii::app()->db->createCommand()->select("*")->from($m[1].'_version')->where("{$m[2]} = :pk",array(":pk" => $this->{$_table->primaryKey}))->queryAll() as $row) {
					if (!isset($transactions[$row['transaction_id']])) {
						$transactions[$row['transaction_id']] = $this->getTransactionText($row['last_modified_user_id'],$row['last_modified_date']);
					}

					if (!is_null($row['deleted_transaction_id']) && !isset($transactions[$row['deleted_transaction_id']])) {
						$transactions[$row['deleted_transaction_id']] = $this->getTransactionText($row['last_modified_user_id'],$row['version_date']);
					}
				}

				break;
		}

		return $transactions;
	}

	/**
	 *
	 */
	public function deDupeByID($items)
	{
		$return = array();

		foreach ($items as $item) {
			if (!isset($return[$item->id])) {
				$return[$item->id] = $item;
			} else {
				if ($item->transaction_id > $return[$item->id]->transaction_id) {
					$return[$item->id] = $item;
				}
			}
		}

		return array_values($return);
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
