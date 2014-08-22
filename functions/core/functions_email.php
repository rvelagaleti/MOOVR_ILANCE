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
* Core email functions for iLance
*
* @package      iLance\Global\Email
* @version      4.0.0.8059
* @author       ILance
*/

/**
* Function to dispatch new email using php's mail() or SMTP
*
* @param       string         to email address
* @param       string         email subject
* @param       string         email message
* @param       string         email from address
* @param       string         email from name
* @param       bool           send html formatted email? (default is false)
* @param       string         log type (default alert)
* @param       string         short form language identifier (default eng)
*
* @return      bool           Returns true on successful email dispatch
*/
function send_email($toemail = '', $subject = '', $message = '', $from = '', $fromname = '', $html = false, $logtype = 'alert', $slng = '')
{
	global $ilance, $ilconfig, $ilpage;
	if (empty($toemail) OR empty($subject) OR empty($message))
	{
		return false;
	}
	$ilance->email->user_id = 0;
	$pathto_sendmail = @ini_get('sendmail_path');
	$encoding = 'UTF-8';
	$delimiter = (!$pathto_sendmail OR $ilconfig['globalserversmtp_enabled'] == '1') ? "\r\n" : "\n";
	if (!empty($slng))
	{
		$locale = $ilance->fetch_language_locale(0, $slng);
		setlocale(LC_TIME, $locale['locale']);
		unset($locale);
	}

	($apihook = $ilance->api('send_email_start')) ? eval($apihook) : false;

	if (!empty($toemail))
	{
		$toemail = un_htmlspecialchars(trim($toemail));
		$subject = trim($subject);
		$subject = $ilance->template->parse_hash('emailtemplate', array ('ilpage' => $ilpage), 1, $subject);
		$subject = $ilance->template->parse_template_phrases('emailtemplate');
		$subject = $ilance->bbcode->strip_bb_tags($subject);
		$subject = str_replace(array ("&lt;", "&gt;", '&amp;', '&#039;', '&quot;', '&lt;', '&gt;', '&euro;', '&pound;'), array ("<", ">", '&', '\'', '"', '<', '>', '€', '£'), htmlspecialchars_decode($subject, ENT_NOQUOTES));
		$message = preg_replace("#(\r\n|\r|\n)#s", $delimiter, trim($message));
		$message = $ilance->template->parse_hash('emailtemplate', array ('ilpage' => $ilpage), 1, $message);
		$message = $ilance->template->parse_template_phrases('emailtemplate');
		$message = $ilance->bbcode->strip_bb_tags($message);
		$message = str_replace(array ("&lt;", "&gt;", '&amp;', '&#039;', '&quot;', '&lt;', '&gt;', '&euro;', '&pound;'), array ("<", ">", '&', '\'', '"', '<', '>', '€', '£'), htmlspecialchars_decode($message, ENT_NOQUOTES));
		if ($ilconfig['template_textalignment'] != 'left')
		{
			$message = '<html dir="rtl">' . $message . '</html>';
			$html = true;
		}
		// #### attach HTML email header/footer ########################
		$content_plain = $content = $message;
		if ($html)
		{
			$html2 = $ilance->template->fetch_template('TEMPLATE_email.html');
			$html2 = $ilance->template->parse_hash('TEMPLATE_email.html', array ('ilpage' => $ilpage), 0, $html2);
			$html2 = stripslashes($html2);
			$html2 = addslashes($html2);
			eval('$message = "' . $html2 . '";');
			$message = stripslashes($message);
			$message = html_entity_decode($message, ENT_QUOTES, 'UTF-8');
		}
		$http_host = HTTP_SERVER;
		if (!$http_host)
		{
			$http_host = mb_substr(md5($message), 6, 12) . '.ilance_unknown.unknown';
		}
		$msgid = '<' . date('YmdHs') . '.' . mb_substr(md5($message . microtime()), 0, 6) . rand(100000, 999999) . '@' . $http_host . '>';
		$from = ((empty($from)) ? SITE_EMAIL : $from);
		$fromname = ((empty($fromname)) ? SITE_NAME : $fromname);
		@ini_set('sendmail_from', $from);
		$headers = "From: \"$fromname\" <" . $from . ">" . $delimiter
		         . "Return-Path: " . $from . $delimiter
			 . "Message-ID: " . $msgid . $delimiter
			 . "X-Priority: 3" . $delimiter
			 . "X-Mailer: " . stripslashes(SITE_NAME) . " " . $ilance->config['ilversion'] . $delimiter
		       //. 'Content-Type: multipart/alternative; boundary="' . md5($message . microtime() . '"' . $delimiter
			 . (($html) ? "Content-Type: text/html" . iif($encoding, "; charset=$encoding") . $delimiter : "Content-Type: text/plain" . iif($encoding, "; charset=$encoding") . $delimiter)
			 . "Content-Transfer-Encoding: 8bit" . $delimiter;
		$ilance->email->dohtml = intval($html);
		/*
		// send html and plain text email simutaneously
		$message = '——' . md5($message . microtime() . '
		Content-Type: text/plain; charset=utf-8
		Content-Transfer-Encoding: 8bit
		
		Plain text email here.';
		
		$message .= '——' . md5($message . microtime() . '
		Content-Type: text/html; charset=utf-8
		Content-Transfer-Encoding: 8bit
		
		HTML email here
		
		--' . md5($message . microtime() . '--’;*/
		if ($ilconfig['globalserversmtp_enabled'] AND !empty($ilconfig['globalserversmtp_host']) AND !empty($ilconfig['globalserversmtp_port']))
		{
			@ini_set('SMTP', $ilconfig['globalserversmtp_host']);
			@ini_set('smtp_port', $ilconfig['globalserversmtp_port']);
			$ilance->smtp->toemail = $toemail;
			$ilance->smtp->fromemail = $from;
			$ilance->smtp->headers = $headers;
			$ilance->smtp->subject = $subject;
			$ilance->smtp->message = $message;
			$ilance->email->sent = $ilance->smtp->send();
			if ($ilance->email->sent)
			{
				$sql = $ilance->db->query("
					SELECT user_id
					FROM " . DB_PREFIX . "users
					WHERE email = '" . $ilance->db->escape_string($toemail) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$res = $ilance->db->fetch_array($sql, DB_ASSOC);
					$ilance->email->user_id = $res['user_id'];
					unset($res);
				}
			}
			$ilance->email->add_to_log();
		}
		else
		{
			if (mb_send_mail($toemail, $subject, $message, $headers))
			{
				$ilance->email->sent = true;
				$sql = $ilance->db->query("
					SELECT user_id
					FROM " . DB_PREFIX . "users
					WHERE email = '" . $ilance->db->escape_string($toemail) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$res = $ilance->db->fetch_array($sql, DB_ASSOC);
					$ilance->email->user_id = $res['user_id'];
					unset($res);
				}
			}
			$ilance->email->add_to_log();
		}
	}
}

/**
* Function to unsubscribe a user from receiving a particular email template on the site
*
* @param       string         email address
* @param       string         email varname (encrypted version)
*
* @return      bool           Returns true on successful email unsubscribe
*/
function unsubscribe_notification($email = '', $emailid = '')
{
	global $ilance, $show, $ilconfig;
	$slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';
	$emailvarname = $ilance->crypt->three_layer_decrypt($emailid, $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']);
	$sql = $ilance->db->query("
		SELECT name_" . $ilance->db->escape_string($slng) . " AS name
		FROM " . DB_PREFIX . "email
		WHERE varname = '" . $ilance->db->escape_string($emailvarname) . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
		$sql2 = $ilance->db->query("
			SELECT id
			FROM " . DB_PREFIX . "email_optout
			WHERE varname = '" . $ilance->db->escape_string($emailvarname) . "'
				AND email = '" . $ilance->db->escape_string($email) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql2) == 0)
		{
			$ilance->db->query("
				INSERT INTO " . DB_PREFIX . "email_optout
				(id, email, varname)
				VALUES (
				NULL,
				'" . $ilance->db->escape_string($email) . "',
				'" . $ilance->db->escape_string($emailvarname) . "')
			", 0, null, __FILE__, __LINE__);
			return true;
		}
	}
	return false;
}

/**
* Function to determine if a specific user email address is unsubscribed to a particular notification
*
* @param       string         email address
* @param       string         email varname (non-encrypted version)
*
* @return      bool           Returns true if an email is unsubscribed from a specific notification otherwise returns false
*/
function is_notification_unsubscribed($email = '', $varname = '')
{
	global $ilance, $show, $ilconfig;
	$sql = $ilance->db->query("
		SELECT id
		FROM " . DB_PREFIX . "email_optout
		WHERE varname = '" . $ilance->db->escape_string($varname) . "'
			AND email = '" . $ilance->db->escape_string($email) . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
		return true;
	}
	return false;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>