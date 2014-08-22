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
* Referral class
*
* @package      iLance\Referral
* @version      4.0.0.8059
* @author       ILance
*/
class referral
{
    /**
    * Function to print a referred by "username" using plain text mode or href url link edition.
    *
    * @param       integer        user id of the referrer
    * @param       boolean        show admin control panel url link? (default false)
    *
    * @return      string         Returns HTML representation of the username
    */
    function print_referred_by_username($refererid = 0, $acplink = false)
    {
	global $ilance, $ilconfig, $ilpage, $show;
	$html = '';
	$sql = $ilance->db->query("
		SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "referred_by, date
		FROM " . DB_PREFIX . "referral_data
		WHERE user_id = '" . intval($refererid) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
	    $res = $ilance->db->fetch_array($sql, DB_ASSOC);
	    if ($acplink)
	    {
		$html = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . intval($res['referred_by']) . '">' . fetch_user('username', $res['referred_by']) . '</a>';
	    }
	    else
	    {
		$html = fetch_user('username', $res['referred_by']);
	    }
	}
	return $html;
    }

    /**
    * Function to verify a referral clickthrough based on a supplied referral code being passed as one of the arguments
    *
    * @param	string       ip address
    * @param        string       client browser agent
    * @param        string       client referrer location (where click came from)
    * @param        string       referral code being clicked
    *
    * @return	void
    */
    function verify_referral_clickthrough($clientip = '', $clientbrowser = '', $clienturl = '', $rid = '')
    {
	global $ilance;

	$sql = $ilance->db->query("
	    SELECT rid
	    FROM " . DB_PREFIX . "referral_clickthroughs
	    WHERE rid = '" . $ilance->db->escape_string($rid) . "'
		AND ipaddress = '" . $ilance->db->escape_string($clientip) . "'
        ", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) == 0)
	{
	    $ilance->db->query("
		INSERT INTO " . DB_PREFIX . "referral_clickthroughs
		(rid, date, browser, ipaddress, referrer)
		VALUES (
		'" . $ilance->db->escape_string($rid) . "',
		'" . DATETIME24H . "',
		'" . $ilance->db->escape_string($clientbrowser) . "',
		'" . $ilance->db->escape_string($clientip) . "',
		'" . $ilance->db->escape_string($clienturl) . "')
	    ", 0, null, __FILE__, __LINE__);
	}
    }

    /**
    * Function to generate a referral code based on a limiter argument
    *
    * @param        integer       length limiter
    *
    * @return	string        Returns the formatted referral code
    */
    function create_referral_code($length = 6)
    {
	$rid = mb_substr(mb_ereg_replace("[^A-Z]", "", crypt(time())) . mb_ereg_replace("[^0-9]", "", crypt(time())) . mb_ereg_replace("[^A-Z]", "", crypt(time())), 0, $length);
	return $rid;
    }

    /**
    * Function to update a specific referred user (from a rid referral) with a particular action being taken
    *
    * @param       string         referral action type (postauction, awardauction, fvf, ins, lanceads, portfolio, credential, enhancement or subscription)
    * @param       integer        user id
    * @param       boolean        don't dispatch email on completion (default false)
    *
    * @return      nothing
    */
    function update_referral_action($type = '', $userid = 0, $dontsendemail = 0)
    {
	global $ilance, $phrase, $ilconfig, $show;
	if ($ilconfig['referalsystem_active'])
	{
	    $sql = $ilance->db->query("
		SELECT referred_by
		FROM " . DB_PREFIX . "referral_data
		WHERE user_id = '" . intval($userid) . "'
	    ", 0, null, __FILE__, __LINE__);
	    if ($ilance->db->num_rows($sql) > 0)
	    {
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		$sql2 = $ilance->db->query("
		    SELECT username
		    FROM " . DB_PREFIX . "users
		    WHERE user_id = '" . $res['referred_by'] . "'
			AND status = 'active'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql2) > 0)
		{
		    $res2 = $ilance->db->fetch_array($sql2, DB_ASSOC);
		    $username = fetch_user('username', $userid);

		    ($apihook = $ilance->api('update_referral_action_start')) ? eval($apihook) : false;

		    switch ($type)
		    {
			// #### POST AUCTION TRACKER ###############################
			case 'postauction':
			{
			    $ilance->db->query("
				UPDATE " . DB_PREFIX . "referral_data
				SET postauction = postauction + 1
				WHERE user_id = '" . intval($userid) . "'
				LIMIT 1
			    ", 0, null, __FILE__, __LINE__);
			    $event = 'The referred user (' . $username . ') (who originally referred by ' . $res2['username'] . ') posted a valid auction.';
			    break;
			}
			// #### AWARD AUCTION TRACKER ##############################
			case 'awardauction':
			{
			    $ilance->db->query("
				UPDATE " . DB_PREFIX . "referral_data
				SET awardauction = awardauction + 1
				WHERE user_id = '" . intval($userid) . "'
				LIMIT 1
			    ", 0, null, __FILE__, __LINE__);
			    $event = 'The referred user (' . $username . ') (who originally referred by ' . $res2['username'] . ') awarded a valid bid for their auction.';
			    break;
			}
			// #### FINAL VALUE FEE TRACKER ############################
			case 'fvf':
			{
			    $ilance->db->query("
				UPDATE " . DB_PREFIX . "referral_data
				SET payfvf = payfvf + 1
				WHERE user_id = '" . intval($userid) . "'
				LIMIT 1
			    ", 0, null, __FILE__, __LINE__);
			    $event = 'The referred user (' . $username . ') (who originally referred by ' . $res2['username'] . ') paid a final value commission fee.';
			    break;
			}
			// #### INSERTION FEE TRACKER ##############################
			case 'ins':
			{
			    $ilance->db->query("
				UPDATE " . DB_PREFIX . "referral_data
				SET payins = payins + 1
				WHERE user_id = '" . intval($userid) . "'
				LIMIT 1
			    ", 0, null, __FILE__, __LINE__);
			    $event = 'The referred user (' . $username . ') (who originally referred by ' . $res2['username'] . ') paid a listing insertion fee.';
			    break;
			}
			// #### FEATURE PORTFOLIO TRACKER ##########################
			case 'portfolio':
			{
			    $ilance->db->query("
				UPDATE " . DB_PREFIX . "referral_data
				SET payportfolio = payportfolio + 1
				WHERE user_id = '" . intval($userid) . "'
				LIMIT 1
			    ", 0, null, __FILE__, __LINE__);
			    $event = 'The referred user (' . $username . ') (who originally referred by ' . $res2['username'] . ') paid for featured portfolio status.';
			    break;
			}
			// #### CREDENTIAL VERIFICATION TRACKER ####################
			case 'credential':
			{
			    $ilance->db->query("
				UPDATE " . DB_PREFIX . "referral_data
				SET paycredentials = paycredentials + 1
				WHERE user_id = '" . intval($userid) . "'
				LIMIT 1
			    ", 0, null, __FILE__, __LINE__);
			    $event = 'The referred user (' . $username . ') (who originally referred by ' . $res2['username'] . ') paid to have credentials verified.';
			    break;
			}
			// #### AUCTION ENHANCEMENTS TRACKER #######################
			case 'enhancements':
			{
			    $ilance->db->query("
				UPDATE " . DB_PREFIX . "referral_data
				SET payenhancements = payenhancements + 1
				WHERE user_id = '" . intval($userid) . "'
				LIMIT 1
			    ", 0, null, __FILE__, __LINE__);
			    $event = 'The referred user (' . $username . ') (who originally referred by ' . $res2['username'] . ') paid listing enhancements for their auction.';
			    break;
			}
			// #### SUBSCRIPTION TRACKER ###############################
			case 'subscription':
			{
			    $ilance->db->query("
				UPDATE " . DB_PREFIX . "referral_data
				SET paysubscription = paysubscription + 1
				WHERE user_id = '" . intval($userid) . "'
				LIMIT 1
			    ", 0, null, __FILE__, __LINE__);
			    $event = 'The referred user (' . $username . ') (who originally referred by ' . $res2['username'] . ') paid for a valid subscription.';
			    break;
			}
		    }

		    ($apihook = $ilance->api('update_referral_action_end')) ? eval($apihook) : false;

		    // are we constructing new auction from an API call?
		    if ($dontsendemail == 0)
		    {
			// no api being used, proceed to dispatching email
			$ilance->email->mail = SITE_EMAIL;
			$ilance->email->slng = fetch_site_slng();
			$ilance->email->get('referral_payout_pending_admin');
			$ilance->email->set(array (
			    '{{username}}' => fetch_user('username', intval($userid)),
			    '{{main_referral}}' => $res2['username'],
			    '{{main_referral_id}}' => intval($userid),
			    '{{event}}' => $event,
			));
			$ilance->email->send();
		    }
		}
	    }
	}
    }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>