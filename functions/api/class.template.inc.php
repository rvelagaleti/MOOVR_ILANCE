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
* Template class to perform the majority of custom template operations in ILance
*
* @package      iLance\Template
* @version	4.0.0.8059
* @author       ILance
*/
class template
{
	/*
	* The ILance registry object
	*
	* @var	    $ilance
	*/
	var $registry = null;

	/**
        * This will store the current template into the registry
        *
        * @var array
        */
	var $templateregistry = array();

	/**
        * This will store the variable modifier pipe action
        *
        * @var str
        */
	var $modifierpipe = '|';

	/**
        * This will store the opening template variable
        *
        * @var str
        */
	var $start = '{';

	/**
        * This will store the closing template variable
        *
        * @var str
        */
	var $end = '}';

	/**
        * This will store the opening template variable for language phrases
        *
        * @var str
        */
	var $phrasestart = '{_';

	/**
        * This will store the closing template variable for language phrases
        *
        * @var str
        */
	var $phraseend = '}';

	/**
        * This will store the tags used to prevent a template from parsing phrase variables
        * within the html templates (admincp templates area, <text areas>'s etc.)
        *
        * @usage <noparse>......</noparse>
        * @var str
        */
	var $noparse = 'noparse';

	/**
        * This will store all current {var_names} used in a template registry
        *
        * @var array
        */
	var $var_names = array();
	var $regexp = null;
	var $js_phrases_file = null;
	/**
        * This array will store all permitted functions allowed to pass through
        * the template's <if condition=""> conditionals
        *
        * @var array
        */
	var $safe_functions = array(
		'in_array',
		'is_array',
		'is_numeric',
		'function_exists',
		'isset',
		'empty',
		'defined',
		'array',
		'extension_loaded',
		'can_display_financials',
		'check_access',
		'is_subscription_permissions_ready',
		'has_winning_bidder',
		'has_highest_bidder',
		'can_display_element',
		'count',
		'strpos'
	);

	/**
        * This array will store all template bits for the templates
        *
        * @var array
        */
	var $templatebits = array();
	var $headerfooter = true;
	var $dynamic_phrases = true;
	
	/**
        * This information will determine if the pmb modal widget is loaded
        */
	var $pmb_modal_loaded = false;
	var $pmb_modal_wysiwyg = null;
	
	public $isadmincp = false;
	public $findregxp = array();
	public $jsinclude;

	/*
	* Constructor
	*
	* @param       $registry	    ILance registry object
	*/
	function __construct(){}

	/*
	* Loads a template popup into the class (does not use template skinning)
	*
	* @param       string       node
	* @param       string       filename
	*/
	function load_popup($node, $filename)
	{
                if (file_exists(DIR_TEMPLATES . $filename))
                {
                        $this->templateregistry["$node"] = file_get_contents(DIR_TEMPLATES . $filename);
                }
	}

	/*
	* Loads an AdminCP template popup into the class (does not use template skinning)
	*
	* @param       string       node
	* @param       string       filename
	*/
	function load_admincp_popup($node, $filename)
	{
                if (file_exists(DIR_TEMPLATES_ADMIN . $filename))
                {
                        $this->templateregistry["$node"] = file_get_contents(DIR_TEMPLATES_ADMIN . $filename);
                }
	}

	/*
	* Function alias to fetch
	*
	* @param       string       node, admin template, client template, use file path only
	* @param       string       filename
	* @param       boolean      is admin cp template
	* @param       integer      use file path only
	* @param       string       custom argument
	*/
	function load_file($node = '', $filename = '', $admin = 0, $filepathonly = '', $custom = '')
	{
		$this->fetch($node, $filename, $admin, $filepathonly, $custom);
	}

	/*
	* Function to fetch and load a template (client or admin) into a specific node
	*
	* @param       string       node, admin template, client template, use file path only
	* @param       string       filename
	* @param       boolean      is admin cp template
	* @param       integer      use file path only
	* @param       string       custom argument
	*/
        function fetch($node = '', $filename = '', $admin = 0, $filepathonly = '', $custom = '')
	{
		global $ilance, $ilconfig, $v3left_nav, $show, $footer_cron;
		$ilance->timer->start();
		$cache_key = "fetch_" . $node . "_" . $filename . "_" . $admin . "_" . $filepathonly . "_" . $custom;
		$this->isadmincp = ($admin) ? true : false;
		if (($this->templateregistry["$node"] = $ilance->cache->fetch($cache_key)) === false)
		{
			if ($admin)
			{
				$this->templateregistry["$node"] = file_get_contents(DIR_TEMPLATES_ADMIN . 'TEMPLATE_header.html');
				$this->templateregistry["$node"] .= file_get_contents(DIR_TEMPLATES_ADMIN . $filename);
				$this->templateregistry["$node"] .= file_get_contents(DIR_TEMPLATES_ADMIN . 'TEMPLATE_footer.html');
			}
			else
			{
				$shell = 'TEMPLATE_SHELL';
				$this->templateregistry["$shell"] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_SHELL' . ((defined('TEMPLATE_NEWUI') AND defined('TEMPLATE_NEWUI_MODE') AND TEMPLATE_NEWUI AND TEMPLATE_NEWUI_MODE != '') ? '_' . TEMPLATE_NEWUI_MODE : '') . '.html');
				$this->templateregistry['TEMPLATE_headerbit'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_headerbit' . ((defined('TEMPLATE_NEWUI') AND defined('TEMPLATE_NEWUI_MODE') AND TEMPLATE_NEWUI AND TEMPLATE_NEWUI_MODE != '') ? '_' . TEMPLATE_NEWUI_MODE : '') . '.html');
				$this->templateregistry['TEMPLATE_topnav'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_topnav' . ((defined('TEMPLATE_NEWUI') AND defined('TEMPLATE_NEWUI_MODE') AND TEMPLATE_NEWUI AND TEMPLATE_NEWUI_MODE != '') ? '_' . TEMPLATE_NEWUI_MODE : '') . '.html');
				$this->templateregistry['TEMPLATE_breadcrumbbit'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_breadcrumbbit' . ((defined('TEMPLATE_NEWUI') AND defined('TEMPLATE_NEWUI_MODE') AND TEMPLATE_NEWUI AND TEMPLATE_NEWUI_MODE != '') ? '_' . TEMPLATE_NEWUI_MODE : '') . '.html');
				$this->templateregistry['TEMPLATE_infobar'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_infobar' . ((defined('TEMPLATE_NEWUI') AND defined('TEMPLATE_NEWUI_MODE') AND TEMPLATE_NEWUI AND TEMPLATE_NEWUI_MODE != '') ? '_' . TEMPLATE_NEWUI_MODE : '') . '.html');
				$this->templateregistry['TEMPLATE_footerbit'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_footerbit' . ((defined('TEMPLATE_NEWUI') AND defined('TEMPLATE_NEWUI_MODE') AND TEMPLATE_NEWUI AND TEMPLATE_NEWUI_MODE != '') ? '_' . TEMPLATE_NEWUI_MODE : '') . '.html');
				$this->templateregistry['TEMPLATE_pluginheaderbit'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_pluginheaderbit' . ((defined('TEMPLATE_NEWUI') AND defined('TEMPLATE_NEWUI_MODE') AND TEMPLATE_NEWUI AND TEMPLATE_NEWUI_MODE != '') ? '_' . TEMPLATE_NEWUI_MODE : '') . '.html');
				$this->templateregistry['TEMPLATE_pluginfooterbit'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_pluginfooterbit' . ((defined('TEMPLATE_NEWUI') AND defined('TEMPLATE_NEWUI_MODE') AND TEMPLATE_NEWUI AND TEMPLATE_NEWUI_MODE != '') ? '_' . TEMPLATE_NEWUI_MODE : '') . '.html');
				// #### MERGE COMMON TEMPLATES #########################
				$this->templateregistry['template'] = file_get_contents(DIR_TEMPLATES . $filename);
				if (defined('TEMPLATE_DEBUG') AND TEMPLATE_DEBUG)
				{
					$this->templateregistry['template'] = $this->templateregistry['template'] . '<div style="font-size:9px" class="litegray">' . $filename . '</div>';
				}
				$this->templateregistry["$shell"] = str_replace($this->start . 'maincontent' . $this->end, $this->templateregistry['template'], $this->templateregistry["$shell"]);
				$this->templateregistry["$shell"] = str_replace($this->start . 'headerbit' . $this->end, $this->templateregistry['TEMPLATE_headerbit'], $this->templateregistry["$shell"]);
				$this->templateregistry["$shell"] = str_replace($this->start . 'navbar' . $this->end, $this->templateregistry['TEMPLATE_topnav'], $this->templateregistry["$shell"]);
				$this->templateregistry["$shell"] = str_replace($this->start . 'infobar' . $this->end, $this->templateregistry['TEMPLATE_infobar'], $this->templateregistry["$shell"]);
				$this->templateregistry["$shell"] = str_replace($this->start . 'breadcrumbbit' . $this->end, $this->templateregistry['TEMPLATE_breadcrumbbit'], $this->templateregistry["$shell"]);
				$this->templateregistry["$shell"] = str_replace($this->start . 'footerbit' . $this->end, $this->templateregistry['TEMPLATE_footerbit'], $this->templateregistry["$shell"]);
				$this->templateregistry["$node"] = $this->templateregistry["$shell"];
			}
			$this->handle_template_hooks($node);
			$ilance->cache->store($cache_key, $this->templateregistry["$node"]);
		}
		$ilance->timer->stop();
		DEBUG("fetch(\$node = $node, \$filename = $filename, \$admin = $admin, \$filepathonly = $filepathonly, \$custom = $custom) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
	}

	/*
	* Function for handling {apihook[xxxx]} custom html locations within the templates
	*
	* @param       string       node
	* @param       string       custom template data
	*
	* @return      string       Returns modified template with parsed template hook
	*/
	function handle_template_hooks($node = '', $customtemplate = '')
	{
		global $ilance;
		$ilance->timer->start();
		$contents =  (!empty($customtemplate)) ? $customtemplate : $this->templateregistry["$node"];
		if (!empty($contents))
		{
			$pattern = '/' . $this->start . 'apihook' . '\[([\w\d_]+)\]' . $this->end . '/';
			if (preg_match_all($pattern, $contents, $m) !== false)
			{
				$replaceable = array();
				foreach ($m[1] AS $key)
				{
					if (!empty($key))
					{
						$replaceable[$this->start . 'apihook' . '[' . $key . ']' . $this->end] = $ilance->api($key);
					}
				}
				$contents = str_replace(array_keys($replaceable), array_values($replaceable), $contents);
			}
			$this->templateregistry["$node"] = $contents;
		}
		$ilance->timer->stop();
		DEBUG("handle_template_hooks(\$node = $node, \$customtemplate = " . strlen($customtemplate) . ") in " . $ilance->timer->get() . " seconds", 'FUNCTION');
		return $contents;
	}

	/*
	* Function for parsing {hash[key]} style tags for links throughout the templates by Dexter Tad-y
	*
	* @param       string       node
	* @param       array        hash names ( array('ilpage' => $ilpage) )
	* @param       integer      parse globals
	* @param       string       custom template data (optional)
	*/
	function parse_hash($node = '', $hashes, $parseglobals = 0, $data = '')
	{
		global $ilance;
		$ilance->timer->start();
		$contents = (empty($data) OR $data == '') ? $this->templateregistry["$node"] : $data;
		$hnames = '';
		if (!empty($contents))
		{
                        foreach ($hashes AS $hname => $hash)
                        {
                                $pattern = '/' . $this->start . $hname . '\[([\w\d_]+)\]' . $this->end . '/';
                                if (preg_match_all($pattern, $contents, $m) > 0)
                                {
                                        $replaceable = array();
                                        $m[1] = array_unique($m[1]);
                                        foreach ($m[1] as $key)
                                        {
                                                if (isset($hash["$key"]))
                                                {
                                                        $replaceable[$this->start . $hname . '['.$key.']' . $this->end] = $hash["$key"];
                                                }
                                        }
                                        $contents = str_replace(array_keys($replaceable), array_values($replaceable), $contents);
                                }
                                $hnames .= $hname . ', ';
                        }
                        $this->templateregistry["$node"] = $contents;
		}
		$ilance->timer->stop();
		DEBUG("parse_hash(\$node = $node, \$hashes = $hnames, \$parseglobals = $parseglobals, \$data = " . strlen($data) . ") in " . $ilance->timer->get() . " seconds", 'FUNCTION');
		return $this->templateregistry["$node"];
	}

	/*
	* Compiles and produces header template for external addons or plugins
	*
	* @param       string       node
	*/
	function construct_header($node)
	{
		global $ilance, $ilconfig, $login_include, $show;
		$this->templateregistry['TEMPLATE_pluginheaderbit'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_pluginheaderbit' . ((defined('TEMPLATE_NEWUI') AND defined('TEMPLATE_NEWUI_MODE') AND TEMPLATE_NEWUI AND TEMPLATE_NEWUI_MODE != '') ? '_' . TEMPLATE_NEWUI_MODE : '') . '.html');
		$this->templateregistry['TEMPLATE_headerbit'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_headerbit' . ((defined('TEMPLATE_NEWUI') AND defined('TEMPLATE_NEWUI_MODE') AND TEMPLATE_NEWUI AND TEMPLATE_NEWUI_MODE != '') ? '_' . TEMPLATE_NEWUI_MODE : '') . '.html');
		$this->templateregistry['TEMPLATE_topnav'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_topnav' . ((defined('TEMPLATE_NEWUI') AND defined('TEMPLATE_NEWUI_MODE') AND TEMPLATE_NEWUI AND TEMPLATE_NEWUI_MODE != '') ? '_' . TEMPLATE_NEWUI_MODE : '') . '.html');
		$this->templateregistry['TEMPLATE_breadcrumbbit'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_breadcrumbbit' . ((defined('TEMPLATE_NEWUI') AND defined('TEMPLATE_NEWUI_MODE') AND TEMPLATE_NEWUI AND TEMPLATE_NEWUI_MODE != '') ? '_' . TEMPLATE_NEWUI_MODE : '') . '.html');
		$this->templateregistry['TEMPLATE_infobar'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_infobar' . ((defined('TEMPLATE_NEWUI') AND defined('TEMPLATE_NEWUI_MODE') AND TEMPLATE_NEWUI AND TEMPLATE_NEWUI_MODE != '') ? '_' . TEMPLATE_NEWUI_MODE : '') . '.html');
		$this->templateregistry['TEMPLATE_pluginheaderbit'] = str_replace($this->start . 'headerbit' . $this->end, $this->templateregistry['TEMPLATE_headerbit'], $this->templateregistry['TEMPLATE_pluginheaderbit']);
		$this->templateregistry['TEMPLATE_pluginheaderbit'] = str_replace($this->start . 'navbar' . $this->end, $this->templateregistry['TEMPLATE_topnav'], $this->templateregistry['TEMPLATE_pluginheaderbit']);
		$this->templateregistry['TEMPLATE_pluginheaderbit'] = str_replace($this->start . 'infobar' . $this->end, $this->templateregistry['TEMPLATE_infobar'], $this->templateregistry['TEMPLATE_pluginheaderbit']);
		$this->templateregistry['TEMPLATE_pluginheaderbit'] = str_replace($this->start . 'breadcrumbbit' . $this->end, $this->templateregistry['TEMPLATE_breadcrumbbit'], $this->templateregistry['TEMPLATE_pluginheaderbit']);
		$this->templateregistry['TEMPLATE_pluginheaderbit'] = str_replace($this->start . 'login_include' . $this->end, $login_include, $this->templateregistry['TEMPLATE_pluginheaderbit']);
		$this->templateregistry["$node"] = $this->templateregistry['TEMPLATE_pluginheaderbit'];
		$this->handle_template_hooks($node);
	}

	/*
	* Compiles and produces footer template for external addons or plugins
	*
	* @param       string       node
	*/
	function construct_footer($node)
	{
		global $ilance, $ilconfig, $show;
		$this->templateregistry['TEMPLATE_pluginfooterbit'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_pluginfooterbit' . ((defined('TEMPLATE_NEWUI') AND defined('TEMPLATE_NEWUI_MODE') AND TEMPLATE_NEWUI AND TEMPLATE_NEWUI_MODE != '') ? '_' . TEMPLATE_NEWUI_MODE : '') . '.html');
		$this->templateregistry['TEMPLATE_footerbit'] = file_get_contents(DIR_TEMPLATES . 'TEMPLATE_footerbit' . ((defined('TEMPLATE_NEWUI') AND defined('TEMPLATE_NEWUI_MODE') AND TEMPLATE_NEWUI AND TEMPLATE_NEWUI_MODE != '') ? '_' . TEMPLATE_NEWUI_MODE : '') . '.html');
		$this->templateregistry['TEMPLATE_pluginfooterbit'] = str_replace($this->start . 'footerbit' . $this->end, $this->templateregistry['TEMPLATE_footerbit'], $this->templateregistry['TEMPLATE_pluginfooterbit']);
		$this->templateregistry["$node"] = $this->templateregistry['TEMPLATE_pluginfooterbit'];
		$this->handle_template_hooks($node);
	}

	/*
	* Function to load template from the file system.
	*
	* @param       string           filename
	* @param       integer          use template filename commenting
	*/
	function fetch_template($filename = '', $htmlcomments = false)
	{
		global $ilance, $templatevars;
		// fetch template from file system
		$cachekey = "fetch_template_$filename";
		if (($template = $ilance->cache->fetch($cachekey)) === false)
		{
			$template = file_get_contents(DIR_TEMPLATES . $filename);
			$this->templateregistry[$cachekey] = addslashes($template);
			if ($htmlcomments)
			{
				$this->templateregistry[$cachekey] = "<!-- BEGIN TEMPLATE: " . $filename . " -->\n" . $this->templateregistry[$cachekey] . "\n<!-- END TEMPLATE: " . $filename . "-->";
			}
			$this->nophrase_cut($cachekey);
			$replaceable = array();
			foreach ($templatevars AS $name => $value)
			{
				if (is_int(mb_strpos($this->templateregistry[$cachekey], $this->start . $name . $this->end)) == true)
				{
					$replaceable[$this->start . $name . $this->end] = $value;
				}
			}
			$this->templateregistry[$cachekey] = str_replace(array_keys($replaceable), array_values($replaceable), $this->templateregistry[$cachekey]);
			$this->nophrase_paste($cachekey);
			$template = $this->templateregistry[$cachekey];
			unset($this->templateregistry[$cachekey]);
			$ilance->cache->store($cachekey, $template);
		}
		return $template;
	}

	/*
	* Function to set template variable identifiers such as "{" and "}"
	*
	* @param       string           starting tag
	* @param       string           ending tag
	*/
	function set_identifiers($start, $end)
	{
		$this->start = $start;
		$this->end = $end;
	}

	/*
	* Function to include another file. eg. A header/footer.
	*
	* @param       string           node
	* @param       string           filename
	*/
	function include_file($node, $filename)
	{
		if (file_exists(DIR_TEMPLATES . $filename))
		{
			$include = file_get_contents(DIR_TEMPLATES . $filename);
		}
		else
		{
			$include = 'Requested template: "' . $filename . '" does not exist.';
		}
		$tag = mb_substr($this->templateregistry["$node"], mb_strpos(mb_strtolower($this->templateregistry["$node"]), '<include filename="' . $filename . '">'), mb_strlen('<include filename="' . $filename . '">'));
		$this->templateregistry["$node"] = str_replace($tag, $include, $this->templateregistry["$node"]);
	}

	/*
	* Function for parsing a <loop name="xxx">yyy</loop name="xxx"> HTML template tag
	*
	* @param       string           node
	* @param       mixed            loop identifier variable (can be single variable or array variable)
	*/
	function parse_loop($node, $array_name, $nocache = true)
	{
		global $ilance;
		$ilance->timer->start();
		$varname = '';
		if (is_array($array_name))
		{
			$temparray = $array_name;
		}
		else
		{
			$temparray[] = $array_name;
		}
		unset($array_name);
		foreach ($temparray AS $array_name)
		{
			if (in_array($array_name, array('v3nav', 'subnav_settings')))
			{
				$nocache = true;
			}
			$loop_code = '';
			$varname .= empty($varname) ? $array_name : ', ' . $array_name;
			$cache_name = "parse_loop_" . $node . "_" . $array_name;
			$start_pos = strpos(strtolower($this->templateregistry["$node"]), '<loop name="' . $array_name . '">') + strlen('<loop name="' . $array_name . '">');
			$end_pos = strpos(strtolower($this->templateregistry["$node"]), '</loop name="' . $array_name . '">');
			$loop_code = substr($this->templateregistry["$node"], $start_pos, $end_pos - $start_pos);
			$start_tag = substr($this->templateregistry["$node"], strpos(strtolower($this->templateregistry["$node"]), '<loop name="' . $array_name . '">'), strlen('<loop name="' . $array_name . '">'));
			$end_tag = substr($this->templateregistry["$node"], strpos(strtolower($this->templateregistry["$node"]), '</loop name="' . $array_name . '">'), strlen('</loop name="' . $array_name . '">'));
			if (($new_code = $ilance->cache->fetch($cache_name)) === false OR $nocache)
			{
				$new_code = '';
				if (!empty($loop_code))
				{
					if (preg_match_all('/' . $this->start . '([\w\d_]+)' . $this->end . '/', $loop_code, $variablematches) == true)
					{
						global ${$array_name};
						$num = count(${$array_name});
						for ($i = 0; $i < $num; $i++)
						{
							if ((!empty(${$array_name}[$i]) AND is_array(${$array_name}[$i]) OR !empty(${$array_name}[$i]) AND is_object(${$array_name}[$i])))
							{
								$replaceable = array ();
								foreach ($variablematches[1] AS $key)
								{
									if (isset(${$array_name}[$i][$key]) AND !is_array(${$array_name}[$i][$key]))
									{
										$replaceable[$this->start . $key . $this->end] = ${$array_name}[$i][$key];
									}
								}
								$new_code .= str_replace(array_keys($replaceable), array_values($replaceable), $loop_code);
							}
						}
						$this->templateregistry["$node"] = str_replace($start_tag . $loop_code . $end_tag, $new_code, $this->templateregistry["$node"]);
						unset(${$array_name});
					}
				}
				$ilance->cache->store($cache_name, $new_code);
			}
			else
			{
				$this->templateregistry["$node"] = str_replace($start_tag . $loop_code . $end_tag, $new_code, $this->templateregistry["$node"]);
			}
		}
		$ilance->timer->stop();
		DEBUG("parse_loop(\$node = $node, \$array_name = $varname) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
	}

	/*
	* Function to display error message based on unaccepted functions used within a if condition in a template
	*
	* @param       string           function name being used
	*/
	function unsafe_precedence($fn = '')
	{
		echo '<strong>Fatal:</strong> callback if condition function <strong>' . $fn . '()</strong> is not in the safe functions list. Please remove this if condition expression from the template ASAP.';
		return 'false';
	}

	/*
	* Function to handle the regular expressions used within the if condition parser
	*
	* @param       string           template content
	*/
	function pr_callback($string = '')
	{
		global $ilance, $nothing_to_parse, $else_error, $show;
		$else_error = $nothing_to_parse = 0;

		// used to allow developers to add more functions to the list above
		($apihook = $ilance->api('template_pr_callback_start')) ? eval($apihook) : false;

		$string = substr($string, strpos($string, 'condition'));
		preg_match("/condition=([\"'])((?:(?!\\1).)*)\\1/is", $string, $condition);
		$quotepos = $quotepos2 = $pos = 0;
		while (true)
		{
			$endpos = strpos($string, '>', $pos);
			if ($quotepos !== false)
			{
				$quotepos = strpos($string, '"', $pos);
			}
			if ($quotepos2 !== false)
			{
				$quotepos2 = strpos($string, "'", $pos);
			}
			if (($quotepos < $endpos AND $quotepos !== false) OR ($quotepos2 < $endpos AND $quotepos2 !== false))
			{
				if (($quotepos < $quotepos2 OR $quotepos2 === false) AND $quotepos !== false)
				{
					// we have " - quotes here
					$quotepos = strpos($string, '"', $quotepos + 1);

					if ($quotepos !== false)
					$pos = $quotepos + 1;

					// back to top of the loop and search for endpos again
					continue;
				}
				if (($quotepos2 < $quotepos OR $quotepos === false) AND $quotepos2 !== false)
				{
					// we have ' - quotes here
					$quotepos2 = strpos($string, "'", $quotepos2 + 1);

					if ($quotepos2 !== false)
					$pos = $quotepos2 + 1;

					// back to top of the loop and search for endpos again
					continue;
				}
			}
			if (($quotepos === false OR $quotepos > $endpos) AND ($quotepos2 === false OR $quotepos2 > $endpos))
			{
				$pos = $endpos;
				break;
			}
			if ($endpos === false)
			{
				$pos = $endpos;
			}
		}
		// from end of the if tag ( '>' char +1)
		$string = substr($string, $pos + 1);
		// from end of the if tag ( '>' char +1)
		// now we have inner content only
		$string = substr($string, 0, strrpos($string, '<'));
		$iels = strpos($string, '<else />');
		$no_start = $yes_end = false;
		$pos = -1;
		$level = 0;
		while ($iels !== false)
		{
			$is = strpos($string, '<if ', $pos + 1);
			$ie = strpos($string, '</if>', $pos + 1);
			$iels = strpos($string, '<else />', $pos + 1);
			// we have found our else
			if (($is > $iels OR $is === false) AND ($ie > $iels OR $ie === false) AND $level == 0)
			{
				if ($iels !== false)
				{
					$yes_end = strpos($string, '<else />', max($pos,0));
					$no_start = $yes_end + strlen('<else />');
				}
				break;
			}
			if (($is < $ie AND $is !== false) OR ($is !== false AND $ie === false))
			{
				$level++;
				$pos = $is;
			}
			if (($is > $ie AND $ie !== false) OR ($is === false AND $ie !== false))
			{
				$level--;
				$pos = $ie;
			}
		}
		if ($yes_end === false)
		{
			$no_start = false;

			// find end of this </if tag
			$yes_end = strlen($string);
		}
		$yes_code = substr($string, 0, $yes_end);
		// find no-code
		$no_code = '';
		if ($no_start !== false)
		{
			$no_end = strlen($string);
			$no_code = substr($string, $no_start, ($no_end - $no_start));
		}
		// if condition has else code
		$possible_elsif = isset($yes_no_code[1]) ? $yes_no_code[1] : '';
		$condition = isset($condition[2]) ? $condition[2] : '';
		$condition = preg_replace("/(([a-z_][a-z_0-9]*)\\(.*?\\))/ie","in_array(strtolower('\\2'), \$this->safe_functions) ? '\\1' : \$this->unsafe_precedence('\\2')", $condition);
		$condition = preg_replace("/\\$([a-z][a-z_0-9]*)/is", "\$GLOBALS['\\1']", $condition);
		
		($apihook = $ilance->api('template_pr_callback_end')) ? eval($apihook) : false;
		
		if (eval("return ($condition);"))
		{
			return $yes_code;
		}
		else
		{
			return $no_code;
		}
	}

	/*
	* Functions for returning <if condition=""> errors
	*
	* @param       void
	*/
	function report_if_error($html = '', $if_pos = 0, $ending = false)
	{
		$start = $if_pos;
		$end = strpos($html, '>', $if_pos);
		if ($ending)
		{
		}
		else
		{
			// get if condition
			$start = $if_pos;
			$end = strpos($html, '>', $if_pos);

			if ($end === false)
			{
				$start = strpos($html, '"', $if_pos);
				$end = strpos($html, '"', $start + 1);
				$start2 = strpos($html, "'", $if_pos);
				$end2 = strpos($html, "'", $start + 1);

				// choose quote type that if condition is enclosed in
				if (($start2 < $start AND $start2 !== false) OR $start === false)
				{
					$start = $start2;
					$end = $end2;
				}
			}
		}
		$if_cond = '';
		if ($start !== false AND $end !== false)
		{
			$if_cond = substr($html, $start, $end - $start + 1);
		}
		else
		{
			$if_cond = 'unknown';
		}
		$style = "<style>.code {margin: 0px 0px 0px 0px;width: 100%;font-family: monospace;font-size: 13px;color:#000000;background-color:#fff; cursor: crosshair;}</style>";
		if ($ending)
		{
			// if tag without ending
			echo $style . '<strong>Fatal:</strong> no ending &lt;/if&gt; found for: ' . ilance_htmlentities(stripslashes($if_cond)) . '<br><br>HTML code: <pre class="code">'.ilance_htmlentities(substr(stripslashes($html), $if_pos, 400)).'</pre>';
		}
		else
		{       // if tag without ending
			echo $style . '<strong>Fatal:</strong> no starting &lt;if condition&gt; tag for ending ' . ilance_htmlentities(stripslashes($if_cond)) . ' tag!<br><br>HTML code: <pre class="code">'.ilance_htmlentities(substr(stripslashes($html), $if_pos, 400)).'</pre>';
		}
	}

	/*
	* Functions for parsing <if condition="">xxx<else />yyy</if> template conditionals
	*
	* @param       string       node
	* @param       string       template data (optional)
	* @param       boolean      apply slashes to template string/data (default false)
	*/
	function parse_if_blocks($node = '', $content = '', $addslashes = false)
	{
		global $ilance, $nothing_to_parse, $else_error, $show;
		$ilance->timer->start();
		$template_str = (empty($content)) ? $this->templateregistry["$node"] : $content;
		// simple support for </if name= & </if condition= closing tags
		$pos = $opening_tags = $level = 0;
		$start = $start2 = -1;
		while (true)
		{
			$pos = strpos($template_str, '</if ');
			$end = strpos($template_str, '>', $pos);
			if ($end === false)
			{
				echo '<strong>Warning:</strong> &lt;/if&gt; tag not closed within template!';
				break;
			}
			if ($pos === false)
			{
				break;
			}
			$template_str = substr($template_str, 0, $pos) . '</if>' . substr($template_str, $end + 1);
		}
		while (true)
		{
			$start2 = strpos($template_str, '<if ', $start + 1);
			if ($start2 !== false)
			{
				$end = strpos($template_str, '</if>', $start + 1);
			}
			else
			{
				break;
			}
			$start = $start2;
			if ($end === false)
			{
				echo $this->report_if_error($template_str, $start, true);
			}
			if ($start > $end)
			{
				echo $this->report_if_error($template_str, $end);
			}
			// start processing if conditional block!
			$end = $start - 1;
			while (true)
			{
				$is = strpos($template_str, '<if ', $end + 1);
				$ie = strpos($template_str, '</if>', $end + 1);
				if (($is < $ie AND $is !== false) OR ($is !== false AND $ie === false))
				{
					$level++;
					$end = $is;
				}
				if (($is > $ie AND $ie !== false) OR ($is === false AND $ie !== false))
				{
					$level--;
					$end = $ie;
				}
				if ($ie === false AND $is === false AND $level != 0)
				{
					$end = false;
					break;
				}
				if ($level == 0 AND ($ie < $is OR $is === false))
				{
					$end = $ie;
					break;
				}
			}
			if ($end === false)
			{
				$this->report_if_error($template_str, $start, true);
			}
			if ($start < $end)
			{
				$a = substr($template_str, 0, $start);
				$b = substr($template_str, $end + 5);
				$c = $this->pr_callback(stripslashes(substr($template_str, $start, $end - $start + 5)));
				$template_str = ($addslashes) ? $a . addslashes($c) . $b : $a . $c . $b;
				$start = -1;
			}
		}
		if (empty($content))
		{
			$this->templateregistry["$node"] = $template_str;
		}
		else
		{
			return $template_str;
		}
		$ilance->timer->stop();
		DEBUG("parse_if_blocks(\$node = $node, \$content = , \$addslashes = $addslashes) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
	}

	/*
	* Function is used only by the register_template_variables() method, for going through arrays and extracting the values.
	*
	* @param       string           node
	* @param       array            array of variable names
	*/
	function traverse_array($node = '', $array)
	{
		while (list(, $value) = each($array))
		{
			if (is_array($value))
			{
				$this->traverse_array($node, $value);
			}
			else
			{
				$this->var_names["$node"][] = $value;
			}
		}
	}

	/*
	* Function to register template variables and assigns them to $this->var_names
	*
	* @param       string           node
	* @param       array            variable names
	*/
	function register_template_variables($node = '', $vars)
	{
		if (!empty($vars) AND is_array($vars))
		{
			$this->traverse_array($node, $vars);
		}
		else if (!empty($vars))
		{
			if (is_long(mb_strpos($vars, ',')) == true)
			{
				$vars = explode(',', $vars);
				for (reset($vars); $current = current($vars); next($vars))
				{
					$this->var_names["$node"][] = $current;
				}
			}
			else
			{
				$this->var_names["$node"][] = $vars;
			}
		}
	}

	/*
	* Function to remove duplicate values in an array
	*
	* @param       array           array of values
	*/
	function remove_duplicate_template_variables($array)
	{
		$newarray = array();
		if (is_array($array))
		{
			foreach($array as $key => $val)
			{
				if (is_array($val))
				{
					$val2 = $this->remove_duplicate_template_variables($val);
				}
				else
				{
					$val2 = $val;
					$newarray = array_unique($array);
					break;
				}
				if (!empty($val2))
				{
					$newarray["$key"] = $val2;
				}
			}
		}
		return $newarray;
	}

	function nophrase_cut($node = '')
	{
		// let's search template for <noparse></noparse> tags
		// so this function can rip those blocks out if required (before we do all phrases in template in next step)
		preg_match_all("'\<$this->noparse\>(.*)\</$this->noparse\>'isU", $this->templateregistry["$node"], $this->findregxp);
		if (!empty($this->findregxp[0]) AND $this->findregxp[0] > 0)
		{
			for ($i = 0; $i < count($this->findregxp[0]); $i++)
			{
				$this->templateregistry["$node"] = str_replace($this->findregxp[0]["$i"], "~~$this->noparse~~$i~~$this->noparse~~", $this->templateregistry["$node"]);
			}
		}
	}
	
	function nophrase_paste($node = '')
	{
		// let's piece back together the template tags used to filter out parsing of phrases
		if (!empty($this->findregxp[0]) AND $this->findregxp[0] > 0)
		{
			for ($i = 0; $i < count($this->findregxp[0]); $i++)
			{
				$this->findregxp[0]["$i"] = str_replace("<$this->noparse>", '', $this->findregxp[0]["$i"]);
				$this->findregxp[0]["$i"] = str_replace("</$this->noparse>", '', $this->findregxp[0]["$i"]);
			}
			for ($i = 0; $i < count($this->findregxp[0]); $i++)
			{
				$this->templateregistry["$node"] = str_replace("~~$this->noparse~~$i~~$this->noparse~~", $this->findregxp[0]["$i"], $this->templateregistry["$node"]);
			}
		}
		$this->findregxp = array();
	}
	
	/*
	* Function to parse template variables within a template
	*
	* @param       node           template node
	*/
	function parse_template_variables($node = '')
	{
		global $ilance, $phrase, $area_title, $page_title, $templatevars, $templatebits, $breadcrumbtrail, $breadcrumbfinal, $navcrumb, $iltemplate, $headinclude, $footinclude, $breadcrumb, $onload, $official_time, $v3left_nav, $v3left_storenav, $ilconfig, $ilpage, $newpmbpopupjs, $show, $cid, $metadescription, $metakeywords, $keywords, $buildversion, $topnav, $topnavlink;
		$ilance->timer->start();
		
		($apihook = $ilance->api('parse_template_variables_start')) ? eval($apihook) : false;
		
		$this->nophrase_cut($node);
		$newpmb_count = 0;
		$new_messages_nav = $newpmbpopupmodal = '';
		if (defined('LOCATION') AND LOCATION != 'admin')
		{
			// version 4 template UI {search_category_pulldown}
			if ($ilconfig['globalauctionsettings_productauctionsenabled'])
			{
				if (defined('LOCATION') AND (LOCATION != 'cron' AND LOCATION != 'attachment' AND LOCATION != 'pmb' AND LOCATION != 'stylesheet' AND LOCATION != 'upload' AND LOCATION != 'ipn'))
				{
					$cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
					if (($search_category_pulldown_v4 = $ilance->cache->fetch("searchcategorypulldown_" . $cid)) === false)
					{
						$search_category_pulldown_v4 = $ilance->categories_pulldown->print_root_category_pulldown($cid, 'product', 'cid', $_SESSION['ilancedata']['user']['slng'], array(), true, false, false, 'cidfield');
						$ilance->cache->store("searchcategorypulldown_" . $cid, $search_category_pulldown_v4);
					}
					if (($categorynavdropdownall = $ilance->cache->fetch("categorynavdropdownall_product")) === false)
					{
						$categorynavdropdownall = $ilance->categories_parser_v4->print_subcategory_columns(3, 12, 'product', 0, $_SESSION['ilancedata']['user']['slng'], 0, '', 0, 0, 0, '', false, false);
						$ilance->cache->store("categorynavdropdownall_product", $categorynavdropdownall);
					}
					if (($categorypulldownpopup = $ilance->cache->fetch("categorypulldownpopup_1col_" . $cid)) === false)
					{
						$categorypulldownpopup = $ilance->categories_parser_v4->print_category_columns(1, 'product', $_SESSION['ilancedata']['user']['slng'], $cid);
						$ilance->cache->store("categorypulldownpopup_1col_" . $cid, $categorypulldownpopup);
					}
					$hideafter = 9; //((LOCATION == 'main') ? 9 : 20);
					if (($categorynavigation = $ilance->cache->fetch("categorynavigation_1col_hideafter_" . $hideafter)) === false)
					{
						$categorynavigation = $ilance->categories_parser_v4->print_category_navigation('product', $_SESSION['ilancedata']['user']['slng'], 0, $hideafter, 3, 12);
						$ilance->cache->store("categorynavigation_1col_hideafter_" . $hideafter, $categorynavigation);
					}
				}
			}
                        if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
			{
				if (defined('LOCATION') AND (LOCATION != 'cron' AND LOCATION != 'attachment' AND LOCATION != 'pmb' AND LOCATION != 'stylesheet' AND LOCATION != 'upload' AND LOCATION != 'ipn'))
				{
					$cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
					if (($search_category_pulldown_v4 = $ilance->cache->fetch("searchcategorypulldown_" . $cid)) === false)
					{
						$search_category_pulldown_v4 = $ilance->categories_pulldown->print_root_category_pulldown($cid, 'service', 'cid', $_SESSION['ilancedata']['user']['slng'], array(), true, false, false, 'cidfield');
						$ilance->cache->store("searchcategorypulldown_" . $cid, $search_category_pulldown_v4);
					}
				}
			}
			// #### NEW PMB JAVASCRIPT POPUP ###############################
			if (defined('LOCATION') AND LOCATION != 'messages' AND LOCATION != 'login' AND LOCATION != 'registration' AND !empty($_SESSION['ilancedata']['user']['userid']))
			{
				$newpmbsql = $ilance->db->query("
					SELECT pm.id, pm.from_id, pmb.subject
					FROM " . DB_PREFIX . "pmb_alerts AS pm,
					" . DB_PREFIX . "pmb AS pmb
					WHERE pm.to_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						AND pmb.id = pm.id
						AND pm.to_status = 'new'
						AND pm.track_popup = '0'
					ORDER BY pm.id DESC
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($newpmbsql) > 0)
				{
					$newpmb = $ilance->db->fetch_array($newpmbsql, DB_ASSOC);
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "pmb_alerts
						SET track_popup = '1'
						WHERE id = '" . $newpmb['id'] . "'
					", 0, null, __FILE__, __LINE__);
					$newpmb['username'] = un_htmlspecialchars(fetch_user('username', $newpmb['from_id']), true);
					$newpmb['subject'] = un_htmlspecialchars($newpmb['subject'], true);
					$newpmbpopupjs = "<script type=\"text/javascript\">
<!--
if (confirm_js(\"{_you_have_a_new_private_message_from} " . $newpmb['username'] . ", {_title}: '" . $newpmb['subject'] . "' - {_click_ok_to_view_it_or_click_cancel_to_hide_this_alert_notification}\"))
{
        if (confirm_js(\"{_open_the_private_message_in_a_new_browser_window}\"))
        {
                var winobj = window.open(\"" . HTTPS_SERVER . $ilpage['messages'] . "\", \"pmbnew\", \"statusbar=yes,menubar=yes,scrollbars=yes,toolbar=yes,location=yes,directories=yes,resizable=yes,top=50,left=50\");
                if (winobj == null)
                {
                        alert_js(\"{_unable_to_open_a_new_browser_window_this_might_be_due_to_a_popup_blocker}\");
                        window.location = \"" . HTTPS_SERVER . $ilpage['messages'] . "\";
                }
        }
        else
        {
                window.location = \"" . HTTPS_SERVER . $ilpage['messages'] . "\";
        }   
}
//-->
</script>";    
				}
				// #### NEW PMB MESSAGE COUNT ##########################
				$newpmbsql_count = $ilance->db->query("
					SELECT pm.id
					FROM " . DB_PREFIX . "pmb_alerts AS pm,
					" . DB_PREFIX . "pmb AS pmb
					WHERE pm.to_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						AND pmb.id = pm.id
						AND pm.to_status = 'new'
						AND pm.track_status = 'unread'
					ORDER BY pm.id DESC
				", 0, null, __FILE__, __LINE__);
				$newpmb_count = $ilance->db->num_rows($newpmbsql_count);
				$new_messages_nav = (isset($newpmb_count) AND $newpmb_count > 0) ? '<span class="smaller badge green">' . number_format($newpmb_count) . '</span>' : '';
				$newpmbpopupmodal = $this->fetch_pmb_modal();
			}
		}
		$this->templatebits = array(
			'headinclude' => (isset($headinclude) ? $headinclude : ''),
			'footinclude' => (isset($footinclude) ? $footinclude : ''),
			'onload' => ((isset($onload) AND !empty($onload)) ? ' onLoad="' . $onload . '"' : ''),
		);
		if ($this->isadmincp AND defined('LOCATION') AND LOCATION == 'admin')
		{
			if (function_exists('usersitelimits') == false)
			{
				include_once(DIR_CORE . 'functions_admincp.php');
			}
			$limits = usersitelimits();
			global $login_include_admin, $loadaverage, $ilanceversion;
			$this->templatebits['login_include_admin'] = $login_include_admin;
			$this->templatebits['loadaverage'] = $loadaverage;
			$this->templatebits['totalusers'] = number_format($ilance->usercount());
			$this->templatebits['userlimit'] = $limits['userlimit'];
			$this->templatebits['sitelimit'] = $limits['sitelimit'];
			$this->templatebits['members_online'] = members_online();
			$this->templatebits['ilanceversion'] = $ilanceversion;
			unset($limits);
			
			($apihook = $ilance->api('parse_template_variables_templatebits_admin')) ? eval($apihook) : false;
		}
		else
		{
			global $login_include;
			$this->templatebits['v3left_nav'] = isset($leftnav) ? $leftnav : '';
			$this->templatebits['topnav_menu_links'] = isset($topnav) ? $topnav : '';
			$this->templatebits['search_category_pulldown_v4'] = isset($search_category_pulldown_v4) ? $search_category_pulldown_v4 : '';
                        $this->templatebits['categorynavdropdownall'] = isset($categorynavdropdownall) ? $categorynavdropdownall : '';
			$this->templatebits['categorypulldownpopup'] = isset($categorypulldownpopup) ? $categorypulldownpopup : '';
			$this->templatebits['categorynavigation'] = isset($categorynavigation) ? $categorynavigation : '';
			//$this->templatebits['v3left_storenav'] = (isset($v3left_storenav) ? $v3left_storenav : ''); //it should go to stores addon to be loaded by apihook
			$this->templatebits['new_pmb_popup_js'] = $newpmbpopupjs;
			$this->templatebits['new_pmb_popup_modal'] = $newpmbpopupmodal;
			$this->templatebits['new_messages_nav'] = $new_messages_nav;
			$this->templatebits['template_languagepulldown'] = $ilance->language->print_language_pulldown($_SESSION['ilancedata']['user']['languageid'], 1);
			$this->templatebits['template_stylepulldown'] = $ilance->styles->print_styles_pulldown($_SESSION['ilancedata']['user']['styleid'], 1);
			$this->templatebits['login_include'] = $login_include;
			$this->templatebits['q'] = (isset($ilance->GPC['q']) ? handle_input_keywords($ilance->GPC['q']) : '');
			$this->templatebits['cid'] = $cid;
			
			($apihook = $ilance->api('parse_template_variables_templatebits_user')) ? eval($apihook) : false;
		}
		$this->templatebits['template_requesturi'] = (isset($_SERVER['PHP_SELF']) ? strip_tags(ilance_htmlentities($_SERVER['PHP_SELF'])) : '');
		$this->templatebits['template_metatitle'] = (!empty($metatitle) ? $metatitle : '{_template_metatitle}');
		$this->templatebits['template_metadescription'] = (!empty($metadescription) ? $metadescription : '');
		$this->templatebits['template_metakeywords'] = (!empty($metakeywords) ? $metakeywords : '');
		$this->templatebits['official_time'] = $ilconfig['official_time'];
		$this->templatebits['template_charset'] = $ilconfig['template_charset'];
		$this->templatebits['template_languagecode'] = $ilconfig['template_languagecode'];
		$this->templatebits['area_title'] = (isset($area_title) ? $area_title : '');
		$this->templatebits['page_title'] = (isset($page_title) ? $page_title : '');
		$this->templatebits['company_name'] = COMPANY_NAME;
		$this->templatebits['site_name'] = SITE_NAME;
		$this->templatebits['site_email'] = SITE_EMAIL;
		$this->templatebits['site_phone'] = SITE_PHONE;
		$this->templatebits['site_address'] = SITE_ADDRESS;
		$this->templatebits['https_server'] = HTTPS_SERVER;
		$this->templatebits['http_server'] = HTTP_SERVER;
		$this->templatebits['https_server_other'] = ((defined('HTTPS_SERVER_OTHER')) ? HTTPS_SERVER_OTHER : HTTPS_SERVER);
		$this->templatebits['http_server_other'] = ((defined('HTTP_SERVER_OTHER')) ? HTTP_SERVER_OTHER : HTTP_SERVER);
		$this->templatebits['https_server_admin'] = HTTPS_SERVER_ADMIN;
		$this->templatebits['http_server_admin'] = HTTP_SERVER_ADMIN;
		$this->templatebits['http_server_cdn'] = HTTP_CDN_SERVER;
		$this->templatebits['https_server_cdn'] = HTTPS_CDN_SERVER;
		$this->templatebits['dir_server_root'] = DIR_SERVER_ROOT;
		$this->templatebits['rand()'] = rand(1, 999999);
		$this->templatebits['keywords'] = (!empty($keywords) ? $keywords : '');
		$this->templatebits['s'] = (!empty($_COOKIE['s']) ? $_COOKIE['s'] : session_id());
		$this->templatebits['token'] = TOKEN;
		$this->templatebits['pageurl'] = PAGEURL;
		$this->templatebits['pageurl_urlencoded'] = urlencode(PAGEURL);
		$this->templatebits['ajaxurl'] = AJAXURL;
		$this->templatebits['last10'] = (LICENSEKEY != '') ? mb_substr(LICENSEKEY, 0, 10) : '';
		$this->templatebits['buildversion'] = $buildversion;
		$this->templatebits['year'] = date('Y');
		$this->templatebits['csrf'] = (!empty($_SESSION['ilancedata']['user']['csrf']) ? $_SESSION['ilancedata']['user']['csrf'] : $token);
		$this->templatebits['site_id'] = SITE_ID;
		$this->templatebits['template_ilversion'] = $ilconfig['current_version'];
		$this->templatebits['template_relativeimagepath'] = $ilconfig['template_relativeimagepath'];
		$this->templatebits['template_relativeimagepath_cdn'] = $ilconfig['template_relativeimagepath_cdn'];
		$this->templatebits['currencysymbol'] = $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_left'];
		$this->templatebits['ilanceaid'] = $ilconfig['globalserversettings_ilanceaid'];
                $this->templatebits['facebookurl'] = $ilconfig['globalserversettings_facebookurl'];
                $this->templatebits['twitterurl'] = $ilconfig['globalserversettings_twitterurl'];
                $this->templatebits['googleplusurl'] = $ilconfig['globalserversettings_googleplusurl'];
                $this->templatebits['section'] = ((isset($topnavlink) AND is_array($topnavlink)) ? $topnavlink[0] : '');
		
		($apihook = $ilance->api('parse_template_variables_templatebits')) ? eval($apihook) : false;

		// merge our new template bits into existing template variable array
		$iltemplate = array_merge($this->templatebits, $templatevars);
		foreach ($iltemplate AS $name => $value)
		{
			// find all occurrences of {template_variables}
			if (is_int(mb_strpos($this->templateregistry["$node"], $this->start . $name . $this->end)) == true)
			{
				$this->templateregistry["$node"] = str_replace($this->start . $name . $this->end, $value, $this->templateregistry["$node"]);
			}
		}
		unset($iltemplate, $templatevars, $this->templatebits);
		$this->nophrase_paste($node);

		($apihook = $ilance->api('parse_template_variables_end')) ? eval($apihook) : false;
		
		$ilance->timer->stop();
		DEBUG("parse_template_variables(\$node = $node) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
	}

	function parse_template_phrases($node = '')
	{
		global $ilance, $phrase, $ilconfig;
		$ilance->timer->start();
		$phrasepattern = '/' . $this->phrasestart . '([\w\d_]+)' . $this->phraseend . '/';
		if (preg_match_all($phrasepattern, $this->templateregistry["$node"], $phrasematches) == true)
		{
			$varnames = array_values(array_unique($phrasematches[1]));
			$replaceable = array();
			$slng = (isset($_SESSION['ilancedata']['user']['slng']) AND !empty($_SESSION['ilancedata']['user']['slng'])) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';
			$querystr = '';
			foreach ($varnames AS $key => $value)
			{
				$querystr .= empty($querystr) ? "'_" . $value . "'" : ", '_" . $value . "'";
			}
			if (!empty($querystr))
			{
				$querystr = 'varname IN (' . $querystr . ')';
				$query = $ilance->db->query("
					SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.varname, p.text_$slng AS text
					FROM " . DB_PREFIX . "language_phrases p
					WHERE $querystr
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($query) > 0)
				{
					while ($cache = $ilance->db->fetch_array($query, DB_ASSOC))
					{
						$cache['text'] = str_replace(array("\r\n", "\n", "\r"), '', $cache['text']);
						$phrase[$cache['varname']] = stripslashes(un_htmlspecialchars($cache['text']));
					}
					unset($cache);
				}
				unset($query, $querystr, $phrasesearch, $phrasereplace, $slng);
			}
			foreach ($varnames AS $key => $value)
			{
				$replaceable[$this->phrasestart . $value . $this->phraseend] = isset($phrase["_$value"]) ? $phrase["_$value"] : $this->phrasestart . $value . $this->phraseend;
			}
			$this->templateregistry["$node"] = str_replace(array_keys($replaceable), array_values($replaceable), $this->templateregistry["$node"]);
			unset($replaceable, $phrasematches, $key, $value, $varnames);
		}
		unset($phrase);
		$ilance->timer->stop();
		DEBUG("parse_template_phrases(\$node = $node) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
		return $this->templateregistry["$node"];
	}

	/*
	* Function for reading and parsing the template's special tags/variables.
	* Now checks for <include filename=""> tags and executes include_file()
	*
	* @param       string
	*/
	function parse_template($node = '')
	{
		global $ilance;
		$ilance->timer->start();
		$nodes = explode(',', $node);
		for (reset($nodes); $node = trim(current($nodes)); next($nodes))
		{
			// do we have any included templates to call?
			while (is_long($pos = mb_strpos(mb_strtolower($this->templateregistry["$node"]), '<include filename="')))
			{
				$pos += 19;
				$endpos = mb_strpos($this->templateregistry["$node"], '">', $pos);
				$filename = mb_substr($this->templateregistry["$node"], $pos, $endpos - $pos);
				$this->include_file($node, $filename);
			}
			$this->parse_session_globals($node);
			$this->parse_template_variables($node);
			$this->parse_template_varnames($node);
			$this->parse_template_phrases($node);
		}
		$ilance->timer->stop();
		DEBUG("parse_template(\$node = $node) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
	}
	
	function parse_template_varnames($node = '')
	{
		global $ilance;
		$ilance->timer->start();
		$nodecount = $y = 0;
		$n = '';
		if (isset($this->var_names["$node"]) OR !empty($this->var_names["$node"]))
		{
			$nodecount = count($this->var_names["$node"]);
			for ($i = 0; $i < $nodecount; $i++)
			{
				$temp_var = $this->var_names["$node"]["$i"];
				preg_match_all("/" . $this->start . $temp_var . $this->end . "/", $this->templateregistry[$node], $m);
				if (is_int(mb_strpos($this->templateregistry["$node"], $this->start . $temp_var . $this->end)) == true)
				{
					global ${$temp_var};
					if (!is_array(${$temp_var}))
					{
						$this->templateregistry["$node"] = str_replace($this->start . $temp_var . $this->end, ${$temp_var}, $this->templateregistry["$node"]);
						$y++;
					}
				}
				else
				{
				    $n .= $temp_var . ' ';
				}
				//unset(${$temp_var}, $temp_var, $this->var_names["$node"][$i]);
			}
		}
		$ilance->timer->stop();
		DEBUG("parse_template_varnames(\$node = $node) nodecount $nodecount parsed $y vars $n in " . $ilance->timer->get() . " seconds", 'FUNCTION');
	}


	/*
	* Function to parse template collapsables
	*
	* @param       node            template node
	*/
	function parse_template_collapsables($node = '')
	{
		global $ilcollapse, $ilconfig;
		/*
		* Usage:
		* <a href="javascript:void(0)" onclick="return toggle('expert_{user_id}');"><img id="collapseimg_expert_{user_id}" src="{template_relativeimagepath}{template_imagesfolder}expand{collapse[collapseimg_expert_{user_id}]}.gif" border="0" alt=""></a>
		* <tbody id="collapseobj_expert_{user_id}" style="{collapse[collapseobj_expert_{user_id}]}">
		*/
		//print_r($ilcollapse);
		if (!empty($ilcollapse))
		{
			foreach ($ilcollapse AS $key => $value)
			{
				$replaceable = array();
				$replaceable[$this->start . 'collapse[' . $key . ']' . $this->end] = $value;
				$this->templateregistry["$node"] = str_replace(array_keys($replaceable), array_values($replaceable), $this->templateregistry["$node"]);
			}
		}
		// find all occurrences of {collapse[XXXXX]}
		$cname = 'collapse';
		$pattern = '/' . $this->start . $cname . '\[([\w\d_]+)\]' . $this->end . '/';
		if (preg_match_all($pattern, $this->templateregistry[$node], $m) !== false)
		{
			$replaceable = array();
			foreach ($m[1] AS $key)
			{
				$replaceable[$this->start . $cname . '[' . $key . ']' . $this->end] = '';
			}
			$this->templateregistry["$node"] = str_replace(array_keys($replaceable), array_values($replaceable), $this->templateregistry["$node"]);
		}
	}

	/*
	* Function lookup in source for js phrases and construct phrase array.
	*
	* @param       string
	*/
	function init_js_phrase_array($node = '')
	{
		global $ilance, $jsinclude, $ilconfig, $show;
		$ilance->timer->start();
		$slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';
		$include_js_phrases = $where = '';
		$charsearch = array("'", '"');
		$charreplace = array('\x27', '\x22');
		$nojsphrases = array('jquery', 'jquery_carousel', 'jquery_easing', 'jquery_slides', 'jquery_blockui', 'yahoo-jar');
		
		($apihook = $ilance->api('init_js_phrase_array_start')) ? eval($apihook) : false;
		
		$source[] = $this->templateregistry["$node"];
		$source[] = file_get_contents(DIR_SERVER_ROOT . DIR_FUNCT_NAME . '/' . DIR_JS_NAME . '/fileuploader/jquery.fileupload-ui.js');
		if (is_array($jsinclude))
		{
			foreach ($jsinclude['header'] AS $key => $value)
			{
				if (!in_array($value, $nojsphrases))
				{
					$path = ($value == 'functions') ? DIR_SERVER_ROOT . DIR_FUNCT_NAME . '/' . DIR_JS_NAME . '/' . $value . '.js' : DIR_SERVER_ROOT . DIR_FUNCT_NAME . '/' . DIR_JS_NAME . '/functions_' . $value . '.js';
					if (file_exists($path))
					{
						$source[] = file_get_contents($path);
					}
				}
			}
			foreach ($jsinclude['footer'] AS $key => $value)
			{
				if (!in_array($value, $nojsphrases))
				{
					$path = ($value == 'functions') ? DIR_SERVER_ROOT . DIR_FUNCT_NAME . '/' . DIR_JS_NAME . '/' . $value . '.js' : DIR_SERVER_ROOT . DIR_FUNCT_NAME . '/' . DIR_JS_NAME . '/functions_' . $value . '.js';
					if (file_exists($path))
					{
						$source[] = file_get_contents($path);
					}
				}
			}
		}
		foreach ($source AS $key => $value)
		{
			if (preg_match_all("/phrase\['(.*)\']/U", $value, $phrasematches) == true)
			{
				$varnames = array_values(array_unique($phrasematches[1]));	
				foreach ($varnames AS $key2 => $value2)
				{
					$where .= (empty($where)) ? "'$value2'" : ", '$value2'";
				}
			}
		}
		unset($phrasematches, $source, $value);
		$hash = md5($where . $slng);
		$js_phrases_filepath = DIR_TMP_JS . 'phrases_' . $hash . '.js';
		$this->js_phrases_file = 'phrases_' . $hash . '.js';
		$js_phrases_url = $ilconfig['template_relativeimagepath'] . DIR_TMP_NAME . '/' . DIR_JS_NAME . '/phrases_' . $hash . '.js';
		$js_phrases_content = "<script type=\"text/javascript\" src=\"" . $js_phrases_url . "\" charset=\"" . mb_strtolower($ilance->language->cache[$_SESSION['ilancedata']['user']['languageid']]['charset']) . "\"></script>";
		$filetime = (file_exists($js_phrases_filepath)) ? filemtime($js_phrases_filepath) : 0;
		if (!empty($where) AND $filetime < (TIMESTAMPNOW - 300))
		{
			$where = 'varname IN (' . $where . ')';
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "varname, text_$slng AS text 
				FROM " .DB_PREFIX . "language_phrases 
				WHERE $where
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					$jsphrase = str_replace($charsearch, $charreplace, $res['text']);
					$jsphrase = html_entity_decode($jsphrase);
					$jsphrase = str_replace(array("\r\n", "\n", "\r"), '', $jsphrase);
					$include_js_phrases .= "'" . trim($res['varname']) . "':'$jsphrase', ";
				}
				$include_js_phrases = substr($include_js_phrases, 0, -2);
				$include_js_phrases = 'var phrase = {' . $include_js_phrases . '};';
			}
			if (@file_put_contents($js_phrases_filepath, $include_js_phrases) === false)
			{
				$js_phrases_content = "<script type=\"text/javascript\" charset=\"" . mb_strtolower($ilance->language->cache[$_SESSION['ilancedata']['user']['languageid']]['charset']) . "\">\n<!--\n" . $include_js_phrases . "\n//-->\n</script>\n";
			}
		}
		$this->templateregistry["$node"] = str_replace('{js_phrases_content}', $js_phrases_content, $this->templateregistry["$node"]);
		
		($apihook = $ilance->api('init_js_phrase_array_end')) ? eval($apihook) : false;
		
		$ilance->timer->stop();
		DEBUG("init_js_phrase_array(\$node = $node) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
	}

	/*
	* Function for printing the compiled templates to the web browser.
	*
	* @param       string
	*/
	function print_parsed_template($node = '', $echo = true)
	{
		global $ilconfig, $ilance, $ilcollapse, $navcrumb, $phrase;
		// handle template collapsable javascript buttons
		$html = '';
		$this->parse_template_collapsables($node);
		$this->init_js_phrase_array($node);
		// #### white space HTML stripper ##############################
		if ($ilconfig['globalfilters_whitespacestripper'])
		{
			$pattern = '/(?:(?<=\>)|(?<=\/\>))(\s+)(?=\<\/?)/';
			$this->templateregistry["$node"] = preg_replace("$pattern", "", $this->templateregistry["$node"]);
		}
		if ($echo)
		{
			echo $this->templateregistry["$node"];
		}
		else
		{
			$html .= $this->templateregistry["$node"];
		}
		if (defined('DEBUG_FOOTER') AND DEBUG_FOOTER AND isset($node) AND ($node == 'footer' OR $node == 'popupfooter' OR $node == 'main'))
		{
			$ta = $ta2 = '';
			foreach ($GLOBALS['DEBUG']['FUNCTION'] AS $key => $value)
			{
				$ta .= "$key : $value\n";
			}
			foreach ($GLOBALS['DEBUG']['CLASS'] AS $key => $value)
			{
				$ta2 .= "$key : $value\n";
			}
			if ($echo)
			{
				echo "<div><strong>Query Count: </strong>" . $ilance->db->query_count . "</div>";
				echo "<div align=\"center\" style=\"padding-top:5px; padding-bottom:20px\"><textarea style=\"width:98%; height:290px; border:1px inset; background-color:#000; color:#fff\">FUNCTIONS:\n\n$ta\nCLASSES:\n\n$ta2</textarea></div>";
			}
			else
			{
				$html .= "<div><strong>Query Count: </strong>" . $ilance->db->query_count . "</div>";
				$html .= "<div align=\"center\" style=\"padding-top:5px; padding-bottom:20px\"><textarea style=\"width:98%; height:290px; border:1px inset; background-color:#000; color:#fff\">FUNCTIONS:\n\n$ta\nCLASSES:\n\n$ta2</textarea></div>";
			}
		}
		if (defined('DB_EXPLAIN') AND DB_EXPLAIN)
		{
			if ($echo)
			{
				echo "<table bgcolor=\"#cccccc\" width=\"95%\" cellpadding=\"9\" cellspacing=\"1\" align=\"center\"><tr><td colspan=\"8\" bgcolor=\"red\"><strong>Total Query Time: </strong>" . $ilance->db->ttquery . "</td></tr><tr><td colspan=\"8\" bgcolor=\"red\"><strong>Query Count: </strong>" . $ilance->db->query_count . "</td></tr></table>";
				echo $ilance->db->explain;
			}
			else
			{
				$html .= "<table bgcolor=\"#cccccc\" width=\"95%\" cellpadding=\"9\" cellspacing=\"1\" align=\"center\"><tr><td colspan=\"8\" bgcolor=\"red\"><strong>Total Query Time: </strong>" . $ilance->db->ttquery . "</td></tr><tr><td colspan=\"8\" bgcolor=\"red\"><strong>Query Count: </strong>" . $ilance->db->query_count . "</td></tr></table>";
				$html .= $ilance->db->explain;
			}
		}
		if ($echo == false)
		{
			return $html;
		}
	}
	
	/*
	* Parses and then immediately prints the file.  Function will be depreciated soon as the name of this function is outdated and will be replaced with ->draw()
	*
	* @param       string
	*/
	function pprint($node = '', $variablearray = '', $echo = true)
	{
		global $ilance, $headinclude, $ilconfig, $navcrumb;
		$ilance->styles->init_head_css();
		$ilance->styles->init_head_js();
		$ilance->styles->init_foot_js();
		if (extension_loaded('zlib') AND isset($_SERVER['HTTP_ACCEPT_ENCODING']) AND substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') AND $ilconfig['globalfilters_gzhandler'])
		{
			@ob_start('ob_gzhandler');
		}	
		$navcrumb = $this->construct_breadcrumb($navcrumb);
		if (defined('LOCATION') AND LOCATION != 'admin')
		{
			$navcrumb['breadcrumbfinal'] = str_replace('$', '\$', $navcrumb['breadcrumbfinal']);
			$navcrumb['breadcrumbtrail'] = str_replace('$', '\$', $navcrumb['breadcrumbtrail']);
			$this->templateregistry["$node"] = preg_replace("/{breadcrumbtrail}/si", "$navcrumb[breadcrumbtrail]", $this->templateregistry["$node"]);
			$this->templateregistry["$node"] = preg_replace("/{breadcrumbfinal}/si", "$navcrumb[breadcrumbfinal]", $this->templateregistry["$node"]);
		}
		$this->register_template_variables($node, $variablearray);
		$this->parse_template($node);
		$html = $this->print_parsed_template($node, $echo);
		if ($echo == false)
		{
			return $html;
		}
	}
        
	/*
	* Function to handle drawing the final parsed template for browser output
	*
	* @param       string           template node
	* @param       string           list of variables to allow for parsing
	*/
	function draw($node = '', $variablearray = '', $echo = true)
	{
		global $ilance, $headinclude, $ilconfig, $navcrumb;
		$ilance->styles->init_head_css();
		$ilance->styles->init_head_js();
		$ilance->styles->init_foot_js();
		if (extension_loaded('zlib') AND isset($_SERVER['HTTP_ACCEPT_ENCODING']) AND substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') AND $ilconfig['globalfilters_gzhandler'])
		{
			@ob_start('ob_gzhandler');
		}	
		$navcrumb = $this->construct_breadcrumb($navcrumb);
		if (defined('LOCATION') AND LOCATION != 'admin')
		{
			$navcrumb['breadcrumbfinal'] = str_replace('$', '\$', $navcrumb['breadcrumbfinal']);
			$navcrumb['breadcrumbtrail'] = str_replace('$', '\$', $navcrumb['breadcrumbtrail']);
			$this->templateregistry["$node"] = preg_replace("/{breadcrumbtrail}/si", "$navcrumb[breadcrumbtrail]", $this->templateregistry["$node"]);
			$this->templateregistry["$node"] = preg_replace("/{breadcrumbfinal}/si", "$navcrumb[breadcrumbfinal]", $this->templateregistry["$node"]);
		}
		$this->register_template_variables($node, $variablearray);
		$this->parse_template($node);
		$html = $this->print_parsed_template($node, $echo);
		if ($echo == false)
		{
			return $html;
		}
	}
        
	/*
	* Parses out and removes any HTML commenting dynamically if required
	*
	* @param       string         template node
	*/
        function parse_htmlcomments($node = '')
	{
                $pattern = '/<!--[^\[](.*)[^\/\/]-->/';
                if (preg_match_all($pattern, $this->templateregistry["$node"], $comments) == true)
                {
                        $this->templateregistry["$node"] = str_replace(array_values($comments[0]), '', $this->templateregistry["$node"]);
                }
	}

	/*
	* Function for parsing $_SESSION['ilancedata'] tags throughout the templates
	*
	* @notes       $_SESSION['ilancedata']['user']['XXXX'] = {user[XXXX]}
	* @usage       {user[username]} would be ILance
	* @param       string
	*/
	function parse_session_globals($node = '')
	{
		if (!empty($_SESSION['ilancedata']) AND is_array($_SESSION['ilancedata']))
		{
			foreach ($_SESSION['ilancedata'] AS $name => $value)
			{
				$pattern = '/' . $this->start . $name . '\[([\w\d_]+)\]' . $this->end . '/';
				if (preg_match_all($pattern, $this->templateregistry[$node], $matches) > 0)
				{
					$matches = array_values(array_unique($this->remove_duplicate_template_variables($matches[1])));
					$replaceable = array();
					foreach ($matches AS $key)
					{
						if (isset($key) AND $key != '')
						{
							$replaceable[$this->start . $name . "[$key]" . $this->end] = (isset($value["$key"]) ? $value["$key"] : '');
						}
					}
					$this->templateregistry["$node"] = str_replace(array_keys($replaceable), array_values($replaceable), $this->templateregistry["$node"]);
					unset($replaceable, $matches);
				}
			}
		}
	}

	/*
	* Function to construct the breadcrumb trail for the client cp template (just under the top nav)
	*
	* @param	string
	*/
	function construct_breadcrumb($navcrumb)
	{
		global $navcrumb, $ilcrumbs, $page_title, $area_title, $phrase, $ilpage;
		$elements = array('breadcrumbtrail' => '', 'breadcrumbfinal' => '');
		$current = sizeof($navcrumb);
		$count = 0;
		if (isset($navcrumb) AND is_array($navcrumb))
		{
			foreach ($navcrumb AS $navurl => $navtitle)
			{
				$type = iif(++$count == $current, 'breadcrumbfinal', 'breadcrumbtrail');
				$dotrail = iif($type == 'breadcrumbtrail', true, false);
				if (empty($navtitle))
				{
					continue;
				}
				if ($dotrail == 1)
				{
					eval('$elements["$type"] .= "' . $this->fetch_template('TEMPLATE_breadcrumb_trail' . ((defined('TEMPLATE_NEWUI') AND defined('TEMPLATE_NEWUI_MODE') AND TEMPLATE_NEWUI AND TEMPLATE_NEWUI_MODE != '') ? '_' . TEMPLATE_NEWUI_MODE : '') . '.html', 0) . '";');
				}
				else
				{
					eval('$elements["$type"] .= "' . $this->fetch_template('TEMPLATE_breadcrumb' . ((defined('TEMPLATE_NEWUI') AND defined('TEMPLATE_NEWUI_MODE') AND TEMPLATE_NEWUI AND TEMPLATE_NEWUI_MODE != '') ? '_' . TEMPLATE_NEWUI_MODE : '') . '.html', 0) . '";');
				}
			}
		}
		return $elements;
	}
	
	/*
	* Function to fetch and print the modal interface for posting a new private message on the site
	*
	* @return	string         Returns HTML representation of the private message popup modal UI
	*/
	function fetch_pmb_modal()
	{
		global $ilance, $show, $ilconfig, $ilpage, $phrase, $headinclude;
		if ($this->pmb_modal_loaded == false)
		{
			require_once(DIR_CORE . 'functions_wysiwyg.php');
			$this->pmb_modal_wysiwyg = print_wysiwyg_editor('message', '', 'bbeditor_pmb', $ilconfig['globalfilters_pmbwysiwyg'], $ilconfig['globalfilters_pmbwysiwyg'], false, '590', '120', '', $ilconfig['default_pmb_wysiwyg'], $ilconfig['ckeditor_pmbtoolbar']);
		}
		$html = '';
		$html .= '<!-- START pmb -->
<div id="pmb_modal" class="modal modal_window" style="display:none; cursor: default; width: ' . $ilconfig['globalfilters_pmbpopupwidth'] . 'px; height: ' . $ilconfig['globalfilters_pmbpopupheight'] . 'px; margin-left:-390px;-webkit-box-shadow: rgb(136, 136, 136) 0px 3px 10px">
<h2 class="a_active" style="padding-bottom:12px"><a href="javascript:void()" class="close" onclick="jQuery(\'#pmb_modal\').jqm({modal: false}).jqmHide(); jQuery(\'body\').css(\'overflow\', \'scroll\')" title="{_close}">{_close}</a>{_private_message} <span id="privatemessageto"></span></h2>
<div style="clear:both;"></div>
<div style="border:none; padding:6px; height:' . ($ilconfig['globalfilters_pmbpopupheight'] - 60) . 'px; background-color: #fff; overflow:auto" id="pmb_modal_top">
<div id="modal_pmb_view_start">
<div id="pmb_preview_table" style="display:none"></div>
<form method="get" name="ilform_pmbform" id="ilform_pmbform" style="margin: 0px;">
<input type="hidden" name="crypted" id="pmbcrypted_modal" />
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr> 
	<td><div>{_subject}</div><div style="padding-top:3px"><input type="text" id="pmbsubject" name="subject" value="" onkeypress="return noenter()" class="input" style="width:585px" /></div></td>
</tr>
<tr>
	<td valign="top"><div style="padding-top:9px;padding-bottom:6px" id="pmbwysiwyg">' . $this->pmb_modal_wysiwyg . '</div><div id="pmbattachmentlist" style="padding-bottom:12px"></div></td>
</tr>
<tr>
	<td><span id="pmbuploadbutton"></span><input type="button" id="previewpmbbutton" value=" {_preview} " class="buttons" style="font-size:15px" onclick="preview_pmb();" />&nbsp;&nbsp;<input type="button" id="submitpmbbutton" value=" {_submit} " class="buttons" style="font-size:15px" onclick="submit_pmb();" /> <span id="modal_pmb_working"></span></td>
</tr>
</table>
</form>
<div id="pmbconversation" style="display:none;padding-top:15px; padding-right:12px"></div></div></div></div><!-- END pmb -->';
		return $html;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>