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
* Styles class to perform the majority of skinning and template functions in ILance
*
* @package      iLance\Styles
* @version	4.0.0.8059
* @author       ILance
*/
class styles
{
	/**
	* This will store the compiled css stylesheet
	*/
	public $computed_style;
	public $css_raw_file;
	public $css_output_file;
	public $css_output_filepath;
	public $css_output_url;
	public $styleid;
	public $filehash;
	public $filehash_current;

	/*
	* Constructor
	* 
	* This class will now make use of our new caching system to prevent loading common style templates
	* for each page hit.  This will ultimately reduce the hits to MySQL.
	*/
	function __construct()
	{
		global $ilance, $headinclude, $templatevars;
		if (empty($_SESSION['ilancedata']['user']['styleid']) OR $this->is_styleid_valid($_SESSION['ilancedata']['user']['styleid']) == false)
		{
			$_SESSION['ilancedata']['user']['styleid'] = $ilconfig['defaultstyle'];
		}
		$this->styleid = intval($_SESSION['ilancedata']['user']['styleid']);
		$this->init_templatevars();
		
		($apihook = $ilance->api('styles_end')) ? eval($apihook) : false;
	}

	/*
	* Function to determine if the selected style id is valid within the datastore
	*
	* @param       integer     style id
	* 
	* @return      bool        Returns true or false
	*/
	function is_styleid_valid($styleid = 0)
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "visible
			FROM " . DB_PREFIX . "styles
			WHERE styleid = '" . intval($styleid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			return true;
		}
		return false;
	}

	/*
	* Function to fetch our style filehash
	*
	* @return      string	   Returns the style filehash
	*/
	function fetch_style_filehash()
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "filehash
			FROM " . DB_PREFIX . "styles
			WHERE styleid = '" . intval($this->styleid) . "'
		", 0, null, __FILE__, __LINE__);
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		return $res['filehash'];
	}

	/*
	* Function to update our style filehash
	*
	* @return      nothing
	*/
	function update_style_filehash()
	{
		global $ilance;
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "styles
			    SET filehash = '" . $ilance->db->escape_string($this->filehash_current) . "'
			WHERE styleid = '" . intval($this->styleid) . "'
		", 0, null, __FILE__, __LINE__);
	}

	/*
	* Function to return site style selection / theme pulldown menu on footer pages
	*
	* @return      string       HTML formatted style pulldown menu
	*/
	function print_styles_pulldown($selected = '', $autosubmit = '', $name = 'styleid')
	{
		global $ilance;
		$onchange = (isset($autosubmit) AND $autosubmit) ? 'onchange="urlswitch(this, \'dostyle\')"' : '';
		$html = '<select name="' . $name . '" id="' . $name . '" ' . $onchange . ' class="select">';
		$html .= '<optgroup label="{_choose_style}">';
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "styleid, name
			FROM " . DB_PREFIX . "styles
			WHERE visible = '1'
			ORDER BY styleid ASC
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$stylecount = 0;
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$sel = (isset($selected) AND $res['styleid'] == $selected) ? 'selected="selected"' : '';
				$html .= '<option value="' . $res['styleid'] . '" ' . $sel . '>' . stripslashes($res['name']) . '</option>';
				$stylecount++;
			}
		}
		$html .= '</optgroup></select>';
		if (isset($autosubmit) AND $autosubmit AND $stylecount <= 1 AND defined('LOCATION') AND LOCATION != 'admin')
		{
			return false;
		}
		return $html;
	}
	/*
	* Function to init our css template {variables}
	*
	* @return      array         Returns array with available CSS template variables like {attachmentlimit_searchresultsmaxwidth}
	*/
	function init_templatevars()
	{
		global $ilance, $ilconfig, $templatevars;
		if (($templatevars = $ilance->cachecore->fetch("templatevars_" . $this->styleid . "_" . $_SESSION['ilancedata']['user']['languageid'])) === false)
		{
			$templatevars = array();
			$sql_v = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "name, content
				FROM " . DB_PREFIX . "templates
				WHERE styleid = '" . $this->styleid . "'
					AND type = 'variable'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_v) > 0)
			{
				while ($res_v = $ilance->db->fetch_array($sql_v, DB_ASSOC))
				{
					$templatevars[$res_v['name']] = stripslashes($res_v['content']);
				}
				unset($res_v);
			}
			unset($sql_v);
			$templatevars['attachmentlimit_productphotowidth'] = $ilconfig['attachmentlimit_productphotowidth'];
			$templatevars['attachmentlimit_productphotoheight'] = $ilconfig['attachmentlimit_productphotoheight'];
			$templatevars['attachmentlimit_productphotothumbwidth'] = $ilconfig['attachmentlimit_productphotothumbwidth'];
			$templatevars['attachmentlimit_productphotothumbheight'] = $ilconfig['attachmentlimit_productphotothumbheight'];
			$templatevars['attachmentlimit_searchresultsmaxwidth'] = $ilconfig['attachmentlimit_searchresultsmaxwidth'];
			$templatevars['attachmentlimit_searchresultsmaxheight'] = $ilconfig['attachmentlimit_searchresultsmaxheight'];
			$templatevars['attachmentlimit_searchresultsgallerymaxwidth'] = $ilconfig['attachmentlimit_searchresultsgallerymaxwidth'];
			$templatevars['attachmentlimit_searchresultsgallerymaxheight'] = $ilconfig['attachmentlimit_searchresultsgallerymaxheight'];
			$templatevars['attachmentlimit_searchresultssnapshotmaxwidth'] = $ilconfig['attachmentlimit_searchresultssnapshotmaxwidth'];
			$templatevars['attachmentlimit_searchresultssnapshotmaxheight'] = $ilconfig['attachmentlimit_searchresultssnapshotmaxheight'];
			$templatevars['attachmentlimit_portfoliothumbwidth'] = $ilconfig['attachmentlimit_portfoliothumbwidth'];
			$templatevars['attachmentlimit_portfoliothumbheight'] = $ilconfig['attachmentlimit_portfoliothumbheight'];
			$templatevars['attachmentlimit_profilemaxwidth'] = $ilconfig['attachmentlimit_profilemaxwidth'];
			$templatevars['attachmentlimit_profilemaxheight'] = $ilconfig['attachmentlimit_profilemaxheight'];
			$templatevars['template_relativeimagepath'] = $ilconfig['template_relativeimagepath'];
			$templatevars['template_charset'] = $ilance->language->cache[$_SESSION['ilancedata']['user']['languageid']]['charset'];
			$templatevars['template_languagecode'] = $ilance->language->cache[$_SESSION['ilancedata']['user']['languageid']]['languageiso'];
			$textdirection = (isset($_SESSION['ilancedata']['user']['languageid'])) ? $ilance->language->cache[$_SESSION['ilancedata']['user']['languageid']]['textdirection'] : $ilconfig['template_textdirection'];
			$templatevars['template_textdirection'] = (($templatevars['template_textdirection'] != $textdirection) ? $templatevars['template_textdirection'] : $textdirection);
			$templatevars['template_textalignment_alt'] = $templatevars['template_textalignment_alt'] = (($templatevars['template_textalignment'] == 'left') ? 'right' : 'left');
			unset($textdirection);
			$ilance->cachecore->store("templatevars_" . $this->styleid, $templatevars);
		}
		$ilconfig = array_merge($ilconfig, $templatevars);
		return $templatevars;
	}
	
	/*
	* Function to init our <head> Javascript
	*
	* @return      nothing
	*/
	function init_head_js()
	{
		global $headinclude, $ilconfig, $templatevars, $ilance, $ilpage, $jsinclude;
		$js = '';
		if (isset($ilance->template->jsinclude['header']))
		{
			$jsinclude['header'] = $ilance->template->jsinclude['header'];
		}
		$js .= "<script type=\"text/javascript\" charset=\"" . mb_strtolower($ilance->language->cache[$_SESSION['ilancedata']['user']['languageid']]['charset']) . "\"><!--\nvar ILSESSION = \"" . session_id() . "\";";
		$js .= (defined('LOCATION') AND LOCATION == 'admin') ? "var ILADMIN = \"1\";" : "var ILADMIN = \"0\";";
		$js .= "var ILBASE = \"" . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . "\";";
		$js .= "var JSBASE = \"" . $ilconfig['template_relativeimagepath'] . DIR_FUNCT_NAME . "/" . DIR_JS_NAME . "/\";";
		$js .= "var SWFBASE = \"" . $ilconfig['template_relativeimagepath'] . DIR_FUNCT_NAME . "/" . DIR_SWF_NAME . "/\";";
		$js .= "var MP3BASE = \"" . $ilconfig['template_relativeimagepath'] . DIR_FUNCT_NAME . "/" . DIR_SOUNDS_NAME . "/\";";
		$js .= "var IMAGEBASE = \"" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "\";";
		$js .= "var THUMBWIDTH = \"" . $ilconfig['attachmentlimit_searchresultsmaxwidth'] . "\";";
		$js .= "var THUMBHEIGHT = \"" . $ilconfig['attachmentlimit_searchresultsmaxheight'] . "\";";
		$js .= "var THUMBGWIDTH = \"" . $ilconfig['attachmentlimit_searchresultsgallerymaxwidth'] . "\";";
		$js .= "var THUMBGHEIGHT = \"" . $ilconfig['attachmentlimit_searchresultsgallerymaxheight'] . "\";";
		$js .= "var ILNAME = \"" . $ilconfig['globalsecurity_cookiename'] . "\";";
		$js .= "var ILTOKEN = \"" . TOKEN . "\";";
		$js .= "var PAGEURL = \"" . ilance_htmlentities(un_htmlspecialchars(PAGEURL)) . "\";";
		$js .= "var AJAXURL = \"" . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['ajax'] . "\";";
		$js .= "var CDNURL = \"" . $ilconfig['template_relativeimagepath_cdn'] . "\";";
		$js .= "var ESCROW = \"" . $ilconfig['escrowsystem_enabled'] . "\";";
		$js .= "var DISTANCE = \"" . $ilconfig['globalserver_enabledistanceradius'] . "\";";
		$js .= "var UID = \"" . (!empty($_SESSION['ilancedata']['user']['userid']) ? $_SESSION['ilancedata']['user']['userid'] : 0) . "\";";
		$js .= "var MINIFY = \"" . $ilconfig['globalfilters_jsminify'] . "\";";
		$js .= "var SSL = \"" . ((PROTOCOL_REQUEST == 'https') ? "1" : "0") . "\";";
		$js .= "var SITEID = \"" . SITE_ID . "\";";
		$js .= "var PRODUCT = \"" . $ilconfig['globalauctionsettings_productauctionsenabled'] . "\";";
		$js .= "var SERVICE = \"" . $ilconfig['globalauctionsettings_serviceauctionsenabled'] . "\";";
		$js .= "var LTR = \"" . (($ilconfig['template_textalignment'] == 'left') ? "1" : "0") . "\";\n";
		
		($apihook = $ilance->api('init_head_js_start')) ? eval($apihook) : false;
		
		$js .= "//--></script>\n";
		
		($apihook = $ilance->api('init_head_js_end')) ? eval($apihook) : false;
		
		if (is_array($jsinclude['header']) AND count($jsinclude['header']) > 0)
		{
			$js .=  "<script type=\"text/javascript\" src=\"" . $ilconfig['template_relativeimagepath'] . (($ilconfig['globalauctionsettings_seourls']) ? 'javascript' : $ilpage['javascript']) . '?dojs=';
			$js .= $this->jsarr2txt($jsinclude['header']);
			$js .= "\" charset=\"" . mb_strtolower($ilance->language->cache[$_SESSION['ilancedata']['user']['languageid']]['charset']) . "\"></script>";
			$js .= (in_array('menu', $jsinclude['header'])) ? "\n<script type=\"text/javascript\" charset=\"" . mb_strtolower($ilance->language->cache[$_SESSION['ilancedata']['user']['languageid']]['charset']) . "\"><!--\nvar d = new v3lib();\n//--></script>\n" : '';
		}
		
		($apihook = $ilance->api('init_head_js_final')) ? eval($apihook) : false;
		
		$headinclude = $js . $headinclude;
	}
	
	/*
	* Function to init Javascript just before our </body>
	*
	* @return      nothing
	*/
	function init_foot_js()
	{
		global $footinclude, $ilconfig, $ilance, $ilpage, $jsinclude;
		$js = '';
		if (isset($ilance->template->jsinclude['footer']))
		{
			$jsinclude['footer'] = $ilance->template->jsinclude['footer'];
		}
		
		($apihook = $ilance->api('init_foot_js_start')) ? eval($apihook) : false;
		
		if (is_array($jsinclude['footer']) AND count($jsinclude['footer']) > 0)
		{
			$js .=  "<script type=\"text/javascript\" src=\"" . $ilconfig['template_relativeimagepath'] . (($ilconfig['globalauctionsettings_seourls']) ? 'javascript' : $ilpage['javascript']) . '?dojs=';
			$js .= $this->jsarr2txt($jsinclude['footer']);
			$js .= "\" charset=\"" . mb_strtolower($ilance->language->cache[$_SESSION['ilancedata']['user']['languageid']]['charset']) . "\"></script>";
			$js .= in_array('menu', $jsinclude['footer']) ? "\n<script type=\"text/javascript\" charset=\"" . mb_strtolower($ilance->language->cache[$_SESSION['ilancedata']['user']['languageid']]['charset']) . "\"><!--\nvar d = new v3lib();\n//--></script>\n" : '';
		}
		
		($apihook = $ilance->api('init_foot_js_end')) ? eval($apihook) : false;
		
		$footinclude = $js . $footinclude;
	}

	/*
	* Function to parse Javascript array to string
	*
	* @return      string	    inline Javascript
	*/
	function jsarr2txt($arr = array())
	{
		$bit = '';
		foreach ($arr as $k => $v)
		{
			switch ($v)
			{
				case 'jquery':
				{
					$bit .= "jquery,jquery_carousel,jquery_easing,";
					break;
				}
				case 'modal':
				{
					$bit .= "jquery_blockui,jquery_modal,";
					break;
				}
				default:
				{
					$bit .= $v . ',';
					break;
				}
			}
		}
		return (!empty($bit) ? substr($bit, 0, -1) : $bit);
	}
	
	/*
	* Function to init our <head> CSS
	*
	* @return      nothing
	*/
	function init_head_css()
	{
		global $ilconfig, $ilance, $headinclude;
		$this->filehash = $this->fetch_style_filehash();
		if (defined('LOCATION') AND LOCATION == 'admin')
		{
			$this->css_output_file = 'css_style_' . $this->styleid . '_admin.css';
			$this->css_raw_file = 'admin_' . $this->styleid . '.css';
		}
		else
		{
			$this->css_output_file = 'css_style_' . $this->styleid . '_client.css';
			$this->css_raw_file = 'client_' . $this->styleid . '.css';
		}
		$this->css_output_filepath = DIR_TMP_CSS . $this->css_output_file;
		$this->css_output_url = $ilconfig['template_relativeimagepath'] . DIR_TMP_NAME . '/' . DIR_CSS_NAME . '/' . $this->css_output_file;
		if (file_exists(DIR_CSS . $this->css_raw_file))
		{
			$this->computed_style = file_get_contents(DIR_CSS . $this->css_raw_file);
		}
		else
		{
			$raw = (defined('LOCATION') AND LOCATION == 'admin') ? 'admin_raw.css' : 'client_raw.css';
			$this->computed_style = file_get_contents(DIR_CSS . $raw);
			@file_put_contents(DIR_CSS . $this->css_raw_file, $this->computed_style);
		}
		$this->filehash_current = md5($this->computed_style);
		if (!file_exists($this->css_output_filepath) OR $this->filehash_current != $this->filehash)
		{
			$this->update_style_filehash();
			$this->parse_css_variables();
			$this->save_output_css();
		}
		unset($this->computed_style);
		$headinclude = "\n<link type=\"text/css\" rel=\"stylesheet\" href=\"" . $ilance->styles->css_output_url . "\"  media=\"screen\" id=\"html\" />\n" . $headinclude;
	}

	/*
	* Function to minify our CSS
	*
	* @return      nothing
	*/
	function minify_css()
	{
		global $ilconfig;
		if ($ilconfig['globalfilters_jsminify'])
		{
			$this->computed_style = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $this->computed_style);
			$this->computed_style = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $this->computed_style);
		}
	}

	/*
	* Function to parse our CSS {variables}
	*
	* @return      nothing
	*/
	function parse_css_variables()
	{
		global $templatevars;
		$templatevars = $this->init_templatevars();
		$pattern = '/{([\w\d_]+)}/';
		if (preg_match_all($pattern, $this->computed_style, $matches) !== false)
		{
			$matches = array_values(array_unique($matches[1]));
			$replaceable = array ();
			foreach ($matches AS $key)
			{
				$replaceable['{' . $key . '}'] = $templatevars["$key"];
			}
			$this->computed_style = str_replace(array_keys($replaceable), array_values($replaceable), $this->computed_style);
			unset($replaceable);
			unset($matches);
		}
	}

	/*
	* Function to save our CSS cache file
	*
	* @return      nothing
	*/
	function save_output_css()
	{
		$css_style['unique_name'] = '';
		do
		{
			$css_style['unique_name'] = rand(0, 100000);
		}
		while (file_exists(DIR_TMP_CSS . $css_style['unique_name'])); 
		{
			$f = fopen(DIR_TMP_CSS . $css_style['unique_name'], 'w');
			if ($f === false)
			{
				@unlink(DIR_TMP_CSS . $css_style['unique_name']);
			}
			else
			{
				$this->minify_css();
				fwrite($f, $this->computed_style);
				fclose($f);
				@unlink($this->css_output_filepath);
				@rename(DIR_TMP_CSS . $css_style['unique_name'], $this->css_output_filepath);
				@unlink(DIR_TMP_CSS . $css_style['unique_name']);
			}
		}
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>