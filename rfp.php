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
		'ckeditor',
		'wysiwyg'
	),
	'footer' => array(
		'v4',
		'tooltip',
		'md5',
		'autocomplete',
		'cron'
	)
);

// #### setup script location ##################################################
define('LOCATION', 'rfp');

// #### require backend ########################################################
require_once('./functions/config.php');

require_once(DIR_CORE . 'functions_wysiwyg.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[rfp]" => $ilcrumbs["$ilpage[rfp]"]);

// #### HANDLE BUYER TOOLS FROM LISTING PAGE ###################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'buyertools' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'enhancements' AND isset($ilance->GPC['pid']) AND $ilance->GPC['pid'] > 0)
{
	if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
	{
		refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['rfp'] . print_hidden_fields($string = true, $excluded = array(), $questionmarkfirst = true)));
		exit();
	}
	$ilance->GPC['featured'] = $ilance->GPC['old']['featured'];
	$ilance->GPC['featured_searchresults'] = $ilance->GPC['old']['featured_searchresults'];
	$ilance->GPC['highlite'] = $ilance->GPC['old']['highlite'];
	$ilance->GPC['bold'] = $ilance->GPC['old']['bold'];
	$ilance->GPC['autorelist'] = $ilance->GPC['old']['autorelist'];
	$ilance->GPC['enhancements'] = (!empty($ilance->GPC['enhancements']) ? $ilance->GPC['enhancements'] : array());
	if (is_array($ilance->GPC['enhancements']))
	{
		$enhance = $ilance->auction_fee->process_listing_enhancements_transaction($ilance->GPC['enhancements'], $_SESSION['ilancedata']['user']['userid'], intval($ilance->GPC['pid']), 'update', 'service');
		if (is_array($enhance))
		{
			$ilance->GPC['featured'] = (int)$enhance['featured'];
			$ilance->GPC['featured_searchresults'] = (int)$enhance['featured_searchresults'];
			$ilance->GPC['highlite'] = (int)$enhance['highlite'];
			$ilance->GPC['bold'] = (int)$enhance['bold'];
			$ilance->GPC['autorelist'] = (int)$enhance['autorelist'];
			$ilance->GPC['featured_date'] = ($ilance->GPC['featured'] AND isset($ilance->GPC['old']['featured_date']) AND $ilance->GPC['old']['featured_date'] == '0000-00-00 00:00:00') ? DATETIME24H : '0000-00-00 00:00:00';
		}
		// #### update auction #########################################
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "projects 
			SET featured = '" . intval($ilance->GPC['featured']) . "',
			featured_date = '" . $ilance->db->escape_string($ilance->GPC['featured_date']) . "',
			featured_searchresults = '" . $ilance->db->escape_string($ilance->GPC['featured_searchresults']) . "',
			highlite = '" . intval($ilance->GPC['highlite']) . "',
			bold = '" . intval($ilance->GPC['bold']) . "',
			autorelist = '" . intval($ilance->GPC['autorelist']) . "'
			WHERE project_id = '" . intval($ilance->GPC['pid']) . "'
				AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
	}
	print_notice('{_listing_updated}', '{_the_options_you_selected_have_been_completed_successfully}', HTTP_SERVER . $ilpage['rfp'] . '?id=' . $ilance->GPC['pid'], '{_return_to_listing}');
	exit();
}

// #### EXTERNAL RFP TAKEOVER REQUEST NOTIFICATION HANDLER #####################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'rfp-accept' AND isset($ilance->GPC['xcode']) AND !empty($ilance->GPC['xcode']))
{
	$area_title = '{_rfp_takeover_acceptance_request_in_progress} . .';
	$page_title = SITE_NAME . ' - {_rfp_takeover_acceptance_request_in_progress} . .';
	// #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
	$sqlxcode = $ilance->db->query("
                SELECT transfer_to_userid, transfer_from_userid, project_title, project_id, cid, filtered_budgetid, status, bids, date_end, description
                FROM " . DB_PREFIX . "projects
                WHERE transfer_code = '" . $ilance->db->escape_string($ilance->GPC['xcode']) . "'
                LIMIT 1
        ", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sqlxcode) == 0)
	{
		$area_title = '{_invalid_rfp_transfer_code}';
		$page_title = SITE_NAME . ' - {_invalid_rfp_transfer_code}';
		print_notice('{_invalid_rfp_transfer_code}', '{_were_sorry_there_was_a_problem_with}'."<br /><br />".'{_If_you_are_clicking_a_link_within_your_email_client}'."<br /><br />".'{_please_contact_customer_support}', $ilpage['main'], '{_main_menu}');
		exit();
	}
	$result_project = $ilance->db->fetch_array($sqlxcode, DB_ASSOC);
	// #### new project owner information
	$sql_newowner = $ilance->db->query("
		SELECT username, email
		FROM " . DB_PREFIX . "users
		WHERE user_id = '" . $result_project['transfer_to_userid'] . "'
			AND status = 'active'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql_newowner) == 0)
	{
		$area_title = '{_invalid_rfp_transfer_to_owner}';
		$page_title = SITE_NAME . ' - {_invalid_rfp_transfer_to_owner}';
		print_notice('{_invalid_rfp_transfer_to_new_owner}', '{_were_sorry_there_was_a_problem_with_the_rfp_transfer_to_the_new_owner}'."<br /><br /><li>".'{_the_buyer_no_longer_exists_on_our_marketplace}'."</li><li>".'{_the_buyer_has_been_suspended_from_using_the_marketplace_resources}'."</li><li>".'{_the_buyer_does_not_have_proper_permissions_to_accept_rfp_takeover_requests_from_others}'."</li><br /><br />".'{_please_contact_customer_support}', $ilpage['main'], '{_main_menu}');
		exit();
	}
	$result_newowner = $ilance->db->fetch_array($sql_newowner, DB_ASSOC);
	// #### old project owner information
	$sql_oldowner = $ilance->db->query("
		SELECT username, email
		FROM " . DB_PREFIX . "users
		WHERE user_id = '" . $result_project['transfer_from_userid'] . "'
			AND status = 'active'
	", 0, null, __FILE__, __LINE__);
	$result_oldowner = $ilance->db->fetch_array($sql_oldowner, DB_ASSOC);
	$area_title = '{_rfp_transfer_of_ownership_complete} ['.$result_project['project_title'].']';
	$page_title = SITE_NAME . ' - {_rfp_transfer_of_ownership_complete}';
	// #### accept rfp transfer takeover request
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "projects
		SET user_id = '" . $result_project['transfer_to_userid'] . "',
		transfer_status = 'accepted',
		transfer_code = '<---" . '{_transfer_complete_upper}' . "--->'
		WHERE project_id = '" . $result_project['project_id'] . "'
			AND transfer_code = '" . $ilance->db->escape_string($ilance->GPC['xcode']) . "'
	", 0, null, __FILE__, __LINE__);
	// #### update bid table for new owner
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "project_bids
		SET project_user_id = '".$result_project['transfer_to_userid']."'
		WHERE project_id = '".$result_project['project_id']."'
	", 0, null, __FILE__, __LINE__);
	// #### update pmb alert table for new owner
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "pmb_alerts
		SET from_id = '" . $result_project['transfer_to_userid'] . "'
		WHERE project_id = '" . $result_project['project_id'] . "'
			AND from_id = '" . $result_project['transfer_from_userid'] . "'
	", 0, null, __FILE__, __LINE__);
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "pmb_alerts
		SET to_id = '" . $result_project['transfer_to_userid'] . "'
		WHERE project_id = '" . $result_project['project_id'] . "'
			AND to_id = '" . $result_project['transfer_from_userid'] . "'
	", 0, null, __FILE__, __LINE__);
	// #### select invoice tied to escrow account for old owner
	$sql_invoice = $ilance->db->query("
		SELECT e.invoiceid, e.project_id, i.invoicetype, i.invoiceid, i.projectid FROM
		" . DB_PREFIX . "projects_escrow AS e,
		" . DB_PREFIX . "invoices AS i
		WHERE e.project_id = '" . $res['project_id'] . "'
			AND i.projectid = '" . $res['project_id'] . "'
			AND i.invoicetype = 'escrow'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql_invoice) > 0 AND $ilconfig['escrowsystem_enabled'])
	{
		// #### escrow process exists with old project owner and buyer
		$result_invoice = $ilance->db->fetch_array($sql_invoice, DB_ASSOC);
		// #### update escrow table for new owner tied to this auction
		$sql_escrow = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "projects_escrow
			WHERE project_user_id = '" . $result_project['transfer_from_userid'] . "'
				AND project_id = '" . $result_project['project_id'] . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql_escrow) > 0)
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects_escrow
				SET project_user_id = '" . $result_project['transfer_to_userid'] . "'
				WHERE project_id = '" . $result_project['project_id'] . "'
			", 0, null, __FILE__, __LINE__);
			// update invoice table tied old buyer invoice to new buyer
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "invoices
				SET user_id = '" . $result_project['transfer_to_userid'] . "'
				WHERE projectid = '" . $result_project['project_id'] . "'
					AND invoicetype = 'escrow'
			", 0, null, __FILE__, __LINE__);
		}
	}
	// #### budget overview
	$budget = $ilance->auction->construct_budget_overview($result_project['cid'], $result_project['filtered_budgetid']);
	
	$existing = array(
		'{{transfer_hash}}' => $newmd5hash,
		'{{transfer_to_username}}' => ucfirst($result_newowner['username']),
		'{{transfer_to_email}}' => $result_newowner['email'],
		'{{transfer_from_username}}' => ucfirst($result_oldowner['username']),
		'{{transfer_from_email}}' => $result_oldowner['email'],
		'{{rfp_title}}' => $result_project['project_title'],
		'{{status}}' => $ilance->auction->print_auction_status($result_project['status']),
		'{{bids}}' => $result_project['bids'],
		'{{closing_date}}' => print_date($result_project['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
		'{{description}}' => short_string(stripslashes($result_project['description']),150),
		'{{project_id}}' => $result_project['project_id'],
		'{{budget}}' => $budget
	);
	// #### email new owner
	$ilance->email->mail = $result_newowner['email'];
	$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
	$ilance->email->get('rfp_takeover_new_buyer');
	$ilance->email->set($existing);
	$ilance->email->send();
	// #### email old owner
	$ilance->email->mail = $result_oldowner['email'];
	$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
	$ilance->email->get('rfp_takeover_old_buyer');
	$ilance->email->set($existing);
	$ilance->email->send();
	// email admin
	$ilance->email->mail = SITE_EMAIL;
	$ilance->email->slng = fetch_site_slng();
	$ilance->email->get('rfp_takeover_admin');
	$ilance->email->set($existing);
	$ilance->email->send();
	print_notice('{_rfp_takeover_request_was_accepted_and_transferred}', '<p>{_you_have_successfully_accepted_this_rfp_takeover_request_and_nothing_more_is_required_by_you}</p><p>{_you_will_now_be_able_to_review_this_new_rfp_from_your_buying_activity_menu}</p><p>{_please_contact_customer_support_for_more_information}</p>', HTTP_SERVER . $ilpage['main'], '{_main_menu}');
	exit();
}

// #### EXTERNAL RFP TAKEOVER REJECT REQUEST ###################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'rfp-reject' AND isset($ilance->GPC['xcode']) AND $ilance->GPC['xcode'] != "")
{
	$area_title = '{_rfp_takeover_rejection_request_in_progress}';
	$page_title = SITE_NAME . ' - {_rfp_takeover_rejection_request_in_progress}';
	// #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
	
	// rfp takeover acceptance
	$sql = $ilance->db->query("
                SELECT transfer_to_userid, transfer_from_userid, project_title, project_id, cid, filtered_budgetid, status, bids, date_end, description
                FROM " . DB_PREFIX . "projects
                WHERE transfer_code = '" . $ilance->db->escape_string($ilance->GPC['xcode']) . "'
                LIMIT 1
        ", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) == 0)
	{
		$area_title = '{_invalid_rfp_transfer_code}';
		$page_title = SITE_NAME . ' - {_invalid_rfp_transfer_code}';
		print_notice('{_invalid_rfp_transfer_code}', '{_were_sorry_there_was_a_problem_with}<br /><br />{_If_you_are_clicking_a_link_within_your_email_client}<br /><br />{_please_contact_customer_support}', $ilpage['main'], '{_main_menu}');
		exit();
	}
	$res = $ilance->db->fetch_array($sql, DB_ASSOC);
	// new buyer information
	$sqlnew = $ilance->db->query("
		SELECT email, username
		FROM " . DB_PREFIX . "users
		WHERE user_id = '" . $res['transfer_to_userid'] . "'
			AND status = 'active'
	", 0, null, __FILE__, __LINE__);
	$resuser = $ilance->db->fetch_array($sqlnew, DB_ASSOC);
	// old buyer information
	$sqlold = $ilance->db->query("
		SELECT email, username
		FROM " . DB_PREFIX . "users
		WHERE user_id = '" . $res['transfer_from_userid'] . "'
			AND status = 'active'
	", 0, null, __FILE__, __LINE__);

	$resolduser = $ilance->db->fetch_array($sqlold, DB_ASSOC);
	// accept rfp transfer takeover
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "projects
		SET transfer_status = 'rejected',
		transfer_code = ''
		WHERE project_id = '" . $res['project_id'] . "'
			AND transfer_code = '" . $ilance->db->escape_string($ilance->GPC['xcode']) . "'
	", 0, null, __FILE__, __LINE__);
	// budget
	$budget = $ilance->auction->construct_budget_overview($res['cid'], $res['filtered_budgetid']);
	// email old owner
	$ilance->email->mail = $resolduser['email'];
	$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
	$ilance->email->get('rfp_takeover_rejected_old_buyer');
	$ilance->email->set(array(
		'{{transfer_hash}}' => $newmd5hash,
		'{{transfer_to_username}}' => ucfirst($resuser['username']),
		'{{transfer_to_email}}' => $resuser['email'],
		'{{transfer_from_username}}' => ucfirst($resolduser['username']),
		'{{transfer_from_email}}' => $resolduser['email'],
		'{{rfp_title}}' => $res['project_title'],
		'{{status}}' => $ilance->auction->print_auction_status($res['status']),
		'{{bids}}' => $res['bids'],
		'{{closing_date}}' => print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
		'{{description}}' => short_string(stripslashes($res['description']), 150),
		'{{project_id}}' => $res['project_id'],
		'{{budget}}' => $budget
	));
	$ilance->email->send();
	$area_title = '{_rfp_takeover_request_rejected}';
	$page_title = SITE_NAME . ' - {_rfp_takeover_request_rejected}';
	print_notice('{_rfp_takeover_request_was_rejected}', '<p>{_you_have_successfully_rejected_this_rfp_takeover_request_and_nothing_more_is_required_by_you}</p><p>{_an_rfp_takeover_request_allows_project_managers_and_helpful_moderators_better_serve_our_customers_by_helping}</p><p>{_please_contact_customer_support_for_more_information}</p>', HTTP_SERVER . $ilpage['main'], '{_main_menu}');
	exit();
}

// #### INSERT PUBLIC MESSAGE ##################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'insertmessage')
{
	// #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
	if (empty($ilance->GPC['message']))
	{
		print_notice('{_message_cannot_be_empty}', '{_please_retry_your_action}', 'javascript: history.go(-1)', '{_retry}');
		exit();
	}
	$ilance->pmb->insert_public_message(intval($ilance->GPC['pid']), intval($ilance->GPC['buyerid']), $_SESSION['ilancedata']['user']['userid'], $_SESSION['ilancedata']['user']['username'], $ilance->GPC['message'], $visible = '1');
	refresh($ilpage['rfp'] . '?id='.intval($ilance->GPC['pid']) . '&tab=messages#tabmessages');
	exit();
}

// #### REMOVE PUBLIC MESSAGE ##################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'removemessage' AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	// #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
	if (empty($ilance->GPC['messageid']))
	{
		print_notice('{_message_does_not_exist}', '{_please_retry_your_action}', 'javascript: history.go(-1)', '{_retry}');
		exit();
	}
	$sql = $ilance->db->query("
                DELETE FROM " . DB_PREFIX . "messages
                WHERE messageid = '" . intval($ilance->GPC['messageid']) . "'
                        AND project_id = '" . intval($ilance->GPC['pid']) . "'
                LIMIT 1
        ", 0, null, __FILE__, __LINE__);
	// check for seo...
	refresh($ilpage['rfp'] . '?id=' . intval($ilance->GPC['pid']) . '&tab=messages#tabmessages');
	exit();
}

// #### SUBMIT BID PROPOSAL SERVICE AUCTION ####################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-bid-submit' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'service-bid')
{
	// #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
	unset($_SESSION['ilancedata']['user']['current_proposal']);
	unset($_SESSION['ilancedata']['user']['current_bidamount']);
	unset($_SESSION['ilancedata']['user']['current_estimate_days']);
	$ilance->GPC['paymethod'] = isset($ilance->GPC['paymethod']) ? $ilance->GPC['paymethod'] : '';
	$ilance->GPC['bidstate'] = isset($ilance->GPC['bidstate']) ? $ilance->GPC['bidstate'] : '';
	$ilance->GPC['bidfieldanswers'] = isset($ilance->GPC['custom']) ? $ilance->GPC['custom'] : '';
	$ilance->GPC['lowbidnotify'] = isset($ilance->GPC['lowbidnotify']) ? intval($ilance->GPC['lowbidnotify']) : 0;
	$ilance->GPC['lasthournotify'] = isset($ilance->GPC['lasthournotify']) ? intval($ilance->GPC['lasthournotify']) : 0;
	$ilance->GPC['subscribed'] = isset($ilance->GPC['subscribed']) ? intval($ilance->GPC['subscribed']) : 0;
	$ilance->bid_service->placebid($_SESSION['ilancedata']['user']['userid'], $ilance->GPC['proposal'], $ilance->GPC['lowbidnotify'], $ilance->GPC['lasthournotify'], $ilance->GPC['subscribed'], intval($ilance->GPC['id']), intval($ilance->GPC['project_user_id']), $ilance->GPC['bidamount'], intval($ilance->GPC['estimate_days']), $ilance->GPC['bidstate'], $ilance->GPC['bidamounttype'], '', $ilance->GPC['bidfieldanswers'], $ilance->GPC['paymethod'], true);
}

// #### SUBMIT BID FOR PRODUCT AUCTION #########################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-bid-submit' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'product-bid')
{
	// #### define top header nav ##########################################
	$topnavlink = array(
	'main_buying'
	);
	if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
	{
		refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
		exit();
	}
	// #### lets bid! ######################################################
	$ilance->categories->build_array('product', $_SESSION['ilancedata']['user']['slng'], 0, true);
	$ilance->GPC['highbidnotify'] = isset($ilance->GPC['highbidnotify']) ? intval($ilance->GPC['highbidnotify']) : 0;
	$ilance->GPC['lasthournotify'] = isset($ilance->GPC['lasthournotify']) ? intval($ilance->GPC['lasthournotify']) : 0;
	$ilance->GPC['subscribed'] = isset($ilance->GPC['subscribed']) ? intval($ilance->GPC['subscribed']) : 0;
	$ilance->GPC['shipperid'] = isset($ilance->GPC['shipperid']) ? intval($ilance->GPC['shipperid']) : 0;
	$buyershipcost = ($ilance->GPC['shipperid'] > 0) ? $ilance->shipping->fetch_ship_cost_by_shipperid($ilance->GPC['id'], $ilance->GPC['shipperid'], $ilance->GPC['qty']) : array('total' => 0);
	$ilance->GPC['buyershipcost'] = $buyershipcost['total'];
	$ilance->bid_product->placebid($ilance->GPC['highbidnotify'], $ilance->GPC['lasthournotify'], $ilance->GPC['subscribed'], intval($ilance->GPC['id']), intval($ilance->GPC['project_user_id']), $ilance->GPC['bidamount'], $ilance->GPC['qty'], $_SESSION['ilancedata']['user']['userid'], $ilconfig['productbid_enableproxybid'], $ilance->GPC['minimum'], $ilance->auction->fetch_reserve_price(intval($ilance->GPC['id'])), true, $ilance->GPC['buyershipcost'], $ilance->GPC['shipperid']);
}

// #### SERVICE AUCTION CATEGORY MAP ###########################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'listings')
{
	if ($ilconfig['globalauctionsettings_productauctionsenabled'])
	{
		$seourl = print_seo_url($ilconfig['productcatmapidentifier']);
		$seourl = HTTP_SERVER . $seourl;
		header('Location: ' . $seourl);
		exit();
	}
	// #### prevent duplicate content from search engines
	$seoproductcategories = print_seo_url($ilconfig['productcatmapidentifier']);
	$seoservicecategories = print_seo_url($ilconfig['servicecatmapidentifier']);
	$seolistings = print_seo_url($ilconfig['listingsidentifier']);
	$seocategories = print_seo_url($ilconfig['categoryidentifier']);
	if ($ilconfig['globalauctionsettings_seourls'] AND (!isset($ilance->GPC['sef']) OR empty($ilance->GPC['sef'])))
	{
		$seourl = HTTP_SERVER . $seolistings;
		header('Location: ' . $seourl);
		exit();
	}
	$show['widescreen'] = false;
	$area_title = '{_jobs}<div class="smaller">{_viewing_all_categories}</div>';
	$page_title = '{_jobs} - {_viewing_all_categories} | ' . SITE_NAME;
	// #### define top header nav ##########################################
	$topnavlink = array(
		'main_categories'
	);
	$ilance->categories->build_array('service', $_SESSION['ilancedata']['user']['slng'], 0, true, '', '', 0, -1, 1);
	$cid = !empty($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
	$popularskills = $category = $description = $seeall = $recursivecategory = $auctioncount = $popularsearch = '';
	if (($search_category_pulldown = $ilance->cache->fetch('search_category_pulldown_' . $cid . '_service_' . $_SESSION['ilancedata']['user']['slng'])) === false)
        {
		$search_category_pulldown = $ilance->categories_pulldown->print_root_category_pulldown($cid, 'service', 'cid', $_SESSION['ilancedata']['user']['slng'], $ilance->categories->cats, true);
		$ilance->cache->store('search_category_pulldown_' . $cid . '_service_' . $_SESSION['ilancedata']['user']['slng'], $search_category_pulldown);
        }
	if (($cathtml = $ilance->cache->fetch('cathtml_' . $cid . '_service_' . $_SESSION['ilancedata']['user']['slng'])) === false)
        {
		$cathtml = $ilance->categories->recursive($cid, 'servicecatmap', $_SESSION['ilancedata']['user']['slng'], 0, '', $ilconfig['globalauctionsettings_seourls']);
		$ilance->cache->store('cathtml_' . $cid . '_service_' . $_SESSION['ilancedata']['user']['slng'], $cathtml);
        }
	if (($v3left_nav = $ilance->cache->fetch('v3left_nav_1col_' . $cid . '_service_' . $_SESSION['ilancedata']['user']['slng'] . '_' . $ilconfig['globalfilters_enablecategorycount'] . '_' . $ilconfig['globalauctionsettings_catmapdepth'])) === false)
        {
		$v3left_nav = $ilance->categories_parser->print_subcategory_columns(1, 'service', 1, $_SESSION['ilancedata']['user']['slng'], $cid, '', $ilconfig['globalfilters_enablecategorycount'], 1, 'font-weight: bold;', 'font-weight: normal;', $ilconfig['globalauctionsettings_catmapdepth'], '', false, false);
		$ilance->cache->store('v3left_nav_1col_' . $cid . '_service_' . $_SESSION['ilancedata']['user']['slng'] . '_' . $ilconfig['globalfilters_enablecategorycount'] . '_' . $ilconfig['globalauctionsettings_catmapdepth'], $v3left_nav);
        }
	$show['canpost'] = false;
	if (!empty($cathtml))
	{
		$metatitle = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $cid);
		$metadescription = $ilance->categories->description($_SESSION['ilancedata']['user']['slng'], $cid);
		$metakeywords = $ilance->categories->keywords($_SESSION['ilancedata']['user']['slng'], $cid);
		if (empty($metadescription))
		{
			$metadescription = '{_find_jobs_services_work_and_more_in} ' . $metatitle;
		}
		$area_title = '{_categories}<div class="smaller">' . $metatitle . '</div>';
		$page_title = SITE_NAME . ' - ' . $metadescription;
		$count = $ilance->categories->auctioncount('service', $cid);
		$auctioncount = ($ilconfig['globalfilters_enablecategorycount']) ? '<span class="gray">(' . number_format($count) . ')</span>' : '';
		if ($ilance->categories->can_post($_SESSION['ilancedata']['user']['slng'], 'service', $cid))
		{
			$show['canpost'] = true;
		}
		else
		{
			$show['categorycolumn'] = true;
		}
		unset($count);
	}
	$text = '{_browse_service_auctions_via_marketplace_categories}';
	$latestserviceauctions = $recentlyviewed = array();
	$show['searchbar'] = false;
	if ($cid > 0)
	{
		// #### category trends ########################################
		if ($ilconfig['trend_tab'])
		{
			if (($stats = $ilance->cache->fetch('stats_' . $cid . '_service')) === false)
			{
				$stats = $ilance->auction->fetch_stats_overview($cid, 'service');
				$ilance->cache->store('stats_' . $cid . '_service', $stats);
			}
			$jobcount = number_format($stats['jobcount']);
			$expertcount = number_format($stats['expertcount']);
			$expertsearch = ($expertcount == 0) ? number_format(0, 1) : number_format((($stats['expertsearch'] / $expertcount)), 1);
			$expertsrevenue = $ilance->currency->format($stats['expertsrevenue']);
			unset($stats);
		}
		if ($ilconfig['popular_tab'])
		{
			if (($popularsearch = $ilance->cache->fetch('popularsearch_' . $cid . '_service')) === false)
			{
				$popularsearch = $ilance->cloudtags->print_tag_cloud($cid, 'service');
				$ilance->cache->store('popularsearch_' . $cid . '_service', $popularsearch);
			}
			if (($popularskills = $ilance->cache->fetch('popularskills_' . $cid . '_service')) === false)
			{
				$popularskills = ''; //$ilance->cloudtags->print_tag_cloud($cid, 'service');
				$ilance->cache->store('popularskills_' . $cid . '_service', $popularskills);
			}
		}
		if (($latestserviceauctions = $ilance->cache->fetch('latestserviceauctions_' . $cid . '_service')) === false)
		{
			$latestserviceauctions = $ilance->auction_listing->fetch_latest_auctions('service', 10, 1, $cid);
			$ilance->cache->store('latestserviceauctions_' . $cid . '_service', $latestserviceauctions);
		}
		if (($recentlyviewed = $ilance->cache->fetch('recentreviewedserviceauctions_' . $cid . '_service')) === false)
		{
			$recentlyviewed = $ilance->auction_listing->fetch_recently_viewed_auctions('service', 10, 1, $cid);
			$ilance->cache->store('recentreviewedserviceauctions_' . $cid . '_service', $recentlyviewed);
		}
		if (($categoryresults = $ilance->cache->fetch('categoryresults_4col_' . $cid . '_service_' . $ilconfig['globalfilters_enablecategorycount'] . '_' . $ilconfig['globalauctionsettings_catmapdepth'] . '_' . $_SESSION['ilancedata']['user']['slng'])) === false)
		{
			$categoryresults = $ilance->categories_parser->print_subcategory_columns(4, 'service', 1, $_SESSION['ilancedata']['user']['slng'], $cid, '', $ilconfig['globalfilters_enablecategorycount'], 1, 'font-weight:bold;font-size:14px;line-height:1.5', 'font-weight:normal;line-height:1.5', $ilconfig['globalauctionsettings_catmapdepth'], '', false, true);
			$ilance->cache->store('categoryresults_4col_' . $cid . '_service_' . $ilconfig['globalfilters_enablecategorycount'] . '_' . $ilconfig['globalauctionsettings_catmapdepth'] . '_' . $_SESSION['ilancedata']['user']['slng'], $categoryresults);
		}
		$category = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $cid);
		$description = $ilance->categories->description($_SESSION['ilancedata']['user']['slng'], $cid);
		$text = '{_categories} {_in} ' . $category;
		$seeall = $ilconfig['globalauctionsettings_seourls']
		  ? '<span class="smaller blue"><a href="' . construct_seo_url('servicecatplain', $cid, 0, $category, '', 0, '', 0, 0) . '">{_see_all}</a>&nbsp;' . $auctioncount . '</span>'
		  : '<span class="smaller blue"><a href="' . $ilpage['search'] . '?mode=service&amp;cid=' . $cid . '">{_see_all}</a>&nbsp;' . $auctioncount . '</span>';
		$navcrumb = array();
		$ilance->categories->breadcrumb($cid, 'servicecatmap', $_SESSION['ilancedata']['user']['slng']);
		$ilance->categories->add_category_viewcount($cid);
	}
	else
	{
		if (($categoryresults = $ilance->cache->fetch('categoryresults_4col_' . $cid . '_service_' . $ilconfig['globalfilters_enablecategorycount'] . '_' . $ilconfig['globalauctionsettings_catmapdepth'] . '_' . $_SESSION['ilancedata']['user']['slng'])) === false)
		{
			$categoryresults = $ilance->categories_parser->print_subcategory_columns(4, 'service', 1, $_SESSION['ilancedata']['user']['slng'], $cid, '', $ilconfig['globalfilters_enablecategorycount'], 1, 'font-weight:bold;font-size:14px;line-height:1.5', 'font-weight:normal;line-height:1.5', $ilconfig['globalauctionsettings_catmapdepth'], '', false, true);
			$ilance->cache->store('categoryresults_4col_' . $cid . '_service_' . $ilconfig['globalfilters_enablecategorycount'] . '_' . $ilconfig['globalauctionsettings_catmapdepth'] . '_' . $_SESSION['ilancedata']['user']['slng'], $categoryresults);
		}
		$navurl = ($ilconfig['globalauctionsettings_seourls']) ? HTTP_SERVER . $seoservicecategories : HTTP_SERVER . $ilpage['main'] . '?cmd=categories';
		$navcrumb = array();
		$navcrumb[""] = '{_browse}';
		unset($navurl);
	}
	// if we have no children, redirect user to the appropriate result listings pages for this category
	if (isset($cid) AND $cid > 0 AND $ilance->categories->fetch_children_ids($cid, 'service') == '')
	{
		$url = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('servicecatplain', $cid, 0, $category, '', 0, '', 0, 0) : $ilpage['search'] . '?mode=service&cid=' . $cid;
		header('Location: ' . $url);
		exit();
	}
	$pprint_array = array('jobcount','expertsrevenue','expertcount','popularskills','seeall','seoservicecategories','seoproductcategories','seolistings','seocategories','description','text','categorypulldown','recursivecategory','category','cid','php_self','categoryresults','three_column_subcategory_results','category','number','prevnext','keywords','search_country_pulldown','search_jobtype_pulldown','five_last_keywords_buynow','five_last_keywords_projects','five_last_keywords_providers','search_ratingrange_pulldown','search_awardrange_pulldown','search_bidrange_pulldown','search_listed_pulldown','search_closing_pulldown','search_category_pulldown','distance','subcategory_name','text','prevnext','prevnext2');
	
	($apihook = $ilance->api('rfp_listings_end')) ? eval($apihook) : false;
		
	$ilance->template->fetch('main', 'rfp_listings.html');
	$ilance->template->parse_loop('main', 'latestserviceauctions');
	$ilance->template->parse_loop('main', 'recentlyviewed');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

// #### SERVICE ACTION CATEGORY LISTINGS ######################################
else if (!empty($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0 AND empty($ilance->GPC['cmd']))
{
	// #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
	// update category view count
	$ilance->categories->add_category_viewcount(intval($ilance->GPC['cid']));
	$urlbit = print_hidden_fields(true, array());
	$ilance->categories->build_array('product', $_SESSION['ilancedata']['user']['slng'], 0, false);
	if ($ilance->categories->can_post($_SESSION['ilancedata']['user']['slng'], 'service', intval($ilance->GPC['cid'])))
	{
		header('Location: ' . $ilpage['search'] . '?mode=service' . $urlbit);
		exit();
	}
	$urlbit = print_hidden_fields(true, array('cid'));
	header('Location: ' . $ilpage['rfp'] . '?cmd=listings&cid=' . intval($ilance->GPC['cid']) . $urlbit);
	exit();
}

// #### ADD ITEMS FOR COMPARE VIEW #############################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'auctioncmd')
{
	// #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
	$show['widescreen'] = false;
	// #### empty inline cookie ############################################
	set_cookie('inlineservice', '', false);
	set_cookie('inlineproduct', '', false);
	set_cookie('inlineexperts', '', false);
	// #### require backend ################################################
	// #### COMPARING SEARCH RESULTS #######################################
	if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'compare')
	{
		include_once(DIR_SERVER_ROOT . 'rfp_compare.php');
	}
	// #### ADD ITEMS TO WATCH LIST ########################################
	else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'watchlist')
	{
		if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
		{
			refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
			exit();
		}
		if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'addtowatchlist') == 'no')
		{
			print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource} <span class="blue"><a href="' . HTTP_SERVER . $ilpage['subscription'] . '"><strong>{_click_here}</strong></a>', $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('addtowatchlist'));
			exit();
		}
		$ilance->GPC['project_id'] = isset($ilance->GPC['project_id']) ? $ilance->GPC['project_id'] : array();
		$count = count($ilance->GPC['project_id']);
		if (empty($ilance->GPC['project_id']) OR $count <= 0)
		{
			print_notice('{_nothing_to_do}', '{_you_did_not_select_any_listings_from_the_previous_page_please_retry}', 'javascript:history.back(-1)', '{_try_again}');
			exit();
		}
		for ($i = 0; $i < $count; $i++)
		{
			$sql_watchlist = $ilance->db->query("
				SELECT watchlistid
				FROM " . DB_PREFIX . "watchlist
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND watching_project_id = '" . intval($ilance->GPC['project_id'][$i]) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_watchlist) == 0)
			{
				$ilance->watchlist->insert_item($_SESSION['ilancedata']['user']['userid'], intval($ilance->GPC['project_id'][$i]), 'auction', '');
			}
		}
		refresh(HTTP_SERVER . $ilpage['watchlist']);
		exit();
	}
	// #### INLINE MODERATION TOOLS ########################################
	else
	{
		include_once(DIR_SERVER_ROOT . 'rfp_tools.php');
	}
}
// #### SEARCH RESULT LISTINGS ACTIONS #########################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'expertcmd')
{
	// #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
	if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
	{
		refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
		exit();
	}
	// #### ADD MEMBER TO WATCHLIST FROM SEARCH RESULT LISTING #############
	if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'watchlist')
	{
		if (!isset($ilance->GPC['vendor_id']))
		{
			$area_title = '{_invalid_vendor_id_warning_menu}';
			$page_title = SITE_NAME . ' - {_invalid_vendor_id_warning_menu}';
			print_notice('{_invalid_vendor_profile_id}', '{_your_requested_action_cannot_be_completed_due_to_an_invalid_vendors_id}<br /><br />{_please_contact_customer_support}', $ilpage['main'], '{_main_menu}');
			exit();
		}
		// empty inline cookie
		set_cookie('inlineservice', '', false);
		set_cookie('inlineproduct', '', false);
		set_cookie('inlineexperts', '', false);
		if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'addtowatchlist') == 'no')
		{
			print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource} <span class="blue"><a href="' . HTTP_SERVER . $ilpage['subscription'] . '"><strong>{_click_here}</strong></a>', $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('addtowatchlist'));
			exit();
		}
		$count = count($ilance->GPC['vendor_id']);
		for ($i = 0; $i < $count; $i++)
		{
			$sql_watchlist = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "watchlist
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND watching_user_id = '" . intval($ilance->GPC['vendor_id'][$i]) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_watchlist) == 0)
			{
				$ilance->watchlist->insert_item($_SESSION['ilancedata']['user']['userid'], intval($ilance->GPC['vendor_id'][$i]), 'sprovider', '');
			}
		}
		refresh(HTTP_SERVER . $ilpage['watchlist']);
		exit();
	}
	// #### INVITE MULTIPLE MEMBERS TO NEW OR EXISTING SERVICE AUCTION #####
	else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'invite')
	{
		
		if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'inviteprovider') == 'no')
		{
			$area_title = '{_provider_invitation_denied_upgrade_subscription}';
			$page_title = SITE_NAME . ' - {_provider_invitation_denied_upgrade_subscription}';
			print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource} <span class="blue"><a href="' . HTTP_SERVER . $ilpage['subscription'] . '"><strong>{_click_here}</strong></a>', $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('inviteprovider'));
			exit();
		}
		if (!isset($ilance->GPC['vendor_id']) OR isset($ilance->GPC['vendor_id']) AND $ilance->GPC['vendor_id'] <= 0)
		{
			$area_title = '{_invalid_vendor_id_warning_menu}';
			$page_title = SITE_NAME . ' - {_invalid_vendor_id_warning_menu}';
			print_notice('{_invalid_vendor_profile_id}', '{_your_requested_action_cannot_be_completed_due_to_an_invalid_vendors_id}'."<br /><br />".'{_please_contact_customer_support}', $ilpage['main'], '{_main_menu}');
			exit();
		}
		// empty inline cookie
		set_cookie('inlineexperts', '', false);
		$area_title = '{_inviting_a_provider_to_a_new_or_existing_rfp}';
		$page_title = SITE_NAME . ' - {_inviting_a_provider_to_a_new_or_existing_rfp}';
		$returnurl = isset($ilance->GPC['returnurl']) ? $ilance->GPC['returnurl'] : '';
		$returnurlback = urldecode($returnurl);
		$provider = $hidden_invitations = '';
		$count = count($ilance->GPC['vendor_id']);
		if ($count > 1)
		{
			for ($i = 0; $i < $count; $i++)
			{
				$sql_vendor = $ilance->db->query("
					SELECT user_id
					FROM " . DB_PREFIX . "users
					WHERE user_id = '" . intval($ilance->GPC['vendor_id'][$i]) . "'
						AND status = 'active'
						AND user_id != '" . $_SESSION['ilancedata']['user']['userid'] . "'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql_vendor) > 0)
				{
					$res_vendor = $ilance->db->fetch_array($sql_vendor, DB_ASSOC);
					$provider .= '<span class="black"><strong>' . fetch_user('username', intval($ilance->GPC['vendor_id'][$i])) . '</strong></span>, ';
					$hidden_invitations .= '<input type="hidden" name="invitationid[]" value="' . $res_vendor['user_id'] . '" />';
				}
			}
			$provider = mb_substr($provider, 0, -2);
		}
		else
		{
			// make sure vendors being invted are active
			$sql_vendor = $ilance->db->query("
				SELECT user_id
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . intval($ilance->GPC['vendor_id'][0]) . "'
					AND status = 'active'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_vendor) > 0)
			{
				$res_vendor = $ilance->db->fetch_array($sql_vendor, DB_ASSOC);
				$provider .= '<span class="black"><strong>' . fetch_user('username', intval($ilance->GPC['vendor_id'][0])) . '</strong></span>';
				$hidden_invitations .= '<input type="hidden" name="invitationid[]" value="' . $res_vendor['user_id'] . '" />';
			}
		}
		$invite_pulldown = '<select name="project_id" style="font-family: verdana">';
		$invite_pulldown .= '<optgroup label="{_service_auction}">';
		$invite_pulldown .= '<option value="">{_none}</option>';
		$sql_projects = $ilance->db->query("
			SELECT project_id, project_title, bids, date_end
			FROM " . DB_PREFIX . "projects
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' 
				AND status = 'open'
				AND project_state = 'service'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql_projects) > 0)
		{
			$show['norfps'] = false;
			while ($res = $ilance->db->fetch_array($sql_projects, DB_ASSOC))
			{
				$invite_pulldown .= '<option value="' . $res['project_id'] . '">#' . $res['project_id'] . ': ' . short_string(stripslashes($res['project_title']), 35) . ' ({_bids}: ' . $res['bids'] . ') ({_ends}: ' . print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0) . ')</option>';
			}
		}
		else
		{
			$show['norfps'] = true;
			$invite_pulldown .= '<option value="">{_no_rfps_available}</option>';
		}
		$invite_pulldown .= '</optgroup>';
		$invite_pulldown .= '</select>';
		$navcrumb = array();
		$navcrumb[""] = '{_invite_to_bid}';
		$pprint_array = array('returnurlback','hidden_invitations','invite_pulldown','provider','project_user_id','cid','currency_id','project_id','portfolio_id','bid_id','filehash','category_id','user_id','project_title','project_id','remote_addr');
		$ilance->template->fetch('main', 'rfp_invitetobid.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'delete')
	{
		if (isset($ilance->GPC['vendor_id']) AND is_array($ilance->GPC['vendor_id']) AND count($ilance->GPC['vendor_id']) > 0)
		{
			$removedusers = $ilance->admincp_users->remove_user($ilance->GPC['vendor_id'], true, true, true, true, true, 0);
			if (!empty($removedusers))
			{
				$removedusers = mb_substr($removedusers, 0, -2);
				print_notice('{_action_completed}', $removedusers . "{_these_customers_will_not_be_able_to_login_to_the_marketplace_unless}", $ilpage['search'] . '?q=&mode=experts&sort=01', '{_back}');
				exit();
			}
			else
			{
				print_notice('{_invalid_vendor_profile_id}', '{_your_requested_action_cannot_be_completed_due_to_an_invalid_vendors_id}'."<br /><br />".'{_please_contact_customer_support}', $ilpage['search'].'?q=&mode=experts&sort=01', '{_back}');
				exit();	
			}
		}
		else
		{
			print_notice('{_invalid_vendor_profile_id}', '{_your_requested_action_cannot_be_completed_due_to_an_invalid_vendors_id}'."<br /><br />".'{_please_contact_customer_support}', $ilpage['main'], '{_main_menu}');
			exit();
		}
	}
}
// #### INVITE SINGLE MEMBERS TO NEW OR EXISTING SERVICE AUCTION ###############
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'rfp-invitation' AND isset($ilance->GPC['id']))
{
	// #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
	if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] <= 0)
	{
		refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
		exit();
	}
	$area_title = '{_inviting_a_provider_to_a_new_or_existing_rfp}';
	$page_title = SITE_NAME . ' - {_inviting_a_provider_to_a_new_or_existing_rfp}';
	if ($ilance->GPC['id'] == $_SESSION['ilancedata']['user']['userid'])
	{
		$area_title = '{_access_denied}';
		$page_title = SITE_NAME . ' - {_access_denied}';

		print_notice('{_cannot_invite_yourself}', '{_please_retry_your_action}', "javascript: history.go(-1)", '{_return_to_the_previous_menu}');
		exit();
	}
	// empty inline cookie
	set_cookie('inlineexperts', '', false);
	$sql_vendor = $ilance->db->query("
		SELECT user_id
		FROM " . DB_PREFIX . "users
		WHERE user_id = '" . intval($ilance->GPC['id']) . "'
		LIMIT 1
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql_vendor) == 0)
	{
		$area_title = '{_invalid_vendor_id_warning_menu}';
		$page_title = SITE_NAME . ' - {_invalid_vendor_id_warning_menu}';
		print_notice('{_invalid_vendor_profile_id}', '{_your_requested_action_cannot_be_completed_due_to_an_invalid_vendors_id}'."<br /><br />".'{_please_contact_customer_support}', $ilpage['main'], '{_main_menu}');
		exit();
	}
	$res_vendor = $ilance->db->fetch_array($sql_vendor, DB_ASSOC);
	$provider = print_username(intval($ilance->GPC['id']), 'href');
	$invitationid = $res_vendor['user_id'];
	$hidden_invitations = '<input type="hidden" name="invitationid[]" value="'.intval($ilance->GPC['id']).'" />';
	$invite_pulldown = '<select name="project_id" style="font-family: verdana">';
	$invite_pulldown .= '<option value="">{_select_rfp}:</option>';
	$sql_projects = $ilance->db->query("
		SELECT project_id, project_title, bids, date_end
		FROM " . DB_PREFIX . "projects
		WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			AND project_state = 'service'
			AND status = 'open'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql_projects) > 0)
	{
		$show['norfps'] = false;
		while ($res = $ilance->db->fetch_array($sql_projects))
		{
			$invite_pulldown .= '<option value="' . $res['project_id'] . '">#' . $res['project_id'] . ': ' . short_string(stripslashes($res['project_title']), 35) . ' ({_bids}: ' . $res['bids'] . ') ({_ends}: ' . print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0) . ')</option>';
		}
	}
	else
	{
		$show['norfps'] = true;
		$invite_pulldown .= '<option value="">{_no_rfps_available}</option>';
	}
	$invite_pulldown .= '</select>';
	$navcrumb = array();
	$navcrumb[""] = '{_invite_to_bid}';
	$pprint_array = array('invitationid','hidden_invitations','invite_pulldown','provider','project_user_id','cid','currency_id','project_id','portfolio_id','bid_id','filehash','category_id','user_id','attachtype','max_filesize','category','current_proposal','current_estimate_days','delivery_pulldown','currency','title','description','budget','bid_controls','buyer_incomespent','buyer_stars','project_title','description','project_type','project_details','project_distance','project_id','bid_details','pmb','projects_posted','projects_awarded','project_currency','distance','subcategory_name','text','prevnext','prevnext2');

	$ilance->template->fetch('main', 'rfp_invitetobid.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

// #### PREVIEW BID FOR SERVICE AUCTION ########################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-bid-preview' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'service-bid')
{
	// #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
	if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] <= 0)
	{
		refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
		exit();
	}
	$ilance->categories->build_array('service', $_SESSION['ilancedata']['user']['slng'], 0, false);
	if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'servicebid') == 'no')
	{
		$area_title = '{_bid_preview_denied_upgrade_subscription}';
		$page_title = SITE_NAME . ' - {_bid_preview_denied_upgrade_subscription}';

		print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource} <span class="blue"><a href="' . HTTP_SERVER . $ilpage['subscription'] . '"><strong>{_click_here}</strong></a>', $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('servicebid'));
		exit();
	}
	$area_title = '{_bid_proposal}: {_preview}';
	$page_title = SITE_NAME . ' - {_bid_proposal}: {_preview}';
	$id = intval($ilance->GPC['id']);
	$currencyid = fetch_auction('currencyid', $id);
	// is our bid within proper budget restrictions?
	if ($ilance->bid->is_valid_bid_range($id, $ilance->GPC['bidamount'], $ilance->GPC['filtered_bidtype'], $ilance->GPC['estimate_days']) == 0)
	{
		print_notice('{_budget_range_restriction}', '{_sorry_the_buyer_of_this_auction_has_set_a_preferred_budget_range}', 'javascript: history.go(-1)', ucwords('{_click_here}'));
		exit();
	}
	// #### PROCESS PREVIEW POST ###################################
	// we assume the user has just posted his message and a preview is being requested
	// we will determine if the wysiwyg editor is enabled before we decide what to do
	$current_proposal = (!empty($ilance->GPC['proposal'])) ? $ilance->GPC['proposal'] : '';
	$paymethod = (!empty($ilance->GPC['paymethod'])) ? $ilance->GPC['paymethod'] : '';
	// #### PREVIEW IN HTML ########################################
	// our text is already converted to bbcode so for preview, we will parse it back to html
	$current_proposal_preview = ($ilconfig['default_proposal_wysiwyg'] == 'bbeditor') ? $ilance->bbcode->bbcode_to_html($current_proposal) : $current_proposal;
	// #### RELOAD INTO HIDDEN FIELD #######################
	$current_proposal = htmlspecialchars_uni($current_proposal);
	$current_bidamount = $ilance->currency->string_to_number($ilance->GPC['bidamount']);
	$bidamount_formatted = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $current_bidamount, $currencyid);
	$bidcustom = '';
	if (isset($ilance->GPC['bidcustom']))
	{
		$bidcustom = $ilance->GPC['bidcustom'];
	}
	$current_bidamounttype = strip_tags($ilance->GPC['filtered_bidtype']);
	$current_estimate_days = isset($ilance->GPC['estimate_days']) ? $ilance->currency->string_to_number($ilance->GPC['estimate_days']) : 1;
	$project_id = $id;
	$ilance->bid->bid_filter_checkup($id);
	$sql_rfp = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "projects
		WHERE project_id = '" . intval($id) . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql_rfp) > 0)
	{
		$res_rfp = $ilance->db->fetch_array($sql_rfp, DB_ASSOC);
		$budget = $ilance->auction->construct_budget_overview($res_rfp['cid'], $res_rfp['filtered_budgetid']);
		$title = stripslashes($res_rfp['project_title']);
		$category = stripslashes($ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $res_rfp['cid']));
		$category = '<a href="' . $ilpage['rfp'] . '?cid=' . $res_rfp['cid'] . '">' . $category . '</a>';
		// make sure bidder is not the owner of the project
		if (!empty($_SESSION['ilancedata']['user']['userid']) AND $res_rfp['user_id'] == $_SESSION['ilancedata']['user']['userid'])
		{
			$area_title = '{_bad_rfp_warning}';
			$page_title = SITE_NAME . ' - {_bad_rfp_warning}';
			print_notice('{_auction_owners_cannot_bid_on_their_own_auctions}', '{_were_sorry_auction_owners_can_not_place_bid_on_their_own_auctions}<br /><br />{_please_contact_customer_support}', 'javascript:history.back(1);', '{_back}');
			exit();
		}
		switch ($ilance->GPC['filtered_bidtype'])
		{
			case 'entire':
			{
				$bidamounttype_formatted = '{_for_entire_project}';
				break;
			}
			case 'hourly':
			{
				$bidamounttype_formatted = '{_per_hour}';
				break;
			}
			case 'daily':
			{
				$bidamounttype_formatted = '{_per_day}';
				break;
			}
			case 'weekly':
			{
				$bidamounttype_formatted = '{_weekly}';
				break;
			}
			case 'monthly':
			{
				$bidamounttype_formatted = '{_monthly}';
				break;
			}
			case 'lot':
			{
				$bidamounttype_formatted = '{_per_lot}';
				break;
			}
			case 'weight':
			{
				$bidamounttype_formatted = '{_per_weight} ' . $bidcustom . ' ' . $res_rfp['filtered_bidtypecustom'];
				break;
			}
			case 'item':
			{
				$bidamounttype_formatted = '{_per_item}';
				break;
			}
		}
	}
	// fetch method name used for this bidding event
	$method = $ilance->auction->construct_measure($ilance->GPC['filtered_bidtype']);
	$project_user_id = $res_rfp['user_id'];
	$cid = $res_rfp['cid'];
	$attachment_style = ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'attachments') == 'yes') ? '' : 'disabled="disabled"';
	$hiddeninput = array(
		'attachtype' => 'bid',
		'project_id' => $project_id,
		'user_id' => $_SESSION['ilancedata']['user']['userid'],
		'category_id' => $cid,
		'filehash' => md5(time()),
		'max_filesize' => $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'uploadlimit')
	);
	$uploadbutton = '<input style="font-size:13px" name="attachment" onclick=Attach("' . $ilpage['upload'] . '?crypted=' . encrypt_url($hiddeninput) . '") type="button" value="{_upload}" class="buttons" ' . $attachment_style . ' />';
	$sql_attachments = $ilance->db->query("
		SELECT visible, filename, filehash, filesize
		FROM " . DB_PREFIX . "attachment 
		WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' 
			AND category_id = '" . $cid . "' 
			AND project_id = '" . $project_id . "'
			AND attachtype ='bid'
	");
	$previewattachmentlist = '';
	while ($res = $ilance->db->fetch_array($sql_attachments, DB_ASSOC))
	{
		$moderated = '';
		if ($res['visible'] == 0)
		{
			$moderated = '[{_review_in_progress}]';
			$attachment_link = $res['filename'];
		}
		else
		{
			$attachment_link = '<a href="' . HTTP_SERVER . $ilpage['attachment'] . '?id=' . $res['filehash'] . '" target="_blank">' . $res['filename'] . '</a>';
		}
		$previewattachmentlist .= '<div><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'paperclip.gif" border="0" alt="' . $res['filename'] . '" /> ' . $attachment_link . ' (' . $res['filesize'] . ' {_bytes}) ' . $moderated . '</div>';
	}
	$current_lowbidnotify = $current_lasthournotify = $current_subscribed = 0;
	if (isset($ilance->GPC['lowbidnotify']) AND $ilance->GPC['lowbidnotify'])
	{
		$current_lowbidnotify = 1;
	}
	if (isset($ilance->GPC['lasthournotify']) AND $ilance->GPC['lasthournotify'])
	{
		$current_lasthournotify = 1;
	}
	if ($current_lowbidnotify OR $current_lasthournotify)
	{
		$current_subscribed = 1;
	}
	$finalvaluefees = $ilance->auction_post->print_final_value_fees($cid, 'service', $ilance->GPC['filtered_bidtype']);
	$bidstate = '';
	$calculated_amount = $ilance->bid->fetch_bid_info(0, 'totalamountinput', $ilance->GPC['filtered_bidtype'], $current_bidamount, $current_estimate_days);
	$current_calculated_amount = $ilance->currency->format($calculated_amount, $res_rfp['currencyid']);
	$_SESSION['ilancedata']['user']['current_proposal'] = $current_proposal;
	$_SESSION['ilancedata']['user']['current_bidamount'] = $current_bidamount;
	$_SESSION['ilancedata']['user']['current_estimate_days'] = $current_estimate_days;
	$fvf = $ilance->escrow_fee->fetch_calculated_amount('fvf', $calculated_amount, 'serviceprovider', $cid, $ilance->GPC['filtered_bidtype'], $_SESSION['ilancedata']['user']['userid']);
	$fvf = $ilance->currency->format($fvf);
	$paymethod_preview = $paymethod;
	if (mb_substr($paymethod, 0, 8) == 'offline_')
	{
		$paymethod_preview = '{' . mb_substr(handle_input_keywords($paymethod), 8) . '}';
	}
	else if ($paymethod == 'escrow')
	{
		$paymethod_preview = '{_' . $paymethod . '}';
	}
	
	// #### handle custom bid fields #######################
	$custom_bid_fields_preview = $ilance->bid_fields->construct_bid_fields($cid, $project_id, 'preview', 'service', 0, false);
	$navcrumb = array();
	$navcrumb["$ilpage[rfp]?cmd=listings"] = '{_services}';
	$navcrumb["$ilpage[rfp]?id=" . $id] = $title;
	$navcrumb["$ilpage[rfp]?cmd=bid&id=" . $id] = '{_place_bid}';
	$navcrumb[""] = '{_preview}';
	$pprint_array = array('paymethod_preview','paymethod','paymentmethods','previewattachmentlist','custom_bid_fields_preview','fvf','current_calculated_amount','finalvaluefees','method','bidcustom','bidamounttype_formatted','current_bidamounttype','current_proposal_preview','uploadbutton','bidamount_formatted','bidstate','current_lowbidnotify','current_lasthournotify','current_subscribed','project_user_id','cid','current_email_clarification','currency_id','attachment_style','pmb_id','project_id','portfolio_id','bid_id','filehash','category_id','user_id','attachtype','max_filesize','category','current_proposal','current_bidamount','current_estimate_days','delivery_pulldown','currency','title','description','budget','bid_controls','buyer_incomespent','buyer_stars','project_title','description','project_type','project_details','project_distance','project_id','bid_details','pmb','projects_posted','projects_awarded','project_currency','distance','subcategory_name','text','prevnext','prevnext2');
	
	($apihook = $ilance->api('rfp_place_service_bid_preview_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'rfp_placebid_preview.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### PREVIEW BID FOR PRODUCT AUCTION ########################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-bid-preview' AND isset($ilance->GPC['minimum']) AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'product-bid')
{
	// #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
	if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
	{
		refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
		exit();
	}
	$ilance->categories->build_array('product', $_SESSION['ilancedata']['user']['slng'], 0, false);
	if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'productbid') == 'no')
	{
		$area_title = '{_bid_preview_denied_upgrade_subscription}';
		$page_title = SITE_NAME . ' - {_bid_preview_denied_upgrade_subscription}';
		print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource} <span class="blue"><a href="' . HTTP_SERVER . $ilpage['subscription'] . '"><strong>{_click_here}</strong></a>', $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('productbid'));
		exit();
	}
	$id = intval($ilance->GPC['id']);
	$ilance->bid->bid_filter_checkup($id);
	$bid_limit_per_day = $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'bidlimitperday');
	$bid_limit_per_month = $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'bidlimitpermonth');
	$bid_per_day = fetch_user_bidcount_per('day', $_SESSION['ilancedata']['user']['userid']);
	$bid_per_month = fetch_user_bidcount_per('month', $_SESSION['ilancedata']['user']['userid']);
	if ($bid_per_month >= $bid_limit_per_month)
	{
		$area_title = '{_access_to_bid_is_denied}';
		$page_title = SITE_NAME . ' - {_access_to_bid_is_denied}';
		print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource} <a href="' . $ilpage['subscription'] . '"><strong>{_click_here}</strong></a>', $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('[bidlimitpermonth'));
		exit();
	} 
	if ($bid_per_day >= $bid_limit_per_day)
	{
		$area_title = '{_access_to_bid_is_denied}';
		$page_title = SITE_NAME . ' - {_access_to_bid_is_denied}';
		print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource} <a href="' . $ilpage['subscription'] . '"><strong>{_click_here}</strong></a>', $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('bidlimitperday'));
		exit();
	}
	// avoid bid minimum manipulations
	$sql_startprice = $ilance->db->query("
		SELECT startprice, currentprice
		FROM " . DB_PREFIX . "projects
		WHERE project_id = '" . intval($id) . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql_startprice) > 0)
	{
		$res = $ilance->db->fetch_array($sql_startprice, DB_ASSOC);
		$ilance->GPC['minimum'] = ($ilance->GPC['minimum'] < $res['startprice']) ? $res['startprice'] : $ilance->GPC['minimum'];
		if ($res['currentprice'] > $ilance->GPC['minimum'])
		{
			$ilance->GPC['minimum'] = $res['currentprice'];
		}
	}
	if ($ilance->GPC['bidamount'] >= $ilance->GPC['minimum'])
	{
		$area_title = '{_previewing_bid_proposal}';
		$page_title = SITE_NAME . ' - {_previewing_bid_proposal}';
	}
	else
	{
		$area_title = '{_bid_preview_denied_bad_bid_minimum_entered}';
		$page_title = SITE_NAME . ' - {_bid_preview_denied_bad_bid_minimum_entered}';
		print_notice('{_bid_minimum_warning}', '{_were_sorry_in_order_to_place_a_bid_on_this_auction_your_bid_amount_must_be_the_same}', 'javascript:history.back(1);', '{_back}');
		exit();
	}
	$sql_bid_qty = $ilance->db->query("
		SELECT buynow_qty, currencyid
		FROM " . DB_PREFIX . "projects
		WHERE project_id = '" . $id . "'
	", 0, null, __FILE__, __LINE__);
	$res_bid_qty = $ilance->db->fetch_array($sql_bid_qty, DB_ASSOC);
	$ilance->GPC['qty'] = isset($ilance->GPC['qty']) ? intval($ilance->GPC['qty']) : 0;
	if ($res_bid_qty['buynow_qty'] < $ilance->GPC['qty'])
	{
		print_notice('{_bid_minimum_warning}', '{_were_sorry_in_order_to_place_a_bid_on_this_auction_your_quantity_must_be_the_same_as_available_or_lower}', 'javascript:history.back(1);', '{_back}');
		exit();
	}
	$state = 'product';
	$current_bidamount = sprintf("%01.2f", $ilance->GPC['bidamount']);
	$current_bidamountformatted = $ilance->currency->format($ilance->GPC['bidamount'], $res_bid_qty['currencyid']);
	// category details
	$sql_rfp = $ilance->db->query("
		SELECT filtered_auctiontype, user_id, cid, filtered_budgetid, project_title, buynow_qty_lot, items_in_lot
		FROM " . DB_PREFIX . "projects
		WHERE project_id = '" . intval($id) . "'
			AND project_state = 'product'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql_rfp) > 0)
	{
		$res_rfp = $ilance->db->fetch_array($sql_rfp, DB_ASSOC);
		if ($res_rfp['user_id'] == $_SESSION['ilancedata']['user']['userid'])
		{
			$area_title = '{_access_denied}';
			$page_title = SITE_NAME . ' - {_access_denied}';
			print_notice($area_title, '{_it_appears_you_are_the_seller_of_this_listing_in_this_case_you_cannot_bid_or_purchase_items_from_your_own_listing}', 'javascript:history.back(1);', '{_back}');
			exit();
		}
		$auctiontype = $res_rfp['filtered_auctiontype'];
		$budget = $ilance->auction->construct_budget_overview($res_rfp['cid'], $res_rfp['filtered_budgetid']);
		$title = stripslashes($res_rfp['project_title']);
		$category = stripslashes($ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $res_rfp['cid']));
		$category = '<a href="' . $ilpage['merch'] . '?cid=' . $res_rfp['cid'] . '">' . $category . '</a>';
	}
	$project_user_id = $res_rfp['user_id'];
	// watchlist bid notification
	$current_highbidnotify = isset($ilance->GPC['highbidnotify']) ? $ilance->GPC['highbidnotify'] : 1;
	$current_lasthournotify = isset($ilance->GPC['lasthournotify']) ? $ilance->GPC['lasthournotify'] : 0;
	$current_subscribed = isset($ilance->GPC['subscribed']) ? $ilance->GPC['subscribed'] : 1;
	$bidstate = $proxytext = $shippingservice = '';
	$shipperid = 0;
	$min_bidamount = isset($ilance->GPC['minimum']) ? $ilance->GPC['minimum'] : '0.00';
	$show['categoryuseproxybid'] = false;
	if ($ilconfig['productbid_enableproxybid'] AND $ilance->categories->useproxybid($_SESSION['ilancedata']['user']['slng'], $res_rfp['cid']))
	{
		$show['categoryuseproxybid'] = true;
		$proxytext = '<a href="javascript:void(0)" onmouseover="Tip(phrase[\'_when_you_place_a_bid_for_an_item_enter_the_maximum_amount\'], BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a> {_proxy}';
	}
	$qty = isset($ilance->GPC['qty']) ? intval($ilance->GPC['qty']) : 1;
	if (isset($ilance->GPC['shipperid']) AND $ilance->GPC['shipperid'] > 0)
	{
		$shippingservice = $ilance->shipping->print_shipping_partner($ilance->GPC['shipperid']);
		$shipperid = intval($ilance->GPC['shipperid']);
	}
	$items_in_lot = $res_rfp['items_in_lot'];
	$show['lot'] =  $res_rfp['buynow_qty_lot'] == '1' ? true : false;
	$navcrumb = array();
	$navcrumb["$ilpage[merch]?cmd=listings"] = '{_product_auctions}';
	$navcrumb["$ilpage[merch]?id=" . $id] = $id;
	$navcrumb["$ilpage[rfp]?cmd=bid&id=" . $id . "&state=product"] = '{_placing_a_bid}';
	$navcrumb[""] = '{_preview_bid}';
	$pprint_array = array('items_in_lot','shipperid','shippingservice','qty','current_bidamountformatted','proxytext','min_bidamount','state','id','bidstate','current_highbidnotify','current_lasthournotify','current_subscribed','project_user_id','cid','current_email_clarification','currency_id','attachment_style','pmb_id','portfolio_id','bid_id','filehash','category_id','user_id','attachtype','max_filesize','category','current_proposal','current_bidamount','current_estimate_days','delivery_pulldown','currency','title','description','budget','bid_controls','buyer_incomespent','buyer_stars','project_title','description','project_type','project_details','project_distance','project_id','bid_details','pmb','projects_posted','projects_awarded','project_currency','distance','subcategory_name','text','prevnext','prevnext2');

	($apihook = $ilance->api('rfp_bid_preview_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'listing_forward_auction_placebid_preview.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### PLACE BID HANDLER FOR SERVICE AUCTION ##################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'bid' AND (isset($ilance->GPC['state']) AND $ilance->GPC['state'] != 'product' OR empty($ilance->GPC['state'])))
{
	// #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
	$id = intval($ilance->GPC['id']);
	$area_title = '{_placing_a_bid}';
	$page_title = SITE_NAME . ' - {_placing_a_bid}';
	$navcrumb = array();
	$navcrumb["$ilpage[rfp]?cmd=listings"] = '{_services}';
	$navcrumb["$ilpage[rfp]?id=" . $id] = fetch_auction('project_title', $id);
	$navcrumb[""] = $area_title;
	if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
	{
		$ilance->categories->build_array('service', $_SESSION['ilancedata']['user']['slng'], 0, false);
		$project_state = fetch_auction('project_state', $id);
		if ($project_state != 'service')
		{
			$area_title = '{_access_to_bid_is_denied}';
			$page_title = SITE_NAME . ' - {_access_to_bid_is_denied}';
			print_notice('{_access_denied}', '{_access_denied}', $ilpage['main'], ucwords('{_click_here}'));
			exit();
		}
		if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'servicebid') == 'no')
		{
			$area_title = '{_access_to_bid_is_denied}';
			$page_title = SITE_NAME . ' - {_access_to_bid_is_denied}';
			print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource} <span class="blue"><a href="' . HTTP_SERVER . $ilpage['subscription'] . '"><strong>{_click_here}</strong></a>', $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('servicebid'));
			exit();
		}
		$bid_limit_per_day = $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'bidlimitperday');
		$bid_limit_per_month = $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'bidlimitpermonth');
		$bid_per_day = fetch_user_bidcount_per('day', $_SESSION['ilancedata']['user']['userid']);
		$bid_per_month = fetch_user_bidcount_per('month', $_SESSION['ilancedata']['user']['userid']);
		if ($bid_per_day > $bid_limit_per_day OR $bid_per_day == $bid_limit_per_day OR $bid_per_month > $bid_limit_per_month OR $bid_per_month == $bid_limit_per_month)
		{
			$area_title = '{_access_to_bid_is_denied}';
			$page_title = SITE_NAME . ' - {_access_to_bid_is_denied}';
			print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource}'." <a href='".$ilpage['subscription']."'><strong>".'{_click_here}'."</strong></a>", $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('bidlimitperday'));
			exit();
		}
		if (empty($ilance->GPC['id']) OR $ilance->GPC['id'] == 0)
		{
			$area_title = '{_bad_rfp_warning}';
			$page_title = SITE_NAME . ' - {_bad_rfp_warning}';
			print_notice('{_invalid_rfp_specified}', '{_your_request_to_review_or_place_a_bid_on_a_valid_request_for_proposal}', $ilpage['search'], '{_search_rfps}');
			exit();
		}
		// the ending true defines if the user can't bid, show a template.  Set false to use as boolean true/false.
		$ilance->bid->user_can_bid($_SESSION['ilancedata']['user']['userid'], $id, true);

		($apihook = $ilance->api('rfp_place_service_bid')) ? eval($apihook) : false;

		// show watchlist options
		$sql = $ilance->db->query("
                        SELECT hourleftnotify, lowbidnotify
                        FROM " . DB_PREFIX . "watchlist
                        WHERE watching_project_id = '" . intval($id) . "' 
                                AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) == 0)
		{
			$lasthour   = '<input type="checkbox" name="lasthournotify" id="lasthournotify" value="1" />';
			$lowerbid   = '<input type="checkbox" name="lowbidnotify" id="lowbidnotify" value="1" />';
			$subscribed = '<input type="checkbox" name="subscribed" id="subscribed" value="1" />';
		}
		else
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$lasthour = (($res['hourleftnotify'])
					? '<input checked type="checkbox" name="lasthournotify" id="lasthournotify" value="1" /> '
					: '<input type="checkbox" name="lasthournotify" id="lasthournotify" value="1" /> ');
				$lowerbid = (($res['lowbidnotify'])
					? '<input checked type="checkbox" name="lowbidnotify" id="lowbidnotify" value="1"  />'
					: '<input type="checkbox" name="lowbidnotify" id="lowbidnotify" value="1" />');
			}
		};
		// check if this project is by invite only
		$res_invite_checklist = array();
		$sql_invite_checklist = $ilance->db->query("
                        SELECT *, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
                        FROM " . DB_PREFIX . "projects 
                        WHERE project_id = '" . $id . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql_invite_checklist) > 0)
		{
			$res_invite_checklist = $ilance->db->fetch_array($sql_invite_checklist, DB_ASSOC);
			// make sure bidder is not the owner of the project
			if (!empty($_SESSION['ilancedata']['user']['userid']) AND $res_invite_checklist['user_id'] == $_SESSION['ilancedata']['user']['userid'])
			{
				$area_title = '{_bad_rfp_warning}';
				$page_title = SITE_NAME . ' - {_bad_rfp_warning}';
				print_notice('{_auction_owners_cannot_bid_on_their_own_auctions}', '{_were_sorry_auction_owners_can_not_place_bid_on_their_own_auctions}'."<br /><br />".'{_please_contact_customer_support}', 'javascript:history.back(1);', '{_back}');
				exit();
			}
			if ($res_invite_checklist['project_details'] == 'invite_only')
			{
				// invite only auction
				$sign = '+';
				if ($res_invite_checklist['mytime'] < 0)
				{
					$res_invite_checklist['mytime'] = - $res_invite_checklist['mytime'];
					$sign = '-';
				}
				if ($sign == '-')
				{
					$area_title = '{_this_rfp_has_expired_bidding_is_over}';
					$page_title = SITE_NAME . ' - {_this_rfp_has_expired_bidding_is_over}';
					print_notice('{_bid_filter_restriction}' . "&nbsp;" . '{_this_rfp_has_expired_bidding_is_over}', '{_were_sorry_this_project_owner_has_set_bid_filtering_permissions_on_their_project}'."<br /><br />".'{_bid_filtering_allows_the_buyer_to_filter_various_aspects_of_their_project}'."<br /><br />".'{_to_learn_more_about_bid_filter_permissions}'." <a href=\"javascript:void(0)\" onClick=Attach(\"".$ilpage['rfp']."?msg=bid-permissions\") >".'{_click_here}'."</a>", $ilpage['main'], '{_main_menu}');
					exit();
				}
				// project is by invitation only
				$sql_checklist_invite = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "project_invitations
                                        WHERE project_id = '" . intval($id) . "'
                                                AND buyer_user_id = '" . $res_invite_checklist['user_id'] . "'
                                                AND seller_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql_checklist_invite) > 0)
				{
					$sign = '+';
					if ($res_invite_checklist['mytime'] < 0)
					{
						$res_invite_checklist['mytime'] = - $res_invite_checklist['mytime'];
						$sign = '-';
					}
					if ($sign == '-')
					{
						$area_title = '{_this_rfp_has_expired_bidding_is_over}';
						$page_title = SITE_NAME . ' - {_this_rfp_has_expired_bidding_is_over}';
						print_notice('{_bid_filter_restriction}' . "&nbsp;" . '{_this_rfp_has_expired_bidding_is_over}', '{_were_sorry_this_project_owner_has_set_bid_filtering_permissions_on_their_project}'."<br /><br />".'{_bid_filtering_allows_the_buyer_to_filter_various_aspects_of_their_project}'."<br /><br />".'{_to_learn_more_about_bid_filter_permissions}'." <a href=\"javascript:void(0)\" onClick=Attach(\"".$ilpage['rfp']."?msg=bid-permissions\") >".'{_click_here}'."</a>", $ilpage['main'], '{_main_menu}');
						exit();
					}
					$ilance->bid->bid_filter_checkup($id);
					$area_title = '{_placing_a_bid}';
					$page_title = SITE_NAME . ' - {_placing_a_bid}';
					// let's fetch the existing bid information for this bid proposal
					$current_proposal = $current_bidamount = $current_estimate_days = '';
					$res_bid = array();
					$show['bidexists'] = false;
					$sql_bid = $ilance->db->query("
                                                SELECT *
                                                FROM " . DB_PREFIX . "project_bids
                                                WHERE project_id = '" . $id . "'
                                                        AND bidstatus != 'declined'
                                                        AND bidstate != 'retracted' 
                                                        AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                                ORDER BY bid_id DESC
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql_bid) > 0)
					{
						$res_bid = $ilance->db->fetch_array($sql_bid, DB_ASSOC);
						$show['bidexists'] = true;
						$current_proposal = stripslashes($res_bid['proposal']);
						$current_bidamount = sprintf("%01.2f", $res_bid['bidamount']);
						$current_estimate_days = intval($res_bid['estimate_days']);
						$ilance->GPC['paymethod'] = $res_bid['winnermarkedaspaidmethod'];

					}
					$wysiwyg_area = print_wysiwyg_editor('proposal', $current_proposal, 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg'], false, '590', '120', '', $ilconfig['default_proposal_wysiwyg'], $ilconfig['ckeditor_proposaltoolbar']);
					// project details
					$sql_rfp = $ilance->db->query("
                                                SELECT *
                                                FROM " . DB_PREFIX . "projects
                                                WHERE project_id = '" . $id . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql_rfp) > 0)
					{
						$res_rfp = $ilance->db->fetch_array($sql_rfp, DB_ASSOC);
						$title = stripslashes($res_rfp['project_title']);
						$project_id = $id;
						$category = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $res_rfp['cid']);
						$category = '<a href="' . $ilpage['rfp'] . '?cid=' . $res_rfp['cid'] . '">' . $category . '</a>';
						$cid = $res_rfp['cid'];
						$attachment_style = ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'attachments') == 'yes')? '': 'disabled="disabled"';
						$hiddeninput = array(
							'attachtype' => 'bid',
							'project_id' => $id,
							'user_id' => $_SESSION['ilancedata']['user']['userid'],
							'category_id' => $res_rfp['cid'],
							'filehash' => md5(time()),
							'max_filesize' => $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'uploadlimit')
						);
						$uploadbutton = '<input name="attachment" style="font-size:13px" onclick=Attach("'.$ilpage['upload'].'?crypted='.encrypt_url($hiddeninput).'") type="button" value="{_upload}" class="buttons" '.$attachment_style.' />';
						// show bid amount pulldown but disable based on buyers bid type payout preference
						if ($res_rfp['filter_bidtype'])
						{
							$bidamounttype_pulldown = '<input id="hidden_filtered_bidtype" type="hidden" name="filtered_bidtype" value="' . $res_rfp['filtered_bidtype'] . '" />';
							$bidamounttype = $ilance->auction->construct_bidamounttype($res_rfp['filtered_bidtype']);
							$method = $ilance->auction->construct_measure($res_rfp['filtered_bidtype']);
						}
						else
						{
							$bamounttype = $method = '';
							if (!empty($res_bid['bidamounttype']))
							{
								$bamounttype = $res_bid['bidamounttype'];
								$method = $ilance->auction->construct_measure($res_bid['bidamounttype']);
							}
							$bidamounttype_pulldown = $ilance->auction->construct_bidamounttype_pulldown($bamounttype, 0, '2', $res_rfp['cid'], 'service');
							unset($bidamounttype);
						}
						// specific javascript includes
						$headinclude .= '
<script type="text/javascript">
<!--
function show_custom(obj)
{
        if (obj.value == \'weight\')
        {
                fetch_js_object("custom").style.display = \'\';
        }
        else
        {
                fetch_js_object("custom").style.display = \'none\';
        }
}
function validate_all()
{
        return validate_bid_amount() && validate_estimate() && validate_title() && validate_paymethod();
}
function validate_paymethod()
{ 
	var i,j=0;
	var buttons12 =  document["ilform"].elements["paymethod"];
	if (isNaN(buttons12.length))
	{
		if (buttons12.checked)
		{
			j=j+1;
		}
	}
	else
	{
		for (var i=0; i <buttons12.length; i++)
		{
			if (buttons12[i].checked)
			{
				j=j+1;
			}
              }
	}
	if (j==0)
	{
		alert_js(phrase[\'_it_appears_you_did_not_select_a_valid_payment_method_please_retry_your_action\']);
		return (false);
	}
	return(true);
}
function validate_title()
{';
	if ($ilconfig['default_proposal_wysiwyg'] == 'bbeditor')
	{
		$headinclude .= '
	fetch_bbeditor_data();
';
	}
	$headinclude .= '     
        if (fetch_js_object(\'proposal_id\').value == \'\')
        {
                alert_js(phrase[\'_please_include_a_bid_proposal_with_your_bid\']);
                return(false);        
        }
        return(true);
}
function validate_estimate()
{
        var Chars = "0123456789.";
        if (fetch_js_object(\'estimate_days\').value == \'\' || fetch_js_object(\'estimate_days\').value < 1 || fetch_js_object(\'estimate_days\').value == \'0\')
        {
                alert_js(phrase[\'_enter_the_estimated_measure_of_time_or_delivery_this_project_will_take_you\']);
                return(false);
        }
        for (var i = 0; i < fetch_js_object(\'estimate_days\').value.length; i++)
        {
                if (Chars.indexOf(fetch_js_object(\'estimate_days\').value.charAt(i)) == -1)
                {
                        alert_js(phrase[\'_delivery_input_accepts_numberonly_values_only_please_try_again\']);
                        return(false);
                }
        }
        return(true);
}
function validate_bid_amount()
{
        var Chars = "0123456789.,";
        for (var i = 0; i < fetch_js_object(\'bidamount\').value.length; i++)
        {
                if (Chars.indexOf(fetch_js_object(\'bidamount\').value.charAt(i)) == -1)
                {
                        alert_js(phrase[\'_invalid_currency_characters_only_numbers_and_a_period_are_allowed_in_this_field\']);
                        return(false);
                }
        }                                                                                
        if (fetch_js_object(\'bidamount\').value == \'0.00\' || fetch_js_object(\'bidamount\').value == \'0\' || fetch_js_object(\'bidamount\').value.length < 1)
        {
                alert_js(phrase[\'_you_have_entered_an_incorrect_bid_amount_please_try_again\']);
                return(false);
        }                                                                                
        ';
						if ($res_rfp['filter_bidtype'] == 0)
						{
							$headinclude .= '
        if (fetch_js_object(\'bidamounttype\').value == 0)
        {
                alert_js(phrase[\'_please_select_a_bid_amount_type_before_submitting_your_bid\']);
                return(false);
        }
        ';
						}
						$headinclude .= '
        return(true);
}
//-->
</script>
';
						// service provider commission fees display
						$budget = $ilance->auction_service->fetch_rfp_budget($res_rfp['project_id']);
						$filtered_bidtypecustom = $res_rfp['filtered_bidtypecustom'];
						$cid = $res_rfp['cid'];
						// display bidtype filter prefered by buyer
						$bidtypefilter = ($res_rfp['filter_bidtype'])? $ilance->auction->construct_bidamounttype($res_rfp['filtered_bidtype']): '{_buyer_accepts_various_bid_amount_types}';
						// #### payment methods the purchaser is offering
						$paymentmethods = $ilance->payment->print_payment_methods($res_rfp['project_id']);
						$paymethodsradios = $ilance->payment->print_payment_methods($res_rfp['project_id'], true);
						$fieldmode = ($show['bidexists']) ? 'update' : 'input';
						$custom_bid_fields = $ilance->bid_fields->construct_bid_fields($cid, $res_rfp['project_id'], $fieldmode, 'service', 0, true);
						$pprint_array = array('lasthour','lowerbid','previewattachmentlist','paymethodsradios','custom_bid_fields','wysiwyg_area','bidamounttype','paymentmethods','bidtypefilter','cid','method','filtered_bidtypecustom','finalvaluefees','bidamounttype_pulldown','cid','uploadbutton','current_bidlock_amount','spellcheck_style','attachment_style','pmb_id','id','portfolio_id','bid_id','filehash','category_id','user_id','attachtype','max_filesize','category','currency_proposal','current_proposal','current_bidamount','current_estimate_days','delivery_pulldown','currency','title','description','budget','bid_controls','buyer_incomespent','buyer_stars','project_title','description','project_type','project_details','project_distance','project_id','bid_details','pmb','projects_posted','projects_awarded','project_currency','distance','subcategory_name','text','prevnext','prevnext2');

						($apihook = $ilance->api('rfp_place_service_bid_end')) ? eval($apihook) : false;
						
						$ilance->template->fetch('main', 'rfp_placebid.html');
						$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
						$ilance->template->parse_if_blocks('main');
						$ilance->template->pprint('main', $pprint_array);
						exit();
					}
				}
				else
				{
					// this service provider has not been invited to this auction!
					$area_title = '{_you_have_not_been_invited_to_place_a_bid}';
					$page_title = SITE_NAME . ' - {_you_have_not_been_invited_to_place_a_bid}';
					print_notice('{_bid_filter_restriction}' . "&nbsp;" . '{_you_have_not_been_invited_to_place_a_bid}', '{_were_sorry_this_project_owner_has_set_bid_filtering_permissions_on_their_project}'."<br /><br />".'{_bid_filtering_allows_the_buyer_to_filter_various_aspects_of_their_project}'."<br /><br />".'{_to_learn_more_about_bid_filter_permissions}'." <a href=\"javascript:void(0)\" onClick=Attach(\"".$ilpage['rfp']."?msg=bid-permissions\") >".'{_click_here}'."</a>", $ilpage['main'], '{_main_menu}');
					exit();
				}
			}
			else
			{
				// not by invitation only .. regular bid checkup
				$id = intval($ilance->GPC['id']);
				$ilance->bid->bid_filter_checkup($id);
				$area_title = '{_placing_a_bid}';
				$page_title = SITE_NAME . ' - {_placing_a_bid}';
				if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
				{
					// fetch existing bid proposal for updating if it has not been retracted
					$show['bidexists'] = false;
					$res_bid = array();
					$current_proposal = isset($_SESSION['ilancedata']['user']['current_proposal']) ? $_SESSION['ilancedata']['user']['current_proposal'] : '';
					$current_bidamount = isset($_SESSION['ilancedata']['user']['current_bidamount']) ? $_SESSION['ilancedata']['user']['current_bidamount'] : '';
					$current_estimate_days = isset($_SESSION['ilancedata']['user']['current_estimate_days']) ? $_SESSION['ilancedata']['user']['current_estimate_days'] : '';
					$sql_bid = $ilance->db->query("
                                                SELECT proposal, bidamount, estimate_days, winnermarkedaspaidmethod, bidamounttype
                                                FROM " . DB_PREFIX . "project_bids
                                                WHERE project_id = '" . intval($id) . "'
                                                        AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                                        AND bidstate != 'retracted'
                                                        AND bidstatus != 'declined'
                                                ORDER BY bid_id DESC
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql_bid) > 0)
					{
						$res_bid = $ilance->db->fetch_array($sql_bid, DB_ASSOC);
						$show['bidexists'] = true;
						$current_proposal = stripslashes($res_bid['proposal']);
						$current_bidamount = sprintf("%01.2f", $res_bid['bidamount']);
						$current_estimate_days = intval($res_bid['estimate_days']);
						$ilance->GPC['paymethod'] = $res_bid['winnermarkedaspaidmethod'];
					}
					// #### bid proposal editor ############
					$wysiwyg_area = print_wysiwyg_editor('proposal', $current_proposal, 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg'], false, '590', '120', '', $ilconfig['default_proposal_wysiwyg'], $ilconfig['ckeditor_proposaltoolbar']);
					$sql_rfp = $ilance->db->query("
                                                SELECT project_title, cid, filter_bidtype, filtered_bidtypecustom, filtered_bidtype, date_starts, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
                                                FROM " . DB_PREFIX . "projects
                                                WHERE project_id = '" . intval($id) . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql_rfp) > 0)
					{
						$res_rfp = $ilance->db->fetch_array($sql_rfp, DB_ASSOC);
						// #### project scheduled for future
						if ($res_rfp['date_starts'] > DATETIME24H)
						{
							$dif = $res_rfp['starttime'];
							$ndays = floor($dif / 86400);
							$dif -= $ndays * 86400;
							$nhours = floor($dif / 3600);
							$dif -= $nhours * 3600;
							$nminutes = floor($dif / 60);
							$dif -= $nminutes * 60;
							$nseconds = $dif;
							$sign = '+';
							if ($res_rfp['starttime'] < 0)
							{
								$res_rfp['starttime'] = - $res_rfp['starttime'];
								$sign = '-';
							}
							if ($sign != '-')
							{
								if ($ndays != '0')
								{
									$project_time_left = $ndays . '{_d_shortform}, ';
									$project_time_left .= $nhours . '{_h_shortform}+';
								}
								elseif ($nhours != '0')
								{
									$project_time_left = $nhours . '{_h_shortform}, ';
									$project_time_left .= $nminutes . '{_m_shortform}+';
								}
								else
								{
									$project_time_left = $nminutes . '{_m_shortform}, ';
									$project_time_left .= $nseconds . '{_s_shortform}+';
								}
							}
							$timeleft = $project_time_left;
							$started = $timeleft;
							$area_title = '{_placing_a_bid}';
							$page_title = SITE_NAME . ' - {_placing_a_bid}';
							print_notice('{_auction_event_is_scheduled}', '{_this_auction_event_is_scheduled_and_has_not_started_yet}', $ilpage['main'], '{_main_menu}');
							exit();
						}
						// #### project started ########
						else
						{
							$title = stripslashes($res_rfp['project_title']);
							$category = stripslashes($ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $res_rfp['cid']));
							$category = '<a href="' . $ilpage['rfp'] . '?cid=' . $res_rfp['cid'] . '">' . $category . '</a>';
							// #### check for attachment permissions
							$attachment_style = ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], $accessname='attachments') == "yes") ? '' : 'disabled="disabled"';
							// #### encrypted upload button
							$cid = $res_rfp['cid'];
							$hiddeninput = array(
								'attachtype' => 'bid',
								'project_id' => $id,
								'user_id' => $_SESSION['ilancedata']['user']['userid'],
								'category_id' => $res_rfp['cid'],
								'filehash' => md5(time()),
								'max_filesize' => $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'uploadlimit')
							);
							$uploadbutton = '<input name="attachment" style="font-size:13px" onclick=Attach("'.$ilpage['upload'].'?crypted='.encrypt_url($hiddeninput).'") type="button" value="{_upload}" class="buttons" '.$attachment_style.' />';
							$sql_attachments = $ilance->db->query("
								SELECT visible, filename, filehash, filesize
								FROM " . DB_PREFIX . "attachment 
								WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' 
									AND category_id = '" . $res_rfp['cid'] . "' 
									AND project_id = '" . $id . "'
									AND attachtype = 'bid'
							");
							$previewattachmentlist = '';
							while ($res = $ilance->db->fetch_array($sql_attachments, DB_ASSOC))
							{
								$moderated = '';
								if ($res['visible'] == 0)
								{
									$moderated = '[{_review_in_progress}]';
									$attachment_link = $res['filename'];
								}
								else
								{
									$attachment_link = '<a href="' . HTTP_SERVER . $ilpage['attachment'] . '?id=' . $res['filehash'] . '" target="_blank">' . $res['filename'] . '</a>';
								}
								$previewattachmentlist .= '<div><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'paperclip.gif" border="0" alt="' . $res['filename'] . '" /> ' . $attachment_link . ' (' . $res['filesize'] . ' {_bytes}) ' . $moderated . '</div>';
							}
							// show bid amount pulldown but disable based on buyers bid type payout preference
							if ($res_rfp['filter_bidtype'])
							{
								$bidamounttype_pulldown = '<input id="hidden_filtered_bidtype" type="hidden" name="filtered_bidtype" value="' . $res_rfp['filtered_bidtype'] . '" />';
								$bidamounttype = $ilance->auction->construct_bidamounttype($res_rfp['filtered_bidtype']);
								$method = $ilance->auction->construct_measure($res_rfp['filtered_bidtype']);
							}
							else
							{
								$bidamounttype = $bamounttype = $method = '';
								if (!empty($res_bid['bidamounttype']))
								{
									$bamounttype = $res_bid['bidamounttype'];
									$method = $ilance->auction->construct_measure($res_bid['bidamounttype']);
								}
								else
								{
									$method = $ilance->auction->construct_measure();
								}
								$bidamounttype_pulldown = $ilance->auction->construct_bidamounttype_pulldown($bamounttype, 0, '2', $res_rfp['cid'], 'service');
							}
							// #### purchaser payment methods being offered
							$paymentmethods = $ilance->payment->print_payment_methods($id);
							$paymethodsradios = $ilance->payment->print_payment_methods($id, true);
							$headinclude .= '
<script type="text/javascript">
<!--
function show_custom(obj)
{
        if (obj.value == \'weight\')
        {
                fetch_js_object("custom").style.display = \'\';
        }
        else
        {
                fetch_js_object("custom").style.display = \'none\';
        }
}
function validate_all()
{
        return validate_bid_amount() && validate_estimate() && validate_title() && validate_paymethod() && validate_bid_budget();
}
function validate_paymethod()
{ 
	var i,j=0;
	var buttons12 =  document["ilform"].elements["paymethod"];
	if (isNaN(buttons12.length))
	{
		if (buttons12.checked)
		{
			j=j+1;
		}
	}
	else
	{
		for (var i=0; i <buttons12.length; i++)
		{
			if (buttons12[i].checked)
			{
				j=j+1;
			}
		}
	}
	if (j==0)
	{
		alert_js(phrase[\'_it_appears_you_did_not_select_a_valid_payment_method_please_retry_your_action\']);
		return (false);
	}
	return(true);
}
function validate_title()
{';
	if ($ilconfig['default_proposal_wysiwyg'] == 'bbeditor')
	{
		$headinclude .= '
	fetch_bbeditor_data();
';
	}
	$headinclude .= '
        if (fetch_js_object(\'proposal_id\').value == \'\')
        {
                alert_js(phrase[\'_please_include_a_bid_proposal_with_your_bid\']);
                return(false);        
        }
        return(true);
}
function validate_estimate()
{
        var Chars = "0123456789.";
        if (fetch_js_object(\'estimate_days\').value == \'\' || fetch_js_object(\'estimate_days\').value < 1 || fetch_js_object(\'estimate_days\').value == \'0\')
        {
                alert_js(phrase[\'_enter_the_estimated_measure_of_time_or_delivery_this_project_will_take_you\']);
                return(false);
        }
        for (var i = 0; i < fetch_js_object(\'estimate_days\').value.length; i++)
        {
                if (Chars.indexOf(fetch_js_object(\'estimate_days\').value.charAt(i)) == -1)
                {
                        alert_js(phrase[\'_delivery_input_accepts_numberonly_values_only_please_try_again\']);
                        return(false);
                }
        }
        return(true);
}
function validate_bid_budget()
{
	var cur_bid = parseFloat(fetch_js_object(\'bidamount\').value);
	var estimate_days = parseFloat(fetch_js_object(\'estimate_days\').value);
	if (fetch_js_object(\'hidden_filtered_bidtype\').value != \'entire\')
	{
		cur_bid = cur_bid * estimate_days;
	}
	var to_budget = parseFloat(fetch_js_object(\'to_budget\').value);
	var from_budget = parseFloat(fetch_js_object(\'from_budget\').value);
	if (((cur_bid > to_budget) && to_budget != -1) || ((cur_bid < from_budget) && from_budget != -1))
	{
		alert_js(phrase[\'_sorry_the_buyer_of_this_auction_has_set_a_preferred_budget_range\']);
		return false;
	}
	return true;
}
function validate_bid_amount()
{
        var Chars = "0123456789.,";
        for (var i = 0; i < fetch_js_object(\'bidamount\').value.length; i++)
        {
                if (Chars.indexOf(fetch_js_object(\'bidamount\').value.charAt(i)) == -1)
                {
                        alert_js(phrase[\'_invalid_currency_characters_only_numbers_and_a_period_are_allowed_in_this_field\']);
                        return(false);
                }
        }                                                                                
        if (fetch_js_object(\'bidamount\').value == \'0.00\' || fetch_js_object(\'bidamount\').value == \'0\' || fetch_js_object(\'bidamount\').value.length < 1)
        {
                alert_js(phrase[\'_you_have_entered_an_incorrect_bid_amount_please_try_again\']);
                return(false);
        }                                                                                
        ';
							if ($res_rfp['filter_bidtype'] == 0)
							{
								$headinclude .= '
        if (fetch_js_object(\'bidamounttype\').value == 0)
        {
                alert_js(phrase[\'_please_select_a_bid_amount_type_before_submitting_your_bid\']);
                return(false);
        }
        ';
							}
							$headinclude .= '
        return(true);
}
//-->
</script>
';
							$budget = $ilance->auction_service->fetch_rfp_budget($id);
							$from_to_array = $ilance->auction->fetch_rfp_budget_low_high($id);
							$to_budget = (isset($from_to_array[1])) ? $from_to_array[1] : -1;
							$from_budget = (isset($from_to_array[0])) ? $from_to_array[0] : -1;
							// service provider commission fees display
							$filtered_bidtypecustom = $res_rfp['filtered_bidtypecustom'];
							$cid = $res_rfp['cid'];
							// display bidtype filter prefered by buyer
							$bidtypefilter = ($res_rfp['filter_bidtype']) ? $ilance->auction->construct_bidamounttype($res_rfp['filtered_bidtype']) : '{_buyer_accepts_various_bid_amount_types}';
							$fieldmode = ($show['bidexists']) ? 'update' : 'input';
							$custom_bid_fields = $ilance->bid_fields->construct_bid_fields($cid, $id, $fieldmode, 'service', 0, true);
							$pprint_array = array('from_budget','to_budget','previewattachmentlist','paymethodsradios','custom_bid_fields','lasthour', 'lowerbid', 'subscribed', 'wysiwyg_area','bidtypefilter','cid','method','filtered_bidtypecustom','paymentmethods','bidamounttype','finalvaluefees','bidamounttype_pulldown','cid','uploadbutton','current_bidlock_amount','spellcheck_style','attachment_style','pmb_id','id','portfolio_id','bid_id','filehash','category_id','user_id','attachtype','max_filesize','category','currency_proposal','current_proposal','current_bidamount','current_estimate_days','delivery_pulldown','currency','title','description','budget','bid_controls','buyer_incomespent','buyer_stars','project_title','description','project_type','project_details','project_distance','project_id','bid_details','pmb','projects_posted','projects_awarded','project_currency','distance','subcategory_name','text','prevnext','prevnext2');
							
							($apihook = $ilance->api('rfp_place_service_bid_end')) ? eval($apihook) : false;

							$ilance->template->fetch('main', 'rfp_placebid.html');
							$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
							$ilance->template->parse_if_blocks('main');
							$ilance->template->pprint('main', $pprint_array);
							exit();
						}
					}
				}
				else
				{
					refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
					exit();
				}
			}
		}
	}
	else
	{
		refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
		exit();
	}
}
// #### PLACE NEW BID FOR PRODUCT AUCTION ######################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'bid' AND $ilance->GPC['state'] == 'product')
{
	// #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
	$id = intval($ilance->GPC['id']);
	$area_title = '{_placing_a_bid}';
	$page_title = SITE_NAME . ' - {_placing_a_bid}';
	$navcrumb = array();
	$navcrumb["$ilpage[merch]?cmd=listings"] = '{_product_auctions}';
	$navcrumb["$ilpage[merch]?id=" . $id] = $id;
	$navcrumb[""] = $area_title;
	if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
	{
		$ilance->categories->build_array($cattype = 'product', $_SESSION['ilancedata']['user']['slng'], $mode = 0, $propersort = false);
		// #### check subscription #####################################
		if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'productbid') == 'no')
		{
			$area_title = '{_access_to_bid_is_denied}';
			$page_title = SITE_NAME . ' - {_access_to_bid_is_denied}';
			print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource} <span class="blue"><a href="' . HTTP_SERVER . $ilpage['subscription'] . '"><strong>{_click_here}</strong></a>', $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('productbid'));
			exit();
		}
		$project_state = fetch_auction('project_state', $id);
		if ($project_state != 'product')
		{
			$area_title = '{_access_to_bid_is_denied}';
			$page_title = SITE_NAME . ' - {_access_to_bid_is_denied}';
			print_notice('{_access_denied}', '{_access_denied}', $ilpage['main'], ucwords('{_click_here}'));
			exit();
		}
		// #### check bids per day limit ###############################
		$bidtotal = $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'bidlimitperday');
		$bidsleft = max(0, ($bidtotal - fetch_bidcount_today($_SESSION['ilancedata']['user']['userid'])));
		if ($bidsleft <= 0)
		{
			$area_title = '{_access_to_bid_is_denied}';
			$page_title = SITE_NAME . ' - {_access_to_bid_is_denied}';
			print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource} <span class="blue"><a href="' . HTTP_SERVER . $ilpage['subscription'] . '"><strong>{_click_here}</strong></a>', $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('bidlimitperday'));
			exit();
		}
		// #### check if the listing id was properly entered ###########
		if (empty($id) OR $id <= 0)
		{
			$area_title = '{_bad_rfp_warning}';
			$page_title = SITE_NAME . ' - {_bad_rfp_warning}';
			print_notice('{_invalid_rfp_specified}', '{_your_request_to_review_or_place_a_bid_on_a_valid_request_for_proposal}', $ilpage['search'], '{_search_rfps}');
			exit();
		}
		// #### determine if bidder can pass any filter requirements by the seller
		$ilance->bid->product_bid_filter_checkup($id);
		// #### determine if this bidder was invited to place a bid
		if ($ilance->auction_product->is_bidder_invited($_SESSION['ilancedata']['user']['userid'], $id) == false)
		{
			$area_title = '{_you_have_not_been_invited_to_place_a_bid}';
			$page_title = SITE_NAME . ' - {_you_have_not_been_invited_to_place_a_bid}';
			print_notice('{_you_have_not_been_invited_to_place_a_bid}', '{_were_sorry_this_project_owner_has_set_bid_filtering_permissions_on_their_project}' . "<br /><br />" . '{_bid_filtering_allows_the_buyer_to_filter_various_aspects_of_their_project}' . "<br /><br />" . '{_to_learn_more_about_bid_filter_permissions}' . " <a href=\"javascript:void(0)\" onClick=Attach(\"" . $ilpage['rfp'] . "?msg=bid-permissions\") >" . '{_click_here}' . "</a>", $ilpage['main'], '{_main_menu}');
			exit();
		}
		// #### show watchlist options selected ########################
		$sql = $ilance->db->query("
                        SELECT hourleftnotify, highbidnotify, subscribed
                        FROM " . DB_PREFIX . "watchlist
                        WHERE watching_project_id = '" . $id . "' 
                                AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) == 0)
		{
			$lasthour = '<input type="checkbox" name="lasthournotify" id="lasthournotify" value="1" />';
			$higherbid = '<input type="checkbox" name="highbidnotify" id="highbidnotify" value="1" />';
			$subscribed = '<input type="checkbox" name="subscribed" id="subscribed" value="1" />';
		}
		else
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$lasthour = (($res['hourleftnotify']) ? '<input checked type="checkbox" name="lasthournotify" id="lasthournotify" value="1" /> ' : '<input type="checkbox" name="lasthournotify" id="lasthournotify" value="1" /> ');
				$higherbid = (($res['highbidnotify']) ? '<input checked type="checkbox" name="highbidnotify" id="highbidnotify" value="1" />' : '<input type="checkbox" name="highbidnotify" id="highbidnotify" value="1" />');
				$subscribed = (($res['subscribed']) ? '<input checked type="checkbox" name="subscribed" id="subscribed"  value="1" />' : '<input type="checkbox" name="subscribed" id="subscribed" value="1" />');
			}
		}
		// #### rebid details (if applicable) ##########################
		$current_bidamount = 0;
		$sql_bid = $ilance->db->query("
                        SELECT bidamount
                        FROM " . DB_PREFIX . "project_bids
                        WHERE project_id = '" . $id . "'
                                AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql_bid) > 0)
		{
			$res_bid = $ilance->db->fetch_array($sql_bid);
			$current_bidamount = $res_bid['bidamount'];
		}
		// auction details
		$sql_rfp = $ilance->db->query("
                        SELECT p.filtered_auctiontype, p.buynow_qty, p.user_id, p.date_starts, p.project_title, p.startprice, p.currencyid, p.buynow_price, p.currentprice, p.bids, p.cid, p.reserve_price, p.reserve, s.ship_method, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, s.ship_method
                        FROM " . DB_PREFIX . "projects p
			LEFT JOIN " . DB_PREFIX . "projects_shipping s ON p.project_id = s.project_id
                        WHERE p.project_id = '" . $id . "'
                                AND p.project_state = 'product'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql_rfp) > 0)
		{
			$res_rfp = $ilance->db->fetch_array($sql_rfp, DB_ASSOC);
			$auctiontype = $res_rfp['filtered_auctiontype'];
			if ($auctiontype == 'fixed')
			{
				$buynow_qty = $res_rfp['buynow_qty'];
			}
			// quantity available
			$qty = $res_rfp['buynow_qty'];
			// is owner trying to bid?
			if ($res_rfp['user_id'] == $_SESSION['ilancedata']['user']['userid'])
			{
				$area_title = '{_bid_denied_cannot_bid_on_own_auction}';
				$page_title = SITE_NAME . ' - {_bid_denied_cannot_bid_on_own_auction}';
				print_notice($area_title, '{_sorry_merchants_cannot_place_bids_on_their_own_product_auctions}<br />', 'javascript:history.back(1);', '{_back}');
				exit();
			}
			if ($res_rfp['date_starts'] > DATETIME24H)
			{
				print_notice('{_auction_event_is_scheduled}', '{_this_auction_event_is_scheduled_and_has_not_started_yet}', $ilpage['main'], '{_main_menu}');
				exit();
			}
			$currency = print_left_currency_symbol();
			// highest bid amount placed for this auction
			$highestbid = 0;
			$highbid = $ilance->db->query("
                                SELECT MAX(bidamount) AS maxbidamount
                                FROM " . DB_PREFIX . "project_bids
                                WHERE project_id = '" . $id . "'
                        ", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($highbid) > 0)
			{
				$res = $ilance->db->fetch_array($highbid, DB_ASSOC);
				$highestbid = sprintf("%.02f", $res['maxbidamount']);
			}
			$title = stripslashes(handle_input_keywords($res_rfp['project_title']));
			// show starting bid price
			$startprice = $ilance->currency->format($res_rfp['startprice'], $res_rfp['currencyid']);
			$buynowprice = '';
			if ($res_rfp['buynow_price'] > 0)
			{
				$buynowprice = $ilance->currency->format($res_rfp['buynow_price'], $res_rfp['currencyid']);
			}
			$show['hasnobids'] = false;
			$show['currentbid'] = true;
			$currentprice = $ilance->currency->format($res_rfp['currentprice'], $res_rfp['currencyid']);
			$min_bidamount = sprintf("%.02f", '0.01');
			$min_bidamountformatted = $ilance->currency->format('0.01', $res_rfp['currencyid']);
			if ($res_rfp['bids'] <= 0)
			{
				$show['hasnobids'] = true;
				$show['currentbid'] = false;
				// do we have starting price?
				if ($res_rfp['startprice'] > 0)
				{
					$min_bidamount = sprintf("%.02f", $res_rfp['startprice']);
					$min_bidamountformatted = $ilance->currency->format($res_rfp['startprice'], $res_rfp['currencyid']);

					// just in case our highest bid is 0 we will check our starting bid
					// and adjust the $highestbid variable to the start price to at least
					// generate the next increment if we've defined any in this category
					if ($highestbid == 0)
					{
						$highestbid = $min_bidamount;
					}
				}
				$currentprice = $ilance->currency->format($min_bidamount, $res_rfp['currencyid']);
			}
			// is admin using custom bid increments?
			$proxybit = '';
			$incrementgroup = $ilance->categories->incrementgroup($res_rfp['cid']);
			$sqlincrements = $ilance->db->query("
                                SELECT amount
                                FROM " . DB_PREFIX . "increments
                                WHERE ((increment_from <= $highestbid
                                        AND increment_to >= $highestbid)
                                                OR (increment_from < $highestbid
                                        AND increment_to < $highestbid))
                                        AND groupname = '" . $ilance->db->escape_string($incrementgroup) . "'
                                ORDER BY amount DESC
                        ", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sqlincrements) > 0)
			{
				$show['increments'] = 1;
				$resincrement = $ilance->db->fetch_array($sqlincrements);
				$increment = $ilance->currency->format($resincrement['amount'], $res_rfp['currencyid']) . ' - <a href="javascript:void(0)" onclick="Attach(\'' . HTTP_SERVER . $ilpage['rfp'] . '?msg=bid-increments&amp;c=' . $res_rfp['cid'] . '\')">{_more}</a>';
				if ($res_rfp['bids'] > 0)
				{
					// if we have more than 1 bid start the bid increments since the first bidder cannot bid against the opening bid
					$min_bidamount = sprintf("%.02f", $highestbid + $resincrement['amount']);
					$min_bidamountformatted = $ilance->currency->format(($highestbid + $resincrement['amount']), $res_rfp['currencyid']);
				}
				$pbit = $ilance->bid_proxy->fetch_user_proxy_bid($id, $_SESSION['ilancedata']['user']['userid']);
				if ($pbit > 0)
				{
					$proxybit = $ilance->currency->format($pbit, $res_rfp['currencyid']) . ' : {_invisible}';
				}
			}
			else
			{
				$show['increments'] = 0;
				// admin should define some increments if we get to this point
				$increment = $ilance->currency->format(0, $res_rfp['currencyid']) . ' - <a href="javascript:void(0)" onclick="Attach(\'' . $ilpage['rfp'] . '?msg=bid-increments&amp;c=' . $res_rfp['cid'] . '\')">{_more}</a>';
				// minimum bid amount
				$min_bidamount = sprintf("%.02f", $highestbid);
				$min_bidamountformatted = $ilance->currency->format($highestbid, $res_rfp['currencyid']);
				$pbit = $ilance->bid_proxy->fetch_user_proxy_bid($id, $_SESSION['ilancedata']['user']['userid']);
				if ($pbit > 0)
				{
					$proxybit = $ilance->currency->format($pbit, $res_rfp['currencyid']) . ' - {_invisible}';
				}
			}
			$proxytext = '';
			$show['categoryuseproxybid'] = false;
			if ($ilconfig['productbid_enableproxybid'] AND $ilance->categories->useproxybid($_SESSION['ilancedata']['user']['slng'], $res_rfp['cid']))
			{
				$show['categoryuseproxybid'] = true;
				$proxytext = '<a href="javascript:void(0)" onmouseover="Tip(phrase[\'_when_you_place_a_bid_for_an_item_enter_the_maximum_amount\'], BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a> {_proxy}';
				if (isset($pbit) AND $pbit > $min_bidamount)
				{
					$min_bidamount = sprintf("%.02f", $pbit) + 0.01;
					$min_bidamountformatted = $ilance->currency->format($min_bidamount, $res_rfp['currencyid']);
				}
			}
			$state = 'product';
			// #### specific javascript includes ###################
			$headinclude .= '
<script type="text/javascript">
<!--
function validate_place_bid(f)
{
        var Chars = "0123456789.,";
        haveerrors = 0;
        (f.bidamount.value.length < 1) ? showImage("bidamounterror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("bidamounterror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        for (var i = 0; i < f.bidamount.value.length; i++)
        {
                if (Chars.indexOf(f.bidamount.value.charAt(i)) == -1)
                {
                        alert_js(phrase[\'_invalid_currency_characters_only_numbers_and_a_period_are_allowed_in_this_field\']);
                        haveerrors = 1;
                }
        }
	if (haveerrors != 1)
        {
                val = fetch_js_object(\'bidamount_field\').value;
                var bidamount = string_to_number(val);
		bidamount = parseFloat(bidamount);
                val2 = fetch_js_object(\'hiddenfieldminimum\').value;
                var minimumbid = string_to_number(val2);
		minimumbid = parseFloat(minimumbid);
                if (bidamount == \'NaN\' || bidamount == \'\' || bidamount <= \'0\')
                {
                        alert_js(phrase[\'_cannot_place_value_for_your_bid_amount_your_bid_amount_must_be_greater_than_the_minimum_bid_amount\']);
                        haveerrors = 1;
                }
                else
                {
                        if (bidamount < minimumbid)
                        {
                                alert_js(phrase[\'_cannot_place_value_for_your_bid_amount_your_bid_amount_must_be_greater_than_the_minimum_bid_amount\']);
                                haveerrors = 1;
                        }
                }
		if (f.qty.value <= 0 || f.qty.value == \'\')
		{
			alert_js(\'Please enter the amount of quantity to purchase.\');
			haveerrors = 1;
		}
                fetch_js_object(\'bidamount_field\').value = bidamount;
        }
        return (!haveerrors);
}
//-->
</script>';
			// #### do we have a reserve price #####################
			$reserve_auction = 0;
			$reserve_met = '';
			if ($res_rfp['reserve'])
			{
				$reserve_auction = 1;
				$highest_amount = '--';
				$sql_highest = $ilance->db->query("
                                        SELECT MAX(bidamount) AS highest
                                        FROM " . DB_PREFIX . "project_bids
                                        WHERE project_id = '" . $id . "'
                                        ORDER BY highest
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql_highest) > 0)
				{
					$res_highest = $ilance->db->fetch_array($sql_highest, DB_ASSOC);
					$highest_amount = $res_highest['highest'];
				}
				// is reserve met?
				if ($highest_amount != '--' AND $highest_amount >= $res_rfp['reserve_price'])
				{
					$reserve_met = '{_yes_reserve_price_met}';
				}
				else
				{
					$reserve_met = '<span style="color:red">{_no_reserve_price_not_met}'. '</span>';
					if ($show['hasnobids'] AND $show['currentbid'])
					{
						$reserve_met .= '<div><strong>{_this_bid_will_be_the_actual_bid_placed_up_to_the_reserve_price}</strong></div>';
					}
				}
			}
			// #### shipping information selector ##################
			$show['localpickuponly'] = false;
			if ($res_rfp['ship_method'] == 'localpickup')
			{
				$show['localpickuponly'] = true;
			}
			$shipservicepulldown = $ilance->shipping->print_shipping_methods($id, 1, false, false, true, $_SESSION['ilancedata']['user']['countryid'], $_SESSION['ilancedata']['user']['slng']);
			$shippercount = $ilance->shipping->print_shipping_methods($id, 1, false, true, false, $_SESSION['ilancedata']['user']['countryid'], $_SESSION['ilancedata']['user']['slng']);
			if ($shippercount == 1)
			{
				$ilance->shipping->print_shipping_methods($id, 1, false, false, false, $_SESSION['ilancedata']['user']['countryid'], $_SESSION['ilancedata']['user']['slng']);
				$shipperid = $ilance->db->fetch_field(DB_PREFIX . "projects_shipping_destinations", "project_id = '" . intval($id) . "'", "ship_service_$shipperidrow");
			}
			$pprint_array = array('shipperid','shipservicepulldown','lasthour','higherbid','subscribed','qty','proxybit','buynowprice','currentprice','buynow_qty','reserve_met','min_bidamountformatted','startprice','proxytext','state','increment','highestbid','min_bidamount','current_bidlock_amount','spellcheck_style','attachment_style','pmb_id','id','portfolio_id','bid_id','filehash','category_id','user_id','attachtype','max_filesize','category','currency_proposal','current_proposal','current_bidamount','current_estimate_days','delivery_pulldown','currency','title','description','budget','bid_controls','buyer_incomespent','buyer_stars','project_title','description','project_type','project_details','project_distance','project_id','bid_details','pmb','projects_posted','projects_awarded','project_currency','distance','subcategory_name','text','prevnext','prevnext2');

			($apihook = $ilance->api('rfp_bid_revise_service_bid_end')) ? eval($apihook) : false;
			
			$ilance->template->fetch('main', 'listing_forward_auction_placebid.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}
	}
}
else if (isset($ilance->GPC['msg']) AND $ilance->GPC['msg'] == 'bid-permissions')
{
	$area_title = '{_viewing_bid_permissions_help}';
	$page_title = SITE_NAME . ' - {_viewing_bid_permissions_help}';
	// #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
	$ilance->template->load_popup('popupheader', 'popup_header.html');
	$ilance->template->load_popup('popupmain', 'popup_bid_permissions.html');
	$ilance->template->load_popup('popupfooter', 'popup_footer.html');
	$ilance->template->parse_hash('popupmain', array('ilpage' => $ilpage));
	$ilance->template->parse_hash('popupheader', array('ilpage' => $ilpage));
	$ilance->template->parse_hash('popupfooter', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('popupheader');
	$ilance->template->parse_if_blocks('popupmain');
	$ilance->template->parse_if_blocks('popupfooter');
	$ilance->template->pprint('popupheader', array('headinclude','onload','onbeforeunload','meta_desc','meta_keyw','official_time') );
	$ilance->template->pprint('popupmain',   array('remote_addr','rid','default_exchange_rate'));
	$ilance->template->pprint('popupfooter', array('finaltime','finalqueries'));
	exit();
}

else if (isset($ilance->GPC['msg']) AND $ilance->GPC['msg'] == 'bid-increments')
{
	$page_title = SITE_NAME.' - {_bid_increments}';
	$area_title = '{_bid_increments}';

	// #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);

	$cid = isset($ilance->GPC['c']) ? intval($ilance->GPC['c']) : 0;

	if ($cid == 0)
	{
		echo '{_you_must_select_a_category_please_close_this_window}';
		exit;
	}

	$ilance->categories->build_array($cattype = 'product', $_SESSION['ilancedata']['user']['slng'], $mode = 0, $propersort = false);

	// custom product bid increment logic
	$show['no_increments'] = true;
	$incrementgroup = $ilance->categories->incrementgroup($cid);
	$categorytitle = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $cid);

	$sqlincrements = $ilance->db->query("
                SELECT increment_from, increment_to, amount
                FROM " . DB_PREFIX . "increments
                WHERE groupname = '" . $ilance->db->escape_string($incrementgroup) . "'
                ORDER BY sort ASC
        ", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sqlincrements) > 0)
	{
		$row_count2 = 0;
		$show['no_increments'] = false;
		while ($rows = $ilance->db->fetch_array($sqlincrements, DB_ASSOC))
		{
			$rows['from'] = $ilance->currency->format($rows['increment_from']);
			if ($rows['increment_to'] == -1)
			{
				$rows['to'] = '<strong>{_or_more}</strong>';
			}
			else
			{
				$rows['to'] = $ilance->currency->format($rows['increment_to']);
			}
			$rows['amount'] = $ilance->currency->format($rows['amount']);
			$rows['class'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
			$increments[] = $rows;
			$row_count2++;
		}
	}
	else
	{
		$show['no_increments'] = true;
	}

	$ilance->template->load_popup('popupheader', 'popup_header.html');
	$ilance->template->load_popup('popupmain', 'popup_increments.html');
	$ilance->template->load_popup('popupfooter', 'popup_footer.html');
	$ilance->template->parse_hash('popupmain', array('ilpage' => $ilpage));
	$ilance->template->parse_hash('popupheader', array('ilpage' => $ilpage));
	$ilance->template->parse_hash('popupfooter', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('popupmain', 'increments');
	$ilance->template->parse_if_blocks('popupheader');
	$ilance->template->parse_if_blocks('popupmain');
	$ilance->template->parse_if_blocks('popupfooter');
	$ilance->template->pprint('popupheader', array('headinclude','onload','onbeforeunload','meta_desc','meta_keyw','official_time') );
	$ilance->template->pprint('popupmain', array('categorytitle'));
	$ilance->template->pprint('popupfooter', array('finaltime','finalqueries'));
	exit();
}
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'revisionlog' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	$page_title = SITE_NAME . ' - {_listing_revision_details}';
	$area_title = '{_listing_revision_details}';
	// #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
	$id = isset($ilance->GPC['id']) ? intval($ilance->GPC['id']) : 0;
	$navcrumb = array();
	$navcrumb["$ilpage[rfp]?id=" . $id] = $id;
	$navcrumb[""] = '{_listing_revision_details}';
	$returnurl = $ilpage['rfp'] . '?id=' . $id;
	$sql = $ilance->db->query("
                SELECT datetime, changelog
                FROM " . DB_PREFIX . "projects_changelog
                WHERE project_id = '" . $id . "'
                ORDER BY id DESC
        ", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
		$show['revision'] = true;
		$row_count = 0;
		while ($rows = $ilance->db->fetch_array($sql))
		{
			$rows['datetime'] = print_date($rows['datetime'], $ilconfig['globalserverlocale_globaltimeformat'], 1, 1);
			$rows['info'] = stripslashes($rows['changelog']);
			$rows['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$revisions[] = $rows;
			$row_count++;
		}
	}
	else
	{
		$show['revision'] = false;
	}
	$ilance->template->fetch('main', 'listing_revision_log.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', 'revisions');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', array('returnurl'));
	exit();
}
else
{
	include_once(DIR_SERVER_ROOT . 'rfp_viewjob.php');
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>