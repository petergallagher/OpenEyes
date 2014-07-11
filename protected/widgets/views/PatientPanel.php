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
<div class="panel patient" id="patientID">
	<div class="patient-details">
		<!-- Name -->
		<?php echo CHtml::link($this->patient->getDisplayName(),array('/patient/view/'.$this->patient->id)) ?>
		<!-- Age -->
		<span class="patient-age">
			(<?php echo $this->age; ?>)
		</span>
		<!-- Gender -->
		<span class="icon icon-alert icon-alert-<?php echo strtolower($this->patient->getGenderString()) ?>_trans">
			<?php echo $this->patient->getGenderString() ?>
		</span>
	</div>
	<div class="clearfix">
		<span class="hospital-number">
			<span class="screen-only abbr">
				No.
			</span>
			<span class="print-only">
				Hosptial No.
			</span>
			<?php echo $this->patient->hos_num?>
		</span>
		<!-- NHS number -->
		<span class="nhs-number">
			<span class="hide-text print-only">
				NHS number:
			</span>
			<?php echo $this->patient->nhsnum?>
		</span>
	</div>
	<div class="patient-summary-anchor">
		<?php echo CHtml::link('Patient Summary',array('/patient/view/'.$this->patient->id)); ?>
	</div>

	<!-- Widgets (extra icons, links etc) -->
	<ul class="patient-widgets">
		<?php foreach ($this->widgets as $widget) {
			echo "<li>{$widget}</li>";
		}?>
	</ul>
</div>