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
	    'tabfx'
	),
	'footer' => array(
		'tooltip',
		'cron'
	)
);
// #### setup script location ##################################################
define('LOCATION', 'admin');
define('AREA', 'language');
// #### require backend ########################################################
require_once('./../functions/config.php');

($apihook = $ilance->api('admincp_language_start')) ? eval($apihook) : false;

// #### setup default breadcrumb ###############################################
$navcrumb = array ("$ilpage[language]" => $ilcrumbs["$ilpage[language]"]);
if (($v3nav = $ilance->cache->fetch("print_admincp_nav_language")) === false)
{
	$v3nav = $ilance->admincp->print_admincp_nav($_SESSION['ilancedata']['user']['slng'], $ilpage['language']);
	$ilance->cache->store("print_admincp_nav_language", $v3nav);
}
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	if (isset($ilance->GPC['cmd']))
	{
		include_once('language_cmd.php');
	}
	else
	{
		$area_title = '{_language_administration_menu}';
		$page_title = SITE_NAME . ' - {_language_administration_menu}';
	
		($apihook = $ilance->api('admincp_language_management')) ? eval($apihook) : false;
	
		$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['language'], $ilpage['language'], $_SESSION['ilancedata']['user']['slng']);
		$show['editlanguage'] = false;
		// does admin request template to show all phrases in drop down?
		if (isset($ilance->GPC['showphrases']))
		{
			$phrases_selectlist = '<select name="phrasesid[]" multiple size="10" style="font-family: verdana">';
			$result = $ilance->db->query("
				SELECT phraseid, varname
				FROM " . DB_PREFIX . "language_phrases
				ORDER BY phraseid ASC
			");
			while ($record = $ilance->db->fetch_array($result))
			{
				$phrases_selectlist .= '<option value="' . $record['phraseid'] . '">[' . $record['phraseid'] . '] ' . shorten($record['varname'], 70) . '</option>';
			}
			$phrases_selectlist .= '</select>';
		}
		$language_pulldown = $ilance->language->print_language_pulldown();
		$phr = '{_choose_base_language}';
		$base_language_pulldown = $ilance->language->print_language_pulldown(false, false, 'baselanguage', $phr);
		$phrasegroup_pulldown = $ilance->language->print_phrase_groups_pulldown(false, false, $_SESSION['ilancedata']['user']['slng']);
		$limit_pulldown = '<select name="limit" class="flat" style="font-family: verdana">';
		$limit_pulldown .= '<option value="5" ';
		if (isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "5" OR isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "5")
		{
			$limit_pulldown .= 'selected="selected"';
		}
		$limit_pulldown .= '>5 ' . '{_per_page}' . '</option>';
		$limit_pulldown .= '<option value="10" ';
		if (isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "10" OR isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "10")
		{
			$limit_pulldown .= 'selected="selected"';
		}
		$limit_pulldown .= '>10 ' . '{_per_page}' . '</option>';
		$limit_pulldown .= '<option value="25" ';
		if (isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "25" OR isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "25")
		{
			$limit_pulldown .= 'selected="selected"';
		}
		$limit_pulldown .= '>25 ' . '{_per_page}' . '</option>';
		$limit_pulldown .= '<option value="50" ';
		if (isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "50" OR isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "50")
		{
			$limit_pulldown .= 'selected="selected"';
		}
		$limit_pulldown .= '>50 ' . '{_per_page}' . '</option>';
		$limit_pulldown .= '</select>';
		if (isset($ilance->GPC['keyword']) AND $ilance->GPC['keyword'] != '')
		{
			$keyword = $ilance->GPC['keyword'];
		}
		// mysql character set information
		$charsetvariable = $ilance->db->query("SHOW VARIABLES LIKE 'character_set%'");
		if ($ilance->db->num_rows($charsetvariable) > 0)
		{
			while ($resvar = $ilance->db->fetch_array($charsetvariable))
			{
				$mysqlcharset[] = $resvar;
			}
		}
		// mysql connection collation
		$collation = $ilance->db->query("SHOW VARIABLES LIKE 'collation%'");
		if ($ilance->db->num_rows($collation) > 0)
		{
			while ($rescollation = $ilance->db->fetch_array($collation))
			{
				$mysqlcollation[] = $rescollation;
			}
		}
		// language management results
		$languageresults = $ilance->db->query("
			SELECT languageid, languagecode, title, charset, locale, author, textdirection, languageiso, canselect, installdate
			FROM " . DB_PREFIX . "language
		");
		if ($ilance->db->num_rows($languageresults) > 0)
		{
			$rowcount = 0;
			while ($res = $ilance->db->fetch_array($languageresults))
			{
				$res['actions'] = '<a href="' . $ilpage['language'] . '?cmd=edit-language&amp;id=' . $res['languageid'] . '#editlanguage"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
				if ($res['textdirection'] == 'ltr')
				{
					$res['textdirection'] = '{_left_to_right}';
				}
				else
				{
					$res['textdirection'] = '{_right_to_left}';
				}
				if ($res['installdate'] != '0000-00-00 00:00:00')
				{
					$res['installdate'] = print_date($res['installdate']);
				}
				else
				{
					$res['installdate'] = '-';
				}
				$res['class'] = ($rowcount % 2) ? 'alt2' : 'alt1';
				$installedlanguages[] = $res;
				$rowcount++;
			}
		}
		$masterphrases = number_format($ilance->admincp->fetch_master_phrases_count());
		$customphrases = number_format($ilance->admincp->fetch_custom_phrases_count());
		$movedphrases = number_format($ilance->admincp->fetch_moved_phrases_count());
		$totalphrases = number_format($ilance->admincp->fetch_total_phrases_count());
		$global_languagesettings = $ilance->admincp->construct_admin_input('language', $ilpage['language']);
		$adminuser = $_SESSION['ilancedata']['user']['username'];
		$products_pulldown = $ilance->admincp->products_pulldown('');
		$pprint_array = array ('products_pulldown', 'replacements', 'canselect0', 'canselect1', 'languageiso', 'textdirection0', 'textdirection1', 'masterphrases', 'customphrases', 'movedphrases', 'totalphrases', 'adminuser', 'id', 'author', 'title', 'locale', 'charset', 'defaultlanguage0', 'defaultlanguage1', 'global_languagesettings', 'language_pulldown2', 'phrases_selectlist', 'keyword', 'base_language_pulldown', 'limit_pulldown', 'phrasegroup_pulldown', 'language_pulldown');
	
		($apihook = $ilance->api('admincp_language_end')) ? eval($apihook) : false;
	
		$ilance->template->fetch('main', 'language.html', 1);
		$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
		$ilance->template->parse_loop('main', 'mysqlcharset');
		$ilance->template->parse_loop('main', 'mysqlcollation');
		$ilance->template->parse_loop('main', 'installedlanguages');
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>