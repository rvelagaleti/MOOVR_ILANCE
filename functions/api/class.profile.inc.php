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
* Class to handle user profiles in ILance
*
* @package      iLance\Profile
* @version      4.0.0.8059
* @author       ILance
*/
class profile
{
    function __construct()
    {
	
    }

    /**
    * Function to determine if we can display the profile of a particular user
    *
    * @param       integer        user id
    *
    * @return      string         Returns true or false
    */
    function display_profile($userid = 0)
    {
	return fetch_user('displayprofile', intval($userid));
    }

    /**
    * Function to determine if a particular user has answered any profile questions.
    *
    * @param       integer        user id
    *
    * @return      string         Returns true or false
    */
    function has_answered_profile_questions($userid = 0)
    {
	global $ilance;
	$sql = $ilance->db->query("
                SELECT questionid
                FROM " . DB_PREFIX . "profile_answers
                WHERE user_id = '" . intval($userid) . "'
        ", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
	    while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
	    {
		$sql2 = $ilance->db->query("
                                SELECT groupid
                                FROM " . DB_PREFIX . "profile_questions
                                WHERE questionid = '" . $res['questionid'] . "'
                        ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql2) > 0)
		{
		    while ($res2 = $ilance->db->fetch_array($sql2, DB_ASSOC))
		    {
			$sql3 = $ilance->db->query("
                                                SELECT cid
                                                FROM " . DB_PREFIX . "profile_groups
                                                WHERE groupid = '" . $res2['groupid'] . "'
                                                        AND canremove = '1'
                                                        AND visible = '1'
                                        ", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql3) > 0)
			{
			    return 1;
			}
		    }
		}
	    }
	}
	return 0;
    }

    /**
    * Function to print the ending javascript for the dynamic country and states pulldown menu.
    *
    * @param       integer        user id
    * @param       integer        video width (default 290px)
    * @param       integer        video height (default 240px)
    *
    * @return      string         Returns the profile video
    */
    function print_profile_video($userid = 0, $videowidth = '320', $videoheight = '240')
    {
	global $ilance, $show, $ilconfig, $phrase;
	$uniqueid = rand(1, 9999);
	$html = '';
	$profilevideourl = fetch_user('profilevideourl', $userid);
	$profilevideourl = parse_youtube_video_url($profilevideourl);
	if (!empty($profilevideourl))
	{
	    $show['profilevideo'] = true;
	    $html = '<div id="videoapplet-' . $uniqueid . '"></div>
<script type="text/javascript">
<!--
var fo = new FlashObject("' . $profilevideourl . '", "videoapplet-' . $uniqueid . '", "' . $videowidth . '", "' . $videoheight . '", "8,0,0,0", "#ffffff");
fo.addParam("movie", "' . $profilevideourl . '");
fo.addParam("quality", "high");
fo.addParam("allowScriptAccess", "sameDomain");
fo.addParam("swLiveConnect", "true");
fo.addParam("menu", "false");
fo.addParam("wmode", "transparent");
fo.write("videoapplet-' . $uniqueid . '");
//-->
</script>';
	}
	else
	{
	    $show['profilevideo'] = false;
	}
	return $html;
    }

    /**
    * Function to print out the abuse type pulldown menu letting a user select a pre-defined abuse type
    *
    * @param        string      abuse type (listing, bid, portfolio, profile)
    * @param        integer     abuse iddentifier (listing id, bid id, profile id, portfolio id, etc)
    *
    * @return	boolean     returns true or false if string is seralized
    */
    function print_abuse_type_pulldown($abusetype = 'listing', $abuseid = 0)
    {
	global $ilance, $phrase;
	$s1 = $s2 = $s3 = $s4 = $s5 = $s6 = $s7 = $t1 = $t2 = $t3 = $t4 = $t5 = $t6 = $t7 = $option1 = $option2 = $option3 = $option4 = $option5 = $option6 = $option7 = '';
	switch ($abusetype)
	{
	    case 'listing':
		{
		    $s1 = 'selected="selected"';
		    if ($abuseid > 0)
		    {
			$t1 = '({_listing_id}: ' . $abuseid . ')';
		    }
		    $option1 = '<option value="listing" ' . $s1 . '>{_listing_abuse} ' . $t1 . '</option>';
		    break;
		}
	    case 'bid':
		{
		    $s2 = 'selected="selected"';
		    if ($abuseid > 0)
		    {
			$t2 = '({_bid_id}: ' . $abuseid . ')';
		    }
		    $option2 = '<option value="bid" ' . $s2 . '>{_bidding_abuse} ' . $t2 . '</option>';
		    break;
		}
	    case 'portfolio':
		{
		    $s3 = 'selected="selected"';
		    if ($abuseid > 0)
		    {
			$t3 = '({_attachment_id}: ' . $abuseid . ')';
		    }
		    $option3 = '<option value="portfolio" ' . $s3 . '>{_portfolio_abuse} ' . $t3 . '</option>';
		    break;
		}
	    case 'profile':
		{
		    $s4 = 'selected="selected"';
		    if ($abuseid > 0)
		    {
			$t4 = '(' . fetch_user('username', $abuseid) . ')';
		    }
		    $option4 = '<option value="profile" ' . $s4 . '>{_profile_abuse} ' . $t4 . '</option>';
		    break;
		}
	    case 'feedback':
		{
		    $s5 = 'selected="selected"';
		    if ($abuseid > 0)
		    {
			$t5 = '({_feedback_id}: ' . $abuseid . ')';
		    }
		    $option5 = '<option value="feedback" ' . $s5 . '>{_feedback_abuse} ' . $t5 . '</option>';
		    break;
		}
	    case 'pmb':
		{
		    $s6 = 'selected="selected"';
		    if ($abuseid > 0)
		    {
			$t6 = '({_pmb_id}: ' . $abuseid . ')';
		    }
		    $option6 = '<option value="pmb" ' . $s6 . '>{_pmb_abuse} ' . $t6 . '</option>';
		    break;
		}
	    case 'forum':
		{
		    $s6 = 'selected="selected"';
		    if ($abuseid > 0)
		    {
			$t7 = '(Topic/Thread ID: ' . $abuseid . ')';
		    }
		    $option7 = '<option value="forum" ' . $s7 . '>Forum Topic Abuse ' . $t7 . '</option>';
		    break;
		}
	}
	$html = '<select name="abusetype" class="select-250">' . $option1 . '' . $option2 . '' . $option3 . '' . $option4 . '' . $option5 . '' . $option6 . '' . $option7 . '</select>';
	return $html;
    }

    /**
    * Function to fetch the total amount of verified credentials for a particular user within a specific category
    *
    * @param       integer        user id
    * @param       integer        category id
    *
    * @return      integer        Returns integer amount of verified credentials
    */
    function fetch_verified_credentials($userid = 0, $cid = 0)
    {
	global $ilance, $phrase, $ilpage, $ilconfig;
	$extracid = '';
	if (isset($cid) AND $cid > 0)
	{
	    $extracid = '&amp;cid=' . $cid;
	}
	$html = '-';
	$sql = $ilance->db->query("
                SELECT COUNT(isverified) AS verified
                FROM " . DB_PREFIX . "profile_questions q
                LEFT JOIN " . DB_PREFIX . "profile_answers a ON a.questionid = q.questionid
                WHERE a.user_id = '" . intval($userid) . "'
                        AND a.isverified = '1'
                        AND a.invoiceid > 0
                        AND a.verifyexpiry > '" . DATETIME24H . "'
        ", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
	    $res = $ilance->db->fetch_array($sql, DB_ASSOC);
	    if ($res['verified'] == 0)
	    {
		$html = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'not_verified.gif" border="0" alt="" id="' . intval($userid) . '_unverified" />';
	    }
	    else if ($res['verified'] == 1)
	    {
		$html = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'verified_icon.gif" border="0" alt="" id="' . intval($userid) . '_verified" />';
	    }
	    else if ($res['verified'] > 1)
	    {
		$html = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'verified_icon_stack.gif" border="0" alt="" id="' . intval($userid) . '_verified_stack" />';
	    }
	}
	return $html;
    }
    
    /**
    * Function to fetch the profile verification count for a user within a particular category.
    *
    * @param       integer      category id
    * @param       integer      user id
    *
    * @return      integer      Returns xxx
    */
    function fetch_profile_verification_count($cid = 0, $userid = 0)
    {
	global $ilance;

	$count = 0;
	$groups = $ilance->db->query("
	    SELECT groupid
	    FROM " . DB_PREFIX . "profile_groups
	    WHERE cid = '" . intval($cid) . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($groups) > 0)
	{
	    // for every group, fetch questionid's
	    while ($group = $ilance->db->fetch_array($groups))
	    {
		$questions = $ilance->db->query("
		    SELECT questionid
		    FROM " . DB_PREFIX . "profile_questions
		    WHERE groupid = '" . $group['groupid'] . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($questions) > 0)
		{
		    // for every questionid in this group, count answers
		    while ($res = $ilance->db->fetch_array($questions))
		    {
			$verified = $ilance->db->query("
			    SELECT answerid
			    FROM " . DB_PREFIX . "profile_answers
			    WHERE questionid = '" . $res['questionid'] . "'
				AND user_id = '" . intval($userid) . "'
				AND isverified = '1'
				AND visible = '1'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($verified) > 0)
			{
			    $count += 1;
			}
		    }
		}
	    }
	}
	return $count;
    }
    
    /**
    * Function to fetch the entire profile count within any category.
    *
    * @param       integer      category id
    *
    * @return      integer      Returns xxx
    */
    function fetch_profile_question_count($cid = 0)
    {
	global $ilance;

	$count = 0;
	$groups = $ilance->db->query("
	    SELECT groupid, cid
	    FROM " . DB_PREFIX . "profile_groups
	    WHERE cid = '" . intval($cid) . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($groups) > 0)
	{
	    while ($group = $ilance->db->fetch_array($groups, DB_ASSOC))
	    {
		// count questions in this profile group
		$questions = $ilance->db->query("
		    SELECT COUNT(*) AS count
		    FROM " . DB_PREFIX . "profile_questions
		    WHERE groupid = '" . $group['groupid'] . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($questions) > 0)
		{
		    $res = $ilance->db->fetch_array($questions, DB_ASSOC);
		    $count += $res['count'];
		}
	    }
	}
	return $count;
    }
    
    /**
    * Function to fetch the answer count for the profile question within a category for a particular user.
    *
    * @param       integer      category id
    * @param       integer      user id
    *
    * @return      integer      Returns xxx
    */
    function fetch_profile_answer_count($cid = 0, $userid = 0)
    {
	global $ilance;
	$count = 0;
	$groups = $ilance->db->query("
	    SELECT groupid
	    FROM " . DB_PREFIX . "profile_groups
	    WHERE cid = '" . intval($cid) . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($groups) > 0)
	{
	    // for every group, fetch questionid's
	    while ($group = $ilance->db->fetch_array($groups, DB_ASSOC))
	    {
		$questions = $ilance->db->query("
		    SELECT questionid
		    FROM " . DB_PREFIX . "profile_questions
		    WHERE groupid = '" . $group['groupid'] . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($questions) > 0)
		{
		    while ($res = $ilance->db->fetch_array($questions, DB_ASSOC))
		    {
			$answers = $ilance->db->query("
			    SELECT answerid
			    FROM " . DB_PREFIX . "profile_answers
			    WHERE questionid = '" . $res['questionid'] . "'
				AND user_id = '" . intval($userid) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($answers) > 0)
			{
			    $count += 1;
			}
		    }
		}
	    }
	}
	return $count;
    }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>