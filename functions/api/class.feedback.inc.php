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
* Feedback class to perform the majority of feedback and rating functions in ILance
*
* @package      iLance\Feedback
* @version      4.0.0.8059
* @author       ILance
*/
class feedback
{
	/**
	* Function to fetch feedback criteria details for a particular user id
	*
	* @param       integer       user id
	* @param       string        short language identifier (default eng)
	*
	* @return      nothing
	*/
	function criteria($userid = 0, $slng = 'eng')
	{
		global $ilance, $show;
		$maxrows = $ratings = 0;
		if ($userid > 0)
		{
			$maxrows = 10;
			$ratings = $this->fetch_criteria_ratings_posted($userid);
		}
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "id, title_$slng AS title
			FROM " . DB_PREFIX . "feedback_criteria
			ORDER BY sort ASC
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$show['hidecriteria'] = false;
			$row_count = 0;
			while ($row = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				if ($userid > 0)
				{
					$row['average'] = $this->fetch_criteria_average($userid, $row['id'], true);
					$row['ratings'] = $this->fetch_criteria_ratings($userid, $row['id']);
				}
				$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				$criteria[] = $row;
				$row_count++;
			}
			if ($userid > 0 AND $ratings < $maxrows)
			{
				$show['hidecriteria'] = true;
			}
		}
		else
		{
			$show['hidecriteria'] = true;
		}
		return $criteria;
	}
    
	/**
	* Function to fetch feedback criteria ratings posted for a particular user id
	*
	* @param       integer       user id
	*
	* @return      integer       Returns count
	*/
	function fetch_criteria_ratings_posted($userid = 0)
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "COUNT(*) AS count
			FROM " . DB_PREFIX . "feedback_ratings
			WHERE user_id = '" . intval($userid) . "'
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			return $res['count'];
		}
		return 0;
	}
    
	/**
	* Function to fetch feedback criteria average rating for a particular user id
	*
	* @param       integer       user id
	* @param       integer       criteria id
	* @param       boolean       images? (default false)
	*
	* @return      integer       Returns average count
	*/
	function fetch_criteria_average($userid = 0, $criteriaid = 0, $images = false)
	{
		global $ilance;
		$average = 0;
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "AVG(rating) AS average
			FROM " . DB_PREFIX . "feedback_ratings
			WHERE criteria_id = '" . intval($criteriaid) . "'
				AND user_id = '" . intval($userid) . "'
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$average = number_format($res['average'], 1, '.', '');
		}
		if ($images)
		{
			$average = $this->print_feedback_stars($average);
		}
		return $average;
	}
    
	/**
	 * Function to fetch feedback criteria ratings for a particular user id
	 *
	 * @param       integer       user id
	 * @param       integer       criteria id
	 *
	 * @return      integer       Returns ratings count
	 */
	function fetch_criteria_ratings($userid = 0, $criteriaid = 0)
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "COUNT(*) AS count
			FROM " . DB_PREFIX . "feedback_ratings
			WHERE criteria_id = '" . intval($criteriaid) . "'
				AND user_id = '" . intval($userid) . "'
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			return $res['count'];
		}
		return 0;
	}
    
	/**
	* Function to fetch detailed seller rating feedback information
	*
	* @param       integer       user id
	* @param       string        short language identifier (default eng)
	*
	* @return      integer       Returns detailed seller rating
	*/
	function fetch_detailed_seller_rating($userid = 0, $slng = 'eng')
	{
		global $ilance;
		$sum = $count = 0;
		$rating = '0.00';
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "id, title_$slng AS title
			FROM " . DB_PREFIX . "feedback_criteria
			ORDER BY sort ASC
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$count = $ilance->db->num_rows($sql);
			while ($row = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$sum += $this->fetch_criteria_average($userid, $row['id'], false);
			}
			if ($this->fetch_criteria_ratings_posted($userid) >= 10)
			{
				$rating = ($sum / $count);
			}
		}
		return $rating;
	}
    
	/**
	* Function to print feedback stars
	*
	* @param       integer       feedback points
	*
	* @return      string        Returns feedback stars
	*/
	function print_feedback_stars($result = 0)
	{
		global $ilconfig;
		$result = number_format($result, 1, '.', '');
		if ($result >= $ilconfig['min_5_stars_value'] AND $result <= $ilconfig['max_5_stars_value'])
		{
			$stars = '<span title="' . $result . ' / 5.0"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_full.gif" border="0" alt="" /> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_full.gif" border="0" alt="" /> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_full.gif" border="0" alt="" /> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_full.gif" border="0" alt="" /> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_full.gif" border="0" alt="" /></span>';
		}
		else if ($result >= $ilconfig['min_4_stars_value'] AND $result <= $ilconfig['max_4_stars_value'])
		{
			$stars = '<span title="' . $result . ' / 5.0"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_full.gif" border="0" alt="" /> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_full.gif" border="0" alt="" /> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_full.gif" border="0" alt="" /> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_full.gif" border="0" alt="" /> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_empty.gif" border="0" alt="" /></span>';
		}
		else if ($result >= $ilconfig['min_3_stars_value'] AND $result <= $ilconfig['max_3_stars_value'])
		{
			$stars = '<span title="' . $result . ' / 5.0"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_full.gif" border="0" alt="" /> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_full.gif" border="0" alt="" /> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_full.gif" border="0" alt="" /> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_empty.gif" border="0" alt="" /> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_empty.gif" border="0" alt="" /></span>';
		}
		else if ($result >= $ilconfig['min_2_stars_value'] AND $result <= $ilconfig['max_2_stars_value'])
		{
			$stars = '<span title="' . $result . ' / 5.0"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_full.gif" border="0" alt="" /> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_full.gif" border="0" alt="" /> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_empty.gif" border="0" alt="" /> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_empty.gif" border="0" alt="" /> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_empty.gif" border="0" alt="" /></span>';
		}
		else if ($result >= $ilconfig['min_1_stars_value'] AND $result <= $ilconfig['max_1_stars_value'])
		{
			$stars = '<span title="' . $result . ' / 5.0"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_full.gif" border="0" alt="" /> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_empty.gif" border="0" alt="" /> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_empty.gif" border="0" alt="" /> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_empty.gif" border="0" alt="" /> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_empty.gif" border="0" alt="" /></span>';
		}
		else
		{
			$stars = '<span title="' . $result . ' / 5.0"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_empty.gif" border="0" alt="" /> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_empty.gif" border="0" alt="" /> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_empty.gif" border="0" alt="" /> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_empty.gif" border="0" alt="" /> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_empty.gif" border="0" alt="" /></span>';
		}
		return $stars;
	}
    
	/**
	* Function fetch the title for a particular feedback criteria id
	*
	* @param       integer       criteria id
	*
	* @return      string        Returns criteria title
	*/
	function fetch_title($id = 0)
	{
		global $ilance;
		$slng = $_SESSION['ilancedata']['user']['slng'];
		$html = '';
		if ($id > 0)
		{
			$html = $ilance->db->fetch_field(DB_PREFIX . "feedback_criteria", "id = '" . intval($id) . "'", "title_$slng");
		}
		return $html;
	}
    
	/**
	* Function to fetch the entire feedback details for a particular user id
	*
	* @param       integer       user id
	*
	* @return      string        Returns array with useful feedback information about user
	*/
	function datastore($userid = 0)
	{
		global $ilance, $memberinfo, $ilconfig, $phrase;
		$memberinfo['pos'] = $memberinfo['neu'] = $memberinfo['neg'] = $memberinfo['ret'] = $memberinfo['posall'] = $memberinfo['pos30'] = $memberinfo['neu30'] = $memberinfo['neg30'] = $memberinfo['ret30'] = $memberinfo['pos180'] = $memberinfo['neu180'] = $memberinfo['neg180'] = $memberinfo['ret180'] = $memberinfo['pos365'] = $memberinfo['neu365'] = $memberinfo['neg365'] = $memberinfo['ret365'] = $memberinfo['score'] = $memberinfo['pcnt'] = 0;
		$memberinfo['rating'] = $this->fetch_detailed_seller_rating($userid, $_SESSION['ilancedata']['user']['slng']);
		$ratings = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "COUNT(for_user_id) AS count, COUNT(*) AS countall, response
			FROM " . DB_PREFIX . "feedback
			WHERE for_user_id = '" . intval($userid) . "'
			GROUP BY response
		");
		while ($rating = $ilance->db->fetch_array($ratings, DB_ASSOC))
		{
			switch ($rating['response'])
			{
				case 'positive':
				{
					$memberinfo['pos'] = (int) $rating['count'];
					$memberinfo['posall'] = (int) $rating['countall'];
					break;
				}
				case 'neutral':
				{
					$memberinfo['neu'] = (int) $rating['count'];
					break;
				}
				case 'negative':
				{
					$memberinfo['neg'] = (int) $rating['count'];
					break;
				}
			}
		}
		// score - positive feedback minus negative feedback
		$memberinfo['score'] = (int) ($memberinfo['pos'] - $memberinfo['neg']);
		$all = (int) ($memberinfo['pos'] + $memberinfo['neu'] + $memberinfo['neg']);
		if ($all > 0)
		{
			@$memberinfo['pcnt'] = (1 - ($memberinfo['neg'] / $all)) * 100;
			$memberinfo['pcnt'] = number_format($memberinfo['pcnt'], 1);
		}
		// feedback: 1 month
		$ratings = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "id, TO_DAYS('" . DATETIME24H . "') - TO_DAYS(date_added) AS daysago, COUNT(*) AS countuser, response
			FROM " . DB_PREFIX . "feedback
			WHERE for_user_id = '" . intval($userid) . "'
				AND TO_DAYS('" . DATETIME24H . "') - TO_DAYS(date_added) <= 30
			GROUP BY response
		");
		while ($rating = $ilance->db->fetch_array($ratings, DB_ASSOC))
		{
			switch ($rating['response'])
			{
				case 'positive':
				{
					$memberinfo['pos30'] = (int) $rating['countuser'];
					break;
				}
				case 'neutral':
				{
					$memberinfo['neu30'] = (int) $rating['countuser'];
					break;
				}
				case 'negative':
				{
					$memberinfo['neg30'] = (int) $rating['countuser'];
					break;
				}
			}
		}
		// feedback: 6 months
		$ratings = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "id, TO_DAYS('" . DATETIME24H . "') - TO_DAYS(date_added) AS daysago, COUNT(*) AS countuser, response
			FROM " . DB_PREFIX . "feedback
			WHERE for_user_id = '" . intval($userid) . "'
				AND TO_DAYS('" . DATETIME24H . "') - TO_DAYS(date_added) <= 180
			GROUP BY response
		");
		while ($rating = $ilance->db->fetch_array($ratings, DB_ASSOC))
		{
			switch ($rating['response'])
			{
				case 'positive':
				{
					$memberinfo['pos180'] = (int) $rating['countuser'];
					break;
				}
				case 'neutral':
				{
					$memberinfo['neu180'] = (int) $rating['countuser'];
					break;
				}
				case 'negative':
				{
					$memberinfo['neg180'] = (int) $rating['countuser'];
					break;
				}
			}
		}
		// feedback: 1 year
		$ratings = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "id, TO_DAYS('" . DATETIME24H . "') - TO_DAYS(date_added) AS daysago, COUNT(*) AS countuser, response
			FROM " . DB_PREFIX . "feedback
			WHERE for_user_id = '" . intval($userid) . "'
				AND TO_DAYS('" . DATETIME24H . "') - TO_DAYS(date_added) <= 365
			GROUP BY response
		");
		while ($rating = $ilance->db->fetch_array($ratings, DB_ASSOC))
		{
			switch ($rating['response'])
			{
				case 'positive':
				{
					$memberinfo['pos365'] = (int) $rating['countuser'];
					break;
				}
				case 'neutral':
				{
					$memberinfo['neu365'] = (int) $rating['countuser'];
					break;
				}
				case 'negative':
				{
					$memberinfo['neg365'] = (int) $rating['countuser'];
					break;
				}
			}
		}
		return $memberinfo;
	}
	
	/**
	* Function to print out the feedback icon based on a set of points
	*
	* @param       integer       points
	*
	* @return      string        Returns feedback icon
	*/
	function print_feedback_icon($points = 0)
	{
		global $ilance, $ilconfig, $phrase;
		$points = intval($points);
		$sqlpoints = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "icon, pointsfrom, pointsto
			FROM " . DB_PREFIX . "stars
			WHERE ((pointsfrom <= " . intval($points) . "
				AND pointsto >= " . intval($points) . ")
					OR (pointsfrom < " . intval($points) . "
				AND pointsto < " . intval($points) . "))
			ORDER BY starid DESC
			LIMIT 1
		");
		if ($ilance->db->num_rows($sqlpoints) > 0)
		{
			$respoints = $ilance->db->fetch_array($sqlpoints, DB_ASSOC);
			return ' <span title="' . $ilance->language->construct_phrase('{_feedback_score_x_to_x_points}', array ($respoints['pointsfrom'], $respoints['pointsto'])) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/' . $respoints['icon'] . '" border="0" style="vertical-align: middle;margin-top:-5px" /></span>';
		}
		return '';
	}
    
	/**
	* Function to construct a particular user's rating
	*
	* @param       integer       user id
	*
	* @return      string       Returns
	*/
	function construct_ratings($userid = 0)
	{
		global $ilance;
		if ($userid > 0)
		{
			$memberinfo = array ();
			$memberinfo = $this->datastore($userid);
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "users
				SET rating = '" . $memberinfo['rating'] . "',
				score = '" . $memberinfo['score'] . "',
				feedback = '" . $memberinfo['pcnt'] . "'
				WHERE user_id = '" . intval($userid) . "'
			");
		}
	}
    
	/**
	* Function to print the profile pulldown menu used within the feedback menu
	*
	* @param       string        field name
	* @param       string        select name (default feedback)
	* @param       integer       user id
	* @param       integer       category id
	* @param       integer       auction listing id
	* @param       string        anchor bit (optional)
	*
	* @return      stringr       Returns HTML formatted feedback pulldown menu
	*/
	function print_profile_pulldown($fieldname = 'fb', $selectname = 'feedback', $uid, $cid, $id, $anchor = '')
	{
		global $ilance, $headinclude, $phrase, $ilpage, $ilconfig;
		$headinclude .= "<script type=\"text/javascript\">
<!--
function openURL_$fieldname()
{
    selInd = document.$fieldname.$selectname.selectedIndex;
    goURL = document.$fieldname.$selectname.options[selInd].value;
    top.location.href = goURL;
}
//-->
</script>
";
		$html = '<form name="' . $fieldname . '" method="get" accept-charset="UTF-8" style="margin:0px;"><select name="' . $selectname . '" onchange="openURL_' . $fieldname . '();" style="font-family: verdana">';
		if ($ilconfig['globalauctionsettings_seourls'])
		{
			$html .= '<option value="' . print_username($uid, 'url') . $anchor . '">{_choose_feedback_display_type}:</option>';
		}
		else
		{
			$html .= '<option value="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['members'] . '?id=' . $uid . $anchor . '">{_choose_feedback_display_type}:</option>';
		}
		if ($ilconfig['globalauctionsettings_serviceauctionsenabled'] > 0)
		{
			$type = '';
			if (isset($ilance->GPC['type']))
			{
				$type = $ilance->GPC['type'];
			}
			$html .= '<optgroup label="{_service_feedback_history}">';
			if ($ilconfig['globalauctionsettings_seourls'])
			{
				$html .= '<option value="' . print_username($uid, 'url', 0, '&amp;feedback=2&amp;type=' . $type . '&amp;cid=' . $cid . $anchor, '?feedback=2&amp;type=' . $type . '&amp;cid=' . $cid . $anchor, '') . '"';
			}
			else
			{
				$html .= '<option value="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['members'] . '?id=' . $uid . '&amp;feedback=2&type=' . $type . '&amp;cid=' . $cid . $anchor . '"';
			}
			if (isset($ilance->GPC['feedback']) AND $ilance->GPC['feedback'] == 'service' AND isset($ilance->GPC['dba']) AND $ilance->GPC['dba'] == 'provider')
			{
				$html .= ' selected="selected"';
			}
			$html .= '>{_service_feedback_history} ({_as_provider})</option>';
			if ($ilconfig['globalauctionsettings_seourls'])
			{
				$html .= '<option value="' . print_username($uid, 'url', 0, '&amp;feedback=3&amp;type=' . $type . '&amp;cid=' . $cid . $anchor, '?feedback=3&amp;type=' . $type . '&amp;cid=' . $cid . $anchor, '') . '"';
			}
			else
			{
				$html .= '<option value="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['members'] . '?id=' . $uid . '&amp;feedback=3&amp;type=' . $type . '&amp;cid=' . $cid . $anchor . '"';
			}
			if (isset($ilance->GPC['feedback']) AND $ilance->GPC['feedback'] == 'service' AND isset($ilance->GPC['dba']) AND $ilance->GPC['dba'] == 'buyer')
			{
				$html .= ' selected="selected"';
			}
			$html .= '>{_service_feedback_history} ({_as_buyer})</option>';
			$html .= '</optgroup>';
		}
		if ($ilconfig['globalauctionsettings_productauctionsenabled'] > 0)
		{
			$html .= '<optgroup label="{_product_feedback_history}">';
			if ($ilconfig['globalauctionsettings_seourls'])
			{
				$html .= '<option value="' . print_username($uid, 'url', 0, '&amp;feedback=2&amp;type=' . $type . '&amp;cid=' . $cid . $anchor, '?feedback=2&amp;type=' . $type . '&amp;cid=' . $cid . $anchor, '') . '"';
			}
			else
			{
				$html .= '<option value="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['members'] . '?id=' . $uid . '&amp;feedback=2&amp;type=' . $type . '&amp;cid=' . $cid . $anchor . '"';
			}
			if (isset($ilance->GPC['feedback']) AND $ilance->GPC['feedback'] == 'product' AND isset($ilance->GPC['dba']) AND $ilance->GPC['dba'] == 'merchant')
			{
				$html .= ' selected="selected"';
			}
			$html .= '>{_product_feedback_history} ({_as_merchant})</option>';
			if ($ilconfig['globalauctionsettings_seourls'])
			{
				$html .= '<option value="' . print_username($uid, 'url', 0, '&amp;feedback=3&amp;type=' . $type . '&amp;cid=' . $cid . $anchor, '?feedback=3&amp;type=' . $type . '&amp;cid=' . $cid . $anchor, '') . '"';
			}
			else
			{
				$html .= '<option value="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['members'] . '?id=' . $id . '&amp;feedback=3&type=' . $type . '&amp;cid=' . $cid . $anchor . '"';
			}
			if (isset($ilance->GPC['feedback']) AND $ilance->GPC['feedback'] == 'product' AND isset($ilance->GPC['dba']) AND $ilance->GPC['dba'] == 'buyer')
			{
				$html .= ' selected="selected"';
			}
			$html .= '>{_product_feedback_history} ({_as_buyer})</option>';
			$html .= '</optgroup>';
		}
		$html .= '</select></form>';
		return $html;
	}
    
	/**
	* Function to print the feedback column bit for a particular tab
	*
	* @param       integer       tab id
	*
	* @return      stringr       Returns
	*/
	function print_feedback_columnbit($tab = 1)
	{
		global $phrase;
		$html = '';
		$accepted = array (1, 2, 3, 4);
		if (in_array($tab, $accepted))
		{
			switch ($tab)
			{
				case 1:
				{
				    $html .= '{_from} / {_price}';
				    break;
				}
				case 2:
				{
				    $html .= '{_from_buyer_price}';
				    break;
				}
				case 3:
				{
				    $html .= '{_from_seller}';
				    break;
				}
				case 4:
				{
				    $html .= '{_left_for}';
				    break;
				}
			}
		}
		return $html;
	}
    
	/**
	* Function to print the feedback "from" column bit for a particular tab
	*
	* @param       integer       tab id
	*
	* @return      stringr       Returns
	*/
	function print_from_column_bit($tab = 1, $mode = 'service', $type = 'buyer')
	{
		global $ilance, $phrase, $show;
		$html = '';
		$accepted = array (1, 2, 3, 4);
		if (in_array($tab, $accepted))
		{
			switch ($tab)
			{
				// all feedback
				case 1:
				{
					if ($mode == 'product' AND $type == 'buyer')
					{
						$html .= '{_seller}: ';
					}
					else if ($mode == 'product' AND $type == 'seller')
					{
						$html .= '{_buyer}: ';
					}
					else if ($mode == 'service' AND $type == 'buyer')
					{
						$html .= '{_provider}: ';
					}
					else if ($mode == 'service' AND $type == 'seller')
					{
						$html .= '{_buyer}: ';
					}
					break;
				}
				// feedback as seller
				case 2:
				{
					$html .= '{_buyer}: ';
					break;
				}
				// feedback as buyer
				case 3:
				{
					if ($mode == 'product' AND $type == 'buyer')
					{
						$html .= '{_seller}: ';
					}
					else if ($mode == 'product' AND $type == 'seller')
					{
						$html .= '{_buyer}: ';
					}
					else if ($mode == 'service' AND $type == 'buyer')
					{
						$html .= '{_provider}: ';
					}
					else if ($mode == 'service' AND $type == 'seller')
					{
						$html .= '{_buyer}: ';
					}
					break;
				}
				// feedback left for others
				case 4:
				{
					if ($mode == 'product' AND $type == 'buyer')
					{
						$html .= '{_buyer}: ';
					}
					else if ($mode == 'product' AND $type == 'seller')
					{
						$html .= '{_seller}: ';
					}
					else if ($mode == 'service' AND $type == 'buyer')
					{
						$html .= '{_buyer}: ';
					}
					else if ($mode == 'service' AND $type == 'seller')
					{
						$html .= '{_provider}: ';
					}
					break;
				}
			}
		}
	
		($apihook = $ilance->api('feedback_print_from_column_bit_end')) ? eval($apihook) : false;
	
		return $html;
	}
    
	/**
	* Function to print out the feedback tabs used within the detailed profile menu of a particular user
	*
	* @param       integer       tab id
	*
	* @return      stringr       Returns HTML formatted tab
	*/
	function print_feedback_tabs($tab = 1, $username = '')
	{
		global $ilance, $phrase, $ilpage, $ilconfig;
		if ($ilconfig['seourls_lowercase'])
		{
			$username = mb_strtolower($username);
		}
	    $html = '<div class="bigtabs" style="padding-bottom:10px; padding-top:0px">
    <div class="bigtabsheader">
    <ul>';
		$accepted = array (1, 2, 3, 4);
		if (in_array($tab, $accepted))
		{
			switch ($tab)
			{
				case 1:
				{
					$url1 = ($ilconfig['globalauctionsettings_seourls']) ? 'javascript:void(0)' : 'javascript:void(0)';
					$url2 = ($ilconfig['globalauctionsettings_seourls']) ? HTTP_SERVER . print_seo_url($ilconfig['memberslistingidentifier']) . '/' . $username . '-feedback-2' : PHP_SELF . '&amp;feedback=2';
					$url3 = ($ilconfig['globalauctionsettings_seourls']) ? HTTP_SERVER . print_seo_url($ilconfig['memberslistingidentifier']) . '/' . $username . '-feedback-3' : PHP_SELF . '&amp;feedback=3';
					$url4 = ($ilconfig['globalauctionsettings_seourls']) ? HTTP_SERVER . print_seo_url($ilconfig['memberslistingidentifier']) . '/' . $username . '-feedback-4' : PHP_SELF . '&amp;feedback=4';
					$html .= '<li class="on"><a href="' . $url1 . '" title="{_all_feedback}">{_all_feedback}</a></li>
    <li class="" id=""><a href="' . $url2 . '" title="{_feedback_as_a_seller}">{_feedback_as_a_seller}</a></li>
    <li class="" id=""><a href="' . $url3 . '" title="{_feedback_as_a_buyer}">{_feedback_as_a_buyer}</a></li>		
    <li class="" id=""><a href="' . $url4 . '" title="{_feedback_left_for_others}">{_feedback_left_for_others}</a></li>';
					break;
				}
				case 2:
				{
					$url1 = ($ilconfig['globalauctionsettings_seourls']) ? HTTP_SERVER . print_seo_url($ilconfig['memberslistingidentifier']) . '/' . $username . '' : PHP_SELF . '&amp;feedback=1';
					$url2 = ($ilconfig['globalauctionsettings_seourls']) ? 'javascript:void(0)' : 'javascript:void(0)';
					$url3 = ($ilconfig['globalauctionsettings_seourls']) ? HTTP_SERVER . print_seo_url($ilconfig['memberslistingidentifier']) . '/' . $username . '-feedback-3' : PHP_SELF . '&amp;feedback=3';
					$url4 = ($ilconfig['globalauctionsettings_seourls']) ? HTTP_SERVER . print_seo_url($ilconfig['memberslistingidentifier']) . '/' . $username . '-feedback-4' : PHP_SELF . '&amp;feedback=4';
					$html .= '<li class="" id=""><a href="' . $url1 . '" title="{_all_feedback}">{_all_feedback}</a></li>
    <li class="on" id=""><a href="' . $url2 . '" title="{_feedback_as_a_seller}">{_feedback_as_a_seller}</a></li>
    <li class="" id=""><a href="' . $url3 . '" title="{_feedback_as_a_buyer}">{_feedback_as_a_buyer}</a></li>		
    <li class="" id=""><a href="' . $url4 . '" title="{_feedback_left_for_others}">{_feedback_left_for_others}</a></li>';
					break;
				}
				case 3:
				{
					$url1 = ($ilconfig['globalauctionsettings_seourls']) ? HTTP_SERVER . print_seo_url($ilconfig['memberslistingidentifier']) . '/' . $username . '' : PHP_SELF . '&amp;feedback=1';
					$url2 = ($ilconfig['globalauctionsettings_seourls']) ? HTTP_SERVER . print_seo_url($ilconfig['memberslistingidentifier']) . '/' . $username . '-feedback-2' : PHP_SELF . '&amp;feedback=2';
					$url3 = ($ilconfig['globalauctionsettings_seourls']) ? 'javascript:void(0)' : 'javascript:void(0)';
					$url4 = ($ilconfig['globalauctionsettings_seourls']) ? HTTP_SERVER . print_seo_url($ilconfig['memberslistingidentifier']) . '/' . $username . '-feedback-4' : PHP_SELF . '&amp;feedback=4';
					$html .= '<li class="" id=""><a href="' . $url1 . '" title="{_all_feedback}">{_all_feedback}</a></li>
    <li class="" id=""><a href="' . $url2 . '" title="{_feedback_as_a_seller}">{_feedback_as_a_seller}</a></li>
    <li class="on" id=""><a href="' . $url3 . '" title="{_feedback_as_a_buyer}">{_feedback_as_a_buyer}</a></li>		
    <li class="" id=""><a href="' . $url4 . '" title="{_feedback_left_for_others}">{_feedback_left_for_others}</a></li>';
					break;
				}
				case 4:
				{
					$url1 = ($ilconfig['globalauctionsettings_seourls']) ? HTTP_SERVER . print_seo_url($ilconfig['memberslistingidentifier']) . '/' . $username . '' : PHP_SELF . '&amp;feedback=1';
					$url2 = ($ilconfig['globalauctionsettings_seourls']) ? HTTP_SERVER . print_seo_url($ilconfig['memberslistingidentifier']) . '/' . $username . '-feedback-2' : PHP_SELF . '&amp;feedback=2';
					$url3 = ($ilconfig['globalauctionsettings_seourls']) ? HTTP_SERVER . print_seo_url($ilconfig['memberslistingidentifier']) . '/' . $username . '-feedback-3' : PHP_SELF . '&amp;feedback=3';
					$url4 = ($ilconfig['globalauctionsettings_seourls']) ? 'javascript:void(0)' : 'javascript:void(0)';
					$html .= '<li class="" id=""><a href="' . $url1 . '" title="{_all_feedback}">{_all_feedback}</a></li>
    <li class="" id=""><a href="' . $url2 . '" title="{_feedback_as_a_seller}">{_feedback_as_a_seller}</a></li>
    <li class="" id=""><a href="' . $url3 . '" title="{_feedback_as_a_buyer}">{_feedback_as_a_buyer}</a></li>		
    <li class="on" id=""><a href="' . $url4 . '" title="{_feedback_left_for_others}">{_feedback_left_for_others}</a></li>';
					break;
				}
			}
		}
		$html .= '</ul>
    </div>
    </div>
    <div style="clear:both;"></div>';
		return $html;
	}
    
	/**
	* Function to determine if a particular user has left feedback for a particular project based on a project type (service / product)
	*
	* @param       integer       feedback for user id
	* @param       integer       feedback from user id
	* @param       integer       feedback for listing id
	* @param       string        feedback type (buyer/seller)
	* @param       integer       buy now order id (if bought via buy now only) or awarded bid_id if won via auction
	*
	* @return      string       Returns true if the from user id needs to leave feedback to the for user id based on the project id
	*/
	function has_left_feedback($for_user_id = 0, $from_user_id = 0, $project_id = 0, $type = '', $orderid = 0)
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "response
			FROM " . DB_PREFIX . "feedback
			WHERE for_user_id = '" . intval($for_user_id) . "'
				AND from_user_id = '" . intval($from_user_id) . "'
				AND type = '" . $ilance->db->escape_string($type) . "'
				AND project_id = '" . intval($project_id) . "'
				" . (($orderid > 0) ? "AND buynoworderid = '" . intval($orderid) . "'" : "") . "
			LIMIT 1
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			return true;
		}
		return false;
	}
    
	/**
	* Function to determine if a feedback rating process is complete for a particular auction listing
	*
	* @param       integer       product id
	*
	* @return      stringr       Returns true if the feedback for the listing is complete, otherwise returns false
	*/
	function is_feedback_complete($project_id = 0)
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "response
			FROM " . DB_PREFIX . "feedback
			WHERE project_id = '" . intval($project_id) . "'
		");
		if ($ilance->db->num_rows($sql) == 1)
		{
			return false;
		}
		else if ($ilance->db->num_rows($sql) == 2)
		{
			return true;
		}
		return false;
	}
    
	/**
	* Function to print the latest feedback received based on a particular user
	*
	* @param        integer     user id
	* @param        string      feedback type (service/product)
	*
	* @return	string      Returns HTML representation of the latest feedback response received
	*/
	function print_latest_feedback_received($userid = 0, $feedbacktype = '', $shownone = false)
	{
		global $ilance, $ilpage, $phrase;
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "comments
			FROM " . DB_PREFIX . "feedback
			WHERE for_user_id = '" . intval($userid) . "'
			ORDER BY id DESC
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			return stripslashes(handle_input_keywords($res['comments']));
		}
		return false;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>