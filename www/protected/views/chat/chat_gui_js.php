<?php
Yii::app()->clientScript->registerScript(uniqid(), "
	
	var ChatGUI = {
		
		blockControls : function()
		{
			ChatGUI.changeControlsAvailability(false);
		},
		
		unblockControls : function()
		{
			ChatGUI.changeControlsAvailability(true);
		},
		
		changeControlsAvailability : function(value)
		{
			var jControls = [
				$('#inputMessage'),
				$('#btnSend')
			];
			
			if (value)
			{
				jControls.forEach(function(jControl, i)
				{
					jControl.removeAttr('disabled');
				});
			}
			else
			{
				jControls.forEach(function(jControl, i)
				{
					jControl.attr('disabled', '');
				});
			}
		}
	};
	
", CClientScript::POS_HEAD);