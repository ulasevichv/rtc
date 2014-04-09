<?php

class TestController extends Controller
{
	public function actionGetPwd()
	{
		$plainPassword = '123456';
		
		$fullHash = UserIdentity::getPasswordFullHash($plainPassword);
		
		echo '<br/><b>$plainPassword: </b>'.htmlspecialchars($plainPassword).'<br/>';
		echo '<br/><b>$fullHash: </b>'.htmlspecialchars($fullHash).'<br/>';
		
		Yii::app()->end();
	}
	
	public function actionCheckPwd()
	{
		$plainPassword = '123456';
		$fullHash = '8e42a6338021335f2492983f8f178ad9:Jbx8TpAalSHIdaX8';
		
		echo '<br/><b>$plainPassword: </b>'.htmlspecialchars($plainPassword).'<br/>';
		echo '<br/><b>$fullHash: </b>'.htmlspecialchars($fullHash).'<br/>';
		
		$result = UserIdentity::validatePassword($plainPassword, $fullHash);
		
		echo '<br/><b>$result: </b>' . ($result ? 'true' : 'false') . '<br/>';
		
		Yii::app()->end();
	}
	
	public function actionPreviewEmail()
	{
		$realEmail = 'INPUT REAL EMAIL HERE';
		
		$model = new RegisterForm();
		
		$model->setAttributes(array(
			'firstName' => 'FIRSTNAME',
			'lastName' => 'LASTNAME',
			'email' => $realEmail,
			'password' => '123456',
		), false);
		
		$templateName = 'registration';
		
		$templateData = array(
			'model' => $model,
		);
		
		echo Yii::app()->controller->renderPartial('//email/'.$templateName, $templateData, true);
		
		if (true)
		{
			echo '<div style="float:left; clear:both; margin-top:50px;">=== SENDING EMAIL ===';
			
			$email = new Email();
			
			$email->to = $realEmail;
			$email->subject = 'Yii site registration';
			
			$result = $email->sendFromTemplate($templateName, $templateData);

			echo '<br/><b>$result: </b>' . ($result ? 'true' : 'false');
			echo '</div>';
		}
		
		Yii::app()->end();
	}
}