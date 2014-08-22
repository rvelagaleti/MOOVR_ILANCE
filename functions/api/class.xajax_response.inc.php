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
* The xajax class generates the xajax javascript for your page including the 
* javascript wrappers for the PHP functions that you want to call from your page.
* It also handles processing and executing the command messages in the xml responses
* sent back to your page from your PHP functions.
*
* @package      iLance\XAJAX\Response
* @version      4.0.0.8059
* @author       XAJAX
*/
class xajaxResponse
{
	var $xml;
	var $sEncoding;

	// Constructor. Its main job is to set the character encoding for the response.
	// $sEncoding is a string containing the character encoding string to use.
	// * Note: to change the character encoding for all of the responses, set the
	// XAJAX_DEFAULT_ENCODING constant near the beginning of the xajax.inc.php file
	function xajaxResponse($sEncoding=XAJAX_DEFAULT_CHAR_ENCODING)
	{
		$this->setCharEncoding($sEncoding);
	}
	
	// setCharEncoding() sets the character encoding for the response based on
	// $sEncoding, which is a string containing the character encoding to use. You
	// don't need to use this method normally, since the character encoding for the
	// response gets set automatically based on the XAJAX_DEFAULT_CHAR_ENCODING
	// constant.
	function setCharEncoding($sEncoding)
	{
		$this->sEncoding = $sEncoding;
	}
	
	// addAssign() adds an assign command message to the XML response
	// $sTarget is a string containing the id of an HTML element
	// $sAttribute is the part of the element you wish to modify ("innerHTML", "value", etc.)
	// $sData is the data you want to set the attribute to
	// usage: $objResponse->addAssign("contentDiv", "innerHTML", "Some Text");
	function addAssign($sTarget,$sAttribute,$sData)
	{
		$this->xml .= $this->_cmdXML(array("n"=>"as","t"=>$sTarget,"p"=>$sAttribute),$sData);
	}
        
	// addAppend() adds an append command message to the XML response
	// $sTarget is a string containing the id of an HTML element
	// $sAttribute is the part of the element you wish to modify ("innerHTML", "value", etc.)
	// $sData is the data you want to append to the end of the attribute
	// usage: $objResponse->addAppend("contentDiv", "innerHTML", "Some New Text");
	function addAppend($sTarget,$sAttribute,$sData)
	{	
		$this->xml .= $this->_cmdXML(array("n"=>"ap","t"=>$sTarget,"p"=>$sAttribute),$sData);
	}
	
	// addPrepend() adds an prepend command message to the XML response
	// $sTarget is a string containing the id of an HTML element
	// $sAttribute is the part of the element you wish to modify ("innerHTML", "value", etc.)
	// $sData is the data you want to prepend to the beginning of the attribute
	// usage: $objResponse->addPrepend("contentDiv", "innerHTML", "Some Starting Text");
	function addPrepend($sTarget,$sAttribute,$sData)
	{
		$this->xml .= $this->_cmdXML(array("n"=>"pp","t"=>$sTarget,"p"=>$sAttribute),$sData);
	}
	
	// addReplace() adds an replace command message to the XML response
	// $sTarget is a string containing the id of an HTML element
	// $sAttribute is the part of the element you wish to modify ("innerHTML", "value", etc.)
	// $sSearch is a string to search for
	// $sData is a string to replace the search string when found in the attribute
	// usage: $objResponse->addReplace("contentDiv", "innerHTML", "text", "<b>text</b>");
	function addReplace($sTarget,$sAttribute,$sSearch,$sData)
	{
		$sDta = "<s><![CDATA[$sSearch]]></s><r><![CDATA[$sData]]></r>";
		$this->xml .= $this->_cmdXML(array("n"=>"rp","t"=>$sTarget,"p"=>$sAttribute),$sDta);
	}
	
	// addClear() adds an clear command message to the XML response
	// $sTarget is a string containing the id of an HTML element
	// $sAttribute is the part of the element you wish to clear ("innerHTML", "value", etc.)
	// usage: $objResponse->addClear("contentDiv", "innerHTML");
	function addClear($sTarget,$sAttribute)
	{
		$this->addAssign($sTarget,$sAttribute,'');
	}
	
	// addAlert() adds an alert command message to the XML response
	// $sMsg is the text to be displayed in the Javascript alert box
	// usage: $objResponse->addAlert("This is important information");
	function addAlert($sMsg)
	{
		$this->xml .= $this->_cmdXML(array("n"=>"al"),$sMsg);
	}
	
	// addRedirect() uses the addScript() method to add a Javascript redirect to
	// another URL
	// $sURL is the URL to redirect the client browser to
	// usage: $objResponse->addRedirect("http://www.xajaxproject.org");
	function addRedirect($sURL)
	{
		//we need to parse the query part so that the values are rawurlencode()'ed
		//can't just use parse_url() cos we could be dealing with a relative URL which
		//  parse_url() can't deal with.
		$queryStart = mb_strpos($sURL, '?', mb_strrpos($sURL, '/'));
		if ($queryStart !== FALSE)
		{
			$queryStart++;
			$queryEnd = mb_strpos($sURL, '#', $queryStart);
			if ($queryEnd === FALSE)
				$queryEnd = mb_strlen($sURL);
			$queryPart = mb_substr($sURL, $queryStart, $queryEnd-$queryStart);
			mb_parse_str($queryPart, $queryParts);
			foreach($queryParts AS $key => $value)
			{
				$newQueryPart .= rawurlencode($key).'='.rawurlencode($value).ini_get('arg_separator.output');
			}
			$sURL = str_replace($queryPart, $newQueryPart, $sURL);
		}
		$this->addScript('window.location = "'.$sURL.'";');
	}

	// addScript() adds a Javascript command message to the XML response
	// $sJS is a string containing Javascript code to be executed
	// usage: $objResponse->addScript("var x = prompt('get some text');");
	function addScript($sJS)
	{
		$this->xml .= $this->_cmdXML(array("n"=>"js"),$sJS);
	}
	
	// addRemove() adds a remove element command message to the XML response
	// $sTarget is a string containing the id of an HTML element to be removed
	// from your page
	// usage: $objResponse->addRemove("Div2");
	function addRemove($sTarget)
	{
		$this->xml .= $this->_cmdXML(array("n"=>"rm","t"=>$sTarget),'');
	}
	
	// addCreate() adds a create element command message to the XML response
	// $sParent is a string containing the id of an HTML element to which the new
	// element will be appended.
	// $sTag is the tag to be added
	// $sId is the id to be assigned to the new element
	// $sType has been deprecated, use the addCreateInput() method instead
	// usage: $objResponse->addCreate("parentDiv", "h3", "myid");
	function addCreate($sParent, $sTag, $sId, $sType="")
	{
		if ($sType)
		{
			trigger_error("The \$sType parameter of addCreate has been deprecated.  Use the addCreateInput() method instead.", E_USER_WARNING);
			return;
		}
		$this->xml .= $this->_cmdXML(array("n"=>"ce","t"=>$sParent,"p"=>$sId),$sTag);
	}
	
	// addInsert() adds an insert element command message to the XML response
	// $sBefore is a string containing the id of the child before which the new element
	// will be inserted
	// $sTag is the tag to be added
	// $sId is the id to be assigned to the new element
	// usage: $objResponse->addInsert("childDiv", "h3", "myid");
	function addInsert($sBefore, $sTag, $sId)
	{
		$this->xml .= $this->_cmdXML(array("n"=>"ie","t"=>$sBefore,"p"=>$sId),$sTag);
	}

	// addInsertAfter() adds an insert element command message to the XML response
	// $sAfter is a string containing the id of the child after which the new element
	// will be inserted
	// $sTag is the tag to be added
	// $sId is the id to be assigned to the new element
	// usage: $objResponse->addInsertAfter("childDiv", "h3", "myid");	
	function addInsertAfter($sAfter, $sTag, $sId)
	{
		$this->xml .= $this->_cmdXML(array("n"=>"ia","t"=>$sAfter,"p"=>$sId),$sTag);
	}
	
	// addCreateInput() adds a create input command message to the XML response
	// $sParent is a string containing the id of an HTML element to which the new
	// input will be appended
	// $sType is the type of input to be created (text, radio, checkbox, etc.)
	// $sName is the name to be assigned to the new input and the variable name when it is submitted
	// $sId is the id to be assigned to the new input
	// usage: $objResponse->addCreateInput("form1", "text", "username", "input1");
	function addCreateInput($sParent, $sType, $sName, $sId)
	{
		$this->xml .= $this->_cmdXML(array("n"=>"ci","t"=>$sParent,"p"=>$sId,"c"=>$sType),$sName);
	}
	
	// addInsertInput() adds an insert input command message to the XML response
	// $sBefore is a string containing the id of the child before which the new element
	// will be inserted
	// $sType is the type of input to be created (text, radio, checkbox, etc.)
	// $sName is the name to be assigned to the new input and the variable name when it is submitted
	// $sId is the id to be assigned to the new input
	// usage: $objResponse->addInsertInput("input5", "text", "username", "input1");
	function addInsertInput($sBefore, $sType, $sName, $sId)
	{
		$this->xml .= $this->_cmdXML(array("n"=>"ii","t"=>$sBefore,"p"=>$sId,"c"=>$sType),$sName);
	}
	
	// addEvent() adds an event command message to the XML response
	// $sTarget is a string containing the id of an HTML element
	// $sEvent is the event you wish to set ("click", "mouseover", etc.)
	// $sScript is the Javascript string you want to the event to invoke
	// usage: $objResponse->addEvent("contentDiv", "click", "alert(\'Hello World\');");
	function addEvent($sTarget,$sEvent,$sScript)
	{
		$this->xml .= $this->_cmdXML(array("n"=>"ev","t"=>$sTarget,"p"=>$sEvent),$sScript);
	}
	
	// addHandler() adds a handler command message to the XML response
	// $sTarget is a string containing the id of an HTML element
	// $sEvent is the event you wish to set ("click", "mouseover", etc.)
	// $sHandler is a string containing the name of a Javascript function
	// that will handle the event. Multiple handlers can be added for the same event
	// usage: $objResponse->addHandler("contentDiv", "click", "content_click");
	function addHandler($sTarget,$sEvent,$sHandler)
	{	
		$this->xml .= $this->_cmdXML(array("n"=>"ah","t"=>$sTarget,"p"=>$sEvent),$sHandler);
	}
	
	// addRemoveHandler() adds a remove handler command message to the XML response
	// $sTarget is a string containing the id of an HTML element
	// $sEvent is the event you wish to remove ("click", "mouseover", etc.)
	// $sHandler is a string containing the name of a Javascript handler function
	// that you want to remove
	// usage: $objResponse->addRemoveHandler("contentDiv", "click", "content_click");
	function addRemoveHandler($sTarget,$sEvent,$sHandler)
	{	
		$this->xml .= $this->_cmdXML(array("n"=>"rh","t"=>$sTarget,"p"=>$sEvent),$sHandler);
	}
	
	// addIncludeScript() adds an include script command message to the XML response
	// $sFileName is a URL of the Javascript file to include
	// usage: $objResponse->addIncludeScript("functions.js");
	function addIncludeScript($sFileName)
	{
		$this->xml .= $this->_cmdXML(array("n"=>"in"),$sFileName);
	}
	
	// getXML() returns the XML to be returned from your function to the xajax
	// processor on your page. Since xajax 0.2, you can also return an xajaxResponse
	// object from your function directly, and xajax will automatically request the
	// XML using this method call.
	// usage: return $objResponse->getXML();
	function getXML()
	{
		$sXML = "<?xml version=\"1.0\"";
		if ($this->sEncoding && mb_strlen(trim($this->sEncoding)) > 0)
			$sXML .= " encoding=\"".$this->sEncoding."\"";
		$sXML .= " ?"."><xjx>" . $this->xml . "</xjx>";
		
		return $sXML;
	}
	
	// loadXML() adds the commands of the provided response XML output to this
	// response object
	// $sXML is the response XML (returned from a getXML() method) to add to the
	// end of this response object
	// usage: $r1 = $objResponse1->getXML();
	//        $objResponse2->loadXML($r1);
	//        return $objResponse2->getXML();
	function loadXML($mXML)
	{
		if (is_a($mXML, "xajaxResponse")) {
			$mXML = $mXML->getXML();
		}
		$sNewXML = "";
		$iStartPos = mb_strpos($mXML, "<xjx>") + 5;
		$sNewXML = mb_substr($mXML, $iStartPos);
		$iEndPos = mb_strpos($sNewXML, "</xjx>");
		$sNewXML = mb_substr($sNewXML, 0, $iEndPos);
		$this->xml .= $sNewXML;
	}

	// private method, used internally
	function _cmdXML($aAttributes, $sData)
	{
		if (function_exists('mb_convert_encoding'))
			$sData = call_user_func_array('mb_convert_encoding', array(&$sData, 'HTML-ENTITIES', $this->sEncoding));
		$xml = "<cmd";
		foreach($aAttributes as $sAttribute => $sValue)
			$xml .= " $sAttribute=\"$sValue\"";
		if ($sData !== null && !mb_stristr($sData,'<![CDATA['))
			$xml .= "><![CDATA[$sData]]></cmd>";
		else if ($sData !== null)
			$xml .= ">$sData</cmd>";
		else
			$xml .= "></cmd>";
		
		return $xml;
	}	
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>