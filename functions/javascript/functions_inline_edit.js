/**
* Core inline edit javascript functions within ILance.
*
* @package      iLance
* @version      4.0.0.8059
* @author       ILance
*/
function iLance_Inline_Edit(){};
iLance_Inline_Edit.prototype.init_edit = function()
{
	try
	{
		// Mozilla / Safari
		this._xh = new XMLHttpRequest();
	}
	catch (e)
	{
		// Explorer
		var _ie_modes = new Array(
			'MSXML2.XMLHTTP.5.0',
			'MSXML2.XMLHTTP.4.0',
			'MSXML2.XMLHTTP.3.0',
			'MSXML2.XMLHTTP',
			'Microsoft.XMLHTTP'
		);
		
		var success = false;
		
		for (var i = 0; i < _ie_modes.length && !success; i++)
		{
			try
			{
				this._xh = new ActiveXObject(_ie_modes[i]);
				success = true;
			}
			catch (e)
			{
				
			}
		}
		
		if (!success)
		{
			return false;
		}
		
		return true;
	}
}
iLance_Inline_Edit.prototype.busy = function()
{
	estadoActual = this._xh.readyState;
	return (estadoActual && (estadoActual < 4));
}
iLance_Inline_Edit.prototype.procesa = function()
{
	if (this._xh.readyState == 4 && this._xh.status == 200)
	{
		this.procesado = true;
	}
}
iLance_Inline_Edit.prototype.send = function(urlget, data)
{
	if (!this._xh)
	{
		this.init_edit();
	}
	if (!this.busy())
	{
		this._xh.open('POST', urlget, false);
		this._xh.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		this._xh.send(data + '&s=' + fetch_session_id());
		if (this._xh.readyState == 4 && this._xh.status == 200)
		{
			return this._xh.responseText;
		}
	}
	return false;
}
function _gr(reqseccion, divcont)
{
	remotos = new iLance_Inline_Edit;
	nt = remotos.send(reqseccion, "");
	fetch_js_object(divcont).innerHTML = nt;
}
function base64(){}
base64.chars = new Array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','0','1','2','3','4','5','6','7','8','9','+','/');
base64.cadena = "";
base64.cuenta = 0;
base64.setCadena = function (str)
{
	base64.cadena = str;
	base64.cuenta = 0;
}
base64.read = function ()
{    
	if (!base64.cadena) return "END_OF_INPUT";
	if (base64.cuenta >= base64.cadena.length) return "END_OF_INPUT";
	var c = base64.cadena.charCodeAt(base64.cuenta) & 0xff;
	base64.cuenta++;
	return c;
}
base64.prototype.encode = function (str)
{
	base64.setCadena(str);
	var result = '';
	var inBuffer = new Array(3);
	var lineCount = 0;
	var done = false;
	while (!done && (inBuffer[0] = base64.read()) != "END_OF_INPUT")
	{
		inBuffer[1] = base64.read();
		inBuffer[2] = base64.read();
		result += (base64.chars[ inBuffer[0] >> 2 ]);
		
		if (inBuffer[1] != "END_OF_INPUT")
		{
			result += (base64.chars [(( inBuffer[0] << 4 ) & 0x30) | (inBuffer[1] >> 4) ]);
			if (inBuffer[2] != "END_OF_INPUT")
			{
				result += (base64.chars [((inBuffer[1] << 2) & 0x3c) | (inBuffer[2] >> 6) ]);
				result += (base64.chars [inBuffer[2] & 0x3F]);
			}
			else
			{
				result += (base64.chars [((inBuffer[1] << 2) & 0x3c)]);
				result += ('=');
				done = true;
			}
		}
		else
		{
			result += (base64.chars [(( inBuffer[0] << 4 ) & 0x30)]);
			result += ('=');
			result += ('=');
			done = true;
		}
		lineCount += 4;
		if (lineCount >= 76)
		{
			result += ('\n');
			lineCount = 0;
		}
	}
	return result;
}
b64 = new base64;
function do_inline_edit(nn, actual)
{
	elem = fetch_js_object('phrase' + nn + 'inline');
	elem.innerHTML = '<input maxlength="140" type="text" value="' + html_entity_decode(actual.innerHTML) + '" size="50" onkeypress="return detect_enter(this, event, \'' + nn + '\')" onblur="return detect_blur(this, \'' + nn + '\')" class="input" />';
	elem.firstChild.focus();
}
function detect_enter(campo, evt, idfld)
{
	evt = (evt) ? evt : window.event;
	if (evt.keyCode == 13 && campo.value != '')
	{
		elem = fetch_js_object('phrase' + idfld + 'inline');
		remotos = new iLance_Inline_Edit;
		nt = remotos.send(urlBase + idfld + '&text=' + escape(campo.value) + '&s=' + ILSESSION + '&token=' + ILTOKEN, '');
		elem.innerHTML = '<span ondblclick="do_inline_edit(\'' + idfld + '\', this);">' + nt + '</span>';
		elem.firstChild.innerHTML = nt;
		return false;
	}
	else
	{
		return true;
	}
}
function detect_blur(campo, idfld)
{
	if (campo.value != '')
	{
		elem = fetch_js_object('phrase' + idfld + 'inline');
		remotos = new iLance_Inline_Edit;
		nt = remotos.send(urlBase + idfld + "&text=" + escape(campo.value) + '&s=' + ILSESSION + '&token=' + ILTOKEN, '');
		elem.innerHTML = '<span ondblclick="do_inline_edit(\'' + idfld + '\', this);">' + nt + '</span>';
		elem.firstChild.innerHTML = nt;
		return false;
	}
}