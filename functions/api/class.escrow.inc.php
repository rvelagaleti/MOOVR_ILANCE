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
* Escrow class to perform the majority of escrow and related payment functions in ILance
*
* @package      iLance\Escrow
* @version      4.0.0.8059
* @author       ILance
*/
class escrow
{
        /**
        * Function to fetch the escrow status
        *
        * @param       integer      project id
        *
        * @return      bool         Returns the status of the escrow account
        */
        function status($projectid = 0)
        {
                global $ilance, $ilconfig, $phrase, $ilconfig;
                $status = '-';
                if ($ilconfig['escrowsystem_enabled'])
                {
                        $filter_escrow = fetch_auction('filter_escrow', intval($projectid));
                        if ($filter_escrow == '1')
                        {
                                $sql = $ilance->db->query("
                                        SELECT escrow_id
                                        FROM " . DB_PREFIX . "projects
                                        WHERE project_id = '" . intval($projectid) . "'
                                ");
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        $result = $ilance->db->fetch_array($sql, DB_ASSOC);
                                        if ($result['escrow_id'] > 0)
                                        {
                                                $sql2 = $ilance->db->query("
                                                        SELECT status
                                                        FROM " . DB_PREFIX . "projects_escrow
                                                        WHERE escrow_id = '" . $result['escrow_id'] . "'
                                                ");
                                                if ($ilance->db->num_rows($sql2) > 0)
                                                {
                                                        $result2 = $ilance->db->fetch_array($sql2, DB_ASSOC);
                                                        $status = ucfirst($result2['status']);
                                                }
                                        }
                                }
                        }
                }
                return $status;
        }
        
        /**
        * Function to obtain the owner of an escrow account.
        *
        * @param       integer      auction id
        * @param       integer      invoice id
        * @param       string       mode (service or product)
        *
        * @return      string       username of escrow account owner
        */
        function fetch_escrow_owner($projectid = 0, $invoiceid = 0, $mode = '')
        {
                global $ilance;
                
                if ($mode == 'service')
                {
                        // project_user_id = the buyer of this service auction
                        // user_id = the service provider who wants payment from escrow
                        $sql = $ilance->db->query("
                                SELECT project_user_id
                                FROM " . DB_PREFIX . "projects_escrow
                                WHERE project_id = '" . intval($projectid) . "'
                                    AND invoiceid = '" . intval($invoiceid) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql);
                                $value = fetch_user('username', $res['project_user_id']);
                        }
                        else
                        {
                                $value = '--';
                        }
                }
                else if ($mode == 'product')
                {
                        // project_user_id = the merchant of this product auction
                        // user_id = the bidder/winner who must pay this escrow invoice
                        $sql = $ilance->db->query("
                                SELECT project_user_id
                                FROM " . DB_PREFIX . "projects_escrow
                                WHERE project_id = '" . intval($projectid) . "'
                                    AND invoiceid = '" . intval($invoiceid) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql);
                                $value = fetch_user('username', $res['project_user_id']);
                        }
                        else
                        {
                                $value = '--';
                        }
                }
                
                return $value;
        }
        
        /**
        * Function to obtain the opponent's username of an escrow account between a buyer and seller.
        *
        * @param       integer      auction id
        * @param       integer      invoice id
        * @param       string       mode (service or product)
        *
        * @return      string       username of escrow opponent
        */
        function fetch_escrow_opponent($projectid = 0, $invoiceid = 0, $mode = '')
        {
                global $ilance;
                
                if ($mode == 'service')
                {
                        // project_user_id = the buyer of this service auction
                        // user_id = the service provider who wants payment from escrow
                        $sql = $ilance->db->query("
                                SELECT user_id
                                FROM " . DB_PREFIX . "projects_escrow
                                WHERE project_id = '" . intval($projectid) . "'
                                    AND invoiceid = '" . intval($invoiceid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql);
                                $value = fetch_user('username', $res['user_id']);
                        }
                        else
                        {
                                $value = '--';
                        }
                }
                else if ($mode == 'product')
                {
                        // project_user_id = the merchant of this product auction
                        // user_id = the bidder/winner who must pay this escrow invoice
                        $sql = $ilance->db->query("
                                SELECT user_id FROM " . DB_PREFIX . "projects_escrow
                                WHERE project_id = '" . intval($projectid) . "'
                                    AND invoiceid = '" . intval($invoiceid) . "'
                                LIMIT 1"
                        , 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql);
                                $value = fetch_user('username', $res['user_id']);
                        }
                        else
                        {
                                $value = '--';
                        }
                }
                
                return $value;
        }
        
        /**
        * Function to fetch the escrow commission amount defined by the admin based on a custom commission argument.
        *
        * @param       string       commission type
        *
        * @return      string       calculated amount
        */
        function fetch_escrow_commission($ctype = '')
        {
                global $ilance, $ilconfig;
                
                if ($ilconfig['escrowsystem_escrowcommissionfees'])
                {
                        switch ($ctype)
                        {
                                case 'merchantbuynow':
                                {
                                        if ($ilconfig['escrowsystem_merchantfixedprice'] > 0)
                                        {
                                                return $ilconfig['escrowsystem_merchantfixedprice'];
                                        }
                                        else 
                                        {
                                                return $ilconfig['escrowsystem_merchantpercentrate'];
                                        }
                                        break;
                                }                            
                                case 'bidderbuynow':
                                {
                                        if ($ilconfig['escrowsystem_merchantfixedprice'] > 0)
                                        {
                                                return $ilconfig['escrowsystem_merchantfixedprice'];
                                        }
                                        else 
                                        {
                                                return $ilconfig['escrowsystem_merchantpercentrate'];
                                        }
                                        break;
                                }                            
                                case 'servicebuyer':
                                {
                                        if ($ilconfig['escrowsystem_servicebuyerfixedprice'] > 0)
                                        {
                                                return $ilconfig['escrowsystem_servicebuyerfixedprice'];
                                        }
                                        else 
                                        {
                                                return $ilconfig['escrowsystem_servicebuyerpercentrate'];
                                        }
                                        break;
                                }                            
                                case 'serviceprovider':
                                {
                                        if ($ilconfig['escrowsystem_providerfixedprice'] > 0)
                                        {
                                                return $ilconfig['escrowsystem_providerfixedprice'];
                                        }
                                        else 
                                        {
                                                return $ilconfig['escrowsystem_providerpercentrate'];
                                        }
                                        break;
                                }                            
                                case 'productmerchant':
                                {
                                        if ($ilconfig['escrowsystem_merchantfixedprice'] > 0)
                                        {
                                                return $ilconfig['escrowsystem_merchantfixedprice'];
                                        }
                                        else 
                                        {
                                                return $ilconfig['escrowsystem_merchantpercentrate'];
                                        }
                                        break;
                                }                            
                                case 'productbidder':
                                {
                                        if ($ilconfig['escrowsystem_bidderfixedprice'] > 0)
                                        {
                                                return $ilconfig['escrowsystem_bidderfixedprice'];
                                        }
                                        else 
                                        {
                                                return $ilconfig['escrowsystem_bidderpercentrate'];
                                        }
                                        break;
                                }
                        }
                }
                
                return 0;
        }
        
        /**
        * Function to fetch the escrow commission logic defined by the admin based on a custom commission argument.
        *
        * @param       string       commission type
        *
        * @return      string       returns a string (fixed/percentage)
        */
        function fetch_escrow_commission_logic($ctype = '')
        {
                global $ilconfig;
                
                if ($ilconfig['escrowsystem_escrowcommissionfees'])
                {
                        if (isset($ctype) AND !empty($ctype))
                        {
                                switch ($ctype)
                                {
                                        case 'merchantbuynow':
                                        {
                                                if ($ilconfig['escrowsystem_merchantfixedprice'] > 0)
                                                {
                                                        return 'fixed';
                                                }
                                                else 
                                                {
                                                        return 'percentage';
                                                }
                                                break;
                                        }
                                        case 'bidderbuynow':
                                        {
                                                if ($ilconfig['escrowsystem_merchantfixedprice'] > 0)
                                                {
                                                        return 'fixed';
                                                }
                                                else 
                                                {
                                                        return 'percentage';
                                                }
                                                break;
                                        }                                    
                                        case 'servicebuyer':
                                        {
                                                if ($ilconfig['escrowsystem_servicebuyerfixedprice'] > 0)
                                                {
                                                        return 'fixed';
                                                }
                                                else 
                                                {
                                                        return 'percentage';
                                                }
                                                break;
                                        }                                    
                                        case 'serviceprovider':
                                        {
                                                if ($ilconfig['escrowsystem_providerfixedprice'] > 0)
                                                {
                                                        return 'fixed';
                                                }
                                                else 
                                                {
                                                        return 'percentage';
                                                }
                                                break;
                                        }                                    
                                        case 'productmerchant':
                                        {
                                                if ($ilconfig['escrowsystem_merchantfixedprice'] > 0)
                                                {
                                                        return 'fixed';
                                                }
                                                else 
                                                {
                                                        return 'percentage';
                                                }
                                                break;
                                        }                                    
                                        case 'productbidder':
                                        {
                                                if ($ilconfig['escrowsystem_bidderfixedprice'] > 0)
                                                {
                                                        return 'fixed';
                                                }
                                                else 
                                                {
                                                        return 'percentage';
                                                }
                                                break;
                                        }
                                }
                        }
                }
        }
        
        /**
        * Function to find all escrow related invoices and check the escrow table for a match if we do not have a match,
        * chances are the escrow account was not created or the admin removed the invoiceid # in the escrow table tied to the
        * auction .. so in these situations we'll set that invoice to 'cancelled'
        */
        function cancel_unlinked_escrow_invoices()
        {
                global $ilance, $phrase, $ilconfig, $ilpage;
                
                $cronlog = '';
                
                if ($ilconfig['escrowsystem_enabled'])
                {
                        $sql = $ilance->db->query("
                                SELECT projectid, invoiceid
                                FROM " . DB_PREFIX . "invoices
                                WHERE invoicetype = 'escrow'
                                    AND status = 'unpaid'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $sent = 0;
                                while ($resms = $ilance->db->fetch_array($sql, DB_ASSOC))
                                {
                                        $sqlec = $ilance->db->query("
                                                SELECT escrow_id
                                                FROM " . DB_PREFIX . "projects_escrow
                                                WHERE project_id = '" . $resms['projectid'] . "'
                                                    AND invoiceid = '" . $resms['invoiceid'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sqlec) == 0)
                                        {
                                                $sent++;
                                                // cannot find escrow tied to invoice
                                                // cancel this invoice as we're not sure what happened!?!
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "invoices
                                                        SET status = 'cancelled',
                                                        custommessage = '" . $ilance->db->escape_string('{_transaction_cancelled_due_to_unlinked_and_unpaid_escrow_account_for_this_specific_invoice}') . "'
                                                        WHERE invoiceid = '" . $resms['invoiceid'] . "'
                                                ", 0, null, __FILE__, __LINE__);
                                        }
                                }
                                $cronlog .= 'Removed ' . $sent . ' unlinked/unpaid escrow transactions (reason: auction not found), ';
                                unset($sent);
                        }
                    
                        // we'll use the same logic for unlinked escrows as above for buynow escrow orders below
                        $sql = $ilance->db->query("
                                SELECT projectid, invoiceid
                                FROM " . DB_PREFIX . "invoices
                                WHERE invoicetype = 'buynow'
                                        AND status = 'unpaid'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $sent = 0;
                                while ($resin = $ilance->db->fetch_array($sql, DB_ASSOC))
                                {
                                        $sqlbn = $ilance->db->query("
                                                SELECT orderid
                                                FROM " . DB_PREFIX . "buynow_orders
                                                WHERE project_id = '" . $resin['projectid'] . "'
                                                        AND invoiceid = '" . $resin['invoiceid'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sqlbn) == 0)
                                        {
                                                $sent++;
                                                // cannot find buynow escrow tied to invoice
                                                // cancel this invoice as we're not sure what happened!?!
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "invoices
                                                        SET status = 'cancelled',
                                                        custommessage = '" . $ilance->db->escape_string('{_transaction_cancelled_due_to_unlinked_and_unpaid_escrow_account_for_this_specific_invoice}') . "'
                                                        WHERE invoiceid = '" . $resin['invoiceid'] . "'
                                                ", 0, null, __FILE__, __LINE__);
                                        }
                                }
                                $cronlog .= 'Removed ' . $sent . ' unlinked/unpaid buy now escrow transactions (reason: auction not found), ';
                                unset($sent);
                        }
                }
                return $cronlog;
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>