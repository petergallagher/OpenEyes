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
<div id="div_<?php echo $element ? CHtml::modelName($element).'_'.$field : $field?>" class="recordsWidget"<?php if ($hidden) {?> style="display: none"<?php }?>>
	<div class="row field-row">
		<div class="large-<?php echo $label_width?> column">
			<label><?php echo $element->getAttributeLabel($field)?>:</label>
		</div>
		<div class="large-<?php echo (12 - $label_width)?> column end">
			<table class="recordsTable">
				<thead>
					<tr>
						<?php foreach ($headings as $heading) {?>
							<th><?php echo $heading?></th>
						<?php }?>
						<?php if ($edit) {?>
							<th>Actions</th>
						<?php }?>
					</tr>
				</thead>
				<tbody>
					<tr<?php if (!empty($element->$field)) {?> style="display: none"<?php }?>>
						<td colspan="3">
							<?php echo $no_items_text?>
						</td>
					</tr>
					<?php foreach ($element->$field as $i => $item) {?>
						<?php echo $this->renderFile($row_view,array('item' => $item,'i' => $i,'edit' => $edit))?>
					<?php }?>
				</tbody>
			</table>
		</div>
	</div>
	<?php if ($edit) {?>
		<div class="addRecordItemDiv" style="display: none">
			<input name="<?php echo CHtml::modelName($element)?>_<?php echo $field?>_editItem" type="hidden" class="recordInput recordEditItem" value="" />
			<input name="<?php echo CHtml::modelName($element)?>_<?php echo $field?>_editItem_id" type="hidden" class="recordInput recordEditItemID" value="" />
			<div class="row field-row">
				<div class="large-<?php echo $label_width?> column">
					<label></label>
				</div>
				<div class="large-<?php echo (12-$label_width)?> column end">
					<?php if ($include_timestamp) {?>
						<div class="row field-row">
							<div class="large-9 column end">
								<div class="row field-row">
									<div class="large-4 column recordDateLabel">
										<?php if ($include_date) {?>
											<label>Date/time:</label>
										<?php }else{?>
											<label>Time:</label>
										<?php }?>
									</div>
									<div class="large-8 column end">
										<?php
										if ($include_date) {
											$this->widget('zii.widgets.jui.CJuiDatePicker',array(
												'name' => 'timestamp',
												'options' => array(
													'showAnim' => 'fold',
													'dateFormat' => Helper::NHS_DATE_FORMAT_JS,
												),
												'htmlOptions' => array(
													'class' => 'recordTimestamp',
												),
											));
										}
										$this->widget('application.widgets.TimePicker', array(
											'name' => 'time',
											'htmlOptions' => array('nowrapper' => true, 'class' => 'recordTime'),
										))?>
										<?php echo EventAction::button('Now', 'now', array('level' => 'save'),array('class' => 'recordsTimeNow'))->toHtml()?>
									</div>
								</div>
							</div>
						</div>
					<?php }?>
					<?php if ($use_last_button_text) {?>
						<div class="row field-row recordsUseLastItemRow"<?php if (empty($element->$field)) {?> style="display: none"<?php }?>>
							<div class="large-12 column end">
								<div class="recordsUseLastItemDiv">
									<?php echo EventAction::button($use_last_button_text, 'use_last_item', array('level' => 'save'),array('class' => 'recordsUseLastItem'))->toHtml()?>
								</div>
							</div>
						</div>
					<?php }?>
					<?php foreach ($columns[0]['fields'] as $j => $field) {?>
						<div class="row field-row">
							<?php echo $this->render('Records_field',array('column_field' => $field, 'column_width' => $columns[0]['width'], 'field_width' => @$columns[0]['field_width']));

							$i=0; while (isset($columns[$i+1])) {
								echo $this->render('Records_field',array('column_field' => $columns[$i+1]['fields'][$j], 'column_width' => $columns[$i+1]['width'], 'field_width' => @$columns[0]['field_width']));
								$i++;
							}?>
						</div>
					<?php }?>
				</div>
			</div>
			<div class="row field-row recordItemErrorsDiv" style="display: none">
				<div class="large-<?php echo $label_width?> column">
					<label></label>
				</div>
				<div class="large-<?php echo (12-$label_width)?> column end">
					<div class="alert-box alert with-icon">
						<p>Please fix the following input errors:</p>
						<ul class="recordItemErrors">
						</ul>
					</div>
				</div>
			</div>
			<div class="row field-row">
				<div class="large-<?php echo $label_width?> column">
					<label></label>
				</div>

				<div class="large-<?php echo (12-$label_width)?> column end">
					<?php echo EventAction::button('Save', 'save', array('level' => 'save'), array(
						'class' => 'saveRecordItem',
						'data-validate-method' => $validate_method,
						'data-sort-after-save' => json_encode($sort_table_after_save)
					))->toHtml()?>
					<?php echo EventAction::button('Cancel', 'cancel', array(),array('class' => 'small warning primary cancelRecordItem'))->toHtml()?>
				</div>
			</div>
		</div>
		<div class="row field-row addItemButton">
			<div class="large-<?php echo $label_width?> column">
				<label></label>
			</div>
			<div class="large-<?php echo (12-$label_width)?> column end">
				<?php echo EventAction::button($add_button_text, 'add', array('level' => 'save'),array('class' => 'addRecordItem'))->toHtml()?>
			</div>
		</div>
	<?php }?>
</div>
