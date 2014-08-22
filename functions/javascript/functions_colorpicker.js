/**
* Core Color Picker Javascript functions for ILance.
*
* @package      iLance\Javascript\ColorPicker
* @version	4.0.0.8059
* @author       ILance
*/
function getScrollY()
{
        var scrOfX = 0, scrOfY=0;
        
        if (typeof(window.pageYOffset) == 'number')
        {
                scrOfY = window.pageYOffset;
                scrOfX = window.pageXOffset;
        }
        else if (document.body && (document.body.scrollLeft || document.body.scrollTop))
        {
                scrOfY = document.body.scrollTop;
                scrOfX = document.body.scrollLeft;
        }
        else if (document.documentElement && (document.documentElement.scrollLeft || document.documentElement.scrollTop))
        {
                scrOfY = document.documentElement.scrollTop;
                scrOfX = document.documentElement.scrollLeft;
        }
        return scrOfY;
}
document.write("<style>.colorpicker301{width:300px;text-align:center;visibility:hidden;display:none;position:absolute;background-color:#FFF;border:solid 1px #CCC;padding:4px;z-index:999;filter:progid:DXImageTransform.Microsoft.Shadow(color=#D0D0D0,direction=135);}.o5582brd{border-bottom:solid 1px #DFDFDF;border-right:solid 1px #DFDFDF;padding:0;width:12px;height:14px;}a.o5582n66,.o5582n66,.o5582n66a{font-family:arial,tahoma,sans-serif;text-decoration:underline;font-size:9px;color:#666;border:none;}.o5582n66,.o5582n66a{text-align:center;text-decoration:none;}a:hover.o5582n66{text-decoration:none;color:#FFA500;cursor:pointer;}.a01p3{padding:1px 4px 1px 2px;background:whitesmoke;border:solid 1px #DFDFDF;}</style>");
function gett6op6()
{
        csBrHt = 0;
        if (typeof(window.innerWidth) == 'number')
        {
                csBrHt = window.innerHeight;
        }
        else if (document.documentElement && (document.documentElement.clientWidth || document.documentElement.clientHeight))
        {
                csBrHt = document.documentElement.clientHeight;
        }
        else if (document.body && (document.body.clientWidth || document.body.clientHeight))
        {
                csBrHt = document.body.clientHeight;
        }
        
        ctop = ((csBrHt/2)-132)+getScrollY();
        
        return ctop;
}
function getLeft6()
{
        var csBrWt = 0;
        
        if (typeof(window.innerWidth) == 'number')
        {
                csBrWt = window.innerWidth;
        }
        else if (document.documentElement && (document.documentElement.clientWidth || document.documentElement.clientHeight))
        {
                csBrWt = document.documentElement.clientWidth;
        }
        else if (document.body && (document.body.clientWidth || document.body.clientHeight))
        {
                csBrWt = document.body.clientWidth;
        }
        
        cleft = (csBrWt/2)-125;
        
        return cleft;
}
var nocol1="&#78;&#79;&#32;&#67;&#79;&#76;&#79;&#82;",clos1="&#67;&#76;&#79;&#83;&#69;",tt6="&#70;&#82;&#69;&#69;&#45;&#67;&#79;&#76;&#79;&#82;&#45;&#80;&#73;&#67;&#75;&#69;&#82;&#46;&#67;&#79;&#77;",hm6="&#104;&#116;&#116;&#112;&#58;&#47;&#47;&#119;&#119;&#119;&#46;";hm6+=tt6;tt6="&#80;&#79;&#87;&#69;&#82;&#69;&#68;&#32;&#98;&#121;&#32;&#70;&#67;&#80;";
function setCCbldID6(objID, val)
{
        fetch_js_object(objID).value = val;
}
function setCCbldSty6(objID,prop,val)
{
        switch(prop)
        {
                case "bc":
                    if(objID != 'none')
                    {
                            fetch_js_object(objID).style.backgroundColor = val;
                    }
                break;
            
                case "vs":
                    fetch_js_object(objID).style.visibility = val;
                break;
            
                case "ds":
                    fetch_js_object(objID).style.display = val;
                break;
            
                case "tp":
                    fetch_js_object(objID).style.top = val;
                break;
            
                case "lf":
                    fetch_js_object(objID).style.left = val;
                break;
        }
}
function putOBJxColor6(OBjElem,Samp,pigMent)
{
        if (pigMent != 'x')
        {
                setCCbldID6(OBjElem,pigMent);
                setCCbldSty6(Samp,'bc', pigMent);
        }
        
        setCCbldSty6('colorpicker301','vs','hidden');
        setCCbldSty6('colorpicker301','ds','none');
}
function showColorGrid3(OBjElem,Sam)
{
        var objX = new Array('00','33','66','99','CC','FF');
        var c = 0;
        var z = '"' + OBjElem + '","' + Sam + '",""';
        var xl = '"' + OBjElem + '","' + Sam + '","x"';
        var mid = '';
        
        mid += '<center><table border="0" cellpadding="9" cellspacing="1" class="tableborder">';
        mid += "<tr><td colspan='18' align='left' class='alt2'>&nbsp;Color Selection Palette</td></tr>";
        mid += "<tr><td colspan='18' align='center' style='margin:0;padding:2px;height:14px;'><input class='input' type='text' size='10' id='o5582n66' value='#FFFFFF'><input class='sample_swatch' type='text' id='o5582n66a' onclick='javascript:alert(\"click on selected swatch below...\");' value='' style='height:18px'>&nbsp;&nbsp;<input type='button' value='No Color' class='buttons' onclick='putOBJxColor6("+z+")' /> <input type='button' value='Close' class='buttons' onclick='putOBJxColor6("+xl+")'></td></tr>";
        
        mid += "<tr>";
        
        var br = 1;
        for (o=0; o<6; o++)
        {
                mid += '</tr><tr>';
                
                for (y=0; y<6; y++)
                {
                        if (y == 3)
                        {
                                mid += '</tr><tr>';
                        }
                        
                        for (x=0; x<6; x++)
                        {
                                var grid='';
                                grid = objX[o] + objX[y] + objX[x];
                                
                                var b= "'" + OBjElem + "', '" + Sam + "','#" + grid + "'";
                                mid += '<td class="o5582brd" style="background-color:#'+grid+'"><a class="o5582n66"  href="javascript:onclick=putOBJxColor6('+b+');" onmouseover=javascript:fetch_js_object("o5582n66").value="#'+grid+'";javascript:fetch_js_object("o5582n66a").style.backgroundColor="#'+grid+'";  title="#'+grid+'"><div style="width:12px;height:14px;"></div></a></td>';
                                c++;
                        }
                }
        }
        
        mid += '</tr></table>';
        
        var objX = new Array('0','3','6','9','C','F');
        var c = 0;
        var z = '"'+OBjElem+'","'+Sam+'",""';var xl='"'+OBjElem+'","'+Sam+'","x"';
        mid += '<table bgcolor="#FFFFFF" border="0" cellpadding="0" cellspacing="0" style="border:solid 1px #F0F0F0;padding:1px;"><tr>';
        
        var br = 0;
        
        for (y=0; y<6; y++)
        {
                for (x=0; x<6; x++)
                {
                        if (br == 18)
                        {
                                br = 0;
                                
                                mid += '</tr><tr>';
                        }
                        
                        br++;
                        
                        var grid = '';
                        grid = objX[y] + objX[x] + objX[y] + objX[x] + objX[y] + objX[x];
                        
                        var b = "'"+OBjElem+"', '"+Sam+"','#"+grid+"'";
                        
                        mid += '<td class="o5582brd" style="background-color:#'+grid+'"><a class="o5582n66"  href="javascript:onclick=putOBJxColor6('+b+');" onmouseover=javascript:fetch_js_object("o5582n66").value="#'+grid+'";javascript:fetch_js_object("o5582n66a").style.backgroundColor="#'+grid+'";  title="#'+grid+'"><div style="width:12px;height:14px;"></div></a></td>';
                        c++;
                }
        }
        
        mid += "</tr>";
        mid += '</table></center>';
        
        setCCbldSty6('colorpicker301','tp','100px');
        
        fetch_js_object('colorpicker301').style.top = gett6op6();
        fetch_js_object('colorpicker301').style.left = getLeft6();
        
        setCCbldSty6('colorpicker301','vs','visible');
        setCCbldSty6('colorpicker301','ds','block');
        fetch_js_object('colorpicker301').innerHTML = mid;
}
function is_swatch_transparent(value)
{
	if (value == "" || value == "none" || value == "transparent")
	{
		return true;
	}
	else
	{
		return false;
	}
}
function preview_color_swatch(elm)
{
        var colorElement = fetch_js_object('color_' + elm);
	var previewElement = fetch_js_object('preview_' + elm);
        
	var cssRegExp = new RegExp(/url\(('|"|)((http:\/\/|\/)?)(.*)\1\)/i);

	if (is_swatch_transparent(colorElement.value))
	{
		previewElement.style.background = "none";
	}
	else
	{
		var cssValue = colorElement.value;
		var matches;
		if (matches = colorElement.value.match(cssRegExp))
		{
			if (typeof matches[3] == "undefined" || matches[3] == "")
			{
				cssValue = colorElement.value.replace(matches[4], (ILBASE + matches[4]));
			}
		}
		// try/catch requires a version 5 browser
		try
		{
			previewElement.style.background = cssValue;
		}
		catch(csserror)
		{
                        alert("Error: '" + cssValue + "' is not a valid CSS entry.");
		}
	}
}