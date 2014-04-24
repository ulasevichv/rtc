<?php
Yii::app()->clientScript->registerScript(uniqid(), "
	
	var Chat = {
		connection : null,
		domain : '".$xmppAddress."',
		boshAddress : '".$boshAddress."',
		currentUser : {
			jid : '".$xmppUser->serverUserName."' + '@' + '".$xmppAddress."',
			name : '".$xmppUser->serverUserName."',
			password : '".$xmppUser->serverUserPass."'
		},
		persistentRoomName : 'room01',
		predefinedRecipientName : 'daniel',
		
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
				}).c('x', {'xmlns': 'http://jabber.org/protocol/muc'})
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

			RosterObj.connection = Chat.conn;
			
			
			
			var iq = \$iq({type: 'get'}).c('query', {xmlns: 'jabber:iq:roster'});
			Chat.conn.sendIQ(iq, RosterObj.on_roster);
			Chat.conn.addHandler(RosterObj.on_roster_changed, 'jabber:iq:roster', 'iq', 'set');
			
			
			
			
			
			
			
			Chat.conn.addHandler(Chat.onMessage, null, 'message', null, null, null);
			
			Chat.conn.send(\$pres());
			
			// Chat.conn.addHandler(Chat.handlePong, null, 'iq', null, 'ping1');
			// Chat.sendPing();
			
			// refreshUsers();
			
			
			
			
			Chat.connectToRoom(Chat.persistentRoomName);
			
			
			ChatGUI.unblockControls();
		},
		
		onDisconnect : function()
		{
			alert('".Yii::t('general', 'Unable to connect to server. Please, reload the page')."');
		},
		
		sendPing : function()
		{
			
			
			console.log('Sending ping to ' + Chat.domain + '.');
			
			Chat.conn.send(\$iq({
				to: Chat.domain,
				type: 'get',
				id: 'ping1'
				}).c('ping', {xmlns: 'urn:xmpp:ping'})
			);
		},
		
		handlePong : function(iq)
		{
			console.log('Received pong from server');
			
			return false;
		},
		
		sendMessage : function(userName, resource, text)
		{
			var jid = Chat.domain;
			
			if (userName != '') jid = userName + '@' + jid;
			if (resource != '') jid = jid + '/' + resource;
			
			console.log('sendXmppMessage(\'' + jid + '\', \'' + text + '\')');
			
			Chat.conn.send(\$msg({
				to: jid,
				type: 'chat'
				}).cnode(Strophe.xmlElement('body', text)).up()
				.c('active', {xmlns: 'http://jabber.org/protocol/chatstates'})
			);
			
			console.log('sent');
		},
		
		onMessage : function(msg)
		{
			console.log(msg);
			
			var to = msg.getAttribute('to');
			var from = msg.getAttribute('from');
			var type = msg.getAttribute('type');
			var elems = msg.getElementsByTagName('body');
			
			if (type == 'chat' && elems.length > 0)
			{
				var body = elems[0];
				
				console.log('Received message from \'' + from + '\': \'' + Strophe.getText(body) + '\'');
			}
			
			return true;
		},
		
		connectToRoom : function(roomName)
		{
			// console.log('rooms:');
			// console.log(xmppConnection.muc.listRooms());
			
//			Chat.conn.muc.join(roomName + '@conference.' + Chat.domain, Chat.currentUser.name, Chat.onRoomMsg, Chat.onRoomPresence, function() {});
			Chat.conn.muc.join(roomName + '@conference.' + Chat.domain, Chat.currentUser.name, Chat.onRoomMsg, function() {}, function() {});
			
			Chat.conn.addHandler(Chat.on_presence, null, 'presence');
			
//			Chat.conn.send(\$pres({
//				// from: Chat.currentUser.jid,
//				to: roomName + '@conference.' + Chat.domain + '/' + Chat.currentUser.name
//				}).c('x', {'xmlns': 'http://jabber.org/protocol/muc'})
//			);
			
			console.log('Connected to room');
			
			Chat.conn.muc.queryOccupants(roomName + '@conference.' + Chat.domain, Chat.onRoomQueryOccupants, null);
		},
		
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
		
		onRoomMsg : function(stanza)
		{
			console.log('onRoomMsg');
			console.log(stanza);
		},
		
		onRoomPresence : function(stanza)
		{
			console.log('onRoomPresence');
			console.log(stanza);
		},
		
		on_presence : function(stanza)
		{
			console.log('on_presence');
			console.log(stanza);
		}
	};
	
", CClientScript::POS_HEAD);