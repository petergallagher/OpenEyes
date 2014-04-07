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
?>
		<?php echo CHtml::hiddenField('_title',$this->patient->contact->title)?>
		<?php echo CHtml::hiddenField('_first_name',$this->patient->contact ? $this->patient->contact->first_name : '')?>
		<?php echo CHtml::hiddenField('_last_name',$this->patient->contact ? $this->patient->contact->last_name : '')?>
		<?php echo CHtml::hiddenField('_address1',$this->patient->contact && $this->patient->contact->address ? $this->patient->contact->address->address1 : '')?>
		<?php echo CHtml::hiddenField('_address2',$this->patient->contact && $this->patient->contact->address ? $this->patient->contact->address->address2 : '')?>
		<?php echo CHtml::hiddenField('_city',$this->patient->contact && $this->patient->contact->address ? $this->patient->contact->address->city: '')?>
		<?php echo CHtml::hiddenField('_county',$this->patient->contact && $this->patient->contact->address ? $this->patient->contact->address->county: '')?>
		<?php echo CHtml::hiddenField('_postcode',$this->patient->contact && $this->patient->contact->address ? $this->patient->contact->address->postcode : '')?>
		<?php echo CHtml::hiddenField('_country_id',$this->patient->contact && $this->patient->contact->address ? $this->patient->contact->address->country_id : '')?>
		<?php echo CHtml::hiddenField('_dob',$this->patient->NHSDate('dob'))?>
		<?php echo CHtml::hiddenField('_date_of_death',$this->patient->NHSDate('date_of_death'))?>
		<?php echo CHtml::hiddenField('_yob',$this->patient->yob)?>
		<?php echo CHtml::hiddenField('_gender_id',$this->patient->gender_id)?>
		<?php echo CHtml::hiddenField('_ethnic_group_id',$this->patient->ethnic_group_id)?>

		<?php
		$form = $this->beginWidget('BaseEventTypeCActiveForm', array(
			'id' => 'patient-details-edit',
			'enableAjaxValidation' => false,
			'focus' => '#title',
			'layoutColumns' => array(
				'label' => 2,
				'field' => 5
			)
		))?>
			<div class="edit-mode" style="display: none;">
				<?php echo $this->renderPartial('_patient_details_form',array(
					'patient' => $patient,
					'contact' => $patient->contact,
					'address' => $patient->contact->address ? $patient->contact->address : new Address,
				))?>
				<div class="row data-row">
					<div class="large-12 column">
						<button id="btn-save-patient-details" class="secondary small">
							Save
						</button>
						<button id="btn-cancel-edit-patient-details" class="secondary small warning">
							Cancel
						</button>
					</div>
				</div>
			</div>
		<?php $this->endWidget()?>
