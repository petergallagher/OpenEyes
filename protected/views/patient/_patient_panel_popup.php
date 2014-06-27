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
$clinical = $this->checkAccess('OprnViewClinical');
$warnings = $this->patient->getWarnings($clinical);
$ophthalmicDiagnoses = join(',<br/>', $this->patient->ophthalmicDiagnosesSummary);
$systemicDiagnoses = join(',<br/>', $this->patient->systemicDiagnosesSummary);
$cviStatus = $this->patient->getOPHInfo()->cvi_status->name;
$medications = join(',<br/>', $this->patient->medicationsSummary);
$allergies = join(',<br/>', $this->patient->allergiesSummary);
?>
<div id="patient-popup-container" class="patient-popup-container">

	<!-- Patient icon -->
	<button class="toggle-patient-summary-popup icon-patient-patient-id_small<?= $warnings ? '-warning' : '';?>">
		Toggle patient summary
	</button>
	<!-- Quicklook icon -->
	<button
		class="toggle-patient-summary-popup icon-alert-quicklook"
		data-hide-icon="icon-alert-cross"
		data-show-icon="icon-alert-quicklook">
		Toggle patient summary
	</button>

	<div class="panel patient-popup" id="patient-summary-popup">
		<!-- Help hint -->
		<span
			class="help-hint"
			data-text='{
				"close": {
					"full": "Click to close",
					"short": "Close"
				},
				"lock": {
					"full": "Click to lock",
					"short": "Lock"
				}
			}'>
			Click to lock
		</span>
		<!-- Warnings -->
		<?php if ($warnings) {?>
			<div class="alert-box patient with-icon">
				<span>
					<?php foreach ($warnings as $warn) {?>
						<strong><?php echo $warn['long_msg']; ?></strong>
						- <?php echo $warn['details'];
					}?>
				</span>
			</div>
		<?php }?>
		<?php if ($ophthalmicDiagnoses) {?>
			<div class="row">
				<div class="large-3 column label">
					Ophthalmic Diagnoses
				</div>
				<div class="large-9 column data">
					<?php echo $ophthalmicDiagnoses;?>
				</div>
			</div>
		<?php }?>
		<?php if ($systemicDiagnoses) {?>
			<div class="row">
				<div class="large-3 column label">
					Systemic Diagnoses
				</div>
				<div class="large-9 column data">
					<?php echo $systemicDiagnoses;?>
				</div>
			</div>
		<?php }?>
		<div class="row">
			<div class="large-3 column label">
				CVI Status
			</div>
			<div class="large-9 column data">
				<?php echo $cviStatus;?>
			</div>
		</div>
		<?php if ($medications) {?>
			<div class="row">
				<div class="large-3 column label">
					Medication
				</div>
				<div class="large-9 column data">
					<?php echo $medications;?>
				</div>
			</div>
		<?php }?>
		<div class="row">
			<div class="large-3 column label">
				Allergies
			</div>
			<div class="large-9 column data">
				<?php echo $allergies;?>
			</div>
		</div>
	</div>
</div>