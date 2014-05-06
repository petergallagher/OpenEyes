<?php

class ReferenceTable extends BaseFieldWidget
{
	public $model;

	public function run()
	{
		$model_name = $this->model;
		if($_POST)
		{
			$display_order = 1;
			foreach($_POST as $key=>$value)
			{
				if(@substr($key, 0, 4)=="rto-"){ //only change order for existing values
					$id = substr($key, 4);
					if ($model = $model_name::model()->find("id=?",$id)) {
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
		$this->render(get_class($this),array('data'=>$rows));
	}
}
