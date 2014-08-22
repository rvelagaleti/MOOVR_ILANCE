/**
* Core Autocomplete functions for ILance.
*
* @package      iLance\Javascript\AutoComplete
* @version	4.0.0.8059
* @author       ILance
*/
function iLance_AutoComplete(elem, divname)
{
	var me = this;
	this.clearField = false;
	this.minLength = 2;
	this.elem = fetch_js_object(elem);
	this.highlighted = -1;
	this.arrItens = new Array();
	this.ajaxTarget = AJAXURL + '?do=autocomplete' + '&s=' + ILSESSION + '&token=' + ILTOKEN;
	this.chooseFunc = null;
	this.div = fetch_js_object(divname);
	this.hideSelects = false;
	var TAB = 9;
	var ESC = 27;
	var KEYUP = 38;
	var KEYDN = 40;
	var ENTER = 13;
	me.elem.setAttribute('autocomplete', 'off');
	this.ajaxReq = createRequest();
	me.elem.onkeydown = function(ev)
	{
		var key = me.getKeyCode(ev);
		switch (key)
		{
			case TAB:
			case ENTER:
				if (me.highlighted.id != 'undefined')
				{
					me.acChoose(me.highlighted.id);
				}
				me.hideDiv();
				return false; // disables enter key
			break;
			case ESC:
				me.hideDiv();
				return false;
			break;
			case KEYUP:
				me.changeHighlight('up');
				return false;
			break;
			case KEYDN:
				me.changeHighlight('down');
				return false;
			break;
		}
	}
	me.elem.onkeyup = function(ev) 
	{
		var key = me.getKeyCode(ev);
		switch(key)
		{
			case TAB:
			case ESC:
			case KEYUP:
			case KEYDN:
				return;
			case ENTER:
				return false;
				break;
			default:
				me.ajaxReq.abort();
				if (me.elem.value.length >= me.minLength)
				{
					if (me.ajaxReq != undefined)
					{
						me.ajaxReq.open("POST", me.ajaxTarget, true);
						me.ajaxReq.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
						me.ajaxReq.onreadystatechange = me.acResult;
						var param = 'q=' + me.elem.value;
						me.ajaxReq.send(param);
					}
				}
				else
				{
					return;	
				}
				me.highlighted = '';
		}
	}
	me.elem.onblur = function()
	{
		me.hideDiv();
	}
	this.setElemValue = function()
	{
		var a = me.highlighted.firstChild;
		me.elem.value = a.innerTEXT;
	}
	this.highlightThis = function(obj, yn)
	{
		if (yn == 'y')
		{
			me.highlighted.className = '';
			me.highlighted = obj;
			me.highlighted.className = 'selected';
			me.setElemValue(obj);
		}
		else
		{
			obj.className = '';
			me.highlighted = '';
		}
	}
	this.changeHighlight = function(way)
	{
		if (me.highlighted != '' && me.highlighted != null)
		{
			me.highlighted.className = '';
			switch (way)
			{
				case 'up':
					if (me.highlighted.parentNode.firstChild == me.highlighted)
					{
						me.highlighted = me.highlighted.parentNode.lastChild;
					}
					else
					{
						me.highlighted = me.highlighted.previousSibling;
					}
				break;
				case 'down':
					if (me.highlighted.parentNode.lastChild == me.highlighted)
					{
						me.highlighted = me.highlighted.parentNode.firstChild;
					}
					else
					{
						me.highlighted = me.highlighted.nextSibling;
					}
				break;
				
			}
			me.highlighted.className = 'selected';
			me.setElemValue();
		}
		else
		{
			switch (way)
			{
				case 'up':
					me.highlighted = me.div.firstChild.lastChild;
				break;
				case 'down':
					me.highlighted = me.div.firstChild.firstChild;
				break;
				
			}
			me.highlighted.className = 'selected';
			me.setElemValue();
		}		
	}
	this.acResult = function()
	{
		if (me.ajaxReq.readyState == 4)
		{
			me.showDiv()
			var xmlRes = me.ajaxReq.responseXML;
			if (xmlRes == undefined)
			{
				return false;
			}
			var itens = xmlRes.getElementsByTagName('item');
			var itCnt = itens.length;
			me.div.innerHTML = '';
			var ul = document.createElement('div');
			me.div.appendChild(ul);
			if (itCnt > 0)
			{
				for (i = 0; i < itCnt; i++)
				{
					me.arrItens[itens[i].getAttribute('id')] = new Array();
					me.arrItens[itens[i].getAttribute('id')]['label'] = itens[i].getAttribute('label');
					me.arrItens[itens[i].getAttribute('id')]['text'] = itens[i].getAttribute('text');
					me.arrItens[itens[i].getAttribute('id')]['searchmode'] = itens[i].getAttribute('searchmode');
					me.arrItens[itens[i].getAttribute('id')]['flabel'] = itens[i].getAttribute('flabel');
					var li = document.createElement('div');
					li.id = itens[i].getAttribute('id');
					li.onmouseover = function()
					{
					       this.className = 'selected';
					       me.highlightThis(this, 'y')
					}
					li.onmouseout = function()
					{
					       this.className = '';
					       me.highlightThis(this, 'n')
					}
					li.onmousedown = function()
					{
					       me.acChoose(this.id);
					       me.hideDiv();
					       return false;
					}
					var a = document.createElement('a');
					a.href = '#';
					a.onclick = function()
					{
						return false;
					}
					a.innerHTML = unescape(itens[i].getAttribute("label"));
					if (itens[i].getAttribute("text") != null)
					{
						a.innerTEXT = unescape(itens[i].getAttribute("text"));
					}
					else
					{
						a.innerTEXT = unescape(itens[i].getAttribute("label"));
					}
					li.appendChild(a);
					ul.appendChild(li);	
				}
			}
			else
			{
				me.hideDiv();	
			}
		}
	}
	this.acChoose = function (id)
	{
		me.hideDiv();
		if (this.clearField)
		{
			me.elem.value = '';
		}
		else
		{
			me.elem.value = unescape(me.arrItens[id]['text']);
			fetch_js_object('searchmode').value = unescape(me.arrItens[id]['searchmode']);
			fetch_js_object('globalsearch').submit();
		}
	}
	this.positionDiv = function()
	{
		var el = this.elem;
		var x = 0;
		var y = el.offsetHeight;
		while (el.offsetParent && el.tagName.toUpperCase() != 'BODY')
		{
			x += el.offsetLeft;
			y += el.offsetTop;
			el = el.offsetParent;
		}
		x += el.offsetLeft;
		y += el.offsetTop;
		if (!checkmac)
		{
			this.div.style.left = (x + 6) + 'px'; // add 6 pixels to browsers not using mac
		}
		else
		{
			this.div.style.left = x + 'px';
		}
		this.div.style.top = y + 'px';
		
	};
	this.hideDiv = function()
	{
		me.highlighted = '';
		me.div.style.display = 'none';
		me.handleSelects('');
	}
	this.showDiv = function()
	{
		me.highlighted = '';
		me.positionDiv();
		me.handleSelects('none');
		me.div.style.display = 'block';
	}
	this.handleSelects = function(state)
	{
		if (!me.hideSelects) return false;
		var selects = document.getElementsByTagName('SELECT');
		for (var i = 0; i < selects.length; i++)
		{
			selects[i].style.display = state;
		}
	}
	this.getKeyCode = function(ev)
	{
		if (ev)			//Moz
		{
			return ev.keyCode;
		}
		if (window.event)	//IE
		{
			return window.event.keyCode;
		}
	};
	this.getEventSource = function(ev)
	{
		if (ev)			//Moz
		{
			return ev.target;
		}
	
		if (window.event)	//IE
		{
			return window.event.srcElement;
		}
	};
	this.cancelEvent = function(ev)
	{
		if (ev)			//Moz
		{
			ev.preventDefault();
			ev.stopPropagation();
		}
		if (window.event)	//IE
		{
			window.event.returnValue = false;
		}
	}
}
function createRequest()
{
	try
	{
		var request;
		request = new XMLHttpRequest();
	}
	catch (trymicrosoft)
	{
		try
		{
			request = new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch (othermicrosoft)
		{
			try
			{
				request = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (failed)
			{
				request = false;
			}
		}
	}
	if (!request)
	{
	}
	else
	{
		return request;
	}
}
var iL_AutoComplete;
iL_AutoComplete = new iLance_AutoComplete('search_keywords_id', 'search_autocomplete');