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
	
	public function actionSaveOffer()
	{
		@ob_clean();
		header('Expires: Thu, 01 Jan 1970 00:00:01 GMT');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		header('Cache-Control: no-cache, must-revalidate');
		header('Pragma: no-cache');
		header('Content-Type: text/plain; charset=utf-8');
		
		$error = '';
		$offer = null;
		
		try
		{
			$offer = $this->getVar('offer', array(ParamCondition::NOT_EMPTY));

			$fileFullName = Yii::app()->basePath.'/runtime/rtc_offer.txt';
			$handle = @fopen($fileFullName, 'w+');
			if ($handle === false) throw new Exception(Yii::t('general', 'Cannot write to file').': '.$fileFullName);
			fwrite($handle, json_encode($offer));
			fclose($handle);
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
			$result->offer = $offer;
		}
		
		echo json_encode($result);
		
		Yii::app()->end();
	}
}