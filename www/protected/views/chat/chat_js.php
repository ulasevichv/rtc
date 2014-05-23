<?php
Yii::app()->clientScript->registerScript(uniqid('chat_js'), "
	
	var Chat = {
		conn : null,
		domain : '".$xmppAddress."',
		boshAddress : '".$boshAddress."',
		currentUser : new InternalChatUser(
			'',
			'".$xmppUser->serverUserName."' + '@' + '".$xmppAddress."',
			'".$xmppUser->serverUserName."',
			'".Yii::app()->user->fullName."',
			'".$xmppUser->serverUserPass."'
		),
		disconnecting : false,
		loginDateTime : new Date(),
		
		connect : function()
		{
			Chat.conn  = new Strophe.Connection(Chat.boshAddress);
			
			console.log('Connecting \'' + Chat.currentUser.bareJid + '\' (' + Chat.currentUser.password + ')');
			
			Chat.conn.connect(Chat.currentUser.bareJid, Chat.currentUser.password, function(status) { Chat.onConnectionStatusChange(status); });
		},
		
		disconnect : function()
		{
			Chat.disconnecting = true;
			
			Chat.conn.send(\$pres({
				to : Chat.domain,
				type : PresenceType.UNAVAILABLE
				})
			);
			
			for (var roomName in Chat.conn.muc.rooms)
			{
				console.log('Disconnecting from room: ' + roomName);
    			Chat.conn.muc.rooms[roomName].leave();
			}
			
			Chat.conn.disconnect();
			Chat.conn = null;
		},
		
		disconnectFromRoom : function(roomJid)
		{
			var room = ChatGUI.getRoomById(roomJid);
			
			for (var roomName in Chat.conn.muc.rooms)
			{
				if (roomName == roomJid)
				{
					Chat.conn.muc.rooms[roomName].leave();
					break;
				}
			}
		},
		
		onConnectionStatusChange : function(status)
		{
			switch (status)
			{
				case Strophe.Status.CONNECTED: Chat.onConnect(); break;
				case Strophe.Status.DISCONNECTED: Chat.onDisconnect(); break;
				default:
				{
					// console.log('Unhandled XMPP-connection status: ' + status);
				}
			}
		},
		
		onConnect : function()
		{
			console.log('Connected');
			
			Chat.conn.muc.init(Chat.conn);
//			Chat.conn.roster.init(Chat.conn);
			
			
			
			
			
//			var iq = \$iq({type: 'get'}).c('query', {xmlns: Strophe.NS.ROSTER});
//			Chat.conn.sendIQ(iq, RosterObj.on_roster);
//			Chat.conn.addHandler(RosterObj.on_roster_changed, Strophe.NS.ROSTER, 'iq', 'set');

			Chat.conn.addHandler(Chat.onSystemMessage, null, 'message', MessageType.SYSTEM);
			Chat.conn.addHandler(Chat.onVideoCall, null, 'message', MessageType.VIDEO_CALL);
			Chat.conn.addHandler(Chat.onDrawingCall, null, 'message', MessageType.DRAWING_CALL);
			Chat.conn.addHandler(Chat.onDrawingContent, null, 'message', MessageType.DRAWING_CONTENT);
			Chat.conn.addHandler(Chat.onVideoCallAccepted, null, 'message', MessageType.VIDEO_CALL_ACCEPTED);
			Chat.conn.addHandler(Chat.onVideoCallDeclined, null, 'message', MessageType.VIDEO_CALL_DECLINED);
			
			Chat.getRoster();
			Chat.getRoomsList();
			
			ChatGUI.unblockControls();
			ChatGUI.resizeChatTextDiv();
		},
		
		onDisconnect : function()
		{
			if (Chat.disconnecting) return;
			
			alert('".Yii::t('general', 'Unable to connect to server. Please, reload the page')."');
		},
		
		getRoomsList : function()
		{
			Chat.conn.muc.listRooms('conference.' + Chat.domain, Chat.onRoomsList, Chat.onError);
		},
		
		onRoomsList : function(iq)
		{
			console.log('onRoomsList');
			
			$(iq).find('item').each(function ()
			{
				var jid = $(this).attr('jid');
				
				var staticRoom = new InternalStaticChatRoom(
					jid,
					Strophe.getNodeFromJid(jid),
					$(this).attr('name')
				);
				
				ChatGUI.staticRooms.push(staticRoom);
			});
			
			ChatGUI.staticRooms.sort(function(a,b) { return a.fullName.localeCompare(b.fullName); });
			
			ChatGUI.refreshStaticRooms();
		},
		
		getRoster : function()
		{
			var iq = \$iq({type: 'get'}).c('query', {xmlns: Strophe.NS.ROSTER});
			Chat.conn.sendIQ(iq, Chat.onRoster);
			Chat.conn.addHandler(Chat.onRosterChange, Strophe.NS.ROSTER, 'iq', 'set');
		},
		
		onRoster : function(iq)
		{
			console.log('onRoster');
			
			Chat.currentUser.fullJid = $(iq).attr('to');
			
			ChatGUI.addUser(Chat.currentUser);
			
			$(iq).find('item').each(function () {
				var bareJid = $(this).attr('jid');
				var nickname = Strophe.getNodeFromJid(bareJid);
				var userName = $(this).attr('name') || bareJid;
				
				console.log(userName + '(' + nickname + ')');
				
				var user = new InternalChatUser(
					'',
					bareJid,
					nickname,
					userName);
				
				ChatGUI.addUser(user);
			});
			
			ChatGUI.openedRoom = ChatGUI.getRoomById('dashboard');
			ChatGUI.refreshRooms();
			
			Chat.conn.addHandler(Chat.onMessage, null, 'message', 'chat');
			Chat.conn.addHandler(Chat.onMessage, null, 'message', 'groupchat');
			
			Chat.conn.addHandler(Chat.onPresence, null, 'presence');
       		Chat.conn.send(\$pres());
		},
		
		onRosterChange : function(iq)
		{
			console.log('onRosterChanged');
			
			$(iq).find('item').each(function () {
				var sub = $(this).attr('subscription');
				var jid = $(this).attr('jid');
				var name = $(this).attr('name') || jid;
				var jidId = Strophe.getNodeFromJid(jid);
				
				console.log('onRosterChange: ' + sub + ', ' + jid + ', ' + name + ', ' + jidId);
				
				if (sub === 'remove')
				{
					// Contact is being removed.
					
					// Example code.
					// $('#' + jid_id).remove();
				}
				else
				{
					// Contact is being added or modified.
					
					// Example code.
//					var contact_html = '<li id=\"' + jid_id + '\">' +
//						'<div class=\"' + 
//						($('#' + jid_id).attr('class') || 'roster-contact offline') +
//						'\">' +
//						'<div class=\"roster-name\">' +
//						name +
//						'</div><div class=\"roster-jid\">' +
//						jid +
//						'</div></div></li>';
//					
//					if ($('#' + jid_id).length > 0) {
//						$('#' + jid_id).replaceWith(contact_html);
//					} else {
//						Gab.insert_contact($(contact_html));
//					}
				}
			});
			
			return true;
		},
		
		sendMessage : function(recipientJid, message)
		{
			console.log('sendMessage(' + recipientJid + ', ' + message.text +')');
			
			$.ionSound.play('button_push');
			
			if (message.type == MessageType.CHAT)
			{
				Chat.conn.send(\$msg({
					to : recipientJid,
					type : 'chat',
					}).c('body').t(message.text).up()
					.c('active', {xmlns: 'http://jabber.org/protocol/chatstates'})
				);
			}
			else if (message.type == MessageType.GROUP_CHAT)
			{
			    console.log(message);
				var xmppRoom = Chat.conn.muc.rooms[message.roomJid];
				
				xmppRoom.groupchat(message.text);
			}
			else
			{
				Chat.conn.send(\$msg({
						to : recipientJid,
						type : message.type,
						}).c('body').t(message.text).up()
						.c('active', {xmlns: 'http://jabber.org/protocol/chatstates'})
					);
			}
		},
		
		connectToRoom : function(roomName)
		{
			console.log('Connecting to room: ' + roomName);
			
			// console.log('rooms:');
			// console.log(xmppConnection.muc.listRooms());
			
//			Chat.conn.muc.join(roomName + '@conference.' + Chat.domain, Chat.currentUser.nickname, Chat.onRoomMsg, Chat.onRoomPresence, function() {});
//			Chat.conn.muc.join(roomName + '@conference.' + Chat.domain, Chat.currentUser.nickname, Chat.onRoomMsg, Chat.onPresence, function() {});
//			Chat.conn.muc.join(roomName + '@conference.' + Chat.domain, Chat.currentUser.nickname, Chat.onRoomMsg, null, null);
			Chat.conn.muc.join(roomName + '@conference.' + Chat.domain, Chat.currentUser.nickname, null, null, null);
			
//			Chat.conn.send(\$pres({
//				// from: Chat.currentUser.bareJid,
//				to: roomName + '@conference.' + Chat.domain + '/' + Chat.currentUser.nickname
//				}).c('x', {xmlns: Strophe.NS.MUC})
//			);
			
//			Chat.conn.send(\$pres({
//				to: roomName + '@conference.' + Chat.domain + '/' + Chat.currentUser.nickname
//				})
//			);
			
//			Chat.conn.send(\$pres({
//				from : Chat.currentUser.fullJid,
//				to: roomName + '@conference.' + Chat.domain + '/' + Chat.currentUser.nickname
//				}).c('x', {xmlns: Strophe.NS.MUC})
//			);
			
//			Chat.conn.muc.queryOccupants(roomName + '@conference.' + Chat.domain, Chat.onRoomQueryOccupants, null);
		},
		
		onPresence : function(stanza)
		{
			if (Chat.disconnecting) return true;
			
			var from = $(stanza).attr('from');
			var to = $(stanza).attr('to');

			var fullJid = from;
			var bareJid = Strophe.getBareJidFromJid(fullJid);
			var jidId = Strophe.getNodeFromJid(fullJid);
			var resource = Strophe.getResourceFromJid(fullJid);
			if ($(stanza).find('status').text()) {
			    statusId = $(stanza).find('show').text();
			    statusText = $(stanza).find('status').text();
			} else {
			    statusId = '';
			    statusText = '';
			}
			var presenceType = $(stanza).attr('type');
			if (typeof(presenceType) == 'undefined') presenceType = PresenceType.AVAILABLE;
			
			var sourceType = StanzaSourceType.DIRECT;
			
			var jX = $(stanza).find('x');
			
			if (jX.length != 0)
			{
				var presenceProtocolType = jX.eq(0).attr('xmlns');
				
				if (presenceProtocolType == Strophe.NS.MUC + '#user')
				{
					sourceType = StanzaSourceType.ROOM;
				}
			}
			
			switch (sourceType)
			{
				case StanzaSourceType.DIRECT:
				{
					console.log('onDirectPresence: ' + fullJid + ' (' + bareJid + ', ' + jidId + ') > ' + to + ' > ' + presenceType);
					
					if (bareJid == Chat.currentUser.bareJid && from != to) return true; // Unwanted status of current user from previous sessions.
					
					var user = ChatGUI.getUserByBareJid(bareJid);
					
					if (user != null)
					{
						if (user.fullJid != '' && user.fullJid != fullJid)
						{
							if (presenceType == PresenceType.AVAILABLE)
							{
								ChatGUI.updateUser(bareJid, fullJid, true,statusId,statusText);
							}
							
							return true;
						}
						
						if (presenceType !== 'error')
						{
							ChatGUI.updateUser(bareJid, fullJid, (presenceType == PresenceType.AVAILABLE),statusId,statusText);
						}
					}
					
					break;
				}
				case StanzaSourceType.ROOM:
				{
					var roomJid = bareJid;
					
					var room = ChatGUI.getRoomById(roomJid);
					
					console.log('onRoomPresence: ' + fullJid + ' (' + bareJid + ', ' + jidId + ') > ' + to + ' > ' + presenceType);
					
					// Fixing current user room presence.
					
					if (bareJid == Chat.currentUser.bareJid && to == Chat.currentUser.fullJid && presenceType == PresenceType.UNAVAILABLE)
					{
//						Chat.conn.send(\$pres({
//							from : Chat.currentUser.fullJid,
//							to : roomJid + '/' + Chat.currentUser.nickname
//							}).c('x', {xmlns: Strophe.NS.MUC})
//						);
						
						return true;
					}
					
					// Creating room or revealing room.
					
					if (room == null && presenceType == PresenceType.AVAILABLE && resource == Chat.currentUser.nickname)
					{
//						var xmppRoom = Chat.conn.muc.rooms[bareJid];
//						
//						console.log('Connected to room: ' + bareJid);
//						console.log(xmppRoom);
						
						var staticRoom = ChatGUI.getStaticRoomByName(jidId);
						
						var room = new InternalChatRoom(
							bareJid,
							MessageType.GROUP_CHAT,
							staticRoom.fullName
						);
						
						for (var i = 0; i < ChatGUI.roomOnlineStatusesPull.length; i++)
						{
							var pullObject = ChatGUI.roomOnlineStatusesPull[i];
							
							if (pullObject.roomJid == bareJid)
							{
								room.changeParticipantOnlineStatus(pullObject.nickname, true);
								
								ChatGUI.roomOnlineStatusesPull.splice(i, 1);
								i--;
							}
						}
						
						ChatGUI.rooms.push(room);
						
						ChatGUI.openedRoom = room;
						
						ChatGUI.refreshRooms();
						
						ChatGUI.loadChatRoomHistory(room);
					}
					
					// Saving participants statuses for not existing group.
					
					if (room == null && presenceType == PresenceType.AVAILABLE)
					{
						ChatGUI.roomOnlineStatusesPull.push({ roomJid : roomJid, nickname : resource });
					}
					
					// Updating participants statuses for existing group.
					
					if (room != null)
					{
						var participantNickname = resource;
						var online = (presenceType == PresenceType.AVAILABLE);
						
						room.changeParticipantOnlineStatus(participantNickname, online);
						
						ChatGUI.updateUsersVisibility();
					}
					
					break;
				}
			}
			
			return true;
		},
		
//		onRoomPresence : function(stanza)
//		{
////			console.log('onRoomPresence');
////			console.log(stanza);
//			
//			var from = stanza.getAttribute('from');
//			var to = stanza.getAttribute('to');
//			var fullJid = from;
//			var bareJid = Strophe.getBareJidFromJid(fullJid);
//			var jidId = Strophe.getNodeFromJid(fullJid);
//			
//			var type = $(stanza).attr('type');
//			if (typeof(type) == 'undefined') type = PresenceType.AVAILABLE;
//			
//			console.log('onRoomPresence: ' + from + ' > ' + to + ' > ' + type);
//			
//			
//			
//			console.log(Chat.conn.muc.rooms);
//			
//			return true;
//		},
		
		onMessage : function(stanza)
		{
		    console.log('onMessage');
			var from = $(stanza).attr('from');
			var to = $(stanza).attr('to');
			var type = $(stanza).attr('type');
			var fullJid = from;
			var bareJid = Strophe.getBareJidFromJid(fullJid);
			var jidId = Strophe.getNodeFromJid(fullJid);
			var resource = Strophe.getResourceFromJid(fullJid);
			
			var jBody = $(stanza).find('body');
			
			if (jBody.length == 0) return true;
			
			var text = jBody.text();
			
			if (type == MessageType.CHAT)
			{
				console.log('onDirectMessage: ' + from + ' > ' + to + ' > ' + text);
				
				var user = ChatGUI.getUserByBareJid(Strophe.getBareJidFromJid(from));
				
				var sendDate = new Date();
				
				var jX = $(stanza).find('delay[xmlns=\"urn:xmpp:delay\"]');
				
				if (jX.length != 0)
				{
					var xmppStamp = jX.attr('stamp');
					
					sendDate = new Date(xmppStamp);
				}
				
				if (user != null)
				{
					var newMessage = new InternalChatMessage(
						MessageType.CHAT,
						sendDate,
						MethodsForDateTime.dateToString(sendDate),
						user.bareJid,
						user.fullName,
						text);
					
					ChatGUI.addChatMessage(newMessage);
				}
			}
			else if (type == MessageType.GROUP_CHAT)
			{
				if ($(stanza).attr('drawingcontent')) {
					Chat.onDrawingContent(stanza);
					return true;
				}
				
				var roomJid = bareJid;
				
				var room = ChatGUI.getRoomById(roomJid);
				
				console.log('onRoomMessage: [' + roomJid + '] ' + from + ' > ' + to + ' > ' + text);
				
				var sendDate = new Date();
				
//				var jX = $(stanza).find('x[xmlns=\"jabber:x:delay\"]');
				var jX = $(stanza).find('delay[xmlns=\"urn:xmpp:delay\"]');
				
				if (jX.length != 0)
				{
					var xmppStamp = jX.attr('stamp');
					
					sendDate = new Date(xmppStamp);
				}
				
				var senderBareJid = resource + '@' + Strophe.getDomainFromJid(from).replace('conference.', '');
				
				var sender = ChatGUI.getUserByBareJid(senderBareJid);
				
				if (sender != null && sender.bareJid == Chat.currentUser.bareJid && jX.length == 0) // Received message from self at runtime.
				{
					return true;
				}
				
				console.log('----------------------');
				console.log(sender);
				console.log(Chat.currentUser.bareJid);
				console.log('----------------------');
				
				if ($(stanza).attr('videocall'))
				{
					console.log('--- groupcall request ---');
					var opentokIniJsonObj = $.parseJSON(text);
					console.log(bareJid);
					Chat.currentUser.addOpentokIniObject(bareJid, opentokIniJsonObj);
					console.log(Chat.currentUser);
					
//					$.ionSound.play('sound_message');
					
					ChatGUI.addVideoCallInvitationControls(Chat.currentUser.bareJid);
					
					return true;
				}
				else if ($(stanza).attr('whiteboard'))
				{
				    ChatGUI.addDrawingCallInvitationControls(ChatGUI.openedRoom.id);
				}
				
				if (room != null && sender != null)
				{
//					$.ionSound.play('sound_message');
					
					var newMessage = new InternalChatMessage(
						MessageType.GROUP_CHAT,
						sendDate,
						MethodsForDateTime.dateToString(sendDate),
						sender.bareJid,
						sender.fullName,
						text,
						roomJid);
					
					ChatGUI.addChatMessage(newMessage);
				}
			}
			
			ChatGUI.scrollOpenedMessagesToBottom();
			
			return true;
		},
		
		startVideoCall : function()
		{
			$.ajax({
				type: 'POST',
				url: '?r=chat/initializeVideoCall',
				data: null,
				success: function(json)
				{
					Chat.openTokInit($.parseJSON(json));
					Chat.changeStatus('onVideoCall','".Yii::t('general','On Video Call')."');
					ChatGUI.showVideoCallInvitationSentMessage();
					
					if (ChatGUI.openedRoom.type == 'groupchat')
					{
						var xmppRoom = Chat.conn.muc.rooms[ChatGUI.openedRoom.id];
						
						Chat.conn.muc.VideoCallInviteMessage(xmppRoom.name, null,'',json,'groupchat');
						
						return true;
					}
					else
					{
						var sendDate = new Date();
						
						var newMessage = new InternalChatMessage(
							MessageType.VIDEO_CALL,
							sendDate,
							MethodsForDateTime.dateToString(sendDate),
							ChatGUI.openedRoom.id,
							ChatGUI.openedRoom.fullName,
							json);
						console.log(newMessage);
						Chat.sendMessage(ChatGUI.openedRoom.id, newMessage);
					}
					
					return true;
				}
			});
		},
		
		startWhiteboardDrawing : function()
		{
			if (ChatGUI.openedRoom.type == 'groupchat')
			{
				var xmppRoom = Chat.conn.muc.rooms[ChatGUI.openedRoom.id];
				
				Chat.conn.muc.WhiteboardCallInviteMessage(xmppRoom.name, null,'','Do you want to join my drawing demonstration?','groupchat');
				
				return true;
			}
			else
			{
				var sendDate = new Date();
				
				 var newMessage = new InternalChatMessage(
					MessageType.DRAWING_CALL,
					sendDate,
					MethodsForDateTime.dateToString(sendDate),
					ChatGUI.openedRoom.id,
					ChatGUI.openedRoom.fullName,
					'Invite');
				Chat.sendMessage(ChatGUI.openedRoom.id, newMessage);
			}
			
			return true;
		},
		
		onVideoCall : function(msg)
		{
			var to = $(msg).attr('to');
			var from = $(msg).attr('from');
			var type = $(msg).attr('type');
			var jBody = $(msg).find('body');
			
			console.log('onVideoCall: ' + from + ', ' + type);
			
			if (from != Chat.currentUser.fullJid)
			{
				var sender = ChatGUI.getUserByBareJid(Strophe.getBareJidFromJid(from));
				
				var text = 'Start video call with ' + sender.fullName + '?';
				
				var sendDate = new Date();
				
				var newMessage = new InternalChatMessage(
					MessageType.VIDEO_CALL,
					sendDate,
					MethodsForDateTime.dateToString(sendDate),
					sender.bareJid,
					sender.fullName,
					text);
				
				ChatGUI.addChatMessage(newMessage);
				ChatGUI.addVideoCallInvitationControls(sender.bareJid);
				
				var opentokIniJsonObj = $.parseJSON(jBody.text());
				
//				console.log(opentokIniJsonObj);
				
				Chat.currentUser.addOpentokIniObject(Strophe.getBareJidFromJid(from), opentokIniJsonObj);
			}
			
			return true;
		},
		
		onDrawingCall : function(msg)
		{
			var to = $(msg).attr('to');
			var from = $(msg).attr('from');
			var type = $(msg).attr('type');
			var jBody = $(msg).find('body');
			
			console.log('onDrawingCall: ' + from + ', ' + type);
			
			if (from != Chat.currentUser.fullJid)
			{
				var sender = ChatGUI.getUserByBareJid(Strophe.getBareJidFromJid(from));
				
				var text = 'Start drawing with ' + sender.fullName + '?';
				
				var sendDate = new Date();
				
				var newMessage = new InternalChatMessage(
					MessageType.VIDEO_CALL,
					sendDate,
					MethodsForDateTime.dateToString(sendDate),
					sender.bareJid,
					sender.fullName,
					text);
				
				ChatGUI.addChatMessage(newMessage);
				
				ChatGUI.addDrawingCallInvitationControls(sender.bareJid);
		    }
		},
		
		onDrawingContent : function(msg)
		{
			var to = $(msg).attr('to');
			var from = $(msg).attr('from');
			var type = $(msg).attr('type');
			var jBody = $(msg).find('body');
			
			if (jQuery('.literally.localstorage').is(':visible'))
			{
				window.whiteboard.loadSnapshotJSON(jBody.text());
			} else {
				ChatGUI.addDrawingCallInvitationControls(from);
			}
			
			return true;
		},
		
		sendDrawingContent : function(json)
		{
			if (ChatGUI.openedRoom.type == 'groupchat')
			{
				var xmppRoom = Chat.conn.muc.rooms[ChatGUI.openedRoom.id];
				
				Chat.conn.muc.WhiteboardDrawingContentMessage(xmppRoom.name, null,'',json,'groupchat');
				
				return true;
			}
			else
			{
				var sendDate = new Date();
				
				var newMessage = new InternalChatMessage(
					MessageType.DRAWING_CONTENT,
					sendDate,
					MethodsForDateTime.dateToString(sendDate),
					ChatGUI.openedRoom.bareJid,
					ChatGUI.openedRoom.fullName,
					json);
				
				Chat.sendMessage(ChatGUI.openedRoom.id, newMessage);
//				ChatGUI.addChatMessage(newMessage);
			}
			
			console.log(json);
		},
		
		onSystemMessage : function(msg)
		{
			var to = $(msg).attr('to');
			var from = $(msg).attr('from');
			var type = $(msg).attr('type');
			var jBody = $(msg).find('body');
			Chat.addSystemMessage(jBody.text());
			
			return true;
		},
		
		addSystemMessage : function(text)
		{
			jQuery('#system-messages').html('<div class=\"alert alert-success alert-dismissable\">' +
				'<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>'+
				'<strong> Information! </strong>'+text+'</div>');
			jQuery('#system-messages').show('slow');
			setTimeout(function() { $('#system-messages').fadeOut('slow');}, 5000);
			
			return true;
		},
		
		acceptVideoCall : function ()
		{
			var opentokIniObject = Chat.currentUser.getOpentokIniObject(ChatGUI.openedRoom.id);

			$.ajax({
				type: 'POST',
				url: '?r=chat/getVideoCallToken',
				data: { sessionId : opentokIniObject.obj.sessionId, apiKey : opentokIniObject.obj.apiKey},
				success: function(token)
				{
					opentokIniObject.obj.token = token;
					
					Chat.openTokInit(opentokIniObject.obj);
					Chat.changeStatus('onVideoCall','".Yii::t('general','On Video Call')."');
				}
			});
		},
		
//		acceptVideoCall : function ()
//		{
//			var sendDate = new Date();
//			
//			var newMessage = new InternalChatMessage(
//				MessageType.VIDEO_CALL_ACCEPTED,
//				sendDate,
//				MethodsForDateTime.dateToString(sendDate),
//				ChatGUI.openedRoom.id,
//				ChatGUI.openedRoom.fullName,
//				'');
//			
//			Chat.sendMessage(ChatGUI.openedRoom.id, newMessage);
//			
//			console.log(ChatGUI.openedRoom.id);
//			
//			return true;
//		},
		
		onVideoCallAccepted : function(message)
		{
			console.log('onVideoCallAccepted()');
			
			var elems = message.getElementsByTagName('body');
			
			if (Strophe.getText(elems[0])) // For call starter.
			{
				json = Strophe.getText(elems[0]).replace(new RegExp('&quot;','g'),'\"');
				
				jsonObj = $.parseJSON(json);
				
				$.ajax({
					type: 'POST',
					url: 'index.php?r=chat/getVideoCallToken',
					data: { sessionId : jsonObj.sessionId, apiKey : jsonObj.apiKey},
					success: function(token)
					{
						jsonObj.token = token;
						
						Chat.openTokInit(jsonObj);
					}
				});
			}
			else // For call recipient.
			{
				 $.ajax({
					type: 'POST',
					url: 'index.php?r=chat/initializeVideoCall',
					data: null,
					success: function(json)
					{
						Chat.openTokInit($.parseJSON(json));
						
						var to = $(message).attr('from');
						
						var sendDate = new Date();
						
						var newMessage = new InternalChatMessage(
							MessageType.VIDEO_CALL_ACCEPTED,
							sendDate,
							MethodsForDateTime.dateToString(sendDate),
							ChatGUI.openedRoom.id,
							ChatGUI.openedRoom.fullName,
							json);
						
//						Chat.sendMessage(to, json, MessageType.VIDEO_CALL_ACCEPTED);
						Chat.sendMessage(to, newMessage);
					}
				});
			}
			
			return true;
		},
		
		onVideoCallDeclined : function(message)
		{
			console.log('onVideoCallDeclined()');
		},
		changeStatus : function(statusId, statusText) {
            var pres = \$pres().c('status') .t(statusText).up().c('show').t(statusId);
            Chat.conn.send(pres.tree());

            return true;
		},
		
		openTokInit : function(openTokObj)
		{
			//var OTvideo = OTvideo || {};
			
			OTvideo.apiKey = openTokObj.apiKey;
			OTvideo.sessionId = openTokObj.sessionId;
			OTvideo.token = openTokObj.token;
			var openedRoomNickName = ChatGUI.openedRoom.id.split('@');
			OTvideo.myDiv = '#msg_' + openedRoomNickName[0];
			
			OTvideo.init();
			
			return true;
		},
		
//		onRoomMsg : function(stanza)
//		{
//			var from = stanza.getAttribute('from');
//			var to = stanza.getAttribute('to');
//			var type = stanza.getAttribute('type');
//			var content = stanza.getElementsByTagName('body');
//			
//			if (content.length == 0) return true;
//			
//			console.log('onRoomMsg: [' + type + '] ' + from + ' > ' + to + ' ' + Strophe.getText(content[0]));
//			
//			return true;
//		},
		
//		onRoomQueryOccupants : function(stanza)
//		{
//			console.log('onRoomQueryOccupants');
//			console.log(stanza);
//			
//			var jItems = $(stanza).find('item');
//			
//			console.log(jItems);
//			console.log(jItems.length);
//			
//			var occupantJids = [];
//			
//			for (var i = 0; i < jItems.length; i++)
//			{
//				occupantJids.push(jItems.eq(i).attr('jid'));
//			}
//			
//			console.log(occupantJids);
//		},
		
		onError : function(stanza)
		{
			console.log('ERROR OCCURED!');
			console.log(stanza);
		},
		
//		loadMessageCollections : function()
//		{
////			var iq = \$iq({type: 'get'}).c('query', {xmlns: Strophe.NS.ROSTER});
//			
//			Chat.conn.sendIQ(
////				\$iq({type : 'get', with : 'marina@192.237.219.76'})
////				\$iq({type : 'get', with : 'room01@conference.192.237.219.76'})
//				\$iq({type : 'get'})
////				.c('list', {xmlns : 'urn:xmpp:archive', with : 'nastassia' + '@' + Chat.domain})
////				.c('list', {xmlns : 'urn:xmpp:archive', with : 'nastassia' + '@' + Chat.domain, start : '2014-05-16T00:00:00Z'})
////				.c('list', {xmlns : 'urn:xmpp:archive', with : 'nastassia' + '@' + Chat.domain, start : '2014-05-16T07:01:40.513Z'})
////				.c('list', {xmlns : 'urn:xmpp:archive', with : 'nastassia' + '@' + Chat.domain, start : '2014-05-13T00:00:00Z', end : '2014-05-25T00:00:00Z'})
//				.c('list', {xmlns : 'urn:xmpp:archive', with : 'nastassia' + '@' + Chat.domain, end : '2014-05-17T00:00:00Z'})
//				.c('set', {xmlns : 'http://jabber.org/protocol/rsm'})
//				.c('max').t(25).up()
////				.c('after').t('2014-05-16T00:00:00Z' + 'nastassia' + '@' + Chat.domain),
////				.c('after').t(0)
////				.c('start').t('2014-05-16T00:00:00Z')
//			, Chat.onLoadMessageCollections);
//		},
		
//		onLoadMessageCollections : function(stanza)
//		{
//			console.log('onLoadMessageCollections()');
//			console.log(stanza);
//			
////			var jCollections = $(stanza).find('list chat');
////			
//////			console.log(jCollections.length);
////			
////			var jCollection = jCollections.eq(1);
////			
////			var jid = jCollection.attr('with');
////			var startTime = jCollection.attr('start');
////			
////			console.log(jid + ', ' + startTime);
////			
////			setTimeout(function() { Chat.loadMessages(jid, startTime); }, 20);
//		},
		
//		loadMessages : function(jid, startTime)
//		{
//			Chat.conn.sendIQ(
//				\$iq({type : 'get'})
//				.c('retrieve', {xmlns : 'urn:xmpp:archive', with : jid, start : startTime})
//				.c('set', {xmlns : 'http://jabber.org/protocol/rsm'})
//				.c('max').t(100),
//			Chat.onLoadMessages);
//		},
//		
//		onLoadMessages : function(stanza)
//		{
//			console.log('onLoadMessages()');
//			console.log(stanza);
//		},
		
		loadChatRoomHistory : function(room, period)
		{
			room.historyLoading = true;
			room.historyConversations = [];
			room.historyMessages = [];
			
			var currentDateTime = new Date();
			
			var periodStartDateTime = ChatHistoryPeriod.getPeriodStartDate(currentDateTime, period);
			
			var listObj = null;
			
			if (period == ChatHistoryPeriod.FROM_BEGINNING)
			{
				listObj = { xmlns : 'urn:xmpp:archive', with : room.id };
			}
			else
			{
//				listObj = { xmlns : 'urn:xmpp:archive', 'with' : room.id, 'start' : MethodsForDateTime.dateToISO8601(periodStartDateTime),
// 					'end' : MethodsForDateTime.dateToISO8601(currentDateTime) };
				listObj = { xmlns : 'urn:xmpp:archive', 'with' : room.id, 'start' : MethodsForDateTime.dateToISO8601(periodStartDateTime) };
			}
			
			var iq = \$iq({type : 'get'})
				.c('list', listObj)
				.c('set', { xmlns : 'http://jabber.org/protocol/rsm' })
				.c('max').t(500);
			
			console.log(Strophe.serialize(iq));
			
			Chat.conn.sendIQ(iq, function(stanza) { Chat.onHistoryCollectionsReceived(stanza, room); });
		},
		
		onHistoryCollectionsReceived : function(stanza, room)
		{
			var jConversations = $(stanza).find('chat');
			
			for (var i = 0; i < jConversations.length; i++)
			{
				var jConversation = jConversations.eq(i);
				
				var withAttr = jConversation.attr('with');
				
				if (withAttr != room.id) continue;
				
				var conversation = new ChatRoomHistoryConversation(withAttr, jConversation.attr('start'));
				
				room.historyConversations.push(conversation);
			}
			
			room.historyConversations.sort(function(a, b) { return a.start.localeCompare(b.start); });
			
			Chat.loadHistoryMessages(room);
		},
		
		loadHistoryMessages : function(room)
		{
			var everythingLoaded = true;
			
			for (var i = 0; i < room.historyConversations.length; i++)
			{
				var conversation = room.historyConversations[i];
				
				if (!conversation.loaded)
				{
					everythingLoaded = false;
					
					var iq = \$iq({type : 'get'})
						.c('retrieve', {xmlns : 'urn:xmpp:archive', with : conversation.with, start : conversation.start})
						.c('set', {xmlns : 'http://jabber.org/protocol/rsm'})
						.c('max').t(500);
					
					console.log(Strophe.serialize(iq));
					
					Chat.conn.sendIQ(iq, function(stanza) { Chat.onHistoryMessagesLoaded(stanza, room, conversation); });
					
					break;
				}
			}
			
			if (everythingLoaded)
			{
				console.log('HISTORY LOADED');
				
				room.historyMessages.sort(function(a, b) { return a.dateTime > b.dateTime; });
				
				room.historyLoading = false;
				
				// Removing redundant history messages.
				
				if (room.messages.length > 0)
				{
					for (var i = 0; i < room.messages.length; i++)
					{
						var message = room.messages[i];
						
						var roundedMessageDateTime = new Date(message.dateTime.getTime());
						roundedMessageDateTime.setMilliseconds(0);
						
						console.log('i: ' + i);
						console.log(message.senderJid + ' # ' + message.text + ' # ' + MethodsForDateTime.dateToString(message.dateTime, true) + ' (' +
							MethodsForDateTime.dateToString(roundedMessageDateTime, true) + ')');
						
						for (var j = room.historyMessages.length - 1; j >= 0; j--)
						{
							var historyMessage = room.historyMessages[j];
							
							var roundedHistoryMessageDateTime = new Date(historyMessage.dateTime.getTime());
							roundedHistoryMessageDateTime.setMilliseconds(0);
							
							console.log('j: ' + j);
							console.log(historyMessage.from + ' # ' + historyMessage.text + ' # ' + MethodsForDateTime.dateToString(historyMessage.dateTime, true) + ' (' +
								MethodsForDateTime.dateToString(roundedHistoryMessageDateTime, true));
							
							if (historyMessage.from == message.senderJid && historyMessage.text == message.text && roundedMessageDateTime.getTime() == roundedHistoryMessageDateTime.getTime())
							{
								console.log('!!!!!!!!!!!!!');
								
								room.historyMessages.splice(j, 1);
								j--;
								continue;
							}
							
							if (roundedMessageDateTime.getTime() > roundedHistoryMessageDateTime.getTime())
							{
								break;
							}
						}
					}
				}
				
				ChatGUI.onChatRoomHistoryLoaded(room);
			}
		},
		
		onHistoryMessagesLoaded : function(stanza, room, conversation)
		{
			var jChat = $(stanza).find('chat');
			var jMessagesTo = $(stanza).find('to');
			var jMessagesFrom = $(stanza).find('from');
			
			var startDateTime = new Date(jChat.attr('start'));
			
//			var previousMessageDateTime = new Date(startDateTime.getTime());
			
			var jNodes = jChat.children();
			
			for (var i = 0; i < jNodes.length; i++)
			{
				var jNode = jNodes.eq(i);
				var nodeName = jNode.get(0).nodeName;
				
				if (nodeName != 'to' && nodeName != 'from') continue;
				
				var seconds = parseInt(jNode.attr('secs'));
				
//				previousMessageDateTime.setSeconds(previousMessageDateTime.getSeconds() + seconds);
//				var messageDateTime = new Date(previousMessageDateTime.getTime());
				
				var messageDateTime = new Date(startDateTime.getTime());
				messageDateTime.setSeconds(messageDateTime.getSeconds() + seconds);
				
				var from = (nodeName == 'from' ? jNode.attr('jid') : '');
				
				var message = new ChatRoomHistoryMessage(from, messageDateTime, jNode.find('body').text());
				
				room.historyMessages.push(message);
			}
			
			conversation.loaded = true;
			
			Chat.loadHistoryMessages(room);
		}
	};
	
", CClientScript::POS_HEAD);