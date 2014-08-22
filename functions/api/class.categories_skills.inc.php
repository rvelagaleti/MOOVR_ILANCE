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
* Category Skills class to perform the majority of service category skill related functions within ILance.
*
* @package      iLance\Categories\Skills
* @version      4.0.0.8059
* @author       ILance
*/
class categories_skills extends categories
{
	/**
        * Formatted skills category array placeholder
        * @var array
        * @access public
        */
        var $fetchskills = array();
	/**
        * Skills category id user counter
        * @var integer
        * @access public
        */
        var $cidusercount = 0;
	/**
        * Skills category array placeholder
        * @var array
        * @access public
        */
	var $cats = array();
        
        /**
        * Function to fetch the array of the skills category structure.
        *
        * @param       string       short language identifier (default eng)
        * @param       integer      per page limit
        * @param       integer      counter
        * @param       integer      level
        * @param       integer      category id
        * @param       string       category title
        * @param       integer      visible (default true)
        * @param       boolean      do proper sorting (default false)
        *
        * @return      array        Returns category array structure
        */
        function build_array_skills($slng = 'eng', $limit = -1, $counter = 0, $level = 10, $cid = 0, $title = '', $visible = 1, $propersort = false)
        {
                global $ilance;
                $query = $ilance->db->query("
                        SELECT cid, parentid, level, rootcid, title_" . $slng . " AS title, description_" . $slng . " AS description, seourl_" . $slng . " AS seourl, views, keywords, visible, sort
                        FROM " . DB_PREFIX . "skills
			WHERE visible = '" . intval($visible) . "'
			" . ((isset($level) AND $level <= 0) ? '' : "AND level <= '" . intval($level) . "'") . "
                        " . ((isset($title) AND !empty($title)) ? "AND title_$slng LIKE '%" . $ilance->db->escape_string($title) . "%'" : '') . "
                        ORDER BY sort ASC
			" . (($limit == -1 OR empty($limit)) ? '' : " LIMIT " . $counter . ", " . $limit) . "
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($query) > 0)
                {
	                while ($categories = $ilance->db->fetch_array($query, DB_ASSOC))
	                {
	                        $this->fetchskills[$slng]["$categories[cid]"] = array(
	                                'cid' => $categories['cid'],
	                                'parentid' => $categories['parentid'],
	                                'level' => 0,
					'rootcid' => $categories['rootcid'],
	                                'title' => $categories['title'],
	                                'description' => $categories['description'],
					'seourl' => $categories['seourl'],
	                                'views' => $categories['views'],
	                                'keywords' => $categories['keywords'],
	                                'visible' => $categories['visible'],
	                                'sort' => $categories['sort'],
	                                'auctioncount' => 0
	                        );
	                }
	                unset($categories);
                }
                else 
                {
                	$this->fetchskills["$slng"] = array();
                }
                $arr = array();
                foreach ($this->fetchskills["$slng"] AS $cid => $array)
                {
                        $arr[] = $array;
                }
		if ($propersort)
		{
			return $arr;
		}
		$this->get_cats($arr, 0, 1, $counter);
                return $this->cats;
        }
	
	/**
        * Function to process and fetch categories
        *
        * @param       array        category results array
        * @param       integer      parent id
        * @param       integer      category level
        * @param       integer      counter
        *
        * @return      nothing
        */
        function get_cats($result, $parentid = 0, $level = 1, $counter = 0)
        {
                $this->cats = array();
                $this->get_cats_recursive($result, $parentid, $level, $counter);
        }
        
        /**
        * Function to process and fetch categories recusively
        *
        * @param       array        category results array
        * @param       integer      category parent id
        * @param       integer      category level
        * @param       integer      counter (default 0)
        *
        * @return      nothing
        */
        function get_cats_recursive($result, $parentid = 0, $level = 1, $counter = 0)
        {
                global $ilance;
                $ilance->GPC['pp'] = isset($ilance->GPC['pp']) ? intval($ilance->GPC['pp']) : 10;
		$startfrom = (isset($ilance->GPC['page']) AND $ilance->GPC['page'] > 1) ? ($ilance->GPC['pp'] + $counter - $ilance->GPC['pp']) : 0;
		$endat = ($ilance->GPC['pp'] + $counter);
		for ($i = 0; $i < count($result); $i++)
                {
                        if ($result[$i]['parentid'] == $parentid)
                        {
                                $result[$i]['level'] = $level;
                                $this->cats[] = $result[$i];
                                $this->get_cats_recursive($result, $result[$i]['cid'], $level + 1, $i);
                        }
                }
        }
        
        /**
        * Function to determine if a user is skilled in a particular category based on his/her skills selection
        *
        * @param       integer      user id
        * @param       integer      skill category id
        * @param       mixed        custom field for future development
        * @param       mixed        custom field for future development
        *
        * @return      array        Returns true or false if user is skilled in a particular category
        */
        function is_user_skilled($userid = 0, $cid = 0, $customfield1 = '', $customfield2 = '')
        {
                global $ilance, $show, $ilconfig, $phrase, $ilpage, $ilcrumbs;
		$tables = DB_PREFIX . "skills_answers";
		$fields = "aid";
		$ufield = "user_id";
		$cfield = "cid";
		$extra = "";
		
		($apihook = $ilance->api('category_skills_is_user_skilled_start')) ? eval($apihook) : false;
		
                $sql = $ilance->db->query("
                        SELECT $fields
                        FROM $tables
                        WHERE $ufield = '" . intval($userid) . "'
                                AND $cfield = '" . intval($cid) . "'
				$extra
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        return true;
                }
		
		($apihook = $ilance->api('category_skills_is_user_skilled_end')) ? eval($apihook) : false;
                
                return false;
        }
        
        /**
        * Function to print the skills categories with checkboxes with the ability to pre-populate previously selected checkboxes.
        *
        * @param       integer     parentid
        * @param       integer     level
        * @param       boolean     show the experts in skills count?
        * @param       integer     user id to obtain pre-population info from
        * @param       boolean     pre-populate selected checkboxes
        * @param       string      short form language indentifier (default eng)
        * @param       boolean     determine if we should use ajax or not (default false)
        * @param       mixed       custom field for future development
        * @param       mixed       custom field for future development
        * @param       integer     value of child checker
        *
        * @return      string      Returns HTML formatted checkboxes beside each Skill category
        */
        function print_skills_children($parentid = 0, $level, $showcount, $userid, $prepopulate, $slng = 'eng', $doajax = false, $customfield1 = '', $customfield2 = '', $check_value_child)
        {
                global $ilance, $ilconfig, $phrase; $headinclude;
                $html = '';
                $h = array();
                $count = 0;
                $result = $ilance->db->query("
                        SELECT parentid, cid, title_$slng
                        FROM " . DB_PREFIX . "skills
                        WHERE parentid = '" . intval($parentid) . "'
                            AND visible = '1'
                        GROUP BY cid
                        ORDER BY sort ASC
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($result) > 0)
                {
                        while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
                        {
                                if ($doajax)
                                {
                                        $js = 'onclick="add_skill(\'' . $row['cid'] . '\', \'' . addslashes(stripslashes($row['title_' . $slng])) . '\')"';
                                }
                                $h[$count]['html'] = '';
                                $h[$count]['html'] .= '<div style="padding-top:4px; padding-left:4px"><label for="cid_' . $row['cid'] . '"><input type="checkbox" id="cid_' . $row['cid'] . '" name="sid[' . $row['cid'] . ']" value="true" ' . (($doajax) ? ($js) : '') . ' ' . ((!empty($_SESSION['ilancedata']['user']['userid']) AND $this->is_user_skilled($_SESSION['ilancedata']['user']['userid'], $row['cid'], $customfield1, $customfield2) AND $prepopulate) ? 'checked="checked"' : ((isset($check_value_child[$row['cid']]) AND ($check_value_child[$row['cid']] == 'true')) ? 'checked="checked"' : '')) . ' /> ' . ((!empty($_SESSION['ilancedata']['user']['userid']) AND $this->is_user_skilled($_SESSION['ilancedata']['user']['userid'], $row['cid'], $customfield1, $customfield2) AND $prepopulate) ? '<strong>' . stripslashes($row['title_' . $slng]) . '</strong>' : stripslashes($row['title_' . $slng])) . '</label>' . ((isset($showcount) AND $showcount) ? ' <span class="smaller gray">(' . $this->fetch_skills_category_count($row['cid']) . ')</span>' : '') . '</div>';
                                $count++;
                        }
                }
                $bit['visible'] = $bit['hidden'] = '';
                $hidden = '<div style="padding-left:4px; padding-bottom:6px; padding-top:5px" class="blue"><a href="javascript:void(0)" onclick="toggle_more(\'showmoreskills_' . $parentid . '\', \'moretext_' . $parentid . '\', \'' . '{_more}' . '\', \'' . '{_less}' . '\', \'showmoreicon_' . $parentid . '\')"><span id="moretext_' . $parentid . '" style="font-weight:bold; text-decoration:none">' . (!empty($ilcollapse["showmoreskills_$parentid"]) ? '{_less}' : '{_more}') . '</span></a> <img id="showmoreicon_' . $parentid . '" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/' . (!empty($ilcollapse["showmoreskills_$parentid"]) ? 'arrowup2.gif' : 'arrowdown2.gif') . '" border="0" alt="" /></div>';
                if (!empty($h) AND is_array($h))
                {
                        $c = 0;
                        foreach ($h AS $key => $array)
                        {
                                $c++;
                                if ($c <= $ilconfig['globalauctionsettings_catcutoff'])
                                {
                                        $bit['visible'] .= $h[$key]['html'];
                                }
                                else
                                {
                                        $bit['hidden'] .= $h[$key]['html'];
                                }
                        }
                }
                if ($count <= $ilconfig['globalauctionsettings_catcutoff'])
                {
                        $hidden = '';
                }
                $html = "$bit[visible] <div id=\"showmoreskills_$parentid\" style=\"" . (!empty($ilcollapse["showmoreskills_$parentid"]) ? $ilcollapse["showmoreskills_$parentid"] : 'display: none;') . "\">$bit[hidden]</div>$hidden";
                return $html;
        }
        
        /**
        * Function to fetch skill columns
        *
        * @param       integer      parent category id
        * @param       string       short language identifier (default = eng)
        * @param       boolean      show the skills category count?
        * @param       integer      skill category level
        * @param       boolean      determine if we should pre-populate skill categories
        * @param       integer      columns
        * @param       boolean      determine if we are using ajax for sending sid[]'s to the page outside the iframe
        * @param       mixed        custom field for future development
        * @param       mixed        custom field for future development
        * @param       integer      check new value
        *
        * @return      string       Returns HTML formatted display of skill category columns
        */
	function fetch_skills_columns($parentid, $slng = 'eng', $showcount, $level, $prepopulate, $columns, $doajax = false, $customfield1 = '', $customfield2 = '', $check_value_new)
        {
                global $ilance, $ilconfig, $headinclude;
                $html = '';
                $cols = 0;
                $result = $ilance->db->query("
                        SELECT s.parentid, s.cid, s.rootcid, s.title_$slng, a.user_id
                        FROM " . DB_PREFIX . "skills s
			LEFT JOIN " . DB_PREFIX . "skills_answers a ON a.cid = s.cid
                        WHERE parentid = '" . intval($parentid) . "'
                            AND visible = '1'
                        GROUP BY cid
                        ORDER BY sort ASC
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($result) > 0)
                {
                        while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
                        {
                                if ($cols == 0)
                                {
                                        $html .= '<tr><td colspan="' . $columns . '"></td></tr><tr>';        
                                }
                                $html .= '<td width="25%" valign="top"><div style="padding-top:4px; padding-left:' . $this->fetch_level_padding($level) . 'px"><strong>' . handle_input_keywords(stripslashes($row['title_' . $slng])) . '</strong>' . ((isset($showcount) AND $showcount) ? ' <span class="gray">(' . $this->fetch_skills_category_recursive_count($row['cid']) . ')</span>' : '') . '</div>' . $this->print_skills_children($row['cid'], $level + 1, $showcount, $row['user_id'], $prepopulate, $slng, $doajax, $customfield1, $customfield2, $check_value_new) . '</td>';
                                $cols++;
                                if ($cols == $columns)
                                {
                                        $html .= '</tr>';
                                        $cols = 0;
                                }
                        }
                        if ($cols != $columns && $cols != 0)
                        {
                                $neededtds = $columns - $cols;
                                for ($i = 0; $i < $neededtds; $i++)
                                {
                                        $html .= '<td></td>';
                                }
                                $html .= '</tr>'; 
                        }
                }
                return $html;
        }
	
        /**
	* Function to print the main subcategory columns of a particular category being viewed or selected
	*
	* @param        string          short language code
	* @param        boolean         show category counts? (default yes)
	* @param        boolean         pre-populate skills (default true)
	* @param        integer         number of columns to display (default 4)
	* @param        boolean         do ajax logic for advanced searches? (default false)
	* @param        string          custom field for future development
	* @param        string          custom field for future development
	* @param        string          skill id value
	*/
        function print_skills_columns($slng = 'eng', $showcount = 1, $prepopulate = true, $columns = 4, $doajax = false, $customfield1 = '', $customfield2 = '', $sid_value = '')
        {
                global $ilance, $phrase, $ilconfig, $ilpage, $show, $sqlquery, $categoryfinderhtml, $headinclude;
		$html = '<table border="0" cellspacing="6" cellpadding="1" width="100%" dir="' . $ilconfig['template_textdirection'] . '">';
                $html .= $this->fetch_skills_columns(0, $slng, $showcount, 1, $prepopulate, $columns, $doajax, $customfield1, $customfield2, $sid_value);
                $html .= '</table>';
                return $html;
        }
        
	/**
        * Function to fetch the root service category id of a skill category.
        *
        * @param       string       short language identifier (default eng)
        * @param       integer      skill category id
        *
        * @return      integer      Returns the root service category id of a skill category
        */
        function rootcid($slng = 'eng', $cid = 0)
        {
                if (!empty($this->fetchskills["$slng"]["$cid"]))
                {
                        return $this->fetchskills["$slng"]["$cid"]['rootcid'];
                }
                return 0;
        }
	
        /**
        * Function to fetch the parent id of a skill category.
        *
        * @param       string       short language identifier (default eng)
        * @param       integer      category id
        *
        * @return      integer      Returns parentid of a category or 0 otherwise
        */
        function parentid($slng = 'eng', $cid = 0)
        {
                if (!empty($this->fetchskills["$slng"]["$cid"]))
                {
                        return $this->fetchskills["$slng"]["$cid"]['parentid'];
                }
                return 0;
        }
	
	/**
        * Function to fetch the SEO URL of a skill category.
        *
        * @param       string       short language identifier (default eng)
        * @param       integer      skill category id
        *
        * @return      integer      Returns the root service category id of a skill category
        */
        function seourl($slng = 'eng', $cid = 0)
        {
                if (!empty($this->fetchskills["$slng"]["$cid"]))
                {
                        return $this->fetchskills["$slng"]["$cid"]['seourl'];
                }
                return '';
        }
        
        /**
        * Function to fetch the meta tag keywords text of a category.
        *
        * @param       string       short language identifier (default eng)
        * @param       integer      category id
        * @param       boolean      insert comma after? (default false)
        * @param       boolean      show input keywords (default false)
        *
        * @return      mixed        Returns category array structure (or All Categories) text otherwise
        */
        function keywords($slng = 'eng', $cid = 0, $commaafter = false, $showinputkeywords = false)
        {
                $keywordbit = $text = $bit = '';
                if (!empty($this->fetchskills["$slng"]["$cid"]) OR !empty($this->fetchskills["$slng"]["$cid"]) AND $this->fetchskills["$slng"]["$cid"] != '0')
                {
                        if (!empty($this->fetchskills["$slng"]["$cid"]['keywords']))
                        {
                                if ($commaafter)
                                {
                                        $bit = ', ';
                                }
                                $text = $this->fetchskills["$slng"]["$cid"]['keywords'] . $bit;
                        }
                }
                if ($showinputkeywords)
                {
                        if (!empty($ilance->GPC['q']))
                        {
                                $keywordbit = htmlspecialchars($ilance->GPC['q']) . ', ';
                        }
                }
                return $keywordbit . $text;
        }
        
        /**
        * Function to fetch all skill children category id numbers recursivly in comma separated values based on a parent category id number.
        * This function is useful because it reads from the cache and does not hit the database.
        *
        * @param       string         category id number (or all)
        *
        * @return      string         Returns category id's in comma separate values (ie: 1,3,4,6)
        */
        function fetch_skills_children_ids($cid = 'all')
        {
                $ids = '';
                foreach ($this->fetchskills[$_SESSION['ilancedata']['user']['slng']] AS $cid2 => $categories)
		{
			if ($categories['parentid'] == $cid)
			{
				if ($categories['cid'] != $cid)
				{
					$ids .= $categories['cid'] . ',' . $this->fetch_skills_children_ids($categories['cid']);
				}    
			}
		}                
                return $ids;
        }
        
        /**
        * Function to fetch all children category id numbers returns in comma separated values.
        *
        * @param       integer        category id number (or all)
        *
        * @return      string         Returns category id's in comma separate values (ie: 1,3,4,6)
        */
        function fetch_skills_children($cid = 0)
        {
                $ids = $this->fetch_skills_children_ids($cid);
                if (empty($ids))
                {
                        $ids = $cid;
                }
                else 
                {
                        $ids = $cid . ',' . mb_substr($ids, 0, -1);
                }
                return $ids;
        }
        
        /**
        * Function to remove skill categories recursively
        *
        * @param       integer      category id
        */
        function remove_skills_category_recursive($cid = 0)
        {
                global $ilance;
                if (empty($cid) OR $cid == 0)
                {
                        return;
                }
		$cids = $this->fetch_skills_children(intval($cid));
                // remove skill answers
                $sql = $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "skills_answers
                        WHERE cid IN (" . $cids . ")
                ", 0, null, __FILE__, __LINE__);
                // remove skill categories
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "skills
                        WHERE cid IN (" . $cids . ")
                ", 0, null, __FILE__, __LINE__);                
        }
        
        /**
        * Function to determine if a skill category can be removed from the datastore
        */
        function can_remove_skill_categories()
        {
                global $ilance;
                $sql = $ilance->db->query("
                        SELECT COUNT(*) AS count
                        FROM " . DB_PREFIX . "skills
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        if ($res['count'] == 1)
                        {
                                return 0;
                        }
                }
                return 1;
        }
        
        /**
        * Function to update and set the proper category level for each category
        */
        function set_levels_skills()
        {
                global $ilance;
                $sql = $ilance->db->query("
                        SELECT cid, parentid
                        FROM " . DB_PREFIX . "skills
                ", 0, null, __FILE__, __LINE__);
                while ($cats = $ilance->db->fetch_array($sql, DB_ASSOC))
                {
                        if ($cats['parentid'] == 0)
                        {    
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "skills
                                        SET level = 1
                                        WHERE cid = '" . $cats['cid'] . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);                    
                        }
                        else
                        {
				$level = 1;
                                $this->set_levels_skills_update($cats['cid'], $cats['parentid'], $level);
                        }
                }
        }
        
        /**
        * Function to set skill level and update the datastore
        *
        * @param       integer      category id
        * @param       integer      parent category id
        * @param       integer      level
        * @param       integer      category id to update
        */
        function set_levels_skills_update($cid, $parentid, $level, $cid_save = '')
        {
                global $ilance;
                if (empty($cid_save))
                {
                        $cid_save = $cid;
                }
                $sql = $ilance->db->query("
                        SELECT cid, parentid
                        FROM " . DB_PREFIX . "skills
                        WHERE cid = '" . intval($parentid) . "'
                ", 0, null, __FILE__, __LINE__);
                $category = $ilance->db->fetch_array($sql, DB_ASSOC);
                if ($category['parentid'] == 0)
                {
                        $level = $level + 1;
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "skills
                                SET level = '" . intval($level) . "'
                                WHERE cid = '" . intval($cid_save) . "'
                        ", 0, null, __FILE__, __LINE__);                    
                }
                else
                {
                        $level = $level + 1;
                        $this->set_levels_skills_update($category['cid'], $category['parentid'], $level, $cid_save);
                }                 
        }
        
        /**
        * Function to fetch the user count currently opted to a particular skill category id
        *
        * @param       integer      skills category id
        */
        function fetch_skills_category_count($cid = 0)
        {
                global $ilance;
                $count = 0;
                $sql = $ilance->db->query("
                        SELECT user_id
                        FROM " . DB_PREFIX . "skills_answers
                        WHERE cid = '" . intval($cid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $count = $ilance->db->num_rows($sql);
                }
                return $count;
        }
        
        /**
        * Function to fetch the user count currently opted to a particular skill category id recursively.
        * This function is only called on the main parent skill categories.
        *
        * @param       integer      skills category id
        * @param       integer      counter
        *
        * @return      integer      Returns the skill count recursively
        */
        function fetch_skills_category_recursive_count($cid = 0, $counter = 0)
        {
                global $ilance, $ilconfig, $phrase;
                $sql = $ilance->db->query("
                        SELECT cid
                        FROM " . DB_PREFIX . "skills
                        WHERE parentid = '" . intval($cid) . "'
                                AND visible = '1'
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                $sql2 = $ilance->db->query("
                                        SELECT user_id
                                        FROM " . DB_PREFIX . "skills_answers
                                        WHERE cid = '" . $res['cid'] . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql2) > 0)
                                {
                                        $c = 0;
                                        while ($r = $ilance->db->fetch_array($sql2, DB_ASSOC))
                                        {
                                                $c++;
                                        }
                                        $counter+= $c;
                                }
                                $this->fetch_skills_category_recursive_count($res['cid'], $counter);
                        }
                }
                return $counter;
        }
	
	/**
        * Function to reset duplicate parentids on skill categories via automation
        *
        * @return       string         Returns a string based on what duplicate skill categories were found.
        */
	function cron_reset_skill_parentid_duplicates()
	{
		global $ilance;
		$html = '';
		$sql = $ilance->db->query("
			SELECT cid
			FROM " . DB_PREFIX . "skills
			WHERE cid = parentid
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$html .= 'Found a duplicate parentid and cid mixup for cid [' . $res['cid'] . '].. resolving now, ';
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "skills
					SET parentid = '0'
					WHERE cid = '" . $res['cid'] . "'
				", 0, null, __FILE__, __LINE__);
			}
		}
		return $html;
	}
	
	function print_root_categories_ul($limit = 75, $breakafter = 15)
	{
		global $ilance, $ilconfig, $ilpage;
		$html = '';
		$slng = $_SESSION['ilancedata']['user']['slng'];
		$sql = $ilance->db->query("
			SELECT cid, title_$slng AS title
			FROM " . DB_PREFIX . "skills
			WHERE visible = '1'
			ORDER BY title_$slng ASC
			LIMIT $limit
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$counter = 1;
			while ($row = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$html .= '<ul>';
				if ($counter % $breakafter == 0)
				{
					$counter = 1;
					$html .= '</ul><ul>';
				}
				$url = HTTP_SERVER . $ilpage['search'] . '?mode=experts&sid[' . $row['cid'] . ']=true';
				$html .= '<li><a href="' . $url . '" title="' . handle_input_keywords($row['title']) . '">' . $row['title'] . '</a></li>';
				$counter++;
				$html .= '</ul>';
			}
		}
		return $html;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>