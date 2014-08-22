/**
* Core javascript pop-out menu functions within ILance.
*
* @package      iLance\Javascript\Menu
* @version	4.0.0.8059
* @author       ILance
*/
function v3lib(){}
v3lib.prototype.getElementById = function(id)
{
	if (document.getElementById)
	{
		return document.getElementById(id);
	}
	else if (document.all)
	{
		return document.all[id];
	}
	else if (document.layers)
	{
		return document.layers[id];
	}
	else
	{
		return false;
	}
}
v3lib.prototype.getElementsByTagName = function(parentobj, tagname)
{
	var elements = false;
	if (typeof parentobj.getElementsByTagName != 'undefined')
	{
		elements = parentobj.getElementsByTagName(tagname);
	}
	else if (parentobj.all && parentobj.all.tags)
	{
		elements = parentobj.all.tags(tagname);
	}
	return elements;
}
v3lib.prototype.in_array = function(thearray, needle)
{
	var bool = false;
	for (var i = 0; i < this.sizeof(thearray); i++)
	{
		if (thearray[i] == needle)
		{
			bool = true;
		}
	}
	return bool;
}
v3lib.prototype.array_key = function(thearray, needle)
{
	var key = 0;
	for (var i = 0; i < this.sizeof(thearray); i++)
	{
		if (thearray[i] == needle)
		{
			key = i;
		}
	}
	return key;
}
v3lib.prototype.unset = function(thearray, value)
{
	for (var i = 0; i < this.sizeof(thearray); i++)
	{
		if (thearray[i] == value)
		{
			delete thearray[i];
		}
	}
	return true;
}
v3lib.prototype.array_push = function(thearray, value)
{
	thearray[this.sizeof(thearray)] = value;
}
v3lib.prototype.sizeof = function(thearray)
{
	array_length = 0;
	if (thearray != null && thearray != 'undefined')
	{
		for (i = 0; i < thearray.length; i++)
		{
			if ((thearray[i] == "undefined") || (thearray[i] == "") || (thearray[i] == null))
			{
				return i;
			}
		}
		array_length = thearray.length;
	}
	else
	{
		array_length = 0;
	}
	return array_length;
}
v3lib.prototype.setIndex = function(element, array)
{
	var temp = this.getElementById(array);
	if (temp)
	{
		temp.selectedIndex = this.getSelectedIndex(element, temp);
	}
}
v3lib.prototype.setIndices = function(values_array, select) {
	var temp = this.getElementById(select);
	if (temp)
	{
		if (this.sizeof(values_array) > 0)
		{
			for (var i = 0; i < this.sizeof(temp.options); i++)
			{
				if (this.in_array(values_array, temp.options[i].value))
				{
					temp.options[i].selected = true;
				}
			}
		}
	}
}
v3lib.prototype.setRadio = function(value, name)
{
	var inputs = this.getElementsByTagName(document, 'input');
	if (inputs)
	{
		for (var x = 0; x < this.sizeof(inputs); x++)
		{
			if (inputs[x])
			{
				if (inputs[x].name == name)
				{
					if (inputs[x].value == value)
					{
						inputs[x].checked = true;
					}
					else
					{
						inputs[x].checked = false;
					}
				}
			}
		}
	}
	return true;
}
v3lib.prototype.setCheckbox = function(value, id)
{
	var input = this.getElementById(id);
	var check = false;
	if (input)
	{
		check = (value || value > 0) ? true : false;
	}
	input.checked = check;
}
v3lib.prototype.getSelectedIndex = function(element, array)
{
	var pos = 0;
	if (array)
	{
		for(var i = 0; i < this.sizeof(array); i++)
		{
			if (array[i].value == element)
			{
				pos = i;
			}
		}
	}
	return pos;
}
v3lib.prototype.top = function(obj)
{
	var postop = 0;
	while (obj && obj != null)
	{
		postop	+= obj.offsetTop;
		obj = obj.offsetParent;
	}
	return postop;
}
v3lib.prototype.left = function(obj)
{
	var posleft = 0;
	if(obj)
	{
		posleft = obj.offsetLeft;
		while ((obj = obj.offsetParent) != null)
		{
			posleft += obj.offsetLeft;
		}
	}
	return posleft;
}
v3lib.prototype.bottom = function(obj)
{
	return (this.top(obj) + this.height(obj));
}
v3lib.prototype.right = function(obj)
{
	return (this.left(obj) + this.width(obj));
}
v3lib.prototype.width = function(obj)
{
	var objwidth = 0;
	if (obj)
	{
		objwidth = obj.offsetWidth;
	}
	return objwidth;
}
v3lib.prototype.height = function(obj)
{
	var objheight = 0;
	if(obj)
	{
		objheight = obj.offsetHeight;
	}
	return objheight;
}
v3lib.prototype.overlaps = function(over, under)
{
	var does_overlap = true;
	if(this.left(under) > this.right(over)) does_overlap = false;
	if(this.right(under) < this.left(over)) does_overlap = false;
	if(this.top(under) > this.bottom(over)) does_overlap = false;
	if(this.bottom(under) < this.top(over)) does_overlap = false;
	return does_overlap;
}
v3lib.prototype.forceCursor = function(obj)
{
	if (obj)
	{
		try
		{
			obj.style.cursor = 'pointer';
		}
		catch(e)
		{
			obj.style.cursor = 'hand';
		}
	}
}
var d = new v3lib()
var open_menu = false;
var hidden_selects = new Array()
var tempX = 0;
var tempY = 0;
var use_click = true;
function menu_init(link_id, menu_id)
{
	var menu = d.getElementById(menu_id);
	var link = d.getElementById(link_id);
	
	if (menu && link) 
	{
		menu.style.display = 'none';
		link.unselectable = true;
		d.forceCursor(link);
		link.onclick = function() 
		{
			menu.link_id = this.id;
			if (menu.style.display == 'none')
			{
				openmenu(menu, this);
			} 
			else 
			{
				closemenu(menu);
			}
		}
		
		link.onmouseover = function() 
		{
			if (open_menu) 
			{
				if (open_menu.link_id != this.id) 
				{
					closemenu(open_menu);
					openmenu(menu, this);
				}
			}

			if (!use_click) 
			{
				menu.link_id = this.id;
				if (menu.style.display == 'none') 
				{
					openmenu(menu, this);
				} 
				else 
				{
					if (menu.link_id != open_menu.link_id) 
					{
						closemenu(menu);
					}
				}
			}
		}
		
		if (!use_click) 
		{
			link.onmouseout = function(event) 
			{
				hidemenuclick(event);
			}
			
			menu.onmouseout = function(event) 
			{
				hidemenuclick(event);
			}
		}
		highlightmenurows(menu);
	}
}
function openmenu(menu, link) 
{
	if (menu && link) 
	{
		if (menu.style.display == 'none') 
		{
			
			menu.style.display = 'block';
			menu_positions(menu, link);
			selects = d.getElementsByTagName(document, 'select');
			if (selects) 
			{
				for (var s = 0; s < d.sizeof(selects); s++) 
				{
					if (selects[s]) 
					{
						/* If the menu overlaps the select menu, hide the select */
						if (d.overlaps(menu, selects[s]))
						{
							selects[s].style.display = 'none';
							d.array_push(hidden_selects, selects[s]);
						}
					}
				}
			}
			
			open_menu = menu;
		} 
		else 
		{
			
			close_menu(menu);
		}
	}
	return true;
}
function closemenu(menu)
{
	if (menu) 
	{
		menu.style.display = 'none';
		open_menu = false;
		if (d.sizeof(hidden_selects) > 0) 
		{
			for (var i = 0; i < d.sizeof(hidden_selects); i++) 
			{
				hidden_selects[i].style.display = 'block';
			}
			hidden_selects = new Array()
		}
	}
}
function hidemenuclick(event)
{
	if (event) 
	{
		if (navigator.appName != "Netscape") 
		{
			tempX = window.event.clientX + window.document.body.scrollLeft;
			tempY = window.event.clientY + window.document.body.scrollTop;
			
		} 
		else 
		{
			tempX = event.pageX;
			tempY = event.pageY;
		}
		if (tempX <= 0) { tempX = 0 }
		if (tempY <= 0) { tempY = 0 }
	}
	
	if (open_menu)
	{
		open_menulink = d.getElementById(open_menu.link_id);
		keep_open = true;
		
		if (tempY > d.bottom(open_menu))
		{
			keep_open = false;
		}
		if (tempY < d.top(open_menulink))
		{
			keep_open = false;
		}
		if (tempX < d.left(open_menu))
		{
			keep_open = false;
		}
		if (tempX > d.right(open_menu))
		{
			keep_open = false;	
		}
		if (!keep_open) 
		{
			//open_menu.style.display = 'none';
			//open_menu = false;
		}
	}
}
function highlightmenurows(menu)
{
	if (menu) 
	{
		var rows = d.getElementsByTagName(menu, 'td');
		if (rows) 
		{
			for (var i = 0; i < d.sizeof(rows); i++) 
			{
				//if (rows[i].className == 'tablehead_alt')
				if (rows[i].bgColor == '#dfdfdf')
				{
					rows[i].onmouseover = function() 
					{
						//this.className = 'tablehead_alt2';
						this.bgColor = '#aaaaaa';
					}

					rows[i].onmouseout = function() 
					{
						//this.className = 'tablehead_alt';
						this.bgColor = '#dfdfdf';
					}
				}
			}
		}
	}
}
function menu_positions(menu, link)
{
	if (menu && link) 
	{
		menu.style.position = 'absolute';
		force_right = parseInt(d.left(link) + d.width(menu)) >= document.body.clientWidth ? true : false;
		menu.style.left = (force_right ? (d.left(link) - (d.width(menu) - d.width(link))) : d.left(link)) + 'px';
		menu.style.top = parseInt(d.top(link) + d.height(link) + 3) + 'px';
	}
}
if (document.addEventListener) 
{
	document.addEventListener('click', hidemenuclick, false);
	if (!use_click)
		document.addEventListener('mousemove', hidemenuclick, false);
} 
else if(document.attachEvent) 
{
	document.attachEvent('onclick', hidemenuclick);
	if (!use_click)
		document.attachEvent('onmousemove', hidemenuclick);
}