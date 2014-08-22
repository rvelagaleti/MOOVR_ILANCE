<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 4.0.0 Build 8059
|| # -------------------------------------------------------------------- # ||
|| # Customer License # H7LhzCqPSNEABnY
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000ñ2014 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/
@set_time_limit(0);
@ignore_user_abort(true);

$ext = '.php';

$hr1 = '#\(\$(\w+)\s*=\s*&?\s*\$ilance->api' . 
       '\s*\(\s*([\'"])(\w+)\2\s*\)\s*\)\s*\?\s*eval\s*' . 
       '\(\s*"?\$\1"?\s*\)\s*:\s*false;#iU';
$hr2 = '#//\[p\:(\w+)\](?:.*)//\[/p\:\1\]#isU';

function build_tree(&$tree, $ext = '.php', $dir = './')
{
      if ($d = opendir($dir))
      {
            while (($f = readdir($d)) !== false)
            {
                  if ($f != '.' && $f != '..')
                  {
                        if (is_dir($dir.$f))
                        {
                              build_tree($tree,$ext,$dir.$f.'/');
                        }
                        elseif (mb_substr($f,-mb_strlen($ext))==$ext)
                        {
                              $tree[] = $dir.$f;
                        }
                  }
            }
      }				
}

$tree = array();
build_tree($tree, $ext);

$data = array();
foreach ($tree AS $f)
{
  $d = file_get_contents($f);
  preg_match_all($hr1, $d, $mm[0]);
  preg_match_all($hr2, $d, $mm[1]);
  foreach ($mm[0][3] AS $m) { $data[0]++; $data[1][$m] = ''; $data[2][$f][] = $m; }
  foreach ($mm[1][1] AS $m) { $data[0]++; $data[1][$m] = ''; $data[2][$f][] = $m; }
}

echo "<html>" .
     "<head><title>Hook Finder</title></head><body>" . 
     "<font size='4' face='arial,verdana,tahoma'>Hook Statistics</font><br />" .
     "<font size='3' face='arial,verdana,tahoma'>" .
     "Total Hooks: <b>$data[0]</b><br />" .
     "Unique Hooks: <b>" . count($data[1]) . "</b></font><br /><br />" .
     "<font size='4' face='arial,verdana,tahoma'>Files With Hooks</font><br />" .
     "<font size='2' face='arial,verdana,tahoma'>";
     foreach ($data[2] AS $k => $v)
     {
       echo "<b>$k</b> <br />" .
            "<font size='2' face='arial,verdana,tahoma'>";
       foreach ($v AS $h) { echo "-- $h <br />"; }
       echo "</font><br />";
     }
echo "<font></body></html>";

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>