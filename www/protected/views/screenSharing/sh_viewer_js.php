<?php
Yii::app()->clientScript->registerScript(uniqid('sh_viewer_js'), "
	
	//==================================================
	// Screen sharing viewer.
	//==================================================
	
	ScreenSharingViewer.prototype = new ScreenSharingPeer();
	
	function ScreenSharingViewer()
	{
		this.peerConnection = null;
	}
	
	ScreenSharingViewer.prototype.connectToScreenSharing = function()
	{
		this.getOffer();
	}
	
	ScreenSharingViewer.prototype.getOffer = function()
	{
		var request = $.ajax({
			url : '?r=screenSharing/getKey',
			data : { type : RtcKeyType.Offer },
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
			
			var offer = response.key;
			
			inst.createPeerConnection(offer);
		});
		
		request.error(inst.requestTimedOut);
	}
	
	ScreenSharingViewer.prototype.saveAnswer = function(answer)
	{
		// Fixing bugged object conversion to JSON.
		answer = { type : answer.type, sdp : answer.sdp.toString() };
		
		var request = $.ajax({
			url : '?r=screenSharing/saveKey',
			data : { type : RtcKeyType.Answer, key : answer },
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
			
			console.log(response);
		});
		
		request.error(inst.requestTimedOut);
	}
	
	ScreenSharingViewer.prototype.createPeerConnection = function(offer)
	{
		var pc = new PeerConnection(
			{ 'iceServers' : [{ 'url' : this.mainIceServerUrl }] }
		);
		
		var inst = this;
		
		pc.onicecandidate = function(iceCandidateEvent) { inst.onIceCandidate(inst, iceCandidateEvent); };
		pc.onaddstream = function(stream) { inst.onAddStream(inst, stream); };
		
		this.peerConnection = pc;
		
		pc.setRemoteDescription(new SessionDescription(offer), function() { inst.onPeerConnectionRemoteDescCallback(inst); }, inst.onError);
	}
	
	ScreenSharingViewer.prototype.onPeerConnectionRemoteDescCallback = function(inst)
	{
		var pc = inst.peerConnection;
		
		pc.createAnswer(function(answer)
		{
			pc.setLocalDescription(new SessionDescription(answer), function()
			{
				console.log('!!!');
				console.log(answer);
				
				var streams = pc.getRemoteStreams();
				
				console.log('STREAMS:');
				console.log(streams);
				
				if (streams.length > 0)
				{
					var stream = streams[0];
					
					var videoContainer = $('#video').get(0);
					videoContainer.src = window.URL.createObjectURL(stream);
					videoContainer.autoplay = true;
				}
				
				inst.saveAnswer(answer);
				
			}, inst.onError);
		}, inst.onError);
	}
	
", CClientScript::POS_HEAD);