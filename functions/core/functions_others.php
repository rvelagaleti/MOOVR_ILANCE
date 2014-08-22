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
* Other functions for iLance
*
* @package      iLance\Global\Other
* @version      4.0.0.8059
* @author       ILance
*/

/**
* Function for determining the entire size used within the database for ILance operations.
*
* @return      string         size in bytes
*/
function fetch_database_size()
{
        global $ilance;
        $total = 0;
        $result = $ilance->db->query("SHOW TABLE STATUS", 0, null, __FILE__, __LINE__);
        while ($row = $ilance->db->fetch_array($result))
        { 
                $total += ($row['Data_length'] + $row['Index_length']); 
        } 
        return $total;
}

/**
* Function to calculate and fetch the age (in years) based on a supplied birthday date
*
* @param        string      date (MM-DD-YYYY)
*
* @return	integer     Returns the age in years
*/
function fetch_age($birthday)
{
        $bday = explode('-', $birthday);
        if ($bday[2] < 1970)
        {
                $years = 1970 - $bday[2];
                $year = $bday[2] + ($years * 2);
                $stamp = mktime(0, 0, 0, $bday[1], $bday[0], $year) - ($years * 31556926 * 2);
        }
        else
        {
                $stamp = mktime(0, 0, 0, $bday[1], $bday[0], $bday[2]);
        }
        $age = floor((time() - $stamp) / 31556926);
        return $age;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>