<?php
Yii::app()->clientScript->registerScript(uniqid(), "
	
	var ChatGUI = {
		
		users : [],
		
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
			
			ChatGUI.users.sort(function(a,b){return a.fullName.localeCompare(b.fullName);});
			
			ChatGUI.refreshUsers();
		},
		
		refreshUsers : function()
		{
			var feed = [];
			
			ChatGUI.users.forEach(function(user, i)
			{
				var onlineStatusClass = (user.online ? 'online' : 'offline');
				
				feed.push('<div class=\"user ' + onlineStatusClass + '\" userId=\"' + user.id + '\">');
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