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

if (!class_exists('categories'))
{
	exit;
}

/**
* Category pulldown class to perform the majority of category pulldown menus and other related parsing functions within ILance.
*
* @package      iLance\Categories\Pulldown
* @version      4.0.0.8059
* @author       ILance
*/
class categories_pulldown extends categories
{
	/**
        * Function to fetch level options for a select menu
        *
        * @param       integer      selected category id
        * @param       array        category result array
        * @param       string       type
        * @param       string       category type
        * @param       boolean      force no category count? (default false)
        * @param       boolean      do javascript? (default false)
        * @param       integer      category id number to skip (if applicable) default 0
        *
        * @return      string       Returns HTML formatted pulldown menu
        */
        function fetch_level_options($selected = '', $result = array(), $type = '', $cattype = '', $forcenocount, $dojs, $hidecid = 0)
        {
                global $ilance, $ilconfig, $phrase, $ilpage;
                $html = '';
                $count = count($result);
                for ($i = 0; $i < $count; $i++)
                {
                        if (isset($result[$i]['visible']) AND $result[$i]['visible'] AND isset($result[$i]['cid']) AND $result[$i]['cid'] != $hidecid)
                        {
                                $catbitcount = ($ilconfig['globalfilters_enablecategorycount'] AND $forcenocount == 0) ? '(' . (int)$result[$i]['auctioncount'] . ')' : '';
                                if (isset($selected) AND $selected == $result[$i]['cid'])
                                {
                                        if ($cattype == 'skills')
                                        {
                                                if ($result[$i]['level'] == 1)
                                                {
                                                        $html .= '<option value="' . $result[$i]['cid'] . '" selected="selected">' . stripslashes($result[$i]['title']) . ' ' . $catbitcount . '</option>';
                                                }        
                                        }
                                        else
                                        {
                                                if ($dojs == 0)
                                                {
                                                        $html .= '<option value="' . $result[$i]['cid'] . '" selected="selected">' . (($result[$i]['level'] > 1) ? str_repeat(' &nbsp; ', $result[$i]['level']) : '' ) . stripslashes($result[$i]['title']) . ' ' . $catbitcount . '</option>';
                                                }
                                                else 
                                                {
                                                        $html .= '<option value="' . HTTP_SERVER . $ilpage['selling'] . '?cmd=profile&amp;cid=' . $result[$i]['cid'] . '#categories" selected="selected">' . (($result[$i]['level'] > 1) ? str_repeat(' &nbsp; ', $result[$i]['level']) : '' ) . stripslashes($result[$i]['title']) . ' ' . $catbitcount . '</option>';
                                                }
                                        }
                                }
                                else 
                                {
                                        if ($cattype == 'skills')
                                        {
                                                if ($result[$i]['level'] == 1)
                                                {
                                                        $html .= '<option value="' . $result[$i]['cid'] . '">' . stripslashes($result[$i]['title']) . ' ' . $catbitcount . '</option>';
                                                }
                                        }
                                        else
                                        {
                                                if ($dojs == 0)
                                                {
                                                        $html .= '<option value="' . $result[$i]['cid'] . '">' . (($result[$i]['level'] > 1) ? str_repeat(' &nbsp; ', $result[$i]['level']) : '' ) . stripslashes($result[$i]['title']) . ' ' . $catbitcount . '</option>';
                                                }
                                                else 
                                                {
                                                        $html .= '<option value="' . HTTP_SERVER . $ilpage['selling'] . '?cmd=profile&amp;cid=' . $result[$i]['cid'] . '#categories">' . (($result[$i]['level'] > 1) ? str_repeat(' &nbsp; ', $result[$i]['level']) : '' ) . stripslashes($result[$i]['title']) . ' ' . $catbitcount . '</option>';						
                                                }
                                        }
                                }
                        }
                }
                unset($result, $count);                                                
                return $html;
        }
        
        /**
        * Function to fetch profile level options for a select menu
        *
        * @param       integer      selected category id
        * @param       array        category result array
        * @param       string       type
        * @param       string       category type
        * @param       boolean      force no category count? (default false)
        * @param       boolean      do javascript? (default false)
        * @param       integer      user id (optional)
        *
        * @return      string       Returns HTML formatted pulldown menu
        */
	function fetch_levelprofile_options($selected = '', $result = array(), $type = '', $cattype = '', $forcenocount, $dojs, $uid)
        {
                global $ilance, $ilconfig, $phrase, $ilpage;
                $html = '';
                $count = count($result);
                for ($i = 0; $i < $count; $i++)
                {
                        if ($result[$i]['visible'])
                        {
                                $questioncount = $ilance->profile->fetch_profile_question_count($result[$i]['cid']);
                                if (isset($selected) AND $selected == $result[$i]['cid'])
                                {
                                        if ($questioncount > 0)
                                        {
                                                if ($dojs == 0)
                                                {
                                                        $html .= '<option value="' . $result[$i]['cid'] . '" selected="selected">' . (($result[$i]['level'] > 1) ? str_repeat(' &nbsp; ', $result[$i]['level']) : '' ) . stripslashes($result[$i]['title']) . ' (' . $questioncount . ' ' . mb_strtolower('{_questions}') . ', ' . $ilance->profile->fetch_profile_answer_count($result[$i]['cid'], $uid).' '.mb_strtolower('{_answered}').', '.$ilance->profile->fetch_profile_verification_count($result[$i]['cid'], $uid).' '.mb_strtolower('{_verified}').')</option>';
                                                }
                                                else 
                                                {
                                                        $html .= '<option value="' . HTTP_SERVER . $ilpage['selling'] . '?cmd=profile-specifics&amp;cid=' . $result[$i]['cid'] . '" selected="selected">' . (($result[$i]['level'] > 1) ? str_repeat(' &nbsp; ', $result[$i]['level']) : '' ) . stripslashes($result[$i]['title']) . ' (' . $questioncount . ' ' . mb_strtolower('{_questions}').', '.$ilance->profile->fetch_profile_answer_count($result[$i]['cid'], $uid).' '.mb_strtolower('{_answered}').', '.$ilance->profile->fetch_profile_verification_count($result[$i]['cid'], $uid).' '.mb_strtolower('{_verified}').')</option>';
                                                }
                                        }
                                }
                                else 
                                {
                                        if ($questioncount > 0)
                                        {
                                                if ($dojs == 0)
                                                {
                                                        $html .= '<option value="' . $result[$i]['cid'] . '">' . (($result[$i]['level'] > 1) ? str_repeat(' &nbsp; ', $result[$i]['level']) : '' ) . stripslashes($result[$i]['title']) . ' (' . $questioncount . ' ' . mb_strtolower('{_questions}').', '.$ilance->profile->fetch_profile_answer_count($result[$i]['cid'], $uid).' '.mb_strtolower('{_answered}').', '.$ilance->profile->fetch_profile_verification_count($result[$i]['cid'], $uid).' '.mb_strtolower('{_verified}').')</option>';
                                                }
                                                else 
                                                {
                                                        $html .= '<option value="' . HTTP_SERVER . $ilpage['selling'] . '?cmd=profile-specifics&amp;cid=' . $result[$i]['cid'] . '">' . (($result[$i]['level'] > 1) ? str_repeat(' &nbsp; ', $result[$i]['level']) : '' ) . stripslashes($result[$i]['title']) . ' (' . $questioncount . ' ' . mb_strtolower('{_questions}').', '.$ilance->profile->fetch_profile_answer_count($result[$i]['cid'], $uid).' '.mb_strtolower('{_answered}').', '.$ilance->profile->fetch_profile_verification_count($result[$i]['cid'], $uid).' '.mb_strtolower('{_verified}').')</option>';						
                                                }
                                        }
                                }
                        }
                }
                return $html;
        }
        
        /**
        * Function for fetching the recursive category nodes for a single category pulldown menu.
        *
        * This function is a call-back to the [print_root_category_pulldown()] function.
        *
        * @param       string       currently selected menu option
        * @param       string       category type (service or product)
        * @param       string       language to parse output within (default eng)
        *
        * @return      string       HTML representation of the single pulldown menu <option>'s
        */
        function fetch_recursive_category_options($cid = '', $cattype = '', $slng = 'eng')
        {
                global $ilance, $ilconfig, $phrase, $ilpage;
		$html = '';
		// #### fetch our nested breadcrumb bit for this category ######
                $result = $ilance->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "parent.cid, parent.title_$slng AS title
                        FROM " . DB_PREFIX . "categories AS child,
                        " . DB_PREFIX . "categories AS parent
                        WHERE child.lft BETWEEN parent.lft AND parent.rgt
                                AND parent.cattype = '" . $ilance->db->escape_string($cattype) . "'
                                AND child.cattype = '" . $ilance->db->escape_string($cattype) . "'
                                AND child.cid = '" . intval($cid) . "'
                        ORDER BY parent.lft DESC
                ");
                $resultscount = $ilance->db->num_rows($result);
                if ($resultscount > 0)
                {
                        while ($results = $ilance->db->fetch_array($result, DB_ASSOC))
                        {
				if ($results['cid'] == $cid)
				{
					$html .= '<option value="' . $results['cid'] . '" selected="selected">' . $results['title'] . '</option>';
				}
				else
				{
					$html .= '<option value="' . $results['cid'] . '">&#9492; ' . $results['title'] . '</option>';	
				}
			}
			unset($results);
		}
		return $html;
        }
	
        /**
        * Function for printing a single category pulldown menu only presenting main categories along with a recursive selected category node feature.  This function
        * will provide marketplaces with huge category lists a much faster display and response vs displaying all nodes for all categories.
        *
        * This function requires a call back function [fetch_recursive_category_options()] to fetch and display the selected recursive nodes.
        *
        * @param       string       currently selected menu option
        * @param       string       category type (service or product)
        * @param       string       fieldname of selection output box
        * @param       string       language to parse output within (default eng)
        * @param       array        category array cache to construct the pulldown menu (accepts build_array() format only)
        * @param       boolean      show "All categories" option (blank value)
        * @param       boolean      can we display an option such as -1 for the category to allow "assign to all categories" option?
        * @param       boolean      show option in pulldown that displays "None" (default false)
        *
        * @return      string       HTML representation of the single pulldown or multi-selection menu
        */
        function print_root_category_pulldown($selected = '', $cattype = '', $fieldname = 'cid', $slng = 'eng', $categorycache = array(), $showselectall = true, $showcanassigntoall = false, $shownone = false, $id = 'cid')
        {
                global $ilance, $ilconfig, $phrase, $ilpage;
		// #### use existing cache for building level 1 nodes ##########
		if (is_array($categorycache) AND count($categorycache) > 0)
		{
			$this->cats = $categorycache;
		}
		// #### build level 1 category nodes ###################
		else
		{
			$this->cats = $this->build_array($cattype, $_SESSION['ilancedata']['user']['slng'], 0, true, '', '', 0, -1, 1);
		}
                $html = '<select name="' . $fieldname . '" id="' . $id . '" class="select">';
		if ($shownone)
		{
			$html .= '<option value="0">{_none}</option>';
		}
		if ($showcanassigntoall AND defined('LOCATION') AND LOCATION == 'admin')
                {
                        $html .= '<option value="-1" style="background:yellow; color:#000">{_assign_to_all_available_categories}</option>';
			$html .= '<option value="">-----------------------</option>';
                }
                if (isset($selected) AND $selected > 0)
                {
                        $html .= $this->fetch_recursive_category_options($selected, $cattype, $slng);
			if ($showselectall)
			{
				$html .= '<option value="">&#9492; {_all_categories_upper}</option>';
			}
			$html .= '<option value="">-----------------------</option>';
                }
                else
                {
			if ($showselectall)
			{
				$html .= '<option value="">{_all_categories_upper}</option>';
			}
			$html .= '<option value="">-----------------------</option>';
                }
                $count = count($this->cats);
                for ($i = 0; $i < $count; $i++)
                {
                        if ($this->cats[$i]['visible'] AND $this->cats[$i]['level'] == 1)
                        {
                                $html .= '<option value="' . $this->cats[$i]['cid'] . '">' . handle_input_keywords($this->cats[$i]['title']) . '</option>';
                        }
                }
                $html .= '</select>';
                return $html;
        }
        
        /**
        * Function for printing a category pulldown or selection box.  This function now uses a suitable method to call the category cache without reloading the db.
        *
        * @param       string       parent category id
        * @param       string       category type (service or product)
        * @param       string       type of element (multi-select, single pulldown, etc)
        * @param       string       fieldname of selection output box
        * @param       boolean      show "Please select" option (blank value)
        * @param       string       language to parse output within
        * @param       boolean      show option groups and labels
        * @param       string       enable prepopulate mode? (used in areas like newsletter for retaining selected values)
        * @param       integer      mode switch for various category situations (0 = all, 1 = portfolio, 2 = xml, 3 = newsletter, 4 = skills)
        * @param       boolean      show all categories argument (yes/no)
        * @param       boolean      enable javascript for certain pulldown options (yes/no)
        * @param       string       width of the outputted selection box
        * @param       integer      custom user id to supply this function for various purposes
        * @param       boolean      do we want to force-hide the total auction count in category display even if enabled?
        * @param       boolean      is expert pulldown menu (yes/no)
        * @param       boolean      can we display an option such as -1 for the category to allow "assign to all categories" option?
        * @param       boolean      show best matching text (vs "All Categories") (default true)
        * @param       array        category array cache to construct the pulldown menu (accepts build_array() format only)
        * @param       boolean      determine if we need to use onclick when a category is selected to show content within a innerHTML div (default false) (this argument is valid only when $type == levelmultisearch
        * @param       integer      the category id being selected (if applicable) if this value exists we'll prevent this category pulldown option from showing
        * 
        * @return      string       HTML representation of the pulldown or multi-selection menu
        */
        function print_cat_pulldown($selected = '', $cattype = 'service', $type = '', $fieldname = '', $showpleaseselectoption = 0, $slng = 'eng', $nooptgroups = 0, $prepopulate = 0, $mode = 0, $showallcats = 1, $dojs = 0, $width = '540px', $uid = 0, $forcenocount = 0, $expertspulldown = 0, $canassigntoall = false, $showbestmatching = false, $categorycache = array(), $onclickajax = false, $selectedcid = 0)
        {
                global $ilance, $phrase, $ilconfig, $ilpage, $headinclude, $show;
                $this->cats = $categorycache;
                $userid = !empty($_SESSION['ilancedata']['user']['userid']) ? intval($_SESSION['ilancedata']['user']['userid']) : 0;            
                $mycats = array();
                $counter = 0;
                if ($mode == 0)
                {
                        $result = array();
                        $results = $categorycache;
                        for ($i = 0; $i < count($results); $i++)
                        {
                                $counter = $this->cats[$i]['auctioncount'];
                                if ($expertspulldown)
                                {
                                        $count = $ilance->db->query("
                                                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "user_id
                                                FROM " . DB_PREFIX . "profile_categories
                                                WHERE FIND_IN_SET(" . intval($this->cats[$i]['cid']) . ", cid)
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($count) > 0)
                                        {
                                                while ($exclude = $ilance->db->fetch_array($count, DB_ASSOC))
                                                {
                                                        // for each user opt'ed, check if they can be listed in search results..
                                                        if ($ilance->permissions->check_access($exclude['user_id'], 'searchresults') == 'yes' AND $ilance->profile->display_profile($exclude['user_id']))
                                                        {
                                                                $counter++;
                                                        }                                                        
                                                }
                                        }   
                                }
                                $result[] = array (
                                        'cid' => $this->cats[$i]['cid'],
                                        'title' => $this->cats[$i]['title'],
                                        'parentid' => $this->cats[$i]['parentid'],
                                        'canpost' => $this->cats[$i]['canpost'],
                                        'auctioncount' => $counter,
                                        'level' => $this->cats[$i]['level'],
                                        'visible' => $this->cats[$i]['visible']
                                );
                        }                        
                }
                else
                {
                        $result = $categorycache;
                }
                $html = '<select name="' . $fieldname . '" class="select-250">';                
                if ($dojs)
                {
                        // viewing pulldown menu from the selling profile menu
                        $html = '<select name="' . $fieldname . '" onchange="openURL();" class="input">';
                        $headinclude .= "<script type=\"text/javascript\">
<!--
function openURL()
{
    selInd = document.sellingprofile.$fieldname.selectedIndex;
    if (selInd > 0)
    {
	goURL = document.sellingprofile.$fieldname.options[selInd].value;
	top.location.href = goURL;
    }
}
//-->
</script>";                        
                }
                if ($type == 'levelmulti' OR $type == 'levelmultisearch')
                {
                        $onclickjs = $divid = '';
                        if (isset($onclickajax) AND $onclickajax)
                        {
                                $onclickjs = 'onclick="return print_profile_filters();"';
                                $divid = 'id="cid_list"';
                        }
                        $html = '<select ' . $divid . ' name="' . $fieldname . (($type == 'levelmulti') ? '[]" multiple' : '"') . ' size="10" style="width:' . $width . '" class="input" ' . $onclickjs . '>';	
                }
                if ($showpleaseselectoption)
                {
                        if ($showbestmatching)
                        {
                                $phr = '{_best_matching}';
                        }
                        else
                        {
                                if ($cattype == 'skills')
                                {
                                        $phr = '{_none}';       
                                }
                                else
                                {
                                        $phr = '-';
                                }
                        }
                        $html .= '<option value="">' . $phr . '</option>';
                }
                if ($canassigntoall AND defined('LOCATION') AND LOCATION == 'admin')
                {
                        // admin control panel options
                        $html .= '<optgroup label="{_all_categories}">';
                        $html .= '<option value="-1" style="background:#ebebeb">{_assign_to_all_available_categories}</option>';
                        $html .= '</optgroup>';
                }
                // #### SINGLE LEVEL PULLDOWN MENU #############################
                if ($type == 'level' OR $type == 'levelprofile')
                {
                        switch ($type)
                        {
                                // #### GENERAL LEVEL PULLDOWN #################
                                case 'level':
                                {
                                        $html .= $this->fetch_level_options($selected, $result, $type, $cattype, $forcenocount, $dojs, $selectedcid);                                        
                                        break;        
                                }
                                // #### PROFILE CATEGORY PULLDOWN MENU #########
                                case 'levelprofile':
                                {
                                        $html .= $this->fetch_levelprofile_options($selected, $result, $type, $cattype, $forcenocount, $dojs, $uid);
                                        break;        
                                }                                                        
                        }
                }
                // #### MULTIPLE SELECT CATEGORY ###############################
                else if ($type == 'levelmulti' OR $type == 'levelmultisearch')
                {
                        if (!empty($prepopulate))
                        {
                                // #### PREPOPULATION LOGIC FOR VARIOUS CONDITIONS ### 
                                switch ($prepopulate)
                                {
                                        case 'notifyservicescats':
                                        {
                                                $popsql = $ilance->db->query("
                                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "notifyservicescats
                                                        FROM " . DB_PREFIX . "users
                                                        WHERE user_id = '" . intval($userid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($popsql) > 0)
                                                {
                                                        $cats = $ilance->db->fetch_array($popsql, DB_ASSOC);
                                                        if (!empty($cats['notifyservicescats']))
                                                        {
                                                                $selection = explode(',', $cats['notifyservicescats']);
                                                                foreach ($selection as $catid)
                                                                {
                                                                        if (!empty($catid))
                                                                        {
                                                                                $scats['selected']["$catid"] = '1';
                                                                        }
                                                                }
                                                        }
                                                }
                                                break;        
                                        }                                    
                                        case 'notifyproductscats':
                                        {
                                                $popsql = $ilance->db->query("
                                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "notifyproductscats
                                                        FROM " . DB_PREFIX . "users
                                                        WHERE user_id = '" . intval($userid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($popsql) > 0)
                                                {
                                                        $cats = $ilance->db->fetch_array($popsql, DB_ASSOC);
                                                        if (!empty($cats['notifyproductscats']))
                                                        {
                                                                $selection = explode(',', $cats['notifyproductscats']);
                                                                foreach ($selection as $catid)
                                                                {
                                                                        if (!empty($catid))
                                                                        {
                                                                                $scats['selected']["$catid"] = '1';
                                                                        }
                                                                }
                                                        }
                                                }
                                                break;        
                                        }                                    
                                        case 'sellingprofile':
                                        {
                                                $popsql = $ilance->db->query("
                                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "cid
                                                        FROM " . DB_PREFIX . "profile_categories
                                                        WHERE user_id = '" . intval($userid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($popsql) > 0)
                                                {
                                                        while ($cats = $ilance->db->fetch_array($popsql, DB_ASSOC))
                                                        {
                                                                if (!empty($cats['cid']) AND $cats['cid'] > 0)
                                                                {
                                                                        $scats['selected'][$cats['cid']] = '1';	
                                                                }
                                                        }
                                                }
                                                break;        
                                        }
                                }
                        }
                        if ($showbestmatching)
                        {
                                $html .= '<option value="0">{_best_matching}</option>';
                        }
                        $count = count($result);
                        for ($i = 0; $i < $count; $i++)
                        {
                                if ($result[$i]['visible'])
                                {
                                        if ($result[$i]['canpost'])
                                        {
                                                if (!empty($prepopulate) AND isset($scats['selected'][$result[$i]['cid']]) AND $scats['selected'][$result[$i]['cid']] == 1)
                                                {
                                                        if ($result[$i]['level'] == 1)
                                                        {
                                                                $categoryname = stripslashes($result[$i]['title']);
                                                        }
                                                        else
                                                        {
                                                                $categoryname = str_repeat('&nbsp; &nbsp; ', $result[$i]['level'] - 1) . stripslashes($result[$i]['title']);	
                                                        }
                                                        $html .= '<option value="' . $result[$i]['cid'] . '" selected="selected">' . $categoryname . '</option>';
                                                }
                                                else 
                                                {
                                                        if ($result[$i]['level'] == 1)
                                                        {
                                                                $categoryname = stripslashes($result[$i]['title']);
                                                        }
                                                        else
                                                        {
                                                                $categoryname = str_repeat('&nbsp; &nbsp; ', $result[$i]['level'] - 1) . stripslashes($result[$i]['title']);	
                                                        }
                                                        $html .= '<option value="' . $result[$i]['cid'] . '">' . $categoryname . '</option>';
                                                }
                                        }
                                        else 
                                        {
                                                if ($nooptgroups == 0)
                                                {
                                                        if ($result[$i]['level'] == 1)
                                                        {
                                                                $categoryname = stripslashes($result[$i]['title']);
                                                        }
                                                        else
                                                        {
                                                                $categoryname = str_repeat('&nbsp; &nbsp; ', $result[$i]['level'] - 1) . stripslashes($result[$i]['title']);	
                                                        }
                                                        $html .= '<optgroup label="' . $categoryname . '"></optgroup>'; 
                                                }
                                                else 
                                                {
                                                        if (!empty($prepopulate) AND isset($scats['selected'][$result[$i]['cid']]) AND $scats['selected'][$result[$i]['cid']] == 1)
                                                        {
                                                                if ($result[$i]['level'] == 1)
                                                                {
                                                                        $categoryname = stripslashes($result[$i]['title']);
                                                                }
                                                                else
                                                                {
                                                                        $categoryname = str_repeat('&nbsp; &nbsp; ', $result[$i]['level'] - 1) . stripslashes($result[$i]['title']);	
                                                                }
                                                                $html .= '<option value="' . $result[$i]['cid'] . '" selected="selected">' . $categoryname . '</option>';
                                                        }
                                                        else 
                                                        {
                                                                if ($result[$i]['level'] == 1)
                                                                {
                                                                        $categoryname = stripslashes($result[$i]['title']);
                                                                }
                                                                else
                                                                {
                                                                        $categoryname = str_repeat('&nbsp; &nbsp; ', $result[$i]['level'] - 1) . stripslashes($result[$i]['title']);	
                                                                }
                                                                $html .= '<option value="' . $result[$i]['cid'] . '">' . $categoryname . '</option>';
                                                        }
                                                }
                                        }
                                }
                        }
                }
                $html .= '</select>';
                
                ($apihook = $ilance->api('cat_print_pulldown_end')) ? eval($apihook) : false;
                
                return $html;
        }	
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>