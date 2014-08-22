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
* Function to print a character via javascript as a progress bar.
*
* @param	string		string
* @param        string          character to output while working
* @param        string          span id reference
*/
function print_progress_begin($str = '', $char = '.', $id = 'progressspan')
{
	flush();
	?>
	<p><?php echo $str; ?><br /><br />(<span style="color:#000; font-weight:bold" id="<?php echo $id; ?>"><?php echo $char; ?></span>)</p>
	<script type="text/javascript">
	<!--
	function print_progress()
	{
		<?php echo $id; ?>.innerText = <?php echo $id; ?>.innerText + "<?php echo $char; ?>";
		timer = setTimeout("print_progress();", 75);
	}
	if (document.all)
	{
		print_progress();
	}
	//-->
	</script>
	<?php
	flush();
}

/**
* Function to stop the characters via javascript to act like the progress bar is finished.
*/
function print_progress_end()
{
	flush();
	?>
	<script type="text/javascript">
	<!--
	if (document.all)
	{
		clearTimeout(timer);
	}
	//-->
	</script>
	<?php flush();
}

function convert_all_tables_collation($collate = 'utf8_general_ci', $charset = 'utf8')
{
        global $ilance;
	print_progress_begin('<b>Converting database, tables and fields to charset: ' . $charset . ' collation: ' . $collate . '</b>, please wait.', '.', 'progressspanutf8');
	$ilance->db->query("ALTER DATABASE `" . DB_DATABASE . "` DEFAULT CHARACTER SET $charset COLLATE $collate");
        $sql = $ilance->db->query("SHOW TABLES");
        if ($ilance->db->num_rows($sql) > 0)
        {
                while ($tables = $ilance->db->fetch_array($sql, DB_ASSOC))
                {
                        foreach ($tables AS $key => $value)
                        {
                                if (!empty($value))
                                {
                                        $ilance->db->query("ALTER TABLE $value COLLATE $collate");
                                        $sql2 = $ilance->db->query("SHOW FULL FIELDS FROM `$value`");
                                        if ($ilance->db->num_rows($sql2) > 0)
                                        {
                                                while ($row = $ilance->db->fetch_array($sql2, DB_ASSOC))
                                                {
                                                        // Is the field allowed to be null?
                                                        if ($row['Null'] == 'YES')
                                                        {
                                                                $nullable = 'NULL';
                                                        }
                                                        else
                                                        {
                                                                $nullable = 'NOT NULL';
                                                        }
                                                        if ($row['Default'] != '')
                                                        {
                                                                $default = "DEFAULT '" . $ilance->db->escape_string($row['Default']) . "'";
                                                        }
                                                        else
                                                        {
                                                                $default = "DEFAULT ''";
                                                        }
                                                        if (preg_match("/\bvarchar\b/i", $row['Type']) OR preg_match("/\bchar\b/i", $row['Type']) OR preg_match("/\benum\b/i", $row['Type']) OR preg_match("/\bmediumtext\b/i", $row['Type']) OR preg_match("/\btinytext\b/i", $row['Type']) OR preg_match("/\blongtext\b/i", $row['Type']) OR preg_match("/\btext\b/i", $row['Type']))
                                                        {
                                                                $field = $ilance->db->escape_string($row['Field']);
                                                                $ilance->db->query("ALTER TABLE `$value` CHANGE `$field` `$field` $row[Type] CHARACTER SET $charset COLLATE $collate $nullable $default");
                                                        }
                                                }
                                        }
                                }
                        }
                }
        }
	print_progress_end();
	return "<li>All database tables and fields are now set to charset: utf8 collation: utf8_general_ci</li>";
}

/**
* Function to update untranslated phrases to their master phrase so translators can have something to work from (instead of a blank phrase)
* This script is used when languages are being imported from AdminCP and when a old version of ILance is being upgraded to a newer version
* 
* @return      nothing
*/
function update_untranslated_phrases_to_master()
{
	global $ilance;
	$langs = $ilance->db->query("
		SELECT languagecode
		FROM " . DB_PREFIX . "language
	");
	if ($ilance->db->num_rows($langs) > 0)
	{
		while ($reslangs = $ilance->db->fetch_array($langs, DB_ASSOC))
		{
			$installedlanguages[] = $reslangs['languagecode'];
		}
		$installedlanguagescount = count($installedlanguages);
		if ($installedlanguagescount > 1)
		{
			foreach ($installedlanguages AS $languagetitle)
			{
				$sqlchk = $ilance->db->query("
					SELECT phraseid, text_original, text_" . mb_substr($languagetitle, 0, 3) . "
					FROM " . DB_PREFIX . "language_phrases
					WHERE text_" . mb_substr($languagetitle, 0, 3) . " = ''
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sqlchk) > 0)
				{
					while ($reschk = $ilance->db->fetch_array($sqlchk, DB_ASSOC))
					{
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "language_phrases
							SET text_" . mb_substr($languagetitle, 0, 3) . " = text_original
							WHERE phraseid = '" . $reschk['phraseid'] . "'
						");
					}
				}
			}
		}
	}
}

/**
* Function for importing or updating language phrases in the database.
* This function will only insert new phrases and will not update existing ones.
*
* @param	integer		per page value
* @param        integer         start from phrase number
*/
function import_language_phrases($perpage = 10000, $fromphrase = 0)
{
	global $ilance;
	print_progress_begin('<b>Importing stock phrases from xml</b>, please wait.', '.', 'progressspan');
	$data = array();
	$xml = file_get_contents(DIR_SERVER_ROOT . 'install/xml/master-phrases-english.xml');
	if ($xml == false)
	{
		return;
	}
	$xml_encoding = 'UTF-8';
	$xml_encoding = mb_detect_encoding($xml);
	if ($xml_encoding == 'ASCII') 
	{
		$xml_encoding = '';
	}
	$parser = xml_parser_create($xml_encoding);
	xml_parse_into_struct($parser, $xml, $data);
	$error_code = xml_get_error_code($parser);
	xml_parser_free($parser);
	if ($error_code == 0) 
	{
		$ilance->common = construct_object('api.common');
		$ilance->xml = construct_object('api.xml');
		$result = $ilance->xml->process_lang_xml($data, $xml_encoding);
		$installedlanguages = array();
		$langquery = $ilance->db->query("
			SELECT languageid
			FROM " . DB_PREFIX . "language
			WHERE languagecode = '" . $ilance->db->escape_string($result['lang_code']) . "'
		");
		if ($ilance->db->num_rows($langquery) == 0) 
		{
			print_progress_end();
			return "<li>We're sorry.  Language pack uploading requires the actual language to already exist within the database before you upload any new language packages.  Please retry your action using a language pack that already exists (or you can simply create the new language than retry your upload action)</li>";
		}
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "language 
			SET title = '" . $ilance->db->escape_string($result['title']) . "',
			charset = '" . $ilance->db->escape_string($result['charset']) . "',
			locale = '" . $ilance->db->escape_string($result['locale']) . "',
			author = '" . $ilance->db->escape_string($result['author']) . "',
			languageiso = '" . $ilance->db->escape_string($result['languageiso']) . "',
			textdirection = '" . $ilance->db->escape_string($result['textdirection']) . "',
			canselect = '" . intval($result['canselect']) . "',
			replacements = '" . $ilance->db->escape_string($result['replacements']) . "' 
			WHERE languagecode = '" . $ilance->db->escape_string($result['lang_code']) . "'
			LIMIT 1
		");
		$lfn = 'text_' . mb_substr($result['lang_code'], 0, 3);
		$phrasearray = $result['phrasearray'];
		$phrasecounttotal = count($phrasearray);
		$phraseperpg = 10000;
		$added = $updated = 0;
		for ($i = 0; $i < $phrasecounttotal; $i++)
		{
			$query = $ilance->db->query("
				SELECT phraseid
				FROM " . DB_PREFIX . "language_phrases
				WHERE varname = '" . trim($ilance->db->escape_string($phrasearray[$i][1])) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($query) == 0)
			{
				$phrasearray[$i][2] = ilance_htmlentities($phrasearray[$i][2]);
				$ilance->db->query("
					INSERT INTO " . DB_PREFIX . "language_phrases 
					(phrasegroup, varname, ismaster, text_original, $lfn) 
					VALUES (
					'" . $ilance->db->escape_string($phrasearray[$i][0]) . "',
					'" . $ilance->db->escape_string($phrasearray[$i][1]) . "',
					'1',
					'" . $ilance->db->escape_string($phrasearray[$i][2]) . "',
					'" . $ilance->db->escape_string($phrasearray[$i][2]) . "')
				");
				$added++;
			}
			else
			{
				$phrasearray[$i][2] = ilance_htmlentities($phrasearray[$i][2]);
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "language_phrases
					SET text_original = '" . $ilance->db->escape_string($phrasearray[$i][2]) . "',
					$lfn = IF(isupdated = 0, '" . $ilance->db->escape_string($phrasearray[$i][2]) . "', $lfn),
					phrasegroup = IF(phrasegroup != '" . $ilance->db->escape_string($phrasearray[$i][0]) . "', '" . $ilance->db->escape_string($phrasearray[$i][0]) . "', phrasegroup)
					WHERE varname = '" . $ilance->db->escape_string($phrasearray[$i][1]) . "'
				");
				$updated++;
			}
		}
		update_untranslated_phrases_to_master();		
		print_progress_end();
		return "<li>$phrasecounttotal total phrases: newly added: $added, updated: $updated</li>";
	}
	else 
	{
		print_progress_end();
		$error_string = xml_error_string($error_code);
		return "<li>We're sorry.  There was an error with the formatting of the xml language package file [$error_string].  Please fix the problem and retry your action.</li>";
	}	
}

/**
* Function for importing or updating email templates in the database.
* This function will only insert new email templates and will not update existing ones.
*/
function import_email_templates($overwrite = true)
{
	global $ilance;
	print_progress_begin('<b>Importing stock email templates from xml</b>, please wait.', '.', 'progressspan2');
	$data = array();
	$xml = file_get_contents(DIR_SERVER_ROOT . 'install/xml/master-emails-english.xml');
	if ($xml == false)
	{
		return;
	}
	$xml_encoding = 'UTF-8';
	$xml_encoding = mb_detect_encoding($xml);
	if ($xml_encoding == 'ASCII') 
	{
		$xml_encoding = '';
	}
	$parser = xml_parser_create($xml_encoding);
	xml_parse_into_struct($parser, $xml, $data);
	$error_code = xml_get_error_code($parser);
	xml_parser_free($parser);
	if ($error_code == 0)
	{
		$ilance->common = construct_object('api.common');
		$ilance->xml = construct_object('api.xml');
		$result = $ilance->xml->process_email_xml($data, $xml_encoding);
		$query = $ilance->db->query("
			SELECT languageid, title, languagecode, charset, locale, author, textdirection, languageiso, canselect, installdate, replacements
			FROM " . DB_PREFIX . "language
			WHERE languagecode = '" . $ilance->db->escape_string($result['langcode']) . "'
			LIMIT 1
		");
		if ($ilance->db->num_rows($query) == 0)
		{
			print_progress_end();
			return "<li>We're sorry.  Your marketplace needs to already have the language <strong>" . ucfirst($result['langcode']) . "</strong> created in your system before you can import new email templates.  Please retry your action using a language that already exists</li>";
		}
		$phrasearray = $result['emailarray'];
		$lfn1 = 'subject_' . mb_substr($result['langcode'], 0, 3);
		$lfn2 = 'message_' . mb_substr($result['langcode'], 0, 3);
		$lfn3 = 'name_' . mb_substr($result['langcode'], 0, 3);
		$docount = count($phrasearray);
		$added = $updated = 0;
		for ($i = 0; $i < $docount; $i++)
		{
			$product = isset($phrasearray[$i][5]) ? $phrasearray[$i][5] : 'ilance';
			$ishtml = isset($phrasearray[$i][11]) ? $phrasearray[$i][11] : 0;
			if (!empty($phrasearray[$i][0]) AND !empty($phrasearray[$i][1]) AND !empty($phrasearray[$i][2]) AND !empty($phrasearray[$i][3]) AND !empty($phrasearray[$i][4]))
			{
				$sql = $ilance->db->query("
					SELECT id
					FROM " . DB_PREFIX . "email
					WHERE varname = '" . $ilance->db->escape_string($phrasearray[$i][4]) . "'
				");
				if ($ilance->db->num_rows($sql) == 0)
				{
					$phrasearray[$i][0] = ilance_htmlentities($phrasearray[$i][0]);
					$phrasearray[$i][1] = ilance_htmlentities($phrasearray[$i][1]);
					$phrasearray[$i][2] = ilance_htmlentities($phrasearray[$i][2]);
					$ilance->db->query("
						INSERT INTO " . DB_PREFIX . "email 
						(id, varname, subject_original, message_original, $lfn1, $lfn2, $lfn3, type, product, cansend, departmentid, buyer, seller, admin, ishtml) 
						VALUES (
						NULL,
						'" . $ilance->db->escape_string($phrasearray[$i][4]) . "', 
						'" . $ilance->db->escape_string($phrasearray[$i][1]) . "', 
						'" . $ilance->db->escape_string($phrasearray[$i][2]) . "',
						'" . $ilance->db->escape_string($phrasearray[$i][1]) . "', 
						'" . $ilance->db->escape_string($phrasearray[$i][2]) . "', 
						'" . $ilance->db->escape_string($phrasearray[$i][0]) . "', 
						'" . $ilance->db->escape_string($phrasearray[$i][3]) . "',
						'" . $ilance->db->escape_string($product) . "',
						'" . intval($phrasearray[$i][6]) . "',
						'" . intval($phrasearray[$i][7]) . "',
						'" . intval($phrasearray[$i][8]) . "',
						'" . intval($phrasearray[$i][9]) . "',
						'" . intval($phrasearray[$i][10]) . "',
						'" . intval($ishtml) . "')
					");
					$added++;
				}
				else
				{
					$phrasearray[$i][1] = ilance_htmlentities($phrasearray[$i][1]);
					$phrasearray[$i][2] = ilance_htmlentities($phrasearray[$i][2]);
					$extraquery = '';
					if ($overwrite)
					{
						$extraquery .= $lfn1 . " = '" . $ilance->db->escape_string($phrasearray[$i][1]) . "',";
						$extraquery .= $lfn2 . " = '" . $ilance->db->escape_string($phrasearray[$i][2]) . "',";
					}
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "email
						SET subject_original = '" . $ilance->db->escape_string($phrasearray[$i][1]) . "',
						message_original = '" . $ilance->db->escape_string($phrasearray[$i][2]) . "',
						$extraquery
						name_eng = '" . $ilance->db->escape_string($phrasearray[$i][0]) . "',
						product = '" . $ilance->db->escape_string($product) . "',
						buyer = '" . intval($phrasearray[$i][8]) . "',
						seller = '" . intval($phrasearray[$i][9]) . "',
						admin = '" . intval($phrasearray[$i][10]) . "',
						ishtml = '" . intval($ishtml) . "'
						WHERE varname = '" . trim($ilance->db->escape_string($phrasearray[$i][4])) . "'
						LIMIT 1
					");
					$updated++;
				}
			}
		}
		print_progress_end();
		$extrainfo = '';
		if ($overwrite)
		{
			$extrainfo .= ' (overwritten from xml)';	
		}
		return '<li>' . $docount . ' ' . ucfirst($result['langcode']) . ' email templates, newly added: ' . $added . ', updated: ' . $updated . $extrainfo . '</li>';
	}
	else 
	{
		print_progress_end();
		$error_string = xml_error_string($error_code);
		return "<li>We're sorry.  There was an error with the formatting of the language xml package file [$error_string].  Please fix the problem and retry your action.</li>";
	}
}

/**
* Function for importing or updating template styles into the database.
* This function will only insert new email templates and will not update existing ones.
*/
function import_templates()
{
	global $ilance;
	print_progress_begin('<b>Importing stock CSS from xml</b>, please wait.', '.', 'progressspan3');
	$xml = file_get_contents(DIR_SERVER_ROOT . 'install/xml/master-style.xml');
	if ($xml == false)
	{
		return;
	}
	$data = array();
	$xml_encoding = 'UTF-8';
	$xml_encoding = mb_detect_encoding($xml);
	if ($xml_encoding == 'ASCII') 
	{
		$xml_encoding = '';
	}
	$parser = xml_parser_create($xml_encoding);
	xml_parse_into_struct($parser, $xml, $data);
	$error_code = xml_get_error_code($parser);
	xml_parser_free($parser);
	if ($error_code == 0)
	{
		$ilance->xml = construct_object('api.xml');
		$result = $ilance->xml->process_style_xml($data, $xml_encoding);
		if ($result['ilversion'] != $ilance->config['ilversion'])
		{
			print_progress_end();
			return "<li>The version of the this template/style package <strong>" . $result['ilversion'] . "</strong> is different than the installed version of ILance <strong>".$ilance->config['ilversion']."</strong>.  The operation has aborted due to a version conflict.</li>";
		}
		$notice = '';
		$query = $ilance->db->query("
			SELECT styleid, name, visible, sort
			FROM " . DB_PREFIX . "styles
			WHERE name = '" . $ilance->db->escape_string($result['name']) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($query) == 0)
		{
			$ilance->db->query("
				INSERT INTO " . DB_PREFIX . "styles
				(styleid, name, visible, sort)
				VALUES(
				NULL,
				'" . $ilance->db->escape_string($result['name']) . "',
				'1',
				'100')
			", 0, null, __FILE__, __LINE__);
			
			$newstyleid = $ilance->db->insert_id();
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "configuration
				SET value = '" . intval($newstyleid) . "'
				WHERE name = 'defaultstyle'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			$templatearray = $result['templatearray'];
			$templatecount = count($templatearray);
			$added = $updated = 0;
			for ($i = 0; $i < $templatecount; $i++)
			{
				if (isset($templatearray[$i][0]) AND !empty($templatearray[$i][0]))
				{
					$ilance->db->query("
						INSERT INTO " . DB_PREFIX . "templates
						(tid, name, description, type, status, iscustom, styleid, createdate, original, content, product, sort)
						VALUES(
						NULL,
						'" . $ilance->db->escape_string($templatearray[$i][0]) . "',
						'" . $ilance->db->escape_string($templatearray[$i][1]) . "',
						'" . $ilance->db->escape_string($templatearray[$i][2]) . "',
						'1',
						'0',
						'" . intval($newstyleid) . "',
						NOW(),
						'" . $ilance->db->escape_string($templatearray[$i][5]) . "',
						'" . $ilance->db->escape_string($templatearray[$i][5]) . "',
						'" . $ilance->db->escape_string($templatearray[$i][3]) . "',
						'" . intval($templatearray[$i][4]) . "')
					", 0, null, __FILE__, __LINE__);
					$added++;
				}
			}
			print_progress_end();
			return "<li>$templatecount CSS templates, added: $added, updated: $updated</li>";
		}
		else
		{
			$styleid = $ilance->db->fetch_field(DB_PREFIX . "styles", "name = '" . trim($ilance->db->escape_string($result['name'])) . "'", "styleid");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "styles
				SET visible = '1'
				WHERE name = '" . trim($ilance->db->escape_string($result['name'])) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			$templatearray = $result['templatearray'];
			$templatecount = count($templatearray);
			$added = $updated = 0;
			for ($i = 0; $i < $templatecount; $i++)
			{
				if (isset($templatearray[$i][0]) AND !empty($templatearray[$i][0]))
				{
					$sql = $ilance->db->query("
						SELECT tid
						FROM " . DB_PREFIX . "templates
						WHERE name = '" . trim($ilance->db->escape_string($templatearray[$i][0])) . "'
							AND type = '" . $ilance->db->escape_string($templatearray[$i][2]) . "'
							AND styleid = '" . $styleid . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) == 0)
					{
						$ilance->db->query("
							INSERT INTO " . DB_PREFIX . "templates
							(tid, name, description, type, status, styleid, updatedate, original, content, product, sort)
							VALUES(
							NULL,
							'" . $ilance->db->escape_string($templatearray[$i][0]) . "',
							'" . $ilance->db->escape_string($templatearray[$i][1]) . "',
							'" . $ilance->db->escape_string($templatearray[$i][2]) . "',
							'1',
							'" . intval($styleid) . "',
							NOW(),
							'" . $ilance->db->escape_string($templatearray[$i][5]) . "',
							'" . $ilance->db->escape_string($templatearray[$i][5]) . "',
							'" . $ilance->db->escape_string($templatearray[$i][3]) . "',
							'" . intval($templatearray[$i][4]) . "')
						", 0, null, __FILE__, __LINE__);
						$added++;
					}
					else
					{
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "templates
							SET original = '" . $ilance->db->escape_string($templatearray[$i][5]) . "',
							content = '" . $ilance->db->escape_string($templatearray[$i][5]) . "',
							type = '" . $ilance->db->escape_string($templatearray[$i][2]) . "',
							product = '" . $ilance->db->escape_string($templatearray[$i][3]) . "',
							sort = '" . intval($templatearray[$i][4]) . "',
							status = '1'
							WHERE name = '" . $ilance->db->escape_string($templatearray[$i][0]) . "'
								AND type = '" . $ilance->db->escape_string($templatearray[$i][2]) . "'
								AND styleid = '" . intval($styleid) . "'
						", 0, null, __FILE__, __LINE__);
						$updated++;
					}
				}
			}
			// set the new or updated style as the default style to ensure the latest upgraded templates make use of the newest release css efforts
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "configuration
				SET value = '" . intval($styleid) . "'
				WHERE name = 'defaultstyle'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			print_progress_end();
			return "<li>$templatecount CSS templates, newly added: $added, updated: $updated</li>";
		}
	}
	else
        {
		print_progress_end();
		$error_string = xml_error_string($error_code);
		return "<li>We're sorry.  There was an error with the formatting of the template package file [$error_string].  Please fix the problem and retry your action.</li>";
	}
}

/**
* Function for importing new master categories for a fresh installation of ILance
*/
function import_master_categories()
{
	global $ilance;
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "categories VALUES (NULL, 0, '', 1, 'web-design', 'Web Design', 'Web Design Service and Solutions', 1, 1, '0000-00-00 00:00:00', 0, 1, 1, 1, 0, 'default', '0', '0', '', 'service', '', 0, '0.00', '0.00', 0, 0, 'lowest', '0', '0', '0', '0', '', '', '', '', '', '', '', '', 1, 0, 0, 0)", 0, null, __FILE__, __LINE__);
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "categories VALUES (NULL, 0, '', 1, 'programming', 'Programming', 'Programming Solutions', 1, 1, '0000-00-00 00:00:00', 0, 0, 0, 0, 0, 'default', '0', '0', '', 'service', '', 0, '0.00', '0.00', 0, 0, 'lowest', '0', '0', '0', '0', '', '', '', '', '', '', '', '', 1, 0, 0, 0)", 0, null, __FILE__, __LINE__);
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "categories VALUES (NULL, 0, '', 1, 'computers', 'Computers', 'Buy Computers from our top sellers', 1, 1, '0000-00-00 00:00:00', 0, 1, 0, 1, 0, '', '0', '0', '', 'product', '', 0, '0.00', '0.00', 0, 0, 'lowest', '0', '0', '1', '0', '', '', '', '', '', '', '', '', 1, 0, 0, 0)", 0, null, __FILE__, __LINE__);
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "categories VALUES (NULL, 0, '', 1, 'arts-collectables', 'Arts and Collectables', 'Buy arts and collectables from our top sellers', 1, 1, '0000-00-00 00:00:00', 0, 1, 0, 1, 0, '', '0', '0', '', 'product', '', 0, '0.00', '0.00', 0, 0, 'lowest', '0', '0', '1', '0', '', '', '', '', '', '', '', '', 1, 0, 0, 0)", 0, null, __FILE__, __LINE__);
	$ilance->categories = construct_object('api.categories');
	$ilance->categories_manager->set_levels();
	$ilance->categories_manager->rebuild_category_tree(0, 1, 'service', 'eng');
	$ilance->categories_manager->rebuild_category_tree(0, 1, 'product', 'eng');
	$ilance->categories_manager->rebuild_category_geometry_install();
	rebuild_spatial_category_indexes(false);
}

/**
* Function to converts an adjacency list to a modified preorder tree traversal
* start as: rebuild_category_tree(1, 1);
*
* @param	integer 	starting parentid (default 0)
* @param        integer         starting left (default 1)
*/
function rebuild_category_tree($parentid = 0, $left = 1)
{
	global $ilance;
	$right = ($left + 1);
	$result = $ilance->db->query("
		SELECT cid
		FROM " . DB_PREFIX . "categories
		WHERE parentid = '" . intval($parentid) . "'
	");   
	while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
	{   
		$right = rebuild_category_tree($row['cid'], $right);   
	}   
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "categories
		SET lft = '" . intval($left) . "', rgt = '" . intval($right) . "'
		WHERE cid = '" . intval($parentid) . "'
	");
	return ($right + 1);
}

/**
* Function to build a spatial geometry index for the category system in 4.0.0
*/
function rebuild_spatial_category_indexes($html = false)
{
	global $ilance;
	if ($html)
	{
		print_progress_begin('<b>Rebuilding spatial data for category table</b>, please wait.', '.', 'progressspan341');
	}
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "categories SET `sets` = LineString(Point(-1, lft), Point(1, rgt));
	");
	$ilance->db->query("
		ALTER TABLE " . DB_PREFIX . "categories MODIFY `sets` LINESTRING NOT NULL
	");
	$ilance->db->query("
		CREATE SPATIAL INDEX sx_categories_sets ON " . DB_PREFIX . "categories (sets)
	");
	if ($html)
	{
		print_progress_end();
	}
}

/**
* Function to print the installation menu header
*/
function print_install_header()
{
	$html = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html dir="ltr" lang="us">
<head>
<title>Installation System</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="-1">
<meta http-equiv="Cache-Control" content="no-cache">
<meta name="robot" content="noindex, nofollow">
<style id="css" type="text/css">
<!--
body
{
	font-family: Arial, Helvetica, Verdana, sans-serif;
	font-size: 9pt;
	background-color: #fff;
	margin: 25px 25px 25px 25px;
	padding: 0px 25px 0px 25px;
}
table, td, th, p, li
{
	font-family: Arial, Helvetica, Verdana, sans-serif;
	font-size: 9pt;
	color: #000000;
}
p, div
{
	font-family: Arial, Helvetica, Verdana, sans-serif;
	font-size: 9pt;
	color: #000000;
}
.page
{
	background-color: #fff;
	color: #000000;
}
a:link
{
	color: #113456;
}
a:visited
{
	color: #113456;
}
a:hover
{
	color: #C00;
}
a:active
{
	color: #C00;
}
.highlight
{
	background: #6D8CB3;
	color: #FFFFFF;
}
.fieldset legend
{
	padding: 1px;
	font-size: 9pt;
	font-weight: bold;
	color: #000000;
}
.smaller
{
	font-family: Arial, Helvetica, Verdana, sans-serif;
	font-size: 11px;
	color: #000000;
}
div.bluehlite
{
	padding-right: 4px;
	border-top: #5a7edc 1px solid;
	padding-left: 4px;
	padding-bottom: 8px;
	margin: 5px auto;
	padding-top: 8px;
	background-color: #fcfdff;
}
div.greenhlite
{
	padding-right: 4px;
	border-top: #83DB5A 1px solid;
	padding-left: 4px;
	padding-bottom: 8px;
	margin: 5px auto;
	padding-top: 8px;
	background-color: #fcfffa;
}
div.yellowhlite
{
	padding-right: 4px;
	border-top: #D9CE5B 1px solid;
	padding-left: 4px;
	padding-bottom: 8px;
	margin: 5px auto;
	padding-top: 8px;
	background-color: #fffefa;
}
div.redhlite
{
	padding-right: 4px;
	border-top: #d95b5b 1px solid;
	padding-left: 4px;
	padding-bottom: 8px;
	margin: 5px auto;
	padding-top: 8px;
	background-color: #fffafa;
}
div.purplehlite
{
	padding-right: 4px;
	border-top: #d95bb7 1px solid;
	padding-left: 4px;
	padding-bottom: 8px;
	margin: 5px auto;
	padding-top: 8px;
	background-color: #fff7fd;
}
div.smaller
{
	font-family: Arial, Helvetica, Verdana, sans-serif;
	font-size: 11px;
	color: #000000;
}
.buttons
{
	font-size: 12px;
	color: #333333;
	font-family: Arial, Helvetica, Verdana, sans-serif;
	font-weight: bold;
}
.buttons_smaller
{
	font-size: 10px;
	color : #333333;
	font-family: Arial, Helvetica, Verdana, sans-serif;
	font-weight: bold;
}
.input
{
   font: 10pt Verdana, Arial, Helvetica, sans-serif;
}
.textarea
{
	border: 1px inset;
	padding-left: 6px;
	font-size: 12px;
	font-weight: bold;
	width: 191px;
	color: #444444;
	padding-top: 4px;
	font-family: Arial, Helvetica, Verdana, sans-serif;
	height: 77px;
	background-color: #ffffff;
}
.pulldown
{
	font-size: 13px;
	width: 198px;
	color: #444444;
	font-family: Arial, Helvetica, Verdana, sans-serif;
	height: 24px;
}
.block-wrapper
{
	margin-top:14px;
}
.block3 .block3-content
{
	position: relative;
	background-color: #fff;
	border: solid #ccc;
	border-width: 0px 1px 0px 1px;
	padding: 10px 10px 10px 10px;
	background-color: #fff;
}
.block3 .block3-content-gray
{
	position: relative;
	background-color: #fff;
	border: solid #ccc;
	border-width: 0px 1px 1px 1px;
	padding: 10px 10px 10px 10px;
	background-color: #fff;
	padding: 6px;
	margin: 0px;
	background-color: #ededed;
}
.block3 .block3-content-gray-top
{
	position: relative;
	background-color: #fff;
	border: solid #ccc;
	border-width: 0px 1px 0px 1px;
	padding: 10px 10px 10px 10px;
	background-color: #fff;
	padding: 6px;
	margin: 0px;
	background-color: #ededed;
	border-top: 1px;
	border-bottom: 1px;
	border-top-color: #cccccc;
	border-bottom-color: #cccccc;
}
.block3 .block3-footer
{
	position: relative;
	background-color: #fff;
	border: 1px solid #ccc;
	border-top: 0px;
}
.block3 .block3-footer .block3-left
{
	position: relative;
	left: -2px;
	height: 6px;
	font-size: 0px;
	background: url(../images/default/blocks/block3_lower_left.gif) no-repeat bottom left;
}
.block3 .block3-footer .block3-right
{
	position: relative;
	bottom: -1px;
	right: -1px;
	background: url(../images/default/blocks/block3_lower_right.gif) no-repeat bottom right;
}
.block3 .block3-header
{
	position: relative;
	padding: 0 6px 6px 8px;
	color: #5d5d5d;
	font-family: Arial, Helvetica, sans-serif;
	font-weight: bold;
	font-size: medium;
	background: #FCFCFC url(../images/default/blocks/block3_header.gif) repeat-x bottom;
	border-style: solid;
	border-color: #cccccc #cccccc #cccccc #cccccc;
	border-width: 0px 1px 1px 1px;
}
.block3 .block3-top
{
	position: relative; 
	background-color: #F3F3F3; 
	border: 1px solid #cccccc; 
	border-bottom: 0px;
}
.block3 .block3-top .block3-left
{
	position: relative;
	left: -2px;
	height: 6px;
	font-size: 0px;
	background: url(../images/default/blocks/block3_upper_left.gif) no-repeat top left;
}
.block3 .block3-top .block3-right
{
	position: relative; 
	top: -1px; 
	right: -1px; 
	background: url(../images/default/blocks/block3_upper_right.gif) no-repeat top right;
}
//-->
</style>
</head>
<body>

<!-- content table -->
<div align="center">
<div class="page" style="width:790px; text-align:left">
<div style="padding:0px 25px 0px 25px"><img src="../images/default/v4/logo.png" border="0" alt="" />

<div class="block-wrapper">
	<div class="block3">
			<div class="block3-top">
					<div class="block3-right">
							<div class="block3-left"></div>
					</div>
			</div>
			<div class="block3-header">ILance ' . VERSION . ' Installation System</div>
			<div class="block3-content" style="padding:14px">';
	
	return $html;
}

/**
* Function to print the installation menu footer
*/
function print_install_footer()
{
	$html = '</div>			
	<div class="block3-footer">
		<div class="block3-right">
			<div class="block3-left"></div>
		</div>
	</div>			
	</div>
	</div>
	</div>
	</div>
	</div>
	<!-- / content area table -->
	<br />
	<div align="center">
	    <div class="smaller" align="center">
	    <!-- Do not remove copyright notice without branding removal -->
	    Powered by: ILance&reg; Version ' . VERSION . '<br />
	    Copyright &copy;2002 - ' . date('Y') . ', ILance Inc.
	    <!-- Do not remove copyright notice without branding removal -->
	     <br />
	    </div>
	    <br />
	</div>
	</body>
	</html>';
	return $html;
}

/**
* Function to construct our encryption system keys during installation
*
* @param	integer		length of key
*/
function createkey($length = 50)
{
	$alpha = '-ABCDEFGHIJ=KLMNOP-QRSTUVWXYZ-abcdefgh-ijklmnopq-rstuvwxy-z1234567890';
	$ran_string = '';
	for ($i = 0; $i < $length; $i++)
	{
  		$ran_string .= $alpha[rand(0,61)];
	}
	return $ran_string;
}

/**
* Function to create the latest fresh database schema for the version of ILance being installed.
* This function will attempt to DROP all tables before creating new ones.  This function should only
* be used during the process of a fresh installation.
*/
function create_db_schema()
{
	global $ilance;
	$ilance->db->query("ALTER DATABASE `" . DB_DATABASE . "` DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "");
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "abuse_reports");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "abuse_reports (
		`abuseid` INT(5) NOT NULL AUTO_INCREMENT,
		`regarding` MEDIUMTEXT,
		`username` MEDIUMTEXT,
		`email` MEDIUMTEXT,
		`itemid` INT(5) NOT NULL DEFAULT '0',
		`abusetype` ENUM('listing','bid','portfolio','profile','feedback','pmb') NOT NULL default 'listing',
		`type` VARCHAR(100) NOT NULL default '',
		`status` INT(1) NOT NULL DEFAULT '1',
		`dateadded` DATETIME NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY (`abuseid`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "abuse_reports</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "admincp_news");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "admincp_news (
		`newsid` INT(5) NOT NULL AUTO_INCREMENT,
		`subject` VARCHAR(250) NOT NULL DEFAULT '',
		`content` MEDIUMTEXT,
		`datetime` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`visible` INT(1) NOT NULL default '1',
		PRIMARY KEY (`newsid`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "admincp_news</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "attachment");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "attachment (
		`attachid` INT(100) UNSIGNED NOT NULL AUTO_INCREMENT,
		`attachtype` ENUM('profile','portfolio','project','itemphoto','bid','pmb','ws','kb','ads','digital','slideshow','stores','storesitemphoto','storesdigital','storesbackground','bb') NOT NULL default 'profile',
		`user_id` INT(10) UNSIGNED NOT NULL default '0',
		`portfolio_id` INT(100) NOT NULL default '0',
		`project_id` INT(100) NOT NULL default '0',
		`pmb_id` INT(100) NOT NULL default '0',
		`bulk_id` INT(100) NOT NULL default '0',
		`category_id` INT(20) NOT NULL default '0',
		`date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`filename` VARCHAR(100) NOT NULL default '',
		`filedata` LONGBLOB,
		`filedata_original` LONGBLOB,
		`filedata_full` LONGBLOB,
		`filedata_mini` LONGBLOB,
		`filedata_search` LONGBLOB,
		`filedata_gallery` LONGBLOB,
		`filedata_snapshot` LONGBLOB,
		`filetype` VARCHAR(50) NOT NULL default '',
		`filetype_original` VARCHAR(50) NOT NULL default '',
		`width` INT(5) NOT NULL default '0',
		`width_original` INT(5) NOT NULL default '0',
		`width_full` INT(5) NOT NULL default '0',
		`width_mini` INT(5) NOT NULL default '0',
		`width_search` INT(5) NOT NULL default '0',
		`width_gallery` INT(5) NOT NULL default '0',
		`width_snapshot` INT(5) NOT NULL default '0',
		`height` INT(5) NOT NULL default '0',
		`height_original` INT(5) NOT NULL default '0',
		`height_full` INT(5) NOT NULL default '0',
		`height_mini` INT(5) NOT NULL default '0',
		`height_search` INT(5) NOT NULL default '0',
		`height_gallery` INT(5) NOT NULL default '0',
		`height_snapshot` INT(5) NOT NULL default '0',
		`visible` INT(1) UNSIGNED NOT NULL default '0',
		`counter` SMALLINT(5) UNSIGNED NOT NULL default '0',
		`filesize` INT(10) UNSIGNED NOT NULL default '0',
		`filesize_original` INT(10) UNSIGNED NOT NULL default '0',
		`filesize_full` INT(10) UNSIGNED NOT NULL default '0',
		`filesize_mini` INT(10) UNSIGNED NOT NULL default '0',
		`filesize_search` INT(10) UNSIGNED NOT NULL default '0',
		`filesize_gallery` INT(10) UNSIGNED NOT NULL default '0',
		`filesize_snapshot` INT(10) UNSIGNED NOT NULL default '0',
		`filehash` VARCHAR(32) NOT NULL default '',
		`ipaddress` VARCHAR(50) NOT NULL default '',
		`tblfolder_ref` INT(100) NOT NULL default '0',
		`exifdata` MEDIUMTEXT,
		`invoiceid` INT(10) NOT NULL default '0',
		`isexternal` INT(1) NOT NULL default '0',
		`color` INT(1) NOT NULL default '0',
		`watermarked` INT(1) NOT NULL default '0',
		PRIMARY KEY  (`attachid`),
		KEY filehash (`filehash`),
		INDEX (`user_id`),
		INDEX (`portfolio_id`),
		INDEX (`project_id`),
		INDEX (`category_id`),
		INDEX (`color`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "attachment</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "attachment_color");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "attachment_color (
		`colorid` INT(10) NOT NULL AUTO_INCREMENT,
		`attachid` INT(7) NOT NULL default '0',
		`project_id` INT(10) NOT NULL default '0',
		`color` VARCHAR(7) NOT NULL default '',
		`count` INT(5) NOT NULL default '0',
		`relativecolor` VARCHAR(7) NOT NULL default '',
		`relativetitle` VARCHAR(100) NOT NULL default '',
		`relativefont` VARCHAR(100) NOT NULL default '',
		PRIMARY KEY  (`colorid`),
		INDEX (`attachid`),
		INDEX (`project_id`),
		INDEX (`relativefont`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "attachment_color</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "attachment_folder");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "attachment_folder (
		`id` INT(10) NOT NULL AUTO_INCREMENT,
		`name` VARCHAR(255) default NULL,
		`comments` MEDIUMTEXT,
		`p_id` INT(100) default NULL,
		`project_id` INT(10) default NULL,
		`user_id` INT(10) NOT NULL DEFAULT '0',
		`buyer_id` INT(10) default NULL,
		`seller_id` INT(10) default NULL,
		`folder_size` INT(10) default NULL,
		`folder_type` INT(10) default NULL,
		`create_date` DATE default NULL,
		PRIMARY KEY  (`id`),
		INDEX (`name`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "attachment_folder</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "audit");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "audit (
		`logid` INT(10) NOT NULL AUTO_INCREMENT,
		`user_id` INT(10) NOT NULL default '0',
		`script` VARCHAR(200) NOT NULL default '',
		`cmd` VARCHAR(250) NOT NULL default '',
		`subcmd` VARCHAR(250) NOT NULL default '',
		`do` VARCHAR(250) NOT NULL default '',
		`action` VARCHAR(250) NOT NULL default '',
		`otherinfo` MEDIUMTEXT,
		`datetime` INT(11) NOT NULL default '0',
		`ipaddress` VARCHAR(50) NOT NULL default '',
		PRIMARY KEY  (`logid`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "audit</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "bankaccounts");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "bankaccounts (
		`bank_id` INT(100) NOT NULL AUTO_INCREMENT,
		`user_id` INT(100) NOT NULL default '0',
		`beneficiary_account_name` VARCHAR(100) NOT NULL default '',
		`destination_currency_id` INT(100) NOT NULL default '0',
		`beneficiary_bank_name` VARCHAR(100) NOT NULL default '',
		`beneficiary_account_number` VARCHAR(100) NOT NULL default '',
		`beneficiary_bank_routing_number_swift` VARCHAR(100) NOT NULL default '',
		`bank_account_type` VARCHAR(100) NOT NULL default '',
		`beneficiary_bank_address_1` VARCHAR(200) NOT NULL default '',
		`beneficiary_bank_address_2` VARCHAR(200) default NULL,
		`beneficiary_bank_city` VARCHAR(100) NOT NULL default '',
		`beneficiary_bank_state` VARCHAR(100) NOT NULL default '',
		`beneficiary_bank_zipcode` VARCHAR(25) NOT NULL default '',
		`beneficiary_bank_country_id` INT(100) NOT NULL default '0',
		`wire_bin_type` ENUM('SWIFT','BLZ','ABA/ROUTING NUMBER','OTHER') default 'SWIFT',
		PRIMARY KEY  (`bank_id`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "bankaccounts</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "bid_fields");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "bid_fields (
		`fieldid` INT(10) NOT NULL AUTO_INCREMENT,
		`question_eng` MEDIUMTEXT NOT NULL,
		`description_eng` MEDIUMTEXT NOT NULL,
		`inputtype` ENUM('yesno','int','textarea','text','pulldown','multiplechoice','date') NOT NULL default 'text',
		`multiplechoice` MEDIUMTEXT,
		`sort` INT(3) NOT NULL default '0',
		`visible` INT(1) NOT NULL default '1',
		`required` INT(1) NOT NULL default '0',
		`canremove` INT(1) NOT NULL default '1',
		PRIMARY KEY  (`fieldid`),
		INDEX (`inputtype`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "bid_fields</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "bid_fields_answers");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "bid_fields_answers (
		`answerid` INT(10) NOT NULL AUTO_INCREMENT,
		`fieldid` INT(10) NOT NULL default '0',
		`project_id` INT(10) NOT NULL default '0',
		`bid_id` INT(10) NOT NULL default '0',
		`answer` MEDIUMTEXT,
		`date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`visible` INT(1) NOT NULL default '1',
		PRIMARY KEY  (`answerid`),
		INDEX (`fieldid`),
		INDEX (`project_id`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "bid_fields_answers</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "budget");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "budget (
		`budgetid` INT(5) NOT NULL AUTO_INCREMENT,
		`budgetgroup` VARCHAR(250) NOT NULL default '',
		`title` VARCHAR(200) NOT NULL default '',
		`fieldname` VARCHAR(50) NOT NULL default '',
		`budgetfrom` DECIMAL(17,2) NOT NULL default '0',
		`budgetto` DECIMAL(17,2) NOT NULL default '0',
		`insertiongroup` VARCHAR(250) NOT NULL default '',
		`sort` INT(5) NOT NULL default '0',
		PRIMARY KEY  (`budgetid`),
		INDEX (`budgetgroup`),
		INDEX (`title`),
		INDEX (`fieldname`),
		INDEX (`insertiongroup`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	echo "<li>" . DB_PREFIX . "budget</li>";
	flush();
	
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "budget
		(`budgetid`, `budgetgroup`, `title`, `fieldname`, `budgetfrom`, `budgetto`, `sort`)
		VALUES
		(1, 'default', 'Large Project', 'large', 1000.00, 100000.00, 10),
		(2, 'default', 'Medium Project', 'medium', 500.00, 1000.00, 20),
		(3, 'default', 'Small Project', 'small', 100.00, 500.00, 30),
		(4, 'default', 'Minor Task', 'minor', 1.00, 100.00, 40)
	");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Imported default budgets ranges</strong></li></ul>";
	flush();
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "budget_groups");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "budget_groups (
		`groupid` INT(5) NOT NULL AUTO_INCREMENT,
		`groupname` VARCHAR(50) NOT NULL default 'default',
		`description` MEDIUMTEXT,
		PRIMARY KEY  (`groupid`),
		INDEX ( `groupname` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	echo "<li>" . DB_PREFIX . "budget_groups</li>";
	flush();
	
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "budget_groups
		(`groupid`, `groupname`, `description`)
		VALUES
		(1, 'default', 'Default service budget group that holds a list of pulldown budget options')
	");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Imported default budget groups</strong></li></ul>";
	flush();
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "buynow_orders");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "buynow_orders (
		`orderid` INT(10) NOT NULL AUTO_INCREMENT,
		`parentid` INT(5) NOT NULL default '0',
		`project_id` INT(10) NOT NULL default '0',
		`buyer_id` INT(10) NOT NULL default '0',
		`owner_id` INT(10) NOT NULL default '0',
		`bid_id` INT(5) NOT NULL default '0',
		`invoiceid` INT(10) NOT NULL default '0',
		`attachid` INT(10) NOT NULL default '0',
		`qty` INT(5) NOT NULL default '1',
		`amount` DOUBLE(17,2) NOT NULL default '0.00',
		`originalcurrencyid` INT(5) NOT NULL default '0',
		`originalcurrencyidrate` VARCHAR(10) NOT NULL default '0',
		`convertedtocurrencyid` INT(5) NOT NULL default '0',
		`convertedtocurrencyidrate` VARCHAR(10) NOT NULL default '0',
		`salestax` DOUBLE(17,2) NOT NULL default '0.00',
		`salestaxstate` VARCHAR(250) NOT NULL default '',
		`salestaxrate` VARCHAR(10) NOT NULL default '0',
		`salestaxshipping` INT(1) NOT NULL default '0',
		`escrowfee` DOUBLE(17,2) NOT NULL default '0.00',
		`escrowfeebuyer` DOUBLE(17,2) NOT NULL default '0.00',
		`fvf` DOUBLE(17,2) NOT NULL default '0.00',
		`fvfbuyer` DOUBLE(17,2) NOT NULL default '0.00',
		`isescrowfeepaid` INT(1) NOT NULL default '0',
		`isescrowfeebuyerpaid` INT(1) NOT NULL default '0',
		`isfvfpaid` INT(1) NOT NULL default '0',
		`isfvfbuyerpaid` INT(1) NOT NULL default '0',
		`escrowfeeinvoiceid` INT(10) NOT NULL default '0',
		`escrowfeebuyerinvoiceid` INT(10) NOT NULL default '0',
		`fvfinvoiceid` INT(10) NOT NULL default '0',
		`fvfbuyerinvoiceid` INT(10) NOT NULL default '0',
		`ship_required` INT(1) NOT NULL default '1',
		`ship_location` MEDIUMTEXT,
		`orderdate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`canceldate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`arrivedate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`paiddate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`releasedate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`returndate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`returnedondate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`winnermarkedaspaid` INT(1) NOT NULL default '0',
		`winnermarkedaspaiddate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`winnermarkedaspaidmethod` MEDIUMTEXT,
		`buyerpaymethod` VARCHAR(250) NOT NULL default '',
		`buyershipcost` DOUBLE(17,2) NOT NULL default '0.00',
		`buyershipperid` INT(5) NOT NULL default '0',
		`sellermarkedasshipped` INT(1) NOT NULL default '0',
		`sellermarkedasshippeddate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`shiptracknumber` VARCHAR(250) NOT NULL default '',
		`buyerfeedback` INT(1) NOT NULL default '0',
		`sellerfeedback` INT(1) NOT NULL default '0',
		`status` ENUM('paid','cancelled','pending_delivery','delivered','fraud','offline','offline_delivered') NOT NULL default 'paid',
		PRIMARY KEY (`orderid`),
		INDEX (`parentid`),
		INDEX (`project_id`),
		INDEX (`buyer_id`),
		INDEX (`owner_id`),
		INDEX (`bid_id`),
		INDEX (`attachid`),
		INDEX (`invoiceid`),
		INDEX (`status`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "buynow_orders</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "bulk_sessions");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "bulk_sessions (
		`id` INT(10) NOT NULL AUTO_INCREMENT,
		`user_id` INT(4) NOT NULL default '0',
		`dateupload` datetime,
		`items` INT(5) NOT NULL default '0',
		`itemsuploaded` INT(5) default '0',
		PRIMARY KEY (`id`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "bulk_sessions</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "bulk_tmp");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "bulk_tmp (
		`id` INT(10) NOT NULL AUTO_INCREMENT,
		`project_title` MEDIUMTEXT NOT NULL,
		`description` MEDIUMTEXT NOT NULL,
		`startprice` MEDIUMTEXT NOT NULL,
		`buynow_price` MEDIUMTEXT NOT NULL,
		`reserve_price` MEDIUMTEXT NOT NULL,
		`buynow_qty` MEDIUMTEXT NOT NULL,
		`buynow_qty_lot` MEDIUMTEXT NOT NULL,
		`project_details` MEDIUMTEXT NOT NULL,
		`filtered_auctiontype` MEDIUMTEXT NOT NULL,
		`cid` MEDIUMTEXT NOT NULL,
		`sample` MEDIUMTEXT NOT NULL,
		`currency` MEDIUMTEXT NOT NULL,
		`city` MEDIUMTEXT,
		`state` MEDIUMTEXT,
		`zipcode` MEDIUMTEXT,
		`country` MEDIUMTEXT,
		`attributes` MEDIUMTEXT,
		`sku` MEDIUMTEXT,
		`upc` MEDIUMTEXT,
		`partnumber` MEDIUMTEXT,
		`modelnumber` MEDIUMTEXT,
		`ean` MEDIUMTEXT,
		`keywords` MEDIUMTEXT,
		`project_type` MEDIUMTEXT,
		`project_state` MEDIUMTEXT,
		`dateupload` DATE NOT NULL default '0000-00-00',
		`correct` INT(2) NOT NULL default '0',
		`user_id` INT(4) NOT NULL default '0',
		`rfpid` INT(15) NOT NULL default '0',
		`sample_uploaded` INT(2) NOT NULL default '0',
		`bulk_id` INT(10) NOT NULL,
		PRIMARY KEY (`id`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "bulk_tmp</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "calendar");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "calendar (
		`calendarid` INT(5) NOT NULL AUTO_INCREMENT,
		`userid` INT(5) NOT NULL default '0',
		`dateline` date NOT NULL,
		`comment` MEDIUMTEXT,
		`visible` INT(1) NOT NULL default '1',
		PRIMARY KEY  (`calendarid`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "calendar</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "categories");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "categories (
		`cid` INT(100) NOT NULL AUTO_INCREMENT,
		`parentid` INT(100) NOT NULL default '0',
		`sets` LINESTRING NOT NULL,
		`level` INT(5) NOT NULL default '1',
		`seourl_eng` MEDIUMTEXT,
		`title_eng` MEDIUMTEXT,
		`description_eng` MEDIUMTEXT,
		`canpost` INT(1) NOT NULL default '1',
		`canpostclassifieds` INT(1) NOT NULL default '1',
		`lastpost` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`views` INT(100) NOT NULL default '0',
		`xml` INT(1) NOT NULL default '0',
		`portfolio` INT(1) NOT NULL default '0',
		`newsletter` INT(1) NOT NULL default '0',
		`auctioncount` INT(10) NOT NULL default '0',
		`budgetgroup` VARCHAR(250) NOT NULL default '',
		`insertiongroup` VARCHAR(250) NOT NULL default '',
		`finalvaluegroup` VARCHAR(250) NOT NULL default '',
		`incrementgroup` VARCHAR(250) NOT NULL default '',
		`cattype` ENUM('service','product') NOT NULL default 'service',
		`bidamounttypes` MEDIUMTEXT,
		`usefixedfees` INT(1) NOT NULL default '0',
		`fixedfeeamount` DOUBLE(17,2) NOT NULL default '0.00',
		`nondisclosefeeamount` DOUBLE(17,2) NOT NULL default '0.00',
		`multipleaward` INT(1) NOT NULL default '0',
		`bidgrouping` INT(1) NOT NULL default '0',
		`bidgroupdisplay` ENUM('lowest','highest') NOT NULL default 'lowest',
		`hidebuynow` INT(1) NULL default '0',
		`useproxybid` INT(1) NOT NULL default '0',
		`usereserveprice` INT(1) NOT NULL default '1',
		`useantisnipe` INT(1) NOT NULL default '0',
		`bidfields` MEDIUMTEXT,
		`catimage` VARCHAR(250) NOT NULL default '',
		`catimagehero` VARCHAR(250) NOT NULL default '',
		`catimageherourl` MEDIUMTEXT,
		`keywords_eng` MEDIUMTEXT,
		`durationdays` MEDIUMTEXT,
		`durationhours` MEDIUMTEXT,
		`durationminutes` MEDIUMTEXT,
		`visible` INT(1) NOT NULL default '1',
		`sort` INT(3) NOT NULL default '0',
		`lft` INT(10) NOT NULL,
		`rgt` INT(10) NOT NULL,
		PRIMARY KEY (`cid`),
		INDEX (`parentid`),
		INDEX (`level`),
		INDEX (`cattype`),
		INDEX (`canpost`),
		INDEX (`canpostclassifieds`),
		INDEX (`bidgroupdisplay`),
		INDEX (`budgetgroup`),
		INDEX (`insertiongroup`),
		INDEX (`finalvaluegroup`),
		INDEX (`incrementgroup`),
		INDEX (`lft`),
		INDEX (`rgt`),
		INDEX (`visible`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "categories</li>";
	import_master_categories();
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Imported default categories</strong></li></ul>";
	flush();
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "charities");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "charities (
		`charityid` INT(5) NOT NULL AUTO_INCREMENT,
		`groupid` INT(10) NOT NULL default '0',
		`title` MEDIUMTEXT NOT NULL,
		`description` MEDIUMTEXT NOT NULL,
		`url` MEDIUMTEXT NOT NULL,
		`donations` INT(5) NOT NULL default '0',
		`earnings` DOUBLE(17,2) NOT NULL default '0.00',
		`visible` INT(1) NOT NULL default '1',
		PRIMARY KEY (`charityid`),
		INDEX ( `groupid` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "charities</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "cms");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "cms (
		`terms` LONGTEXT NOT NULL default '',
		`privacy` LONGTEXT NOT NULL default '',
		`about` LONGTEXT NOT NULL default '',
		`registrationterms` LONGTEXT NOT NULL default '',
		`news` LONGTEXT NOT NULL default ''
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "cms VALUES ('Marketplace terms coming soon.', 'Marketplace privacy coming soon.', 'About us coming soon.', 'Registration terms coming soon.', 'Marketplace news coming soon.')");
	flush();
	echo "<li>" . DB_PREFIX . "cms</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "configuration");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "configuration (
		`name` VARCHAR(250) NOT NULL default '',
		`value` MEDIUMTEXT,
		`configgroup` VARCHAR(250) NOT NULL default '',
		`inputtype` ENUM('yesno','int','textarea','text','pass','pulldown') NOT NULL default 'yesno',
		`inputcode` MEDIUMTEXT,
		`inputname` VARCHAR(250) NOT NULL default '',
		`sort` INT(5) NOT NULL default '0',
		`visible` INT(1) NOT NULL default '1',
		`type` ENUM('global','service','product') NOT NULL DEFAULT 'global',
		PRIMARY KEY  (`name`),
		INDEX ( `configgroup` ),
		INDEX ( `inputtype` ),
		INDEX ( `inputname` ),
		INDEX ( `type` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "configuration</li>";

	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('maintenance_mode', '0', 'maintenance', 'yesno', '', '', '1', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('maintenance_excludeips', '111.111.111.111, 222.222.222.*', 'maintenance', 'textarea', '', '', '2', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('maintenance_excludeurls', 'redirect" . ILMIME . "', 'maintenance', 'textarea', '', '', '3', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('maintenance_message', '_marketplace_currently_in_maintenance_mode', 'maintenance', 'text', '', '', '4', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_highlightactive', '1', 'serviceupsell_highlight', 'yesno', '', '', '1', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_highlightfees', '1', 'serviceupsell_highlight', 'yesno', '', '', '1', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_highlightcolor', 'featured_highlight', 'serviceupsell_highlight', 'text', '', '', '2', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_highlightfee', '3.00', 'serviceupsell_highlight', 'int', '', '', '3', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_autorelistactive', '1', 'serviceupsell_autorelist', 'yesno', '', '', '1', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_autorelistfees', '1', 'serviceupsell_autorelist', 'yesno', '', '', '1', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_autorelistfee', '3.75', 'serviceupsell_autorelist', 'int', '', '', '2', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_autorelistmaxdays', '7', 'serviceupsell_autorelist', 'int', '', '', '2', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_featured_searchresultsactive', '1', 'serviceupsell_featured_searchresults', 'yesno', '', '', '1', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_featured_searchresultsfees', '1', 'serviceupsell_featured_searchresults', 'yesno', '', '', '2', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_featured_searchresultsfee', '2.75', 'serviceupsell_featured_searchresults', 'int', '', '', '3', '1', 'service')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_boldactive', '1', 'productupsell_bold', 'yesno', '', '', '1', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_boldfees', '1', 'productupsell_bold', 'yesno', '', '', '1', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_boldfee', '3.75', 'productupsell_bold', 'int', '', '', '2', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_featuredactive', '1', 'productupsell_featured', 'yesno', '', '', '1', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_featuredfees', '1', 'productupsell_featured', 'yesno', '', '', '2', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_featuredfee', '2.75', 'productupsell_featured', 'int', '', '', '3', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_highlightactive', '1', 'productupsell_highlight', 'yesno', '', '', '1', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_highlightcolor', 'featured_highlight', 'productupsell_highlight', 'text', '', '', '4', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_highlightfee', '3.00', 'productupsell_highlight', 'int', '', '', '3', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_highlightfees', '1', 'productupsell_highlight', 'yesno', '', '', '2', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_autorelistactive', '1', 'productupsell_autorelist', 'yesno', '', '', '1', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_autorelistfees', '1', 'productupsell_autorelist', 'yesno', '', '', '1', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_autorelistfee', '3.75', 'productupsell_autorelist', 'int', '', '', '2', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_autorelistmaxdays', '7', 'productupsell_autorelist', 'int', '', '', '2', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_featured_searchresultsactive', '1', 'productupsell_featured_searchresults', 'yesno', '', '', '1', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_featured_searchresultsfees', '1', 'productupsell_featured_searchresults', 'yesno', '', '', '2', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_featured_searchresultsfee', '2.75', 'productupsell_featured_searchresults', 'int', '', '', '3', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productaward_pmbafterend', '1', 'productaward_pmb', 'yesno', '', '', '1', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productaward_mediashareafterend', '1', 'productaward_mediashare', 'yesno', '', '', '2', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('portfolioupsell_featuredactive', '1', 'portfolioupsell', 'yesno', '', '', '1', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('portfolioupsell_featuredfee', '5.00', 'portfolioupsell', 'int', '', '', '2', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('portfolioupsell_featureditemname', 'Featured Portfolio Status', 'portfolioupsell', 'text', '', '', '3', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('portfoliodisplay_thumbsperpage', '10', 'portfoliodisplay', 'int', '', '', '6', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('portfoliodisplay_imagetypes', '.gif, .jpg, .png, .jpeg', 'portfoliodisplay', 'textarea', '', '', '3', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationdisplay_turingimage', '1', 'registrationdisplay', 'yesno', '', '', '4', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationupsell_bonusactive', '0', 'registrationupsell', 'yesno', '', '', '1', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationdisplay_phoneformat', 'US', 'registrationdisplay', 'int', '', '', '1', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationdisplay_quickregistration', '0', 'registrationdisplay', 'yesno', '', '', '100', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationupsell_amount', '5.00', 'registrationupsell', 'int', '', '', '2', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationupsell_bonusitemname', 'New Account Registration Bonus', 'registrationupsell', 'text', '', '', '4', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('genderactive', '0', 'registrationdisplay', 'yesno', '', '', '5', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('referalsystem_active', '0', 'referalsystem', 'yesno', '', '', '1', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('referalsystem_payout', '5.00', 'referalsystem', 'int', '', '', '2', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachment_dbstorage', '0', 'attachmentsystem', 'yesno', '', '', '10', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('watermark', '0', 'attachmentsystem', 'yesno', '', '', '20', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('watermark_text', '', 'attachmentsystem', 'text', '', '', '30', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('watermark_image', '', 'attachmentsystem', 'text', '', '', '40', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('watermark_textsize', '10', 'attachmentsystem', 'int', '', '', '50', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('watermark_imageopacity', '80', 'attachmentsystem', 'int', '', '', '60', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('watermark_textfont', 'in901xki.ttf', 'attachmentsystem', 'text', '', '', '70', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('watermark_quality', '100', 'attachmentsystem', 'int', '', '', '72', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('watermark_angle', '0', 'attachmentsystem', 'int', '', '', '74', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('watermark_position', 'TOPLEFT', 'attachmentsystem', 'text', '', '', '76', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('watermark_padding', '5', 'attachmentsystem', 'int', '', '', '78', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('watermark_profiles', '0', 'attachmentsystem', 'yesno', '', '', '80', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('watermark_portfolios', '0', 'attachmentsystem', 'yesno', '', '', '90', '1', 'service')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('watermark_itemphoto', '0', 'attachmentsystem', 'yesno', '', '', '100', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('watermark_storesitemphoto', '0', 'attachmentsystem', 'yesno', '', '', '110', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachment_moderationdisabled', '1', 'attachmentmoderation', 'yesno', '', '', '1', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachment_mediasharemoderationdisabled', '1', 'attachmentmoderation', 'yesno', '', '', '2', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_profileextensions', '.gif, .jpg, .png', 'attachmentlimit_profileextensions', 'textarea', '', '', '1', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_defaultextensions', '.psd, .doc, .txt, .pdf, .jpg, .gif, .png, .bmp, .zip, .gz, .tar, .rar, .csv, .xls', 'attachmentlimit_defaultextensions', 'textarea', '', '', '1', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_portfolioextensions', '.psd, .doc, .txt, .pdf, .jpg, .gif, .png, .bmp, .zip, .csv, .xls', 'attachmentlimit_portfolioextensions', 'textarea', '', '', '1', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_profilemaxwidth', '108', 'attachmentlimit_profileextensions', 'int', '', '', '2', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_profilemaxheight', '108', 'attachmentlimit_profileextensions', 'int', '', '', '3', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_profilemaxsize', '2500000', 'attachmentlimit_profileextensions', 'int', '', '', '3', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_projectmaxwidth', '1024', 'attachmentlimit_defaultextensions', 'int', '', '', '2', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_projectmaxheight', '768', 'attachmentlimit_defaultextensions', 'int', '', '', '3', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_projectmaxsize', '2500000', 'attachmentlimit_defaultextensions', 'int', '', '', '3', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_portfoliomaxwidth', '1024', 'attachmentlimit_portfolioextensions', 'int', '', '', '2', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_portfoliomaxheight', '768', 'attachmentlimit_portfolioextensions', 'int', '', '', '3', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_portfoliomaxsize', '2500000', 'attachmentlimit_portfolioextensions', 'int', '', '', '4', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_portfoliothumbwidth', '100', 'attachmentlimit_portfolioextensions', 'int', '', '', '5', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_portfoliothumbheight', '100', 'attachmentlimit_portfolioextensions', 'int', '', '', '6', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_bidmaxwidth', '1024', 'attachmentlimit_bidsettings', 'int', '', '', '10', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_bidmaxheight', '768', 'attachmentlimit_bidsettings', 'int', '', '', '20', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_bidmaxsize', '2500000', 'attachmentlimit_bidsettings', 'int', '', '', '30', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_pmbmaxwidth', '1024', 'attachmentlimit_pmbsettings', 'int', '', '', '10', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_pmbmaxheight', '768', 'attachmentlimit_pmbsettings', 'int', '', '', '20', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_pmbmaxsize', '2500000', 'attachmentlimit_pmbsettings', 'int', '', '', '30', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_mediasharemaxwidth', '1024', 'attachmentlimit_workspacesettings', 'int', '', '', '10', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_mediasharemaxheight', '768', 'attachmentlimit_workspacesettings', 'int', '', '', '20', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_mediasharemaxsize', '2500000', 'attachmentlimit_workspacesettings', 'int', '', '', '30', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_searchresultsmaxwidth', '108', 'attachmentlimit_searchresultsettings', 'int', '', '', '10', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_searchresultsmaxheight', '108', 'attachmentlimit_searchresultsettings', 'int', '', '', '20', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_searchresultsgallerymaxwidth', '208', 'attachmentlimit_searchresultsettings', 'int', '', '', '30', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_searchresultsgallerymaxheight', '208', 'attachmentlimit_searchresultsettings', 'int', '', '', '40', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_searchresultssnapshotmaxwidth', '150', 'attachmentlimit_searchresultsettings', 'int', '', '', '50', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_searchresultssnapshotmaxheight', '150', 'attachmentlimit_searchresultsettings', 'int', '', '', '60', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_thumbnailmaxwidth', '62', 'attachmentlimit_defaultextensions', 'int', '', '', '18', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_thumbnailmaxheight', '62', 'attachmentlimit_defaultextensions', 'int', '', '', '19', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationdisplay_defaultcountry', 'Canada', 'registrationdisplay', 'pulldown', '', '', '2', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationdisplay_defaultstate', 'Ontario', 'registrationdisplay', 'pulldown', '', '', '3', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('escrowsystem_payercancancelfunds', '0', 'escrowsystem', 'yesno', '', '', '2', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('escrowsystem_payercancancelfundsafterrelease', '0', 'escrowsystem', 'yesno', '', '', '3', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_enablep2btransactionfees', '1', 'invoicesystem', 'yesno', '', '', '0', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_transactionidlength', '17', 'invoicesystem', 'int', '', '', '3', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('moderationsystem_disableauctionmoderation', '1', 'globalauctionsettings', 'yesno', '', '', '20', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalsecurity_emailonfailedlogins', '0', 'globalsecurity', 'yesno', '', '', '1', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalsecurity_numfailedloginattempts', '5', 'globalsecurity', 'int', '', '', '2', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalsecurity_extensionmime', '" . ILMIME . "', 'globalsecuritymime', 'int', '', '', '1', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_emailfilterpmb', '1', 'globalfilterspmb', 'yesno', '', '', '1', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_domainfilterpmb', '1', 'globalfilterspmb', 'yesno', '', '', '2', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_enablepmbspy', '1', 'globalfilterspmb', 'yesno', '', '', '3', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_emailfilterrfp', '1', 'globalfiltersrfp', 'yesno', '', '', '1', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_domainfilterrfp', '1', 'globalfiltersrfp', 'yesno', '', '', '2', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_enablerfpcancellation', '1', 'globalfiltersrfp', 'yesno', '', '', '3', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_emailfilterbid', '1', 'globalfiltersbid', 'yesno', '', '', '1', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_domainfilterbid', '1', 'globalfiltersbid', 'yesno', '', '', '1', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_emailfilterpsp', '1', 'globalfilterspsp', 'yesno', '', '', '1', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_domainfilterpsp', '1', 'globalfilterspsp', 'yesno', '', '', '2', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_vulgarpostfilter', '1', 'globalfiltersvulgar', 'yesno', '', '', '1', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_vulgarpostfilterlist', 'fuck, fucker, fucking, fucked, fuckhead, fuk, fuked, fukd, fuckface, shit, shithead, bitch, b!tch, asshole, cunt, whore, lush, faggot, fag, cock, cocksucker, dick, dickhead, dickface, nigger, arse, bastard, slut, dork, wanker, dumbass, arsehole, honkey, pigface', 'globalfiltersvulgar', 'textarea', '', '', '2', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_vulgarpostfilterreplace', '--**--', 'globalfiltersvulgar', 'textarea', '', '', '3', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_blockips', '', 'globalfiltersipblacklist', 'textarea', '', '', '1', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_enablecategorycount', '1', 'globalfilterresults', 'yesno', '', '', '3', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_maxrowsdisplay', '5', 'globalfilterresults', 'int', '', '', '6', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserver_enabledistanceradius', '0', 'globalserverdistanceapi', 'yesno', '', '', '1', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserverlocale_sitetimezone', 'America/Toronto', 'globalserverlocale', 'pulldown', '', 'timezones', '5', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserverlocale_defaultcurrency', '1', 'globalserverlocalecurrency', 'pulldown', '<select name=\'config[globalserverlocale_defaultcurrency]\' class=\'select\'><option value=\'1\' SELECTED>US DOLLAR (USD)</option><option value=\'3\'>AUSTRALIAN DOLLAR (AUD)</option><option value=\'6\'>BRITISH POUND (GBP)</option><option value=\'7\'>CANADIAN DOLLAR (CAD)</option><option value=\'9\'>CYPRUS POUND (CYP)</option><option value=\'11\'>DANISH KRONE (DKK)</option><option value=\'14\'>EURO (EUR)</option><option value=\'18\'>HONG KONG DOLLAR (HKD)</option><option value=\'22\'>JAPANESE YEN (JPY)</option><option value=\'24\'>MALTESE LIRA (MTL)</option><option value=\'27\'>NEW ZEALAND DOLLAR (NZD)</option><option value=\'28\'>NORWEGIAN KRONE (NOK)</option><option value=\'32\'>RAND (ZAR)</option><option value=\'37\'>SINGAPORE DOLLAR (SGD)</option><option value=\'39\'>SWEDISH KRONA (SEK)</option><option value=\'40\'>SWISS FRANC (CHF)</option><option value=\'41\'>TURKISH LIRA (TRL)</option></select>', 'currencyrates', 1, 1, 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserverlocale_defaultcurrencyxml', 'http://www.ecb.int/stats/eurofxref/eurofxref-daily.xml', 'globalserverlocalecurrency', 'text', '', 'currencyrates', '2', '1', '')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserverlocale_currencyselector', '0', 'globalserverlocalecurrency', 'yesno', '', 'currencyrates', '2', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversmtp_enabled', '0', 'globalserversmtp', 'yesno', '', '', '100', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversmtp_usetls', '0', 'globalserversmtp', 'yesno', '', '', '200', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversmtp_host', '', 'globalserversmtp', 'text', '', '', '300', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversmtp_port', '25', 'globalserversmtp', 'int', '', '', '400', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversmtp_user', '', 'globalserversmtp', 'text', '', '', '500', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversmtp_pass', '', 'globalserversmtp', 'pass', '', '', '600', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_companyname', '" . $ilance->db->escape_string($_SESSION['company_name']) . "', 'globalserversettings', 'text', '', '', '1', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_sitename', '" . $ilance->db->escape_string($_SESSION['site_name']) . "', 'globalserversettings', 'text', '', '', '2', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_siteaddress', '" . $ilance->db->escape_string($_SESSION['site_address']) . "', 'globalserversettings', 'textarea', '', '', '3', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_siteemail', '" . $ilance->db->escape_string($_SESSION['site_email']) . "', 'globalserversettings', 'text', '', '', '5', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_sitephone', '+1.111.111.1111', 'globalserversettings', 'text', '', '', '4', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserverlanguage_defaultlanguage', '1', 'language', 'int', '', '', '1', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_productauctionsenabled', '1', 'globalauctionsettings', 'yesno', '', '', '2', '0', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_serviceauctionsenabled', '0', 'globalauctionsettings', 'yesno', '', '', '1', '0', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_auctionstypeenabled', 'product', 'globalauctionsettings', 'pulldown', '', '', '1', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_pmbpopupwidth', '760', 'globalfilterspmb', 'int', '', '', '6', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_pmbpopupheight', '350', 'globalfilterspmb', 'int', '', '', '7', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_pmbwysiwyg', '1', 'globalfilterspmb', 'yesno', '', '', '4', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_pmbattachments', '1', 'globalfilterspmb', 'yesno', '', '', '5', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_enablebbcode', '1', 'globalfilterresults', 'yesno', '', '', '2', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_enablewysiwyg', '1', 'globalfilterresults', 'yesno', '', '', '4', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_changeauctiontitle', '0', 'globalfiltersrfp', 'yesno', '', '', '5', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_maxcharactersdescriptionbulk', '5000', 'globalfilterresults', 'int', '', '', '390', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_maxcharacterstitle', '100', 'globalfilterresults', 'int', '', '', '401', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_clientcpnag', '1', 'globalfilterresults', 'yesno', '', '', '402', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_whitespacestripper', '0', 'globalfilterresults', 'yesno', '', '', '403', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_countdowndelayms', '1000', 'globalfilterresults', 'int', '', '', '404', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_categorydelayms', '1200', 'globalfilterresults', 'int', '', '', '405', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_categorynextdelayms', '800', 'globalfilterresults', 'int', '', '', '406', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_auctiondescriptioncutoff', '40', 'globalfilterresults', 'int', '', '', '411', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_auctiontitlecutoff', '25', 'globalfilterresults', 'int', '', '', '412', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_specialshomepage', '1', 'globalfilterresults', 'yesno', '', '', '408', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_ajaxrefresh', '0', 'globalfilterresults', 'yesno', '', '', '409', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_regionmodal', '1', 'globalfilterresults', 'yesno', '', '', '410', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_listinginventory', '0', 'globalfilterresults', 'yesno', '', '', '416', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserverlocale_globaltimeformat', 'D, M d, Y h:i:s A', 'globalserverlocale', 'text', '', '', '3', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserverlocale_globaldateformat', 'M d, Y', 'globalserverlocale', 'text', '', '', '2', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserverlocale_yesterdaytodayformat', '1', 'globalserverlocale', 'yesno', '', '', '4', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalsecurity_blockregistrationproxies', '0', 'globalsecuritysettings', 'yesno', '', '', '1', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_accountsabbrev', 'IL', 'globalserversettings', 'text', '', '', '6', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_seourls', '0', 'globalseo', 'yesno', '', '', '1', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('seourls_lowercase', '1', 'globalseo', 'yesno', '', '', '2', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('seourls_utf8', '1', 'globalseo', 'yesno', '', '', '3', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_maximumpaymentdays', '15', 'invoicesystem', 'int', '', '', '4', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_mindepositamount', '100', 'invoicesystem', 'int', '', '', '5', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_maxdepositamount', '1000', 'invoicesystem', 'int', '', '', '6', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_minwithdrawamount', '100', 'invoicesystem', 'int', '', '', '7', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_maxwithdrawamount', '1000', 'invoicesystem', 'int', '', '', '8', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_minofflinedepositamount', '100', 'invoicesystem', 'int', '', '', '12', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_maxofflinedepositamount', '1000', 'invoicesystem', 'int', '', '', '13', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('portfoliodisplay_thumbsperrow', '2', 'portfoliodisplay', 'int', '', '', '2', '1', 'service')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('defaultstyle', '1', 'template', 'pulldown', '', '', '3', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationdisplay_dob', '1', 'registrationdisplay', 'yesno', '', '', '5', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationdisplay_emailverification', '1', 'registrationdisplay', 'yesno', '', '', '6', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationdisplay_emailban', '', 'registrationdisplay', 'textarea', '', '', '7', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationdisplay_userban', '', 'registrationdisplay', 'textarea', '', '', '8', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('escrowsystem_enabled', '0', 'escrowsystem', 'yesno', '', '', '1', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserver_distanceformula', '0', 'globalserverdistanceapi', 'text', '', '', '5', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserver_distanceresults', 'mi', 'globalserverdistanceapi', 'text', '', '', '6', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationdisplay_dobunder18', '1', 'registrationdisplay', 'yesno', '', '', '6', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productbid_enablesniping', '0', 'productbid_limits', 'yesno', '', '', '1', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productbid_displaybidname', '1', 'productbid_limits', 'yesno', '', '', '21', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productbid_snipeduration', '2', 'productbid_limits', 'int', '', '', '2', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productbid_enableproxybid', '1', 'productbid_limits', 'yesno', '', '', '8', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_finalvaluefeesactive', '0', 'productupsell_fees', 'yesno', '', '', '1', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('escrowsystem_escrowcommissionfees', '0', 'escrowsystem', 'yesno', '', '', '3', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_registrationnumber', '', 'globalserversettings', 'text', '', '', '10', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_vatregistrationnumber', '', 'globalserversettings', 'text', '', '', '20', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_vatregistrationoption', '', 'globalserversettings', 'yesno', '', '', '30', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_dunsoption', '', 'globalserversettings', 'yesno', '', '', '40', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_ilanceaid', '', 'globalserversettings', 'text', '', '', '50', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_facebookurl', '#', 'globalserversettings', 'text', '', '', '60', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_twitterurl', '#', 'globalserversettings', 'text', '', '', '70', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_googleplusurl', '#', 'globalserversettings', 'text', '', '', '80', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_homepageadurl', '#', 'globalserversettings', 'text', '', '', '90', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationdisplay_moderation', '', 'registrationdisplay', 'yesno', '', '', '10', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_unpaidreminders', '1', 'invoicesystem', 'yesno', '', '', '9', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_resendfrequency', '15', 'invoicesystem', 'int', '', '', '10', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_daysafterfirstreminder', '3', 'invoicesystem', 'int', '', '', '11', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('portfoliodisplay_popups', '1', 'portfoliodisplay', 'yesno', '', '', '4', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('escrowsystem_servicebuyerfixedprice', '0', 'escrowsystem', 'int', '', '', '4', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('escrowsystem_servicebuyerpercentrate', '0.0', 'escrowsystem', 'int', '', '', '5', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('escrowsystem_providerfixedprice', '0', 'escrowsystem', 'int', '', '', '6', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('escrowsystem_providerpercentrate', '0.0', 'escrowsystem', 'int', '', '', '7', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_slideshowmaxfiles', '12', 'attachmentlimit_productslideshowsettings', 'int', '', '', '10', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_slideshowextensions', '.gif, .jpg, .png', 'attachmentlimit_productslideshowsettings', 'textarea', '', '', '20', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_slideshowmaxwidth', '1024', 'attachmentlimit_productslideshowsettings', 'int', '', '', '30', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_slideshowmaxheight', '768', 'attachmentlimit_productslideshowsettings', 'int', '', '', '40', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_slideshowmaxsize', '250000', 'attachmentlimit_productslideshowsettings', 'int', '', '', '50', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_digitalfilemaxsize', '2500000', 'attachmentlimit_productdigitalsettings', 'int', '', '', '20', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_insertionfeesactive', '1', 'productupsell_fees', 'yesno', '', '', '1', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_slideshowcost', '0', 'productupsell_fees', 'int', '', '', '1', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_reservepricecost', '0', 'productupsell_fees', 'int', '', '', '1', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_buynowcost', '0', 'productupsell_fees', 'int', '', '', '1', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_videodescriptioncost', '0', 'productupsell_fees', 'int', '', '', '1', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_classifiedcost', '0', 'productupsell_fees', 'int', '', '', '1', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_productphotoextensions', '.jpg, .gif, .png', 'attachmentlimit_productphotosettings', 'textarea', '', '', '10', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_productphotomaxwidth', '1024', 'attachmentlimit_productphotosettings', 'int', '', '', '20', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_productphotomaxheight', '768', 'attachmentlimit_productphotosettings', 'int', '', '', '30', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_productphotomaxsize', '250000', 'attachmentlimit_productphotosettings', 'int', '', '', '40', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_productphotowidth', '310', 'attachmentlimit_productphotosettings', 'int', '', '', '50', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_productphotoheight', '310', 'attachmentlimit_productphotosettings', 'int', '', '', '60', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_productphotothumbwidth', '62', 'attachmentlimit_productphotosettings', 'int', '', '', '70', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_productphotothumbheight', '62', 'attachmentlimit_productphotosettings', 'int', '', '', '80', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_digitalfileextensions', '.zip, .rar, .gz, .tar', 'attachmentlimit_productdigitalsettings', 'textarea', '', '', '10', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('escrowsystem_merchantfixedprice', '0', 'escrowsystem', 'int', '', '', '10', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('escrowsystem_merchantpercentrate', '0.0', 'escrowsystem', 'int', '', '', '20', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('escrowsystem_bidderfixedprice', '0', 'escrowsystem', 'int', '', '', '30', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('escrowsystem_bidderpercentrate', '0.0', 'escrowsystem', 'int', '', '', '40', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_boldactive', '1', 'serviceupsell_bold', 'yesno', '', '', '1', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_boldfees', '1', 'serviceupsell_bold', 'yesno', '', '', '1', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_boldfee', '3.75', 'serviceupsell_bold', 'int', '', '', '2', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_featuredactive', '1', 'serviceupsell_featured', 'yesno', '', '', '1', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_featuredfees', '1', 'serviceupsell_featured', 'yesno', '', '', '1', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_featuredfee', '2.75', 'serviceupsell_featured', 'int', '', '', '2', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_videodescriptioncost', '0', 'serviceupsell_fees', 'int', '', '', '1', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('portfolioupsell_featuredlength', '14', 'portfolioupsell', 'int', '', '', '4', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_featuredlength', '5', 'productupsell_featured', 'int', '', '', '4', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_featuredlength', '5', 'serviceupsell_featured', 'int', '', '', '4', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_p2bfeesfixed', '0', 'invoicesystem', 'yesno', '', '', '1', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_p2bfee', '0', 'invoicesystem', 'int', '', '', '2', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productbid_snipedurationcount', '10', 'productbid_limits', 'int', '', '', '3', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('portfoliodisplay_enabled', '1', 'portfoliodisplay', 'yesno', '', '', '1', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalsecurity_cookiename', 'ilance_', 'globalsecuritymime', 'text', '', '', '2', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('verificationlength', '365', 'verificationsystem', 'int', '', '', '1', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('verificationupdateafter', '0', 'verificationsystem', 'yesno', '', '', '2', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('verificationmoderation', '1', 'verificationsystem', 'yesno', '', '', '3', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_maxrowsdisplaysubscribers', '10', 'globalfilterresults', 'int', '', '', '7', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_refresh', '0', 'globalfilterresults', 'yesno', '', '', '10', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_cansendpms', '0', 'globalfilterspmb', 'yesno', '', '', '20', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_showlivedepositfees', '0', 'invoicesystem', 'yesno', '', '', '20', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_enableoffsitedepositpayment', '1', 'invoicesystem', 'yesno', '', '', '25', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_enableoffsitepaymenttypes', '1', 'invoicesystem', 'yesno', '', '', '30', '1', 'global')"); 	
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_sendinvoice', '1', 'invoicesystem', 'yesno', '', '', '40', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('searchfloodprotect', '0', 'search', 'yesno', '', '', '10', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('searchflooddelay', '20', 'search', 'int', '', '', '20', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('fulltextsearch', '1', 'search', 'yesno', '', '', '30', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('savedsearches', '1', 'search', 'yesno', '', '', '40', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('didyoumeancorrection', '1', 'search', 'yesno', '', '', '45', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_maincatcutoff', '50', 'globalcategorysettings', 'int', '', '', '175', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_contactform_listing', '1', 'globalfiltersrfp', 'yesno', '', '', '9', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productbid_countdownresets', '5', 'productbid_limits', 'int', '', '', '4', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('searchdefaultcolumns', '" . $ilance->db->escape_string('a:15:{s:7:"perpage";s:2:"10";s:10:"colsperrow";s:1:"4";s:4:"sort";s:2:"01";s:10:"showtimeas";s:6:"static";s:4:"list";s:4:"list";s:15:"serviceselected";a:6:{i:0;s:5:"title";i:1;s:6:"budget";i:2;s:4:"bids";i:3;s:10:"averagebid";i:4;s:8:"timeleft";i:5;s:3:"sel";}s:15:"productselected";a:7:{i:0;s:6:"sample";i:1;s:5:"title";i:2;s:5:"price";i:3;s:4:"bids";i:4;s:8:"shipping";i:5;s:8:"timeleft";i:6;s:3:"sel";}s:14:"expertselected";a:8:{i:0;s:11:"profilelogo";i:1;s:6:"expert";i:2;s:11:"credentials";i:3;s:11:"rateperhour";i:4;s:8:"earnings";i:5;s:9:"portfolio";i:6;s:7:"country";i:7;s:3:"sel";}s:14:"latestfeedback";s:4:"true";s:8:"username";s:4:"true";s:5:"icons";s:5:"false";s:15:"currencyconvert";s:5:"false";s:10:"hidelisted";s:5:"false";s:11:"hideverbose";s:5:"false";s:15:"listinglocation";s:4:"true";}') . "', 'search', 'text', '', '', '30', '0', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('current_version', '" . ILANCEVERSION . "', 'globalsecuritymime', 'int', '', '', '4', '0', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('current_sql_version', '" . SQLVERSION . "', 'globalsecuritymime', 'int', '', '', '5', '0', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productbid_bidretract', '0', 'productbid_limits', 'yesno', '', '', '10', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productbid_awardbidretract', '0', 'productbid_limits', 'yesno', '', '', '20', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('min_1_stars_value', '1', 'servicerating', 'int', '', '', '60', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('max_1_stars_value', '1.99', 'servicerating', 'int', '', '', '70', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('min_2_stars_value', '2', 'servicerating', 'int', '', '', '80', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('max_2_stars_value', '2.99', 'servicerating', 'int', '', '', '90', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('min_3_stars_value', '3', 'servicerating', 'int', '', '', '100', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('max_3_stars_value', '3.99', 'servicerating', 'int', '', '', '110', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('min_4_stars_value', '4', 'servicerating', 'int', '', '', '120', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('max_4_stars_value', '4.84', 'servicerating', 'int', '', '', '130', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('min_5_stars_value', '4.85', 'servicerating', 'int', '', '', '140', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('max_5_stars_value', '5', 'servicerating', 'int', '', '', '150', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('servicebid_bidretract', '0', 'servicebid_limits', 'yesno', '', '', '10', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('servicebid_awardbidretract', '0', 'servicebid_limits', 'yesno', '', '', '10', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('servicebid_awardwaitperiod', '7', 'servicebid_limits', 'int', '', '', '10', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('servicebid_buyerunaward', '0', 'servicebid_limits', 'yesno', '', '', '10', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_payperpost', '0', 'globalauctionsettings', 'yesno', '', '', '60', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_showfees', '1', 'globalauctionsettings', 'yesno', '', '', '70', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_endsoondays', '7', 'globalauctionsettings', 'pulldown', '', '', '80', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_archivedays', '90', 'globalauctionsettings', 'int', '', '', '90', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_catmapgenres', '0', 'globalcategorysettings', 'yesno', '', '', '100', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_newicondays', '7', 'globalcategorysettings', 'int', '', '', '110', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_catmapdepth', '2', 'globalcategorysettings', 'int', '', '', '130', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_catquestiondepth', '3', 'globalcategorysettings', 'int', '', '', '140', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_catanswerdepth', '5', 'globalcategorysettings', 'int', '', '', '150', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_catmapgenredepth', '1', 'globalcategorysettings', 'int', '', '', '160', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_showcurrentcat', '1', 'globalcategorysettings', 'yesno', '', '', '170', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_catcutoff', '10', 'globalcategorysettings', 'int', '', '', '180', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_showbackto', '1', 'globalcategorysettings', 'yesno', '', '', '190', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('portfoliodisplay_popups_width', '490', 'portfoliodisplay', 'int', '', '', '5', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('portfoliodisplay_popups_height', '410', 'portfoliodisplay', 'int', '', '', '6', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationdisplay_defaultcity', 'Toronto', 'registrationdisplay', 'text', '', '', '4', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('escrowsystem_feestaxable', '0', 'escrowsystem', 'yesno', '', '', '100', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('clean_old_log_entries', '0', 'globalsecuritysettings', 'int', '', '', '101', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('showfeaturedlistings', '1', 'globalauctionsettings', 'yesno', '', '', '100', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('showendingsoonlistings', '1', 'globalauctionsettings', 'yesno', '', '', '110', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('showlatestlistings', '1', 'globalauctionsettings', 'yesno', '', '', '120', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('categorymapcache', '0', 'globalcategorysettings', 'yesno', '', '', '300', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('categorymapcachetimeout', '30', 'globalcategorysettings', 'int', '', '', '310', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('serveroverloadlimit', '0', 'globalsecuritysettings', 'int', '', '', '400', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('multilevelpulldown', '0', 'globalcategorysettings', 'yesno', '', '', '320', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('enableskills', '1', 'skills', 'yesno', '', '', '10', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('enablepopulartags', '1', 'globalfilterresults', 'yesno', '', '', '100', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('resetpopulartags', '1', 'globalfilterresults', 'yesno', '', '', '100', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('populartagcount', '30', 'globalfilterresults', 'int', '', '', '200', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('populartaglimit', '50', 'globalfilterresults', 'int', '', '', '300', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('showadmincpnews', '1', 'globalfilterresults', 'yesno', '', '', '400', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('enablenonprofits', '0', 'nonprofits', 'yesno', '', '', '700', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_bulkupload', '0', 'globalfiltersrfp', 'yesno', '', '', '6', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_bulkuploadlimit', '1000', 'globalfiltersrfp', 'int', '', '', '7', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_bulkuploadpreviewlimit', '50', 'globalfiltersrfp', 'int', '', '', '8', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_bulkuploadcolsep', ',', 'globalfiltersrfp', 'text', '', '', '9', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_bulkuploadcolencap', '\"', 'globalfiltersrfp', 'text', '', '', '10', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('durationdays', '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,GTC', 'globalfiltersrfp', 'text', '', '', '100', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('durationhours', '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30', 'globalfiltersrfp', 'text', '', '', '200', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('durationminutes', '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30', 'globalfiltersrfp', 'text', '', '', '300', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('servicecatschema', '{HTTP_SERVER}{IDENTIFIER}/{CID}/{KEYWORDS}{CATEGORY}{URLBIT}', 'globalseo', 'text', '', '', '100', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productcatschema', '{HTTP_SERVER}{IDENTIFIER}/{CID}/{KEYWORDS}{CATEGORY}{URLBIT}', 'globalseo', 'text', '', '', '200', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('servicecatidentifier', 'Projects', 'globalseo', 'text', '', '', '300', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productcatidentifier', 'Items', 'globalseo', 'text', '', '', '400', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('servicecatmapidentifier', 'Categories/Projects', 'globalseo', 'text', '', '', '500', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productcatmapidentifier', 'Categories/Items', 'globalseo', 'text', '', '', '600', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('categoryidentifier', 'Categories', 'globalseo', 'text', '', '', '700', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('listingsidentifier', 'Listings', 'globalseo', 'text', '', '', '800', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('servicelistingschema', '{HTTP_SERVER}{IDENTIFIER}/{ID}/{KEYWORDS}{CATEGORY}{URLBIT}', 'globalseo', 'text', '', '', '900', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productlistingschema', '{HTTP_SERVER}{IDENTIFIER}/{ID}/{KEYWORDS}{CATEGORY}{URLBIT}', 'globalseo', 'text', '', '', '1000', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('servicelistingidentifier', 'Project', 'globalseo', 'text', '', '', '1100', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productlistingidentifier', 'Item', 'globalseo', 'text', '', '', '1200', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('expertslistingidentifier', 'Experts', 'globalseo', 'text', '', '', '1300', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('memberslistingidentifier', 'Members', 'globalseo', 'text', '', '', '1400', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('portfolioslistingidentifier', 'Portfolios', 'globalseo', 'text', '', '', '1500', '1', 'service')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('categorylinkheaderpopup', '0', 'globalcategorysettings', 'yesno', '', '', '1600', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('categorylinkheaderpopuptype', 'product', 'globalcategorysettings', 'text', '', '', '1700', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('categorymainsingleleftnavcount', '0', 'globalcategorysettings', 'yesno', '', '', '1800', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('worldwideshipping', '1', 'shippingsettings', 'yesno', '', '', '1800', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('shipping_regions', 'a:7:{i:0;s:6:\"europe\";i:1;s:6:\"africa\";i:2;s:10:\"antarctica\";i:3;s:4:\"asia\";i:4;s:13:\"north_america\";i:5;s:7:\"oceania\";i:6;s:13:\"south_america\";}', 'shippingsettings', 'pulldown', '<select name=\'config[shipping_regions][]\' multiple=\'multiple\'><option value=\'europe\' selected=\'selected\'>{_europe}</option><option value=\'africa\' selected=\'selected\'>{_africa}</option><option value=\'antarctica\' selected=\'selected\'>{_antarctica}</option><option value=\'asia\' selected=\'selected\'>{_asia}</option><option value=\'north_america\' selected=\'selected\'>{_north_america}</option><option value=\'oceania\' selected=\'selected\'>{_oceania}</option><option value=\'south_america\' selected=\'selected\'>{_south_america}</option></select>', '', '1810', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('maxshipservices', '5', 'shippingsettings', 'int', '', '', '1900', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('shippingapi', '0', 'shippingsettings', 'yesno', '', '', '1910', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('shippingapi_debug', '0', 'shippingsettings', 'yesno', '', '', '1915', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('digitaldownload', '1', 'shippingsettings', 'yesno', '', '', '1920', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('ups_access_id', '', 'shippingapiservices_ups', 'text', '', '', '110', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('ups_username', '', 'shippingapiservices_ups', 'text', '', '', '120', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('ups_password', '', 'shippingapiservices_ups', 'pass', '', '', '130', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('ups_server', 'https://www.ups.com/ups.app/xml/Rate', 'shippingapiservices_ups', 'text', '', '', '210', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('usps_login', '', 'shippingapiservices_usps', 'text', '', '', '220', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('usps_password', '', 'shippingapiservices_usps', 'pass', '', '', '230', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('usps_server', 'http://production.shippingapis.com/ShippingAPI.dll', 'shippingapiservices_usps', 'text', '', '', '240', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('fedex_account', '', 'shippingapiservices_fedex', 'text', '', '', '310', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('fedex_access_id', '', 'shippingapiservices_fedex', 'text', '', '', '320', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('fedex_password', '', 'shippingapiservices_fedex', 'pass', '', '', '321', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('fedex_developer_key', '', 'shippingapiservices_fedex', 'text', '', '', '325', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('enableauctiontab', '1', 'globalauctionsettings', 'yesno', '', '', '130', '1', 'product')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('enablefixedpricetab', '1', 'globalauctionsettings', 'yesno', '', '', '140', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('enableclassifiedtab', '0', 'globalauctionsettings', 'yesno', '', '', '150', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('publicfacing', '1', 'globalauctionsettings', 'yesno', '', '', '160', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_deletearchivedlistings', '0', 'globalauctionsettings', 'yesno', '', '', '150', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserverlocale_currencycatcutoff', '5', 'globalserverlocalecurrency', 'int', '', 'currencyrates', '3', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_locationformat', '[city], [zip], [state], [country]', 'globalfilterresults', 'text', '', '', '413', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_gzhandler', '0', 'globalfilterresults', 'yesno', '' , '', '414', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_jsminify', '0', 'globalfilterresults', 'yesno', '' , '', '417', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('footercronjob', '1', 'globalfilterresults', 'yesno', '' , '', '415', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('subscriptions_emailexpiryreminder', '1', 'subscriptions_settings', 'yesno', '', '', '1', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('subscriptions_defaultroleid', '0', 'subscriptions_settings', 'int',  '', '', '2', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('post_request_whitelist', 'paypal.com moneybookers.com authorize.net cashu.com plugnpay.com', 'globalsecuritymime', 'textarea', '', '', '3', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachment_forceproductupload', '1', 'attachmentlimit_productphotosettings', 'yesno',  '', '', '70', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('automation_removewatchlist', '1', 'globalauctionsettings', 'yesno',  '', '', '41', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_slideshowimages', '1', 'globalfiltersrfp', 'yesno',  '', '', '11', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_rowsslideshowimages', '1', 'globalfiltersrfp', 'int',  '', '', '12', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_product_publicboards', '1', 'search', 'yesno', '', '', '50', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_product_images', '1', 'search', 'yesno', '', '', '60', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_product_noimages', '1', 'search', 'yesno', '', '', '70', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_product_freeship', '1', 'search', 'yesno', '', '', '80', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_product_lots', '1', 'search', 'yesno', '', '', '90', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_product_escrow', '1', 'search', 'yesno', '', '', '100', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_product_donation', '1', 'search', 'yesno', '', '', '110', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_product_completed', '1', 'search', 'yesno', '', '', '120', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_work_escrow', '1', 'search', 'yesno', '', '', '140', '1', 'service')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_work_nondisclosed', '1', 'search', 'yesno', '', '', '150', '1', 'service')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_work_completed', '1', 'search', 'yesno', '', '', '160', '1', 'service')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('servicesearchheadercolumns', '1', 'search', 'yesno', '', '', '170', '1', 'service')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('productsearchheadercolumns', '1', 'search', 'yesno', '', '', '180', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('expertssearchheadercolumns', '1', 'search', 'yesno', '', '', '190', '1', 'service')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('oneleftribbonsearchresults', '0', 'search', 'yesno', '', '', '200', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registration_allow_special', '0', 'registrationdisplay', 'yesno', '', '', '110', '1', 'global')"); 
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('trend_tab', '1', 'globaltabvisibility', 'yesno', '', '', '10', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('legend_tab', '1', 'globaltabvisibility', 'yesno', '', '', '20', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('legend_tab_search_results', '1', 'globaltabvisibility', 'yesno', '', '', '21', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('popular_tab', '1', 'globaltabvisibility', 'yesno', '', '', '30', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('keywords_tab', '1', 'globaltabvisibility', 'yesno', '', '', '40', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('keywords_tab_textcutoff', '35', 'globaltabvisibility', 'int', '', '', '45', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('lowestpricecombined', '0', 'globaltabvisibility', 'yesno', '', '', '50', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('timeleftblocks', '1', 'globaltabvisibility', 'yesno', '', '', '60', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_price_tab', '1', 'globaltabvisibility', 'yesno', '', '', '80', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_currency_tab', '1', 'globaltabvisibility', 'yesno', '', '', '90', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_seller_tab', '1', 'globaltabvisibility', 'yesno', '', '', '100', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_location_tab', '1', 'globaltabvisibility', 'yesno', '', '', '110', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_options_tab', '1', 'globaltabvisibility', 'yesno', '', '', '120', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_colors_tab', '1', 'globaltabvisibility', 'yesno', '', '', '130', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_bidrange_tab', '1', 'globaltabvisibility', 'yesno', '', '', '140', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_radius_tab', '1', 'globaltabvisibility', 'yesno', '', '', '150', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_localsearch_tab', '1', 'globaltabvisibility', 'yesno', '', '', '151', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('categoryboxorder', '1', 'globalcategorysettings', 'yesno', '', '', '1900', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('default_pmb_wysiwyg', 'bbeditor', 'pmb_wysiwygsettings', 'pulldown', '<select name=\'config[default_pmb_wysiwyg]\' class=\'select\'><option value=\'bbeditor\' selected>BBeditor</option><option value=\'ckeditor\'>CKeditor</option></select>', '', '10', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('ckeditor_pmbtoolbar', '" . $ilance->db->escape_string("{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','About'] }") . "', 'pmb_wysiwygsettings', 'textarea', '', '', '20', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('ckeditor_listingdescriptiontoolbar', '" . $ilance->db->escape_string("{ name: 'document', items : [ 'Source','-','DocProps','Preview','Print','-','Templates','About' ] },
{ name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
{ name: 'editing', items : [ 'Find','Replace','-','SelectAll','-','SpellChecker', 'Scayt' ] },
'/',
{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ] },
{ name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl' ] },
{ name: 'links', items : [ 'Link','Unlink','Anchor' ] },
{ name: 'insert', items : [ 'Image','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','Iframe' ] },
'/',
{ name: 'styles', items : [ 'Styles','Format','Font','FontSize' ] },
{ name: 'colors', items : [ 'TextColor','BGColor' ] },
{ name: 'tools', items : [ 'Maximize', 'ShowBlocks' ] }") . "', 'listingdescription_wysiwygsettings', 'textarea', '', '', '20', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('default_proposal_wysiwyg', 'bbeditor', 'proposal_wysiwygsettings', 'pulldown', '<select name=\'config[default_proposal_wysiwyg]\' class=\'select\'><option value=\'bbeditor\' selected>BBeditor</option><option value=\'ckeditor\'>CKEditor</option></select>', '', '10', '1', 'service')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('ckeditor_proposaltoolbar', '" . $ilance->db->escape_string("{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','About'] }") . "', 'proposal_wysiwygsettings', 'textarea', '', '', '20', '1', 'service')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('default_profileintro_wysiwyg', 'textarea', 'profileintro_wysiwygsettings', 'pulldown', '<select name=\'config[default_profileintro_wysiwyg]\' class=\'select\'><option value=\'textarea\' selected>Textarea</option><option value=\'ckeditor\'>CKEditor</option></select>', '', '10', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('ckeditor_profileintrotoolbar', '" . $ilance->db->escape_string("{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','About'] }") . "', 'profileintro_wysiwygsettings', 'textarea', '', '', '20', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('product_draft_block', '1', 'productblocks', 'yesno', '', '', '100', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('product_invite_block', '1', 'productblocks', 'yesno', '', '', '200', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('product_restrictions_block', '1', 'productblocks', 'yesno', '', '', '300', '1', 'product')");  
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('product_publicboard_block', '1', 'productblocks', 'yesno', '', '', '400', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('product_returnpolicy_block', '1', 'productblocks', 'yesno', '', '', '500', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('product_scheduled_bidding_block', '1', 'productblocks', 'yesno', '', '', '600', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('product_videodescription_block', '1', 'productblocks', 'yesno', '', '', '700', '1', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalservercache_engine', 'none', 'globalservercache', 'pulldown', '<select name=\'config[globalservercache_engine]\' class=\'select\'><option value=\'none\' selected>None</option><option value=\'filecache\'>File Cache</option></select>', '', '100', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalservercache_prefix', 'ilance_', 'globalservercache', 'text', NULL, '', '200', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalservercache_expiry', '300', 'globalservercache', 'int', NULL, '', '300', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversession_guesttimeout', '10', 'globalserversession', 'int', '', '', '100', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversession_membertimeout', '90', 'globalserversession', 'int', '', '', '200', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversession_admintimeout', '90', 'globalserversession', 'int', '', '', '300', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversession_crawlertimeout', '5', 'globalserversession', 'int', '', '', '400', '1', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('emailssettings_queueenabled', '1', 'emailssettings', 'yesno', '', '', '100', '1', 'global')");

	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing default configuration settings . .</strong></li></ul>";
	flush();
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "configuration_groups");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "configuration_groups (
		`parentgroupname` VARCHAR(250) NOT NULL default '',
		`groupname` VARCHAR(250) NOT NULL default '',
		`sort` INT(5) NOT NULL default '0',
		`type` ENUM('global','service','product') NOT NULL DEFAULT 'global',
		PRIMARY KEY  (`groupname`),
		INDEX ( `parentgroupname` ),
		INDEX ( `type` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "configuration_groups</li>";
    
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('maintenance', 'maintenance', '10', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('servicerating', 'servicerating', '20', 'service')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('serviceupsell', 'serviceupsell_bold', '50', 'service')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('serviceupsell', 'serviceupsell_featured', '60', 'service')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('serviceupsell', 'serviceupsell_highlight', '70', 'service')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('serviceupsell', 'serviceupsell_autorelist', '80', 'service')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('serviceupsell', 'serviceupsell_featured_searchresults', '90', 'service')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('productupsell', 'productupsell_bold', '50', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('productupsell', 'productupsell_featured', '60', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('productupsell', 'productupsell_highlight', '70', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('productupsell', 'productupsell_autorelist', '80', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('productupsell', 'productupsell_featured_searchresults', '90', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('productaward', 'productaward_pmb', '80', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('productaward', 'productaward_mediashare', '90', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('productbid', 'productbid_limits', '100', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('portfoliodisplay', 'portfoliodisplay', '110', 'service')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('portfolioupsell', 'portfolioupsell', '120', 'service')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('registrationdisplay', 'registrationdisplay', '130', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('registrationupsell', 'registrationupsell', '140', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('referalsystem', 'referalsystem', '150', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentsystem', 'attachmentsystem', '160', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentmoderation', 'attachmentmoderation', '170', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentlimit', 'attachmentlimit_profileextensions', '180', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentlimit', 'attachmentlimit_defaultextensions', '190', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('escrowsystem', 'escrowsystem', '200', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentlimit', 'attachmentlimit_portfolioextensions', '210', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('invoicesystem', 'invoicesystem', '220', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalserversettings', 'globalserversettings', '230', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalserverlocalecurrency', 'globalserverlocalecurrency', '240', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalserverlocale', 'globalserverlocale', '250', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalserverdistanceapi', 'globalserverdistanceapi', '260', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalfilters', 'globalfilterspmb', '270', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalsecurity', 'globalsecuritysettings', '280', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalsecurity', 'globalsecurity', '290', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalsecurity', 'globalsecuritymime', '300', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalfilters', 'globalfiltersrfp', '310', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalfilters', 'globalfiltersbid', '320', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalfilters', 'globalfiltersvulgar', '330', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalfilters', 'globalfiltersipblacklist', '340', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalfilterresults', 'globalfilterresults', '350', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalauctionsettings', 'globalauctionsettings', '360', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalfilters', 'globalfilterspsp', '370', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('template', 'template', '390', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('productupsell', 'productupsell_fees', '400', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('verificationsystem', 'verificationsystem', '410', 'service')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('search', 'search', '420', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('servicebid', 'servicebid_limits', '430', 'service')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('language', 'language', '440', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('skills', 'skills', '450', 'service')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('nonprofits', 'nonprofits', '460', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalseo', 'globalseo', '470', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('serviceupsell', 'serviceupsell_fees', '480', 'service')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalcategorysettings', 'globalcategorysettings', '490', 'global')");	
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentlimit', 'attachmentlimit_productphotosettings', '220', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentlimit', 'attachmentlimit_productslideshowsettings', '230', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentlimit', 'attachmentlimit_productdigitalsettings', '240', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentlimit', 'attachmentlimit_searchresultsettings', '250', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentlimit', 'attachmentlimit_bidsettings', '260', 'service')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentlimit', 'attachmentlimit_pmbsettings', '270', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentlimit', 'attachmentlimit_workspacesettings',  '280', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('shippingsettings', 'shippingsettings', '290', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('shippingapiservices', 'shippingapiservices_fedex', '300', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('shippingapiservices', 'shippingapiservices_ups', '310', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('shippingapiservices', 'shippingapiservices_usps', '320', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('subscriptions_settings', 'subscriptions_settings', '510', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globaltabvisibility', 'globaltabvisibility', '520', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('wysiwygsettings', 'pmb_wysiwygsettings', '530', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('wysiwygsettings', 'listingdescription_wysiwygsettings', '540', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('wysiwygsettings', 'proposal_wysiwygsettings', '550', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('wysiwygsettings', 'profileintro_wysiwygsettings', '560', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('productblocks', 'productblocks', '300', 'product')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalserversmtp', 'globalserversmtp', '600', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalservercache', 'globalservercache', '700', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalserversession', 'globalserversession', '800', 'global')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('emailssettings', 'emailssettings', '100', 'global')");
	flush();
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing default configuration groups . .</strong></li></ul>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "creditcards");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "creditcards (
		`cc_id` INT(100) NOT NULL AUTO_INCREMENT,
		`date_added` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`date_updated` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`user_id` INT(100) NOT NULL default '0',
		`creditcard_number` VARCHAR(250) NOT NULL default '',
		`creditcard_expiry` VARCHAR(10) NOT NULL default '',
		`cvv2` VARCHAR(30) NOT NULL default '',
		`name_on_card` VARCHAR(100) NOT NULL default '',
		`phone_of_cardowner` VARCHAR(50) NOT NULL default '',
		`email_of_cardowner` VARCHAR(75) NOT NULL default '',
		`card_billing_address1` VARCHAR(200) NOT NULL default '',
		`card_billing_address2` VARCHAR(200) default NULL,
		`card_city` VARCHAR(100) NOT NULL default '',
		`card_state` VARCHAR(100) NOT NULL default '',
		`card_postalzip` VARCHAR(50) NOT NULL default '',
		`card_country` VARCHAR(100) NOT NULL default '',
		`creditcard_status` VARCHAR(200) NOT NULL default '',
		`default_card` VARCHAR(5) NOT NULL default '',
		`creditcard_type` VARCHAR(10) NOT NULL default '',
		`authorized` VARCHAR(5) NOT NULL default '',
		`auth_amount1` DOUBLE(17,2) NOT NULL default '0.00',
		`auth_amount2` DOUBLE(17,2) NOT NULL default '0.00',
		`attempt_num` VARCHAR(10) default NULL,
		`trans1_id` VARCHAR(150) NOT NULL default '',
		`trans2_id` VARCHAR(150) NOT NULL default '',
		PRIMARY KEY  (`cc_id`),
		INDEX ( `user_id` ),
		INDEX ( `creditcard_number` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "creditcards</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "cron");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "cron (
		`cronid` INT UNSIGNED NOT NULL AUTO_INCREMENT,
		`nextrun` INT UNSIGNED NOT NULL DEFAULT '0',
		`weekday` SMALLINT NOT NULL DEFAULT '0',
		`day` SMALLINT NOT NULL DEFAULT '0',
		`hour` SMALLINT NOT NULL DEFAULT '0',
		`minute` VARCHAR(100) NOT NULL DEFAULT '',
		`filename` CHAR(50) NOT NULL DEFAULT '',
		`loglevel` SMALLINT NOT NULL DEFAULT '0',
		`active` SMALLINT NOT NULL DEFAULT '1',
		`varname` VARCHAR(100) NOT NULL DEFAULT '',
		`product` VARCHAR(200) NOT NULL DEFAULT '',
		PRIMARY KEY (cronid),
		KEY nextrun (nextrun),
		UNIQUE KEY (varname),
		INDEX (`product`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "cron</li>";
	
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "cron
		(nextrun, weekday, day, hour, minute, filename, loglevel, varname, product)
		VALUES
		(1053532560, -1, -1, -1, 'a:1:{i:0;i:-1;}', 'cron.subscriptions.php', 1, 'subscriptions', 'ilance'),
		(1053532560, -1, -1, -1, 'a:1:{i:0;i:-1;}', 'cron.rfp.php',	      1, 'rfp', 'ilance'),
		(1053532560, -1, -1, -1, 'a:1:{i:0;i:30;}', 'cron.reminders.php',     1, 'reminders', 'ilance'),
		(1053532560, -1, -1, -1, 'a:1:{i:0;i:-1;}', 'cron.minute.php',        1, 'minute', 'ilance'),
		(1053532560, -1, -1, -1, 'a:1:{i:0;i:30;}', 'cron.halfhour.php',      1, 'halfhour', 'ilance'),
		(1053532560, -1, -1, -1, 'a:1:{i:0;i:60;}', 'cron.hourly.php',        1, 'hourly', 'ilance'),
		(1053271600, -1, -1,  0, 'a:1:{i:0;i:0;}',  'cron.daily.php',         1, 'daily', 'ilance'),
		(1053271600, -1, -1, 15, 'a:1:{i:0;i:0;}',  'cron.currency.php',      1, 'currency', 'ilance'),
		(1053271600, -1, -1,  0, 'a:1:{i:0;i:0;}',  'cron.dailyreports.php',  0, 'dailyreports', 'ilance'),
		(1053271600, -1, -1,  0, 'a:1:{i:0;i:0;}',  'cron.dailyrfp.php',      1, 'dailyrfp', 'ilance'),
		(1053271600, -1, -1,  0, 'a:1:{i:0;i:0;}',  'cron.creditcards.php',   1, 'creditcards', 'ilance'),
		(1053271600, -1,  1, -1, 'a:1:{i:0;i:0;}',  'cron.monthly.php',       1, 'monthly', 'ilance'),
		(1053271600, -1, -1, -1, 'a:1:{i:0;i:-1;}', 'cron.watchlist.php',     1, 'watchlist', 'ilance'),
		(1053532560, -1, -1, -1, 'a:1:{i:0;i:-1;}', 'cron.bulk_photos.php',   1, 'bulk_photos', 'ilance'),
		(1053532560,  1, -1, -1, 'a:1:{i:0;i:0;}',  'cron.weekly.php',        1, 'weekly', 'ilance'),
		(1053271600, -1, -1, 22, 'a:1:{i:0;i:0;}',  'cron.sitemap.php',       1, 'sitemap', 'ilance'),
		(1,	     -1, -1, -1, 'a:1:{i:0;i:-1;}',  'cron.emailqueue.php',   1, 'emailqueue', 'ilance')
	");
	flush();
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Building default cron job tasks . .</strong></li></ul>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "cronlog");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "cronlog (
		cronlogid INT UNSIGNED NOT NULL AUTO_INCREMENT,
		varname VARCHAR(100) NOT NULL DEFAULT '',
		dateline INT UNSIGNED NOT NULL DEFAULT '0',
		description MEDIUMTEXT,
		time FLOAT(10,2) NOT NULL default '0.00',
		PRIMARY KEY (cronlogid),
		KEY (varname),
		INDEX (`dateline`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "cronlog</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "currency");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "currency (
		`currency_id` INT(100) NOT NULL AUTO_INCREMENT,
		`currency_abbrev` VARCHAR(10) NOT NULL default '',
		`currency_name` VARCHAR(50) NOT NULL default '',
		`rate` VARCHAR(10) NOT NULL default '',
		`time` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`isdefault` INT(1) NOT NULL default '0',
		`symbol_left` VARCHAR(20) NOT NULL default '$',
		`symbol_right` VARCHAR(20) NOT NULL default '',
		`decimal_point` VARCHAR(5) NOT NULL default '.',
		`thousands_point` VARCHAR(5) NOT NULL default ',',
		`decimal_places` VARCHAR(5) NOT NULL default '2',
		PRIMARY KEY  (`currency_id`),
		INDEX ( `currency_abbrev` ),
		INDEX ( `currency_name` )
	      ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "currency</li>";
    
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (1, 'USD', 'US DOLLAR', '1.3216', '2005-03-01 10:17:47', 1, 'US$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (2, 'ISK', 'ICELANDIC KRONA', '78.64', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (3, 'AUD', 'AUSTRALIAN DOLLAR', '1.6763', '2005-03-01 10:17:47', 1, 'AU$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (4, 'BGN', 'BULGARIA LEVA', '1.9559', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (5, 'CZK', 'CZECH KORUNA', '29.955', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (6, 'GBP', 'BRITISH POUND', '0.68790', '2005-03-01 10:17:47', 1, '&pound;', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (7, 'CAD', 'CANADIAN DOLLAR', '1.6306', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (8, 'EEK', 'ESTONIAN KROON', '15.6466', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (9, 'CYP', 'CYPRUS POUND', '0.5834', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (10, 'HUF', 'HUNGARIAN FORINT', '247.20', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (11, 'DKK', 'DANISH KRONE', '7.4420', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (12, 'LTL', 'LITHUANIA LITAS', '3.4528', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (13, 'LVL', 'LATIVA LAT', '0.6960', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (14, 'EUR', 'EURO', '1.0000', '2005-03-01 10:17:47', 1, '&euro; ', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (15, 'PLN', 'POLAND ZLOTYCH', '4.0807', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (18, 'HKD', 'HONG KONG DOLLAR', '10.3082', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (22, 'JPY', 'JAPANESE YEN', '137.90', '2005-03-01 10:17:47', 1, '&yen;', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (24, 'MTL', 'MALTESE LIRA', '0.4311', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (27, 'NZD', 'NEW ZEALAND DOLLAR', '1.8201', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (28, 'NOK', 'NORWEGIAN KRONE', '8.2120', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (32, 'ZAR', 'RAND', '7.7193', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (37, 'SGD', 'SINGAPORE DOLLAR', '2.1448', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (39, 'SEK', 'SWEDISH KRONA', '9.0517', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (40, 'CHF', 'SWISS FRANC', '1.5357', '2005-03-01 10:17:47', 1, '$', '', '.', ',', '2')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (41, 'RON', 'ROMANIA NEW LEI', '3.3751', '2007-03-01 10:17:47', 1, 'RON', '', '.', ',', '2')");
	flush();
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing default currencies and rates . .</strong></li></ul>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "deposit_offline_methods");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "deposit_offline_methods (
		`id` INT(10) NOT NULL AUTO_INCREMENT,
		`name` MEDIUMTEXT,
		`number` MEDIUMTEXT,
		`swift` MEDIUMTEXT,
		`company_name` MEDIUMTEXT,
		`company_address` MEDIUMTEXT,
		`custom_notes` MEDIUMTEXT,
		`fee` DOUBLE(17,2) NOT NULL DEFAULT '0.00',
		`visible` INT(1) NOT NULL DEFAULT '1',
		`sort` INT(3) NOT NULL DEFAULT '0',
		PRIMARY KEY (`id`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "deposit_offline_methods</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_au");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_au (
		`ZIPCode` INT(5) NOT NULL,
		`City` MEDIUMTEXT,
		`State` MEDIUMTEXT,
		`Longitude` DOUBLE NOT NULL default '0',
		`Latitude` DOUBLE NOT NULL default '0',
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
	      ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_au</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_be");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_be (
		`ZIPCode` INT(5) NOT NULL,
		`City` MEDIUMTEXT,
		`Latitude` DOUBLE NOT NULL default '0',
		`Longitude` DOUBLE NOT NULL default '0',
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
	      ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_be</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_br");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_br (
		`ZIPCode` CHAR(30) NOT NULL default '',
		`City` MEDIUMTEXT,
		`Latitude` DOUBLE NOT NULL default '0',
		`Longitude` DOUBLE NOT NULL default '0',
		`State` MEDIUMTEXT,
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
	      ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_br</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_canada");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_canada (
		`ZIPCode` CHAR(30) NOT NULL default '',
		`City` MEDIUMTEXT,
		`Province` MEDIUMTEXT,
		`Latitude` DOUBLE NOT NULL default '0',
		`Longitude` DOUBLE NOT NULL default '0',
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_canada</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_de");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_de (
		`ZIPCode` VARCHAR(255) default NULL,
		`City` MEDIUMTEXT,
		`Latitude` DOUBLE NOT NULL default '0',
		`Longitude` DOUBLE NOT NULL default '0',
		`State` MEDIUMTEXT,
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
	      ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_de</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_fr");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_fr (
		`ZIPCode` VARCHAR(255) default NULL,
		`City` MEDIUMTEXT,
		`Latitude` DOUBLE NOT NULL default '0',
		`Longitude` DOUBLE NOT NULL default '0',
		`State` MEDIUMTEXT,
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
	      ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_fr</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_hu");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_hu (
		`ZIPCode` INT(5) NOT NULL,
		`City` MEDIUMTEXT,
		`Latitude` DOUBLE NOT NULL default '0',
		`Longitude` DOUBLE NOT NULL default '0',
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
	      ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_hu</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_in");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_in (
		`ZIPCode` CHAR(30) NOT NULL default '',
		`City` MEDIUMTEXT,
		`Latitude` DOUBLE NOT NULL default '0',
		`Longitude` DOUBLE NOT NULL default '0',
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_in</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_it");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_it (
		`ZIPCode` VARCHAR(255) default NULL,
		`City` MEDIUMTEXT,
		`Latitude` DOUBLE NOT NULL default '0',
		`Longitude` DOUBLE NOT NULL default '0',
		`State` MEDIUMTEXT,
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
	      ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_it</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_jp");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_jp (
		`ZIPCode` VARCHAR(255) default NULL,
		`City` MEDIUMTEXT,
		`Latitude` DOUBLE NOT NULL default '0',
		`Longitude` DOUBLE NOT NULL default '0',
		`State` MEDIUMTEXT,
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
	      ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_jp</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_ke");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_ke (
		`ZIPCode` CHAR(30) NOT NULL default '',
		`Latitude` DOUBLE NOT NULL default '0',
		`Longitude` DOUBLE NOT NULL default '0',
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_ke</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_ma");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_ma (
		`ZIPCode` INT(5) NOT NULL,
		`City` MEDIUMTEXT,
		`Latitude` DOUBLE NOT NULL default '0',
		`Longitude` DOUBLE NOT NULL default '0',
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
	      ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_ma</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_ng");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_ng (
		`ZIPCode` VARCHAR(255) default NULL,
		`City` MEDIUMTEXT,
		`Latitude` DOUBLE NOT NULL default '0',
		`Longitude` DOUBLE NOT NULL default '0',
		`State` MEDIUMTEXT,
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
	      ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_ng</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_pl");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_pl (
		`ZIPCode` VARCHAR(255) default NULL,
		`City` MEDIUMTEXT,
		`Latitude` DOUBLE NOT NULL default '0',
		`Longitude` DOUBLE NOT NULL default '0',
		`State` MEDIUMTEXT,
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
	      ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_pl</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_ro");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_ro (
		`ZIPCode` VARCHAR(255) default NULL,
		`City` MEDIUMTEXT,
		`Latitude` DOUBLE NOT NULL default '0',
		`Longitude` DOUBLE NOT NULL default '0',
		`State` MEDIUMTEXT,
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
	      ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_ro</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_sp");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_sp (
		`ZIPCode` CHAR(30) NOT NULL default '',
		`City` MEDIUMTEXT,
		`Latitude` DOUBLE NOT NULL default '0',
		`Longitude` DOUBLE NOT NULL default '0',
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_sp</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_tr");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_tr (
		`ZIPCode` VARCHAR(255) default NULL,
		`City` MEDIUMTEXT,
		`Latitude` DOUBLE NOT NULL default '0',
		`Longitude` DOUBLE NOT NULL default '0',
		`State` MEDIUMTEXT,
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
	      ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_tr</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_uk");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_uk (
		`ZIPCode` CHAR(30) NOT NULL default '',
		`Latitude` DOUBLE NOT NULL default '0',
		`Longitude` DOUBLE NOT NULL default '0',
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_uk</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_usa");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_usa (
		`ZIPCode` CHAR(10) NOT NULL default '',
		`City` CHAR(50) NOT NULL default '',
		`State` CHAR(50) NOT NULL default '',
		`Latitude` DOUBLE NOT NULL default '0',
		`Longitude` DOUBLE NOT NULL default '0',
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_usa</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "distance_nl");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "distance_nl (
		`ZIPCode` CHAR(30) NOT NULL default '',
		`Latitude` VARCHAR(150) NOT NULL default '0',
		`Longitude` VARCHAR(150) NOT NULL default '0',
		KEY `ZIPCode` (`ZIPCode`),
		KEY `Latitude` (`Latitude`),
		KEY `Longitude` (`Longitude`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "distance_nl</li>";
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:red\"><strong>Could not find distance data to import; skipping import . .</strong></li></ul>";
	flush();
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "email");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "email (
		`id` INT(200) UNSIGNED NOT NULL AUTO_INCREMENT,
		`varname` VARCHAR(100) NOT NULL default '',
		`name_eng` VARCHAR(255) NOT NULL default '',
		`subject_original` MEDIUMTEXT,
		`message_original` MEDIUMTEXT,
		`subject_eng` MEDIUMTEXT,
		`message_eng` MEDIUMTEXT,
		`type` ENUM('global','service','product') NOT NULL default 'global',
		`product` VARCHAR(100) NOT NULL default 'ilance',
		`cansend` INT(1) NOT NULL default '1',
		`departmentid` INT(5) NOT NULL default '1',
		`buyer` INT(1) NOT NULL DEFAULT '0',
		`seller` INT(1) NOT NULL DEFAULT '0',
		`admin` INT(1) NOT NULL DEFAULT '0',
		`ishtml` INT(1) NOT NULL DEFAULT '0',
		PRIMARY KEY  (`id`),
		INDEX ( `varname` ),
		INDEX ( `name_eng` ),
		INDEX ( `type` ),
		INDEX ( `product` ),
		INDEX ( `departmentid` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "email</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "emaillog");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "emaillog (
		`emaillogid` INT(10) NOT NULL AUTO_INCREMENT,
		`logtype` ENUM('escrow','subscription','subscriptionremind','send2friend','alert','queue','dailyservice','dailyproduct','dailyreport','dailyfavorites','watchlist') NOT NULL default 'alert',
		`user_id` INT(10) NOT NULL default '0',
		`project_id` INT(10) NOT NULL default '0',
		`email` VARCHAR(60) NOT NULL default '',
		`subject` VARCHAR(250) NOT NULL default '',
		`body` MEDIUMTEXT,
		`date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`varname` VARCHAR(100) NOT NULL default '',
		`type` ENUM('global','service','product') NOT NULL default 'global',
		`sent` ENUM('yes','no') NOT NULL default 'no',
		`ishtml` INT(1) NOT NULL DEFAULT '0',
		PRIMARY KEY (`emaillogid`),
		INDEX ( `logtype` ),
		INDEX ( `user_id` ),
		INDEX ( `project_id` ),
		INDEX ( `type` ),
		INDEX ( `sent` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "emaillog</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "email_departments");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "email_departments (
		`departmentid` INT(10) NOT NULL AUTO_INCREMENT,
		`title` MEDIUMTEXT,
		`email` VARCHAR(250) NOT NULL default '',
		`canremove` INT(1) NOT NULL default '1',
		PRIMARY KEY (`departmentid`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "email_departments</li>";
	
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "email_departments
		(`departmentid`, `title`, `email`, `canremove`)
		VALUES (
		NULL,
		'" . $ilance->db->escape_string($_SESSION['site_name']) . "',
		'" . $ilance->db->escape_string($_SESSION['site_email']) . "',
		0)
	");
	flush();
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing default email department . .</strong></li></ul>";

	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "email_optout");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "email_optout (
		`id` INT(10) NOT NULL AUTO_INCREMENT,
		`email` MEDIUMTEXT,
		`varname` MEDIUMTEXT,
		PRIMARY KEY (`id`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "email_optout</li>";

	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "email_queue");
	$ilance->db->query("
		CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "email_queue (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`mail` tinytext NOT NULL,
		`fromemail` tinytext NOT NULL,
		`fromname` tinytext NOT NULL,
		`departmentid` smallint(6) NOT NULL DEFAULT '0',
		`subject` text NOT NULL,
		`message` mediumtext NOT NULL,
		`dohtml` enum('1','0') NOT NULL DEFAULT '1',
		`date_added` int(11) NOT NULL,
		`varname` VARCHAR(100) NOT NULL default '',
		`type` ENUM('global','service','product') NOT NULL default 'global',
		PRIMARY KEY (`id`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "email_queue</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "error_log");
	$ilance->db->query("
		CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "error_log (
		`log_id` INT(5) NOT NULL AUTO_INCREMENT,
		`error_id` INT(5),
		`name` MEDIUMTEXT,
		`info` MEDIUMTEXT,
		`value` INT(1) NOT NULL default '1',
		PRIMARY KEY  (`log_id`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "error_log</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "failed_logins");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "failed_logins (
		`id` INT(255) NOT NULL AUTO_INCREMENT,
		`attempted_username` VARCHAR(100) NOT NULL default '',
		`attempted_password` VARCHAR(100) NOT NULL default '',
		`referrer_page` VARCHAR(200) NOT NULL default '',
		`ip_address` VARCHAR(20) NOT NULL default '',
		`datetime_failed` DATETIME NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY  (`id`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "failed_logins</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "feedback");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "feedback (
		`id` INT(100) NOT NULL AUTO_INCREMENT,
		`for_user_id` INT(10) NOT NULL default '0',
		`for_username` mediumtext,
		`project_id` INT(10) NOT NULL default '0',
		`title` mediumtext,
		`finalprice` DOUBLE(17,2) NOT NULL default '0.00',
		`buynoworderid` INT(10) NOT NULL default '0',
		`from_user_id` INT(10) NOT NULL default '0',
		`from_username` mediumtext,
		`comments` mediumtext,
		`date_added` datetime NOT NULL default '0000-00-00 00:00:00',
		`response` enum('','positive','neutral','negative') NOT NULL default '',
		`type` enum('','buyer','seller') NOT NULL,
		`cid` int(10) NOT NULL default '0',
		`cattype` enum('','service','product') NOT NULL default '',
		PRIMARY KEY  (`id`),
		INDEX (`for_user_id`),
		INDEX (`from_user_id`),
		INDEX (`project_id`),
		INDEX (`buynoworderid`),
		INDEX (`cid`),
		INDEX (`cattype`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "feedback</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "feedback_criteria");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "feedback_criteria (
		`id` INT(10) NOT NULL AUTO_INCREMENT,
		`title_eng` MEDIUMTEXT,
		`sort` INT(5) NOT NULL,
		PRIMARY KEY  (`id`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "feedback_criteria</li>";
	
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "feedback_criteria
		(`id`, `title_eng`, `sort`)
		VALUES
		(NULL, 'Item as described', 10),
		(NULL, 'Professionalism', 20),
		(NULL, 'Quality', 30),
		(NULL, 'Delivery', 40),
		(NULL, 'Price', 50),
		(NULL, 'Communication', 60),
		(NULL, 'Shipping time', 70);
	");
	flush();
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing default feedback rating criteria . .</strong></li></ul>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "feedback_import");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "feedback_import (
		`id` INT(100) NOT NULL AUTO_INCREMENT,
		`userid` INT(10) NOT NULL default '0',
		`fb_ebay` INT(10) NOT NULL default '0',
		`dv_ebay` datetime NOT NULL default '0000-00-00 00:00:00',
		`id_ebay` mediumtext,
		`fb_yahoo` INT(10) NOT NULL default '0',
		`dv_yahoo` datetime NOT NULL default '0000-00-00 00:00:00',
		`id_yahoo` mediumtext,
		`fb_emarket` INT(10) NOT NULL default '0',
		`dv_emarket` datetime NOT NULL default '0000-00-00 00:00:00',
		`id_emarket` mediumtext,
		`fb_bonanzle` INT(10) NOT NULL default '0',
		`dv_bonanzle` datetime NOT NULL default '0000-00-00 00:00:00',
		`id_bonanzle` mediumtext,
		`fb_etsy` INT(10) NOT NULL default '0',
		`dv_etsy` datetime NOT NULL default '0000-00-00 00:00:00',
		`id_etsy` mediumtext,
		`fb_ioffer` INT(10) NOT NULL default '0',
		`dv_ioffer` datetime NOT NULL default '0000-00-00 00:00:00',
		`id_ioffer` mediumtext,
		`fb_overstock` INT(10) NOT NULL default '0',
		`dv_overstock` datetime NOT NULL default '0000-00-00 00:00:00',
		`id_overstock` mediumtext,
		`fb_ricardo` INT(10) NOT NULL default '0',
		`dv_ricardo` datetime NOT NULL default '0000-00-00 00:00:00',
		`id_ricardo` mediumtext,
		`fb_amazon` INT(10) NOT NULL default '0',
		`dv_amazon` datetime NOT NULL default '0000-00-00 00:00:00',
		`id_amazon` mediumtext,
		`fb_ebid` INT(10) NOT NULL default '0',
		`dv_ebid` datetime NOT NULL default '0000-00-00 00:00:00',
		`id_ebid` mediumtext,
		`fb_ebidus` INT(10) NOT NULL default '0',
		`dv_ebidus` datetime NOT NULL default '0000-00-00 00:00:00',
		`id_ebidus` mediumtext,
		PRIMARY KEY  (`id`),
		INDEX (`userid`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "feedback_import</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "feedback_ratings");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "feedback_ratings (
		`id` INT(100) NOT NULL AUTO_INCREMENT,
		`user_id` INT(10) NOT NULL default '0',
		`project_id` INT(10) NOT NULL default '0',
		`buynoworderid` INT(10) NOT NULL default '0',
		`criteria_id` INT(10) NOT NULL default '0',
		`rating` DOUBLE NOT NULL,
		`cid` int(10) NOT NULL default '0',
		`cattype` enum('','service','product') NOT NULL default '',
		PRIMARY KEY  (`id`),
		INDEX ( `user_id` ),
		INDEX ( `project_id` ),
		INDEX ( `buynoworderid` ),
		INDEX ( `criteria_id` ),
		INDEX ( `cid` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "feedback_ratings</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "feedback_response");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "feedback_response (
		`id` INT(100) NOT NULL AUTO_INCREMENT,
		`feedbackid` INT(10) NOT NULL default '0',
		`for_user_id` INT(10) NOT NULL default '0',
		`for_username` mediumtext,
		`project_id` INT(10) NOT NULL default '0',
		`buynoworderid` INT(10) NOT NULL default '0',
		`from_user_id` INT(10) NOT NULL default '0',
		`from_username` mediumtext,
		`comments` mediumtext,
		`date_added` datetime NOT NULL default '0000-00-00 00:00:00',
		`type` enum('','buyer','seller') NOT NULL,
		`cid` int(10) NOT NULL default '0',
		`cattype` enum('','service','product') NOT NULL default '',
		PRIMARY KEY  (`id`),
		INDEX ( `feedbackid` ),
		INDEX ( `for_user_id` ),
		INDEX ( `project_id` ),
		INDEX ( `buynoworderid` ),
		INDEX ( `from_user_id` ),
		INDEX ( `cid` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "feedback_response</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "finalvalue");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "finalvalue (
		`tierid` INT(5) NOT NULL AUTO_INCREMENT,
		`groupname` VARCHAR(50) NOT NULL default 'default',
		`finalvalue_from` DOUBLE(17,2) NOT NULL default '0.00',
		`finalvalue_to` DOUBLE(17,2) NOT NULL default '0.00',
		`amountfixed` DOUBLE(17,2) NOT NULL default '0.00',
		`amountpercent` VARCHAR(10) NOT NULL default '',
		`state` ENUM('service','product') NOT NULL default 'service',
		`sort` INT(5) NOT NULL default '0',
		PRIMARY KEY  (`tierid`),
		INDEX ( `groupname` ),
		INDEX ( `state` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	echo "<li>" . DB_PREFIX . "finalvalue</li>";
	flush();
	
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "finalvalue
		(`tierid`, `groupname`, `finalvalue_from`, `finalvalue_to`, `amountfixed`, `amountpercent`, `state`, `sort`)
		VALUES
		(1, 'default', '0.01', '250.00', '0', '15.0', 'service', 10),
		(2, 'default', '250.01', '500.00', '0', '10.0', 'service', 20),
		(3, 'default', '500.01', '1000.00', '0', '5.0', 'service', 30),
		(4, 'default', '1000.01', '-1', '0', '1.25', 'service', 40),
		(5, 'default', '0.01', '250.00', '0', '17.0', 'product', 10),
		(6, 'default', '250.01', '500.00', '0', '10.0', 'product', 20),
		(7, 'default', '500.01', '1000.00', '0', '5.0', 'product', 30),
		(8, 'default', '1000.01', '-1', '0', '1.25', 'product', 40)
	");
	flush();
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing default final value fees . .</strong></li></ul>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "finalvalue_groups");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "finalvalue_groups (
		`groupid` INT(5) NOT NULL AUTO_INCREMENT,
		`groupname` VARCHAR(50) NOT NULL default 'default',
		`description` MEDIUMTEXT,
		`state` ENUM('service','product') NOT NULL default 'service',
		KEY `groupid` (`groupid`),
		INDEX ( `groupname` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	echo "<li>" . DB_PREFIX . "finalvalue_groups</li>";
	flush();
	
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "finalvalue_groups
		(`groupid`, `groupname`, `description`, `state`)
		VALUES
		(NULL, 'default', 'This service final value group will hold a 4-tier commission fee structure', 'service'),
		(NULL, 'default', 'This product final value group will hold a 4-tier commission fee structure', 'product')
	");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing default final groups . .</strong></li></ul>";
	flush();
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "hero");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "hero (
		`id` int(5) NOT NULL AUTO_INCREMENT,
		`mode` enum('homepage','categorymap') NOT NULL default 'homepage',
		`cid` INT(5) NOT NULL default '0',
		`filename` VARCHAR(250) NOT NULL default '',
		`imagemap` mediumtext,
		`date_added` datetime NOT NULL default '0000-00-00 00:00:00',
		`sort` INT(5) NOT NULL default '0',
		PRIMARY KEY (`id`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	echo "<li>" . DB_PREFIX . "hero</li>";
	flush();
	
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "hero
		(`id`, `mode`, `cid`, `filename`, `date_added`, `sort`)
		VALUES
		(NULL, 'homepage', '0', 'img_slide1.jpg', NOW(), '10'),
		(NULL, 'homepage', '0', '14.jpg', NOW(), '20')
	");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing default hero pictures for homepage . .</strong></li></ul>";
	flush();
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "increments_groups");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "increments_groups (
		`groupid` INT(5) NOT NULL AUTO_INCREMENT,
		`groupname` VARCHAR(50) NOT NULL default 'default',
		`description` MEDIUMTEXT,
		PRIMARY KEY  (`groupid`),
		INDEX ( `groupname` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	echo "<li>" . DB_PREFIX . "increments_groups</li>";
	flush();
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "increments");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "increments (
		`incrementid` INT( 5 ) NOT NULL AUTO_INCREMENT,
		`groupname` VARCHAR(250) NOT NULL default 'default',
		`increment_from` DOUBLE(17,2) NOT NULL default '0.00',
		`increment_to` DOUBLE(17,2) NOT NULL default '0.00',
		`amount` DOUBLE(17,2) NOT NULL default '0.00',
		`sort` INT(5) NOT NULL default '0',
		`cid` INT(10) NOT NULL default '0',
		PRIMARY KEY  (`incrementid`),
		INDEX ( `groupname` ),
		INDEX ( `cid` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	echo "<li>" . DB_PREFIX . "increments</li>";
	flush();
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "industries");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "industries (
		`cid` int(100) NOT NULL auto_increment,
		`parentid` int(100) NOT NULL default '0',
		`level` int(5) NOT NULL default '1',
		`title_eng` mediumtext,
		`description_eng` mediumtext,
		`views` int(100) NOT NULL default '0',
		`keywords` mediumtext,
		`visible` int(1) NOT NULL default '1',
		`sort` int(3) NOT NULL default '0',
		PRIMARY KEY  (`cid`),
		INDEX ( `parentid` ),
		INDEX ( `level` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "industries</li>";
	
	$ilance->db->query("
		INSERT INTO `" . DB_PREFIX . "industries` (`cid`, `parentid`, `level`, `title_eng`, `description_eng`, `views`, `keywords`, `visible`, `sort`) VALUES
		(1, 0, 1, 'Agriculture', NULL, 0, 'Agriculture', 1, 100),
		(2, 0, 1, 'Arts', NULL, 0, 'Arts', 1, 200),
		(3, 0, 1, 'Construction', NULL, 0, 'Construction', 1, 300),
		(4, 0, 1, 'Consumer Goods', NULL, 0, 'Consumer Goods', 1, 400),
		(5, 0, 1, 'Corporate', NULL, 0, 'Corporate', 1, 500),
		(6, 0, 1, 'Educational', NULL, 0, 'Educational', 1, 600),
		(7, 0, 1, 'Finance', NULL, 0, 'Finance', 1, 700),
		(8, 0, 1, 'Government', NULL, 0, 'Government', 1, 800),
		(9, 0, 1, 'High Tech', NULL, 0, 'High Tech', 1, 900),
		(10, 0, 1, 'Legal', NULL, 0, 'Legal', 1, 1000),
		(11, 0, 1, 'Manufacturing', NULL, 0, NULL, 1, 1100),
		(12, 0, 1, 'Media', NULL, 0, 'Media', 1, 1200),
		(13, 0, 1, 'Non-profit', NULL, 0, 'Non-profit', 1, 1400),
		(14, 0, 1, 'Recreational', NULL, 0, 'Recreational', 1, 1500),
		(15, 0, 1, 'Service', NULL, 0, 'Service', 1, 1600),
		(16, 0, 1, 'Transportation', NULL, 0, 'Transportation', 1, 1700),
		(17, 1, 2, 'Farming', NULL, 0, 'Farming', 1, 10),
		(18, 1, 2, 'Ranching', NULL, 0, 'Ranching', 1, 20),
		(19, 1, 2, 'Dairy', NULL, 0, 'Dairy', 1, 30),
		(20, 1, 2, 'Fishery', NULL, 0, 'Fishery', 1, 40),
		(21, 2, 2, 'Motion Pictures and Film', NULL, 0, 'Motion Pictures and Film', 1, 10),
		(22, 2, 2, 'Museums and Institutions', NULL, 0, 'Museums and Institutions', 1, 20),
		(23, 2, 2, 'Fine Art', NULL, 0, 'Fine Art', 1, 30),
		(24, 2, 2, 'Performing Arts', NULL, 0, 'Performing Arts', 1, 40),
		(25, 2, 2, 'Design', NULL, 0, 'Design', 1, 50),
		(26, 2, 2, 'Arts and Crafts', NULL, 0, 'Arts and Crafts', 1, 60),
		(27, 2, 2, 'Photography', NULL, 0, 'Photography', 1, 70),
		(28, 2, 2, 'Graphic Design', NULL, 0, 'Graphic Design', 1, 80),
		(29, 3, 2, 'Construction', NULL, 0, 'Construction', 1, 10),
		(30, 3, 2, 'Building Materials', NULL, 0, 'Building Materials', 1, 20),
		(31, 3, 2, 'Architecture & Planning', NULL, 0, 'Architecture & Planning', 1, 30),
		(32, 3, 2, 'Civil Engineering', NULL, 0, 'Civil Engineering', 1, 40),
		(33, 4, 2, 'Cosmetics', NULL, 0, 'Cosmetics', 1, 1),
		(34, 4, 2, 'Apparel & Fashion', NULL, 0, 'Apparel & Fashion', 1, 2),
		(35, 4, 2, 'Sporting Goods', NULL, 0, 'Sporting Goods', 1, 3),
		(36, 4, 2, 'Tobacco', NULL, 0, 'Tobacco', 1, 4),
		(37, 4, 2, 'Supermarkets', NULL, 0, 'Supermarkets', 1, 5),
		(38, 4, 2, 'Food Production', NULL, 0, 'Food Production', 1, 6),
		(39, 4, 2, 'Consumer Electronics', NULL, 0, 'Consumer Electronics', 1, 7),
		(40, 4, 2, 'Consumer Goods', NULL, 0, 'Consumer Goods', 1, 8),
		(41, 4, 2, 'Furniture', NULL, 0, 'Furniture', 1, 9),
		(42, 4, 2, 'Retail', NULL, 0, 'Retail', 1, 10),
		(43, 4, 2, 'Wholesale', NULL, 0, 'Wholesale', 1, 11),
		(44, 4, 2, 'Import and Export', NULL, 0, 'Import and Export', 1, 12),
		(45, 4, 2, 'Wine and Spirits', NULL, 0, 'Wine and Spirits', 1, 13),
		(46, 4, 2, 'Luxury Goods & Jewelry', NULL, 0, 'Luxury Goods & Jewelry', 1, 14),
		(47, 5, 2, 'Management Consulting', NULL, 0, 'Management Consulting', 1, 1),
		(48, 5, 2, 'Marketing and Advertising', NULL, 0, 'Marketing and Advertising', 1, 2),
		(49, 5, 2, 'Market Research', NULL, 0, 'Market Research', 1, 3),
		(50, 5, 2, 'Public Relations and Communications', NULL, 0, 'Public Relations and Communications', 1, 4),
		(51, 5, 2, 'Staffing and Recruiting', NULL, 0, 'Staffing and Recruiting', 1, 5),
		(52, 5, 2, 'Professional Training & Coaching', NULL, 0, 'Professional Training & Coaching', 1, 6),
		(53, 5, 2, 'Security and Investigations', NULL, 0, 'Security and Investigations', 1, 7),
		(54, 5, 2, 'Facilities Services', NULL, 0, 'Facilities Services', 1, 8),
		(55, 5, 2, 'Outsourcing/Offshoring', NULL, 0, 'Outsourcing/Offshoring', 1, 9),
		(56, 5, 2, 'Human Resources', NULL, 0, 'Human Resources', 1, 10),
		(57, 5, 2, 'Business Supplies and Equipment', NULL, 0, 'Business Supplies and Equipment', 1, 11),
		(58, 6, 2, 'Primary/Secondary Education', NULL, 0, 'Primary/Secondary Education', 1, 1),
		(59, 6, 2, 'Higher Education', NULL, 0, 'Higher Education', 1, 2),
		(60, 6, 2, 'Education Management', NULL, 0, 'Education Management', 1, 3),
		(61, 6, 2, 'Research', NULL, 0, 'Research', 1, 4),
		(62, 6, 2, 'E-Learning', NULL, 0, 'E-Learning', 1, 5),
		(63, 7, 2, 'Banking', NULL, 0, 'Banking', 1, 1),
		(64, 7, 2, 'Insurance', NULL, 0, 'Insurance', 1, 2),
		(65, 7, 2, 'Financial Services', NULL, 0, 'Financial Services', 1, 3),
		(66, 7, 2, 'Real Estate', NULL, 0, 'Real Estate', 1, 4),
		(67, 7, 2, 'Investment Banking', NULL, 0, 'Investment Banking', 1, 5),
		(68, 7, 2, 'Investment Management', NULL, 0, 'Investment Management', 1, 6),
		(69, 7, 2, 'Accounting', NULL, 0, 'Accounting', 1, 7),
		(70, 7, 2, 'Venture Capital & Private Equity', NULL, 0, 'Venture Capital & Private Equity', 1, 8),
		(71, 7, 2, 'Commercial Real Estate', NULL, 0, 'Commercial Real Estate', 1, 9),
		(72, 7, 2, 'Capital Markets', NULL, 0, 'Capital Markets', 1, 10),
		(73, 8, 2, 'Military', NULL, 0, 'Military', 1, 1),
		(74, 8, 2, 'Legislative Office', NULL, 0, 'Legislative Office', 1, 2),
		(75, 8, 2, 'Judiciary', NULL, 0, 'Judiciary', 1, 3),
		(76, 8, 2, 'International Affairs', NULL, 0, 'International Affairs', 1, 4),
		(77, 8, 2, 'Government Administration', NULL, 0, 'Government Administration', 1, 5),
		(78, 8, 2, 'Executive Office', NULL, 0, 'Executive Office', 1, 6),
		(79, 8, 2, 'Law Enforcement', NULL, 0, 'Law Enforcement', 1, 7),
		(80, 8, 2, 'Public Safety', NULL, 0, 'Public Safety', 1, 8),
		(81, 8, 2, 'Public Policy', NULL, 0, 'Public Policy', 1, 9),
		(82, 8, 2, 'Political Organization', NULL, 0, 'Political Organization', 1, 10),
		(83, 8, 2, 'Government Relations', NULL, 0, 'Government Relations', 1, 11),
		(84, 9, 2, 'Defense & Space', NULL, 0, 'Defense & Space', 1, 1),
		(85, 9, 2, 'Computer Hardware', NULL, 0, 'Computer Hardware', 1, 2),
		(86, 9, 2, 'Computer Software', NULL, 0, 'Computer Software', 1, 3),
		(87, 9, 2, 'Computer Networking', NULL, 0, 'Computer Networking', 1, 4),
		(88, 9, 2, 'Internet', NULL, 0, 'Internet', 1, 5),
		(89, 9, 2, 'Semiconductors', NULL, 0, 'Semiconductors', 1, 6),
		(90, 9, 2, 'Telecommunications', NULL, 0, 'Telecommunications', 1, 7),
		(91, 9, 2, 'Information Technology and Services', NULL, 0, 'Information Technology and Services', 1, 8),
		(92, 9, 2, 'Nanotechnology', NULL, 0, 'Nanotechnology', 1, 9),
		(93, 9, 2, 'Computer & Network Security', NULL, 0, 'Computer & Network Security', 1, 10),
		(94, 9, 2, 'Wireless', NULL, 0, 'Wireless', 1, 11),
		(95, 10, 2, 'Law Practice', NULL, 0, 'Law Practice', 1, 1),
		(96, 10, 2, 'Legal Services', NULL, 0, 'Legal Services', 1, 2),
		(97, 10, 2, 'Alternative Dispute Resolution', NULL, 0, 'Alternative Dispute Resolution', 1, 3),
		(98, 11, 2, 'Aviation & Aerospace', NULL, 0, 'Aviation & Aerospace', 1, 1),
		(99, 11, 2, 'Automotive', NULL, 0, 'Automotive', 1, 2),
		(100, 11, 2, 'Chemicals', NULL, 0, 'Chemicals', 1, 3),
		(101, 11, 2, 'Machinery', NULL, 0, 'Machinery', 1, 4),
		(102, 11, 2, 'Mining & Metals', NULL, 0, 'Mining & Metals', 1, 5),
		(103, 11, 2, 'Oil & Energy', NULL, 0, 'Oil & Energy', 1, 6),
		(104, 11, 2, 'Shipbuilding', NULL, 0, 'Shipbuilding', 1, 7),
		(105, 11, 2, 'Utilities', NULL, 0, 'Utilities', 1, 8),
		(106, 11, 2, 'Textiles', NULL, 0, 'Textiles', 1, 9),
		(107, 11, 2, 'Paper & Forest Products', NULL, 0, 'Paper & Forest Products', 1, 10),
		(108, 11, 2, 'Railroad Manufacture', NULL, 0, 'Railroad Manufacture', 1, 11),
		(109, 11, 2, 'Electrical/Electronic Manufacturing', NULL, 0, 'Electrical/Electronic Manufacturing', 1, 12),
		(110, 11, 2, 'Plastics', NULL, 0, 'Plastics', 1, 13),
		(111, 11, 2, 'Mechanical or Industrial Engineering', NULL, 0, 'Mechanical or Industrial Engineering', 1, 14),
		(112, 11, 2, 'Renewables & Environment', NULL, 0, 'Renewables & Environment', 1, 15),
		(113, 11, 2, 'Glass, Ceramics & Concrete', NULL, 0, 'Glass, Ceramics & Concrete', 1, 16),
		(114, 11, 2, 'Packaging and Containers', NULL, 0, 'Packaging and Containers', 1, 17),
		(115, 11, 2, 'Industrial Automation', NULL, 0, 'Industrial Automation', 1, 18),
		(116, 12, 2, 'Broadcast Media', NULL, 0, 'Broadcast Media', 1, 1),
		(117, 12, 2, 'Newspapers', NULL, 0, 'Newspapers', 1, 2),
		(118, 12, 2, 'Publishing', NULL, 0, 'Publishing', 1, 3),
		(119, 12, 2, 'Printing', NULL, 0, 'Printing', 1, 4),
		(120, 12, 2, 'Writing and Editing', NULL, 0, 'Writing and Editing', 1, 5),
		(121, 12, 2, 'Online Media', NULL, 0, 'Online Media', 1, 6),
		(122, 12, 2, 'Media Production', NULL, 0, 'Media Production', 1, 7),
		(123, 12, 2, 'Animation', NULL, 0, 'Animation', 1, 8),
		(124, 0, 1, 'Medical', NULL, 0, 'Medical', 1, 1300),
		(125, 124, 2, 'Biotechnology', NULL, 0, 'Biotechnology', 1, 1),
		(126, 124, 2, 'Medical Practice', NULL, 0, 'Medical Practice', 1, 2),
		(127, 124, 2, 'Hospital & Health Care', NULL, 0, 'Hospital & Health Care', 1, 3),
		(128, 124, 2, 'Pharmaceuticals', NULL, 0, 'Pharmaceuticals', 1, 4),
		(129, 124, 2, 'Veterinary', NULL, 0, 'Veterinary', 1, 5),
		(130, 124, 2, 'Medical Devices', NULL, 0, 'Medical Devices', 1, 6),
		(131, 124, 2, 'Health, Wellness and Fitness', NULL, 0, 'Health, Wellness and Fitness', 1, 7),
		(132, 124, 2, 'Alternative Medicine', NULL, 0, 'Alternative Medicine', 1, 8),
		(133, 124, 2, 'Mental Health Care', NULL, 0, 'Mental Health Care', 1, 9),
		(134, 13, 2, 'Consumer Services', NULL, 0, 'Consumer Services', 1, 1),
		(135, 13, 2, 'Non-Profit Organization Management', NULL, 0, 'Non-Profit Organization Management', 1, 2),
		(136, 13, 2, 'Fund-Raising', NULL, 0, 'Fund-Raising', 1, 3),
		(137, 13, 2, 'Program Development', NULL, 0, 'Program Development', 1, 4),
		(138, 13, 2, 'Think Tanks', NULL, 0, 'Think Tanks', 1, 5),
		(139, 13, 2, 'Philanthropy', NULL, 0, 'Philanthropy', 1, 6),
		(140, 13, 2, 'International Trade and Development', NULL, 0, 'International Trade and Development', 1, 7),
		(141, 14, 2, 'Entertainment', NULL, 0, 'Entertainment', 1, 1),
		(142, 14, 2, 'Gambling & Casinos', NULL, 0, 'Gambling & Casinos', 1, 2),
		(143, 14, 2, 'Leisure, Travel & Tourism', NULL, 0, 'Leisure, Travel & Tourism', 1, 3),
		(144, 14, 2, 'Hospitality', NULL, 0, 'Hospitality', 1, 4),
		(145, 14, 2, 'Restaurants', NULL, 0, 'Restaurants', 1, 5),
		(146, 14, 2, 'Sports', NULL, 0, 'Sports', 1, 6),
		(147, 14, 2, 'Food & Beverages', NULL, 0, 'Food & Beverages', 1, 7),
		(148, 14, 2, 'Recreational Facilities and Services', NULL, 0, 'Recreational Facilities and Services', 1, 8),
		(149, 14, 2, 'Computer Games', NULL, 0, 'Computer Games', 1, 9),
		(150, 14, 2, 'Events Services', NULL, 0, 'Events Services', 1, 10),
		(151, 15, 2, 'Information Services', NULL, 0, 'Information Services', 1, 1),
		(152, 15, 2, 'Libraries', NULL, 0, 'Libraries', 1, 2),
		(153, 15, 2, 'Environmental Services', NULL, 0, 'Environmental Services', 1, 3),
		(154, 15, 2, 'Individual & Family Services', NULL, 0, 'Individual & Family Services', 1, 4),
		(155, 15, 2, 'Religious Institutions', NULL, 0, 'Religious Institutions', 1, 5),
		(156, 15, 2, 'Civic & Social Organization', NULL, 0, 'Civic & Social Organization', 1, 6),
		(157, 15, 2, 'Translation and Localization', NULL, 0, 'Translation and Localization', 1, 7),
		(158, 16, 2, 'Package/Freight Delivery', NULL, 0, 'Package/Freight Delivery', 1, 0),
		(159, 16, 2, 'Transportation/Trucking/Railroad', NULL, 0, 'Transportation/Trucking/Railroad', 1, 2),
		(160, 16, 2, 'Warehousing', NULL, 0, 'Warehousing', 1, 3),
		(161, 16, 2, 'Airlines/Aviation', NULL, 0, 'Airlines/Aviation', 1, 4),
		(162, 16, 2, 'Maritime', NULL, 0, 'Maritime', 1, 5),
		(163, 16, 2, 'Logistics and Supply Chain', NULL, 0, 'Logistics and Supply Chain', 1, 6);
	");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing default industries . .</strong></li></ul>";
	flush();
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "industries_answers");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "industries_answers (
		`aid` INT(5) NOT NULL AUTO_INCREMENT,
		`cid` INT(5) NOT NULL,
		`user_id` INT(10) NOT NULL,
		PRIMARY KEY  (`aid`),
		INDEX ( `cid` ),
		INDEX ( `user_id` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "industries_answers</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "insertion_fees");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "insertion_fees (
		`insertionid` INT(5) NOT NULL AUTO_INCREMENT,
		`groupname` VARCHAR(50) NOT NULL default 'default',
		`insertion_from` DOUBLE(17,2) NOT NULL default '0.00',
		`insertion_to` DOUBLE(17,2) NOT NULL default '0.00',
		`amount` DOUBLE(17,2) NOT NULL default '0.00',
		`sort` INT(5) NOT NULL default '0',
		`state` ENUM('service','product') NOT NULL default 'service',
		PRIMARY KEY  (`insertionid`),
		INDEX ( `groupname` ),
		INDEX ( `state` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	echo "<li>" . DB_PREFIX . "insertion_fees</li>";
	flush();
	
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "insertion_fees
		(`insertionid`, `groupname`, `insertion_from`, `insertion_to`, `amount`, `sort`, `state`)
		VALUES
		(1, 'default', '0', '0', '8.00', 10, 'service'),
		(3, 'default', '0.01', '0.99', '0.20', 10, 'product'),
		(4, 'default', '1.00', '9.99', '0.35', 20, 'product'),
		(5, 'default', '10.00', '24.99', '0.60', 30, 'product'),
		(6, 'default', '25.00', '49.99', '1.20', 40, 'product'),
		(7, 'default', '50.00', '199.00', '2.40', 50, 'product'),
		(8, 'default', '200.00', '499.99', '3.60', 60, 'product'),
		(9, 'default', '500.00', '-1', '4.80', 70, 'product')
	");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing default insertion fees . .</strong></li></ul>";
	flush();
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "insertion_groups");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "insertion_groups (
		`groupid` INT(5) NOT NULL AUTO_INCREMENT,
		`groupname` VARCHAR(50) NOT NULL default 'default',
		`description` MEDIUMTEXT,
		`state` ENUM('service','product') NOT NULL default 'service',
		PRIMARY KEY  (`groupid`),
		INDEX ( `groupname` ),
		INDEX ( `state` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	echo "<li>" . DB_PREFIX . "insertion_groups</li>";
	flush();
	
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "insertion_groups
		(`groupid`, `groupname`, `description`, `state`)
		VALUES
		(1, 'default', 'Default fixed insertion fees', 'service'),
		(2, 'default', 'Default product insertion fees', 'product')
	");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing default insertion groups . .</strong></li></ul>";
	flush();
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "invoicelog");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "invoicelog (
		`invoicelogid` INT(200) NOT NULL AUTO_INCREMENT,
		`user_id` INT(100) NOT NULL default '0',
		`invoiceid` INT(10) NOT NULL default '0',
		`invoicetype` ENUM('storesubscription','subscription','commission','p2b','buynow','credential','debit','credit','escrow') NOT NULL default 'debit',
		`date_sent` DATE NOT NULL default '0000-00-00',
		`date_remind` DATE NOT NULL default '0000-00-00',
		PRIMARY KEY  (`invoicelogid`),
		INDEX ( `user_id` ),
		INDEX ( `invoiceid` ),
		INDEX ( `invoicetype` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	echo "<li>" . DB_PREFIX . "invoicelog</li>";
	flush();
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "invoices");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "invoices (
		`invoiceid` INT(100) NOT NULL AUTO_INCREMENT,
		`parentid` INT(10) NOT NULL default '0',
		`currency_id` INT(5) NOT NULL default '0',
		`currency_rate` VARCHAR(10) NOT NULL default '0',
		`subscriptionid` INT(10) NOT NULL default '0',
		`projectid` INT(10) NOT NULL default '0',
		`buynowid` INT(10) NOT NULL default '0',
		`user_id` INT(100) NOT NULL default '0',
		`p2b_user_id` INT(10) NOT NULL default '0',
		`p2b_paymethod` MEDIUMTEXT,
		`p2b_markedaspaid` INT(1) NOT NULL default '0',
		`storeid` INT(10) NOT NULL default '0',
		`orderid` INT(10) NOT NULL default '0',
		`description` MEDIUMTEXT,
		`amount` DOUBLE(17,2) NOT NULL default '0.00',
		`paid` DOUBLE(17,2) default '0.00',
		`totalamount` DOUBLE(17,2) NOT NULL default '0.00',
		`istaxable` INT(1) NOT NULL default '0',
		`taxamount` DOUBLE(17,2) NOT NULL default '0.00',
		`taxinfo` MEDIUMTEXT,
		`status` ENUM('paid','unpaid','scheduled','complete','cancelled') NOT NULL default 'unpaid',
		`invoicetype` ENUM('store','storesubscription','subscription','commission','p2b','buynow','credential','debit','credit','escrow','refund') NOT NULL default 'subscription',
		`paymethod` ENUM('account','bank','visa','amex','mc','disc','paypal','paypal_pro','check','purchaseorder','cashu','moneybookers','external') NOT NULL default 'account',
		`paymentgateway` VARCHAR(200) NOT NULL default '',
		`ipaddress` VARCHAR(15) NOT NULL default '0.0.0.0',
		`referer` VARCHAR(255) NOT NULL default '',
		`createdate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`duedate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`paiddate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`canceldate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`canceuserid` INT(5) NOT NULL default '0',
		`custommessage` MEDIUMTEXT,
		`transactionid` VARCHAR(25) NOT NULL default '0',
		`archive` TINYINT(4) NOT NULL default '0',
		`ispurchaseorder` INT(1) NOT NULL default '0',
		`isdeposit` INT(1) NOT NULL default '0',
		`depositcreditamount` DOUBLE(17,2) NOT NULL default '0.00',
		`iswithdraw` INT(1) NOT NULL default '0',
		`withdrawinvoiceid` INT(5) NOT NULL default '0',
		`withdrawdebitamount` DOUBLE(17,2) NOT NULL default '0.00',
		`isfvf` INT(1) NOT NULL default '0',
		`isif` INT(1) NOT NULL default '0',
		`isportfoliofee` INT(1) NOT NULL default '0',
		`isenhancementfee` INT(1) NOT NULL default '0',
		`isescrowfee` INT(1) NOT NULL default '0',
		`iswithdrawfee` INT(1) NOT NULL default '0',
		`isp2bfee` INT(1) NOT NULL default '0',
		`isdonationfee` INT(1) NOT NULL default '0',
		`ischaritypaid` INT(1) NOT NULL default '0',
		`charityid` INT(5) NOT NULL default '0',
		`isregisterbonus` INT(1) NOT NULL default '0',
		`indispute` INT(1) NOT NULL default '0',
		`isautopayment` INT(1) NOT NULL default '0',
		`last_reminder_sent` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`refund_date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY  (`invoiceid`),
		INDEX ( `parentid` ),
		INDEX ( `currency_id` ),
		INDEX ( `subscriptionid` ),
		INDEX ( `projectid` ),
		INDEX ( `buynowid` ),
		INDEX ( `user_id` ),
		INDEX ( `p2b_user_id` ),
		INDEX ( `orderid` ),
		INDEX ( `status` ),
		INDEX ( `invoicetype` ),
		INDEX ( `paymethod` ),
		INDEX ( `transactionid` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "invoices</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "language");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "language (
		`languageid` INT(100) NOT NULL AUTO_INCREMENT,
		`title` VARCHAR(30) NOT NULL default '',
		`languagecode` VARCHAR(10) NOT NULL default '',
		`charset` VARCHAR(100) NOT NULL default '',
		`locale` VARCHAR(20) NOT NULL default 'en_US',
		`author` VARCHAR(100) NOT NULL default 'ilance',
		`textdirection` VARCHAR(3) NOT NULL default 'ltr',
		`languageiso` VARCHAR(10) NOT NULL default 'en',
		`canselect` INT(1) NOT NULL default '1',
		`installdate` DATETIME NOT NULL,
		`replacements` MEDIUMTEXT,
		PRIMARY KEY languageid (`languageid`),
		INDEX ( `title` ),
		INDEX ( `languagecode` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	echo "<li>" . DB_PREFIX . "language</li>";
	flush();
	
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "language
		(languageid, title, languagecode, charset, locale, author, textdirection, languageiso, canselect, installdate, replacements)
		VALUES(
		NULL,
		'English (US)',
		'english',
		'UTF-8',
		'en_US',
		'ilance',
		'ltr',
		'en',
		'1',
		NOW(),
		'')
	");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing default language . .</strong></li></ul>";
	flush();
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "language_phrasegroups");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "language_phrasegroups (
		`groupname` VARCHAR(100) NOT NULL default '',
		`description` MEDIUMTEXT,
		`product` VARCHAR(250) NOT NULL default 'ilance',
		KEY groupname (`groupname`),
		INDEX (`product`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	echo "<li>" . DB_PREFIX . "language_phrasegroups</li>";
	flush();
    
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('main', 'Global Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('seo', 'SEO Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('accounting', 'Accounting Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('search', 'Search Engine Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('preferences', 'Preferences Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('registration', 'Registration Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('subscription', 'Subscription Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('ipn', 'Payment Handler Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('buying', 'Buying Activities Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('feedback', 'Feedback Activities Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('rfp', 'RFP Activities Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('watchlist', 'Watchlist Activities Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('selling', 'Selling Activity Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('pmb', 'PMB Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('cron', 'Cron Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('portfolio', 'Portfolio Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('administration', 'Administration Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('livebid', 'LiveBid Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('mediashare', 'MediaShare Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('javascript', 'Javascript Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('admincp_permissions', 'AdminCP Permissions Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('admincp_configuration', 'AdminCP Configuration Phrases', 'ilance')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('admincp_configuration_groups', 'AdminCP Configuration Groups', 'ilance')");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing default phrase groups . .</strong></li></ul>";
	flush();
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "language_phrases");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "language_phrases (
		`phraseid` INT(100) NOT NULL AUTO_INCREMENT,
		`phrasegroup` MEDIUMTEXT,
		`varname` VARCHAR(250) NOT NULL default '',
		`text_original` MEDIUMTEXT NOT NULL,
		`text_eng` MEDIUMTEXT NOT NULL,
		`baselanguageid` INT(2) NOT NULL default '1',
		`isupdated` INT(1) NOT NULL default '0',
		`ismoved` INT(1) NOT NULL default '0',
		`ismaster` INT(1) NOT NULL default '0',
		PRIMARY KEY (`phraseid`),
		UNIQUE KEY varname (`varname`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "language_phrases</li>";
	
	// create xx_locations_states and import default states
	$dbengine = MYSQL_ENGINE;
	$dbtype = MYSQL_TYPE;
	
	include(DIR_SERVER_ROOT . 'install/functions/locations_schema.php');
	create_locations_schema();
	flush();
	sleep(5);
	
	// Canada cities (513KB)
	include(DIR_SERVER_ROOT . 'install/functions/locations_cities_canada.php');
	import_cities_canada();
	flush();
	sleep(10);
	
	// USA cities (2.3MB)
	include(DIR_SERVER_ROOT . 'install/functions/locations_cities_usa.php');
	import_cities_usa();
	flush();
	sleep(10);
	
	// Keyna cities (12KB)
	include(DIR_SERVER_ROOT . 'install/functions/locations_cities_kenya.php');
	import_cities_kenya();
	flush();
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "messages");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "messages (
		`messageid` INT(100) NOT NULL AUTO_INCREMENT,
		`project_id` INT(100) NOT NULL default '0',
		`user_id` INT(100) NOT NULL default '0',
		`username` VARCHAR(200) NOT NULL default '',
		`message` MEDIUMTEXT,
		`date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`visible` INT(1) NOT NULL default '1',
		PRIMARY KEY  (`messageid`),
		INDEX ( `project_id` ),
		INDEX ( `user_id` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "messages</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "modules");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "modules (
		`id` INT(10) NOT NULL AUTO_INCREMENT,
		`modulegroup` VARCHAR(250) NOT NULL default '',
		`parentkey` VARCHAR(100) NOT NULL default '',
		`tab` VARCHAR(250) NOT NULL default '',
		`template` MEDIUMTEXT,
		`subcmd` VARCHAR(250) NOT NULL default '',
		`parentid` INT(2) NOT NULL default '0',
		`sort` INT(2) NOT NULL default '0',
		PRIMARY KEY  (`id`),
		INDEX ( `modulegroup` ),
		INDEX ( `parentkey` ),
		INDEX ( `tab` ),
		INDEX ( `subcmd` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "modules</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "modules_group");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "modules_group (
		`id` INT(10) NOT NULL AUTO_INCREMENT,
		`modulegroup` VARCHAR(250) NOT NULL default '',
		`modulename` VARCHAR(250) NOT NULL default '',
		`folder` VARCHAR(250) NOT NULL default '',
		`configtable` VARCHAR(50) NOT NULL default '',
		`installcode` MEDIUMTEXT,
		`uninstallcode` MEDIUMTEXT,
		`version` VARCHAR(10) NOT NULL default '1.0.0',
		`versioncheckurl` VARCHAR(250) NOT NULL default '',
		`url` VARCHAR(250) NOT NULL default '',
		`developer` VARCHAR(250) NOT NULL default 'ILance',
		`filestructure` MEDIUMTEXT,
		`installdate` DATETIME NOT NULL,
		`upgradedate` DATETIME NOT NULL,
		PRIMARY KEY  (`id`),
		INDEX ( `modulegroup` ),
		INDEX ( `modulename` ),
		INDEX ( `folder` ),
		INDEX ( `configtable` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "modules_groups</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "motd");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "motd (
		`motdid` INT(5) NOT NULL AUTO_INCREMENT,
		`content` MEDIUMTEXT,
		`date` DATE NOT NULL,
		`visible` INT(1) NOT NULL default '1',
		PRIMARY KEY (`motdid`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "motd</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "payment_configuration");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "payment_configuration (
		`id` INT(5) NOT NULL AUTO_INCREMENT,
		`name` VARCHAR(250) NOT NULL default '',
		`description` MEDIUMTEXT,
		`value` MEDIUMTEXT,
		`configgroup` VARCHAR(250) NOT NULL default '',
		`inputtype` ENUM('yesno','int','textarea','text','pass','pulldown') NOT NULL default 'yesno',
		`inputcode` MEDIUMTEXT,
		`inputname` VARCHAR(250) NOT NULL default '',
		`help` MEDIUMTEXT,
		`sort` INT(5) NOT NULL default '0',
		`visible` INT(1) NOT NULL default '1',
		PRIMARY KEY  (`id`),
		INDEX ( `name` ),
		INDEX ( `configgroup` ),
		INDEX ( `inputtype` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "payment_configuration</li>";
    
	// WIRE SETTINGS GROUP
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'Wire Transfer', 'wiretransfer', 'text', '', '', '', 10, 1)");
	
	// DEFAULT GATEWAY SETTINGS GROUP
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'use_internal_gateway', 'Please select your credit card payment gateway [or disable]', 'none', 'defaultgateway', 'pulldown', '<select name=\"config[use_internal_gateway]\" class=\"select\"><option value=\"authnet\">Authorize.Net</option><option value=\"bluepay\">BluePay</option><option value=\"plug_n_pay\">PlugNPay</option><option value=\"psigate\">PSIGate</option><option value=\"eway\">eWAY</option><option value=\"paypal_pro\">PayPal Payments Pro</option><option value=\"none\" selected=\"selected\">Disable Credit Card Support</option></select>', 'defaultgateway', 'This setting ultimately informs the marketplace that you are allowing users to fund their online account balance using a credit card based on the selected merchant gateway.  If you would like to disable credit card support select disable credit card support from the pulldown menu.', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'save_credit_cards', 'Would you like to save credit cards in the database?', '0', 'defaultgateway', 'yesno', '', '', 'Depending on the environment which you plan on doing business this option ultimately decides if a member can add a credit card from the accounting menu for later use without having to re-type the credit card information each time a deposit is made.  Note: it may be prohibited to save credit cards in your database for your local country, state, province or region.  Please use this setting with caution.', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'creditcard_authentication', 'Enable credit card authentication process?', '1', 'defaultgateway', 'yesno', '', '', 'This setting forces users that have added a credit card to their account to complete a debit process where two transactions for two amounts both under two dollars is debited from the card.  The user then has x attempts to verify the amounts to validate the newly added credit card.', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'max_cc_verify_attempts', 'Maximum input attempts a member has during the authencity of the verify process?', '5', 'defaultgateway', 'int', '', '', 'This setting lets you decide how many input attempts the person adding the credit card has to actually get the two amounts debited correct.', 40, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'admin_cc_auth_expired_days', 'Maximum days a card will remain in the DB [with unfinished authentication]?', '60', 'defaultgateway', 'int', '', '', 'This setting defines the number of days until a credit card that has started but has not completed the card holder debit process authorization will be removed from the database.', 50, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'admin_cc_expired_days', 'Maximum days a card will remain in the DB [without attempting CC authentication process]?', '120', 'defaultgateway', 'int', '', '', 'This setting defines the number of days until a credit card that was added but never started the card holder debit process authorization will be removed from the database.', 60, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cron_refund_on_max_cc_auth_days', 'Auto-refund (to card) auth amounts [if max days for authentication exceed]?', '0', 'defaultgateway', 'yesno', '', '', 'This setting will physically refund the two authentication amounts used for the card holder debit process (if enabled) back to the credit card holder when the max days of unfinished authentcation has been met.  This feature is executed from the automation system via cron.creditcards.php.', 70, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authentication_refund_on_max_cc_attempts', 'Auto-refund (to card) auth amounts [if max attempts fail then fund online account balance]?', '0', 'defaultgateway', 'yesno', '', '', 'This setting will physically refund the two authentication amounts used for the card holder debit process (if enabled) back to the credit card holder when the user attempting to get the two debit amounts correct fails the max input attempts.  When disabled, the two authentication amounts are credited to the users account balance.', 80, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'advanced_email_filter', 'Enable email filter security [checks email MX domain record]?', '1', 'defaultgateway', 'yesno', '', '', 'When this feature is enabled, a more in depth email address security check occurs when a new credit card is added.  This option physically connects to the mail server of the email address entered to double check for authenticity. Beware: many new ccTLDs are created monthly (ie: .mobi, .tel, .me) and the function powering this feature may not support all ccTLDs and may return a false positive.  Consider beta.', 90, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'multi_creditcard_support', 'Enable multi-credit card support [customers can add more than one credit card]?', '0', 'defaultgateway', 'yesno', '', '', 'This setting ultimately allows you to let members add more than one credit card profile to their account.  Each card added will be a new payment method option within the pulldown menu from the deposit funds menu.', 100, 1)");
	
	// AUTHORIZE.NET
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'Authorize.Net', 'authnet', 'text', '', '', '', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_login', 'Authorize.Net username', 'testing', 'authnet', 'text', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_password', 'Authorize.Net password', 'testing', 'authnet', 'pass', '', '', '', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_key', 'Authorize.Net transaction key', '', 'authnet', 'pass', '', '', '', 40, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_transaction_fee', 'Authorize.Net transaction usage fee 1 [value in percentage; i.e: 0.029]', '0.029', 'authnet', 'int', '', '', '', 50, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_transaction_fee2', 'Authorize.Net transaction usage fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'authnet', 'int', '', '', '', 60, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authentication_capture', 'Authorize.Net credit card authentication process capture mode [auth|charge|capture]?', 'charge', 'authnet', 'text', '', '', '', 70, 1)");	
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authentication_refund', 'Authorize.Net credit card authentication process refund mode [process|void|credit]?', 'credit', 'authnet', 'text', '', '', '', 80, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_candeposit', 'Allow members to deposit funds using this gateway?', '1', 'authnet', 'yesno', '', '', '', 90, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authnet_subscriptions', 'Enable Authorize.Net Recurring Subscriptions? (used in subscription menu)', '0', 'authnet', 'yesno', '', '', '', 100, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authnet_enabled', 'Enable Authorize.Net gateway module?', '1', 'authnet', 'yesno', '', '', '', 110, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authnet_test', 'Enable Test Mode', '0', 'authnet', 'yesno', '', '', '', 120, 1)");
	
	// BLUEPAY
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'BluePay', 'bluepay', 'text', '', '', '', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'bluepay_accountid', 'BluePay Account ID', '', 'bluepay', 'text', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'bluepay_secretkey', 'BluePay Secret Key', '', 'bluepay', 'text', '', '', '', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'bluepay_transaction_fee', 'BluePay transaction usage fee 1 [value in percentage; i.e: 0.029]', '0.029', 'bluepay', 'int', '', '', '', 50, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'bluepay_transaction_fee2', 'BluePay transaction usage fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'bluepay', 'int', '', '', '', 60, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authentication_capture', 'BluePay credit card authentication process capture mode [auth|charge|capture]?', 'charge', 'bluepay', 'text', '', '', '', 70, 1)");	
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authentication_refund', 'BluePay credit card authentication process refund mode [process|void|credit]?', 'credit', 'bluepay', 'text', '', '', '', 80, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'bluepay_candeposit', 'Allow members to deposit funds using this gateway?', '1', 'bluepay', 'yesno', '', '', '', 90, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'bluepay_subscriptions', 'Enable BluePay Recurring Subscriptions? (used in subscription menu)', '0', 'bluepay', 'yesno', '', '', '', 100, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'bluepay_enabled', 'Enable BluePay gateway module?', '1', 'bluepay', 'yesno', '', '', '', 110, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'bluepay_test', 'Enable Test Mode', '0', 'bluepay', 'yesno', '', '', '', 120, 1)");
	
	// PLUGNPAY
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'PlugNPay', 'plug_n_pay', 'text', '', '', '', 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_login', 'Enter your PlugNPay username', 'pnpdemo', 'plug_n_pay', 'text', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_password', 'Enter your PlugNPay password', 'pnpdemo', 'plug_n_pay', 'pass', '', '', '', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_key', 'PlugNPay transaction key [supplied by plugnpay.com]', '', 'plug_n_pay', 'pass', '', '', '', 40, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_transaction_fee', 'PlugNPay transaction usage fee 1 [value in percentage; i.e: 0.029]', '0.029', 'plug_n_pay', 'int', '', '', '', 50, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_transaction_fee2', 'PlugNPay transaction usage fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'plug_n_pay', 'int', '', '', '', 60, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authentication_capture', 'PlugNPay credit card authentication process capture mode [auth|charge]?', 'charge', 'plug_n_pay', 'text', '', '', '', 70, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authentication_refund', 'PlugNPay credit card authentication process refund mode [process|void|credit]?', 'credit', 'plug_n_pay', 'text', '', '', '', 80, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_candeposit', 'Allow members to deposit funds using this gateway?', '1', 'plug_n_pay', 'yesno', '', '', '', 90, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'plug_n_pay_enabled', 'Enable PlugNPay Gateway module?', '1', 'plug_n_pay', 'yesno', '', '', '', 100, 1)");
	
	// PSIGATE
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'PSIGate', 'psigate', 'text', '', '', '', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_login', 'Enter your PSIGate StoreID', 'teststore', 'psigate', 'text', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_password', 'Enter your PSIGate passphrase', 'psigate1234', 'psigate', 'pass', '', '', '', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_transaction_fee', 'PSIGate transaction usage fee [value in percentage; i.e: 0.029]', '0.029', 'psigate', 'int', '', '', '', 40, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_transaction_fee2', 'PSIGate transaction usage fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'psigate', 'int', '', '', '', 50, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authentication_capture', 'PSIGate credit card authentication process capture mode [charge]?', 'charge', 'psigate', 'text', '', '', '', 60, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authentication_refund', 'PSIGate credit card authentication process refund mode [credit]?', 'credit', 'psigate', 'text', '', '', '', 70, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_candeposit', 'Allow members to deposit funds using this gateway?', '1', 'psigate', 'yesno', '', '', '', 80, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'psigate_enabled', 'Enable PSIGate gateway module?', '0', 'psigate', 'yesno', '', '', '', 90, 1)");
	
	// EWAY
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'eWAY', 'eway', 'text', '', '', '', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_login', 'Enter your eWAY ClientID', '87654321', 'eway', 'text', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_transaction_fee', 'eWAY transaction usage fee [value in percentage; i.e: 0.029]', '0.029', 'eway', 'int', '', '', '', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_transaction_fee2', 'eWAY transaction usage fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'eway', 'int', '', '', '', 40, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authentication_capture', 'eWAY credit card authentication process capture mode [charge]?', 'charge', 'eway', 'text', '', '', '', 50, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authentication_refund', 'eWAY credit card authentication process refund mode [credit]?', 'credit', 'eway', 'text', '', '', '', 60, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_candeposit', 'Allow members to deposit funds using this gateway?', '1', 'eway', 'yesno', '', '', '', 70, 1)");	
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'eway_enabled', 'Enable eWAY gateway module?', '0', 'eway', 'yesno', '', '', '', 80, 1)");
		
	// PAYPAL
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'PayPal', 'paypal', 'text', '', '', '', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_business_email', 'Enter your PayPal email address', 'payments@yourdomain.com', 'paypal', 'text', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_username', 'Enter your PayPal API Username', '', 'paypal', 'text', '', '', '', 21, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_password', 'Enter your PayPal API Password', '', 'paypal', 'pass', '', '', '', 22, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_signature', 'Enter your PayPal API Signature', '', 'paypal', 'text', '', '', '', 23, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_master_currency', 'Enter the currency used in PayPal transactions', 'USD', 'paypal', 'text', '', '', '', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_withdraw_fee_active', 'Enable withdraw payment usage fees?', '1', 'paypal', 'yesno', '', '', '', 40, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_withdraw_fee', 'Enter the withdraw usage fee amount', '5.00', 'paypal', 'int', '', '', '', 50, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_transaction_fee', 'Enter deposit transaction fee 1 [value in percentage; i.e: 0.029]', '0.029', 'paypal', 'int', '', '', '', 60, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_transaction_fee2', 'Enter deposit transaction fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'paypal', 'int', '', '', '', 70, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_deposit_echeck_active', 'Enable e-check deposit support [will show e-check support in payment pulldown]?', '1', 'paypal', 'yesno', '', '', '', 80, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_deposit_echeck_fee', 'Enter deposit via e-check usage fee amount [value is fixed dollar]', '5.00', 'paypal', 'int', '', '', '', 90, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_withdraw_active', 'Allow members to request withdrawals using this gateway?', '1', 'paypal', 'yesno', '', '', '', 100, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_active', 'Allow members to deposit funds using this gateway?', '1', 'paypal', 'yesno', '', '', '', 110, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_subscriptions', 'Enable PayPal Recurring Subscriptions? (used in subscription menu)', '0', 'paypal', 'yesno', '', '', '', 120, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_directpayment', 'Allow members to directly pay other members through this gateway?', '0', 'paypal', 'yesno', '', '', 'For example, if a buyer purchases an item from a seller and the seller chooses PayPal as their gateway, the marketplace will directly send the buyer to the sellers gateway for direct payment.  After payment, the buyer is redirected back to the Marketplace.', 130, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_currency', 'Enter available currency in PayPal transactions', 'CAD|EUR|GBP|USD|JPY|AUD|NZD|CHF|HKD|SGD|SEK|DKK|PLN|NOK|HUF|CZK|ILS|MXN|BRL|MYR|PHP|TWD|THB', 'paypal', 'textarea', '', '', '', 140, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_sandbox', 'Enable PayPal Sandbox testing environment?', '0', 'paypal', 'yesno', '', '', '', 150, 1)");
	
	// CASHU
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'CashU', 'cashu', 'text', '', '', '', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_business_email', 'Enter your CashU Merchant ID', 'payments@yourdomain.com', 'cashu', 'text', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_secret_code', 'Enter the secret passphrase code [must be set at cashu.com]', 'mypassphrase', 'cashu', 'text', '', '', '', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_master_currency', 'Enter the currency used in CashU transactions', 'USD', 'cashu', 'text', '', '', '', 40, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_transaction_fee', 'Enter deposit transaction fee 1 [value in percentage; i.e: 0.029]', '0.029', 'cashu', 'int', '', '', '', 50, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_transaction_fee2', 'Enter deposit transaction fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'cashu', 'int', '', '', '', 60, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_active', 'Allow members to deposit funds using this gateway?', '1', 'cashu', 'yesno', '', '', '', 70, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_testmode', 'Put this payment module in test mode only?', '0', 'cashu', 'yesno', '', '', '', 80, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_directpayment', 'Allow members to directly pay other members through this gateway?', '0', 'cashu', 'yesno', '', '', 'For example, if a buyer purchases an item from a seller and the seller chooses CashU as their gateway, the marketplace will directly send the buyer to the sellers gateway for direct payment.  After payment, the buyer is redirected back to the Marketplace.', 90, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_currency', 'Enter available currency in CashU transactions', 'EUR|GBP|BGN|USD|AUD|CAD|CZK|DKK|EEK|HKD|HUF|ILS|JPY|LTL|LVL|MYR|TWD|TRY|NZD|NOK|PLN|SGD|SKK|ZAR|KRW|SEK|CHF', 'cashu', 'textarea', '', '', '', 100, 1)");
	
	// MONEYBOOKERS
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'MoneyBookers', 'moneybookers', 'text', '', '', '', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'moneybookers_business_email', 'Enter your MoneyBookers email address', 'payments@yourdomain.com', 'moneybookers', 'text', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'moneybookers_secret_code', 'Enter the secret passphrase code [must be set at moneybookers.com]', 'mypassphrase', 'moneybookers', 'text', '', '', '', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'moneybookers_master_currency', 'Enter the currency used in MoneyBookers transactions', 'USD', 'moneybookers', 'text', '', '', '', 50, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'moneybookers_transaction_fee', 'Enter deposit transaction fee 1 [value in percentage; i.e: 0.029]', '0.029', 'moneybookers', 'int', '', '', '', 60, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'moneybookers_transaction_fee2', 'Enter deposit transaction fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'moneybookers', 'int', '', '', '', 70, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'moneybookers_active', 'Allow members to deposit funds using this gateway?', '1', 'moneybookers', 'yesno', '', '', '', 80, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'moneybookers_subscriptions', 'Enable MoneyBookers Recurring Subscriptions? (used in subscription menu)', '0', 'moneybookers', 'yesno', '', '', '', 90, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'moneybookers_directpayment', 'Allow members to directly pay other members through this gateway?', '0', 'moneybookers', 'yesno', '', '', 'For example, if a buyer purchases an item from a seller and the seller chooses MoneyBookers as their gateway, the marketplace will directly send the buyer to the sellers gateway for direct payment.  After payment, the buyer is redirected back to the Marketplace.', 100, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'moneybookers_currency', 'Enter available currency in MoneyBookers transactions', 'EUR|GBP|BGN|USD|AUD|CAD|CZK|DKK|EEK|HKD|HUF|ILS|JPY|LTL|LVL|MYR|TWD|TRY|NZD|NOK|PLN|SGD|SKK|ZAR|KRW|SEK|CHF', 'moneybookers', 'textarea', '', '', '', 110, 1)");
	
	// CHEQUE
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'checkpayout_support', 'Enable check or money order requests [via withdraw funds menu]?', '1', 'check', 'yesno', '', '', '', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'check_withdraw_fee_active', 'Enable withdraw payment usage fees?', '1', 'check', 'yesno', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'check_withdraw_fee', 'Enter the withdraw usage fee amount', '5.00', 'check', 'int', '', '', '', 30, 1)");
	
	// BANK
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'enable_bank_deposit_support', 'Enable the ability for customers to add a new bank deposit account [for withdraw payment requests]?', '1', 'bank', 'yesno', '', '', '', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'multi_bankaccount_support', 'Enable multi-bank deposit account support [customers can add more than one deposit account]?', '1', 'bank', 'yesno', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'bank_withdraw_fee_active', 'Enable withdraw payment usage fees?', '1', 'bank', 'yesno', '', '', '', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'bank_withdraw_fee', 'Enter the withdraw usage fee amount', '5.00', 'bank', 'int', '', '', '', 40, 1)");
	
	// KEYS
	$key1 = createkey(50);
	$key2 = createkey(50);
	$key3 = createkey(50);

	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'key1', 'Enter encryption layer 1 key', '" . $ilance->db->escape_string($key1) . "', 'keys', 'textarea', '', '', 'Once this value has been set it should never be changed.', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'key2', 'Enter encryption layer 2 key', '" . $ilance->db->escape_string($key2) . "', 'keys', 'textarea', '', '', 'Once this value has been set it should never be changed.', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'key3', 'Enter encryption layer 3 key', '" . $ilance->db->escape_string($key3) . "', 'keys', 'textarea', '', '', 'Once this value has been set it should never be changed.', 30, 1)");
	
	//OWNER BANK INFO
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'owner_bank_name', 'Owner Bank Name', '', 'owner_bank_info', 'text', '', '', '', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'owner_bank_account_number', 'Owner Bank Account Number', '', 'owner_bank_info', 'text', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'owner_bank_swift', 'Owner Bank Swift Code', '', 'owner_bank_info', 'text', '', '', '', 30, 1)");
	
	// PLATNOSCI.PL
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'Platnosci.pl', 'platnosci', 'text', '', '', '', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'platnosci_pos_id', 'Enter your POS id', '', 'platnosci', 'text', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'platnosci_pos_auth_key', 'Enter your POS Auth Key', '', 'platnosci', 'text', '', '', '', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'platnosci_pos_key1', 'Enter your Key1', '', 'platnosci', 'text', '', '', '', 40, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'platnosci_pos_key2', 'Enter your Key2', '', 'platnosci', 'text', '', '', '', 50, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'platnosci_transaction_fee', 'Enter deposit transaction fee 1 [value in percentage; i.e: 0.029]', '0.029', 'platnosci', 'int', '', '', '', 60, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'platnosci_transaction_fee2', 'Enter deposit transaction fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'platnosci', 'int', '', '', '', 70, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'platnosci_master_currency', 'Enter the currency used in Platnosci transactions', 'PLN', 'platnosci', 'text', '', '', '', 80, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'platnosci_active', 'Allow members to deposit funds using this gateway?', '0', 'platnosci', 'yesno', '', '', '', 110, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'platnosci_directpayment', 'Allow members to directly pay other members through this gateway?', '0', 'platnosci', 'yesno', '', '', 'For example, if a buyer purchases an item from a seller and the seller chooses Platnosci as their gateway, the marketplace will directly send the buyer to the sellers gateway for direct payment.  After payment, the buyer is redirected back to the Marketplace.', 130, 1)");
	
	// PAYPAL PRO GATEWAY PAYMENTS
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'PayPal Payments Pro', 'paypal_pro', 'text', '', '', '', 10, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_pro_username', 'Enter your PayPal Payments Pro username', '', 'paypal_pro', 'text', '', '', '', 20, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_pro_password', 'Enter your PayPal Payments Pro password', '', 'paypal_pro', 'pass', '', '', '', 30, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_pro_signature', 'Enter your PayPal Payments Pro signature', '', 'paypal_pro', 'text', '', '', '', 40, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_pro_master_currency', 'Enter the currency used in PayPal Payments Pro transactions', 'USD', 'paypal_pro', 'text', '', '', '', 50, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_pro_transaction_fee', 'Enter transaction fee 1 [value in percentage; i.e: 0.029]', '0.029', 'paypal_pro', 'int', '', '', '', 60, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_pro_transaction_fee2', 'Enter transaction fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'paypal_pro', 'int', '', '', '', 70, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_pro_currency', 'Enter available currency in PayPal Payments Pro transactions', 'CAD|EUR|GBP|USD|JPY|AUD|NZD|CHF|HKD|SGD|SEK|DKK|PLN|NOK|HUF|CZK|ILS|MXN|BRL|MYR|PHP|TWD|THB', 'paypal_pro', 'textarea', '', '', '', 80, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_pro_directpayment', 'Allow buyers to directly pay the seller (admins only) through this gateway?', '0', 'paypal_pro', 'yesno', '', '', '', 90, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_pro_sandbox', 'Enable PayPal Payments Pro Sandbox testing environment?', '0', 'paypal_pro', 'yesno', '', '', '', 100, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_candeposit', 'Allow members to deposit funds using this gateway?', '0', 'paypal_pro', 'yesno', '', '', '', 110, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_pro_subscriptions', 'Enable PayPal Payments Pro Recurring Subscriptions? (used in subscription menu)', '0', 'paypal_pro', 'yesno', '', '', '', 120, 1)");
	
	flush();
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing payment configuration settings . .</strong></li></ul>";
	
	unset($key1, $key2, $key3);
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "payment_groups");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "payment_groups (
		`parentgroupname` VARCHAR(250) NOT NULL default '',
		`groupname` VARCHAR(250) NOT NULL default '',
		`description` MEDIUMTEXT,
		`help` MEDIUMTEXT,
		`moduletype` VARCHAR(250) NOT NULL default '',
		PRIMARY KEY  (`groupname`),
		INDEX ( `moduletype` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "payment_groups</li>";
    
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('defaultgateway', 'defaultgateway', 'Credit Card Gateway Settings and Configuration', '', '')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('paypal', 'paypal', 'PayPal IPN Gateway Configuration', '', 'ipn')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('cashu', 'cashu', 'CashU IPN Gateway Configuration', '', 'ipn')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('moneybookers', 'moneybookers', 'MoneyBookers IPN Gateway Configuration', '', 'ipn')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('authnet', 'authnet', 'Authorize.Net Gateway Configuration', '', 'gateway')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('bluepay', 'bluepay', 'BluePay Gateway Configuration', '', 'gateway')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('check', 'check', 'Check / Money Order Payment Configuration', '', 'local')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('bank', 'bank', 'Bank Payment Configuration', '', 'local')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('keys', 'keys', 'Global Encryption Key Configuration and Settings', '', '')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('plug_n_pay', 'plug_n_pay', 'PlugNPay Gateway Configuration', '', 'gateway')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('psigate', 'psigate', 'PSIGate Gateway Configuration', '', 'gateway')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('eway', 'eway', 'eWAY Gateway Configuration', '', 'gateway')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('platnosci', 'platnosci', 'Platnosci.pl', '', 'ipn')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('paypal_pro','paypal_pro','PayPal Payments Pro Gateway Configuration','','gateway')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('owner_bank_info', 'owner_bank_info', 'Owner Bank Configuration', '', '')");
	flush();
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing payment configuration groups . .</strong></li></ul>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "payment_methods");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "payment_methods (
		`id` INT(10) NOT NULL AUTO_INCREMENT,
		`title` MEDIUMTEXT,
		`sort` INT(5) NOT NULL,
		PRIMARY KEY  (`id`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "payment_methods</li>";
	
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, '_see_description_for_my_accepted_payment_methods', 10)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, '_master_card', 20)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, '_visa', 30)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, '_money_order', 40)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, '_personal_check', 50)");
	flush();
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Imported payment methods . .</strong></li></ul>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "pmb");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "pmb (
		`id` INT(100) NOT NULL AUTO_INCREMENT,
		`project_id` INT(100) NOT NULL default '0',
		`event_id` INT(100) NOT NULL default '0',
		`datetime` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`message` MEDIUMTEXT,
		`subject` VARCHAR(200) NOT NULL default 'No Subject',
		`ishtml` ENUM('0', '1') NOT NULL default '0',
		PRIMARY KEY  (`id`),
		INDEX ( `project_id` ),
		INDEX ( `event_id` ),
		INDEX ( `subject` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "pmb</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "pmb_alerts");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "pmb_alerts (
		`id` INT(100) NOT NULL default '0',
		`event_id` INT(100) NOT NULL default '0',
		`project_id` INT(100) NOT NULL default '0',
		`from_id` INT(100) NOT NULL default '0',
		`to_id` INT(100) NOT NULL default '0',
		`isadmin` INT(1) NOT NULL default '0',
		`from_status` ENUM('new','active','archived','deleted') NOT NULL default 'new',
		`to_status` ENUM('new','active','archived','deleted') NOT NULL default 'new',
		`track_status` ENUM('unread','read') NOT NULL default 'unread',
		`track_dateread` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`track_popup` INT(1) NOT NULL default '0',
		PRIMARY KEY  (`id`),
		INDEX ( `event_id` ),
		INDEX ( `project_id` ),
		INDEX ( `from_id` ),
		INDEX ( `to_id` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "pmb_alerts</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "portfolio");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "portfolio (
		`portfolio_id` INT(100) NOT NULL AUTO_INCREMENT,
		`user_id` INT(100) NOT NULL default '0',
		`caption` VARCHAR(75) NOT NULL default '',
		`description` VARCHAR(100) NOT NULL default '0',
		`category_id` INT(10) NOT NULL default '0',
		`featured` INT(1) NOT NULL default '0',
		`featured_date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`featured_invoiceid` INT(5) NOT NULL default '0',
		`visible` INT(1) NOT NULL default '0',
		PRIMARY KEY  (`portfolio_id`),
		INDEX (`user_id`),
		INDEX (`category_id`),
		INDEX (`description`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "portfolio</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "product_answers");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "product_answers (
		`answerid` INT(10) NOT NULL AUTO_INCREMENT,
		`questionid` INT(10) NOT NULL default '0',
		`project_id` INT(10) NOT NULL default '0',
		`answer` MEDIUMTEXT,
		`optionid` INT(5) NOT NULL default '0',
		`date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`visible` INT(1) NOT NULL default '0',
		PRIMARY KEY  (`answerid`),
		INDEX ( `questionid` ),
		INDEX ( `project_id` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "product_answers</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "product_questions");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "product_questions (
		`questionid` INT(10) NOT NULL AUTO_INCREMENT,
		`cid` INT(10) NOT NULL default '0',
		`question_eng` MEDIUMTEXT,
		`description_eng` MEDIUMTEXT,
		`formname` VARCHAR(100) NOT NULL default '',
		`formdefault` VARCHAR(100) NOT NULL default '',
		`inputtype` ENUM('yesno','int','textarea','text','pulldown','multiplechoice','range','url') NOT NULL default 'text',
		`sort` INT(3) NOT NULL default '0',
		`visible` INT(1) NOT NULL default '1',
		`required` INT(1) NOT NULL default '0',
		`cansearch` INT(1) NOT NULL default '0',
		`canremove` INT(1) NOT NULL default '1',
		`recursive` INT(1) NOT NULL default '0',
		`guests` INT(1) NOT NULL default '1',
		PRIMARY KEY  (`questionid`),
		INDEX ( `cid` ),
		INDEX ( `formname` ),
		INDEX ( `formdefault` ),
		INDEX ( `inputtype` ),
		INDEX ( `visible` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "product_questions</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "product_questions_choices");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "product_questions_choices (
		`optionid` INT(10) NOT NULL AUTO_INCREMENT,
		`parentoptionid` INT(5) NOT NULL DEFAULT '0',
		`questionid` INT(5) NOT NULL DEFAULT '0',
		`choice_eng` MEDIUMTEXT,
		`sort` INT(3) NOT NULL DEFAULT '0',
		`visible` INT(1) NOT NULL DEFAULT '1',
		PRIMARY KEY (`optionid`),
		INDEX ( `parentoptionid` ),
		INDEX ( `questionid` ),
		INDEX ( `visible` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "product_questions_choices</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "profile_answers");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "profile_answers (
		`answerid` INT(10) NOT NULL AUTO_INCREMENT,
		`questionid` INT(10) NOT NULL default '0',
		`user_id` INT(10) NOT NULL default '0',
		`answer` MEDIUMTEXT,
		`date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`visible` INT(1) NOT NULL default '0',
		`isverified` INT(1) NOT NULL default '0',
		`verifyexpiry` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`invoiceid` INT(10) NOT NULL default '0',
		`contactname` VARCHAR(250) NOT NULL default '',
		`contactnumber` VARCHAR(50) NOT NULL default '',
		`contactnotes` MEDIUMTEXT,
		PRIMARY KEY  (`answerid`),
		INDEX ( `questionid` ),
		INDEX ( `user_id` ),
		INDEX ( `invoiceid` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "profile_answers</li>";

	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "profile_categories");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "profile_categories (
		`user_id` INT(10) NOT NULL default '0',
		`cid` INT(10) NOT NULL default '0',
		KEY `user_id` (`user_id`),
		INDEX (`cid`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "profile_categories</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "profile_filter_auction_answers");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "profile_filter_auction_answers (
		`answerid` INT(10) NOT NULL AUTO_INCREMENT,
		`questionid` INT(10) NOT NULL default '0',
		`project_id` INT(10) NOT NULL default '0',
		`user_id` INT(10) NOT NULL default '0',
		`answer` MEDIUMTEXT,
		`filtertype` ENUM( 'range', 'checkbox', 'pulldown' ) NOT NULL default 'range',
		`date` DATETIME NOT NULL,
		`visible` INT(1) NOT NULL default '1',
		PRIMARY KEY `answerid` (`answerid`),
		INDEX ( `questionid` ),
		INDEX ( `project_id` ),
		INDEX ( `user_id` ),
		INDEX ( `filtertype` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "profile_filter_auction_answers</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "profile_groups");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "profile_groups (
		`groupid` INT(10) NOT NULL AUTO_INCREMENT,
		`name` VARCHAR(250) NOT NULL default '',
		`description` VARCHAR(250) NOT NULL default '',
		`visible` INT(1) NOT NULL default '1',
		`canremove` INT(1) NOT NULL default '1',
		`cid` INT(5) NOT NULL default '0',
		PRIMARY KEY  (`groupid`),
		INDEX ( `name` ),
		INDEX ( `description` ),
		INDEX ( `cid` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "profile_groups</li>";
    
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "profile_groups VALUES (1, 'Education', 'Educational Background', 1, 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "profile_groups VALUES (2, 'Availability', 'Availability', 1, 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "profile_groups VALUES (3, 'Company', 'Company', 1, 1, 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "profile_groups VALUES (4, 'All Profile Categories', 'General', 1, 0, -1)");
	flush();
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing default profile groups and sample questions . .</strong></li></ul>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "profile_questions");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "profile_questions (
		`questionid` INT(10) NOT NULL AUTO_INCREMENT,
		`groupid` INT(10) NOT NULL default '0',
		`question` MEDIUMTEXT,
		`description` MEDIUMTEXT,
		`inputtype` ENUM('yesno','int','textarea','text','pulldown','multiplechoice','range') NOT NULL default 'text',
		`multiplechoice` MEDIUMTEXT,
		`sort` INT(3) NOT NULL default '0',
		`visible` INT(1) NOT NULL default '1',
		`required` INT(1) NOT NULL default '0',
		`canverify` INT(1) NOT NULL default '1',
		`canremove` INT(1) NOT NULL default '1',
		`verifycost` DOUBLE(17,2) NOT NULL default '0.00',
		`isfilter` INT(1) NOT NULL default '0',
		`filtertype` ENUM('pulldown','multiplechoice','range') NOT NULL default 'pulldown',
		`filtercategory` INT(10) NOT NULL default '0',
		`guests` INT(1) NOT NULL default '0',
		PRIMARY KEY  (`questionid`),
		INDEX ( `groupid` ),
		INDEX ( `inputtype` ),
		INDEX ( `filtercategory` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "profile_questions</li>";
	
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "profile_questions (questionid, groupid, question, description, inputtype, multiplechoice, sort, visible, required, canverify, canremove, verifycost, isfilter, filtertype, filtercategory) VALUES (1, 1, 'Summary Of Expertise', 'Self-summary of expertise', 'textarea', '', 4, 1, 0, 1, 1, '5.00', 0, 'pulldown', '0')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "profile_questions (questionid, groupid, question, description, inputtype, multiplechoice, sort, visible, required, canverify, canremove, verifycost, isfilter, filtertype, filtercategory) VALUES (2, 1, 'Certifications', 'Certifications received within the past 5 years', 'textarea', '', 2, 1, 0, 1, 1, '10.00', 0, 'pulldown', '0')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "profile_questions (questionid, groupid, question, description, inputtype, multiplechoice, sort, visible, required, canverify, canremove, verifycost, isfilter, filtertype, filtercategory) VALUES (3, 1, 'Licenses', 'Licenses or Awards received within the past 5 years', 'textarea', '', 3, 1, 0, 1, 1, '15.00', 0, 'pulldown', '0')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "profile_questions (questionid, groupid, question, description, inputtype, multiplechoice, sort, visible, required, canverify, canremove, verifycost, isfilter, filtertype, filtercategory) VALUES (4, 1, 'Education', 'Educational Background', 'textarea', '', 1, 1, 0, 1, 1, '20.00', 0, 'pulldown', '0')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "profile_questions (questionid, groupid, question, description, inputtype, multiplechoice, sort, visible, required, canverify, canremove, verifycost, isfilter, filtertype, filtercategory) VALUES (5, 2, 'Willing to work on-site', 'Willing to work on-site in your local area?', 'yesno', '', 3, 1, 0, 0, 1, '0.00', 0, 'pulldown', '0')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "profile_questions (questionid, groupid, question, description, inputtype, multiplechoice, sort, visible, required, canverify, canremove, verifycost, isfilter, filtertype, filtercategory) VALUES (6, 2, 'Payment Terms', 'Payment terms', 'textarea', '', 2, 1, 0, 0, 1, '0.00', 0, 'pulldown', '0')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "profile_questions (questionid, groupid, question, description, inputtype, multiplechoice, sort, visible, required, canverify, canremove, verifycost, isfilter, filtertype, filtercategory) VALUES (7, 3, 'Years In Business', 'Total years in business', 'int', '', 2, 1, 0, 1, 1, '10.00', 0, 'pulldown', '0')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "profile_questions (questionid, groupid, question, description, inputtype, multiplechoice, sort, visible, required, canverify, canremove, verifycost, isfilter, filtertype, filtercategory) VALUES (8, 3, 'Number Of Employees', 'Number of employees within company', 'int', '', 1, 1, 0, 1, 1, '5.00', 0, 'pulldown', '0')");
	flush();
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Importing default profile questions . .</strong></li></ul>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "projects");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "projects (
		`id` INT(10) NOT NULL AUTO_INCREMENT,
		`project_id` INT(15) NOT NULL default '0',
		`escrow_id` INT(10) NOT NULL default '0',
		`cid` INT(10) NOT NULL default '0',
		`description` MEDIUMTEXT,
		`ishtml` ENUM('0','1') NOT NULL default '0',
		`description_videourl` MEDIUMTEXT,
		`date_added` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`date_starts` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`date_end` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`gtc` INT(1) NOT NULL default '0',
		`gtc_cancelled` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`user_id` INT(100) NOT NULL default '0',
		`visible` INT(1) NOT NULL default '0',
		`views` INT(10) NOT NULL default '0',
		`project_title` VARCHAR(250) NOT NULL default '',
		`bids` INT(10) NOT NULL default '0',
		`bidsdeclined` INT(10) NOT NULL default '0',
		`bidsretracted` INT(10) NOT NULL default '0',
		`bidsshortlisted` INT(10) NOT NULL default '0',
		`budgetgroup` VARCHAR(30) NOT NULL default '',
		`additional_info` MEDIUMTEXT,
		`status` ENUM('draft','open','closed','expired','delisted','wait_approval','approval_accepted','frozen','finished','archived') NOT NULL default 'draft',
		`close_date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`transfertype` ENUM('userid','email') NOT NULL default 'userid',
		`transfer_to_userid` INT(10) NOT NULL default '0',
		`transfer_from_userid` INT(10) NOT NULL default '0',
		`transfer_to_email` VARCHAR(50) NOT NULL default '',
		`transfer_status` ENUM('','pending','accepted','rejected') NOT NULL default '',
		`transfer_code` VARCHAR(32) NOT NULL default '',
		`project_details` ENUM('public','invite_only','realtime') NOT NULL default 'public',
		`project_type` ENUM('reverse','forward') NOT NULL default 'reverse',
		`project_state` ENUM('service','product') NOT NULL default 'service',
		`bid_details` ENUM('open','sealed','blind','full') NOT NULL default 'open',
		`filter_rating` ENUM('0','1') NOT NULL default '0',
		`filter_country` ENUM('0','1') NOT NULL default '0',
		`filter_state` ENUM('0','1') NOT NULL default '0',
		`filter_city` ENUM('0','1') NOT NULL default '0',
		`filter_zip` ENUM('0','1') NOT NULL default '0',
		`filter_underage` ENUM('0','1') NOT NULL default '0',
		`filter_businessnumber` ENUM('0','1') NOT NULL default '0',
		`filter_bidtype` ENUM('0','1') NOT NULL default '0',
		`filter_budget` ENUM('0','1') NOT NULL default '0',
		`filter_escrow` INT(1) NOT NULL default '0',
		`filter_gateway` INT(1) NOT NULL default '0',
		`filter_ccgateway` INT(1) NOT NULL default '0',
		`filter_offline` INT(1) NOT NULL default '0',
		`filter_publicboard` ENUM('0','1') NOT NULL default '0',
		`filtered_rating` ENUM('1','2','3','4','5') NOT NULL default '1',
		`filtered_country` VARCHAR(50) NOT NULL default '',
		`filtered_state` VARCHAR(50) NOT NULL default '',
		`filtered_city` VARCHAR(20) NOT NULL default '',
		`filtered_zip` VARCHAR(10) NOT NULL default '',
		`filter_bidlimit` int(1) NOT NULL default '0',
		`filtered_bidlimit` int(10) NOT NULL default '10',
		`filtered_bidtype` ENUM('entire','hourly','daily','weekly','monthly','lot','weight','item') NOT NULL default 'entire',
		`filtered_bidtypecustom` VARCHAR(250) NOT NULL default '',
		`filtered_budgetid` INT(5) NOT NULL default '0',
		`filtered_auctiontype` ENUM('regular','fixed','classified') NOT NULL default 'regular',
		`classified_phone` VARCHAR(32) NOT NULL default '',
		`classified_price` DOUBLE(17,2) NOT NULL default '0.00',
		`urgent` INT(1) NOT NULL default '0',
		`buynow` INT(1) NOT NULL default '0',
		`buynow_price` DOUBLE(17,2) NOT NULL default '0.00',
		`buynow_qty` INT(10) NOT NULL default '0',
		`buynow_qty_lot` INT(1) NOT NULL DEFAULT '0',
		`items_in_lot` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0',
		`buynow_purchases` INT(10) NOT NULL default '0',
		`reserve` INT(1) NOT NULL default '0',
		`reserve_price` DOUBLE(17,2) NOT NULL default '0.00',
		`featured` INT(1) NOT NULL default '0',
		`featured_date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`featured_searchresults` INT(1) NOT NULL default '0',
		`highlite` INT(1) NOT NULL default '0',
		`bold` INT(1) NOT NULL default '0',
		`autorelist` INT(1) NOT NULL default '0',
		`autorelist_date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`startprice` DOUBLE(17,2) NOT NULL default '0.00',
		`paymethod` MEDIUMTEXT,
		`paymethodcc` MEDIUMTEXT,
		`paymethodoptions` MEDIUMTEXT,
		`paymethodoptionsemail` MEDIUMTEXT,
		`keywords` VARCHAR(250) NOT NULL default '',
		`currentprice` DOUBLE(17,2) NOT NULL default '0.00',
		`insertionfee` DOUBLE(17,2) NOT NULL default '0.00',
		`enhancementfee` DOUBLE(17,2) NOT NULL default '0.00',
		`fvf` DOUBLE(17,2) NOT NULL default '0.00',
		`isfvfpaid` INT(1) NOT NULL default '0',
		`isifpaid` INT(1) NOT NULL default '0',
		`isenhancementfeepaid` INT(1) NOT NULL default '0',
		`ifinvoiceid` INT(5) NOT NULL default '0',
		`enhancementfeeinvoiceid` INT(5) NOT NULL default '0',
		`fvfinvoiceid` INT(5) NOT NULL default '0',
		`returnaccepted` INT(1) NOT NULL default '0',
		`returnwithin` ENUM('0','3','7','14','30','60') NOT NULL default '0',
		`returngivenas` ENUM('none','exchange','credit','moneyback') NOT NULL default 'none',
		`returnshippaidby` ENUM('none','buyer','seller') NOT NULL default 'none',
		`returnpolicy` MEDIUMTEXT,
		`buyerfeedback` INT(1) NOT NULL default '0',
		`sellerfeedback` INT(1) NOT NULL default '0',
		`hasimage` INT(1) NOT NULL default '0',
		`hasimageslideshow` INT(1) NOT NULL default '0',
		`hasdigitalfile` INT(1) NOT NULL default '0',
		`haswinner` INT(1) NOT NULL default '0',
		`hasbuynowwinner` INT(1) NOT NULL default '0',
		`winner_user_id` INT(5) NOT NULL default '0',
		`donation` INT(1) NOT NULL default '0',
		`charityid` INT(5) NOT NULL default '0',
		`donationpercentage` INT(5) NOT NULL default '0',
		`donermarkedaspaid` INT(1) NOT NULL default '0',
		`donermarkedaspaiddate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`donationinvoiceid` INT(5) NOT NULL default '0',
		`currencyid` INT(5) NOT NULL default '0',
		`countryid` INT(5) NOT NULL default '0',
		`country` VARCHAR(250) NOT NULL default '',
		`state` VARCHAR(250) NOT NULL default '',
		`city` VARCHAR(250) NOT NULL default '',
		`zipcode` VARCHAR(50) NOT NULL default '',
		`sku` VARCHAR(250) NOT NULL default '',
		`upc` VARCHAR(250) NOT NULL default '',
		`ean` VARCHAR(250) NOT NULL default '',
		`isbn10` VARCHAR(250) NOT NULL default '',
		`isbn13` VARCHAR(250) NOT NULL default '',
		`partnumber` VARCHAR(250) NOT NULL default '',
		`modelnumber` VARCHAR(250) NOT NULL default '',
		`salestaxstate` VARCHAR(250) NOT NULL default '',
		`salestaxrate` VARCHAR(10) NOT NULL default '0',
		`salestaxentirecountry` INT(1) NOT NULL default '0',
		`salestaxshipping` INT(1) NOT NULL default '0',
		`countdownresets` INT(5) NOT NULL default '0',
		`bulkid` INT(5) NOT NULL default '0',
		`updateid` INT(5) NOT NULL default '1',
		PRIMARY KEY  (`id`),
		INDEX (`project_id`),
		INDEX (`cid`),
		INDEX (`project_title`),
		INDEX (`status`),
		INDEX (`project_details`),
		INDEX (`project_type`),
		INDEX (`project_state`),
		INDEX (`charityid`),
		INDEX (`countryid`),
		INDEX (`zipcode`),
		INDEX (`sku`),
		INDEX (`isbn10`),
		INDEX (`isbn13`),
		INDEX (`partnumber`),
		INDEX (`modelnumber`),
		INDEX (`hasimage`),
		INDEX (`hasimageslideshow`),
		INDEX (`hasdigitalfile`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "projects</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "projects_changelog");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "projects_changelog (
		`id` INT(5) NOT NULL auto_increment,
		`project_id` INT(5) NOT NULL,
		`datetime` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`changelog` MEDIUMTEXT,
		PRIMARY KEY  (`id`),
		INDEX ( `project_id` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "projects_changelog</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "projects_escrow");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "projects_escrow (
		`escrow_id` INT(100) NOT NULL AUTO_INCREMENT,
		`bid_id` INT(10) NOT NULL default '0',
		`project_id` INT(10) NOT NULL default '0',
		`invoiceid` INT(100) NOT NULL default '0',
		`project_user_id` INT(100) NOT NULL default '0',
		`user_id` INT(100) NOT NULL default '0',
		`date_awarded` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`date_paid` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`date_released` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`date_cancelled` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`escrowamount` DOUBLE(17,2) NOT NULL default '0.00',
		`bidamount` DOUBLE(17,2) NOT NULL default '0.00',
		`salestax` DOUBLE(17,2) NOT NULL default '0.00',
		`shipping` DOUBLE(17,2) NOT NULL default '0.00',
		`total` DOUBLE(17,2) NOT NULL default '0.00',
		`fee` DOUBLE(17,2) NOT NULL default '0.00',
		`fee2` DOUBLE(17,2) NOT NULL default '0.00',
		`isfeepaid` INT(1) NOT NULL default '0',
		`isfee2paid` INT(1) NOT NULL default '0',
		`feeinvoiceid` INT(5) NOT NULL default '0',
		`fee2invoiceid` INT(5) NOT NULL default '0',
		`qty` INT(5) NOT NULL default '1',
		`buyerfeedback` INT(1) NOT NULL default '0',
		`sellerfeedback` INT(1) NOT NULL default '0',
		`status` ENUM('pending','started','confirmed','finished','cancelled') NOT NULL default 'pending',
		`sellermarkedasshipped` INT(1) NOT NULL default '0',
		`sellermarkedasshippeddate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`shiptracknumber` VARCHAR(255) NOT NULL default '',
		PRIMARY KEY  (`escrow_id`),
		INDEX (`bid_id`),
		INDEX (`project_id`),
		INDEX (`invoiceid`),
		INDEX (`project_user_id`),
		INDEX (`user_id`),
		INDEX (`status`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "projects_escrow</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "projects_skills_answers");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "projects_skills_answers (
		`aid` INT(5) NOT NULL AUTO_INCREMENT,
		`cid` INT(5) NOT NULL,
		`project_id` INT(10) NOT NULL,
		PRIMARY KEY  (`aid`),
		INDEX ( `cid` ),
		INDEX ( `project_id` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "projects_skills_answers</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "projects_shipping");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "projects_shipping (
		`project_id` INT(5) NOT NULL,
		`ship_method` ENUM('flatrate', 'calculated', 'localpickup', 'digital') NOT NULL default 'localpickup',
		`ship_handlingtime` ENUM('1','2','3','4','5','10','15','30') NOT NULL default '1',
		`ship_handlingfee` DOUBLE(17,2) NOT NULL default '0.00',
		`ship_length` INT(5) NOT NULL default '0',
		`ship_width` INT(5) NOT NULL default '0',
		`ship_height` INT(5) NOT NULL default '0',
		`ship_weightlbs` INT(5) NOT NULL default '1',
		`ship_weightoz` INT(5) NOT NULL default '0',
		INDEX ( `project_id` ) 
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "projects_shipping</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "projects_shipping_destinations");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "projects_shipping_destinations (
		`destinationid` INT(100) NOT NULL AUTO_INCREMENT,
		`project_id` INT(5) NOT NULL,
		`ship_options_1` VARCHAR(250) NOT NULL default '',
		`ship_service_1` INT(5) NOT NULL default '0',
		`ship_packagetype_1` VARCHAR(250) NOT NULL default '',
		`ship_pickuptype_1` VARCHAR(250) NOT NULL default '',
		`ship_fee_1` DOUBLE(17,2) NOT NULL default '0.00',
		`ship_fee_next_1` DOUBLE(17,2) NOT NULL default '0.00',
		`ship_additionalfee_1` DOUBLE(17,2) NOT NULL default '0.00',
		`freeshipping_1` INT(1) NOT NULL default '0',
		`ship_options_2` VARCHAR(250) NOT NULL default '',
		`ship_service_2` INT(5) NOT NULL default '0',
		`ship_packagetype_2` VARCHAR(250) NOT NULL default '',
		`ship_pickuptype_2` VARCHAR(250) NOT NULL default '',
		`ship_fee_2` DOUBLE(17,2) NOT NULL default '0.00',
		`ship_fee_next_2` DOUBLE(17,2) NOT NULL default '0.00',
		`ship_additionalfee_2` DOUBLE(17,2) NOT NULL default '0.00',
		`freeshipping_2` INT(1) NOT NULL default '0',
		`ship_options_3` VARCHAR(250) NOT NULL default '',
		`ship_service_3` INT(5) NOT NULL default '0',
		`ship_packagetype_3` VARCHAR(250) NOT NULL default '',
		`ship_pickuptype_3` VARCHAR(250) NOT NULL default '',
		`ship_fee_3` DOUBLE(17,2) NOT NULL default '0.00',
		`ship_fee_next_3` DOUBLE(17,2) NOT NULL default '0.00',
		`ship_additionalfee_3` DOUBLE(17,2) NOT NULL default '0.00',
		`freeshipping_3` INT(1) NOT NULL default '0',
		`ship_options_4` VARCHAR(250) NOT NULL default '',
		`ship_service_4` INT(5) NOT NULL default '0',
		`ship_packagetype_4` VARCHAR(250) NOT NULL default '',
		`ship_pickuptype_4` VARCHAR(250) NOT NULL default '',
		`ship_fee_4` DOUBLE(17,2) NOT NULL default '0.00',
		`ship_fee_next_4` DOUBLE(17,2) NOT NULL default '0.00',
		`ship_additionalfee_4` DOUBLE(17,2) NOT NULL default '0.00',
		`freeshipping_4` INT(1) NOT NULL default '0',
		`ship_options_5` VARCHAR(250) NOT NULL default '',
		`ship_service_5` INT(5) NOT NULL default '0',
		`ship_packagetype_5` VARCHAR(250) NOT NULL default '',
		`ship_pickuptype_5` VARCHAR(250) NOT NULL default '',
		`ship_fee_5` DOUBLE(17,2) NOT NULL default '0.00',
		`ship_fee_next_5` DOUBLE(17,2) NOT NULL default '0.00',
		`ship_additionalfee_5` DOUBLE(17,2) NOT NULL default '0.00',
		`freeshipping_5` INT(1) NOT NULL default '0',
		`ship_options_6` VARCHAR(250) NOT NULL default '',
		`ship_service_6` INT(5) NOT NULL default '0',
		`ship_packagetype_6` VARCHAR(250) NOT NULL default '',
		`ship_pickuptype_6` VARCHAR(250) NOT NULL default '',
		`ship_fee_6` DOUBLE(17,2) NOT NULL default '0.00',
		`ship_fee_next_6` DOUBLE(17,2) NOT NULL default '0.00',
		`ship_additionalfee_6` DOUBLE(17,2) NOT NULL default '0.00',
		`freeshipping_6` INT(1) NOT NULL default '0',
		`ship_options_7` VARCHAR(250) NOT NULL default '',
		`ship_service_7` INT(5) NOT NULL default '0',
		`ship_packagetype_7` VARCHAR(250) NOT NULL default '',
		`ship_pickuptype_7` VARCHAR(250) NOT NULL default '',
		`ship_fee_7` DOUBLE(17,2) NOT NULL default '0.00',
		`ship_fee_next_7` DOUBLE(17,2) NOT NULL default '0.00',
		`ship_additionalfee_7` DOUBLE(17,2) NOT NULL default '0.00',
		`freeshipping_7` INT(1) NOT NULL default '0',
		`ship_options_8` VARCHAR(250) NOT NULL default '',
		`ship_service_8` INT(5) NOT NULL default '0',
		`ship_packagetype_8` VARCHAR(250) NOT NULL default '',
		`ship_pickuptype_8` VARCHAR(250) NOT NULL default '',
		`ship_fee_8` DOUBLE(17,2) NOT NULL default '0.00',
		`ship_fee_next_8` DOUBLE(17,2) NOT NULL default '0.00',
		`ship_additionalfee_8` DOUBLE(17,2) NOT NULL default '0.00',
		`freeshipping_8` INT(1) NOT NULL default '0',
		`ship_options_9` VARCHAR(250) NOT NULL default '',
		`ship_service_9` INT(5) NOT NULL default '0',
		`ship_packagetype_9` VARCHAR(250) NOT NULL default '',
		`ship_pickuptype_9` VARCHAR(250) NOT NULL default '',
		`ship_fee_9` DOUBLE(17,2) NOT NULL default '0.00',
		`ship_fee_next_9` DOUBLE(17,2) NOT NULL default '0.00',
		`ship_additionalfee_9` DOUBLE(17,2) NOT NULL default '0.00',
		`freeshipping_9` INT(1) NOT NULL default '0',
		`ship_options_10` VARCHAR(250) NOT NULL default '',
		`ship_service_10` INT(5) NOT NULL default '0',
		`ship_packagetype_10` VARCHAR(250) NOT NULL default '',
		`ship_pickuptype_10` VARCHAR(250) NOT NULL default '',
		`ship_fee_10` DOUBLE(17,2) NOT NULL default '0.00',
		`ship_fee_next_10` DOUBLE(17,2) NOT NULL default '0.00',
		`ship_additionalfee_10` DOUBLE(17,2) NOT NULL default '0.00',
		`freeshipping_10` INT(1) NOT NULL default '0',
		INDEX ( `destinationid` ),
		INDEX ( `project_id` ) 
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "projects_shipping_destinations</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "projects_shipping_regions");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "projects_shipping_regions (
		`project_id` INT UNSIGNED NOT NULL,
		`countryid` SMALLINT UNSIGNED NOT NULL DEFAULT '0',
		`row` TINYINT UNSIGNED NOT NULL,
		INDEX ( `project_id` ) 
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "projects_shipping_regions</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "project_answers");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "project_answers (
		`answerid` INT(10) NOT NULL AUTO_INCREMENT,
		`questionid` INT(10) NOT NULL default '0',
		`project_id` INT(10) NOT NULL default '0',
		`answer` MEDIUMTEXT,
		`optionid` INT(5) NOT NULL default '0',
		`date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`visible` INT(1) NOT NULL default '0',
		PRIMARY KEY  (`answerid`),
		INDEX ( `questionid` ),
		INDEX ( `project_id` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "project_answers</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "project_bids");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "project_bids (
		`bid_id` INT(100) NOT NULL AUTO_INCREMENT,
		`user_id` INT(100) NOT NULL default '0',
		`project_id` INT(100) NOT NULL default '0',
		`project_user_id` INT(100) NOT NULL default '0',
		`proposal` MEDIUMTEXT,
		`bidamount` DOUBLE(17,2) NOT NULL default '0.00',
		`salestax` DOUBLE(17,2) NOT NULL default '0.00',
		`salestaxstate` VARCHAR(250) NOT NULL default '',
		`salestaxrate` VARCHAR(10) NOT NULL default '0',
		`salestaxshipping` INT(1) NOT NULL default '0',
		`qty` INT(10) NOT NULL default '1',
		`estimate_days` INT(100) NOT NULL default '0',
		`date_added` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`date_updated` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`date_awarded` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`date_retracted` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`bidstatus` ENUM('placed','awarded','declined','choseanother','outbid') NOT NULL default 'placed',
		`bidstate` ENUM('','reviewing','wait_approval','shortlisted','invited','archived','expired','retracted') default '',
		`bidamounttype` ENUM('entire','hourly','daily','weekly','monthly','lot','item','weight') NOT NULL default 'entire',
		`bidcustom` VARCHAR(100) NOT NULL default '',
		`fvf` DOUBLE(17,2) NOT NULL default '0.00',
		`state` ENUM('service','product') NOT NULL default 'service',
		`isproxybid` INT(1) NOT NULL default '0',
		`isshortlisted` INT(1) NOT NULL default '0',
		`winnermarkedaspaid` INT(1) NOT NULL default '0',
		`winnermarkedaspaiddate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`winnermarkedaspaidmethod` MEDIUMTEXT,
		`buyerpaymethod` VARCHAR(250) NOT NULL default '',
		`buyershipcost` DOUBLE(17,2) NOT NULL default '0.00',
		`buyershipperid` INT(5) NOT NULL default '0',
		`sellermarkedasshipped` INT(1) NOT NULL default '0',
		`sellermarkedasshippeddate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`shiptracknumber` VARCHAR(250) NOT NULL default '',
		PRIMARY KEY (`bid_id`),
		INDEX (`project_id`),
		INDEX ( `user_id` ),
		INDEX ( `project_user_id` ),
		INDEX ( `bidstatus` ),
		INDEX ( `bidstate` ),
		INDEX ( `bidamounttype` ),
		INDEX ( `state` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "project_bids</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "project_realtimebids");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "project_realtimebids (
		`id` INT(100) NOT NULL AUTO_INCREMENT,
		`bid_id` INT(100) NOT NULL default '0',
		`user_id` INT(100) NOT NULL default '0',
		`project_id` INT(100) NOT NULL default '0',
		`project_user_id` INT(100) NOT NULL default '0',
		`proposal` MEDIUMTEXT,
		`bidamount` DOUBLE(17,2) NOT NULL default '0.00',
		`salestax` DOUBLE(17,2) NOT NULL default '0.00',
		`salestaxstate` VARCHAR(250) NOT NULL default '',
		`salestaxrate` VARCHAR(10) NOT NULL default '0',
		`salestaxshipping` INT(1) NOT NULL default '0',
		`qty` INT(10) NOT NULL default '1',
		`estimate_days` INT(100) NOT NULL default '0',
		`date_added` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`date_updated` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`date_awarded` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`date_retracted` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`bidstatus` ENUM('placed','awarded','declined','choseanother','outbid') NOT NULL default 'placed',
		`bidstate` ENUM('','reviewing','wait_approval','shortlisted','invited','archived','expired','retracted') default '',
		`bidamounttype` ENUM('entire','hourly','daily','weekly','monthly','lot','item','weight') NOT NULL default 'entire',
		`bidcustom` VARCHAR(100) NOT NULL default '',
		`fvf` DOUBLE(17,2) NOT NULL default '0.00',
		`state` ENUM('service','product') NOT NULL default 'service',
		`isproxybid` INT(1) NOT NULL default '0',
		`isshortlisted` INT(1) NOT NULL default '0',
		`winnermarkedaspaid` INT(1) NOT NULL default '0',
		`winnermarkedaspaiddate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`winnermarkedaspaidmethod` MEDIUMTEXT,
		`buyerpaymethod` VARCHAR(250) NOT NULL default '',
		`buyershipcost` DOUBLE(17,2) NOT NULL default '0.00',
		`buyershipperid` INT(5) NOT NULL default '0',
		`sellermarkedasshipped` INT(1) NOT NULL default '0',
		`sellermarkedasshippeddate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`shiptracknumber` VARCHAR(250) NOT NULL default '',
		PRIMARY KEY (`id`),
		INDEX (`bid_id`),
		INDEX (`project_id`),
		INDEX ( `user_id` ),
		INDEX ( `project_user_id` ),
		INDEX ( `bidstatus` ),
		INDEX ( `bidstate` ),
		INDEX ( `bidamounttype` ),
		INDEX ( `state` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "project_realtimebids</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "project_bid_retracts");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "project_bid_retracts (
		`retractid` INT(10) NOT NULL AUTO_INCREMENT,
		`user_id` INT(10) NOT NULL default '0',
		`bid_id` INT(10) NOT NULL default '0',
		`project_id` INT(10) NOT NULL default '0',
		`bidamount` DOUBLE(17,2) NOT NULL default '0.00',
		`reason` MEDIUMTEXT,
		`date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY  (`retractid`),
		INDEX ( `user_id` ),
		INDEX ( `bid_id` ),
		INDEX ( `project_id` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "project_bid_retracts</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "project_invitations");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "project_invitations (
		`id` INT(200) NOT NULL AUTO_INCREMENT,
		`project_id` INT(200) NOT NULL default '0',
		`buyer_user_id` INT(200) NOT NULL default '0',
		`seller_user_id` INT(200) NOT NULL default '0',
		`email` VARCHAR(100) NOT NULL default '',
		`name` VARCHAR(250) NOT NULL default '',
		`invite_message` MEDIUMTEXT,
		`date_of_invite` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`date_of_bid` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`date_of_remind` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`bid_placed` INT(1) NOT NULL default '0',
		PRIMARY KEY  (`id`),
		INDEX ( `project_id` ),
		INDEX ( `buyer_user_id` ),
		INDEX ( `seller_user_id` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "project_invitations</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "project_questions");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "project_questions (
		`questionid` INT(10) NOT NULL AUTO_INCREMENT,
		`cid` INT(10) NOT NULL default '0',
		`question_eng` MEDIUMTEXT,
		`description_eng` MEDIUMTEXT,
		`formname` VARCHAR(100) NOT NULL default '',
		`formdefault` VARCHAR(100) NOT NULL default '',
		`inputtype` ENUM('yesno','int','textarea','text','pulldown','multiplechoice','range','url') NOT NULL default 'text',
		`sort` INT(3) NOT NULL default '0',
		`visible` INT(1) NOT NULL default '1',
		`required` INT(1) NOT NULL default '0',
		`cansearch` INT(1) NOT NULL default '0',
		`canremove` INT(1) NOT NULL default '1',
		`recursive` INT(1) NOT NULL default '0',
		`guests` INT(1) NOT NULL default '1',
		PRIMARY KEY  (`questionid`),
		INDEX ( `cid` ),
		INDEX ( `inputtype` ),
		INDEX ( `visible` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "project_questions</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "project_questions_choices");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "project_questions_choices (
		`optionid` INT(10) NOT NULL AUTO_INCREMENT,
		`parentoptionid` INT(5) NOT NULL DEFAULT '0',
		`questionid` INT(5) NOT NULL DEFAULT '0',
		`choice_eng` MEDIUMTEXT,
		`sort` INT(3) NOT NULL DEFAULT '0',
		`visible` INT(1) NOT NULL DEFAULT '1',
		PRIMARY KEY (`optionid`),
		INDEX ( `parentoptionid` ),
		INDEX ( `questionid` ),
		INDEX ( `visible` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "project_questions_choices</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "proxybid");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "proxybid (
		`id` INT(10) NOT NULL AUTO_INCREMENT,
		`project_id` INT(11) NOT NULL default '0',
		`user_id` INT(11) NOT NULL default '0',
		`maxamount` DOUBLE(17,2) NOT NULL default '0.00',
		`date_added` DATETIME NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY  (`id`),
		INDEX ( `project_id` ),
		INDEX ( `user_id` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "proxybid</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "referral_clickthroughs");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "referral_clickthroughs (
		`rid` VARCHAR(20) default '',
		`date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`browser` VARCHAR(200) default '',
		`ipaddress` VARCHAR(50) default NULL,
		`referrer` MEDIUMTEXT,
		KEY `rid` (`rid`),
		INDEX ( `ipaddress` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "referral_clickthroughs</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "referral_data");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "referral_data (
		`id` INT(200) NOT NULL AUTO_INCREMENT,
		`user_id` INT(10) NOT NULL default '0',
		`referred_by` INT(10) NOT NULL default '0',
		`date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`postauction` INT(10) NOT NULL default '0',
		`awardauction` INT(10) NOT NULL default '0',
		`paysubscription` INT(10) NOT NULL default '0',
		`payfvf` INT(10) NOT NULL default '0',
		`payins` INT(10) NOT NULL default '0',
		`payportfolio` INT(10) NOT NULL default '0',
		`paycredentials` INT(10) NOT NULL default '0',
		`payenhancements` INT(10) NOT NULL default '0',
		`invoiceid` INT(10) NOT NULL default '0',
		`paidout` INT(1) NOT NULL default '0',
		PRIMARY KEY  (`id`),
		INDEX ( `user_id` ),
		INDEX ( `referred_by` ),
		INDEX ( `invoiceid` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "referral_data</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "register_answers");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "register_answers (
		`answerid` INT(10) NOT NULL AUTO_INCREMENT,
		`questionid` INT(10) NOT NULL default '0',
		`user_id` INT(10) NOT NULL default '0',
		`answer` MEDIUMTEXT,
		`date` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`visible` INT(1) NOT NULL default '0',
		PRIMARY KEY  (`answerid`),
		INDEX ( `questionid` ),
		INDEX ( `user_id` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "register_answers</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "register_questions");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "register_questions (
		`questionid` INT(10) NOT NULL AUTO_INCREMENT,
		`pageid` INT(5) NOT NULL default '0',
		`question_eng` MEDIUMTEXT,
		`description_eng` MEDIUMTEXT,
		`formname` VARCHAR(100) NOT NULL default '',
		`formdefault` VARCHAR(100) NOT NULL default '',
		`inputtype` ENUM('yesno','int','textarea','text','pulldown','multiplechoice','range') NOT NULL default 'text',
		`multiplechoice` MEDIUMTEXT,
		`sort` INT(3) NOT NULL default '0',
		`visible` INT(1) NOT NULL default '1',
		`required` INT(1) NOT NULL default '0',
		`profile` INT(1) NOT NULL default '1',
		`cansearch` INT(1) NOT NULL default '0',
		`guests` INT(1) NOT NULL default '0',
		`roleid` MEDIUMTEXT NOT NULL,
		PRIMARY KEY  (`questionid`),
		INDEX ( `pageid` ),
		INDEX ( `inputtype` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "register_questions</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "rssfeeds");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "rssfeeds (
		`rssid` INT( 10 ) NOT NULL AUTO_INCREMENT ,
		`rssname` VARCHAR( 200 ) NOT NULL default '',
		`rssurl` VARCHAR( 250 ) NOT NULL default '',
		`sort` INT( 50 ) NOT NULL default '0',
		PRIMARY KEY (`rssid`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "rssfeeds</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "search");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "search (
		`id` INT(100) NOT NULL AUTO_INCREMENT,
		`keyword` MEDIUMTEXT,
		`searchmode` MEDIUMTEXT,
		`cid` INT(5) NOT NULL DEFAULT '0',
		`visible` INT(1) NOT NULL DEFAULT '0',
		`count` INT(100) NOT NULL default '0',
		PRIMARY KEY  (`id`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "search</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "search_favorites");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "search_favorites (
		`searchid` INT(10) NOT NULL AUTO_INCREMENT,
		`user_id` INT(10) NOT NULL,
		`searchoptions` MEDIUMTEXT,
		`searchoptionstext` MEDIUMTEXT,
		`title` VARCHAR(200) NOT NULL,
		`cattype` ENUM('service','product','experts','stores','wantads') NOT NULL default 'service',
		`subscribed` INT(1) NOT NULL default '0',
		`added` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`lastsent` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`lastseenids` MEDIUMTEXT,
		PRIMARY KEY  (`searchid`),
		INDEX ( `user_id` ),
		INDEX ( `cattype` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "search_favorites</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "search_users");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "search_users (
		`id` INT(100) NOT NULL AUTO_INCREMENT,
		`user_id` INT(10) NOT NULL,
		`project_id` INT(10) NOT NULL,
		`cid` INT(5) NOT NULL DEFAULT '0',
		`keyword` MEDIUMTEXT,
		`searchmode` MEDIUMTEXT,
		`added` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`ipaddress` VARCHAR(25) NOT NULL DEFAULT '',
		`uservisible` INT(1) NOT NULL DEFAULT '1',
		PRIMARY KEY  (`id`),
		INDEX ( `user_id` ),
		INDEX ( `project_id` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "search_users</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "sessions");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "sessions (
		`sesskey` VARCHAR(32) NOT NULL default '',
		`expiry` INT(11) NOT NULL default '0',
		`value` MEDIUMTEXT,
		`userid` INT(11) NOT NULL default '0',
		`isuser` INT(1) NOT NULL default '0',
		`isadmin` INT(1) NOT NULL default '0',
		`isrobot` INT(1) NOT NULL default '0',
		`iserror` INT(1) NOT NULL default '0',
                `languageid` INT(1) NOT NULL default '0',
		`styleid` INT(1) NOT NULL default '0',
		`agent` MEDIUMTEXT,
		`ipaddress` VARCHAR(25) NOT NULL default '',
		`url` TEXT NOT NULL,
		`title` MEDIUMTEXT,
		`firstclick` VARCHAR(50) NOT NULL default '',
		`lastclick` VARCHAR(50) NOT NULL default '',
		`browser` VARCHAR(50) NOT NULL default 'unknown',
		`token` VARCHAR(32) NOT NULL default '',
		`sesskeyapi` VARCHAR(250) NOT NULL default '',
		`siteid` VARCHAR(20) NOT NULL default '001',
		PRIMARY KEY  (`sesskey`),
		INDEX ( `userid` ),
		INDEX ( `ipaddress` ),
		INDEX ( `token` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "sessions</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "shipping_rates_cache");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "shipping_rates_cache (
		`id` int(10) NOT NULL auto_increment,
		`carrier` VARCHAR(250) NOT NULL default '',
		`shipcode` VARCHAR(250) NOT NULL default '',
		`from_country` VARCHAR(250) NOT NULL default '',
		`from_state` VARCHAR(250) NOT NULL default '',
		`from_city` VARCHAR(250) NOT NULL default '',
		`from_zipcode` VARCHAR(250) NOT NULL default '',
		`to_country` VARCHAR(250) NOT NULL default '',
		`to_state` VARCHAR(250) NOT NULL default '',
		`to_city` VARCHAR(250) NOT NULL default '',
		`to_zipcode` VARCHAR(250) NOT NULL default '',
		`weight` DOUBLE NOT NULL default '1.0',
		`weightunit` VARCHAR(20) NOT NULL default '',
		`dimensionunit` VARCHAR(20) NOT NULL default '',
		`ounces` DOUBLE NOT NULL default '0',
		`container` VARCHAR(250) NOT NULL default '',
		`size` VARCHAR(250) NOT NULL default '',
		`machinable` VARCHAR(250) NOT NULL default '',
		`length` INT(5) NOT NULL default '0',
		`width` INT(5) NOT NULL default '0',
		`height` INT(5) NOT NULL default '0',
		`pickuptype` VARCHAR(250) NOT NULL default '',
		`packagetype` VARCHAR(250) NOT NULL default '',
		`datetime` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`gatewayrequest` MEDIUMTEXT,
		`gatewayresult` MEDIUMTEXT,
		`traffic` INT(5) NOT NULL default '1',
		INDEX ( `id` ),
		INDEX ( `carrier` ),
		INDEX ( `from_country` ),
		INDEX ( `from_zipcode` ),
		INDEX ( `to_country` ),
		INDEX ( `to_zipcode` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "shipping_rates_cache</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "skills");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "skills (
		`cid` int(100) NOT NULL auto_increment,
		`parentid` int(100) NOT NULL default '0',
		`level` int(5) NOT NULL default '1',
		`rootcid` int(5) NOT NULL default '0',
		`title_eng` mediumtext,
		`description_eng` mediumtext,
		`seourl_eng` mediumtext,
		`views` int(100) NOT NULL default '0',
		`keywords` mediumtext,
		`visible` int(1) NOT NULL default '1',
		`sort` int(3) NOT NULL default '0',
		PRIMARY KEY  (`cid`),
		INDEX ( `parentid` ),
		INDEX ( `level` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "skills</li>";
	
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "skills
		(`cid`, `parentid`, `level`, `rootcid`, `title_eng`, `description_eng`, `seourl_eng`, `views`, `keywords`, `visible`, `sort`)
		VALUES
		(1, 0, 1, 2, 'Programming', 'Programming', 'programming', 0, 'Programming', 1, 0),
		(2, 1, 2, 2, 'AJAX', 'AJAX', 'ajax', 0, 'AJAX', 1, 0),
		(3, 1, 2, 2, 'ASP', 'ASP', 'asp', 0, 'ASP', 1, 0),
		(4, 1, 2, 2, 'ASP.NET+ADO', 'ASP.NET+ADO', 'asp-net-ado', 0, 'ASP.NET+ADO', 1, 0),
		(5, 1, 2, 2, 'ActiveX', 'ActiveX', 'activex', 0, 'ActiveX', 1, 0),
		(6, 1, 2, 2, 'Adobe Flex', 'Adobe Flex', 'adobe-flex', 0, 'Adobe Flex', 1, 0),
		(7, 1, 2, 2, 'Assembler', 'Assembler', 'assembler', 0, 'Assembler', 1, 0),
		(8, 1, 2, 2, 'Borland C++ Builder', 'Borland C++ Builder', 'borland-c-builder', 0, 'Borland C++ Builder', 1, 0),
		(9, 1, 2, 2, 'C#/.Net', 'C#/.Net', 'c-net', 0, 'C#/.Net', 1, 0),
		(10, 1, 2, 2, 'C/C++/Unix', 'C/C++/Unix', 'c-unix', 0, 'C/C++/Unix', 1, 0),
		(11, 1, 2, 2, 'C/C++/Win32SDK', 'C/C++/Win32SDK', 'c-wind32sdk', 0, 'C/C++/Win32SDK', 1, 0),
		(12, 1, 2, 2, 'CSS', 'CSS', 'css', 0, 'CSS', 1, 0),
		(13, 1, 2, 2, 'CodeWarrior/C++', 'CodeWarrior/C++', 'codewarrior', 0, 'CodeWarrior/C++', 1, 0),
		(14, 1, 2, 2, 'ColdFusion', 'ColdFusion', 'coldfusion', 0, 'ColdFusion', 1, 0),
		(15, 1, 2, 2, 'Crystal Reports', 'Crystal Reports', 'crystal-reports', 0, 'Crystal Reports', 1, 0),
		(16, 1, 2, 2, 'Delphi', 'Delphi', 'delphi', 0, 'Delphi', 1, 0),
		(17, 1, 2, 2, 'Delphi/VB', 'Delphi/VB', 'delphi-vb', 0, 'Delphi/VB', 1, 0),
		(18, 1, 2, 2, 'Driver development', 'Driver development', 'driver-development', 0, 'Driver development', 1, 0),
		(19, 1, 2, 2, 'Flash/ActionScript', 'Flash/ActionScript', 'flash-actionscript', 0, 'Flash/ActionScript', 1, 0),
		(20, 1, 2, 2, 'FoxPro', 'FoxPro', 'foxpro', 0, 'FoxPro', 1, 0),
		(21, 1, 2, 2, 'GTK programming', 'GTK programming', 'gtk-programming', 0, 'GTK programming', 1, 0),
		(22, 1, 2, 2, 'Games/Windows', 'Games/Windows', 'games-windows', 0, 'Games/Windows', 1, 0),
		(23, 1, 2, 2, 'HTML/DHTML', 'HTML/DHTML', 'html-dhtml', 0, 'HTML/DHTML', 1, 0),
		(24, 1, 2, 2, 'Hyperion', 'Hyperion', 'hyperion', 0, 'Hyperion', 1, 0),
		(25, 1, 2, 2, 'IntelliJ IDEA', 'IntelliJ IDEA', 'intellij', 0, 'IntelliJ IDEA', 1, 0),
		(26, 1, 2, 2, 'J2EE', 'J2EE', 'j2ee', 0, 'J2EE', 1, 0),
		(27, 1, 2, 2, 'JBoss', 'JBoss', 'jboss', 0, 'JBoss', 1, 0),
		(28, 1, 2, 2, 'JFC', 'JFC', 'jfc', 0, 'JFC', 1, 0),
		(29, 1, 2, 2, 'JSP', 'JSP', 'jsp', 0, 'JSP', 1, 0),
		(30, 1, 2, 2, 'JavaScript', 'JavaScript', 'javascript', 0, 'JavaScript', 1, 0),
		(31, 1, 2, 2, 'Kylix', 'Kylix', 'kylix', 0, 'Kylix ', 1, 0),
		(32, 1, 2, 2, 'LaTeX', 'LaTeX', 'latex', 0, 'LaTeX', 1, 0),
		(33, 1, 2, 2, 'Lingo', 'Lingo', 'lingo', 0, 'Lingo', 1, 0),
		(34, 1, 2, 2, 'Mason', 'Mason', 'mason', 0, 'Mason', 1, 0),
		(35, 1, 2, 2, 'OCX', 'OCX', 'ocx', 0, 'OCX', 1, 0),
		(36, 1, 2, 2, 'PHP', 'PHP', 'php', 0, 'PHP', 1, 0),
		(37, 1, 2, 2, 'PHP/HTML/DHTML', 'PHP/HTML/DHTML', 'php-html-dhtml', 0, 'PHP/HTML/DHTML', 1, 0),
		(38, 1, 2, 2, 'PHP/IIS/MS SQL', 'PHP/IIS/MS SQL', 'php-iis-mssql', 0, 'PHP/IIS/MS SQL', 1, 0),
		(39, 1, 2, 2, 'PHP/MySQL', 'PHP/MySQL', 'php-mysql', 0, 'PHP/MySQL', 1, 0),
		(40, 1, 2, 2, 'Perl', 'Perl', 'perl', 0, 'Perl', 1, 0),
		(41, 1, 2, 2, 'Python', 'Python', 'python', 0, 'Python', 1, 0),
		(42, 1, 2, 2, 'Qt', 'Qt', 'qt', 0, 'Qt', 1, 0),
		(43, 1, 2, 2, 'Remoting', 'Remoting', 'remoting', 0, 'Remoting', 1, 0),
		(44, 1, 2, 2, 'Resin', 'Resin', 'resin', 0, 'Resin', 1, 0),
		(45, 1, 2, 2, 'Ruby', 'Ruby', 'ruby', 0, 'Ruby', 1, 0),
		(46, 1, 2, 2, 'SOAP', 'SOAP', 'soap', 0, 'SOAP', 1, 0),
		(47, 1, 2, 2, 'SatelliteForms', 'SatelliteForms', 'satelliteforms', 0, 'SatelliteForms', 1, 0),
		(48, 1, 2, 2, 'Smarty', 'Smarty', 'smarty', 0, 'Smarty', 1, 0),
		(49, 1, 2, 2, 'Struts', 'Struts', 'struts', 0, 'Struts', 1, 0),
		(50, 1, 2, 2, 'SyncML', 'SyncML', 'syncml', 0, 'SyncML', 1, 0),
		(51, 1, 2, 2, 'TCP/IP', 'TCP/IP', 'tcpip', 0, 'TCP/IP', 1, 0),
		(52, 1, 2, 2, 'Tomcat', 'Tomcat', 'tomcat', 0, 'Tomcat', 1, 0),
		(53, 1, 2, 2, 'Unix Shell', 'Unix Shell', 'unix-shell', 0, 'Unix Shell', 1, 0),
		(54, 1, 2, 2, 'VB/.NET', 'VB/.NET', 'vb-net', 0, 'VB/.NET', 1, 0),
		(55, 1, 2, 2, 'VB/Delphi', 'VB/Delphi', 'vb-delphi', 0, 'VB/Delphi', 1, 0),
		(56, 1, 2, 2, 'VB/Delphi/ASP/IIS', 'VB/Delphi/ASP/IIS', 'vb-delphi-asp-iis', 0, 'VB/Delphi/ASP/IIS', 1, 0),
		(57, 1, 2, 2, 'VBA', 'VBA', 'vba', 0, 'VBA', 1, 0),
		(58, 1, 2, 2, 'Visual Basic ', 'Visual Basic ', 'visual-basic', 0, 'Visual Basic ', 1, 0),
		(59, 1, 2, 2, 'VoiceXML', 'VoiceXML', 'voicexml', 0, 'VoiceXML', 1, 0),
		(60, 1, 2, 2, 'WML/WMLScript', 'WML/WMLScript', 'wml-wmlscript', 0, 'WML/WMLScript', 1, 0),
		(61, 1, 2, 2, 'WordPress', 'WordPress', 'wordpress', 0, 'WordPress', 1, 0),
		(62, 1, 2, 2, 'XML', 'XML', 'xml', 0, 'XML', 1, 0),
		(63, 1, 2, 2, 'XML-RPC', 'XML-RPC', 'xml-rpc', 0, 'XML-RPC', 1, 0),
		(64, 1, 2, 2, 'XUL', 'XUL', 'xul', 0, 'XUL', 1, 0),
		(65, 1, 2, 2, 'Zope/Python', 'Zope/Python', 'zope-phython', 0, 'Zope/Python', 1, 0),
		(66, 0, 1, 2, 'Databases', 'Databases', 'databases', 0, 'Databases', 1, 0),
		(67, 66, 2, 2, 'Access', 'Access', 'access', 0, 'Access', 1, 0),
		(68, 66, 2, 2, 'Cobol', 'Cobol', 'cobol', 0, 'Cobol', 1, 0),
		(69, 66, 2, 2, 'Filemaker Pro', 'Filemaker Pro', 'filemaker-pro', 0, 'Filemaker Pro', 1, 0),
		(70, 66, 2, 2, 'Informix', 'Informix', 'informix', 0, 'Informix', 1, 0),
		(71, 66, 2, 2, 'InterBase', 'InterBase', 'interbase', 0, 'InterBase', 1, 0),
		(72, 66, 2, 2, 'MS-SQL', 'MS-SQL', 'ms-sql', 0, 'MS-SQL', 1, 0),
		(73, 66, 2, 2, 'MySQL', 'MySQL', 'mysql', 0, 'MySQL', 1, 0),
		(74, 66, 2, 2, 'Oracle DBA', 'Oracle DBA', 'oracle-dba', 0, 'Oracle DBA', 1, 0),
		(75, 66, 2, 2, 'Oracle Forms', 'Oracle Forms', 'oracle-forms', 0, 'Oracle Forms', 1, 0),
		(76, 66, 2, 2, 'Oracle PL/SQL', 'Oracle PL/SQL', 'oracle-pl-sql', 0, 'Oracle PL/SQL', 1, 0),
		(77, 66, 2, 2, 'Oracle Reports', 'Oracle Reports', 'oracle-reports', 0, 'Oracle Reports', 1, 0),
		(78, 66, 2, 2, 'PostgreSQL', 'PostgreSQL', 'postgresql', 0, 'PostgreSQL', 1, 0),
		(79, 66, 2, 2, 'SQL', 'SQL', 'sql', 0, 'SQL', 1, 0),
		(80, 66, 2, 2, 'SQLite', 'SQLite', 'sqlite', 0, 'SQLite', 1, 0),
		(81, 66, 2, 2, 'Sybase', 'Sybase', 'sybase', 0, 'Sybase', 1, 0),
		(82, 0, 1, 2, 'Mobile', 'Mobile', 'mobile', 0, 'Mobile', 1, 0),
		(83, 82, 2, 2, 'Blackberry/RIM', 'Blackberry/RIM', 'blackberry-rim', 0, 'Blackberry/RIM', 1, 0),
		(84, 82, 2, 2, 'J2ME', 'J2ME', 'j2me', 0, 'J2ME', 1, 0),
		(85, 82, 2, 2, 'PalmOS', 'PalmOS', 'palmos', 0, 'PalmOS', 1, 0),
		(86, 82, 2, 2, 'PocketPC', 'PocketPC', 'pocketpc', 0, 'PocketPC', 1, 0),
		(87, 82, 2, 2, 'Symbian SDK', 'Symbian SDK', 'symbian-sdk', 0, 'Symbian SDK', 1, 0),
		(88, 0, 1, 1, 'Design/Graphics', 'Design/Graphics', 'design-graphics', 0, 'Design/Graphics', 1, 0),
		(89, 88, 2, 1, '3D Design', '3D Design', '3d-design', 0, '3D Design', 1, 0),
		(90, 88, 2, 1, 'Design/Flash', 'Design/Flash', 'design-flash', 0, 'Design/Flash', 1, 0),
		(91, 88, 2, 1, 'Flash/Macromedia', 'Flash/Macromedia', 'flash-macromedia', 0, 'Flash/Macromedia', 1, 0),
		(92, 88, 2, 1, 'Graphics', 'Graphics', 'graphics', 0, 'Graphics', 1, 0),
		(93, 88, 2, 1, 'Macromedia Director', 'Macromedia Director', 'macromedia-director', 0, 'Macromedia Director', 1, 0),
		(94, 88, 2, 1, 'Photoshop', 'Photoshop', 'photoshops', 0, 'Photoshop', 1, 0),
		(95, 88, 2, 1, 'QNX', 'QNX', 'qnx', 0, 'QNX', 1, 0),
		(96, 88, 2, 1, 'UI Design', 'UI Design', 'ui-design', 0, 'UI Design', 1, 0),
		(97, 88, 2, 1, 'Video Streaming', 'Video Streaming', 'video-streaming', 0, 'Video Streaming', 1, 0),
		(98, 0, 1, 2, 'Systems Admin', 'Systems Admin', 'system-admin', 0, 'Systems Admin', 1, 0),
		(99, 98, 2, 2, 'AS/400', 'AS/400', 'as-400', 0, 'AS/400', 1, 0),
		(100, 98, 2, 2, 'LAMP administration', 'LAMP administration', 'lamp-administration', 0, 'LAMP administration ', 1, 0),
		(101, 98, 2, 2, 'Mac OS X', 'Mac OS X', 'mac-osx', 0, 'Mac OS X', 1, 0),
		(102, 98, 2, 2, 'Windows Administration', 'Windows Administration', 'windows-administration', 0, 'Windows Administration', 1, 0),
		(103, 0, 1, 2, 'Application Servers', 'Application Servers', 'application-servers', 0, 'Application Servers', 1, 0),
		(104, 103, 2, 2, 'Asterisk', 'Asterisk', 'asterisk', 0, 'Asterisk', 1, 0),
		(105, 103, 2, 2, 'Lotus Domino', 'Lotus Domino', 'lotus-domino', 0, 'Lotus Domino', 1, 0),
		(106, 103, 2, 2, 'Lotus Notes', 'Lotus Notes', 'lotus-notes', 0, 'Lotus Notes', 1, 0),
		(107, 103, 2, 2, 'MS Navision', 'MS Navision', 'ms-navision', 0, 'MS Navision', 1, 0),
		(108, 103, 2, 2, 'Oracle Application Server', 'Oracle Application Server', 'oracle-application-server', 0, 'Oracle Application Server', 1, 0),
		(109, 103, 2, 2, 'OsCommerce', 'OsCommerce', 'oscommerce', 0, 'OsCommerce', 1, 0),
		(110, 103, 2, 2, 'Web Sphere', 'Web Sphere', 'web-sphere', 0, 'Web Sphere', 1, 0),
		(111, 103, 2, 2, 'WebLogic', 'WebLogic', 'weblogic', 0, 'WebLogic', 1, 0),
		(112, 0, 1, 2, 'Platforms', 'Platforms', 'platforms', 0, 'Platforms', 1, 0),
		(113, 112, 2, 2, 'DotNetNuke', 'DotNetNuke', 'dotnetnuke', 0, 'DotNetNuke', 1, 0),
		(114, 112, 2, 2, 'EDI', 'EDI', 'edi', 0, 'EDI', 1, 0),
		(115, 112, 2, 2, 'Hibernate', 'Hibernate', 'hibernate', 0, 'Hibernate', 1, 0),
		(116, 112, 2, 2, 'Joomla', 'Joomla', 'joomla', 0, 'Joomla', 1, 0),
		(117, 112, 2, 2, 'Mambo', 'Mambo', 'mambo', 0, 'Mambo', 1, 0),
		(118, 112, 2, 2, 'Online Payments', 'Online Payments', 'online-payments', 0, 'Online Payments', 1, 0),
		(119, 112, 2, 2, 'PowerBuilder', 'PowerBuilder', 'powerbuilder', 0, 'PowerBuilder', 1, 0),
		(120, 112, 2, 2, 'Sharepoint', 'Sharepoint', 'sharepoint', 0, 'Sharepoint', 1, 0),
		(121, 112, 2, 2, 'Voice/Windows', 'Voice/Windows', 'voice-windows', 0, 'Voice/Windows', 1, 0),
		(122, 112, 2, 2, 'Wireless', 'Wireless', 'wireless', 0, 'Wireless', 1, 0),
		(123, 112, 2, 2, 'phpNuke', 'phpNuke', 'phpnuke', 0, 'phpNuke', 1, 0),
		(124, 112, 2, 2, 'postNuke', 'postNuke', 'postnuke', 0, 'postNuke', 1, 0),
		(125, 0, 1, 2, 'Concepts', 'Concepts', 'concepts', 0, 'Concepts', 1, 0),
		(126, 125, 2, 2, 'Application Design', 'Application Design', 'application-design', 0, 'Application Design', 1, 0),
		(127, 125, 2, 2, 'Database Modeling', 'Database Modeling', 'database-modeling', 0, 'Database Modeling', 1, 0),
		(128, 125, 2, 2, 'Systems Programming', 'Systems Programming', 'systems-programming', 0, 'Systems Programming', 1, 0),
		(129, 125, 2, 2, 'UML', 'UML', 'uml', 0, 'UML', 1, 0),
		(130, 125, 2, 2, 'VoIP', 'VoIP', 'voip', 0, 'VoIP', 1, 0),
		(131, 0, 1, 2, 'Other', 'Other', 'other', 0, 'Other', 1, 0),
		(132, 131, 2, 2, 'Data Entry', 'Data Entry', 'data-entry', 0, 'Data Entry', 1, 0),
		(133, 131, 2, 2, 'Project Management', 'Project Management', 'project-management', 0, 'Project Management', 1, 0),
		(134, 131, 2, 2, 'QA', 'QA', 'qa', 0, 'QA', 1, 0),
		(135, 131, 2, 2, 'Recruiting', 'Recruiting', 'recruiting', 0, 'Recruiting', 1, 0),
		(136, 131, 2, 2, 'SEO', 'SEO', 'seo', 0, 'SEO', 1, 0),
		(137, 131, 2, 2, 'Search', 'Search', 'search', 0, 'Search', 1, 0),
		(138, 131, 2, 2, 'Tech Writer', 'Tech Writer', 'tech-writer', 0, 'Tech Writer', 1, 0),
		(139, 131, 2, 2, 'Testing', 'Testing', 'testing', 0, 'Testing', 1, 0)
	");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Imported 139 default skills (IT / Tech Related)</strong></li></ul>";
	flush();
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "skills_answers");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "skills_answers (
		`aid` INT(5) NOT NULL AUTO_INCREMENT,
		`cid` INT(5) NOT NULL,
		`user_id` INT(10) NOT NULL,
		PRIMARY KEY  (`aid`),
		INDEX ( `cid` ),
		INDEX ( `user_id` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "skills_answers</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "shippers");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "shippers (
		`shipperid` INT(5) NOT NULL AUTO_INCREMENT,
		`title` MEDIUMTEXT,
		`shipcode` VARCHAR(250) NOT NULL,
		`domestic` INT(1) NOT NULL default '1',
		`international` INT(1) NOT NULL default '0',
		`carrier` VARCHAR(250) NOT NULL,
		`trackurl` MEDIUMTEXT,
		`sort` INT NOT NULL DEFAULT '10',
		PRIMARY KEY  (`shipperid`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "shippers</li>";
	
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "shippers
		(`shipperid`, `title`, `shipcode`, `domestic`, `international`, `carrier`, `trackurl`)
		VALUES
		(NULL, 'Express Saver', 'FEDEX_EXPRESS_SAVER', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
		(NULL, 'Ground', 'FEDEX_GROUND', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
		(NULL, '2 Day', 'FEDEX_2_DAY', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
		(NULL, 'First Overnight', 'FIRST_OVERNIGHT', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
		(NULL, 'Ground Home Delivery', 'GROUND_HOME_DELIVERY', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
		(NULL, 'Priority Overnight', 'PRIORITY_OVERNIGHT', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
		(NULL, 'Smart Post', 'SMART_POST', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
		(NULL, 'Standard Overnight', 'STANDARD_OVERNIGHT', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
		(NULL, 'Freight', 'FEDEX_FREIGHT', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
		(NULL, '1 Day Freight', 'FEDEX_1_DAY_FREIGHT', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
		(NULL, '2 Day Freight', 'FEDEX_2_DAY_FREIGHT', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
		(NULL, '3 Day Freight', 'FEDEX_3_DAY_FREIGHT', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
		(NULL, 'National Freight', 'FEDEX_NATIONAL_FREIGHT', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
		(NULL, 'International Economy', 'INTERNATIONAL_ECONOMY', 0, 1, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
		(NULL, 'International Economy Freight', 'INTERNATIONAL_ECONOMY_FREIGHT', 0, 1, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
		(NULL, 'International First', 'INTERNATIONAL_FIRST', 0, 1, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
		(NULL, 'International Priority', 'INTERNATIONAL_PRIORITY', 0, 1, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
		(NULL, 'International Priority Freight', 'INTERNATIONAL_PRIORITY_FREIGHT', 0, 1, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
		(NULL, 'Europe First International Priority', 'EUROPE_FIRST_INTERNATIONAL_PRIORITY', 0, 1, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
		(NULL, 'Ground', '03', 1, 0, 'ups', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums='),
		(NULL, '3-Day Select', '12', 1, 0, 'ups', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums='),
		(NULL, '2nd Day Air', '02', 1, 0, 'ups', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums='),
		(NULL, 'Next Day Air Saver', '13', 1, 0, 'ups', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums='),
		(NULL, 'Next Day Air Early AM', '14', 1, 0, 'ups', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums='),
		(NULL, 'Next Day Air', '01', 1, 0, 'ups', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums='),
		(NULL, 'Worldwide Express', '07', 1, 1, 'ups', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums='),
		(NULL, 'Worldwide Expedited', '08', 1, 1, 'ups', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums='),
		(NULL, 'Standard', '11', 1, 0, 'ups', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums='),
		(NULL, 'Next Day Air Saver', '13', 1, 0, 'ups', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums='),
		(NULL, '2nd Day Air Early AM', '59', 1, 0, 'ups', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums='),
		(NULL, 'Worldwide Express Plus', '54', 1, 1, 'ups', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums='),
		(NULL, 'Express Saver', '65', 1, 0, 'ups', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums='),
		(NULL, 'Express Mail', 'EXPRESS', 1, 0, 'usps', 'http://trkcnfrm1.smi.usps.com/PTSInternetWeb/InterLabelInquiry.do?origTrackNum='),
		(NULL, 'First Class Mail', 'FIRST CLASS', 1, 0, 'usps', 'http://trkcnfrm1.smi.usps.com/PTSInternetWeb/InterLabelInquiry.do?origTrackNum='),
		(NULL, 'Priority Mail', 'PRIORITY', 1, 0, 'usps', 'http://trkcnfrm1.smi.usps.com/PTSInternetWeb/InterLabelInquiry.do?origTrackNum='),
		(NULL, 'Parcel Mail', 'PARCEL', 1, 0, 'usps', 'http://trkcnfrm1.smi.usps.com/PTSInternetWeb/InterLabelInquiry.do?origTrackNum='),
		(NULL, 'Library Mail', 'LIBRARY', 1, 0, 'usps', 'http://trkcnfrm1.smi.usps.com/PTSInternetWeb/InterLabelInquiry.do?origTrackNum='),
		(NULL, 'Bound Printed Matter', 'BPM', 1, 0, 'usps', 'http://trkcnfrm1.smi.usps.com/PTSInternetWeb/InterLabelInquiry.do?origTrackNum='),
		(NULL, 'Media Mail', 'MEDIA', 1, 0, 'usps', 'http://trkcnfrm1.smi.usps.com/PTSInternetWeb/InterLabelInquiry.do?origTrackNum=')
	");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Imported 33 various ship services (from UPS, FedEx &amp; USPS)</strong></li></ul>";
	flush();
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "stars");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "stars (
		`starid` INT(5) NOT NULL AUTO_INCREMENT,
		`pointsfrom` INT(5) NOT NULL default '0',
		`pointsto` INT(5) NOT NULL default '0',
		`icon` VARCHAR(20) NOT NULL default '',
		PRIMARY KEY (`starid`),
		INDEX ( `pointsfrom` ),
		INDEX ( `pointsto` ),
		INDEX ( `icon` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "stars</li>";
	
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "stars VALUES ('1', '0', '49', 'star1.gif')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "stars VALUES ('2', '50', '99', 'star2.gif')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "stars VALUES ('3', '100', '499', 'star3.gif')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "stars VALUES ('4', '500', '999', 'star4.gif')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "stars VALUES ('5', '1000', '4999', 'star4.gif')");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "stars VALUES ('6', '5000', '10000', 'star5.gif')");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Imported 6 levels of feedback rating stars</strong></li></ul>";
	flush();
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "styles");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "styles (
		`styleid` INT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
		`name` VARCHAR(100) NOT NULL default '',
		`filehash` CHAR(32) NOT NULL default '',
		`visible` INT(1) NOT NULL default '0',
		`sort` INT(3) NOT NULL default '0',
		PRIMARY KEY `styleid` (`styleid`),
		INDEX ( `name` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "styles</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "subscription");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "subscription (
		`subscriptionid` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
		`title_eng` VARCHAR(100) NOT NULL default '',
		`description_eng` VARCHAR(255) NOT NULL default '',
		`cost` DOUBLE(17,2) NOT NULL default '0.00',
		`length` VARCHAR(10) NOT NULL default '1',
		`units` ENUM('D','M','Y') NOT NULL default 'M',
		`subscriptiongroupid` INT(10) NOT NULL default '0',
		`roleid` INT(5) NOT NULL default '-1',
		`active` ENUM('yes','no') NOT NULL default 'no',
		`canremove` INT(1) NOT NULL default '1',
		`visible_registration` ENUM('0','1') DEFAULT '1',
		`visible_upgrade` ENUM('0','1') DEFAULT '1',
		`icon` VARCHAR(250) NOT NULL default 'images/default/icons/default.gif',
		`sort` INT(8) NOT NULL DEFAULT '0',
		`migrateto` INT(10) NOT NULL default '0',
		`migratelogic` ENUM('none','waived','unpaid','paid') NOT NULL default 'none',
		PRIMARY KEY  (`subscriptionid`),
		INDEX ( `title_eng` ),
		INDEX ( `subscriptiongroupid` ),
		INDEX ( `roleid` ),
		INDEX ( `active` ),
		INDEX ( `migrateto` ),
		INDEX ( `migratelogic` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "subscription</li>";
    
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "subscription (subscriptionid, title_eng, description_eng, cost, length, units, subscriptiongroupid, roleid, active, canremove, visible_registration, visible_upgrade, icon, migrateto, migratelogic)
		VALUES
		(NULL, 'Default Plan', 'Click view access to see subscription permissions', '0.00', '1', 'Y', '1', '1', 'yes', '0', '1', '1', 'images/default/icons/default.gif', '0', 'none')
	");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Imported default subscription plan (Default Plan)</strong></li></ul>";
	flush();
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "subscriptionlog");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "subscriptionlog (
		`subscriptionlogid` INT(200) NOT NULL AUTO_INCREMENT,
		`user_id` INT(100) NOT NULL default '0',
		`date_sent` DATE NOT NULL default '0000-00-00',
		PRIMARY KEY  (`subscriptionlogid`),
		INDEX ( `user_id` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "subscriptionlog</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "subscription_group");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "subscription_group (
		`subscriptiongroupid` SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT,
		`title_eng` VARCHAR(100) NOT NULL default '',
		`description_eng` VARCHAR(250) NOT NULL default '',
		`canremove` INT(1) NOT NULL default '1',
		PRIMARY KEY  (`subscriptiongroupid`),
		INDEX ( `title_eng` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "subscription_group</li>";
    
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "subscription_group
		VALUES
		(1, 'Default Permissions', 'Default permissions for the default subscription plan', 0)
	");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Imported default subscription permissions group (Default Permissions)</strong></li></ul>";
	flush();
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "subscription_permissions");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "subscription_permissions (
		`id` INT(5) NOT NULL AUTO_INCREMENT,
		`subscriptiongroupid` INT(2) NOT NULL default '1',
		`accessgroup` VARCHAR(250) NOT NULL default '',
		`accessname` VARCHAR(250) NOT NULL default '',
		`accesstype` ENUM('yesno','int') NOT NULL default 'yesno',
		`accessmode` ENUM('global','service','product') NOT NULL default 'global',
		`value` VARCHAR(250) NOT NULL default '',
		`canremove` INT(1) NOT NULL default '1',
		`original` INT(1) NOT NULL default '0',
		`iscustom` INT(1) NOT NULL default '1',
		`visible` INT(1) NOT NULL default '1',
		PRIMARY KEY  (`id`),
		INDEX ( `subscriptiongroupid` ),
		INDEX ( `accessname` ),
		INDEX ( `accesstype` ),
		INDEX ( `accessmode` ),
		INDEX ( `value` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "subscription_permissions</li>";
	
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "subscription_permissions 
		VALUES
		(NULL, 1, 'attachment', 'attachments', 'yesno', 'global', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'attachment', 'attachlimit', 'int', 'global', '1500000', 0, 1, 0, 1),
		(NULL, 1, 'attachment', 'uploadlimit', 'int', 'global', '1500000', 0, 1, 0, 1),
		(NULL, 1, 'attachment', 'maxpmbattachments', 'int', 'global', '1', 0, 1, 0, 1),
		(NULL, 1, 'attachment', 'maxbidattachments', 'int', 'service', '5', 0, 1, 0, 1),
		(NULL, 1, 'attachment', 'maxprojectattachments', 'int', 'service', '5', 0, 1, 0, 1),
		(NULL, 1, 'attachment', 'maxprofileattachments', 'int', 'global', '1', 0, 1, 0, 1),
		(NULL, 1, 'attachment', 'maxportfolioattachments', 'int', 'service', '5', 0, 1, 0, 1),
		(NULL, 1, 'attachment', 'bulkattachlimit', 'int', 'product', '10000000', 0, 1, 0, 1),
		(NULL, 1, 'attachment', 'addportfolio', 'yesno', 'service', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'accounting', 'deposit', 'yesno', 'global', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'accounting', 'withdraw', 'yesno', 'global', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'accounting', 'addcreditcard', 'yesno', 'global', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'accounting', 'delcreditcard', 'yesno', 'global', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'accounting', 'usecreditcard', 'yesno', 'global', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'accounting', 'addbankaccount', 'yesno', 'global', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'accounting', 'delbankaccount', 'yesno', 'global', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'accounting', 'usebankaccount', 'yesno', 'global', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'accounting', 'generateinvoices', 'yesno', 'service', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'accounting', 'enablecurrencyconversion', 'yesno', 'global', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'messaging', 'pmb', 'yesno', 'global', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'messaging', 'pmbtotal', 'int', 'global', '500', 0, 1, 0, 1),
		(NULL, 1, 'messaging', 'pmbcompose', 'yesno', 'global', 'no', 0, 1, 0, 1),
		(NULL, 1, 'listingbiddinglimits', 'auctiondelists', 'int', 'global', '1', 0, 1, 0, 1),
		(NULL, 1, 'listingbiddinglimits', 'bidretracts', 'int', 'global', '5', 0, 1, 0, 1),
		(NULL, 1, 'listingbiddinglimits', 'bidlimitperday', 'int', 'global', '15', 0, 1, 0, 1),
		(NULL, 1, 'listingbiddinglimits', 'bidlimitpermonth', 'int', 'global', '15', 0, 1, 0, 1),
		(NULL, 1, 'listingbiddinglimits', 'buynow', 'yesno', 'product', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'listingbiddinglimits', 'createserviceauctions', 'yesno', 'service', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'listingbiddinglimits', 'servicebid', 'yesno', 'service', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'listingbiddinglimits', 'createproductauctions', 'yesno', 'product', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'listingbiddinglimits', 'productbid', 'yesno', 'product', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'listingbiddinglimits', 'cansealbids', 'yesno', 'global', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'listingbiddinglimits', 'canblindbids', 'yesno', 'global', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'listingbiddinglimits', 'canfullprivacybids', 'yesno', 'global', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'other', 'searchresults', 'yesno', 'global', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'other', 'workshare', 'yesno', 'service', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'other', 'distance', 'yesno', 'global', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'other', 'updateprofile', 'yesno', 'global', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'other', 'maxprofilegroups', 'int', 'service', '5', 0, 1, 0, 1),
		(NULL, 1, 'other', 'newsletteropt_in', 'yesno', 'global', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'other', 'maxskillscat', 'int', 'service', '5', 0, 1, 0, 1),
		(NULL, 1, 'other', 'inviteprovider', 'yesno', 'service', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'other', 'addtowatchlist', 'yesno', 'global', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'other', 'iprestrict', 'yesno', 'global', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'other', 'createserviceprofile', 'yesno', 'service', 'yes', 0, 1, 0, 1),
		(NULL, 1, 'exemptions', 'fvfexempt', 'yesno', 'global', 'no', 0, 1, 0, 1),
		(NULL, 1, 'exemptions', 'insexempt', 'yesno', 'global', 'no', 0, 1, 0, 1),
		(NULL, 1, 'exemptions', 'payasgoexempt', 'yesno', 'global', 'no', 0, 1, 0, 1),
		(NULL, 1, 'exemptions', 'posthtml', 'yesno', 'global', 'no', 0, 1, 0, 1),
		(NULL, 1, 'exemptions', 'servicefvfgroup', 'int', 'service', '0', 0, 1, 0, 0),
		(NULL, 1, 'exemptions', 'productfvfgroup', 'int', 'product', '0', 0, 1, 0, 0),
		(NULL, 1, 'exemptions', 'serviceinsgroup', 'int', 'service', '0', 0, 1, 0, 0),
		(NULL, 1, 'exemptions', 'productinsgroup', 'int', 'product', '0', 0, 1, 0, 0)
	");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Imported default membership permissions</strong></li></ul>";
	flush();
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "subscription_permissions_groups");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "subscription_permissions_groups (
		`groupid` INT(5) NOT NULL AUTO_INCREMENT,
		`accessgroup` VARCHAR(250) NOT NULL default '',
		`original` INT(1) NOT NULL default '0',
		`visible` INT(1) NOT NULL default '1',
		PRIMARY KEY  (`groupid`),
		INDEX ( `accessgroup` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "subscription_permissions_groups</li>";
	
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "subscription_permissions_groups 
		VALUES
		(NULL, 'attachment', 1, 1),
		(NULL, 'accounting', 1, 1),
		(NULL, 'messaging', 1, 1),
		(NULL, 'listingbiddinglimits', 1, 1),
		(NULL, 'other', 1, 1),
		(NULL, 'exemptions', 1, 1)
	");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Imported default membership permission groups</strong></li></ul>";
	flush();
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "subscription_roles");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "subscription_roles (
		`roleid` INT(5) NOT NULL AUTO_INCREMENT,
		`purpose_eng` VARCHAR(250) NOT NULL default '',
		`title_eng` VARCHAR(250) NOT NULL default '',
		`custom` VARCHAR(200) NOT NULL default '',
		`roletype` ENUM('service','product','both') NOT NULL default 'service',
		`roleusertype` ENUM('servicebuyer','serviceprovider','productbuyer','merchantprovider','all') NOT NULL default 'servicebuyer',
		`active` INT(1) NOT NULL default '1',
		PRIMARY KEY  (`roleid`),
		INDEX ( `title_eng` ),
		INDEX ( `roletype` ),
		INDEX ( `roleusertype` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	echo "<li>" . DB_PREFIX . "subscription_roles</li>";
	flush();
	
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "subscription_roles VALUES (1, 'Create service auctions', 'Service Buyer', '[fbscore] [stars] [verified] [subscription]', 'service', 'servicebuyer', 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "subscription_roles VALUES (2, 'Bid on service auctions', 'Service Provider', '[fbscore] [stars] [store] [verified] [subscription]', 'service', 'serviceprovider', 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "subscription_roles VALUES (3, 'Sell your items and merchandise', 'Merchant Provider', '[fbscore] [stars] [store] [verified] [subscription]', 'product', 'merchantprovider', 1)");
	$ilance->db->query("INSERT INTO " . DB_PREFIX . "subscription_roles VALUES (4, 'Bid on items and merchandise', 'Product Buyer', '[fbscore] [stars] [verified] [subscription]', 'product', 'productbuyer', 1)");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Imported default subscription roles</strong></li></ul>";
	flush();
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "subscription_user");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "subscription_user (
		`id` INT(100) NOT NULL AUTO_INCREMENT,
		`subscriptionid` INT(10) NOT NULL default '1',
		`user_id` INT(100) NOT NULL default '-1',
		`paymethod` ENUM('account','bank','visa','amex','mc','disc','paypal','paypal_pro','check','cashu','moneybookers') NOT NULL default 'account',
		`startdate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`renewdate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`autopayment` INT(1) NOT NULL default '1',
		`autorenewal` ENUM('0','1') NOT NULL DEFAULT '1',
		`active` ENUM('yes','no','cancelled') NOT NULL default 'no',
		`cancelled` INT(1) NOT NULL default '0',
		`migrateto` INT(10) NOT NULL default '0',
		`migratelogic` ENUM('none','waived','unpaid','paid') NOT NULL default 'none',
		`recurring` INT(1) NOT NULL default '0',
		`invoiceid` INT(10) NOT NULL default '0',
		`roleid` INT(5) NOT NULL default '-1',
		PRIMARY KEY  (`id`),
		INDEX ( `subscriptionid` ),
		INDEX ( `user_id` ),
		INDEX ( `paymethod` ),
		INDEX ( `active` ),
		INDEX ( `migratelogic` ),
		INDEX ( `invoiceid` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "subscription_user</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "subscription_user_exempt");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "subscription_user_exempt (
		`exemptid` INT(100) NOT NULL AUTO_INCREMENT,
		`user_id` INT(10) NOT NULL default '0',
		`accessname` VARCHAR(250) NOT NULL default '',
		`value` VARCHAR(250) NOT NULL default '',
		`exemptfrom` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`exemptto` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`comments` MEDIUMTEXT,
		`invoiceid` INT(10) NOT NULL default '0',
		`active` INT(1) NOT NULL default '1',
		PRIMARY KEY  (`exemptid`),
		INDEX ( `user_id` ),
		INDEX ( `accessname` ),
		INDEX ( `value` ),
		INDEX ( `invoiceid` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "subscription_user_exempt</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "taxes");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "taxes (
		`taxid` INT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
		`taxlabel` VARCHAR( 255 ) NOT NULL default '',
		`state` VARCHAR( 20 ) NOT NULL default '',
		`countryname` VARCHAR( 50 ) NOT NULL default '',
		`countryid` INT( 10 ) NOT NULL default '0',
		`city` VARCHAR( 255 ) NOT NULL default '',
		`amount` VARCHAR( 10 ) NOT NULL default '0.0',
		`invoicetypes` MEDIUMTEXT,
		`entirecountry` INT( 1 ) NOT NULL default '0',
		PRIMARY KEY ( `taxid` ) ,
		KEY `taxlabel` ( `taxlabel` ) ,
		KEY `state` ( `state` ) ,
		KEY `countryname` ( `countryname` ) ,
		KEY `countryid` ( `countryid` ) ,
		KEY `city` ( `city` ) ,
		KEY `amount` ( `amount` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "taxes</li>";
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "templates");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "templates (
		`tid` INT(50) NOT NULL AUTO_INCREMENT,
		`name` VARCHAR(250) NOT NULL default '',
		`description` VARCHAR(250) NOT NULL default '',
		`original` MEDIUMTEXT,
		`content` MEDIUMTEXT,
		`type` ENUM('variable','cssclient','cssadmin','csswysiwyg','csstabs','csscommon','csscustom') NOT NULL default 'variable',
		`status` INT(1) NOT NULL default '0',
		`iscustom` ENUM('0', '1') NOT NULL DEFAULT '0',
		`createdate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`author` VARCHAR(50) NOT NULL default '',
		`request` VARCHAR(250) NOT NULL default '',
		`version` VARCHAR(10) NOT NULL default '1.0',
		`isupdated` INT(1) NOT NULL default '0',
		`updatedate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`styleid` INT(5) NOT NULL default '1',
		`product` VARCHAR(250) NOT NULL default 'ilance',
		`sort` INT(10) NOT NULL default '100',
		PRIMARY KEY  (`tid`),
		INDEX ( `name` ),
		INDEX ( `type` ),
		INDEX ( `styleid` ),
		INDEX ( `product` ),
		INDEX ( `sort` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "templates</li>";
    	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "users");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "users (
		`user_id` INT(100) NOT NULL AUTO_INCREMENT,
		`ipaddress` VARCHAR(25) NOT NULL default '',
		`iprestrict` INT(1) NOT NULL default '0',
		`username` VARCHAR(50) NOT NULL default '',
		`password` VARCHAR(32) NOT NULL default '',
		`salt` VARCHAR(5) NOT NULL default '',
		`secretquestion` VARCHAR(200) NOT NULL default '',
		`secretanswer` VARCHAR(32) NOT NULL default '',
		`email` VARCHAR(60) NOT NULL default '',
		`first_name` VARCHAR(100) NOT NULL default '',
		`last_name` VARCHAR(100) NOT NULL default '',
		`address` VARCHAR(200) NOT NULL default '',
		`address2` VARCHAR(200) default NULL,
		`city` VARCHAR(100) NOT NULL default '',
		`state` VARCHAR(100) NOT NULL default '',
		`zip_code` VARCHAR(10) NOT NULL default '',
		`phone` VARCHAR(20) NOT NULL default '',
		`country` INT(10) NOT NULL default '500',
		`date_added` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`subcategories` MEDIUMTEXT,
		`status` ENUM('active','suspended','cancelled','unverified','banned','moderated') NOT NULL default 'active',
		`serviceawards` INT(5) NOT NULL default '0',
		`productawards` INT(5) NOT NULL default '0',
		`servicesold` INT(5) NOT NULL default '0',
		`productsold` INT(5) NOT NULL default '0',
		`rating` DOUBLE NOT NULL default '0.00',
		`score` INT(5) NOT NULL default '0',
		`feedback` DOUBLE NOT NULL default '0',
		`bidstoday` INT(10) NOT NULL default '0',
		`bidsthismonth` INT(10) NOT NULL default '0',
		`auctiondelists` INT(5) NOT NULL default '0',
		`bidretracts` INT(5) NOT NULL default '0',
		`lastseen` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`dob` DATE NOT NULL default '0000-00-00',
		`rid` VARCHAR(10) NOT NULL default '',
		`account_number` VARCHAR(25) NOT NULL default '',
		`available_balance` DOUBLE(17,2) NOT NULL default '0.00',
		`total_balance` DOUBLE(17,2) NOT NULL default '0.00',
		`income_reported` DOUBLE(17,2) NOT NULL default '0.00',
		`income_spent` DOUBLE(17,2) NOT NULL default '0.00',
		`startpage` VARCHAR(250) NOT NULL default 'main',
		`styleid` INT(3) NOT NULL,
		`project_distance` INT(1) NOT NULL default '1',
		`currency_calculation` INT(1) NOT NULL default '1',
		`languageid` INT(3) NOT NULL,
		`currencyid` INT(3) NOT NULL,
		`timezone` VARCHAR(250) NOT NULL,
		`notifyservices` INT(1) NOT NULL,
		`notifyproducts` INT(1) NOT NULL,
		`notifyservicescats` MEDIUMTEXT,
		`notifyproductscats` MEDIUMTEXT,
		`lastemailservicecats` DATE NOT NULL default '0000-00-00',
		`lastemailproductcats` DATE NOT NULL default '0000-00-00',
		`displayprofile` INT(1) NOT NULL,
		`emailnotify` INT(1) NOT NULL,
		`displayfinancials` INT(1) NOT NULL,
		`vatnumber` VARCHAR(250) NOT NULL,
		`regnumber` VARCHAR(250) NOT NULL,
		`dnbnumber` VARCHAR(250) NOT NULL,
		`companyname` VARCHAR(100) NOT NULL,
		`usecompanyname` INT(1) NOT NULL,
		`timeonsite` INT(10) NOT NULL,
		`daysonsite` INT(10) NOT NULL,
		`isadmin` INT(1) NOT NULL default '0',
		`permissions` MEDIUMTEXT,
		`searchoptions` MEDIUMTEXT,
		`rateperhour` DOUBLE(17,2) NOT NULL default '0.00',
		`profilevideourl` MEDIUMTEXT,
		`profileintro` MEDIUMTEXT,
		`gender` ENUM('','male','female') NOT NULL default '',
		`freelancing` ENUM('','individual','business') NOT NULL default '',
		`autopayment` INT(1) NOT NULL default '1',
		`posthtml` INT(1) NOT NULL default '0',
		`username_history` MEDIUMTEXT,
		`password_lastchanged` DATETIME NOT NULL default '0000-00-00 00:00:00',		
		PRIMARY KEY (`user_id`),
		INDEX (`username`),
		INDEX (`email`),
		INDEX (`first_name`),
		INDEX (`last_name`),
		INDEX (`zip_code`),
		INDEX (`country`),
		INDEX (`rating`),
		INDEX (`city`),
		INDEX (`state`),
		INDEX (`status`),
		INDEX (`serviceawards`),
		INDEX (`score`),
		INDEX (`gender`),
		INDEX (`freelancing`)
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "users</li>";
	
	$salt = construct_password_salt(5);
	$password = md5(md5($_SESSION['admin_password']) . $salt);
	$account_number = rand(100, 999) . rand(100, 999) . rand(100, 999) . rand(100, 999) . rand(1, 9);	
	
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "users
		(user_id, username, password, salt, email, phone, isadmin, date_added, languageid, currencyid, account_number, styleid, currency_calculation, timezone, notifyservices, notifyproducts, displayprofile, emailnotify, displayfinancials, vatnumber, regnumber, dnbnumber, companyname, usecompanyname, timeonsite, daysonsite, posthtml)
		VALUES (
		NULL,
		'" . $ilance->db->escape_string($_SESSION['admin_username']) . "',
		'" . $ilance->db->escape_string($password) . "',
		'" . $ilance->db->escape_string($salt) . "',
		'" . $ilance->db->escape_string($_SESSION['admin_email']) . "',
		'1-111-111-1111',
		'1',
		NOW(),
		'1',
		'1',
		'" . $account_number . "',
		'1',
		'0',
		'America/New_York',
		'0',
		'0',
		'0',
		'1',
		'0',
		'0',
		'0',
		'0',
		'N/A',
		'0',
		'0',
		'0',
		'1')
	");
	echo "<ul style=\"list-style-type: circle; padding:0px; margin:0px; margin-left:35px;\"><li style=\"font-size:9px; color:#777\"><strong>Imported default admin user</strong></li></ul>";
	flush();
	
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "visits");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "visits (
		`vid` INT(100) NOT NULL AUTO_INCREMENT,
		`sesskey` VARCHAR(200) default '',
		`userid` INT(5) default '0',
		`firstdate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`lastdate` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`browser` VARCHAR(200) default '',
		`ipaddress` VARCHAR(50) default '',
		`referrer` MEDIUMTEXT,
		KEY `vid` (`vid`),
		INDEX ( `ipaddress` ),
		INDEX ( `userid` ),
		INDEX ( `sesskey` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "visits</li>";
    
	$ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "watchlist");
	$ilance->db->query("
		CREATE TABLE " . DB_PREFIX . "watchlist (
		`watchlistid` INT(100) NOT NULL AUTO_INCREMENT,
		`user_id` INT(100) NOT NULL default '0',
		`watching_user_id` INT(10) NOT NULL default '0',
		`watching_project_id` INT(100) NOT NULL default '0',
		`watching_category_id` INT(100) NOT NULL default '0',
		`comment` MEDIUMTEXT,
		`dateadded` DATETIME NOT NULL default '0000-00-00 00:00:00',
		`state` ENUM('sprovider','mprovider','buyer','auction','cat','subcat') NOT NULL default 'auction',
		`mode` ENUM('service','product') NOT NULL default 'product',
		`lowbidnotify` INT(1) NOT NULL default '0',
		`highbidnotify` INT(1) NOT NULL default '0',
		`hourleftnotify` INT(1) NOT NULL default '0',
		`subscribed` INT(1) NOT NULL default '0',
		PRIMARY KEY  (`watchlistid`),
		INDEX ( `user_id` ),
		INDEX ( `watching_user_id` ),
		INDEX ( `watching_project_id` ),
		INDEX ( `watching_category_id` ),
		INDEX ( `state` ),
		INDEX ( `mode` )
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "
	");
	flush();
	echo "<li>" . DB_PREFIX . "watchlist</li>";
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>