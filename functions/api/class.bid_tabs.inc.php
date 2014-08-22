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
* Bid Tabs class to perform the majority of bid tab display and output operations within ILance.
*
* @package      iLance\Bid\Tabs
* @version      4.0.0.8059
* @author       ILance
*/
class bid_tabs extends bid
{
        /**
        * Function for printing a specific service bid tab sql query.
        *
        * @param       string       bid tab (drafts, delisted, archived, expired, pending, active, serviceescrow)
        * @param       string       count or string
        *
        * @return     string       MySQL query or MySQL query count
        */
        function fetch_service_bidtab_sql($tab = '', $countorstring = '', $userid = 0, $extra = '')
        {
                global $ilance, $ilconfig, $show;

                $query_field_info = $query_field_data = '';
                
                ($apihook = $ilance->api('fetch_service_bidtab_sql_start')) ? eval($apihook) : false;

                if ($countorstring == 'count')
                {
                        if ($tab == 'drafts')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.*
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.user_id = '" . intval($userid) . "'
                                            $extra
                                            AND p.status = 'draft'
                                            AND p.visible = '1'
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($tab == 'delisted')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.*
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.user_id = '" . intval($userid) . "'
                                            $extra
                                            AND p.project_state = 'service'
                                            AND p.status = 'delisted'
                                            AND p.visible = '1'
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($tab == 'archived')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.*
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.user_id = '" . intval($userid) . "'
                                            $extra
                                            AND p.project_state = 'service'
                                            AND p.status = 'archived'
                                            AND p.visible = '1'
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($tab == 'expired')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.*
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE project_state = 'service'
                                            $extra
                                            AND visible = '1'
                                            AND status = 'expired'
                                            AND user_id = '" . intval($userid) . "'
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($tab == 'awarded')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.*
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE project_state = 'service'
                                            $extra
                                            AND visible = '1'
                                            AND (status = 'wait_approval' OR status = 'approval_accepted' OR status = 'finished')
                                            AND user_id = '" . intval($userid) . "'
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($tab == 'pending')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.*
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.user_id = '" . intval($userid) . "'
                                            $extra
                                            AND p.project_state = 'service'
                                            AND p.status != 'archived'
                                            " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.visible = '0' OR p.status = 'frozen' OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '0') OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '0'))" : "AND p.visible = '0'") . "
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($tab == 'active')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.*
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.user_id = '" . intval($userid) . "'
                                            $extra
                                            AND p.project_state = 'service'
                                            AND p.status = 'open'
                                            AND p.visible = '1' 
                                            " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.enhancementfee = 0 OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '1')) AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($tab == 'serviceescrow')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.project_id, p.project_state, p.user_id as owner_id, p.project_title, p.description, u.username, e.project_user_id, e.user_id, e.escrowamount, e.bidamount, e.date_awarded, e.date_paid, e.status, e.bid_id, e.project_id, e.invoiceid, e.escrow_id, b.bid_id, b.user_id as bidder_id, b.bidamount, b.bidstatus, i.invoiceid, i.projectid, i.buynowid, i.paid, i.invoicetype, i.paiddate
                                        FROM " . DB_PREFIX . "projects AS p,
                                        " . DB_PREFIX . "users AS u,
                                        " . DB_PREFIX . "projects_escrow AS e,
                                        " . DB_PREFIX . "project_realtimebids AS b,
                                        " . DB_PREFIX . "invoices AS i
                                        WHERE p.user_id = '" . intval($userid) . "'
                                            $extra
                                            AND u.user_id = '" . intval($userid) . "'
                                            AND e.project_user_id = '" . intval($userid) . "'
                                            AND e.status != 'cancelled'
                                            AND e.bid_id = b.id
                                            AND e.user_id = b.user_id
                                            AND e.project_id = p.project_id
                                            AND e.invoiceid = i.invoiceid
                                            AND i.invoicetype = 'escrow'
                                            AND p.project_state = 'service'
                                            AND i.projectid = e.project_id
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        
                        return $sqlcount;
                }
                else
                {
                        if ($tab == 'drafts')
                        {
                                $query = "
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.*
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.user_id = '" . intval($userid) . "'
                                            $extra
                                            AND p.status = 'draft'
                                            AND p.visible = '1'
                                ";
                                
                        }
                        else if ($tab == 'delisted')
                        {
                                $query = "
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.*
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.user_id = '" . intval($userid) . "'
                                            $extra
                                            AND p.project_state = 'service'
                                            AND p.status = 'delisted'
                                            AND p.visible = '1'
                                ";
                        }
                        else if ($tab == 'archived')
                        {
                                $query = "
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.*
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.user_id = '" . intval($userid) . "'
                                            $extra
                                            AND p.project_state = 'service'
                                            AND p.status = 'archived'
                                            AND p.visible = '1'
                                ";
                        }
                        else if ($tab == 'expired')
                        {
                                $query = "
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.*
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.project_state = 'service'
                                            $extra
                                            AND p.visible = '1'
                                            AND p.status = 'expired'
                                            AND p.user_id = '" . intval($userid) . "'
                                ";
                        }
                        else if ($tab == 'awarded')
                        {
                                $query = "
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.*
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.project_state = 'service'
                                            $extra
                                            AND p.visible = '1'
                                            AND (p.status = 'wait_approval' OR p.status = 'approval_accepted' OR p.status = 'finished')
                                            AND p.user_id = '" . intval($userid) . "'
                                ";
                        }
                        else if ($tab == 'pending')
                        {
                                $query = "
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.*
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE p.user_id = '" . intval($userid) . "'
                                            $extra
                                            AND p.project_state = 'service'
                                            AND p.status != 'archived'
                                            " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.visible = '0' OR p.status = 'frozen' OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '0') OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '0'))" : "AND p.visible = '0'") . "
                                ";
                        }
                        else if ($tab == 'active')
                        {
                                $query = "
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.*
                                        FROM " . DB_PREFIX . "projects AS p
                                        WHERE user_id = '" . intval($userid) . "'
                                            $extra
                                            AND p.project_state = 'service'
                                            AND p.status = 'open'
                                            AND p.visible = '1' 
                                            " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.enhancementfee = 0 OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '1')) AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
                                ";
                        }
                        
                        return $query;
                }
        }
        
        /**
        * Function for printing a specific product bid tab sql query.
        *
        * @param       string       bid tab
        * @param       string       count or string
        * @param       string       group by statement
        * @param       string       order by statement
        * @param       integer      limit
        * @param       integer      user id
        * @param       string       extra sql
        *
        * @return     string       MySQL Query
        */
        function fetch_product_bidtab_sql($bidtab = '', $countorstring = '', $groupby = '', $orderby = '', $limit = '', $userid = 0, $extra = '')
        {
                global $ilance, $phrase, $show;
                $query_field_info = $query_field_data = $query_field_condition = '';

                ($apihook = $ilance->api('fetch_product_bidtab_sql')) ? eval($apihook) : false;
                
                if ($countorstring == 'count')
                {
                        if ($bidtab == 'retracted')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "b.bid_id
                                        FROM " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id 
                                        $extra
                                            AND b.user_id = '" . intval($userid) . "'
                                            AND p.project_state = 'product'
                                            AND (p.status = 'open' AND b.bidstatus = 'awarded' AND b.bidstate = 'retracted'
                                                OR p.status = 'open' AND b.bidstatus = 'placed' AND b.bidstate = 'retracted'
                                                OR p.status = 'finished' AND b.bidstatus = 'awarded' AND b.bidstate = 'retracted'
                                                OR p.status = 'expired' AND b.bidstatus = 'awarded' AND b.bidstate = 'retracted')
                                        $orderby
                                        $limit
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($bidtab == 'awarded')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "b.bid_id
                                        FROM " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "projects AS p,
                                        " . DB_PREFIX . "projects_shipping AS s
                                        WHERE b.project_id = p.project_id
                                            $extra
                                            AND p.project_id = s.project_id
                                            AND b.bidstate != 'retracted'
                                            AND b.user_id = '" . intval($userid) . "'
                                            AND p.project_state = 'product'
                                            AND b.bidstatus = 'awarded'
                                            AND (p.status = 'open' OR p.status = 'archived' OR p.status = 'finished' OR p.status = 'expired')
                                        $groupby
                                        $orderby
                                        $limit
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($bidtab == 'invited')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.project_id
                                        FROM " . DB_PREFIX . "projects AS p,
                                        " . DB_PREFIX . "project_invitations as i
                                        WHERE i.project_id = p.project_id
                                            $extra
                                            AND i.seller_user_id = '" . intval($userid) . "'
                                            AND p.project_state = 'product'
                                            AND p.status = 'open'
                                            AND i.bid_placed = 'no'
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($bidtab == 'expired')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "b.date_added
                                        FROM " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                            $extra
                                            AND b.bidstate != 'retracted'
                                            AND (b.bidstate = 'expired' OR b.bidstate = '')
                                            AND (b.bidstatus = 'outbid')
                                            AND b.user_id = '" . intval($userid) . "'
                                            AND p.project_state = 'product'
                                            AND (p.status = 'expired' OR p.status = 'finished')
                                            AND p.winner_user_id != '" . intval($userid) . "'
                                        $groupby
                                        $orderby
                                        $limit
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($bidtab == 'active')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "b.bid_id
                                        FROM " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                            $extra
                                            AND b.bidstate != 'retracted'
                                            AND b.user_id = '" . intval($userid) . "'
                                            AND p.project_state = 'product'
                                            AND (b.bidstatus = 'placed'
                                                AND p.status != 'finished'
                                                AND p.status != 'expired'
                                                AND p.status != 'archived'
                                                AND p.status != 'closed'
                                                AND p.status != 'delisted')
                                        $groupby
                                        $orderby
                                        $limit
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($bidtab == 'productescrow')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.project_id
                                        FROM " . DB_PREFIX . "projects AS p,
                                        " . DB_PREFIX . "users AS u,
                                        " . DB_PREFIX . "projects_escrow AS e,
                                        " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "invoices AS i
                                        WHERE e.user_id = '" . intval($userid) . "'
                                            $extra
                                            AND u.user_id = '" . intval($userid) . "'
                                            AND b.bidstate != 'retracted'
                                            AND e.status != 'cancelled'
                                            AND e.bid_id = b.bid_id
                                            AND e.user_id = b.user_id
                                            AND e.project_id = p.project_id
                                            AND e.invoiceid = i.invoiceid
                                            AND i.invoicetype = 'escrow'
                                            AND i.projectid = e.project_id
                                            AND p.project_state = 'product'
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($bidtab == 'buynowproductescrow')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "b.orderid
                                        FROM " . DB_PREFIX . "buynow_orders AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE p.project_id = b.project_id
                                            $extra
                                            $query_field_condition
                                            AND b.buyer_id = '" . intval($userid) . "'
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        return $sqlcount;
                }
                else
                {
                        if ($bidtab == 'retracted')
                        {
                                $query = "
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "b.bid_id, b.user_id AS bidderid, b.bidamount, b.estimate_days, b.date_added, b.date_retracted, b.bidstatus, b.bidstate, b.buyerpaymethod, b.buyershipcost, b.buyershipperid, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.qty, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end)-UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.cid, p.close_date, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.date_starts, p.date_end, p.currentprice, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.filter_escrow, p.reserve_price, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.escrow_id, p.paymethodoptions, p.currencyid, p.description_videourl, p.filter_budget, p.filter_gateway, p.filtered_auctiontype, p.buynow_qty_lot, p.items_in_lot, s.ship_method, s.ship_handlingtime, s.ship_handlingfee$query_field_info
                                        FROM " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "projects AS p,
                                        " . DB_PREFIX . "projects_shipping AS s
                                        WHERE b.project_id = p.project_id
                                            $extra
                                            AND p.project_id = s.project_id
                                            AND b.user_id = '" . intval($userid) . "'
                                            AND p.project_state = 'product'
                                            AND (p.status = 'open' AND b.bidstatus = 'awarded' AND b.bidstate = 'retracted'
                                                OR p.status = 'open' AND b.bidstatus = 'placed' AND b.bidstate = 'retracted'
                                                OR p.status = 'finished' AND b.bidstatus = 'awarded' AND b.bidstate = 'retracted'
                                                OR p.status = 'expired' AND b.bidstatus = 'awarded' AND b.bidstate = 'retracted')
                                        $orderby
                                        $limit
                                ";
                        }
                        else if ($bidtab == 'awarded')
                        {
                                $query = "
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "b.bid_id, b.user_id AS bidderid, b.bidamount, b.date_added, b.date_retracted, b.bidstatus, b.bidstate, b.buyerpaymethod, b.buyershipcost, b.buyershipperid, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.qty, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end)-UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.cid, p.close_date, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.date_starts, p.date_end, p.currentprice, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.filter_escrow, p.reserve_price, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.escrow_id, p.paymethodoptions, p.currencyid, p.description_videourl, p.filter_budget, p.filter_gateway, p.filtered_auctiontype, p.buynow_qty_lot, p.items_in_lot, s.ship_method, s.ship_handlingtime, s.ship_handlingfee$query_field_info
                                        FROM " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "projects AS p,
                                        " . DB_PREFIX . "projects_shipping AS s
                                        WHERE b.project_id = p.project_id
                                            $extra
                                            AND p.project_id = s.project_id
                                            AND b.bidstate != 'retracted'
                                            AND b.user_id = '" . intval($userid) . "'
                                            AND p.project_state = 'product'
                                            AND b.bidstatus = 'awarded'
                                            AND (p.status = 'open' OR p.status = 'archived' OR p.status = 'finished' OR p.status = 'expired')
                                        $groupby
                                        $orderby
                                        $limit
                                ";
                        }
                        else if ($bidtab == 'invited')
                        {
                                $query = "
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.cid, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.date_starts, p.date_end, p.close_date, p.currentprice, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.filter_escrow, p.reserve_price, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.escrow_id, p.paymethodoptions, p.currencyid, p.description_videourl, p.filter_budget, p.filter_gateway, p.filtered_auctiontype, s.ship_method, s.ship_handlingtime, s.ship_handlingfee, p.buynow_qty_lot, p.items_in_lot$query_field_info
                                        FROM " . DB_PREFIX . "projects AS p,
                                        " . DB_PREFIX . "project_invitations AS i,
                                        " . DB_PREFIX . "projects_shipping AS s
                                        WHERE i.project_id = p.project_id
                                            $extra
                                            AND p.project_id = s.project_id
                                            AND i.seller_user_id = '" . intval($userid) . "'
                                            AND p.project_state = 'product'
                                            AND p.status = 'open'
                                            AND i.bid_placed = 'no'
                                ";
                        }
                        else if ($bidtab == 'expired')
                        {
                                $query = "
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "MAX(b.bid_id) AS bid_id, b.user_id AS bidderid, MAX(b.bidamount) AS bidamount, MAX(b.date_added) AS date_added, b.date_retracted, b.bidstatus, b.bidstate, b.buyerpaymethod, b.buyershipcost, b.buyershipperid, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.qty, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end)-UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.cid, p.close_date, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.date_starts, p.date_end, p.currentprice, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.filter_escrow, p.reserve_price, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.escrow_id, p.paymethodoptions, p.currencyid, p.description_videourl, p.filter_budget, p.filter_gateway, p.filtered_auctiontype, p.buynow_qty_lot, p.items_in_lot, s.ship_method, s.ship_handlingtime, s.ship_handlingfee$query_field_info
                                        FROM " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "projects AS p,
                                        " . DB_PREFIX . "projects_shipping AS s
                                        WHERE b.project_id = p.project_id
                                            $extra
                                            AND p.project_id = s.project_id
                                            AND b.bidstate != 'retracted'
                                            AND (b.bidstate = 'expired' OR b.bidstate = '')
                                            AND (b.bidstatus = 'outbid')
                                            AND b.user_id = '" . intval($userid) . "'
                                            AND p.project_state = 'product'
                                            AND (p.status = 'expired' OR p.status = 'finished')
                                            AND p.winner_user_id != '" . intval($userid) . "'
                                        $groupby
                                        $orderby
                                        $limit
                                ";
                        }
                        else if ($bidtab == 'active')
                        {
                                $query = "
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "MAX(b.bid_id) AS bid_id, b.user_id AS bidderid, MAX(b.bidamount) AS bidamount, b.estimate_days, MAX(b.date_added) AS date_added, b.date_retracted, b.bidstatus, b.bidstate, b.buyerpaymethod, b.buyershipcost, b.buyershipperid, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.qty, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end)-UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.cid, p.close_date, p.user_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.date_starts, p.date_end, p.currentprice, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.filter_escrow, p.reserve_price, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.escrow_id, p.paymethodoptions, p.currencyid, p.description_videourl, p.filter_budget, p.filter_gateway, p.filtered_auctiontype, p.buynow_qty_lot, p.items_in_lot, s.ship_method, s.ship_handlingtime, s.ship_handlingfee$query_field_info
                                        FROM " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "projects AS p,
                                        " . DB_PREFIX . "projects_shipping AS s
                                        WHERE b.project_id = p.project_id
                                            $extra
                                            AND p.project_id = s.project_id
                                            AND b.user_id = '" . intval($userid) . "'
                                            AND b.bidstate != 'retracted'
                                            AND p.project_state = 'product'
                                            AND (b.bidstatus = 'placed'
                                                AND p.status != 'finished'
                                                AND p.status != 'expired'
                                                AND p.status != 'archived'
                                                AND p.status != 'closed'
                                                AND p.status != 'delisted')
                                        $groupby
                                        $orderby
                                        $limit
                                ";
                        }
                        return $query;
                }
        }
        
        /**
        * Function for printing a specific bid tab sql query.
        *
        * @param       string       bid tab
        * @param       string       count or string
        * @param       string       group by statement
        * @param       string       order by statement
        * @param       integer      limit
        * @param       integer      user id
        * @param       string       extra sql query (used for listing period for service bid results)
        *
        * @return     string       MySQL Query
        */
        function fetch_bidtab_sql($bidtab = '', $countorstring = '', $groupby = '', $orderby = '', $limit = '', $userid = 0, $extra = '', $keyw = '')
        {
                global $ilance, $phrase, $show;
                
                $query_field_info = $query_field_data = '';
				
		$keyw = $ilance->db->escape_string($keyw);
                
                ($apihook = $ilance->api('fetch_bidtab_sql')) ? eval($apihook) : false;
                
                if ($countorstring == 'count')
                {
                        if ($bidtab == 'delisted')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "b.bid_id
                                        FROM " . DB_PREFIX . "project_realtimebids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                                $extra
                                                AND b.bidstate != 'retracted'
                                                AND b.bidstate != 'archived'
                                                AND b.user_id = '" . intval($userid) . "'
                                                AND p.project_state = 'service'
                                                AND (p.status = 'open' AND b.bidstatus = 'declined'
                                                        OR p.status = 'closed' AND b.bidstatus = 'declined'
                                                        OR p.status = 'expired' AND b.bidstatus = 'declined'
                                                        OR p.status = 'delisted' AND b.bidstatus = 'declined'
                                                        OR p.status = 'finished' AND b.bidstatus = 'declined'
                                                        OR p.status = 'wait_approval' AND b.bidstatus = 'declined'
                                                        OR p.status = 'approval_accepted' AND b.bidstatus = 'declined'
                                                        OR p.status = 'frozen' AND b.bidstatus = 'declined'
                                                        OR p.status = 'finished' AND b.bidstatus = 'declined'
                                                        OR p.status = 'archived' AND b.bidstatus = 'declined')
						AND p.project_title LIKE '%" . $keyw . "%'
                                        $groupby
                                        $orderby
                                        $limit
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($bidtab == 'retracted')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "b.bid_id
                                        FROM " . DB_PREFIX . "project_realtimebids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                                $extra
                                                AND b.bidstate = 'retracted'
                                                AND b.user_id = '" . intval($userid) . "'
                                                AND p.project_state = 'service' 
						AND p.project_title LIKE '%" . $keyw . "%'
                                        $groupby
                                        $orderby
                                        $limit
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($bidtab == 'invited')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.project_id
                                        FROM " . DB_PREFIX . "projects AS p,
                                        " . DB_PREFIX . "project_invitations as i
                                        WHERE i.project_id = p.project_id
                                                $extra
                                                AND i.seller_user_id = '" . intval($userid) . "'
                                                AND p.project_state = 'service'
                                                AND p.status = 'open'
                                                AND i.bid_placed = 'no'
						AND p.project_title LIKE '%" . $keyw . "%'
                                        $groupby
                                        $orderby
                                        $limit
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($bidtab == 'expired')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "b.bid_id
                                        FROM " . DB_PREFIX . "project_realtimebids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                                $extra
                                                AND b.bidstate != 'retracted'
                                                AND b.bidstate != 'archived'
                                                AND b.user_id = '" . intval($userid) . "'
                                                AND p.project_state = 'service'
                                                AND (p.status = 'expired' AND b.bidstatus = 'placed'
                                                        OR p.status = 'wait_approval' AND b.bidstatus = 'placed'
                                                        OR p.status = 'approval_accepted'AND bidstatus = 'choseanother'
                                                        OR p.status = 'finished' AND bidstatus = 'choseanother'
                                                        OR p.status = 'delisted' AND bidstatus = 'choseanother'
                                                        OR p.status = 'frozen' AND bidstatus = 'choseanother'
                                                        OR p.status = 'archived' AND bidstatus = 'choseanother')
						AND p.project_title LIKE '%" . $keyw . "%'
                                        $groupby
                                        $orderby
                                        $limit
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = 0;
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($bidtab == 'archived')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "b.bid_id
                                        FROM " . DB_PREFIX . "project_realtimebids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                                $extra
                                                AND b.bidstate = 'archived'
                                                AND b.user_id = '" . intval($userid) . "'
                                                AND p.project_state = 'service'
                                                AND b.bidstate = 'archived'
						AND p.project_title LIKE '%" . $keyw . "%'
                                        $groupby
                                        $orderby
                                        $limit
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($bidtab == 'awarded')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.project_id
                                        FROM " . DB_PREFIX . "project_realtimebids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                                $extra
                                                AND b.bidstate != 'retracted'
                                                AND b.user_id = '" . intval($userid) . "'
                                                AND p.project_state = 'service'
                                                AND (p.status = 'wait_approval' AND b.bidstatus = 'placed' AND b.bidstate = 'wait_approval'
                                                        OR p.status = 'approval_accepted' AND b.bidstatus = 'awarded' AND b.bidstate != 'archived'
                                                        OR p.status = 'archived' AND b.bidstatus = 'awarded' AND b.bidstate != 'archived'
                                                        OR p.status = 'closed' AND b.bidstatus = 'awarded' AND b.bidstate != 'archived'
                                                        OR p.status = 'frozen' AND b.bidstatus = 'awarded' AND b.bidstate != 'archived'
                                                        OR p.status = 'finished' AND b.bidstatus = 'awarded' AND b.bidstate != 'archived'
                                                        OR p.status = 'delisted' AND b.bidstatus = 'awarded' AND b.bidstate != 'archived')
						AND p.project_title LIKE '%" . $keyw . "%'
                                        $groupby
                                        $orderby
                                        $limit
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($bidtab == 'active')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "bid_id
                                        FROM " . DB_PREFIX . "project_realtimebids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                                $extra
                                                AND b.bidstate != 'retracted'
                                                AND b.bidstate != 'archived'
                                                AND b.user_id = '" . intval($userid) . "'
                                                AND p.project_state = 'service'
                                                AND p.status = 'open'
                                                AND b.bidstatus = 'placed'
						AND p.project_title LIKE '%" . $keyw . "%'
                                        $groupby
                                        $orderby
                                        $limit
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        else if ($bidtab == 'serviceescrow')
                        {
                                $exequery = $ilance->db->query("
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.project_id
                                        FROM " . DB_PREFIX . "projects AS p,
                                        " . DB_PREFIX . "users AS u,
                                        " . DB_PREFIX . "projects_escrow AS e,
                                        " . DB_PREFIX . "project_bids AS b,
                                        " . DB_PREFIX . "invoices AS i
                                        WHERE u.user_id = '" . intval($userid) . "'
                                                $extra
                                                AND b.bidstate != 'retracted'
                                                AND e.user_id = u.user_id
                                                AND e.status != 'cancelled'
                                                AND e.bid_id = b.bid_id
                                                AND e.user_id = b.user_id
                                                AND e.project_id = p.project_id
                                                AND e.invoiceid = i.invoiceid
                                                AND i.invoicetype = 'escrow'
                                                AND p.project_state = 'service'
                                                AND i.projectid = e.project_id
						AND p.project_title LIKE '%" . $keyw . "%'
                                        $groupby
                                        $orderby
                                        $limit
                                ", 0, null, __FILE__, __LINE__);
                                $sqlcount = '0';
                                if ($ilance->db->num_rows($exequery) > 0)
                                {
                                        $sqlcount = $ilance->db->num_rows($exequery);
                                }
                        }
                        return $sqlcount;
                }
                else
                {
                        if ($bidtab == 'delisted')
                        {
                                $query = "
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "b.bid_id, b.id, b.bidamount, b.bidamounttype, b.estimate_days, b.date_added, b.bidstatus, b.bidstate, b.fvf, b.buyerpaymethod, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.qty, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.filter_escrow, p.currencyid, p.close_date, p.description_videourl, p.filter_budget, p.filter_gateway, p.filtered_auctiontype, p.buynow_qty_lot, p.items_in_lot$query_field_info
                                        FROM " . DB_PREFIX . "project_realtimebids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                                $extra
                                                AND b.bidstate != 'retracted'
                                                AND b.bidstate != 'archived'
                                                AND b.user_id = '" . intval($userid) . "'
                                                AND p.project_state = 'service'
                                                AND (p.status = 'open' AND b.bidstatus = 'declined'
                                                        OR p.status = 'closed' AND b.bidstatus = 'declined'
                                                        OR p.status = 'expired' AND b.bidstatus = 'declined'
                                                        OR p.status = 'delisted' AND b.bidstatus = 'declined'
                                                        OR p.status = 'finished' AND b.bidstatus = 'declined'
                                                        OR p.status = 'wait_approval' AND b.bidstatus = 'declined'
                                                        OR p.status = 'approval_accepted' AND b.bidstatus = 'declined'
                                                        OR p.status = 'frozen' AND b.bidstatus = 'declined'
                                                        OR p.status = 'finished' AND b.bidstatus = 'declined'
                                                        OR p.status = 'archived' AND b.bidstatus = 'declined')
                                                AND p.project_title LIKE '%" . $keyw . "%'
                                        $groupby
                                        $orderby
                                        $limit
                                ";
                        }
                        else if ($bidtab == 'retracted')
                        {
                                $query = "
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "b.bid_id, b.id, b.bidamount, b.bidamounttype, b.estimate_days, b.date_added, b.bidstatus, b.bidstate, b.fvf, b.buyerpaymethod, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.qty, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.filter_escrow, p.currencyid, p.close_date, p.description_videourl, p.filter_budget, p.filter_gateway, p.filtered_auctiontype, p.buynow_qty_lot, p.items_in_lot$query_field_info
                                        FROM " . DB_PREFIX . "project_realtimebids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                                $extra
                                                AND b.bidstate = 'retracted'
                                                AND b.user_id = '" . intval($userid) . "'
                                                AND p.project_state = 'service' 
						AND p.project_title LIKE '%" . $keyw . "%'
                                        $groupby
                                        $orderby
                                        $limit
                                ";
                        }
                        else if ($bidtab == 'invited')
                        {
                                $query = "
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.date_starts, p.fvf, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.filter_escrow, p.currencyid, p.close_date, p.description_videourl, p.filter_budget, p.filter_gateway, p.filtered_auctiontype, p.buynow_qty_lot, p.items_in_lot$query_field_info
                                        FROM " . DB_PREFIX . "projects AS p,
                                        " . DB_PREFIX . "project_invitations as i
                                        WHERE i.project_id = p.project_id
                                                $extra
                                                AND i.seller_user_id = '" . intval($userid) . "'
                                                AND p.project_state = 'service'
                                                AND p.status = 'open'
                                                AND i.bid_placed = 'no'
						AND p.project_title LIKE '%" . $keyw . "%'
                                        $groupby
                                        $orderby
                                        $limit
                                ";
                        }
                        else if ($bidtab == 'expired')
                        {
                                $query = "
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "b.bid_id, b.id, b.bidamount, b.estimate_days, b.bidstatus, b.bidstate, b.bidamounttype, b.date_added, b.bidstatus, b.bidstate, b.fvf, b.buyerpaymethod, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.qty, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.filter_escrow, p.currencyid, p.close_date, p.description_videourl, p.filter_budget, p.filter_gateway, p.filtered_auctiontype, p.buynow_qty_lot, p.items_in_lot$query_field_info
                                        FROM " . DB_PREFIX . "project_realtimebids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                                $extra
                                                AND b.bidstate != 'retracted'
                                                AND b.bidstate != 'archived'
                                                AND b.user_id = '" . intval($userid) . "'
                                                AND p.project_state = 'service'
                                                 AND (p.status = 'expired' AND b.bidstatus = 'placed'
                                                        OR p.status = 'wait_approval' AND b.bidstatus = 'placed'
                                                        OR p.status = 'approval_accepted' AND bidstatus = 'choseanother'
                                                        OR p.status = 'finished' AND bidstatus = 'choseanother'
                                                        OR p.status = 'delisted' AND bidstatus = 'choseanother'
                                                        OR p.status = 'frozen' AND bidstatus = 'choseanother'
                                                        OR p.status = 'archived' AND bidstatus = 'choseanother')
						AND p.project_title LIKE '%" . $keyw . "%'
                                        $groupby
                                        $orderby
                                        $limit
                                ";
                        }
                        else if ($bidtab == 'archived')
                        {
                                $query = "
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "b.bid_id, b.id, b.bidamount, b.bidamounttype, b.estimate_days, b.date_added, b.bidstatus, b.bidstate, b.fvf, b.buyerpaymethod, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.qty, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.filter_escrow, p.currencyid, p.close_date, p.description_videourl, p.filter_budget, p.filter_gateway, p.filtered_auctiontype, p.buynow_qty_lot, p.items_in_lot$query_field_info
                                        FROM " . DB_PREFIX . "project_realtimebids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                                $extra
                                                AND b.bidstate = 'archived'
                                                AND b.user_id = '" . intval($userid) . "'
                                                AND p.project_state = 'service'
                                                AND b.bidstate = 'archived'
						AND p.project_title LIKE '%" . $keyw . "%'
                                        $groupby
                                        $orderby
                                        $limit
                                ";
                        }
                        else if ($bidtab == 'awarded')
                        {
                                $query = "
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "b.bid_id, b.id, b.bidamount, b.bidamounttype, b.estimate_days, b.date_added, b.bidstatus, b.bidstate, b.fvf, b.buyerpaymethod, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.qty, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.filter_escrow, p.currencyid, p.close_date, p.description_videourl, p.filter_budget, p.filter_gateway, p.filtered_auctiontype, p.buynow_qty_lot, p.items_in_lot$query_field_info
                                        FROM " . DB_PREFIX . "project_realtimebids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                                $extra
                                                AND b.bidstate != 'retracted'
                                                AND b.user_id = '" . intval($userid) . "'
                                                AND p.project_state = 'service'
                                                AND (p.status = 'wait_approval' AND b.bidstatus = 'placed' AND b.bidstate = 'wait_approval'
                                                        OR p.status = 'approval_accepted' AND b.bidstatus = 'awarded' AND b.bidstate != 'archived'
                                                        OR p.status = 'archived' AND b.bidstatus = 'awarded' AND b.bidstate != 'archived'
                                                        OR p.status = 'closed' AND b.bidstatus = 'awarded' AND b.bidstate != 'archived'
                                                        OR p.status = 'frozen' AND b.bidstatus = 'awarded' AND b.bidstate != 'archived'
                                                        OR p.status = 'finished' AND b.bidstatus = 'awarded' AND b.bidstate != 'archived'
                                                        OR p.status = 'delisted' AND b.bidstatus = 'awarded' AND b.bidstate != 'archived')
												AND p.project_title LIKE '%" . $keyw . "%'
                                        $groupby
                                        $orderby
                                        $limit
                                ";
                        }
                        else if ($bidtab == 'active')
                        {
                                $query = "
                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "b.bid_id, b.id, b.bidamount, b.bidamounttype, b.estimate_days, b.date_added, b.bidstatus, b.bidstate, b.fvf, b.buyerpaymethod, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.qty, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.filter_escrow, p.currencyid, p.close_date, p.description_videourl, p.filter_budget, p.filter_gateway, p.filtered_auctiontype, p.buynow_qty_lot, p.items_in_lot$query_field_info
                                        FROM " . DB_PREFIX . "project_realtimebids AS b,
                                        " . DB_PREFIX . "projects AS p
                                        WHERE b.project_id = p.project_id
                                                $extra
                                                AND b.bidstate != 'retracted'
                                                AND b.bidstate != 'archived'
                                                AND b.user_id = '" . intval($userid) . "'
                                                AND p.project_state = 'service'
                                                AND p.status = 'open'
                                                AND b.bidstatus = 'placed'
                                                AND p.project_title LIKE '%" . $keyw . "%'
                                        $groupby
                                        $orderby
                                        $limit
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