<?php

class RegisterForm extends CFormModel
{
	public $firstName;
	public $lastName;
	public $email;
	public $password;
	public $passwordRepeat;
	public $verifyCode;
	
	public function rules()
	{
		return array(
			array('firstName, lastName, email, password, passwordRepeat, verifyCode', 'required'),
			array('firstName, lastName', 'match', 'pattern' => '/^[[:alpha:]\- ]+$/u', 'message' => Yii::t('general', '{attribute} contains forbidden characters.')),
			array('email', 'email'),
			array('email', 'unique', 'className' => 'User', 'attributeName' => 'email', 'caseSensitive' => false, 'message' => Yii::t('general', 'Specified {attribute} is already registered.')),
			array('password', 'length', 'min' => 3),
			array('passwordRepeat', 'compare', 'compareAttribute' => 'password'),
			array('verifyCode', 'application.components.CaptchaValidator'),
		);
	}
	
	public function attributeLabels()
	{
		return array(
			'firstName' => Yii::t('general', 'First name'),
			'lastName' => Yii::t('general', 'Last name'),
			'email' => Yii::t('general', 'Email'),
			'password' => Yii::t('general', 'Password'),
			'passwordRepeat' => Yii::t('general', 'Repeat password'),
			'verifyCode' => Yii::t('general', 'Verification code'),
		);
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