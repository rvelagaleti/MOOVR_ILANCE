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
		'tabfx',
		'inline_edit'
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
	'watchlist'
);

// #### setup script location ##################################################
define('LOCATION', 'watchlist');

// #### require backend ########################################################
require_once('./functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[watchlist]" => $ilcrumbs["$ilpage[watchlist]"]);
$show['widescreen'] = false;
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
{
	if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'addtowatchlist') == 'no')
	{
		$area_title = '{_access_denied_to_watchlist_resource}';
		$page_title = SITE_NAME . ' - {_access_denied_to_watchlist_resource}';
		$navcrumb = array();
		$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
		$navcrumb["$ilpage[watchlist]"] = '{_watchlist}';
		$navcrumb[""] = '{_access_denied_to_watchlist_resource}';
		print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource} <span class="blue"><a href="' . $ilpage['subscription'] . '"><strong>{_click_here}</strong></a></span>.<div style="padding-top:9px" class="gray">{_additionally_you_may_be_seeing_this_message_due_to_an_unpaid}</div>', $ilpage['subscription'], ucwords('{_subscription_manager}'), fetch_permission_name('addtowatchlist'));
		exit();
	}
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-watch-cmd' AND isset($ilance->GPC['state']))
	{
		// remove selected auctions
		if ($ilance->GPC['state'] == 'auction' AND isset($ilance->GPC['rfpcmd']) AND $ilance->GPC['rfpcmd'] == 'delete')
		{
			if (isset($ilance->GPC['rfpcmd']) AND $ilance->GPC['rfpcmd'] == 'delete')
			{
				if (isset($ilance->GPC['project_id']))
				{
					foreach ($ilance->GPC['project_id'] AS $value)
					{
						$ilance->db->query("
							DELETE FROM " . DB_PREFIX . "watchlist
							WHERE watching_project_id = '" . intval($value) . "'
								AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
								AND state = '" . $ilance->db->escape_string($ilance->GPC['state']) . "'
						");
					}
					refresh($ilpage['watchlist']);
					exit();
				}
				else
				{
					refresh($ilpage['watchlist']);
					exit();
				}
			}
		}
		else if ($ilance->GPC['state'] == 'auction' AND isset($ilance->GPC['rfpcmd']) AND $ilance->GPC['rfpcmd'] == 'delete_all')
		{
			if (isset($ilance->GPC['rfpcmd']) AND $ilance->GPC['rfpcmd'] == 'delete_all')
			{
				$ilance->db->query("
					DELETE FROM " . DB_PREFIX . "watchlist 
					WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' 
						AND state = '" . $ilance->db->escape_string($ilance->GPC['state']) . "'
				");
				refresh($ilpage['watchlist']);
				exit();
			}
		}
		else if ($ilance->GPC['state'] == 'buyer' AND isset($ilance->GPC['buyercmd']) AND ($ilance->GPC['buyercmd'] == 'delete' OR $ilance->GPC['buyercmd'] == 'invite' OR $ilance->GPC['buyercmd'] == 'delete_all'))
		{
			if (isset($ilance->GPC['buyercmd']) AND $ilance->GPC['buyercmd'] == 'watchlist')
			{
				if (isset($ilance->GPC['vendor_id']))
				{
					$watchtype = $ilance->GPC['state'];
					$comment = "";
					foreach ($ilance->GPC['vendor_id'] AS $value)
					{
						$ilance->watchlist->insert_item($_SESSION['ilancedata']['user']['userid'], intval($value), $watchtype, $comment);
					}
					refresh($ilpage['watchlist']);
					exit();
				}
				else
				{
					refresh($ilpage['watchlist']);
					exit();
				}
			}
			else if ($ilance->GPC['buyercmd'] == 'delete')
			{
				if (isset($ilance->GPC['vendor_id']))
				{
					foreach ($ilance->GPC['vendor_id'] AS $value)
					{
						$ilance->db->query("
							DELETE FROM " . DB_PREFIX . "watchlist
							WHERE watching_user_id = '" . intval($value) . "'
								AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
								AND state = '" . $ilance->db->escape_string($ilance->GPC['state']) . "'
						");
					}
					refresh($ilpage['watchlist']);
					exit();
				}
				else
				{
					refresh($ilpage['watchlist']);
					exit();
				}
			}
			else if ($ilance->GPC['buyercmd'] == 'delete_all')
			{
				$ilance->db->query("
					DELETE FROM " . DB_PREFIX . "watchlist
					WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						AND state = '" . $ilance->db->escape_string($ilance->GPC['state']) . "'
				");
				refresh($ilpage['watchlist']);
				exit();
			}
			else if ($ilance->GPC['buyercmd'] == 'invite')
			{
				if (isset($ilance->GPC['vendor_id']))
				{
					if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'inviteprovider') == 'no')
					{
						$area_title = '{_provider_invitation_denied_upgrade_subscription}';
						$page_title = SITE_NAME . ' - {_provider_invitation_denied_upgrade_subscription}';
						print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource} <a href="' . $ilpage['subscription'] . '"><strong>{_click_here}</strong></a>', $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('inviteprovider'));
						exit();
					}
					
					$area_title = '{_inviting_a_provider_to_a_new_or_existing_rfp}';
					$page_title = SITE_NAME . ' - {_inviting_a_provider_to_a_new_or_existing_rfp}';
					
					$hidden_invitations = "";
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
							");
							if ($ilance->db->num_rows($sql_vendor) > 0)
							{
								$res_vendor = $ilance->db->fetch_array($sql_vendor, DB_ASSOC);
								$invitationid = $res_vendor['user_id'];
								$provider .= print_username($invitationid, 'href') . ' ';
								$hidden_invitations .= '<input type="hidden" name="invitationid[]" value="' . $invitationid . '" />';
							}
						}
					}
					else
					{
						$sql_vendor = $ilance->db->query("
							SELECT user_id
							FROM " . DB_PREFIX . "users
							WHERE user_id = '" . intval($ilance->GPC['vendor_id'][0]) . "'
								AND status = 'active'
						");
						if ($ilance->db->num_rows($sql_vendor) > 0)
						{
							$res_vendor = $ilance->db->fetch_array($sql_vendor, DB_ASSOC);
							$invitationid = $res_vendor['user_id'];
							$provider .= print_username($invitationid, 'href') . ' ';
							$hidden_invitations .= '<input type="hidden" name="invitationid" value="' . $invitationid . '">';
						}
					}
					$sql_projects = $ilance->db->query("
						SELECT project_id, project_title, bids, date_end
						FROM " . DB_PREFIX . "projects
						WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
							AND status = 'open'
							AND project_state = 'product'
					");
					$invite_pulldown = '<select name="project_id">';
					$invite_pulldown .= '<option value="">{_select_rfp}:</option>';
					if ($ilance->db->num_rows($sql_projects) > 0)
					{
						while ($res = $ilance->db->fetch_array($sql_projects, DB_ASSOC))
						{
							$invite_pulldown .= '<option value="' . $res['project_id'] . '">{_item} (' . $res['project_id'] . '): ' . short_string(stripslashes($res['project_title']), 35) . ' ({_bids}: ' . $res['bids'] . ') ({_ends}: ' . print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0) . ')</option>';
						}
					}
					else
					{
						$invite_pulldown .= '<option value="">--- {_no_rfps_available} ---</option>';
					}
					$invite_pulldown .= '</select>';
					
					$pprint_array = array('hidden_invitations','invite_pulldown','provider','project_user_id','cid','currency_id','project_id','portfolio_id','bid_id','filehash','category_id','user_id','attachtype','max_filesize','category','current_proposal','current_bidamount','current_estimate_days','delivery_pulldown','currency','title','description','bid_controls','buyer_incomespent','buyer_stars','project_title','description','project_type','project_details','project_distance','project_id','bid_details','pmb','project_buyer','projects_posted','projects_awarded','project_currency','project_attachment','distance','subcategory_name','text','prevnext','prevnext2');
					
					$ilance->template->fetch('main', 'rfp_invitetobid.html');
					$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
					$ilance->template->parse_if_blocks('main');
					$ilance->template->pprint('main', $pprint_array);
					exit();                                                
				}
				else
				{
					refresh($ilpage['watchlist']);
					exit();
				}
			}
		}
		else if ($ilance->GPC['state'] == 'mprovider' AND isset($ilance->GPC['action']) AND ($ilance->GPC['action'] == 'delete' OR $ilance->GPC['action'] == 'invite' OR $ilance->GPC['action'] == 'delete_all'))
		{
			if ($ilance->GPC['action'] == 'watchlist')
			{
				if (isset($ilance->GPC['vendor_id']))
				{
					$watchtype = $ilance->GPC['state'];
					$comment = "";
					foreach ($ilance->GPC['vendor_id'] AS $value)
					{
						$ilance->watchlist->insert_item($_SESSION['ilancedata']['user']['userid'], intval($value), $watchtype, $comment);
					}
					refresh($ilpage['watchlist']);
					exit();
				}
				else
				{
					refresh($ilpage['watchlist']);
					exit();
				}
			}
			else if ($ilance->GPC['action'] == 'delete')
			{
				if (isset($ilance->GPC['vendor_id']))
				{
					foreach ($ilance->GPC['vendor_id'] AS $value)
					{
						$ilance->db->query("
							DELETE FROM " . DB_PREFIX . "watchlist
							WHERE watching_user_id = '" . intval($value) . "'
								AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
								AND state = '" . $ilance->db->escape_string($ilance->GPC['state']) . "'
						");
					}
					refresh($ilpage['watchlist']);
					exit();
				}
				else
				{
					refresh($ilpage['watchlist']);
					exit();
				}
			}
			else if ($ilance->GPC['action'] == 'delete_all')
			{
				$ilance->db->query("
					DELETE FROM " . DB_PREFIX . "watchlist
					WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						AND state = '" . $ilance->db->escape_string($ilance->GPC['state']) . "'
				");
				refresh($ilpage['watchlist']);
				exit();
				
			}
			else if ($ilance->GPC['action'] == 'invite')
			{
				if (isset($ilance->GPC['vendor_id']))
				{
					if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'inviteprovider') == 'no')
					{
						$area_title = '{_provider_invitation_denied_upgrade_subscription}';
						$page_title = SITE_NAME . ' - {_provider_invitation_denied_upgrade_subscription}';
						print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource} <a href="' . $ilpage['subscription'] . '"><strong>{_click_here}</strong></a>', $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('inviteprovider'));
						exit();
					}
					$area_title = '{_inviting_a_provider_to_a_new_or_existing_rfp}';
					$page_title = SITE_NAME . ' - {_inviting_a_provider_to_a_new_or_existing_rfp}';
					$hidden_invitations = $provider = '';
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
							");
							if ($ilance->db->num_rows($sql_vendor) > 0)
							{
								$res_vendor = $ilance->db->fetch_array($sql_vendor, DB_ASSOC);
								$invitationid = $res_vendor['user_id'];
								$provider .= print_username($invitationid, 'href'). ' ';
								$hidden_invitations .= '<input type="hidden" name="invitationid[]" value="' . $invitationid . '" />';
							}
						}
					}
					else
					{
						$sql_vendor = $ilance->db->query("
							SELECT user_id
							FROM " . DB_PREFIX . "users
							WHERE user_id = '" . intval($ilance->GPC['vendor_id'][0]) . "'
								AND status = 'active'
						");
						if ($ilance->db->num_rows($sql_vendor) > 0)
						{
							$res_vendor = $ilance->db->fetch_array($sql_vendor, DB_ASSOC);
							$invitationid = $res_vendor['user_id'];
							$provider .= print_username($invitationid, 'href') . ' ';
							$hidden_invitations .= '<input type="hidden" name="invitationid" value="' . $invitationid . '">';
						}
					}
					
					$sql_projects = $ilance->db->query("
						SELECT project_id, project_title, bids, date_end
						FROM " . DB_PREFIX . "projects
						WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' 
							AND status = 'open'
							AND project_state = 'product'
					");
					$invite_pulldown = '<select name="project_id">';
					$invite_pulldown .= '<option value="">{_select_rfp}:</option>';
					if ($ilance->db->num_rows($sql_projects) > 0)
					{
						while ($res = $ilance->db->fetch_array($sql_projects, DB_ASSOC))
						{
							$invite_pulldown .= '<option value="' . $res['project_id'] . '">{_item} (' . $res['project_id'] . '): ' . short_string(stripslashes($res['project_title']), 35) . ' ({_bids}: ' . $res['bids'] . ') ({_ends}: ' . print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0) . ')</option>';
						}
					}
					else
					{
						$invite_pulldown .= '<option value="">{_no_rfps_available}</option>';
					}
					$invite_pulldown .= '</select>';
					$pprint_array = array('hidden_invitations','invite_pulldown','provider','project_user_id','cid','currency_id','project_id','portfolio_id','bid_id','filehash','category_id','user_id','attachtype','max_filesize','category','current_proposal','current_bidamount','current_estimate_days','delivery_pulldown','currency','title','description','bid_controls','buyer_incomespent','buyer_stars','project_title','description','project_type','project_details','project_distance','project_id','bid_details','pmb','project_buyer','projects_posted','projects_awarded','project_currency','project_attachment','distance','subcategory_name','text','prevnext','prevnext2');
					$ilance->template->fetch('main', 'rfp_invitetobid.html');
					$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
					$ilance->template->parse_if_blocks('main');
					$ilance->template->pprint('main', $pprint_array);
					exit();
				}
				else
				{
					refresh($ilpage['watchlist']);
					exit();
				}
			}
		}
		else if ($ilance->GPC['state'] == 'sprovider' AND isset($ilance->GPC['action']) AND ($ilance->GPC['action'] == 'delete' OR $ilance->GPC['action'] == 'invite' OR $ilance->GPC['action'] == 'watchlist' OR $ilance->GPC['action'] == 'delete_all'))
		{
			if ($ilance->GPC['action'] == 'watchlist')
			{
				if (isset($ilance->GPC['vendor_id']))
				{
					$watchtype = $ilance->GPC['state'];
					$comment = "";
					foreach($ilance->GPC['vendor_id'] AS $value)
					{
						$ilance->watchlist->insert_item($_SESSION['ilancedata']['user']['userid'], intval($value), $watchtype, $comment);
					}
					refresh($ilpage['watchlist']);
					exit();
				}
				else
				{
					refresh($ilpage['watchlist']);
					exit();
				}
			}
			else if ($ilance->GPC['action'] == 'delete')
			{
				if (isset($ilance->GPC['vendor_id']))
				{
					foreach ($ilance->GPC['vendor_id'] as $value)
					{
						$ilance->db->query("
							DELETE FROM " . DB_PREFIX . "watchlist
							WHERE watching_user_id = '".intval($value)."'
								AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
								AND state = '" . $ilance->db->escape_string($ilance->GPC['state']) . "'
						");
					}
					
					refresh($ilpage['watchlist']);
					exit();
				}
				else
				{
					refresh($ilpage['watchlist']);
					exit();
				}
			}
			else if ($ilance->GPC['action'] == 'delete_all')
			{
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "watchlist
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND state = '" . $ilance->db->escape_string($ilance->GPC['state']) . "'
				");
					
				refresh($ilpage['watchlist']);
				exit();
				
			}
			else if ($ilance->GPC['action'] == 'invite')
			{
				if (isset($ilance->GPC['vendor_id']))
				{
					if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'inviteprovider') == 'no')
					{
						$area_title = '{_provider_invitation_denied_upgrade_subscription}';
						$page_title = SITE_NAME . ' - {_provider_invitation_denied_upgrade_subscription}';
						
						print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource} <a href="' . $ilpage['subscription'] . '"><strong>{_click_here}</strong></a>', $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('inviteprovider'));
						exit();
					}
					
					$area_title = '{_inviting_a_provider_to_a_new_or_existing_rfp}';
					$page_title = SITE_NAME . ' - {_inviting_a_provider_to_a_new_or_existing_rfp}';

					$hidden_invitations = "";
					$count = count($ilance->GPC['vendor_id']);
					$provider = '';
					if ($count > 1)
					{
						for ($i=0; $i<$count; $i++)
						{
							$sql_vendor = $ilance->db->query("
								SELECT user_id
								FROM " . DB_PREFIX . "users
								WHERE user_id = '".intval($ilance->GPC['vendor_id'][$i])."'
									AND status = 'active'
							");
							if ($ilance->db->num_rows($sql_vendor) > 0)
							{
								$res_vendor = $ilance->db->fetch_array($sql_vendor, DB_ASSOC);
								$invitationid = $res_vendor['user_id'];
								$provider .= print_username($invitationid, 'href').' ';
								$hidden_invitations .= '<input type="hidden" name="invitationid[]" value="' . $invitationid . '" />';
							}
						}
					}
					else
					{
						$sql_vendor = $ilance->db->query("
							SELECT user_id
							FROM " . DB_PREFIX . "users
							WHERE user_id = '" . intval($ilance->GPC['vendor_id'][0]) . "'
								AND status = 'active'
						");
						if ($ilance->db->num_rows($sql_vendor) > 0)
						{
							$res_vendor = $ilance->db->fetch_array($sql_vendor);
							$invitationid = $res_vendor['user_id'];
							$provider .= print_username($invitationid, 'href'). ' ';
							$hidden_invitations .= '<input type="hidden" name="invitationid[]" value="' . $invitationid . '" />';
						}
					}
					
					$sql_projects = $ilance->db->query("
						SELECT project_id, project_title, bids, date_end
						FROM " . DB_PREFIX . "projects
						WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
							AND status = 'open'
							AND project_state = 'service'
					");
					$invite_pulldown = '<select name="project_id" style="font-family: verdana">';
					$invite_pulldown .= '<option value="">{_select_rfp}:</option>';
					if ($ilance->db->num_rows($sql_projects) > 0)
					{
						while ($res = $ilance->db->fetch_array($sql_projects))
						{
							$invite_pulldown .= '<option value="'.$res['project_id'].'">RFP ('.$res['project_id'].'): '.short_string(stripslashes($res['project_title']),35).' ({_bids}: '.$res['bids'].') ({_ends}: '.print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0).')</option>';
						}
					}
					else
					{
						$invite_pulldown .= '<option value="">--- {_no_rfps_available} ---</option>';
					}
					
					$invite_pulldown .= '</select>';
					
					$pprint_array = array('hidden_invitations','invite_pulldown','provider','project_user_id','cid','currency_id','project_id','portfolio_id','bid_id','filehash','category_id','user_id','attachtype','max_filesize','category','current_proposal','current_bidamount','current_estimate_days','delivery_pulldown','currency','title','description','bid_controls','buyer_incomespent','buyer_stars','project_title','description','project_type','project_details','project_distance','project_id','bid_details','pmb','project_buyer','projects_posted','projects_awarded','project_currency','project_attachment','distance','subcategory_name','text','prevnext','prevnext2');
					
					$ilance->template->fetch('main', 'rfp_invitetobid.html');
					$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
					$ilance->template->parse_if_blocks('main');
					$ilance->template->pprint('main', $pprint_array);
					exit();
				}
				else
				{
					refresh($ilpage['watchlist']);
					exit();
				}
			}
		}
	}
	else
	{
		$area_title = '{_watchlist_menu}';
		$page_title = SITE_NAME . ' - {_watchlist_menu}';
		$navcrumb = array();
		$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
		$navcrumb[""] = '{_watchlist_menu}';
		$headinclude .= "
<script type=\"text/javascript\">
<!--
var urlBase = AJAXURL + '?do=inlineedit&action=watchlistcomment&id=';
var watchlistid = 0;
var type = '';
var value = '';
var imgtag = '';
var watchlisticon = '';
var status = '';
function reset_watchlist_image()
{
        imgtag.src = watchlisticon;
}
function fetch_response(type)
{
        if (xmldata.handler.readyState == 4 && xmldata.handler.status == 200 && xmldata.handler.responseXML)
        {
                response = fetch_tags(xmldata.handler.responseXML, 'status')[0];
                phpstatus = xmldata.fetch_data(response);
                watchiconsrc = fetch_js_object('inline_watchlist_' + xmldata.type + '_' + xmldata.watchlistid).src;
                status = watchiconsrc.match(/\/unchecked.gif/gi);
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
                                watchlisticonsrc = fetch_js_object('inline_watchlist_' + xmldata.type + '_' + xmldata.watchlistid).src;
                                imgtag = fetch_js_object('inline_watchlist_' + xmldata.type + '_' + xmldata.watchlistid);
                                watchlisticon2 = watchlisticonsrc.replace(/unchecked.gif/gi, 'working.gif');
                                imgtag.src = watchlisticon2;
                                watchlisticon = watchlisticonsrc.replace(/unchecked.gif/gi, 'checked.gif');
                                var t = window.setTimeout('reset_watchlist_image()', 700);
                                if (xmldata.type != 'subscribed')
                                {
                                        watchlisticonsrc2 = fetch_js_object('inline_watchlist_subscribed_' + xmldata.watchlistid).src;
                                        imgtag2 = fetch_js_object('inline_watchlist_subscribed_' + xmldata.watchlistid);
                                        
                                        substatus = watchlisticonsrc2.match(/\/unchecked.gif/gi);
                                        if (substatus == '/unchecked.gif')
                                        {
                                                imgtag2.src = watchlisticonsrc2.replace(/unchecked.gif/gi, 'checked.gif');
                                        }
                                }
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
                                watchlisticonsrc = fetch_js_object('inline_watchlist_' + xmldata.type + '_' + xmldata.watchlistid).src;
                                imgtag = fetch_js_object('inline_watchlist_' + xmldata.type + '_' + xmldata.watchlistid);
                                watchlisticon2 = watchlisticonsrc.replace(/checked.gif/gi, 'working.gif');
                                imgtag.src = watchlisticon2;
                                watchlisticon = watchlisticonsrc.replace(/checked.gif/gi, 'unchecked.gif');
                                var t = window.setTimeout('reset_watchlist_image()', 700);
                        }
                        else
                        {
                                alert_js(phpstatus);
                        }
                }
                xmldata.handler.abort();
        }
}
function update_watchlist(type, watchlistid)
{                        
        xmldata = new AJAX_Handler(true);
        watchlistid = urlencode(watchlistid);
        xmldata.watchlistid = watchlistid;
        type = urlencode(type);
        xmldata.type = type;
        watchiconsrc = fetch_js_object('inline_watchlist_' + type + '_' + watchlistid).src;
        status = watchiconsrc.match(/\/unchecked.gif/gi);
        if (status == '/unchecked.gif')
        {
               value = 'on';
        }
        else
        {
               value = 'off';
        }
        xmldata.onreadystatechange(fetch_response);
        xmldata.send(AJAXURL, 'do=watchlist&type=' + type + '&value=' + value + '&watchlistid=' + watchlistid + '&s=' + ILSESSION + '&token=' + ILTOKEN);                        
}
//-->
</script>
";
		// service providers
		$sql_users = $ilance->db->query("
			SELECT watchlistid, user_id, watching_project_id, watching_user_id, watching_category_id, comment, state, lowbidnotify, highbidnotify, hourleftnotify, subscribed
			FROM " . DB_PREFIX . "watchlist
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND watching_user_id != '0'
				AND state = 'sprovider'
			ORDER BY watchlistid DESC
		");
		if ($ilance->db->num_rows($sql_users) > 0)
		{
			require_once(DIR_CORE . 'functions_search.php');
			$show['no_watchlist_sproviders'] = false;
			$row_count = 0;
			while ($row = $ilance->db->fetch_array($sql_users, DB_ASSOC))
			{
				$row['watchlistid'] = $row['watchlistid'];
				$sql_providers = $ilance->db->query("
					SELECT user_id, username
					FROM " . DB_PREFIX . "users
					WHERE user_id = '" . $row['watching_user_id'] . "'
				");
				if ($ilance->db->num_rows($sql_providers) > 0)
				{						
					while ($row2 = $ilance->db->fetch_array($sql_providers, DB_ASSOC))
					{
						$row2['watchlistid'] = $row['watchlistid'];
						$row2['provider'] = print_username($row['watching_user_id'], 'href', 0);
						$row2['provider_plain'] = fetch_user('username', $row['watching_user_id']);
						$row2['id'] = $row2['user_id'];
						$row2['feedback'] = print_username($row['watching_user_id'], 'custom', 0, '', '', $ilance->auction_service->fetch_service_reviews_reported($row['watching_user_id']).' {_reviews}');
						$row2['earnings'] = $ilance->accounting_print->print_income_reported($row['watching_user_id'], 0);
						$row2['online'] = print_online_status($row['watching_user_id']);
						$memberinfo = array();
						$memberinfo = $ilance->feedback->datastore($row['watching_user_id']);
						$row2['rating'] = $memberinfo['rating'] . ' / 5.00';
						$row2['score'] = $memberinfo['pcnt'];                                                        
						unset($memberinfo);
						$row2['hourlyrate'] = $ilance->currency->format(fetch_user('rateperhour', $row['watching_user_id']), fetch_user('currencyid', $row['watching_user_id']));
						$row2['skills'] = print_skills($row['watching_user_id'], $showmaxskills = 500, $nourls = true);
						$sqlattach = $ilance->db->query("
							SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "attachid, filehash, filename, width, height
							FROM " . DB_PREFIX . "attachment
							WHERE user_id = '" . $row2['user_id'] . "' 
								AND visible = '1'
								AND attachtype = 'profile'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sqlattach) > 0)
						{
							$resattach = $ilance->db->fetch_array($sqlattach, DB_ASSOC);
							$row2['picture'] = '<a href="' . print_username($row2['user_id'], 'url') . '"><img src="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . (($ilconfig['globalauctionsettings_seourls']) ? 'i/profile/' . $resattach['filehash'] . '/' . ($resattach['width'] <= 0 ? $ilconfig['attachmentlimit_searchresultsmaxwidth'] : $resattach['width']) . 'x' . ($resattach['height'] <= 0 ? $ilconfig['attachmentlimit_searchresultsmaxheight'] : $resattach['height']) . '_' . $resattach['filename'] : $ilpage['attachment'] . '?cmd=profile&amp;id=' . $resattach['filehash']) . '" border="0" id="' . $resattach['filehash'] . '" alt="' . handle_input_keywords($row2['username']) . '" /></a>';
						}
						else
						{
							$row2['picture'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif" border="0" alt="" id="" height"' . $ilconfig['attachmentlimit_searchresultsmaxheight'] . '" />';
						}
						unset($sqlattach, $resattach);
						$row2['comment'] = str_replace('"', "&#34;", $row['comment']);
						$row2['comment'] = str_replace("'", "&#39;", $row2['comment']);
						$row2['comment'] = str_replace("<", "&#60;", $row2['comment']);
						$row2['comment'] = str_replace(">", "&#61;", $row2['comment']);
						$row2['comment'] = '<strong><span id="phrase' . $row2['watchlistid'] . 'inline" title="{_doubleclick_to_edit}"><span ondblclick="do_inline_edit(' . $row2['watchlistid'] . ', this);">' . handle_input_keywords($row2['comment']) . '</span></span></strong>';
						$row2['action'] = '<input type="checkbox" name="vendor_id[]" value="' . $row['watching_user_id'] . '" />';
						$row2['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
						$watchlist_sproviders[] = $row2;
					}
				}
				$row_count++;
			}
		}
		else
		{
			$show['no_watchlist_sproviders'] = true;
		}
		$show['no_watchlist_mproviders'] = false;
		// sellers
		$sql_users = $ilance->db->query("
			SELECT watchlistid, user_id, watching_project_id, watching_user_id, watching_category_id, comment, state, lowbidnotify, highbidnotify, hourleftnotify, subscribed
			FROM " . DB_PREFIX . "watchlist
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND watching_user_id != '0'
				AND state = 'mprovider'
			ORDER BY watchlistid DESC
		");
		if ($ilance->db->num_rows($sql_users) > 0)
		{
			include_once(DIR_CORE . 'functions_flash.php');
			while ($row = $ilance->db->fetch_array($sql_users, DB_ASSOC))
			{
				$sql_providers = $ilance->db->query("
					SELECT user_id, username
					FROM " . DB_PREFIX . "users
					WHERE user_id = '" . $row['watching_user_id'] . "'
				");
				if ($ilance->db->num_rows($sql_providers) > 0)
				{
					$row_count = 0;
					while ($row2 = $ilance->db->fetch_array($sql_providers))
					{
						$row2['watchlistid'] = $row['watchlistid'];
						$row2['provider'] = print_username($row['watching_user_id'], 'href', 0);
						$row2['provider_plain'] = fetch_user('username', $row['watching_user_id']);
						$row2['id'] = $row2['user_id'];
						$row2['feedback'] = print_username($row['watching_user_id'], 'custom', 0, '', '', $ilance->auction_service->fetch_service_reviews_reported($row['watching_user_id']).' {_reviews}');
						$row2['earnings'] = $ilance->accounting_print->print_income_reported($row['watching_user_id'], 0);
						$row2['online'] = print_online_status($row['watching_user_id']);
						$memberinfo = array();
						$memberinfo = $ilance->feedback->datastore($row['watching_user_id']);
						$row2['rating'] = $memberinfo['rating'] . ' / 5.00';
						$row2['score'] = $memberinfo['pcnt'];                                                        
						unset($memberinfo);
						$sqlattach = $ilance->db->query("
							SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "attachid, filehash, filename, width, height
							FROM " . DB_PREFIX . "attachment
							WHERE user_id = '" . $row2['user_id'] . "' 
								AND visible = '1'
								AND attachtype = 'profile'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sqlattach) > 0)
						{
							$resattach = $ilance->db->fetch_array($sqlattach, DB_ASSOC);
							$row2['picture'] = '<a href="' . print_username($row2['user_id'], 'url') . '"><img src="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . (($ilconfig['globalauctionsettings_seourls'])
								? 'i/profile/' . $resattach['filehash'] . '/' . ($resattach['width'] <= 0 ? $ilconfig['attachmentlimit_searchresultsmaxwidth'] : $resattach['width']) . 'x' . ($resattach['height'] <= 0 ? $ilconfig['attachmentlimit_searchresultsmaxheight'] : $resattach['height']) . '_' . $resattach['filename']
								: $ilpage['attachment'] . '?cmd=profile&amp;id=' . $resattach['filehash']) . '" border="0" id="' . $resattach['filehash'] . '" alt="' . handle_input_keywords($row2['username']) . '" /></a>';
						}
						else
						{
							$row2['picture'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif" border="0" alt="" id="" height"' . $ilconfig['attachmentlimit_searchresultsmaxheight'] . '" />';
						}
						unset($sqlattach, $resattach);
						$row2['comment'] = str_replace('"', "&#34;", $row['comment']);
						$row2['comment'] = str_replace("'", "&#39;", $row2['comment']);
						$row2['comment'] = str_replace("<", "&#60;", $row2['comment']);
						$row2['comment'] = str_replace(">", "&#61;", $row2['comment']);
						$row2['comment'] = '<strong><span id="phrase' . $row2['watchlistid'] . 'inline" title="{_doubleclick_to_edit}"><span ondblclick="do_inline_edit(' . $row2['watchlistid'] . ', this);">' . handle_input_keywords($row2['comment']) . '</span></span></strong>';
						$row2['action'] = '<input type="checkbox" name="vendor_id[]" value="' . $row['watching_user_id'] . '" />';
						$row2['flashgallery'] = print_flash_gallery('favoriteseller', $row['watching_user_id']);
						$row2['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
						$watchlist_mproviders[] = $row2;
						$row_count++;
					}
				}
				else
				{
					$show['no_watchlist_mproviders'] = true;
				}
			}
		}
		else
		{
			$show['no_watchlist_mproviders'] = true;
		}
		// listings
		$sqlauctions = $ilance->db->query("
			SELECT watchlistid, user_id, watching_project_id, watching_user_id, watching_category_id, comment, state, lowbidnotify, highbidnotify, hourleftnotify, subscribed
			FROM " . DB_PREFIX . "watchlist
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND watching_project_id != '0'
				AND mode = '" . $ilance->db->escape_string($ilconfig['globalauctionsettings_auctionstypeenabled']) . "'
			ORDER BY watchlistid DESC
		");
		$row_count = 0;
		if ($ilance->db->num_rows($sqlauctions) > 0)
		{
			while ($row = $ilance->db->fetch_array($sqlauctions, DB_ASSOC))
			{
				$result = $ilance->db->query("
					SELECT *, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . $row['watching_project_id'] . "'
					ORDER BY date_end ASC
				");
				if ($ilance->db->num_rows($result) > 0)
				{
					$rows = $ilance->db->fetch_array($result, DB_ASSOC);
					if ($rows['bids'] == 0)
					{
						$row['bids'] = '0 {_bids_lower}';
					}
					else
					{
						$row['bids'] = $rows['bids'] . ' {_bids_lower}';
					}
					$row['action'] = '<input type="checkbox" name="project_id[]" value="' . $row['watching_project_id'] . '" />';
					$row['comment'] = str_replace('"', "&#34;", $row['comment']);
					$row['comment'] = str_replace("'", "&#39;", $row['comment']);
					$row['comment'] = str_replace("<", "&#60;", $row['comment']);
					$row['comment'] = str_replace(">", "&#61;", $row['comment']);
					$row['comment'] = '<strong><span id="phrase' . $row['watchlistid'] . 'inline" title="{_doubleclick_to_edit}"><span ondblclick="do_inline_edit(' . $row['watchlistid'] . ', this);">' . handle_input_keywords($row['comment']) . '</span></span></strong>';
					//$row['comment'] = handle_input_keywords($row['comment']);
					$sql_attach = $ilance->db->query("
						SELECT filename
						FROM " . DB_PREFIX . "attachment
						WHERE project_id = '" . $rows['project_id'] . "'
							AND user_id = '" . $rows['user_id'] . "'
							AND visible = '1'
					");
					if ($ilance->db->num_rows($sql_attach) > 0)
					{
						$res_attach = $ilance->db->fetch_array($sql_attach, DB_ASSOC);
						$row['attach'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'paperclip.gif" alt="{_attachment}: ' . stripslashes($res_attach['filename']) . '" />';
					}
					else
					{
						$row['attach'] = '';
					}
					if ($rows['project_state'] == 'service')
					{
						$row['auctionpage'] = $ilpage['rfp'];
						$row['sample'] = '';
						$row['title'] = ($ilconfig['globalauctionsettings_seourls'])
							? construct_seo_url('serviceauction', 0, $row['watching_project_id'], handle_input_keywords($rows['project_title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0)
							: '<a href="' . $row['auctionpage'] . '?id=' . $row['watching_project_id'] . '">' . handle_input_keywords($rows['project_title']) . '</a>';
						$row['titleplain'] = handle_input_keywords($rows['project_title']);
					}
					else if ($rows['project_state'] == 'product')
					{
						$row['attach'] = '';
						$row['auctionpage'] = $ilpage['merch'];
						if ($ilconfig['globalauctionsettings_seourls'])
						{
							$url = construct_seo_url('productauctionplain', 0, $rows['project_id'], stripslashes($rows['project_title']), '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
							$row['sample'] = '<span style="float:left; padding-right:12px"><table><tr valign="top"><td><div align="center">' . $ilance->auction->print_item_photo($url, 'thumb', $rows['project_id']) . '</div></td></tr></table></span>';
							$row['title'] = construct_seo_url('productauction', 0, $row['watching_project_id'], handle_input_keywords($rows['project_title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
						}
						else
						{
							$row['sample'] = '<span style="float:left; padding-right:12px"><table><tr valign="top"><td><div align="center">' . $ilance->auction->print_item_photo($ilpage['merch'] . '?id=' . $rows['project_id'], 'thumb', $rows['project_id']) . '</div></td></tr></table></span>';
							$row['title'] = '<a href="' . $row['auctionpage'] . '?id=' . $row['watching_project_id'] . '">' . handle_input_keywords($rows['project_title']) . '</a>';
						}
						$row['titleplain'] = handle_input_keywords($rows['project_title']);
					}
					$row['description'] = $ilance->bbcode->strip_bb_tags($rows['description']);
					$row['description'] = short_string($row['description'], 100);
					$row['description'] = handle_input_keywords($row['description']);
					$row['status'] = $ilance->auction->print_auction_status($rows['status']);
					// is bid placed?
					$sql_bidplaced = $ilance->db->query("
						SELECT bid_id
						FROM " . DB_PREFIX . "project_bids
						WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' 
							AND project_id = '" . $rows['project_id'] . "'
					");
					$row['bidplaced'] = ($ilance->db->num_rows($sql_bidplaced) > 0)
						? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid.gif" border="0" alt="{_you_have_placed_a_bid_on_this_auction}" />'
						: '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_gray.gif" border="0" alt="{_place_a_bid}" />';
					// is realtime auction?
					$row['realtime'] = ($rows['project_details'] == 'realtime')
						? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/ico_clock.png" alt="{_realtime_auction}" border="0" alt="" />'
						: '';
					// auction owner info
					$sql_user_results = $ilance->db->query("
						SELECT city, zip_code, username
						FROM " . DB_PREFIX . "users
						WHERE user_id = '" . $row['user_id'] . "'
					");
					$res_project_user = $ilance->db->fetch_array($sql_user_results, DB_ASSOC);
					$row['city'] = ucfirst($res_project_user['city']);
					$row['zip'] = trim(mb_strtoupper($res_project_user['zip_code']));
					$ilance->categories->build_array($rows['project_state'], $_SESSION['ilancedata']['user']['slng'], 0, true);
					$row['category'] = stripslashes($ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $rows['cid']));
					$currencyid = $rows['currencyid'];
					$bids = $rows['bids'];
					$startprice = $rows['startprice'];
					$currentbid = $rows['currentprice'];
					$row['location'] = $ilance->common_location->print_user_location($row['user_id']);
					if ($rows['date_starts'] > DATETIME24H)
					{
						$dif = $rows['starttime'];
						$ndays = floor($dif / 86400);
						$dif -= $ndays * 86400;
						$nhours = floor($dif / 3600);
						$dif -= $nhours * 3600;
						$nminutes = floor($dif / 60);
						$dif -= $nminutes * 60;
						$nseconds = $dif;
						$sign = '+';
						if ($rows['starttime'] < 0)
						{
							$row['starttime'] = - $row['starttime'];
							$sign = '-';
							$row['currentbid'] = '-';
						}
						if ($sign != '-')
						{
							if ($ndays != '0')
							{
								$project_time_left = $ndays . '{_d_shortform}, ';	
								$project_time_left .= $nhours . '{_h_shortform}+';
							}
							else if ($nhours != '0')
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
						$row['timetostart'] = $project_time_left;
						$row['timeleft'] = '{_starts}: ' . $row['timetostart'];
					}
					else
					{
						$dif = $rows['mytime'];
						$ndays = floor($dif / 86400);
						$dif -= $ndays * 86400;
						$nhours = floor($dif / 3600);
						$dif -= $nhours * 3600;
						$nminutes = floor($dif / 60);
						$dif -= $nminutes * 60;
						$nseconds = $dif;
						$sign = '+';
						if ($rows['mytime'] < 0)
						{
							$row['mytime'] = - $rows['mytime'];
							$sign = '-';
						}
						
						if ($sign == '-')
						{
							$project_time_left = '{_ended}';
							$row['currentbid'] = '-';
						}
						else
						{
							if ($ndays != '0')
							{
								$project_time_left = $ndays . '{_d_shortform}, ';	
								$project_time_left .= $nhours . '{_h_shortform}+';
							}
							else if ($nhours != '0')
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
						
						$row['timeleft'] = $project_time_left;
					}
					if ($rows['project_state'] == 'service')
					{
						$row['ajax_lowbidnotify'] = ($row['lowbidnotify'])
							? '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'"><td><div class="blueonly"><a href="javascript:void(0)" onclick="update_watchlist(\'lowbid\', '.$row['watchlistid'].');">{_notify_me_via_email_when_a_lower_bid_is_placed}</a><span style="float:left;padding-right:6px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="{_click_to_enable_disable}" border="0" id="inline_watchlist_lowbid_'.$row['watchlistid'].'" onclick="update_watchlist(\'lowbid\', '.$row['watchlistid'].');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" /></span></div></td></tr>'
							: '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'"><td><div class="blueonly"><a href="javascript:void(0)" onclick="update_watchlist(\'lowbid\', '.$row['watchlistid'].');">{_notify_me_via_email_when_a_lower_bid_is_placed}</a><span style="float:left;padding-right:6px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="{_click_to_enable_disable}" border="0" id="inline_watchlist_lowbid_'.$row['watchlistid'].'" onclick="update_watchlist(\'lowbid\', '.$row['watchlistid'].');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" /></span></div></td></tr>';
						$row['currentbid'] = '-';
					}
					else
					{
						$row['ajax_lowbidnotify'] = '';
					}
					if ($rows['project_state'] == 'product')
					{
						if ($bids > 0 AND $currentbid > $startprice)
						{
							$row['currentbid'] = $ilance->currency->format($currentbid, $currencyid);
						}
						else if ($bids > 0 AND $currentbid == $startprice)
						{
							$row['currentbid'] = $ilance->currency->format($currentbid, $currencyid);        
						}
						else
						{
							$row['currentbid'] = $ilance->currency->format($startprice, $currencyid);
							$currentbid = $startprice;        
						}
						$row['ajax_highbidnotify'] = ($row['highbidnotify'])
							? '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'"><td><div class="blueonly"><a href="javascript:void(0)" onclick="update_watchlist(\'highbid\', '.$row['watchlistid'].');">{_notify_me_via_email_when_a_higher_bid_is_placed}</a><span style="float:left;padding-right:6px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="{_click_to_enable_disable}" border="0" id="inline_watchlist_highbid_'.$row['watchlistid'].'" onclick="update_watchlist(\'highbid\', '.$row['watchlistid'].');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" /></span></div></td></tr>'
							: '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'"><td><div class="blueonly"><a href="javascript:void(0)" onclick="update_watchlist(\'highbid\', '.$row['watchlistid'].');">{_notify_me_via_email_when_a_higher_bid_is_placed}</a><span style="float:left;padding-right:6px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="{_click_to_enable_disable}" border="0" id="inline_watchlist_highbid_'.$row['watchlistid'].'" onclick="update_watchlist(\'highbid\', '.$row['watchlistid'].');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" /></span></div></td></tr>';
					}
					else
					{
						$row['ajax_highbidnotify'] = '';
					}
					$row['ajax_lasthournotify'] = ($row['hourleftnotify'])
						? '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'"><td><div class="blueonly"><a href="javascript:void(0)" onclick="update_watchlist(\'lasthour\', '.$row['watchlistid'].');">{_notify_me_via_email_when_auction_has_one_hour_left}</a><span style="float:left;padding-right:6px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="{_click_to_enable_disable}" border="0" id="inline_watchlist_lasthour_'.$row['watchlistid'].'" onclick="update_watchlist(\'lasthour\', '.$row['watchlistid'].');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" /></span></div></td></tr>'
						: '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'"><td><div class="blueonly"><a href="javascript:void(0)" onclick="update_watchlist(\'lasthour\', '.$row['watchlistid'].');">{_notify_me_via_email_when_auction_has_one_hour_left}</a><span style="float:left;padding-right:6px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="{_click_to_enable_disable}" border="0" id="inline_watchlist_lasthour_'.$row['watchlistid'].'" onclick="update_watchlist(\'lasthour\', '.$row['watchlistid'].');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" /></span></div></td></tr>';
					$row['ajax_subscribed'] = ($row['subscribed'])
						? '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'"><td><div class="blueonly"><a href="javascript:void(0)" onclick="update_watchlist(\'subscribed\', '.$row['watchlistid'].');">{_notify_me_via_email_when_auction_has_one_hour_left}</a><span style="float:left;padding-right:6px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="{_click_to_enable_disable}" border="0" id="inline_watchlist_subscribed_'.$row['watchlistid'].'" onclick="update_watchlist(\'subscribed\', '.$row['watchlistid'].');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" /></span></div></td></tr>'
						: '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'"><td><div class="blueonly"><a href="javascript:void(0)" onclick="update_watchlist(\'subscribed\', '.$row['watchlistid'].');">{_notify_me_via_email_when_auction_has_one_hour_left}</a><span style="float:left;padding-right:6px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="{_click_to_enable_disable}" border="0" id="inline_watchlist_subscribed_'.$row['watchlistid'].'" onclick="update_watchlist(\'subscribed\', '.$row['watchlistid'].');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" /></span></div></td></tr>';
					if ($rows['status'] != 'open')
					{
						$row['ajax_lasthournotify'] = '';
						$row['ajax_highbidnotify'] = '';
						$row['ajax_lowbidnotify'] = '';
						$row['ajax_subscribed'] = '';
					}
					$row['seller'] = $res_project_user['username'];
					
					($apihook = $ilance->api('show_watchlist_options')) ? eval($apihook) : false;
					
					$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
					$row['cid'] = $rows['cid'];
					$watchlist_rfp[] = $row;
					$row_count++;
				}
			}
		}
		if ($row_count <= 0)
		{
			$show['no_watchlist_rfp'] = true;
		}
		else
		{
			$show['no_watchlist_rfp'] = false;
		}
		$tab = '0';
		if (isset($ilance->GPC['tab']))
		{
			$tab = intval($ilance->GPC['tab']);
		}
		$pprint_array = array('tab','input_style');
		$ilance->template->fetch('main', 'watchlist.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', 'watchlist_sproviders');
		$ilance->template->parse_loop('main', 'watchlist_mproviders');
		$ilance->template->parse_loop('main', 'watchlist_buyers');
		$ilance->template->parse_loop('main', 'watchlist_rfp');
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
}
else
{
	refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['watchlist'] . print_hidden_fields($string = true, $excluded = array(), $questionmarkfirst = true)));
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>