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
		'modal',
		'tabfx',
		'wysiwyg',
		'ckeditor'
	),
	'footer' => array(
		'v4',
		'tooltip',
		'autocomplete',
		'cron'
	)
);
// #### setup script location ##################################################
define('LOCATION', 'buying');
// #### require backend ########################################################
require_once('./functions/config.php');
require_once(DIR_CORE . 'functions_wysiwyg.php');
// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[buying]" => $ilcrumbs["$ilpage[buying]"]);
$uncrypted = (!empty($ilance->GPC['crypted'])) ? decrypt_url($ilance->GPC['crypted']) : array();
$ilance->GPC['cmd'] = isset($ilance->GPC['cmd']) ? $ilance->GPC['cmd'] : '';
$ilance->GPC['subcmd'] = isset($ilance->GPC['subcmd']) ? $ilance->GPC['subcmd'] : '';
if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
{
	refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['buying'] . print_hidden_fields(true, array(), true)));
	exit();
}
// #### INVITATION CONTROLS ####################################################
if ($ilance->GPC['cmd'] == 'management' AND $ilance->GPC['subcmd'] == 'invitations')
{
	// #### INVITATION REMINDER ############################################
	if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'remind' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$sql = $ilance->db->query("
			SELECT id, project_id, buyer_user_id, seller_user_id, email, name, invite_message, date_of_invite, date_of_bid, date_of_remind, bid_placed
			FROM " . DB_PREFIX . "project_invitations
			WHERE id = '" . intval($ilance->GPC['id']) . "'
				AND buyer_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$process = false;
			if ($res['date_of_remind'] != '0000-00-00 00:00:00')
			{
				$dor = explode(' ', $res['date_of_remind']);
				if ($dor[0] == DATETODAY)
				{
					$process = false;
				}
				else
				{
					$process = true;
				}
			}
			else
			{
				$process = true;						
			}
			if ($process)
			{
				$touser = fetch_user('username', $res['seller_user_id']);
				$toemail = fetch_user('email', $res['seller_user_id']);
				$title = fetch_auction('project_title', $res['project_id']);
				
				$ilance->email->mail = $toemail;
				$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
				$ilance->email->get('auction_invite_reminder');		
				$ilance->email->set(array(
					'{{receiver}}' => $touser,
					'{{username}}' => $_SESSION['ilancedata']['user']['username'],
					'{{project_id}}' => $res['project_id'],
					'{{project_title}}' => $title
				));
				$ilance->email->send();
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "project_invitations
					SET date_of_remind = '" . DATETIME24H . "'
					WHERE id = '" . intval($ilance->GPC['id']) . "'
				");
				refresh(HTTP_SERVER . $ilpage['buying'] . '?cmd=management');
				exit();
			}
			else
			{
				$area_title = '{_error_invitation_reminder}';
				$page_title = SITE_NAME . ' - {_error_invitation_reminder}';
				print_notice('{_one_invitation_per_each_user_per_day}', '{_it_appears_you_have_already_reminded_this_user_about_your_project_today}', $ilpage['buying'] . '?cmd=management', '{_buying_activity}');
				exit();
			}
		}
	}
	// #### REMOVE INVITATION ##############################################
	else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'remove-invite' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$sql = $ilance->db->query("
			SELECT id, project_id, buyer_user_id, seller_user_id, email, name, invite_message, date_of_invite, date_of_bid, date_of_remind, bid_placed
			FROM " . DB_PREFIX . "project_invitations
			WHERE id = '" . intval($ilance->GPC['id']) . "'
				AND buyer_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$sql2 = $ilance->db->query("
				DELETE FROM " . DB_PREFIX . "project_invitations
				WHERE id = '" . intval($ilance->GPC['id']) . "'
					AND buyer_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			", 0, null, __FILE__, __LINE__);
			
			$touser = fetch_user('username', $res['seller_user_id']);
			$toemail = fetch_user('email', $res['seller_user_id']);
			$title = fetch_auction('project_title', $res['project_id']);
			$ilance->email->mail = $toemail;
			$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
			$ilance->email->get('auction_uninvite_reminder');		
			$ilance->email->set(array(
				'{{receiver}}' => $touser,
				'{{username}}' => $_SESSION['ilancedata']['user']['username'],
				'{{project_id}}' => $res['project_id'],
				'{{project_title}}' => $title
			));
			$ilance->email->send();
			refresh(HTTP_SERVER . $ilpage['buying']);
			exit();
		}
	}
	// #### INVALID INVITATION ACTION ######################################
	else
	{
		print_notice('{_invalid_auction_command}', '{_sorry_the_action_you_are_trying_to_take_is_invalid_please_click_back_on_your_browser}', $ilpage['main'], '{_main_menu}');
		exit();
	}
}
// #### NEW AUCTION CREATION ###################################################
else if ($ilance->GPC['cmd'] == 'rfp')
{
	// #### are we are inviting single or multiple members? ################
	if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'invite')
	{
		if ($ilconfig['globalauctionsettings_serviceauctionsenabled'] == 0)
		{
			print_notice('{_disabled}', '{_were_sorry_this_feature_is_currently_disabled}', $ilpage['main'], '{_main_menu}');
			exit();
		}
		if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'createserviceauctions') == 'no')
		{
			if ($_SESSION['ilancedata']['user']['isadmin'] == '0')
			{
				$area_title = '{_viewing_access_denied_menu}';
				$page_title = SITE_NAME . ' - {_viewing_access_denied_menu}';
				$navcrumb = array();
				$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
				$navcrumb["$ilpage[buying]?cmd=management"] = '{_buying_activity}';
				$navcrumb[""] = '{_new_service_auction}';
				print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource}'." <a href='".$ilpage['subscription']."'><strong>".'{_click_here}'."</strong></a>", $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('createserviceauctions'));
				exit();
			}
		}
		// it appears we are inviting members to a new service auction
		// let's create temp sessions with the invitation data
		if (isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0)
		{	
			// count how many members are being invited        
			$count = count($ilance->GPC['invitationid']);
			if ($count > 0)
			{
				for ($i = 0; $i < $count; $i++)
				{
					$ilance->auction_rfp->insert_auction_invitation($_SESSION['ilancedata']['user']['userid'], $ilance->GPC['invitationid'][$i], $ilance->GPC['project_id'], 0, 'service');
				}
			}
			$area_title = '{_vendor_was_invited_to_rfp_menu}';
			$page_title = SITE_NAME . ' - {_vendor_was_invited_to_rfp_menu}';
			print_notice('{_vendor_invited_to_your_rfp}', '{_congratulations_you_have_successfully_invited_a_service_vendor_to_bid_on_your_auction}', $ilpage['buying'], '{_buying_activity}');
			exit();
		}
		else
		{
			// we are inviting members to a new auction we'll be creating now (or later)
			// let's build a new tmp session and hold the members to be invited
			$_SESSION['ilancedata']['tmp'] = array('invitations' => serialize($ilance->GPC['invitationid']));
			$url = ($ilconfig['globalauctionsettings_seourls']) ? HTTP_SERVER . 'buy' : HTTP_SERVER . $ilpage['main'] . '?cmd=buying';
			header('Location: ' . $url);
			exit();
		}                                
	}
	else
	{
		// #### define top header nav ##################################
		$topnavlink = array(
			'main_buying'
		);
		if ($ilconfig['globalauctionsettings_serviceauctionsenabled'] == 0)
		{
			print_notice('{_disabled}', '{_were_sorry_this_feature_is_currently_disabled}', $ilpage['main'], '{_main_menu}');
			exit();
		}
		if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'createserviceauctions') == 'no')
		{
			if ($_SESSION['ilancedata']['user']['isadmin'] == '0')
			{
				$area_title = '{_viewing_access_denied_menu}';
				$page_title = SITE_NAME . ' - {_viewing_access_denied_menu}';
				$navcrumb = array();
				$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
				$navcrumb["$ilpage[buying]?cmd=management"] = '{_buying_activity}';
				$navcrumb[""] = '{_new_service_auction}';
				print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource}'." <a href='".$ilpage['subscription']."'><strong>".'{_click_here}'."</strong></a>", $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('createserviceauctions'));
				exit();
			}
		}
		$area_title = '{_posting_new_service_rfp_category_selection_menu}';
		$page_title = SITE_NAME . ' - {_posting_new_service_rfp_category_selection_menu}';
		$cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
		$navcrumb = array();
		$navcrumb["$ilpage[buying]?cmd=rfp"] = '{_buy}';
		// #### handle pre-invited registered members ##################	
		$inviteduserlist = $invitespaces = '';
		$invitewidth = '0';
		if (!empty($_SESSION['ilancedata']['tmp']['invitations']) AND is_serialized($_SESSION['ilancedata']['tmp']['invitations']))
		{
			$invitedusers = unserialize($_SESSION['ilancedata']['tmp']['invitations']);
			$invitedcount = count($invitedusers);
			if ($invitedcount > 0 AND is_array($invitedusers))
			{
				foreach ($invitedusers AS $userid)
				{
					$inviteduserlist .= '<strong>' . fetch_user('username', $userid) . '</strong>, ';
				}
				if (!empty($inviteduserlist))
				{
					$inviteduserlist = '<div>' . mb_substr($inviteduserlist, 0, -2) . '</div>';
				}
			}
			$inviteduserlist = '<div class="block-wrapper">
	<div class="block2">
			<div class="block2-top">
					<div class="block2-right">
							<div class="block2-left"></div>
					</div>
			</div>
			<div class="block2-header">{_vendors_invited}</div>
			<div class="block2-content" style="padding:' . $ilconfig['table_cellpadding'] . 'px">' . $inviteduserlist . '</div>
			<div class="block2-footer">
					<div class="block2-right">
							<div class="block2-left"></div>
					</div>
			</div>
	</div>
</div>';
			$invitespaces = '&nbsp;';
			$invitewidth = '200';
		}
		$pprint_array = array('invitewidth','invitespaces','inviteduserlist','category','additionalcategories','cid','categories','hidden_invitations','invitation','rfp_category_js','rfp_category_left');
		$ilance->template->fetch('main', 'listing_reverse_auction_category.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
}
// #### CREATE AND UPDATE SERVICE AUCTION AUCTION ##############################
else if (($ilance->GPC['cmd'] == 'new-rfp' AND isset($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0 OR $ilance->GPC['cmd'] == 'rfp-management' AND (isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 OR isset($ilance->GPC['rfpid']) AND $ilance->GPC['rfpid'] > 0)))
{
	if ($ilance->GPC['cmd'] == 'new-rfp')
	{
		// set top nav link to "Buy" hover state
		// #### define top header nav ##################################
		$topnavlink = array(
			'main_buying'
		);
		// check permissions of buyer attemping to post a new service project
		if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'createserviceauctions') == 'no')
		{
			$area_title = '{_access_denied}';
			$page_title = SITE_NAME . ' - {_access_denied}';
			$navcrumb = array();
			$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
			$navcrumb["$ilpage[buying]?cmd=management"] = '{_buying_activity}';
			$navcrumb[""] = '{_new_service_auction}';
			print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource}' . ' <a href="' . $ilpage['subscription'] . '"><strong>{_click_here}</strong></a>', $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('createserviceauctions'));
			exit();
		}
	}
	else
	{
		// set top nav link to "My CP" hover state
		// #### define top header nav ##################################
		$topnavlink = array(
			'mycp'
		);
		// check permissions of buyer attemping to post a new service project
		if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'createserviceauctions') == 'no' AND $_SESSION['ilancedata']['user']['isadmin'] == '0')
		{
			$area_title = '{_access_denied}';
			$page_title = SITE_NAME . ' - {_access_denied}';
			$navcrumb = array();
			$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
			$navcrumb["$ilpage[buying]?cmd=management"] = '{_buying_activity}';
			$navcrumb[""] = '{_new_service_auction}';
			print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource}' . ' <a href="' . $ilpage['subscription'] . '"><strong>{_click_here}</strong></a>', $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('createserviceauctions'));
			exit();
		}
	}
	if ($ilconfig['globalauctionsettings_serviceauctionsenabled'] == 0)
	{
		print_notice('{_disabled}', '{_were_sorry_this_feature_is_currently_disabled}', $ilpage['main'], '{_main_menu}');
		exit();
	}
	$categorycache = $ilance->categories->build_array('service', $_SESSION['ilancedata']['user']['slng'], 0, true);
	$show['error_service_questions'] = false;
	// #### category question #############################################
	if (isset($ilance->GPC['custom']) AND is_array($ilance->GPC['custom']))
	{
		foreach ($ilance->GPC['custom'] AS $questionid => $answerarray)
		{
			foreach ($answerarray AS $formname => $answer)
			{
				$checkanswer = $ilance->db->query("
				SELECT formdefault, formname, questionid
				FROM " . DB_PREFIX . "project_questions
				WHERE formname='" . $ilance->db->escape_string($formname) . "'
					AND visible = '1'
					AND required = '1'
				");
				if ($ilance->db->num_rows($checkanswer) > 0)
				{
					$row = $ilance->db->fetch_array($checkanswer);
					if (is_array($answer))
					{
						foreach ($answer AS $key => $value)
						{
							if ($value == $row['formdefault'])
							{
								$show['error_service_questions'] = true;
							}
							else 
							{
								$_SESSION['ilancedata']['questions'][$formname] = $answer;		                                		
							}
						}
					}
					else
					{
						if ($answer == $row['formdefault'])
						{
							$show['error_service_questions'] = true;
						}
						else 
						{
							$_SESSION['ilancedata']['questions'][$formname] = $answer;		                                		
						}
					}
				}   
			}
		}
	}
	$navcrumb = array();
	if ($ilconfig['globalauctionsettings_seourls'])
	{
		$navcrumb[HTTP_SERVER . "buy"] = '{_buy}';
	}
	else
	{
		$navcrumb[HTTP_SERVER . "$ilpage[main]?cmd=buying"] = '{_buy}';
	}
	$show['bidsplaced'] = false;
	// #### set default listing state ######################################
	$ilance->GPC['project_state'] = 'service';
	// #### SUBMIT NEW SERVICE AUCTION #####################################
	if (isset($ilance->GPC['dosubmit']))
	{
		// #### final category checkup #################################
		if ($ilance->categories->can_post($_SESSION['ilancedata']['user']['slng'], 'service', $ilance->GPC['cid']) == false)
		{
			print_notice('{_this_is_a_nonposting_category}', '{_please_choose_another_category_to_list_your_auction_under_this_category_is_currently_reserved_for_postable_subcategories_and_does_not_allow_any_auction_postings}', 'javascript:history.back(1);', '{_back}');
			exit();
		}
		$_SESSION['ilancedata']['tmp']['new_project_id'] = '';
		unset($_SESSION['ilancedata']['tmp']['new_project_id']);
		$area_title = '{_saving_new_service_auction}';
		$page_title = SITE_NAME . ' - {_saving_new_service_auction}';
		// #### AUCTION FILTERS ########################################
		$ilance->GPC['filtered_auctiontype'] = 'regular';
		$ilance->GPC['filter_rating'] = isset($ilance->GPC['filter_rating']) ? intval($ilance->GPC['filter_rating']) : '0';
		$ilance->GPC['filtered_rating'] = isset($ilance->GPC['filtered_rating']) ? $ilance->GPC['filtered_rating'] : '';
		$ilance->GPC['filter_country'] = isset($ilance->GPC['filter_country']) ? intval($ilance->GPC['filter_country']) : '0';
		$ilance->GPC['filtered_country'] = isset($ilance->GPC['filtered_country']) ? $ilance->GPC['filtered_country'] : '';
		$ilance->GPC['filter_state'] = isset($ilance->GPC['filter_state']) ? intval($ilance->GPC['filter_state']) : '0';
		$ilance->GPC['filtered_state'] = isset($ilance->GPC['filtered_state']) ? $ilance->GPC['filtered_state'] : '';
		$ilance->GPC['filter_city'] = isset($ilance->GPC['filter_city']) ? intval($ilance->GPC['filter_city']) : '0';
		$ilance->GPC['filtered_city'] = isset($ilance->GPC['filtered_city']) ? $ilance->GPC['filtered_city'] : '';
		$ilance->GPC['filter_zip'] = isset($ilance->GPC['filter_zip']) ? intval($ilance->GPC['filter_zip']) : '0';
		$ilance->GPC['filter_bidlimit'] = isset($ilance->GPC['filter_bidlimit']) ? $ilance->GPC['filter_bidlimit'] : '';
		$ilance->GPC['filtered_bidlimit'] = isset($ilance->GPC['filtered_bidlimit']) ? intval($ilance->GPC['filtered_bidlimit']) : '0';
		$ilance->GPC['filtered_zip'] = isset($ilance->GPC['filtered_zip']) ? $ilance->GPC['filtered_zip'] : '';
		$ilance->GPC['filter_underage'] = isset($ilance->GPC['filter_underage']) ? $ilance->GPC['filter_underage'] : '0';
		$ilance->GPC['filter_businessnumber'] = isset($ilance->GPC['filter_businessnumber']) ? $ilance->GPC['filter_businessnumber'] : '0';
		$ilance->GPC['filter_publicboard'] = isset($ilance->GPC['filter_publicboard']) ? intval($ilance->GPC['filter_publicboard']) : '0';
		$ilance->GPC['filter_escrow'] = isset($ilance->GPC['filter_escrow']) ? intval($ilance->GPC['filter_escrow']) : '0';
		$ilance->GPC['filter_gateway'] = '0';
		$ilance->GPC['filter_offline'] = isset($ilance->GPC['filter_offline']) ? intval($ilance->GPC['filter_offline']) : '0';
		$ilance->GPC['paymethod'] = isset($ilance->GPC['paymethod']) ? $ilance->GPC['paymethod'] : array();
		$ilance->GPC['paymethodoptions'] = isset($ilance->GPC['paymethodoptions']) ? $ilance->GPC['paymethodoptions'] : array();
		$ilance->GPC['paymethodoptionsemail'] = isset($ilance->GPC['paymethodoptionsemail']) ? $ilance->GPC['paymethodoptionsemail'] : array();
		// #### CUSTOM BIDDING TYPE ACCEPTANCE FILTERS #########
		$ilance->GPC['filter_bidtype'] = isset($ilance->GPC['filter_bidtype']) ? $ilance->GPC['filter_bidtype'] : '0';
		$ilance->GPC['filtered_bidtype'] = isset($ilance->GPC['filtered_bidtype']) ? $ilance->GPC['filtered_bidtype'] : 'entire';
		// #### AUCTION DETAILS ################################
		$ilance->GPC['description_videourl'] = isset($ilance->GPC['description_videourl']) ? strip_tags($ilance->GPC['description_videourl']) : '';
		$ilance->GPC['project_type'] = 'reverse';
		$ilance->GPC['status'] = 'open';
		$ilance->GPC['draft'] = '0';
		if (isset($ilance->GPC['saveasdraft']) AND $ilance->GPC['saveasdraft'])
		{
			$ilance->GPC['draft'] = '1';
			$ilance->GPC['status'] = 'draft';
		}
		// #### BUDGET DETAILS #################################
		if ($ilance->GPC['filter_budget'] == 0)
		{
			$ilance->GPC['filtered_budgetid'] = 0;
		}
		// #### CUSTOM INFORMATION #############################
		$ilance->GPC['custom'] = (!empty($ilance->GPC['custom']) ? $ilance->GPC['custom'] : array());
		$ilance->GPC['pa'] = (!empty($ilance->GPC['pa']) ? $ilance->GPC['pa'] : array());
		$ilance->GPC['enhancements'] = (!empty($ilance->GPC['enhancements']) ? $ilance->GPC['enhancements'] : array());
		// #### SCHEDULED AUCTION ONLY #########################
		$ilance->GPC['year'] = (isset($ilance->GPC['year'])) ? $ilance->GPC['year'] : '';
		$ilance->GPC['month'] = (isset($ilance->GPC['month'])) ? $ilance->GPC['month'] : '';
		$ilance->GPC['day'] = (isset($ilance->GPC['day'])) ? $ilance->GPC['day'] : '';
		$ilance->GPC['hour'] = (isset($ilance->GPC['hour'])) ? $ilance->GPC['hour'] : '';
		$ilance->GPC['min'] = (isset($ilance->GPC['min'])) ? $ilance->GPC['min'] : '';
		$ilance->GPC['sec'] = (isset($ilance->GPC['sec'])) ? $ilance->GPC['sec'] : '';
		// #### service location #######################################
		$ilance->GPC['city'] = (isset($ilance->GPC['city'])) ? $ilance->GPC['city'] : $_SESSION['ilancedata']['user']['city'];
		$ilance->GPC['state'] = (isset($ilance->GPC['state'])) ? $ilance->GPC['state'] : $_SESSION['ilancedata']['user']['state'];
		$ilance->GPC['zipcode'] = (isset($ilance->GPC['zipcode'])) ? $ilance->GPC['zipcode'] : $_SESSION['ilancedata']['user']['postalzip'];
		$ilance->GPC['country'] = (isset($ilance->GPC['country'])) ? $ilance->GPC['country'] : $_SESSION['ilancedata']['user']['country'];
		// #### currency ###############################################
		$ilance->GPC['currencyid'] = (isset($ilance->GPC['currencyid'])) ? intval($ilance->GPC['currencyid']) : $ilconfig['globalserverlocale_defaultcurrency'];
		// #### invited registered service providers ###################
		$ilance->GPC['invitedmember'] = isset($ilance->GPC['invitedmember']) ? $ilance->GPC['invitedmember'] : array();
		$ilance->GPC['invitelist'] = isset($ilance->GPC['invitelist']) ? $ilance->GPC['invitelist'] : array();
		$ilance->GPC['invitemessage'] = isset($ilance->GPC['invitemessage']) ? $ilance->GPC['invitemessage'] : '';
		$apihookcustom = array();
		log_event($_SESSION['ilancedata']['user']['userid'], $ilpage['buying'], $ilance->GPC['cmd']);
		
		($apihook = $ilance->api('buying_submit_end')) ? eval($apihook) : false;
		
		// #### CREATE AUCTION #################################
		$ilance->auction_rfp->insert_service_auction(
			$_SESSION['ilancedata']['user']['userid'],
			$ilance->GPC['project_type'],
			$ilance->GPC['status'],
			$ilance->GPC['project_state'],
			$ilance->GPC['cid'],
			$ilance->GPC['rfpid'],
			$ilance->GPC['project_title'],
			$ilance->GPC['description'],
			$ilance->GPC['description_videourl'],
			$ilance->GPC['additional_info'],
			$ilance->GPC['keywords'],
			$ilance->GPC['custom'],
			$ilance->GPC['pa'],
			$ilance->GPC['filter_bidtype'],
			$ilance->GPC['filtered_bidtype'],
			$ilance->GPC['filter_budget'],
			$ilance->GPC['filtered_budgetid'],
			$ilance->GPC['filtered_auctiontype'],
			$ilance->GPC['filter_escrow'],
			$ilance->GPC['filter_gateway'],
			$ilance->GPC['filter_offline'],
			$ilance->GPC['paymethod'],
			$ilance->GPC['paymethodoptions'],
			$ilance->GPC['paymethodoptionsemail'],
			$ilance->GPC['project_details'],
			$ilance->GPC['bid_details'],
			$ilance->GPC['invitelist'],
			$ilance->GPC['invitemessage'],
			$ilance->GPC['invitedmember'],
			$ilance->GPC['year'],
			$ilance->GPC['month'],
			$ilance->GPC['day'],
			$ilance->GPC['hour'],
			$ilance->GPC['min'],
			$ilance->GPC['sec'],
			$ilance->GPC['duration'],
			$ilance->GPC['duration_unit'],
			$ilance->GPC['filtered_rating'],
			$ilance->GPC['filtered_country'],
			$ilance->GPC['filtered_state'],
			$ilance->GPC['filtered_city'],
			$ilance->GPC['filtered_zip'],			
			$ilance->GPC['filter_rating'],
			$ilance->GPC['filter_country'],
			$ilance->GPC['filter_state'],
			$ilance->GPC['filter_city'],
			$ilance->GPC['filter_zip'],
			$ilance->GPC['filter_bidlimit'],
			$ilance->GPC['filtered_bidlimit'],
			$ilance->GPC['filter_underage'],
			$ilance->GPC['filter_businessnumber'],
			$ilance->GPC['filter_publicboard'],
			$ilance->GPC['enhancements'],
			$ilance->GPC['draft'],
			$ilance->GPC['city'],
			$ilance->GPC['state'],
			$ilance->GPC['zipcode'],
			$ilance->GPC['country'],
			$skipemailprocess = 0,
			$apihookcustom,
			$isbulkupload = false,
			$ilance->GPC['currencyid']
		);
		exit();
	}
	// #### SAVE EXISTING SERVICE AUCTION ##################################
	else if (isset($ilance->GPC['dosave']))
	{
		// developers can use below query to attach an api hook to final sql
		$query_field_data = '';
		$ownerid = (!empty($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin']) ? fetch_auction('user_id', intval($ilance->GPC['rfpid'])) : $_SESSION['ilancedata']['user']['userid'];
		// #### final category checkup #################################
		if ($ilance->categories->can_post($_SESSION['ilancedata']['user']['slng'], 'service', $ilance->GPC['cid']) == false)
		{
			print_notice('{_this_is_a_nonposting_category}', '{_please_choose_another_category_to_list_your_auction_under_this_category_is_currently_reserved_for_postable_subcategories_and_does_not_allow_any_auction_postings}', 'javascript:history.back(1);', '{_back}');
			exit();
		}
		if (empty($ilance->GPC['rfpid']) OR empty($ilance->GPC['description']) OR empty($ilance->GPC['date_end']) OR !mb_ereg("([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})", $ilance->GPC['date_end']))
		{
			$area_title = '{_rfp_details_update_error}';
			$page_title = SITE_NAME . ' - {_rfp_details_update_error}';
			print_notice('{_rfp_was_not_updated}', '<p>{_were_sorry_there_was_a_problem_updating_your_request_for_proposal}</p><ul><li />{_description_can_not_be_empty}<li />{_budget_can_not_be_empty}<li />{_verify_the_end_date_for_your_rfp_is_formatted_correctly}</ul><p>{_please_contact_customer_support}</p>', 'javascript:history.back(1);', '{_retry}');
			exit();
		}
		$ilance->GPC['old']['paymethod'] = isset($ilance->GPC['old']['paymethod']) ? unserialize($ilance->GPC['old']['paymethod']) : '';
		// handle updating any category questions for this auction (if changed)
		if (isset($ilance->GPC['custom']) AND is_array($ilance->GPC['custom']))
		{
			$ilance->auction_post->process_custom_questions($ilance->GPC['custom'], $ilance->GPC['rfpid'], 'service');
		}
		// #### PROCESS CUSTOM PROFILE ANSWER FILTERS ##########
		if (isset($ilance->GPC['pa']) AND is_array($ilance->GPC['pa']))
		{
			// process our answer input and store them into the datastore
			$ilance->profile_questions->insert_profile_answers($ilance->GPC['pa'], $ilance->GPC['rfpid']);
		}
		$ilance->GPC['featured'] = isset($ilance->GPC['old']['featured']) ? $ilance->GPC['old']['featured'] : '0';
		$ilance->GPC['highlite'] = isset($ilance->GPC['old']['highlite']) ? $ilance->GPC['old']['highlite'] : '0';
		$ilance->GPC['bold'] = isset($ilance->GPC['old']['bold']) ? $ilance->GPC['old']['bold'] : '0';   
		$ilance->GPC['autorelist'] = isset($ilance->GPC['old']['autorelist']) ? $ilance->GPC['old']['autorelist'] : '0';                              
		$ilance->GPC['enhancements'] = (!empty($ilance->GPC['enhancements']) ? $ilance->GPC['enhancements'] : array());
		if (is_array($ilance->GPC['enhancements']))
		{
			$show['disableselectedenhancements'] = true;
			$enhance = $ilance->auction_fee->process_listing_enhancements_transaction($ilance->GPC['enhancements'], $_SESSION['ilancedata']['user']['userid'], intval($ilance->GPC['rfpid']), 'update', 'service');
			if (is_array($enhance))
			{
				$ilance->GPC['featured'] = (int)$enhance['featured'];
				$ilance->GPC['highlite'] = (int)$enhance['highlite'];
				$ilance->GPC['bold'] = (int)$enhance['bold'];
				$ilance->GPC['autorelist'] = (int)$enhance['autorelist'];
			}
		}
		$ilance->GPC['featured_date'] = ($ilance->GPC['featured'] AND isset($ilance->GPC['old']['featured_date']) AND $ilance->GPC['old']['featured_date'] == '0000-00-00 00:00:00') ? DATETIME24H : '0000-00-00 00:00:00';
		// does owner extend the auction?
		$sqlextend = (isset($ilance->GPC['extend']) AND $ilance->GPC['extend'] > 0) ? "date_end = DATE_ADD(date_end, INTERVAL " . intval($ilance->GPC['extend']) . " DAY)," : '';
		$ilance->GPC['filter_rating'] = isset($ilance->GPC['filter_rating']) ? $ilance->GPC['filter_rating'] : '0';
		$ilance->GPC['filter_country'] = isset($ilance->GPC['filter_country']) ? $ilance->GPC['filter_country'] : '0';
		$ilance->GPC['filter_state'] = isset($ilance->GPC['filter_state']) ? $ilance->GPC['filter_state'] : '0';
		$ilance->GPC['filter_city'] = isset($ilance->GPC['filter_city']) ? $ilance->GPC['filter_city'] : '0';
		$ilance->GPC['filter_zip'] = isset($ilance->GPC['filter_zip']) ? $ilance->GPC['filter_zip'] : '0';
		$ilance->GPC['filter_bidlimit'] = isset($ilance->GPC['filter_bidlimit']) ? $ilance->GPC['filter_bidlimit'] : '';
		$ilance->GPC['filtered_bidlimit'] = isset($ilance->GPC['filtered_bidlimit']) ? intval($ilance->GPC['filtered_bidlimit']) : '0';
		$ilance->GPC['filter_underage'] = isset($ilance->GPC['filter_underage']) ? $ilance->GPC['filter_underage'] : '0';
		$ilance->GPC['filter_businessnumber'] = isset($ilance->GPC['filter_businessnumber']) ? $ilance->GPC['filter_businessnumber'] : '0';
		$ilance->GPC['filter_publicboard'] = isset($ilance->GPC['filter_publicboard']) ? $ilance->GPC['filter_publicboard'] : '0';
		$ilance->GPC['filter_bidtype'] = isset($ilance->GPC['filter_bidtype']) ? $ilance->GPC['filter_bidtype'] : $ilance->GPC['old']['filter_bidtype'];
		$ilance->GPC['filter_budget'] = isset($ilance->GPC['filter_budget']) ? $ilance->GPC['filter_budget'] : $ilance->GPC['old']['filter_budget'];
		$ilance->GPC['filter_escrow'] = isset($ilance->GPC['filter_escrow']) ? $ilance->GPC['filter_escrow'] : $ilance->GPC['old']['filter_escrow'];
		$ilance->GPC['filter_offline'] = isset($ilance->GPC['filter_offline']) ? $ilance->GPC['filter_offline'] : $ilance->GPC['old']['filter_offline'];
		$ilance->GPC['filtered_rating'] = isset($ilance->GPC['filtered_rating']) ? $ilance->GPC['filtered_rating'] : $ilance->GPC['old']['filtered_rating'];
		$ilance->GPC['filtered_country'] = isset($ilance->GPC['filtered_country']) ? $ilance->GPC['filtered_country'] : $ilance->GPC['old']['filtered_country'];
		$ilance->GPC['filtered_state'] = isset($ilance->GPC['filtered_state']) ? $ilance->GPC['filtered_state'] : $ilance->GPC['old']['filtered_state'];
		$ilance->GPC['filtered_city'] = isset($ilance->GPC['filtered_city']) ? $ilance->GPC['filtered_city'] : $ilance->GPC['old']['filtered_city'];
		$ilance->GPC['filtered_zip'] = isset($ilance->GPC['filtered_zip']) ? $ilance->GPC['filtered_zip'] : $ilance->GPC['old']['filtered_zip'];
		$ilance->GPC['filtered_bidtype'] = isset($ilance->GPC['filtered_bidtype']) ? mb_strtolower($ilance->GPC['filtered_bidtype']) : $ilance->GPC['old']['filtered_bidtype'];
		if ($ilance->GPC['cid'] != $ilance->GPC['old']['cid'])
		{
			$budgetgroup = $ilance->categories->budgetgroup($ilance->GPC['cid']);
			$query_field_data .= "budgetgroup = '" . $budgetgroup . "',";
		}
		if (isset($ilance->GPC['filtered_budgetid']) AND $ilance->GPC['filtered_budgetid'] > 0 AND $ilance->GPC['filter_budget'] == 1)
		{
			$ilance->GPC['filtered_budgetid'] = $ilance->GPC['filtered_budgetid'];
		}
		else if (isset($ilance->GPC['filtered_budgetid']) AND $ilance->GPC['filter_budget'] == 0)
		{
			$ilance->GPC['filtered_budgetid'] = 0;
		}
		else
		{
			$ilance->GPC['filtered_budgetid'] = $ilance->GPC['old']['filtered_budgetid'];
		}
		$ilance->GPC['additional_info'] = isset($ilance->GPC['additional_info']) ? $ilance->GPC['additional_info'] : $ilance->GPC['old']['additional_info'];
		$ilance->GPC['description_videourl'] = isset($ilance->GPC['description_videourl']) ? strip_tags($ilance->GPC['description_videourl']) : $ilance->GPC['old']['description_videourl'];
		$ilance->GPC['keywords'] = isset($ilance->GPC['keywords']) ? strip_tags($ilance->GPC['keywords']) : $ilance->GPC['old']['keywords'];
		$ilance->GPC['paymethod'] = isset($ilance->GPC['paymethod']) ? serialize($ilance->GPC['paymethod']) : serialize($ilance->GPC['old']['paymethod']);
		$ilance->GPC['paymethodoptions'] = isset($ilance->GPC['paymethodoptions']) ? serialize($ilance->GPC['paymethodoptions']) : $ilance->GPC['old']['paymethodoptions'];
		$ilance->GPC['paymethodoptionsemail'] = isset($ilance->GPC['paymethodoptionsemail']) ? serialize($ilance->GPC['paymethodoptionsemail']) : $ilance->GPC['old']['paymethodoptionsemail'];
		$ilance->GPC['project_title'] = isset($ilance->GPC['project_title']) ? strip_tags($ilance->GPC['project_title']) : $ilance->GPC['old']['project_title'];
		$ilance->GPC['project_details'] = isset($ilance->GPC['project_details']) ? $ilance->GPC['project_details'] : $ilance->GPC['old']['project_details'];
		$ilance->GPC['bid_details'] = isset($ilance->GPC['bid_details']) ? $ilance->GPC['bid_details'] : (isset($ilance->GPC['old']['bid_details']) ? $ilance->GPC['old']['bid_details'] : '');
		// auction moderation logic
		$sql = $ilance->db->query("
			SELECT cid, status, project_state
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . intval($ilance->GPC['rfpid']) . "'
				AND user_id = '" . intval($ownerid) . "'
		", 0, null, __FILE__, __LINE__);
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		$visible = (($ilconfig['moderationsystem_disableauctionmoderation'] == '1' OR $res['status'] == 'draft')? '1' : '0');
		// #### service location ###############################
		$ilance->GPC['city'] = (isset($ilance->GPC['city'])) ? $ilance->GPC['city'] : isset($ilance->GPC['old']['city']) ? $ilance->GPC['old']['city'] : $_SESSION['ilancedata']['user']['city'];
		$ilance->GPC['state'] = (isset($ilance->GPC['state'])) ? $ilance->GPC['state'] : isset($ilance->GPC['old']['state']) ? $ilance->GPC['old']['state'] : $_SESSION['ilancedata']['user']['state'];
		$ilance->GPC['zipcode'] = (isset($ilance->GPC['zipcode'])) ? $ilance->GPC['zipcode'] : isset($ilance->GPC['old']['zipcode']) ? $ilance->GPC['old']['zipcode'] : $_SESSION['ilancedata']['user']['postalzip'];
		$ilance->GPC['country'] = (isset($ilance->GPC['country'])) ? $ilance->GPC['country'] : $ilance->GPC['old']['country'];
		$ilance->GPC['countryid'] = fetch_country_id($ilance->GPC['country'], $_SESSION['ilancedata']['user']['slng']);
		// handle listing differences and store into revision log
		$ilance->auction_post->handle_revision_log_changes('service');
		log_event($_SESSION['ilancedata']['user']['userid'], $ilpage['buying'], $ilance->GPC['cmd']);
		
		($apihook = $ilance->api('update_service_auction_submit_start')) ? eval($apihook) : false;
		
		// update auction
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "projects 
			SET $sqlextend
			cid = '" . intval($ilance->GPC['cid']) . "',
			visible = '" . $visible . "',
			project_title = '" . $ilance->db->escape_string($ilance->GPC['project_title']) . "',
			description = '" . $ilance->db->escape_string($ilance->GPC['description']) . "',
			description_videourl = '" . $ilance->db->escape_string($ilance->GPC['description_videourl']) . "',
			keywords = '" . $ilance->db->escape_string($ilance->GPC['keywords']) . "',
			additional_info = '" . $ilance->db->escape_string($ilance->GPC['additional_info']) . "',
			paymethod = '" . $ilance->db->escape_string($ilance->GPC['paymethod']) . "',
			paymethodoptions = '" . $ilance->db->escape_string($ilance->GPC['paymethodoptions']) . "',
			paymethodoptionsemail = '" . $ilance->db->escape_string($ilance->GPC['paymethodoptionsemail']) . "',
			bid_details = '" . $ilance->db->escape_string($ilance->GPC['bid_details']) . "',
			project_details = '" . $ilance->db->escape_string($ilance->GPC['project_details']) . "',
			filter_rating = '" . intval($ilance->GPC['filter_rating']) . "',
			filter_country = '" . intval($ilance->GPC['filter_country']) . "',
			filter_state = '" . intval($ilance->GPC['filter_state']) . "',
			filter_city = '" . intval($ilance->GPC['filter_city']) . "',
			filter_zip = '" . intval($ilance->GPC['filter_zip']) . "',
			filter_underage = '" . intval($ilance->GPC['filter_underage']) . "',
			filter_businessnumber = '" . intval($ilance->GPC['filter_businessnumber']) . "',
			filter_publicboard = '" . intval($ilance->GPC['filter_publicboard']) . "',
			filter_bidtype = '" . intval($ilance->GPC['filter_bidtype']) . "',
			filter_budget = '" . intval($ilance->GPC['filter_budget']) . "',
			filter_escrow = '" . intval($ilance->GPC['filter_escrow']) . "',
			filter_gateway = '0',
			filter_offline = '" . intval($ilance->GPC['filter_offline']) . "',
			filtered_rating = '" . $ilance->db->escape_string($ilance->GPC['filtered_rating']) . "',
			filtered_country = '" . $ilance->db->escape_string($ilance->GPC['filtered_country']) . "',
			filtered_state = '" . ucfirst($ilance->db->escape_string($ilance->GPC['filtered_state'])) . "',
			filtered_city = '" . ucfirst($ilance->db->escape_string($ilance->GPC['filtered_city'])) . "',
			filtered_zip = '" . mb_strtoupper($ilance->db->escape_string($ilance->GPC['filtered_zip'])) . "',
			filter_bidlimit = '" . intval($ilance->GPC['filter_bidlimit']) . "',
			filtered_bidlimit = '" . intval($ilance->GPC['filtered_bidlimit']) . "',
			filtered_bidtype = '" . mb_strtoupper($ilance->db->escape_string($ilance->GPC['filtered_bidtype'])) . "',
			filtered_budgetid = '" . intval($ilance->GPC['filtered_budgetid']) . "',
			featured = '" . intval($ilance->GPC['featured']) . "',
			featured_date = '" . $ilance->db->escape_string($ilance->GPC['featured_date']) . "',
			highlite = '" . intval($ilance->GPC['highlite']) . "',
			autorelist = '" . intval($ilance->GPC['autorelist']) . "',
			bold = '" . intval($ilance->GPC['bold']) . "',
			countryid = '" . intval($ilance->GPC['countryid']) . "',
			country = '" . $ilance->db->escape_string($ilance->GPC['country']) . "',
			state = '" . $ilance->db->escape_string($ilance->GPC['state']) . "',
			city = '" . $ilance->db->escape_string($ilance->GPC['city']) . "',
			$query_field_data
			zipcode = '" . $ilance->db->escape_string(format_zipcode($ilance->GPC['zipcode'])) . "'
			WHERE project_id = '" . intval($ilance->GPC['rfpid']) . "'
				AND user_id = '" . intval($ownerid) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		// #### determine if we need to move the category (user change)
		$ilance->categories->move_listing_category_from_to($ilance->GPC['rfpid'], $res['cid'], $ilance->GPC['cid'], $res['project_state'], $res['status'], $res['status']);
		unset($res);
		
		($apihook = $ilance->api('update_service_auction_submit')) ? eval($apihook) : false;
		
		$area_title = '{_rfp_detailed_information_updated}';
		$page_title = SITE_NAME . ' - {_rfp_detailed_information_updated}';
		if ($ilconfig['moderationsystem_disableauctionmoderation'] == '0')
		{
			$ilance->categories->build_category_count(intval($ilance->GPC['cid']), 'subtract', "seller updating his listings from selling activity: subtracting increment count category id " . $ilance->GPC['cid']);
			$ilance->email->mail = SITE_EMAIL;
			$ilance->email->slng = fetch_site_slng();
			$ilance->email->get('updateauction_moderation_admin');
			$ilance->email->set(array(
				'{{project_title}}' => $ilance->GPC['project_title'],
				'{{category}}' =>$ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $ilance->GPC['cid']),
				'{{p_id}}' => intval($ilance->GPC['rfpid']),
				'{{closing_date}}' => print_date($ilance->GPC['date_end'], $ilconfig['globalserverlocale_globaltimeformat']),
				'{{details}}' => ucfirst($ilance->GPC['project_details']),
				'{{privacy}}' => ucfirst($ilance->GPC['bid_details']),
			));
			$ilance->email->send();
			print_notice('{_rfp_successfully_updated}', '{_you_have_successfully_updated_your_request_for_proposal}' . " " . '{_if_you_have_entered_a_new_ending_date_for_your_rfp_this_change_would_take_effect_immediately}' . '{_your_listing_was_posted_and_requires_staff_moderation}', $ilpage['buying'] . '?cmd=management', '{_return_to_the_previous_menu}');
			exit();
		}
		else
		{  
			print_notice('{_rfp_successfully_updated}', '{_you_have_successfully_updated_your_request_for_proposal}' . " " . '{_if_you_have_entered_a_new_ending_date_for_your_rfp_this_change_would_take_effect_immediately}' . "<br /><br />" . '{_please_contact_customer_support}' . "<br /><br />", $ilpage['buying'] . '?cmd=management', '{_return_to_the_previous_menu}');
			exit();
		}                    
	}
	// #### UPDATE EXISTING SERVICE AUCTION ################################
	else
	{
		if ($ilance->GPC['cmd'] == 'new-rfp')
		{
			if ($ilance->categories->can_post($_SESSION['ilancedata']['user']['slng'], $cattype = 'service', $ilance->GPC['cid']) == false)
			{
				$url = ($ilconfig['globalauctionsettings_seourls']) ? HTTP_SERVER . 'buy' : HTTP_SERVER . $ilpage['main'] . '?cmd=buying';
				
				print_notice('{_this_is_a_nonposting_category}', '{_please_choose_another_category_to_list_your_auction_under_this_category_is_currently_reserved_for_postable_subcategories_and_does_not_allow_any_auction_postings}', $url, '{_try_again}');
				exit();
			}
			$area_title = '{_post_project}';
			$page_title = SITE_NAME . ' - {_post_project}';
			// #### main category being posted in ##########
			$cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
			$owner_id = $_SESSION['ilancedata']['user']['userid'];
			// #### prevent the top cats in breadcrumb to contain any fields from this form
			$show['nourlbit'] = true;
			$ilance->categories->breadcrumb($cid, 'service', $_SESSION['ilancedata']['user']['slng']);
			$navcrumb[""] = '{_post_project}';
			if (!empty($_SESSION['ilancedata']['tmp']['new_project_id']) AND $_SESSION['ilancedata']['tmp']['new_project_id'] > 0)
			{
				$project_id = $_SESSION['ilancedata']['tmp']['new_project_id'];
			}
			else
			{
				$project_id = $ilance->auction_rfp->construct_new_auctionid();
				$_SESSION['ilancedata']['tmp']['new_project_id'] = $project_id;
			}
			// #### saving as draft? #######################
			$draft = (isset($ilance->GPC['saveasdraft']) AND $ilance->GPC['saveasdraft']) ? 'checked="checked"' : '';
			$wysiwyg_area = can_post_html($owner_id)
				? print_wysiwyg_editor('description', '', 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg'], false, ($ilconfig['template_table_width'] - ($ilconfig['table_cellpadding'] * 2)), '200', '', 'ckeditor', $ilconfig['ckeditor_listingdescriptiontoolbar'])
				: print_wysiwyg_editor('description', '', 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg'], false, ($ilconfig['template_table_width'] - ($ilconfig['table_cellpadding'] * 2)), '120', '', 'bbeditor');
			$project_questions = $ilance->auction_questions->construct_auction_questions($cid, $project_id, 'input', 'service', $columns = 3);
			$bidfilters = $ilance->auction_post->print_bid_filters();
			$profilebidfilters = $ilance->auction_post->print_profile_bid_filters($cid, 'input', 'service');        
		}
		else
		{
			$area_title = '{_update_project}';
			$page_title = SITE_NAME . ' - {_update_project}';
			$project_id = intval($ilance->GPC['id']);
			$owner_id = $_SESSION['ilancedata']['user']['userid'];
			$sql = $ilance->db->query("
				SELECT id, project_id, escrow_id, cid, description, ishtml, description_videourl, date_added, date_starts, date_end, user_id, visible, views, project_title, bids, bidsdeclined, bidsretracted, bidsshortlisted, budgetgroup, additional_info, status, close_date, transfertype, transfer_to_userid, transfer_from_userid, transfer_to_email, transfer_status, transfer_code, project_details, project_type, project_state, bid_details, filter_rating, filter_country, filter_state, filter_city, filter_zip, filter_underage, filter_businessnumber, filter_bidtype, filter_budget, filter_escrow, filter_gateway, filter_ccgateway, filter_offline, filter_publicboard, filtered_rating, filtered_country, filtered_state, filtered_city, filtered_zip, filter_bidlimit, filtered_bidlimit, filtered_bidtype, filtered_bidtypecustom, filtered_budgetid, filtered_auctiontype, buynow, buynow_price, buynow_qty, buynow_qty_lot, items_in_lot, buynow_purchases, reserve, reserve_price, featured, featured_date, featured_searchresults, highlite, bold, autorelist, autorelist_date, startprice, paymethod, paymethodcc, paymethodoptions, paymethodoptionsemail, keywords, currentprice, insertionfee, enhancementfee, fvf, isfvfpaid, isifpaid, isenhancementfeepaid, ifinvoiceid, enhancementfeeinvoiceid, fvfinvoiceid, returnaccepted, returnwithin, returngivenas, returnshippaidby, returnpolicy, buyerfeedback, sellerfeedback, hasimage, hasimageslideshow, hasdigitalfile, haswinner, hasbuynowwinner, winner_user_id, donation, charityid, donationpercentage, donermarkedaspaid, donermarkedaspaiddate, donationinvoiceid, currencyid, countryid, country, state, city, zipcode, sku, upc, ean, isbn10, isbn13, partnumber, modelnumber, countdownresets, bulkid, updateid
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . intval($ilance->GPC['id']) . "'
					AND project_state = 'service'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$ilance->GPC = array_merge($ilance->GPC, $ilance->db->fetch_array($sql, DB_ASSOC));
				// #### can we update auction? #########
				$show['noupdateauction'] = ($ilance->GPC['status'] == 'open' OR $ilance->GPC['status'] == 'draft') ? 0 : 1;
				$date_end = $ilance->GPC['date_end'];
			}
			else
			{
				print_notice('{_disabled}', '{_were_sorry_this_feature_is_currently_disabled}', $ilpage['main'], '{_main_menu}');
				exit();
			}
			// #### ADMIN UPDATING LISTING? ################
			if (!empty($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] AND isset($ilance->GPC['admincp']) AND $ilance->GPC['admincp'])
			{
				// inline auction ajax controls
				$headinclude .= "
<script type=\"text/javascript\">
<!--
var searchid = 0;
var value = '';
var type = '';
var imgtag = '';
var favoriteicon = '';
var status = '';
function fetch_response()
{
	if (xmldata.handler.readyState == 4 && xmldata.handler.status == 200 && xmldata.handler.responseXML)
	{
		// format response
		response = fetch_tags(xmldata.handler.responseXML, 'status')[0];
		phpstatus = xmldata.fetch_data(response);
		
		searchiconsrc = fetch_js_object('inline_enhancement_' + xmldata.searchid + '_' + xmldata.type + '').src;
		status = searchiconsrc.match(/\/unchecked.gif/gi);
		if (status == '/unchecked.gif')
		{
		       status = 'unchecked';
		}
		else
		{
		       status = 'checked';
		}                                
		if (status == 'unchecked')
		{
			if (phpstatus == 'on' || phpstatus == 'off')
			{
				favoriteiconsrc = fetch_js_object('inline_enhancement_' + xmldata.searchid + '_' + xmldata.type + '').src;
				imgtag = fetch_js_object('inline_enhancement_' + xmldata.searchid + '_' + xmldata.type + '');
				
				favoriteicon2 = favoriteiconsrc.replace(/unchecked.gif/gi, 'working.gif');
				imgtag.src = favoriteicon2;
				
				favoriteicon = favoriteiconsrc.replace(/unchecked.gif/gi, 'checked.gif');
				var t = window.setTimeout('reset_image()', 700);
			}
			else
			{
				alert_js(phpstatus);
			}
		}
		else if (status == 'checked')
		{
			if (phpstatus == 'on' || phpstatus == 'off')
			{
				favoriteiconsrc = fetch_js_object('inline_enhancement_' + xmldata.searchid + '_' + xmldata.type + '').src;
				imgtag = fetch_js_object('inline_enhancement_' + xmldata.searchid + '_' + xmldata.type + '');
				
				favoriteicon2 = favoriteiconsrc.replace(/checked.gif/gi, 'working.gif');
				imgtag.src = favoriteicon2;
	
				favoriteicon = favoriteiconsrc.replace(/checked.gif/gi, 'unchecked.gif');
				var t = window.setTimeout('reset_image()', 700);
			}
			else
			{
				alert_js(phpstatus); 
			}
		}
		xmldata.handler.abort();
	}
}
function update_enhancement(searchid, type)
{                        
	// set ajax handler
	xmldata = new AJAX_Handler(true);
	
	// url encode the vars
	searchid = urlencode(searchid);
	xmldata.searchid = searchid;
	
	type = urlencode(type);
	xmldata.type = type;
	
	searchiconsrc = fetch_js_object('inline_enhancement_' + searchid + '_' + type + '').src;
	status = searchiconsrc.match(/\/unchecked.gif/gi);
	if (status == '/unchecked.gif')
	{
	       value = 'on';
	}
	else
	{
	       value = 'off';
	}
	xmldata.onreadystatechange(fetch_response);
	
	// send data to php
	xmldata.send(AJAXURL, 'do=acpenhancements&value=' + value + '&id=' + searchid + '&type=' + type + '&s=' + ILSESSION + '&token=' + ILTOKEN);                        
}
//-->
</script>";                                        
				$ilance->categories->build_array('service', $_SESSION['ilancedata']['user']['slng'], 0, true);
				$category_pulldown = $ilance->categories_pulldown->print_cat_pulldown($ilance->GPC['cid'], 'service', 'level', 'cid', 0, $_SESSION['ilancedata']['user']['slng'], 1, '', 0, 1, 0, '540px', 0, 1, 0, false, false, $ilance->categories->cats);
				if ($ilance->GPC['visible'])
				{
					$auctionvisible = ($ilance->GPC['status'] == 'draft') ? '<label for="visible1"><input type="radio" name="visible" value="1" disabled="disabled" id="visible1" /> {_yes}</label> <label for="visible0"><input type="radio" name="visible" value="0" checked="checked" id="visible0" /> {_no}</label>' : '<label for="visible1"><input type="radio" name="visible" value="1" checked="checked" id="visible1" /> {_yes}</label> <label for="visible0"><input type="radio" name="visible" value="0" id="visible0" /> {_no}</label>';
				}
				else
				{
					$auctionvisible = '<label for="visible1"><input type="radio" name="visible" value="1" id="visible1" /> {_yes}</label> <label for="visible0"><input type="radio" name="visible" value="0" checked="checked" id="visible0" /> {_no}</label>';
				}
				$transfer_ownership = $ilance->auction->fetch_transfer_ownership($project_id);
				$project_state_pulldown = $ilance->admincp->auction_state_pulldown($project_id);
				$project_details_pulldown = $ilance->admincp->auction_details_pulldown($ilance->GPC['project_details'], 0, 'service');
				$status_pulldown = $ilance->admincp->auction_status_pulldown($ilance->GPC['status'], false, 'service');
				$enhancement_list = $ilance->admincp->fetch_auction_enhancements_list($project_id);
				$date_added = $ilance->GPC['date_added'];
				$date_starts = $ilance->GPC['date_starts'];
				$owner_id = $ilance->GPC['user_id'];
			}
			// main category being posted in
			$cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
			$show['nourlbit'] = true;
			$ilance->categories->breadcrumb($cid, 'service', $_SESSION['ilancedata']['user']['slng']);
			$navcrumb[""] = '{_update_project}';
			// fetch attachments uploaded
			$attachmentlist = fetch_inline_attachment_filelist($owner_id, $project_id, 'project');
			// saving as draft?
			$draft = ($ilance->GPC['status'] == 'draft') ? 'checked="checked"' : '';
			// existing information in hidden fields so we can compare for revision log
			$hiddenfields = print_hidden_fields(false, array('buynow','buynow_price','buynow_qty','reserve','startprice','invoiceid','escrow_id','bids','budgetgroup','transfer_to_userid','transfer_from_userid','cmd','date_end','filtered_auctiontype','project_id','project_type','reserve_price','rfpid','state','updateid','fvf','insertionfee','currentprice','bid_details','project_state','transfertype','close_date','status','views','visible','user_id','date_added','id','fvfinvoiceid','ifinvoiceid','isifpaid','isfvfpaid'), $questionmarkfirst = false, $prepend_text = 'old[', $append_text = ']');
			$wysiwyg_area = (isset($ilance->GPC['ishtml']) AND $ilance->GPC['ishtml'] == '1')
				? print_wysiwyg_editor('description', $ilance->GPC['description'], 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg'], false, ($ilconfig['template_table_width'] - ($ilconfig['table_cellpadding'] * 2)), '200', '', 'ckeditor', $ilconfig['ckeditor_listingdescriptiontoolbar'])
				: print_wysiwyg_editor('description', $ilance->GPC['description'], 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg'], false, ($ilconfig['template_table_width'] - ($ilconfig['table_cellpadding'] * 2)), '120', '', 'bbeditor');
			$project_questions = $ilance->auction_questions->construct_auction_questions($cid, $project_id, 'update', 'service', 3);
			$bidfilters = $ilance->auction_post->print_bid_filters();
			$profilebidfilters = $ilance->auction_post->print_profile_bid_filters($cid, 'update', 'service', $project_id);
			// update mode
			$date_end = $ilance->GPC['date_end'];
			$extendauction = $ilance->auction_post->print_extend_auction('extend');
			// rebuild selected auction enhancements
			$show['disableselectedenhancements'] = true;
			if ($ilance->GPC['featured'])
			{
				$ilance->GPC['enhancements']['featured'] = 1;
			}
			if ($ilance->GPC['highlite'])
			{
				$ilance->GPC['enhancements']['highlite'] = 1;
			}
			if ($ilance->GPC['bold'])
			{
				$ilance->GPC['enhancements']['bold'] = 1;
			}
			if ($ilance->GPC['autorelist'])
			{
				$ilance->GPC['enhancements']['autorelist'] = 1;
			}	
			// repopulate the invitation user list
			$invitesql = $ilance->db->query("
				SELECT invite_message, email, name
				FROM " . DB_PREFIX . "project_invitations
				WHERE project_id = '$project_id'
					AND email != ''
					AND name != ''
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($invitesql) > 0)
			{
				while ($inviteres = $ilance->db->fetch_array($invitesql, DB_ASSOC))
				{
					$ilance->GPC['invitelist']['email'][] = $inviteres['email'];
					$ilance->GPC['invitelist']['name'][] = $inviteres['name'];
					$ilance->GPC['invitemessage'] = $inviteres['invite_message'];
				}
			}
			$show['bidsplaced'] = false;
			if ($ilance->GPC['bids'] > 0)
			{
				if (!empty($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'])
				{
					// this is so admin can update all fields of the listing
					$show['bidsplaced'] = false;
				}
				else
				{
					$show['bidsplaced'] = true;
				}
			}
		}
	}
	// some javascript above the template (not between <head>..)
	$js_start = $ilance->auction_post->print_js('service');
	// build an if condition to either show advanced profile filters or hide them if none available
	$filter_quantity = $ilance->auction_post->get_filters_quantity($cid);
	// #### auction title ##########################################
	if ($ilance->GPC['cmd'] == 'new-rfp')
	{
		$title = $ilance->auction_post->print_title_input('project_title');	
	}
	else
	{
		if ($ilconfig['globalfilters_changeauctiontitle'] == '1' AND $show['bidsplaced'] == false)
		{
			$title = $ilance->auction_post->print_title_input('project_title');
		}
		else
		{
			$title = $ilance->auction_post->print_title_input('project_title', true);
		}
	}
	// #### video description cost #################################
	$videodescriptioncost = ($ilconfig['serviceupsell_videodescriptioncost'] > 0)
		? $ilance->currency->format($ilconfig['serviceupsell_videodescriptioncost'])
		: '{_free}';
	// #### video description ######################################
	$description_videourl = $ilance->auction_post->print_video_description_input('description_videourl', $show['bidsplaced']);
	// additional info input
	$additional = $ilance->auction_post->print_additional_info_input('additional_info');
	// keywords input
	$keywordinput = $ilance->auction_post->print_keywords_input('keywords');
	// upload attachment button
	$attachment_style = ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'attachments') == 'yes') ? '' : 'disabled="disabled"';
	$uploadbutton = '<input name="attachment" onclick=Attach("' . HTTP_SERVER . $ilpage['upload'] . '?crypted=' . encrypt_url(array('attachtype' => 'project', 'project_id' => $project_id, 'user_id' => $_SESSION['ilancedata']['user']['userid'], 'category_id' => $cid, 'filehash' => md5(time()), 'max_filesize' => $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'uploadlimit'))) . '") type="button" value="{_upload}" class="buttons" ' . $attachment_style . ' style="font-size:15px" />';
	// bid amount type pulldown
	$bidtypefilter = $ilance->auction_post->print_bid_amount_type($cid, 'service');
	// construct budget logic pulldown
	$budgetfilter = $ilance->auction_post->print_budget_logic_type($cid, 'service');
	// escrow filter (if enabled, javascript will hide the payment methods input box on preview also)
	$escrowfilter = $ilance->auction_post->print_escrow_filter($cid, 'service', 'servicebuyer');
	// auction event access
	$auctioneventtype = $ilance->auction_post->print_event_type_filter('service');
	// invitation options and controls
	$inviteoptions = $ilance->auction_post->print_invitation_controls('service');
	// duration
	$duration = isset($ilance->GPC['duration']) ? intval($ilance->GPC['duration']) : '';
	$duration = $ilance->auction_post->duration($duration, 'duration', $show['bidsplaced'], 'D', true, $cid);
	// realtime scheduled event date/time
	$durationbits = isset($ilance->GPC['duration_unit']) ? intval($ilance->GPC['duration_unit']) : 'D';
	$durationbits = $ilance->auction_post->print_duration_logic($durationbits, 'duration_unit', $show['bidsplaced'], 'duration', true, $cid);
	// bidding privacy
	$biddingprivacy = $ilance->auction_post->print_bid_privacy('bid_details');
	// public message boards?
	$publicboard = $ilance->auction_post->print_public_board('filter_publicboard');
	// construct countries / states pulldown
	$jscountry = isset($ilance->GPC['filtered_country']) ? $ilance->GPC['filtered_country'] : $ilconfig['registrationdisplay_defaultcountry'];
	$jsstate = isset($ilance->GPC['filtered_state']) ? $ilance->GPC['filtered_state'] : $ilconfig['registrationdisplay_defaultstate'];
	$jscity = isset($ilance->GPC['filtered_city']) ? $ilance->GPC['filtered_city'] : $ilconfig['registrationdisplay_defaultcity'];
	$countryid = fetch_country_id($jscountry, $_SESSION['ilancedata']['user']['slng']);
	$country_js_pulldown = $ilance->common_location->construct_country_pulldown($countryid, $jscountry, 'filtered_country', false, 'filtered_state');
	$state_js_pulldown = '<div id="stateid" style="height:20px">' . $ilance->common_location->construct_state_pulldown($countryid, $jsstate, 'filtered_state') . '</div>';
	unset($jscountry, $jsstate, $jscity);
	// save as draft
	$saveasdraft = '<label for="savedraft"><input type="checkbox" id="savedraft" name="saveasdraft" value="1" ' . $draft . ' /> {_save_this_auction_as_a_draft}</label>';
	// print listing auction enhancements
	$enhancements = $ilance->auction_post->print_listing_enhancements('service');
	// custom insertion fees in this category
	$insertionfees = $ilance->auction_post->print_insertion_fees($cid, 'service', $_SESSION['ilancedata']['user']['userid']);
	// custom budget based insertion fees
	$budgetinsertionfees = $ilance->auction_post->print_budget_insertion_fees($cid);
	// default livefee breakdown
	$currency = print_left_currency_symbol();
	$livefee = ($show['insertionfeeamount'] + $show['selectedbudgetlogic'] + $show['selectedenhancements']);
	$livefee = sprintf("%01.2f", $livefee);
	$ilance->template->fetch('main', 'listing_reverse_auction_create.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$pprint_array = array('videodescriptioncost','description_videourl','transfer_ownership','auctionvisible','category_pulldown','project_state_pulldown','project_details_pulldown','status_pulldown','enhancement_list','date_added','date_starts','tab','hiddenfields','date_end','currency','livefee','paymentmethod','extendauction','inviteoptions','title','additional','keywordinput','budgetinsertionfees','instantpay','js_end','js_start','wysiwyg_area','insertionfees','additionalcategory','listingfees','enhancements','keywords','saveasdraft','maincategory','paymentmethods','attachmentlist','bidfilters','profilebidfilters','publicboard','biddingprivacy','durationbits','auctioneventtype','escrowfilter','budgetfilter','bidtypefilter','attachmentlist','additional_info','description','preview_pane','cid','js','state_js_pulldown','country_js_pulldown','bidamounttype_pulldown','moderationalert','project_questions','uploadbutton','project_title','budget_pulldown','duration','year','month','day','hour','min','sec','invitation','invitationid','country_pulldown','category','subcategory','filehash','max_filesize','attachment_style','user_id','state','catid','subcatid','currency','project_id','input_style');
	
	($apihook = $ilance->api('new_rfp_end')) ? eval($apihook) : false;
	
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### DRAFT AUCTION MANAGEMENT ###############################################
else if ($ilance->GPC['cmd'] == 'management' AND isset($ilance->GPC['sub']) AND $ilance->GPC['sub'] == 'drafts')
{
	// #### define top header nav ##################################################
	$topnavlink = array(
		'mycp'
	);
	$area_title = '{_draft_rfps}';
	$page_title = SITE_NAME . ' - {_draft_rfps}';
	$navcrumb = array();
	$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
	$navcrumb["$ilpage[buying]?cmd=management"] = '{_buying_activity}';
	$navcrumb[""] = '{_draft_auctions}';
	if ($ilconfig['globalauctionsettings_serviceauctionsenabled'] == 0)
	{
		print_notice('{_disabled}', '{_were_sorry_this_feature_is_currently_disabled}', $ilpage['main'], '{_main_menu}');
		exit();
	}
	// check permissions of buyer attemping to post a new service project
	if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'createserviceauctions') == 'no')
	{
		if ($_SESSION['ilancedata']['user']['isadmin'] == '0')
		{
			$area_title = '{_access_denied}';
			$page_title = SITE_NAME . ' - {_access_denied}';
			
			print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource}' . ' <span class="blue"><a href="' . $ilpage['subscription'] . '">{_click_here}</a>.</span>', $ilpage['subscription'], ucwords('{_click_here}'));
			exit();
		}
	}
	$show['widescreen'] = false;
	// #### DELETE DRAFT AUCTION ###################################
	if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
	{
		if (isset($ilance->GPC['rfpcmd']) AND $ilance->GPC['rfpcmd'] == 'deleterfp' AND isset($ilance->GPC['rfp']))
		{
			foreach ($ilance->GPC['rfp'] AS $value)
			{
				$ilance->db->query("
					DELETE FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . intval($value) . "'
						AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						AND status = 'draft'
				", 0, null, __FILE__, __LINE__);
				
			}         
			print_notice('{_action_completed}', '{_the_selected_listings_were_removed}', $ilpage['buying'] . '?cmd=management&sub=drafts', '{_return_to_the_previous_menu}');
			exit();	
		}
	}
	// #### POST DRAFT AUCTION PUBLIC ##############################
	if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == '_do-draft-create' AND isset($ilance->GPC['rfp']))
	{
		$ilance->categories->build_array('service', $_SESSION['ilancedata']['user']['slng'], 0, false);
		foreach ($ilance->GPC['rfp'] AS $value)
		{
			// does admin enable or disable moderation?
			if ($ilconfig['moderationsystem_disableauctionmoderation'])
			{
				$sql = $ilance->db->query("
					SELECT user_id, cid, project_title, description, project_state, project_details, bid_details, date_starts, date_end,  bold, highlite, featured, autorelist, buynow, reserve, featured, autorelist, buynow, reserve, description_videourl, filtered_budgetid, filter_budget, UNIX_TIMESTAMP('" . DATETIME24H . "') - UNIX_TIMESTAMP(date_added) AS seconds
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . intval($value) . "'
				");
				if ($ilance->db->num_rows($sql) > 0)
				{
					$res = $ilance->db->fetch_array($sql, DB_ASSOC);
					$cid1 = $res['cid'];
					// seconds that have past since the listing was posted
					$secondspast = $res['seconds'];
					// fetch the new future date end based on elapsed seconds
					$sqltime = $ilance->db->query("
						SELECT DATE_ADD('$res[date_end]', INTERVAL $secondspast SECOND) AS new_date_end
					");
					$restime = $ilance->db->fetch_array($sqltime, DB_ASSOC);
					if ($res['project_details'] == 'realtime')
					{
						if ($datenow > $res['date_starts'])
						{
							$new_date_start = $datenow;
						}
						else
						{
							$new_date_start = $res['date_starts'];	
						}
					}
					else
					{
						$new_date_start = DATETIME24H;
					}
					$enhancements_invoices = $ilance->db->query("SELECT invoiceid FROM " . DB_PREFIX . "invoices WHERE projectid = '" . $value . "' AND isenhancementfee = '1'");
					$count_enhancements_invoices = $ilance->db->num_rows($enhancements_invoices);
					if ($count_enhancements_invoices == 0)
					{
						if ($res['featured'])
						{
							$ilance->GPC['enhancements']['featured'] = 1;
						}
						if ($res['highlite'])
						{
							$ilance->GPC['enhancements']['highlite'] = 1;
						}
						if ($res['bold'])
						{
							$ilance->GPC['enhancements']['bold'] = 1;
						}
						if ($res['autorelist'])
						{
							$ilance->GPC['enhancements']['autorelist'] = 1;
						}
						if ($res['buynow'] > 0)
						{
							$ilance->GPC['enhancements']['buynow'] = 1;
						}
						if ($res['reserve'] > 0)
						{
							$ilance->GPC['enhancements']['reserve'] = 1;
						}
						$ilance->GPC['description_videourl']  = $res['description_videourl'];
						$ilance->GPC['enhancements'] = (!empty($ilance->GPC['enhancements']) ? $ilance->GPC['enhancements'] : array());
						$enhance = $ilance->auction_fee->process_listing_enhancements_transaction($ilance->GPC['enhancements'], $_SESSION['ilancedata']['user']['userid'], intval($value), 'insert', 'service');
					}
					$insertion_invoices = $ilance->db->query("SELECT invoiceid FROM " . DB_PREFIX . "invoices WHERE projectid = '" . $value . "' AND isif = '1'");
					$count_insertion_invoices = $ilance->db->num_rows($insertion_invoices);
					if ($count_insertion_invoices == 0)
					{		
						// #### INSERTION FEES IN THIS CATEGORY ################
						$insertion = $ilance->auction_fee->process_insertion_fee_transaction($res['cid'], 'service', '', $value, $res['user_id'], $res['filter_budget'], $res['filtered_budgetid']);
						//$ilance->auction_fee->process_listing_duration_transaction($res['cid'], $duration, $duration_unit, $res['user_id'], $value, 'service', false);
					}
					// new date end 
					$new_date_end = $restime['new_date_end'];
					$datenow = DATETIME24H;
					if ($res['project_details'] == 'realtime')
					{
						if ($datenow > $res['date_starts'])
						{
							$new_date_start = $datenow;
						}
						else
						{
							$new_date_start = $res['date_starts'];	
						}
					}
					else
					{
						$new_date_start = DATETIME24H;
					}
					// set auction to open state
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET status = 'open',
						visible = '1',
						date_starts = '" . $ilance->db->escape_string($new_date_start) . "',
						date_end = '" . $ilance->db->escape_string($new_date_end) . "'				
						WHERE project_id = '" . intval($value) . "'
							AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
							AND status = 'draft'
					", 0, null, __FILE__, __LINE__);
					
					($apihook = $ilance->api('buyer_draft_action_validate_foreach')) ? eval($apihook) : false;
					
					// rebuild category count
					$ilance->categories->build_category_count($cid1, 'add', "post draft listing public: adding increment count category id $cid1");
					// send email to auction owner
					$ilance->email->mail = $_SESSION['ilancedata']['user']['email'];
					$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
					$ilance->email->get('new_auction_open_for_bids');		
					$ilance->email->set(array(
						'{{username}}' => $_SESSION['ilancedata']['user']['username'],
						'{{projectname}}' => strip_tags($res['project_title']),
						'{{project_title}}' => strip_tags($res['project_title']),
						'{{description}}' => strip_tags($res['description']),
						'{{bids}}' => '0',
						'{{category}}' => $ilance->categories->recursive($cid1, 'service', $_SESSION['ilancedata']['user']['slng'], 1, '', 0),
						'{{budget}}' => $ilance->auction_rfp->construct_budget_overview($cid1, fetch_auction('filtered_budgetid', intval($value))),
						'{{p_id}}' => intval($value),
						'{{details}}' => ucfirst($res['project_details']),
						'{{privacy}}' => ucfirst($res['bid_details']),
						'{{closing_date}}' => print_date(fetch_auction('date_end', intval($value)), $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
					));
					$ilance->email->send();
					$area_title = '{_new_service_auctions_posted_menu}';
					$page_title = SITE_NAME . ' - {_new_service_auctions_posted_menu}';
					// dispatch email to any service providers the buyer had chose to invite to bid (empty array should fetch all invited users from db instead)
					$ilance->auction_rfp->dispatch_invited_members_email(array(), 'service', fetch_auction('project_id', intval($value)), $_SESSION['ilancedata']['user']['userid']);
					// did this buyer manually enter email addresses to invite users to bid?
					$ilance->auction_rfp->dispatch_external_members_email('service', fetch_auction('project_id', intval($value)), $_SESSION['ilancedata']['user']['userid'], strip_tags(fetch_auction('project_title', intval($value))), fetch_auction('bid_details', intval($value)), fetch_auction('date_end', intval($value)), '', '', $skipemailprocess = 0);
					// #### REFERRAL SYSTEM TRACKER ########
					$ilance->referral->update_referral_action('postauction', $_SESSION['ilancedata']['user']['userid']);
				}
			}
			else
			{
				// moderation enabled place in the rfp queue in admincp and send email to admin
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "projects
					SET status = 'open',
					visible = '0',
					date_starts = '" . DATETIME24H . "'
					WHERE project_id = '" . intval($value) . "'
						AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						AND status = 'draft'
				", 0, null, __FILE__, __LINE__);
				foreach ($ilance->GPC['rfp'] AS $value)
				{
					$ilance->auction_rfp->dispatch_invited_members_email('service', fetch_auction('project_id', intval($value)), $_SESSION['ilancedata']['user']['userid'], strip_tags(fetch_auction('project_title', intval($value))), fetch_auction('bid_details', intval($value)), fetch_auction('date_end', intval($value)), '', '', $skipemailprocess = 1);
				}
			}
		}
		if ($ilconfig['moderationsystem_disableauctionmoderation'])
		{
			// moderation disabled
			$area_title = '{_new_service_auctions_posted_menu}';
			$page_title = SITE_NAME . ' - {_new_service_auctions_posted_menu}';
			$url = '';
			$pprint_array = array('url','session_project_title','session_description','session_additional_info','session_budget','country_pulldown','category','subcategory','filehash','max_filesize','attachment_style','user_id','state','catid','subcatid','currency','datetime_now','project_id','category_id');
			$ilance->template->fetch('main', 'listing_reverse_auction_complete.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}
		else
		{
			// show auction under moderation notice
			$area_title = '{_new_service_auctions_posted_menu}';
			$page_title = SITE_NAME . ' - {_new_service_auctions_posted_menu}';
			$ilance->email->mail = SITE_EMAIL;
			$ilance->email->slng = fetch_site_slng();
			$ilance->email->get('new_auction_pending_moderation');		
			$ilance->email->set(array());
			$ilance->email->send();
			$url = '<a href="' . HTTP_SERVER . $ilpage['buying'] . '?cmd=management&amp;sub=rfp-pending"><strong>{_pending_auctions_menu}</strong></a>';
			$pprint_array = array('url','session_project_title','session_description','session_additional_info','session_budget','country_pulldown','category','subcategory','filehash','max_filesize','attachment_style','user_id','state','catid','subcatid','currency','datetime_now','project_id','category_id');
			$ilance->template->fetch('main', 'listing_reverse_auction_moderation.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}
	}
	// #### DRAFT AUCTION LISTINGS #################################
	$ilance->categories->build_array($cattype = 'service', $_SESSION['ilancedata']['user']['slng'], $categorymode = 0, $propersort = false);
	$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
	$ilconfig['globalfilters_maxrowsdisplay'] = (isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] >= 0)  ? intval($ilance->GPC['pp']) : $ilconfig['globalfilters_maxrowsdisplay'];
	$limit = ' ORDER BY p.date_added DESC LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
	// #### LISTING PERIOD #########################################
	require_once(DIR_CORE . 'functions_search.php');
	require_once(DIR_CORE . 'functions_tabs.php');
	$ilance->GPC['period'] = (isset($ilance->GPC['period']) ? intval($ilance->GPC['period']) : -1);
	$periodsql = fetch_startend_sql($ilance->GPC['period'], 'DATE_SUB', 'p.date_added', '>=');
	$extra = '&amp;period=' . $ilance->GPC['period'];
	$keyw = isset($ilance->GPC['keyw']) ? $ilance->common->xss_clean(handle_input_keywords($ilance->GPC['keyw'])) : '';
	$servicetabs = print_buying_activity_tabs('drafts', 'service', $_SESSION['ilancedata']['user']['userid'], $periodsql);
	$numberrows = $ilance->db->query("
		SELECT p.*, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime
		FROM " . DB_PREFIX . "projects AS p
		WHERE p.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			$periodsql
			AND p.status = 'draft'
			AND p.visible = '1'
	", 0, null, __FILE__, __LINE__);
	$number = $ilance->db->num_rows($numberrows);
	$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
	$condition = $condition2 = '';
	$row_count = 0;
	$result = $ilance->db->query("
		SELECT p.*, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime
		FROM " . DB_PREFIX . "projects AS p
		WHERE p.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			$periodsql
			AND p.status = 'draft'
			AND p.visible = '1'
		$limit
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($result) > 0)
	{
		while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
		{
			// check for auction attachments
			$row['attach'] = '-';                                
			$sql_attachments = $ilance->db->query("
				SELECT attachid, filename, filehash
				FROM " . DB_PREFIX . "attachment
				WHERE project_id = '" . $row['project_id'] . "'
					AND user_id = '" . $row['user_id'] . "'
					AND visible = '1' 
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_attachments) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql_attachments, DB_ASSOC))
				{
					$row['attach'] .= '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'paperclip.gif"><span class="smaller"><a href="' . $ilpage['attachment'] . '?id=' . $res['filehash'] . '" target="_blank">' . $res['filename'] . '</a></span> ';
				}
			}
			$row['added'] = print_date($row['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
			$row['starts'] = print_date($row['date_starts'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
			$row['ends'] = print_date($row['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
			$row['job_title'] = print_string_wrap(stripslashes($row['project_title']), '45');
			$row['type'] = ucfirst($row['project_state']);
			$row['state'] = $row['project_state'];
			$row['description'] = short_string(stripslashes($row['description']), 100);				
			$row['category'] = stripslashes($ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $row['cid']));
			$row['actions'] = '<input type="checkbox" name="rfp[]" value="'.$row['project_id'].'" id="'.$row['project_state'].'_'.$row['project_id'].'" />';
			$row['status'] = '{_pending}';
			$row['revisions'] = $row['updateid'];
			$row['invitecount'] = $ilance->auction_rfp->fetch_invited_users_count($row['project_id']);
			if ($row['insertionfee'] > 0 AND $row['ifinvoiceid'] > 0)
			{
				$row['insfee'] = ($row['isifpaid'])
					? '<div class="smaller blue"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['ifinvoiceid'] . '">(' . $ilance->currency->format($row['insertionfee']) . ')</a></div>'
					: '<div class="smaller red"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['ifinvoiceid'] . '">(' . $ilance->currency->format($row['insertionfee']) . ')</a></div>';
			}
			else
			{
				$row['insfee'] = '-';
			}
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$project_results_rows[] = $row;
			$row_count++;
		}
		$show['no_project_rows_returned'] = false;
		$show['rfppulldownmenu'] = true;
	}
	else
	{
		$show['no_project_rows_returned'] = true;
		$show['rfppulldownmenu'] = false;
	}
	$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $ilpage['buying'].'?cmd=management&amp;sub=drafts');
	$pprint_array = array('servicetabs','rfpvisible','prevnext','redirect','referer','keyw');
	$ilance->template->fetch('main', 'buying_drafts.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', 'project_results_rows');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### ARCHIVED AUCTION MANAGEMENT ############################################
else if ($ilance->GPC['cmd'] == 'management' AND isset($ilance->GPC['sub']) AND $ilance->GPC['sub'] == 'archived')
{
	// #### define top header nav ##################################################
	$topnavlink = array(
		'mycp'
	);
	$show['widescreen'] = false;
	$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
	$ilconfig['globalfilters_maxrowsdisplay'] = (isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] >= 0)  ? intval($ilance->GPC['pp']) : $ilconfig['globalfilters_maxrowsdisplay'];
	$limit = ' ORDER BY p.date_added DESC LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
	$keyw = isset($ilance->GPC['keyw']) ? $ilance->common->xss_clean(handle_input_keywords($ilance->GPC['keyw'])) : '';
	// #### LISTING PERIOD #########################################
	require_once(DIR_CORE . 'functions_search.php');
	require_once(DIR_CORE . 'functions_tabs.php');                
	$ilance->GPC['period'] = (isset($ilance->GPC['period']) ? intval($ilance->GPC['period']) : -1);
	$periodsql = fetch_startend_sql($ilance->GPC['period'], 'DATE_SUB', 'p.date_added', '>=');
	$extra = '&amp;period=' . $ilance->GPC['period'];
	$servicetabs = print_buying_activity_tabs('archived', 'service', $_SESSION['ilancedata']['user']['userid'], $periodsql);
	$numberrows = $ilance->db->query("
		SELECT p.*, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime
		FROM " . DB_PREFIX . "projects AS p
		WHERE p.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			$periodsql
			AND p.project_state = 'service'
			AND p.status = 'archived'
			AND p.visible = '1' 
			AND p.project_title like '%" . $ilance->db->escape_string($keyw) . "%'
	", 0, null, __FILE__, __LINE__);
	$number = $ilance->db->num_rows($numberrows);
	$area_title = '{_archived_rfps}';
	$page_title = SITE_NAME . ' - {_archived_rfps}';
	$navcrumb = array();
	$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
	$navcrumb["$ilpage[buying]?cmd=management"] = '{_buying_activity}';
	$navcrumb[""] = '{_archived_auctions}';
	$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
	$row_count = 0;
	$condition = $condition2 = '';
	$result = $ilance->db->query("
		SELECT p.*, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime
		FROM " . DB_PREFIX . "projects AS p
		WHERE p.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			$periodsql
			AND p.project_state = 'service'
			AND p.status = 'archived'
			AND p.visible = '1'
			AND p.project_title like '%" . $ilance->db->escape_string($keyw) . "%'
		$limit
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($result) > 0)
	{
		while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
		{
			$row['provider'] = $row['work'] = $row['invoice'] = $row['feedback'] = '-';
			$row['pmb'] = '';
			$row['job_title'] = print_string_wrap(stripslashes($row['project_title']), '45');
			$row['description'] = short_string(stripslashes($row['description']), 100);
			$row['state'] = ucfirst($row['project_state']);
			if ($row['bids'] == 0)
			{
				$row['bids'] = '-';
			}
			if ($row['views'] == 0)
			{
				$row['views'] = '-';
			}
			if ($row['status'] != 'closed')
			{
				$dif = $row['mytime'];
				$ndays = floor($dif / 86400);
				$dif -= $ndays * 86400;
				$nhours = floor($dif / 3600);
				$dif -= $nhours * 3600;
				$nminutes = floor($dif / 60);
				$dif -= $nminutes * 60;
				$nseconds = $dif;				
				$sign = '+';
				if ($row['mytime'] < 0)
				{
					$row['mytime'] = - $row['mytime'];
					$sign = '-';
				}
				if ($sign == '-')
				{
					$project_time_left = '<span class="gray">{_ended}</span>';
				}
				else
				{
					if ($ndays != '0')
					{
						$project_time_left = $ndays . '{_d_shortform}'.', ';
						$project_time_left .= $nhours . '{_h_shortform}' . '+';
					}
					elseif ($nhours != '0')
					{
						$project_time_left = $nhours . '{_h_shortform}' . ', ';
						$project_time_left .= $nminutes . '{_m_shortform}' . '+';
					}
					else
					{
						$project_time_left = $nminutes . '{_m_shortform}' . ', ';
						$project_time_left .= $nseconds . '{_s_shortform}' . '+';
					}
				}
				
				$row['timeleft'] = $project_time_left;
			}
			else
			{
				$project_time_left = '-';
			}
			$bidgrouping = $ilance->categories->bidgrouping($row['cid']);
			$bids_table = ($bidgrouping) ? "project_bids" : "project_realtimebids";
			$sql_provider = $ilance->db->query("
				SELECT user_id
				FROM " . DB_PREFIX . $bids_table . "
				WHERE project_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND project_id = '" . $row['project_id'] . "'
					AND bidstatus = 'awarded'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_provider) > 0)
			{
				$res_provider = $ilance->db->fetch_array($sql_provider, DB_ASSOC);
				
				$row['provider'] = print_username($res_provider['user_id']);
			}
			$row['pmb'] = '<div align="center"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_gray.gif" border="0" alt="" /></div>';
			$row['work'] = '<div align="center"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/share_gray.gif" border="0" alt="" /></div>';
			$row['invoice'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/invoice_gray.gif" border="0" alt="" />';    	
			$row['feedback'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_gray.gif" border="0" alt="{_please_pay_your_fvf_for_this_listing_before_leaving_feedback}" /></a></span></div>';
			$row['timeleft'] = $project_time_left;
			$row['actions'] = '<input type="checkbox" name="rfp[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" disabled="disabled" />';
			$row['status'] = '{_archived}';
			if ($row['insertionfee'] > 0 AND $row['ifinvoiceid'] > 0)
			{
				$row['insfee'] = ($row['isifpaid'])
					? '<div class="smaller blue"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['ifinvoiceid'] . '">(' . $ilance->currency->format($row['insertionfee']) . ')</a></div>'
					: '<div class="smaller red"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['ifinvoiceid'] . '">(' . $ilance->currency->format($row['insertionfee']) . ')</a></div>';
			}
			else
			{
				$row['insfee'] = '-';
			}
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$project_results_rows[] = $row;
			$row_count++;
		}
		$show['no_project_rows_returned'] = false;
	}
	else
	{
		$show['no_project_rows_returned'] = true;
	}
	$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $ilpage['buying'].'?cmd=management&amp;sub=archived');
	$pprint_array = array('servicetabs','rfpvisible','prevnext','redirect','referer','keyw');
	$ilance->template->fetch('main', 'buying_archived.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', 'project_results_rows');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### DELISTED AUCTION MANAGEMENT ############################################
else if ($ilance->GPC['cmd'] == 'management' AND isset($ilance->GPC['sub']) AND $ilance->GPC['sub'] == 'delisted')
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp'
	);
	$show['widescreen'] = false;
	$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
	$ilconfig['globalfilters_maxrowsdisplay'] = (isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] >= 0)  ? intval($ilance->GPC['pp']) : $ilconfig['globalfilters_maxrowsdisplay'];
	$limit = ' ORDER BY p.date_added DESC LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
	// #### LISTING PERIOD #########################################
	require_once(DIR_CORE . 'functions_search.php');
	require_once(DIR_CORE . 'functions_tabs.php');
	$ilance->GPC['period'] = (isset($ilance->GPC['period']) ? intval($ilance->GPC['period']) : -1);
	$periodsql = fetch_startend_sql($ilance->GPC['period'], 'DATE_SUB', 'p.date_added', '>=');
	$extra = '&amp;period=' . $ilance->GPC['period'];
	$servicetabs = print_buying_activity_tabs('delisted', 'service', $_SESSION['ilancedata']['user']['userid'], $periodsql);
	$numberrows = $ilance->db->query("
		SELECT p.*, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime
		FROM " . DB_PREFIX . "projects AS p
		WHERE p.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			$periodsql
			AND p.project_state = 'service'
			AND p.status = 'delisted'
			AND p.visible = '1' 
	", 0, null, __FILE__, __LINE__);
	$number = $ilance->db->num_rows($numberrows);
	$area_title = '{_delisted_rfps}';
	$page_title = SITE_NAME . ' - {_delisted_rfps}';
	$navcrumb = array();
	$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
	$navcrumb["$ilpage[buying]?cmd=management"] = '{_buying_activity}';
	$navcrumb[""] = '{_delisted_auctions}';
	$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
	$row_count = 0;
	$condition = $condition2 = '';
	$result = $ilance->db->query("
		SELECT p.*, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime
		FROM " . DB_PREFIX . "projects AS p
		WHERE p.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			$periodsql
			AND p.project_state = 'service'
			AND p.status = 'delisted'
			AND p.visible = '1'
		$limit 
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($result) > 0)
	{
		while ($row = $ilance->db->fetch_array($result))
		{
			// check for attachments
			$row['attach'] = '-';
			$sql_attachments = $ilance->db->query("
				SELECT attachid, filename, filehash
				FROM " . DB_PREFIX . "attachment
				WHERE attachtype = 'project'
					AND project_id = '" . $row['project_id'] . "'
					AND user_id = '" . $row['user_id'] . "'
					AND visible = '1' 
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_attachments) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql_attachments))
				{
					$row['attach'] .= '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'paperclip.gif"><span class="smaller"><a href="' . $ilpage['attachment'] . '?id=' . $res['filehash'] . '" target="_blank">' . $res['filename'] . '</a></span> ';
				}
			}
			$row['provider'] = $row['pmb'] =  '';
			$row['work'] = $row['invoice'] = $row['feedback'] = '-';
			$row['state'] = ucfirst($row['project_state']);
			$row['job_title'] = print_string_wrap(stripslashes($row['project_title']), '45');
			$row['description'] = short_string(stripslashes($row['description']), 100);
			if ($row['status'] != 'closed')
			{
				$dif = $row['mytime'];
				$ndays = floor($dif / 86400);
				$dif -= $ndays * 86400;
				$nhours = floor($dif / 3600);
				$dif -= $nhours * 3600;
				$nminutes = floor($dif / 60);
				$dif -= $nminutes * 60;
				$nseconds = $dif;
				$sign = '+';
				if ($row['mytime'] < 0)
				{
					$row['mytime'] = - $row['mytime'];
					$sign = '-';
				}
				if ($sign == '-')
				{
					$project_time_left = '<span class="gray">{_ended}</span>';
				}
				else
				{
					if ($ndays != '0')
					{
						$project_time_left = $ndays . '{_d_shortform}' . ', ';
						$project_time_left .= $nhours . '{_h_shortform}' .'+';
					}
					elseif ($nhours != '0')
					{
						$project_time_left = $nhours . '{_h_shortform}' . ', ';
						$project_time_left .= $nminutes . '{_m_shortform}' . '+';
					}
					else
					{
						$project_time_left = $nminutes . '{_m_shortform}' . ', ';
						$project_time_left .= $nseconds . '{_s_shortform}'. '+';
					}
				}
				$row['timeleft'] = $project_time_left;
			}
			else
			{
				$project_time_left = '-';
			}
			$row['timeleft'] = $project_time_left;
			$row['actions'] = '<input type="checkbox" name="rfp[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" disabled="disabled" />';
			$row['status'] = '{_delisted}';
			if ($row['insertionfee'] > 0 AND $row['ifinvoiceid'] > 0)
			{
				$row['insfee'] = ($row['isifpaid'])
					? '<div class="smaller blue"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['ifinvoiceid'] . '">(' . $ilance->currency->format($row['insertionfee']) . ')</a></div>'
					: '<div class="smaller red"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['ifinvoiceid'] . '">(' . $ilance->currency->format($row['insertionfee']) . ')</a></div>';
			}
			else
			{
				$row['insfee'] = '-';
			}
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$project_results_rows[] = $row;
			$row_count++;
		}
		$show['no_project_rows_returned'] = false;
	}
	else
	{
		$show['no_project_rows_returned'] = true;
	}
	$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $ilpage['buying'].'?cmd=management&amp;sub=delisted');
	$pprint_array = array('servicetabs','rfpvisible','prevnext','redirect','referer','keyw');
	$ilance->template->fetch('main', 'buying_delisted.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', 'project_results_rows');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### AUCTION PENDING BY ADMIN ###############################################
// additionally auctions that have not paid the entire insertion fee will also be listed below with their payment status
else if ($ilance->GPC['cmd'] == 'management' AND isset($ilance->GPC['sub']) AND $ilance->GPC['sub'] == 'rfp-pending')
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp'
	);
	$show['widescreen'] = false;
	$ilance->categories->build_array($cattype = 'service', $_SESSION['ilancedata']['user']['slng'], $categorymode = 0, $propersort = false);
	$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
	$ilconfig['globalfilters_maxrowsdisplay'] = (isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] >= 0)  ? intval($ilance->GPC['pp']) : $ilconfig['globalfilters_maxrowsdisplay'];
	$limit = 'ORDER BY p.date_added DESC LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
	// #### LISTING PERIOD #########################################
	require_once(DIR_CORE . 'functions_search.php');
	require_once(DIR_CORE . 'functions_tabs.php');
	$ilance->GPC['period'] = (isset($ilance->GPC['period']) ? intval($ilance->GPC['period']) : -1);
	$periodsql = fetch_startend_sql($ilance->GPC['period'], 'DATE_SUB', 'p.date_added', '>=');
	$extra = '&amp;period=' . $ilance->GPC['period'];
	$servicetabs = print_buying_activity_tabs('rfp-pending', 'service', $_SESSION['ilancedata']['user']['userid'], $periodsql);
	$numberrows = $ilance->db->query("
		SELECT p.*, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime
		FROM " . DB_PREFIX . "projects AS p
		WHERE p.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			$periodsql
			AND p.project_state = 'service'
			AND p.status != 'archived'
			" . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.visible = '0' OR p.status = 'frozen' OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '0') OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '0'))" : "AND p.visible = '0'") . " 
	", 0, null, __FILE__, __LINE__);
	$number = $ilance->db->num_rows($numberrows);
	$area_title = '{_pending_auctions}';
	$page_title = SITE_NAME . ' - {_pending_auctions}';
	$navcrumb = array();
	$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
	$navcrumb["$ilpage[buying]?cmd=management"] = '{_buying_activity}';
	$navcrumb[""] = '{_pending_auctions}';
	$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
	$row_count = 0;
	$condition =$condition2 = '';
	$result = $ilance->db->query("
		SELECT p.*, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime
		FROM " . DB_PREFIX . "projects AS p
		WHERE p.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			$periodsql
			AND p.project_state = 'service'
			AND p.status != 'archived'
			" . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.visible = '0' OR p.status = 'frozen' OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '0') OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '0'))" : "AND p.visible = '0'") . " 
		$limit
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($result) > 0)
	{
		while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
		{
			$row['job_title'] = print_string_wrap(stripslashes($row['project_title']), '45');
			$row['description'] = short_string(stripslashes($row['description']), 100);
			$row['category'] = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $row['cid']);
			$row['actions'] = '<input type="checkbox" name="rfp[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" disabled="disabled" />';
			$row['state'] = ucfirst($row['project_state']);
			if (($row['insertionfee'] > 0 AND $row['ifinvoiceid'] > 0) OR ($row['enhancementfee'] > 0 AND $row['enhancementfeeinvoiceid'] > 0))
			{
				$row['insfee'] = ($row['isifpaid'])
					? '<div class="smaller blue"><a href="' . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['ifinvoiceid'] . '">(' . $ilance->currency->format($row['insertionfee']) . ')</a></div>'
					: '<div class="smaller red">(' . $ilance->currency->format($row['insertionfee']) . ')</div>';
					
				$row['status'] = ($row['isifpaid'] AND $row['isenhancementfeepaid'])
					? '{_pending_payment}'
					: '{_review_in_progress}';
				
				$row['paystatus'] = ($row['isifpaid'] AND $row['isenhancementfeepaid'])
					? '{_paid}'
					: '<a href="' . HTTPS_SERVER . $ilpage['accounting'] .'?cmd=transactions&amp;subcmd=directmasspayment&amp;projectid='.$row['project_id'].'" class="buttons"><strong>{_pay_now}</strong></a>';
			}
			else
			{
				$row['insfee'] = $row['paystatus'] = '-';
				$row['status'] = '{_review_in_progress}';
			}
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$project_results_rows[] = $row;
			$row_count++;
		}
		$show['no_project_rows_returned'] = false;
	}
	else
	{
		$show['no_project_rows_returned'] = true;
	}
	$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $ilpage['buying'].'?cmd=management&amp;sub=rfp-pending');
	$pprint_array = array('servicetabs','rfpvisible','prevnext','redirect','referer','keyw');
	$ilance->template->fetch('main', 'buying_rfp_pending.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', 'project_results_rows');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// ### RETRACT BID PREVIEW #####################################################
else if ($ilance->GPC['cmd'] == 'management' AND $ilance->GPC['subcmd'] == 'bidretract' AND isset($ilance->GPC['bid']) AND $ilance->GPC['bid'] > 0 AND isset($ilance->GPC['pid']) AND $ilance->GPC['pid'] > 0)
{
	$area_title = '{_retract_your_bid}';
	$page_title = SITE_NAME . ' - {_retract_your_bid}';
	$navcrumb = array();
	$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
	$navcrumb["$ilpage[buying]?cmd=management"] = '{_buying_activity}';
	$navcrumb[""] = '{_retract_bid}';
	// is listing valid?
	$sql = $ilance->db->query("
		SELECT project_title, cid, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP('" . DATETIME24H . "') - UNIX_TIMESTAMP(date_starts) AS starttime, date_starts, currencyid
		FROM " . DB_PREFIX . "projects
		WHERE project_id = '" . intval($ilance->GPC['pid']) . "'
			AND project_state = 'product'
			AND visible = '1'
		LIMIT 1
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) == 0)
	{
		print_notice('{_cannot_retract_bid}', '{_invalid_listing_perform_action}', $ilpage['buying'] . '?cmd=management', '{_buying_activity}');
		exit();
	}
	$res = $ilance->db->fetch_array($sql, DB_ASSOC);
	// is bid being retracted owned by this user?
	if ($ilance->bid->fetch_bid_owner(intval($ilance->GPC['bid'])) != $_SESSION['ilancedata']['user']['userid'])
	{
		print_notice('{_access_denied}', '{_you_cannot_retract_bid_that_not_owner}', $ilpage['buying'] . '?cmd=management', '{_buying_activity}');
		exit();
	}
	// is user the highest bidder of this listing?
	if ($ilance->bid->fetch_highest_bidder(intval($ilance->GPC['pid'])) != $_SESSION['ilancedata']['user']['userid'])
	{
		print_notice('{_cannot_retract_bid}', '{_you_cannot_retract_bid_if_not_highest_bidder}', $ilpage['buying'] . '?cmd=management', '{_buying_activity}');
		exit();
	}
	// does user have any bid retracts left for this month?
	$bidretractstotal = $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'bidretracts');
	$bidretracts = fetch_user('bidretracts', $_SESSION['ilancedata']['user']['userid']);
	$bidretractsleft = ($bidretractstotal - $bidretracts);
	if ($bidretractstotal > 0 AND $bidretracts >= $bidretractstotal)
	{
		print_notice('{_maximum_bid_retracts_used_this_month}', $ilance->language->construct_phrase('{_sorry_you_have_used_the_total_number_of_bid_retractions_for_your_subscription}', $bidretractstotal), $ilpage['buying'] . '?cmd=management', '{_buying_activity}');
		exit();
	}
	$project_title = handle_input_keywords(stripslashes($res['project_title']));
	$timeleft = $ilance->auction->auction_timeleft(true, $res['date_starts'], $res['mytime'], $res['starttime']);
	$project_id = intval($ilance->GPC['pid']);
	$bid_id = intval($ilance->GPC['bid']);
	$bidamount = $ilance->currency->format($ilance->db->fetch_field(DB_PREFIX . "project_bids", "bid_id = '" . intval($ilance->GPC['bid']) . "'", 'bidamount'), $res['currencyid']);
	$otherbidders = $ilance->bid->print_other_bidders_verbose($_SESSION['ilancedata']['user']['userid'], $ilance->GPC['pid']);
	$returnurl = urlencode($ilance->GPC['returnurl']);
	$pprint_array = array('project_title', 'timeleft', 'project_id', 'bid_id', 'otherbidders', 'bidamount', 'bidretractsleft', 'returnurl');
	$ilance->template->fetch('main', 'buying_activity_retractbid.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### INCREASE MAX BID PREVIEW #################################################
else if ($ilance->GPC['cmd'] == 'management' AND $ilance->GPC['subcmd'] == 'increasemaxbid' AND isset($ilance->GPC['pid']) AND $ilance->GPC['pid'] > 0)
{
	$area_title = '{_increase_my_max_bid}';
	$page_title = SITE_NAME . ' - {_increase_my_max_bid}';
	$navcrumb = array();
	$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
	$navcrumb["$ilpage[buying]?cmd=management"] = '{_buying_activity}';
	$navcrumb[""] = '{_increase_proxy_bid}';
	// is listing valid?
	$sql = $ilance->db->query("
		SELECT project_title, cid, currencyid, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP('" . DATETIME24H . "') - UNIX_TIMESTAMP(date_starts) AS starttime, date_starts
		FROM " . DB_PREFIX . "projects
		WHERE project_id = '" . intval($ilance->GPC['pid']) . "'
			AND project_state = 'product'
			AND visible = '1'
		LIMIT 1
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) == 0)
	{
		print_notice('{_access_denied}', '{_invalid_listing_perform_action}', $ilpage['buying'] . '?cmd=management', '{_buying_activity}');
		exit();
	}
	$res = $ilance->db->fetch_array($sql, DB_ASSOC);
	// is proxy bidding enabled?
	if ($ilconfig['productbid_enableproxybid'] == 0 OR $ilance->categories->useproxybid($_SESSION['ilancedata']['user']['slng'], $res['cid']) == 0)
	{
		print_notice('{_access_denied}', '{_proxy_bidding_not_supported_for_category}', $ilpage['buying'] . '?cmd=management', '{_buying_activity}');
		exit();
	}
	// has user placed a bid before?
	if ($ilance->bid->is_bid_placed(intval($ilance->GPC['pid']), $_SESSION['ilancedata']['user']['userid']) == false)
	{
		print_notice('{_access_denied}', '{_in_order_place_max_proxy_bid_first}', $ilpage['buying'] . '?cmd=management', '{_buying_activity}');
		exit();
	}
	$project_title = handle_input_keywords(stripslashes($res['project_title']));
	$timeleft = $ilance->auction->auction_timeleft(true, $res['date_starts'], $res['mytime'], $res['starttime']);
	$project_id = intval($ilance->GPC['pid']);
	$maxbidamount = $ilance->currency->format($ilance->bid_proxy->fetch_highest_proxy_bid(intval($ilance->GPC['pid']), $_SESSION['ilancedata']['user']['userid']), $res['currencyid']);
	$otherbidders = $ilance->bid->print_other_bidders_verbose($_SESSION['ilancedata']['user']['userid'], intval($ilance->GPC['pid']));
	$returnurl = urlencode($ilance->GPC['returnurl']);
	$pprint_array = array('project_title', 'timeleft', 'project_id', 'otherbidders', 'maxbidamount', 'returnurl');
	$ilance->template->fetch('main', 'buying_activity_increasemaxbid.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### BID ACTIONS ############################################################
else if ($ilance->GPC['cmd'] == '_do-bid-action')
{
	// #### RETRACT SINGLE BID #############################################
	if (isset($ilance->GPC['bidcmd']) AND $ilance->GPC['bidcmd'] == 'retract')
	{
		if (isset($ilance->GPC['bidid']) AND $ilance->GPC['bidid'] > 0)
		{
			$sql = $ilance->db->query("
				SELECT project_id, bidstatus, user_id, state
				FROM " . DB_PREFIX . "project_bids
				WHERE bid_id = '" . intval($ilance->GPC['bidid']) . "'
					AND bidstate != 'retracted' 
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$res['reason'] = (!empty($ilance->GPC['bidretractreason'])) ? handle_input_keywords($ilance->GPC['bidretractreason']) : '{_no_reason_provided}';
				$res['isawarded'] = ($res['bidstatus'] == 'awarded') ? true : false;
				if ($res['state'] == 'service')
				{
					if ($ilconfig['servicebid_bidretract'])
					{
						$ilance->bid_retract->construct_service_bid_retraction($res['user_id'], intval($ilance->GPC['bidid']), $res['project_id'], $res['reason'], $res['isawarded'], true);
					}
					else
					{
						print_notice('{_disabled}', '{_were_sorry_this_feature_is_currently_disabled}', $ilpage['main'], '{_main_menu}');
						exit();
					}
				}
				else if ($res['state'] == 'product')
				{
					if ($ilconfig['productbid_bidretract'])
					{
						$ilance->bid_retract->construct_product_bid_retraction($res['user_id'], intval($ilance->GPC['bidid']), $res['project_id'], $res['reason'], $res['isawarded'], true);
					}
					else
					{
						print_notice('{_disabled}', '{_were_sorry_this_feature_is_currently_disabled}', $ilpage['main'], '{_main_menu}');
						exit();
					}
				}
				$totalretracts = $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'bidretracts');
				print_notice('{_bid_retracted}', $ilance->language->construct_phrase('{_you_have_retracted_your_bid_from_this_auction_please_remember_your_current_subscription_level_provides_x_bid_retracts}', $totalretracts), urldecode($ilance->GPC['returnurl']), '{_return_to_the_previous_menu}');
				exit();
			}
			else
			{
				$area_title = '{_cannot_retract_bid}';
				$page_title = SITE_NAME . ' - {_cannot_retract_bid}';
				print_notice('{_cannot_retract_bid}', '{_it_appears_already_retracted}', urldecode($ilance->GPC['returnurl']), '{_return_to_the_previous_menu}');
				exit();
			}
		}
		else
		{
			$area_title = '{_cannot_retract_bid}';
			$page_title = SITE_NAME . ' - {_cannot_retract_bid}';
			print_notice('{_cannot_retract_bid}', '{_you_did_not_select_a_valid_bid_to_retract_please_try_again_from_the_previous_menu}', urldecode($ilance->GPC['returnurl']), '{_return_to_the_previous_menu}');
			exit();
		}
	}
	// #### INCREASE MAX BID ###############################################
	else if (isset($ilance->GPC['bidcmd']) AND $ilance->GPC['bidcmd'] == 'increasemaxbid')
	{
		if (isset($ilance->GPC['pid']) AND $ilance->GPC['pid'] > 0 AND isset($ilance->GPC['maxbid']) AND $ilance->GPC['maxbid'] > 0)
		{
			$sql = $ilance->db->query("
				SELECT id, maxamount
				FROM " . DB_PREFIX . "proxybid
				WHERE project_id = '" . intval($ilance->GPC['pid']) . "'
					AND user_id = '" . intval($_SESSION['ilancedata']['user']['userid']) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				if ($res['maxamount'] >= $ilance->GPC['maxbid'])
				{
					$area_title = '{_cannot_increase_max_bid}';
					$page_title = SITE_NAME . ' - {_cannot_increase_max_bid}';
					print_notice('{_cannot_increase_max_bid}', '{_it_appears_entered_less_than_current_max_bid}', urldecode($ilance->GPC['returnurl']), '{_return_to_the_previous_menu}');
					exit();
				}
				else
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "proxybid
						SET maxamount = '" . $ilance->db->escape_string($ilance->GPC['maxbid']) . "',
						date_added = '" . DATETIME24H . "'
						WHERE project_id = '" . intval($ilance->GPC['pid']) . "'
							AND user_id = '" . intval($_SESSION['ilancedata']['user']['userid']) . "'
						LIMIT 1
					");
					print_notice('{_maximum_bid_amount_updated}', '{_you_have_successfully_updated_max_bid}', urldecode($ilance->GPC['returnurl']), '{_return_to_the_previous_menu}');
					exit();
				}
			}
			else
			{
				$area_title = '{_cannot_increase_max_bid}';
				$page_title = SITE_NAME . ' - {_cannot_increase_max_bid}';
				print_notice('{_cannot_increase_max_bid}', '{_it_appears_you_have_not_placed_bid_yet}', urldecode($ilance->GPC['returnurl']), '{_return_to_the_previous_menu}');
				exit();
			}
		}
		else
		{
			$area_title = '{_cannot_increase_max_bid}';
			$page_title = SITE_NAME . ' - {_cannot_increase_max_bid}';
			print_notice('{_cannot_increase_max_bid}', '{_it_appears_you_are_trying_to_increase_max_bid}', urldecode($ilance->GPC['returnurl']), '{_return_to_the_previous_menu}');
			exit();
		}
	}
}
// #### AUCTION ACTIONS ########################################################
else if ($ilance->GPC['cmd'] == '_do-rfp-action' AND isset($ilance->GPC['rfpcmd']) AND empty($ilance->GPC['bidcmd']))
{
	// #### define top header nav ##########################################
	$topnavlink = array(
		'mycp'
	);
	// #### empty inline cookie ############################################
	set_cookie('inlineservice', '', false);
	// #### ARCHIVE ########################################################
	if (isset($ilance->GPC['rfpcmd']) AND $ilance->GPC['rfpcmd'] == 'archive' AND !empty($ilance->GPC['rfp']) AND is_array($ilance->GPC['rfp']))
	{
		if (count($ilance->GPC['rfp']) > 0)
		{
			// empty inline cookie
			set_cookie('inlineservice', '', false);
			for ($i = 0; $i < count($ilance->GPC['rfp']); $i++)
			{
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "projects
					SET status = 'archived'
					WHERE project_id = '" . intval($ilance->GPC['rfp'][$i]) . "'
						AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						AND (status = 'closed' OR status = 'expired' OR status = 'finished')
						AND visible = '1'
				", 0, null, __FILE__, __LINE__);
				$sqlupd = $ilance->db->query("
					SELECT id
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . intval($ilance->GPC['rfp'][$i]) . "'
						AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						AND status = 'archived'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sqlupd) == 0)
				{
					$area_title = '{_archive_rfp_error_rfp_in_progress}';
					$page_title = SITE_NAME . ' - {_archive_rfp_error_rfp_in_progress}';
					print_notice($area_title, '{_one_of_the_requested_rfps_you_are_trying_to_archive}', $ilpage['buying'], '{_return_to_the_previous_menu}');
				}
			}
			$area_title = '{_rfps_archive_display}';
			$page_title = SITE_NAME . ' - {_rfps_archive_display}';
			print_notice('{_requested_rfps_have_been_archived}', '{_you_will_now_be_able_to_review_these_rfps_from_your_archived_rfps_menu}', $ilpage['buying'], '{_return_to_the_previous_menu}');
		}
		else
		{
			$area_title = '{_invalid_items_selected}';
			$page_title = SITE_NAME . ' - {_invalid_items_selected}';
			print_notice($area_title, '{_your_requested_rfp_control_action_cannot_be_completed_because_one_or_more_rfps}' . '</p><p>{_when_an_rfp_is_in_award_phase_rfp_control_features_such_as_archive}</p>', $ilpage['buying'], '{_return_to_the_previous_menu}');
		}
	}
	// #### CANCEL #########################################################
	else if (isset($ilance->GPC['rfpcmd']) AND $ilance->GPC['rfpcmd'] == 'cancel' AND !empty($ilance->GPC['rfp']))
	{
		if ($ilconfig['globalfilters_enablerfpcancellation'])
		{
			if (count($ilance->GPC['rfp']) > 0)
			{
				// empty inline cookie
				set_cookie('inlineservice', '', false);
				for ($i = 0; $i < count($ilance->GPC['rfp']); $i++)
				{
					$delist_msg = '{_delisted_by} ' . $_SESSION['ilancedata']['user']['username'] . ' [' . print_date(DATETIME24H, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0) . ']';
					$presql = $ilance->db->query("
						SELECT cid
						FROM " . DB_PREFIX . "projects
						WHERE project_id = '" . intval($ilance->GPC['rfp'][$i]) . "'
							AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
							AND (status = 'open' OR status = 'expired')
							AND visible = '1'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($presql) > 0)
					{
						$auctioninfo = $ilance->db->fetch_array($presql, DB_ASSOC);
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "projects
							SET status = 'delisted'
							WHERE project_id = '" . intval($ilance->GPC['rfp'][$i]) . "'
								AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						", 0, null, __FILE__, __LINE__);
						$ilance->categories->build_category_count($auctioninfo['cid'], 'subtract', "buyer cancel service listing: subtracting increment count category id $auctioninfo[cid]");
					}
					else
					{
						$area_title = '{_cancel_rfp_error_rfp_in_progress}';
						$page_title = SITE_NAME . ' - {_cancel_rfp_error_rfp_in_progress}';
						print_notice($area_title, '{_your_requested_rfp_control_action_cannot_be_completed_because_one_or_more_rfps}'.'</p><p>'.'{_when_an_rfp_is_in_award_phase_rfp_control_features_such_as_archive}'.'</p>', $ilpage['buying'], '{_return_to_the_previous_menu}');
					}
				}
				$area_title = '{_requested_rfps_have_been_cancelled}';
				$page_title = SITE_NAME . ' - {_auctions_have_been_cancelled}';
				print_notice($area_title, '{_you_have_successfully_delisted_cancelled_one_or_more_auctions_from_your_buying_activity_menu_no_more_bids_can_be_placed}' . '</p><p>{_you_will_now_be_able_to_review_these_delisted_rfps_from_your} <a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=delisted">{_delisted_auctions}</a> {_menu}</p>', $ilpage['buying'], '{_return_to_the_previous_menu}');
			}
			else
			{
				$area_title = '{_invalid_items_selected}';
				$page_title = SITE_NAME . ' - {_cancel_rfp_error_rfp_in_progress}';
				print_notice($area_title, '{_your_requested_rfp_control_action_cannot_be_completed_because_one_or_more_rfps}'.'</p><p>'.'{_when_an_rfp_is_in_award_phase_rfp_control_features_such_as_archive}'.'</p>', $ilpage['buying'], '{_return_to_the_previous_menu}');
			}
		}
		else
		{
			$area_title = '{_access_denied_cancel_rfp_feature_currently_disabled}';
			$page_title = SITE_NAME . ' - {_access_denied_cancel_rfp_feature_currently_disabled}';
			print_notice('{_access_to_feature_denied}', '{_were_sorry_this_feature_is_currently_disabled}'."<br /><br />".'{_please_contact_customer_support}', $ilpage['main'], '{_main_menu}');
			exit();
		}
	}
	// #### PROCESS AUCTION RELISTER #######################################
	else if ($ilance->GPC['rfpcmd'] == 'relist')
	{
		foreach ($ilance->GPC['rfp'] AS $key => $value)
		{
			$success = $ilance->db->query("UPDATE " . DB_PREFIX . "projects SET autorelist = '1', autorelist_date = '0000-00-00 00:00:00' WHERE project_id = '" . intval($value) . "' AND bids = '0' AND status='expired' AND user_id='".$_SESSION['ilancedata']['user']['userid']."'", 0, null, __FILE__, __LINE__);
			if ($success)
			{
				$ilance->auction_expiry->process_auction_relister($value, $dontsendemail = true);
			}
		}
		refresh(HTTP_SERVER . $ilpage['buying'] . '?cmd=management&sub=ended');
		exit();
	}
	// #### RELIST PRODUCT AUCTIONS ########################################
	else if ($ilance->GPC['rfpcmd'] == 'relist_sel' OR $ilance->GPC['rfpcmd'] == 'relist_all')
	{
		$col = '<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" width="100%">';
		$sum_insertion_fees = $insertion_fees = $insertion_fees_tax = $sum_insertion_fees_tax = $i = $sum_enhancement_fees = $enhancement_fees = $enhancement_fees_tax = $sum_enhancement_fees_tax = 0;
		$hidden = '';
		if ($ilance->GPC['rfpcmd'] == 'relist_all')
		{
			$ilance->GPC['rfp'] = array();
			$sql_all = $ilance->db->query("
				SELECT project_id
				FROM " . DB_PREFIX . "projects 
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' 
					AND visible = '1' 
					AND status = 'expired' 
					AND project_state = 'service'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_all) > 0)
			{
				$j = 0;
				while($res_all = $ilance->db->fetch_array($sql_all, DB_ASSOC))
				{
					$ilance->GPC['rfp'][$j] = $res_all['project_id'];
					$j++;
				}
			}
		}
		else 
		{
			$col .= '<tr class="featured_highlight" valign="top">';
			$col .= '<td><div>{_title}</div></td>';
			$col .= '<td><div>{_category}</div></td>';
			$col .= '<td><div>{_insertion_fee}</div></td>';
			$col .= '<td><div>{_upsell_fee}</div></td>';
			$col .= '<td><div>{_total}</div></td>';
			$col .= '</tr>';
		}
		foreach ($ilance->GPC['rfp'] AS $key => $value)
		{
			$enhancement_fees = $enhancement_fees_notax = $enhancement_fees_tax = $insertion_fees = $insertion_fees_notax = $insertion_fees_tax = 0;
			$col .= '<tr>';
			$rfpid = $value;
			$sql = $ilance->db->query("
				SELECT project_title, cid, featured, bold, autorelist, highlite, project_details, description_videourl, user_id, project_state 	 
				FROM " . DB_PREFIX . "projects 
				WHERE project_id = '" . intval($rfpid) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$ilance->GPC = array_merge($ilance->GPC, $res);
				if ($ilance->categories->can_post($_SESSION['ilancedata']['user']['slng'], 'service', $res['cid']))
				{
					$insertion_fees_notax = $insertion_fees_inctax = $insertion_fees = $insertion_fees_tax = 0;
					$insertion_fees_notax = $ilance->accounting_fees->calculate_insertion_fee($res['cid'], 'service', '0', $rfpid, $_SESSION['ilancedata']['user']['userid'], 0, 0, false);
					$insertion_fees_inctax = $ilance->accounting_fees->calculate_insertion_fee($res['cid'], 'service', '0', $rfpid, $_SESSION['ilancedata']['user']['userid'], 0, 0);
					$insertion_fees = $insertion_fees_inctax;
					$insertion_fees_tax = $insertion_fees_inctax - $insertion_fees_notax;
					$sum_insertion_fees_tax += $insertion_fees_tax;
					$sum_insertion_fees += $insertion_fees;
					$enhancements = array();
					$promo = array('bold', 'featured', 'highlite', 'autorelist');
					foreach($promo AS $key)
					{
						if (isset($ilance->GPC[$key]) AND $ilance->GPC[$key] == '1')
						{
							if($key == 'highlite')
							{
								$enhancements['highlite'] = '1';
							}
							else 
							{
								$enhancements[$key] = $ilance->GPC[$key];
							}
						}
					}
					$enhancement_fees = $ilance->auction_fee->process_listing_enhancements_transaction($enhancements, $_SESSION['ilancedata']['user']['userid'], $rfpid, 'insert', $res['project_state'], true);
					$enhancement_fees_notax = $enhancement_fees;
					if ($ilance->tax->is_taxable(intval($_SESSION['ilancedata']['user']['userid']), 'enhancements') AND $enhancement_fees > 0)
					{
						// #### fetch tax amount to charge for this invoice type
						$enhancement_fees_tax = $ilance->tax->fetch_amount(intval($_SESSION['ilancedata']['user']['userid']), $enhancement_fees, 'enhancements', 0);
						$enhancement_fees = $enhancement_fees + $enhancement_fees_tax;
						$sum_enhancement_fees_tax += $enhancement_fees_tax;
					}	
					$sum_enhancement_fees += $enhancement_fees;
					if ($ilance->GPC['rfpcmd'] != 'relist_all')
					{
						$col .= '<td class="alt1" valign="top"><div class="blue">' . handle_input_keywords($res['project_title']) . '</div></td>';
						$col .= '<td class="alt1" valign="top"><div class="blue">' . $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $res['cid']) . '</div></td>';			
						$tax1 = $insertion_fees_tax > 0 ? '<br /><div class="smaller gray">{_tax}: ' . $ilance->currency->format($insertion_fees_tax) . '</div>' : '';		
						$col .= '<td class="alt1" valign="top"><div class="blue">' . $ilance->currency->format($insertion_fees) . $tax1 . '</div></td>';
						$tax2 = $enhancement_fees_tax > 0 ? '<br /><div class="smaller gray">{_tax}: ' . $ilance->currency->format($enhancement_fees_tax) . '</div>' : '';
						$col .= '<td class="alt1" valign="top"><div class="blue">' . $ilance->currency->format($enhancement_fees) . $tax2 . '</div></td>';
						$tax3 = (($enhancement_fees_tax + $insertion_fees_tax) > 0) ? '<br /><div class="smaller gray">{_tax}: ' . $ilance->currency->format($enhancement_fees_tax + $insertion_fees_tax) . '</div>' : '';
						$col .= '<td class="alt1" valign="top"><div class="blue">' . $ilance->currency->format($enhancement_fees + $insertion_fees) . $tax3 . '</div></td>';
					}
					$hidden .= '<input type="hidden" name="rfp[' . $i . ']" value="' . $value . '" />';
					$i++;
				}
			}
			$col .= '</tr>';
		}
		$total = $sum_insertion_fees + $sum_enhancement_fees;
		$total_tax = $sum_insertion_fees_tax + $sum_enhancement_fees_tax; 
		if ($ilance->GPC['rfpcmd'] == 'relist_all')
		{
			$col = '<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" width="100%"><td><div>'.'{_you_are_trying_to_relist}'.' '.$i.' '.'{_auctions}'.'</div></td><td><div> </div></td><td><div>'.'{_total}'.' '.'{_fees}'.'  : ' . $ilance->currency->format($total) . '</div></td>';
		}
		else
		{
			$tax4 = ($total_tax > 0) ? '<br /><div class="smaller gray">{_tax}: ' . $ilance->currency->format($total_tax) . '</div>' : '';
			$col .= '<td><div></div></td><td><div></div></td><td></td><td></td><td><div><strong>{_total}</strong>: <span class="blue">' . $ilance->currency->format($total) . '</span> ' . $tax4 . '</div></td>';
		}
		$col .= '</table>';
		if ($i > 0)
		{
			$col .= '<form name="relist" method="post" action="' . HTTP_SERVER . $ilpage['buying'] . '" accept-charset="UTF-8" style="margin:0px">';
			$col .= '<input type="hidden" name="cmd" value="_do-rfp-action" />';
			$col .= '<input type="hidden" name="rfpcmd" value="relist-do" />';
			$col .= $hidden;
			$col .= '<div style="clear:both"></div><div style="padding-top:9px; padding-bottom:25px"><span><input type="submit" value=" {_continue} " style="font-size:15px" class="buttons" /></span></div></form>';
		}
		$area_title = '{_relist_auctions}';
		$page_title = SITE_NAME . ' - {_relist_auctions}';
		print_notice($area_title, $col, $ilpage['selling'], '{_return_to_the_previous_menu}');
		exit();
	}
	// #### RELIST PRODUCT AUCTIONS ########################################
	else if ($ilance->GPC['rfpcmd'] == 'relist-do')
	{
		$status = array();
		$pending = '';
		foreach ($ilance->GPC['rfp'] AS $key => $value)
		{
			$status[] = $ilance->auction_product->relist_product_auction($value);
		}
		if (in_array("frozen", $status))
		{
			$pending = "&pending=1";
		}
		refresh(HTTP_SERVER . $ilpage['buying'] . '?cmd=_do-rfp-action&rfpcmd=relist-do-notice' . $pending);
		exit();
	}
	else if ($ilance->GPC['rfpcmd'] == 'relist-do-notice')
	{
		$area_title = '{_relist_auctions}';
		$page_title = SITE_NAME . ' - {_relist_auctions}';
		if (isset($ilance->GPC['pending']) AND $ilance->GPC['pending'] == '1')
		{
			$pprint_array = array('url','login_include');
			$ilance->template->fetch('main', 'listing_reverse_auction_complete_frozen.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}
		else
		{
			print_notice('{_action_completed}', '{_auctions_successfully_relisted}', $ilpage['buying'] . '?cmd=management&sub=expired&displayorder=desc', '{_back}');
			exit();
		}
	}
	// #### TRANSFER AUCTIONS TO ANOTHER USER ##############################
	else if (isset($ilance->GPC['rfpcmd']) AND $ilance->GPC['rfpcmd'] == 'transfer' AND !empty($ilance->GPC['rfp']))
	{
		$show['submit'] = true;
		$area_title = '{_transfer_of_project_ownership}';
		$page_title = SITE_NAME . ' - {_transfer_of_project_ownership}';
		$navcrumb[""] = '{_transfer_of_project_ownership}';
		$rfp_list = $transfer_hidden_inputs = '';
		if (count($ilance->GPC['rfp']) > 0)
		{
			// empty inline cookie
			set_cookie('inlineservice', '', false);
			for ($i = 0; $i < count($ilance->GPC['rfp']); $i++)
			{
				$sql = $ilance->db->query("
					SELECT project_id, project_title, status
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . intval($ilance->GPC['rfp'][$i]) . "'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$res = $ilance->db->fetch_array($sql);
					if ($res['status'] == 'open')
					{
						$transfer_hidden_inputs .= '<input type="hidden" name="rfp[]" value="' . intval($ilance->GPC['rfp'][$i]) . '" />';
						$rfp_list .= '<li><a href="' . $ilpage['rfp'] . '?id=' . $res['project_id'] . '">' . print_string_wrap(stripslashes($res['project_title']), '45') . '</a> <span class="gray">(#' . $res['project_id'] . ')</span></li><br />';
					}
					else
					{
						$rfp_list .= '<li><span class="gray">{_ended}: ' . print_string_wrap(stripslashes($res['project_title']), '45') . ' (#' . $res['project_id'] . ')</span></li><br />';
					}
				}
			}
			if (empty($transfer_hidden_inputs))
			{
				$show['submit'] = false;
			}
			$ilance->template->fetch('main', 'buying_transfer.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_loop('main', 'project_results_rows');
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', array('rfp_list','selectedprojects','transfer_hidden_inputs','redirect','referer'));
			exit();
		}
	}
	else
	{
		$area_title = '{_rfp_error_no_rfp_selected}';
		$page_title = SITE_NAME . ' - {_rfp_error_no_rfp_selected}';
		print_notice('{_invalid_rfp_specified}', '{_your_request_to_review_or_place_a_bid_on_a_valid_request_for_proposal}', $ilpage['search'], '{_search_rfps}');
	}
}
// #### AUCTION TAKEOVER #######################################################
else if ($ilance->GPC['cmd'] == '_do-transfer-type' AND isset($ilance->GPC['transfertype']) AND $ilance->GPC['transfertype'] == 'userid')
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp'
	);
	$area_title = '{_transfer_rfp_ownership_menu}';
	$page_title = SITE_NAME . ' - {_transfer_rfp_ownership_menu}';
	$headinclude .= '<script type="text/javascript">
<!--
function validate_all(f)
{
	haveerrors = 0;
	(f.username.value.length < 1) ? showImage(\'usernameerror\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif\', true) : showImage(\'usernameerror\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif\', false);
	return (!haveerrors);
}
//-->
</script>';
	$rfpcount = 0;
	$transfer_hidden_inputs = '';
	if (!empty($ilance->GPC['rfp']) AND is_array($ilance->GPC['rfp']))
	{
		foreach ($ilance->GPC['rfp'] AS $value)
		{
			$rfpcount++;
			$transfer_hidden_inputs .= '<input type="hidden" name="rfp[]" value="' . intval($value) . '" />';
		}
	}
	$selectedprojects = $rfpcount;
	$pprint_array = array('selectedprojects','transfer_pulldown','transfer_hidden_inputs','redirect','referer');
	$ilance->template->fetch('main', 'buying_transfer_userid.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', 'project_results_rows');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### AUCTION TAKEOVER VIA EMAIL #############################################
else if ($ilance->GPC['cmd'] == '_do-transfer-type' AND isset($ilance->GPC['transfertype']) AND $ilance->GPC['transfertype'] == 'email')
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp'
	);
	$area_title = '{_transfer_rfp_ownership_menu}';
	$page_title = SITE_NAME . ' - {_transfer_rfp_ownership_menu}';
	$rfpcount = 0;
	foreach ($ilance->GPC['rfp'] as $value)
	{
		$rfpcount++;
		$transfer_hidden_inputs .= '<input type="hidden" name="rfp[]" value="'.$value.'" />';
	}
	$selectedprojects = $rfpcount;
	$ilance->template->fetch('main', 'buying_transfer_email.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', 'project_results_rows');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', array('selectedprojects','transfer_pulldown','transfer_hidden_inputs','redirect','referer'));
	exit();
}
// #### DO PROJECT TAKEOVER USERID TYPE ########################################
else if ($ilance->GPC['cmd'] == '_do-transfer-userid' AND isset($ilance->GPC['username']))
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp'
	);
	$rfpcount = 0;
	$sqluser = $ilance->db->query("
		SELECT user_id, email, username
		FROM " . DB_PREFIX . "users
		WHERE username = '" . $ilance->db->escape_string($ilance->GPC['username']) . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sqluser) > 0)
	{
		$resuser = $ilance->db->fetch_array($sqluser, DB_ASSOC);
		foreach ($ilance->GPC['rfp'] AS $value)
		{
			$rfpcount++;
			$sql = $ilance->db->query("
				SELECT cid, filtered_budgetid, project_title, status, date_end, bids, description
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . intval($value) . "'
					AND (status = 'draft' OR status = 'open')
					AND project_state = 'service'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$newmd5hash = md5(rand(0, 9999));
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "projects
					SET transfertype = 'userid',
					transfer_to_userid = '" . $resuser['user_id'] . "',
					transfer_from_userid = '" . $_SESSION['ilancedata']['user']['userid'] . "',
					transfer_to_email = '" . $resuser['email'] . "',
					transfer_status = 'pending',
					transfer_code = '" . $ilance->db->escape_string($newmd5hash) . "'
					WHERE project_id = '" . intval($value) . "'
						AND (status = 'draft' OR status = 'open')
						AND project_state = 'service'
				", 0, null, __FILE__, __LINE__);
				$budget = $ilance->auction->construct_budget_overview($res['cid'], $res['filtered_budgetid']);
				$ilance->email->mail = $resuser['email'];
				$ilance->email->slng = fetch_user_slng($resuser['user_id']);
				$ilance->email->get('rfp_takeover_via_userid');		
				$ilance->email->set(array(
					'{{transfer_to_username}}' => ucfirst(stripslashes($resuser['username'])),
					'{{transfer_from_username}}' => $_SESSION['ilancedata']['user']['username'],
					'{{rfp_title}}' => stripslashes($res['project_title']),
					'{{status}}' => $ilance->auction->print_auction_status($res['status']),
					'{{bids}}' => $res['bids'],
					'{{closing_date}}' => print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
					'{{description}}' => short_string(stripslashes($res['description']),150),
					'{{project_id}}' => $value,
					'{{transfer_hash}}' => $newmd5hash,
					'{{transfer_from_email}}' => $_SESSION['ilancedata']['user']['email'],
					'{{budget}}' => $budget,
				));
				$ilance->email->send();
			}
			else
			{
				$area_title = '{_invalid_rfp_state_rfp_can_not_be_transferred}';
				$page_title = SITE_NAME . ' - {_invalid_rfp_state_rfp_can_not_be_transferred}';
				print_notice($area_title, '{_your_requested_rfp_control_action_cannot_be_completed_because_one_or_more_rfps}</p><p>{_when_an_rfp_is_in_award_phase_rfp_control_features_such_as_archive}</p>', $ilpage['buying'], '{_return_to_the_previous_menu}');
			}
		}
		$selectedprojects = $rfpcount;
		print_notice('{_project_pending_ownership}', '{_please_allow_up_to_five_business_days_for_project_takeover_status}<br /><br />{_please_contact_customer_support}', $ilpage['buying'].'?cmd=management', '{_buying_activity}');
		exit();
	}
	else
	{
		$area_title = '{_invalid_username_for_rfp_transfer}';
		$page_title = SITE_NAME . ' - {_invalid_username_for_rfp_transfer}';
		print_notice('{_invalid_rfp_transfer_to_new_owner}', '{_were_sorry_there_was_a_problem_with_the_rfp_transfer_to_the_new_owner}<br /><br /><li>{_the_buyer_no_longer_exists_on_our_marketplace}</li><li>{_the_buyer_has_been_suspended_from_using_the_marketplace_resources}</li><li>{_the_buyer_does_not_have_proper_permissions_to_accept_rfp_takeover_requests_from_others}</li><br /><br />{_please_contact_customer_support}', $ilpage['main'], '{_main_menu}');
		exit();
	}
}
// #### DO TRANSFER EMAIL ######################################################
else if ($ilance->GPC['cmd'] == '_do-transfer-email' AND isset($ilance->GPC['email']))
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp'
	);
	$rfpcount = 0;
	foreach ($ilance->GPC['rfp'] AS $value)
	{
		$rfpcount++;
	}
	$selectedprojects = $rfpcount;
	print_notice('{_project_pending_ownership}', '{_please_allow_up_to_five_business_days_for_project_takeover_status}'."<br /><br />".'{_please_contact_customer_support}', $ilpage['buying'].'?cmd=management', '{_buying_activity}');
	exit();
}
// #### AWARD BID PROPOSAL PREVIEW #############################################
else if (isset($uncrypted['cmd']) AND $uncrypted['cmd'] == '_do-rfp-action' AND isset($uncrypted['bidcmd']) AND $uncrypted['bidcmd'] == 'awardbid' AND empty($ilance->GPC['rfpcmd']))
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp'
	);
	$area_title = '{_awarding_bid_proposal}';
	$page_title = SITE_NAME . ' - {_awarding_bid_proposal}';
	if (!isset($uncrypted['bid_id']) OR $uncrypted['bid_id'] == 0)
	{
		$area_title = '{_bid_award_error_no_bidid_selected}';
		$page_title = SITE_NAME . ' - {_bid_award_error_no_bidid_selected}';
		print_notice('{_invalid_bidid_selected_no_radio_button_selected}', '{_your_requested_action_cannot_be_completed}'."<br /><br />".'{_in_order_to_successfully_award}'."<br /><br />".'{_please_contact_customer_support}', $ilpage['main'], '{_main_menu}');
		exit();
	}
	$bid_id = intval($uncrypted['bid_id']);
	$show['disableaward'] = 0;
	if($uncrypted['bid_grouping'] == 1)
	{
		$field = 'bid_id';
		$table = 'project_bids';
	}
	else 
	{
		$field = 'id';
		$table = 'project_realtimebids';
	}
	$sql = $ilance->db->query("
		SELECT bid_id, user_id, project_id, project_user_id, proposal, bidamount, qty, estimate_days, date_added, date_updated, date_awarded, bidstatus, bidstate, bidamounttype, bidcustom, fvf, state, isproxybid, isshortlisted, winnermarkedaspaidmethod, winnermarkedaspaiddate, winnermarkedaspaid, buyerpaymethod, sellermarkedasshipped, sellermarkedasshippeddate, buyershipperid, buyershipcost
		FROM " . DB_PREFIX . $table . "
		WHERE " . $field . " = '" . intval($bid_id) . "'
			AND bidstate != 'wait_approval'
			AND bidstatus = 'placed'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
		$result = $ilance->db->fetch_array($sql, DB_ASSOC);
		$prosql = $ilance->db->query("
			SELECT currencyid, filter_escrow
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . $result['project_id'] . "'
				AND (status = 'open' OR status = 'expired')
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($prosql) > 0)
		{
			$projectsinfo = $ilance->db->fetch_array($prosql, DB_ASSOC);
			$winner_user_id = $result['user_id'];
			$winner_bid_message = stripslashes($result['proposal']);
			$winner_bid_message = $ilance->bbcode->strip_bb_tags($winner_bid_message);
			$winner_bid_price = $ilance->currency->format($result['bidamount'], $projectsinfo['currencyid']);
			$winner_bid_estimate_days = $result['estimate_days'];
			$show['escrow'] = ($projectsinfo['filter_escrow'] == '1' AND $result['winnermarkedaspaidmethod'] == 'escrow') ? 1 : 0;
			$awardamount = ($result['bidamounttype'] == 'entire')
				? $winner_bid_price
				: $ilance->currency->format(($result['bidamount'] * $winner_bid_estimate_days), $projectsinfo['currencyid']);
			$winner_bid_date_added = print_date($result['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
			$winner_project_id = $result['project_id'];
			$paymethod = !empty($result['winnermarkedaspaidmethod'])
				? $ilance->payment->print_fixed_payment_method($result['winnermarkedaspaidmethod'], false)
				: '<span class="red">{_provider_has_not_selected_a_payment_method_yet}</span>';
			$show['disableaward'] = empty($result['winnermarkedaspaidmethod']) ? 1 : 0;
		}
		else
		{
			$area_title = '{_bid_award_error_bidid_has_already_been_awarded}';
			$page_title = SITE_NAME . ' - {_bid_award_error_bidid_has_already_been_awarded}';
			print_notice('{_invalid_bid_action}', '{_your_requested_bid_control_action}' . '<br /><br />{_if_this_rfp_is_in_the_waiting_approval_phase}<br /><br />' . '{_please_contact_customer_support}', $ilpage['buying'] . '?cmd=management', '{_buying_activity}');
			exit();
		}
	}
	else
	{
		$area_title = '{_bid_award_error_bidid_has_already_been_awarded}';
		$page_title = SITE_NAME . ' - {_bid_award_error_bidid_has_already_been_awarded}';
		print_notice('{_invalid_bid_action}', '{_your_requested_bid_control_action}' . '<br /><br />{_if_this_rfp_is_in_the_waiting_approval_phase}<br /><br />' . '{_please_contact_customer_support}', $ilpage['buying'] . '?cmd=management', '{_buying_activity}');
		exit();
	}
	// #### awarded service provider details ###############################
	$sql_winner = $ilance->db->query("
		SELECT email, username, first_name, last_name, address, address2, city, state, zip_code, phone
		FROM " . DB_PREFIX . "users
		WHERE user_id = '".intval($winner_user_id)."'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql_winner) > 0)
	{
		$result_winner = $ilance->db->fetch_array($sql_winner, DB_ASSOC);
		$winner_user_email = $result_winner['email'];
		$winner_user_username = stripslashes($result_winner['username']);
		$winner_user_first_name = stripslashes($result_winner['first_name']);
		$winner_user_last_name = stripslashes($result_winner['last_name']);
		$winner_user_address = stripslashes($result_winner['address']);
		$winner_user_address2 = stripslashes($result_winner['address2']);
		$winner_user_city = stripslashes($result_winner['city']);
		$winner_user_state = stripslashes($result_winner['state']);
		$winner_user_zip_code = mb_strtoupper($result_winner['zip_code']);
		$winner_user_phone = $result_winner['phone'];
		$winner_user_country = $ilance->common_location->print_user_country($winner_user_id, $_SESSION['ilancedata']['user']['slng']);
	}
	// #### project details ################################################
	$sql_project = $ilance->db->query("
		SELECT project_title, description, date_added, date_end, bids, cid, filtered_budgetid, user_id
		FROM " . DB_PREFIX . "projects
		WHERE project_id = '" . intval($winner_project_id) . "'
			AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql_project) > 0)
	{
		$result_project = $ilance->db->fetch_array($sql_project, DB_ASSOC);
		$project_title = stripslashes($result_project['project_title']);
		$project_description = stripslashes($result_project['description']);
		$project_date_added = print_date($result_project['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
		$project_date_end = print_date($result_project['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
		$project_bids = $result_project['bids'];
		$project_budget = $ilance->auction->construct_budget_overview($result_project['cid'], $result_project['filtered_budgetid']);
		$project_user_id = $result_project['user_id'];
	}
	// #### service buyer details ##########################################
	$sql_project_owner = $ilance->db->query("
		SELECT email, username, first_name, last_name, address, address2, city, state, zip_code, phone
		FROM " . DB_PREFIX . "users
		WHERE user_id = '".intval($project_user_id)."'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql_project_owner) > 0)
	{
		$result_owner = $ilance->db->fetch_array($sql_project_owner, DB_ASSOC);
		$project_user_email = $result_owner['email'];
		$project_user_username = stripslashes($result_owner['username']);
		$project_user_first_name = stripslashes($result_owner['first_name']);
		$project_user_last_name = stripslashes($result_owner['last_name']);
		$project_user_address = stripslashes($result_owner['address']);
		$project_user_address2 = stripslashes($result_owner['address2']);
		$project_user_city = stripslashes($result_owner['city']);
		$project_user_state = stripslashes($result_owner['state']);
		$project_user_zip_code = mb_strtoupper($result_owner['zip_code']);
		$project_user_phone = $result_owner['phone'];
		$project_user_country = $ilance->common_location->print_user_country($project_user_id, $_SESSION['ilancedata']['user']['slng']);
	}
	// #### bid amount type ################################################
	switch ($result['bidamounttype'])
	{
		case 'entire':
		{
			$bidamounttype = '{_for_entire_project}';
			$measure = '{_days}';
			break;
		}                        
		case 'hourly':
		{
			$bidamounttype = '{_per_hour}';
			$measure = '{_hours}';
			break;
		}                        
		case 'daily':
		{
			$bidamounttype = '{_per_day}';
			$measure = '{_days}';
			break;
		}                        
		case 'weekly':
		{
			$bidamounttype = '{_weekly}';
			$measure = '{_weeks}';
			break;
		}                        
		case 'monthly':
		{
			$bidamounttype = '{_monthly}';
			$measure = '{_months}';
			break;
		}                        
		case 'lot':
		{
			$bidamounttype = '{_per_lot}';
			$measure = '{_lots}';
			break;
		}                        
		case 'weight':
		{
			$bidamounttype = '{_per_weight}';// . ' ' . stripslashes($rows['bidcustom']);
			$measure = '{_weight}';
			break;
		}                        
		case 'item':
		{
			$bidamounttype = '{_per_item}';
			$measure = '{_items}';
			break;
		}
	}
	$area_title = '{_award_bid_preview}';
	$page_title = SITE_NAME . ' - {_award_bid_preview}';
	$navcrumb = array();
	$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
	$navcrumb["$ilpage[buying]?cmd=management"] = '{_buying_activity}';
	$navcrumb[""] = '{_award_bid_preview}';
	$pprint_array = array('paymethod','measure','awardamount','bidamounttype','stars','project_budget','referrer','winner_bid_message','winner_bid_price','winner_bid_estimate_days','project_bids','bid_id','winner_project_id','winner_user_id','winner_user_username','project_title');
	$ilance->template->fetch('main', 'buying_award_bid_preview.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### AWARD BID PROPOSAL HANDLER #############################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_award-bid' AND isset($ilance->GPC['bid_id']) AND $ilance->GPC['bid_id'] > 0 AND isset($ilance->GPC['vendor_id']) AND $ilance->GPC['vendor_id'] > 0 AND isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0 AND (isset($ilance->GPC['awardthisbid']) OR isset($ilance->GPC['cancelaward'])))
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp'
	);
	if (isset($ilance->GPC['cancelaward']))
	{
		refresh(HTTP_SERVER . $ilpage['buying']);
		exit();
	}
	$notifybidders = isset($ilance->GPC['notifybidders']) ? intval($ilance->GPC['notifybidders']) : 0;
	$success = $ilance->auction_award->award_service_auction(intval($ilance->GPC['project_id']), intval($ilance->GPC['bid_id']), intval($_SESSION['ilancedata']['user']['userid']), intval($ilance->GPC['vendor_id']), $notifybidders, $_SESSION['ilancedata']['user']['slng']);
	if ($success)
	{
		$area_title = '{_rfp_was_awarded_to_a_vendor}';
		$page_title = SITE_NAME . ' - {_rfp_was_awarded_to_a_vendor}';
		$ilance->template->fetch('main', 'buying_award_bid_final.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', array('login_include'));
		exit();
	}
}
// #### DECLINE BID PROPOSAL ###################################################
else if (isset($uncrypted['cmd']) AND $uncrypted['cmd'] == '_do-rfp-action' AND isset($uncrypted['bidcmd']) AND $uncrypted['bidcmd'] == 'declinebid' AND empty($ilance->GPC['rfpcmd']))
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp'
	);
	if (isset($uncrypted['bid_id']) AND $uncrypted['bid_id'] > 0)
	{
		$bidid = intval($uncrypted['bid_id']);
		$success = $ilance->auction_award->service_auction_bid_decline($bidid, $_SESSION['ilancedata']['user']['userid'], $uncrypted['bid_grouping'], $_SESSION['ilancedata']['user']['slng']);
		if ($success)
		{
			$area_title = '{_selected_bid_was_declined}';
			$page_title = SITE_NAME . ' - {_selected_bid_was_declined}';
			print_notice('{_vendors_bid_was_declined}', '{_you_have_successfully_declined_this_vendors_bid_from_your_rfp}', $ilpage['buying'], '{_buying_activity}');
			exit();	
		}
		else
		{
			$area_title = '{_decline_bid_error_rfp_in_progress}';
			$page_title = SITE_NAME . ' - {_decline_bid_error_rfp_in_progress}';
			print_notice('{_invalid_bid_action}', '{_your_requested_bid_control_action}'."<br /><br />".'{_if_this_rfp_is_in_the_waiting_approval_phase}'."<br /><br />".'{_please_contact_customer_support}', $ilpage['buying'], '{_buying_activity}');
			exit();
		}
	}
	else
	{
		$area_title = '{_decline_bid_error_no_bidid_selected}';
		$page_title = SITE_NAME . ' - {_decline_bid_error_no_bidid_selected}';
		print_notice('{_invalid_bidid_selected_no_radio_button_selected}', '{_your_requested_action_cannot_be_completed}'."<br /><br />".'{_in_order_to_successfully_award}'."<br /><br />".'{_please_contact_customer_support}', $ilpage['main'], '{_main_menu}');
		exit();
	}
}
// #### CANCEL AWARD PROPOSAL ##################################################
else if (isset($uncrypted['cmd']) AND $uncrypted['cmd'] == '_do-rfp-action' AND isset($uncrypted['bidcmd']) AND $uncrypted['bidcmd'] == 'unawardbid' AND empty($ilance->GPC['rfpcmd']))
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp'
	);
	$area_title = '{_unawarding_service_provider}';
	$page_title = SITE_NAME . ' - {_unawarding_service_provider}';
	if (isset($uncrypted['bid_id']) AND $uncrypted['bid_id'] > 0)
	{
		$bid_grouping = isset($uncrypted['bid_grouping']) ? intval($uncrypted['bid_grouping']) : 1;
		$success = $ilance->auction_award->unaward_service_auction(intval($uncrypted['bid_id']), $bid_grouping);
		if ($success)
		{
			$area_title = '{_bid_was_un_awarded}';
			$page_title = SITE_NAME . ' - {_bid_was_un_awarded}';
			print_notice('{_awarded_vendor_was_un_awarded}', '{_you_have_successfully_un_awarded_this_vendor_from_your_rfp}', $ilpage['buying'] . '?cmd=management', '{_buying_activity}');
			exit();
		}
		else
		{
			$area_title = '{_cancel_award_error_vendor_is_not_awarded_yet}';
			$page_title = SITE_NAME . ' - {_cancel_award_error_vendor_is_not_awarded_yet}';
			print_notice('{_cannot_unaward_bid}', '{_were_sorry_you_cannot_unaward}' . '<br /><br />' . '{_if_you_require_additional_information}', $ilpage['buying'] . '?cmd=management', '{_buying_activity}');
			exit();
		}
	}
	else
	{
		$area_title = '{_cancel_award_error_no_bidid_selected}';
		$page_title = SITE_NAME . ' - {_cancel_award_error_no_bidid_selected}';
		print_notice('{_invalid_bidid_selected_no_radio_button_selected}', '{_your_requested_action_cannot_be_completed}' . '<br /><br />{_in_order_to_successfully_award}<br /><br />' . '{_please_contact_customer_support}', $ilpage['main'], '{_main_menu}');
		exit();
	}
}
// #### SET AUCTION AS FINISHED ################################################
else if (isset($uncrypted['cmd']) AND $uncrypted['cmd'] == '_do-auction-action' AND isset($uncrypted['sub']) AND $uncrypted['sub'] == 'finished' AND $uncrypted['project_id'] > 0 AND $uncrypted['buyer_id'] > 0 AND $uncrypted['seller_id'] > 0)
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp'
	);
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "projects
		SET status = 'finished'
		WHERE project_id = '" . $uncrypted['project_id'] . "'
			AND user_id = '" . $uncrypted['buyer_id'] . "'
			AND status = 'approval_accepted'
	", 0, null, __FILE__, __LINE__);
	$sql = $ilance->db->query("
		SELECT cid, filtered_budgetid, project_title, description, date_added, date_end, bids, user_id
		FROM " . DB_PREFIX . "projects
		WHERE project_id = '" . $uncrypted['project_id'] . "'
			AND user_id = '" . $uncrypted['buyer_id'] . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
		while ($result_p = $ilance->db->fetch_array($sql))
		{
			$project_budget = $ilance->auction->construct_budget_overview($result_p['cid'], $result_p['filtered_budgetid']);
			$project_title = stripslashes($result_p['project_title']);
			$project_description = $result_p['description'];
			$project_date_added = $result_p['date_added'];
			$project_date_end = $result_p['date_end'];
			$project_bids = $result_p['bids'];
			$project_user_id = $result_p['user_id'];
		}
	}
	$sql2 = $ilance->db->query("
		SELECT user_id, email, username, first_name, last_name, address, address2, city, state, zip_code, phone
		FROM " . DB_PREFIX . "users
		WHERE user_id = '" . $uncrypted['buyer_id'] . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql2) > 0)
	{
		$result_email = $ilance->db->fetch_array($sql2, DB_ASSOC);
		$project_user_currency = $ilance->currency->fetch_user_currency($result_email['user_id']);
		$project_user_email = $result_email['email'];
		$project_user_username = stripslashes($result_email['username']);
		$project_user_first_name = ucfirst($result_email['first_name']);
		$project_user_last_name = ucfirst($result_email['last_name']);
		$project_user_address = stripslashes($result_email['address']);
		$project_user_address2 = stripslashes($result_email['address2']);
		$project_user_city = ucfirst($result_email['city']);
		$project_user_state = ucfirst($result_email['state']);
		$project_user_zip_code = mb_strtoupper($result_email['zip_code']);
		$project_user_phone = $result_email['phone'];
		$project_user_country = $ilance->common_location->print_user_country($uncrypted['buyer_id'], $_SESSION['ilancedata']['user']['slng']);
	}
	$provider_user_email = fetch_user('email', $uncrypted['seller_id']);
	$provider_user_username = fetch_user('username', $uncrypted['seller_id']);
	$existing = array(
		'{{provider_user_username}}' => $provider_user_username,
		'{{provider_user_email}}' => $provider_user_email,
		'{{project_user_username}}' => $project_user_username,
		'{{project_user_email}}' => $project_user_email,
		'{{project_title}}' => $project_title,
		'{{project_description}}' => $project_description,
		'{{project_date_added}}' => $project_date_added,
		'{{project_date_end}}' => $project_date_end,
		'{{project_bids}}' => $project_bids,
		'{{project_budget}}' => $project_budget,
	);
	$ilance->email->mail = $_SESSION['ilancedata']['user']['email'];
	$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
	$ilance->email->get('project_marked_finished');		
	$ilance->email->set($existing);
	$ilance->email->send();
	$ilance->email->mail = $provider_user_email;
	$ilance->email->slng = fetch_user_slng($uncrypted['seller_id']);
	$ilance->email->get('project_finished_notify');		
	$ilance->email->set($existing);
	$ilance->email->send();
	$ilance->email->mail = SITE_EMAIL;
	$ilance->email->slng = fetch_site_slng();
	$ilance->email->get('project_finished_notify_admin');		
	$ilance->email->set($existing);
	$ilance->email->send();
	print_notice('{_service_auction_complete}', '{_you_have_just_set_this_service_auction_as_being_finished_and_your_requirements_have_been_delivered}' . '<br /><br />' . '{_please_contact_customer_support}', $ilpage['buying'] . '?cmd=management', '{_buying_activity}');
	exit();
}
// #### buyer marking listing as paid ##########################################
else if (isset($uncrypted['cmd']) AND $uncrypted['cmd'] == 'management' AND isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == 'markaspaid' AND isset($uncrypted['pid']) AND $uncrypted['pid'] > 0 AND isset($uncrypted['bid']) AND $uncrypted['bid'] > 0)
{
	// #### buyer input as to how the payment was made to the seller
	$ilance->GPC['winnermarkedaspaidmethod'] = isset($ilance->GPC['winnermarkedaspaidmethod']) ? handle_input_keywords($ilance->GPC['winnermarkedaspaidmethod']) : '{_unknown}';
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "project_bids
		SET winnermarkedaspaid = '1',
		winnermarkedaspaiddate = '" . DATETIME24H . "',
		winnermarkedaspaidmethod = '" . $ilance->db->escape_string($ilance->GPC['winnermarkedaspaidmethod']) . "'
		WHERE project_id = '" . intval($uncrypted['pid']) . "'
			AND bid_id = '" . intval($uncrypted['bid']) . "'
	", 0, null, __FILE__, __LINE__);
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "project_realtimebids
		SET winnermarkedaspaid = '1',
		winnermarkedaspaiddate = '" . DATETIME24H . "',
		winnermarkedaspaidmethod = '" . $ilance->db->escape_string($ilance->GPC['winnermarkedaspaidmethod']) . "'
		WHERE project_id = '" . intval($uncrypted['pid']) . "'
			AND bid_id = '" . intval($uncrypted['bid']) . "'
	", 0, null, __FILE__, __LINE__);
	if (isset($ilance->GPC['returnurl']) AND !empty($ilance->GPC['returnurl']))
	{
		refresh($ilance->GPC['returnurl']);
		exit();
	}
	refresh(HTTP_SERVER . $ilpage['buying'] . '?cmd=management');
	exit();
}
// #### buyer marking listing unpaid ###########################################
else if (isset($uncrypted['cmd']) AND $uncrypted['cmd'] == 'management' AND isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == 'markasunpaid' AND isset($uncrypted['pid']) AND $uncrypted['pid'] > 0 AND isset($uncrypted['bid']) AND $uncrypted['bid'] > 0)
{
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "project_bids
		SET winnermarkedaspaid = '0',
		winnermarkedaspaiddate = '0000-00-00 00:00:00',
		winnermarkedaspaidmethod = ''
		WHERE project_id = '" . intval($uncrypted['pid']) . "'
			AND bid_id = '" . intval($uncrypted['bid']) . "'
	", 0, null, __FILE__, __LINE__);
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "project_realtimebids
		SET winnermarkedaspaid = '0',
		winnermarkedaspaiddate = '0000-00-00 00:00:00',
		winnermarkedaspaidmethod = ''
		WHERE project_id = '" . intval($uncrypted['pid']) . "'
			AND bid_id = '" . intval($uncrypted['bid']) . "'
	", 0, null, __FILE__, __LINE__);
	if (isset($ilance->GPC['returnurl']) AND !empty($ilance->GPC['returnurl']))
	{
		refresh($ilance->GPC['returnurl']);
		exit();
	}
	refresh(HTTP_SERVER . $ilpage['buying'] . '?cmd=management');
	exit();
}
// #### marking buy now order as paid ##########################################
else if (isset($uncrypted['cmd']) AND $uncrypted['cmd'] == 'management' AND isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == 'markorderaspaid' AND isset($uncrypted['id']) AND $uncrypted['id'] > 0 AND isset($uncrypted['orderid']) AND $uncrypted['orderid'] > 0)
{
	$winnermarkedaspaidmethod = isset($ilance->GPC['winnermarkedaspaidmethod']) ? $ilance->GPC['winnermarkedaspaidmethod'] : '';
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "buynow_orders
		SET paiddate = '" . DATETIME24H . "',
		winnermarkedaspaiddate = '" . DATETIME24H . "',
		winnermarkedaspaid = '1',
		winnermarkedaspaidmethod = '" . $ilance->db->escape_string($winnermarkedaspaidmethod) . "'
		WHERE orderid = '" . intval($uncrypted['orderid']) . "'
			AND project_id = '" . intval($uncrypted['id']) . "'
	");
	if (isset($uncrypted['status']) AND !empty($uncrypted['status']))
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "buynow_orders
			SET status = '" . $ilance->db->escape_string($uncrypted['status']) . "'
			WHERE orderid = '" . intval($uncrypted['orderid']) . "'
				AND project_id = '" . intval($uncrypted['id']) . "'
		");
	}
	if (isset($ilance->GPC['returnurl']) AND !empty($ilance->GPC['returnurl']))
	{
		refresh($ilance->GPC['returnurl']);
		exit();
	}
	refresh(HTTP_SERVER . $ilpage['buying'] . '?cmd=management');
	exit();
}
// #### marking buy now order as unpaid ########################################
else if (isset($uncrypted['cmd']) AND $uncrypted['cmd'] == 'management' AND isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == 'markorderasunpaid' AND isset($uncrypted['id']) AND $uncrypted['id'] > 0 AND isset($uncrypted['orderid']) AND $uncrypted['orderid'] > 0)
{
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "buynow_orders
		SET paiddate = '0000-00-00 00:00:00',
		winnermarkedaspaiddate = '0000-00-00 00:00:00',
		winnermarkedaspaid = '0',
		winnermarkedaspaidmethod = ''
		WHERE orderid = '" . intval($uncrypted['orderid']) . "'
			AND project_id = '" . intval($uncrypted['id']) . "'
	");
	if (isset($uncrypted['status']) AND !empty($uncrypted['status']))
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "buynow_orders
			SET status = '" . $ilance->db->escape_string($uncrypted['status']) . "'
			WHERE orderid = '" . intval($uncrypted['orderid']) . "'
				AND project_id = '" . intval($uncrypted['id']) . "'
		");
	}
	if (isset($ilance->GPC['returnurl']) AND !empty($ilance->GPC['returnurl']))
	{
		refresh($ilance->GPC['returnurl']);
		exit();
	}
	refresh(HTTP_SERVER . $ilpage['buying'] . '?cmd=management');
	exit();
}
// #### BUYING ACTIVITY MENU ###################################################
else
{
	include_once(DIR_SERVER_ROOT . 'buying_activity.php');
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>