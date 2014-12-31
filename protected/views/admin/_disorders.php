<?php
/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2012
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2012, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

if (!empty($disorders['results'])) {
	foreach ($disorders['results'] as $i => $disorder) {?>
		<tr class="clickable" data-id="<?php echo $disorder->id?>" data-uri="admin/editDisorder/<?php echo $disorder->id?>">
			<td><input type="checkbox" name="disorders[]" value="<?php echo $disorder->id?>" /></td>
			<td><?php echo $disorder->id?></td>
			<td><?php echo $disorder->specialty ? $disorder->specialty->name : '-'?></td>
			<td><?php echo $disorder->term?></td>
			<td><?php echo $disorder->fully_specified_name?></td>
			<td><?php echo $disorder->active ? 'Yes' : 'No'?></td>
		</tr>
	<?php }
} else {?>
	<tr><td>No disorders match your filters.</td></tr>
<?php }?>
