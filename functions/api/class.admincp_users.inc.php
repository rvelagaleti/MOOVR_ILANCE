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
* AdminCP Users class to perform the majority of functions within the ILance Admin Control Panel
*
* @package      iLance\AdminCP\Users
* @version      4.0.0.8059
* @author       ILance
*/
class admincp_users extends admincp
{
	/**
	* Function to remove single or multiple users from the marketplace
	*
	* @param	array	        array with user ids flagged for removal
	* @param        bool            remove invoices for user? (default no)
	* @param        bool            remove listings for user? (default no)
	* @param        bool            remove escrow for user? (default no)
	* @param        bool            remove buynow orders for user? (default no)
	* @param        bool            remove bids for user? (default no)
	*
	* @return       string          Returns HTML string of users removed separated by a comma for display purposes
	*/
	function remove_user($ids = array (), $removeinvoices = true, $removelistings = true, $removeescrow = true, $removebuynoworders = true, $removebids = true, $from_admin = 1)
	{
		global $ilance, $show, $ilconfig, $ilpage;
		$removedusers = '';
		$status = '{_removed}';
		foreach ($ids AS $inc => $userid)
		{
			$sql = $ilance->db->query("
				SELECT isadmin, email, username, first_name, last_name
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . intval($userid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				if ($res['isadmin'] == '1')
				{
					$sql2 = $ilance->db->query("
						SELECT user_id
						FROM " . DB_PREFIX . "users
						WHERE user_id != '" . intval($userid) . "'
						    AND isadmin = '1'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql2) == 0)
					{
						if ($from_admin == 1)
						{
							print_action_failed('{_you_cant_delete_admin_account_if_you_dont_have_any_admin_accounts_left}', $ilpage['subscribers']);
							exit();
						}
						else
						{
							print_notice('{_invalid_vendor_profile_id}', '{_you_cant_delete_admin_account_if_you_dont_have_any_admin_accounts_left}', $ilpage['main'], '{_main_menu}');
							exit();
						}
					}
				}
				$removedusers .= '<strong>' . fetch_user('username', intval($userid)) . '</strong>, ';
		
				($apihook = $ilance->api('admincp_remove_user_start')) ? eval($apihook) : false;
		
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "attachment WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "attachment_folder WHERE buyer_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "attachment_folder WHERE seller_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "audit WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "bankaccounts WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "creditcards WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "emaillog WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "invoicelog WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "messages WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "portfolio WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "register_answers WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "profile_answers WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "profile_categories WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "profile_filter_auction_answers WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "project_bid_retracts WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "project_invitations WHERE buyer_user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "project_invitations WHERE seller_user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "referral_data WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "sessions WHERE userid = '" . intval($userid) . "' AND isuser = '1'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "subscriptionlog WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_user WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_user_exempt WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "users WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "watchlist WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "watchlist WHERE watching_user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "search_users WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "search_favorites WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "sessions WHERE userid = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "skills_answers WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "calendar WHERE userid = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "feedback_ratings WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				$ilance->db->query("DELETE FROM " . DB_PREFIX . "email_optout WHERE email = '" . $ilance->db->escape_string($res['email']) . "'", 0, null, __FILE__, __LINE__);
				// invoices
				if ($removeinvoices)
				{
					$ilance->db->query("DELETE FROM " . DB_PREFIX . "invoices WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
					$ilance->db->query("DELETE FROM " . DB_PREFIX . "invoices WHERE p2b_user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				}
				// projects
				if ($removelistings)
				{
					$sql = $ilance->db->query("SELECT cid FROM " . DB_PREFIX . "projects WHERE user_id = '" . intval($userid) . "' AND STATUS = 'open'", 0, null, __FILE__, __LINE__);
					$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
					$ilance->db->query("DELETE FROM " . DB_PREFIX . "bulk_tmp WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						$res = $ilance->db->fetch_array($sql, DB_ASSOC);
						foreach ($res AS $key => $value)
						{
							$ilance->categories->build_category_count($value, 'subtract', "admin removing multiple users from admincp: subtracting increment count category id $value");
						}
					}
				}
				// projects escrow
				if ($removeescrow)
				{
					$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_escrow WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
					$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_escrow WHERE project_user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				}
				// buy now orders
				if ($removebuynoworders)
				{
					$ilance->db->query("DELETE FROM " . DB_PREFIX . "buynow_orders WHERE owner_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
					$ilance->db->query("DELETE FROM " . DB_PREFIX . "buynow_orders WHERE buyer_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				}
				// bids
				if ($removebids)
				{
					$ilance->db->query("DELETE FROM " . DB_PREFIX . "project_bids WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
					$ilance->db->query("DELETE FROM " . DB_PREFIX . "project_bid_retracts WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
					$ilance->db->query("DELETE FROM " . DB_PREFIX . "project_realtimebids WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
					$ilance->db->query("DELETE FROM " . DB_PREFIX . "proxybid WHERE user_id = '" . intval($userid) . "'", 0, null, __FILE__, __LINE__);
				}
				if ($from_admin == 1)
				{
					log_event($_SESSION['ilancedata']['user']['userid'], $ilpage['subscribers'], $ilance->GPC['subcmd'], '', $userid);
				}
				else
				{
					log_event($_SESSION['ilancedata']['user']['userid'], $ilpage['rfp'], 'remove', '', $userid);
				}
				$ilance->email->mail = $res['email'];
				$ilance->email->slng = fetch_user_slng($userid);
				$ilance->email->get('admin_changed_user_status');
				$ilance->email->set(array (
					'{{username}}' => $res['username'],
					'{{user_id}}' => $userid,
					'{{first_name}}' => $res['first_name'],
					'{{last_name}}' => $res['last_name'],
					'{{status}}' => $status
				));
				$ilance->email->send();
		
				($apihook = $ilance->api('admincp_remove_user_end')) ? eval($apihook) : false;
			}
		}
		return $removedusers;
	}
    
	/**
	* Function to suspend single or multiple users from the marketplace
	*
	* @param	array	        array with user ids flagged for suspension
	*
	* @return       string          Returns HTML string of users suspended separated by a comma for display purposes
	*/
	function suspend_user($ids = array ())
	{
		global $ilance, $show, $ilconfig, $ilpage;
		$suspendusers = '';
		$status = '{_suspended}';
		foreach ($ids AS $inc => $userid)
		{
			$sql = $ilance->db->query("
				SELECT username, email, first_name, last_name
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . intval($userid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$suspendusers .= '<strong>' . $res['username'] . '</strong>, ';
		
				// this hook is important in order to suspend various addons for this user too..
				($apihook = $ilance->api('admincp_suspend_user_start')) ? eval($apihook) : false;
		
				// suspend the user
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "users
					SET status = 'suspended'
					WHERE user_id = '" . intval($userid) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				// remove the session (in case user is logged in)
				$ilance->db->query("
					DELETE FROM " . DB_PREFIX . "sessions
					WHERE userid = '" . intval($userid) . "'
				", 0, null, __FILE__, __LINE__);
				$ilance->email->mail = $res['email'];
				$ilance->email->slng = fetch_user_slng($userid);
				$ilance->email->get('admin_changed_user_status');
				$ilance->email->set(array (
					'{{username}}' => $res['username'],
					'{{user_id}}' => $userid,
					'{{first_name}}' => $res['first_name'],
					'{{last_name}}' => $res['last_name'],
					'{{status}}' => $status
				));
				$ilance->email->send();
		
				// this hook is important in order to suspend various addons for this user too..
				($apihook = $ilance->api('admincp_suspend_user_end')) ? eval($apihook) : false;
			}
		}
		return $suspendusers;
	}
    
	/**
	* Function to unsuspend single or multiple users from the marketplace
	*
	* @param	array	        array with user ids flagged for unsuspension
	*
	* @return       string          Returns HTML string of users unsuspended separated by a comma for display purposes
	*/
	function unsuspend_user($ids = array ())
	{
		global $ilance, $show, $ilconfig, $ilpage, $phrase;
		$unsuspendusers = '';
		foreach ($ids AS $inc => $userid)
		{
			$sql = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . intval($userid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$unsuspendusers .= '<strong>' . fetch_user('username', intval($userid)) . '</strong>, ';
		
				// this hook is important in order to unsuspend various addons for this user too..
				($apihook = $ilance->api('admincp_unsuspend_user_start')) ? eval($apihook) : false;
		
				// suspend the user
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "users
					SET status = 'active'
					WHERE user_id = '" . intval($userid) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				// remove the session (in case user is logged in)
				$ilance->db->query("
					DELETE FROM " . DB_PREFIX . "sessions
					WHERE userid = '" . intval($userid) . "'
				", 0, null, __FILE__, __LINE__);
		
				// this hook is important in order to unsuspend various addons for this user too..
				($apihook = $ilance->api('admincp_unsuspend_user_end')) ? eval($apihook) : false;
			}
		}
		return $unsuspendusers;
	}
    
	/**
	* Function to activate single or multiple users from the marketplace
	*
	* @param	array	        array with user ids flagged for activation
	*
	* @return       string          Returns HTML string of users activated separated by a comma for display purposes
	*/
	function activate_user($ids = array ())
	{
		global $ilance, $show, $phrase, $ilpage, $ilconfig;
		$activatedusers = '';
		
		foreach ($ids AS $inc => $userid)
		{
			$sql = $ilance->db->query("
				SELECT status, email, username, first_name, last_name, phone
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . intval($userid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				if ($res['status'] == 'moderated')
				{
					$categories = '';
					if ($ilconfig['globalauctionsettings_productauctionsenabled'])
					{
						$getcats = $ilance->db->query("
							SELECT cid, title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title
							FROM " . DB_PREFIX . "categories
							WHERE parentid = '0'
								AND cattype = 'product'
								AND visible = '1'
							ORDER BY title_" . $_SESSION['ilancedata']['user']['slng'] . " ASC
							LIMIT 10
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($getcats) > 0)
						{
							while ($res_p = $ilance->db->fetch_array($getcats, DB_ASSOC))
							{
								$categories .= $res_p['title'] . LINEBREAK;
							}
						}
					}
					if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
					{
						$getcats = $ilance->db->query("
							SELECT cid, title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title
							FROM " . DB_PREFIX . "categories
							WHERE parentid = '0'
							    AND cattype = 'service'
							    AND visible = '1'
							ORDER BY title_" . $_SESSION['ilancedata']['user']['slng'] . " ASC
							LIMIT 10
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($getcats) > 0)
						{
							while ($res_s = $ilance->db->fetch_array($getcats, DB_ASSOC))
							{
								$categories .= $res_s['title'] . LINEBREAK;
							}
						}
					}
					// user is moderated and admin is now validating him
					$ilance->email->mail = $res['email'];
					$ilance->email->slng = fetch_user_slng($userid);
					$ilance->email->get('register_welcome_email');
					$ilance->email->set(array (
						'{{username}}' => $res['username'],
						'{{user_id}}' => $userid,
						'{{first_name}}' => $res['first_name'],
						'{{last_name}}' => $res['last_name'],
						'{{phone}}' => $res['phone'],
						'{{categories}}' => $categories
					));
					$ilance->email->send();
					// additionally, we'll run our account bonus function so this email is also dispatched
					$registerbonus = '0.00';
					if ($ilconfig['registrationupsell_bonusactive'])
					{
						// lets construct a little payment bonus for new member, we will:
						// - create a transaction and send email to user and admin
						// - return the bonus amount so we can update the users account
						$registerbonus = $ilance->accounting->construct_account_bonus($userid, 'active');
						if ($registerbonus > 0)
						{
							// update register bonus credit to online account data
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "users
								SET total_balance = total_balance + $registerbonus,
								available_balance = available_balance + $registerbonus
								WHERE user_id = '" . $userid . "'
							");
						}
					}
				}
				$activatedusers .= '<strong>' . $res['username'] . '</strong>, ';
		
				($apihook = $ilance->api('admincp_activate_user_start')) ? eval($apihook) : false;
		
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "users
					SET status = 'active'
					WHERE user_id = '" . intval($userid) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
		
				($apihook = $ilance->api('admincp_activate_user_end')) ? eval($apihook) : false;
			}
		}
		return $activatedusers;
	}
    
	/**
	* Function to ban single or multiple users from the marketplace
	*
	* @param	array	        array with user ids flagged for ban
	*
	* @return       string          Returns HTML string of users banned separated by a comma for display purposes
	*/
	function ban_user($ids = array ())
	{
		global $ilance;
		$bannedusers = '';
		$status = '{_banned}';
		
		foreach ($ids AS $inc => $userid)
		{
			$sql = $ilance->db->query("
				SELECT username, email, first_name, last_name
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . intval($userid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$bannedusers .= '<strong>' . $res['username'] . '</strong>, ';
		
				($apihook = $ilance->api('admincp_ban_user_start')) ? eval($apihook) : false;
		
				// ban this user
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "users
					SET status = 'banned'
					WHERE user_id = '" . intval($userid) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				// remove the session (in case user is logged in)
				$ilance->db->query("
					DELETE FROM " . DB_PREFIX . "sessions
					WHERE userid = '" . intval($userid) . "'
				", 0, null, __FILE__, __LINE__);
				$ilance->email->mail = $res['email'];
				$ilance->email->slng = fetch_user_slng($userid);
				$ilance->email->get('admin_changed_user_status');
				$ilance->email->set(array (
					'{{username}}' => $res['username'],
					'{{user_id}}' => $userid,
					'{{first_name}}' => $res['first_name'],
					'{{last_name}}' => $res['last_name'],
					'{{status}}' => $status
				));
				$ilance->email->send();
		
				($apihook = $ilance->api('admincp_ban_user_end')) ? eval($apihook) : false;
			}
		}
		return $bannedusers;
	}
    
	/**
	* Function to cancel status single or multiple users from the marketplace
	*
	* @param	array	        array with user ids flagged for ban
	*
	* @return       string          Returns HTML string of users banned separated by a comma for display purposes
	*/
	function cancel_user($ids = array ())
	{
		global $ilance;
		$cancelledusers = '';
		foreach ($ids AS $inc => $userid)
		{
			$sql = $ilance->db->query("
				SELECT username
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . intval($userid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$cancelledusers .= '<strong>' . $res['username'] . '</strong>, ';
		
				($apihook = $ilance->api('admincp_cancel_user_start')) ? eval($apihook) : false;
		
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "users
					SET status = 'cancelled'
					WHERE user_id = '" . intval($userid) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				// remove the session (in case user is logged in)
				$ilance->db->query("
					DELETE FROM " . DB_PREFIX . "sessions
					WHERE userid = '" . intval($userid) . "'
				", 0, null, __FILE__, __LINE__);
		
				($apihook = $ilance->api('admincp_cancel_user_end')) ? eval($apihook) : false;
			}
		}
		return $cancelledusers;
	}
    
	/**
	* Function to unverify single or multiple users from the marketplace
	*
	* @param	array	        array with user ids flagged for unverification
	*
	* @return       string          Returns HTML string of users unverified separated by a comma for display purposes
	*/
	function unverify_user($ids = array ())
	{
		global $ilance;
		$unverifiedusers = '';
		foreach ($ids AS $inc => $userid)
		{
			$sql = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . intval($userid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$unverifiedusers .= '<strong>' . fetch_user('username', intval($userid)) . '</strong>, ';
		
				($apihook = $ilance->api('admincp_unverify_user_start')) ? eval($apihook) : false;
		
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "users
					SET status = 'unverified'
					WHERE user_id = '" . intval($userid) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				// remove the session (in case user is logged in)
				$ilance->db->query("
					DELETE FROM " . DB_PREFIX . "sessions
					WHERE userid = '" . intval($userid) . "'
				", 0, null, __FILE__, __LINE__);
		
				($apihook = $ilance->api('admincp_unverify_user_end')) ? eval($apihook) : false;
			}
		}
		return $unverifiedusers;
	}
    
	/**
	* Function to create a new user.
	*
	* @param       string       username
	* @param       string       password
	* @param       string       password salt
	* @param       string       secret question
	* @param       string       secret answer
	* @param       string       email
	* @param       string       first name
	* @param       string       last name
	* @param       string       address 1
	* @param       string       address 2
	* @param       string       city
	* @param       string       state
	* @param       string       zip code
	* @param       string       phone number
	* @param       integer      country id
	* @param       string       date of birth
	* @param       string       referral code
	* @param       integer      language id
	* @param       integer      currency id
	* @param       integer      timezone id
	* @param       boolean      use dst? (default disabled)
	* @param       string       newsletter categories (default none)
	* @param       integer      is admin (default off)
	*/
	function construct_new_member($username = '', $password = '', $salt = '', $secretquestion = '', $secretanswer = '', $email = '', $first = '', $last = '', $address = '', $address2 = '', $city = '', $state = '', $zipcode = '', $phone = '', $country = '', $dob = '', $rid = '', $languageid = '', $currencyid = '', $usertimezone = '', $categories = '', $isadmin = 0)
	{
		global $ilance, $ilconfig;
		$accountbonus = 0;
		if ($ilconfig['registrationupsell_bonusactive'] AND empty($ilance->GPC['bonusdisable']))
		{
			$accountbonus = $ilconfig['registrationupsell_amount'];
		}
		$accountnumber = construct_account_number();
		$ipaddress = IPADDRESS;
		$ilance->db->query("
		    INSERT INTO " . DB_PREFIX . "users
		    (user_id, ipaddress, username, password, salt, secretquestion, secretanswer, email, first_name, last_name, address, address2, city, state, zip_code, phone, country, date_added, status, dob, rid, account_number, available_balance, total_balance, languageid, currencyid, timezone, notifyservices, notifyproducts, notifyservicescats, emailnotify, isadmin)
		    VALUES(
		    NULL,
		    '" . $ilance->db->escape_string($ipaddress) . "',
		    '" . $ilance->db->escape_string($username) . "',
		    '" . $ilance->db->escape_string($password) . "',
		    '" . $ilance->db->escape_string($salt) . "',
		    '" . $ilance->db->escape_string($secretquestion) . "',
		    '" . $ilance->db->escape_string($secretanswer) . "',
		    '" . $ilance->db->escape_string($email) . "',
		    '" . $ilance->db->escape_string($first) . "',
		    '" . $ilance->db->escape_string($last) . "',
		    '" . $ilance->db->escape_string($address) . "',
		    '" . $ilance->db->escape_string($address2) . "',
		    '" . $ilance->db->escape_string($city) . "',
		    '" . $ilance->db->escape_string($state) . "',
		    '" . $ilance->db->escape_string($zipcode) . "',
		    '" . $ilance->db->escape_string($phone) . "',
		    '" . intval($country) . "',
		    '" . DATETIME24H . "',
		    'active',
		    '" . $ilance->db->escape_string($dob) . "',
		    '" . $ilance->db->escape_string($rid) . "',
		    '" . $ilance->db->escape_string($accountnumber) . "',
		    '" . $ilance->db->escape_string($accountbonus) . "',
		    '" . $ilance->db->escape_string($accountbonus) . "',
		    '" . intval($languageid) . "',
		    '" . intval($currencyid) . "',
		    '" . $ilance->db->escape_string($usertimezone) . "',
		    '1',
		    '1',
		    '" . $ilance->db->escape_string($categories) . "',
		    '1',
		    '" . intval($isadmin) . "')
		", 0, null, __FILE__, __LINE__);
		$user_id = $ilance->db->insert_id();
		if ($accountbonus > 0)
		{
			$ilance->accounting_payment->insert_income_reported($user_id, $accountbonus, 'credit');
		}
		return $user_id;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>