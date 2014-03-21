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
	public $based_on_transaction_id = null;
	private $resolves_conflict_based_on_transaction_id = null;
	public $was_deleted;

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
	 * Method to indicate prior to a save() that the edit being made is based on a given transaction ID
	 * If at the point of saving the transaction ID on the locked object is different we raise a conflict
	 */
	public function basedOnTransactionID($transaction_id)
	{
		$this->based_on_transaction_id = $transaction_id;

		return $this;
	}

	/**
	 * Indicate that the current transaction will resolve the conflict with the specified transaction_id
	 */
	public function resolvesConflictWithTransactionID($transaction_id)
	{
		$this->resolves_conflict_based_on_transaction_id = $transaction_id;

		return $this;
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

	public function updateByPk($pk,$attributes,$condition='',$params=array())
	{
		if (Yii::app()->params['enable_transactions']) {
			if (!$transaction = Yii::app()->db->getCurrentTransaction()) {
				throw new Exception("updateByPk() called without a transaction");
			}

			$transaction->addTable($this->tableName());
		}

		if (!$this->enable_version || $this->versionToTableByPk($pk, $condition, $params)) {
			return parent::updateByPk($pk,$attributes,$condition,$params);
		}

		throw new Exception("versionToTableByPk() failed");
	}

	public function updateAll($attributes,$condition='',$params=array())
	{
		if (Yii::app()->params['enable_transactions']) {
			if (!$transaction = Yii::app()->db->getCurrentTransaction()) {
				throw new Exception("updateAll() called without a transaction");
			}

			$transaction->addTable($this->tableName());
		}

		if (!$this->enable_version || $this->versionAllToTable($condition, $params)) {
			return parent::updateAll($attributes,$condition,$params);
		}

		throw new Exception("versionAllToTable() failed");
	}

	public function deleteByPk($pk,$condition='',$params=array())
	{
		if (Yii::app()->params['enable_transactions']) {
			if (!$transaction = Yii::app()->db->getCurrentTransaction()) {
				throw new Exception("deleteByPk() called without a transaction");
			}

			$transaction->addTable($this->tableName());
		}

		return parent::deleteByPk($pk,$condition,$params);
	}

	public function deleteAll($condition='',$params=array())
	{
		if (Yii::app()->params['enable_transactions']) {
			if (!$transaction = Yii::app()->db->getCurrentTransaction()) {
				throw new Exception("deleteAll() called without a transaction");
			}

			$transaction->addTable($this->tableName());
		}

		return parent::deleteAll($condition,$params);
	}

	public function deleteAllByAttributes($attributes,$condition='',$params=array())
	{
		if (Yii::app()->params['enable_transactions']) {
			if (!$transaction = Yii::app()->db->getCurrentTransaction()) {
				throw new Exception("deleteAllByAttributes() called without a transaction");
			}

			$transaction->addTable($this->tableName());
		}

		return parent::deleteAllByAttributes($attributes,$condition,$params);
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
		if (!$this->isActiveVersion()) {
			throw new Exception("save() should not be called on versiond model instances.");
		}

		if (Yii::app()->params['enable_transactions']) {
			if (!$transaction = Yii::app()->db->getCurrentTransaction()) {
				throw new Exception("save() called without a transaction");
			}

			$transaction->addTable($this->tableName());
		}

		$this->hash = $this->generateHash();

		$this->handleConflictDetectionAndResolution('save',$transaction);

		if ($this->transaction_id == $transaction->id) {
			// Don't create a new version row if save is called again on the same object in the same transaction

			$this->noVersion();

			$result = parent::save($runValidation, $attributes, $allow_overriding);

			$this->withVersion();

		} else {
			$this->transaction_id = $transaction->id;

			$result = parent::save($runValidation, $attributes, $allow_overriding);
		}

		return $result;
	}

	public function handleConflictDetectionAndResolution($operation, $transaction)
	{
		// Patient-associated data is handled differently
		if ($patient = (get_class($this) == 'Patient') ? $this : $this->patient) {
			if ($this->based_on_transaction_id && $patient->latestTransaction->id != $this->based_on_transaction_id) {
				if (method_exists($this,'detectConflictForRow')) {
					$conflict_transactions = $this->detectTransactionConflicts($operation, Transaction::model()->findAllBetween($this->based_on_transaction_id+1, $patient->latestTransaction->id));
				} else {
					// Do any of the new transactions touch this model
					$conflict_transactions = Transaction::model()->searchForModel($this, $this->based_on_transaction_id+1, $patient->latestTransaction->id, true);
				}

				if (!empty($conflict_transactions)) {
					if (!$conflict = $this->unresolvedConflict) {
						$conflict_transaction = array_shift($conflict_transactions);

						$conflict = $transaction->raiseConflict($conflict_transaction->id);
					}

					foreach ($conflict_transactions as $conflict_transaction) {
						$transaction->addToConflict($conflict, $conflict_transaction->id);
					}
				} else if ($this->unresolvedConflict) {
					if ($this->resolves_conflict_based_on_transaction_id) {
						$transaction->resolveConflict($this->unresolvedConflict, $this->resolves_conflict_based_on_transaction_id);
					} else {
						$transaction->addToConflict($this->unresolvedConflict, $this->transaction_id);
					}
				}
			} else if ($this->unresolvedConflict) {
				if ($this->resolves_conflict_based_on_transaction_id) {
					$transaction->resolveConflict($this->unresolvedConflict, $this->resolves_conflict_based_on_transaction_id);
				} else {
					$transaction->addToConflict($this->unresolvedConflict, $this->transaction_id);
				}
			}
		} else {
			if ($this->based_on_transaction_id && $this->transaction_id != $this->based_on_transaction_id) {
				// Object was changed while being edited, the save POST'd in the form was based on a version prior to the current latest version
				// so this new transaction is now in conflict with any transactions made since $this->based_on_transaction_id which is the transaction_id
				// the edit was based on.
				if ($this->unresolvedConflict) {
					// Object is already in a conflicted state so we simply append this transaction to the conflict
					$transaction->addToConflict($this->unresolvedConflict, $this->transaction_id);
				} else {
					// Raise a new conflict
					$transaction->raiseConflict($this->transaction_id);
				}
			} else if ($this->unresolvedConflict) {
				if ($this->resolves_conflict_based_on_transaction_id) {
					// Object was not changed while being edited and the user specified that they are resolving the conflict so we mark the conflict as resolved
					$transaction->resolveConflict($this->resolves_conflict_based_on_transaction_id);
				} else {
					// Object is conflicted and the user did not explicitly mark it resolved with the POST'd edit so we add this transaction to the conflict
					$transaction->addToConflict($this->unresolvedConflict, $this->transaction_id);
				}
			}
		}
	}

	public function detectTransactionConflicts($operation, $transactions)
	{
		$conflicted_transactions = array();

		foreach ($transactions as $transaction) {
			if ($transaction->model_class->name == get_class($this)) {
				foreach ($this->getAllRowsInTableForTransactionID($this->tableName(),$transaction->id) as $row) {
					if ($this->detectConflictForRow($operation, $row, @$row['deleted_transaction_id'] == $transaction->id)) {
						$conflicted_transactions[] = $transaction;
					}
				}
			}
		}

		return $conflicted_transactions;
	}

	public function getTransaction()
	{
		return Transaction::model()->findByPk($this->transaction_id);
	}

	public function getConflict()
	{
		if ($transaction = $this->transaction) {
			return $transaction->conflict;
		}

		return false;
	}

	public function getUnresolvedConflict()
	{
		if ($transaction = $this->transaction) {
			return $transaction->unresolvedConflict;
		}

		return false;
	}

	/**
	 * Resolves true if this object's related transaction attempted to resolve a conflict (regardless of whether it was successful)
	 */
	public function getResolvedConflict()
	{
		return $this->transaction_id && Conflict::model()->find('resolved_transaction_id=?',array($this->transaction_id));
	}

	public function delete()
	{
		if ($this->version_id) {
			throw new Exception("delete() should not be called on versiond model instances.");
		}

		if (Yii::app()->params['enable_transactions']) {
			if (!$transaction = Yii::app()->db->getCurrentTransaction()) {
				throw new Exception("delete() called without a transaction");
			}

			$transaction->addTable($this->tableName());
		}

		$this->handleConflictDetectionAndResolution('delete',$transaction);

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
	public function getFullTransactionList($unique_hash=false)
	{
		$transactions = array();

		$model = get_class($this);
		$active = $model::model()->findByPk($this->id);

		$transactions[0] = 'Current: by '.$this->getTransactionText($active->last_modified_user_id,$active->last_modified_date);

		if ($this->conflict) {
			$transactions[0] .= ' [C]';
		}

		if ($this->resolvedConflict) {
			$transactions[0] .= ' [R]';
		}

		foreach ($active->getPreviousVersions($unique_hash, $active->hash) as $previous_version) {
			if ($previous_version->transaction_id) {
				$transactions[$previous_version->transaction_id] = 'Edit by '.$this->getTransactionText($previous_version->last_modified_user_id,$previous_version->last_modified_date);

				if ($previous_version->conflict) {
					$transactions[$previous_version->transaction_id] .= ' [C]';
				}

				if ($previous_version->resolvedConflict) {
					$transactions[$previous_version->transaction_id] .= ' [R]';
				}
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

	public function getLatestTransactionIDForRelation($relation)
	{
		$transactions = $this->getVersionHistoryForRelation($relation);
		krsort($transactions);

		if (!empty($transactions)) {
			foreach ($transactions as $transaction_id => $description) {
				return $transaction_id;
			}
		}

		return null;
	}

	public function getUnresolvedConflictForRelation($relation)
	{
		$transactions = $this->getVersionHistoryForRelation($relation);
		krsort($transactions);

		if (!empty($transactions)) {
			foreach ($transactions as $transaction_id => $description) {
				return Transaction::model()->findByPk($transaction_id)->unresolvedConflict;
			}
		}

		return null;
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
	public function getRelated($relation_name,$refresh=false,$params=array(),$transaction_id=null,$touched_by_transaction_id=null)
	{
		if ($this->isActiveVersion() && !$transaction_id && !$touched_by_transaction_id) {
			return parent::getRelated($relation_name,$refresh,$params);
		}

		$relation = $this->getRelationDefinition($relation_name);
		$criteria = $this->getRelationCriteria($relation_name, $relation);

		!$transaction_id && $transaction_id = $this->transaction_id;

		if (!method_exists($this, 'getRelated_'.$relation[0])) {
			throw new Exception("Unhandled relation type: ".$relation[0]);
		}

		return $this->{'getRelated_'.$relation[0]}($relation, $criteria, $transaction_id, $touched_by_transaction_id);
	}

	public function getRelated_CBelongsToRelation($relation, $criteria, $transaction_id, $touched_by_transaction_id)
	{
		$related = $relation[1]::model()->find($criteria);

		if (method_exists($related,'getPreviousVersionByTransactionID')) {
			if ($previous_version = $related->getPreviousVersionByTransactionID($transaction_id)) {
				return $previous_version;
			}
		}

		return $related;
	}

	public function getRelated_CHasOneRelation($relation, $criteria, $transaction_id, $touched_by_transaction_id)
	{
		return $this->getRelated_CBelongsToRelation($relation, $criteria, $transaction_id, $touched_by_transaction_id);
	}

	public function getRelated_CHasManyRelation($relation, $criteria, $transaction_id, $touched_by_transaction_id)
	{
		if ($touched_by_transaction_id) {
			$criteria->params[':transaction_id'] = $touched_by_transaction_id;

			$deleted_criteria = clone $criteria;

			$criteria->addCondition($criteria->alias.'.transaction_id = :transaction_id');

			$version_criteria = clone $criteria;

			$deleted_criteria->addCondition($criteria->alias.'.deleted_transaction_id = :transaction_id');

			$results = $this->deDupeByID(array_merge(
				$relation[1]::model()->findAll($criteria),
				$relation[1]::model()->fromVersion()->findAll($version_criteria)
			));

			foreach ($relation[1]::model()->fromVersion()->findAll($deleted_criteria) as $deleted) {
				$deleted->was_deleted = true;

				$results[] = $deleted;
			}

			return $results;
		}

		if ($transaction_id) {
			$criteria->addCondition($criteria->alias.'.transaction_id <= :transaction_id');
			$criteria->params[':transaction_id'] = $transaction_id;

			$version_criteria = clone $criteria;
			$version_criteria->addCondition($criteria->alias.'.deleted_transaction_id is null or '.$criteria->alias.'.deleted_transaction_id > :transaction_id');

			return $this->deDupeByID(array_merge(
				$relation[1]::model()->findAll($criteria),
				$relation[1]::model()->fromVersion()->findAll($version_criteria)
			));
		}

		return $relation[1]::model()->findAll($criteria);
	}

	public function getRelated_CManyManyRelation($relation, $criteria, $transaction_id, $touched_by_transaction_id)
	{
		if (!preg_match('/^(.*?)\((.*?),[\s\t]*(.*?)\)$/',$relation[2],$m)) {
			throw new Exception("Unhandled MANY_MANY relation: ".print_r($relation,true));
		}

		if ($touched_by_transaction_id) {
			$criteria->join = "join `{$m[1]}` on `{$m[1]}`.`{$m[3]}` = `$criteria->alias`.`".$relation[1]::model()->tableSchema->primaryKey."` and `{$m[1]}`.`{$m[2]}` = :pk and `{$m[1]}`.`transaction_id` = :transaction_id";
			$criteria->params[':transaction_id'] = $touched_by_transaction_id;
			$criteria->params[':pk'] = $this->{$this->tableSchema->primaryKey};

			$version_criteria = clone $criteria;
			$version_criteria->join = "join `{$m[1]}_version` on `{$m[1]}_version`.`{$m[3]}` = `$criteria->alias`.`".$relation[1]::model()->tableSchema->primaryKey."` and `{$m[1]}_version`.`{$m[2]}` = :pk and (`{$m[1]}_version`.`transaction_id` = :transaction_id or `{$m[1]}_version`.`deleted_transaction_id` = :transaction_id)";

			return $this->deDupeByID(array_merge(
				$relation[1]::model()->findAll($criteria),
				$relation[1]::model()->findAll($version_criteria)
			));
		}

		if ($transaction_id) {
			$criteria->join = "join `{$m[1]}` on `{$m[1]}`.`{$m[3]}` = `$criteria->alias`.`".$relation[1]::model()->tableSchema->primaryKey."` and `{$m[1]}`.`{$m[2]}` = :pk and `{$m[1]}`.`transaction_id` <= :transaction_id";
			$criteria->params[':transaction_id'] = $transaction_id;
			$criteria->params[':pk'] = $this->{$this->tableSchema->primaryKey};

			$version_criteria = clone $criteria;
			$version_criteria->join = "join `{$m[1]}_version` on `{$m[1]}_version`.`{$m[3]}` = `$criteria->alias`.`".$relation[1]::model()->tableSchema->primaryKey."` and `{$m[1]}_version`.`{$m[2]}` = :pk and `{$m[1]}_version`.`transaction_id` <= :transaction_id and (`{$m[1]}_version`.`deleted_transaction_id` is null or `{$m[1]}_version`.deleted_transaction_id > :transaction_id )";

			return $this->deDupeByID(array_merge(
				$relation[1]::model()->findAll($criteria),
				$relation[1]::model()->findAll($version_criteria)
			));
		}

		$criteria->join = "join `{$m[1]}` on `{$m[1]}`.`{$m[3]}` = `$criteria->alias`.`".$relation[1]::model()->tableSchema->primaryKey."` and `{$m[1]}`.`{$m[2]}` = :pk";
		$criteria->params[':pk'] = $this->{$this->tableSchema->primaryKey};

		return $relation[1]::model()->findAll($criteria);
	}

	/**
	 * Execute a relation on the current model for a specific transaction ID
	 * Returns the contents of the relation at the point of the transaction (including changes made by the transaction)
	 */
	public function relationAsOfTransactionID($relation_name, $transaction_id)
	{
		return $this->getRelated($relation_name,false,array(),$transaction_id);
	}

	/**
	 * Execute a relation on the current model for a specific transaction ID, or if the transaction ID is null just return the active relation
	 */
	public function relationAsOfTransactionIDOrActive($relation_name, $transaction_id)
	{
		return $transaction_id ? $this->relationAsOfTransactionID($relation_name, $transaction_id) : $this->{$relation_name};
	}

	/**
	 * Return the related items that were changed by the transaction, with a reference to what the change was (eg add/edit/delete)
	 */
	public function relationChangedItemsAsOfTransactionID($relation_name, $transaction_id)
	{
		return $this->getRelated($relation_name,false,array(),null,$transaction_id);
	}

	/**
	 * Gets the definition of the specified model relation
	 */
	public function getRelationDefinition($name)
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
	public function getRelationCriteria($relation_name, $relation)
	{
		foreach ($relation as $i => $value) {
			if (!is_int($i) && !in_array($i,array('condition','on','params','order','limit','offset','alias','with'))) {
				throw new Exception("Unhandled relation property: $i");
			}
		}

		if (!method_exists($this, 'setRelationCriteria_'.$relation[0])) {
			throw new Exception("Unknown relation type: {$relation[0]}");
		}

		$criteria = new CDbCriteria;
		$criteria->alias = $relation_name;

		isset($relation['condition']) && $criteria->addCondition($relation['condition']);
		isset($relation['params']) && $criteria->params = $relation['params'];
		isset($relation['on']) && $criteria->addCondition($relation['on']);
		isset($relation['order']) && $criteria->order = $relation['order'];
		isset($relation['limit']) && $criteria->limit = $relation['limit'];
		isset($relation['offset']) && $criteria->offset = $relation['offset'];
		isset($relation['alias']) && $criteria->alias = $relation['alias'];
		isset($relation['with']) && $criteria->with = $relation['with'];

		return $this->{'setRelationCriteria_'.$relation[0]}($criteria, $relation);
	}

	public function setRelationCriteria_CBelongsToRelation($criteria, $relation)
	{
		$criteria->addCondition($relation[1]::model()->tableSchema->primaryKey.' = :pk');
		$criteria->params[':pk'] = $this->{$relation[2]};

		return $criteria;
	}

	public function setRelationCriteria_CHasOneRelation($criteria, $relation)
	{
		$criteria->addCondition($relation[2].' = :pk');
		$criteria->params[':pk'] = $this->{$this->tableSchema->primaryKey};

		return $criteria;
	}

	public function setRelationCriteria_CHasManyRelation($criteria, $relation)
	{
		return $this->setRelationCriteria_CHasOneRelation($criteria, $relation);
	}

	public function setRelationCriteria_CManyManyRelation($criteria, $relation)
	{
		if (!preg_match('/^(.*?)\((.*?),[\s\t]*(.*?)\)$/',$relation[2],$m)) {
			throw new Exception("Unhandled MANY_MANY relation type: ".print_r($relation,true));
		}

		if (!$this->isActiveVersion() && $this->tableHasTransactionID($m[1].'_version', $this->transaction_id)) {
			$criteria->join = "join `{$m[1]}_version` on `{$m[1]}_version`.`{$m[3]}` = `$criteria->alias`.`".$relation[1]::model()->tableSchema->primaryKey."` and `{$m[1]}_version`.`{$m[2]}` = :pk and `{$m[1]}_version`.`transaction_id` = :transaction_id";
			$criteria->params[':transaction_id'] = $this->transaction_id;
		} else {
			$criteria->join = "join `{$m[1]}` on `{$m[1]}`.`{$m[3]}` = `$criteria->alias`.`".$relation[1]::model()->tableSchema->primaryKey."` and `{$m[1]}`.`{$m[2]}` = :pk";
		}

		$criteria->params[':pk'] = $this->{$this->tableSchema->primaryKey};

		return $criteria;
	}

	/**
	 * Get all version items for a given relation
	 */
	public function getVersionHistoryForRelation($relation_name)
	{
		$relation = $this->getRelationDefinition($relation_name);

		if (!method_exists($this, 'getVersionHistoryForRelation_'.$relation[0])) {
			throw new Exception("Unhandled relation type: ".$relation[0]);
		}

		return $this->{'getVersionHistoryForRelation_'.$relation[0]}($relation_name, $relation);
	}

	public function getVersionHistoryForRelation_CBelongsToRelation($relation_name, $relation)
	{
		$criteria = $this->getRelationCriteria($relation_name, $relation);

		$transactions = array();

		$items = $this->{$relation_name} ? array($this->{$relation_name}) : array();

		foreach ($items as $item) {
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

		return $transactions;
	}

	public function getVersionHistoryForRelation_CHasOneRelation($relation_name, $relation)
	{
		return $this->getVersionHistoryForRelation_CBelongsToRelation($relation_name, $relation);
	}

	public function getVersionHistoryForRelation_CHasManyRelation($relation_name, $relation)
	{
		$criteria = $this->getRelationCriteria($relation_name, $relation);

		$transactions = array();

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

		return $transactions;
	}

	public function getVersionHistoryForRelation_CManyManyRelation($relation_name, $relation)
	{
		$transactions = array();

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

	public function getPatient()
	{
		if (isset($this->event)) return $this->event->episode->patient;
		if (isset($this->episode)) return $this->episode->patient;
		if (isset($this->patient)) return $this->patient;

		return null;
	}

	public function beginTransaction($operation_name)
	{
		$transaction = Yii::app()->db->beginTransaction($operation_name, ($this->patient ? $this->patient->id : null));
		$transaction->setModel($this);

		return $transaction;
	}

	public function getAllRowsInTableForTransactionID($table,$transaction_id)
	{
		return Yii::app()->db->createCommand()
			->select("*")
			->from($table)
			->where("transaction_id = :transaction_id",array(
				":transaction_id" => $transaction_id
			))
			->queryAll() +
			Yii::app()->db->createCommand()
				->select("*")
				->from($table."_version")
				->where("transaction_id = :transaction_id",array(
				":transaction_id" => $transaction_id
			))
			->queryAll() + 
			Yii::app()->db->createCommand()
				->select("*")
				->from($table."_version")
				->where("deleted_transaction_id = :transaction_id",array(
				":transaction_id" => $transaction_id
			))
			->queryAll();
	}
}
