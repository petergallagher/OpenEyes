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

class UserService extends DeclarativeModelService
{
	static protected $operations = array(self::OP_READ, self::OP_UPDATE, self::OP_DELETE, self::OP_CREATE, self::OP_SEARCH);

	static protected $search_params = array(
		'id' => self::TYPE_TOKEN,
		'identifier' => self::TYPE_TOKEN,
	);

	static protected $primary_model = 'User';

	static public $model_map = array(
		'User' => array(
			'related_objects' => array(
				'contact' => array('contact_id', 'Contact'),
			),
			'fields' => array(
				'username' => 'username',
				'first_name' => 'first_name',
				'last_name' => 'last_name',
				'email' => 'email',
				'active' => 'active',
				'global_firm_rights' => 'global_firm_rights',
				'title' => 'title',
				'qualifications' => 'qualifications',
				'role' => 'role',
				'code' => 'code',
				'password' => 'password',
				'salt' => 'salt',
				'last_firm_ref' => array(self::TYPE_REF, 'last_firm_id', 'Firm'),
				'is_doctor' => 'is_doctor',
				'last_site_ref' => array(self::TYPE_REF, 'last_site_id', 'Site'),
				'is_clinical' => 'is_clinical',
				'is_consultant' => 'is_consultant',
				'is_surgeon' => 'is_surgeon',
				'has_selected_firms' => 'has_selected_firms',
			),
		),
	);

	public function search(array $params)
	{
	}
}
