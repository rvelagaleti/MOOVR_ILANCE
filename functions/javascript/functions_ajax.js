/**
* Core AJAX Javascript functions for ILance.
*
* @package      iLance\Javascript\AJAX
* @version	4.0.0.8059
* @author       ILance
*/
function AJAX_Handler(async)
{
	this.async = async ? true : false;
}
AJAX_Handler.prototype.init = function()
{
	try
	{
		this.handler = new XMLHttpRequest();
		return (this.handler.setRequestHeader ? true : false);
	}
	catch(e)
	{
		try
		{
			this.handler = eval("new A" + "ctiv" + "eX" + "Ob" + "ject('Micr" + "osoft.XM" + "LHTTP');");
			return true;
		}
		catch(e)
		{
			return false;
		}
	}
}
AJAX_Handler.prototype.is_compatible = function()
{
	if (typeof ilance_disable_ajax != 'undefined' && ilance_disable_ajax == 2)
	{
		return false; // disable ajax functionality
	}
	if (checkie && !checkie4)
	{
		return true;
	}
	else if (typeof XMLHttpRequest != 'undefined')
	{
		try
		{
			return XMLHttpRequest.prototype.setRequestHeader ? true : false;
		}
		catch(e)
		{
			try { var tester = new XMLHttpRequest(); return tester.setRequestHeader ? true : false; }
			catch(e) { return false; }
		}
	}
	else { return false; }
}
AJAX_Handler.prototype.not_ready = function()
{
	return (this.handler.readyState && (this.handler.readyState < 4));
}
AJAX_Handler.prototype.onreadystatechange = function(event)
{
	if (!this.handler)
	{
		if  (!this.init())
		{
			return false;
		}
	}
	if (typeof event == 'function')
	{
		this.handler.onreadystatechange = event;
	}
	else
	{
		alert('XML Sender OnReadyState event is not a function');
	}
}
AJAX_Handler.prototype.send = function(url, data)
{
	if (!this.handler)
	{
		if (!this.init())
		{
			return false;
		}
	}
	if (!this.not_ready())
	{
		this.handler.open('POST', url, this.async);
		this.handler.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		this.handler.send(data + '&s=' + fetch_session_id());
		if (!this.async && this.handler.readyState == 4 && this.handler.status == 200)
		{
			return true;
		}
	}
	return false;
}
AJAX_Handler.prototype.fetch_data = function(xml_node)
{
	if (xml_node && xml_node.firstChild && xml_node.firstChild.nodeValue)
	{
		return unescape_cdata(xml_node.firstChild.nodeValue);
	}
	else
	{
		return '';
	}
}
/**
* Set AJAX Compatiblity
*/
var AJAX_Compatible = AJAX_Handler.prototype.is_compatible();