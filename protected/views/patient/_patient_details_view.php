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
		<div class="view-mode">
			<?php if (Yii::app()->params['patient_summary_show_hos_num']) {?>
				<div class="row data-row">
					<div class="large-4 column">
						<div class="data-label"><?php echo $patient->getAttributeLabel('hos_num')?>:</div>
					</div>
					<div class="large-8 column">
						<div class="data-value"><?php echo $patient->hos_num?></div>
					</div>
				</div>
			<?php }?>
			<?php echo $this->renderPartial('_patient_metadata_view',array('patient'=>$patient,'before'=>'first_name'))?>
			<?php if (!Yii::app()->params['patient_summary_hide_blank_fields'] || $patient->first_name) {?>
				<div class="row data-row">
					<div class="large-4 column">
						<div class="data-label"><?php echo $patient->getAttributeLabel('first_name')?>:</div>
					</div>
					<div class="large-8 column">
						<div class="data-value"><?php echo $patient->first_name?></div>
					</div>
				</div>
			<?php }?>
			<?php echo $this->renderPartial('_patient_metadata_view',array('patient'=>$patient,'after'=>'first_name'))?>
			<?php echo $this->renderPartial('_patient_metadata_view',array('patient'=>$patient,'before'=>'last_name'))?>
			<?php if (!Yii::app()->params['patient_summary_hide_blank_fields'] || $patient->last_name) {?>
				<div class="row data-row">
					<div class="large-4 column">
						<div class="data-label"><?php echo $patient->getAttributeLabel('last_name')?>:</div>
					</div>
					<div class="large-8 column">
						<div class="data-value"><?php echo $patient->last_name?></div>
					</div>
				</div>
			<?php }?>
			<?php echo $this->renderPartial('_patient_metadata_view',array('patient'=>$patient,'after'=>'last_name'))?>
			<?php echo $this->renderPartial('_patient_metadata_view',array('patient'=>$patient,'before'=>'address'))?>
			<?php if (!Yii::app()->params['patient_summary_hide_blank_fields'] || $patient->getSummaryAddress()) {?>
				<div class="row data-row">
					<div class="large-4 column">
						<div class="data-label">Address:</div>
					</div>
					<div class="large-8 column">
						<div class="data-value">
							<?php echo $patient->getSummaryAddress()?>
						</div>
					</div>
				</div>
			<?php }?>
			<?php echo $this->renderPartial('_patient_metadata_view',array('patient'=>$patient,'after'=>'address'))?>
			<?php echo $this->renderPartial('_patient_metadata_view',array('patient'=>$patient,'before'=>'dob'))?>
			<?php if (!Yii::app()->params['patient_summary_hide_blank_fields'] || $patient->dob) {?>
				<div class="row data-row">
					<div class="large-4 column">
						<div class="data-label"><?php echo $patient->getAttributeLabel('dob')?>:</div>
					</div>
					<div class="large-8 column">
						<div class="data-value">
							<?php echo ($patient->dob) ? $patient->NHSDate('dob') : 'Unknown' ?>
						</div>
					</div>
				</div>
			<?php }?>
			<?php echo $this->renderPartial('_patient_metadata_view',array('patient'=>$patient,'after'=>'dob'))?>
			<?php if (!$patient->dob) {?>
				<?php echo $this->renderPartial('_patient_metadata_view',array('patient'=>$patient,'before'=>'yob'))?>
				<?php if (!Yii::app()->params['patient_summary_hide_blank_fields'] || $patient->yob) {?>
					<div class="row data-row">
						<div class="large-4 column">
							<div class="data-label"><?php echo $patient->getAttributeLabel('yob')?>:</div>
						</div>
						<div class="large-8 column">
							<div class="data-value">
								<?php echo ($patient->yob) ? $patient->yob : 'Unknown' ?>
							</div>
						</div>
					</div>
				<?php }?>
				<?php echo $this->renderPartial('_patient_metadata_view',array('patient'=>$patient,'after'=>'yob'))?>
			<?php }?>
			<div class="row data-row">
				<?php if ($patient->date_of_death) {?>
					<?php echo $this->renderPartial('_patient_metadata_view',array('patient'=>$patient,'before'=>'date_of_death'))?>
					<div class="large-4 column">
						<div class="data-label"><?php echo $patient->getAttributeLabel('date_of_death')?>:</div>
					</div>
					<div class="large-8 column">
						<div class="data-value">
							<?php echo $patient->NHSDate('date_of_death') . ' (Age '.$patient->getAge().')' ?>
						</div>
					</div>
					<?php echo $this->renderPartial('_patient_metadata_view',array('patient'=>$patient,'after'=>'date_of_death'))?>
				<?php } else {?>
					<?php echo $this->renderPartial('_patient_metadata_view',array('patient'=>$patient,'before'=>'age'))?>
					<?php if (!Yii::app()->params['patient_summary_hide_blank_fields'] || $patient->age) {?>
						<div class="large-4 column">
							<div class="data-label"><?php echo $patient->getAttributeLabel('age')?>:</div>
						</div>
						<div class="large-8 column">
							<div class="data-value">
								<?php echo $patient->getAge()?>
							</div>
						</div>
					<?php }?>
					<?php echo $this->renderPartial('_patient_metadata_view',array('patient'=>$patient,'after'=>'age'))?>
				<?php }?>
			</div>
			<?php echo $this->renderPartial('_patient_metadata_view',array('patient'=>$patient,'before'=>'gender'))?>
			<?php if (!Yii::app()->params['patient_summary_hide_blank_fields'] || $patient->gender) {?>
				<div class="row data-row">
					<div class="large-4 column">
						<div class="data-label"><?php echo $patient->getAttributeLabel('gender')?>:</div>
					</div>
					<div class="large-8 column">
						<div class="data-value">
							<?php echo $patient->getGenderString() ?>
						</div>
					</div>
				</div>
			<?php }?>
			<?php echo $this->renderPartial('_patient_metadata_view',array('patient'=>$patient,'after'=>'gender'))?>
			<?php echo $this->renderPartial('_patient_metadata_view',array('patient'=>$patient,'before'=>'ethnic_group_id'))?>
			<?php if (!Yii::app()->params['patient_summary_hide_blank_fields'] || $patient->ethnic_group_id) {?>
				<div class="row data-row">
					<div class="large-4 column">
						<div class="data-label"><?php echo $patient->getAttributeLabel('ethnic_group_id')?>:</div>
					</div>
					<div class="large-8 column">
						<div class="data-value">
							<?php echo $patient->getEthnicGroupString() ?>
						</div>
					</div>
				</div>
			<?php }?>
			<?php echo $this->renderPartial('_patient_metadata_view',array('patient'=>$patient,'after'=>'ethnic_group_id'))?>
		</div>
