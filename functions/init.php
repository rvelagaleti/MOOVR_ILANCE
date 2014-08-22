<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 4.0.0 Build 8059
|| # -------------------------------------------------------------------- # ||
|| # Customer License # H7LhzCqPSNEABnY
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000–2014 ILance Inc. All Rights Reserved.          # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/
/**
* force some recommended php.ini configuration values
*/
@set_time_limit(0);
@ini_set('memory_limit', -1);
@ini_set('magic_quotes_gpc', 0);
@ini_set('magic_quotes_runtime', 0);
@ini_set('session.save_handler', 'user');

/**
* function overload makes easy to port ILance supporting only single-byte encoding for multibyte usage
* for example, where substr() is used, mb_substr() will be called automatically instead
*/
if (MULTIBYTE)
{
        // this must be set in php.ini or httpd.conf !!!!
        @ini_set('mbstring.func_overload', 7);
}

/**
* initialize our custom session identifier
*/
session_name('s');

/**
* Load up our function timer class used to get the exact time for scripts to be executed
*/
require_once(DIR_API . 'class.timer.inc.php');

/**
* defines if we should show any memory consumption debug information to the browser
*/
define('MEMORY_DEBUG', false);

/**
* defines if we want to output the function debug feature at the footer of the marketplace showing execution times
*/
define('DEBUG_FOOTER', false);

/**
* defines if we should explain all queries executed by the current script(s) in action
*/
define('DB_EXPLAIN', false);

require_once(DIR_CORE . 'functions_core.php');

/**
* important input filter handling constants
*/
define('TYPE_INT', 1);
define('TYPE_NUM', 2);
define('TYPE_STR', 3);
define('TYPE_NOTRIM', 4);
define('TYPE_NOHTML', 5);
define('TYPE_BOOL', 6);
define('TYPE_ARRAY', 7);

/**
* print a stop message when our url is being manipulated
*/
if (isset($_REQUEST['GLOBALS']) OR isset($_FILES['GLOBALS']))
{
        die('<strong>Fatal error:</strong> Request manipulation attempted.');
}
                   
/**
* defines if safemode in php is enabled or disabled
*/
define('SAFEMODE', (mb_strtolower(@ini_get('safe_mode')) == 'on' OR @ini_get('safe_mode') == 1) ? true : false);

/**
* fetch ip address of browsing visitor & from a proxy if applicable
*/
define('IPADDRESS', fetch_ip_address());
define('IPADDRESS_ALT', fetch_proxy_ip_address());

/**
* fetch user agent, referrer & protocol request
*/
define('USERAGENT', (!empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown'));
define('REFERRER', (!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'Not referred'));
define('PROTOCOL_REQUEST', ((!empty($_SERVER['HTTPS']) AND ($_SERVER['HTTPS'] == 'on' OR $_SERVER['HTTPS'] == '1')) ? 'https' : 'http'));

/**
* this token should not change during a valid session
*/
define('TOKEN', md5(USERAGENT . IPADDRESS . IPADDRESS_ALT));

/**
* defines if we should show the actual database error output to the browser within a textarea field
*/
define('DB_DEBUGMODE', false);

/**
* defines if we should hide the db error information in the textarea but actually show it in the view source as commenting?
*/
define('DB_DEBUGMODE_VIEWSOURCE', true);

/**
* defines if we should show or hide the actual template filename used to display the current page section
*/
define('TEMPLATE_DEBUG', false);

/**
* defines if the admincp should check the ilance web site to see if there is a new version
*/
define('VERSIONCHECK', false);

/**
* defines if we should disable any custom api code that might be causing any issues to the framework of ilance
*/
define('DISABLE_PLUGINAPI', false);

/**
* defines the script url
*/
define('SCRIPT_URI', (!empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : ''));

/**
* defines the server domain name
*/
$domainprefix = (mb_ereg('www', HTTP_SERVER)) ? 'www.' : '';
if (mb_ereg('www', $_SERVER['SERVER_NAME']))
{
	define('DOMAINNAME', $_SERVER['SERVER_NAME']);
}
else
{
	define('DOMAINNAME', $domainprefix . $_SERVER['SERVER_NAME']);
}
unset($domainprefix);

/**
* defines the entire url
*/
define('PAGEURL', PROTOCOL_REQUEST . '://' . DOMAINNAME . SCRIPT_URI);

/**
* initialize our ilance registry object
*/
$ilance = construct_object('api.ilance');

/**
* initialize our timer object so we know how long our functions take to execute
*/
$ilance->timer = new timer;

/**
* initialize and connect to our datastore
*/
require_once(DIR_API . 'class.db.inc.php');

/**
* initialize cache core
*/
$ilance->cachecore = construct_object('api.cache', false, array('uid' => false, 'sid' => false, 'rid' => false, 'styleid' => false, 'slng' => false));

/**
* set the payment gateway transaction log
*/
define('PAYMENTGATEWAYLOG', PAYMENTLOG . 'gateway.log');

/**
* set the curl path and application
*/
define('CURLPATH', '/usr/local/bin/curl');

/**
* set the path and filename to your certification file used for curl operations over ssl
*/
define('CURLCERT', DIR_SERVER_ROOT . DIR_FUNCT_NAME . '/' . DIR_CERTS_NAME . '/certificate.cer');

/**
* pre-installation folder checkup
*/
if (@file_exists(DIR_SERVER_ROOT . 'functions/connect.php.new') OR @file_exists(DIR_SERVER_ROOT . 'functions/config.php.new'))
{
	if (IPADDRESS != '127.0.0.1')
	{
		die('<strong>Fatal</strong>: There are pre-installation steps that require your attention.  Please review <a href="install/how-to-install.txt">how-to-install.txt</a> step 3.');
	}
}

/**
* license key post-installation checkup
*/
if ((defined('LICENSEKEY') AND LICENSEKEY == '') OR (!defined('LICENSEKEY')))
{
	if (IPADDRESS != '127.0.0.1')
	{
		die('<strong>Fatal</strong>: License key was not entered correctly within the config file.');
	}
}

/**
* used when installing the software - DO NOT REMOVE!
*/
if (defined('LOCATION') AND LOCATION == 'installer')
{
        // because we are installing a fresh copy, we don't have a default template folder
        $ilconfig = array();
        $ilconfig['template_folder'] = 'templates/default/';
        return;
}

/**
* installation folder protection
*/
if (@file_exists(DIR_SERVER_ROOT . 'install/installer.php'))
{
	if (IPADDRESS != '127.0.0.1')
	{
		die('<strong>Fatal</strong>: The installation folder still exists.  Please remove or at least rename the installer script within the installation folder.');
	}
}

/**
* set our $_SERVER globals with some GeoIP data
*/
$ilance->fetch_geoip_server_vars();

/**
* create our initial $ilconfig configuration datastore
*/
$ilance->init_configuration();

/**
* cross site $_POST request forgery protection
*/
$ilance->post_request_protection();

/**
* initialize our sessions engine
*/
$ilance->sessions = construct_object('api.sessions');

/**
* initialize our date and time localisation settings
*/
define('TIMESTAMPNOW', time());
define('DATETIME24H', date('Y-m-d H:i:s'));
define('DATETODAY', date('Y-m-d'));
define('TIMENOW', date('H:i:s'));
define('CURRENTHOUR', date('H'));
define('CURRENTMONTH', date('m'));
define('CURRENTDAY', date('d'));
define('CURRENTYEAR', date('Y'));
define('DATEYESTERDAY', date('Y-m-d', (TIMESTAMPNOW - 86400)));
define('DATETOMORROW', date('Y-m-d', (TIMESTAMPNOW + 2 * 86400)));
define('DATEIN30DAYS', date('Y-m-d', (TIMESTAMPNOW + 30 * 86400)));
define('DATEIN60DAYS', date('Y-m-d', (TIMESTAMPNOW + 60 * 86400)));
define('DATEIN90DAYS', date('Y-m-d', (TIMESTAMPNOW + 90 * 86400)));
define('DATEIN180DAYS', date('Y-m-d', (TIMESTAMPNOW + 180 * 86400)));;
define('DATEIN365DAYS', date('Y-m-d', (TIMESTAMPNOW + 365 * 86400)));
define('ONEDAYFROMNOW', date('Y-m-d', (TIMESTAMPNOW + 1 * 86400)));
define('THREEDAYSFROMNOW', date('Y-m-d', (TIMESTAMPNOW + 3 * 86400)));
define('SIXDAYSFROMNOW', date('Y-m-d', (TIMESTAMPNOW + 6 * 86400)));
define('NINEDAYSFROMNOW', date('Y-m-d', (TIMESTAMPNOW + 9 * 86400)));
define('ONEDAYAGO', date('Y-m-d', (TIMESTAMPNOW - 1 * 86400)));
define('THREEDAYSAGO', date('Y-m-d', (TIMESTAMPNOW - 3 * 86400)));
define('SIXDAYSAGO', date('Y-m-d', (TIMESTAMPNOW - 6 * 86400)));
define('NINEDAYSAGO', date('Y-m-d', (TIMESTAMPNOW - 9 * 86400)));
define('TWELVEDAYSAGO', date('Y-m-d', (TIMESTAMPNOW - 12 * 86400)));
define('FIFETEENDAYSAGO', date('Y-m-d', (TIMESTAMPNOW - 15 * 86400)));
define('TWENTYDAYSAGO', date('Y-m-d', (TIMESTAMPNOW - 20 * 86400)));
define('TWENTYNINEDAYSAGO', date('Y-m-d', (TIMESTAMPNOW - 29 * 86400)));
define('THIRTYDAYSAGO', date('Y-m-d', (TIMESTAMPNOW - 30 * 86400)));
define('SIXTYDAYSAGO', date('Y-m-d', (TIMESTAMPNOW - 60 * 86400)));
define('NINETYDAYSAGO', date('Y-m-d', (TIMESTAMPNOW - 90 * 86400)));
define('DATETIME1Y', date('Y-m-d H:i:s', (TIMESTAMPNOW + 365 * 86400)));
define('DATEINVOICEDUE', date('Y-m-d H:i:s', (TIMESTAMPNOW + $ilconfig['invoicesystem_maximumpaymentdays'] * 86400)));

/**
* read from our crawlers.xml and detect if this visitor is search bot/crawler
*/
$show['searchengine'] = is_search_crawler();

/**
* detect the server load limit
*/
$show['serveroverloaded'] = init_server_overload_checkup();
$show['ADMINCP_TEST_MODE'] = ADMINCP_TEST_MODE;

/**
* defines the AJAX posting url we're using
*/
define('AJAXURL', ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['ajax']);

($apihook = $ilance->api('init_configuration_end')) ? eval($apihook) : false;

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>