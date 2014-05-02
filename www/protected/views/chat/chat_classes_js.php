<?php
Yii::app()->clientScript->registerScript(uniqid('chat_classes'), "
	
	// Chat message.
	
	InternalChatMessage.prototype = new Object();
	
	function InternalChatMessage(time, senderJid, senderFullName, text)
	{
		this.time = time;
		this.senderJid = senderJid;
		this.senderFullName = senderFullName;
		this.text = text;
	}
	
	// Chat room.
	
	InternalChatRoom.prototype = new Object();
	
	function InternalChatRoom(id, name, hidden, unread)
	{
		hidden = (typeof(hidden) == 'undefined' ? false : hidden);
		unread = (typeof(unread) == 'undefined' ? false : unread);
		
		this.id = id;
		this.callinvite = false;
		this.name = name;
		this.hidden = hidden;
		this.unread = unread;
		this.messages = []; // InternalChatMessage
	}
	
", CClientScript::POS_HEAD);