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
				<?php if (!Yii::app()->params['patient_summary_hide_blank_fields'] || $metadata_key->fieldType->name == 'Checkbox' || $patient->{$metadata_key->key_name}) {?>
					<div class="row data-row">
						<div class="large-4 column">
							<div class="data-label"><?php echo $metadata_key->key_label?>:</div>
						</div>
						<div class="large-8 column">
							<div class="data-value">
								<?php if ($metadata_key->fieldType->name == 'Checkbox') {
									echo $patient->{$metadata_key->key_name} ? 'Yes' : 'No';
								} else {
									echo $patient->{$metadata_key->key_name} ? $patient->{$metadata_key->key_name} : '-';
								}?>
							</div>
						</div>
					</div>
				<?php }?>
			<?php }?>
