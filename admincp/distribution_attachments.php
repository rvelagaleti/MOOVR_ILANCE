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
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'attachment-manage-storagetype' AND isset($ilance->GPC['action']))
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$area_title = '{_managing_attachment_storage_type}';
	$page_title  = SITE_NAME . ' - {_managing_attachment_storage_type}';
	if ($ilance->GPC['action'] == 'movetofilepath')
	{
		$notice = $ilance->attachment_tools->move_attachments_to_filepath();
		if (!empty($notice))
		{
			print_action_success('{_the_following_attachments_within_the_database_were_moved_to_the_file_system}<br /><br />' . $notice, $ilance->GPC['return']);
			exit();
		}
		else
		{
			print_action_failed('{_there_was_an_error_no_attachments_were_found_in_the_database_to_move}', $ilance->GPC['return']);
			exit();
		}
	}
	else if ($ilance->GPC['action'] == 'movetodatabase')
	{
		$notice = $ilance->attachment_tools->move_attachments_to_database(true);
		if (!empty($notice))
		{
			print_action_success('{_the_following_attachments_were_moved_into_the_database}<br /><br />' . $notice, $ilance->GPC['return']);
			exit();
		}
		else
		{
			print_action_failed('{_there_was_an_error_no_attachments_were_found_in_the_filesystem}', $ilance->GPC['return']);
			exit();
		}
	}
	else if ($ilance->GPC['action'] == 'rebuildpictures')
	{
		$ilance->auction_pictures_rebuilder->process_picture_rebuilder();
		print_action_success('{_pictures_within_attachment_system_rebuilt}', $ilance->GPC['return']);
		exit();
	}
}
// #### MANAGING ATTACHMENTS ###########################################
else if (isset($ilance->GPC['subcmd']) AND ($ilance->GPC['subcmd'] == 'attachment-manage' OR ($ilance->GPC['subcmd'] == 'attachment-moderate-manage' AND !isset($ilance->GPC['verify']))))
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$area_title = '{_managing_attachments}';
	$page_title = SITE_NAME . ' - {_managing_attachments}';
	if (isset($ilance->GPC['attachid']) AND is_array($ilance->GPC['attachid']))
	{
		if (isset($ilance->GPC['delete']))
		{
			foreach ($ilance->GPC['attachid'] AS $value)
			{
				if ($value > 0)
				{
					$ilance->attachment->remove_attachment(intval($value));
				}
			}
			log_event($_SESSION['ilancedata']['user']['userid'], $ilpage['distribution'], $ilance->GPC['cmd'], $ilance->GPC['subcmd'], $ilance->GPC['delete']);
			print_action_success('{_selected_attachments_have_been_removed_from_the_marketplace}', urldecode($ilance->GPC['return']));
			exit();
		}
		else if (isset($ilance->GPC['rebuild']))
		{
			foreach ($ilance->GPC['attachid'] AS $value)
			{
				if ($value > 0)
				{
					$ilance->auction_pictures_rebuilder->process_picture_rebuilder(intval($value));
				}
			}
			log_event($_SESSION['ilancedata']['user']['userid'], $ilpage['distribution'], $ilance->GPC['cmd'], $ilance->GPC['subcmd'], $ilance->GPC['rebuild']);
			print_action_success('{_selected_pictures_within_attachment_system_rebuilt}', urldecode($ilance->GPC['return']));
			exit();
		}
	}
	else
	{
		print_action_failed('{_there_was_an_error_no_attachments_have_been_selected_please_retry_your_actions}', $ilance->GPC['return']);
		exit();
	}
}
// #### MODERATING ATTACHMENTS #########################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'attachment-moderate-manage' AND isset($ilance->GPC['verify']))
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$area_title = '{_moderating_attachments}';
	$page_title = SITE_NAME . ' - {_moderating_attachments}';
	if (isset($ilance->GPC['attachid']) AND !empty($ilance->GPC['attachid']))
	{
		foreach ($ilance->GPC['attachid'] AS $value)
		{
			if (!empty($value))
			{
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "attachment
					SET visible = '1'
					WHERE attachid = '" . intval($value) . "'
				", 0, null, __FILE__, __LINE__);
			}
		}
		print_action_success('{_selected_attachments_have_been_moderated_and_verified_to_the_public_marketplace}', $ilance->GPC['return']);
		exit();
	}
	else
	{
		print_action_failed('{_there_was_an_error_no_attachments_have_been_selected_for_moderation}', $ilance->GPC['return']);
		exit();
	}
}
// #### ATTACHMENT MANAGER AREA ########################################
else
{
	$area_title = '{_attachments_manager}';
	$page_title = SITE_NAME . ' - {_attachments_manager}';
	
	($apihook = $ilance->api('admincp_attachment_management')) ? eval($apihook) : false;
	
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'] . '?cmd=attachments', $_SESSION['ilancedata']['user']['slng']);
	$maxrowsdisplay = (isset($ilance->GPC['pp']) AND is_numeric($ilance->GPC['pp'])) ? intval($ilance->GPC['pp']) : $ilconfig['globalfilters_maxrowsdisplay'];
	$filtervalue = '';
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'search')
	{
		if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
		{
			$ilance->GPC['page'] = 1;
		}
		else
		{
			$ilance->GPC['page'] = intval($ilance->GPC['page']);
		}
		if (isset($ilance->GPC['filtervalue']) AND !empty($ilance->GPC['filtervalue']))
		{
			$filtervalue = handle_input_keywords($ilance->GPC['filtervalue']);
		}
		$limit = ' ORDER BY a.attachid ' . $ilance->db->escape_string($ilance->GPC['orderby']) . ' LIMIT ' . (($ilance->GPC['page'] - 1) * $maxrowsdisplay) . ',' . $maxrowsdisplay;
	}
	else
	{
		if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
		{
			$ilance->GPC['page'] = 1;
		}
		else
		{
			$ilance->GPC['page'] = intval($ilance->GPC['page']);
		}
		if (isset($ilance->GPC['filtervalue']) AND !empty($ilance->GPC['filtervalue']))
		{
			$filtervalue = handle_input_keywords($ilance->GPC['filtervalue']);
		}
		$limit = ' ORDER BY a.attachid DESC LIMIT ' . (($ilance->GPC['page'] - 1) * $maxrowsdisplay) . ',' . $maxrowsdisplay;
	}
	$filtersql = '';
	if (isset($ilance->GPC['filterby']) AND !empty($ilance->GPC['filterby']))
	{
		if ($ilance->GPC['filterby'] == 'user_id')
		{
			$nameuserid = $ilance->db->fetch_field(DB_PREFIX . "users", "username = '" . $ilance->db->escape_string($ilance->GPC['filtervalue']) . "'", "user_id");
			if ($nameuserid > 0)
			{
				$filtersql = " AND a.user_id = '" . $nameuserid . "'";
			}
			else
			{
				$filtersql = " AND a.user_id = '" . $ilance->db->escape_string($ilance->GPC['filtervalue']) . "'";
			}
		}
		else
		{
			$filtersql = " AND a.".$ilance->db->escape_string($ilance->GPC['filterby'])." = '".$ilance->db->escape_string($ilance->GPC['filtervalue'])."'";
		}
	}
	// #### MODERATED ATTACHMENTS ##########################################
	$sql = $ilance->db->query("
		SELECT a.attachid, a.attachtype, a.user_id, a.portfolio_id, a.project_id, a.pmb_id, a.category_id, a.date, a.filename, a.filetype, a.visible, a.counter, a.filesize, a.filehash, a.ipaddress, a.tblfolder_ref
		FROM " . DB_PREFIX . "attachment a
		WHERE a.visible = '0'
		$filtersql
		$limit
	", 0, null, __FILE__, __LINE__);
	$sqltmp = $ilance->db->query("
		SELECT a.attachid, a.attachtype, a.user_id, a.portfolio_id, a.project_id, a.pmb_id, a.category_id, a.date, a.filename, a.filetype, a.visible, a.counter, a.filesize, a.filehash, a.ipaddress, a.tblfolder_ref
		FROM " . DB_PREFIX . "attachment a
		WHERE a.visible = '0'
		$filtersql
	", 0, null, __FILE__, __LINE__);
	$totalcount = $ilance->db->num_rows($sqltmp);
	$counter = ($ilance->GPC['page']-1)*$ilconfig['globalfilters_maxrowsdisplay'];
	if ($ilance->db->num_rows($sql) > 0)
	{
		$show['no_moderateattachments'] = false;
		$row_count = 0;
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$res['subscriber'] = fetch_user('username', $res['user_id']);
			$res['filesize'] = print_filesize($res['filesize']);
			$filename = $res['filename'];
			$res['filename'] = shorten($filename, 23);
			$res['filenamefull'] = $filename;
			$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$moderateattachments[] = $res;
			$row_count++;
		}
	}
	else
	{
		$show['no_moderateattachments'] = true;
	}
	$prevnext = print_pagnation($totalcount, $maxrowsdisplay, $ilance->GPC['page'], $counter, $ilpage['distribution'] . '?cmd=attachments&amp;subcmd=moderate');
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'search')
	{
		if (!isset($ilance->GPC['page2']) OR isset($ilance->GPC['page2']) AND $ilance->GPC['page2'] <= 0)
		{
			$ilance->GPC['page2'] = 1;
		}
		else
		{
			$ilance->GPC['page2'] = intval($ilance->GPC['page2']);
		}
		$limit2 = ' ORDER BY a.attachid ' . $ilance->db->escape_string($ilance->GPC['orderby']) . ' LIMIT ' . (($ilance->GPC['page2'] - 1) * $maxrowsdisplay) . ',' . $maxrowsdisplay;
	}
	else
	{
		if (!isset($ilance->GPC['page2']) OR isset($ilance->GPC['page2']) AND $ilance->GPC['page2'] <= 0)
		{
			$ilance->GPC['page2'] = 1;
		}
		else
		{
			$ilance->GPC['page2'] = intval($ilance->GPC['page2']);
		}
		$limit2 = ' ORDER BY a.attachid DESC LIMIT ' . (($ilance->GPC['page2'] - 1) * $maxrowsdisplay) . ',' . $maxrowsdisplay;
	}
	$filtersql = '';
	if (isset($ilance->GPC['filterby']) AND !empty($ilance->GPC['filterby']))
	{
		if ($ilance->GPC['filterby'] == 'user_id')
		{
			$nameuserid = $ilance->db->fetch_field(DB_PREFIX . "users", "username = '" . $ilance->db->escape_string($ilance->GPC['filtervalue']) . "'", "user_id");
			if ($nameuserid > 0)
			{
				$filtersql = " AND a.user_id = '" . $nameuserid . "'";
			}
			else
			{
				$filtersql = " AND a.user_id = '" . $ilance->db->escape_string($ilance->GPC['filtervalue']) . "'";
			}
		}
		else
		{
			$filtersql = " AND a." . $ilance->db->escape_string($ilance->GPC['filterby']) . " = '" . $ilance->db->escape_string($ilance->GPC['filtervalue']) . "'";
		}
	}
	$optionssql = $leftjoinsql = '';
	if (isset($ilance->GPC['options']) AND !empty($ilance->GPC['options']))
	{
		$leftjoinsql = "LEFT JOIN " . DB_PREFIX . "projects p ON (a.project_id = p.project_id)";
		if ($ilance->GPC['options'] == 'onlyended')
		{
			$optionssql = "AND p.status = 'expired'";
		}
		else if ($ilance->GPC['options'] == 'onlyopen')
		{
			$optionssql = "AND p.status = 'open'";
		}
	}
	// #### MANAGE ATTACHMENTS #############################################
	$sql2 = $ilance->db->query("
		SELECT a.attachid, a.attachtype, a.user_id, a.portfolio_id, a.project_id, a.pmb_id, a.category_id, a.date, a.filename, a.filetype, a.visible, a.counter, a.filesize, a.filesize_original, a.filesize_full, a.filesize_search, a.filesize_gallery, a.filesize_snapshot, a.filesize_mini, a.filehash, a.ipaddress, a.tblfolder_ref, a.width, a.height, a.width_original, a.height_original, a.width_full, a.height_full, a.width_mini, a.height_mini, a.width_search, a.height_search, a.width_gallery, a.height_gallery, a.width_snapshot, a.height_snapshot
		FROM " . DB_PREFIX . "attachment a
		$leftjoinsql
		WHERE a.visible = '1'
		$filtersql
		$optionssql
		$limit2
	", 0, null, __FILE__, __LINE__);
	$sql2tmp = $ilance->db->query("
		SELECT a.attachid, a.attachtype, a.user_id, a.portfolio_id, a.project_id, a.pmb_id, a.category_id, a.date, a.filename, a.filetype, a.visible, a.counter, a.filesize, a.filesize_original, a.filesize_full, a.filesize_search, a.filesize_gallery, a.filesize_snapshot, a.filesize_mini, a.filehash, a.ipaddress, a.tblfolder_ref, a.width, a.height, a.width_original, a.height_original, a.width_full, a.height_full, a.width_mini, a.height_mini, a.width_search, a.height_search, a.width_gallery, a.height_gallery, a.width_snapshot, a.height_snapshot
		FROM " . DB_PREFIX . "attachment a
		$leftjoinsql
		WHERE a.visible = '1'
		$filtersql
		$optionssql
	", 0, null, __FILE__, __LINE__);
	$totalcount2 = $ilance->db->num_rows($sql2tmp);
	$counter2 = ($ilance->GPC['page2'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
	if ($ilance->db->num_rows($sql2) > 0)
	{
		$show['no_attachments'] = false;
		$row_count2 = 0;
		while ($res2 = $ilance->db->fetch_array($sql2, DB_ASSOC))
		{
			$res2['subscriber'] = fetch_user('username', $res2['user_id']);
			$res2['filesize'] = print_filesize($res2['filesize']);
			$res2['class'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
			$url = $ilance->attachment->print_file_extension_icon($res2['filename']);
			$res2['attachextension'] = '<img src="' . $url . '" border="0" alt="" />';
			$res2['date'] = print_date($res2['date']);
			$filename = $res2['filename'];
			$res2['filename'] = shorten($filename, 23);
			$res2['filenamefull'] = $filename;
			$res2['attachtype'] = $ilance->attachment_tools->fetch_attachment_type($res2['attachtype'], $res2['project_id'], $res2['attachid']);
			$res2['counter'] = number_format($res2['counter']);
			$res2['sizes'] = '<div style="padding-top:6px">' . $ilance->attachment_tools->fetch_attachment_dimensions($res2) . '</div>';
			$attachments[] = $res2;
			$row_count2++;
		}
	}
	else
	{
		$show['no_attachments'] = true;
	}
	if (empty($ilance->GPC['filterby']))
	{
		$ilance->GPC['filterby'] = '';
	}
	if (empty($ilance->GPC['filtervalue']))
	{
		$ilance->GPC['filtervalue'] = '';
	}
	if (empty($ilance->GPC['orderby']))
	{
		$ilance->GPC['orderby'] = '';
	}
	$prevnext2 = print_pagnation($totalcount2, $maxrowsdisplay, $ilance->GPC['page2'], $counter2, $ilpage['distribution'] . '?cmd=attachments&amp;filterby='.$ilance->GPC['filterby'].'&amp;filtervalue='.$ilance->GPC['filtervalue'].'&amp;orderby='.$ilance->GPC['orderby'], 'page2');
	$totalattachments = number_format($ilance->attachment->totalattachments());
	$totaldiskspace = $ilance->attachment->totaldiskspace();
	$totaldownloads = number_format($ilance->attachment->totaldownloads());
	$storagetype = $ilance->attachment->storagetype('type');
	$storagetypeaction = $ilance->attachment->storagetype('formaction');
	$configuration_attachmentsettings = $ilance->admincp->construct_admin_input('attachmentsystem', $ilpage['distribution'] . '?cmd=attachments');
	$configuration_attachmentmoderation = $ilance->admincp->construct_admin_input('attachmentmoderation', $ilpage['distribution'] . '?cmd=attachments');
	$configuration_attachmentlimits = $ilance->admincp->construct_admin_input('attachmentlimit', $ilpage['distribution'] . '?cmd=attachments');
	$pp = (isset($ilance->GPC['pp']) AND !empty($ilance->GPC['pp'])) ? $ilance->GPC['pp'] : $maxrowsdisplay;
	$perpage_array = array ('5' => '5', '10' => '10', '15' => '15', '25' => '25', '50' => '50', '75' => '75', '100' => '100', '125' => '125', '150' => '150', '175' => '175', '200' => '200', '225' => '225', '250' => '250', '500' => '500', '750' => '750', '1000' => '1000', '2000' => '2000', '3000' => '3000', '4000' => '4000', '5000' => '5000', '10000' => '10000');
	$perpage_pulldown = construct_pulldown('pp', 'pp', $perpage_array, $pp, 'style="font-family: verdana"');
	$pprint_array = array('pp','perpage_pulldown','filtervalue','configuration_attachmentsettings','configuration_attachmentmoderation','configuration_attachmentlimits','totalattachments','totaldiskspace','storagetype','totaldownloads','storagetypeaction','prevnext','prevnext2','id');
	    
	($apihook = $ilance->api('admincp_attachments_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'attachments.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
	$ilance->template->parse_loop('main', 'moderateattachments');
	$ilance->template->parse_loop('main', 'attachments');
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