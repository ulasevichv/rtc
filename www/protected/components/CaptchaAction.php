<?php

// Changes make captcha-image to be regenerated on each page reload and add correct handling of captcha expiration.
// See also customized class CaptchaValidator.
//
class CaptchaAction extends CCaptchaAction
{
	public function run()
	{
		if(isset($_GET[self::REFRESH_GET_VAR]))  // AJAX request for regenerating code
		{
			$code=$this->getVerifyCode(true);
			echo CJSON::encode(array(
				'hash1'=>$this->generateValidationHash($code),
				'hash2'=>$this->generateValidationHash(strtolower($code)),
				// we add a random 'v' parameter so that FireFox can refresh the image
				// when src attribute of image tag is changed
				'url'=>$this->getController()->createUrl($this->getId(),array('v' => uniqid())),
			));
		}
		else
			$this->renderImage($this->getVerifyCode(true));
		Yii::app()->end();
	}
	
	public function validate($input,$caseSensitive)
	{
		$code = $this->getVerifyCode();
		$valid = $caseSensitive ? ($input === $code) : strcasecmp($input,$code)===0;
		$session = Yii::app()->session;
		$session->open();
		$name = $this->getSessionKey() . 'count';
		$session[$name] = $session[$name] + 1;
		
		if ($this->testLimit > 0 && $session[$name] >= $this->testLimit + 2)
		{
			throw new Exception(Yii::t('general', 'Captcha has expired - only {testLimit} attempts are allowed. Please, reload the page.', array('{testLimit}' => $this->testLimit)));
		}
		
		return $valid;
	}
}