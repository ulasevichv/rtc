<?php
$baseUrl = Yii::app()->theme->baseUrl;

Yii::app()->clientScript->registerCssFile($baseUrl.'/assets/css/screen_sharing.css');

Yii::app()->clientScript->registerScript(uniqid('main_js'), "
	
	var streamingStarted = false;
	var mainStream = null;
	
	var PeerConnection = null;
	var SessionDescription = null;
	var pc = null;
	
	var Role = {
		Presenter : 0,
		Viewer : 1
	};
	
	var RtcKeyType = {
		Offer : 'offer',
		Answer : 'answer'
	};
	
	var role = null;
	
	function validateRequirementsAndGetUniversalObjects()
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
			PeerConnection = window.webkitRTCPeerConnection;
			SessionDescription = window.RTCSessionDescription; // window.webkitRTCSessionDescription; - not working!
		}
		else if (navigator.userAgent.match('Firefox'))
		{
			var browserMajorVersion = parseInt(navigator.userAgent.match(/ Firefox\/(.*)/)[1]);
			
			if (browserMajorVersion < 24) return 'You need Firefox 24+ to run this application';
			
			navigator.getUserMedia = navigator.mozGetUserMedia;
			PeerConnection = window.mozRTCPeerConnection;
//			SessionDescription = window.RTCSessionDescription; // window.mozRTCSessionDescription; - not working!
			SessionDescription = window.mozRTCSessionDescription;
			
			if (role == Role.Presenter) return 'You need Chrome to run this application'; // Doesn't really work in Firefox.
		}
		else
		{
			return 'This application needs to be executed in Firefox or Chrome';
		}
		
		return '';
	}
	
	function getScreen()
	{
		var error = validateRequirementsAndGetUniversalObjects();
		
		if (error != '')
		{
			alert(error);
			return;
		}
		
		if (role == Role.Presenter)
		{
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
		else if (role == Role.Viewer)
		{
			navigator.getUserMedia(
				{
					audio : false,
					video : false
				},
				onSuccess,
				onError
			);
		}
	}
	
	function onSuccess(stream)
	{
		if (role == Role.Presenter)
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
			
			
			
			// Creating peer connection.
			
			pc = new PeerConnection(
				{ 'iceServers' : [{ 'url' : 'stun:stun.l.google.com:19302' }] }
			);
			
			pc.onicecandidate = onIceCandidate;
			pc.onaddstream = onAddStream;
			pc.addStream(stream);
			
			console.log(pc);
			
			pc.createOffer(onPeerConnectionOfferCallback, onError);
		}
		else if (role == Role.Viewer)
		{
			alert('ZZZ');
		}
	}
	
	function onPeerConnectionOfferCallback(offer)
	{
		console.log('onPeerConnectionOfferCallback(offer)');
		console.log(offer);
		
		saveKey(RtcKeyType.Offer, offer);
		
//		pc.setLocalDescription(new SessionDescription(offer), onPeerConnectionLocalDescCallback, onError);
	}
	
	function onPeerConnectionLocalDescCallback(data)
	{
		console.log('onPeerConnectionSessionDescCallback(data)');
		console.log(data);
	}
	
	function saveKey(type, key)
	{
		switch (type)
		{
			case RtcKeyType.Offer: key = key; break;
			case RtcKeyType.Answer:
			{
				// Fixing bugged object conversion to JSON.
				key = { type : key.type, sdp : key.sdp.toString() };
				break;
			}
		}
		
		var request = $.ajax({
			url : '?r=screenSharing/saveKey',
			data : { type : type, key : key },
			type : 'POST',
			dataType : 'json',
			cache : false,
			timeout : 5000
		});
		
		request.success(function(response, status, request)
		{
			if (response.error != '')
			{
				alert(response.error);
				return;
			}
			
			console.log(response);
		});
		
		request.error(requestTimedOut);
	}
	
	function getKey(type)
	{
		var request = $.ajax({
			url : '?r=screenSharing/getKey',
			data : { type : type },
			type : 'POST',
			dataType : 'json',
			cache : false,
			timeout : 5000
		});
		
		request.success(function(response, status, request)
		{
			if (response.error != '')
			{
				alert(response.error);
				return;
			}
			
			switch (type)
			{
				case RtcKeyType.Offer:
				{
					var offer = response.key;
					
					var error = validateRequirementsAndGetUniversalObjects();
					
					if (error != '')
					{
						alert(error);
						return;
					}
					
					// Creating peer connection.
					
					pc = new PeerConnection(
						{ 'iceServers' : [{ 'url' : 'stun:stun.l.google.com:19302' }] }
					);
					
					pc.onicecandidate = onIceCandidate;
					pc.onaddstream = onAddStream;
					
					pc.setRemoteDescription(new SessionDescription(offer), onPeerConnectionRemoteDescCallback, onError);
					
					break;
 				}
 				case RtcKeyType.Answer:
 				{
 					console.log('RECEIVED');
 					
 					var answer = response.key;
 					
 					console.log(answer);
 					
 					break;
 				}
 			}
		});
		
		request.error(requestTimedOut);
	}
	
	function onPeerConnectionRemoteDescCallback()
	{
		console.log('onPeerConnectionRemoteDescCallback()');
		
		pc.createAnswer(function(answer)
		{
			pc.setLocalDescription(new SessionDescription(answer), function()
			{
				console.log('!!!');
				console.log(answer);
				
				saveKey(RtcKeyType.Answer, answer);
				
			}, onError);
		}, onError);
	}
	
	function onError(error)
	{
		console.log('onError()');
		console.log(error);
		
		if (typeof(error.code) != 'undefined' && error.code == error.PERMISSION_DENIED)
		{
			alert('PERMISSION_DENIED. Have you enabled the --enable-usermedia-screen-capture flag?');
		}
		
//		alert(JSON.stringify(error, null, '\t'));
		
//		if (typeof(error.message) != 'undefined')
//		{
//			alert('An error occurred: ' + error.message);
//		}
//		else
//		{
//			alert('An error occurred: ' + error);
//		}
		
		alert('An error occurred: ' + JSON.stringify(error, null, '\t'));
		
		return;
	}
	
	function onEnded()
	{
		endStreaming();
	}
	
	function endStreaming()
	{
		streamingStarted = false;
		if (mainStream != null && mainStream.stop != null && typeof(mainStream.stop) != 'undefined') mainStream.stop();
		mainStream = null;
		
		if (pc != null && pc.close != null && typeof(pc.close) != 'undefined') pc.close();
		pc = null;
		
		var jBtn = $('#btnStartScreenSharing');
		jBtn.html('Start');
	}
	
	function onIceCandidate(iceCandidateEvent)
	{
		console.log('onIceCandidate()');
		console.log(iceCandidateEvent);
	}
	
	function onAddStream(stream)
	{
		console.log('onAddStream()');
		console.log(stream);
	}
	
	function connectToScreenSharing()
	{
		getKey(RtcKeyType.Offer);
	}
	
	function acceptRequest()
	{
		getKey(RtcKeyType.Answer);
	}
	
	function requestTimedOut(request, status, error)
	{
		if (status == 'timeout') alert('".Yii::t('general', 'Request timed out. Please, try again')."');
	}
	
", CClientScript::POS_HEAD);

Yii::app()->clientScript->registerScript(uniqid('main_js'), "
	
	$('#btnStartScreenSharing').on('click', function()
	{
		if (role == null) role = Role.Presenter;
		
		if (streamingStarted)
		{
			endStreaming();
		}
		else
		{
			getScreen();
		}
	});
	
	$('#btnConnect').on('click', function()
	{
		if (role == null) role = Role.Viewer;
		
		connectToScreenSharing();
	});
	
	$('#btnAccept').on('click', function()
	{
		if (role == Role.Presenter)
		{
			acceptRequest();
		}
	});
	
", CClientScript::POS_READY);
?>

<div class="screenSharingRoot">
	<p>
		<video id="video"/>
	</p>
	<p>
		<button id="btnStartScreenSharing">Start</button>
	</p>
	<p>
		<button id="btnConnect">Connect</button>
	</p>
	<p>
		<button id="btnAccept">Accept</button>
	</p>
</div>

<?php
return;

Yii::app()->clientScript->registerScriptFile('https://www.webrtc-experiment.com/firebase.js');
Yii::app()->clientScript->registerScriptFile('https://www.webrtc-experiment.com/Pluginfree-Screen-Sharing/conference.js');
?>

<section class="experiment">
	<section>
		<span>
			Private ?? <a href="/Pluginfree-Screen-Sharing/" target="_blank" title="Open this link for private screen sharing!"><code><strong id="unique-token">#123456789</strong></code></a>
		</span>
		<input type="text" id="room-name" placeholder="Your Name">
		<button id="share-screen" class="setup">Share Your Screen</button>
	</section>
	<!-- list of all available broadcasting rooms -->
	<table style="width: 100%;" id="rooms-list"></table>
	<!-- local/remote videos container -->
	<div id="videos-container"></div>
</section>

<?php
Yii::app()->clientScript->registerScript(uniqid('main_js'), "
	
	var conferenceUI = null;
	var config = null;
	var videosContainer = null;
	var roomsList = null;
	var isChrome = null;
	var DetectRTC = null;
	
	function initialize()
	{
		config = getConfig();
		conferenceUI = conference(config); // On page load: get public rooms.
		
		videosContainer = document.getElementById('videos-container') || document.body;
		roomsList = document.getElementById('rooms-list');
		
		var uniqueToken = document.getElementById('unique-token');
		
		if (uniqueToken)
		{
			if (location.hash.length > 2)
			{
				uniqueToken.parentNode.parentNode.parentNode.innerHTML = '<h2 style=\"text-align:center;\"><a href=\"' + location.href + '\" target=\"_blank\">Share this link</a></h2>';
			}
			else
			{
				uniqueToken.innerHTML = uniqueToken.parentNode.parentNode.href = '#' + (Math.random() * new Date().getTime()).toString(36).toUpperCase().replace( /\./g , '-');
			}
		}
		
		// todo: need to check exact chrome browser because opera also uses chromium framework
		isChrome = !!navigator.webkitGetUserMedia;
		
		// DetectRTC.js - https://github.com/muaz-khan/WebRTC-Experiment/tree/master/DetectRTC
		// Below code is taken from RTCMultiConnection-v1.8.js (http://www.rtcmulticonnection.org/changes-log/#v1.8)
		DetectRTC = {};
		
		(function () {
			
			function CheckDeviceSupport(callback) {
				// This method is useful only for Chrome!
				
				// 1st step: verify \"MediaStreamTrack\" support.
				if (!window.MediaStreamTrack && !navigator.getMediaDevices) {
					return;
				}
				
				if (!window.MediaStreamTrack && navigator.getMediaDevices) {
					window.MediaStreamTrack = {};
				}
				
				// 2nd step: verify \"getSources\" support which is planned to be removed soon!
				// \"getSources\" will be replaced with \"getMediaDevices\"
				if (!MediaStreamTrack.getSources) {
					MediaStreamTrack.getSources = MediaStreamTrack.getMediaDevices;
				}
				
				// todo: need to verify if this trick works
				// via: https://code.google.com/p/chromium/issues/detail?id=338511
				if (!MediaStreamTrack.getSources && navigator.getMediaDevices) {
					MediaStreamTrack.getSources = navigator.getMediaDevices.bind(navigator);
				}
				
				// if still no \"getSources\"; it MUST be firefox!
				if (!MediaStreamTrack.getSources) {
					// assuming that it is older chrome or chromium implementation
					if (isChrome) {
						DetectRTC.hasMicrophone = true;
						DetectRTC.hasWebcam = true;
					}
					
					return;
				}
				
				// loop over all audio/video input/output devices
				MediaStreamTrack.getSources(function (sources) {
					var result = {};
					
					for (var i = 0; i < sources.length; i++) {
						result[sources[i].kind] = true;
					}
					
					DetectRTC.hasMicrophone = result.audio;
					DetectRTC.hasWebcam = result.video;
					
					if(callback) callback();
				});
			}
			
			// check for microphone/webcam support!
			CheckDeviceSupport();
			DetectRTC.load = CheckDeviceSupport;
			
			var screenCallback;
			
			DetectRTC.screen = {
				chromeMediaSource: 'screen',
				getSourceId: function(callback) {
					if(!callback) throw '\"callback\" parameter is mandatory.';
					screenCallback = callback;
					window.postMessage('get-sourceId', '*');
				},
				isChromeExtensionAvailable: function(callback) {
					if(!callback) return;
					
					if(DetectRTC.screen.chromeMediaSource == 'desktop') callback(true);
					
					// ask extension if it is available
					window.postMessage('are-you-there', '*');
					
					setTimeout(function() {
						if(DetectRTC.screen.chromeMediaSource == 'screen') {
							callback(false);
						}
						else callback(true);
					}, 2000);
				},
				onMessageCallback: function(data) {
					console.log('chrome message', data);
					
					// \"cancel\" button is clicked
					if(data == 'PermissionDeniedError') {
						DetectRTC.screen.chromeMediaSource = 'PermissionDeniedError';
						if(screenCallback) return screenCallback('PermissionDeniedError');
						else throw new Error('PermissionDeniedError');
					}
					
					// extension notified his presence
					if(data == 'rtcmulticonnection-extension-loaded') {
						if(document.getElementById('install-button')) {
							document.getElementById('install-button').parentNode.innerHTML = '<strong>Great!</strong> <a href=' +
								'\"https://chrome.google.com/webstore/detail/screen-capturing/ajhifddimkapgcifgcodmmfdlknahffk\" target=\"_blank\">Google chrome extension</a> is installed.';
						}
						DetectRTC.screen.chromeMediaSource = 'desktop';
					}
					
					// extension shared temp sourceId
					if(data.sourceId) {
						DetectRTC.screen.sourceId = data.sourceId;
						if(screenCallback) screenCallback( DetectRTC.screen.sourceId );
					}
				}
			};
			
			// check if desktop-capture extension installed.
			if(window.postMessage && isChrome) {
				DetectRTC.screen.isChromeExtensionAvailable();
			}
			
		})();
		
		window.addEventListener('message', function (event) {
			if (event.origin != window.location.origin) {
				return;
			}
	
			DetectRTC.screen.onMessageCallback(event.data);
		});
		
		console.log('current chromeMediaSource', DetectRTC.screen.chromeMediaSource);
	}
	
	function getConfig()
	{
		return {
			openSocket: function(config) {
				var channel = config.channel || 'screen-capturing-' + location.href.replace( /\/|:|#|%|\.|\[|\]/g , '');
				var socket = new Firebase('https://chat.firebaseIO.com/' + channel);
				socket.channel = channel;
				socket.on('child_added', function(data) {
					config.onmessage && config.onmessage(data.val());
				});
				socket.send = function(data) {
					this.push(data);
				};
				config.onopen && setTimeout(config.onopen, 1);
				socket.onDisconnect().remove();
				return socket;
			},
			onRemoteStream: function(media) {
				var video = media.video;
				video.setAttribute('controls', true);
				videosContainer.insertBefore(video, videosContainer.firstChild);
				video.play();
				rotateVideo(video);
			},
			onRoomFound: function(room) {
				var alreadyExist = document.getElementById(room.broadcaster);
				if (alreadyExist) return;
	
				if (typeof roomsList === 'undefined') roomsList = document.body;
	
				var tr = document.createElement('tr');
				tr.setAttribute('id', room.broadcaster);
				tr.innerHTML = '<td>' + room.roomName + '</td>' +
					'<td><button class=\"join\" id=\"' + room.roomToken + '\">Open Screen</button></td>';
				roomsList.insertBefore(tr, roomsList.firstChild);
	
				var button = tr.querySelector('.join');
				button.onclick = function() {
					var button = this;
					button.disabled = true;
					conferenceUI.joinRoom({
						roomToken: button.id,
						joinUser: button.parentNode.parentNode.id
					});
				};
			},
			onNewParticipant: function(numberOfParticipants) {
				document.title = numberOfParticipants + ' users are viewing your screen!';
				var element = document.getElementById('number-of-participants');
				if (element) {
					element.innerHTML = numberOfParticipants + ' users are viewing your screen!';
				}
			}
		};
	}
	
	function startSharing()
	{
		var jRoomName = $('#room-name');
		var jBtnShareScreen = $('#share-screen');
		
		jRoomName.get(0).disabled = true;
		jBtnShareScreen.get(0).disabled = true;
		
		captureUserMedia(function()
		{
			conferenceUI.createRoom({
				roomName: (roomName.value || 'Anonymous') + ' shared his screen with you'
			});
		});
	}
	
	function captureUserMedia(callback, extensionAvailable)
	{
		console.log('captureUserMedia chromeMediaSource', DetectRTC.screen.chromeMediaSource);
		
		var screen_constraints = {
			mandatory: {
				chromeMediaSource: DetectRTC.screen.chromeMediaSource,
				maxWidth: 1920,
				maxHeight: 1080,
				minAspectRatio: 1.77
			},
			optional: []
		};
		
		// try to check if extension is installed.
		if(typeof extensionAvailable == 'undefined' && DetectRTC.screen.chromeMediaSource != 'desktop') {
			DetectRTC.screen.isChromeExtensionAvailable(function(available) {
				captureUserMedia(callback, available);
			});
			return;
		}
		
		if(DetectRTC.screen.chromeMediaSource == 'desktop' && !DetectRTC.screen.sourceId) {
			DetectRTC.screen.getSourceId(function(error) {
				if(error && error == 'PermissionDeniedError') {
					alert('PermissionDeniedError: User denied to share content of his screen.');
				}
				
				captureUserMedia(callback);
			});
			return;
		}
		
		if(DetectRTC.screen.chromeMediaSource == 'desktop') {
			screen_constraints.mandatory.chromeMediaSourceId = DetectRTC.screen.sourceId;
		}
		
		var constraints = {
			audio: false,
			video: screen_constraints
		};
		
		console.log(JSON.stringify(constraints , null, '\t'));
		
		var video = document.createElement('video');
		video.setAttribute('autoplay', true);
		video.setAttribute('controls', true);
		videosContainer.insertBefore(video, videosContainer.firstChild);
		
		getUserMedia({
			video: video,
			constraints: constraints,
			onsuccess: function(stream) {
				config.attachStream = stream;
				callback && callback();
				
				video.setAttribute('muted', true);
				rotateVideo(video);
			},
			onerror: function() {
				if (location.protocol === 'http:') {
					alert('Please test this WebRTC experiment on HTTPS.');
				} else {
					alert('Screen capturing is either denied or not supported. Please install chrome extension for screen capturing or run chrome with command-line flag: ' +
						'--enable-usermedia-screen-capturing');
				}
			}
		});
	}
	
	function rotateVideo(video)
	{
		video.style[navigator.mozGetUserMedia ? 'transform' : '-webkit-transform'] = 'rotate(0deg)';
		
		setTimeout(function() {
			video.style[navigator.mozGetUserMedia ? 'transform' : '-webkit-transform'] = 'rotate(360deg)';
		}, 1000);
	}
	
", CClientScript::POS_HEAD);

Yii::app()->clientScript->registerScript(uniqid('main_js'), "
	
	initialize();
	
	$('#share-screen').on('click', function()
	{
		startSharing();
	});
	
", CClientScript::POS_READY);