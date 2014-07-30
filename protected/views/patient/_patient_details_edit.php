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
		<script type="text/javascript">
			var PatientSummary_original_values = {};

			PatientSummary_original_values['hos_num'] = '<?php echo $this->patient->hos_num?>';
			PatientSummary_original_values['nhs_num'] = '<?php echo $this->patient->nhs_num?>';
			PatientSummary_original_values['title'] = '<?php echo $this->patient->contact->title?>';
			PatientSummary_original_values['first_name'] = '<?php echo $this->patient->contact ? $this->patient->contact->first_name : ''?>';
			PatientSummary_original_values['last_name'] = '<?php echo $this->patient->contact ? $this->patient->contact->last_name : ''?>';
			PatientSummary_original_values['address1'] = '<?php echo $this->patient->contact && $this->patient->contact->address ? $this->patient->contact->address->address1 : ''?>';
			PatientSummary_original_values['address2'] = '<?php echo $this->patient->contact && $this->patient->contact->address ? $this->patient->contact->address->address2 : ''?>';
			PatientSummary_original_values['city'] = '<?php echo $this->patient->contact && $this->patient->contact->address ? $this->patient->contact->address->city: ''?>';
			PatientSummary_original_values['county'] = '<?php echo $this->patient->contact && $this->patient->contact->address ? $this->patient->contact->address->county: ''?>';
			PatientSummary_original_values['postcode'] = '<?php echo $this->patient->contact && $this->patient->contact->address ? $this->patient->contact->address->postcode : ''?>';
			PatientSummary_original_values['country_id'] = '<?php echo $this->patient->contact && $this->patient->contact->address ? $this->patient->contact->address->country_id : ''?>';
			PatientSummary_original_values['dob'] = '<?php echo $this->patient->NHSDate('dob')?>';
			PatientSummary_original_values['date_of_death'] = '<?php echo $this->patient->NHSDate('date_of_death')?>';
			PatientSummary_original_values['yob'] = '<?php echo $this->patient->yob?>';
			PatientSummary_original_values['gender_id'] = '<?php echo $this->patient->gender_id?>';
			PatientSummary_original_values['ethnic_group_id'] = '<?php echo $this->patient->ethnic_group_id?>';

			<?php foreach (PatientMetadataKey::model()->findAll() as $metadata_key) {?>
				PatientSummary_original_values['<?php echo $metadata_key->key_name?>'] = '<?php echo $patient->metadata($metadata_key->key_name)?>';
			<?php }?>
		</script>

		<?php
		$form = $this->beginWidget('BaseEventTypeCActiveForm', array(
			'id' => 'patient-details-edit',
			'enableAjaxValidation' => false,
			'focus' => '#title',
			'layoutColumns' => array(
				'label' => 2,
				'field' => 5
			),
			'htmlOptions' => array(
				'class' => 'patient-details-edit',
			),
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
