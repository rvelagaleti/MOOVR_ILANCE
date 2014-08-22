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
// #### EMAIL EXPORT ###########################################
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'email-export')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	if (isset($ilance->GPC['method']))
	{
		switch ($ilance->GPC['method'])
		{
			case 'newline':
			{
				$sql = $ilance->db->query("
					SELECT email
					FROM " . DB_PREFIX . "users
					ORDER BY user_id ASC
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$txt = '';
					while ($emails = $ilance->db->fetch_array($sql))
					{
						$txt .= trim($emails['email']) . LINEBREAK;
					}
				}
				$ext = '.txt';
				$mime = 'text/plain';
				break;
			}                                    
			case 'csv':
			{
				$sql = $ilance->db->query("
					SELECT email
					FROM " . DB_PREFIX . "users
					ORDER BY user_id ASC
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$txt = '';
					while ($emails = $ilance->db->fetch_array($sql))
					{
						$txt .= trim($emails['email']) . ",";
					}
					$txt = mb_substr($txt, 0, -1);
				}
				$ext = '.csv';
				$mime = 'text/x-csv';
				break;
			}                                    
			case 'csvnewline':
			{
				$sql = $ilance->db->query("
					SELECT email
					FROM " . DB_PREFIX . "users
					ORDER BY user_id ASC
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$txt = '';
					while ($emails = $ilance->db->fetch_array($sql))
					{
						$txt .= trim($emails['email']) . "," . LINEBREAK;
					}
				}
				$ext = '.csv';
				$mime = 'text/x-csv';
				break;
			}
		}
		$ilance->common->download_file($txt, "email-list".$ext, $mime);
	}
}
// count total emails in system
$emailcount = 0;
$ec = $ilance->db->query("
	SELECT COUNT(*) AS count
	FROM " . DB_PREFIX . "users
");
if ($ilance->db->num_rows($ec) > 0)
{
	$rs = $ilance->db->fetch_array($ec);
	$emailcount = (int)$rs['count'];
}
// email export options
$emailmethod_pulldown  = '<select name="method" style="font-family: verdana">';
$emailmethod_pulldown .= '<option value="newline">' . '{_each_email_address_per_line}' . '</option>';
$emailmethod_pulldown .= '<option value="csv">' . '{_comma_seperated_values}' . '</option>';
$emailmethod_pulldown .= '<option value="csvnewline">' . '{_comma_seperated_values_with_newlines}' . '</option>';
$emailmethod_pulldown .= '</select>';
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_send-bulk-email')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$area_title = '{_sending_bulk_email}';
	$page_title = SITE_NAME . ' - {_sending_bulk_email}';
	$batch = isset($ilance->GPC['batch']) ? intval($ilance->GPC['batch']) : 0;
	$from = trim($ilance->GPC['from']);
	$subject = handle_input_keywords($ilance->GPC['subject']);
	$dohtml = true;
	$message = $ilance->GPC['description'];
	if (isset($ilance->GPC['users']) AND !empty($ilance->GPC['users']))
	{
		$plan = false;
		$users = explode('|', $ilance->GPC['users']);
		if (count($users) > 0)
		{
			$customwhere = '';
			foreach ($users AS $username)
			{
				$customwhere .= "username = '" . $ilance->db->escape_string($username) . "' OR ";
			}
			$customwhere = mb_substr($customwhere, 0, -4);
		}
	}
	else if ($ilance->GPC['subscriptionid'] != '0')
	{
		$plan = true;
		$subscriptionid = $ilance->GPC['subscriptionid'];
	}
	else
	{
		$plan = false;
	}
	if (isset($ilance->GPC['testmode']) AND $ilance->GPC['testmode'])
	{
		// admin sending test bulk email
		$area_title = '{_bulk_email_test_message}';
		$page_title = SITE_NAME . ' - {_bulk_email_test_message}';
		$wysiwyg_area = print_wysiwyg_editor('description', $ilance->GPC['description'], 'ckeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg'], false, '850', '220', '', 'ckeditor');
		$message = str_replace("{{username}}", $_SESSION['ilancedata']['user']['username'], $message);
		$ilance->email->mail = SITE_EMAIL;
		$ilance->email->from = $from;
		$ilance->email->subject = $subject;
		$ilance->email->message = $message;
		$ilance->email->dohtml = $dohtml;
		$ilance->email->send();
		$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'] . '?cmd=bulkemail', $_SESSION['ilancedata']['user']['slng']);
		$site_email = $from;
		$subscription_pulldown = $ilance->subscription->pulldown();
		$pprint_array = array('wysiwyg_area','description','emailcount','emailmethod_pulldown','subject','message','subscription_pulldown','site_email','numberpaid','numberunpaid','id');
		
		($apihook = $ilance->api('admincp_bulkemail_testmode_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'bulkemail.html', 1);
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	else
	{
		// admin dispatching bulk newsletter
		if ($plan == true)
		{
			$sql = $ilance->db->query("
				SELECT user_id
				FROM " . DB_PREFIX . "subscription_user
				WHERE subscriptionid = '" . intval($subscriptionid) . "'
				    AND active = 'yes'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					$sql2 = $ilance->db->query("
						SELECT username, email, first_name, last_name
						FROM " . DB_PREFIX . "users
						WHERE user_id = '" . $res['user_id'] . "'
						    AND status = 'active'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql2) > 0)
					{
						while ($res2 = $ilance->db->fetch_array($sql2, DB_ASSOC))
						{
							unset($premessage, $presubject);
							$premessage = str_replace("{{username}}", $res2['username'], $message);
							$premessage = str_replace("{{firstname}}", ucfirst($res2['first_name']), $premessage);
							$premessage = str_replace("{{lastname}}", ucfirst($res2['last_name']), $premessage);
							$presubject = str_replace("{{username}}", $res2['username'], $subject);
							$presubject = str_replace("{{firstname}}", ucfirst($res2['first_name']), $presubject);
							$presubject = str_replace("{{lastname}}", ucfirst($res2['last_name']), $presubject);
							$ilance->email->mail = $res2['email'];
							$ilance->email->from = $from;
							$ilance->email->subject = $presubject;
							$ilance->email->message = $premessage;
							$ilance->email->dohtml = $dohtml;
							$ilance->email->send();
						}
					}
				}
				print_action_success('{_bulk_email_was_sent_to_subscribers_within_the_selected_subscription_plan}', $ilance->GPC['return']);
				exit();
			}
			else
			{
				print_action_failed('{_bulk_email_could_not_be_sent_to_any_subscribers_within_the_selected_subscription_plan}', $ilance->GPC['return']);
				exit();
			}
		}
		else
		{
			if (empty($customwhere))
			{
				// sending to all active members
				$sql = $ilance->db->query("
					SELECT username, email, first_name, last_name
					FROM " . DB_PREFIX . "users
					WHERE status = 'active'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
					{
						unset($premessage, $presubject);
						$premessage = str_replace("{{username}}", $res['username'], $message);
						$premessage = str_replace("{{firstname}}", ucfirst($res['first_name']), $premessage);
						$premessage = str_replace("{{lastname}}", ucfirst($res['last_name']), $premessage);
						$presubject = str_replace("{{username}}", $res['username'], $subject);
						$presubject = str_replace("{{firstname}}", ucfirst($res['first_name']), $presubject);
						$presubject = str_replace("{{lastname}}", ucfirst($res['last_name']), $presubject);
						$ilance->email->mail = $res['email'];
						$ilance->email->from = $from;
						$ilance->email->subject = $presubject;
						$ilance->email->message = $premessage;
						$ilance->email->dohtml = $dohtml;
						$ilance->email->send();
					}
					print_action_success('{_bulk_email_was_sent_to_all_active_subscribers}', $ilance->GPC['return']);
					exit();
				}
		       }
		       else
		       {
				// send to only selected usernames admin has defined
				$sql = $ilance->db->query("
					SELECT username, email, first_name, last_name
					FROM " . DB_PREFIX . "users
					WHERE $customwhere
					    AND status = 'active'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
					{
						unset($premessage, $presubject);
						$premessage = str_replace("{{username}}", $res['username'], $message);
						$premessage = str_replace("{{firstname}}", ucfirst($res['first_name']), $premessage);
						$premessage = str_replace("{{lastname}}", ucfirst($res['last_name']), $premessage);
						$presubject = str_replace("{{username}}", $res['username'], $subject);
						$presubject = str_replace("{{firstname}}", ucfirst($res['first_name']), $presubject);
						$presubject = str_replace("{{lastname}}", ucfirst($res['last_name']), $presubject);
						$ilance->email->mail = $res['email'];
						$ilance->email->from = $from;
						$ilance->email->subject = $presubject;
						$ilance->email->message = $premessage;
						$ilance->email->dohtml = $dohtml;
						$ilance->email->send();
					}
					print_action_success('{_bulk_email_was_sent_to_selected_subscribers}', $ilance->GPC['return']);
					exit();
				}
				else
				{
				    print_action_failed('{_the_selected_user_was_not_found}', $ilance->GPC['return']);
				}
			}
		}
	}
}
else
{
	$area_title = '{_bulk_email_manager}';
	$page_title = SITE_NAME . ' - {_bulk_email_manager}';
	
	($apihook = $ilance->api('admincp_bulkemail_management')) ? eval($apihook) : false;
	
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'] . '?cmd=bulkemail', $_SESSION['ilancedata']['user']['slng']);
	
	$site_email = SITE_EMAIL;
	$subscription_pulldown = $ilance->subscription->pulldown();
	$wysiwyg_area = print_wysiwyg_editor('description', '', 'ckeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg'], false, '850', '220', '', 'ckeditor');
	$pprint_array = array('wysiwyg_area','emailcount','emailmethod_pulldown','subject','message','subscription_pulldown','site_email','numberpaid','numberunpaid','id');
	
	($apihook = $ilance->api('admincp_bulkemail_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'bulkemail.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
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