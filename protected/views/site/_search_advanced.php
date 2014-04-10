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
	$this->beginWidget('CActiveForm', array(
		'id' => 'patient-filter',
		'focus' => '#query',
		'action' => Yii::app()->createUrl('site/search'),
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
							<th><?php echo Patient::model()->getAttributeLabel('first_name')?>:</th>
							<th><?php echo Patient::model()->getAttributeLabel('last_name')?>:</th>
							<th><?php echo Patient::model()->getAttributeLabel('hos_num')?>:</th>
							<th><?php echo Patient::model()->getAttributeLabel('nhs_num')?>:</th>
						</tr>
						</thead>
						<tbody>
						<tr>
							<td>
								<?php echo CHtml::textField('first_name',@$_POST['first_name'])?>
							</td>
							<td>
								<?php echo CHtml::textField('last_name',@$_POST['last_name'])?>
							</td>
							<td>
								<?php echo CHtml::textField('hos_num',@$_POST['hos_num'])?>
							</td>
							<td>
								<?php echo CHtml::textField('nhs_num',@$_POST['nhs_num'])?>
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

					<button id="search_button" class="secondary" type="submit">
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

<div id="patientList" class="patient-list"></div>
