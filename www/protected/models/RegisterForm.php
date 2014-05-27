<?php

class RegisterForm extends CFormModel
{
	public $firstName;
	public $lastName;
	public $email;
	public $password;
	public $passwordRepeat;
	public $verifyCode;
	public $xmppUserName;
	public $xmppUserPassword;
	public $useCaptcha = true;
	
	public function rules()
	{
		$rules = array(
			array('firstName, lastName, email, password, passwordRepeat', 'required'),
			array('firstName, lastName', 'match', 'pattern' => '/^[[:alpha:]\- ]+$/u', 'message' => Yii::t('general', '{attribute} contains forbidden characters.')),
			array('email', 'email'),
			array('email', 'unique', 'className' => 'User', 'attributeName' => 'email', 'caseSensitive' => false, 'message' => Yii::t('general', 'Specified {attribute} is already registered.')),
			array('password', 'length', 'min' => 3),
			array('passwordRepeat', 'compare', 'compareAttribute' => 'password'),
		);
		
		if ($this->useCaptcha)
		{
			$rules[] = array('verifyCode', 'required');
			$rules[] = array('verifyCode', 'application.components.CaptchaValidator');
		}
		
		return $rules;
	}
	
	public function attributeLabels()
	{
		$labels = array(
			'firstName' => Yii::t('general', 'First name'),
			'lastName' => Yii::t('general', 'Last name'),
			'email' => Yii::t('general', 'Email'),
			'password' => Yii::t('general', 'Password'),
			'passwordRepeat' => Yii::t('general', 'Repeat password'),
		);
		
		if ($this->useCaptcha)
		{
			$labels['verifyCode'] = Yii::t('general', 'Verification code');
		}
		
		return $labels;
	}
	
	public function createNewUser()
	{
		$userModel = User::model();
		
		$userModel->setAttributes($this->attributes, false);
		
		$userModel->setIsNewRecord(true);
		$userModel->save();
		
		if ($userModel->hasErrors()) return Helper::modelErrorToString($userModel);
		
		return '';
	}
}