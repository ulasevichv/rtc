<?php
Yii::import('application.components.AdvancedController');

class ScreenSharingController extends AdvancedController
{
	public function actions()
	{
	}
	
	public function actionIndex()
	{
		$this->render('index');
	}
	
	public function actionSaveKey()
	{
		@ob_clean();
		header('Expires: Thu, 01 Jan 1970 00:00:01 GMT');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		header('Cache-Control: no-cache, must-revalidate');
		header('Pragma: no-cache');
		header('Content-Type: text/plain; charset=utf-8');
		
		$error = '';
		
		try
		{
			$type = $this->getVar('type', array(ParamCondition::NOT_EMPTY));
			$key = $this->getVar('key', array(ParamCondition::NOT_EMPTY));
			
			$fileFullName = Yii::app()->basePath.'/runtime/';
			
			switch ($type)
			{
				case 'offer': $fileFullName .= 'rtc_offer.txt'; break;
				case 'answer': $fileFullName .= 'rtc_answer.txt'; break;
			}
			
			$handle = @fopen($fileFullName, 'w+');
			if ($handle === false) throw new Exception(Yii::t('general', 'Cannot write to file').': '.$fileFullName);
			fwrite($handle, json_encode($key));
			fclose($handle);
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
	
	public function actionGetKey()
	{
		@ob_clean();
		header('Expires: Thu, 01 Jan 1970 00:00:01 GMT');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		header('Cache-Control: no-cache, must-revalidate');
		header('Pragma: no-cache');
		header('Content-Type: text/plain; charset=utf-8');
		
		$error = '';
		$key = null;
		
		try
		{
			$type = $this->getVar('type', array(ParamCondition::NOT_EMPTY));
			
			$fileFullName = Yii::app()->basePath.'/runtime/';
			
			switch ($type)
			{
				case 'offer': $fileFullName .= 'rtc_offer.txt'; break;
				case 'answer': $fileFullName .= 'rtc_answer.txt'; break;
			}
			
			$handle = @fopen($fileFullName, 'r+');
			if ($handle === false) throw new Exception(Yii::t('general', 'Cannot read file').': '.$fileFullName);
			$content = fread($handle, filesize($fileFullName));
			fclose($handle);
			
			$key = json_decode($content);
		}
		catch (Exception $ex)
		{
			$error = $ex->getMessage();
		}
		
		$result = (object) array(
			'error' => $error,
		);
		
		if ($error == '')
		{
			$result->key = $key;
		}
		
		echo json_encode($result);
		
		Yii::app()->end();
	}
}