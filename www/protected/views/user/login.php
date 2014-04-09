<?php

if (!empty(Yii::app()->user->id))
{
	echo Yii::t('general', 'Already logged in');
	return;
}

if (!Yii::app()->request->isAjaxRequest)
{
	echo '<h1>'.Yii::t('general', 'Login').'</h1>';
}

$form = $this->beginWidget('CActiveForm', array(
	'id' => 'user_login_form',
	'enableClientValidation' => false,
	'enableAjaxValidation' => true,
	'action' => $this->createUrl('user/login'),
	'clientOptions' => array(
		'validateOnSubmit' => true,
	),
	'htmlOptions' => array(
		'autocomplete' => 'on',
		'class' => (Yii::app()->request->isAjaxRequest ? '' : '_not_modal'),
		'onsubmit' => "ajaxValidateUserLoginForm(); return false;",
	),
));
?>
	
	<div class="_row">
		<div class="input-group">
			<span class="input-group-addon glyphicon glyphicon-user"></span>
			<?php echo $form->textField($model, 'email', array('class' => 'form-control', 'placeholder' => $model->getAttributeLabel('email'))); ?>
		</div>
	</div>
	
	<div class="_row">
		<div class="input-group">
			<span class="input-group-addon glyphicon glyphicon-lock"></span>
			<?php echo $form->passwordField($model, 'password', array('class' => 'form-control', 'placeholder' => $model->getAttributeLabel('password'))); ?>
		</div>
	</div>
	
	<div class="_row">
		<?php echo $form->checkBox($model, 'rememberMe', array('value' => 1, 'checked' => 'checked')); ?>
		<?php echo $form->label($model, 'rememberMe'); ?>
	</div>
	
	<div class="alert alert-danger"></div>
	
	<div class="_row _hidden">
		<?php echo CHtml::submitButton('#'); ?>
	</div>
	
<?php $this->endWidget(); ?>

<?php
Yii::app()->clientScript->registerScript(uniqid(), "
	
	if (typeof(LoginFormJsAdded) == 'undefined')
	{
		var LoginFormJsAdded = true;
		
		function ajaxValidateUserLoginForm()
		{
			$('#".$form->id." > ._row').removeClass('has-error');
			
			var jFormErrorDiv = $('#".$form->id." .alert');
			jFormErrorDiv.css('display', 'none');
			
			var request = $.ajax({
				url : '?r=user/loginValidate',
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
				
				$('#".$form->id."').removeAttr('onsubmit');
				$('#".$form->id."').submit();
			});
			
			request.error(requestTimedOutDefault);
		}
	}
	
", CClientScript::POS_END);