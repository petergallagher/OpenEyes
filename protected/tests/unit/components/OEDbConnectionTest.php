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
class OEDbConnectionTest extends CDbTestCase
{
	public function testBeginTransaction_Enabled()
	{
		$transactions = Yii::app()->params['enable_transactions'];
		Yii::app()->params['enable_transactions'] = true;

		$transaction = Yii::app()->db->beginTransaction('Something');

		$this->assertInstanceOf('OETransaction', Yii::app()->db->getCurrentTransaction());

		// Cleanup
		$transaction->rollback();
		Yii::app()->params['enable_transactions'] = $transactions;
	}

	public function testBeginTransaction_Disabled()
	{
		$transactions = Yii::app()->params['enable_transactions'];
		Yii::app()->params['enable_transactions'] = false;

		$transaction = Yii::app()->db->beginTransaction('Something');

		$this->assertInstanceOf('OETransactionStub', Yii::app()->db->getCurrentTransaction());

		// Cleanup
		Yii::app()->params['enable_transactions'] = $transactions;
	}

	public function testClearTransaction()
	{
		$transactions = Yii::app()->params['enable_transactions'];
		Yii::app()->params['enable_transactions'] = true;

		$transaction = Yii::app()->db->beginTransaction('Something');

		$this->assertInstanceOf('OETransaction', Yii::app()->db->getCurrentTransaction());

		Yii::app()->db->clearTransaction();

		$this->assertNull(Yii::app()->db->getCurrentTransaction());

		// Cleanup
		$transaction->rollback();
		Yii::app()->params['enable_transactions'] = $transactions;
	}
}
