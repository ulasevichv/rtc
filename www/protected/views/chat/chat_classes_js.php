<?php
Yii::app()->clientScript->registerScript(uniqid('chat_classes'), "
	
	var StanzaSourceType = {
		DIRECT : 0,
		ROOM : 1
	};
	
	var MessageType = {
		CHAT : 'chat',
		GROUP_CHAT : 'groupchat',
		VIDEO_CALL : 'videoCall',
		VIDEO_CALL_ACCEPTED : 'videoCallAccepted',
		VIDEO_CALL_DECLINED : 'videoCallDeclined',
		SYSTEM : 'system',
		DRAWING_CALL : 'drawingCall',
		DRAWING_CONTENT : 'drawingContent',
		SCREEN_SHARING_INVITE : 'screenSharingInvite',
		SCREEN_SHARING_INVITE_ACCEPTED : 'screenSharingInviteAccepted',
		SCREEN_SHARING_OFFER : 'screenSharingOffer',
		SCREEN_SHARING_ANSWER : 'screenSharingAnswer',
		SCREEN_SHARING_ESTABLISHED : 'screenSharingEstablished'
	};
	
	var PresenceType = {
		AVAILABLE : 'available',
		UNAVAILABLE : 'unavailable'
	};
	
	var ChatHistoryPeriod = {
		YESTERDAY : 'yesterday',
		SEVEN_DAYS : 'seven days',
		THIRTY_DAYS : 'thirty days',
		THREE_MONTHS : 'three months',
		SIX_MONTHS : 'six months',
		ONE_YEAR : 'one year',
		FROM_BEGINNING : 'from beginning',
		
		getPeriodIndex : function(period)
		{
			switch (period)
			{
				case ChatHistoryPeriod.YESTERDAY: return 0;
				case ChatHistoryPeriod.SEVEN_DAYS: return 1;
				case ChatHistoryPeriod.THIRTY_DAYS: return 2;
				case ChatHistoryPeriod.THREE_MONTHS: return 3;
				case ChatHistoryPeriod.SIX_MONTHS: return 4;
				case ChatHistoryPeriod.ONE_YEAR: return 5;
				case ChatHistoryPeriod.FROM_BEGINNING: return 6;
			}
			
			return -1;
		},
		
		getPeriodByIndex : function(index)
		{
			switch (index)
			{
				case 0: return ChatHistoryPeriod.YESTERDAY;
				case 1: return ChatHistoryPeriod.SEVEN_DAYS;
				case 2: return ChatHistoryPeriod.THIRTY_DAYS;
				case 3: return ChatHistoryPeriod.THREE_MONTHS;
				case 4: return ChatHistoryPeriod.SIX_MONTHS;
				case 5: return ChatHistoryPeriod.ONE_YEAR;
				case 6: return ChatHistoryPeriod.FROM_BEGINNING;
			}
			
			return null;
		},
		
		getPeriodStartDate : function(originalDate, period)
		{
			var dayBeginningDateTime = MethodsForDateTime.getDayBeginningDateTime(originalDate);
			
			var newDateTime = new Date(dayBeginningDateTime.getTime());
			
			switch (period)
			{
				case ChatHistoryPeriod.YESTERDAY:
				{
					newDateTime.setHours(-24);
					return newDateTime;
				}
				case ChatHistoryPeriod.SEVEN_DAYS:
				{
					newDateTime.setHours(-24 * 7);
					return newDateTime;
				}
				case ChatHistoryPeriod.THIRTY_DAYS:
				{
					newDateTime.setHours(-24 * 30);
					return newDateTime;
				}
				case ChatHistoryPeriod.THREE_MONTHS:
				{
					newDateTime.setMonth(newDateTime.getMonth() - 3);
					return newDateTime;
				}
				case ChatHistoryPeriod.SIX_MONTHS:
				{
					newDateTime.setMonth(newDateTime.getMonth() - 6);
					return newDateTime;
				}
				case ChatHistoryPeriod.ONE_YEAR:
				{
					newDateTime.setFullYear(newDateTime.getFullYear() - 1);
					return newDateTime;
				}
				case ChatHistoryPeriod.FROM_BEGINNING:
				{
					return originalDate;
				}
			}
			
			return null;
		}
	};
	
	//==================================================
	// Chat user.
	//==================================================
	
	InternalChatUser.prototype = new Object();
	
	function InternalChatUser(fullJid, bareJid, nickname, fullName, password, online)
	{
		password = (typeof(password) == 'undefined' ? '' : password);
		online = (typeof(online) == 'undefined' ? false : online);
		
		this.fullJid = fullJid;
		this.bareJid = bareJid;
		this.nickname = nickname;
		this.fullName = fullName;
		this.password = password;
		this.online = online;
		this.statusId = null;
		this.statusText = null;

		this.opentokIniObjects = [];
	}
	
	InternalChatUser.prototype.addOpentokIniObject = function(roomJid, obj)
	{
		var iniObject = this.getOpentokIniObject(roomJid);
		
		if (iniObject == null)
		{
			this.opentokIniObjects.push({
				roomJid : roomJid,
				obj: obj });
		}
		else
		{
			iniObject.obj = obj;
		}
	}
	
	InternalChatUser.prototype.getOpentokIniObject = function(roomJid)
	{
		for (var i = 0; i < this.opentokIniObjects.length; i++)
		{
			var opentokIniObject = this.opentokIniObjects[i];
			
			if (opentokIniObject.roomJid == roomJid) return opentokIniObject;
		}
		
		return null;
	}
	
	//==================================================
	// Chat message.
	//==================================================
	
	InternalChatMessage.prototype = new Object();
	
	function InternalChatMessage(type, dateTime, time, senderJid, senderFullName, text, roomJid)
	{
		roomJid = (typeof(roomJid) == 'undefined' ? null : roomJid);
		
		this.type = type;
		this.dateTime = dateTime;
		this.time = time;
		this.senderJid = senderJid;
		this.senderFullName = senderFullName;
		this.text = text;
		this.roomJid = roomJid;
	}
	
	//==================================================
	// Chat room.
	//==================================================
	
	InternalChatRoom.prototype = new Object();
	
	function InternalChatRoom(id, type, fullName, hidden, unread)
	{
		hidden = (typeof(hidden) == 'undefined' ? false : hidden);
		unread = (typeof(unread) == 'undefined' ? false : unread);
		
		this.id = id;
		this.type = type; // MessageType
		this.callinvite = false;
		this.drawInvite = false;
		this.fullName = fullName;
		this.screenSharing = false;
		this.screenSharingInviteFrom = false;
		this.hidden = hidden;
		this.unread = unread;
		this.messages = []; // InternalChatMessage
		this.onlineUserNicknames = [];
		
		this.historyLoading = false;
		this.currentHistoryPeriod = null;
		this.historyConversations = []; // ChatRoomHistoryConversation
		this.historyMessages = []; // ChatRoomMessage
	}
	
	InternalChatRoom.prototype.changeParticipantOnlineStatus = function(nickname, online)
	{
		var index = this.onlineUserNicknames.indexOf(nickname);
		
		if (online)
		{
			if (index == -1) this.onlineUserNicknames.push(nickname);
		}
		else
		{
			if (index != -1) this.onlineUserNicknames.splice(index, 1);
		}
		
//		console.log(this.fullName + ' > changeParticipantOnlineStatus(' + nickname + ', ' + online + ')');
//		console.log(this.onlineUserNicknames);
	}
	
	InternalChatRoom.prototype.isParticipantOnline = function(nickname)
	{
		return (this.onlineUserNicknames.indexOf(nickname) != -1);
	}
	
	//==================================================
	// Static chat room.
	//==================================================
	
	InternalStaticChatRoom.prototype = new Object();
	
	function InternalStaticChatRoom(jid, name, fullName)
	{
		this.jid = jid;
		this.name = name;
		this.fullName = fullName;
	}
	
	//==================================================
	// Chat room history conversation.
	//==================================================
	
	ChatRoomHistoryConversation.prototype = new Object();
	
	function ChatRoomHistoryConversation(with_, start, loaded)
	{
		loaded = (typeof(loaded) == 'undefined' ? false : loaded);
		
		this.with = with_;
		this.start = start;
		this.loaded = loaded;
	}
	
	//==================================================
	// Chat room history message.
	//==================================================
	
	ChatRoomHistoryMessage.prototype = new Object();
	
	function ChatRoomHistoryMessage(from, dateTime, text)
	{
		this.from = from;
		this.dateTime = dateTime;
		this.text = text;
	}
	
", CClientScript::POS_HEAD);