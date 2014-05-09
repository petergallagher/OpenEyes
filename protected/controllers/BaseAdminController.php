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

class BaseAdminController extends BaseController
{
	public $layout = '//layouts/admin';
	public $items_per_page = 30;

	public function accessRules()
	{
		return array(array('allow', 'roles' => array('admin')));
	}

	protected function beforeAction($action)
	{
		Yii::app()->assetManager->registerCssFile('css/admin.css', null, 10);
		Yii::app()->assetManager->registerScriptFile('js/admin.js', null, 10);
		$this->jsVars['items_per_page'] = $this->items_per_page;
		return parent::beforeAction($action);
	}

	/**
	 *	@description Initialise and handle admin pagination
	 *  @author bizmate
	 * 	@param class $model
	 * 	@param string $criteria
	 * 	@return CPagination
	 */
	protected function initPagination($model, $criteria = null)
	{
		$criteria = is_null($criteria) ? new CDbCriteria() : $criteria;
		$itemsCount = $model->count($criteria);
		$pagination = new CPagination($itemsCount);
		$pagination->pageSize = $this->items_per_page;
		// not needed as $_GET['page'] is used by default
		//if(isset($_GET['page']) && is_int($_GET['page']) )
		//{
		//	$pagination->currentPage = $_GET['page'];
		//}
		$pagination->applyLimit($criteria);
		return $pagination;
	}

	public function referenceTableAdmin($model,$title)
	{
		$this->Render('//admin/referencetable',array('model'=>$model, 'title'=>$title));
	}

	public function handleReferenceTableAdmin($model_name)
	{
		if($_POST)
		{
			$display_order = 1;
			foreach($_POST as $key=>$value)
			{
				if(@substr($key, 0, 4)=="rto-"){ //only change order for existing values
					$id = substr($key, 4);
					$model = $model_name::model()->findByPk($id);

					if ($model) {
						$model->display_order = $display_order;
						$display_order ++;
						if (!$model->save()) {
							throw new Exception("Unable to save: ".print_r($model->getErrors(),true));
						}
					}
				}
				if(@substr($key, 0, 4)=="rtn-"){ //new value
					$model = new $model_name();
					$model->name = $value;
					$model->display_order = $display_order;
					$display_order ++;
					if (!$model->save()) {
						throw new Exception("Unable to save: ".print_r($model->getErrors(),true));
					}
				}
			}

		}

		$models=$model_name::model()->findAll(array('order'=>'display_order'));
		foreach($models as $model){
			$rows[] = $model->attributes;
		}

		$this->renderFile(Yii::app()->basePath.'/widgets/views/ReferenceTable.php',array('data'=>$rows));
	}


	public function actionAddReferenceTableRow()
	{
		return $this->renderFile(Yii::app()->basePath.'/widgets/views/_ReferenceTableRow.php',array('name'=>''));
	}
}
