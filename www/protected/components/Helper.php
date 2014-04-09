<?php

class Helper
{
	public static function modelErrorToString(CModel $model)
	{
		$msg = '';
		
		if ($model->hasErrors())
		{
			$errors = $model->getErrors();
			
			foreach ($errors as $key => $value)
			{
				$msg = $key.' - '.$value[0];
				break;
			}
		}
		
		return $msg;
	}
}