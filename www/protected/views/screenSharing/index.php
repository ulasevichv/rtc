<?php
$baseUrl = Yii::app()->theme->baseUrl;

Yii::app()->clientScript->registerCssFile($baseUrl.'/assets/css/screen_sharing.css');

Yii::app()->clientScript->registerScript(uniqid('chat_js'), "
	
	var streamingStarted = false;
	var mainStream = null;
	
	function validateRequirements()
	{
		if (!location.protocol.match('https')) return 'You need to run this application from https';
		
		if (!(navigator.userAgent.match('Chrome') && parseInt(navigator.userAgent.match(/Chrome\/(.*) /)[1]) >= 26)) return 'You need Chrome 26+ to run this application';
		
		if (!navigator.getUserMedia && !navigator.webkitGetUserMedia) return 'navigator.getUserMedia is not supported by your browser';
		
		return '';
	}
	
	function getScreen()
	{
		var error = validateRequirements();
		
		if (error != '')
		{
			alert(error);
			return;
		}
		
		navigator.getUserMedia = navigator.webkitGetUserMedia || navigator.getUserMedia;
		
		navigator.getUserMedia(
			{
				audio : false,
				video : {
					mandatory : {
						chromeMediaSource: 'screen',
						maxWidth: 1920,
						maxHeight: 1080
					},
					optional : []
				}
			},
			onSuccess,
			onError
		);
	}
	
	function onSuccess(stream)
	{
		streamingStarted = true;
		mainStream = stream;
		
		stream.onended = function() { onEnded(); }; 
		
		console.log(stream);
		
		var jBtn = $('#btnStartScreenSharing');
		
		jBtn.html('Stop');
		
		var videoContainer = $('#video').get(0);
		
		videoContainer.src = window.URL.createObjectURL(stream);
		videoContainer.autoplay = true;
	}
	
	function onError(error)
	{
		if (error.code == error.PERMISSION_DENIED)
		{
			alert('PERMISSION_DENIED. Are you no SSL? Have you enabled the --enable-usermedia-screen-capture flag?');
		}
		else
		{
			console.log('An error occurred: ' + error.message);
			console.log(error);
			return;
		}
	}
	
	function onEnded()
	{
		endStreaming();
	}
	
	function endStreaming()
	{
		streamingStarted = false;
		mainStream.stop();
		mainStream = null;
		
		var jBtn = $('#btnStartScreenSharing');
		jBtn.html('Start');
	}
	
", CClientScript::POS_HEAD);

Yii::app()->clientScript->registerScript(uniqid('chat_js'), "
	
	$('#btnStartScreenSharing').on('click', function()
	{
		if (streamingStarted)
		{
			endStreaming();
		}
		else
		{
			getScreen();
		}
	});
	
", CClientScript::POS_READY);
?>

<div class="screenSharingRoot">
	<p align="center">
		<video id="video"/>
	</p>
	<p align="center">
		<button id="btnStartScreenSharing">Start</button>
	</p>
</div>