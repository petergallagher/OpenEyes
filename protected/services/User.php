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

class User extends Resource
{
	static protected $fhir_type = 'User';
	static protected $fhir_prefix = 'user';

	public $username;
	public $first_name;
	public $last_name;
	public $email;
	public $active;
	public $global_firm_rights;
	public $title;
	public $qualifications;
	public $role;
	public $code;
	public $password;
	public $salt;
	public $last_firm_ref;
	public $is_doctor;
	public $last_site_ref;
	public $is_clinical;
	public $is_consultant;
	public $is_surgeon;
	public $has_selected_firms;
}
