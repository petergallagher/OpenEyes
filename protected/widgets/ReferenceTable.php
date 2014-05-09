<?php

class ReferenceTable extends BaseFieldWidget
{
	public $model;

	public function run()
	{
		Yii::app()->controller->handleReferenceTableAdmin($this->model);
	}
}
