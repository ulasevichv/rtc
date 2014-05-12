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
	}
	
	Registration.prototype.connect = function()
	{
		this.conn  = new Strophe.Connection(this.boshAddress);
		
		console.log('Connecting \'' + this.adminUser.jid + '\' (' + this.adminUser.password + ')');
		
		var obj = this;
		
		this.conn.connect(this.adminUser.jid, this.adminUser.password, function(status) { obj.onConnectionStatusChange(status); });
	}
	
	Registration.prototype.onConnectionStatusChange = function(status)
	{
		console.log(status);
	}
	
//	var Registration = {
//		conn : null,
//		domain : '".$xmppAddress."',
//		boshAddress : '".$boshAddress."',
//		adminUser : {
//			username : 'admin',
//			password : ''
//			jid : 'admin@".$xmppAddress."'
//		},
//		
//		connect : function()
//		{
//			Registration.conn  = new Strophe.Connection(Registration.boshAddress);
//			
//			console.log('Connecting \'' + Registration.adminUser.jid + '\' (' + Registration.adminUser.password + ')');
//			
//			Registration.conn.connect(Registration.currentUser.bareJid, Registration.currentUser.password, function(status) { Registration.onConnectionStatusChange(status); });
//		},
//		
//		onConnectionStatusChange : function(status)
//		{
//			
//		}
//	};
	
", CClientScript::POS_HEAD);

Yii::app()->clientScript->registerScript(uniqid(), "
	
	var registration = new Registration('".$xmppAddress."', '".$boshAddress."', 'admin', 'zxasqw12');
	
//	Registration.connect();
	
//	console.log(registration);
	
	registration.connect();
	
", CClientScript::POS_READY);