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
<?php if ($label) {?>
<div class="row field-row">
		<div class="large-3 column"><label><?php if ($label) { echo $label.':';} ?></label></div>
		<div class="large-9 column end">
<?php }?>
		<section class="box patient-info associated-data js-toggle-container allergies">
			<header class="box-header">
				<h3 class="box-title">
					<span class="icon-patient-clinician-hd_flag"></span>
					Allergies
				</h3>
				<?php if ($allow_collapse) {?>
					<a href="#" class="toggle-trigger toggle-hide js-toggle">
						<span class="icon-showhide">
							Show/hide this section
						</span>
					</a>
				<?php }?>
			</header>
			<div class="js-toggle-body">
				<p class="allergy-status-unknown"<?php if ($patient->hasAllergyStatus()) {?> style="display: none"<?php }?>>Patient allergy status is unknown</p>
				<p class="allergy-status-none"<?php if (!$patient->no_allergies_date || !empty($patient->allergies)) {?> style="display: none"<?php }?>>Patient has no known allergies</p>
				<input type="hidden" id="allergies_none" name="Allergies_none" value="<?php echo $patient->no_allergies_date ? '1' : '0'?>" />
				<table class="plain patient-data currentAllergies"<?php if ($patient->no_allergies_date || empty($patient->allergies)) {?> style="display: none"<?php }?>>
					<thead>
						<tr>
							<th>Allergies</th>
							<?php if ($edit) {?><th>Actions</th><?php } ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($patient->allergyAssignments as $aa) {?>
							<tr data-assignment-id="<?php echo $aa->id?>" data-allergy-id="<?php echo $aa->allergy->id?>" data-allergy-name="<?php echo $aa->allergy->name?>" data-allergy-other="<?php echo $aa->other?>">
								<td><?php echo CHtml::encode($aa->allergy->name == 'Other' ? $aa->other : $aa->name)?></td>
								<?php if ($edit) {?>
									<td>
										<a href="#" rel="<?php echo $aa->id?>" class="small removeAllergy">
											Remove
										</a>
										<input type="hidden" name="Allergies[]" value="<?php echo $aa->allergy->id?>" />
										<input type="hidden" name="AllergiesOther[]" value="<?php echo $aa->other?>" />
									</td>
								<?php } ?>
							</tr>
						<?php }?>
					</tbody>
				</table>
				<?php if ($edit) {?>
					<div class="box-actions" style="<?php if ($button_align == 'left') {?>text-align: left;<?php }?><?php if (!$post) {?>display: none<?php }?>">
						<button class="secondary small addAllergy">
							Edit
						</button>
					</div>

					<div class="add-allergy"<?php if ($post) {?> style="display: none;"<?php }?>>
						<div class="row field-row">
							<div class="<?php echo $form->columns('label');?>">
								<label></label>
							</div>
							<div class="<?php echo $form->columns('field');?>">
								<input type="hidden" name="no_allergies" value="0" />
								<?php echo CHtml::checkBox('no_allergies', $patient->no_allergies_date ? true : false); ?>
								<label for="no_allergies">Confirm patient has no allergies:</label>
							</div>
						</div>

						<input type="hidden" name="patient_id" value="<?php echo $patient->id?>" />

						<div class="row field-row" id="allergy_field" <?php if ($patient->no_allergies_date && $post) { echo 'style="display: none;"'; }?>>
							<div class="<?php echo $form->columns('label')?>">
								<label for="allergy_id">Add allergy:</label>
							</div>
							<div class="<?php echo $form->columns('field');?>">
								<?php echo CHtml::dropDownList('allergy_id', null, CHtml::listData($patient->availableAllergies, 'id', 'name'), array('empty' => '-- Select --'))?>
							</div>
						</div>
						<div class="allergyOther row field-row hidden">
							<div class="<?php echo $form->columns('label')?>">
								<label for="other">Other allergy:</label>
							</div>
							<div class="<?php echo $form->columns('field');?>">
								<?php echo CHtml::textField('allergy_other','',array('autocomplete'=>Yii::app()->params['html_autocomplete'])); ?>
							</div>
						</div>
						<div class="allergyOtherButton row field-row hidden">
							<div class="<?php echo $form->columns('label')?>">
								<label for="other_button"></label>
							</div>
							<div class="<?php echo $form->columns('field');?>">
								<button class="secondary small addOtherAllergy">
									Add other allergy
								</button>
							</div>
						</div>
						<div class="buttons"<?php if (!$post) {?> style="display: none"<?php }?>>
							<img src="<?php echo Yii::app()->assetManager->createUrl('img/ajax-loader.gif')?>" class="add_allergy_loader" style="display: none;" />
							<button class="secondary small saveAllergy" type="submit">Save</button>
							<button class="warning small cancelAllergy" type="submit">Cancel</button>
						</div>
					</div>
				<?php }?>
			</div>
		</section>
<?php if ($label) {?>
	</div>
</div>
<?php }?>
<?php if ($post) {?>
	<div id="confirm_remove_allergy_dialog" title="Confirm remove allergy" style="display: none;">
		<div id="delete_allergy">
			<div class="alert-box alert with-icon">
				<strong>WARNING: This will remove the allergy from the patient record.</strong>
			</div>
			<p>
				<strong>Are you sure you want to proceed?</strong>
			</p>
			<div class="buttons">
				<input type="hidden" id="remove_allergy_id" value="" />
				<button type="submit" class="warning small btn_remove_allergy">Remove allergy</button>
				<button type="submit" class="secondary small btn_cancel_remove_allergy">Cancel</button>
				<img class="loader" src="<?php echo Yii::app()->assetManager->createUrl('img/ajax-loader.gif')?>" alt="loading..." style="display: none;" />
			</div>
		</div>
	</div>
<?php }?>
<script type="text/javascript">
	OE_allergies_post = <?php echo CJavaScript::encode($post)?>;
</script>
