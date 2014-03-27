<?php
class GiiTemplate extends CFormModel
{
	public $template;

	public function rules()
	{
		return array(
				array('template', 'file', 'types'=>'gii'),
        );
    }
}
