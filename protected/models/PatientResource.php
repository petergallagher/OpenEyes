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
class PatientResource extends Resource
{
	public $id;
	public $pas_key;
	public $dob;
	public $gender;
	public $hos_num;
	public $nhs_num;
	public $gp_id;
	public $date_of_death;
	public $practice_id;
	public $ethnic_group_id;
	public $ethnic_group;
	public $contact_id;
	public $contact;
	public $no_allergies_date;

	public function relations()
	{
		return array(
			'contact' => array(self::BELONGS_TO, 'Contact', 'contact_id'),
			//'gp' => array(self::BELONGS_TO, 'Gp', 'gp_id'),
			//'practice' => array(self::BELONGS_TO, 'Practice', 'practice_id'),
		);
	}
}
