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
		<?php echo CHtml::hiddenField('_primary_phone',$contact->primary_phone)?>
		<?php echo CHtml::hiddenField('_email',$address->email)?>

		<?php
		$form = $this->beginWidget('BaseEventTypeCActiveForm', array(
			'id' => 'patient-contact-details-edit',
			'enableAjaxValidation' => false,
			'focus' => '#telephone',
			'layoutColumns' => array(
				'label' => 2,
				'field' => 5
			)
		))?>
			<div class="edit-mode" style="display: none">
				<?php echo $this->renderPartial('_patient_contact_details_form',array(
					'contact' => $contact,
					'address' => $address,
				))?>
				<div class="row data-row">
					<div class="large-12 column">
						<button id="btn-save-patient-contact-details" class="secondary small">
							Save
						</button>
						<button id="btn-cancel-edit-patient-contact-details" class="secondary small warning">
							Cancel
						</button>
					</div>
				</div>
			</div>
		<?php $this->endWidget()?>
