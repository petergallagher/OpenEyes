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
			'nhs_num' => 'nhs_num',
			'hos_num' => 'hos_num',
			'title' => 'contact.title',
			'family_name' => 'contact.last_name',
			'given_name' => 'contact.first_name',
			'gender' => 'gender',
			'birth_date' => 'dob',
			'date_of_death' => 'date_of_death',
			'primary_phone' => 'contact.primary_phone',
			'addresses' => array(self::TYPE_LIST, 'contact.addresses', 'PatientAddress'),
			'gp_ref' => array(self::TYPE_REF, 'gp_id', 'Gp'),
			'prac_ref' => array(self::TYPE_REF, 'practice_id', 'Practice'),
		),
		'PatientAddress' => array(
			'line1' => 'address1',
			'line2' => 'address2',
			'city' => 'city',
			'state' => 'county',
			'zip' => 'postcode',
			'country' => 'country.name',
			'date_start' => array(self::TYPE_OBJECT, 'date_start', 'Date'),
			'date_end' => array(self::TYPE_OBJECT, 'date_end', 'Date'),
			'correspond' => array(self::TYPE_CONDITION, 'address_type_id', 'equals', \AddressType::CORRESPOND),
			'transport' => array(self::TYPE_CONDITION, 'address_type_id', 'equals', \AddressType::TRANSPORT),
		),
	);

	public function search(array $params)
	{
		$model = $this->getSearchModel();
		if (isset($params['id'])) $model->id = $params['id'];
		if (isset($params['identifier'])) {
			$model->hos_num = $params['identifier'];
			$model->nhs_num = $params['identifier'];
		}

		$searchParams = array('pageSize' => null);
		if (isset($params['family'])) $searchParams['last_name'] = $params['family'];
		if (isset($params['given'])) $searchParams['first_name'] = $params['given'];

		return $this->getResourcesFromDataProvider($model->search($searchParams));
	}

	public function resourceToModel($res, $patient)
	{
		$patient->nhs_num = $res->nhs_num;
		$patient->hos_num = $res->hos_num;
		$patient->gender = $res->gender;
		$patient->dob = $res->birth_date;
		$patient->date_of_death = $res->date_of_death;
		$patient->gp_id = $res->gp_ref ? $res->gp_ref->getId() : null;
		$patient->practice_id = $res->prac_ref ? $res->prac_ref->getId() : null;
		$this->saveModel($patient);

		$contact = $patient->contact;
		$contact->title = $res->title;
		$contact->last_name = $res->family_name;
		$contact->first_name = $res->given_name;
		$contact->primary_phone = $res->primary_phone;
		$this->saveModel($contact);

		$cur_addrs = array();
		foreach ($contact->addresses as $addr) {
			$cur_addrs[$addr->id] = PatientAddress::fromModel($addr);
		}

		$add_addrs = array();
		$matched_ids = array();

		foreach ($res->addresses as $new_addr) {
			$found = false;
			foreach ($cur_addrs as $id => $cur_addr) {
				if ($cur_addr->isEqual($new_addr)) {
					$matched_ids[] = $id;
					$found = true;
					unset($cur_addrs[$id]);
					break;
				}
			}
			if (!$found) $add_addrs[] = $new_addr;
		}

		$crit = new \CDbCriteria;
		$crit->compare('contact_id', $contact->id)->addNotInCondition('id', $matched_ids);
		\Address::model()->deleteAll($crit);

		foreach ($add_addrs as $add_addr) {
			$addr = new \Address;
			$addr->contact_id = $contact->id;
			$add_addr->toModel($addr);
			$this->saveModel($addr);
		}

		$cur_cb_ids = array();
		foreach ($patient->commissioningbodies as $cb) {
			$cur_cb_ids[] = $cb->id;
		}

		$new_cb_ids = array();
		foreach ($res->cb_refs as $cb_ref) {
			$new_cb_ids[] = $cb_ref->getId();
		};

		$add_cb_ids = array_diff($new_cb_ids, $cur_cb_ids);
		$del_cb_ids = array_diff($cur_cb_ids, $new_cb_ids);

		foreach ($add_cb_ids as $cb_id) {
			$cba = new \CommissioningBodyPatientAssignment;
			$cba->commissioning_body_id = $cb_id;
			$cba->patient_id = $patient->id;
			$this->saveModel($cba);
		}

		if ($del_cb_ids) {
			$crit = new \CDbCriteria;
			$crit->compare('patient_id', $patient->id)->addInCondition('commissioning_body_id', $del_cb_ids);
			\CommissioningBodyPatientAssignment::model()->deleteAll($crit);
		}
	}

	public function fromJSON($blob)
	{
		$data = json_decode($blob,true);

		$patient = new Patient($data);

		return $patient;
	}
}
