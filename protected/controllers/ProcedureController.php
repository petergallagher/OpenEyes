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

class ProcedureController extends BaseController
{
	public function accessRules()
	{
		return array(
			array('allow',
				'roles' => array('OprnViewClinical'),
			),
		);
	}

	protected function beforeAction($action)
	{
		// Sample code to be used when RBAC is fully implemented.
//		if (!Yii::app()->user->checkAccess('admin')) {
//			throw new CHttpException(403, 'You are not authorised to perform this action.');
//		}

		return parent::beforeAction($action);
	}

	/**
	 * Lists all disorders for a given search term.
	 */
	public function actionAutocomplete()
	{
		echo CJavaScript::jsonEncode(Procedure::getList($_GET['term'], @$_GET['restrict'], @$_GET['subsection'], @$_GET['restrict_common']));
	}

	public function actionList()
	{
		if (!empty($_POST['subsection'])) {
			$criteria = new CDbCriteria;
			$criteria->select = 't.id, term, short_format, default_duration';
			$criteria->join = 'LEFT JOIN proc_subspecialty_subsection_assignment pssa ON t.id = pssa.proc_id';
			$criteria->compare('pssa.subspecialty_subsection_id', $_POST['subsection']);
			$criteria->order = 'term asc';

			$procedures = Procedure::model()->active()->findAll($criteria);

			$this->renderPartial('_procedureOptions', array('procedures' => $procedures), false, false);
		}
	}

	public function actionBenefits($id)
	{
		if (!Procedure::model()->findByPk($id)) {
			throw new Exception("Unknown procedure: $id");
		}

		$benefits = array();

		foreach (Yii::app()->db->createCommand()
			->select("b.name")
			->from("benefit b")
			->join("procedure_benefit pb","pb.benefit_id = b.id")
			->where("pb.proc_id = $id and b.active = 1")
			->order("b.name asc")
			->queryAll() as $row) {
			$benefits[] = $row['name'];
		}

		echo json_encode($benefits);
	}

	public function actionComplications($id)
	{
		if (!Procedure::model()->findByPk($id)) {
			throw new Exception("Unknown procedure: $id");
		}

		$complications = array();

		foreach (Yii::app()->db->createCommand()
			->select("b.name")
			->from("complication b")
			->join("procedure_complication pb","pb.complication_id = b.id")
			->where("pb.proc_id = $id and b.active = 1")
			->order("b.name asc")
			->queryAll() as $row) {
			$complications[] = $row['name'];
		}

		echo json_encode($complications);
	}

	public function actionGetProcedure()
	{
		if (!$procedure = Procedure::model()->findByPk(@$_GET['proc_id'])) {
			throw new Exception("Procedure not found: ".@$_GET['proc_id']);
		}

		foreach (array('element_class','field','eye_field') as $field) {
			if (strlen(@$_GET[$field]) == 0) {
				throw new Exception("Missing parameter in ajax request: ".$field);
			}

			if (!preg_match('/^[A-Za-z0-9_]*$/',@$_GET[$field])) {
				throw new Exception("Disallowed characters in ajax request field: ".@$_GET[$field]);
			}
		}

		$durations = @$_GET['durations'] == 'true' ? true : false;
		$read_only = @$_GET['read_only'] == 'true' ? true : false;
		$eye_field = @$_GET['eye_field'] != 'false' ? @$_GET['eye_field'] : false;

		$this->widget('application.widgets.ProcedureSelection',array(
			'row_only' => true,
			'selected_procedures' => array($procedure->id),
			'i' => (integer)@$_GET['i'],
			'element' => @$_GET['element_class'],
			'field' => @$_GET['field'],
			'eye_field' => $eye_field,
			'durations' => (boolean)$durations,
			'read_only' => (boolean)$read_only,
		));
	}
}
