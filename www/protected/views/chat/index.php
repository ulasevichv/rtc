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
	<div class="sections">
		<div id="groups">
			Groups
		</div>
		<div id="users">
			Users
		</div>
		<div id="chat">
			<div id="messages"></div>
			<div id="sending">
				<div class="controls">
					<?php echo CHtml::textArea(null, '', array('id' => 'inputMessage')); ?>
					<?php echo CHtml::htmlButton(Yii::t('general', 'Send'), array('id' => 'btnSend', 'class' => 'btn btn-primary')); ?>
				</div>
			</div>
		</div>
	</div>
</div>

<?php
Yii::app()->clientScript->registerScript(uniqid(), "
	
	var groups = [
		{ id : 1, name : 'Marketing', numUsersOnline : 3 },
		{ id : 2, name : 'Sellers', numUsersOnline : 0 },
		{ id : 3, name : 'Teqniksoft', numUsersOnline: 1 },
		{ id : 4, name : 'Teqspring', numUsersOnline: 0 },
		{ id : 5, name : 'Sysadmins', numUsersOnline: 6 }
	];
	
	var messages = [
		{ time : '2014-04-15 16:25:02', from : 'Vladimir Putin', text : 'Hello' },
		{ time : '2014-04-15 16:27:14', from : 'Vladimir Putin', text : 'Hey, Victor, are you there?' },
		{ time : '2014-04-15 16:27:25', from : 'Victor Ulasevich', text : 'Yeah, how are you? How\'s the situation with Crimea?' },
		{ time : '2014-04-15 16:27:44', from : 'Vladimir Putin', text : 'All good, all good...' }
	];
	
	var openedGroup = null;
	var sendingDivSize = null;
	
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
	
	function getSendingDivSize()
	{
		var jSending = $('#sending');
		
		return { x : jSending.width(), y : jSending.height() };
	}
	
	function onWindowResize(initialCall)
	{
		initialCall = (typeof(initialCall) != 'undefined' ? initialCall : false);
		
		var newSendingDivSize = getSendingDivSize();
		
		if (newSendingDivSize.x != sendingDivSize.x || initialCall)
		{
			var jInputMessage = $('#inputMessage');
			var jBtnSend = $('#btnSend');
			
			jInputMessage.css('width', (newSendingDivSize.x - parseInt(jBtnSend.width()) - 35) + 'px');
		}
		
		sendingDivSize = newSendingDivSize;
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
	
	feed = [];
	
	messages.forEach(function(message, i)
	{
		var blockType = (message.from == 'Victor Ulasevich' ? 'outgoing' : 'incoming');
		
		feed.push('<div class=\"message ' + blockType + '\">');
		feed.push(	'<div class=\"from\">');
//		feed.push(		'<div class=\"txt\">');
		feed.push(			message.from);
//		feed.push(		'</div>');
		feed.push(	'</div>');
		feed.push(	'<div class=\"text\">');
		feed.push(		message.text);
		feed.push(	'</div>');
		feed.push(	'<div class=\"time\">');
		feed.push(		message.time);
		feed.push(	'</div>');
		feed.push('</div>');
	});
	
	$('#messages').html(feed.join(''));
	
	sendingDivSize = getSendingDivSize();
	onWindowResize(true);
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
	
	$(window).resize(function()
	{
		onWindowResize();
	});
	
", CClientScript::POS_READY);