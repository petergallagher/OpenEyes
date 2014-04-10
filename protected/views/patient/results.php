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
<h1 class="badge">Advanced search</h1>
<?php
	$this->beginWidget('CActiveForm', array(
		'id' => 'patient-filter',
		'focus' => '#query',
		'action' => Yii::app()->createUrl('patient/results'),
		'method' => 'GET',
		'htmlOptions' => array(
			'class' => 'form search'
		)
	))?>
	<div class="large-12 column">
		<div class="panel">
			<div class="row">
				<div class="large-12 column">
					<table class="grid">
						<thead>
							<tr>
								<?php foreach (PatientSearchField::model()->findAll(array('order' => 'display_order asc')) as $patient_search_field) {?>
									<th>
										<?php if ($patient_search_field->label) {
											echo $patient_search_field->label.':';
										} else {
											echo Patient::model()->getAttributeLabel($patient_search_field->name).':';
										}?>
									</th>
								<?php }?>
							</tr>
						</thead>
						<tbody>
							<tr>
								<?php foreach (PatientSearchField::model()->findAll(array('order' => 'display_order asc')) as $patient_search_field) {?>
									<td>
										<?php if ($metadata_key = PatientMetadataKey::model()->find('key_name=?',array($patient_search_field->name))) {
											switch($metadata_key->fieldType->name) {
												case 'Text':
													echo CHtml::textField($metadata_key->key_name,empty($_GET) ? $patient->{$metadata_key->key_name} : @$_GET[$metadata_key->key_name]);
													break;
												case 'Select':
													$htmlOptions = $metadata_key->field_option1 ? array('empty' => $metadata_key->field_option1) : array();

													$class_name = $metadata_key->field_option2;

													$options = $metadata_key->field_option2 ?
														CHtml::listData($class_name::model()->findAll(array('order' => 'display_order asc')),'name','name') :
														CHtml::listData($metadata_key->options,'option_value','option_value');

													echo CHtml::dropDownList($metadata_key->key_name,empty($_GET) ? $patient->{$metadata_key->key_name} : @$_GET[$metadata_key->key_name],$options,$htmlOptions);
													break;
												case 'Checkbox':
													echo CHtml::hiddenField($metadata_key->key_name,0);
													echo CHtml::checkBox($metadata_key->key_name,empty($_GET) ? $patient->{$metadata_key->key_name} : @$_GET[$metadata_key->key_name]);
													echo '&nbsp;'.$metadata_key->key_label;
													break;
											}
										} else {
											echo CHtml::textField($patient_search_field->name,@$_GET[$patient_search_field->name]);
										}?>
									</td>
								<?php }?>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="row">
				<div class="large-10 column">
				</div>
				<div class="large-2 column text-right">
					<span style="width: 30px;">
						<img class="loader" src="<?php echo Yii::app()->assetManager->createUrl('img/ajax-loader.gif')?>" alt="loading..." style="display: none;" />
					</span>
					<button id="search_button" class="secondary small" type="submit">
						Search
					</button>
				</div>
			</div>
		</div>
	</div>
<?php $this->endWidget()?>

<div class="row hide" id="patient-search-loading">
	<div class="large-12 column">
		<div class="alert-box">
			<img src="<?php echo Yii::app()->assetManager->createUrl('img/ajax-loader.gif');?>" class="spinner" /> <strong>Please wait...</strong>
		</div>
	</div>
</div>

<div id="patientList" class="patient-list">
	<?php if (!empty($_GET)) {?>
		<?php if ($total_items == 0) {?>
			<div class="row" id="theatre-search-no-results">
				<div class="large-12 column">
					<div class="alert-box">
						<strong>
							<?php echo @$message ? $message : 'No patients match your search criteria.'?>
						</strong>
					</div>
				</div>
			</div>
		<?php } else {?>
			<div class="row">
				<div class="large-12 column">
					<table class="grid patient-list">
						<thead>
							<tr>
								<?php foreach (PatientSearchResultField::model()->findAll(array('order' => 'display_order asc')) as $patient_search_result_field) {?>
									<th>
										<a href="<?php echo $this->getPatientSearchUrl($patient_search_result_field->name)?>">
											<?php if ($patient_search_result_field->label) {
												echo $patient_search_result_field->label;
											} else {
												echo Patient::model()->getAttributeLabel($patient_search_result_field->name);
											}?>
										</a>
									</th>
								<?php }?>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($data as $i => $result) {?>
								<tr data-id="<?php echo $result->id?>" class="clickable">
									<?php foreach (PatientSearchResultField::model()->findAll(array('order' => 'display_order asc')) as $patient_search_result_field) {?>
										<td>
											<?php if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/',$result->{$patient_search_result_field->name})) {
												echo $result->NHSDate($patient_search_result_field->name);
											} else {
												echo $result->{$patient_search_result_field->name};
											}?>
										</td>
									<?php }?>
								</tr>
							<?php }?>
						</tbody>
						<tfoot class="pagination-container">
							<tr>
								<td colspan="7">
									<?php for ($i=1; $i <= $pages; $i++) {?>
										<?php if ($i == $page) {?>
											<?php echo $i?>
										<?php }else{?>
											<a href="<?php echo Yii::app()->createUrl('/patient/results',array('page' => $i) + $search_terms)?>">
												<?php echo $i?>
											</a>
										<?php }?>
										&nbsp;&nbsp;
									<?php }?>
								</td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		<?php }?>
	<?php }?>
</div>
<?php if ($focus = PatientSearchField::model()->find('focus=1')) {?>
	<script type="text/javascript">
		$(document).ready(function() {
			$('#<?php echo $focus->name?>').select().focus();
		});
	</script>
<?php }?>
