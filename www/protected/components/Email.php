<?php

class Email extends CComponent
{
	public $fromEmail;
	public $fromName;
	public $to;
	public $subject;
	
	public function __construct()
	{
		$this->fromEmail = Yii::app()->params['adminEmail'];
		$this->fromName = Yii::app()->params['adminEmailName'];
	}
	
	private function checkInitErrors()
	{
		if (empty($this->fromEmail)) throw new Exception(Yii::t('general', 'Parameter is undefined').': '.'fromEmail');
		if (empty($this->fromName)) throw new Exception(Yii::t('general', 'Parameter is undefined').': '.'fromName');
		if (empty($this->to)) throw new Exception(Yii::t('general', 'Parameter is undefined').': '.'to');
		if (empty($this->subject)) throw new Exception(Yii::t('general', 'Parameter is undefined').': '.'subject');
	}
	
	public function send($content)
	{
		$this->checkInitErrors();
		
		if (empty($content)) throw new Exception(Yii::t('general', 'Parameter is undefined').': '.'content');
		
		$headers = "From: ".$this->fromName." <".$this->fromEmail.">\r\n".
			"MIME-Version: 1.0\r\n".
			"Content-Type: text/plain; charset=UTF-8";
		
		return mail($this->to, $this->subject, $content, $headers);
	}
	
	// Templates are PHP-files in views/email.
	//
	public function sendFromTemplate($templateName, $templateData = null)
	{
		$this->checkInitErrors();
		
		if (empty($templateName)) throw new Exception(Yii::t('general', 'Parameter is undefined').': '.'templateName');
		
		$content = Yii::app()->controller->renderPartial('//email/'.$templateName, $templateData, true);

		$headers = "From: ".$this->fromName." <".$this->fromEmail.">\r\n".
			"MIME-Version: 1.0\r\n".
			"Content-Type: text/html; charset=UTF-8";
		
		return mail($this->to, $this->subject, $content, $headers);
	}
}