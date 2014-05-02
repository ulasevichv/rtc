<?php
Yii::app()->clientScript->registerScript(uniqid(), "
	
	var StanzaSourceType = {
		DIRECT : 0,
		ROOM : 1
	};
	
	var MessageType = {
		CHAT : 'chat',
		GROUPCHAT : 'groupchat'
	};
	
	// Chat user.
	
	InternalChatUser.prototype = new Object();
	
	function InternalChatUser(fullJid, bareJid, nickname, fullName, online)
	{
		online = (typeof(online) == 'undefined' ? false : online);
		
		this.fullJid = fullJid;
		this.bareJid = bareJid;
		this.nickname = nickname;
		this.fullName = fullName;
		this.online = online;
	}
	
	// Chat message.
	
	InternalChatMessage.prototype = new Object();
	
	function InternalChatMessage(type, time, senderJid, senderFullName, text, roomJid)
	{
		roomJid = (typeof(roomJid) == 'undefined' ? null : roomJid);
		
		this.type = type;
		this.time = time;
		this.senderJid = senderJid;
		this.senderFullName = senderFullName;
		this.text = text;
		this.roomJid = roomJid;
	}
	
	// Chat room.
	
	InternalChatRoom.prototype = new Object();
	
	function InternalChatRoom(id, name, hidden, unread)
	{
		hidden = (typeof(hidden) == 'undefined' ? false : hidden);
		unread = (typeof(unread) == 'undefined' ? false : unread);
		
		this.id = id;
		this.name = name;
		this.hidden = hidden;
		this.unread = unread;
		this.messages = []; // InternalChatMessage
	}
	
", CClientScript::POS_HEAD);