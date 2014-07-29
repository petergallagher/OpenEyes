<?php

class InstitutionTest extends CDbTestCase
{
	protected $fixtures = array(
		'import_sources' => 'ImportSource',
		'institutions' => 'Institution',
	);

	public function testGetCurrent_Success()
	{
		Yii::app()->params['institution_code'] = 'foo';
		$this->assertEquals($this->institutions('moorfields'), Institution::model()->getCurrent());
	}

	/**
	 * @expectedException Exception
	 * @expectedExceptionMessage Institution code is not set
	 */
	public function testGetCurrent_CodeNotSet()
	{
		unset(Yii::app()->params['institution_code']);
		Institution::model()->getCurrent();
	}

	public function testGetCurrent_NotFound()
	{
		Yii::app()->params['institution_code'] = 'bar';
		$this->assertEquals($this->institutions('moorfields'), Institution::model()->getCurrent());
	}
}
