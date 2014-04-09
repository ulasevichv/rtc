<?php

class UserIdentity extends CUserIdentity
{
	private $_id;
	
	public static function getPasswordFullHash($plainPassword)
	{
		$salt = MethodsForStrings::GenerateRandomString(16);
		
		$hash = md5($plainPassword.$salt);
		
		$hash = $hash.':'.$salt;
		
		return $hash;
	}
	
	public static function validatePassword($plainPassword, $fullHash)
	{
		usleep(250 * 1000);
		
		$parts = explode(':', $fullHash);
		
		$hash = $parts[0];
		$salt = $parts[1];
		
		$newHash = md5($plainPassword.$salt);
		
		return ($newHash == $hash);
	}
	
	public function authenticate()
	{
		$error = '';
		
		$userAR = User::model()->findByAttributes(array('email' => $this->username));
		
		if (!isset($userAR)) $error = self::ERROR_UNKNOWN_IDENTITY;
		
		if ($error == '')
		{
			$row = (object) $userAR->attributes;
			
			if (!self::validatePassword($this->password, $row->password)) $error = self::ERROR_PASSWORD_INVALID;
		}
		
		if ($error == '')
		{
			$this->_id = $row->id;
		}
		
		$this->errorCode = ($error == '' ? self::ERROR_NONE : $error);
		
		return !$this->errorCode;
	}
	
	public function getId()
	{
		return $this->_id;
	}
}