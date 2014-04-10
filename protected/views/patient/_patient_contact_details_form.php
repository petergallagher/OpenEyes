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
		<?php echo $this->renderPartial('_patient_metadata_edit',array('patient'=>$patient,'before'=>'primary_phone'))?>
		<div class="row data-row">
			<div class="large-4 column">
				<div class="data-label"><?php echo $contact->getAttributeLabel('primary_phone')?>:</div>
			</div>
			<div class="large-8 column">
				<div class="data-value">
					<?php echo CHtml::textField('primary_phone',$contact->primary_phone)?>
				</div>
			</div>
		</div>
		<?php echo $this->renderPartial('_patient_metadata_edit',array('patient'=>$patient,'after'=>'primary_phone'))?>
		<?php echo $this->renderPartial('_patient_metadata_edit',array('patient'=>$patient,'before'=>'email'))?>
		<div class="row data-row">
			<div class="large-4 column">
				<div class="data-label">Email:</div>
			</div>
			<div class="large-8 column">
				<div class="data-value">
					<?php echo CHtml::textField('email',$address->email)?>
				</div>
			</div>
		</div>
		<?php echo $this->renderPartial('_patient_metadata_edit',array('patient'=>$patient,'after'=>'email'))?>
