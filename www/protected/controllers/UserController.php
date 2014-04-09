<?php

class UserController extends Controller
{
	public function actions()
	{
		return array(
			'captcha' => array(
				'class' => 'application.components.CaptchaAction',
				'backColor' => 0xffffff,
				'foreColor' => 0x009900,
				'offset' => 1,
				'testLimit' => 3,
			),
		);
	}
	
	public function actionLogin()
	{
		$model = new LoginForm();
		
		$view = 'login';
		
		$data = array(
			'model' => $model,
		);
		
		if (isset($_POST['LoginForm']))
		{
			$model->attributes = $_POST['LoginForm'];
			
			if ($model->validate() && $model->login())
			{
				$this->redirect(Yii::app()->user->returnUrl);
			}
			else
			{
				Yii::app()->user->setFlash('error', Helper::modelErrorToString($model));
			}
		}
		
		Yii::app()->request->isAjaxRequest ? $this->renderPartial($view, $data, false, true) : $this->render($view, $data);
	}
	
	public function actionLoginValidate()
	{
		$model = new LoginForm();
		
		if (Yii::app()->request->isAjaxRequest)
		{
			echo CActiveForm::validate($model);
		}
		
		Yii::app()->end();
	}
	
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}
	
	public function actionRegister()
	{
		$model = new RegisterForm();
		
		if (isset($_POST['RegisterForm']))
		{
			$model->attributes = $_POST['RegisterForm'];
			
			if ($model->validate())
			{
				$error = $model->createNewUser();
				
				if ($error == '')
				{
					// Sending email.

					$email = new Email();

					$email->to = $model->email;
					$email->subject = 'Q-aces registration';

					$email->sendFromTemplate('registration', array('model' => $model));
					
					// Redirecting.
					
					Yii::app()->user->setFlash('success', Yii::t('general', 'Thank you for registration. Check your email or just login, using provided credentials.'));
					$this->redirect(array('/site/msg'));
				}
				else
				{
					Yii::app()->user->setFlash('error', $error);
				}
			}
			else
			{
				Yii::app()->user->setFlash('error', Helper::modelErrorToString($model));
			}
		}
		
		$this->render('register', array('model' => $model));
	}
	
	public function actionRegisterValidate()
	{
		$model = new RegisterForm();
		
		if (Yii::app()->request->isAjaxRequest)
		{
			echo CActiveForm::validate($model);
		}
		
		Yii::app()->end();
	}
}