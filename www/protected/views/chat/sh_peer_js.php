<?php
Yii::app()->clientScript->registerScript(uniqid('sh_peer_js'), "
	
	var PeerConnection = null;
	var SessionDescription = null;
	
	var RtcKeyType = {
		Offer : 'offer',
		Answer : 'answer'
	};
	
	//==================================================
	// Screen sharing peer (base class for presenter and viewer).
	//==================================================
	
	ScreenSharingPeer.prototype = new Object();
	
	function ScreenSharingPeer()
	{
		this.mainIceServerUrl = 'stun:stun.l.google.com:19302';
	}
	
	ScreenSharingPeer.prototype._validateRequirementsAndGetUniversalObjects = function()
	{
		// Browser versions examples:
		// Firefox: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:30.0) Gecko/20100101 Firefox/30.0
		// Chrome:  Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/537.36
		
		if (!location.protocol.match('https')) return '".Yii::t('general', 'You need HTTPs for this functionality')."';
		
		if (navigator.userAgent.match('Chrome'))
		{
			var browserMajorVersion = parseInt(navigator.userAgent.match(/ Chrome\/(.*)/)[1]);
			
			if (browserMajorVersion < 26) return '".Yii::t('general', 'You need Chrome 26+ for this functionality')."';
			
			navigator.getUserMedia = navigator.webkitGetUserMedia;
			PeerConnection = window.webkitRTCPeerConnection;
			SessionDescription = window.RTCSessionDescription; // window.webkitRTCSessionDescription; - not working!
		}
		else if (navigator.userAgent.match('Firefox'))
		{
			var browserMajorVersion = parseInt(navigator.userAgent.match(/ Firefox\/(.*)/)[1]);
			
			if (browserMajorVersion < 24) return '".Yii::t('general', 'You need Firefox 24+ for this functionality')."';
			
			navigator.getUserMedia = navigator.mozGetUserMedia;
			PeerConnection = window.mozRTCPeerConnection;
			SessionDescription = window.mozRTCSessionDescription;
		}
		else
		{
			return '".Yii::t('general', 'You need Firefox or Chrome for this functionality')."';
		}
		
		return '';
	}
	
	ScreenSharingPeer.prototype.validateRequirementsAndGetUniversalObjects = function()
	{
		return this._validateRequirementsAndGetUniversalObjects();
	}
	
	ScreenSharingPeer.prototype.onIceCandidate = function(inst, iceCandidateEvent)
	{
//		console.log('onIceCandidate()');
//		console.log(iceCandidateEvent);
	}
	
	ScreenSharingPeer.prototype.onAddStream = function(inst, stream)
	{
		console.log('onAddStream()');
		console.log(stream);
	}
	
	ScreenSharingPeer.prototype.requestTimedOut = function(request, status, error)
	{
		if (status == 'timeout') alert('".Yii::t('general', 'Request timed out. Please, try again')."');
	}
	
	ScreenSharingPeer.prototype.onError = function(error)
	{
		console.log('onError()');
		console.log(error);
		
		if (typeof(error.code) != 'undefined' && error.code == error.PERMISSION_DENIED)
		{
			alert('".Yii::t('general', 'PERMISSION_DENIED. Have you enabled the --enable-usermedia-screen-capture flag?')."');
		}
		
		alert('".Yii::t('general', 'An error occurred')."' + ': ' + JSON.stringify(error, null, '\t'));
		
		return;
	}
	
", CClientScript::POS_HEAD);