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
* Accounting class to perform the majority of accounting functions within ILance.
*
* @package      iLance\Accounting\Payment
* @version      4.0.0.8059
* @author       ILance
*/
class accounting_payment extends accounting
{
	/**
	* Function to handle all related invoice payments in ILance.
	*
	* @param        integer     invoice id
	* @param        string      invoice type
	* @param        integer     amount
	* @param        integer     user id
	* @param        string      payment method (default account)
	* @param        string      payment gateway (default blank)
	* @param        string      payment gateway return transaction id
	* @param        boolean     is payment refunded? (default false)
	* @param        string      original gateway transaction id (in the case of gateway refund status)
	* @param        boolean     silent mode (default false; when true it returns only true or false responses)
	*
	* @return	mixed       Returns true or false
	*/
	function invoice_payment_handler($invoiceid = 0, $invoicetype = 'debit', $amount = 0, $userid = 0, $paymethod = 'account', $gateway = '', $gatewaytxn = '', $isrefund = false, $originalgatewaytxn = '', $silentmode = false)
	{
		global $ilance, $ilconfig, $phrase, $ilpage, $area_title, $page_title, $show;
		$success = false;
		switch ($invoicetype)
		{
			case 'subscription':
			{
				if ($paymethod == 'account' OR $paymethod == 'ipn')
				{
					$success = $ilance->subscription->payment($userid, $invoiceid, $paymethod, $gateway, $gatewaytxn, $isrefund, $originalgatewaytxn, $silentmode);
				}
				break;
			}
			case 'escrow':
			{
				if ($ilconfig['escrowsystem_enabled'] AND ($paymethod == 'ipn' OR $paymethod == 'account'))
				{
					$success = $ilance->escrow_payment->payment($userid, $invoiceid, $invoicetype, $amount, $paymethod, $gateway, $gatewaytxn, $isrefund, $originalgatewaytxn, $silentmode);
				}
				break;
			}
			case 'commission':
			case 'debit':
			{
				if ($paymethod == 'account' OR $paymethod == 'ipn')
				{
					$success = $ilance->accounting->payment($userid, $invoiceid, $invoicetype, $amount, $paymethod, $gateway, $gatewaytxn, $isrefund, $originalgatewaytxn, $silentmode);
				}
				break;
			}
			case 'p2b':
			{
				// #### PAYMENT VIA ONLINE ACCOUNT #####################		
				if ($paymethod == 'account' OR $paymethod == 'ipn')
				{
					$success = $ilance->accounting_p2b->payment($userid, $invoiceid, $invoicetype, $amount, $paymethod, $gateway, $gatewaytxn, $isrefund, $originalgatewaytxn, $silentmode);
				}
				break;
			}
		}
	
		($apihook = $ilance->api('invoice_payment_handler_end')) ? eval($apihook) : false;
	
		return $success;
	}
    
	/**
	* Function to track income reported for a particular user for a certain amount based on a certain action.
	*
	* @param       integer        user id
	* @param       integer        amount to process
	* @param       string         action to perform (credit or debit)
	*
	* @return      nothing
	*/
	function insert_income_reported($userid = 0, $amount = 0, $action = '')
	{
		global $ilance;
		$amount = sprintf("%01.2f", $amount);
		if ($action == 'credit')
		{
			$ilance->db->query("
			    UPDATE " . DB_PREFIX . "users
			    SET income_reported = income_reported + $amount
			    WHERE user_id = '" . intval($userid) . "'
			", 0, null, __FILE__, __LINE__);
		}
		else if ($action == 'debit')
		{
			$ilance->db->query("
			    UPDATE " . DB_PREFIX . "users
			    SET income_reported = income_reported - $amount
			    WHERE user_id = '" . intval($userid) . "'
			", 0, null, __FILE__, __LINE__);
		}
	}
    
	/**
	* Function to track income spent for a particular user for a certain amount based on a certain action.
	*
	* @param       integer        user id
	* @param       integer        amount to process
	* @param       string         action to perform (credit or debit)
	*
	* @return      nothing
	*/
	function insert_income_spent($userid = 0, $amount = 0, $action = '')
	{
		global $ilance;
		$amount = sprintf("%01.2f", $amount);
		if ($action == 'credit')
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "users
				SET income_spent = income_spent + $amount
				WHERE user_id = '" . intval($userid) . "'
			", 0, null, __FILE__, __LINE__);
		}
		if ($action == 'debit')
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "users
				SET income_spent = income_spent - $amount
				WHERE user_id = '" . intval($userid) . "'
			", 0, null, __FILE__, __LINE__);
		}
	}
    
	/**
	* Function to create a unique transaction id used within the billing and payment system
	*
	* @return      string         Returns unique transaction id
	*/
	function construct_transaction_id()
	{
		global $ilance, $ilconfig;
		if ($ilconfig['invoicesystem_transactionidlength'] > 0)
		{
			$tid = '';
			for ($i = 1; $i <= $ilconfig['invoicesystem_transactionidlength']; $i++)
			{
				mt_srand((double) microtime() * 1000000);
				$num = mt_rand(1, 36);
				$tid .= $ilance->common->construct_random_value($num);
			}
		}
		return $tid;
	}
    
	/**
	* Function to determine if a transaction id being passed already exists within the invoice and billing system
	* 
	* @param       string         transaction id
	*
	* @return      boolean        Returns true or false
	*/
	function is_duplicate_txn_id($txn_id = '')
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT invoiceid
			FROM " . DB_PREFIX . "invoices
			WHERE custommessage = '" . $ilance->db->escape_string($txn_id) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			return true;
		}
		return false;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>