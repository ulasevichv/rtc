<?php

$baseUrl = Yii::app()->theme->baseUrl;
?>

<div id="chat_left_menu" class="navbar navbar-inverse navbar-static-top" role="navigation">
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
					array('label' => Yii::t('general', 'Chat'), 'url' => array('/chat')),
//					array('label' => Yii::t('general', 'Registration'), 'url' => array('/registration')),
//					array('label' => Yii::t('general', 'Strophe'), 'url' => array('/strophe')),
//					array('label' => '',
//						'template' => '<a href="#" class="dropdown-toggle" data-toggle="dropdown">'.Yii::t('general', 'Test: Dropdown').'<b class="caret"></b></a>',
//						'itemOptions' => array('class' => 'dropdown'),
//						'submenuOptions' => array('class' => 'dropdown-menu'),
//						'items' => array(
//							array('label' => Yii::t('general', 'Test: Action'), 'url' => '#'),
//							array('label' => Yii::t('general', 'Test: Another action'), 'url' => '#'),
//							array('label' => Yii::t('general', 'Test: Something else here'), 'url' => '#'),
//							array('template' => '', 'itemOptions' => array('class' => 'divider')),
//							array('label' => Yii::t('general', 'Test: Nav header'), 'itemOptions' => array('class' => 'dropdown-header')),
//							array('label' => Yii::t('general', 'Test: Separated link'), 'url' => '#'),
//							array('label' => Yii::t('general', 'Test: One more separated link'), 'url' => '#'),
//						),
//					),
				),
			), true);
			?>
		</div>
	</div>
</div>