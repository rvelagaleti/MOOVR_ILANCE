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

/**
* Email data manager class to handle the majority of sending emails in ILance.
*
* @package      iLance\Email
* @version      4.0.0.8059
* @author       ILance
*/
class email
{
	var $mail = null;
	var $from = null;
        var $fromname = '';
	var $slng = null;
	var $subject = null;
	var $message = null;
        var $emailid = '';
	var $departmentid = 0;
	var $dohtml = false;
        var $logtype = 'alert';
	var $type = null;
	public $varname = '';
	public $sent = false;
	public $toqueue = true;
	public $user_id = 0;
        function __construct()
	{
		global $ilconfig;
		$this->toqueue = ($ilconfig['emailssettings_queueenabled'] == '1') ? true : false;
		require_once(DIR_CORE . 'functions_email.php');
	}
	function get($varname = '')
	{
		global $ilance, $ilconfig, $show;
		
		($apihook = $ilance->api('datamanager_email_get_start')) ? eval($apihook) : false;
		
		if (!empty($varname))
		{
			if (empty($this->slng))
			{
				$this->slng = 'eng';
			}
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "id, subject_" . $this->slng . " AS subject, message_" . $this->slng . " AS message, type, departmentid, ishtml
				FROM " . DB_PREFIX . "email
				WHERE varname = '" . $ilance->db->escape_string($varname) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$this->varname = $varname;
				$this->emailid = $ilance->crypt->three_layer_encrypt(trim($varname), $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']);
				$this->subject = stripslashes(trim($res['subject']));
				$this->message = stripslashes(trim($res['message']));
				$this->departmentid = $res['departmentid'];
				$this->type = $res['type'];
				$this->dohtml = $res['ishtml'];
				unset($res);
			}
		}
		
		($apihook = $ilance->api('datamanager_email_get_end')) ? eval($apihook) : false;
	}
	
	function set($toconvert = array())
	{
		global $ilance, $ilconfig, $show, $ilpage;
		if (isset($toconvert) AND is_array($toconvert))
		{
			foreach ($toconvert AS $search => $replace)
			{
				if (!empty($search))
				{
					$this->subject = str_replace("$search", $replace, $this->subject);
					$this->message = str_replace("$search", $replace, $this->message);
				}
			}
			if (!$this->dohtml)
			{
				$this->message = strip_tags($this->message);
			}
			unset($search, $replace);
		}
		$unsubscribelink = HTTPS_SERVER . $ilpage['preferences'] . '?cmd=email';
		if (!empty($this->mail))
                {
                        if (is_array($this->mail))
                        {
				// handle sending the same email template to multiple receipents
                                foreach ($this->mail AS $email)
                                {
                                        if (is_valid_email($email))
                                        {
						$unsubscribelink = HTTPS_SERVER . $ilpage['preferences'] . '?cmd=email';
                                        }
                                }
                        }
                        else
                        {
                                if (is_valid_email($this->mail))
                                {
                                        $unsubscribelink = HTTPS_SERVER . $ilpage['preferences'] . '?cmd=email&do=unsubscribe&id=' . urlencode($this->emailid) . '&e=' . urlencode(base64_encode($this->mail));
                                }
                        }
			
			($apihook = $ilance->api('datamanager_email_send_end')) ? eval($apihook) : false;
                }
		$commonfields = array(
			'{{site_name}}' => SITE_NAME,
			'{{site_email}}' => SITE_EMAIL,
			'{{site_phone}}' => SITE_PHONE,
			'{{site_address}}' => SITE_ADDRESS,
			'{{http_server_admin}}' => HTTP_SERVER_ADMIN,
			'{{https_server_admin}}' => HTTPS_SERVER_ADMIN,
			'{{https_server}}' => HTTPS_SERVER,
			'{{http_server}}' => HTTP_SERVER,
			'{{generate_date}}' => print_date(DATETIME24H, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
			'{{email_id}}' => $this->emailid,
			'{{email_notifications}}' => HTTPS_SERVER . $ilpage['preferences'] . '?cmd=email',
			'{{email_unsubscribe}}' => $unsubscribelink,
			'{{email_unsubscribeall}}' => HTTPS_SERVER . $ilpage['preferences'] . '?cmd=email&do=unsubscribeall',
		);
		
		($apihook = $ilance->api('datamanager_email_set_commonfields')) ? eval($apihook) : false;
		
		foreach ($commonfields AS $search => $replace)
		{
			if (!empty($search))
			{
				$this->subject = str_replace("$search", $replace, $this->subject);
				$this->message = str_replace("$search", $replace, $this->message);
			}
			unset($search, $replace);
		}
	}
	
	function send()
	{
                global $ilance, $show;
		if (empty($this->from))
		{
			$this->from = SITE_EMAIL;
		}
                if (empty($this->fromname))
                {
                        $this->fromname = SITE_NAME;
                }
		if ($this->departmentid > 0)
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "email, title
				FROM " . DB_PREFIX . "email_departments
				WHERE departmentid = '" . intval($this->departmentid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$this->from = handle_input_keywords($res['email']);
				$this->fromname = handle_input_keywords($res['title']);
			}
		}
                if (!empty($this->mail))
                {
			if (!$this->toqueue)
			{
				($apihook = $ilance->api('datamanager_email_send_start')) ? eval($apihook) : false;
				
				if (is_array($this->mail))
				{
					// handle sending the same email template to multiple receipents
					foreach ($this->mail AS $email)
					{
						if (is_valid_email($email) AND is_notification_unsubscribed($email, $this->varname) == false)
						{
							send_email($email, $this->subject, $this->message, $this->from, $this->fromname, $this->dohtml, $this->logtype, $this->slng);
						}
					}
				}
				else
				{
					if (is_valid_email($this->mail) AND is_notification_unsubscribed($this->mail, $this->varname) == false)
					{
						send_email($this->mail, $this->subject, $this->message, $this->from, $this->fromname, $this->dohtml, $this->logtype, $this->slng);
					}
				}
				
				($apihook = $ilance->api('datamanager_email_send_end')) ? eval($apihook) : false;
			}
			else
			{
				$this->add_to_queue();
			}
                }
		$this->mail = $this->subject = $this->message = $this->from = $this->fromname = $this->emailid = $this->departmentid = $this->varname = $this->type = null;
                $this->logtype = 'alert';
	}	
	
	function add_to_queue()
	{
		global $ilance;
		if (is_array($this->mail))
		{ // multiple recipients
			foreach ($this->mail AS $email)
			{
				if (is_valid_email($email) AND is_notification_unsubscribed($email, $this->varname) == false)
				{
					$ilance->db->query("
						INSERT INTO " . DB_PREFIX . "email_queue
						(id, mail, fromemail, fromname, departmentid, subject, message, dohtml, date_added, varname, type)
						VALUES(
						NULL,
						'" . $ilance->db->escape_string($email) . "',
						'" . $ilance->db->escape_string($this->from) . "',
						'" . $ilance->db->escape_string($this->fromname) . "',
						'" . $ilance->db->escape_string($this->departmentid) . "',
						'" . $ilance->db->escape_string($this->subject) . "',
						'" . $ilance->db->escape_string($this->message) . "',
						'" . intval($this->dohtml) . "',
						'" . $ilance->db->escape_string(TIMESTAMPNOW) . "',
						'" . $ilance->db->escape_string($this->varname) . "',
						'" . $ilance->db->escape_string($this->type) . "')
					");
				}
			}
		}
		else
		{ // single recipient
			$ilance->db->query("
				INSERT INTO " . DB_PREFIX . "email_queue
				(id, mail, fromemail, fromname, departmentid, subject, message, dohtml, date_added, varname, type)
				VALUES(
				NULL,
				'" . $ilance->db->escape_string($this->mail) . "',
				'" . $ilance->db->escape_string($this->from) . "',
				'" . $ilance->db->escape_string($this->fromname) . "',
				'" . $ilance->db->escape_string($this->departmentid) . "',
				'" . $ilance->db->escape_string($this->subject) . "',
				'" . $ilance->db->escape_string($this->message) . "',
				'" . intval($this->dohtml) . "',
				'" . $ilance->db->escape_string(TIMESTAMPNOW) . "',
				'" . $ilance->db->escape_string($this->varname) . "',
				'" . $ilance->db->escape_string($this->type) . "')
			");
		}
	}
	
	function add_to_log()
	{
		if (!defined('NO_DB'))
		{
			global $ilance;
			$logtype = !empty($this->logtype) ? $this->logtype : '';
			$user_id = !empty($this->user_id) ? $this->user_id : '';
			$mail = !empty($this->mail) ? $this->mail : '';
			$subject = !empty($this->subject) ? $this->subject : '';
			$message = !empty($this->message) ? $this->message : '';
			$varname = !empty($this->varname) ? $this->varname : '';
			$type = !empty($this->type) ? $this->type : ''; 
			$dohtml = isset($this->dohtml) ? $this->dohtml : 0;
			$sent = ($this->sent) ? 'yes' : 'no';
			$ilance->db->query("
				INSERT INTO " . DB_PREFIX . "emaillog
				(emaillogid, logtype, user_id, email, subject, body, date, varname, type, sent, ishtml)
				VALUES(
				NULL,
				'" . $ilance->db->escape_string($logtype) . "',
				'" . $ilance->db->escape_string($user_id) . "',
				'" . $ilance->db->escape_string($mail) . "',
				'" . $ilance->db->escape_string($subject) . "',
				'" . $ilance->db->escape_string($message) . "',
				'" . DATETIME24H . "',
				'" . $ilance->db->escape_string($varname) . "',
				'" . $ilance->db->escape_string($type) . "',
				'" . $sent . "',
				'" . intval($dohtml) . "')
			", 0, null, __FILE__, __LINE__);
		}
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>