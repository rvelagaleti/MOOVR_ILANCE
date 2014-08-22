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

// return all available tables 
$result_tbl = $ilance->db->query("SHOW TABLES"); 

$tables = array(); 
while ($row = $ilance->db->fetch_row($result_tbl))
{ 
        $tables[] = $row[0]; 
} 

$output = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"; 
$output .= "<schema version=\"$ilconfig[current_sql_version]\">\n"; 

// iterate over each table and return the fields for each table
foreach ($tables AS $table)
{ 
        $output .= "\t<table name=\"$table\">\n"; 
        $result_fld = $ilance->db->query("SHOW FIELDS FROM ".$table); 

        while($row1 = $ilance->db->fetch_row($result_fld))
        {
                $output .= "\t\t<field name=\"$row1[0]\" type=\"$row1[1]\"";
                $output .= ($row1[3] == "PRI") ? " primary_key=\"yes\" />\n" : " />\n";
        } 

        $output .= "\t</table>\n"; 
} 

$output .= "</schema>"; 

/*
$f = fopen(DIR_XML . 'database_schema.xml', 'w');
if ($f === false)
{
        @unlink(DIR_XML . 'database_schema.xml');
}
else 
{
        fwrite($f, $output);
        fclose($f);
}
*/

header('Content-type: application/xml; charset="' . $ilconfig['template_charset'] . '"');
echo $output;

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>