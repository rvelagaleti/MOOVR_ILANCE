<?php
error_reporting(0);
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

define('LICENSEKEY', 'H7LhzCqPSNEABnY');
define('HTTP_SERVER', 'http://moovr.me/app/');
define('HTTPS_SERVER', 'http://moovr.me/app/');
define('HTTP_SERVER_OTHER', '');
define('HTTPS_SERVER_OTHER', '');
define('DIR_SERVER_ROOT', '/home/rvelagaleti/www/app/');
define('DIR_SERVER_ROOT_IMAGES', '/home/rvelagaleti/www/app/');
define('SUB_FOLDER_ROOT', '/app/');
define('HTTP_CDN_SERVER', '');
define('HTTPS_CDN_SERVER', '');

/**
* Marketplace identifier id
*/
define('SITE_ID', '001');

/**
* Folder name settings
*/
define('DIR_FUNCT_NAME', 'functions');
define('DIR_ADMIN_NAME', 'admincp');
define('DIR_ADMIN_ADDONS_NAME', 'addons');
define('DIR_CORE_NAME', 'core');
define('DIR_CRON_NAME', 'cron');
define('DIR_TMP_NAME', 'cache');
define('DIR_API_NAME', 'api');
define('DIR_XML_NAME', 'xml');
define('DIR_UPLOADS_NAME', 'uploads');
define('DIR_ATTACHMENTS_NAME', 'attachments');
define('DIR_FONTS_NAME', 'fonts');
define('DIR_SOUNDS_NAME', 'sounds');
define('DIR_SWF_NAME', 'swf');
define('DIR_CERTS_NAME', 'certs');
define('DIR_CSS_NAME', 'css');
define('DIR_JS_NAME', 'javascript');
define('DIR_DATASTORE_NAME', 'datastore');

/**
* defines the ability to show the new ILance HTML5/CSS3 design interface for PRODUCT or SERVICE.
*/
define('TEMPLATE_NEWUI', true);
define('TEMPLATE_NEWUI_MODE', 'PRODUCT');

chdir(DIR_SERVER_ROOT . DIR_FUNCT_NAME);
require_once('./global.php');

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>