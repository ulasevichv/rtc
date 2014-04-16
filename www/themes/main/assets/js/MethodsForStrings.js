MethodsForStrings =
{
	idsToString : function(ids)
	{
		var result = '';
		
		for (var i = 0; i < ids.length; i++)
		{
			result += (i == 0 ? '' : ',') + ids[i].toString();
		}
		
		return result;
	},
	
	escapeQuotes : function(str)
	{
		if (typeof str !== 'string') str = str.toString();
		
		return str.replace(new RegExp('"', 'gi'), '&quot;');
	},
	
	escapeApos : function(str)
	{
		if (typeof str !== 'string') str = str.toString();
		
		return str.replace(new RegExp('\'', 'gi'), '\\\'');
	},
	
	escapeHtml : function(str)
	{
		if (typeof str !== 'string') str = str.toString();
		
		return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&apos;');
	},
	
	trim : function(str)
	{
		if (typeof str !== 'string') str = str.toString();
		
		return str.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
	},
	
	fixLength : function(str, maximumLength, showDots)
	{
		showDots = (showDots !== undefined ? showDots : true);
		
		if (typeof str !== 'string') str = str.toString();
		
		var result = str;
		
		if (maximumLength >= 1 && str.length > maximumLength)
		{
			if (showDots && maximumLength >= 4) result = str.substring(0, maximumLength - 3) + '...';
			else result = str.substring(0, maximumLength);
		}
		
		return result;
	},
	
	generateRandomString : function(stringLength, symbolsCase)
	{
		symbolsCase = (typeof(symbolsCase) != 'undefined' ? symbolsCase : 'all');
		
		var chars = '';
		
		switch (symbolsCase)
		{
			case 'all': chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'; break;
			case 'upper': chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'; break;
			case 'lower': chars = '0123456789abcdefghijklmnopqrstuvwxyz'; break;
		}
		
		var numChars = chars.length;
		
		var s = '';
		
		for (var i = 0; i < stringLength; i++)
		{
			var index = Math.floor((Math.random() * numChars));
			
			s += chars.substring(index, index + 1);
		}
		
		return s;
	}
};