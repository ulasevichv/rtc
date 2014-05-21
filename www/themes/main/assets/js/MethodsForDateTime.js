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
	
	// Expected format is canonical: 2001-01-01 00:00:00
	//
	stringToDate : function(str)
	{
		var year = parseInt(str.substring(0, 4));
		var month = parseInt(str.substring(5, 7));
		var day = parseInt(str.substring(8, 10));
		var hour = parseInt(str.substring(11, 13));
		var minute = parseInt(str.substring(14, 16));
		var second = parseInt(str.substring(17, 19));
		
		return new Date(year, month - 1, day, hour, minute, second);
	},
	
	xmppStampToDateString : function(stamp)
	{
		var result = stamp;
		
		result = result.substring(0, 4) + '-' + result.substring(4, 6) + '-' + result.substring(6, 8) + ' ' + result.substring(9);
		
		return result;
	},
	
	// Difference between this and Date.toISOString() is absence of milliseconds in the result.
	//
	dateToISO8601 : function(date)
	{
		return date.getUTCFullYear() + '-' +
			MethodsForDateTime.intToPadString(1 + date.getUTCMonth(), 2) + '-' +
			MethodsForDateTime.intToPadString(date.getUTCDate(), 2) + 'T' +
			MethodsForDateTime.intToPadString(date.getUTCHours(), 2) + ':' +
			MethodsForDateTime.intToPadString(date.getUTCMinutes(), 2) + ':' +
			MethodsForDateTime.intToPadString(date.getUTCSeconds(), 2) + 'Z';
	},
	
	getDayBeginningDateTime : function(date)
	{
		var newDate = new Date(date.getTime());
		
		newDate.setHours(0);
		newDate.setMinutes(0);
		newDate.setSeconds(0);
		newDate.setMilliseconds(0);
		
		return newDate;
	}
};