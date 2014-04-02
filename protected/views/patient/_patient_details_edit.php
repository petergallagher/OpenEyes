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
		<?php echo CHtml::hiddenField('_title',$this->patient->contact->title)?>
		<?php echo CHtml::hiddenField('_first_name',$this->patient->contact ? $this->patient->contact->first_name : '')?>
		<?php echo CHtml::hiddenField('_last_name',$this->patient->contact ? $this->patient->contact->last_name : '')?>
		<?php echo CHtml::hiddenField('_address1',$this->patient->contact && $this->patient->contact->address ? $this->patient->contact->address->address1 : '')?>
		<?php echo CHtml::hiddenField('_address2',$this->patient->contact && $this->patient->contact->address ? $this->patient->contact->address->address2 : '')?>
		<?php echo CHtml::hiddenField('_city',$this->patient->contact && $this->patient->contact->address ? $this->patient->contact->address->city: '')?>
		<?php echo CHtml::hiddenField('_county',$this->patient->contact && $this->patient->contact->address ? $this->patient->contact->address->county: '')?>
		<?php echo CHtml::hiddenField('_postcode',$this->patient->contact && $this->patient->contact->address ? $this->patient->contact->address->postcode : '')?>
		<?php echo CHtml::hiddenField('_country_id',$this->patient->contact && $this->patient->contact->address ? $this->patient->contact->address->country_id : '')?>
		<?php echo CHtml::hiddenField('_dob',$this->patient->NHSDate('dob'))?>
		<?php echo CHtml::hiddenField('_date_of_death',$this->patient->NHSDate('date_of_death'))?>
		<?php echo CHtml::hiddenField('_yob',$this->patient->yob)?>
		<?php echo CHtml::hiddenField('_gender_id',$this->patient->gender_id)?>
		<?php echo CHtml::hiddenField('_ethnic_group_id',$this->patient->ethnic_group_id)?>

		<?php
		$form = $this->beginWidget('BaseEventTypeCActiveForm', array(
			'id' => 'patient-details-edit',
			'enableAjaxValidation' => false,
			'focus' => '#title',
			'layoutColumns' => array(
				'label' => 2,
				'field' => 5
			)
		))?>
			<div class="edit-mode" style="display: none;">
				<div class="row data-row">
					<div class="large-4 column">
						<div class="data-label"><?php echo Contact::model()->getAttributeLabel('title')?>:</div>
					</div>
					<div class="large-8 column">
						<div class="data-value">
							<?php echo CHtml::textField('title',$this->patient->contact->title)?>
							<span class="error" id="title_error"></span>
						</div>
					</div>
				</div>
				<div class="row data-row">
					<div class="large-4 column">
						<div class="data-label"><?php echo Contact::model()->getAttributeLabel('first_name')?>(s):</div>
					</div>
					<div class="large-8 column">
						<div class="data-value">
							<?php echo CHtml::textField('first_name',$this->patient->first_name)?>
							<span class="error" id="first_name_error"></span>
						</div>
					</div>
				</div>
				<div class="row data-row">
					<div class="large-4 column">
						<div class="data-label">Last name:</div>
					</div>
					<div class="large-8 column">
						<div class="data-value">
							<?php echo CHtml::textField('last_name',$this->patient->last_name)?>
							<span class="error" id="last_name_error"></span>
						</div>
					</div>
				</div>
				<div class="row data-row">
					<div class="large-4 column">
						<div class="data-label"><?php echo Address::model()->getAttributeLabel('address1')?>:</div>
					</div>
					<div class="large-8 column">
						<?php echo CHtml::textField('address1',($this->patient->contact && $this->patient->contact->address) ? $this->patient->contact->address->address1 : '')?>
						<span class="error" id="address1_error"></span>
					</div>
				</div>
				<div class="row data-row">
					<div class="large-4 column">
						<div class="data-label"><?php echo Address::model()->getAttributeLabel('address2')?>:</div>
					</div>
					<div class="large-8 column">
						<?php echo CHtml::textField('address2',($this->patient->contact && $this->patient->contact->address) ? $this->patient->contact->address->address2 : '')?>
						<span class="error" id="address2_error"></span>
					</div>
				</div>
				<div class="row data-row">
					<div class="large-4 column">
						<div class="data-label"><?php echo Address::model()->getAttributeLabel('city')?>:</div>
					</div>
					<div class="large-8 column">
						<?php echo CHtml::textField('city',($this->patient->contact && $this->patient->contact->address) ? $this->patient->contact->address->city : '')?>
						<span class="error" id="city_error"></span>
					</div>
				</div>
				<div class="row data-row">
					<div class="large-4 column">
						<div class="data-label"><?php echo Address::model()->getAttributeLabel('county')?>:</div>
					</div>
					<div class="large-8 column">
						<?php echo CHtml::textField('county',($this->patient->contact && $this->patient->contact->address) ? $this->patient->contact->address->county : '')?>
						<span class="error" id="county_error"></span>
					</div>
				</div>
				<div class="row data-row">
					<div class="large-4 column">
						<div class="data-label"><?php echo Address::model()->getAttributeLabel('postcode')?>:</div>
					</div>
					<div class="large-8 column">
						<?php echo CHtml::textField('postcode',($this->patient->contact && $this->patient->contact->address) ? $this->patient->contact->address->postcode : '')?>
						<span class="error" id="postcode_error"></span>
					</div>
				</div>
				<div class="row data-row">
					<div class="large-4 column">
						<div class="data-label"><?php echo Address::model()->getAttributeLabel('country_id')?>:</div>
					</div>
					<div class="large-8 column">
						<?php echo CHtml::dropDownList('country_id',($this->patient->contact && $this->patient->contact->address) ? $this->patient->contact->address->country_id : '',CHtml::listData(Country::model()->findAll(array('order' => 'name asc')),'id','name'))?>
						<span class="error" id="country_id_error"></span>
					</div>
				</div>
				<div class="row data-row">
					<div class="large-4 column">
						<div class="data-label"><?php echo $this->patient->getAttributeLabel('dob')?>:</div>
					</div>
					<div class="large-8 column">
						<div class="data-value">
							<?php $this->widget('zii.widgets.jui.CJuiDatePicker',array(
								'name' => 'dob',
								'id' => 'dob',
								'options' => array(
									'showAnim' => 'fold',
									'dateFormat'=>Helper::NHS_DATE_FORMAT_JS
								),
								'value' => $this->patient->NHSDate('dob'),
								'htmlOptions' => array(
									'style' => 'width: 80px;',
								)
							))?>
							<span class="error" id="dob_error"></span>
						</div>
					</div>
				</div>
				<div class="row data-row">
					<div class="large-4 column">
						<div class="data-label"><?php echo $this->patient->getAttributeLabel('yob')?>:</div>
					</div>
					<div class="large-8 column">
						<div class="data-value">
							<?php echo CHtml::textField('yob',$this->patient->yob,array('style'=>'width: 40px'))?>
							<span class="error" id="age_error"></span>
						</div>
					</div>
				</div>
				<div class="row data-row">
					<div class="large-4 column">
						<div class="data-label"><?php echo $this->patient->getAttributeLabel('date_of_death')?>:</div>
					</div>
					<div class="large-8 column">
						<div class="data-value">
							<?php $this->widget('zii.widgets.jui.CJuiDatePicker',array(
								'name' => 'date_of_death',
								'id' => 'date_of_death',
								'options' => array(
									'showAnim' => 'fold',
									'dateFormat'=>Helper::NHS_DATE_FORMAT_JS
								),
								'value' => $this->patient->NHSDate('date_of_death'),
								'htmlOptions' => array(
									'style' => 'width: 80px;',
								)
							))?>
							<span class="error" id="date_of_death_error"></span>
						</div>
					</div>
				</div>
				<div class="row data-row">
					<div class="large-4 column">
						<div class="data-label"><?php echo $this->patient->getAttributeLabel('gender_id')?>:</div>
					</div>
					<div class="large-8 column">
						<div class="data-value">
							<?php foreach (Gender::model()->findAll() as $gender) {?>
								<?php echo CHtml::radioButton('gender_id',$this->patient->gender_id == $gender->id,array('value' => $gender->id))?> <?php echo $gender->name?>
							<?php }?>
							<span class="error" id="gender_id_error"></span>
						</div>
					</div>
				</div>
				<div class="row data-row">
					<div class="large-4 column">
						<div class="data-label"><?php echo $this->patient->getAttributeLabel('ethnic_group_id')?>:</div>
					</div>
					<div class="large-8 column">
						<div class="data-value">
							<?php echo CHtml::dropDownList('ethnic_group_id',$this->patient->ethnic_group_id,CHtml::listData(EthnicGroup::model()->findAll(array('order' => 'name asc')),'id','name'),array('empty' => '- Unknown -'))?>
							<span class="error" id="ethnic_group_id"></span>
						</div>
					</div>
				</div>
				<div class="row data-row">
					<div class="large-12 column">
						<button id="btn-save-patient-details" class="secondary small">
							Save
						</button>
						<button id="btn-cancel-edit-patient-details" class="secondary small warning">
							Cancel
						</button>
					</div>
				</div>
			</div>
		<?php $this->endWidget()?>
