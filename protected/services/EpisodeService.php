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

class EpisodeService extends DeclarativeModelService
{
	static protected $operations = array(self::OP_READ, self::OP_UPDATE, self::OP_CREATE, self::OP_SEARCH);

	static protected $search_params = array(
		'id' => self::TYPE_TOKEN,
	);

	static protected $primary_model = 'Episode';

	static protected $model_map = array(
		'Episode' => array(
			'reference_objects' => array(
				'firm' => array('firm_id', 'Firm', array('name','service_subspecialty_assignment_id')),
				'subspecialty' => array('firm.serviceSubspecialtyAssignment.subspecialty_id', 'Subspecialty', array('name')),
				'status' => array('episode_status_id', 'EpisodeStatus', array('name')),
				'eye' => array('eye_id', 'Eye', array('name')),
				'diagnosis' => array('disorder_id', 'Disorder', array('term')),
			),
			'fields' => array(
				'patient_ref' => array(self::TYPE_REF, 'patient_id', 'Patient'),
				'firm' => 'firm.name',
				'subspecialty' => 'firm.serviceSubspecialtyAssignment.subspecialty.name',
				'start_date' => array(self::TYPE_SIMPLEOBJECT, 'start_date', 'Date'),
				'end_date' => array(self::TYPE_SIMPLEOBJECT, 'end_date', 'Date'),
				'status' => 'status.name',
				'legacy' => 'legacy',
				'deleted' => 'deleted',
				'eye' => 'eye.name',
				'disorder' => 'diagnosis.term',
				'support_services' => 'support_services',
			),
		),
	);

	public function search(array $params)
	{
	}

	public function getComplexReferenceObjects(&$model, $resource)
	{
		if ($resource->firm && $resource->subspecialty) {
			if (!$subspecialty = \Subspecialty::model()->find('name=?',array($resource->subspecialty))) {
				throw new \Exception("Subspecialty not found: $resource->subspecialty");
			}

			if (!$firm = \Firm::model()->find('name=? and service_subspecialty_assignment_id=?',array($resource->firm,$subspecialty->serviceSubspecialtyAssignment->id))) {
				throw new \Exception("Firm $resource->firm not found with subspecialty $resource->subspecialty");
			}
		} else if ($resource->firm) {
			if (!$firm = \Firm::model()->find('name=? and service_subspecialty_assignment_id is null',array($resource->firm))) {
				throw new \Exception("Firm $resource->firm not found with null subspecialty");
			}
		}

		if (isset($firm)) {
			$model->setAttribute('firm',$firm);
			$model->setAttribute('firm_id',$firm->id);
		}
	}
}
