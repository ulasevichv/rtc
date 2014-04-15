<?php
$baseUrl = Yii::app()->theme->baseUrl;

Yii::app()->clientScript->registerCssFile($baseUrl.'/assets/css/chat.css');
Yii::app()->clientScript->registerCssFile('http://fonts.googleapis.com/css?family=Roboto:400,700');
//Yii::app()->clientScript->registerCssFile('http://fonts.googleapis.com/css?family=Roboto+Condensed:400,700');

//echo 'userId: '.Yii::app()->user->id;

?>

<div class="chatRoot">
	<div class="header">
	</div>
	<div id="groups">
		Groups
	</div>
	<div id="users">
		Users
	</div>
	<div id="messages">
		Messages
	</div>
</div>

<?php
Yii::app()->clientScript->registerScript(uniqid(), "
	
	var groups = [
		{ id : 1, name : 'Marketing', numUsersOnline : 3 },
		{ id : 2, name : 'Sellers', numUsersOnline : 0 },
		{ id : 3, name : 'Teqniksoft', numUsersOnline: 1 }
	];
	
	var openedGroup = null;
	
	function getGroupById(id)
	{
		for (var i = 0; i < groups.length; i++)
		{
			var group = groups[i];
			
			if (group.id == id) return group;
		}
		
		return null;
	}
	
	function updateChat()
	{
		var jHeader = $('.chatRoot > .header');
		
		if (openedGroup == null)
		{
			jHeader.html('".Yii::t('general', 'Chat')."');
		}
		else
		{
			jHeader.html('".Yii::t('general', 'Chat')."' + ' (' + openedGroup.name + ')');
		}
	}
	
", CClientScript::POS_HEAD);

Yii::app()->clientScript->registerScript(uniqid(), "
	
	var feed = [];
	
	groups.forEach(function(group, i)
	{
		feed.push('<div class=\"group\" id=\"' + group.id + '\">');
		feed.push(	'<div class=\"icon\"></div>');
		feed.push(	'<div class=\"text\">');
		feed.push(		group.name);
		feed.push(	'</div>');
		feed.push('</div>');
	});
	
	$('#groups').html(feed.join(''));
	
	updateChat();
	
	$('#groups').on('click', '> .group', function()
	{
		var wasOpened = ($(this).attr('opened') != null);
		
		$('#groups > .group').removeAttr('opened');
		
		var group = getGroupById(parseInt($(this).attr('id')));
		
		if (!wasOpened)
		{
			$(this).attr('opened', '');
			openedGroup = group;
		}
		else
		{
			openedGroup = null;
		}
		
		updateChat();
	});
	
", CClientScript::POS_READY);