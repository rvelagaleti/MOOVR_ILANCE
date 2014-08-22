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
error_reporting(E_ALL);
@set_time_limit(0);
@ini_set('memory_limit', '256M');
define('LOCATION', 'installer');
define('ILMIME', '.php');
/* this should match global.php */
define('VERSION', '4.0.0');
/* this should match $last_query_index in plugin_ilance.xml */
define('SQLVERSION', '898');
/* pre-installation folder checkup */
if (@file_exists('../functions/connect.php.new') OR @file_exists('../functions/config.php.new'))
{
	if ($_SERVER['REMOTE_ADDR'] != '127.0.0.1')
	{
		die('<strong>Fatal</strong>: There are pre-installation steps that require your attention.  Please review <a href="how-to-install.txt">how-to-install.txt</a> step 3 and step 7.');
	}
}
session_start();
include('./functions/functions_install.php');
if (empty($_REQUEST['step']))
{
	include('../functions/connect.php');
	if (DB_SERVER == '')
	{
		echo "<strong>Fatal:</strong> <em>DB_SERVER</em> is not setup in <em>../functions/connect.php</em>.  Please set this value before installation can continue.";
		exit();
	}
	else
	{
		$_SESSION['DB_SERVER'] = DB_SERVER;
	}
	if (DB_SERVER_PORT == '')
	{
		echo "<strong>Fatal:</strong> <em>DB_SERVER_PORT</em> is not setup in <em>../functions/connect.php</em>.  Please set this value before installation can continue.";
		exit();
	}
	else
	{
		$_SESSION['DB_SERVER_PORT'] = DB_SERVER_PORT;
	}
	if (DB_SERVER_USERNAME == '')
	{
		echo "<strong>Fatal:</strong> <em>DB_SERVER_USERNAME</em> is not setup in <em>../functions/connect.php</em>.  Please set this value before installation can continue.";
		exit();
	}
	else
	{
		$_SESSION['DB_SERVER_USERNAME'] = DB_SERVER_USERNAME;
	}
	if (DB_SERVER_PASSWORD == '')
	{
		$_SESSION['DB_SERVER_PASSWORD'] = '';
	}
	else
	{
		$_SESSION['DB_SERVER_PASSWORD'] = DB_SERVER_PASSWORD;
	}
	if (DB_DATABASE == '')
	{
		echo "<strong>Fatal:</strong> DB_DATABASE is not setup in <em>../functions/connect.php</em>.  Please set this value before installation can continue.";
		exit();
	}
	else
	{
		$_SESSION['DB_DATABASE'] = DB_DATABASE;
		// also set our table prefix for this database (can be blank or filled in)
		$_SESSION['DB_PREFIX'] = DB_PREFIX;
	}
}
if (!empty($_REQUEST['step']))
{
	$step = $_REQUEST['step'];
}
if (empty($_REQUEST['do']) OR !$_REQUEST['do'])
{
	$_REQUEST['do'] = 'install';
}
if (empty($step))
{
	$step = '0';
}
echo print_install_header();
// #### MBSTRING ###############################################
if (!extension_loaded('mbstring'))
{
	echo '<div class="redhlite"><span class="smaller"><b><font color="red">PHP Function: mb_detect_encoding()</font><br /></b></span>Your PHP build does not support multibyte character encoding.</div>';
	exit();
}
if (isset($step))
{
	// ######################### Start the install #######################
	if ($step == '0')
	{
		include('../functions/config.php');
		$mysqlver = $ilance->db->query_fetch("SELECT version() AS version", 0, null, __FILE__, __LINE__);
		$ver = $ilance->db->query_fetch("
			SELECT value
			FROM " . DB_PREFIX . "configuration
			WHERE name = 'current_version'
		", 1, null, __FILE__, __LINE__); // 1 denotes we should hide any db errors
		$version = $ver['value'];
		$sql = $ilance->db->query_fetch("
			SELECT value
			FROM " . DB_PREFIX . "configuration
			WHERE name = 'current_sql_version'
		", 1, null, __FILE__, __LINE__);  // 1 denotes we should hide any db errors
		$sqlversion = $sql['value'];
		if (empty($version) OR empty($sqlversion))
		{
			$version = VERSION;
			$sqlversion = SQLVERSION;
		}
		echo "<span style='float:right; color:#777; align:right'>Current Version: <strong>$version</strong><br />Current DB Version: <strong>$sqlversion</strong></span>";
		echo "<div style='padding-bottom:10px;font-size:13px'><a href='installer.php?do=install&step=1' style='color:green'><strong>Install a fresh version of ILance " . VERSION . "</strong></a></div>";
		echo "<div style=height:1px;width:100%;background-color:#ccc;margin-top:10px;margin-bottom:15px></div>";
		echo "<strong>Upgrade from older versions of ILance to $version</strong><br /><br />";
		if ($version == '3.0.6')
		{ // upgrade 3.0.6 to 3.0.7
			echo "<li /> <a href=installer.php?do=install&step=14><strong><span style='color:green'>Upgrade 3.0.6 to 3.0.7</strong></span></a><br />";
		}
		else if ($version == '3.0.7')
		{ // upgrade 3.0.7 to 3.0.8		
			echo "<li /> <a href=installer.php?do=install&step=15><strong><span style='color:green'>Upgrade 3.0.7 to 3.0.8</strong></span></a><br />";
		}
		else if ($version == '3.0.8')
		{ // upgrade 3.0.8 to 3.0.9
			echo "<li /> <a href=installer.php?do=install&step=16><strong><span style='color:green'>Upgrade 3.0.8 to 3.0.9</strong></span></a><br />";
		}
		else if ($version == '3.0.9')
		{ // upgrade 3.0.9 to 3.1.0
			echo "<li /> <a href=installer.php?do=install&step=17><span style='color:green'><strong>Upgrade 3.0.9 to 3.1.0</strong></span></a><br />";
		}
		else if (($version == '3.1.0' OR $version == '3.1.1' OR $version == '3.1.2') AND $sqlversion == '5')
		{ // upgrade 3.1.0 or 3.1.1 or 3.1.2 to 3.1.3 (sql version 6)
			echo "<li /> <a href=installer.php?do=install&step=23><strong><span style='color:green'>Upgrade from $version to 3.1.3</span></a><br />";
		}
		else if ($version == '3.1.3')
		{ // upgrade 3.1.3 to 3.1.4 (sql version 7)
			echo "<li /> <a href=installer.php?do=install&step=24><strong><span style='color:green'>Upgrade from 3.1.3 to 3.1.4</span></a><br />";
		}
		else if (($version == '3.1.4' OR $version == '3.1.4 PL 1'))
		{ // upgrade 3.1.4 to 3.1.5 (sql version 9)
			echo "<li /> <a href=installer.php?do=install&step=25><strong><span style='color:green'>Upgrade from 3.1.4 to 3.1.5</span></a><br />";
		}
		else if (($version == '3.1.5' OR $version == '3.1.5 PL 1'))
		{ // upgrade 3.1.5 to 3.1.6 (sql version 10)
			echo "<li /> <a href=installer.php?do=install&step=26><strong><span style='color:green'>Upgrade from 3.1.5 to 3.1.6</span></a><br />";
		}
		else if ($version == '3.1.6')
		{ // upgrade 3.1.6 to 3.1.7 (sql version 11)
			echo "<li /> <a href=installer.php?do=install&step=27><strong><span style='color:green'>Upgrade from 3.1.6 to 3.1.7</span></a><br />";
		}
		else if ($version == '3.1.7')
		{ // upgrade 3.1.7 to 3.1.8 (sql version 12)
			echo "<li /> <a href=installer.php?do=install&step=28><strong><span style='color:green'>Upgrade from 3.1.7 to 3.1.8</span></a><br />";
		}
		else if ($version == '3.1.8')
		{ // upgrade 3.1.8 to 3.1.9 (sql version 13)
			echo "<li /> <a href=installer.php?do=install&step=29><strong><span style='color:green'>Upgrade from 3.1.8 to 3.1.9</span></a><br />";
		}
		else if ($version == '3.1.9')
		{ // upgrade 3.1.9 to 3.2.0 (sql version 14)
			echo "<li /> <a href=installer.php?do=install&step=30><strong><span style='color:green'>Upgrade from 3.1.9 to 3.2.0</span></a><br />";
		}
		else if ($version == '3.2.0')
		{ // upgrade 3.2.0 to 3.2.1 (sql version 155)
			echo "<li /> <a href=installer.php?do=install&step=31><strong><span style='color:green'>Upgrade from 3.2.0 to 3.2.1</span></a><br />";
		}
		else if ($version == '3.2.1')
		{ // upgrade 3.2.1 to 4.0.0. (sql version 745)
			echo "<li /> <a href=installer.php?do=install&step=32><strong><span style='color:green'>Upgrade from 3.2.1 to 4.0.0</span></a><br />";
		}
		else
		{
			echo "<img src='../images/default/icons/checkmark.gif' border='0' /> <span style=color:#777;padding-left:6px>There is nothing to upgrade</span>";
		}
	}
	// #### INSTALLATION ###########################################################
	else if ($step == 1)
	{
		if ($_REQUEST['do'] == 'install')
		{
			$dontcontinue = 0;
			// Step 1: Check Server Requirements
			echo '<h1>Check Server Requirements</span><p>Entries marked in red should be reviewed by your host administrator for review and details on activating a module or extension for your PHP build.</h1>';
			// #### PHP VERSION ############################################
			if (function_exists("version_compare") AND version_compare(phpversion(), "5.2.0", ">="))
			{
				echo '<div class="greenhlite"><img src="../images/default/icons/checkmark.gif" border="0" /> <span class="smaller"><b>php</b></span></div>';
			}
			else
			{
				echo '<div class="redhlite"><span class="smaller"><b><font color="red">php</font></b></span><br />Your server requires PHP 5.2.0 or later installed.</div>';
				$dontcontinue = 1;
			}
			// #### GD #####################################################
			if (extension_loaded('gd'))
			{
				echo '<div class="greenhlite"><img src="../images/default/icons/checkmark.gif" border="0" /> <span class="smaller"><b>gd</b></span></div>';
			}
			else
			{
				echo '<div class="redhlite"><span class="smaller"><b><font color="red">gd</font></b></span><br />Your PHP build does not support GD Library and cannot use (thumbnail generation, re-size/scale, etc).</div>';
				$dontcontinue = 1;
			}
			// #### MBSTRING ###############################################
			if (extension_loaded('mbstring'))
			{
				echo '<div class="greenhlite"><img src="../images/default/icons/checkmark.gif" border="0" /> <span class="smaller"><b>mbstring</b></span></div>';
				$_SESSION['noencoding'] = '0';
			}
			else
			{
				echo '<div class="redhlite"><span class="smaller"><b><font color="red">mbstring</font></b></span><br />Your PHP build does not support multibyte character encoding.</div>';
				$_SESSION['noencoding'] = '1';
			}
			// #### OPENSSL ################################################
			if (extension_loaded('openssl'))
			{
				echo '<div class="greenhlite"><img src="../images/default/icons/checkmark.gif" border="0" /> <span class="smaller"><b>openssl</b></span></div>';
			}
			else
			{
				echo '<div class="redhlite"><span class="smaller"><b><font color="red">openssl()</font></b></span><br />Your PHP build does not support openssl().</div>';
			}
			// do we at least have curl?
			if (extension_loaded('curl'))
			{
				echo '<div class="greenhlite"><img src="../images/default/icons/checkmark.gif" border="0" /> <span class="smaller"><b>curl</b></span></div>';
			}
			else
			{
				echo '<div class="redhlite"><span class="smaller"><b><font color="red">PHP Extension: curl()</font></b></span><br />Your PHP build does not support curl()</div>';
				$dontcontinue = 1;
			}
			// #### ZLIB ###################################################
			if (extension_loaded('zlib'))
			{
				echo '<div class="greenhlite"><img src="../images/default/icons/checkmark.gif" border="0" /> <span class="smaller"><b>zlib</b></span></div>';
			}
			else
			{
				echo '<div class="redhlite"><span class="smaller"><b><font color="red">zlib</font></b></span><br />Your PHP build does not support zlib.</div>';
			}
			// #### MAX EXECUTION TIME ######################################
			if (get_cfg_var('max_execution_time') >= '30')
			{
				echo '<div class="greenhlite"><img src="../images/default/icons/checkmark.gif" border="0" /> <span class="smaller"><b>max_execution_time</b></span></div>';
			}
			else
			{
				echo '<div class="redhlite"><span class="smaller"><b><font color="red">max_execution_time</font></b></span><br />Max Execution Time should be greater or equal than 30.</div>';
			}
			include('../functions/config.php');
			if (function_exists("version_compare") AND version_compare(MYSQL_VERSION, "5.1", ">="))
			{
				echo '<div class="greenhlite"><img src="../images/default/icons/checkmark.gif" border="0" /> <span class="smaller"><b>mysql</b></span></div>';
			}
			else
			{
				echo '<div class="redhlite"><span class="smaller"><b><font color="red">mysql</font></b></span><br />MySQL version should be greater or equal to 5.1</div>';
			}
			if ($dontcontinue)
			{
				echo "<p><strong>Installation cannot continue (fix red items above)</strong> : <a href=installer.php?do=install&step=1>Step 1: Check Server Requirements</a></p><br />";
			}
			else
			{
				echo '<div style="padding-top:8px"><form method="post" action="installer.php" accept-charset="UTF-8">
				    <input type="hidden" name="step" value="2">
				    <input type="hidden" name="do" value="install">';
				echo '<input type="submit" value=" Next Step " style="font-size:9pt; font-weight: bold; height:25px; font-family: verdana" /></form></div>';
			}
		}
	}
	else if ($step == 2)
	{
		if ($_REQUEST['do'] == 'install')
		{
			// Step 2: Check Database
			echo '<h1>Check Database</h1><p>Entries marked as fatal should be reviewed by your host administrator for details on connecting to your database server. When installing ILance, you should have the following ready:<br /><li>Database Hostname (usually localhost)</li><li>Database Port Number (usually 3306)</li><li>Database Username</li><li>Database Password</li></p><strong>Information Returned From Your Server</strong><br />';
			if (mysql_connect($_SESSION['DB_SERVER'] . ':' . $_SESSION['DB_SERVER_PORT'], $_SESSION['DB_SERVER_USERNAME'], $_SESSION['DB_SERVER_PASSWORD']))
			{
				$db_connected = 1;
				echo '<div class="greenhlite">Database server host connection to <strong>' . $_SESSION['DB_SERVER'] . ':' . $_SESSION['DB_SERVER_PORT'] . '</strong> is ready for connections</div>';
				if (mysql_select_db($_SESSION['DB_DATABASE']))
				{
					echo '<div class="greenhlite">Database <strong>' . $_SESSION['DB_DATABASE'] . '</strong> is ready for connections</div>';
				}
				else
				{
					$db_connected = 0;
					echo '<div class="redhlite">Database <strong>' . $_SESSION['DB_DATABASE'] . '</strong> could not be selected (check <em>DB_DATABASE</em> in <em>../functions/connect.php</em>)</div>';
				}
			}
			else
			{
				$db_connected = 0;
				echo '<div class="redhlite">Database server host connection to <strong>' . $_SESSION['DB_DATABASE'] . '</strong> could not be selected (check <em>DB_DATABASE</em> in <em>../functions/connect.php</em>)</div>';
			}
			if ($db_connected)
			{
				// include our db settings to fetch the db table prefix we'll be using
				include('../functions/connect.php');
				if (DB_PREFIX == '')
				{
					echo '<div class="yellowhlite">You have not defined a database table prefix - change <em>DB_PREFIX</em> to something like <strong>v3_</strong> in <em>../functions/connect.php</em>)</div>';
				}
				echo '<form method="post" action="installer.php" accept-charset="UTF-8">';
				echo '<input type="hidden" name="step" value="4">';
				echo '<input type="hidden" name="do" value="install">';
				echo '<input type="submit" value=" Next Step " style="font-size:9pt; font-weight: bold; height:25px; font-family: verdana" /></form>';
			}
			else
			{
				echo '<form method="post" action="installer.php" accept-charset="UTF-8">';
				echo '<input type="hidden" name="step" value="4">';
				echo '<input type="hidden" name="do" value="install">';
				echo '<input type="submit" value=" Retry " style="font-size:9pt; font-weight: bold; height:25px; font-family: verdana" /></form>';
			}
		}
	}
	else if ($step == 4)
	{
		if ($_REQUEST['do'] == 'install')
		{
			include('../functions/config.php');
			// Step 4: Check Administrator Settings
			echo '<h1>Configure Administrator</h1><p>This account will be your main administrator account to login to your Admin CP.</p>';
			if (empty($_REQUEST['check']))
			{
				echo '<script type="text/javascript" src="' . HTTP_SERVER . 'functions/javascript/functions_password_strength.min.js"></script> 
				    <form method="post" name="add_admin" action="installer.php" accept-charset="UTF-8" onsubmit="return validatePwd()">
				    <input type="hidden" name="step" value="4">
				    <input type="hidden" name="do" value="install">
				    <input type="hidden" name="check" value="1">
			    
				    <table width="400" border="0" cellspacing="1" cellpadding="2">
				    <tr>
					    <td width="1%" nowrap="nowrap"><span style="size:16px; color:red">*</span>Admin email: </td>
					    <td width="1%"><input type="text" value="' . ((!empty($_SESSION['admin_email'])) ? $_SESSION['admin_email'] : '') . '" name="admin_email" class="input" style="width:210px" /></td>
				    </tr>
				    <tr>
					    <td width="1%" nowrap="nowrap"><span style="size:16px; color:red">*</span>Admin username: </td>
					    <td width="1%"><input type="text" value="' . ((!empty($_SESSION['admin_username'])) ? $_SESSION['admin_username'] : '') . '" name="username" class="input" style="width:210px" /></td>
				    </tr>
				    <tr>
					    <td width="1%" nowrap="nowrap"><span style="size:16px; color:red">*</span>Admin password: </td>
					    <td width="1%"><input type="password" onKeyUp="checkPassword(this.value)" value="' . ((!empty($_SESSION['admin_password'])) ? $_SESSION['admin_password'] : '') . '" name="password" class="input" style="width:210px" /></td>
					    <td><div style="border: 1px solid #acacac; width: 100px;" class="input"> 
					    <div id="progressBar" style="font-size: 1px; height: 18px; width: 0px; border: 1px solid white;"></div>
					    </div>
					    </td>
					    <td>
					    <p>Strength</p>
					    </td>
				    </tr>
				    <tr>
					    <td width="1%" nowrap="nowrap"><span style="size:16px; color:red">*</span>Verify password: </td>
					    <td width="1%"><input type="password" value="' . ((!empty($_SESSION['admin_password2'])) ? $_SESSION['admin_password2'] : '') . '" name="password2" class="input" style="width:210px" /></td>
				    </tr>
				    </table>
				    <br /> 
				    <input type="submit" value=" Next Step " style="font-size:9pt; font-weight: bold; height:25px; font-family: verdana" />
				    </form>';
			}
			if (isset($_REQUEST['check']) AND $_REQUEST['check'] == 1)
			{
				if (empty($_REQUEST['admin_email']))
				{
					echo "<strong>Fatal:</strong> <em>Admin email</em> was not entered.<br />";
					$adminfatal = 1;
				}
				else
				{
					$_SESSION['admin_email'] = $_REQUEST['admin_email'];
					echo 'Admin email <strong>' . $_SESSION['admin_email'] . '</strong> has been configured<br />';
				}
				if (empty($_REQUEST['username']))
				{
					echo "<strong>Fatal:</strong> <em>Admin username</em> was not entered.<br />";
					$adminfatal = 1;
				}
				else
				{
					$_SESSION['admin_username'] = $_REQUEST['username'];
					echo 'Admin username <strong>' . $_SESSION['admin_username'] . '</strong> has been configured<br />';
				}
				if (empty($_REQUEST['password']))
				{
					echo "<strong>Fatal:</strong> <em>Admin password</em> was not entered.<br />";
					$adminfatal = 1;
				}
				else
				{
					if ($_REQUEST['password'] == $_REQUEST['password2'])
					{
						$_SESSION['admin_password'] = $_REQUEST['password'];
						echo 'Admin password <strong>*****************</strong> has been configured<br />';
					}
					else
					{
						echo 'The admin passwords entered do not match.  Please try again.';
						$adminfatal = 1;
					}
				}
				if (isset($adminfatal) AND $adminfatal)
				{
					echo '<form method="post" action="installer.php" accept-charset="UTF-8">';
					echo '<input type="hidden" name="step" value="4">';
					echo '<input type="hidden" name="do" value="install">';
					echo '<input type="submit" value=" Retry " style="font-size:9pt; font-weight: bold; height:25px; font-family: verdana" /></form>';
				}
				else
				{
					echo '<form method="post" action="installer.php" accept-charset="UTF-8">';
					echo '<input type="hidden" name="step" value="5">';
					echo '<input type="hidden" name="do" value="install">';
					echo '<input type="submit" value=" Next Step " style="font-size:9pt; font-weight: bold; height:25px; font-family: verdana" /></form>';
				}
			}
		}
	}
	else if ($step == 5)
	{
		if ($_REQUEST['do'] == 'install')
		{
			// Step 5: Check Company / Licenee Information
			echo '<h1>Company and Site Name Details</h1><p>When setting up settings in this area please have the following ready:<br /><li>Company name</li><li>Site name / title</li><li>Company address</li><li>Company email (address to send email from)</li></p><p><strong>Additional Information</strong><br /><br />Please specify the company email address (where all emails will go out to customers from your marketplace) ie: support@mydomain.com.  This email should not be a personal email address.  When the marketplace sends an invoice or other related email to a member this will be the email address the user will see.</p>';
			if (empty($_REQUEST['check']))
			{
				echo '<form method="post" action="installer.php" accept-charset="UTF-8">
				    <input type="hidden" name="step" value="5">
				    <input type="hidden" name="do" value="install">
				    <input type="hidden" name="check" value="1">
				    <table width="400" border="0" cellspacing="1" cellpadding="2">
				    <tr>
					    <td width="1%" nowrap="nowrap"><span style="size:16px; color:red">*</span>Company name</td>
					    <td width="1%"><input type="text" value="' . ((!empty($_SESSION['company_name'])) ? $_SESSION['company_name'] : '') . '" name="company_name" class="input" /></td>
				    </tr>
				    <tr>
					    <td width="1%" nowrap="nowrap"><span style="size:16px; color:red">*</span>Web site name / title </td>
					    <td width="1%"><input type="text" value="' . ((!empty($_SESSION['site_name'])) ? $_SESSION['site_name'] : '') . '" name="site_name" class="input" /></td>
				    </tr>
				    <tr>
					    <td width="1%" nowrap="nowrap"><span style="size:16px; color:red">*</span>Company address </td>
					    <td width="1%"><input type="text" value="' . ((!empty($_SESSION['site_address'])) ? $_SESSION['site_address'] : '') . '" name="site_address" class="input" /></td>
				    </tr>
				    <tr>
					    <td width="1%" nowrap="nowrap"><span style="size:16px; color:red">*</span>Company email </td>
					    <td width="1%"><input type="text" value="' . ((!empty($_SESSION['site_email'])) ? $_SESSION['site_email'] : '') . '" name="site_email" class="input" /></td>
				    </tr>
				    </table>
				    <br />
				    <input type="submit" value=" Next Step " style="font-size:9pt; font-weight: bold; height:25px; font-family: verdana" />
				    </form>';
			}
			if (isset($_REQUEST['check']) AND $_REQUEST['check'] == 1)
			{
				if (empty($_REQUEST['company_name']))
				{
					echo "<strong>Fatal:</strong> <em>Company name</em> was not entered.<br />";
					$adminfatal = 1;
				}
				else
				{
					$_SESSION['company_name'] = $_REQUEST['company_name'];
					echo '<strong>Success:</strong> Company name <strong>' . $_SESSION['company_name'] . '</strong> has been configured<br />';
				}
				if (empty($_REQUEST['site_name']))
				{
					echo "<strong>Fatal:</strong> <em>Web site name / title</em> was not entered.<br />";
					$adminfatal = 1;
				}
				else
				{
					$_SESSION['site_name'] = $_REQUEST['site_name'];
					echo '<strong>Success:</strong> Web site name / title <strong>' . $_SESSION['site_name'] . '</strong> has been configured<br />';
				}
				if (empty($_REQUEST['site_address']))
				{
					echo "<strong>Fatal:</strong> <em>Company / site address</em> was not entered.<br />";
					$adminfatal = 1;
				}
				else
				{
					$_SESSION['site_address'] = $_REQUEST['site_address'];
					echo '<strong>Success:</strong> Company / site address <strong>' . $_SESSION['site_address'] . '</strong> has been configured<br />';
				}
				if (empty($_REQUEST['site_email']))
				{
					echo "<strong>Fatal:</strong> <em>Company email</em> was not entered.<br />";
					$adminfatal = 1;
				}
				else
				{
					$_SESSION['site_email'] = $_REQUEST['site_email'];
					echo '<strong>Success:</strong> Company email <strong>' . $_SESSION['site_email'] . '</strong> has been configured<br /><br />';
				}
				if (isset($adminfatal) AND $adminfatal == 1)
				{
					echo '<form method="post" action="installer.php" accept-charset="UTF-8">';
					echo '<input type="hidden" name="step" value="5">';
					echo '<input type="hidden" name="do" value="install">';
					echo '<input type="submit" value=" Retry " style="font-size:9pt; font-weight: bold; height:25px; font-family: verdana" /></form>';
				}
				else
				{
					echo '<form method="post" action="installer.php" accept-charset="UTF-8">';
					echo '<input type="hidden" name="step" value="6">';
					echo '<input type="hidden" name="do" value="install">';
					echo '<input type="submit" value=" Next Step " style="font-size:9pt; font-weight: bold; height:25px; font-family: verdana" /></form>';
				}
			}
		}
	}
	else if ($step == 6)
	{
		if ($_REQUEST['do'] == 'install')
		{
			// Step 6: Check Web Server Path Settings
			echo '<h1>Check Web Server Paths</h1><p>Entries marked as fatal should be reviewed carefully and changed accordingly to your server path configuration. Failure to set the server paths correctly may result in "blank pages" being shown after installation. Before you continue below, you should have <b>already opened</b> /functions/<b>config.php</b> and set the following variables in that file. The variables to edit are:</p>
			    <ul>
			    <li><b>HTTP_SERVER</b> : This is a url like http://www.ilance.com/ with an <b>ending slash</b></li>
			    <li><b>HTTPS_SERVER</b> : This is a url like http<b>s</b>://www.ilance.com/ with an <b>ending slash</b></li>
			    <li><b>HTTP_SERVER_OTHER</b> : This is a url like http://www.ilance2.com/ with an <b>ending slash</b></li>
			    <li><b>HTTPS_SERVER_OTHER</b> : This is a url like http<b>s</b>://www.ilance2.com/ with an <b>ending slash</b></li>
			    <li><b>DIR_SERVER_ROOT</b> : This is a full server path like /home/domain/www/ with an <b>start and ending slashs</b></li>
			    <li><b>DIR_SERVER_ROOT_IMAGES</b> : Set this to the same as <b>DIR_SERVER_ROOT</b></li>
			    <li><b>SUB_FOLDER_ROOT</b> : If installed on root of domain enter <b>/</b> otherwise enter <b>/foldername/</b></li>
			    </ul>';
			echo '<li><a href="pathxp.txt" target="_blank">View example configuration scenerio for a <strong>Windows server</strong> running WAMP</a></li>';
			echo '<li><a href="pathlinux.txt" target="_blank">View example configuration scenerio for a <strong>Linux server</strong> running LAMP</a></li><br />';
			echo 'ILance installer will give you best results for your server below.  You may or may not need to change anything.  This is just for overview:<br /><br />';
			echo '<li>(installer thinks) <b>HTTP_SERVER</b> could be <strong>' . str_replace('/install/installer.php', '', $_SERVER['HTTP_REFERER']) . '/</strong></li>';
			echo '<li>(installer thinks) <b>HTTPS_SERVER</b> could be <strong>' . str_replace('/install/installer.php', '', $_SERVER['HTTP_REFERER']) . '/</strong></li>';
			echo '<li>(installer thinks) <b>DIR_SERVER_ROOT</b> could be <strong>' . str_replace('/install/installer.php', '', $_SERVER['SCRIPT_FILENAME']) . '/</strong></li>';
			echo '<li>(installer thinks) <b>DIR_SERVER_ROOT_IMAGES</b> could be <strong>' . str_replace('/install/installer.php', '', $_SERVER['SCRIPT_FILENAME']) . '/</strong></li>';
			echo '<li>(installer thinks) <b>SUB_FOLDER_ROOT</b> could be <strong>' . str_replace('/install/installer.php', '', $_SERVER['SCRIPT_NAME']) . '/</strong></li>';
			echo '<br />Please verify your setup before continuing.<br /><br />';
			echo '<form method="post" action="installer.php" accept-charset="UTF-8">';
			echo '<input type="hidden" name="step" value="7">';
			echo '<input type="hidden" name="do" value="install">';
			echo '<input type="submit" value=" Next Step " style="font-size:9pt; font-weight: bold; height:25px; font-family: verdana" /></form>';
		}
	}
	else if ($step == 7)
	{
		if ($_REQUEST['do'] == 'install')
		{
			// Step 7: Save Settings to Main Configuration File
			echo '<h1>Writable Files and Folders</h1>
			<p>View the following list of files and folders that require read and write permissions on your web site.  Please CHMOD the following manually:<br />
			<ul>
			<li>CHMOD 777 <b>./cache/</b> and all folders within</li>
			<li>CHMOD 777 <b>./uploads/</b> and all folders within</li>
			<li>CHMOD 777 <b>./sitemap.xml</b></li>
			<li>CHMOD 777 <b>./images/default/categoryicons/</b></li>
			<li>CHMOD 777 <b>./images/default/categoryheros/</b></li>
			<li>CHMOD 777 <b>./images/default/heros/</b></li>
			</ul></p>';
			$fatal = 0;
			if (!is_writable('../cache/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../cache/css/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../cache/datastore/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../cache/didyoumean/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../cache/javascript/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../cache/paymentlog/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../cache/shippinglog/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../sitemap.xml'))
			{
				$fatal = 1;
			}
			if (!is_writable('../uploads/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../uploads/attachments/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../uploads/attachments/auctions/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../uploads/attachments/auctions/original/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../uploads/attachments/auctions/resized/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../uploads/attachments/auctions/resized/full/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../uploads/attachments/auctions/resized/gallery/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../uploads/attachments/auctions/resized/mini/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../uploads/attachments/auctions/resized/search/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../uploads/attachments/auctions/resized/snapshot/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../uploads/attachments/bids/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../uploads/attachments/pmbs/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../uploads/attachments/portfolios/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../uploads/attachments/portfolios/original/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../uploads/attachments/portfolios/resized/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../uploads/attachments/portfolios/resized/full/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../uploads/attachments/portfolios/resized/gallery/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../uploads/attachments/portfolios/resized/mini/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../uploads/attachments/portfolios/resized/search/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../uploads/attachments/portfolios/resized/snapshot/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../uploads/attachments/profiles/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../uploads/attachments/profiles/original/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../uploads/attachments/profiles/resized/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../uploads/attachments/profiles/resized/full/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../uploads/attachments/profiles/resized/gallery/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../uploads/attachments/profiles/resized/mini/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../uploads/attachments/profiles/resized/search/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../uploads/attachments/profiles/resized/snapshot/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../uploads/attachments/ws/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../images/default/categoryicons/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../images/default/categoryheros/'))
			{
				$fatal = 1;
			}
			if (!is_writable('../images/default/heros/'))
			{
				$fatal = 1;
			}
			if ($fatal == 0)
			{
				echo '<div class="yellowhlite"><img src="../images/default/icons/checkmark.gif" border="0" /> <strong>All folder and file permissions are properly set!</strong></div>';
				echo '<form method="post" action="installer.php" accept-charset="UTF-8">';
				echo '<input type="hidden" name="step" value="8">';
				echo '<input type="hidden" name="do" value="install">';
				echo '<input type="submit" value=" Next Step " style="font-size:9pt; font-weight: bold; height:25px; font-family: verdana" /></form>';
			}
			else
			{
				echo '<form method="post" action="installer.php" accept-charset="UTF-8">';
				echo '<input type="hidden" name="step" value="7">';
				echo '<input type="hidden" name="do" value="install">';
				echo '<input type="submit" value=" Retry " style="font-size:9pt; font-weight: bold; height:25px; font-family: verdana" /></form>';
			}
		}
	}
	else if ($step == 8)
	{
		if ($_REQUEST['do'] == 'install')
		{
			// Step 8: Create Database Table Schema
			echo '<h1>Create Database Schema</h1><p>This step will allow you to choose if you would like to install a fresh database schema or skip this step entirely (no new tables will be created).  Your database should already be created at this point.<br /><br /><strong>Please select one of the following options</strong><br />';
			echo '<form method="post" action="installer.php" accept-charset="UTF-8">';
			echo '<input type="hidden" name="do" value="install">';
			echo '<label for="schema1"><input type="radio" name="step" id="schema1" value="8.1" checked="checked"> Install fresh database table schema <!--[ <a href="../functions/xml/database_schema.xml" target="_blank">view xml version</a> ]--></label><br />';
			echo '<label for="schema2"><input type="radio" name="step" id="schema2" value="9"> Skip this step (do not install database schema)</label><br /><br /><input type="submit" value="Continue" style="font-size:9pt; font-weight: bold; height:25px; font-family: verdana" /></form>';
		}
	}
	else if ($step == '8.1')
	{
		if ($_REQUEST['do'] == 'install')
		{
			// load our main core config (this script will die within init.php so it doesn't 
			// continue to load all the default configuration values since we haven't created
			// them just yet.
			include('../functions/config.php');
			// Step 8: Create Database Table Schema
			echo '<h1>Database Table Schema Creation</h1><p>From the previous menu selection you selected create new database table schema.  Please find the results below for this operation:<br /><br />';
			// let's create the tables
			if (!empty($_SESSION['admin_username'])
				AND !empty($_SESSION['admin_password'])
				AND !empty($_SESSION['company_name'])
				AND !empty($_SESSION['site_name'])
				AND !empty($_SESSION['site_address'])
				AND !empty($_SESSION['site_email']))
			{
				echo create_db_schema();
				echo '<br /><form method="post" action="installer.php" accept-charset="UTF-8">';
				echo '<input type="hidden" name="step" value="9" />';
				echo '<input type="hidden" name="do" value="install" />';
				echo '<input type="submit" value=" Next Step " style="font-size:9pt; font-weight: bold; height:25px; font-family: verdana" /></form>';
			}
			else
			{
				echo 'Installer cannot create new database tables due to session timeout.  To avoid seeing this message, please ensure you start the installation process from the beginning (ie: Step 1) and continue through all steps.<br />';
				echo '<form method="post" action="installer.php" accept-charset="UTF-8">';
				echo '<input type="hidden" name="do" value="install">';
				echo '<input type="submit" value=" Return to Step 1 " style="font-size:9pt; font-weight: bold; height:25px; font-family: verdana" /></form>';
			}
		}
	}
	else if ($step == '9')
	{
		if ($_REQUEST['do'] == 'install')
		{
			echo '<h1>Import v' . VERSION . ' Language Phrases</h1><p>This process will import official language phrases into your database.  The valid language files exist within <em><strong>/install/xml/</strong></em> folder.<br /><br /><strong>Please review and make desired selections from the following options.  <span style="color:#990000">Warning</span> - this import process consumes server memory & resources. If you experience a blank page during this step, please configure your php.ini by allocating additional memory resources or you may specify how many phrases to import at once (in bulk) below.</strong><br /><br />';
			echo '<form method="post" action="installer.php" accept-charset="UTF-8">';
			echo '<input type="hidden" name="do" value="install" />';
			echo '<label for="eng"><input type="checkbox" name="importenglish" id="eng" value="1" checked="checked" /> Import English Phrases</label><br /><br />';
			echo '<label for="schema1"><input type="radio" name="step" id="schema1" value="9.1" checked="checked" /> Proceed and import selected language phrases into my database</label><br />';
			echo '<label for="schema2"><input type="radio" name="step" id="schema2" value="10" /> Skip this step and continue to Step 10: Import v' . VERSION . ' Email Templates</label><br />
			    <br /><table><tr><td>Phrases to import at once:</td><td><input type="text" name="perpage" value="5000" size="5" /></td></tr></table>
			    <br /><input type="submit" value=" Import " style="font-size:9pt; font-weight: bold; height:25px; font-family: verdana" /></form>';
		}
	}
	else if ($step == '9.1')
	{
		if ($_REQUEST['do'] == 'install')
		{
			include('../functions/config.php');
			// Step 9.1: Import Template / Phrases
			echo '<h1>Import Language Phrases</h1><p>From the previous menu, you selected import selected language phrases into the database.  Please find the results below for this operation:<br /><br />';
			// let's import the languages
			if (empty($_REQUEST['importenglish']))
			{
				echo 'Installer cannot import any phrases since you did not select any language phrases from the previous menu.  <br /><br /><strong><a href="installer.php?do=install&step=9">Return to previous menu to make selections using the checkboxes</a>.</strong><br />';
			}
			// start from phrase (if none specified, it will start from 0 -> xxxx)
			$fromphrase = 0;
			if (isset($_REQUEST['fromphrase']))
			{
				$fromphrase = intval($_REQUEST['fromphrase']);
			}
			$perpage = 5000;
			if (isset($_REQUEST['perpage']))
			{
				// user wants to only import so many phrases per page
				$perpage = intval($_REQUEST['perpage']);
			}
			// import styles and templates
			echo import_templates();
			// english
			if (isset($_REQUEST['importenglish']) AND $_REQUEST['importenglish'] == 1)
			{
				echo import_language_phrases(10000, 0);
			}
			echo '<br /><form method="get" action="installer.php" accept-charset="UTF-8">';
			echo '<input type="hidden" name="step" value="10" />';
			echo '<input type="hidden" name="do" value="install" />';
			echo '<input type="submit" value=" Next Step " style="font-size:9pt; font-weight: bold; height:25px; font-family: verdana" /></form>';
		}
	}
	else if ($step == '10')
	{
		if ($_REQUEST['do'] == 'install')
		{
			// Step 10: Import v".VERSION." Email Templates
			echo '<h1>Import v' . VERSION . ' Email Templates</h1><p>This process will allow you to import fresh email templates into your database (for all existing languages within your database).  The valid email files exist within <em><strong>/install/xml/</strong></em> folder.<br /><br /><strong>Please review and make desired selections from the following options</strong><br /><br />';
			echo '<form method="post" action="installer.php" accept-charset="UTF-8">';
			echo '<input type="hidden" name="do" value="install" />';
			echo '<label for="eng"><input type="checkbox" name="importenglish" id="eng" value="1" checked="checked" /> Import English (US) Email Templates</label><br /><br />';
			echo '<label for="schema1"><input type="radio" name="step" id="schema1" value="10.1" checked="checked" /> Proceed and import selected email templates into my database</label><br />';
			echo '<label for="schema2"><input type="radio" name="step" id="schema2" value="12" /> Skip this step and read final installation notes</label><br /><br /><input type="submit" value="Continue" style="font-size:9pt; font-weight: bold; height:25px; font-family: verdana" /></form>';
		}
	}
	else if ($step == '10.1')
	{
		if ($_REQUEST['do'] == 'install')
		{
			include('../functions/config.php');
			// Step 9.1: Import Template / Phrases
			echo '<h1>Import Email Templates</h1><p>From the previous menu, you selected import selected email templates into the database.  Please find the results below for this operation:<br /><br /><strong>Please review output of the email templates import below</strong><br /><br />';
			// let's import the templates
			if (empty($_REQUEST['importenglish']))
			{
				echo 'Installer cannot import any email templates since you did not select any language from the previous menu.  <br /><br /><strong><a href="installer.php?do=install&step=10">Return to previous menu to make selections using the checkboxes</a>.</strong><br />';
			}
			// english
			if (isset($_REQUEST['importenglish']) AND $_REQUEST['importenglish'] == 1)
			{
				echo import_email_templates();
			}
			echo '<br /><form method="post" action="installer.php" accept-charset="UTF-8">';
			echo '<input type="hidden" name="step" value="12" />';
			echo '<input type="hidden" name="do" value="install" />';
			echo '<input type="submit" value=" Next Step " style="font-size:9pt; font-weight: bold; height:25px; font-family: verdana" /></form>';
		}
	}
	else if ($step == '12')
	{
		if ($_REQUEST['do'] == 'install')
		{
			// Step 12: Final Installation Notes
			echo '<h1>Final Installation Notes</h1><br /> Please review the following installation notes:<br /><br /><strong>Final Installation Notes</strong><br /><br />';
			echo '<li> Please remember to <strong>delete</strong> the installation folder (./install/)</li>';
			echo '<li> Please remember to register on the <a href="http://www.ilance.com/forum/" target="_blank">ILance Forum</a> to stay current with the latest updates and software releases</li>';
			echo "<br /><br /><a href=\"../main.php\"><b>Launch ILance " . VERSION . "</b></a> or <a href=\"../admincp/login.php\"><b>Launch ILance " . VERSION . " Admin CP</b></a><br /><br />\n";
		}
	}
	// #### UPGRADE 3.0.6 to 3.0.7 #################################################
	else if ($step == '14')
	{
		if ($_REQUEST['do'] == 'install')
		{
			include('../functions/config.php');
			include(DIR_SERVER_ROOT . 'install/functions/3.0.6-3.0.7.php');
		}
	}
	// #### UPGRADE 3.0.7 to 3.0.8 #################################################
	else if ($step == '15')
	{
		if ($_REQUEST['do'] == 'install')
		{
			include('../functions/config.php');
			include(DIR_SERVER_ROOT . 'install/functions/3.0.7-3.0.8.php');
		}
	}
	// #### UPGRADE 3.0.8 to 3.0.9 #################################################
	else if ($step == '16')
	{
		if ($_REQUEST['do'] == 'install')
		{
			include('../functions/config.php');
			include(DIR_SERVER_ROOT . 'install/functions/3.0.8-3.0.9.php');
		}
	}
	// #### UPGRADE 3.0.9 to 3.1.0 #################################################
	else if ($step == '17')
	{
		if ($_REQUEST['do'] == 'install')
		{
			include('../functions/config.php');
			include(DIR_SERVER_ROOT . 'install/functions/3.0.9-3.1.0.php');
		}
	}
	// #### UPGRADE 3.1.0 to 3.1.3 #################################################
	else if ($step == '23')
	{
		if ($_REQUEST['do'] == 'install')
		{
			include('../functions/config.php');
			include(DIR_SERVER_ROOT . 'install/functions/3.1.0-3.1.3.php');
		}
	}
	// #### UPGRADE 3.1.3 to 3.1.4 #################################################
	else if ($step == '24')
	{
		if ($_REQUEST['do'] == 'install')
		{
			include('../functions/config.php');
			include(DIR_SERVER_ROOT . 'install/functions/3.1.3-3.1.4.php');
		}
	}
	// #### UPGRADE 3.1.4 to 3.1.5 #################################################
	else if ($step == '25')
	{
		if ($_REQUEST['do'] == 'install')
		{
			include('../functions/config.php');
			include(DIR_SERVER_ROOT . 'install/functions/3.1.4-3.1.5.php');
		}
	}
	// #### UPGRADE 3.1.5 to 3.1.6 #################################################
	else if ($step == '26')
	{
		if ($_REQUEST['do'] == 'install')
		{
			include('../functions/config.php');
			include(DIR_SERVER_ROOT . 'install/functions/3.1.5-3.1.6.php');
		}
	}
	// #### UPGRADE 3.1.6 to 3.1.7 #################################################
	else if ($step == '27')
	{
		if ($_REQUEST['do'] == 'install')
		{
			include('../functions/config.php');
			include(DIR_SERVER_ROOT . 'install/functions/3.1.6-3.1.7.php');
		}
	}
	// #### UPGRADE 3.1.7 to 3.1.8 #################################################
	else if ($step == '28')
	{
		if ($_REQUEST['do'] == 'install')
		{
			include('../functions/config.php');
			include(DIR_SERVER_ROOT . 'install/functions/3.1.7-3.1.8.php');
		}
	}
	// #### UPGRADE 3.1.8 to 3.1.9 #################################################
	else if ($step == '29')
	{
		if ($_REQUEST['do'] == 'install')
		{
			include('../functions/config.php');
			include(DIR_SERVER_ROOT . 'install/functions/3.1.8-3.1.9.php');
		}
	}
	// #### UPGRADE 3.1.9 to 3.2.0 #################################################
	else if ($step == '30')
	{
		if ($_REQUEST['do'] == 'install')
		{
			include('../functions/config.php');
			include(DIR_SERVER_ROOT . 'install/functions/3.1.9-3.2.0.php');
		}
	}
	// #### UPGRADE 3.2.0 to 3.2.1 #################################################
	else if ($step == '31')
	{
		if ($_REQUEST['do'] == 'install')
		{
			include('../functions/config.php');
			include(DIR_SERVER_ROOT . 'install/functions/3.2.0-3.2.1.php');
		}
	}
	// #### UPGRADE 3.2.1 to 4.0.0 #################################################
	else if ($step == '32')
	{
		if ($_REQUEST['do'] == 'install')
		{
			include('../functions/config.php');
			include(DIR_SERVER_ROOT . 'install/functions/3.2.1-4.0.0.php');
		}
	}
}
echo print_install_footer();

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>