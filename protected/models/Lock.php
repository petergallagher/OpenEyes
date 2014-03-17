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
 * This is the model class for table "lock".
 *
 * The followings are the available columns in table 'lock':
 * @property integer $id
 * @property integer $item_id
 * @property string $item_table
 */
class Lock extends BaseActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Issue the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'lock';
	}

	/**
	 * Automatically release the lock when all memory references are gone
	 * We don't need a transaction for this as the locks are local (non-synced) and exclusive
	 */
	public function __destruct()
	{
		$this->release();
	}

	/**
	 * Release the lock
	 */
	public function release()
	{
		if (!$this->getIsNewRecord()) {
			$transactions = Yii::app()->params['enable_transactions'];
			Yii::app()->params['enable_transactions'] = false;

			$this->delete();

			Yii::app()->params['enable_transactions'] = $transactions;
		}
	}

	static public function obtain($table, $id, $block=true, $timeout=null, $request_timestamp=null)
	{
		if (is_null($timeout)) {
			$timeout = Yii::app()->params['lock_wait_timeout'];
		}
		if (is_null($request_timestamp)) {
			$request_timestamp = time();
		}

		Yii::app()->db->createCommand("delete from `lock` where item_table = :table and item_id = :id and last_modified_date <= :timedout")
			->bindValue(':table',$table)
			->bindValue(':id',$id)
			->bindValue(':timedout',date('Y-m-d H:i:s',time()-Yii::app()->params['lock_expiry']))
			->query();

		if ($lock = Lock::model()->find('item_table=? and item_id=?',array($table,$id))) {
			if (!$block) {
				return false;
			}

			if ((time() - $request_timestamp) >= $timeout) {
				return false;
			}

			sleep(1);

			return Lock::obtain($table, $id, $block, $timeout, $request_timestamp);
		}

		$lock = new Lock;
		$lock->item_table = $table;
		$lock->item_id = $id;

		try {
			$lock->save();
		} catch (Exception $e) {
			if (preg_match('/Duplicate entry/',$e->getMessage())) {
				if (!$block) {
					return false;
				}

				if ((time() - $request_timestamp) >= $timeout) {
					return false;
				}

				sleep(1);

				return Lock::obtain($table, $id, $block, $timeout, $request_timestamp);
			}
		}

		return $lock;
	}
}
