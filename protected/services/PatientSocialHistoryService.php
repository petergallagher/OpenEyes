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

class PatientSocialHistoryService extends DeclarativeModelService
{
	static protected $operations = array(self::OP_READ, self::OP_UPDATE, self::OP_CREATE, self::OP_SEARCH);

	static protected $search_params = array(
		'id' => self::TYPE_TOKEN,
	);

	static protected $primary_model = 'Patient';

	static public $model_map = array(
		'Patient' => array(
			'related_objects' => array(
				'socialHistory' => array(null, 'SocialHistory', 'patient_id', 'children' => array(
					'occupation' => array('occupation_id', 'SocialHistoryOccupation'),
					'driving_status' => array('driving_status_id', 'SocialHistoryDrivingStatus'),
					'smoking_status' => array('smoking_status_id', 'SocialHistorySmokingStatus'),
					'accommodation' => array('accommodation_id', 'SocialHistoryAccommodation'),
					'carer' => array('carer_id', 'SocialHistoryCarer'),
					'substance_misuse' => array('substance_misuse_id', 'SocialHistorySubstanceMisuse'),
				)),
			),
			'reference_objects' => array(
				'occupation' => array('socialHistory.occupation_id', 'SocialHistoryOccupation', array('name')),
				'driving_status' => array('socialHistory.driving_status_id', 'SocialHistoryDrivingStatus', array('name')),
				'smoking_status' => array('socialHistory.smoking_status_id', 'SocialHistorySmokingStatus', array('name')),
				'accommodation' => array('socialHistory.accommodation_id', 'SocialHistoryAccommodation', array('name')),
				'carer' => array('socialHistory.carer_id', 'SocialHistoryCarer', array('name')),
				'substance_misuse' => array('socialHistory.substance_misuse_id', 'SocialHistorySubstanceMisuse', array('name')),
			),
			'fields' => array(
				'occupation' => 'socialHistory.occupation.name',
				'driving_status' => 'socialHistory.driving_status.name',
				'smoking_status' => 'socialHistory.smoking_status.name',
				'accommodation' => 'socialHistory.accommodation.name',
				'comments' => 'socialHistory.comments',
				'type_of_job' => 'socialHistory.type_of_job',
				'carer' => 'socialHistory.carer.name',
				'alcohol_intake' => 'socialHistory.alcohol_intake',
				'substance_misuse' => 'socialHistory.substance_misuse.name',
			),
		),
	);

	public function search(array $params)
	{
	}

	public function setModelAttributeFromResource(&$model, $attribute, $resource_value)
	{
		if (preg_match('/^socialHistory\.(.*?)\./',$attribute,$m)) {
			if (!$socialHistory = $model->expandAttribute('socialHistory')) {
				$socialHistory = new \SocialHistory;
				$socialHistory->patient_id = method_exists($model,'getId') ? $model->getId() : $model->id;
			}

			$relation_def = PatientSocialHistoryService::$model_map['Patient']['related_objects']['socialHistory']['children'][$m[1]];
			$class = '\\'.$relation_def[1];

			if (!$lookup = $class::model()->find('name=?',array($resource_value))) {
				$lookup = new $class;
				$lookup->name = $resource_value;
			}

			$socialHistory->{$relation_def[0]} = $lookup->id;

			$attribute = 'socialHistory';
			$resource_value = $socialHistory;
		}

		parent::setModelAttributeFromResource($model, $attribute, $resource_value);
	}
}
