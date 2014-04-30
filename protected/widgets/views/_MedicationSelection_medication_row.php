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
<tr<?php if ($_edit) {?> id="t<?php echo $_medication->id ? $_medication->id : $_i?>" data-medication-id="<?php echo $_medication->id?>"<?php }?>>
	<td>
		<?php echo $_medication->drug->name?>
		<?php if ($_edit) {?>
			<input type="hidden" name="<?php echo $_input_name?>_medication_ids[]" value="<?php echo $_medication->id?>" />
			<input type="hidden" name="<?php echo $_input_name?>_drug_ids[]" value="<?php echo $_medication->drug_id?>" />
		<?php }?>
	</td>
	<td>
		<?php echo $_medication->route->name?>
		<?php if ($_edit) {?>
			<input type="hidden" name="<?php echo $_input_name?>_route_ids[]" value="<?php echo $_medication->route_id?>" />
		<?php }?>
	</td>
	<td>
		<?php echo $_medication->option ? $_medication->option->name : '-'?>
		<?php if ($_edit) {?>
			<input type="hidden" name="<?php echo $_input_name?>_option_ids[]" value="<?php echo $_medication->option_id?>" />
		<?php }?>
	</td>
	<td>
		<?php echo $_medication->frequency->name?>
		<?php if ($_edit) {?>
			<input type="hidden" name="<?php echo $_input_name?>_frequency_ids[]" value="<?php echo $_medication->frequency_id?>" />
		<?php }?>
	</td>
	<td>
		<?php echo $_medication->NHSDate('start_date')?>
		<?php if ($_edit) {?>
			<input type="hidden" name="<?php echo $_input_name?>_start_dates[]" value="<?php echo $_medication->start_date?>" />
		<?php }?>
	</td>
	<?php if ($_edit) {?>
		<td>
			<a href="#" class="editMedication" data-drug-id="<?php echo $_medication->drug_id?>" data-drug-name="<?php echo $_medication->drug->name?>" data-route-id="<?php echo $_medication->route_id?>" data-option-id="<?php echo $_medication->option_id?>" data-frequency-id="<?php echo $_medication->frequency_id?>" data-start-date="<?php echo $_medication->NHSDate('start_date')?>" data-input-name="<?php echo $_input_name?>">edit</a>
			&nbsp;&nbsp;
			<a href="#" class="removeMedication" data-input-name="<?php echo $_input_name?>">remove</a>
		</td>
	<?php }?>
</tr>
