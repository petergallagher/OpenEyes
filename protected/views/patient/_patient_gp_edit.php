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
		<?php echo CHtml::hiddenField('_gp_id',$gp->id)?>
		<?php echo CHtml::hiddenField('_gp_name',$gp->contact ? $gp->contact->fullName : 'Unknown')?>
		<?php echo CHtml::hiddenField('_gp_address',$gp->contact && $gp->contact->address ? $gp->contact->address->letterLine : 'Unknown')?>
		<?php echo CHtml::hiddenField('_gp_telephone',$gp->contact && $gp->contact->primary_phone ? $gp->contact->primary_phone : 'Unknown')?>
		<?php echo CHtml::hiddenField('_practice_id',$practice->id)?>
		<?php echo CHtml::hiddenField('_gp_practice_address',$practice->contact && $practice->contact->address ? $practice->contact->address->letterLine : 'Unknown')?>
		<?php echo CHtml::hiddenField('_gp_practice_telephone',$practice->phone ? $practice->phone : 'Unknown')?>

		<?php
		$form = $this->beginWidget('BaseEventTypeCActiveForm', array(
			'id' => 'patient-gp-details-edit',
			'enableAjaxValidation' => false,
			'focus' => '#title',
			'layoutColumns' => array(
				'label' => 2,
				'field' => 5
			)
		))?>
			<?php echo CHtml::hiddenField('gp_id',$gp->id)?>
			<?php echo CHtml::hiddenField('practice_id',$practice->id)?>

			<div class="edit-mode" style="display: none;">
				<?php echo $this->renderPartial('_patient_gp_form',array(
					'patient' => $patient,
					'gp' => $gp,
					'practice' => $practice,
				))?>
				<div class="row data-row">
					<div class="large-12 column">
						<button id="btn-save-patient-gp-details" class="secondary small">
							Save
						</button>
						<button id="btn-cancel-edit-patient-gp-details" class="secondary small warning">
							Cancel
						</button>
					</div>
				</div>
			</div>
		<?php $this->endWidget()?>
