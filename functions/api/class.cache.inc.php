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
* Core cache class to perform the majority of cache handling operations in ILance
*
* @package      iLance\Cache
* @version	4.0.0.8059
* @author       ILance
*/
class cache
{
	/**
	* Cache name placeholder
	*
	* @var string
	* @access public
	*/
	public $name;
	/**
	* Cache instance placeholder
	*
	* @var string
	* @access public
	*/
	public $instance;
	
	/**
	* Cache constructor
	*
	* @param      boolean     cache filename using md5 filehash (default false)
	* @param      array       cache parameters
	* 
	* @return     nothing
	*/
	public function __construct($md5 = false, $params = array('uid' => true, 'sid' => true, 'rid' => false, 'styleid' => true, 'slng' => true))
	{
		global $ilance, $ilconfig;
		if (defined('LOCATION') AND LOCATION == 'installer')
		{
			$engine = 'none';
		}
		else
		{
			$engine = $ilconfig['globalservercache_engine'];
		}
		$params = (!empty($params)) ? $params : array('uid' => true, 'sid' => true, 'rid' => false, 'styleid' => true, 'slng' => true);
		switch ($engine)
		{
			case 'filecache':
			{
				$this->name = 'ilance_filecache';
				break;
			}
			case 'apc':
			{
				$this->name = (extension_loaded('apc') AND ini_get('apc.enabled')) ? 'ilance_apc' : 'ilance_filecache';
				break;
			}
			case 'memcached':
			{
				$this->name = 'ilance_memcached';
				break;
			}
			case 'none':
			{
				$this->name = 'ilance_nocache';
				break;
			}
			default:
			{
				$this->name = 'ilance_nocache';
				break;
			}
		}
		$this->instance = new $this->name($ilconfig['globalservercache_expiry'], $ilconfig['globalservercache_prefix'], $md5, $params);
		return $this->instance;
	}
	/**
	* Function to fetch the cache based on a cache key
	*
	* @param      string      cache key
	* 
	* @return     string      Returns cache data
	*/
	public function fetch($key = '')
	{
		if (!empty($key))
		{
			return $this->instance->fetch($key);
		}
		return false;
	}
	/**
	* Function to save the cache based on a cache key
	*
	* @param      string      cache key
	* 
	* @return     nothing
	*/
	public function store($key = '', $data = '', $ttl = 0)
	{
		if (!empty($key))
		{
			if ($ttl > 0)
			{
				$this->ttl = $ttl;
			}
			return $this->instance->store($key, $data, $ttl);
		}
		return false;
	}
	/**
	* Function to delete a cache value based on a cache key
	*
	* @param      string      cache key
	* 
	* @return     nothing
	*/
	public function delete($key = '')
	{
		if (!empty($key))
		{
			return $this->instance->delete($key);
		}
		return false;
	}
}

/**
* Core no-cache class
*
* @package      iLance\Cache\NoCache
* @version	4.0.0.8059
* @author       ILance
*/
class ilance_nocache extends cache
{
	/**
	* Cache constructor
	*
	* @param      integer     cache expiry
	* @param      string      cache prefix
	* @param      boolean     use cache filehash
	* @param      array       cache parameters
	* 
	* @return     nothing
	*/
	function __construct($ttl = 60, $prefix = '', $md5 = false, $params = array()){}
	
	/**
	* Fetch items from cache
	* 
	* @return      
	*/
	public function fetch($key = '')
	{
		return false;
	}
	
	/**
	* Store items in cache
	* 
	* @return      
	*/
	public function store($key = '', $data = '', $ttl = 0)
	{
		return false;
	}
	
	/**
	* Delete items in cache
	* 
	* @return      
	*/
	public function delete($key = '')
	{
		return false;
	}
}
/**
* ILance file system cache class to perform the majority of database caching in ILance
*
* @package      iLance\Cache\FileCache
* @version      4.0.0.8059
* @author       ILance
*/
class ilance_filecache extends cache
{
	/**
	* Cache time to live placeholder
	*
	* @var integer
	* @access public
	*/
	public $ttl;
	/**
	* Cache prefix
	*
	* @var string
	* @access public
	*/
	public $prefix;
	/**
	* Cache filehash placeholder
	*
	* @var string
	* @access public
	*/
	public $md5;
	/**
	* Cache parameter array
	*
	* @var array
	* @access public
	*/
	public $params = array('uid' => true, 'sid' => true, 'rid' => false, 'styleid' => true, 'slng' => true);
	/**
	* Cache data placeholder
	*
	* @var string
	* @access public
	*/
	public $data;
	
	/**
	* Cache constructor
	*
	* @param      integer     cache expiry
	* @param      string      cache prefix
	* @param      boolean     use cache filehash
	* @param      array       cache parameters
	* 
	* @return     nothing
	*/
	public function __construct($ttl = 60, $prefix = 'ilance_', $md5 = false, $params = array('uid' => true, 'sid' => true, 'rid' => false, 'styleid' => true, 'slng' => true))
	{
		$this->ttl = $ttl;
		$this->prefix = $prefix;
		$this->md5 = $md5;
		$this->params = $params;
		$this->gc();
	}
	
	/**
	* Fetch items from iLance file sytem cache
	*
	* @param      string       cache key
	* 
	* @return     string       Returns cached data
	*/
	public function fetch($key = '')
	{
		if (!empty($key))
		{
			$filename = $this->getfilename($key);
			if (!file_exists($filename))
			{
				return false;
			}
			$data = file_get_contents($filename);
			if (is_serialized($data))
			{	
				$data = unserialize($data);
			}
			return $data;
		}
		return false;
	}
	
	/**
	* Store items in iLance file system cache/datastore/ folder
	*
	* @param      string       cache key
	* @param      string       data to cache
	* @param      integer      time to live (in seconds) default 60 seconds
	* 
	* @return     null 
	*/
	public function store($key = '', $data = '', $ttl = 0)
	{
		if (!empty($key))
		{
			if ($ttl > 0)
			{
				$this->ttl = $ttl;
			}
			if (empty($data))
			{
				return false;
			}
			$filename = $this->getfilename($key);
			if (isset($data) AND is_array($data))
			{
				$data = serialize($data);
			}
			//file_put_contents($filename, $data, LOCK_EX);
			file_put_contents($filename, $data);
			touch($filename);
		}
		return false;
	}
	
	/**
	* Delete items in iLance file system cache
	*
	* @param      string       cache key
	* 
	* @return     boolean      Returns true or false if cache could not be deleted 
	*/
	public function delete($key = '')
	{
		if (!empty($key))
		{
			$filename = $this->getfilename($key);
			if (file_exists($filename))
			{
				return unlink($filename);
			}
		}
		return false;
	}
	
	/**
	* Get local cache filename on server
	*
	* @param       string       cache key
	* 
	* @return      string       Return full folder and filename of cache file
	*/
	private function getfilename($key = '')
	{
		if (!empty($key))
		{
			$uid = (isset($_SESSION['ilancedata']['user']['userid']) AND $this->params['uid'] == true) ? $_SESSION['ilancedata']['user']['userid'] : 0;
			$sid = (isset($_SESSION['ilancedata']['user']['subscriptionid']) AND $this->params['sid'] == true) ? $_SESSION['ilancedata']['user']['subscriptionid'] : 0;
			$rid = (isset($_SESSION['ilancedata']['user']['roleid']) AND $this->params['rid'] == true) ? $_SESSION['ilancedata']['user']['roleid'] : 0;
			$styleid = (isset($_SESSION['ilancedata']['user']['styleid']) AND $this->params['styleid'] == true) ? $_SESSION['ilancedata']['user']['styleid'] : 0;
			$slng = (isset($_SESSION['ilancedata']['user']['slng']) AND $this->params['slng'] == true) ? $_SESSION['ilancedata']['user']['slng'] : 0;
			$key = $key . '_' . $styleid . '_' . $slng . '_' . $uid . '_' . $sid . '_' . $rid . '_' . SITE_ID;
			if ($this->md5)
			{
				$key = md5($key);
			}
			$key = $this->prefix . $key;
			return DIR_SERVER_ROOT . DIR_TMP_NAME . '/' . DIR_DATASTORE_NAME . '/' . $key;
		}
		return false;
	}
	
	/**
	* Garbage cache collection removal
	* 
	* @return      
	*/
	private function gc()
	{
		if ($handle = opendir(DIR_SERVER_ROOT . DIR_TMP_NAME . '/' . DIR_DATASTORE_NAME . '/'))
		{ 
			$dir_array = array(); 
			while (false !== ($file = readdir($handle)))
			{ 
				if ($file != '.' AND $file != '..')
				{
					if (file_exists(DIR_SERVER_ROOT . DIR_TMP_NAME . '/' . DIR_DATASTORE_NAME . '/' . $file))
					{
						$lastmod = @filemtime(DIR_SERVER_ROOT . DIR_TMP_NAME . '/' . DIR_DATASTORE_NAME . '/' . $file);
						if (($lastmod + ($this->ttl)) < time())
						{
							@unlink(DIR_SERVER_ROOT . DIR_TMP_NAME . '/' . DIR_DATASTORE_NAME . '/' . $file);
						}
					}
				} 
			} 
			closedir($handle);
		}
	}
}

/**
* ILance APC class to perform the majority of database caching in ILance
*
* @package      iLance\Cache\APC
* @version      4.0.0.8059
* @author       ILance
*/
class ilance_apc extends cache
{
	/**
	* Cache time to live placeholder
	*
	* @var integer
	* @access public
	*/
	public $ttl;
	/**
	* Cache prefix
	*
	* @var string
	* @access public
	*/
	public $prefix;
	/**
	* Cache filehash placeholder
	*
	* @var string
	* @access public
	*/
	public $md5;
	/**
	* Cache parameter array
	*
	* @var array
	* @access public
	*/
	public $params = array('uid' => true, 'sid' => true, 'rid' => false, 'styleid' => true, 'slng' => true);
	
	/**
	* Cache constructor
	*
	* @param      integer     cache expiry ttl (seconds)
	* @param      string      cache prefix
	* @param      boolean     use cache filehash (default true)
	* @param      array       cache parameters
	* 
	* @return     nothing
	*/
	public function __construct($ttl = 60, $prefix = 'ilance_', $md5 = false, $params = array('uid' => true, 'sid' => true, 'rid' => false, 'styleid' => true, 'slng' => true))
	{
		$this->ttl = $ttl;
		$this->prefix = $prefix;
		$this->md5 = $md5;
		$this->params = $params;
	}
	
	/**
	* Fetch items from APC cache
	*
	* @param      string       cache key
	* 
	* @return     string       Returns cached data 
	*/
	public function fetch($key = '')
	{
		if (!empty($key))
		{
			return apc_fetch($this->getfilename($key));
		}
		return false;
	}
	
	/**
	* Store items in APC cache
	* 
	* @param      string       cache key
	* @param      string       data to cache
	* @param      integer      time to live (in seconds) default 60 seconds
	* 
	* @return      
	*/
	public function store($key = '', $data = '', $ttl = 0)
	{
		if ($ttl > 0)
		{
			$this->ttl = $ttl;
		}
		if (!empty($key))
		{
			return apc_store($this->getfilename($key), $data, $this->ttl);
		}
		return false;
	}
	
	/**
	* Delete items in APC cache
	*
	* @param      string       cache key
	* 
	* @return      
	*/
	public function delete($key = '')
	{
		if (!empty($key))
		{
			return apc_delete($this->getfilename($key));
		}
		return false;
	}
	
	/**
	* Get local cache filename on server
	*
	* @param       string       cache key
	* 
	* @return      string       Return full folder and filename of cache file
	*/
	private function getfilename($key = '')
	{
		if (!empty($key))
		{
			$uid = (isset($_SESSION['ilancedata']['user']['userid']) AND $this->params['uid'] == true) ? $_SESSION['ilancedata']['user']['userid'] : 0;
			$sid = (isset($_SESSION['ilancedata']['user']['subscriptionid']) AND $this->params['sid'] == true) ? $_SESSION['ilancedata']['user']['subscriptionid'] : 0;
			$rid = (isset($_SESSION['ilancedata']['user']['roleid']) AND $this->params['rid'] == true) ? $_SESSION['ilancedata']['user']['roleid'] : 0;
			$styleid = (isset($_SESSION['ilancedata']['user']['styleid']) AND $this->params['styleid'] == true) ? $_SESSION['ilancedata']['user']['styleid'] : 0;
			$slng = (isset($_SESSION['ilancedata']['user']['slng']) AND $this->params['slng'] == true) ? $_SESSION['ilancedata']['user']['slng'] : 0;
			$key = $key . '_' . $styleid . '_' . $slng . '_' . $uid . '_' . $sid . '_' . $rid . '_' . SITE_ID;
			if ($this->md5)
			{
				$key = md5($key);
			}
			$key = $this->prefix . $key;
			return $key;
		}
		return false;
	}
}
	
/**
* ILance Memcached class to perform the majority of database memory caching in ILance
*
* @package      iLance\Cache\Memcached
* @version      4.0.0.8059
* @author       ILance
*/
class ilance_memcached extends cache
{
	public $connection;
	public $ttl;
	public $prefix;
	public $md5;
	/**
	* Constructor
	*/
	public function __construct($ttl = 60, $prefix = 'ilance_', $md5 = false, $params = array('uid' => true, 'sid' => true, 'rid' => false, 'styleid' => true, 'slng' => true))
	{
		global $memcacheserver;
		$this->connection = new MemCache;
		$this->ttl = $ttl;
		$this->prefix = $prefix;
		$this->md5 = $md5;
		foreach ($memcacheserver AS $servernumber => $serverinfo)
		{
			$this->connection->addServer(
				$serverinfo['server'],
				$serverinfo['port'],
				$serverinfo['persistent'],
				$serverinfo['weight'],
				$serverinfo['timeout'],
				$serverinfo['retry']
			);
		}
		$this->memcache_connected = true;
	}
    
	/**
	* Fetch items from memcached server
	*
	* @param      string       cache key
	* 
	* @return     string       Returns cached data    
	*/
	public function fetch($key = '')
	{
		if (!empty($key))
		{
			return $this->connection->get($this->prefix . $key);
		}
		return false;
	}
	
	/**
	* Store items in memcached server
	*
	* @param      string       cache key
	* @param      string       data to cache
	* @param      integer      time to live (in seconds) default 60 seconds
	* 
	* @return      
	*/
	public function store($key = '', $data = '', $ttl = 0)
	{
		if ($ttl > 0)
		{
			$this->ttl = $ttl;
		}
		if (!empty($key))
		{
			return $this->connection->set($this->prefix . $key, $data, 0, $this->ttl);
		}
		return false;
	}
	
	/**
	* Delete items in cache
	* 
	* @return      
	*/
	public function delete($key = '')
	{
		if (!empty($key))
		{
			return $this->connection->delete($this->prefix . $key);
		}
		return false;
	}
	
	/**
	* Close the memcache connection
	* 
	* @return      
	*/
	private function close()
	{
		if ($this->memcache_connected)
		{
			$this->connection->close();
			$this->memcache_connected = false;
		}
	}
	
	/**
	* Print the memcache server status and statistics
	* 
	* Sample usage:
	* $memcache = new Memcache;
	* $memcache->addServer('memcache_host', 11211);
	* $this->stats($memcache->getStats());
	* 
	* @return      
	*/
	private function stats($status = array())
	{
		$html = "<table border=\"1\">";
		$html .= "<tr><td>Memcache Server version:</td><td>$status[version]</td></tr>";
		$html .= "<tr><td>Process id of this server process </td><td>$status[pid]</td></tr>";
		$html .= "<tr><td>Number of seconds this server has been running </td><td>$status[uptime]</td></tr>";
		$html .= "<tr><td>Accumulated user time for this process </td><td>$status[rusage_user] seconds</td></tr>";
		$html .= "<tr><td>Accumulated system time for this process </td><td>$status[rusage_system] seconds</td></tr>";
		$html .= "<tr><td>Total number of items stored by this server ever since it started </td><td>$status[total_items]</td></tr>";
		$html .= "<tr><td>Number of open connections </td><td>$status[curr_connections]</td></tr>";
		$html .= "<tr><td>Total number of connections opened since the server started running </td><td>$status[total_connections]</td></tr>";
		$html .= "<tr><td>Number of connection structures allocated by the server </td><td>$status[connection_structures]</td></tr>";
		$html .= "<tr><td>Cumulative number of retrieval requests </td><td>$status[cmd_get]</td></tr>";
		$html .= "<tr><td>Cumulative number of storage requests </td><td>$status[cmd_set]</td></tr>";
		$percCacheHit = ((real)$status["get_hits"] / (real)$status["cmd_get"] * 100);
		$percCacheHit = round($percCacheHit, 3);
		$percCacheMiss = 100 - $percCacheHit;
		$html .= "<tr><td>Number of keys that have been requested and found present </td><td>$status[get_hits] ($percCacheHit%)</td></tr>";
		$html .= "<tr><td>Number of items that have been requested and not found </td><td>$status[get_misses] ($percCacheMiss%)</td></tr>";
		$MBRead = (real)$status["bytes_read"] / (1024 * 1024);
		$html .= "<tr><td>Total number of bytes read by this server from network </td><td>$MBRead Mega Bytes</td></tr>";
		$MBWrite = (real)$status["bytes_written"] / (1024 * 1024);
		$html .= "<tr><td>Total number of bytes sent by this server to network </td><td>$MBWrite Mega Bytes</td></tr>";
		$MBSize = (real)$status["limit_maxbytes"] / (1024 * 1024);
		$html .= "<tr><td>Number of bytes this server is allowed to use for storage.</td><td>$MBSize Mega Bytes</td></tr>";
		$html .= "<tr><td>Number of valid items removed from cache to free memory for new items.</td><td>$status[evictions]</td></tr>";
		$html .= "</table>";
		return $html;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>