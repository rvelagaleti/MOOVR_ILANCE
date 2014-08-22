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
		'inline_edit',
		'jquery',
		'modal'
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
        'portfolio'
);

// #### setup script location ##################################################
define('LOCATION', 'portfolio');

// #### require backend ########################################################
require_once('./functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[portfolio]" => $ilcrumbs["$ilpage[portfolio]"]);

if (isset($ilconfig['portfoliodisplay_enabled']) AND $ilconfig['portfoliodisplay_enabled'] == 0)
{
	print_notice('{_disabled}', '{_were_sorry_this_feature_is_currently_disabled}', $ilpage['main'], '{_main_menu}');
	exit();
}

// #### PREPARE DEFAULT URLS ###########################################
$scriptpage = $ilpage['portfolio'] . print_hidden_fields($string = true, $excluded = array('page'), $questionmarkfirst = true);

$php_self = $scriptpage;
$php_self_urlencoded = urlencode($php_self);
define('PHP_SELF', HTTP_SERVER . $php_self);
$show['widescreen'] = true;
$headinclude .= "\t<script type=\"text/javascript\" src=\"" . $ilconfig['template_relativeimagepath'] . "functions/javascript/functions_trail" . (($ilconfig['globalfilters_jsminify']) ? '.min' : '') . ".js\"></script>\n";
/*$headinclude .= "
<script type=\"text/javascript\">
<!--
var message = '';
function clickIE() 
{
	if (document.all) 
	{
		(message);return false;
	}
}
function clickNS(e) 
{
	if (document.layers||(document.getElementById&&!document.all)) 
	{
		if (e.which==2||e.which==3) 
		{
			(message);
			return false;
		}
	}
}
if (document.layers)
{
	document.captureEvents(Event.MOUSEDOWN);document.onmousedown=clickNS;
}
else
{
	document.onmouseup=clickNS;document.oncontextmenu=clickIE;
}
document.oncontextmenu=new Function('return false')\n
//-->
</script>";*/
$categorycache = $ilance->categories->build_array('service', $_SESSION['ilancedata']['user']['slng'], 1, true);
// #### user deleting portfolio items ##########################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'manage-portfolio')
{
	if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'addportfolio') == 'no')
	{
                $area_title = '{_access_denied_to_portfolio_resources}';
		$page_title = SITE_NAME . ' - {_access_denied_to_portfolio_resources}';
		print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource}'." <a href='".$ilpage['subscription']."'><strong>".'{_click_here}'."</strong></a>", $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('addportfolio'));
		exit();
        }
	// #### remove one or more portfolio attachments #######################
        if (isset($ilance->GPC['attachid']) AND $ilance->GPC['attachid'] != '' AND is_array($ilance->GPC['attachid']) AND count($ilance->GPC['attachid']) > 0)
        {
		($apihook = $ilance->api('manage_portfolio_remove_attachment_start')) ? eval($apihook) : false;
		
                foreach ($ilance->GPC['attachid'] AS $value)
                {
                        if (!empty($value) AND $value > 0)
                        {
				// #### delete from attachment table ###########
				$ilance->attachment->remove_attachment(intval($value), $_SESSION['ilancedata']['user']['userid']);
				
				// #### delete from portfolio table ############
				$ilance->db->query("
                                        DELETE FROM " . DB_PREFIX . "portfolio
                                        WHERE portfolio_id = '" . intval($value) . "'
                                            AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                ", 0, null, __FILE__, __LINE__);
				
				($apihook = $ilance->api('manage_portfolio_remove_attachment_foreach_end')) ? eval($apihook) : false;
                        }
                }
		
		($apihook = $ilance->api('manage_portfolio_remove_attachment_end')) ? eval($apihook) : false;
		
                refresh($ilpage['portfolio'] . '?cmd=management', HTTP_SERVER . $ilpage['portfolio'] . '?cmd=management');
                exit();
        }
}

// #### feature portfolio ######################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_feature-portfolio' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
        $show['widescreen'] = false;
	if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'addportfolio') == 'no')
	{
                $area_title = '{_access_denied_to_portfolio_resources}';
		$page_title = SITE_NAME . ' - {_access_denied_to_portfolio_resources}';
		print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource}'." <a href='".$ilpage['subscription']."'><strong>".'{_click_here}'."</strong></a>", $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('addportfolio'));
		exit();
        }
	if ($ilconfig['portfolioupsell_featuredactive'] == false)
        {
		$area_title = '{_invalid_portfolio_menu}';
		$page_title = SITE_NAME . ' - {_invalid_portfolio_menu}';
		print_notice('{_access_denied}', '{_were_sorry_this_feature_is_currently_disabled}', 'javascript:history.back(1);', '{_back}');
		exit();
	}
        $area_title = '{_featured_portfolio_order_menu}';
        $page_title = SITE_NAME . ' - {_featured_portfolio_order_menu}';
	$navcrumb = array();
	$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
	$navcrumb["$ilpage[portfolio]?cmd=management"] = '{_portfolio_manager}';
	$navcrumb[""] = '{_feature}';
        $sql = $ilance->db->query("
                SELECT portfolio_id, category_id
                FROM " . DB_PREFIX . "portfolio
                WHERE portfolio_id = '" . intval($ilance->GPC['id']) . "'
        ");
        if ($ilance->db->num_rows($sql) > 0)
        { 
                $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                $portfolio_id = intval($ilance->GPC['id']);
                $cid = $res['category_id'];
                $category = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $cid);            
                $payment_method_pulldown = $ilance->accounting_print->print_paymethod_pulldown('portfolio', 'account_id', $_SESSION['ilancedata']['user']['userid'], $javascript = '');
                $item_name = '<strong>' . stripslashes($ilance->db->fetch_field(DB_PREFIX . "attachment", "portfolio_id = '" . intval($ilance->GPC['id']) . "'", "filename")) . '</strong>';
                $qty = 1;
                $amount = 0;
                $amount_formatted = $ilance->currency->format(0);
                $duration = $ilconfig['portfolioupsell_featuredlength'];
                $show['istaxable'] = 0;
                if ($ilconfig['portfolioupsell_featuredactive'])
                {
			$duration = $ilconfig['portfolioupsell_featuredlength'];
			if ($ilconfig['portfolioupsell_featuredfee'] > 0)
			{
				$amount_formatted = $ilance->currency->format($ilconfig['portfolioupsell_featuredfee']);
				$total_formatted = $ilance->currency->format($ilconfig['portfolioupsell_featuredfee']);
				$amount = $ilconfig['portfolioupsell_featuredfee'];
				// find out if portfolios are taxable
				
				if ($ilance->tax->is_taxable($_SESSION['ilancedata']['user']['userid'], 'portfolio') == 1)
				{
					$show['istaxable'] = 1;
					$taxamount = $ilance->tax->fetch_amount($_SESSION['ilancedata']['user']['userid'], $amount, 'portfolio', 0);
					$tax_formatted = $ilance->currency->format($taxamount);
					$total_formatted = $ilance->currency->format($amount+$taxamount);
				}
			}
			else
			{
				$amount_formatted = '{_free}';
				$total_formatted = '{_free}';
				$amount = 0;
			}
                }
		$pprint_array = array('total_formatted','tax_formatted','cid','category','duration','amount_formatted','payment_method_pulldown','item_name','id','portfolio_id','amount','qty','topay','input_style');
                $ilance->template->fetch('main', 'portfolio_featured_order.html');
                $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                $ilance->template->parse_if_blocks('main');
                $ilance->template->pprint('main', $pprint_array);
                exit();
        }
        else
        {
                $area_title = '{_invalid_portfolio_menu}';
                $page_title = SITE_NAME . ' - {_invalid_portfolio_menu}';
                print_notice('{_invalid_portfolio_catalog}', '{_were_sorry_the_portfolio_catalog_for_this_customer_has_been_removed_or_is_currently_not_prepared_for_viewing}'." ".'{_please}'." <span class=\"blue\"><a href='".$ilpage['portfolio']."'>".'{_view_other_portfolios}'."</a></span> ".'{_here_a_few_reasons_you_might_be_seeing_this_page}'.":<br /><br /><li>".'{_portfolio_has_not_been_setup_or_created}'."</li><li>".'{_this_vendors_subscription_may_be_inactive}'."</li><li>".'{_this_vendor_may_be_suspended_or_removed_from_the_marketplace}'."</li>", 'javascript:history.back(1);', '{_back}');
                exit();
        }
}
// #### feature portfolio purchase preview #####################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_portfolio-purchase-preview' AND isset($ilance->GPC['portfolio_id']) AND $ilance->GPC['portfolio_id'] > 0 AND isset($ilance->GPC['qty']) AND $ilance->GPC['qty'] > 0)
{
        $show['widescreen'] = false;
	$portfolio_id = intval($ilance->GPC['portfolio_id']);
	if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'addportfolio') == 'no')
	{
		$area_title = '{_access_denied_to_portfolio_resources}';
		$page_title = SITE_NAME . ' - {_access_denied_to_portfolio_resources}';
		print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource}'." <a href='".$ilpage['subscription']."'><strong>".'{_click_here}'."</strong></a>", $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('addportfolio'));
		exit();	
	}
	if ($ilconfig['portfolioupsell_featuredactive'] == false)
	{
		$area_title = '{_invalid_portfolio_menu}';
		$page_title = SITE_NAME . ' - {_invalid_portfolio_menu}';
		print_notice('{_access_denied}', '{_were_sorry_this_feature_is_currently_disabled}', 'javascript:history.back(1);', '{_back}');
		exit();
	}
	$area_title = '{_featured_portfolio_preview_confirmation_menu}';
	$page_title = SITE_NAME . ' - {_featured_portfolio_preview_confirmation_menu}';
	$sql = $ilance->db->query("
		SELECT portfolio_id, category_id
		FROM " . DB_PREFIX . "portfolio
		WHERE portfolio_id = '" . intval($ilance->GPC['portfolio_id']) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		$cid = $res['category_id'];
		$category = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $cid);
		$amount_formatted = $ilance->currency->format(0);
		$amount = '0.00';
		$duration = $ilconfig['portfolioupsell_featuredlength'];
		$show['istaxable'] = 0;
		if ($ilconfig['portfolioupsell_featuredactive'])
		{
			if ($ilconfig['portfolioupsell_featuredfee'] > 0)
			{
				$amount_formatted = $ilance->currency->format($ilconfig['portfolioupsell_featuredfee']);
				$total_formatted = $ilance->currency->format($ilconfig['portfolioupsell_featuredfee']);
				$amount = $ilconfig['portfolioupsell_featuredfee'];	
				$duration = $ilconfig['portfolioupsell_featuredlength'];
				$total = $amount;
				// find out if portfolios are taxable
				
				if ($ilance->tax->is_taxable($_SESSION['ilancedata']['user']['userid'], 'portfolio') == 1)
				{
					$show['istaxable'] = 1;
					$taxamount = $ilance->tax->fetch_amount($_SESSION['ilancedata']['user']['userid'], $amount, 'portfolio', 0);
					$tax_formatted = $ilance->currency->format($taxamount);
					$total_formatted = $ilance->currency->format($amount+$taxamount);
					$total = $amount+$taxamount;
				}
			}
			else
			{
				$amount_formatted = '{_free}';
				$total_formatted = '{_free}';
				$amount = 0;	
				$duration = $ilconfig['portfolioupsell_featuredlength'];
				$total = $amount;
			}
		}
		$qty = intval($ilance->GPC['qty']);
		$item_name = '<strong>' . stripslashes($ilance->db->fetch_field(DB_PREFIX . "attachment", "portfolio_id = '" . intval($ilance->GPC['portfolio_id']) . "'", "filename")) . '</strong>';
		$pprint_array = array('total','total_formatted','tax_formatted','duration','category','amount_formatted','payment_method_pulldown','item_name','id','portfolio_id','amount','qty','topay','input_style');
		$ilance->template->fetch('main', 'portfolio_featured_preview.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	else
	{
		$area_title = '{_invalid_portfolio_menu}';
		$page_title = SITE_NAME . ' - {_invalid_portfolio_menu}';
		print_notice('{_invalid_portfolio_catalog}', '{_were_sorry_the_portfolio_catalog_for_this_customer_has_been_removed_or_is_currently_not_prepared_for_viewing}'." ".'{_please}'." <span class=\"blue\"><a href='".$ilpage['portfolio']."'>".'{_view_other_portfolios}'."</a></span> ".'{_here_a_few_reasons_you_might_be_seeing_this_page}'.":<br /><br /><li>".'{_portfolio_has_not_been_setup_or_created}'."</li><li>".'{_this_vendors_subscription_may_be_inactive}'."</li><li>".'{_this_vendor_may_be_suspended_or_removed_from_the_marketplace}'."</li>", 'javascript:history.back(1);', '{_back}');
		exit();
	}
}
// #### feature portfolio purchase handler #####################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_portfolio-purchase-process' AND isset($ilance->GPC['portfolio_id']) AND $ilance->GPC['portfolio_id'] > 0  AND isset($ilance->GPC['qty']) AND $ilance->GPC['qty'] > 0  AND isset($ilance->GPC['amount']) AND isset($ilance->GPC['total']))
{
	if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'addportfolio') == 'no')
	{
		$area_title = '{_access_denied_to_portfolio_resources}';
		$page_title = SITE_NAME . ' - {_access_denied_to_portfolio_resources}';
		print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource}'." <a href='".$ilpage['subscription']."'><strong>".'{_click_here}'."</strong></a>", $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('addportfolio'));
		exit();
	}
	if ($ilconfig['portfolioupsell_featuredactive'] == false)
	{
		$area_title = '{_invalid_portfolio_menu}';
		$page_title = SITE_NAME . ' - {_invalid_portfolio_menu}';
		print_notice('{_access_denied}', '{_were_sorry_this_feature_is_currently_disabled}', 'javascript:history.back(1);', '{_back}');
		exit();
	}
	$sql = $ilance->db->query("
		SELECT portfolio_id
		FROM " . DB_PREFIX . "portfolio
		WHERE portfolio_id = '" . intval($ilance->GPC['portfolio_id']) . "'
		    AND visible = '1'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$area_title = '{_featured_portfolio_payment_process}';
		$page_title = SITE_NAME . ' - {_featured_portfolio_payment_process}';
		// we will pass the total amount before taxes and let the account system determine tax settings
		// for the users that will be making payments
		$successful = $ilance->portfolio->portfolio_process($_SESSION['ilancedata']['user']['userid'], $ilance->GPC['portfolio_id'], $ilance->GPC['amount'], $ilance->GPC['total']);
		if ($successful)
		{
			$area_title = '{_invoice_payment_complete_menu}';
			$page_title = SITE_NAME . ' - {_invoice_payment_complete_menu}';
			print_notice('{_invoice_payment_complete}', '{_your_invoice_has_been_paid_in_full}', $ilpage['portfolio'] . '?cmd=management', '{_portfolio_manager}');
			exit();
		}
		else
		{
			$area_title = '{_no_funds_available_in_online_account}';
			$page_title = SITE_NAME . ' - {_no_funds_available_in_online_account}';
			print_notice('{_invoice_payment_warning_insufficient_funds}', '{_were_sorry_this_invoice_can_not_be_paid_due_to_insufficient_funds}', $ilpage['accounting'] . '?cmd=deposit', '{_deposit_funds_menu}');
			exit();
		}
	}
	else
	{
		$area_title = '{_invalid_portfolio_menu}';
		$page_title = SITE_NAME . ' - {_invalid_portfolio_menu}';
		print_notice('{_invalid_portfolio_catalog}', '{_were_sorry_the_portfolio_catalog_for_this_customer_has_been_removed_or_is_currently_not_prepared_for_viewing}'." ".'{_please}'." <span class=\"blue\"><a href='".$ilpage['portfolio']."'>".'{_view_other_portfolios}'."</a></span> ".'{_here_a_few_reasons_you_might_be_seeing_this_page}'.":<br /><br /><li>".'{_portfolio_has_not_been_setup_or_created}'."</li><li>".'{_this_vendors_subscription_may_be_inactive}'."</li><li>".'{_this_vendor_may_be_suspended_or_removed_from_the_marketplace}'."</li>", 'javascript:history.back(1);', '{_back}');
		exit();
	}	
}
// #### viewing portfolio in detail ############################################
else if (isset($ilance->GPC['id']) AND !empty($ilance->GPC['id']))
{
        // determine if the supplied user id is a string (by username) or number (user id)
	if (!is_numeric($ilance->GPC['id']))
	{
		$id = strip_tags(trim($ilance->GPC['id']));
		$id = $ilance->db->fetch_field(DB_PREFIX . "users", "username = '" . $ilance->db->escape_string($id) . "'", "user_id");
	}
	else
	{
		$id = intval($ilance->GPC['id']);
		$byusername = false;
	}
	if (isset($ilance->GPC['item']))
	{
		$itemid = intval($ilance->GPC['item']);
	}
	// #### REGULAR PORTFOLIOS #############################################
	if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
	{
		$ilance->GPC['page'] = 1;
	}
	else
	{
		$ilance->GPC['page'] = intval($ilance->GPC['page']);
	}
	$limit = ' ORDER BY p.portfolio_id DESC LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['portfoliodisplay_thumbsperpage']) . ',' . $ilconfig['portfoliodisplay_thumbsperpage'];
	$area_title = '{_viewing_portfolio_for} ' . fetch_user('username', intval($id));
	$page_title = SITE_NAME . ' - {_viewing_portfolio_for} ' . fetch_user('username', intval($id));
	// #### PORTFOLIO ZOOOOM IN ############################################
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'zoom')
	{
		$show['widescreen'] = true;
		$zoomed = 1;
		$not_zoomed = 0;
		$sql = "
			SELECT p.user_id, p.caption, p.description, p.category_id, p.featured, a.filename, a.counter, a.filesize, a.attachid, a.filehash, u.username
			FROM " . DB_PREFIX . "portfolio AS p,
			" . DB_PREFIX . "attachment AS a,
			" . DB_PREFIX . "users AS u,
			" . DB_PREFIX . "subscription_user AS su
			WHERE p.user_id = '" . intval($ilance->GPC['id']) . "'
				AND a.user_id = '" . intval($ilance->GPC['id']) . "'
				AND u.user_id = '" . intval($ilance->GPC['id']) . "'
				AND su.user_id = '" . intval($ilance->GPC['id']) . "'
				AND su.active = 'yes'
				AND a.attachid = '" . intval($itemid) . "'
				AND p.portfolio_id = a.portfolio_id
			$limit
		";
		$sql2 = "
			SELECT p.user_id, p.caption, p.description, p.category_id, p.featured, a.filename, a.counter, a.filesize, a.attachid, a.filehash, u.username
			FROM " . DB_PREFIX . "portfolio AS p,
			" . DB_PREFIX . "attachment AS a,
			" . DB_PREFIX . "users AS u,
			" . DB_PREFIX . "subscription_user AS su
			WHERE p.user_id = '" . intval($ilance->GPC['id']) . "'
				AND a.user_id = '" . intval($ilance->GPC['id']) . "'
				AND u.user_id = '" . intval($ilance->GPC['id']) . "'
				AND su.user_id = '" . intval($ilance->GPC['id']) . "'
				AND su.active = 'yes'
				AND a.attachid = '" . intval($itemid) . "'
				AND p.portfolio_id = a.portfolio_id
		";
	}
	else
	{
		$show['widescreen'] = false;
		$zoomed = 0;
		$not_zoomed = 1;
		$sql = "
			SELECT p.user_id, p.caption, p.description, p.category_id, p.featured, a.filename, a.counter, a.filesize, a.attachid, a.filehash, u.username
			FROM " . DB_PREFIX . "portfolio AS p, 
			" . DB_PREFIX . "attachment AS a,
			" . DB_PREFIX . "users AS u,
			" . DB_PREFIX . "subscription_user AS su
			WHERE p.user_id = '" . intval($id) . "'
				AND a.user_id = '" . intval($id) . "'
				AND u.user_id = '" . intval($id) . "'
				AND su.user_id = '" . intval($id) . "'
				AND su.active = 'yes'
				AND p.portfolio_id = a.portfolio_id
			$limit
		";
		$sql2 = "
			SELECT p.user_id, p.caption, p.description, p.category_id, p.featured, a.filename, a.counter, a.filesize, a.attachid, a.filehash, u.username
			FROM " . DB_PREFIX . "portfolio AS p, 
			" . DB_PREFIX . "attachment AS a,
			" . DB_PREFIX . "users AS u,
			" . DB_PREFIX . "subscription_user AS su
			WHERE p.user_id = '" . intval($id) . "'
				AND a.user_id = '" . intval($id) . "'
				AND u.user_id = '" . intval($id) . "'
				AND su.user_id = '" . intval($id) . "'
				AND su.active = 'yes'
				AND p.portfolio_id = a.portfolio_id
		";
	}
	$numberrows = $ilance->db->query($sql2);
	$number = $ilance->db->num_rows($numberrows);
	$area_title = '{_viewing_portfolio_for} ' . fetch_user('username', intval($id));
	$page_title = SITE_NAME . ' - {_viewing_portfolio_for} ' . fetch_user('username', intval($id));
	$counter = ($ilance->GPC['page'] - 1) * $ilconfig['portfoliodisplay_thumbsperpage'];
	$displayablearr = explode(', ', $ilconfig['portfoliodisplay_imagetypes']);
	$displayable = 0;
	$result = $ilance->db->query($sql);
	if ($ilance->db->num_rows($result) > 0)
	{
		$portfolio_html = $portfolio_html2 = '';
		$num = 0;
		$numcolumns = $ilconfig['portfoliodisplay_thumbsperrow'];
		$row_count = 0;
		while ($row = $ilance->db->fetch_array($result))
		{
			$memberstart = print_date(fetch_user('date_added', $row['user_id']), $ilconfig['globalserverlocale_globaldateformat']);
			$countryname = $ilance->common_location->print_user_country($row['user_id']);
			$ext = strchr($row['filename'], '.');
			if (in_array($ext, $displayablearr))
			{
				// display as an image
				$displayable = 1;
			}
			else
			{
				// display as an attachment link
				$displayable = 0;
			}			
			if ($displayable == 1)
			{
				if (isset($not_zoomed) AND $not_zoomed)
				{
					// portfolio thumbnails for detailed portfolio
					$thumbnail_photo = '<a href="' . $ilpage['portfolio'] . '?cmd=zoom&amp;item=' . $row['attachid'] . '&amp;id=' . $row['user_id'] . '" title="' . $row['filename'] . '"><img src="' . $ilpage['attachment'] . '?cmd=thumb&id=' . $row['filehash'] . '&subcmd=portfolio' . '" alt="' . $row['filename'] . '" border="0" /></a>';
				}
				else if (isset($zoomed) AND $zoomed)
				{
					$thumbnail_photo = '<a href="' . $ilpage['portfolio'] . '?id=' . $id . '" title="' . $row['filename'] . '"><img src="' . $ilpage['attachment'] . '?cmd=portfolio&id=' . $row['filehash'] . '&amp;original=1" alt="' . $row['filename'] . '" border="0" style="max-width:900px" /></a>';
				}
			}
			else
			{
				$attachextension = fetch_extension($row['filename']) . '.gif';
				if (file_exists(DIR_SERVER_ROOT . $ilconfig['template_imagesfolder'] . 'icons/' . $attachextension))
				{
					$attachextension = fetch_extension($row['filename']) . '.gif';
				}
				else
				{
					$attachextension = 'attach.gif';
				}				
				if (isset($not_zoomed) AND $not_zoomed)
				{
					$thumbnail_photo = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/' . $attachextension . '" alt="' . $row['filename'] . '" /> <a href="' . $ilpage['portfolio'] . '?cmd=zoom&item=' . $row['attachid'] . '&id=' . $row['user_id'] . '" title="' . $row['filename'] . '" border="0">' . $row['filename'] . '</a>';
				}
				else if (isset($zoomed) AND $zoomed)
				{
					$thumbnail_photo = '<a href="' . $ilpage['portfolio'] . '?id=' . $id . '" title="' . $row['filename'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/' . $attachextension . '" alt="' . $row['filename'] . '" border="0" /></a> ' . $row['filename'] . '<div><strong><a href="' . $ilpage['attachment'] . '?id=' . $row['filehash'] . '">{_download}</a></strong></div>';
				}
			}
			$thumbnail_caption = (empty($row['caption']))
				? stripslashes($ilance->db->fetch_field(DB_PREFIX.'attachment', 'attachid=' . $row['attachid'], 'filename'))
				: stripslashes($row['caption']);
			if (isset($zoomed) AND $zoomed)
			{
				$area_title = '{_detailed_portfolio_for} ' . fetch_user('username', intval($id)) . ' {_in} ' . $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $row['category_id']) . ' | ' . $thumbnail_caption . ' (' . $row['filename'] . ')';
				$page_title = SITE_NAME . ' - {_detailed_portfolio_for} ' . fetch_user('username', intval($id)) . ' {_in} ' . $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $row['category_id']) . ' | ' . $thumbnail_caption . ' (' . $row['filename'] . ')';
				$metakeywords = fetch_user('username', intval($id)) . ', ' . $thumbnail_caption . ', ' . $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $row['category_id']) . ', ' . '{_portfolio}';
				$metadescription = stripslashes($row['description']);
				$navcrumb = array();
				if ($ilconfig['globalauctionsettings_seourls'])
				{
					$navcrumb[HTTP_SERVER . "portfolios"] = '{_portfolios}';
				}
				else
				{
					$navcrumb["$ilpage[portfolio]"] = '{_portfolios}';
				}
				$navcrumb[construct_seo_url('portfoliocatplain', $row['category_id'], 0, $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $row['category_id']), '', 0, '', 0, 0)] = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $row['category_id']);
				$navcrumb[$ilpage['portfolio'] . '?id=' . fetch_user('username', intval($ilance->GPC['id']))] = fetch_user('username', intval($ilance->GPC['id']));
				$navcrumb[print_username(intval($ilance->GPC['id']), 'href')] = $thumbnail_caption;
			}
			else
			{
				$navcrumb = array();
				if ($ilconfig['globalauctionsettings_seourls'])
				{
					$navcrumb[HTTP_SERVER . "portfolios"] = '{_portfolios}';
				}
				else
				{
					$navcrumb["$ilpage[portfolio]"] = '{_portfolios}';
				}
				$navcrumb[fetch_user('username', intval($id))] = fetch_user('username', intval($id));	    
			}
			$provider = print_username($row['user_id'], 'href');
			$gender = fetch_user('gender', $row['user_id'], '', '', false);
			if ($gender == '' OR $gender == 'male')
			{
				$profile_logo = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto2.gif';
			}
			else if ($gender == 'female')
			{
				$profile_logo = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto3.gif';
			}
			$sql_attach = $ilance->db->query("
				SELECT attachid, filehash
				FROM " . DB_PREFIX . "attachment
				WHERE user_id = '" . $row['user_id'] . "'
				    AND visible = '1'
				    AND attachtype = 'profile'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql_attach) > 0)
			{
				$res_attach = $ilance->db->fetch_array($sql_attach, DB_ASSOC);
				$profile_logo = $ilpage['attachment'] . '?cmd=profile&amp;id=' . $res_attach['filehash'];
			}
			$thumbnail_provider = print_username($row['user_id'], 'href', 1);
			$thumbnail_views = (int)$row['counter'];
			$thumbnail_size = print_filesize($row['filesize']);
			$thumbnail_category = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $row['category_id']);
			$thumbnail_description = (!empty($row['description'])) ? shorten(stripslashes($row['description']), 35) : '{_none}';
			if (isset($not_zoomed) AND $not_zoomed)
			{
				// portfolio html display logic
				if ($num == $numcolumns)
				{
					$portfolio_html2 .= '<tr><td colspan="' . $numcolumns . '"></td></tr>';
				}
				else
				{
					if ($num == $numcolumns)
					{
						$portfolio_html2 .= '<tr>';
					}
					$portfolio_html2 .= '<td valign="bottom" width="33%">
		<div align="center" style="margin-top: 5px; margin-bottom: 15px">' . $thumbnail_photo . '</div>
		<table style="float:right" border="0" cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
		<tr>
		    <td colspan="2"><h2>' . $thumbnail_caption . '</h2></td>
		</tr>
		<tr class="alt1">
		    <td><div class="gray">{_category}</div></td>
		    <td width="75%">' . $thumbnail_category . '</td>
		</tr>
		</table>
	</td>';
				}
				$num++;
				if ($num == $numcolumns)
				{
					$portfolio_html2 .= '</tr><tr>'; 
					$num = 0;
				}
			}
			else if (isset($zoomed) AND $zoomed)
			{
				// currently zoomed in on a portfolio item
				$portfolio_html .= '<h1>' . $thumbnail_caption . '</h1>
<div class="black" style="padding-top:6px">' . $thumbnail_description . '</div>
<div align="center" style="padding-top:' . $ilconfig['table_cellpadding'] . 'px">' . $thumbnail_photo . '</div>
<div align="center" class="smaller litegray" style="padding-top:' . $ilconfig['table_cellpadding'] . 'px;padding-bottom:' . $ilconfig['table_cellpadding'] . 'px">{_category}: ' . $thumbnail_category . '</div>';
			}
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$row_count++;
		}
	}
	else
	{
		$show['no_portfolio_items'] = true;
		print_notice('{_invalid_portfolio_catalog}', '{_were_sorry_the_portfolio_catalog_for_this_customer_has_been_removed_or_is_currently_not_prepared_for_viewing}'." ".'{_please}'." <span class=\"blue\"><a href='".$ilpage['portfolio']."'>".'{_view_other_portfolios}'."</a></span> ".'{_here_a_few_reasons_you_might_be_seeing_this_page}'.":<br /><br /><li>".'{_portfolio_has_not_been_setup_or_created}'."</li><li>".'{_this_vendors_subscription_may_be_inactive}'."</li><li>".'{_this_vendor_may_be_suspended_or_removed_from_the_marketplace}'."</li>", 'javascript:history.back(1);', '{_back}');
	}
	$catbit = '';
	if (!empty($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0)
	{
		$catbit = '&amp;cid=' . intval($ilance->GPC['cid']);
	}
	$prevnext = print_pagnation($number, $ilconfig['portfoliodisplay_thumbsperpage'], intval($ilance->GPC['page']), $counter, $ilpage['portfolio'] . '?id=' . $id . $catbit);
        $pprint_array = array('portfolio_html2','profile_logo','js','feed1','feed6','feed12','vendorstars','memberstart','countryname','profile_logo','id','provider','prevnext','portfolio_html','input_style');
	$ilance->template->fetch('main', 'portfolio.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### viewing portfolio manager ##############################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'management' OR isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'create-portfolio')
{
        $show['widescreen'] = false;
	if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
	{
		$attachment_style = '';
		if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'attachments') != 'yes')
		{
			$attachment_style = 'disabled="disabled"';
		}		
		if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'addportfolio') == 'yes')
		{
			$area_title = '{_portfolio_management}';
			$page_title = SITE_NAME . ' - {_portfolio_management}';
                        $navcrumb = array();
			$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
			$navcrumb[""] = '{_portfolio_manager}';
                        // #### define top header nav ##########################
                        $topnavlink = array(
                                'mycp',
                                'portfolio_manage'
                        );
			if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
                        {
                                $ilance->GPC['page'] = 1;
                        }
                        else
                        {
                                $ilance->GPC['page'] = intval($ilance->GPC['page']);
                        }
			$limit = ' ORDER by date DESC LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
			$cntexe = $ilance->db->query("
				SELECT COUNT(*) AS number
				FROM " . DB_PREFIX . "attachment
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				    AND attachtype = 'portfolio'
				    AND visible = '1'
			");            
			$cntarr = $ilance->db->fetch_array($cntexe);            
			$number = $cntarr['number'];
			$SQL = "
				SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
				FROM " . DB_PREFIX . "attachment
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				    AND attachtype = 'portfolio'
				    AND visible = '1'
				$limit
			";
			$sql_file_sum = $ilance->db->query("
				SELECT SUM(filesize) AS attach_usage_total
				FROM " . DB_PREFIX . "attachment
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			");
			$res_file_sum = $ilance->db->fetch_array($sql_file_sum, DB_ASSOC);
			$attach_usage_total = $res_file_sum['attach_usage_total'];
			if (mb_strlen($attach_usage_total) <= 9 AND mb_strlen($attach_usage_total) >= 7)
			{ 
				$attach_usage_total = number_format($res_file_sum['attach_usage_total'] / 1048576, 1) . ' MB';
			} 
			else if (mb_strlen($attach_usage_total) >= 10)
			{
				$attach_usage_total = number_format($res_file_sum['attach_usage_total'] / 1073741824, 1) . ' GB';
			} 
			else
			{ 
				$attach_usage_total = number_format($res_file_sum['attach_usage_total'] / 1024, 1) . ' KB';
			}
			$displayablearr = mb_split(', ', $ilconfig['portfoliodisplay_imagetypes']);
                        $headinclude .= "
<script type=\"text/javascript\">
<!--
var urlBase = AJAXURL + '?do=inlineedit&action=portfolio&id=';
//-->
</script>";
			$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
			$row_count = 0;
			$res = $ilance->db->query($SQL);
			if ($ilance->db->num_rows($res) > 0)
			{
				while ($row = $ilance->db->fetch_array($res, DB_ASSOC))
				{
					$row['attach_id'] = $row['attachid'];
					$row['attach_filename'] = stripslashes($row['filename']);
					$caption = stripslashes($ilance->db->fetch_field(DB_PREFIX . "portfolio", "portfolio_id = '" . $row['portfolio_id'] . "'", "caption"));
					if (empty($caption))
					{
                                                $caption = $ilance->db->fetch_field(DB_PREFIX . "attachment", "attachid = '" . $row['attachid'] . "'", "filename");
					}
					$row['attach_caption'] = '<strong><span id="phrase' . $row['portfolio_id'] . '_titleinline"><span ondblclick="do_inline_edit(\'' . $row['portfolio_id'] . '_title\', this);">' . $caption . '</span></span></strong>';
					$description = stripslashes($ilance->db->fetch_field(DB_PREFIX . "portfolio", "portfolio_id = '" . $row['portfolio_id'] . "'", "description"));
					if (empty($description))
					{
						$description = '{_no_description_available}';
					}
					$row['attach_description'] = '<span id="phrase' . $row['portfolio_id'] . '_descriptioninline"><span ondblclick="do_inline_edit(\'' . $row['portfolio_id'] . '_description\', this);">' . $description . '</span></span>';
					$attachextension = fetch_extension($row['attach_filename']);
					$row['icon'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/attach.gif" width="16" height="16" border="0" />';
					if (file_exists(DIR_SERVER_ROOT . $ilconfig['template_imagesfolder'] . 'icons/' . $attachextension . '.gif'))
					{
						$row['icon'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/' . $attachextension . '.gif" width="16" height="16" border="0" />';
					}
					// is this portfolio item currently featured?
					if ($ilance->db->fetch_field(DB_PREFIX."portfolio", "portfolio_id = '" . $row['portfolio_id'] . "'", "featured"))
					{
						$date1split = explode(' ', $ilance->db->fetch_field(DB_PREFIX . "portfolio", "portfolio_id = '" . $row['portfolio_id'] . "'", "featured_date"));
						$date2split = explode('-', $date1split[0]);
						$totaldays = $ilconfig['portfolioupsell_featuredlength'];
						$elapsed = $ilance->datetimes->fetch_days_between($date2split[1], $date2split[2], $date2split[0], date('m'), date('d'), date('Y'));
						$days = ($totaldays-$elapsed);
						if ($days < 0)
						{
							// somehow the cron job did not expire the featured portfolio for this member
							// let's update our portfolio and set to non-featured
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "portfolio
								SET featured = '0'
								WHERE portfolio_id = '" . $row['portfolio_id'] . "'
								LIMIT 1
							");
							$row['isfeatured'] = '<input type="button" value=" {_feature} " onclick="location.href=\'' . $ilpage['portfolio'] . '?cmd=_feature-portfolio&amp;id=' . $row['portfolio_id'] . '\'" class="buttons_smaller" />';
						}
						$row['isfeatured'] = '<input type="button" value=" ' . $days . ' {_days_left} " onclick="location.href=\'\'" class="buttons_smaller" disabled="disabled" />';
						$row['class'] = 'featured_highlight';
					}
					else if ($ilconfig['portfolioupsell_featuredactive'])
					{
						$row['isfeatured'] = '<input type="button" value=" {_feature} " onclick="location.href=\'' . $ilpage['portfolio'] . '?cmd=_feature-portfolio&amp;id=' . $row['portfolio_id'] . '\'" class="buttons_smaller" />';
						$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
					}
					else 
					{
						$row['isfeatured'] = '';
						$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
					}
					if (isset($row['filesize']) AND mb_strlen($row['filesize']) <= 9 AND mb_strlen($row['filesize']) >= 7)
					{
						$row['attach_size'] = number_format($row['filesize'] / 1048576, 1) . ' MB';
					} 
					else if (isset($row['filesize']) AND mb_strlen($row['filesize']) >= 10)
					{ 
						$row['attach_size'] = number_format($row['filesize'] / 1073741824, 1) . ' GB';
					} 
					else
					{ 
						$row['attach_size'] = number_format($row['filesize'] / 1024, 1) . ' KB';
					}
					// thumbnail presentation
					$ext = strchr($row['filename'], '.');
					$displayable = 0;
					if (in_array($ext, $displayablearr))
					{
						$displayable = 1;
					}                            
					if ($displayable)
					{
						$row['thumbnail'] = '<img src="' . $ilpage['attachment'] . '?cmd=thumb&id=' . $row['filehash'] . '&subcmd=portfolio' . '" border="0" alt="" />';
					}
					else
					{
						$attachextension = fetch_extension($row['filename']) . '.gif';
						if (file_exists(DIR_SERVER_ROOT . $ilconfig['template_imagesfolder'] . 'icons/' . $attachextension))
						{
							$attachextension = fetch_extension($row['filename']) . '.gif';
						}
						else
						{
							$attachextension = 'attach.gif';
						}
                                                
						$row['thumbnail'] = '<span class="smaller gray">{_nonimage_format}</span>';
					}
					$row['attach_type'] = ucfirst($row['attachtype']);
					$row['attach_views'] = $row['counter'];
					$row['attach_date'] = print_date($row['date'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
					$row['attach_action'] = '<input type="checkbox" name="attachid[]" value="' . $row['attachid'] . '" />';
					$attachment_rows[] = $row;
					$row_count++;
				}
				$show['no_attachment_rows'] = false;
			}
			else
			{
				$show['no_attachment_rows'] = true;
			}
			$scriptpage = $ilpage['portfolio'] . '?cmd=management';
			$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], intval($ilance->GPC['page']), $counter, $scriptpage);
			// #### PENDING ATTACHMENTS TABLE ##################################
			if (!isset($ilance->GPC['page2']) OR isset($ilance->GPC['page2']) AND $ilance->GPC['page2'] <= 0)
                        {
                                $ilance->GPC['page2'] = 1;
                        }
                        else
                        {
                                $ilance->GPC['page2'] = intval($ilance->GPC['page2']);
                        }
			$limit2 = ' ORDER BY date DESC LIMIT ' . (($ilance->GPC['page2'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
			$numberrows2 = $ilance->db->query("
				SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
				FROM " . DB_PREFIX . "attachment
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				    AND attachtype = 'portfolio'
				    AND visible = '0'
			");
			$number2 = $ilance->db->num_rows($numberrows2);
			$counter2 = ($ilance->GPC['page2'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
			$row_count2 = 0;
			$result2 = $ilance->db->query("
				SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
				FROM " . DB_PREFIX . "attachment
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				    AND attachtype = 'portfolio'
				    AND visible = '0'
				$limit2
			");
			if ($ilance->db->num_rows($result2) > 0)
			{
				$show['no_attachment_pending_rows'] = false;
				while ($row_ = $ilance->db->fetch_array($result2))
				{
					$row_['attach_id'] = $row_['attachid'];
					$row_['attach_filename'] = $row_['filename'];
					$row_['attach_type'] = ucfirst($row_['attachtype']);
					$row_['status'] = '{_review_in_progress}';
					$row_['actions'] = '<input type="checkbox" name="attachid[]" value="' . $row_['attachid'] . '" />';
					$row_['class'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
					$attachment_pending_rows[] = $row_;
					$row_count2++;
				}
			}
			else
			{
				$show['no_attachment_pending_rows'] = true;
			}
			$prevnext2 = print_pagnation($number2, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page2'], $counter2, $ilpage['portfolio'] . '?cmd=management', 'page2');
			$hiddeninput = array(
				'attachtype' => 'portfolio',
				'project_id' => 0,
				'user_id' => $_SESSION['ilancedata']['user']['userid'],
				'category_id' => 0,
				'filehash' => md5(time()),
				'max_filesize' => $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'uploadlimit')
			);
			$uploadbutton = '<a href="javascript:void(0)" onclick=Attach("' . $ilpage['upload'] . '?crypted=' . encrypt_url($hiddeninput) . '&amp;refresh=1")>{_upload_media}</a>';
			$hiddeninput = encrypt_url($hiddeninput);
			$pprint_array = array('hiddeninput','numcolumns','uploadbutton','project_id','category_id','filehash','user_id','attachtype','max_filesize','attach_user_max','attach_usage_total','prevnext','prevnext2','input_style');
			$ilance->template->fetch('main', 'portfolio_manage.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_loop('main', 'attachment_rows');
			$ilance->template->parse_loop('main', 'attachment_pending_rows');
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}
		else
		{
			$area_title = '{_access_denied_to_portfolio_resources}';
			$page_title = SITE_NAME . ' - {_access_denied_to_portfolio_resources}';
			print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource}'." <a href='".$ilpage['subscription']."'><strong>".'{_click_here}'."</strong></a>", $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('addportfolio'));
			exit();
		}
	}
	else
	{	
		refresh($ilpage['login'] . '?redirect=' . urlencode($ilpage['portfolio'] . print_hidden_fields($string = true, $excluded = array(), $questionmarkfirst = true)));
		exit();
	}
}
// #### portfolio main menu landing ############################################
else
{
	$show['widescreen'] = false;
	// #### do we search via specific category? ############################
	$cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
	$catbit = '';
	$childrenids = $ilance->categories->fetch_children_ids($cid, 'service');
	$subcategorylist = (!empty($childrenids)) ? $cid . ',' . $childrenids : $cid . ',';
	$subcatsql = "AND (FIND_IN_SET(p.category_id, '$subcategorylist'))";
	// #### defaults #######################################################
	$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
	$limitfeatured = ' ORDER BY p.featured DESC, p.user_id ASC LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['portfoliodisplay_thumbsperpage']) . ',' . $ilconfig['portfoliodisplay_thumbsperpage'];
	$metatitle = '{_portfolios}';
	// #### setup some page titles and meta tags ###########################
	if ($cid > 0)
	{
		$metatitle = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $cid) . ' | ' . '{_portfolios}';
		$metadescription = $ilance->categories->description($_SESSION['ilancedata']['user']['slng'], $cid) . ' ' . '{_portfolios}';
		$metakeywords = $ilance->categories->keywords($_SESSION['ilancedata']['user']['slng'], $cid, $commaafter = true, $showinputkeywords = true);
	}
	$area_title = $metatitle;
	$page_title = SITE_NAME . ' - ' . $metatitle;
	// #### featured portfolios ############################################
	$row_count = 0;
	$resultfeatured = $ilance->db->query("
		SELECT p.user_id, p.caption, p.description, p.category_id, p.featured, a.filename, a.filesize, a.counter, a.attachid, a.filehash, u.username
		FROM " . DB_PREFIX . "portfolio AS p,
		" . DB_PREFIX . "attachment AS a,
		" . DB_PREFIX . "users AS u,
		" . DB_PREFIX . "subscription_user AS su
		WHERE p.user_id = a.user_id
			AND u.user_id = p.user_id
			AND su.user_id = p.user_id
			AND su.active = 'yes'
			AND p.featured = '1'
			AND p.portfolio_id = a.portfolio_id 
		$subcatsql
		$limitfeatured
	", 0, null, __FILE__, __LINE__);
	$result2featured = $ilance->db->query("
		SELECT p.user_id, a.filehash, u.username
		FROM " . DB_PREFIX . "portfolio AS p,
		" . DB_PREFIX . "attachment AS a,
		" . DB_PREFIX . "users AS u,
		" . DB_PREFIX . "subscription_user AS su
		WHERE p.user_id = a.user_id
			AND u.user_id = p.user_id
			AND su.user_id = p.user_id
			AND su.active = 'yes'
			AND p.featured = '1'
			AND p.portfolio_id = a.portfolio_id
		$subcatsql
	", 0, null, __FILE__, __LINE__);
	$counterfeatured = ($ilance->GPC['page'] - 1) * $ilconfig['portfoliodisplay_thumbsperpage'];    
	$numberfeatured = $ilance->db->num_rows($result2featured);
	$numcolumnsfeatured = $ilconfig['portfoliodisplay_thumbsperrow'];
	$displayablearr = mb_split(', ', $ilconfig['portfoliodisplay_imagetypes']);
	$displayable = $numfeatured = 0;
	if ($ilance->db->num_rows($resultfeatured) > 0)
	{
                require_once(DIR_CORE . 'functions_search.php');
		$show['no_featured_portfolio_items'] = false;
		$num = 0;
		$portfolio_featured_html = '<tr class="alt1">';
		while ($rowfeatured = $ilance->db->fetch_array($resultfeatured, DB_ASSOC))
		{
			$ext = strchr($rowfeatured['filename'], '.');
			$displayable = (in_array($ext, $displayablearr)) ? 1 : 0;
			$tdclass = '_left';
			if ($num == 0)
			{
				$tdclass = '';
			}
			// #### caption title ##################################
			if (empty($rowfeatured['caption']))
			{
				$thumbnail_caption = shorten(stripslashes($ilance->db->fetch_field(DB_PREFIX . "attachment", "attachid = '" . $rowfeatured['attachid'] . "'", "filename")), 35);
				if ($rowfeatured['featured'])
				{
					// $thumbnail_caption .= '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'showcase.gif" alt="'.'{_featured}'.'" border="0" />';
				}
			}
			else
			{
				$thumbnail_caption = shorten(stripslashes($rowfeatured['caption']), 35);
				if ($rowfeatured['featured'])
				{
					// $thumbnail_caption .= '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'showcase.gif" alt="'.'{_featured}'.'" border="0" />';
				}
			}
			if ($displayable)
			{
				$portfoliopopup = $ilconfig['portfoliodisplay_popups']
					? "onmouseover=\"showtrail('" . $ilpage['attachment'] . "?id=" . $rowfeatured['filehash'] . "&cmd=thumb&subcmd=portfolio&w=" . $ilconfig['portfoliodisplay_popups_width'] . "&h=" . $ilconfig['portfoliodisplay_popups_height'] . "','" . $thumbnail_caption . "',1)\" onmouseout=\"hidetrail()\""
					: '';
				$thumbnail_photo = '<a href="' . $ilpage['portfolio'] . '?cmd=zoom&amp;item=' . $rowfeatured['attachid'] . '&amp;id=' . $rowfeatured['user_id'] . '"><img src="' . $ilpage['attachment'] . '?cmd=thumb&amp;id=' . $rowfeatured['filehash'] . '&amp;subcmd=portfoliofeatured" ' . $portfoliopopup . ' border="0" alt="" /></a>';
			}
			else
			{
				$attachextension = fetch_extension($rowfeatured['filename']) . '.gif';
				$attachextension = (file_exists(DIR_SERVER_ROOT . $ilconfig['template_imagesfolder'] . 'icons/' . $attachextension)) ? fetch_extension($rowfeatured['filename']) . '.gif' : 'attach.gif';
				$thumbnail_photo = '<a href="' . $ilpage['portfolio'] . '?cmd=zoom&amp;item=' . $rowfeatured['attachid'] . '&amp;id=' . $rowfeatured['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/' . $attachextension . '" border="0" alt="" /></a>';
			}
			$thumbnail_provider = print_username($rowfeatured['user_id'], 'href', 1);
                        $thumbnail_invite = '<a href="' . HTTP_SERVER . $ilpage['rfp'] . '?cmd=rfp-invitation&amp;id=' . $rowfeatured['user_id'] . '&amp;trk=f_gallery" rel="nofollow">{_invite_to_bid}</a>';
			$thumbnail_views = $rowfeatured['counter'];
			$thumbnail_size = print_filesize($rowfeatured['filesize']);
			$thumbnail_category = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $rowfeatured['category_id']);
			$thumbnail_description = '{_no_description}';
                        // #### caption description ############################
			if (!empty($rowfeatured['description']))
			{
				$thumbnail_description  = shorten(stripslashes($rowfeatured['description']), 35);
			}
			// #### featured portfolios html display logic #########
			if ($numfeatured == $numcolumnsfeatured)
			{
				$portfolio_featured_html .= '<tr class="alt1"><td colspan="' . $numcolumnsfeatured . '"></td></tr>';
			}
			else
			{
				if ($numfeatured == $numcolumnsfeatured)
				{
					$portfolio_featured_html .= '<tr class="alt1">';
				}
				$portfolio_featured_html .= '<td valign="bottom" width="33%" class="alt' . $tdclass . '">
<div align="center" style="width:100%; margin-top:5px;margin-bottom:14px">' . $thumbnail_photo . '</div>
<table style="float:right" border="0" cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
<tr>
	<td class="alt2" colspan="2"><div><strong>' . $thumbnail_caption . '</strong> <span class="smaller gray">(' . $thumbnail_size . ')</span></div><div style="padding-top:3px" class="smaller gray">' . $thumbnail_description . '</div></td>
</tr>
<tr class="alt1">
	<td><span class="gray">{_provider}</span></td>
	<td><span class="blue" style="padding-right:45px; font-size:14px">' . $thumbnail_provider . '</span> <span class="smaller blue">' . $thumbnail_invite . '</span></td>
</tr>';
                                if ($ilconfig['enableskills'])
                                {
                                        $portfolio_featured_html .= '<tr class="alt1">
	<td valign="top"><span class="gray">{_skills}</span></td>
	<td width="95%" valign="top">' . print_skills($rowfeatured['user_id'], $showmaxskills = 3, $nourls = true) . '</td>
</tr>';
                                }
                                $portfolio_featured_html .= '</table>
	</td>';
			}
			$numfeatured++;
			if ($numfeatured == $numcolumnsfeatured)
			{
                                $portfolio_featured_html .= '</tr><tr class="alt1">'; 
                                $numfeatured = 0;
			}
			$rowfeatured['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$row_count++;
			$num++;
		}
		$portfolio_featured_html .= '</tr>';
	}
	else
	{
		$show['no_featured_portfolio_items'] = true;
	}
	$catbit .= (!empty($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0) ? '&amp;cid=' . intval($ilance->GPC['cid']) : '?cmd=listings';
	$prevnext = print_pagnation($numberfeatured, $ilconfig['portfoliodisplay_thumbsperpage'], $ilance->GPC['page'], $counterfeatured, $ilpage['portfolio'] . $catbit);
	// #### setup defaults for regular portfolios ##########################
	$ilance->GPC['page2'] = (!isset($ilance->GPC['page2']) OR isset($ilance->GPC['page2']) AND $ilance->GPC['page2'] <= 0) ? 1 : intval($ilance->GPC['page2']);
	$limit2 = ' ORDER BY p.featured DESC, p.user_id ASC LIMIT ' . (($ilance->GPC['page2'] - 1) * $ilconfig['portfoliodisplay_thumbsperpage']) . ',' . $ilconfig['portfoliodisplay_thumbsperpage'];
	// #### regular portfolios #############################################
	$numberrows = $ilance->db->query("
		SELECT p.portfolio_id, a.counter, u.username
		FROM " . DB_PREFIX . "portfolio AS p,
		" . DB_PREFIX . "attachment AS a,
		" . DB_PREFIX . "users AS u,
		" . DB_PREFIX . "subscription_user AS su
		WHERE p.user_id = a.user_id
			AND u.user_id = p.user_id 
			AND su.user_id = p.user_id
			AND su.active = 'yes'
			AND p.featured = '0'
			AND p.portfolio_id = a.portfolio_id 
		$subcatsql
	", 0, null, __FILE__, __LINE__);
	$result = $ilance->db->query("
		SELECT p.portfolio_id, p.user_id, p.caption, p.description, p.category_id, p.featured, a.filename, a.filesize, a.attachid, a.filehash, a.counter, u.username
		FROM " . DB_PREFIX . "portfolio AS p,
		" . DB_PREFIX . "attachment AS a,
		" . DB_PREFIX . "users AS u,
		" . DB_PREFIX . "subscription_user AS su
		WHERE p.user_id = a.user_id
			AND u.user_id = p.user_id
			AND su.user_id = p.user_id
			AND su.active = 'yes'
			AND p.featured = '0'
			AND p.portfolio_id = a.portfolio_id 
		$subcatsql
		$limit2
	", 0, null, __FILE__, __LINE__);
	$number2 = $ilance->db->num_rows($numberrows);
	$counter2 = ($ilance->GPC['page2'] - 1) * $ilconfig['portfoliodisplay_thumbsperpage'];
	$row_count = 0;
	$displayablearr = mb_split(', ', $ilconfig['portfoliodisplay_imagetypes']);
	$displayable = 0;
	if ($ilance->db->num_rows($result) > 0)
	{
		$show['no_portfolio_items'] = false;
		$num = 0;
		$numcolumns = $ilconfig['portfoliodisplay_thumbsperrow'];
		$portfolio_html = '<tr>';
		while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
		{
			$ext = strchr($row['filename'], '.');
			$displayable = (in_array($ext, $displayablearr)) ? 1 : 0;
			$tdclass = '_left';
			if ($num == 0)
			{
				$tdclass = '';
			}
			// caption
			$thumbnail_caption = '';
			if (empty($row['caption']))
			{
				$thumbnail_caption .= ($row['featured'])
					? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/dollarsign.gif" border="0" alt="" />'
					: shorten(stripslashes($ilance->db->fetch_field(DB_PREFIX . "attachment", "attachid = '" . $row['attachid'] . "'", "filename")), 35);
			}
			else
			{
				$thumbnail_caption .= ($row['featured'])
					? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/dollarsign.gif" border="0" alt="" />'
					: shorten(stripslashes($row['caption']), 35);
			}
			if ($displayable)
			{
				$portfoliopopup = $ilconfig['portfoliodisplay_popups']
					? "onmouseover=\"showtrail('" . $ilpage['attachment'] . "?id=" . $row['filehash'] . "&cmd=thumb&subcmd=portfolio&w=" . $ilconfig['portfoliodisplay_popups_width'] . "&h=" . $ilconfig['portfoliodisplay_popups_height'] . "','" . $thumbnail_caption . "',1)\" onmouseout=\"hidetrail()\""
					: '';
						
				$thumbnail_photo = '<a href="' . HTTP_SERVER . $ilpage['portfolio'] . '?cmd=zoom&amp;item=' . $row['attachid'] . '&amp;id=' . $row['user_id'] . '"><img src="' . $ilpage['attachment'] . '?cmd=thumb&amp;id=' . $row['filehash'] . '&amp;subcmd=portfolio" ' . $portfoliopopup . ' border="0" alt="" /></a>';
			}
			else
			{
				$attachextension = fetch_extension($row['filename']) . '.gif';
				$attachextension = (file_exists(DIR_SERVER_ROOT.$ilconfig['template_imagesfolder'] . 'icons/' . $attachextension)) ? fetch_extension($row['filename']) . '.gif' : 'attach.gif';
				$thumbnail_photo = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/' . $attachextension . '" border="0" /> <a href="' . $ilpage['portfolio'] . '?cmd=zoom&amp;item=' . $row['attachid'] . '&amp;id=' . $row['user_id'] . '">' . stripslashes($row['filename']) . '</a>';
			}
			$thumbnail_provider = print_username($row['user_id'], 'plain', 1);
			$thumbnail_invite = '<a href="' . HTTP_SERVER . $ilpage['rfp'] . '?cmd=rfp-invitation&amp;id=' . $row['user_id'] . '">{_invite_to_bid}</a>';
			$thumbnail_views = $row['counter'];
			$thumbnail_size = print_filesize($row['filesize']);
			$thumbnail_category = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $row['category_id']);
			$thumbnail_description = '{_no_description}';
			if (!empty($row['description']))
			{
				$thumbnail_description = shorten(stripslashes($row['description']), "35");
			}
			// portfolio html display logic
			if ($num == $numcolumns)
			{
				$portfolio_html .= '<tr><td colspan="' . $numcolumns . '"></td></tr>';
			}
			else
			{
				if ($num == $numcolumns)
				{
					$portfolio_html .= '<tr>';
				}
				$portfolio_html .= '<td valign="bottom" width="33%">
		<h3 style="margin-top:5px;margin-bottom:14px">' . $thumbnail_photo . '</h3>
		<h3>' . $thumbnail_caption . '</h3>
		<h3 style="font-weight:normal" class="litegray">' . $thumbnail_provider . '</h3>
		
	</td>';
			}
			$num++;
			if ($num == $numcolumns)
			{
				$portfolio_html .= '</tr><tr>'; 
				$num = 0;
			}
			$row['class'] = '';
			$row_count++;
		}
		$portfolio_html .= '</tr>';
	}
	else
	{
		$show['no_portfolio_items'] = true;
	}
	$catbit2 = '?cmd=listings';
	if (!empty($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0)
	{
		$catbit2 .= '&amp;cid=' . intval($ilance->GPC['cid']);
	}
	$prevnext2 = print_pagnation($number2, $ilconfig['portfoliodisplay_thumbsperpage'], $ilance->GPC['page2'], $counter2, $ilpage['portfolio'] . $catbit2, 'page2');
}
$cid = 0;
if (!empty($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0)
{
	$cid = intval($ilance->GPC['cid']);	
	$navcrumb = array();
	if ($ilconfig['globalauctionsettings_seourls'])
	{
		$url = print_seo_url($ilconfig['portfolioslistingidentifier']);
		$navcrumb[HTTP_SERVER . "$url"] = '{_portfolios}';
		unset($url);
	}
	else
	{
		$navcrumb[HTTP_SERVER . "$ilpage[portfolio]"] = '{_portfolios}';
	}	
	$ilance->categories->breadcrumb($cid, 'portfolio', $_SESSION['ilancedata']['user']['slng']);
}
//$ilance->categories->build_array('portfolio', $_SESSION['ilancedata']['user']['slng'], 1, true, '', '', 0, -1, 2);
$portfoliocategory = $ilance->template_nav->print_left_nav('portfolio', $cid, 1, 0, $ilconfig['globalfilters_enablecategorycount']);
// #### recently featured portfolio entries ####################################
$recentlyfeatured = $ilance->portfolio->print_recently_featured_users($cid, $ilconfig['portfolioupsell_featuredlength']);
// #### portfolio statistics ###################################################
$portfoliocats = $ilance->portfolio->fetch_stats('countcats');
$portfolioitems = $ilance->portfolio->fetch_stats('countitems');
$portfolioviews = $ilance->portfolio->fetch_stats('countviews');
$portfoliodiskspace = $ilance->portfolio->fetch_stats('diskspace');
// #### portfolio calendar #####################################################
$portfoliocalendar = '';
$pprint_array = array('portfoliocalendar','recentlyfeatured','portfoliocats','portfolioitems','portfolioviews','portfoliodiskspace','portfoliocategory','js','prevnext','prevnext2','portfolio_featured_html','portfolio_html','input_style');
$ilance->template->fetch('main', 'portfolio_main.html');
$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', $pprint_array);
exit();

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>