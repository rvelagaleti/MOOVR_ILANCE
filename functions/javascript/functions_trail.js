/**
* Core javascript image popup with trailing movement functions within ILance.
*
* @package      iLance\Javascript\Trail
* @version	4.0.0.8059
* @author       Walter Zorn
*/
// image x,y offsets from cursor position in pixels. Enter 0,0 for no offset
var offsetfrommouse = [20,0]; 

// duration in seconds image should remain visible. 0 for always.
var displayduration = 0; 

// maximum image size.
var currentimageheight = 410;	

// maximum image size.
var currentimagewidth = 540;

var myTimer;

var t_id = setInterval(animate, 20);
var pos = 0;
var dir = 2;
var len = 0;

function animate()
{
	var elem = fetch_js_object("progress");
	if (elem != null) 
	{
		if (pos==0) len += dir;
		if (len>32 || pos>79) pos += dir;
		if (pos>79) len -= dir;
		if (pos>79 && len==0) pos=0;
		elem.style.left = pos;
		elem.style.width = len;
	}
}

function remove_loading() 
{
	this.clearInterval(t_id);
	var targelem = fetch_js_object("loader");
	targelem.style.display = 'none';
	targelem.style.visibility = 'hidden';
	var t_id = setInterval(animate, 60);
}

function gettrailobj()
{
	if (document.getElementById)
	{
		return document.getElementById("preview_div").style
	}
	else if (document.all)
	{
		return document.all.trailimagid.style
	}
}

function gettrailobjnostyle()
{
	if (document.getElementById)
	{
		return document.getElementById("preview_div")
	}
	else if (document.all)
	{
		return document.all.trailimagid
	}
}

function truebody()
{
	return (!window.opera && document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body
}

function hidetrail()
{
	clearTimeout(myTimer);
	gettrailobj().display = "none";
	document.onmousemove = ""
	gettrailobj().left = "-1000px"
}

function showtrail(imagename, title, showthumb)
{
	myTimer = setTimeout('do_showtrail(\'' + imagename + '\', \'' + title + '\', \'' + showthumb + '\');', 700);
	//do_showtrail(imagename, title, showthumb);
}

function do_showtrail(imagename, title, showthumb)
{
	var docwidth = document.all? truebody().scrollLeft+truebody().clientWidth : pageXOffset+window.innerWidth - offsetfrommouse[0]
	var docheight = document.all? Math.min(truebody().scrollHeight, truebody().clientHeight) : Math.min(window.innerHeight)

	if ((navigator.userAgent.indexOf("Firefox")!=-1 || (navigator.userAgent.indexOf("Opera")==-1 && navigator.appVersion.indexOf("MSIE")!=-1)) && (docwidth > 650 && docheight > 500))
	{
		document.onmousemove = followmouse;
		
		newHTML = '<div class="border_preview"><div id="loader_container"><div id="loader"><div align="center">Loading preview...</div><div id="loader_bg"><div id="progress"></div></div></div></div>';
		newHTML = newHTML + '<div class="black" style="font-size:16px; padding:9px; font-weight:bold">' + title + '</div>';
		if (showthumb > 0)
		{
			newHTML = newHTML + '<div align="center" style="padding: 8px 10px 17px 10px;"><img onload="javascript:remove_loading();" src="' + imagename + '" border="0" alt="" /></div>';
		}		
		newHTML = newHTML + '</div>';
		if(navigator.userAgent.indexOf("Firefox")==-1)
		{
			newHTML = newHTML + '<iframe src="about:blank" scrolling="no" frameborder="0" width="390" height="380"></iframe>';
		}
		
		gettrailobjnostyle().innerHTML = newHTML;
		gettrailobj().display = "block";
	}

	function followmouse(e)
	{
		var xcoord = offsetfrommouse[0]
		var ycoord = offsetfrommouse[1]

		var docwidth = document.all? truebody().scrollLeft + truebody().clientWidth : pageXOffset + window.innerWidth - offsetfrommouse[0]
		var docheight = document.all? Math.min(truebody().scrollHeight, truebody().clientHeight) : Math.min(window.innerHeight)

		if (typeof e != "undefined")
		{
			if (docwidth - e.pageX < currentimagewidth)
			{	
				if (navigator.userAgent.indexOf("Firefox")!=-1)
				{
					xcoord = e.pageX - xcoord - currentimagewidth + 2 * offsetfrommouse[0]
				}
				else
				{
					xcoord = e.pageX - xcoord - currentimagewidth + 6 * offsetfrommouse[0] ;
				} 
			} 
			else 
			{
				xcoord += e.pageX;
			}
			
			if (docheight - e.pageY < (currentimageheight + 110))
			{
				ycoord += e.pageY - Math.max(0, (110 + currentimageheight + e.pageY - docheight - truebody().scrollTop));
			} 
			else 
			{
				ycoord += e.pageY;
			}
	
		} 
		else if (typeof window.event != "undefined")
		{
			if (docwidth - event.clientX < currentimagewidth)
			{
				// Move to the left side of the cursor
				xcoord = event.clientX + truebody().scrollLeft - xcoord - currentimagewidth + -1 * offsetfrommouse[0]; 
			} 
			else 
			{
				xcoord += truebody().scrollLeft + event.clientX
			}			
			if (docheight - event.clientY < (currentimageheight + 110))
			{
				ycoord += event.clientY + truebody().scrollTop - Math.max(0, (110 + currentimageheight + event.clientY - docheight));
			} 
			else 
			{
				ycoord += truebody().scrollTop + event.clientY;
			}
		}
	
		var docwidth = document.all? truebody().scrollLeft + truebody().clientWidth : pageXOffset + window.innerWidth - offsetfrommouse[0]
		var docheight = document.all? Math.max(truebody().scrollHeight, truebody().clientHeight) : Math.max(document.body.offsetHeight, window.innerHeight)
		
		if (ycoord < 0) 
		{ 
			ycoord = ycoord*-1; 
		}

		gettrailobj().left = xcoord + "px"
		gettrailobj().top = ycoord + "px"
	}
}