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
			
//			Chat.conn.muc.init(Chat.conn);
//			Chat.conn.roster.init(Chat.conn);
			
			
			
			
			
			
			
			
			
			
			
//			RosterObj.connection = Chat.conn;
			
//			var iq = \$iq({type: 'get'}).c('query', {xmlns: 'jabber:iq:roster'});
//			Chat.conn.sendIQ(iq, RosterObj.on_roster);
//			Chat.conn.addHandler(RosterObj.on_roster_changed, 'jabber:iq:roster', 'iq', 'set');
			
			
			
			
//			Chat.conn.addHandler(Chat.onMessage, null, 'message', null, null, null);
//			
//			Chat.conn.send(\$pres());
//			
//			// Chat.conn.addHandler(Chat.handlePong, null, 'iq', null, 'ping1');
//			// Chat.sendPing();
//			
//			// refreshUsers();
//			
//			
//			
//			
//			Chat.connectToRoom(Chat.persistentRoomName);
			
			
			
			
			
//			var iq = \$iq({type: 'get'}).c('query', {xmlns: 'jabber:iq:auth'});
//			Chat.conn.sendIQ(iq, Chat.onAuth);
			
			
			
			
			var iq = \$iq({type: 'get'}).c('query', {xmlns: 'jabber:iq:roster'});
			Chat.conn.sendIQ(iq, Chat.onRoster);
			Chat.conn.addHandler(Chat.onRosterChange, 'jabber:iq:roster', 'iq', 'set');
			Chat.conn.addHandler(Chat.onMessage, null, 'message', 'chat');
			Chat.conn.addHandler(Chat.onVideoCall, null, 'message', 'videoCall');
			Chat.conn.addHandler(Chat.onVideoCallAccepted, null, 'message', 'VideoCallAccepted');

			
			
			
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
			
			Chat.conn.addHandler(Chat.onPresence, null, 'presence');
       		Chat.conn.send(\$pres());
		},
		
		onPresence : function(presence)
		{
			var pType = $(presence).attr('type');
			if (typeof(pType) == 'undefined') pType = 'available';
			
			var from = $(presence).attr('from');
			var to = $(presence).attr('to');
			var fullJid = from;
			var bareJid = Strophe.getBareJidFromJid(fullJid);
			var jidId = Strophe.getNodeFromJid(fullJid);
			
			console.log('onPresence: ' + pType + ', ' + fullJid + ', ' + bareJid + ', ' + jidId);
			
			if (bareJid == Chat.currentUser.jid && from != to) return true; // Unwanted status of current user from previous sessions.
			
			var user = ChatGUI.getUserByBareJid(bareJid);
			
			if (user != null)
			{
				if (user.fullJid != '' && user.fullJid != fullJid)
				{
					console.log('!!!!!!!!!!! ' + user.fullJid + ' | ' + fullJid);
					return true;
				}
				
				if (pType !== 'error')
				{
					ChatGUI.updateUser(bareJid, fullJid, (pType == 'available'));
				}
			}
			
			return true;
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
		
		sendMessage : function(recipientJid, text, type)
		{
			console.log('sendMessage(' + recipientJid + ', ' + text +')');

			if (typeof(type)=='undefined') {
			    type='chat';
			}
			Chat.conn.send(\$msg({
				to : recipientJid,
				type : type,
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
		
		onMessage : function(msg)
		{
			var to = $(msg).attr('to');
			var from = $(msg).attr('from');
			var type = $(msg).attr('type');
			var jBody = $(msg).find('body');
			
			console.log('onMessage: ' + from + ', ' + type);
			
			if (type == 'chat' && jBody.length != 0)
			{
				var text = jBody.text();
				
				console.log(text);
				
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
			
			return true;
		},

		onVideoCall : function(msg)
		{
			var to = $(msg).attr('to');
			var from = $(msg).attr('from');
			var type = $(msg).attr('type');
			var jBody = $(msg).find('body');

            var user = ChatGUI.getUserByBareJid(Strophe.getBareJidFromJid(from));
			console.log('onVideoCall: ' + from + ', ' + type);

			var text = 'Start video call with ' + user.fullName + '?';



			var newMessage = new InternalChatMessage(
				MethodsForDateTime.dateToString(new Date()),
				user.bareJid,
				user.fullName,
				text);

			// ChatGUI.addChatMessages([newMessage]);
			ChatGUI.addChatMessage(newMessage);
			ChatGUI.addVideoCallInvitationControls(user.bareJid);


			return true;
		},
		acceptVideoCall : function () {
		    Chat.sendMessage(ChatGUI.openedRoom.id,'','VideoCallAccepted');
		    console.log(ChatGUI.openedRoom.id);
		    return true;
		},
		onVideoCallAccepted : function(message) {

            var elems = message.getElementsByTagName('body');
            window.txtt = Strophe.getText(elems[0]);
            if (Strophe.getText(elems[0])) {
                json = Strophe.getText(elems[0]).replace(new RegExp('&quot;','g'),'\"');
                jsonObj = $.parseJSON(json);
                $.ajax({
                    type: 'POST',
                    url: 'index.php?r=chat/videocallToken',
                    data: {'sessionId':jsonObj.sessionId,'apiKey':jsonObj.apiKey},
                    success: function(token) {
                        jsonObj.token = token;
                        Chat.openTokInit(jsonObj);
                    }
                });

            } else {
                 $.ajax({
                    type: 'POST',
                    url: 'index.php?r=chat/videocall',
                    data: null,
                    success: function(json) {
                        Chat.openTokInit($.parseJSON(json));
                        var to = $(message).attr('from');
                        Chat.sendMessage(to,json,'VideoCallAccepted');

                    }
                });
            }

		    return true;
		},
        openTokInit : function(openTokObj)
        {

            var apiKey = openTokObj.apiKey;
            var sessionId = openTokObj.sessionId;
            var token = openTokObj.token;

          // Initialize session, set up event listeners, and connect
          var session = TB.initSession(apiKey, sessionId);
          $('#videoChat').append('<div id=\"myvideo\"></div>');
//          var publisher = OT.initPublisher('myvideo');
          $('#videoChat').show();
          session.connect(token, function(error) {
              var publisher = OT.initPublisher('myvideo');
              session.publish(publisher);
          });
            session.on('streamCreated', function(event) {
            var id = 'video-' + event.stream.connection.connectionId;
            $('#videoChat').append('<div class=\"other-video\" style=\"float: left;\" id=\"' + id + '\"></div>');
                  session.subscribe(event.stream, id);
                });


          return true;
        },
		onMessage_old : function(msg)
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
			
			Chat.conn.addHandler(Chat.on_presence, null, 'presence');
			
			Chat.conn.muc.join(roomName + '@conference.' + Chat.domain, Chat.currentUser.name, Chat.onRoomMsg, Chat.onRoomPresence, function() {});
//			Chat.conn.muc.join(roomName + '@conference.' + Chat.domain, Chat.currentUser.name, Chat.onRoomMsg, function() {}, function() {});
			
			
			
//			Chat.conn.send(\$pres({
//				// from: Chat.currentUser.jid,
//				to: roomName + '@conference.' + Chat.domain + '/' + Chat.currentUser.name
//				}).c('x', {'xmlns': 'http://jabber.org/protocol/muc'})
//			);
			
			console.log('Connected to room');
			
//			Chat.conn.muc.queryOccupants(roomName + '@conference.' + Chat.domain, Chat.onRoomQueryOccupants, null);
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