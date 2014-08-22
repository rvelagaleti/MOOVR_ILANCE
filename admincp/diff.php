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

// #### require backend ########################################################
require_once('./../functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[login]" => $ilcrumbs["$ilpage[login]"]);

$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['login'], $ilpage['login'], $_SESSION['ilancedata']['user']['slng']);
$cmd = '';

$area_title = 'Diff';
$page_title = SITE_NAME . ' - Diff';

// set default redirection to dashboard area
$redirect = HTTPS_SERVER_ADMIN . $ilpage['dashboard'];
if (!empty($ilance->GPC['redirect']))
{
        $redirect = trim($ilance->GPC['redirect']);
}

$left_template = '';
$right_template = '';

$ilance->diff = construct_object('api.diff', $left_template, $right_template);
$entries =& $ilance->diff->fetch_diff();
$html = '';
foreach ($entries as $diff_entry)
{
        // possible classes: unchanged, notext, deleted, added, changed
        $html .= "<tr>\n\t";
        $html .= '<td width="50%" valign="top" class="diff-' . $diff_entry->fetch_data_old_class() . '" dir="ltr">' .
                $diff_entry->prep_diff_text($diff_entry->fetch_data_old(), $ilance->GPC['wrap']) . "</td>\n\t";
        $html .= '<td width="50%" valign="top" class="diff-' . $diff_entry->fetch_data_new_class() . '" dir="ltr">' .
                $diff_entry->prep_diff_text($diff_entry->fetch_data_new(), $ilance->GPC['wrap']) . "</td>\n";
        $html .= "</tr>\n\n";
}

$html .= "<tr><td class=\"diff-deleted\" align=\"center\" width=\"50%\">" . '{_removed_from_old_version}' . "</td><td class=\"diff-notext\">&nbsp;</td></tr>\n";
$html .= "<tr><td class=\"diff-changed\" colspan=\"2\" align=\"center\">" . '{_changed_between_versions}' . "</td></tr>\n";
$html .= "<tr><td class=\"diff-notext\" width=\"50%\">&nbsp;</td><td class=\"diff-added\" align=\"center\">" . '{_added_in_new_version}' . "</td></tr>\n";

$ilance->template->fetch('main', 'diff.html', 1);
$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', array('headinclude','html','out_filename1','relfilename','out_filename2','out_ratio2','param_togglefullsourcesubmit','param_togglefullsource','param_filename','param_md5verify','param_localmd5','linestyleclass','lnum','linestylex','linestylex2','linetext','linetext2','global_serverliveupdate','livesync_ui_showprogress','livesync_ui_startsync','livesync_ui_confirmsync','livesync_sql_selection','livesync_ui_selection','i','this_script','localhost','remotehost','input_style'));
exit();

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>