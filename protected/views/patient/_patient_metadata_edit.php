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

$criteria = new CDbCriteria;
$criteria->order = 'display_order asc';

if (@$before) {
	$criteria->addCondition('before_field = :before');
	$criteria->params[':before'] = $before;
} else if (@$after) {
	$criteria->addCondition('after_field = :after');
	$criteria->params[':after'] = $after;
} else {
	$criteria->addCondition('before_field = :blank and after_field = :blank');
	$criteria->params[':blank'] = '';
}
			foreach (PatientMetadataKey::model()->findAll($criteria) as $metadata_key) {?>
				<div class="row data-row">
					<div class="large-4 column">
						<div class="data-label">
							<?php if ($metadata_key->fieldType->name != 'Checkbox') {?>
								<?php echo $metadata_key->key_label?>:
							<?php }?>
						</div>
					</div>
					<div class="large-8 column">
						<div class="data-value">
							<?php switch($metadata_key->fieldType->name) {
								case 'Text':
									$htmlOptions = array();

									if ($metadata_key->getDisabled($patient)) {
										$htmlOptions['disabled'] = 'disabled';
										$value = 'N/A';
									} else {
										$value = empty($_POST) ? $patient->{$metadata_key->key_name} : @$_POST[$metadata_key->key_name];
									}

									echo CHtml::hiddenField($metadata_key->key_name,'');
									echo CHtml::textField($metadata_key->key_name, $value, $htmlOptions);
									break;
								case 'Select':
									$htmlOptions = $metadata_key->field_option1 ? array('empty' => $metadata_key->field_option1) : array();

									if ($metadata_key->hide_fields) {
										$htmlOptions['class'] = 'metadata-hide-fields';
										$htmlOptions['data-hide-fields'] = $metadata_key->hide_fields;
										$htmlOptions['data-hide-fields-values'] = $metadata_key->hide_fields_values;
									}

									$class_name = $metadata_key->field_option2;

									$options = $metadata_key->field_option2 ?
										CHtml::listData($class_name::model()->findAll(array('order' => 'display_order asc')),'name','name') :
										CHtml::listData($metadata_key->options,'option_value','option_value');

									echo CHtml::dropDownList($metadata_key->key_name,empty($_POST) ? $patient->{$metadata_key->key_name} : $_POST[$metadata_key->key_name],$options,$htmlOptions);
									break;
								case 'Checkbox':
									echo CHtml::hiddenField($metadata_key->key_name,0);
									echo CHtml::checkBox($metadata_key->key_name,empty($_POST) ? $patient->{$metadata_key->key_name} : $_POST[$metadata_key->key_name]);
									echo '&nbsp;'.$metadata_key->key_label;
									break;
							}?>
						</div>
					</div>
				</div>
			<?php }?>
