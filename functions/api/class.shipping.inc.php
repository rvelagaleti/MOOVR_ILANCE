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
* Shipping class for ILance
*
* @package      iLance\Shipping
* @version      4.0.0.8059
* @author       ILance
*/
class shipping
{
	/**
	* Function to print out a listings pre-defined shipping methods to a specific buyers location.
	* 
	* This function can be used to show payment methods in a string or used to generate radio boxes based on a buyer shipping selector process
	* when multiple shipping services to a buyers location is defined.
	*
	* @param       integer        listing id
	* @param       integer        order quantity (default 1)
	* @param       boolean        print radio button logic (default false)
	* @param       boolean        print number of shipping services count only (default false)
	* @param       boolean        print pull down menu (default false)
	* @param       integer        buyers countryid (default 0)
	* @param       string         viewing users language (to present proper country title for specific language)
	*
	* @return      string         Returns HTML formatted string of ship-to locations available to a specific buyers location
	*/
	function print_shipping_methods($projectid = 0, $qty = 1, $radiobuttons = false, $countonly = false, $pulldownmenu = false, $countryid = 0, $slng = 'eng')
	{
		global $ilance, $phrase, $ilconfig, $ilpage, $show, $shipperidrow, $shippingradios_js;
		if ((isset($show['digital_download_delivery']) AND $show['digital_download_delivery'] == true) OR (isset($show['localpickuponly']) AND $show['localpickuponly'] == true))
		{
			return $countonly ? 0 : '';
		}
		$count = $shipperidrow = 0;
		$shipperidrowcount = 1;
		$html = '';
		if ($countryid > 0)
		{
			$countryid = intval($countryid);
		}
		else
		{
			$countryid = !empty($_SESSION['ilancedata']['user']['countryid']) ? $_SESSION['ilancedata']['user']['countryid'] : 0;
		}
		if ($countryid == 0)
		{
			return false;
		}
		if ($pulldownmenu)
		{
			$html .= '<select name="shipperid" id="shipperid" style="font-family: verdana">';
		}
		$result = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.row, l.location_$slng AS countrytitle, r.region_$slng AS region
			FROM " . DB_PREFIX . "projects_shipping_regions p
			LEFT JOIN " . DB_PREFIX . "locations l ON (p.countryid = l.locationid)
			LEFT JOIN " . DB_PREFIX . "locations_regions r ON (r.regionid = l.regionid)
			WHERE p.project_id = '" . intval($projectid) . "'
				AND p.countryid = '" . intval($countryid) . "'
			ORDER BY p.row ASC
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($result) > 0)
		{
			while ($res = $ilance->db->fetch_array($result, DB_ASSOC))
			{
				$count++;
				if ($countonly == false AND $radiobuttons)
				{
					$html .= $this->fetch_radio_ship_service_row($res['row'], $projectid, $res['countrytitle'], $res['region'], $qty);
				}
				if ($countonly == false AND $radiobuttons == false AND $pulldownmenu)
				{
					$html .= $this->fetch_option_ship_service_row($res['row'], $projectid, $res['countrytitle'], $res['region'], $qty);
				}
				if ($countonly == false AND $radiobuttons == false)
				{
					$shipperidrowcount = $res['row'];
				}
			}
		}
		$show['multipleshipservices'] = ($count > 1) ? true : false;
		$show['shipservices'] = ($count > 0) ? true : false;
		if ($count == 1 OR $shipperidrowcount > 0)
		{
			$shipperidrow = $shipperidrowcount;
		}
		if ($countonly == false AND $radiobuttons == false AND $pulldownmenu)
		{
			$html .= '</select>';
		}
		return $countonly ? $count : $html;
	}
    
	/**
	* Function to print the ajax shipping service rows called via AJAX from the listing page
	* 
	* This function will fetch the shipping service rows for an ajax related call from an auction listing page
	*
	* @param       integer        shipping row number
	* @param       integer        auction listing id
	* @param       string         country title
	* $param       string         region title
	* @param       integer        quantity
	*
	* @return      string         Returns HTML formatted string of payment method output or radio button input logic
	*/
	function fetch_radio_ship_service_row($row = 0, $pid = 0, $countrytitle = '', $region = '', $qty = 1)
	{
		global $ilance, $phrase, $ilconfig, $shippingradios_js, $hiddenfields;
		$html = $country = $qtystring = '';
		$currencyid = fetch_auction('currencyid', $pid);
		if ($row > 0)
		{
			$result = $ilance->db->query("
				SELECT d.ship_options_$row AS location, d.ship_service_$row AS shipperid, d.ship_fee_$row AS cost, d.ship_fee_next_$row AS cost_next, d.freeshipping_$row AS freeshipping, s.ship_method, s.ship_handlingtime, s.ship_handlingfee, s.ship_length, s.ship_width, s.ship_height, s.ship_weightlbs, s.ship_weightoz, d.ship_packagetype_$row as packagetype, d.ship_pickuptype_$row as pickuptype, p.zipcode, p.state, p.countryid
				FROM " . DB_PREFIX . "projects_shipping_destinations d
				LEFT JOIN " . DB_PREFIX . "projects_shipping s ON (d.project_id = s.project_id)
				LEFT JOIN " . DB_PREFIX . "projects p ON (d.project_id = p.project_id)
				WHERE d.project_id = '" . intval($pid) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($result) > 0)
			{
				$res = $ilance->db->fetch_array($result, DB_ASSOC);
				$disabled = '';
				$country = ($res['location'] == 'worldwide') ? $countrytitle . ' ({_worldwide})' : "$countrytitle";
				$service = ($res['shipperid'] > 0) ? $this->print_shipping_partner($res['shipperid']) : '';
				if ($res['freeshipping'])
				{
					$price = '<strong>{_free}</strong>';
					if ($res['ship_handlingfee'] > 0)
					{
						$price .= '&nbsp;&nbsp;&nbsp;<span style="padding-top:3px" class="smaller gray">+' . $ilance->currency->format($res['ship_handlingfee'], $currencyid) . ' {_handling_fee}</span>';
					}
				}
				else
				{
					if ($res['ship_method'] == 'flatrate')
					{
						if ($qty == '1')
						{
							$price = $ilance->currency->format(($res['cost'] + $res['ship_handlingfee']), $currencyid);
						}
						else if ($qty > 1)
						{
							$price = $ilance->currency->format(($res['cost'] + $res['ship_handlingfee'] + ($res['cost_next'] * (intval($qty) - 1))), $currencyid);
						}
						else
						{
							$price = '';
						}
					}
					else if ($res['ship_method'] == 'calculated')
					{
						$sql2 = $ilance->db->query("
							SELECT carrier, shipcode
							FROM " . DB_PREFIX . "shippers
							WHERE shipperid = '" . $res['shipperid'] . "'
						");
						$res2 = $ilance->db->fetch_array($sql2, DB_ASSOC);
						$sql3 = $ilance->db->query("
							SELECT cc
							FROM " . DB_PREFIX . "locations
							WHERE locationid = '" . $res['countryid'] . "'
						");
						$res3 = $ilance->db->fetch_array($sql3, DB_ASSOC);
						$ilance->GPC['sizecode'] = isset($ilance->GPC['sizecode']) ? $ilance->GPC['sizecode'] : $ilance->shipcalculator->sizeunits($res2['carrier'], $res['ship_length'], $res['ship_width'], $res['ship_height'], true);
						$carriers[$res2['carrier']] = true;
						$shipinfo = array('weight' => $res['ship_weightlbs'],
							'destination_zipcode' => $_SESSION['ilancedata']['user']['postalzip'],
							'destination_state' => $_SESSION['ilancedata']['user']['state'],
							'destination_city' => $_SESSION['ilancedata']['user']['city'],
							'destination_country' => $_SESSION['ilancedata']['user']['countryshort'],
							'origin_zipcode' => $res['zipcode'],
							'origin_state' => $res['state'],
							'origin_city' => $res['city'],
							'origin_country' => $res3['cc'],
							'carriers' => $carriers,
							'shipcode' => $res2['shipcode'],
							'length' => $res['ship_length'],
							'width' => $res['ship_width'],
							'height' => $res['ship_height'],
							'pickuptype' => $res['pickuptype'],
							'packagingtype' => $res['packagetype'],
							'weightunit' => 'LBS',
							'dimensionunit' => 'IN',
							'sizecode' => $ilance->GPC['sizecode']
						);
						$rates = $ilance->shipcalculator->get_rates($shipinfo);
						if (isset($rates['price'][0]))
						{
							$price = $rates['price'][0];
							foreach ($rates['code'] as $key => $value)
							{
								if ($value == $res['packagetype'])
								{
									$price = $rates['price'][$key];
								}
							}
							$hiddenfields .= '<input type="hidden" name="shipp_' . $res['shipperid'] . '" value="' . sprintf("%01.2f", $price) . '" />';
							$full_price = $price * $qty;
							$price = '<span id="ship_handling_working_' . $row . '" title="{_fetching_realtime_rates}"><b>' . $ilance->currency->format(sprintf("%01.2f", $full_price)) . '</b> (QTY x ' . $qty . ')</span>';
						}
						else if (isset($rates['errordesc']))
						{
							$price = '<span id="ship_handling_working_' . $row . '" title="{_fetching_realtime_rates}" class="smaller">' . $rates['errordesc'] . '</span>';
							$disabled = ' disabled="disabled"';
						}
						else
						{
							$price = '<span id="ship_handling_working_' . $row . '" title="{_fetching_realtime_rates}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'working.gif" border="0" alt="" id="" /></span>';
							$disabled = ' disabled="disabled"';
						}
					}
					else
					{
						$price = '{_local_pickup_only}';
					}
				}
				/*
				if ($qty > 1 AND $res['freeshipping'] == 0)
				{
					$qtystring = '<div style="padding-top:3px" class="smaller gray">(QTY x ' . $qty . ')</div>';
				}
				*/
				if (!empty($service))
				{
					$checked = '';
					if (isset($ilance->GPC['shipperid']) AND $ilance->GPC['shipperid'] == $res['shipperid'])
					{
						$checked = ' checked="checked"';
					}
					$html .= '<div style="padding-top:4px; padding-left:7px"><label for=""><input type="radio" name="shipperid" id="shipperid_' . $row . '" value="' . $res['shipperid'] . '" ' . $checked . ' ' . $disabled . ' /> <span class="black">' . $service . '</span> ' . '{_to}' . ' <span class="black">' . $country . '</span> :<span class="blue">&nbsp;' . $price . '</span></label></div>';
					$shippingradios_js .= '	 if (fetch_js_object(\'shipperid_' . $row . '\').checked == true)
	{
		haveerror = false;
	}
';
					unset($checked);
				}
			}
		}
		return $html;
	}
    
	/**
	* Function to print an options list for generation of a pulldown menu with buyer shipping choices available to them
	* 
	* This function will fetch the shipping service rows
	*
	* @param       integer        shipping row number
	* @param       integer        auction listing id
	* @param       string         country title
	* $param       string         region title
	* @param       integer        quantity
	*
	* @return      string         Returns HTML formatted string of payment method output or radio button input logic
	*/
	function fetch_option_ship_service_row($row = 0, $pid = 0, $countrytitle = '', $region = '', $qty = 1, $showprice = false)
	{
		global $ilance, $phrase, $ilconfig;
		$html = $country = $qtystring = '';
		$currencyid = fetch_auction('currencyid', $pid);
		if ($row > 0)
		{
			$result = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "d.ship_options_$row AS location, d.ship_service_$row AS shipperid, d.ship_fee_$row AS cost, d.ship_fee_next_$row AS cost_next, d.freeshipping_$row AS freeshipping, s.ship_method, s.ship_handlingtime, s.ship_handlingfee
				FROM " . DB_PREFIX . "projects_shipping_destinations d
				LEFT JOIN " . DB_PREFIX . "projects_shipping s ON (d.project_id = s.project_id)
				WHERE d.project_id = '" . intval($pid) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($result) > 0)
			{
				$res = $ilance->db->fetch_array($result, DB_ASSOC);
				$country = $countrytitle;
				$service = ($res['shipperid'] > 0) ? $this->print_shipping_partner($res['shipperid']) : '';
				if ($res['freeshipping'])
				{
					$price = '{_free}';
					if ($res['ship_handlingfee'] > 0)
					{
						$price .= '&nbsp;+' . $ilance->currency->format($res['ship_handlingfee'], $currencyid) . ' {_handling_fee}';
					}
				}
				else
				{
					if ($res['ship_method'] == 'flatrate')
					{
						if ($qty == '1')
						{
							$price = $ilance->currency->format(($res['cost'] + $res['ship_handlingfee']), $currencyid);
						}
						else if ($qty > 1)
						{
							$price = $ilance->currency->format(($res['cost'] + $res['ship_handlingfee'] + ($res['cost_next'] * (intval($qty) - 1))), $currencyid);
						}
						else
						{
							$price = '';
						}
					}
					else if ($res['ship_method'] == 'calculated')
					{
						$price = '';
					}
					else
					{
						$price = '{_local_pickup_only}';
					}
				}
				if (!empty($service))
				{
					$html .= '<option value="' . $res['shipperid'] . '" />' . (($showprice) ? $price . ' : ' : '') . $service . ' {_to} ' . $country . '</option>';
				}
			}
		}
		return $html;
	}
    
	/**
	* Function to print shipping countries pulldown based on a specific auction listing id
	* 
	* This function 
	*
	* @param       integer        auction listing id
	* @param       boolean        do string output? default false
	* @param       boolean        do only regions output? default false
	* @param       boolean        do only worldwide? default false
	*
	* @return      string         Returns HTML formatted string
	*/
	function print_item_shipping_countries_pulldown($projectid = 0, $string = false, $onlyregions = false, $worldwide = false, $selectedcid = 0, $dojs = false, $fieldname = 'showshippingdestinations', $statesfieldname = 'showshippingdestinationsstate', $statesdivid = 'showshippingdestinationsstateid', $disablecities = false, $citiesfieldname = 'showshippingdestinationscity', $citiesdivid = 'showshippingdestinationscityid')
	{
		global $ilance, $phrase, $show, $headinclude;
		$html = '';
		$htmlx = array();
		if ($string == false)
		{
			$onlyiso = '0';
			$extracss = '';
			$html = '<select name="' . $fieldname . '" id="' . $fieldname . '" class="select-250"' . (($dojs) ? ' onchange="print_states(\'' . $statesfieldname . '\', \'' . $fieldname . '\', \'' . $statesdivid . '\', \'' . intval($onlyiso) . '\', \'' . $extracss . '\', \'' . intval($disablecities) . '\', \'' . $citiesfieldname . '\', \'' . $citiesdivid . '\');' : '') . '"><option value="">-</option>';
		}
		$slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';
		$sql = $ilance->db->query("
			SELECT l.location_" . $slng . " AS country, s.countryid, r.region_" . $slng . " AS region
			FROM " . DB_PREFIX . "projects_shipping_regions s
			LEFT JOIN " . DB_PREFIX . "locations l ON s.countryid = l.locationid
			LEFT JOIN " . DB_PREFIX . "locations_regions r ON (r.regionid = l.regionid)
			WHERE s.project_id = '" . intval($projectid) . "'
				AND l.visible = '1'
			" . ($onlyregions ? "GROUP BY r.regionid" : "") . "
			" . ($string == false ? "GROUP BY countryid" : "") . "
			ORDER BY country ASC
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				if ($string == false)
				{
					$html .= (isset($selectedcid) AND $selectedcid > 0 AND $selectedcid == $res['countryid']) ? '<option value="' . $res['countryid'] . '" selected="selected">' . handle_input_keywords($res['country']) . '</option>' : '<option value="' . $res['countryid'] . '">' . handle_input_keywords($res['country']) . '</option>';
				}
				else
				{
					if ($onlyregions)
					{
						$html .= ucwords(str_replace('_', ' ', $res['region'])) . ', ';
					}
					else
					{
						$htmlx[] = handle_input_keywords($res['country']);
					}
				}
			}
			if ($onlyregions)
			{
				if (!empty($html) AND $string)
				{
					$html = substr($html, 0, -2);
				}
			}
			else
			{
				if (is_array($htmlx) AND count($htmlx) > 0)
				{
					$htmlx = array_unique($htmlx);
				}
			}
		}
		if ($onlyregions OR $string == false)
		{
			$html .= ($string == false) ? '</select>' : '';
		}
		else
		{
			foreach ($htmlx AS $country)
			{
				$html .= $country . ', ';
			}
			$html = substr($html, 0, -2);
		}
		return $html;
	}
    
	/**
	* Function to print shipping countries string based on a specific auction listing id
	* 
	* This function 
	*
	* @param       integer        auction listing id
	* @param       boolean        force all countries? default false
	*
	* @return      string         Returns HTML formatted string of payment method output or radio button input logic
	*/
	function print_item_shipping_countries_string($projectid = 0, $forceall = false)
	{
		global $ilance, $phrase, $show, $ilconfig;
		$show['shipsworldwide'] = false;
		$html = '';
		$fields = '';
		for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
		{
			$fields .= 'ship_options_' . $i . ', ';
		}
		$fields .= 'destinationid';
		$sql = $ilance->db->query("
			SELECT $fields
			FROM " . DB_PREFIX . "projects_shipping_destinations
			WHERE project_id = '" . intval($projectid) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
			{
				if (isset($res['ship_options_' . $i]) AND !empty($res['ship_options_' . $i]))
				{
					switch ($res['ship_options_' . $i])
					{
						case 'domestic':
						{
							if ($show['shipsworldwide'] == false)
							{
								$html = $this->print_item_shipping_countries_pulldown($projectid, true, false) . ' {_only_lower}';
							}
							break;
						}
						case 'worldwide':
						{
							$show['shipsworldwide'] = true;
							if ($forceall == false)
							{
								$html = '{_worldwide}';
							}
							else
							{
								$html = $this->print_item_shipping_countries_pulldown($projectid);
							}
							break;
						}
						case 'custom':
						{
							$html = $this->print_item_shipping_countries_pulldown($projectid, true, true);
							break;
						}
					}
				}
			}
		}
		return $html;
	}
    
	/**
	* Function to fetch the lowest possible shipping prices within a haystack of prices
	* 
	* This function will be used on the search results to display the lowest possible shipping cost to buyers
	*
	* @param       array          array with multiple shipping costs
	* @param       boolean        include currency format (default false)
	* @param       integer        listing id
	* @param       integer        currency id
	* @param       boolean        if price is 0 show "Free" phrase? (default true)
	* @param       boolean        add class to "Free" phrase (default true)
	* @param       boolean        show plus symbol in the output (default false)
	*
	* @return      string         Returns HTML formatted string of lowest shipping price in the haystack
	*/
	function fetch_lowest_shipping_cost($prices = array(), $docurrencyformat = false, $pid = 0, $currencyid = '', $showfreephrase = true, $showfreephraseformat = true, $showplussymbol = false)
	{
		global $ilance;
		$currencyid = empty($currencyid) ? fetch_auction('currencyid', $pid) : $currencyid;
		if ($docurrencyformat)
		{
			if (count($prices) > 0)
			{
				if ($showfreephrase)
				{
					foreach ($prices AS $price)
					{
						if ($price <= 0)
						{
							if ($showfreephraseformat)
							{
								return '<span class="green">{_free}</span>';
							}
							else
							{
								return '{_free}';
							}
						}
					}
					return (($showplussymbol) ? '+' : '') . $ilance->currency->format(min($prices), $currencyid);
				}
				else
				{
					return (($showplussymbol) ? '+' : '') . $ilance->currency->format(min($prices), $currencyid);
				}
			}
			else
			{
				return '{_see_listing}';
			}
		}
		else
		{
			if (count($prices) > 0)
			{
				if ($showfreephrase)
				{
					foreach ($prices AS $price)
					{
						if ($price <= 0)
						{
							if ($showfreephraseformat)
							{
								return '<span class="green">{_free}</span>';
							}
							else
							{
								return '{_free}';
							}
						}
					}
					return min($prices);
				}
				else
				{
					return min($prices);
				}
			}
		}
		return false;
	}
    
	/**
	* Function to handle saving the shipping logic for an item within the appropriate areas of the database
	*
	* @param       integer     listing id
	* @param       array       shipping array with all details
	*
	* @return      nothing
	*/
	function save_item_shipping_logic($rfpid = 0, $shipping = array())
	{
		global $ilance, $ilconfig, $phrase, $ilpage;
		if (isset($shipping) AND is_array($shipping) AND $rfpid > 0)
		{
			// #### item shipping info #####################
			$sql = $ilance->db->query("
				SELECT project_id
				FROM " . DB_PREFIX . "projects_shipping
				WHERE project_id = '" . intval($rfpid) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) == 0)
			{
				$ilance->db->query("
					INSERT INTO " . DB_PREFIX . "projects_shipping
					(project_id, ship_method, ship_handlingtime, ship_handlingfee, ship_length, ship_width, ship_height, ship_weightlbs, ship_weightoz)
					VALUES(
					'" . intval($rfpid) . "',
					'" . $ilance->db->escape_string($shipping['ship_method']) . "',
					'" . intval($shipping['ship_handlingtime']) . "',
					'" . $ilance->db->escape_string($shipping['ship_handlingfee']) . "',
					'" . intval($shipping['ship_length']) . "',
					'" . intval($shipping['ship_width']) . "',
					'" . intval($shipping['ship_height']) . "',
					'" . intval($shipping['ship_weightlbs']) . "',
					'" . intval($shipping['ship_weightoz']) . "')
				", 0, null, __FILE__, __LINE__);
			}
			else
			{
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "projects_shipping
					SET ship_method = '" . $ilance->db->escape_string($shipping['ship_method']) . "',
					ship_handlingtime = '" . intval($shipping['ship_handlingtime']) . "',
					ship_handlingfee = '" . $ilance->db->escape_string($shipping['ship_handlingfee']) . "',
					ship_length = '" . intval($shipping['ship_length']) . "',
					ship_width = '" . intval($shipping['ship_width']) . "',
					ship_height = '" . intval($shipping['ship_height']) . "',
					ship_weightlbs = '" . intval($shipping['ship_weightlbs']) . "',
					ship_weightoz = '" . intval($shipping['ship_weightoz']) . "'
					WHERE project_id = '" . intval($rfpid) . "'
				", 0, null, __FILE__, __LINE__);
			}
			// #### item shipping destinations info ########
			$ilance->db->query("
				DELETE FROM " . DB_PREFIX . "projects_shipping_regions
				WHERE project_id = '" . intval($rfpid) . "'
			", 0, null, __FILE__, __LINE__);
			for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
			{
				if (isset($shipping['ship_options_' . $i]) AND isset($shipping['ship_service_' . $i]) AND !empty($shipping['ship_options_' . $i]) AND !empty($shipping['ship_service_' . $i]))
				{
					// #### item ship-to regions ###########
					if ($shipping['ship_options_' . $i] == 'domestic')
					{
						$countryid = fetch_country_id($ilconfig['registrationdisplay_defaultcountry'], fetch_site_slng());
						$region = $this->fetch_region_by_countryid($countryid);
						$ilance->db->query("
							INSERT INTO " . DB_PREFIX . "projects_shipping_regions
							(project_id, countryid, row)
							VALUES(
							'" . intval($rfpid) . "',
							'" . intval($countryid) . "',
							'" . $i . "')
						", 0, null, __FILE__, __LINE__);
						unset($countryid, $region);
					}
					else if ($shipping['ship_options_' . $i] == 'worldwide')
					{
						$countries = $this->fetch_countries_by_region_array('worldwide');
						foreach ($countries AS $countryinfo)
						{
							$ilance->db->query("
								INSERT INTO " . DB_PREFIX . "projects_shipping_regions
								(project_id, countryid, row)
								VALUES(
								'" . intval($rfpid) . "',
								'" . intval($countryinfo['countryid']) . "',
								'" . $i . "')
							", 0, null, __FILE__, __LINE__);
						}
					}
					else
					{
						if (isset($shipping['ship_options_custom_region_' . $i]) AND count($shipping['ship_options_custom_region_' . $i]) > 0)
						{
							foreach ($shipping['ship_options_custom_region_' . $i] AS $key => $region)
							{
								if (!empty($region))
								{
									$countries = $this->fetch_countries_by_region_array($region);
									foreach ($countries AS $countryinfo)
									{
										$ilance->db->query("
											INSERT INTO " . DB_PREFIX . "projects_shipping_regions
											(project_id, countryid, row)
											VALUES(
											'" . intval($rfpid) . "',
											'" . intval($countryinfo['countryid']) . "',
											'" . $i . "')
										", 0, null, __FILE__, __LINE__);
									}
								}
							}
						}
					}
					$sql = $ilance->db->query("
						SELECT project_id
						FROM " . DB_PREFIX . "projects_shipping_destinations
						WHERE project_id = '" . intval($rfpid) . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) == 0)
					{
						$ilance->db->query("
							INSERT INTO " . DB_PREFIX . "projects_shipping_destinations
							(destinationid, project_id, ship_options_" . $i . ", ship_service_" . $i . ", ship_packagetype_" . $i . ", ship_pickuptype_" . $i . ", ship_fee_" . $i . ", ship_fee_next_" . $i . ", freeshipping_" . $i . ")
							VALUES(
							NULL,
							'" . intval($rfpid) . "',
							'" . $ilance->db->escape_string($shipping['ship_options_' . $i]) . "',
							'" . $ilance->db->escape_string($shipping['ship_service_' . $i]) . "',
							'" . $ilance->db->escape_string($shipping['ship_packagetype_' . $i]) . "',
							'" . $ilance->db->escape_string($shipping['ship_pickuptype_' . $i]) . "',
							'" . $ilance->db->escape_string($shipping['ship_fee_' . $i]) . "',
							'" . $ilance->db->escape_string($shipping['ship_fee_next_' . $i]) . "',
							'" . $ilance->db->escape_string($shipping['freeshipping_' . $i]) . "')
						", 0, null, __FILE__, __LINE__);
					}
					else
					{
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "projects_shipping_destinations
							SET ship_options_" . $i . " = '" . $ilance->db->escape_string($shipping['ship_options_' . $i]) . "',
							ship_service_" . $i . " = '" . intval($shipping['ship_service_' . $i]) . "',
							ship_packagetype_" . $i . " = '" . $ilance->db->escape_string($shipping['ship_packagetype_' . $i]) . "',
							ship_pickuptype_" . $i . " = '" . $ilance->db->escape_string($shipping['ship_pickuptype_' . $i]) . "',
							ship_fee_" . $i . " = '" . $ilance->db->escape_string($shipping['ship_fee_' . $i]) . "',
							ship_fee_next_" . $i . " = '" . $ilance->db->escape_string($shipping['ship_fee_next_' . $i]) . "',
							freeshipping_" . $i . " = '" . intval($shipping['freeshipping_' . $i]) . "'
							WHERE project_id = '" . intval($rfpid) . "'
						", 0, null, __FILE__, __LINE__);
					}
				}
			}
		}
		return true;
	}
    
	/**
	* Function to fetch and return an array with the raw shipping costs by a shipper id for a specific listing (including single or multiple qty)
	* in array format $array = ('amount', 'total', 'free').  'free' denotes free shipping.
	*
	* @param       integer        listing id
	* @param       integer        shipping service id
	* @param       integer        quantity (default 1)
	* @param       integer        force shipping cost to be used (default 0)
	*
	* @return      string         Returns php array with $array['total'], $array['amount'] and $array['free'] values
	*/
	function fetch_ship_cost_by_shipperid($projectid = 0, $shipperid = 0, $qty = 1, $overridecost = 0)
	{
		global $ilance, $ilconfig;
		$cost = array ('amount' => 0, 'total' => 0, 'free' => 0);
		$fields = '';
		if ($overridecost > 0)
		{
			return array ('amount' => $overridecost, 'total' => $overridecost, 'free' => 0);
		}
		for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
		{
			$fields .= "d.ship_service_$i, d.ship_fee_$i, d.ship_fee_next_$i, d.freeshipping_$i, ";
		}
		$fields = substr($fields, 0, -2);
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "s.ship_handlingfee, s.ship_method, $fields
			FROM " . DB_PREFIX . "projects_shipping_destinations d
			LEFT JOIN " . DB_PREFIX . "projects_shipping s ON (d.project_id = s.project_id)
			WHERE d.project_id = '" . intval($projectid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			if ($res['ship_method'] == 'calculated' AND isset($ilance->GPC['shipp_' . $shipperid]))
			{
				$cost = array (
					'amount' => ($ilance->GPC['shipp_' . $shipperid] + $res['ship_handlingfee']),
					'total' => (($ilance->GPC['shipp_' . $shipperid] * intval($qty)) + $res['ship_handlingfee']),
					'free' => 0,
				    );
				return $cost;
			}
			for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
			{
				if (isset($res['ship_service_' . $i]) AND $shipperid > 0 AND $res['ship_service_' . $i] > 0 AND $res['ship_service_' . $i] == $shipperid)
				{
					if ($qty > 1)
					{
						if ($res['ship_fee_next_' . $i] > 0)
						{
							$cost = array (
								'amount' => ($res['ship_fee_' . $i] + $res['ship_handlingfee']),
								'total' => ($res['ship_fee_' . $i] + $res['ship_handlingfee'] + ($res['ship_fee_next_' . $i] * (intval($qty) - 1))),
								'free' => $res['freeshipping_' . $i],
							);
						}
						else
						{
							$cost = array (
								'amount' => ($res['ship_fee_' . $i] + $res['ship_handlingfee']),
								'total' => (($res['ship_fee_' . $i]) + $res['ship_handlingfee']),
								'free' => $res['freeshipping_' . $i],
							);
						}
					}
					else if ($qty == 1)
					{
						$cost = array (
							'amount' => ($res['ship_fee_' . $i] + $res['ship_handlingfee']),
							'total' => ($res['ship_fee_' . $i] + $res['ship_handlingfee']),
							'free' => $res['freeshipping_' . $i],
						);
					}
					else
					{
						$cost = array (
							'amount' => 0,
							'total' => 0,
							'free' => 0,
						);
					}
					break;
				}
			}
		}
		return $cost;
	}
    
	/**
	* Function to to determine if a specific item / listing id can be shipped to a specific country
	*
	* @param       integer        listing id
	* @param       integer        country id
	*
	* @return      boolean        Returns true or false
	*/
	function can_item_ship_to_countryid($projectid = 0, $countryid = 0)
	{
		global $ilance, $show;
		$show['itemcanshiptouser'] = false;
		$slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';
		$result = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.row, l.location_$slng AS countrytitle, r.region_$slng AS region
			FROM " . DB_PREFIX . "projects_shipping_regions p
			LEFT JOIN " . DB_PREFIX . "locations l ON (p.countryid = l.locationid)
			LEFT JOIN " . DB_PREFIX . "locations_regions r ON (r.regionid = l.regionid)
			WHERE p.project_id = '" . intval($projectid) . "'
				AND p.countryid = '" . intval($countryid) . "'
				AND l.visible = '1'
			ORDER BY p.row ASC
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($result) > 0)
		{
			$show['itemcanshiptouser'] = true;
			return true;
		}
		return false;
	}
    
	function fetch_listing_shipping_regions($pid = 0, $row = '')
	{
		global $ilance;
		$array = array ();
		$slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';
		$row = !empty($row) ? " AND sr.row = '" . $row . "'" : "";
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "r.region_$slng AS region, sr.row
			FROM " . DB_PREFIX . "projects_shipping_regions sr
			LEFT JOIN " . DB_PREFIX . "locations l ON (sr.countryid = l.locationid)
			LEFT JOIN " . DB_PREFIX . "locations_regions r ON (r.regionid = l.regionid)
			WHERE sr.project_id = '" . intval($pid) . "'
			    AND l.visible = '1'
			$row
			GROUP BY r.regionid
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$array[$res['row']][] = $res['region'];
			}
		}
		$array = $ilance->template->remove_duplicate_template_variables($array);
		return $array;
	}
    
	/**
	* Function to print the actual shipping tracking number with URL to let buyer visit shipper site to see progress with the tracking.
	*
	* @param        integer     shipping partner id
	*
	* @return	boolean     Returns HTML presentation
	*/
	function print_tracking_url($partnerid = 0, $trackingnumber = '')
	{
		global $ilance, $ilconfig, $phrase;
		$html = '<span class="black">' . $trackingnumber . '</span>';
		$sql = $ilance->db->query("
			    SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "trackurl, title
			    FROM " . DB_PREFIX . "shippers
			    WHERE shipperid = '" . intval($partnerid) . "'
			    LIMIT 1
		    ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			if (!empty($res['trackurl']))
			{
				$html = '<span class="blueonly" title="{_track_your_shipment_with} ' . handle_input_keywords($res['title']) . '"><a href="' . handle_input_keywords($res['trackurl']) . handle_input_keywords($trackingnumber) . '" target="_blank">' . $trackingnumber . '</a></span>';
			}
		}
		return $html;
	}
    
	/**
	* Function to print address select field options based on the users credit card billing details
	*
	* @param        integer     user id
	* @param        string      selected option
	* @param        boolean     force text only (default false)
	*
	* @return	string      Returns the HTML representation of the selected address <option>'s from a credit card profile
	*/
	function print_cc_shipping_address_pulldown($userid = 0, $selected = '', $textonly = false)
	{
		global $ilance, $ilconfig;
		$html = '';
		if ($userid > 0)
		{
			$sql = $ilance->db->query("
				SELECT cc_id, card_billing_address1, card_billing_address2, card_city, card_state, card_postalzip, card_country
				FROM " . DB_PREFIX . "creditcards
				WHERE user_id = '" . intval($userid) . "'
					AND creditcard_status = 'active'
					AND authorized = 'yes'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$selectedvalue = '';
				$fieldvalue = 'card[' . $res['cc_id'] . ']';
				if (isset($selected) AND $selected == $fieldvalue)
				{
					$selectedvalue = ' selected="selected"';
				}
				if ($textonly == false)
				{
					$html = '<option value="' . $fieldvalue . '"' . $selectedvalue . ' style="font-family: verdana">';
				}
				if (!empty($res['card_billing_address2']))
				{
					$html .= handle_input_keywords(stripslashes(ucfirst($res['card_billing_address1']))) . ", " .
					handle_input_keywords(stripslashes(ucfirst($res['card_billing_address2']))) . ", " .
					handle_input_keywords(stripslashes(ucfirst($res['card_city']))) . ", " .
					handle_input_keywords(stripslashes(ucfirst($res['card_state']))) . ", " .
					handle_input_keywords(mb_strtoupper($res['card_postalzip'])) . ", " .
					handle_input_keywords($ilance->db->fetch_field(DB_PREFIX . "locations", "locationid = '" . $res['card_country'] . "'", "location_" . fetch_user_slng(intval($userid))));
				}
				else
				{
					$html .= handle_input_keywords(stripslashes(ucfirst($res['card_billing_address1']))) . ", " .
					handle_input_keywords(stripslashes(ucfirst($res['card_city']))) . ", " .
					handle_input_keywords(stripslashes(ucfirst($res['card_state']))) . ", " .
					handle_input_keywords(mb_strtoupper($res['card_postalzip'])) . ", " .
					handle_input_keywords($ilance->db->fetch_field(DB_PREFIX . "locations", "locationid = '" . $res['card_country'] . "'", "location_" . fetch_user_slng(intval($userid))));
				}
				if ($textonly)
				{
					if (isset($selected) AND $selected == $fieldvalue)
					{
						return $html;
					}
				}
				if ($textonly == false)
				{
					$html .= '</option>';
				}
			}
		}
		return $html;
	}
    
	/**
	* Function to print an address based on the users credit card billing details
	*
	* @param        integer     user id
	*
	* @return	string      Returns the HTML representation of the address
	*/
	function print_cc_shipping_address_text($userid = 0)//unused
	{
		global $ilance, $phrase, $ilconfig;
		if ($userid > 0)
		{
			$sql = $ilance->db->query("
				SELECT cc_id, card_billing_address1, card_billing_address2, card_city, card_state, card_postalzip, card_country
				FROM " . DB_PREFIX . "creditcards
				WHERE user_id = '" . intval($userid) . "'
					AND creditcard_status = 'active'
					AND authorized = 'yes'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql);
				if (!empty($res['card_billing_address2']))
				{
					$html = stripslashes(ucfirst($res['card_billing_address1'])) . ", " .
					stripslashes(ucfirst($res['card_billing_address2'])) . ", " .
					stripslashes(ucfirst($res['card_city'])) . ", " .
					stripslashes(ucfirst($res['card_state'])) . ", " .
					mb_strtoupper($res['card_postalzip']) . ", " .
					$ilance->db->fetch_field(DB_PREFIX . "locations", "locationid = '" . $res['card_country'] . "'", "location_" . fetch_user_slng(intval($userid)));
				}
				else
				{
					$html = stripslashes(ucfirst($res['card_billing_address1'])) . ", " .
					stripslashes(ucfirst($res['card_city'])) . ", " .
					stripslashes(ucfirst($res['card_state'])) . ", " .
					mb_strtoupper($res['card_postalzip']) . ", " .
					$ilance->db->fetch_field(DB_PREFIX . "locations", "locationid = '" . $res['card_country'] . "'", "location_" . fetch_user_slng(intval($userid)));
				}
			}
		}
		else
		{
			$html = '{_no_shipping_address_available}';
		}
		return $html;
	}
    
	/**
	* Function to print a shipping address pulldown menu based on the users personal details as well as the address from any credit card profiles added.
	*
	* @param        integer     user id
	* @param        string      fieldname
	* @param        string      class
	* @param        string      selected value (if applicable)
	* @param        boolean     force text only? (default false)
	*
	* @return	string      Returns the HTML representation of the shipping address pull down menu
	*/
	function print_shipping_address_pulldown($userid = 0, $fieldname = 'shipping_address_id', $class = '', $selected = '', $textonly = false, $width = '300')
	{
		global $ilance, $phrase, $ilconfig;
		$html = '';
		if ($userid > 0)
		{
			$slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';
			if ($textonly == false)
			{
				$html = '<select name="' . $fieldname . '" style="font-family: verdana; width:' . $width . 'px" class="' . $class . '" id="' . $fieldname . '">';
			}
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "u.address, u.address2, u.city, u.state, u.zip_code, l.location_$slng AS country
				FROM " . DB_PREFIX . "users u
				LEFT JOIN " . DB_PREFIX . "locations l ON (l.locationid = u.country)
				WHERE u.user_id = '" . intval($userid) . "'
					AND u.status = 'active'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$res = array_map('stripslashes', $res);
				$res = array_map('handle_input_keywords', $res);
				$zip = (mb_strtolower($res['zip_code']) == '{_unknown}') ? mb_strtolower($res['zip_code']) : $res['zip_code'];
				$fieldvalue = 'profile[' . intval($userid) . ']';
				$selectedvalue = '';
				if (isset($selected) AND $selected == $fieldvalue)
				{
					$selectedvalue = ' selected="selected"';
				}
				if ($textonly == false)
				{
					$html .= '<option value="' . $fieldvalue . '"' . $selectedvalue . '>';
					if (!empty($res['address2']))
					{
						$html .= ucwords($res['address']) . ", " . ucwords($res['address2']) . ", " . ucwords($res['city']) . ", " . ucwords($res['state']) . ", " . $zip . ", " . $res['country'];
					}
					else
					{
						$html .= ucwords($res['address']) . ", " . ucwords($res['city']) . ", " . ucwords($res['state']) . ", " . $zip . ", " . $res['country'];
					}
					$html .= '</option>';
					$html .= $this->print_cc_shipping_address_pulldown(intval($userid), $selected, $textonly);
				}
				else
				{
					if (isset($selected) AND $selected == $fieldvalue)
					{
						if (!empty($res['address2']))
						{
							$html .= ucwords($res['address']) . ", " . ucwords($res['address2']) . ", " . ucwords($res['city']) . ", " . ucwords($res['state']) . ", " . $zip . ", " . $res['country'];
						}
						else
						{
							$html .= ucwords($res['address']) . ", " . ucwords($res['city']) . ", " . ucwords($res['state']) . ", " . $zip . ", " . $res['country'];
						}
						return $html;
					}
					$html .= $this->print_cc_shipping_address_pulldown(intval($userid), $selected, $textonly);
				}
			}
			if ($textonly == false)
			{
				$html .= '</select>';
			}
		}
		else
		{
			$html = '{_no_shipping_address_available}';
		}
		return $html;
	}
    
	/**
	* Function to print a shipping address text based on the users personal details
	*
	* @param        integer     user id
	*
	* @return	string      Returns the HTML representation of the shipping address
	*/
	function print_shipping_address_text($userid = 0)
	{
		global $ilance, $phrase, $ilconfig;
		$html = '{_no_shipping_address_available}';
		if ($userid > 0)
		{
			$slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';
			$sql = $ilance->db->query("
				SELECT u.address, u.address2, u.city, u.state, u.zip_code, l.location_" . $slng . " as country
				FROM " . DB_PREFIX . "users u
				LEFT JOIN " . DB_PREFIX . "locations l ON (l.locationid = u.country)
				WHERE u.user_id = '" . intval($userid) . "'
					AND u.status = 'active'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$res = array_map('stripslashes', $res);
				$res = array_map('handle_input_keywords', $res);
				$zip = (mb_strtolower($res['zip_code']) == '{_unknown}') ? mb_strtolower($res['zip_code']) : $res['zip_code'];
		
				($apihook = $ilance->api('print_shipping_address_text_start')) ? eval($apihook) : false;
		
				if (!empty($res['address2']))
				{
					$html = ucwords($res['address']) . ", " . ucwords($res['address2']) . ", " . ucwords($res['city']) . ", " . ucwords($res['state']) . ", " . $zip . ", " . $res['country'];
				}
				else
				{
					$html = ucwords($res['address']) . ", " . ucwords($res['city']) . ", " . ucwords($res['state']) . ", " . $zip . ", " . $res['country'];
				}
		
				($apihook = $ilance->api('print_shipping_address_text_end')) ? eval($apihook) : false;
			}
		}
		return $html;
	}
    
	/**
	* Function to fetch the total number of shipping services an item is using for their listing
	*
	* @param       integer        listing id
	*
	* @return      integer        Returns number of shipping services count
	*/
	function fetch_shipping_services_count($pid = 0)
	{
		global $ilance;
		$count = 0;
		$ship_method = $ilance->db->fetch_field(DB_PREFIX . "projects_shipping", "project_id = '" . intval($pid) . "'", "ship_method");
		if ($ship_method == 'flatrate' OR $ship_method == 'calculated')
		{
			$count = 1;
			$sql = $ilance->db->query("
				SELECT MAX(row) AS count
				FROM " . DB_PREFIX . "projects_shipping_regions
				WHERE project_id = '" . intval($pid) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$count = $res['count'];
			}
		}
		return $count;
	}
    
	/**
	* Function to print the actual shipping partner based on a supplied shipping partner id
	*
	* @param        integer     shipping partner id
	*
	* @return	boolean     Returns HTML presentation
	*/
	function print_shipping_partner($partnerid = 0)
	{
		global $ilance, $ilconfig, $phrase;
		$html = '{_no_shipping_partner_assigned_to_this_listing}';
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "title, carrier
			FROM " . DB_PREFIX . "shippers
			WHERE shipperid = '" . intval($partnerid) . "'
			ORDER BY sort ASC
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$html = $this->print_shipping_carrier($res['carrier']) . handle_input_keywords(stripslashes($res['title']));
		}
		return $html;
	}
    
	function print_shipping_carrier($carrier = '')
	{
		$html = '';
		switch ($carrier)
		{
			case 'fedex':
			{
				$html = 'FedEx ';
				break;
			}
			case 'ups':
			{
				$html = 'UPS ';
				break;
			}
			case 'usps':
			{
				$html = 'USPS ';
				break;
			}
		}
		return $html;
	}
    
	/**
	* Function to mark a listing as un-shipped (by the seller themselves)
	*
	* @return      nothing
	*/
	function mark_listing_as_unshipped($pid = 0, $bid = 0, $sellerid = 0, $buyerid = 0, $mode = '')
	{
		global $ilance, $phrase, $ilconfig;
		if ($mode == 'buynow')
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "buynow_orders
				SET sellermarkedasshipped = '0',
				sellermarkedasshippeddate = '0000-00-00 00:00:00',
				shiptracknumber = ''
				WHERE project_id = '" . intval($pid) . "'
					AND buyer_id = '" . intval($buyerid) . "'
					AND owner_id = '" . intval($sellerid) . "'
					AND orderid = '" . intval($bid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
		}
		else if ($mode == 'escrow')
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects_escrow
				SET sellermarkedasshipped = '0',
				sellermarkedasshippeddate = '0000-00-00 00:00:00',
				shiptracknumber = ''
				WHERE project_id = '" . intval($pid) . "'
					AND user_id = '" . intval($buyerid) . "'
					AND project_user_id = '" . intval($sellerid) . "'
					AND escrow_id = '" . intval($bid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
		}
		else if ($mode == 'bid')
		{
			$ilance->db->query("
			    UPDATE " . DB_PREFIX . "project_bids
				SET sellermarkedasshipped = '0',
				sellermarkedasshippeddate = '0000-00-00 00:00:00',
				shiptracknumber = ''
				WHERE project_id = '" . intval($pid) . "'
					AND user_id = '" . intval($buyerid) . "'
					AND project_user_id = '" . intval($sellerid) . "'
					AND bid_id = '" . intval($bid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_realtimebids
				SET sellermarkedasshipped = '0',
				sellermarkedasshippeddate = '0000-00-00 00:00:00',
				shiptracknumber = ''
				WHERE project_id = '" . intval($pid) . "'
					AND user_id = '" . intval($buyerid) . "'
					AND project_user_id = '" . intval($sellerid) . "'
					AND bid_id = '" . intval($bid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
		}
	}
    
	/**
	* Function to mark a listing as shipped (by the seller themselves)
	*
	* @return      nothing
	*/
	function mark_listing_as_shipped($pid = 0, $bid = 0, $sellerid = 0, $buyerid = 0, $mode = '', $trackingnumber = '')
	{
		global $ilance, $phrase, $ilconfig;
		if ($mode == 'buynow')
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "buynow_orders
				SET sellermarkedasshipped = '1',
				sellermarkedasshippeddate = '" . DATETIME24H . "',
				shiptracknumber = '" . $ilance->db->escape_string($trackingnumber) . "'
				WHERE project_id = '" . intval($pid) . "'
					AND buyer_id = '" . intval($buyerid) . "'
					AND owner_id = '" . intval($sellerid) . "'
					AND orderid = '" . intval($bid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
		}
		else if ($mode == 'escrow')
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects_escrow
				SET sellermarkedasshipped = '1',
				sellermarkedasshippeddate = '" . DATETIME24H . "',
				shiptracknumber = '" . $ilance->db->escape_string($trackingnumber) . "'
				WHERE project_id = '" . intval($pid) . "'
					AND user_id = '" . intval($buyerid) . "'
					AND project_user_id = '" . intval($sellerid) . "'
					AND escrow_id = '" . intval($bid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_bids
				SET sellermarkedasshipped = '1',
				sellermarkedasshippeddate = '" . DATETIME24H . "',
				shiptracknumber = '" . $ilance->db->escape_string($trackingnumber) . "'
				WHERE project_id = '" . intval($pid) . "'
					AND user_id = '" . intval($buyerid) . "'
					AND project_user_id = '" . intval($sellerid) . "'
					AND bid_id = '" . intval($bid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_realtimebids
				SET sellermarkedasshipped = '1',
				sellermarkedasshippeddate = '" . DATETIME24H . "',
				shiptracknumber = '" . $ilance->db->escape_string($trackingnumber) . "'
				WHERE project_id = '" . intval($pid) . "'
					AND user_id = '" . intval($buyerid) . "'
					AND project_user_id = '" . intval($sellerid) . "'
					AND bid_id = '" . intval($bid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
		}
		else if ($mode == 'bid')
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_bids
				SET sellermarkedasshipped = '1',
				sellermarkedasshippeddate = '" . DATETIME24H . "',
				shiptracknumber = '" . $ilance->db->escape_string($trackingnumber) . "'
				WHERE project_id = '" . intval($pid) . "'
					AND user_id = '" . intval($buyerid) . "'
					AND project_user_id = '" . intval($sellerid) . "'
					AND bid_id = '" . intval($bid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_realtimebids
				SET sellermarkedasshipped = '1',
				sellermarkedasshippeddate = '" . DATETIME24H . "',
				shiptracknumber = '" . $ilance->db->escape_string($trackingnumber) . "'
				WHERE project_id = '" . intval($pid) . "'
					AND user_id = '" . intval($buyerid) . "'
					AND project_user_id = '" . intval($sellerid) . "'
					AND bid_id = '" . intval($bid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
		}
		$ilance->email->mail = fetch_user('email', $buyerid);
		$ilance->email->slng = fetch_user_slng($buyerid);
		$ilance->email->get('seller_marked_as_shipped');
		$ilance->email->set(array (
			'{{buyer}}' => fetch_user('username', $buyerid),
			'{{project_id}}' => intval($pid),
			'{{seller}}' => fetch_user('username', $sellerid),
			'{{tracking_number}}' => $trackingnumber,
			'{{project_title}}' => fetch_auction('project_title', $pid),
		));
		$ilance->email->send();
	}
    
	/**
	* Function to fetch region title/name by a country id
	* 
	* This function 
	*
	* @param       integer        country id
	*
	* @return      string         Returns HTML formatted string
	*/
	function fetch_region_by_countryid($countryid = 0, $doformatting = true)
	{
		global $ilance;
		$html = '';
		$slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "r.region_$slng AS region
			FROM " . DB_PREFIX . "locations l
			LEFT JOIN " . DB_PREFIX . "locations_regions r ON (r.regionid = l.regionid)
			WHERE l.locationid = '" . intval($countryid) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$html = $res['region'];
			if ($doformatting)
			{
				$html = str_replace(' ', '_', $html);
				$html = strtolower($html);
			}
		}
		return $html;
	}
    
	/**
	* Function to 
	* 
	* This function 
	*
	* @param       string         region
	*
	* @return      string         Returns HTML formatted string
	*/
	function fetch_countries_by_region_array($region = '')
	{
		global $ilance;
		$slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';
		$query = "";
		if ($region != 'worldwide')
		{
			$returnarray = array();
			$fixedregion = str_replace('_', ' ', $region);
			$fixedregion = ucwords($fixedregion);
			$query = " AND r.region_" . $slng . " = '" . $ilance->db->escape_string($fixedregion) . "'";
		}
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "l.locationid, l.location_$slng AS location, l.cc, r.region_$slng AS region
			FROM " . DB_PREFIX . "locations l
			LEFT JOIN " . DB_PREFIX . "locations_regions r ON (r.regionid = l.regionid)
			WHERE l.visible = '1'
			$query
			ORDER BY l.locationid ASC
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$array['countryid'] = $res['locationid'];
				$array['country'] = $res['location'];
				$array['region'] = strtolower(str_replace(' ', '_', $res['region']));
				$array['iso'] = $res['cc'];
				$returnarray[] = $array;
			}
		}
		return $returnarray;
	}
	/**
	* Function to print the shipping delivery estimate like Wednesday Sep 18 2013 - Wednesday Sep 25 2013 by 8:00pm
	* 
	* @param       integer        listing id
	* @param       string         order date (YYYY-MM-DD HH:MM:SS)
	*
	* @return      string         Returns HTML formatted string
	*/
	function print_delivery_estimate($pid = 0, $orderdate = '')
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT ship_handlingtime
			FROM " . DB_PREFIX . "projects_shipping
			WHERE project_id = '" . intval($pid) . "'
			LIMIT 1
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$datetime1 = $ilance->datetimes->fetch_datetime_from(($res['ship_handlingtime']), $orderdate);
			$temp1 = explode(' ', $datetime1);
			$temp2 = explode('-', $temp1[0]);
			$temp3 = $ilance->datetimes->is_business_day($temp2[0], $temp2[1], $temp2[2]);
			if ($temp3[0])
			{
				$datetime1 = print_date($datetime1, 'l M d Y', 0, 0);
			}
			else
			{
				if ($temp3[1] == '6') // saturday, add 2 days to date
				{
					$datetime1 = $ilance->datetimes->fetch_datetime_from(2, $datetime1);
					$datetime1 = print_date($datetime1, 'l M d Y', 0, 0);
				}
				else if ($temp3[1] == '0') // sunday, add 1 day to date
				{
					$datetime1 = $ilance->datetimes->fetch_datetime_from(1, $datetime1);
					$datetime1 = print_date($datetime1, 'l M d Y', 0, 0);
				}
			}
			unset($temp1, $temp2, $temp3);
			$datetime2 = $ilance->datetimes->fetch_datetime_from(($res['ship_handlingtime'] + 12), $orderdate);
			$temp1 = explode(' ', $datetime2);
			$temp2 = explode('-', $temp1[0]);
			$temp3 = $ilance->datetimes->is_business_day($temp2[0], $temp2[1], $temp2[2]);
			if ($temp3[0])
			{
				$datetime2 = print_date($datetime2, 'l M d Y', 0, 0);
			}
			else
			{
				if ($temp3[1] == '6') // saturday, add 2 days to date
				{
					$datetime2 = $ilance->datetimes->fetch_datetime_from(2, $datetime2);
					$datetime2 = print_date($datetime2, 'l M d Y', 0, 0);
				}
				else if ($temp3[1] == '0') // sunday, add 1 day to date
				{
					$datetime2 = $ilance->datetimes->fetch_datetime_from(1, $datetime2);
					$datetime2 = print_date($datetime2, 'l M d Y', 0, 0);
				}
			}
			unset($temp1, $temp2, $temp3);
			return "$datetime1 &ndash; $datetime2 {_by_lower} 8:00pm";
		}
		else
		{
			// default 1 week threshold
			$datetime1 = $ilance->datetimes->fetch_datetime_from(7, $orderdate);
			$temp1 = explode(' ', $datetime1);
			$temp2 = explode('-', $temp1[0]);
			$temp3 = $ilance->datetimes->is_business_day($temp2[0], $temp2[1], $temp2[2]);
			if ($temp3[0])
			{
				$datetime1 = print_date($datetime1, 'l M d Y', 0, 0);
			}
			else
			{
				if ($temp3[1] == '6') // saturday, add 2 days to date
				{
					$datetime1 = $ilance->datetimes->fetch_datetime_from(2, $datetime1);
					$datetime1 = print_date($datetime1, 'l M d Y', 0, 0);
				}
				else if ($temp3[1] == '0') // sunday, add 1 day to date
				{
					$datetime1 = $ilance->datetimes->fetch_datetime_from(1, $datetime1);
					$datetime1 = print_date($datetime1, 'l M d Y', 0, 0);
				}
			}
			unset($temp1, $temp2, $temp3);
			$datetime2 = $ilance->datetimes->fetch_datetime_from(12, $orderdate);
			$temp1 = explode(' ', $datetime2);
			$temp2 = explode('-', $temp1[0]);
			$temp3 = $ilance->datetimes->is_business_day($temp2[0], $temp2[1], $temp2[2]);
			if ($temp3[0])
			{
				$datetime2 = print_date($datetime2, 'l M d Y', 0, 0);
			}
			else
			{
				if ($temp3[1] == '6') // saturday, add 2 days to date
				{
					$datetime2 = $ilance->datetimes->fetch_datetime_from(2, $datetime2);
					$datetime2 = print_date($datetime2, 'l M d Y', 0, 0);
				}
				else if ($temp3[1] == '0') // sunday, add 1 day to date
				{
					$datetime2 = $ilance->datetimes->fetch_datetime_from(1, $datetime2);
					$datetime2 = print_date($datetime2, 'l M d Y', 0, 0);
				}
			}
			unset($temp1, $temp2, $temp3);
			return "$datetime1 &ndash; $datetime2 {_by_lower} 8:00pm";
		}
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>