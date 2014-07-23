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
	<div class="large-3 column">
		<label><?php echo $element->getAttributeLabel($field)?>:</label>
	</div>
	<div class="large-9 column end">
		<table>
			<thead>
				<tr>
					<th>Date/time</th>
					<th>Description</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php if (empty($element->$field)) {?>
					<tr>
						<td colspan="3">
							<?php echo $no_items_text?>
						</td>
					</tr>
				<?php }else{?>
					<?php foreach ($element->$field as $item) {?>
						<tr>
							<td><?php echo $item->NHSDate('timestamp')?></td>
							<td><?php echo $item->description?></td>
							<td>
								<a class="editRecordItem">edit</a>
								<a class="deleteRecordItem">delete</a>
							</td>
						</tr>
					<?php }?>
				<?php }?>
			</tbody>
		</table>
	</div>
</div>
<div class="addRecordItemDiv" style="display: none">
	<div class="row field-row">
		<div class="large-3 column">
			<label></label>
		</div>
		<div class="large-9 column end">
			<div class="row field-row">
				<div class="large-2 column">
					<label>Date/time:</label>
				</div>
				<div class="large-7 column end">
					<?php echo $form->datePicker($model,'timestamp',array(),array('nowrapper'=>true),array())?>
				</div>
			</div>
			<?php foreach ($columns[0]['fields'] as $j => $field) {?>
				<div class="row field-row">
					<div class="large-<?php echo $columns[0]['width']?> column end">
						<?php echo $form->textField($model,$field,array('append-text' => $model->getAttributeSuffix($field)),array(),array('label' => 4, 'field' => 4, 'append-text' => 2))?>
					</div>
					<?php $i=0; while (isset($columns[$i+1])) {?>
						<div class="large-<?php echo $columns[$i+1]['width']?> column end">
							<?php echo $form->textField($model,$columns[$i+1]['fields'][$j],array('append-text' => $model->getAttributeSuffix($columns[$i+1]['fields'][$j])),array(),array('label' => 4, 'field' => 4, 'append-text' => 2))?>
						</div>
						<?php $i++;?>
					<?php }?>
				</div>
			<?php }?>
		</div>
	</div>
	<div class="row field-row">
		<div class="large-3 column">
			<label></label>
		</div>
		<div class="large-9 column end">
			<?php echo EventAction::button('Save', 'save', array('level' => 'save'),array('class' => 'saveRecordItem'))->toHtml()?>
			<?php echo EventAction::button('Cancel', 'cancel', array(),array('class' => 'small warning primary cancelRecordItem'))->toHtml()?>
		</div>
	</div>
</div>
<div class="row field-row addItemButton">
	<div class="large-3 column">
		<label></label>
	</div>
	<div class="large-9 column end">
		<?php echo EventAction::button($add_button_text, 'add', array('level' => 'save'),array('class' => 'addRecordItem'))->toHtml()?>
	</div>
</div>
