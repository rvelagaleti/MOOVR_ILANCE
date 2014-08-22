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
* AdminCP category system class to perform the majority category functions
*
* @package      iLance\AdminCP\Category
* @version      4.0.0.8059
* @author       ILance
*/
class admincp_category extends admincp
{
	/**
        * Function to remove categories recursively
        *
        * @param       integer      category id
        * @param       string       category type (service or product)
        */
        function remove_category_recursive($cid = 0, $cattype = '')
        {
                global $ilance;
                
                if (empty($cid) OR empty($cattype))
                {
                        return;
                }
		
		($apihook = $ilance->api('admincp_remove_category_recursive_start')) ? eval($apihook) : false;
		
                $cids = $ilance->categories->fetch_children(intval($cid), $cattype);
                if ($cattype == 'service')
                {
                        $table1 = DB_PREFIX . "project_questions";
                        $table2 = DB_PREFIX . "project_answers";
                }
                else 
                {
                        $table1 = DB_PREFIX . "product_questions";
                        $table2 = DB_PREFIX . "product_answers";	
                }
                // #### remove custom questions and answers for this parent and any children
                $sql = $ilance->db->query("
                        SELECT cid
                        FROM " . DB_PREFIX . "categories
                        WHERE cid IN (" . $ilance->db->escape_string($cids) . ")
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                $sql2 = $ilance->db->query("
                                        SELECT questionid
                                        FROM $table1
                                        WHERE cid = '" . $res['cid'] . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql2) > 0)
                                {
                                        $res2 = $ilance->db->fetch_array($sql2, DB_ASSOC);
                                        // #### remove questions for this subcategory
                                        $ilance->db->query("
                                                DELETE FROM $table1
                                                WHERE questionid = '" . $res2['questionid'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        // #### remove answers for this questionid within this subcategory
                                        $ilance->db->query("
                                                DELETE FROM $table2
                                                WHERE questionid = '" . $res2['questionid'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                }
                        }
                }
                // #### remove categories ######################################
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "categories
                        WHERE cid IN (" . $ilance->db->escape_string($cids) . ")
                ", 0, null, __FILE__, __LINE__);
                // #### select all profile groups in this deleting category ####
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "profile_questions
                        SET filtercategory = '0'
                        WHERE filtercategory IN (" . $ilance->db->escape_string($cids) . ")
                ", 0, null, __FILE__, __LINE__);
                $pgroups = $ilance->db->query("
                        SELECT groupid
                        FROM " . DB_PREFIX . "profile_groups
                        WHERE cid IN (" . $ilance->db->escape_string($cids) . ")
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($pgroups) > 0)
                {
                        while ($rgroups = $ilance->db->fetch_array($pgroups, DB_ASSOC))
                        {
                                // #### select all answered profile questions
                                $sqla = $ilance->db->query("
                                        SELECT questionid
                                        FROM " . DB_PREFIX . "profile_questions
                                        WHERE groupid = '" . $rgroups['groupid'] . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sqla) > 0)
                                {
                                        while ($resa = $ilance->db->fetch_array($sqla, DB_ASSOC))
                                        {
                                                // #### remove all profile answered questions from this category
                                                $ilance->db->query("
                                                        DELETE FROM " . DB_PREFIX . "profile_answers
                                                        WHERE questionid = '" . $resa['questionid'] . "'
                                                ", 0, null, __FILE__, __LINE__);
                                        }
                                }
                                // #### remove profile questions for this category group
                                $ilance->db->query("
                                        DELETE FROM " . DB_PREFIX . "profile_questions
                                        WHERE groupid = '" . $rgroups['groupid'] . "'
                                ", 0, null, __FILE__, __LINE__);
                        }
                }
                // #### remove profile groups within this category #############
                // we will not remove groups that have a flag to not remove! (default at least 1 must remain)...
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "profile_groups
                        WHERE cid IN (" . $ilance->db->escape_string($cids) . ")
                            AND canremove = '1'
                            AND cid > 0
                ", 0, null, __FILE__, __LINE__);
                // #### remove profile categories for users (that opt in from their selling profile menu)
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "profile_categories
                        WHERE cid IN (" . $ilance->db->escape_string($cids) . ")
                ", 0, null, __FILE__, __LINE__);
		// #### remove portfolio items #################################
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "portfolio
			WHERE category_id IN (" . $ilance->db->escape_string($cids) . ")
		", 0, null, __FILE__, __LINE__);
		// #### remove portfolio attachments ###########################
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "attachment
			WHERE category_id IN (" . $ilance->db->escape_string($cids) . ")
				AND attachtype = 'portfolio'
		", 0, null, __FILE__, __LINE__);
		// #### remove skills association ##############################
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "skills
			SET rootcid = '0'
			WHERE rootcid IN ($cids)
		", 0, null, __FILE__, __LINE__);
                // #### REMOVE AUCTIONS FROM DELETING CATEGORIES ###############
                // #### fetch auctions within categories #######################
                $sql = $ilance->db->query("
                        SELECT project_details, project_id, project_title
                        FROM " . DB_PREFIX . "projects
                        WHERE cid IN (" . $ilance->db->escape_string($cids) . ")
                        ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
				// remove bids for listing
				$ilance->db->query("
					DELETE FROM " . DB_PREFIX . "project_bids
					WHERE project_id = '" . $res['project_id'] . "'
				", 0, null, __FILE__, __LINE__);
				$ilance->db->query("
					DELETE FROM " . DB_PREFIX . "project_realtimebids
					WHERE project_id = '" . $res['project_id'] . "'
				", 0, null, __FILE__, __LINE__);
				$ilance->db->query("
					DELETE FROM " . DB_PREFIX . "proxybid
					WHERE project_id = '" . $res['project_id'] . "'
				", 0, null, __FILE__, __LINE__);
                                // remove attachments and attachment_folder for project
                                $ilance->db->query("
                                        DELETE FROM " . DB_PREFIX . "attachment
                                        WHERE project_id = '" . $res['project_id'] . "'
                                ", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("
                                        DELETE FROM " . DB_PREFIX . "attachment_folder
                                        WHERE project_id = '" . $res['project_id'] . "'
                                ", 0, null, __FILE__, __LINE__);
                                // remove mediashare for this project
                                $ilance->workspace->remove_mediashare_data($res['project_id']);
                                // remove pmb and pmb_alerts for this project
                                $ilance->db->query("
                                        DELETE FROM " . DB_PREFIX . "pmb
                                        WHERE project_id = '" . $res['project_id'] . "'
                                ", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("
                                        DELETE FROM " . DB_PREFIX . "pmb_alerts
                                        WHERE project_id = '" . $res['project_id'] . "'
                                ", 0, null, __FILE__, __LINE__);
                                // remove watchlist for users watching this project
                                $ilance->db->query("
                                        DELETE FROM " . DB_PREFIX . "watchlist
                                        WHERE watching_project_id = '" . $res['project_id'] . "'
                                ", 0, null, __FILE__, __LINE__);
                                // remove auctions within these categories
                                $ilance->db->query("
                                        DELETE FROM " . DB_PREFIX . "projects
                                        WHERE project_id = '" . $res['project_id'] . "'
                                ", 0, null, __FILE__, __LINE__);
				// handler for addons to remove project_id values within various tables
				($apihook = $ilance->api('admincp_remove_category_recursive_loop_end')) ? eval($apihook) : false;
                        }
                }
		
		($apihook = $ilance->api('admincp_remove_category_recursive_end')) ? eval($apihook) : false;
        }
	
	/**
        * Function to learn if any categories can be removed within ILance
        *
        * It basically makes sure there is always 1 category left in the system.  This double checks that.
        *
        * @return      string        HTML representation of the pulldown menu with auction type values
        */
        function can_remove_categories()
        {
                global $ilance;
                $sql = $ilance->db->query("
                        SELECT COUNT(*) AS count
                        FROM " . DB_PREFIX . "categories
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        if ($res['count'] == 1)
                        {
                                return false;
                        }
                }
                return true;
        }
	
	/**
        * Function to fetch the count (integer) of category listings questions in a specific category
        *
        * @param       integer      category id
        * @param       string       category type (service/product)
        *
        * @return      string       Returns listing questions count
        */
        function fetch_category_listing_question_count($cid = 0, $cattype = '')
        {
                global $ilance;
                $count = 0;
		$pid = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "parentid");
                $var = $ilance->categories->fetch_children_ids($cid, $cattype);
		$var2 = $ilance->categories->fetch_parent_ids($cid).','.$cid;
                $extracids = "AND (FIND_IN_SET(cid, '$var2') OR cid = '-1')";
                unset($explode, $var);
                if ($cattype == 'service')
                {
			$sql = $ilance->db->query("
                                SELECT recursive, cid
                                FROM " . DB_PREFIX . "project_questions
                                WHERE visible = '1'
                                $extracids
                        ", 0, null, __FILE__, __LINE__);
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				if (($res['recursive'] == 1 AND $res['cid'] != $cid) OR $res['cid'] == $cid)
				{                        
					$count++;						
				}
			}
                }
                else if ($cattype == 'product')
                {
                        $sql = $ilance->db->query("
                                SELECT recursive, cid
                                FROM " . DB_PREFIX . "product_questions
                                WHERE visible = '1'
                                $extracids
                        ", 0, null, __FILE__, __LINE__);
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				if (($res['recursive'] == 1 AND $res['cid'] != $cid) OR $res['cid'] == $cid)
				{                        
					$count++;						
				}
			}
                }
                return $count;
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>