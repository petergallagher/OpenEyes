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

class ProcedureSelection extends BaseFieldWidget
{
	public $subsections;
	public $procedures;
	public $procedures_options;
	public $newRecord;
	public $selected_procedures;
	public $form;
	public $durations = false;
	public $class;
	public $total_duration = 0;
	public $last;
	public $label = 'Procedures';
	public $headertext;
	public $read_only = false;
	public $restrict = false;
	public $restrict_common = false;
	public $callback = false;
	public $layout = false;
	public $calculated_total_duration = 0;

	public function run()
	{
		if (empty($_POST)) {
			if (!$this->selected_procedures && $this->element) {
				$this->selected_procedures = array();
				
				foreach ($this->element->{$this->field} as $proc) {
					$this->selected_procedures[] = array(
						'id' => $proc->id,
						'term' => $proc->term,
						'default_duration' => $proc->default_duration,
						'is_common' => false,
					);
				}
			}
		} else {
			$this->selected_procedures = array();
			if (isset($_POST[CHtml::modelName($this->element)][$this->field]) && is_array($_POST[CHtml::modelName($this->element)][$this->field])) {
				foreach ($_POST[CHtml::modelName($this->element)][$this->field] as $proc_id) {
					$proc = Procedure::model()->findByPk($proc_id);
					$this->selected_procedures[] = array(
						'id' => $proc->id,
						'term' => $proc->term,
						'default_duration' => $proc->default_duration,
						'is_common' => false,
					);
				}
			}
		}

		if ($this->durations) {
			foreach ($this->selected_procedures as $proc) {
				$this->calculated_total_duration += $proc['default_duration'];
			}
		}

		$firm = Firm::model()->findByPk(Yii::app()->session['selected_firm_id']);
		$subspecialty_id = $firm->serviceSubspecialtyAssignment ? $firm->serviceSubspecialtyAssignment->subspecialty_id : null;
		if ($this->restrict_common == 'unbooked') {
			$this->subsections = array();
		} else {
			$this->subsections = SubspecialtySubsection::model()->getList($subspecialty_id);
		}
		$this->procedures = array();
		$this->procedures_options = array();
		if (empty($this->subsections)) {
			foreach (Procedure::model()->getListBySubspecialty($subspecialty_id, $this->restrict_common) as $proc_id => $name) {
				$found = false;
				if ($this->selected_procedures) {
					foreach ($this->selected_procedures as $procedure) {
						if ($procedure->id == $proc_id) {
							$found = true; break;
						}
					}
				}
				if (!$found) {
					$_proc = Procedure::model()->findByPk($proc_id);
					$this->procedures[$proc_id] = $name;
					$this->procedures_options[$proc_id] = array(
						'data-default-duration' => $_proc->default_duration,
					);
				} else {
					foreach ($this->selected_procedures as $i => $procedure) {
						if ($procedure['id'] == $proc_id) {
							$this->selected_procedures[$i]['is_common'] = true;
						}
					}
				}
			}
		}

		$this->class = get_class($this->element);

		$this->render(get_class($this));
	}

	public function render($view, $data=null, $return=false)
	{
		if ($this->layout) {
			$view .= '_'.$this->layout;
		}
		parent::render($view, $data, $return);
	}
}
