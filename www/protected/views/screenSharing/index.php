<?php
$baseUrl = Yii::app()->theme->baseUrl;

Yii::app()->clientScript->registerCssFile($baseUrl.'/assets/css/screen_sharing.css');

$this->renderPartial('sh_peer_js', array(), false, false);
$this->renderPartial('sh_presenter_js', array(), false, false);
$this->renderPartial('sh_viewer_js', array(), false, false);

Yii::app()->clientScript->registerScript(uniqid('main_js'), "
	
	var screenSharingPeer = null;
	
", CClientScript::POS_HEAD);

Yii::app()->clientScript->registerScript(uniqid('main_js'), "
	
	$('#btnStartScreenSharing').on('click', function()
	{
		if (screenSharingPeer == null)
		{
			screenSharingPeer = new ScreenSharingPresenter();
			
			var error = screenSharingPeer.validateRequirementsAndGetUniversalObjects();
			
			if (error != '')
			{
				screenSharingPeer = null;
				alert(error);
				return;
			}
		}
		
		if (screenSharingPeer.screenBeingCaptured)
		{
			screenSharingPeer.finishScreenCapturing();
		}
		else
		{
			screenSharingPeer.startScreenCapturing();
		}
	});
	
	$('#btnConnect').on('click', function()
	{
		if (screenSharingPeer == null)
		{
			screenSharingPeer = new ScreenSharingViewer();
			
			var error = screenSharingPeer.validateRequirementsAndGetUniversalObjects();
			
			if (error != '')
			{
				screenSharingPeer = null;
				alert(error);
				return;
			}
		}
		
		screenSharingPeer.connectToScreenSharing();
	});
	
	$('#btnAccept').on('click', function()
	{
		screenSharingPeer.acceptAnswer();
	});
	
", CClientScript::POS_READY);
?>

<div class="screenSharingRoot">
	<p>
		<video id="video" autoplay="true" controls="true"/>
	</p>
	<p>
		<button id="btnStartScreenSharing">Start</button>
	</p>
	<p>
		<button id="btnConnect">Connect</button>
	</p>
	<p>
		<button id="btnAccept" disabled>Accept</button>
	</p>
</div>