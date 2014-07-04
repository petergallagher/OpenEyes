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

class EventService extends DeclarativeModelService
{
	static protected $operations = array(self::OP_READ, self::OP_UPDATE, self::OP_CREATE, self::OP_SEARCH);

	static protected $search_params = array(
		'id' => self::TYPE_TOKEN,
	);

	static protected $primary_model = 'Patient';

	static protected $model_map = array(
		'Event' => array(
			'fields' => array(
				'episode_ref' => array(self::TYPE_REF, 'episode_id', 'Episode'),
				'event_type_ref' => array(self::TYPE_REF, 'event_type_id', 'EventType'),
				'created_date' => array(self::TYPE_SIMPLEOBJECT, 'created_date', 'Date'),
				'event_date' => array(self::TYPE_SIMPLEOBJECT, 'created_date', 'Date'),
				'info' => 'info',
				'deleted' => 'deleted',
				'delete_reason' => 'delete_reason',
				'delete_pending' => 'delete_pending',
				'elements' => array(self::TYPE_ELEMENTS, 'elements', '', '', array('event_id' => 'primaryKey')),
			),
		),
	);

	public function search(array $params)
	{
	}
}
