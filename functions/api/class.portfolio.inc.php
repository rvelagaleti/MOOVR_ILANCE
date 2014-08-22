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
* Portfolio class to handle the majory of portfolio functions and operations in ILance
*
* @package      iLance\Portfolio
* @version      4.0.0.8059
* @author       ILance
*/
class portfolio
{
        /**
        * Function for processing a portfolio featured payment upgrade.
        * This function is also responsible for updating isportfoliofee = '1' upon a successful transaction.
        *
        * @param       integer      user id
        * @param       integer      portfolio id
        * @param       string       amount
        * @param       string       total amount to process
        *
        * @return      bool         returns true or false for successful account balance debit
        */
        function portfolio_process($userid = 0, $portfolioid = 0, $amount = 0, $total = 0)
        {
                global $ilance, $phrase, $page_title, $area_title, $ilconfig, $ilpage;
                if ($ilconfig['portfolioupsell_featuredactive'] AND $ilconfig['portfolioupsell_featuredfee'] > 0 AND $amount > 0 AND $total > 0)
                {
                        // #### select account balance #########################
                        $sql_balance = $ilance->db->query("
                            SELECT available_balance, total_balance
                            FROM " . DB_PREFIX . "users
                            WHERE user_id = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql_balance) > 0)
                        {
                                $res_balance = $ilance->db->fetch_array($sql_balance, DB_ASSOC);
                                
                                if ($res_balance['available_balance'] >= $total)
                                {
                                        $avail_balance = $res_balance['available_balance'];
                                        $total_balance = $res_balance['total_balance'];
                                        $avail_balance_after = ($avail_balance - $total);
                                        $total_balance_after = ($total_balance - $total);
                    
                                        // #### adjust online account ##########
                                        $ilance->db->query("
                                            UPDATE " . DB_PREFIX . "users
                                            SET available_balance = '" . $avail_balance_after . "',
                                            total_balance = '" . $total_balance_after . "'
                                            WHERE user_id = '" . intval($userid) . "'
                                        ", 0, null, __FILE__, __LINE__);
                    
                                        $transactionid = $ilance->accounting_payment->construct_transaction_id();
                    
                                        // track income spent
                                        $ilance->accounting_payment->insert_income_spent(intval($userid), sprintf("%01.2f", $total), 'credit');
                                        
                                        // referral tracker
                                        $ilance->referral->update_referral_action('portfolio', intval($userid));
                                        
                                        // create transaction
                                        $invoiceid = $ilance->accounting->insert_transaction(
                                                0,
                                                0,
                                                0,
                                                intval($userid),
                                                0,
                                                0,
                                                0,
                                                $ilconfig['portfolioupsell_featureditemname'] . ': ' . stripslashes($ilance->db->fetch_field(DB_PREFIX . "attachment", "portfolio_id = '" . intval($portfolioid) . "'", "filename")),
                                                sprintf("%01.2f", $amount),
                                                sprintf("%01.2f", $total),
                                                'paid',
                                                'portfolio',
                                                'account',
                                                DATETIME24H,
                                                DATEINVOICEDUE,
                                                DATETIME24H,
                                                '',
                                                0,
                                                0,
                                                1,
                                                $transactionid
                                        );
                                        
                                        // #### update the invoice transaction so we can make use of better fee reporting in the admincp
                                        $ilance->db->query("
                                            UPDATE " . DB_PREFIX . "invoices
                                            SET isportfoliofee = '1'
                                            WHERE invoiceid = '" . intval($invoiceid) . "'
                                        ", 0, null, __FILE__, __LINE__);
                    
                                        // #### feature the portfolio item #####
                                        $ilance->db->query("
                                            UPDATE " . DB_PREFIX . "portfolio 
                                            SET featured = '1',
                                            featured_date = '" . DATETIME24H . "',
                                            featured_invoiceid = '" . intval($invoiceid) . "'
                                            WHERE user_id = '" . intval($userid) . "'
                                                AND portfolio_id = '" . intval($portfolioid) . "'
                                                AND visible = '1'
                                        ", 0, null, __FILE__, __LINE__);
                                        
                                        // #### associate this invoice to the attachment table for extra info when required
                                        $ilance->db->query("
                                            UPDATE " . DB_PREFIX . "attachment 
                                            SET invoiceid = '" . intval($invoiceid) . "'
                                            WHERE user_id = '" . intval($userid) . "'
                                                AND portfolio_id = '" . intval($portfolioid) . "'
                                                AND visible = '1'
                                                AND attachtype = 'portfolio'
                                        ", 0, null, __FILE__, __LINE__);
                                        
                                        $ilance->email->mail = SITE_EMAIL;
                                        $ilance->email->slng = fetch_site_slng();
                                        $ilance->email->get('featured_portfolio_payment_admin');		
                                        $ilance->email->set(array(
                                                '{{portfolioid}}' => $portfolioid,
                                                '{{invoiceid}}' => intval($invoiceid),
                                                '{{amount}}' => $ilance->currency->format($total),
                                                '{{transactionid}}' => $transactionid,
                                                '{{customer}}' => $_SESSION['ilancedata']['user']['username'],
                                        ));
                                        $ilance->email->send();
                    
                                        $ilance->email->mail = $_SESSION['ilancedata']['user']['email'];
                                        $ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
                                        $ilance->email->get('featured_portfolio_payment');		
                                        $ilance->email->set(array(
                                                '{{portfolioid}}' => $portfolioid,
                                                '{{invoiceid}}' => intval($invoiceid),
                                                '{{amount}}' => $ilance->currency->format($total),
                                                '{{transactionid}}' => $transactionid,
                                                '{{customer}}' => $_SESSION['ilancedata']['user']['username'],
                                        ));
                                        $ilance->email->send();
                                        
                                        return 1;
                                }
                        }
                }
                else if ($ilconfig['portfolioupsell_featuredactive'] AND ($ilconfig['portfolioupsell_featuredfee'] == '0' OR $ilconfig['portfolioupsell_featuredfee'] == '0.00' OR empty($ilconfig['portfolioupsell_featuredfee'])))
                {
                        // no fees so feature the portfolio
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "portfolio 
                                SET featured = '1',
                                featured_date = '" . DATETIME24H . "',
                                featured_invoiceid = '0'
                                WHERE user_id = '" . intval($userid) . "'
                                    AND portfolio_id = '" . intval($portfolioid) . "'
                                    AND visible = '1'
                        ", 0, null, __FILE__, __LINE__);
            
                        $ilance->email->mail = SITE_EMAIL;
                        $ilance->email->slng = fetch_site_slng();
                        $ilance->email->get('featured_portfolio_payment_admin');		
                        $ilance->email->set(array(
                                '{{portfolioid}}' => $portfolioid,
                                '{{invoiceid}}' => '--',
                                '{{transactionid}}' => '--',
                                '{{customer}}' => $_SESSION['ilancedata']['user']['username'],
                                '{{amount}}' => $ilance->currency->format($total),
                        ));
                        $ilance->email->send();
                        
                        $ilance->email->mail = $_SESSION['ilancedata']['user']['email'];
                        $ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
                        $ilance->email->get('featured_portfolio_payment');		
                        $ilance->email->set(array(
                                '{{portfolioid}}' => $portfolioid,
                                '{{invoiceid}}' => '--',
                                '{{transactionid}}' => '--',
                                '{{customer}}' => $_SESSION['ilancedata']['user']['username'],
                                '{{amount}}' => $ilance->currency->format($total),
                        ));
                        $ilance->email->send();
                        
                        return 1;
                }
                
                return 0;
        }
        
        /*
	* Function to fetch the portfolio menu statistics
	*
	* @param      string       stats mode (countcats, countitems, countviews, diskspace)
	*
	* @return     string       Returns formatted statistic
	*/
        function fetch_stats($mode = '')
        {
                global $ilance;
                
                if ($mode == 'countcats')
                {
                        $pcats = $ilance->db->query("SELECT category_id FROM " . DB_PREFIX . "attachment WHERE attachtype = 'portfolio' AND visible='1' GROUP BY category_id");
                        $rcats = $ilance->db->num_rows($pcats);
                        return number_format($rcats);
                }
                else if ($mode == 'countitems')
                {
                        $pitems = $ilance->db->query("SELECT COUNT(*) AS count FROM " . DB_PREFIX . "attachment WHERE attachtype = 'portfolio' AND visible='1'");
                        $ritems = $ilance->db->fetch_array($pitems, DB_ASSOC);
                        return number_format($ritems['count']);
                }
                else if ($mode == 'countviews')
                {
                        $pviews = $ilance->db->query("SELECT SUM(counter) AS count FROM " . DB_PREFIX . "attachment WHERE attachtype = 'portfolio' AND visible='1'");
                        $rviews = $ilance->db->fetch_array($pviews, DB_ASSOC);
                        return number_format($rviews['count']);
                }
                else if ($mode == 'diskspace')
                {
                        $pspace = $ilance->db->query("SELECT SUM(filesize) AS count FROM " . DB_PREFIX . "attachment WHERE attachtype = 'portfolio' AND visible='1'");
                        $rspace = $ilance->db->fetch_array($pspace, DB_ASSOC);
                        return print_filesize($rspace['count']);
                }
        }
        
        /*
	* Function to print recently featured users that have paid to feature their portfolio
	*
	* @param      integer     category id
	* @param      integer     days ago range (default 30)
	* 
	* @return     string      Returns formatted block of users who recently upgraded their portfolio items
	*/
        function print_recently_featured_users($cid = '', $days = 30)
        {
                global $ilance, $phrase, $ilpage, $ilconfig;
                
                $html = '';
                $where = "p.category_id > 0";
                if (isset($cid) AND $cid > 0)
                {
                        $where = "p.category_id = '" . intval($cid) . "'";
                }
                
                $sql = $ilance->db->query("
                        SELECT p.user_id, a.counter
                        FROM " . DB_PREFIX . "portfolio p
                        LEFT JOIN " . DB_PREFIX . "attachment a ON (p.portfolio_id = a.portfolio_id)
                        WHERE " . $where . "
                        		AND p.featured = '1'
                                AND p.visible = '1'
                                AND p.featured_date >= DATE_SUB('" . DATETIME24H . "', INTERVAL " . $days . " DAY)
                        GROUP BY p.user_id 
                        ORDER BY a.counter DESC
                        LIMIT 5
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                        		$sql_p = $ilance->db->query("
				                        SELECT p.user_id
				                        FROM " . DB_PREFIX . "portfolio p
				                        LEFT JOIN " . DB_PREFIX . "attachment a ON (p.portfolio_id = a.portfolio_id)
				                        WHERE " . $where . "
				                        		AND p.user_id = '" . $res['user_id'] . "'
				                        		AND p.featured = '1'
				                                AND p.visible = '1'
				                                AND p.featured_date >= DATE_SUB('" . DATETIME24H . "', INTERVAL " . $days . " DAY)
				                ", 0, null, __FILE__, __LINE__);
                        		$num = $ilance->db->num_rows($sql_p);
                        		
                                $html .= '<div style="padding-bottom:5px">' . fetch_user('username', $res['user_id']) . ' ' . '{_portfolio_lowercase}' . '</div> <div class="smaller blue" style="padding-top:3px">(<span class="blue"><a href="' . HTTP_SERVER . $ilpage['portfolio'] . '?id=' . $res['user_id'] . '">' . $num  . ' ' . '{_featured_lowercase}' . '</a></span>)<div class="smaller blue" style="padding-top:3px"><a href="' . HTTP_SERVER . $ilpage['rfp'] . '?cmd=rfp-invitation&amp;id=' . $res['user_id'] . '">' . '{_invite_to_bid}' . '</a></div></div>';
                        }
                }
                else
                {
                        $html = '<div style="padding-bottom:5px">{_no_recently_featured_portfolios}</div>';
                }
                
                return $html;
        }
        
        /**
        * Function to expire any verified profile credential accounts if they are due (pased from cron.reminders.php)
        */
        function expire_verified_profile_credentials()
        {
                global $ilance, $ilconfig, $phrase;
                
                $cronlog = '';
                $verification = $ilance->db->query("
                        SELECT answerid, verifyexpiry 
                        FROM " . DB_PREFIX . "profile_answers
                        WHERE answer != ''
                            AND visible = '1'
                            AND isverified = '1'
                            AND verifyexpiry != '0000-00-00 00:00:00'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($verification) > 0)
                {
                        while ($verifications = $ilance->db->fetch_array($verification, DB_ASSOC))	
                        {
                                $date1split = explode(' ', $verifications['verifyexpiry']);
                                $date2split = explode('-', $date1split[0]);
                                $daystoexpire = $ilance->datetimes->fetch_days_between(date('m'), date('d'), date('Y'), $date2split[1], $date2split[2], $date2split[0]);
                                if ($daystoexpire <= 0)
                                {
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "profile_answers
                                                SET isverified = '0',
                                                verifyexpiry = '0000-00-00 00:00:00',
                                                invoiceid = '0'
                                                WHERE answerid = '" . $verifications['answerid'] . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                }
                        }
                }
                return $cronlog;
        }
        
        /*
	* Function to expire featured portfolios (parsed from cron.reminders.php)
	*/
        function expire_featured_portfolios()
        {
                global $ilance, $ilconfig, $phrase;
                
                $cronlog = '';
                $portfolio = $ilance->db->query("
                        SELECT portfolio_id, user_id, caption, description, category_id, featured, featured_date, featured_invoiceid, visible
                        FROM " . DB_PREFIX . "portfolio
                        WHERE featured = '1'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($portfolio) > 0)
                {
                        while ($portfolios = $ilance->db->fetch_array($portfolio, DB_ASSOC))	
                        {
                                $date1split = explode(' ', $portfolios['featured_date']);
                                $date2split = explode('-', $date1split[0]);
                                $totaldays = $ilconfig['portfolioupsell_featuredlength'];
                                $elapsed = $ilance->datetimes->fetch_days_between($date2split[1], $date2split[2], $date2split[0], date('m'), date('d'), date('Y'));
                                $days = ($totaldays - $elapsed);
                                if ($days < 0)
                                {
                                        // update this portfolio to non-featured
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "portfolio
                                                SET featured = '0'
                                                WHERE portfolio_id = '" . $portfolios['portfolio_id'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                }
                        }
                }
                return $cronlog;
        }
	
	/**
	* Function to determine if a user has a portfolio setup
	*
	* @param        integer     user id
	*
	* @return	bool        Returns true or false
	*/
	function has_portfolio($userid = 0)
	{
		global $ilance, $ilconfig;
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "u.username
			FROM " . DB_PREFIX . "portfolio as p,
			" . DB_PREFIX . "attachment as a,
			" . DB_PREFIX . "users as u,
			" . DB_PREFIX . "subscription_user as su
			WHERE p.user_id = '" . intval($userid) . "'
			    AND a.user_id = '" . intval($userid) . "'
			    AND u.user_id = '" . intval($userid) . "'
			    AND su.user_id = '" . intval($userid) . "'
			    AND su.active = 'yes'
			    AND p.portfolio_id = a.portfolio_id
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			return true;
		}
		return false;
	}
	
	
	/**
	 * Function to fetch the number of portfolio items within a particular category
	 *
	 * @param       integer      category id
	 *
	 * @return      integer      Returns xxx
	 */
	function fetch_portfolio_count($cid = 0)
	{
	    global $ilance;
	    $count = 0;
	    $items = $ilance->db->query("
		SELECT COUNT(*) AS count
		FROM " . DB_PREFIX . "attachment
		WHERE category_id = '" . intval($cid) . "'
		    AND attachtype = 'portfolio'
		    AND visible = '1'
	    ", 0, null, __FILE__, __LINE__);
	    if ($ilance->db->num_rows($items) > 0)
	    {
		$item = $ilance->db->fetch_array($items, DB_ASSOC);
		$count = intval($item['count']);
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