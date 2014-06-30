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
				'testLimit' => 0,
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
			$model->setAttributes($_POST['RegisterForm'], false);
			
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
	
	public function actionAddXmppUserToGroup()
	{
		$error = '';

		try
		{
			$xmppUserName = Yii::app()->request->getParam('xmppUserName');
			$xmppGroupName = Yii::app()->request->getParam('xmppGroupName');
			
			if (!isset($xmppUserName)) throw new Exception(Yii::t('general', 'Undefined parameter').': '.'xmppUserName');
			if (!isset($xmppGroupName)) throw new Exception(Yii::t('general', 'Undefined parameter').': '.'xmppGroupName');
			
//			$db = Yii::app()->openFireDb;
//
//			$query = "SELECT COUNT(*) FROM `ofGroupUser`" .
//				" WHERE `groupName` = ".$db->quoteValue($xmppGroupName)." AND `username` = ".$db->quoteValue($xmppUserName);
//			$count = $db->createCommand($query)->queryScalar();
//
//			if ($count != 0) throw new Exception(Yii::t('general', 'Username already registered').': '.$xmppUserName);
//
//			$query = "INSERT INTO `ofGroupUser` (`groupName`, `username`, `administrator`)" .
//				" VALUES (".$db->quoteValue($xmppGroupName).", ".$db->quoteValue($xmppUserName).", 0)";
//			$db->createCommand($query)->execute();
		}
		catch (Exception $ex)
		{
			$error = $ex->getMessage();
		}
		
		$result = (object) array(
			'error' => $error,
		);
		
		echo json_encode($result);
		
		Yii::app()->end();
	}
}