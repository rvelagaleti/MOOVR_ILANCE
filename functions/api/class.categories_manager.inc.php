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
* Category manager class.
*
* @package      iLance\Categories\Manager
* @version      4.0.0.8059
* @author       ILance
*/
class categories_manager extends categories
{
    
    /**
    * Function to print the category jump edit/delete/actions menu from within the AdminCP > Category Manager
    *
    * @param       string         form 1 id
    * @param       string         form 2 id
    * @param       string         category id field
    *
    * @return      string         Returns HTML formatted pulldown jump menu
    */
    function print_category_jump_js($formid1 = 'ilform', $formid2 = 'ilform2', $cidid = 'cid', $page = 1, $page2 = 1)
    {
	global $ilconfig, $ilpage, $phrase;
	$html = "
<script type=\"text/javascript\">";
	if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
	{
	    $html .= "
function category_jump(catinfo, pid, level, lft, rgt)
{
        if (catinfo == 0)
        {
                alert_js('" . '{_please_select_a_category}' . "');
                return;
        }
        else if (typeof(document.$formid1.$cidid) != 'undefined')
        {
                action = document.$formid1.$cidid.options[document.$formid1.$cidid.selectedIndex].value;
        }
        else
        {
                action = eval(\"document.$formid1.$cidid\" + catinfo + \".options[document.$formid1.$cidid\" + catinfo + \".selectedIndex].value\");
        }
        if (action != '')
        {
                switch (action)
                {
                        case 'edit': page = \"" . $ilpage['distribution'] . "?cmd=categories&subcmd=editservicecat&cid=\"; break;
                        case 'questions': page = \"" . $ilpage['distribution'] . "?cmd=categories&subcmd=servicequestions&cid=\"; break;
                        case 'add': page = \"" . $ilpage['distribution'] . "?cmd=categories&subcmd=addservicecat&cid=\"; break;
                        case 'remove': page = \"" . $ilpage['distribution'] . "?cmd=categories&subcmd=removeservicecat&cid=\"; break;
                        case 'importcsv': page = \"" . $ilpage['distribution'] . "?cmd=categories&subcmd=importservicecsv&cid=\"; break;
                }
                document.$formid1.reset();
                switch (action)
                {
                        case 'edit': jumptopage = page + catinfo + \"&pid=\" + pid + \"&level=\" + level + \"&lft=\" + lft + \"&rgt=\" + rgt + \"&page=\" + $page; break;
                        case 'questions': jumptopage = page + catinfo + \"&page=\" + $page + \"\"; break;
                        case 'increments': jumptopage = page + catinfo + \"\"; break;
                        case 'add': jumptopage = page + catinfo + \"&page=\" + $page + \"\"; break;
                        case 'remove': jumptopage = page + catinfo + \"&page=\" + $page + \"\"; break;
                        case 'importcsv': jumptopage = page + catinfo + \"&page=\" + $page + \"\"; break;
                }
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
                alert_js(\"" . '{_invalid_action}' . "\");
        }
}
";
	}

	if ($ilconfig['globalauctionsettings_productauctionsenabled'])
	{
	    $html .= "
function category_jump2(catinfo, pid, level, lft, rgt)
{
        if (catinfo == 0)
        {
                alert_js('" . '{_please_select_a_category}' . "');
                return;
        }
        else if (typeof(document.$formid2.$cidid) != 'undefined')
        {
                action = document.$formid2.$cidid.options[document.$formid2.$cidid.selectedIndex].value;
        }
        else
        {
                action = eval(\"document.$formid2.$cidid\" + catinfo + \".options[document.$formid2.$cidid\" + catinfo + \".selectedIndex].value\");
        }
        if (action != '')
        {
                switch (action)
                {
                        case 'edit': page = \"" . $ilpage['distribution'] . "?cmd=categories&subcmd=editproductcat&cid=\"; break;
                        case 'questions': page = \"" . $ilpage['distribution'] . "?cmd=categories&subcmd=productquestions&cid=\"; break;
                        case 'increments': page = \"" . $ilpage['distribution'] . "?cmd=categories&subcmd=editincrements&cid=\"; break;
                        case 'add': page = \"" . $ilpage['distribution'] . "?cmd=categories&subcmd=addproductcat&cid=\"; break;
                        case 'remove': page = \"" . $ilpage['distribution'] . "?cmd=categories&subcmd=removeproductcat&cid=\"; break;
                        case 'importcsv': page = \"" . $ilpage['distribution'] . "?cmd=categories&subcmd=importproductcsv&cid=\"; break;
                }
                document.$formid2.reset();
                switch (action)
                {
                        case 'edit': jumptopage = page + catinfo + \"&pid=\" + pid + \"&level=\" + level + \"&lft=\" + lft + \"&rgt=\" + rgt + \"&page2=\" + $page2; break;
                        case 'questions': jumptopage = page + catinfo + \"&page2=\" + $page2 + \"\"; break;
                        case 'increments': jumptopage = page + catinfo + \"\"; break;
                        case 'add': jumptopage = page + catinfo + \"&page2=\" + $page2 + \"\"; break;
                        case 'remove': jumptopage = page + catinfo + \"&page2=\" + $page2 + \"\"; break;
                        case 'importcsv': jumptopage = page + catinfo + \"&page2=\" + $page2 + \"\"; break;
                }
                if (action == 'remove')
                {
                        var agree = confirm_js(\"{_please_take_a_moment_to_confirm_your_action_continue}\");
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
                alert_js(\"{_invalid_action}\");
        }
}
";
	}
	$html .= "
//-->
</script>";
	return $html;
    }

    
    /**
    * Function to rebuild a nested category structure in the database
    *
    * @param       integer        parent category id number (default 0)
    * @param       integer        lft node (default 1)
    * @param       string         category type (service or product)
    * @param       string         short language identifier (english = eng) default eng
    * @param       string         category table name (default categories)
    */
    function rebuild_category_tree($parentid = 0, $left = 1, $cattype = 'product', $slng = 'eng', $table = 'categories')
    {
	global $ilance;
	$right = $left + 1;
	$result = $ilance->db->query("
	    SELECT cid
	    FROM " . DB_PREFIX . $table . "
	    WHERE parentid = '" . intval($parentid) . "'
		AND cattype = '" . $ilance->db->escape_string($cattype) . "'
	    ORDER BY sort ASC, title_" . $slng . " ASC
	", 0, null, __FILE__, __LINE__);
	while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
	{
	    $right = $this->rebuild_category_tree($row['cid'], $right, $cattype, $slng);
	}
	$ilance->db->query("
	    UPDATE " . DB_PREFIX . $table . "
	    SET lft = '" . $left . "', rgt = '" . $right . "'
	    WHERE cid = '" . $parentid . "'
	", 0, null, __FILE__, __LINE__);
	return $right + 1;
    }

    /**
    * Function to rebuild the category structure geometry for ultra fast database queries
    *
    * @param       string         category table name (default categories)
    */
    function rebuild_category_geometry($table = 'categories')
    {
	global $ilance;
	$ilance->db->add_field_if_not_exist(DB_PREFIX . $table, 'sets', "LINESTRING NOT NULL", 'AFTER `parentid`', true);
	$ilance->db->query("ALTER TABLE " . DB_PREFIX . $table . " DROP `sets`", 0, null, __FILE__, __LINE__);
	$ilance->db->add_field_if_not_exist(DB_PREFIX . $table, 'sets', "LINESTRING NOT NULL", 'AFTER `parentid`', true);
	$ilance->db->query("UPDATE " . DB_PREFIX . $table . " SET `sets` = LineString(Point(-1, lft), Point(1, rgt))", 0, null, __FILE__, __LINE__);
	$ilance->db->query("CREATE SPATIAL INDEX sx_categories_sets ON " . DB_PREFIX . $table . " (sets)", 0, null, __FILE__, __LINE__);
    }

    /**
    * Function to rebuild the category structure geometry during installation for ultra fast database queries
    *
    * @param       string         category table name (default categories)
    */
    function rebuild_category_geometry_install($table = 'categories')
    {
	global $ilance;
	$ilance->db->add_field_if_not_exist(DB_PREFIX . $table, 'sets', "LINESTRING NOT NULL", 'AFTER `parentid`', true);
	$ilance->db->query("ALTER TABLE " . DB_PREFIX . $table . " DROP `sets`", 0, null, __FILE__, __LINE__);
	$ilance->db->add_field_if_not_exist(DB_PREFIX . $table, 'sets', "LINESTRING NOT NULL", 'AFTER `parentid`', true);
	$ilance->db->query("UPDATE " . DB_PREFIX . $table . " SET `sets` = LineString(Point(-1, lft), Point(1, rgt))", 0, null, __FILE__, __LINE__);
    }

    
    /**
    * Function to set all the levels in proper format for the main category system
    *
    * @param       string         category table name (default categories)
    */
    function set_levels($table = 'categories')
    {
	global $ilance;
	$sql = $ilance->db->query("
	    SELECT cid, parentid, level
	    FROM " . DB_PREFIX . $table . "
	", 0, null, __FILE__, __LINE__);
	while ($cats = $ilance->db->fetch_array($sql, DB_ASSOC))
	{
	    if ($cats['parentid'] == 0)
	    {
		$ilance->db->query("
		    UPDATE " . DB_PREFIX . $table . "
		    SET level = '1'
		    WHERE cid = '" . $cats['cid'] . "'
		", 0, null, __FILE__, __LINE__);
	    }
	    else
	    {
		$level = 1;
		$this->set_levels_update($cats['cid'], $cats['parentid'], $level, '', $table);
	    }
	}
    }

    /**
    * Function to set all levels and to handle the updating
    *
    * @param       integer        category id
    * @param       integer        parent id
    * @param       integer        level
    * @param       integer        category id to save
    * @param       string         category table name (default categories)
    *
    * @return      nothing
    */
    function set_levels_update($cid, $parentid, $level, $cid_save = '', $table = 'categories')
    {
	global $ilance;
	if (empty($cid_save))
	{
	    $cid_save = $cid;
	}
	$sql = $ilance->db->query("
	    SELECT cid, parentid, level
	    FROM " . DB_PREFIX . $table . "
	    WHERE cid = '" . intval($parentid) . "'
	", 0, null, __FILE__, __LINE__);
	$category = $ilance->db->fetch_array($sql, DB_ASSOC);
	if ($category['parentid'] == 0)
	{
	    $level = $level + 1;
	    $ilance->db->query("
		UPDATE " . DB_PREFIX . $table . "
		SET level = '" . intval($level) . "'
		WHERE cid = '" . intval($cid_save) . "'
	    ", 0, null, __FILE__, __LINE__);
	}
	else
	{
	    $level = $level + 1;
	    $this->set_levels_update($category['cid'], $category['parentid'], $level, $cid_save, $table);
	}
    }

}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>
