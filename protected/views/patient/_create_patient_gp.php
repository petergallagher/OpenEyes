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
<section class="box patient-edit js-toggle-container">
	<h3 class="box-title">General Practitioner:</h3>
			<?php echo CHtml::hiddenField('gp_id',$this->patient->gp_id)?>
			<?php echo CHtml::hiddenField('practice_id',$this->patient->practice_id)?>

			<div class="row data-row">
				<div class="large-4 column">
					<div class="data-label">Search for GP:</div>
				</div>
				<div class="large-8 column">
					<?php
					$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
						'name' => "gp_search",
						'id' => "gp_search",
						'value'=>'',
						'source'=>"js:function(request, response) {

							$.ajax({
								'url': '" . Yii::app()->createUrl('patient/GPSearch') . "',
								'type':'GET',
								'data':{'term': request.term},
								'success':function(data) {
									data = $.parseJSON(data);

									var result = [];

									GPCache = {};

									for (var i = 0; i < data.length; i++) {
										result.push(data[i]['line']);
										GPCache[data[i]['line']] = data[i]['gp_id'];
									}

									response(result);
								}
							});
						}",
						'options'=>array(
							'minLength'=>'3',
							'select'=>"js:function(event, ui) {
								var value = ui.item.value;

								$('#gp_search').val('');

								$.ajax({
									'type': 'GET',
									'url': '".Yii::app()->createUrl('patient/getGPDetails')."?gp_id='+GPCache[value],
									'dataType': 'json',
									'success': function(data) {
										$('#gp_id').val(GPCache[value]);
										$('#gp_name').html(data['name']+' (<a href=\"#\" id=\"clear_gp\">clear</a>)');
										if (typeof(data['address']) != 'undefined') {
											$('#gp_address').text(data['address']);
										}
										if (typeof(data['telephone']) != 'undefined') {
											$('#gp_telephone').text(data['telephone']);
										}
									}
								});

								return false;
							}",
						),
						'htmlOptions'=>array(
							'placeholder' => 'search for GP by name'
						),
					))?>
				</div>
			</div>
			<div class="row data-row">
				<div class="large-4 column">
					<div class="data-label">Name:</div>
				</div>
				<div class="large-8 column">
					<div class="data-value" id="gp_name">
						<?php echo ($this->patient->gp) ? $this->patient->gp->contact->fullName : 'Unknown'; ?>
						<?php if ($this->patient->gp) {?>
							(<a href="#" id="clear_gp">clear</a>)
						<?php }?>
					</div>
				</div>
			</div>
			<?php if (Yii::app()->user->checkAccess('admin')) { ?>
				<div class="row data-row highlight">
					<div class="large-4 column">
						<div class="data-label">GP Address:</div>
					</div>
					<div class="large-8 column">
						<div class="data-value" id="gp_address">
							<?php echo ($this->patient->gp && $this->patient->gp->contact->address) ? $this->patient->gp->contact->address->letterLine : 'Unknown'; ?>
						</div>
					</div>
				</div>
				<div class="row data-row highlight">
					<div class="large-4 column">
						<div class="data-label">GP Telephone:</div>
					</div>
					<div class="large-8 column">
						<div class="data-value" id="gp_telephone">
							<?php echo ($this->patient->gp && $this->patient->gp->contact->primary_phone) ? $this->patient->gp->contact->primary_phone : 'Unknown'; ?>
						</div>
					</div>
				</div>
			<?php } ?>
			<div class="row data-row">
				<div class="large-4 column">
					<div class="data-label">Search for practice:</div>
				</div>
				<div class="large-8 column">
					<?php
					$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
						'name' => "practice_search",
						'id' => "practice_search",
						'value'=>'',
						'source'=>"js:function(request, response) {

							$.ajax({
								'url': '" . Yii::app()->createUrl('patient/PracticeSearch') . "',
								'type':'GET',
								'data':{'term': request.term},
								'success':function(data) {
									data = $.parseJSON(data);

									var result = [];

									PracticeCache = {};

									for (var i = 0; i < data.length; i++) {
										result.push(data[i]['line']);
										PracticeCache[data[i]['line']] = data[i]['practice_id'];
									}

									response(result);
								}
							});
						}",
						'options'=>array(
							'minLength'=>'3',
							'select'=>"js:function(event, ui) {
								var value = ui.item.value;

								$('#practice_search').val('');

								$.ajax({
									'type': 'GET',
									'url': '".Yii::app()->createUrl('patient/getPracticeDetails')."?practice_id='+PracticeCache[value],
									'dataType': 'json',
									'success': function(data) {
										$('#practice_id').val(PracticeCache[value]);
										$('#gp_practice_address').html(data['address']+' (<a href=\"#\" id=\"clear_practice\">clear</a>)');
										$('#gp_practice_telephone').text(data['telephone']);
									}
								});

								return false;
							}",
						),
						'htmlOptions'=>array(
							'placeholder' => 'search for practice by address or telephone number'
						),
					))?>
				</div>
			</div>
			<div class="row data-row">
				<div class="large-4 column">
					<div class="data-label">Practice Address:</div>
				</div>
				<div class="large-8 column">
					<div class="data-value" id="gp_practice_address">
						<?php echo ($this->patient->practice && $this->patient->practice->contact->address) ? $this->patient->practice->contact->address->letterLine : 'Unknown'; ?>
						<?php if ($this->patient->practice) {?>
							(<a href="#" id="clear_practice">clear</a>)
						<?php }?>
					</div>
				</div>
			</div>
			<div class="row data-row">
				<div class="large-4 column">
					<div class="data-label">Practice Telephone:</div>
				</div>
				<div class="large-8 column">
					<div class="data-value" id="gp_practice_telephone">
						<?php echo ($this->patient->practice && $this->patient->practice->phone) ? $this->patient->practice->phone : 'Unknown'; ?>
					</div>
				</div>
			</div>
			<div class="row data-row">
				<div class="large-12 column">
					<button id="btn-create-patient" class="secondary small">
						Save
					</button>
				</div>
			</div>
			<script type="text/javascript">
				var GPCache = {};
				var PracticeCache = {};
			</script>
