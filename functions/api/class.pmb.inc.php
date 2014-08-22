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
* PMB class
*
* @package      iLance\PMB
* @version      4.0.0.8059
* @author       ILance
*/
class pmb
{
	/*
	* Function to fetch and print the last subject title posted within the messages area
	*
	* @param        integer     project id
	* @param        integer     event id
	* @param        integer     to user id
	* @param        bool        specifies if we should not bold the subject text?
	*
	* @return	string      Returns the formatted subject text
	*/
	function fetch_last_pmb_subject($projectid = 0, $eventid = 0, $toid = 0, $nobold = 0)
	{
		global $ilance, $phrase;
		$id = 0;
		$sql = $ilance->db->query("
			SELECT id, subject
			FROM " . DB_PREFIX . "pmb
			WHERE project_id = '" . intval($projectid) . "'
				AND event_id = '" . $ilance->db->escape_string($eventid) . "'
			ORDER BY id DESC
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$id = $res['id'];
			if (!empty($res['subject']))
			{
				$html = handle_input_keywords($res['subject']);
			}
			else
			{
				$html = '{_no_subject}';
			}
		}
		else
		{
			$html = '{_no_subject}';
		}
		// is the latest post in this message board new to the user viewing?
		$sql2 = $ilance->db->query("
			SELECT to_status
			FROM " . DB_PREFIX . "pmb_alerts
			WHERE project_id = '" . intval($projectid) . "'
				AND event_id = '" . $ilance->db->escape_string($eventid) . "'
				AND id = '" . intval($id) . "'
				AND to_id = '" . intval($toid) . "'
			ORDER BY id DESC
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql2) > 0)
		{
			$res2 = $ilance->db->fetch_array($sql2, DB_ASSOC);
			if ($res2['to_status'] == 'new')
			{
				$html = '<strong>' . $html . '</strong>';
			}
		}
		return $html;
	}
    
	/*
	* Function to fetch and print the total number of private message posts within a particular message board
	*
	* @param        integer     project id
	* @param        integer     event id
	*
	* @return	string      Returns the number of messages posted in the board
	*/
	function fetch_pmb_posts($projectid = 0, $eventid = 0)
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "pmb
			WHERE project_id = '" . intval($projectid) . "'
				AND event_id = '" . intval($eventid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			return $res['count'];
		}
		return 0;
	}
    
	/*
	* Function to fetch and print the total number of private messages posted within a particular message folder
	*
	* @param        integer     project id
	* @param        string      defines what folder we are looking into (received, sent, etc)
	*
	* @return	string      Returns the number of message boards
	*/
	function fetch_pmb_count($userid = 0, $dowhat = '')
	{
		global $ilance;
		if (isset($dowhat) AND !empty($dowhat))
		{
			switch ($dowhat)
			{
				case 'received':
				{
					$sql = $ilance->db->query("
						SELECT id, event_id, project_id, from_id, to_id, isadmin, from_status, to_status, track_status, track_dateread, track_popup
						FROM " . DB_PREFIX . "pmb_alerts
						WHERE to_id = '" . intval($userid) . "'
							AND to_status != 'deleted'
							AND to_status != 'archived'
						GROUP BY event_id
					", 0, null, __FILE__, __LINE__);
					$html = (int) @$ilance->db->num_rows($sql);
					break;
				}
				case 'sent':
				{
					$sql = $ilance->db->query("
						SELECT id, event_id, project_id, from_id, to_id, isadmin, from_status, to_status, track_status, track_dateread, track_popup
						FROM " . DB_PREFIX . "pmb_alerts
						WHERE from_id = '" . intval($userid) . "'
							AND from_status != 'deleted'
							AND from_status != 'archived'
						GROUP BY event_id
					", 0, null, __FILE__, __LINE__);
					$html = (int) @$ilance->db->num_rows($sql);
					break;
				}
				case 'archived':
				{
					$sql = $ilance->db->query("
						SELECT id, event_id, project_id, from_id, to_id, isadmin, from_status, to_status, track_status, track_dateread, track_popup
						FROM " . DB_PREFIX . "pmb_alerts
						WHERE (from_id = '" . intval($userid) . "' AND from_status = 'archived'
							OR to_id = '" . intval($userid) . "' AND to_status = 'archived')
						GROUP BY event_id
					", 0, null, __FILE__, __LINE__);
					$html = (int) @$ilance->db->num_rows($sql);
					break;
				}
				case 'siteemail':
				{
					$sql = $ilance->db->query("
						SELECT emaillogid, emaillogid, logtype, user_id, project_id, email, subject, body, date, sent, user_id
						FROM " . DB_PREFIX . "emaillog
						WHERE logtype = 'alert'
							AND user_id = '" . intval($userid) . "'
					", 0, null, __FILE__, __LINE__);
					$html = (int) @$ilance->db->num_rows($sql);
					break;
				}
			}
		}
		return $html;
	}
    
	/*
	* Function to print out an attachment gauge based on a supplied user id
	*
	* @param        integer         user id
	*
	* @return       string          Returns HTML formatted bar of attachment usage
	*/
	function print_pmb_gauge($userid = 0)
	{
		global $ilance, $ilconfig, $phrase;
		$total = $this->fetch_pmb_count(intval($userid), 'received');
		$total += $this->fetch_pmb_count(intval($userid), 'sent');
		$total += $this->fetch_pmb_count(intval($userid), 'archived');
		$limit = $ilance->permissions->check_access($userid, 'pmbtotal');
		if (!is_numeric($limit))
		{
			$limit = $total;
		}
		if ($limit == 0 AND $total == 0)
		{
			$percentage_used = 0;
		}
		else
		{
			$percentage_used = round(($total / $limit) * 100);
		}
		$percentage_left = (100 - $percentage_used);
		$html = '<table width="100%" border="0" align="left" cellpadding="0" cellspacing="0" dir="' . $ilconfig['template_textdirection'] . '" style="display:none">
<tr> 
    <td width="69%" class="gaugeArea">
	<table width="100%" style="height:9px" align="center" cellpadding="0" cellspacing="0" class="gaugeLayout" dir="' . $ilconfig['template_textdirection'] . '">
	<tr> 
		<td width="4"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'begin-filled.gif" /></td>
		<td title="' . round($percentage_left) . '% {_left}" width="' . $percentage_used . '%" style="background:url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'fill.gif); background-repeat:repeat-x; background-position:center"><span title="' . round($percentage_left) . '% {_left}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'fill.gif" alt="' . round($percentage_left) . '% {_left}" /></span></td>
		<td width="' . $percentage_left . '%" style="background:url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'empty.gif); background-repeat:repeat-x; background-position:center;margin-top:-1px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'empty.gif" alt="" /></td>
		<td width="4"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'end-empty.gif" /></td>
	</tr>
	</table>
    </td>
    <td><div align="center" class="smaller"><strong>' . $percentage_used . '%</strong> {_used}</div></td>
</tr>
</table>
<div class="smaller gray">' . $ilance->language->construct_phrase('{_you_have_x_pmbs_stored_of_a_total_x_allowed}', array (number_format($total), number_format($limit))) . '</div>';
		return $html;
	}
    
	/*
	* Function to fetch and print the total number of unread private message posts within a particular message board
	*
	* @param        integer     project id
	* @param        integer     event id
	* @param        integer     user id
	*
	* @return	string      Returns the number of unread messages posted in a particular message board
	*/
	function fetch_unread_pmb_posts($projectid = 0, $eventid = 0, $userid = 0)
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT id
			FROM " . DB_PREFIX . "pmb_alerts
			WHERE project_id = '" . intval($projectid) . "'
				AND to_id = '" . intval($userid) . "'
				AND event_id = '" . intval($eventid) . "'
				AND to_status = 'new'
		", 0, null, __FILE__, __LINE__);
		$num = $ilance->db->num_rows($sql);
		if ($num > 0)
		{
			return $num;
		}
		return 0;
	}
    
	/*
	* Function to track and update any messages within a message board posted as read to maintain proper read/unread functionality
	*
	* @param        integer     private message board id
	* @param        integer     user id
	*
	* @return	void
	*/
	function update_pmb_tracker($id = 0, $userid = 0)
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT id, event_id, project_id, from_id, to_id, isadmin, from_status, to_status, track_status, track_dateread, track_popup
			FROM " . DB_PREFIX . "pmb_alerts
			WHERE id = '" . intval($id) . "'
				AND to_id = '" . intval($userid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			if ($res['to_status'] == 'new')
			{
				// update as active, user read message.
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "pmb_alerts
					SET to_status = 'active',
					track_dateread = '" . DATETIME24H . "',
					track_status = 'read'
					WHERE id = '" . intval($id) . "'
						AND to_id = '" . intval($userid) . "'
				", 0, null, __FILE__, __LINE__);
			}
		}
	}
    
	/*
	* Function to remove a private message board post
	*
	* @param        integer     private message board post number id
	*
	* @return	void
	*/
	function remove_pmb_post($id = 0)
	{
		global $ilance;
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "pmb_alerts
			WHERE id = '" . intval($id) . "'
		", 0, null, __FILE__, __LINE__);
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "pmb
			WHERE id = '" . intval($id) . "'
		", 0, null, __FILE__, __LINE__);
		return true;
	}
    
	/*
	* Function to fetch the PMB event id based on a supplied project id, from user id and a to user id.
	*
	* @param        integer      project id
	* @param        integer      from user id
	* @param        integer      to user id
	*
	* @return	integer      Returns an event id, if none exists, will return one
	*/
	function fetch_pmb_eventid($projectid = 0, $fromid = 0, $toid = 0)
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT event_id
			FROM " . DB_PREFIX . "pmb_alerts
			WHERE project_id = '" . intval($projectid) . "'
				AND from_id = '" . intval($fromid) . "'
				AND to_id = '" . intval($toid) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			return $res['event_id'];
		}
		return TIMESTAMPNOW;
	}
    
	/*
	* Function to compose a private message from one user to the next
	*
	* @param        integer      to user id
	* @param        integer      from user id
	* @param        string       message subject
	* @param        string       message body
	* @param        integer      project id
	* @param        integer      pmb event id
	* @param        boolean      is admin composing message (default false)
	*
	* @return	nothing
	*/
	function compose_private_message($to_id = 0, $from_id = 0, $subject = '', $message = '', $project_id = 0, $event_id = '', $isadmin = 0)
	{
		global $ilance, $ilconfig, $phrase;
		if (empty($event_id))
		{
			$event_id = time();
		}
		$pmb['message'] = $message;
		$pmb['subject'] = (isset($subject) AND !empty($subject)) ? $subject : '{_no_subject}';
		$pmb['ishtml'] = ($ilconfig['default_pmb_wysiwyg'] == 'ckeditor') ? 1 : 0;
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "pmb
			(id, project_id, event_id, datetime, message, subject, ishtml)
			VALUES(
			NULL,
			'" . intval($project_id) . "',
			'" . intval($event_id) . "',
			'" . DATETIME24H . "',
			'" . $ilance->db->escape_string($pmb['message']) . "',
			'" . $ilance->db->escape_string($pmb['subject']) . "',
			'" . $ilance->db->escape_string($pmb['ishtml']) . "')
		", 0, null, __FILE__, __LINE__);
		$insertid = $ilance->db->insert_id();
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "pmb_alerts
			(id, event_id, project_id, from_id, to_id, from_status, to_status, isadmin)
			VALUES(
			'" . $insertid . "',
			'" . intval($event_id) . "',
			'" . intval($project_id) . "',
			'" . intval($from_id) . "',
			'" . intval($to_id) . "',
			'active',
			'new',
			'" . $isadmin . "')
		", 0, null, __FILE__, __LINE__);
		// since we're the poster let's update the message to "active"
		$sql_active = $ilance->db->query("
			SELECT event_id
			FROM " . DB_PREFIX . "pmb_alerts
			WHERE event_id = '" . intval($event_id) . "'
			AND id = '" . intval($insertid) . "'
		", 0, null, __FILE__, __LINE__);
		while ($res_active = $ilance->db->fetch_array($sql_active, DB_ASSOC))
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "pmb_alerts
				SET from_status = 'active'
				WHERE event_id = '" . $res_active['event_id'] . "'
					AND id = '" . intval($insertid) . "'
					AND from_id = '" . intval($from_id) . "'
			", 0, null, __FILE__, __LINE__);
		}
		if (isset($isadmin) AND $isadmin)
		{
			$sql_fromid = $ilance->db->query("
				SELECT username, email
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . intval($from_id) . "'
				AND isadmin = '1'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_fromid) > 0)
			{
				// hide/mask admin's username
				$result_fromid = $ilance->db->fetch_array($sql_fromid, DB_ASSOC);
				//$result_fromid['username'] = 'Administrator';
			}
		}
		else
		{
			$sql_fromid = $ilance->db->query("
				SELECT username, email
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . intval($from_id) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_fromid) > 0)
			{
				$result_fromid = $ilance->db->fetch_array($sql_fromid, DB_ASSOC);
			}
		}
		$sql_toid = $ilance->db->query("
			SELECT username, email
			FROM " . DB_PREFIX . "users
			WHERE user_id = '" . intval($to_id) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql_toid) > 0)
		{
			$result_toid = $ilance->db->fetch_array($sql_toid, DB_ASSOC);
		}
		if ($ilconfig['globalfilters_emailfilterpmb'])
		{
			$pmb['message'] = strip_email_words($pmb['message']);
		}
		if ($ilconfig['globalfilters_domainfilterpmb'])
		{
			$pmb['message'] = strip_domain_words($pmb['message']);
		}
		$ilance->email->slng = fetch_user_slng(intval($to_id));
		$ilance->email->mail = ($ilconfig['globalfilters_enablepmbspy']) ? array($result_toid['email'], SITE_EMAIL) : $result_toid['email'];
		$ilance->email->get('pmb_email_alert');
		$ilance->email->set(array(
			'{{receiver}}' => $result_toid['username'],
			'{{sender}}' => $result_fromid['username'],
			'{{message}}' => $ilance->bbcode->strip_bb_tags(strip_tags($pmb['message'])),
			'{{pmb_insert_id}}' => $insertid,
		));
		$ilance->email->send();
	}
    
	/**
	* Function to insert a public message from a detailed auction page
	*
	* @param        integer     project id
	* @param        integer     seller id
	* @param        integer     from id
	* @param        string      user name
	* @param        string      message being posted
	* @param        integer     is visible?
	*
	* @return	void
	*/
	function insert_public_message($projectid = 0, $sellerid = 0, $fromid = 0, $username = '', $message = '', $visible = '1')
	{
		global $ilance, $ilpage, $ilconfig;
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "messages
			(messageid, project_id, user_id, username, message, date, visible)
			VALUES(
			NULL,
			'" . intval($projectid) . "',
			'" . intval($fromid) . "',
			'" . $ilance->db->escape_string($username) . "',
			'" . $ilance->db->escape_string($message) . "',
			'" . DATETIME24H . "',
			'" . intval($visible) . "')
		", 0, null, __FILE__, __LINE__);
		// fetch seller info
		$seller = fetch_user('username', $sellerid);
		$selleremail = fetch_user('email', $sellerid);
		$auctiontype = fetch_auction('project_state', intval($projectid));
		$ownerid = fetch_auction('user_id', intval($projectid));
		// todo: check for seo
		if ($auctiontype == 'service')
		{
			$url = HTTP_SERVER . $ilpage['rfp'] . '?id=' . intval($projectid) . '&tab=messages#tabmessages';
		}
		else
		{
			$url = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productauctionplain', fetch_auction('cid', $projectid), $projectid, stripslashes(fetch_auction('project_title', $projectid)), '', 0, '', 0, 0, '') . '/messages' : HTTP_SERVER . $ilpage['merch'] . '?id=' . intval($projectid) . '&tab=messages#tabmessages';
		}
		if ($ownerid != $fromid)
		{
			$ilance->email->slng = fetch_user_slng(intval($sellerid));
			$ilance->email->mail = $selleremail;
			$ilance->email->get('new_public_message');
			$ilance->email->set(array (
				'{{seller}}' => $seller,
				'{{sender}}' => $_SESSION['ilancedata']['user']['username'],
				'{{url}}' => $url,
			));
			$ilance->email->send();
		}
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>