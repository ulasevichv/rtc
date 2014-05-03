<?php
Yii::app()->clientScript->registerScript(uniqid(), "
	
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
		},
		
		updateUsersVisibility : function()
		{
			var jSending = $('#sending');
			
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
				else if (ChatGUI.openedRoom.type == MessageType.GROUPCHAT)
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
				
				if (room.unread && room == ChatGUI.openedRoom) room.unread = false;
				
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
				jMessages.html('');
			}
			else
			{
				var feed = [];
				
				for (var i = 0; i < ChatGUI.openedRoom.messages.length; i++)
				{
					var message = ChatGUI.openedRoom.messages[i];
					
//					console.log ('###: ' + message.senderJid + ' [' + Chat.currentUser.bareJid + ']');
					
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
				
				jMessages.html(feed.join(''));
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
		
		closeRoom : function(roomId)
		{
			var room = ChatGUI.getRoomById(roomId);
			
			room.hidden = true;
			
			ChatGUI.openedRoom = ChatGUI.getRoomById('dashboard');
			
			ChatGUI.refreshRooms();
		},
		
		updateChatTitle : function()
		{
			var jHeader = $('.chatRoot > .header');
			
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
				
				if (ChatGUI.openedRoom.type == MessageType.GROUPCHAT)
				{
					newMessage.roomJid = ChatGUI.openedRoom.id;
				}
				
				ChatGUI.addChatMessage(newMessage);
				
				jInputMessage.val('');
				
				var recipientJid = ChatGUI.openedRoom.id;
				
				Chat.sendMessage(recipientJid, newMessage);
			}
		},
		
		addChatMessage : function(message)
		{
			var targetRoom = null;
			
			if (message.type == MessageType.CHAT)
			{
				if (message.senderJid == Chat.currentUser.bareJid)
				{
					targetRoom = ChatGUI.openedRoom;
				}
				else
				{
					targetRoom = ChatGUI.getRoomById(message.senderJid);
					
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
				
				ChatGUI.refreshRooms();
			}
			else if (message.type == MessageType.GROUPCHAT)
			{
				targetRoom = ChatGUI.getRoomById(message.roomJid);
				
				targetRoom.messages.push(message);
				
				ChatGUI.refreshRooms();
			}
		}
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
		
		ChatGUI.refreshRooms();
	});
	
	$('#users').on('dblclick', '> .user', function(e)
	{
		var userBareJid = $(this).attr('bareJid');
		
		if (userBareJid == Chat.currentUser.bareJid) return;
		
		var user = ChatGUI.getUserByBareJid(userBareJid);
		
		var roomId = userBareJid;
		
		var room = ChatGUI.getRoomById(roomId);
		
		if (room == null)
		{
			room = new InternalChatRoom(roomId, MessageType.CHAT, user.fullName);
			ChatGUI.rooms.push(room);
			
			ChatGUI.openedRoom = room;
			ChatGUI.refreshRooms();
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
	});
	
	$('#btnSend').on('click', function(e)
	{
		ChatGUI.sendChatMessage();
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
	
	$('#staticRooms').on('dblclick', '> .room', function(e)
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
			
			if (room.hidden)
			{
				ChatGUI.revealRoom(room);
			}
			
			ChatGUI.openedRoom = room;
			ChatGUI.refreshRooms();
		}
	});
	
	// Starting chat.
	
	ChatGUI.sendingDivSize = ChatGUI.getSendingDivSize();
	ChatGUI.onWindowResize(true);
	
	ChatGUI.updateChatTitle();
	ChatGUI.blockControls();
	
	Chat.connect();
	
", CClientScript::POS_READY);