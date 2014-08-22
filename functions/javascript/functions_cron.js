/**
* Core Automated Tasks AJAX Javascript functions for ILance.  This prevents the page from
* stopping due to a behind the scenes scheduled event that is currently in progress.
*
* @package      iLance\Javascript\Cron
* @version	4.0.0.8059
* @author       ILance
*/
function iL_Tasks(location, random)
{
	if (AJAX_Compatible)
        {
		new iL_Cron(location, random);
	}
}
function iL_Cron(_location, _random)
{
	this.ilance_xml = null;
	this.link = _location;
	this.httpurl = location.hostname;
	this.random = _random;
	this.url_safe = function ()
        {
		var needs = (this.link.indexOf('//www.')==-1)? false:true;
		var has = (this.httpurl.indexOf('www.')==-1)? false:true;
		if(needs && !has)
                {
			this.link = this.link.replace('http://www.','http://');
		}
                else if(!needs && has)
                {
			this.link = this.link.replace('http://','http://www.');
		}
	}
	this.do_cron = function()
        {
		if (!this.ilance_xml)
                {
			this.ilance_xml = new AJAX_Handler(true);
		}
		this.url_safe();
		this.ilance_xml.onreadystatechange(this.onreadystatechange);
		this.ilance_xml.send
		(
			this.link + '?rand=' + this.random,
			'rand=' + this.random
		);
	}
	var ilance = this;
	this.onreadystatechange = function()
        {
		if (ilance.ilance_xml.handler.readyState == 4 && ilance.ilance_xml.handler.status == 200 && ilance.ilance_xml.handler.responseText)
                {
			if (checkie)
                        {
				ilance.ilance_xml.handler.abort();
			}
		}
	}
	this.do_cron();
}