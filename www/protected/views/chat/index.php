<?php
$baseUrl = Yii::app()->theme->baseUrl;

Yii::app()->clientScript->registerCssFile($baseUrl.'/assets/css/chat.css');

Yii::app()->clientScript->registerCssFile('http://fonts.googleapis.com/css?family=Roboto:400&subset=latin,cyrillic');
//Yii::app()->clientScript->registerCssFile('http://fonts.googleapis.com/css?family=Roboto:400,700');
//Yii::app()->clientScript->registerCssFile('http://fonts.googleapis.com/css?family=Roboto+Condensed:400,700');
//Yii::app()->clientScript->registerCssFile('http://fonts.googleapis.com/css?family=Inconsolata:400');

Yii::app()->clientScript->registerScriptFile($baseUrl.'/assets/js/MethodsForStrings.js');

Yii::app()->clientScript->registerScriptFile($baseUrl.'/assets/js/strophe.js');
//Yii::app()->clientScript->registerScriptFile($baseUrl.'/assets/js/strophe.chatstates.js');
Yii::app()->clientScript->registerScriptFile($baseUrl.'/assets/js/strophe.muc.js');


//Yii::app()->clientScript->registerScriptFile($baseUrl.'/assets/js/flXHR.js');
//Yii::app()->clientScript->registerScriptFile($baseUrl.'/assets/js/strophe.flxhr.js');

$xmppAddress = Yii::app()->params->xmppServerIP;
$boshAddress = 'http://'.Yii::app()->params->xmppServerIP.'/http-bind';

$xmppUser = null;

foreach (Yii::app()->params->xmppUsers as $user)
{
	if ($user->email == Yii::app()->user->email)
	{
		$xmppUser = $user;
		break;
	}
}

if (!isset($xmppUser))
{
	Yii::app()->user->setFlash('error', Yii::t('general', 'XMPP user is not found. Chatting is not possible.'));
	return;
}
?>

<div class="chatRoot">
	<div class="header">
	</div>
	<div class="sections">
		<div id="groups">
		</div>
		<div id="users">
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
	
	var allGroups = [
//		{ id : 1, name : 'Marketing', numUsersOnline : 3 },
//		{ id : 2, name : 'Sellers', numUsersOnline : 0 },
//		{ id : 3, name : 'Teqniksoft', numUsersOnline: 1 },
//		{ id : 4, name : 'Teqspring', numUsersOnline: 0 },
//		{ id : 5, name : 'Sysadmins', numUsersOnline: 6 }
	];
	
	var allMessages = [
//		{ time : '2014-04-15 16:25:02', from : 'Vladimir Putin', text : 'Hello' },
//		{ time : '2014-04-15 16:27:14', from : 'Vladimir Putin', text : 'Hey, Victor, are you there?' },
//		{ time : '2014-04-15 16:27:25', from : 'Victor Ulasevich', text : 'Yeah, how are you?<br/>How\'s the situation with Crimea?' },
//		{ time : '2014-04-15 16:27:44', from : 'Vladimir Putin', text : 'All good, all good...' }
	];
	
	var allUsers = [
		{ id : 1, email : 'ulasevich@tut.by', fullName : 'Victor Ulasevich', online : true },
		{ id : 2, email : 'putin@kremlin.ru', fullName : 'Vladimir Putin', online: false },
		{ id : 3, email : 'test@nomail.ru', fullName : 'Test User', online: true }
	];
	
	var openedGroup = null;
	var chatSize = null;
	var sendingDivSize = null;
	
	var xmppConnection = null;
	var xmppAddress = '".$xmppAddress."';
	var boshAddress = '".$boshAddress."';
	var currentUserJid = '".$xmppUser->serverUserName."' + '@' + xmppAddress;
	var currentUserPass = '".$xmppUser->serverUserPass."';
	var persistentRoomName = 'room01';
	
	function getGroupById(id)
	{
		for (var i = 0; i < allGroups.length; i++)
		{
			var group = allGroups[i];
			
			if (group.id == id) return group;
		}
		
		return null;
	}
	
	function getUserById(id)
	{
		for (var i = 0; i < allUsers.length; i++)
		{
			var user = allUsers[i];
			
			if (user.id == id) return user;
		}
		
		return null;
	}
	
	function getChatSize()
	{
		var jChat = $('#chat');
		
		return { x : jChat.width(), y : jChat.height() };
	}
	
	function getSendingDivSize()
	{
		var jSending = $('#sending');
		
		return { x : jSending.width(), y : jSending.height() };
	}
	
	function onWindowResize(initialCall)
	{
		initialCall = (typeof(initialCall) != 'undefined' ? initialCall : false);
		
		var newChatSize = getChatSize();
		var newSendingDivSize = getSendingDivSize();
		
		if (initialCall || newSendingDivSize.x != sendingDivSize.x)
		{
			var jInputMessage = $('#inputMessage');
			var jBtnSend = $('#btnSend');
			
			jInputMessage.css('width', (newSendingDivSize.x - parseInt(jBtnSend.width()) - 35) + 'px');
		}
		
		if (initialCall || newChatSize.y != chatSize.y)
		{
			var jMessages = $('#messages');
			var jSending = $('#sending');
			
			jMessages.css('height', (newChatSize.y - parseInt(jSending.height()) - 13) + 'px');
		}
		
		chatSize = newChatSize;
		sendingDivSize = newSendingDivSize;
	}
	
	function blockControls()
	{
		changeControlsAvailability(false);
	}
	
	function unblockControls()
	{
		changeControlsAvailability(true);
	}
	
	function changeControlsAvailability(value)
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
	}
	
	function refreshGroups()
	{
		var feed = [];
		
		allGroups.forEach(function(group, i)
		{
			feed.push('<div class=\"group\" id=\"' + group.id + '\">');
			feed.push(	'<div class=\"icon\"></div>');
			feed.push(	'<div class=\"text\">');
			feed.push(		group.name);
			feed.push(	'</div>');
			feed.push('</div>');
		});
		
		$('#groups').html(feed.join(''));
	}
	
	function updateChatTitle()
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
	
	function addChatMessages(messages)
	{
		var feed = [];
		
		messages.forEach(function(message, i)
		{
			var blockType = (message.from == 'Victor Ulasevich' ? 'outgoing' : 'incoming');
			
			feed.push('<div class=\"message ' + blockType + '\">');
			feed.push(	'<div class=\"from\">');
			feed.push(		message.from);
			feed.push(	'</div>');
			feed.push(	'<div class=\"text\">');
			feed.push(		message.text);
			feed.push(	'</div>');
			feed.push(	'<div class=\"time\">');
			feed.push(		message.time);
			feed.push(	'</div>');
			feed.push('</div>');
		});
		
		var jMessages = $('#messages');
		
		jMessages.html(jMessages.html() + feed.join(''));
	}
	
	function sendChatMessage()
	{
		var jInputMessage = $('#inputMessage');
		
		var msg = jInputMessage.val();
		
		if (msg != '')
		{
			var currentDateTime = new Date();
			
			var dateTimeStr = dateToString(currentDateTime);
			
			msg = MethodsForStrings.escapeHtml(msg);
			msg = msg.replace('\\n', '<br/>');
			
			var newMessage = {
				time : dateTimeStr,
				from : 'Victor Ulasevich',
				text : msg
			};
			
			addChatMessages([newMessage]);
			
			jInputMessage.val('');
			
			sendXmppMessage('daniel' + '@' + xmppAddress, msg);
		}
	}
	
	function refreshUsers()
	{
		var feed = [];
		
		allUsers.forEach(function(user, i)
		{
			var onlineStatus = (user.online ? 'online' : 'offline');
			
			feed.push('<div class=\"user ' + onlineStatus + '\" userId=\"' + user.id + '\">');
			feed.push(	'<div class=\"icon\"></div>');
			feed.push(	'<div class=\"text\">');
			feed.push(		user.fullName);
			feed.push(	'</div>');
			feed.push('</div>');
		});
		
		$('#users').html(feed.join(''));
	}
	
	function dateToString(date, showTimestamp)
	{
		showTimestamp = (typeof showTimestamp !== 'undefined' ? showTimestamp : false);
		
		if (typeof date === 'number')
		{
			date = new Date(date);
		}
		
		var result = date.getFullYear() + '-' +
			intToPadString(1 + date.getMonth(), 2) + '-' +
			intToPadString(date.getDate(), 2) + ' ' +
			intToPadString(date.getHours(), 2) + ':' +
			intToPadString(date.getMinutes(), 2) + ':' +
			intToPadString(date.getSeconds(), 2);// + '.' +
			//intToPadString(date.getMilliseconds(), 3);
		
		if (showTimestamp) result += ' (' + Math.floor(date.getTime() / 1000) + '.' + date.getMilliseconds() + ')';
		
		return result;
	}
	
	function intToPadString(intValue, numDigitsRequired)
	{
		var strValue = intValue.toString();
		
		var numMissingDigits = numDigitsRequired - strValue.length;
		
		if (numMissingDigits <= 0) return strValue;
		
		var str = '';
		
		for (var i = 0; i < numMissingDigits; i++)
		{
			str += '0';
		}
		
		return str + strValue;
	}
	
	
	
	
	
	
	
	
	
	function connectToXmppServer()
	{
		xmppConnection  = new Strophe.Connection(boshAddress);
		
		console.log('Connecting \'' + currentUserJid + '\' (' + currentUserPass + ')');
		
		xmppConnection.connect(currentUserJid, currentUserPass, function(status)
		{
			if (status === Strophe.Status.CONNECTED)
			{
				console.log('connected');
				
				xmppConnection.muc.init(xmppConnection);
				
				xmppConnection.addHandler(handlePong, null, 'iq', null, 'ping1');
				xmppConnection.addHandler(onMessage, null, 'message', null, null, null);
				xmppConnection.send(\$pres().tree());
				
				sendPing();
				
//				refreshUsers();
			}
			else if (status === Strophe.Status.DISCONNECTED)
			{
				alert('".Yii::t('general', 'Unable to connect to server. Please, reload the page')."');
			}
		});
	}
	
	function sendPing()
	{
		var domain = Strophe.getDomainFromJid(xmppConnection.jid);
		
		var ping = \$iq({
			to: domain,
			type: 'get',
			id: 'ping1'}).c('ping', {xmlns: 'urn:xmpp:ping'});
		
		console.log('Sending ping to ' + domain + '.');
		
		xmppConnection.send(ping);
	}
	
	function handlePong(iq)
	{
		console.log('Received pong from server in ' + '---' + 'ms.');
		
		unblockControls();
		

		
//		console.log('rooms:');
//		console.log(xmppConnection.muc.listRooms());
		
		xmppConnection.muc.join(persistentRoomName + '@conference' + xmppAddress, Strophe.getNodeFromJid(currentUserJid), function() {}, function() {}, function() {});
		
		var d = \$pres({
			'from': currentUserJid,
			'to': persistentRoomName + '@conference' + xmppAddress + '/' + Strophe.getNodeFromJid(currentUserJid)
			})
			.c('x', {'xmlns': 'http://jabber.org/protocol/muc'});
		
		xmppConnection.send(d.tree());
		
		return false;
	}
	
	function sendXmppMessage(jid, text)
	{
		console.log('sendXmppMessage(\'' + jid + '\', \'' + text + '\')');
		
		var msg = \$msg({
			to: jid,
			type: 'chat'
			})
			.cnode(Strophe.xmlElement('body', text)).up()
			.c('active', {xmlns: 'http://jabber.org/protocol/chatstates'});
		
		xmppConnection.send(msg);
		
		console.log('sent');
	}
	
	function onMessage(msg)
	{
		console.log(msg);
		
		var to = msg.getAttribute('to');
		var from = msg.getAttribute('from');
		var type = msg.getAttribute('type');
		var elems = msg.getElementsByTagName('body');
		
		if (type == 'chat' && elems.length > 0)
		{
			var body = elems[0];
			
			console.log('Received message from \'' + from + '\': \'' + Strophe.getText(body) + '\'');
		}
		
		return true;
	}
	
", CClientScript::POS_HEAD);

Yii::app()->clientScript->registerScript(uniqid(), "
	
	sendingDivSize = getSendingDivSize();
	onWindowResize(true);
	refreshGroups();
	updateChatTitle();
	addChatMessages(allMessages);
//	refreshUsers();
	
	blockControls();
	connectToXmppServer();
	
	$(document).tooltip(
	{
		items : '#users > .user',
		content: function() {
			var element = $(this);
			var user = getUserById(parseInt($(this).attr('userId')));
			
			var onlineStatusStr = (user.online ? '".Yii::t('general', 'Online')."' : '".Yii::t('general', 'Offline')."');
			
			return '".Yii::t('general', 'Email').": ' + user.email + '<br/>' + '".Yii::t('general', 'Status').": ' + onlineStatusStr;
		}
	});
	
	$(window).resize(function()
	{
		onWindowResize();
	});
	
	$('#groups').on('click', '> .group', function(e)
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
		
		updateChatTitle();
	});
	
	$('#btnSend').on('click', function(e)
	{
		sendChatMessage();
	});
	
	$('#inputMessage').keydown(function (e)
	{
		if ((e.keyCode == 10 || e.keyCode == 13) && e.ctrlKey)
		{
			sendChatMessage();
		}
	});
	
", CClientScript::POS_READY);