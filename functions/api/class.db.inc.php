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
if (defined('DB_SERVER_TYPE') AND DB_SERVER_TYPE != '')
{
	switch (DB_SERVER_TYPE)
	{
		case 'mysql':
		{
			if (!empty($_SESSION['DIR_SERVER_ROOT']))
			{
				include_once($_SESSION['DIR_SERVER_ROOT'] . DIR_FUNCT_NAME . '/' . DIR_API_NAME . '/class.database.inc.php');
				include_once($_SESSION['DIR_SERVER_ROOT'] . DIR_FUNCT_NAME . '/' . DIR_API_NAME . '/class.database_mysql.inc.php');
			}
			else
			{
				include_once(DIR_API . 'class.database.inc.php');
				include_once(DIR_API . 'class.database_mysql.inc.php');
			}
			$ilance->db = new ilance_mysql($ilance);
			$mysqlqcache = false;
			$mysqlver = $ilance->db->query_fetch("SELECT version() AS version", 0, null, __FILE__, __LINE__);
			if ($mysqlqcache = $ilance->db->query_fetch("SHOW VARIABLES LIKE 'have_query_cache'", 0, null, __FILE__, __LINE__))
			{
				if ($mysqlqcache['Value'] == 'YES')
				{
					$mysqlqcache = true;
				}
			}
			define('MYSQL_VERSION', $mysqlver['version']);
			define('MYSQL_ENGINE', (version_compare($mysqlver['version'], '4.0.18', '<')) ? 'TYPE' : 'ENGINE');
			define('MYSQL_TYPE', (version_compare($mysqlver['version'], '4.1', '<')) ? 'MyISAM' : 'MyISAM');
			define('MYSQL_QUERYCACHE', $mysqlqcache);
			unset($mysqlver, $mysqlqcache);
			break;
		}
		case 'mysqli':
		{
			if (!empty($_SESSION['DIR_SERVER_ROOT']))
			{
				include_once($_SESSION['DIR_SERVER_ROOT'] . DIR_FUNCT_NAME . '/' . DIR_API_NAME . '/class.database.inc.php');
				include_once($_SESSION['DIR_SERVER_ROOT'] . DIR_FUNCT_NAME . '/' . DIR_API_NAME . '/class.database_mysqli.inc.php');
			}
			else
			{
				include_once(DIR_API . 'class.database.inc.php');
				include_once(DIR_API . 'class.database_mysqli.inc.php');
			}
			$ilance->db = new ilance_mysqli($ilance);
			$mysqlqcache = false;
			$mysqlver = $ilance->db->query_fetch("SELECT version() AS version", 0, null, __FILE__, __LINE__);
			if ($mysqlqcache = $ilance->db->query_fetch("SHOW VARIABLES LIKE 'have_query_cache'", 0, null, __FILE__, __LINE__))
			{
				if ($mysqlqcache['Value'] == 'YES')
				{
					$mysqlqcache = true;
				}
			}
			define('MYSQL_VERSION', $mysqlver['version']);
			define('MYSQL_ENGINE', (version_compare($mysqlver['version'], '4.0.18', '<')) ? 'TYPE' : 'ENGINE');
			define('MYSQL_TYPE', (version_compare($mysqlver['version'], '4.1', '<')) ? 'MyISAM' : 'MyISAM');
			define('MYSQL_QUERYCACHE', $mysqlqcache);
			unset($mysqlver, $mysqlqcache);
			break;
		}
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>