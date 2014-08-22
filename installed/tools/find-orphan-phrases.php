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

include('../../functions/config.php');

@set_time_limit(0);
@ini_set('memory_limit', '1000M');

class RecursiveSearch
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

    function RecursiveSearch($root)
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
                    ORDER BY phraseid ASC
            ");
            
            $this->totalphrases = $ilance->db->num_rows($sql);            
            $this->query = "DELETE FROM " . DB_PREFIX . "language_phrases WHERE";
            
            $this->__cache_files($root);
            
            while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
            {
                    $this->__search('', $res['varname']);
                    
                    //echo "<li><strong>".$this->matches[$res['varname']]." matches found for <font color='blue'>".$res['varname']."</font></strong></li>";
                    if ($this->matches[$res['varname']] == 0)
                    {
                            $this->orphanphrases = $this->orphanphrases + 1;
                            $this->query .= " varname = '" . $res['varname'] . "' OR";
                            echo "<li><strong>" . $this->matches[$res['varname']] . " matches found for <font color='red'>".$res['varname']." (orphan) - (phraseid ".$res['phraseid']." of ".$this->totalphrases.")</font></strong></li>";
                    }
                    flush();
            }
            
            $tmp = $this->query;
            $this->query = mb_substr($tmp, 0, -3);
            unset($tmp);
            
            echo "<p>" . $this->query . "</p>";
    }
    
    function __cache_files($dir = '', $varname = '')
    {
    		global $ilance;
            $path = $dir;// ? $this->basedir : "{$this->basedir}/$dir";
            
            foreach (scandir($path) AS $found)
            {
                    if (!$this->__isdot($found) AND !$this->__issvn($found) AND !$this->__isimg($found) AND !$this->__isinstall($found))
                    {
                            $absolute = "$path/$found";
                            $relative = $dir == '' ? $found : "$dir/$found";                  
                            if (is_dir($absolute))
                            {
                                    $this->folders[] = $relative;
                                    $this->__cache_files($relative);
                            }
                            else if (is_file($absolute))
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
            
            foreach ($this->filecontents as $key => $value)
            {
            	$match = strpos($value, $varname);
            	if (is_numeric($match))
            	{
            		break;
            	}
            }
            if (!$match)
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

echo "<b>Orphan Phrases:</b>\n<ul>\n";
$search = new RecursiveSearch(DIR_SERVER_ROOT);
echo "</ul>\n",
     "<br /><table border='0'>\n",
     "<tr><td><i>Total number of phrases found: <strong>".$search->totalphrases."</strong>, Total number of orphan phrases found:</i></td><td><strong>".$search->orphanphrases."</strong></td></tr>\n",
     "</table>";
     
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>