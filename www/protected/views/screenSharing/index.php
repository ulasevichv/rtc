<?php
$baseUrl = Yii::app()->theme->baseUrl;

Yii::app()->clientScript->registerCssFile($baseUrl.'/assets/css/screen_sharing.css');

Yii::app()->clientScript->registerScript(uniqid('chat_js'), "
	
	var streamingStarted = false;
	var mainStream = null;
	
	function validateRequirementsAndGetUserMedia()
	{
		// Browser versions examples:
		// Firefox: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:30.0) Gecko/20100101 Firefox/30.0
		// Chrome:  Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/537.36
		
		if (!location.protocol.match('https')) return 'You need to run this application from https';
		
		if (navigator.userAgent.match('Chrome'))
		{
			var browserMajorVersion = parseInt(navigator.userAgent.match(/ Chrome\/(.*)/)[1]);
			
			if (browserMajorVersion < 26) return 'You need Chrome 26+ to run this application';
			
			navigator.getUserMedia = navigator.webkitGetUserMedia;
		}
		else if (navigator.userAgent.match('Firefox'))
		{
			var browserMajorVersion = parseInt(navigator.userAgent.match(/ Firefox\/(.*)/)[1]);
			
			if (browserMajorVersion < 24) return 'You need Firefox 24+ to run this application';
			
			navigator.getUserMedia = navigator.mozGetUserMedia;
			
			return 'You need Chrome to run this application'; // Doesn't really work in Firefox.
		}
		else
		{
			return 'This application needs to be executed in Firefox or Chrome';
		}
		
		return '';
	}
	
	function getScreen()
	{
		var error = validateRequirementsAndGetUserMedia();
		
		if (error != '')
		{
			alert(error);
			return;
		}
		
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
		
		//
		
		var peerConnection = new webkitRTCPeerConnection(
			{ 'iceServers' : [{ 'url' : 'stun:stun.l.google.com:19302' }] }
		);
		
		peerConnection.onicecandidate = onIceCandidate;
		peerConnection.onaddstream = onAddStream;
		peerConnection.addStream(stream);
		
		console.log(peerConnection);
	}
	
	function onError(error)
	{
		if (typeof(error.code) != 'undefined' && error.code == error.PERMISSION_DENIED)
		{
			alert('PERMISSION_DENIED. Have you enabled the --enable-usermedia-screen-capture flag?');
		}
		
//		alert(JSON.stringify(e, null, '\t'));
		
		if (typeof(error.message) != 'undefined')
		{
			alert('An error occurred: ' + error.message);
		}
		else
		{
			alert('An error occurred: ' + error);
		}
		
		return;
	}
	
	function onEnded()
	{
		endStreaming();
	}
	
	function endStreaming()
	{
		streamingStarted = false;
		if (typeof(mainStream.stop) != 'undefined') mainStream.stop();
		mainStream = null;
		
		var jBtn = $('#btnStartScreenSharing');
		jBtn.html('Start');
	}
	
	function onIceCandidate()
	{
		
	}
	
	function onAddStream()
	{
		
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