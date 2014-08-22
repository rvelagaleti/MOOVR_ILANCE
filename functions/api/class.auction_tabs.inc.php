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
* Auction tabs class to perform the majority of printing and displaying of auction tabs within the MyCP areas of the front end.
*
* @package      iLance\Auction\Tabs
* @version      4.0.0.8059
* @author       ILance
*/
class auction_tabs extends auction
{
        /**
        * Function to print sql code based on specific auction tabs being called to this function ultimately
        * saving many lines of code within the main php script files.
        *
        * @param       string       tab to process
        * @param       string       type of tab to process (actual count or sql string)
        * @param       integer      user id
        * @param       string       extra sql query (for listing period control)
        *
        * @return      string       count result of sql or sql string itself
        */
        function product_auction_tab_sql($tab, $countorstring, $userid, $extra = '', $keyw = '')
        {
		global $ilance, $ilconfig;
                if ($countorstring == 'count')
                {
                        if ($tab == 'drafts')
                        {
                                $sqlcount = '0';
                                $exequery = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "project_id
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.project_state = 'product'
                                                $extra
                                                AND p.visible = '1'
                                                AND p.status = 'draft'
                                                AND p.user_id = '" . intval($userid) . "'
                                                AND p.project_title LIKE '%" . $ilance->db->escape_string($keyw) . "%'
                                ", 0, null, __FILE__, __LINE__);
				$sqlcount = $ilance->db->num_rows($exequery);
                        }
                        else if ($tab == 'delisted')
                        {
                                $sqlcount = '0';
                                $exequery = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "project_id
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.project_state = 'product'
                                                $extra
                                                AND p.visible = '1'
                                                AND p.status = 'delisted'
                                                AND p.user_id = '" . intval($userid) . "' 
                                                AND p.project_title LIKE '%" . $ilance->db->escape_string($keyw) . "%'
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = $ilance->db->num_rows($exequery);
                        }
                        else if ($tab == 'archived')
                        {
                                $sqlcount = '0';
                                $exequery = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "project_id
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.project_state = 'product'
                                                $extra
                                                AND p.visible = '1'
                                                AND p.status = 'archived'
                                                AND p.user_id = '" . intval($userid) . "' 
                                                AND p.project_title LIKE '%" . $ilance->db->escape_string($keyw) . "%'
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = $ilance->db->num_rows($exequery);
                        }
                        else if ($tab == 'expired')
                        {
                                $sqlcount = '0';
                                $exequery = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "project_id
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.project_state = 'product'
                                                $extra
                                                AND p.visible = '1'
                                                AND p.status = 'expired'
                                                AND p.user_id = '" . intval($userid) . "' 
                                                AND p.project_title LIKE '%" . $ilance->db->escape_string($keyw) . "%'
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = $ilance->db->num_rows($exequery);
                        }
                        else if ($tab == 'pending')
                        {
                                $sqlcount = '0';
                                $exequery = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "project_id
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.project_state = 'product'
                                                $extra
                                                AND p.user_id = '" . intval($userid) . "'
                                                " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.visible = '0' OR p.status = 'frozen' OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '0') OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '0'))" : "AND p.visible = '0'") . "
                                                AND p.project_title LIKE '%" . $ilance->db->escape_string($keyw) . "%'
                                                AND p.status != 'delisted'
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = $ilance->db->num_rows($exequery);
                        }
                        else if ($tab == 'active')
                        {
                                $sqlcount = '0';
                                $exequery = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "project_id
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.project_state = 'product'
                                                $extra
                                                AND p.status != 'archived'
                                                AND p.status != 'delisted'
                                                AND p.status != 'expired'
                                                AND p.status != 'draft'
                                                AND p.user_id = '" . intval($userid) . "'
                                                AND p.visible = '1' 
                                                " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND p.status != 'frozen' AND ((p.insertionfee > 0 AND p.isifpaid = '1') OR (p.ifinvoiceid = '0')) AND ((p.enhancementfee > 0 AND p.isenhancementfeepaid = '1') OR (p.enhancementfeeinvoiceid = '0'))" : "") . "
                                                AND p.project_title LIKE '%" . $ilance->db->escape_string($keyw) . "%'
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = $ilance->db->num_rows($exequery);
                        }
                        else if ($tab == 'sold')
                        {
                                $sqlcount = '0';
                                $exequery = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "project_id
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.project_state = 'product'
                                                $extra
                                                AND (p.status = 'expired' OR p.status = 'finished' OR p.status = 'open')
                                                AND p.user_id = '" . intval($userid) . "'
                                                AND (p.haswinner = '1' OR p.hasbuynowwinner = '1')
                                                AND p.visible = '1' 
                                                " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND ((p.insertionfee > 0 AND p.isifpaid = '1') OR (p.ifinvoiceid = '0')) AND ((p.enhancementfee > 0 AND p.isenhancementfeepaid = '1') OR (p.enhancementfeeinvoiceid = '0'))" : "") . " 
                                                AND p.project_title LIKE '%" . $ilance->db->escape_string($keyw) . "%'
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = $ilance->db->num_rows($exequery);
                        }
                        else if ($tab == 'productescrow')
                        {
                                $extra = str_replace('date_added', 'p.date_added', $extra);
                                
                                $sqlcount = '0';
                                $exequery = $ilance->db->query("
                                    SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.project_id
                                    FROM " . DB_PREFIX . "projects AS p,
                                    " . DB_PREFIX . "users AS u,
                                    " . DB_PREFIX . "projects_escrow AS e,
                                    " . DB_PREFIX . "project_bids AS b,
                                    " . DB_PREFIX . "invoices AS i
                                    WHERE p.user_id = '" . intval($userid) . "'
                                                $extra
                                                AND u.user_id = '" . intval($userid) . "'
                                                AND e.project_user_id = '" . intval($userid) . "'
                                                AND e.status != 'cancelled'
                                                AND e.bid_id = b.bid_id
                                                AND e.user_id = b.user_id
                                                AND e.project_id = p.project_id
                                                AND e.invoiceid = i.invoiceid
                                                AND i.invoicetype = 'escrow'
                                                AND i.projectid = e.project_id
                                         		AND p.project_state = 'product' 
                                         		AND p.project_title LIKE '%" . $ilance->db->escape_string($keyw) . "%'
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = $ilance->db->num_rows($exequery);
                        }
                        
                        return $sqlcount;
                }
                else
                {
                        if ($tab == 'drafts')
                        {
                                $query = "
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "*, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.project_state = 'product'
                                                $extra
                                                AND p.visible = '1'
                                                AND p.status = 'draft'
                                                AND p.user_id = '" . intval($userid) . "' 
                                                AND p.project_title LIKE '%" . $ilance->db->escape_string($keyw) . "%'
                                ";
                        }
                        else if ($tab == 'delisted')
                        {
                                $query = "
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "*, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
                                        FROM " . DB_PREFIX . "projects as p
                                        WHERE p.project_state = 'product'
                                                $extra
                                                AND p.visible = '1'
                                                AND p.status = 'delisted'
                                                AND p.user_id = '" . intval($userid) . "' 
                                                AND p.project_title LIKE '%" . $ilance->db->escape_string($keyw) . "%'
                                ";
                        }
                        else if ($tab == 'archived')
                        {
                                $query = "
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "*, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.project_state = 'product'
                                                $extra
                                                AND p.visible = '1'
                                                AND p.status = 'archived'
                                                AND p.user_id = '" . intval($userid) . "' 
                                                AND p.project_title LIKE '%" . $ilance->db->escape_string($keyw) . "%'
                                ";
                        }
                        else if ($tab == 'expired')
                        {
                                $query = "
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "*, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE project_state = 'product'
                                                $extra
                                                AND p.visible = '1'
                                                AND p.status = 'expired'
                                                AND p.user_id = '" . intval($userid) . "' 
                                                AND p.project_title LIKE '%" . $ilance->db->escape_string($keyw) . "%'
                                ";
                        }
                        else if ($tab == 'pending')
                        {
                                $query = "
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "*, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.project_state = 'product'
                                                $extra
                                                AND p.user_id = '" . intval($userid) . "'
                                                " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.visible = '0' OR p.status = 'frozen' OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '0') OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '0'))" : "AND p.visible = '0'") . " 
                                                AND p.project_title LIKE '%" . $ilance->db->escape_string($keyw) . "%'
                                                AND p.status != 'delisted'
                                ";  
                        }
                        else if ($tab == 'active')
                        {
                                $query = "
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "*, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.project_state = 'product'
                                                $extra
                                                AND p.status != 'archived'
                                                AND p.status != 'delisted'
                                                AND p.status != 'expired'
                                                AND p.status != 'draft'
                                                AND p.user_id = '" . intval($userid) . "'
                                                AND p.visible = '1'
                                                " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND p.status != 'frozen' AND ((p.insertionfee > 0 AND p.isifpaid = '1') OR (p.ifinvoiceid = '0')) AND ((p.enhancementfee > 0 AND p.isenhancementfeepaid = '1') OR (p.enhancementfeeinvoiceid = '0'))" : "") . " 
                                                AND p.project_title LIKE '%" . $ilance->db->escape_string($keyw) . "%'
                                ";
                        }
                        else if ($tab == 'sold')
                        {
                                $query = "
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "*, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.project_state = 'product'
                                                $extra
                                                AND (p.status = 'expired' OR p.status = 'finished' OR p.status = 'open')
                                                AND p.user_id = '" . intval($userid) . "'
                                                AND (p.haswinner = '1' OR p.hasbuynowwinner = '1')
                                                AND p.visible = '1'
                                                " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND ((p.insertionfee > 0 AND p.isifpaid = '1') OR (p.ifinvoiceid = '0')) AND ((p.enhancementfee > 0 AND p.isenhancementfeepaid = '1') OR (p.enhancementfeeinvoiceid = '0'))" : "") . " 
                                                AND p.project_title LIKE '%" . $ilance->db->escape_string($keyw) . "%'
                                ";
                        }
                        
                        return $query;
                }
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>