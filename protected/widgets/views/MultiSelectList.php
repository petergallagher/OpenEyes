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
<?php
if (isset($htmlOptions['options'])) {
	$opts = $htmlOptions['options'];
} else {
	$opts = array();
}

if (isset($htmlOptions['div_id'])) {
	$div_id = $htmlOptions['div_id'];
} else {
	// for legacy, this is the original definition of the div id that was created for the multiselect
	// not recommended as it doesn't allow for sided uniqueness
	$div_id = "div_" . CHtml::modelName($element) . '_' . $field;
}

if (isset($htmlOptions['div_class'])) {
	$div_class = $htmlOptions['div_class'];
} else {
	$div_class = "eventDetail";
}

if (isset($htmlOptions['field_id'])) {
	$field_id = $htmlOptions['field_id'];
} else {
	$field_id = CHtml::getIdByName($field);
}

$found = false;
foreach($selected_ids as $id) {
	if (isset($options[$id])) {
		$found = true;
		break;
	}
}

$widgetOptionsJson = json_encode(array(
	'sorted' => $sorted,
	'maxItems' => $maxItems,
));
?>

<?php
// Don't output this input for this field multiple times.
$fieldName = $element ? CHtml::modelName($element).'['.$field.']' : $field;
if (!in_array($fieldName, MultiSelectList::$fieldNames)) {?>
	<input name="<?php echo $element ? CHtml::modelName($element).'['.$field.']' : $field?>" type="hidden" />
<?php }?>

<?php if (!@$htmlOptions['nowrapper']) {?>
	<div id="<?php echo $div_id ?>" class="<?php echo $div_class ?> row field-row widget"<?php if ($hidden) {?> style="display: none;"<?php }?>>
		<div class="large-<?php echo $layoutColumns['label'];?> column">
			<label for="<?php echo $field_id?>">
				<?php if (@$htmlOptions['label']) {?>
					<?php echo @$htmlOptions['label']?>:
				<?php }?>
			</label>
		</div>
		<div class="large-<?php echo $layoutColumns['field'];?> column end">
	<?php }?>
		<div
			class="multi-select<?php if (!$inline) echo ' multi-select-list';?>"
			data-options='<?php echo $widgetOptionsJson;?>'
			data-show-none-placeholder="<?php echo $showNonePlaceholder ? 'yes' : 'no'?>"
			data-field-name="<?php echo $element ? CHtml::modelName($element).'['.$field.']' : $field?>"
		>
			<div class="multi-select-dropdown-container">
				<select id="<?php echo $field_id;?>" class="MultiSelectList<?php if ($showRemoveAllLink) {?> inline<?php }?><?php if (isset($htmlOptions['class'])) {?> <?php echo $htmlOptions['class']?><?php }?>" name=""<?php if (isset($htmlOptions['data-linked-fields'])) {?> data-linked-fields="<?php echo $htmlOptions['data-linked-fields']?>"<?php }?><?php if (isset($htmlOptions['data-linked-values'])) {?> data-linked-values="<?php echo $htmlOptions['data-linked-values']?>"<?php }?><?php if (!empty($extra_fields)) {?> data-extra-fields="<?php echo implode(',',$extra_fields)?>"<?php }?><?php if ($input_class) {?> data-input-class="<?php echo $input_class?>"<?php }?>>
					<option value=""><?php echo $htmlOptions['empty']?></option>
					<?php foreach ($filtered_options as $value => $option) {
						$attributes = array('value' => $value);
						if (isset($opts[$value])) {
							$attributes = array_merge($attributes, $opts[$value]);
						}
						echo "<option";
						foreach ($attributes as $att => $att_val) {
							echo " " . $att . "=\"" . $att_val . "\"";
						}
						echo ">" . strip_tags($option) . "</option>";
					}?>
				</select>
				<?php if ($showRemoveAllLink) {?>
					<a href="#" class="remove-all<?php echo !$found ? ' hide': '';?>">Remove all</a>
				<?php }?>
			</div>
			<?php if ($noSelectionsMessage) {?>
				<div class="no-selections-msg pill<?php if ($found) {?> hide<?php }?>"><?php echo $noSelectionsMessage;?></div>
			<?php }?>
			<ul class="MultiSelectList multi-select-selections<?php if (!$found && (!empty($selected_ids) || !$showNonePlaceholder)) echo ' hide';?>">
				<li class="MultiSelectNone"<?php if (!empty($selected_ids) || !$showNonePlaceholder) {?> style="display: none"<?php }?>><?php echo is_bool($showNonePlaceholder) ? 'None' : $showNonePlaceholder?></li>
				<?php foreach ($selected_ids as $id) {
					if (isset($options[$id])) {?>
						<li>
							<span class="text">
								<?php echo CHtml::encode($options[$id])?>
								<?php if ($extra_fields) {?>
									<?php foreach ($extra_fields as $field) {?>
										<?php echo CHtml::textField($field.'[]','',$input_class ? array('autocomplete' => Yii::app()->params['html_autocomplete'], 'class' => $input_class) : array('autocomplete' => Yii::app()->params['html_autocomplete']))?>
									<?php }?>
								<?php }?>
							</span>
							<a href="#" data-name="<?php echo CHtml::modelName($element)?>[<?php echo $field?>][]" data-text="<?php echo $options[$id] ?>" class="MultiSelectRemove remove-one<?php if (isset($htmlOptions['class'])) {?> <?php echo $htmlOptions['class']?><?php }?>"<?php if (isset($htmlOptions['data-linked-fields'])) {?> data-linked-fields="<?php echo $htmlOptions['data-linked-fields']?>"<?php }?><?php if (isset($htmlOptions['data-linked-values'])) {?> data-linked-values="<?php echo $htmlOptions['data-linked-values']?>"<?php }?>>Remove</a>
							<input<?php if ($input_class) {?> class="<?php echo $input_class?>"<?php }?> type="hidden" name="<?php echo $element ? CHtml::modelName($element).'['.$field.'][]' : $field.'[]'?>" value="<?php echo $id?>"
							<?php if (isset($opts[$id])) {
								foreach ($opts[$id] as $key => $val) {
									echo " " . $key . "=\"" . $val . "\"";
								}
							}?>
							/>
						</li>
					<?php }?>
				<?php }?>
			</ul>

		</div>
<?php if (!@$htmlOptions['nowrapper']) {?>
	</div>
</div>
<?php }?>
