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
?>
<?php
$max_numbers = 20;
$interval = floor($disorders['pages'] / $max_numbers);
if ($interval <1) {
	$interval = 1;
}

$numbers = array();

for ($i=1; $i<=$disorders['pages']; $i+=$interval) {
	$numbers[] = $i;
}

if ($i != $disorders['pages'] && !in_array($disorders['pages'],$numbers)) {
	$numbers[] = $disorders['pages'];
}

if (!in_array($disorders['page'],$numbers)) {
	$diff = 9999999;
	$closest = null;

	foreach ($numbers as $i => $n) {
		if ($i+1 < count($numbers)) {
			if (abs($n - $disorders['page']) < $diff) {
				$diff = abs($n - $disorders['page']);
				$closest = $n;
			}
		}
	}

	$_numbers = array();

	foreach ($numbers as $n) {
		if ($n == $closest) {
			$n = $disorders['page'];
		}
		$_numbers[] = $n;
	}

	$numbers = $_numbers;
}
?>
<center>
	<div class="pagination">
		<?php if ($disorders['page'] > 1) {?>
			<a href="<?php echo $this->getDisorderPageURI($disorders['page']-1)?>">&laquo; back</a>
		<?php }else{?>
			&laquo; back
		<?php }?>
		<?php foreach ($numbers as $i) {?>
			<?php if ($i == $disorders['page']) {?>
				<?php echo $i?>
			<?php }else{?>
				<a href="<?php echo $this->getDisorderPageURI($i)?>"><?php echo $i?></a>
			<?php }?>
		<?php }?>
		<?php if ($disorders['page'] < $disorders['pages']) {?>
			<a href="<?php echo $this->getDisorderPageURI($disorders['page']+1)?>">next &raquo;</a>
		<?php }else{?>
			next &raquo;
		<?php }?>
	</div>
</center>
