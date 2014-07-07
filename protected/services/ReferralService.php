<?php
/**
 * (C) OpenEyes Foundation, 2014
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (C) 2014, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

namespace services;

class ReferralService extends DeclarativeModelService
{
	static protected $operations = array(self::OP_READ, self::OP_UPDATE, self::OP_DELETE, self::OP_CREATE, self::OP_SEARCH);

	static protected $search_params = array(
		'id' => self::TYPE_TOKEN,
		'identifier' => self::TYPE_TOKEN,
	);

	static protected $primary_model = 'Referral';

	static public $model_map = array(
		'Referral' => array(
			'reference_objects' => array(
				'referral_type' => array('referral_type_id', 'ReferralType', array('name')),
			),
			'fields' => array(
				'refno' => 'refno',
				'patient_ref' => array(self::TYPE_REF, 'patient_id', 'Patient'),
				'referral_type' => 'referral_type.name',
				'received_date' => array(self::TYPE_SIMPLEOBJECT, 'received_date', 'Date'),
				'closed_date' => array(self::TYPE_SIMPLEOBJECT, 'closed_date', 'Date'),
				'referrer' => 'referrer',
				'firm_ref' => array(self::TYPE_REF, 'firm_id', 'Firm'),
				'service_subspecialty_assignment_ref' => array(self::TYPE_REF, 'service_subspecialty_assignment_id', 'ServiceSubspecialtyAssignment'),
			),
		)
	);

	public function search(array $params)
	{
	}
}
