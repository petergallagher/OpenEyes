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
			<div class="large-3 column"><label><?php if ($label) echo $label.':'?></label></div>
			<div class="large-9 column end">
				<table class="grid medications" data-input-name="<?php echo $input_name?>">
					<thead>
						<tr>
							<th>Medication</th>
							<th>Route</th>
							<th>Option</th>
							<th>Frequency</th>
							<th>Start date</th>
							<?php if ($edit) {?>
								<th>Actions</th>
							<?php }?>
						</tr>
					</thead>
					<tbody>
						<tr class="no_medications"<?php if (!empty($element->$relation)) {?> style="display: none"<?php }?>>
							<td colspan="7">
								<?php echo $no_medications_text?>
							</td>
						</tr>
						<?php if (!empty($element->$relation)) {?>
							<?php foreach ($element->$relation as $i => $_medication) {
								$this->render('_MedicationSelection_medication_row',array(
									'_medication' => $_medication,
									'_i' => $i,
									'_edit' => $edit,
									'_input_name' => $input_name,
								));
							}?>
						<?php }?>
					</tbody>
				</table>
			</div>
		</div>
		<?php if ($edit) {?>
			<div class="addMedicationFields" data-input-name="<?php echo $input_name?>" style="display: none">
				<div class="row field-row">
					<div class="large-3 column">
						<label>Medication:</label>
					</div>
					<div class="large-9 column end medicationName" data-input-name="<?php echo $input_name?>">
						<span>None selected</span>
						<input type="hidden" id="_<?php echo $input_name?>_medication_id" value="" />
						<input type="hidden" id="_<?php echo $input_name?>_edit_row_id" value="" />
					</div>
				</div>
				<div class="row field-row">
					<div class="large-3 column">
						<label></label>
					</div>
					<div class="large-4 column end">
						<?php echo CHtml::dropDownList($input_name.'_medication_id','',Drug::model()->listBySubspecialty(Firm::model()->findByPk(Yii::app()->session['selected_firm_id'])->getSubspecialtyID()),array('empty' => '- Select -','class' => 'MedicationSelection-medication-id','data-input-name' => $input_name))?>
					</div>
				</div>
				<div class="row field-row">
					<div class="large-3 column"><label></label></div>
					<div class="large-4 column end">
						<?php
						$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
								'name' => $input_name.'_drug_id',
								'id' => $input_name.'_autocomplete_drug_id',
								'source' => "js:function(request, response) {
									$.getJSON('".Yii::app()->createUrl('/patient/DrugList')."', {
										term : request.term,
									}, response);
								}",
								'options' => array(
									'select' => "js:function(event, ui) {
										$('.medicationName[data-input-name=\"".$input_name."\"] span').html(ui.item.value);
										$('#_".$input_name."_medication_id').val(ui.item.id);
										$(this).val('');
										return false;
									}",
								),
								'htmlOptions' => array(
									'placeholder' => $placeholder,
								),
							))?>
					</div>
				</div>
				<div class="row field-row">
					<div class="large-3 column"><label>Route:</label></div>
					<div class="large-4 column end">
						<?php echo CHtml::dropDownList($input_name.'_route_id','',CHtml::listData(DrugRoute::model()->findAll(),'id','name'),array('empty'=>'- Select -','class' => 'MedicationSelection-route-id','data-input-name' => $input_name))?>
					</div>
				</div>
				<div class="row field-row">
					<div class="large-3 column"><label>Option:</label></div>
					<div class="large-4 column end">
						<?php echo CHtml::dropDownList($input_name.'_option_id','',array(),array('empty'=>'- Select -','class' => 'MedicationSelection-option-id'))?>
					</div>
				</div>
				<div class="row field-row">
					<div class="large-3 column"><label>Frequency:</label></div>
					<div class="large-4 column end">
						<?php echo CHtml::dropDownList($input_name.'_frequency_id','',CHtml::listData(DrugFrequency::model()->findAll(),'id','name'),array('empty'=>'- Select -','class' => 'MedicationSelection-frequency-id'))?>
					</div>
				</div>
				<div class="row field-row">
					<div class="large-3 column"><label>Start date:</label></div>
					<div class="large-2 column end">
						<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
							'name'=>$input_name.'_start_date',
							'id'=>$input_name.'_start_date',
							'options'=>array(
								'showAnim'=>'fold',
								'dateFormat'=>Helper::NHS_DATE_FORMAT_JS
							),
						))?>
					</div>
				</div>
				<div class="row field-row">
					<div class="large-3 column"><label></label></div>
					<div class="large-9 column end">
						<button class="saveMedication secondary small" data-input-name="<?php echo $input_name?>">Add</button>
						<button class="cancelMedication warning small" data-input-name="<?php echo $input_name?>">Cancel</button>
					</div>
				</div>
			</div>
			<div class="row field-row medicationErrors" data-input-name="<?php echo $input_name?>" style="display: none">
				<div class="large-3 column"><label></label></div>
				<div class="large-5 column end">
					<div class="alert-box alert with-icon">
						<p>Please fix the following input errors:</p>
						<ul class="medicationErrorList" data-input-name="<?php echo $input_name?>">
						</ul>
					</div>
				</div>
			</div>
			<div class="row field-row">
				<div class="large-3 column"><label></label></div>
				<div class="large-9 column end">
					<button class="addMedication secondary small" data-input-name="<?php echo $input_name?>">Add medication</button>
				</div>
			</div>
		<?php }?>
