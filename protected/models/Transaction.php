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

/**
 * This is the model class for table "transaction".
 *
 * The followings are the available columns in table 'transaction':
 * @property integer $id
 */
class Transaction extends BaseActiveRecord
{
	public $tables = array();

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'transaction';
	}

	/**
	* @return array relational rules.
	*/
	public function relations()
	{
		return array(
			'operation' => array(self::BELONGS_TO, 'TransactionOperation', 'operation_id'),
			'object' => array(self::BELONGS_TO, 'TransactionObject', 'object_id'),
			'table_assignments' => array(self::HAS_MANY, 'TransactionTableAssignment', 'transaction_id', 'order' => 'display_order asc'),
		);
	}

	/**
	 * Add the specified table to the transaction if it's not already associated
	 */
	public function addTable($table)
	{
		if (!in_array($table,$this->tables)) {
			$this->tables[] = $table;
		}
	}

	/**
	 * Set the operation of the transaction (eg create/update/delete/etc)
	 */
	public function setOperation($operation_name)
	{
		if (!$operation = TransactionOperation::model()->find('name=?',array($operation_name))) {
			$operation = new TransactionOperation;
			$operation->name = $operation_name;

			if (!$operation->save()) {
				throw new Exception("Unable to save TransactionOperation: ".print_r($operation->getErrors(),true));
			}
		}

		$this->operation_id = $operation->id;

		if (!$this->save()) {
			throw new Exception("Unable to save transaction: ".print_r($this->getErrors(),true));
		}

		$this->operation = $operation;
	}

	/**
	 * Set the object of the transaction (eg event, diagnosis etc)
	 */
	public function setObject($object_name)
	{
		if (!$object = TransactionObject::model()->find('name=?',array($object_name))) {
			$object = new TransactionObject;
			$object->name = $object_name;

			if (!$object->save()) {
				throw new Exception("Unable to save TransactionObject: ".print_r($object->getErrors(),true));
			}
		}

		$this->object_id = $object->id;

		if (!$this->save()) {
			throw new Exception("Unable to save transaction: ".print_r($this->getErrors(),true));
		}

		$this->object = $object;
	}
}
