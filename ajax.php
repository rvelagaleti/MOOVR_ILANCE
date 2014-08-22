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
$jsinclude = array('header' => array('functions'), 'footer' => array('tooltip'));

// #### setup script location ##################################################
define('LOCATION', 'ajax');
define('SKIP_SESSION', true);

// #### require backend ########################################################
require_once('./functions/config.php');

($apihook = $ilance->api('ajax_start')) ? eval($apihook) : false;

if (isset($ilance->GPC['do']))
{
	// #### PRIVATE MESSAGE PREVIEW ################################################
	if ($ilance->GPC['do'] == 'previewpm')
	{
		if (isset($ilance->GPC['message']) AND isset($ilance->GPC['subject']))
		{
			$area_title = '{_posting_private_message}<div class="smaller">{_preview}</div>';
			$page_title = SITE_NAME . ' - {_posting_private_message} - {_preview}';
			if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
			{
				$ilance->GPC['subject'] = urldecode(handle_input_keywords($ilance->GPC['subject']));
				$ilance->GPC['message'] = urldecode($ilance->GPC['message']);
				$ilance->GPC['subject'] = $ilance->common->js_escaped_to_xhtml_entities($ilance->GPC['subject']);
				$ilance->GPC['subject'] = $ilance->common->xhtml_entities_to_numeric_entities($ilance->GPC['subject']);
				$ilance->GPC['subject'] = mb_convert_encoding($ilance->GPC['subject'], 'UTF-8', 'HTML-ENTITIES');
				$ilance->GPC['message'] = $ilance->common->js_escaped_to_xhtml_entities($ilance->GPC['message']);
				$ilance->GPC['message'] = $ilance->common->xhtml_entities_to_numeric_entities($ilance->GPC['message']);
				$ilance->GPC['message'] = mb_convert_encoding($ilance->GPC['message'], 'UTF-8', 'HTML-ENTITIES');
				$ilance->GPC['message'] = (isset($ilance->GPC['editor']) AND $ilance->GPC['editor'] == 'ck') ? $ilance->GPC['message'] : $ilance->bbcode->bbcode_to_html($ilance->GPC['message']);
				$ilance->template->templateregistry['previewmessage'] = '<div style="padding-right:12px"><div class="block-wrapper"><div class="block"><div class="block-top"><div class="block-right"><div class="block-left"></div></div></div><div class="block-header">' . $ilance->GPC['subject'] . '</div><div class="block-content" style="padding:' . $ilconfig['table_cellpadding'] . 'px"><div><div style="font-family: verdana">' . $ilance->GPC['message'] . '</div></div></div><div class="block-footer"><div class="block-right"><div class="block-left"></div></div></div></div></div></div>';
				echo $ilance->template->parse_template_phrases('previewmessage');
				exit();
			}
			else
			{
				echo '0';
				exit();
			}
		}
	}
	// #### PRIVATE MESSAGE SUBMIT #################################################
	else if ($ilance->GPC['do'] == 'submitpm')
	{
		if (isset($ilance->GPC['crypted']) AND isset($ilance->GPC['message']) AND isset($ilance->GPC['subject']))
		{
			if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
			{
				$ilance->GPC['crypted'] = urldecode($ilance->GPC['crypted']);
				$ilance->GPC['message'] = urldecode($ilance->GPC['message']);
				$ilance->GPC['subject'] = urldecode($ilance->GPC['subject']);
				$ilance->GPC['subject'] = $ilance->common->js_escaped_to_xhtml_entities($ilance->GPC['subject']);
				$ilance->GPC['subject'] = $ilance->common->xhtml_entities_to_numeric_entities($ilance->GPC['subject']);
				$ilance->GPC['subject'] = mb_convert_encoding($ilance->GPC['subject'], 'UTF-8', 'HTML-ENTITIES');
				$ilance->GPC['message'] = $ilance->common->js_escaped_to_xhtml_entities($ilance->GPC['message']);
				$ilance->GPC['message'] = $ilance->common->xhtml_entities_to_numeric_entities($ilance->GPC['message']);
				$ilance->GPC['message'] = mb_convert_encoding($ilance->GPC['message'], 'UTF-8', 'HTML-ENTITIES');
				$ilance->GPC['decrypt'] = decrypt_url($ilance->GPC['crypted']);
				$ilance->GPC['decrypt']['from_id'] = $_SESSION['ilancedata']['user']['userid'];
				$ilance->GPC['decrypt']['isadmin'] = ($_SESSION['ilancedata']['user']['isadmin'] == '1') ? '1' : '0';
				$ilance->GPC['decrypt']['project_id'] = !empty($ilance->GPC['decrypt']['project_id']) ? $ilance->GPC['decrypt']['project_id'] : 0;
				if (!empty($ilance->GPC['decrypt']['event_id']) AND $ilance->GPC['decrypt']['event_id'] > 0)
				{
					$ilance->GPC['decrypt']['event_id'] = intval($ilance->GPC['decrypt']['event_id']);
				}
				else
				{
					$ilance->GPC['decrypt']['event_id'] = $ilance->pmb->fetch_pmb_eventid($ilance->GPC['decrypt']['project_id'], $ilance->GPC['decrypt']['from_id'], $ilance->GPC['decrypt']['to_id']);
				}
				$ilance->GPC['decrypt']['event_id'] = ($ilance->GPC['decrypt']['event_id'] > 0) ? $ilance->GPC['decrypt']['event_id'] : TIMESTAMPNOW;
				$ilance->pmb->compose_private_message(intval($ilance->GPC['decrypt']['to_id']), intval($ilance->GPC['decrypt']['from_id']), $ilance->GPC['subject'], $ilance->GPC['message'], intval($ilance->GPC['decrypt']['project_id']), $ilance->GPC['decrypt']['event_id'], $ilance->GPC['decrypt']['isadmin']);
				$ilance->template->templateregistry['messageposted'] = '<div style="padding-right:12px"><div class="block-wrapper"><div class="block5"><div class="block5-top"><div class="block5-right"><div class="block5-left"></div></div></div><div class="block5-header">{_your_message_was_posted}</div><div class="block5-content" style="padding:' . $ilconfig['table_cellpadding'] . 'px"><div><div style="font-family: verdana">{_your_private_message_was_posted_and_delivered}</div></div></div><div class="block5-footer"><div class="block5-right"><div class="block5-left"></div></div></div></div></div></div>';
				echo $ilance->template->parse_template_phrases('messageposted');
				exit();
			}
			else
			{
				$ilance->template->templateregistry['messageerror'] = '<div class="block-wrapper"><div class="block5"><div class="block5-top"><div class="block5-right"><div class="block5-left"></div></div></div><div class="block5-header">{_message_not_sent}</div><div class="block5-content" style="padding:' . $ilconfig['table_cellpadding'] . 'px"><div><div style="font-family: verdana">{_your_message_could_not_be_sent}</div></div></div><div class="block5-footer"><div class="block5-right"><div class="block5-left"></div></div></div></div></div>';
				echo $ilance->template->parse_template_phrases('messageerror');
				exit();
			}
		}
	}
	// #### PRIVATE MESSAGE ATTACHMENTS LOADER #####################################
	else if ($ilance->GPC['do'] == 'pminfo')
	{
		if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND isset($ilance->GPC['crypted']))
		{
			$attachment_list = $uploadbutton = $wysiwygeditor = $pmbaction = $conversation = '';
			$ilance->GPC['crypted'] = urldecode($ilance->GPC['crypted']);
			$ilance->GPC['decrypt'] = decrypt_url($ilance->GPC['crypted']);
			$ilance->GPC['decrypt']['isadmin'] = ($_SESSION['ilancedata']['user']['isadmin'] == '1') ? '1' : '0';
			$ilance->GPC['decrypt']['project_id'] = !empty($ilance->GPC['decrypt']['project_id']) ? $ilance->GPC['decrypt']['project_id'] : 0;
			if (!empty($ilance->GPC['decrypt']['event_id']) AND $ilance->GPC['decrypt']['event_id'] > 0)
			{
				$ilance->GPC['decrypt']['event_id'] = intval($ilance->GPC['decrypt']['event_id']);
			}
			else
			{
				$ilance->GPC['decrypt']['event_id'] = $ilance->pmb->fetch_pmb_eventid($ilance->GPC['decrypt']['project_id'], $ilance->GPC['decrypt']['from_id'], $ilance->GPC['decrypt']['to_id']);
			}
			// message attachments
			if ($ilconfig['globalfilters_pmbattachments'])
			{
				$ilance->template->templateregistry['filelist'] = fetch_inline_attachment_filelist('', $ilance->GPC['decrypt']['project_id'], 'pmb');
				$attachment_list = $ilance->template->parse_template_phrases('filelist');
				$crypted = array (
					'attachtype' => 'pmb',
					'pmb_id' => intval($ilance->GPC['decrypt']['event_id']),
					'project_id' => intval($ilance->GPC['decrypt']['project_id']),
					'user_id' => $ilance->GPC['decrypt']['from_id'],
					'category_id' => '0',
					'filehash' => md5(TIMESTAMPNOW),
					'max_filesize' => $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'uploadlimit')
				);
				$ilance->template->templateregistry['uploadbutton'] = '<input name="attachment" onclick=Attach("' . $ilpage['upload'] . '?crypted=' . encrypt_url($crypted) . '") type="button" value="{_upload}" class="buttons" style="font-size:15px"' . $pmbaction . ' />&nbsp;&nbsp;';
				$uploadbutton = $ilance->template->parse_template_phrases('uploadbutton');
				unset($crypted);
			}
			// message conversation
			if ($ilance->GPC['decrypt']['isadmin'] == '1')
			{
				$querymessages = $ilance->db->query("
					SELECT alert.id, alert.from_id, alert.to_id, alert.from_status, alert.to_status, alert.isadmin, pm.project_id, pm.event_id, pm.datetime, pm.message, pm.subject, pm.ishtml
					FROM " . DB_PREFIX . "pmb_alerts AS alert,
					" . DB_PREFIX . "pmb AS pm
					WHERE alert.id = pm.id
						AND alert.project_id = '" . intval($ilance->GPC['decrypt']['project_id']) . "'
						AND alert.event_id = '" . intval($ilance->GPC['decrypt']['event_id']) . "'
						AND alert.event_id = pm.event_id
						AND alert.project_id = pm.project_id
					ORDER BY pm.id ASC
				");
			}
			else
			{
				$querymessages = $ilance->db->query("
					SELECT alert.id, alert.from_id, alert.to_id, alert.from_status, alert.to_status, alert.isadmin, pm.project_id, pm.event_id, pm.datetime, pm.message, pm.subject, pm.ishtml
					FROM " . DB_PREFIX . "pmb_alerts AS alert,
					" . DB_PREFIX . "pmb AS pm
					WHERE alert.id = pm.id
						AND (alert.from_id = '" . intval($ilance->GPC['decrypt']['from_id']) . "' AND alert.to_id = '" . intval($ilance->GPC['decrypt']['to_id']) . "' OR alert.from_id = '" . intval($ilance->GPC['decrypt']['to_id']) . "' AND alert.to_id = '" . intval($ilance->GPC['decrypt']['from_id']) . "')
						AND alert.project_id = '" . intval($ilance->GPC['decrypt']['project_id']) . "'
						AND alert.event_id = '" . intval($ilance->GPC['decrypt']['event_id']) . "'
						AND alert.event_id = pm.event_id
						AND alert.project_id = pm.project_id
					ORDER BY pm.id ASC
				");
			}
			if ($ilance->db->num_rows($querymessages) > 0)
			{
				$rows = $item = 0;
				while ($resmessages = $ilance->db->fetch_array($querymessages, DB_ASSOC))
				{
					$rows++;
					$item++;
					$ilance->pmb->update_pmb_tracker($resmessages['id'], $_SESSION['ilancedata']['user']['userid']);
					$pmb['subject'] = handle_input_keywords($resmessages['subject']);
					$pmb['subject'] = strip_vulgar_words($pmb['subject']);
					if (empty($resmessages['message']))
					{
						$pmb['message'] = '{_no_message_posted}';
					}
					else
					{
						$pmb['message'] = strip_vulgar_words($resmessages['message']);
						$pmb['message'] = (isset($resmessages['ishtml']) AND $resmessages['ishtml'] == '1') ? $pmb['message'] : $ilance->bbcode->bbcode_to_html($pmb['message']);
						$pmb['message'] = print_string_wrap($pmb['message'], 75);
					}
					if ($ilconfig['globalfilters_emailfilterpmb'])
					{
						$pmb['subject'] = strip_email_words($pmb['subject']);
						$pmb['message'] = strip_email_words($pmb['message']);
					}
					if ($ilconfig['globalfilters_domainfilterpmb'])
					{
						$pmb['subject'] = strip_domain_words($pmb['subject']);
						$pmb['message'] = strip_domain_words($pmb['message']);
					}
					$pmb['id'] = $resmessages['id'];
					$pmb['datetime'] = print_date($resmessages['datetime'], $ilconfig['globalserverlocale_globaltimeformat'], 1, 1);
					$pmb['username'] = fetch_user('username', $resmessages['from_id']);
					$pmb['online_status'] = print_online_status($resmessages['from_id']);
					if ($resmessages['from_id'] == $_SESSION['ilancedata']['user']['userid'])
					{
						$conversation .= '<div class="block-wrapper" id="pmbpostblock_' . $pmb['id'] . '"><div class="block"><div class="block-top"><div class="block-right"><div class="block-left"></div></div></div><div class="block-header">' . handle_input_keywords($pmb['subject']) . '&nbsp;</div><div class="block-content" style="padding:0px"><table id="pid' . $pmb['id'] . '" cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="100%"><tr><td class="alt1" style="padding:0px; margin:0px; background:url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/bg_gradient_yellow_1x1000.gif) repeat-x;"><!-- user info --><table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="100%"><tr><td nowrap="nowrap"><div class="blue" style="font-size:17px"><strong>' . $pmb['username'] . '</strong></div><div class="smaller" style="padding-top:3px">' . $pmb['online_status'] . '</div></td><td width="100%">&nbsp;</td><td valign="top" nowrap="nowrap" align="right"><div class="smaller gray">' . $pmb['datetime'] . '</div><div style="padding-top:7px" id="pmbremove_' . $pmb['id'] . '"><input id="pmbid_' . $pmb['id'] . '" type="button" class="buttons" name="remove" value="{_remove_post}" onclick="return remove_pmb_post(\'' . $pmb['id'] . '\')" style="font-size:10px" /></div></td></tr></table><!-- / user info --></td></tr><tr><td class="alt_top"><!-- message --><div style="font-family: verdana">' . $pmb['message'] . '</div><!-- / message --></td></tr><tr><td align="right"></td></tr></table></div><div class="block-footer"><div class="block-right"><div class="block-left"></div></div></div></div></div>';
					}
					else
					{
						$conversation .= '<div class="block-wrapper" id="pmbpostblock_' . $pmb['id'] . '"><div class="block3"><div class="block3-top"><div class="block3-right"><div class="block3-left"></div></div></div><div class="block3-header">' . handle_input_keywords($pmb['subject']) . '&nbsp;</div><div class="block3-content" style="padding:0px"><table id="pid' . $pmb['id'] . '" cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="100%"><tr><td class="alt1" style="padding:0px; margin:0px; background:url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/bg_gradient_gray_1x1000.gif) repeat-x;"><!-- user info --><table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="100%"><tr><td nowrap="nowrap"><div class="blue" style="font-size:17px"><strong>' . $pmb['username'] . '</strong></div><div class="smaller" style="padding-top:3px">' . $pmb['online_status'] . '</div></td><td width="100%">&nbsp;</td><td valign="top" nowrap="nowrap" align="right"><div class="smaller gray">' . $pmb['datetime'] . '</div><div style="padding-top:7px" id="pmbremove_' . $pmb['id'] . '"><input id="pmbid_' . $pmb['id'] . '" type="button" class="buttons" name="remove" value="{_remove_post}" onclick="return remove_pmb_post(\'' . $pmb['id'] . '\')" style="font-size:10px" /></div></td></tr></table><!-- / user info --></td></tr><tr><td class="alt_top"><!-- message --><div style="font-family: verdana">' . $pmb['message'] . '</div><!-- / message --></td></tr><tr><td align="right"></td></tr></table></div><div class="block3-footer"><div class="block3-right"><div class="block3-left"></div></div></div></div></div>';
					}
				}
				$ilance->template->templateregistry['conversation'] = '<div style="font-size:17px;font-weight:bold; padding-bottom:12px; padding-top:9px">{_conversation} <span id="pmbconversationrefresh"></span></div>' . $conversation;
				$conversation = $ilance->template->parse_template_phrases('conversation');
			}
			else
			{
				$ilance->template->templateregistry['conversation'] = '<div style="font-size:17px;font-weight:bold; padding-bottom:12px; padding-top:9px">{_conversation} <span id="pmbconversationrefresh"></span></div>';
				$conversation = $ilance->template->parse_template_phrases('conversation');
			}
			echo "$attachment_list|$uploadbutton|$conversation";
			exit();
		}
		echo '';
		exit();
	}
	// #### PRIVATE MESSAGE POST REMOVAL (ADMIN) ###################################
	else if ($ilance->GPC['do'] == 'pmremove')
	{
		if (isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
		{
			// does admin request pmb removal?
			if ($ilance->pmb->remove_pmb_post(intval($ilance->GPC['id'])))
			{
				echo '1';
				exit();
			}
			echo '';
			exit();
		}
	}
	// #### SEND TEST EMAIL TEMPLATE (ADMIN) #######################################
	else if ($ilance->GPC['do'] == 'admincpemailtest')
	{
		if (!empty($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1' AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND isset($ilance->GPC['varname']) AND !empty($ilance->GPC['varname']))
		{
			$ilance->email->mail = SITE_EMAIL;
			$ilance->email->slng = fetch_site_slng();
			$ilance->email->get($ilance->GPC['varname']);
			$ilance->email->set(array());
			$ilance->email->send();
			echo '1';
			exit();
		}
	}
	// #### INLINE TEXT INPUT EDITOR ###############################################
	else if ($ilance->GPC['do'] == 'inlineedit')
	{
		if (isset($ilance->GPC['action']) AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
		{
			switch ($ilance->GPC['action'])
			{
				// #### subscription permissions title #################
				case 'permission_accesstext':
				{
				    break;
				}
				// #### subscription permissions description ###########
				case 'permission_description':
				{
				    break;
				}
				// #### favorite search title ##########################
				case 'favsearchtitle':
				{
					$ilance->GPC['text'] = $ilance->common->js_escaped_to_xhtml_entities($ilance->GPC['text']);
					$ilance->GPC['text'] = $ilance->common->xhtml_entities_to_numeric_entities($ilance->GPC['text']);
					$ilance->GPC['text'] = mb_convert_encoding($ilance->GPC['text'], 'UTF-8', 'HTML-ENTITIES');
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "search_favorites
						SET title = '" . $ilance->db->escape_string($ilance->GPC['text']) . "'
						WHERE searchid = '" . intval($ilance->GPC['id']) . "'
					", 0, null, __FILE__, __LINE__);
					echo $ilance->GPC['text'];
					break;
				}
				// #### favorite search title ##########################
				case 'watchlistcomment':
				{
					$ilance->GPC['text'] = $ilance->common->js_escaped_to_xhtml_entities($ilance->GPC['text']);
					$ilance->GPC['text'] = $ilance->common->xhtml_entities_to_numeric_entities($ilance->GPC['text']);
					$ilance->GPC['text'] = mb_convert_encoding($ilance->GPC['text'], 'UTF-8', 'HTML-ENTITIES');
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "watchlist
						SET comment = '" . $ilance->db->escape_string($ilance->GPC['text']) . "'
						WHERE watchlistid = '" . intval($ilance->GPC['id']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					echo $ilance->GPC['text'];
					break;
				}
				// #### portfolio title ################################
				case 'portfolio':
				{
					$ilance->GPC['text'] = $ilance->common->js_escaped_to_xhtml_entities($ilance->GPC['text']);
					$ilance->GPC['text'] = $ilance->common->xhtml_entities_to_numeric_entities($ilance->GPC['text']);
					$ilance->GPC['text'] = mb_convert_encoding($ilance->GPC['text'], 'UTF-8', 'HTML-ENTITIES');
					// http://www.ilance.com/forum/project.php?issueid=1207
					$ilance->GPC['text'] = mb_convert_encoding($ilance->GPC['text'], 'UTF-8', 'HTML-ENTITIES');
					$setwhat = "caption = '" . $ilance->db->escape_string($ilance->GPC['text']) . "'";
					if (stristr($ilance->GPC['id'], '_title'))
					{
						$setwhat = "caption = '" . $ilance->db->escape_string($ilance->GPC['text']) . "'";
						$id = explode('_', $ilance->GPC['id']);
					}
					else if (stristr($ilance->GPC['id'], '_description'))
					{
						$setwhat = "description = '" . $ilance->db->escape_string($ilance->GPC['text']) . "'";
						$id = explode('_', $ilance->GPC['id']);
					}
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "portfolio
						SET $setwhat
						WHERE portfolio_id = '" . intval($id[0]) . "'
					", 0, null, __FILE__, __LINE__);
					echo $ilance->GPC['text'];
					break;
				}
				// #### seller updating paymethod #######
				case 'sellerpaymethod':
				{
					$ilance->GPC['text'] = $ilance->common->js_escaped_to_xhtml_entities($ilance->GPC['text']);
					$ilance->GPC['text'] = $ilance->common->xhtml_entities_to_numeric_entities($ilance->GPC['text']);
					$ilance->GPC['text'] = mb_convert_encoding($ilance->GPC['text'], 'UTF-8', 'HTML-ENTITIES');
					echo $ilance->GPC['text'];
					break;
				}
				// #### seller updating shipment tracking number #######
				case 'sellershiptracking':
				{
					$ilance->GPC['text'] = $ilance->common->js_escaped_to_xhtml_entities($ilance->GPC['text']);
					$ilance->GPC['text'] = $ilance->common->xhtml_entities_to_numeric_entities($ilance->GPC['text']);
					$ilance->GPC['text'] = mb_convert_encoding($ilance->GPC['text'], 'UTF-8', 'HTML-ENTITIES');
					echo $ilance->GPC['text'];
					break;
				}
			}
			exit();
		}
	}
	// #### WATCHLIST ##############################################################
	else if ($ilance->GPC['do'] == 'watchlist')
	{
		($apihook = $ilance->api('ajax_watchlist_start')) ? eval($apihook) : false;
	
		if (isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
		{
			// #### SAVE WATCHLIST ITEM FOR USER ###########################
			if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'savewatchlist')
			{
			}
			// #### SAVE WATCHLIST SUBSCRIPTION PREFERENCES ################
			if (!empty($ilance->GPC['value']) AND !empty($ilance->GPC['type']))
			{
				$ilance->GPC['value'] = ($ilance->GPC['value'] == 'on' ? 1 : 0);
				$ilance->GPC['type'] = $ilance->GPC['type'];
				switch ($ilance->GPC['type'])
				{
					case 'lasthour':
					{
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "watchlist
							SET hourleftnotify = '" . intval($ilance->GPC['value']) . "', subscribed = '1'
							WHERE watchlistid = '" . intval($ilance->GPC['watchlistid']) . "'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->GPC['value'])
						{
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "watchlist
								SET subscribed = '1'
								WHERE watchlistid = '" . intval($ilance->GPC['watchlistid']) . "'
							", 0, null, __FILE__, __LINE__);
							$ilance->xml->add_tag('status', 'on');
						}
						else
						{
							$ilance->xml->add_tag('status', 'off');
						}
						break;
					}
					case 'lowbid':
					{
						$sql = $ilance->db->query("
							SELECT watching_project_id
							FROM " . DB_PREFIX . "watchlist
							WHERE watchlistid = '" . intval($ilance->GPC['watchlistid']) . "'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							$res = $ilance->db->fetch_array($sql);
							// did this user already place a bid?
							$sql2 = $ilance->db->query("
								SELECT bidamount
								FROM " . DB_PREFIX . "project_bids
								WHERE user_id = '" . intval($_SESSION['ilancedata']['user']['userid']) . "'
									AND project_id = '" . $res['watching_project_id'] . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							if ($ilance->db->num_rows($sql2) > 0)
							{
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "watchlist
									SET lowbidnotify = '" . intval($ilance->GPC['value']) . "', subscribed = '1' WHERE watchlistid = '" . intval($ilance->GPC['watchlistid']) . "'
								", 0, null, __FILE__, __LINE__);
								if ($ilance->GPC['value'])
								{
									$ilance->db->query("UPDATE " . DB_PREFIX . "watchlist SET subscribed = '1' WHERE watchlistid = '" . intval($ilance->GPC['watchlistid']) . "'", 0, null, __FILE__, __LINE__);
									$ilance->xml->add_tag('status', 'on');
								}
								else
								{
									$ilance->xml->add_tag('status', 'off');
								}
							}
							else
							{
								$ilance->template->templateregistry['phrase'] = '{_sorry_to_track_lower_bid_amounts_you_will_need_to_place_a_bid_on_this_auction_first}';
								$ilance->xml->add_tag('status', $ilance->template->parse_template_phrases('phrase'));
							}
						}
						else
						{
							$ilance->template->templateregistry['phrase'] = '{_sorry_to_track_lower_bid_amounts_you_will_need_to_place_a_bid_on_this_auction_first}';
							$ilance->xml->add_tag('status', $ilance->template->parse_template_phrases('phrase'));
						}
						break;
					}
					case 'highbid':
					{
						$sql = $ilance->db->query("
							SELECT watching_project_id
							FROM " . DB_PREFIX . "watchlist
							WHERE watchlistid = '" . intval($ilance->GPC['watchlistid']) . "'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							$res = $ilance->db->fetch_array($sql);
							$sql2 = $ilance->db->query("
								SELECT bidamount
								FROM " . DB_PREFIX . "project_bids
								WHERE user_id = '" . intval($_SESSION['ilancedata']['user']['userid']) . "'
									AND project_id = '" . $res['watching_project_id'] . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							if ($ilance->db->num_rows($sql2) > 0)
							{
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "watchlist
									SET highbidnotify = '" . intval($ilance->GPC['value']) . "',
									subscribed = '1'
									WHERE watchlistid = '" . intval($ilance->GPC['watchlistid']) . "'
								", 0, null, __FILE__, __LINE__);
								if ($ilance->GPC['value'])
								{
									$ilance->db->query("
										UPDATE " . DB_PREFIX . "watchlist
										SET subscribed = '1'
										WHERE watchlistid = '" . intval($ilance->GPC['watchlistid']) . "'
									", 0, null, __FILE__, __LINE__);
									$ilance->xml->add_tag('status', 'on');
								}
								else
								{
									$ilance->xml->add_tag('status', 'off');
								}
							}
							else
							{
								$ilance->template->templateregistry['phrase'] = '{_sorry_to_track_higher_bid_amounts_you_will_need_to_place_a_bid_on_this_auction_first}';
								$ilance->xml->add_tag('status', $ilance->template->parse_template_phrases('phrase'));
							}
						}
						else
						{
							$ilance->template->templateregistry['phrase'] = '{_sorry_to_track_higher_bid_amounts_you_will_need_to_place_a_bid_on_this_auction_first}';
							$ilance->xml->add_tag('status', $ilance->template->parse_template_phrases('phrase'));
						}
						break;
					}
					case 'subscribed':
					{
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "watchlist
							SET subscribed = '" . intval($ilance->GPC['value']) . "'
							WHERE watchlistid = '" . intval($ilance->GPC['watchlistid']) . "'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->GPC['value'])
						{
							$ilance->xml->add_tag('status', 'on');
						}
						else
						{
							$ilance->xml->add_tag('status', 'off');
						}
						break;
					}
				}
				$ilance->xml->print_xml();
				exit();
			}
		}
	}
	// #### ADD LISTING TO WATCHLIST AND SAVE SELLER AS FAVORTE HANDLER ############
	else if ($ilance->GPC['do'] == 'addwatchlist')
	{
		($apihook = $ilance->api('ajax_addwatchlist_start')) ? eval($apihook) : false;
	
		// #### SAVE WATCHLIST SUBSCRIPTION PREFERENCES ########################
		if (isset($ilance->GPC['userid']) AND !empty($ilance->GPC['userid']) AND $ilance->GPC['userid'] > 0 AND isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
		{
			if (isset($ilance->GPC['projectid']) AND $ilance->GPC['projectid'] > 0)
			{
				$success = $ilance->watchlist->insert_item(intval($ilance->GPC['userid']), intval($ilance->GPC['projectid']), 'auction', '{_added_from_listing_page}', 0, 0, 0, 0);
				if ($success)
				{
					$ilance->xml->add_tag('status', 'addeditem');
				}
				else
				{
					$ilance->xml->add_tag('status', 'alreadyaddeditem');
				}
			}
			else if (isset($ilance->GPC['sellerid']) AND $ilance->GPC['sellerid'] > 0)
			{
				$success = $ilance->watchlist->insert_item(intval($ilance->GPC['userid']), intval($ilance->GPC['sellerid']), 'mprovider', '{_added_from_listing_page}', 0, 0, 0, 0);
				if ($success)
				{
					$ilance->xml->add_tag('status', 'addedseller');
				}
				else
				{
					$ilance->xml->add_tag('status', 'alreadyaddedseller');
				}
			}
			else
			{
				$ilance->xml->add_tag('status', 'error');
			}
			$ilance->xml->print_xml();
			exit();
		}
	}
	else if ($ilance->GPC['do'] == 'check_email')
	{
		if (isset($ilance->GPC['email_user']))
		{
			$add_customer['status'] = $add_customer['status1'] = true;
			$sql = $ilance->db->query("
				SELECT user_id
				FROM " . DB_PREFIX . "users
				WHERE email = '" . $ilance->db->escape_string($ilance->GPC['email']) . "'
			", 0, null, __FILE__, __LINE__);
			$html = " ";
			if ($ilance->db->num_rows($sql) > 0)
			{
				$add_customer['status1'] = false;
				$html = "0";
			}
			$sql1 = $ilance->db->query("
				SELECT user_id
				FROM " . DB_PREFIX . "users
				WHERE username = '" . $ilance->db->escape_string($ilance->GPC['username']) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql1) > 0)
			{
				$add_customer['status'] = false;
				$html = "1";
			}
			if ($ilance->db->num_rows($sql) > 0 AND $ilance->db->num_rows($sql1) > 0)
			{
				$html = "2";
			}
			if ($ilance->db->num_rows($sql) == 0 AND $ilance->db->num_rows($sql1) == 0)
			{
				$html = "3";
			}
			echo $html;
			exit();
		}
	}
	// #### SEARCH FAVORITES #######################################################
	else if ($ilance->GPC['do'] == 'searchfavorites')
	{
		if (isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
		{
			($apihook = $ilance->api('ajax_searchfavorites_start')) ? eval($apihook) : false;
	    
			$ilance->GPC['searchid'] = intval($ilance->GPC['searchid']);
			$ilance->GPC['value'] = ($ilance->GPC['value'] == 'on' ? 1 : 0);
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "search_favorites
				SET subscribed = '" . intval($ilance->GPC['value']) . "',
				added = '" . DATETIME24H . "'
				WHERE searchid = '" . intval($ilance->GPC['searchid']) . "'
					AND user_id = '" . intval($_SESSION['ilancedata']['user']['userid']) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->GPC['value'])
			{
				$ilance->xml->add_tag('status', 'on');
			}
			else
			{
				$ilance->xml->add_tag('status', 'off');
			}
			$ilance->xml->print_xml();
			exit();
		}
	}
	// #### ACP AJAX ENHANCEMENTS ##################################################
	else if ($ilance->GPC['do'] == 'acpenhancements')
	{
		if (isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'])
		{
			($apihook = $ilance->api('ajax_acpenhancements_start')) ? eval($apihook) : false;
	    
			$ilance->GPC['id'] = intval($ilance->GPC['id']);
			$ilance->GPC['value'] = ($ilance->GPC['value'] == 'on' ? 1 : 0);
			$ilance->GPC['type'] = strip_tags($ilance->GPC['type']);
			switch ($ilance->GPC['type'])
			{
				case 'featured':
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET featured = '" . intval($ilance->GPC['value']) . "'
						WHERE project_id = '" . intval($ilance->GPC['id']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					break;
				}
				case 'featured_searchresults':
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET featured_searchresults = '" . intval($ilance->GPC['value']) . "'
						WHERE project_id = '" . intval($ilance->GPC['id']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					break;
				}
				case 'bold':
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET bold = '" . intval($ilance->GPC['value']) . "'
						WHERE project_id = '" . intval($ilance->GPC['id']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					break;
				}
				case 'highlite':
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET highlite = '" . intval($ilance->GPC['value']) . "'
						WHERE project_id = '" . intval($ilance->GPC['id']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					break;
				}
				case 'autorelist':
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET autorelist = '" . intval($ilance->GPC['value']) . "'
						WHERE project_id = '" . intval($ilance->GPC['id']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					break;
				}
			}
			if ($ilance->GPC['value'])
			{
				$ilance->xml->add_tag('status', 'on');
			}
			else
			{
				$ilance->xml->add_tag('status', 'off');
			}
			$ilance->xml->print_xml();
			exit();
		}
	}
	// #### FLASH GALLERY APPLET ###################################################
	else if ($ilance->GPC['do'] == 'flashgallery')
	{
		($apihook = $ilance->api('ajax_flashgallery_start')) ? eval($apihook) : false;
		
		include_once(DIR_CORE . 'functions_flash.php');
		switch ($ilance->GPC['config'])
		{
			case 'portfolio':
			{
				$xml = '<?xml version="1.0"?>
<gallery>
<config>
<big_thumb type="number">80</big_thumb>
<inc_koef type="number">1.1</inc_koef>
<dec_koef type="number">0.9</dec_koef>
<interval_delay type="number">20</interval_delay>
<fade_in_delay type="number">20</fade_in_delay>
<fade_in_step type="number">5</fade_in_step>
<speed_increment type="number">1</speed_increment>
<speed_up_part type="number">0.2</speed_up_part>
<speed_decrement type="number">1</speed_decrement>
<speed_down_part type="number">0.8</speed_down_part>
<speed_delay type="number">7</speed_delay>
<pager_scroll_alpha type="number">4</pager_scroll_alpha>
<show_thumb_after_scroll_delay type="number">25</show_thumb_after_scroll_delay>
<show_thumb_after_scroll_alpha_step type="number">5</show_thumb_after_scroll_alpha_step>
<pager_controls_alpha type="number">30</pager_controls_alpha>
</config>
<items>
' . fetch_flash_gallery_xml_items($ilance->GPC['config']) . '
</items>
</gallery>';
				break;
			}
			case 'favoriteseller':
			{
				$xml = '<?xml version="1.0"?>
<gallery>
<config>
<big_thumb type="number">80</big_thumb>
<inc_koef type="number">1.1</inc_koef>
<dec_koef type="number">0.9</dec_koef>
<interval_delay type="number">20</interval_delay>
<fade_in_delay type="number">20</fade_in_delay>
<fade_in_step type="number">5</fade_in_step>
<speed_increment type="number">1</speed_increment>
<speed_up_part type="number">0.2</speed_up_part>
<speed_decrement type="number">1</speed_decrement>
<speed_down_part type="number">0.8</speed_down_part>
<speed_delay type="number">7</speed_delay>
<pager_scroll_alpha type="number">4</pager_scroll_alpha>
<show_thumb_after_scroll_delay type="number">25</show_thumb_after_scroll_delay>
<show_thumb_after_scroll_alpha_step type="number">5</show_thumb_after_scroll_alpha_step>
<pager_controls_alpha type="number">30</pager_controls_alpha>
</config>
<items>
' . fetch_flash_gallery_xml_items($ilance->GPC['config'], $ilance->GPC['userid']) . '
</items>
	</gallery>';
				break;
			}
			case 'recentlyviewed':
			{
				$xml = '<?xml version="1.0"?>
<gallery>
<config>
<big_thumb type="number">80</big_thumb>
<inc_koef type="number">1.1</inc_koef>
<dec_koef type="number">0.9</dec_koef>
<interval_delay type="number">20</interval_delay>
<fade_in_delay type="number">20</fade_in_delay>
<fade_in_step type="number">5</fade_in_step>
<speed_increment type="number">1</speed_increment>
<speed_up_part type="number">0.2</speed_up_part>
<speed_decrement type="number">1</speed_decrement>
<speed_down_part type="number">0.8</speed_down_part>
<speed_delay type="number">7</speed_delay>
<pager_scroll_alpha type="number">4</pager_scroll_alpha>
<show_thumb_after_scroll_delay type="number">25</show_thumb_after_scroll_delay>
<show_thumb_after_scroll_alpha_step type="number">5</show_thumb_after_scroll_alpha_step>
<pager_controls_alpha type="number">30</pager_controls_alpha>
</config>
<items>
' . fetch_flash_gallery_xml_items($ilance->GPC['config']) . '
</items>
</gallery>';
				break;
			}
		}
		echo $xml;
		exit();
	}
	// #### STATS GALLERY APPLET ###################################################
	else if ($ilance->GPC['do'] == 'stats')
	{
		if (isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND isset($ilance->GPC['config']) AND !empty($ilance->GPC['config']))
		{
			$startdate = (isset($ilance->GPC['startdate']) ? $ilance->GPC['startdate'] : DATETODAY);
			$enddate = (isset($ilance->GPC['enddate']) ? $ilance->GPC['enddate'] : DATETODAY);
			$custom1 = (isset($ilance->GPC['custom1']) ? $ilance->GPC['custom1'] : '');
			$custom2 = (isset($ilance->GPC['custom2']) ? $ilance->GPC['custom2'] : '');
			$custom3 = (isset($ilance->GPC['custom3']) ? $ilance->GPC['custom3'] : '');
			include_once(DIR_CORE . 'functions_flash.php');
			$xml = '<?xml version="1.0"?>
<chart>
<config>
<axises_space_left type="number">15</axises_space_left>
<axises_space_top type="number">20</axises_space_top>
<axises_space_right type="number">0</axises_space_right>
<axises_space_bottom type="number">45</axises_space_bottom>
<axises_line_thickness type="number">1</axises_line_thickness>
<axises_marks_thickness type="number">1</axises_marks_thickness>
<axises_marks_length type="number">5</axises_marks_length>
<axises_labels_space type="number">5</axises_labels_space>
<axises_labels_font_size type="number">10</axises_labels_font_size>
<axises_labels_font_face type="string">Verdana</axises_labels_font_face>
<axises_labels_font_bold type="boolean">false</axises_labels_font_bold>
<axises_color type="hex">0x000000</axises_color>
<axises_bg_grid_color type="hex">0xD1D1D1</axises_bg_grid_color>
<mouse_pointer_thickness type="number">1</mouse_pointer_thickness>
<mouse_pointer_color type="hex">0x0066ff</mouse_pointer_color>
<date_mouse_pointer_color type="hex">0x0066ff</date_mouse_pointer_color>
<date_mouse_pointer_alpha type="number">100</date_mouse_pointer_alpha>
<date_mouse_pointer_distance_axis type="number">2</date_mouse_pointer_distance_axis>
<date_mouse_pointer_distance_x_arrow type="number">7</date_mouse_pointer_distance_x_arrow>
<date_mouse_pointer_distance_y_arrow type="number">7</date_mouse_pointer_distance_y_arrow>
<date_mouse_pointer_label_dx type="number">1</date_mouse_pointer_label_dx>
<date_mouse_pointer_label_dy type="number">10</date_mouse_pointer_label_dy>
<date_mouse_pointer_bg_dx type="number">10</date_mouse_pointer_bg_dx>
<date_mouse_pointer_bg_dy type="number">1</date_mouse_pointer_bg_dy>
<date_mouse_pointer_date_label_color type="hex">0xFFFFFF</date_mouse_pointer_date_label_color>
<value_mouse_pointer_label_color type="hex">0xFFFFFF</value_mouse_pointer_label_color>
<value_mouse_pointer_distance_x_arrow type="number">7</value_mouse_pointer_distance_x_arrow>
<value_mouse_pointer_distance_y_arrow type="number">7</value_mouse_pointer_distance_y_arrow>
<show_all_btn_dx type="number">100</show_all_btn_dx>
<show_all_btn_dy type="number">30</show_all_btn_dy>
<scroll_bg_color type="hex">0xFFFFFF</scroll_bg_color>
<scroll_color type="hex">0x3366ff</scroll_color>
<scroll_height type="number">15</scroll_height>
<scroll_space_bottom type="number">20</scroll_space_bottom>
<pin_bg_color type="hex">0x000000</pin_bg_color>
<pin_text_color type="hex">0xFFFFFF</pin_text_color>
<pin_text_font_size type="hex">10</pin_text_font_size>
<pin_text_font_face type="hex">Verdana</pin_text_font_face>
<pin_bg_alpha type="number">100</pin_bg_alpha>
<show_dots_after type="number">50</show_dots_after>
<dots_radius type="number">3</dots_radius>';
			$xml .= fetch_flash_stats_xml_items($ilance->GPC['config'], $startdate, $enddate, $custom1, $custom2, $custom3);
			echo $xml;
			exit();
		}
	}
	else if ($ilance->GPC['do'] == 'stats2')
	{
		if (isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
		{
			$startdate = isset($ilance->GPC['startdate']) ? $ilance->GPC['startdate'] : DATETODAY;
			$enddate = isset($ilance->GPC['enddate']) ? $ilance->GPC['enddate'] : DATETODAY;
			include_once(DIR_CORE . 'functions_flash.php');
			switch ($ilance->GPC['config'])
			{
				case 'connections':
				{
					$xml = '<?xml version="1.0"?>
<chart>
<config>
<axises_space_left type="number">0</axises_space_left>
<axises_space_top type="number">15</axises_space_top>
<axises_space_right type="number">0</axises_space_right>
<axises_space_bottom type="number">25</axises_space_bottom>
<axises_font_size type="number">10</axises_font_size>
<axises_font_face type="string">Tahoma</axises_font_face>
<axises_font_bold type="boolean">true</axises_font_bold>
<axises_color type="hex">0x444444</axises_color>
<axises_bg_grid_color type="hex">0xCCCCCC</axises_bg_grid_color>

<axises_line_thickness type="number">2</axises_line_thickness>
<axises_marks_thickness type="number">1</axises_marks_thickness>
<axises_marks_length type="number">8</axises_marks_length>
<axises_marks_font_size type="number">10</axises_marks_font_size>
<axises_marks_font_face type="string">Tahoma</axises_marks_font_face>
<axises_marks_font_bold type="boolean">false</axises_marks_font_bold>
<axises_marks_font_color type="hex">0x333333</axises_marks_font_color>

<axises_labels_space type="number">5</axises_labels_space>
<axises_labels_font_size type="number">10</axises_labels_font_size>
<axises_labels_font_face type="string">Verdana</axises_labels_font_face>
<axises_labels_font_bold type="boolean">false</axises_labels_font_bold>

<value_mouse_pointer_label_color type="hex">0xFFFFFF</value_mouse_pointer_label_color>
<value_mouse_pointer_bg_color type="hex">0x004B95</value_mouse_pointer_bg_color>
<value_mouse_pointer_bg_alpha type="number">100</value_mouse_pointer_bg_alpha>
<value_mouse_pointer_distance_x_arrow type="number">7</value_mouse_pointer_distance_x_arrow>
<value_mouse_pointer_distance_y_arrow type="number">7</value_mouse_pointer_distance_y_arrow>
<value_mouse_pointer_space_left type="number">10</value_mouse_pointer_space_left>
<value_mouse_pointer_space_top type="number">10</value_mouse_pointer_space_top>

<value_bar_color1 type="hex">0x99BBDB</value_bar_color1> 
<value_bar_color2 type="hex">0x4A6E7D</value_bar_color2>

<value_bar_alpha1 type="number">100</value_bar_alpha1>
<value_bar_alpha2 type="number">100</value_bar_alpha2>

<value_bar_gradient_spread1 type="number">0</value_bar_gradient_spread1>
<value_bar_gradient_spread2 type="number">255</value_bar_gradient_spread2>

<value_bar_height type="number">10</value_bar_height>
</config>
<items start_date="' . date('Y') . '-' . date('m') . '-' . date('d') . '">
' . fetch_flash_stats_xml_items($ilance->GPC['config'], $startdate, $enddate) . '
</items>
</chart>';
				break;
				}
			}
			echo $xml;
			exit();
		}
	}
	// #### AJAX CATEGORY SELECTOR #################################################
	else if ($ilance->GPC['do'] == 'categories')
	{
		$modetypes = array ('service', 'product');
		if (!isset($ilance->GPC['mode']) OR isset($ilance->GPC['mode']) AND !in_array($ilance->GPC['mode'], $modetypes))
		{
			return '';
		}
		// #### determine if we're displaying rss feeds to hide cats that admin prefers not to include
		$rssquery = "";
		$rss = isset($ilance->GPC['rss']) ? intval($ilance->GPC['rss']) : 0;
		if ($rss)
		{
			$rssquery = "AND xml = '1' ";
		}
		// #### determine if we're displaying category notifications to hide cats that admin prefers not to include
		$newsquery = "";
		$news = isset($ilance->GPC['newsletter']) ? intval($ilance->GPC['newsletter']) : 0;
		if ($news)
		{
			$newsquery = "AND newsletter = '1' ";
		}
		// #### show categories for the first box ##############################
		$getcats = $ilance->db->query("
			SELECT cid, title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title
			FROM " . DB_PREFIX . "categories
			WHERE parentid = '0'
			    AND cattype = '" . $ilance->db->escape_string($ilance->GPC['mode']) . "'
			    AND visible = '1'
			    $rssquery
			    $newsquery
			ORDER BY sort ASC, title_" . $_SESSION['ilancedata']['user']['slng'] . " ASC
		", 0, null, __FILE__, __LINE__);
		include_once(DIR_API . 'class.xajax.inc.php');
		$xajax = new xajax();
		include_once(DIR_CORE . 'functions_categories_ajax.php');
		$xajax->registerFunction('print_next_category');
		$xajax->processRequests();
		$cidfield = isset($ilance->GPC['cidfield']) ? $ilance->GPC['cidfield'] : 'cid';
		$showcontinue = isset($ilance->GPC['showcontinue']) ? intval($ilance->GPC['showcontinue']) : 1;
		$showthumb = isset($ilance->GPC['showthumb']) ? intval($ilance->GPC['showthumb']) : 1;
		$showcidbox = isset($ilance->GPC['showcidbox']) ? intval($ilance->GPC['showcidbox']) : 1;
		$showyouselectedstring = isset($ilance->GPC['showyouselectedstring']) ? intval($ilance->GPC['showyouselectedstring']) : 1;
		$readonly = isset($ilance->GPC['readonly']) ? intval($ilance->GPC['readonly']) : 0;
		$showcheckmarkafterstring = isset($ilance->GPC['showcheckmarkafterstring']) ? intval($ilance->GPC['showcheckmarkafterstring']) : 1;
		$categoryfinderapi = isset($ilance->GPC['categoryfinderapi']) ? intval($ilance->GPC['categoryfinderapi']) : 0;
		$categoryfinderjs = isset($ilance->GPC['categoryfinderjs']) ? intval($ilance->GPC['categoryfinderjs']) : 0;
		$id = isset($ilance->GPC['id']) ? intval($ilance->GPC['id']) : 0;
		$cmd = isset($ilance->GPC['cmd']) ? $ilance->GPC['cmd'] : '';
		$rootcid = $assigntoall = 0;
		$showaddanother = isset($ilance->GPC['showaddanother']) ? intval($ilance->GPC['showaddanother']) : 0;
		if ($showaddanother AND isset($ilance->GPC['mode']))
		{
			$cmd = handle_input_keywords($ilance->GPC['mode']);
		}
		if (!empty($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
		{
			$assigntoall = isset($ilance->GPC['assigntoall']) ? intval($ilance->GPC['assigntoall']) : 0;
			$rootcid = isset($ilance->GPC['rootcid']) ? intval($ilance->GPC['rootcid']) : 0;
		}
		$footerscript = '';
		if (isset($ilance->GPC["$cidfield"]) AND $ilance->GPC["$cidfield"] > 0)
		{
			$footerscript = '
<script type="text/javascript">
<!--
' . fetch_recursive_category_ids_js(intval($ilance->GPC["$cidfield"]), $ilance->GPC['mode'], $_SESSION['ilancedata']['user']['slng'], $cidfield, $showcontinue, $showthumb, $showcidbox, $showyouselectedstring, $readonly, $showcheckmarkafterstring, $categoryfinderjs, $id, $cmd, $rss, $news, $showaddanother, $categoryfinderapi) . '
//--></script>
';
		}
?>
	<html dir="<?php echo $ilconfig['template_textdirection']; ?>"><head><script type="text/javascript" src="<?php echo $ilconfig['template_relativeimagepath'] . DIR_FUNCT_NAME . '/javascript/functions' . (($ilconfig['globalfilters_jsminify']) ? '.min' : '') . '.js'; ?>"></script>
	<?php $xajax->printJavascript(); ?></head>
	<body bgcolor="#ffffff" style="margin:0px; padding:0px" onLoad=""><table cellpadding="0" cellspacing="0" border="0" dir="<?php echo $ilconfig['template_textdirection']; ?>"><tr valign="top">
		<td id="catbox_1"><select id="catbox_1_list" name="catbox_1" onChange="xajax_print_next_category(this[this.selectedIndex].value, 'catbox_1', '<?php echo $cidfield; ?>', '<?php echo $showcontinue; ?>', '<?php echo $showthumb; ?>', '<?php echo $showcidbox; ?>', '<?php echo $showyouselectedstring; ?>', '<?php echo $readonly; ?>', '<?php echo $showcheckmarkafterstring; ?>', '<?php echo $categoryfinderjs; ?>', '<?php echo $id; ?>', '<?php echo $cmd; ?>', '<?php echo $rss; ?>', '<?php echo $news; ?>', '<?php echo $showaddanother; ?>', '<?php echo $categoryfinderapi; ?>')" style="position:relative; height:225px" class="input" size="13">
<?php
		if ($rootcid)
		{
		    $ilance->template->templateregistry['phrase'] = '{_no_parent_category}';
		    echo '<option value="0">' . $ilance->template->parse_template_phrases('phrase') . '</option>';
		}
		if ($assigntoall)
		{
		    $ilance->template->templateregistry['phrase'] = '{_assign_to_all_categories}';
		    echo '<option value="-1">' . $ilance->template->parse_template_phrases('phrase') . '</option>';
		}
		while ($res = $ilance->db->fetch_array($getcats, DB_ASSOC))
		{
		    echo '<option value="' . $res['cid'] . '">' . handle_input_keywords($res['title']) . '' . (is_last_category($res['cid']) ? '' : ' &gt;') . '</option>';
		}
?>
		</select></td><td id="catbox_2" style="padding-left:10px"></td><td id="catbox_3" style="padding-left:10px"></td><td id="catbox_4" style="padding-left:10px"></td><td id="catbox_5" style="padding-left:10px"></td><td id="catbox_6" style="padding-left:10px"></td><td id="catbox_7" style="padding-left:10px"></td><td id="catbox_8" style="padding-left:10px"></td><td id="catbox_9" style="padding-left:10px"></td><td id="catbox_10" style="padding-left:10px"></td><td id="catbox_11" style="padding-left:10px"></td><td id="catbox_12" style="padding-left:10px"></td><td id="catbox_13" style="padding-left:10px"></td><td id="catbox_14" style="padding-left:10px"></td><td id="catbox_15" style="padding-left:10px"></td></tr></table>
<?php
		echo $footerscript;
		if ($rootcid AND $ilance->GPC["$cidfield"] == 0)
		{
		    echo "<script type=\"text/javascript\">window.setTimeout(function(){xajax_print_next_category('0','catbox_1','pid','0','0','0','0','0','0','0','0','','0','0','0','0');}," . $ilconfig['globalfilters_categorydelayms'] . ");</script>";
		}
?>
	</body></html>
<?php
	}
	// ##### COUNTRY CHECKBOXES ####################################################
	else if ($ilance->GPC['do'] == 'cbcountries')
	{
		if (isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
		{
			$sql = $ilance->db->query("
				SELECT locationid, location_" . $_SESSION['ilancedata']['user']['slng'] . " AS title
				FROM " . DB_PREFIX . "locations
				WHERE visible = '1'
				ORDER BY location_" . $_SESSION['ilancedata']['user']['slng'] . " ASC
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$rc = 0;
				while ($res = $ilance->db->fetch_array($sql))
				{
					$res['class'] = ($rc % 2) ? 'alt2' : 'alt1';
					$res['cb'] = '<input type="checkbox" name="locationid[]" value="' . $res['locationid'] . '" />';
					$res['title'] = stripslashes($res['title']);
					$countries[] = $res;
					$rc++;
				}
				$ilance->template->load_popup('head', 'popup_header.html');
				$ilance->template->load_popup('main', 'ajax_countries.html');
				$ilance->template->load_popup('foot', 'popup_footer.html');
				$ilance->template->parse_loop('main', 'countries');
				$ilance->template->parse_if_blocks('head');
				$ilance->template->parse_if_blocks('main');
				$ilance->template->parse_if_blocks('foot');
				$ilance->template->pprint('head', array ('headinclude', 'onbeforeunload', 'onload', 'area_title', 'page_title', 'site_name', 'https_server', 'http_server', 'lanceads_header', 'lanceads_footer', 'meta_desc', 'meta_keyw', 'official_time'));
				$ilance->template->pprint('main', array ('headerstyle', 'bidamounttype', 'bidamounttype_pulldown', 'type', 'amount', 'fvf', 'ins', 'esc', 'final_conversion', 'category_pulldown', 'category_pulldown2', 'cid', 'remote_addr', 'rid',  'login_include', 'headinclude', 'onload', 'area_title', 'page_title', 'site_name', 'https_server', 'http_server', 'lanceads_header', 'lanceads_footer'));
				$ilance->template->pprint('foot', array ('headinclude', 'onload', 'area_title', 'page_title', 'site_name', 'https_server', 'http_server', 'lanceads_header', 'lanceads_footer', 'finaltime', 'finalqueries'));
				exit();
			}
			else
			{
				echo 'Could not fetch country list at this time.';
				exit();
			}
		}
	}
	// #### SKILLS CHECKBOXES ######################################################
	else if ($ilance->GPC['do'] == 'cbskills')
	{
		$headinclude .= '<script type="text/javascript">
<!--
var newArray = new Array();
var selectedskillbit;
var selectedskills;
var skillhiddenfields;
var skillshidden;
window.top.document.getElementById(\'selectedskills\').innerHTML = \'\';
window.top.document.getElementById(\'skillhiddenfields\').innerHTML = \'\';
function add_skill(cid, title)
{
	selectedskillbit = \'<div style="padding-bottom:3px"><span style="float:right; padding-right:10px; padding-top:8px"><input type="submit" value=" {_search} " class="buttons" style="font-size:15px" /></span><strong>{_you_have_selected_the_following_skills}</strong></div>\';
	selectedskills = \'\';
	skillhiddenfields = \'\';
	skillshidden = \'\';
	if (newArray[cid] != title)
	{
		newArray[cid] = title;
	}
	else if (newArray[cid] == title)
	{
		newArray[cid] = \'\';
	}
	for (i = 0; i <= newArray.length; i++)
	{
		skillshidden = \'\';
		if (newArray[i] != undefined && newArray[i] != \'\')
		{
			if (selectedskills != \'\')
			{
				selectedskills = selectedskills + \', <span class="gray">\' + newArray[i] + \'</span>\';
				skillshidden = \'<input type="hidden" name="sid[\' + i + \']" value="true" />\';
				skillhiddenfields = skillhiddenfields + skillshidden;
			}
			else
			{
				selectedskills = \'<span class="gray">\' + newArray[i] + \'</span>\';
				skillhiddenfields = \'<input type="hidden" name="sid[\' + i + \']" value="true" />\';
			}
		}
	}
	if (selectedskills == \'\')
	{
		window.top.document.getElementById(\'selectedskills\').innerHTML = \'\';
		window.top.document.getElementById(\'skillhiddenfields\').innerHTML = \'\';
	}
	else
	{
		window.top.document.getElementById(\'selectedskills\').innerHTML = selectedskillbit + " " + selectedskills;
		window.top.document.getElementById(\'skillhiddenfields\').innerHTML = skillhiddenfields;
	}
}
//-->
</script>';
		$cbskills = $ilance->categories_skills->print_skills_columns($_SESSION['ilancedata']['user']['slng'], 0, false, 4, true);
		$ilance->template->load_popup('head', 'popup_header.html');
		$ilance->template->load_popup('main', 'ajax_skills.html');
		$ilance->template->load_popup('foot', 'popup_footer.html');
		$ilance->template->pprint('head', array ());
		$ilance->template->pprint('main', array ('cbskills'));
		$ilance->template->pprint('foot', array ());
		exit();
	}
	// #### QUICK REGISTRATION #####################################################
	else if ($ilance->GPC['do'] == 'quickregister')
	{
		if (isset($ilance->GPC['qusername']) AND isset($ilance->GPC['qpassword']) AND isset($ilance->GPC['qemail']))
		{
			// some check-ups
			$unicode_name = preg_replace('/&#([0-9]+);/esiU', "convert_int2utf8('\\1')", $ilance->GPC['qusername']);
			$unicode_email = preg_replace('/&#([0-9]+);/esiU', "convert_int2utf8('\\1')", $ilance->GPC['qemail']);
			if ($ilance->common->is_username_banned($ilance->GPC['qusername']) OR $ilance->common->is_username_banned($unicode_name))
			{
				$ilance->template->templateregistry['quickregister_notice'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'infowarning3.gif" border="0" alt="" /> ' . date('h:i A') . ': <strong>{_registration_problem}</strong>: {_that_username_is_currently_banned}';
				echo $ilance->template->parse_template_phrases('quickregister_notice');
				exit();
			}
			$sqlusercheck = $ilance->db->query("
					SELECT user_id
					FROM " . DB_PREFIX . "users
					WHERE username IN ('" . addslashes(htmlspecialchars_uni($ilance->GPC['qusername'])) . "', '" . addslashes(htmlspecialchars_uni($unicode_name)) . "')
				", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sqlusercheck) > 0)
			{
			    $ilance->template->templateregistry['quickregister_notice'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'infowarning3.gif" border="0" alt="" /> ' . date('h:i A') . ': <strong>{_registration_problem}</strong>: {_that_username_already_exists_in_our_system}';
			    echo $ilance->template->parse_template_phrases('quickregister_notice');
			    exit();
			}
			$sqlemailcheck = $ilance->db->query("
				SELECT user_id
				FROM " . DB_PREFIX . "users
				WHERE email IN ('" . addslashes(htmlspecialchars_uni($ilance->GPC['qemail'])) . "', '" . addslashes(htmlspecialchars_uni($unicode_email)) . "')
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sqlemailcheck) > 0)
			{
				$ilance->template->templateregistry['quickregister_notice'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'infowarning3.gif" border="0" alt="" /> ' . date('h:i A') . ': <strong>{_registration_problem}</strong>: {_that_email_address_already_exists_in_our_system}';
				echo $ilance->template->parse_template_phrases('quickregister_notice');
				exit();
			}
			if (isset($ilance->GPC['qemail']) AND isset($ilance->GPC['qemail2']) AND $ilance->GPC['qemail'] != $ilance->GPC['qemail2'])
			{
				$ilance->template->templateregistry['quickregister_notice'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'infowarning3.gif" border="0" alt="" /> ' . date('h:i A') . ': <strong>{_registration_problem}</strong>: {_email_addresses_do_not_match}';
				echo $ilance->template->parse_template_phrases('quickregister_notice');
				exit();
			}
			if ($ilance->common->is_email_banned(trim($ilance->GPC['qemail'])) OR $ilance->common->is_email_valid(trim($ilance->GPC['qemail'])) == false)
			{
				$ilance->template->templateregistry['quickregister_notice'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'infowarning3.gif" border="0" alt="" /> ' . date('h:i A') . ': <strong>{_registration_problem}</strong>: {_it_appears_this_email_address_is_banned_from_the_marketplace_please_try_another_email_address}';
				echo $ilance->template->parse_template_phrases('quickregister_notice');
				exit();
			}
			$sqlemailcheck = $ilance->db->query("
				SELECT user_id
				FROM " . DB_PREFIX . "users
				WHERE email = '" . $ilance->db->escape_string($ilance->GPC['qemail']) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sqlemailcheck) > 0)
			{
				$ilance->template->templateregistry['quickregister_notice'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'infowarning3.gif" border="0" alt="" /> ' . date('h:i A') . ': <strong>{_registration_problem}</strong>: {_that_email_address_already_exists_in_our_system}';
				echo $ilance->template->parse_template_phrases('quickregister_notice');
				exit();
			}
			// set new member defaults
			$user = array();
			$subscription = array();
			$preferences = '';
			$user['roleid'] = '-1';
			$user['username'] = trim($ilance->GPC['qusername']);
			$user['password'] = $ilance->GPC['qpassword'];
			$user['secretquestion'] = '{_what_is_my_email_address}';
			$user['secretanswer'] = md5($ilance->GPC['qemail']);
			$user['email'] = $ilance->GPC['qemail'];
			$user['firstname'] = $user['lastname'] = $user['address'] = $user['city'] = $user['state'] = $user['zipcode'] = '{_unknown}';
			$user['phone'] = '000-000-0000';
			$user['countryid'] = fetch_country_id($ilconfig['registrationdisplay_defaultcountry'], $_SESSION['ilancedata']['user']['slng']);
			$user['styleid'] = $_SESSION['ilancedata']['user']['styleid'];
			$user['slng'] = $_SESSION['ilancedata']['user']['slng'];
			$user['languageid'] = $_SESSION['ilancedata']['user']['languageid'];
			// we must tell the registration system what plan to set as default!
			$subscription['subscriptionid'] = (isset($ilance->GPC['subscriptionid'])) ? intval($ilance->GPC['subscriptionid']) : '1';
			$subscription['subscriptionpaymethod'] = (isset($ilance->GPC['subscriptionpaymethod'])) ? $ilance->GPC['subscriptionpaymethod'] : 'account';
			$subscription['promocode'] = '';
			$questions = array();
			$final = $ilance->registration->build_user_datastore($user, $preferences, $subscription, $questions, 'return_userarray');
			if (!empty($final))
			{
				// set cookies
				set_cookie('userid', $ilance->crypt->three_layer_encrypt($final['userid'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']));
				set_cookie('username', $ilance->crypt->three_layer_encrypt($final['username'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']));
				set_cookie('password', $ilance->crypt->three_layer_encrypt($final['password'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']));
				set_cookie('lastvisit', DATETIME24H);
				set_cookie('lastactivity', DATETIME24H);
				switch ($final['status'])
				{
					case 'active':
					{
						if (!empty($_SESSION['ilancedata']['user']['password_md5']))
						{
							$_SESSION['ilancedata']['user']['password'] = $_SESSION['ilancedata']['user']['password_md5'];
							unset($_SESSION['ilancedata']['user']['password_md5']);
						}
						$ilance->template->templateregistry['quickregister_notice'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/picture.gif" border="0" alt="" /> ' . date('h:i A') . ': <strong>{_registration_complete}</strong>';
						echo $ilance->template->parse_template_phrases('quickregister_notice');
						break;
					}
					case 'unverified':
					{
						$ilance->template->templateregistry['quickregister_notice'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/picture.gif" border="0" alt="" /> ' . date('h:i A') . ': <strong>{_registration_not_completed}</strong><div style="padding-top:4px">{_thank_you_for_registering_an_email_has_been_dispatched_to_you}</div>';
						echo $ilance->template->parse_template_phrases('quickregister_notice');
						break;
					}
				}
				exit();
			}
			else
			{
				$ilance->template->templateregistry['quickregister_notice'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'infowarning3.gif" border="0" alt="" /> ' . date('h:i A') . ': <strong> {_registration_problem}</strong>: {_sorry_there_was_a_problem_completing_your_registration_we_apologize}';
				echo $ilance->template->parse_template_phrases('quickregister_notice');
				exit();
			}
		}
	}
	// #### SEARCH PAGE PROFILE FILTER CATEGORY RESULTS ############################
	else if ($ilance->GPC['do'] == 'profilefilters')
	{
		$cid = 0;
		if (isset($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0)
		{
			$cid = intval($ilance->GPC['cid']);
		}
		$ilance->template->templateregistry['profile_bid_filters'] = $ilance->auction_post->print_profile_bid_filters($cid, 'input', 'service');
		echo '<div id="profile_filters_text">' . $ilance->template->parse_template_phrases('profile_bid_filters') . '</div>';
		exit();
	}
	// #### AUTOCOMPLETE SEARCH BAR ################################################
	else if ($ilance->GPC['do'] == 'autocomplete')
	{
		include_once(DIR_CORE . 'functions_autocomplete.php');
		$xmlDoc = '<?xml version="1.0" encoding="utf-8"?>';
		$xmlDoc .= '<root>';
		if (isset($ilance->GPC['q']) AND !empty($ilance->GPC['q']))
		{
			$keyword_text = $ilance->GPC['q'];
			$sqlquery['keywords'] = '';
			$keyword_text_array = preg_split("/[\s,]+/", trim($keyword_text));
			if (sizeof($keyword_text_array) > 1)
			{
				$sqlquery['keywords'] .= 'AND (';
				for ($i = 0; $i < sizeof($keyword_text_array); $i++)
				{
					$sqlquery['keywords'] .= "keyword LIKE '%" . $ilance->db->escape_string($keyword_text_array[$i]) . "%' OR ";
					$keywords_array[] = $keyword_text_array[$i];
				}
				$sqlquery['keywords'] = mb_substr($sqlquery['keywords'], 0, -4) . ')';
			}
			else
			{
				$keywords_array[] = $keyword_text_array[0];
				$sqlquery['keywords'] .= "AND (keyword LIKE '%" . $ilance->db->escape_string($keyword_text_array[0]) . "%')";
			}
			$stopwords = file(DIR_CORE . 'functions_stop_words.dat');
			$available1 = $available2 = array();
			$sql = $ilance->db->query("
				SELECT LOWER(keyword) AS keyword, searchmode
				FROM " . DB_PREFIX . "search
				WHERE count > 10
					$sqlquery[keywords]
					AND visible = '1'
				GROUP BY keyword
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					$available1[] = stop_words($res['keyword'], $stopwords);
					$availablemode[stop_words($res['keyword'], $stopwords)] = $res['searchmode'];
				}
			}
			$sqlquery['keywords'] = '';
			$keyword_text_array = preg_split("/[\s,]+/", trim($keyword_text));
			if (sizeof($keyword_text_array) > 1)
			{
				$sqlquery['keywords'] .= 'AND (';
				for ($i = 0; $i < sizeof($keyword_text_array); $i++)
				{
					$sqlquery['keywords'] .= "project_title LIKE '%" . $ilance->db->escape_string($keyword_text_array[$i]) . "%' OR ";
					$keywords_array[] = $keyword_text_array[$i];
				}
				$sqlquery['keywords'] = mb_substr($sqlquery['keywords'], 0, -4) . ')';
			}
			else
			{
				$keywords_array[] = $keyword_text_array[0];
				$sqlquery['keywords'] .= "AND (project_title LIKE '%" . $ilance->db->escape_string($keyword_text_array[0]) . "%')";
			}
			$sql = $ilance->db->query("
				SELECT LOWER(project_title) AS project_title, project_state
				FROM " . DB_PREFIX . "projects
				WHERE status = 'open'
					$sqlquery[keywords]
				GROUP BY project_title
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					$available2[] = stop_words($res['project_title'], $stopwords);
					$availablemode[stop_words($res['project_title'], $stopwords)] = (($res['project_state'] == 'stores') ? 'product' : $res['project_state']);
				}
			}
			$available = array_merge($available1, $available2);
			$available = array_unique($available);
			$results = $available;
			if (count($results) > 0)
			{
				$i = 0;
				foreach ($results AS $key => $label)
				{
					if ($i <= 10)
					{
						$label = stop_words($label, $stopwords);
						$labelformatted = str_replace(mb_strtolower($ilance->GPC['q']), '<span class="black"><strong>' . mb_strtolower($ilance->GPC['q']) . '</strong></span>', $label);
						$labelformatted = '<div class="search_autocomplete_label">' . $labelformatted . '</div>';
						$xmlDoc .= '<item id="' . $i . '" label="' . handle_input_keywords($labelformatted) . '" text="' . handle_input_keywords($label) . '" searchmode="' . $availablemode[handle_input_keywords($label)] . '"></item>';
					}
					$i++;
				}
			}
		}
		$xmlDoc .= '</root>';
		header('Content-type: application/xml; charset="' . $ilconfig['template_charset'] . '"');
		echo $xmlDoc;
		exit();
	}
	// #### REFRESH PROJECT DETAIL PAGE ############################################
	else if ($ilance->GPC['do'] == 'refreshprojectdetails')
	{
		if (isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
		{
			include_once(DIR_CORE . 'functions_ajax_service_response.php');
			echo fetch_service_response($ilance->GPC['id']);
			exit();
		}
	}
	// #### REFRESH ITEM DETAIL PAGE ###############################################
	else if ($ilance->GPC['do'] == 'refreshitemdetails')
	{
		if (isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['type']) AND !empty($ilance->GPC['type']))
		{
			include_once(DIR_CORE . 'functions_ajax_product_response.php');
			echo fetch_product_response($ilance->GPC['id'], $ilance->GPC['type']);
			exit();
		}
	}
	else if ($ilance->GPC['do'] == 'refreshitemdetailsv4')
	{
		if (isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['type']) AND !empty($ilance->GPC['type']))
		{
			include_once(DIR_CORE . 'functions_ajax_product_response.php');
			echo fetch_product_response_v4($ilance->GPC['id'], $ilance->GPC['type']);
			exit();
		}
	}
	// #### SHOW STATES BASED ON COUNTRIES #########################################
	else if ($ilance->GPC['do'] == 'showstates')
	{
		if (isset($ilance->GPC['countryname']) AND !empty($ilance->GPC['countryname']) AND isset($ilance->GPC['fieldname']) AND !empty($ilance->GPC['fieldname']))
		{
			if ($ilance->GPC['countryname'] > 0)
			{
				$locationid = intval($ilance->GPC['countryname']);
			}
			else
			{
				$locationid = fetch_country_id($ilance->GPC['countryname'], $_SESSION['ilancedata']['user']['slng']);
			}
			$shortform = isset($ilance->GPC['shortform']) ? intval($ilance->GPC['shortform']) : 0;
			$extracss = isset($ilance->GPC['extracss']) ? $ilance->GPC['extracss'] : '';
			$disablecities = isset($ilance->GPC['disablecities']) ? intval($ilance->GPC['disablecities']) : 1;
			$citiesfieldname = isset($ilance->GPC['citiesfieldname']) ? $ilance->GPC['citiesfieldname'] : 'city';
			$citiesdivid = isset($ilance->GPC['citiesdivid']) ? $ilance->GPC['citiesdivid'] :  'cityid';
			$html = $ilance->common_location->construct_state_pulldown($locationid, '', $ilance->GPC['fieldname'], false, true, $shortform, $extracss, $disablecities, $citiesfieldname, $citiesdivid);
			    
			($apihook = $ilance->api('ajax_do_showstates_end')) ? eval($apihook) : false;
	    
			$ilance->template->templateregistry['showstates'] = $html;
			echo $ilance->template->parse_template_phrases('showstates');
			exit();
		}
	}
	// #### SHOW CITIES BASED ON STATE ##############################################
	else if ($ilance->GPC['do'] == 'showcities')
	{
		if (isset($ilance->GPC['state']) AND !empty($ilance->GPC['state']) AND isset($ilance->GPC['fieldname']) AND !empty($ilance->GPC['fieldname']))
		{
			$extracss = isset($ilance->GPC['extracss']) ? $ilance->GPC['extracss'] : '';
			$html = $ilance->common_location->construct_city_pulldown($ilance->GPC['state'], $ilance->GPC['fieldname'], '', false, true, $extracss);
	    
			($apihook = $ilance->api('ajax_do_showcities_end')) ? eval($apihook) : false;
	    
			$ilance->template->templateregistry['showcities'] = $html;
			echo $ilance->template->parse_template_phrases('showcities');
			exit();
		}
	}
	// #### SHOW SHIPPING SERVICES BASED ON SHIP TO OPTION SELECTED ################
	else if ($ilance->GPC['do'] == 'showshippers')
	{
		if (isset($ilance->GPC['fieldname']) AND !empty($ilance->GPC['fieldname']))
		{
			if (($html = $ilance->cache->fetch('showshippers_' . $ilance->GPC['fieldname'])) === false)
			{
				$ilance->GPC['domestic'] = isset($ilance->GPC['domestic']) ? $ilance->GPC['domestic'] : 'false';
				$ilance->GPC['international'] = isset($ilance->GPC['international']) ? $ilance->GPC['international'] : 'false';
				$ilance->GPC['shipperid'] = isset($ilance->GPC['shipperid']) ? intval($ilance->GPC['shipperid']) : 0;
				$ilance->GPC['disabled'] = isset($ilance->GPC['disabled']) ? $ilance->GPC['disabled'] : false;
				$ilance->GPC['jspackagetype'] = isset($ilance->GPC['jspackagetype']) ? intval($ilance->GPC['jspackagetype']) : 0;
				$ilance->GPC['jspackagedivcontent'] = isset($ilance->GPC['jspackagedivcontent']) ? $ilance->GPC['jspackagedivcontent'] : '';
				$ilance->GPC['jspackagefieldname'] = isset($ilance->GPC['jspackagefieldname']) ? $ilance->GPC['jspackagefieldname'] : '';
				$ilance->GPC['jspackagevalue'] = isset($ilance->GPC['jspackagevalue']) ? $ilance->GPC['jspackagevalue'] : '';
				$ilance->GPC['jspickupdivcontent'] = isset($ilance->GPC['jspickupdivcontent']) ? $ilance->GPC['jspickupdivcontent'] : '';
				$ilance->GPC['jspickupfieldname'] = isset($ilance->GPC['jspickupfieldname']) ? $ilance->GPC['jspickupfieldname'] : '';
				$ilance->GPC['jspickupvalue'] = isset($ilance->GPC['jspickupvalue']) ? $ilance->GPC['jspickupvalue'] : '';
				$ilance->GPC['ship_method'] = isset($ilance->GPC['ship_method']) ? $ilance->GPC['ship_method'] : '';
				$html = $ilance->auction_post->print_shipping_partners($ilance->GPC['fieldname'], false, $ilance->GPC['domestic'], $ilance->GPC['international'], $ilance->GPC['shipperid'], $ilance->GPC['disabled'], $ilance->GPC['jspackagetype'], $ilance->GPC['jspackagedivcontent'], $ilance->GPC['jspackagefieldname'], $ilance->GPC['jspackagevalue'], $ilance->GPC['jspickupdivcontent'], $ilance->GPC['jspickupfieldname'], $ilance->GPC['jspickupvalue'], '150', $ilance->GPC['ship_method']);
				$ilance->cache->store('showshippers', $html);
			}
	    
			($apihook = $ilance->api('ajax_do_showshippers_end')) ? eval($apihook) : false;
	    
			echo $html;
			exit();
		}
	}
	// #### SHOW SHIPPING PACKAGE TYPES BASED ON SHIPPING SERVICE ##################
	else if ($ilance->GPC['do'] == 'showshippackages')
	{
		if (isset($ilance->GPC['fieldname']) AND !empty($ilance->GPC['fieldname']))
		{
			$ilance->GPC['shipperid'] = isset($ilance->GPC['shipperid']) ? intval($ilance->GPC['shipperid']) : 0;
			$ilance->GPC['packageid'] = isset($ilance->GPC['packageid']) ? $ilance->GPC['packageid'] : '';
			$ilance->GPC['disabled'] = isset($ilance->GPC['disabled']) ? $ilance->GPC['disabled'] : false;
			$html = $ilance->auction_post->print_shipping_packages($ilance->GPC['fieldname'], $ilance->GPC['packageid'], $ilance->GPC['disabled'], $ilance->GPC['shipperid'], '150');
	    
			($apihook = $ilance->api('ajax_do_showshippackages_end')) ? eval($apihook) : false;
	    
			echo $html;
			exit();
		}
	}
	// #### SHOW SHIPPING PICK-UP/DROP OFF TYPES BASED ON SHIPPING SERVICE #########
	else if ($ilance->GPC['do'] == 'showshippickupdropoff')
	{
		if (isset($ilance->GPC['fieldname']) AND !empty($ilance->GPC['fieldname']))
		{
			$ilance->GPC['shipperid'] = isset($ilance->GPC['shipperid']) ? intval($ilance->GPC['shipperid']) : 0;
			$ilance->GPC['pickupid'] = isset($ilance->GPC['pickupid']) ? $ilance->GPC['pickupid'] : '';
			$ilance->GPC['disabled'] = isset($ilance->GPC['disabled']) ? $ilance->GPC['disabled'] : false;
			$html = $ilance->auction_post->print_shipping_pickupdropoff($ilance->GPC['fieldname'], $ilance->GPC['pickupid'], $ilance->GPC['disabled'], $ilance->GPC['shipperid'], '150');
	    
			($apihook = $ilance->api('ajax_do_showshippickups_end')) ? eval($apihook) : false;
	    
			echo $html;
			exit();
		}
	}
	// #### SHOW DYNAMIC DURATION LOGIC ############################################
	else if ($ilance->GPC['do'] == 'showduration')
	{
		if (isset($ilance->GPC['fieldname']) AND !empty($ilance->GPC['fieldname']))
		{
			$ilance->GPC['unittype'] = isset($ilance->GPC['unittype']) ? $ilance->GPC['unittype'] : 'D';
			$ilance->GPC['showprices'] = isset($ilance->GPC['showprices']) ? $ilance->GPC['showprices'] : true;
			$ilance->GPC['cid'] = isset($ilance->GPC['cid']) ? $ilance->GPC['cid'] : 0;
			$ilance->GPC['disabled'] = isset($ilance->GPC['disabled']) ? $ilance->GPC['disabled'] : false;
			$ilance->GPC['duration'] = isset($ilance->GPC['duration']) ? intval($ilance->GPC['duration']) : '';
			$html = $ilance->auction_post->duration($ilance->GPC['duration'], $ilance->GPC['fieldname'], $ilance->GPC['disabled'], $ilance->GPC['unittype'], $ilance->GPC['showprices'], $ilance->GPC['cid']);
			$ilance->template->templateregistry['durationoutput'] = $html;
			$html = $ilance->template->parse_template_phrases('durationoutput');
	    
			($apihook = $ilance->api('ajax_do_showduration_end')) ? eval($apihook) : false;
	    
			echo $html;
			exit();
		}
	}
	// #### SHOW SHIPPING SERVICES BASED ON SHIP TO OPTION SELECTED ################
	else if ($ilance->GPC['do'] == 'shipcalculator')
	{
		if (isset($ilance->GPC['shipperid']) AND isset($ilance->GPC['weightlbs']) AND isset($ilance->GPC['country_from']) AND isset($ilance->GPC['zipcode_from']) AND isset($ilance->GPC['country_to']) AND isset($ilance->GPC['zipcode_to']))
		{
			$ilance->GPC['state_from'] = isset($ilance->GPC['state_from']) ? $ilance->GPC['state_from'] : ''; // required for fedex only
			$ilance->GPC['state_to'] = isset($ilance->GPC['state_to']) ? $ilance->GPC['state_to'] : ''; // required for fedex only
			$ilance->GPC['city_to'] = isset($ilance->GPC['city_to']) ? $ilance->GPC['city_to'] : ''; // required for fedex only
			$ilance->GPC['city_from'] = isset($ilance->GPC['city_from']) ? $ilance->GPC['city_from'] : ''; // required for fedex only
			$ilance->GPC['carrier'] = $ilance->db->fetch_field(DB_PREFIX . "shippers", "shipperid = '" . intval($ilance->GPC['shipperid']) . "'", "carrier");
			$ilance->GPC['shipcode'] = $ilance->db->fetch_field(DB_PREFIX . "shippers", "shipperid = '" . intval($ilance->GPC['shipperid']) . "'", "shipcode");
			$ilance->GPC['length'] = isset($ilance->GPC['length']) ? $ilance->GPC['length'] : 12;
			$ilance->GPC['width'] = isset($ilance->GPC['width']) ? $ilance->GPC['width'] : 12;
			$ilance->GPC['height'] = isset($ilance->GPC['height']) ? $ilance->GPC['height'] : 12;
			$ilance->GPC['weightlbs'] = isset($ilance->GPC['weightlbs']) ? intval($ilance->GPC['weightlbs']) : 1;
			$ilance->GPC['weightoz'] = isset($ilance->GPC['weightoz']) ? intval($ilance->GPC['weightoz']) : 1;
			$ilance->GPC['weight'] = $ilance->GPC['weightlbs'] . '.' . $ilance->GPC['weightoz'];
			$ilance->GPC['pickuptype'] = isset($ilance->GPC['pickuptype']) ? $ilance->GPC['pickuptype'] : $ilance->shipcalculator->pickuptypes($ilance->GPC['carrier'], true);
			$ilance->GPC['packagetype'] = isset($ilance->GPC['packagetype']) ? $ilance->GPC['packagetype'] : $ilance->shipcalculator->packagetypes($ilance->GPC['carrier'], $ilance->GPC['shipcode'], true);
			$ilance->GPC['weightunit'] = isset($ilance->GPC['weightunit']) ? $ilance->GPC['weightunit'] : $ilance->shipcalculator->weightunits($ilance->GPC['carrier'], true);
			$ilance->GPC['dimensionunit'] = isset($ilance->GPC['dimensionunit']) ? $ilance->GPC['dimensionunit'] : $ilance->shipcalculator->dimensionunits($ilance->GPC['carrier'], true);
			$ilance->GPC['sizecode'] = isset($ilance->GPC['sizecode']) ? $ilance->GPC['sizecode'] : $ilance->shipcalculator->sizeunits($ilance->GPC['carrier'], $ilance->GPC['length'], $ilance->GPC['width'], $ilance->GPC['height'], true);
			$carriers[$ilance->GPC['carrier']] = true;
			$shipinfo = array('weight' => handle_input_keywords(trim($ilance->GPC['weight'])),
				'destination_zipcode' => handle_input_keywords(format_zipcode(trim($ilance->GPC['zipcode_to']))),
				'destination_state' => handle_input_keywords(trim($ilance->GPC['state_to'])),
				'destination_city' => handle_input_keywords(trim($ilance->GPC['city_to'])),
				'destination_country' => handle_input_keywords(trim($ilance->GPC['country_to'])),
				'origin_zipcode' => handle_input_keywords(format_zipcode(trim($ilance->GPC['zipcode_from']))),
				'origin_state' => handle_input_keywords(trim($ilance->GPC['state_from'])),
				'origin_city' => handle_input_keywords(trim($ilance->GPC['city_from'])),
				'origin_country' => handle_input_keywords(trim($ilance->GPC['country_from'])),
				'carriers' => $carriers,
				'shipcode' => handle_input_keywords(trim($ilance->GPC['shipcode'])),
				'length' => handle_input_keywords(trim($ilance->GPC['length'])),
				'width' => handle_input_keywords(trim($ilance->GPC['width'])),
				'height' => handle_input_keywords(trim($ilance->GPC['height'])),
				'pickuptype' => handle_input_keywords(trim($ilance->GPC['pickuptype'])),
				'packagingtype' => handle_input_keywords(trim($ilance->GPC['packagetype'])),
				'weightunit' => handle_input_keywords(trim($ilance->GPC['weightunit'])),
				'dimensionunit' => handle_input_keywords(trim($ilance->GPC['dimensionunit'])),
				'sizecode' => handle_input_keywords(trim($ilance->GPC['sizecode']))
			);
			$rates = $ilance->shipcalculator->get_rates($shipinfo);
			if (isset($rates['price'][0]))
			{
				$currencyid = (isset($rates['currency'][0]) AND isset($ilance->currency->currencies[$rates['currency'][0]]['currency_id'])) ? $ilance->currency->currencies[$rates['currency'][0]]['currency_id'] : 1;
				echo '<h1><strong>' . $ilance->currency->format(sprintf("%01.2f", $rates['price'][0]), $currencyid) . '</strong></h1>';
			}
			else
			{
				if (isset($rates['errordesc']))
				{
					echo '<div class="smaller red" style="max-width:415px">' . handle_input_keywords($rates['errordesc']) . '</div>';
				}
				else
				{
					$ilance->template->templateregistry['output'] = '{_out_of_region_try_again}';
					echo $ilance->template->parse_template_phrases('output');
				}
				exit();
			}
	    
			($apihook = $ilance->api('ajax_do_shipcalculator_end')) ? eval($apihook) : false;
	    
			unset($test);
			exit();
		}
	}
	// #### SHOW SHIPPING SERVICE ROWS BASED ON SHIP TO COUNTRY OPTION SELECTED ####
	else if ($ilance->GPC['do'] == 'showshipservicerows')
	{
		if (isset($ilance->GPC['countryid']) AND isset($ilance->GPC['pid']) AND isset($ilance->GPC['state']) AND isset($ilance->GPC['city']) AND isset($ilance->GPC['radiuszip']))
		{
			// #### require shipping backend #######################################
			include_once(DIR_CORE . 'functions_shipping_ajax.php');
			$output = '';
			$rows = 0;
			$ilance->GPC['qty'] = isset($ilance->GPC['qty']) ? intval($ilance->GPC['qty']) : 1;
			$ilance->GPC['radiuszip'] = isset($ilance->GPC['radiuszip']) ? format_zipcode($ilance->GPC['radiuszip']) : (!empty($_COOKIE[COOKIE_PREFIX . 'radiuszip']) ? $_COOKIE[COOKIE_PREFIX . 'radiuszip'] : '');
			$ilance->GPC['state'] = isset($ilance->GPC['state']) ? $ilance->GPC['state'] : '';
			$ilance->GPC['city'] = isset($ilance->GPC['city']) ? $ilance->GPC['city'] : '';
			$ilance->GPC['vqty'] = isset($ilance->GPC['vqty']) ? intval($ilance->GPC['vqty']) : 1;
			$result = $ilance->db->query("
				SELECT p.row, l.location_" . $_SESSION['ilancedata']['user']['slng'] . " AS countrytitle, r.region_" . $_SESSION['ilancedata']['user']['slng'] . " AS region
				FROM " . DB_PREFIX . "projects_shipping_regions p
				LEFT JOIN " . DB_PREFIX . "locations l ON (p.countryid = l.locationid)
				LEFT JOIN " . DB_PREFIX . "locations_regions r ON (r.regionid = l.regionid)
				WHERE p.project_id = '" . intval($ilance->GPC['pid']) . "'
					AND p.countryid = '" . intval($ilance->GPC['countryid']) . "'
					AND l.visible = '1'
				ORDER BY p.row ASC
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($result) > 0)
			{
				while ($res = $ilance->db->fetch_array($result, DB_ASSOC))
				{
					$ilance->GPC['country'] = $res['countrytitle'];
					$ship_service_row = fetch_ajax_ship_service_row($res['row'], $ilance->GPC['pid'], $res['countrytitle'], $res['region'], $ilance->GPC['qty'], $ilance->GPC['vqty'], $ilance->GPC['radiuszip'], $ilance->GPC['state'], $ilance->GPC['city']);
					$output .= '|' . $ship_service_row; // returns: |ship cost~~~~ship to country title~~~~ship service title~~~~est delivery info
					$rows++;
				}
			}
			// check if user supplied us with a country
			if (!empty($ilance->GPC['country']))
			{
				set_cookie('country', handle_input_keywords($ilance->GPC['country']));
			}
			// check if user supplied us with a state
			if (!empty($ilance->GPC['state']))
			{
				set_cookie('state', handle_input_keywords($ilance->GPC['state']));
			}
			// check if user supplied us with a city
			if (!empty($ilance->GPC['city']))
			{
				set_cookie('city', handle_input_keywords($ilance->GPC['city']));
			}
			// check if user supplied us with a zip code
			if (!empty($ilance->GPC['radiuszip']))
			{
				set_cookie('radiuszip', handle_input_keywords(format_zipcode($ilance->GPC['radiuszip'])));
			}
			echo $rows . $output;
	    
			($apihook = $ilance->api('ajax_do_showshipservicerows_end')) ? eval($apihook) : false;
	    
			exit();
		}
	}
	// #### SHOW REGISTRATION CUSTOM QUESTIONS FOR SELECTED ROLE ID ################
	else if ($ilance->GPC['do'] == 'registrationquestions')
	{
		if (isset($ilance->GPC['roleid']) AND $ilance->GPC['roleid'] > 0)
		{
			$customquestions = $ilance->registration_questions->construct_register_questions(1, 'input', 0, $ilance->GPC['roleid']);
			$ilance->template->templateregistry['customquestions'] = $customquestions;
			echo $ilance->template->parse_template_phrases('customquestions');
			exit();
		}
	}
	// #### CALCULATE INSERTION AND FV FEES ########################################
	else if ($ilance->GPC['do'] == 'calculateinsertionfees')
	{
		$cid = intval($ilance->GPC['cid']);
		$htmlinsertionfees = '';
		$startprice = isset($ilance->GPC['startprice']) ? floatval($ilance->GPC['startprice']) : 0.00;
		$reserve_price = isset($ilance->GPC['reserve_price']) ? floatval($ilance->GPC['reserve_price']) : 0.00;
		$buynow_price = isset($ilance->GPC['buynow_price']) ? floatval($ilance->GPC['buynow_price']) : 0.00;
		$currencyid = isset($ilance->GPC['currencyid']) ? $ilance->GPC['currencyid'] : $ilconfig['globalserverlocale_defaultcurrency'];
		$price = $startprice;
		if ($reserve_price > $startprice)
		{
			$price = $reserve_price;
			if ($buynow_price > $reserve_price)
			{
				$price = $buynow_price;
			}
		}
		else if ($buynow_price > $startprice)
		{
			$price = $buynow_price;
		}
		$price = convert_currency($ilconfig['globalserverlocale_defaultcurrency'], $price, $currencyid);
		$sql = $ilance->db->query("
			SELECT insertiongroup, finalvaluegroup, cattype
			FROM " . DB_PREFIX . "categories 
			WHERE cid = '" . $cid . "'
		", 0, null, __FILE__, __LINE__);
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		$sql_fees = $ilance->db->query("
			SELECT insertionid
			FROM " . DB_PREFIX . "insertion_fees
			WHERE groupname = '" . $res['insertiongroup'] . "'
		", 0, null, __FILE__, __LINE__);
		// check for membership insertion fee exemption
		if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'insexempt') == 'yes')
		{
			$htmlinsertionfees = '<tr><td valign="top" colspan="2"><span class="gray">{_you_are_exempt_from_insertion_fees}</span></td></tr>';
		}
		else
		{
			if ($res['insertiongroup'] != '' AND $res['insertiongroup'] != '0' AND $ilance->db->num_rows($sql_fees) > 0 AND !empty($_SESSION['ilancedata']['user']['userid']))
			{
				$ifgroupname = $res['insertiongroup'];
				$forceifgroupid = $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], "{$res['cattype']}insgroup");
				if ($forceifgroupid > 0)
				{
					$ifgroupname = $ilance->db->fetch_field(DB_PREFIX . "insertion_groups", "groupid = '" . intval($forceifgroupid) . "'", "groupname");
				}
				$sqlinsertions = $ilance->db->query("
					SELECT insertionid, groupname, insertion_from, insertion_to, amount, sort, state
					FROM " . DB_PREFIX . "insertion_fees
					WHERE groupname = '" . $ilance->db->escape_string($ifgroupname) . "'
						AND state = '" . $ilance->db->escape_string($res['cattype']) . "'
						AND (insertion_from < '" . $ilance->db->escape_string($price) . "' OR insertion_from = '" . $ilance->db->escape_string($price) . "')
						AND (insertion_to > '" . $ilance->db->escape_string($price) . "' OR insertion_to = '" . $ilance->db->escape_string($price) . "' OR insertion_to = '-1')
					ORDER BY sort ASC
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sqlinsertions) > 0)
				{
					$res_ins = $ilance->db->fetch_array($sqlinsertions, DB_ASSOC);
					$fee = $ilance->currency->format($res_ins['amount']);
					$show['insertionfeeamount'] = $res_ins['amount'];
					$htmlinsertionfees .= '<tr class="alt1"><td valign="top">' . $ilance->currency->format($res_ins['insertion_from']) . ' - <b>' . $ilance->currency->format($price) . '</b>' . (($res_ins['insertion_to'] != '-1') ? (' - ' . $ilance->currency->format($res_ins['insertion_to'])) : ' ({_or_more})') . '</td><td valign="top"><b>' . $fee . '</b></td></tr>';
				}
			}
			else
			{
				$show['insertionfees'] = $show['insertionfeeamount'] = 0;
				$htmlinsertionfees .= '<tr><td valign="top" colspan="2"><span class="gray">{_no_insertion_fees_within_this_category}</span></td></tr>';
			}
		}
		$listingfees = '<div class="block-wrapper">
<div class="block">
<div class="block-top">
	<div class="block-right">
		<div class="block-left"></div>
	</div>
</div>
<div class="block-header">{_insertion_listing_fees}</div>
<div class="block-content" style="padding:0px">
<table border="0" cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
</tr>
<tr class="alt2">
	<td valign="top"><strong>{_start_price_or_reserve_amount} {_or} {_buy_now_price}</strong></td>
	<td valign="top"><strong>{_insertion_fee_amount}</strong></td>
</tr>
' . $htmlinsertionfees . '
</table></div>
	<div class="block-footer">
		<div class="block-right">
				<div class="block-left"></div>
		</div>
	</div>
	</div>
</div>';
		$ilance->template->templateregistry['listingfees'] = $listingfees;
		//###### FVF ###########################################################
		$htmlfinalvaluefees = $bidamounttype = '';
		// check for membership fvf exemption
		if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'fvfexempt') == 'yes')
		{
			$htmlfinalvaluefees = '<tr><td valign="top" colspan="2" class="alt1"><span class="gray">{_you_are_exempt_from_final_value_fees}</span></td></tr>';
		}
		else
		{
			if ($res['finalvaluegroup'] != '' AND $res['finalvaluegroup'] != '0' AND !empty($_SESSION['ilancedata']['user']['userid']))
			{
				// first check if admin uses fixed fees in this category instead of final value fees
				if ($ilance->categories->usefixedfees($cid) AND isset($bidamounttype) AND !empty($bidamounttype))
				{
					// admin charges a fixed fee within this category to service providers
					// let's determine if the bid amount type logic is configured
					if ($bidamounttype != 'entire' AND $bidamounttype != 'item' AND $bidamounttype != 'lot')
					{
						// bid amount type passes accepted commission types
						// let's output our final value fee table
						if ($cattype == 'service')
						{
							$htmlfinalvaluefees .= '<tr><td class="alt1">{_no_awarded_provider}</td><td class="alt1"><strong>{_no_fee}</strong></td></tr>';
						}
						else
						{
							$htmlfinalvaluefees .= '<tr><td class="alt1">{_no_winning_bid}</td><td class="alt1"><strong>{_no_fee}</strong></td></tr>';
						}
						$htmlfinalvaluefees .= '<tr><td valign="top" nowrap="nowrap" class="alt1">' . $ilance->currency->format(0.01) . ' {_or_more}</td><td valign="top" class="alt1">' . $ilance->currency->format($ilance->categories->fixedfeeamount($cid)) . ' ({_fixed})</td></tr>';
					}
					else
					{
						$htmlfinalvaluefees .= '<tr><td valign="top" colspan="2" class="alt1"><span class="gray">{_no_final_value_fees_within_this_category}</span></td></tr>';
					}
				}
				else
				{
					$show['finalvaluefees'] = 1;
					$fvfgroupname = $ilance->categories->finalvaluegroup($cid);
					$forcefvfgroupid = $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], "{$res['cattype']}fvfgroup");
					if ($forcefvfgroupid > 0)
					{
						$fvfgroupname = $ilance->db->fetch_field(DB_PREFIX . "finalvalue_groups", "groupid = '" . intval($forcefvfgroupid) . "'", "groupname");
					}
					$sqlfinalvalues = $ilance->db->query("
						SELECT tierid, groupname, finalvalue_from, finalvalue_to, amountfixed, amountpercent, state, sort
						FROM " . DB_PREFIX . "finalvalue
						WHERE groupname = '" . $ilance->db->escape_string($fvfgroupname) . "'
							AND state = '" . $ilance->db->escape_string($res['cattype']) . "'
						ORDER BY sort ASC
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sqlfinalvalues) > 0)
					{
						$tier = 1;
						while ($rows = $ilance->db->fetch_array($sqlfinalvalues, DB_ASSOC))
						{
							$from = $ilance->currency->format($rows['finalvalue_from']);
							$to = ' &ndash; ' . $ilance->currency->format($rows['finalvalue_to']);
							if ($rows['amountfixed'] > 0)
							{
								$amountraw = $rows['amountfixed'];
								$amount = '<strong>' . $ilance->currency->format($rows['amountfixed']) . '</strong> {_fixed_price}';
							}
							else
							{
								$amountraw = $rows['amountpercent'];
								if ($tier == 1)
								{
									$amount = '<strong>' . $rows['amountpercent'] . '%</strong> {_of_the_closing_value}';
								}
								else
								{
									$amount = '<strong>' . $rows['amountpercent'] . '%</strong> {_of_the_remaining_balance_plus_tier_above}';
								}
							}
							if ($rows['finalvalue_to'] == '-1')
							{
								$to = '{_or_more}';
							}
							$htmlfinalvaluefees .= '<tr><td valign="top" nowrap="nowrap" class="alt1">' . $from . ' ' . $to . '</td><td valign="top" class="alt1">' . $amount . '</td></tr>';
							$tier++;
						}
						if ($res['cattype'] == 'service')
						{
							$htmlfinalvaluefees .= '<tr><td><span class="gray">{_no_awarded_provider}</span></td><td><span class="gray"><strong>{_no_fee}</strong></span></td></tr>';
						}
						else
						{
							$htmlfinalvaluefees .= '<tr><td><span class="gray">{_no_winning_bid}</span></td><td><span class="gray"><strong>{_no_fee}</strong></span></td></tr>';
						}
					}
					else
					{
						$show['finalvaluefees'] = 0;
						$htmlfinalvaluefees .= '<tr><td valign="top" colspan="2"><span class="gray">{_no_final_value_fees_within_this_category}</span></td></tr>';
					}
				}
			}
			else
			{
				$show['finalvaluefees'] = 0;
				$htmlfinalvaluefees .= '<tr><td valign="top" colspan="2"><span class="gray">{_no_final_value_fees_within_this_category}</span></td></tr>';
			}
		}
		$listingfees = '<div class="block-wrapper">
<div class="block">
<div class="block-top">
	<div class="block-right">
		<div class="block-left"></div>
	</div>
</div>
<div class="block-header">{_final_value_fees}</div>
<div class="block-content" style="padding:0px">
<table border="0" width="100%" cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" dir="' . $ilconfig['template_textdirection'] . '">';
		if ($res['cattype'] == 'service')
		{
			$listingfees .= '<tr>
	<td valign="top" class="alt2"><strong>{_awarded_price}</strong></td>
	<td valign="top" class="alt2"><strong>{_final_value_fee}</strong></td>
</tr>
' . $htmlfinalvaluefees;
		}
		else
		{
			$listingfees .= '<tr>
	<td valign="top" class="alt2"><strong>{_closing_price}</strong></td>
	<td valign="top" class="alt2"><strong>{_final_value_fee}</strong></td>
</tr>
' . $htmlfinalvaluefees;
		}
	$listingfees .= '</table>
	</div>
	<div class="block-footer">
		<div class="block-right">
			<div class="block-left"></div>
		</div>
	</div>
	</div>
	</div>';
		$ilance->template->templateregistry['finalvaluefees'] = $listingfees;
		echo $ilance->template->parse_template_phrases('listingfees') . '|' . $ilance->template->parse_template_phrases('finalvaluefees');
		exit();
	}
	// #### ADMINCP SEARCH CONFIGURATION ###########################################
	else if ($ilance->GPC['do'] == 'search_configuration_variable')
	{
		if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
		{
			$slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';
			$var = isset($ilance->GPC['var']) ? $ilance->db->escape_string($ilance->GPC['var']) : '';
			if (!empty($var) AND strlen($var) > 2 AND $var != 'x')
			{
				$sql = $ilance->db->query("
					SELECT c.name, c.configgroup
					FROM " . DB_PREFIX . "configuration c
					LEFT JOIN " . DB_PREFIX . "language_phrases l ON (c.name = substr(l.varname, 2, (length(l.varname)-6)))
					WHERE l.phrasegroup = 'admincp_configuration'
						AND (l.text_$slng LIKE '%$var%' OR c.name LIKE '%$var%')
				");
				if ($ilance->db->num_rows($sql) > 0)
				{
					$show['search_results'] = true;
					$configgroup = $varname = $results = '';
					while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
					{
						if (!isset($varname[$res['configgroup']]))
						{
							$varname[$res['configgroup']] = "'" . $res['name'] . "'";
						}
						else
						{
							$varname[$res['configgroup']] .= empty($varname[$res['configgroup']]) ? "'" . $res['name'] . "'" : ", '" . $res['name'] . "'";
						}
					}
					if (is_array($varname))
					{
						foreach ($varname AS $key => $value)
						{
							$results .= $ilance->admincp->construct_admin_input($key, $ilpage['dashboard'], $value);
						}
						$ilance->template->templateregistry['results'] = $results;
						$ilance->template->parse_template_collapsables('results');
						echo $ilance->template->parse_template_phrases('results');
						exit();
					}
				}
			}
		}
		echo '';
		exit();
	}
	// #### ADMINCP HERO PICTURE INFO ##############################################
	else if ($ilance->GPC['do'] == 'heropicture')
	{
		if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
		{
			$filename = isset($ilance->GPC['filename']) ? $ilance->GPC['filename'] : '';
			$mode = isset($ilance->GPC['mode']) ? $ilance->GPC['mode'] : '';
			if (!empty($filename))
			{
				if ($mode == 'load')
				{
					$sql = $ilance->db->query("
						SELECT imagemap, sort
						FROM " . DB_PREFIX . "hero
						WHERE filename = '" . $ilance->db->escape_string($filename) . "'
						LIMIT 1
					");
					if ($ilance->db->num_rows($sql) > 0)
					{
						$res = $ilance->db->fetch_array($sql, DB_ASSOC);
						echo "$res[sort]|$res[imagemap]";
						exit();
					}
				}
				else if ($mode == 'insert')
				{
					$sql = $ilance->db->query("
						SELECT sort
						FROM " . DB_PREFIX . "hero
						ORDER BY sort DESC
						LIMIT 1
					");
					if ($ilance->db->num_rows($sql) > 0)
					{
						$res = $ilance->db->fetch_array($sql, DB_ASSOC);
						echo  "$res[sort]|";
						exit();
					}
					else
					{
						echo "10|";
						exit();
					}
				}
			}
		}
		echo '';
		exit();
	}
	// #### FILEUPLOADER ###########################################################
	else if ($ilance->GPC['do'] == 'fileuploader')
	{
		if (isset($_SESSION['ilancedata']['user']['userid']) AND !empty($_SESSION['ilancedata']['user']['userid']))
		{
			if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'remove' AND isset($ilance->GPC['aid']) AND $ilance->GPC['aid'] > 0)
			{
				$ilance->fileuploaderhandler->delete();
			}
			else
			{
				$ilance->fileuploaderhandler->attachtype = (isset($ilance->GPC['attachtype']) AND in_array($ilance->GPC['attachtype'], array('itemphoto', 'slideshow', 'project', 'storesitemphoto'))) ? $ilance->GPC['attachtype'] : 'slideshow';
				$ilance->fileuploaderhandler->init();
			}
		}
		exit();
	}
	// #### FILEUPLOADER FORM #######################################################
	else if ($ilance->GPC['do'] == 'fileuploaderform')
	{
		if (isset($_SESSION['ilancedata']['user']['userid']) AND !empty($_SESSION['ilancedata']['user']['userid']))
		{
			$_SESSION['ilancedata']['tmp']['newitemid'] = isset($_SESSION['ilancedata']['tmp']['newitemid']) ? $_SESSION['ilancedata']['tmp']['newitemid'] : 0 ;
			$ilance->GPC['pid'] = isset($ilance->GPC['pid']) ? $ilance->GPC['pid'] : 0;
			$pid = ($ilance->GPC['pid'] == '1') ? $_SESSION['ilancedata']['tmp']['newitemid'] : $ilance->GPC['pid'] ;
			$attachtype = isset($ilance->GPC['attachtype']) ? $ilance->GPC['attachtype'] : 'slideshow';
			$maximum_files = $attach_usage_total = $attach_user_max = $max_size = $attach_usage_left = $max_width = $max_height = $extensions = '-';
			$slideshowcost = '';
			if ($attachtype == 'itemphoto' OR $attachtype == 'slideshow' OR $attachtype == 'project' OR $attachtype == 'storesitemphoto')
			{
				$res_file_sum['attach_usage_total'] = 0;
				$sql_file_sum = $ilance->db->query("
					SELECT SUM(filesize) AS attach_usage_total
					FROM " . DB_PREFIX . "attachment
					WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				");
				if ($ilance->db->num_rows($sql_file_sum) > 0)
				{
					$res_file_sum = $ilance->db->fetch_array($sql_file_sum, DB_ASSOC);
					$attach_usage_total = print_filesize($res_file_sum['attach_usage_total']);
				}
				$attach_user_max = $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'attachlimit');
				$attach_usage_left = ($attach_user_max - $res_file_sum['attach_usage_total']);
				$attach_usage_left = ($attach_usage_left <= 0) ? print_filesize(0) : print_filesize($attach_usage_left);
				$condition = $ilance->attachment->handle_attachtype_rebuild_settings($attachtype, $_SESSION['ilancedata']['user']['userid'], $pid, '', '');
				$attach_user_max = print_filesize($attach_user_max);
				$maximum_files = $condition['maximum_files'];
				$max_width = $condition['max_width'];
				$max_height = $condition['max_height'];
				$max_filesize = $condition['max_filesize'];
				$max_size = $condition['max_size'];
				$extensions = $condition['extensions'];
				if ($attachtype == 'itemphoto' OR $attachtype == 'slideshow')
				{
					$slideshowcost = ($ilconfig['productupsell_slideshowcost'] > 0) ? $ilance->currency->format($ilconfig['productupsell_slideshowcost']) : '{_free_lower}';
					$slideshowcost = '<div class="smaller">{_each_slideshow_picture_is} ' . $slideshowcost . '</div>';
				}
			}
			$ilance->template->load_popup('fileuploader', 'jqueryfileupload.html');
			$ilance->template->init_js_phrase_array('fileuploader');
			$vars = array(
				'pid' => $pid,
				'attachtype' => $attachtype,
				'jspath' => DIR_FUNCT_NAME . '/' . DIR_JS_NAME . '/fileuploader/',
				'csspath' => DIR_FUNCT_NAME . '/' . DIR_CSS_NAME . '/fileuploader/',
				'phrases' => DIR_TMP_NAME . '/' . DIR_JS_NAME . '/' . $ilance->template->js_phrases_file,
				'maximum_files' => $maximum_files,
				'attach_usage_total' => $attach_usage_total,
				'attach_user_max' => $attach_user_max,
				'max_size' => $max_size,
				'attach_usage_left' => $attach_usage_left,
				'max_width' => $max_width,
				'max_height' => $max_height,
				'extensions' => $extensions,
				'slideshowcost' => $slideshowcost
			);
			$ilance->template->parse_hash('fileuploader', array('vars' => $vars, 'ilpage' => $ilpage));
			echo $ilance->template->parse_template_phrases('fileuploader');
		}
		exit();
	}
	else if ($ilance->GPC['do'] == 'recentlyvieweditems')
	{
		$show['type'] = isset($ilance->GPC['type']) ? $ilance->GPC['type'] : 'load';
		$show['norecentitems'] = false;
		$returnurl = isset($ilance->GPC['returnurl']) ? urlencode($ilance->GPC['returnurl']) : urlencode(HTTP_SERVER);
		$columns = isset($ilance->GPC['columns']) ? intval($ilance->GPC['columns']) : 3;
		$recentlyviewedtopitems = array();
		if (($recentlyviewedtopitems = $ilance->cache->fetch('recentreviewedproductauctions_col_' . $columns)) === false)
		{
			$recentlyviewedtopitems = $ilance->auction_listing->fetch_recently_viewed_auctions('product', 12, 1, 0, '', true);
			$ilance->cache->store('recentreviewedproductauctions_col_' . $columns, $recentlyviewedtopitems);
		}
		if (count($recentlyviewedtopitems) <= 0)
		{
			$show['norecentitems'] = true;
		}
		$ilance->template->load_popup('main', 'inline_recentlyvieweditems_' . $columns . 'col.html');
		$ilance->template->parse_loop('main', 'recentlyviewedtopitems');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->draw('main', array('https_server', 'http_server', 'returnurl'));
		exit();
	}
	else if ($ilance->GPC['do'] == 'favourites')
	{
		$html1 = $html2 = $html3 = '';
		$ilance->GPC['limit'] = isset($ilance->GPC['limit']) ? intval($ilance->GPC['limit']) : 10;
		$show['noresults'] = true;
		$favouriteitems = array();
		if (($favouriteitems = $ilance->cache->fetch('favouriteitems')) === false)
		{
			$favouriteitems = $ilance->watchlist->fetch_watching_items($ilance->GPC['limit']);
			$ilance->cache->store('favouriteitems', $favouriteitems);
		}
		if (is_array($favouriteitems) AND count($favouriteitems) > 0)
		{
			$show['noresults'] = false;
		}
		$ilance->template->load_popup('main', 'inline_favouriteitems.html');
		$ilance->template->parse_loop('main', 'favouriteitems');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$html1 = $ilance->template->draw('main', array('https_server', 'http_server', 'returnurl'), false);
		$show['noresults'] = true;
		$favouritesellers = array();
		if (($favouritesellers = $ilance->cache->fetch('favouritesellers')) === false)
		{
			$favouritesellers = $ilance->watchlist->fetch_watching_sellers($ilance->GPC['limit']);
			$ilance->cache->store('favouritesellers', $favouritesellers);
		}
		if (is_array($favouritesellers))
		{
			$show['noresults'] = false;
		}
		$ilance->template->load_popup('main', 'inline_favouritesellers.html');
		$ilance->template->parse_loop('main', 'favouritesellers');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$html2 = $ilance->template->draw('main', array('https_server', 'http_server', 'returnurl'), false);
		$show['noresults'] = true;
		$favouritesearches = array();
		if (($favouritesearches = $ilance->cache->fetch('favouritesearches')) === false)
		{
			$favouritesearches = $ilance->watchlist->fetch_favourite_searches($ilance->GPC['limit'], true);
			$ilance->cache->store('favouritesearches', $favouritesearches);
		}
		if (is_array($favouritesearches))
		{
			$show['noresults'] = false;
		}
		$ilance->template->load_popup('main', 'inline_favouritesearches.html');
		$ilance->template->parse_loop('main', 'favouritesearches');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$html3 = $ilance->template->draw('main', array('https_server', 'http_server', 'returnurl'), false);
		echo json_encode(array('favouriteitems' => $html1, 'favouritesellers' => $html2, 'favouritesearches' => $html3));
		unset($html1, $html2, $html3);
		exit();
	}
	else if ($ilance->GPC['do'] == 'categoryquestionspulldown')
	{
		if (isset($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0 AND isset($ilance->GPC['qid']) AND isset($ilance->GPC['cattype']) AND isset($ilance->GPC['fieldname']) AND isset($ilance->GPC['mode']) AND isset($ilance->GPC['counter']))
		{
			$html = '';
			$languages = $ilance->db->query("
				SELECT languagecode, title, languageiso
				FROM " . DB_PREFIX . "language
			", 0, null, __FILE__, __LINE__);
			$lc = $ilance->db->num_rows($languages);
			$lcc = 1;
			while ($language = $ilance->db->fetch_array($languages, DB_ASSOC))
			{
				$language['slng'] = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
				$language['language'] = $language['title'];
				$fieldname = $ilance->GPC['fieldname'];
				if ($lc > 1)
				{
					if ($lcc == 1)
					{
						$html .= '<div style="padding:2px 0 2px 0"><span class="litegray">' . strtoupper($language['languageiso']) . '</span> <input class="input" name="newmultiplechoice[' . $language['slng'] . '][]" value="" id="newmultiplechoice_' . $language['slng'] . '_' . intval($ilance->GPC['counter']) . '" style="width:35%" placeholder="' . $language['language'] . '" title="' . $language['language'] . '" /> <input title="{_display_order}" class="input" name="newmultiplechoiceorder[]" id="newmultiplechoiceorder_' . intval($ilance->GPC['counter']) . '" value="10" style="width:5%" /> <span id="pdmdiv_' . intval($ilance->GPC['counter']) . '">' . $ilance->auction_questions->print_category_question_pulldown_groups($ilance->GPC['cid'], $ilance->GPC['qid'], 0, $ilance->GPC['cattype'], $language['slng'], $ilance->GPC['mode'], $fieldname, $ilance->GPC['counter']) . '</span></div>';
					}
					else
					{
						$html .= '<div style="padding:2px 0 2px 0"><span class="litegray">' . strtoupper($language['languageiso']) . '</span> <input class="input" name="newmultiplechoice[' . $language['slng'] . '][]" value="" id="newmultiplechoice_' . $language['slng'] . '_' . intval($ilance->GPC['counter']) . '" style="width:35%" placeholder="' . $language['language'] . '" title="' . $language['language'] . '" /></div>';
					}
				}
				else
				{
					$html .= '<div style="padding:2px 0 2px 0"><span class="litegray">' . strtoupper($language['languageiso']) . '</span> <input class="input" name="newmultiplechoice[' . $language['slng'] . '][]" value="" id="newmultiplechoice_' . $language['slng'] . '_' . intval($ilance->GPC['counter']) . '" style="width:35%" placeholder="' . $language['language'] . '" title="' . $language['language'] . '" /> <input title="{_display_order}" class="input" name="newmultiplechoiceorder[]" id="newmultiplechoiceorder_' . intval($ilance->GPC['counter']) . '" value="10" style="width:5%" /> <span id="pdmdiv_' . intval($ilance->GPC['counter']) . '">' . $ilance->auction_questions->print_category_question_pulldown_groups($ilance->GPC['cid'], $ilance->GPC['qid'], 0, $ilance->GPC['cattype'], $language['slng'], $ilance->GPC['mode'], $fieldname, $ilance->GPC['counter']) . '</span></div>';	
				}
				$lcc++;
			}
			if (!empty($html))
			{
				$html .= '<div style="height:1px; background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
			}
			$ilance->template->templateregistry['categoryquestionspulldown'] = $html;
			echo $ilance->template->parse_template_phrases('categoryquestionspulldown');
		}
		exit();
	}
	else if ($ilance->GPC['do'] == 'searchresult')
	{
		if (isset($ilance->GPC['itemid']) AND $ilance->GPC['itemid'] > 0)
		{
			$title = fetch_auction('project_title', $ilance->GPC['itemid']);
			$cid = fetch_auction('cid', $ilance->GPC['itemid']);
			$url = construct_seo_url('productauctionplain', 0, $ilance->GPC['itemid'], stripslashes($title), '',  0, '', 0, 0);
			$t['bigphoto'] = ($ilconfig['globalauctionsettings_seourls']) ? $ilance->auction->print_item_photo($url, 'thumbgallery', $ilance->GPC['itemid'], '0', '#ffffff', 0, '', false, 1) : $ilance->auction->print_item_photo($ilpage['merch'] . '?id=' . $ilance->GPC['itemid'], 'thumbgallery', $ilance->GPC['itemid'], '0', '#ffffff', 0, '', false, 1);
			if (($specifics = $ilance->cache->fetch("specifics_" . $ilance->GPC['itemid'] . "_outputmini_cid_" . $cid . "_5")) === false)
			{
				$specifics = $ilance->auction_questions->construct_auction_questions($cid, $ilance->GPC['itemid'], 'outputmini', 'product', 0, false, 5);
				$ilance->cache->store("specifics_" . $ilance->GPC['itemid'] . "_outputmini_cid_" . $cid . "_5", $specifics);
			}
			$t['specifics'] = $specifics;
			unset($title, $url, $specifics, $cid);
			echo "$t[bigphoto]|{_price}|<strong>$0.00</strong>|$t[specifics]|timeleft|0";
		}
		exit();
	}
	else if ($ilance->GPC['do'] == 'build')
	{
		echo '8059';
		exit();
	}
}

($apihook = $ilance->api('ajax_end')) ? eval($apihook) : false;
    
/*=======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>