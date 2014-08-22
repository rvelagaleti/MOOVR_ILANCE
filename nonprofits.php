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
		'jquery'
	),
	'footer' => array(
		'v4',
		'tooltip',
		'autocomplete',
		'cron'
	)
);

// #### define top header nav ##################################################
$topnavlink = array(
        'nonprofits'
);

// #### setup script location ##################################################
define('LOCATION', 'nonprofits');

// #### require backend ########################################################
require_once('./functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[nonprofits]" => $ilcrumbs["$ilpage[nonprofits]"]);

$area_title = '{_nonprofits}';
$page_title = SITE_NAME . ' - {_nonprofits}';

($apihook = $ilance->api('nonprofits_start')) ? eval($apihook) : false;

if ($ilconfig['enablenonprofits'] == 0)
{
	$navcrumb = array("nonprofits.php" => '{_nonprofits}');
	print_notice('{_disabled}', '{_were_sorry_this_feature_is_currently_disabled}', HTTP_SERVER . $ilpage['main'], '{_main_menu}');
	exit();
}

// #### detailed nonprofit view ################################################
if (isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
        $sql = $ilance->db->query("
                SELECT charityid, title, description, url, donations, earnings, visible
                FROM " . DB_PREFIX . "charities
                WHERE charityid = '" . intval($ilance->GPC['id']) . "'
                LIMIT 1
        ");
        if ($ilance->db->num_rows($sql) > 0)
        {
                while ($res = $ilance->db->fetch_array($sql))
                {
                        $title = $res['title'];
                        if ($ilconfig['globalauctionsettings_seourls'])
                        {
                                $navcrumb = array(HTTP_SERVER . "nonprofits" => '{_nonprofits}', "$ilpage[nonprofits]?id=$res[charityid]" => $res['title']);
                        }
                        else
                        {
                                $navcrumb = array(HTTP_SERVER . "$ilpage[nonprofits]" => '{_nonprofits}', "$ilpage[nonprofits]?id=$res[charityid]" => $res['title']);
                        }
                        $charityid = $res['charityid'];
                        $nonprofit[] = $res;
                }
                unset($res);
        }
        
        // #### active sellers supporting this nonprofit #######################
        $url = ($ilconfig['globalauctionsettings_seourls']) ? 'sell?donation=1&amp;charityid=' . $charityid : $ilpage['main'] . '?cmd=selling&amp;donation=1&amp;charityid=' . $chairtyid;
        $activesellers = '<span class="gray">{_none}</span> - <strong><a href="' . $url . '">{_be_the_first}</a></strong>';
        $sql = $ilance->db->query("
                SELECT user_id
                FROM " . DB_PREFIX . "projects
                WHERE donation = '1'
                        AND charityid = '" . intval($ilance->GPC['id']) . "'
                GROUP BY user_id
                LIMIT 30
        ");
        if ($ilance->db->num_rows($sql) > 0)
        {
                $activesellers = '';
                while ($res = $ilance->db->fetch_array($sql))
                {
                        //$activesellers .= fetch_user('username', $res['user_id']) . ' <span class="gray">(' . fetch_nonprofit_seller_count($res['user_id'], intval($ilance->GPC['id'])) . ')</span>, ';
                        $username = fetch_user('username', $res['user_id']);
                        $activesellers .= '<a href="' . $ilpage['search'] . '?mode=product&amp;searchuser=' . $username . '&amp;donation=1&amp;charityid=' . $charityid . '">' . $username . '</a> <!--<span class="gray">(0)</span>-->, ';
                        unset($username);
                }
                unset($res);
                
                if (!empty($activesellers))
                {
                        $activesellers = mb_substr($activesellers, 0, -2);
                }
                
        }
        
        $area_title = '{_nonprofits}<div class="smaller">' . $title . '</div>';
        $page_title = $title . ' {_nonprofit} {_on} ' . SITE_NAME;
}

// #### nonprofit main menu ####################################################
else
{
        $sql = $ilance->db->query("
                SELECT charityid, title, description, url, donations, earnings, visible
                FROM " . DB_PREFIX . "charities
                ORDER BY RAND()
                LIMIT 2
        ");
        if ($ilance->db->num_rows($sql) > 0)
        {
                while ($res = $ilance->db->fetch_array($sql))
                {
                        $randomnonprofits[] = $res;
                }
        }
        
        $totalsnonprofit = $ilance->currency->format(0);
        $sql = $ilance->db->query("
                SELECT SUM(earnings) AS amount
                FROM " . DB_PREFIX . "charities
        ");
        if ($ilance->db->num_rows($sql) > 0)
        {
                while ($res = $ilance->db->fetch_array($sql))
                {
                        $totalsnonprofit = $ilance->currency->format($res['amount']);
                }
        }
        
        $totalsellers = 0;
        $sql = $ilance->db->query("
                SELECT SUM(donations) AS count
                FROM " . DB_PREFIX . "charities
        ");
        if ($ilance->db->num_rows($sql) > 0)
        {
                while ($res = $ilance->db->fetch_array($sql))
                {
                        $totalsellers = number_format($res['count']);
                }
        }
}

$pprint_array = array('totalsnonprofit','totalsellers','activesellers','charityid','type','abusetype_pulldown','requesturi','url','cmd','id','input_style');

$ilance->template->fetch('main', 'nonprofits.html');
$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
$ilance->template->parse_loop('main', 'randomnonprofits');
$ilance->template->parse_loop('main', 'nonprofit');
$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', $pprint_array);
exit();

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>