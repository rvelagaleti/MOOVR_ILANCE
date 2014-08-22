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
 * Currency class to perform the majority of currency related functions in ILance
 *
 * @package      iLance\Currency
 * @version      4.0.0.8059
 * @author       ILance
 */
class currency
{
	public $currencies;
	public $conversion = false;
	/**
	* Constructor
	*
	*/
	function __construct()
	{
		$this->init_currencies();
	}
    
	function init_currencies()
	{
		global $ilance;
		if (($this->currencies = $ilance->cachecore->fetch("currencies")) === false)
		{
			$this->currencies = array();
			$query = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "currency_id, currency_abbrev AS code, symbol_left, symbol_right, decimal_point, thousands_point, decimal_places, rate, currency_name, currency_abbrev
				FROM " . DB_PREFIX . "currency
			", 0, null, __FILE__, __LINE__);
			while ($currencies = $ilance->db->fetch_array($query, DB_ASSOC))
			{
				// generate string type values (ie: USD)
				$this->currencies[$currencies['code']] = array (
					'symbol_left' => $currencies['symbol_left'],
					'symbol_right' => $currencies['symbol_right'],
					'decimal_point' => $currencies['decimal_point'],
					'thousands_point' => $currencies['thousands_point'],
					'decimal_places' => $currencies['decimal_places'],
					'rate' => $currencies['rate'],
					'currency_id' => $currencies['currency_id'],
					'currency_name' => $currencies['currency_name'],
					'currency_abbrev' => $currencies['currency_abbrev']
				);
				// generate integer type values (ie: 1)
				$this->currencies[$currencies['currency_id']] = array (
					'symbol_left' => $currencies['symbol_left'],
					'symbol_right' => $currencies['symbol_right'],
					'decimal_point' => $currencies['decimal_point'],
					'thousands_point' => $currencies['thousands_point'],
					'decimal_places' => $currencies['decimal_places'],
					'rate' => $currencies['rate'],
					'code' => $currencies['code'],
					'currency_name' => $currencies['currency_name'],
					'currency_abbrev' => $currencies['currency_abbrev']
				);
			}
			$ilance->cachecore->store("currencies", $this->currencies);
		}
	}
    
	/**
	* Function to mimik number_format() to ensure the output is based on the viewing users thousands point, decimal places and decimal point.
	*
	*/
	function number_format($number = 0, $userid = 0)
	{
		if ($userid <= 0)
		{
			$currencyid = $this->fetch_default_currencyid();
			$html = number_format($number, 0, $this->currencies[$currencyid]['decimal_point'], $this->currencies[$currencyid]['thousands_point']);
		}
		else
		{
			$currencyid = fetch_user('currencyid', $userid);
			$html = number_format($number, 0, $this->currencies[$currencyid]['decimal_point'], $this->currencies[$currencyid]['thousands_point']);
		}
		return $html;
	}
	function numbers_to_k($number)
	{
		if ($number >= 1000)
		{
			return round($number/1000) . "k";
		}
		else if ($number >= 1000000)
		{
			return round($number/1000000) . "M";
		}
		else if ($number >= 1000000)
		{
			return round($number/1000000) . "M";
		}
		else if ($number >= 1000000000)
		{
			return round($number/1000000000) . "B";
		}
		else
		{
			return number_format($number);
		}
	}
	/**
	* Function to properly format a dollar value based on the database currency settings (symbols, decimal places, thousands place, etc)
	*
	*/
	function format($number = 0, $currencyid = 0, $hidesymbols = false, $forcedecimalhide = false, $conversion = true, $numberstok = false)
	{
		global $ilconfig;
		$html = '';
		if ($currencyid == 0)
		{
			$currencyid = $ilconfig['globalserverlocale_defaultcurrency'];
		}
		$number = $this->string_to_number($number) * 1;
		if ($hidesymbols == false)
		{
			$html .= $this->currencies["$currencyid"]['symbol_left'];
			if ($forcedecimalhide)
			{
				if ($numberstok)
				{
					$html .= $this->numbers_to_k($number);
				}
				else
				{
					$html .= number_format($number);
				}
			}
			else
			{
				if ($numberstok)
				{
					$html .= $this->numbers_to_k($number);
				}
				else
				{
					$html .= number_format($number, $this->currencies["$currencyid"]['decimal_places'], $this->currencies["$currencyid"]['decimal_point'], $this->currencies["$currencyid"]['thousands_point']);
				}
			}
			$html .= $this->currencies["$currencyid"]['symbol_right'];
			if ($conversion OR $this->conversion)
			{
				if (isset($_SESSION['ilancedata']['user']['currencyid']) AND $_SESSION['ilancedata']['user']['currencyid'] != $currencyid)
				{
					$html = '<span class="currency-hover" title="' . $html . ' {_is_roughly} ' . $this->currencies[$_SESSION['ilancedata']['user']['currencyid']]['symbol_left'] . number_format(convert_currency($_SESSION['ilancedata']['user']['currencyid'], $number, $currencyid), $this->currencies[$_SESSION['ilancedata']['user']['currencyid']]['decimal_places'], $this->currencies[$_SESSION['ilancedata']['user']['currencyid']]['decimal_point'], $this->currencies[$_SESSION['ilancedata']['user']['currencyid']]['thousands_point']) . $this->currencies[$_SESSION['ilancedata']['user']['currencyid']]['symbol_right'] . '">' . $html . '</span>';
				}
			}
		}
		else
		{
			if ($forcedecimalhide)
			{
				if ($numberstok)
				{
					$html .= $this->numbers_to_k($number);
				}
				else
				{
					$html .= number_format($number);
				}
			}
			else
			{
				if ($numberstok)
				{
					$html .= $this->numbers_to_k($number);
				}
				else
				{
					$html .= number_format($number, $this->currencies["$currencyid"]['decimal_places'], $this->currencies["$currencyid"]['decimal_point'], $this->currencies["$currencyid"]['thousands_point']);
				}
			}
		}
		return $html;
	}
    
	/**
	* Function to fetch the default currency id installed for the marketplace
	*
	*/
	function fetch_default_currencyid()
	{
		global $ilconfig;
		return (isset($ilconfig['globalserverlocale_defaultcurrency']) ? $ilconfig['globalserverlocale_defaultcurrency'] : '1');
	}
    
	/**
	* Function to fetch a user's default currency setup when they registered or edited their profile
	*
	*/
	function fetch_user_currency($userid = 0)
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "currencyid
			FROM " . DB_PREFIX . "users
			WHERE user_id = '" . intval($userid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$cur = $ilance->db->fetch_array($sql, DB_ASSOC);
			return $cur['currencyid'];
		}
		else
		{
			return $this->fetch_default_currencyid();
		}
	}
    
	/**
	* Function to build a currency selector pulldown menu element
	*
	*/
	function pulldown($inputtype = '', $variableinfo = '')
	{
		global $ilance, $ilconfig, $headinclude;
		$pulldown = '';
		foreach ($this->currencies AS $key => $val)
		{
			if (is_int($key))
			{
				$values[$key] = $val['currency_abbrev'] . ' - ' . stripslashes($val['currency_name']) . ' (' . (empty($val['symbol_left']) ? $val['symbol_right'] : $val['symbol_left']) . ')';
			}
		}
		if ($inputtype == 'admin')
		{
			$disabled = '';
			$sql_rfp = $ilance->db->query("
				SELECT orderid
				FROM " . DB_PREFIX . "buynow_orders
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql_rfp) > 0)
			{
				$disabled = 'disabled="disabled"';
				$sql_conf = $ilance->db->query("SELECT configgroup FROM " . DB_PREFIX . "configuration WHERE name = '" . $variableinfo . "'");
				$res_conf = $ilance->db->fetch_array($sql_conf, DB_ASSOC);
				$headinclude .= '<script type="text/javascript">
<!--
jQuery(\'document\').ready(function(){ jQuery(\'#formid_' . $res_conf['configgroup'] . '\').submit(function() {alert_js(phrase[\'_active_listings_and_or_invoices_have_been_recorded_in_the_database_and_processed_in_your_previous_default_site_currency\']); }); });
//-->
</script>';
			}
			$pulldown = construct_pulldown('config_' . $variableinfo, 'config[' . $variableinfo . ']', $values, $ilconfig['globalserverlocale_defaultcurrency'], ' class="select-250" ' . $disabled);
		}
		else
		{
			$default_user_currency = (!empty($_SESSION['ilancedata']['user']['userid'])) ? (fetch_user('currencyid', intval($_SESSION['ilancedata']['user']['userid']))) : ((isset($ilance->GPC['currencyid']) AND $inputtype == 'registration') ? $ilance->GPC['currencyid'] : $ilconfig['globalserverlocale_defaultcurrency']);
			$pulldown = construct_pulldown('currencyid', 'currencyid', $values, $default_user_currency, ' class="select-250"');
		}
		return $pulldown;
	}
    
	/**
	* Function to take a string inputted by a user based on a dollar amount to be converted into 2 decimal places.
	* Example: 1,002.23 = 1002.23 or 12 = 12.00, etc.
	*
	* @param        integer         input price to be evaluated
	* 
	* @credit       developer       ratherodd.com
	* @return       integer         return 2 decimal place dollar amount ready for storing into database
	*/
	function string_to_number($price)
	{
		$price = stripslashes(preg_replace('/^\s+|\s+$/', '', $price));
		$decPoint = strrpos($price, '.');
		$decComma = strrpos($price, ',');
		$thous = "' ";
		$first = $second = '';
		if ($decPoint > -1 && $decComma > -1)
		{
			if ($decPoint > $decComma)
			{
				$thous .= ',';
			}
			else
			{
				$thous .= '.';
			}
			$decMark = ',';
		}
		if ((strpos($price, ' ') OR strpos($price, "'")) AND $decComma)
		{
			$decMark = ',';
		}
		if (strlen(substr($price, $decPoint + 1)) === 3 AND $decComma === false AND strpos($price, '.') < $decPoint)
		{
			$thous .= '.';
		}
		if (strlen(substr($price, $decComma + 1)) === 3 AND $decPoint === false AND strpos($price, ',') < $decComma)
		{
			$thous .= ',';
		}
		preg_match('/^(?:(\d{1,3}(?:(?:(?:[' . $thous . ']\d{3})+)?)?|\d+)?([,.]\d{1,})?|\d+)$/', $price, $matches);
		if (!isset($matches))
		{
			//return false;
			return $price;
		}
		if (!isset($matches[1]) AND !isset($matches[2]) AND isset($matches[0]))
		{
			$matches[1] = $matches[0];
		}
		$dec = ((isset($matches[2]) AND $matches[2] AND strlen($matches[2]) === 4 AND !isset($decMark) AND $matches[1] !== '0') ? '' : '.');
		if (isset($matches[1]))
		{
			$first = preg_replace("/[,' .]/", '', $matches[1]);
		}
		if (isset($matches[2]))
		{
			$second = str_replace(',', $dec, $matches[2]);
		}
		return (float) ($first . $second);
	}
    
	/**
	* Function to print the currency pull down selector
	*
	* @param       integer        currency id
	* @param       integer        use javascript onchange? (default false)
	* @param       boolean        disabled (default false)
	*
	* @return      string         Returns the formatted currency pull down menu
	*/
	function print_currency_pulldown($currencyid = 0, $jsonchange = false, $disabled = false)
	{
		global $ilconfig;
		$extra = ' class="select-250"' . (($disabled) ? ' disabled="disabled"' : '');
		if ($jsonchange AND $ilconfig['globalserverlocale_currencyselector'])
		{
			$this->fetch_currency_symbols_js();
			$extra .= ' onchange="currency_switcher()" ';
		}
		foreach ($this->currencies as $key => $val)
		{
			if (is_int($key))
			{
				$symbol = (!empty($val['symbol_left'])) ? $val['symbol_left'] : $val['symbol_right'];
				$values[$key] = handle_input_keywords($val['currency_abbrev']) . ' - ' . handle_input_keywords($val['currency_name']) . ' (' . handle_input_keywords($symbol) . ')';
			}
		}
		return construct_pulldown('currencyoptions', 'currencyid', $values, $currencyid, $extra);
	}
    
	function fetch_currency_symbols_js()
	{
		global $ilance, $ilconfig, $headinclude;
		$headinclude .= '<script type="text/javascript">
<!--
function currency_switcher()
{
	var currencyid = fetch_js_object(\'currencyoptions\').options[fetch_js_object(\'currencyoptions\').selectedIndex].value;
	fetch_js_object(\'ship_handlingfee_currency\').innerHTML = currencysymbols[currencyid];
	fetch_js_object(\'ship_handlingfee_currency_right\').innerHTML = currencysymbols2[currencyid];
';
		if ($ilconfig['enableauctiontab'])
		{
			$headinclude .= '
	fetch_js_object(\'startprice_currency\').innerHTML = currencysymbols[currencyid]; fetch_js_object(\'startprice_currency_right\').innerHTML = currencysymbols2[currencyid];
	fetch_js_object(\'buynowprice_currency\').innerHTML = currencysymbols[currencyid]; fetch_js_object(\'buynowprice_currency_right\').innerHTML = currencysymbols2[currencyid];';
			$headinclude .= '
	if(fetch_js_object(\'reserveprice_currency\'))
	{
		fetch_js_object(\'reserveprice_currency\').innerHTML = currencysymbols[currencyid]; fetch_js_object(\'reserveprice_currency_right\').innerHTML = currencysymbols2[currencyid];
	}';
		}
		$headinclude .= $ilconfig['enablefixedpricetab'] ? '
	fetch_js_object(\'buynowpricefixed_currency\').innerHTML = currencysymbols[currencyid]; fetch_js_object(\'buynowpricefixed_currency_right\').innerHTML = currencysymbols2[currencyid];' : '';
		$headinclude .= $ilconfig['enableclassifiedtab'] ? '
	fetch_js_object(\'classifiedprice_currency\').innerHTML = currencysymbols[currencyid]; fetch_js_object(\'classifiedprice_currency_right\').innerHTML = currencysymbols2[currencyid];' : '';

		($apihook = $ilance->api('fetch_currency_symbols_js_headinclude_end')) ? eval($apihook) : false;

		if ($ilconfig['globalserverlocale_currencyselector'])
		{
			for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
			{
				$headinclude .= 'fetch_js_object(\'ship_service_' . $i . '_css_costsymbol\').innerHTML = currencysymbols[currencyid]; fetch_js_object(\'ship_service_' . $i . '_css_costsymbol_right\').innerHTML = currencysymbols2[currencyid]; ';
				$headinclude .= 'fetch_js_object(\'next_ship_service_' . $i . '_css_costsymbol\').innerHTML = currencysymbols[currencyid]; fetch_js_object(\'next_ship_service_' . $i . '_css_costsymbol_right\').innerHTML = currencysymbols2[currencyid]; ';
			}
		}
	$headinclude .= '
}
var currencysymbols = [];
var currencysymbols2 = [];';
		foreach ($this->currencies AS $key => $val)
		{
			if (is_int($key))
			{
				$headinclude .= '
currencysymbols[' . $key . '] = \'' . $val['symbol_left'] . '\'
currencysymbols2[' . $key . '] = \'' . $val['symbol_right'] . '\'';
			}
		}
		$headinclude .= '
//-->
</script>' . "\n";
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>