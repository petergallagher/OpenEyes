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
<div class="row">
	<div class="large-8 medium-9 small-12 small-centered medium-centered large-centered column">
		<div class="panel search-examples">
			Find a patient by
			<strong><?php echo Patient::model()->getAttributeLabel('hos_num')?></strong>,
			<strong><?php echo Patient::model()->getAttributeLabel('nhs_num')?></strong>,
			<strong><?php echo Patient::model()->getAttributeLabel('first_name')?> <?php echo Patient::model()->getAttributeLabel('last_name')?></strong> or
			<strong><?php echo Patient::model()->getAttributeLabel('last_name')?> <?php echo Patient::model()->getAttributeLabel('first_name')?></strong>.
		</div>
	</div>
</div>

<div class="row">
	<div class="large-8 medium-9 small-12 small-centered medium-centered large-centered column">

		<?php $this->renderPartial('//base/_messages'); ?>

		<?php
			$this->beginWidget('CActiveForm', array(
				'id' => 'search-form',
				'focus' => '#query',
				'action' => Yii::app()->createUrl('site/search'),
				'htmlOptions' => array(
					'class' => 'form panel search'
				)
			));?>
			<div class="row">
				<div class="large-9 medium-8 small-7 column">
					<?php echo CHtml::textField('query', '', array('class' => 'large', 'placeholder' => 'Enter search...')); ?>
				</div>
				<div class="large-3 medium-4 small-5 column text-right">
					<img class="loader" src="<?php echo Yii::app()->assetManager->createUrl('img/ajax-loader.gif')?>" alt="loading..." style="margin-right: 10px; display: none;" />
					<button type="submit" class="primary long">
						Search
					</button>
				</div>
				<div class="advanced">
					<a href="<?php echo Yii::app()->createUrl('/patient/results')?>">Advanced search</a>
				</div>
			</div>
		<?php $this->endWidget(); ?>
	</div>
</div>