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

	static public $model_map = array(
		'Patient' => array(
			'fields' => array(
				'contacts' => array(self::TYPE_LIST, 'contactAssignments', 'PatientAssociatedContact', 'PatientContactAssignment', array('patient_id' => 'primaryKey')),
			),
		),
		'PatientAssociatedContact' => array(
			'ar_class' => 'PatientContactAssignment',
			'related_objects' => array(
				'patient' => array('patient_id', 'Patient', 'save' => 'no'),
				'location' => array(
					'location_id', 'ContactLocation',
				),
				'contact' => array(
					'contact_id', 'Contact',
				),
			),
			'fields' => array(
				'title' => 'contact.title',
				'given_name' => 'contact.first_name',
				'family_name' => 'contact.last_name',
				'primary_phone' => 'contact.primary_phone',
				'site_ref' => array(self::TYPE_REF, 'location.site_id', 'Site'),
				'institution_ref' => array(self::TYPE_REF, 'location.institution_id', 'Institution'),
				'contact_id' => 'contact.id',
				'location_id' => 'location.id',
			),
		),
	);

	public function search(array $params)
	{
	}

	public function expandModelAttribute($model, $attribute)
	{
		if (preg_match('/^contact\./',$attribute)) {
			return DeclarativeTypeParser::expandObjectAttribute($model, $model->location ? "location.$attribute" : $attribute);
		} else {
			return parent::expandModelAttribute($model, $attribute);
		}
	}

	public function setModelAttributeFromResource(&$model, $attribute, $resource_value)
	{
		if (preg_match('/^contact\./',$attribute) && $model->expandAttribute('location')) {
			$attribute = "location.$attribute";
		}

		// blank pks on new records break Yii
		if (preg_match('/\.id$/',$attribute) && !$resource_value) return;

		return parent::setModelAttributeFromResource($model, $attribute, $resource_value);
	}

	public function setUpRelatedObjects(&$model, $resource)
	{
		foreach ($model->getRelatedObjectDefinitions() as $relation_name => $def) {
			$attribute = $this->getRelatedObjectAttribute($relation_name, $def, $resource);
			$related_object_value = $this->getRelatedObjectValue($model, $relation_name, $def, $resource);

			$object_relation = ($pos = strpos($attribute,'.')) ? substr($attribute,0,$pos).'.'.$relation_name : $relation_name;

			if ($object_relation == 'location' && (DeclarativeTypeParser::attributesAllNull($resource, array('site_ref','institution_ref')))) {
				$related_object_value = null;
			}

			if ($object_relation == 'contact' && (!DeclarativeTypeParser::attributesAllNull($resource, array('site_ref','institution_ref')))) {
				$object_relation = "location.contact";
			}

			$model->setAttribute($object_relation, $related_object_value, false);
		}
	}

	public function saveListitem_PatientContactAssignment($pca)
	{
		if ($pca->location) {
			$this->saveModel($pca->location->contact);

			$pca->location->contact_id = $pca->location->contact->id;

			$this->saveModel($pca->location);

			$pca->location_id = $pca->location->id;
			$pca->contact_id = null;
		} else {
			$this->saveModel($pca->contact);

			$pca->contact_id = $pca->contact->id;
			$pca->location_id = null;
		}

		$this->saveModel($pca);
	}

	public function setUpListItem($item, $model_class)
	{
		$model_item = parent::setUpListItem($item, $model_class);

		if (!$item->location_id) {
			$model_item->location = null;
			$model_item->location_id = null;
		} else if (!$item->contact_id) {
			$model_item->contact = null;
			$model_item->contact_id = null;
		}

		return $model_item;
	}
}
