<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 4.0.0 Build 8059
|| # -------------------------------------------------------------------- # ||
|| # Customer License # H7LhzCqPSNEABnY
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000-2014 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/
define('ILANCEVERSION', '4.0.0'); // this should match installer.php
define('VERSIONSTRING', str_replace('.', '', ILANCEVERSION));
define('SVNVERSION', '8059');

/**
* Define our line-break pattern (Windows: \r\n or Linux: \n)
*/
define('LINEBREAK', "\n"); // or \r\n or \r

/**
* defines if we have multibyte encoding available
*/
define('MULTIBYTE', (extension_loaded('mbstring') AND function_exists('mb_detect_encoding')) ? true : false);

/**
* base folders
*/
define('DIR_FUNCTIONS', DIR_SERVER_ROOT . DIR_FUNCT_NAME . '/');
define('DIR_CORE', DIR_FUNCTIONS . DIR_CORE_NAME . '/');
define('DIR_API', DIR_FUNCTIONS . DIR_API_NAME . '/');
define('DIR_XML', DIR_FUNCTIONS . DIR_XML_NAME . '/');
define('DIR_CSS', DIR_FUNCTIONS . 'css/');
define('DIR_WDSL', DIR_FUNCTIONS . DIR_API_NAME . '/wdsl/');

/**
* writable cache folder
*/
define('DIR_TMP', DIR_SERVER_ROOT . DIR_TMP_NAME . '/');
define('DIR_TMP_CSS', DIR_TMP . DIR_CSS_NAME . '/');
define('DIR_TMP_JS', DIR_TMP . DIR_JS_NAME . '/');

/**
* other important application folders
*/
define('PAYMENTLOG', DIR_SERVER_ROOT . DIR_TMP_NAME . '/paymentlog/');
define('SHIPPINGLOG', DIR_SERVER_ROOT . DIR_TMP_NAME . '/shippinglog/');
define('DIR_CRON', DIR_FUNCTIONS . DIR_CRON_NAME . '/');
define('DIR_UPLOADS', DIR_SERVER_ROOT_IMAGES . DIR_UPLOADS_NAME . '/');
define('DIR_ATTACHMENTS', DIR_UPLOADS . DIR_ATTACHMENTS_NAME . '/');
define('DIR_WS_ATTACHMENTS', DIR_ATTACHMENTS . 'ws/');
define('DIR_PORTFOLIO_ATTACHMENTS', DIR_ATTACHMENTS . 'portfolios/');
define('DIR_PROFILE_ATTACHMENTS', DIR_ATTACHMENTS . 'profiles/');
define('DIR_AUCTION_ATTACHMENTS', DIR_ATTACHMENTS . 'auctions/');
define('DIR_BID_ATTACHMENTS', DIR_ATTACHMENTS . 'bids/');
define('DIR_PMB_ATTACHMENTS', DIR_ATTACHMENTS . 'pmbs/');
define('DIR_STORE_ATTACHMENTS', DIR_ATTACHMENTS . 'stores/');
define('DIR_FORUM_ATTACHMENTS', DIR_ATTACHMENTS . 'bb/');
define('DIR_ADMIN', DIR_SERVER_ROOT . DIR_ADMIN_NAME . '/');
define('HTTP_SERVER_ADMIN', HTTP_SERVER . DIR_ADMIN_NAME . '/');
define('HTTPS_SERVER_ADMIN', HTTPS_SERVER . DIR_ADMIN_NAME . '/');
define('DIR_FONTS', DIR_FUNCTIONS . DIR_FONTS_NAME . '/');

/**
* iadvertiser
*/
define('DIR_ADS', DIR_SERVER_ROOT);
define('HTTP_ADS', HTTP_SERVER);
define('HTTPS_ADS', HTTPS_SERVER);
define('DIR_ADS_ATTACHMENTS', DIR_ATTACHMENTS . 'ads/');

/**
* icommunity
*/
define('DIR_KB', DIR_SERVER_ROOT . 'kb/');
define('DIR_KB_TEMPLATES', DIR_KB . 'templates/');
define('DIR_KB_FUNCTIONS', DIR_KB . 'functions/');
define('HTTP_KB', HTTP_SERVER . 'kb/');
define('HTTPS_KB', HTTPS_SERVER . 'kb/');
define('DIR_KB_ATTACHMENTS', DIR_ATTACHMENTS . 'kb/');

/**
* start HTML onload document body placeholder
*/
$onload = '';

/**
* start $show array for template conditionals
*/
$show = array();

/**
* software build version in variable format
*/
$buildversion = SVNVERSION;

/**
* Initialize core functions
*/
require_once(DIR_FUNCTIONS . 'connect.php');
require_once(DIR_CORE . 'functions.php');
require_once(DIR_CORE . 'functions_cookie.php');
require_once(DIR_CORE . 'functions_fetch.php');
require_once(DIR_CORE . 'functions_censor.php');
require_once(DIR_CORE . 'functions_pagnation.php');
require_once(DIR_CORE . 'functions_currency.php');
require_once(DIR_CORE . 'functions_license.php');
require_once(DIR_CORE . 'functions_password.php');
require_once(DIR_CORE . 'functions_seo.php');

/**
* Initialize software
*/
require_once(DIR_FUNCTIONS . 'init.php');

/**
* Used only when installing the software.
*/
if (defined('LOCATION') AND LOCATION == 'installer')
{
	// define templates folder based on the style currently selected
	define('DIR_TEMPLATES', DIR_SERVER_ROOT . 'templates/');
	return;
}

/**
* Initialize session datastore
*/
if (!session_id())
{
	session_start();
}
else
{
	die('<strong>Fatal:</strong> iLance application must have ownership of the very first session_start().  A previously created session was detected in <strong>global.php</strong> on line <strong>' . __LINE__ . '</strong>.');
}

/**
* Determine and handle a user selected language or style switch
* Function also sets the default currency for the visitor
*/
$ilance->sessions->handle_language_style_changes();

/**
* Will remember users that have selected to be remembered
*/
$ilance->sessions->init_remembered_session();

/**
* Initialize locale environment (helpful for languages other than english)
*/
$locale = $ilance->fetch_language_locale($_SESSION['ilancedata']['user']['languageid']);
setlocale(LC_TIME, $locale['locale']);
unset($locale);
$ilconfig['official_time'] = print_date('', '', true, false, $ilconfig['globalserverlocale_sitetimezone']);

/**
* For each cookie identifier in the list, this code will hide those elements
*/
$ilcollapse = array();
if (!empty($_COOKIE[COOKIE_PREFIX . 'collapse']))
{
	$cookiedata = explode('|', $_COOKIE[COOKIE_PREFIX . 'collapse']);
	foreach ($cookiedata AS $cookiekey)
	{
		$ilcollapse["collapseobj_$cookiekey"] = 'display: none;';
		$ilcollapse["collapseimg_$cookiekey"] = '_collapsed';
	}
}

/**
* For each cookie identifier in the list, this code will show those elements
*/
if (!empty($_COOKIE[COOKIE_PREFIX . 'deflate']))
{	
	$cookiedata = explode('|', $_COOKIE[COOKIE_PREFIX . 'deflate']);
	foreach ($cookiedata AS $cookiekey)
	{
		$ilcollapse["collapseobj_$cookiekey"] = 'display: inline;';
		$ilcollapse["collapseimg_$cookiekey"] = '_collapsed';
	}	
}
unset($cookiedata, $cookiekey);

// handle updating of the region cookie so we can remember who guests are
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'saveregion')
{
	if (!empty($ilance->GPC['region']) AND strrchr($ilance->GPC['region'], '.'))
	{
		set_cookie('region', handle_input_keywords($ilance->GPC['region']));
	}
	if (!empty($ilance->GPC['country']))
	{
		set_cookie('country', handle_input_keywords($ilance->GPC['country']));
	}
	if (!empty($ilance->GPC['state']))
	{
		set_cookie('state', handle_input_keywords($ilance->GPC['state']));
	}
	if (!empty($ilance->GPC['city']))
	{
		set_cookie('city', handle_input_keywords($ilance->GPC['city']));
	}
	if (!empty($ilance->GPC['radiuszip']))
	{
		set_cookie('radiuszip', handle_input_keywords(format_zipcode($ilance->GPC['radiuszip'])));
		if (empty($ilance->GPC['state']) AND !empty($ilance->GPC['country']))
		{
			$countryid = fetch_country_id($ilance->GPC['country'], $_SESSION['ilancedata']['user']['slng']);
			$state = $ilance->distance->fetch_state_from_zipcode($countryid, $ilance->GPC['radiuszip']);
			if ($countryid > 0 AND !empty($state))
			{
				set_cookie('state', handle_input_keywords($state));
				$ilance->GPC['state'] = $state;
			}
			unset($countryid, $state);
		}
		if (empty($ilance->GPC['city']) AND !empty($ilance->GPC['country']) AND !empty($ilance->GPC['state']))
		{
			$countryid = fetch_country_id($ilance->GPC['country'], $_SESSION['ilancedata']['user']['slng']);
			$state = $ilance->distance->fetch_state_from_zipcode($countryid, $ilance->GPC['radiuszip']);
			$city = $ilance->distance->fetch_city_from_zipcode($countryid, $state, $ilance->GPC['radiuszip']);
			if ($countryid > 0 AND !empty($state) AND !empty($city))
			{
				set_cookie('city', handle_input_keywords($city));
			}
			unset($countryid, $state, $city);
		}
	}
	if (!empty($ilance->GPC['returnurl']))
	{
		refresh(urldecode($ilance->GPC['returnurl']));
		exit();
	}
}

($apihook = $ilance->api('global_start')) ? eval($apihook) : false;

/**
* Initialize main breadcrumb phrases
*/
$ilcrumbs = $ilance->fetch_breadcrumb_titles();

/**
* Initialize styles and template variables backend
*/
$ilance->styles = construct_object('api.styles');

/**
* Initialize styles and template constants
*/
define('DIR_TEMPLATES', DIR_SERVER_ROOT . $ilconfig['template_folder']);
define('DIR_TEMPLATES_ADMIN', DIR_SERVER_ROOT . $ilconfig['template_folder'] . 'admincp/');

/**
* Initialize marketplace type template conditionals
*/
$show['product_selling_activity'] = $show['service_selling_activity'] = $show['service_buying_activity'] = $show['product_buying_activity'] = false;
if (isset($_SESSION['ilancedata']['user']['userid']) AND !empty($_SESSION['ilancedata']['user']['userid']))
{
	if ($ilconfig['globalauctionsettings_productauctionsenabled'])
	{
		if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'createproductauctions') == 'yes')
		{
			$show['product_selling_activity'] = true;
		}
		if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'productbid') == 'yes' OR $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'buynow') == 'yes')
		{
			$show['product_buying_activity'] = true;
		}
	}
	if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
	{
		if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'servicebid') == 'yes')
		{
			$show['service_selling_activity'] = true;
		}
		if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'createserviceauctions') == 'yes')
		{
			$show['service_buying_activity'] = true;
		}
	}
}

/*
* Initialize ip banning backend
*/
$ilance->ipban = construct_object('api.ipban');

/**
* Initialize admin breadcrumb and login info
*/
if (defined('LOCATION') AND LOCATION == 'admin')
{
	include_once(DIR_CORE . 'functions_admincp.php');
	$login_include_admin = admin_login_include();
	$ilanceversion = print_version();
}

/**
* Initialize client breadcrumb and login info
*/
if (defined('LOCATION') AND LOCATION == 'registration')
{
	$login_include = '{_registration}' . '...';
	if (!empty($_SESSION['ilancedata']['user']['builduser']))
	{
		$login_include = $ilance->common->login_include();
	}
}
else
{
	$login_include = $ilance->common->login_include();
}

/**
* Initialize referral tracker
*/
init_referral_tracker();

/**
* Initialize multibyte character encoding
*/
if (MULTIBYTE)
{
	mb_internal_encoding($ilconfig['template_charset']);
}

($apihook = $ilance->api('global_end')) ? eval($apihook) : false;

/**
* Initialize our very first header and set content and cache control to browser
*/
header('Content-type: text/html; charset="' . $ilconfig['template_charset'] . '"');  
header("Cache-Control: private");
header("Pragma: private");

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>