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
        'feedback'
);

// #### setup script location ##################################################
define('LOCATION','feedback');

// #### require backend ########################################################
require_once('./functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array();
$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
$navcrumb[""] = '{_feedback}';

// #### LEAVE FEEDBACK #########################################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_leave-feedback' AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
{
        $area_title = '{_leave_feedback}';
        $page_title = SITE_NAME . ' - {_leave_feedback}';
        // #### define our feedback control tabs ###############################
        $showview = $jsbit = '';
        if (empty($ilance->GPC['view']) OR !empty($ilance->GPC['view']) AND $ilance->GPC['view'] == 0)
        {
                $showview = '<div style="font-size:13px" class="gray"><strong>{_view}:</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><span class="black">{_all}</span></strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><span class="blue"><a href="' . HTTP_SERVER . $ilpage['feedback'] . '?cmd=_leave-feedback&amp;view=1">{_bought}</a></span></strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><span class="blue"><a href="' . HTTP_SERVER . $ilpage['feedback'] . '?cmd=_leave-feedback&amp;view=2">{_sold}</a></span></strong></div>';
                $ilance->GPC['view'] = 'all';
        }
        else if (!empty($ilance->GPC['view']) AND $ilance->GPC['view'] == 1)
        {
                $showview = '<div style="font-size:13px" class="gray"><strong>{_view}:</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><span class="blue"><a href="' . HTTP_SERVER . $ilpage['feedback'] . '?cmd=_leave-feedback">{_all}</a></span></strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><span class="black">{_bought}</span></strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><span class="blue"><a href="' . HTTP_SERVER . $ilpage['feedback'] . '?cmd=_leave-feedback&amp;view=2">{_sold}</a></span></strong></div>';
                $ilance->GPC['view'] = 'bought';
        }
        else if (!empty($ilance->GPC['view']) AND $ilance->GPC['view'] == 2)
        {
                $showview = '<div style="font-size:13px" class="gray"><strong>{_view}:</strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><span class="blue"><a href="' . HTTP_SERVER . $ilpage['feedback'] . '?cmd=_leave-feedback">{_all}</a></span></strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><span class="blue"><a href="' . HTTP_SERVER . $ilpage['feedback'] . '?cmd=_leave-feedback&amp;view=1">{_bought}</a></span></strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong><span class="black">{_sold}</span></strong></div>';
                $ilance->GPC['view'] = 'sold';
        }
        // #### generate loop for our template display #########################
        $fb = $ilance->mycp->feedback_activity($_SESSION['ilancedata']['user']['userid'], $ilance->GPC['view']);
        $feedback = (isset($fb) AND is_array($fb)) ? $fb[0] : array();
        $count = count($feedback);
	$hiddenfields = (isset($ilance->GPC['returnurl']) ? '<input type="hidden" name="returnurl" value="' . handle_input_keywords(urlencode($ilance->GPC['returnurl'])) . '" />' : '');
        $show['noresults'] = ($count == 0) ? true : false;
        $criteria = $ilance->feedback->criteria(0, $_SESSION['ilancedata']['user']['slng']);
        if (isset($feedback) AND is_array($feedback))
        {
                foreach ($feedback AS $key => $value)
                {
                        $GLOBALS['feedback_criteria' . $value['project_id']] = $criteria;
                }
        }
        // #### build our javascript ###########################################
        $headinclude .= '<script type="text/javascript"> 
<!--
son = \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/star_on.gif\';
soff = \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/star_off.gif\';
enablerating = new Array(1);
enableratingsend = new Array(1);
for (i = 0; i < 100; ++ i)
{
        enablerating[i] = new Array(1);
        enableratingsend[i] = new Array(1);
}
';
$i = 0;
foreach ($criteria AS $key => $value)
{
        foreach ($feedback AS $key2 => $value2)
        {
                $headinclude .= '
enablerating[\'' . $value['id'] . '\'][\'' . $value2['project_id'] . '_' . $value2['md5'] . '\'] = 1;
enableratingsend[\'' . $value['id'] . '\'][\'' . $value2['project_id'] . '_' . $value2['md5'] . '\'] = 1;
';
        }
        $i++;
}
        $headinclude .= 'function starover(sid, pid, cid)
{
	if (enablerating[cid][pid] == 1)
	{
		counter = 5;
		while (counter < 6 && counter > 0)
		{
			if (counter > sid)
			{
				fetch_js_object(\'star\' + counter + \'_\' + pid + \'_\' + cid).src = soff;
                                fetch_js_object(\'fbtext_\' + pid + \'_\' + cid).innerHTML = sid + \' {_of_5_stars}\';
			}
                        else
                        {
				fetch_js_object(\'star\' + counter + \'_\' + pid + \'_\' + cid).src = son;
                                fetch_js_object(\'fbtext_\' + pid + \'_\' + cid).innerHTML = sid + \' {_of_5_stars}\';
			}
			counter--;
		}
                if (sid == \'0\')
                {
                        fetch_js_object(\'fbtext_\' + pid + \'_\' + cid).innerHTML = \'\';        
                }
	}
}
function stardown(sid, pid, cid)
{
	enablerating[cid][pid] = 1;
	//if (enableratingsend[cid][pid] == 1)
	{
		starover(sid, pid, cid);
                fetch_js_object(\'fbtext_\' + pid + \'_\' + cid).innerHTML = sid + \' {_of_5_stars}\';
                fetch_js_object(\'criteria_\' + pid + \'_\' + cid).value = sid;
		enableratingsend[cid][pid] = 0;
	}
	enablerating[cid][pid] = 0;
}
//-->
</script>
';
        $pprint_array = array('hiddenfields','showview','for_user_id','from_user_id','from_type_reverse','responsepulldown','totalamount','project_title','customer','project_id','seller_id','buyer_id','from_type');
        $ilance->template->fetch('main', 'feedback.html');
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
        $ilance->template->parse_loop('main', 'feedback','criteria');
        if (!isset($feedback))
        {
                $feedback = array();
        }
        @reset($feedback);
        while ($i = @each($feedback))
        {
                $ilance->template->parse_loop('main', 'feedback_criteria' . $i['value']['project_id']);
        }
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', $pprint_array);
        exit();
}
// #### FEEDBACK COMPLETE ######################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'complete' AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
{
	$area_title = '{_leave_feedback}';
        $page_title = SITE_NAME . ' - {_leave_feedback}';
	$pprint_array = array();
        $ilance->template->fetch('main', 'feedback_complete.html');
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', $pprint_array);
        exit();
}
// #### IMPORT FEEDBACK ########################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_import-feedback' AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
{
        $area_title = '{_import_feedback}';
        $page_title = SITE_NAME . ' - {_import_feedback}';
	$error = '';
	$md5user = md5($_SESSION['ilancedata']['user']['userid']);
	$data = array(
		'fb_ebay' => '-',
		'dv_ebay' => '{_never}',
		'id_ebay' => '',
		'fb_yahoo' => '-',
		'dv_yahoo' => '{_never}',
		'id_yahoo' => '',
		'fb_emarket' => '-',
		'dv_emarket' => '{_never}',
		'id_emarket' => '',
		'fb_bonanzle' => '-',
		'dv_bonanzle' => '{_never}',
		'id_bonanzle' => '',
		'fb_etsy' => '-',
		'dv_etsy' => '{_never}',
		'id_etsy' => '',
		'fb_ioffer' => '-',
		'dv_ioffer' => '{_never}',
		'id_ioffer' => '',
		'fb_overstock' => '-',
		'dv_overstock' => '{_never}',
		'id_overstock' => '',
		'fb_ricardo' => '-',
		'dv_ricardo' => '{_never}',
		'id_ricardo' => '',
		'fb_amazon' => '-',
		'dv_amazon' => '{_never}',
		'id_amazon' => '',
		'fb_ebid' => '-',
		'dv_ebid' => '{_never}',
		'id_ebid' => '',
		'fb_ebidus' => '-',
		'dv_ebidus' => '{_never}',
		'id_ebidus' => '');
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "feedback_import
		WHERE userid = '" . $_SESSION['ilancedata']['user']['userid'] . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$res['dv_ebay'] = (($res['dv_ebay'] != '0000-00-00 00:00:00') ? print_date($res['dv_ebay']) : '{_never}');
			$res['dv_yahoo'] = (($res['dv_yahoo'] != '0000-00-00 00:00:00') ? print_date($res['dv_yahoo']) : '{_never}');
			$res['dv_emarket'] = (($res['dv_emarket'] != '0000-00-00 00:00:00') ? print_date($res['dv_emarket']) : '{_never}');
			$res['dv_bonanzle'] = (($res['dv_bonanzle'] != '0000-00-00 00:00:00') ? print_date($res['dv_bonanzle']) : '{_never}');
			$res['dv_etsy'] = (($res['dv_etsy'] != '0000-00-00 00:00:00') ? print_date($res['dv_etsy']) : '{_never}');
			$res['dv_ioffer'] = (($res['dv_ioffer'] != '0000-00-00 00:00:00') ? print_date($res['dv_ioffer']) : '{_never}');
			$res['dv_overstock'] = (($res['dv_overstock'] != '0000-00-00 00:00:00') ? print_date($res['dv_overstock']) : '{_never}');
			$res['dv_ricardo'] = (($res['dv_ricardo'] != '0000-00-00 00:00:00') ? print_date($res['dv_ricardo']) : '{_never}');
			$res['dv_amazon'] = (($res['dv_amazon'] != '0000-00-00 00:00:00') ? print_date($res['dv_amazon']) : '{_never}');
			$res['dv_ebid'] = (($res['dv_ebid'] != '0000-00-00 00:00:00') ? print_date($res['dv_ebid']) : '{_never}');
			$res['dv_ebidus'] = (($res['dv_ebidus'] != '0000-00-00 00:00:00') ? print_date($res['dv_ebidus']) : '{_never}');
			$res['id_ebay'] = (($res['id_ebay'] != '') ? handle_input_keywords($res['id_ebay']) : '');
			$res['id_yahoo'] = (($res['id_yahoo'] != '') ? handle_input_keywords($res['id_yahoo']) : '');
			$res['id_emarket'] = (($res['id_emarket'] != '') ? handle_input_keywords($res['id_emarket']) : '');
			$res['id_bonanzle'] = (($res['id_bonanzle'] != '') ? handle_input_keywords($res['id_bonanzle']) : '');
			$res['id_etsy'] = (($res['id_etsy'] != '') ? handle_input_keywords($res['id_etsy']) : '');
			$res['id_ioffer'] = (($res['id_ioffer'] != '') ? handle_input_keywords($res['id_ioffer']) : '');
			$res['id_overstock'] = (($res['id_overstock'] != '') ? handle_input_keywords($res['id_overstock']) : '');
			$res['id_ricardo'] = (($res['id_ricardo'] != '') ? handle_input_keywords($res['id_ricardo']) : '');
			$res['id_amazon'] = (($res['id_amazon'] != '') ? handle_input_keywords($res['id_amazon']) : '');
			$res['id_ebid'] = (($res['id_ebid'] != '') ? handle_input_keywords($res['id_ebid']) : '');
			$res['id_ebidus'] = (($res['id_ebidus'] != '') ? handle_input_keywords($res['id_ebidus']) : '');
			$res['fb_ebay'] = (($res['fb_ebay'] > 0) ? intval($res['fb_ebay']) : '-');
			$res['fb_yahoo'] = (($res['fb_yahoo'] > 0) ? intval($res['fb_yahoo']) : '-');
			$res['fb_emarket'] = (($res['fb_emarket'] > 0) ? intval($res['fb_emarket']) : '-');
			$res['fb_bonanzle'] = (($res['fb_bonanzle'] > 0) ? intval($res['fb_bonanzle']) : '-');
			$res['fb_etsy'] = (($res['fb_etsy'] > 0) ? intval($res['fb_etsy']) : '-');
			$res['fb_ioffer'] = (($res['fb_ioffer'] > 0) ? intval($res['fb_ioffer']) : '-');
			$res['fb_overstock'] = (($res['fb_overstock'] > 0) ? intval($res['fb_overstock']) : '-');
			$res['fb_ricardo'] = (($res['fb_ricardo'] > 0) ? intval($res['fb_ricardo']) : '-');
			$res['fb_amazon'] = (($res['fb_amazon'] > 0) ? intval($res['fb_amazon']) : '-');
			$res['fb_ebid'] = (($res['fb_ebid'] > 0) ? intval($res['fb_ebid']) : '-');
			$res['fb_ebidus'] = (($res['fb_ebidus'] > 0) ? intval($res['fb_ebidus']) : '-');
			$data = $res;
		}
	}
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'process')
	{
		### process form for eBay
		if ($ilance->GPC['import'] == "ebay" AND $ilance->GPC['ebayusername'] > "")
		{ 
			$code = $_SESSION['ilancedata']['user']['userid'];
			$result = $ilance->feedback_import->verify_ebay($ilance->GPC['ebayusername'], $code);
			if ($result == "0") { $error = "The verification code we issued you is not found on your eBay AboutME Page!"; }
			if ($result == "1") { $error = "We are unable to connect to eBay at this time. Please try again later!"; }
			if ($result == "2")
			{
				$fbscore = $ilance->feedback_import->get_ebay_feedback_score($ilance->GPC['ebayusername']); 
				$error = "Congratulations, we have successfully imported your eBay feedback score of: <strong>$fbscore</strong>.";
				$exist = $ilance->db->query("SELECT id FROM " . DB_PREFIX . "feedback_import WHERE userid = '" . $_SESSION['ilancedata']['user']['userid'] . "'");
				if ($ilance->db->num_rows($exist) > 0)
				{
					$ilance->db->query("UPDATE " . DB_PREFIX . "feedback_import SET fb_ebay = '" . $ilance->db->escape_string($fbscore) . "', dv_ebay = '" . DATETIME24H . "', id_ebay = '" . $ilance->db->escape_string($ilance->GPC['ebayusername']) . "' WHERE userid = '" . $_SESSION['ilancedata']['user']['userid'] . "'");
				}
				else
				{
					$ilance->db->query("INSERT INTO " . DB_PREFIX . "feedback_import (userid, fb_ebay, dv_ebay, id_ebay) VALUES ('" . $_SESSION['ilancedata']['user']['userid'] . "','" . $ilance->db->escape_string($fbscore) . "','" . DATETIME24H . "','" . $ilance->db->escape_string($ilance->GPC['ebayusername']) . "')");
				}
			}
		}
		### process form for Yahoo
		if ($ilance->GPC['import'] == "yahoo" AND $ilance->GPC['yahoousername'] > "")
		{ 
			$code = $_SESSION['ilancedata']['user']['userid'];
			$result = $ilance->feedback_import->verify_yahoo($ilance->GPC['yahoousername'], $code);
			if ($result == "0") { $error = "The verification code we issued you is not found on your Yahoo AboutME Page!"; }
			if ($result == "1") { $error = "We are unable to connect to Yahoo at this time. Please try again later!"; }
			if ($result == "2")
			{
				$fbscore = $ilance->feedback_import->get_yahoo_score($ilance->GPC['yahoousername']); 
				$error = "Congratulations, we have successfully imported your Yahoo feedback score of: <strong>$fbscore</strong>.";
				$exist = $ilance->db->query("SELECT id FROM " . DB_PREFIX . "feedback_import WHERE userid = '" . $_SESSION['ilancedata']['user']['userid'] . "'");
				if ($ilance->db->num_rows($exist) > 0)
				{
					$ilance->db->query("UPDATE " . DB_PREFIX . "feedback_import SET fb_yahoo = '" . $ilance->db->escape_string($fbscore) . "', dv_yahoo = '" . DATETIME24H . "', id_yahoo = '" . $ilance->db->escape_string($ilance->GPC['yahoousername']) . "' WHERE userid = '" . $_SESSION['ilancedata']['user']['userid'] . "'");
				}
				else
				{
					$ilance->db->query("INSERT INTO " . DB_PREFIX . "feedback_import (userid, fb_yahoo, dv_yahoo, id_yahoo) VALUES ('" . $_SESSION['ilancedata']['user']['userid'] . "','" . $ilance->db->escape_string($fbscore) . "','" . DATETIME24H . "','" . $ilance->db->escape_string($ilance->GPC['yahoousername']) . "')");
				}
			}
		}
		### process form for emarketGR
		if ($ilance->GPC['import'] == "emarket" AND $ilance->GPC['emarketusername'] > "")
		{ 
			$code = $_SESSION['ilancedata']['user']['userid'];
			$result = $ilance->feedback_import->verify_emarketGR($ilance->GPC['emarketusername'], $code);
			if ($result == "0") { $error = "The verification code we issued you is not found on your Emarket.gr AboutME Page!"; }
			if ($result == "1") { $error = "We are unable to connect to Emarket.gr at this time. Please try again later!"; }
			if ($result == "2")
			{
				$fbscore = $ilance->feedback_import->get_emarketGR_score($ilance->GPC['emarketusername']); 
				$error = "Congratulations, we have successfully imported your Emarket.gr feedback score of: <strong>$fbscore</strong>.";
				$exist = $ilance->db->query("SELECT id FROM " . DB_PREFIX . "feedback_import WHERE userid = '" . $_SESSION['ilancedata']['user']['userid'] . "'");
				if ($ilance->db->num_rows($exist) > 0)
				{
					$ilance->db->query("UPDATE " . DB_PREFIX . "feedback_import SET fb_emarket = '" . $ilance->db->escape_string($fbscore) . "', dv_emarket = '" . DATETIME24H . "', id_emarket = '" . $ilance->db->escape_string($ilance->GPC['emarketusername']) . "' WHERE userid = '" . $_SESSION['ilancedata']['user']['userid'] . "'");
				}
				else
				{
					$ilance->db->query("INSERT INTO " . DB_PREFIX . "feedback_import (userid, fb_emarket, dv_emarket, id_emarket) VALUES ('" . $_SESSION['ilancedata']['user']['userid'] . "','" . $ilance->db->escape_string($fbscore) . "','" . DATETIME24H . "','" . $ilance->db->escape_string($ilance->GPC['emarketusername']) . "')");
				}
			}
		}
		### process form for Bonanzle
		if ($ilance->GPC['import'] == "bonanzle" AND $ilance->GPC['bonanzleusername'] > "")
		{ 
			$code = $_SESSION['ilancedata']['user']['userid'];
			$result = $ilance->feedback_import->verify_bonanzle($ilance->GPC['bonanzleusername'], $code);
			if ($result == "0") { $error = "The verification code we issued you is not found on your Bonanzle Booth Details Page!"; }
			if ($result == "1") { $error = "We are unable to connect to Bonanzle at this time. Please try again later!"; }
			if ($result == "2")
			{
				$fbscore = $ilance->feedback_import->get_bonanzle_score($ilance->GPC['bonanzleusername']); 
				$error = "Congratulations, we have successfully imported your Bonanzle feedback score of: <strong>$fbscore</strong>.";
				$exist = $ilance->db->query("SELECT id FROM " . DB_PREFIX . "feedback_import WHERE userid = '" . $_SESSION['ilancedata']['user']['userid'] . "'");
				if ($ilance->db->num_rows($exist) > 0)
				{
					$ilance->db->query("UPDATE " . DB_PREFIX . "feedback_import SET fb_bonanzle = '" . $ilance->db->escape_string($fbscore) . "', dv_bonanzle = '" . DATETIME24H . "', id_bonanzle = '" . $ilance->db->escape_string($ilance->GPC['bonanzleusername']) . "' WHERE userid = '" . $_SESSION['ilancedata']['user']['userid'] . "'");
				}
				else
				{
					$ilance->db->query("INSERT INTO " . DB_PREFIX . "feedback_import (userid, fb_bonanzle, dv_bonanzle, id_bonanzle) VALUES ('" . $_SESSION['ilancedata']['user']['userid'] . "','" . $ilance->db->escape_string($fbscore) . "','" . DATETIME24H . "','" . $ilance->db->escape_string($ilance->GPC['bonanzleusername']) . "')");
				}
			}
		}
		### process form for Etsy
		if ($ilance->GPC['import'] == "etsy" AND $ilance->GPC['etsyusername'] > "")
		{ 
			$code = $_SESSION['ilancedata']['user']['userid'];
			$result = $ilance->feedback_import->verify_etsy($ilance->GPC['etsyusername'], $code);
			if ($result == "0") { $error = "The verification code we issued you is not found on your Etsy Shop Accouncement Page!"; }
			if ($result == "1") { $error = "We are unable to connect to Etsy at this time. Please try again later!"; }
			if ($result == "2")
			{
				$fbscore = $ilance->feedback_import->get_etsy_score($ilance->GPC['etsyusername']); 
				$error = "Congratulations, we have successfully imported your Etsy feedback score of: <strong>$fbscore</strong>.";
				$exist = $ilance->db->query("SELECT id FROM " . DB_PREFIX . "feedback_import WHERE userid = '" . $_SESSION['ilancedata']['user']['userid'] . "'");
				if ($ilance->db->num_rows($exist) > 0)
				{
					$ilance->db->query("UPDATE " . DB_PREFIX . "feedback_import SET fb_etsy = '" . $ilance->db->escape_string($fbscore) . "', dv_etsy = '" . DATETIME24H . "', id_etsy = '" . $ilance->db->escape_string($ilance->GPC['etsyusername']) . "' WHERE userid = '" . $_SESSION['ilancedata']['user']['userid'] . "'");
				}
				else
				{
					$ilance->db->query("INSERT INTO " . DB_PREFIX . "feedback_import (userid, fb_etsy, dv_etsy, id_etsy) VALUES ('" . $_SESSION['ilancedata']['user']['userid'] . "','" . $ilance->db->escape_string($fbscore) . "','" . DATETIME24H . "','" . $ilance->db->escape_string($ilance->GPC['etsyusername']) . "')");
				}
			}
		}
		### process form for iOffer
		if ($ilance->GPC['import'] == "ioffer" AND $ilance->GPC['iofferusername'] > "")
		{ 
			$code = $_SESSION['ilancedata']['user']['userid'];
			$result = $ilance->feedback_import->verify_ioffer($ilance->GPC['iofferusername'], $code);
			if ($result == "1") { $error = "We are unable to connect to iOffer at this time. Please try again later!"; }
			if ($result == "0")
			{
				$fbscore = $ilance->feedback_import->get_ioffer_score($ilance->GPC['iofferusername']); 
				$error = "Congratulations, we have successfully imported your iOffer feedback score of: <strong>$fbscore</strong>.";
				$exist = $ilance->db->query("SELECT id FROM " . DB_PREFIX . "feedback_import WHERE userid = '" . $_SESSION['ilancedata']['user']['userid'] . "'");
				if ($ilance->db->num_rows($exist) > 0)
				{
					$ilance->db->query("UPDATE " . DB_PREFIX . "feedback_import SET fb_ioffer = '" . $ilance->db->escape_string($fbscore) . "', dv_ioffer = '" . DATETIME24H . "', id_ioffer = '" . $ilance->db->escape_string($ilance->GPC['iofferusername']) . "' WHERE userid = '" . $_SESSION['ilancedata']['user']['userid'] . "'");
				}
				else
				{
					$ilance->db->query("INSERT INTO " . DB_PREFIX . "feedback_import (userid, fb_ioffer, dv_ioffer, id_ioffer) VALUES ('" . $_SESSION['ilancedata']['user']['userid'] . "','" . $ilance->db->escape_string($fbscore) . "','" . DATETIME24H . "','" . $ilance->db->escape_string($ilance->GPC['iofferusername']) . "')");
				}
			}
		}
		### process form for Overstock
		if ($ilance->GPC['import'] == "overstock" AND $ilance->GPC['overstockusername'] > "")
		{ 
			$code = $_SESSION['ilancedata']['user']['userid'];
			$result = $ilance->feedback_import->verify_overstock($ilance->GPC['overstockusername'], $code);
			if ($result == "0") { $error = "The verification code we issued you is not found on your Overstock Home Page!"; }
			if ($result == "1") { $error = "We are unable to connect to Overstock at this time. Please try again later!"; }
			if ($result == "2")
			{
				$fbscore = $ilance->feedback_import->get_overstock_score($ilance->GPC['overstockusername']); 
				$error = "Congratulations, we have successfully imported your Overstock feedback score of: <strong>$fbscore</strong>.";
				$exist = $ilance->db->query("SELECT id FROM " . DB_PREFIX . "feedback_import WHERE userid = '" . $_SESSION['ilancedata']['user']['userid'] . "'");
				if ($ilance->db->num_rows($exist) > 0)
				{
					$ilance->db->query("UPDATE " . DB_PREFIX . "feedback_import SET fb_overstock = '" . $ilance->db->escape_string($fbscore) . "', dv_overstock = '" . DATETIME24H . "', id_overstock = '" . $ilance->db->escape_string($ilance->GPC['overstockusername']) . "' WHERE userid = '" . $_SESSION['ilancedata']['user']['userid'] . "'");
				}
				else
				{
					$ilance->db->query("INSERT INTO " . DB_PREFIX . "feedback_import (userid, fb_overstock, dv_overstock, id_overstock) VALUES ('" . $_SESSION['ilancedata']['user']['userid'] . "','" . $ilance->db->escape_string($fbscore) . "','" . DATETIME24H . "','" . $ilance->db->escape_string($ilance->GPC['overstockusername']) . "')");
				}
			}
		}
		### process form for Ricardo
		if ($ilance->GPC['import'] == "ricardo" AND $ilance->GPC['ricardousername'] > "")
		{ 
			$code = $_SESSION['ilancedata']['user']['userid'];
			$result = $ilance->feedback_import->verify_ricardo($ilance->GPC['ricardousername'], $code);
			if ($result == "0") { $error = "The verification code we issued you is not found on your Ricardo Page!"; }
			if ($result == "1") { $error = "We are unable to connect to Ricardo at this time. Please try again later!"; }
			if ($result == "2")
			{
				$fbscore = $ilance->feedback_import->get_ricardo_score($ilance->GPC['ricardousername']); 
				$error = "Congratulations, we have successfully imported your Ricardo feedback score of: <strong>$fbscore</strong>.";
				$exist = $ilance->db->query("SELECT id FROM " . DB_PREFIX . "feedback_import WHERE userid = '" . $_SESSION['ilancedata']['user']['userid'] . "'");
				if ($ilance->db->num_rows($exist) > 0)
				{
					$ilance->db->query("UPDATE " . DB_PREFIX . "feedback_import SET fb_ricardo = '" . $ilance->db->escape_string($fbscore) . "', dv_ricardo = '" . DATETIME24H . "', id_ricardo = '" . $ilance->db->escape_string($ilance->GPC['ricardousername']) . "' WHERE userid = '" . $_SESSION['ilancedata']['user']['userid'] . "'");
				}
				else
				{
					$ilance->db->query("INSERT INTO " . DB_PREFIX . "feedback_import (userid, fb_ricardo, dv_ricardo, id_ricardo) VALUES ('" . $_SESSION['ilancedata']['user']['userid'] . "','" . $ilance->db->escape_string($fbscore) . "','" . DATETIME24H . "','" . $ilance->db->escape_string($ilance->GPC['ricardousername']) . "')");
				}
			}
		}
		### process form for Amazon
		if ($ilance->GPC['import'] == "amazon" AND $ilance->GPC['amazonusername'] > "")
		{ 
			$code = $_SESSION['ilancedata']['user']['userid'];
			$result = $ilance->feedback_import->verify_amazon($ilance->GPC['amazonusername'], $code);
			if ($result == "0") { $error = "The verification code we issued you is not found on your Amazon Store Page!"; }
			if ($result == "1") { $error = "We are unable to connect to Amazon at this time. Please try again later!"; }
			if ($result == "2")
			{
				$fbscore = $ilance->feedback_import->get_amazon_score($ilance->GPC['amazonusername']); 
				$error = "Congratulations, we have successfully imported your Amazon feedback score of: <strong>$fbscore</strong>.";
				$exist = $ilance->db->query("SELECT id FROM " . DB_PREFIX . "feedback_import WHERE userid = '" . $_SESSION['ilancedata']['user']['userid'] . "'");
				if ($ilance->db->num_rows($exist) > 0)
				{
					$ilance->db->query("UPDATE " . DB_PREFIX . "feedback_import SET fb_amazon = '" . $ilance->db->escape_string($fbscore) . "', dv_amazon = '" . DATETIME24H . "', id_amazon='" . $ilance->db->escape_string($ilance->GPC['amazonusername']) . "' WHERE userid = '" . $_SESSION['ilancedata']['user']['userid'] . "'");
				}
				else
				{
					$ilance->db->query("INSERT INTO " . DB_PREFIX . "feedback_import (userid, fb_amazon, dv_amazon, id_amazon) VALUES ('" . $_SESSION['ilancedata']['user']['userid'] . "','" . $ilance->db->escape_string($fbscore) . "','" . DATETIME24H . "','" . $ilance->db->escape_string($ilance->GPC['amazonusername']) . "')");
				}
			}
		}
		### process form for EBid.uk
		if ($ilance->GPC['import'] == "ebid" AND $ilance->GPC['ebidusername'] > "")
		{ 
			$code = $_SESSION['ilancedata']['user']['userid'];
			$result = $ilance->feedback_import->verify_ebid($ilance->GPC['ebidusername'], $code);
			if ($result == "0") { $error = "The verification code we issued you is not found on your eBid All about Page!"; }
			if ($result == "1") { $error = "We are unable to connect to eBid at this time. Please try again later!"; }
			if ($result == "2")
			{
				$fbscore = $ilance->feedback_import->get_ebid_score($ilance->GPC['ebidusername']); 
				$error = "Congratulations, we have successfully imported your eBid feedback score of: <strong>$fbscore</strong>.";
				$exist = $ilance->db->query("SELECT id FROM " . DB_PREFIX . "feedback_import WHERE userid = '" . $_SESSION['ilancedata']['user']['userid'] . "'");
				if ($ilance->db->num_rows($exist) > 0)
				{
					$ilance->db->query("UPDATE " . DB_PREFIX . "feedback_import SET fb_ebid = '" . $ilance->db->escape_string($fbscore) . "', dv_ebid = '" . DATETIME24H . "', id_ebid = '" . $ilance->db->escape_string($ilance->GPC['ebidusername']) . "' WHERE userid = '" . $_SESSION['ilancedata']['user']['userid'] . "'");
				}
				else
				{
					$ilance->db->query("INSERT INTO " . DB_PREFIX . "feedback_import (userid, fb_ebid, dv_ebid, id_ebid) VALUES ('" . $_SESSION['ilancedata']['user']['userid'] . "','" . $ilance->db->escape_string($fbscore) . "','" . DATETIME24H . "','" . $ilance->db->escape_string($ilance->GPC['ebidusername']) . "')");
				}
			}
		}
		### process form for EBid.us
		if ($ilance->GPC['import'] == "ebid" AND $ilance->GPC['ebidususername'] > "")
		{ 
			$code = $_SESSION['ilancedata']['user']['userid'];
			$result = $ilance->feedback_import->verify_ebidus($ilance->GPC['ebidususername'], $code);
			if ($result == "0") { $error = "The verification code we issued you is not found on your eBid All about Page!"; }
			if ($result == "1") { $error = "We are unable to connect to eBid at this time. Please try again later!"; }
			if ($result == "2")
			{
				$fbscore = $ilance->feedback_import->get_ebid_scoreus($ilance->GPC['ebidususername']); 
				$error = "Congratulations, we have successfully imported your eBid feedback score of: <strong>$fbscore</strong>.";
				$exist = $ilance->db->query("SELECT id FROM " . DB_PREFIX . "feedback_import WHERE userid = '" . $_SESSION['ilancedata']['user']['userid'] . "'");
				if ($ilance->db->num_rows($exist) > 0)
				{
					$ilance->db->query("UPDATE " . DB_PREFIX . "feedback_import SET fb_ebidus = '" . $ilance->db->escape_string($fbscore) . "', dv_ebidus = '" . DATETIME24H . "', id_ebidus = '" . $ilance->db->escape_string($ilance->GPC['ebidususername']) . "' WHERE userid = '" . $_SESSION['ilancedata']['user']['userid'] . "'");
				}
				else
				{
					$ilance->db->query("INSERT INTO " . DB_PREFIX . "feedback_import (userid, fb_ebidus, dv_ebidus, id_ebidus) VALUES ('" . $_SESSION['ilancedata']['user']['userid'] . "','" . $ilance->db->escape_string($fbscore) . "','" . DATETIME24H . "','" . $ilance->db->escape_string($ilance->GPC['ebidususername']) . "')");
				}
			}
		}
	}
        $pprint_array = array('md5user','error');
        $ilance->template->fetch('main', 'feedback_import.html');
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage, 'data' => $data));
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', $pprint_array);
        exit();
}
// #### LEAVE FEEDBACK HANDLER #################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-feedback-submit' AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
{
	$area_title = '{_feedback_and_rating_submit_process}';
	$page_title = SITE_NAME . ' - {_feedback_and_rating_submit_process}';
        // #### begin feedback save ############################################
        $ilance->GPC['response'] = ((isset($ilance->GPC['response']) AND is_array($ilance->GPC['response'])) ? $ilance->GPC['response'] : array());
        $ilance->GPC['comments'] = ((isset($ilance->GPC['comments']) AND is_array($ilance->GPC['comments'])) ? $ilance->GPC['comments'] : array());
        $ilance->GPC['criteria'] = ((isset($ilance->GPC['criteria']) AND is_array($ilance->GPC['criteria'])) ? $ilance->GPC['criteria'] : array());
        $ilance->GPC['for_user_id'] = ((isset($ilance->GPC['for_user_id']) AND is_array($ilance->GPC['for_user_id'])) ? $ilance->GPC['for_user_id'] : array());
        $ilance->GPC['from_user_id'] = ((isset($ilance->GPC['from_user_id']) AND is_array($ilance->GPC['from_user_id'])) ? $ilance->GPC['from_user_id'] : array());
        $ilance->GPC['fromtype'] = ((isset($ilance->GPC['fromtype']) AND is_array($ilance->GPC['fromtype'])) ? $ilance->GPC['fromtype'] : array());
	$ilance->GPC['orderids'] = ((isset($ilance->GPC['orderids']) AND is_array($ilance->GPC['orderids'])) ? $ilance->GPC['orderids'] : array());
        // #### skip listings where feedback will be left later ################
        $pids = array();
        $success = false;
        foreach ($ilance->GPC['response'] AS $project_id => $value)
        {
                if ($value != 'later')
                {
                        $pids[] = $project_id;
                }
        }
	// #### submit multiple feedback ratings ###############################
        foreach ($pids AS $project_id)
        {
		$pid = explode('_', $project_id);
                $success = $ilance->feedback_rating->insert_feedback_rating($pid[0], (isset($ilance->GPC['orderids']["$project_id"]) ? $ilance->GPC['orderids']["$project_id"] : 0), $ilance->GPC['for_user_id']["$project_id"], $ilance->GPC['from_user_id']["$project_id"], (isset($ilance->GPC['criteria']["$project_id"]) ? $ilance->GPC['criteria']["$project_id"] : array()), $ilance->GPC['comments']["$project_id"], $ilance->GPC['fromtype']["$project_id"], $ilance->GPC['response']["$project_id"]);
        }
        if ($success)
        {
		if (isset($ilance->GPC['returnurl']) AND !empty($ilance->GPC['returnurl']))
		{
			refresh('', $ilance->GPC['returnurl']);
		}
		else
		{
			//print_notice('{_feedback_and_rating_complete}', '{_thank_you_for_taking_a_few_moments_to_rate_this_customer_and_provide_feedback_for_others_to_review}<br /><br />{_please_contact_customer_support}', $ilpage['main'] . '?cmd=cp', '{_my_cp}');
			header('Location: ' . HTTP_SERVER . $ilpage['feedback'] . '?cmd=complete');
		}
                exit();
        }
        else
        {
		if (isset($ilance->GPC['returnurl']) AND !empty($ilance->GPC['returnurl']))
		{
			refresh('', $ilance->GPC['returnurl']);
		}
		else
		{
			print_notice('{_leave_feedback}', '{_you_have_chosen_to_leave_feedback_later_for_one_or_more_listings}', $ilpage['feedback'] . '?cmd=_leave-feedback', '{_leave_feedback}');
		}
                exit();
        }
}
else
{
	refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['feedback'] . print_hidden_fields($string = true, $excluded = array(), $questionmarkfirst = true)));
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>