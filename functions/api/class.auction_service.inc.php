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
* Auction class to perform the majority of functions dealing with anything to do with auctions within ILance.
*
* @package      iLance\Auction\Service
* @version      4.0.0.8059
* @author       ILance
*/
class auction_service extends auction
{
    function fetch_job_history_info($userid = 0, $cid = 0)
    {
	global $ilance, $ilconfig;
	$memberinfo = array ();
	$memberinfo = $ilance->feedback->datastore(intval($userid));
	$repeatinfo = $this->fetch_total_repeat_clients_info($userid);
	$earnings = $ilance->accounting_print->print_income_reported($userid, true);
	$earningsaverage = (($earnings > 0 AND $repeatinfo['clients'] > 0) ? $ilance->currency->format($earnings / $repeatinfo['clients']) : 0);
	$array = array (
	    'jobs' => $this->fetch_service_bids_awarded($userid),
	    'milestones' => $this->fetch_total_milestones($userid),
	    'hours' => $this->fetch_total_milestone_hours($userid),
	    'rating' => $ilance->feedback->print_feedback_stars($memberinfo['rating']),
	    'reviews' => $this->fetch_service_reviews_reported($userid),
	    'scorepercent' => $memberinfo['pcnt'] . '%',
	    'clients' => number_format($repeatinfo['clients']),
	    'repeatclientspercent' => '<span title="' . $repeatinfo['repeatclients'] . ' {_repeat}">' . $repeatinfo['repeatclientspercent'] . '%</span>',
	    'earnings' => ($this->can_display_financials($userid) ? $ilance->currency->format($earnings, $ilconfig['globalserverlocale_defaultcurrency']) : '{_private}'),
	    'earningsaverage' => ($this->can_display_financials($userid) ? $earningsaverage : '{_private}'),
	);
	return $array;
    }

    function fetch_job_categories_link_menu($userid = 0, $selected = 0)
    {
	global $ilance, $phrase, $ilconfig, $ilpage;
	$html = '<div style="padding-top:3px;font-size:14px;font-weight:bold;padding-bottom:20px;padding-top:9px"><a href="javascript:void(0)" onmouseover="show_actions_popup(\'' . $userid . '\')" onmouseout="hide_actions_popup(\'' . $userid . '\')">{_all_categories}</a></div>
<div style="display:none;position:absolute;z-index:5000;background-color:#fff;margin-top:-16px;margin-left:0px;background-color:#fff;border:1px solid #ccc;-webkit-box-shadow: #ddd 3px 3px 6px;-moz-box-shadow: #ddd 3px 3px 6px" id="actions_popup_' . $userid . '" onmouseover="show_actions_popup_links(\'' . $userid . '\');" onmouseout="hide_actions_popup(\'' . $userid . '\');"><div class="n"><div class="e"><div class="w"></div></div></div><div>
<table border="0" cellpadding="9" cellspacing="0">

<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div>Website Design &amp; Development</div></td>
</tr>
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div>Programming</div></td>
</tr>';

	$html .= '</table></div><div class="s"><div class="e"><div class="w"></div></div></div>';
	return $html;
    }

    /**
     * Function to fetch the request for proposal budget amount for a particular project
     *
     * @param       integer       project id
     *
     * @return      string        HTML representation of the duration pulldown menu
     */
    function fetch_rfp_budget($projectid = 0, $showicon = false)
    {
	global $ilance, $phrase, $ilconfig;
	$html = '';
	$sql = $ilance->db->query("
		SELECT budgetgroup, filter_budget, filtered_budgetid
		FROM " . DB_PREFIX . "projects
		WHERE project_id = '" . intval($projectid) . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
	    $res = $ilance->db->fetch_array($sql, DB_ASSOC);
	    if ($res['filter_budget'] > 0 AND $res['filtered_budgetid'] > 0)
	    {
		// buyer is filtering budget via specific range
		$sql2 = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "budget
				WHERE budgetid = '" . $res['filtered_budgetid'] . "'
			", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql2) > 0)
		{
		    $res2 = $ilance->db->fetch_array($sql2, DB_ASSOC);
		    if ($res2['budgetto'] == '-1')
		    {
			$html = stripslashes($res2['title']) . ' (' . $ilance->currency->format($res2['budgetfrom']) . ' {_or_more})';
		    }
		    else
		    {
			$html = stripslashes($res2['title']) . ' (' . $ilance->currency->format($res2['budgetfrom']) . ' - ' . $ilance->currency->format($res2['budgetto']) . ')';
		    }
		}
		else
		{
		    $html .= '{_non_disclosed}';
		}
	    }
	    else
	    {
		$html = '{_non_disclosed}';
		if ($showicon)
		{
		    $html .= ' <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/nondisclosed.gif" border="0" alt="{_non_disclosed_budget}" />';
		}
	    }
	}
	return $html;
    }

    /**
     * Function to fetch and return an array with buyer facts such as jobs posted, awarded and the overall award ratio percentage
     *
     * @param      integer      user id
     * @param      string       mode (service or product)
     *
     * @return     array        Mixed array of amounts requested
     */
    function fetch_buyer_facts($user_id = 0, $mode = 'service')
    {
	global $ilance, $ilconfig, $show, $phrase;
	$jobsposted = $jobsdelisted = $jobsawarded = $awardratio = 0;
	$sql = $ilance->db->query("
		SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "COUNT(*) AS jobsposted
		FROM " . DB_PREFIX . "projects
		WHERE user_id = '" . intval($user_id) . "'
			AND project_state = 'service'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
	    $res = $ilance->db->fetch_array($sql, DB_ASSOC);
	    $jobsposted = $res['jobsposted'];
	}
	$sql = $ilance->db->query("
		SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "COUNT(*) AS jobsdelisted
		FROM " . DB_PREFIX . "projects
		WHERE project_state = 'service'
			AND user_id = '" . intval($user_id) . "'
			AND status = 'delisted'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
	    $res = $ilance->db->fetch_array($sql, DB_ASSOC);
	    $jobsdelisted = $res['jobsdelisted'];
	}
	$sql = $ilance->db->query("
		SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "COUNT(*) AS jobsawarded
		FROM " . DB_PREFIX . "project_bids
		WHERE state = 'service'
			AND project_user_id = '" . intval($user_id) . "'
			AND bidstatus = 'awarded'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
	    $res = $ilance->db->fetch_array($sql, DB_ASSOC);
	    $jobsawarded = $res['jobsawarded'];
	}
	if ($jobsposted > 0 AND $jobsawarded > 0)
	{
	    $awardratio = number_format(($jobsawarded / $jobsposted) * 100, 1);
	}
	return array (
	    'jobsposted' => $jobsposted,
	    'jobsawarded' => $jobsawarded,
	    'jobsdelisted' => $jobsdelisted,
	    'awardratio' => $awardratio
	);
    }

    /**
     * Function to print the total number of service feedback reviews for this particular user
     *
     * @param        integer       user id
     *
     * @return	string        Returns number of service feedback reviews reported
     */
    function fetch_service_reviews_reported($userid = 0)
    {
	global $ilance;
	$sql = $ilance->db->query("
                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "COUNT(*) AS reviewcount
                FROM " . DB_PREFIX . "feedback
                WHERE for_user_id = '" . intval($userid) . "'
                        AND cattype = 'service'
        ", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
	    $res = $ilance->db->fetch_array($sql, DB_ASSOC);
	    return $res['reviewcount'];
	}
	return 0;
    }

    /**
     * Function to print the total number of service auction bid proposals awarded for this particular user
     *
     * @param        integer       user id
     * @param        bool          force an update right now?
     *
     * @return	string        Returns number of service bids awarded
     */
    function fetch_service_bids_awarded($userid = 0, $doupdate = false)
    {
	global $ilance;
	$sql = $ilance->db->query("
                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "COUNT(*) AS bidsawarded
                FROM " . DB_PREFIX . "project_bids
                WHERE user_id = '" . intval($userid) . "'
                    AND bidstatus = 'awarded'
                    AND state = 'service'
        ", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
	    $res = $ilance->db->fetch_array($sql, DB_ASSOC);
	    if ($doupdate)
	    {
		$ilance->db->query("
                                UPDATE " . DB_PREFIX . "users
                                SET serviceawards = '" . $res['bidsawarded'] . "'
                                WHERE user_id = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
	    }
	    return $res['bidsawarded'];
	}
	return 0;
    }
    
    function fetch_provider_facts($userid = 0)//unused
    {
	    global $ilance, $phrase, $ilconfig, $ilpage;
	    $memberinfo = array();
	    $memberinfo = $ilance->feedback->datastore(intval($userid));
	    $repeatinfo = $this->fetch_total_repeat_clients_info($userid);
	    $earnings = $ilance->accounting_print->print_income_reported($userid, true);
	    $earningsaverage = (($earnings > 0 AND $repeatinfo['clients'] > 0) ? $ilance->currency->format($earnings / $repeatinfo['clients']) : 0);
	    $pattern = str_replace('[fbscore]', $memberinfo['score'], $pattern);
	    $pattern = str_replace('[fbpercent]', '<a href="' . print_username(intval($userid), 'url', 0, '', '') . '" title="' . '{_total_positive_feedback_percentile}' . '">' . $memberinfo['pcnt'] . '%</a>', $pattern);
	    $pattern = str_replace('[rating]', '<a href="' . print_username(intval($userid), 'url', 0, '', '') . '" title="' . '{_total_feedback_rating_out_of_500}' . '">' . $memberinfo['rating'] . '</a>', $pattern);
	    $pattern = str_replace('[stars]', $ilance->feedback->print_feedback_icon($memberinfo['score']), $pattern);
	    $pattern = str_replace('[store]', '', $pattern);
	    $pattern = str_replace('[verified]', '', $pattern);
	    $pattern = str_replace('[subscription]', $ilance->subscription->print_subscription_icon(intval($userid)), $pattern);
	    $facts = array();
	    $facts['jobs'] = $this->fetch_service_bids_awarded($userid);
	    $facts['milestones'] = $this->fetch_total_milestones($userid);
	    $facts['hours'] = $this->fetch_total_milestone_hours($userid);
	    $facts['rating'] = $memberinfo['rating'];
	    $facts['stars'] = $ilance->feedback->print_feedback_icon($memberinfo['score']);
	    $facts['reviews'] = $this->fetch_service_reviews_reported($userid);
	    $facts['scorepercent'] = $memberinfo['pcnt'] . '%';
	    $facts['clients'] = number_format($repeatinfo['clients']);
	    $facts['repeatclientspercent'] = '<span title="' . $repeatinfo['repeatclients'] . ' repeat clients">' . $repeatinfo['repeatclientspercent'] . '%</span>';
	    $facts['earnings'] = ($this->can_display_financials($userid) ? $ilance->currency->format($earnings, $ilconfig['globalserverlocale_defaultcurrency']) : '{_private}');
	    $facts['earningsaverage'] = ($this->can_display_financials($userid) ? $earningsaverage : '{_private}');
	    return $facts;
    }

	
	/**
        * Function to relist service auction
        *
        * @return      nothing
        */
	function relist_service_auction($id = 0)//unused
	{
		global $ilance , $ilconfig;
		$rfpid = $ilance->auction_rfp->construct_new_auctionid_bulk();
		$sql = $ilance->db->query("
		        SELECT p.*
		        FROM " . DB_PREFIX . "projects p
		        WHERE p.project_id = '" . intval($id) . "'
	        ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$ilance->GPC = array_merge($ilance->GPC, $ilance->db->fetch_array($sql, DB_ASSOC));
			$this->rewrite_photos($id, $rfpid);
			$enhancements = array();
			$promo = array('bold', 'featured', 'highlite', 'autorelist');
			foreach($promo AS $key)
			{
				if (isset($ilance->GPC[$key]) AND $ilance->GPC[$key] == '1')
				{
					if ($key == 'highlite')
					{
						$enhancements['highlite'] = '1';
					}
					else
					{
						$enhancements[$key] = $ilance->GPC[$key];
					}
				}
			}
			$duration = strtotime($ilance->GPC['date_end']) - strtotime($ilance->GPC['date_starts']);
			$duration_unit = 'D';
			if ($duration / 60 > 0 AND $duration / 60 <= 30)
			{
				$duration_unit = 'M';
				$duration = $duration / 60;
			}
			else if ($duration / 3600 > 0 AND $duration / 3600 <= 30)
			{
				$duration_unit = 'H';
				$duration = $duration / 3600;
			}
			else if ($duration / 86400 > 0 AND $duration / 86400 <= 30)
			{
				$duration_unit = 'D';
				$duration = $duration / 86400;
			}
			$sql_quest = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "project_answers
				WHERE project_id = '" . intval($id) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_quest) > 0)
			{
				while ($res_quest = $ilance->db->fetch_array($sql_quest, DB_ASSOC))
				{
					$ilance->db->query("
						INSERT INTO " . DB_PREFIX . "project_answers
						(answerid, questionid, project_id, answer, date, visible)
						VALUES(
						NULL,
						'" . intval($res_quest['questionid']) . "',
						'" . intval($rfpid) . "',
						'" . $ilance->db->escape_string($res_quest['answer']) . "',
						'" . DATETIME24H . "',
						'1')
					", 0, null, __FILE__, __LINE__);  
				}
			}
			$ilance->GPC['filtered_auctiontype'] = 'regular';
			$ilance->GPC['filter_bidlimit'] = isset($ilance->GPC['filter_bidlimit']) ? $ilance->GPC['filter_bidlimit'] : '';
			$ilance->GPC['filtered_bidlimit'] = isset($ilance->GPC['filtered_bidlimit']) ? intval($ilance->GPC['filtered_bidlimit']) : '0';
			$ilance->GPC['filter_rating'] = isset($ilance->GPC['filter_rating']) ? intval($ilance->GPC['filter_rating']) : '0';
			$ilance->GPC['filtered_rating'] = isset($ilance->GPC['filtered_rating']) ? $ilance->GPC['filtered_rating'] : '';
			$ilance->GPC['filter_country'] = isset($ilance->GPC['filter_country']) ? intval($ilance->GPC['filter_country']) : '0';
			$ilance->GPC['filtered_country'] = isset($ilance->GPC['filtered_country']) ? $ilance->GPC['filtered_country'] : '';
			$ilance->GPC['filter_state'] = isset($ilance->GPC['filter_state']) ? intval($ilance->GPC['filter_state']) : '0';
			$ilance->GPC['filtered_state'] = isset($ilance->GPC['filtered_state']) ? $ilance->GPC['filtered_state'] : '';
			$ilance->GPC['filter_city'] = isset($ilance->GPC['filter_city']) ? intval($ilance->GPC['filter_city']) : '0';
			$ilance->GPC['filtered_city'] = isset($ilance->GPC['filtered_city']) ? $ilance->GPC['filtered_city'] : '';
			$ilance->GPC['filter_zip'] = isset($ilance->GPC['filter_zip']) ? intval($ilance->GPC['filter_zip']) : '0';
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
			$ilance->GPC['filter_bidtype'] = isset($ilance->GPC['filter_bidtype']) ? $ilance->GPC['filter_bidtype'] : '0';
			$ilance->GPC['filtered_bidtype'] = isset($ilance->GPC['filtered_bidtype']) ? $ilance->GPC['filtered_bidtype'] : 'entire';
			$ilance->GPC['description_videourl'] = isset($ilance->GPC['description_videourl']) ? strip_tags($ilance->GPC['description_videourl']) : '';
			$ilance->GPC['project_type'] = 'reverse';
			$ilance->GPC['status'] = 'open';
			$ilance->GPC['draft'] = '0';
			if (isset($ilance->GPC['saveasdraft']) AND $ilance->GPC['saveasdraft'])
			{
				$ilance->GPC['draft'] = '1';
				$ilance->GPC['status'] = 'draft';
			}
			if ($ilance->GPC['filter_budget'] == 0)
			{
				$ilance->GPC['filtered_budgetid'] = 0;
			}
			$ilance->GPC['custom'] = (!empty($ilance->GPC['custom']) ? $ilance->GPC['custom'] : array());
			$ilance->GPC['pa'] = (!empty($ilance->GPC['pa']) ? $ilance->GPC['pa'] : array());
			$ilance->GPC['enhancements'] = (!empty($ilance->GPC['enhancements']) ? $ilance->GPC['enhancements'] : array());
			$ilance->GPC['year'] = (isset($ilance->GPC['year'])) ? $ilance->GPC['year'] : '';
			$ilance->GPC['month'] = (isset($ilance->GPC['month'])) ? $ilance->GPC['month'] : '';
			$ilance->GPC['day'] = (isset($ilance->GPC['day'])) ? $ilance->GPC['day'] : '';
			$ilance->GPC['hour'] = (isset($ilance->GPC['hour'])) ? $ilance->GPC['hour'] : '';
			$ilance->GPC['min'] = (isset($ilance->GPC['min'])) ? $ilance->GPC['min'] : '';
			$ilance->GPC['sec'] = (isset($ilance->GPC['sec'])) ? $ilance->GPC['sec'] : '';
			$ilance->GPC['city'] = (isset($ilance->GPC['city'])) ? $ilance->GPC['city'] : $_SESSION['ilancedata']['user']['city'];
			$ilance->GPC['state'] = (isset($ilance->GPC['state'])) ? $ilance->GPC['state'] : $_SESSION['ilancedata']['user']['state'];
			$ilance->GPC['zipcode'] = (isset($ilance->GPC['zipcode'])) ? $ilance->GPC['zipcode'] : $_SESSION['ilancedata']['user']['postalzip'];
			$ilance->GPC['country'] = (isset($ilance->GPC['country'])) ? $ilance->GPC['country'] : $_SESSION['ilancedata']['user']['country'];
			$ilance->GPC['currencyid'] = (isset($ilance->GPC['currencyid'])) ? intval($ilance->GPC['currencyid']) : $ilconfig['globalserverlocale_defaultcurrency'];
			$ilance->GPC['invitedmember'] = isset($ilance->GPC['invitedmember']) ? $ilance->GPC['invitedmember'] : array();
			$invitesql = $ilance->db->query("
				SELECT invite_message, email, name
				FROM " . DB_PREFIX . "project_invitations
				WHERE project_id = '".intval($id)."'
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
			else
			{
				$ilance->GPC['invitelist']['email'][] = '';
				$ilance->GPC['invitelist']['name'][] = '';
				$ilance->GPC['invitemessage'] = '';
			}
			$apihookcustom = array();

			($apihook = $ilance->api('buying_submit_end')) ? eval($apihook) : false;

			// #### CREATE AUCTION #################################
			$ilance->auction_rfp->insert_service_auction(
				$_SESSION['ilancedata']['user']['userid'],
				$ilance->GPC['project_type'],
				$ilance->GPC['status'],
				$ilance->GPC['project_state'],
				$ilance->GPC['cid'],
				$rfpid,
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
				$duration,
				$duration_unit,
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
				$skipemailprocess = 1,
				$apihookcustom,
				$isbulkupload = false,
				$ilance->GPC['currencyid']
			);
		}
	}
	
	function fetch_total_milestones($userid = 0)
	{
		global $ilance;
		$count = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "COUNT(*) AS milestones
			FROM " . DB_PREFIX . "projects_milestones
			WHERE user_id = '" . intval($userid) . "'
		");
		$res = $ilance->db->fetch_array($count, DB_ASSOC);
		return (int)$res['milestones'];
	}

	function fetch_total_milestone_hours($userid = 0)
	{
		global $ilance;
		$count = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "SUM(totalhours) AS hours
			FROM " . DB_PREFIX . "projects_milestones
			WHERE user_id = '" . intval($userid) . "'
		");
		$res = $ilance->db->fetch_array($count, DB_ASSOC);
		return (int)$res['hours'];
	}
	
	function fetch_total_repeat_clients_info($userid = 0)
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "repeat_customers, customer_count, repeat_customers / customer_count AS repeat_percentage
			FROM (SELECT COUNT(counter) AS repeat_customers FROM (SELECT 1 AS counter FROM " . DB_PREFIX. "project_bids WHERE user_id = '" . intval($userid) . "' AND bidstatus = 'awarded' AND state = 'service' GROUP by project_user_id HAVING COUNT(*) > 1) AS dt) AS dt2  
			CROSS JOIN (SELECT COUNT(*) AS customer_count FROM " . DB_PREFIX. "project_bids WHERE user_id = '" . intval($userid) . "' AND bidstatus = 'awarded' AND state = 'service') AS dt3
		");
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		return array('repeatclientspercent' => number_format($res['repeat_percentage'] * 100, 1), 'clients' => $res['customer_count'], 'repeatclients' => $res['repeat_customers']);
	}
	
	/**
	* Function to determine if we can display the financials for a particular user (if they allow it from their profile menu)
	*
	* @param       integer        user id
	*
	* @return      bool           Returns true or false
	*/
	function can_display_financials($userid = 0)
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "displayfinancials
			FROM " . DB_PREFIX . "users
			WHERE user_id = '" . intval($userid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			return $res['displayfinancials'];
		}
		return 0;
	}
	
	/**
	* Function to determine if a particular auction event has sealed bidding enabled
	* 
	* @param       integer        project id
	*
	* @return      boolean        Returns true or false
	*/
	function is_sealed_auction($projectid = 0)
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT bid_details
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . intval($projectid) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			if ($res['bid_details'] != 'open')
			{
				return 1;
			}
		}
		return 0;
	}

}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>