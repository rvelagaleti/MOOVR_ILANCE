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
* XML class to perform the majority of xml tasks within ILance
*
* @package      iLance\XML
* @version      4.0.0.8059
* @author       ILance
*/
class xml
{
	var $charset = 'UTF-8';
	var $content_type = 'text/xml';
	var $xmldata = '';
	var $tag_count = '';
	var $tabs = '';
	var $doc = '';
	var $data = '';
	var $xml_parser = '';
	var $cdata = '';
	var $stack = array();
	var $open_tags = array();
	var $parsedxmldata = array();
	
        /**
        * Constructor
        */
	function xml($content_type = null, $charset = null)
	{
                global $ilconfig;
		if ($content_type)
		{
			$this->content_type = $content_type;
		}
		if ($charset == null)
		{
			$charset = (!empty($ilconfig['template_charset']) ? $ilconfig['template_charset'] : 'ISO-8859-1');
		}
		$this->charset = (mb_strtolower($charset) == 'iso-8859-1') ? 'windows-1252' : $charset;
	}

	/*
        * Function to send the content-type header
        *
        * @return      
        */
        function send_content_type_header()
	{
		@header('Content-Type: ' . $this->content_type . ($this->charset == '' ? '' : '; charset=' . $this->charset));
	}

	/*
        * Fetch to fetch the XML tag
        *
        * @return      
        */
        function fetch_xml_tag()
	{
		return '<?xml version="1.0" encoding="' . $this->charset . '"?>' . "\n";
	}

	/*
        * Function to add an XML tag to a group
        *
        * @return      
        */
        function add_group($tag, $attr = array())
	{
		$this->open_tags[] = $tag;
		$this->doc .= $this->tabs . $this->build_tag($tag, $attr) . "\n";
		$this->tabs .= "\t";
	}

	/*
        * Function to close the xml group
        *
        * @return      
        */
        function close_group()
	{
		$tag = array_pop($this->open_tags);
		$this->tabs = mb_substr($this->tabs, 0, -1);
		$this->doc .= $this->tabs . "</$tag>\n";
	}

	/*
        * Function to add a tag with specific content
        *
        * @return      
        */
        function add_tag($tag, $content = '', $attr = array(), $cdata = false, $htmlspecialchars = false)
	{
		$this->doc .= $this->tabs . $this->build_tag($tag, $attr, ($content === ''));
                
		if ($content !== '')
		{
			if ($htmlspecialchars)
			{
				$this->doc .= htmlspecialchars_uni($content);
			}
			else if ($cdata OR preg_match('/[\<\>\&\'\"\[\]]/', $content))
			{
				$this->doc .= '<![CDATA[' . $this->escape_cdata($content) . ']]>';
			}
			else
			{
				$this->doc .= $content;
			}
                        
			$this->doc .= "</$tag>\n";
		}
	}

	/*
        * Function to build an XML tag
        *
        * @return      
        */
        function build_tag($tag, $attr, $closing = false)
	{
		$tmp = "<$tag";
		if (!empty($attr))
		{
			foreach ($attr AS $attr_name => $attr_key)
			{
				if (mb_strpos($attr_key, '"') !== false)
				{
					$attr_key = htmlspecialchars_uni($attr_key);
				}
                                
				$tmp .= " $attr_name=\"$attr_key\"";
			}
		}
		$tmp .= ($closing ? " />\n" : '>');
		return $tmp;
	}

	/*
        * Function to escape XML CDATA
        *
        * @return      
        */
        function escape_cdata($xml)
	{
		$xml = preg_replace('#[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]#', '', $xml);
		return str_replace(array('<![CDATA[', ']]>'), array('«![CDATA[', ']]»'), $xml);
	}

	/*
        * Function to output the XML document
        *
        * @return      
        */
        function output()
	{
		if (!empty($this->open_tags))
		{
			return false;
		}
                
		return $this->doc;
	}

	/*
        * Function to print the XML
        *
        * @return      
        */
        function print_xml()
	{
		$this->send_content_type_header();
                
		echo $this->fetch_xml_tag() . $this->output();
		exit;
	}
	
	/*
        * Function to handle cdata
        *
        * @return      
        */
        function handle_cdata(&$parser, $data)
	{
		$this->cdata .= $data;
	}
	
	/*
        * Function to add a note
        *
        * @return      
        */
        function add_node(&$child, $name, $value)
	{
		if (!is_array($child) OR !in_array($name, array_keys($child)))
		{
			$child[$name] = $value;
		}
		else if (is_array($child[$name]) AND isset($child[$name][0]))
		{
			$child[$name][] = $value;
		}
		else
		{
			$child[$name] = array($child[$name]);
			$child[$name][] = $value;
		}
	}
	
	/*
        * Function to unescape cdata
        *
        * @return      
        */
        function unescape_cdata($xml)
	{
		static $find, $replace;
		if (!is_array($find))
		{
			$find = array('«![CDATA[', ']]»', "\r\n", "\n");
			$replace = array('<![CDATA[', ']]>', "\n", "\r\n");
		}
		return str_replace($find, $replace, $xml);
	}
	
	/*
        * ...
        *
        * @return      
        */
        function handle_element_start(&$parser, $name, $attribs)
	{
		$this->cdata = '';
		foreach ($attribs as $key => $val)
		{
			if (preg_match('#&[a-z]+;#i', $val))
			{
				$attribs["$key"] = un_htmlspecialchars($val);
			}
		}
		array_unshift($this->stack, array('name' => $name, 'attribs' => $attribs, 'tag_count' => ++$this->tag_count));
	}
	
	/*
        * ...
        *
        * @return      
        */
        function handle_element_end(&$parser, $name)
	{
		$tag = array_shift($this->stack);
		if ($tag['name'] != $name)
		{
			return;
		}
		$output = $tag['attribs'];
		if (trim($this->cdata) !== '' OR $tag['tag_count'] == $this->tag_count)
		{
			if (sizeof($output) == 0)
			{
				$output = $this->unescape_cdata($this->cdata);
			}
			else
			{
				$this->add_node($output, 'value', $this->unescape_cdata($this->cdata));
			}
		}

		if (isset($this->stack[0]))
		{
			$this->add_node($this->stack[0]['attribs'], $name, $output);
		}
		else
		{
			$this->parsedxmldata = $output;
		}
		$this->cdata = '';
	}
	
        /*
        * Function to break down xml tags into usable arrays
        *
        * @param       string        encoding character set (default ISO-8859-1)
        * @param       bool 	     decide if we should empty the xml data being held in memory after processing
        * @param       string        xml filename to process
        * 
        * @return      array         Returns formatted array of xml tag data
        */
	function construct_xml_array($encoding = 'UTF-8', $emptyafter = true, $xmlfile)
	{
		if (!empty($xmlfile))
		{
			$this->xmldata = @file_get_contents(DIR_XML . $xmlfile);
		}
		if (empty($this->xmldata))
		{
			return false;
		}
		if (!($this->xml_parser = xml_parser_create($encoding)))
		{
			return false;
		}	
		xml_parser_set_option($this->xml_parser, XML_OPTION_SKIP_WHITE, 0);
		xml_parser_set_option($this->xml_parser, XML_OPTION_CASE_FOLDING, 0);
		xml_set_character_data_handler($this->xml_parser, array(&$this, 'handle_cdata'));
		xml_set_element_handler($this->xml_parser, array(&$this, 'handle_element_start'), array(&$this, 'handle_element_end'));
		xml_parse($this->xml_parser, $this->xmldata);
		$err = xml_get_error_code($this->xml_parser);
		if ($emptyafter)
		{
			$this->xmldata = '';
			$this->stack = array();
			$this->cdata = '';
		}
		if ($err)
		{
			return false;
		}
		xml_parser_free($this->xml_parser);
		return $this->parsedxmldata;
	}
        
	/*
	* Function to process a valid ILance XML Configuration data
	*
	* @param       array 	     xml data
	* @param       string        error level (unused)
	* 
	* @return      array         Returns formatted array of xml tag data
	*/
	function process_configuration_xml($a = array(), $e)
	{
    		$site_name = $ilance_version = $ilance_build = $date = $main_configuration = '';
		$elementcount = count($a);
		for ($i = 0; $i < $elementcount; $i++)
		{
			if ($a[$i]['tag'] == 'CONFIGURATION')
			{
				if ($a[$i]['type'] == 'open')
				{
					$ilance_version = $a[$i]['attributes']['ILVERSION'];
					$ilance_build = $a[$i]['attributes']['ILBUILD'];
				}
			}
			else if ($a[$i]['tag'] == 'SITENAME')
			{
				if ($a[$i]['type'] == 'complete')
				{
					$site_name = $a[$i]['value'];
				}
			}
			else if ($a[$i]['tag'] == 'DATE')
			{
				if ($a[$i]['type'] == 'complete')
				{
					$date = $a[$i]['value'];
				}
			}
			else if ($a[$i]['tag'] == 'MAIN_CONFIGURATION')
			{
				if ($a[$i]['type'] == 'open')
				{
					$main_configuration = array();
				}
			}
			else if ($a[$i]['tag'] == 'CONFIGURATION_GROUP')
			{
				if ($a[$i]['type'] == 'open')
				{
					$current_groupname = $a[$i]['attributes']['GROUPNAME'];
				}
			}
			else if ($a[$i]['tag'] == 'OPTION')
			{
				if ($a[$i]['type'] == 'complete')
				{
					$main_configuration[$current_groupname][$a[$i]['attributes']['NAME']] = trim($a[$i]['value']);
				}
			}
		}
		$result = array(
            		'ilance_version' => $ilance_version,
			'ilance_build' => $ilance_build,
			'site_name' => $site_name,
			'date' => $date,
			'main_configuration' => $main_configuration
		);
		return $result;
	}
	
        /*
        * Function to process a valid ILance XML Phrases Template data to convert all xml tags into usable arrays
        *
        * @param       array 	     xml data
        * @param       string        error level (unused)
        * 
        * @return      array         Returns formatted array of xml tag data
        */
        function process_lang_xml($a = array(), $e)
        {
                $illang_version = $title = $author = $lang_code = $charset = $locale = $languageiso = $textdirection = $current_phrase_group = $replacements = $canselect = '';
                $phrasearray = $phrase_group_data = array();
                $elementcount = count($a);
                for ($i = 0; $i < $elementcount; $i++)
                {
                        if ($a[$i]['tag'] == 'LANGUAGE')
                        {
                                if (empty($illang_version) AND $a[$i]['type'] == 'open')
                                {
                                        $illang_version = $a[$i]['attributes']['ILVERSION'];
                                }
                        }
                        else if ($a[$i]['tag'] == 'TITLE')
                        {
                                if (empty($title) AND $a[$i]['type'] == 'complete')
                                {
                                        $title = trim($a[$i]['value']);
                                }
                        }
                        else if ($a[$i]['tag'] == 'AUTHOR')
                        {
                                if (empty($author) AND $a[$i]['type'] == 'complete')
                                {
                                        $author = trim($a[$i]['value']);
                                }
                        }
                        else if ($a[$i]['tag'] == 'LANGUAGECODE')
                        {
                                if (empty($lang_code) AND $a[$i]['type'] == 'complete')
                                {
                                        $lang_code = $a[$i]['value'];
                                }
                        }
                        else if ($a[$i]['tag'] == 'CHARSET')
                        {
                                if (empty($charset) AND $a[$i]['type'] == 'complete')
                                {
                                        $charset = trim($a[$i]['value']);
                                }
                        }
                        else if ($a[$i]['tag'] == 'LOCALE')
                        {
                                if (empty($locale) AND $a[$i]['type'] == 'complete')
                                {
                                        $locale = trim($a[$i]['value']);
                                }
                        }
                        else if ($a[$i]['tag'] == 'LANGUAGEISO')
                        {
                                if (empty($languageiso) AND $a[$i]['type'] == 'complete')
                                {
                                        $languageiso = trim($a[$i]['value']);
                                }
                        }
                        else if ($a[$i]['tag'] == 'TEXTDIRECTION')
                        {
                                if (empty($textdirection) AND $a[$i]['type'] == 'complete')
                                {
                                        $textdirection = trim($a[$i]['value']);
                                }
                        }
                        else if ($a[$i]['tag'] == 'REPLACEMENTS')
                        {
                                if (empty($replacements) AND $a[$i]['type'] == 'complete' AND isset($a[$i]['value']))
                                {
                                        $replacements = trim($a[$i]['value']);
                                }
                        }
                        else if ($a[$i]['tag'] == 'CANSELECT')
                        {
                                if (empty($canselect) AND $a[$i]['type'] == 'complete')
                                {
                                        $canselect = trim($a[$i]['value']);
                                }
                        }
                        else if ($a[$i]['tag'] == 'PHRASEGROUP')
                        {
                                if ($a[$i]['type'] == 'open' OR $a[$i]['type'] == 'complete')
                                {
                                        $current_phrase_group = $a[$i]['attributes']['NAME'];
                                        $phrase_group_data[] = array(
                                                $current_phrase_group, 
                                                $a[$i]['attributes']['NAME'], 
                                                $a[$i]['attributes']['DESCRIPTION'],
                                                $a[$i]['attributes']['PRODUCT']
                                        );
                                }
                        }
                        else if ($a[$i]['tag'] == 'PHRASE')
                        {
                                if ($a[$i]['type'] == 'complete')
                                {
                                        $phrasearray[] = array(
                                                $current_phrase_group, 
                                                trim($a[$i]['attributes']['VARNAME']), 
                                                trim($a[$i]['value'])
                                        );
                                }
                        }
                }
                $result = array(
                        'illang_version' => $illang_version, 
                        'title' => $title,
                        'author' => $author,
                        'lang_code' => $lang_code, 
                        'charset' => $charset, 
                        'phrasearray' => $phrasearray, 
                        'phrase_group_data' => $phrase_group_data,
                        'locale' => $locale,
                        'languageiso' => $languageiso,
                        'textdirection' => $textdirection,
                        'replacements' => $replacements,
                        'canselect' => $canselect
                );
                return $result;
        }
        
        /*
        * Function to process a valid ILance XML Email Template data to convert all xml tags into usable arrays
        *
        * @param       array 	     xml data
        * @param       string        error level (unused)
        * 
        * @return      array         Returns formatted array of xml tag data
        */
        function process_email_xml($a = array(), $e)
        {
                $ilversion = $langcode = $charset = $author = $emailname = $emailsubject = $emailbody = $emailtype = $emailvarname = $emailproduct = $emailcansend = $emaildepartmentid = $emailbuyer = $emailseller = $emailadmin = $emailishtml = '';                
                $emailarray = array();
                $arraycount = count($a);
                for ($i = 0; $i < $arraycount; $i++)
                {
                        if ($a[$i]['tag'] == 'LANGUAGE')
                        {
                                if (empty($ilversion) AND $a[$i]['type'] == 'open')
                                {
                                        $ilversion = $a[$i]['attributes']['ILVERSION'];
                                }
                        }
                        else if ($a[$i]['tag'] == 'AUTHOR')
                        {
                                if (empty($author) AND $a[$i]['type'] == 'complete')
                                {
                                        $author = trim($a[$i]['value']);
                                }
                        }
                        else if ($a[$i]['tag'] == 'LANGUAGECODE')
                        {
                                if (empty($langcode) AND $a[$i]['type'] == 'complete')
                                {
                                        $langcode = $a[$i]['value'];
                                }
                        }
                        else if ($a[$i]['tag'] == 'CHARSET')
                        {
                                if (empty($charset) AND $a[$i]['type'] == 'complete')
                                {
                                        $charset = trim($a[$i]['value']);
                                }
                        }
                        else if ($a[$i]['tag'] == 'NAME')
                        {
                                $emailname = $a[$i]['value'];
                        }
                        else if ($a[$i]['tag'] == 'SUBJECT')
                        {
                                $emailsubject = $a[$i]['value'];        
                        }
                        else if ($a[$i]['tag'] == 'MESSAGE')
                        {
                                $emailbody = $a[$i]['value'];     
                        }
                        else if ($a[$i]['tag'] == 'TYPE')
                        {
                                $emailtype = $a[$i]['value'];
                        }
                        else if ($a[$i]['tag'] == 'VARNAME')
                        {
                                $emailvarname = $a[$i]['value'];        
                        }
                        else if ($a[$i]['tag'] == 'PRODUCT')
                        {
                                $emailproduct = $a[$i]['value'];       
                        }
                        else if ($a[$i]['tag'] == 'CANSEND')
                        {
                                $emailcansend = $a[$i]['value'];        
                        }
                        else if ($a[$i]['tag'] == 'DEPARTMENTID')
                        {
                                $emaildepartmentid = $a[$i]['value'];       
                        }
			else if ($a[$i]['tag'] == 'BUYER')
                        {
                                $emailbuyer = $a[$i]['value'];       
                        }
			else if ($a[$i]['tag'] == 'SELLER')
                        {
                                $emailseller = $a[$i]['value'];       
                        }
			else if ($a[$i]['tag'] == 'ADMIN')
                        {
                                $emailadmin = $a[$i]['value'];       
                        }
                        else if ($a[$i]['tag'] == 'ISHTML')
                        {
                                $emailishtml = $a[$i]['value'];       
                        }
                        if (!empty($emailvarname) AND !empty($emailname) AND !empty($emailsubject) AND !empty($emailbody) AND !empty($emailtype) AND !empty($emailproduct) AND !empty($emailcansend) AND !empty($emaildepartmentid))
                        {
                                $emailarray[] = array(
                                        $emailname, 
                                        $emailsubject, 
                                        $emailbody, 
                                        $emailtype,
                                        $emailvarname,
                                        (isset($emailproduct) ? $emailproduct : 'ilance'),
                                        intval($emailcansend),
                                        intval($emaildepartmentid),
					$emailbuyer,
					$emailseller,
					$emailadmin,
                                        intval($emailishtml)
                                );
                                // reset for next email
                                $emailname = $emailsubject = $emailbody = $emailtype = $emailvarname = $emailproduct = $emailcansend = $emaildepartmentid = $emailbuyer = $emailseller = $emailadmin = $emailishtml = '';
                        }
                }
                $result = array(
                        'ilversion' => $ilversion, 
                        'langcode' => $langcode,
                        'author' => $author,
                        'charset' => $charset,
                        'emailarray' => $emailarray
                );
                return $result;
        }
        
        /*
        * Function to process a valid ILance XML CSS Styles xml data
        *
        * @param       array 	     xml data
        * @param       string        error level (unused)
        * 
        * @return      array         Returns formatted array of xml tag data
        */
        function process_style_xml($a = array(), $e)
        {
                $templatearray = array();
                $stylename = $ilversion = $visible = $sort = '';
                $counter = count($a);
                for ($i = 0; $i < $counter; $i++)
                {
                        if ($a[$i]['tag'] == 'STYLE')
                        {
                                if (empty($stylename) AND $a[$i]['type'] == 'open')
                                {
                                        $stylename = $a[$i]['attributes']['NAME'];
                                }
                                if (empty($ilversion) AND $a[$i]['type'] == 'open')
                                {
                                        $ilversion = $a[$i]['attributes']['ILVERSION'];
                                }
                        }
                        else if ($a[$i]['tag'] == 'VISIBLE')
                        {
                                if (empty($visible) AND $a[$i]['type'] == 'complete')
                                {
                                        $visible = $a[$i]['value'];
                                }
                        }
                        else if ($a[$i]['tag'] == 'SORT')
                        {
                                if (empty($sort) AND $a[$i]['type'] == 'complete')
                                {
                                        $sort = $a[$i]['value'];
                                }
                        }
                        else if ($a[$i]['tag'] == 'TEMPLATE')
                        {
                                if ($a[$i]['type'] == 'complete')
                                {
                                        $templatearray[] = array(
                                                $a[$i]['attributes']['NAME'], 
                                                $a[$i]['attributes']['DESCRIPTION'], 
                                                $a[$i]['attributes']['TYPE'],
						$a[$i]['attributes']['PRODUCT'] = (isset($a[$i]['attributes']['PRODUCT']) ? $a[$i]['attributes']['PRODUCT'] : 'ilance'),
						$a[$i]['attributes']['SORT'] = (isset($a[$i]['attributes']['SORT']) ? $a[$i]['attributes']['SORT'] : '100000'),
                                                $a[$i]['value'] = (isset($a[$i]['value']) ? $a[$i]['value'] : ''),
                                        );
                                }
                        }
                }
                $result = array(
                        'name' => $stylename, 
                        'ilversion' => $ilversion, 
                        'visible' => $visible, 
                        'sort' => $sort, 
                        'templatearray' => $templatearray
                );
                return $result;
        }
        
        /*
        * Function to process a valid ILance XML Add-on Installer Package to convert all xml tags into usable arrays
        *
        * @param       array 	     xml data
        * @param       string        error level (unused)
        * 
        * @return      array         Returns formatted array of xml tag data
        */
        function process_addon_xml($a = array(), $e)
	{
                $filestructure = $installcode = $uninstallcode = $upgradecode = $developer = $product = $modulearray = $modulegroup = $setting = $configgroup = $phrasegroup = $taskgroup = $taskarray = $emailgroup = $cssgroup = array();
                $emailname = $emailsubject = $emailbody = $emailtype = $emailvarname = $emailbuyer = $emailseller = $emailadmin = $csselement = $elementdescription = $csstype = $cssstatus = $cssauthor = $styleids = $csscontent = $csssort = $version = $minbuild = $maxbuild = '';
                $current_module_group = 0;
                $count = count($a);
                for ($i = 0; $i < $count; $i++)
                {
                        if ($a[$i]['tag'] == 'VERSION')
                        {
                                $version = $a[$i]['value'];	
                        }
                        else if ($a[$i]['tag'] == 'VERSIONCHECKURL')
                        {
                                $versioncheckurl = $a[$i]['value'];	
                        }
                        else if ($a[$i]['tag'] == 'URL')
                        {
                                $url = $a[$i]['value'];	
                        }
                        else if ($a[$i]['tag'] == 'MINVERSION')
                        {
                                $minversion = $a[$i]['value'];	
                        }
                        else if ($a[$i]['tag'] == 'MAXVERSION')
                        {
                                $maxversion = !empty($a[$i]['value']) ? $a[$i]['value'] : '';	
                        }
			else if ($a[$i]['tag'] == 'MINBUILD')
                        {
                                $minbuild = !empty($a[$i]['value']) ? $a[$i]['value'] : '';	
                        }
                        else if ($a[$i]['tag'] == 'MAXBUILD')
                        {
                                $maxbuild = !empty($a[$i]['value']) ? $a[$i]['value'] : '';	
                        }
                        else if ($a[$i]['tag'] == 'DEVELOPER')
                        {
                                $developer = $a[$i]['value'];	
                        }
                        // #### SETTINGS #######################################
                        else if ($a[$i]['tag'] == 'CONFIGGROUP')
                        {
                                if ($a[$i]['type'] == 'open' OR $a[$i]['type'] == 'complete')
                                {
                                        $current_config_group = $a[$i]['attributes']['GROUPNAME'];
                                        $current_config_table = $a[$i]['attributes']['TABLE'];
                                        $configgroup[] = array(
                                                $a[$i]['attributes']['GROUPNAME'], 
                                                $a[$i]['attributes']['PARENTGROUPNAME'],
                                                $a[$i]['attributes']['DESCRIPTION'],
                                                $a[$i]['attributes']['TABLE']
                                        );
                                }
                        }
                        else if ($a[$i]['tag'] == 'SETTING')
                        {
                                if ($a[$i]['type'] == 'open' OR $a[$i]['type'] == 'complete')
                                {
                                        $setting[] = array(
                                                $current_config_group = isset($current_config_group) ? $current_config_group : '',
                                                $current_config_table = isset($current_config_table) ? $current_config_table : '',
                                                $a[$i]['attributes']['NAME'], 
                                                $a[$i]['attributes']['DESCRIPTION'],
                                                $a[$i]['attributes']['VALUE'],
                                                $a[$i]['attributes']['INPUTTYPE'],
                                                $a[$i]['attributes']['SORT'],
                                                htmlspecialchars(trim($a[$i]['value']), ENT_COMPAT, $e)
                                        );
                                }
                        }
                        // #### PRODUCT DATA ###################################
                        else if ($a[$i]['tag'] == 'MODULEGROUP')
                        {
                                if ($a[$i]['type'] == 'open' OR $a[$i]['type'] == 'complete')
                                {
                                        $current_module_group = $a[$i]['attributes']['NAME'];
                                        $modulegroup[] = array(
                                                $a[$i]['attributes']['NAME'], 
                                                $a[$i]['attributes']['MODULENAME'],
                                                $a[$i]['attributes']['FOLDER'], 
                                                $current_config_table = isset($current_config_table) ? $current_config_table : '',
                                        );
                                }
                        }
                        else if ($a[$i]['tag'] == 'MODULE')
                        {
                                if ($a[$i]['type'] == 'complete')
                                {
                                        $modulearray[] = array(
                                                $current_module_group,
                                                trim($a[$i]['attributes']['TAB']),
                                                trim($a[$i]['attributes']['SUBCMD']),
                                                trim($a[$i]['attributes']['PARENTID']),
                                                trim($a[$i]['attributes']['SORT']),
                                                trim($a[$i]['attributes']['KEY']),
                                                trim($a[$i]['value'])
                                        );
                                }
                        }
                        // #### PHRASES ########################################
                        else if ($a[$i]['tag'] == 'PHRASEGROUP')
                        {
                                if ($a[$i]['type'] == 'open' || $a[$i]['type'] == 'complete')
                                {
                                        $productname = !empty($a[$i]['attributes']['PRODUCT']) ? $a[$i]['attributes']['PRODUCT'] : mb_strtolower($a[$i]['attributes']['NAME']);
                                        $current_phrase_group = !empty($a[$i]['attributes']['NAME']) ? $a[$i]['attributes']['NAME'] : '';
                                        $phrasegroup[] = array(
                                                $a[$i]['attributes']['NAME'], 
                                                $a[$i]['attributes']['DESCRIPTION'],
                                                $productname
                                        );
                                }
                        }
                        else if ($a[$i]['tag'] == 'PHRASE')
                        {
                                if ($a[$i]['type'] == 'complete')
                                {
                                        $phrasearray[] = array(
                                                $current_phrase_group, 
                                                trim($a[$i]['attributes']['VARNAME']), 
                                                htmlspecialchars(trim($a[$i]['value']), ENT_COMPAT, $e)
                                        );
                                }
                        }
                        // #### FILE STRUCTURE #################################
                        else if ($a[$i]['tag'] == 'FILE')
                        {
                                if ($a[$i]['type'] == 'complete')
                                {
                                        $filestructure[] = array(
                                                ((!empty($a[$i]['attributes']['MD5']) AND mb_strlen($a[$i]['attributes']['MD5']) == 32) ? $a[$i]['attributes']['MD5'] : ''),
                                                (!empty($a[$i]['value']) ? htmlspecialchars(trim($a[$i]['value']), ENT_COMPAT, $e) : '')
                                        );
                                }
                        }
                        // #### SCHEDULED TASKS ################################
                        else if ($a[$i]['tag'] == 'TASK')
                        {
                                if ($a[$i]['type'] == 'open' || $a[$i]['type'] == 'complete')
                                {
                                        $current_task_group = !empty($a[$i]['attributes']['VARNAME']) ? $a[$i]['attributes']['VARNAME'] : '';
                                        $taskgroup[] = array(
                                                $a[$i]['attributes']['VARNAME'], 
                                                $a[$i]['attributes']['FILENAME'],
                                                $a[$i]['attributes']['ACTIVE'],
                                                $a[$i]['attributes']['LOGLEVEL'],
                                                $a[$i]['attributes']['PRODUCT']
                                        );
                                }
                        }
                        else if ($a[$i]['tag'] == 'SCHEDULE')
                        {
                                if ($a[$i]['type'] == 'complete')
                                {
                                        $taskarray[] = array(
                                                $current_task_group, 
                                                trim($a[$i]['attributes']['WEEKDAY']),
                                                trim($a[$i]['attributes']['DAY']),
                                                trim($a[$i]['attributes']['HOUR']),
                                                trim($a[$i]['attributes']['MINUTE'])
                                        );
                                }
                        }
                        // #### EMAIL TEMPLATES ################################                        
                        else if ($a[$i]['tag'] == 'NAME')
                        {
                                $emailname = $a[$i]['value'];
                        }
                        else if ($a[$i]['tag'] == 'SUBJECT')
                        {
                                $emailsubject = $a[$i]['value'];        
                        }
                        else if ($a[$i]['tag'] == 'MESSAGE')
                        {
                                $emailbody = $a[$i]['value'];     
                        }
                        else if ($a[$i]['tag'] == 'TYPE')
                        {
                                $emailtype = $a[$i]['value'];
                        }
                        else if ($a[$i]['tag'] == 'VARNAME')
                        {
                                $emailvarname = $a[$i]['value'];        
                        }
			else if ($a[$i]['tag'] == 'BUYER')
                        {
                                $emailbuyer = $a[$i]['value'];        
                        }
			else if ($a[$i]['tag'] == 'SELLER')
                        {
                                $emailseller = $a[$i]['value'];        
                        }
			else if ($a[$i]['tag'] == 'ADMIN')
                        {
                                $emailadmin = $a[$i]['value'];        
                        }
                        if (!empty($emailvarname) AND !empty($emailname) AND !empty($emailsubject) AND !empty($emailtype) AND !empty($emailbody))
                        {
                                $emailgroup[] = array(
                                        $emailvarname,
                                        $emailname, 
                                        $emailsubject, 
                                        $emailtype,
                                        $emailbody,
					$emailbuyer,
					$emailseller,
					$emailadmin
                                );
                                // reset for next email
                                $emailname = $emailsubject = $emailbody = $emailtype = $emailvarname = $emailbuyer = $emailseller = $emailadmin = '';
                        }
			// #### CSS TEMPLATES ##################################
                        else if ($a[$i]['tag'] == 'CSSELEMENT')
                        {
                                $csselement = $a[$i]['value'];
                        }
                        else if ($a[$i]['tag'] == 'ELEMENTDESCRIPTION')
                        {
                                $elementdescription = isset($a[$i]['value']) ? $a[$i]['value'] : '';        
                        }
                        else if ($a[$i]['tag'] == 'CSSTYPE')
                        {
                                $csstype = $a[$i]['value'];     
                        }
                        else if ($a[$i]['tag'] == 'CSSSTATUS')
                        {
                                $cssstatus = $a[$i]['value'];
                        }
                        else if ($a[$i]['tag'] == 'CSSAUTHOR')
                        {
                                $cssauthor = $a[$i]['value'];        
                        }
			else if ($a[$i]['tag'] == 'STYLEIDS')
                        {
                                $styleids = $a[$i]['value'];        
                        }
			else if ($a[$i]['tag'] == 'CSSCONTENT')
                        {
                                $csscontent = $a[$i]['value'];        
                        }
			else if ($a[$i]['tag'] == 'CSSSORT')
			{
				$csssort = $a[$i]['value'];
			}
                        if (!empty($csselement) AND !empty($csstype) AND !empty($cssstatus) AND !empty($styleids) AND !empty($csscontent))
                        {
                                $cssgroup[] = array(
                                        $csselement,
					$elementdescription,
                                        $csstype, 
                                        $cssstatus, 
                                        $cssauthor,
                                        $styleids,
					$csscontent,
					$csssort
                                );
                                // reset for next css template
                                $csselement = $elementdescription = $csstype = $cssstatus = $cssauthor = $styleids = $csscontent = $csssort = '';
                        }
                        // #### INSTALLATION CODE ##############################
                        else if ($a[$i]['tag'] == 'INSTALLCODE')
                        {
                                if ($a[$i]['type'] == 'complete')
                                {
                                        $installcode = !empty($a[$i]['value']) ? trim($a[$i]['value']) : '';
                                }
                        }
                        // #### UNINSTALLATION CODE ############################
                        else if ($a[$i]['tag'] == 'UNINSTALLCODE')
                        {
                                if ($a[$i]['type'] == 'complete')
                                {
                                        $uninstallcode = !empty($a[$i]['value']) ? trim($a[$i]['value']) : '';
                                }
                        }
                        // #### UPGRADE CODE ###################################
                        else if ($a[$i]['tag'] == 'UPGRADECODE')
                        {
                                if ($a[$i]['type'] == 'complete')
                                {
                                        $upgradecode = !empty($a[$i]['value']) ? trim($a[$i]['value']) : '';
                                }
                        }
                }
                $product[] = array($version, $versioncheckurl, $url, $minversion, $maxversion, $minbuild, $maxbuild);
                $result = array(
                        'product' 	=> $product,
                        'configgroup' 	=> isset($configgroup) 	 ? $configgroup : '',
                        'setting' 	=> isset($setting) 	 ? $setting : '',
                        'modulearray' 	=> $modulearray,
                        'modulegroup' 	=> $modulegroup,
                        'phrasearray' 	=> isset($phrasearray) 	 ? $phrasearray : '',
                        'phrasegroup' 	=> isset($phrasegroup) 	 ? $phrasegroup : '',                        
                        'taskarray' 	=> isset($taskarray) 	 ? $taskarray : '',
                        'taskgroup' 	=> isset($taskgroup) 	 ? $taskgroup : '',
                        'emailgroup' 	=> isset($emailgroup) 	 ? $emailgroup : '',
                        'installcode' 	=> isset($installcode) 	 ? $installcode : '',
                        'uninstallcode' => isset($uninstallcode) ? $uninstallcode : '',
                        'upgradecode' 	=> isset($upgradecode) 	 ? $upgradecode : '',
                        'developer' 	=> isset($developer) 	 ? $developer : '',
                        'filestructure' => isset($filestructure) ? $filestructure : '',
			'cssgroup' 	=> isset($cssgroup)      ? $cssgroup : ''
                );
                return $result;
        }
        
        /*
        * Function to process a valid ILance XML configuration data to convert all xml tags into usable arrays
        *
        * @param       array 	     xml data
        * @param       string        error level (unused)
        * 
        * @return      array         Returns formatted array of xml tag data
        */
        function process_config_xml($a = array(), $e)
        {
                $ilversion = $current_setting_group = '';
                $settingarray = array();
                $elementcount = count($a);
                for ($i = 0; $i < $elementcount; $i++)
                {
                        if ($a[$i]['tag'] == 'CONFIG')
                        {
                                if (empty($ilversion) AND $a[$i]['type'] == 'open')
                                {
                                        $ilversion = $a[$i]['attributes']['ILVERSION'];
                                }
                        }
                        else if ($a[$i]['tag'] == 'CONFIGGROUP')
                        {
                                if ($a[$i]['type'] == 'open' OR $a[$i]['type'] == 'complete')
                                {
                                        $current_setting_group = $a[$i]['attributes']['GROUPNAME'];
                                        $settinggrouparray[] = array(
                                                $current_setting_group, 
                                                $a[$i]['attributes']['PARENTGROUPNAME'], 
                                                $a[$i]['attributes']['GROUPNAME'],
                                                $a[$i]['attributes']['DESCRIPTION'],
                                                $a[$i]['attributes']['HELP'],
                                                $a[$i]['attributes']['CLASS'],
                                                $a[$i]['attributes']['SORT']
                                        );
                                }
                        }
                        else if ($a[$i]['tag'] == 'SETTING')
                        {
                                if ($a[$i]['type'] == 'complete')
                                {
                                        $settingarray[] = array(
                                                $current_setting_group, 
                                                trim($a[$i]['attributes']['NAME']),
                                                trim($a[$i]['attributes']['DESCRIPTION']),
                                                trim($a[$i]['attributes']['VALUE']),
                                                trim($a[$i]['attributes']['CONFIGGROUP']),
                                                trim($a[$i]['attributes']['INPUTTYPE']),
                                                trim($a[$i]['attributes']['INPUTCODE']),
                                                trim($a[$i]['attributes']['INPUTNAME']),
                                                trim($a[$i]['attributes']['HELP']),
                                                trim($a[$i]['attributes']['SORT']),
                                                trim($a[$i]['attributes']['VISIBLE'])
                                        );
                                }
                        }
                }
                $result = array(
                        'ilversion' => $ilversion, 
                        'settingarray' => $settingarray, 
                        'settinggrouparray' => $settinggrouparray,
                );
                return $result;
        }
        
        function search_to_xml($array = array(), $doheaders = true)
        {
                global $ilconfig;
                $skiptags = array();
                $xml = "";
                if ($doheaders)
                {
                        header("Content-type: text/xml; charset=" . $ilconfig['template_charset'] . "");
                        $xml = "<?xml version=\"1.0\" encoding=\"" . $ilconfig['template_charset'] . "\"?>" . LINEBREAK;
                }
                if (!empty($array) AND is_array($array))
                {
                        $xml .= "<search>" . LINEBREAK;
                        foreach ($array AS $key => $value)
                        {
                                $xml .= "\t<result>" . LINEBREAK;
                                if (isset($value) AND !empty($value) AND is_array($value))
                                {
                                        foreach ($value AS $field => $data)
                                        {
                                                $xml .= "\t\t<$field><![CDATA[" . ilance_htmlentities($data) . "]]></$field>" . LINEBREAK;
                                        }
                                }
                                $xml .= "\t</result>" . LINEBREAK;
                        }
                        $xml .= "</search>";
                }
                return $xml;
        }
        
        function xml_to_array($xml = '')
        {
                $xmlary = array();
                $reels = '/<(\w+)\s*([^\/>]*)\s*(?:\/>|>(.*)<\/\s*\\1\s*>)/s';
                $reattrs = '/(\w+)=(?:"|\')([^"\']*)(:?"|\')/';
                preg_match_all($reels, $xml, $elements);
                foreach ($elements[1] AS $ie => $xx)
                {
                        $xmlary[$ie]["name"] = $elements[1][$ie];
                        if ($attributes = trim($elements[2][$ie]))
                        {
                                preg_match_all($reattrs, $attributes, $att);
                                foreach ($att[1] as $ia => $xx)
                                {
                                        $xmlary[$ie]["attributes"][$att[1][$ia]] = $att[2][$ia];
                                }
                        }
                        $cdend = mb_strpos($elements[3][$ie], "<");
                        if ($cdend > 0)
                        {
                                $xmlary[$ie]["text"] = mb_substr($elements[3][$ie], 0, $cdend - 1);
                        }
                        if (preg_match($reels, $elements[3][$ie]))
                        {
                                $xmlary[$ie]["elements"] = $this->xml_to_array($elements[3][$ie]);
                        }
                        else if ($elements[3][$ie])
                        {
                                $xmlary[$ie]["text"] = $elements[3][$ie];
                        }
                }
                return $xmlary;
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>