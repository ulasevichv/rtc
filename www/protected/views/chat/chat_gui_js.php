<?php
Yii::app()->clientScript->registerScript(uniqid(), "
	
	var ChatGUI = {
		
		users : [],
		
		getUserByBareJid : function(bareJid)
		{
			for (var i = 0; i < ChatGUI.users.length; i++)
			{
				var user = ChatGUI.users[i];
				
				if (user.bareJid == bareJid) return user;
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
//			ChatGUI.users.sort(function(a,b){return a.fullName.localeCompare(b.fullName);});

//			for (var i = 0; i < ChatGUI.users.length; i++) { console.log(ChatGUI.users[i]); }
			
			ChatGUI.users.sort(function(a, b) {
				if (b.online && !a.online) return 1;
				else if (a.online && !b.online) return -1;
				
				return a.fullName.localeCompare(b.fullName);
			});
			
//			console.log('-----');
			
//			for (var i = 0; i < ChatGUI.users.length; i++) { console.log(ChatGUI.users[i]); }
			
			// Moving current user to top.
			
			var currentUser = null;
			
			for (var i = 0; i < ChatGUI.users.length; i++)
			{
				var user = ChatGUI.users[i];
				
				if (user.bareJid == Chat.currentUser.jid)
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
	
", CClientScript::POS_READY);