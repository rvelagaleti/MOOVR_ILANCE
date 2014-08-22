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
if (!isset($GLOBALS['ilance']->db))
{
	die('<strong>Warning:</strong> This script cannot be loaded indirectly.  Operation aborted.');
}
$ilance->timer->start();

($apihook = $ilance->api('cron_currency_start')) ? eval($apihook) : false;

$searchfor = '<Cube currency';
if (($fcontents = @file($ilconfig['globalserverlocale_defaultcurrencyxml'])))
{
        $i = 0;
        foreach ($fcontents AS $line)
        {
                if ($sp = mb_strpos($line, $searchfor))
                { 
                        $xmlarray = explode("'", $line);
                        $xmlabbrev = trim($xmlarray[3]);
                        $xmlrate[$i]['abbv'] = mb_strtoupper(trim($xmlarray[1]));
                        $xmlrate[$i]['rate'] = $xmlabbrev;                        
                        $i++;
                }
        }
        $rates = '';
        for ($x = 0; $x < $i; $x++)
        {
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "currency
                        SET rate = '" . $ilance->db->escape_string($xmlrate[$x]['rate']) . "',
                        time = '" . DATETIME24H . "' 
                        WHERE currency_abbrev = '" . $ilance->db->escape_string($xmlrate[$x]['abbv']) . "'
                ", 0, null, __FILE__, __LINE__);
                $rates .= $xmlrate[$x]['abbv'] . ' = ' . $xmlrate[$x]['rate'] . ', ';
        }
	// BTC - Bitcoin from blockchain
	$btc = @file_get_contents('http://blockchain.info/tobtc?currency=EUR&value=1');
	if (!empty($btc))
	{
		$rates .= 'BTC = ' . $btc . ', ';
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "currency
			SET rate = '" . $ilance->db->escape_string($btc) . "',
			time = '" . DATETIME24H . "'
			WHERE currency_abbrev = 'BTC'
		", 0, null, __FILE__, __LINE__);
	}
        $ilance->db->query("
                UPDATE " . DB_PREFIX . "currency
                SET rate = '1.0000',
                time = '" . DATETIME24H . "'
                WHERE currency_abbrev = 'EUR'
        ", 0, null, __FILE__, __LINE__);
        if (!empty($rates))
        {
                $rates = mb_substr($rates, 0, -2);
        }
	
        ($apihook = $ilance->api('cron_currency_end')) ? eval($apihook) : false;
        
        $ilance->timer->stop();
        log_cron_action('{_the_following_currency_rates_were_updated} ' . $rates, $nextitem, $ilance->timer->get());
}
else
{
	$ilance->timer->stop();
        log_cron_action('{_error_currency_rates_could_not_be_updated_could_not_execute_php_function_file} ', $nextitem, $ilance->timer->get());
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>