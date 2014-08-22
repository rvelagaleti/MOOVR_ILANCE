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
// #### load required javascript ###############################################
$jsinclude = array(
	'header' => array(
		'functions',
		'ajax',
		'inline',
		'tabfx',
		'wysiwyg'
	),
	'footer' => array(
		'tooltip',
		'cron'
	)
);
// #### setup script location ##################################################
define('LOCATION', 'admin');
// #### require backend ########################################################
require_once('./../functions/config.php');
require_once(DIR_CORE . 'functions_wysiwyg.php');
// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['dashboard'] => $ilcrumbs[$ilpage['dashboard']]);
$area_title = '{_admin_cp_dashboard}';
$page_title = SITE_NAME . ' - {_admin_cp_dashboard}';
$navroot = '1';
if (($v3nav = $ilance->cache->fetch("print_admincp_nav_dashboard")) === false)
{
    $v3nav = $ilance->admincp->print_admincp_nav($_SESSION['ilancedata']['user']['slng'], $ilpage['dashboard']);
    $ilance->cache->store("print_admincp_nav_dashboard", $v3nav);
}
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
        $vars = array();
	// #### CHART ##########################################################
	include_once(DIR_CORE . 'functions_flash.php');
	$totalusers = print_flash_stats('totalusers', 'stats');
	$admincpnews = $ilance->admincp->fetch_admincp_news();
	// #### MOTD ###########################################################
	$currentmotd = $ilance->db->fetch_field(DB_PREFIX . "motd", "date = '" . DATETODAY . "'", "content");
	$currentmotd_preview = $ilance->bbcode->bbcode_to_html($currentmotd);
	if ($currentmotd_preview == '')
	{
		$currentmotd_preview = '{_none}';
	}
	$ilance->GPC['description'] = !empty($ilance->GPC['description']) ? $ilance->GPC['description'] : '';
	$wysiwyg_area = print_wysiwyg_editor('description', $ilance->GPC['description'], 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg'], false, '590', '120', '');
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'motd')
	{
		if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'insert')
		{
			if (!empty($ilance->GPC['description']))
			{
				$message = $ilance->GPC['description'];
				$message = $ilance->bbcode->prepare_special_codes('PHP', $message);
				$message = $ilance->bbcode->prepare_special_codes('HTML', $message);
				$message = $ilance->bbcode->prepare_special_codes('CODE', $message);
				$message = $ilance->bbcode->prepare_special_codes('QUOTE', $message);
				//$message = $ilance->bbcode->strip_bb_tags($message);
				$message = html_entity_decode($message);
				$ilance->db->query("
					INSERT INTO " . DB_PREFIX . "motd
					(motdid, content, date, visible)
					VALUES (
					NULL,
					'" . $ilance->db->escape_string($message) . "',
					'" . DATETODAY . "',
					'1')
				");
				print_action_success('{_you_have_successfully_composed_a_new_message_of_the_day}', $ilance->GPC['return']);
				exit();
			}
		}
		if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update')
		{
			if (!empty($ilance->GPC['description']))
			{
				$message = $ilance->GPC['description'];
				$message = $ilance->bbcode->prepare_special_codes('PHP', $message);
				$message = $ilance->bbcode->prepare_special_codes('HTML', $message);
				$message = $ilance->bbcode->prepare_special_codes('CODE', $message);
				$message = $ilance->bbcode->prepare_special_codes('QUOTE', $message);
				$message = html_entity_decode($message);
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "motd
					SET content = '" . $ilance->db->escape_string($message) . "'
					WHERE date = '" . DATETODAY . "'
					LIMIT 1
				");
				print_action_success('{_you_have_successfully_updated_the_current_message_of_the_day}', $ilance->GPC['return']);
				exit();
			}
		}
		if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'remove')
		{
			// admin sending bulk email as plain text
			$ilance->db->query("
				DELETE FROM " . DB_PREFIX . "motd
				WHERE date = '" . DATETODAY . "'
			");
			print_action_success('{_the_current_message_of_the_day_was_removed}', $ilpage['dashboard']);
			exit();
		}
		if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'edit')
		{
			$currentmotd = $ilance->db->fetch_field(DB_PREFIX . "motd", "date = '" . DATETODAY . "'", "content");
			if (empty($currentmotd))
			{
				$currentmotd = '{_there_is_no_motd_posted_for_today}';        
			}
			else
			{
				$wysiwyg_area = print_wysiwyg_editor('description', $currentmotd, 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg'], false, '590', '120', '');
			}
		}
	}
	// #### POPULAR KEYWORDS ###############################################
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'searchtag')
	{
		if (isset($ilance->GPC['save']))
		{
			if (isset($ilance->GPC['action']) AND is_array($ilance->GPC['action']))
			{
				foreach ($ilance->GPC['action'] AS $keyword => $value)
				{
					if (isset($keyword))
					{
						$keyword = urldecode($keyword);
						switch ($value)
						{
							case -1:
							{
								$ilance->db->query("
									DELETE FROM " . DB_PREFIX . "search
									WHERE keyword = '" . $ilance->db->escape_string($keyword) . "'
								");
								break;
							}
							case 0:
							{
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "search
									SET visible = '0'
									WHERE keyword = '" . $ilance->db->escape_string($keyword) . "'
								");
								break;
							}
							case 1:
							{
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "search
									SET visible = '1'
									WHERE keyword = '" . $ilance->db->escape_string($keyword) . "'
								");
								break;
							}
						}
					}
				}
			}
		}
		else if (isset($ilance->GPC['showall']))
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "search
				SET visible = '1'
			");
		}
		else if (isset($ilance->GPC['hideall']))
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "search
				SET visible = '0'
			");
		}
		else if (isset($ilance->GPC['deleteselected']))
		{
			if (isset($ilance->GPC['bulk']) AND is_array($ilance->GPC['bulk']))
			{
				foreach ($ilance->GPC['bulk'] AS $keyword)
				{
					if (isset($keyword))
					{
						$keyword = urldecode($keyword);
						$ilance->db->query("
							DELETE FROM " . DB_PREFIX . "search
							WHERE keyword = '" . $ilance->db->escape_string($keyword) . "'
						");
					}
				}
			}
		}
		else if (isset($ilance->GPC['hideselected']))
		{
			if (isset($ilance->GPC['bulk']) AND is_array($ilance->GPC['bulk']))
			{
				foreach ($ilance->GPC['bulk'] AS $keyword)
				{
					if (isset($keyword))
					{
						$keyword = urldecode($keyword);
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "search
							SET visible = '0'
							WHERE keyword = '" . $ilance->db->escape_string($keyword) . "'
						");
					}
				}
			}
		}
		else if (isset($ilance->GPC['showselected']))
		{
			if (isset($ilance->GPC['bulk']) AND is_array($ilance->GPC['bulk']))
			{
				foreach ($ilance->GPC['bulk'] AS $keyword)
				{
					if (isset($keyword))
					{
						$keyword = urldecode($keyword);
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "search
							SET visible = '1'
							WHERE keyword = '" . $ilance->db->escape_string($keyword) . "'
						");
					}
				}
			}
		}
		print_action_success('{_tags_visibily_status_were_successfully_updated}', ((isset($ilance->GPC['returnurl']) AND !empty($ilance->GPC['returnurl'])) ? urldecode($ilance->GPC['returnurl']) : $ilpage['dashboard']));
		exit();
	}
	/**/
	function first_letter_exists_in_popular_search($letter = '')
	{
		global $ilance;
		if ($letter == '0-9')
		{
			$query = "
			SELECT keyword
			FROM " . DB_PREFIX . "search
			WHERE visible = '1'
			REGEXP '^[0-9]'";
		}
		else
		{
			$query = "
			SELECT keyword
			FROM " . DB_PREFIX . "search
			WHERE keyword LIKE '" . $ilance->db->escape_string($letter) . "%'";
		}
		$sql = $ilance->db->query($query);
		if ($ilance->db->num_rows($sql) > 0)
		{
			return true;
		}
		return false;
	}
	$q = isset($ilance->GPC['q']) ? handle_input_keywords($ilance->GPC['q']) : '';
	$atoz = '';
	for ($i = 65; $i <= 90; $i++)
	{
		$x = chr($i);
		if (first_letter_exists_in_popular_search($x))
		{
			$atoz .= "<span style=\"font-size:13px\" class=\"blue\"><a href=\"" . $ilpage['dashboard'] . "?cmd=keywords&amp;ql=$x\">$x</a>&nbsp;&nbsp;<span class=\"smaller litegray\">|</span>&nbsp;&nbsp;</span>";
		}
		else
		{
			$atoz .= "<span style=\"font-size:13px\" class=\"gray\">$x&nbsp;&nbsp;<span class=\"smaller litegray\">|</span>&nbsp;&nbsp;</span>";
		}
	}
	$atoz .= "<span style=\"font-size:13px\" class=\"blue\"><a href=\"" . $ilpage['dashboard'] . "?cmd=keywords&amp;ql=0-9\">0-9</a></span>";
	$maxrowsdisplay = (isset($ilance->GPC['pp']) AND is_numeric($ilance->GPC['pp'])) ? intval($ilance->GPC['pp']) : $ilconfig['globalfilters_maxrowsdisplay'];
	if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
	{
		$ilance->GPC['page'] = 1;
	}
	else
	{
		$ilance->GPC['page'] = intval($ilance->GPC['page']);
	}
	$limit = ' ORDER BY keyword ASC, count DESC LIMIT ' . (($ilance->GPC['page'] - 1) * $maxrowsdisplay) . ',' . $maxrowsdisplay;
	$pagenavbit = '';
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'keywords')
	{
		if (isset($ilance->GPC['q']) AND !empty($ilance->GPC['q']))
		{
			$keywordquery = "keyword LIKE '%" . $ilance->db->escape_string($ilance->GPC['q']) . "%'";
			$pagenavbit = '&q=' . handle_input_keywords($ilance->GPC['q']) . '';
		}
		else if (isset($ilance->GPC['ql']) AND !empty($ilance->GPC['ql']))
		{
			if ($ilance->GPC['ql'] == '0-9')
			{
				$keywordquery = "keyword != '' REGEXP '^[0-9]'";
				$pagenavbit = '&ql=0-9';
			}
			else
			{
				$keywordquery = "keyword LIKE '" . $ilance->db->escape_string($ilance->GPC['ql']) . "%'";
				$pagenavbit = '&ql=' . handle_input_keywords($ilance->GPC['ql']) . '';
			}
		}
		else
		{
			$keywordquery = "keyword != ''";
		}
	}
	else
	{
		$keywordquery = "keyword != ''";
	}
	$query_fav = $ilance->db->query("
		SELECT keyword, SUM(count) AS count, visible, cid
		FROM " . DB_PREFIX . "search
		WHERE $keywordquery
		GROUP BY keyword
	", 0, null, __FILE__, __LINE__);
	$totalcount = $ilance->db->num_rows($query_fav);
	$totalkeywords = number_format($totalcount); // 5831
	$counter = ($ilance->GPC['page'] - 1) * $maxrowsdisplay;
	$query_fav = $ilance->db->query("
		SELECT keyword, SUM(count) AS count, visible, cid
		FROM " . DB_PREFIX . "search
		WHERE $keywordquery
		GROUP BY keyword
		$limit
	", 0, null, __FILE__, __LINE__);
	$show['count_tags'] = $ilance->db->num_rows($query_fav);
	if ($show['count_tags'] >= 1)
	{
		while ($arr_fav = $ilance->db->fetch_array($query_fav, DB_ASSOC))
		{
			$favourite['tag_name'] = urlencode($arr_fav['keyword']);
			$favourite['tag_name_plain'] = print_string_wrap(handle_input_keywords($arr_fav['keyword']), 50);
			$favourite['category'] = $ilance->admincp_dashboard->print_keyword_searched_in_categories($arr_fav['keyword']);
			$favourite['tag_count'] = (($arr_fav['count'] <= 0) ? 1 : $arr_fav['count']);
			$action = '<select name="action[' . $favourite['tag_name'] . ']" class="select">';
			if ($arr_fav['visible'])
			{
				$action .= '<option value="1" selected="selected">{_show}</option>';
				$action .= '<option value="0">{_hide}</option>';
				$action .= '<option value="-1">{_delete}</option>';
			}
			else
			{
				$action .= '<option value="1">{_show}</option>';
				$action .= '<option value="0" selected="selected">{_hide}</option>';
				$action .= '<option value="-1">{_delete}</option>';
			}
			$action .= '</select>';
			$favourite['action'] = $action;
			$favourite_tag[] = $favourite;
		}
		$prevnextkeywords = print_pagnation($totalcount, $maxrowsdisplay, $ilance->GPC['page'], $counter, $ilpage['dashboard'] . '?cmd=keywords' . $pagenavbit);
	}
        // #### DISMISS NEWS ARTICLE ###########################################
        if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'news' AND isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'dismiss' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
        {
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "admincp_news
                        SET visible = '0'
                        WHERE newsid = '" . intval($ilance->GPC['id']) . "'
			LIMIT 1
                ");
                refresh(HTTPS_SERVER_ADMIN . $ilpage['dashboard']);
                exit();
        }
	// #### HERO PICTURE MANAGER ###########################################
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'heromanage')
	{
		if (isset($ilance->GPC['activatehero']) AND isset($ilance->GPC['source_url4']) AND !empty($ilance->GPC['source_url4']))
		{
			$ilance->db->query("
				INSERT INTO " . DB_PREFIX . "hero
				(id, mode, cid, filename, imagemap, date_added, sort)
				VALUES (
				NULL,
				'homepage',
				'0',
				'" . $ilance->db->escape_string($ilance->GPC['source_url4']) . "',
				'" . $ilance->db->escape_string($ilance->GPC['html_container']) . "',
				NOW(),
				'" . intval($ilance->GPC['sort']) . "')
			");
		}
		else if (isset($ilance->GPC['inactivatehero']) AND isset($ilance->GPC['source_url3']) AND !empty($ilance->GPC['source_url3']))
		{
			$ilance->db->query("
				DELETE FROM " . DB_PREFIX . "hero
				WHERE filename = '" . $ilance->db->escape_string($ilance->GPC['source_url3']) . "'
				LIMIT 1
			");
		}
		else if (isset($ilance->GPC['updatehero']) AND isset($ilance->GPC['source_url3']) AND !empty($ilance->GPC['source_url3']))
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "hero
				SET sort = '" .intval($ilance->GPC['sort']) . "',
				imagemap = '" . $ilance->db->escape_string($ilance->GPC['html_container']) . "'
				WHERE filename = '" . $ilance->db->escape_string($ilance->GPC['source_url3']) . "'
				LIMIT 1
			");
		}
	}
        // #### BACKUP MARKETPLACE CONFIGURATION ###############################
        if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'backup')
        {
		if ($show['ADMINCP_TEST_MODE'])
		{
			print_action_failed('{_demo_mode_only}', $ilpage['components']);
			exit();
		}
                $area_title = '{_exporting_marketplace_configuration_to_xml}';
		$page_title = SITE_NAME . ' - {_exporting_marketplace_configuration_to_xml}';
                $xml_output = "<?xml version=\"1.0\"?>" . LINEBREAK;
                $xml_output .= "<!-- This configuration build was generated on " . DATETIME24H . " //-->" . LINEBREAK;
                $xml_output .= "<config ilversion=\"" . $ilance->config['ilversion'] . "\">" . LINEBREAK;
                $query2 = $ilance->db->query("
                        SELECT parentgroupname, groupname, sort
                        FROM " . DB_PREFIX . "configuration_groups
                        ORDER BY sort ASC
                ");
                if ($ilance->db->num_rows($query2) > 0)
                {
                        while ($groupres = $ilance->db->fetch_array($query2, DB_ASSOC))
                        {
                                $xml_output .= "\t<configgroup parentgroupname=\"" . stripslashes($groupres['parentgroupname']) . "\" groupname=\"" . stripslashes($groupres['groupname']) . "\" sort=\"" . stripslashes($groupres['sort']) . "\">" . LINEBREAK;
                                $query3 = $ilance->db->query("
                                        SELECT name, value, configgroup, inputtype, inputcode, inputname, sort, visible
                                        FROM " . DB_PREFIX . "configuration
                                        WHERE configgroup = '" . $groupres['groupname'] . "'
                                        ORDER BY sort ASC
                                ");
                                if ($ilance->db->num_rows($query3) > 0)
                                {
                                        while ($res = $ilance->db->fetch_array($query3))
                                        {
                                                $xml_output .= "\t\t<setting name=\"" . stripslashes($res['name']) . "\" value=\"" . ilance_htmlentities($res['value']) . "\" configgroup=\"" . $res['configgroup'] . "\" inputtype=\"" . $res['inputtype'] . "\" inputcode=\"" . ilance_htmlentities($res['inputcode']) . "\" inputname=\"" . $res['inputname'] . "\" sort=\"" . $res['sort'] . "\" visible=\"" . $res['visible'] . "\"></setting>" . LINEBREAK;
                                        }
                                }
                                $xml_output .= "\t</configgroup>" . LINEBREAK;
                        }
                }
                $xml_output .= "</config>";
                $ilance->common->download_file($xml_output, 'ilance_' . VERSIONSTRING . '_config.xml', 'text/plain');
                exit();
        }
        // #### RESTORE MARKETPLACE CONFIGURATION ##############################
        if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'restore')
        {
		if ($show['ADMINCP_TEST_MODE'])
		{
			print_action_failed('{_demo_mode_only}', $ilpage['components']);
			exit();
		}
                $area_title = '{_restoring_marketplace_configuration_via_xml}';
		$page_title = SITE_NAME . ' - {_restoring_marketplace_configuration_via_xml}';
		while (list($key, $value) = each($_FILES))
		{
			$GLOBALS[$key] = $value;
			foreach ($_FILES AS $key => $value)
			{
				$GLOBALS[$key] = $_FILES[$key]['tmp_name'];
				foreach ($value as $ext => $value2)
				{
					$key2 = $key . '_' . $ext;
					$GLOBALS[$key2] = $value2;
				}
			}
		}
		$xml = file_get_contents($xml_file);
		$xml_encoding = '';
		if (MULTIBYTE)
		{
			$xml_encoding = mb_detect_encoding($xml);
		}
		if ($xml_encoding == 'ASCII')
		{
			$xml_encoding = '';
		}
		$data = array();
		$parser = xml_parser_create($xml_encoding);
		xml_parse_into_struct($parser, $xml, $data);
		$error_code = xml_get_error_code($parser);
		xml_parser_free($parser);
		if ($error_code == 0)
		{
			$ilance->xml = construct_object('api.xml');
			$result = $ilance->xml->process_config_xml($data, $xml_encoding);
			print_r($result);
			exit;
			if ($result['ilversion'] != $ilance->config['ilversion'])
			{
				print_action_failed('{_the_version_of_the_this_configuration_xml_package_is_different_than_the_currently_installed_version_of_ilance} <strong><em>' . $ilance->config['ilversion'] . '</em></strong>.  {_the_operation_has_aborted_due_to_a_version_conflict}', $ilance->GPC['return']);
				exit();
			}
		}
		else
		{
			print_action_failed('{_were_sorry_there_was_an_error_with_the_formatting_of_the_configuration_file} [' . xml_error_string($error_code) . '].', $ilance->GPC['return']);
			exit();
		}        
        }
        $headinclude .= '<script type="text/javascript">
<!--
if (!window.XMLHttpRequest)
{
	var reqObj = 
	[
		function() {return new ActiveXObject("Msxml2.XMLHTTP");},
		function() {return new ActiveXObject("Microsoft.XMLHTTP");},
		function() {return window.createRequest();}
	];
	for(a = 0, z = reqObj.length; a < z; a++)
	{
		try
		{
			window.XMLHttpRequest = reqObj[a];
			break;
		}
		catch(e)
		{
			window.XMLHttpRequest = null;
		}
	}
}
var req = new XMLHttpRequest();
function show_search_results()
{
	if (fetch_js_object(\'var\'))
	{
		if (fetch_js_object(\'var\').value.length > 2)
		{
			var varname = fetch_js_object(\'var\').value;
		}
		else
		{
			var varname = \'x\';
		}
	}
	else
	{
		var varname = \'x\';
	}
	req.open(\'GET\', \'' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['ajax'] . '?do=search_configuration_variable&var=\' + varname);
	req.send(null); 
}
req.onreadystatechange = function()
{
	if (req.readyState == 4 && req.status == 200)
	{
		var myString;
		myString = req.responseText;
		obj = fetch_js_object(\'results\');
		obj.innerHTML = myString;
	}
}   
//-->
</script>
';
	if (($dashboard = $ilance->cache->fetch("admincp_dashboard_fetch")) === false)
	{
		$dashboard = $ilance->admincp_dashboard->fetch();
		$ilance->cache->store("admincp_dashboard_fetch", $dashboard);
	}
	
	($apihook = $ilance->api('admincp_dashboard_mid')) ? eval($apihook) : false;
	
	$dashboard[] = $dashboard;
	
	// #### HERO MANAGER ##################################################
	$heropictureoptions = $activeheropictureoptions = '';
	$show['inactiveheros'] = false;
	$active = array();
	$sql = $ilance->db->query("
		SELECT id, mode, filename, imagemap, date_added, sort
		FROM " . DB_PREFIX . "hero
		ORDER BY sort ASC
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$active[] = $res['filename'];
			$activeheropictureoptions .= '<option value="' . $res['filename'] . '">' . $res['filename'] . ' | {_location}: ' . $res['mode'] . ' | {_display_order}: ' . $res['sort'] . '</option>';
		}
	}
	$dirname = DIR_SERVER_ROOT . 'images/default/heros';
	$dir = opendir($dirname);
	while (false != ($file = readdir($dir)))
        {
		if (($file != '.') AND ($file != '..'))
		{
			if (!in_array($file, $active))
			{
				$heropictureoptions .= '<option value="' . $file . '">' . $file . '</option>';
			}
		}
        }
	if (empty($heropictureoptions))
	{
		$show['inactiveheros'] = true;
	}
	$pprint_array = array('q','atoz','totalkeywords','prevnextkeywords','results','currentmotd','currentmotd_preview','wysiwyg_area','admincpnews','totalusers','heropictureoptions','activeheropictureoptions');
	
	($apihook = $ilance->api('admincp_dashboard_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'dashboard.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', 'v3nav', false);
	$ilance->template->parse_loop('main', 'dashboard');
	$ilance->template->parse_loop('main', 'favourite_tag');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN. $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>