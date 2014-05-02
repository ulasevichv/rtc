<?php
Yii::app()->clientScript->registerScript(uniqid(), "
	
	var Chat = {
		connection : null,
		domain : '".$xmppAddress."',
		boshAddress : '".$boshAddress."',
		currentUser : {
			jid : '".$xmppUser->serverUserName."' + '@' + '".$xmppAddress."',
			name : '".$xmppUser->serverUserName."',
			password : '".$xmppUser->serverUserPass."',
			fullName : '".Yii::app()->user->fullName."'
		},
		persistentRoomName : 'room01',
//		predefinedRecipientName : 'daniel',
		
		connect : function()
		{
			Chat.conn  = new Strophe.Connection(Chat.boshAddress);
			
			console.log('Connecting \'' + Chat.currentUser.jid + '\' (' + Chat.currentUser.password + ')');
			
			Chat.conn.connect(Chat.currentUser.jid, Chat.currentUser.password, function(status) { Chat.onConnectionStatusChange(status); });
		},
		
		disconnect : function()
		{
			Chat.conn.send(\$pres({
				// to : Chat.domain,
				type : 'unavailable'
				})
			);
			
			Chat.conn.send(\$pres({
				to : Chat.persistentRoomName + '@conference.' + Chat.domain + '/' + Chat.currentUser.name,
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
		
//		onAuth : function(iq)
//		{
//			console.log('onAuth');
//		},
		
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
			
			var currentUser = {
				fullJid : $(iq).attr('to'),
				bareJid : Chat.currentUser.jid,
				nickname : Strophe.getNodeFromJid(Chat.currentUser.jid),
				fullName : Chat.currentUser.fullName,
				online : false
			};
			
			ChatGUI.addUser(currentUser);
			
			$(iq).find('item').each(function () {
				var bareJid = $(this).attr('jid');
				var nickname = Strophe.getNodeFromJid(bareJid);
				var userName = $(this).attr('name') || bareJid;
				
				console.log(userName + '(' + nickname + ')');
				
				var user = {
					fullJid : '',
					bareJid : bareJid,
					nickname : nickname,
					fullName : userName,
					online : false
				};
				
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
			
//			Chat.conn.muc.join(roomName + '@conference.' + Chat.domain, Chat.currentUser.name, Chat.onRoomMsg, Chat.onRoomPresence, function() {});
//			Chat.conn.muc.join(roomName + '@conference.' + Chat.domain, Chat.currentUser.name, Chat.onRoomMsg, Chat.onPresence, function() {});
//			Chat.conn.muc.join(roomName + '@conference.' + Chat.domain, Chat.currentUser.name, Chat.onRoomMsg, null, null);
			Chat.conn.muc.join(roomName + '@conference.' + Chat.domain, Chat.currentUser.name, null, null, null);
			
//			Chat.conn.send(\$pres({
//				// from: Chat.currentUser.jid,
//				to: roomName + '@conference.' + Chat.domain + '/' + Chat.currentUser.name
//				}).c('x', {xmlns: Strophe.NS.MUC})
//			);
			
			Chat.conn.send(\$pres({
				to: roomName + '@conference.' + Chat.domain + '/' + Chat.currentUser.name
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
					
					if (bareJid == Chat.currentUser.jid && from != to) return true; // Unwanted status of current user from previous sessions.
					
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
					
					if (presenceType == 'available' && resource == Chat.currentUser.name && room == null)
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
			
			if (type == 'chat')
			{
				console.log('onDirectMessage: ' + from + ' > ' + to + ' > ' + text);
				
				var user = ChatGUI.getUserByBareJid(Strophe.getBareJidFromJid(from));
				
				if (user != null)
				{
					var newMessage = new InternalChatMessage(
						MethodsForDateTime.dateToString(new Date()),
						user.bareJid,
						user.fullName,
						text);
					
					// ChatGUI.addChatMessages([newMessage]);
					ChatGUI.addChatMessage(newMessage);
				}
			}
			else if (type == 'groupchat')
			{
				console.log('onRoomMessage: ' + from + ' > ' + to + ' > ' + text);
				
				var room = ChatGUI.getRoomById(bareJid);
				
				console.log(room);
				
				var newMessage = new InternalChatMessage(
					MethodsForDateTime.dateToString(new Date()),
					user.bareJid,
					user.fullName,
					text);
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