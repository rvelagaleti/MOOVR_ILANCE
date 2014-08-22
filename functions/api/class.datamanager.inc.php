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
* Data manager class to handle the majority of getting and setting routines in ILance
*
* @package      iLance\DataManager
* @version      4.0.0.8059
* @author       ILance
*/
class datamanager
{
        var $hide_errors = false;
        var $getdata = array();
        var $setdata = array();
        var $errors = array();
        var $condition = null;
	var $slng = null;
        
        /**
        * Data manager constructor
        */
        function datamanager(&$registry)
        {
                if (is_object($registry))
		{
                        $this->dm =& $registry;
                        if (is_object($registry->db))
			{
				$this->db =& $registry->db;
			}
			else
			{
				echo '<strong>Fatal:</strong> Database data is not a valid object';
                                exit;
			}
                }
                else
                {
                        echo '<strong>Fatal:</strong> Registry data is not a valid object';
                        exit;
                }
        }
        
        function set_condition()
        {
                if (!array($this->getdata))
                {
                        echo '<strong>Fatal:</strong> $this->getdata does not contain a valid condition to update the db table';
                }
                else
                {
                        foreach ($this->getdata as $key => $value)
                        {
                                $condition = "WHERE $key = $value";
                        }
                }
                $this->condition =& $condition;                
        }
        
        function get(&$existingdata)
        {
                if (is_array($existingdata))
		{
			if (sizeof($this->getdata) == 0)
			{
				$this->getdata =& $existingdata;
			}
			else
			{
				foreach (array_keys($existingdata) as $fieldname)
				{
					$this->getdata["$fieldname"] =& $existingdata["$fieldname"];
				}
			}

			$this->set_condition();
		}
		else
		{
			echo 'Existing data structure sent is not an array';
                        exit;
		}
        }
        
        function verify_required_fields()
	{
		foreach ($this->allowfields as $fieldname => $allowfield)
		{
			if ($allowfield[1] == 'REQUIRED_YES' AND !isset($this->setdata["$fieldname"]))
			{
				$this->error("Required field <em>$fieldname</em> is invalid or missing in <em>set()</em>");
				return false;
			}
		}

		return true;
	}
        
        function verify($fieldname, &$value, $dofilter = true)
	{
		if (isset($this->allowfields["$fieldname"]))
		{
			$field =& $this->allowfields["$fieldname"];
                        if ($dofilter)
                        {
                                $value = $this->dm->clean_gpc('s', $value, $field[0]);
                        }
                        return true;
		}
		else
		{
			$this->error("<strong>Developer Error:</strong> Field <em>$fieldname</em> is not defined in <em>\$allowfields</em> in class <strong>" . get_class($this) . "</strong>");
			return false;
		}
	}
        
        function set($fieldname, $value, $dofilter = true)
        {
                if ($dofilter)
		{
			$verify = $this->verify($fieldname, $value, $dofilter);
			if ($verify)
			{
				$this->setdata["$fieldname"] = $value;
				return true;
			}
		}
		else if (isset($this->allowfields["$fieldname"]))
		{
			$this->setdata["$fieldname"] = $value;
			return true;
		}
                else
                {
                        $this->error($fieldname . ' field is not permitted');    
                }
        }
        
        function error($text)
	{
		$this->errors[] = $text;
		switch ($this->hide_errors)
		{
			case true:
			{
				// show nothing
			}
			break;

			case false:
                        default:
			{
				echo $text;
			}
			break;
		}
	}     

        function pre_save()
        {
                $this->verify_required_fields();
                
                $errorlist = '';
                foreach ($this->errors as $index => $error)
                {
                        $errorlist .= "<li>$error</li>";
                }
                return $errorlist;
        }
        
        function save()
        {
                $this->verify_required_fields();
                
                if (count($this->errors) > 0)
                {
                        return false;
                }
                else
                {
                        // save data
                }
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>