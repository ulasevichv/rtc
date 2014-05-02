var MethodsForDateTime =
{
	dateToString : function(date, showMilliseconds, showTimestamp)
	{
		showMilliseconds = (typeof(showMilliseconds) != 'undefined' ? showMilliseconds : false);
		showTimestamp = (typeof(showTimestamp) != 'undefined' ? showTimestamp : false);
		
		if (typeof(date) == 'number')
		{
			date = new Date(date);
		}
		
		var result = date.getFullYear() + '-' +
			MethodsForDateTime.intToPadString(1 + date.getMonth(), 2) + '-' +
			MethodsForDateTime.intToPadString(date.getDate(), 2) + ' ' +
			MethodsForDateTime.intToPadString(date.getHours(), 2) + ':' +
			MethodsForDateTime.intToPadString(date.getMinutes(), 2) + ':' +
			MethodsForDateTime.intToPadString(date.getSeconds(), 2);
		
		if (showMilliseconds)  result += '.' + MethodsForDateTime.intToPadString(date.getMilliseconds(), 3);
		
		if (showTimestamp) result += ' (' + Math.floor(date.getTime() / 1000) + '.' + date.getMilliseconds() + ')';
		
		return result;
	},
	
	intToPadString : function(intValue, numDigitsRequired)
	{
		var strValue = intValue.toString();
		
		var numMissingDigits = numDigitsRequired - strValue.length;
		
		if (numMissingDigits <= 0) return strValue;
		
		var str = '';
		
		for (var i = 0; i < numMissingDigits; i++)
		{
			str += '0';
		}
		
		return str + strValue;
	},
	
	xmppStampToDateString : function(stamp)
	{
		var result = stamp;
		
		result = result.substring(0, 4) + '-' + result.substring(4, 6) + '-' + result.substring(6, 8) + ' ' + result.substring(9);
		
		return result;
	}
};