<?php

class ReferenceTable extends BaseFieldWidget
{
	public $model;

	public function run()
	{
		//TODO: had to stop working on this due to time needs refactor

		$model_name = $this->model;
		if($_POST)
		{
			$display_order = 1;
			foreach($_POST as $key=>$value)
			{
				if(@substr($key, 0, 4)=="rto-"){
					$id = substr($key, 4);
					if ($model = $model_name::model()->find("id=?",$id)) {
						$model->name = $value;
						$model->display_order = $display_order;
						$display_order ++;
						if (!$model->save()) {
							throw new Exception("Unable to save: ".print_r($model->getErrors(),true));
						}
					}
				}
				if(@substr($key, 0, 4)=="rtn-"){
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
		$this->render(get_class($this),array('data'=>$rows));
	}
}
