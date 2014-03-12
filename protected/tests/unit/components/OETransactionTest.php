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
class OETransactionTest extends CDbTestCase
{
	public function testConstruct()
	{
		$transaction = new OETransaction('pdo','operation name','object name');

		$this->assertEquals('pdo',$transaction->pdo_transaction);
		$this->assertInstanceOf('Transaction',$transaction->oe_transaction);
		$this->assertEquals('operation name',$transaction->oe_transaction->operation->name);
		$this->assertEquals('object name',$transaction->oe_transaction->object->name);
	}

	public function testCommitNotPossibleWithoutOperationName()
	{
		$transaction = new OETransaction('pdo');

		$this->setExpectedException('Exception', 'Transaction has no operation set and so cannot be committed.');

		$transaction->commit();
	}

	public function testCommitNotPossibleWithoutObjectName()
	{
		$transaction = new OETransaction('pdo','operation name');

		$this->setExpectedException('Exception', 'Transaction has no object set and so cannot be committed.');

		$transaction->commit();
	}

	public function testCommitNotPossibleWithoutAnyTableAssignments()
	{
		$transaction = new OETransaction('pdo','operation name','object name');

		$this->setExpectedException('Exception', 'Transaction has no table assignments and so cannot be committed.');

		$transaction->commit();
	}

	public function testSetOperation()
	{
		$transaction = new OETransaction('pdo');

		$transaction->setOperation('operation blah');

		$this->assertEquals('operation blah',$transaction->oe_transaction->operation->name);
	}

	public function testSetObject()
	{
		$transaction = new OETransaction('pdo');

		$transaction->setObject('operation bloo');

		$this->assertEquals('operation bloo',$transaction->oe_transaction->object->name);
	}
}
