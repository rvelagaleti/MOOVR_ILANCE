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
* Proxy Bid class to perform the majority of proxy bidding functions within ILance.
*
* @package      iLance\Bid\Proxy
* @version      4.0.0.8059
* @author       ILance
*/
class bid_proxy extends bid
{
        /**
        * Function to fetch a bidder's proxy bid for a particular auction event.
        *
        * @param       integer      project id
        * @param       integer      user id
        * 
        * @return      string       bid amount
        */
        function fetch_user_proxy_bid($projectid = 0, $userid = 0)
        {
                global $ilance;
                $highest = 0;
                $sqlproxy = $ilance->db->query("
                        SELECT maxamount, user_id
                        FROM " . DB_PREFIX . "proxybid
                        WHERE project_id = '" . intval($projectid) . "'
                        AND user_id = '" . intval($userid) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sqlproxy) > 0)
                {
                        while ($top = $ilance->db->fetch_array($sqlproxy, DB_ASSOC))
                        {
                                return $top['maxamount'];
                        }
                }
                return $highest;
        }
        
        /**
        * Function to fetch the highest proxy bid for a particular auction event.
        *
        * @param       integer      project id
        * @param       integer      user id
        * 
        * @return      string       bid amount
        */
        function fetch_highest_proxy_bid($projectid = 0, $userid = 0)
        {
                global $ilance;
                $highest = 0;
                $sqlproxy = $ilance->db->query("
                        SELECT maxamount, user_id
                        FROM " . DB_PREFIX . "proxybid
                        WHERE project_id = '" . intval($projectid) . "'
                        ORDER BY maxamount DESC
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sqlproxy) > 0)
                {
                        while ($top = $ilance->db->fetch_array($sqlproxy, DB_ASSOC))
                        {
                                if ($top['user_id'] == $userid)
                                {
                                        return $top['maxamount'];
                                }
                        }
                }
                return $highest;
        }
        
        /**
        * Function to fetch the second highest proxy bid for a particular auction event.
        *
        * @param       integer      project id
        * @param       integer      user id
        * 
        * @return      string       bid amount
        */
        function fetch_second_highest_proxy_bid($projectid = 0, $userid = 0)
        {
                global $ilance;
                $second_highest = 0;
                $sqlhighest = $ilance->db->query("
                        SELECT maxamount, user_id
                        FROM " . DB_PREFIX . "proxybid
                        WHERE project_id = '" . intval($projectid) . "'
                        ORDER BY maxamount DESC, date_added ASC
                        LIMIT 2
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sqlhighest) == 2)
                {
                        $count = 0;
                        while ($toptwo = $ilance->db->fetch_array($sqlhighest, DB_ASSOC))
                        {
                                $count++;
                                // fetch 2nd highest proxy bid amount, ie: 90.00
                                if ($count == 2 AND $toptwo['user_id'] == $userid)
                                {
                                        return $toptwo['maxamount'];
                                }
                        }
                }
                return $second_highest;
        }
        
        /**
        * Function to fetch the earliest highest proxy bid for a particular auction event.
        *
        * @param       integer      project id
        * 
        * @return      string       bid amount
        */
        function fetch_first_highest_proxybid($projectid = 0)
        {
                global $ilance;
                $highest = $userid = 0;
                $sqlhighest = $ilance->db->query("
                        SELECT maxamount, user_id
                        FROM " . DB_PREFIX . "proxybid
                        WHERE project_id = '" . intval($projectid) . "'
                        ORDER BY maxamount DESC, date_added ASC
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sqlhighest) > 0)
                {
                        while ($top = $ilance->db->fetch_array($sqlhighest, DB_ASSOC))
                        {
                                $highest = $top['maxamount'];
                                $userid = $top['user_id'];
                        }
                }
                return array($highest, $userid);
        }
        
        /**
        * Function to fetch the second highest proxy bid for a particular auction event.
        *
        * @param       integer      project id
        * 
        * @return      string       bid amount
        */
        function fetch_second_highest_proxybid($projectid = 0)
        {
                global $ilance;
                $amount = $userid = 0;
                $sqlhighest = $ilance->db->query("
                        SELECT maxamount, user_id
                        FROM " . DB_PREFIX . "proxybid
                        WHERE project_id = '" . intval($projectid) . "'
                        ORDER BY maxamount DESC, date_added ASC
                        LIMIT 2
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sqlhighest) > 1)
                {
                        while ($toptwo = $ilance->db->fetch_array($sqlhighest, DB_ASSOC))
                        {
                                // fetch 2nd highest proxy bid amount, ie: 90.00
                                $amount = $toptwo['maxamount'];
                                $userid = $toptwo['user_id'];
                        }
                }
                return array($amount, $userid);
        }
        
        /**
        * Function to do all the proxy bid backend tasks after a proxy bid is placed.
        *
        * @param       integer      listing id
        * @param       integer      bidder id
        * @param       integer      owner id
        * @param       bool         do we skip this bidder?
        * @param       integer      if we are skipping this bidder, this would be the bidder's user id
        */
        function do_proxy_bidder($projectid = 0, $bidderid = 0, $ownerid = 0, $skipbidder, $skipbidderid)
        {
                global $ilance, $ilconfig, $ilpage, $phrase;
                // we skip bidder due to the last bidder placing a bid.
                // we'll continue this logic for all other proxy bidders
                // against the last bidder
                $sqlextra = '';
                if ($skipbidder)
                {
                        $sqlextra = "AND user_id != '" . intval($skipbidderid) . "'";
                }
                // fetch highest bid currently placed in bids table
                $highestbid = $this->fetch_highest_bid(intval($projectid));
                $highestproxy = $this->fetch_first_highest_proxybid(intval($projectid));
                $min_bidamount = $highestbid;
                // #################################################################
                // fetch all proxy bidders with maximum bid amounts entered
                // #################################################################
                $sql = $ilance->db->query("
                        SELECT id, project_id, user_id, maxamount, date_added
                        FROM " . DB_PREFIX . "proxybid
                        WHERE project_id = '" . intval($projectid) . "'
                        $sqlextra
                        ORDER BY date_added ASC
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        // can this bidder place any more bids (subscription checkup)?
                        $bidtotal = $ilance->permissions->check_access($bidderid, 'bidlimitperday');
                        $bidsleft = max(0, ($bidtotal - fetch_bidcount_today($bidderid)));
                        // we will loop results based on earilest bids placed
                        while ($bids = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                // is this proxy user already the highest bidder?
                                if ($this->fetch_highest_bidder(intval($projectid)) != $bids['user_id'] AND $bidsleft > 0)
                                {
                                        // fetch last bid (highest)
                                        $highestbid = $this->fetch_highest_bid(intval($projectid));
                                        // fetch next minimum bid amount that can be placed
                                        $cid = fetch_auction('cid', intval($projectid));
                                        // this will take the highest bid and apply any category increment charges to it.
                                        // so if we had 300.00 as highest, next min to place would be 310.00 (if we had 10.00 increments)
                                        $min_bidamount = $this->fetch_minimum_bid($highestbid, $cid);
                                        DEBUG("do_proxy_bidder() - It appears the next minimum bid amount that can be placed is: $min_bidamount in category id $cid", 'NOTICE');
                                        if ($bids['maxamount'] >= $min_bidamount)
                                        {
                                                DEBUG("SQL: do_proxy_bidder() - INSERT INTO " . DB_PREFIX . "project_bids amount: $min_bidamount for bidder id $bids[user_id]", 'NOTICE');
                                                // enter bid as $min_bidamount for bidder.
                                                $ilance->db->query("
                                                        INSERT INTO " . DB_PREFIX . "project_bids
                                                        (bid_id, user_id, project_id, project_user_id, proposal, bidamount, date_added, bidstatus, bidstate, state, isproxybid)
                                                        VALUES(
                                                        NULL,
                                                        '" . $bids['user_id'] . "',
                                                        '" . intval($projectid) . "',
                                                        '" . intval($ownerid) . "',
                                                        '" . $ilance->db->escape_string('{_bid_placed_via_proxy_agent}') . "',
                                                        '" . sprintf("%01.2f", $min_bidamount) . "',
                                                        '" . $bids['date_added'] . "',
                                                        'placed',
                                                        '',
                                                        'product',
                                                        '1')
                                                ", 0, null, __FILE__, __LINE__);
                                                $this_rbid_id = $ilance->db->insert_id();
                                                $ilance->db->query("
                                                        INSERT INTO " . DB_PREFIX . "project_realtimebids
                                                        (id, bid_id, user_id, project_id, project_user_id, proposal, bidamount, date_added, bidstatus, bidstate, state, isproxybid)
                                                        VALUES(
                                                        NULL,
                                                        '" . intval($this_rbid_id) . "',
                                                        '" . $bids['user_id'] . "',
                                                        '" . intval($projectid) . "',
                                                        '" . intval($ownerid) . "',
                                                        '" . $ilance->db->escape_string('{_bid_placed_via_proxy_agent}') . "',
                                                        '" . sprintf("%01.2f", $min_bidamount) . "',
                                                        '" . DATETIME24H . "',
                                                        'placed',
                                                        '',
                                                        'product',
                                                        '1')
                                                ", 0, null, __FILE__, __LINE__);
                                                // update bid count and current price
                                                DEBUG("SQL: do_proxy_bidder() - Set bid count and new current auction price to $min_bidamount", 'NOTICE');
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "projects
                                                        SET bids = bids + 1,
                                                        currentprice = '" . sprintf("%01.2f", $min_bidamount) . "'
                                                        WHERE project_id = '" . intval($projectid) . "'
                                                        LIMIT 1
                                                ", 0, null, __FILE__, __LINE__);
                                        }
                                        else if ($bids['maxamount'] == $highestbid)
                                        {
                                                DEBUG("SQL: do_proxy_bidder() - Insert bid into " . DB_PREFIX . "project_bids with amount $highestbid for bidder #$bids[user_id] (opponent)", 'NOTICE');
                                                // enter bid as $min_bidamount for bidder.
                                                $ilance->db->query("
                                                        INSERT INTO " . DB_PREFIX . "project_bids
                                                        (bid_id, user_id, project_id, project_user_id, proposal, bidamount, date_added, bidstatus, bidstate, state, isproxybid)
                                                        VALUES(
                                                        NULL,
                                                        '" . $bids['user_id'] . "',
                                                        '" . intval($projectid) . "',
                                                        '" . intval($ownerid) . "',
                                                        '" . $ilance->db->escape_string('{_bid_placed_via_proxy_agent}') . "',
                                                        '" . sprintf("%01.2f", $highestbid) . "',
                                                        '" . $bids['date_added'] . "',
                                                        'placed',
                                                        '',
                                                        'product',
                                                        '1')
                                                ", 0, null, __FILE__, __LINE__);
                                                $this_rbid_id = $ilance->db->insert_id();
                                                $ilance->db->query("
                                                        INSERT INTO " . DB_PREFIX . "project_realtimebids
                                                        (id, bid_id, user_id, project_id, project_user_id, proposal, bidamount, date_added, bidstatus, bidstate, state, isproxybid)
                                                        VALUES(
                                                        NULL,
                                                        '" . intval($this_rbid_id) . "',
                                                        '" . $bids['user_id'] . "',
                                                        '" . intval($projectid) . "',
                                                        '" . intval($ownerid) . "',
                                                        '" . $ilance->db->escape_string('{_bid_placed_via_proxy_agent}') . "',
                                                        '" . sprintf("%01.2f", $highestbid) . "',
                                                        '" . DATETIME24H . "',
                                                        'placed',
                                                        '',
                                                        'product',
                                                        '1')
                                                ", 0, null, __FILE__, __LINE__);
                                                // update bid count and current price
                                                DEBUG("SQL: do_proxy_bidder() - Set bid count and new current auction price to $highestbid", 'NOTICE');
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "projects
                                                        SET bids = bids + 1,
                                                        currentprice = '" . sprintf("%01.2f", $highestbid) . "'
                                                        WHERE project_id = '" . intval($projectid) . "'
                                                        LIMIT 1
                                                ", 0, null, __FILE__, __LINE__);
                                        }
                                        else if ($highestbid < $bids['maxamount'] AND $bids['maxamount'] < $min_bidamount)
                                        {
                                                DEBUG("SQL: do_proxy_bidder() - Insert bid into " . DB_PREFIX . "project_bids with amount $bids[maxamount] for bidder #$bids[user_id] (opponent)", 'NOTICE');
                                                // enter bid as $min_bidamount for bidder.
                                                $ilance->db->query("
                                                        INSERT INTO " . DB_PREFIX . "project_bids
                                                        (bid_id, user_id, project_id, project_user_id, proposal, bidamount, date_added, bidstatus, bidstate, state, isproxybid)
                                                        VALUES(
                                                        NULL,
                                                        '" . $bids['user_id'] . "',
                                                        '" . intval($projectid) . "',
                                                        '" . intval($ownerid) . "',
                                                        '" . $ilance->db->escape_string('{_bid_placed_via_proxy_agent}') . "',
                                                        '" . sprintf("%01.2f", $bids['maxamount']) . "',
                                                        '" . $bids['date_added'] . "',
                                                        'placed',
                                                        '',
                                                        'product',
                                                        '1')
                                                ", 0, null, __FILE__, __LINE__);
                                                $this_rbid_id = $ilance->db->insert_id();
                                                $ilance->db->query("
                                                        INSERT INTO " . DB_PREFIX . "project_realtimebids
                                                        (id, bid_id, user_id, project_id, project_user_id, proposal, bidamount, date_added, bidstatus, bidstate, state, isproxybid)
                                                        VALUES(
                                                        NULL,
                                                        '" . intval($this_rbid_id) . "',
                                                        '" . $bids['user_id'] . "',
                                                        '" . intval($projectid) . "',
                                                        '" . intval($ownerid) . "',
                                                        '" . $ilance->db->escape_string('{_bid_placed_via_proxy_agent}') . "',
                                                        '" . sprintf("%01.2f", $bids['maxamount']) . "',
                                                        '" . DATETIME24H . "',
                                                        'placed',
                                                        '',
                                                        'product',
                                                        '1')
                                                ", 0, null, __FILE__, __LINE__);
                                                // update bid count and current price
                                                DEBUG("SQL: do_proxy_bidder() - Set bid count and new current auction price to $bids[maxamount]", 'NOTICE');
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "projects
                                                        SET bids = bids + 1,
                                                        currentprice = '" . sprintf("%01.2f", $bids['maxamount']) . "'
                                                        WHERE project_id = '" . intval($projectid) . "'
                                                        LIMIT 1
                                                ", 0, null, __FILE__, __LINE__);
                                        }
                                        else
                                        {
                                                DEBUG("PROXY: Nothing to do", 'NOTICE');
                                        }
                                }
                                else
                                {
                                        if ($bidsleft <= 0)
                                        {
                                                DEBUG("PROXY: bidder ID #$bidderid cannot place any more bids today (bidlimit) membership restriction", 'NOTICE');
                                        }
                                        else
                                        {
                                                DEBUG("PROXY: Nothing to do", 'NOTICE');
                                        }
                                }
                        }
                }
                else
                {
                        DEBUG("PROXY: Nothing to do", 'NOTICE');        
                }
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>