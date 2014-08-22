<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 4.0.0 Build 8059
|| # -------------------------------------------------------------------- # ||
|| # Customer License # H7LhzCqPSNEABnY
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000ñ2014 ILance Inc. All Rights Reserved.          # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/

// #### MASTER DB CONFIGURATION (REQUIRED) #####################################
define('DB_DATABASE', 'ilance_app');
define('DB_SERVER', 'localhost');
define('DB_SERVER_PORT', '3306');
define('DB_SERVER_USERNAME', 'ilance_admin');
define('DB_SERVER_PASSWORD', '01=xfA2o~&MF');
define('DB_PERSISTANT_MASTER', 1);

// #### SLAVE DB CONFIGURATION (OPTIONAL) ######################################
define('DB_SERVER2', 'localhost');
define('DB_SERVER_PORT2', '3306');
define('DB_SERVER_USERNAME2', '');
define('DB_SERVER_PASSWORD2', '');
define('DB_PERSISTANT_SLAVE', 1);

// #### OTHER DB CONSTANTS #####################################################
define('DB_SERVER_TYPE', 'mysqli');
define('DB_PREFIX', 'ilance_');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', 'utf8_general_ci');

// #### memcache server 1 ######################################################
$i = 1;
$memcacheserver = array();
$memcacheserver[$i]['server'] = '127.0.0.1';
$memcacheserver[$i]['port'] = '11211';
$memcacheserver[$i]['persistent'] = true;
$memcacheserver[$i]['weight'] = '1'; // give more weight on larger ram servers
$memcacheserver[$i]['timeout'] = '1';
$memcacheserver[$i]['retry'] = '15';
// #### memcache server 2 ######################################################
/*$i++;
$memcacheserver[$i]['server'] = '127.0.0.1';
$memcacheserver[$i]['port'] = '11211';
$memcacheserver[$i]['persistent'] = true;
$memcacheserver[$i]['weight'] = '1';
$memcacheserver[$i]['timeout'] = '1';
$memcacheserver[$i]['retry'] = '15';*/
unset($i);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>