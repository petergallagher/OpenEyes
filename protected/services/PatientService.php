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

class PatientService extends DeclarativeModelService
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
			'related_objects' => array(
				'contact' => array('contact_id', 'Contact'),
			),
			'fields' => array(
				'nhs_num' => 'nhs_num',
				'hos_num' => 'hos_num',
				'title' => 'contact.title',
				'family_name' => 'contact.last_name',
				'given_name' => 'contact.first_name',
				'gender_ref' => array(self::TYPE_REF, 'gender_id', 'Gender'),
				'birth_date' => array(self::TYPE_SIMPLEOBJECT, 'dob', 'Date'),
				'date_of_death' => array(self::TYPE_SIMPLEOBJECT, 'date_of_death', 'Date'),
				'primary_phone' => 'contact.primary_phone',
				'addresses' => array(self::TYPE_LIST, 'contact.addresses', 'PatientAddress', 'Address', 'contact_id'),
				'gp_ref' => array(self::TYPE_REF, 'gp_id', 'Gp'),
				'prac_ref' => array(self::TYPE_REF, 'practice_id', 'Practice'),
				'cb_refs' => array(self::TYPE_REF_LIST, 'commissioningbody_assignments', 'commissioning_body_id', 'CommissioningBody'),
			),
		),
		'PatientAddress' => array(
			'ar_class' => 'Address',
			'related_objects' => array(
				'contact' => array('contact_id', 'Contact'),
			),
			'reference_objects' => array(
				'country' => array('country_id', 'Country', array('name')),
			),
			'fields' => array(
				'line1' => 'address1',
				'line2' => 'address2',
				'city' => 'city',
				'state' => 'county',
				'zip' => 'postcode',
				'country' => 'country.name',
				'date_start' => array(self::TYPE_SIMPLEOBJECT, 'date_start', 'Date'),
				'date_end' => array(self::TYPE_SIMPLEOBJECT, 'date_end', 'Date'),
				'correspond' => array(self::TYPE_CONDITION, 'address_type_id', 'equals', \AddressType::CORRESPOND),
				'transport' => array(self::TYPE_CONDITION, 'address_type_id', 'equals', \AddressType::TRANSPORT),
			),
			'model_defaults' => array(
				'address_type_id' => \AddressType::HOME
			),
		),
		'Gender' => array(
			'fields' => array(
				'name' => 'name',
			),
		),
		'Country' => array(
			'fields' => array(
				'name' => 'name',
			),
		),
	);

	public static function getModelMap()
	{
		return self::$model_map;
	}

	public function search(array $params)
	{
		$model = $this->getSearchModel();
		if (isset($params['id'])) $model->id = $params['id'];

		$searchParams = array('pageSize' => null);
		if (isset($params['identifier'])) {
			$searchParams['hos_num'] = $params['identifier'];
			$searchParams['nhs_num'] = $params['identifier'];
		}
		if (isset($params['family'])) $searchParams['last_name'] = $params['family'];
		if (isset($params['given'])) $searchParams['first_name'] = $params['given'];

		$resources = array();
		$results = $model->search($searchParams);
		foreach ($results['data'] as $model) {
			$resources[] = $this->modelToResource($model);
		}
		return $resources;
	}
}
