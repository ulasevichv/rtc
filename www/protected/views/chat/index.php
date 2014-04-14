<?php
$baseUrl = Yii::app()->theme->baseUrl;

Yii::app()->clientScript->registerCssFile($baseUrl.'/assets/css/chat.css');

//echo 'userId: '.Yii::app()->user->id;

?>

<div class="chatRoot">
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
		{ id : 3, name : 'Teniksoft', numUsersOnline: 1 }
	];
	
", CClientScript::POS_HEAD);

Yii::app()->clientScript->registerScript(uniqid(), "
	
", CClientScript::POS_READY);