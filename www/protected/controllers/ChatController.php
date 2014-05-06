<?php

class ChatController extends Controller
{
	protected function beforeAction($action)
	{
		if (empty(Yii::app()->user->id))
		{
			if (Yii::app()->request->isAjaxRequest)
			{
				echo Yii::t('general', 'Login required');
				Yii::app()->end();
			}
			else
			{
				Yii::app()->user->returnUrl = array(Yii::app()->controller->getId().'/'.Yii::app()->controller->getAction()->getId());
				
				Yii::app()->user->setFlash('info', Yii::t('general', 'Login required'));
				
				$this->redirect(array('/user/login'));
			}
		}
		
		return true;
	}
	
	public function actions()
	{
	}
	
	public function actionIndex()
	{
		$this->render('index');
	}
	
	public function actionVideoCall() {
		
		$apiObj = new OpenTokSDK();
		
		$session = $apiObj->createSession(null, array(SessionPropertyConstants::P2P_PREFERENCE => 'disabled'));
		
		$token = $apiObj->generateToken($session->getSessionId(), RoleConstants::MODERATOR);
		
		$script = "
			var OTvideo = OTvideo || {};
			
			OTvideo.apiKey = ".Yii::app()->params['opentok_api_key'].";
			OTvideo.sessionId = '{$session->getSessionId()}';
			OTvideo.token = '{$token}';
			OTvideo.default_user_id = ".Yii::app()->user->id.";
			OTvideo.username = '<div class=\"ot-username\" data-uid=\"".Yii::app()->user->id."\">".Yii::app()->user->firstName."</div>';
			OTvideo.init();
			OTvideo.checker();
		
		";
		
		$response = array(
			'apiKey' => Yii::app()->params['opentok_api_key'],
			'sessionId' => $session->getSessionId(),
			'token' => $token,
		);
		
		echo json_encode($response);
		
		Yii::app()->end();
	}
	
	public function actionGetVideoCallToken()
	{
		$apiObj = new OpenTokSDK();
		
		$token = $apiObj->generateToken($_POST['sessionId'], RoleConstants::PUBLISHER);
		
		echo $token;
		
		Yii::app()->end();
	}
}