<?php
$baseUrl = Yii::app()->theme->baseUrl;

Yii::app()->clientScript->registerScriptFile($baseUrl.'/assets/js/strophe.js');

$xmppAddress = Yii::app()->params->xmppServerIP;
$boshAddress = 'http://'.Yii::app()->params->xmppServerIP.'/http-bind';
?>

Registration

<?php

Yii::app()->clientScript->registerScript(uniqid('registrator'), "
	
	Registration.prototype = new Object();
	
	function Registration(xmppAddress, boshAddress, adminUsername, adminPassword)
	{
		this.conn = null;
		this.xmppAddress = xmppAddress;
		this.boshAddress = boshAddress;
		this.adminUser = {
			username : adminUsername,
			password : adminPassword,
			jid : adminUsername + '@' + xmppAddress
		};
		this.disconnecting = false;
	}
	
	Registration.prototype.connect = function()
	{
		this.conn  = new Strophe.Connection(this.boshAddress);
		
		console.log('Connecting \'' + this.adminUser.jid + '\' (' + this.adminUser.password + ')');
		
		var inst = this;
		
		this.conn.connect(this.adminUser.jid, this.adminUser.password, function(status) { inst.onConnectionStatusChange(status); });
	}
	
	Registration.prototype.disconnect = function()
	{
		this.disconnecting = true;
		
		this.conn.disconnect();
		this.conn = null;
	}
	
	Registration.prototype.onConnectionStatusChange = function(status)
	{
		switch (status)
		{
			case Strophe.Status.CONNECTED: this.onConnect(); break;
			case Strophe.Status.DISCONNECTED: this.onDisconnect(); break;
		}
	}
	
	Registration.prototype.onConnect = function()
	{
		console.log('Connected');
		
//		this.requestAddUserForm();
		this.addUserToGroup('regtest@192.237.219.76', 'groups/TeqSpring');
	}
	
	Registration.prototype.onDisconnect = function()
	{
		console.log('Disconnected');
		
		if (this.disconnecting) return;
		
		alert('".Yii::t('general', 'Unable to connect to server. Please, reload the page')."');
	}
	
	Registration.prototype.requestAddUserForm = function()
	{
		var iq = \$iq({ type : 'set' })
			.c('command', { xmlns : 'http://jabber.org/protocol/commands', node : 'http://jabber.org/protocol/admin#add-user', action : 'execute' });
		
		console.log(Strophe.serialize(iq));
		
		var inst = this;
		
		this.conn.sendIQ(iq, function(stanza) { inst.onAddUserFormReceived(stanza); });
	}
	
	Registration.prototype.onAddUserFormReceived = function(stanza)
	{
		console.log('onAddUserFormReceived');
		console.log(stanza);
		
		var jCommand = $(stanza).find('command[node=\"http://jabber.org/protocol/admin#add-user\"][status=\"executing\"]');
		var jForm = $(jCommand).find('x[xmlns=\"jabber:x:data\"][type=\"form\"]');
		
		if (jCommand.length == 1 && jForm.length == 1)
		{
			console.log('FORM RECEIVED');
			
			var sessionId = jCommand.attr('sessionid');
			
			this.sendAddUserForm(sessionId);
		}
	}
	
	Registration.prototype.sendAddUserForm = function(sessionId)
	{
		console.log('sendAddUserForm(' + sessionId + ')');
		
		var userJid = 'regtest3' + '@' + this.xmppAddress;
		var password = '123456';
		var email = 'regtest@nomail.com';
		var firstName = 'Testfn';
		var lastName = 'Testln';
		
		var iq = \$iq({ type : 'set' })
			.c('command', { xmlns : 'http://jabber.org/protocol/commands', node : 'http://jabber.org/protocol/admin#add-user', sessionid : sessionId })
			.c('x', { xmlns : 'jabber:x:data', type : 'submit' })
				.c('field', { type : 'hidden', var : 'FORM_TYPE' })
					.c('value').t('http://jabber.org/protocol/admin').up().up()
				.c('field', { var : 'accountjid' })
					.c('value').t(userJid).up().up()
				.c('field', { var : 'password' })
					.c('value').t(password).up().up()
				.c('field', { var : 'password-verify' })
					.c('value').t(password).up().up()
				.c('field', { var : 'email' })
					.c('value').t(email).up().up()
				.c('field', { var : 'given_name' })
					.c('value').t(firstName + ' ' + lastName).up().up()
				.c('field', { var : 'surname' })
					.c('value').t('').up().up();
		
		console.log(Strophe.serialize(iq));
		
		var inst = this;
		
		this.conn.sendIQ(iq, function(stanza) { inst.onUserFormSubmitResponse(stanza, userJid); });
	}
	
	Registration.prototype.onUserFormSubmitResponse = function(stanza, userJid)
	{
		console.log('onUserFormSubmitResponse');
		console.log(stanza);
		
		var jCommand = $(stanza).find('command');
		var commandStatus = jCommand.attr('status')
		
		if (jCommand.length == 1 && commandStatus == 'completed')
		{
			var jNote = jCommand.find('note');
			var resultType = jNote.attr('type');
			var resultText = jNote.text();
			
			console.log('resultType: ' + resultType);
			console.log('resultText: ' + resultText);
			
			if (resultType == 'info' && resultText == 'Operation finished successfully')
			{
				console.log('SUCCESS');
				
				this.addUserToGroup(userJid, 'TeqSpring');
			}
		}
	}
	
	Registration.prototype.addUserToGroup = function(userJid, groupName)
	{
		console.log('addUserToGroup(' + userJid + ', ' + groupName + ')');
		
		var iq = \$iq({ type : 'set', to : 'pubsub.' + this.xmppAddress })
			.c('pubsub', { xmlns : 'http://jabber.org/protocol/pubsub' })
				.c('entities', { node : groupName })
					.c('entity', { jid : userJid, affiliation : 'none', subscription : 'subscribed' });
		
		console.log(Strophe.serialize(iq));
		
		this.conn.sendIQ(iq, function(stanza) { inst.onAddUserToGroupResponse(stanza); });
	}
	
	Registration.prototype.onAddUserToGroupResponse = function(stanza)
	{
		console.log('onAddUserToGroupResponse');
	}
	
", CClientScript::POS_HEAD);

Yii::app()->clientScript->registerScript(uniqid(), "
	
	var registration = new Registration('".$xmppAddress."', '".$boshAddress."', 'admin', 'zxasqw12');
	
	registration.connect();
	
", CClientScript::POS_READY);