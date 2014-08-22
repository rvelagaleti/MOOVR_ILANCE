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
* Membership roles class for ILance.
*
* @package      iLance\Membership\Roles
* @version      4.0.0.8059
* @author       ILance
*/
class subscription_role extends subscription
{
	/**
	* Function to print a particular role title
	*
	* @param       integer        role id
	*
	* @return      string         Returns the role title
	*/
	function print_role($roleid = 0, $slng = 'eng')
	{
		global $ilance, $phrase;
		if (empty($slng))
		{
			$slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';
		}
		$sqlroles = $ilance->db->query("
			SELECT title_$slng as title
			FROM " . DB_PREFIX . "subscription_roles
			WHERE roleid = '" . intval($roleid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sqlroles) > 0)
		{
			$roles = $ilance->db->fetch_array($sqlroles, DB_ASSOC);
			return stripslashes($roles['title']);
		}
		return '{_no_role}';
	}
    
	/**
	* Function to fetch a particular role count within the subscription table
	*
	* @param       integer        role id
	*
	* @return      string         Returns the role count
	*/
	function fetch_subscription_role_count($roleid = 0)
	{
	    global $ilance, $phrase;
	    $sql = $ilance->db->query("
		SELECT COUNT(*) AS total
		FROM " . DB_PREFIX . "subscription
		WHERE roleid = '" . intval($roleid) . "'
		    AND active = 'yes'
		    AND (visible_registration != '0' OR visible_upgrade != '0')
	    ", 0, null, __FILE__, __LINE__);
	    if ($ilance->db->num_rows($sql) > 0)
	    {
		$res = $ilance->db->fetch_array($sql);
		return (int) $res['total'];
	    }
	    return 0;
	}
    
	/**
	* Function to print the role pulldown menu with selected options as the roles
	*
	* @param       string         selected role option
	* @param       bool           show "none selected" option
	* @param       bool           show role plan count beside role name
	* @param       bool           are we generating the pulldown via admincp?
	*
	* @return      string         Returns HTML representation of the role pulldown menu
	*/
	function print_role_pulldown($selected = '', $shownoneselected = 0, $showplancount = 0, $adminmode = 0, $js = '', $slng = '')
	{
		global $ilance, $phrase;
		if (empty($slng))
		{
			$slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : fetch_site_slng();
		}
		$arr = array();
		$default = $selected;
		if ($adminmode == 0)
		{
			$sql = "
				SELECT r.roleid, r.purpose_$slng as purpose, r.title_$slng as title, r.custom, r.roletype, r.roleusertype, r.active
				FROM " . DB_PREFIX . "subscription_roles r,
				" . DB_PREFIX . "subscription s
				WHERE r.roleid = s.roleid
					AND r.active = '1'
					AND s.active = 'yes'
					AND s.visible_registration = '1'
				GROUP BY r.roleid ASC
			";
		}
		else
		{
			$sql = "SELECT r.roleid, r.purpose_$slng as purpose, r.title_$slng as title, r.custom, r.roletype, r.roleusertype, r.active FROM " . DB_PREFIX . "subscription_roles r WHERE r.active = '1'";
		}
		if (isset($shownoneselected) AND $shownoneselected)
		{
			$default = '-1';
			$arr['-1'] = '{_tie_this_subscription_plan_to_a_role}:';
		}
		$sqlroles = $ilance->db->query($sql, 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sqlroles) > 0)
		{
			while ($roles = $ilance->db->fetch_array($sqlroles, DB_ASSOC))
			{
				// fetch total number of subscription plans tied to this role
				$roleattach = $this->fetch_subscription_role_count($roles['roleid']);
				if (isset($adminmode) AND $adminmode OR $roleattach > 0)
				{
					$arr[$roles['roleid']] = stripslashes($roles['title']) . ' - ' . stripslashes($roles['purpose']);
					$arr[$roles['roleid']] .= (isset($showplancount) AND $showplancount) ? ' - ' . $roleattach . ' ' . '{_subscription_plans}' : '';
				}
			}
		}
		return construct_pulldown('roleid', 'roleid', $arr, $default, 'class="select-250" ' . $js);
	}
    
	/**
	* Function to fetch a particular role id for a user
	*
	* @param       integer        user id
	*
	* @return      bool           Returns integer role id value
	*/
	function fetch_user_roleid($userid = 0)
	{
		global $ilance;
		$sqlroles = $ilance->db->query("
			SELECT roleid
			FROM " . DB_PREFIX . "subscription_user
			WHERE user_id = '" . intval($userid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sqlroles) > 0)
		{
			$roles = $ilance->db->fetch_array($sqlroles, DB_ASSOC);
			return $roles['roleid'];
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