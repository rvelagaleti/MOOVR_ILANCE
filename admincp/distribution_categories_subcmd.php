<?php
/* ==========================================================================*\
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
\*========================================================================== */
if (!defined('LOCATION') OR defined('LOCATION') != 'admin')
{
	die('<strong>Fatal:</strong> This script cannot be parsed indirectly.');
}
$question_types = array(
	'yesno' => '{_radio_selection_box_yes_or_no_type_question}', 
	'int' => '{_integer_field_numbers_only}',
	'textarea' => '{_text_area_field_multiline}',
	'text' => '{_input_text_field_singleline}',
	'url' => '{_url_singleline}',
	'multiplechoice' => '{_multiple_choice_enter_values_below}',
	'pulldown' => '{_pulldown_menu_enter_values_below}'
);
// #### UPDATE QUESTIONS CATEGORY SORTING ##############################
if ($ilance->GPC['subcmd'] == '_update-category-questions-sort')
{
	$table = ((isset($ilance->GPC['type']) AND $ilance->GPC['type'] == 'service') ? DB_PREFIX . 'project_questions' : DB_PREFIX . 'product_questions');
	if (isset($ilance->GPC['sort']))
	{
		foreach ($ilance->GPC['sort'] AS $key => $value)
		{
			$ilance->db->query("
				UPDATE $table
				SET sort = '" . intval($value) . "'
				WHERE questionid = '" . intval($key) . "'
				LIMIT 1
			");
		}
		print_action_success('{_question_sort_display_order_was_successfully_saved}', $ilance->GPC['return']);
		exit();
	}
}
// #### UPDATE SERVICE AUCTION CATEGORIES HANDLER ######################
else if ($ilance->GPC['subcmd'] == '_update-service-category')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$query1 = $query2 = $query3 = $query4 = $bidtypes = $bidfields = '';
	$usefixedfees = $fixedfeeamount = $nondisclosefeeamount = $multipleaward = $bidgroupdisplay = 0;
	if (!empty($ilance->GPC['title']))
	{
		foreach ($ilance->GPC['title'] AS $slng => $value)
		{
			$query1 .= "title_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "',";
		}
	}
	if (!empty($ilance->GPC['description']))
	{
		foreach ($ilance->GPC['description'] AS $slng => $value)
		{
			$query2 .= "description_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "',";
		}
	}
	if (!empty($ilance->GPC['keywords']))
	{
		foreach ($ilance->GPC['keywords'] AS $slng => $value)
		{
			$query3 .= "keywords_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "',";
		}
	}
	if (!empty($ilance->GPC['seourl']))
	{
		foreach ($ilance->GPC['seourl'] AS $slng => $value)
		{
			$query4 .= "seourl_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "',";
		}
	}
	$bidamounttypes = isset($ilance->GPC['bidamounttypes']) ? $ilance->GPC['bidamounttypes'] : '';
	$bidtypes = serialize($bidamounttypes);
	$bidfieldtypes = isset($ilance->GPC['bidfieldtypes']) ? $ilance->GPC['bidfieldtypes'] : '';
	if (!empty($bidfieldtypes))
	{
		$bidfields = serialize($bidfieldtypes);
	}
	if (isset($ilance->GPC['usefixedfees']) AND $ilance->GPC['usefixedfees'])
	{
		$usefixedfees = 1;
	}
	if (isset($ilance->GPC['fixedfeeamount']) AND $ilance->GPC['fixedfeeamount'] > 0)
	{
		$fixedfeeamount = $ilance->GPC['fixedfeeamount'];
	}
	if (isset($ilance->GPC['nondisclosefeeamount']) AND $ilance->GPC['nondisclosefeeamount'] > 0)
	{
		$nondisclosefeeamount = $ilance->GPC['nondisclosefeeamount'];
	}
	if (isset($ilance->GPC['multipleaward']) AND $ilance->GPC['multipleaward'])
	{
		$multipleaward = 1;
	}
	$bidgrouping = false;
	if (isset($ilance->GPC['bidgrouping']) AND $ilance->GPC['bidgrouping'])
	{
		$bidgrouping = true;
	}
	if (isset($ilance->GPC['bidgroupdisplay']))
	{
		$bidgroupdisplay = $ilance->GPC['bidgroupdisplay'];
	}
	$bachildren = isset($ilance->GPC['bachildren']) ? intval($ilance->GPC['bachildren']) : 0;
	$igchildren = isset($ilance->GPC['igchildren']) ? intval($ilance->GPC['igchildren']) : 0;
	$fvchildren = isset($ilance->GPC['fvchildren']) ? intval($ilance->GPC['fvchildren']) : 0;
	$brchildren = isset($ilance->GPC['brchildren']) ? intval($ilance->GPC['brchildren']) : 0;
	$ext = mb_substr($_FILES['catimage']['name'], strpos($_FILES['catimage']['name'],'.'), strlen($_FILES['catimage']['name'])-1);
	if (in_array($ext, array('.jpg', '.gif', '.bmp', '.png')) AND filesize($_FILES['catimage']['tmp_name']) < 65536)
	{
		if (move_uploaded_file($_FILES['catimage']['tmp_name'], DIR_SERVER_ROOT .  $ilconfig['template_imagesfolder'] . 'categoryicons/' . $_FILES['catimage']['name']) AND isset($ilance->GPC['category_icon_old']))
		{
			@unlink(DIR_SERVER_ROOT .  $ilconfig['template_imagesfolder'] . 'categoryicons/' . $ilance->GPC['category_icon_old']);
		}
	}
	$ext = mb_substr($_FILES['catimagehero']['name'], strpos($_FILES['catimagehero']['name'],'.'), strlen($_FILES['catimagehero']['name'])-1);
	if (in_array($ext, array('.jpg', '.gif', '.bmp', '.png')))
	{
		if (move_uploaded_file($_FILES['catimagehero']['tmp_name'], DIR_SERVER_ROOT .  $ilconfig['template_imagesfolder'] . 'categoryheros/' . $_FILES['catimagehero']['name']) AND isset($ilance->GPC['category_hero_old']))
		{
			@unlink(DIR_SERVER_ROOT .  $ilconfig['template_imagesfolder'] . 'categoryheros/' . $ilance->GPC['category_hero_old']);
		}
	}
	$ilance->GPC['catimage'] = isset($ilance->GPC['catimage']) ? $ilance->GPC['catimage'] : '';
	$ilance->GPC['catimagehero'] = isset($ilance->GPC['catimagehero']) ? $ilance->GPC['catimagehero'] : '';
	// #### final parent category pulldown checkup to be safe
	if (isset($ilance->GPC['cid']) AND isset($ilance->GPC['pid']) AND $ilance->GPC['cid'] == $ilance->GPC['pid'])
	{
		print_action_failed('{_the_category_you_are_trying_to_save_cannot_be_the_same_category}', 'javascript:history.back(1);');
		exit();
	}
	$sql = $ilance->db->query("
		SELECT parentid, level
		FROM " . DB_PREFIX . "categories
		WHERE cid = '" . intval($ilance->GPC['cid']) . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql))
	{
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		if ($ilance->GPC['pid'] != $res['parentid'])
		{
			$sql_new = $ilance->db->query("
				SELECT level
				FROM " . DB_PREFIX . "categories
				WHERE cid = '" . intval($ilance->GPC['pid']) . "'
			", 0, null, __FILE__, __LINE__);
			$res_new = $ilance->db->fetch_array($sql_new, DB_ASSOC);
			if ($res['level'] < $res_new['level'])
			{
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "categories
					SET parentid = '" . $res['parentid'] . "'
					WHERE cid = '" . intval($ilance->GPC['pid']) . "'
				", 0, null, __FILE__, __LINE__);
			}
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "categories
				SET parentid = '" . intval($ilance->GPC['pid']) . "'
				WHERE cid = '" . intval($ilance->GPC['cid']) . "'
			", 0, null, __FILE__, __LINE__);
		}
	}
	$cids = $ilance->categories->fetch_children_ids(intval($ilance->GPC['cid']), 'service');
	if ($bachildren AND !empty($cids))
	{
		$sql = $ilance->db->query("
			UPDATE " . DB_PREFIX . "categories
			SET bidamounttypes = '" . $ilance->db->escape_string($bidtypes) . "'
			WHERE cid IN (" . intval($ilance->GPC['cid']) . ",$cids)
		", 0, null, __FILE__, __LINE__);
	}
	if ($igchildren AND !empty($ilance->GPC['insertiongroup']) AND !empty($cids))
	{
		$sql = $ilance->db->query("
			UPDATE " . DB_PREFIX . "categories
			SET insertiongroup = '" . $ilance->db->escape_string($ilance->GPC['insertiongroup']) . "'
			WHERE cid IN (" . intval($ilance->GPC['cid']) . ",$cids)
		", 0, null, __FILE__, __LINE__);
	}
	if ($fvchildren AND !empty($ilance->GPC['finalvaluegroup']) AND !empty($cids))
	{
		$sql = $ilance->db->query("
			UPDATE " . DB_PREFIX . "categories
			SET finalvaluegroup = '" . $ilance->db->escape_string($ilance->GPC['finalvaluegroup']) . "'
			WHERE cid IN (" . intval($ilance->GPC['cid']) . ",$cids)
		", 0, null, __FILE__, __LINE__);
	}
	if ($brchildren AND !empty($ilance->GPC['budgetgroup']) AND !empty($cids))
	{
		$sql = $ilance->db->query("
			UPDATE " . DB_PREFIX . "categories
			SET budgetgroup = '" . $ilance->db->escape_string($ilance->GPC['budgetgroup']) . "'
			WHERE cid IN (" . intval($ilance->GPC['cid']) . ",$cids)
		", 0, null, __FILE__, __LINE__);
	}

	($apihook = $ilance->api('admincp_update_service_category_end')) ? eval($apihook) : false;

	$ilance->db->query("
		UPDATE " . DB_PREFIX . "categories
		SET parentid = '" . intval($ilance->GPC['pid']) . "',
		$query1
		$query2
		$query3
		$query4
		canpost = '" . intval($ilance->GPC['canpost']) . "',
		xml = '" . intval($ilance->GPC['xml']) . "',
		portfolio = '" . intval($ilance->GPC['portfolio']) . "',
		newsletter = '" . intval($ilance->GPC['newsletter']) . "',
		budgetgroup = '" . $ilance->db->escape_string($ilance->GPC['budgetgroup']) . "',
		insertiongroup = '" . $ilance->db->escape_string($ilance->GPC['insertiongroup']) . "',
		finalvaluegroup = '" . $ilance->db->escape_string($ilance->GPC['finalvaluegroup']) . "',
		cattype = 'service',
		bidamounttypes = '" . $ilance->db->escape_string($bidtypes) . "',
		bidfields = '" . $ilance->db->escape_string($bidfields) . "',
		usefixedfees = '" . intval($usefixedfees) . "',
		fixedfeeamount = '" . $ilance->db->escape_string($fixedfeeamount) . "',
		nondisclosefeeamount = '" . $ilance->db->escape_string($nondisclosefeeamount) . "',
		multipleaward = '" . intval($multipleaward) . "',
		bidgrouping = '" . intval($bidgrouping) . "',
		bidgroupdisplay = '" . $ilance->db->escape_string($bidgroupdisplay) . "',
		catimage = '" . $ilance->db->escape_string($_FILES['catimage']['name']) . "',
		catimagehero = '" . $ilance->db->escape_string($_FILES['catimagehero']['name']) . "',
		visible = '" . intval($ilance->GPC['visible']) . "',
		sort = '" . intval($ilance->GPC['sort']) . "'
		WHERE cid = '" . intval($ilance->GPC['cid']) . "'
	", 0, null, __FILE__, __LINE__);
	$ilance->GPC['scid'] = isset($ilance->GPC['scid']) ? intval($ilance->GPC['scid']) : 0;
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "skills
		SET rootcid = '0'
		WHERE rootcid = '" . intval($ilance->GPC['cid']) . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->GPC['scid'] > 0)
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "skills
			SET rootcid = '" . intval($ilance->GPC['cid']) . "'
			WHERE cid = '" . intval($ilance->GPC['scid']) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "skills
			SET rootcid = '" . intval($ilance->GPC['cid']) . "'
			WHERE parentid = '" . intval(intval($ilance->GPC['scid'])) . "'
		", 0, null, __FILE__, __LINE__);
	}
	// #### update the new level bit for the category tree structure
	$ilance->categories_manager->set_levels();
	$ilance->categories_manager->rebuild_category_tree(0, 1, 'service', $_SESSION['ilancedata']['user']['slng']);
	$ilance->categories_manager->rebuild_category_geometry();
	refresh($ilance->GPC['return']);
	exit();
}
// #### UPDATE PRODUCT AUCTION CATEGORIES HANDLER ######################
else if ($ilance->GPC['subcmd'] == '_update-product-category')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$query1 = $query2 = $query3 = $query4 = '';
	if (!empty($ilance->GPC['title']))
	{
		foreach ($ilance->GPC['title'] AS $slng => $value)
		{
			$query1 .= "title_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value)."',";
		}
	}
	if (!empty($ilance->GPC['description']))
	{
		foreach ($ilance->GPC['description'] AS $slng => $value)
		{
			$query2 .= "description_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "',";
		}
	}
	if (!empty($ilance->GPC['keywords']))
	{
		foreach ($ilance->GPC['keywords'] AS $slng => $value)
		{
			$query3 .= "keywords_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "',";
		}
	}
	if (!empty($ilance->GPC['seourl']))
	{
		foreach ($ilance->GPC['seourl'] AS $slng => $value)
		{
			$query4 .= "seourl_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "',";
		}
	}
	$xml = isset($ilance->GPC['xml']) ? intval($ilance->GPC['xml']) : 0;
	$portfolio = isset($ilance->GPC['portfolio']) ? intval($ilance->GPC['portfolio']) : 0;
	$newsletter = isset($ilance->GPC['newsletter']) ? intval($ilance->GPC['newsletter']) : 0;
	$ilance->GPC['catimage'] = isset($ilance->GPC['catimage']) ? $ilance->GPC['catimage'] : '';
	$ilance->GPC['catimagehero'] = isset($ilance->GPC['catimagehero']) ? $ilance->GPC['catimagehero'] : '';
	$ilance->GPC['catimageherourl'] = isset($ilance->GPC['catimageherourl']) ? $ilance->GPC['catimageherourl'] : '';
	$ilance->GPC['budgetgroup'] = isset($ilance->GPC['budgetgroup']) ? $ilance->GPC['budgetgroup'] : '';
	$ilance->GPC['finalvaluegroup'] = isset($ilance->GPC['finalvaluegroup']) ? $ilance->GPC['finalvaluegroup'] : '';
	$ilance->GPC['insertiongroup'] = isset($ilance->GPC['insertiongroup']) ? $ilance->GPC['insertiongroup'] : '';
	$ilance->GPC['incrementgroup'] = isset($ilance->GPC['incrementgroup']) ? $ilance->GPC['incrementgroup'] : '';
	$ilance->GPC['canpostclassifieds'] = isset($ilance->GPC['canpostclassifieds']) ? intval($ilance->GPC['canpostclassifieds']) : 1;
	$igchildren = isset($ilance->GPC['igchildren']) ? intval($ilance->GPC['igchildren']) : 0;
	$fvchildren = isset($ilance->GPC['fvchildren']) ? intval($ilance->GPC['fvchildren']) : 0;
	$bichildren = isset($ilance->GPC['bichildren']) ? intval($ilance->GPC['bichildren']) : 0;
	$useproxybid = isset($ilance->GPC['useproxybid']) ? intval($ilance->GPC['useproxybid']) : 0;
	$usereserveprice = isset($ilance->GPC['usereserveprice']) ? intval($ilance->GPC['usereserveprice']) : 0;
	$usehidebuynow = isset($ilance->GPC['usehidebuynow']) ? intval($ilance->GPC['usehidebuynow']) : 0;
	$useantisnipe = isset($ilance->GPC['useantisnipe']) ? intval($ilance->GPC['useantisnipe']) : 0;
	$catimage = $catimagehero = '';
	$ext = mb_substr($_FILES['catimage']['name'], strpos($_FILES['catimage']['name'],'.'), strlen($_FILES['catimage']['name'])-1);
	if (in_array($ext, array('.jpg', '.gif', '.bmp', '.png')) AND filesize($_FILES['catimage']['tmp_name']) < 65536)
	{
		if (move_uploaded_file($_FILES['catimage']['tmp_name'], DIR_SERVER_ROOT . $ilconfig['template_imagesfolder'] . 'categoryicons/' . $_FILES['catimage']['name']) AND isset($ilance->GPC['category_icon_old']))
		{
			@unlink(DIR_SERVER_ROOT .  $ilconfig['template_imagesfolder'] . 'categoryicons/' . $ilance->GPC['category_icon_old']);
			$catimage = "catimage = '" . $ilance->db->escape_string($_FILES['catimage']['name']) . "',";
		}
	}
	$ext = mb_substr($_FILES['catimagehero']['name'], strpos($_FILES['catimagehero']['name'],'.'), strlen($_FILES['catimagehero']['name'])-1);
	if (in_array($ext, array('.jpg', '.gif', '.bmp', '.png')))
	{
		if (move_uploaded_file($_FILES['catimagehero']['tmp_name'], DIR_SERVER_ROOT . $ilconfig['template_imagesfolder'] . 'categoryheros/' . $_FILES['catimagehero']['name']) AND isset($ilance->GPC['category_hero_old']))
		{
			@unlink(DIR_SERVER_ROOT .  $ilconfig['template_imagesfolder'] . 'categoryheros/' . $ilance->GPC['category_hero_old']);
			$catimagehero = "catimagehero = '" . $ilance->db->escape_string($_FILES['catimagehero']['name']) . "',";
		}
	}
	// #### final parent category pulldown checkup to be safe
	if (isset($ilance->GPC['cid']) AND isset($ilance->GPC['pid']) AND $ilance->GPC['cid'] == $ilance->GPC['pid'])
	{
		print_action_failed('{_the_category_you_are_trying_to_save_cannot_be_the_same_category}', 'javascript:history.back(1);');
		exit();
	}
	$sql = $ilance->db->query("
		SELECT parentid, level
		FROM " . DB_PREFIX . "categories
		WHERE cid = '" . intval($ilance->GPC['cid']) . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql))
	{
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		if ($ilance->GPC['pid'] != $res['parentid'])
		{
			$sql_new = $ilance->db->query("
				SELECT level
				FROM " . DB_PREFIX . "categories
				WHERE cid = '" . intval($ilance->GPC['pid']) . "'
			");
			$res_new = $ilance->db->fetch_array($sql_new, DB_ASSOC);
			if ($res['level'] < $res_new['level'])
			{
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "categories
					SET parentid = '" . $res['parentid'] . "'
					WHERE cid = '" . intval($ilance->GPC['pid']) . "'
				", 0, null, __FILE__, __LINE__);
			}
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "categories
				SET parentid = '" . intval($ilance->GPC['pid']) . "'
				WHERE cid = '" . intval($ilance->GPC['cid']) . "'
			", 0, null, __FILE__, __LINE__);
		}
	}
	$cids = $ilance->categories->fetch_children_ids(intval($ilance->GPC['cid']), 'product');
	if ($igchildren AND isset($ilance->GPC['insertiongroup']) AND !empty($cids))
	{
		$sql = $ilance->db->query("
			UPDATE " . DB_PREFIX . "categories
			SET insertiongroup = '" . $ilance->db->escape_string($ilance->GPC['insertiongroup']) . "'
			WHERE cid IN (" . intval($ilance->GPC['cid']) . ",$cids)
		", 0, null, __FILE__, __LINE__);
	}
	if ($fvchildren AND isset($ilance->GPC['finalvaluegroup']) AND !empty($cids))
	{
		$sql = $ilance->db->query("
			UPDATE " . DB_PREFIX . "categories
			SET finalvaluegroup = '" . $ilance->db->escape_string($ilance->GPC['finalvaluegroup']) . "'
			WHERE cid IN (" . intval($ilance->GPC['cid']) . ",$cids)
		", 0, null, __FILE__, __LINE__);
	}
	if ($bichildren AND isset($ilance->GPC['incrementgroup']) AND !empty($cids))
	{
		$sql = $ilance->db->query("
			UPDATE " . DB_PREFIX . "categories
			SET incrementgroup = '" . $ilance->db->escape_string($ilance->GPC['incrementgroup']) . "'
			WHERE cid IN (" . intval($ilance->GPC['cid']) . ",$cids)
		", 0, null, __FILE__, __LINE__);
	}

	($apihook = $ilance->api('admincp_update_product_category_end')) ? eval($apihook) : false;

	$ilance->db->query("
		UPDATE " . DB_PREFIX . "categories
		SET parentid = '" . intval($ilance->GPC['pid']) . "',
		$query1
		$query2
		$query3
		$query4
		canpost = '" . intval($ilance->GPC['canpost']) . "',
		canpostclassifieds = '" . intval($ilance->GPC['canpostclassifieds']) . "',
		xml = '" . $xml . "',
		portfolio = '" . $portfolio . "',
		newsletter = '" . $newsletter . "',
		budgetgroup = '" . $ilance->db->escape_string($ilance->GPC['budgetgroup']) . "',
		insertiongroup = '" . $ilance->db->escape_string($ilance->GPC['insertiongroup']) . "',
		finalvaluegroup = '" . $ilance->db->escape_string($ilance->GPC['finalvaluegroup']) . "',
		incrementgroup = '" . $ilance->db->escape_string($ilance->GPC['incrementgroup']) . "',
		cattype = 'product',
		$catimage
		$catimagehero
		catimageherourl = '" . $ilance->db->escape_string($ilance->GPC['catimageherourl']) . "',
		useproxybid = '" . $useproxybid . "',
		usereserveprice = '" . $usereserveprice . "',
		useantisnipe = '" . $useantisnipe . "',
		hidebuynow = '" . $usehidebuynow . "',
		visible = '" . intval($ilance->GPC['visible']) . "',
		sort = '" . intval($ilance->GPC['sort']) . "'
		WHERE cid = '" . intval($ilance->GPC['cid']) . "'
	", 0, null, __FILE__, __LINE__);
	$ilance->categories_manager->set_levels();
	$ilance->categories_manager->rebuild_category_tree(0, 1, 'product', $_SESSION['ilancedata']['user']['slng']);
	$ilance->categories_manager->rebuild_category_geometry();
	refresh($ilance->GPC['return']);
	exit();
}
// #### CREATE NEW SERVICE CATEGORY HANDLER ############################
else if ($ilance->GPC['subcmd'] == '_insert-service-category')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
	$pid = $cid;
	$customfields1 = $customfields2 = $customfields3 = $customfieldvalues1 = $customfieldvalues2 = $customfieldvalues3 = $keywordsfields = $keywordsvalues = $seourlfields = $seourlvalues = '';
	$titleerror = 0;
	if (!empty($ilance->GPC['title']))
	{
		$titlefields = $titlevalues = '';
		foreach ($ilance->GPC['title'] AS $slng => $title)
		{
			if (!empty($slng) AND !empty($title))
			{
				$titlefields .= 'title_' . $ilance->db->escape_string(mb_strtolower($slng)) . ', ';
				$titlevalues .= "'" . $ilance->db->escape_string($title) . "',";
			}
			else 
			{
				$titleerror = 1;
			}
		}
		if ($titleerror)
		{
			print_action_failed('{_sorry_to_create_a_new_category_you_must_define_a_title_for_all_available_languages_in_your_system}', $ilpage['distribution'] . '?cmd=categories&subcmd=addservicecat&cid=' . $cid);
			exit();	
		}
	}
	else 
	{
		print_action_failed('{_sorry_to_create_a_new_category_you_must_define_a_title_for_all_available_languages_in_your_system}', $ilpage['distribution'] . '?cmd=categories&subcmd=addservicecat&cid=' . $cid);
		exit();		
	}
	$descriptionerror = false;
	if (!empty($ilance->GPC['description']))
	{
		$descriptionfields = $descriptionvalues = '';
		foreach ($ilance->GPC['description'] AS $slng => $title)
		{
			if (!empty($slng) AND !empty($title))
			{
				$descriptionfields .= 'description_' . $ilance->db->escape_string(mb_strtolower($slng)) . ', ';
				$descriptionvalues .= "'" . $ilance->db->escape_string($title) . "',";
			}
			else 
			{
				$descriptionerror = true;
			}
		}		
	}
	if (!empty($ilance->GPC['keywords']))
	{
		$keywordsfields = $keywordsvalues = '';
		foreach ($ilance->GPC['keywords'] AS $slng => $title)
		{
			if (!empty($slng) AND !empty($title))
			{
				$keywordsfields .= 'keywords_' . $ilance->db->escape_string(mb_strtolower($slng)) . ', ';
				$keywordsvalues .= "'" . $ilance->db->escape_string($title) . "',";
			}
		}
	}
	if (!empty($ilance->GPC['seourl']))
	{
		$seourlfields = $seourlvalues = '';
		foreach ($ilance->GPC['seourl'] AS $slng => $title)
		{
			if (!empty($slng) AND !empty($title))
			{
				$seourlfields .= 'seourl_' . $ilance->db->escape_string(mb_strtolower($slng)) . ', ';
				$seourlvalues .= "'" . $ilance->db->escape_string($title) . "',";
			}
		}
	}
	$canpost = 0;
	if (isset($ilance->GPC['canpost']) AND $ilance->GPC['canpost'] == 1)
	{
		$canpost = 1;
	}
	$xml = 0;
	if (isset($ilance->GPC['xml']) AND $ilance->GPC['xml'] == 1)
	{
		$xml = 1;
	}
	$portfolio = 0;
	if (isset($ilance->GPC['portfolio']) AND $ilance->GPC['portfolio'] == 1)
	{
		$portfolio = 1;
	}
	$newsletter = 0;
	if (isset($ilance->GPC['newsletter']) AND $ilance->GPC['newsletter'] == 1)
	{
		$newsletter = 1;
	}
	$visible = 0;
	if (isset($ilance->GPC['visible']) AND $ilance->GPC['visible'] == 1)
	{
		$visible = 1;
	}
	$sort = isset($ilance->GPC['sort']) ? intval($ilance->GPC['sort']) : '1';
	$budgetgroup = isset($ilance->GPC['budgetgroup']) ? $ilance->GPC['budgetgroup'] : '';
	$insertiongroup = isset($ilance->GPC['insertiongroup']) ? $ilance->GPC['insertiongroup'] : '';
	$finalvaluegroup = isset($ilance->GPC['finalvaluegroup']) ? $ilance->GPC['finalvaluegroup'] : '';
	$cattype = isset($ilance->GPC['cattype']) ? $ilance->GPC['cattype'] : '';
	$bidamounttypes = isset($ilance->GPC['bidamounttypes']) ? $ilance->GPC['bidamounttypes'] : '';
	$bidtypes = '';
	if (!empty($bidamounttypes))
	{
		$bidtypes = serialize($bidamounttypes);
	}
	$bidfieldtypes = isset($ilance->GPC['bidfieldtypes']) ? $ilance->GPC['bidfieldtypes'] : '';
	$bidfields = '';
	if (!empty($bidfieldtypes))
	{
		$bidfields = serialize($bidfieldtypes);
	}
	$usefixedfees = 0;
	if (isset($ilance->GPC['usefixedfees']) AND $ilance->GPC['usefixedfees'] == 1)
	{
		$usefixedfees = 1;
	}
	$fixedfeeamount = 0;
	if (isset($ilance->GPC['fixedfeeamount']) AND $ilance->GPC['fixedfeeamount'] > 0)
	{
		$fixedfeeamount = $ilance->GPC['fixedfeeamount'];
	}
	$nondisclosefeeamount = 0;
	if (isset($ilance->GPC['nondisclosefeeamount']) AND $ilance->GPC['nondisclosefeeamount'] > 0)
	{
		$nondisclosefeeamount = $ilance->GPC['nondisclosefeeamount'];
	}
	$multipleaward = 0;
	if (isset($ilance->GPC['multipleaward']) AND $ilance->GPC['multipleaward'] == 1)
	{
		$multipleaward = 1;
	}
	$bidgrouping = false;
	if (isset($ilance->GPC['bidgrouping']) AND $ilance->GPC['bidgrouping'])
	{
		$bidgrouping = true;
	}
	$bidgroupdisplay = 'lowest';
	if (isset($ilance->GPC['bidgroupdisplay']))
	{
		$bidgroupdisplay = $ilance->GPC['bidgroupdisplay'];
	}
	$catimage = isset($ilance->GPC['catimage']) ? $ilance->db->escape_string($ilance->GPC['catimage']) : '';
	$catimagehero = isset($ilance->GPC['catimagehero']) ? $ilance->db->escape_string($ilance->GPC['catimagehero']) : '';

	($apihook = $ilance->api('admincp_insert_service_category_end')) ? eval($apihook) : false;

	// #### get the parent record ##########################
	$sql = $ilance->db->query("
		SELECT rgt
		FROM " . DB_PREFIX . "categories
		WHERE cid = '" . intval($cid) . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
		$parent = $ilance->db->fetch_array($sql, DB_ASSOC);
		if ($parent['rgt'] > 0)
		{
			// #### prepare the table for the insert
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "categories
				SET rgt = rgt + 2 
				WHERE rgt > " . intval($parent['rgt']) . "
					AND cattype = 'service'
			", 0, null, __FILE__, __LINE__);
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "categories
				SET lft = lft + 2
				WHERE lft > " . intval($parent['rgt']) . "
					AND cattype = 'service'
			", 0, null, __FILE__, __LINE__);
		}
	}
	else
	{
		$parent['rgt'] = 0; 
	}
	$ilance->db->query("ALTER TABLE " . DB_PREFIX . "categories DROP `sets`", 0, null, __FILE__, __LINE__);
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "categories
		(parentid, $titlefields $descriptionfields $seourlfields $customfields1 $customfields2 $customfields3 $keywordsfields canpost, xml, portfolio, newsletter, budgetgroup, insertiongroup, finalvaluegroup, cattype, bidamounttypes, bidfields, usefixedfees, fixedfeeamount, nondisclosefeeamount, multipleaward, bidgrouping, bidgroupdisplay, catimage, catimagehero, visible, sort, lft, rgt)
		VALUES(
		'" . $pid . "',
		$titlevalues
		$descriptionvalues
		$seourlvalues
		$customfieldvalues1
		$customfieldvalues2
		$customfieldvalues3
		$keywordsvalues
		'" . $canpost . "',
		'" . $xml . "',
		'" . $portfolio . "',
		'" . $newsletter . "',
		'" . $budgetgroup . "',
		'" . $insertiongroup . "',
		'" . $finalvaluegroup . "',
		'service',
		'" . $bidtypes . "',
		'" . $bidfields . "',
		'" . $usefixedfees . "',
		'" . $fixedfeeamount . "',
		'" . $nondisclosefeeamount . "',
		'" . $multipleaward . "',
		'" . $bidgrouping . "',
		'" . $bidgroupdisplay . "',
		'" . $catimage . "',
		'" . $catimagehero . "',
		'" . $visible . "',
		'" . $sort . "',
		'" . ($parent['rgt'] + 1) . "',
		'" . ($parent['rgt'] + 2). "')
	", 0, null, __FILE__, __LINE__);
	$newcid = $ilance->db->insert_id();
	$ilance->GPC['scid'] = isset($ilance->GPC['scid']) ? intval($ilance->GPC['scid']) : 0;
	if ($ilance->GPC['scid'] > 0)
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "skills
			SET rootcid = '" . intval($newcid) . "'
			WHERE cid = '" . intval($ilance->GPC['scid']) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);

		$ilance->db->query("
			UPDATE " . DB_PREFIX . "skills
			SET rootcid = '" . intval($newcid) . "'
			WHERE parentid = '" . intval($ilance->GPC['scid']) . "'
		", 0, null, __FILE__, __LINE__);
	}
	$ilance->db->add_field_if_not_exist(DB_PREFIX . "categories", 'sets', "LINESTRING NOT NULL", 'AFTER `parentid`', true);
	$ilance->categories_manager->set_levels();
	$ilance->categories_manager->rebuild_category_tree(0, 1, 'service', $_SESSION['ilancedata']['user']['slng']);
	$ilance->categories_manager->rebuild_category_geometry();
	print_action_success('{_new_category_was_added}', $ilpage['distribution'] . '?cmd=categories&cid2=' . $newcid);
	exit();
}
// #### CREATE NEW PRODUCT CATEGORY HANDLER ############################
else if ($ilance->GPC['subcmd'] == '_insert-product-category')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
	$pid = $cid;
	$customfields1 = $customfields2 = $customfields3 = $customfieldvalues1 = $customfieldvalues2 = $customfieldvalues3 = $keywordsfields = $keywordsvalues = $seourlfields = $seourlvalues = '';
	$canpost = $xml = $newsletter = $visible = $canpostclassifieds = 0;
	$titleerror = $descriptionerror = false;
	if (!empty($ilance->GPC['title']))
	{
		$titlefields = $titlevalues = '';
		foreach ($ilance->GPC['title'] AS $slng => $title)
		{
			if (!empty($slng) AND !empty($title))
			{
				$titlefields .= 'title_' . $ilance->db->escape_string(mb_strtolower($slng)) . ', ';
				$titlevalues .= "'" . $ilance->db->escape_string($title) . "',";
			}
			else 
			{
				$titleerror = true;
			}
		}
		if ($titleerror)
		{
			print_action_failed('{_sorry_to_create_a_new_category_you_must_define_a_title_for_all_available_languages_in_your_system}', $ilpage['distribution'] . '?cmd=categories&subcmd=addproductcat&cid='.$cid);
			exit();	
		}
	}
	else 
	{
		print_action_failed('{_sorry_to_create_a_new_category_you_must_define_a_title_for_all_available_languages_in_your_system}', $ilpage['distribution'] . '?cmd=categories&subcmd=addproductcat&cid='.$cid);
		exit();		
	}
	if (!empty($ilance->GPC['description']))
	{
		$descriptionfields = $descriptionvalues = '';
		foreach ($ilance->GPC['description'] AS $slng => $title)
		{
			if (!empty($slng) AND !empty($title))
			{
				$descriptionfields .= 'description_' . $ilance->db->escape_string(mb_strtolower($slng)) . ', ';
				$descriptionvalues .= "'" . $ilance->db->escape_string($title) . "',";
			}
			else 
			{
				$descriptionerror = true;
			}
		}
	}
	if (!empty($ilance->GPC['keywords']))
	{
		$keywordsfields = $keywordsvalues = '';
		foreach ($ilance->GPC['keywords'] AS $slng => $title)
		{
			if (!empty($slng) AND !empty($title))
			{
				$keywordsfields .= 'keywords_' . $ilance->db->escape_string(mb_strtolower($slng)) . ', ';
				$keywordsvalues .= "'" . $ilance->db->escape_string($title) . "',";
			}
		}
	}
	if (!empty($ilance->GPC['seourl']))
	{
		$seourlfields = $seourlvalues = '';
		foreach ($ilance->GPC['seourl'] AS $slng => $title)
		{
			if (!empty($slng) AND !empty($title))
			{
				$seourlfields .= 'seourl_' . $ilance->db->escape_string(mb_strtolower($slng)) . ', ';
				$seourlvalues .= "'" . $ilance->db->escape_string($title) . "',";
			}
		}
	}
	if (isset($ilance->GPC['canpost']) AND $ilance->GPC['canpost'])
	{
		$canpost = 1;
	}
	if (isset($ilance->GPC['canpostclassifieds']) AND $ilance->GPC['canpostclassifieds'])
	{
		$canpostclassifieds = 1;
	}
	if (isset($ilance->GPC['xml']) AND $ilance->GPC['xml'])
	{
		$xml = 1;
	}
	if (isset($ilance->GPC['newsletter']) AND $ilance->GPC['newsletter'])
	{
		$newsletter = 1;
	}
	if (isset($ilance->GPC['visible']) AND $ilance->GPC['visible'])
	{
		$visible = 1;
	}
	$sort = isset($ilance->GPC['sort']) ? intval($ilance->GPC['sort']) : '1';
	$useproxybid = isset($ilance->GPC['useproxybid']) ? intval($ilance->GPC['useproxybid']) : 0;
	$usereserveprice = isset($ilance->GPC['usereserveprice']) ? intval($ilance->GPC['usereserveprice']) : 0;
	$useantisnipe = isset($ilance->GPC['useantisnipe']) ? intval($ilance->GPC['useantisnipe']) : 0;
	$insertiongroup = isset($ilance->GPC['insertiongroup']) ? $ilance->GPC['insertiongroup'] : '';
	$finalvaluegroup = isset($ilance->GPC['finalvaluegroup']) ? $ilance->GPC['finalvaluegroup'] : '';
	$incrementgroup = isset($ilance->GPC['incrementgroup']) ? $ilance->GPC['incrementgroup'] : '';
	$cattype = isset($ilance->GPC['cattype']) ? $ilance->GPC['cattype'] : '';
	$bidamounttypes = isset($ilance->GPC['bidamounttypes']) ? $ilance->GPC['bidamounttypes'] : '';
	$bidtypes = !empty($bidamounttypes) ? serialize($bidamounttypes) : '';                        
	$catimage = isset($ilance->GPC['catimage']) ? $ilance->db->escape_string($ilance->GPC['catimage']) : '';
	$catimagehero = isset($ilance->GPC['catimagehero']) ? $ilance->db->escape_string($ilance->GPC['catimagehero']) : '';
	$catimageherourl = isset($ilance->GPC['catimageherourl']) ? $ilance->db->escape_string($ilance->GPC['catimageherourl']) : '';

	($apihook = $ilance->api('admincp_insert_product_category_end')) ? eval($apihook) : false;

	// #### get the parent record ##################################
	$sql = $ilance->db->query("
		SELECT rgt
		FROM " . DB_PREFIX . "categories
		WHERE cid = '" . intval($cid) . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
		$parent = $ilance->db->fetch_array($sql, DB_ASSOC);
		if ($parent['rgt'] > 0)
		{
			// #### prepare the table for the insert
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "categories
				SET rgt = rgt + 2 
				WHERE rgt > " . intval($parent['rgt']) . "
					AND cattype = 'product'
			", 0, null, __FILE__, __LINE__);
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "categories
				SET lft = lft + 2
				WHERE lft > " . intval($parent['rgt']) . "
					AND cattype = 'product'
			", 0, null, __FILE__, __LINE__);
		}
	}
	else
	{
		$parent['rgt'] = 0; 
	}
	$ilance->db->query("ALTER TABLE " . DB_PREFIX . "categories DROP `sets`", 0, null, __FILE__, __LINE__);
	// #### insert the record ######################################
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "categories
		(parentid, $titlefields $descriptionfields $seourlfields $customfields1 $customfields2 $customfields3 $keywordsfields canpost, canpostclassifieds, xml, newsletter, insertiongroup, finalvaluegroup, incrementgroup, cattype, bidamounttypes, useproxybid, usereserveprice, useantisnipe, catimage, catimagehero, catimageherourl, visible, sort, lft, rgt)
		VALUES(
		'" . $pid . "',
		$titlevalues
		$descriptionvalues
		$seourlvalues
		$customfieldvalues1
		$customfieldvalues2
		$customfieldvalues3
		$keywordsvalues
		'" . $canpost . "',
		'" . $canpostclassifieds . "',
		'" . $xml . "',
		'" . $newsletter . "',
		'" . $insertiongroup . "',
		'" . $finalvaluegroup . "',
		'" . $incrementgroup . "',
		'product',
		'" . $bidtypes . "',
		'" . $useproxybid . "',
		'" . $usereserveprice . "',
		'" . $useantisnipe . "',
		'" . $catimage . "',
		'" . $catimagehero . "',
		'" . $catimageherourl . "',
		'" . $visible . "',
		'" . $sort . "',
		'" . ($parent['rgt'] + 1) . "',
		'" . ($parent['rgt'] + 2) . "')
	", 0, null, __FILE__, __LINE__);
	$newcid = $ilance->db->insert_id();  
	$ilance->db->add_field_if_not_exist(DB_PREFIX . "categories", 'sets', "LINESTRING NOT NULL", 'AFTER `parentid`', true);
	$ilance->categories_manager->set_levels();
	$ilance->categories_manager->rebuild_category_tree(0, 1, 'product', $_SESSION['ilancedata']['user']['slng']);
	$ilance->categories_manager->rebuild_category_geometry();
	print_action_success('{_new_category_was_added}', $ilpage['distribution'] . '?cmd=categories&cid2=' . $newcid);
	exit();
}
// #### REMOVE SERVICE CATEGORY ########################################
else if ($ilance->GPC['subcmd'] == 'removeservicecat' AND isset($ilance->GPC['cid']))
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	if ($ilance->admincp_category->can_remove_categories())
	{
		$ilance->admincp_category->remove_category_recursive(intval($ilance->GPC['cid']), 'service');
		// update the new level bit for the category structure system.
		$ilance->categories_manager->set_levels();
		$ilance->categories_manager->rebuild_category_tree(0, 1, 'service', $_SESSION['ilancedata']['user']['slng']);
		$ilance->categories_manager->rebuild_category_geometry();
		print_action_success('{_category_was_removed_from_the_service_category_system_please_note}', $ilpage['distribution'] . "?cmd=categories");
		exit();
	}
	print_action_failed('{_sorry_you_must_have_at_least_1_category_in_the_system_please_update}', $ilpage['distribution'] . '?cmd=categories');
	exit();	
}
// #### REMOVE MULTIPLE SERVICE CATEGORY ###############################
else if ($ilance->GPC['subcmd'] == 'removeservicecats' AND isset($ilance->GPC['cids']) AND is_array($ilance->GPC['cids']))
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	if ($ilance->admincp_category->can_remove_categories())
	{
		foreach ($ilance->GPC['cids'] AS $catid)
		{
			$ilance->admincp_category->remove_category_recursive(intval($catid), 'service');
		}
		$ilance->categories_manager->set_levels();
		$ilance->categories_manager->rebuild_category_tree(0, 1, 'service', $_SESSION['ilancedata']['user']['slng']);
		$ilance->categories_manager->rebuild_category_geometry();
		print_action_success('{_category_was_removed_from_the_service_category_system_please_note}', $ilpage['distribution'] . "?cmd=categories");
		exit();
	}
	print_action_failed('{_sorry_you_must_have_at_least_1_category_in_the_system_please_update}', $ilpage['distribution'] . '?cmd=categories');
	exit();	
}
// #### REMOVE PRODUCT CATEGORY ########################################
else if ($ilance->GPC['subcmd'] == 'removeproductcat' AND isset($ilance->GPC['cid']))
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	if ($ilance->admincp_category->can_remove_categories())
	{
		$ilance->admincp_category->remove_category_recursive(intval($ilance->GPC['cid']), 'product');
		$ilance->categories_manager->set_levels();
		$ilance->categories_manager->rebuild_category_tree(0, 1, 'product', $_SESSION['ilancedata']['user']['slng']);
		$ilance->categories_manager->rebuild_category_geometry();
		print_action_success('{_category_was_removed_from_the_product_category_system_please}', $ilpage['distribution'] . '?cmd=categories');
		exit();
	}
	print_action_failed('{_sorry_you_must_have_at_least_1_category_in_the_system_please_update}', $ilpage['distribution'] . '?cmd=categories');
	exit();	
}
// #### REMOVE MULTIPLE PRODUCT CATEGORIES #############################
else if ($ilance->GPC['subcmd'] == 'removeproductcats' AND isset($ilance->GPC['cids']) AND is_array($ilance->GPC['cids']))
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	if ($ilance->admincp_category->can_remove_categories())
	{
		foreach ($ilance->GPC['cids'] AS $catid)
		{
			$ilance->admincp_category->remove_category_recursive(intval($catid), 'product');
		}
		$ilance->categories_manager->set_levels();
		$ilance->categories_manager->rebuild_category_tree(0, 1, 'product', $_SESSION['ilancedata']['user']['slng']);
		$ilance->categories_manager->rebuild_category_geometry();
		print_action_success('{_category_was_removed_from_the_product_category_system_please}', $ilpage['distribution'] . '?cmd=categories');
		exit();
	}
	print_action_failed('{_sorry_you_must_have_at_least_1_category_in_the_system_please_update}', $ilpage['distribution'] . '?cmd=categories');
	exit();	
}
else if ($ilance->GPC['subcmd'] == 'activecats' AND isset($ilance->GPC['cids']) AND is_array($ilance->GPC['cids']))
{
	foreach ($ilance->GPC['cids'] AS $catid)
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "categories
			SET
			visible = '1'
			WHERE cid = '" . $catid . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
	}
	print_action_success('{_updated}', $ilpage['distribution'] . '?cmd=categories');
	exit();
}
else if ($ilance->GPC['subcmd'] == 'inactivecats' AND isset($ilance->GPC['cids']) AND is_array($ilance->GPC['cids']))
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	foreach ($ilance->GPC['cids'] AS $catid)
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "categories
			SET
			visible = '0'
			WHERE cid = '" . $catid . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
	}
	print_action_success('{_updated}', $ilpage['distribution'] . '?cmd=categories');
	exit();
}
// #### EDIT SERVICE CATEGORY DETAILS ##################################
else if (($ilance->GPC['subcmd'] == 'editservicecat' OR $ilance->GPC['subcmd'] == 'addservicecat'))
{
	$area_title = '{_add_update_category}';
	$page_title = SITE_NAME . ' - {_add_update_category}';
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'] . '?cmd=categories', $_SESSION['ilancedata']['user']['slng']);
	$cid = intval($ilance->GPC['cid']);
	$slng = fetch_site_slng();
	$pagebit = isset($ilance->GPC['page2']) ? '&amp;page2=' . intval($ilance->GPC['page2']) : isset($ilance->GPC['page']) ? '&amp;page=' . intval($ilance->GPC['page']) : '';
	$return = $ilpage['distribution'] . '?cmd=categories' . $pagebit;
	if ($ilance->GPC['subcmd'] == 'editservicecat')
	{
		$cidfield = 'pid';
		// #### we are editing a particular question
		$submit = ($show['ADMINCP_TEST_MODE'])
			? '<input type="button" value="{_save}" class="buttons" style="font-size:15px" disabled="disabled" />'
			: '<input type="submit" value="{_save}" class="buttons" style="font-size:15px" />';
		$subcmd = '_update-service-category';
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "categories
			WHERE cid = '" . intval($ilance->GPC['cid']) . "'
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$pid = $res['parentid'];
				// #### BEGIN YES/NO ###################################
				$res['checked_canpost_1'] = '';
				$res['checked_canpost_0'] = 'checked="checked"';
				if ($res['canpost'])
				{
					$res['checked_canpost_1'] = 'checked="checked"';
					$res['checked_canpost_0'] = '';	
				}
				$res['checked_xml_1'] = '';
				$res['checked_xml_0'] = 'checked="checked"';
				if ($res['xml'])
				{
					$res['checked_xml_1'] = 'checked="checked"';
					$res['checked_xml_0'] = '';	
				}
				$res['checked_portfolio_1'] = '';
				$res['checked_portfolio_0'] = 'checked="checked"';
				if ($res['portfolio'])
				{
					$res['checked_portfolio_1'] = 'checked="checked"';
					$res['checked_portfolio_0'] = '';	
				}
				$res['checked_newsletter_1'] = '';
				$res['checked_newsletter_0'] = 'checked="checked"';
				if ($res['newsletter'])
				{
					$res['checked_newsletter_1'] = 'checked="checked"';
					$res['checked_newsletter_0'] = '';	
				}
				$res['checked_visible_1'] = '';
				$res['checked_visible_0'] = 'checked="checked"';
				if ($res['visible'])
				{
					$res['checked_visible_1'] = 'checked="checked"';
					$res['checked_visible_0'] = '';	
				}
				########################################################
				$res['insertionfeepulldown'] = $ilance->admincp->construct_insertion_group_pulldown($res['insertiongroup'], 'service');
				$res['budgetgrouppulldown'] = $ilance->admincp->construct_budget_group_pulldown($res['budgetgroup'], 'service');
				$res['finalvaluepulldown'] = $ilance->admincp->construct_finalvalue_group_pulldown($res['finalvaluegroup'], 'service');
				$res['skillspulldown'] = $ilance->categories_pulldown->print_cat_pulldown($ilance->db->fetch_field(DB_PREFIX . "skills", "rootcid = '" . intval($ilance->GPC['cid']) . "' AND level = '1' AND parentid = '0'", "cid"), 'skills', 'level', 'scid', 1, fetch_site_slng(), 1, '', 4, 1, 0, '540px', 0, 1, 0, false, false, $ilance->categories_skills->build_array_skills($_SESSION['ilancedata']['user']['slng']));
				$res['bidtypes'] = $ilance->admincp->construct_bidamounttypes(intval($ilance->GPC['cid']), 0);
				$res['bidfields'] = $ilance->bid_fields->print_bid_field_checkboxes(intval($ilance->GPC['cid']), $_SESSION['ilancedata']['user']['slng']);
				########################################################
				$res['checked_usefixedfees_1'] = '';
				$res['checked_usefixedfees_0'] = 'checked="checked"';
				if ($res['usefixedfees'])
				{
					$res['checked_usefixedfees_1'] = 'checked="checked"';
					$res['checked_usefixedfees_0'] = '';	
				}
				$res['checked_multipleaward_1'] = '';
				$res['checked_multipleaward_0'] = 'checked="checked"';
				if ($res['multipleaward'])
				{
					$res['checked_multipleaward_1'] = 'checked="checked"';
					$res['checked_multipleaward_0'] = '';       
				}
				$res['checked_bidgrouping_1'] = '';
				$res['checked_bidgrouping_0'] = 'checked="checked"';
				if ($res['bidgrouping'])
				{
					$res['checked_bidgrouping_1'] = 'checked="checked"';
					$res['checked_bidgrouping_0'] = '';
				}
				$res['checked_bidgroupdisplay_1'] = 'checked="checked"';
				$res['checked_bidgroupdisplay_0'] = '';
				if ($res['bidgroupdisplay'] == 'highest')
				{
					$res['checked_bidgroupdisplay_1'] = '';
					$res['checked_bidgroupdisplay_0'] = 'checked="checked"';
				}
				$res['nondisclosefee'] = '<input type="text" name="nondisclosefeeamount" value="' . $res['nondisclosefeeamount'] . '" class="input" size="5" />';
				if (isset($ilance->GPC['delete_icon']) AND ($ilance->GPC['delete_icon'] == '1') AND !empty($res['catimage']) AND isset($ilance->GPC['cid']))
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "categories
						SET `catimage` = ''
						WHERE `cid` = '" . intval($ilance->GPC['cid']) . "'
						LIMIT 1 ", 0, null, __FILE__, __LINE__);
					@unlink(DIR_SERVER_ROOT .  $ilconfig['template_imagesfolder'] . 'categoryicons/' . $ilance->GPC['category_icon_old']);
					print_action_success('{_category_icon_have_been_successfully_deleted}', $ilpage['distribution'] . '?cmd=categories&subcmd=editservicecat&cid=' . intval($ilance->GPC['cid']));
					exit();
				}
				$res['category_icon_old'] =  (!empty($res['catimage']) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'categoryicons/' . $res['catimage'] . '">&nbsp;&nbsp;<strong>' . $res['catimage'] . '</strong>' : '');
				$res['category_icon_old'] .= (empty($res['catimage']) ? '' : '<a href="' . $ilpage['distribution'] . '?cmd=categories&subcmd=editservicecat&cid=' . intval($ilance->GPC['cid']) . '&delete_icon=1" alt="{_delete}" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>');
				$res['category_icon_old'] .= '<input type="hidden" name="category_icon_old" value="' . $res['catimage'] . '" />';
				if (isset($ilance->GPC['delete_hero']) AND ($ilance->GPC['delete_hero'] == '1') AND !empty($res['catimagehero']) AND isset($ilance->GPC['cid']))
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "categories
						SET `catimagehero` = ''
						WHERE `cid` = '" . intval($ilance->GPC['cid']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					@unlink(DIR_SERVER_ROOT .  $ilconfig['template_imagesfolder'] . 'categoryheros/' . $ilance->GPC['category_hero_old']);
					print_action_success('{_category_icon_have_been_successfully_deleted}', $ilpage['distribution'] . '?cmd=categories&subcmd=editservicecat&cid=' . intval($ilance->GPC['cid']));
					exit();
				}
				$res['category_hero_old'] =  (!empty($res['catimagehero']) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'categoryheros/' . $res['catimagehero'] . '">&nbsp;&nbsp;<strong>' . $res['catimagehero'] . '</strong>' : '');
				$res['category_hero_old'] .= (empty($res['catimagehero']) ? '' : '<a href="' . $ilpage['distribution'] . '?cmd=categories&subcmd=editservicecat&cid=' . intval($ilance->GPC['cid']) . '&delete_hero=1" alt="{_delete}" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>');
				$res['category_hero_old'] .= '<input type="hidden" name="category_hero_old" value="' . $res['catimagehero'] . '" />';
				$servicecategory[] = $res;
			}
		}
		// multilanguage question and description
		$row_count = 0;
		$languages = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "language
		");
		while ($language = $ilance->db->fetch_array($languages, DB_ASSOC))
		{
			$language['slng'] = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
			$language['language'] = $language['title'];
			// fetch title in this language
			$sql = $ilance->db->query("
				SELECT title_$language[slng] AS title,
				description_$language[slng] AS description,
				keywords_$language[slng] AS keywords,
				seourl_$language[slng] AS seourl
				FROM " . DB_PREFIX . "categories
				WHERE cid = '" . intval($cid) . "'
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					$language['title'] = $res['title'];
					$language['description'] = $res['description'];
					$language['keywords'] = $res['keywords'];
					$language['seourl'] = $res['seourl'];
				}
			}
			$servicelanguages[] = $language;
			$row_count++;
		}
	}
	// #### adding new service cat #########################
	else if ($ilance->GPC['subcmd'] == 'addservicecat')
	{
		$cidfield = 'cid';
		$cid = intval($ilance->GPC['cid']);
		// we are editing a particular question
		$submit = ($show['ADMINCP_TEST_MODE'])
			? '<input type="button" style="font-size:15px" value=" {_save} " class="buttons" disabled="disabled" />'
			: '<input type="submit" style="font-size:15px" value=" {_save} " class="buttons" />';

		$subcmd = '_insert-service-category';
		$res = array();
		// #### BEGIN YES/NO ###########################################
		$res['checked_canpost_1'] = '';
		$res['checked_canpost_0'] = 'checked="checked"';
		$res['checked_xml_1'] = '';
		$res['checked_xml_0'] = 'checked="checked"';
		$res['checked_portfolio_1'] = '';
		$res['checked_portfolio_0'] = 'checked="checked"';
		$res['checked_newsletter_1'] = '';
		$res['checked_newsletter_0'] = 'checked="checked"';
		$res['checked_visible_1'] = 'checked="checked"';
		$res['checked_visible_0'] = '';
		################################################################
		$res['insertionfeepulldown'] = $ilance->admincp->construct_insertion_group_pulldown('', 'service');
		$res['budgetgrouppulldown'] = $ilance->admincp->construct_budget_group_pulldown('', 'service');
		$res['finalvaluepulldown'] = $ilance->admincp->construct_finalvalue_group_pulldown('', 'service');
		$res['bidtypes'] = $ilance->admincp->construct_bidamounttypes('', 1);
		$res['bidfields'] = $ilance->bid_fields->print_bid_field_checkboxes('', $_SESSION['ilancedata']['user']['slng']);
		$res['skillspulldown'] = $ilance->categories_pulldown->print_cat_pulldown(0, 'skills', 'level', 'scid', 1, fetch_site_slng(), 1, '', 4, 1, 0, '540px', 0, 1, 0, false, false, $ilance->categories_skills->build_array_skills($_SESSION['ilancedata']['user']['slng']));
		################################################################
		$res['checked_usefixedfees_1'] = '';
		$res['checked_usefixedfees_0'] = 'checked="checked"';
		$res['fixedfeeamount'] = '0';
		$res['checked_multipleaward_1'] = '';
		$res['checked_multipleaward_0'] = 'checked="checked"';
		$res['checked_bidgrouping_1'] = '';
		$res['checked_bidgrouping_0'] = 'checked="checked"';
		$res['checked_bidgroupdisplay_1'] = 'checked="checked"';
		$res['checked_bidgroupdisplay_0'] = '';
		################################################################
		$res['catimage'] = '';
		$res['catimagehero'] = '';
		$res['title'] = '';
		$res['nondisclosefee'] = '<input type="text" name="nondisclosefeeamount" value="" class="input" size="5" />';
		$servicecategory[] = $res;
		// multilanguage question and description
		$row_count = 0;
		$languages = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "language
		");
		while ($language = $ilance->db->fetch_array($languages, DB_ASSOC))
		{
			$language['slng'] = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
			$language['language'] = $language['title'];
			$language['title'] = '';
			$language['seourl'] = '';
			$servicelanguages[] = $language;
			$row_count++;
		}
	}
	$pprint_array = array('return','cidfield','pid','submit','subcmd','question_inputtype_pulldown','questionid','cid','slng','categoryname','language_pulldown','slng','checked_question_cansearch','checked_question_active','checked_question_required','subcategory_pulldown','formdefault','multiplechoice','question','description','formname','sort','submit_category_question','question_id_hidden','question_subcmd','question_inputtype_pulldown','subcatid','subcatname','catname','service_subcategories','product_categories','subcmd','id','submit','description','name','checked_profile_group_active','category_icon_old','category_hero_old');

	($apihook = $ilance->api('admincp_service_category_details')) ? eval($apihook) : false;

	$ilance->template->fetch('main', 'categories_edit.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
	$ilance->template->parse_loop('main', array('servicecategory', 'servicelanguages'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();	
}
// #### EDIT PRODUCT CATEGORY DETAILS ##################################
else if (($ilance->GPC['subcmd'] == 'editproductcat' OR $ilance->GPC['subcmd'] == 'addproductcat'))
{
	$area_title = '{_add_update_category}';
	$page_title = SITE_NAME . ' - {_add_update_category}';;
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'] . '?cmd=categories', $_SESSION['ilancedata']['user']['slng']);
	$cid = intval($ilance->GPC['cid']);
	$slng = fetch_site_slng();
	$ilance->GPC['page'] = isset($ilance->GPC['page']) ? intval($ilance->GPC['page']) : 1;
	$pagebit = isset($ilance->GPC['page2']) ? '&amp;page2=' . intval($ilance->GPC['page2']) : '&amp;page=' . intval($ilance->GPC['page']);
	$return = $ilpage['distribution'] . '?cmd=categories' . $pagebit;
	// #### adding a new category ##########################
	if ($ilance->GPC['subcmd'] == 'addproductcat')
	{
		$cidfield = 'cid';
		// we are editing a particular question
		$submit = ($show['ADMINCP_TEST_MODE'])
			? '<input type="button" value=" {_save} " style="font-size:15px" class="buttons" disabled="disabled" />'
			: '<input type="submit" value=" {_save} " style="font-size:15px" class="buttons" />';

		$subcmd = '_insert-product-category';
		// #### BEGIN YES/NO ###########################################
		$res = array();
		$res['checked_canpost_1'] = 'checked="checked"';
		$res['checked_canpost_0'] = '';
		$res['checked_canpostclassifieds_1'] = 'checked="checked"';
		$res['checked_canpostclassifieds_0'] = '';
		$res['checked_xml_1'] = '';
		$res['checked_xml_0'] = 'checked="checked"';
		$res['checked_portfolio_1'] = '';
		$res['checked_portfolio_0'] = 'checked="checked"';
		$res['checked_newsletter_1'] = '';
		$res['checked_newsletter_0'] = 'checked="checked"';
		$res['checked_visible_1'] = 'checked="checked"';
		$res['checked_visible_0'] = '';
		$res['checked_useproxybid_1'] = '';
		$res['checked_useproxybid_0'] = 'checked="checked"';
		$res['checked_usereserveprice_1'] = 'checked="checked"';
		$res['checked_usereserveprice_0'] = '';
		$res['checked_useantisnipe_1'] = '';
		$res['checked_useantisnipe_0'] = 'checked="checked"';
		$res['checked_usehidebuynow_1'] = '';
		$res['checked_usehidebuynow_0'] = 'checked="checked"';
		################################################
		$res['catimage'] = '';
		$res['catimagehero'] = '';
		$res['catimageherourl'] = '';
		$res['sort'] = '';
		################################################
		$res['insertionfeepulldown'] = $ilance->admincp->construct_insertion_group_pulldown('', 'product');
		$res['finalvaluepulldown'] = $ilance->admincp->construct_finalvalue_group_pulldown('', 'product');
		$res['incrementpulldown'] = $ilance->admincp->construct_increment_group_pulldown('', 'product');
		$res['title'] = '';

		($apihook = $ilance->api('admincp_product_add_category_end')) ? eval($apihook) : false;

		$productcategory[] = $res;
		// multilanguage question and description
		$row_count = 0;
		$languages = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "language
		");
		while ($language = $ilance->db->fetch_array($languages, DB_ASSOC))
		{
			$language['slng'] = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
			$language['language'] = $language['title'];
			$language['title'] = $res['title'];
			$language['seourl'] = '';
			$productlanguages[] = $language;
			$row_count++;
		}
	}
	// #### editing the category details ###################
	else 
	{
		$cidfield = 'pid';
		if (isset($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0)
		{
			$submit = ($show['ADMINCP_TEST_MODE'])
				? '<input type="submit" style="font-size:15px" value="{_save}" class="buttons" disabled="disabled" />'
				: '<input type="submit" style="font-size:15px" value="{_save}" class="buttons" />';
			$subcmd = '_update-product-category';
			$sql = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "categories
				WHERE cid = '" . intval($ilance->GPC['cid']) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					$pid = $res['parentid'];
					$categoryname = $res["title_$slng"];
					$res['checked_canpost_1'] = '';
					$res['checked_canpost_0'] = 'checked="checked"';
					if ($res['canpost'])
					{
						$res['checked_canpost_1'] = 'checked="checked"';
						$res['checked_canpost_0'] = '';	
					}
					$res['checked_canpostclassifieds_1'] = '';
					$res['checked_canpostclassifieds_0'] = 'checked="checked"';
					if ($res['canpostclassifieds'])
					{
						$res['checked_canpostclassifieds_1'] = 'checked="checked"';
						$res['checked_canpostclassifieds_0'] = '';	
					}
					$res['checked_xml_1'] = '';
					$res['checked_xml_0'] = 'checked="checked"';
					if ($res['xml'])
					{
						$res['checked_xml_1'] = 'checked="checked"';
						$res['checked_xml_0'] = '';	
					}
					$res['checked_portfolio_1'] = '';
					$res['checked_portfolio_0'] = 'checked="checked"';
					if ($res['portfolio'])
					{
						$res['checked_portfolio_1'] = 'checked="checked"';
						$res['checked_portfolio_0'] = '';	
					}
					$res['checked_newsletter_1'] = '';
					$res['checked_newsletter_0'] = 'checked="checked"';
					if ($res['newsletter'])
					{
						$res['checked_newsletter_1'] = 'checked="checked"';
						$res['checked_newsletter_0'] = '';	
					}
					$res['checked_visible_1'] = '';
					$res['checked_visible_0'] = 'checked="checked"';
					if ($res['visible'])
					{
						$res['checked_visible_1'] = 'checked="checked"';
						$res['checked_visible_0'] = '';	
					}
					$res['checked_useproxybid_1'] = '';
					$res['checked_useproxybid_0'] = 'checked="checked"';
					if ($res['useproxybid'])
					{
						$res['checked_useproxybid_1'] = 'checked="checked"';
						$res['checked_useproxybid_0'] = '';	
					}
					$res['checked_usereserveprice_1'] = '';
					$res['checked_usereserveprice_0'] = 'checked="checked"';
					if ($res['usereserveprice'])
					{
						$res['checked_usereserveprice_1'] = 'checked="checked"';
						$res['checked_usereserveprice_0'] = '';	
					}
					$res['checked_useantisnipe_1'] = '';
					$res['checked_useantisnipe_0'] = 'checked="checked"';
					if ($res['useantisnipe'])
					{
						$res['checked_useantisnipe_1'] = 'checked="checked"';
						$res['checked_useantisnipe_0'] = '';	
					}
					$res['checked_usehidebuynow_1'] = '';
					$res['checked_usehidebuynow_0'] = 'checked="checked"';
					if ($res['hidebuynow'])
					{
						$res['checked_usehidebuynow_1'] = 'checked="checked"';
						$res['checked_usehidebuynow_0'] = '';	
					}
					$res['insertionfeepulldown'] = $ilance->admincp->construct_insertion_group_pulldown($res['insertiongroup'], 'product');
					$res['budgetgrouppulldown'] = $ilance->admincp->construct_budget_group_pulldown($res['budgetgroup'], 'product');
					$res['finalvaluepulldown'] = $ilance->admincp->construct_finalvalue_group_pulldown($res['finalvaluegroup'], 'product');
					$res['incrementpulldown'] = $ilance->admincp->construct_increment_group_pulldown($res['incrementgroup'], 'product');
					if (isset($ilance->GPC['delete_icon']) AND ($ilance->GPC['delete_icon'] == '1') AND !empty($res['catimage']) AND isset($ilance->GPC['cid']))
					{
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "categories
							SET `catimage` = ''
							WHERE `cid` = '" . intval($ilance->GPC['cid']) . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						@unlink(DIR_SERVER_ROOT .  $ilconfig['template_imagesfolder'] . 'categoryicons/' . $ilance->GPC['category_icon_old']);
						print_action_success('{_category_icon_have_been_successfully_deleted}', $ilpage['distribution'] . '?cmd=categories&subcmd=editproductcat&cid=' . intval($ilance->GPC['cid']));
						exit();
					}
					$res['category_icon_old'] =  (!empty($res['catimage']) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'categoryicons/' . $res['catimage'] . '">&nbsp;&nbsp;<strong>' . $res['catimage'] . '</strong>' : '');
					$res['category_icon_old'] .= (empty($res['catimage']) ? '' : '<a href="' . $ilpage['distribution'] . '?cmd=categories&subcmd=editproductcat&cid=' . intval($ilance->GPC['cid']) . '&amp;delete_icon=1" alt="{_delete}" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>');
					$res['category_icon_old'] .= '<input type="hidden" name="category_icon_old" value="' . $res['catimage'] . '" />';
					if (isset($ilance->GPC['delete_hero']) AND ($ilance->GPC['delete_hero'] == '1') AND !empty($res['catimagehero']) AND isset($ilance->GPC['cid']))
					{
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "categories
							SET catimagehero = '',
							catimageherourl = ''
							WHERE cid = '" . intval($ilance->GPC['cid']) . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						@unlink(DIR_SERVER_ROOT .  $ilconfig['template_imagesfolder'] . 'categoryheros/' . $ilance->GPC['category_hero_old']);
						print_action_success('{_category_icon_have_been_successfully_deleted}', $ilpage['distribution'] . '?cmd=categories&amp;subcmd=editproductcat&amp;cid=' . intval($ilance->GPC['cid']));
						exit();
					}
					$res['category_hero_old'] =  (!empty($res['catimagehero']) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'categoryheros/' . $res['catimagehero'] . '">&nbsp;&nbsp;<strong>' . $res['catimagehero'] . '</strong>' : '');
					$res['category_hero_old'] .= (empty($res['catimagehero']) ? '' : '<a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=editproductcat&amp;cid=' . intval($ilance->GPC['cid']) . '&amp;delete_hero=1" alt="{_delete}" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>');
					$res['category_hero_old'] .= '<input type="hidden" name="category_hero_old" value="' . $res['catimagehero'] . '" />';
					
					($apihook = $ilance->api('admincp_product_edit_category_end')) ? eval($apihook) : false;

					$productcategory[] = $res;
				}
			}
			// #### multilanguage question and description
			$row_count = 0;
			$languages = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "language
			");
			while ($language = $ilance->db->fetch_array($languages, DB_ASSOC))
			{
				$language['slng'] = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
				$language['language'] = $language['title'];
				// fetch title in this language
				$sql = $ilance->db->query("
					SELECT title_" . $language['slng'] . " AS title,
					description_" . $language['slng'] . " AS description,
					keywords_" . $language['slng'] . " AS keywords,
					seourl_" . $language['slng'] . " AS seourl
					FROM " . DB_PREFIX . "categories
					WHERE cid = '" . intval($cid) . "'
				");
				if ($ilance->db->num_rows($sql) > 0)
				{
					while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
					{
						$language['title'] = $res['title'];
						$language['description'] = $res['description'];
						$language['keywords'] = $res['keywords'];
						$language['seourl'] = $res['seourl'];
					}
				}
				$productlanguages[] = $language;
				$row_count++;
			}
		}
	}
	// custom product bid increment logic
	$sqlincrements = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "increments
		WHERE (cid = '" . intval($cid) . "')
		ORDER BY incrementid ASC
	");
	if ($ilance->db->num_rows($sqlincrements) > 0)
	{
		$row_count = 0;
		$show['no_increments'] = false;
		while ($rows = $ilance->db->fetch_array($sqlincrements, DB_ASSOC))
		{
			$rows['from'] = $ilance->currency->format($rows['increment_from']);
			$rows['to'] = $ilance->currency->format($rows['increment_to']);
			$rows['amount'] = $ilance->currency->format($rows['amount']);
			$rows['actions'] = '<a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=editproductcat&amp;cid=' . $cid . '&amp;do=editincrement&amp;id='.$rows['incrementid'].'#edit"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a> &nbsp; <a href="' . $ilpage['distribution'] . '?cmd=bids&amp;subcmd=_remove-increment&amp;id='.$rows['incrementid'].'&amp;cid=' . $cid . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
			$rows['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$increments[] = $rows;
			$row_count++;
		}
	}
	else
	{
		$show['no_increments'] = true;
	}
	$pprint_array = array('return','cidfield','pid','inchidden','incform','incsubmit','inshidden','insform','inssubmit','incamount','incto','incfrom','insamount','insto','insfrom','submit','subcmd','question_inputtype_pulldown','questionid','cid','slng','categoryname','language_pulldown','slng','checked_question_cansearch','checked_question_active','checked_question_required','subcategory_pulldown','formdefault','multiplechoice','question','description','formname','sort','submit_category_question','question_id_hidden','question_subcmd','question_inputtype_pulldown','subcatid','subcatname','catname','service_subcategories','product_categories','subcmd','id','submit','description','name','checked_profile_group_active','category_icon_old','category_hero_old',);

	($apihook = $ilance->api('admincp_product_category_details')) ? eval($apihook) : false;

	$ilance->template->fetch('main', 'categories_edit.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
	$ilance->template->parse_loop('main', array('productcategory', 'productlanguages', 'increments'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();	
}
// #### DELETE SERVICE CATEGORY QUESTION ###############################
else if ($ilance->GPC['subcmd'] == '_remove-service-question')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$ilance->db->query("
		DELETE FROM " . DB_PREFIX . "project_answers
		WHERE questionid = '" . intval($ilance->GPC['qid']) . "'
	", 0, null, __FILE__, __LINE__);
	$ilance->db->query("
		DELETE FROM " . DB_PREFIX . "project_questions
		WHERE questionid = '" . intval($ilance->GPC['qid']) . "'
		LIMIT 1
	");
	$ilance->db->query("
		DELETE FROM " . DB_PREFIX . "project_questions_choices
		WHERE questionid = '" . intval($ilance->GPC['qid']) . "'
	", 0, null, __FILE__, __LINE__);
	print_action_success('{_category_question_was_removed_from_the_system}', $ilpage['distribution'] . '?cmd=categories&amp;subcmd=servicequestions&amp;cid='.$ilance->GPC['cid']);
	exit();
}
// #### REMOVE PRODUCT CATEGORY QUESTIONS ##############################
else if ($ilance->GPC['subcmd'] == '_remove-product-question')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$ilance->db->query("
		DELETE FROM " . DB_PREFIX . "product_answers
		WHERE questionid = '" . intval($ilance->GPC['qid']) . "'
	", 0, null, __FILE__, __LINE__);
	$ilance->db->query("
		DELETE FROM " . DB_PREFIX . "product_questions
		WHERE questionid = '" . intval($ilance->GPC['qid']) . "'
	", 0, null, __FILE__, __LINE__);
	$ilance->db->query("
		DELETE FROM " . DB_PREFIX . "product_questions_choices
		WHERE questionid = '" . intval($ilance->GPC['qid']) . "'
	", 0, null, __FILE__, __LINE__);
	print_action_success('{_category_question_was_removed_from_the_system}', $ilpage['distribution'] . '?cmd=categories&amp;subcmd=productquestions&amp;cid=' . $ilance->GPC['cid']);
	exit();
}
// #### CREATE NEW SERVICE CATEGORY QUESTION ###########################
else if ($ilance->GPC['subcmd'] == '_insert-service-question')
{
	$visible = isset($ilance->GPC['visible']) ? intval($ilance->GPC['visible']) : '0';
	$required = isset($ilance->GPC['required']) ? intval($ilance->GPC['required']) : '0';
	$cansearch = isset($ilance->GPC['cansearch']) ? intval($ilance->GPC['cansearch']) : '0';
	$recursive = isset($ilance->GPC['recursive']) ? intval($ilance->GPC['recursive']) : '0';
	$guests = isset($ilance->GPC['guests']) ? intval($ilance->GPC['guests']) : '0';
	$sort = isset($ilance->GPC['sort']) ? intval($ilance->GPC['sort']) : '0';
	$formname = construct_form_name(14);
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "project_questions
		(cid, formname, formdefault, inputtype, sort, visible, required, cansearch, recursive, guests)
		VALUES(
		'" . intval($ilance->GPC['cid']) . "',
		'" . $ilance->db->escape_string($formname) . "',
		'" . $ilance->db->escape_string($ilance->GPC['formdefault']) . "',
		'" . $ilance->db->escape_string($ilance->GPC['inputtype']) . "',
		'" . $sort . "',
		'" . $visible . "',
		'" . $required . "',
		'" . $cansearch . "',
		'" . $recursive . "',
		'" . $guests . "')
	", 0, null, __FILE__, __LINE__);
	$insid = $ilance->db->insert_id();
	$query1 = $query2 = $query3 = $query4 = $value3 = $value4 = '';
	if (!empty($ilance->GPC['question']) AND !empty($ilance->GPC['description']))
	{
		foreach ($ilance->GPC['question'] AS $slng => $value)
		{
			$query1 .= "question_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "',";
		}
		foreach ($ilance->GPC['description'] AS $slng => $value)
		{
			$query2 .= "description_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "',";
		}
	}
	if (isset($ilance->GPC['multiplechoice']) AND is_array($ilance->GPC['multiplechoice']))
	{
		foreach ($ilance->GPC['multiplechoice'] AS $slng => $multiplechoice)
		{
			$query3 .= "choice_" . mb_strtolower($slng) . ", ";
		}
		foreach ($ilance->GPC['multiplechoice'] AS $slng => $multiplechoice)
		{
			foreach ($multiplechoice AS $key => $choice)
			{
				if ($choice != '')
				{
					$value3[$key][] = "'" . $ilance->db->escape_string($choice) . "', ";
				}
			}
		}
		$string = '';
		$c = 0;
		foreach ($ilance->GPC['multiplechoice'] AS $slng => $multiplechoice)
		{
			foreach ($multiplechoice AS $keyy => $valuee)
			{
				if (isset($value3[$c]) AND is_array($value3[$c]))
				{
					foreach ($value3[$c] AS $key => $value)
					{
						$string .= $value;
					}
					$parentoptionid = isset($ilance->GPC['multiplechoicegroup'][$c]) ? $ilance->GPC['multiplechoicegroup'][$c] : 0;
					$sort = isset($ilance->GPC['multiplechoiceorder'][$c]) ? $ilance->GPC['multiplechoiceorder'][$c] : 0;
					$ilance->db->query("
						INSERT INTO " . DB_PREFIX . "project_questions_choices
						(optionid, parentoptionid, questionid, {$query3}sort, visible)
						VALUES(
						NULL,
						'" . intval($parentoptionid) . "', 
						'" . intval($insid) . "',
						$string
						'" . intval($sort) . "',
						'1')
					", 0, null, __FILE__, __LINE__);
					$string = '';
					$c++;
				}
			}
		}
	}
	if (isset($ilance->GPC['newmultiplechoice']) AND is_array($ilance->GPC['newmultiplechoice']))
	{
		foreach ($ilance->GPC['newmultiplechoice'] AS $slng => $newmultiplechoice)
		{
			$query4 .= "choice_" . mb_strtolower($slng) . ", ";
		}
		foreach ($ilance->GPC['newmultiplechoice'] AS $slng => $newmultiplechoice)
		{
			foreach ($newmultiplechoice AS $key => $choice)
			{
				if ($choice != '')
				{
					$value4[$key][] = "'" . $ilance->db->escape_string($choice) . "', ";
				}
			}
		}
		$string = '';
		$c = 0;
		foreach ($ilance->GPC['newmultiplechoice'] AS $slng => $newmultiplechoice)
		{
			foreach ($newmultiplechoice AS $keyy => $valuee)
			{
				if (isset($value4[$c]) AND is_array($value4[$c]))
				{
					foreach ($value4[$c] AS $key => $value)
					{
						$string .= $value;
					}
					$parentoptionid = isset($ilance->GPC['newmultiplechoicegroup'][$c]) ? $ilance->GPC['newmultiplechoicegroup'][$c] : 0;
					$sort = isset($ilance->GPC['newmultiplechoiceorder'][$c]) ? $ilance->GPC['newmultiplechoiceorder'][$c] : 0;
					$ilance->db->query("
						INSERT INTO " . DB_PREFIX . "project_questions_choices
						(optionid, parentoptionid, questionid, {$query4}sort, visible)
						VALUES(
						NULL,
						'" . intval($parentoptionid) . "', 
						'" . intval($insid) . "',
						$string
						'" . intval($sort) . "',
						'1')
					", 0, null, __FILE__, __LINE__);
					$string = '';
					$c++;
				}
			}
		}
	}
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "project_questions
		SET $query1
		$query2
		canremove = '1'
		WHERE questionid = '" . $insid . "'
		LIMIT 1
	", 0, null, __FILE__, __LINE__);
	print_action_success('{_new_category_question_was_added}', $ilance->GPC['return']);
	exit();
}
// #### CREATE PRODUCT CATEGORY QUESTION ###############################
else if ($ilance->GPC['subcmd'] == '_insert-product-question')
{
	$visible = isset($ilance->GPC['visible']) ? intval($ilance->GPC['visible']) : '0';
	$required = isset($ilance->GPC['required']) ? intval($ilance->GPC['required']) : '0';
	$cansearch = isset($ilance->GPC['cansearch']) ? intval($ilance->GPC['cansearch']) : '0';
	$recursive = isset($ilance->GPC['recursive']) ? intval($ilance->GPC['recursive']) : '0';
	$guests = isset($ilance->GPC['guests']) ? intval($ilance->GPC['guests']) : '0';
	$sort = isset($ilance->GPC['sort']) ? intval($ilance->GPC['sort']) : '0';
	$formname = construct_form_name(14);
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "product_questions
		(cid, formname, formdefault, inputtype, sort, visible, required, cansearch, recursive, guests)
		VALUES(
		'" . intval($ilance->GPC['cid']) . "',
		'" . $ilance->db->escape_string($formname) . "',
		'" . $ilance->db->escape_string($ilance->GPC['formdefault']) . "',
		'" . $ilance->db->escape_string($ilance->GPC['inputtype']) . "',
		'" . $sort . "',
		'" . $visible . "',
		'" . $required . "',
		'" . $cansearch . "',
		'" . $recursive . "',
		'" . $guests . "')
	", 0, null, __FILE__, __LINE__);
	$insid = $ilance->db->insert_id();
	$query1 = $query2 = $query3 = $query4 = $value3 = $value4 = '';
	if (!empty($ilance->GPC['question']) AND !empty($ilance->GPC['description']))
	{
		foreach ($ilance->GPC['question'] AS $slng => $value)
		{
			$query1 .= "question_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "',";
		}
		foreach ($ilance->GPC['description'] AS $slng => $value)
		{
			$query2 .= "description_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "',";
		}
	}
	if (isset($ilance->GPC['multiplechoice']) AND is_array($ilance->GPC['multiplechoice']))
	{
		foreach ($ilance->GPC['multiplechoice'] AS $slng => $multiplechoice)
		{
			$query3 .= "choice_" . mb_strtolower($slng) . ", ";
		}
		foreach ($ilance->GPC['multiplechoice'] AS $slng => $multiplechoice)
		{
			foreach ($multiplechoice AS $key => $choice)
			{
				if ($choice != '')
				{
					$value3[$key][] = "'" . $ilance->db->escape_string($choice) . "', ";
				}
			}
		}
		$string = '';
		$c = 0;
		foreach ($ilance->GPC['multiplechoice'] AS $slng => $multiplechoice)
		{
			foreach ($multiplechoice AS $keyy => $valuee)
			{
				if (isset($value3[$c]) AND is_array($value3[$c]))
				{
					foreach ($value3[$c] AS $key => $value)
					{
						$string .= $value;
					}
					$parentoptionid = isset($ilance->GPC['multiplechoicegroup'][$c]) ? $ilance->GPC['multiplechoicegroup'][$c] : 0;
					$sort = isset($ilance->GPC['multiplechoiceorder'][$c]) ? $ilance->GPC['multiplechoiceorder'][$c] : 0;
					$ilance->db->query("
						INSERT INTO " . DB_PREFIX . "product_questions_choices
						(optionid, parentoptionid, questionid, {$query3}sort, visible)
						VALUES(
						NULL,
						'" . intval($parentoptionid) . "', 
						'" . intval($insid) . "',
						$string
						'" . intval($sort) . "',
						'1')
					", 0, null, __FILE__, __LINE__);
					$string = '';
					$c++;
				}
			}
		}
	}
	if (isset($ilance->GPC['newmultiplechoice']) AND is_array($ilance->GPC['newmultiplechoice']))
	{
		foreach ($ilance->GPC['newmultiplechoice'] AS $slng => $newmultiplechoice)
		{
			$query4 .= "choice_" . mb_strtolower($slng) . ", ";
		}
		foreach ($ilance->GPC['newmultiplechoice'] AS $slng => $newmultiplechoice)
		{
			foreach ($newmultiplechoice AS $key => $choice)
			{
				if ($choice != '')
				{
					$value4[$key][] = "'" . $ilance->db->escape_string($choice) . "', ";
				}
			}
		}
		$string = '';
		$c = 0;
		foreach ($ilance->GPC['newmultiplechoice'] AS $slng => $newmultiplechoice)
		{
			foreach ($newmultiplechoice AS $keyy => $valuee)
			{
				if (isset($value4[$c]) AND is_array($value4[$c]))
				{
					foreach ($value4[$c] AS $key => $value)
					{
						$string .= $value;
					}
					$parentoptionid = isset($ilance->GPC['newmultiplechoicegroup'][$c]) ? $ilance->GPC['newmultiplechoicegroup'][$c] : 0;
					$sort = isset($ilance->GPC['newmultiplechoiceorder'][$c]) ? $ilance->GPC['newmultiplechoiceorder'][$c] : 0;
					$ilance->db->query("
						INSERT INTO " . DB_PREFIX . "product_questions_choices
						(optionid, parentoptionid, questionid, {$query4}sort, visible)
						VALUES(
						NULL,
						'" . intval($parentoptionid) . "', 
						'" . intval($insid) . "',
						$string
						'" . intval($sort) . "',
						'1')
					", 0, null, __FILE__, __LINE__);
					$string = '';
					$c++;
				}
			}
		}
	}
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "product_questions
		SET $query1
		$query2
		canremove = '1'
		WHERE questionid = '" . $insid . "'
		LIMIT 1
	", 0, null, __FILE__, __LINE__);
	print_action_success('{_new_category_question_was_added}', $ilance->GPC['return']);
	exit();
}
// #### UPDATE SERVICE CATEGORY QUESTION ###############################
else if ($ilance->GPC['subcmd'] == '_update-service-question')
{
	$required = $visible = $cansearch = $recursive = $guests = 0;
	$query1 = $query2 = $query3 = $value3 = '';
	if (isset($ilance->GPC['required']) AND $ilance->GPC['required'] > 0)
	{
		$required = 1;
	}
	if (isset($ilance->GPC['visible']) AND $ilance->GPC['visible'] > 0)
	{
		$visible = 1;
	}
	if (isset($ilance->GPC['cansearch']) AND $ilance->GPC['cansearch'] > 0)
	{
		$cansearch = 1;
	}
	if (isset($ilance->GPC['recursive']) AND $ilance->GPC['recursive'] > 0)
	{
		$recursive = 1;
	}
	if (isset($ilance->GPC['guests']) AND $ilance->GPC['guests'] > 0)
	{
		$guests = 1;
	}
	if (!empty($ilance->GPC['question']) AND !empty($ilance->GPC['description']))
	{
		foreach ($ilance->GPC['question'] AS $slng => $value)
		{
			$query1 .= "question_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "',";
		}
		foreach ($ilance->GPC['description'] AS $slng => $value)
		{
			$query2 .= "description_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "',";
		}
	}
	if (!isset($ilance->GPC['formname']) OR empty($ilance->GPC['formname']))
	{
		$ilance->GPC['formname'] = construct_form_name(14);        
	}
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "project_questions
		SET cid = '" . intval($ilance->GPC['cid']) . "',
		$query1
		$query2
		formname = '" . $ilance->db->escape_string($ilance->GPC['formname']) . "',
		formdefault = '" . $ilance->db->escape_string($ilance->GPC['formdefault']) . "',
		inputtype = '" . $ilance->db->escape_string($ilance->GPC['inputtype']) . "',
		sort = '" . intval($ilance->GPC['sort']) . "',
		visible = '" . $visible . "',
		required = '" . $required . "',
		cansearch = '" . $cansearch . "',
		recursive = '" . $recursive . "',
		canremove = '1'
		guests = '" . $guests . "'
		WHERE questionid = '" . intval($ilance->GPC['qid']) . "'
		LIMIT 1
	", 0, null, __FILE__, __LINE__);
	if (isset($ilance->GPC['multiplechoice']) AND is_array($ilance->GPC['multiplechoice']))
	{
		foreach ($ilance->GPC['multiplechoice'] AS $slng => $multiplechoice)
		{
			foreach ($multiplechoice AS $key => $choice)
			{
				if ($choice != '')
				{
					$parentoptionid = isset($ilance->GPC['multiplechoicegroup'][$key]) ? $ilance->GPC['multiplechoicegroup'][$key] : 0;
					$sort = isset($ilance->GPC['multiplechoiceorder'][$key]) ? $ilance->GPC['multiplechoiceorder'][$key] : 0;
					$sql = $ilance->db->query("
						SELECT optionid
						FROM " . DB_PREFIX . "project_questions_choices
						WHERE optionid = '" . intval($key) . "'
							AND questionid = '" . intval($ilance->GPC['qid']) . "'
							AND optionid > 0
						LIMIT 1
					");
					if ($ilance->db->num_rows($sql) > 0)
					{
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "project_questions_choices
							SET choice_$slng = '" . $ilance->db->escape_string(trim($choice)) . "',
							sort = '" . intval($sort) . "',
							parentoptionid = '" . intval($parentoptionid) . "'
							WHERE questionid = '" . intval($ilance->GPC['qid']) . "'
								AND optionid = '" . intval($key) . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
					}
				}
			}
		}
	}
	if (isset($ilance->GPC['newmultiplechoice']) AND is_array($ilance->GPC['newmultiplechoice']))
	{
		foreach ($ilance->GPC['newmultiplechoice'] AS $slng => $newmultiplechoice)
		{
			$query3 .= "choice_" . mb_strtolower($slng) . ", ";
		}
		foreach ($ilance->GPC['newmultiplechoice'] AS $slng => $newmultiplechoice)
		{
			foreach ($newmultiplechoice AS $key => $choice)
			{
				if ($choice != '')
				{
					$value3[$key][] = "'" . $ilance->db->escape_string($choice) . "', ";
				}
			}
		}
		$string = '';
		$c = 0;
		foreach ($ilance->GPC['newmultiplechoice'] AS $slng => $newmultiplechoice)
		{
			foreach ($newmultiplechoice AS $keyy => $valuee)
			{
				if (isset($value3[$c]) AND is_array($value3[$c]))
				{
					foreach ($value3[$c] AS $key => $value)
					{
						$string .= $value;
					}
					$parentoptionid = isset($ilance->GPC['newmultiplechoicegroup'][$c]) ? $ilance->GPC['newmultiplechoicegroup'][$c] : 0;
					$sort = isset($ilance->GPC['newmultiplechoiceorder'][$c]) ? $ilance->GPC['newmultiplechoiceorder'][$c] : 0;
					$ilance->db->query("
						INSERT INTO " . DB_PREFIX . "project_questions_choices
						(optionid, parentoptionid, questionid, {$query3}sort, visible)
						VALUES(
						NULL,
						'" . intval($parentoptionid) . "', 
						'" . intval($ilance->GPC['qid']) . "',
						$string
						'" . intval($sort) . "',
						'1')
					", 0, null, __FILE__, __LINE__);
					$string = '';
					$c++;
				}
			}
		}
	}
	print_action_success('{_new_category_question_details_was_updated_for_the_selected_question}', $ilance->GPC['return']);
	exit();
}
// #### UPDATE PRODUCT CATEGORY QUESTION ###############################
else if ($ilance->GPC['subcmd'] == '_update-product-question')
{
	$required = $visible = $cansearch = $recursive = $guests = 0;
	$query1 = $query2 = $query3 = $value3 = '';
	if (isset($ilance->GPC['required']) AND $ilance->GPC['required'] > 0)
	{
		$required = 1;
	}
	if (isset($ilance->GPC['visible']) AND $ilance->GPC['visible'] > 0)
	{
		$visible = 1;
	}
	if (isset($ilance->GPC['cansearch']) AND $ilance->GPC['cansearch'] > 0)
	{
		$cansearch = 1;
	}
	if (isset($ilance->GPC['recursive']) AND $ilance->GPC['recursive'] > 0)
	{
		$recursive = 1;
	}
	if (isset($ilance->GPC['guests']) AND $ilance->GPC['guests'] > 0)
	{
		$guests = 1;
	}
	if (!empty($ilance->GPC['question']) AND !empty($ilance->GPC['description']))
	{
		foreach ($ilance->GPC['question'] AS $slng => $value)
		{
			$query1 .= "question_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "',";
		}
		foreach ($ilance->GPC['description'] AS $slng => $value)
		{
			$query2 .= "description_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "',";
		}
	}
	if (!isset($ilance->GPC['formname']) OR empty($ilance->GPC['formname']))
	{
		$ilance->GPC['formname'] = construct_form_name(14);
	}
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "product_questions
		SET cid = '" . intval($ilance->GPC['cid']) . "',
		$query1
		$query2
		formname = '" . $ilance->db->escape_string($ilance->GPC['formname']) . "',
		formdefault = '" . $ilance->db->escape_string($ilance->GPC['formdefault']) . "',
		inputtype = '" . $ilance->db->escape_string($ilance->GPC['inputtype']) . "',
		sort = '" . intval($ilance->GPC['sort']) . "',
		visible = '" . $visible . "',
		required = '" . $required . "',
		cansearch = '" . $cansearch . "',
		recursive = '" . $recursive . "',
		canremove = '1',
		guests = '" . $guests . "'
		WHERE questionid = '" . intval($ilance->GPC['qid']) . "'
		LIMIT 1
	", 0, null, __FILE__, __LINE__);
	if (isset($ilance->GPC['multiplechoice']) AND is_array($ilance->GPC['multiplechoice']))
	{
		foreach ($ilance->GPC['multiplechoice'] AS $slng => $multiplechoice)
		{
			foreach ($multiplechoice AS $key => $choice)
			{
				if ($choice != '')
				{
					$parentoptionid = isset($ilance->GPC['multiplechoicegroup'][$key]) ? $ilance->GPC['multiplechoicegroup'][$key] : 0;
					$sort = isset($ilance->GPC['multiplechoiceorder'][$key]) ? $ilance->GPC['multiplechoiceorder'][$key] : 0;
					$sql = $ilance->db->query("
						SELECT optionid
						FROM " . DB_PREFIX . "product_questions_choices
						WHERE optionid = '" . intval($key) . "'
							AND questionid = '" . intval($ilance->GPC['qid']) . "'
							AND optionid > 0
						LIMIT 1
					");
					if ($ilance->db->num_rows($sql) > 0)
					{
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "product_questions_choices
							SET choice_$slng = '" . $ilance->db->escape_string(trim($choice)) . "',
							sort = '" . intval($sort) . "',
							parentoptionid = '" . intval($parentoptionid) . "'
							WHERE questionid = '" . intval($ilance->GPC['qid']) . "'
								AND optionid = '" . intval($key) . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
					}
				}
			}
		}
	}
	if (isset($ilance->GPC['newmultiplechoice']) AND is_array($ilance->GPC['newmultiplechoice']))
	{
		foreach ($ilance->GPC['newmultiplechoice'] AS $slng => $newmultiplechoice)
		{
			$query3 .= "choice_" . mb_strtolower($slng) . ", ";
		}
		foreach ($ilance->GPC['newmultiplechoice'] AS $slng => $newmultiplechoice)
		{
			foreach ($newmultiplechoice AS $key => $choice)
			{
				if ($choice != '')
				{
					$value3[$key][] = "'" . $ilance->db->escape_string($choice) . "', ";
				}
			}
		}
		$string = '';
		$c = 0;
		foreach ($ilance->GPC['newmultiplechoice'] AS $slng => $newmultiplechoice)
		{
			foreach ($newmultiplechoice AS $keyy => $valuee)
			{
				if (isset($value3[$c]) AND is_array($value3[$c]))
				{
					foreach ($value3[$c] AS $key => $value)
					{
						$string .= $value;
					}
					$parentoptionid = isset($ilance->GPC['newmultiplechoicegroup'][$c]) ? $ilance->GPC['newmultiplechoicegroup'][$c] : 0;
					$sort = isset($ilance->GPC['newmultiplechoiceorder'][$c]) ? $ilance->GPC['newmultiplechoiceorder'][$c] : 0;
					$ilance->db->query("
						INSERT INTO " . DB_PREFIX . "product_questions_choices
						(optionid, parentoptionid, questionid, {$query3}sort, visible)
						VALUES(
						NULL,
						'" . intval($parentoptionid) . "', 
						'" . intval($ilance->GPC['qid']) . "',
						$string
						'" . intval($sort) . "',
						'1')
					", 0, null, __FILE__, __LINE__);
					$string = '';
					$c++;
				}
			}
		}
	}
	print_action_success('{_new_category_question_details_was_updated_for_the_selected_question}', $ilance->GPC['return']);
	exit();
}
// #### EDIT SERVICE CATEGORY QUESTIONS ################################
else if ($ilance->GPC['subcmd'] == 'servicequestions')
{
	if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'removeoption' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "project_questions_choices
			WHERE optionid = '" . intval($ilance->GPC['id']) . "'
			LIMIT 1
		");
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "project_answers
			WHERE optionid = '" . intval($ilance->GPC['id']) . "'
		");
		print_action_success('{_success_removed_option_cat_question}', urldecode($ilance->GPC['returnurl']));
		exit();
	}
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'] . '?cmd=categories', $_SESSION['ilancedata']['user']['slng']);
	$cid = intval($ilance->GPC['cid']);
	$categoryname = $ilance->categories->title(fetch_user_slng($_SESSION['ilancedata']['user']['userid']), $cid);
	$slng = fetch_site_slng();
	$area_title = '{_create_or_update_category_question}: ' . $categoryname;
	$page_title = SITE_NAME . ' - {_create_or_update_category_question}: ' . $categoryname;
	$questionid = 0;
	$question_subcmd = '_insert-service-question';
	$submit_category_question = ($show['ADMINCP_TEST_MODE']) ? '<input type="button" style="font-size:15px" value=" {_save} " class="buttons" disabled="disabled" />' : '<input type="submit" style="font-size:15px" value=" {_save} " class="buttons" />';
	$question = $description = $formname = $formdefault = $multiplechoice = $sort = $checked_question_active = $checked_question_required = $checked_question_cansearch = '';
	$searchablecb_style = 'style="display:none"';
	{
		$pulldown_extra = 'class="select-250" onchange="javascript:
if (document.ilform.inputtype[5].selected == true)
{ // multiple choice items
	toggle_show(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_show(\'searchablecb_service\');
}
else if (document.ilform.inputtype[6].selected == true)
{ // pulldown menu items
	toggle_show(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_show(\'searchablecb_product\');
}
else if (document.ilform.inputtype[1].selected == true)
{ // integer field
	toggle_hide(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_hide(\'searchablecb_service\');
}
else if (document.ilform.inputtype[2].selected == true)
{ // text area field
	toggle_hide(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_hide(\'searchablecb_service\');
}
else if (document.ilform.inputtype[2].selected == true)
{ // input text field
	toggle_hide(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_hide(\'searchablecb_service\');
}
else if (document.ilform.inputtype[3].selected == true)
{ // url single line field
	toggle_hide(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_hide(\'searchablecb_service\');
}
else if (document.ilform.inputtype[0].selected == true)
{ // radio yes/no field
	toggle_hide(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_hide(\'searchablecb_service\');
}
else
{
	toggle_hide(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_hide(\'searchablecb_service\');
}
"';
	}
	$question_inputtype_pulldown = construct_pulldown('inputtype', 'inputtype', $question_types, '', $pulldown_extra);
	$var = $ilance->categories->fetch_children_ids($cid, 'service');
	$var2 = $ilance->categories->fetch_parent_ids($cid);
	$extracids = "AND (FIND_IN_SET(cid, '$cid,$var2') OR cid = '-1')";
	unset($explode, $var);
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "project_questions
		WHERE questionid > 0
		$extracids
		ORDER BY sort ASC
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$show['noservicequestions'] = false;
		$row_count = 0;
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			if (($res['recursive'] == 1 AND $res['cid'] != $cid) OR $res['cid'] == $cid)
			{
				$count = $ilance->categories->fetch_category_question_answer_count('service', $res['questionid']);
				$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				$res['active'] = ($res['visible'] == 1) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
				$res['cansearch'] = ($res['cansearch'] == 1) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
				$res['sort'] = '<input type="text" name="sort[' . $res['questionid'] . ']" value="' . $res['sort'] . '" class="input" style="text-align:center" size="3" />';
				$res['question'] = '<strong>' . $res['question_' . fetch_site_slng()] . '</strong> <span class="smaller gray" title="' . $count . ' answers for this question">(' . $count . ')</span><div class="smaller gray" style="padding-top:3px">' . $res['description_' . fetch_site_slng()] . '</div>';
				$res['inputtype'] = $res['inputtype'];
				$res['fieldname'] = $res['formname'];
				if ($res['cid'] == '-1')
				{
					$res['category'] = '{_assigned_to_all_categories}';
				}
				else
				{
					$res['category'] = $ilance->categories->recursive($res['cid'], 'service', $_SESSION['ilancedata']['user']['slng'], 1, '', 0);
				}
				$res['edit'] = '<a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=servicequestions&amp;cid=' . $res['cid'] . '&amp;qid=' . $res['questionid'] . '#question"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
				$res['remove'] = '<a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=_remove-service-question&amp;cid=' . $res['cid'] . '&amp;qid=' . $res['questionid'] . '" onClick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
				$res['isrequired'] = ($res['required'] == 1) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
				$res['recursive'] = ($res['recursive'] == 1) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
				$res['guests'] = ($res['guests'] == 1) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
				$servicequestions[] = $res;
				$row_count++;
			}
		}
	}
	else
	{
		$show['noservicequestions'] = true;
	}
	if (isset($ilance->GPC['qid']) AND $ilance->GPC['qid'] > 0)
	{
		// we are editing a particular question
		$submit_category_question = ($show['ADMINCP_TEST_MODE']) ? '<input type="button" style="font-size:15px" value=" {_save} " class="buttons" disabled="disabled" />' : '<input type="submit" style="font-size:15px" value=" {_save} " class="buttons" />';
		$questionid = intval($ilance->GPC['qid']);
		$question_subcmd = '_update-service-question';
		$var = $ilance->categories->fetch_children_ids($cid, 'service');
		$extracids = "AND (FIND_IN_SET(cid, '$cid,$var') OR cid = '-1')";
		unset($explode, $var);
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "project_questions 
			WHERE questionid = '" . intval($ilance->GPC['qid']) . "'
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$question = stripslashes($res['question_' . fetch_site_slng()]);
			$description = stripslashes($res['description_' . fetch_site_slng()]);
			$formname = $res['formname'];
			$formdefault = $res['formdefault'];
			$multiplechoice = '';
			$c = 0;
			$sql2 = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "project_questions_choices
				WHERE questionid = '" . intval($ilance->GPC['qid']) . "'
				ORDER BY sort ASC
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql2) > 0)
			{
				while ($res2 = $ilance->db->fetch_array($sql2, DB_ASSOC))
				{
					$languages = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "language
					", 0, null, __FILE__, __LINE__);
					$lc = $ilance->db->num_rows($languages);
					$lcc = 1;
					while ($language = $ilance->db->fetch_array($languages, DB_ASSOC))
					{
						$language['slng'] = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
						$language['language'] = $language['title'];
						if ($lc > 1)
						{
							if ($lcc == 1)
							{
								$multiplechoice .= '<div style="padding:2px 0 2px 0"><span class="litegray">' . mb_strtoupper($language['languageiso']) . '</span> <input class="input" name="multiplechoice[' . $language['slng'] . '][' . $res2['optionid'] . ']" value="' . handle_input_keywords($res2['choice_' . $language['slng']]) . '" id="multiplechoice_' . $language['slng'] . '_' . $res2['optionid'] . '" style="width:35%" title="' . $language['title'] . '" /> <input title="{_display_order}" class="input" name="multiplechoiceorder[' . $res2['optionid'] . ']" id="multiplechoiceorder_' . $res2['optionid'] . '" value="' . $res2['sort'] . '" style="width:5%" /> ' . $ilance->auction_questions->print_category_question_pulldown_groups($cid, $ilance->GPC['qid'], $res2['optionid'], 'service', $language['slng'], 'update', 'multiplechoicegroup', 0, $res2['parentoptionid']) . ' &nbsp;&nbsp;<span class="smaller blue"><a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=servicequestions&amp;do=removeoption&amp;id=' . $res2['optionid'] . '&amp;returnurl=' . urlencode(PAGEURL) . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')">{_delete}</a></span></div>';
							}
							else
							{
								$multiplechoice .= '<div style="padding:2px 0 2px 0"><span class="litegray">' . mb_strtoupper($language['languageiso']) . '</span> <input class="input" name="multiplechoice[' . $language['slng'] . '][' . $res2['optionid'] . ']" value="' . handle_input_keywords($res2['choice_' . $language['slng']]) . '" id="multiplechoice_' . $language['slng'] . '_' . $res2['optionid'] . '" style="width:35%" title="' . $language['title'] . '" /></div>';
							}
						}
						else
						{
							$multiplechoice .= '<div style="padding:2px 0 2px 0"><span class="litegray">' . mb_strtoupper($language['languageiso']) . '</span> <input class="input" name="multiplechoice[' . $language['slng'] . '][' . $res2['optionid'] . ']" value="' . handle_input_keywords($res2['choice_' . $language['slng']]) . '" id="multiplechoice_' . $language['slng'] . '_' . $res2['optionid'] . '" style="width:35%" title="' . $language['title'] . '" /> <input title="{_display_order}" class="input" name="multiplechoiceorder[' . $res2['optionid'] . ']" id="multiplechoiceorder_' . $res2['optionid'] . '" value="' . $res2['sort'] . '" style="width:5%" /> ' . $ilance->auction_questions->print_category_question_pulldown_groups($cid, $ilance->GPC['qid'], $res2['optionid'], 'service', $language['slng'], 'update', 'multiplechoicegroup', 0, $res2['parentoptionid']) . ' &nbsp;&nbsp;<span class="smaller blue"><a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=servicequestions&amp;do=removeoption&amp;id=' . $res2['optionid'] . '&amp;returnurl=' . urlencode(PAGEURL) . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')">{_delete}</a></span></div>';
						}
						$c++;
						$lcc++;
					}
					$multiplechoice .= '<div style="height:1px; background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
				}
				$multiplechoice .= '<div style="padding:2px 0 2px 0" id="updateoptions" style="display:none"></div>';
				$multiplechoice .= '<div class="smaller blue" style="padding-top:4px"><a href="javascript:;" onclick="add_display_value_option(\'update\', \'updateoptions\', \'service\', \'' . $cid . '\', \'' . intval($ilance->GPC['qid']) . '\')">{_add_new_option}</a><input type="hidden" id="qcounter" value="0" /></div>';
			}
			else
			{
				$languages = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "language
				", 0, null, __FILE__, __LINE__);
				$lc = $ilance->db->num_rows($languages);
				$lcc = 1;
				while ($language = $ilance->db->fetch_array($languages, DB_ASSOC))
				{
					$language['slng'] = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
					$language['language'] = $language['title'];
					if ($lc > 1)
					{
						if ($lcc == 1)
						{
							$multiplechoice .= '<div style="padding:2px 0 2px 0"><span class="litegray">' . mb_strtoupper($language['languageiso']) . '</span> <input class="input" name="multiplechoice[' . $language['slng'] . '][]" value="" id="multiplechoice_' . $language['slng'] . '_0" style="width:35%" title="' . $language['title'] . '" /> <input title="{_display_order}" class="input" name="multiplechoiceorder[]" id="multiplechoiceorder_0" value="10" style="width:5%" /> ' . $ilance->auction_questions->print_category_question_pulldown_groups($cid, $ilance->GPC['qid'], 0, 'service', $language['slng'], 'insert', 'multiplechoicegroup', 0, 0) . '</div>';
						}
						else
						{
							$multiplechoice .= '<div style="padding:2px 0 2px 0"><span class="litegray">' . mb_strtoupper($language['languageiso']) . '</span> <input class="input" name="multiplechoice[' . $language['slng'] . '][]" value="" id="multiplechoice_' . $language['slng'] . '_0" style="width:35%" title="' . $language['title'] . '" /><</div>';
						}
					}
					else
					{
						$multiplechoice .= '<div style="padding:2px 0 2px 0"><span class="litegray">' . mb_strtoupper($language['languageiso']) . '</span> <input class="input" name="multiplechoice[' . $language['slng'] . '][]" value="" id="multiplechoice_' . $language['slng'] . '_0" style="width:35%" title="' . $language['title'] . '" /> <input title="{_display_order}" class="input" name="multiplechoiceorder[]" id="multiplechoiceorder_0" value="' . $res2['sort'] . '" style="width:5%" /> ' . $ilance->auction_questions->print_category_question_pulldown_groups($cid, $ilance->GPC['qid'], 0, 'service', $language['slng'], 'insert', 'multiplechoicegroup', 0, 0) . '</div>';
					}
					$c++;
					$lcc++;
				}
				$multiplechoice .= '<div style="height:1px; background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
				$multiplechoice .= '<div style="padding:2px 0 2px 0" id="insertoptions" style="display:none"></div>';
				$multiplechoice .= '<div class="smaller blue" style="padding-top:4px"><a href="javascript:;" onclick="add_display_value_option(\'insert\', \'insertoptions\', \'service\', \'' . $cid . '\', \'' . intval($ilance->GPC['qid']) . '\')">{_add_new_option}</a><input type="hidden" id="qcounter" value="0" /></div>';
			}
			$sort = $res['sort'];
			$checked_question_active = ($res['visible']) ? 'checked="checked"' : '';
			$checked_question_required = ($res['required']) ? 'checked="checked"' : '';
			$checked_question_cansearch = ($res['cansearch']) ? 'checked="checked"' : '';
			$checked_question_recursive = ($res['recursive']) ? 'checked="checked"' : '';
			$regchecked_guests = ($res['guests'] > 0) ? 'checked="checked"' : '';
			$pulldown_extra = 'class="select-250" onchange="javascript:
if (document.ilform.inputtype[5].selected == true)
{ // multiple choice items
	toggle_show(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_show(\'searchablecb_service\');
}
else if (document.ilform.inputtype[6].selected == true)
{ // pulldown menu items
	toggle_show(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_show(\'searchablecb_service\');
}
else if (document.ilform.inputtype[1].selected == true)
{ // integer field
	toggle_hide(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_hide(\'searchablecb_service\');
}
else if (document.ilform.inputtype[2].selected == true)
{ // text area field
	toggle_hide(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_hide(\'searchablecb_service\');
}
else if (document.ilform.inputtype[2].selected == true)
{ // input text field
	toggle_hide(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_hide(\'searchablecb_service\');
}
else if (document.ilform.inputtype[3].selected == true)
{ // url single line field
	toggle_hide(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_hide(\'searchablecb_service\');
}
else if (document.ilform.inputtype[0].selected == true)
{ // radio yes/no field
	toggle_hide(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_hide(\'searchablecb_service\');
}
else
{
	toggle_hide(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_hide(\'searchablecb_service\');
}
"';
			$question_inputtype_pulldown = construct_pulldown('inputtype', 'inputtype', $question_types, $res['inputtype'], $pulldown_extra);
			if ($res['inputtype'] == "pulldown" OR $res['inputtype'] == "multiplechoice")
			{
				$searchablecb_style = '';
			}
			else 
			{
				$searchablecb_style = 'style="display:none"';
			}
		}
		$row_count = 0;
		$languages = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "language");
		while ($language = $ilance->db->fetch_array($languages, DB_ASSOC))
		{
			$language['slng'] = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
			$language['language'] = $language['title'];
			$language['languageiso'] = mb_strtoupper($language['languageiso']);
			$language['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$sql = $ilance->db->query("
				SELECT question_$language[slng] AS question, description_$language[slng] AS description
				FROM " . DB_PREFIX . "project_questions
				WHERE questionid = '" . intval($ilance->GPC['qid']) . "'
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					$language['question'] = handle_input_keywords($res['question']);	
					$language['description'] = handle_input_keywords($res['description']);	
				}
			}
			$servicequestiontitles[] = $language;
			$servicequestiondescription[] = $language;
			$row_count++;
		}
	}
	else 
	{
		// multilanguage question and description form fields (blank)
		$row_count = 0;
		$languages = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "language");
		$lc = $ilance->db->num_rows($languages);
		$lcc = 1;
		while ($language = $ilance->db->fetch_array($languages, DB_ASSOC))
		{
			$language['slng'] = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
			$language['language'] = $language['title'];
			$language['languageiso'] = mb_strtoupper($language['languageiso']);
			$language['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			if ($lc > 1)
			{
				if ($lcc == 1)
				{
					$multiplechoice .= '<div style="padding:2px 0 2px 0"><span class="litegray">' . mb_strtoupper($language['languageiso']) . '</span> <input class="input" name="multiplechoice[' . $language['slng'] . '][]" id="multiplechoice_' . $language['slng'] . '_0" value="" style="width:35%" placeholder="' . $language['language'] . '" title="' . $language['language'] . '" /> <input title="{_display_order}" class="input" name="multiplechoiceorder[]" id="multiplechoiceorder_0" value="10" style="width:5%" /> ' . $ilance->auction_questions->print_category_question_pulldown_groups($cid, 0, 0, 'service', $language['slng'], 'insert', 'multiplechoicegroup', 0, 0) . '</div>';
				}
				else
				{
					$multiplechoice .= '<div style="padding:2px 0 2px 0"><span class="litegray">' . mb_strtoupper($language['languageiso']) . '</span> <input class="input" name="multiplechoice[' . $language['slng'] . '][]" id="multiplechoice_' . $language['slng'] . '_0" value="" style="width:35%" placeholder="' . $language['language'] . '" title="' . $language['language'] . '" /></div>';
				}
			}
			else
			{
				$multiplechoice .= '<div style="padding:2px 0 2px 0"><span class="litegray">' . mb_strtoupper($language['languageiso']) . '</span> <input class="input" name="multiplechoice[' . $language['slng'] . '][]" id="multiplechoice_' . $language['slng'] . '_0" value="" style="width:35%" placeholder="' . $language['language'] . '" title="' . $language['language'] . '" /> <input title="{_display_order}" class="input" name="multiplechoiceorder[]" id="multiplechoiceorder_0" value="10" style="width:5%" /> ' . $ilance->auction_questions->print_category_question_pulldown_groups($cid, 0, 0, 'service', $language['slng'], 'insert', 'multiplechoicegroup', 0, 0) . '</div>';
			}
			$lcc++;
			$sql = $ilance->db->query("
				SELECT question_$language[slng] AS question, description_$language[slng] AS description
				FROM " . DB_PREFIX . "project_questions
				WHERE cid = '" . intval($cid) . "'
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$row_count = 0;
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					$language['question'] = '';	
					$language['description'] = '';	
				}
			}
			$servicequestiontitles[] = $language;
			$servicequestiondescription[] = $language;
			$row_count++;
		}
		$multiplechoice .= '<div style="height:1px; background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
		$multiplechoice .= '<div style="padding:2px 0 2px 0" id="insertoptions" style="display:none"></div>';
		$multiplechoice .= '<div class="smaller blue" style="padding-top:4px"><a href="javascript:;" onclick="add_display_value_option(\'insert\', \'insertoptions\', \'service\', \'' . $cid . '\', \'0\')">{_add_new_option}</a><input type="hidden" id="qcounter" value="0" /></div>';
	}
	$pprint_array = array('searchablecb_style','checked_question_recursive','buildversion', 'regchecked_guests','question_inputtype_pulldown','questionid','cid','slng','categoryname','language_pulldown','slng','checked_question_cansearch','checked_question_active','checked_question_required','subcategory_pulldown','formdefault','multiplechoice','question','description','formname','sort','submit_category_question','question_id_hidden','question_subcmd','question_inputtype_pulldown','subcatid','subcatname','catname','service_subcategories','product_categories','subcmd','id','submit','description','name','checked_profile_group_active');

	($apihook = $ilance->api('admincp_categories_questions_service_end')) ? eval($apihook) : false;

	$ilance->template->fetch('main', 'categories_questions.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
	$ilance->template->parse_loop('main', array('servicequestions', 'servicequestiontitles', 'servicequestiondescription'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();	
}
// #### EDIT PRODUCT CATEGORY QUESTIONS ################################
else if ($ilance->GPC['subcmd'] == 'productquestions')
{
	if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'removeoption' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		// delete this choice
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "product_questions_choices
			WHERE optionid = '" . intval($ilance->GPC['id']) . "'
			LIMIT 1
		");
		// delete all answers based on this choice
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "product_answers
			WHERE optionid = '" . intval($ilance->GPC['id']) . "'
		");
		print_action_success('{_success_removed_option_cat_question}', urldecode($ilance->GPC['returnurl']));
		exit();
	}
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'] . '?cmd=categories', $_SESSION['ilancedata']['user']['slng']);
	$cid = intval($ilance->GPC['cid']);
	$slng = fetch_site_slng();
	$categoryname = $ilance->categories->title($slng, $cid);
	$area_title = '{_create_or_update_category_question}: ' . $categoryname;
	$page_title = SITE_NAME . ' - {_create_or_update_category_question}: ' . $categoryname;
	$questionid = 0;
	$question_subcmd = '_insert-product-question';
	$submit_category_question = ($show['ADMINCP_TEST_MODE']) ? '<input type="button" value=" {_save} " style="font-size:15px" class="buttons" disabled="disabled" />' : '<input type="submit" value=" {_save} " style="font-size:15px" class="buttons" />';
	$question = $description = $formname = $formdefault = $multiplechoice = $sort = $checked_question_active = $checked_question_required = $checked_question_cansearch = '';
	$searchablecb_style = 'style="display:none"';
	{
	$pulldown_extra = 'class="select-250" onchange="javascript:
if (document.ilform.inputtype[5].selected == true)
{ // multiple choice items
	toggle_show(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_show(\'searchablecb_product\');
}
else if (document.ilform.inputtype[6].selected == true)
{ // pulldown menu items
	toggle_show(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_show(\'searchablecb_product\');
}
else if (document.ilform.inputtype[1].selected == true)
{ // integer field
	toggle_hide(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_hide(\'searchablecb_product\');
}
else if (document.ilform.inputtype[2].selected == true)
{ // text area field
	toggle_hide(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_hide(\'searchablecb_product\');
}
else if (document.ilform.inputtype[2].selected == true)
{ // input text field
	toggle_hide(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_hide(\'searchablecb_product\');
}
else if (document.ilform.inputtype[3].selected == true)
{ // url single line field
	toggle_hide(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_hide(\'searchablecb_product\');
}
else if (document.ilform.inputtype[0].selected == true)
{ // radio yes/no field
	toggle_hide(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_hide(\'searchablecb_product\');
}
else
{
	toggle_hide(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_hide(\'searchablecb_product\');
}
"';
	}
	$question_inputtype_pulldown = construct_pulldown('inputtype', 'inputtype', $question_types, '', $pulldown_extra);
	$var = $ilance->categories->fetch_children_ids($cid, 'product');
	$var2 = $ilance->categories->fetch_parent_ids($cid);
	$extracids = "AND (FIND_IN_SET(cid, '$cid,$var2') OR cid = '-1')";
	unset($explode, $var);
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "product_questions
		WHERE questionid > 0
		$extracids
		ORDER BY sort ASC
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
		$show['noproductquestions'] = false;
		$row_count = 0;
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			if (($res['recursive'] == 1 AND $res['cid'] != $cid) OR $res['cid'] == $cid)
			{
				$count = $ilance->categories->fetch_category_question_answer_count('product', $res['questionid']);
				$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				$res['sort'] = '<input type="text" name="sort[' . $res['questionid'] . ']" value="' . $res['sort'] . '" class="input" style="text-align:center" size="3" />';
				$res['question'] = '<strong>' . $res['question_' . fetch_site_slng()] . '</strong> <span class="smaller gray" title="' . $count . ' answers for this question">(' . $count . ')</span><div class="smaller gray" style="padding-top:3px">' . $res['description_' . fetch_site_slng()] . '</div>';
				$res['inputtype'] = $res['inputtype'];
				$res['fieldname'] = $res['formname'];
				if ($res['cid'] == '-1')
				{
					$res['category'] = '{_assigned_to_all_categories}';
				}
				else
				{
					$res['category'] = $ilance->categories->recursive($res['cid'], 'product', $_SESSION['ilancedata']['user']['slng'], 1, '', 0);
				}
				$res['edit'] = '<a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=productquestions&amp;cid=' . $res['cid'] . '&amp;qid=' . $res['questionid'] . '#question"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
				$res['remove'] = '<a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=_remove-product-question&amp;cid=' . $res['cid'] . '&amp;qid=' . $res['questionid'] . '" onClick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
				$res['active'] = ($res['visible'] == 1) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
				$res['cansearch'] = ($res['cansearch'] == 1) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
				$res['isrequired'] = ($res['required'] == 1) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
				$res['recursive'] = ($res['recursive'] == 1) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
				$res['guests'] = ($res['guests'] == 1) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
				$productquestions[] = $res;
				$row_count++;
			}
		}
	}
	else
	{
		$show['noproductquestions'] = true;
	}
	if (isset($ilance->GPC['qid']) AND $ilance->GPC['qid'] > 0)
	{
		// we are editing a particular question
		$submit_category_question = ($show['ADMINCP_TEST_MODE']) ? '<input type="button" value="{_save}" style="font-size:15px" class="buttons" disabled="disabled" />' : '<input type="submit" value="{_save}" style="font-size:15px" class="buttons" />';
		$questionid = intval($ilance->GPC['qid']);
		$question_subcmd = '_update-product-question';
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "product_questions 
			WHERE questionid = '" . intval($ilance->GPC['qid']) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$question = stripslashes($res['question_' . fetch_site_slng()]);
			$description = stripslashes($res['description_' . fetch_site_slng()]);
			$formname = $res['formname'];
			$formdefault = $res['formdefault'];
			$multiplechoice = '';
			$c = 0;
			$sql2 = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "product_questions_choices
				WHERE questionid = '" . intval($ilance->GPC['qid']) . "'
				ORDER BY sort ASC
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql2) > 0)
			{
				while ($res2 = $ilance->db->fetch_array($sql2, DB_ASSOC))
				{
					$languages = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "language
					", 0, null, __FILE__, __LINE__);
					$lc = $ilance->db->num_rows($languages);
					$lcc = 1;
					while ($language = $ilance->db->fetch_array($languages, DB_ASSOC))
					{
						$language['slng'] = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
						$language['language'] = $language['title'];
						if ($lc > 1)
						{
							if ($lcc == 1)
							{
								$multiplechoice .= '<div style="padding:2px 0 2px 0"><span class="litegray">' . mb_strtoupper($language['languageiso']) . '</span> <input class="input" name="multiplechoice[' . $language['slng'] . '][' . $res2['optionid'] . ']" value="' . handle_input_keywords($res2['choice_' . $language['slng']]) . '" id="multiplechoice_' . $language['slng'] . '_' . $res2['optionid'] . '" style="width:35%" title="' . $language['title'] . '" /> <input title="{_display_order}" class="input" name="multiplechoiceorder[' . $res2['optionid'] . ']" id="multiplechoiceorder_' . $res2['optionid'] . '" value="' . $res2['sort'] . '" style="width:5%" /> ' . $ilance->auction_questions->print_category_question_pulldown_groups($cid, $ilance->GPC['qid'], $res2['optionid'], 'product', $language['slng'], 'update', 'multiplechoicegroup', 0, $res2['parentoptionid']) . ' &nbsp;&nbsp;<span class="smaller blue"><a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=productquestions&amp;do=removeoption&amp;id=' . $res2['optionid'] . '&amp;returnurl=' . urlencode(PAGEURL) . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')">{_delete}</a></span></div>';
							}
							else
							{
								$multiplechoice .= '<div style="padding:2px 0 2px 0"><span class="litegray">' . mb_strtoupper($language['languageiso']) . '</span> <input class="input" name="multiplechoice[' . $language['slng'] . '][' . $res2['optionid'] . ']" value="' . handle_input_keywords($res2['choice_' . $language['slng']]) . '" id="multiplechoice_' . $language['slng'] . '_' . $res2['optionid'] . '" style="width:35%" title="' . $language['title'] . '" /></div>';
							}
						}
						else
						{
							$multiplechoice .= '<div style="padding:2px 0 2px 0"><span class="litegray">' . mb_strtoupper($language['languageiso']) . '</span> <input class="input" name="multiplechoice[' . $language['slng'] . '][' . $res2['optionid'] . ']" value="' . handle_input_keywords($res2['choice_' . $language['slng']]) . '" id="multiplechoice_' . $language['slng'] . '_' . $res2['optionid'] . '" style="width:35%" title="' . $language['title'] . '" /> <input title="{_display_order}" class="input" name="multiplechoiceorder[' . $res2['optionid'] . ']" id="multiplechoiceorder_' . $res2['optionid'] . '" value="' . $res2['sort'] . '" style="width:5%" /> ' . $ilance->auction_questions->print_category_question_pulldown_groups($cid, $ilance->GPC['qid'], $res2['optionid'], 'product', $language['slng'], 'update', 'multiplechoicegroup', 0, $res2['parentoptionid']) . ' &nbsp;&nbsp;<span class="smaller blue"><a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=productquestions&amp;do=removeoption&amp;id=' . $res2['optionid'] . '&amp;returnurl=' . urlencode(PAGEURL) . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')">{_delete}</a></span></div>';
						}
						$c++;
						$lcc++;
					}
					$multiplechoice .= '<div style="height:1px; background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
				}
				$multiplechoice .= '<div style="padding:2px 0 2px 0" id="updateoptions" style="display:none"></div>';
				$multiplechoice .= '<div class="smaller blue" style="padding-top:4px"><a href="javascript:;" onclick="add_display_value_option(\'update\', \'updateoptions\', \'product\', \'' . $cid . '\', \'' . intval($ilance->GPC['qid']) . '\')">{_add_new_option}</a><input type="hidden" id="qcounter" value="0" /></div>';
			}
			else
			{
				$languages = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "language
				", 0, null, __FILE__, __LINE__);
				$lc = $ilance->db->num_rows($languages);
				$lcc = 1;
				while ($language = $ilance->db->fetch_array($languages, DB_ASSOC))
				{
					$language['slng'] = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
					$language['language'] = $language['title'];
					if ($lc > 1)
					{
						if ($lcc == 1)
						{
							$multiplechoice .= '<div style="padding:2px 0 2px 0"><span class="litegray">' . mb_strtoupper($language['languageiso']) . '</span> <input class="input" name="multiplechoice[' . $language['slng'] . '][]" value="" id="multiplechoice_' . $language['slng'] . '_0" style="width:35%" title="' . $language['title'] . '" /> <input title="{_display_order}" class="input" name="multiplechoiceorder[]" id="multiplechoiceorder_0" value="10" style="width:5%" /> ' . $ilance->auction_questions->print_category_question_pulldown_groups($cid, $ilance->GPC['qid'], 0, 'product', $language['slng'], 'insert', 'multiplechoicegroup', 0, 0) . '</div>';
						}
						else
						{
							$multiplechoice .= '<div style="padding:2px 0 2px 0"><span class="litegray">' . mb_strtoupper($language['languageiso']) . '</span> <input class="input" name="multiplechoice[' . $language['slng'] . '][]" value="" id="multiplechoice_' . $language['slng'] . '_0" style="width:35%" title="' . $language['title'] . '" /></div>';
						}
					}
					else
					{
						$multiplechoice .= '<div style="padding:2px 0 2px 0"><span class="litegray">' . mb_strtoupper($language['languageiso']) . '</span> <input class="input" name="multiplechoice[' . $language['slng'] . '][]" value="" id="multiplechoice_' . $language['slng'] . '_0" style="width:35%" title="' . $language['title'] . '" /> <input title="{_display_order}" class="input" name="multiplechoiceorder[]" id="multiplechoiceorder_' . $language['slng'] . '_0" value="10" style="width:5%" /> ' . $ilance->auction_questions->print_category_question_pulldown_groups($cid, $ilance->GPC['qid'], 0, 'product', $language['slng'], 'insert', 'multiplechoicegroup', 0, 0) . '</div>';
					}
					$c++;
					$lcc++;
				}
				$multiplechoice .= '<div style="height:1px; background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
				$multiplechoice .= '<div style="padding:2px 0 2px 0" id="insertoptions" style="display:none"></div>';
				$multiplechoice .= '<div class="smaller blue" style="padding-top:4px"><a href="javascript:;" onclick="add_display_value_option(\'insert\', \'insertoptions\', \'product\', \'' . $cid . '\', \'' . intval($ilance->GPC['qid']) . '\')">{_add_new_option}</a><input type="hidden" id="qcounter" value="0" /></div>';
			}
			$sort = $res['sort'];
			$checked_question_active = ($res['visible']) ? 'checked="checked"' : '';
			$checked_question_required = ($res['required']) ? 'checked="checked"' : '';	    
			$checked_question_cansearch = ($res['cansearch']) ? 'checked="checked"' : '';
			$checked_question_recursive = ($res['recursive']) ? 'checked="checked"' : '';
			$pulldown_extra = 'class="select-250" onchange="javascript:
if (document.ilform.inputtype[5].selected == true)
{ // multiple choice items
	toggle_show(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_show(\'searchablecb_product\');
}
else if (document.ilform.inputtype[6].selected == true)
{ // pulldown menu items
	toggle_show(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_show(\'searchablecb_product\');
}
else if (document.ilform.inputtype[1].selected == true)
{ // integer field
	toggle_hide(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_hide(\'searchablecb_product\');
}
else if (document.ilform.inputtype[2].selected == true)
{ // text area field
	toggle_hide(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_hide(\'searchablecb_product\');
}
else if (document.ilform.inputtype[2].selected == true)
{ // input text field
	toggle_hide(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_hide(\'searchablecb_product\');
}
else if (document.ilform.inputtype[3].selected == true)
{ // url single line field
	toggle_hide(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_hide(\'searchablecb_product\');
}
else if (document.ilform.inputtype[0].selected == true)
{ // radio yes/no field
	toggle_hide(\'displayvalues\');
	toggle_show(\'defaultdisplayvalue\');
	toggle_hide(\'searchablecb_product\');
}
else
{
	toggle_hide(\'displayvalues\');
	toggle_paid(\'defaultdisplayvalue\');
	toggle_hide(\'searchablecb_product\');
}
"';
			$question_inputtype_pulldown = construct_pulldown('inputtype', 'inputtype', $question_types, $res['inputtype'], $pulldown_extra);
			$regchecked_guests = ($res['guests'] > 0) ? 'checked="checked"' : '';
			if ($res['inputtype'] == "pulldown" OR $res['inputtype'] == "multiplechoice")
			{
				$searchablecb_style = '';
			}
			else 
			{
				$searchablecb_style = 'style="display:none"';
			}
		}
		// multilanguage question and description
		$row_count = 0;
		$languages = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "language
		", 0, null, __FILE__, __LINE__);
		while ($language = $ilance->db->fetch_array($languages, DB_ASSOC))
		{
			$language['slng'] = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
			$language['language'] = $language['title'];
			$language['languageiso'] = mb_strtoupper($language['languageiso']);
			$language['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$sql = $ilance->db->query("
				SELECT question_$language[slng] AS question, description_$language[slng] AS description
				FROM " . DB_PREFIX . "product_questions
				WHERE questionid = '" . intval($ilance->GPC['qid']) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					$language['question'] = handle_input_keywords($res['question']);	
					$language['description'] = handle_input_keywords($res['description']);	
				}
			}
			$productquestiontitles[] = $language;
			$productquestiondescription[] = $language;
			$row_count++;
		}
	}
	else 
	{
		// multilanguage question and description form fields (blank)
		$row_count = 0;
		$languages = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "language
		", 0, null, __FILE__, __LINE__);
		$lc = $ilance->db->num_rows($languages);
		$lcc = 1;
		while ($language = $ilance->db->fetch_array($languages, DB_ASSOC))
		{
			$language['slng'] = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
			$language['language'] = $language['title'];
			$language['languageiso'] = mb_strtoupper($language['languageiso']);
			$language['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			if ($lc > 1)
			{
				if ($lcc == 1)
				{
					$multiplechoice .= '<div style="padding:2px 0 2px 0"><span class="litegray">' . mb_strtoupper($language['languageiso']) . '</span> <input class="input" name="multiplechoice[' . $language['slng'] . '][]" id="multiplechoice_' . $language['slng'] . '_0" value="" style="width:35%" placeholder="' . $language['language'] . '" title="' . $language['language'] . '" /> <input title="{_display_order}" class="input" name="multiplechoiceorder[]" id="multiplechoiceorder_' . $language['slng'] . '_0" value="10" style="width:5%" /> ' . $ilance->auction_questions->print_category_question_pulldown_groups($cid, 0, 0, 'product', $language['slng'], 'insert', 'multiplechoicegroup', 0, 0) . '</div>';
				}
				else
				{
					$multiplechoice .= '<div style="padding:2px 0 2px 0"><span class="litegray">' . mb_strtoupper($language['languageiso']) . '</span> <input class="input" name="multiplechoice[' . $language['slng'] . '][]" id="multiplechoice_' . $language['slng'] . '_0" value="" style="width:35%" placeholder="' . $language['language'] . '" title="' . $language['language'] . '" /></div>';
				}
			}
			else
			{
				$multiplechoice .= '<div style="padding:2px 0 2px 0"><span class="litegray">' . mb_strtoupper($language['languageiso']) . '</span> <input class="input" name="multiplechoice[' . $language['slng'] . '][]" id="multiplechoice_' . $language['slng'] . '_0" value="" style="width:35%" placeholder="' . $language['language'] . '" title="' . $language['language'] . '" /> <input title="{_display_order}" class="input" name="multiplechoiceorder[]" id="multiplechoiceorder_0" value="10" style="width:5%" /> ' . $ilance->auction_questions->print_category_question_pulldown_groups($cid, 0, 0, 'product', $language['slng'], 'insert', 'multiplechoicegroup', 0, 0) . '</div>';
			}
			$lcc++;
			$sql = $ilance->db->query("
				SELECT question_$language[slng] AS question, description_$language[slng] AS description
				FROM " . DB_PREFIX . "product_questions
				WHERE cid = '" . intval($cid) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$row_count = 0;
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					$language['question'] = '';	
					$language['description'] = '';
				}
			}
			$productquestiontitles[] = $language;
			$productquestiondescription[] = $language;
			$row_count++;
		}
		$multiplechoice .= '<div style="height:1px; background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
		$multiplechoice .= '<div style="padding:2px 0 2px 0" id="insertoptions" style="display:none"></div>';
		$multiplechoice .= '<div class="smaller blue" style="padding-top:4px"><a href="javascript:;" onclick="add_display_value_option(\'insert\', \'insertoptions\', \'product\', \'' . $cid . '\', \'0\')">{_add_new_option}</a><input type="hidden" id="qcounter" value="0" /></div>';
	}
	$pprint_array = array('searchablecb_style', 'checked_question_recursive', 'regchecked_guests', 'question_inputtype_pulldown','questionid','cid','slng','categoryname','language_pulldown','slng','checked_question_cansearch','checked_question_active','checked_question_required','subcategory_pulldown','formdefault','multiplechoice','question','description','formname','sort','submit_category_question','question_id_hidden','question_subcmd','question_inputtype_pulldown','subcatid','subcatname','catname','service_subcategories','product_categories','subcmd','id','submit','description','name','checked_profile_group_active');

	($apihook = $ilance->api('admincp_categories_questions_product_end')) ? eval($apihook) : false;

	$ilance->template->fetch('main', 'categories_questions.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
	$ilance->template->parse_loop('main', array('productquestions', 'productquestiontitles', 'productquestiondescription'));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();	
}
// #### IMPORT SERVICE CSV CATEGORY LISTINGS ###########################
else if ($ilance->GPC['subcmd'] == 'importservicecsv')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$importcategory = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($ilance->GPC['cid']) . "'", "title_" . $_SESSION['ilancedata']['user']['slng']);
	$cid = $ilance->GPC['cid'];
	$columns = array('project_title', 'description', 'keywords', 'attributes');
	$columnphrases = array(
		'project_title' => '{_title}',
		'description' => '{_description}',
		'keywords' => '{_keywords}',
		'attributes' => '{_budget}'
	);
	$coloumncount = count($columns);
	if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'assign')
	{
		if (empty($_FILES['csv_file']['name']))
		{
			print_notice('{_invalid_bulk_import_file}', '{_the_bulk_file_you_uploaded_was_not_correct_please_try_again}', 'javascript:history.go(-1)', '{_retry}');
			exit();
		}
		else
		{
			$extension = mb_strtolower(mb_strrchr($_FILES['csv_file']['name'], '.'));
			if ($extension != '.csv')
			{
				print_notice('{_invalid_file_extension}', '{_the_bulk_file_you_uploaded_did_not_have_the_correct_file_extension}', 'javascript:history.go(-1)', '{_retry}');
				exit();
			}
		}
		while (list($key, $value) = each($_FILES))
		{
			$GLOBALS[$key] = $value;
			foreach ($_FILES AS $key => $value)
			{
				$GLOBALS[$key] = $_FILES[$key]['tmp_name'];
				foreach ($value AS $ext => $value2)
				{
					$key2 = $key . '_' . $ext;
					$GLOBALS[$key2] = $value2;
				}
			}
		}
		$tmp_name = $_FILES['csv_file']['tmp_name'];
		$file_name = DIR_SERVER_ROOT . DIR_UPLOADS_NAME . '/' . $_FILES['csv_file']['name'];

		if (file_exists($file_name))
		{
			@unlink($file_name);
		}
		move_uploaded_file($tmp_name, $file_name);
		// standardize line breaks
		$data = file_get_contents($file_name);
		$data = str_replace(array("\r\n", "\r", "\n"), LINEBREAK, $data);
		file_put_contents($file_name, $data);
		$datetime = DATETIME24H;
		$sq2 = $ilance->db->query("
			INSERT INTO " . DB_PREFIX . "bulk_sessions
			(user_id, dateupload, itemsuploaded)
			VALUES (
			'" . $_SESSION['ilancedata']['user']['userid'] . "',
			'" . $ilance->db->escape_string($datetime) . "',
			'0')
		", 0, null, __FILE__, __LINE__);
		$sql3 = $ilance->db->query("
			SELECT id
			FROM " . DB_PREFIX . "bulk_sessions
			WHERE dateupload = '" . $ilance->db->escape_string($datetime) . "'
		", 0, null, __FILE__, __LINE__);
		$res = $ilance->db->fetch_array($sql3, DB_ASSOC);
		$bulk_id = $res['id'];
		// #### handle importing ###############################
		$containsheader = isset($ilance->GPC['containsheader']) ? true : false;
		$ilance->csv->csv_to_db($file_name, $_SESSION['ilancedata']['user']['userid'], $bulk_id, $containsheader, true);
		// remove uploaded csv file...
		if (file_exists($file_name))
		{
			@unlink($file_name);
		}
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "bulk_tmp
			SET project_type = 'reverse',
			project_state = 'service',
			cid = '" . intval($cid) . "'
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND bulk_id = '" . $bulk_id . "'
		", 0, null, __FILE__, __LINE__);
		$sql = $ilance->db->query("
			SELECT id
			FROM " . DB_PREFIX . "bulk_tmp
			WHERE correct = '0'
				AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND bulk_id = '" . $bulk_id . "'
		", 0, null, __FILE__, __LINE__);
		$items = $ilance->db->num_rows($sql);
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "bulk_sessions
			SET items = '" . intval($items) . "'
			WHERE id = '" . intval($bulk_id) . "'
		", 0, null, __FILE__, __LINE__);
		$col = '<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" width="100%" dir="' . $ilconfig['template_textdirection'] . '">';
		$col .= '<tr class="alt2">';
		for ($i = 1; $i <= $coloumncount; $i++)
		{
			$col .= '<td><div><select name="col[' . $i . ']" style="font-family: verdana" class="smaller"><option value="">-</option>';
			$x = 1;
			foreach ($columns AS $key)
			{
				if (isset($columnphrases["$key"]) AND !empty($columnphrases["$key"]))
				{
					$col .= ($i == $x) ? '<option value="' . $key . '" selected="selected">' . $columnphrases["$key"] . '</option>' : '<option value="' . $key . '">' . $columnphrases["$key"] . '</option>';
					$x++;
				}
			}
			$col .= '</select></div></td>';
		}
		$col .= '</tr>';
		$cutoff = $ilconfig['globalfilters_auctiondescriptioncutoff'];
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "bulk_tmp
			WHERE bulk_id = '" . intval($bulk_id) . "'
			LIMIT " . $ilconfig['globalfilters_bulkuploadpreviewlimit']
		);
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$valid = $ilance->categories->can_post($_SESSION['ilancedata']['user']['slng'], 'service', $cid);
				if (!empty($valid))
				{
					$style = 'background-color:#bffdbd';
				}
				else
				{
					$style = 'background-color:#ffcccc';
				}
				$col .= '<tr valign="top" style="' . $style . '">';
				$col .= '<td>' . shorten(handle_input_keywords($res['project_title']), $cutoff) . '</td>';
				$col .= '<td>' . shorten(handle_input_keywords($res['description']), $cutoff) . '</td>';
				$col .= '<td>' . shorten(handle_input_keywords($res['keywords']), $cutoff) . '</td>';
				$col .= '<td>' . shorten(handle_input_keywords($res['attributes']), $cutoff) . '</td>';
				$col .= '</tr>';
			}
		}
		$preview_count = ($items > $ilconfig['globalfilters_bulkuploadpreviewlimit']) ? $ilance->language->construct_phrase('{_showing_only_x_results_for_this_preview}', $ilconfig['globalfilters_bulkuploadpreviewlimit']): '';
		$col .= '</table><div style="clear:both"></div>';
	}
	else if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'assign-preview')
	{
		$bulk_id = $ilance->GPC['bulk_id'];
		$duration = isset($ilance->GPC['duration']) ? intval($ilance->GPC['duration']) : '';
		$duration = $ilance->auction_post->duration($duration, 'duration', false, 'D', true, 0);
		$durationbits = isset($ilance->GPC['duration_unit']) ? intval($ilance->GPC['duration_unit']) : 'D';
		$durationbits = $ilance->auction_post->print_duration_logic($durationbits, 'duration_unit', false, 'duration', true, 0);
		$ilance->GPC['mode'] = 'bulk';
		// #### escrow filter (if enabled, javascript will hide the payment methods input box on preview also)
		$escrowfilter = $ilance->auction_post->print_escrow_filter($cid, 'service', 'servicebuyer', false);
		$publicboard = $ilance->auction_post->print_public_board('filter_publicboard');
		$js_start = $ilance->auction_post->print_js('service', true);
		$col = $hidden = '';
		// #### column titles ##########################################
		$col .= '<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" width="100%" dir="' . $ilconfig['template_textdirection'] . '">';
		$col .= '<tr class="alt2" valign="top">';
		if (isset($ilance->GPC['col']) AND !empty($ilance->GPC['col']) AND is_array($ilance->GPC['col']))
		{
			$empty = $notempty = 0;
			foreach ($ilance->GPC['col'] AS $key => $field)
			{
				if (empty($field))
				{
					$empty++;
				}
				else
				{
					$col .= '<td><div>' . $columnphrases["$field"] . '</div></td>';
					$identifier["$key"] = $field;
					$hidden .= '<input type="hidden" name="co[' . $key . ']" value="' . handle_input_keywords($field) . '" />' . "\n";
					$notempty++;
				}
			}
		}
		$col .= '</tr>';
		// #### seller must have required fields selected from the pulldown
		$passrequirement = false;
		if (isset($identifier) AND !empty($identifier) AND is_array($identifier))
		{
			if (in_array('project_title', $identifier) AND in_array('description', $identifier))
			{
				$passrequirement = true;
			}
		}
		if ($passrequirement == false)
		{
			print_notice('{_you_did_not_assign_required_pulldown_fields}', '{_sorry_you_must_reupload_your_csv_file_and_select_the_proper_columns}', $ilpage['distribution'] . '?cmd=categories&subcmd=importservicecsv&cid=' . $ilance->GPC['cid'], '{_back}');
			exit();
		}
		// #### results ################################################
		$ilance->categories->build_array('service', $_SESSION['ilancedata']['user']['slng'], 0, true);
		$sql_fields = 'id, project_title, description, keywords, attributes, cid, dateupload, correct, user_id, currency, rfpid, sample_uploaded, bulk_id';
		$sql = $ilance->db->query("
			SELECT " . $sql_fields . "
			FROM " . DB_PREFIX . "bulk_tmp
			WHERE bulk_id = '" . intval($ilance->GPC['bulk_id']) . "'
		", 0, null, __FILE__, __LINE__);
		$itemuploadcount = $ilance->db->num_rows($sql);
		$z = $sumfees = 0;
		$td = array();
		while ($res = $ilance->db->fetch_array($sql, DB_BOTH))
		{
			$z++;
			$td['class'][$z] = '';
			$currencyid = $ilconfig['globalserverlocale_defaultcurrency'];
			$canpost = $ilance->categories->can_post($_SESSION['ilancedata']['user']['slng'], 'service', $res['cid']);
			$fontclass1 = ($canpost) ? 'black'   : 'red';
			$fontclass2 = ($canpost) ? 'black'   : 'red';
			$fontclass3 = ($canpost) ? 'black'  : 'red';
			$fontclass4 = ($canpost) ? 'black' : 'red';
			$fontclass5 = ($canpost) ? 'black'  : 'red';
			$td['style'][$z] = ($canpost) ? 'background-color:#bffdbd' : 'background-color:#ffcccc';
			foreach ($identifier AS $key => $value)
			{
				if ($value == 'project_title')
				{
					$td[$value][$z] = '<td valign="top"><div class="' . $fontclass1 . '">' . handle_input_keywords(short_string(print_string_wrap($res[$key], 50), 100)) . '</td>';
				}
				else if ($value == 'description')
				{
					$td[$value][$z] = '<td valign="top"><div class="' . $fontclass2 . '">' . handle_input_keywords(short_string(print_string_wrap($res[$key], 50), 100)) . '</td>';
				}
				else if ($value == 'keywords')
				{
					$td[$value][$z] = '<td valign="top"><div class="' . $fontclass1 . '">' . handle_input_keywords(short_string(print_string_wrap($res[$key], 50), 100)) . '</td>';
				}
				else if ($value == 'attributes')
				{
					$td[$value][$z] = '<td valign="top"><div class="' . $fontclass1 . '">' . handle_input_keywords(short_string(print_string_wrap($res[$key], 50), 100)) . '</td>';
				}
			}
			if ($canpost)
			{
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "bulk_tmp
					SET correct = '1'
					WHERE id = '" . intval($res['0']) . "'
				");
				$td['class'][$z] = '';
				$disabled = 1;
			}
		}
		$limit = ($z <= $ilconfig['globalfilters_bulkuploadpreviewlimit']) ? $z : $ilconfig['globalfilters_bulkuploadpreviewlimit'];
		for ($a = 1; $a <= $limit; $a++)
		{
			$col .= '<tr class="' . $td['class'][$a] . '" style="' . $td['style'][$a] . '" valign="top">';
			foreach ($identifier AS $key => $value)
			{
				$col .= isset($td[$value][$a]) ? $td[$value][$a] : '';
			}
			$col .= '</tr>';
		}
		unset($z);
		$col .= '</table>';
		$draft = (isset($ilance->GPC['saveasdraft']) AND $ilance->GPC['saveasdraft']) ? 'checked="checked"' : '';
		$saveasdraft = '<label for="savedraft"><input type="checkbox" id="savedraft" name="saveasdraft" value="1" ' . $draft . ' /> {_save_this_auction_as_a_draft}</label>';
		$disabled = isset($disabled) ? '' : 'disabled="disabled"';
	}
	else if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'assign-import')
	{	
		$bulk_id = isset($ilance->GPC['bulk_id']) ? intval($ilance->GPC['bulk_id']) : 0;
		$sql_fields = 'id, project_title, description, keywords, attributes, cid, dateupload, correct, user_id, currency, rfpid, sample_uploaded, bulk_id';
		$sql = $ilance->db->query("
			SELECT " . $sql_fields . "
			FROM " . DB_PREFIX . "bulk_tmp
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND bulk_id = '" . $bulk_id . "'
				AND correct = '1'
		", 0, null, __FILE__, __LINE__);
		$itemuploadcount = $ilance->db->num_rows($sql);
		if (isset($ilance->GPC['co']) AND !empty($ilance->GPC['co']) AND is_array($ilance->GPC['co']))
		{
			$empty = $notempty = 0;
			foreach ($ilance->GPC['co'] AS $key => $field)
			{
				if (empty($field))
				{
					$empty++;
				}
				else
				{
					$identifier["$key"] = $field;
					$notempty++;
				}
			}
		}
		$pids = array();
		$z = 1;
		$a = $b = 0;
		while ($res = $ilance->db->fetch_array($sql, DB_BOTH))
		{
			foreach ($identifier AS $key => $value)
			{
				switch ($value)
				{
					case 'project_title':
					{
						$ilance->GPC['project_title'] = $res[$key];
						break;
					}
					case 'description':
					{
						$ilance->GPC['description'] = $res[$key];
						break;
					}
					case 'cid':
					{
						$ilance->GPC['cid'] = (isset($res[$key])) ? intval($res[$key]) : '0';
						break;
					}
					case 'keywords':
					{
						$ilance->GPC['keywords'] = (isset($res[$key])) ? ($res[$key]) : '';
						break;
					}
					case 'attributes':
					{
						$ilance->GPC['attributes'] = (isset($res[$key])) ? ($res[$key]) : '';
						break;
					}
				}
			}
			$ilance->GPC['rfpid'] = $ilance->auction_rfp->construct_new_auctionid_bulk();
			$pids[] = $ilance->GPC['rfpid'];
			// #### AUCTION FILTERS ########################
			$ilance->GPC['filtered_auctiontype'] = 'regular';
			$ilance->GPC['filter_rating'] = isset($ilance->GPC['filter_rating']) ? intval($ilance->GPC['filter_rating']) : '0';
			$ilance->GPC['filtered_rating'] = isset($ilance->GPC['filtered_rating']) ? $ilance->GPC['filtered_rating'] : '';
			$ilance->GPC['filter_country'] = isset($ilance->GPC['filter_country']) ? intval($ilance->GPC['filter_country']) : '0';
			$ilance->GPC['filtered_country'] = isset($ilance->GPC['filtered_country']) ? $ilance->GPC['filtered_country'] : '';
			$ilance->GPC['filter_state'] = isset($ilance->GPC['filter_state']) ? intval($ilance->GPC['filter_state']) : '0';
			$ilance->GPC['filtered_state'] = isset($ilance->GPC['filtered_state']) ? $ilance->GPC['filtered_state'] : '';
			$ilance->GPC['filter_city'] = isset($ilance->GPC['filter_city']) ? intval($ilance->GPC['filter_city']) : '0';
			$ilance->GPC['filtered_city'] = isset($ilance->GPC['filtered_city']) ? $ilance->GPC['filtered_city'] : '';
			$ilance->GPC['filter_zip'] = isset($ilance->GPC['filter_zip']) ? intval($ilance->GPC['filter_zip']) : '0';
			$ilance->GPC['filtered_zip'] = isset($ilance->GPC['filtered_zip']) ? $ilance->GPC['filtered_zip'] : '';
			$ilance->GPC['filter_underage'] = isset($ilance->GPC['filter_underage']) ? $ilance->GPC['filter_underage'] : '0';
			$ilance->GPC['filter_businessnumber'] = isset($ilance->GPC['filter_businessnumber']) ? $ilance->GPC['filter_businessnumber'] : '0';
			$ilance->GPC['filter_publicboard'] = isset($ilance->GPC['filter_publicboard']) ? intval($ilance->GPC['filter_publicboard']) : '0';
			$ilance->GPC['filter_escrow'] = isset($ilance->GPC['filter_escrow']) ? intval($ilance->GPC['filter_escrow']) : '0';
			$ilance->GPC['filter_gateway'] = '0';
			$ilance->GPC['filter_offline'] = isset($ilance->GPC['filter_offline']) ? intval($ilance->GPC['filter_offline']) : '0';
			$ilance->GPC['paymethod'] = isset($ilance->GPC['paymethod']) ? $ilance->GPC['paymethod'] : array();
			$ilance->GPC['paymethodoptions'] = isset($ilance->GPC['paymethodoptions']) ? $ilance->GPC['paymethodoptions'] : array();
			$ilance->GPC['paymethodoptionsemail'] = isset($ilance->GPC['paymethodoptionsemail']) ? $ilance->GPC['paymethodoptionsemail'] : array();
			// #### CUSTOM BIDDING TYPE ACCEPTANCE FILTERS #########
			$ilance->GPC['filter_bidtype'] = isset($ilance->GPC['filter_bidtype']) ? $ilance->GPC['filter_bidtype'] : '0';
			$ilance->GPC['filtered_bidtype'] = isset($ilance->GPC['filtered_bidtype']) ? $ilance->GPC['filtered_bidtype'] : 'entire';
			// #### AUCTION DETAILS ################################
			$ilance->GPC['description_videourl'] = isset($ilance->GPC['description_videourl']) ? strip_tags($ilance->GPC['description_videourl']) : '';
			$ilance->GPC['project_type'] = 'reverse';
			$ilance->GPC['project_state'] = 'service';
			$ilance->GPC['project_details'] = 'public';
			$ilance->GPC['bid_details'] = 'open';
			$ilance->GPC['additional_info'] = isset($ilance->GPC['additional_info']) ? $ilance->GPC['additional_info'] : '';
			$ilance->GPC['status'] = 'open';
			$ilance->GPC['draft'] = '0';
			if (isset($ilance->GPC['saveasdraft']) AND $ilance->GPC['saveasdraft'])
			{
				$ilance->GPC['draft'] = '1';
				$ilance->GPC['status'] = 'draft';
			}
			$ilance->GPC['filter_budget'] = isset($ilance->GPC['filter_budget']) ? $ilance->GPC['filter_budget'] : 0;
			// #### BUDGET DETAILS #################################
			if ($ilance->GPC['filter_budget'] == 0)
			{
				$ilance->GPC['filtered_budgetid'] = 0;
			}
			// #### CUSTOM INFORMATION #############################
			$ilance->GPC['custom'] = (!empty($ilance->GPC['custom']) ? $ilance->GPC['custom'] : array());
			$ilance->GPC['pa'] = (!empty($ilance->GPC['pa']) ? $ilance->GPC['pa'] : array());
			$ilance->GPC['enhancements'] = (!empty($ilance->GPC['enhancements']) ? $ilance->GPC['enhancements'] : array());
			// #### SCHEDULED AUCTION ONLY #########################
			$ilance->GPC['year'] = (isset($ilance->GPC['year'])) ? $ilance->GPC['year'] : '';
			$ilance->GPC['month'] = (isset($ilance->GPC['month'])) ? $ilance->GPC['month'] : '';
			$ilance->GPC['day'] = (isset($ilance->GPC['day'])) ? $ilance->GPC['day'] : '';
			$ilance->GPC['hour'] = (isset($ilance->GPC['hour'])) ? $ilance->GPC['hour'] : '';
			$ilance->GPC['min'] = (isset($ilance->GPC['min'])) ? $ilance->GPC['min'] : '';
			$ilance->GPC['sec'] = (isset($ilance->GPC['sec'])) ? $ilance->GPC['sec'] : '';
			// #### service location #######################################
			$ilance->GPC['city'] = (isset($ilance->GPC['city'])) ? $ilance->GPC['city'] : $_SESSION['ilancedata']['user']['city'];
			$ilance->GPC['state'] = (isset($ilance->GPC['state'])) ? $ilance->GPC['state'] : $_SESSION['ilancedata']['user']['state'];
			$ilance->GPC['zipcode'] = (isset($ilance->GPC['zipcode'])) ? $ilance->GPC['zipcode'] : $_SESSION['ilancedata']['user']['postalzip'];
			$ilance->GPC['country'] = (isset($ilance->GPC['country'])) ? $ilance->GPC['country'] : $_SESSION['ilancedata']['user']['country'];
			// #### currency ###############################################
			$ilance->GPC['currencyid'] = (isset($ilance->GPC['currencyid'])) ? intval($ilance->GPC['currencyid']) : $ilconfig['globalserverlocale_defaultcurrency'];
			// #### invited registered service providers ###################
			$ilance->GPC['invitedmember'] = isset($ilance->GPC['invitedmember']) ? $ilance->GPC['invitedmember'] : array();
			$ilance->GPC['invitelist'] = isset($ilance->GPC['invitelist']) ? $ilance->GPC['invitelist'] : '';
			$ilance->GPC['invitemessage'] = isset($ilance->GPC['invitemessage']) ? $ilance->GPC['invitemessage'] : '';
			$ilance->GPC['filter_bidlimit'] = isset($ilance->GPC['filter_bidlimit']) ? $ilance->GPC['filter_bidlimit'] : ''; 
			$ilance->GPC['filtered_bidlimit'] = isset($ilance->GPC['filtered_bidlimit']) ? $ilance->GPC['filtered_bidlimit'] : '';
			// #### CREATE AUCTION #################################
			$ilance->auction_rfp->insert_service_auction(
				$_SESSION['ilancedata']['user']['userid'],
				$ilance->GPC['project_type'],
				$ilance->GPC['status'],
				$ilance->GPC['project_state'],
				$ilance->GPC['cid'],
				$ilance->GPC['rfpid'],
				$ilance->GPC['project_title'],
				$ilance->GPC['description'],
				$ilance->GPC['description_videourl'],
				$ilance->GPC['additional_info'],
				$ilance->GPC['keywords'],
				$ilance->GPC['custom'],
				$ilance->GPC['pa'],
				$ilance->GPC['filter_bidtype'],
				$ilance->GPC['filtered_bidtype'],
				$ilance->GPC['filter_budget'],
				$ilance->GPC['filtered_budgetid'],
				$ilance->GPC['filtered_auctiontype'],
				$ilance->GPC['filter_escrow'],
				$ilance->GPC['filter_gateway'],
				$ilance->GPC['filter_offline'],
				$ilance->GPC['paymethod'],
				$ilance->GPC['paymethodoptions'],
				$ilance->GPC['paymethodoptionsemail'],
				$ilance->GPC['project_details'],
				$ilance->GPC['bid_details'],
				$ilance->GPC['invitelist'],
				$ilance->GPC['invitemessage'],
				$ilance->GPC['invitedmember'],
				$ilance->GPC['year'],
				$ilance->GPC['month'],
				$ilance->GPC['day'],
				$ilance->GPC['hour'],
				$ilance->GPC['min'],
				$ilance->GPC['sec'],
				$ilance->GPC['duration'],
				$ilance->GPC['duration_unit'],
				$ilance->GPC['filtered_rating'],
				$ilance->GPC['filtered_country'],
				$ilance->GPC['filtered_state'],
				$ilance->GPC['filtered_city'],
				$ilance->GPC['filtered_zip'],
				$ilance->GPC['filter_rating'],
				$ilance->GPC['filter_country'],
				$ilance->GPC['filter_state'],
				$ilance->GPC['filter_city'],
				$ilance->GPC['filter_zip'],
				$ilance->GPC['filter_bidlimit'], 
				$ilance->GPC['filtered_bidlimit'],
				$ilance->GPC['filter_underage'],
				$ilance->GPC['filter_businessnumber'],
				$ilance->GPC['filter_publicboard'],
				$ilance->GPC['enhancements'],
				$ilance->GPC['draft'],
				$ilance->GPC['city'],
				$ilance->GPC['state'],
				$ilance->GPC['zipcode'],
				$ilance->GPC['country'],
				$skipemailprocess = 1,
				array(),
				$isbulkupload = true,
				$ilance->GPC['currencyid']
			);
			$items = $ilance->db->fetch_field(DB_PREFIX . "projects", "", "count(id)");
			if ($items > $a)
			{
				$a = $items;
				$b++;
			}
			$z++;
			$sql2 = $ilance->db->query("
				SELECT id
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . intval($ilance->GPC['rfpid']) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql2) == 1)
			{
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "bulk_tmp
					SET rfpid = '" . intval($ilance->GPC['rfpid']) . "'
					WHERE id = '" . $res['id'] . "'
				", 0, null, __FILE__, __LINE__);

				$ilance->db->query("
					UPDATE " . DB_PREFIX . "projects
					SET bulkid = '" . intval($bulk_id) . "'
					WHERE project_id = '" . intval($ilance->GPC['rfpid']) . "'
				", 0, null, __FILE__, __LINE__);
			}
		}
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "bulk_sessions
			SET itemsuploaded = '" . intval($b) . "'
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND id = '" . $bulk_id . "'
		", 0, null, __FILE__, __LINE__);
		$posted_auctions = $b;
		$sql2 = $ilance->db->query("SELECT " . $sql_fields . " FROM " . DB_PREFIX . "bulk_tmp WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' AND bulk_id = '" . $bulk_id . "' AND correct = '0'", 0, null, __FILE__, __LINE__);
		$incorrect_auctions = $ilance->db->num_rows($sql2);
		refresh(HTTPS_SERVER_ADMIN . $ilpage['distribution'] . '?cmd=categories');
		exit();
	}
}
// #### IMPORT PRODUCT CSV CATEGORY LISTINGS ###########################
else if ($ilance->GPC['subcmd'] == 'importproductcsv')
{
	refresh(HTTP_SERVER . $ilpage['bulk'] . '?cmd=sell');
	exit();
}
// #### IMPORT SERVICE CSV CATEGORY LISTINGS ###########################
else if ($ilance->GPC['subcmd'] == 'importcategorycsv')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'assign')
	{
		if (empty($_FILES['csv_file']['name']))
		{
			print_notice('{_invalid_bulk_import_file}', '{_the_bulk_file_you_uploaded_was_not_correct_please_try_again}', 'javascript:history.go(-1)', '{_retry}');
			exit();
		}
		else
		{
			$extension = mb_strtolower(mb_strrchr($_FILES['csv_file']['name'], '.'));
			if ($extension != '.csv')
			{
				print_notice('{_invalid_file_extension}', '{_the_bulk_file_you_uploaded_did_not_have_the_correct_file_extension}', 'javascript:history.go(-1)', '{_retry}');
				exit();
			}
		}
		while (list($key, $value) = each($_FILES))
		{
			$GLOBALS[$key] = $value;
			foreach ($_FILES AS $key => $value)
			{
				$GLOBALS[$key] = $_FILES[$key]['tmp_name'];
				foreach ($value AS $ext => $value2)
				{
					$key2 = $key . '_' . $ext;
					$GLOBALS[$key2] = $value2;
				}
			}
		}
		$tmp_name = $_FILES['csv_file']['tmp_name'];
		$file_name = DIR_SERVER_ROOT . DIR_UPLOADS_NAME . '/' . $_FILES['csv_file']['name'];
		if (file_exists($file_name))
		{
			@unlink($file_name);
		}
		move_uploaded_file($tmp_name, $file_name);
		// #### standardize line breaks ########################
		$data = file_get_contents($file_name);
		$data = str_replace(array("\r\n", "\r", "\n"), LINEBREAK, $data);
		file_put_contents($file_name, $data);
		// #### handle importing ###############################
		$containsheader = isset($ilance->GPC['containsheader']) ? true : false;
		$deletecurrent = isset($ilance->GPC['deletecurrent']) ? true : false;
		$ilance->csv->category_csv_to_db($data, $containsheader, $deletecurrent);
		refresh(HTTPS_SERVER_ADMIN . $ilpage['distribution'] . '?cmd=categories');
		exit();
	}
}

/*=======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*====================================================================== */
?>