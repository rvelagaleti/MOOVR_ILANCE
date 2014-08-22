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

define('SESSIONHOST', mb_substr(IPADDRESS, 0, 15));
if (defined('LOCATION') AND LOCATION == 'admin')
{
	define('IN_ADMIN_CP', true);
}
else
{
	define('IN_ADMIN_CP', false);
}

/**
* Session class to perform the majority of session functionality in ILance.
*
* @package      iLance\Sessions
* @version      4.0.0.8059
* @author       ILance
*/
class sessions
{
	/**
	* Constructor
	*
	* @param       $registry	ILance registry object
	*/
	function __construct()
	{
		session_set_save_handler(
			array (&$this, 'session_open'), 
			array (&$this, 'session_close'), 
			array (&$this, 'session_read'), 
			array (&$this, 'session_write'), 
			array (&$this, 'session_destroy'), 
			array (&$this, 'session_gc')
		);
	}
    
	/**
	* Encrypt and compress the serailized session data
	*
	* @param       array        session data
	* @return      string       Encrypted session data
	*/
	function encrypt($data = '')
	{
		if ($this->sessionencrypt)
		{
			global $ilconfig, $ilance;
			return $ilance->crypt->three_layer_encrypt($data, $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']);
		}
		return $data;
	}
    
	/**
	* Decrypt and return the encrypted or serialized session data
	*
	* @param       string       encrypted session data
	* @return      array        Session data
	*/
	function decrypt($data = '')
	{
		if ($this->sessionencrypt)
		{
			global $ilconfig, $ilance;
			return $ilance->crypt->three_layer_decrypt($data, $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']);
		}
		return $data;
	}
    
	/**
	* Fetch session first click if applicable
	*
	* @param       string       session key
	* 
	* @return      string       Returns first click timestamp
	*/
	function session_firstclick($sessionkey = '')
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "firstclick
			FROM " . DB_PREFIX . "sessions
			WHERE sesskey = '" . $ilance->db->escape_string($sessionkey) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			return $res['firstclick'];
		}
		return TIMESTAMPNOW;
	}
    
	/**
	* Session open handler
	*
	* @return      bool         true if session data could be opened
	*/
	function session_open($savepath = '', $sessioname = '')
	{
		return true;
	}
    
	/**
	* Session close handler
	*
	* @return      bool         true if session data could be closed
	*/
	function session_close()
	{
		$this->session_gc();
		return true;
	}
    
	/**
	* Session read handler is called once the script is loaded
	*
	* @param       string       session key
	* 
	* @return      string       value from the session table
	*/
	function session_read($sessionkey)
	{
		global $ilance;
		$result = $ilance->db->query("
			SELECT value
			FROM " . DB_PREFIX . "sessions
			WHERE sesskey = '" . $ilance->db->escape_string($sessionkey) . "'
				AND expiry > " . TIMESTAMPNOW . "
		", 0, null, __FILE__, __LINE__);
		if (list($value) = $ilance->db->fetch_row($result))
		{
			return $value;
		}
		return '';
	}
    
	/**
	* Session write handler is called once the script is finished executing
	*
	* @param       string       session key
	* @param       string       session data we would like to update
	*/
	function session_write($sessionkey = '', $sessiondata = '')
	{
		global $ilance, $ilconfig, $area_title, $show, $phrase;
		$session = array ();
		$skipsession = array ('cron');
		if (defined('SKIP_SESSION') AND SKIP_SESSION OR defined('LOCATION') AND in_array(LOCATION, $skipsession))
		{
			return true;
		}
		// if we've never been here before, we'll create a "last visit" cookie to remember the user
		if (empty($_COOKIE[COOKIE_PREFIX . 'lastvisit']))
		{
			set_cookie('lastvisit', DATETIME24H);
		}
		// we will continue to update our last activity cookie on each page hit
		set_cookie('lastactivity', DATETIME24H);
		$session['ilancedata'] = unserialize(str_replace('ilancedata|', '', $sessiondata));
		$scriptname = !empty($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
		$querystring = print_hidden_fields(true, array (), true);
		$firstclick = $this->session_firstclick($sessionkey);
		$session['ilancedata']['user']['url'] = $scriptname . $querystring;
		$session['ilancedata']['user']['area_title'] = !empty($area_title) ? $area_title : '{_unknown}';
	
		// #### SEARCH BOT TRACKER #####################################
		if (isset($show['searchengine']) AND $show['searchengine'])
		{
			$ilance->db->query("
				REPLACE " . DB_PREFIX . "sessions
				(sesskey, expiry, value, userid, isuser, isadmin, isrobot, iserror, languageid, styleid, agent, lastclick, ipaddress, url, title, firstclick, browser, token, siteid)
				VALUES(
				'" . $ilance->db->escape_string($sessionkey) . "',
				'" . (TIMESTAMPNOW + ($ilconfig['globalserversession_crawlertimeout'] * 60)) . "',
				'" . $ilance->db->escape_string($sessiondata) . "',
				'0',
				'0',
				'0',
				'1',
				'0',
				'" . intval($session['ilancedata']['user']['languageid']) . "',
				'" . intval($session['ilancedata']['user']['styleid']) . "',
				'" . $ilance->db->escape_string(USERAGENT) . "',
				'" . TIMESTAMPNOW . "',
				'" . $ilance->db->escape_string(IPADDRESS) . "',
				'" . $ilance->db->escape_string($session['ilancedata']['user']['url']) . "',
				'" . $ilance->db->escape_string($session['ilancedata']['user']['area_title']) . "',
				'" . $ilance->db->escape_string($firstclick) . "',
				'" . $ilance->db->escape_string($ilance->common->fetch_browser_name()) . "',
				'" . $ilance->db->escape_string(TOKEN) . "',
				'" . $ilance->db->escape_string(SITE_ID) . "')
			", 0, null, __FILE__, __LINE__);
		}
		// #### USER & STAFF TRACKER ###################################
		else if (!empty($session['ilancedata']['user']['userid']))
		{
			$expiry = ((IN_ADMIN_CP AND $session['ilancedata']['user']['isadmin']) ? "'" . (TIMESTAMPNOW + ($ilconfig['globalserversession_admintimeout'] * 60)) . "'," : "'" . (TIMESTAMPNOW + ($ilconfig['globalserversession_membertimeout'] * 60)) . "',");
			$isuser = ((IN_ADMIN_CP AND $session['ilancedata']['user']['isadmin']) ? "'0'," : "'1',");
			$isadmin = ((IN_ADMIN_CP AND $session['ilancedata']['user']['isadmin']) ? "'1'," : "'0',");
			$ilance->db->query("
				REPLACE " . DB_PREFIX . "sessions
				(sesskey, expiry, value, userid, isuser, isadmin, isrobot, iserror, languageid, styleid, agent, lastclick, ipaddress, url, title, firstclick, browser, token, siteid)
				VALUES(
				'" . $ilance->db->escape_string($sessionkey) . "',
				$expiry
				'" . $ilance->db->escape_string($sessiondata) . "',
				'" . $session['ilancedata']['user']['userid'] . "',
				$isuser
				$isadmin
				'0',
				'0',
				'" . intval($session['ilancedata']['user']['languageid']) . "',
				'" . intval($session['ilancedata']['user']['styleid']) . "',
				'" . $ilance->db->escape_string(USERAGENT) . "',
				'" . TIMESTAMPNOW . "',
				'" . $ilance->db->escape_string(IPADDRESS) . "',
				'" . $ilance->db->escape_string($session['ilancedata']['user']['url']) . "',
				'" . $ilance->db->escape_string($session['ilancedata']['user']['area_title']) . "',
				'" . $ilance->db->escape_string($firstclick) . "',
				'" . $ilance->db->escape_string($ilance->common->fetch_browser_name()) . "',
				'" . $ilance->db->escape_string(TOKEN) . "',
				'" . $ilance->db->escape_string(SITE_ID) . "')
			", 0, null, __FILE__, __LINE__);
			unset($expiry, $isadmin);
		}
	
		// #### GUEST TRACKER ##########################################
		else
		{
			$ilance->db->query("
				REPLACE " . DB_PREFIX . "sessions
				(sesskey, expiry, value, userid, isuser, isadmin, isrobot, iserror, languageid, styleid, agent, lastclick, ipaddress, url, title, firstclick, browser, token, siteid)
				VALUES(
				'" . $ilance->db->escape_string($sessionkey) . "',
				'" . (TIMESTAMPNOW + ($ilconfig['globalserversession_guesttimeout'] * 60)) . "',
				'" . $ilance->db->escape_string($sessiondata) . "',
				'0',
				'0',
				'0',
				'0',
				'0',
				'" . intval($session['ilancedata']['user']['languageid']) . "',
				'" . intval($session['ilancedata']['user']['styleid']) . "',
				'" . $ilance->db->escape_string(USERAGENT) . "',
				'" . TIMESTAMPNOW . "',
				'" . $ilance->db->escape_string(IPADDRESS) . "',
				'" . $ilance->db->escape_string($session['ilancedata']['user']['url']) . "',
				'" . $ilance->db->escape_string($session['ilancedata']['user']['area_title']) . "',
				'" . $ilance->db->escape_string($firstclick) . "',
				'" . $ilance->db->escape_string($ilance->common->fetch_browser_name()) . "',
				'" . $ilance->db->escape_string(TOKEN) . "',
				'" . $ilance->db->escape_string(SITE_ID) . "')
			", 0, null, __FILE__, __LINE__);
		}
		unset($scriptname, $querystring);
		return true;
	}
    
	/**
	* Session destroy handler
	*
	* @param       string       session key
	* @return      void
	*/
	function session_destroy($sessionkey = '')
	{
		global $ilance;
		$ilance->db->query("
			DELETE
			FROM " . DB_PREFIX . "sessions
			WHERE sesskey = '" . $ilance->db->escape_string($sessionkey) . "'
		", 0, null, __FILE__, __LINE__);
		return true;
	}
    
	/**
	* Session garbage collection handler
	*
	* @return      void
	*/
	function session_gc($maxlifetime = '')
	{
		global $ilance;
		$ilance->db->query("
			DELETE
			FROM " . DB_PREFIX . "sessions
			WHERE expiry < " . TIMESTAMPNOW
		, 0, null, __FILE__, __LINE__);
		return true;
	}
    
	/**
	* Function to handle remembering a user by automatically initializing their session based on valid cookies and them wanting to be remembered.
	*
	* @return      void
	*/
	function init_remembered_session()
	{
		global $ilance, $show, $ilconfig;
		$session = array ();
		$noremember = array ('registration', 'attachment', 'login', 'admin', 'cron', 'ipn', 'ajax', 'lancealert');
		if (empty($_SESSION['ilancedata']['user']['userid']) AND !empty($_COOKIE[COOKIE_PREFIX . 'password']) AND !empty($_COOKIE[COOKIE_PREFIX . 'username']) AND !empty($_COOKIE[COOKIE_PREFIX . 'userid']) AND defined('LOCATION') AND !in_array(LOCATION, $noremember))
		{
			$sql = $ilance->db->query("
				SELECT u.*, su.roleid, su.subscriptionid, su.active, sp.cost, c.currency_name, c.currency_abbrev, l.languagecode
				FROM " . DB_PREFIX . "users AS u
				LEFT JOIN " . DB_PREFIX . "subscription_user su ON u.user_id = su.user_id
				LEFT JOIN " . DB_PREFIX . "subscription sp ON su.subscriptionid = sp.subscriptionid
				LEFT JOIN " . DB_PREFIX . "currency c ON u.currencyid = c.currency_id
				LEFT JOIN " . DB_PREFIX . "language l ON u.languageid = l.languageid
				WHERE username = '" . $ilance->db->escape_string($ilance->crypt->three_layer_decrypt($_COOKIE[COOKIE_PREFIX . 'username'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3'])) . "'
					AND password = '" . $ilance->db->escape_string($ilance->crypt->three_layer_decrypt($_COOKIE[COOKIE_PREFIX . 'password'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3'])) . "'
					AND u.user_id = '" . intval($ilance->crypt->three_layer_decrypt($_COOKIE[COOKIE_PREFIX . 'userid'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3'])) . "'
					AND status = 'active'
				GROUP BY username
				LIMIT 1      
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$userinfo = $ilance->db->fetch_array($sql, DB_ASSOC);
				$userinfo['zip_code'] = (mb_strtolower($userinfo['zip_code']) != '{_unknown}') ? mb_strtoupper($userinfo['zip_code']) : mb_strtolower($userinfo['zip_code']);
				$session['ilancedata'] = $ilance->sessions->build_user_session($userinfo, true);
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "users
					SET lastseen = '" . DATETIME24H . "'
					WHERE user_id = '" . $userinfo['user_id'] . "'
				", 0, null, __FILE__, __LINE__);
				set_cookie('radiuszip', handle_input_keywords(format_zipcode($userinfo['zip_code'])));
		
				($apihook = $ilance->api('remember_me_session')) ? eval($apihook) : false;
			}
		}
		if (!empty($session['ilancedata']['user']) AND is_array($session['ilancedata']['user']))
		{
			foreach ($session AS $key => $value)
			{
				$_SESSION["$key"] = $value;
			}
		}
	}
    
	/**
	* Function to handle a user language or style switch within the marketplace.  Additionally, will update their account within the db if the user is active and logged in.  This is called from global.php.
	* Additionally, this function is responsible for setting the user's initial languageid and styleid for the active session.
	*
	* @return      void
	*/
	function handle_language_style_changes()
	{
		global $ilance, $ilconfig;
		if (isset($ilance->GPC['language']) AND !empty($ilance->GPC['language']))
		{
			$ilconfig['langcode'] = urldecode(mb_strtolower(trim($ilance->GPC['language'])));
			$langdata = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "languageid, languagecode
				FROM " . DB_PREFIX . "language
				WHERE languagecode = '" . $ilance->db->escape_string($ilconfig['langcode']) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($langdata) > 0)
			{
				$langinfo = $ilance->db->fetch_array($langdata, DB_ASSOC);
				if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "users
						SET languageid = '" . $langinfo['languageid'] . "'
						WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					", 0, null, __FILE__, __LINE__);
				}
				$_SESSION['ilancedata']['user']['languageid'] = intval($langinfo['languageid']);
				$_SESSION['ilancedata']['user']['languagecode'] = $langinfo['languagecode'];
				$_SESSION['ilancedata']['user']['slng'] = mb_substr($_SESSION['ilancedata']['user']['languagecode'], 0, 3);
				unset($langinfo);
			}
		}
		if (isset($ilance->GPC['styleid']) AND $ilance->GPC['styleid'] > 0 AND defined('LOCATION') AND LOCATION != 'admin')
		{
			$styledata = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "styleid
				FROM " . DB_PREFIX . "styles
				WHERE styleid = '" . intval($ilance->GPC['styleid']) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($styledata) > 0)
			{
				if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "users
						SET styleid = '" . intval($ilance->GPC['styleid']) . "'
						WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					", 0, null, __FILE__, __LINE__);
				}
				$_SESSION['ilancedata']['user']['styleid'] = intval($ilance->GPC['styleid']);
			}
		}
		if (empty($_SESSION['ilancedata']['user']['languageid']) OR empty($_SESSION['ilancedata']['user']['slng']))
		{
			$_SESSION['ilancedata']['user']['languageid'] = $ilconfig['globalserverlanguage_defaultlanguage'];
			$_SESSION['ilancedata']['user']['languagecode'] = $ilance->language->print_language_code($ilconfig['globalserverlanguage_defaultlanguage']);
			$_SESSION['ilancedata']['user']['slng'] = $ilance->language->print_short_language_code();
		}
		if (empty($_SESSION['ilancedata']['user']['styleid']))
		{
			$_SESSION['ilancedata']['user']['styleid'] = $ilconfig['defaultstyle'];
		}
		if (empty($_SESSION['ilancedata']['user']['currencyid']))
		{
			$_SESSION['ilancedata']['user']['currencyid'] = $ilconfig['globalserverlocale_defaultcurrency'];
		}
		if (empty($_SESSION['ilancedata']['user']['csrf']))
		{
			$_SESSION['ilancedata']['user']['csrf'] = md5(uniqid(mt_rand(), true));
		}
	}
    
	/**
	* Function to build a valid user session after successful sign-in.  This function was created because we've implemented the new admin user switcher
	* and it's pointless to handle 2 large pieces of code for session building- so this was created.
	*
	* @param       array          $userinfo array of user from the database
	* @param       boolean        only return array (default false, builds $_SESSION['ilancedata'])
	*
	* @return      nothing
	*/
	function build_user_session($userinfo = array (), $returnonly = false)
	{
		global $ilance, $ilconfig, $ilpage, $show, $_SESSION;
	
		// #### empty inline cookie ############################################
		set_cookie('inlineservice', '', false);
		set_cookie('inlineproduct', '', false);
		set_cookie('inlineexperts', '', false);
	
		($apihook = $ilance->api('build_user_session_start')) ? eval($apihook) : false;
	
		// #### build user session #############################################
		$session = array ('user' => array (
			'isadmin' => $userinfo['isadmin'],
			'status' => $userinfo['status'],
			'userid' => $userinfo['user_id'],
			'username' => $userinfo['username'],
			'password' => $userinfo['password'],
			'salt' => $userinfo['salt'],
			'email' => handle_input_keywords($userinfo['email']),
			'phone' => handle_input_keywords($userinfo['phone']),
			'firstname' => handle_input_keywords($userinfo['first_name']),
			'lastname' => handle_input_keywords($userinfo['last_name']),
			'fullname' => handle_input_keywords($userinfo['first_name'] . ' ' . $userinfo['last_name']),
			'address' => ucwords(handle_input_keywords($userinfo['address'])),
			'address2' => ucwords(handle_input_keywords($userinfo['address2'])),
			'fulladdress' => ucwords(stripslashes($userinfo['address'])) . ' ' . ucwords(handle_input_keywords($userinfo['address2'])),
			'city' => ucwords(handle_input_keywords($userinfo['city'])),
			'state' => ucwords(handle_input_keywords($userinfo['state'])),
			'postalzip' => handle_input_keywords(mb_strtoupper(trim($userinfo['zip_code']))),
			'countryid' => intval($userinfo['country']),
			'country' => handle_input_keywords($ilance->common_location->print_country_name($userinfo['country'])),
			'countryshort' => $ilance->common_location->print_country_name($userinfo['country'], mb_substr($userinfo['languagecode'], 0, 3), true),
			'lastseen' => $userinfo['lastseen'],
			'ipaddress' => $userinfo['ipaddress'],
			'iprestrict' => $userinfo['iprestrict'],
			'auctiondelists' => intval($userinfo['auctiondelists']),
			'bidretracts' => intval($userinfo['bidretracts']),
			'ridcode' => $userinfo['rid'],
			'dob' => handle_input_keywords($userinfo['dob']),
			'browseragent' => handle_input_keywords(USERAGENT),
			'serviceawards' => intval($userinfo['serviceawards']),
			'productawards' => intval($userinfo['productawards']),
			'servicesold' => intval($userinfo['servicesold']),
			'productsold' => intval($userinfo['productsold']),
			'rating' => $userinfo['rating'],
			'languageid' => intval($userinfo['languageid']),
			'languagecode' => $userinfo['languagecode'],
			'slng' => mb_substr($userinfo['languagecode'], 0, 3),
			'styleid' => intval($userinfo['styleid']),
			'timezone' => handle_input_keywords($userinfo['timezone']),
			'distance' => $userinfo['project_distance'],
			'emailnotify' => intval($userinfo['emailnotify']),
			'companyname' => handle_input_keywords(stripslashes($userinfo['companyname'])),
			'roleid' => intval($userinfo['roleid']),
			'subscriptionid' => intval($userinfo['subscriptionid']),
			'cost' => $userinfo['cost'],
			'active' => $userinfo['active'],
			'currencyid' => intval($userinfo['currencyid']),
			'currencyname' => handle_input_keywords(stripslashes($userinfo['currency_name'])),
			'currencysymbol' => (isset($userinfo['currencyid']) AND !empty($userinfo['currencyid'])) ? $ilance->currency->currencies[$userinfo['currencyid']]['symbol_left'] : '$',
			'currencyabbrev' => handle_input_keywords(mb_strtoupper($userinfo['currency_abbrev'])),
			'searchoptions' => isset($userinfo['searchoptions']) ? $userinfo['searchoptions'] : '',
			'token' => TOKEN,
			'siteid' => SITE_ID,
			'csrf' => md5(uniqid(mt_rand(), true)))
		);
	
		($apihook = $ilance->api('build_user_session_end')) ? eval($apihook) : false;
	
		if ($returnonly)
		{
			return $session;
		}
		else
		{
			$_SESSION['ilancedata'] = $session;
		}
	}
    
	/**
	* Ensure session data is written out before classes are destroyed
	*
	* @return      void
	*/
	function __destruct()
	{
		@session_write_close();
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>