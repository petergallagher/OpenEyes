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

class PracticeService extends DeclarativeModelService
{
	static protected $operations = array(self::OP_READ, self::OP_UPDATE, self::OP_DELETE, self::OP_CREATE, self::OP_SEARCH);

	static protected $search_params = array(
		'id' => self::TYPE_TOKEN,
		'identifier' => self::TYPE_TOKEN,
	);

	static protected $primary_model = 'Practice';

	static protected $model_map = array(
		'Practice' => array(
			'code' => 'code',
			'primary_phone' => 'phone',
			'address' => array(self::TYPE_OBJECT, 'contact.address', 'Address'),
		),
		'Address' => array(
			'line1' => 'address1',
			'line2' => 'address2',
			'city' => 'city',
			'state' => 'county',
			'zip' => 'postcode',
			'country' => 'country.name',
		),
	);

	public function search(array $params)
	{
		$model = $this->getSearchModel();
		if (isset($params['id'])) $model->id = $id;
		if (isset($params['identifier'])) $model->code = $params['identifier'];

		return $this->getResourcesFromDataProvider($model->search());
	}

	public function resourceToModelOLD($res, $prac)
	{
		$prac->code = $res->code;
		$prac->phone = $res->primary_phone;
		$this->saveModel($prac);

		$contact = $prac->contact;
		$contact->primary_phone = $res->primary_phone;
		$this->saveModel($contact);

		if ($res->address) {
			if (!($address = $contact->address)) {
				$address = new \Address;
				$address->contact_id = $contact->id;
			}

			$res->address->toModel($address);
			$this->saveModel($address);
		}
	}
}
