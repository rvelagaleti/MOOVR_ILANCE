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
* Permissions class to perform the majority of permissions functionality in ILance.
*
* @package      iLance\Permissions
* @version      4.0.0.8059
* @author       ILance
*/
class permissions
{
	/**
	* Function used to check a user's access when trying to access a certain marketplace resource or area.
	*
	* @param       integer        user id
	* @param       string         access name
	*
	* @return      bool           Returns true or false if boolean setting or will return
	*                             the actual "value" if other (ie: bid limit per day might return 10)..
	*/
	function check_access($userid = 0, $accessname = '')
	{
		global $ilance;
		$value = 'no';
		$userid = isset($userid) ? intval($userid) : 0;
		if ($userid > 0 AND !empty($accessname))
		{
			$sql = $ilance->db->query("
				SELECT user.subscriptionid, user.user_id, sub.subscriptiongroupid, perm.value
				FROM " . DB_PREFIX . "subscription_user user
				LEFT JOIN " . DB_PREFIX . "subscription sub ON (sub.subscriptionid = user.subscriptionid)
				LEFT JOIN " . DB_PREFIX . "subscription_permissions perm ON (perm.subscriptiongroupid = sub.subscriptiongroupid)
				WHERE user.user_id = '" . intval($userid) . "'
					AND sub.active = 'yes'
					AND user.cancelled = '0'
					AND user.active = 'yes'
					AND perm.subscriptiongroupid = sub.subscriptiongroupid
					AND perm.accessname = '" . $ilance->db->escape_string($accessname) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				// does admin force a permission exemption?
				$sql2 = $ilance->db->query("
					SELECT value
					FROM " . DB_PREFIX . "subscription_user_exempt
					WHERE user_id = '" . intval($userid) . "' 
						AND accessname = '" . $ilance->db->escape_string($accessname) . "'
						AND active = '1'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql2) > 0)
				{
					$res2 = $ilance->db->fetch_array($sql2, DB_ASSOC);
					if ($accessname == 'bidlimitperday')
					{
						// allows admin to offer bidder extra bids on a per (day/month) basis
						$value = ($res['value'] + $res2['value']);
					}
					else
					{
						$value = $res2['value'];
					}
				}
				// if there is no exemption for this user fpr this permission resource
				else
				{
					$value = $res['value'];
				}
			}
		}
		return $value;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>