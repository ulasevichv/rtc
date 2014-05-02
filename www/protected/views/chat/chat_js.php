<?php
Yii::app()->clientScript->registerScript(uniqid(), "
	
	var Chat = {
		connection : null,
		domain : '".$xmppAddress."',
		boshAddress : '".$boshAddress."',
//		currentUser : new InternalChatUser(
//			'".$xmppUser->serverUserName."' + '@' + '".$xmppAddress."',
//			'".$xmppUser->serverUserPass."',
//			'".$xmppUser->serverUserName."',
//			'".Yii::app()->user->fullName."'),
		currentUserData : {
			jid : '".$xmppUser->serverUserName."' + '@' + '".$xmppAddress."',
			password : '".$xmppUser->serverUserPass."',
			nickname : '".$xmppUser->serverUserName."',
			fullName : '".Yii::app()->user->fullName."'
		},
		persistentRoomName : 'room01',
//		predefinedRecipientName : 'daniel',
		
		connect : function()
		{
			Chat.conn  = new Strophe.Connection(Chat.boshAddress);
			
			console.log('Connecting \'' + Chat.currentUserData.jid + '\' (' + Chat.currentUserData.password + ')');
			
			Chat.conn.connect(Chat.currentUserData.jid, Chat.currentUserData.password, function(status) { Chat.onConnectionStatusChange(status); });
		},
		
		disconnect : function()
		{
			Chat.conn.send(\$pres({
				// to : Chat.domain,
				type : 'unavailable'
				})
			);
			
			Chat.conn.send(\$pres({
				to : Chat.persistentRoomName + '@conference.' + Chat.domain + '/' + Chat.currentUserData.nickname,
				type : 'unavailable'
				}).c('x', {xmlns: Strophe.NS.MUC})
			);
			
			Chat.conn.disconnect();
			Chat.conn = null;
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
			
			
			
			Chat.getRoster();
			Chat.getRoomsList();
			
			ChatGUI.unblockControls();
		},
		
		onDisconnect : function()
		{
			alert('".Yii::t('general', 'Unable to connect to server. Please, reload the page')."');
		},
		
		getRoomsList : function()
		{
			Chat.conn.muc.listRooms('conference.' + Chat.domain, Chat.onRoomsList, Chat.onError);
		},
		
		onRoomsList : function(iq)
		{
			console.log('onRoomsList');
			
			$(iq).find('item').each(function () {
				var fullJid = $(this).attr('jid');
				var roomName = Strophe.getNodeFromJid(fullJid);
				var roomTextName = $(this).attr('name');
				
				console.log(roomName + ', ' + roomTextName);
			});
			
			console.log(Chat.conn.muc.rooms);
			
			Chat.connectToRoom(Chat.persistentRoomName);
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
			
			var currentUser = new InternalChatUser(
				$(iq).attr('to'),
				Chat.currentUserData.jid,
				Strophe.getNodeFromJid(Chat.currentUserData.jid),
				Chat.currentUserData.fullName);
			
			ChatGUI.addUser(currentUser);
			
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
		
		sendMessage : function(recipientJid, text)
		{
			console.log('sendMessage(' + recipientJid + ', ' + text +')');
			
			Chat.conn.send(\$msg({
				to : recipientJid,
				type : 'chat',
				}).c('body').t(text).up()
				.c('active', {xmlns: 'http://jabber.org/protocol/chatstates'})
			);
		},
		
//		sendMessage_old : function(userName, resource, text)
//		{
//			var jid = Chat.domain;
//			
//			if (userName != '') jid = userName + '@' + jid;
//			if (resource != '') jid = jid + '/' + resource;
//			
//			console.log('sendXmppMessage(\'' + jid + '\', \'' + text + '\')');
//			
//			Chat.conn.send(\$msg({
//				to: jid,
//				type: 'chat'
//				}).cnode(Strophe.xmlElement('body', text)).up()
//				.c('active', {xmlns: 'http://jabber.org/protocol/chatstates'})
//			);
//			
//			console.log('sent');
//		},
		
		connectToRoom : function(roomName)
		{
			console.log('Connecting to room: ' + roomName);
			
			// console.log('rooms:');
			// console.log(xmppConnection.muc.listRooms());
			
//			Chat.conn.addHandler(Chat.on_presence, null, 'presence');
			
//			Chat.conn.muc.join(roomName + '@conference.' + Chat.domain, Chat.currentUserData.nickname, Chat.onRoomMsg, Chat.onRoomPresence, function() {});
//			Chat.conn.muc.join(roomName + '@conference.' + Chat.domain, Chat.currentUserData.nickname, Chat.onRoomMsg, Chat.onPresence, function() {});
//			Chat.conn.muc.join(roomName + '@conference.' + Chat.domain, Chat.currentUserData.nickname, Chat.onRoomMsg, null, null);
			Chat.conn.muc.join(roomName + '@conference.' + Chat.domain, Chat.currentUserData.nickname, null, null, null);
			
//			Chat.conn.send(\$pres({
//				// from: Chat.currentUserData.jid,
//				to: roomName + '@conference.' + Chat.domain + '/' + Chat.currentUserData.nickname
//				}).c('x', {xmlns: Strophe.NS.MUC})
//			);
			
			Chat.conn.send(\$pres({
				to: roomName + '@conference.' + Chat.domain + '/' + Chat.currentUserData.nickname
				})
			);
			
//			Chat.conn.muc.queryOccupants(roomName + '@conference.' + Chat.domain, Chat.onRoomQueryOccupants, null);
		},
		
		onPresence : function(stanza)
		{
			var from = $(stanza).attr('from');
			var to = $(stanza).attr('to');
			var fullJid = from;
			var bareJid = Strophe.getBareJidFromJid(fullJid);
			var jidId = Strophe.getNodeFromJid(fullJid);
			var resource = Strophe.getResourceFromJid(fullJid);
			
			var presenceType = $(stanza).attr('type');
			if (typeof(presenceType) == 'undefined') presenceType = 'available';
			
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
					
					if (bareJid == Chat.currentUserData.jid && from != to) return true; // Unwanted status of current user from previous sessions.
					
					var user = ChatGUI.getUserByBareJid(bareJid);
					
					if (user != null)
					{
						if (user.fullJid != '' && user.fullJid != fullJid)
						{
							console.log('!!!!!!!!!!! ' + user.fullJid + ' | ' + fullJid);
							return true;
						}
						
						if (presenceType !== 'error')
						{
							ChatGUI.updateUser(bareJid, fullJid, (presenceType == 'available'));
						}
					}
					
					break;
				}
				case StanzaSourceType.ROOM:
				{
					console.log('onRoomPresence: ' + fullJid + ' (' + bareJid + ', ' + jidId + ') > ' + to + ' > ' + presenceType);
					
					var roomName = bareJid;
					
					var room = ChatGUI.getRoomById(roomName);
					
					// Creating room.
					
					if (presenceType == 'available' && resource == Chat.currentUserData.nickname && room == null)
					{
						var xmppRoom = Chat.conn.muc.rooms[bareJid];
						
						console.log('Connected to room: ' + bareJid);
						console.log(xmppRoom);
						
						var room = new InternalChatRoom(bareJid, jidId);
						
						ChatGUI.rooms.push(room);
						
						ChatGUI.refreshRooms();
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
//			if (typeof(type) == 'undefined') type = 'available';
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
				
				if (user != null)
				{
					var newMessage = new InternalChatMessage(
						MessageType.CHAT,
						MethodsForDateTime.dateToString(new Date()),
						user.bareJid,
						user.fullName,
						text);
					
					// ChatGUI.addChatMessages([newMessage]);
					ChatGUI.addChatMessage(newMessage);
				}
			}
			else if (type == MessageType.GROUPCHAT)
			{
				var roomJid = bareJid;
				
				var room = ChatGUI.getRoomById(roomJid);
				
				console.log('onRoomMessage: [' + roomJid + '] ' + from + ' > ' + to + ' > ' + text);
				
				var sendDateString = '';
				
				var jX = $(stanza).find('x[xmlns=\"jabber:x:delay\"]');
				
				if (jX.length != 0)
				{
					var xmppStamp = jX.attr('stamp');
					
					sendDateString = MethodsForDateTime.xmppStampToDateString(xmppStamp);
				}
				
				if (sendDateString == '') sendDateString = MethodsForDateTime.dateToString(new Date());
				
				var senderBareJid = resource + '@' + Strophe.getDomainFromJid(from).replace('conference.', '');
				
				console.log('senderBareJid: ' + ', ' + senderBareJid);
				
				var sender = ChatGUI.getUserByBareJid(senderBareJid);
				
				console.log(sender);
				
				if (room != null && sender != null)
				{
					var newMessage = new InternalChatMessage(
						MessageType.GROUPCHAT,
						sendDateString,
						sender.bareJid,
						sender.fullName,
						text,
						roomJid);
					
					ChatGUI.addChatMessage(newMessage);
				}
			}
			
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
		
		onRoomQueryOccupants : function(stanza)
		{
			console.log('onRoomQueryOccupants');
			console.log(stanza);
			
			var jItems = $(stanza).find('item');
			
			console.log(jItems);
			console.log(jItems.length);
			
			var occupantJids = [];
			
			for (var i = 0; i < jItems.length; i++)
			{
				occupantJids.push(jItems.eq(i).attr('jid'));
			}
			
			console.log(occupantJids);
		},
		
//		on_presence : function(stanza)
//		{
//			console.log('on_presence');
//			console.log(stanza);
//		},
		
		onError : function(stanza)
		{
			console.log('ERROR OCCURED!');
			console.log(stanza);
		}
	};
	
", CClientScript::POS_HEAD);