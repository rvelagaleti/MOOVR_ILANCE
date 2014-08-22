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
* Shipping AJAX functions for iLance
*
* @package      iLance\Global\AJAX\Shipping
* @version      4.0.0.8059
* @author       ILance
*/

/**
* Function to print the ajax shipping services via AJAX on the listing page
* 
* This function will fetch the shipping service rows for an ajax related call from a listing page
*
* @param       integer        shipping row number
* @param       integer        listing id
* @param       string         country title
* $param       string         region title
* @param       integer        quantity
* @param       boolean        show verbose qty as string (example: C$9.95 (QTY x 3) vs. C$9.95) default true
* @param       string         zip code
* @param       string         state
* @param       string         city
*
* @return      string         Returns HTML formatted string of payment method output or radio button input logic
*/
function fetch_ajax_ship_service_row($row = 0, $pid = 0, $countrytitle = '', $region = '', $qty = 1, $vqty = 1, $zipcode = '', $state = '', $city = '')
{
	global $ilance, $phrase, $ilconfig;
	$html = $country = $qtystring = '';
	$delivery = '';
	if ($row > 0)
	{
		$result = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "d.ship_options_$row AS location, d.ship_service_$row AS shipperid, d.ship_packagetype_$row AS packagetype, d.ship_pickuptype_$row AS pickuptype, d.ship_fee_$row AS cost, d.ship_fee_next_$row AS cost_next, d.freeshipping_$row AS freeshipping, s.ship_method, s.ship_handlingtime, s.ship_handlingfee, s.ship_length, s.ship_width, s.ship_height, s.ship_weightlbs, s.ship_weightoz, p.countryid, p.country, p.state, p.city, p.zipcode, p.currencyid
			FROM " . DB_PREFIX . "projects_shipping_destinations d
			LEFT JOIN " . DB_PREFIX . "projects_shipping s ON (d.project_id = s.project_id)
			LEFT JOIN " . DB_PREFIX . "projects p ON (d.project_id = p.project_id)
			WHERE d.project_id = '" . intval($pid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($result) > 0)
		{
			$res = $ilance->db->fetch_array($result, DB_ASSOC);
			$currencyid = $res['currencyid'];
			$delivery .= '<div title="' . ((isset($_SESSION['ilancedata']['user']['country']) AND isset($countrytitle) AND $countrytitle == $_SESSION['ilancedata']['user']['country']) ? '{_domestic_services_get_to_your_location_fast}' : '{_varies_for_items_shipped_from_international_locations}') . '">' . $ilance->shipping->print_delivery_estimate($pid, DATETIME24H) . '</div>';
			$delivery .= '<div class="smaller litegray" style="padding-top:2px">{_seller_ships_within} <span id="ship_shipdays_' . $row . '">' . $res['ship_handlingtime'] . '</span> ' . (($res['ship_handlingtime'] == '1') ? '{_day_lower}' : '{_days_lower}') . ' {_of_cleared_payment}</div>';
			$country = $countrytitle;
			$service = ($res['shipperid'] > 0) ? $ilance->shipping->print_shipping_partner($res['shipperid']) : '-';
			if ($res['freeshipping'])
			{
				if ($res['ship_handlingfee'] > 0)
				{
					$price = $ilance->currency->format($res['ship_handlingfee'], $res['currencyid']) . ' {_handling_fee}';
				}
				else
				{
					$price = '<span style="text-transform:uppercase"><strong>{_free}</strong></span>';
				}
			}
			else
			{
				if ($res['ship_method'] == 'flatrate')
				{
					$zipcode = !empty($zipcode) ? strtoupper($zipcode) : '';
					$state = !empty($state) ? $state : '';
					$price = '';
					if ($qty == 1)
					{
						$price = $ilance->currency->format($res['cost'], $res['currencyid']);
						if ($res['ship_handlingfee'] > 0)
						{
							$price .= ' <span title="{_handling_fee}">+ ' . $ilance->currency->format($res['ship_handlingfee'], $res['currencyid']) . '</span>';
						}
					}
					else if ($qty > 1)
					{
						$price = $ilance->currency->format(($res['cost'] + ($res['cost_next'] * (intval($qty) - 1))), $res['currencyid']) ;
						if ($res['ship_handlingfee'] > 0)
						{
							$price .= ' <span title="{_handling_fee}">+ ' . $ilance->currency->format($res['ship_handlingfee'], $res['currencyid']) . '</span>';
						}
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
					$zipcode = !empty($zipcode) ? strtoupper($zipcode) : '';
					$state = !empty($state) ? $state : '';
					$city = !empty($city) ? $city : '';
					$countryshort = !empty($countrytitle) ? $ilance->common_location->print_country_name(0, $_SESSION['ilancedata']['user']['slng'], true, $countrytitle) : $_SESSION['ilancedata']['user']['countryshort'];
					$shipinfo = array('weight' => $res['ship_weightlbs'],
						'destination_zipcode' => $zipcode,
						'destination_state' => $state,
						'destination_city' => $city,
						'destination_country' => $countryshort,
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
						$full_price = ($price * $qty);
						$price = '<span id="ship_handling_working_' . $row . '" title="Shipping price to your location"><strong>' . $ilance->currency->format(sprintf("%01.2f", $full_price), $res['currencyid']) . '</strong></span>';
					}
					else if (isset($rates['errordesc']))
					{
						$price = '<span id="ship_handling_working_' . $row . '" title="' . handle_input_keywords($rates['errordesc']) . '">{_unknown}</span>';
					}
					else
					{
						$price = '<span id="ship_handling_working_' . $row . '" title="Could not determine shipping price to your location, please contact seller.">{_unknown}</span>';
					}
				}
				else
				{
					$price = '{_local_pickup_only}';	
				}
			}
			if ($qty > 1 AND $res['freeshipping'] <= 0)
			{
				$qtystring = '';
				if ($vqty)
				{
					$qtystring = '&nbsp;(<span title="{_quantity}">{_qty}</span> x ' . $qty . ')';
				}
			}
			$html .= $price . $qtystring . "~~~~$country" . (!empty($state) ? ', ' . $state : '') . (!empty($city) ? ', ' . $city : '') . (!empty($zipcode) ? ', ' . $zipcode : '') . "~~~~$service~~~~$delivery";
		}
	}
	$ilance->template->templateregistry['ship_service_row'] = $html;
	return $ilance->template->parse_template_phrases('ship_service_row');
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>