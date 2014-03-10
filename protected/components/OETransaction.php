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

	public function __construct($pdo_transaction, $operation_name=null, $object_name=null)
	{
		$this->pdo_transaction = $pdo_transaction;
		$this->oe_transaction = new Transaction;

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
			throw new Exception("Transaction has no operation set and so cannot be committed.");
		}
		if (!$this->oe_transaction->object) {
			throw new Exception("Transaction has no object set and so cannot be committed.");
		}
		if (count($this->oe_transaction->tables) <1) {
			throw new Exception("Transaction has no table assignments and so cannot be committed.");
		}

		foreach ($this->oe_transaction->tables as $i => $table) {
			if (!$_table = TransactionTable::model()->find('name=?',array($table))) {
				$_table = new TransactionTable;
				$_table->name = $table;

				if (!$_table->save()) {
					throw new Exception("Unable to save TransactionTable: ".print_r($_table->getErrors(),true));
				}
			}

			$tta = new TransactionTableAssignment;
			$tta->transaction_id = $this->oe_transaction->id;
			$tta->table_id = $_table->id;
			$tta->display_order = $i + 1;

			if (!$tta->save()) {
				throw new Exception("Unable to save TransactionTableAssignment: ".print_r($tta->getErrors(),true));
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
}
