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
if (!defined('LOCATION') OR defined('LOCATION') != 'admin')
{
	die('<strong>Fatal:</strong> This script cannot be parsed indirectly.');
}

$area_title = '{_auctions_distribution}';
$page_title = SITE_NAME . ' - {_auctions_distribution}';

($apihook = $ilance->api('admincp_auction_management')) ? eval($apihook) : false;

$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'], $_SESSION['ilancedata']['user']['slng']);
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'auctions' OR empty($ilance->GPC['cmd']))
{
	if (isset($ilance->GPC['pagetype']))
	{
		$pagetype = $ilance->GPC['pagetype'];
		$page = intval($ilance->GPC['page']);
		$viewtype = $ilance->GPC['viewtype'];
		$pp = isset($ilance->GPC['pp']) ? intval($ilance->GPC['pp']) : $ilconfig['globalfilters_maxrowsdisplay'];
	}
	// #### UPDATE AUCTION HANDLER #################################
	if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == '_update-auction')
	{
		$cid = intval($ilance->GPC['acpcid']);
		$visible = intval($ilance->GPC['visible']);
		$query = $ilance->db->query("
			SELECT cid, status
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . intval($ilance->GPC['project_id']) . "'
		");
		if ($ilance->db->num_rows($query) > 0)
		{
			$qres = $ilance->db->fetch_array($query, DB_ASSOC);
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET status = '" . $ilance->db->escape_string($ilance->GPC['status']) . "',
				    project_state = '" . $ilance->db->escape_string($ilance->GPC['project_state']) . "',
				    project_details = '" . $ilance->db->escape_string($ilance->GPC['project_details']) . "',
				    cid = '" . intval($cid) . "',
				    date_added = '" . $ilance->db->escape_string($ilance->GPC['date_added']) . "',
				    date_starts = '" . $ilance->db->escape_string($ilance->GPC['date_starts']) . "',
				    date_end = '" . $ilance->db->escape_string($ilance->GPC['date_end']) . "',
				    visible = '" . $visible . "'
				WHERE project_id = '" . intval($ilance->GPC['project_id']) . "'
			");
			// is the admin changing the category for this listing?
			// if so, we must remove all answers based on this category..
			$ilance->categories->move_listing_category_from_to($ilance->GPC['project_id'], $qres['cid'], $cid, $ilance->GPC['project_state'], $qres['status'], $ilance->GPC['status']);
		}
		print_action_success('{_listing_id_was_updated_no_email_was_dispatched_to_the_member}', $ilance->GPC['return']);
		exit();
	}
	// #### AUCTION MODERATION CONTROLS ############################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'moderate-action')
	{
		// #### VALIDATE MULTIPLE AUCTIONS #####################
		if (isset($ilance->GPC['validate']))
		{
			// default email template to parse when sending out verified listing emails
			$emailtemplate = 'moderate_auction_verified';
			

			($apihook = $ilance->api('admincp_moderate_action_validate_start')) ? eval($apihook) : false;

			foreach ($ilance->GPC['project_id'] AS $value)
			{
				$sql = $ilance->db->query("
					SELECT user_id, cid, project_state, project_details, project_title, date_starts, date_end, UNIX_TIMESTAMP('" . DATETIME24H . "') - UNIX_TIMESTAMP(date_added) AS seconds, status
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . intval($value) . "'
					ORDER BY user_id ASC
				");
				if ($ilance->db->num_rows($sql) > 0)
				{
					$res = $ilance->db->fetch_array($sql, DB_ASSOC);
					if ($res['project_state'] == 'product')
					{
						$url = HTTP_SERVER . $ilpage['merch'] . '?id=' . $value;
					}
					else if ($res['project_state'] == 'service')
					{
						$ilance->auction_rfp->dispatch_invited_members_email_afteradminvalidate(array (), 'service', fetch_auction('project_id', intval($value)), $res['user_id']);
						$url = HTTP_SERVER . $ilpage['rfp'] . '?id=' . $value;
					}
					$secondspast = $res['seconds'];
					$sqltime = $ilance->db->query("
						SELECT DATE_ADD('$res[date_end]', INTERVAL $secondspast SECOND) AS new_date_end
					");
					$restime = $ilance->db->fetch_array($sqltime, DB_ASSOC);
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
					// add seconds that have past back to the listings date_end
					$ilance->db->query("
						    UPDATE " . DB_PREFIX . "projects
						    SET date_starts = '" . $ilance->db->escape_string($new_date_start) . "',
						    date_end = '" . $ilance->db->escape_string($new_date_end) . "',
						    visible = '1'
						    WHERE project_id = '" . intval($value) . "'
					");

					($apihook = $ilance->api('admincp_moderate_action_validate_foreach')) ? eval($apihook) : false;

					// rebuild category count
					if ($res['status'] == 'open')
					{
						$ilance->categories->build_category_count($res['cid'], 'add', "admin validating moderated listing from admincp: adding increment count category id $res[cid]");
					}
					$ilance->email->mail = array (fetch_user('email', $res['user_id']), SITE_EMAIL);
					$ilance->email->slng = fetch_user_slng($res['user_id']);
					$ilance->email->get($emailtemplate);
					$ilance->email->set(array (
					    '{{project_id}}' => $value,
					    '{{project_title}}' => $res['project_title'],
					    '{{url}}' => $url,
					    '{{new_date_end}}' => print_date($new_date_end, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
					    '{{new_date_start}}' => print_date($new_date_start, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
					));
					$ilance->email->send();
					$ilance->referral->update_referral_action('postauction', $res['user_id']);
				}
			}
			print_action_success('{_the_selected_listings_have_been_verified_an_email_was_also_dispatched}', $ilance->GPC['return']);
			exit();
		}
		// #### REMOVE MULTIPLE AUCTIONS PENDING MODERATION
		else if (isset($ilance->GPC['remove']))
		{
			
			$emailnotice = '';

			($apihook = $ilance->api('admincp_moderate_action_remove_start')) ? eval($apihook) : false;

			if (!isset($ilance->GPC['project_id']))
			{
				print_action_failed('{_there_was_an_error_no_listings_have_been_selected_for_moderation}', $ilance->GPC['return']);
				exit();
			}
			$count = 1;
			foreach ($ilance->GPC['project_id'] AS $value)
			{
				$sql = $ilance->db->query("
					SELECT user_id, cid, project_state, project_title
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . intval($value) . "'
				");
				if ($ilance->db->num_rows($sql) > 0)
				{
					$res = $ilance->db->fetch_array($sql, DB_ASSOC);
					$ilance->common_listing->physically_remove_listing(intval($value));
					if ($res['project_state'] == 'product')
					{
						$url = HTTP_SERVER . $ilpage['merch'] . '?id=' . $value;
						$emailnotice .= "$count. Item title: $res[project_title] (#$value) - $url" . LINEBREAK;
					}
					else if ($res['project_state'] == 'service')
					{
						$url = HTTP_SERVER . $ilpage['rfp'] . '?id=' . $value;
						$emailnotice .= "$count. Job title: $res[project_title] (#$value) - $url" . LINEBREAK;
					}
			
					($apihook = $ilance->api('admincp_moderate_action_remove_foreach')) ? eval($apihook) : false;
			
					$count++;
				}
			}
			if (!empty($emailnotice))
			{
				$ilance->email->mail = SITE_EMAIL;
				$ilance->email->slng = fetch_site_slng();
				$ilance->email->get('moderate_auction_unverified');
				$ilance->email->set(array (
				    '{{listingsremoved}}' => $emailnotice,
				));
				$ilance->email->send();
			}
			print_action_success('{_the_selected_listings_were_removed}', $ilance->GPC['return']);
			exit();
		}
		else if (isset($ilance->GPC['removeall']))
		{
			if ($ilance->GPC['actioncmd'] == 'moderate-action')
			{
				$sql = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects WHERE visible = '0' AND status = '" . $ilance->GPC['condition'] . "'");
				$total = $ilance->db->num_rows($sql);
				$notice = '{_are_you_sure_you_want_to_delete_all_moderate_auctions} {_totally} ' . $total . ' {_' . $ilance->GPC['condition'] . '}  {_auction}?';
				$confirm_url = $ilpage['distribution'] . '?cmd=auctions&amp;viewtype=moderate&do=removeall&condition=' . $ilance->GPC['condition'];
				$cancel_url = $ilpage['distribution'] . '?cmd=auctions&amp;viewtype=moderate';
			}
			continue_action_success($notice, $confirm_url, '');
			exit();
		}
	}
	// #### REGULAR AUCTION CONTROLS ###############################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'auction-action')
	{
		if ($show['ADMINCP_TEST_MODE'])
		{
			print_action_failed('{_demo_mode_only}', $ilpage['components']);
			exit();
		}
		// #### CLOSE MULTIPLE AUCTIONS ################
		if (isset($ilance->GPC['close']))
		{
			if (!isset($ilance->GPC['project_id']))
			{
			    print_action_failed('{_there_was_an_error_no_listings_have_been_selected_for_moderation}', $ilance->GPC['return']);
			    exit();
			}
			$emailtemplate = 'moderate_auction_closed';
			
		
			($apihook = $ilance->api('admincp_action_close_start')) ? eval($apihook) : false;
		
			$notice = $listingsended = '';
			foreach ($ilance->GPC['project_id'] AS $value)
			{
				$sql = $ilance->db->query("
					SELECT user_id, cid, status, project_state, project_title
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . intval($value) . "'
					LIMIT 1
				");
				if ($ilance->db->num_rows($sql) > 0)
				{
					$res = $ilance->db->fetch_array($sql, DB_ASSOC);
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET status = 'closed',
						    close_date = '" . DATETIME24H . "'
						WHERE project_id = '" . intval($value) . "'
					");
					if ($res['status'] == 'open')
					{
						$ilance->categories->build_category_count($res['cid'], 'subtract', "admin closing multiple listings from admincp: subtracting increment count category id $res[cid]");
					}
					if ($res['project_state'] == 'product')
					{
						$url = HTTP_SERVER . $ilpage['merch'] . '?id=' . $value;
					}
					else if ($res['project_state'] == 'service')
					{
						$url = HTTP_SERVER . $ilpage['rfp'] . '?id=' . $value;
					}
					$listingsended .= $res['project_title'] . " (#" . intval($value) . ") - $url" . LINEBREAK;

					($apihook = $ilance->api('admincp_action_close_foreach')) ? eval($apihook) : false;
				}
			}
			if (!empty($listingsended))
			{
				$ilance->email->mail = SITE_EMAIL;
				$ilance->email->slng = fetch_site_slng();
				$ilance->email->get($emailtemplate);
				$ilance->email->set(array (
				    '{{listingsended}}' => $listingsended,
				));
				$ilance->email->send();
			}
			print_action_success('{_the_selected_listings_were_closed_early}', $ilance->GPC['return']);
			exit();
		}
		// #### DELIST MULTIPLE AUCTIONS ###############
		else if (isset($ilance->GPC['delist']))
		{
			if (!isset($ilance->GPC['project_id']))
			{
				print_action_failed('{_there_was_an_error_no_listings_have_been_selected_for_moderation}', $ilance->GPC['return']);
				exit();
			}
			$emailtemplate = 'moderate_auction_delist';
			

			($apihook = $ilance->api('admincp_action_delist_start')) ? eval($apihook) : false;
		
			$notice = $listingsdelisted = '';
			foreach ($ilance->GPC['project_id'] AS $value)
			{
				$sql = $ilance->db->query("
					SELECT user_id, cid, status, project_state
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . intval($value) . "'
					ORDER BY user_id ASC
				");
				if ($ilance->db->num_rows($sql) > 0)
				{
					$res = $ilance->db->fetch_array($sql, DB_ASSOC);
					if ($res['status'] == 'open')
					{
					    $ilance->categories->build_category_count($res['cid'], 'subtract', "admin delisting multiple listings from admincp: subtracting increment count category id $res[cid]");
					}
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET status = 'delisted',
						close_date = '" . DATETIME24H . "'
						WHERE project_id = '" . intval($value) . "'
					");
					if ($res['project_state'] == 'product')
					{
						$url = HTTP_SERVER . $ilpage['merch'] . '?id=' . $value;
						$listingsdelisted .= "Item title: " . fetch_auction('project_title', intval($value)) . " (#" . intval($value) . ") - $url" . LINEBREAK;
					}
					else if ($res['project_state'] == 'service')
					{
						$url = HTTP_SERVER . $ilpage['rfp'] . '?id=' . $value;
						$listingsdelisted .= "Job title: " . fetch_auction('project_title', intval($value)) . " (#" . intval($value) . ") - $url" . LINEBREAK;
					}

					($apihook = $ilance->api('admincp_action_delist_foreach')) ? eval($apihook) : false;
				}
			}
			if (!empty($listingsdelisted))
			{
				$ilance->email->mail = SITE_EMAIL;
				$ilance->email->slng = fetch_site_slng();
				$ilance->email->get($emailtemplate);
				$ilance->email->set(array (
				    '{{listingsdelisted}}' => $listingsdelisted,
				));
				$ilance->email->send();
			}
			print_action_success('{_the_selected_listings_were_delisted_closed}', $ilance->GPC['return']);
			exit();
		}
		// #### ARCHIVE MULTIPLE AUCTIONS ##############
		else if (isset($ilance->GPC['archive']))
		{
			if (!isset($ilance->GPC['project_id']))
			{
				print_action_failed('{_there_was_an_error_no_listings_have_been_selected_for_moderation}', $ilance->GPC['return']);
				exit();
			}
			$emailtemplate = 'moderate_auction_archive';
		
			
		
			($apihook = $ilance->api('admincp_action_archive_start')) ? eval($apihook) : false;
		
			$notice = $listingsarchived = '';
			foreach ($ilance->GPC['project_id'] AS $value)
			{
				$sql = $ilance->db->query("
					SELECT user_id, cid, status, project_state
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . intval($value) . "'
					ORDER BY user_id ASC
				");
				if ($ilance->db->num_rows($sql) > 0)
				{
					$res = $ilance->db->fetch_array($sql, DB_ASSOC);
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET status = 'archived',
						close_date = '" . DATETIME24H . "'
						WHERE project_id = '" . intval($value) . "'
					");
					if ($res['status'] == 'open')
					{
						$ilance->categories->build_category_count($res['cid'], 'subtract', "admin archiving multiple listings from admincp: subtracting increment count category id $res[cid]");
					}
					if ($res['project_state'] == 'product')
					{
						$url = HTTP_SERVER . $ilpage['merch'] . '?id=' . $value;
						$listingsarchived .= "Item title: " . fetch_auction('project_title', intval($value)) . " (#" . intval($value) . ") - $url" . LINEBREAK;
					}
					else if ($res['project_state'] == 'service')
					{
						$url = HTTP_SERVER . $ilpage['rfp'] . '?id=' . $value;
						$listingsarchived .= "Job title: " . fetch_auction('project_title', intval($value)) . " (#" . intval($value) . ") - $url" . LINEBREAK;
					}

					($apihook = $ilance->api('admincp_action_archive_foreach')) ? eval($apihook) : false;
				}
			}

			if (!empty($listingsarchived))
			{
				$ilance->email->mail = SITE_EMAIL;
				$ilance->email->slng = fetch_site_slng();
				$ilance->email->get($emailtemplate);
				$ilance->email->set(array (
				    '{{listingsarchived}}' => $listingsarchived,
				));
				$ilance->email->send();
			}
			print_action_success('{_the_selected_listings_were_archived}', $ilance->GPC['return']);
			exit();
		}
		// #### REMOVE MULTIPLE AUCTIONS ###############
		else if (isset($ilance->GPC['remove']))
		{
			
			$notice = $emailnotice = '';
			$count = 1;

			($apihook = $ilance->api('admincp_action_remove_start')) ? eval($apihook) : false;

			if (!isset($ilance->GPC['project_id']))
			{
				print_action_failed('{_there_was_an_error_no_listings_have_been_selected_for_moderation}', $ilance->GPC['return']);
				exit();
			}
			foreach ($ilance->GPC['project_id'] AS $value)
			{
				$sql = $ilance->db->query("
					SELECT user_id, cid, status, project_state, project_title
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . intval($value) . "'
					ORDER BY user_id ASC
				");
				if ($ilance->db->num_rows($sql) > 0)
				{
					$res = $ilance->db->fetch_array($sql, DB_ASSOC);
					if ($res['status'] == 'open')
					{
						$ilance->categories->build_category_count($res['cid'], 'subtract', "admin removing multiple listings from admincp: subtracting increment count category id $res[cid]");
					}
					$ilance->common_listing->physically_remove_listing(intval($value));
					if ($res['project_state'] == 'product')
					{
						$url = HTTP_SERVER . $ilpage['merch'] . '?id=' . $value;
						$emailnotice .= "$count. Item listing: $res[project_title] (#$value) - $url";
					}
					else if ($res['project_state'] == 'service')
					{
						$url = HTTP_SERVER . $ilpage['rfp'] . '?id=' . $value;
						$emailnotice .= "$count. Job listing: $res[project_title] (#$value) - $url";
					}
		
					($apihook = $ilance->api('admincp_action_remove_foreach')) ? eval($apihook) : false;
		
					$count++;
				}
			}
			if (!empty($emailnotice))
			{
				$ilance->email->mail = SITE_EMAIL;
				$ilance->email->slng = fetch_site_slng();
				$ilance->email->get('moderate_auction_unverified');
				$ilance->email->set(array (
				    '{{listingsremoved}}' => $emailnotice,
				));
				$ilance->email->send();
			}
			log_event($_SESSION['ilancedata']['user']['userid'], $ilpage['distribution'], $ilance->GPC['cmd'], $ilance->GPC['subcmd'], $ilance->GPC['remove'], '{_removed} ' . $count);
			print_action_success('{_the_selected_listings_were_removed}', $ilance->GPC['return']);
			exit();
		}
		else if (isset($ilance->GPC['removeall']))
		{
			if ($ilance->GPC['actioncmd'] == 'product-action')
			{
				$sql = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects WHERE visible = '1' AND project_state = 'product'");
				$total = $ilance->db->num_rows($sql);
				$notice = '{_are_you_sure_you_want_to_delete_all_product_auctions} (' . number_format($total) . ')?';
				$confirm_url = $ilpage['distribution'] . '?cmd=auctions&amp;viewtype=product&do=removeall';
				$cancel_url = $ilpage['distribution'] . '?cmd=auctions&amp;viewtype=product';
			}
			if ($ilance->GPC['actioncmd'] == 'service-action')
			{
				$sql = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "projects WHERE visible = '1' AND project_state = 'service'");
				$total = $ilance->db->num_rows($sql);
				$notice = '{_are_you_sure_you_want_to_delete_all_service_auctions} (' . number_format($total) . ')?';
				$confirm_url = $ilpage['distribution'] . '?cmd=auctions&amp;viewtype=service&do=removeall';
				$cancel_url = $ilpage['distribution'] . '?cmd=auctions&amp;viewtype=service';
			}
			continue_action_success($notice, $confirm_url, $cancel_url = '');
			exit();
		}
	}
	else if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'removeall')
	{
		if ($show['ADMINCP_TEST_MODE'])
		{
			print_action_failed('{_demo_mode_only}', $ilpage['components']);
			exit();
		}
		
		$notice = $emailnotice = '';
		$count = 1;
	
		($apihook = $ilance->api('admincp_action_remove_start')) ? eval($apihook) : false;
	
		if ($ilance->GPC['viewtype'] == 'moderate')
		{
			$dosql = "visible = '0'";
			$ilance->GPC['return'] = $ilpage['distribution'] . '?cmd=auctions&amp;viewtype=moderate';
		}
		if ($ilance->GPC['viewtype'] == 'product')
		{
			$dosql = "visible = '1' AND project_state = 'product'";
			$ilance->GPC['return'] = $ilpage['distribution'] . '?cmd=auctions&amp;viewtype=product';
		}
		if ($ilance->GPC['viewtype'] == 'service')
		{
			$dosql = "visible = '1' AND project_state = 'service'";
			$ilance->GPC['return'] = $ilpage['distribution'] . '?cmd=auctions&amp;viewtype=service';
		}
		$sql = $ilance->db->query("
			SELECT user_id, cid, status, project_state, project_title, project_id
			FROM " . DB_PREFIX . "projects
			WHERE " . $dosql . "
			ORDER BY user_id ASC
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				if ($res['status'] == 'open')
				{
					$ilance->categories->build_category_count($res['cid'], 'subtract', "admin removing multiple listings from admincp: subtracting increment count category id $res[cid]");
				}
				$ilance->common_listing->physically_remove_listing(intval($res['project_id']));
				if ($res['project_state'] == 'product')
				{
					$url = HTTP_SERVER . $ilpage['merch'] . '?id=' . $res['project_id'];
					$emailnotice .= "$count. Item listing: $res[project_title] (#$res[project_id]) - $url";
				}
				else if ($res['project_state'] == 'service')
				{
					$url = HTTP_SERVER . $ilpage['rfp'] . '?id=' . $res['project_id'];
					$emailnotice .= "$count. Job listing: $res[project_title] (#$res[project_id]) - $url";
				}
		    
				($apihook = $ilance->api('admincp_action_remove_foreach')) ? eval($apihook) : false;
		    
				$count++;
			}
		}
		if (!empty($emailnotice))
		{
			$ilance->email->mail = SITE_EMAIL;
			$ilance->email->slng = fetch_site_slng();
			$ilance->email->get('moderate_auction_unverified');
			$ilance->email->set(array (
			    '{{listingsremoved}}' => $emailnotice,
			));
			$ilance->email->send();
		}
		log_event($_SESSION['ilancedata']['user']['userid'], $ilpage['distribution'], $ilance->GPC['cmd'], '', $ilance->GPC['return'], '{_removed_all} ' . $count);
		print_action_success('{_the_selected_listings_were_removed}', $ilance->GPC['return']);
		exit();
	}
	else if (!isset($ilance->GPC['subcmd']) OR isset($ilance->GPC['do']) AND $ilance->GPC['do'] != '_update-auction')
	{
		$show['update_auction'] = false;
		$show['no_update_auction'] = true;
		$dosql = $dosql2 = $dosql3 = $extraquery = $extraquery2 = $extraquery3 = '';
	
		($apihook = $ilance->api('admincp_auction_management_overview_start')) ? eval($apihook) : false;
	
		if (isset($ilance->GPC['viewtype']) AND !empty($ilance->GPC['viewtype']))
		{
			$viewtype = $ilance->GPC['viewtype'];
		}
		if (isset($ilance->GPC['page3']) AND $ilance->GPC['page3'] > 0)
		{
			$pagetype = 'page3';
			$page = intval($ilance->GPC['page3']);
		}
		else if (isset($ilance->GPC['page2']) AND $ilance->GPC['page2'] > 0)
		{
			$pagetype = 'page2';
			$page = intval($ilance->GPC['page2']);
		}
		else if (isset($ilance->GPC['page']) AND $ilance->GPC['page'] > 0)
		{
			$pagetype = 'page';
			$page = intval($ilance->GPC['page']);
		}
		else
		{
			$pagetype = 'page';
			$page = 1;
		}
		if (!isset($ilance->GPC['page3']) OR isset($ilance->GPC['page3']) AND $ilance->GPC['page3'] <= 0)
		{
			$ilance->GPC['page3'] = 1;
		}
		else
		{
			$ilance->GPC['page3'] = intval($ilance->GPC['page3']);
		}
		if (isset($ilance->GPC['orderby']) AND !empty($ilance->GPC['orderby']) AND $ilance->GPC['viewtype'] == 'moderate')
		{
			$ordersort = strip_tags($ilance->GPC['orderby']);
		}
		else
		{
			$ordersort = 'DESC';
		}
		$maxrowsdisplay = (isset($ilance->GPC['pp']) AND is_numeric($ilance->GPC['pp'])) ? intval($ilance->GPC['pp']) : $ilconfig['globalfilters_maxrowsdisplay'];
		$orderlimit3 = ' ORDER BY project_title ' . $ordersort . ' LIMIT ' . (($ilance->GPC['page3'] - 1) * $maxrowsdisplay) . ',' . $maxrowsdisplay;
		if (isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0 AND $ilance->GPC['viewtype'] == 'moderate')
		{
			$dosql = " AND project_id = '" . intval($ilance->GPC['project_id']) . "' ";
		}
		if (isset($ilance->GPC['user_id']) AND $ilance->GPC['user_id'] > 0 AND $ilance->GPC['viewtype'] == 'moderate')
		{
			$dosql .= " AND user_id = '" . intval($ilance->GPC['user_id']) . "' ";
		}
		if (isset($ilance->GPC['project_details']) AND $ilance->GPC['project_details'] != "" AND $ilance->GPC['viewtype'] == 'moderate')
		{
			$dosql .= " AND project_details = '" . $ilance->db->escape_string($ilance->GPC['project_details']) . "' ";
		}
		if (isset($ilance->GPC['project_title']) AND $ilance->GPC['project_title'] != "" AND $ilance->GPC['viewtype'] == 'moderate')
		{
			$dosql .= " AND project_title LIKE '%" . $ilance->db->escape_string($ilance->GPC['project_title']) . "%' ";
		}
		if (isset($ilance->GPC['status']) AND $ilance->GPC['status'] != "" AND $ilance->GPC['viewtype'] == 'moderate')
		{
			$dosql .= " AND status = '" . $ilance->db->escape_string($ilance->GPC['status']) . "' ";
			$dosql .= ($ilance->GPC['status'] == 'expired') ? "AND date_end < '" . DATETIME24H . "'" : $dosql;
		}
		if (isset($ilance->GPC['cid']) AND $ilance->GPC['cid'] != "" AND $ilance->GPC['viewtype'] == 'moderate')
		{
			$dosql .= " AND cid = '" . $ilance->db->escape_string($ilance->GPC['cid']) . "' ";
		}
		if (empty($dosql))
		{
			$dosql = '';
		}
		if (empty($ilance->GPC['status']))
		{
			$ilance->GPC['status'] = '';
		}
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "projects
			WHERE visible = '0'
			$dosql
			$extraquery
			$orderlimit3
		");
		$sql2 = $ilance->db->query("
			SELECT project_id
			FROM " . DB_PREFIX . "projects
			WHERE visible = '0'
			$dosql
			$extraquery
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$show['no_moderateauctions'] = false;
			$row_count = 0;
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				if (isset($ilance->GPC['page3']) AND $ilance->GPC['page3'] != '')
				{
					$res['pagetype'] = 'page3';
				     $res['page'] = intval($ilance->GPC['page3']);
				}
				$res['project_title'] = print_string_wrap(stripslashes($res['project_title']), 25);
				$res['r3'] = '<input type="checkbox" name="project_id[]" value="' . $res['project_id'] . '" />';
				$res['added'] = print_date($res['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				$res['owner'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $res['user_id'] . '">' . fetch_user('username', $res['user_id']) . '</a>';
				$res['category'] = '<strong>' . $ilance->categories->title(fetch_site_slng(), $res['cid']) . '</strong>';
				$res['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $res['user_id'], $res['project_id'], 1);
				$res['escrow'] = $ilance->escrow->status($res['project_id']);
				$res['auctiontype'] = ucfirst($res['project_details']);
				$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				$res['type'] = $res['project_state'];
				$res['insertionfee'] = ($res['insertionfee'] > 0) ? $ilance->currency->format($res['insertionfee']) : '-';
				$moderateauctions[] = $res;
				$row_count++;
			}
			$numbermoderation = $ilance->db->num_rows($sql2);
		}
		else
		{
		    $numbermoderation = 0;
		    $show['no_moderateauctions'] = true;
		}
		$moderateprevnext = $extraquery2 = '';
		$maxrowsdisplay = (isset($ilance->GPC['pp']) AND is_numeric($ilance->GPC['pp'])) ? intval($ilance->GPC['pp']) : $ilconfig['globalfilters_maxrowsdisplay'];
		if ($show['no_moderateauctions'] == false)
		{
			$ilance->GPC['project_id'] = isset($ilance->GPC['project_id']) ? intval($ilance->GPC['project_id']) : 0;
			$ilance->GPC['user_id'] = isset($ilance->GPC['user_id']) ? intval($ilance->GPC['user_id']) : 0;
			$ilance->GPC['project_details'] = isset($ilance->GPC['project_details']) ? $ilance->GPC['project_details'] : '';
			$ilance->GPC['orderby'] = isset($ilance->GPC['orderby']) ? $ilance->GPC['orderby'] : '';
			$moderateprevnext = print_pagnation($numbermoderation, $maxrowsdisplay, $ilance->GPC['page3'], ($ilance->GPC['page3'] - 1) * $maxrowsdisplay, $ilpage['distribution'] . "?cmd=auctions&amp;viewtype=moderate&amp;project_id=" . intval($ilance->GPC['project_id']) . "&amp;user_id=" . (int) $ilance->GPC['user_id'] . "&amp;project_details=" . $ilance->GPC['project_details'] . "&amp;orderby=" . $ilance->GPC['orderby'], 'page3');
		}
		if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
		{
			$ilance->GPC['page'] = 1;
		}
		else
		{
			$ilance->GPC['page'] = intval($ilance->GPC['page']);
		}
		$ordersort = 'DESC';
		if (isset($ilance->GPC['orderby']) AND !empty($ilance->GPC['orderby']) AND $ilance->GPC['viewtype'] == 'service')
		{
			$ordersort = strip_tags($ilance->GPC['orderby']);
		}
		$orderlimit = ' ORDER BY project_title ' . $ordersort . ' LIMIT ' . (($ilance->GPC['page'] - 1) * $maxrowsdisplay) . ',' . $maxrowsdisplay;
		if (isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0 AND $ilance->GPC['viewtype'] == 'service')
		{
			$dosql2 = " AND project_id = '" . intval($ilance->GPC['project_id']) . "' ";
		}
		if (isset($ilance->GPC['user_id']) AND $ilance->GPC['user_id'] > 0 AND $ilance->GPC['viewtype'] == 'service')
		{
			$dosql2 .= " AND user_id = '" . intval($ilance->GPC['user_id']) . "' ";
		}
		if (isset($ilance->GPC['project_details']) AND !empty($ilance->GPC['project_details']) AND $ilance->GPC['viewtype'] == 'service')
		{
			$dosql2 .= " AND project_details = '" . $ilance->db->escape_string($ilance->GPC['project_details']) . "' ";
			$ilance->GPC['auctiontype'] = $ilance->GPC['project_details'];
		}
		if (isset($ilance->GPC['project_title']) AND $ilance->GPC['project_title'] != "" AND $ilance->GPC['viewtype'] == 'service')
		{
			$dosql2 .= " AND project_title LIKE '%" . $ilance->db->escape_string($ilance->GPC['project_title']) . "%' ";
		}
		if (isset($ilance->GPC['status']) AND !empty($ilance->GPC['status']) AND $ilance->GPC['viewtype'] == 'service')
		{
			$dosql2 .= " AND status = '" . $ilance->db->escape_string($ilance->GPC['status']) . "' ";
			$dosql2 .= ($ilance->GPC['status'] == 'expired') ? "AND date_end < '" . DATETIME24H . "'" : $dosql2;
		}
		if (isset($ilance->GPC['cid']) AND $ilance->GPC['cid'] != "" AND $ilance->GPC['viewtype'] == 'service')
		{
			$dosql2 .= " AND cid = '" . $ilance->db->escape_string($ilance->GPC['cid']) . "' ";
		}
		if (!isset($dosql2))
		{
			$dosql2 = '';
		}
		if (empty($ilance->GPC['status']))
		{
			$ilance->GPC['status'] = '';
		}
		$sqlservice = $ilance->db->query("
			SELECT *, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
			FROM " . DB_PREFIX . "projects
			WHERE visible = '1'
				AND project_state = 'service'
				$dosql2
				$extraquery2
				$orderlimit
		");
		$sqlservice2 = $ilance->db->query("
			SELECT *, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
			FROM " . DB_PREFIX . "projects
			WHERE visible = '1'
				AND project_state = 'service'
				$dosql2
				$extraquery2
		");
		if ($ilance->db->num_rows($sqlservice) > 0)
		{
			$show['no_serviceauctions'] = false;
			$row_count = 0;
			while ($res = $ilance->db->fetch_array($sqlservice, DB_ASSOC))
			{
				$res['r1'] = '<input class="service_checkbox" type="checkbox" name="project_id[]" value="' . $res['project_id'] . '" />';
				$res['added'] = print_date($res['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				$res['owner'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $res['user_id'] . '"">' . fetch_user('username', $res['user_id']) . '</a>';
				$res['project_title'] = print_string_wrap(stripslashes($res['project_title']), 25);
				$res['awarded'] = $ilance->auction->fetch_auction_winner($res['project_id']);
				$res['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $res['user_id'], $res['project_id'], 1);
				$res['escrow'] = $ilance->escrow->status($res['project_id']);
				$res['auctiontype'] = ucfirst($res['project_details']);
				if ($res['status'] == 'wait_approval')
				{
					$res['status'] = '{_pending_acceptance}';
				}
				else if ($res['status'] == 'approval_accepted')
				{
					$res['status'] = '{_accepted}';
				}
				else
				{
					$res['status'] = ucwords($res['status']);
				}
				$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				$res['timeleft'] = $ilance->auction->auction_timeleft(false, $res['date_starts'], $res['mytime'], $res['starttime']);
				if ($res['insertionfee'] > 0)
				{
					$res['insertionfee'] = $ilance->currency->format($res['insertionfee']);
				}
				else
				{
					$res['insertionfee'] = '-';
				}
				if ($res['fvf'] > 0)
				{
					$res['fvf'] = $ilance->currency->format($res['fvf']);
				}
				else
				{
					$res['fvf'] = '-';
				}
				if ($res['bids'] == 0)
				{
					$res['bids'] = '-';
				}
				$serviceauctions[] = $res;
				$row_count++;
			}
			$numberservice = $ilance->db->num_rows($sqlservice2);
		}
		else
		{
			$numberservice = '0';
			$show['no_serviceauctions'] = true;
		}
		if ($show['no_serviceauctions'] == false)
		{
			if (!isset($ilance->GPC['project_id']))
			{
				$ilance->GPC['project_id'] = 0;
			}
			if (!isset($ilance->GPC['project_details']))
			{
				$ilance->GPC['project_details'] = '';
			}
			if (!isset($ilance->GPC['user_id']))
			{
				$ilance->GPC['user_id'] = 0;
			}
			if (!isset($ilance->GPC['orderby']))
			{
				$ilance->GPC['orderby'] = '';
			}
			$serviceprevnext = print_pagnation($numberservice, $maxrowsdisplay, $ilance->GPC['page'], ($ilance->GPC['page'] - 1) * $maxrowsdisplay, $ilpage['distribution'] . "?cmd=auctions&amp;viewtype=service&amp;project_id=" . intval($ilance->GPC['project_id']) . "&amp;user_id=" . intval($ilance->GPC['user_id']) . "&amp;project_details=" . $ilance->GPC['project_details'] . "&amp;orderby=" . $ilance->GPC['orderby'] . "&amp;status=" . $ilance->GPC['status'] . "");
		}
		else
		{
			$serviceprevnext = '';
		}
		if (!isset($ilance->GPC['page2']) OR isset($ilance->GPC['page2']) AND $ilance->GPC['page2'] <= 0)
		{
			$ilance->GPC['page2'] = 1;
		}
		else
		{
			$ilance->GPC['page2'] = intval($ilance->GPC['page2']);
		}
		$ordersort = 'DESC';
		if (isset($ilance->GPC['orderby']) AND $ilance->GPC['orderby'] != "" AND $ilance->GPC['viewtype'] == 'product')
		{
			$ordersort = strip_tags($ilance->GPC['orderby']);
		}
		$maxrowsdisplay = (isset($ilance->GPC['pp']) AND is_numeric($ilance->GPC['pp'])) ? intval($ilance->GPC['pp']) : $ilconfig['globalfilters_maxrowsdisplay'];
		$orderlimit = ' ORDER BY project_title ' . $ordersort . ' LIMIT ' . (($ilance->GPC['page2'] - 1) * $maxrowsdisplay) . ',' . $maxrowsdisplay;
		if (isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0 AND $ilance->GPC['viewtype'] == 'product')
		{
			$dosql3 = " AND project_id = '" . intval($ilance->GPC['project_id']) . "' ";
		}
		if (isset($ilance->GPC['user_id']) AND $ilance->GPC['user_id'] > 0 AND $ilance->GPC['viewtype'] == 'product')
		{
			$dosql3 .= " AND user_id = '" . intval($ilance->GPC['user_id']) . "' ";
		}
		if (isset($ilance->GPC['project_details']) AND $ilance->GPC['project_details'] != "" AND $ilance->GPC['viewtype'] == 'product')
		{
			$dosql3 .= " AND project_details = '" . $ilance->db->escape_string($ilance->GPC['project_details']) . "' ";
		}
		if (isset($ilance->GPC['project_details2']) AND $ilance->GPC['project_details2'] != "" AND $ilance->GPC['viewtype'] == 'product')
		{
			$dosql3 .= " AND filtered_auctiontype = '" . $ilance->db->escape_string($ilance->GPC['project_details2']) . "' ";
		}
		if (isset($ilance->GPC['project_title']) AND $ilance->GPC['project_title'] != "" AND $ilance->GPC['viewtype'] == 'product')
		{
			$dosql3 .= " AND project_title LIKE '%" . $ilance->db->escape_string($ilance->GPC['project_title']) . "%' ";
		}
		if (isset($ilance->GPC['status']) AND $ilance->GPC['status'] != "" AND $ilance->GPC['viewtype'] == 'product')
		{
			$dosql3 .= " AND status = '" . $ilance->db->escape_string($ilance->GPC['status']) . "' ";
			$dosql3 .= ($ilance->GPC['status'] == 'expired') ? "AND date_end < '" . DATETIME24H . "'" : $dosql3;
		}
		if (isset($ilance->GPC['cid']) AND $ilance->GPC['cid'] != "" AND $ilance->GPC['viewtype'] == 'product')
		{
			$dosql3 .= " AND cid = '" . $ilance->db->escape_string($ilance->GPC['cid']) . "' ";
		}
		if (isset($ilance->GPC['image']) AND $ilance->GPC['image'] != "" AND $ilance->GPC['viewtype'] == 'product')
		{
			if ($ilance->GPC['image'] == '1')
			{
				 $dosql3 .= " AND (hasimage = '1' OR hasimageslideshow = '1') ";
			}
			else if ($ilance->GPC['image'] == '2')
			{
				 $dosql3 .= " AND (hasimage = '0' AND hasimageslideshow = '0') ";
			}
			else
			{
				 $dosql3 .= "";
			}
		}
		if (!isset($dosql3))
		{
			$dosql3 = '';
		}
		$sqlproduct = $ilance->db->query("
			SELECT *, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
			FROM " . DB_PREFIX . "projects
			WHERE visible = '1'
				AND project_state = 'product'
				$dosql3
				$extraquery3
				$orderlimit
		");
		$sqlproduct2 = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "projects
			WHERE visible = '1'
				AND project_state = 'product'
				$dosql3
				$extraquery3
		");
		if ($ilance->db->num_rows($sqlproduct) > 0)
		{
			$show['no_productauctions'] = false;
			$row_count = 0;
			while ($res = $ilance->db->fetch_array($sqlproduct, DB_ASSOC))
			{
				$res['photo'] = $ilance->auction->print_item_photo('javascript:void(0)', 'thumb', $res['project_id']);
				$res['r2'] = '<input class="product_checkbox" type="checkbox" name="project_id[]" value="' . $res['project_id'] . '" />';
				$res['merchant'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $res['user_id'] . '"">' . fetch_user('username', $res['user_id']) . '</a>';
				$res['status'] = ucfirst($res['status']);
				$res['project_title'] = print_string_wrap(stripslashes($res['project_title']), 25);
				if ($res['filtered_auctiontype'] == 'regular')
				{
					$res['winner'] = $ilance->auction->fetch_auction_winner($res['project_id']);
				}
				else
				{
					$sql_bo = $ilance->db->query("
						SELECT buyer_id
						FROM " . DB_PREFIX . "buynow_orders
						WHERE project_id = '" . intval($res['project_id']) . "'
							AND status != 'cancelled'
							GROUP BY buyer_id
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql_bo) > 0)
					{
						if ($ilance->db->num_rows($sql_bo) == 1)
						{
							$res_bo = $ilance->db->fetch_array($sql_bo);
							$sql_count = $ilance->db->query("
								SELECT COUNT(orderid) AS sold 
								FROM " . DB_PREFIX . "buynow_orders 
								WHERE buyer_id ='" . $res_bo['buyer_id'] . "' 
									AND project_id = '" . intval($res['project_id']) . "'
									AND status != 'cancelled'
							", 0, null, __FILE__, __LINE__);
							$res_count = $ilance->db->fetch_array($sql_count);
							$buynow_count = ($res_count['sold'] == 1) ? '' : ' (' . $res_count['sold'] . ')';
							$res['winner'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $res_bo['buyer_id'] . '">' . fetch_user('username', $res_bo['buyer_id']) . $buynow_count . '</a>';
						}
						else
						{
							$winners = '';
							while ($res_bo = $ilance->db->fetch_array($sql_bo))
							{
								$sql_count = $ilance->db->query("
									SELECT COUNT(orderid) AS sold 
									FROM " . DB_PREFIX . "buynow_orders 
									WHERE buyer_id ='" . $res_bo['buyer_id'] . "' 
										AND project_id = '" . intval($res['project_id']) . "'
										AND status != 'cancelled'
								", 0, null, __FILE__, __LINE__);
								$res_count = $ilance->db->fetch_array($sql_count);
								$buynow_count = ($res_count['sold'] == 1) ? '' : ' (' . $res_count['sold'] . ')';
								$winners .= (empty($winners)) ? fetch_user('username', $res_bo['buyer_id']) . $buynow_count : ', ' . fetch_user('username', $res_bo['buyer_id']) . $buynow_count;
							}
							$res['winner'] = '<a href="javascript:void(0)" onmouseover="Tip(\'' . $winners . '\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a>';
						}
					}
					else
					{
					    $res['winner'] = '-';
					}
				}
				$sold = $ilance->auction_product->fetch_buynow_ordercount($res['project_id']);
				if ($sold > 0)
				{
					$res['sales'] = $sold;
				}
				else
				{
					$res['sales'] = '-';
				}
				$res['escrow'] = $ilance->escrow->status($res['project_id']);
				$res['auctiontype'] = ucfirst($res['project_details']);
				$res['auctiontype2'] = ucfirst($res['filtered_auctiontype']);
				$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				$res['timeleft'] = $ilance->auction->auction_timeleft(false, $res['date_starts'], $res['mytime'], $res['starttime']);
				if ($res['insertionfee'] > 0)
				{
					$res['insertionfee'] = $ilance->currency->format($res['insertionfee']);
				}
				else
				{
					$res['insertionfee'] = '-';
				}
				if ($res['fvf'] > 0)
				{
					$res['fvf'] = $ilance->currency->format($res['fvf']);
				}
				else
				{
					$res['fvf'] = '-';
				}
				if ($res['bids'] == 0)
				{
					$res['bids'] = '-';
				}
				$productauctions[] = $res;
				$row_count++;
			}
			$numberproduct = $ilance->db->num_rows($sqlproduct2);
		}
		else
		{
		    $numberproduct = 0;
		    $show['no_productauctions'] = true;
		}
		$productprevnext = '';
		if ($show['no_productauctions'] == false)
		{
			$productprevnext = print_pagnation($numberproduct, $maxrowsdisplay, $ilance->GPC['page2'], ($ilance->GPC['page2'] - 1) * $maxrowsdisplay, $ilpage['distribution'] . "?cmd=auctions&amp;viewtype=product&amp;project_id=" . (isset($ilance->GPC['project_id']) ? intval($ilance->GPC['project_id']) : 0) . "&amp;user_id=" . (isset($ilance->GPC['user_id']) ? intval($ilance->GPC['user_id']) : 0) . "&amp;project_details=" . (isset($ilance->GPC['project_details']) ? $ilance->GPC['project_details'] : '') . "&amp;orderby=" . (isset($ilance->GPC['orderby']) ? $ilance->GPC['orderby'] : '') . "&amp;status=" . $ilance->GPC['status'] . "", 'page2');
		}
		$ilance->GPC['auctiontype'] = isset($ilance->GPC['auctiontype']) ? $ilance->GPC['auctiontype'] : '';
		$ilance->GPC['project_details2'] = isset($ilance->GPC['project_details2']) ? $ilance->GPC['project_details2'] : '';
		$auction_type_pulldown = $ilance->admincp->auction_details_pulldown($ilance->GPC['auctiontype'], 1, 'service');
		$auction_type_pulldown2 = $ilance->admincp->auction_details_pulldown($ilance->GPC['auctiontype'], 1, 'product');
		$auctiontype_pulldown2 = $ilance->admincp->auction_details_pulldown2($ilance->GPC['project_details2'], 1, 'product');
		$ilance->GPC['status'] = isset($ilance->GPC['status']) ? $ilance->GPC['status'] : 0;
		$auction_status_pulldown = $ilance->admincp->auction_status_pulldown($ilance->GPC['status'], 1, 'service');
		$auction_status_pulldown2 = $ilance->admincp->auction_status_pulldown($ilance->GPC['status'], 1, 'product');
		$picture_pulldown = '<select name="image" id="image" style="font-family: verdana">';
		$picture_pulldown .= '<option value="0">{_all}</option>';
		$picture_pulldown .= '<option value="1">{_show_only_with_images}</option>';
		$picture_pulldown .= '<option value="2">{_show_only_with_no_images}</option>';
		$picture_pulldown .= '</select>';
	}
	else
	{
		header("Location: " . HTTPS_SERVER . (($ilance->GPC['viewtype'] == 'service') ? $ilpage['buying'] . "?cmd=rfp-management&id=" . intval($ilance->GPC['id']) . "&admincp=1" : $ilpage['selling'] . "?cmd=product-management&id=" . intval($ilance->GPC['id']) . "&admincp=1"));
		exit();
	}
}
$id = 0;
if (isset($ilance->GPC['id']))
{
	$id = intval($ilance->GPC['id']);
}
$project_id = $user_id = $project_title = $cid = $pp = '';
if (isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0)
{
	$project_id = intval($ilance->GPC['project_id']);
}
if (isset($ilance->GPC['user_id']) AND $ilance->GPC['user_id'] > 0)
{
	$user_id = intval($ilance->GPC['user_id']);
}
if (isset($ilance->GPC['project_title']) AND !empty($ilance->GPC['project_title']))
{
	$project_title = $ilance->GPC['project_title'];
}
if (isset($ilance->GPC['cid']) AND !empty($ilance->GPC['cid']))
{
	$cid = $ilance->GPC['cid'];
}
if (isset($ilance->GPC['pp']) AND !empty($ilance->GPC['pp']))
{
	$pp = $ilance->GPC['pp'];
}
// #### AUCTION SETTINGS TAB ###########################################
$global_auctionoptions = $ilance->admincp->construct_admin_input('globalauctionsettings', $ilpage['distribution']);
$perpage_array = array ('5', '10', '15', '25', '50', '75', '100', '125', '150', '175', '200', '225', '250', '500', '750', '1000', '2000', '3000', '4000', '5000', '10000');
$perpage_pulldown = '<select name="pp" id="pp" style="font-family: verdana">';
foreach ($perpage_array as $key => $value)
{
	$sel = ($maxrowsdisplay == $value) ? 'selected="selected"' : '';
	$perpage_pulldown .= '<option value="' . $value . '" ' . $sel . '>' . $value . '</option>';
}
$perpage_pulldown .= '</select>';
$pprint_array = array ('perpage_pulldown', 'cid', 'auctiontype_pulldown2', 'project_title', 'user_id', 'project_id', 'auction_status_pulldown2', 'auction_type_pulldown2', 'wysiwyg_area', 'global_auctionoptions', 'configuration_moderationsystem', 'project_questions', 'auction_status_pulldown', 'productprevnext', 'numberproduct', 'pagetype', 'page', 'viewtype', 'id', 'auction_type_pulldown', 'numbermoderation', 'serviceprevnext', 'moderateprevnext', 'numberservice', 'id', 'picture_pulldown');

($apihook = $ilance->api('admincp_auctions_end')) ? eval($apihook) : false;

$ilance->template->fetch('main', 'auctions.html', 1);
$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
$ilance->template->parse_loop('main', 'bid_results_rows');
$ilance->template->parse_loop('main', 'serviceescrows');
$ilance->template->parse_loop('main', 'productescrows');
$ilance->template->parse_loop('main', 'moderateauctions');
$ilance->template->parse_loop('main', 'serviceauctions');
$ilance->template->parse_loop('main', 'productauctions');
$ilance->template->parse_loop('main', 'updateserviceauction');
if (!isset($updateserviceauction))
{
	$updateserviceauction = array ();
}
@reset($updateserviceauction);
while ($i = @each($updateserviceauction))
{
	$ilance->template->parse_loop('main', 'purchase_now_activity' . $i['value']['project_id']);
}
$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', $pprint_array);
exit();

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>