<?php
$cs = Yii::app()->clientScript;

$cs->scriptMap = array(
	'jquery.js' => false,
	'jquery.min.js' => false,
	'jquery-ui.js' => false,
	'jquery-ui.min.js' => false,
);
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="<?php echo Yii::app()->theme->baseUrl.'/assets/css/jquery-ui-1.10.4.modified.css'; ?>" />
		<link rel="stylesheet" href="<?php echo Yii::app()->theme->baseUrl.'/assets/css/bootstrap.min.css'; ?>" />
		<link rel="stylesheet" href="<?php echo Yii::app()->theme->baseUrl.'/assets/css/bootstrap-theme.min.css'; ?>" />
		<link rel="stylesheet" href="<?php echo Yii::app()->theme->baseUrl.'/assets/css/general.css'; ?>" />
		<script src="<?php echo Yii::app()->theme->baseUrl.'/assets/js/jquery-1.11.0.min.js'; ?>"></script>
		<script src="<?php echo Yii::app()->theme->baseUrl.'/assets/js/bootstrap-3.1.1.min.js'; ?>"></script>
		<script src="<?php echo Yii::app()->theme->baseUrl.'/assets/js/jquery-ui-1.10.4.min.js'; ?>"></script>
		<title><?php echo CHtml::encode($this->pageTitle); ?></title>
	</head>
	<body>
		<?php
		echo $this->widget('application.widgets.mainMenu.mainMenu', array(
		), true);
		?>
		<div class="container theme-showcase" role="main">
			<?php
			// Flash-messages.
			$flashMessages = Yii::app()->user->getFlashes();
			if ($flashMessages && count($flashMessages) > 0)
			{
				foreach ($flashMessages as $key => $message)
				{
					$bootstrapAlertClass = '';
					
					switch ($key)
					{
						case 'success': $bootstrapAlertClass = 'success'; break;
						case 'notice': $bootstrapAlertClass = 'warning'; break;
						case 'info' : $bootstrapAlertClass = 'info'; break; // Not present in Yii by default.
						case 'error': default: $bootstrapAlertClass = 'danger'; break;
					}
					
					echo '<div class="alert alert-'.$bootstrapAlertClass.'">'.$message.'</div>';
				}
			}
			// Content.
			echo $content;
			?>
		</div>
	</body>
</html>
<?php
Yii::app()->clientScript->registerScript(uniqid(), "
	
	function requestTimedOutDefault(request, status, error)
	{
		if (status == 'timeout') alert('".Yii::t('general', 'Request timed out. Please, try again')."');
	}
	
	function ajaxFormValidationJsonToArray(json)
	{
		var arr = new Array();
		
		for (var property in json)
		{
			arr.push({ id : property, msg : json[property] });
		}
		
		return arr;
	}
	
", CClientScript::POS_HEAD);