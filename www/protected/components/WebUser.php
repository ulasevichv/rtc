<?php

class WebUser extends CWebUser
{
	public $dbUser = null;
	public $email = '';
	public $firstName = '';
	public $lastName = '';
	public $fullName = '';
	public $password = '';
	
	public function init()
	{
		parent::init();
		
		if (!empty($this->id))
		{
			$userAR = User::model()->findByPk($this->id);
			
			$this->dbUser = (object) $userAR->attributes;
			
			$this->email = $this->dbUser->email;
			$this->firstName = $this->dbUser->firstName;
			$this->lastName = $this->dbUser->lastName;
			$this->fullName = $this->dbUser->firstName.' '.$this->dbUser->lastName;
			$this->password = $this->dbUser->password;
		}
	}
}