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
if (!defined('LOCATION') OR defined('LOCATION') != 'admin')
{
    die('<strong>Fatal:</strong> This script cannot be parsed indirectly.');
}

$area_title = '{_skills_management}';
$page_title = SITE_NAME . ' - {_skills_management}';

($apihook = $ilance->api('admincp_skills_settings')) ? eval($apihook) : false;

$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['subscribers'], $ilpage['subscribers'] . '?cmd=skills', $_SESSION['ilancedata']['user']['slng']);

// #### template url defaults ##################################
$ilance->GPC['pp'] = (!isset($ilance->GPC['pp']) OR isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] <= 0) ? $ilconfig['globalfilters_maxrowsdisplay'] : intval($ilance->GPC['pp']);
$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
$counter = ($ilance->GPC['page'] - 1) * $ilance->GPC['pp'];
// save skill category display order
if (isset($ilance->GPC['savesort']) AND !empty($ilance->GPC['savesort']))
{
	foreach ($ilance->GPC['sort'] AS $key => $sortid)
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "skills
			SET sort = '" . intval($sortid) . "'
			WHERE cid = '" . intval($key) . "'
		");
	}
	print_action_success('{_skills_sorting_display_order_has_been_updated}', $ilpage['subscribers'] . '?cmd=skills');
	exit();
}
// add or update a skill category
if (isset($ilance->GPC['subcmd']) AND ($ilance->GPC['subcmd'] == 'editskill' OR $ilance->GPC['subcmd'] == 'addskill'))
{
	// edit skill category
	if ($ilance->GPC['subcmd'] == 'editskill')
	{
		$categorycache = $ilance->categories_skills->build_array_skills($_SESSION['ilancedata']['user']['slng']);
		$cid = intval($ilance->GPC['cid']);
		$pid = $ilance->categories_skills->parentid(fetch_site_slng(), $cid);
		$subcat_pulldown = $ilance->categories_pulldown->print_cat_pulldown($pid, 'skills', 'level', 'pid', $showpleaseselectoption = 1, fetch_site_slng(), $nooptgroups = 1, $prepopulate = '', $mode = 4, $showallcats = 0, $dojs = 0, $width = '540px', $uid = 0, $forcenocount = 1, $expertspulldown = 0, $canassigntoall = false, $showbestmatching = false, $categorycache);
		unset($categorycache);
		$submit = '<input type="submit" name="submit" value=" {_save} " style="font-size:15px" class="buttons" />';
		$subcmd = 'updateskill';
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "skills
			WHERE cid = '" . intval($ilance->GPC['cid']) . "'
			LIMIT 1
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$res['keywords'] = '<input type="text" name="keywords" value="' . $ilance->categories_skills->keywords(fetch_site_slng(), $cid, false, false) . '" style="width:98%" />';
				$servicecategory[] = $res;
			}
		}
		$row_count = 0;
		$languages = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "language");
		while ($language = $ilance->db->fetch_array($languages, DB_ASSOC))
		{
			$language['slng'] = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
			$language['language'] = $language['title'];
			$sql = $ilance->db->query("
				SELECT title_" . $language['slng'] . " AS title, description_" . $language['slng'] . " AS description, seourl_" . $language['slng'] . " AS seourl
				FROM " . DB_PREFIX . "skills
				WHERE cid = '" . intval($ilance->GPC['cid']) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					$language['title'] = $res['title'];
					$language['description'] = $res['description'];
					$language['seourl'] = $res['seourl'];
				}
			}
			$servicelanguages[] = $language;
			$row_count++;
		}
	}
	// add new skill category
	else if ($ilance->GPC['subcmd'] == 'addskill')
	{
		$cid = intval($ilance->GPC['cid']);
		$categorycache = $ilance->categories_skills->build_array_skills($_SESSION['ilancedata']['user']['slng']);
		$subcat_pulldown = $ilance->categories_pulldown->print_cat_pulldown($cid, 'skills', 'level', 'pid', 1, fetch_site_slng(), 1, '', 4, 1, 0, '540px', 0, 1, 0, false, false, $categorycache);
		foreach ($categorycache AS $key => $value)
		{
			if ($value['cid'] == $cid AND $value['level'] > 1)
			{
				print_action_failed('{_sorry_you_can_only_create_new_skill_categories_based_on_2_levels}', $ilpage['subscribers'] . '?cmd=skills');
				exit();
			}
		}
		unset($categorycache);
		$submit = '<input type="submit" value=" {_save} " style="font-size:15px" class="buttons" />';
		$subcmd = 'insertskill';
		$res['keywords'] = '<input class="input" type="text" name="keywords" value="" style="width:98%" />';
		$res['sort'] = '0';
		$servicecategory[] = $res;
		$row_count = 0;
		$languages = $ilance->db->query("
			SELECT languagecode, title
			FROM " . DB_PREFIX . "language
		");
		while ($language = $ilance->db->fetch_array($languages, DB_ASSOC))
		{
			$language['slng'] = mb_strtolower(mb_substr($language['languagecode'], 0, 3));
			$language['language'] = $language['title'];
			$language['title'] = '';
			$language['description'] = '';
			$language['seourl'] = '';
			$servicelanguages[] = $language;
			$row_count++;
		}
	}
	$pprint_array = array ('submit', 'subcmd', 'subcat_pulldown', 'question_inputtype_pulldown', 'questionid', 'cid', 'categoryname', 'language_pulldown', 'slng', 'checked_question_cansearch', 'checked_question_active', 'checked_question_required', 'subcategory_pulldown', 'formdefault', 'multiplechoice', 'question', 'description', 'formname', 'sort', 'submit_category_question', 'question_id_hidden', 'question_subcmd', 'question_inputtype_pulldown', 'subcatid', 'subcatname', 'catname', 'service_subcategories', 'product_categories', 'subcmd', 'id', 'submit', 'description', 'name', 'checked_profile_group_active');
    
	($apihook = $ilance->api('admincp_skills_edit_end')) ? eval($apihook) : false;
    
	$ilance->template->fetch('main', 'skills_edit.html', 1);
	$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array ('v3nav', 'subnav_settings'), false);
	$ilance->template->parse_loop('main', 'servicecategory');
	$ilance->template->parse_loop('main', 'servicelanguages');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// update skill category
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'updateskill')
{
	$cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
	$pid = isset($ilance->GPC['pid']) ? intval($ilance->GPC['pid']) : 0;
	if ($cid == $pid)
	{
		print_action_failed('{_the_category_you_are_trying_to_save_cannot_be_the_same_category}', 'javascript:history.back(1);');
		exit();
	}
	$query1 = '';
	if (!empty($ilance->GPC['title']))
	{
		foreach ($ilance->GPC['title'] AS $slng => $value)
		{
			$query1 .= "title_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "', ";
		}
	}
	$query2 = '';
	if (!empty($ilance->GPC['description']))
	{
		foreach ($ilance->GPC['description'] AS $slng => $value)
		{
			$query2 .= "description_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "', ";
		}
	}
	$query3 = '';
	if (!empty($ilance->GPC['seourl']))
	{
		foreach ($ilance->GPC['seourl'] AS $slng => $value)
		{
			$query2 .= "seourl_" . mb_strtolower($slng) . " = '" . $ilance->db->escape_string($value) . "', ";
		}
	}
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "skills
		SET parentid = '" . intval($pid) . "',
		$query1
		$query2
		$query3
		keywords = '" . $ilance->db->escape_string($ilance->GPC['keywords']) . "',
		sort = '" . intval($ilance->GPC['sort']) . "'
		WHERE cid = '" . intval($cid) . "'
		LIMIT 1
	");
	$ilance->categories_skills->set_levels_skills();
	refresh($ilance->GPC['return']);
	exit();
}
// create new skill category handler
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'insertskill')
{
	$cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
	$pid = isset($ilance->GPC['pid']) ? intval($ilance->GPC['pid']) : 0;
	$titleerror = false;
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
			print_action_failed('{_sorry_to_create_a_new_skill_category_you_must_define_a_title}', $ilpage['subscribers'] . '?cmd=skills&subcmd=addskill&cid=' . $cid);
			exit();
		}
	}
	else
	{
		print_action_failed('{_sorry_to_create_a_new_skill_category_you_must_define_a_title_for_all_available}', $ilpage['subscribers'] . '?cmd=skills&subcmd=addskill&cid=' . $cid);
		exit();
	}
	// handle multilanguage descriptions
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
		if ($descriptionerror)
		{
			print_action_failed('{_sorry_to_create_a_new_skill_category_you_must_define_a_description}', $ilpage['subscribers'] . '?cmd=skills&subcmd=addskill&cid=' . $cid);
			exit();
		}
	}
	else
	{
		print_action_failed('{_sorry_to_create_a_new_skill_category_you_must_define_a_description_for_all_available}', $ilpage['subscribers'] . '?cmd=skills&subcmd=addskill&cid=' . $cid);
		exit();
	}
	// handle multilanguage seourls
	$seourlerror = false;
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
			else
			{
				$seourlerror = true;
			}
		}
		if ($seourlerror)
		{
			print_action_failed('{_sorry_to_create_a_new_skill_category_you_must_define_a_seourl}', $ilpage['subscribers'] . '?cmd=skills&subcmd=addskill&cid=' . $cid);
			exit();
		}
	}
	else
	{
		print_action_failed('{_sorry_to_create_a_new_skill_category_you_must_define_a_seourl_for_all_available}', $ilpage['subscribers'] . '?cmd=skills&subcmd=addskill&cid=' . $cid);
		exit();
	}
	$keywords = isset($ilance->GPC['keywords']) ? $ilance->db->escape_string($ilance->GPC['keywords']) : '';
	$sort = isset($ilance->GPC['sort']) ? intval($ilance->GPC['sort']) : '0';
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "skills
		(cid, parentid, $titlefields $descriptionfields $seourlfields keywords, sort)
		VALUES(
		NULL,
		'" . $pid . "',
		$titlevalues
		$descriptionvalues
		$seourlvalues
		'" . $keywords . "',
		'" . $sort . "')
	", 0, null, __FILE__, __LINE__);
	$insid = $ilance->db->insert_id();
	$ilance->categories_skills->set_levels_skills();
	print_action_success('{_new_skill_category_was_added}', $ilance->GPC['return']);
	exit();
}
// remove single skill category
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'removeskill' AND isset($ilance->GPC['cid']))
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	if ($ilance->categories_skills->can_remove_skill_categories())
	{
		$ilance->categories_skills->build_array_skills($_SESSION['ilancedata']['user']['slng'], -1, 0, 0);
		$ilance->categories_skills->remove_skills_category_recursive(intval($ilance->GPC['cid']));
		$ilance->categories_skills->set_levels_skills();
		print_action_success('{_the_selected_skill_category_was_removed_from_the_category_system}', $ilpage['subscribers'] . '?cmd=skills');
		exit();
	}
	else
	{
		print_action_failed('{_sorry_you_must_have_at_least_1_skill_category_in_the_system_please_update_or_modify_this_category_instead}', $ilpage['subscribers'] . "?cmd=skills");
		exit();
	}
}
// remove multiple skill categories
else if (isset($ilance->GPC['remove']) AND !empty($ilance->GPC['remove']) AND isset($ilance->GPC['skillcid']) AND !empty($ilance->GPC['skillcid']) AND is_array($ilance->GPC['skillcid']))
{
	$sql = $ilance->db->query("SELECT COUNT(*) AS count FROM " . DB_PREFIX . "skills");
	$res = $ilance->db->fetch_array($sql, DB_ASSOC);
	if ($res['count'] != count($ilance->GPC['skillcid']))
	{
		$ilance->categories_skills->build_array_skills($_SESSION['ilancedata']['user']['slng'], -1, 0, 0);
		foreach ($ilance->GPC['skillcid'] AS $skillcid)
		{
			if (isset($skillcid) AND $skillcid > 0)
			{
				$ilance->categories_skills->remove_skills_category_recursive(intval($skillcid));
			}
		}
		$ilance->categories_skills->set_levels_skills();
		print_action_success('{_the_selected_skill_category_was_removed_from_the_category_system}', $ilpage['subscribers'] . '?cmd=skills');
		exit();
	}
	else
	{
		print_action_failed('{_sorry_you_must_have_at_least_1_skill_category_in_the_system_please_update_or_modify_this_category_instead}', $ilpage['subscribers'] . '?cmd=skills');
		exit();
	}
}
// add new skill category test for current selected skill category
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'addskilltest')
{
	echo 'coming soon!';
	exit;
}
// main skill categories landing page
else
{
	$ilance->GPC['level'] = (!isset($ilance->GPC['level']) OR isset($ilance->GPC['level']) AND $ilance->GPC['level'] <= 0) ? 10 : intval($ilance->GPC['level']);
	$ilance->GPC['title'] = !empty($ilance->GPC['title']) ? $ilance->GPC['title'] : '';
	$ilance->GPC['visible'] = !empty($ilance->GPC['visible']) ? $ilance->GPC['visible'] : '';
	$ilance->GPC['cid'] = !empty($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
	$cid = ($ilance->GPC['cid'] > 0) ? $ilance->GPC['cid'] : '';
	$page = $ilance->GPC['page'];
	$title = handle_input_keywords($ilance->GPC['title']);
	$counter = ($ilance->GPC['page'] - 1) * $ilance->GPC['pp'];
	$row_count = 0;
	$ilance->GPC['pp'] = (!isset($ilance->GPC['pp']) OR isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] <= 0) ? $ilconfig['globalfilters_maxrowsdisplay'] : intval($ilance->GPC['pp']);
	$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
	$page = $ilance->GPC['page'];
	$pp = $ilance->GPC['pp'];
	$counter = ($ilance->GPC['page'] - 1) * $ilance->GPC['pp'];
	$count = $ilance->db->query("
		SELECT COUNT(*) AS count
		FROM " . DB_PREFIX . "skills
		WHERE level <= '" . intval($ilance->GPC['level']) . "'
		" . ((isset($ilance->GPC['title']) AND !empty($ilance->GPC['title'])) ? "AND title_" . $_SESSION['ilancedata']['user']['slng'] . " LIKE '%" . $ilance->db->escape_string($ilance->GPC['title']) . "%'" : "") . "
		" . ((isset($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0) ? "AND cid = '" . intval($ilance->GPC['cid']) . "'" : "") . "
	");
	$count = $ilance->db->fetch_array($count, DB_ASSOC);
	$count = $count['count'];
	$propersort = false;
	if (isset($ilance->GPC['title']) AND !empty($ilance->GPC['title']) OR isset($ilance->GPC['cid']) AND !empty($ilance->GPC['cid']))
	{
		$propersort = true;
	}
	$ilance->categories->cats = array ();
	$ilance->categories->cats = $ilance->categories_skills->build_array_skills($_SESSION['ilancedata']['user']['slng'], -1, $counter, $ilance->GPC['level'], $ilance->GPC['cid'], $ilance->GPC['title'], 1, $propersort);
	$startfrom = ($ilance->GPC['page'] > 1) ? ($ilance->GPC['pp'] + $counter - $ilance->GPC['pp']) : 0;
	$endat = ($ilance->GPC['pp'] + $counter);
	for ($i = 0; $i < ($count); $i++)
	{
		if (isset($ilance->categories->cats[$i]['cid']) AND !empty($ilance->categories->cats[$i]['cid']) AND $ilance->categories->cats[$i]['cid'] > 0)
		{
			$GLOBALS['level' . $ilance->categories->cats[$i]['cid']] = $ilance->categories->cats[$i]['level'];
			$ilance->categories->cats[$i]['rootcategory'] = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($ilance->categories->cats[$i]['rootcid']) . "'", "title_" . $_SESSION['ilancedata']['user']['slng']);
			if ($ilance->categories->cats[$i]['level'] == 1)
			{
				if ($ilance->categories->cats[$i]['parentid'] == 0)
				{
					if (empty($ilance->categories->cats[$i]['rootcategory']))
					{
						$ilance->categories->cats[$i]['rootcategory'] = '<div>{_none}</div><div class="smaller blue" style="padding-top:3px" title="{_you_must_associate_this_skill_category_to_a_main_service_category_for_better_filtering_performance}"><a href="' . $ilpage['distribution'] . '?cmd=categories">{_associate_now}</a></div>';
					}
					$ilance->categories->cats[$i]['auctioncount'] = $ilance->categories_skills->fetch_skills_category_recursive_count($ilance->categories->cats[$i]['cid']);
					$ilance->categories->cats[$i]['title'] = '<a href="' . $ilpage['subscribers'] . '?cmd=skills&amp;subcmd=editskill&amp;cid=' . $ilance->categories->cats[$i]['cid'] . '" title="' . handle_input_keywords($ilance->categories->cats[$i]['description']) . '"><strong>' . handle_input_keywords($ilance->categories->cats[$i]['title']) . '</strong></a>';
				}
				else
				{
					if (empty($ilance->categories->cats[$i]['rootcategory']))
					{
						$ilance->categories->cats[$i]['rootcategory'] = '<div class="litegray">{_none}</div>';
					}
					$ilance->categories->cats[$i]['auctioncount'] = $ilance->categories_skills->fetch_skills_category_count($ilance->categories->cats[$i]['cid']);
					$ilance->categories->cats[$i]['title'] = '<a href="' . $ilpage['subscribers'] . '?cmd=skills&amp;subcmd=editskill&amp;cid=' . $ilance->categories->cats[$i]['cid'] . '" title="' . handle_input_keywords($ilance->categories->cats[$i]['description']) . '">' . handle_input_keywords($ilance->categories->cats[$i]['title']) . '</a>';
				}
			}
			else if ($ilance->categories->cats[$i]['level'] > 1)
			{
				if ($ilance->categories->cats[$i]['parentid'] == 0)
				{
					if (empty($ilance->categories->cats[$i]['rootcategory']))
					{
						$ilance->categories->cats[$i]['rootcategory'] = '<div>{_none}</div><div class="smaller blue" style="padding-top:3px" title="{_you_must_associate_this_skill_category_to_a_main_service_category_for_better_filtering_performance}"><a href="' . $ilpage['distribution'] . '?cmd=categories">{_associate_now}</a></div>';
					}
					$ilance->categories->cats[$i]['auctioncount'] = $ilance->categories_skills->fetch_skills_category_recursive_count($ilance->categories->cats[$i]['cid']);
					$ilance->categories->cats[$i]['title'] = str_repeat('<span class="gray">--</span> ', $ilance->categories->cats[$i]['level']) . '<a href="' . $ilpage['subscribers'] . '?cmd=skills&amp;subcmd=editskill&amp;cid=' . $ilance->categories->cats[$i]['cid'] . '" title="' . handle_input_keywords($ilance->categories->cats[$i]['description']) . '"><strong>' . handle_input_keywords($ilance->categories->cats[$i]['title']) . '</strong></a>';
				}
				else
				{
					if (empty($ilance->categories->cats[$i]['rootcategory']))
					{
						$ilance->categories->cats[$i]['rootcategory'] = '<div class="litegray">{_none}</div>';
					}
					$ilance->categories->cats[$i]['auctioncount'] = $ilance->categories_skills->fetch_skills_category_count($ilance->categories->cats[$i]['cid']);
					$ilance->categories->cats[$i]['title'] = str_repeat('<span class="gray">--</span> ', $ilance->categories->cats[$i]['level']) . '<a href="' . $ilpage['subscribers'] . '?cmd=skills&amp;subcmd=editskill&amp;cid=' . $ilance->categories->cats[$i]['cid'] . '" title="' . handle_input_keywords($ilance->categories->cats[$i]['description']) . '">' . handle_input_keywords($ilance->categories->cats[$i]['title']) . '</a>';
				}
			}
			else
			{
				if (empty($ilance->categories->cats[$i]['rootcategory']))
				{
					$ilance->categories->cats[$i]['rootcategory'] = '<div class="litegray">{_none}</div>';
				}
				$ilance->categories->cats[$i]['title'] = '<a href="' . $ilpage['subscribers'] . '?cmd=skills&amp;subcmd=editskill&amp;cid=' . $ilance->categories->cats[$i]['cid'] . '" title="' . handle_input_keywords($ilance->categories->cats[$i]['description']) . '">' . handle_input_keywords($ilance->categories->cats[$i]['title']) . '</a>';
			}
			$ilance->categories->cats[$i]['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			if ($i >= $startfrom AND $i < $endat)
			{
				$servicecategories[] = $ilance->categories->cats[$i];
				$row_count++;
			}
		}
	}
	$urlbit = '';
	$prevnext = print_pagnation($count, $ilance->GPC['pp'], $ilance->GPC['page'], $counter, $ilpage['subscribers'] . '?cmd=skills' . $urlbit);
	$headinclude .= "<script type=\"text/javascript\">
<!--
function category_jump(catinfo)
{
	if (catinfo == 0)
	{
		alert_js('" . '{_please_select_a_category}' . "');
		return;
	}
	else if (typeof(document.ilform.cid) != 'undefined')
	{
		action = document.ilform.controls.options[document.ilform.controls.selectedIndex].value;
	}
	else
	{
		action = eval(\"document.ilform.cid\" + catinfo + \".options[document.ilform.cid\" + catinfo + \".selectedIndex].value\");
	}
	if (action != '')
	{
		switch (action)
		{
			case 'edit': page = \"" . $ilpage['subscribers'] . "?cmd=skills&subcmd=editskill&cid=\"; break;
			case 'add': page = \"" . $ilpage['subscribers'] . "?cmd=skills&subcmd=addskill&cid=\"; break;
			case 'remove': page = \"" . $ilpage['subscribers'] . "?cmd=skills&subcmd=removeskill&cid=\"; break;
		}
		document.ilform.reset();
		jumptopage = page + catinfo + \"\";
		if (action == 'remove')
		{
			var agree = confirm_js(\"" . '{_please_take_a_moment_to_confirm_your_action_continue}' . "\");
			if (agree)
			{ 
				return window.location = jumptopage;
			}
			else
			{
				return false;
			}
		}
		else
		{
			window.location = jumptopage;
		}
	}
	else
	{
		alert_js(\"Invalid Action\");
	}
}
//-->
</script>
";
}
$configuration_skills = $ilance->admincp->construct_admin_input('skills', $ilpage['subscribers'] . '?cmd=skills');
$pprint_array = array ('pp', 'page', 'title', 'prevnext', 'configuration_skills', 'incrementgrouppulldown', 'incsort', 'inchidden', 'incform', 'incsubmit', 'incamount', 'incfrom', 'incto', 'hiddenincrementgroupid2', 'incrementgroupname', 'incrementgroupdescription', 'submitincrement', 'subcmdincrementgroup', 'language_pulldown', 'slng', 'checked_question_cansearch', 'checked_question_active', 'checked_question_required', 'subcategory_pulldown', 'formdefault', 'multiplechoice', 'question', 'description', 'formname', 'sort', 'submit_category_question', 'question_id_hidden', 'question_subcmd', 'question_inputtype_pulldown', 'subcatid', 'subcatname', 'catname', 'service_subcategories', 'product_categories', 'subcmd', 'id', 'submit', 'description', 'name', 'checked_profile_group_active');

($apihook = $ilance->api('admincp_skills_end')) ? eval($apihook) : false;

$ilance->template->fetch('main', 'skills.html', 1);
$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
$ilance->template->parse_loop('main', array ('v3nav', 'subnav_settings'), false);
$ilance->template->parse_loop('main', 'servicecategories');
$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', $pprint_array);
exit();

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>