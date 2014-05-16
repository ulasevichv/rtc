<?php

$baseUrl = Yii::app()->theme->baseUrl;
?>

<div id="chat_right_menu" class="navbar navbar-inverse navbar-static-top" role="navigation">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="sr-only"><?php echo CHtml::encode(Yii::t('general', 'Toggle navigation')); ?></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
		</div>
		<div class="navbar-collapse collapse">
			<?php

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
			
			echo $this->widget('zii.widgets.CMenu', array(
				'htmlOptions' => array('class' => 'nav navbar-nav login_items'),
				'items' => $items,
			), true);
			?>
		</div>
	</div>
</div>

<?php
Yii::app()->clientScript->registerScript(uniqid(), "

		$('#login_menu_item').on('click', function()
		{
			if (!confirm('".Yii::t('general', 'Are you sure you want to logout?')."')) return;

			if (typeof(Chat) != 'undefined')
			{
				Chat.disconnect();
			}

			var form = document.createElement('form');
			form.setAttribute('action', '".Yii::app()->controller->createUrl('user/logout')."');
			form.setAttribute('method', 'post');
			form.setAttribute('target', '_self');
			document.body.appendChild(form);

			form.submit();
		});

	", CClientScript::POS_READY);