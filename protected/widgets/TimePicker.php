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

class TimePicker extends BaseFieldWidget
{
	/**
	 * Name of the widget.
	 * @var [type]
	 */
	public $name;

	/**
	 * Widget options
	 * @var array
	 */
	public $options = array();

	/**
	 * CSS class to added to the text field.
	 * @var string
	 */
	private $cssClass = 'time-picker-field';

	/**
	 * Default widget options. Will be merged into options.
	 * @var array
	 */
	private $defaultOptions = array(
		'showTimeNowButton' => false
	);

	/**
	 * We prevent including the script file automatically because we're using a
	 * package instead.
	 */
	public $includeScriptFile = false;

	public function init()
	{
		$this->options = array_merge($this->defaultOptions, $this->options);

		Yii::app()->clientScript->registerPackage('TimePicker');

		if (!isset($this->htmlOptions['placeholder'])) {
			$this->htmlOptions['placeholder'] = '00:00';
		}

		$cssClass = isset($this->htmlOptions['class']) ? $this->htmlOptions['class'] : '';
		$cssClass .= " {$this->cssClass}";
		$this->htmlOptions['class'] = trim($cssClass);

		return parent::init();
	}
}
