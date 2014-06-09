<?php
Yii::app()->clientScript->registerScript(uniqid('register_xmpp'), "
	
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
		this.newUser = null;
		this.connectCallback = null;
		this.registerUserCallback = null;
		this.disconnecting = false;
	}
	
	Registration.prototype.setNewUserData = function(firstName, lastName, email)
	{
		this.newUser = {
			firstName : firstName,
			lastName : lastName,
			email :email
		};
	}
	
	Registration.prototype.setRegisterUserCallback = function(callback)
	{
		this.registerUserCallback = callback;
	}
	
	Registration.prototype.connect = function(callback)
	{
		this.conn  = new Strophe.Connection(this.boshAddress);
		
		console.log('Connecting \'' + this.adminUser.jid + '\' (' + this.adminUser.password + ')');
		
		this.connectCallback = callback;
		
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
//		this.addUserToGroup();
		
		this.connectCallback();
	}
	
	Registration.prototype.onDisconnect = function()
	{
		console.log('Disconnected');
		
		if (this.disconnecting) return;
		
		alert('".Yii::t('general', 'Unable to connect to server. Please, reload the page')."');
	}
	
	// This approach described in XEP-0133 (http://xmpp.org/extensions/xep-0133.html#add-user)
	//
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
		
		var firstName = this.newUser.firstName;
		var lastName = this.newUser.lastName;
		var email = this.newUser.email;
		
		var xmppUserName = firstName.toLowerCase() + '_' + lastName.toLowerCase();
		var userJid = xmppUserName + '@' + this.xmppAddress;
		
		var xmppUserPassword = MethodsForStrings.generateRandomString(8, 'lower');
		
		var iq = \$iq({ type : 'set' })
			.c('command', { xmlns : 'http://jabber.org/protocol/commands', node : 'http://jabber.org/protocol/admin#add-user', sessionid : sessionId })
			.c('x', { xmlns : 'jabber:x:data', type : 'submit' })
				.c('field', { type : 'hidden', var : 'FORM_TYPE' })
					.c('value').t('http://jabber.org/protocol/admin').up().up()
				.c('field', { var : 'accountjid' })
					.c('value').t(userJid).up().up()
				.c('field', { var : 'password' })
					.c('value').t(xmppUserPassword).up().up()
				.c('field', { var : 'password-verify' })
					.c('value').t(xmppUserPassword).up().up()
				.c('field', { var : 'email' })
					.c('value').t(email).up().up()
				.c('field', { var : 'given_name' })
					.c('value').t(firstName + ' ' + lastName).up().up()
				.c('field', { var : 'surname' })
					.c('value').t('').up().up();
		
		console.log(Strophe.serialize(iq));
		
		var inst = this;
		
		this.conn.sendIQ(iq, function(stanza) { inst.onUserFormSubmitResponse(stanza, userJid, xmppUserName, xmppUserPassword); });
	}
	
	Registration.prototype.onUserFormSubmitResponse = function(stanza, userJid, xmppUserName, xmppUserPassword)
	{
		console.log('onUserFormSubmitResponse');
		console.log(stanza);
		
		var result = null;
		
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
				result = { type : 'success', text : resultText };
			}
			else
			{
				result = { type : 'error', text : resultText };
			}
		}
		else
		{
			result = { type : 'error', text : 'Wrong response' };
		}
		
		result.xmppUserName = xmppUserName;
		result.xmppUserPassword = xmppUserPassword;
		
		this.registerUserCallback(result);
	}
	
	// Not working with OpenFire!
	
	Registration.prototype.addUserToGroup = function(userJid, groupName)
	{
		userJid = 'testerr_testerr@192.237.219.76';
		groupName = 'TeqSpring';
		
		console.log('addUserToGroup(' + userJid + ', ' + groupName + ')');
		
		var iq = \$iq({ type : 'set', to : 'pubsub.' + this.xmppAddress })
			.c('pubsub', { xmlns : 'http://jabber.org/protocol/pubsub' })
				.c('entities', { node : groupName })
					.c('entity', { jid : userJid, affiliation : 'none', subscription : 'subscribed' });
		
		console.log(Strophe.serialize(iq));
		
		this.conn.sendIQ(iq, function(stanza) { inst.onAddUserToGroupResponse(stanza); });
		
//		var msg = \$msg({})
//			.c('x', { xmlns : 'http://jabber.org/protocol/rosterx'})
//				.c('item', { action : 'add', jid : userJid, name : 'testerr_testerr' })
//					.c('group').t(groupName);
//		
//		console.log(Strophe.serialize(msg));
//		
//		this.conn.sendIQ(msg);
	}
	
	Registration.prototype.onAddUserToGroupResponse = function(stanza)
	{
		console.log('onAddUserToGroupResponse');
	}
	
", CClientScript::POS_HEAD);