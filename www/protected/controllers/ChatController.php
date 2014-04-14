<?php

class ChatController extends Controller
{
	protected function beforeAction($action)
	{
		if (empty(Yii::app()->user->id))
		{
			if (Yii::app()->request->isAjaxRequest)
			{
				echo Yii::t('general', 'Login required');
				Yii::app()->end();
			}
			else
			{
				Yii::app()->user->returnUrl = array(Yii::app()->controller->getId().'/'.Yii::app()->controller->getAction()->getId());
				
				Yii::app()->user->setFlash('info', Yii::t('general', 'Login required'));
				
				$this->redirect(array('/user/login'));
			}
		}
		
		return true;
	}
	
	public function actions()
	{
	}
	
	public function actionIndex()
	{
		$this->render('index');
	}
}