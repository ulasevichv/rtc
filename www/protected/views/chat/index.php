<?php
$baseUrl = Yii::app()->theme->baseUrl;

Yii::app()->clientScript->registerCssFile('http://fonts.googleapis.com/css?family=Roboto:400&subset=latin,cyrillic');
Yii::app()->clientScript->registerCssFile($baseUrl.'/assets/css/chat.css');
Yii::app()->clientScript->registerCssFile($baseUrl.'/assets/css/literally.css');

Yii::app()->clientScript->registerScriptFile($baseUrl.'/assets/js/MethodsForDateTime.js');
Yii::app()->clientScript->registerScriptFile($baseUrl.'/assets/js/MethodsForStrings.js');

Yii::app()->clientScript->registerScriptFile($baseUrl.'/assets/js/json.js');
Yii::app()->clientScript->registerScriptFile($baseUrl.'/assets/js/jquery.stringify.js');
Yii::app()->clientScript->registerScriptFile($baseUrl.'/assets/js/OTvideo.js');
Yii::app()->clientScript->registerScriptFile($baseUrl.'/assets/js/literallycanvas.jquery.js');
Yii::app()->clientScript->registerScriptFile($baseUrl.'/assets/js/whiteboard.js');
//Yii::app()->clientScript->registerScriptFile('//static.opentok.com/webrtc/v2.2/js/opentok.min.js');
Yii::app()->clientScript->registerScriptFile('//static.opentok.com/webrtc/v2.2/js/opentok.js');

Yii::app()->clientScript->registerScriptFile($baseUrl.'/assets/js/ion.sound.min.js');

Yii::app()->clientScript->registerScriptFile($baseUrl.'/assets/js/strophe.js');
Yii::app()->clientScript->registerScriptFile($baseUrl.'/assets/js/strophe.muc.js');
//Yii::app()->clientScript->registerScriptFile($baseUrl.'/assets/js/strophe.chatstates.js');
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
        <div class="header-left-menu">
            <?php
            	echo $this->widget('application.widgets.chatLeftMenu.chatLeftMenu', array(
                ), true);
            ?>
        </div>
        <div class="header-title">

        </div>
        <div class="header-right-menu">
            <?php
            echo $this->widget('application.widgets.chatRightMenu.chatRightMenu', array(
            ), true);
            ?>
        </div>
	</div>
	<div class="sections">
		<div class="roomsContainer">
			<div id="rooms">
			</div>
			<div id="staticRooms">
			</div>
		</div>
		<div id="users">
		</div>
		<div id="chat">
			<div id="videoChat" style="display:none;">
			</div>
			<div id="videoChatInviteButtons" style="display:none;">
				<p><?php echo Yii::t('general', 'User wants to use a video/audio communication.'); ?></p>
				<?php echo CHtml::htmlButton(Yii::t('general', 'Accept'), array('id' => 'btnAcceptVideoCall', 'class' => 'btn btn-primary')); ?>
				<?php echo CHtml::htmlButton(Yii::t('general', 'Decline'), array('id' => 'btnDeclineVideoCall', 'class' => 'btn btn-primary')); ?>
			</div>
            <div id="whiteboardInviteButtons" style="display:none;">
                <p><?php echo Yii::t('general', 'User wants to use a whiteboard'); ?></p>
                <?php echo CHtml::htmlButton(Yii::t('general', 'Accept'), array('id' => 'btnAcceptWhiteboard', 'class' => 'btn btn-primary')); ?>
                <?php echo CHtml::htmlButton(Yii::t('general', 'Decline'), array('id' => 'btnDeclineWhiteboard', 'class' => 'btn btn-primary')); ?>
            </div>
			<div id="messages"></div>
			<div id="sending" style="visibility:hidden;">
				<div id="userPanel">
					<a id="btnStartVideoCall" class="btn btn-primary" href="javascript:void(0)"><span class="glyphicon glyphicon-earphone"></span> <?php echo Yii::t('general', 'Video Call') ?></a>
					<a id="btnEndCall" style="display: none" onclick="OTvideo.session.disconnect();" class="btn btn-primary" href="javascript:void(0)"> <span class="glyphicon glyphicon-remove">
						</span> <?php echo Yii::t('general', 'End Call') ?>
					</a>
                    <a id="btnExpandVideo" style="display: none"  onclick="OTvideo.expand();" class="btn btn-primary" href="javascript:void(0)"> <span class="glyphicon glyphicon-fullscreen">
						</span> <?php echo Yii::t('general', 'Expand Video') ?>
                    </a>
					<?php //echo CHtml::htmlButton(Yii::t('general', 'Video Call'), array('id' => 'btnStartVideoCall', 'class' => 'btn btn-primary')); ?>
					<?php //echo CHtml::htmlButton(Yii::t('general', 'End Call'), array('id' => 'btnEndCall', 'class' => 'btn btn-primary', 'onclick' => 'OTvideo.session.disconnect();',
//						'style' => 'display:none;')); ?>
                    <a id="btnShowHistory" class="btn btn-primary" href="javascript:void(0)"> <span class="glyphicon glyphicon-dashboard">
						</span> <?php echo Yii::t('general', 'Show Chat History') ?>
                    </a>
                    <a id="btnWhiteboard" class="btn btn-primary" href="javascript:void(0)"> <span class="glyphicon glyphicon-dashboard">
						</span> <?php echo Yii::t('general', 'Start Drawing') ?>
                    </a>
				</div>
				<div class="controls">
					<?php echo CHtml::textArea(null, '', array('id' => 'inputMessage')); ?>
                    <a id="btnSend" class="btn btn-primary" href="javascript:void(0)"> <span class="glyphicon glyphicon-envelope">
						</span> <?php echo Yii::t('general', 'Send') ?>
                    </a>
<!--					--><?php //echo CHtml::htmlButton(Yii::t('general', 'Send'), array('id' => 'btnSend', 'class' => 'btn btn-primary')); ?>
				</div>
			</div>
		</div>
	</div>
    <div id="video-expanded" style="display: none">
        <div class="video-expanded-videoContainer">

        </div>
        <div class="video-expanded-buttons">
            <a id="btnCollapseVideo" onclick="OTvideo.collapse();" class="btn btn-primary" href="javascript:void(0)"> <span class="glyphicon glyphicon-log-in">
						</span> <?php echo Yii::t('general', 'Collapse Video') ?>
            </a>
        </div>

    </div>
    <div id="whiteboard-container" style="display: none">
        <div class="literally localstorage"><canvas></canvas></div>
        <a id="btnCloseWhiteboard" onclick="jQuery('whiteboard-container').hide(400)" class="btn btn-primary" href="javascript:void(0)"> <span class="glyphicon glyphicon-log-in">
						</span> <?php echo Yii::t('general', 'Close Whiteboard') ?>
        </a>
    </div>
</div>
<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog', array(
    'id'      => 'chat-history-dialog',
    'cssFile' => null,
    'options' => array(
        'title'     => Yii::t('general', 'Chat history'),
        'autoOpen'  => false,
        'modal'     => true,
        'hide'      => 'drop',
        'show'      => 'drop',
        'position'  => 'center',
        'height'    =>600,
        'width'     => 800,
        'resizable' => false,
        'buttons'   => array(
            array(
                'text'  => Yii::t('general', 'Close'),
                'click' => 'js:function(){ $(this).dialog("close");}',
                'class' => 'btn'
            ),
        ),
    ),
));
?>
    <div id="chat-history-dialog-container"></div>
<?php $this->endWidget('zii.widgets.jui.CJuiDialog');
?>