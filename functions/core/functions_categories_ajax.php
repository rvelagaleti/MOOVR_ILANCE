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
* Global category helper functions for iLance.
*
* @package      iLance\Global\AJAX\Category
* @version      4.0.0.8059
* @author       ILance
*/

/**
* Function to print the next category box for the AJAX category selector
*
* @param       integer        category id
* @param       string         box identifier
* @param       string         category string identifier
* @param       boolean        show continue button? (default 1 = true)
* @param       boolean        show thumb's up icon (default 1 = true)
* @param       boolean        show the category id mini box to the far right bottom (default 1 = yes)
* @param       boolean        show the you selected x category string (default 1 = yes)
* @param       boolean        read-mode only (default 0 = false)
* @param       boolean        show the checkmark after the you selected x category (default 1 = yes)
* @param       boolean        show the default category finder javascript logic handler (default 0 = false)
* @param       integer        id
* @param       string         cmd action string
* @param       boolean        do rss (default 0 = false)
* @param       boolean        do news (default 0 = false)
* @param       boolean        show add another category link (default 0 = false) mainly used in preferences > category notifications
* @param       boolean        show our category finder output in API mode (so sellers know how to use it within bulk CSV files) (default 0 = false)
*
* @return      string         Returns HTML formatted string
*/
function print_next_category($cid = 0, $box = '', $cidfield = 'cid', $showcontinue = 1, $showthumb = 1, $showcidbox = 1, $showyouselectedstring = 1, $readonly = 0, $showcheckmarkafterstring = 1, $categoryfinderjs = 0, $id = 0, $cmd = '', $rss = 0, $news = 0, $showaddanother = 0, $categoryfinderapi = 0)
{
	global $ilance, $phrase, $ilconfig;
	list($j, $boxnum) = explode('_', $box);
	$boxnum++;
	$newcontent = $newcontentextra = '';
	$objResponse = new xajaxResponse();
	$objResponse->addScript("window.top.document.getElementById('$cidfield').value = '$cid';"); // outside iframe
	for ($i = ($boxnum); $i < 16; $i++)
	{
		$objResponse->addAssign('catbox_' . $i, 'innerHTML', '');
	}
	if (is_last_category($cid))
	{
		if (is_postable_category($cid))
		{
			if ($showthumb)
			{
				$newcontent = '<div style="padding-top:55px; padding-' . $ilconfig['template_textalignment'] . ':15px; font-family: Arial; font-size:13px"><span style="float:' . $ilconfig['template_textalignment'] . '; padding-' . $ilconfig['template_textalignment_alt'] . ':10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'thumbsup.gif" border="0" alt="" /></span>{_youve_selected_a_category_click_continue_button}</div>';
				$ilance->template->templateregistry['phrase'] = $newcontent;
				$newcontent = $ilance->template->parse_template_phrases('phrase');
				$objResponse->addAssign('catbox_' . ($boxnum + 1), 'innerHTML', $newcontent);
			}
			if ($showcheckmarkafterstring)
			{
				$newcontentextra = '&nbsp;&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="" />';
				$objResponse->addAssign('cidstringcb', 'innerHTML', $newcontentextra);
			}
		}
		else
		{
			if ($showcheckmarkafterstring)
			{
				$newcontentextra = '&nbsp;&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" />';
				$objResponse->addAssign('cidstringcb', 'innerHTML', $newcontentextra);
			}	
		}
	}
	else
	{
		if (is_postable_category($cid))
		{
			if ($showcheckmarkafterstring)
			{
				$newcontentextra = '&nbsp;&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="" />';
				$objResponse->addAssign('cidstringcb', 'innerHTML', $newcontentextra);
			}
		}
		else
		{
			if ($showcheckmarkafterstring)
			{
				$newcontentextra = '&nbsp;&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" />';
				$objResponse->addAssign('cidstringcb', 'innerHTML', $newcontentextra);
			}	
		}
	}
	if ($cid == '-1')
	{
		$recursivecats = '{_assign_to_all_categories}';
	}
	else if ($cid == '0')
	{
		$recursivecats = '{_no_parent_category}';
	}
	else
	{
		if ($showaddanother)
		{
			if ($cmd == 'product')
			{
				$recursivecats = '<div style="padding-top:3px" id="hiderow_' . $cid . '"><input type="hidden" id="subcategories2_' . $cid . '" name="subcategories2[]" value="' . intval($cid) . '" /><span class="blue">' . $ilance->categories->recursive($cid, $cmd, $_SESSION['ilancedata']['user']['slng'], 1, '', 0) . '</span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="smaller blue">(<a href="javascript:void(0)" onclick="fetch_js_object(\x27subcategories2_' . $cid . '\x27).disabled=true;toggle_hide(\x27hiderow_' . $cid . '\x27)" style="text-decoration:underline">{_remove}</a>)</span></div>';
			}
			else if ($cmd == 'service')
			{
				$recursivecats = '<div style="padding-top:3px" id="hiderow_' . $cid . '"><input type="hidden" id="subcategories_' . $cid . '" name="subcategories[]" value="' . intval($cid) . '" /><span class="blue">' . $ilance->categories->recursive($cid, $cmd, $_SESSION['ilancedata']['user']['slng'], 1, '', 0) . '</span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="smaller blue">(<a href="javascript:void(0)" onclick="fetch_js_object(\x27subcategories_' . $cid . '\x27).disabled=true;toggle_hide(\x27hiderow_' . $cid . '\x27)" style="text-decoration:underline">{_remove}</a>)</span></div>';
			}
		}
		else
		{
			$recursivecats = $ilance->db->escape_string($ilance->categories->recursive($cid, $ilance->GPC['mode'], $_SESSION['ilancedata']['user']['slng'], 1, '', 0));
		}
	}
	$divcontent = ($showyouselectedstring)
		? '<div style="padding-top:10px; padding-bottom:5px"><strong>{_you_have_selected_the_following_category}</strong></div>' . $recursivecats
		: $recursivecats;
		
	$divcontent .= $newcontentextra;
	unset($recursivecats);
	$ilance->template->templateregistry['phrase'] = $divcontent;
	$divcontent = $ilance->template->parse_template_phrases('phrase');
	$objResponse->addScript("window.top.document.getElementById('selectedcategory').innerHTML = '$divcontent';");
	if ($showcontinue)
	{
		$ilance->template->templateregistry['phrase'] = '<div style="padding-top:10px"><input type="submit" value="{_continue}" class="buttons" style="font-size:15px" /></div>';
		$div2content = $ilance->template->parse_template_phrases('phrase');
		$objResponse->addScript("window.top.document.getElementById('categorybutton').innerHTML = '$div2content';");
	}
	if ($ilconfig['template_textalignment'] == 'left')
	{
		$objResponse->addScript("window.scrollTo(2500,0);");
	}
	else
	{
		$objResponse->addScript("window.scrollTo(0,2500);");
	}
	$selectedindex = array();
	$index = 0;
	$rssquery = $newsquery = "";
        if ($rss)
        {
                $rssquery = "AND xml = '1' ";
        }
        if ($news)
        {
                $newsquery = "AND newsletter = '1' ";
        }
	$getcats = $ilance->db->query("
		SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "cid, title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title
		FROM " . DB_PREFIX . "categories
		WHERE parentid = '" . intval($cid) . "'
			AND cattype = '" . $ilance->db->escape_string($ilance->GPC['mode']) . "'
			AND visible = '1'
			$rssquery
			$newsquery
		ORDER BY sort ASC, title_" . $_SESSION['ilancedata']['user']['slng'] . " ASC
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($getcats) > 0 AND $cid > 0)
	{
		$newcontent .= '<select disabled="disabled" id="catbox_' . $boxnum . '_list" name="catbox_' . $boxnum . '" onchange="xajax_print_next_category(this[this.selectedIndex].value, \'catbox_' . $boxnum . '\', \'' . $cidfield . '\', \'' . $showcontinue . '\', \'' . $showthumb . '\', \'' . $showcidbox . '\', \'' . $showyouselectedstring . '\', \'' . $readonly . '\', \'' . $showcheckmarkafterstring . '\', \'' . $categoryfinderjs . '\', \'' . $id . '\', \'' . $cmd . '\', \'' . $rss . '\', \'' . $news . '\', \'' . $showaddanother . '\', \'' . $categoryfinderapi . '\')" size="8" style="position: relative; height:225px; font-family: verdana">';
		while ($res = $ilance->db->fetch_array($getcats, DB_ASSOC))
		{
			$selected = '';
			if ($cid == $res['cid'])
			{
				$selected = 'selected="selected"';
			}
			$newcontent .= '<option value="' . $res['cid'] . '" ' . $selected . '>' . handle_input_keywords($res['title']) . '' . (is_last_category($res['cid']) ? '' : ' &gt;') . '</option>';
			$selectedindex[$res['cid']] = $index;
			$index++;
		}
		$newcontent .= '</select>';
		$objResponse->addScript("window.fetch_js_object('" . $box . "_list').selectedIndex = fetch_js_object('" . $box . "_list').options[fetch_js_object('" . $box . "_list').value].selectedIndex;");
		$objResponse->addScript('window.setTimeout(function(){fetch_js_object(\'catbox_' . $boxnum . '_list\').disabled = false;}, 400);');
	}
	$objResponse->addAssign('catbox_' . $boxnum, 'innerHTML', $newcontent);
	for ($i = ($boxnum + 1); $i < 16; $i++)
	{
		$objResponse->addAssign('catbox_' . $i, 'innerHTML', '');
		if (is_postable_category($cid) == false)
		{
			$objResponse->addScript("window.top.document.getElementById('categorybutton').innerHTML = '';");
		}                        
	}
	if (is_postable_category($cid) AND is_last_category($cid) == false)
	{
		if ($showthumb)
		{
			$newcontent = '<div style="padding-top:55px; padding-' . $ilconfig['template_textalignment'] . ':15px; font-family: Arial; font-size:13px"><span style="float:' . $ilconfig['template_textalignment'] . '; padding-' . $ilconfig['template_textalignment_alt'] . ':10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'thumbsup.gif" border="0" alt="" /></span>{_youve_selected_a_category_click_continue_button}</div>';
			$ilance->template->templateregistry['phrase'] = $newcontent;
			$newcontent = $ilance->template->parse_template_phrases('phrase');
			$objResponse->addAssign('catbox_' . ($boxnum + 1), 'innerHTML', $newcontent);
		}
		if ($showcheckmarkafterstring)
		{
			$newcontentextra = '&nbsp;&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="" />';
			$objResponse->addAssign('cidstringcb', 'innerHTML', $newcontentextra);
		}
	}
	$objResponse->addScript("fetch_js_object('" . $box . "_list').value = '$cid';");
	if ($ilconfig['template_textalignment'] == 'left')
	{
		$objResponse->addScript("window.scrollTo(2500,0);");
	}
	else
	{
		$objResponse->addScript("window.scrollTo(0,2500);");
	}
	if ($categoryfinderjs)
	{
		$project_questions = '';
		if ($cmd == 'new-item')
		{
			$project_questions = $ilance->auction_questions->construct_auction_questions($cid, $id, 'input', 'product', 3, true);
		}
		else if ($cmd == 'product-management' AND $id > 0)
		{
			$id = isset($ilance->GPC['old_id']) ? $ilance->GPC['old_id'] : $id;
			$project_questions = $ilance->auction_questions->construct_auction_questions($cid, $id, 'update', 'product', 3, true);
		}
		else if ($cmd == 'new-rfp')
		{
			$project_questions = $ilance->auction_questions->construct_auction_questions($cid, $id, 'input', 'service', 3, true);
		}
		else if ($cmd == 'rfp-management' AND $id > 0)
		{
			$project_questions = $ilance->auction_questions->construct_auction_questions($cid, $id, 'update', 'service', 3, true);
		}
		else if ($cmd == 'specifics-api')
		{
			$project_questions = $ilance->auction_questions->construct_auction_questions($cid, $id, 'api', 'product', 1, true);
		}
		if (empty($project_questions))
		{
			$project_questions = '{_no_category_specifics_exist_in_this_category}';	
		}
		$ilance->template->templateregistry['project_questions'] = $project_questions;
		$project_questions = $ilance->template->parse_template_phrases('project_questions');
		$objResponse->addScript("window.top.document.getElementById('categoryfindertext').innerHTML = '" . $ilance->db->escape_string($project_questions) . "';");
	}
	if ($ilance->categories->cattype('', $cid) == 'service')
	{
		$sql = $ilance->db->query("SELECT filter_bidtype, filtered_bidtype,cid FROM " . DB_PREFIX . "projects WHERE project_id = '" . $id . "' LIMIT 1");
		$res = $ilance->db->fetch_array($sql);
		if ($res['cid'] == $cid)
		{
		    $ilance->GPC['filter_bidtype'] = $res['filter_bidtype'];
		    $ilance->GPC['filtered_bidtype'] = $res['filtered_bidtype'];
		}
		$budgetfilter = $ilance->auction_post->print_budget_logic_type($cid, 'service');
		$ilance->template->templateregistry['budgetfilter'] = $budgetfilter;
		$budgetfilter = $ilance->template->parse_template_phrases('budgetfilter');
		$budgetfilter = str_replace('\n', '', $ilance->db->escape_string($budgetfilter));
		
		$bidtypefilter = $ilance->auction_post->print_bid_amount_type($cid, 'service');
		$ilance->template->templateregistry['bidtypefilter'] = $bidtypefilter;
		$bidtypefilter = $ilance->template->parse_template_phrases('bidtypefilter');
		$bidtypefilter = str_replace('\n', '', $ilance->db->escape_string($bidtypefilter));
		
		$objResponse->addScript("window.top.document.getElementById('bidtypefilterbox').innerHTML = '" . $bidtypefilter . "';");
		$objResponse->addScript("window.top.document.getElementById('budgetfilterbox').innerHTML = '" . $budgetfilter . "';");
	}
	if ($showaddanother)
	{
		$ilance->template->templateregistry['phrase'] = "window.top.document.getElementById('showaddanother').innerHTML = '<span class=\"black\"><strong>{_you_can}:</strong></span> <span class=\"green\"><a href=\"javascript:void(0)\" onclick=\"move_from_merge_to(\'selectedcategory\', \'existing" . $cmd . "\')\" style=\"text-decoration:underline\">{_add_another_category_to_your_list}</a></span>';";
		$objResponse->addScript($ilance->template->parse_template_phrases('phrase'));
	}
	return $objResponse->getXML();
}

/**
* Function to print the category via AJAX recursively
*
* @param       integer        category id
* @param       string         category type
* @param       string         short language identifier
* @param       boolean        category id fieldname
* @param       boolean        show contiune button (default true)
* @param       boolean        show thumb icon (default true)
* @param       boolean        show the category id mini box to the far right bottom (default 1 = yes)
* @param       boolean        show the you selected x category string (default 1 = yes)
* @param       boolean        read-mode only (default 0 = false)
* @param       boolean        show the checkmark after the you selected x category (default 1 = yes)
* @param       boolean        show the default category finder javascript logic handler (default 0 = false)
* @param       integer        id
* @param       string         cmd action string
* @param       boolean        do rss (default 0 = false)
* @param       boolean        do news (default 0 = false)
* @param       boolean        show add another category link (default 0 = false) mainly used in preferences > category notifications
* @param       boolean        show our category finder output in API mode (so sellers know how to use it within bulk CSV files) (default 0 = false)
*
* @return      string         Returns HTML formatted string
*/
function fetch_recursive_category_ids_js($cid = '', $cattype = '', $slng = 'eng', $cidfield = 'cid', $showcontinue = 1, $showthumb = 1, $showcidbox = 1, $showyouselectedstring = 1, $readonly = 0, $showcheckmarkafterstring = 1, $categoryfinderjs = false, $id = 0, $cmd = '', $rss = 1, $news = 0, $showaddanother = 0, $categoryfinderapi = 0)
{
	global $ilance, $ilconfig, $phrase, $ilpage;
	$html = '';
	$delay1st = $delay = $ilconfig['globalfilters_categorynextdelayms'];
	$count = 1;
	$result = $ilance->db->query("
		SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "parent.cid, parent.title_$slng AS title
		FROM " . DB_PREFIX . "categories AS child,
		" . DB_PREFIX . "categories AS parent
		WHERE child.lft BETWEEN parent.lft AND parent.rgt
			AND parent.cattype = '" . $ilance->db->escape_string($cattype) . "'
			AND child.cattype = '" . $ilance->db->escape_string($cattype) . "'
			AND child.cid = '" . intval($cid) . "'
		ORDER BY parent.lft ASC
	", 0, null, __FILE__, __LINE__);
	$resultscount = $ilance->db->num_rows($result);
	if ($resultscount > 0)
	{
		while ($results = $ilance->db->fetch_array($result, DB_ASSOC))
		{
			if ($count == 1)
			{
				$html .= 'window.setTimeout(function(){xajax_print_next_category(\'' . $results['cid'] . '\',\'catbox_' . $count . '\',\'' . $cidfield . '\',\'' . $showcontinue . '\',\'' . $showthumb . '\',\'' . $showcidbox . '\',\'' . $showyouselectedstring . '\',\'' . $readonly . '\',\'' . $showcheckmarkafterstring . '\',\'' . $categoryfinderjs . '\',\'' . $id . '\',\'' . $cmd . '\',\'' . $rss . '\',\'' . $news . '\',\'' . $showaddanother . '\',\'' . $categoryfinderapi . '\');},' . $delay1st . ');' . "\n";	
			}
			else
			{
				$delay1st = $delay * $count;
				$html .= 'window.setTimeout(function(){xajax_print_next_category(\'' . $results['cid'] . '\',\'catbox_' . $count . '\',\'' . $cidfield . '\',\'' . $showcontinue . '\',\'' . $showthumb . '\',\'' . $showcidbox . '\',\'' . $showyouselectedstring . '\',\'' . $readonly . '\',\'' . $showcheckmarkafterstring . '\',\'' . $categoryfinderjs . '\',\'' . $id . '\',\'' . $cmd . '\',\'' . $rss . '\',\'' . $news . '\',\'' . $showaddanother . '\',\'' . $categoryfinderapi . '\');},' . $delay1st . ');' . "\n";	
			}
			
			$count++;
		}
		unset($results);
	}
	return $html;
}

/**
* Function to respond as true or false based on the supplied category being the last category (leaf) in the category tree.
*
* @param       integer        category id
*
* @return      string         Returns true or false
*/
function is_last_category($cid = 0)
{
        global $ilance;
        $sql = $ilance->db->query("
                SELECT cid
                FROM " . DB_PREFIX . "categories
                WHERE parentid = '" . intval($cid) . "'
				 AND visible = '1'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                return false;
        }
        return true;
}

/**
* Function to respond as true or false based on the supplied category being the last category (leaf) in the category tree.
*
* @param       integer        category id
*
* @return      string         Returns true or false
*/
function is_postable_category($cid = 0)
{
        global $ilance;
        $sql = $ilance->db->query("
                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "canpost
                FROM " . DB_PREFIX . "categories
                WHERE cid = '" . intval($cid) . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
                return $res['canpost'];
        }
	return false;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>