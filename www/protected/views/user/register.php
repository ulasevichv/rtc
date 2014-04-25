<?php
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
		<?php echo $form->textField($model, 'firstName', array('class' => 'form-control')); ?>
	</div>
	
	<div class="_row">
		<?php echo $form->labelEx($model, 'lastName'); ?>
		<?php echo $form->textField($model, 'lastName', array('class' => 'form-control')); ?>
	</div>
	
	<div class="_row">
		<?php echo $form->labelEx($model, 'email'); ?>
		<?php echo $form->textField($model, 'email', array('class' => 'form-control')); ?>
	</div>
	
	<div class="_row">
		<?php echo $form->labelEx($model, 'password'); ?>
		<?php echo $form->passwordField($model, 'password', array('class' => 'form-control')); ?>
	</div>
	
	<div class="_row">
		<?php echo $form->labelEx($model, 'passwordRepeat'); ?>
		<?php echo $form->passwordField($model, 'passwordRepeat', array('class' => 'form-control')); ?>
	</div>
	
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
	
	<div class="alert alert-danger"></div>
	
	<div class="_row">
		<?php echo CHtml::submitButton(Yii::t('general', 'Register'), array('class' => 'btn btn-primary')); ?>
	</div>
	
<?php $this->endWidget(); ?>

<?php
Yii::app()->clientScript->registerScript(uniqid(), "
	
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
			
			$('#".$form->id."').removeAttr('onsubmit');
			$('#".$form->id."').submit();
		});
		
		request.error(requestTimedOutDefault);
	}
	
", CClientScript::POS_END);