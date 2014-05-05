<?php
Yii::app()->clientScript->registerScript(uniqid(), "
	
	var StanzaSourceType = {
		DIRECT : 0,
		ROOM : 1
	};
	
	var MessageType = {
		CHAT : 'chat',
		GROUPCHAT : 'groupchat',
		VIDEOCALL : 'videoCall'
	};
	
	var PresenceType = {
		AVAILABLE : 'available',
		UNAVAILABLE : 'unavailable'
	};
	
	// Chat user.
	
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
	
	function InternalChatRoom(id, type, fullName, hidden, unread)
	{
		hidden = (typeof(hidden) == 'undefined' ? false : hidden);
		unread = (typeof(unread) == 'undefined' ? false : unread);
		
		this.id = id;
		this.type = type;
		this.callinvite = false;
		this.fullName = fullName;
		this.hidden = hidden;
		this.unread = unread;
		this.messages = []; // InternalChatMessage
		
		this.onlineUserNicknames = [];
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
		
		console.log(this.fullName + ' > changeParticipantOnlineStatus(' + nickname + ', ' + online + ')');
		console.log(this.onlineUserNicknames);
	}
	
	InternalChatRoom.prototype.isParticipantOnline = function(nickname)
	{
		return (this.onlineUserNicknames.indexOf(nickname) != -1);
	}
	
	// Static chat room.
	
	InternalStaticChatRoom.prototype = new Object();
	
	function InternalStaticChatRoom(jid, name, fullName)
	{
		this.jid = jid;
		this.name = name;
		this.fullName = fullName;
	}
	
", CClientScript::POS_HEAD);