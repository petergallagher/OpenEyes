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
<div class="box admin">
	<div class="row">
		<div class="large-8 column end">
			<h2>Disorders</h2>
		</div>
	</div>
	<div class="row data-row">
		<div class="large-2 column">
			<?php echo CHtml::dropDownList('specialty_id',@$_GET['specialty_id'],array('NONE' => '- Systemic -') + CHtml::listData($specialties,'id','name'),array('empty' => '- Specialty -'))?>
		</div>
		<div class="large-2 column">
			<?php echo CHtml::dropDownList('active',@$_GET['active'],array('1' => 'Yes', '0' => 'No'),array('empty' => '- Active status -'))?>
		</div>
		<div class="large-1 column">
			<label for="query">
				Search:
			</label>
		</div>
		<div class="large-3 column">
			<?php echo CHtml::textField('query',@$_GET['query'])?>
		</div>
		<div class="large-1 column">
			<?php echo CHtml::htmlButton("Filter",array('class' => 'button small filterDisorders'))?>
		</div>
		<div class="large-1 column end">
			<?php echo CHtml::htmlButton("Reset",array('class' => 'button small resetFilterDisorders'))?>
		</div>
	</div>
	<div class="row data-row">
		<div class="large-4 column end">
			<?php echo CHtml::htmlButton("Add disorder",array('class' => 'button small addDisorder'))?>
		</div>
	</div>
	<?php echo $this->renderPartial('_disorders_pagination',array('disorders' => $disorders))?>
	<form id="admin_disorders">
		<input type="hidden" name="YII_CSRF_TOKEN" value="<?php echo Yii::app()->request->csrfToken?>" />
		<table class="grid">
			<thead>
				<tr>
					<?php if (count($disorders['results']) >0) {?>
						<th><input type="checkbox" name="selectall" id="selectall" /></th>
					<?php }?>
					<th><a href="<?php echo $column_uris['id']?>">ID</a></th>
					<th><a href="<?php echo $column_uris['specialty_id']?>">Specialty</a></th>
					<th><a href="<?php echo $column_uris['term']?>">Term</a></th>
					<th><a href="<?php echo $column_uris['fully_specified_name']?>">Fully specified name</a></th>
					<th><a href="<?php echo $column_uris['active']?>">Active</a></th>
				</tr>
			</thead>
			<tbody>
				<?php $this->renderPartial('_disorders',array('disorders' => $disorders))?>
			</tbody>
		</table>
	</form>
	<?php if (count($disorders['results']) >0) {?>
		<div class="row data-row">
			<div class="large-4 column end">
				<?php echo CHtml::htmlButton("Delete disorder(s)",array('class' => 'button small deleteDisorders'))?>
			</div>
		</div>
	<?php }?>
</div>
