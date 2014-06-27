<?php
Yii::app()->clientScript->registerScript(uniqid('sh_presenter_js'), "
	
	//==================================================
	// Screen sharing presenter.
	//==================================================
	
	ScreenSharingPresenter.prototype = new ScreenSharingPeer();
	
	function ScreenSharingPresenter()
	{
		this.screenBeingCaptured = false;
		this.screenStream = null;
		this.peerConnections = [];
		
		this.onScreenCaptureStartCallback = null;
		this.onScreenCaptureFinishCallback = null;
	}
	
	ScreenSharingPresenter.prototype.validateRequirementsAndGetUniversalObjects = function()
	{
		var error = ScreenSharingPresenter.prototype._validateRequirementsAndGetUniversalObjects.call(this);
		
		if (error != '') return error;
		
		if (navigator.userAgent.match('Firefox'))
		{
			return '".Yii::T('general', 'You need Chrome for this functionality')."';
		}
		
		return '';
	}
	
	ScreenSharingPresenter.prototype.startScreenCapturing = function()
	{
		var inst = this;
		
		navigator.getUserMedia(
			{
				audio : false,
				video : {
					mandatory : {
						chromeMediaSource : 'screen',
						maxWidth : 1920,
						maxHeight : 1080
					},
					optional : []
				}
			},
			function(stream) { inst.onScreenStreamCaptured(inst, stream) },
			inst.onError
		);
	}
	
	ScreenSharingPresenter.prototype.onScreenStreamCaptured = function(inst, stream)
	{
		inst.screenBeingCaptured = true;
		inst.screenStream = stream;
		
		stream.onended = function() { inst.onScreenCapturingEnded(inst); };
		
//		var jBtn = $('#btnStartScreenSharing');
//		jBtn.html('Stop');
//		
//		var jBtnConnect = $('#btnConnect');
//		jBtnConnect.attr('disabled', '');
//		
//		var videoContainer = $('#video').get(0);
//		videoContainer.src = window.URL.createObjectURL(stream);
//		videoContainer.autoplay = true;
		
		inst.onScreenCaptureStartCallback(stream);
		
//		inst.createPeerConnection();
	}
	
	ScreenSharingPresenter.prototype.onScreenCapturingEnded = function(inst)
	{
		inst.finishScreenCapturing();
	}
	
	ScreenSharingPresenter.prototype.finishScreenCapturing = function()
	{
		this.screenBeingCaptured = false;
		
		if (this.screenStream != null && this.screenStream.stop != null && typeof(this.screenStream.stop) != 'undefined')
		{
			this.screenStream.stop();
			this.screenStream = null;
		}
		
		for (var i = 0; i < this.peerConnections.length; i++)
		{
			var pc = this.peerConnections[i];
			
			if (pc.close != null && typeof(pc.close) != 'undefined')
			{
				pc.close();
				pc = null;
			}
		}
		
		this.peerConnections = [];
		
//		var jBtn = $('#btnStartScreenSharing');
//		jBtn.html('Start');
		
		this.onScreenCaptureFinishCallback();
	}
	
	ScreenSharingPresenter.prototype.saveOffer = function(offer)
	{
//		var jBtnAccept = $('#btnAccept');
//		jBtnAccept.removeAttr('disabled');
//		
//		var request = $.ajax({
//			url : '?r=screenSharing/saveKey',
//			data : { type : RtcKeyType.Offer, key : offer },
//			type : 'POST',
//			dataType : 'json',
//			cache : false,
//			timeout : 5000
//		});
//		
//		var inst = this;
//		
//		request.success(function(response, status, request)
//		{
//			if (response.error != '')
//			{
//				alert(response.error);
//				return;
//			}
//			
//			console.log(response);
//		});
//		
//		request.error(inst.requestTimedOut);
	}
	
	ScreenSharingPresenter.prototype.acceptAnswer = function()
	{

		var request = $.ajax({
			url : '?r=screenSharing/getKey',
			data : { type : RtcKeyType.Answer },
			type : 'POST',
			dataType : 'json',
			cache : false,
			timeout : 5000
		});
		var inst = this;
		
		request.success(function(response, status, request)
		{
			if (response.error != '')
			{
				alert(response.error);
				return;
			}
			
			var answer = response.key;
 			console.log('------------------------------');
 			var latestPeerConnection = inst.peerConnections[inst.peerConnections.length - 1];
 			console.log(latestPeerConnection);
 			console.log(inst);
 			console.log(inst.peerConnections);
 			latestPeerConnection.setRemoteDescription(new SessionDescription(answer), function() { }, inst.onError);
 			console.log('------------------------------');
 			inst.createPeerConnection();
		});
		
		request.error(inst.requestTimedOut);
	}
	
	ScreenSharingPresenter.prototype.createPeerConnection = function()
	{
		if (!this.screenBeingCaptured || this.screenStream == null)
		{
			alert('".Yii::t('general', 'Screen sharing is not started')."');
			return;
		}
		
		var pc = new PeerConnection(
			{ 'iceServers' : [{ 'url' : this.mainIceServerUrl }] }
		);
		
		var inst = this;
		
		pc.onicecandidate = function(iceCandidateEvent) { inst.onIceCandidate(inst, iceCandidateEvent); };
		pc.onaddstream = function(stream) { inst.onAddStream(inst, stream); };
		
		pc.addStream(this.screenStream);
		
		pc.createOffer(function(offer)
		{
			pc.setLocalDescription(new SessionDescription(offer), function()
			{
				
				inst.saveOffer(offer);
				
			}, inst.onError);
		}, inst.onError);
		
		this.peerConnections.push(pc);
	}
	
", CClientScript::POS_HEAD);