/**
* Core inline javascript functions within ILance.
*
* @package      iLance\Javascript\Inline
* @version	4.0.0.8059
* @author       ILance
*/
function iL_Inline(varname, cbtype, formobjid, submitname, ilancecookie)
{
	this.varname = varname;
	if (cbtype.toLowerCase() == 'service')
	{
		this.cbtype = 'service';	
	}
	else if (cbtype.toLowerCase() == 'product')
	{
		this.cbtype = 'product';
	}
	else if (cbtype.toLowerCase() == 'experts')
	{
		this.cbtype = 'experts';
	}
	else if (cbtype.toLowerCase() == 'updates')
	{
		this.cbtype = 'updates';
	}
	else if (cbtype.toLowerCase() == 'members')
	{
		this.cbtype = 'members';
	}
	else if (cbtype.toLowerCase() == 'escrow1')
	{
		this.cbtype = 'escrow1';
	}
	else if (cbtype.toLowerCase() == 'escrow2')
	{
		this.cbtype = 'escrow2';
	}
	else if (cbtype.toLowerCase() == 'pboard1')
	{
		this.cbtype = 'pboard1';
	}
	else if (cbtype.toLowerCase() == 'pboard2')
	{
		this.cbtype = 'pboard2';
	}
	else if (cbtype.toLowerCase() == 'undage1')
	{
		this.cbtype = 'undage1';
	}
	else if (cbtype.toLowerCase() == 'undage2')
	{
		this.cbtype = 'undage2';
	}
	else if (cbtype.toLowerCase() == 'buynow2')
	{
		this.cbtype = 'buynow2';
	}
	else if (cbtype.toLowerCase() == 'images2')
	{
		this.cbtype = 'images2';
	}
	else if (cbtype.toLowerCase() == 'freesh2')
	{
		this.cbtype = 'freesh2';
	}
	else if (cbtype.toLowerCase() == 'laslot2')
	{
		this.cbtype = 'laslot2';
	}
	else if (cbtype.toLowerCase() == 'budget1')
	{
		this.cbtype = 'budget1';
	}
	else if (cbtype.toLowerCase() == 'islogd1')
	{
		this.cbtype = 'islogd1';
	}
	else if (cbtype.toLowerCase() == 'images3')
	{
		this.cbtype = 'images3';
	}
	else if (cbtype.toLowerCase() == 'portfo1')
	{
		this.cbtype = 'portfo1';
	}
	else if (cbtype.toLowerCase() == 'servicebids')
	{
		this.cbtype = 'servicebids';
	}
	this.formobj = fetch_js_object(formobjid);
	this.submitname = submitname;
	if (typeof ilancecookie != 'undefined')
	{
		this.ilancecookie = ilancecookie;
	}
	else
	{
		this.ilancecookie = 'ilance_inline';
	}
	if (this.cbtype == 'service')
	{
		this.list = 'service_';
	}
	else if (this.cbtype == 'product')
	{
		this.list = 'product_';
	}
	else if (this.cbtype == 'experts')
	{
		this.list = 'experts_';
	}
	else if (this.cbtype == 'updates')
	{
		this.list = 'updates_';
	}
	else if (this.cbtype == 'members')
	{
		this.list = 'members_';
	}
	else if (this.cbtype == 'escrow1')
	{
		this.list = 'escrow1_';
	}
	else if (this.cbtype == 'escrow2')
	{
		this.list = 'escrow2_';
	}
	else if (this.cbtype == 'pboard1')
	{
		this.list = 'pboard1_';
	}
	else if (this.cbtype == 'pboard2')
	{
		this.list = 'pboard2_';
	}
	else if (this.cbtype == 'undage1')
	{
		this.list = 'undage1_';
	}
	else if (this.cbtype == 'undage2')
	{
		this.list = 'undage2_';
	}
	else if (this.cbtype == 'buynow2')
	{
		this.list = 'buynow2_';
	}
	else if (this.cbtype == 'images2')
	{
		this.list = 'images2_';
	}
	else if (this.cbtype == 'freesh2')
	{
		this.list = 'freesh2_';
	}
	else if (this.cbtype == 'laslot2')
	{
		this.list = 'laslot2_';
	}
	else if (this.cbtype == 'budget1')
	{
		this.list = 'budget1_';
	}
	else if (this.cbtype == 'islogd1')
	{
		this.list = 'islogd1_';
	}
	else if (this.cbtype == 'images3')
	{
		this.list = 'images3_';
	}
	else if (this.cbtype == 'portfo1')
	{
		this.list = 'portfo1_';
	}
	else if (this.cbtype == 'servicebids')
	{
		this.list = 'servicebid_';
	}
	else if (cbtype.toLowerCase() == 'single')
	{
		this.list = 'single_';	
	}
	this.cookieids = null;
	this.cookiearray = new Array();
	this.init = function(elements)
	{
		for (i = 0; i < elements.length; i++)
		{
			if (this.is_cookie_in_list(elements[i]))
			{
				elements[i].inline_id = this.varname;
				elements[i].onclick = inline_checkbox_onclick;
			}
		}
		this.cookiearray = new Array();
		if (this.fetch_ids())
		{
			for (i in this.cookieids)
			{
				if (this.cookieids[i] != '')
				{
					//if (this.checkbox == fetch_js_object(this.list + this.cookieids[i]))
					//{
						//this.checkbox.checked = true;
					//}
					this.cookiearray[this.cookiearray.length] = this.cookieids[i];
				}
			}
		}
		this.set_button_counters();
	}
	this.fetch_ids = function()
	{
		this.cookieids = fetch_js_cookie(this.ilancecookie + this.cbtype);
		if (this.cookieids != null && this.cookieids != '')
		{
			this.cookieids = this.cookieids.split('-');
			if (this.cookieids.length > 0)
			{
				return true;
			}
		}
		return false;
	}
	this.toggle = function(checkbox)
	{
		this.save(checkbox.id.substr(8), checkbox.checked);
	}
	this.save = function(checkboxid, checked)
	{
		this.cookiearray = new Array();
		if (this.fetch_ids())
		{
			for (i in this.cookieids)
			{
				if (this.cookieids[i] != checkboxid && this.cookieids[i] != '')
				{
					this.cookiearray[this.cookiearray.length] = this.cookieids[i];
				}
			}
		}
		if (checked)
		{
			this.cookiearray[this.cookiearray.length] = checkboxid;
		}
		this.set_button_counters();
		this.set_cookie();
		return true;
	}
	this.set_cookie = function()
	{
		expires = new Date();
		expires.setTime(expires.getTime() + 3600000);
		update_js_cookie(this.ilancecookie + this.cbtype, this.cookiearray.join('-'), expires);
	}
	this.is_cookie_in_list = function(obj)
	{
		return (obj.type == 'checkbox' && obj.id.indexOf(this.list) == 0 && (obj.disabled == false || obj.disabled == 'undefined'));
	}
	this.check_all = function(checked, itemtype, caller)
	{
		if (typeof checked == 'undefined')
		{
			checked = this.formobj.allbox.checked;
		}
		this.cookiearray = new Array();
		if (this.fetch_ids())
		{
			for (i in this.cookieids)
			{
				if (!fetch_js_object(this.list + this.cookieids[i]))
				{
					this.cookiearray[this.cookiearray.length] = this.cookieids[i]
				}
			}
		}
		counter = 0;
		for (var i = 0; i < this.formobj.elements.length; i++)
		{
			if (this.is_cookie_in_list(this.formobj.elements[i]))
			{
				elm = this.formobj.elements[i];
				if (typeof itemtype != 'undefined')
				{
					if (elm.value & itemtype)
					{
						elm.checked = checked;
					}
					else
					{
						elm.checked = !checked;
					}
				}
				else if (checked == 'invert')
				{
					elm.checked = !elm.checked;
				}
				else
				{
					elm.checked = checked;
				}
				if (elm.checked)
				{
					this.cookiearray[this.cookiearray.length] = elm.id.substring(8);
				}
			}
		}
		this.set_button_counters();
		this.set_cookie();
		return true;
	}
	this.set_button_counters = function()
	{
		if (this.submitname != '')
		{
			if (fetch_js_object('inlinebutton'))
			{
				//fetch_js_object('inlinebutton').value = ((LTR == 1) ? this.submitname + " (" + this.cookiearray.length + ")" : "(" + this.cookiearray.length + ") " + this.submitname);
				fetch_js_object('inlinebutton').value = this.submitname;
			}
			if (fetch_js_object('inlinebutton2'))
			{
				//fetch_js_object('inlinebutton2').value = ((LTR == 1) ? this.submitname + " (" + this.cookiearray.length + ")" : "(" + this.cookiearray.length + ") " + this.submitname);
				fetch_js_object('inlinebutton2').value = this.submitname;
			}	
		}
	}
	this.init(this.formobj.elements);
}
function inline_checkbox_onclick(e)
{
	var inlineobj = eval(this.inline_id);
	inlineobj.toggle(this);
}