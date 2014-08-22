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
    die('<strong>Warning:</strong> This script cannot be loaded indirectly.  Operation aborted.');
}

$ilance->timer->start();

class MySQLDump
{
        var $database = '';
        
        // remember to change the table prefix ( v3_ ) below to your own!!
        var $skipdump = array(
            'ilance_sessions'
        );
        
        function MySQLDump($db)
        {
                return $this->setDatabase($db);
        }
    
        function setDatabase($db)
        {
                $this->database = $db;
                return true;
        }
    
        function getDatabase()
        {
                return $this->database;
        }
    
        function getStructure()
        {
                if (!(@mysql_select_db($this->database)))
                {
                        return false;
                }
            
                // get table names from database
                $records = @mysql_list_tables($this->database);
                
                // for each table it creates the CREATE query
                $structure = '';
                while ($record = @mysql_fetch_row($records))
                {
                        $table = $record[0];
                        
                        //Header
                        $structure .= "-- \n";
                        $structure .= "-- ".SITE_NAME." table structure for table `{$table}` \n";
                        $structure .= "-- \n\n";
            
                        //Dump Structure
                        $structure .= "DROP TABLE IF EXISTS `{$table}`; \n";
                        $tableStructure = @mysql_fetch_assoc(@mysql_query("SHOW CREATE TABLE $table"));
                        $structure .= $tableStructure['Create Table'];
                        $structure .= ";\n\n\n";
                }
                return $structure;
        }
    
        function getData()
        {
                if (!(@mysql_select_db($this->database)))
                {
                        return false;
                }
                
                // get table names from database
                $records = @mysql_list_tables($this->database);
                
                // for each record it creates the INSERT query
                $data = '';
                while ($record = @mysql_fetch_row($records))
                {
                        $table = $record[0];
                        if (!in_array($table, $this->skipdump))
                        {
                                // header
                                $data .= "-- \n";
                                $data .= "-- ".SITE_NAME." data dump for table `$table` \n";
                                $data .= "-- \n\n";
                    
                                // dump data
                                $records2 = mysql_query("SELECT * FROM `$table`");
                                while ($record2 = @mysql_fetch_assoc($records2))
                                {
                                        unset($tmp_data);
                                        $data .= "INSERT INTO `$table` VALUES (";
                                        
                                        // values
                                        $tmp_data = '';
                                        foreach ($record2 as $key => $value)
                                        {
                                                $tmp_data .= ( !isset($record2[$key]) ) ? 'NULL' : "'".addslashes($value)."'";
                                                $tmp_data .= ', ';
                                        }
                                        $tmp_data = mb_substr($tmp_data, 0, -2);
                                        $data .= $tmp_data.");\n";
                                }
                                $data .= "\n\n";    
                        }
                        else
                        {
                                // header
                                $data .= "-- \n";
                                $data .= "-- ".SITE_NAME." skipping data dump for table `$table` \n";
                                $data .= "-- \n\n";    
                        }
                }
                return $data;
        }
    
        function getDump()
        {
                if (!(@mysql_select_db($this->database)))
                {
                        return false;
                }
                return $this->getStructure().$this->getData();
        }
    
        function writeDump($filename)
        {
                if (!(@mysql_select_db($this->database)) || !($fp = @fopen($filename,'wb')))
                {
                        return false;
                }
                $dump = $this->getDump();
                $return = (@fwrite($fp,$dump,@mb_strlen($dump))) ? true : false;
                @fclose($fp);
                return $return;
        }
}

class zipfile
{
        var $datasec = array();
        var $ctrl_dir = array();
        var $eof_ctrl_dir = "\x50\x4b\x05\x06\x00\x00\x00\x00";
        var $old_offset = 0;
    
        function add_dir($name)
        {
                $name = str_replace("\\", "/", $name);
                $fr = "\x50\x4b\x03\x04";
                $fr .= "\x0a\x00";
                $fr .= "\x00\x00";
                $fr .= "\x00\x00";
                $fr .= "\x00\x00\x00\x00";
                $fr .= pack("V",0);
                $fr .= pack("V",0);
                $fr .= pack("V",0);
                $fr .= pack("v", mb_strlen($name) );
                $fr .= pack("v", 0 );
                $fr .= $name;
                $this->datasec[] = $fr;
                $new_offset = mb_strlen(implode("", $this->datasec));
        
                // now add to central record
                $cdrec = "\x50\x4b\x01\x02";
                $cdrec .="\x00\x00";
                $cdrec .="\x0a\x00";
                $cdrec .="\x00\x00";
                $cdrec .="\x00\x00";
                $cdrec .="\x00\x00\x00\x00";
                $cdrec .= pack("V", 0);
                $cdrec .= pack("V", 0);
                $cdrec .= pack("V", 0);
                $cdrec .= pack("v", mb_strlen($name) );
                $cdrec .= pack("v", 0);
                $cdrec .= pack("v", 0);
                $cdrec .= pack("v", 0);
                $cdrec .= pack("v", 0);
                $ext = "\x00\x00\x10\x00";
                $ext = "\xff\xff\xff\xff";
                $cdrec .= pack("V", 16);
                $cdrec .= pack("V", $this->old_offset);
                $this -> old_offset = $new_offset;
                $cdrec .= $name;
                $this -> ctrl_dir[] = $cdrec;
        }
    
        function add_file($data, $name)
        {
                $name = str_replace("\\", "/", $name);
                $fr = "\x50\x4b\x03\x04";
                $fr .= "\x14\x00";
                $fr .= "\x00\x00";
                $fr .= "\x08\x00";
                $fr .= "\x00\x00\x00\x00";
                $unc_len = mb_strlen($data);
                $crc = crc32($data);
                $zdata = gzcompress($data);
                $zdata = mb_substr(mb_substr($zdata, 0, mb_strlen($zdata) - 4), 2);
                $c_len = mb_strlen($zdata);
                $fr .= pack("V", $crc);
                $fr .= pack("V", $c_len);
                $fr .= pack("V", $unc_len);
                $fr .= pack("v", mb_strlen($name) );
                $fr .= pack("v", 0);
                $fr .= $name;
                $fr .= $zdata;
                $fr .= pack("V", $crc);
                $fr .= pack("V", $c_len);
                $fr .= pack("V", $unc_len);
                $this->datasec[] = $fr;
                $new_offset = mb_strlen(implode("", $this->datasec));
                $cdrec = "\x50\x4b\x01\x02";
                $cdrec .="\x00\x00";
                $cdrec .="\x14\x00";
                $cdrec .="\x00\x00";
                $cdrec .="\x08\x00";
                $cdrec .="\x00\x00\x00\x00";
                $cdrec .= pack("V", $crc);
                $cdrec .= pack("V", $c_len);
                $cdrec .= pack("V", $unc_len);
                $cdrec .= pack("v", mb_strlen($name));
                $cdrec .= pack("v", 0);
                $cdrec .= pack("v", 0);
                $cdrec .= pack("v", 0);
                $cdrec .= pack("v", 0);
                $cdrec .= pack("V", 32);
                $cdrec .= pack("V", $this -> old_offset );
                $this -> old_offset = $new_offset;
                $cdrec .= $name;
                $this -> ctrl_dir[] = $cdrec;
        }
    
        function file()
        {
                $data = implode("", $this->datasec);
                $ctrldir = implode("", $this->ctrl_dir);
                return
                    $data .
                    $ctrldir .
                    $this->eof_ctrl_dir .
                    pack("v", sizeof($this->ctrl_dir)) .
                    pack("v", sizeof($this->ctrl_dir)) .
                    pack("V", mb_strlen($ctrldir)) .
                    pack("V", mb_strlen($data)) . "\x00\x00";
        }
}

/*
 MySQL Dump Settings
*/
$use_gzip = true;
$remove_sql_file = true;
$remove_gzip_file = false;
$send_email_w_file = false;

$to = SITE_EMAIL;
$from = SITE_EMAIL;
$savepath = mb_substr(DIR_TMP, 0, -1);
$senddate = date("j F Y");

$subject = SITE_NAME . " Database Backup - $senddate";
$headers = "From: $from";

// Init mysqldump
$dumper = new MySQLDump(DB_DATABASE);

/*
$databasedump = $dumper->getDump();
//If you want to change the database
$dumper->setDatabase('database_2');
//If you want to get only the database structure
$str = $dumper->getStructure();
//If you want to get only the database data
$str = $dumper->getData(); 
*/

if ($send_email_w_file)
{
		$message = 'The ' . SITE_NAME .' database has been backed up and is attached to this email.';
        $backuptasks = 'was emailed to ' . SITE_EMAIL . ' along with the database backup attachment';
}
else
{
		$message = 'The ' . SITE_NAME . ' database was backed up and placed in ' . $savepath;
        $backuptasks = 'is placed in ' . $savepath;
}

$date = date('mdy-hia');

// path and filename to store database backup
$filename = $savepath . '/' . DB_DATABASE . '-' . $date . '.sql';

// fetch mysql database dump
$dumper->writeDump($filename);

if ($use_gzip)
{
        $zipfile = new zipfile();
        
        $zipfile->add_dir($savepath);
        $file6 = fopen($filename, 'rb');
        $filedata = fread($file6, filesize($filename));
        fclose($file6);
        
        $zipfile->add_file($filedata, $filename);
        $filename3 = $savepath . '/' . DB_DATABASE . '-' . $date . '_sql.tar.gz';
        $fd = fopen($filename3, 'wb');
        $out = fwrite($fd, $zipfile->file());
        fclose($fd);
}

if ($remove_sql_file)
{
        if (isset($filename) AND file_exists($filename))
        {
                @unlink($filename);
        }
}

if ($use_gzip)
{
        $filename2 = $filename3;
}
else
{
        $filename2 = $savepath. '/' . DB_DATABASE . '-' . $date . '.sql';
}

if ($send_email_w_file)
{
        $fileatt_name = DB_DATABASE . '-' . $date . '_sql.tar.gz';
        $content = fread(fopen($filename2, 'rb'), filesize($filename2));
        $content = chunk_split(base64_encode($content));
        $uid = mb_strtoupper(md5(uniqid(time())));

        $header  = "From: $from\nReply-To: $from\n";
        $header .= "MIME-Version: 1.0\n";
        $header .= "Content-Type: multipart/mixed; boundary=$uid\n";
        $header .= "--$uid\n";
        $header .= "Content-Type: text/plain\n";
        $header .= "Content-Transfer-Encoding: 8bit\n\n";
        $header .= "$message\n";
        $header .= "--$uid\n";
        $header .= "Content-Type: application/gzip; name=\"$fileatt_name\"\n";
        $header .= "Content-Transfer-Encoding: base64\n";
        $header .= "Content-Disposition: attachment; filename=\"$fileatt_name\"\n\n";
        $header .= "$content\n";
        $header .= "--$uid--";
        
        mb_send_mail($to, $subject, '', $header);
}

if ($remove_gzip_file)
{
        if (file_exists($filename2))
        {
                @unlink($filename2);
        }
}

$ilance->timer->stop();
log_cron_action('{_the_database_was_successfully_backed_up_and} ' . $backuptasks, $nextitem, $ilance->timer->get());

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>