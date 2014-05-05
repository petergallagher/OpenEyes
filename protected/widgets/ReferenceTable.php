<?php

class ReferenceTable extends BaseFieldWidget
{
	public $model;

	public function run()
	{
		$model_name = $this->model;
		$models=$model_name::model()->findAll();
		foreach($models as $model){
			$rows[] = $model->attributes;
		}
		$this->render(get_class($this),array('data'=>$rows));
	}
}
