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
<div class="large-<?php echo $column_width?> column end">
	<div class="row field-row">
		<div class="large-4 column">
			<label><?php echo $model->getAttributeLabel($column_field['field'])?>:</label>
		</div>
		<div class="large-<?php if (@$column_field['width']) { echo $column_field['width']; } else { echo '4'; }?> column end">
			<?php echo $this->render('Records_'.$column_field['type'],array('column_field' => $column_field))?>
		</div>
		<?php if ($model->getAttributeSuffix($column_field['field'])) {?>
			<div class="large-2 column end">
				<span class="field-info"><?php echo $model->getAttributeSuffix($column_field['field'])?></span>
			</div>
		<?php }?>
	</div>
</div>
