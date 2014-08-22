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
* Zip class to perform the majority of compression and archiving functions within ILance.
*
* @package      iLance\Zip
* @version      4.0.0.8059
* @author       ILance
*/
class zip
{
        /**
        *
        */
        var $datasec = array();
        
        /**
        *
        */
        var $ctrl_dir = array();
        
        /**
        *
        */
        var $eof_ctrl_dir = "\x50\x4b\x05\x06\x00\x00\x00\x00";
        
        /**
        *
        */
        var $old_offset = 0;
      
        /*
        * ...
        *
        * @param       
        *
        * @return      
        */
        function add_dir($name = '')
        {
                $name = str_replace("\\", "/", $name);
                $fr = "\x50\x4b\x03\x04\x0a\x00\x00\x00\x00\x00\x00\x00\x00\x00";
                $fr .= pack("V", 0);
                $fr .= pack("V", 0);
                $fr .= pack("V", 0);
                $fr .= pack("v", mb_strlen($name));
                $fr .= pack("v", 0);
                $fr .= $name;
                $fr .= pack("V", 0);
                $fr .= pack("V", 0);
                $fr .= pack("V", 0);
                $this->datasec[] = $fr;
                $new_offset = mb_strlen(implode("", $this->datasec));
                $cdrec = "\x50\x4b\x01\x02\x00\x00\x0a\x00\x00\x00\x00\x00\x00\x00\x00\x00";
                $cdrec .= pack("V", 0);
                $cdrec .= pack("V", 0);
                $cdrec .= pack("V", 0);
                $cdrec .= pack("v", mb_strlen($name));
                $cdrec .= pack("v", 0);
                $cdrec .= pack("v", 0);
                $cdrec .= pack("v", 0);
                $cdrec .= pack("v", 0);
                $cdrec .= pack("V", 16);
                $cdrec .= pack("V", $this->old_offset);
                $cdrec .= $name;
                $this->ctrl_dir[] = $cdrec;
                $this->old_offset = $new_offset;
                return;
        }
    
        /*
        * ...
        *
        * @param       
        *
        * @return      
        */
        function add_file($data, $name)
        {
                // $fp = fopen($data, "rb");
                // $data = fread($fp, filesize($data));
                // fclose($fp);
                $name = str_replace("\\", "/", $name);
                $unc_len = mb_strlen($data);
                $crc = crc32($data);
                $zdata = gzcompress($data);
                $zdata = substr ($zdata, 2, -4);
                $c_len = mb_strlen($zdata);
                $fr = "\x50\x4b\x03\x04\x14\x00\x00\x00\x08\x00\x00\x00\x00\x00";
                $fr .= pack("V", $crc);
                $fr .= pack("V", $c_len);
                $fr .= pack("V", $unc_len);
                $fr .= pack("v", mb_strlen($name));
                $fr .= pack("v", 0);
                $fr .= $name;
                $fr .= $zdata;
                $fr .= pack("V", $crc);
                $fr .= pack("V", $c_len);
                $fr .= pack("V", $unc_len);
                $this->datasec[] = $fr;
                $new_offset = mb_strlen(implode("", $this->datasec));
                $cdrec = "\x50\x4b\x01\x02\x00\x00\x14\x00\x00\x00\x08\x00\x00\x00\x00\x00";
                $cdrec .= pack("V", $crc);
                $cdrec .= pack("V", $c_len);
                $cdrec .= pack("V", $unc_len);
                $cdrec .= pack("v", mb_strlen($name));
                $cdrec .= pack("v", 0);
                $cdrec .= pack("v", 0);
                $cdrec .= pack("v", 0);
                $cdrec .= pack("v", 0);
                $cdrec .= pack("V", 32);
                $cdrec .= pack("V", $this->old_offset);
                $cdrec .= $name;
                $this->old_offset = $new_offset;
                $this->ctrl_dir[] = $cdrec;
        }
    
        /*
        * ...
        *
        * @param       
        *
        * @return      
        */
        function file()
        {
                $data = implode("", $this->datasec);
                $ctrldir = implode("", $this->ctrl_dir);
                return $data . $ctrldir . $this->eof_ctrl_dir . pack("v", sizeof($this->ctrl_dir)) . pack("v", sizeof($this->ctrl_dir)) . pack("V", mb_strlen($ctrldir)) . pack("V", mb_strlen($data)) . "\x00\x00";
        }
}

/*
$zipTest = new zipfile();
$zipTest->add_dir("images/");
$zipTest->add_file("images/box1.jpg", "images/box1.jpg");
$zipTest->add_file("images/box2.jpg", "images/box2.jpg");
// Return Zip File to Browser
Header("Content-type: application/octet-stream");
Header ("Content-disposition: attachment; filename=zipTest.zip");
echo $zipTest->file();
*/
// Alternatively, you can write the file to the file system and provide a link:
/*
	$filename = "output.zip";
	$fd = fopen ($filename, "wb");
	$out = fwrite ($fd, $zipTest -> file());
	fclose ($fd);
	echo "<a href=\"output.zip\">Click here to download the new zip file.</a>";
*/

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>