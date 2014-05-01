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
		<div class="row field-row">
			<div class="large-3 column"><label><?php if ($label) { echo $label.':';} ?></label></div>
			<div class="large-9 column end">
				<?php if (!$edit && empty($element->relation)) {?>
					<div class="data-value">
						<?php if ($no_allergies_field && $element->$no_allergies_field) {?>
							It was confirmed that the patient has no allergies.
						<?php }else{?>
							<?php echo $no_allergies_text?>
						<?php }?>
					</div>
				<?php }else{?>
					<table class="grid allergies" data-input-name="<?php echo $input_name?>">
						<thead>
							<tr>
								<th>Allergy</th>
								<?php if ($edit) {?>
									<th>Actions</th>
								<?php }?>
							</tr>
						</thead>
						<tbody>
							<tr class="no_allergies"<?php if (!empty($element->$relation)) {?> style="display: none"<?php }?> data-input-name="<?php echo $input_name?>">
								<td colspan="7">
									<?php echo $no_allergies_text?>
								</td>
							</tr>
							<?php if (!empty($element->$relation)) {?>
								<?php foreach ($element->$relation as $i => $allergy) {?>
									<tr>
										<td>
											<?php echo $allergy->allergy->name?>
											<input type="hidden" name="<?php echo $input_name?>_allergies[]" value="<?php echo $allergy->allergy_id?>" />
										</td>
										<?php if ($edit) {?>
											<td>
												<a href="#" class="removeAllergy" data-input-name="<?php echo $input_name?>" data-no-allergies-field="<?php echo get_class($element).'_'.$no_allergies_field?>">remove</a>
											</td>
										<?php }?>
									</tr>
								<?php }?>
							<?php }?>
						</tbody>
					</table>
				<?php }?>
			</div>
		</div>
		<?php if ($edit) {?>
			<?php if ($no_allergies_field) {
				echo $form->checkBox($element, $no_allergies_field, array('text-align' => 'right', 'disabled' => empty($element->allergies) ? '' : 'disabled'), array('label' => 3, 'field' => 4));
			}?>
			<div class="addAllergyFields" style="display: none" data-input-name="<?php echo $input_name?>">
				<div class="row field-row">
					<div class="large-3 column">
						<label>Allergy:</label>
					</div>
					<div class="large-4 column end">
						<?php echo CHtml::dropDownList($input_name.'_allergy_id','',$element->availableAllergyList,array('empty' => '- Select -','class' => 'allergySelection', 'data-input-name' => $input_name, 'data-no-allergies-field' => get_class($element).'_'.$no_allergies_field))?>
					</div>
				</div>
				<div class="row field-row">
					<div class="large-3 column"><label></label></div>
					<div class="large-9 column end">
						<button class="cancelAllergy warning small" data-input-name="<?php echo $input_name?>">Cancel</button>
					</div>
				</div>
			</div>
			<div class="row field-row">
				<div class="large-3 column"><label></label></div>
				<div class="large-9 column end">
					<button class="addAllergy secondary small" data-input-name="<?php echo $input_name?>">Add allergy</button>
				</div>
			</div>
		<?php }?>
