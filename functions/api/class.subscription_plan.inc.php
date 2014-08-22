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
* Subscription class to perform the majority of subscription functionality in ILance.
*
* @package      iLance\Membership\Plan
* @version      4.0.0.8059
* @author       ILance
*/
class subscription_plan extends subscription
{
	/**
	* Function to deactivate a particular subscription plan for a specific user id
	*
	* @param       string         user id
	*
	* @return      void
	*/
	function deactivate_subscription_plan($userid = 0)
	{
		global $ilance;
		$ilance->db->query("
		    UPDATE " . DB_PREFIX . "subscription_user
		    SET active = 'no'
		    WHERE user_id = '" . intval($userid) . "'
		    LIMIT 1
		", 0, null, __FILE__, __LINE__);
	}
    
	/**
	* Function to activate a particular subscription plan for a specific user id
	*
	* @param       string         user id
	* @param       string         start date
	* @param       string         renew date
	* @param       boolean        is recurring? (default false)
	* @param       integer        invoice id
	* @param       integer        subscription id
	* @param       string         payment method
	* @param       integer        role id
	* @param       string         cost
	*
	* @return      void
	*/
	function activate_subscription_plan($userid = 0, $startdate = '', $renewdate = '', $recurring = 0, $invoiceid = 0, $subscriptionid = 0, $paymethod = '', $roleid = 0, $cost = 0)
	{
		global $ilance, $ilconfig, $phrase;
		// do we already have a subscription in the database for this member?
		$sql = $ilance->db->query("
			SELECT id
			FROM " . DB_PREFIX . "subscription_user
			WHERE user_id = '" . intval($userid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			// we do ! let's change it ..
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "subscription_user
				SET active = 'yes',
				cancelled = '0',
				startdate = '" . $ilance->db->escape_string($startdate) . "',
				renewdate = '" . $ilance->db->escape_string($renewdate) . "',
				recurring = '" . intval($recurring) . "',
				roleid = '" . intval($roleid) . "',
				subscriptionid = '" . intval($subscriptionid) . "',
				invoiceid = '" . intval($invoiceid) . "',
				autopayment = '1'
				WHERE user_id = '" . intval($userid) . "'
			", 0, null, __FILE__, __LINE__);
		}
		else
		{
			// we will create a new subscription for this user
			$ilance->db->query("
				INSERT INTO " . DB_PREFIX . "subscription_user
				(id, subscriptionid, user_id, paymethod, startdate, renewdate, autopayment, active, cancelled, recurring, invoiceid, roleid)
				VALUES(
				NULL,
				'" . intval($subscriptionid) . "',
				'" . intval($userid) . "',
				'" . $ilance->db->escape_string($paymethod) . "',
				'" . $ilance->db->escape_string($startdate) . "',
				'" . $ilance->db->escape_string($renewdate) . "',
				'1',
				'yes',
				'0',
				'" . intval($recurring) . "',
				'" . intval($invoiceid) . "',
				'" . intval($roleid) . "')
			", 0, null, __FILE__, __LINE__);
		}
		$existing = array (
			'{{provider}}' => fetch_user('username', intval($userid)),
			'{{invoice_id}}' => intval($invoiceid),
			'{{invoice_amount}}' => $ilance->currency->format($cost),
			'{{paymethod}}' => $paymethod,
			'{{startdate}}' => $startdate,
			'{{renewdate}}' => $renewdate,
			'{{subscriptionid}}' => $subscriptionid,
			'{{roleid}}' => $roleid,
		);
		$ilance->email->mail = SITE_EMAIL;
		$ilance->email->slng = fetch_site_slng();
		$ilance->email->get('subscription_paid_via_paypal_admin');
		$ilance->email->set($existing);
		$ilance->email->send();
		$ilance->email->mail = fetch_user('email', intval($userid));
		$ilance->email->slng = fetch_user_slng(intval($userid));
		$ilance->email->get('subscription_paid_via_paypal');
		$ilance->email->set($existing);
		$ilance->email->send();
	}
    
	/**
	* Function to cancel a particular subscription plan for a specific user id
	*
	* @param       integer        user id
	* @param       integer        invoice id (optional)
	* @param       string         payment gateway (optional)
	*
	* @return      void
	*/
	function cancel_subscription_plan($userid = 0, $invoiceid = 0, $paymentgateway = '')
	{
		global $ilance, $ilconfig, $phrase;
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "subscription_user
			SET cancelled = '1',
			autopayment = '0',
			recurring = '0',
			active = 'no',
			renewdate = '" . DATETIME24H . "'
			WHERE user_id = '" . intval($userid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilconfig['authnet_enabled'] AND isset($invoiceid) AND $invoiceid > 0 AND !empty($paymentgateway) AND ($paymentgateway == 'authnet' OR $paymentgateway == 'bluepay'))
		{
			$ilance->authorizenet = construct_object('api.authorizenet', $ilance->GPC);
			$ilance->authorizenet->error_email = SITE_EMAIL;
			$ilance->authorizenet->timeout = 120;
			$subscriptionId = $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . $invoiceid . "'", "custommessage");
			$data['subscriptionId'] = $subscriptionId;
			// #### build our special cancellation recurring subscription xml data
			$xml = $ilance->authorizenet->build_recurring_subscription_xml('cancel', $data, $paymentgateway);
			$method = 'curl'; // curl or fsockopen can be used
			unset($data);
			// #### post and fetch gateway response ################################
			if ($xml != '')
			{
				$gatewayresponse = $ilance->authorizenet->send_response($method, $xml, 'https://api.authorize.net', '/xml/v1/request.api');
				if ($gatewayresponse != '')
				{
					$refId = $resultCode = $code = $text = '';
					list($refId, $resultCode, $code, $text, $subscriptionId) = $ilance->authorizenet->parse_return($gatewayresponse);
					if (strtolower($resultCode) == 'ok')
					{
						// #### COMPLETED!!
					}
					else
					{
						    $ilance->authorizenet->error_out('Warning: ' . $paymentgateway . ' subscription cancellation gateway response: resultcode: ' . $resultCode . ', code: ' . $code . ', text: ' . $text . ', subscriptionId: ' . $subscriptionId);
						    return false;
					}
				}
				else
				{
					$ilance->authorizenet->error_out('Warning: could not communicate with ' . $paymentgateway . ' (no gateway response) to cancel subscription via PHP function: ' . $method . ' in functions.php (try curl or fsockopen)');
					return false;
				}
			}
			else
			{
				$ilance->authorizenet->error_out('Warning: function build_recurring_subscription_xml() could not construct a valid xml response in functions.php to cancel recurring subscription payment at merchant gateway (' . $paymentgateway . ')');
				return false;
			}
		}
		$existing = array (
			'{{user}}' => $_SESSION['ilancedata']['user']['username'],
			'{{comment}}' => $ilance->GPC['comment']
		);
		$ilance->email->mail = $_SESSION['ilancedata']['user']['email'];
		$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
		$ilance->email->get('member_cancelled_subscription');
		$ilance->email->set($existing);
		$ilance->email->send();
		$ilance->email->mail = SITE_EMAIL;
		$ilance->email->slng = fetch_site_slng();
		$ilance->email->get('member_cancelled_subscription_admin');
		$ilance->email->set($existing);
		$ilance->email->send();
		return true;
	}
    
	/**
	* Function to determine if a user's subscription is cancelled based on a supplied user id
	*
	* @param       string         user id
	*
	* @return      boolean        Returns true if cancelled, false if not
	*/
	function is_subscription_cancelled($userid = 0)
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT cancelled
			FROM " . DB_PREFIX . "subscription_user
			WHERE user_id = '" . intval($userid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			return intval($res['cancelled']);
		}
		return 0;
	}
    
	/**
	* Function to determine if a subscription plan's permission is setup or not
	*
	* @param       string         subscription id
	*
	* @return      boolean        Returns true if ready, false if not
	*/
	function is_subscription_permissions_ready($subscriptiongroupid = 0)
	{
		global $ilance, $ilconfig;
		// make sure this function supports older versions of ILance
		if ($ilconfig['current_version'] >= '3.1.4')
		{
			$table = DB_PREFIX . "subscription_permissions";
		}
		else
		{
			$table = DB_PREFIX . "subscription_group_titles";
		}
		$sql = $ilance->db->query("
			SELECT COUNT(*) AS permissioncount
			FROM $table
			WHERE subscriptiongroupid = '" . intval($subscriptiongroupid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql);
			if ($res['permissioncount'] > 0)
			{
				return true;
			}
		}
		return false;
	}
    
	/**
	* Function to add valid subscription permissions into the subscription datastore
	* 
	* @param       string         access text
	* @param       string         access text description
	* @param       string         access name
	* @param       string         access type
	* @param       string         access default value
	* @param       boolean        can access permission be removed? (default true)
	* @param       boolean        is original framework access? (default true)
	*
	* @return      boolean        Returns true or false
	*/
	function add_subscription_permissions($accesstext = '', $accessdescription = '', $accessname = '', $accesstype = '', $value = '', $canremove = 1)
	{
		global $ilance, $ilconfig;
		$sql = $ilance->db->query("
			SELECT id
			FROM " . DB_PREFIX . "subscription_permissions
			WHERE accessname = '" . $ilance->db->escape_string($accessname) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			return false;
		}
		else
		{
			$sqlcreate = $ilance->db->query("
				SELECT subscriptiongroupid, title, description, canremove
				FROM " . DB_PREFIX . "subscription_group
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sqlcreate) > 0)
			{
				while ($res = $ilance->db->fetch_array($sqlcreate, DB_ASSOC))
				{
					if ($ilance->subscription_plan->is_subscription_permissions_ready($res['subscriptiongroupid']))
					{
						$ilance->db->query("
							INSERT INTO " . DB_PREFIX . "subscription_permissions
							(id, subscriptiongroupid, accessname, accesstype, value, canremove, original, iscustom, visible)
							VALUES(
							NULL,
							'" . $res['subscriptiongroupid'] . "',
							'" . $ilance->db->escape_string($accessname) . "',
							'" . $ilance->db->escape_string($accesstype) . "',
							'" . $ilance->db->escape_string($value) . "',
							'" . $ilance->db->escape_string($canremove) . "',
							'1',
							'0',
							'1')
						", 0, null, __FILE__, __LINE__);
					}
				}
				return 1;
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