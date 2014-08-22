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

if (!isset($GLOBALS['ilance']->db))
{
        die('<strong>Warning:</strong> This script does not appear to have database functions loaded.  Operation aborted.');
}

/**
* Function to fetch and import the distance data within the ILance database.
*
* @param     string      distance country identifier (supported: au, be, canada, de, in, nl, pl, sp, uk, usa)
*
* @return    string      Returns formatted text of actions that have occured.
**/
function import_distance_data($schema = '')
{
        global $ilance;
        if (empty($schema))
        {
                return false;
        }
        $title = $table = $filename = '';
        $count = 0;
        if ($schema == 'au')
        {
                $title = 'Australia';
                $count = number_format(16079);
        }
        else if ($schema == 'be')
        {
                $title = 'Belgium';
                $count = number_format(3778);
        }
        else if ($schema == 'canada')
        {
                $title = 'Canada';
                $count = number_format(774014);
        }
        else if ($schema == 'de')
        {
                $title = 'Germany';
                $count = number_format(16375);
        }
        else if ($schema == 'in')
        {
                $title = 'India';
                $count = number_format(14568);
        }
        else if ($schema == 'nl')
        {
                $title = 'Netherlands';
                $count = number_format(435296);
        }
        else if ($schema == 'pl')
        {
                $title = 'Poland';
                $count = number_format(21987);
        }
        else if ($schema == 'sp')
        {
                $title = 'Spain';
                $count = number_format(54116);
        }
        else if ($schema == 'uk')
        {
                $title = 'United Kingdom';
                $count = number_format(1969257);
        }
        else if ($schema == 'usa')
        {
                $title = 'United States';
                $count = number_format(70706);
        }
        else if ($schema == 'fr')
        {
                $title = 'France';
                $count = number_format(39069);
        }
        else if ($schema == 'it')
        {
                $title = 'Italy';
                $count = number_format(17965);
        }
        else if ($schema == 'jp')
        {
                $title = 'Japan';
                $count = number_format(83289);
        }
        $table = DB_PREFIX . "distance_$schema";
        $filename = "distance_$schema.php";
        if (!empty($title) AND !empty($count) AND !empty($table) AND !empty($filename))
        {
                print_progress_begin('<b>Importing ' . $count . ' longitude and lattitude coordinates for ' . $title . '</b>, please wait.', '.', 'progressspanimportdistance' . $table);
                $newfile = DIR_SERVER_ROOT . 'install/importers/' . $filename;
                if (file_exists($newfile))
                {
                        require_once($newfile);
                }
                else
                {
                        echo '<span style="color:red">Distance import file for ' . $title . ' not found.</span>';
                }
                print_progress_end();
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>