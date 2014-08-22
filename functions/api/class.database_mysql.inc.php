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

if (!class_exists('ilance_database'))
{
	echo 'Could not find database backend.';
	exit;
}

/**
* MySQL database class to perform the majority of database related functions in ILance
*
* @package      iLance\Database\MySQL
* @version      4.0.0 Build 8059
* @author       ILance
*/
class ilance_mysql extends ilance_database
{
	/**
	* MySQL Database Array Resource Types
	*
	* @var	    $types
	*/
        var $types = array(
		DB_NUM => MYSQL_NUM,
		DB_ASSOC => MYSQL_ASSOC,
		DB_BOTH => MYSQL_BOTH
	);
	
	/**
	* MySQL Database Interface Functions
	*
	* @var	    $functions
	*/
	var $functions = array(
		'select_db' => 'mysql_select_db',
		'pconnect' => 'mysql_pconnect',
		'connect' => 'mysql_connect',
		'query' => 'mysql_query',
		'query_unbuffered' => 'mysql_unbuffered_query',
		'fetch_row' => 'mysql_fetch_row',
		'fetch_object' => 'mysql_fetch_object',
		'fetch_array' => 'mysql_fetch_array',
		'fetch_field' => 'mysql_fetch_field',
		'free_result' => 'mysql_free_result',
		'data_seek' => 'mysql_data_seek',
		'error' => 'mysql_error',
		'errno' => 'mysql_errno',
		'affected_rows' => 'mysql_affected_rows',
		'num_rows' => 'mysql_num_rows',
		'num_fields' => 'mysql_num_fields',
		'field_name' => 'mysql_field_name',
		'insert_id' => 'mysql_insert_id',
		'list_tables' => 'mysql_list_tables',
		'list_fields' => 'mysql_list_fields',
		'escape_string' => 'mysql_escape_string',
		'real_escape_string' => 'mysql_real_escape_string',
		'close' => 'mysql_close',
		'client_encoding' => 'mysql_client_encoding',
		'create_db' => 'mysql_create_db',
		'ping' => 'mysql_ping',
		'free_result' => 'mysql_free_result'
	);
	
        /**
	* Constructor
	*
	* @param	object	        ilance registry object
	* @param        integer         cache time out
	* @param        bool            cache results to database within cache table?
	*/
	function ilance_mysql(&$registry, $cachetimeout = 1, $cachetodatabase = true)
	{
                $this->registry =& $registry;
		parent::ilance_database();
                $this->connect();
	}
	
	/**
        * Connect to the database and return the connection link resource
        *
        * Connects to a database server and physically returns the connection link identifier
        * 
        * @return	boolean
        */
        function db_connect()
        {
                $hostname = DB_SERVER;
                $username = DB_SERVER_USERNAME;
                $password = DB_SERVER_PASSWORD;
                $pconnect = DB_PERSISTANT_MASTER;
                $dbcharset = DB_CHARSET;
                $dbcollate = DB_COLLATE;
                $port = DB_SERVER_PORT;
                $port = $port ? $port : 3306;
                $link = $this->functions[$pconnect ? 'pconnect' : 'connect']("$hostname:$port", $username, $password);
                if (!empty($dbcharset) AND !empty($dbcollate))
                {
                        $this->query("SET CHARACTER SET $dbcharset", 0, null, 'class.database.inc.php', __LINE__, true, $link);
                        $this->query("SET NAMES $dbcharset", 0, null, 'class.database.inc.php', __LINE__, true, $link);
                        $this->query("SET COLLATION_DATABASE $dbcollate", 0, null, 'class.database.inc.php', __LINE__, true, $link);
                        $this->query("SET COLLATION_CONNECTION $dbcollate", 0, null, 'class.database.inc.php', __LINE__, true, $link);
                        $this->query("SET character_set_results = '$dbcharset', character_set_client = '$dbcharset', character_set_connection = '$dbcharset', character_set_database = '$dbcharset', character_set_server = '$dbcharset', character_set_system = '$dbcharset'", 0, null, 'class.database.inc.php', __LINE__, true, $link);
                }
                return $link;
        }
	
	/**
	* Function to select the database with an associated mysql link identifier
	* 
	* @param       string        database name
	* @param       object        database link
	* 
        * @return      nothing
	*/
	function select_db_wrapper($database = '', $link = null)
	{
		return $this->functions['select_db']($database, $link);
	}
	
	/**
	* Function to perform a database specific query
	* 
	* @param       string        sql code
	* @param       bool          hide database errors? default false
	* @param       string        enable cache? (will use defined cache engine selected within connect.php)
	* @param       string        script filename
	* @param       string        script line number
	* 
        * @return      nothing
	*/
	function query($string = '', $hideerrors = 0, $enablecache = null, $script = '', $line = '', $buffered = true, $errorexempt = array())
	{
		global $pagestarttime, $querytime;
		$this->query_count++;
                $qtimer = $this->timer();
		if ($enablecache == null)
		{
			$query = $this->functions['query']($string);
		}
		else
		{
			$query = $this->query_cache($string, $this->connection_link);
		}
		if ($this->errno() AND !$hideerrors)
		{
			if(count($errorexempt) > 0 AND in_array($this->errno(), $errorexempt))
			{
				return $this->errno();
			}
			else 
			{
				$this->dberror($string);
			 	exit();
			}
		}
		$qtime = $this->stop();
		$this->explain_query($string, $qtime, $script, $line);
		$querytime += $this->totaltime;
		$this->remove();
		return $query;
	}
	
	/**
	* Function to perform a database specific query and immediately returns the associated array/results
	* 
	* @param       string        sql code
	* @param       bool          hide database errors? default false
	* @param       string        enable cache? (will use defined cache engine selected within connect.php)
	* @param       string        script filename
	* @param       string        script line number
	* 
        * @return      nothing
	*/
	function query_fetch($string = '', $hideerrors = 0, $enablecache = null, $script = '', $line = '')
	{
		global $pagestarttime, $querytime;
		$qtimer = $this->timer();
		$query = $this->functions['query']($string);
		if ($this->errno() AND !$hideerrors)
		{
			 $this->dberror($string);
			 exit();
		}
		$qtime = $this->stop();
		$this->explain_query($string, $qtime, $script, $line);
		$querytime += $this->totaltime;
		$this->remove();
		$this->query_count++;
		return $this->fetch_array($query);
	}
	
	/**
	* Function to perform a database fetch array
	* 
	* @param       string        sql object
	* @param       string        sql result type
	* 
        * @return      nothing
	*/
	function fetch_array(&$query, $type = DB_BOTH)
	{
		return @$this->functions['fetch_array']($query, $this->types[$type]);
	}
	
	/**
	* Function to perform a database fetch object
	* 
	* @param       string        sql code
	* 
        * @return      nothing
	*/
	function fetch_object(&$query)
        {
                return $this->functions['fetch_object']($query);
        }
	
	/**
	* Function to perform a database fetch associative array
	* 
	* @param       string        sql code
	* @param       string        sql result type
	* 
        * @return      nothing
	*/
	function fetch_assoc(&$query, $type = DB_ASSOC)
	{
		return @$this->functions['fetch_array']($query, $this->types[$type]);
	}
	
	/**
	* Function to perform a database fetch row
	* 
	* @param       string        sql code
	* @param       string        sql result type
	* 
        * @return      nothing
	*/
	function fetch_row(&$query, $type = DB_NUM)
	{
		return @$this->functions['fetch_row']($query);
	}
	
	/**
	* Function to fetch the total number of affected rows for the connection
	* 
        * @return      nothing
	*/
	function affected_rows()
	{
		return $this->functions['affected_rows']($this->connection_link);
	}
	
	/**
	* Function to fetch a field value result from a table
	* 
	* @param       string       table name
	* @param       string       sql condition code
	* @param       string       field name
	* @param       bool         (optional) cache results to file?
	*
        * @return      nothing
	*/
	function fetch_field($tbl = '', $condition = '', $field = '', $limit = '')
	{
		$limit = !empty($limit) ? ' LIMIT ' . $limit : '';
		$condition = !empty($condition) ? ' WHERE ' . $condition : '';
		$result = $this->query("
                        SELECT " . $this->escape_string($field) . "
                        FROM " . $this->escape_string($tbl) . 
			$condition .
			$limit . "
		");
		$row = ($this->fetch_array($result));
		return $row["$field"];	
	}
	
	/**
	* Function to perform a database num rows
	* 
	* @param       string        sql code
	* 
        * @return      nothing
	*/
	function num_rows($query = '')
	{
		return $this->functions['num_rows']($query);
	}
	
	/*
	* Function to perform a database num fields
	* 
	* @param       string        sql code
	* 
        * @return      nothing
	*/
        function num_fields($query = '')
	{
		return $this->functions['num_fields']($query);
	}
	
	/*
	* Function to perform a database field name
	* 
	* @param       string        sql code
	* 
        * @return      nothing
	*/
        function field_name($query = '')
	{
		return $this->functions['field_name']($query);
	}
	
	/**
	* Function to fetch the last insert id for the database connection
	* 
        * @return      integer     Returns the last insert id row from the database table
	*/
	function insert_id()
	{
		return $this->functions['insert_id']($this->connection_link);
	}
	
	/**
	* Function to close the database connection
	* 
        * @return      nothing
	*/
	function close()
	{
		@$this->functions['close']($this->connection_link);
	}
	
	/**
	* Function to mimic database error handling
	* 
        * @return      string      Returns the database error
	*/
	function error()
	{
		$this->error = ($this->connection_link === null) ? '' : $this->functions['error']($this->connection_link);
		return $this->error;
	}
	
	/**
	* Function to mimic database error number handling
	* 
        * @return      mixed	    Returns the database error number
	*/
	function errno()
	{
		$this->errno = ($this->connection_link === null) ? 0 : $this->functions['errno']($this->connection_link);
		return $this->errno;	
	}
	
	/**
	* Function to execute xxxx_real_escape_string()
	* 
	* @param       string        sql code
	* 
        * @return      string        Returns escaped SQL string to prevent SQL injection attacks
	*/
	function escape_string($query = '')
	{
		return $this->functions['escape_string']($query, $this->connection_write);
	}
	
	/**
	* Function to frees the memory associated with a result
	* 
	* @param       string        sql result
	* 
	* @return      Returns TRUE on success or FALSE on failure. 
	*/
	function free_result($res = '')
	{
		return $this->functions['free_result']($res);
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>