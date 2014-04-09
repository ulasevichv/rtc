<?php

// Changes add correct handling of captcha expiration.
// See also customized class CaptchaAction.
//
class CaptchaValidator extends CCaptchaValidator
{
	protected function validateAttribute($object,$attribute)
	{
		$value=$object->$attribute;
		if($this->allowEmpty && $this->isEmpty($value))
			return;
		$captcha=$this->getCaptchaAction();
		// reason of array checking is explained here: https://github.com/yiisoft/yii/issues/1955
		if (is_array($value))
		{
			$message = ($this->message !== null ? $this->message : Yii::t('yii', 'The verification code is incorrect.'));
			$this->addError($object, $attribute, $message);
		}
		else
		{
			try
			{
				if (!$captcha->validate($value, $this->caseSensitive))
				{
					$message = ($this->message !== null ? $this->message : Yii::t('yii', 'The verification code is incorrect.'));
					$this->addError($object, $attribute, $message);
				}
			}
			catch (Exception $ex)
			{
				$message = ($this->message !== null ? $this->message : $ex->getMessage());
				$this->addError($object, $attribute, $message);
			}
		}
	}
}