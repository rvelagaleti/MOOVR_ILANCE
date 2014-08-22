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

if (!defined('XAJAX_DEFAULT_CHAR_ENCODING'))
{
	define('XAJAX_DEFAULT_CHAR_ENCODING', 'utf-8');
}
require_once(DIR_API . 'class.xajax_response.inc.php');
if (!defined('XAJAX_GET'))
{
	define ('XAJAX_GET', 0);
}
if (!defined('XAJAX_POST'))
{
	define ('XAJAX_POST', 1);
}

/**
* The xajax class generates the xajax javascript for your page including the 
* javascript wrappers for the PHP functions that you want to call from your page.
* It also handles processing and executing the command messages in the xml responses
* sent back to your page from your PHP functions.
*
* @package      iLance\XAJAX
* @version      4.0.0.8059
* @author       XAJAX
*/
class xajax
{
	var $aFunctions;		// Array of PHP functions that will be callable through javascript wrappers
	var $aObjects;			// Array of object callbacks that will allow Javascript to call PHP methods (key=function name)
	var $aFunctionRequestTypes;	// Array of RequestTypes to be used with each function (key=function name)
	var $aFunctionIncludeFiles;	// Array of Include Files for any external functions (key=function name)
	var $sCatchAllFunction;		// Name of the PHP function to call if no callable function was found
	var $sPreFunction;		// Name of the PHP function to call before any other function
	var $sRequestURI;		// The URI for making requests to the xajax object
	var $bDebug;			// Show debug messages (true/false)
	var $bExitAllowed;		// Allow xajax to exit after processing a request (true/false)
	var $bErrorHandler;		// Use an special xajax error handler so the errors are sent to the browser properly
	var $sLogFile;			// Specify if xajax should log errors (and more information in a future release)
	var $sWrapperPrefix;		// The prefix to prepend to the javascript wraper function name
	var $bStatusMessages;		// Show debug messages (true/false)
	var $bWaitCursor;		// Use wait cursor in browser (true/false)
	var $bCleanBuffer;		// Clean all output buffers before outputting response (true/false)
	var $bDecodeUTF8Input;		// Decode input request args from UTF-8 (true/false)
	var $aObjArray;			// Array for parsing complex objects
	var $iPos;			// Position in $aObjArray
	var $sEncoding;			// The Character Encoding to use
	
	// Contructor
	// $sRequestURI - defaults to the current page
	// $sWrapperPrefix - defaults to "xajax_";
	// $sEncoding - defaults to XAJAX_DEFAULT_CHAR_ENCODING defined above
	// $bDebug Mode - defaults to false
	// usage: $xajax = new xajax();
	function xajax($sRequestURI="",$sWrapperPrefix="xajax_",$sEncoding=XAJAX_DEFAULT_CHAR_ENCODING,$bDebug=false)
	{
		$this->aFunctions = array();
		$this->aObjects = array();
		$this->aFunctionIncludeFiles = array();
		$this->sRequestURI = $sRequestURI;
		if ($this->sRequestURI == "")
			$this->sRequestURI = $this->_detectURI();
		$this->sWrapperPrefix = $sWrapperPrefix;
		$this->setCharEncoding($sEncoding);
		$this->bDebug = $bDebug;
		$this->bWaitCursor = true;
		$this->bExitAllowed = true;
		$this->bErrorHandler = false;
		$this->sLogFile = "";
		$this->bCleanBuffer = true;
		$this->bDecodeUTF8Input;
	}
		
	// setRequestURI() sets the URI to which requests will be made
	// usage: $xajax->setRequestURI("http://xajax.sourceforge.net");
	function setRequestURI($sRequestURI)
	{
		$this->sRequestURI = $sRequestURI;
	}
	
	// debugOn() enables debug messages for xajax
	function debugOn()
	{
		$this->bDebug = true;
	}
	
	// debugOff() disables debug messages for xajax (default behavior)
	function debugOff()
	{
		$this->bDebug = false;
	}
	
	// statusMessagesOn() enables messages in the statusbar for xajax
	function statusMessagesOn()
	{
		$this->bStatusMessages = true;
	}
	
	// statusMessagesOff() disables messages in the statusbar for xajax (default behavior)
	function statusMessagesOff()
	{
		$this->bStatusMessages = false;
	}
	
	// waitCursor() enables the wait cursor to be displayed in the browser (default behavior)
	function waitCursorOn()
	{
		$this->bWaitCursor = true;
	}
	
	// waitCursorOff() disables the wait cursor to be displayed in the browser
	function waitCursorOff()
	{
		$this->bWaitCursor = false;
	}	
	
	// exitAllowedOn() enables xajax to exit immediately after processing a request
	// and sending the response back to the browser (default behavior)
	function exitAllowedOn()
	{
		$this->bExitAllowed = true;
	}
	
	// exitAllowedOff() disables xajax's default behavior of exiting immediately
	// after processing a request and sending the response back to the browser
	function exitAllowedOff()
	{
		$this->bExitAllowed = false;
	}
	
	// errorHandlerOn() turns on xajax's error handling system so that PHP errors
	// that occur during a request are trapped and pushed to the browser in the
	// form of a Javascript alert
	function errorHandlerOn()
	{
		$this->bErrorHandler = true;
	}
	// errorHandlerOff() turns off xajax's error handling system (default behavior)
	function errorHandlerOff()
	{
		$this->bErrorHandler = false;
	}
	
	// setLogFile() specifies a log file that will be written to by xajax during
	// a request (used only by the error handling system at present). If you don't
	// invoke this method, or you pass in "", then no log file will be written to.
	// usage: $xajax->setLogFile("/xajax_logs/errors.log");
	function setLogFile($sFilename)
	{
		$this->sLogFile = $sFilename;
	}

	// cleanBufferOn() causes xajax to clean out all output buffers before outputting
	// a response (default behavior)
	function cleanBufferOn()
	{
		$this->bCleanBuffer = true;
	}
	// cleanBufferOff() turns off xajax's output buffer cleaning
	function cleanBufferOff()
	{
		$this->bCleanBuffer = false;
	}

	// decodeUTF8InputOn() causes xajax to decode the input request args from UTF-8
	function decodeUTF8InputOn()
	{
		$this->bDecodeUTF8Input = true;
	}
	// decodeUTF8InputOff() turns off decoding the input request args from UTF-8
	// (default behavior)
	function decodeUTF8InputOff()
	{
		$this->bDecodeUTF8Input = false;
	}
		
	// setWrapperPrefix() sets the prefix that will be appended to the Javascript
	// wrapper functions (default is "xajax_").
	function setWrapperPrefix($sPrefix)
	{
		$this->sWrapperPrefix = $sPrefix;
	}
	
	// setCharEncoding() sets the character encoding to be used by xajax
	// usage: $xajax->setCharEncoding("utf-8");
	// *Note: to change the default character encoding for all xajax responses, set 
	// the XAJAX_DEFAULT_CHAR_ENCODING constant near the beginning of the xajax.inc.php file
	function setCharEncoding($sEncoding)
	{
		$this->sEncoding = $sEncoding;
	}
	
	// registerFunction() registers a PHP function or method to be callable through
	// xajax in your Javascript. If you want to register a function, pass in the name
	// of that function. 
	function registerFunction($mFunction,$sRequestType=XAJAX_POST)
	{
		if (is_array($mFunction)) {
			$this->aFunctions[$mFunction[0]] = 1;
			$this->aFunctionRequestTypes[$mFunction[0]] = $sRequestType;
			$this->aObjects[$mFunction[0]] = array_slice($mFunction, 1);
		}	
		else {
			$this->aFunctions[$mFunction] = 1;
			$this->aFunctionRequestTypes[$mFunction] = $sRequestType;
		}
	}
	
	// registerExternalFunction() registers a PHP function to be callable through xajax
	// which is located in some other file.
	function registerExternalFunction($mFunction,$sIncludeFile,$sRequestType=XAJAX_POST)
	{
		$this->registerFunction($mFunction, $sRequestType);
		
		if (is_array($mFunction)) {
			$this->aFunctionIncludeFiles[$mFunction[0]] = $sIncludeFile;
		}
		else {
			$this->aFunctionIncludeFiles[$mFunction] = $sIncludeFile;
		}
	}
	
	// registerCatchAllFunction() registers a PHP function to be called when xajax cannot
	// find the function being called via Javascript.
	function registerCatchAllFunction($mFunction)
	{
		if (is_array($mFunction)) {
			$this->sCatchAllFunction = $mFunction[0];
			$this->aObjects[$mFunction[0]] = array_slice($mFunction, 1);
		}
		else {
			$this->sCatchAllFunction = $mFunction;
		}
	}
	
	// registerPreFunction() registers a PHP function to be called before xajax calls
	// the requested function. 
	function registerPreFunction($mFunction)
	{
		if (is_array($mFunction)) {
			$this->sPreFunction = $mFunction[0];
			$this->aObjects[$mFunction[0]] = array_slice($mFunction, 1);
		}
		else {
			$this->sPreFunction = $mFunction;
		}
	}
	
	// returns true if xajax can process the request, false if otherwise
	// you can use this to determine if xajax needs to process the request or not
	function canProcessRequests()
	{
		if ($this->getRequestMode() != -1) return true;
		return false;
	}
	
	// returns the current request mode, or -1 if there is none
	function getRequestMode()
	{
		if (!empty($_GET["xajax"]))
			return XAJAX_GET;
		
		if (!empty($_POST["xajax"]))
			return XAJAX_POST;
			
		return -1;
	}
	
	// processRequests() is the main communications engine of xajax
	// The engine handles all incoming xajax requests, calls the apporiate PHP functions
	// and passes the xml responses back to the javascript response handler
	// if your RequestURI is the same as your web page then this function should
	// be called before any headers or html has been sent.
	// usage: $xajax->processRequests()
	function processRequests()
	{			
		$requestMode = -1;
		$sFunctionName = "";
		$bFoundFunction = true;
		$bFunctionIsCatchAll = false;
		$sFunctionNameForSpecial = "";
		$aArgs = array();
		$sPreResponse = "";
		$bEndRequest = false;
		$sResponse = "";
		
		$requestMode = $this->getRequestMode();
		if ($requestMode == -1) return;
	
		if ($requestMode == XAJAX_POST)
		{
			$sFunctionName = $_POST["xajax"];
			
			if (!empty($_POST["xajaxargs"])) 
				$aArgs = $_POST["xajaxargs"];
		}
		else
		{	
			header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header ("Last-Modified: " . date("D, d M Y H:i:s") . " GMT");
			header ("Cache-Control: no-cache, must-revalidate");
			header ("Pragma: no-cache");
			
			$sFunctionName = $_GET["xajax"];
			
			if (!empty($_GET["xajaxargs"])) 
				$aArgs = $_GET["xajaxargs"];
		}
		
		// Use xajax error handler if necessary
		if ($this->bErrorHandler) {
			$GLOBALS['xajaxErrorHandlerText'] = "";
			set_error_handler("xajaxErrorHandler");
		}
		
		if ($this->sPreFunction) {
			if (!$this->_isFunctionCallable($this->sPreFunction)) {
				$bFoundFunction = false;
				$objResponse = new xajaxResponse();
				$objResponse->addAlert("Unknown Pre-Function ". $this->sPreFunction);
				$sResponse = $objResponse->getXML();
			}
		}
		//include any external dependencies associated with this function name
		if (array_key_exists($sFunctionName,$this->aFunctionIncludeFiles))
		{
			ob_start();
			include_once($this->aFunctionIncludeFiles[$sFunctionName]);
			ob_end_clean();
		}
		
		if ($bFoundFunction) {
			$sFunctionNameForSpecial = $sFunctionName;
			if (!array_key_exists($sFunctionName, $this->aFunctions))
			{
				if ($this->sCatchAllFunction) {
					$sFunctionName = $this->sCatchAllFunction;
					$bFunctionIsCatchAll = true;
				}
				else {
					$bFoundFunction = false;
					$objResponse = new xajaxResponse();
					$objResponse->addAlert("Unknown Function $sFunctionName.");
					$sResponse = $objResponse->getXML();
				}
			}
			else if ($this->aFunctionRequestTypes[$sFunctionName] != $requestMode)
			{
				$bFoundFunction = false;
				$objResponse = new xajaxResponse();
				$objResponse->addAlert("Incorrect Request Type.");
				$sResponse = $objResponse->getXML();
			}
		}
		
		if ($bFoundFunction)
		{
			for ($i = 0; $i < sizeof($aArgs); $i++)
			{
				// If magic quotes is on, then we need to strip the slashes from the args
				if (get_magic_quotes_gpc() == 1 && is_string($aArgs[$i])) {
				
					$aArgs[$i] = stripslashes($aArgs[$i]);
				}
				if (mb_stristr($aArgs[$i],"<xjxobj>") != false)
				{
					$aArgs[$i] = $this->_xmlToArray("xjxobj",$aArgs[$i]);	
				}
				else if (mb_stristr($aArgs[$i],"<xjxquery>") != false)
				{
					$aArgs[$i] = $this->_xmlToArray("xjxquery",$aArgs[$i]);	
				}
			}

			if ($this->sPreFunction) {
				$mPreResponse = $this->_callFunction($this->sPreFunction, array($sFunctionNameForSpecial, $aArgs));
				if (is_array($mPreResponse) && $mPreResponse[0] === false) {
					$bEndRequest = true;
					$sPreResponse = $mPreResponse[1];
				}
				else {
					$sPreResponse = $mPreResponse;
				}
				if (($sPreResponse instanceof xajaxResponse)) 
				{
					$sPreResponse = $sPreResponse->getXML();
				}
				if ($bEndRequest) $sResponse = $sPreResponse;
			}
			
			if (!$bEndRequest) {
				if (!$this->_isFunctionCallable($sFunctionName)) {
					$objResponse = new xajaxResponse();
					$objResponse->addAlert("The Registered Function $sFunctionName Could Not Be Found.");
					$sResponse = $objResponse->getXML();
				}
				else {
					if ($bFunctionIsCatchAll) {
						$aArgs = array($sFunctionNameForSpecial, $aArgs);
					}
					$sResponse = $this->_callFunction($sFunctionName, $aArgs);
				}
				if (($sResponse instanceof xajaxResponse)) 
				{
					$sResponse = $sResponse->getXML();
				}
				if (!is_string($sResponse) || mb_strpos($sResponse, "<xjx>") === FALSE) {
					$objResponse = new xajaxResponse();
					$objResponse->addAlert("No XML Response Was Returned By Function $sFunctionName.");
					$sResponse = $objResponse->getXML();
				}
				else if ($sPreResponse != "") {
					$sNewResponse = new xajaxResponse();
					$sNewResponse->loadXML($sPreResponse);
					$sNewResponse->loadXML($sResponse);
					$sResponse = $sNewResponse->getXML();
				}
			}
		}
		
		$sContentHeader = "Content-type: text/xml;";
		if ($this->sEncoding && mb_strlen(trim($this->sEncoding)) > 0)
			$sContentHeader .= " charset=".$this->sEncoding;
		header($sContentHeader);
		if ($this->bErrorHandler && !empty( $GLOBALS['xajaxErrorHandlerText'] )) {
			$sErrorResponse = new xajaxResponse();
			$sErrorResponse->addAlert("** PHP Error Messages: **" . $GLOBALS['xajaxErrorHandlerText']);
			if ($this->sLogFile) {
				$fH = @fopen($this->sLogFile, "a");
				if (!$fH) {
					$sErrorResponse->addAlert("** Logging Error **\n\nxajax was unable to write to the error log file:\n" . $this->sLogFile);
				}
				else {
					fwrite($fH, "** xajax Error Log - " . strftime("%b %e %Y %I:%M:%S %p") . " **" . $GLOBALS['xajaxErrorHandlerText'] . "\n\n\n");
					fclose($fH);
				}
			}

			$sErrorResponse->loadXML($sResponse);
			$sResponse = $sErrorResponse->getXML();
			
		}
		if ($this->bCleanBuffer) while (@ob_end_clean());
		print $sResponse;
		if ($this->bErrorHandler) restore_error_handler();
		
		if ($this->bExitAllowed)
		{
			exit();
		}
	}
			
	// printJavascript() prints the xajax javascript code into your page by printing
	// the output of the getJavascript() method. It should only be called between the
	// <head> </head> tags in your HTML page. 
	function printJavascript($sJsURI="", $sJsFile=NULL)
	{
		print $this->getJavascript($sJsURI, $sJsFile);
	}
	
	// getJavascript() returns the xajax javascript code that should be added to
	// your HTML page between the <head> </head> tags. 
	function getJavascript($sJsURI="", $sJsFile=NULL)
	{	
		$html = $this->getJavascriptConfig();
		$html .= $this->getJavascriptInclude($sJsURI, $sJsFile);
		
		return $html;
	}
	
	// getJavascriptConfig() returns a string containing inline Javascript that sets
	// up the xajax runtime
	function getJavascriptConfig()
	{
		$html  = "\t<script type=\"text/javascript\">\n";
		$html .= "var xajaxRequestUri=\"".$this->sRequestURI."\";\n";
		$html .= "var xajaxDebug=".($this->bDebug?"true":"false").";\n";
		$html .= "var xajaxStatusMessages=".($this->bStatusMessages?"true":"false").";\n";
		$html .= "var xajaxWaitCursor=".($this->bWaitCursor?"true":"false").";\n";
		$html .= "var xajaxDefinedGet=".XAJAX_GET.";\n";
		$html .= "var xajaxDefinedPost=".XAJAX_POST.";\n";
		$html .= "var xajaxLoaded=false;\n";
		foreach($this->aFunctions as $sFunction => $bExists)
		{
			$html .= $this->_wrap($sFunction,$this->aFunctionRequestTypes[$sFunction]);
		}
		$html .= "\t</script>\n";
		return $html;		
	}
	
	// getJavascriptInclude() returns a string containing a Javascript include of the
	// xajax.js file along with a check to see if the file loaded after six seconds
	function getJavascriptInclude($sJsURI="", $sJsFile=NULL)
	{
		global $ilconfig;
		if ($sJsFile == NULL)
		{
			$sJsFile = $ilconfig['template_relativeimagepath'] . DIR_FUNCT_NAME . '/javascript/functions_xajax' . (($ilconfig['globalfilters_jsminify']) ? '.min' : '') . '.js';
		}
		if ($sJsURI != "" && mb_substr($sJsURI, -1) != "/")
		{
			$sJsURI .= "/";
		}
		$html = "\t<script type=\"text/javascript\" src=\"" . $sJsURI . $sJsFile . "\"></script>\n";	
		$html .= "\t<script type=\"text/javascript\">\n";
		$html .= "window.setTimeout(function () { if (!xajaxLoaded) { alert('Error: the xajax Javascript file could not be included. Perhaps the URL is incorrect?\\nURL: {$sJsURI}{$sJsFile}'); } }, 6000);\n";
		$html .= "\t</script>\n";
		return $html;
	}

	// autoCompressJavascript() can be used to create a new xajax.js file out of the
	// xajax_uncompressed.js file (which will only happen if xajax.js doesn't already
	// exist on the filesystem).
	function autoCompressJavascript($sJsFullFilename=NULL)
	{	
		$sJsFile = "xajax_js/xajax.js";
		
		if ($sJsFullFilename) {
			$realJsFile = $sJsFullFilename;
		}
		else {
			$realPath = realpath(dirname(__FILE__));
			$realJsFile = $realPath . "/". $sJsFile;
		}

		// Create a compressed file if necessary
		if (!file_exists($realJsFile)) {
			$srcFile = str_replace(".js", "_uncompressed.js", $realJsFile);
			if (!file_exists($srcFile)) {
				trigger_error("The xajax uncompressed Javascript file could not be found in the <b>" . dirname($realJsFile) . "</b> folder. Error ", E_USER_ERROR);	
			}
			require("xajaxCompress.php");
			$javaScript = implode('', file($srcFile));
			$compressedScript = xajaxCompressJavascript($javaScript);
			$fH = @fopen($realJsFile, "w");
			if (!$fH) {
				trigger_error("The xajax compressed javascript file could not be written in the <b>" . dirname($realJsFile) . "</b> folder. Error ", E_USER_ERROR);
			}
			else {
				fwrite($fH, $compressedScript);
				fclose($fH);
			}
		}
	}
	// _detectURL() returns the current URL based upon the SERVER vars
	// used internally
	function _detectURI() {
		$aURL = array();

		// Try to get the request URL
		$uri = SCRIPT_URI;
		if (!empty($uri))
		{
			$aURL = parse_url($uri);
		}

		// Fill in the empty values
		if (empty($aURL['scheme'])) {
			if (!empty($_SERVER['HTTP_SCHEME'])) {
				$aURL['scheme'] = $_SERVER['HTTP_SCHEME'];
			} else {
				$aURL['scheme'] = (!empty($_SERVER['HTTPS']) && mb_strtolower($_SERVER['HTTPS']) != 'off') ? 'https' : 'http';
			}
		}

		if (empty($aURL['host'])) {
			if (!empty($_SERVER['HTTP_HOST'])) {
				if (mb_strpos($_SERVER['HTTP_HOST'], ':') > 0) {
					list($aURL['host'], $aURL['port']) = explode(':', $_SERVER['HTTP_HOST']);
				} else {
					$aURL['host'] = $_SERVER['HTTP_HOST'];
				}
			} else if (!empty($_SERVER['SERVER_NAME'])) {
				$aURL['host'] = $_SERVER['SERVER_NAME'];
			} else {
				print "xajax Error: xajax failed to automatically identify your Request URI.";
				print "Please set the Request URI explicitly when you instantiate the xajax object.";
				exit();
			}
		}

		if (empty($aURL['port']) && !empty($_SERVER['SERVER_PORT'])) {
			$aURL['port'] = $_SERVER['SERVER_PORT'];
		}

		if (empty($aURL['path'])) {
			if (!empty($_SERVER['PATH_INFO'])) {
				$sPath = parse_url($_SERVER['PATH_INFO']);
			} else {
				$sPath = parse_url($_SERVER['PHP_SELF']);
			}
			$aURL['path'] = $sPath['path'];
			unset($sPath);
		}

		if (!empty($aURL['query'])) {
			$aURL['query'] = '?'.$aURL['query'];
		}

		// Build the URL: Start with scheme, user and pass
		$sURL = $aURL['scheme'].'://';
		if (!empty($aURL['user'])) {
			$sURL.= $aURL['user'];
			if (!empty($aURL['pass'])) {
				$sURL.= ':'.$aURL['pass'];
			}
			$sURL.= '@';
		}

		// Add the host
		$sURL.= $aURL['host'];

		// Add the port if needed
		if (!empty($aURL['port']) && (($aURL['scheme'] == 'http' && $aURL['port'] != 80) || ($aURL['scheme'] == 'https' && $aURL['port'] != 443))) {
			$sURL.= ':'.$aURL['port'];
		}

		// Add the path and the query string
		$sURL.= $aURL['path'].@$aURL['query'];

		// Clean up
		unset($aURL);
		return $sURL;
	}
	
	// returns true if the function name is associated with an object callback,
	// false if not.
	// user internally
	function _isObjectCallback($sFunction)
	{
		if (array_key_exists($sFunction, $this->aObjects)) return true;
		return false;
	}
	
	// return true if the function or object callback can be called, false if not
	// user internally
	function _isFunctionCallable($sFunction)
	{
		if ($this->_isObjectCallback($sFunction)) {
			if (is_object($this->aObjects[$sFunction][0])) {
				return method_exists($this->aObjects[$sFunction][0], $this->aObjects[$sFunction][1]);
			}
			else {
				return is_callable($this->aObjects[$sFunction]);
			}
		}
		else {
			return function_exists($sFunction);
		}	
	}
	
	// calls the function, class method, or object method with the supplied arguments
	// user internally
	function _callFunction($sFunction, $aArgs)
	{
		if ($this->_isObjectCallback($sFunction)) {
			$mReturn = call_user_func_array($this->aObjects[$sFunction], $aArgs);
		}
		else {
			$mReturn = call_user_func_array($sFunction, $aArgs);
		}
		return $mReturn;
	}
	
	// generates the javascript wrapper for the specified PHP function
	// used internally
	function _wrap($sFunction,$sRequestType=XAJAX_POST)
	{
		$js = "function ".$this->sWrapperPrefix."$sFunction(){return xajax.call(\"$sFunction\", arguments, ".$sRequestType.");}\n";		
		return $js;
	}

	// _xmlToArray() takes a string containing xajax xjxobj xml or xjxquery xml
	// and builds an array representation of it to pass as an argument to
	// the php function being called. Returns an array.
	// used internally
	function _xmlToArray($rootTag, $sXml)
	{
		$aArray = array();
		$sXml = str_replace("<$rootTag>","<$rootTag>|~|",$sXml);
		$sXml = str_replace("</$rootTag>","</$rootTag>|~|",$sXml);
		$sXml = str_replace("<e>","<e>|~|",$sXml);
		$sXml = str_replace("</e>","</e>|~|",$sXml);
		$sXml = str_replace("<k>","<k>|~|",$sXml);
		$sXml = str_replace("</k>","|~|</k>|~|",$sXml);
		$sXml = str_replace("<v>","<v>|~|",$sXml);
		$sXml = str_replace("</v>","|~|</v>|~|",$sXml);
		$sXml = str_replace("<q>","<q>|~|",$sXml);
		$sXml = str_replace("</q>","|~|</q>|~|",$sXml);
		
		$this->aObjArray = explode("|~|",$sXml);
		
		$this->iPos = 0;
		$aArray = $this->_parseObjXml($rootTag);
		
		if (function_exists('iconv')) {
            foreach ($aArray as $sKey => $sValue) {
                if (is_string($sValue))
                    $aArray[$sKey] = iconv("UTF-8", $this->sEncoding, $sValue);
            }
        }
        
		return $aArray;
	}
	
	// _parseObjXml() is a recursive function that generates an array from the
	// contents of $this->aObjArray. Returns an array.
	// used internally
	function _parseObjXml($rootTag)
	{
		$aArray = array();
		
		if ($rootTag == "xjxobj")
		{
			while(!mb_stristr($this->aObjArray[$this->iPos],"</xjxobj>"))
			{
				$this->iPos++;
				if(mb_stristr($this->aObjArray[$this->iPos],"<e>"))
				{
					$key = "";
					$value = null;
						
					$this->iPos++;
					while(!mb_stristr($this->aObjArray[$this->iPos],"</e>"))
					{
						if(mb_stristr($this->aObjArray[$this->iPos],"<k>"))
						{
							$this->iPos++;
							while(!mb_stristr($this->aObjArray[$this->iPos],"</k>"))
							{
								$key .= $this->aObjArray[$this->iPos];
								$this->iPos++;
							}
						}
						if(mb_stristr($this->aObjArray[$this->iPos],"<v>"))
						{
							$this->iPos++;
							while(!mb_stristr($this->aObjArray[$this->iPos],"</v>"))
							{
								if(mb_stristr($this->aObjArray[$this->iPos],"<xjxobj>"))
								{
									$value = $this->_parseObjXml("xjxobj");
									$this->iPos++;
								}
								else
								{
									$value .= $this->aObjArray[$this->iPos];
								}
								$this->iPos++;
							}
						}
						$this->iPos++;
					}
					
					$aArray[$key]=$value;
				}
			}
		}
		
		if ($rootTag == "xjxquery")
		{
			$sQuery = "";
			$this->iPos++;
			while(!mb_stristr($this->aObjArray[$this->iPos],"</xjxquery>"))
			{
				if (mb_stristr($this->aObjArray[$this->iPos],"<q>") || mb_stristr($this->aObjArray[$this->iPos],"</q>"))
				{
					$this->iPos++;
					continue;
				}
				$sQuery	.= $this->aObjArray[$this->iPos];
				$this->iPos++;
			}
			
			mb_parse_str($sQuery, $aArray);
			
			// If magic quotes is on, then we need to strip the slashes from the
			// array values because of the parse_str pass which adds slashes
			if (get_magic_quotes_gpc() == 1) {
				$newArray = array();
				foreach ($aArray as $sKey => $sValue) {
					if (is_string($sValue))
						$newArray[$sKey] = stripslashes($sValue);
					else
						$newArray[$sKey] = $sValue;
				}
				$aArray = $newArray;
			}
			if ($this->bDecodeUTF8Input) {
				$aArray = array_map("utf8_decode", $aArray);
			}
		}
		
		return $aArray;
	}
		
}// end class xajax 

// xajaxErrorHandler() is registered with PHP's set_error_handler() function if
// the xajax error handling system is turned on
// used by the xajax class
function xajaxErrorHandler($errno, $errstr, $errfile, $errline)
{
	$errorReporting = error_reporting();
	if (($errno & $errorReporting) == 0) return;
	
	if ($errno == E_NOTICE) {
		$errTypeStr = "NOTICE";
	}
	else if ($errno == E_WARNING) {
		$errTypeStr = "WARNING";
	}
	else if ($errno == E_USER_NOTICE) {
		$errTypeStr = "USER NOTICE";
	}
	else if ($errno == E_USER_WARNING) {
		$errTypeStr = "USER WARNING";
	}
	else if ($errno == E_USER_ERROR) {
		$errTypeStr = "USER FATAL ERROR";
	}
	else if ($errno == E_STRICT) {
		return;
	}
	else {
		$errTypeStr = "UNKNOWN: $errno";
	}
	$GLOBALS['xajaxErrorHandlerText'] .= "\n----\n[$errTypeStr] $errstr\nerror in line $errline of file $errfile";
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>