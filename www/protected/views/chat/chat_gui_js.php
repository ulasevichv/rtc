<?php
Yii::app()->clientScript->registerScript(uniqid('chat_gui'), "
	
	var ChatGUI = {
		
		users : [],
		rooms : [
			new InternalChatRoom('dashboard', MessageType.CHAT, 'Dashboard')
		],
		openedRoom : null,
		staticRooms : [],
		chatSize : null,
		sendingDivSize : null,
		roomOnlineStatusesPull : [],
		initialPageTitle : '',
		pageTitleTimerId : -1,
		pageTitleAnimationStep : 0,
		
		getUserByBareJid : function(bareJid)
		{
			for (var i = 0; i < ChatGUI.users.length; i++)
			{
				var user = ChatGUI.users[i];
				
				if (user.bareJid == bareJid) return user;
			}
			
			return null;
		},
		
		getRoomById : function(id)
		{
			for (var i = 0; i < ChatGUI.rooms.length; i++)
			{
				var room = ChatGUI.rooms[i];
				
				if (room.id == id) return room;
			}
			
			return null;
		},
		
		getStaticRoomByName : function(name)
		{
			for (var i = 0; i < ChatGUI.staticRooms.length; i++)
			{
				var staticRoom = ChatGUI.staticRooms[i];
				
				if (staticRoom.name == name) return staticRoom;
			}
			
			return null;
		},
		
		getRoomMessageContainerDiv : function(room)
		{
			var roomMsgContainerId = 'msg_' + Strophe.getNodeFromJid(room.id);
			
			var jMsgContainerDiv = $('#' + roomMsgContainerId);
			
			return jMsgContainerDiv;
		},
		
		blockControls : function()
		{
			ChatGUI.changeControlsAvailability(false);
		},
		
		unblockControls : function()
		{
			ChatGUI.changeControlsAvailability(true);
		},
		
		changeControlsAvailability : function(value)
		{
			var jControls = [
				$('#inputMessage'),
				$('#btnSend')
			];
			
			if (value)
			{
				jControls.forEach(function(jControl, i)
				{
					jControl.removeAttr('disabled');
				});
			}
			else
			{
				jControls.forEach(function(jControl, i)
				{
					jControl.attr('disabled', '');
				});
			}
		},
		
		getChatSize : function()
		{
			var jChat = $('#chat');
			
			return { x : jChat.width(), y : jChat.height() };
		},
		
		getSendingDivSize : function()
		{
			var jSending = $('#sending');
			
			return { x : jSending.width(), y : jSending.height() };
		},
		
		onWindowResize : function(initialCall)
		{
			initialCall = (typeof(initialCall) != 'undefined' ? initialCall : false);
			
			var newChatSize = ChatGUI.getChatSize();
			var newSendingDivSize = ChatGUI.getSendingDivSize();
			
			if (initialCall || newSendingDivSize.x != ChatGUI.sendingDivSize.x)
			{
				var jInputMessage = $('#inputMessage');
				var jBtnSend = $('#btnSend');
				
				jInputMessage.css('width', (newSendingDivSize.x - parseInt(jBtnSend.width()) - 35) + 'px');
			}
			
			if (initialCall || newChatSize.y != ChatGUI.chatSize.y)
			{
				var jMessages = $('#messages');
				var jSending = $('#sending');
				
				jMessages.css('height', (newChatSize.y - parseInt(jSending.height()) - 13) + 'px');
			}
			
			ChatGUI.chatSize = newChatSize;
			ChatGUI.sendingDivSize = newSendingDivSize;
			ChatGUI.resizeChatTextDiv();


		},
		
		resizeChatTextDiv : function ()
		{
			var videoHeight = 0;
			var videoToggleHeight = 0;
			var containerDiv = '';
			if (ChatGUI.openedRoom) {
				containerDiv = '#msg_'+ Strophe.getNodeFromJid(ChatGUI.openedRoom.id);
			} else {
				containerDiv = '';
			}
			
			if ($('.video').is(':visible')) {
				videoHeight = $(containerDiv +' .video').outerHeight()
			}
			if ($('.video-toggle').is(':visible')) {
				videoToggleHeight = $(containerDiv +' .video-toggle').outerHeight()
			}
//			console.log(videoHeight);
//			console.log(videoToggleHeight);
//			console.log($(containerDiv+ '.msgContainer').outerHeight());
			$('.chat-text').css('height',($(containerDiv+ '.msgContainer').outerHeight() - videoToggleHeight - videoHeight) + 'px');
			return true
		},
		
		scrollOpenedMessagesToBottom : function ()
		{
			if (ChatGUI.openedRoom != null)
			{
				var roomNameForId = Strophe.getNodeFromJid(ChatGUI.openedRoom.id);
				
				if (roomNameForId == null) return;
				
				var containerDivId = '#msg_' + roomNameForId;
				
				var jTextDiv = $(containerDivId + ' .chat-text');
				
				if (jTextDiv.length == 0) return;
				
				jTextDiv.animate({scrollTop: jTextDiv.prop(\"scrollHeight\")}, 500);
			}
		},
		
		addUser : function(user)
		{
			if (ChatGUI.users.indexOf(user) != -1) return;
			
			ChatGUI.users.push(user);
			
			ChatGUI.refreshUsers();
		},
		
		updateUser : function(bareJid, fullJid, online)
		{
			console.log(bareJid + ', ' + online);
			
			var user = ChatGUI.getUserByBareJid(bareJid);
			
			if (user == null) return;
			
			user.fullJid = fullJid;
			user.online = online;
			
			var jUser = $('#users .user[bareJid=\"' + bareJid + '\"]');
			
			if (user.online)
			{
				jUser.addClass('online');
			}
			else
			{
				jUser.removeClass('online');
			}
			
			ChatGUI.refreshUsers();
		},
		
		refreshUsers : function()
		{
			ChatGUI.users.sort(function(a, b) {
				if (b.online && !a.online) return 1;
				else if (a.online && !b.online) return -1;
				
				return a.fullName.localeCompare(b.fullName);
			});
			
			// Moving current user to top.
			
			var currentUser = null;
			
			for (var i = 0; i < ChatGUI.users.length; i++)
			{
				var user = ChatGUI.users[i];
				
				if (user.bareJid == Chat.currentUser.bareJid)
				{
					currentUser = user;
					ChatGUI.users.splice(i, 1);
					break;
				}
			}
			
			ChatGUI.users.unshift(currentUser);
			
			// Generating HTML.
			
			var feed = [];
			
			ChatGUI.users.forEach(function(user, i)
			{
				var onlineStatusClass = (user.online ? ' online' : '');
				
				feed.push('<div class=\"user' + onlineStatusClass + '\" bareJid=\"' + user.bareJid + '\">');
				feed.push(	'<div class=\"icon\"></div>');
				feed.push(	'<div class=\"text\">');
				feed.push(		user.fullName);
				feed.push(	'</div>');
				feed.push('</div>');
			});
			
			$('#users').html(feed.join(''));
			
			ChatGUI.updateUsersVisibility();
		},
		
		updateUsersVisibility : function()
		{
			var jSending = $('#sending');
			
			if (ChatGUI.openedRoom == null) return;
			
			if (ChatGUI.openedRoom == ChatGUI.getRoomById('dashboard'))
			{
				jSending.css('visibility', 'hidden');
				
				ChatGUI.users.forEach(function(user, i)
				{
					var jUser = $('#users .user[barejid=\"' + user.bareJid  + '\"]');
					
					jUser.css('display', 'block');
				});
			}
			else
			{
				jSending.css('visibility', 'visible');
				
				if (ChatGUI.openedRoom.type == MessageType.CHAT)
				{
					ChatGUI.users.forEach(function(user, i)
					{
						var jUser = $('#users .user[bareJid=\"' + user.bareJid  + '\"]');
						
						if (user.bareJid == Chat.currentUser.bareJid || user.bareJid == ChatGUI.openedRoom.id)
						{
							jUser.css('display', 'block');
						}
						else
						{
							jUser.css('display', 'none');
						}
					});
				}
				else if (ChatGUI.openedRoom.type == MessageType.GROUP_CHAT)
				{
					ChatGUI.users.forEach(function(user, i)
					{
						var jUser = $('#users .user[bareJid=\"' + user.bareJid  + '\"]');
						
						if (user.bareJid == Chat.currentUser.bareJid || (user.online && ChatGUI.openedRoom.isParticipantOnline(user.nickname)))
						{
							jUser.css('display', 'block');
						}
						else
						{
							jUser.css('display', 'none');
						}
					});
				}
			}
		},
		
		refreshRooms : function()
		{
			var feed = [];
			
			for (var i = 0; i < ChatGUI.rooms.length; i++)
			{
				var room = ChatGUI.rooms[i];
				
				if (room.hidden) continue;
				
				var openedRoomWasUnread = false;
				
				if (room.unread && room == ChatGUI.openedRoom)
				{
					room.unread = false;
					openedRoomWasUnread = true;
				}
				
				var openedAttr = (room == ChatGUI.openedRoom ? ' opened' : '');
				var unreadAttr = (room.unread ? ' unread' : '');
				
				feed.push('<div class=\"room\" roomId=\"' + room.id + '\"' + openedAttr + unreadAttr + '>');
				feed.push(	'<div class=\"icon\"></div>');
				feed.push(	'<div class=\"text\">');
				feed.push(		room.fullName);
				feed.push(	'</div>');
				
				if (room.id != 'dashboard')
				{
					feed.push(	'<span class=\"btnClose ui-button ui-state-hover\" title=\"".Yii::t('general', 'Close')."\">');
					feed.push(		'<span class=\"ui-icon ui-icon-closethick\"></span>');
					feed.push(	'</span>');
					
				}
				
				feed.push('</div>');
			}
			
			$('#rooms').html(feed.join(''));
			
			ChatGUI.updateChatTitle();
			
			ChatGUI.updateUsersVisibility();
			
			// Updating messages.
			
			var jMessages = $('#messages');
			
			if (ChatGUI.openedRoom == ChatGUI.getRoomById('dashboard'))
			{
				jMessages.children().css('display', 'none');
			}
			else
			{
				jMessages.children().css('display', 'none');
				
				var openedRoomMsgContainerId = 'msg_' + Strophe.getNodeFromJid(ChatGUI.openedRoom.id);
				
				var jMsgContainerDiv = ChatGUI.getRoomMessageContainerDiv(ChatGUI.openedRoom);
				
				if (jMsgContainerDiv.length == 0)
				{
					jMessages.append(
						'<div id=\"' + openedRoomMsgContainerId + '\" class=\"msgContainer\">' +
							'<div class=\"video\"  style=\"display: none;\"></div>' +
							'<div class=\"video-toggle btn btn-primary\" style=\"display: none;\">Show/Hide video</div>' +
							'<div style=\"clear: both\"></div>' +
							'<div class=\"text chat-text\"></div>' +
						'</div>'
					);
					
					jMsgContainerDiv = $('#' + openedRoomMsgContainerId);
				}
				else
				{
					jMsgContainerDiv.css('display', 'block');
				}
				
				var feed = [];
				
				feed.push('<div class=\"chat_history_controls\">');
				feed.push(	'<div class=\"clock\"></div>');
				feed.push(	'<div class=\"descr\">".Yii::t('chat', 'Show messages from').":</div>');
				feed.push(	'<div class=\"period\">".Yii::t('chat', 'Yesterday')."</div>');
				feed.push(	'<div class=\"period\">".Yii::t('chat', '7 days')."</div>');
				feed.push(	'<div class=\"period\">".Yii::t('chat', '30 days')."</div>');
				feed.push(	'<div class=\"period\">".Yii::t('chat', '3 months')."</div>');
				feed.push(	'<div class=\"period\">".Yii::t('chat', '6 months')."</div>');
				feed.push(	'<div class=\"period\">".Yii::t('chat', '1 year')."</div>');
				feed.push(	'<div class=\"period\">".Yii::t('chat', 'From Beginning')."</div>');
				feed.push('</div>');
				
				for (var i = 0; i < ChatGUI.openedRoom.messages.length; i++)
				{
					var message = ChatGUI.openedRoom.messages[i];
					
					var blockType = (message.senderJid == Chat.currentUser.bareJid ? 'outgoing' : 'incoming');
					
					feed.push('<div class=\"message ' + blockType + '\">');
					feed.push(	'<div class=\"from\">');
					feed.push(		message.senderFullName);
					feed.push(	'</div>');
					feed.push(	'<div class=\"text\">');
					feed.push(		message.text);
					feed.push(	'</div>');
					feed.push(	'<div class=\"time\">');
					feed.push(		message.time);
					feed.push(	'</div>');
					feed.push('</div>');
				}
				
				jMsgContainerDiv.find('.text').html(feed.join(''));
				
				ChatGUI.resizeChatTextDiv();
			}
			
			if (openedRoomWasUnread)
			{
				ChatGUI.scrollOpenedMessagesToBottom();
			}
			
			// Updating page title.
			
			var thereAreUnreadMessages = false;
			
			for (var i = 0; i < ChatGUI.rooms.length; i++)
			{
				var room = ChatGUI.rooms[i];
				
				if (room.unread)
				{
					thereAreUnreadMessages = true;
					break;
				}
			}
			
			if (thereAreUnreadMessages)
			{
				ChatGUI.showDesktopNotification();
				ChatGUI.startPageTitleAnimation();
			}
			else
			{
				ChatGUI.stopPageTitleAnimation();
			}
		},
		
		setRoomLoadingState : function(roomJid, value)
		{
			var room = ChatGUI.getRoomById(roomJid);
			
			var jMsgContainerDiv = ChatGUI.getRoomMessageContainerDiv(room);
			var jTextDiv = jMsgContainerDiv.find('.text');
			
			if (value)
			{
				jTextDiv.attr('loading', '');
				setTimeout(function() { ChatGUI.setRoomLoadingState(roomJid, false); }, 4000);
			}
			else
			{
				jTextDiv.removeAttr('loading');
			}
		},
		
		loadChatHistory : function(room, period)
		{
			var jMsgContainerDiv = ChatGUI.getRoomMessageContainerDiv(room);
			var jTextDiv = jMsgContainerDiv.find('.text');
			
			jTextDiv.attr('loading', '');
			
			var periodIndex = ChatHistoryPeriod.getPeriodIndex(period);
			
			var jPeriodDiv = jTextDiv.find('> .chat_history_controls > .period:nth-child(' + (periodIndex + 3) + ')');
			
			jPeriodDiv.attr('active', '');
			
			Chat.loadChatHistory(room, period);
		},
		
		startPageTitleAnimation : function()
		{
			ChatGUI.stopPageTitleAnimation();
			ChatGUI.pageTitleTimerId = setInterval(function() { ChatGUI.performPageTitleAnimationStep(); }, 500);
		},
		
		stopPageTitleAnimation : function()
		{
			clearInterval(ChatGUI.pageTitleTimerId);
			
			ChatGUI.pageTitleAnimationStep = 0;
			
			$(document).attr('title', ChatGUI.initialPageTitle);
		},
		
		performPageTitleAnimationStep : function()
		{
			var title = '".Yii::t('general', 'New message')."';
			
			switch (ChatGUI.pageTitleAnimationStep)
			{
				case 0: title = title; break;
				case 1: title = '> ' + title; break;
				case 2: title = '>> ' + title; break;
				case 3: title = '>>> ' + title; break;
			}
			
			if (ChatGUI.pageTitleAnimationStep == 3) ChatGUI.pageTitleAnimationStep = 0;
			else ChatGUI.pageTitleAnimationStep++;
			
			$(document).attr('title', title);
		},
		
		showDesktopNotification : function()
		{
			if (!window.webkitNotifications)
			{
				alert('".Yii::t('general', 'Notifications are not allowed')."');
				return;
			}
			
			var havePermission = window.webkitNotifications.checkPermission();
			
			if (havePermission == 0)
			{
				var notification = window.webkitNotifications.createNotification(
					'".Yii::app()->theme->baseUrl."/assets/images/chat/exclamation_mark_green_48.png',
					'".Yii::t('general', 'New message')."',
					'".Yii::t('general', 'You have unread messages')."'
				);
				
				notification.onclick = function ()
				{
					window.focus();
					this.cancel();
				};
				
				notification.show();
			}
			else
			{
				window.webkitNotifications.requestPermission();
			}
		},
		
		refreshStaticRooms : function()
		{
			var feed = [];
			
			for (var i = 0; i < ChatGUI.staticRooms.length; i++)
			{
				var staticRoom = ChatGUI.staticRooms[i];
				
				feed.push('<div class=\"room\" jid=\"' + staticRoom.jid + '\" roomName=\"' + staticRoom.name + '\">');
				feed.push(	'<div class=\"text\">');
				feed.push(		staticRoom.fullName);
				feed.push(	'</div>');
				feed.push('</div>');
			}
			
			$('#staticRooms').html(feed.join(''));
		},
		
		openPrivateChatRoom : function(user)
		{
			var roomJid = user.bareJid;
			
			var room = ChatGUI.getRoomById(roomJid);
			
			if (room == null)
			{
				room = new InternalChatRoom(roomJid, MessageType.CHAT, user.fullName);
				ChatGUI.rooms.push(room);
				
//				room.loading = true;
				
				ChatGUI.openedRoom = room;
				ChatGUI.refreshRooms();
				
//				ChatGUI.setRoomLoadingState(room.id, true);
				
//				ChatGUI.loadChatHistory(room, ChatHistoryPeriod.YESTERDAY);
//				ChatGUI.loadChatHistory(room, ChatHistoryPeriod.SEVEN_DAYS);
				ChatGUI.loadChatHistory(room, ChatHistoryPeriod.THIRTY_DAYS);
//				ChatGUI.loadChatHistory(room, ChatHistoryPeriod.THREE_MONTHS);
//				ChatGUI.loadChatHistory(room, ChatHistoryPeriod.SIX_MONTHS);
//				ChatGUI.loadChatHistory(room, ChatHistoryPeriod.ONE_YEAR);
//				ChatGUI.loadChatHistory(room, ChatHistoryPeriod.FROM_BEGINNING);
			}
			else
			{
				if (ChatGUI.openedRoom == room) return;
				
				if (room.hidden)
				{
					ChatGUI.revealRoom(room);
				}
				
				ChatGUI.openedRoom = room;
				ChatGUI.refreshRooms();
			}
		},
		
		revealRoom : function(room)
		{
			if (room.hidden)
			{
				for (var i = 0; i < ChatGUI.rooms.length; i++)
				{
					if (ChatGUI.rooms[i] == room)
					{
						ChatGUI.rooms.splice(i, 1);
						break;
					}
				}
				
				ChatGUI.rooms.push(room);
				
				room.hidden = false;
			}
		},
		
		closeRoom : function(roomJid)
		{
			var room = ChatGUI.getRoomById(roomJid);
			
			if (room.type == MessageType.CHAT)
			{
				room.hidden = true;
			}
			else if (room.type == MessageType.GROUP_CHAT)
			{
				Chat.disconnectFromRoom(roomJid);
				
				for (var i = 0; i < ChatGUI.rooms.length; i++)
				{
					var chatRoom = ChatGUI.rooms[i];
					
					if (chatRoom.id == room.id)
					{
						ChatGUI.rooms.splice(i, 1);
						break;
					}
				}
			}
			
			ChatGUI.openedRoom = ChatGUI.getRoomById('dashboard');
			
			ChatGUI.refreshRooms();
		},
		
		updateChatTitle : function()
		{
			var jHeader = $('.chatRoot > .header > .header-title');
			
			if (ChatGUI.openedRoom == null)
			{
				jHeader.html('".Yii::t('general', 'Chat')."');
			}
			else
			{
				jHeader.html('".Yii::t('general', 'Chat')."' + ' (' + ChatGUI.openedRoom.fullName + ')');
			}
		},
		
		sendChatMessage : function()
		{
			var jInputMessage = $('#inputMessage');
			
			var msg = jInputMessage.val();
			
			if (msg != '')
			{
				msg = MethodsForStrings.escapeHtml(msg);
				msg = msg.replace(/\\n/g, '<br/>');
				
				var newMessage = new InternalChatMessage(
					ChatGUI.openedRoom.type,
					MethodsForDateTime.dateToString(new Date()),
					Chat.currentUser.bareJid,
					Chat.currentUser.fullName,
					msg);
				
				if (ChatGUI.openedRoom.type == MessageType.GROUP_CHAT)
				{
					newMessage.roomJid = ChatGUI.openedRoom.id;
				}
				
				ChatGUI.addChatMessage(newMessage);
				
				jInputMessage.val('');
				
				var recipientJid = ChatGUI.openedRoom.id;
				
				Chat.sendMessage(recipientJid, newMessage);
				
				ChatGUI.scrollOpenedMessagesToBottom();
			}
		},
		
		showVideoCallInvitationSentMessage : function()
		{
			var recipientJid = ChatGUI.openedRoom.id;
			var msg = 'Video Call invitation sent to ' + recipientJid;
			
			msg = MethodsForStrings.escapeHtml(msg);
			msg = msg.replace('\\n', '<br/>');
			
			var newMessage = new InternalChatMessage(
				MessageType.VIDEO_CALL,
				MethodsForDateTime.dateToString(new Date()),
				Chat.currentUser.bareJid,
				Chat.currentUser.fullName,
				msg);
			
			ChatGUI.addChatMessage(newMessage);
			
//			console.log('showVideoCallInvitationSentMessage');
			
//			Chat.sendMessage(recipientJid, newMessage);
		},
		
		addChatMessage : function(message)
		{
			var targetRoom = null;
			
			if (message.type == MessageType.CHAT || message.type == MessageType.VIDEO_CALL)
			{
				if (message.senderJid == Chat.currentUser.bareJid)
				{
					targetRoom = ChatGUI.openedRoom;
				}
				else
				{
					targetRoom = ChatGUI.getRoomById(message.senderJid);
					
					$.ionSound.play('sound_message');
					
					if (targetRoom != ChatGUI.openedRoom)
					{
						if (targetRoom == null)
						{
							targetRoom = new InternalChatRoom(message.senderJid, MessageType.CHAT, message.senderFullName, false, true);
							
							ChatGUI.rooms.push(targetRoom);
						}
						else
						{
							if (targetRoom.hidden) ChatGUI.revealRoom(targetRoom);
							targetRoom.unread = true;
						}
					}
				}
				
				targetRoom.messages.push(message);
			}
			else if (message.type == MessageType.GROUP_CHAT)
			{
				targetRoom = ChatGUI.getRoomById(message.roomJid);
				
				targetRoom.messages.push(message);
				
				var messageDateTime = MethodsForDateTime.stringToDate(message.time);
				
				if (messageDateTime > Chat.loginDateTime && message.senderJid != Chat.currentUser.bareJid)
				{
					$.ionSound.play('sound_message');
					
					if (targetRoom != ChatGUI.openedRoom)
					{
						targetRoom.unread = true;
					}
				}
			}
			
			ChatGUI.refreshRooms();
		},
		
		addVideoCallInvitationControls : function(senderJid)
		{
			targetRoom = ChatGUI.getRoomById(senderJid);
			
			if (!targetRoom)
			{
				targetRoom = ChatGUI.getRoomById(ChatGUI.openedRoom.id);
			}
			
			targetRoom.callinvite = true;
			
			if (ChatGUI.openedRoom == targetRoom)
			{
				$('#videoChatInviteButtons').show(400);
			}
			
			return true;
		},
		addDrawingCallInvitationControls : function(senderJid)
		{
//		    alert('addDrawingCallInvitationControls');
			targetRoom = ChatGUI.getRoomById(senderJid);

			if (!targetRoom)
			{
				targetRoom = ChatGUI.getRoomById(ChatGUI.openedRoom.id);
			}
            targetRoom.drawInvite = true;
            console.log(ChatGUI.openedRoom);
            console.log(targetRoom);
			if (ChatGUI.openedRoom.id == targetRoom.id)
			{
				$('#whiteboardInviteButtons').show(400);
			}

			return true;
		},
	};
	
", CClientScript::POS_HEAD);

Yii::app()->clientScript->registerScript(uniqid(), "
	
	$(document).tooltip(
	{
		items : '#users > .user',
		content: function() {
			var element = $(this);
			var user = ChatGUI.getUserByBareJid($(this).attr('bareJid'));
			
			var onlineStatusStr = (user.online ? '".Yii::t('general', 'Online')."' : '".Yii::t('general', 'Offline')."');
			
			// return '".Yii::t('general', 'Email').": ' + user.email + '<br/>' + '".Yii::t('general', 'Status').": ' + onlineStatusStr;
			
			return '".Yii::t('general', 'Bare JID').": ' + user.bareJid + '<br/>' +
				'".Yii::t('general', 'Full JID').": ' + user.fullJid + '<br/>' +
				'".Yii::t('general', 'Status').": ' + onlineStatusStr;
		}
	});
	
	$('#rooms').on('click', '> .room', function(e)
	{
		var wasOpened = ($(this).attr('opened') != null);
		
		$('#rooms > .room').removeAttr('opened');
		
		var room = ChatGUI.getRoomById($(this).attr('roomId'));
		
		if (room.id == 'dashboard')
		{
			$(this).attr('opened', '');
			ChatGUI.openedRoom = room;
		}
		else
		{
			if (!wasOpened)
			{
				$(this).attr('opened', '');
				ChatGUI.openedRoom = room;
			}
			else
			{
				ChatGUI.openedRoom = ChatGUI.getRoomById('dashboard');
			}
		}
		
		if (ChatGUI.openedRoom.callinvite == true)
		{
			$('#videoChatInviteButtons').show(400);
		}
		else
		{
			$('#videoChatInviteButtons').hide(0);
		}

		if (ChatGUI.openedRoom.drawInvite == true)
		{
			$('#whiteboardInviteButtons').show(400);
		}
		else
		{
			$('#whiteboardInviteButtons').hide(0);
		}
		
		ChatGUI.refreshRooms();
	});
	
	$('#users').on('click', '> .user', function(e)
	{
		var userBareJid = $(this).attr('bareJid');
		
		if (userBareJid == Chat.currentUser.bareJid) return;
		
		var user = ChatGUI.getUserByBareJid(userBareJid);
		
		ChatGUI.openPrivateChatRoom(user);
	});
	
	$('#btnSend').on('click', function(e)
	{
		ChatGUI.sendChatMessage();
	});
	
	$('#btnStartVideoCall').on('click', function(e)
	{
//		ChatGUI.showVideoCallInvitationSentMessage();
		
		Chat.startVideoCall();
	});

	$('#btnWhiteboard').on('click', function(e)
	{
		Chat.startWhiteboardDrawing();
	});
	
	$('#btnShowHistory').on('click', function(e)
	{
		$.post(
		'index.php?r=chat/getUserChatHistory',
		{userJid:Chat.currentUser.bareJid, openedRoom: ChatGUI.openedRoom.id},
		function (data) {
			$('#chat-history-dialog-container').html(data);
			$('#chat-history-dialog').dialog('open');
		},
		'html'
		);
		return false;
	});

	$('#btnWhiteboard').on('click', function(e)
	{
	    jQuery('#whiteboard-container .literally.localstorage').html('<canvas></canvas>');
        $('#whiteboard-container').show();
        var whiteboard = $('.literally.localstorage').literallycanvas({
          backgroundColor: 'whiteSmoke',
          imageURLPrefix: '".Yii::app()->theme->baseUrl."/assets/images/whiteboard',
          onInit: function(lc) {
            lc.on('drawingChange', function() {
                Chat.sendDrawingContent(lc.getSnapshotJSON());
//                console.log(lc.getSnapshotJSON());
	        });
	        lc.on('shapeSave', function(shape) {
//	        window.shape = shape;
//	        Chat.sendDrawingContent(JSON.stringify(shape));
//                console.log(shape);
//                console.log(lc.getSnapshotJSON());
	        });
          }
    });
		return false;
	});
	
	$('#btnAcceptVideoCall').on('click', function(e)
	{
		Chat.acceptVideoCall();

		ChatGUI.openedRoom.callinvite = false;
		$('#videoChatInviteButtons').hide(400);
	});
	$('#btnAcceptWhiteboard').on('click', function(e)
	{
	    jQuery('#whiteboard-container .literally.localstorage').html('<canvas></canvas>');
		$('#whiteboardInviteButtons').hide(400);
		$('#whiteboard-container').show();
          $('.literally.localstorage').literallycanvas({
          backgroundColor: 'whiteSmoke',
          imageURLPrefix: '".Yii::app()->theme->baseUrl."/assets/images/whiteboard',
          onInit: function(lc) {
            window.whiteboard = lc;
            lc.on('drawingChange', function() {
                return false;
                //console.log(lc.getSnapshotJSON());
	        }),
	        lc.on('drawStart',function(){
	            return false;
	        });
          }
    });
		return false;
	});
	
	$('#btnDeclineVideoCall').on('click', function(e)
	{
//		Chat.sendMessage(ChatGUI.openedRoom.id, '', MessageType.VIDEO_CALL_DECLINED);
		
//		Chat.sendMessage(ChatGUI.openedRoom.id, '".Yii::t('general','Video/audio call declined')."', 'chat');
		
		$('#videoChatInviteButtons').hide(400);
	});
	
	$('#inputMessage').keydown(function (e)
	{
//		// Send message on CTRL+ENTER.
//		if ((e.keyCode == 10 || e.keyCode == 13) && e.ctrlKey)
//		{
//			ChatGUI.sendChatMessage();
//		}
		
		// Send message on ENTER.
		if (e.keyCode == 10 || e.keyCode == 13)
		{
			if (e.ctrlKey)
			{
				$(this).val($(this).val() + '\\n');
			}
			else
			{
				e.preventDefault();
				
				ChatGUI.sendChatMessage();
			}
		}
	});
	
	$(window).resize(function(e)
	{
		ChatGUI.onWindowResize();
	});
	
	$('#rooms').on('mouseenter', '> .room .btnClose', function(e)
	{
		$(this).removeClass('ui-state-hover');
		$(this).addClass('ui-state-default');
	});
	
	$('#rooms').on('mouseleave', '> .room .btnClose', function(e)
	{
		$(this).removeClass('ui-state-default');
		$(this).addClass('ui-state-hover');
	});
	
	$('#rooms').on('click', '> .room .btnClose', function(e)
	{
		e.stopImmediatePropagation();
		
		var roomId = $(this).parents('.room').attr('roomId');
		
		ChatGUI.closeRoom(roomId);
	});

	$('#messages').on('click', '> .msgContainer .video-toggle', function(e)
	{
            $(this).parent().find('.video').slideToggle('fast','swing',function(){
                ChatGUI.resizeChatTextDiv();
            });
//            ChatGUI.resizeChatTextDiv();
            return true;
	});
	
	$('#staticRooms').on('click', '> .room', function(e)
	{
		var staticRoomName = $(this).attr('roomName');
		
		var staticRoom = ChatGUI.getStaticRoomByName(staticRoomName);
		
		var room = ChatGUI.getRoomById(staticRoom.jid);
		
		if (room == null)
		{
			Chat.connectToRoom(staticRoom.name);
		}
		else
		{
			if (ChatGUI.openedRoom == room) return;
			
//			if (room.hidden) // OLD code
//			{
//				ChatGUI.revealRoom(room);
//			}
			
			ChatGUI.openedRoom = room;
			ChatGUI.refreshRooms();
		}
		ChatGUI.resizeChatTextDiv();
	});
	
	$(window).on('beforeunload', function()
	{
		console.log('beforeunload');
		
		Chat.disconnect();
	});
	
	$('.chatRoot .header-title').on('click', function()
	{
		Chat.loadMessageCollections();
	});
	
	// Starting chat.
	
	ChatGUI.sendingDivSize = ChatGUI.getSendingDivSize();
	ChatGUI.onWindowResize(true);
	
	ChatGUI.updateChatTitle();
	ChatGUI.blockControls();
	
	ChatGUI.initialPageTitle = document.title;
	
	Chat.connect();
	
	// Sound initialization.
	
	$.ionSound({
		path : '".Yii::app()->theme->baseUrl."/assets/sounds/',
		sounds: [
			'sound_message',
			'button_push',
		]
	});

	
", CClientScript::POS_READY);