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

class OETransaction
{
	public $id;
	public $pdo_transaction;
	public $oe_transaction;
	public $conflicted_with_transaction_id = null;
	public $conflict_resolved_transaction_id = null;
	public $append_to_conflict = null;

	public function __construct($pdo_transaction, $operation_name=null, $object_name=null, $patient_id=null)
	{
		$this->pdo_transaction = $pdo_transaction;
		$this->oe_transaction = new Transaction;
		$this->oe_transaction->patient_id = $patient_id;

		if (!$this->oe_transaction->save()) {
			throw new Exception("Unable to save Transaction: ".print_r($this->oe_transaction->getErrors(),true));
		}

		$this->id = $this->oe_transaction->id;

		$operation_name && $this->oe_transaction->setOperation($operation_name);
		$object_name && $this->oe_transaction->setObject($object_name);
	}

	public function commit()
	{
		if (!$this->oe_transaction->operation) {
			$this->rollback();

			throw new Exception("Transaction has no operation set and so cannot be committed.");
		}
		if (!$this->oe_transaction->object) {
			$this->rollback();

			throw new Exception("Transaction has no object set and so cannot be committed.");
		}
		if (count($this->oe_transaction->tables) <1) {
			$this->rollback();

			throw new Exception("Transaction has no table assignments and so cannot be committed.");
		}

		foreach ($this->oe_transaction->tables as $table) {
			if (!$_table = TransactionTable::model()->find('name=?',array($table))) {
				$_table = new TransactionTable;
				$_table->name = $table;

				if (!$_table->save()) {
					$this->rollback();

					throw new Exception("Unable to save TransactionTable: ".print_r($_table->getErrors(),true));
				}
			}

			$tta = new TransactionTableAssignment;
			$tta->transaction_id = $this->oe_transaction->id;
			$tta->table_id = $_table->id;
			$tta->display_order = $i + 1;

			if (!$tta->save()) {
				$this->rollback();

				throw new Exception("Unable to save TransactionTableAssignment: ".print_r($tta->getErrors(),true));
			}
		}

		// Handle conflict
		if ($this->conflicted_with_transaction_id) {
			if ($this->append_to_conflict) {
				$conflict = $this->append_to_conflict;
			} else {
				$conflict = new Conflict;

				if (!$conflict->save()) {
					$this->rollback();

					throw new Exception("Unable to save conflict: ".print_r($conflict->getErrors(),true));
				}
			}

			foreach ($this->oe_transaction->tables as $table) {
				foreach (array($table,$table.'_version') as $_table) {
					if (Yii::app()->db->getSchema()->getTable($_table)) {
						foreach (Yii::app()->db->createCommand()
							->select("transaction_id")
							->from($_table)
							->where("transaction_id >= :transaction_from and transaction_id < :transaction_to",array(
								":transaction_from" => $this->conflicted_with_transaction_id,
								":transaction_to" => $this->oe_transaction->id,
							))
							->queryAll() as $row) {

							$conflict->addTransactionID($row['transaction_id']);
						}
					}
				}
			}

			$conflict->addTransactionID($this->oe_transaction->id);
		}

		// Handle resolution of conflict
		if ($this->conflict_resolved_transaction_id) {
			$conflict = Transaction::model()->findByPk($this->conflict_resolved_transaction_id)->conflict;
			$conflict->resolved_transaction_id = $this->oe_transaction->id;

			if (!$conflict->save()) {
				throw new Exception("Unable to mark conflict resolved: ".print_r($conflict->getErrors(),true));
			}
		}

		$result = $this->pdo_transaction->commit();

		Yii::app()->db->clearTransaction();

		return $result;
	}

	public function rollback()
	{
		$result = $this->pdo_transaction->rollback();

		Yii::app()->db->clearTransaction();

		return $result;
	}

	public function setOperation($operation_name)
	{
		return $this->oe_transaction->setOperation($operation_name);
	}

	public function setObject($object_name)
	{
		return $this->oe_transaction->setObject($object_name);
	}

	public function addTable($table_name)
	{
		return $this->oe_transaction->addTable($table_name);
	}

	/**
	 * Mark the transaction and associated saved rows as conflicted with the specified $transaction_id and any other transactions that have occured
	 * since $transaction_id and this transaction's id
	 */
	public function raiseConflict($transaction_id)
	{
		if ($transaction_id != $this->conflict_resolved_transaction_id) {
			$this->conflicted_with_transaction_id = $transaction_id;
		}
	}

	/**
	 * Append transactions to an existing conflict
	 */
	public function addToConflict($conflict, $transaction_id)
	{
		$this->append_to_conflict = $conflict;

		$this->raiseConflict($transaction_id);
	}

	/**
	 * Indicates that the conflict related to the passed transaction_id is being resolved with this commit
	 */
	public function resolveConflict($transaction_id)
	{
		$this->conflict_resolved_transaction_id = $transaction_id;

		if ($transaction_id == $this->conflicted_with_transaction_id) {
			$this->conflicted_with_transaction_id = null;
		}
	}
}
