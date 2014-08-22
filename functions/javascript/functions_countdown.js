/**
* Core count down functions for ILance.
*
* @package      iLance\Javascript\CountDown
* @version	4.0.0.8059
* @author       ILance
*/
var CountActive = true;
var CountStepper = -1;
var LeadingZero = false;
function calcage(secs, num1, num2)
{
	var s = ((Math.floor(secs / num1)) % num2).toString();
	if (LeadingZero && s.length < 2)
	{
		s = '0' + s;
	}
	return s;
}
function refresh_item_countdown(secs, auctiontype)
{
	if (secs == 1)
	{
		window.location.reload();
		return;
	}
	if (dstart > dnow)
	{
		if (secs < 0)
		{
			timed_refresh(300);
			return;
		}
		var days = calcage(secs, 86400, 100000);
		var hours = calcage(secs, 3600, 24);
		var mins = calcage(secs, 60, 60);
		var secss = calcage(secs, 1, 60);
		if (days > 0)
		{
			ilancestring = ilance_date_format1.replace(/%%D%%/g, days);
			ilancestring = ilancestring.replace(/%%H%%/g, hours);
			ilancestring = ilancestring.replace(/%%M%%/g, mins);
			ilancestring = ilancestring.replace(/%%S%%/g, secss);
		}
		else if (days == 0 && hours > 0)
		{
			ilancestring = ilance_date_format2.replace(/%%H%%/g, hours);
			ilancestring = ilancestring.replace(/%%M%%/g, mins);
			ilancestring = ilancestring.replace(/%%S%%/g, secss);
		}
		else if (days == 0 && hours == 0 && mins > 0)
		{
			ilancestring = ilance_date_format3.replace(/%%M%%/g, mins);
			ilancestring = ilancestring.replace(/%%S%%/g, secss);
		}
		else if (days == 0 && hours == 0 && mins == 0 && secss > 0)
		{
			ilancestring = ilance_date_format4.replace(/%%S%%/g, secss);
		}
		if (days == 0 && hours == 0)
		{
			var CountActive = true;
		}
		else
		{
			var CountActive = false;
		}
		fetch_js_object('timelefttext').innerHTML = ilancestring;
		fetch_js_object('timelefttext_modal').innerHTML = ilancestring;
	}
	else
	{
		if (secs < 0)
		{
			timed_refresh(300);
			return;
		}
		var days = calcage(secs, 86400, 100000);
		var hours = calcage(secs, 3600, 24);
		var mins = calcage(secs, 60, 60);
		var secss = calcage(secs, 1, 60);
		if (days > 0)
		{
			ilancestring = ilance_date_format1.replace(/%%D%%/g, days);
			ilancestring = ilancestring.replace(/%%H%%/g, hours);
			ilancestring = ilancestring.replace(/%%M%%/g, mins);
			ilancestring = ilancestring.replace(/%%S%%/g, secss);
		}
		else if (days == 0 && hours > 0)
		{
			ilancestring = ilance_date_format2.replace(/%%H%%/g, hours);
			ilancestring = ilancestring.replace(/%%M%%/g, mins);
			ilancestring = ilancestring.replace(/%%S%%/g, secss);
		}
		else if (days == 0 && hours == 0 && mins > 0)
		{
			ilancestring = ilance_date_format3.replace(/%%M%%/g, mins);
			ilancestring = ilancestring.replace(/%%S%%/g, secss);
			ilancestring = '<span class="bigblackcountdown">' + ilancestring + '</span>';
		}
		else if (days == 0 && hours == 0 && mins == 0 && secss > 0)
		{
			ilancestring = ilance_date_format4.replace(/%%S%%/g, secss);
			ilancestring = '<span class="bigredcountdown">' + ilancestring + '</span>';
		}
		if (days == 0 && hours == 0)
		{
			var CountActive = true;
		}
		else
		{
			var CountActive = false;
		}
		fetch_js_object('timelefttext').innerHTML = ilancestring;
		fetch_js_object('timelefttext_modal').innerHTML = ilancestring;
	}
	if (CountActive)
	{
		fetch_js_object('isecs').value = (secs + CountStepper);
		if (fetch_js_object('dthen').value != '')
		{
			var str = mysql_datetime_to_js_date(fetch_js_object('dthen').value);
			var string1 = str.toString();
			var string2 = dthen.toString();
			if (string1 != string2)
			{
				dthen = mysql_datetime_to_js_date(fetch_js_object('dthen').value);
				dnow = mysql_datetime_to_js_date(fetch_js_object('dnow').value);
				ddiff = new Date(dthen - dnow);
				var isecs = Math.floor(ddiff.valueOf() / 1000);
				if (isecs > secs)
				{
					var newcount = (Math.abs(isecs - secs));
					fetch_js_object('isecs').value = (secs + newcount) + CountStepper;
				}
			}
		}
		window.setTimeout("refresh_item_countdown(" + (Math.abs(fetch_js_object('isecs').value)) + ", \"" + auctiontype + "\")", SetTimeOutPeriod);
	}
}
CountStepper = Math.ceil(CountStepper);
if (CountStepper == 0)
{
	CountActive = false;
}
var SetTimeOutPeriod = (Math.abs(CountStepper) - 1) * 1000 + 1000;
if (dstart > dnow)
{
	var ddiff = new Date(dstart - dnow);
}
else
{
	var ddiff = new Date(dthen - dnow);
}
var isecs = Math.floor(ddiff.valueOf() / 1000);