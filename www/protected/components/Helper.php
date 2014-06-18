<?php

class Helper
{
	public static function modelErrorToString(CModel $model)
	{
		$msg = '';
		
		if ($model->hasErrors())
		{
			$errors = $model->getErrors();
			
			foreach ($errors as $key => $value)
			{
				$msg = $key.' - '.$value[0];
				break;
			}
		}
		
		return $msg;
	}
	
	public static function getProtocolFromUrl($url)
	{
		if (strpos($url, 'http://') === 0) return 'http';
		else if (strpos($url, 'https://') === 0) return 'https';
		else return '';
	}
	
	public static function getCurrentPageProtocol()
	{
		return self::getProtocolFromUrl(self::getCurrentPageUrl());
	}
	
	public static function getCurrentPageUrl()
	{
		return Yii::app()->createAbsoluteUrl(Yii::app()->controller->getId().'/'.Yii::app()->controller->getAction()->getId());
	}
}