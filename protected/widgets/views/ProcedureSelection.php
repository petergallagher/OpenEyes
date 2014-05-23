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
<div class="row field-row procedure-selection eventDetail<?php if ($last) {?> eventDetailLast<?php }?>" data-durations="<?php echo $durations ? "1" : "0"?>"<?php if ($hidden) {?> style="display: none;"<?php }?>>
	<div class="large-2 column">
		<label>
			<?php echo $label?>:
		</label>
	</div>
	<div class="large-4 column">
		<fieldset>
			<legend><em>Add a procedure:</em></legend>
			<?php if ($headertext) {?>
				<p><em><?php echo $headertext?></em></p>
			<?php }?>
			<?php
			if (!empty($subsections) || !empty($procedures)) {
				if (!empty($subsections)) {?>
					<div class="field-row">
						<?php echo CHtml::dropDownList(CHtml::modelName($element).'_'.$field.'_subsection_id', '', $subsections, array('class' => 'subSectionSelect', 'empty' => 'Select a subsection', 'data-element' => CHtml::modelName($element), 'data-field' => $field));?>
					</div>
					<div class="field-row hide">
						<?php echo CHtml::dropDownList(CHtml::modelName($element).'_'.$field.'_select_procedure_id', '', array(), array('class' => 'procedureSelect', 'empty' => 'Select a commonly used procedure', 'data-element' => CHtml::modelName($element), 'data-field' => $field));?>
					</div>
				<?php } else { ?>
					<div class="field-row">
						<?php echo CHtml::dropDownList(CHtml::modelName($element).'_'.$field.'_select_procedure_id', '', $procedures, array('class' => 'procedureSelect', 'empty' => 'Select a commonly used procedure', 'data-element' => CHtml::modelName($element), 'data-field' => $field, 'options' => $procedures_options));?>
					</div>
				<?php }
			}
			?>
			<div class="field-row">
				<?php
				$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
						'name'=>'autocomplete_'.CHtml::modelName($element).'_'.$field,
						'id'=>'autocomplete_'.CHtml::modelName($element).'_'.$field,
						'source'=>"js:function(request, response) {
							$.ajax({
								'url': '" . Yii::app()->createUrl('procedure/autocomplete') . "',
								'type':'GET',
								'data':{
									term: request.term,
									restrict: '$restrict',
									restrict_common: ".CJavaScript::encode($restrict_common).",
									subsection: $('select.subSectionSelect[data-element=\"".CHtml::modelName($element)."\"][data-field=\"$field\"]').val()
								},
								'success':function(data) {
									response(ProcedureSelection_".CHtml::modelName($element)."_{$field}.filterSearchResults($.parseJSON(data)));
								}
							});
						}",
						'options'=>array(
							'minLength'=>'2',
							'select'=>"js:function(event, ui) {
								ProcedureSelection_".CHtml::modelName($element)."_{$field}.selectProcedure(ProcedureSelection_".CHtml::modelName($element)."_{$field}.findProcFromCache(ui.item.value));
								return false;
							}",
						),
						'htmlOptions'=>array('placeholder'=>'or enter procedure here')
					)); ?>
			</div>
		</fieldset>
	</div>
	<div class="large-6 column">
		<div class="panel procedures ProcedureSelectionProcedureList" data-element="<?php echo CHtml::modelName($element)?>" data-field="<?php echo $field?>" style="<?php if (empty($selected_procedures)) {?> display: none;<?php }?>">
			<input type="hidden" name="<?php echo CHtml::modelName($element)?>[<?php echo $field?>]" />
			<table class="plain">
				<thead>
				<tr>
					<th>Procedure</th>
					<?php if ($durations) {?>
						<th>Duration</th>
					<?php }?>
					<th>Actions</th>
				</tr>
				</thead>
				<tbody class="body">
				<?php
				if (!empty($selected_procedures)) {
					foreach ($selected_procedures as $procedure) {?>
						<tr class="item" data-proc-id="<?php echo $procedure['id']?>">
							<td class="procedure">
								<span class="field"><?= CHtml::hiddenField(CHtml::modelName($element).'['.$field.'][]', $procedure['id']); ?></span>
								<span class="value"><?= $procedure['term']; ?></span>
							</td>
							<?php if ($durations) {?>
								<td class="duration">
									<?php echo $procedure['default_duration']?> mins
								</td>
							<?php } ?>
							<td>
								<a href="#" class="removeProcedure" data-element="<?php echo CHtml::modelName($element)?>" data-field="<?php echo $field?>" data-proc-id="<?php echo $procedure['id']?>">Remove</a>
							</td>
						</tr>
					<?php	}
				}?>
				</tbody>
			</table>
			<?php if ($durations) {?>
				<table class="grid durations">
					<tfoot>
					<tr>
						<td>
							Calculated Total Duration:
						</td>
						<td class="ProcedureSelectionProjectedDuration" data-element="<?php echo CHtml::modelName($element)?>" data-field="<?php echo $field?>">
							<?php echo $calculated_total_duration?> mins
						</td>
						<td>
							Estimated Total Duration:
						</td>
						<td>
							<?php echo CHtml::activeTextField($element,'total_duration',array('class' => 'ProcedureSelectionEstimatedDuration', 'data-element' => CHtml::modelName($element), 'data-field' => $field, 'style' => 'width: 60px'))?>
						</td>
					</tr>
					</tfoot>
				</table>
			<?php }?>
		</div>
	</div>
</div>
<script type="text/javascript">
	var ProcedureSelection_<?php echo CHtml::modelName($element)?>_<?php echo $field?> = new ProcedureSelection({
		element: '<?php echo CHtml::modelName($element)?>',
		field: '<?php echo $field?>',
		durations: <?php echo CJavaScript::encode($durations)?>,
		selected_procedures: <?php echo CJavaScript::encode($selected_procedures)?>,
		callback: <?php echo CJavaScript::encode($callback)?>
	});
</script>
