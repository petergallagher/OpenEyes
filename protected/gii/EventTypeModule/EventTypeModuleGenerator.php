<?php

class EventTypeModuleGenerator extends ModuleGenerator // CCodeGenerator
{
	public $codeModel='application.gii.EventTypeModule.EventTypeModuleCode';
	public $form_errors = array();
	public $target_class;

	public function actionIndex() {

		//if a template is uploaded inject into page state
		if($_FILES) {
			$template = json_decode((file_get_contents($_FILES['GiiTemplate']['tmp_name']['template'])),true);
			$template['YII_CSRF_TOKEN']=$_POST['YII_CSRF_TOKEN'];
			$template['preview'] = 'Preview';
			unset($template['generate']);
			$_POST = $template;
			$_REQUEST = $template;
		}

		parent::actionIndex();
	}






}
