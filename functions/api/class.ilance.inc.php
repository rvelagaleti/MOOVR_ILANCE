<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 4.0.0 Build 8059
|| # -------------------------------------------------------------------- # ||
|| # Customer License # H7LhzCqPSNEABnY
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000–2014 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/

/**
* ILance class to perform the majority of the main common ILance functions.
*
* @package      iLance\iLance
* @version      4.0.0.8059
* @author       ILance
*/
class ilance
{
        /**
	* $_GET, $_POST and $_COOKIE array
	*
	* @var	    $GPC
	*/
	var $GPC = array();
        
        /**
	* Will store {apihook[xxx]}'s that are being loaded to prevent double loading
	*
	* @var	    $apihooks
	*/
        var $apihooks = array();
        
        /**
	* Will store $ilconfig as $this->config['xxx'] (will use more in future)
	*
	* @var	    $config
	*/
	var $config = array();
        
        /**
	* Will store all plugins currently installed into an array for future processing
	*
	* @var	    $pluginsxml
	*/
        var $plugins;
    
        /**
	* Constructor
	*/
	function __construct()
	{
		// set main ilance product details
		$this->config['ilversion'] = ILANCEVERSION;
		$this->config['licensekey'] = LICENSEKEY;
		if (get_magic_quotes_gpc())
		{
			$this->magicquotes = 1;
			$this->strip_slashes_array($_REQUEST);
			$this->strip_slashes_array($_POST);
			$this->strip_slashes_array($_GET);
			$this->strip_slashes_array($_COOKIE);
		}
		@ini_set('magic_quotes_runtime', 0);
		$arrays = array_merge($_GET, $_POST);
		$this->parse_incoming($arrays);
		if (@ini_get('register_globals') OR !@ini_get('gpc_order'))
		{
			$this->unset_globals($_POST);
			$this->unset_globals($_GET);
			$this->unset_globals($_FILES);
		}
	}
	public function __get($name)
	{
		global $ilance;
		$arr = explode("_", $name);
		if (is_array($arr) AND count($arr) > 1)
		{
			$objmain = construct_object('api.' . $arr[0]);
			$ilance->$arr[0] = $objmain;
		}
		$obj = construct_object('api.' . $name);
		$ilance->$name = $obj;
		return $obj;
	}
	/**
	* $_POST request site forgery protection and whitelist control
	*/
	function post_request_protection()
	{
		global $ilconfig;
		if (mb_strtolower($_SERVER['REQUEST_METHOD']) == 'post')
                {
			// default referrers should be: paypal.com moneybookers.com authorize.net cashu.com plugnpay.com
			// please see AdminCP > Global Security Settings to update this list
                        $acceptedreferrers = $ilconfig['post_request_whitelist'];
                        if (!empty($_ENV['HTTP_HOST']) OR !empty($_SERVER['HTTP_HOST']))
                        {
                                $httphost = $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : $_ENV['HTTP_HOST'];
                        }
                        else if (!empty($_SERVER['SERVER_NAME']) OR !empty($_ENV['SERVER_NAME']))
                        {
                                $httphost = $_SERVER['SERVER_NAME'] ? $_SERVER['SERVER_NAME'] : $_ENV['SERVER_NAME'];
                        }
                        if (!empty($httphost) AND !empty($_SERVER['HTTP_REFERER']))
                        {
                                $httphost = preg_replace('#:80$#', '', trim($httphost));
                                $parts = @parse_url($_SERVER['HTTP_REFERER']);
                                $port = !empty($parts['port']) ? intval($parts['port']) : '80';
                                $host = $parts['host'] . ((!empty($port) AND $port != '80') ? ":$port" : '');
                                $allowdomains = preg_split('#\s+#', $acceptedreferrers, -1, PREG_SPLIT_NO_EMPTY);
                                $allowdomains[] = preg_replace('#^www\.#i', '', $httphost);
                                $passcheck = false;
                                foreach ($allowdomains AS $allowhost)
                                {
                                        if (preg_match('#' . preg_quote($allowhost, '#') . '$#siU', $host))
                                        {
                                                $passcheck = true;
                                                break;
                                        }
                                }
                                unset($allowdomains);
                                if ($passcheck == false)
                                {
                                        $message = 'POST request could not find your domain in the whitelist. Please contact support for further information.';
                                        $template = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
        <title>POST request error</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <style type="text/css">
        <!--	
        body { background-color: white; color: black; }
        #container { width: 400px; }
        #message   { width: 400px; color: black; background-color: #FFFFCC; }
        #bodytitle { font: 13pt/15pt verdana, arial, sans-serif; height: 35px; vertical-align: top; }
        .bodytext  { font: 8pt/11pt verdana, arial, sans-serif; }
        a:link     { font: 8pt/11pt verdana, arial, sans-serif; color: red; }
        a:visited  { font: 8pt/11pt verdana, arial, sans-serif; color: #4e4e4e; }
        -->
        </style>
</head>
<body>
<table cellpadding="3" cellspacing="5" id="container">
<tr>
        <td id="bodytitle" width="100%">POST Request Error</td>
</tr>
<tr>
        <td class="bodytext" colspan="2">' . $message . '</td>
</tr>
<tr>
        <td colspan="2"><hr /></td>
</tr>
<tr>
        <td class="bodytext" colspan="2">
                Please try the following:
                <ul>
                        <li>Click the <a href="javascript:history.back(1)">Back</a> button to try another link.</li>
                </ul>
        </td>
</tr>
<tr>
        <td class="bodytext" colspan="2">The technical staff have been notified of the error.  We apologise for any inconvenience.</td>
</tr>
</table>

</body>
</html>';
                                        // tell the search engines that our service is temporarily unavailable to prevent indexing db errors
                                        header('HTTP/1.1 503 Service Temporarily Unavailable');
                                        header('Status: 503 Service Temporarily Unavailable');
                                        header('Retry-After: 3600');
                                        die($template);
                                }
                        }
                }
	}
	/**
	* Function to parse any incoming input and tranform it into our reusable $ilance->GPC array used in the software.
	*
	* @param       array         array
	* 
        * @return      nothing
	*/
        function parse_incoming($array)
	{
		if (!is_array($array))
		{
			return;
		}
                
		foreach ($array AS $key => $val)
		{
			$this->GPC["$key"] = $val;
		}
	}
	/**
	* Function wrapper for the xx_escape_string function for escaping valid sql input
	*
	* @param       string        string to escape
	* 
        * @return      string        Returns xx_escape_string value
	*/
        function escape_string($text = '')
	{
		global $ilance;
		return $ilance->db->escape_string($text);
	}
	/**
	* Function to strip any slashes within a regular or recursive array
	*
	* @param       array         array
	* 
        * @return      nothing
	*/
        function strip_slashes_array(&$array)
	{
		foreach ($array AS $key => $val)
		{
			if (is_array($array[$key]))
			{
				$this->strip_slashes_array($array[$key]);
			}
			else
			{
				$array[$key] = stripslashes($array[$key]);
			}
		}
	}
	/**
	* Function to unset $_GLOBAL's from being set by users via URL manipulation
	*
	* @param       array         array value to clean
	* 
        * @return      nothing
	*/
        function unset_globals($array)
	{
		if (!is_array($array))
		{
			return;
		}
		foreach (array_keys($array) AS $key)
		{
			unset($GLOBALS["$key"]);
		}
	}
	
	/**
	* Function to clean $_GLOBAL, $_POST and $_COOKIE input
	*
	* @param       string        g, p or c values 
	* @param       array         array or value to clean
	* @param       string        variable clean type selector (ie: TYPE_INT, TYPE_NUM, etc)
	* 
        * @return      nothing
	*/
        function clean_gpc($gpc, $variable, $type = '')
	{
		$boolmethods = array('1', 'yes', 'y', 'true');
                if (empty($type))
                { // handling input in main scripts (abuse.php, etc)
                        foreach ($variable as $fieldname => $type)
                        {
                                switch ($type)
                                {
                                        case 'TYPE_INT':
                                        {
                                                if ($gpc == 'g')
                                                {
                                                        $this->GPC["$fieldname"] = intval($_GET["$fieldname"]);	
                                                }
                                                else if ($gpc == 'p')
                                                {
                                                        $this->GPC["$fieldname"] = intval($_POST["$fieldname"]);	
                                                }
                                                else if ($gpc == 'c')
                                                {
                                                        $this->GPC["$fieldname"] = intval($_COOKIE["$fieldname"]);	
                                                }
                                                break;
                                        }                                
                                        case 'TYPE_NUM':
                                        {
                                                if ($gpc == 'g')
                                                {
                                                        $this->GPC["$fieldname"] = strval($_GET["$fieldname"]) + 0;	
                                                }
                                                else if ($gpc == 'p')
                                                {
                                                        $this->GPC["$fieldname"] = strval($_POST["$fieldname"]) + 0;
                                                }
                                                else if ($gpc == 'c')
                                                {
                                                        $this->GPC["$fieldname"] = strval($_COOKIE["$fieldname"]) + 0;
                                                }
                                                break;
                                        }                                
                                        case 'TYPE_STR':
                                        {
                                                if ($gpc == 'g')
                                                {
                                                        $this->GPC["$fieldname"] = trim(strval($_GET["$fieldname"]));
                                                }
                                                else if ($gpc == 'p')
                                                {
                                                        $this->GPC["$fieldname"] = trim(strval($_POST["$fieldname"]));
                                                }
                                                else if ($gpc == 'c')
                                                {
                                                        $this->GPC["$fieldname"] = trim(strval($_COOKIE["$fieldname"]));
                                                }
                                                break;
                                        }                                
                                        case 'TYPE_NOTRIM':
                                        {
                                                if ($gpc == 'g')
                                                {
                                                        $this->GPC["$fieldname"] = strval($_GET["$fieldname"]);
                                                }
                                                else if ($gpc == 'p')
                                                {
                                                        $this->GPC["$fieldname"] = strval($_POST["$fieldname"]);
                                                }
                                                else if ($gpc == 'c')
                                                {
                                                        $this->GPC["$fieldname"] = strval($_COOKIE["$fieldname"]);
                                                }
                                                break;
                                        }                                
                                        case 'TYPE_NOHTML':
                                        {
                                                if ($gpc == 'g')
                                                {
                                                        $this->GPC["$fieldname"] = htmlspecialchars_uni(trim(strval($_GET["$fieldname"])));
                                                }
                                                else if ($gpc == 'p')
                                                {
                                                        $this->GPC["$fieldname"] = htmlspecialchars_uni(trim(strval($_POST["$fieldname"])));
                                                }
                                                else if ($gpc == 'c')
                                                {
                                                        $this->GPC["$fieldname"] = htmlspecialchars_uni(trim(strval($_COOKIE["$fieldname"])));
                                                }
                                                break;
                                        }                                
                                        case 'TYPE_BOOL':
                                        {
                                                if ($gpc == 'g')
                                                {
                                                        $this->GPC["$fieldname"] = in_array(mb_strtolower($_GET["$fieldname"]), $boolmethods) ? 1 : 0; 
                                                }
                                                else if ($gpc == 'p')
                                                {
                                                        $this->GPC["$fieldname"] = in_array(mb_strtolower($_POST["$fieldname"]), $boolmethods) ? 1 : 0; 
                                                }
                                                else if ($gpc == 'c')
                                                {
                                                        $this->GPC["$fieldname"] = in_array(mb_strtolower($_COOKIE["$fieldname"]), $boolmethods) ? 1 : 0; 
                                                }
                                                break;
                                        }                                
                                        case 'TYPE_ARRAY':
                                        {
                                                if ($gpc == 'g')
                                                {
                                                        $this->GPC["$fieldname"] = (is_array($_GET["$fieldname"])) ? $fieldname : array();
                                                }
                                                else if ($gpc == 'p')
                                                {
                                                        $this->GPC["$fieldname"] = (is_array($_POST["$fieldname"])) ? $fieldname : array();
                                                }
                                                else if ($gpc == 'c')
                                                {
                                                        $this->GPC["$fieldname"] = (is_array($_COOKIE["$fieldname"])) ? $fieldname : array();
                                                }
                                                break;
                                        }
                                }
                        }
                }
                else
                { // handling input in datamanger scripts (class.datamanager_xxx.inc.php, etc)
                        switch ($type)
                        {
                                case 'TYPE_INT':
                                {
                                        if ($gpc == 'g')
                                        {
                                                $this->GPC["$variable"] = intval($_GET["$variable"]);	
                                        }
                                        else if ($gpc == 'p')
                                        {
                                                $this->GPC["$variable"] = intval($_POST["$variable"]);	
                                        }
                                        else if ($gpc == 'c')
                                        {
                                                $this->GPC["$variable"] = intval($_COOKIE["$variable"]);	
                                        }
                                        else if ($gpc == 's')
                                        {
                                                $this->GPC["$variable"] = intval($variable);
                                        }
                                        break;
                                }                        
                                case 'TYPE_NUM':
                                {
                                        if ($gpc == 'g')
                                        {
                                                $this->GPC["$variable"] = strval($_GET["$variable"]) + 0;	
                                        }
                                        else if ($gpc == 'p')
                                        {
                                                $this->GPC["$variable"] = strval($_POST["$variable"]) + 0;
                                        }
                                        else if ($gpc == 'c')
                                        {
                                                $this->GPC["$variable"] = strval($_COOKIE["$variable"]) + 0;
                                        }
                                        else if ($gpc == 's')
                                        {
                                                $this->GPC["$variable"] = strval($variable) + 0;
                                        }
                                        break;
                                }                        
                                case 'TYPE_STR':
                                {
                                        if ($gpc == 'g')
                                        {
                                                $this->GPC["$variable"] = trim(strval($_GET["$variable"]));
                                        }
                                        else if ($gpc == 'p')
                                        {
                                                $this->GPC["$variable"] = trim(strval($_POST["$variable"]));
                                        }
                                        else if ($gpc == 'c')
                                        {
                                                $this->GPC["$variable"] = trim(strval($_COOKIE["$variable"]));
                                        }
                                        else if ($gpc == 's')
                                        {
                                                $this->GPC["$variable"] = trim(strval($variable));
                                        }
                                        break;
                                }                        
                                case 'TYPE_NOTRIM':
                                {
                                        if ($gpc == 'g')
                                        {
                                                $this->GPC["$variable"] = strval($_GET["$variable"]);
                                        }
                                        else if ($gpc == 'p')
                                        {
                                                $this->GPC["$variable"] = strval($_POST["$variable"]);
                                        }
                                        else if ($gpc == 'c')
                                        {
                                                $this->GPC["$variable"] = strval($_COOKIE["$variable"]);
                                        }
                                        else if ($gpc == 's')
                                        {
                                                $this->GPC["$variable"] = strval($variable);
                                        }
                                        break;
                                }                        
                                case 'TYPE_NOHTML':
                                {
                                        if ($gpc == 'g')
                                        {
                                                $this->GPC["$variable"] = htmlspecialchars_uni(trim(strval($_GET["$variable"])));
                                        }
                                        else if ($gpc == 'p')
                                        {
                                                $this->GPC["$variable"] = htmlspecialchars_uni(trim(strval($_POST["$variable"])));
                                        }
                                        else if ($gpc == 'c')
                                        {
                                                $this->GPC["$variable"] = htmlspecialchars_uni(trim(strval($_COOKIE["$variable"])));
                                        }
                                        else if ($gpc == 's')
                                        {
                                                $this->GPC["$variable"] = htmlspecialchars_uni(trim(strval($variable)));
                                        }
                                        break;
                                }                        
                                case 'TYPE_BOOL':
                                {
                                        if ($gpc == 'g')
                                        {
                                                $this->GPC["$variable"] = in_array(mb_strtolower($_GET["$variable"]), $boolmethods) ? 1 : 0; 
                                        }
                                        else if ($gpc == 'p')
                                        {
                                                $this->GPC["$variable"] = in_array(mb_strtolower($_POST["$variable"]), $boolmethods) ? 1 : 0; 
                                        }
                                        else if ($gpc == 'c')
                                        {
                                                $this->GPC["$variable"] = in_array(mb_strtolower($_COOKIE["$variable"]), $boolmethods) ? 1 : 0; 
                                        }
                                        else if ($gpc == 's')
                                        {
                                                $this->GPC["$variable"] = in_array(mb_strtolower($variable), $boolmethods) ? 1 : 0; 
                                        }
                                        break;
                                }                        
                                case 'TYPE_ARRAY':
                                {
                                        if ($gpc == 'g')
                                        {
                                                $this->GPC["$variable"] = (is_array($_GET["$variable"])) ? $variable : array();
                                        }
                                        else if ($gpc == 'p')
                                        {
                                                $this->GPC["$variable"] = (is_array($_POST["$variable"])) ? $variable : array();
                                        }
                                        else if ($gpc == 'c')
                                        {
                                                $this->GPC["$variable"] = (is_array($_COOKIE["$variable"])) ? $variable : array();
                                        }
                                        else if ($gpc == 's')
                                        {
                                                $this->GPC["$variable"] = (is_array($variable)) ? $variable : array();
                                        }
                                        break;
                                }
                        }
                        if ($gpc == 's')
                        {
                                return $this->GPC["$variable"];
                        }        
                }
	}	
	/**
	* Function to connect to the ilance.com web site to fetch the latest version of any specific add-on product supported by ILance.
	*
	* @param       string        version checkup url (ie: http://www.ilance.com/lancebb/versioncheck)
	* 
        * @return      string        Returns formatted HTML or PHP code to be parsed inline as called.
	*/
        function latest_addon_version($versioncheckurl = '')
	{
                global $ilconfig, $phrase;
                $version = '-';
		return $version;
                if (defined('LOCATION') AND LOCATION == 'admin' AND !empty($versioncheckurl) AND defined('VERSIONCHECK') AND VERSIONCHECK)
                {
                        // may cause slight delay for 1 or 2 seconds to grab latest version
                        $fp = @fopen($versioncheckurl, 'r');
                        $version = trim(@fread($fp, 16));
                        @fclose($fp);                    
                        if (mb_strlen($version) > 5)
                        {
                                $version = '-';
                        }
                }
                if (empty($versioncheckurl))
                {
                        $versioncheckurl = 'javascript:void(0);';
                }
                return '<span class="smaller gray"><a href="' . $versioncheckurl . '" target="_blank">' . $version . '</a></span>';
        }
	
	/**
	* Function to fetch the phrases count for an add-on product
	*
	* @param       string        addon name (simple version, i.e.: lanceads, lancekb, stores, wantads, etc)
	* 
        * @return      string        Returns formatted number (i.e.: 3,201)
	*/
        function addon_phrase_count($addon = '')
	{
                global $ilance, $ilconfig, $phrase, $ilpage;
                $count = '-';
                if (defined('LOCATION') AND LOCATION == 'admin' AND !empty($addon))
                {
                        $sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "COUNT(*) AS count
				FROM " . DB_PREFIX . "language_phrases
				WHERE phrasegroup = '" . $ilance->db->escape_string($addon) . "'
			");
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			// todo: cache values!!
			if ($res['count'] <= 0)
			{
				$count = '-';
			}
			else
			{
				$count = '<span class="smaller gray"><a href="' . $ilpage['language'] . '?cmd=search&amp;languageid=' . $ilconfig['globalserverlanguage_defaultlanguage'] . '&amp;phrasegroup=' . $addon . '&amp;limit=10">' . number_format($res['count']) . '</a></span>';
			}
                }
                return $count;
        }
	
	/**
	* Function to fetch the css element count for an add-on product
	*
	* @param       string        addon name (simple version, i.e.: lanceads, lancekb, stores, wantads, etc)
	* 
        * @return      string        Returns formatted number (i.e.: 3,201)
	*/
        function addon_css_count($addon = '')
	{
                global $ilance, $ilconfig, $phrase, $ilpage;
                $count = '-';
                if (defined('LOCATION') AND LOCATION == 'admin' AND !empty($addon))
                {
                }
                return $count;
        }
	
	/**
	* Function to fetch the email templates count for an add-on product
	*
	* @param       string        addon name (simple version, i.e.: lanceads, lancekb, stores, wantads, etc)
	* 
        * @return      string        Returns formatted number (i.e.: 3,201)
	*/
        function addon_email_count($addon = '')
	{
                global $ilance, $ilconfig, $phrase, $ilpage;
                $count = '-';
                if (defined('LOCATION') AND LOCATION == 'admin' AND !empty($addon))
                {
                        $sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "COUNT(*) AS count
				FROM " . DB_PREFIX . "email
				WHERE product = '" . $ilance->db->escape_string($addon) . "'
			");
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			// todo: cache values!!
			if ($res['count'] <= 0)
			{
				$count = '-';
			}
			else
			{
				$count = '<span class="smaller gray"><a href="' . $ilpage['settings'] . '?cmd=emailtemplates&amp;subcmd=search&amp;product=' . $addon . '">' . number_format($res['count']) . '</a></span>';
			}
                }
                return $count;
        }
	
	/**
	* Function to fetch the automated tasks count for an add-on product
	*
	* @param       string        addon name (simple version, i.e.: lanceads, lancekb, stores, wantads, etc)
	* 
        * @return      string        Returns formatted number (i.e.: 3,201)
	*/
        function addon_task_count($addon = '')
	{
                global $ilance, $ilconfig, $phrase, $ilpage;
                $count = '-';
                if (defined('LOCATION') AND LOCATION == 'admin' AND !empty($addon))
                {
                        $sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "COUNT(*) AS count
				FROM " . DB_PREFIX . "cron
				WHERE product = '" . $ilance->db->escape_string($addon) . "'
			");
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			// todo: cache values!!
			if ($res['count'] <= 0)
			{
				$count = '-';
			}
			else
			{
				$count = '<span class="smaller gray"><a href="' . $ilpage['settings'] . '?cmd=automation&amp;subcmd=search&amp;product=' . $addon . '">' . number_format($res['count']) . '</a></span>';
			}
                }
                return $count;
        }
        
        /**
	* Function to search and locate any inline php or html code to be parsed within an official ILance api hook.
	*
	* @param       string        api location hook name (ie: init_configuration_end)
	* 
        * @return      string        Returns formatted HTML or PHP code to be parsed inline as called.
	*/
        function api($location = '')
        {
                global $ilance;
		$ilance->timer->start();
                if ((defined('DISABLE_PLUGINAPI') AND DISABLE_PLUGINAPI) OR empty($location))
                {
                        return false;
                }
                if (!in_array($location, $this->apihooks))
                {
                        $this->apihooks["$location"] = true;
                }
                $foundinlinecode = $foundinlinehtml = 0;
		if (($plugincode = $ilance->cache->fetch("api_$location")) === false)
		{
			if ($this->plugins == null)
			{
				$this->plugins = $this->fetch_installed_plugins();
			}
			$plugincode = '';
			foreach ($this->plugins AS $plugs)
			{
				// each array denotes a new plugin_*.xml file loaded from the xml folder
				if (!empty($plugs) AND is_array($plugs))
				{
					// perhaps this plugin_*.xml file contains multiple <plug> calls in the same plugin file
					// this will return at least 1 plug array [attached to a specific inline api hook] (if not we'll skip)
					foreach ($plugs AS $plugin)
					{
						if (is_array($plugin))
						{
							foreach ($plugin AS $plugkey => $plugvalue)
							{
								if (($plugkey == 'key' OR $plugkey == 'addon' OR $plugkey == 'title' OR $plugkey == 'api' OR $plugkey == 'php' OR $plugkey == 'html') AND !is_array($plugvalue))
								{
									// plugin_*.xml file contains a single <plug> tags
									if ($location == $plugin['api'])
									{
										if (empty($plugin['html']) AND !empty($plugin['php']))
										{
											$plugincode .= stripslashes($plugin['php']);
											$foundinlinecode++;
											break;
										}
										else if  (!empty($plugin['html']) AND empty($plugin['php']))
										{
											$plugincode .= stripslashes($plugin['html']);
											$foundinlinehtml++;
											break;
										}
									}
								}
								else
								{
									// plugin_*.xml file contains multiple <plug> tags
									foreach ($plugvalue AS $pluginkey => $pluginkeyvalue)
									{
										if ($location == $plugvalue['api'])
										{
											if (empty($plugvalue['html']) AND !empty($plugvalue['php']))
											{
												$plugincode .= stripslashes($plugvalue['php']);
												$foundinlinecode++;
												break;
											}
											else if  (!empty($plugvalue['html']) AND empty($plugvalue['php']))
											{
												$plugincode .= stripslashes($plugvalue['html']);
												$foundinlinehtml++;
												break;
											}
										}
									}
								}
							}
						}        
					}        
				}
			}
			$ilance->cache->store("api_$location", $plugincode);
		}
		$ilance->timer->stop();
		DEBUG("api(\$location = $location) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
                return $plugincode;
        }
    
        /**
	* Function to fetch all installed plugin_*.xml files and build the plugin array with any installed add-on products
	*
        * @return      none
	*/
	function fetch_installed_plugins()
	{
                global $ilance, $phrase;
                if ((defined('DISABLE_PLUGINAPI') AND DISABLE_PLUGINAPI))
                {
                        return;
                }
                $foundplugins = 0;
		$function = new stdClass();
                $function->timer = new timer;
                $function->timer->start();
                $xml = array();
                $handle = opendir(DIR_XML);
                while (($file = readdir($handle)) !== false)
                {
                        if (!preg_match('#^plugin_(.*).xml$#i', $file, $matches))
                        {
                                continue;
                        }
                        $xml[] = $ilance->xml->construct_xml_array('UTF-8', 1, $file);
                        $foundplugins++;
                }
                ksort($xml);
                $function->timer->stop();
                DEBUG("fetch_installed_plugins(), found $foundplugins plugins in " . $function->timer->get() . " seconds", 'FUNCTION');
                return $xml;
	}
	
        /**
        * Function to fetch the language locale settings to setup our environment
        *
        * @param       integer       language id
        * @param       string        (optional) short form language identifier (if specified, will override language id argument).  i.e: eng
        * 
        * @return      integer       Returns an array like $res['locale] which would equal 'en_US' or 'en_US.utf8', 'pl_PL.utf8', etc.
        */
        function fetch_language_locale($languageid = 1, $slng = '')
        {
                global $ilance;
                $res['locale'] = 'en_US';
		if (!empty($slng))
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "locale
				FROM " . DB_PREFIX . "language
				WHERE CONCAT(SUBSTRING(languagecode, 1, 3)) = '" . $ilance->db->escape_string($slng) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
		}
		else
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "locale
				FROM " . DB_PREFIX . "language
				WHERE languageid = '" . intval($languageid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
		}
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                }
                return $res;
        }
	
	/**
	* Function to fetch the user count
	*
        * @return      integer        Returns number (i.e.: 3201)
	*/
        function usercount()
	{
                global $ilance, $show;
                $count = 0;
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "COUNT(*) AS count
			FROM " . DB_PREFIX . "users
		");
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		if ($res['count'] > 0)
		{
			$count = $res['count'];
		}
                return $count;
        }
        
	/**
	* Function to set $_SERVER globals with new GeoIP details for usage within the application
	*
	* @return     nothing
	*/
	function fetch_geoip_server_vars($ipaddress = '')
	{
		$_SERVER['GEOIP_COUNTRYCODE'] = '';
		$_SERVER['GEOIP_COUNTRY'] = '';
		$_SERVER['GEOIP_STATECODE'] = '';
		$_SERVER['GEOIP_STATE'] = '';
		$_SERVER['GEOIP_CITY'] = '';
		$_SERVER['GEOIP_ZIPCODE'] = '';
		if (file_exists(DIR_CORE . 'functions_geoip_city.dat'))
		{
			if (!function_exists('geoip_open'))
			{
				require_once(DIR_CORE . 'functions_geoip.php');
			}
			$geoip = geoip_open(DIR_CORE . 'functions_geoip_city.dat', GEOIP_STANDARD);
			$geo = geoip_record_by_addr($geoip, IPADDRESS);
			$_SERVER['GEOIP_COUNTRYCODE'] = (!empty($geo->country_code) ? $geo->country_code : '');
			$_SERVER['GEOIP_COUNTRY'] = (!empty($geo->country_name) ? $geo->country_name : '');
			$_SERVER['GEOIP_STATECODE'] = (!empty($geo->region) ? $geo->region : '');
			$_SERVER['GEOIP_STATE'] = (!empty($geo->region) ? (!empty($GEOIP_REGION_NAME[$geo->country_code][$geo->region]) ? $GEOIP_REGION_NAME[$geo->country_code][$geo->region] : '') : '');
			$_SERVER['GEOIP_CITY'] = (!empty($geo->city) ? $geo->city : '');
			$_SERVER['GEOIP_ZIPCODE'] = (!empty($geo->postal_code) ? $geo->postal_code : '');
			unset($geoip, $geo);
		}
	}

	/**
	* Initializes the $ilconfig array as well as the payment modules configuration construction
	*
	* @return      none
	*/
	function init_configuration()
	{
                /**
                * print a stop message on servers running php < 5.2.0
                */
                if (PHP_VERSION < '5.2.0')
                {
                        die('<strong>Fatal error:</strong> installed php version <strong>' . PHP_VERSION . '</strong> is currently not supported. Minimum expected version is <strong>5.2.0</strong> or higher');
                }
                $function = new stdClass();
                $function->timer = new timer;
                $function->timer->start();
                $ilconfig = $ilregions = array();
                global $ilance, $ilconfig, $ilpage, $ilregions, $ilcrumbs, $phrase;
		if (($ilconfig = $ilance->cachecore->fetch("ilconfig")) === false)
		{	
			$config = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "name, value
				FROM " . DB_PREFIX . "configuration
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($config) > 0)
			{
				while ($res = $ilance->db->fetch_array($config, DB_ASSOC))
				{
					$ilconfig[$res['name']] = $res['value'];
				}
				unset($res);
			}
			unset($config);
			$config = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "name, value
				FROM " . DB_PREFIX . "payment_configuration
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($config) > 0)
			{
				while ($res = $ilance->db->fetch_array($config, DB_ASSOC))
				{
					$ilconfig[$res['name']] = $res['value'];
				}
				unset($res);
			}
			unset($config);
			$paygroups = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "groupname
				FROM " . DB_PREFIX . "payment_groups
				WHERE moduletype = 'gateway'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($paygroups) > 0)
			{
				while ($res = $ilance->db->fetch_array($paygroups, DB_ASSOC))
				{
					if ($res['groupname'] == $ilconfig['use_internal_gateway'])
					{
						$v3pay['selectedmodule'] = $res['groupname'];
						break;
					}
					else
					{
						$v3pay['selectedmodule'] = 'none';
					}
				}
				unset($res);
				if ($v3pay['selectedmodule'] != 'none')
				{
					$sql = $ilance->db->query("
						SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "name, value
						FROM " . DB_PREFIX . "payment_configuration
						WHERE configgroup = '" . $ilance->db->escape_string($v3pay['selectedmodule']) . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
						{
							$ilconfig[$res['name']] = $res['value'];
						}
						unset($res);
					}
					unset($sql);
				}
				unset($v3pay);
			}
			unset($paygroups);
			$ilance->cachecore->store("ilconfig", $ilconfig);
		}
		$regions = array('europe', 'africa', 'antarctica', 'asia', 'north_america', 'oceania', 'south_america');
		$sel_regions = is_serialized($ilconfig['shipping_regions']) ? unserialize($ilconfig['shipping_regions']) : array();
		foreach ($regions AS $value)
		{
		       $ilregions[$value] = (in_array($value, $sel_regions)) ? true : false;
		}
		$ilregions['worldwide'] = ($ilconfig['worldwideshipping'] == '1') ? true : false;
		if (function_exists('date_default_timezone_set'))
		{
			$ilconfig['globalserverlocale_sitetimezone'] = (empty($ilconfig['globalserverlocale_sitetimezone']) ? 'America/Los_Angeles' : $ilconfig['globalserverlocale_sitetimezone']);
			date_default_timezone_set($ilconfig['globalserverlocale_sitetimezone']);
		}
		define('SITE_NAME', stripslashes($ilconfig['globalserversettings_sitename']));
		define('SITE_ADDRESS', stripslashes($ilconfig['globalserversettings_siteaddress']));
		define('SITE_EMAIL', stripslashes($ilconfig['globalserversettings_siteemail']));
		define('SITE_PHONE', stripslashes($ilconfig['globalserversettings_sitephone']));
		define('COMPANY_NAME', stripslashes($ilconfig['globalserversettings_companyname']));
		define('COOKIE_PREFIX', (empty($ilconfig['globalsecurity_cookiename']) ? 'ilance_' : $ilconfig['globalsecurity_cookiename']));
		if (preg_match('#ilance.#', $_SERVER['HTTP_HOST']))
		{
			$ipaddress = IPADDRESS;
			if (ip_address_excluded($ipaddress))
			{
				define('ADMINCP_TEST_MODE', false);
			}
			else
			{
				define('ADMINCP_TEST_MODE', true);
			}
		}
		else
		{
			define('ADMINCP_TEST_MODE', false);
		}
		$ilpage = array(
			'invoicepayment' => 'invoicepayment' . $ilconfig['globalsecurity_extensionmime'],
			'login' => 'login' . $ilconfig['globalsecurity_extensionmime'],
			'payment' => 'payment' . $ilconfig['globalsecurity_extensionmime'],
			'attachment' => 'attachment' . $ilconfig['globalsecurity_extensionmime'],
			'buying' => 'buying' . $ilconfig['globalsecurity_extensionmime'],
			'rfp' => 'rfp' . $ilconfig['globalsecurity_extensionmime'],
			'pmb' => 'pmb' . $ilconfig['globalsecurity_extensionmime'],
			'feedback' => 'feedback' . $ilconfig['globalsecurity_extensionmime'],
			'members' => 'members' . $ilconfig['globalsecurity_extensionmime'],
			'portfolio' => 'portfolio' . $ilconfig['globalsecurity_extensionmime'],
			'merch' => 'merch' . $ilconfig['globalsecurity_extensionmime'],
			'main' => 'main' . $ilconfig['globalsecurity_extensionmime'],
			'watchlist' => 'watchlist' . $ilconfig['globalsecurity_extensionmime'],
			'upload' => 'upload' . $ilconfig['globalsecurity_extensionmime'],
			'preferences' => 'preferences' . $ilconfig['globalsecurity_extensionmime'],
			'subscription' => 'subscription' . $ilconfig['globalsecurity_extensionmime'],
			'accounting' => 'accounting' . $ilconfig['globalsecurity_extensionmime'],
			'messages' => 'messages' . $ilconfig['globalsecurity_extensionmime'],
			'notify' => 'notify' . $ilconfig['globalsecurity_extensionmime'],
			'abuse' => 'abuse' . $ilconfig['globalsecurity_extensionmime'],
			'search' => 'search' . $ilconfig['globalsecurity_extensionmime'],
			'upload' => 'upload' . $ilconfig['globalsecurity_extensionmime'],
			'rss' => 'rss' . $ilconfig['globalsecurity_extensionmime'],
			'registration' => 'registration' . $ilconfig['globalsecurity_extensionmime'],
			'selling' => 'selling' . $ilconfig['globalsecurity_extensionmime'],
			'index' => 'index' . $ilconfig['globalsecurity_extensionmime'],
			'workspace' => 'workspace' . $ilconfig['globalsecurity_extensionmime'],
			'mediashare' => 'mediashare' . $ilconfig['globalsecurity_extensionmime'],
			'campaign' => 'campaign' . $ilconfig['globalsecurity_extensionmime'],
			'ajax' => 'ajax' . $ilconfig['globalsecurity_extensionmime'],
			'nonprofits' => 'nonprofits' . $ilconfig['globalsecurity_extensionmime'],
			'escrow' => 'escrow' . $ilconfig['globalsecurity_extensionmime'],
			'bulk' => 'bulk' . $ilconfig['globalsecurity_extensionmime'],
			'components' => 'components' . $ilconfig['globalsecurity_extensionmime'],
			'connections' => 'connections' . $ilconfig['globalsecurity_extensionmime'],
			'distribution' => 'distribution' . $ilconfig['globalsecurity_extensionmime'],
			'language' => 'language' . $ilconfig['globalsecurity_extensionmime'],
			'locations' => 'locations' . $ilconfig['globalsecurity_extensionmime'],
			'settings' => 'settings' . $ilconfig['globalsecurity_extensionmime'],
			'subscribers' => 'subscribers' . $ilconfig['globalsecurity_extensionmime'],
			'dashboard' => 'dashboard' . $ilconfig['globalsecurity_extensionmime'],
			'compare' => 'compare' . $ilconfig['globalsecurity_extensionmime'],
			'styles' => 'styles' . $ilconfig['globalsecurity_extensionmime'],
			'tools' => 'tools' . $ilconfig['globalsecurity_extensionmime'],
			'javascript' => 'javascript' . $ilconfig['globalsecurity_extensionmime']
		);
		
		($apihook = $this->api('init_configuration_start')) ? eval($apihook) : false;
                
		if (defined('SUB_FOLDER_ROOT') AND SUB_FOLDER_ROOT != '')
		{
			$ilconfig['template_relativeimagepath'] = SUB_FOLDER_ROOT;
		}
		else
		{
			$ilconfig['template_relativeimagepath'] = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER);
		}
		// #### CDN ####################################################
		$ilconfig['template_relativeimagepath_cdn'] = $ilconfig['template_relativeimagepath'];
		if ((defined('HTTP_CDN_SERVER') AND defined('HTTPS_CDN_SERVER')))
		{
			if (HTTP_CDN_SERVER != '' AND HTTPS_CDN_SERVER != '')
			{
				$ilconfig['template_relativeimagepath_cdn'] = ((PROTOCOL_REQUEST == 'https') ? HTTPS_CDN_SERVER : HTTP_CDN_SERVER);
			}
		}
		$function->timer->stop();
		DEBUG("init_configuration() in " . $function->timer->get() . " seconds", 'FUNCTION');      
        }
	
	/**
	* Function to fetch the entire list of pre-defined pages to breadcrumb titles.  For example, rfp.php would display RFP, PMB would display Private Message, etc.
	*
	* @return	nothing
	*/
	function fetch_breadcrumb_titles()
	{
		global $ilance, $phrase, $ilconfig;
		$ilcrumbs = array(
			'invoicepayment' . $ilconfig['globalsecurity_extensionmime'] => '{_invoicing}',
			'login' . $ilconfig['globalsecurity_extensionmime'] => '{_login}',
			'payment' . $ilconfig['globalsecurity_extensionmime'] => '{_payments}',
			'attachment' . $ilconfig['globalsecurity_extensionmime'] => '{_attachment}',
			'buying' . $ilconfig['globalsecurity_extensionmime'] => '{_buying}',
			'rfp' . $ilconfig['globalsecurity_extensionmime'] => 'RFP',
			'pmb' . $ilconfig['globalsecurity_extensionmime'] => 'PMB',
			'feedback' . $ilconfig['globalsecurity_extensionmime'] => '{_feedback}',
			'members' . $ilconfig['globalsecurity_extensionmime'] => '{_members}',
			'portfolio' . $ilconfig['globalsecurity_extensionmime'] => '{_portfolios}',
			'merch' . $ilconfig['globalsecurity_extensionmime'] => '{_products}',
			'main' . $ilconfig['globalsecurity_extensionmime'] => '{_main}',
			'watchlist' . $ilconfig['globalsecurity_extensionmime'] => '{_watchlist}',
			'upload' . $ilconfig['globalsecurity_extensionmime'] => '{_upload}',
			'preferences' . $ilconfig['globalsecurity_extensionmime'] => '{_preferences}',
			'subscription' . $ilconfig['globalsecurity_extensionmime'] => '{_subscription}',
			'accounting' . $ilconfig['globalsecurity_extensionmime'] => '{_accounting}',
			'messages' . $ilconfig['globalsecurity_extensionmime'] => '{_messages}',
			'notify' . $ilconfig['globalsecurity_extensionmime'] => '{_notify}',
			'abuse' . $ilconfig['globalsecurity_extensionmime'] => '{_abuse}',
			'search' . $ilconfig['globalsecurity_extensionmime'] => '{_search}',
			'rss' . $ilconfig['globalsecurity_extensionmime'] => '{_rss_feeds}',
			'registration' . $ilconfig['globalsecurity_extensionmime'] => '{_registration}',
			'selling' . $ilconfig['globalsecurity_extensionmime'] => '{_selling}',
			'index' . $ilconfig['globalsecurity_extensionmime'] => '{_main}',
			'workspace' . $ilconfig['globalsecurity_extensionmime'] => '{_workspace}',
			'mediashare' . $ilconfig['globalsecurity_extensionmime'] => '{_workspace}',
			'compare' . $ilconfig['globalsecurity_extensionmime'] => '{_compare}',
			'campaign' . $ilconfig['globalsecurity_extensionmime'] => 'Campaign',
			'ajax' . $ilconfig['globalsecurity_extensionmime'] => 'Ajax',
			'nonprofits' . $ilconfig['globalsecurity_extensionmime'] => '{_nonprofits}',
			'escrow' . $ilconfig['globalsecurity_extensionmime'] => '{_escrow}',
			'bulk' . $ilconfig['globalsecurity_extensionmime'] => '{_bulk}',
			// admin control panel
			'components' . $ilconfig['globalsecurity_extensionmime'] => '{_products}',
			'connections' . $ilconfig['globalsecurity_extensionmime'] => '{_connections}',
			'distribution' . $ilconfig['globalsecurity_extensionmime'] => '{_distribution}',
			'language' . $ilconfig['globalsecurity_extensionmime'] => '{_languages}',
			'locations' . $ilconfig['globalsecurity_extensionmime'] => '{_locations}',
			'settings' . $ilconfig['globalsecurity_extensionmime'] => '{_settings}',
			'subscribers' . $ilconfig['globalsecurity_extensionmime'] => '{_customers}',
			'dashboard' . $ilconfig['globalsecurity_extensionmime'] => '{_dashboard}',
			'styles' . $ilconfig['globalsecurity_extensionmime'] => '{_styles}',
			'tools' . $ilconfig['globalsecurity_extensionmime'] => '{_tools}',
		);
		
		($apihook = $ilance->api('fetch_breadcrumb_titles_end')) ? eval($apihook) : false;
		
		return $ilcrumbs;
	}
	function callhome()
	{
		global $ilconfig;
		$s = 'https://www.ilance.com/?do=callhome&licensekey=' . LICENSEKEY . '&url=' . HTTP_SERVER . '&email=' . $ilconfig['globalserversettings_siteemail'] . '&usercount=' . $this->usercount();
		fetch_curl_string($s);
		unset($s);
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>