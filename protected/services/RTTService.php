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

class RTTService extends DeclarativeModelService
{
	static protected $operations = array(self::OP_READ, self::OP_UPDATE, self::OP_DELETE, self::OP_CREATE, self::OP_SEARCH);

	static protected $search_params = array(
		'id' => self::TYPE_TOKEN,
		'identifier' => self::TYPE_TOKEN,
	);

	static protected $primary_model = 'RTT';

	static protected $model_map = array(
		'RTT' => array(
			'reference_objects' => array(
				'referral_type' => array('referral_type_id', 'ReferralType', array('name')),
			),
			'fields' => array(
				'clock_start' => array(self::TYPE_SIMPLEOBJECT, 'clock_start', 'Date'),
				'clock_end' => array(self::TYPE_SIMPLEOBJECT, 'clock_end', 'Date'),
				'breach' => array(self::TYPE_SIMPLEOBJECT, 'breach', 'Date'),
				'referral_ref' => array(self::TYPE_REF, 'referral_id', 'Referral'),
				'active' => 'active',
				'comments' => 'comments',
			),
		)
	);

	public function search(array $params)
	{
	}
}
