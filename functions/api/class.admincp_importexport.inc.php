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

if (!class_exists('admincp'))
{
	exit;
}

/**
* AdminCP importer handler for emails, language phrases and css styles within ILance.
*
* This class will allow better functionality whereby it can support command line import tools and/or
* import/export functionality from within the ILance Admin CP keeping code updated from a central place.
*
* @package      iLance\AdminCP\ImportExport
* @version      4.0.0.8059
* @author       ILance
*/
class admincp_importexport extends admincp
{
	/**
        * Function to import emails, phrases or css styles via XML
        *
        * @param       string       import what? [email, phrase or css]
        * @param       string       location/area we're performing the import from [admincp or commandline]
        * @param       string       location/file path to the file we're importing
        * @param       boolean      determines if we should not run print_notice(), print_action_success(), or print_action_failed() [default false, use true when using commandline argument]
        * @param       boolean      determines if we should skip the version checkup of the xml file being imported (default true)
        * @param       boolean      determines if we should overwrite our current phrases with phrases found from imported xml file (default false)
        * @param       boolean      determines if we should set the newly uploaded style XML file as the default marketplace style everyone sees (default false)
        * 
        * @return      boolean      Returns true or false
        */
        function import($what = '', $where = 'admincp', $xml = '', $slientmode = false, $noversioncheck = 1, $overwritephrases = 0, $setasdefault = 0)
        {
		global $ilance, $ilconfig, $phrase, $ilpage, $show, $notice;
		$notice = '';
		if (empty($what))
		{
			die('No import action [email, phrase or css] was specified.  Cannot continue.');
		}
		if (empty($where))
		{
			die('No import location [admincp or commandline] was specified.  Cannot continue.');
		}
		if (empty($xml))
		{
			die('No xml file to import was specified.  Cannot continue.');
		}
		$ilance->xml = construct_object('api.xml');
		switch ($what)
		{
			case 'email':
			{
				$xml_encoding = 'UTF-8';
				$xml_encoding = mb_detect_encoding($xml);
				if ($xml_encoding == 'ASCII') 
				{
					$xml_encoding = '';
				}
				$parser = xml_parser_create($xml_encoding);
				$data = array();
				xml_parse_into_struct($parser, $xml, $data);
				$error_code = xml_get_error_code($parser);
				xml_parser_free($parser);
				if ($error_code == 0)
				{
					$result = $ilance->xml->process_email_xml($data, $xml_encoding);
					if ($result['ilversion'] != $ilance->config['ilversion'] AND $noversioncheck == 0)
					{
						if ($slientmode == false)
						{
							print_action_failed('{_the_version_of_the_this_email_package_is_different_than} <strong>' . $ilance->config['ilversion'] . '</strong>.  {_the_operation_has_aborted_due_to_a_version_conflict}', $ilance->GPC['return']);
							exit();
						}
						else
						{
							return false;
						}
					}
					// check if language exists before importing xml file
					$query = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "language
						WHERE languagecode = '" . $ilance->db->escape_string($result['langcode']) . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($query) == 0)
					{
						if ($slientmode == false)
						{
							print_action_failed('{_were_sorry_email_pack_uploading_requires_the_actual_language_to_already_exist}', $ilance->GPC['return']);
							exit();
						}
						else
						{
							return false;
						}
					}
					$query2 = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "language
						WHERE languagecode = '" . $ilance->db->escape_string($result['langcode']) . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($query2) > 0)
					{
						$AllLanguages = $AllSubjects = array();                                        
						while ($row = $ilance->db->fetch_array($query2, DB_ASSOC))
						{
							$AllSubjects[] = 'subject_' . mb_substr($row['languagecode'], 0, 3);
							$AllMessages[] = 'message_' . mb_substr($row['languagecode'], 0, 3);
							$AllNames[] = 'name_' . mb_substr($row['languagecode'], 0, 3);
						}
						$phrasearray = $result['emailarray'];
						$lfn1 = 'subject_' . mb_substr($result['langcode'], 0, 3);
						$lfn2 = 'message_' . mb_substr($result['langcode'], 0, 3);
						$lfn3 = 'type';
						$lfn4 = 'varname';
						$lfn5 = 'name_' . mb_substr($result['langcode'], 0, 3);
						$newid = 0;                                                
						for ($i = 0; $i < count($phrasearray); $i++)
						{
							$product = isset($phrasearray[$i][5]) ? $phrasearray[$i][5] : 'ilance';
							$ishtml = isset($phrasearray[$i][11]) ? $phrasearray[$i][11] : 0;
							if ($ilance->db->num_rows($ilance->db->query("SELECT * FROM " . DB_PREFIX . "email WHERE varname = '" . $ilance->db->escape_string($phrasearray[$i][4]) . "' LIMIT 1", 0, null, __FILE__, __LINE__)) == 0)
							{
								// checks email subject for email text before new insert email
								if ($phrasearray[$i][4] != '')
								{
									$ilance->db->query("
										INSERT INTO " . DB_PREFIX . "email
										(`varname`)
										VALUES ('" . $ilance->db->escape_string($phrasearray[$i][4]) . "')
									", 0, null, __FILE__, __LINE__);
								}
								else
								{
									$notice .= "Error: Email template name '<strong>$phrasearray[$i][0]</strong>' could not be added due to a blank phrase existing within the xml file (near CDATA[])";
								}
								// if new email subject is not blank .. update proper field content
								if ($phrasearray[$i][1] != '') 
								{
									$ilance->db->query("
										UPDATE " . DB_PREFIX . "email 
										SET `subject_original` = '" . $ilance->db->escape_string($phrasearray[$i][1]) . "',
										`message_original` = '" . $ilance->db->escape_string($phrasearray[$i][2]) . "',
										`" . $lfn1 . "` = '" . $ilance->db->escape_string($phrasearray[$i][1]) . "',
										`" . $lfn2 . "` = '" . $ilance->db->escape_string($phrasearray[$i][2]) . "',
										`" . $lfn3 . "` = '" . $ilance->db->escape_string($phrasearray[$i][3]) . "',
										`" . $lfn4 . "` = '" . trim($ilance->db->escape_string($phrasearray[$i][4])) . "',
										`" . $lfn5 . "` = '" . trim($ilance->db->escape_string($phrasearray[$i][0])) . "',
										`product` = '" . trim($ilance->db->escape_string($product)) . "',
										`cansend` = '" . intval($phrasearray[$i][6]) . "',
										`departmentid` = '" . intval($phrasearray[$i][7]) . "',
										`buyer` = '" . intval($phrasearray[$i][8]) . "',
										`seller` = '" . intval($phrasearray[$i][9]) . "',
										`admin` = '" . intval($phrasearray[$i][10]) . "',
										`ishtml` = '" . intval($ishtml) . "'
										WHERE `varname` = '" . $ilance->db->escape_string($phrasearray[$i][4]) . "'
										LIMIT 1
									", 0, null, __FILE__, __LINE__);
								}
								else
								{
									$notice .= "Error: email: <strong>$phrasearray[$i][0]</strong> could not be added due to a blank email template existing within the xml file (near CDATA)";
								}
							}
							else
							{
								// 'name' exists .. update
								if ($phrasearray[$i][1] != '') 
								{
									$extraquery = '';
									if ($overwritephrases)
									{
										$extraquery .= "`" . $lfn1 . "` = '" . $ilance->db->escape_string($phrasearray[$i][1]) . "',";
										$extraquery .= "`" . $lfn2 . "` = '" . $ilance->db->escape_string($phrasearray[$i][2]) . "',";
										$extraquery .= "`" . $lfn5 . "` = '" . $ilance->db->escape_string($phrasearray[$i][0]) . "',";
										$ilance->db->query("
											UPDATE " . DB_PREFIX . "email 
											SET `subject_original` = '" . $ilance->db->escape_string($phrasearray[$i][1]) . "',
											`message_original` = '" . $ilance->db->escape_string($phrasearray[$i][2]) . "',
											$extraquery
											`" . $lfn3 . "` = '" . $ilance->db->escape_string($phrasearray[$i][3]) . "',
											`" . $lfn4 . "` = '" . trim($ilance->db->escape_string($phrasearray[$i][4])) . "',
											`product` = '" . trim($ilance->db->escape_string($product)) . "',
											`cansend` = '" . intval($phrasearray[$i][6]) . "',
											`departmentid` = '" . intval($phrasearray[$i][7]) . "',
											`buyer` = '" . intval($phrasearray[$i][8]) . "',
											`seller` = '" . intval($phrasearray[$i][9]) . "',
											`admin` = '" . intval($phrasearray[$i][10]) . "',
											`ishtml` = '" . intval($ishtml) . "'
											WHERE `varname` = '" . $ilance->db->escape_string($phrasearray[$i][4]) . "'
											LIMIT 1
										", 0, null, __FILE__, __LINE__);
									}
									else 
									{
										$extraquery = '';
										$extraquery .= "`" . $lfn1 . "` = '" . $ilance->db->escape_string($phrasearray[$i][1]) . "',";
										$extraquery .= "`" . $lfn2 . "` = '" . $ilance->db->escape_string($phrasearray[$i][2]) . "',";
										$extraquery .= "`" . $lfn5 . "` = '" . $ilance->db->escape_string($phrasearray[$i][0]) . "',";
										$ilance->db->query("
											UPDATE " . DB_PREFIX . "email 
											SET
											$extraquery
											`" . $lfn3 . "` = '" . $ilance->db->escape_string($phrasearray[$i][3]) . "',
											`" . $lfn4 . "` = '" . trim($ilance->db->escape_string($phrasearray[$i][4])) . "',
											`product` = '" . trim($ilance->db->escape_string($product)) . "',
											`cansend` = '" . intval($phrasearray[$i][6]) . "',
											`departmentid` = '" . intval($phrasearray[$i][7]) . "',
											`buyer` = '" . intval($phrasearray[$i][8]) . "',
											`seller` = '" . intval($phrasearray[$i][9]) . "',
											`admin` = '" . intval($phrasearray[$i][10]) . "',
											`ishtml` = '" . intval($ishtml) . "'
											WHERE `varname` = '" . $ilance->db->escape_string($phrasearray[$i][4]) . "'
											LIMIT 1
										", 0, null, __FILE__, __LINE__);
									}
								}
								else
								{
									$notice .= "Error: template: <strong>$phrasearray[$i][0]</strong> could not be added due to a blank template existing within the xml file (near CDATA[])";
								}
							}
						}
						if ($slientmode == false)
						{
							print_action_success('{_email_language_pack_importation_success}', $ilance->GPC['return']);
							exit();
						}
						else
						{
							return true;
						}
					}
					else 
					{
						if ($slientmode == false)
						{
							print_action_failed('{_were_sorry_this_language_does_not_exist}', $ilance->GPC['return']);
							exit();
						}
						else
						{
							return false;
						}
					}
				}
				else
				{
					$error_string = xml_error_string($error_code);
					if ($slientmode == false)
					{
						print_action_failed('{_were_sorry_there_was_an_error_with_the_formatting}' . ' <strong>' . $error_string . '</strong>.', $ilance->GPC['return']);
						exit();
					}
					else
					{
						return false;
					}
				}
				break;
			}
			case 'phrase':
			{
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
					$result = $ilance->xml->process_lang_xml($data, $xml_encoding);
					if ($result['illang_version'] != $ilance->config['ilversion'] AND $noversioncheck == 0)
					{
						if ($slientmode == false)
						{
							print_action_failed('{_the_version_of_the_this_language_xml_package_is_different_than_the_currently_installed_version} <strong><em>' . $ilance->config['ilversion'] . '</em></strong>.  {_the_operation_has_aborted_due_to_a_language_version_conflict}<br /><br />{_tip_you_can_click_the_checkbox_on_the_previous_page_to_ignore_language_version_conflicts_which_will_ultimately_bypass_this_version_checker}', $ilance->GPC['return']);
							exit();
						}
						else
						{
							return false;
						}
					}
					
					$query = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "language
						WHERE languagecode = '" . $result['lang_code'] . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($query) == 0)
					{
						if ($slientmode == false)
						{
							print_action_failed('{_were_sorry_the_language_package_being_uploaded_requires}', $ilance->GPC['return']);
							exit();
						}
						else
						{
							return false;
						}
					}
					
					// update language table with defaults in the xml file
					// since there may have been new settings or character encoding strings changed
					// with this specific import
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
					", 0, null, __FILE__, __LINE__);
					
					$AllLanguages = array();
					
					$query = $ilance->db->query("
						SELECT languagecode
						FROM " . DB_PREFIX . "language
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($query) > 0)
					{
						while ($row = $ilance->db->fetch_array($query, DB_ASSOC))
						{
							$AllLanguages[] = 'text_' . mb_substr($row['languagecode'], 0, 3);
						}
			    
						$lfn = 'text_' . mb_substr($result['lang_code'], 0, 3);
						$phrasearray = $result['phrasearray'];
						$phrasecount = count($phrasearray);
						for ($i = 0; $i < $phrasecount; $i++)
						{
							// does varname exist in the db?
							$varexist = $ilance->db->query("
								SELECT phrasegroup
								FROM " . DB_PREFIX . "language_phrases
								WHERE varname = '" . $ilance->db->escape_string($phrasearray[$i][1]) . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							if ($ilance->db->num_rows($varexist) == 0)
							{
								// varname DOES NOT exist for this language within the db
								// this must be a new phrase! let's add it into the database
								// so the admin has the new phrase and the ability to update this phrase
								// in their own language .. lets also add this new phrase to the original_text field
								// for future reverts
								if (!empty($phrasearray[$i][0]) AND !empty($phrasearray[$i][1]))
								{
									$ilance->db->query("
										INSERT INTO " . DB_PREFIX . "language_phrases
										(phrasegroup, varname, text_original)
										VALUES(
										'" . $ilance->db->escape_string($phrasearray[$i][0]) . "',
										'" . $ilance->db->escape_string($phrasearray[$i][1]) . "',
										'" . $ilance->db->escape_string($phrasearray[$i][2]) . "')
									", 0, null, __FILE__, __LINE__);
								}
								else
								{
									$notice .= "Notice: varname - <strong>$phrasearray[$i][1]</strong> for phrasegroup <strong>$phrasearray[$i][0]</strong> could not be added due to a blank phrase existing within the xml file (near CDATA[])";
								}
				    
								// since varname does not exist, update ALL languages with this one phrase
								// so we have it to translate later!
								foreach ($AllLanguages AS $value)
								{
									if (!empty($phrasearray[$i][1]) AND !empty($phrasearray[$i][2]))
									{
										// update the phrase for all installed languages
										// note: if the author of the package being uploaded is by ILance or ilance, set ismaster = '1'
										$ismastersql = '';
										if (strtolower($result['author']) == 'ilance')
										{
											$ismastersql = "ismaster = '1',";
										}
										
										$ilance->db->query("
											UPDATE " . DB_PREFIX . "language_phrases
											SET " . $ilance->db->escape_string($value) . " = '" . $ilance->db->escape_string($phrasearray[$i][2]) . "',
											$ismastersql
											isupdated = '0'
											WHERE varname = '" . $ilance->db->escape_string($phrasearray[$i][1]) . "'
											LIMIT 1
										", 0, null, __FILE__, __LINE__);
									}
									else
									{
										$notice .= "Notice: varname - <strong>$phrasearray[$i][1]</strong> for phrase group <strong>$phrasearray[$i][0]</strong> could not be added due to a blank phrase existing within the xml file (near CDATA[])";
									}
								}
							}
							else
							{
								// varname exists within the DB
								// update phrase.. but also make sure to change the phrase group
								// to the value of what is in the xml package being uploaded as team ilance
								// may have moved phrases around in this release or any future releases
								if (!empty($phrasearray[$i][0]) AND !empty($phrasearray[$i][1]) AND !empty($phrasearray[$i][2]))
								{
									// ilance software original phrase text is based on English US
									// if this language pack being uploaded is english, be sure to set the
									// 'text_original' to the phrase text in this uploaded xml file
									// if not, skip this field from being updated
									$updateoriginaltext = $ismastersql = '';
									
									if ($result['lang_code'] == 'english' AND strtolower($result['author']) == 'ilance')
									{
										// this is the official English (US) xml language package produced by
										// ILance so let's be sure to update this phrases original_text
										// with that of the xml phrase currently being processed
										$updateoriginaltext = "text_original = '" . $ilance->db->escape_string($phrasearray[$i][2]) . "',";
										$ismastersql = "ismaster = '1',";
									}
									
									if ($overwritephrases == 0)
									{
										// update only new phrase group id
										// also update the text_original so admins can use revert to original
										// in the future for this specific phrase
										$ilance->db->query("
											UPDATE " . DB_PREFIX . "language_phrases
											SET phrasegroup = '" . $ilance->db->escape_string($phrasearray[$i][0]) . "',
											$updateoriginaltext
											$ismastersql
											isupdated = '0'
											WHERE varname = '" . $ilance->db->escape_string($phrasearray[$i][1]) . "'
											LIMIT 1
										", 0, null, __FILE__, __LINE__);    
									}
									else
									{
										// update phrase, phrase group id
										// also update the text_original so admins can use revert to original
										// in the future for this specific phrase
										$ilance->db->query("
											UPDATE " . DB_PREFIX . "language_phrases
											SET " . $ilance->db->escape_string($lfn) . " = '" . $ilance->db->escape_string($phrasearray[$i][2]) . "',
											$updateoriginaltext
											$ismastersql
											phrasegroup = '" . $ilance->db->escape_string($phrasearray[$i][0]) . "',
											isupdated = '0'
											WHERE varname = '" . $ilance->db->escape_string($phrasearray[$i][1]) . "'
											LIMIT 1
										", 0, null, __FILE__, __LINE__);
									}
								}
								else
								{
									$notice .= "Notice: varname - <strong>$phrasearray[$i][1]</strong> for phrase group <strong>$phrasearray[$i][0]</strong> could not be added due to a blank phrase existing within the xml file (near CDATA[])";
								}
							}
						}
						
						if ($slientmode == false)
						{
							print_action_success('{_language_import_successful}', $ilance->GPC['return']);
							exit();
						}
						else
						{
							return true;
						}
					}
				}
				else
				{
					if ($slientmode == false)
					{
						print_action_failed('{_were_sorry_there_was_an_error_with_the_formatting_of_the_language_file}', $ilance->GPC['return']);
						exit();
					}
					else
					{
						return false;
					}
				}
				break;
			}
			case 'css':
			{
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
					$result = $ilance->xml->process_style_xml($data, $xml_encoding);
					if ($result['ilversion'] != $ilance->config['ilversion'])
					{
						if ($slientmode == false)
						{
							print_action_failed('{_the_version_of_the_css_package_is_different_than_the_installed_version_of_ilance}' . ' <strong>' . $ilance->config['ilversion'] . '</strong>. ' . '{_the_operation_has_aborted_due_to_a_version_conflict}', $ilance->GPC['return']);
							exit();
						}
						else
						{
							return false;
						}
					}
					
					$notice = '';
					
					$query = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "styles
						WHERE name = '" . $ilance->db->escape_string($result['name']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($query) == 0)
					{
						// doesn't exist.. insert new style from xml
						$ilance->db->query("
							INSERT INTO " . DB_PREFIX . "styles
							(styleid, name, visible, sort)
							VALUES(
							NULL,
							'" . $ilance->db->escape_string($result['name']) . "',
							'1',
							'10')
						", 0, null, __FILE__, __LINE__);
						
						$newstyleid = $ilance->db->insert_id();
						
						// set the updated XML as default?
						if ($setasdefault)
						{
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "configuration
								SET value = '" . intval($newstyleid) . "'
								WHERE name = 'defaultstyle'
							", 0, null, __FILE__, __LINE__);
						}
						
						// move onto updating each template
						
						// holds NAME=0, DESCRIPTION=1, TYPE=2, value=3
						$templatearray = $result['templatearray'];
						
						$templatecount = count($templatearray);
						for ($i = 0; $i < $templatecount; $i++)
						{
							// ensure template is not empty
							if (isset($templatearray[$i][4]) AND $templatearray[$i][4] != '')
							{
								$ilance->db->query("
									INSERT INTO " . DB_PREFIX . "templates
									(tid, name, description, type, status, original, content, createdate, styleid, product, sort)
									VALUES(
									NULL,
									'" . $ilance->db->escape_string($templatearray[$i][0]) . "',
									'" . $ilance->db->escape_string($templatearray[$i][1]) . "',
									'" . $ilance->db->escape_string($templatearray[$i][2]) . "',
									'1',
									'" . $ilance->db->escape_string($templatearray[$i][5]) . "',
									'" . $ilance->db->escape_string($templatearray[$i][5]) . "',
									'" . DATETIME24H . "',
									'" . intval($newstyleid) . "',
									'" . $ilance->db->escape_string($templatearray[$i][3]) . "',
									'" . intval($templatearray[$i][4]) . "')
								", 0, null, __FILE__, __LINE__);
							}
							else
							{
								$notice .= "Error: style: <strong>".$templatearray[$i][1]."</strong> could not be added due to blank template data existing within the xml file (near CDATA)";
							}
						}
						
						// set the imported style as default?
						if ($setasdefault AND isset($newstyleid) AND $newstyleid > 0)
						{
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "configuration
								SET value = '" . intval($newstyleid) . "'
								WHERE name = 'defaultstyle'
							", 0, null, __FILE__, __LINE__);
						}
						
						if ($slientmode == false)
						{
							print_action_success('{_css_style_importation_success}', $ilance->GPC['return']);
							exit();
						}
						else
						{
							return true;
						}
					}
					else
					{
						// xml style exists in db already .. lets update templates instead
						
						// fetch style id based on the style name being imported (hopefully we have a match!)
						$styleid = $ilance->db->fetch_field(DB_PREFIX . "styles", "name = '" . trim($ilance->db->escape_string($result['name'])) . "'", "styleid");
						
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "styles
							SET visible = '1',
							sort = '10'
							WHERE name = '" . trim($ilance->db->escape_string($result['name'])) . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
		
						// #### move onto updating each template #######
						$templatearray = $result['templatearray'];
						$templatecount = count($templatearray);
						
						for ($i = 0; $i < $templatecount; $i++)
						{
							// ensure the template is not empty
							if (isset($templatearray[$i][0]) AND !empty($templatearray[$i][0]))
							{
								// does css template exist?
								$sql = $ilance->db->query("
									SELECT tid
									FROM " . DB_PREFIX . "templates
									WHERE name = '" . trim($ilance->db->escape_string($templatearray[$i][0])) . "'
										AND type = '" . $ilance->db->escape_string($templatearray[$i][2]) . "'
										AND styleid = '" . intval($styleid) . "'
								", 0, null, __FILE__, __LINE__);
								if ($ilance->db->num_rows($sql) == 0)
								{
									// does not exist - add new css template
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
										'" . DATETIME24H . "',
										'" . $ilance->db->escape_string($templatearray[$i][5]) . "',
										'" . $ilance->db->escape_string($templatearray[$i][5]) . "',
										'" . $ilance->db->escape_string($templatearray[$i][3]) . "',
										'" . intval($templatearray[$i][4]) . "')
									", 0, null, __FILE__, __LINE__);	
								}
								else
								{
									$ilance->db->query("
										UPDATE " . DB_PREFIX . "templates
										SET `name` = '" . $ilance->db->escape_string($templatearray[$i][0]) . "',
										`description` = '" . $ilance->db->escape_string($templatearray[$i][1]) . "',
										`type` = '" . $ilance->db->escape_string($templatearray[$i][2]) . "',
										`status` = '1',
										`updatedate` = '" . DATETIME24H . "',
										`original` = '" . $ilance->db->escape_string($templatearray[$i][5]) . "',
										`content` = '" . $ilance->db->escape_string($templatearray[$i][5]) . "',
										`product` = '" . $ilance->db->escape_string($templatearray[$i][3]) . "',
										`sort` = '" . intval($templatearray[$i][4]) . "'
										WHERE `name` = '" . $ilance->db->escape_string($templatearray[$i][0]) . "'
										AND `type` = '" . $ilance->db->escape_string($templatearray[$i][2]) . "'
										AND `styleid` = '" . intval($styleid) . "'
									", 0, null, __FILE__, __LINE__);	
								}
							}
							else
							{
								$notice .= "Error: style: <strong>" . $templatearray[$i][0] . "</strong> could not be added due to blank template data existing within the xml file (near CDATA)";
							}
						}
						
						// set the imported style as default?
						if ($setasdefault AND isset($styleid) AND $styleid > 0)
						{
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "configuration
								SET value = '" . intval($styleid) . "'
								WHERE name = 'defaultstyle'
							", 0, null, __FILE__, __LINE__);
						}
						
						if ($slientmode == false)
						{
							print_action_success('{_css_style_importation_success}', $ilance->GPC['return']);
							exit();
						}
						else
						{
							return true;	
						}
					}
				}
				else
				{
					$error_string = xml_error_string($error_code);
					
					if ($slientmode == false)
					{
						$notice .= '{_were_sorry_there_was_an_error_with_the_formatting_of_the_configuration_file}' . ' [' . $error_string . '].';
						print_action_failed($notice, $ilance->GPC['return']);
						exit();
					}
					else
					{
						return false;
					}
				}
				break;
			}
			case 'configuration':
			{
				$data = array();
				$html = '<tr class="alt2">
	<td width="20%" wrap="wrap">{_varname}</td>
	<td width="40%" wrap="wrap">{_old} {_value}</td>
	<td width="40%" wrap="wrap">{_new} {_value}</td>
</tr>';
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
					$result = $ilance->xml->process_configuration_xml($data, $xml_encoding);
					$sql = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "configuration");
					while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
					{
						$config[$res['name']] = $res['value']; 	
					}
					$i = 0;
					foreach ($result['main_configuration'] AS $key => $value)
					{
						foreach ($value AS $key2 => $value2)
						{
							if (isset($config[$key2]) AND $config[$key2] != $value2)
							{
								$class = ' class="red"';
								$html .= '<input type="hidden" name="xml[' . $key2 . ']" value="' . htmlspecialchars($value2) . '" />';
								$i++;
							}
							else 
							{
								$class = '';
							}
							if (!empty($class))
							{
								$len1 = strlen($config[$key2]);
								$rows1 = ($len1 > 30) ? intval($len1 / 30) : 1;
								$len2 = strlen($value2);
								$rows2 = ($len2 > 30) ? intval($len2 / 30) : 1;
								$html .= '<tr class="alt1">
	<td width="20%" wrap="wrap"><div' . $class . '>' . $key2 . '</div></td>
	<td width="40%" wrap="wrap"><textarea cols="30" rows="' . $rows1 . '">' . htmlspecialchars($config[$key2]) . '</textarea></td>
	<td width="40%" wrap="wrap"><textarea cols="30" rows="' . $rows2 . '">' . $value2 . '</textarea></td>
</tr>';
							}
						}
					}
					return array($html, $i);
				}	
				else
				{
					$error_string = xml_error_string($error_code);
					if ($slientmode == false)
					{
						$notice .= '{_were_sorry_there_was_an_error_with_the_formatting_of_the_configuration_file}' . ' [' . $error_string . '].';
						print_action_failed($notice);
						exit();
					}
					else
					{
						return false;
					}
				}
				break;
			}
		}
		return false;
        }
	
	/**
        * Function to export emails, phrases or css styles via XML
        *
        * @param       string        export what? [email, phrase or css]
        * @param       string        location/area we're performing the export from [admincp or commandline]
        * @param       integer       language id for export
        * @param       string        location/file path to exported file [used only when commandline argument is used]
        * @param       boolean       determines if we should not run print_notice(), print_action_success(), or print_action_failed() [default false, use true when using commandline argument]
        * @param       boolean       determines if we should only export language phrases that haven't been translated
        * @param       integer       default style id we're attempting to export to XML (when we're exporting CSS only)
        * @param       string        default product to export
        * 
        * @return      boolean       Returns true or false
        */
        function export($what = '', $where = 'admincp', $languageid = 0, $pathtofile = '', $slientmode = false, $untranslated = 0, $styleid = 0, $product = '')
        {
                global $ilance, $ilconfig, $phrase, $ilpage, $show, $notice, $buildversion;
		switch ($what)
		{
			case 'email':
			{
				if ($languageid <= 0)
				{
					die('No language id was specified.  Cannot continue.');
				}
				$query = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "language
					WHERE languageid = '" . intval($languageid) . "'
					LIMIT 1
				");
				if ($ilance->db->num_rows($query) > 0)
				{
					$langconfig = $ilance->db->fetch_array($query, DB_ASSOC);
					header("Content-type: text/xml; charset=" . stripslashes($langconfig['charset']));
					$xml_output = "<?xml version=\"1.0\" encoding=\"" . stripslashes($langconfig['charset']) . "\"?>\n";
					$xml_output .= "<language ilversion=\"" . $ilance->config['ilversion'] . "\">\n\n";
					$xml_output .= "\t<settings>\n";
					$xml_output .= "\t\t<author><![CDATA[" . stripslashes(SITE_NAME) . "]]></author>\n";
					$xml_output .= "\t\t<languagecode><![CDATA[" . stripslashes($langconfig['languagecode']) . "]]></languagecode>\n";
					$xml_output .= "\t\t<charset><![CDATA[" . stripslashes($langconfig['charset']) . "]]></charset>\n";
					$xml_output .= "\t</settings>\n";
					$query2 = $ilance->db->query("
						SELECT name_" . mb_strtolower(mb_substr($langconfig['languagecode'], 0, 3)) . " AS name, varname, type, subject_" . mb_strtolower(mb_substr($langconfig['languagecode'], 0, 3)) . " AS subject, message_" . mb_strtolower(mb_substr($langconfig['languagecode'], 0, 3)) . " AS message, product, cansend, departmentid, buyer, seller, admin, ishtml
						FROM " . DB_PREFIX . "email
						ORDER BY id ASC
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($query2) > 0)
					{
						while ($phraseres = $ilance->db->fetch_array($query2, DB_ASSOC))
						{
							$themessage = stripslashes($phraseres['message']);
							$themessage = (($phraseres['ishtml']) ? $themessage : str_replace("<br />", LINEBREAK, $themessage));
							$thesubject = stripslashes($phraseres['subject']);
							$thename = stripslashes($phraseres['name']);
							$xml_output .= "
\t<email>
\t\t<varname>" . trim($phraseres['varname']) . "</varname>
\t\t<name><![CDATA[" . $thename . "]]></name>
\t\t<subject><![CDATA[" . $thesubject . "]]></subject>
\t\t<type><![CDATA[" . trim($phraseres['type']) . "]]></type>
\t\t<product><![CDATA[" . trim($phraseres['product']) . "]]></product>
\t\t<cansend>" . intval($phraseres['cansend']) . "</cansend>
\t\t<departmentid>" . intval($phraseres['departmentid']) . "</departmentid>
\t\t<buyer>" . intval($phraseres['buyer']) . "</buyer>
\t\t<seller>" . intval($phraseres['seller']) . "</seller>
\t\t<admin>" . intval($phraseres['admin']) . "</admin>
\t\t<ishtml>" . intval($phraseres['ishtml']) . "</ishtml>
\t\t<message><![CDATA[" . $themessage . "]]></message>
\t</email>\n";
						}
					}
					$xml_output .= "</language>";
					$ilance->common->download_file($xml_output, 'emails-' . VERSIONSTRING . '-' . mb_strtolower($langconfig['languagecode']) . '.xml', 'text/plain');
					return true;
				}
				break;
			}
			case 'phrase':
			{
				if ($languageid <= 0)
				{
					die('No language id was specified.  Cannot continue.');
				}
				$query = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "language
					WHERE languageid = '" . $languageid . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($query) > 0)
				{
					$langconfig = $ilance->db->fetch_array($query, DB_ASSOC);
					header("Content-type: text/xml; charset=" . stripslashes($langconfig['charset']));
					$replacements = $langconfig['replacements'];
					// language header configuration settings for this particular language
					$xml_output = "<?xml version=\"1.0\" encoding=\"" . stripslashes($langconfig['charset']) . "\"?>" . LINEBREAK;
					$xml_output .= "<language ilversion=\"" . $ilance->config['ilversion'] . "\">" . LINEBREAK;
					$xml_output .= "\t<settings>" . LINEBREAK;
					$xml_output .= "\t\t<title>" . stripslashes($langconfig['title']) . "</title>" . LINEBREAK;
					$xml_output .= "\t\t<author>" . stripslashes(SITE_NAME) . "</author>" . LINEBREAK;
					$xml_output .= "\t\t<languagecode><![CDATA[" . stripslashes($langconfig['languagecode']) . "]]></languagecode>" . LINEBREAK;
					$xml_output .= "\t\t<charset><![CDATA[" . stripslashes($langconfig['charset']) . "]]></charset>" . LINEBREAK;
					$xml_output .= "\t\t<locale><![CDATA[" . stripslashes($langconfig['locale']) . "]]></locale>" . LINEBREAK;
					$xml_output .= "\t\t<languageiso><![CDATA[" . stripslashes($langconfig['languageiso']) . "]]></languageiso>" . LINEBREAK;
					$xml_output .= "\t\t<textdirection><![CDATA[" . stripslashes($langconfig['textdirection']) . "]]></textdirection>" . LINEBREAK;
					$xml_output .= "\t\t<canselect><![CDATA[" . intval($langconfig['canselect']) . "]]></canselect>" . LINEBREAK;
					$xml_output .= "\t\t<replacements><![CDATA[" . stripslashes($replacements) . "]]></replacements>" . LINEBREAK;
					$xml_output .= "\t</settings>" . LINEBREAK . LINEBREAK;
					$query2 = $ilance->db->query("
						SELECT groupname, description, product
						FROM " . DB_PREFIX . "language_phrasegroups
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($query2) > 0)
					{
						while ($groupres = $ilance->db->fetch_array($query2, DB_ASSOC))
						{
							$xml_output .= "\t<phrasegroup name=\"" . stripslashes($groupres['groupname']) . "\" description=\"" . stripslashes($groupres['description']) . "\" product=\"" . stripslashes($groupres['product']) . "\">" . LINEBREAK;
							if ($untranslated)
							{
								// export only untranslated phrases
								$query3 = $ilance->db->query("
									SELECT varname, text_" . mb_strtolower(mb_substr($langconfig['languagecode'], 0, 3)) . " AS text
									FROM " . DB_PREFIX . "language_phrases
									WHERE phrasegroup = '" . $groupres['groupname'] . "'
										AND text_" . mb_strtolower(mb_substr($langconfig['languagecode'], 0, 3)) . " = text_eng
									ORDER BY phraseid ASC
								", 0, null, __FILE__, __LINE__);
							}
							else
							{
								// export entire language phrases
								$query3 = $ilance->db->query("
									SELECT varname, text_" . mb_strtolower(mb_substr($langconfig['languagecode'], 0, 3)) . " AS text
									FROM " . DB_PREFIX . "language_phrases
									WHERE phrasegroup = '" . $groupres['groupname'] . "'
									ORDER BY phraseid ASC
								", 0, null, __FILE__, __LINE__);
							}
							if ($ilance->db->num_rows($query3) > 0)
							{
								$shortlang = mb_strtolower(mb_substr($langconfig['languagecode'], 0, 3));
								
								while ($phraseres = $ilance->db->fetch_array($query3, DB_ASSOC))
								{
									$xml_output .= "\t\t<phrase varname=\"" . stripslashes(trim($phraseres['varname'])) . "\">" . LINEBREAK . "\t\t\t<![CDATA[" . stripslashes($phraseres['text']) . "]]>" . LINEBREAK . "\t\t</phrase>" . LINEBREAK;
								}
							}
							$xml_output .= "\t</phrasegroup>" . LINEBREAK;
						}
					}
					$xml_output .= "</language>";
					$ilance->common->download_file($xml_output, 'phrases-' . VERSIONSTRING . '-' . $langconfig['languagecode'] . '.xml', 'text/plain');
					return true;
				}
				break;
			}
			case 'css':
			{
				if ($styleid <= 0)
				{
					die('No style id was specified.  Cannot continue.');
				}
				if (empty($product))
				{
					die('No product was specified.  Cannot continue.');
				}
				$query = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "styles
					WHERE styleid = '" . intval($styleid) . "'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($query) > 0)
				{
					$style = $ilance->db->fetch_array($query, DB_ASSOC);
					$xml_output = '<?xml version="1.0" encoding="' . $ilconfig['template_charset'] . '"?>' . LINEBREAK . LINEBREAK;
					$xml_output .= "<!--" . LINEBREAK . "This xml document was exported for use with ILance " . $ilance->config['ilversion'] . ".  Do not hand edit this document." . LINEBREAK . "-->" . LINEBREAK . LINEBREAK;
					$xml_output .= "<style name=\"" . stripslashes($style['name']) . "\" ilversion=\"" . $ilance->config['ilversion'] . "\">" . LINEBREAK . LINEBREAK;
					$query2 = $ilance->db->query("
						SELECT name, description, type, content, product, sort
						FROM " . DB_PREFIX . "templates
						WHERE styleid = '" . intval($styleid) . "'
							AND product = '" . $ilance->db->escape_string($product) . "'
						ORDER BY sort ASC
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($query2) > 0)
					{
						while ($template = $ilance->db->fetch_array($query2, DB_ASSOC))
						{
							$xml_output .= "\t<template name=\"" . trim(stripslashes($template['name'])) . "\" description=\"" . trim(stripslashes($template['description'])) . "\" type=\"" . trim($template['type']) . "\" product=\"" . trim($template['product']) . "\" sort=\"" . intval($template['sort']) . "\"><![CDATA[" . $template['content'] . "]]></template>" . LINEBREAK;
						}
					}
					$xml_output .= LINEBREAK . "</style>";
					$ilance->common->download_file($xml_output, "$product-style-" . VERSIONSTRING . "-styleid-$styleid.xml", "text/plain");
					return true;
				}
				break;
			}
			case 'configuration':
			{
				header("Content-type: text/xml; charset=" . $ilconfig['template_charset']);
				$xml_output = "<?xml version=\"1.0\" encoding=\"" . $ilconfig['template_charset'] . "\"?>" . LINEBREAK;
				$xml_output .= "<configuration ilversion=\"" . $ilance->config['ilversion'] . "\" ilbuild=\"" . $buildversion . "\">" . LINEBREAK;
				$xml_output .= "\t<settings>" . LINEBREAK;
				$xml_output .= "\t\t<sitename>" . stripslashes($ilconfig['globalserversettings_sitename']) . "</sitename>" . LINEBREAK;
				$xml_output .= "\t\t<date>" . DATETIME24H . "</date>" . LINEBREAK;
				$xml_output .= "\t</settings>" . LINEBREAK . LINEBREAK;
				$xml_output .= "\t<main_configuration>" . LINEBREAK;
				$sql_main_conf_group = $ilance->db->query("
					SELECT parentgroupname, groupname, sort, type
					FROM " . DB_PREFIX . "configuration_groups
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql_main_conf_group) > 0)
				{
					while ($res_main_conf_group = $ilance->db->fetch_array($sql_main_conf_group, DB_ASSOC))
					{
						$xml_output .= "\t\t<configuration_group parentgroupname=\"" . stripslashes(trim($res_main_conf_group['parentgroupname'])) . "\" groupname=\"" . stripslashes(trim($res_main_conf_group['groupname'])) . "\" sort=\"" . intval(trim($res_main_conf_group['sort'])) . "\" type=\"" . stripslashes(trim($res_main_conf_group['type'])) . "\">" . LINEBREAK;
						$sql_main_conf = $ilance->db->query("
							SELECT name, value, configgroup, inputtype, inputcode, inputname, sort, visible, type
							FROM " . DB_PREFIX . "configuration
							WHERE configgroup = '" . $res_main_conf_group['groupname'] . "'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql_main_conf) > 0)
						{ 
							while ($res_main_conf = $ilance->db->fetch_array($sql_main_conf, DB_ASSOC))
							{
								$xml_output .= "\t\t\t<option name=\"" . stripslashes(handle_input_keywords(trim($res_main_conf['name']))) . "\" configgroup=\"" . stripslashes(handle_input_keywords(trim($res_main_conf['configgroup']))) . "\" inputtype=\"" . stripslashes(handle_input_keywords(trim($res_main_conf['inputtype']))) . "\" inputcode=\"" . stripslashes(handle_input_keywords(trim($res_main_conf['inputcode']))) . "\" inputname=\"" . stripslashes(handle_input_keywords(trim($res_main_conf['inputname']))) . "\" sort=\"" . intval(trim($res_main_conf['sort'])) . "\" visible=\"" . intval(trim($res_main_conf['visible'])) . "\" inputtype=\"" . stripslashes(handle_input_keywords(trim($res_main_conf['type']))) . "\">" . LINEBREAK . "\t\t\t\t<![CDATA[" . stripslashes($res_main_conf['value']) . "]]>" . LINEBREAK . "\t\t\t</option>" . LINEBREAK;
							}
						}
						$xml_output .= "\t\t</configuration_group>" . LINEBREAK . LINEBREAK;
					}
					unset($res_main_conf_group);
				}
				unset($sql_main_conf_group);
				$xml_output .= "\t</main_configuration>" . LINEBREAK . LINEBREAK;
				if (false)
				{
					$sql_deposit_methods = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "deposit_offline_methods
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql_deposit_methods) > 0)
					{
						$xml_output .= "\t<deposit_methods>" . LINEBREAK;
						while ($res_deposit_methods = $ilance->db->fetch_array($sql_deposit_methods, DB_ASSOC))
						{
							$xml_output .= "\t\t<method id=\"" . stripslashes(trim($res_deposit_methods['id'])) . "\" name=\"" . stripslashes(trim($res_deposit_methods['name'])) . "\" number=\"" . stripslashes(trim($res_deposit_methods['number'])) . "\" swift=\"" . stripslashes(trim($res_deposit_methods['swift'])) . "\" company_name=\"" . stripslashes(trim($res_deposit_methods['company_name'])) . "\" id=\"" . stripslashes(trim($res_deposit_methods['id'])) . "\" company_address=\"" . stripslashes(trim($res_deposit_methods['company_address'])) . "\" custom_notes=\"" . stripslashes(trim($res_deposit_methods['custom_notes'])) . "\" visible=\"" . stripslashes(trim($res_deposit_methods['visible'])) . "\"sort=\"" . stripslashes(trim($res_deposit_methods['sort'])) . "\"></method>" . LINEBREAK;
						}
						$xml_output .= "\t</deposit_methods>" . LINEBREAK . LINEBREAK;
					}
				}
				if (false)
				{
					$sql_payment_groups = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "payment_groups
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql_payment_groups) > 0)
					{
						$xml_output .= "\t<payment_configuration>" . LINEBREAK;
						while ($res_payment_groups = $ilance->db->fetch_array($sql_payment_groups, DB_ASSOC))
						{
							$xml_output .= "\t\t<payment_group groupname=\"" . stripslashes(trim($res_payment_groups['groupname'])) . "\">" . LINEBREAK;
							$sql_payment_configuration = $ilance->db->query("
								SELECT *
								FROM " . DB_PREFIX . "payment_configuration
								WHERE configgroup = '" . $res_payment_groups['groupname'] . "'
							", 0, null, __FILE__, __LINE__);
							if ($ilance->db->num_rows($sql_payment_configuration) > 0)
							{
								while($res_payment_configuration = $ilance->db->fetch_array($sql_payment_configuration, DB_ASSOC))
								{
									$xml_output .= "\t\t\t<option id=\"" . stripslashes(trim($res_payment_configuration['id'])) . "\" name=\"" . stripslashes(trim($res_payment_configuration['name'])) . "\" value=\"" . stripslashes(trim($res_payment_configuration['value'])) . "\" configgroup=\"" . stripslashes(trim($res_payment_configuration['configgroup'])) . "\" inputname=\"" . stripslashes(trim($res_payment_configuration['inputname'])) . "\" ></option>" . LINEBREAK;
								}
							}
							$xml_output .= "\t\t</payment_group>" . LINEBREAK;
						}
						$xml_output .= "\t</payment_configuration>" . LINEBREAK . LINEBREAK;
					}
				}
				if (false)
				{
					$sql_payment_methods = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "payment_methods
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql_payment_methods) > 0)
					{
						$xml_output .= "\t<payment_methods>" . LINEBREAK;
						while ($res_payment_methods = $ilance->db->fetch_array($sql_payment_methods, DB_ASSOC))
						{
							$xml_output .= "\t\t<method id=\"" . stripslashes(trim($res_payment_methods['id'])) . "\" title=\"" . stripslashes(trim($res_payment_methods['title'])) . "\" sort=\"" . stripslashes(trim($res_payment_methods['sort'])) . "\"></option>" . LINEBREAK;
						}
						$xml_output .= "\t</payment_methods>" . LINEBREAK . LINEBREAK;
					}
				}
				$xml_output .= "</configuration>";
				$ilance->common->download_file($xml_output, 'configuration-' . VERSIONSTRING . '-' . $ilconfig['globalserversettings_sitename'] . '.xml', 'text/plain');
				break;
			}
		}
		return false;
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>