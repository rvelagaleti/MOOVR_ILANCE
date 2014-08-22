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
// #### POPUP LANGUGAGE PHRASE REFERENCE FEATURE ###########################
if ($ilance->GPC['cmd'] == 'phrase-reference')
{
	$area_title = '{_viewing_quick_reference_language_phrases}';
	$page_title = SITE_NAME . ' - {_viewing_quick_reference_language_phrases}';
	if (isset($ilance->GPC['phrasegroup']))
	{
		$phraselist = $ilance->admincp->phraselist_pulldown($ilance->GPC['phrasegroup'], $_SESSION['ilancedata']['user']['languageid']);
	}
	else
	{
		$phraselist = $ilance->admincp->phraselist_pulldown('accounting', $_SESSION['ilancedata']['user']['languageid']);
	}
	$language_pulldown = $ilance->language->print_language_pulldown(intval($ilance->GPC['languageid']), false);
	$phrasegroup_pulldown = $ilance->admincp->phrasegroup_pulldown();
	if (isset($ilance->GPC['languageid']) AND $ilance->GPC['languageid'] > 0)
	{
		$language = $ilance->admincp->fetch_language_name(intval($ilance->GPC['languageid']));
	}
	else
	{
		$language = $ilance->admincp->fetch_language_name($_SESSION['ilancedata']['user']['languageid']);
	}
	if (isset($ilance->GPC['phrasegroup']))
	{
		$phrasegroup = $ilance->admincp->api_phrasegroupname($ilance->GPC['phrasegroup']);
	}
	else
	{
		 $phrasegroup = $ilance->admincp->api_phrasegroupname('accounting');
	}
	$pprint_array = array ('language', 'phraselist', 'phrasegroup', 'language_pulldown', 'phrasegroup_pulldown');
    
	($apihook = $ilance->api('admincp_phrase_reference_end')) ? eval($apihook) : false;
    
	$ilance->template->load_admincp_popup('main', 'phrase_reference.html', 1);
	$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', 'phrase_search_results');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### DOWNLOAD XML LANGUAGE PACKAGE ######################################
else if ($ilance->GPC['cmd'] == '_download-xml-language')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$area_title = '{_exporting_language_phrases_to_xml}';
	$page_title = SITE_NAME . ' - {_exporting_language_phrases_to_xml}';
	$languageid = (isset($ilance->GPC['languageid'])) ? intval($ilance->GPC['languageid']) : 0;
	$untranslated = (isset($ilance->GPC['untranslated'])) ? intval($ilance->GPC['untranslated']) : 0;
	$ilance->admincp_importexport = construct_object('api.admincp_importexport');
	$ilance->admincp_importexport->export('phrase', 'admincp', $languageid, '', false, $untranslated);
	exit();
}
// #### UPLOAD XML LANGUAGE PACKAGE ########################################
else if ($ilance->GPC['cmd'] == '_upload-xml-language')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$area_title = '{_importing_language_pack_via_xml}';
	$page_title = SITE_NAME . ' - {_importing_language_pack_via_xml}';
	while (list($key, $value) = each($_FILES))
	$GLOBALS["$key"] = $value;
	foreach ($_FILES AS $key => $value)
	{
		$GLOBALS["$key"] = $_FILES["$key"]['tmp_name'];
		foreach ($value AS $ext => $value2)
		{
			$key2 = $key . '_' . $ext;
			$GLOBALS["$key2"] = $value2;
		}
	}
	$xml = file_get_contents($xml_file);
	$noversioncheck = isset($ilance->GPC['noversioncheck']) ? intval($ilance->GPC['noversioncheck']) : 0;
	$overwritephrases = isset($ilance->GPC['overwrite']) ? intval($ilance->GPC['overwrite']) : 0;
	$ilance->admincp_importexport = construct_object('api.admincp_importexport');
	$ilance->admincp_importexport->import('phrase', 'admincp', $xml, false, $noversioncheck, $overwritephrases);
	exit();
}
// #### REMOVE PHRASE GROUP ############################################
else if ($ilance->GPC['cmd'] == 'removegroup' AND isset($ilance->GPC['phrasegroup']) AND !empty($ilance->GPC['phrasegroup']))
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$area_title = '{_removing_phrase_group}';
	$page_title = SITE_NAME . ' - {_removing_phrase_group}';
	$ilance->db->query("
		DELETE FROM " . DB_PREFIX . "language_phrasegroups
		WHERE groupname = '" . $ilance->db->escape_string($ilance->GPC['phrasegroup']) . "'
	");
	$ilance->db->query("
		DELETE FROM " . DB_PREFIX . "language_phrases
		WHERE phrasegroup = '" . $ilance->db->escape_string($ilance->GPC['phrasegroup']) . "'
	");
	print_action_success('{_you_have_successfully_removed_a_phrase_group_from_your_language}', $ilance->GPC['return']);
	exit();
}
// #### REMOVE LANGUAGE ####################################################
else if ($ilance->GPC['cmd'] == 'removelanguage')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$area_title = '{_removing_languages}';
	$page_title = SITE_NAME . ' - {_removing_languages}';
    
	($apihook = $ilance->api('admincp_removelanguage_start')) ? eval($apihook) : false;
    
	$success = true;
	if (isset($ilance->GPC['languageid']) AND $ilance->GPC['languageid'] > 1)
	{
		$sql_lang = $ilance->db->query("
			SELECT languageid, title, languagecode, charset, locale, author, textdirection, languageiso, canselect, installdate, replacements
			FROM " . DB_PREFIX . "language
			WHERE languageid = '" . intval($ilance->GPC['languageid']) . "'
			LIMIT 1
		");
		if ($ilance->db->num_rows($sql_lang) > 0)
		{
			$res_lang = $ilance->db->fetch_array($sql_lang, DB_ASSOC);
			$ilance->db->query("
				DELETE FROM " . DB_PREFIX . "language
				WHERE languageid = '" . intval($ilance->GPC['languageid']) . "'
				LIMIT 1
			");
			if ($ilance->db->field_exists("text_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "language_phrases") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "language_phrases
					DROP text_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("subject_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "email") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "email
					DROP subject_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("message_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "email") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "email
					DROP message_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("name_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "email") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "email
					DROP name_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("title_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "categories") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "categories
					DROP title_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("description_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "categories") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "categories
					DROP description_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("keywords_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "categories") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "categories
					DROP keywords_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("seourl_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "categories") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "categories
					DROP seourl_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("location_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "locations") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "locations
					DROP location_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("region_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "locations_regions") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "locations_regions
					DROP region_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("question_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "product_questions") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "product_questions
					DROP question_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("description_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "product_questions") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "product_questions
					DROP description_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("choice_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "product_questions_choices") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "product_questions_choices
					DROP choice_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("question_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "project_questions") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "project_questions
					DROP question_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("description_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "project_questions") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "project_questions
					DROP description_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("choice_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "project_questions_choices") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "project_questions_choices
					DROP choice_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("question_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "register_questions") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "register_questions
					DROP question_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("description_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "register_questions") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "register_questions
					DROP description_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("description_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "skills") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "skills
					DROP description_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("title_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "skills") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "skills
					DROP title_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("seourl_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "skills") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "skills
					DROP seourl_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("title_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "feedback_criteria") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "feedback_criteria
					DROP title_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("question_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "bid_fields") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "bid_fields
					DROP question_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("description_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "bid_fields") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "bid_fields
					DROP description_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("description_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "industries") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "industries
					DROP description_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("title_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "industries") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "industries
					DROP title_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("title_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "subscription") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "subscription
					DROP title_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("description_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "subscription") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "subscription
					DROP description_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("title_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "subscription_group") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "subscription_group
					DROP title_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("description_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "subscription_group") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "subscription_group
					DROP description_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("purpose_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "subscription_roles") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "subscription_roles
					DROP purpose_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			if ($ilance->db->field_exists("title_" . mb_substr($res_lang['languagecode'], 0, 3), DB_PREFIX . "subscription_roles") == 1)
			{
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "subscription_roles
					DROP title_" . mb_substr($res_lang['languagecode'], 0, 3) . "
				");
			}
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "users
				SET languageid = '" . intval($ilance->GPC['baselanguage']) . "'
				WHERE languageid = '" . $res_lang['languageid'] . "'
			");
	    
			($apihook = $ilance->api('admincp_removelanguage_end')) ? eval($apihook) : false;
	    
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "configuration
				SET value = '" . intval($ilance->GPC['baselanguage']) . "'
				WHERE name = 'globalserverlanguage_defaultlanguage'
			");
			// if we are viewing the page in the language we are attempting to remove
			// let's ensure we switch back to the default language so no db phrase errors occur
			if ($_SESSION['ilancedata']['user']['languageid'] == $ilance->GPC['languageid'])
			{
				$_SESSION['ilancedata']['user']['languageid'] = intval($ilconfig['globalserverlanguage_defaultlanguage']);
				$_SESSION['ilancedata']['user']['languagecode'] = $ilance->language->print_language_code($ilconfig['globalserverlanguage_defaultlanguage']);
				$_SESSION['ilancedata']['user']['slng'] = $ilance->language->print_short_language_code();
			}
		}
		else
		{
			$success = false;
		}
		if ($success == true)
		{
			print_action_success('{_the_selected_language_was_successfully_removed_from_the_marketplace_datastore}', $ilance->GPC['return']);
			exit();
		}
		else
		{
			print_action_failed('{_there_was_an_error_deleting_the_selected_language_please_select_all_required_form_fields_and_retry_your_action}', $ilance->GPC['return']);
			exit();
		}
	}
	else
	{
		print_action_failed('{_there_was_an_error_deleting_the_selected_language_you_cannot_remove_language_id_1}', $ilance->GPC['return']);
		exit();
	}
}
// #### REMOVE SINGLE OR MULTIPLE PHRASES ##################################
else if ($ilance->GPC['cmd'] == 'deletephrases')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$area_title = '{_removing_phrases}';
	$page_title = SITE_NAME . ' - {_removing_phrases}';
	$success = true;
	$notice = '';
	if (isset($ilance->GPC['removevarnames']))
	{
		foreach ($ilance->GPC['phrasesid'] AS $varname)
		{
			$ilance->db->query("DELETE FROM " . DB_PREFIX . "language_phrases WHERE phraseid = '" . intval($varname) . "' LIMIT 1");
			$notice .= "Template Variable ID#<strong>{$varname}</strong> was deleted from all language locale(s) available.";
		}
		if ($notice == "")
		{
			$success = false;
			print_action_failed('{_warning_phrases_could_not_be_deleted_to_delete_a_phrase_you_must}', $ilance->GPC['return']);
			exit();
		}
		else
		{
			$admurl = $ilance->GPC['return'];
			print_action_success($notice, $admurl);
			exit();
		}
	}
	else
	{
		$admurl = $ilance->GPC['return'];
		print_action_failed('{_warning_your_template_phrases_could_not_be_deleted_to_delete_a_phrase_you}', $admurl);
		exit();
	}
}
// #### ADD NEW LANGUAGE ###################################################
else if ($ilance->GPC['cmd'] == 'addlanguage')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$area_title = '{_adding_a_new_language}';
	$page_title = SITE_NAME . ' - {_adding_a_new_language}';
    
	($apihook = $ilance->api('admincp_addlanguage_start')) ? eval($apihook) : false;
    
	$create = true;
	if (empty($ilance->GPC['lng']))
	{
		$error = '{_please_enter_a_language_name}';
		$create = false;
	}
	if (empty($ilance->GPC['baselanguage']))
	{
		$error .= '{_please_select_a_base_language}';
		$create = false;
	}
	if (empty($ilance->GPC['author']))
	{
		$ilance->GPC['author'] = $_SESSION['ilancedata']['user']['username'];
	}
	$conflicts = $ilance->db->query("
		SELECT languageid, title, languagecode, charset, locale, author, textdirection, languageiso, canselect, installdate, replacements
		FROM " . DB_PREFIX . "language
		WHERE (title LIKE '%" . $ilance->db->escape_string($ilance->GPC['lng']) . "%' OR languagecode LIKE '%" . $ilance->db->escape_string($ilance->GPC['lng']) . "%')
		LIMIT 1
	");
	if ($ilance->db->num_rows($conflicts) > 0)
	{
		$error = '{_this_language_appears_to_be_similar_to_a_language_already_installed_operation_aborted}';
		$create = false;
	}
	if ($create == true)
	{
		$title = ucfirst(mb_strtolower(trim($ilance->GPC['lng'])));
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "language
			(languageid, title, languagecode, charset, author, locale, textdirection, languageiso, installdate, replacements)
			VALUES(
			NULL,
			'" . $ilance->db->escape_string($title) . "',
			'" . $ilance->db->escape_string(mb_strtolower(trim($ilance->GPC['lng']))) . "',
			'" . $ilance->db->escape_string(mb_strtoupper($ilance->GPC['charset'])) . "',
			'" . $ilance->db->escape_string($ilance->GPC['author']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['locale']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['textdirection']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['languageiso']) . "',
			'" . DATETIME24H . "',
			'" . $ilance->db->escape_string($ilance->GPC['replacements']) . "')
		");
		$newlangid = $ilance->db->insert_id();
		$sql_blang = $ilance->db->query("
			SELECT languageid, title, languagecode, charset, locale, author, textdirection, languageiso, canselect, installdate, replacements
			FROM " . DB_PREFIX . "language 
			WHERE languageid = '" . intval($ilance->GPC['baselanguage']) . "'
			LIMIT 1
		");
		if ($ilance->db->num_rows($sql_blang) > 0)
		{
			$res_blang = $ilance->db->fetch_array($sql_blang, DB_ASSOC);
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "categories
				ADD title_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `parentid`
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "categories
				SET title_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = title_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "categories
				ADD description_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `parentid`
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "categories
				SET description_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = description_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "categories
				ADD keywords_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `catimage`
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "categories
				SET keywords_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = keywords_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "categories
				ADD seourl_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `level`
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "categories
				SET seourl_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = seourl_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "email
				ADD subject_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `message_original`
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "email
				SET subject_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = subject_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "email
				ADD message_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `message_original`
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "email
				SET message_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = message_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "email
				ADD name_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `message_original`
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "email
				SET name_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = name_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "locations
				ADD location_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `locationid`
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "locations
				SET location_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = location_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "locations_regions
				ADD region_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `regionid`
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "locations_regions
				SET region_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = region_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "language_phrases
				ADD text_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `text_original`
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "language_phrases
				SET text_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = text_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "project_questions
				ADD question_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `cid`
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_questions
				SET question_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = question_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "project_questions
				ADD description_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `cid`
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_questions
				SET description_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = description_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "project_questions_choices
				ADD choice_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `questionid`
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_questions_choices
				SET choice_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = choice_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "product_questions
				ADD question_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `cid`
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "product_questions
				SET question_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = question_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "product_questions
				ADD description_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `cid`
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "product_questions
				SET description_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = description_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "product_questions_choices
				ADD choice_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `questionid`
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "product_questions_choices
				SET choice_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = choice_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "register_questions
				ADD question_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `pageid`
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "register_questions
				SET question_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = question_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "register_questions
				ADD description_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `pageid`
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "register_questions
				SET description_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = description_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "skills
				ADD title_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `parentid`
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "skills
				ADD description_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `parentid`
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "skills
				ADD seourl_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `rootcid`
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "skills
				SET title_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = title_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "skills
				SET description_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = description_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "skills
				SET seourl_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = seourl_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "feedback_criteria
				ADD title_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `id`
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "feedback_criteria
				SET title_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = title_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "bid_fields
				ADD question_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `fieldid`
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "bid_fields
				SET question_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = question_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "bid_fields
				ADD description_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `fieldid`
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "bid_fields
				SET description_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = description_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "industries
				ADD title_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `parentid`
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "industries
				ADD description_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `parentid`
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "industries
				SET title_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = title_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "industries
				SET description_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = description_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "subscription
				ADD description_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `subscriptionid`
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "subscription
				ADD title_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `subscriptionid`
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "subscription
				SET description_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = description_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "subscription
				SET title_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = title_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "subscription_group
				ADD description_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `subscriptiongroupid`
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "subscription_group
				ADD title_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `subscriptiongroupid`
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "subscription_group
				SET description_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = description_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "subscription_group
				SET title_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = title_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "subscription_roles
				ADD title_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `roleid`
			");
			$ilance->db->query("
				ALTER TABLE " . DB_PREFIX . "subscription_roles
				ADD purpose_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " MEDIUMTEXT
				AFTER `roleid`
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "subscription_roles
				SET title_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = title_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "subscription_roles
				SET purpose_" . strtolower(substr($ilance->GPC['lng'], 0, 3)) . " = purpose_" . strtolower(substr($res_blang['languagecode'], 0, 3)) . "
			");
	    
			($apihook = $ilance->api('admincp_addlanguage_end')) ? eval($apihook) : false;
	    
			if (isset($ilance->GPC['defaultlanguage']) AND $ilance->GPC['defaultlanguage'])
			{
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "configuration 
					SET value = '" . $newlangid . "' 
					WHERE name = 'globalserverlanguage_defaultlanguage' 
				");
				$default_language_id = $newlangid;
			}
		}
		print_action_success('{_new_language_was_successfully_created}', $ilance->GPC['return']);
		exit();
	}
	else
	{
		print_action_failed($error, $ilance->GPC['return']);
		exit();
	}
}
// #### CREATE NEW TEMPLATE VARIABLE #######################################
else if ($ilance->GPC['cmd'] == 'createphrase')
{
	$area_title = '{_creating_new_phrases}';
	$page_title = SITE_NAME . ' - {_creating_new_phrases}';
	$error = $notice = '';
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$languageid = intval($ilance->GPC['languageid']);
	$phrasegroup = $ilance->GPC['phrasegroup'];
	$newvariable = $ilance->GPC['varname'];
	$newtext = $ilance->GPC['text'];
	$sql_languages = $ilance->db->query("
		SELECT languageid, title, languagecode, charset, locale, author, textdirection, languageiso, canselect, installdate, replacements
		FROM " . DB_PREFIX . "language
	");
	if ($ilance->db->num_rows($sql_languages) > 0)
	{
		while ($row = $ilance->db->fetch_array($sql_languages, DB_ASSOC))
		{
			$sql_checkvar = $ilance->db->query("
				SELECT phraseid, phrasegroup, varname, text_" . mb_substr($row['languagecode'], 0, 3) . " AS text
				FROM " . DB_PREFIX . "language_phrases
				WHERE varname = '" . $ilance->db->escape_string($newvariable) . "'
			");
			if ($ilance->db->num_rows($sql_checkvar) > 0)
			{
				## CHECK FOR NEW LANGUAGE..
				$sql_checkvar2 = $ilance->db->query("
					SELECT phraseid, phrasegroup, varname, text_" . mb_substr($row['languagecode'], 0, 3) . " AS text
					FROM " . DB_PREFIX . "language_phrases
					WHERE text_" . mb_substr($row['languagecode'], 0, 3) . " = ''
				");
				if ($ilance->db->num_rows($sql_checkvar2) > 0)
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "language_phrases
						SET text_" . mb_substr($row['languagecode'], 0, 3) . " = '" . $ilance->db->escape_string($newtext) . "'
						WHERE varname = '" . $ilance->db->escape_string($newvariable) . "'
					");
					$notice .= "New language template variable <strong>" . stripslashes($ilance->GPC['varname']) . "</strong> was successfully updated for the " . ucfirst($row['languagecode']) . " language locale also existing in the template engine.";
				}
				else
				{
					$notice .= "The new template variable <strong>" . stripslashes($ilance->GPC['varname']) . "</strong> already exists.  Your new template variable could not be created.  To learn more, please use the Search Phrases or Content option below to find out where this phrase is being used.";
				}
			}
			else
			{
				// no rows exist > insert new phrase and variable for this language
				$ilance->db->query("
					INSERT INTO " . DB_PREFIX . "language_phrases
					(phraseid, phrasegroup, varname, text_original, text_" . mb_substr($row['languagecode'], 0, 3) . ")
					VALUES(
					NULL,
					'" . $phrasegroup . "',
					'" . $ilance->db->escape_string($newvariable) . "',
					'" . $ilance->db->escape_string($newtext) . "',
					'" . $ilance->db->escape_string($newtext) . "')
				");
				$notice .= "New language template variable <strong>" . stripslashes($ilance->GPC['varname']) . "</strong> was successfully created.  In order to use this new template within your HTML templates, you must use it like the following: <strong>{" . stripslashes($ilance->GPC['varname']) . "}</strong> keeping the braces in tact.";
			}
		}
		print_action_success($notice, $ilance->GPC['return']);
		exit();
	}
	else
	{
		$error .= '{_were_sorry_there_is_currently_no_available_languages_to_add_phrases_to_please_add_a_new_language_before_you_create_new_phrases}';
		print_action_failed($error, $ilance->GPC['return']);
		exit();
	}
}
// #### CREATE NEW PHRASE GROUP ############################################
else if ($ilance->GPC['cmd'] == 'addphrasegroup')
{
	$area_title = '{_adding_new_phrase_group}';
	$page_title = SITE_NAME . ' - {_adding_new_phrase_group}';
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$create = true;
	if (empty($ilance->GPC['groupname']))
	{
		$create = false;
	}
	if (empty($ilance->GPC['description']))
	{
		$create = false;
	}
	if ($create == true)
	{
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "language_phrasegroups
			(groupname, description, product)
			VALUES(
			'" . mb_strtolower($ilance->db->escape_string($ilance->GPC['groupname'])) . "',
			'" . $ilance->db->escape_string($ilance->GPC['description']) . "',
			'ilance')
		");
		print_action_success('{_new_language_phrase_group_was_successfully_created}', $ilance->GPC['return']);
		exit();
	}
	else
	{
		print_action_failed('{_there_was_an_error_adding_your_new_phrase_group_please_fill_in_all}', $ilance->GPC['return']);
		exit();
	}
}
// #### REBUILD LANGUAGE CACHE FILES #######################################
else if ($ilance->GPC['cmd'] == 'rebuildlanguage')
{
	$area_title = '{_rebuilding_language_cache}';
	$page_title = SITE_NAME . ' - {_rebuilding_language_cache}';
	$ilance->admincp->rebuild_language_cache();
	print_action_success('{_language_cache_files_have_been_rebuilt_any_recent_phrase_changes_should}', $ilance->GPC['return']);
	exit();
}
// #### SET LANGUAGE DEFAULT ALL USERS #####################################
else if ($ilance->GPC['cmd'] == 'languagedefault')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	if (isset($ilance->GPC['languageid']) AND $ilance->GPC['languageid'] > 0)
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "users
			SET languageid = '" . intval($ilance->GPC['languageid']) . "'
		");
		print_action_success('You have successfully updated the language preference for all users in the marketplace.', $ilance->GPC['return']);
		exit();
	}
	print_action_failed('There was a problem updating the language preference for all users in the marketplance.', $ilance->GPC['return']);
	exit();
}
// #### UPDATE PHRASE VARIABLE OR CONTENT HANDLER ##########################
else if ($ilance->GPC['cmd'] == 'update')
{
	$area_title = '{_updating_existing_phrases}';
	$page_title = SITE_NAME . ' - {_updating_existing_phrases}';
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$success = true;
	$notice = "";
	if (isset($ilance->GPC['removevarnames']))
	{
		foreach ($ilance->GPC['phrasesid'] AS $varname)
		{
			$ilance->db->query("
				DELETE FROM " . DB_PREFIX . "language_phrases
				WHERE phraseid = '" . intval($varname) . "'
				LIMIT 1
			");
			$notice .= "Template Variable <strong>{$varname}</strong> was deleted from all languages available.";
		}
		if (empty($notice))
		{
			$success = false;
			print_action_failed("Warning: your template phrase <strong>{$varname}</strong> could not be deleted.  To delete a phrase you must select it first by using the checkbox option beside each phrase available from the search result listings.", $ilance->GPC['return']);
			exit();
		}
		else
		{
			print_action_success($notice, $ilance->GPC['return']);
			exit();
		}
	}
	else
	{
		$languageid = isset($ilance->GPC['languageid']) ? intval($ilance->GPC['languageid']) : 0;
		$phrasegroup = isset($ilance->GPC['phrasegroup']) ? $ilance->GPC['phrasegroup'] : 'main';
		$page = isset($ilance->GPC['page']) ? intval($ilance->GPC['page']) : 1;
		$phrgroupname = isset($ilance->GPC['phrgroupname']) ? $ilance->GPC['phrgroupname'] : '';
		$phrvartplname = isset($ilance->GPC['phrvartplname']) ? $ilance->GPC['phrvartplname'] : '';
		$phrvarid = isset($ilance->GPC['phrvarid']) ? $ilance->GPC['phrvarid'] : '';
		$phrvarname = isset($ilance->GPC['phrvarname']) ? $ilance->GPC['phrvarname'] : '';
		$sql_lang = $ilance->db->query("
		    SELECT languageid, title, languagecode, charset, locale, author, textdirection, languageiso, canselect, installdate, replacements
			FROM " . DB_PREFIX . "language
			WHERE languageid = '" . $languageid . "'
			LIMIT 1
		");
		if ($ilance->db->num_rows($sql_lang) > 0)
		{
			$res_lang = $ilance->db->fetch_array($sql_lang, DB_ASSOC);
			if (isset($phrgroupname) AND is_array($phrgroupname))
			{
				foreach ($phrgroupname AS $phraseid => $groupname)
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "language_phrases
						SET phrasegroup = '" . $ilance->db->escape_string($groupname) . "',
						ismoved = '1'
						WHERE phraseid = '" . intval($phraseid) . "'
							AND phrasegroup != '" . $ilance->db->escape_string($groupname) . "'
					");
				}
			}
			if (isset($phrvartplname) AND is_array($phrvartplname))
			{
				foreach ($phrvartplname AS $phraseid => $varname)
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "language_phrases
						SET varname = '" . $ilance->db->escape_string($varname) . "',
						isupdated = '1'
						WHERE phraseid = '" . intval($phraseid) . "'
							AND varname != '" . $ilance->db->escape_string($varname) . "'
					");
				}
			}
			if (isset($phrvarname) AND is_array($phrvarname))
			{
				foreach ($phrvarname AS $phraseid => $phrasetext)
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "language_phrases
						SET text_" . mb_strtolower(mb_substr($res_lang['languagecode'], 0, 3)) . " = '" . $ilance->db->escape_string($phrasetext) . "',
						isupdated = '1'
						WHERE phraseid = '" . intval($phraseid) . "'
							AND text_" . mb_strtolower(mb_substr($res_lang['languagecode'], 0, 3)) . " NOT LIKE BINARY '" . $ilance->db->escape_string($phrasetext) . "'
					");
				}
			}
		}
		else
		{
			$error = "{_your_requested_actions_to_change_move_or_modify_an_existing_variable_or_phrase_could_not_be_performed}";
			$success = false;
		}
	}
	if ($success == true)
	{
		print_action_success('{_you_have_sucessfully_changed_moved_or_modified_language_phrases}', $ilance->GPC['return']);
		exit();
	}
	else
	{
		print_action_failed($error, $ilance->GPC['return']);
		exit();
	}
}
else if ($ilance->GPC['cmd'] == 'search')
{
	$area_title = '{_phrase_search_listings}';
	$page_title = SITE_NAME . ' - {_phrase_search_listings}';
	if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
	{
		$ilance->GPC['page'] = 1;
	}
	else
	{
		$ilance->GPC['page'] = intval($ilance->GPC['page']);
	}
	$languageid = isset($ilance->GPC['languageid']) ? intval($ilance->GPC['languageid']) : $ilance->language->fetch_default_languageid();
	$phrasegroup = isset($ilance->GPC['phrasegroup']) ? $ilance->GPC['phrasegroup'] : 'main';
	$request_uri = SCRIPT_URI;
	isset($keyword) ? $ilance->GPC['keyword'] : '';
	$sql_lang = $ilance->db->query("
		SELECT languageid, title, languagecode, charset, locale, author, textdirection, languageiso, canselect, installdate, replacements
		FROM " . DB_PREFIX . "language
		WHERE languageid = '" . $languageid . "'
		LIMIT 1
	");
	$res_lang = $ilance->db->fetch_array($sql_lang, DB_ASSOC);
	$rowlimit = isset($ilance->GPC['limit']) ? $ilance->GPC['limit'] : 5;
	$counter = ($ilance->GPC['page'] - 1) * $rowlimit;
	$orderlimit = ' ORDER BY varname ASC LIMIT ' . ((intval($ilance->GPC['page']) - 1) * $rowlimit) . ',' . $rowlimit;
	$sql = "
		SELECT phraseid, phrasegroup, varname, text_" . mb_strtolower(mb_substr($res_lang['languagecode'], 0, 3)) . " AS text, isupdated, ismoved
		FROM " . DB_PREFIX . "language_phrases
	";
	// view listings - no advanced search keywords entered
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'view')
	{
		if (isset($ilance->GPC['allgroups']) AND $ilance->GPC['allgroups'])
		{
			// perform search across all phrase groups
			$sql .= " WHERE phrasegroup != '' ";
		}
		else
		{
			// search a specific phrase group
			$sql .= " WHERE phrasegroup = '" . $ilance->db->escape_string($phrasegroup) . "' ";
		}
	}
	else
	{
		// exact matching queries within all phrasegroups
		if (isset($ilance->GPC['exactmatch']) AND $ilance->GPC['exactmatch'] AND $ilance->GPC['keyword'] != "" AND isset($ilance->GPC['allgroups']) AND $ilance->GPC['allgroups'])
		{
			$sql .= " WHERE (text_" . mb_strtolower(mb_substr($res_lang['languagecode'], 0, 3)) . " = '" . $ilance->db->escape_string($ilance->GPC['keyword']) . "' OR varname = '" . $ilance->db->escape_string($ilance->GPC['keyword']) . "') ";
		}
		// matching queries within a phrasegroup
		else if (isset($ilance->GPC['exactmatch']) AND $ilance->GPC['exactmatch'] AND $ilance->GPC['keyword'] != "" AND empty($ilance->GPC['allgroups']))
		{
			$sql .= " WHERE (text_" . mb_strtolower(mb_substr($res_lang['languagecode'], 0, 3)) . " = '" . $ilance->db->escape_string($ilance->GPC['keyword']) . "' OR varname = '" . $ilance->db->escape_string($ilance->GPC['keyword']) . "') AND phrasegroup = '" . $ilance->db->escape_string($phrasegroup) . "' ";
		}
		// search a phrasegroup
		else if (empty($ilance->GPC['allgroups']) AND isset($ilance->GPC['keyword']) AND isset($ilance->GPC['keyword']) AND $ilance->GPC['keyword'] != "" AND empty($ilance->GPC['exactmatch']))
		{
			$sql .= " WHERE (text_" . mb_strtolower(mb_substr($res_lang['languagecode'], 0, 3)) . " LIKE '%" . $ilance->db->escape_string($ilance->GPC['keyword']) . "%' OR varname LIKE '%" . $ilance->db->escape_string($ilance->GPC['keyword']) . "%') AND phrasegroup = '" . $ilance->db->escape_string($phrasegroup) . "' ";
		}
		// search all known phrasegroups
		else if (isset($ilance->GPC['allgroups']) AND $ilance->GPC['allgroups'] AND isset($ilance->GPC['keyword']) AND $ilance->GPC['keyword'] != "" AND empty($ilance->GPC['exactmatch']))
		{
			$sql .= " WHERE (text_" . mb_strtolower(mb_substr($res_lang['languagecode'], 0, 3)) . " LIKE '%" . $ilance->db->escape_string($ilance->GPC['keyword']) . "%' OR varname LIKE '%" . $ilance->db->escape_string($ilance->GPC['keyword']) . "%') ";
		}
		// listing all phrases in all phrasegroups
		else if (empty($ilance->GPC['keyword']) AND isset($ilance->GPC['allgroups']) AND $ilance->GPC['allgroups'] AND empty($ilance->GPC['exactmatch']))
		{
			$sql .= " WHERE phrasegroup != '' ";
		}
		// listing all phrases in a phrasegroup
		else if (empty($ilance->GPC['keyword']) AND empty($ilance->GPC['allgroups']) AND empty($ilance->GPC['exactmatch']))
		{
			$sql .= " WHERE phrasegroup = '" . $ilance->db->escape_string($phrasegroup) . "' ";
		}
		// listing keywords in a phrasegroup
		else if (isset($ilance->GPC['keyword']) AND $ilance->GPC['keyword'] != "" AND empty($ilance->GPC['allgroups']) AND empty($ilance->GPC['exactmatch']))
		{
			$sql .= " WHERE (text_" . mb_strtolower(mb_substr($res_lang['languagecode'], 0, 3)) . " LIKE '%" . $ilance->db->escape_string($ilance->GPC['keyword']) . "%' OR varname LIKE '%" . $ilance->db->escape_string($ilance->GPC['keyword']) . "%') AND phrasegroup = '" . $ilance->db->escape_string($phrasegroup) . "' ";
		}
		if (isset($ilance->GPC['untranslated']) AND $ilance->GPC['untranslated'])
		{
			$sql .= " AND text_" . mb_strtolower(mb_substr($res_lang['languagecode'], 0, 3)) . " = text_eng";
		}
	}
	$sql .= $orderlimit;
	$sql = $ilance->db->query($sql);
	$sql2 = "
		SELECT phraseid, phrasegroup, varname, text_" . mb_strtolower(mb_substr($res_lang['languagecode'], 0, 3)) . " AS text
		FROM " . DB_PREFIX . "language_phrases
	";
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'view')
	{
		if (isset($ilance->GPC['allgroups']) AND $ilance->GPC['allgroups'])
		{
			$sql2 .= " WHERE phrasegroup != '' ";
		}
		else
		{
			$sql2 .= " WHERE phrasegroup = '" . $ilance->db->escape_string($phrasegroup) . "' ";
		}
	}
	else
	{
		// exact matching queries within all phrasegroups -->
		if (isset($ilance->GPC['exactmatch']) AND $ilance->GPC['exactmatch'] AND isset($ilance->GPC['keyword']) AND $ilance->GPC['keyword'] != "" AND isset($ilance->GPC['allgroups']) AND $ilance->GPC['allgroups'] == 1)
		{
			$sql2 .= " WHERE (text_" . mb_strtolower(mb_substr($res_lang['languagecode'], 0, 3)) . " = '" . $ilance->db->escape_string($ilance->GPC['keyword']) . "' OR varname = '" . $ilance->db->escape_string($ilance->GPC['keyword']) . "') ";
		}
		// matching queries within a phrasegroup
		else if (isset($ilance->GPC['exactmatch']) AND $ilance->GPC['exactmatch'] AND isset($ilance->GPC['keyword']) AND $ilance->GPC['keyword'] != "" AND empty($ilance->GPC['allgroups']))
		{
			$sql2 .= " WHERE (text_" . mb_strtolower(mb_substr($res_lang['languagecode'], 0, 3)) . " = '" . $ilance->db->escape_string($ilance->GPC['keyword']) . "' OR varname = '" . $ilance->db->escape_string($ilance->GPC['keyword']) . "') AND phrasegroup = '" . $ilance->db->escape_string($phrasegroup) . "' ";
		}
		// search a phrasegroup
		else if (empty($ilance->GPC['allgroups']) AND isset($ilance->GPC['keyword']) AND $ilance->GPC['keyword'] != "" AND empty($ilance->GPC['exactmatch']))
		{
			$sql2 .= " WHERE (text_" . mb_strtolower(mb_substr($res_lang['languagecode'], 0, 3)) . " LIKE '%" . $ilance->db->escape_string($ilance->GPC['keyword']) . "%' OR varname LIKE '%" . $ilance->db->escape_string($ilance->GPC['keyword']) . "%') AND phrasegroup = '" . $ilance->db->escape_string($phrasegroup) . "' ";
		}
		// search all known phrasegroups
		else if (isset($ilance->GPC['allgroups']) AND $ilance->GPC['allgroups'] AND isset($ilance->GPC['keyword']) AND $ilance->GPC['keyword'] != "" AND empty($ilance->GPC['exactmatch']))
		{
			$sql2 .= " WHERE (text_" . mb_strtolower(mb_substr($res_lang['languagecode'], 0, 3)) . " LIKE '%" . $ilance->db->escape_string($ilance->GPC['keyword']) . "%' OR varname LIKE '%" . $ilance->db->escape_string($ilance->GPC['keyword']) . "%') ";
		}
		// listing all phrases in all phrasegroups
		else if (empty($ilance->GPC['keyword']) AND isset($ilance->GPC['allgroups']) AND $ilance->GPC['allgroups'] AND empty($ilance->GPC['exactmatch']))
		{
			$sql2 .= " WHERE phrasegroup != '' ";
		}
		// listing all phrases in a phrasegroup
		else if (empty($ilance->GPC['keyword']) AND empty($ilance->GPC['allgroups']) AND empty($ilance->GPC['exactmatch']))
		{
			$sql2 .= " WHERE phrasegroup = '" . $ilance->db->escape_string($phrasegroup) . "' ";
		}
		// listing keywords in a phrasegroup
		else if (isset($ilance->GPC['keyword']) AND $ilance->GPC['keyword'] != "" AND empty($ilance->GPC['allgroups']) AND empty($ilance->GPC['exactmatch']))
		{
			$sql2 .= " WHERE (text_" . mb_strtolower(mb_substr($res_lang['languagecode'], 0, 3)) . " LIKE '%" . $ilance->db->escape_string($ilance->GPC['keyword']) . "%' OR varname LIKE '%" . $ilance->db->escape_string($ilance->GPC['keyword']) . "%') AND phrasegroup = '" . $ilance->db->escape_string($phrasegroup) . "' ";
		}
	}
	$sql2 = $ilance->db->query($sql2);
	if ($ilance->db->num_rows($sql) > 0)
	{
		$number = $ilance->db->num_rows($sql2);
		$row_count = 0;
		while ($row = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$row['groupname'] = $ilance->db->fetch_field(DB_PREFIX . "language_phrasegroups", "groupname = '" . $row['phrasegroup'] . "'", "description");
			$row['tempvariable'] = str_replace("_", "_ ", $row['varname']);
			$row['langcode'] = ucfirst($res_lang['languagecode']);
			$sqlbaselanguage = $ilance->db->query("
				SELECT baselanguageid
				FROM " . DB_PREFIX . "language_phrases
				WHERE varname = '" . $ilance->db->escape_string($row['varname']) . "'
			");
			if ($ilance->db->num_rows($sqlbaselanguage) > 0)
			{
				$resbaselanguage = $ilance->db->fetch_array($sqlbaselanguage);
			}
			$sqllang = $ilance->db->query("
				SELECT languagecode
				FROM " . DB_PREFIX . "language
				WHERE languageid = '" . $resbaselanguage['baselanguageid'] . "'
			");
			$reslang = $ilance->db->fetch_array($sqllang);
			$reslngshort = $reslang['languagecode'];
			$sqlbaselanguagetext = $ilance->db->query("
				SELECT text_original, text_" . mb_strtolower(mb_substr($reslngshort, 0, 3)) . " AS text
				FROM " . DB_PREFIX . "language_phrases
				WHERE varname = '" . $ilance->db->escape_string($row['varname']) . "'
			");
			if ($ilance->db->num_rows($sqlbaselanguagetext) > 0)
			{
				$resbaselanguage = $ilance->db->fetch_array($sqlbaselanguagetext, DB_ASSOC);
		
				($apihook = $ilance->api('admincp_language_loop')) ? eval($apihook) : false;
		
				// original text
				$info = '<div>' . '{_original_phrase_based_from}' . ' <strong>' . ucfirst($reslang['languagecode']) . '</strong></div>';
				$info .= '<div class="gray">' . stripslashes($resbaselanguage['text_original']) . '</div><br />';
				// actual text (applied with some highlighting)
				$info .= '<div><strong>' . '{_actual_phrase}' . '</strong> ' . '{_in}' . ' <strong>' . $row['langcode'] . '</strong></div>';
				if (isset($ilance->GPC['keyword']))
				{
					$first_q = str_replace($ilance->GPC['keyword'], "<b><span class='errormessage'>" . $ilance->GPC['keyword'] . "</span></b>", stripslashes($row['text']));
					$ucf_q = str_replace(ucfirst($ilance->GPC['keyword']), "<b><span class='errormessage'>" . ucfirst($ilance->GPC['keyword']) . "</span></b>", $first_q);
					$uc_q = str_replace(mb_strtoupper($ilance->GPC['keyword']), "<b><span class='errormessage'>" . mb_strtoupper($ilance->GPC['keyword']) . "</span></b>", $ucf_q);
				}
				else
				{
					$uc_q = stripslashes($row['text']);
				}
			}
			else
			{
				$info .= '{_no_phrase_content_available}';
			}
			$uc_q = '<div class="gray">' . $uc_q . '</div>';
			$row['phraseinfo'] = $info . $uc_q;
			$row['phrasetext'] = stripslashes($row['text']);
			$row['varname'] = $row['varname'];
			$phrasegroupname_pulldown = '<select name="phrgroupname[' . $row['phraseid'] . ']" style="font-family: verdana">';
			$sql_phrasegroups = $ilance->db->query("
				SELECT groupname, description, product
				FROM " . DB_PREFIX . "language_phrasegroups
			");
			while ($res_phrasegroups = $ilance->db->fetch_array($sql_phrasegroups, DB_ASSOC))
			{
				$phrasegroupname_pulldown .= '<option value="' . $res_phrasegroups['groupname'] . '"';
				if ($res_phrasegroups['groupname'] == $row['phrasegroup'])
				{
					$phrasegroupname_pulldown .= ' selected="selected"';
				}
				$phrasegroupname_pulldown .= '>' . $res_phrasegroups['description'] . '</option>';
			}
			$phrasegroupname_pulldown .= '</select>';
			$row['phrasegroupname_pulldown'] = $phrasegroupname_pulldown;
			if (isset($val))
			{
				$row['templates_scan'] = $val;
			}
			else
			{
				$row['templates_scan'] = '';
			}
			$row['isupd'] = '<input type="checkbox" name="isupdated" value="" ';
			if ($row['isupdated'] == '1')
			{
				$row['isupd'] .= 'checked="checked"';
			}
			$row['isupd'] .= ' disabled="disabled" />';
			$row['ismov'] = '<input type="checkbox" name="ismoved" value="" ';
			if ($row['ismoved'] == '1')
			{
				$row['ismov'] .= 'checked="checked"';
			}
			$row['ismov'] .= ' disabled="disabled" />';
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$phrase_search_results[] = $row;
			$row_count++;
		}
		$show['no_phrase_search_results'] = false;
	}
	else
	{
		$show['no_phrase_search_results'] = true;
	}
	$ilance->GPC['phrasegroup'] = isset($ilance->GPC['phrasegroup']) ? $ilance->GPC['phrasegroup'] : 'main';
	$ilance->GPC['languageid'] = isset($ilance->GPC['languageid']) ? $ilance->GPC['languageid'] : $ilance->language->fetch_default_languageid();
	$row['phrasegroup_pulldown'] = $ilance->language->print_phrase_groups_pulldown($ilance->GPC['phrasegroup'], false, $_SESSION['ilancedata']['user']['slng']);
	$phrasegroup_pulldown = $ilance->language->print_phrase_groups_pulldown($ilance->GPC['phrasegroup'], false, $_SESSION['ilancedata']['user']['slng']);
	$language_pulldown = $ilance->language->print_language_pulldown(intval($ilance->GPC['languageid']), false);
	$limit_pulldown = '<select name="limit" style="font-family: verdana">';
	$limit_pulldown .= '<option value="5" ';
	if (isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "5" OR isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "5")
	{
		$limit_pulldown .= 'selected';
	}
	$limit_pulldown .= '>5 per page</option>';
	$limit_pulldown .= '<option value="10" ';
	if (isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "10" OR isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "10")
	{
		$limit_pulldown .= 'selected';
	}
	$limit_pulldown .= '>10 per page </option>';
	$limit_pulldown .= '<option value="25" ';
	if (isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "25" OR isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "25")
	{
		$limit_pulldown .= 'selected';
	}
	$limit_pulldown .= '>25 per page</option>';
	$limit_pulldown .= '<option value="50" ';
	if (isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "50" OR isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] == "50")
	{
		$limit_pulldown .= 'selected';
	}
	$limit_pulldown .= '>50 per page</option>';
	$limit_pulldown .= '</select>';
	$phrasegroupname = $ilance->db->fetch_field(DB_PREFIX . "language_phrasegroups", "groupname = '" . $ilance->db->escape_string($ilance->GPC['phrasegroup']) . "'", "description");
	$phrasegroup = $ilance->GPC['phrasegroup'];
	$keyword = '';
	if (isset($ilance->GPC['keyword']))
	{
		$keyword = $ilance->GPC['keyword'];
	}
	$limit = isset($ilance->GPC['limit']) ? intval($ilance->GPC['limit']) : 5;
	$subcmd = '';
	if (isset($ilance->GPC['subcmd']))
	{
		$subcmd = $ilance->GPC['subcmd'];
	}
	$allgroups = '';
	if (isset($ilance->GPC['allgroups']))
	{
		$allgroups = $ilance->GPC['allgroups'];
	}
	$exactmatch = '';
	if (isset($ilance->GPC['exactmatch']))
	{
		$exactmatch = $ilance->GPC['exactmatch'];
	}
	$untranslated = '';
	if (isset($ilance->GPC['untranslated']))
	{
		$untranslated = $ilance->GPC['untranslated'];
	}
	$scriptpage = $ilpage['language'] . '?cmd=' . $ilance->GPC['cmd'] . '&amp;subcmd=' . $subcmd . '&amp;languageid=' . intval($ilance->GPC['languageid']) . '&amp;phrasegroup=' . $ilance->GPC['phrasegroup'] . '&amp;allgroups=' . $allgroups . '&amp;keyword=' . $keyword . '&amp;limit=' . $limit . '&amp;exactmatch=' . $exactmatch . '&amp;untranslated=' . $untranslated;
	if (empty($counter))
	{
		$counter = 0;
	}
	if (empty($number))
	{
		$number = 0;
	}
	$prevnext = print_pagnation($number, $rowlimit, intval($ilance->GPC['page']), $counter, $scriptpage);
	$pprint_array = array ('limit', 'keyword', 'phrasegroup', 'limit_pulldown', 'language_pulldown', 'phrasegroupname', 'prevnext', 'ismov', 'isupd', 'languageid', 'request_uri', 'keyword', 'base_language_pulldown', 'limit_pulldown', 'phrasegroup_pulldown', 'language_pulldown');
    
	($apihook = $ilance->api('admincp_language_phrase_results_end')) ? eval($apihook) : false;
    
	$ilance->template->fetch('main', 'language_phrase_results.html', 1);
	$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', 'v3nav');
	$ilance->template->parse_loop('main', 'phrase_search_results');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
else if ($ilance->GPC['cmd'] == 'edit-language')
{
	$show['editlanguage'] = true;
	// #### saving language details
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'save')
	{
		if ($show['ADMINCP_TEST_MODE'])
		{
			print_action_failed('{_demo_mode_only}', $ilpage['components']);
			exit();
		}
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "language
			SET title = '" . $ilance->db->escape_string($ilance->GPC['title']) . "',
			charset = '" . $ilance->db->escape_string($ilance->GPC['charset']) . "',
			locale = '" . $ilance->db->escape_string($ilance->GPC['locale']) . "',
			author = '" . $ilance->db->escape_string($ilance->GPC['author']) . "',
			textdirection = '" . $ilance->db->escape_string($ilance->GPC['textdirection']) . "',
			languageiso = '" . $ilance->db->escape_string($ilance->GPC['languageiso']) . "',
			canselect = '" . intval($ilance->GPC['canselect']) . "',
			replacements = '" . $ilance->db->escape_string($ilance->GPC['replacements']) . "'
			WHERE languageid = '" . intval($ilance->GPC['id']) . "'
		");
		$lcount = $ilance->db->query("SELECT COUNT(*) AS count FROM " . DB_PREFIX . "language");
		$rcount = $ilance->db->fetch_array($lcount);
		$languagecount = $rcount['count'];
		// remember to set as default if admin wants this to be default
		// to be safe, if there is only 1 language, this will not update the core configuration as the admin
		// might be setting the default language to NONE if there is only 1 language which should be the primary language to use!
		if ($ilconfig['globalserverlanguage_defaultlanguage'] != $ilance->GPC['id'] AND $languagecount > 1)
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "configuration
				SET value = '" . intval($ilance->GPC['id']) . "'
				WHERE name = 'globalserverlanguage_defaultlanguage'
			");
		}
		print_action_success('{_you_have_updated_the_language_and_new_settings_have_been_applied}', $ilpage['language']);
		exit();
	}
	// #### updating language
	else
	{
		$sql = $ilance->db->query("
			SELECT languageid, title, charset, author, locale, textdirection, languageiso, canselect, replacements
			FROM " . DB_PREFIX . "language
			WHERE languageid = '" . intval($ilance->GPC['id']) . "'
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$title = $res['title'];
			$id = $res['languageid'];
			$charset = $res['charset'];
			$author = $res['author'];
			$locale = $res['locale'];
			$languageiso = $res['languageiso'];
			$replacements = $res['replacements'];
			if ($ilconfig['globalserverlanguage_defaultlanguage'] == $res['languageid'])
			{
				$defaultlanguage0 = '';
				$defaultlanguage1 = 'selected="selected"';
			}
			else
			{
				$defaultlanguage0 = 'selected="selected"';
				$defaultlanguage1 = '';
			}
			if ($res['textdirection'] == 'rtl')
			{
				$textdirection0 = '';
				$textdirection1 = 'selected="selected"';
			}
			else
			{
				$textdirection0 = 'selected="selected"';
				$textdirection1 = '';
			}
			if ($res['canselect'])
			{
				$canselect0 = '';
				$canselect1 = 'selected="selected"';
			}
			else
			{
				$canselect0 = 'selected="selected"';
				$canselect1 = '';
			}
		}
		$pprint_array = array ('products_pulldown', 'replacements', 'canselect0', 'canselect1', 'languageiso', 'textdirection0', 'textdirection1', 'masterphrases', 'customphrases', 'movedphrases', 'totalphrases', 'adminuser', 'id', 'author', 'title', 'locale', 'charset', 'defaultlanguage0', 'defaultlanguage1', 'global_languagesettings', 'language_pulldown2', 'phrases_selectlist', 'keyword', 'base_language_pulldown', 'limit_pulldown', 'phrasegroup_pulldown', 'language_pulldown');
		
		($apihook = $ilance->api('admincp_language_end')) ? eval($apihook) : false;
	
		$ilance->template->fetch('main', 'language.html', 1);
		$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
		$ilance->template->parse_loop('main', 'mysqlcharset');
		$ilance->template->parse_loop('main', 'mysqlcollation');
		$ilance->template->parse_loop('main', 'installedlanguages');
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
}
else if ($ilance->GPC['cmd'] == 'locations')
{
	$area_title = '{_locations_administration_menu}';
	$page_title = SITE_NAME . ' - {_locations_administration_menu}';
    
	($apihook = $ilance->api('admincp_locations_management')) ? eval($apihook) : false;
    
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['language'], $ilpage['language'] . '?cmd=locations', $_SESSION['ilancedata']['user']['slng']);
	if (isset($ilance->GPC['subcmd']))
	{
		if ($show['ADMINCP_TEST_MODE'])
		{
			print_action_failed('{_demo_mode_only}', $ilpage['components']);
			exit();
		}
		if ($ilance->GPC['subcmd'] == 'location')
		{
			if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'edit' AND isset($ilance->GPC['id']) AND is_numeric($ilance->GPC['id']))
			{
				$id = $ilance->db->escape_string($ilance->GPC['id']);
				$langs_table = '';
				$sql_lang = $ilance->db->query("
					SELECT languagecode
					FROM " . DB_PREFIX . "language
				");
				while ($lang = $ilance->db->fetch_array($sql_lang, DB_ASSOC))
				{
					$slng = substr($lang['languagecode'], 0, 3);
					$sql = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "locations
						WHERE locationid = '" . $id . "'
					");
					while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
					{
						$activecb = '<input type="checkbox" name="visible" value="1"' . (($res['visible']) ? ' checked="checked"' : '') . ' />';
						$langs_table .= '<tr class="alt1"><td nowrap="nowrap" width="20%"><strong>' . ucfirst($lang['languagecode']) . '</strong></td><td class="alt2" width="80%"><input type="text" name="location_' . $slng . '" id="location_' . $slng . '" value="' . $res['location_' . $slng] . '" class="input" /></td></tr>';
					}
				}
				$pprint_array = array('activecb','id', 'langs_table', 'ilanceversion', 'login_include_admin');
		
				($apihook = $ilance->api('admincp_locations_edit_end')) ? eval($apihook) : false;
		
				$ilance->template->fetch('main', 'locations_edit.html', 1);
				$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
				$ilance->template->parse_loop('main', array ('v3nav', 'subnav_settings'));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->pprint('main', $pprint_array);
				exit();
			}
			else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'save' AND isset($ilance->GPC['id']) AND is_numeric($ilance->GPC['id']))
			{
				$location = '';
				$id = intval($ilance->GPC['id']);
				$visible = isset($ilance->GPC['visible']) ? '1' : '0';
				$sql_lang = $ilance->db->query("
					SELECT languagecode
					FROM " . DB_PREFIX . "language
				");
				while ($lang = $ilance->db->fetch_array($sql_lang, DB_ASSOC))
				{
					$slng = substr($lang['languagecode'], 0, 3);
					if (isset($ilance->GPC['location_' . $slng]))
					{
						$translation = $ilance->db->escape_string($ilance->GPC['location_' . $slng]);
						$location .= "location_$slng = '" . $translation . "', ";
					}
				}
				if (!empty($location))
				{
					$location = substr($location, 0, -2);
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "locations 
						SET $location,
						visible = '" . intval($visible). "'
						WHERE locationid = '" . $id . "'
						LIMIT 1
					");
				}
				print_action_success('{_location_updated}', $ilpage['language'] . '?cmd=locations');
				exit();
			}
			else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'save_locations')
			{
				$regionid = isset($ilance->GPC['regionid']) ? intval($ilance->GPC['regionid']) : 0;
				$ilance->GPC['visible'] = isset($ilance->GPC['visible']) ? $ilance->GPC['visible'] : array();
				if ($regionid <= 0)
				{
					print_action_failed('Please select a region filter.', urldecode($ilance->GPC['returnurl']));
				}
				$ilance->db->query("UPDATE " . DB_PREFIX . "locations SET visible = '1' WHERE regionid = '" . intval($ilance->GPC['regionid']) . "'");
				$sql = $ilance->db->query("
					SELECT locationid
					FROM " . DB_PREFIX . "locations
					WHERE regionid = '" . intval($ilance->GPC['regionid']) . "'
				");
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					if (!in_array($res['locationid'], $ilance->GPC['visible']))
					{
						$ilance->db->query("UPDATE " . DB_PREFIX . "locations SET visible = '0' WHERE locationid = '" . $res['locationid'] . "'");
					}
				}
				print_action_success('{_location_updated}', urldecode($ilance->GPC['returnurl']));
				exit();
			}
		}
		else if ($ilance->GPC['subcmd'] == 'state')
		{
			if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'save_states')
			{
				$cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
				$ilance->GPC['visible'] = isset($ilance->GPC['visible']) ? $ilance->GPC['visible'] : array();
				if ($cid <= 0)
				{
					print_action_failed('No country filter was selected.', urldecode($ilance->GPC['returnurl']));
					exit();
				}
				$ilance->db->query("UPDATE " . DB_PREFIX . "locations_states SET visible = '1' WHERE locationid = '" . intval($cid) . "'");
				$sql = $ilance->db->query("
					SELECT id
					FROM " . DB_PREFIX . "locations_states
					WHERE locationid = '" . intval($cid) . "'
				");
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					if (!in_array($res['id'], $ilance->GPC['visible']))
					{
						$ilance->db->query("UPDATE " . DB_PREFIX . "locations_states SET visible = '0' WHERE id = '" . $res['id'] . "'");
					}
				}
				print_action_success('{_location_updated}', urldecode($ilance->GPC['returnurl']));
				exit();
			}
		}
		else if ($ilance->GPC['subcmd'] == 'city')
		{
			if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'save_cities')
			{
				$cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
				$state = isset($ilance->GPC['state']) ? $ilance->GPC['state'] : '';
				$ilance->GPC['visible'] = isset($ilance->GPC['visible']) ? $ilance->GPC['visible'] : array();
				if ($cid <= 0 OR empty($state))
				{
					print_action_failed('No country or state filter was selected.', urldecode($ilance->GPC['returnurl']));
				}
				$ilance->db->query("UPDATE " . DB_PREFIX . "locations_cities SET visible = '1' WHERE state = '" . $ilance->db->escape_string($state) . "'");
				$sql = $ilance->db->query("
					SELECT id
					FROM " . DB_PREFIX . "locations_cities
					WHERE state = '" . $ilance->db->escape_string($state) . "'
				");
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					if (!in_array($res['id'], $ilance->GPC['visible']))
					{
						$ilance->db->query("UPDATE " . DB_PREFIX . "locations_cities SET visible = '0' WHERE id = '" . $res['id'] . "'");
					}
				}
				print_action_success('{_location_updated}', urldecode($ilance->GPC['returnurl']));
				exit();
			}
			else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'import_cities')
			{
				if (isset($ilance->GPC['state']) AND !empty($ilance->GPC['state']) AND isset($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0)
				{
					$ciso = $ilance->distance->countries[$ilance->GPC['cid']]; // RO
					$distancedb = $ilance->distance->dbtables["$ciso"]; // distance_ro
					if (!empty($distancedb))
					{
						if ($ilance->GPC['cid'] == '330')
						{
							$statefield = 'Province';
						}
						else
						{
							$statefield = 'State';
						}
						$sql = $ilance->db->query("
							SELECT City
							FROM " . DB_PREFIX . $distancedb . "
							WHERE $statefield = '" . $ilance->db->escape_string($ilance->GPC['state']) . "'
							GROUP BY City
						");
						if ($ilance->db->num_rows($sql) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
							{
								$ilance->db->query("
									INSERT INTO " . DB_PREFIX . "locations_cities
									(`id`, `locationid`, `state`, `city`, `visible`)
									VALUES (
									NULL,
									'" . intval($ilance->GPC['cid']) . "',
									'" . $ilance->db->escape_string($ilance->GPC['state']) . "',
									'" . $ilance->db->escape_string($res['City']) . "',
									'1')
								");
							}
							print_action_success('Cities imported successfully.', urldecode($ilance->GPC['returnurl']));
							exit();
						}
					}
				}
			}
		}
		else if ($ilance->GPC['subcmd'] == 'region')
		{
			if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'edit' AND isset($ilance->GPC['id']) AND is_numeric($ilance->GPC['id']))
			{
				$id = $ilance->db->escape_string($ilance->GPC['id']);
				$langs_table = '';
				$sql_lang = $ilance->db->query("
					SELECT languagecode
					FROM " . DB_PREFIX . "language
				");
				while ($lang = $ilance->db->fetch_array($sql_lang, DB_ASSOC))
				{
					$slng = substr($lang['languagecode'], 0, 3);
					$sql = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "locations_regions WHERE regionid = '" . $id . "'");
					while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
					{
						$langs_table .= '<tr class="alt1"><td nowrap="nowrap" width="20%"><strong>' . ucfirst($lang['languagecode']) . '</strong></td><td class="alt2"><input type="text" name="region_' . $slng . '" id="region_' . $slng . '" value="' . $res['region_' . $slng] . '" class="input" /></td></tr>';
					}
				}
				$pprint_array = array ('activecb','id', 'langs_table', 'ilanceversion', 'login_include_admin');
		
				($apihook = $ilance->api('admincp_regions_edit_end')) ? eval($apihook) : false;
		
				$ilance->template->fetch('main', 'locations_region_edit.html', 1);
				$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
				$ilance->template->parse_loop('main', array ('v3nav', 'subnav_settings'));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->pprint('main', $pprint_array);
				exit();
			}
			else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'save' AND isset($ilance->GPC['id']) AND is_numeric($ilance->GPC['id']))
			{
				$location = '';
				$id = $ilance->db->escape_string($ilance->GPC['id']);
				$sql_lang = $ilance->db->query("
					SELECT languagecode
					FROM " . DB_PREFIX . "language
				");
				while ($lang = $ilance->db->fetch_array($sql_lang, DB_ASSOC))
				{
					$slng = substr($lang['languagecode'], 0, 3);
					if (isset($ilance->GPC['region_' . $slng]))
					{
						$translation = $ilance->db->escape_string($ilance->GPC['region_' . $slng]);
						$location .= "region_$slng = '" . $translation . "', ";
					}
				}
				if (!empty($location))
				{
					$location = substr($location, 0, -2);
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "locations_regions
						SET $location  
						WHERE regionid = '" . $id . "'
					");
				}
				print_action_success('{_region_updated}', $ilpage['language'] . '?cmd=locations');
				exit();
			}
		}
	}
	$filterlocations = $filterstates = $filtercities = '';
	if (isset($ilance->GPC['regionid']) AND $ilance->GPC['regionid'] > 0)
	{
		$filterlocations .= "WHERE r.regionid = '" . intval($ilance->GPC['regionid']) . "'";
		$filterstates .= "AND r.regionid = '" . intval($ilance->GPC['regionid']) . "' ";
	}
	if (isset($ilance->GPC['country']) AND !empty($ilance->GPC['country']))
	{
		$filterstates .= "AND l.location_" . $_SESSION['ilancedata']['user']['slng'] . " = '" . $ilance->db->escape_string($ilance->GPC['country']) . "' ";
	}
	if (isset($ilance->GPC['country2']) AND !empty($ilance->GPC['country2']))
	{
		$filtercities .= "AND l.location_" . $_SESSION['ilancedata']['user']['slng'] . " = '" . $ilance->db->escape_string($ilance->GPC['country2']) . "' ";
		if (isset($ilance->GPC['state']) AND !empty($ilance->GPC['state']))
		{
			$filtercities .= "AND s.state = '" . $ilance->db->escape_string($ilance->GPC['state']) . "' ";
		}
	}
	$locations = $regions = $locations_states = $locations_cities = array();
	$sql_lang = $ilance->db->query("
		SELECT languagecode
		FROM " . DB_PREFIX . "language
	");
	while ($lang = $ilance->db->fetch_array($sql_lang, DB_ASSOC))
	{
		$langs[] = $lang['languagecode'];
	}
	unset($sql_lang);
	$table_head = $r_table_head = $l_table_head = '';
	$accepted_order = array();
	foreach ($langs AS $key => $value)
	{
		$slng = substr($value, 0, 3);
		$l_table_head .= '<td><b><a href="' . HTTPS_SERVER_ADMIN . $ilpage['language'] . '?cmd=locations&amp;lob=' . $slng . '&amp;lo=d">' . ucfirst($value) . '</a></b></td>';
		$r_table_head .= '<td><b><a href="' . HTTPS_SERVER_ADMIN . $ilpage['language'] . '?cmd=locations&amp;rob=' . $slng . '&amp;ro=d">' . ucfirst($value) . '</a></b></td>';
		$table_head .=  '<td><b>' . ucfirst($value) . '</b></td>';
		$accepted_order[] = $slng;
	}
	$region_table_head = '' . $table_head;
	$sql_r = $ilance->db->query("
		SELECT * 
		FROM " . DB_PREFIX . "locations_regions 
		ORDER BY region_eng
	");
	while ($res_r = $ilance->db->fetch_array($sql_r, DB_ASSOC))
	{
		$res_r['names'] = '';
		foreach ($langs AS $key => $value)
		{
			$res_r['names'] .= '<td>' . $res_r['region_' . substr($value, 0, 3)] . '</td>';
		}
		$res_r['actions'] = '<a href="' . $ilpage['language'] . '?cmd=locations&amp;subcmd=region&amp;action=edit&amp;id=' . $res_r['regionid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
		$regions[] = $res_r;
	}
	unset($sql_r, $res_r);
	$location_table_head = '' . $table_head;
	if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'countries')
	{
		$sql = $ilance->db->query("
			SELECT l.*, r.* 
			FROM " . DB_PREFIX . "locations l
			LEFT JOIN " . DB_PREFIX . "locations_regions r on (r.regionid = l.regionid)
			$filterlocations
			ORDER BY r.regionid
		");
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$res['names'] = '';
			foreach ($langs AS $key => $value)
			{
				$res['names'] .= '<td class="alt2">' . $res['location_' . substr($value, 0, 3)] . '</td>';
			}
			$res['visible'] = ($res['visible'] == '1') ? 'checked="checked"' : '';
			$res['actions'] = '<a href="' . $ilpage['language'] . '?cmd=locations&amp;subcmd=location&amp;action=edit&amp;id=' . $res['locationid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
			$res['region'] = $res['region_' . $slng];
			$locations[] = $res;
		}
	}
	unset($sql, $res);
	if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'states')
	{
		$sql = $ilance->db->query("
			SELECT s.visible, l.location_$slng AS country, r.region_$slng AS region, s.state, s.id
			FROM " . DB_PREFIX . "locations l
			LEFT JOIN " . DB_PREFIX . "locations_regions r on (r.regionid = l.regionid)
			LEFT JOIN " . DB_PREFIX . "locations_states s on (s.locationid = l.locationid)
			WHERE l.visible = '1'
			$filterstates
			ORDER BY r.regionid ASC, l.location_$slng ASC, s.state ASC
		");
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$res['visible'] = ($res['visible'] == '1') ? 'checked="checked"' : '';
			$locations_states[] = $res;
		}
	}
	unset($sql, $res);
	$country = isset($ilance->GPC['country']) ? $ilance->GPC['country'] : '';
	$country2 = isset($ilance->GPC['country2']) ? $ilance->GPC['country2'] : '';
	$state = isset($ilance->GPC['state']) ? $ilance->GPC['state'] : '';
	$cid = fetch_country_id($country, fetch_site_slng());
	$cid2 = fetch_country_id($country2, fetch_site_slng());
	$regionid = isset($ilance->GPC['regionid']) ? intval($ilance->GPC['regionid']) : '';
	$countrypulldownstates = $ilance->common_location->construct_country_pulldown($cid, $country, 'country', true, '', false, false, false, '', false, '', '', '', '', '', false, $regionid);
	$countrypulldowncities = $ilance->common_location->construct_country_pulldown($cid2, $country2, 'country2', false, 'state');
	$statepulldowncities = '<div id="stateid" style="height:20px">' . $ilance->common_location->construct_state_pulldown($cid2, $state, 'state') . '</div>';
	$show['canimportcities'] = false;
	$show['activecities'] = true;
	if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'cities')
	{
		$sql = $ilance->db->query("
			SELECT c.visible, l.location_$slng AS country, r.region_$slng AS region, c.city, c.id
			FROM " . DB_PREFIX . "locations_states s
			LEFT JOIN " . DB_PREFIX . "locations_cities c on (c.state = s.state)
			LEFT JOIN " . DB_PREFIX . "locations l on (l.locationid = s.locationid)
			LEFT JOIN " . DB_PREFIX . "locations_regions r on (r.regionid = l.regionid)
			WHERE l.visible = '1'
				AND c.city != ''
			$filtercities
			ORDER BY s.state ASC
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$res['visible'] = ($res['visible'] == '1') ? 'checked="checked"' : '';
				$locations_cities[] = $res;
			}
		}
		else
		{
			$show['activecities'] = false;
			if (in_array($cid2, $ilance->distance->accepted_countries))
			{
				$show['canimportcities'] = true;
			}
		}
	}
	unset($sql, $res);
	$pprint_array = array ('location_table_head','region_table_head','countrypulldownstates','cid','cid2','state','regionid','countrypulldowncities','statepulldowncities','country2');
    
	($apihook = $ilance->api('admincp_locations_end')) ? eval($apihook) : false;
    
	$ilance->template->fetch('main', 'locations.html', 1);
	$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
	$ilance->template->parse_loop('main', array ('locations', 'regions', 'locations_states', 'locations_cities'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>