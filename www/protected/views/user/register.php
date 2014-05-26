<?php
$baseUrl = Yii::app()->theme->baseUrl;

Yii::app()->clientScript->registerScriptFile($baseUrl.'/assets/js/MethodsForStrings.js');
Yii::app()->clientScript->registerScriptFile($baseUrl.'/assets/js/strophe.js');

$xmppAddress = Yii::app()->params->xmppServerIP;
$boshAddress = 'http://'.Yii::app()->params->xmppServerIP.'/http-bind';

$this->renderPartial('register_xmpp_js', array(
), false, false);
?>

<h1><?php echo Yii::t('general', 'Register'); ?></h1>

<?php
$form = $this->beginWidget('CActiveForm', array(
	'id' => 'user_register_form',
	'enableClientValidation' => false,
	'enableAjaxValidation' => true,
	'action' => $this->createUrl('user/register'),
	'clientOptions' => array(
		'validateOnSubmit' => true,
	),
	'htmlOptions' => array(
		'autocomplete' => 'off',
		'onsubmit' => "ajaxValidateUserRegisterForm(); return false;",
	),
));
?>
	
	<div class="_row">
		<?php echo $form->labelEx($model, 'firstName'); ?>
		<?php echo $form->textField($model, 'firstName', array('class' => 'form-control', 'value' => 'Test')); ?>
	</div>
	
	<div class="_row">
		<?php echo $form->labelEx($model, 'lastName'); ?>
		<?php echo $form->textField($model, 'lastName', array('class' => 'form-control', 'value' => 'Test')); ?>
	</div>
	
	<div class="_row">
		<?php echo $form->labelEx($model, 'email'); ?>
		<?php echo $form->textField($model, 'email', array('class' => 'form-control', 'value' => 'test@nomail.com')); ?>
	</div>
	
	<div class="_row">
		<?php echo $form->labelEx($model, 'password'); ?>
		<?php echo $form->passwordField($model, 'password', array('class' => 'form-control', 'value' => '123456')); ?>
	</div>
	
	<div class="_row">
		<?php echo $form->labelEx($model, 'passwordRepeat'); ?>
		<?php echo $form->passwordField($model, 'passwordRepeat', array('class' => 'form-control', 'value' => '123456')); ?>
	</div>
	
	<?php
	if ($model->useCaptcha)
	{
		?>
		<div class="_row">
			<?php echo $form->labelEx($model, 'verifyCode'); ?>
			<div class="_captcha">
				<div class="_controls">
					<?php
					echo $this->widget('system.web.widgets.captcha.CCaptcha', array(
						'buttonOptions' => array(
							'tabindex' => -1,
						),
					), true);
					?>
				</div>
				<div class="_input">
					<?php echo $form->textField($model, 'verifyCode', array('class' => 'form-control')); ?>
				</div>
			</div>
		</div>
		<?php
	}	
	?>
	
	<div class="_row" style="display:none;">
		<?php echo $form->textField($model, 'xmppUserName', array()); ?>
	</div>
	
	<div class="_row" style="display:none;">
		<?php echo $form->textField($model, 'xmppUserPassword', array()); ?>
	</div>
	
	<div class="alert alert-danger"></div>
	
	<div class="_row">
		<?php echo CHtml::submitButton(Yii::t('general', 'Register'), array('class' => 'btn btn-primary')); ?>
	</div>
	
<?php $this->endWidget(); ?>

<?php
Yii::app()->clientScript->registerScript(uniqid(), "
	
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
		var jFirstName = $('#RegisterForm_' + 'firstName');
		var jLastName = $('#RegisterForm_' + 'lastName');
		var jEmail = $('#RegisterForm_' + 'email');
		var jPassword = $('#RegisterForm_' + 'password');
		var jPasswordRepeat = $('#RegisterForm_' + 'passwordRepeat');
		var jPasswordRepeat = $('#RegisterForm_' + 'passwordRepeat');
		var jVerifyCode = $('#RegisterForm_' + 'verifyCode');
		var jRefreshCaptcha = $('#".$form->id." div._captcha a');
		var jSubmitButton = $('#".$form->id." input[type=\"submit\"]');
		
		var controls = [ jFirstName, jLastName, jEmail, jPassword, jPasswordRepeat, jVerifyCode, jSubmitButton ];
		
		if (value)
		{
			for (var i = 0; i < controls.length; i++)
			{
				var control = controls[i];
				
				control.removeAttr('disabled');
			}
			
			jRefreshCaptcha.css('visibility', 'visible');
		}
		else
		{
			for (var i = 0; i < controls.length; i++)
			{
				var control = controls[i];
				
				control.attr('disabled', '');
			}
			
			jRefreshCaptcha.css('visibility', 'hidden');
		}
	}
	
	function ajaxValidateUserRegisterForm()
	{
		$('#".$form->id." > ._row').removeClass('has-error');
		
		var jFormErrorDiv = $('#".$form->id." .alert');
		jFormErrorDiv.css('display', 'none');
		
		var request = $.ajax({
			url : '?r=user/registerValidate',
			data : $('#".$form->id."').serialize(),
			type : 'POST',
			dataType : 'json',
			cache : false,
			timeout : 5000
		});
		
		request.success(function(response, status, request)
		{
			var errors = ajaxFormValidationJsonToArray(response);
			
			if (errors.length > 0)
			{
				var jFormRow = $('#'+String(errors[0].id)).parents('._row');
				
				jFormRow.addClass('has-error');
				
				jFormErrorDiv.html(errors[0].msg);
				jFormErrorDiv.css('display', 'block');
				return;
			}
			
			registerXmppUser();
		});
		
		request.error(requestTimedOutDefault);
	}
	
	function registerXmppUser()
	{
		blockControls();
		
		var firstName = $('#RegisterForm_' + 'firstName').val();
		var lastName = $('#RegisterForm_' + 'lastName').val();
		var email = $('#RegisterForm_' + 'email').val();
		
		var registration = new Registration('".$xmppAddress."', '".$boshAddress."', 'admin', 'zxasqw12');
		registration.setNewUserData(firstName, lastName, email);
		registration.setCallback(function(result) { onRegisterXmppUserCompleted(result); });
		registration.connect();
	}
	
	function onRegisterXmppUserCompleted(result)
	{
		console.log('onRegisterXmppUserCompleted()');
		console.log(result);
		
		if (result.type == 'error')
		{
			var jFormErrorDiv = $('#".$form->id." .alert');
			
			jFormErrorDiv.html(result.text);
			jFormErrorDiv.css('display', 'block');
			
			unblockControls();
			
			return;
		}
		
		addXmppUserToGroup(result.xmppUserName);
		
//		var jXmppUserNameInput = $('#RegisterForm_' + 'xmppUserName');
//		jXmppUserNameInput.attr('value', result.xmppUserName);
//		
//		var jXmppUserPasswordInput = $('#RegisterForm_' + 'xmppUserPassword');
//		jXmppUserPasswordInput.attr('value', result.xmppUserPassword);
//		
//		unblockControls();
//		
//		$('#".$form->id."').removeAttr('onsubmit');
//		$('#".$form->id."').submit();
	}
	
	function addXmppUserToGroup(xmppUserName)
	{
		var xmppGroupName = 'TeqSpring';
		
		var request = $.ajax({
			url : '?r=user/addXmppUserToGroup',
			data : { xmppUserName : xmppUserName, xmppGroupName : xmppGroupName },
			type : 'POST',
			dataType : 'json',
			cache : false,
			timeout : 5000
		});
		
		request.success(function(response, status, request)
		{
			console.log(response);
		});
		
		request.error(requestTimedOutDefault);
	}
	
", CClientScript::POS_END);