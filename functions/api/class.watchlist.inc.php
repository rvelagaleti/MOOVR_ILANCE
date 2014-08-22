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
* Watchlist class to perform the majority of watchlist and notification tasks within ILance.
*
* @package      iLance\Watchlist
* @version      4.0.0.8059
* @author       ILance
*/
class watchlist
{
        /*
        * Function to send a watchlist notification based on a particular notification type
        *
        * @param       
        *
        * @return      
        */
        function send_notification($bidderid = 0, $type = 'lowbidnotify', $id = 0, $bidamount = 0)
        {
                global $ilance, $ilconfig;
                if ($type == 'lowbidnotify')
                {
                        // select all bidders that are watching this auction with lowbidnotify enabled
                        $sql = $ilance->db->query("
                                SELECT user_id
                                FROM " . DB_PREFIX . "watchlist
                                WHERE lowbidnotify = '1'
                                    AND subscribed = '1'
                                    AND watching_project_id = '" . intval($id) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                while ($bidders = $ilance->db->fetch_array($sql, DB_ASSOC))
                                {
                                        $sql_low = $ilance->db->query("
                                                SELECT bidamount
                                                FROM " . DB_PREFIX . "project_bids
                                                WHERE project_id = '" . intval($id) . "'
                                                    AND user_id = '" . $bidders['user_id'] . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql_low) > 0)
                                        {
                                                $result_low = $ilance->db->fetch_array($sql_low, DB_ASSOC);
                                                if ($bidamount < $result_low['bidamount'])
                                                {
                                                        $ilance->email->mail = fetch_user('email', $bidders['user_id']);
                                                        $ilance->email->slng = fetch_user_slng($bidders['user_id']);
                                                        $ilance->email->get('lower_bid_notification_alert');		
                                                        $ilance->email->set(array(
                                                                '{{p_id}}' => $id,
                                                        ));
                                                        $ilance->email->send();
                                                }
                                        }
                                }
                        }
                }
                else if ($type == 'highbidnotify')
                {
                        // select all bidders that are watching this auction with high bid notify enabled
                        $sql = $ilance->db->query("
                                SELECT user_id
                                FROM " . DB_PREFIX . "watchlist
                                WHERE highbidnotify = '1'
                                    AND subscribed = '1'
                                    AND watching_project_id = '" . intval($id) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                while ($bidders = $ilance->db->fetch_array($sql, DB_ASSOC))
                                {
                                        // fetch last bid placed by this user
                                        $sql_high = $ilance->db->query("
                                                SELECT bidamount
                                                FROM " . DB_PREFIX . "project_bids
                                                WHERE project_id = '" . intval($id) . "'
                                                    AND user_id = '" . $bidders['user_id'] . "'
                                                ORDER BY bid_id DESC
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql_high) > 0)
                                        {
                                                $result = $ilance->db->fetch_array($sql_high, DB_ASSOC);
                                                if ($bidamount > $result['bidamount'])
                                                {
                                                        $currencyid = fetch_auction('currencyid', $id);
                                                        
                                                        $ilance->email->mail = fetch_user('email', $bidders['user_id']);
                                                        $ilance->email->slng = fetch_user_slng($bidders['user_id']);
                                                        $ilance->email->get('higher_bid_notification_alert');		
                                                        $ilance->email->set(array(
                                                                '{{p_id}}' => $id,
                                                                '{{username}}' => fetch_user('username', $bidders['user_id']),
                                                                '{{project_title}}' => fetch_auction('project_title', $id),
                                                                '{{bidamount}}' => $ilance->currency->format($bidamount, $currencyid)
                                                        ));
                                                        $ilance->email->send();
                                                }
                                        }
                                }
                        }
                }
        }

        /*
        * Function to insert/add a new watchlist entry
        *
        * @param       
        *
        * @return      
        */
        function insert_item($userid = 0, $watchingid = 0, $watchtype = '', $comment = '', $lowbidnotify = 0, $highbidnotify = 0, $hourleftnotify = 0, $subscribed = 0)
        {
                global $ilance, $phrase, $ilconfig;
                if ($watchingid > 0 AND $userid > 0)
                {
                        if (empty($comment))
                        {
                                $comment = '{_added} ' . print_date(DATETIME24H, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                        }
                        if ($watchtype == 'mprovider')
                        {
                                $sql = $ilance->db->query("
                                        SELECT watchlistid
                                        FROM " . DB_PREFIX . "watchlist
                                        WHERE watching_user_id = '" . intval($watchingid) . "'
                                                AND user_id = '" . intval($userid) . "'
                                                AND state = 'mprovider'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) == 0)
                                {
                                        $ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "watchlist
                                                (watchlistid, user_id, watching_user_id, comment, state, mode, dateadded)
                                                VALUES(
                                                NULL,
                                                '" . intval($userid) . "',
                                                '" . intval($watchingid) . "',
                                                '" . $ilance->db->escape_string($comment) . "',
                                                'mprovider',
                                                'product',
                                                NOW())
                                        ", 0, null, __FILE__, __LINE__);
                                        return true;
                                }
                        }
                        else if ($watchtype == 'sprovider')
                        {
                                $sql = $ilance->db->query("
                                        SELECT watchlistid
                                        FROM " . DB_PREFIX . "watchlist
                                        WHERE watching_user_id = '" . intval($watchingid) . "'
                                                AND user_id = '" . intval($userid) . "'
                                                AND state = 'sprovider'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) == 0)
                                {
                                        $ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "watchlist
                                                (watchlistid, user_id, watching_user_id, comment, state, mode, dateadded)
                                                VALUES(
                                                NULL,
                                                '" . intval($userid) . "',
                                                '" . intval($watchingid) . "',
                                                '" . $ilance->db->escape_string($comment) . "',
                                                'sprovider',
                                                'service',
                                                NOW())
                                        ", 0, null, __FILE__, __LINE__);
                                        return true;
                                }
                        }
                        else if ($watchtype == 'buyer')
                        {
                                $sql = $ilance->db->query("
                                        SELECT watchlistid
                                        FROM " . DB_PREFIX . "watchlist
                                        WHERE watching_user_id = '" . intval($watchingid) . "'
                                                AND user_id = '" . intval($userid) . "'
                                                AND state = 'buyer'
                                                AND mode = 'product'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) == 0)
                                {
                                        $ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "watchlist
                                                (watchlistid, user_id, watching_user_id, comment, state, mode, dateadded)
                                                VALUES(
                                                NULL,
                                                '" . intval($userid) . "',
                                                '" . intval($watchingid) . "',
                                                '" . $ilance->db->escape_string($comment) . "',
                                                'buyer',
                                                'product',
                                                NOW())
                                        ", 0, null, __FILE__, __LINE__);
                                        return true;
                                }
                        }
                        else if ($watchtype == 'auction')
                        {
                                $mode = fetch_auction('project_state', $watchingid);
                                $sql = $ilance->db->query("
                                        SELECT watchlistid
                                        FROM " . DB_PREFIX . "watchlist
                                        WHERE watching_project_id = '" . intval($watchingid) . "'
                                                AND user_id = '" . intval($userid) . "'
                                                AND state = 'auction'
                                                AND mode = '" . $ilance->db->escape_string($mode) . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) == 0)
                                {
                                        $ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "watchlist
                                                (watchlistid, user_id, watching_project_id, comment, state, mode, dateadded)
                                                VALUES(
                                                NULL,
                                                '" . intval($userid) . "',
                                                '" . intval($watchingid) . "',
                                                '" . $ilance->db->escape_string($comment) . "',
                                                'auction',
                                                '" . $ilance->db->escape_string($mode) . "',
                                                NOW())
                                        ", 0, null, __FILE__, __LINE__);
                                        $insertid = $ilance->db->insert_id();
                                }
                                else
                                {
                                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                                        {
                                              $insertid = $res['watchlistid'];
                                        }
                                }
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "watchlist
                                        SET lowbidnotify = '" . $lowbidnotify . "',
                                        highbidnotify = '" . $highbidnotify . "',
                                        hourleftnotify = '" . $hourleftnotify . "',
                                        subscribed = '" . $subscribed . "'
                                        WHERE watchlistid = '" . intval($insertid) . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                return true;  
                        }
                        else if ($watchtype == 'category')
                        {
                                $mode = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($watchingid) . "'", "cattype");
                                $sql = $ilance->db->query("
                                        SELECT watchlistid
                                        FROM " . DB_PREFIX . "watchlist
                                        WHERE watching_category_id = '" . intval($watchingid) . "'
                                                AND user_id = '" . intval($userid) . "'
                                                AND state = 'category'
                                                AND mode = '" . $ilance->db->escape_string($mode) . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) == 0)
                                {
                                        $ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "watchlist
                                                (watchlistid, user_id, watching_category_id, comment, state, mode, dateadded)
                                                VALUES(
                                                NULL,
                                                '" . intval($userid) . "',
                                                '" . intval($watchingid) . "',
                                                '" . $ilance->db->escape_string($comment) . "',
                                                'cat',
                                                '" . $ilance->db->escape_string($mode) . "',
                                                NOW())
                                        ", 0, null, __FILE__, __LINE__);
                                        return true;
                                }
                        }
                }
                return false;
        }
        
        /*
        * Function to update a watchlist entry
        *
        * @param       
        *
        * @return      
        */
        function update_item($userid = 0, $watchingid = 0, $watchtype = '', $comment = '')
        {
                global $ilance,$phrase, $ilconfig;
                if ($watchingid > 0 AND $userid > 0 AND !empty($watchtype))
                {
                        if (empty($comment))
                        {
                                $comment = '{_added} ' . print_date(DATETIME24H, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                        }
                        else
                        {
                                $comment = $comment;
                        }
                        if ($watchtype == 'mprovider')
                        {
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "watchlist
                                        SET comment = '" . $ilance->db->escape_string($comment) . "'
                                        WHERE watching_user_id = '" . intval($watchingid) . "'
                                                AND user_id = '" . intval($userid) . "'
                                                AND state = 'mprovider'
                                ", 0, null, __FILE__, __LINE__);
                                return true;
                        }
                        else if ($watchtype == 'sprovider')
                        {
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "watchlist
                                        SET comment = '" . $ilance->db->escape_string($comment) . "'
                                        WHERE watching_user_id = '" . intval($watchingid) . "'
                                                AND user_id = '" . intval($userid) . "'
                                                AND state = 'sprovider'
                                ", 0, null, __FILE__, __LINE__);
                                return true;
                        }
                        else if ($watchtype == 'buyer')
                        {
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "watchlist
                                        SET comment = '" . $ilance->db->escape_string($comment) . "'
                                        WHERE watching_user_id = '" . intval($watchingid) . "'
                                                AND user_id = '" . intval($userid) . "'
                                                AND state = 'buyer'
                                ", 0, null, __FILE__, __LINE__);
                                return true;
                        }
                        else if ($watchtype == 'auction')
                        {
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "watchlist
                                        SET comment = '" . $ilance->db->escape_string($comment) . "'
                                        WHERE watching_project_id = '" . intval($watchingid) . "'
                                                AND user_id = '" . intval($userid) . "'
                                                AND state = 'auction'
                                ", 0, null, __FILE__, __LINE__);
                                return true;
                        }
                        else if ($watchtype == 'category')
                        {
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "watchlist
                                        SET comment = '" . $ilance->db->escape_string($comment) . "'
                                        WHERE watching_category_id = '" . intval($watchingid) . "'
                                                AND user_id = '" . intval($userid) . "'
                                                AND state = 'cat'
                                ", 0, null, __FILE__, __LINE__);
                                return true;
                        }
                }
                return false;
        }
        
        /*
        * Function to determine if a user is watching a particular auction
        *
        * @param       integer          auction id
        *
        * @return      boolean          Returns true or false   
        */
        function is_listing_added_to_watchlist($auctionid = 0)
        {
                global $ilance;
                // is added to watchlist?
                if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
                {
                        $sql = $ilance->db->query("
                                SELECT user_id
                                FROM " . DB_PREFIX . "watchlist
                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                    AND watching_project_id = '" . intval($auctionid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                return true;
                        }
                }
                return false;
        }
        
        /*
        * Function to determine if a user is watching a particular seller
        *
        * @param       integer          seller id
        *
        * @return      boolean          Returns true or false   
        */
        function is_seller_added_to_watchlist($userid = 0)
        {
                global $ilance;
                // is added to watchlist?
                if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
                {
                        $sql = $ilance->db->query("
                                SELECT user_id
                                FROM " . DB_PREFIX . "watchlist
                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                    AND watching_user_id = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                return true;
                        }
                }
                return false;
        }
        
        /*
        * Function to fetch array of items being watched by a logged in user
        *
        * @param       integer          listings limit (default 5)
        *
        * @return      boolean          Returns array() with formatted item elements 
        */
        function fetch_watching_items($limit = 5)
        {
                global $ilance, $ilconfig, $ilpage;
                if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
                {
                        $sql = $ilance->db->query("
                                SELECT w.watchlistid, w.user_id, w.watching_project_id, w.watching_user_id, w.watching_category_id, w.comment, w.state, w.lowbidnotify, w.highbidnotify, w.hourleftnotify, w.subscribed, p.project_title, p.project_details, p.currentprice, p.buynow_price, p.startprice, p.bids, p.currencyid, p.date_starts, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime 
				FROM " . DB_PREFIX . "watchlist w
                                LEFT JOIN " . DB_PREFIX . "projects p ON (w.watching_project_id = p.project_id)
				WHERE w.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND w.watching_project_id != '0'
                                        AND p.project_state = 'product'
                                ORDER BY w.watchlistid DESC
                                LIMIT $limit
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                                {
                                        $res['titleplain'] = handle_input_keywords($res['project_title']);
                                        if ($ilconfig['globalauctionsettings_seourls'])
                                        {
                                                $url = construct_seo_url('productauctionplain', 0, $res['watching_project_id'], stripslashes($res['project_title']), '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
                                                $res['picture'] = $ilance->auction->print_item_photo($url, 'thumbmini', $res['watching_project_id'], 1);
                                                $res['title'] = construct_seo_url('productauction', 0, $res['watching_project_id'], handle_input_keywords($res['project_title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
                                        }
                                        else
                                        {
                                                $res['picture'] = $ilance->auction->print_item_photo($ilpage['merch'] . '?id=' . $res['watching_project_id'], 'thumbmini', $res['watching_project_id'], 1);
                                                $res['title'] = '<a href="' . $ilpage['merch'] . '?id=' . $row['watching_project_id'] . '">' . handle_input_keywords($res['project_title']) . '</a>';
                                        }
                                        if ($res['bids'] > 0 AND $res['currentprice'] > $res['startprice'])
                                        {
                                                $res['currentbid'] = $ilance->currency->format($res['currentprice'], $res['currencyid']);
                                        }
                                        else if ($res['bids'] > 0 AND $res['currentprice'] == $res['startprice'])
                                        {
                                                $res['currentbid'] = $ilance->currency->format($res['currentprice'], $res['currencyid']);        
                                        }
                                        else
                                        {
                                                $res['currentbid'] = $ilance->currency->format($res['startprice'], $res['currencyid']);
                                        }
                                        $res['timeleft'] = $ilance->auction->auction_timeleft(false, $res['date_starts'], $res['mytime'], $res['starttime']);
                                        $array[] = $res;
                                }
                                return $array;
                        }
                }
                return '';
        }
        
        /*
        * Function to fetch array of sellers being watched by a logged in user
        *
        * @param       integer          sellers limit (default 5)
        *
        * @return      boolean          Returns array() with formatted seller elements (username, profile pic, etc)
        */
        function fetch_watching_sellers($limit = 5)
        {
                global $ilance;
                if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
                {
                        $sql = $ilance->db->query("
                                SELECT w.watchlistid, w.user_id, w.watching_project_id, w.watching_user_id, w.watching_category_id, w.comment, w.state, w.lowbidnotify, w.highbidnotify, w.hourleftnotify, w.subscribed, u.username
                                FROM " . DB_PREFIX . "watchlist w
                                LEFT JOIN " . DB_PREFIX . "users u ON (w.watching_user_id = u.user_id)
                                WHERE w.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                        AND w.watching_user_id != '0'
                                        AND w.state = 'mprovider'
                                ORDER BY w.watchlistid DESC
                                LIMIT $limit
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                                {
                                        $res['usernameplain'] = $res['username'];
                                        $res['username'] = '<a href="' . print_username($res['watching_user_id'], 'url', 0) . '">' . $res['username'] . '</a>';
                                        $memberinfo = array();
                                        $memberinfo = $ilance->feedback->datastore($res['watching_user_id']);
                                        $res['score'] = $memberinfo['pcnt'];
                                        unset($memberinfo);
                                        $array[] = $res;
                                }
                                return $array;
                        }
                }
                return '';
        }
        
        /*
        * Function to fetch array of saved favourite searches by a logged in user
        *
        * @param       integer          searches limit (default 10)
        *
        * @return      boolean          Returns array() with saved formatted searches elements (keyword, criteria, etc)
        */
        function fetch_favourite_searches($limit = 10, $striphtml = false)
        {
                global $ilance, $ilpage;
                if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
                {
                        $sql = $ilance->db->query("
                                SELECT searchid, user_id, searchoptions, searchoptionstext, title, cattype, subscribed, added, lastsent
                                FROM " . DB_PREFIX . "search_favorites
                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                ORDER BY searchid DESC
                                LIMIT $limit
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                                {
                                        $searchoptions = stripslashes($res['searchoptions']);
                                        $searchoptions = mb_substr($searchoptions, 5);
                                        if ($striphtml)
                                        {
                                                $res['searchoptionstext'] = strip_tags($res['searchoptionstext']);
                                        }
                                        $res['title'] = '<a href="' . HTTP_SERVER . $ilpage['search'] . '?' . $searchoptions . '&amp;searchid=' . $res['searchid'] . '">' . handle_input_keywords($res['title']) . '</a>';
                                        $array[] = $res;
                                }
                                return $array;
                        }
                }
                return '';
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>