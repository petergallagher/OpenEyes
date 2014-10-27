<?php
/**
 * (C) OpenEyes Foundation, 2014
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (C) 2014, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

$tabs = array_filter(Yii::app()->params['search_tabs'], function ($tab) { return $tab['position'] !== null; });
usort($tabs, function ($a, $b) { return ($a['position'] < $b['position']) ? -1 : 1; });
?>
<h1 class="badge">Search</h1>
<?php if (count($tabs) > 1): ?>
	<div class="row">
		<div class="large-8 large-centered column panel">
			<ul class="inline-list tabs search">
				<?php foreach ($tabs as $tab): ?>
					<li<?php if ($tab['url'] == Yii::app()->request->requestUri) echo ' class="selected"' ?> >
						<a href="<?= $tab['url'] ?>"><?= CHtml::encode($tab['title']) ?></a>
					</li>
				<?php endforeach ?>
			</ul>
		</div>
	</div>
<?php endif ?>
