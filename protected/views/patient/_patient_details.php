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
<section class="box patient-info patient-details js-toggle-container">
	<?php echo $this->renderPartial('//patient/_form_errors')?>
	<h3 class="box-title">Personal Details:</h3>
	<a href="#" class="toggle-trigger toggle-hide js-toggle">
		<span class="icon-showhide">
			Show/hide this section
		</span>
	</a>
	<?php if (!@$no_edit && Yii::app()->params['patient_demographics_editable'] && Yii::app()->user->checkAccess('OprnEditPatientDetails')) {?>
		<a href="#" class="toggle-edit-patient-details edit-patient-details">
			edit
		</a>
	<?php }?>
	<div class="js-toggle-body patient-details">
		<?php echo $this->renderPartial('_patient_details_view',array('patient' => $patient))?>
		<?php if (!@$no_edit && Yii::app()->params['patient_demographics_editable'] && Yii::app()->user->checkAccess('OprnEditPatientDetails')) {?>
			<?php echo $this->renderPartial('_patient_details_edit',array('patient' => $patient))?>
		<?php }?>
	</div>
	<?php echo $this->renderPartial('//patient/_form_errors')?>
</section>
