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
	}
	
	ScreenSharingViewer.prototype.connectToScreenSharing = function(presenterId, offer)
	{
		this.createPeerConnection(presenterId, offer);
	}
	
//	ScreenSharingViewer.prototype.connectToScreenSharing = function()
//	{
//		this.getOffer();
//	}
	
//	ScreenSharingViewer.prototype.getOffer = function()
//	{
//		var request = $.ajax({
//			url : '?r=screenSharing/getKey',
//			data : { type : RtcKeyType.Offer },
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
//			var offer = response.key;
//			
//			inst.createPeerConnection(offer);
//		});
//		
//		request.error(inst.requestTimedOut);
//	}
	
//	ScreenSharingViewer.prototype.saveAnswer = function(answer)
//	{
//		// Fixing bugged object conversion to JSON.
//		answer = { type : answer.type, sdp : answer.sdp.toString() };
//		
//		var request = $.ajax({
//			url : '?r=screenSharing/saveKey',
//			data : { type : RtcKeyType.Answer, key : answer },
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
//	}
	
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
				console.log('!!!');
				console.log(answer);
				
				// Fixing bugged object conversion to JSON.
				answer = { type : answer.type, sdp : answer.sdp.toString() };
				
				console.log('fixed answer');
				console.log(answer);
				
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
		
		console.log('STREAMS:');
		console.log(streams);
		
		if (streams.length > 0)
		{
			var stream = streams[0];
			
			
			
			
			
			var msgContainerId = 'msg_'+ Strophe.getNodeFromJid(ChatGUI.openedRoom.id);
			
			var jMsgContainer = $('#' + msgContainerId);
			var jVideo = jMsgContainer.find('.video');
			var jScreenSharing = jMsgContainer.find('.screenSharing');
			var jVideoToggle = jMsgContainer.find('.video-toggle');
			var screenSharingOwnVideoId = msgContainerId + '_sh_own';
			
			jScreenSharing.append('<video id=\"' + screenSharingOwnVideoId + '\" class=\"own_video\" controls=\"true\" autoplay=\"true\"></video>');
			
			var jOwnVideo = $('#' + screenSharingOwnVideoId);
			var videoElement = jOwnVideo.get(0);
			videoElement.src = window.URL.createObjectURL(stream);
			
//					var videoContainer = $('.screenSharing').get(0);
//					videoContainer.src = window.URL.createObjectURL(stream);
//					videoContainer.autoplay = true;
			
			jVideo.css('display', 'block');
			jScreenSharing.css('display', 'block');
			jVideoToggle.css('display', 'block');
			
			ChatGUI.resizeChatTextDiv();
		}
	}
	
", CClientScript::POS_HEAD);