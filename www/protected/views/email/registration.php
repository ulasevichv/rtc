<?php
/* @var $model RegisterForm */

$baseUrl = Yii::app()->getBaseUrl(true);
$baseUrlText = str_replace('http://', '', $baseUrl);
$baseUrlText = str_replace('https://', '', $baseUrlText);
?>
<style type="text/css">
	
	.qaces_email_root {
		font-family: 'Times New Roman';
		font-size: 18px;
		color: #333333;
		cursor: default;
	}
	.qaces_label {
		color: #999900;
	}
	.qaces_value {
		color: #006600;
	}
	.qaces_credentials {
		float: left;
		margin-left: 30px;
	}
	
</style>

<div class="qaces_email_root">
	<?php echo Yii::t('email', 'Hello').','; ?>&nbsp;<span class="qaces_value"><?php echo htmlspecialchars($model->firstName.' '.$model->lastName).'.'; ?></span>
	<?php echo Yii::t('email', 'Thank you for registration on Q-Aces.'); ?>
	<br/><br/>
	<?php echo Yii::t('email', 'In order to login to {siteUrl} you can use following credentials',
			array('{siteUrl}' => '<a href="'.$baseUrl.'" target="_blank">'.$baseUrlText.'</a>')).':'; ?>
	<br/><br/>
	<div class="qaces_credentials">
		<span class="qaces_label"><?php echo Yii::t('email', 'Login (email)').':'; ?></span>
		<span class="qaces_value"><?php echo htmlspecialchars($model->email); ?></span>
		<br/><br/>
		<span class="qaces_label"><?php echo Yii::t('email', 'Password').':'; ?></span>
		<span class="qaces_value"><?php echo htmlspecialchars($model->password); ?></span>
	</div>
</div>