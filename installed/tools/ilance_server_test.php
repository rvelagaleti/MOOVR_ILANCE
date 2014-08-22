<?php

$db_host = 'localhost';		// database host address - on most servers it is localhost
$db_username = 'root';		// database username
$db_password = '';			// database password
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html dir="ltr" lang="us">
<head>
<title>ILance test script</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="-1">
<meta http-equiv="Cache-Control" content="no-cache">
<meta name="robot" content="noindex, nofollow">
<style id="css" type="text/css">
<!-- 
.h1
{
    font-family: Century Gothic, Lucida Grande, Trebuchet MS, Verdana, Sans-Serif;
    font-size: 18px;
    letter-spacing: -1px;
    font-weight: bold;
    margin: 10px 0 7px 0;
    color: #000000;
}
div.redhlite
{
	padding-right: 4px;
	border-top: #d95b5b 1px solid;
	padding-left: 4px;
	padding-bottom: 4px;
	margin: 5px auto;
	padding-top: 4px;
	border-bottom: #d95b5b 1px solid;
	background-color: #fffafa;
}
div.bluehlite
{
	padding-right: 4px;
	border-top: #5a7edc 1px solid;
	padding-left: 4px;
	padding-bottom: 4px;
	margin: 5px auto;
	padding-top: 4px;
	border-bottom: #5a7edc 1px solid;
	background-color: #fcfdff;
}
//-->
</style>
</head>
<body>
<?php
$connection = @mysql_connect($db_host, $db_username, $db_password);

		echo '<h1>ILance Pre-installation server test</h1><p>Entries marked in red should be reviewed by your host administrator for review and details on activating a module or extension for your PHP build.</p>';
        
		// #### PHP VERSION ############################################
		if (function_exists("version_compare") AND version_compare(phpversion(), "5.2.0", ">="))
		{
			echo '<div class="bluehlite"><span class="smaller"><b><font color="#000000">PHP Version: ' . phpversion() . '</font><br />
			</b></span>Your server supports the PHP functions available in PHP 5.2.0 and higher.</div>';
		}
		else
		{
			echo '<div class="redhlite"><span class="smaller"><b><font color="red">PHP Version: ' . phpversion() . '</font><br />
			</b></span>Your server requires PHP 5.2.0 or later installed.</div>';
		}
                
        // #### BC MATH ################################################
        if (function_exists("bcmod"))
		{
			echo '<div class="bluehlite"><span class="smaller"><b><font color="#000000">PHP Function: bcmod()</font><br />
			</b></span>Your PHP build supports the math functions in PHP.</div>';
		}
		else
		{
			echo '<div class="redhlite"><span class="smaller"><b><font color="red">PHP Function: bcmod()</font><br />
			</b></span>Your PHP build requires the math functions.  These functions are only available if PHP was configured with --enable-bcmath.<br /> Notice: Extension is required for Connections tab in AdminCP</div>';
		}
		
		// #### GD #####################################################
		if (extension_loaded('gd'))
		{
			echo '<div class="bluehlite"><span class="smaller"><b><font color="#000000">PHP Extension: GD</font><br />
			</b></span>Your PHP build supports GD Library (thumbnail generation, re-size/scale, etc).</div>';
		}
		else
		{
			echo '<div class="redhlite"><span class="smaller"><b><font color="red">PHP Extension: GD</font><br />
			</b></span>Your PHP build does not support GD Library and cannot use (thumbnail generation, re-size/scale, etc).</div>';
		}

		// #### MBSTRING ###############################################
		if(extension_loaded('mbstring'))
		{
			echo '<div class="bluehlite"><span class="smaller"><b><font color="#000000">PHP Function: mb_detect_encoding()</font><br />
			</b></span>Your PHP build supports multibyte character encoding.</div>';			
		}
		else
		{
			echo '<div class="redhlite"><span class="smaller"><b><font color="red">PHP Function: mb_detect_encoding()</font><br />
			</b></span>Your PHP build does not support multibyte character encoding. This PHP extension is required for language support.</div>';
		}

		// #### OPENSSL ################################################
		if (extension_loaded('openssl'))
		{
			echo '<div class="bluehlite"><span class="smaller"><b><font color="#000000">PHP Extension: openssl()</font><br />
			</b></span>Your PHP build supports openssl().</div>';
		}
		else
		{
			echo '<div class="redhlite"><span class="smaller"><b><font color="red">PHP Extension: openssl()</font><br />
			</b></span>Your PHP build does not support openssl().</div>';
		}
		
		// do we at least have curl?
		if (extension_loaded('curl'))
		{
			echo '<div class="bluehlite"><span class="smaller"><b><font color="#000000">PHP Extension: curl()</font><br />
			</b></span>Your PHP build support curl().</div>';	
		}
		else
		{
			echo '<div class="redhlite"><span class="smaller"><b><font color="red">PHP Extension: curl()</font><br />
			</b></span>Your PHP build does not support curl() which is required for Bulk Upload</div>';
		}
		
		// #### ZLIB ###################################################
		if (extension_loaded('zlib'))
		{
			echo '<div class="bluehlite"><span class="smaller"><b><font color="#000000">PHP Extension: zlib</font><br />
			</b></span>Your PHP build support zlib.</div>';
		}
		else
		{
			echo '<div class="redhlite"><span class="smaller"><b><font color="red">PHP Extension: zlib</font><br />
			</b></span>Your PHP build does not support zlib.</div>';
		}
		
		// #### MEMORYLIMIT ############################################
		if (mb_substr(get_cfg_var('memory_limit'), 0, -1) >= '64')
		{
			echo '<div class="bluehlite"><span class="smaller"><b><font color="#000000">PHP Value: Memory Limit</font><br />
			</b></span>Memory Limit >= 64.</div>';
		}
		else
		{
			echo '<div class="redhlite"><span class="smaller"><b><font color="red">PHP Value: Memory Limit</font><br />
			</b></span>Memory Limit should be greater or equal than 64.</div>';
		}
		
		
		// #### MAX EXECUTION TIME ######################################
		if (get_cfg_var('max_execution_time') >= '300')
		{
			echo '<div class="bluehlite"><span class="smaller"><b><font color="#000000">PHP Value: Max Execution Time</font><br />
			</b></span>Max Execution Time >= 300.</div>';
		}
		else
		{
			echo '<div class="redhlite"><span class="smaller"><b><font color="red">PHP Value: Max Execution Time</font><br />
			</b></span>Max Execution Time should be greater or equal than 300. If you have set default value 30 then you can also try to install ILance script. On some servers during installation it is required to have execution time greater than 30.</div>';
		}
		
		$mysqlver = "SELECT version() AS version";

		if($result = @mysql_query($mysqlver))
		{
		    $row = mysql_fetch_assoc($result);
		
			if (function_exists("version_compare") AND version_compare($row['version'], "5.1", ">="))
			{
				echo '<div class="bluehlite"><span class="smaller"><b><font color="#000000">MySQL version: ' . $row['version'] . '</font><br />
				</b></span>MySQL version >= 5.1.x</div>';
			}
			else
			{
				echo '<div class="redhlite"><span class="smaller"><b><font color="red">MySQL version: ' . $row['version'] . '</font><br />
				</b></span>MySQL version should be greater or equal than 5.1.x however ILance still supports backward compatibility for MySQL < 5.1.x. If you use large number of categories then you will need MySQL 5.1.x</div>';
			}
			mysql_free_result($result);
		}
		else 
		{
				echo '<div class="redhlite"><span class="smaller"><b><font color="red">MySQL connection error</font><br />
				</b></span>I could not connect to your MySQL server. If you want to test your database then you should fill in properly fields at the begining of this file</div>';

		}
		
		function get_folder_name($php_self)
		{
			$filename = explode("/", $php_self); // THIS WILL BREAK DOWN THE PATH INTO AN ARRAY
			for( $i = 0; $i < (count($filename) - 1); ++$i ) 
			{
				$filename2 .= $filename[$i].'/';
			}
			return $filename2;
		}

		$sub_folder = get_folder_name($_SERVER['PHP_SELF']);
		$server_root = get_folder_name($_SERVER['SCRIPT_FILENAME']);
		
		echo "<br />";
		echo "<strong>This is config default values from the file /functions/config.php</strong><br />";
		echo("define('HTTP_SERVER', 'http://xxxx/ilance/');<br />");
		echo("define('HTTPS_SERVER', 'http://xxxx/ilance/');<br />");
		echo("define('DIR_SERVER_ROOT', '/path/to/ilance/');<br />");
		echo("define('SUB_FOLDER_ROOT', '/ilance/');<br /><br /><br />");
		
		echo "<hr>";
		echo "<strong>Below there are values which are predicted by this script. Depending on server configuration it won't work properly on some servers.</strong>";
		echo '<li>(installer thinks) <b>HTTP_SERVER</b> could be <strong>http://' . $_SERVER['HTTP_HOST'] . $sub_folder . '</strong></li>';
		echo '<li>(installer thinks) <b>HTTPS_SERVER</b> could be <strong>https://' . $_SERVER['HTTP_HOST'] . $sub_folder . '</strong></li>';
		echo '<li>(installer thinks) <b>DIR_SERVER_ROOT</b> could be <strong>' . str_replace('/install/installer.php', '', $server_root) . '</strong></li>';
		echo '<li>(installer thinks) <b>SUB_FOLDER_ROOT</b> could be <strong>' . str_replace('/install/installer.php', '', $sub_folder) . '</strong></li>';
		
?>
</body>
</html>