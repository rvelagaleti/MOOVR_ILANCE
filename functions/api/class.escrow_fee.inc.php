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
* Escrow Fee class to perform the majority of escrow and related payment functions in ILance
*
* @package      iLance\Escrow\Fee
* @version      4.0.0.8059
* @author       ILance
*/
class escrow_fee extends escrow
{

    /**
    * Function to print escrow fees (escrow commission fees must be enabled)
    *
    * @param       string         mode (as_service_provider, as_service_buyer, as_merchant_provider, as_merchant_buyer or as_admin)
    * @param       string         amount
    * @param       integer        listing id #
    *
    * @return      string         Returns formatted escrow fees amount (if applicable)
    */
    function print_escrow_fees($mode = '', $amount = 0, $pid = 0)
    {
	global $ilance, $ilconfig;

	$currencyid = fetch_auction('currencyid', $pid);

	if ($ilconfig['escrowsystem_escrowcommissionfees'])
	{
	    if (isset($mode) AND !empty($mode))
	    {
		switch ($mode)
		{
		    case 'as_service_provider';
			{
			    if ($ilconfig['escrowsystem_providerfixedprice'] > 0 OR $ilconfig['escrowsystem_providerpercentrate'] > 0)
			    {
				$html = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $amount, $currencyid);
			    }
			    else
			    {
				$html = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], 0, $currencyid);
			    }
			    break;
			}
		    case 'as_service_buyer';
			{
			    if ($ilconfig['escrowsystem_servicebuyerfixedprice'] > 0 OR $ilconfig['escrowsystem_servicebuyerpercentrate'] > 0)
			    {
				$html = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $amount, $currencyid);
			    }
			    else
			    {
				$html = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], 0, $currencyid);
			    }
			    break;
			}
		    case 'as_merchant_provider';
			{
			    if ($ilconfig['escrowsystem_merchantfixedprice'] > 0 OR $ilconfig['escrowsystem_merchantpercentrate'] > 0)
			    {
				$html = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $amount, $currencyid);
			    }
			    else
			    {
				$html = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], 0, $currencyid);
			    }
			    break;
			}
		    case 'as_merchant_buyer';
			{
			    if ($ilconfig['escrowsystem_bidderfixedprice'] > 0 OR $ilconfig['escrowsystem_bidderpercentrate'] > 0)
			    {
				$html = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $amount, $currencyid);
			    }
			    else
			    {
				$html = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], 0, $currencyid);
			    }
			    break;
			}
		    case 'as_admin';
			{
			    $html = $ilance->currency->format($amount, $currencyid);
			    break;
			}
		}

		return $html;
	    }
	}
	else
	{
	    return print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], 0, $currencyid);
	}
    }

    /**
    * Function to calculate the escrow fee based on an amount and a particular logic type
    *
    * @param       string         amount
    * @param       string         logic type (merchantbuynow, bidderbuynow, servicebuyer, serviceprovider, productmerchant or productbidder)
    *
    * @return      string         Returns formatted final value feee (if applicable)
    */
    function calculate_escrow_fee($amount = 0, $logictype = '')
    {
	global $ilance, $ilconfig;
	$fee = 0;
	if ($ilconfig['escrowsystem_escrowcommissionfees'] AND $logictype != '')
	{
	    $logic = $ilance->escrow->fetch_escrow_commission_logic($logictype);
	    if ($logic == 'fixed')
	    {
		// fixed service commission logic
		$fee = $ilance->escrow->fetch_escrow_commission($logictype);
	    }
	    else
	    {
		// percentage of the overall cost logic
		$fee = ($amount * $ilance->escrow->fetch_escrow_commission($logictype) / 100);
	    }
	    $fee = $ilance->currency->string_to_number($fee);
	}
	return $fee;
    }

    /**
    * Function to calculate the escrow fee based on an amount and a particular logic type
    *
    * @param       string         fee type (fvf, ins or esc)
    * @param       string         amount
    * @param       string         category type
    * @param       integer        category id
    * @param       string         bid amount type
    * @param       integer        user id
    *
    * @return      string         Returns formatted final value feee (if applicable)
    */
    function fetch_calculated_amount($feetype = 'fvf', $amount = 0, $cattype = '', $cid = 0, $bidamounttype = '', $userid = 0)
    {
	global $ilance;
	if (!isset($bidamounttype) OR empty($bidamounttype))
	{
	    $bidamounttype = '';
	}
	$value = 0;

	($apihook = $ilance->api('fetch_calculated_amount_start')) ? eval($apihook) : false;

	switch ($feetype)
	{
	    case 'fvf':
		{
		    $value = $ilance->accounting_fees->calculate_final_value_fee($amount, $cid, $cattype, $bidamounttype, $userid);
		    break;
		}
	    case 'ins':
		{
		    // to be added
		    break;
		}
	    case 'esc':
		{
		    $value = $this->calculate_escrow_fee($amount, $cattype);
		    break;
		}
	}

	($apihook = $ilance->api('fetch_calculated_amount_end')) ? eval($apihook) : false;

	return $value;
    }

    /**
    * Function to determine the escrow fee (including tax if any) for a seller based on a userid and amount
    *
    * @param       integer        user id
    * @param       integer        amount
    *
    * @return      string         Returns formatted fee including applicable taxes
    */
    function fetch_merchant_escrow_fee_plus_tax($userid = 0, $amount = 0)
    {
	global $ilance, $ilconfig;
	$taxamount = $total_fee = $fee = 0;
	if ($ilconfig['escrowsystem_escrowcommissionfees'])
	{
	    
	    // escrow commission fees to auction owner enabled
	    if ($ilconfig['escrowsystem_merchantfixedprice'] > 0)
	    {
		// fixed escrow cost to merchant
		$fee = $ilconfig['escrowsystem_merchantfixedprice'];
	    }
	    else
	    {
		if ($ilconfig['escrowsystem_merchantpercentrate'] > 0)
		{
		    // percentage rate of total winning bid amount
		    // which would be the same as the amount being forwarded into escrow
		    $fee = ($amount * $ilconfig['escrowsystem_merchantpercentrate'] / 100);
		}
	    }
	    if ($fee > 0)
	    {
		if ($ilance->tax->is_taxable(intval($userid), 'commission'))
		{
		    // fetch tax amount to charge for this invoice type
		    $taxamount = $ilance->tax->fetch_amount(intval($userid), $fee, 'commission', 0);
		}
		// exact amount to charge merchant
		$total_fee = ($fee + $taxamount);
	    }
	}
	return array ($fee, $taxamount, $total_fee);
    }

    /**
    * Function to determine the escrow fee (including tax if any) for a product buyer based on a userid and amount
    *
    * @param       integer        user id
    * @param       integer        amount
    *
    * @return      string         Returns formatted fee including applicable taxes
    */
    function fetch_product_bidder_escrow_fee_plus_tax($userid = 0, $amount = 0)
    {
	global $ilance, $ilconfig;
	$taxamount = $total_fee = $fee = 0;
	if ($ilconfig['escrowsystem_escrowcommissionfees'])
	{
	    
	    // escrow commission fees to auction owner enabled
	    if ($ilconfig['escrowsystem_bidderfixedprice'] > 0)
	    {
		// fixed escrow cost to provider for release of funds
		$fee = $ilconfig['escrowsystem_bidderfixedprice'];
	    }
	    else
	    {
		if ($ilconfig['escrowsystem_bidderpercentrate'] > 0)
		{
		    // percentage rate of total winning bid amount
		    // which would be the same as the amount being forwarded into escrow
		    $fee = ($amount * $ilconfig['escrowsystem_bidderpercentrate'] / 100);
		}
	    }
	    if ($fee > 0)
	    {
		if ($ilance->tax->is_taxable(intval($userid), 'commission'))
		{
		    // fetch tax amount to charge for this invoice type
		    $taxamount = $ilance->tax->fetch_amount(intval($userid), $fee, 'commission', 0);
		}
		// exact amount to charge provider for release of funds
		$total_fee = ($fee + $taxamount);
	    }
	}
	return array ($fee, $taxamount, $total_fee);
    }

    /**
    * Function to determine the escrow fee amount for a seller based on a particular amount
    *
    * @param       integer        amount
    *
    * @return      string         Returns formatted fee amount
    */
    function fetch_merchant_escrow_fee($amount = 0)
    {
	global $ilance, $ilconfig;
	$fee = 0;
	if ($ilconfig['escrowsystem_escrowcommissionfees'])
	{
	    // escrow commission fees to auction owner enabled
	    if ($ilconfig['escrowsystem_merchantfixedprice'] > 0)
	    {
		// fixed escrow cost to merchant
		$fee = $ilconfig['escrowsystem_merchantfixedprice'];
	    }
	    else
	    {
		if ($ilconfig['escrowsystem_merchantpercentrate'] > 0)
		{
		    // percentage rate of total winning bid amount
		    // which would be the same as the amount being forwarded into escrow
		    $fee = ($amount * $ilconfig['escrowsystem_merchantpercentrate'] / 100);
		}
	    }
	}
	return sprintf("%01.2f", $fee);
    }

    /**
    * Function to determine the escrow fee amount for a product buyer based on a particular userid and amount
    *
    * @param       integer        user id
    * @param       integer        amount
    *
    * @return      string         Returns formatted fee amount
    */
    function fetch_product_bidder_escrow_fee($userid = 0, $amount = 0)
    {
	global $ilance, $ilconfig;
	$fee = 0;
	if ($ilconfig['escrowsystem_escrowcommissionfees'])
	{
	    // escrow commission fees to auction owner enabled
	    if ($ilconfig['escrowsystem_bidderfixedprice'] > 0)
	    {
		// fixed escrow cost to provider for release of funds
		$fee = $ilconfig['escrowsystem_bidderfixedprice'];
	    }
	    else
	    {
		if ($ilconfig['escrowsystem_bidderpercentrate'] > 0)
		{
		    // percentage rate of total winning bid amount
		    // which would be the same as the amount being forwarded into escrow
		    $fee = ($amount * $ilconfig['escrowsystem_bidderpercentrate'] / 100);
		}
	    }
	}
	return sprintf("%01.2f", $fee);
    }

    /**
    * Function to determine the escrow fee amount for a service buyer based on a particular amount
    *
    * @param       integer        amount
    *
    * @return      string         Returns formatted fee amount
    */
    function fetch_service_buyer_escrow_fee($amount = 0)
    {
	global $ilance, $ilconfig;
	$fee = 0;
	if ($ilconfig['escrowsystem_escrowcommissionfees'])
	{
	    // escrow commission fees to auction owner enabled
	    if ($ilconfig['escrowsystem_servicebuyerfixedprice'] > 0)
	    {
		// fixed escrow cost to merchant
		$fee = $ilconfig['escrowsystem_servicebuyerfixedprice'];
	    }
	    else
	    {
		if ($ilconfig['escrowsystem_servicebuyerpercentrate'] > 0)
		{
		    // percentage rate of total winning bid amount
		    // which would be the same as the amount being forwarded into escrow
		    $fee = ($amount * $ilconfig['escrowsystem_servicebuyerpercentrate'] / 100);
		}
	    }
	}
	return sprintf("%01.2f", $fee);
    }

    /**
    * Function for fetching the total escrow fee based on a particular amount.
    *
    * @param       string         amount
    *
    * @return      string         escrow fee amount
    */
    function fetch_provider_escrow_fee($amount = 0)
    {
	global $ilance, $ilconfig;
	$fee = 0;
	if ($ilconfig['escrowsystem_escrowcommissionfees'])
	{
	    if ($ilconfig['escrowsystem_providerfixedprice'] > 0)
	    {
		$fee = $ilconfig['escrowsystem_providerfixedprice'];
	    }
	    else
	    {
		if ($ilconfig['escrowsystem_providerpercentrate'] > 0)
		{
		    $fee = ($amount * $ilconfig['escrowsystem_providerpercentrate'] / 100);
		}
	    }
	}
	return sprintf("%01.2f", $fee);
    }

    /**
    * Function for fetching the total escrow fee (plus applicable taxes) based on a particular amount.
    *
    * @param       integer        user id
    * @param       string         amount
    *
    * @return      string         escrow fee amount
    */
    function fetch_provider_escrow_fee_plus_tax($userid = 0, $amount = 0)
    {
	global $ilance, $ilconfig;
	$fee = 0;
	if ($ilconfig['escrowsystem_escrowcommissionfees'])
	{
	    
	    if ($ilconfig['escrowsystem_providerfixedprice'] > 0)
	    {
		$fee = $ilconfig['escrowsystem_providerfixedprice'];
	    }
	    else
	    {
		if ($ilconfig['escrowsystem_providerpercentrate'] > 0)
		{
		    $fee = ($amount * $ilconfig['escrowsystem_providerpercentrate'] / 100);
		}
	    }
	    if ($fee > 0)
	    {
		$taxamount = 0;
		if ($ilance->tax->is_taxable(intval($userid), 'commission'))
		{
		    $taxamount = $ilance->tax->fetch_amount(intval($userid), $fee, 'commission', 0);
		}
		$fee = ($fee + $taxamount);
	    }
	}
	return sprintf("%01.2f", $fee);
    }

    /**
    * Function for fetching the escrow tax bit info within the buying and selling activity areas.
    *
    * @param       integer        user id
    * @param       string         amount
    * @param       integer        project id (to determine if escrow is enabled for auction)
    * @param       boolean        show the phrase (default true)
    *
    * @return      string         tax information bit
    */
    function fetch_escrow_taxinfo_bit($userid = 0, $amount = 0, $projectid = 0, $showphrase = true)
    {
	global $ilance, $ilconfig, $phrase;
	$taxinfo = '{_none}';
	$filter_escrow = fetch_auction('filter_escrow', intval($projectid));
	if ($ilconfig['escrowsystem_enabled'] AND $filter_escrow == '1' AND $amount > 0)
	{
	    if ($ilance->tax->is_taxable(intval($userid), 'commission'))
	    {
		$taxinfo = (($showphrase) ? '{_taxes_added_to_the_escrow_fee} ' : '') . $ilance->tax->fetch_amount(intval($userid), $amount, 'commission', 1);
	    }
	}
	return $taxinfo;
    }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>