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
* Core currency functions for iLance.
*
* @package      iLance\Global\Currency
* @version      4.0.0.8059
* @author       ILance
*/

/**
* Function to print the left currency symbol
*
* @return      string         Returns left currency symbol (US$, $, etc)
*/
function print_left_currency_symbol()
{
        global $ilance, $ilconfig;
        return $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_left'];
}

/**
* Function to print the currency conversion based on a supplied currency id
*
* @param       integer        viewing user's currency id
* @param       integer        dollar amount to process
* @param       integer        listing currency id
* @param       boolean        flip output
*
* @return      string         Returns the formatted amount based on a particular currency id
*/
function print_currency_conversion($currencyid = 0, $amount = 0, $currencyid_item = 0, $flipoutput = false)
{
        global $ilance, $ilconfig, $ilpage;
        $html = '';
        // #### default currency exchange rate for viewing user ################
	$default_rate = ($currencyid_item == 0)
		? $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['rate']
		: $ilance->currency->currencies[$currencyid_item]['rate'];
        // #### convert amount into something php can work with ################
        $amount = $ilance->currency->string_to_number($amount);
	// #### viewing user's currency rate ###################################
        $customer_rate = $default_rate;
        if ($currencyid > 0)
        {
                $customer_rate = $ilance->currency->currencies[$currencyid]['rate'];
        }
        $price_conversion_rate = floatval($amount * $customer_rate / $default_rate);
	$convert_currencyid = ($currencyid == 0)
		? $ilconfig['globalserverlocale_defaultcurrency']
		: $currencyid;
	$convert2_currencyid = ($currencyid_item == 0)
		? $ilconfig['globalserverlocale_defaultcurrency']
		: $currencyid_item;
	$converted1 = $ilance->currency->format($price_conversion_rate, $convert_currencyid, false, false, false);
	$converted2 = $ilance->currency->format($amount, $convert2_currencyid, false, false, false);
	if ($flipoutput)
	{
		$html = ($default_rate == $customer_rate)
			? $converted2
			: '<span title="' . $converted1 . ' {_is_roughly} ' . $converted2 . '">' . $converted1 . '</span>';
	}
	else
	{
		$html = ($default_rate == $customer_rate)
			? $converted1
			: '<span title="' . $converted2 . ' {_is_roughly} ' . $converted1 . '">' . $converted2 . '</span>';
	}
        return $html;
}

/**
* Function to print the currency conversion based on a pruction auction currency id with global currency 
*
* @param       integer        viewing user's currency id
* @param       integer        dollar amount to process
* @param       integer        listing currency id
*
* @return      string         Returns the formatted amount based on a particular currency id
*/
function print_currency_conversion_invoice($currencyid = 0, $amount = 0, $currencyid_item = 0)
{
        global $ilance, $ilconfig, $ilpage;
        $html = '';
        // #### default currency exchange rate for viewing user ################
	$default_rate = ($currencyid_item == 0)
		? $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['rate']
		: $ilance->currency->currencies[$currencyid_item]['rate'];
        // #### convert amount into something php can work with ################
        $amount = $ilance->currency->string_to_number($amount);
	// #### viewing user's currency rate ###################################
        $customer_rate = $default_rate;
        if ($currencyid > 0)
        {
                $customer_rate = $ilance->currency->currencies[$currencyid]['rate'];
        }
        $price_conversion_rate = ($amount * $customer_rate / $default_rate);
        $price_conversion_rate = sprintf("%01.2f", $price_conversion_rate);
	$convert_currencyid = ($currencyid == 0)
		? $ilconfig['globalserverlocale_defaultcurrency']
		: $currencyid;
	$convert2_currencyid = ($currencyid_item == 0)
		? $ilconfig['globalserverlocale_defaultcurrency']
		: $currencyid_item;
	$converted1 = $ilance->currency->format($price_conversion_rate, $convert_currencyid, false, false, false);
	$converted2 = $ilance->currency->format($amount, $convert2_currencyid, false, false, false);
        //echo "$currencyid, $amount, $currencyid_item<br />";
	//echo "$default_rate, $amount, $customer_rate<br />";
	//echo "$converted1, $amount, $converted2<br />";
        // if default site currency is same as users show 1 instance only of the conversion
	$html = ($default_rate == $customer_rate OR $amount <= 0)
		? $converted1
		: $converted1 . '<span style="text-decoration:none">&nbsp;&nbsp;<span class="smaller gray">(<span class="blueonly"><a href="' . HTTP_SERVER . $ilpage['accounting'] . '?cmd=currency-converter&amp;subcmd=process&amp;amount=' . sprintf("%01.2f", $amount) . '&amp;transfer_from=' . $currencyid_item . '&amp;transfer_to=' . $currencyid . '&amp;returnurl=' . urlencode(PAGEURL) . '">' . $converted2  . '</a></span>)</span>';
        
        return $html;
}

/**
* Function to convert currency
*
* @param       integer        viewing currency id
* @param       integer        amount to process
*
* @return      string         Returns the converted currency
*/
function convert_currency($currencyid = 0, $amount = 0, $currencyid_item = 0)
{
        global $ilance, $ilconfig, $ilpage;
	$default_rate = ($currencyid_item == 0) ? $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['rate'] : $ilance->currency->currencies[$currencyid_item]['rate'];
        $amount = $ilance->currency->string_to_number($amount);
        $customer_rate = $default_rate;
        if ($currencyid > 0)
        {
                $customer_rate = $ilance->currency->currencies[$currencyid]['rate'];
        }
        $price_conversion_rate = round(floatval($amount * $customer_rate / $default_rate), 2);
        return $price_conversion_rate;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>