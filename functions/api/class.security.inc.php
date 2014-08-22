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
* Security class to perform the majority of security handling functions within ILance
*
* @package      iLance\Security
* @version      4.0.0.8059
* @author       ILance
*/
class security
{
        /**
        * Function to verify using an mx record checkup of a supplied email address.  Additionally, this 
        * will actually attempt to connect to the server and verify if the email actually exists.
        *
        * @param        string          email address
        * @param        string          level
        * @param        integer         time out
        *
        * @return       boolean         Returns true or false on good email address
        */
        function verify_email_mx($addr, $level, $timeout = 15000)
        {
                $ctlds = "com:net:org:edu:gov:mil:co:ne:or:ed:go:mi:";
                $gtlds = "com:net:org:edu:gov:mil:int:arpa:";
                $ccs = "ad:ae:af:ag:ai:al:am:an:ao:aq:ar:as:at:au:aw:az:ba:bb:bd:be:bf:" .
                "bg:bh:bi:bj:bm:bn:bo:br:bs:bt:bv:bw:by:bz:ca:cc:cf:cd:cg:ch:ci:" .
                "ck:cl:cm:cn:co:cr:cs:cu:cv:cx:cy:cz:de:dj:dk:dm:do:dz:ec:ee:eg:" .
                "eh:er:es:et:fi:fj:fk:fm:fo:fr:fx:ga:gb:gd:ge:gf:gh:gi:gl:gm:gn:" .
                "gp:gq:gr:gs:gt:gu:gw:gy:hk:hm:hn:hr:ht:hu:id:ie:il:in:io:iq:ir:" .
                "is:it:jm:jo:jp:ke:kg:kh:ki:km:kn:kp:kr:kw:ky:kz:la:lb:lc:li:lk:" .
                "lr:ls:lt:lu:lv:ly:ma:mc:md:mg:mh:mk:ml:mm:mn:mo:mp:mq:mr:ms:mt:" .
                "mu:mv:mw:mx:my:mz:na:nc:ne:nf:ng:ni:nl:no:np:nr:nt:nu:nz:om:pa:" .
                "pe:pf:pg:ph:pk:pl:pm:pn:pr:pt:pw:py:qa:re:ro:ru:rw:sa:sb:sc:sd:" .
                "se:sg:sh:si:sj:sk:sl:sm:sn:so:sr:st:su:sv:sy:sz:tc:td:tf:tg:th:" .
                "tj:tk:tm:tn:to:tp:tr:tt:tv:tw:tz:ua:ug:uk:um:us:uy:uz:va:vc:ve:" .
                "vg:vi:vn:vu:wf:ws:ye:yt:yu:za:zm:zr:zw:";
                $fail = 0;
                $addr = mb_strtolower($addr);
                $ud = explode("@", $addr);
                $levels = array();
                $slevels = 0;
                if (sizeof($ud) != 2 OR !$ud[0])
                {
                        return true;   
                }
                if (!empty($ud[1]))
                {
                        $levels = explode(".", $ud[1]);
                        $slevels = sizeof($levels);
                }
                else
                {
                        return true;
                }
                if ($slevels < 2) $fail = 1;
                $tld = $levels[$slevels-1];
                $tld = mb_ereg_replace("[>)}]$|]$", "", $tld);
                if (mb_strlen($tld) < 2 || mb_strlen($tld) > 3 && $tld != "arpa") $fail = 1;
                $level--;
                if ($level AND !$fail)
                {
                        $level--;
                        if (!mb_ereg($tld.":", $gtlds) AND !mb_ereg($tld.":", $ccs)) $fail = 2;
                }
                if ($level AND !$fail)
                {
                        $cd = $slevels - 2; $domain = $levels[$cd].".".$tld;
                        if (mb_ereg($levels[$cd].":", $ctlds))
                        {
                            $cd--;
                            $domain = $levels[$cd].".".$domain;
                        }
                }
                if ($level AND !$fail)
                {
                        $level--;
                        if (!getmxrr($domain, $mxhosts, $weight)) $fail = 3;
                }        
                if ($level AND !$fail)
                {
                        $level--;
                        while (!$sh && list($nul, $mxhost) = each($mxhosts))
                                $sh = fsockopen($mxhost, 25);
                        if (!$sh) $fail = 4;
                }
                if ($level AND !$fail)
                {
                        $level--;
                        set_socket_blocking($sh, false);
                        $out = '';
                        $t = 0;
                        while ($t++ < $timeout AND !$out)
                                $out = fgets($sh, 256);
                        if (!mb_ereg("^220", $out)) $fail = 5;
                }
                if (isset($sh)) fclose($sh);
                return $fail;
        }
        
        /**
        * Function to read and scan through an entire directory specified.
        *
        * @param        string          directory
        *
        * @return       string          Returns directory contents
        */
        function read_my_dir($dir)
        {
                global $tfile, $tdir; $i=0; $j=0; $myfiles;
                $md5_files = array();
                $html = '';
                $myfiles[][] = array();
                if (is_dir($dir))
                {
                        if ($dh = opendir($dir))
                        {
                                while (($file = readdir($dh)) !== false)
                                {
                                        if (!is_dir($dir . "\\" . $file) && ($file != "config.php") && ($file != "connect.php") && ($file != "config.php.dist" OR $file != "config.php.new") && ($file != "connect.php.dist" OR $file != "connect.php.new"))
                                        {
                                                $tfile[$i] = $file;
                                                $i++;
                                                $html .= $dir . "\\" . $file . "\n";
                                        }
                                        else
                                        {
                                                if (($file != ".") && ($file != "..") && ($file != ".svn") && ($file != "images") && ($file != "cache") && ($file != "uploads"))
                                                {   
                                                        $tdir[$j]=$file;
                                                        $html .= $this->read_my_dir($dir . "\\" . $file);
                                                        $j++;
                                                }
                                        }
                                }
                                closedir($dh);
                        }
                }
                return $html;
        }
        
        /**
        * Function to read, build and create a new php script based on md5 file hash of all files for the ILance product.  Ultimately we'll use this to allow the user
        * to check their original files for any changes since last download of iLance
        *
        * @return       nothing
        */
        function build_md5_filelist()
        {
                $filelist = $this->read_my_dir(DIR_SERVER_ROOT);
                $filelist = str_replace(DIR_SERVER_ROOT . "\\", "/", $filelist);
                $filelist = str_replace("\\", "/", $filelist);
                $files_a = explode("\n", $filelist);
                foreach ($files_a AS $key => $filename)
                {
                        $filename = trim($filename);
                        if ($filename == "")
                        {
                                unset($files_a[$key]);
                        }
                        else
                        {
                                $files_a[$key] = (mb_substr($filename, 0, 1) == "/" ? $filename : "/" . $filename);
                        }
                }
                sort($files_a);
                $files_a = array_unique($files_a);
                foreach ($files_a as $key => $filename)
                {
                        // Watch it, if root directory (ie. /), on windows dirname might return a backslash \
                        $directory = str_replace('\\', '/', dirname($filename));
                        $productfilename = $filename;
                        $filehash = @md5_file(DIR_SERVER_ROOT . $productfilename);
                        $file = basename($filename);
                        $md5_files[$directory][] = "'$file' => '$filehash'";
                }
                ksort($md5_files);
                if ($fp = fopen(DIR_SERVER_ROOT . "functions/core/functions_md5_checkup.php", 'wt'))
                {
                        // Write a header
                        fwrite($fp, "<?php\n");
                        fwrite($fp, "/*==========================================================================*\ \n|| ######################################################################## ||\n|| # ILance Marketplace Software\n|| # -------------------------------------------------------------------- # ||\n|| # Copyright ©2000–" . date('Y')  . " ILance Inc. All Rights Reserved.	          # ||\n|| # This file may not be redistributed in whole or significant part. 	  # ||\n|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||\n|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||\n|| # -------------------------------------------------------------------- # ||\n|| ######################################################################## ||\n\*==========================================================================*/\n\n");
                        fwrite($fp, "// #### Built on " . date('M, d, Y')  . " at " . date('H:i:s') . "\n");
                        fwrite($fp, "// #### Version " . ILANCEVERSION . "\n\n");
                        fwrite($fp, '$ilance_md5 = array(' . "\n");
                        foreach ($md5_files AS $dir => $data)
                        {
                                fwrite($fp, "\t'$dir' => array(" . "\n");
                                sort($data);
                                foreach ($data AS $key => $fileinfo)
                                {
                                        fwrite($fp, "\t\t$fileinfo,\n");
                                }
                                fwrite($fp, "\t),\n");
                        }
                        fwrite($fp, ");\n");
                        fwrite($fp, "\n");
                        fwrite($fp, "?>");
                        fclose($fp);
                }        
        }
        
        function fetch_csf_deny()
        {
                $file = file_get_contents("/etc/csf/csf.deny");
                $lines = explode("\n", $file);
                $ips = array();
                foreach ($lines AS $line)
                {
                        $explode = explode("#", $line);
                        if (isset($explode[1]) AND substr(trim($explode[1]), 0, 4) == "lfd:")
                        {
                                $ips[] = trim($explode[0]);
                        }
                }
                return $ips;
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>