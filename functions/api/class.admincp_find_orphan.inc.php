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

/**
* AdminCP orphan phrase variable finder for ILance
*
* @package      iLance\AdminCP\Orphan
* @version      4.0.0.8059
* @author       ILance
*/
class admincp_find_orphan
{
	var $basedir = '';
	var $files;
	var $folders;
	var $keyword;
	var $matches = array();
	var $match = 0;
	var $totalphrases = 0;
	var $orphanphrases = 0;
	var $query = '';
	var $filecontents = array(); //very big array
    
	/**
	* Function to find a phrase
	*
	*/
	function find_phrase($root)
	{
		global $ilance;
		$this->basedir = $root;
		$this->files = 0;
		$this->folders = array();
		$sql = $ilance->db->query("
			SELECT phraseid, varname
			FROM " . DB_PREFIX . "language_phrases
			WHERE phrasegroup != 'admincp_configuration'
				AND phrasegroup != 'admincp_permissions'
				AND phrasegroup != 'admincp_configuration_groups'
				AND ismaster = '1'
			ORDER BY phraseid ASC
		");
		$this->totalphrases = $ilance->db->num_rows($sql);            
		$this->query = '';
		$this->__cache_files($root);
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$this->__search('', $res['varname']);
			if ($this->matches[$res['varname']] == 0)
			{
				$this->orphanphrases = $this->orphanphrases + 1;
				$this->query .= "DELETE FROM " . DB_PREFIX . "language_phrases WHERE varname = '" . $res['varname'] . "';" . LINEBREAK;
			}
			flush();
		}
		return $this->query;
	}
	
	function find_emailtemplate($root)
	{
		global $ilance;
		$this->basedir = $root;
		$this->files = 0;
		$this->folders = array();
		$sql = $ilance->db->query("SELECT id, varname FROM " . DB_PREFIX . "email ORDER BY id ASC");
		$this->totalphrases = $ilance->db->num_rows($sql);
		$this->query = '';//"DELETE FROM " . DB_PREFIX . "email WHERE";
		$this->__cache_files($root);
		while ($res = $ilance->db->fetch_array($sql))
		{
			$this->__search('', $res['varname']);
			if ($this->matches[$res['varname']] == 0)
			{
				$this->orphanphrases = $this->orphanphrases + 1;
				$this->query .= "DELETE FROM " . DB_PREFIX . "email WHERE varname = '" . $res['varname'] . "';" . LINEBREAK;
			}
			flush();
		}
		return $this->query;
	}
	function __cache_files($dir = '', $varname = '')
	{
		global $ilance;
		$path = $dir;
		foreach (scandir($path) AS $found)
		{
			if (!$this->__isdot($found) AND !$this->__issvn($found) AND !$this->__isimg($found) AND !$this->__isinstall($found))
			{
				$absolute = "$path/$found";
				$relative = $dir == '' ? $found : "$dir/$found";  
				$ext = substr($absolute, -3);
				if (is_dir($absolute))
				{
					$this->folders[] = $relative;
					$this->__cache_files($relative);
				}
				else if (is_file($absolute) AND ($ext == 'php' OR $ext == 'tml' OR $ext == 'htm' OR $ext == 'xml' OR $ext == '.js'))
				{
					$this->filecontents[] = file_get_contents($absolute, "r");
				}
			}
		}
		echo '';
	}
	function __search($dir = '', $varname = '')
	{
		$path = $dir == '' ? $this->basedir : "{$this->basedir}/$dir";
		foreach ($this->filecontents AS $key => $value)
		{
			if (isset($value) AND !empty($value) AND isset($varname) AND !empty($varname))
			{
				$match = strpos($value, $varname);
				if (is_numeric($match))
				{
					break;
				}
			}
		}
		if (isset($match) AND !$match)
		{
			$this->matches[$varname] = (!empty($this->matches[$varname])) ? ($this->matches[$varname]) : 0;
		}
		else
		{
			$this->matches[$varname] = (!empty($this->matches[$varname])) ? ($this->matches[$varname] + 1) : 1;
		}
		unset($match);
	}
	function __isdot($s)
	{
		return ($s == '.' || $s == '..');
	}
	function __issvn($s)
	{
		return ($s == '.svn');
	}
	function __isimg($s)
	{
		return ($s == 'images');
	}
	function __isinstall($s)
	{
		return ($s == 'install');
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>