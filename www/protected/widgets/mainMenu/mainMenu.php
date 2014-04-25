<?php
Yii::import('system.web.widgets.CWidget');
Yii::import('zii.widgets.CMenu');

class mainMenu extends CWidget
{
	public function init()
	{
		return parent::init();
	}
	
	public function run()
	{
		$this->render('default', array(
		));
	}
}