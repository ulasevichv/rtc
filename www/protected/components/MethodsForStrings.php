<?php

class MethodsForStrings
{
	// Generate random string.
	// @param int - required string length
	// @param string - case variant
	// @return string - generated random string
	//
	public static function GenerateRandomString($stringLength, $case = 'all')
	{
		$chars = '';
		
		switch ($case)
		{
			case 'all': $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; break;
			case 'upper': $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'; break;
			case 'lower': $chars = '0123456789abcdefghijklmnopqrstuvwxyz'; break;
		}
		
		$numChars = strlen($chars);
		
		$s = '';
		
		for ($i = 0; $i < $stringLength; $i++)
		{
			$index = rand(0, $numChars - 1);
			
			$s .= substr($chars, $index, 1);
		}
		
		return $s;
	}
}