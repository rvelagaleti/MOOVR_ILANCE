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

define('DB_ASSOC', 1);
define('DB_NUM', 2);
define('DB_BOTH', 3);

/**
* ILance database class to perform the majority of database caching in ILance
*
* @package      iLance\Database
* @version      4.0.0.8059
* @author       ILance
*/
class ilance_database
{
	/**
	* The ILance registry object
	*
	* @var	    $registry
	*/
        var $registry = null;
        
        /**
	* Debug Mode
	*
	* @var	    $debug
	*/
	var $debug = false;
        
        /**
	* Email Error Reporting
	*
	* @var	    $email_reporting
	*/
        var $error_reporting = true;
	var $email_reporting = true;
        
        /**
	* Timer Variables
	*
	* @var	    $start
	* @var      $end
	* @var      $totaltime
	* @var      $formatted
	*/
	var $start = null;
	var $end = null;
	var $totaltime = null;
	var $formatted = null;
        
        /**
	* Database Connection Parameters
	*
	* @var	    $multiserver
	* @var      $database
	* @var      $explain
	* @var      $querylist
	* @var      $query_count
	* @var      $connection_write
	* @var      $connection_read
	* @var      $connection_link
	*/
        var $multiserver = false;
	var $database = null;
        var $explain = null;
        var $querylist = array();
        var $query_count = 0;
	var $connection_write = null;
	var $connection_read = null;
	var $connection_link = null;
	var $error = '';
	var $errno = '';
	var $ttquery = 0;
	
	/**
	* Constructor
	*/
	function ilance_database()
	{
		// #### prepare our default dbms escape string #################
                if (isset($this->functions) AND function_exists($this->functions['real_escape_string']))
		{
			$this->functions['escape_string'] = $this->functions['real_escape_string'];
		}
	}
	
	/**
	* Initialize database connection
	*
	* Connects to a database server
	* 
	* @return	boolean
	*/
	function connect()
	{
		$this->connection_write = $this->db_connect();
		$this->multiserver = false;
		$this->connection_read =& $this->connection_write;
		$this->database = DB_DATABASE;
		if ($this->connection_write)
		{
			$this->select_db($this->database);
		}
	}
	
	/**
	* Selects a database for usage
	*
	* @param	string	 name of the database to use
	*
	* @return	boolean
	*/
        function select_db($database = '')
	{
		if ($database != '')
		{
			$this->database = $database;
		}
		if ($check_write = @$this->select_db_wrapper($this->database, $this->connection_write))
		{
			$this->connection_link =& $this->connection_write;
			return true;
		}
		else
		{
			$this->connection_link =& $this->connection_write;
			$this->dberror('Cannot select database ' . $this->database . ' for usage');
			return false;
		}
	}

	/**
	* Function to perform a database explain query
	* 
	* @param       string        sql code
	* @param       integer       sql query time
	* @param       string        script name
	* @param       string        script line number
	* 
        * @return      nothing
	*/
	function explain_query($string = '', $qtime = '', $script = '', $line = '')
	{
		if (defined('DB_EXPLAIN') AND DB_EXPLAIN)
		{
			if (mb_substr($string, 0, 6) == 'SELECT')
			{
				$query = (DB_SERVER_TYPE == 'mysql') ? $this->functions['query']("EXPLAIN $string") : $this->functions['query']($this->connection_link, "EXPLAIN $string");
				$this->explain .= "<table bgcolor=\"#cccccc\" width=\"95%\" cellpadding=\"9\" cellspacing=\"1\" align=\"center\">\n".
"<tr>\n".
"<td colspan=\"8\" bgcolor=\"orange\"><strong>#".$this->query_count." - Select Query</strong></td>\n".
"</tr>\n".
"<tr>\n".
"<td colspan=\"8\" bgcolor=\"#fefefe\"><span style=\"font-family: Courier; font-size: 14px;\">Script: ".$script.", Line: ".$line."</span></td>\n".
"</tr>\n".
"<tr>\n".
"<td colspan=\"8\" bgcolor=\"#fefefe\"><span style=\"font-family: Courier; font-size: 14px;\">".$string."</span></td>\n".
"</tr>\n".
"<tr bgcolor=\"#efefef\">\n".
"<td><strong>table</strong></td>\n".
"<td><strong>type</strong></td>\n".
"<td><strong>possible_keys</strong></td>\n".
"<td><strong>key</strong></td>\n".
"<td><strong>key_len</strong></td>\n".
"<td><strong>ref</strong></td>\n".
"<td><strong>rows</strong></td>\n".
"<td><strong>Extra</strong></td>\n".
"</tr>\n";
				while ($table = $this->functions['fetch_array']($query))
				{
					$this->explain .= "<tr bgcolor=\"#ffffff\">\n".
"<td>".$table['table']."</td>\n".
"<td>".$table['type']."</td>\n".
"<td>".$table['possible_keys']."</td>\n".
"<td>".$table['key']."</td>\n".
"<td>".$table['key_len']."</td>\n".
"<td>".$table['ref']."</td>\n".
"<td>".$table['rows']."</td>\n".
"<td>".$table['Extra']."</td>\n".
"</tr>\n";
				}
				$this->explain .= "<tr>\n".
"<td colspan=\"8\" bgcolor=\"#ffffff\">Query Time: ".$qtime."</td>\n".
"</tr>\n".
"</table>\n".
"<br />\n";
			}
			else if (mb_substr($string, 0, 6) == 'DELETE')
			{
				$this->explain .= "<table bgcolor=\"#cccccc\" width=\"95%\" cellpadding=\"9\" cellspacing=\"1\" align=\"center\">\n".
"<tr>\n".
"<td bgcolor=\"#ff0000\"><font color=\"#ffffff\"><strong>#".$this->query_count." - Delete Query</strong></font></td>\n".
"</tr>\n".
"<tr bgcolor=\"#fefefe\">\n".
"<td><span style=\"font-family: Courier; font-size: 14px;\">Script: ".$script.", Line: ".$line."</span></td>\n".
"</tr>\n".
"<tr bgcolor=\"#fefefe\">\n".
"<td><span style=\"font-family: Courier; font-size: 14px;\">".$string."</span></td>\n".
"</tr>\n".
"<tr>\n".
"<td bgcolor=\"#ffffff\">Query Time: ".$qtime."</td>\n".
"</tr>\n".
"</table>\n".
"</table>\n".
"<br />\n";
			}
			else
			{
				$this->explain .= "<table bgcolor=\"#cccccc\" width=\"95%\" cellpadding=\"9\" cellspacing=\"1\" align=\"center\">\n".
"<tr>\n".
"<td bgcolor=\"#ffee00\"><strong>#".$this->query_count." - Write Query</strong></td>\n".
"</tr>\n".
"<tr bgcolor=\"#fefefe\">\n".
"<td><span style=\"font-family: Courier; font-size: 14px;\">Script: ".$script.", Line: ".$line."</span></td>\n".
"</tr>\n".
"<tr bgcolor=\"#fefefe\">\n".
"<td><span style=\"font-family: Courier; font-size: 14px;\">".$string."</span></td>\n".
"</tr>\n".
"<tr>\n".
"<td bgcolor=\"#ffffff\">Query Time: ".$qtime."</td>\n".
"</tr>\n".
"</table>\n".
"</table>\n".
"<br />\n";
			}
			$this->querylist[$this->query_count]['query'] = $string;
			$this->querylist[$this->query_count]['time'] = $qtime;	
			$this->ttquery += $qtime;
		}
	}

	/**
	* Function to perform database error handling
	* 
        * @return      nothing
	*/
	function dberror($string = '')
	{   
		if (!defined('NO_DB'))
		{
			define('NO_DB', true);
		}
		global $ilance, $message, $ilconfig, $site_email, $site_name, $loadaverage;
		if ($this->error_reporting)
		{
                        $message = $messageemail = '';
                        $html = "
			$string
MySQL Error	: " . $this->error() . "
Error Number	: " . $this->errno() . "
Date		: " . date('M d, Y, H:i:s') . "
Script		: http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . print_hidden_fields(true, array(), true, '', '', true, false) . "
Class		: " . get_class($this) . "
Location	: " . (defined('LOCATION') ? LOCATION : 'Unknown') . "
Referrer	: " . REFERRER . "
IP Address	: " . IPADDRESS . "
ILance Version	: " . ILANCEVERSION . "
Build		: " . SVNVERSION . "
SQL Version	: " . (isset($ilconfig['current_sql_version']) ? $ilconfig['current_sql_version'] : SQLVERSION) . "
MySQL Version	: " . MYSQL_VERSION . "
Server Load	: " . $loadaverage;
                        $messageemail = $html;
			if (defined('DB_DEBUGMODE') AND DB_DEBUGMODE)
			{
				$message = '<textarea style="width:600px; height:130px">' . $html . '</textarea>';
			}
                        else
                        {
				if (defined('DB_DEBUGMODE_VIEWSOURCE') AND DB_DEBUGMODE_VIEWSOURCE)
				{
					$message = "\n\n<!-- DATABASE ERROR\n\n************************\n$html\n************************\n\nEND DATABASE ERROR //-->\n\n";
				}    
                        }
			if ($this->email_reporting)
			{
				$subject = "Database error on " . date('M d, Y, H:i:s');
				if (defined('SITE_EMAIL'))
				{
					$ilance->email->toqueue = false;
					$ilance->email->mail = SITE_EMAIL;
					$ilance->email->from = 'ILance MySQL ReportBot';
					$ilance->email->subject = $subject;
					$ilance->email->message = $messageemail;

					$ilance->email->send();
				}
			}
                        $template = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>The database has encountered a problem.</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style type="text/css">
<!--	
body { background-color: white; color: black; }
#container { width: 400px; }
#message   { width: 400px; color: black; background-color: #FFFFCC; }
#bodytitle { font: 13pt/15pt verdana, arial, sans-serif; height: 35px; vertical-align: top; }
.bodytext  { font: 8pt/11pt verdana, arial, sans-serif; }
a:link     { font: 8pt/11pt verdana, arial, sans-serif; color: red; }
a:visited  { font: 8pt/11pt verdana, arial, sans-serif; color: #4e4e4e; }
-->
</style>
</head>
<body>
<table cellpadding="3" cellspacing="5" id="container">
<tr>
        <td id="bodytitle" width="100%">Database error</td>
</tr>
<tr>
        <td class="bodytext" colspan="2">The database has encountered a problem.</td>
</tr>
<tr>
        <td colspan="2"><hr /></td>
</tr>
<tr>
        <td class="bodytext" colspan="2">
                Please try the following:
                <ul>
                        <li>Load the page again by clicking the <a href="#" onclick="window.location = window.location;">Refresh</a> button in your web browser.</li>
                        <li>Click the <a href="javascript:history.back(1)">Back</a> button to try another link.</li>
                </ul>
        </td>
</tr>
<tr>
        <td class="bodytext" colspan="2">The technical staff have been notified of the error.  We apologise for any inconvenience.</td>
</tr>
<tr>
        <td class="bodytext" colspan="2">' . $message . '</td>
</tr>
</table>
</body>
</html>';
                        // tell the search engines that our service is temporarily unavailable to prevent indexing db errors
                        header('HTTP/1.1 503 Service Temporarily Unavailable');
                        header('Status: 503 Service Temporarily Unavailable');
                        header('Retry-After: 3600');
                        echo $template;
			exit();
		}
	}

	/**
	* Function to determine if a field within a table exists
	* 
	* @param       string       field name
	* @param       string       table name
	*
        * @return      boolean      Returns false on no field existing, true on field existing
	*/
        function field_exists($field = '', $table = '')
        {
                $exists = false;
                $columns = $this->query("SHOW COLUMNS FROM $table");
                while ($c = $this->fetch_assoc($columns))
                {
                        if ($c['Field'] == $field)
                        {
                                $exists = true;
                                break;
                        }
                }
                return $exists;
        }
	
	/**
	* Function to determine if a database table exists based on the currently selected database
	* 
	* @param       string       table name
	*
	* @return      boolean      Returns false on no table existing, true on table existing
	*/
	function table_exists($table = '')
	{		
		$res = $this->query("SHOW TABLES LIKE '$table'");
		if ($this->num_rows($res) > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
        /**
        * Function to determine if a field within a table exists, and if not, to automatically add the necessary field column details
        * 
        * @param       string       database table name
        * @param       string       table field name to add (if does not already exist)
        * @param       string       table field attributes to process (ie: VARCHAR(250) NOT NULL)
        * @param       string       table field name that we'll add our new field name after (ie: AFTER `title`)
        *
        * @return      boolean      Returns valid sql string if added, blank string if already exists
        */
        function add_field_if_not_exist($table = '', $column = '', $attributes = '', $addaftercolumn = '', $doquery = true)
        {
                $exists = false;
                $sql = '';
                $columns = $this->query("SHOW COLUMNS FROM $table", 0, null, __FILE__, __LINE__);
                while ($c = $this->fetch_assoc($columns))
                {
                        if (isset($c['Field']) AND !empty($c['Field']) AND $c['Field'] == $column)
                        {
                                $exists = true;
                                break;
                        }
                }
                if ($exists == false)
                {
                        if ($doquery)
                        {
                                $sql = "ALTER TABLE `$table` ADD `$column` $attributes $addaftercolumn";
                                $this->query($sql, 0, null, __FILE__, __LINE__);
                        }
                        else
                        {
                                $sql = "ALTER TABLE `$table` ADD `$column` $attributes $addaftercolumn";
                        }
                }
                return $sql;
        }
	
	/**
        * Function to determine if an existing field attribute needs to be changed preventing upgrades from any sql errors on duplicate attempts
        * 
        * @param       string       database table name
        * @param       string       table field name to add (if does not already exist)
        * @param       string       table field attributes to process (ie: VARCHAR(250) NOT NULL)
        * @param       string       table field (ie: NOT NULL DEFAULT)
        * @param       string       table field default (ie: 0000-00-00) if type was `date`
        *
        * @return      boolean      Returns valid sql string if added, blank string if already exists
        */
        function change_field_if_not_exist($table = '', $column = '', $attributes = '', $null = '', $default = '', $doquery = true)
        {
                $sql = '';
                $columns = $this->query("SHOW COLUMNS FROM $table", 0, null, __FILE__, __LINE__);
                while ($c = $this->fetch_assoc($columns))
                {
                        if (isset($c['Field']) AND !empty($c['Field']) AND $c['Field'] == $column)
                        {
				// column exists.. find out if the `Type` or `Default` field attributes have changed..
				if ((isset($c['Type']) AND strtolower($c['Type']) != strtolower($attributes)) OR (isset($c['Default']) AND strtolower($c['Default']) != strtolower($default)))
				{
					if ($doquery)
					{
						$sql = (empty($default) ? "ALTER TABLE `$table` CHANGE `$column` `$column` $attributes $null" : "ALTER TABLE `$table` CHANGE `$column` `$column` $attributes $null '$default'");
						$this->query($sql, 0, null, __FILE__, __LINE__);
					}
					else
					{
						$sql = (empty($default) ? "ALTER TABLE `$table` CHANGE `$column` `$column` $attributes $null" : "ALTER TABLE `$table` CHANGE `$column` `$column` $attributes $null '$default'");
					}
					break;
				}
                        }
                }
                return $sql;
        }

	/**
	* Timer function
	* 
        * @return      nothing
	*/
	function timer()
	{
		$this->add();
	}

	/**
	* Timer add function
	* 
        * @return      nothing
	*/
	function add()
	{
		if (!$this->start) 
		{
			$mtime1 = explode(" ", microtime());
			$this->start = $mtime1[1] + $mtime1[0];
		}
	}

	/**
	* Get Time from timer() function
	* 
        * @return      nothing
	*/
	function gettime()
	{
		if ($this->end)
		{ // timer has been stopped
			return $this->totaltime;
		}
		else if ($this->start AND !$this->end)
		{ // timer is still going
			$mtime2 = explode(" ", microtime());
			$currenttime = $mtime2[1] + $mtime2[0];
			$totaltime = $currenttime - $this->start;
			return $this->format($totaltime);
		}
		else
		{
			return false;
		}
	}
	
	/**
	* Stop time from timer() function
	* 
        * @return      nothing
	*/
	function stop()
	{
		if ($this->start)
		{
			$mtime2 = explode(" ", microtime());
			$this->end = $mtime2[1] + $mtime2[0];
			$totaltime = $this->end - $this->start;
			$this->totaltime = $totaltime;
			$this->formatted = $this->format($totaltime);
			return $this->formatted;
		}
	}
	
	/**
	* Remove time from timer() function
	* 
        * @return      nothing
	*/
	function remove()
	{
		$this->name = $this->start = $this->end = $this->totaltime = $this->formatted = '';
	}
	
	/**
	* Format time from timer() function
	* 
        * @return      nothing
	*/
	function format($string = '')
	{
		return number_format($string, 7);
	}
	
	function query_cache($sql, $linkidentifier, $timeout = 60)
	{
		global $ilance;
		$cache = $ilance->cache->fetch($sql);
		if ($cache == false)
		{
			$result = ($linkidentifier != false)
				? $this->functions['query']($linkidentifier, $sql, MYSQLI_STORE_RESULT)
				: $this->functions['query']($sql);
				
			if ($ilance->db->num_rows($result) > 0)
			{
				while ($res = $ilance->db->fetch_array($result, DB_ASSOC))
				{
					$cache[] = $res;
				}
				$ilance->cache->store($sql, $cache, $timeout);
			}
		}
		return $cache;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>