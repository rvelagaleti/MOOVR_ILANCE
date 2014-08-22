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
* Registration class to perform the majority of registration handling tasks
*
* @package      iLance\Registration
* @version      4.0.0.8059
* @author       ILance
*/
class registration
{
        /**
        * Function for creating a valid ILance member using the registration datastore.
        *
        * @param       array        user information
        * @param       array        user preferences information
        * @param       array        user subscription information
        * @param       array        custom registration questions and answers
        * @param       string       tells this function what data to return when completed: return_userid OR return_userstatus OR return_userarray
        * @param       bool         tells this function if it should skip sessions (for api calls from other applications if required)
        * @param       string       tells the site the exact source where the user registering is from (api calls like Facebook apis, external applications, etc)
        *
        * @return      mixed        returns integers, strings and arrays
        */
        function build_user_datastore(&$user, &$preferences, &$subscription, &$questions, $custom = 'return_userarray', $skipsessions = 0, $registersource = '{_direct_registration}')
        {
                global $ilance, $ilconfig, $ilpage, $phrase, $show;
                if (!empty($user) AND is_array($user))
                {
                        // #### password logic #################################
                        if (!empty($user['password_md5']) AND !empty($user['salt']))
                        {
                                // we are sending an already salted md5 password ready to store in the database
                                $user['password'] = $user['password_md5'];
                        }
                        else
                        {
                                if (empty($user['salt']) AND !empty($user['password']))
                                {
                                        // no salt found! just a clear text password! encode password!
                                        $user['salt'] = construct_password_salt(5);
                                        $user['password'] = md5(md5($user['password']) . $user['salt']);
                                }
                                else if (!empty($user['salt']) AND !empty($user['password']))
                                {
                                        // clear text password and salt found! encode password!
                                        $user['password'] = md5(md5($user['password']) . $user['salt']);
                                }
                        }
                        if (empty($user['address2']))
                        {
                                $user['address2'] = '';
                        }
                        if (empty($user['dob']))
                        {
                                $user['dob'] = '0000-00-00';
                        }
                        if ($ilconfig['registrationdisplay_emailverification'])
                        {
                                $user['status'] = 'unverified';
                        }
                        else
                        {
                                if ($ilconfig['registrationdisplay_moderation'])
                                {
                                        $user['status'] = 'moderated';
                                }
                                else
                                {
                                        $user['status'] = 'active';
                                }
                        }
                        if (empty($user['ipaddress']))
                        {
                                $user['ipaddress'] = IPADDRESS;
                        }
                        if (empty($user['roleid']))
                        {
                                // DEV NOTE: should be changed to search and find any available roles...
                                $user['roleid'] = '1';    
                        }
                        if (empty($user['state']))
                        {
                                $user['state'] = $ilconfig['registrationdisplay_defaultstate'];
                        }
                        if (empty($user['city']))
                        {
                                $user['city'] = $ilconfig['registrationdisplay_defaultcity'];
                        }
                        if (empty($user['country']))
                        {
                                $user['country'] = $ilconfig['registrationdisplay_defaultcountry'];
				$user['countryid'] = fetch_country_id($user['country']);
                        }
                        if (file_exists(DIR_CORE . 'functions_geoip.php') AND file_exists(DIR_CORE . 'functions_geoip_city.dat') AND file_exists(DIR_CORE . 'functions_geoip_city.dat'))
                        {
                                if (!function_exists('geoip_open'))
                                {
                                        require_once(DIR_CORE . 'functions_geoip.php');
                                }
                                $geoip = geoip_open(DIR_CORE . 'functions_geoip_city.dat', GEOIP_STANDARD);
                                $geo = geoip_record_by_addr($geoip, IPADDRESS);
                                $user['country'] = ((!empty($geo->country_name) AND $geo->country_name != '') ? $geo->country_name : $user['country']);
                                $user['countryid'] = ((!empty($geo->country_code) AND $geo->country_code != '') ? $ilance->common_location->fetch_country_id_by_code($geo->country_code) : $user['countryid']);
                                $user['state'] = ((!empty($GEOIP_REGION_NAME[$geo->country_code][$geo->region]) AND $GEOIP_REGION_NAME[$geo->country_code][$geo->region] != '') ? $GEOIP_REGION_NAME[$geo->country_code][$geo->region] : $user['state']);
                                $user['city'] = ((!empty($geo->city) AND $geo->city != '') ? $geo->city : $user['city']);
                                geoip_close($geoip);
                        }
                        if (empty($user['ridcode']))
                        {
                                $user['ridcode'] = $ilance->referral->create_referral_code(6);
                        }
                        if (!empty($preferences['companyname']))
                        {
                                $preferences['usecompanyname'] = '1';
                        }
                        else
                        {
                                $preferences['usecompanyname'] = '0';
                        }
                        $user['gender'] = ((isset($user['gender']) AND $user['gender'] != '') ? $user['gender'] : '');
                        if (!empty($user['username']) AND !empty($user['password']) AND !empty($user['salt']) AND !empty($user['secretquestion']) AND !empty($user['secretanswer']) AND !empty($user['email']) AND !empty($user['firstname']) AND !empty($user['lastname']) AND !empty($user['address']) AND !empty($user['city']) AND !empty($user['state']) AND !empty($user['zipcode']) AND !empty($user['phone']) AND !empty($user['countryid']))
                        {
                                if (!is_array($preferences) OR count($preferences) < 5)
                                {
                                        // set defaults
                                        $preferences['languageid'] = $user['languageid'];
                                        $preferences['currencyid'] = $ilance->currency->fetch_default_currencyid();
                                        $preferences['usertimezone'] = $this->fetch_default_timezone();
                                        $preferences['notifyservices'] = $preferences['notifyproducts'] = $preferences['usecompanyname'] = '0';
                                        $preferences['notifyservicescats'] = $preferences['notifyproductscats'] = $preferences['companyname'] = '';
                                }
                                $ilance->db->query("
                                        INSERT INTO " . DB_PREFIX . "users
                                        (user_id, ipaddress, username, password, salt, secretquestion, secretanswer, email, first_name, last_name, address, address2, city, state, zip_code, phone, country, date_added, status, lastseen, dob, rid, styleid, languageid, currencyid, timezone, notifyservices, notifyproducts, notifyservicescats, notifyproductscats, displayprofile, emailnotify, companyname, usecompanyname,gender)
                                        VALUES(
                                        NULL,
                                        '" . $ilance->db->escape_string($user['ipaddress']) . "',
                                        '" . $ilance->db->escape_string($user['username']) . "',
                                        '" . $ilance->db->escape_string($user['password']) . "',
                                        '" . $ilance->db->escape_string($user['salt']) . "',
                                        '" . $ilance->db->escape_string($user['secretquestion']) . "',
                                        '" . $ilance->db->escape_string(md5($user['secretanswer'])) . "',
                                        '" . $ilance->db->escape_string($user['email']) . "',
                                        '" . $ilance->db->escape_string($user['firstname']) . "',
                                        '" . $ilance->db->escape_string($user['lastname']) . "',
                                        '" . $ilance->db->escape_string($user['address']) . "',
                                        '" . $ilance->db->escape_string($user['address2']) . "',
                                        '" . $ilance->db->escape_string($user['city']) . "',
                                        '" . $ilance->db->escape_string($user['state']) . "',
                                        '" . $ilance->db->escape_string($user['zipcode']) . "',
                                        '" . $ilance->db->escape_string($user['phone']) . "',
                                        '" . intval($user['countryid']) . "',
                                        '" . DATETIME24H . "',
                                        '" . $ilance->db->escape_string($user['status']) . "',
                                        '" . DATETIME24H . "',
                                        '" . $ilance->db->escape_string($user['dob']) . "',
                                        '" . $ilance->db->escape_string($user['ridcode']) . "',
                                        '" . intval($user['styleid']) . "',
                                        '" . intval($preferences['languageid']) . "',
                                        '" . intval($preferences['currencyid']) . "',
                                        '" . $ilance->db->escape_string($preferences['usertimezone']) . "',
                                        '" . intval($preferences['notifyservices']) . "',
                                        '" . intval($preferences['notifyproducts']) . "',
                                        '" . $ilance->db->escape_string($preferences['notifyservicescats']) . "',
                                        '" . $ilance->db->escape_string($preferences['notifyproductscats']) . "',
                                        '1',
                                        '1',
                                        '" . $ilance->db->escape_string($preferences['companyname']) . "',
                                        '" . intval($preferences['usecompanyname']) . "',
					'" . $ilance->db->escape_string($user['gender']) . "')
                                ", 0, null, __FILE__, __LINE__);
                                $member['userid'] = $ilance->db->insert_id();
                                
                                ($apihook = $ilance->api('build_user_datastore_create_user_end')) ? eval($apihook) : false;
                                
                                if (isset($user['new_redirect']) AND !empty($user['new_redirect']))
                                {
                                        $member['redirect'] = $user['new_redirect'];
                                }
                        }
                        else
                        {
                                // one or more elements within the $user array is missing
                                return false;
                        }
                }
                else
                {
                        return false;
                }
                if (!empty($member['userid']) AND $member['userid'] > 0)
                {
                        // ##### BUILD SUBSCRIPTION ############################
                        if (!is_array($subscription))
                        {
                                // set default elements
                                // DEV NOTE: should be changed to search and find any available roles...
                                $subscription['subscriptionid'] = '1';
                                $subscription['subscriptionpaymethod'] = 'account';
                                $subscription['promocode'] = '';
                        }
                        // referral check: anyone referring this new member to register?
                        if ($ilconfig['referalsystem_active'] AND !empty($_COOKIE[COOKIE_PREFIX . 'rid']))
                        {
                                $this->referral_check($member['userid'], $_COOKIE[COOKIE_PREFIX . 'rid']);
                        }
                        // obtain subscription plan information
                        $sqlplan = $ilance->db->query("
                                SELECT subscriptionid, title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title, description_" . $_SESSION['ilancedata']['user']['slng'] . " AS description, cost, length, units, subscriptiongroupid, roleid, active, canremove, visible_registration, visible_upgrade, icon, migrateto, migratelogic
                                FROM " . DB_PREFIX . "subscription
                                WHERE subscriptionid = '" . intval($subscription['subscriptionid']) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sqlplan) > 0)
                        {
                                $subscription_plan_result = $ilance->db->fetch_array($sqlplan, DB_ASSOC);
                        }
                        $sqlcurrencies = $ilance->db->query("
                                SELECT currency_id, currency_abbrev, currency_name, rate, time, isdefault, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places
                                FROM " . DB_PREFIX . "currency
                                WHERE currency_id = '" . intval($preferences['currencyid']) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sqlcurrencies) > 0)
                        {
                                $res_currencies = $ilance->db->fetch_array($sqlcurrencies, DB_ASSOC);
                        }
                        unset($sqlplan, $sqlcurrencies);
                        // construct full member session
                        if ($skipsessions == 0)
                        {
                                $user['browseragent'] = USERAGENT;
                                $user['languagecode'] = $ilance->language->print_language_code($_SESSION['ilancedata']['user']['languageid']);
                                $user['slng'] = $_SESSION['ilancedata']['user']['slng'];
                                $user['styleid'] = $_SESSION['ilancedata']['user']['styleid'];
                                // if the status of this user is active, admin is disabling new user email verifications
                                if ($user['status'] == 'active')
                                {
                                        // globalize user
                                        $_SESSION['ilancedata'] = array(
                                                'user' => array(
                                                        // carry over session details
                                                        'agreeterms' => 1,
                                                        'browseragent' => $user['browseragent'],
                                                        'languagecode' => $user['languagecode'],
                                                        'slng' => $user['slng'],
                                                        'styleid' => $user['styleid'],
                                                        'status' => $user['status'],
                                                        'userid' => intval($member['userid']),
                                                        'username' => $user['username'],
                                                        'password' => $user['password'],
                                                        'salt' => $user['salt'],
                                                        'email' => $user['email'],
                                                        'phone' => $user['phone'],
                                                        'firstname' => $user['firstname'],
                                                        'lastname' => $user['lastname'],
                                                        'fullname' => $user['firstname'] . ' ' . $user['lastname'],
                                                        'address' => ucwords($user['address']),
                                                        'address2' => ucwords($user['address2']),
                                                        'fulladdress' => ucwords($user['address']) . ' ' . ucwords($user['address2']),
                                                        'city' => ucwords($user['city']),
                                                        'state' => ucwords($user['state']),
                                                        'postalzip' => mb_strtoupper(trim($user['zipcode'])),
                                                        'zipcode' => mb_strtoupper(trim($user['zipcode'])),
                                                        'country' => ucwords($user['country']),
                                                        'countryid' => intval($user['countryid']),
                                                        'lastseen' => DATETIME24H,
                                                        'ipaddress' => $user['ipaddress'],
                                                        'iprestrict' => 0,
                                                        'auctiondelists' => 0,
                                                        'bidretracts' => 0,
                                                        'ridcode' => $user['ridcode'],
                                                        'dob' => $user['dob'],
                                                        'serviceawards' => 0,
                                                        'productawards' => 0,
                                                        'servicesold' => 0,
                                                        'productsold' => 0,
                                                        'rating' => 0,
                                                        'languageid' => intval($preferences['languageid']),
                                                        'timezone' => $preferences['usertimezone'],
                                                        'distance' => 1,
                                                        'emailnotify' => 1,
                                                        'companyname' => stripslashes($preferences['companyname']),
                                                        'roleid' => $user['roleid'],
                                                        'subscriptionid' => intval($subscription['subscriptionid']),
                                                        'active' => 'no',
                                                        'currencyid' => intval($preferences['currencyid']),
                                                        'currencyname' => stripslashes($res_currencies['currency_name']),
                                                        'currencysymbol' => $ilance->currency->currencies[$preferences['currencyid']]['symbol_left'],
                                                        'currencyabbrev' => mb_strtoupper($res_currencies['currency_abbrev']),
                                                        'token' => TOKEN,
                                                        'siteid' => SITE_ID,
                                        		'isadmin' => 0,
                                                        'csrf' => md5(uniqid(mt_rand(), true))
                                                )
                                        );
                                }
                                else
                                {
                                        // admin requires new members to verify their emails
                                        $_SESSION['ilancedata'] = array(
                                                'user' => array(
                                                        'agreeterms' => 1,
                                                        'browseragent' => $user['browseragent'],
                                                        'languagecode' => $user['languagecode'],
                                                        'slng' => $user['slng'],
                                                        'styleid' => $user['styleid'],
                                                        'status' => $user['status'],
                                                        'username' => $user['username'],
                                                        'password' => $user['password'],
                                                        'salt' => $user['salt'],
                                                        'email' => $user['email'],
                                                        'phone' => $user['phone'],
                                                        'firstname' => $user['firstname'],
                                                        'lastname' => $user['lastname'],
                                                        'fullname' => $user['firstname'] . ' ' . $user['lastname'],
                                                        'address' => ucwords($user['address']),
                                                        'address2' => ucwords($user['address2']),
                                                        'fulladdress' => ucwords($user['address']) . ' ' . ucwords($user['address2']),
                                                        'city' => ucwords($user['city']),
                                                        'state' => ucwords($user['state']),
                                                        'postalzip' => mb_strtoupper(trim($user['zipcode'])),
                                                        'zipcode' => mb_strtoupper(trim($user['zipcode'])),
                                                        'country' => ucwords($user['country']),
                                                        'countryid' => intval($user['countryid']),
                                                        'lastseen' => DATETIME24H,
                                                        'ipaddress' => $user['ipaddress'],
                                                        'iprestrict' => 0,
                                                        'auctiondelists' => 0,
                                                        'bidretracts' => 0,
                                                        'ridcode' => $user['ridcode'],
                                                        'dob' => $user['dob'],
                                                        'serviceawards' => 0,
                                                        'productawards' => 0,
                                                        'servicesold' => 0,
                                                        'productsold' => 0,
                                                        'rating' => 0,
                                                        'languageid' => intval($preferences['languageid']),
                                                        'timezone' => $preferences['usertimezone'],
                                                        'distance' => 1,
                                                        'emailnotify' => 1,
                                                        'companyname' => stripslashes($preferences['companyname']),
                                                        'roleid' => $user['roleid'],
                                                        'subscriptionid' => intval($subscription['subscriptionid']),
                                                        'active' => 'no',
                                                        'currencyid' => intval($preferences['currencyid']),
                                                        'currencyname' => stripslashes($res_currencies['currency_name']),
                                                        'currencysymbol' => $ilance->currency->currencies[$preferences['currencyid']]['symbol_left'],
                                                        'currencyabbrev' => mb_strtoupper($res_currencies['currency_abbrev']),
                                                        'token' => TOKEN,
                                                        'siteid' => SITE_ID,
                                        		'isadmin' => 0,
                                                        'csrf' => md5(uniqid(mt_rand(), true))
                                                )
                                        );
                                }
                                
                                ($apihook = $ilance->api('build_user_datastore_create_session_end')) ? eval($apihook) : false;
                        }
                        // does admin permit new account registration bonuses?
                        $preferences['registerbonus'] = '0.00';
                        if ($ilconfig['registrationupsell_bonusactive'])
                        {
                                // lets construct a little payment bonus for new member
                                // if user status is active, this function will create a transaction and send bonus email to user
                                // if user is unverified, this function will create a transaction and will not send bonus email to user
                                // `-- it will only send bonus email when this user finally verifies his account via email link code
                                //     `-- OR if user is moderated, the admin cp will verify and send email to new user
                                $preferences['registerbonus'] = $ilance->accounting->construct_account_bonus($member['userid'], $user['status']);
                        }
                        // create account data
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "users
                                SET account_number = '" . $ilance->db->escape_string($ilconfig['globalserversettings_accountsabbrev']) . $this->construct_account_number() . "',
                                available_balance = '" . $ilance->db->escape_string($preferences['registerbonus']) . "',
                                total_balance = '" . $ilance->db->escape_string($preferences['registerbonus']) . "',
                                income_reported = '" . $ilance->db->escape_string($preferences['registerbonus']) . "',
                                income_spent = '0.00'
                                WHERE user_id = '" . intval($member['userid']) . "'
                        ");
                        // build users membership
                        $this->build_user_subscription($member['userid'], $subscription['subscriptionid'], $subscription['subscriptionpaymethod'], $subscription['promocode'], $user['roleid']);
                        // tie invited auction to new user id!
                        $this->build_invitation_datastore($member['userid'], $user['email']);
                        // build users registration questions
                        if (!empty($questions))
                        {
                                $this->build_registration_questions($questions, $member['userid']);
                        }
                        // #### SEND WELCOME EMAIL #############################
                        if ($user['status'] == 'active')
                        {
                                $categories = '';			
                                if ($ilconfig['globalauctionsettings_productauctionsenabled'])
                                {
                                        $getcats = $ilance->db->query("
                                                SELECT cid, title_" . $user['slng'] . " AS title
                                                FROM " . DB_PREFIX . "categories
                                                WHERE parentid = '0'
                                                        AND cattype = 'product'
                                                        AND visible = '1'
                                                ORDER BY title_" . $user['slng'] . " ASC
                                                LIMIT 10
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($getcats) > 0)
                                        {
                                                while ($res = $ilance->db->fetch_array($getcats, DB_ASSOC))
                                                {
                                                        $categories .= "$res[title]\n";
                                                }
                                        }
                                }
                                if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
                                {
                                        $getcats = $ilance->db->query("
                                                SELECT cid, title_" . $user['slng'] . " AS title
                                                FROM " . DB_PREFIX . "categories
                                                WHERE parentid = '0'
                                                        AND cattype = 'service'
                                                        AND visible = '1'
                                                ORDER BY title_" . $user['slng'] . " ASC
                                                LIMIT 10
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($getcats) > 0)
                                        {
                                                while ($res = $ilance->db->fetch_array($getcats, DB_ASSOC))
                                                {
                                                        $categories .= "$res[title]\n";
                                                }
                                        }
                                }
                                // geoip backend
				$geoipcity = $geoipcountry = $geoipstate = $geoipzip = '';
				if (file_exists(DIR_CORE . 'functions_geoip.php') AND file_exists(DIR_CORE . 'functions_geoip_city.dat') AND file_exists(DIR_CORE . 'functions_geoip_city.dat'))
				{
                                        if (!function_exists('geoip_open'))
					{
						require_once(DIR_CORE . 'functions_geoip.php');
					}
					$geoip = geoip_open(DIR_CORE . 'functions_geoip_city.dat', GEOIP_STANDARD);
					$geo = geoip_record_by_addr($geoip, IPADDRESS);
					$geoipcity = (!empty($geo->city) ? $geo->city : '-');
					$geoipcountry = (!empty($geo->country_name) ? $geo->country_name : '-');
					$geoipstate = (!empty($geo->region) ? (!empty($GEOIP_REGION_NAME[$geo->country_code][$geo->region]) ? $GEOIP_REGION_NAME[$geo->country_code][$geo->region] : '-') : '-');
					$geoipzip = (!empty($geo->postal_code) ? $geo->postal_code : '-');
				}
                                $ilance->email->mail = $user['email'];
                                $ilance->email->slng = $user['slng'];
                                $ilance->email->get('register_welcome_email');		
                                $ilance->email->set(array(
                                        '{{username}}' => $user['username'],
                                        '{{user_id}}' => $member['userid'],
                                        '{{first_name}}' => $user['firstname'],
                                        '{{last_name}}' => $user['lastname'],
                                        '{{phone}}' => $user['phone'],
                                        '{{categories}}' => $categories
                                ));
                                $ilance->email->send();
                                $ilance->email->mail = SITE_EMAIL;
                                $ilance->email->slng = fetch_site_slng();
                                $ilance->email->get('register_welcome_email_admin');		
                                $ilance->email->set(array(
                                        '{{username}}' => $user['username'],
                                        '{{user_id}}' => $member['userid'],
                                        '{{first_name}}' => $user['firstname'],
                                        '{{last_name}}' => $user['lastname'],
                                        '{{phone}}' => $user['phone'],
                                        '{{emailaddress}}' => $user['email'],
                                        '{{ipaddress}}' => IPADDRESS,
                                        '{{country}}' => $geoipcountry,
                                        '{{city}}' => $geoipcity,
                                        '{{state}}' => $geoipstate,
                                        '{{zipcode}}' => $geoipzip
                                ));
                                $ilance->email->send();
                        }
                        else
                        {
                                if ($user['status'] == 'unverified')
                                {
                                        // send link code verification email
					if (isset($member['redirect']))
                                        {
                                                $this->send_email_activation_with_redirect_link($user['email'], $member['redirect']);
                                        }
                                        else
                                        {										
                                                $this->send_email_activation($user['email']);
					}
                                }
                                else if ($user['status'] == 'moderated')
                                {
                                        $ilance->email->mail = $user['email'];
                                        $ilance->email->slng = $user['slng'];
                                        $ilance->email->get('register_moderation_email');		
                                        $ilance->email->set(array());
                                        $ilance->email->send();
                                }
                        }
                        // you may define, create, update or alter any information you see fit within
                        // this area.  By the time the code reaches this point, the new member would
                        // have been created within the database, new subscription account setup
                        // and preferences all good to go!
                        ($apihook = $ilance->api('registration_end')) ? eval($apihook) : false;
                }
                else
                {
                        return false;
                }
                // handle custom arguments to send valid response back
                if (!empty($custom))
                {
                        switch ($custom)
                        {
                                // let's return the new member ID to the script
                                case 'return_userid':
                                {
                                        return intval($member['userid']);
                                        break;
                                }                                
                                // let's return the new member user / login status
                                case 'return_userstatus':
                                {
                                        return $user['status'];
                                        break;
                                }                                
                                // let's return the new member array
                                case 'return_userarray':
                                {
                                        $user['userid'] = intval($member['userid']);
                                        if (isset($member['redirect']) AND !empty($member['redirect']))
                                        {
                                                $user['redirect'] = $member['redirect'];
                                        }
                                        return $user;
                                        break;
                                }
                        }
                }
        }
    
        /**
        * Function to insert custom registration questions into the database based on formame (key) and answer (value).
        *
        * @param       array        question formname (key) and answer (value)
        * @param       integer      user id
        *
        * @return      nothing
        */
        function build_registration_questions(&$questions, $userid)
        {
                global $ilance;
                if (!empty($questions) AND is_array($questions))
                {
                        foreach ($questions AS $formname => $answer)
                        {
                                if (isset($formname) AND isset($answer))
                                {
                                        if (is_array($answer))
                                        {
                                                // multiple choice
                                                $answer = serialize($answer);
                                        }
                                        $ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "register_answers
                                                (answerid, questionid, user_id, answer, date, visible)
                                                VALUES(
                                                NULL,
                                                '" . intval($this->fetch_formname_questionid($formname)) . "',
                                                '" . intval($userid) . "',
                                                '" . $ilance->db->escape_string($answer) . "',
                                                '" . DATETIME24H . "',
                                                '1')
                                        ", 0, null, __FILE__, __LINE__);                    
                                }
                        }        
                }
        }
        
        /**
        * Function for dispatching the activation email to new clients.
        *
        * @param       string       user email address
        *
        * @return      nothing
        */
        function send_email_activation($useremail)
        {
                global $ilance, $ilconfig, $phrase, $ilpage;
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "users
                        WHERE email = '" . $ilance->db->escape_string($useremail) . "'
                                AND status = 'unverified'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        $member['userid'] = $res['user_id'];
                        $link = HTTP_SERVER . $ilpage['registration'] . '?cmd=activate&u=' . $ilance->crypt->three_layer_encrypt($member['userid'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']);
                        $ilance->email->mail = $res['email'];
                        $ilance->email->slng = fetch_user_slng($res['user_id']);
                        $ilance->email->get('registration_email');		
                        $ilance->email->set(array(
                                '{{username}}' => $res['username'],
                                '{{user_id}}' => $res['user_id'],
                                '{{first_name}}' => $res['first_name'],
                                '{{last_name}}' => $res['last_name'],
                                '{{phone}}' => $res['phone'],
                                '{{http_server}}' => HTTP_SERVER,
                                '{{site_name}}' => SITE_NAME,
                                '{{staff}}' => SITE_EMAIL,
                                '{{link}}' => $link,
                        ));
                        $ilance->email->send();
                        return true;
                }
                else
                {
                        return false;
                }
        }
		
	/**
        * Function for dispatching the activation email to new clients.
        *
        * @param       string       user email address
        *
        * @return      nothing
        */
        function send_email_activation_with_redirect_link($useremail,$redirect)
        {
                global $ilance, $ilconfig, $phrase, $ilpage;
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "users
                        WHERE email = '" . $ilance->db->escape_string($useremail) . "'
                            AND status = 'unverified'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        $member['userid'] = $res['user_id'];
                        $link = (isset($redirect)) ? HTTP_SERVER . $ilpage['registration'] . '?redirect=' . urlencode($redirect) . '&cmd=activate&u=' . $ilance->crypt->three_layer_encrypt($member['userid'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']) : HTTP_SERVER . $ilpage['registration'] . '?cmd=activate&u=' . $ilance->crypt->three_layer_encrypt($member['userid'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']);
                        $ilance->email->mail = $res['email'];
                        $ilance->email->slng = fetch_user_slng($res['user_id']);
                        $ilance->email->get('registration_email');		
                        $ilance->email->set(array(
                                '{{username}}' => $res['username'],
                                '{{user_id}}' => $res['user_id'],
                                '{{first_name}}' => $res['first_name'],
                                '{{last_name}}' => $res['last_name'],
                                '{{phone}}' => $res['phone'],
                                '{{http_server}}' => HTTP_SERVER,
                                '{{site_name}}' => SITE_NAME,
                                '{{staff}}' => SITE_EMAIL,
                                '{{link}}' => $link,
                        ));
                        $ilance->email->send();
                        return true;
                }
                else
                {
                        return false;
                }
        }
    
        /**
        * Function for fetching the question id based on a formname question.
        *
        * @param       string       name of the form field
        *
        * @return      integer      question id number
        */
        function fetch_formname_questionid($formname)
        {
                global $ilance;
                $sql = $ilance->db->query("
                        SELECT questionid
                        FROM " . DB_PREFIX . "register_questions
                        WHERE formname = '" . $ilance->db->escape_string($formname) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        return intval($res['questionid']);
                }
                return 0;
        }
    
        /**
        * Function for creating a new user subscription plan.
        *
        * @param       integer      user id
        * @param       integer      subscription id
        * @param       string       payment method (account, paypal, cashu, moneybookers, etc)
        * @param       string       promotional code
        * @param       integer      subscription role id
        * @param       boolean      skip session functionality (maybe calling from external script)
        *
        * @return      nothing
        */
        function build_user_subscription($userid = 0, $subscriptionid = 0, $paymethod = 'account', $promocode = '', $roleid = '-1', $skipsession = 0)
        {
                global $ilance, $phrase, $ilconfig, $ilpage;
                if (empty($roleid))
                {
                        $roleid = (!empty($ilance->GPC['roleid']) AND $ilance->GPC['roleid'] > 0) ? intval($ilance->GPC['roleid']) : '-1';
                }
                $subscription_plan_result = array();
                $sql = $ilance->db->query("
                        SELECT subscriptionid, title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title, description_" . $_SESSION['ilancedata']['user']['slng'] . " AS description, cost, length, units, subscriptiongroupid, roleid, active, canremove, visible_registration, visible_upgrade, icon, migrateto, migratelogic
                        FROM " . DB_PREFIX . "subscription
                        WHERE subscriptionid = '" . intval($subscriptionid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $subscription_plan_result = $ilance->db->fetch_array($sql, DB_ASSOC);
                        
                        ($apihook = $ilance->api('registration_build_user_subscription_start')) ? eval($apihook) : false;
                        
                        $subscription_plan_cost = sprintf('%01.2f', $subscription_plan_result['cost']);
                        $subscription_length = $ilance->subscription->subscription_length($subscription_plan_result['units'], $subscription_plan_result['length']);
                        $subscription_renew_date = print_subscription_renewal_datetime($subscription_length);
                        $sql_check = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "subscription_user
                                WHERE user_id = '" . intval($userid) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql_check) == 0)
                        {
                                if ($paymethod == 'wire' OR empty($paymethod))
                                {
                                        $paymethod = 'account';
                                }
                                // build subscription for user and set to unpaid / not active
                                $ilance->db->query("
                                        INSERT INTO " . DB_PREFIX . "subscription_user
                                        (id, subscriptionid, user_id, paymethod, startdate, renewdate, autopayment, active, migrateto, migratelogic, roleid)
                                        VALUES(
                                        NULL,
                                        '" . intval($subscriptionid) . "',
                                        '" . intval($userid) . "',
                                        '" . $ilance->db->escape_string($paymethod) . "',
                                        '" . DATETIME24H . "',
                                        '" . $subscription_renew_date . "',
                                        '1',
                                        'no',
                                        '" . $subscription_plan_result['migrateto'] . "',
                                        '" . $subscription_plan_result['migratelogic'] . "',
                                        '" . $roleid . "')
                                ", 0, null, __FILE__, __LINE__);
                                // if plan is free, update subscription for user to active
                                if (isset($subscription_plan_result['cost']) AND $subscription_plan_result['cost'] <= 0)
                                {
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "subscription_user
                                                SET active = 'yes',
                                                autopayment = '1'
                                                WHERE user_id = '" . intval($userid) . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                        // set subscription session to active
                                        // this will also prevent an admin subscription sessions from changing
                                        if ((defined('LOCATION') AND LOCATION != 'admin') OR $skipsession == 0)
                                        {
                                                $_SESSION['ilancedata']['user']['active'] = 'yes';
                                        }
                                }
                                $subscription_length = $ilance->subscription->subscription_length($subscription_plan_result['units'], $subscription_plan_result['length']);
                                $invoice_due_date = print_subscription_renewal_datetime($subscription_length);
                                $sql_check = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "invoices
                                        WHERE user_id = '" . intval($userid) . "'
                                                AND subscriptionid = '" . intval($subscriptionid) . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql_check) == 0)
                                {
                                        $ispurchaseorder = '0';
                                        $purchaseorderbit = '';
                                        $subscriptionordernumber = $ilance->accounting_payment->construct_transaction_id();
                                        if ($paymethod == 'wire')
                                        {
                                                $paymethod = 'account';
                                        }
                                        else if ($paymethod == 'purchaseorder')
                                        {
                                                $ispurchaseorder = '1';
                                                $paymethod = 'purchaseorder';
                                                $purchaseorderbit = '{_purchase_order_invoice}' . ' (' . $subscriptionordernumber . '): ';
                                        }
                                        else
                                        {
                                                $paymethod = 'account';
                                        }
                                        $subscription_invoice_id = $ilance->accounting->insert_transaction(
                                                intval($subscriptionid),
                                                0,
                                                0,
                                                intval($userid),
                                                0,
                                                0,
                                                0,
                                                $purchaseorderbit . '{_subscription_payment_for}' . ' ' . $subscription_plan_result['title'] . ' (' . $subscription_plan_result['length'] . print_unit($subscription_plan_result['units']) . ')',
                                                sprintf("%01.2f", $subscription_plan_cost),
                                                '',
                                                'scheduled',
                                                'subscription',
                                                $paymethod,
                                                DATETIME24H,
                                                $invoice_due_date,
                                                '',
                                                '{_thank_you_for_your_business}',
                                                0,
                                                $ispurchaseorder,
                                                1,
                                                $subscriptionordernumber
                                        );
                                        if (isset($subscription_plan_result['cost']) AND $subscription_plan_result['cost'] <= 0)
                                        {
                                                // if free plan, update invoice
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "invoices
                                                        SET status = 'paid',
                                                        paiddate = '" . DATETIME24H . "',
                                                        paid = '0.00'
                                                        WHERE invoiceid = '" . intval($subscription_invoice_id) . "'
                                                                AND user_id = '" . intval($userid) . "'
                                                        LIMIT 1
                                                ", 0, null, __FILE__, __LINE__);
                                        }
                                        
                                        ($apihook = $ilance->api('registration_build_user_subscription_end')) ? eval($apihook) : false;
                            
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "subscription_user
                                                SET invoiceid = '" . intval($subscription_invoice_id) . "'
                                                WHERE user_id = '" . intval($userid) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        // if purchase order, send invoice to admin and customer
                                        // provide both with a printable link to the invoice
                                        if (isset($paymethod) AND $paymethod == 'purchaseorder')
                                        {
                                                $address2 = ' / ';
                                                if (!empty($_SESSION['ilancedata']['user']['address2']))
                                                {
                                                        $address2 = ' / ' . $_SESSION['ilancedata']['user']['address2'] . ' / ';
                                                }
                                                $url = HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=print&txn=' . $subscriptionordernumber;
                                                $ilance->email->mail = array(SITE_EMAIL, $_SESSION['ilancedata']['user']['email']);
                                                $ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
                                                $ilance->email->get('registration_purchase_order');		
                                                $ilance->email->set(array(
                                                        '{{subscriptionordernumber}}' => $subscriptionordernumber,
                                                        '{{url}}' => $url,
                                                        '{{date}}' => print_date(DATETIME24H, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
                                                        '{{itemtitle}}' => stripslashes($subscription_plan_result['title']),
                                                        '{{length}}' => $subscription_plan_result['length'],
                                                        '{{units}}' => print_unit($subscription_plan_result['units']),
                                                        '{{subscription_plan_cost}}' => $ilance->currency->format($subscription_plan_cost),
                                                        '{{totalpaid}}' => $ilance->currency->format(0),
                                                        '{{firstname}}' => $_SESSION['ilancedata']['user']['firstname'],
                                                        '{{lastname}}' => $_SESSION['ilancedata']['user']['lastname'],
                                                        '{{address}}' => $_SESSION['ilancedata']['user']['address'],
                                                        '{{address2}}' => $_SESSION['ilancedata']['user']['address2'],
                                                        '{{city}}' => $_SESSION['ilancedata']['user']['city'],
                                                        '{{state}}' => $_SESSION['ilancedata']['user']['state'],
                                                        '{{zipcode}}' => $_SESSION['ilancedata']['user']['zipcode'],
                                                        '{{country}}' => $_SESSION['ilancedata']['user']['country'],
                                                        '{{phone}}' => $_SESSION['ilancedata']['user']['phone'],
                                                        '{{emailaddress}}' => $_SESSION['ilancedata']['user']['email']
                                                ));
                                                $ilance->email->send();
                                        }
                                }
                        }
                }
        }
    
        /**
        * Function for checking a referral code
        *
        * @param       integer      user id
        * @param       string       referral code
        *
        * @return      nothing
        */
        function referral_check($userid, $referralcode)
        {
                global $ilance, $ilconfig, $ilpage;
                $sql = $ilance->db->query("
                        SELECT rid, user_id
                        FROM " . DB_PREFIX . "users
                        WHERE rid = '" . $ilance->db->escape_string($referralcode) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                $sql2 = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "users
                                        WHERE user_id = '" . intval($userid) . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql2) > 0)
                                {
                                        $ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "referral_data
                                                (id, user_id, referred_by, date)
                                                VALUES(
                                                NULL,
                                                '" . intval($userid) . "',
                                                '" . intval($res['user_id']) . "',
                                                '" . DATETIME24H . "')
                                        ", 0, null, __FILE__, __LINE__);
                                        $ilance->email->mail = fetch_user('email', $res['user_id']);
                                        $ilance->email->slng = fetch_user_slng(intval($userid));
                                        $ilance->email->get('referral_registered_referrer');		
                                        $ilance->email->set(array(
                                                '{{username}}' => fetch_user('username', $res['user_id']),
                                                '{{rid}}' => $referralcode,
                                                '{{payout_amount}}' => $ilance->currency->format($ilconfig['referalsystem_payout'])
                                        ));
                                        $ilance->email->send();
                                }
                        }
                }
        }
        
        /**
        * Function for creating a new user account number used in the ILance accounting system.
        *
        * @return      mixed         unique online account balance number
        */
        function construct_account_number()
        {
        	global $ilance;
                do 
                {
	                $rand1 = rand(100, 999);
	                $rand2 = rand(100, 999);
	                $rand3 = rand(100, 999);
	                $rand4 = rand(100, 999);
	                $rand5 = rand(1, 9);
	                $account_number = $rand1 . $rand2 . $rand3 . $rand4 . $rand5;
	                $sql = $ilance->db->query("
                                SELECT user_id 
                                FROM " . DB_PREFIX . "users 
                                WHERE account_number = '" . $account_number . "'
                                LIMIT 1
                        ");
        	}
                while ($ilance->db->num_rows($sql) > 0);
                return $account_number;
        }
        
        /**
        * Function to process submitted custom registration questions to be stored within the database
        *
        * @param       array         custom answers stored in array format
        * @param       integer       user id
        * 
        * @return      mixed         unique online account balance number
        */
        function process_custom_register_questions(&$custom, $userid)
        {
                global $ilance;
                if (isset($custom) AND is_array($custom))
                {
                        foreach ($custom as $questionid => $answerarray)
                        {
                                foreach ($answerarray as $formname => $answer)
                                {
                                        $sql = $ilance->db->query("
                                                SELECT *
                                                FROM " . DB_PREFIX . "register_answers
                                                WHERE user_id = '" . intval($userid) . "'
                                                    AND questionid = '" . intval($questionid) . "'
                                        ");
                                        if ($ilance->db->num_rows($sql) > 0)
                                        {
                                                if (is_array($answer))
                                                {
                                                        $answer = serialize($answer);
                                                }
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "register_answers
                                                        SET answer = '" . $ilance->db->escape_string($answer) . "',
                                                        date = '" . DATETIME24H . "'
                                                        WHERE questionid = '" . intval($questionid) . "'
                                                            AND user_id = '" . intval($userid) . "'
                                                ");
                                        }
                                        else
                                        {
                                                if (is_array($answer))
                                                {
                                                        $answer = serialize($answer);
                                                }
                                                $ilance->db->query("
                                                        INSERT INTO " . DB_PREFIX . "register_answers
                                                        (answerid, questionid, user_id, answer, date, visible)
                                                        VALUES (
                                                        NULL,
                                                        '" . intval($questionid) . "',
                                                        '" . intval($userid) . "',
                                                        '" . $ilance->db->escape_string($answer) . "',
                                                        '" . DATETIME24H . "',
                                                        '1')
                                                ");
                                        }
                                }
                        }
                }
        }
    
        /**
        * Function for checking if a user attempting to register is coming from a proxy service and
        * displays a custom template denying registration if registration proxy disabling is enabled.
        *
        * @return      string        HTML representation of the question registration question
        */
        function proxy_check()
        {
                global $ilconfig, $ilcrumbs, $ilpage, $phrase;
                if (isset($ilconfig['globalfilters_blockregistrationproxies']) AND $ilconfig['globalfilters_blockregistrationproxies'] != "")
                {
                        if (isset($_SERVER['HTTP_FORWARDED']) OR isset($_SERVER['HTTP_X_FORWARDED_FOR']) OR isset($_SERVER['HTTP_VIA']))
                        {
                                $area_title = '{_cannot_register_behind_proxy}';
                                $page_title = SITE_NAME . ' - ' . '{_cannot_register_behind_proxy}';
                                $navcrumb = array("$ilpage[main]" => "$ilcrumbs[registration]");
                                print_notice('{_cannot_register_behind_proxy}', '{_sorry_registration_to_the_marketplace_requires_our_members_not_to_be_behind_a_proxy}', $ilpage['main'], '{_main_menu}');
                                exit();
                        }
                }
        }
        
        /**
        * Function for returning the subscription id of a free subscription plan that is active and visible
        * for the permission of 'servicebid' with a value of 'true'
        *
        * @return      bool          false or the integer of the subscription id
        */
        function fetch_invite_subscriptionid()
        {
                global $ilance;
                $found = 0;
                $sql = $ilance->db->query("
                        SELECT subscriptionid, subscriptiongroupid
                        FROM " . DB_PREFIX . "subscription
                        WHERE cost = '0.00'
                            AND active = 'yes'
                            AND visible_registration = '1'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                // we have free subscription plans: which plan has "servicebid" enabled?
                                $sql2 = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "subscription_permissions
                                        WHERE subscriptiongroupid = '" . $res['subscriptiongroupid'] . "'
                                            AND accessname = 'servicebid'
                                            AND value = 'yes'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql2) > 0)
                                {
                                        // found a plan! lets assign this plan to externally invited bidders!
                                        return $res['subscriptionid'];
                                }
                                else
                                {
                                        return 0;
                                }
                        }
                }
                else
                {
                        return 0;
                }
        }
        
        /**
        * Function for returning the subscription id of a free subscription plan that is active and visible
        * for the permission of 'servicebid' with a value of 'true'
        *
        * @param       integer       user id
        * @param       string        email address
        *
        * @return      nothing
        */
        function build_invitation_datastore($userid = 0, $email)
        {
                global $ilance;
                // transform this once external provider into a registered member invited to bid
                // for service buyers that invite providers to their projects
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "project_invitations
                        WHERE email = '" . $ilance->db->escape_string($email) . "'
                            AND seller_user_id = '-1'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "project_invitations
                                        SET seller_user_id = '" . intval($userid) . "'
                                        WHERE email = '" . $ilance->db->escape_string($email) . "'
                                            AND seller_user_id = '-1'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                        }
                }
                
                // transfer this once external buyer into a registered member invited to bid
                // for merchants that invite buyers to their products
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "project_invitations
                        WHERE email = '" . $ilance->db->escape_string($email) . "'
                            AND buyer_user_id = '-1'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "project_invitations
                                        SET buyer_user_id = '" . intval($userid) . "'
                                        WHERE email = '" . $ilance->db->escape_string($email) . "'
                                            AND buyer_user_id = '-1'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                        }
                }
        }
        
        /**
        * Function for returning the default time zone
        *
        * @return      string        Returns default time zone
        */
        function fetch_default_timezone()
        {
                global $ilconfig;
                $tzn = (!empty($ilconfig['globalserverlocale_sitetimezone']) ? $ilconfig['globalserverlocale_sitetimezone'] : 'America/Toronto');
                return $tzn;
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>