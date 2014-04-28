<?php
$baseUrl = Yii::app()->theme->baseUrl;

Yii::app()->clientScript->registerCssFile($baseUrl.'/assets/css/chat.css');

Yii::app()->clientScript->registerCssFile('http://fonts.googleapis.com/css?family=Roboto:400&subset=latin,cyrillic');

Yii::app()->clientScript->registerScriptFile($baseUrl.'/assets/js/MethodsForDateTime.js');
Yii::app()->clientScript->registerScriptFile($baseUrl.'/assets/js/MethodsForStrings.js');

Yii::app()->clientScript->registerScriptFile($baseUrl.'/assets/js/strophe.js');
//Yii::app()->clientScript->registerScriptFile($baseUrl.'/assets/js/strophe.chatstates.js');
Yii::app()->clientScript->registerScriptFile($baseUrl.'/assets/js/strophe.muc.js');
//Yii::app()->clientScript->registerScriptFile($baseUrl.'/assets/js/strophe.roster.js');
//Yii::app()->clientScript->registerScriptFile($baseUrl.'/assets/js/roster.js');
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


$this->renderPartial('chat_classes_js', array(
), false, true);

$this->renderPartial('chat_js', array(
	'xmppAddress' => $xmppAddress,
	'boshAddress' => $boshAddress,
	'xmppUser' => $xmppUser,
), false, false);

$this->renderPartial('chat_gui_js', array(
), false, false);

?>

<div class="chatRoot">
	<div class="header">
	</div>
	<div class="sections">
		<div id="rooms">
		</div>
		<div id="users">
		</div>
		<div id="chat">
			<div id="messages"></div>
			<div id="sending" style="visibility:hidden;">
				<div class="controls">
					<?php echo CHtml::textArea(null, '', array('id' => 'inputMessage')); ?>
					<?php echo CHtml::htmlButton(Yii::t('general', 'Send'), array('id' => 'btnSend', 'class' => 'btn btn-primary')); ?>
				</div>
			</div>
		</div>
	</div>
</div>