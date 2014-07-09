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
		this.onScreenCaptureStartCallback = null;
		this.onScreenCaptureFinishCallback = null;
		this.onOfferCreatedCallback = null;
		this.onAnswerAcceptedCallback = null;
		this.clients = [];
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
	
	ScreenSharingPresenter.prototype.getClientById = function(id)
	{
		for (var i = 0; i < this.clients.length; i++)
		{
			var client = this.clients[i];
			
			if (client.id == id) return client;
		}
		
		return null;
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
			function(stream) { inst.onScreenStreamCaptured(inst, stream); },
			function(error) { /* inst.onError(error); // Preventing redundant error when clicking 'No' in confirmation dialog */ }
		);
	}
	
	ScreenSharingPresenter.prototype.onScreenStreamCaptured = function(inst, stream)
	{
		inst.screenBeingCaptured = true;
		inst.screenStream = stream;
		
		stream.onended = function() { inst.onScreenCapturingEnded(inst); };
		
		inst.onScreenCaptureStartCallback(stream);
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
		
		var activeClientIds = [];
		
		for (var i = 0; i < this.clients.length; i++)
		{
			var client = this.clients[i];
			
			activeClientIds.push(client.id);
			
			var pc = client.peerConnection;
			
			if (pc.close != null && typeof(pc.close) != 'undefined')
			{
				pc.close();
				pc = null;
			}
		}
		
		this.clients = [];
		
		this.onScreenCaptureFinishCallback(activeClientIds);
	}
	
	ScreenSharingPresenter.prototype.createPeerConnection = function(clientId)
	{
		if (!this.screenBeingCaptured || this.screenStream == null)
		{
//			alert('".Yii::t('general', 'Screen sharing is not started')."');
			return;
		}
		
		var pc = new PeerConnection(
			{ 'iceServers' : [{ 'url' : this.mainIceServerUrl }] }
		);
		
		var inst = this;
		
		pc.onicecandidate = function(iceCandidateEvent) { inst.onIceCandidate(inst, iceCandidateEvent); };
		pc.onaddstream = function(stream) { inst.onAddStream(inst, stream); };
		
		pc.addStream(this.screenStream);
		
		this.clients.push(new ScreenSharingPresenterClient(clientId, pc));
		
		pc.createOffer(function(offer)
		{
			pc.setLocalDescription(new SessionDescription(offer), function()
			{
				var client = inst.getClientById(clientId);
				
				client.offer = offer;
				inst.onOfferCreatedCallback(clientId, offer);
			},
			inst.onError);
		},
		inst.onError);
	}
	
	ScreenSharingPresenter.prototype.acceptAnswer = function(clientId, answer)
	{
		var client = this.getClientById(clientId);
		
		console.log('ACCEPTING ANSWER');
		console.log(client);
		console.log(answer);
		
		var inst = this;
		
		client.peerConnection.setRemoteDescription(new SessionDescription(answer), function()
		{
		 	console.log('ANSWER ACCEPTED');
		 	
		 	inst.onAnswerAcceptedCallback(clientId);
		},
		inst.onError);
	}
	
	//==================================================
	// Screen sharing presenter client.
	//==================================================
	
	ScreenSharingPresenterClient.prototype = new Object();
	
	function ScreenSharingPresenterClient(id, peerConnection)
	{
		this.id = id;
		this.peerConnection = peerConnection;
		this.offer = null;
	}
	
", CClientScript::POS_HEAD);