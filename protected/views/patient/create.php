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

$clinical = $clinical = $this->checkAccess('OprnEditPatientDetails');
?>

<div class="container content">
	<h1 class="badge">Add Patient</h1>
	<div class="messages patient">
		<?php $this->renderPartial('//base/_messages'); ?>
	</div>

	<?php
	$form = $this->beginWidget('BaseEventTypeCActiveForm', array(
		'id' => 'patient-create',
		'enableAjaxValidation' => false,
		'focus' => '#hos_num',
		'layoutColumns' => array(
			'label' => 2,
			'field' => 5
		),
		'htmlOptions' => array(
			'style' => 'margin: 0',
		)
	))?>
		<div class="row">
			<div class="large-6 column">
				<?php $this->renderPartial('_create_patient_details',array(
					'patient' => $patient,
					'contact' => $contact,
					'address' => $address,
					'errors' => $errors,
				))?>
				<?php $this->renderPartial('_create_patient_contact_details',array(
					'patient' => $patient,
					'contact' => $contact,
					'errors' => $errors,
				))?>
				<?php $this->renderPartial('_create_patient_gp',array(
					'patient' => $patient,
					'gp' => $gp,
					'practice' => $practice,
					'errors' => $errors,
				))?>
			</div>
		</div>
	<?php $this->endWidget()?>
</div>
