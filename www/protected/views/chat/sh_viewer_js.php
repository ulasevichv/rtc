<?php
Yii::app()->clientScript->registerScript(uniqid('sh_viewer_js'), "
	
	//==================================================
	// Screen sharing viewer.
	//==================================================
	
	ScreenSharingViewer.prototype = new ScreenSharingPeer();
	
	function ScreenSharingViewer()
	{
		this.peerConnection = null;
		this.onAnswerCreatedCallback = null;
		this.onScreenSharingEstablishedCallback = null;
	}
	
	ScreenSharingViewer.prototype.connectToScreenSharing = function(presenterId, offer)
	{
		this.createPeerConnection(presenterId, offer);
	}
	
	ScreenSharingViewer.prototype.createPeerConnection = function(presenterId, offer)
	{
		var pc = new PeerConnection(
			{ 'iceServers' : [{ 'url' : this.mainIceServerUrl }] }
		);
		
		var inst = this;
		
		pc.onicecandidate = function(iceCandidateEvent) { inst.onIceCandidate(inst, iceCandidateEvent); };
		pc.onaddstream = function(stream) { inst.onAddStream(inst, stream); };
		
		this.peerConnection = pc;
		
		pc.setRemoteDescription(new SessionDescription(offer), function() { inst.onPeerConnectionRemoteDescCallback(inst, presenterId); }, inst.onError);
	}
	
	ScreenSharingViewer.prototype.onPeerConnectionRemoteDescCallback = function(inst, presenterId)
	{
		var pc = inst.peerConnection;
		
		pc.createAnswer(function(answer)
		{
			pc.setLocalDescription(new SessionDescription(answer), function()
			{
				// Fixing bugged object conversion to JSON.
				answer = { type : answer.type, sdp : answer.sdp.toString() };
				
				inst.onAnswerCreatedCallback(presenterId, answer);
			},
			inst.onError);
		},
		inst.onError);
	}
	
	ScreenSharingViewer.prototype.onScreenSharingEstablished = function()
	{
		var pc = this.peerConnection;
		
		var streams = pc.getRemoteStreams();
		
		if (streams.length > 0)
		{
			var stream = streams[0];
			
			this.onScreenSharingEstablishedCallback(stream);
		}
	}
	
	ScreenSharingViewer.prototype.disconnect = function()
	{
		if (this.peerConnection != null)
		{
			if (this.peerConnection.close != null && typeof(this.peerConnection.close) != 'undefined')
			{
				this.peerConnection.close();
				this.peerConnection = null;
			}
		}
	}
	
", CClientScript::POS_HEAD);