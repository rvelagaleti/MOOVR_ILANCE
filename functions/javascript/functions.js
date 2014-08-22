/**
* Core Javascript functions for ILance.
*
* @package      iLance\Javascript\Core
* @version	4.0.0.8059
* @author       ILance
*/
/**
* Browser Compatibility
*/
var DOMTYPE = '';
if (document.getElementById)
{
	DOMTYPE = 'std';
}
else if (document.layers)
{
	DOMTYPE = 'ns4';
}
else if (document.all)
{
	DOMTYPE = 'ie4';
}

if (typeof ILSESSION == 'undefined')
{
	ILSESSION = '';
}

/**
* Fetch Browser Agent
*/
var AGENT = navigator.userAgent.toLowerCase();
var checkopera = (AGENT.indexOf('opera') != -1);
var checksaf = ((AGENT.indexOf('safari') != -1) || (navigator.vendor.toLowerCase().indexOf('apple')));
var checkwebtv = (AGENT.indexOf('webtv') != -1);
var checkie = ((AGENT.indexOf('msie') != -1) && (!checkopera) && (!checksaf) && (!checkwebtv));
var checkie4 = ((checkie) && (AGENT.indexOf('msie 4.') != -1));
var checkie7 = ((checkie) && (AGENT.indexOf('msie 7.') != -1));
var checkmoz = ((navigator.product == 'Gecko') && (!checksaf));
var checkkon = (AGENT.indexOf('konqueror') != -1);
var checkns = ((AGENT.indexOf('compatible') == -1) && (AGENT.indexOf('mozilla') != -1) && (!checkopera) && (!checkwebtv) && (!checksaf));
var checkns4 = ((checkns) && (parseInt(navigator.appVersion) == 4));
var checkmac = (navigator.vendor.toLowerCase().indexOf('apple') != -1);
var checkchrome = (AGENT.indexOf('chrome') != -1);
/**
* Mobile Browser Agents
*/
var checkiphone = (AGENT.indexOf('iphone') != -1);
var checkblackberry = (AGENT.indexOf('blackberry') != -1);

/**
* Define slideshow rotation delay on item listing page
*/
var rotate_delay = 5000;
var current = 0;
var attw = null;
var drww = null;
var checkobj = null;
var ilobjects = new Array();
var category_popup_timer = null;

/**
* AJAX and Regular Expressions Compatibility
*/
var REGEXP_Compatible = (window.RegExp) ? true : false;
var AJAX_Compatible = false;

/**
* Other variables used in this script
*/
cbchecked = false;
cbchecked2 = false;

/**
* Core Functions
*/
function fetch_session_id()
{
	return (ILSESSION == '' ? '' : ILSESSION.substr(2, 32));
}
function fetch_js_object(idname)
{
	if (document.getElementById)
	{
		return document.getElementById(idname);
	}
	else if (document.all)
	{
		return document.all[idname];
	}
	else if (document.layers)
	{
		return document.layers[idname];
	}
	else
	{
		return null;
	}
}
function fetch_js_cookie(name) 
{
	v3cookiename = name + '=';
	v3cookiesize = document.cookie.length;
	v3cookiestart = 0;
	while (v3cookiestart < v3cookiesize) 
	{
		v3cookievalue = v3cookiestart + v3cookiename.length;
		if (document.cookie.substring(v3cookiestart, v3cookievalue) == v3cookiename) 
		{
			var v3cookievalue2 = document.cookie.indexOf (';', v3cookievalue);
			if (v3cookievalue2 == -1) 
			{
				v3cookievalue2 = v3cookiesize;
			}
			return unescape(document.cookie.substring(v3cookievalue, v3cookievalue2));
		}
		v3cookiestart = document.cookie.indexOf(' ', v3cookiestart) + 1;
		if (v3cookiestart == 0) 
		{
			break;
		}
	}
	return null;
}
function update_js_cookie(name, value, expires) 
{
	if (!expires) 
	{
		expires = new Date();
	}
	document.cookie = name + "=" + escape(value) + "; expires=" + expires.toGMTString() +  "; path=/";
}
function update_js_collapse_cookie(objid, setcookiedata, cookiename) 
{
	var cookiedata = fetch_js_cookie(cookiename);
	var cookietemp = new Array();
	if (cookiedata != null) 
	{
		cookiedata = cookiedata.split('|');
		for (i in cookiedata) 
		{
			if (cookiedata[i] != objid && cookiedata[i] != '') 
			{
				cookietemp[cookietemp.length] = cookiedata[i];
			}
		}
	}
	if (setcookiedata) 
	{
		cookietemp[cookietemp.length] = objid;
	}
	cookieexpire = new Date();
	cookieexpire.setTime(cookieexpire.getTime() + (500 * 86400 * 365));
	update_js_cookie(cookiename, cookietemp.join("|"), cookieexpire);
}
function toggle(objid) 
{
	if (!REGEXP_Compatible)
	{
		return false;
	}
	obj = fetch_js_object('collapseobj_' + objid);
	img = fetch_js_object('collapseimg_' + objid);
	if (!obj)
	{
		if (img)
		{
			img.style.display = 'none';
		}
		
		return false;
	}
	// #### our data is collapsed lets show it #############################
	if (obj.style.display == 'none') 
	{
		obj.style.display = '';
		
		// #### tell cookie to remove this obj from the list so php can show it
		update_js_collapse_cookie(objid, false, ILNAME + 'collapse');
		
		if (img) 
		{
			// #### flip the collapsed icon to expanded state
			img_re = new RegExp("_collapsed\\.gif$");
			img.src = img.src.replace(img_re, '.gif');
		}
	}
	// #### our data is expanded lets hide it ##############################
	else 
	{
		obj.style.display = 'none';
		
		// #### tell cookie to add this obj to the list so php can hide it
		update_js_collapse_cookie(objid, true, ILNAME + 'collapse');
		
		if (img) 
		{
			// #### flip the expanded icon collapsed state
			img_re = new RegExp("\\.gif$");
			img.src = img.src.replace(img_re, '_collapsed.gif');
		}
	}
	return false;
}
function agreesubmit(el) 
{
	checkobj = el
	if (document.all || document.getElementById) 
	{
		for (i = 0; i < checkobj.form.length; i++)
		{
			var tempobj = checkobj.form.elements[i]
			if (tempobj.type.toLowerCase() == 'submit')
				tempobj.disabled=!checkobj.checked
		}
	}
}
function defaultagree(el) 
{
	if (!document.all && !document.getElementById) 
	{
		if (window.checkobj && checkobj.checked)
		{
			return true
		}
		else 
		{
			alert_js(phrase['_please_read_accept_terms_to_submit_form'])
			return false
		}
	}
}
function confirm_js(message)
{
	grayscale = document.getElementsByTagName("html");
	grayscale[0].style.filter = "progid:DXImageTransform.Microsoft.BasicImage(grayscale=1)";
	if (confirm(message))
	{
		return true;
	}
	else
	{
		grayscale[0].style.filter = "";
		return false;
	}
}
function alert_js(message)
{
	grayscale = document.getElementsByTagName("html");
	grayscale[0].style.filter = "progid:DXImageTransform.Microsoft.BasicImage(grayscale=1)";
	if (alert(message))
	{
		return true;
	}
	else
	{
		grayscale[0].style.filter = "";
		return false;
	}	
}
function log_out() 
{
	grayscale = document.getElementsByTagName("html");
	grayscale[0].style.filter = "progid:DXImageTransform.Microsoft.BasicImage(grayscale=1)";
	if (confirm_js(phrase['_are_you_ready_to_log_out']))
	{
		return true;
	}
	else
	{
		grayscale[0].style.filter = "";
		return false;
	}
}
function showImage(imagename, imageurl, errors)
{
	document[imagename].src = imageurl;
	if (!haveerrors && errors)
	{
		haveerrors = errors;
		if (obj = fetch_js_object('inlineerror'))
		{
			obj.innerHTML = '<img name="inlineerror" src="' + IMAGEBASE + 'icons/fieldempty.gif" width="21" height="13" border="0" alt="" />';
		}
	}
}
function noenter()
{
	return !(window.event && window.event.keyCode == 13); 
}
function createWindow(u, n, w, h, r)
{
	args = 'width=' + w + ',height=' + h + ',resizable=yes,scrollbars=yes,status=0';
	remote = window.open(u, n, args);
	if (remote != null) 
	{
		if (remote.opener == null)
		{
			remote.opener = self;
		}
	}
	if (r == 1)
	{
		return remote;
	}
}
function Attach(url) 
{
	if (!attw || attw.closed)
	{
		browsername=navigator.appName;
		if (browsername.indexOf("Netscape")!=-1) 
		{
			attw = createWindow(url, 'attachwin', 480, 450, 1);
		}
		else
		{
			attw = createWindow(url, 'attachwin', 470, 450, 1);
		}
	}
	attw.focus();
}
function toggle_tr(target)
{
	obj = fetch_js_object(target);
	obj.style.display = (obj.style.display == 'none') ? 'inline' : 'none';
}
function toggle_hide_tr(target)
{
	obj = fetch_js_object(target);
	obj.style.display = 'none';
}
function toggle_show_tr(target)
{
	obj = fetch_js_object(target);
	obj.style.display = 'inline';
}
function toggle_more(target, showmoreid, moretext, lesstext, showmoreicon)
{
	obj = fetch_js_object(target);
	if (obj.style.display == 'none') 
	{
		obj.style.display = '';
		update_js_collapse_cookie(target, true, ILNAME + 'collapse');
	}
	else 
	{
		obj.style.display = 'none';
		update_js_collapse_cookie(target, false, ILNAME + 'collapse');
	}
	obj2 = fetch_js_object(showmoreid);
	obj2.style.fontweight = 'bold';
	obj2.innerHTML = (obj.style.display == 'none')
		? moretext
		: lesstext;
	
	obj3 = fetch_js_object(showmoreicon);
	obj3.src = (obj.style.display == 'none')
		? IMAGEBASE + 'icons/arrowdown2.gif'
		: IMAGEBASE + 'icons/arrowup2.gif';
	
	return false;
}
function toggle_paid(target) 
{
	obj = fetch_js_object(target);
	obj.style.display = (obj.style.display == 'none') ? 'inline' : 'none'; 
}
function toggle_free(target) 
{
	obj = fetch_js_object(target);
	if (obj.style.display == 'inline') 
	{
		obj.style.display = 'none';
	}
}
function toggle_bid_row(objid)
{
	//x4uuwaxhbz = fetch_js_object(objid);
	//x4uuwaxhbz.style.display = (x4uuwaxhbz.style.display == 'none') ? '' : 'none';
}
function toggle_hide(target)
{
	obj = fetch_js_object(target);
	obj.style.display = (obj.style.display == 'none') ? 'none' : 'none';
}
function toggle_show(target)
{
	obj = fetch_js_object(target);
	obj.style.display = (obj.style.display == 'none') ? '' : '';
}
function trim(field)
{
	value = field;
	while (value.charAt(value.length - 1) == " ") 
	{
		value = value.substring(0, value.length-1);
	} 
	while (value.substring(0, 1) == " ") 
	{
		value = value.substring(1, value.length);
	}
	return value;
}
function popUP(mypage, myname, w, h, scroll, titlebar) 
{
	var winl = (screen.width - w) / 2;
	var wint = (screen.height - h) / 2;
	winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scroll+',resizable'
	win = window.open(mypage, myname, winprops)
	if (parseInt(navigator.appVersion) >= 4) 
	{
		win.window.focus();
	}
}
function urlswitch(styleobj, _type)
{
	var themeid = styleobj.options[styleobj.selectedIndex].value;
	if (themeid == '')
	{
		return;
	}
	var url = new String(window.location);
	var fragment = new String('');
	url = url.split('#');
	if (url[1])
	{
		fragment = '#' + url[1];
	}
	url = url[0];
	if (_type == 'dostyle')
	{
		if (url.indexOf('styleid=') != -1)
		{
			re = new RegExp("styleid=\\d+&?");
			url = url.replace(re, '');
		}
	}
	else
	{
	    if (url.indexOf('language=') != -1)
	    {
		    re = new RegExp("language=\\D+&?");
		    url = url.replace(re, '');
	    }
	}
	if (url.indexOf('?') == -1)
	{
		url += '?';
	}
	else
	{
		endchar = url.substr(url.length - 1);
		if (endchar != '&' && endchar != '?')
		{
			url += '&';
		}
	}
	if (_type == 'dostyle')
	{
		window.location = url + 'styleid=' + themeid + fragment;
	}
	else
	{
		window.location = url + 'language=' + themeid + fragment;
	}
}
function textcount(field, countfield, maxlimit) 
{
	if (field.value.length > maxlimit)
	{
		field.value = field.value.substring(0, maxlimit);
	}
	else
	{
		countfield.value = maxlimit - field.value.length;
	}
}
function populate_countries_pulldown(cboCountry, cboState, sDefaultCountry)
{
	var sDefault, sCountry;
	var selectOption = -1;
	cboCountry.options.length = 0;
	for (i = 0; i < sCountryString.split("|").length; i++)
	{
		sCountry = sCountryString.split("|")[i];
		if (sDefaultCountry == sCountry)
		{
			sDefault = true;
			if (navigator.appName == "Microsoft Internet Explorer")
			{
				//cboState.focus();
			}
		}
		else
		{
			sDefault = false;
		}
		if (sDefault)
		{
			var oo = new Option(sCountry, sCountry, sDefault, sDefault);
			oo.setAttribute("selected", "true");
			cboCountry.options[i] = oo;
			selectOption = i;
		}
		else
		{
			cboCountry.options[i] = new Option(sCountryString.split("|")[i]);
		}
	}
	if (selectOption > -1)
	{
		cboCountry.selectedIndex = selectOption;
	}
}
function populate_states_pulldown(cboCountry, cboState, sDefaultState)
{
	var sState, sDefault;
	var selectOption = -1;
	cboState.options.length = 0;
	for (i = 0; i < sStateArray[cboCountry.selectedIndex].split("|").length; i++)
	{
		sState = sStateArray[cboCountry.selectedIndex].split("|")[i];
		if (sDefaultState == sState)
		{
			sDefault = true;
			selectOption = i;
			if (navigator.appName == "Microsoft Internet Explorer")
			{
				//cboState.focus();
			}
		}
		else
		{
			sDefault = false;
		}
		if (sDefault)
		{
			var oo = new Option(sState, sState, sDefault, sDefault);
			oo.setAttribute("selected", "true");
			cboState.options[i] = oo;	
		}
		else
		{
			cboState.options[i] = new Option(sState,sState);
		}
	}
	if (selectOption > -1)
	{
		cboState.selectedIndex = selectOption;
	}
}
function populate_cities_pulldown(cboState, cboCity, sDefaultCity)
{
	var sState, sCity, sDefault;
	var selectOption = -1;
	cboCity.options.length = 0;
	for (i = 0; i < sCityArray[cboState[cboState.selectedIndex].text].split("|").length; i++)
	{
		sCity = sCityArray[cboState[cboState.selectedIndex].text].split("|")[i];
		if (sDefaultCity == sCity)
		{
			sDefault = true;
			selectOption = i;
			if (navigator.appName == "Microsoft Internet Explorer")
			{
				//cboCity.focus();
			}
		}
		else
		{
			sDefault = false;
		}
		if (sDefault)
		{
			var oo = new Option(sCity, sCity, sDefault, sDefault);
			oo.setAttribute("selected", "true");
			cboCity.options[i] = oo;	
		}
		else
		{
			cboCity.options[i] = new Option(sCity, sCity);
		}
	}
	if (selectOption > -1)
	{
		cboCity.selectedIndex = selectOption;
	}
}
function fetch_tags(parentobj, tag)
{
	if (parentobj == null)
	{
		return new Array();
	}
	else if (typeof parentobj.getElementsByTagName != 'undefined')
	{
		return parentobj.getElementsByTagName(tag);
	}
	else if (parentobj.all && parentobj.all.tags)
	{
		return parentobj.all.tags(tag);
	}
	else
	{
		return new Array();
	}
}
function construct_phrase()
{
	if (!arguments || arguments.length < 1 || !REGEXP_Compatible)
	{
		return false;
	}
	var args = arguments;
	var str = args[0];
	var re;
	for (var i = 1; i < args.length; i++)
	{
		re = new RegExp("%" + i + "\\$s", 'gi');
		str = str.replace(re, args[i]);
	}
	return str;
}
function construct_textarea_height(boxid, pixelvalue)
{
	var box = fetch_js_object(boxid);
	var boxheight = parseInt(box.style.height);
	var newheight = boxheight + pixelvalue;
	if (newheight > 0)
	{
		box.style.height = newheight + "px";
	}
	return false;
}
function mediashare_validate(field, errtext)
{
	if (field.value == '')
	{
		alert_js('You cannot continue without a ' + errtext);
		return false;
	}
	return true;
}
function unescape_cdata(str)
{
	var r1 = /<\=\!\=\[\=C\=D\=A\=T\=A\=\[/g;
	var r2 = /\]\=\]\=>/g;
	return str.replace(r1, '<![CDATA[').replace(r2, ']]>');
}
function urlencode(text)
{
	text = escape(text.toString()).replace(/\+/g, "%2B");
	var matches = text.match(/(%([0-9A-F]{2}))/gi);
	if (matches)
	{
		for (var matchid = 0; matchid < matches.length; matchid++)
		{
			var code = matches[matchid].substring(1,3);
			if (parseInt(code, 16) >= 128)
			{
				text = text.replace(matches[matchid], '%u00' + code);
			}
		}
	}
	text = text.replace('%25', '%u0025');
	return text;
}
function toggle_tab(tabid, obj, obj2, taboutputdiv)
{
	var linkList;
	var newtab;
	linkList = fetch_js_object(tabid).getElementsByTagName('li');
	for (i = 0; i < linkList.length; i++)
	{
		linkList[i].className = '';
	}
	fetch_js_object(obj).className = 'on';	
	newtab = fetch_js_object(obj2).innerHTML;
	fetch_js_object(taboutputdiv).innerHTML = newtab;
}
function toggle_big_tab(obj, phrase)
{
	var linkList;
	linkList = fetch_js_object('skilltabs').getElementsByTagName('li');
	for (i = 0; i < linkList.length; i++)
	{
		linkList[i].className = '';
	}
	fetch_js_object(obj).className = 'on';
	fetch_js_object('tabtext').innerHTML = phrase;
}
function fetch_watchlist_response()
{
	if (xmldata.handler.readyState == 4 && xmldata.handler.status == 200 && xmldata.handler.responseXML)
	{
		// format response
		response = fetch_tags(xmldata.handler.responseXML, 'status')[0];
		phpstatus = xmldata.fetch_data(response);
		if (phpstatus == 'addeditem')
		{
			fetch_js_object('itemwatchlistresponse').innerHTML = '<span class="blue"><a href="' + ILBASE + 'watchlist.php">' + phrase['_you_are_watching_this_item_js'] + '</a></span>';
		}
		else if (phpstatus == 'alreadyaddeditem')
		{
			fetch_js_object('itemwatchlistresponse').innerHTML = '<span class="gray">' + phrase['_this_item_is_already_added_to_your_watchlist'] + '</span>';	
		}
		else if (phpstatus == 'addedseller')
		{
			fetch_js_object('sellerwatchlistresponse' + xmldata.id).innerHTML = '<span class="blue"><a href="' + ILBASE + 'watchlist.php?tab=2">' + phrase['_seller_is_in_favorites_js'] + '</a></span>';
		}
		else if (phpstatus == 'alreadyaddedseller')
		{
			fetch_js_object('sellerwatchlistresponse' + xmldata.id).innerHTML = '<span class="gray">' + phrase['_this_seller_is_already_added_to_your_watchlist'] + '</span>';	
		}
		else if (phpstatus == 'error')
		{
			fetch_js_object('itemwatchlistresponse').innerHTML = '<span class="gray">' + phrase['_please_signin_to_save_items_to_watchlist'] + '</span>';
			fetch_js_object('sellerwatchlistresponse' + xmldata.id).innerHTML = '<span class="gray">' + phrase['_please_signin_to_save_favorite_sellers'] + '</span>';
		}
		xmldata.handler.abort();
	}
}
function add_item_to_watchlist(projectid, userid)
{
	if (userid == '' || userid == 0 || projectid == '' || projectid == 0)
	{
		fetch_js_object('itemwatchlistresponse').innerHTML = '<span class="gray">' + phrase['_please_signin_to_save_items_to_watchlist'] + '</span>';
		return false;
	}
	xmldata = new AJAX_Handler(true);
	projectid = urlencode(projectid);
	xmldata.projectid = projectid;
	userid = urlencode(userid);
	xmldata.userid = userid;
	xmldata.onreadystatechange(fetch_watchlist_response);
	xmldata.send(AJAXURL, 'do=addwatchlist&projectid=' + projectid + '&userid=' + userid + '&s=' + ILSESSION + '&token=' + ILTOKEN);
}
function add_seller_to_watchlist(sellerid, userid, id)
{
	if (userid == '' || userid == 0 || sellerid == '' || sellerid == 0)
	{
		fetch_js_object('itemwatchlistresponse').innerHTML = '<span class="gray">' + phrase['_please_signin_to_save_favorite_sellers'] + '</span>';
		return false;	
	}
	xmldata = new AJAX_Handler(true);
	sellerid = urlencode(sellerid);
	xmldata.sellerid = sellerid;
	userid = urlencode(userid);
	xmldata.userid = userid;
	id = urlencode(id);
	xmldata.id = id;
	xmldata.onreadystatechange(fetch_watchlist_response);
	xmldata.send(AJAXURL, 'do=addwatchlist&sellerid=' + sellerid + '&userid=' + userid + '&s=' + ILSESSION + '&token=' + ILTOKEN);
}
function print_states(fieldname, countryfieldname, divstateid, shortform, extracss, disablecities, citiesfieldname, citiesdivid)
{
	var ajaxRequest;
	fetch_js_object(fieldname).disabled = true;
	try
	{
		ajaxRequest = new XMLHttpRequest();
	}
	catch (e)
	{
		try
		{
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		}	
		catch (e)
		{
			try
			{
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e)
			{
				return false;
			}
		}
	}
	ajaxRequest.onreadystatechange = function()
	{
		if (ajaxRequest.readyState == 4 && ajaxRequest.responseText != '')
		{
			fetch_js_object(fieldname).disabled = false;
			var ajaxDisplay = fetch_js_object(divstateid);
			ajaxDisplay.innerHTML = ajaxRequest.responseText;
			
		}
	}
	var countryname = fetch_js_object(countryfieldname).options[fetch_js_object(countryfieldname).selectedIndex].value;
	var querystring = "&countryname=" + countryname + "&fieldname=" + fieldname + "&shortform=" + shortform + "&extracss=" + extracss + "&disablecities=" + disablecities + "&citiesfieldname=" + citiesfieldname + "&citiesdivid=" + citiesdivid + "&s=" + ILSESSION + "&token=" + ILTOKEN;
	ajaxRequest.open('GET', AJAXURL + '?do=showstates' + querystring, true);
	ajaxRequest.send(null);
}
function print_cities(fieldname, statefieldname, divcityid, extracss)
{
	var ajaxRequest;
	fetch_js_object(fieldname).disabled = true;
	try
	{
		ajaxRequest = new XMLHttpRequest();
	}
	catch (e)
	{
		try
		{
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		}	
		catch (e)
		{
			try
			{
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e)
			{
				return false;
			}
		}
	}
	ajaxRequest.onreadystatechange = function()
	{
		if (ajaxRequest.readyState == 4 && ajaxRequest.responseText != '')
		{
			fetch_js_object(fieldname).disabled = false;
			var ajaxDisplay = fetch_js_object(divcityid);
			ajaxDisplay.innerHTML = ajaxRequest.responseText;
			
		}
	}
	var statename = fetch_js_object(statefieldname).options[fetch_js_object(statefieldname).selectedIndex].value;
	var querystring = "&state=" + statename + "&fieldname=" + fieldname + "&extracss=" + extracss + "&s=" + ILSESSION + "&token=" + ILTOKEN;
	ajaxRequest.open('GET', AJAXURL + '?do=showcities' + querystring, true);
	ajaxRequest.send(null);
}
function print_shipping_services(divcontainer, fieldname, domestic, international, shipperid, disabled, jspackagetype, jspackagedivcontent, jspackagefieldname, jspackagevalue, jspickupdivcontent, jspickupfieldname, jspickupvalue, ship_method)
{
	var ajaxRequest;
	fetch_js_object(fieldname).disabled = true;
	try
	{
		ajaxRequest = new XMLHttpRequest();
	}
	catch (e)
	{
		try
		{
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		}	
		catch (e)
		{
			try
			{
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e)
			{
				return false;
			}
		}
	}
	ajaxRequest.onreadystatechange = function()
	{
		if (ajaxRequest.readyState == 4)
		{
			fetch_js_object(fieldname).disabled = false;
			var ajaxDisplay = fetch_js_object(divcontainer);
			ajaxDisplay.innerHTML = ajaxRequest.responseText;
			
		}
	}
	if (disabled == null)
	{
		disabled = false;
	}
	//alert_js(shipperid);
	var querystring = "&fieldname=" + fieldname + "&domestic=" + domestic + "&international=" + international + "&shipperid=" + shipperid + "&disabled=" + disabled + "&jspackagetype=" + jspackagetype + "&jspackagedivcontent=" + jspackagedivcontent + "&jspackagefieldname=" + jspackagefieldname + "&jspackagevalue=" + jspackagevalue + "&jspickupdivcontent=" + jspickupdivcontent + "&jspickupfieldname=" + jspickupfieldname + "&jspickupvalue=" + jspickupvalue + "&ship_method=" + ship_method + "&s=" + ILSESSION + "&token=" + ILTOKEN;
	ajaxRequest.open('GET', AJAXURL + '?do=showshippers' + querystring, true);
	ajaxRequest.send(null);
}
function print_ship_package_types(divcontainer, fieldname, packageid, disabled, obj)
{
	var ajaxRequest;
	fetch_js_object(fieldname).disabled = true;
	if (typeof obj == 'object')
	{
		var shipperid = obj.value;
	}
	else
	{
		var shipperid = obj;
	}
	try
	{
		ajaxRequest = new XMLHttpRequest();
	}
	catch (e)
	{
		try
		{
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		}	
		catch (e)
		{
			try
			{
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e)
			{
				return false;
			}
		}
	}
	ajaxRequest.onreadystatechange = function()
	{
		if (ajaxRequest.readyState == 4)
		{
			fetch_js_object(fieldname).disabled = false;
			var ajaxDisplay = fetch_js_object(divcontainer);
			ajaxDisplay.innerHTML = ajaxRequest.responseText;
			
		}
	}
	if (disabled == null)
	{
		disabled = false;
	}
	if (fetch_js_object('ship_method').value == "calculated")
	{
		var querystring = "&shipperid=" + shipperid + "&fieldname=" + fieldname + "&packageid=" + packageid + "&disabled=" + disabled + "&s=" + ILSESSION + "&token=" + ILTOKEN;
		ajaxRequest.open('GET', AJAXURL + '?do=showshippackages' + querystring, true);
		ajaxRequest.send(null);
	}
}
function print_ship_pickup_types(divcontainer, fieldname, pickupid, disabled, obj)
{
	var ajaxRequest;
	fetch_js_object(fieldname).disabled = true;
	if (typeof obj == 'object')
	{
		var shipperid = obj.value;
	}
	else
	{
		var shipperid = obj;
	}
	try
	{
		ajaxRequest = new XMLHttpRequest();
	}
	catch (e)
	{
		try
		{
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		}	
		catch (e)
		{
			try
			{
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e)
			{
				return false;
			}
		}
	}
	ajaxRequest.onreadystatechange = function()
	{
		if (ajaxRequest.readyState == 4)
		{
			fetch_js_object(fieldname).disabled = false;
			var ajaxDisplay = fetch_js_object(divcontainer);
			ajaxDisplay.innerHTML = ajaxRequest.responseText;
		}
	}
	if (disabled == null)
	{
		disabled = false;
	}
	if (fetch_js_object('ship_method').value == "calculated")
	{
		var querystring = "&shipperid=" + shipperid + "&fieldname=" + fieldname + "&pickupid=" + pickupid + "&disabled=" + disabled + "&s=" + ILSESSION + "&token=" + ILTOKEN;
		ajaxRequest.open('GET', AJAXURL + '?do=showshippickupdropoff' + querystring, true);
		ajaxRequest.send(null);
	}
}
function print_duration(duration, fieldname, disabled, unittype, showprices, cid)
{
	var ajaxRequest;
	fetch_js_object(fieldname).disabled = true;
	try
	{
		ajaxRequest = new XMLHttpRequest();
	}
	catch (e)
	{
		try
		{
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		}	
		catch (e)
		{
			try
			{
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e)
			{
				return false;
			}
		}
	}
	ajaxRequest.onreadystatechange = function()
	{
		if (ajaxRequest.readyState == 4)
		{
			fetch_js_object(fieldname).disabled = false;
			var ajaxDisplay = fetch_js_object('durationwrapper');
			ajaxDisplay.innerHTML = ajaxRequest.responseText;
			
		}
	}
	if (disabled == null)
	{
		disabled = false;
	}
	var querystring = "&fieldname=" + fieldname + "&duration=" + duration + "&disabled=" + disabled + "&unittype=" + unittype + "&showprices=" + showprices + "&cid=" + cid + "&s=" + ILSESSION + "&token=" + ILTOKEN;
	ajaxRequest.open('GET', AJAXURL + '?do=showduration' + querystring, true);
	ajaxRequest.send(null);
}

function get_html_translation_table(table, quote_style)
{
	// http://kevin.vanzonneveld.net
	// +   original by: Philip Peterson
	// +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// +   bugfixed by: noname
	// %          note: It has been decided that we're not going to add global
	// %          note: dependencies to php.js. Meaning the constants are not
	// %          note: real constants, but strings instead. integers are also supported if someone
	// %          note: chooses to create the constants themselves.
	// %          note: Table from http://www.the-art-of-web.com/html/character-codes/
	// *     example 1: get_html_translation_table('HTML_SPECIALCHARS');
	// *     returns 1: {'"': '&quot;', '&': '&amp;', '<': '&lt;', '>': '&gt;'}
	
	var entities = {}, histogram = {}, decimal = 0, symbol = '';
	var constMappingTable = {}, constMappingQuoteStyle = {};
	var useTable = {}, useQuoteStyle = {};
	
	useTable      = (table ? table.toUpperCase() : 'HTML_SPECIALCHARS');
	useQuoteStyle = (quote_style ? quote_style.toUpperCase() : 'ENT_COMPAT');
	
	// Translate arguments
	constMappingTable[0]      = 'HTML_SPECIALCHARS';
	constMappingTable[1]      = 'HTML_ENTITIES';
	constMappingQuoteStyle[0] = 'ENT_NOQUOTES';
	constMappingQuoteStyle[2] = 'ENT_COMPAT';
	constMappingQuoteStyle[3] = 'ENT_QUOTES';
	
	// Map numbers to strings for compatibilty with PHP constants
	if (!isNaN(useTable)) {
	    useTable = constMappingTable[useTable];
	}
	if (!isNaN(useQuoteStyle)) {
	    useQuoteStyle = constMappingQuoteStyle[useQuoteStyle];
	}
	
	if (useQuoteStyle != 'ENT_NOQUOTES') {
	    entities['34'] = '&quot;';
	}
     
	if (useQuoteStyle == 'ENT_QUOTES') {
	    entities['39'] = '&#039;';
	}
     
	if (useTable == 'HTML_SPECIALCHARS') {
	    // ascii decimals for better compatibility
	    entities['38'] = '&amp;';
	    entities['60'] = '&lt;';
	    entities['62'] = '&gt;';
	} else if (useTable == 'HTML_ENTITIES') {
	    // ascii decimals for better compatibility
	  entities['38']  = '&amp;';
	  entities['60']  = '&lt;';
	  entities['62']  = '&gt;';
	  entities['160'] = '&nbsp;';
	  entities['161'] = '&iexcl;';
	  entities['162'] = '&cent;';
	  entities['163'] = '&pound;';
	  entities['164'] = '&curren;';
	  entities['165'] = '&yen;';
	  entities['166'] = '&brvbar;';
	  entities['167'] = '&sect;';
	  entities['168'] = '&uml;';
	  entities['169'] = '&copy;';
	  entities['170'] = '&ordf;';
	  entities['171'] = '&laquo;';
	  entities['172'] = '&not;';
	  entities['173'] = '&shy;';
	  entities['174'] = '&reg;';
	  entities['175'] = '&macr;';
	  entities['176'] = '&deg;';
	  entities['177'] = '&plusmn;';
	  entities['178'] = '&sup2;';
	  entities['179'] = '&sup3;';
	  entities['180'] = '&acute;';
	  entities['181'] = '&micro;';
	  entities['182'] = '&para;';
	  entities['183'] = '&middot;';
	  entities['184'] = '&cedil;';
	  entities['185'] = '&sup1;';
	  entities['186'] = '&ordm;';
	  entities['187'] = '&raquo;';
	  entities['188'] = '&frac14;';
	  entities['189'] = '&frac12;';
	  entities['190'] = '&frac34;';
	  entities['191'] = '&iquest;';
	  entities['192'] = '&Agrave;';
	  entities['193'] = '&Aacute;';
	  entities['194'] = '&Acirc;';
	  entities['195'] = '&Atilde;';
	  entities['196'] = '&Auml;';
	  entities['197'] = '&Aring;';
	  entities['198'] = '&AElig;';
	  entities['199'] = '&Ccedil;';
	  entities['200'] = '&Egrave;';
	  entities['201'] = '&Eacute;';
	  entities['202'] = '&Ecirc;';
	  entities['203'] = '&Euml;';
	  entities['204'] = '&Igrave;';
	  entities['205'] = '&Iacute;';
	  entities['206'] = '&Icirc;';
	  entities['207'] = '&Iuml;';
	  entities['208'] = '&ETH;';
	  entities['209'] = '&Ntilde;';
	  entities['210'] = '&Ograve;';
	  entities['211'] = '&Oacute;';
	  entities['212'] = '&Ocirc;';
	  entities['213'] = '&Otilde;';
	  entities['214'] = '&Ouml;';
	  entities['215'] = '&times;';
	  entities['216'] = '&Oslash;';
	  entities['217'] = '&Ugrave;';
	  entities['218'] = '&Uacute;';
	  entities['219'] = '&Ucirc;';
	  entities['220'] = '&Uuml;';
	  entities['221'] = '&Yacute;';
	  entities['222'] = '&THORN;';
	  entities['223'] = '&szlig;';
	  entities['224'] = '&agrave;';
	  entities['225'] = '&aacute;';
	  entities['226'] = '&acirc;';
	  entities['227'] = '&atilde;';
	  entities['228'] = '&auml;';
	  entities['229'] = '&aring;';
	  entities['230'] = '&aelig;';
	  entities['231'] = '&ccedil;';
	  entities['232'] = '&egrave;';
	  entities['233'] = '&eacute;';
	  entities['234'] = '&ecirc;';
	  entities['235'] = '&euml;';
	  entities['236'] = '&igrave;';
	  entities['237'] = '&iacute;';
	  entities['238'] = '&icirc;';
	  entities['239'] = '&iuml;';
	  entities['240'] = '&eth;';
	  entities['241'] = '&ntilde;';
	  entities['242'] = '&ograve;';
	  entities['243'] = '&oacute;';
	  entities['244'] = '&ocirc;';
	  entities['245'] = '&otilde;';
	  entities['246'] = '&ouml;';
	  entities['247'] = '&divide;';
	  entities['248'] = '&oslash;';
	  entities['249'] = '&ugrave;';
	  entities['250'] = '&uacute;';
	  entities['251'] = '&ucirc;';
	  entities['252'] = '&uuml;';
	  entities['253'] = '&yacute;';
	  entities['254'] = '&thorn;';
	  entities['255'] = '&yuml;';
	}
	else
	{
		return false;
	}
	
	// ascii decimals to real symbols
	for (decimal in entities)
	{
		symbol = String.fromCharCode(decimal)
		histogram[symbol] = entities[decimal];
	}
	
	return histogram;
}
function html_entity_decode(string, quote_style)
{
	var histogram = {}, symbol = '', tmp_str = '', entity = '';
	tmp_str = string.toString();
	if (false === (histogram = get_html_translation_table('HTML_ENTITIES', quote_style)))
	{
		return false;
	}
	// &amp; must be the last character when decoding!
	delete(histogram['&']);
	histogram['&'] = '&amp;';
	for (symbol in histogram)
	{
		entity = histogram[symbol];
		tmp_str = tmp_str.split(entity).join(symbol);
	}
	return tmp_str;
}
function htmlspecialchars(string, quote_style)
{
	var histogram = {}, symbol = '', tmp_str = '', entity = '';
	tmp_str = string.toString();
	if (false === (histogram = get_html_translation_table('HTML_SPECIALCHARS', quote_style)))
	{
		return false;
	}
	for (symbol in histogram)
	{
		entity = histogram[symbol];
		tmp_str = tmp_str.split(symbol).join(entity);
	}
	return tmp_str;
}
function show_category_popup_link() 
{
	if (category_popup_timer != null) 
	{
		clearTimeout(category_popup_timer);
		category_popup_timer = null;
	}
	var divObj;
	divObj = fetch_js_object('category_popup');
	divObj.style.display = '';
}
function show_category_popup_link1() 
{
	if (category_popup_timer != null) 
	{
		clearTimeout(category_popup_timer);
		category_popup_timer = null;
	}   
	var divObj;
	divObj = fetch_js_object('category_popup1');
	divObj.style.display = '';
}
function show_category_popup() 
{
	var div;
	div = fetch_js_object('category_popup');
	category_popup_timer = setTimeout("do_show_category_popup();", 750);
}
function show_category1_popup() 
{
	var div;
	div = fetch_js_object('category_popup1');
	category_popup_timer = setTimeout("do_show_category_popup1();", 750);
}
function hide_category1_popup() 
{
	if (category_popup_timer != null) 
	{
		clearTimeout(category_popup_timer);
		category_popup_timer = null;
	}
	var div;
	div = fetch_js_object('category_popup1');
	category_popup_timer = setTimeout("do_hide_category_popup1();", 750);
}
function do_hide_category_popup1() 
{
	var divObj;
	divObj = fetch_js_object('category_popup1');
	divObj.style.display = 'none';
}

function do_show_category_popup1() 
{
	if (category_popup_timer != null) 
	{
		clearTimeout(category_popup_timer);
		category_popup_timer = null;
	}
	var divObj;
	divObj = fetch_js_object('category_popup1');
	divObj.style.display = '';
}
function hide_category_popup() 
{
	if (category_popup_timer != null) 
	{
		clearTimeout(category_popup_timer);
		category_popup_timer = null;
	}
	var div;
	div = fetch_js_object('category_popup');
	category_popup_timer = setTimeout("do_hide_category_popup();", 550);
}

function do_hide_category_popup() 
{
	var divObj;
	divObj = fetch_js_object('category_popup');
	divObj.style.display = 'none';
}
function do_show_category_popup() 
{
	if (category_popup_timer != null) 
	{
		clearTimeout(category_popup_timer);
		category_popup_timer = null;
	}
	var divObj;
	divObj = fetch_js_object('category_popup');
	divObj.style.display = '';
}
function ie_prompt(str)
{
	var settings = 'dialogWidth: 380px; dialogHeight: 170px; center: yes; edge: raised; scroll: no; status: no;';
	var response = window.showModalDialog(IMAGEBASE + 'ie_prompt.html', str, settings);
	return response;
}
function ilance_prompt(str)
{
	try
	{
		if (checksaf)
		{
			str = str.replace(/<[^>]+>/ig,'');
			return prompt(str, '');
		}
		else
		{
			return ie_prompt(str);
		}
	}
	
	catch (e)
	{ 
		return false; 
	} 	
}
function livefeecalculator(fee, type, objid)
{
	if (type == 'single')
	{
		if (fetch_js_object(objid).checked)
		{
			// adding new fee
			//alert_js('add: ' + fee);	
		}
		else
		{
			// removing old fee
			//alert_js('rem: ' + fee);
		}
	}
	else if (type == 'bulk')
	{
		if (fetch_js_object(objid).checked)
		{
			// adding new fee
			//alert_js('add: ' + fee);	
		}
		else
		{
			// removing old fee
			//alert_js('rem: ' + fee);
		}	
	}
}
function string_to_number(price)
{
	var thous = " '", decPoint, decComma, decMark, dec, re, matches, pre = '3';
	
	price = price.replace(/^\s+|\s+$/g, "");
	decPoint = price.lastIndexOf('.');
	decComma = price.lastIndexOf(',');
	
	if (decPoint > -1 && decComma > -1)
	{
		if (decPoint > decComma)
		{
			thous += ',';
		}
		else
		{
			thous += '.';
			decMark = ',';
		}
	}
	
	if ((price.indexOf(' ') > 0 || price.indexOf("'") > 0) && decComma)
	{
		decMark = ',';
	}
	
	if (price.substring(decPoint + 1).length === 3 && decComma < 1 && price.indexOf('.') < decPoint)
	{
		thous += '.';
	}
	
	if (price.substring(decComma + 1).length === 3 && decPoint < 1 && price.indexOf(',') < decComma)
	{
		thous += ',';
	}
	
	re = new RegExp("^(?:(\\d{1,3}(?:(?:(?:[" + thous + "]\\d{3})+)?)?|\\d+)?([,.]\\d{1,})?|\\d+)$");
	matches = re.exec(price);
	
	if (!matches)
	{
		return false;
	}
	
	if (!matches[1] && !matches[2])
	{
		matches[1] = matches[0];
	}
	
	dec = matches[2] && matches[2].length === 4 && !decMark && matches[1] !== '0' ? '' : '.';
	
	return (matches[1] || '').replace(/[,' .]/g, '') + '' + (matches[2] || '').replace(',', dec);
}
function show_working_icon(icondiv)
{
	toggle_show(icondiv);
	fetch_js_object(icondiv).innerHTML = '<img src="' + IMAGEBASE + 'working.gif" border="0" width="13" height="13" />';
}
function disable_submit_button(theform, thephrase, showworkingicon, icondiv)
{
	if (document.all || document.getElementById)
	{
		for (i = 0; i < theform.length; i++)
		{
			var tempobj = theform.elements[i];
			if (tempobj.type.toLowerCase() == 'submit' || tempobj.type.toLowerCase() == 'reset')
			{
				tempobj.disabled = true;
			}
		}
		if (showworkingicon == 1)
		{
			show_working_icon(icondiv);
		}
		setTimeout('alert_js("' + thephrase + '")', 2000);
		return true;
	}
}
function rollovericon(img_name, img_src)
{
	document[img_name].src = img_src;
}
function mysql_datetime_to_js_date(timestamp)
{
	var regex = /^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9]) (?:([0-2][0-9]):([0-5][0-9]):([0-5][0-9]))?$/;
	var parts = timestamp.replace(regex, "$1 $2 $3 $4 $5 $6").split(' ');
	return new Date(parts[0], parts[1]-1, parts[2], parts[3], parts[4], parts[5]);
}
function validate_all_fields()
{
	if (typeof editor == 'undefined')
	{
		fetch_bbeditor_data_bbeditor_pmb();
	}
	else
	{
		editor.updateElement();
		fetch_js_object('message_id').value = editor.getData();
	}
	
	if (fetch_js_object('pmbsubject').value == '')
	{
		alert_js(phrase['_please_enter_the_subject_to_dispatch_this_private_message']);
		return false;
	}
	if (fetch_js_object('message_id').value == '')
	{
		alert_js(phrase['_your_description_or_message_should_not_be_empty']);
		return false;
	}
	return true;
}
function fetch_pmb_info()
{
	if (xmldata.handler.readyState == 4 && xmldata.handler.status == 200)
	{
		fetch_js_object('pmbattachmentlist').innerHTML = '';
		fetch_js_object('pmbuploadbutton').innerHTML = '';
		fetch_js_object('pmbconversation').innerHTML = '';
		if (xmldata.handler.responseText != '')
		{
			var attachinfo;
			attachinfo = xmldata.handler.responseText;
			attachinfo = attachinfo.split("|");
			fetch_js_object('pmbattachmentlist').innerHTML = attachinfo[0];
			fetch_js_object('pmbuploadbutton').innerHTML = attachinfo[1];
			fetch_js_object('pmbconversation').innerHTML = attachinfo[2];
			setTimeout('refresh_pmb_conversation(xmldata.crypted)', 10000);
		}
		xmldata.handler.abort();
	}
}
function refresh_pmb_conversation(crypted)
{
	fetch_js_object('pmbcrypted_modal').value = crypted;
	xmldata = new AJAX_Handler(true);
	crypted = fetch_js_object('pmbcrypted_modal').value;
	xmldata.crypted = crypted;
	toggle_show('pmbconversation');
	fetch_js_object('pmbconversationrefresh').innerHTML = '<img src="' + IMAGEBASE + 'working.gif" alt="" border="" />';
	xmldata.onreadystatechange(fetch_pmb_info);
	if (typeof editor == 'undefined')
	{
		xmldata.send(AJAXURL, 'do=pminfo&crypted=' + crypted + '&s=' + ILSESSION + '&token=' + ILTOKEN + '&editor=bb');
	}
	else
	{
		xmldata.send(AJAXURL, 'do=pminfo&crypted=' + crypted + '&s=' + ILSESSION + '&token=' + ILTOKEN + '&editor=ck');
	}
	return true;
}
function update_pmb_crypted(crypted)
{
	jQuery('#pmb_modal').jqm({modal: true}).jqmShow();
	jQuery('body').css('overflow','hidden');
	fetch_js_object('pmbcrypted_modal').value = crypted;
	fetch_js_object('previewpmbbutton').disabled = false;
	fetch_js_object('submitpmbbutton').disabled = false;
	fetch_js_object('modal_pmb_working').innerHTML = '';
	toggle_hide('pmb_preview_table');
	fetch_js_object('pmbsubject').value = '';
	if (typeof editor == 'undefined')
	{
		window.frames['bbeditor_pmb'].document.body.innerHTML = '';
		fetch_js_object('textarea_bbeditor_pmb').value = '';
		fetch_js_object('bbeditor_html_ouput_bbeditor_pmb').value = '';
		fetch_js_object('bbeditor_bbcode_ouput_bbeditor_pmb').value = '';
	}
	
	xmldata = new AJAX_Handler(true);
	crypted = fetch_js_object('pmbcrypted_modal').value;
	xmldata.crypted = crypted;
	toggle_show('pmbconversation');
	fetch_js_object('pmbattachmentlist').innerHTML = '<img src="' + IMAGEBASE + 'working.gif" alt="" border="" />';
	fetch_js_object('pmbconversation').innerHTML = '<img src="' + IMAGEBASE + 'working.gif" alt="" border="" />';
	xmldata.onreadystatechange(fetch_pmb_info);
	if (typeof editor == 'undefined')
	{
		xmldata.send(AJAXURL, 'do=pminfo&crypted=' + crypted + '&s=' + ILSESSION + '&token=' + ILTOKEN + '&editor=bb');
	}
	else
	{
		xmldata.send(AJAXURL, 'do=pminfo&crypted=' + crypted + '&s=' + ILSESSION + '&token=' + ILTOKEN + '&editor=ck');
	}
	return true;
}
function fetch_submit_pmb_response()
{
	if (xmldatasubmit.handler.readyState == 4 && xmldatasubmit.handler.status == 200)
	{
		fetch_js_object('submitpmbbutton').disabled = false;
		fetch_js_object('previewpmbbutton').disabled = false;
		fetch_js_object('modal_pmb_working').innerHTML = '';
		jQuery("#pmb_modal_top").scrollTop(0);
		if (xmldatasubmit.handler.responseText != '')
		{
			fetch_js_object('pmb_preview_table').innerHTML = xmldatasubmit.handler.responseText;
			toggle_show('pmb_preview_table');
			fetch_js_object('pmbsubject').value = '';
			if (typeof editor == 'undefined')
			{
				window.frames['bbeditor_pmb'].document.body.innerHTML = '';
			}
			fetch_js_object('textarea_bbeditor_pmb').value = '';
			fetch_js_object('bbeditor_html_ouput_bbeditor_pmb').value = '';
			fetch_js_object('bbeditor_bbcode_ouput_bbeditor_pmb').value = '';
		}
		xmldatasubmit.handler.abort();
	}
}
function submit_pmb()
{
	validate = validate_all_fields();
	if (validate == true)
	{
		xmldatasubmit = new AJAX_Handler(true);
		crypted = fetch_js_object('pmbcrypted_modal').value;
		subject = fetch_js_object('pmbsubject').value;
		message = fetch_js_object('message_id').value;
		xmldatasubmit.crypted = crypted;
		xmldatasubmit.subject = subject;
		xmldatasubmit.message = message;
		fetch_js_object('submitpmbbutton').disabled = true;
		fetch_js_object('previewpmbbutton').disabled = true;
		fetch_js_object('modal_pmb_working').innerHTML = '<img src="' + IMAGEBASE + 'working.gif" alt="" border="" id="" />';
		xmldatasubmit.onreadystatechange(fetch_submit_pmb_response);
		if (typeof editor == 'undefined')
		{
			xmldatasubmit.send(AJAXURL, 'do=submitpm&crypted=' + crypted + '&message=' + urlencode(message) + '&subject=' + urlencode(subject) + '&s=' + ILSESSION + '&token=' + ILTOKEN + '&editor=bb');
		}
		else
		{
			xmldatasubmit.send(AJAXURL, 'do=submitpm&crypted=' + crypted + '&message=' + urlencode(message) + '&subject=' + urlencode(subject) + '&s=' + ILSESSION + '&token=' + ILTOKEN + '&editor=ck');
                        editor.setData('')
                }
		return true;
	}
	return false;
}
function fetch_preview_pmb_response()
{
	if (xmldatapreview.handler.readyState == 4 && xmldatapreview.handler.status == 200)
	{
		fetch_js_object('previewpmbbutton').disabled = false;
		fetch_js_object('submitpmbbutton').disabled = false;
		fetch_js_object('modal_pmb_working').innerHTML = '';
		jQuery("#pmb_modal_top").scrollTop(0);
		if (xmldatapreview.handler.responseText != '0' && xmldatapreview.handler.responseText != '')
		{
			fetch_js_object('pmb_preview_table').innerHTML = xmldatapreview.handler.responseText;
			toggle_show('pmb_preview_table');
		}
		xmldatapreview.handler.abort();
	}
}
function preview_pmb()
{
	validate = validate_all_fields();
	if (validate == true)
	{
		xmldatapreview = new AJAX_Handler(true);
		subject = fetch_js_object('pmbsubject').value;
		message = fetch_js_object('message_id').value;
		xmldatapreview.subject = subject;
		xmldatapreview.message = message;
		fetch_js_object('previewpmbbutton').disabled = true;
		fetch_js_object('submitpmbbutton').disabled = true;
		fetch_js_object('modal_pmb_working').innerHTML = '<img src="' + IMAGEBASE + 'working.gif" alt="" border="" />';
		xmldatapreview.onreadystatechange(fetch_preview_pmb_response);
		if (typeof editor == 'undefined')
		{
			xmldatapreview.send(AJAXURL, 'do=previewpm&message=' + urlencode(message) + '&subject=' + urlencode(subject) + '&s=' + ILSESSION + '&token=' + ILTOKEN + '&editor=bb');
		}
		else
		{
			xmldatapreview.send(AJAXURL, 'do=previewpm&message=' + urlencode(message) + '&subject=' + urlencode(subject) + '&s=' + ILSESSION + '&token=' + ILTOKEN + '&editor=ck');
		}
		
		return true;
	}
	return false;
}
function remove_pmb()
{
	if (xmlrdata.handler.readyState == 4 && xmlrdata.handler.status == 200)
	{
		fetch_js_object('pmbid_' + xmlrdata.id).disabled = false;
		if (xmlrdata.handler.responseText == '1')
		{
			toggle_hide('pmbpostblock_' + xmlrdata.id);
		}
		xmlrdata.handler.abort();
	}
}
function remove_pmb_post(id)
{
	xmlrdata = new AJAX_Handler(true);
	xmlrdata.id = id;
	fetch_js_object('pmbid_' + id).disabled = true;
	xmlrdata.onreadystatechange(remove_pmb);
	xmlrdata.send(AJAXURL, 'do=pmremove&id=' + urlencode(id) + '&s=' + ILSESSION + '&token=' + ILTOKEN);
	return true;
}
function do_test_email_template()
{
	if (xmldataemail.handler.readyState == 4 && xmldataemail.handler.status == 200)
	{
		fetch_js_object('dispatch_test_' + xmldataemail.varname).disabled = false;
		xmldataemail.handler.abort();
	}
}
function send_test_email_template(varname)
{
	xmldataemail = new AJAX_Handler(true);
	xmldataemail.varname = varname;
	fetch_js_object('dispatch_test_' + varname).disabled = true;
	xmldataemail.onreadystatechange(do_test_email_template);
	xmldataemail.send(AJAXURL, 'do=admincpemailtest&varname=' + urlencode(varname) + '&s=' + ILSESSION + '&token=' + ILTOKEN);
	return true;
}
function validate_store_management_form()
{
	return validate_auction_type();
}
function show_actions_popup(pid) 
{
	var div;
	div = fetch_js_object('actions_popup_' + pid);
	actions_popup_timer = setTimeout("do_show_actions_popup('" + pid + "');", 750);
}
function show_actions_popup_links(pid) 
{
	if (actions_popup_timer != null) 
	{
		clearTimeout(actions_popup_timer);
		actions_popup_timer = null;
	}   
	var divObj;
	divObj = fetch_js_object('actions_popup_' + pid);
	divObj.style.display = '';
}
function hide_actions_popup(pid) 
{
	if (actions_popup_timer != null) 
	{
		clearTimeout(actions_popup_timer);
		actions_popup_timer = null;
	}
	var div;
	div = fetch_js_object('actions_popup_' + pid);
	actions_popup_timer = setTimeout("do_hide_actions_popup('" + pid + "');", 750);
}
function do_hide_actions_popup(pid) 
{
	var divObj;
	divObj = fetch_js_object('actions_popup_' + pid);
	divObj.style.display = 'none';
}
function do_show_actions_popup(pid) 
{
	if (actions_popup_timer != null) 
	{
		clearTimeout(actions_popup_timer);
		actions_popup_timer = null;
	}

	var divObj;
	divObj = fetch_js_object('actions_popup_' + pid);
	divObj.style.display = '';
}
function invite_swap(cntainer, cntents, flag)
{
	if (flag == 1)
	{
		temp = fetch_js_object(cntents).innerHTML;
		fetch_js_object(cntents).innerHTML = '';
		fetch_js_object(cntainer).display = 'none';
		if(temp != '')
		{
		 fetch_js_object('tempinvite').innerHTML = temp;
		}
		fetch_js_object('invitecontain').style.display = 'none';
	}
	else
	{
		if(fetch_js_object(cntainer).display == 'none')
		{
			temp = fetch_js_object('tempinvite').innerHTML;
			fetch_js_object('tempinvite').innerHTML = '';
			fetch_js_object(cntainer).display = 'inline';
			fetch_js_object(cntents).innerHTML = temp;
			fetch_js_object('invitecontain').style.display = 'inline';
		}
	}
}
function close_popup_window()
{
        window.close();
        if (window.opener && !window.opener.closed)
        {
                window.opener.location.reload();
        }
}
function toggle_id(idobjname)
{
        obj = fetch_js_object(idobjname);
        if (obj)
        {
                if (obj.style.display == "none")
                {
                        obj.style.display = "";
                }
                else
                {
                        obj.style.display = "none";
                }
        }
        return false;
}
function show_prompt_payment_buyer(urlbit)
{
	var prompttext = ilance_prompt('<div style="padding-bottom:3px"><strong>' + phrase['_how_exactly_did_you_pay_the_seller_for_this_item'] + '</strong></div><div style="padding-bottom:4px"> ' + phrase['_be_specific_example_paypal_visa_wire_etc'] + '</div>');
	var newurl = '';
	if (prompttext != null && prompttext != false && prompttext != '')
	{
		newurl = urlbit + "&winnermarkedaspaidmethod=" + prompttext;
		var xyz = '';
		xyz = confirm_js(phrase['_you_are_about_to_inform_the_seller_that_payment_for_this_item_has_been_paid_in_full']);
		if (xyz)
		{
			document.location = newurl;
		}
		else
		{		
			return false;
		}
	}
	else
	{
		if (prompttext == null || prompttext == false)
		{
			alert_js(phrase['_please_describe_how_you_paid_the_seller_for_this_item']);
		}
	}
}
function show_prompt_payment_seller(urlbit)
{
	var prompttext = ilance_prompt('<div style="padding-bottom:3px"><strong>' + phrase['_how_exactly_did_the_buyer_pay_you_for_this_item'] + '</strong></div><div style="padding-bottom:4px"> ' + phrase['_be_specific_example_paypal_visa_wire_etc'] + '</div>');
	var newurl = '';
	if (prompttext != null && prompttext != false && prompttext != '')
	{
		newurl = urlbit + "&winnermarkedaspaidmethod=" + prompttext;
		var xyz = '';
		xyz = confirm_js(phrase['_confirmation_you_are_requesting_that_the_payment_status_for_this_item_by_this_buyer']);
		if (xyz)
		{
			document.location = newurl;
		}
		else
		{		
			return false;
		}
	}
	else
	{
		if (prompttext == null || prompttext == false)
		{
			alert_js(phrase['_please_describe_how_the_buyer_paid_you_for_this_item']);
		}
	}
}
function show_prompt_product_retract()
{
	if (fetch_js_object('productbidcmd').value == 'retract')
	{
		var prompttext = ilance_prompt(phrase['_please_enter_the_reason_for_retracting_the_selected_bids']);
		if (prompttext != null && prompttext != false && prompttext != '')
		{
			fetch_js_object('bidretractreason').value = prompttext;
			return true;   
		}
		else
		{
			if (prompttext == null || prompttext == false)
			{
				alert_js(phrase['_in_order_to_retract_one_or_more_bids_placed_you_must_provide']);
			}
			
			return false;
		}
	}
}
function show_prompt_shipping(urlbit)
{
	var prompttext = ilance_prompt(phrase['_only_mark_as_shipped_once_you_have_physically_delivered_or_shipped']);
	var newurl = '';
	if (prompttext != null && prompttext != false || prompttext == '')
	{
		newurl = urlbit + "&trackingnumber=" + prompttext;
		document.location = newurl;
	}
}
function show_prompt_cancel(urlbit)
{
	var prompttext = ilance_prompt(phrase['_please_enter_a_reason_for_this_cancellation']);
	var newurl = '';
	if (prompttext != null && prompttext != false || prompttext == '')
	{
		newurl = urlbit + "&cancelreason=" + prompttext;
		document.location = newurl;
	}
}
function ship_service_add()
{
	var num = fetch_js_object('shippercount').value;
	num++;	
	fetch_js_object('ship_method_service_options_' + num).style.display = '';
	fetch_js_object('ship_options_' + num).disabled = false;
	fetch_js_object('ship_service_' + num).disabled = false;
	if (fetch_js_object('ship_method').value == 'flatrate')
	{
		fetch_js_object('ship_fee_' + num).disabled = false;
		fetch_js_object('ship_fee_next_' + num).disabled = false;
		fetch_js_object('freeshipping_' + num).disabled = false;
		toggle_hide('ship_packagetype_' + num + '_container');
		toggle_hide('ship_pickuptype_' + num + '_container');
	}
	else if (fetch_js_object('ship_method').value == 'calculated')
	{
		fetch_js_object('ship_fee_' + num).disabled = true;
		fetch_js_object('ship_fee_next_' + num).disabled = true;
		fetch_js_object('freeshipping_' + num).disabled = true;
		toggle_show('ship_packagetype_' + num + '_container');
		toggle_show('ship_pickuptype_' + num + '_container');
	}
	fetch_js_object('shippercount').value = num++;
}
function ship_service_remove(num)
{
	fetch_js_object('ship_options_' + num).disabled = true;
	fetch_js_object('ship_service_' + num).disabled = true;
	fetch_js_object('ship_fee_' + num).disabled = true;
	fetch_js_object('ship_fee_next_' + num).disabled = true;
	fetch_js_object('freeshipping_' + num).disabled = true;
	fetch_js_object('ship_method_service_options_' + num).style.display = 'none';
	if (fetch_js_object('ship_options_custom_regionnav_' + num).style.display != 'none')
	{
		fetch_js_object('ship_options_custom_regionnav_' + num).style.display = 'none';
	}
	var num2 = fetch_js_object('shippercount').value;
	num2--;
	fetch_js_object('shippercount').value = num2;
}
function do_calculate_shipping()
{
	fetch_js_object('modal_calculatebutton').disabled = true;
	fetch_js_object('shiprate').innerHTML = '<img src="' + IMAGEBASE + 'working.gif" border="0" alt="" />';	
	var modal_shipperid = fetch_js_object('modal_shipperid').value;
	var modal_country_from = fetch_js_object('modal_country_from').value;
	var modal_state_from = fetch_js_object('modal_state_from').value;
	var modal_city_from = fetch_js_object('modal_city_from').value;
	var modal_zipcode_from = fetch_js_object('modal_zipcode_from').value;
	var modal_country_to = fetch_js_object('modal_country_to').value;
	var modal_state_to = fetch_js_object('modal_state_to').value;
	var modal_city_to = fetch_js_object('modal_city_to').value;
	var modal_zipcode_to = fetch_js_object('modal_zipcode_to').value;
	var modal_ship_weightlbs = fetch_js_object('modal_ship_weightlbs').value;
	var modal_ship_weightoz = fetch_js_object('modal_ship_weightoz').value;
	var modal_ship_length = fetch_js_object('modal_ship_length').value;
	var modal_ship_width = fetch_js_object('modal_ship_width').value;
	var modal_ship_height = fetch_js_object('modal_ship_height').value;
	var modal_packagetype = fetch_js_object('modal_packagetype').value;
	var modal_pickuptype = fetch_js_object('modal_pickuptype').value;
	var ajaxRequest;	
	try
	{
		ajaxRequest = new XMLHttpRequest();
	}
	catch (e)
	{
		try
		{
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		}	
		catch (e)
		{
			try
			{
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch (e)
			{
				return false;
			}
		}
	}	
	ajaxRequest.onreadystatechange = function()
	{
		if (ajaxRequest.readyState == 4)
		{
			fetch_js_object('shiprate').innerHTML = ajaxRequest.responseText;
			fetch_js_object('modal_calculatebutton').disabled = false;
		}
	}	
	var querystring = '&shipperid=' + modal_shipperid + '&country_from=' + modal_country_from + '&state_from=' + modal_state_from + '&city_from=' + modal_city_from + '&zipcode_from=' + modal_zipcode_from + '&country_to=' + modal_country_to + '&state_to=' + modal_state_to + '&city_to=' + modal_city_to + '&zipcode_to=' + modal_zipcode_to + '&weightlbs=' + modal_ship_weightlbs + '&weightoz=' + modal_ship_weightoz + '&length=' + modal_ship_length + '&width=' + modal_ship_width + '&height=' + modal_ship_height + '&packagetype=' + modal_packagetype + '&pickuptype=' + modal_pickuptype + '&s=' + ILSESSION + '&token=' + ILTOKEN;
	ajaxRequest.open('GET', AJAXURL + '?do=shipcalculator' + querystring, true);
	ajaxRequest.send(null);
}
function ship_to_options_onchange(i, disabled)
{
	if (fetch_js_object('ship_options_' + i).value == '')
	{
		toggle_hide('ship_options_custom_regionnav_' + i);
		print_shipping_services('ship_service_' + i + '_container', 'ship_service_' + i, true, false, '', (disabled ? 'true' : 'false'), ((fetch_js_object('ship_method').value == 'calculated') ? 1 : 0), 'ship_packagetype_' + i + '_container', 'ship_packagetype_' + i, '', 'ship_pickuptype_' + i + '_container', 'ship_pickuptype_' + i, '', fetch_js_object('ship_method').value);
		if (fetch_js_object('ship_method').value == 'calculated')
		{
			print_ship_package_types('ship_packagetype_' + i + '_container', 'ship_packagetype_' + i, '', (disabled ? 'true' : 'false'), '');
			print_ship_pickup_types('ship_pickuptype_' + i + '_container', 'ship_pickuptype_' + i, '', (disabled ? 'true' : 'false'), '');
		}
	}
	else if (fetch_js_object('ship_options_' + i).value == 'worldwide')
	{
		toggle_hide('ship_options_custom_regionnav_' + i);
		print_shipping_services('ship_service_' + i + '_container', 'ship_service_' + i, true, true, '', (disabled ? 'true' : 'false'), ((fetch_js_object('ship_method').value == 'calculated') ? 1 : 0), 'ship_packagetype_' + i + '_container', 'ship_packagetype_' + i, '', 'ship_pickuptype_' + i + '_container', 'ship_pickuptype_' + i, '', fetch_js_object('ship_method').value);
		if (fetch_js_object('ship_method').value == 'calculated')
		{
			print_ship_package_types('ship_packagetype_' + i + '_container', 'ship_packagetype_' + i, '', (disabled ? 'true' : 'false'), '');
			print_ship_pickup_types('ship_pickuptype_' + i + '_container', 'ship_pickuptype_' + i, '', (disabled ? 'true' : 'false'), '');
		}
	}
	else if (fetch_js_object('ship_options_' + i).value == 'custom')
	{
		toggle_show('ship_options_custom_regionnav_' + i);
		print_shipping_services('ship_service_' + i + '_container', 'ship_service_' + i, false, true, '', (disabled ? 'true' : 'false'), ((fetch_js_object('ship_method').value == 'calculated') ? 1 : 0), 'ship_packagetype_' + i + '_container', 'ship_packagetype_' + i, '', 'ship_pickuptype_' + i + '_container', 'ship_pickuptype_' + i, '', fetch_js_object('ship_method').value);
		if (fetch_js_object('ship_method').value == 'calculated')
		{
			print_ship_package_types('ship_packagetype_' + i + '_container', 'ship_packagetype_' + i, '', (disabled ? 'true' : 'false'), '');
			print_ship_pickup_types('ship_pickuptype_' + i + '_container', 'ship_pickuptype_' + i, '', (disabled ? 'true' : 'false'), '');
		}
	}
	else if (fetch_js_object('ship_options_' + i).value == 'domestic')
	{
		toggle_hide('ship_options_custom_regionnav_' + i);
		print_shipping_services('ship_service_' + i + '_container', 'ship_service_' + i, true, false, '', (disabled ? 'true' : 'false'), ((fetch_js_object('ship_method').value == 'calculated') ? 1 : 0), 'ship_packagetype_' + i + '_container', 'ship_packagetype_' + i, '', 'ship_pickuptype_' + i + '_container', 'ship_pickuptype_' + i, '', fetch_js_object('ship_method').value);
		if (fetch_js_object('ship_method').value == 'calculated')
		{
			print_ship_package_types('ship_packagetype_' + i + '_container', 'ship_packagetype_' + i, '', (disabled ? 'true' : 'false'), '');
			print_ship_pickup_types('ship_pickuptype_' + i + '_container', 'ship_pickuptype_' + i, '', (disabled ? 'true' : 'false'), '');
		}
	}
}
function check_uncheck_all(formid)
{
	if (cbchecked == false)
	{
		cbchecked = true
	}
	else
	{
		cbchecked = false
	}
	for (var i = 0; i < fetch_js_object(formid).elements.length; i++)
	{
		if (fetch_js_object(formid).elements[i].disabled == false)
		{
			fetch_js_object(formid).elements[i].checked = cbchecked;
		}
	}
}
function check_uncheck_all_id(checkbox_name, class_name)
{
	if (cbchecked2 == false)
	{
		cbchecked2 = true
	}
	else
	{
		cbchecked2 = false
	}
	elements = document.getElementsByName(checkbox_name);
	for (var i = 0; i < elements.length; i++)
	{
		if (elements[i].getAttribute('class') == class_name + '_checkbox')
		{
			if (elements[i].disabled == false)
			{
				elements[i].checked = cbchecked2;
			}
		}
	}
}
function reset_image()
{
	imgtag.src = favoriteicon;
}
function fetch_recently_viewed_items()
{
	if (xmldata.handler.readyState == 4 && xmldata.handler.status == 200)
	{
		if (xmldata.handler.responseText != '')
		{
			fetch_js_object(xmldata.divid).innerHTML = xmldata.handler.responseText;
			jQuery('.carousel_recentviewed_' + xmldata.columns + 'col').jCarouselLite({
				btnNext:xmldata.btnNext,
				btnPrev:xmldata.btnPrev,
				easing:'easeOutQuad',
				visible:xmldata.visible, //jQuery(".carousel_recentviewed_' + xmldata.columns + 'col ul li").length,
				scroll:xmldata.scroll,
				speed:100
			});
			if (xmldata.columns == 3)
			{
				if (jQuery(".carousel_recentviewed_3col ul li").length < 4)
				{
					jQuery("#c6r").addClass('disabled');
				}
			}
		}
		xmldata.handler.abort();
	}
}
function print_recently_viewed_items(type, columns, pageurl)
{                        
	xmldata = new AJAX_Handler(true);
	type = urlencode(type);
	columns = parseInt(columns);
	xmldata.type = type;
	xmldata.columns = columns;
	if (xmldata.columns == 1)
	{
		xmldata.btnNext = '#c7r';
		xmldata.btnPrev = '#c7l';
		xmldata.visible = 1;
		xmldata.scroll = 1;
		xmldata.divid = 'recentviewedmidloader';
	}
	else if (xmldata.columns == 3)
	{
		xmldata.btnNext = '#c6r';
		xmldata.btnPrev = '#c6l';
		xmldata.visible = 3;
		xmldata.scroll = 1;
		xmldata.divid = 'recentviewedtoploader';
	}
	xmldata.onreadystatechange(fetch_recently_viewed_items);
	xmldata.send(AJAXURL, 'do=recentlyvieweditems&type=' + type + '&columns=' + columns + '&s=' + ILSESSION + '&token=' + ILTOKEN + '&returnurl=' + urlencode(pageurl));                        
}
function fetch_favourite_items()
{
	if (xmldata.handler.readyState == 4 && xmldata.handler.status == 200)
	{
		if (xmldata.handler.responseText != '')
		{
			var result = JSON.parse(xmldata.handler.responseText);
			fetch_js_object('favouriteitems').innerHTML = result.favouriteitems;
			fetch_js_object('favouritesellers').innerHTML = result.favouritesellers;
			fetch_js_object('favouritesearches').innerHTML = result.favouritesearches;
		}		
		xmldata.handler.abort();
	}
}
function print_favourite_items(limit)
{                        
	xmldata = new AJAX_Handler(true);
	limit = parseInt(limit);
	xmldata.onreadystatechange(fetch_favourite_items);
	xmldata.send(AJAXURL, 'do=favourites&limit=' + limit + '&s=' + ILSESSION + '&token=' + ILTOKEN);                        
}
function fetch_search_result_moreinfo()
{
	if (xmldata.handler.readyState == 4 && xmldata.handler.status == 200)
	{
		if (xmldata.handler.responseText != '')
		{
			iteminfo = xmldata.handler.responseText;
			iteminfo = iteminfo.split("|");
			fetch_js_object('ibigphoto_' + xmldata.itemid).innerHTML = iteminfo[0];
			//fetch_js_object('ipricelabel_' + xmldata.itemid).innerHTML = iteminfo[1];
			//fetch_js_object('ipricevalue_' + xmldata.itemid).innerHTML = iteminfo[2];
			if (iteminfo[3] != '')
			{
				toggle_show('ispecifics_' + xmldata.itemid);
				fetch_js_object('ispecifics_' + xmldata.itemid).innerHTML = iteminfo[3];
			}
			//fetch_js_object('itimeleft_' + xmldata.itemid).innerHTML = iteminfo[4];
			if (iteminfo[5] == '1')
			{
				toggle_show('ibidlabel_' + xmldata.itemid);
				toggle_show('ibidvalue_' + xmldata.itemid);
			}
		}
		xmldata.handler.abort();
	}
}
function print_search_result_moreinfo(itemid)
{
	xmldata = new AJAX_Handler(true);
	itemid = parseInt(itemid);
	xmldata.itemid = itemid;
	xmldata.onreadystatechange(fetch_search_result_moreinfo);
	xmldata.send(AJAXURL, 'do=searchresult&itemid=' + itemid + '&s=' + ILSESSION + '&token=' + ILTOKEN);
}
function timed_refresh(period)
{
	setTimeout('document.location.reload(true);', period);
}
function update_bid_amount_modal()
{
        price = fetch_js_object('bidamount_temp').value;
        fetch_js_object('bidamount_modal').value = price;
}
function update_bid_amount()
{
        setTimeout("update_bid_amount_modal()", 100);
}
function validate_place_bid_product(f)
{
        var Chars = "0123456789.,";
        haveerrors = 0;
        (f.bidamount.value.length < 1) ? showImage("bidamounterror", IMAGEBASE + "icons/fieldempty.gif", true) : showImage("bidamounterror", IMAGEBASE + "icons/blankimage.gif", false);
        for (var i = 0; i < f.bidamount.value.length; i++)
        {
                if (Chars.indexOf(f.bidamount.value.charAt(i)) == -1)
                {
                        alert_js(phrase['_invalid_currency_characters_only_numbers_and_a_period_are_allowed_in_this_field']);
                        haveerrors = 1;
                }
        }
        if (haveerrors != 1)
        {
		val = fetch_js_object('bidamount_modal').value;
		var bidamount = string_to_number(val);
		bidamount = parseFloat(bidamount);
		val2 = fetch_js_object('hiddenfieldminimum').value;
		var minimumbid = string_to_number(val2);
		minimumbid = parseFloat(minimumbid);
		val3 = fetch_js_object('hiddenfieldreserve').value;
		var reserve = val3;
		reserve = parseFloat(reserve);
		val4 = fetch_js_object('hiddenfieldreservemet').value;
		var reservemet = val4;
		val5 = fetch_js_object('hiddenfieldbuynow').value;
		var buynow = val5;
		buynow = parseFloat(buynow);
		val6 = fetch_js_object('hiddenfieldbuynowprice').value;
		var buynowprice = string_to_number(val6);
		buynowprice = parseFloat(buynowprice);
		if (bidamount == 'NaN' || bidamount == '' || bidamount <= '0')
		{
			alert_js(phrase['_you_have_entered_an_incorrect_bid_amount_please_try_again']);
			haveerrors = 1;
		}
		else
		{
			if (bidamount < minimumbid)
			{
				alert_js(phrase['_cannot_place_value_for_your_bid_amount_your_bid_amount_must_be_greater_than_the_minimum_bid_amount']);
				haveerrors = 1;
			}
		}
		fetch_js_object('bidamount_modal').value = bidamount;
        }
        return (!haveerrors);
}
function enlarge_listing_picture(pid)
{
	jQuery('#enlarge_picture_modal').jqm({modal: false}).jqmShow();
	var picture = fetch_js_object('currentpicture').value;
	jQuery("#original-picture").attr("src", IMAGEBASE + 'spacer.gif');
	jQuery("#original-picture").attr("width", 1);
	jQuery("#original-picture").attr("height", 1);
	jQuery("#original-picture").attr("src", originalpictures[picture]["src"]);
	var maxWidth = maxbigpicturewidth;
        var maxHeight = maxbigpictureheight;
        var ratio = 0; 
        var width = originalpictures[picture]["width"]; 
        var height = originalpictures[picture]["height"];
        if (width > maxWidth)
	{
		ratio = maxWidth / width;
		jQuery("#original-picture").attr("width", maxWidth);
		jQuery("#original-picture").attr("height", height * ratio);
		height = height * ratio;
		width = width * ratio;
        }
	else
	{
		jQuery("#original-picture").attr("width", width);
	} 
        if (height > maxHeight)
	{
		ratio = maxHeight / height;
		jQuery("#original-picture").attr("height", maxHeight);
		jQuery("#original-picture").attr("width", width * ratio); 
		width = width * ratio; 
		height = height * ratio; 
        }
	else
	{
		jQuery("#original-picture").attr("height", height);
	}
	jQuery("#original-picture").attr("alt", "");
	return false;
}
function fetch_category_questions_pulldown()
{
	if (xmldata.handler.readyState == 4 && xmldata.handler.status == 200)
	{
		if (xmldata.handler.responseText != '')
		{
			fetch_js_object('pdmdiv_' + xmldata.counter).innerHTML = xmldata.handler.responseText;
		}
		xmldata.handler.abort();
	}
}
function print_category_questions_pulldown(cid, qid, cattype, fieldname, mode, counter)
{
	xmldata = new AJAX_Handler(true);
	xmldata.counter = parseInt(counter);
	xmldata.onreadystatechange(fetch_category_questions_pulldown);
	xmldata.send(AJAXURL, 'do=categoryquestionspulldown&cid=' + cid + '&qid=' + qid + '&cattype=' + cattype + '&fieldname=' + fieldname + '&mode=' + mode + '&counter=' + counter + '&s=' + ILSESSION + '&token=' + ILTOKEN);
}
function add_display_value_option(mode, divid, cattype, cid, qid)
{
	var counter = parseInt(fetch_js_object('qcounter').value);
	if (mode == 'insert')
	{
		counter++;
		fetch_js_object('qcounter').value = counter;
		var pulldown_menu = print_category_questions_pulldown(cid, qid, cattype, 'newmultiplechoicegroup', mode, counter);
		var existinghtml = fetch_js_object(divid).innerHTML;
		jQuery('#' + divid).before(existinghtml + '<span id="pdmdiv_' + counter + '"></span>');
	}
	else if (mode == 'update')
	{
		counter++;
		fetch_js_object('qcounter').value = counter;
		var pulldown_menu = print_category_questions_pulldown(cid, qid, cattype, 'newmultiplechoicegroup', mode, counter);
		var existinghtml = fetch_js_object(divid).innerHTML;
		jQuery('#' + divid).before(existinghtml + '<span id="pdmdiv_' + counter + '"></span>');
	}
}
function change_modal_width_height(id)
{
	var width = jQuery(window).width();
	var height = jQuery(window).height();
	jQuery('#' + id).css('width', width - 60);
	jQuery('#' + id).css('height', height - 60);
};