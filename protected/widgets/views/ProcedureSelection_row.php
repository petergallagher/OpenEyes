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
<tr class="item" data-proc-id="<?php echo $procedure['id']?>" data-i="<?php echo $j?>">
	<td class="procedure">
		<span class="field">
			<?php echo CHtml::hiddenField($element_class.'['.$field.'_id]['.$j.']', $procedure['assignment_id'])?>
			<?php echo CHtml::hiddenField($element_class.'['.$field.']['.$j.']', $procedure['id'])?>
		</span>
		<span class="value"><?php echo $procedure['term']; ?></span>
	</td>
	<?php if ($durations) {?>
		<td class="duration">
			<?php echo $procedure['default_duration']?> mins
		</td>
	<?php }?>
	<?php if ($eye_field) {?>
		<td class="eye">
			<?php foreach (Eye::model()->findAll(array('order'=>'display_order asc')) as $k => $eye) {?>
				<input type="radio" value="<?php echo $eye->id?>" id="<?php echo $element_class.'_'.$eye_field.'_'.$j.'_'.$k?>" name="<?php echo $element_class.'['.$eye_field.']['.$j.']'?>"<?php if ($eye->id == @$procedure['eye_id']) {?> checked="checked"<?php }?> />
				<label for="<?php echo $element_class.'_'.$eye_field.'_'.$j?>">
					<?php echo $eye->name?>
				</label>
			<?php }?>
		</td>
	<?php }?>
	<?php if (!$read_only) {?>
		<td class="actions">
			<a href="#" class="removeProcedure" data-element="<?php echo $element_class?>" data-field="<?php echo $field?>" data-proc-id="<?php echo $procedure['id']?>">Remove</a>
		</td>
	<?php }?>
</tr>
