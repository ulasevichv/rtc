<?php

$baseUrl = Yii::app()->theme->baseUrl;
?>

<div id="main_menu" class="navbar navbar-inverse navbar-static-top" role="navigation">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="sr-only"><?php echo CHtml::encode(Yii::t('general', 'Toggle navigation')); ?></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
<!--			<a class="navbar-brand" href="/">--><?php //echo CHtml::encode(Yii::app()->name); ?><!--</a>-->
			<span class="navbar-brand navbar_brand_static"><?php echo CHtml::encode(Yii::app()->name); ?></span>
		</div>
		<div class="navbar-collapse collapse">
			<?php
			echo $this->widget('zii.widgets.CMenu', array(
				'htmlOptions' => array('class' => 'nav navbar-nav'),
				'items' => array(
					array('label' => Yii::t('general', 'Home'), 'url' => array('/')),
					array('label' => '',
						'template' => '<a href="#" class="dropdown-toggle" data-toggle="dropdown">'.Yii::t('general', 'Test: Dropdown').'<b class="caret"></b></a>',
						'itemOptions' => array('class' => 'dropdown'),
						'submenuOptions' => array('class' => 'dropdown-menu'),
						'items' => array(
							array('label' => Yii::t('general', 'Test: Action'), 'url' => '#'),
							array('label' => Yii::t('general', 'Test: Another action'), 'url' => '#'),
							array('label' => Yii::t('general', 'Test: Something else here'), 'url' => '#'),
							array('template' => '', 'itemOptions' => array('class' => 'divider')),
							array('label' => Yii::t('general', 'Test: Nav header'), 'itemOptions' => array('class' => 'dropdown-header')),
							array('label' => Yii::t('general', 'Test: Separated link'), 'url' => '#'),
							array('label' => Yii::t('general', 'Test: One more separated link'), 'url' => '#'),
						),
					),
				),
			), true);
			
			// Login/register section.
			
			if (empty(Yii::app()->user->id))
			{
				$items = array(
					array(
						'label' => '',
						'url' => '',
						'itemOptions' => array('id' => 'profile_menu_item'),
						'template' => Yii::t('general', 'Login'),
					),
					array(
						'label' => Yii::t('general', 'Login'),
						'url' => '',
						'itemOptions' => array('id' => 'login_menu_item'),
						'template' => '{menu}',
					),
					array(
						'label' => '/',
						'url' => '',
						'itemOptions' => array('class' => 'separator_menu_item'),
						'template' => '{menu}',
					),
					array(
						'label' => Yii::t('general', 'Register'),
						'url' => array('/user/register'),
						'itemOptions' => array('id' => 'register_menu_item'),
						'template' => '{menu}',
					),
				);
			}
			else
			{
				$items = array(
					array(
						'label' => '',
						'url' => '',
						'itemOptions' => array('id' => 'profile_menu_item'),
						'template' => '<div class="_email">'.CHtml::encode(Yii::app()->user->email).'</div>'.
							'<div class="_icon input-group-addon glyphicon glyphicon-user"></div>',
					),
					array(
						'label' => Yii::t('general', 'Logout'),
						'url' => '',
						'itemOptions' => array('id' => 'login_menu_item'),
						'template' => '{menu}',
					),
				);
			}
			
			echo $this->widget('zii.widgets.CMenu', array(
				'htmlOptions' => array('class' => 'nav navbar-nav login_items'),
				'items' => $items,
			), true);
			?>
		</div>
	</div>
</div>

<?php

if (!empty(Yii::app()->user->id))
{
	Yii::app()->clientScript->registerScript(uniqid(), "
		
		$('#login_menu_item').on('click', function()
		{
			if (!confirm('".Yii::t('general', 'Are you sure you want to logout?')."')) return;
			
			var form = document.createElement('form');
			form.setAttribute('action', '".Yii::app()->controller->createUrl('user/logout')."');
			form.setAttribute('method', 'post');
			form.setAttribute('target', '_self');
			document.body.appendChild(form);
			
			form.submit();
		});
		
	", CClientScript::POS_READY);
}
else
{
	// Preventing login form dialog for login view.
	
	if (Yii::app()->urlManager->parseUrl(Yii::app()->request) == Yii::app()->user->loginUrl)
	{
		Yii::app()->clientScript->registerScript(uniqid(), "
			
			$('#login_menu_item').on('click', function()
			{
				alert('".Yii::t('general', 'Login form is already displayed')."');
			});
			
		", CClientScript::POS_READY);
		
		return;
	}
	
	$this->beginWidget('zii.widgets.jui.CJuiDialog', array(
		'id' => 'login_dialog',
		'cssFile' => null,
		'options' => array(
			'title' => Yii::t('general', 'Login'),
			'modal' => true,
			'autoOpen' => false,
			'show' => array('effect' => 'size'),
			'hide' => array('effect' => 'size'),
			'position' => 'top',
			'width' => 350,
			'height' => 'auto',
			'draggable' => false,
			'resizable' => false,
			'closeText' => Yii::t('general', 'Close'),
			'dialogClass' => 'dialog_top',
			'buttons' => array(
				array(
					'text' => Yii::t('general', 'Login'),
					'class' => 'btn btn-primary',
					'click' => "js:function()
					{
						ajaxValidateUserLoginForm();
					}",
				),
				array(
					'text' => Yii::t('general', 'Close'),
					'class' => 'btn btn-default',
					'click' => "js:function()
					{
						$(this).dialog('close');
					}",
				),
			),
		),
	));
	echo '<div class="_content"></div>';
	$this->endWidget('zii.widgets.jui.CJuiDialog');
	
	Yii::app()->clientScript->registerScript(uniqid(), "
		
		$('#login_menu_item').on('click', function()
		{
			loadLoginDialogHtml();
		});
		
		function loadLoginDialogHtml()
		{
			var request = $.ajax({
				url : '?r=user/login',
				data : {  },
				type : 'POST',
				dataType : 'html',
				cache : false,
				timeout : 5000
			});
			
			request.success(function(response, status, request)
			{
				openLoginDialog(response);
			});
			
			request.error(requestTimedOut);
		}
		
		function openLoginDialog(html)
		{
			var jDialog = $('#login_dialog');
			var jDialogContent = jDialog.children().first();
			
			jDialog.parent().find('button').attr('tabindex', -1); // Buttons tabulation bug-fix.
			
			jDialog.dialog({
				open: function(event, ui) {
					window.setTimeout(function() { jDialog.parent().find('button').removeAttr('tabindex'); }, 600);
				}
			});
			
			jDialogContent.html(html);
			
			jDialog.parent().css({position:'absolute'}); // Overlay height bug-fix.
			jDialog.dialog('open');
		}
		
		function requestTimedOut(request, status, error)
		{
			if (status == 'timeout') alert('".Yii::t('general', 'Request timed out. Please, try again')."');
		}
		
	", CClientScript::POS_READY);
}