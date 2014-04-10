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
<h1 class="badge">Search</h1>
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
								<th><?php echo Patient::model()->getAttributeLabel('hos_num')?>:</th>
								<th><?php echo Patient::model()->getAttributeLabel('nhs_num')?>:</th>
								<th><?php echo Patient::model()->getAttributeLabel('first_name')?>:</th>
								<th><?php echo Patient::model()->getAttributeLabel('last_name')?>:</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>
									<?php echo CHtml::textField('hos_num',@$_GET['hos_num'])?>
								</td>
								<td>
									<?php echo CHtml::textField('nhs_num',@$_GET['nhs_num'])?>
								</td>
								<td>
									<?php echo CHtml::textField('first_name',@$_GET['first_name'])?>
								</td>
								<td>
									<?php echo CHtml::textField('last_name',@$_GET['last_name'])?>
								</td>
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
								<th><a href="<?php echo $this->getPatientSearchUrl('hos_num*1')?>"><?php echo Patient::model()->getAttributeLabel('hos_num')?></a></th>
								<th><a href="<?php echo $this->getPatientSearchUrl('nhs_num*1')?>"><?php echo Patient::model()->getAttributeLabel('nhs_num')?></a></th>
								<th><a href="<?php echo $this->getPatientSearchUrl('title')?>"><?php echo Patient::model()->getAttributeLabel('title')?></a></th>
								<th><a href="<?php echo $this->getPatientSearchUrl('first_name')?>"><?php echo Patient::model()->getAttributeLabel('first_name')?></a></th>
								<th><a href="<?php echo $this->getPatientSearchUrl('last_name')?>"><?php echo Patient::model()->getAttributeLabel('last_name')?></a></th>
								<th><a href="<?php echo $this->getPatientSearchUrl('dob')?>"><?php echo Patient::model()->getAttributeLabel('dob')?></a></th>
								<th><a href="<?php echo $this->getPatientSearchUrl('gender_id')?>"><?php echo Patient::model()->getAttributeLabel('gender')?></a></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($data as $i => $result) {?>
								<tr data-id="<?php echo $result->id?>" class="clickable">
									<td><?php echo $result->hos_num?></td>
									<td><?php echo $result->nhs_num?></td>
									<td><?php echo $result->title?></td>
									<td><?php echo $result->first_name?></td>
									<td><?php echo $result->last_name?></td>
									<td><?php echo $result->dob?></td>
									<td><?php echo $result->gender->name?></td>
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
<script type="text/javascript">
	$(document).ready(function() {
		$('#hos_num').select().focus();
	});
</script>
