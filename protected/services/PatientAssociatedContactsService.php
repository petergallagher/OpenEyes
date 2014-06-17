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

class PatientAssociatedContactsService extends DeclarativeModelService
{
	static protected $operations = array(self::OP_READ, self::OP_UPDATE, self::OP_CREATE, self::OP_SEARCH);

	static protected $search_params = array(
		'id' => self::TYPE_TOKEN,
		'identifier' => self::TYPE_TOKEN,
		'family' => self::TYPE_STRING,
		'given' => self::TYPE_STRING,
	);

	static protected $primary_model = 'Patient';

	static protected $model_map = array(
		'Patient' => array(
			'fields' => array(
				'contacts' => array(self::TYPE_LIST, 'contactAssignments', 'PatientAssociatedContact', 'PatientContactAssignment', array('patient_id' => 'primaryKey')),
			),
		),
		'PatientAssociatedContact' => array(
			'ar_class' => 'PatientContactAssignment',
			'related_objects' => array(
				'patient' => array('patient_id', 'Patient'),
				'location' => array('location_id', 'ContactLocation'),
				'contact' => array(array('location.contact_id', 'contact_id'), 'Contact', array('site_ref', 'institution_ref')),
			),
			'fields' => array(
				'title' => array(self::TYPE_OR, 'title', array('location.contact','contact')),
				'given_name' => array(self::TYPE_OR, 'first_name', array('location.contact', 'contact')),
				'family_name' => array(self::TYPE_OR, 'last_name', array('location.contact', 'contact')),
				'primary_phone' => array(self::TYPE_OR, 'primary_phone', array('location.contact', 'contact')),
				'site_ref' => array(self::TYPE_REF, 'location.site_id', 'Site'),
				'institution_ref' => array(self::TYPE_REF, 'location.institution_id', 'Institution'),
			),
			'rules' => array(
				'title' => array(self::RULE_TYPE_ALLNULL, array('site_ref', 'institution_ref'), 'then' => 'contact', 'else' => 'location.contact'),
				'given_name' => array(self::RULE_TYPE_ALLNULL, array('site_ref', 'institution_ref'), 'then' => 'contact', 'else' => 'location.contact'),
				'family_name' => array(self::RULE_TYPE_ALLNULL, array('site_ref', 'institution_ref'), 'then' => 'contact', 'else' => 'location.contact'),
				'primary_phone' => array(self::RULE_TYPE_ALLNULL, array('site_ref', 'institution_ref'), 'then' => 'contact', 'else' => 'location.contact'),
			),
		),
	);

	public function search(array $params)
	{
	}
}
