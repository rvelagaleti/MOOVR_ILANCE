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

// #### define top header nav ##################################################
$topnavlink = array(
        'messages'
);

// #### setup script location ##################################################
define('LOCATION', 'messages');

// #### require backend ########################################################
require_once('./functions/config.php');
require_once(DIR_CORE . 'functions_search.php');
require_once(DIR_CORE . 'functions_wysiwyg.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array();
$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
$navcrumb["$ilpage[messages]"] = $ilcrumbs["$ilpage[messages]"];
if (empty($_SESSION['ilancedata']['user']['userid']) OR !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] <= 0)
{
	refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['messages'] . print_hidden_fields($string = true, $excluded = array(), $questionmarkfirst = true)));
	exit();
}
if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'pmb-manage' AND isset($ilance->GPC['cmd']))
{
	// #### ARCHIVE PMB ####################################################
	if ($ilance->GPC['cmd'] == 'archive')
	{
		$area_title = '{_pmb_message_archiving}';
		$page_title = SITE_NAME . ' - {_pmb_message_archiving}';
		if (isset($ilance->GPC['event_id']) AND !empty($ilance->GPC['event_id']))
		{
			if (!isset($ilance->GPC['folder']))
			{
				foreach ($ilance->GPC['event_id'] as $value)
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "pmb_alerts
						SET to_status = 'archived'
						WHERE event_id = '" . $ilance->db->escape_string($value) . "'
							AND to_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					");
				}
			}
			else 
			{
				foreach ($ilance->GPC['event_id'] as $value)
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "pmb_alerts
						SET from_status = 'archived'
						WHERE event_id = '" . $ilance->db->escape_string($value) . "'
							AND from_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					");
				}					
			}
			
			print_notice('{_private_message_board_archived}', '{_you_have_successfully_archived_one_or_more_private_message_boards}'. "<br /><br />" .'{_please_contact_customer_support}', $ilpage['messages'], '{_private_message_board_menu}');
			exit();
		}
		else
		{
			print_notice('{_invalid_pmb_event_id_selected}', '{_your_requested_action_cannot_be_completed}'. "<br /><br />" .'{_in_order_to_manage_private_messages}'. "<br /><br />" .'{_please_contact_customer_support}', $ilpage['messages'], '{_private_messages_menu}');
			exit();
		}	
	}
	
	// #### DOWNLOAD PMB IN TEXT FORMAT ####################################
	else if ($ilance->GPC['cmd'] == 'txt')
	{
		$txt = SITE_NAME . " " . HTTP_SERVER . LINEBREAK;
		$txt .= '{_pmb_snapshot_for}'." ".$_SESSION['ilancedata']['user']['username']." ".print_date(DATETIME24H, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0) . LINEBREAK . LINEBREAK;

		foreach ($ilance->GPC['event_id'] AS $value)
		{
			$sql = "
				SELECT a.id, a.from_id, a.to_id, a.from_status, a.to_status, p.id, p.project_id, p.event_id, p.datetime, p.message
				FROM " . DB_PREFIX . "pmb_alerts as a,
				" . DB_PREFIX . "pmb as p
				WHERE p.event_id = '" . $ilance->db->escape_string($value) . "'
					AND a.id = p.id 
				ORDER BY p.id ASC
			";
			
			$sql_rfp = $ilance->db->query("
				SELECT p.project_title FROM " . DB_PREFIX . "projects as p,
				" . DB_PREFIX . "pmb_alerts as a
				WHERE event_id='" . $ilance->db->escape_string($value) . "'
					AND a.project_id = p.project_id
			");
			$res_rfp = $ilance->db->fetch_array($sql_rfp);
			
			$result = $ilance->db->query($sql);
			
			if ($ilance->db->num_rows($result) > 0)
			{
				$title = !empty($row['project_title']) ? mb_strtoupper(stripslashes($res_rfp['project_title'])) : '{_delisted}';
				$txt .= "================================================================================" . LINEBREAK;
				$txt .= '{_pmb_for_upper}' . " " . $title . LINEBREAK;
				$txt .= "================================================================================" . LINEBREAK . LINEBREAK;
				
				while ($pmb = $ilance->db->fetch_array($result))
				{
					$pmb['message'] = str_replace("\n", "\r\n", str_replace("\r\n", "\n", $pmb['message']));
					$pmb['message'] = strip_vulgar_words($pmb['message']);
					
					$txt .= "--------------------------------------------------------------------------------" . LINEBREAK;
					$txt .= '{_from}' . ":\t" . fetch_user('username', $pmb['from_id']) . LINEBREAK;
					$txt .= '{_to}' . ":\t" . fetch_user('username', $pmb['to_id']) . LINEBREAK;
					$txt .= '{_date}' . ":\t" . print_date($pmb['datetime'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0) . LINEBREAK;
					$txt .= "ID:\t" . $pmb['id'] . LINEBREAK;
					$txt .= "--------------------------------------------------------------------------------" . LINEBREAK;
					$txt .= $pmb['message'] . LINEBREAK . LINEBREAK;
				}
			}
		}
		$ilance->common->download_file($txt, "pmb-txt-" . $_SESSION['ilancedata']['user']['username'] . ".txt", "text/plain");	
	}
	
	// #### DOWNLOAD PMB IN CSV FORMAT #####################################
	else if ($ilance->GPC['cmd'] == 'csv')
	{
		$csv = '{_title}' . "," . '{_date}' . "," . '{_from}' . "," . '{_to}' . ",PMBID," . '{_message}' . LINEBREAK;
		foreach ($ilance->GPC['event_id'] as $value)
		{
			$sql = "
				SELECT a.id, a.from_id, a.to_id, a.from_status, a.to_status, p.id, p.project_id, p.event_id, p.datetime, p.message
				FROM " . DB_PREFIX . "pmb_alerts as a,
				" . DB_PREFIX . "pmb as p
				WHERE p.event_id = '" . $ilance->db->escape_string($value) . "'
					AND a.id = p.id 
				ORDER BY p.id ASC
			";
			
			$sql_rfp = $ilance->db->query("
				SELECT p.project_title
				FROM " . DB_PREFIX . "projects AS p,
				" . DB_PREFIX . "pmb_alerts as a
				WHERE event_id = '" . $ilance->db->escape_string($value) . "'
					AND a.project_id = p.project_id
			");
			$res_rfp = $ilance->db->fetch_array($sql_rfp);
			$result = $ilance->db->query($sql);
			if ($ilance->db->num_rows($result) > 0)
			{
				$msg['project_title'] = !empty($msg['project_title']) ? mb_strtoupper(stripslashes($res_rfp['project_title'])) : '{_delisted}';
				while ($pmb = $ilance->db->fetch_array($result))
				{
					$msg['datetime'] = print_date($pmb['datetime'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
					$msg['from_id'] = fetch_user('username', $pmb['from_id']);
					$msg['to_id'] = fetch_user('username', $pmb['to_id']);
					$msg['id'] = $pmb['id'];
					$msg['message'] = str_replace("\n", "\r\n", str_replace("\r\n", "\n", $pmb['message']));
					$msg['message'] = strip_vulgar_words($pmb['message']);
					
					foreach ($msg as $key => $val)
					{
						if (preg_match('/\,|"/siU', $val))
						{
							$msg["$key"] = '"' . str_replace('"', '""', $val) . '"';
						}
					}
					
					$csv .= implode(',', $msg) . LINEBREAK;
				}
			}
		}
		
		$ilance->common->download_file($csv, "pmb-csv-" . $_SESSION['ilancedata']['user']['username'] . ".csv", "text/x-csv");	
	}
	
	// #### DELETE PRIVATE MESSAGES ########################################
	else if ($ilance->GPC['cmd'] == 'delete')
	{
		$area_title = '{_deleting_private_messages}';
		$page_title = SITE_NAME . ' - {_deleting_private_messages}';

		if (isset($ilance->GPC['event_id']) AND $ilance->GPC['event_id'] != "")
		{
			foreach ($ilance->GPC['event_id'] as $value)
			{
				$query = $ilance->db->query("
					UPDATE " . DB_PREFIX . "pmb_alerts
					SET to_status = 'deleted'
					WHERE event_id = '" . intval($value) . "'
						AND to_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				");				  
				$query = $ilance->db->query("
					UPDATE " . DB_PREFIX . "pmb_alerts
					SET from_status = 'deleted'
					WHERE event_id = '" . intval($value) . "'
						AND from_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				");
			}
			print_notice('{_private_message_board_removed}', '{_you_have_successfully_removed}' . "<br /><br />" . '{_a_good_rule_of_thumb_would_be}' . "<br /><br />" . '{_please_contact_customer_support}', $ilpage['messages'], '{_private_messages_menu}');
			exit();
		}
		else
		{
			print_notice('{_invalid_pmb_event_id_selected}', '{_your_requested_action_cannot_be_completed}' . "<br /><br />" . '{_in_order_to_manage_private_messages}' . "<br /><br />" . '{_please_contact_customer_support}', $ilpage['messages'], '{_private_messages_menu}');
			exit();
		}	
	}
}
else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'alert-manage')
{
	if ($ilance->GPC['cmd'] == 'delete')
	{
		if (isset($ilance->GPC['alert_id']) AND !empty($ilance->GPC['alert_id']))
		{
			$area_title = '{_deleting_private_messages}';
			$page_title = SITE_NAME . ' - {_deleting_private_messages}';
			foreach ($ilance->GPC['alert_id'] AS $value)
			{
				$query = $ilance->db->query("
					DELETE FROM " . DB_PREFIX . "emaillog
					WHERE emaillogid = '" . $ilance->db->escape_string($value) . "'
						AND logtype = 'alert'
						AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				");
			}
			refresh(HTTPS_SERVER . $ilpage['messages'] . '?pmbfolder=' . $ilance->GPC['pmbfolder'] . '&note=removed');
			exit();
		}
	}
	else if ($ilance->GPC['cmd'] == 'deleteall')
	{
		$area_title = '{_deleting_private_messages}';
		$page_title = SITE_NAME . ' - {_deleting_private_messages}';
		$query = $ilance->db->query("
			DELETE FROM " . DB_PREFIX . "emaillog
			WHERE logtype = 'alert'
				AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		");
		refresh(HTTPS_SERVER . $ilpage['messages'] . '?pmbfolder=' . $ilance->GPC['pmbfolder'] . '&note=removed');
		exit();
	}
	// #### DOWNLOAD ALERTS IN TEXT FORMAT #################################
	else if ($ilance->GPC['cmd'] == 'txt')
	{
		$txt = SITE_NAME . " " . HTTP_SERVER . LINEBREAK;
		$txt .= 'Alerts and Notifications for ' . $_SESSION['ilancedata']['user']['username'] . ' ' . print_date(DATETIME24H, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0) . LINEBREAK . LINEBREAK;
		$result = $ilance->db->query("
			SELECT emaillogid, logtype, user_id, project_id, email, subject, body, date, sent
			FROM " . DB_PREFIX . "emaillog
			WHERE logtype = 'alert'
				AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			ORDER BY emaillogid DESC
		");
		if ($ilance->db->num_rows($result) > 0)
		{
			while ($pmb = $ilance->db->fetch_array($result, DB_ASSOC))
			{
				$pmb['body'] = str_replace("\n", "\r\n", str_replace("\r\n", "\n", $pmb['body']));
				$pmb['body'] = strip_vulgar_words($pmb['body']);
				$txt .= "-------------------------------------------------------------------------------------------------------" . LINEBREAK;
				$txt .= '{_subject}' . ":\t" . $pmb['subject'] . LINEBREAK;
				$txt .= '{_from}' . ":\t\t" . SITE_NAME . ' <' . SITE_EMAIL . '>' . LINEBREAK;
				$txt .= '{_to}' . ":\t\t" . fetch_user('email', $pmb['user_id']) . LINEBREAK;
				$txt .= '{_date}' . ":\t\t" . print_date($pmb['date'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0) . LINEBREAK;
				$txt .= "-------------------------------------------------------------------------------------------------------" . LINEBREAK;
				$txt .= $pmb['body'] . LINEBREAK . LINEBREAK;
			}
		}
		$ilance->template->templateregistry['textfile'] = $txt;
		$ilance->common->download_file($ilance->template->parse_template_phrases('textfile'), "alerts-txt-" . $_SESSION['ilancedata']['user']['username'] . ".txt", "text/plain");	
	}
	// #### DOWNLOAD ALERTS IN CSV FORMAT ##################################
	else if ($ilance->GPC['cmd'] == 'csv')
	{
		$csv = '{_subject}, {_from}, {_to}, {_date}, {_message}' . LINEBREAK;
		$result = $ilance->db->query("
			SELECT emaillogid, logtype, user_id, project_id, email, subject, body, date, sent
			FROM " . DB_PREFIX . "emaillog
			WHERE logtype = 'alert'
				AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			ORDER BY emaillogid DESC
		");
		if ($ilance->db->num_rows($result) > 0)
		{
			while ($pmb = $ilance->db->fetch_array($result, DB_ASSOC))
			{
				$msg['subject'] = $pmb['subject'];
				$msg['date'] = print_date($pmb['date'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				$msg['from'] = SITE_NAME . ' <' . SITE_EMAIL . '>';
				$msg['to'] = fetch_user('email', $pmb['user_id']);
				$msg['body'] = str_replace("\n", "\r\n", str_replace("\r\n", "\n", $pmb['body']));
				$msg['body'] = strip_vulgar_words($pmb['body']);
				$csv .= '"' . str_replace('"', '\"', $msg['subject']) . '","' . $msg['from'] . '","' . $msg['to'] . '","' . $msg['date'] . '","' . str_replace('"', '\"', $msg['body']) . '"' . LINEBREAK;
			}
		}
		$ilance->template->templateregistry['textfile'] = $csv;
		$ilance->common->download_file($ilance->template->parse_template_phrases('textfile'), "alerts-csv-" . $_SESSION['ilancedata']['user']['username'] . ".csv", "text/x-csv");	
	}
}
else
{
	if ($ilconfig['globalfilters_cansendpms'] == 0)
	{
		print_notice('{_private_messaging_disabled}', '{_private_messaging_functionality_disabled}', $ilpage['messages'] . '?pmbfolder=inbox', '{_messages}');
		exit();
	}
	if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'pmbcompose') == 'no')
	{
		print_notice('{_no_access_to_send_pm}', '{_it_appears_your_membership_does_not_permit_pm_composing}', $ilpage['messages'] . '?pmbfolder=inbox', '{_messages}');
		exit();
	}
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'compose')
	{
		$area_title = '{_messages}<div class="smaller">{_compose}</div>';
		$page_title = SITE_NAME . ' - {_messages}';
		$navcrumb[""] = '{_compose}';
		$js_start = '<script type="text/javascript">
<!--
function validate_username(f)
{
	if (window.document.ilform.username.value == \'\')
	{
		alert_js(phrase[\'_please_enter_a_username_to_dispatch_this_private_message\']);
		return(false);
	}
	if (window.document.ilform.username.value == \'' . $_SESSION['ilancedata']['user']['username'] . '\')
	{
		alert_js(phrase[\'_please_enter_a_username_other_than_yourself_to_dispatch_this_private_message\']);
		return(false);
	}
	return (true);
}

function validate_subject(f)
{
	if (window.document.ilform.subject.value == \'\')
	{
		alert_js(phrase[\'_please_enter_the_subject_to_dispatch_this_private_message\']);
		return(false);
	}
	return (true);
}

function validate_message()
{
	fetch_bbeditor_data();
	return(true);
}

function validate_all()
{	
	return validate_username() && validate_subject() && validate_message();
}
//-->
</script>
';
		$username = isset($ilance->GPC['username']) ? $ilance->GPC['username'] : '';
		$subject = isset($ilance->GPC['subject']) ? handle_input_keywords($ilance->GPC['subject']) : '';
		$project_id = isset($ilance->GPC['project_id']) ? intval($ilance->GPC['project_id']) : '';
		$show['subject'] = (empty($subject)) ? 0 : 1;
		$show['preview'] = $show['errorusername'] = $show['subjecterror'] = $show['errorprojectid'] = 0;
		// #### PREVIEW MESSAGE ########################################
		if (isset($ilance->GPC['preview']) AND !empty($ilance->GPC['preview']))
		{
			$show['preview'] = $show['errorusername'] = $show['errorprojectid'] = 1;
			$project_id = isset($ilance->GPC['project_id']) ? $ilance->GPC['project_id'] : 0;
			$message = (!empty($ilance->GPC['message'])) ? $ilance->GPC['message'] : '';
			$descriptionpv = ($ilconfig['default_pmb_wysiwyg'] == 'ckeditor') ? $message : $ilance->bbcode->bbcode_to_html($message);
			$wysiwyg_area = print_wysiwyg_editor('message', $message, 'bbeditor', $ilconfig['globalfilters_pmbwysiwyg'], $ilconfig['globalfilters_pmbwysiwyg'], false, '590', '120', '', $ilconfig['default_pmb_wysiwyg'], $ilconfig['ckeditor_pmbtoolbar']);		
			$subjectpv = handle_input_keywords($subject);
			if (fetch_userid($ilance->GPC['username']) > 0)
			{
				$show['errorusername'] = 0;
			}
			else
			{
				$username = '';
			}
			if ($project_id > 0)
			{
				if ($ilance->auction->auction_exists($project_id))
				{
					$show['errorprojectid'] = 0;
				}
				else
				{
					$project_id = '';
				}
			}
			else
			{
				$show['errorprojectid'] = 0;
			}				
		}
		// #### SUBMIT MESSAGE #########################################
		else if (isset($ilance->GPC['submit']) AND !empty($ilance->GPC['submit']))
		{
			$show['preview'] = 0;
			$show['errorusername'] = $show['errorprojectid'] = 1;
			$project_id = isset($ilance->GPC['project_id']) ? $ilance->GPC['project_id'] : '0';
			$userid = fetch_userid($ilance->GPC['username']);
			if ($userid > 0)
			{
				$show['errorusername'] = 0;
			}
			else
			{
				$username = '';
			}
			if (isset($project_id) AND $project_id > 0)
			{
				if ($ilance->auction->auction_exists($project_id))
				{
					$show['errorprojectid'] = 0;
				}
			}
			else
			{
				$show['errorprojectid'] = 0;
			}
			if ($show['errorusername'] == 0 AND $show['errorprojectid'] == 0)
			{
				// compose and send email for new private message!
				$message = (!empty($ilance->GPC['message'])) ? $ilance->GPC['message'] : '';
				$ilance->pmb->compose_private_message($userid, intval($ilance->GPC['from_id']), $subject, $message, $project_id);
				refresh(HTTPS_SERVER . $ilpage['messages'] . '?cmd=management&pmbfolder=sent&sent=1');
				exit();
			}
			else
			{
				$show['preview'] = $show['errorusername'] = 1;
				$message = (!empty($ilance->GPC['message'])) ? $ilance->GPC['message'] : '';
				$descriptionpv = $ilance->bbcode->bbcode_to_html($message);
				$wysiwyg_area = print_wysiwyg_editor('message', $message, 'bbeditor', $ilconfig['globalfilters_pmbwysiwyg'], $ilconfig['globalfilters_pmbwysiwyg'], false, '590', '120', '', $ilconfig['default_pmb_wysiwyg'], $ilconfig['ckeditor_pmbtoolbar']);					
				$subjectpv = handle_input_keywords($subject);
			}
		}
		else
		{
			$wysiwyg_area = print_wysiwyg_editor('message', '', 'bbeditor', $ilconfig['globalfilters_pmbwysiwyg'], $ilconfig['globalfilters_pmbwysiwyg'], false, '590', '120', '', $ilconfig['default_pmb_wysiwyg'], $ilconfig['ckeditor_pmbtoolbar']);		
		}
		$pprint_array = array('js_start','project_id','username','subject','message','descriptionpv','subjectpv','wysiwyg_area','pmbfolders','advchat','pmbadv','alertsadv','chatsadv','advtoday','advyesterday','advarchived','advhistory','site_email','collapseobj_alerthistory','collapseobj_chats','collapseobj_pmbs','collapseobj_alerts','collapseobj_chathistory','collapseobj_pmbstoday','collapseobj_pmbsyesterday','collapseobj_pmbsarchived','count_archived','count_yesterday','count_alerts','count_transcripts','count_today','input_style');
		$ilance->template->fetch('main', 'messages_compose.html');
		$ilance->template->parse_if_blocks('main');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->pprint('main', $pprint_array);
		exit();	
	}
	else if (isset($ilance->GPC['view']) AND $ilance->GPC['view'] == 'message' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$sql = $ilance->db->query("
			SELECT subject, date, body, ishtml
			FROM " . DB_PREFIX . "emaillog
			WHERE emaillogid = '" . intval($ilance->GPC['id']) . "'
			LIMIT 1
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$subject = stripslashes(handle_input_keywords($res['subject']));
			$date = print_date($res['date'], 'M. d, Y', 0, 0);
			if ($res['ishtml'])
			{
				$body = $res['body'];
			}
			else
			{
				$body = autolink($res['body'], '_blank', false);
				$body = strip_vulgar_words($body, false);
				$body  = nl2br($body);
			}
		}
		else
		{
			print_notice('Invalid message', 'It appears this email does not exist or does not belong to you.  Please ensure you clicked on a valid email from your messages area.<br /><br />{_please_contact_customer_support}', $ilpage['messages'] . '?pmbfolder=site', '{_messages}');
			exit();
		}
	}
	$area_title = '{_messages}';
	$page_title = SITE_NAME . ' - {_messages}';
	$navcrumb = array();
	$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
	$navcrumb[""] = '{_messages}';
	$show['widescreen'] = true;
	$ilance->GPC['pmbfolder'] = $pmb['folder'] = isset($ilance->GPC['pmbfolder']) ? $ilance->GPC['pmbfolder'] : 'inbox';
	$allowed = array('inbox','sent','archived','site');
	if (isset($ilance->GPC['pmbfolder']) AND $ilance->GPC['pmbfolder'] != '' AND in_array($ilance->GPC['pmbfolder'], $allowed))
	{
		$pmb['folder'] = trim(mb_strtolower($ilance->GPC['pmbfolder']));
		$pmb_name = $pmb['folder'];
	}
	// pmb gauge
	$pmbgauge = $ilance->pmb->print_pmb_gauge($_SESSION['ilancedata']['user']['userid']);
	$ilance->GPC['page'] = isset($ilance->GPC['page']) ? $ilance->GPC['page'] : '1';
	$ilance->GPC['period'] = isset($ilance->GPC['period']) ? $ilance->GPC['period'] : '-1';
	$limit = "LIMIT " . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . "," . $ilconfig['globalfilters_maxrowsdisplay'];
	$periodsql = fetch_startend_sql($ilance->GPC['period'], 'DATE_SUB', 'p.datetime', '>=');
	switch ($pmb['folder'])
	{
		case 'inbox':
		{
			$pmfoldername = '{_received_inbox}';
			$sql = "SELECT a.id, a.event_id, a.project_id, a.from_id, a.to_id, a.from_status, a.to_status, a.track_status, p.subject, p.message, p.datetime, MAX(p.datetime) AS postdate
			FROM " . DB_PREFIX . "pmb_alerts AS a,
			" . DB_PREFIX . "pmb AS p
			WHERE a.to_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND a.to_status != 'deleted'
				AND a.to_status != 'archived'
				AND a.event_id = p.event_id
			$periodsql
			GROUP BY a.event_id
			ORDER BY postdate DESC, p.id DESC
			$limit";
			
			$sql1 = "SELECT a.id, a.event_id, a.project_id, a.from_id, a.to_id, a.from_status, a.to_status, a.track_status, p.subject, p.message, p.datetime, MAX(p.datetime) AS postdate
			FROM " . DB_PREFIX . "pmb_alerts AS a,
			" . DB_PREFIX . "pmb AS p
			WHERE a.to_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND a.to_status != 'deleted'
				AND a.to_status != 'archived'
				AND a.event_id = p.event_id
			$periodsql
			GROUP BY a.event_id
			ORDER BY postdate DESC, p.id DESC";
			break;
		}
		case 'sent':
		{
			$pmfoldername = '{_sent_outbox}';
			$sql = "SELECT a.id, a.event_id, a.project_id, a.from_id, a.to_id, a.from_status, a.to_status, a.track_status, p.subject, p.message, p.datetime, MAX(p.datetime) AS postdate
			FROM " . DB_PREFIX . "pmb_alerts AS a
			LEFT JOIN " . DB_PREFIX . "pmb AS p ON (a.event_id = p.event_id)
			WHERE a.from_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND a.from_status != 'deleted'
				AND a.from_status != 'archived'
			$periodsql
			GROUP BY a.event_id
			ORDER BY postdate DESC, p.id DESC
			$limit";
			
			$sql1 = "SELECT a.id, a.event_id, a.project_id, a.from_id, a.to_id, a.from_status, a.to_status, a.track_status, p.subject, p.message, p.datetime, MAX(p.datetime) AS postdate
			FROM " . DB_PREFIX . "pmb_alerts AS a
			LEFT JOIN " . DB_PREFIX . "pmb AS p ON (a.event_id = p.event_id)
			WHERE a.from_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND a.from_status != 'deleted'
				AND a.from_status != 'archived'
			$periodsql
			GROUP BY a.event_id
			ORDER BY postdate DESC, p.id DESC";
			break;
		}            
		case 'archived':
		{
			$pmfoldername = '{_archived}';
			$sql = "SELECT a.id, a.event_id, a.project_id, a.from_id, a.to_id, a.from_status, a.to_status, a.track_status, p.subject, p.message, p.datetime, MAX(p.datetime) AS postdate
			FROM
			" . DB_PREFIX . "pmb_alerts AS a,
			" . DB_PREFIX . "pmb AS p
			WHERE
			(
				a.from_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND a.from_status = 'archived'
				OR
				a.to_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND a.to_status = 'archived'
			)
				AND a.event_id = p.event_id
			$periodsql
			GROUP BY a.event_id
			ORDER BY postdate DESC, p.id DESC
			$limit";
			
			$sql1 = "SELECT a.id, a.event_id, a.project_id, a.from_id, a.to_id, a.from_status, a.to_status, a.track_status, p.subject, p.message, p.datetime, MAX(p.datetime) AS postdate
			FROM
			" . DB_PREFIX . "pmb_alerts AS a,
			" . DB_PREFIX . "pmb AS p
			WHERE
			(
				a.from_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND a.from_status = 'archived'
				OR
				a.to_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND a.to_status = 'archived'
			)
				AND a.event_id = p.event_id
			$periodsql
			GROUP BY a.event_id
			ORDER BY postdate DESC, p.id DESC ";
			break;
		}            
		case 'deleted': //admin only
		{
			$pmfoldername = '{_deleted}';
			break;
		}
		case 'site':
		{
			$pmfoldername = '{_from} ' . SITE_NAME;
			$sql = "
				SELECT emaillogid, logtype, user_id, project_id, email, subject, body, date, sent, user_id, ishtml
				FROM " . DB_PREFIX . "emaillog
				WHERE logtype = 'alert'
					AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				" . fetch_startend_sql($ilance->GPC['period'], 'DATE_SUB', 'date', '>=') . "
				ORDER BY emaillogid DESC
				$limit
			";
			$sql1 = "
				SELECT emaillogid, logtype, user_id, project_id, email, subject, body, date, sent, user_id, ishtml
				FROM " . DB_PREFIX . "emaillog
				WHERE logtype = 'alert'
					AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				" . fetch_startend_sql($ilance->GPC['period'], 'DATE_SUB', 'date', '>=') . "
				ORDER BY emaillogid DESC
			";
			break;
		}
	}
	$result = $ilance->db->query($sql);
	$count = @$ilance->db->num_rows($result);
	$result1 = $ilance->db->query($sql1);
	$count1 = @$ilance->db->num_rows($result1);
	$scriptpage = $ilpage['messages'] . '?pmbfolder=' . $pmb['folder'] . '&period=' . $ilance->GPC['period'];
	$prevnext = print_pagnation($count1, $ilconfig['globalfilters_maxrowsdisplay'], intval($ilance->GPC['page']), '1', $scriptpage, '');
	$receivedcount = number_format($ilance->pmb->fetch_pmb_count($_SESSION['ilancedata']['user']['userid'], 'received'));
	$sentcount = number_format($ilance->pmb->fetch_pmb_count($_SESSION['ilancedata']['user']['userid'], 'sent'));
	$archivedcount = number_format($ilance->pmb->fetch_pmb_count($_SESSION['ilancedata']['user']['userid'], 'archived'));
	$siteemailcount = number_format($ilance->pmb->fetch_pmb_count($_SESSION['ilancedata']['user']['userid'], 'siteemail'));
	// #### PRIVATE MESSAGE BOARDS #########################################
	if ($ilance->db->num_rows($result) > 0)
	{
		$altrows = 0;
		$row_count = 0;
		while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
		{
			$altrows++;
			if (floor($altrows/2) == ($altrows/2))
			{
				$row['class'] = 'alt2';
			}
			else
			{
				$row['class'] = 'alt1';
			}
			if ($pmb['folder'] == 'site')
			{
				// last poster info
				$sql_max = $ilance->db->query("
					SELECT date
					FROM " . DB_PREFIX . "emaillog
					WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					ORDER BY emaillogid DESC
				");
				if ($ilance->db->num_rows($sql_max) > 0)
				{
					$res_max = $ilance->db->fetch_array($sql_max, DB_ASSOC);
				}
				$row['subject'] = '<a href="' . HTTPS_SERVER . $ilpage['messages'] . '?view=message&amp;id=' . $row['emaillogid'] . '">' . stripslashes(handle_input_keywords($row['subject'])) . '</a>';
				$row['body'] = autolink($row['body'], '_blank', false);
				$row['body'] = strip_vulgar_words($row['body'], false);
				$row['body'] = nl2br($row['body']);
				$row['date'] = print_date($row['date'], 'M. d, Y', 0, 0);
				$row['action'] = '<input type="checkbox" name="alert_id[]" value="' . $row['emaillogid'] . '" />';
				$row['relatedauction'] = '';
				$row['attach'] = '';
				$row['ishtml'] = ($row['ishtml']) ? 'HTML formatted' : 'plain text';
 			}
			else
			{
				// last posted message within this board info bit
				$res_lastpost = array();
				$sql_lastpost = $ilance->db->query("
					SELECT from_id, to_id
					FROM " . DB_PREFIX . "pmb_alerts
					WHERE (to_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' OR from_id = '" . $_SESSION['ilancedata']['user']['userid'] . "')
						AND (project_id = '" . $row['project_id'] . "' OR project_id <= 0)
						AND event_id = '" . $row['event_id'] . "'
					ORDER BY id DESC
					LIMIT 1
				");
				if ($ilance->db->num_rows($sql_lastpost) > 0)
				{
					$res_lastpost = $ilance->db->fetch_array($sql_lastpost, DB_ASSOC);
				}
				$row['lastpost'] = print_username($res_lastpost['from_id'], 'plain');
				$row['date_posted'] = print_date($row['postdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				$sql_projects = $ilance->db->query("
					SELECT project_id, project_title
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . $row['project_id'] . "'
					ORDER BY project_id DESC
					LIMIT 1
				");
				if ($ilance->db->num_rows($sql_projects) > 0)
				{
					$projects_array = $ilance->db->fetch_array($sql_projects, DB_ASSOC);
					$row['relatedauction'] = '<div class="smaller litegray" style="padding-top:4px">{_listing}: <span class="a_active">' . handle_input_keywords($projects_array['project_title']) . '</span></div>';
				}
				else 
				{
					$projects_array['project_id'] = '0';
					$projects_array['project_title'] = '';
					$row['relatedauction'] = '';
				}
				// check for attachments
				$row['attach'] = '';
				$sql_attachments = $ilance->db->query("
					SELECT COUNT(*) AS count
					FROM " . DB_PREFIX . "attachment
					WHERE attachtype = 'pmb'
						AND pmb_id = '" . $row['event_id'] . "'
						AND project_id = '" . $row['project_id'] . "'
						AND visible = '1'
				");
				if ($ilance->db->num_rows($sql_attachments) > 0)
				{
					$res = $ilance->db->fetch_array($sql_attachments, DB_ASSOC);
					if ($res['count'] > 0)
					{
						$row['attach'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'paperclip.gif" border="0" alt="' . $res['count'] . ' {_attachments}" /> ';
					}
				}
				if ($row['to_id'] == $_SESSION['ilancedata']['user']['userid'])
				{
					$toID = $row['from_id'];
					$fromID = $_SESSION['ilancedata']['user']['userid'];
					$row['date_posted2'] = print_date($row['postdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				}
				else
				{
					$toID = $row['to_id'];
					$fromID = $_SESSION['ilancedata']['user']['userid'];
					$row['date_posted2'] = print_date($row['postdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				}
				$row['from'] = print_username($row['from_id'], 'plain');
				$row['to'] = print_username($toID, 'plain');
				$row['recipient'] = ($fromID == $_SESSION['ilancedata']['user']['userid']) ? $row['to'] : $row['from'];
				$crypted = array(
					'event_id' => $row['event_id'],
					'project_id' => $projects_array['project_id'],
					'from_id' => $fromID,
					'to_id' => $toID
				);
				//$row['subject'] = '<a href="' . HTTPS_SERVER . $ilpage['pmb'] . '?crypted=' . encrypt_url($crypted) . '" onclick="popUP(this.href,\'messageboard\',\'' . $ilconfig['globalfilters_pmbpopupwidth'] . '\',\'' . $ilconfig['globalfilters_pmbpopupheight'] . '\',\'yes\',\'yes\'); return false;">' . $ilance->pmb->fetch_last_pmb_subject($row['project_id'], $row['event_id'], $_SESSION['ilancedata']['user']['userid']) . '</a>';
				$row['subject'] = '<a href="javascript:void(0)" onclick="update_pmb_crypted(\'' . encrypt_url($crypted) . '\')">' . $ilance->pmb->fetch_last_pmb_subject($row['project_id'], $row['event_id'], $_SESSION['ilancedata']['user']['userid']) . '</a>';
				$row['posts'] = $ilance->pmb->fetch_pmb_posts($row['project_id'], $row['event_id']);
				$row['unread'] = $ilance->pmb->fetch_unread_pmb_posts($row['project_id'], $row['event_id'], $_SESSION['ilancedata']['user']['userid']);
				$row['action'] = '<input type="checkbox" name="event_id[]" value="' . $row['event_id'] . '" />';
			}
			$pmbs[] = $row;
			$row_count++;
		}
	}
	else
	{
		$show['no_pmbs'] = true;
	}
	if ($pmb['folder'] == 'sent')
	{
		$show['pmb_folder_sent'] = true;
	}
	$pmbmodal = $ilance->template->fetch_pmb_modal();
	$pprint_array = array('subject','date','body','siteemailcount','receivedcount','sentcount','archivedcount','pmbmodal','prevnext2','pmbgauge','pmfoldername','pmb_name','pmbfolders','advchat','pmbadv','alertsadv','chatsadv','advtoday','advyesterday','advarchived','advhistory','site_email','collapseobj_alerthistory','collapseobj_chats','collapseobj_pmbs','collapseobj_alerts','collapseobj_chathistory','collapseobj_pmbstoday','collapseobj_pmbsyesterday','collapseobj_pmbsarchived','count_archived','count_yesterday','count_alerts','count_transcripts','count_today','prevnext');
	$ilance->template->fetch('main', 'messages.html');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', 'pmbs');
	$ilance->template->parse_loop('main', 'chathistory');
	$ilance->template->parse_loop('main', 'alerthistory');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>