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
		'jquery',
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
$navcrumb = array("$ilpage[subscribers]" => $ilcrumbs["$ilpage[subscribers]"]);
if(($v3nav = $ilance->cache->fetch("print_admincp_nav_subscribers")) === false)
{
	$v3nav = $ilance->admincp->print_admincp_nav($_SESSION['ilancedata']['user']['slng'], $ilpage['subscribers']);
	$ilance->cache->store("print_admincp_nav_subscribers", $v3nav);
}
if (empty($_SESSION['ilancedata']['user']['userid']) OR (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '0'))
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();
}
$id = isset($ilance->GPC['id']) ? intval($ilance->GPC['id']) : 0;

($apihook = $ilance->api('admincp_subscribers_start')) ? eval($apihook) : false;

// #### NON-PROFITS MANAGER ############################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'nonprofits')
{
	$area_title = '{_nonprofits}';
	$page_title = SITE_NAME . ' - {_nonprofits}';
	
	($apihook = $ilance->api('admincp_nonprofits_settings')) ? eval($apihook) : false;
	
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['subscribers'], $ilpage['subscribers'] . '?cmd=nonprofits', $_SESSION['ilancedata']['user']['slng']);
	$hiddenfieldsubcmd = 'add-nonprofit';
	$hiddendo = $hiddenid = '';
	// #### DELETE A NON-PROFIT ####################################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'remove-nonprofit' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		if ($show['ADMINCP_TEST_MODE'])
		{
			print_action_failed('{_demo_mode_only}', $ilpage['components']);
			exit();
		}
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "charities
			WHERE charityid = '" . intval($ilance->GPC['id']) . "'
			LIMIT 1
		");
		print_action_success('{_the_selected_nonprofit_was_removed_from_the_system}', $ilpage['subscribers'] . '?cmd=nonprofits');
		exit();
	}
	// #### ADD NEW NON-PROFIT #####################################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'add-nonprofit' AND !empty($ilance->GPC['title']) AND !empty($ilance->GPC['description']) AND !empty($ilance->GPC['url']))
	{
		if ($show['ADMINCP_TEST_MODE'])
		{
			print_action_failed('{_demo_mode_only}', $ilpage['components']);
			exit();
		}
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "charities
			(charityid, title, description, url, visible)
			VALUES (
			NULL,
			'" . $ilance->db->escape_string($ilance->GPC['title']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['description']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['url']) . "',
			'1')
		");
		print_action_success('{_the_selected_nonprofit_was_added_to_the_system_ready_to_receive_donations_from_sellers}', $ilpage['subscribers'] . '?cmd=nonprofits');
		exit();
	}
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-nonprofit' AND !empty($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND empty($ilance->GPC['do']))
	{
		$hiddenfieldsubcmd = 'update-nonprofit';
		$hiddendo = '<input type="hidden" name="do" value="update" />';
		$hiddenid = '<input type="hidden" name="id" value="' . intval($ilance->GPC['id']) . '" />';
		
		$sql = $ilance->db->query("
			SELECT charityid, title, description, url, earnings, donations, visible
			FROM " . DB_PREFIX . "charities
			WHERE charityid = '" . intval($ilance->GPC['id']) . "'
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql);
			$title = $res['title'];
			$description = $res['description'];
			$url = $res['url'];
		}
	}
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-nonprofit' AND !empty($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND !empty($ilance->GPC['do']) AND $ilance->GPC['do'] == 'update')
	{
		if ($show['ADMINCP_TEST_MODE'])
		{
			print_action_failed('{_demo_mode_only}', $ilpage['components']);
			exit();
		}
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "charities
			SET title = '" . $ilance->db->escape_string($ilance->GPC['title']) . "',
			description = '" . $ilance->db->escape_string($ilance->GPC['description']) . "',
			url = '" . $ilance->db->escape_string($ilance->GPC['url']) . "'
			WHERE charityid = '" . intval($ilance->GPC['id']) . "'
		");
		print_action_success('{_the_selected_nonprofit_was_updated}', $ilpage['subscribers'] . '?cmd=nonprofits');
		exit();         
	}
	// #### non-profits ############################################
	$total1 = 0;
	$total2 = 0;
	$show['charities'] = false;
	$row_count = 0;
	$sql = $ilance->db->query("
		SELECT charityid, title, description, url, earnings, donations, visible
		FROM " . DB_PREFIX . "charities
		ORDER BY charityid DESC
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$show['charities'] = true;
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$total1 += $res['donations'];
			$total2 += $res['earnings'];
			$res['donations'] = number_format($res['donations']);
			$res['earnings'] = $ilance->currency->format($res['earnings']);
			$res['action'] = '<span class="blue"><a href="' . $ilpage['subscribers'] . '?cmd=nonprofits&amp;subcmd=update-nonprofit&amp;id=' . $res['charityid'] . '#updatenonprofit"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="' . $ilpage['subscribers'] . '?cmd=nonprofits&amp;subcmd=remove-nonprofit&amp;id=' . $res['charityid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a></span>';
			$nonprofits[] = $res;
			$row_count++;
		}        
	}
	$total2 = $ilance->currency->format($total2);
	$total1 = number_format($total1);
	$configuration_nonprofits = $ilance->admincp->construct_admin_input('nonprofits', $ilpage['subscribers'] . '?cmd=nonprofits');
	$date_range = '';
	if (isset($ilance->GPC['range_start']) AND isset($ilance->GPC['range_end']))
	{
		$startDate = print_array_to_datetime($ilance->GPC['range_start']);
		$endDate = print_array_to_datetime($ilance->GPC['range_end'], TIMENOW);
		$date_range = " AND (i.createdate <= '" . $endDate . "' AND i.createdate >= '" . $startDate . "')";
		$searchquery = "&range_start[0]=".$ilance->GPC['range_start'][0]."&range_start[1]=".$ilance->GPC['range_start'][1]."&range_start[2]=".$ilance->GPC['range_start'][2]."&range_end[0]=".$ilance->GPC['range_end'][0]."&range_end[1]=".$ilance->GPC['range_end'][1]."&range_end[2]=".$ilance->GPC['range_end'][2];
	}
	$reportfromrange = $ilance->admincp->print_from_to_date_range();
	$regular_total = $regular_paid = $regular_unpaid = 0;
	$sql1 = $ilance->db->query("
		SELECT i.totalamount as regular, i.status as status
		FROM " . DB_PREFIX . "invoices i
		LEFT JOIN " . DB_PREFIX . "projects p ON p.project_id = i.projectid   
		WHERE i.isdonationfee = '1'
			AND p.filtered_auctiontype = 'regular'
			$date_range
	");
	if ($ilance->db->num_rows($sql1) > 0)
	{
		while ($res1 = $ilance->db->fetch_array($sql1, DB_ASSOC))
		{
			if ($res1['status'] == 'paid')
			{
				$regular_paid += $res1['regular'];
			}
			else
			{
				$regular_unpaid += $res1['regular'];
			}
		}
	}
	$fixed_total = $fixed_paid = $fixed_unpaid = 0;
	$sql2 = $ilance->db->query("
		SELECT i.totalamount as fixed, i.status as status
		FROM " . DB_PREFIX . "invoices i
		LEFT JOIN " . DB_PREFIX . "projects p ON p.project_id = i.projectid
		WHERE i.isdonationfee = '1'
		    AND p.filtered_auctiontype = 'fixed'
		    $date_range
	");
	if ($ilance->db->num_rows($sql2) > 0)
	{
		while($res2 = $ilance->db->fetch_array($sql2, DB_ASSOC))
		{
			if ($res2['status'] == 'paid')
			{
				$fixed_paid += $res2['fixed'];
			}
			else
			{
				$fixed_unpaid += $res2['fixed'];
			}
		}
	}
	$total_unpaid = ($regular_unpaid + $fixed_unpaid);
	$total_paid = ($regular_paid + $fixed_paid);
	$total_nonprofit = $ilance->currency->format($total_paid + $total_unpaid);
	$total_unpaid = $ilance->currency->format($total_unpaid);
	$total_paid = $ilance->currency->format($total_paid);
	$fixed_total = $ilance->currency->format($fixed_paid + $fixed_unpaid);
	$fixed_paid = $ilance->currency->format($fixed_paid);
	$fixed_unpaid = $ilance->currency->format($fixed_unpaid);
	$regular_total = $ilance->currency->format($regular_paid + $regular_unpaid);
	$regular_paid = $ilance->currency->format($regular_paid);
	$regular_unpaid = $ilance->currency->format($regular_unpaid);
	$pprint_array = array('total_nonprofit','total_paid','total_unpaid','regular_total','regular_paid','regular_unpaid','fixed_total','fixed_paid','fixed_unpaid','reportfromrange','hiddenid','hiddendo','title','description','url','hiddenfieldsubcmd','total1','total2','configuration_nonprofits','submit','subcmd','subcat_pulldown','question_inputtype_pulldown','questionid','cid','slng','categoryname','language_pulldown','slng','checked_question_cansearch','checked_question_active','checked_question_required','subcategory_pulldown','formdefault','multiplechoice','question','description','formname','sort','submit_category_question','question_id_hidden','question_subcmd','question_inputtype_pulldown','subcatid','subcatname','catname','service_subcategories','product_categories','subcmd','id','submit','description','name','checked_profile_group_active');
	
	($apihook = $ilance->api('admincp_nonprofits_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'nonprofits.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
	$ilance->template->parse_loop('main', 'nonprofits');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();        
}
// #### ABUSE REPORT MANAGER ###########################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'abuse')
{
	$area_title = '{_abuse_report_management}';
	$page_title = SITE_NAME . ' - {_abuse_report_management}';
	
	($apihook = $ilance->api('admincp_abuse_report_settings')) ? eval($apihook) : false;
	
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['subscribers'], $ilpage['subscribers'] . '?cmd=abuse', $_SESSION['ilancedata']['user']['slng']);
	
	// #### DISMISS AN ABUSE REPORT ################################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'dismiss' AND isset($ilance->GPC['abuseid']) AND $ilance->GPC['abuseid'] > 0)
	{
		if ($show['ADMINCP_TEST_MODE'])
		{
			print_action_failed('{_demo_mode_only}', $ilpage['components']);
			exit();
		}
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "abuse_reports
			SET status = '0'
			WHERE abuseid = '" . intval($ilance->GPC['abuseid']) . "'
		");
		print_action_success('{_the_selected_abuse_report_was_dismissed}', $ilpage['subscribers'] . '?cmd=abuse');
		exit();
	}
	// #### listings abuse #########################################
	$show['listingabuse'] = false;
	$row_count = 0;
	$sql = $ilance->db->query("
		SELECT abuseid, regarding, username, email, itemid, status, dateadded, type
		FROM " . DB_PREFIX . "abuse_reports
		WHERE abusetype = 'listing'
			AND status = '1'
		ORDER BY dateadded DESC
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$show['listingabuse'] = true;
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$res['action'] = '<a href="' . $ilpage['subscribers'] . '?cmd=abuse&amp;subcmd=dismiss&amp;abuseid=' . $res['abuseid'] . '">{_dismiss}</a>';
			$res['fullregarding'] = trim(addslashes(nl2br(strip_tags($res['regarding']))));
			$res['regarding'] = short_string($res['regarding'], 75, $symbol = ' .....');
			if ($res['type'] == 'service')
			{
				$res['ref'] = '<a href="' . HTTP_SERVER . $ilpage['rfp'] . '?id=' . $res['itemid'] . '" target="_blank">' . $res['itemid'] . '</a>';
			}
			else
			{
				$res['ref'] = '<a href="' . HTTP_SERVER . $ilpage['merch'] . '?id=' . $res['itemid'] . '" target="_blank">' . $res['itemid'] . '</a>';
			}
			$res['dateadded'] = print_date($res['dateadded']);
			$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$listingabuse[] = $res;
			$row_count++;
		}        
	}
	// #### portfolio abuse ########################################
	$show['portfolioabuse'] = false;
	$row_count = 0;
	$sql = $ilance->db->query("
		SELECT abuseid, regarding, username, email, itemid, status, dateadded, type
		FROM " . DB_PREFIX . "abuse_reports
		WHERE abusetype = 'portfolio'
			AND status = '1'
		ORDER BY dateadded DESC
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$show['portfolioabuse'] = true;
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$res['action'] = '<a href="' . $ilpage['subscribers'] . '?cmd=abuse&amp;subcmd=dismiss&amp;abuseid=' . $res['abuseid'] . '">{_dismiss}</a>';
			$res['fullregarding'] = trim(addslashes(nl2br(strip_tags($res['regarding']))));
			$res['regarding'] = short_string($res['regarding'], 75, $symbol = ' .....');
			$res['ref'] = '<a href="' . HTTPS_SERVER . $ilpage['attachment'] . '?id=' . $res['itemid'] . '" target="_blank">' . $res['itemid'] . '</a>';
			$res['dateadded'] = print_date($res['dateadded']);
			$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$portfolioabuse[] = $res;
			$row_count++;
		}        
	}
	// #### profiles abuse #########################################
	$show['profilesabuse'] = false;
	$row_count = 0;
	$sql = $ilance->db->query("
		SELECT abuseid, regarding, username, email, itemid, status, dateadded, type
		FROM " . DB_PREFIX . "abuse_reports
		WHERE abusetype = 'profile'
			AND status = '1'
		ORDER BY dateadded DESC
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$show['profilesabuse'] = true;
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$res['action'] = '<a href="' . $ilpage['subscribers'] . '?cmd=abuse&amp;subcmd=dismiss&amp;abuseid=' . $res['abuseid'] . '">{_dismiss}</a>';
			$res['fullregarding'] = trim(addslashes(nl2br(strip_tags($res['regarding']))));
			$res['regarding'] = short_string($res['regarding'], 75, $symbol = ' .....');
			$res['ref'] = '<a href="' . HTTP_SERVER . $ilpage['members'] . '?id=' . $res['itemid'] . '" target="_blank">' . $res['itemid'] . '</a>';
			$res['dateadded'] = print_date($res['dateadded']);
			$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$profilesabuse[] = $res;
			$row_count++;
		}        
	}
	// #### pmb abuse ##############################################
	$show['pmbabuse'] = false;
	$row_count = 0;
	$sql = $ilance->db->query("
		SELECT abuseid, regarding, username, email, itemid, status, dateadded, type
		FROM " . DB_PREFIX . "abuse_reports
		WHERE abusetype = 'pmb'
			AND status = '1'
		ORDER BY dateadded DESC
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$show['pmbabuse'] = true;
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$res['action'] = '<a href="' . $ilpage['subscribers'] . '?cmd=abuse&amp;subcmd=dismiss&amp;abuseid=' . $res['abuseid'] . '">' . $pjrase['_dismiss'] . '</a>';
			$res['fullregarding'] = trim(addslashes(nl2br(strip_tags($res['regarding']))));
			$res['regarding'] = short_string($res['regarding'], 75, $symbol = ' .....');
			$res['ref'] = '<a href="' . HTTPS_SERVER . $ilpage['attachment'] . '?id=' . $res['itemid'] . '" target="_blank">' . $res['itemid'] . '</a>';
			$res['dateadded'] = print_date($res['dateadded']);
			$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$pmbabuse[] = $res;
			$row_count++;
		}
	}
	$pprint_array = array('submit','subcmd','subcat_pulldown','question_inputtype_pulldown','questionid','cid','slng','categoryname','language_pulldown','slng','checked_question_cansearch','checked_question_active','checked_question_required','subcategory_pulldown','formdefault','multiplechoice','question','description','formname','sort','submit_category_question','question_id_hidden','question_subcmd','question_inputtype_pulldown','subcatid','subcatname','catname','service_subcategories','product_categories','subcmd','id','submit','description','name','checked_profile_group_active');
	
	($apihook = $ilance->api('admincp_abuse_reports_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'abuse_reports.html', 1);               
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
	$ilance->template->parse_loop('main', 'listingabuse');
	$ilance->template->parse_loop('main', 'biddingabuse');
	$ilance->template->parse_loop('main', 'portfolioabuse');
	$ilance->template->parse_loop('main', 'profilesabuse');
	$ilance->template->parse_loop('main', 'feedbackabuse');
	$ilance->template->parse_loop('main', 'pmbabuse');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
	
}
// #### SKILLS MANAGER #################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'skills')
{
	include_once(DIR_ADMIN . 'subscribers_skills.php');
}
// #### remove custom registration answers for customer ########################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-registration-answer' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['uid']) AND $ilance->GPC['uid'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
	    print_action_failed('{_demo_mode_only}', $ilpage['components']);
	    exit();
	}
	$ilance->db->query("
		DELETE FROM " . DB_PREFIX . "register_answers
		WHERE answerid = '" . intval($ilance->GPC['id']) . "'
		AND user_id = '".intval($ilance->GPC['uid'])."'
	");
	
	refresh(HTTPS_SERVER_ADMIN . $ilpage['subscribers'] . '?subcmd=_update-customer&id=' . intval($ilance->GPC['uid']));
	exit();
}
// #### remove custom profile answers for customer #############################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-profile-answer' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['uid']) AND $ilance->GPC['uid'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
	    print_action_failed('{_demo_mode_only}', $ilpage['components']);
	    exit();
	}
	$ilance->db->query("
		DELETE FROM " . DB_PREFIX . "profile_answers
		WHERE answerid = '" . intval($ilance->GPC['id']) . "'
		AND user_id = '" . intval($ilance->GPC['uid']) . "'
	");
	
	refresh(HTTPS_SERVER_ADMIN . $ilpage['subscribers'] . '?subcmd=_update-customer&id=' . intval($ilance->GPC['uid']));
	exit();
}
// #### remove single user #####################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'deleteuser' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
	    print_action_failed('{_demo_mode_only}', $ilpage['components']);
	    exit();
	}
	// empty inline cookie
	set_cookie('inlinemembers', '', false);
	
	$removedusers = $ilance->admincp_users->remove_user(array($ilance->GPC['id']));
	if (!empty($removedusers))
	{
		$removedusers = mb_substr($removedusers, 0, -2);
		print_action_success('{_the_selected_users_were_removed_from_the_marketplace_indefinately}' . " " . $removedusers . ". " . '{_these_customers_will_not_be_able_to_login_to_the_marketplace_unless}', $ilpage['subscribers']);
		exit();
	}
	else
	{
		print_action_failed('{_the_selected_user_was_not_found_to_be_removed_from_the_marketplace}', $ilpage['subscribers']);
		exit();	
	}
}
// #### remove multiple users ##########################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'deleteusers')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
	    print_action_failed('{_demo_mode_only}', $ilpage['components']);
	    exit();
	}
	// empty inline cookie
	set_cookie('inlinemembers', '', false);
	
	if (isset($ilance->GPC['user_id']) AND is_array($ilance->GPC['user_id']) AND count($ilance->GPC['user_id']) > 0)
	{
		$removedusers = $ilance->admincp_users->remove_user($ilance->GPC['user_id']);
		if (!empty($removedusers))
		{
			$removedusers = mb_substr($removedusers, 0, -2);
			
			print_action_success('{_the_selected_users_were_removed_from_the_marketplace_indefinately}' . " " . $removedusers . ". " . '{_these_customers_will_not_be_able_to_login_to_the_marketplace_unless}', $ilpage['subscribers']);
			exit();
		}
		else
		{
			print_action_failed('{_no_customers_were_selected_for_removal_please_try_again}', $ilpage['subscribers']);
			exit();	
		}
	}
	else
	{
		print_action_failed('{_no_customers_were_selected_for_removal_please_try_again}', $ilpage['subscribers']);
		exit();
	}
}
// #### REMOVE SUBSCRIPTION PERMISSION EXEMPTION #######################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-exemption' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['uid']) AND $ilance->GPC['uid'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
	    print_action_failed('{_demo_mode_only}', $ilpage['components']);
	    exit();
	}
	$ilance->db->query("
		DELETE FROM " . DB_PREFIX . "subscription_user_exempt
		WHERE exemptid = '" . intval($ilance->GPC['id']) . "'
		    AND user_id = '" . intval($ilance->GPC['uid']) . "'
		LIMIT 1
	");
	
	print_action_success('{_the_selected_exemption_was_removed_from_the_customers_subscription}', $ilpage['subscribers'].'?subcmd=_update-customer&amp;id='.intval($ilance->GPC['uid']));
	exit();
}
// #### suspend customer ###############################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'suspenduser' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
	    print_action_failed('{_demo_mode_only}', $ilpage['components']);
	    exit();
	}
	// empty inline cookie
	set_cookie('inlinemembers', '', false);
	
	$suspendusers = $ilance->admincp_users->suspend_user(array($ilance->GPC['id']));
	if (!empty($suspendusers))
	{
		$suspendusers = mb_substr($suspendusers, 0, -2);
		print_action_success('{_the_selected_users_have_been_suspended}'.' '.$suspendusers, $ilpage['subscribers']);
		exit();
	}
	else
	{
		print_action_failed('{_could_not_suspend_one_or_more_users_please_try_again}', $ilpage['subscribers']);
		exit();	
	}
}
// #### suspend customers ##############################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'suspendusers')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
	    print_action_failed('{_demo_mode_only}', $ilpage['components']);
	    exit();
	}
	// empty inline cookie
	set_cookie('inlinemembers', '', false);
	
	if (isset($ilance->GPC['user_id']) AND is_array($ilance->GPC['user_id']))
	{
		$suspendusers = $ilance->admincp_users->suspend_user($ilance->GPC['user_id']);
		if (!empty($suspendusers))
		{
			$suspendusers = mb_substr($suspendusers, 0, -2);
			print_action_success('{_the_selected_users_have_been_suspended}'.' '.$suspendusers, $ilpage['subscribers']);
			exit();
		}
		else
		{
			print_action_failed('{_could_not_suspend_one_or_more_users_please_try_again}', $ilpage['subscribers']);
			exit();	
		}
	}
	else
	{
		print_action_failed('{_could_not_suspend_one_or_more_users_please_try_again}', $ilpage['subscribers']);
		exit();		
	}
}
// #### ban users ######################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'banusers')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
	    print_action_failed('{_demo_mode_only}', $ilpage['components']);
	    exit();
	}
	// empty inline cookie
	set_cookie('inlinemembers', '', false);
	
	if (isset($ilance->GPC['user_id']) AND is_array($ilance->GPC['user_id']))
	{
		$bannedusers = $ilance->admincp_users->ban_user($ilance->GPC['user_id']);
		if (!empty($bannedusers))
		{
			$bannedusers = mb_substr($bannedusers, 0, -2);
			print_action_success('{_the_selected_users_have_been_banned}'.' '.$bannedusers, $ilpage['subscribers']);
			exit();
		}
		else
		{
			print_action_failed('{_could_not_place_a_ban_one_or_more_users_please_try_again}', $ilpage['subscribers']);
			exit();	
		}
	}
	else
	{
		print_action_failed('{_could_not_place_a_ban_one_or_more_users_please_try_again}', $ilpage['subscribers']);
		exit();		
	}
}
// #### cancel users ###################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'cancelusers')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
	    print_action_failed('{_demo_mode_only}', $ilpage['components']);
	    exit();
	}
	// empty inline cookie
	set_cookie('inlinemembers', '', false);
	
	if (isset($ilance->GPC['user_id']) AND is_array($ilance->GPC['user_id']))
	{
		$cancelledusers = $ilance->admincp_users->cancel_user($ilance->GPC['user_id']);
		if (!empty($cancelledusers))
		{
			$cancelledusers = mb_substr($cancelledusers, 0, -2);
			print_action_success('{_the_selected_users_have_been_cancelled}'.' '.$cancelledusers, $ilpage['subscribers']);
			exit();
		}
		else
		{
			print_action_failed('{_could_not_cancel_one_or_more_users_please_try_again}', $ilpage['subscribers']);
			exit();	
		}
	}
	else
	{
		print_action_failed('{_could_not_cancel_one_or_more_users_please_try_again}', $ilpage['subscribers']);
		exit();		
	}
}
// #### activate user ##################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'unsuspenduser' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
	    print_action_failed('{_demo_mode_only}', $ilpage['components']);
	    exit();
	}
	// empty inline cookie
	set_cookie('inlinemembers', '', false);
	
	$unsuspendusers = $ilance->admincp_users->unsuspend_user(array($ilance->GPC['id']));
	if (!empty($unsuspendusers))
	{
		$unsuspendusers = mb_substr($unsuspendusers, 0, -2);
		print_action_success('{_the_selected_users_have_been_activated} ' . $unsuspendusers, $ilpage['subscribers']);
		exit();
	}
	else
	{
		print_action_failed('{_could_not_activate_one_or_more_users_please_try_again}', $ilpage['subscribers']);
		exit();	
	}
}
// #### unsuspend multiple users ########################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'unsuspendusers')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
	    print_action_failed('{_demo_mode_only}', $ilpage['components']);
	    exit();
	}
	// empty inline cookie
	set_cookie('inlinemembers', '', false);
		
	if (isset($ilance->GPC['user_id']) AND is_array($ilance->GPC['user_id']))
	{
		$activatedusers = $ilance->admincp_users->unsuspend_user($ilance->GPC['user_id']);
		if (!empty($activatedusers))
		{
			$activatedusers = mb_substr($activatedusers, 0, -2);
			
			print_action_success('{_the_selected_users_have_been_activated} ' . $activatedusers, $ilpage['subscribers']);
			exit();
		}
		else
		{
			print_action_failed('{_could_not_activate_one_or_more_users_please_try_again}', $ilpage['subscribers']);
			exit();	
		}
	}
	else
	{
		print_action_failed('{_could_not_activate_one_or_more_users_please_try_again}', $ilpage['subscribers']);
		exit();
	}
}
// #### activate multiple users ########################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'activateusers')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
	    print_action_failed('{_demo_mode_only}', $ilpage['components']);
	    exit();
	}
	// empty inline cookie
	set_cookie('inlinemembers', '', false);
		
	if (isset($ilance->GPC['user_id']) AND is_array($ilance->GPC['user_id']))
	{
		$activatedusers = $ilance->admincp_users->activate_user($ilance->GPC['user_id']);
		if (!empty($activatedusers))
		{
			$activatedusers = mb_substr($activatedusers, 0, -2);
			
			print_action_success('{_the_selected_users_have_been_activated}'.' '.$activatedusers, $ilpage['subscribers']);
			exit();
		}
		else
		{
			print_action_failed('{_could_not_activate_one_or_more_users_please_try_again}', $ilpage['subscribers']);
			exit();	
		}
	}
	else
	{
		print_action_failed('{_could_not_activate_one_or_more_users_please_try_again}', $ilpage['subscribers']);
		exit();
	}
}
// #### unverify multiple users ########################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'unverifyusers')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
	    print_action_failed('{_demo_mode_only}', $ilpage['components']);
	    exit();
	}
	// empty inline cookie
	set_cookie('inlinemembers', '', false);
		
	if (isset($ilance->GPC['user_id']) AND is_array($ilance->GPC['user_id']))
	{
		$unverifiedusers = $ilance->admincp_users->unverify_user($ilance->GPC['user_id']);
		if (!empty($unverifiedusers))
		{
			$unverifiedusers = mb_substr($unverifiedusers, 0, -2);
			
			print_action_success('{_the_selected_users_have_been_unverified_and_will_need_to_verify_their_email_again_to_become_activated}'.' '.$unverifiedusers, $ilpage['subscribers']);
			exit();
		}
		else
		{
			print_action_failed('{_could_not_unverify_one_or_more_users_please_try_again}', $ilpage['subscribers']);
			exit();	
		}
	}
	else
	{
		print_action_failed('{_could_not_unverify_one_or_more_users_please_try_again}', $ilpage['subscribers']);
		exit();
	}
}
// #### CREATE NEW CUSTOMER ############################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_create-new-customer')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
	    print_action_failed('{_demo_mode_only}', $ilpage['components']);
	    exit();
	}
	
	($apihook = $ilance->api('admincp_subscribers_create_new_customer_start')) ? eval($apihook) : false;
	
	if (!isset($ilance->GPC['password']) OR !isset($ilance->GPC['password2']) OR empty($ilance->GPC['password']) OR empty($ilance->GPC['password2']) OR $ilance->GPC['password'] != $ilance->GPC['password2'])  
	{
		print_action_failed('{_passwords_are_empty_or_do_not_match}', $ilpage['subscribers']);
		exit();
	}
	
	if (!isset($ilance->GPC['username']) OR empty($ilance->GPC['username']))
	{
		print_action_failed('{_please_enter_correct_username}', $ilpage['subscribers']);
		exit();
	}     
	
	if (!isset($ilance->GPC['email']) OR empty($ilance->GPC['email'])) 
	{         
		print_action_failed('{_please_enter_correct_email}', $ilpage['subscribers']);
		exit();
	}
	
	$unicode_name = preg_replace('/&#([0-9]+);/esiU', "convert_int2utf8('\\1')", $ilance->GPC['username']);
	if ($ilance->common->is_username_banned($ilance->GPC['username']) OR $ilance->common->is_username_banned($unicode_name))
	{
		print_action_failed('{_this_username_is_banned}', $ilpage['subscribers']);
		exit();
	}
		
	$sql = $ilance->db->query("
		SELECT locationid
		FROM " . DB_PREFIX . "locations
		WHERE location_" . $_SESSION['ilancedata']['user']['slng'] . " = '" . $ilance->db->escape_string($ilance->GPC['country']) . "'
		LIMIT 1
	");
	$res = $ilance->db->fetch_array($sql, DB_ASSOC);

	if (empty($ilance->GPC['secretanswer']))
	{
		$ilance->GPC['secretanswer'] = $ilance->GPC['email'];
		$ilance->GPC['secretquestion'] = "{_what_is_my_email_address}";
		$secretanswer = md5($ilance->GPC['secretanswer']);
	}
	else if (empty($ilance->GPC['secretquestion']))
	{
		$ilance->GPC['secretanswer'] = $ilance->GPC['email'];
		$ilance->GPC['secretquestion'] = "{_what_is_my_email_address}";
		$secretanswer = md5($ilance->GPC['secretanswer']);
	}
	else
	{
		$secretanswer = md5($ilance->GPC['secretanswer']);
	}
	
	if (empty($ilance->GPC['companyname']))
	{
		$ilance->GPC['companyname'] = '';
	}
	
	$salt = construct_password_salt(5);
	$pass = md5(md5($ilance->GPC['password']) . $salt);
	$ilance->GPC['isadmin'] = ((isset($ilance->GPC['isadmin']) AND $ilance->GPC['isadmin']) ? 1 : 0);
	$ilance->GPC['languageid'] = isset($ilance->GPC['languageid']) ? $ilance->GPC['languageid'] : $ilance->language->fetch_default_languageid();
	
	($apihook = $ilance->api('admincp_subscribers_create_new_customer_fields')) ? eval($apihook) : false;
	
	$newuserid = $ilance->admincp_users->construct_new_member(
		$ilance->GPC['username'],
		$pass,
		$salt,
		$ilance->GPC['secretquestion'],
		$secretanswer,
		$ilance->GPC['email'],
		$ilance->GPC['firstname'],
		$ilance->GPC['lastname'],
		$ilance->GPC['address'],
		$ilance->GPC['address2'],
		$ilance->GPC['city'],
		$ilance->GPC['state'],
		$ilance->GPC['zipcode'],
		$ilance->GPC['phone'],
		$res['locationid'],
		'0000-00-00',
		$ilance->referral->create_referral_code(6),
		$ilance->GPC['languageid'],
		$ilconfig['globalserverlocale_defaultcurrency'],
		$ilconfig['globalserverlocale_sitetimezone'],
		'',
		$ilance->GPC['isadmin']
	);
	
	if ($newuserid > 0)
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "users
			SET companyname = '" . $ilance->db->escape_string($ilance->GPC['companyname']) . "'
			WHERE user_id = '" . $newuserid . "'
			LIMIT 1
		");
		
		($apihook = $ilance->api('admincp_subscribers_create_new_customer_sql')) ? eval($apihook) : false;
	}
	
	// is account bonus active?
	$accountbonus = 0;
	if ($ilconfig['registrationupsell_bonusactive'] AND empty($ilance->GPC['bonusdisable']))
	{
		$accountbonus = $ilance->accounting->construct_account_bonus($newuserid, 'active');
	}
	// role id
	$ilance->GPC['roleid'] = isset($ilance->GPC['roleid']) ? intval($ilance->GPC['roleid']) : '-1';
	$ilance->registration->build_user_subscription($newuserid, intval($ilance->GPC['subscriptionid']), 'account', '', $ilance->GPC['roleid']);
	if (isset($ilance->GPC['notifyregister']) AND $ilance->GPC['notifyregister'])
	{
		$categories = '';
		if ($ilconfig['globalauctionsettings_productauctionsenabled'])
		{
			$getcats = $ilance->db->query("
				SELECT cid, title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title
				FROM " . DB_PREFIX . "categories
				WHERE parentid = '0'
					AND cattype = 'product'
					AND visible = '1'
				ORDER BY title_" . $_SESSION['ilancedata']['user']['slng'] . " ASC
				LIMIT 10
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($getcats) > 0)
			{
				while ($res = $ilance->db->fetch_array($getcats, DB_ASSOC))
				{
					$categories .= $res['title'] . LINEBREAK;
				}
			}
		}
		if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
		{
			$getcats = $ilance->db->query("
				SELECT cid, title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title
				FROM " . DB_PREFIX . "categories
				WHERE parentid = '0'
					AND cattype = 'service'
					AND visible = '1'
				ORDER BY title_" . $_SESSION['ilancedata']['user']['slng'] . " ASC
				LIMIT 10
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($getcats) > 0)
			{
				while ($res = $ilance->db->fetch_array($getcats, DB_ASSOC))
				{
					$categories .= $res['title'] . LINEBREAK;
				}
			}
		}
		$ilance->email->mail = $ilance->GPC['email'];
		$ilance->email->slng = fetch_user_slng($newuserid);
		$ilance->email->get('register_welcome_email_admincp');		
		$ilance->email->set(array(
			'{{username}}' => $ilance->GPC['username'],
			'{{user_id}}' => $newuserid,
			'{{first_name}}' => $ilance->GPC['firstname'],
			'{{last_name}}' => $ilance->GPC['lastname'],
			'{{phone}}' => $ilance->GPC['phone'],
			'{{categories}}' => $categories
		));
		$ilance->email->send();
	}
	if (isset($ilance->GPC['notifywelcome']) AND $ilance->GPC['notifywelcome'])
	{
		$ilance->email->mail = SITE_EMAIL;
		$ilance->email->slng = fetch_site_slng();
		$ilance->email->get('register_welcome_email_admin_admincp');		
		$ilance->email->set(array(
			'{{username}}' => $ilance->GPC['username'],
			'{{user_id}}' => $newuserid,
			'{{first_name}}' => $ilance->GPC['firstname'],
			'{{last_name}}' => $ilance->GPC['lastname'],
			'{{phone}}' => $ilance->GPC['phone'],
			'{{emailaddress}}' => $ilance->GPC['email'],
		));
		$ilance->email->send();
	}
	
	($apihook = $ilance->api('admincp_subscribers_create_new_customer_end')) ? eval($apihook) : false;
	
	print_action_success('{_the_new_customer_was_created_the_new_customer_will_be_required_to}', $ilance->GPC['return']);
	exit();
}
// #### UPDATE CUSTOMER PROFILE ################################################	
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-customer-profile' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$country = $ilance->db->escape_string($ilance->GPC['country']);
	$ilance->GPC['locationid'] = intval(fetch_country_id($country));
	
	($apihook = $ilance->api('update_customer_profile_start')) ? eval($apihook) : false;  
	
	$ipres = ((isset($ilance->GPC['iprestrict']) AND $ilance->GPC['iprestrict']) ? '1' : '0');
	$passwordsql = '';
	if (!empty($ilance->GPC['password']))
	{
		$newsalt = construct_password_salt($length = 5);
		$newpassword = md5(md5($ilance->db->escape_string($ilance->GPC['password'])) . $newsalt);
		$passwordsql = "password = '" . $newpassword . "',";
		$passwordsql .= "salt = '" . $newsalt . "',";
		$passwordsql .= "password_lastchanged = '" . DATETIME24H . "',";
	}
	$status = isset($ilance->GPC['status']) ? $ilance->db->escape_string($ilance->GPC['status']) : 'unverified';
	$dob = isset($ilance->GPC['dob']) ? $ilance->db->escape_string($ilance->GPC['dob']) : '0000-00-00';
	$isadmin = isset($ilance->GPC['isadmin']) ? intval($ilance->GPC['isadmin']) : '0';
	$posthtml = isset($ilance->GPC['posthtml']) ? intval($ilance->GPC['posthtml']) : '0';
	// detect if admin is changing status from 'moderated' to 'active'
	$oldstatus = fetch_user('status', intval($ilance->GPC['id']));
	$username_history = fetch_user('username_history', intval($ilance->GPC['id']));
	if ($oldstatus == 'moderated' AND $status == 'active')
	{
		$activatedusers = $ilance->admincp_users->activate_user(array($ilance->GPC['id']));
	}
	// quick username checkup
	$show['error_username'] = false;
	if (isset($ilance->GPC['username']) AND $ilance->GPC['username'] != '')
	{
		//$unicode_name = preg_replace('/&#([0-9]+);/esiU', "convert_int2utf8('\\1')", $ilance->GPC['username']);
		$unicode_name = $ilance->GPC['username'];
		// username ban checkup
		if ($ilance->common->is_username_banned($ilance->GPC['username']) OR $ilance->common->is_username_banned($unicode_name))
		{
			$show['error_username'] = true;
		}
		else
		{
			$sqlusercheck = $ilance->db->query("
				SELECT user_id
				FROM " . DB_PREFIX . "users
				WHERE username IN ('" . $ilance->db->escape_string(htmlspecialchars_uni($ilance->GPC['username'])) . "', '" . $ilance->db->escape_string(htmlspecialchars_uni($unicode_name)) . "')
					AND user_id != '" . intval($ilance->GPC['id']) . "'
			");
			if ($ilance->db->num_rows($sqlusercheck) > 0)
			{
				$show['error_username'] = true;
			}
			else
			{
				if ($ilance->GPC['oldusername'] != $ilance->GPC['username'])
				{
					if (!empty($username_history))
					{
						$username_history = unserialize($username_history);
						$username_history[] = array(
							'username' => $ilance->GPC['oldusername'],
							'datetime' => DATETIME24H
						);
						$username_history = serialize($username_history);
					}
					else
					{
						$username_history = array(array(
							'username' => $ilance->GPC['oldusername'],
							'datetime' => DATETIME24H)
						);
						$username_history = serialize($username_history);
					}
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "abuse_reports
						SET username = '" . $ilance->db->escape_string($ilance->GPC['username']) . "'
						WHERE username = '" . $ilance->db->escape_string($ilance->GPC['oldusername']) . "'
					");
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "feedback
						SET for_username = '" . $ilance->db->escape_string($ilance->GPC['username']) . "'
						WHERE for_username = '" . $ilance->db->escape_string($ilance->GPC['oldusername']) . "'
					");
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "feedback
						SET from_username = '" . $ilance->db->escape_string($ilance->GPC['username']) . "'
						WHERE from_username = '" . $ilance->db->escape_string($ilance->GPC['oldusername']) . "'
					");
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "feedback_response
						SET for_username = '" . $ilance->db->escape_string($ilance->GPC['username']) . "'
						WHERE for_username = '" . $ilance->db->escape_string($ilance->GPC['oldusername']) . "'
					");
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "feedback_response
						SET from_username = '" . $ilance->db->escape_string($ilance->GPC['username']) . "'
						WHERE from_username = '" . $ilance->db->escape_string($ilance->GPC['oldusername']) . "'
					");
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "messages
						SET username = '" . $ilance->db->escape_string($ilance->GPC['username']) . "'
						WHERE username = '" . $ilance->db->escape_string($ilance->GPC['oldusername']) . "'
					");
					
					($apihook = $ilance->api('update_customer_profile_change_username')) ? eval($apihook) : false;
					
				}
			}
		}
	}
	else
	{
		$show['error_username'] = true;
	}
	if ($show['error_username'])
	{
		print_action_failed('{_sorry_the_username_you_entered_appears_to_be_in_the_username_ban_list}', $ilpage['subscribers'] . '?subcmd=_update-customer&id=' . intval($ilance->GPC['id']));
		exit();
	}
	$gendersql = '';
	if ($ilconfig['genderactive'] AND isset($ilance->GPC['gender']) AND !empty($ilance->GPC['gender']))
	{
		$gendersql = "gender = '" . $ilance->db->escape_string($ilance->GPC['gender']) . "',";
	}
	if (empty($ilance->GPC['secretanswer']))
	{
		$ilance->GPC['secretanswer'] = $ilance->GPC['email'];
		$ilance->GPC['secretquestion'] = '{_what_is_my_email_address}';
		$secretanswer = md5($ilance->GPC['secretanswer']);
	}
	else if (empty($ilance->GPC['secretquestion']))
	{
		$ilance->GPC['secretanswer'] = $ilance->GPC['email'];
		$ilance->GPC['secretquestion'] = '{_what_is_my_email_address}';
		$secretanswer = md5($ilance->GPC['secretanswer']);
	}
	else
	{
		$secretanswer = md5($ilance->GPC['secretanswer']);
	}
	$ilance->GPC['languageid'] = isset($ilance->GPC['languageid']) ? $ilance->GPC['languageid'] : $ilance->language->fetch_default_languageid();
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "users
		SET username = '" . $ilance->db->escape_string($ilance->GPC['username']) . "',
		$passwordsql
		email = '" . $ilance->db->escape_string($ilance->GPC['email']) . "',
		first_name = '" . $ilance->db->escape_string($ilance->GPC['first_name']) . "',
		last_name = '" . $ilance->db->escape_string($ilance->GPC['last_name']) . "',
		address = '" . $ilance->db->escape_string($ilance->GPC['address']) . "',
		address2 = '" . $ilance->db->escape_string($ilance->GPC['address2']) . "',
		city = '" . $ilance->db->escape_string($ilance->GPC['city']) . "',
		state = '" . $ilance->db->escape_string($ilance->GPC['state']) . "',
		zip_code = '" . $ilance->db->escape_string($ilance->GPC['zip_code']) . "',
		timezone = '" . $ilance->db->escape_string($ilance->GPC['timezone']) . "',
		phone = '" . $ilance->db->escape_string($ilance->GPC['phone']) . "',
		country = '" . $ilance->GPC['locationid'] . "',
		ipaddress = '" . $ilance->db->escape_string($ilance->GPC['ipaddress']) . "',
		iprestrict = '" . $ipres . "',
		status = '" . $status . "',
		dob = '" . $dob . "',
		secretquestion = '" . $ilance->db->escape_string($ilance->GPC['secretquestion']) . "',
		secretanswer = '" . $ilance->db->escape_string($secretanswer) . "',
		$gendersql
		isadmin = '" . $isadmin . "',
		posthtml = '" . $posthtml . "',
		username_history = '" . $ilance->db->escape_string($username_history) . "',
		languageid = '" . intval($ilance->GPC['languageid']) . "'
		WHERE user_id = '" . intval($ilance->GPC['id']) . "'
	");
	
	($apihook = $ilance->api('update_customer_profile_end')) ? eval($apihook) : false;
	
	// handle role change if required
	if (isset($ilance->GPC['roleid']) AND $ilance->GPC['roleid'] > 0)
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "subscription_user
			SET roleid = '" . intval($ilance->GPC['roleid']) . "'
			WHERE user_id = '" . intval($ilance->GPC['id']) . "'
		");
	}
	// registration question answers
	if (!empty($ilance->GPC['custom1']) AND is_array($ilance->GPC['custom1']))
	{
		$ilance->registration->process_custom_register_questions($ilance->GPC['custom1'], intval($ilance->GPC['id']));
	}
	// profile question answers
	if (!empty($ilance->GPC['custom2']) AND is_array($ilance->GPC['custom2']))
	{
		$ilance->profile_questions->process_custom_profile_questions($ilance->GPC['custom2'], intval($ilance->GPC['id']));
	}
	if (isset($ilance->GPC['emailuser']) AND $ilance->GPC['emailuser'])
	{
		$notice = '{_the_customers_profile_has_been_updated_with_new_changes_and_the_password_was_reset}';    
		
		$ilance->email->mail = $ilance->GPC['email'];
		$ilance->email->slng = fetch_user_slng(intval($ilance->GPC['id']));
		$ilance->email->get('update_customer_profile');		
		$ilance->email->set(array(
			'{{username}}' => $ilance->GPC['username'],
			'{{password}}' => $ilance->GPC['password'],
		));
		$ilance->email->send();
	}
	else
	{
		$notice = '{_the_customers_profile_has_been_updated_with_new_changes_changes}';
	}
	log_event($_SESSION['ilancedata']['user']['userid'], $ilpage['subscribers'], $ilance->GPC['cmd'], $ilance->GPC['subcmd'], $ilance->GPC['id']);
	print_action_success($notice, $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . intval($ilance->GPC['id']));
	exit();
}
// #### SUBSCRIPTION PLAN RE-ASSIGNMENT ########################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_create-subscription' AND $ilance->GPC['id'] > 0)
{
	$ilance->subscription->subscription_upgrade_process_admincp(intval($ilance->GPC['id']), intval($ilance->GPC['subscriptionid']), $ilance->GPC['txndescription'], $ilance->GPC['action']);	
	print_action_success('{_the_customer_has_been_reassigned_with_the_selected_subscription_plan}', $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . intval($ilance->GPC['id']));
	exit();
}
// #### ADMIN ASSIGNS NEW SUBSCRIPTION EXEMPTION PERMISSION TO MEMBER
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_create-exemption' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	if ($ilance->subscription->construct_subscription_exemption(intval($ilance->GPC['id']), $ilance->GPC['accessname'], $ilance->GPC['exemptvalue'], $ilance->GPC['exemptcost'], $ilance->GPC['exemptdays'], $ilance->GPC['logic'], $ilance->GPC['description']))
	{
		print_action_success('{_the_customer_has_been_assigned_with_the_selected_subscription_permission_exemption}', $ilpage['subscribers'].'?subcmd=_update-customer&amp;id='.intval($ilance->GPC['id']));
		exit();
	}
	else 
	{
		print_action_failed('{_there_was_a_problem_with_the_action_selected_this_may_be_due_to_the_customer_not_having_sufficient_funds}', $ilpage['subscribers'].'?subcmd=_update-customer&amp;id='.intval($ilance->GPC['id']));
		exit();
	}
}
// #### MANUALLY AUTHORIZE CUSTOMER CREDIT CARD FOR USAGE ######################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_authorize-creditcard' AND $ilance->GPC['id'] > 0 AND $ilance->GPC['uid'] > 0)
{
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "creditcards
		SET authorized = 'yes'
		WHERE cc_id = '" . intval($ilance->GPC['id']) . "'
		LIMIT 1
	");
	if (isset($ilance->GPC['ccmgr']) AND $ilance->GPC['ccmgr'] == 1)
	{
		print_action_success('{_the_selected_credit_card_was_manually_authorized_verified_from_administration}', $ilpage['accounting'] . '?cmd=creditcards');
		exit();
	}
	else
	{
		print_action_success('{_the_selected_credit_card_was_manually_authorized_verified_from_administration}', $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . intval($ilance->GPC['uid']));
		exit();
	}
}
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_unauthorize-creditcard' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0  AND isset($ilance->GPC['uid']) AND $ilance->GPC['uid'] > 0)
{
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "creditcards
		SET authorized = 'no'
		WHERE cc_id = '" . intval($ilance->GPC['id']) . "'
		LIMIT 1
	");
	if (isset($ilance->GPC['ccmgr']) AND $ilance->GPC['ccmgr'] == 1)
	{
		print_action_success('{_the_selected_credit_card_was_manually_unauthorized_and_this_customer_will_be_required_to_manually_verify}', $ilpage['accounting'] . '?cmd=creditcards');
		exit();
	}
	else
	{
		print_action_success('{_the_selected_credit_card_was_manually_unauthorized_and_this_customer_will_be_required_to_manually_verify}', $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . intval($ilance->GPC['uid']));
		exit();
	}
}
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-creditcard' AND $ilance->GPC['id'] > 0 AND $ilance->GPC['uid'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
	    print_action_failed('{_demo_mode_only}', $ilpage['components']);
	    exit();
	}
	$ilance->db->query("
		DELETE FROM " . DB_PREFIX . "creditcards
		WHERE cc_id = '" . intval($ilance->GPC['id']) . "'
		LIMIT 1
	");
	if (isset($ilance->GPC['ccmgr']) AND $ilance->GPC['ccmgr'] == 1)
	{
		print_action_success('{_the_selected_credit_card_was_removed_from_the_customers_profile_this_customer_will_be_required_to_verify_any}', $ilpage['accounting'] . '?cmd=creditcards');
		exit();
	}
	else
	{
		print_action_success('{_the_selected_credit_card_was_removed_from_the_customers_profile_this_customer_will_be_required_to_verify_any}', $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . intval($ilance->GPC['uid']));
		exit();
	}
}
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-bankaccount' AND $ilance->GPC['id'] > 0 AND $ilance->GPC['uid'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
	    print_action_failed('{_demo_mode_only}', $ilpage['components']);
	    exit();
	}
	$ilance->db->query("
		DELETE FROM " . DB_PREFIX . "bankaccounts
		WHERE bank_id = '" . intval($ilance->GPC['id']) . "'
	");
	print_action_success('{_the_selected_bank_account_was_removed_from_the_customers_profile}', $ilpage['subscribers'].'?subcmd=_update-customer&amp;id='.intval($ilance->GPC['uid']));
	exit();
}
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-transaction' AND $ilance->GPC['id'] > 0 AND $ilance->GPC['uid'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
	    print_action_failed('{_demo_mode_only}', $ilpage['components']);
	    exit();
	}
	$ilance->db->query("
		DELETE FROM " . DB_PREFIX . "invoices
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	print_action_success('{_the_selected_transaction_was_removed_from_the_transaction_system}', $ilpage['subscribers'].'?subcmd=_update-customer&amp;id='.intval($ilance->GPC['uid']));
	exit();
}
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_create-transaction' AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['action']) AND isset($ilance->GPC['amount']))
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$ilance->GPC['custom'] = isset($ilance->GPC['custom']) ? $ilance->GPC['custom'] : '';
	$ilance->GPC['amount'] = $ilance->currency->string_to_number($ilance->GPC['amount']);
	if ($ilance->GPC['amount'] > 0)
	{
		if ($ilance->GPC['action'] == 'debit')
		{
			$ilance->GPC['description'] = !empty($ilance->GPC['description']) ? $ilance->GPC['description'] : '{_account_debit}';
			$sql = $ilance->db->query("
				SELECT available_balance, total_balance
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . intval($ilance->GPC['id']) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$new_debit_amount = sprintf("%01.2f", $ilance->GPC['amount']);
				$total_now = $res['total_balance'];
				$avail_now = $res['available_balance'];
				$new_total_now = ($total_now - $new_debit_amount);
				$new_avail_now = ($avail_now - $new_debit_amount);
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "users
					SET total_balance = '" . $new_total_now . "',
					available_balance = '" . $new_avail_now . "',
					income_reported = income_reported - $new_debit_amount
					WHERE user_id = '" . intval($ilance->GPC['id']) . "'
					LIMIT 1
				");
				$ilance->accounting->insert_transaction(
					0,
					0,
					0,
					intval($ilance->GPC['id']),
					0,
					0,
					0,
					$ilance->GPC['description'],
					sprintf("%01.2f", $new_debit_amount),
					sprintf("%01.2f", $new_debit_amount),
					'paid',
					'debit',
					'account',
					DATETIME24H,
					DATEINVOICEDUE,
					DATETIME24H,
					$ilance->GPC['custom'],
					0,
					0,
					0
				);
				$sqlemail = $ilance->db->query("
					SELECT email, username, first_name, last_name
					FROM " . DB_PREFIX . "users
					WHERE user_id = '" . intval($ilance->GPC['id']) . "'
					LIMIT 1
				");
				if ($ilance->db->num_rows($sqlemail) > 0)
				{
					$resemail = $ilance->db->fetch_array($sqlemail, DB_ASSOC);
					$ilance->email->mail = $resemail['email'];
					$ilance->email->slng = fetch_user_slng(intval($ilance->GPC['id']));
					$ilance->email->get('account_debit_notification');		
					$ilance->email->set(array(
						'{{customer}}' => $resemail['username'],
						'{{amount}}' => $ilance->currency->format($ilance->GPC['amount']),
						'{{from}}' => $_SESSION['ilancedata']['user']['username']
					));
					$ilance->email->send();
					refresh(HTTPS_SERVER_ADMIN . $ilpage['subscribers'] . '?subcmd=_update-customer&id=' . intval($ilance->GPC['id']) . '&note=success');
					exit();
				}
			}
		}
		else if ($ilance->GPC['action'] == 'credit')
		{
			$ilance->GPC['description'] = !empty($ilance->GPC['description']) ? $ilance->GPC['description'] : '{_account_credit}';
			$sql = $ilance->db->query("
				SELECT available_balance, total_balance
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . intval($ilance->GPC['id']) . "'
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$new_credit_amount = sprintf("%01.2f", $ilance->GPC['amount']);
				$total_now = $res['total_balance'];
				$avail_now = $res['available_balance'];
				$new_total_now = ($total_now + $new_credit_amount);
				$new_avail_now = ($avail_now + $new_credit_amount);
				if (strchr($avail_now, '-'))
				{
					$new_total_now = ($new_credit_amount + -$total_now);
					$new_avail_now = ($new_credit_amount + -$avail_now);
				}
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "users
					SET total_balance = '" . $ilance->db->escape_string($new_total_now) . "',
					available_balance = '" . $ilance->db->escape_string($new_avail_now) . "',
					income_reported = income_reported + $new_credit_amount
					WHERE user_id = '" . intval($ilance->GPC['id']) . "'
					LIMIT 1
				");
				$ilance->accounting->insert_transaction(
					0,
					0,
					0,
					intval($ilance->GPC['id']),
					0,
					0,
					0,
					$ilance->GPC['description'],
					sprintf("%01.2f", $new_credit_amount),
					sprintf("%01.2f", $new_credit_amount),
					'paid',
					'credit',
					'account',
					DATETIME24H,
					DATEINVOICEDUE,
					DATETIME24H,
					$ilance->GPC['custom'],
					0,
					0,
					0
				);
				$sqlemail = $ilance->db->query("
					SELECT email, username, first_name, last_name
					FROM " . DB_PREFIX . "users
					WHERE user_id = '" . intval($ilance->GPC['id']) . "'
					LIMIT 1
				");
				if ($ilance->db->num_rows($sqlemail) > 0)
				{
					$resemail = $ilance->db->fetch_array($sqlemail, DB_ASSOC);
					$ilance->email->mail = $resemail['email'];
					$ilance->email->slng = fetch_user_slng(intval($ilance->GPC['id']));
					$ilance->email->get('account_credit_notification');		
					$ilance->email->set(array(
						'{{customer}}' => $resemail['username'],
						'{{amount}}' => $ilance->currency->format($ilance->GPC['amount']),
						'{{from}}' => $_SESSION['ilancedata']['user']['username']
					));
					$ilance->email->send();
					refresh(HTTPS_SERVER_ADMIN . $ilpage['subscribers'] . '?subcmd=_update-customer&id=' . intval($ilance->GPC['id']) . '&note=success');
					exit();					
				}
			}
		}
	}
}
// #### CONTACTS IMPORT LOGIC ##################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'contacts-import')
{	
	($apihook = $ilance->api('admincp_subscribers_contacts_import_start')) ? eval($apihook) : false;
}
// #### switch to any user #####################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'switchuser' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	$sql = $ilance->db->query("
		SELECT u.*, su.roleid, su.subscriptionid, su.active, sp.cost, c.currency_name, c.currency_abbrev, l.languagecode
		FROM " . DB_PREFIX . "users AS u
		LEFT JOIN " . DB_PREFIX . "subscription_user su ON u.user_id = su.user_id
		LEFT JOIN " . DB_PREFIX . "subscription sp ON su.subscriptionid = sp.subscriptionid
		LEFT JOIN " . DB_PREFIX . "currency c ON u.currencyid = c.currency_id
		LEFT JOIN " . DB_PREFIX . "language l ON u.languageid = l.languageid
		WHERE u.user_id = '" . intval($ilance->GPC['id']) . "'
		GROUP BY username
		LIMIT 1
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$userinfo = $ilance->db->fetch_array($sql, DB_ASSOC);
		$ilance->sessions->build_user_session($userinfo);
		refresh(HTTP_SERVER);
		exit();
	}
}
// #### update customer profile ################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-customer' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	if (file_exists(DIR_CORE . 'functions_geoip.php') AND file_exists(DIR_CORE . 'functions_geoip_city.dat') AND file_exists(DIR_CORE . 'functions_geoip_country.dat'))
	{
		if (!function_exists('geoip_open'))
		{
			require_once(DIR_CORE . 'functions_geoip.php');
		}
		$geoip = geoip_open(DIR_CORE . 'functions_geoip_city.dat', GEOIP_STANDARD);
		$show['geoip'] = true;
	}
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['subscribers'], $ilpage['subscribers'], $_SESSION['ilancedata']['user']['slng']);
	$show['show_update'] = true;
	$show['show_search'] = $show['referredby'] = false;
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "users
		WHERE user_id = '" . intval($ilance->GPC['id']) . "'
		LIMIT 1
	");
	if ($ilance->db->num_rows($sql) == 0)
	{
		print_action_failed('{_the_user_account_no_longer_exists}', $ilpage['subscribers']);
		exit();
	}
	$show['usernamehistory'] = false;
	while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
	{
		$res['username'] = handle_input_keywords($res['username']);
		$username = $res['username'];
		$usernamehistory = '';
		if (!empty($res['username_history']))
		{
			$res['username_history'] = unserialize($res['username_history']);
			foreach ($res['username_history'] AS $array)
			{
				$usernamehistory .= '<div style="padding-bottom:3px" title="Profile changed on ' . print_date($array['datetime']) . '">' . $array['username'] . '</div>';
			}
			$show['usernamehistory'] = true;
		}
		$res['usernamehistory'] = $usernamehistory;
		$area_title = '{_viewing_customer_profile_id}<div class="smaller">' . $res['username'] . '</div>';
		$page_title = SITE_NAME . ' - {_viewing_customer_profile_id} ' . $res['username'];
		$res['first_name'] = handle_input_keywords($res['first_name']);
		$res['last_name'] = handle_input_keywords($res['last_name']);
		$res['phone'] = handle_input_keywords($res['phone']);
		$res['address'] = handle_input_keywords($res['address']);
		$res['address2'] = handle_input_keywords($res['address2']);
		$res['city'] = handle_input_keywords(ucfirst($res['city']));
		$res['zip_code'] = handle_input_keywords($res['zip_code']);
		$res['restrict'] = ($res['iprestrict'] == '1') ? '<input type="checkbox" name="iprestrict" value="1" checked="checked" />' : '<input type="checkbox" name="iprestrict" value="1" />';
		$res['added'] = print_date($res['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
		$res['lastseen'] = ($res['lastseen'] != '0000-00-00 00:00:00') ? print_date($res['lastseen'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0) : '{_never}';
		$res['localtime'] = print_date(DATETIME24H, 'h:i A', true, false, $res['timezone']);
		$res['timezonepulldown'] = $ilance->datetimes->timezone_pulldown('timezone', $res['timezone'], false, true);
		$res['referredby'] = handle_input_keywords($ilance->referral->print_referred_by_username(intval($ilance->GPC['id']), true));
		$res['user_language_pulldown'] = $ilance->language->construct_language_pulldown('languageid', $res['languageid']);
		$geo = (isset($show['geoip'])) ? geoip_record_by_addr($geoip, $res['ipaddress']) : '';
		$res['geoipcity'] = (!empty($geo->city) ? '<span style="float:right" class="smaller litegray">GeoIP thinks city is ' . $geo->city . '</span>' : '');
		$res['geoipcountry'] = (!empty($geo->country_name) ? '<span style="float:right" class="smaller litegray">GeoIP thinks country is ' . $geo->country_name . '</span>' : '');
		$res['geoipstate'] = (!empty($geo->region) ? '<span style="float:right" class="smaller litegray">GeoIP thinks state is ' . (!empty($GEOIP_REGION_NAME[$geo->country_code][$geo->region]) ? $GEOIP_REGION_NAME[$geo->country_code][$geo->region] : '{_unknown}') . '</span>' : '');
		$res['geoipzip'] = (!empty($geo->postal_code) ? '<span style="float:right" class="smaller litegray">GeoIP thinks zip code is ' . $geo->postal_code . '</span>' : '');
		unset($geo);
		if (!empty($res['referredby']))
		{
			$show['referredby'] = true;
		}
		$customername = "(" . $res['username'] . ")";
		$sql_loc = $ilance->db->query("
			SELECT location_" . $_SESSION['ilancedata']['user']['slng'] . " AS location
			FROM " . DB_PREFIX . "locations
			WHERE locationid = '" . $res['country'] . "'
			LIMIT 1
		");
		$res_loc = $ilance->db->fetch_array($sql_loc, DB_ASSOC);
		$countryid = fetch_country_id($res_loc['location'], $_SESSION['ilancedata']['user']['slng']);
		$res['country_js_pulldown'] = $ilance->common_location->construct_country_pulldown($countryid, $res_loc['location'], 'country', false, 'state', false, false, false, 'stateid', false, '', '', '', '', false, false, '', 0, 'city', 'cityid');
		$res['state_js_pulldown'] = '<div id="stateid">' . $ilance->common_location->construct_state_pulldown($countryid, $res['state'], 'state', false, false, 0, '', 0, 'city', 'cityid') . '</div>';
		$res['city_js_pulldown'] = '<div id="cityid">' . $ilance->common_location->construct_city_pulldown($res['state'], 'city', $res['city'], false, false, '') . '</div>';
		$roleid = $ilance->subscription_role->fetch_user_roleid($res['user_id']);
		$roleselected = isset($roleid) ? intval($roleid) : '';
		$rolepulldown = $ilance->subscription_role->print_role_pulldown($roleselected, '', 0, 1);
		switch ($res['status'])
		{
			case 'active':
			{
				$sel1 = 'selected="selected"';
				$sel2 = $sel3 = $sel4 = $sel5 = $sel6 = '';
				break;
			}
			case 'suspended':
			{
				$sel1 = $sel3 = $sel4 = $sel5 = $sel6 = '';
				$sel2 = 'selected="selected"';
				break;
			}
			case 'unverified':
			{
				$sel1 = $sel2 = $sel4 = $sel5 = $sel6 = '';
				$sel3 = 'selected="selected"';
				break;
			}       
			case 'banned':
			{
				$sel1 = $sel2 = $sel3 = $sel5 = $sel6 = '';
				$sel4 = 'selected="selected"';
				break;
			}
			case 'cancelled':
			{
				$sel1 = $sel2 = $sel3 = $sel4 = $sel6 = '';
				$sel5 = 'selected="selected"';
				break;
			}
			case 'moderated':
			{
				$sel1 = $sel2 = $sel3 = $sel4 = $sel5 = '';
				$sel6 = 'selected="selected"';
				break;
			}
		}
		$res['userstatus'] = '<select name="status" style="font-family: Verdana"><option value="active" ' . $sel1 . '>{_active_can_signin}</option><option value="suspended" ' . $sel2 . '>{_suspended_cannot_signin}</option><option value="unverified" ' . $sel3 . '>{_unverified_email_cannot_signin}</option><option value="banned" ' . $sel4 . '>{_banned_cannot_signin}</option><option value="cancelled" ' . $sel5 . '>{_cancelled_can_signin}</option><option value="moderated" ' . $sel6 . '>{_moderated_cannot_signin}</option></select>';
		$res['isadministrator'] = ($res['isadmin']) ? '<input type="checkbox" name="isadmin" id="isadmin" value="1" checked="checked" />' : '<input type="checkbox" name="isadmin" id="isadmin" value="1" onclick="alert_js(\'{_please_remember_enabling_this_user_as_an_admin_will_provide_access_to_let_this_profile_signin_to_the_admin_control_panel_interface}\')" />';
		$res['cbposthtml'] = ($res['posthtml']) ? '<input type="checkbox" name="posthtml" id="posthtml" value="1" checked="checked" />' : '<input type="checkbox" name="posthtml" id="posthtml" value="1" onclick="alert_js(\'{_please_remember_enabling_this_user_with_html_post_rights}\')" />';
		if ($res['gender'] == '')
		{
			$res['cb_gender_undecided'] = 'checked="checked"';
			$res['cb_gender_male'] = $res['cb_gender_female'] = '';
		}
		else
		{
			if ($res['gender'] == 'male')
			{
				$res['cb_gender_undecided'] = $res['cb_gender_female'] = '';
				$res['cb_gender_male'] = 'checked="checked"';
			}
			else if ($res['gender'] == 'female')
			{
				$res['cb_gender_undecided'] = $res['cb_gender_male'] = '';
				$res['cb_gender_female'] = 'checked="checked"';
			}
		}
		$profile[] = $res;
		$profileaccount[] = array('accountnumber' => $res['account_number'], 'availablebalance' => $ilance->currency->format($res['available_balance']), 'totalbalance' => $ilance->currency->format($res['total_balance']));
	}
	if (isset($show['geoip']))
	{
	    geoip_close($geoip);
	}
	$currency = print_left_currency_symbol();
	// #### TRANSACTIONS HISTORY ###################################
	if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
	{
		$ilance->GPC['page'] = 1;
	}
	else
	{
		$ilance->GPC['page'] = intval($ilance->GPC['page']);
	}
	$rowlimit = $ilconfig['globalfilters_maxrowsdisplay'];
	$limit = ' ORDER BY invoiceid DESC LIMIT ' . (($ilance->GPC['page'] - 1) * $rowlimit) . ',' . $rowlimit;
	$cntexe = $ilance->db->query("
		SELECT invoiceid
		FROM " . DB_PREFIX . "invoices
		WHERE user_id = '" . intval($ilance->GPC['id']) . "' 
		    AND status != 'scheduled'
		    AND amount > 0
	");
	$number = $ilance->db->num_rows($cntexe);
	$counter = ($ilance->GPC['page'] - 1) * $rowlimit;
	$row_count = 0;
	$sql = $ilance->db->query("
		SELECT createdate, duedate, paiddate, description, totalamount, amount, paid, paymethod, status, invoiceid, taxamount, invoicetype
		FROM " . DB_PREFIX . "invoices 
		WHERE user_id = '" . intval($ilance->GPC['id']) . "' 
		    AND status != 'scheduled'
		    AND amount > 0
		$limit
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$altrows = 0;
		while ($row = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$altrows++;
			$row['class'] = (floor($altrows/2) == ($altrows/2)) ? 'alt2' : 'alt1';
			$row['createdate'] = print_date($row['createdate'], $ilconfig['globalserverlocale_globaltimeformat'], false, false);
			$row['duedate'] = ($row['duedate'] == "0000-00-00 00:00:00") ? '-' : print_date($row['duedate'], $ilconfig['globalserverlocale_globaltimeformat'], false, false);
			$row['paiddate'] = ($row['paiddate'] == "0000-00-00 00:00:00") ? '-' : print_date($row['paiddate'], $ilconfig['globalserverlocale_globaltimeformat'], false, false);
			$row['description'] = stripslashes($row['description']);
			$row['amount'] = ($row['amount'] > 0) ? $ilance->currency->format($row['amount']) : '{_free}';
			$row['paid'] = ($row['paid'] > 0) ? $ilance->currency->format($row['paid']) : '-';
			$row['tax'] = ($row['taxamount'] > 0) ? $ilance->currency->format($row['taxamount']) : '-';
			//$row['source'] = $ilance->accounting_print->print_paymethod_icon($row['paymethod'], false);
			//$row['target'] = '';
			//$row['method'] = '{_' . $row['invoicetype'] . '}';
			$row['source'] = $ilance->accounting_print->print_paymethod_source($row['invoiceid']);
			$row['target'] = $ilance->accounting_print->print_paymethod_target($row['invoiceid']);
			$row['method'] = $ilance->accounting_print->print_paymethod_method($row['invoiceid']);
			if ($row['status'] == 'unpaid')
			{
				$row['action'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_remove-transaction&amp;id=' . $row['invoiceid'] . '&amp;uid=' . intval($ilance->GPC['id']) . '" target="_self" onClick="return confirm_js(\'{_this_action_will_only_delete_this_transaction_from_history_if_you_want_to_change_user_account_amount_use_tab_account}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" id="" /></a>';
				$row['status'] = '{_pending}';
			}
			else
			{
				$row['action'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_remove-transaction&amp;id=' . $row['invoiceid'] . '&amp;uid=' . intval($ilance->GPC['id']) . '" target="_self" onClick="return confirm_js(\'{_this_action_will_only_delete_this_transaction_from_history_if_you_want_to_change_user_account_amount_use_tab_account}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border=0 alt="" /></a>';
			}
			$transaction_rows[] = $row;
			$row_count++;
		}
	}
	else
	{
	    $show['no_rows_returned'] = true;
	}
	$transactionsprevnext = print_pagnation($number, $rowlimit, $ilance->GPC['page'], $counter, $ilpage['subscribers']."?subcmd=_update-customer&amp;id=".intval($ilance->GPC['id']));
	$sql = $ilance->db->query("
		SELECT UNIX_TIMESTAMP(u.renewdate) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS countdown, s.title_" . $_SESSION['ilancedata']['user']['slng'] . " as title, s.description_" . $_SESSION['ilancedata']['user']['slng'] . " as description, s.cost, s.length, s.units, u.subscriptionid, u.user_id, u.paymethod, u.startdate, u.renewdate, u.active, u.invoiceid, i.status as invoice_status, i.amount
		FROM " . DB_PREFIX . "subscription as s,
		" . DB_PREFIX . "subscription_user as u,
		" . DB_PREFIX . "invoices as i
		WHERE u.user_id = '" . intval($ilance->GPC['id']) . "'
			AND u.subscriptionid = s.subscriptionid
			AND i.invoiceid = u.invoiceid
		ORDER BY u.renewdate DESC
		LIMIT 1
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		while ($row = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$clock_js = "";
			$row['subscriptionid'] = $row['subscriptionid'];
			$row['title'] = stripslashes($row['title']);
			$row['description'] = stripslashes($row['description']);
			$row['cost'] = ($row['cost'] > 0) ? $ilance->currency->format($row['cost']) : '{_free}';
			$row['units'] = print_unit($row['units']);
			$row['paymethod'] = $ilance->accounting_print->print_paymethod_icon($row['paymethod'], false);
			if ($row['active'] == 'yes')
			{
				$row['startdate'] = print_date($row['startdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				$row['renewdate'] = print_date($row['renewdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				$row['status'] = '{_active}';
				$row['action'] = '-';
			}
			else
			{
				$row['startdate'] = '-';
				$row['renewdate'] = '-';
				$row['status'] = '{_inactive}';
				$row['action'] = '-';
				if ($row['invoice_status'] == 'unpaid' AND $row['amount'] > 0)
				{
					$row['action'] = '<a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?id=' . $row['invoiceid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/invoice.gif" border="0" alt="{_pay_invoice}" /></a>';
				}
				else
				{
					$row['status'] = '{_inactive}';
				}
			}
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$subscription_rows[] = $row;
			$row_count++;
		}
		$show['no_subscription_rows'] = false;
	}
	else
	{
		$show['no_subscription_rows'] = true;
	}
	$sqlexempt = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "subscription_user_exempt
		WHERE user_id = '" . intval($ilance->GPC['id']) . "'
	");
	if ($ilance->db->num_rows($sqlexempt) > 0)
	{
		$row_count = 0;
		while ($resexempt = $ilance->db->fetch_array($sqlexempt, DB_ASSOC))
		{
			$resexempt['cost'] = $ilance->currency->format($ilance->db->fetch_field(DB_PREFIX."invoices","invoiceid=".$resexempt['invoiceid'],"amount"));
			if ($resexempt['active'])
			{
				$resexempt['status'] = '{_active}';
				$resexempt['action'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_remove-exemption&amp;id='.$resexempt['exemptid'].'&amp;uid='.$resexempt['user_id'].'" onClick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')">'.'{_remove}'.'</a>';
			}
			else
			{
				$resexempt['status'] = '{_expired}';
				$resexempt['action'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_remove-exemption&amp;id='.$resexempt['exemptid'].'&amp;uid='.$resexempt['user_id'].'" onClick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')">'.'{_remove}'.'</a>';
			}
			$resexempt['accessvalue'] = $resexempt['value'];
			$resexempt['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$resexempt['exemptfrom'] = print_date($resexempt['exemptfrom'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
			$resexempt['exemptto'] = print_date($resexempt['exemptto'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
			$exemptions[] = $resexempt;
			$row_count++;
		}
	}
	else
	{
		$show['no_exemptions'] = true;
	}
	#######################
	## CREDIT CARDS ON FILE
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "creditcards
		WHERE user_id = '" . intval($ilance->GPC['id']) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$row_count = 0;
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$res['ccnum'] = substr_replace($ilance->crypt->three_layer_decrypt($res['creditcard_number'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']), 'XX XXXX XXXX ', 2 , (mb_strlen($ilance->crypt->three_layer_decrypt($res['creditcard_number'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3'])) - 6));
			$res['username'] = stripslashes($res['name_on_card']);
			$res['phone'] = $res['phone_of_cardowner'];
			$res['expiry'] = $res['creditcard_expiry'];
			if ($res['authorized'] == 'yes')
			{
				$res['status'] = ucfirst($res['creditcard_status']) . ' &amp; {_authorized}';
				$res['authenticated'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_unauthorize-creditcard&amp;id=' . $res['cc_id'] . '&amp;uid=' . intval($ilance->GPC['id']) . '&amp;ccmgr=1" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="{_click_to_unauthorize_credit_card_cannot_use_card}" border="0"></a>';
			}
			else
			{
				$res['status'] = ucfirst($res['creditcard_status']) . ' &amp; {_unauthorized}';
				$res['authenticated'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_authorize-creditcard&amp;id=' . $res['cc_id'] . '&amp;uid=' . intval($ilance->GPC['id']) . '&amp;ccmgr=1" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="{_click_to_authorize_credit_card_can_use_card}" border="0"></a>';
			}
			$res['address'] = $res['card_billing_address1'].", ";
			if ($res['card_billing_address2'] != "")
			{
				$res['address'] .= $res['card_billing_address2'].", ";
			}
			$res['address'] .= ucfirst($res['card_city']).", ".ucfirst($res['card_state']).", ".mb_strtoupper($res['card_postalzip']).", ";
			$res['address'] .= stripslashes($ilance->db->fetch_field(DB_PREFIX."locations","locationid=".$ilance->db->fetch_field(DB_PREFIX."creditcards","cc_id=".$res['cc_id'],"card_country"),"location_eng"));
			if ($res['creditcard_type'] == "visa")
			{
				$res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/visa.gif" border="0" alt="" />';
			}
			else if ($res['creditcard_type'] == "mc")
			{
				$res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/mc.gif" border="0" alt="" />';
			}
			else if ($res['creditcard_type'] == "amex")
			{
				$res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/amex.gif" border="0" alt="" />';
			}
			else if ($res['creditcard_type'] == "disc")
			{
				$res['cardtype'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/disc.gif" border="0" alt="" />';
			}
			$res['remove'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_remove-creditcard&amp;id='.$res['cc_id'].'&amp;uid='.intval($ilance->GPC['id']).'" onClick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0" /></a>';
			$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$creditcards[] = $res;
			$row_count++;
		}
	}
	else
	{
		$show['no_creditcards'] = true;
	}
	########################
	## BANK ACCOUNTS ON FILE
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "bankaccounts
		WHERE user_id = '" . intval($ilance->GPC['id']) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$row_count = 0;
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$res['bankname'] = stripslashes($res['beneficiary_bank_name']);
			$res['accountnum'] = $res['beneficiary_account_number'];
			$res['accounttype'] = ucfirst($res['bank_account_type']);
			$res['address'] = stripslashes($res['beneficiary_bank_address_1']);
			$res['swiftnum'] = $res['beneficiary_bank_routing_number_swift'];
			if ($res['beneficiary_bank_address_2'] != "")
			{
				$res['address'] .= ", ".stripslashes($res['beneficiary_bank_address_2']); 
			}
			$res['city'] = ucfirst($res['beneficiary_bank_city']);
			$res['zipcode'] = mb_strtoupper($res['beneficiary_bank_zipcode']);
			$res['country'] = stripslashes($ilance->db->fetch_field(DB_PREFIX."locations","locationid=".$ilance->db->fetch_field(DB_PREFIX."bankaccounts","bank_id=".$res['bank_id'],"beneficiary_bank_country_id"),"location_eng"));
			$res['currency'] = $ilance->db->fetch_field(DB_PREFIX."currency","currency_id=".$ilance->db->fetch_field(DB_PREFIX."bankaccounts","bank_id=".$res['bank_id'],"destination_currency_id"),"currency_abbrev");
			$res['remove'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_remove-bankaccount&amp;id='.$res['bank_id'].'&amp;uid='.intval($ilance->GPC['id']).'" onClick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0" /></a>';
			$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$bankaccounts[] = $res;
			$row_count++;
		}
	}
	else
	{
		$show['no_bankaccounts'] = true;
	}
	
	// custom registration questions
	$customquestions = $ilance->registration_questions->construct_register_questions(0, 'updateprofileadmin', intval($ilance->GPC['id']));
	$profilequestions = $ilance->profile_questions->construct_profile_questions(intval($ilance->GPC['id']), 'updateprofileadmin');
	$subscription_plan_pulldown = $ilance->subscription->plans_pulldown();
	$subscription_permissions_pulldown = $ilance->subscription->exemptions_pulldown();
	$pprint_array = array('city_js_pulldown','scriptpage','profilequestions','customquestions','prevnext','admins_pulldown','members_pulldown','scripts_pulldown','rolepulldown','subscription_role_pulldown','dynamic_js_bodyend2','country_js_pulldown','state_js_pulldown','role_pulldown','subscription_permissions_pulldown','register_questions','reportrange','transactionsprevnext','id','customername','currency','subscription_plan_pulldown','dynamic_js_bodyend','searchprevnext','prevnext','number','get_filtervalue','phrases_selectlist','keyword','base_language_pulldown','limit_pulldown','language_pulldown','input_style');

	($apihook = $ilance->api('admincp_subscribers_end')) ? eval($apihook) : false;

	$ilance->template->fetch('main', 'subscribers_edit.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
	$ilance->template->parse_loop('main', 'profile');
	$ilance->template->parse_loop('main', 'profileaccount');
	$ilance->template->parse_loop('main', 'transaction_rows');
	$ilance->template->parse_loop('main', 'subscription_rows');
	$ilance->template->parse_loop('main', 'creditcards');
	$ilance->template->parse_loop('main', 'bankaccounts');
	$ilance->template->parse_loop('main', 'exemptions');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
else
{
	$area_title = '{_subscriber_management}';
	$page_title = SITE_NAME . ' - {_subscriber_management}';
	
	($apihook = $ilance->api('admincp_subscriber_management')) ? eval($apihook) : false;
	
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['subscribers'], $ilpage['subscribers'], $_SESSION['ilancedata']['user']['slng']);
	
	$show['show_search'] = true;
	$customername = '';
	$reportrange = '<select name="rangepast" style="font-family: verdana"><option value="-1 day"';
	if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == "past" AND isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 day")
	{
		$reportrange .= ' selected'; 
	}
	$reportrange .= '>'.'{_the_past_day}'.'</option><option value="-1 week"';
	if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == "past" AND isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 week")
	{
		$reportrange .= ' selected'; 
	}
	$reportrange .= '>'.'{_the_past_week}'.'</option><option value="-1 month"';
	if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == "past" AND isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 month")
	{
		$reportrange .= ' selected';
	}
	$reportrange .= '>'.'{_the_past_month}'.'</option><option value="-1 year"'; 
	if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == "past" AND isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 year")
	{
		$reportrange .= ' selected; ';
	}
	$reportrange .= '>'.'{_the_past_year}'.'</option></select>';
	$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
	$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
	// #### VIEWING TASK LOG ###########################################
	if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'view')
	{
		// filters
		$ilance->GPC['pp'] = isset($ilance->GPC['pp']) ? intval($ilance->GPC['pp']) : $ilconfig['globalfilters_maxrowsdisplay'];
		$ilance->GPC['script'] = isset($ilance->GPC['script']) ? $ilance->GPC['script'] : '';
		$ilance->GPC['user_id'] = isset($ilance->GPC['user_id']) ? intval($ilance->GPC['user_id']) : '';
		$ilance->GPC['admin_id'] = isset($ilance->GPC['admin_id']) ? intval($ilance->GPC['admin_id']) : '';
		$ilance->GPC['order'] = isset($ilance->GPC['order']) ? $ilance->GPC['order'] : 'ASC';
		$ilance->GPC['where'] = '';
		if (!empty($ilance->GPC['script']))
		{
			$ilance->GPC['where'] = "AND script = '" . $ilance->db->escape_string($ilance->GPC['script']) . "'";
		}
		if (!empty($ilance->GPC['user_id']))
		{
			$ilance->GPC['where'] .= "AND user_id = '" . $ilance->GPC['user_id'] . "'";
		}
		if (!empty($ilance->GPC['admin_id']))
		{
			$ilance->GPC['where'] .= "AND user_id = '" . $ilance->GPC['admin_id'] . "'";
		}
		if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
		{
			$ilance->GPC['page'] = 1;
		}
		else
		{
			$ilance->GPC['page'] = intval($ilance->GPC['page']);
		}
		$ilance->GPC['limit'] = ' ORDER BY '.$ilance->db->escape_string($ilance->GPC['orderby']).' '.$ilance->GPC['order'].' LIMIT '.(($ilance->GPC['page']-1)*$ilance->GPC['pp']).','.$ilance->GPC['pp'];
		$audittmp = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "audit WHERE logid > 0 ".$ilance->GPC['where']);
		$ilance->GPC['totalcount'] = $ilance->db->num_rows($audittmp);
		$ilance->GPC['counter'] = ($ilance->GPC['page']-1)*$ilance->GPC['pp'];
		$audit = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "audit WHERE logid > 0 ".$ilance->GPC['where']." ".$ilance->GPC['limit']);
		if ($ilance->db->num_rows($audit) > 0)
		{
			$count = 0;
			while ($res = $ilance->db->fetch_array($audit, DB_ASSOC))
			{
				$res['class'] = ($count % 2) ? 'alt2' : 'alt1';
				$res['datetime'] = print_date($ilance->datetimes->fetch_datetime_from_timestamp($res['datetime']), $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				$res['is_admin'] = $ilance->db->fetch_field(DB_PREFIX . "users", "user_id='".$res['user_id']."'", "isadmin", "1");
				if ($res['is_admin'] == '1')
				{
					$res['user'] = '<span class="small gray">--</span>';
					$res['admin'] = '<span class="small blue"><a href="' . $ilpage['settings'] . '?cmd=moderators">' . fetch_adminname($res['user_id']) . '</a></span>';
				}
				else 
				{
					if ($res['user_id'] == '0')
					{
						$res['user'] = '<span class="small gray">' . fetch_user('username', $res['user_id']) . '</span>';
					}
					else 
					{
						$res['user'] = '<span class="small blue"><a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&id=' . $res['user_id'] . '">' . fetch_user('username', $res['user_id']) . '</a></span>';
					}
					$res['admin'] = '<span class="small gray">--</span>';
				}
				$res['eventss'] = $res['subcmd'];
				$auditlog[] = $res;
				$count++;
			}
		}
		$prevnext = print_pagnation($ilance->GPC['totalcount'], $ilance->GPC['pp'], $ilance->GPC['page'], $ilance->GPC['counter'], $ilpage['subscribers'].'?cmd=auditlog&amp;do=view&amp;script='.$ilance->GPC['script'].'&amp;admin_id='.$ilance->GPC['admin_id'].'&amp;user_id='.$ilance->GPC['user_id'].'&amp;orderby='.$ilance->GPC['orderby'].'&amp;order='.$ilance->GPC['order']);
	}
	// #### PRUNE AUDIT LOG ################################################
	else if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'prune')
	{
		$ilance->GPC['script'] = isset($ilance->GPC['script']) ? $ilance->GPC['script'] : '';
		$ilance->GPC['user_id'] = isset($ilance->GPC['user_id']) ? intval($ilance->GPC['user_id']) : '';
		$ilance->GPC['days'] = intval($ilance->GPC['days']);
		$ilance->GPC['cutoff'] = TIMESTAMPNOW - (86400 * $ilance->GPC['days']);
		$conds = '';
		if (!empty($ilance->GPC['script']))
		{
			$conds .= " AND script = '" . $ilance->db->escape_string($ilance->GPC['script']) . "'";
		}
		if (!empty($ilance->GPC['user_id']))
		{
			$conds .= " AND user_id = '" . intval($ilance->GPC['user_id']) . "'";
		}
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "audit
			WHERE datetime < " . $ilance->GPC['cutoff'] . "
			$conds
		");
		$count = number_format($ilance->db->num_rows($sql));
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "audit
			WHERE datetime < " . $ilance->GPC['cutoff'] . "
			$conds
		");
		print_action_success('{_audit_logs_pruned} ' .$count, $ilance->GPC['return']);
		exit();    
	}
	else
	{
		$where = "WHERE user_id != '' ";
		// #### ADMIN SEARCHING SUBSCRIBERS ############################
		if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'search')
		{
			$show['advancedsearch'] = $show['showsearch'] = true;
			$filterby = (isset($ilance->GPC['filterby']) AND !empty($ilance->GPC['filterby'])) ? $ilance->GPC['filterby'] : 'user_id';
			$filtervalue = (isset($ilance->GPC['filtervalue']) AND !empty($ilance->GPC['filtervalue'])) ? $ilance->GPC['filtervalue'] : '';
			$orderby = (isset($ilance->GPC['orderby']) AND !empty($ilance->GPC['orderby'])) ? $ilance->GPC['orderby'] : 'desc';
			$acceptedorder = array('asc','desc');
			if (!in_array($orderby, $acceptedorder))
			{
				$orderby = 'desc';
			}
			$orderlimit = ' ORDER BY ' . $filterby . ' ' . mb_strtoupper($orderby) . ' LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']) . ',' . $ilconfig['globalfilters_maxrowsdisplaysubscribers'];
			// searching via specific user status only
			if (isset($ilance->GPC['status']) AND !empty($ilance->GPC['status']))
			{
				$where .= "AND status = '" . $ilance->db->escape_string($ilance->GPC['status']) . "'";
			}
			if (isset($ilance->GPC['period']) AND !empty($ilance->GPC['period']))
			{
				$timeago = intval($ilance->GPC['period']);
				$where .= "AND date_added >= DATE_SUB('" . DATETIME24H . "', INTERVAL $timeago HOUR)";
			}
			if (!empty($filtervalue) AND !empty($filterby))
			{
				$where .= "AND " . $ilance->db->escape_string($filterby) . " = '" . $ilance->db->escape_string($filtervalue) . "'";
			}
			$scriptpage = $ilpage['subscribers'] . "?cmd=search";
			foreach ($ilance->GPC AS $cmd => $value)
			{
				if (!empty($cmd) AND !empty($value) AND $cmd != 'submit' AND $cmd != 'cmd' AND $cmd != 'page')
				{
					$scriptpage .= '&amp;' . $cmd . '=' . $value;
				}
			}
		}
		$displayorderfields = array('ASC', 'DESC');
		$displayorder = '&amp;orderbysearch=ASC';
		$realdisplayorder = $displayorder;
		$displayordersql = 'ASC';
		if (isset($ilance->GPC['orderbysearch']) AND $ilance->GPC['orderbysearch'] == 'ASC')
		{
			$realdisplayorder = '&amp;orderbysearch=ASC';
			$displayorder = '&amp;orderbysearch=DESC';
		}
		else if (isset($ilance->GPC['orderbysearch']) AND $ilance->GPC['orderbysearch'] == 'DESC')
		{
			$realdisplayorder = '&amp;orderbysearch=DESC';
			$displayorder = '&amp;orderbysearch=ASC';
		}
		if (isset($ilance->GPC['orderbysearch']) AND in_array($ilance->GPC['orderbysearch'], $displayorderfields))
		{
			$displayordersql = mb_strtoupper($ilance->GPC['orderbysearch']);
		}
		// ordering by display logic
		$orderbyfields = array('total_balance', 'user_id', 'username');
		$orderby = '';
		$orderbysql = 'user_id';
		if (isset($ilance->GPC['orderby']) AND in_array($ilance->GPC['orderby'], $orderbyfields))
		{
			$orderbysql = mb_strtoupper($ilance->GPC['orderby']);
			$orderby = '&amp;orderby='.$ilance->GPC['orderby'];
		}
		$scriptpage = !isset($scriptpage) ? $ilpage['subscribers'] . '?cmd=listing' . $displayorder . $orderby : $scriptpage;
		$scriptpageprevnext = $ilpage['subscribers'] . '?cmd=listing' . $realdisplayorder . $orderby;
		$scriptpage .= $displayorder;
		$show['showsearch'] = false;
		$sql = $ilance->db->query("
			SELECT user_id, username, first_name, last_name, email, phone, city, state, zip_code, status, available_balance, total_balance, isadmin, permissions
			FROM " . DB_PREFIX . "users
			$where
			ORDER BY $orderbysql
			$displayordersql
			LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplaysubscribers']) . "," . $ilconfig['globalfilters_maxrowsdisplaysubscribers']
		);
		$sql2 = $ilance->db->query("
			SELECT user_id
			FROM " . DB_PREFIX . "users
			$where
			ORDER BY $orderbysql
			$displayordersql
		");
		$number = (int)$ilance->db->num_rows($sql2);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$row_count = 0;
			$sql_admin = $ilance->db->num_rows($ilance->db->query("
				SELECT user_id
				FROM " . DB_PREFIX . "users
				WHERE isadmin = '1'
			"));
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				if ($res['status'] == 'moderated')
				{
					$res['class'] = '#FFF7F9';   
				}
				else
				{
					$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				}
				$status = $res['status'];
				// is suspended
				$res['status'] = ($status == 'active')
					? '<span title="{_click_to_suspend_customer}"><a href="' . $ilpage['subscribers'] . '?subcmd=suspenduser&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="{_click_to_suspend_customer}" border="0"></a></span>'
					: '<span title="{_click_to_unsuspend_customer}"><a href="' . $ilpage['subscribers'] . '?subcmd=unsuspenduser&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="{_click_to_unsuspend_customer}" border="0"></a></span>';
				// is banned
				if ($status == 'banned')
				{
					$res['status2'] = '<span title="{_click_to_unban_customer}"><a href="' . $ilpage['subscribers'] . '?subcmd=unbanuser&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="{_click_to_unban_customer}" border="0"></a></span>';
				}
				else
				{
					$res['status2'] = '<span title="{_click_to_ban_customer}"><a href="' . $ilpage['subscribers'] . '?subcmd=banuser&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="{_click_to_ban_customer}" border="0"></a></span>';
				}
				if ($res['isadmin'])
				{
					$res['isadmin'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="{_yes}" />';
					$is_admin = '1';
				}
				else
				{
					$res['isadmin'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="{_no}" />';
					$is_admin = '0';
				}
				$res['edit'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $res['user_id'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" alt="" border="0"></a>';
				$res['remove'] = ($sql_admin == 1 AND $is_admin == 1) ? '' : '<a href="' . $ilpage['subscribers'] . '?subcmd=deleteuser&amp;id=' . $res['user_id'] . '" onClick="return confirm_js(\'{_this_operation_will_remove_all_informations_regarding_this_user_his_auctions_orders_bids_invoices_escrow}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0"></a>';
				if ($res['available_balance'] > 0)
				{
					$res['balance'] = $ilance->currency->format($res['available_balance']);
				}
				else
				{
					$res['balance'] = '<div class="gray">-</div>';
				}
				$res['unpaid_amount'] = '';
				$inv_unpaid = $ilance->db->query("
					SELECT SUM(totalamount) as unpaid_amount
					FROM ". DB_PREFIX ."invoices
					WHERE user_id = '" . $res['user_id'] . "' 
						AND status = 'unpaid'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($inv_unpaid) > 0)
				{
					$inv_res = $ilance->db->fetch_array($inv_unpaid, DB_ASSOC);
					$res['unpaid_amount'] = ($inv_res['unpaid_amount'] > 0) ? '-' . $ilance->currency->format($inv_res['unpaid_amount']) : '';
				}
				$res['subscription'] = $ilance->subscription->fetch_subscription_plan($res['user_id']);
				$res['role'] = $ilance->subscription_role->print_role($ilance->subscription_role->fetch_user_roleid($res['user_id']));
				$res['action'] = '<input type="checkbox" name="user_id[]" value="' . $res['user_id'] . '" id="members_' . $res['user_id'] . '" />';
				// quick view of items/service bought and sold in marketplace
				$res['bought'] = '';
				$res['sold']   = '';				
				if ($ilconfig['globalauctionsettings_productauctionsenabled'])
				{
					$bought = 0;
					$orders_sql = $ilance->db->query("
						SELECT SUM(qty) AS bought
						FROM ". DB_PREFIX ."buynow_orders
						WHERE buyer_id = '" . $res['user_id'] . "' 
					");	
					if ($ilance->db->num_rows($orders_sql) > 0)
					{
						$inv_bought = $ilance->db->fetch_array($orders_sql, DB_ASSOC);
						$bought = ($inv_bought['bought'] != null) ? $inv_bought['bought'] : 0;
					}	
					$itemwins = 0;
					$itemwin_sql = $ilance->db->query("
						SELECT SUM(bid_id) AS itemwin
						FROM ". DB_PREFIX ."project_bids
						WHERE user_id = '" . $res['user_id'] . "' 
							AND bidstatus = 'awarded'
							AND state = 'product'
					");	
					if ($ilance->db->num_rows($itemwin_sql) > 0)
					{
						$inv_itemwin = $ilance->db->fetch_array($itemwin_sql, DB_ASSOC);
						$itemwins = ($inv_itemwin['itemwin'] != null) ? $inv_itemwin['itemwin'] : 0;
					}	
					$bought_total =	$bought + $itemwins;
					$res['bought'] = '<div class="smaller gray"><span class="black">' . $bought_total . '</span> {_items_lower}</div>';
				}
				if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
				{
					$res['bought'] .= '<div class="smaller gray" style="padding-top:3px"><span class="black">' . fetch_bought_count($res['user_id'], 'service') . '</span> {_services_lower}</div>';
				}
				if ($ilconfig['globalauctionsettings_productauctionsenabled'])
				{
					$res['sold'] = '<div class="smaller gray"><span class="black">' . fetch_sold_count($res['user_id'], 'product') . '</span> {_items_lower}</div>';
				}					
				if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
				{
					$res['sold'] .= '<div class="smaller gray" style="padding-top:3px"><span class="black">' . fetch_sold_count($res['user_id'], 'service') . '</span> {_services_lower}</div>';
				}
				$res['login'] = '<span title="{_switch_to_another_user}"><a href="' . $ilpage['subscribers'] . '?subcmd=switchuser&amp;id=' . $res['user_id'] . '" onClick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/picture_blue.gif" alt="{_switch_to_another_user}" border="0"></a></span>';
				$res['emailshort'] = shorten($res['email'], 15);
				//do we really need this???
				$result2 = $ilance->db->query("
					SELECT project_id, description, project_title, bids, status, project_state, user_id, date_starts,UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime 
					FROM ". DB_PREFIX ."projects 
					WHERE user_id = '" . $res['user_id'] . "' 
					ORDER BY id DESC 
					LIMIT 5
				", 0, null, __FILE__, __LINE__);
				$row_count_bids = 0;
				if ($ilance->db->num_rows($result2) > 0)
				{
					while ($rows = $ilance->db->fetch_array($result2, DB_ASSOC))
					{
						$p_id = $rows['project_id'];	
						$rows['project_status'] = ucwords($rows['status']);
						$rows['timeleft'] = ($rows['status'] == 'expired') ? '{_ended}' : $ilance->auction->calculate_time_left($rows['date_starts'], $rows['starttime'], $rows['mytime']);
						$rows['type'] = '{_' . $rows['project_state'] . '}';							
						$rows['class2'] = ($row_count_bids % 2) ? 'alt2' : 'alt1';	
						$rows['project_title_short'] = shorten($rows['project_title'], 25);
						$rows['viewtype'] = $rows['project_state'];
						$GLOBALS['auctions' . $rows['user_id']][] = $rows;
						$row_count_bids++;
					 }
					 $GLOBALS['no_activity' . $res['user_id']] = false;	
				}
				else
				{
					$GLOBALS['no_activity' . $res['user_id']] = true;						
					$row_count_bids = 0;
				}
				$res['username'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $res['user_id'] . '">' . $res['username'] . '</a>';
				$customers[] = $res;
				$row_count++;
			}
			$searchprevnext = '';
			$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplaysubscribers'], $ilance->GPC['page'], $counter, $scriptpageprevnext);
		}
		else
		{
			$show['no_customers'] = true;
		}
	}
	$subscription_plan_pulldown = $ilance->subscription->plans_pulldown();
	$subscription_permissions_pulldown = $ilance->subscription->exemptions_pulldown();
	$countryid = fetch_country_id($ilconfig['registrationdisplay_defaultcountry'], $_SESSION['ilancedata']['user']['slng']);
	$country_js_pulldown = $ilance->common_location->construct_country_pulldown($countryid, $ilconfig['registrationdisplay_defaultcountry'], 'country', false, 'state', false, false, false, 'stateid', false, '', '', '', '', false, false, '', 0, 'city', 'cityid');
	$state_js_pulldown = '<div id="stateid">' . $ilance->common_location->construct_state_pulldown($countryid, $ilconfig['registrationdisplay_defaultstate'], 'state', false, false, 0, '', 0, 'city', 'cityid') . '</div>';
	$city_js_pulldown = '<div id="cityid">' . $ilance->common_location->construct_city_pulldown($ilconfig['registrationdisplay_defaultstate'], 'city', $ilconfig['registrationdisplay_defaultcity'], false, false, '') . '</div>';
	$subscription_role_pulldown = $ilance->subscription_role->print_role_pulldown('', '', 0, 1);
	$ilance->GPC['script'] = (!empty($ilance->GPC['script']) ? $ilance->GPC['script'] : '');
	$ilance->GPC['user_id'] = (!empty($ilance->GPC['user_id']) ? $ilance->GPC['user_id'] : '');
	$ilance->GPC['admin_id'] = (!empty($ilance->GPC['admin_id']) ? $ilance->GPC['admin_id'] : '');
	$scripts_pulldown = $ilance->admincp->print_audit_scripts_pulldown($ilance->GPC['script']);
	$members_pulldown = '<input type="text" name="user_id" value="' . intval($ilance->GPC['user_id']) . '" class="input" />';
	$admins_pulldown = $ilance->admincp->print_admins_pulldown($ilance->GPC['admin_id']);
	$get_filtervalue = !empty($ilance->GPC['filtervalue']) ? handle_input_keywords($ilance->GPC['filtervalue']) : '';
	$user_language_pulldown = $ilance->language->construct_language_pulldown('languageid', $ilance->language->fetch_default_languageid());
	
	$pprint_array = array('user_language_pulldown','city_js_pulldown','scriptpage','profilequestions','customquestions','prevnext','admins_pulldown','members_pulldown','scripts_pulldown','rolepulldown','subscription_role_pulldown','dynamic_js_bodyend2','country_js_pulldown','state_js_pulldown','role_pulldown','subscription_permissions_pulldown','register_questions','reportrange','transactionsprevnext','id','customername','currency','subscription_plan_pulldown','dynamic_js_bodyend','searchprevnext','prevnext','number','get_filtervalue','phrases_selectlist','keyword','base_language_pulldown','limit_pulldown','language_pulldown','input_style');
	
	($apihook = $ilance->api('admincp_subscribers_end')) ? eval($apihook) : false;

	$ilance->template->fetch('main', 'subscribers.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
	$ilance->template->parse_loop('main', 'customers');
	@reset($customers);
	while ($i = @each($customers))
	{
		$ilance->template->parse_loop('main', 'auctions' . $i['value']['user_id']);
	}	
	$ilance->template->parse_loop('main', 'auditlog');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>