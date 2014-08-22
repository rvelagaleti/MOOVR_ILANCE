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
* Shipping Calculator class to perform the majority of realtime shipping rate calculations from FedEx, UPS and USPS in ILance.
*
* @package      iLance\Shipping\Calculator
* @version      4.0.0.8059
* @author       ILance
*/
class shipcalculator
{
	public $wsdl;
	public $cachedataexpiry = 7;
	public $date_cachedataexpiry;
	private $fedex_response = '';
        function __construct()
        {
		global $ilance;
		$this->date_cachedataexpiry = $ilance->datetimes->fetch_datetime_from_timestamp(TIMESTAMPNOW - ($this->cachedataexpiry * 24 * 3600));
		$this->wsdl = DIR_WDSL . 'fedex/RateService_v13.wsdl';
        }
        
	/**
	* Function to initalize the soap object based on defined wsdl document.
	*
	* @return	object		Returns a valid soap client object
	*/
	function initialize_soap()
	{
		ini_set('soap.wsdl_cache_enabled', '0');
		return new SoapClient($this->wsdl, array('trace' => 1, 'exception' => 0));
	}
	
	function fedex_write_log($client)
	{
		return false;
		if (!$logfile = fopen(SHIPPINGLOG . 'fedex.txt', 'a'))
		{
			error_func("Cannot open " . SHIPPINGLOG . "fedex.txt file.\n", 0);
			exit(1);
		}
		fwrite($logfile, sprintf("\r%s:- %s",date("D M j G:i:s T Y"), $client->__getLastRequest(). "\n\n" . $client->__getLastResponse()));
	}

	function fedex_print_request_response($client)
	{
		$result = array(
			'request' => $client->__getLastRequest(),
			'response' => $client->__getLastResponse()
		);
		return $result;
	}
	
	/**
	* Function to connect to the selected shipping web service API to send and receive a response.
	*
	* @param       string       ship service url
	* @param       string       string data to send to the web service
	*
	* @return      string       Returns a request response from the shipping service API
	*/
	function connect($url = '', $data = '')
        {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);  
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		if (!empty($data))
                {
			curl_setopt($ch, CURLOPT_POST, 1);  
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}  
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		$contents = curl_exec($ch);
		curl_close($ch);
		return $contents;
	}
	
        function get_rates($shipinfo = array()) // 1
	{
                $rates = array();
                if (isset($shipinfo['carriers']['fedex']) AND $shipinfo['carriers']['fedex'] AND $fedex = $this->get_fedex_rates($shipinfo))
                {
                        $rates = array_merge_recursive($rates, $fedex);
                }
                if (isset($shipinfo['carriers']['ups']) AND $shipinfo['carriers']['ups'] AND $ups = $this->get_ups_rates($shipinfo))
                {
                        $rates = array_merge_recursive($rates, $ups);
                }
                if (isset($shipinfo['carriers']['usps']) AND $shipinfo['carriers']['usps'] AND $usps = $this->get_usps_rates($shipinfo))
                {
                        $rates = array_merge_recursive($rates, $usps);
                }
                return $rates;
        }
        
        function get_fedex_rates($shipinfo = array()) // 2
	{
                $services = false;
                $rates = $this->fedex_rateshop($shipinfo);
		if (isset($rates->HighestSeverity))
		{
			if ($rates->HighestSeverity != 'FAILURE' AND $rates->HighestSeverity != 'ERROR')
			{
				if (is_array($rates->RateReplyDetails->RatedShipmentDetails))
				{
					for ($i = 0; $i < sizeof($rates->RateReplyDetails->RatedShipmentDetails); $i++)
					{
						if (isset($rates->RateReplyDetails->RatedShipmentDetails[$i]->ShipmentRateDetail->TotalNetCharge->Amount))
						{
							$services["carrier"][$i] = 'fedex';
							$services["code"][$i] = $rates->RateReplyDetails->ServiceType;
							$services["name"][$i] = $rates->RateReplyDetails->ServiceType;
							$services["price"][$i] = isset($rates->RateReplyDetails->RatedShipmentDetails[$i]->ShipmentRateDetail->TotalNetCharge->Amount) ? $rates->RateReplyDetails->RatedShipmentDetails[$i]->ShipmentRateDetail->TotalNetCharge->Amount : ''; // 14.53
							$services['currency'][$i] = isset($rates->RateReplyDetails->RatedShipmentDetails[$i]->ShipmentRateDetail->TotalNetCharge->Currency) ? $rates->RateReplyDetails->RatedShipmentDetails[$i]->ShipmentRateDetail->TotalNetCharge->Currency : ''; // CAD
							$services['transit'][$i] = isset($rates->RateReplyDetails->TransitTime) ? $rates->RateReplyDetails->TransitTime : '';
						}
					}
				}
				else
				{
					$i = 0;
					$services["carrier"][$i] = 'fedex';
					$services["code"][$i] = $rates->RateReplyDetails->ServiceType;
					$services["name"][$i] = $rates->RateReplyDetails->ServiceType;
					$services["price"][$i] = isset($rates->RateReplyDetails->RatedShipmentDetails->ShipmentRateDetail->TotalNetCharge->Amount) ? $rates->RateReplyDetails->RatedShipmentDetails->ShipmentRateDetail->TotalNetCharge->Amount : ''; // 14.53
					$services['currency'][$i] = isset($rates->RateReplyDetails->RatedShipmentDetails->ShipmentRateDetail->TotalNetCharge->Currency) ? $rates->RateReplyDetails->RatedShipmentDetails->ShipmentRateDetail->TotalNetCharge->Currency : ''; // CAD
					$services['transit'][$i] = isset($rates->RateReplyDetails->TransitTime) ? $rates->RateReplyDetails->TransitTime : ''; // TWO_DAYS
				}

			}
			else
			{
				$services["errorcode"] = '';
				$services["errordesc"] = isset($rates->Notifications->Message) ? $rates->Notifications->Message : '';
			}
		}
		else
		{
			$services["errorcode"] = '';
                        $services["errordesc"] = '';
		}
                return $services;
        }
        
        function get_usps_rates($shipinfo = array()) // 2
        {
		global $ilance;
                $services = false;
                $options = array();
                $sql = $ilance->db->query("
                        SELECT shipcode, title
                        FROM " . DB_PREFIX . "shippers
                        WHERE carrier = 'usps'
                        ORDER BY sort ASC
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                $options[$res['shipcode']] = $res['title'];
                        }
                }
                $rates = $this->usps_rateshop($shipinfo);
                $array = $this->xmlize($rates);
                if (isset($array["RateResponse"]["#"]["Package"]))
                {
                        $parser = $array["RateResponse"]["#"]["Package"];
                        for ($i = 0; $i < sizeof($parser); $i++)
                        {
                                if (isset($options[$parser[$i]["#"]["Service"][0]["#"]]))
                                {
                                        $services["carrier"][$i] = 'usps';
                                        $services["code"][$i] = $parser[$i]["#"]["Service"][0]["#"];
                                        $services["name"][$i] = $options[$parser[$i]["#"]["Service"][0]["#"]];
                                        $services["price"][$i] = $parser[$i]["#"]["Postage"][0]["#"];
                                }
                        }
                }
                else
                {
                        if (isset($array["Error"]["#"]["Description"]) AND !empty($array["Error"]["#"]["Description"][0]["#"]))
                        {
                                $services["errorcode"] = $array["Error"]["#"]["Number"][0]["#"];
                                $services["errordesc"] = $array["Error"]["#"]["Description"][0]["#"];
                        }
			else
			{
				$services["errorcode"] = '';
				$services["errordesc"] = '';
			}
                }
                return $services;
        }
        
        function get_ups_rates($shipinfo = array()) // 2
        {
                global $ilance;
                $services = false;
                $options = array();
                $sql = $ilance->db->query("
                        SELECT shipcode, title
                        FROM " . DB_PREFIX . "shippers
                        WHERE carrier = 'ups'
                        ORDER BY sort ASC
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                $options[$res['shipcode']] = $res['title'];
                        }
                }
                $rates = $this->ups_rateshop($shipinfo);
                $array = $this->xmlize($rates);
                if (isset($array["RatingServiceSelectionResponse"]["#"]["Response"][0]["#"]["ResponseStatusCode"][0]["#"]))
                {
                        if ($array["RatingServiceSelectionResponse"]["#"]["Response"][0]["#"]["ResponseStatusCode"][0]["#"] > 0)
                        {
                                $parser = $array["RatingServiceSelectionResponse"]["#"]["RatedShipment"];
                                for ($i = 0; $i < sizeof($parser); $i++)
                                {
                                        if (isset($options[$parser[$i]["#"]["Service"][0]["#"]["Code"][0]["#"]]))
                                        {
                                                $services["carrier"][$i] = 'ups';
                                                $services["code"][$i] = $parser[$i]["#"]["Service"][0]["#"]["Code"][0]["#"];
                                                $services["name"][$i] = $options[$parser[$i]["#"]["Service"][0]["#"]["Code"][0]["#"]];
                                                $services["price"][$i] = $parser[$i]["#"]["TotalCharges"][0]["#"]["MonetaryValue"][0]["#"];
						$services["currency"][$i] = $parser[$i]["#"]["TotalCharges"][0]["#"]["CurrencyCode"][0]["#"];
                                        }
                                }
                        }
                        else
                        {
                                $services["errorcode"] = $array["RatingServiceSelectionResponse"]["#"]["Response"][0]["#"]["Error"][0]["#"]["ErrorCode"][0]["#"];
                                $services["errordesc"] = $array["RatingServiceSelectionResponse"]["#"]["Response"][0]["#"]["Error"][0]["#"]["ErrorDescription"][0]["#"];
                        }
                }
		else
		{
			$services["errorcode"] = '';
                        $services["errordesc"] = '';
		}
                return $services;
        }
        
        function ups_rateshop($shipinfo = array()) // 3
        {
                global $ilance, $ilconfig;
                $shipinfo['weight'] = ($shipinfo['weight'] < 1) ? 1.0 : number_format($shipinfo['weight'], 1, '.', '');
                $sql = $ilance->db->query("
                        SELECT gatewayresult
                        FROM " . DB_PREFIX . "shipping_rates_cache
                        WHERE carrier = 'ups'
                                AND shipcode = '" . $ilance->db->escape_string($shipinfo['shipcode']) . "'
                                AND from_country = '" . $ilance->db->escape_string($shipinfo['origin_country']) . "'
				AND from_state = '" . $ilance->db->escape_string($shipinfo['origin_state']) . "'
				AND from_city = '" . $ilance->db->escape_string($shipinfo['origin_city']) . "'
                                AND from_zipcode = '" . $ilance->db->escape_string($shipinfo['origin_zipcode']) . "'
                                AND to_country = '" . $ilance->db->escape_string($shipinfo['destination_country']) . "'
				AND to_state = '" . $ilance->db->escape_string($shipinfo['destination_state']) . "'
				AND to_city = '" . $ilance->db->escape_string($shipinfo['destination_city']) . "'
                                AND to_zipcode = '" . $ilance->db->escape_string($shipinfo['destination_zipcode']) . "'
                                AND weight = '" . $ilance->db->escape_string($shipinfo['weight']) . "'
                                AND weightunit = '" . $ilance->db->escape_string($shipinfo['weightunit']) . "'
                                AND dimensionunit = '" . $ilance->db->escape_string($shipinfo['dimensionunit']) . "'
                                AND length = '" . $ilance->db->escape_string($shipinfo['length']) . "'
                                AND width = '" . $ilance->db->escape_string($shipinfo['width']) . "'
                                AND height = '" . $ilance->db->escape_string($shipinfo['height']) . "'
                                AND pickuptype = '" . $ilance->db->escape_string($shipinfo['pickuptype']) . "'
                                AND packagetype = '" . $ilance->db->escape_string($shipinfo['packagingtype']) . "'
                                AND size = '" . $ilance->db->escape_string($shipinfo['sizecode']) . "'
				AND datetime > '" . $ilance->db->escape_string($this->date_cachedataexpiry) . "'
				
                ");
                if ($ilance->db->num_rows($sql) == 0)
                {
                        $shipinfo['packagingtype'] = ($shipinfo['packagingtype'] <= 0) ? '02' : $shipinfo['packagingtype'];
                        $shipinfo['pickuptype'] = ($shipinfo['pickuptype'] <= 0) ? '01' : $shipinfo['pickuptype'];
                        $request = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>
<AccessRequest xml:lang=\"en-US\">
        <AccessLicenseNumber><![CDATA[" . $ilconfig['ups_access_id'] . "]]></AccessLicenseNumber>
        <UserId><![CDATA[" . $ilconfig['ups_username'] . "]]></UserId>
        <Password><![CDATA[" . $ilconfig['ups_password'] . "]]></Password>
</AccessRequest>
<RatingServiceSelectionRequest xml:lang=\"en-US\">
<Request>
        <TransactionReference>
                <CustomerContext>Rating and Service</CustomerContext>
                <XpciVersion>1.0001</XpciVersion>
        </TransactionReference>
        <RequestAction>Rate</RequestAction>
        <RequestOption>shop</RequestOption>
</Request>
<PickupType>
        <Code><![CDATA[$shipinfo[pickuptype]]]></Code>
</PickupType>
<Shipment>
        <Shipper>
                <Address>
                        <CountryCode><![CDATA[$shipinfo[origin_country]]]></CountryCode>
                        <PostalCode><![CDATA[$shipinfo[origin_zipcode]]]></PostalCode>
                </Address>
        </Shipper>
        <ShipTo>
                <Address>
                        <CountryCode><![CDATA[$shipinfo[destination_country]]]></CountryCode>
                        <ResidentialAddress>1</ResidentialAddress>
                        <PostalCode><![CDATA[$shipinfo[destination_zipcode]]]></PostalCode>
                </Address>
        </ShipTo>
        <Service>
                <Code><![CDATA[$shipinfo[shipcode]]]></Code>
        </Service>
        <Package>
                <OversizePackage>0</OversizePackage>
                <Dimensions>
                        <UnitOfMeasurement>
                                <Code><![CDATA[$shipinfo[dimensionunit]]]></Code>
                        </UnitOfMeasurement>
                        <Length><![CDATA[$shipinfo[length]]]></Length>
                        <Width><![CDATA[$shipinfo[width]]]></Width>
                        <Height><![CDATA[$shipinfo[height]]]></Height>
                </Dimensions>
                <PackagingType>
                        <Code><![CDATA[$shipinfo[packagingtype]]]></Code>
                        <Description>Package</Description>
                </PackagingType>
                <Description>Rate Shopping</Description>
                <PackageWeight>
                        <UnitOfMeasurement>
                                <Code><![CDATA[$shipinfo[weightunit]]]></Code>
                                <Description>Pounds</Description>
                        </UnitOfMeasurement>
                        <Weight><![CDATA[$shipinfo[weight]]]></Weight>
                </PackageWeight>
        </Package>
<ShipmentServiceOptions/>
</Shipment>
</RatingServiceSelectionRequest>";
                        $result = $this->connect($ilconfig['ups_server'], $request);
			if (!empty($result))
			{
				$array = $this->xmlize($result);
				if (is_array($array) AND !isset($array["RatingServiceSelectionResponse"]["#"]["Response"][0]["#"]["Error"][0]["#"]["ErrorCode"][0]["#"]) AND !isset($array["RatingServiceSelectionResponse"]["#"]["Response"][0]["#"]["Error"][0]["#"]["ErrorDescription"][0]["#"]))
				{
					$ilance->db->query("
						INSERT INTO " . DB_PREFIX . "shipping_rates_cache
						(carrier, shipcode, from_country, from_state, from_city, from_zipcode, to_country, to_state, to_city, to_zipcode, weight, weightunit, dimensionunit, length, width, height, pickuptype, packagetype, size, datetime, gatewayrequest, gatewayresult)
						VALUES(
						'ups',
						'" . $ilance->db->escape_string($shipinfo['shipcode']) . "',
						'" . $ilance->db->escape_string($shipinfo['origin_country']) . "',
						'" . $ilance->db->escape_string($shipinfo['origin_state']) . "',
						'" . $ilance->db->escape_string($shipinfo['origin_city']) . "',
						'" . $ilance->db->escape_string($shipinfo['origin_zipcode']) . "',
						'" . $ilance->db->escape_string($shipinfo['destination_country']) . "',
						'" . $ilance->db->escape_string($shipinfo['destination_state']) . "',
						'" . $ilance->db->escape_string($shipinfo['destination_city']) . "',
						'" . $ilance->db->escape_string($shipinfo['destination_zipcode']) . "',
						'" . $ilance->db->escape_string($shipinfo['weight']) . "',
						'" . $ilance->db->escape_string($shipinfo['weightunit']) . "',
						'" . $ilance->db->escape_string($shipinfo['dimensionunit']) . "',
						'" . $ilance->db->escape_string($shipinfo['length']) . "',
						'" . $ilance->db->escape_string($shipinfo['width']) . "',
						'" . $ilance->db->escape_string($shipinfo['height']) . "',
						'" . $ilance->db->escape_string($shipinfo['pickuptype']) . "',
						'" . $ilance->db->escape_string($shipinfo['packagingtype']) . "',
						'" . $ilance->db->escape_string($shipinfo['sizecode']) . "',
						'" . DATETIME24H . "',
						'" . $ilance->db->escape_string($request) . "',
						'" . $ilance->db->escape_string($result) . "')
					");
				}
				unset($array);
			}
                }
                else
                {
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "shipping_rates_cache
                                SET traffic = traffic + 1
                                WHERE carrier = 'ups'
                                        AND shipcode = '" . $ilance->db->escape_string($shipinfo['shipcode']) . "'
                                        AND from_country = '" . $ilance->db->escape_string($shipinfo['origin_country']) . "'
                                        AND from_state = '" . $ilance->db->escape_string($shipinfo['origin_state']) . "'
					AND from_city = '" . $ilance->db->escape_string($shipinfo['origin_city']) . "'
                                        AND from_zipcode = '" . $ilance->db->escape_string($shipinfo['origin_zipcode']) . "'
                                        AND to_country = '" . $ilance->db->escape_string($shipinfo['destination_country']) . "'
                                        AND to_state = '" . $ilance->db->escape_string($shipinfo['destination_state']) . "'
					AND to_city = '" . $ilance->db->escape_string($shipinfo['destination_city']) . "'
                                        AND to_zipcode = '" . $ilance->db->escape_string($shipinfo['destination_zipcode']) . "'
                                        AND weight = '" . $ilance->db->escape_string($shipinfo['weight']) . "'
                                        AND weightunit = '" . $ilance->db->escape_string($shipinfo['weightunit']) . "'
                                        AND dimensionunit = '" . $ilance->db->escape_string($shipinfo['dimensionunit']) . "'
                                        AND length = '" . $ilance->db->escape_string($shipinfo['length']) . "'
                                        AND width = '" . $ilance->db->escape_string($shipinfo['width']) . "'
                                        AND height = '" . $ilance->db->escape_string($shipinfo['height']) . "'
                                        AND pickuptype = '" . $ilance->db->escape_string($shipinfo['pickuptype']) . "'
                                        AND packagetype = '" . $ilance->db->escape_string($shipinfo['packagingtype']) . "'
                                        AND size = '" . $ilance->db->escape_string($shipinfo['sizecode']) . "'
                        ");
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        $result = $res['gatewayresult'];
                }                
                return $result;
        }
        
        function usps_rateshop($shipinfo = array()) // 3
        {
                global $ilance, $ilconfig;
                $shipinfo['weight'] = ($shipinfo['weight'] < 1) ? 1.0 : number_format($shipinfo['weight'], 1, '.', '');
                $sql = $ilance->db->query("
                        SELECT gatewayresult
                        FROM " . DB_PREFIX . "shipping_rates_cache
                        WHERE carrier = 'usps'
                                AND shipcode = '" . $ilance->db->escape_string($shipinfo['shipcode']) . "'
                                AND from_country = '" . $ilance->db->escape_string($shipinfo['origin_country']) . "'
				AND from_state = '" . $ilance->db->escape_string($shipinfo['origin_state']) . "'
				AND from_city = '" . $ilance->db->escape_string($shipinfo['origin_city']) . "'
                                AND from_zipcode = '" . $ilance->db->escape_string($shipinfo['origin_zipcode']) . "'
                                AND to_country = '" . $ilance->db->escape_string($shipinfo['destination_country']) . "'
				AND to_state = '" . $ilance->db->escape_string($shipinfo['destination_state']) . "'
				AND to_city = '" . $ilance->db->escape_string($shipinfo['destination_city']) . "'
                                AND to_zipcode = '" . $ilance->db->escape_string($shipinfo['destination_zipcode']) . "'
                                AND weight = '" . $ilance->db->escape_string($shipinfo['weight']) . "'
                                AND weightunit = '" . $ilance->db->escape_string($shipinfo['weightunit']) . "'
                                AND dimensionunit = '" . $ilance->db->escape_string($shipinfo['dimensionunit']) . "'
                                AND length = '" . $ilance->db->escape_string($shipinfo['length']) . "'
                                AND width = '" . $ilance->db->escape_string($shipinfo['width']) . "'
                                AND height = '" . $ilance->db->escape_string($shipinfo['height']) . "'
                                AND pickuptype = '" . $ilance->db->escape_string($shipinfo['pickuptype']) . "'
                                AND packagetype = '" . $ilance->db->escape_string($shipinfo['packagingtype']) . "'
                                AND size = '" . $ilance->db->escape_string($shipinfo['sizecode']) . "'
				AND datetime > '" . $ilance->db->escape_string($this->date_cachedataexpiry) . "'
                ");
                if ($ilance->db->num_rows($sql) == 0)
                {
                        $request = 'API=RateV4&XML=<RateV4Request USERID="' . $ilconfig['usps_login'] . '" PASSWORD="' . $ilconfig['usps_password'] . '">';
                        $sql = $ilance->db->query("
                                SELECT shipcode, title
                                FROM " . DB_PREFIX . "shippers
                                WHERE carrier = 'usps'
                                ORDER BY sort ASC
                        ");
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $count = 0;
                                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                                {
                                        $container = '';
                                        $request .= '<Package ID="' . $count . '">
        <Service>' . $res['shipcode'] . '</Service>
        <ZipOrigination>' . $shipinfo['origin_zipcode'] . '</ZipOrigination>
        <ZipDestination>' . $shipinfo['destination_zipcode'] . '</ZipDestination>
        <Pounds>' . $shipinfo['weight'] . '</Pounds>
        <Ounces>0</Ounces>
        <Size>' . $shipinfo['sizecode'] . '</Size>
        <Machinable>' . $this->machinable($res['shipcode']) . '</Machinable>
</Package>';
                                        $count++;
                                }
                        }
                        $request .= '</RateV4Request>';
                        $result = $this->connect($ilconfig['usps_server'], $request);
			if (!empty($result))
			{
				$array = $this->xmlize($result);
				if (is_array($array) AND !isset($array["Error"]["#"]["Description"]) AND empty($array["Error"]["#"]["Description"][0]["#"]))
				{
					$ilance->db->query("
						INSERT INTO " . DB_PREFIX . "shipping_rates_cache
						(carrier, shipcode, from_country, from_state, from_city, from_zipcode, to_country, to_state, to_city, to_zipcode, weight, weightunit, dimensionunit, length, width, height, pickuptype, packagetype, size, datetime, gatewayrequest, gatewayresult)
						VALUES(
						'usps',
						'" . $ilance->db->escape_string($shipinfo['shipcode']) . "',
						'" . $ilance->db->escape_string($shipinfo['origin_country']) . "',
						'" . $ilance->db->escape_string($shipinfo['origin_state']) . "',
						'" . $ilance->db->escape_string($shipinfo['origin_city']) . "',
						'" . $ilance->db->escape_string($shipinfo['origin_zipcode']) . "',
						'" . $ilance->db->escape_string($shipinfo['destination_country']) . "',
						'" . $ilance->db->escape_string($shipinfo['destination_state']) . "',
						'" . $ilance->db->escape_string($shipinfo['destination_city']) . "',
						'" . $ilance->db->escape_string($shipinfo['destination_zipcode']) . "',
						'" . $ilance->db->escape_string($shipinfo['weight']) . "',
						'" . $ilance->db->escape_string($shipinfo['weightunit']) . "',
						'" . $ilance->db->escape_string($shipinfo['dimensionunit']) . "',
						'" . $ilance->db->escape_string($shipinfo['length']) . "',
						'" . $ilance->db->escape_string($shipinfo['width']) . "',
						'" . $ilance->db->escape_string($shipinfo['height']) . "',
						'" . $ilance->db->escape_string($shipinfo['pickuptype']) . "',
						'" . $ilance->db->escape_string($shipinfo['packagingtype']) . "',
						'" . $ilance->db->escape_string($shipinfo['sizecode']) . "',
						'" . DATETIME24H . "',
						'" . $ilance->db->escape_string($request) . "',
						'" . $ilance->db->escape_string($result) . "')
					");
				}
				unset($array);
			}
                }
                else
                {
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "shipping_rates_cache
                                SET traffic = traffic + 1
                                WHERE carrier = 'usps'
                                        AND shipcode = '" . $ilance->db->escape_string($shipinfo['shipcode']) . "'
                                        AND from_country = '" . $ilance->db->escape_string($shipinfo['origin_country']) . "'
                                        AND from_state = '" . $ilance->db->escape_string($shipinfo['origin_state']) . "'
					AND from_city = '" . $ilance->db->escape_string($shipinfo['origin_city']) . "'
                                        AND from_zipcode = '" . $ilance->db->escape_string($shipinfo['origin_zipcode']) . "'
                                        AND to_country = '" . $ilance->db->escape_string($shipinfo['destination_country']) . "'
                                        AND to_state = '" . $ilance->db->escape_string($shipinfo['destination_state']) . "'
					AND to_city = '" . $ilance->db->escape_string($shipinfo['destination_city']) . "'
                                        AND to_zipcode = '" . $ilance->db->escape_string($shipinfo['destination_zipcode']) . "'
                                        AND weight = '" . $ilance->db->escape_string($shipinfo['weight']) . "'
                                        AND weightunit = '" . $ilance->db->escape_string($shipinfo['weightunit']) . "'
                                        AND dimensionunit = '" . $ilance->db->escape_string($shipinfo['dimensionunit']) . "'
                                        AND length = '" . $ilance->db->escape_string($shipinfo['length']) . "'
                                        AND width = '" . $ilance->db->escape_string($shipinfo['width']) . "'
                                        AND height = '" . $ilance->db->escape_string($shipinfo['height']) . "'
                                        AND pickuptype = '" . $ilance->db->escape_string($shipinfo['pickuptype']) . "'
                                        AND packagetype = '" . $ilance->db->escape_string($shipinfo['packagingtype']) . "'
                                        AND size = '" . $ilance->db->escape_string($shipinfo['sizecode']) . "'
                        ");
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        $result = $res['gatewayresult'];
                }
                return $result;
        }
        
        function fedex_rateshop($shipinfo = array()) // 3
	{
                global $ilance, $ilconfig;
                $shipinfo['weight'] = ($shipinfo['weight'] < 1) ? 1.0 : number_format($shipinfo['weight'], 1, '.', '');
		if (isset($shipinfo['origin_state']) AND mb_strlen($shipinfo['origin_state']) > 2)
		{
			$shipinfo['origin_state'] = $ilance->common_location->fetch_state_abbreviation($shipinfo['origin_state']);
		}
		if (isset($shipinfo['destination_state']) AND mb_strlen($shipinfo['destination_state']) > 2)
		{
			$shipinfo['destination_state'] = $ilance->common_location->fetch_state_abbreviation($shipinfo['destination_state']);
		}
                $sql = $ilance->db->query("
                        SELECT gatewayresult
                        FROM " . DB_PREFIX . "shipping_rates_cache
                        WHERE carrier = 'fedex'
                                AND shipcode = '" . $ilance->db->escape_string($shipinfo['shipcode']) . "'
                                AND from_country = '" . $ilance->db->escape_string($shipinfo['origin_country']) . "'
                                AND from_state = '" . $ilance->db->escape_string($shipinfo['origin_state']) . "'
				AND from_city = '" . $ilance->db->escape_string($shipinfo['origin_city']) . "'
                                AND from_zipcode = '" . $ilance->db->escape_string($shipinfo['origin_zipcode']) . "'
                                AND to_country = '" . $ilance->db->escape_string($shipinfo['destination_country']) . "'
                                AND to_state = '" . $ilance->db->escape_string($shipinfo['destination_state']) . "'
				AND to_city = '" . $ilance->db->escape_string($shipinfo['destination_city']) . "'
                                AND to_zipcode = '" . $ilance->db->escape_string($shipinfo['destination_zipcode']) . "'
                                AND weight = '" . $ilance->db->escape_string($shipinfo['weight']) . "'
                                AND weightunit = '" . $ilance->db->escape_string($shipinfo['weightunit']) . "'
                                AND dimensionunit = '" . $ilance->db->escape_string($shipinfo['dimensionunit']) . "'
                                AND length = '" . $ilance->db->escape_string($shipinfo['length']) . "'
                                AND width = '" . $ilance->db->escape_string($shipinfo['width']) . "'
                                AND height = '" . $ilance->db->escape_string($shipinfo['height']) . "'
                                AND pickuptype = '" . $ilance->db->escape_string($shipinfo['pickuptype']) . "'
                                AND packagetype = '" . $ilance->db->escape_string($shipinfo['packagingtype']) . "'
                                AND size = '" . $ilance->db->escape_string($shipinfo['sizecode']) . "'
				AND datetime > '" . $ilance->db->escape_string($this->date_cachedataexpiry) . "'
                ");
                if ($ilance->db->num_rows($sql) == 0)
                {
			$client = $this->initialize_soap();
			$request = array();
			$details = array(
				'shipper' => array(
					'Contact' => array(
						'PersonName'  => 'Sender Name',
						'CompanyName' => 'Company Name'
					),
					'Address' => array(
						'StreetLines' 		=> array(), //array('139 Basaltic Road'),
						'City'       		=> $shipinfo['origin_city'],
						'StateOrProvinceCode' 	=> $shipinfo['origin_state'],
						'PostalCode' 		=> $shipinfo['origin_zipcode'],
						'CountryCode' 		=> $shipinfo['origin_country']
					)
				),
				'recipient' => array(
					'Contact' => array(
						'PersonName'  => 'Sender Name',
						'CompanyName' => 'Company Name'
					),
					'Address' => array(
						'StreetLines' 		=> array(), //array('139 Basaltic Road'),
						'City'       		=> $shipinfo['destination_city'],
						'StateOrProvinceCode' 	=> $shipinfo['destination_state'],
						'PostalCode' 		=> $shipinfo['destination_zipcode'],
						'CountryCode' 		=> $shipinfo['destination_country'],
						'Residential' 		=> true // $shipinfo['destination_residential']
					)
				),
				'sequence_number' => 1,
				'group_package_count' => 1,
				'weight' => array(
					'value' => $shipinfo['weight'],
					'units' => $shipinfo['weightunit']
				),
				'dimensions' => array(
					'length' => $shipinfo['length'],
					'width'	 => $shipinfo['width'],
					'height' => $shipinfo['height'],
					'units'  => $shipinfo['dimensionunit']
				)
			);
			$request['WebAuthenticationDetail'] = array(
				'UserCredential' => array(
					'Key' => $ilconfig['fedex_developer_key'], 
					'Password' => $ilconfig['fedex_password']
				)
			);
			$request['ClientDetail'] = array(
				'AccountNumber' => $ilconfig['fedex_account'], 
				'MeterNumber' => $ilconfig['fedex_access_id']
			);
			$request['TransactionDetail'] = array(
				'CustomerTransactionId' => ' *** Rate Request v13 via ILance ***'
			);
			$request['Version'] = array(
				'ServiceId' => 'crs', 
				'Major' => '13', 
				'Intermediate' => '0', 
				'Minor' => '0'
			);
			$request['ReturnTransitAndCommit'] = true;
			$request['RequestedShipment']['DropoffType'] = (in_array($shipinfo['shipcode'], array('FEDEX_GROUND', 'GROUND_HOME_DELIVERY')) ? 'REGULAR_PICKUP' : $shipinfo['pickuptype']); 
			$request['RequestedShipment']['ShipTimestamp'] = date('c');
			$request['RequestedShipment']['ServiceType'] = $shipinfo['shipcode'];
			$request['RequestedShipment']['PackagingType'] = (in_array($shipinfo['shipcode'], array('FEDEX_GROUND', 'GROUND_HOME_DELIVERY')) ? 'YOUR_PACKAGING' : $shipinfo['packagingtype']);
			/*$request['RequestedShipment']['TotalInsuredValue'] = array(
				'Amount' => '1.00',
				'Currency' => 'CAD'
			);*/
			$request['RequestedShipment']['Shipper'] = $details['shipper'];
			$request['RequestedShipment']['Recipient'] = $details['recipient'];
			$request['RequestedShipment']['ShippingChargesPayment'] = array(
				'PaymentType' => 'SENDER',
				'Payor' => array(
					'ResponsibleParty' => array(
						'AccountNumber' => $ilconfig['fedex_account'],
						'CountryCode' => $shipinfo['origin_country'])
				)
			);
			$request['RequestedShipment']['RateRequestTypes'] = 'ACCOUNT'; 
			$request['RateRequestTypes'] = 'ACCOUNT';
			// only for smart post
			if (isset($shipinfo['shipcode']) AND $shipinfo['shipcode'] == 'SMART_POST')
			{
				$request['RequestedShipment']['SmartPostDetail'] = array(
					'Indicia' => 'PARCEL_SELECT',
					'AncillaryEndorsement' => 'CARRIER_LEAVE_IF_NO_RESPONSE',
					'SpecialServices' => 'USPS_DELIVERY_CONFIRMATION',
					'HubId' => '5602'
				);
			}
			$request['RequestedShipment']['PackageCount'] = $details['group_package_count'];
			$request['RequestedShipment']['RequestedPackageLineItems'] = array(
				'SequenceNumber'=> $details['sequence_number'],
				'GroupPackageCount'=> $details['group_package_count'],
				'Weight' => array(
					'Value' => !empty($details['weight']['value']) ? $details['weight']['value'] : 8,
					'Units' => !empty($details['weight']['units']) ? $details['weight']['units'] : 'IN'
				),
				'Dimensions' => array(
					'Length' => !empty($details['dimensions']['length']) ? $details['dimensions']['length'] : 12,
					'Width' => !empty($details['dimensions']['width']) ? $details['dimensions']['width'] : 9,
					'Height' => !empty($details['dimensions']['height']) ? $details['dimensions']['height'] : 9,
					'Units' => !empty($details['dimensions']['units']) ? $details['dimensions']['units'] : 'IN'
				)
			);
			try
			{
				//if ($ilconfig['fedex_sandbox']))
				//{
					// https://wsbeta.fedex.com:443/web-services/rate
					// https://ws.fedex.com:443/web-services/rate
					// $newLocation = $client->__setLocation('https://wsbeta.fedex.com:443/web-services/rate);
				//}
				$response = $client->getRates($request);
				if ($response->HighestSeverity != 'FAILURE' AND $response->HighestSeverity != 'ERROR')
				{  	
					$rateReply = $response->RateReplyDetails;
					$this->fedex_write_log($client);
					$ilance->db->query("
						INSERT INTO " . DB_PREFIX . "shipping_rates_cache
						(id, carrier, shipcode, from_country, from_state, from_city, from_zipcode, to_country, to_state, to_city, to_zipcode, weight, weightunit, dimensionunit, length, width, height, pickuptype, packagetype, size, datetime, gatewayrequest, gatewayresult)
						VALUES(
						NULL,
						'fedex',
						'" . $ilance->db->escape_string($shipinfo['shipcode']) . "',
						'" . $ilance->db->escape_string($shipinfo['origin_country']) . "',
						'" . $ilance->db->escape_string($shipinfo['origin_state']) . "',
						'" . $ilance->db->escape_string($shipinfo['origin_city']) . "',
						'" . $ilance->db->escape_string($shipinfo['origin_zipcode']) . "',
						'" . $ilance->db->escape_string($shipinfo['destination_country']) . "',
						'" . $ilance->db->escape_string($shipinfo['destination_state']) . "',
						'" . $ilance->db->escape_string($shipinfo['destination_city']) . "',
						'" . $ilance->db->escape_string($shipinfo['destination_zipcode']) . "',
						'" . $ilance->db->escape_string($shipinfo['weight']) . "',
						'" . $ilance->db->escape_string($shipinfo['weightunit']) . "',
						'" . $ilance->db->escape_string($shipinfo['dimensionunit']) . "',
						'" . $ilance->db->escape_string($shipinfo['length']) . "',
						'" . $ilance->db->escape_string($shipinfo['width']) . "',
						'" . $ilance->db->escape_string($shipinfo['height']) . "',
						'" . $ilance->db->escape_string($shipinfo['pickuptype']) . "',
						'" . $ilance->db->escape_string($shipinfo['packagingtype']) . "',
						'" . $ilance->db->escape_string($shipinfo['sizecode']) . "',
						'" . DATETIME24H . "',
						'" . $ilance->db->escape_string(serialize($request)) . "',
						'" . $ilance->db->escape_string(serialize($response)) . "')
					");
				}
				$this->fedex_write_log($client); 
			}
			catch (SoapFault $exception)
			{
				$detail = $exception->detail;
				$response = $detail->desc;
			}
                }
                else
                {
			// update shipping rates traffic (to cache so we don't have to re query api)
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "shipping_rates_cache
                                SET traffic = traffic + 1
                                WHERE carrier = 'fedex'
                                        AND shipcode = '" . $ilance->db->escape_string($shipinfo['shipcode']) . "'
                                        AND from_country = '" . $ilance->db->escape_string($shipinfo['origin_country']) . "'
                                        AND from_state = '" . $ilance->db->escape_string($shipinfo['origin_state']) . "'
					AND from_city = '" . $ilance->db->escape_string($shipinfo['origin_city']) . "'
                                        AND from_zipcode = '" . $ilance->db->escape_string($shipinfo['origin_zipcode']) . "'
                                        AND to_country = '" . $ilance->db->escape_string($shipinfo['destination_country']) . "'
                                        AND to_state = '" . $ilance->db->escape_string($shipinfo['destination_state']) . "'
					AND to_city = '" . $ilance->db->escape_string($shipinfo['destination_city']) . "'
                                        AND to_zipcode = '" . $ilance->db->escape_string($shipinfo['destination_zipcode']) . "'
                                        AND weight = '" . $ilance->db->escape_string($shipinfo['weight']) . "'
                                        AND weightunit = '" . $ilance->db->escape_string($shipinfo['weightunit']) . "'
                                        AND dimensionunit = '" . $ilance->db->escape_string($shipinfo['dimensionunit']) . "'
                                        AND length = '" . $ilance->db->escape_string($shipinfo['length']) . "'
                                        AND width = '" . $ilance->db->escape_string($shipinfo['width']) . "'
                                        AND height = '" . $ilance->db->escape_string($shipinfo['height']) . "'
                                        AND pickuptype = '" . $ilance->db->escape_string($shipinfo['pickuptype']) . "'
                                        AND packagetype = '" . $ilance->db->escape_string($shipinfo['packagingtype']) . "'
                                        AND size = '" . $ilance->db->escape_string($shipinfo['sizecode']) . "'
                        ");
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        $response = unserialize($res['gatewayresult']);
                }
                return $response;
        }
        function shippinglabel($service = '')
        {
                global $ilance, $show, $ilconfig;
                if ($service == 'ups')
                {
                }
                else if ($service == 'usps')
                {
                        
                }
                else if ($service == 'fedex')
                {
                }
                else
                {
                        ($apihook = $ilance->api('shipcalculator_shippinglabel_else')) ? eval($apihook) : false;
                }
        }
        function machinable($service = '')
        {
                if ($service == 'PARCEL')
                {
                        return 'TRUE';
                }
                else
                {
                        return 'FALSE'; 
                }
        }
	
	/**
	* Function to identify the ship pickup methods by which the package is to be tendered to requested ship service.
	*
	* @param       string       ship service (ups, usps, fedex)
	* @param       boolean      fetch default pickup type (default false)
	*
	* @return      string       Returns a single string or an array of available pickup types
	*/
	function pickuptypes($service = '', $fetchdefault = false)
        {
                global $ilance, $show, $ilconfig;
		$types = array();
                if ($service == 'ups')
                {
                        $types = array(
                                '01' => 'Daily Pickup',
                                '03' => 'Customer Counter',
                                '06' => 'One Time Pickup',
                                '07' => 'On Call Air',
                                '11' => 'Suggested Retail Rates',
                                '19' => 'Letter Center',
                                '20' => 'Air Service Center'
                        );
                        if ($fetchdefault)
                        {
                                return '01';
                        }
                }
                else if ($service == 'usps')
                {
                        $types = array(
                                '00' => 'None'
                        );
                        if ($fetchdefault)
                        {
                                return '00';
                        }
                }
                else if ($service == 'fedex')
                {
			$types = array(
                                'REGULAR_PICKUP' => 'Regular Pick-up',
                                'REQUEST_COURIER' => 'Request Courier',
                                'DROP_BOX' => 'Drop Box',
                                'BUSINESS_SERVICE_CENTER' => 'Business Service Center',
                                'STATION' => 'Station'
                        );
                        if ($fetchdefault)
                        {
				return 'REGULAR_PICKUP';
                        }
                }
                else
                {
                        ($apihook = $ilance->api('shipcalculator_pickuptypes_else')) ? eval($apihook) : false;
                }
                return $types;
        }
	
	/**
	* Function to identify fed ex smart post hubs
	*
	* @return      array       Returns an array with hubid => city as key value pairs
	*/
	function smartpost_hubs()
        {
                global $ilance, $show, $ilconfig;
                $hubs = array(
			'5185' => '(ALPA) Allentown',
			'5303' => '(ATGA) Atlanta',
			'5281' => '(CHNC) Charlotte',
			'5602' => '(CIIL) Chicago',
			'5929' => '(COCA) Chino',
			'5751' => '(DLTX) Dallas',
			'5802' => '(DNCO) Denver',
			'5481' => '(DTMI) Detroit',
			'5087' => '(EDNJ) Edison',
			'5431' => '(GCOH) Grove City',
			'5771' => '(HOTX) Houston',
			'5465' => '(ININ) Indianapolis',
			'5648' => '(KCKS) Kansas City',
			'5902' => '(LACA) Los Angeles',
			'5254' => '(MAWV) Martinsburg',
			'5379' => '(METN) Memphis',
			'5552' => '(MPMN) Minneapolis',
			'5531' => '(NBWI) New Berlin',
			'5110' => '(NENY) Newburgh',
			'5015' => '(NOMA) Northborough',
			'5327' => '(ORFL) Orlando',
			'5194' => '(PHPA) Philadelphia',
			'5854' => '(PHAZ) Phoenix',
			'5150' => '(PTPA) Pittsburgh',
			'5958' => '(SACA) Sacramento',
			'5843' => '(SCUT) Salt Lake City',
			'5983' => '(SEWA) Seattle',
			'5631' => '(STMO) St. Louis'
                );
                return $hubs;
        }
        
        /**
	* Function to identify the packaging used by the requestor for the package.
	*
	* @param       string       ship service (ups, usps, fedex)
	* @param       string       ship code (used for USPS)
	* @param       boolean      fetch default package type (default false)
	*
	* @return      string       Returns a single string or an array of available package types
	*/
	function packagetypes($service = '', $shipcode = '', $fetchdefault = false)
        {
                global $ilance, $show, $ilconfig;
		$types = array();
                if ($service == 'ups')
                {
                        $types = array(
                                '01' => 'UPS Letter',
                                '02' => 'Your Packaging',
                                '03' => 'UPS Tube',
                                '04' => 'UPS Pak',
                                '21' => 'UPS Express Box',
                                '2a' => 'UPS Express Box - Small',
                                '2b' => 'UPS Express Box - Medium',
                                '2c' => 'UPS Express Box - Large'
                        );
                        if ($fetchdefault)
                        {
                                return '02';
                        }
                }
                else if ($service == 'usps')
                {
                        $types = array('VARIABLE' => 'None');
                        if ($shipcode == 'EXPRESS' OR $shipcode == 'PRIORITY')
                        {
                                $types = array( // container
                                        'FLAT RATE ENVELOPE' => 'Flat Rate Envelope',
                                        'FLAT RATE BOX' => 'Flat Rate Box'
                                );
                        }
                        if ($fetchdefault)
                        {
                                return 'VARIABLE';
                        }
                }
                else if ($service == 'fedex')
                {
			$types = array(
                                'YOUR_PACKAGING' => 'Your Packaging',
                                'FEDEX_ENVELOPE' => 'FedEx Envelope',		// 9.252” x 13.189”         max weight: 17.6 oz. Weight when empty: 1.8 oz.
                                'FEDEX_PAK' => 'FedEx Pak',			// 12" x 15.5"		    max weight: 5.5 lbs. Weight when empty: 1 oz.
				'FEDEX_PAK_XL' => 'FedEx XL Pak',		// 17.5" x 20.75" 	    max weight: 5.5 lbs. Weight when empty: 1.5 oz.
				'FEDEX_BOX_SMALL' => 'FedEx Box - Small', 	// 12.25" x 10.9"  x 1.5"   max weight: 20 lbs., Weight when empty: 4.5 oz.
				'FEDEX_BOX_MEDIUM' => 'FedEx Box - Medium',	// 13.25" x 11.5"  x 2.38"  max weight: 20 lbs., Weight when empty: 6.5 oz.
				'FEDEX_BOX_LARGE' => 'FedEx Box - Large',	// 17.88" x 12.38" x 3"     max weight: 20 lbs., Weight when empty: 14.5 oz.
                                'FEDEX_TUBE' => 'FedEx Tube', 			// 38"    x 6"     x 6"x 6" max weight: 20 lbs., Weight when empty: 16 oz.
                                'FEDEX_10KG_BOX' => 'FedEx 10KG Box',		// 15.81" x 12.94" x 10.19" max weight: 22 lbs., Weight when empty: 31 oz.
                                'FEDEX_25KG_BOX' => 'FedEx 25KG Box'		// 21.56" x 16.56" x 13.19" max weight: 55 lbs., Weight when empty: 57 oz.
                        );
                        if ($fetchdefault)
                        {
				return 'YOUR_PACKAGING';
                        }
                }
                else
                {
                        ($apihook = $ilance->api('shipcalculator_packagetypes_else')) ? eval($apihook) : false;
                }
                return $types;
        }
        
        function weightunits($service = '', $fetchdefault = false)
        {
                global $ilance, $show, $ilconfig;
                $units = array();
                if ($service == 'ups')
                {
                        $units = array(
                                'LBS' => 'Pounds',
                                'KGS' => 'Kilograms'
                        );
                        if ($fetchdefault)
                        {
                                return 'LBS';
                        }
                }
                else if ($service == 'usps')
                {
                        $units = array('' => 'None');
                        if ($fetchdefault)
                        {
                                return '';
                        }
                }
                else if ($service == 'fedex')
                {
                        $units = array(
                                'LB' => 'Pounds',
                                'KG' => 'Kilograms'
                        );
                        if ($fetchdefault)
                        {
                                return 'LB';
                        }
                }
                else
                {
                        ($apihook = $ilance->api('shipcalculator_weightunits_else')) ? eval($apihook) : false;
                }
                return $units;
        }
        
        function dimensionunits($service = '', $fetchdefault = false)
        {
                global $ilance, $show, $ilconfig;
		$units = array();
                if ($service == 'ups')
                {
                        $units = array(
                                'IN' => 'Inches',
                                'CM' => 'Centimeters'
                        );
                        if ($fetchdefault)
                        {
                                return 'IN';
                        }
                }
                else if ($service == 'usps')
                {
                        $units = array('' => 'None');
                        if ($fetchdefault)
                        {
                                return '';
                        }
                }
                else if ($service == 'fedex')
                {
                        $units = array(
                                'IN' => 'Inches',
                                'CM' => 'Centimeters'
                        );
                        if ($fetchdefault)
                        {
                                return 'IN';
                        }
                }
                else
                {
                        ($apihook = $ilance->api('shipcalculator_dimensionunits_else')) ? eval($apihook) : false;
                }
                return $units;
        }
        
        function sizeunits($service = '', $l = 0, $w = 0, $h = 0, $fetchdefault = false)
        {
                global $ilance, $show, $ilconfig;
		$units = array();
                if ($service == 'ups')
                {
                        $units = array('' => 'None');
                        if ($fetchdefault)
                        {
                                return '';
                        }
                }
                else if ($service == 'usps')
                {
                        $units = array(
                                'REGULAR' => 'Regular', // Package dimensions are 12" or less
                                'LARGE' => 'Large' // Any package dimension is larger than 12"
                        );
                        if ($fetchdefault)
                        {
                                if ($l > 12 OR $w > 12 OR $h > 12)
                                {
                                        return 'LARGE';
                                }
                                return 'REGULAR';
                        }
                }
                else if ($service == 'fedex')
                {
                        $units = array('' => 'None');
                        if ($fetchdefault)
                        {
                                return '';
                        }
                }
                else
                {
                        ($apihook = $ilance->api('shipcalculator_sizeunits_else')) ? eval($apihook) : false;
                }
                return $units;
        }
        
        function convert_weight_unit($weight, $old_unit, $new_unit)
        {
		$units['OZ'] = 1;
		$units['LBS'] = 0.0625;
		$units['GRAM'] = 28.3495231;
		$units['KG'] = 0.0283495231;
		if ($old_unit != 'OZ')
                {
                        $weight = $weight / $units[$old_unit];
                }
		$weight = $weight * $units[$new_unit];
		if ($weight < .1)
                {
                        $weight = .1;
                }
		return round($weight, 2);
	}
	
	function convert_dimension_unit($size, $old_unit, $new_unit)
        {
		$units['IN'] = 1;
		$units['CM'] = 2.54;
		$units['FT'] = 0.083333;
		if ($old_unit != 'IN')
                {
                        $size = $size / $units[$old_unit];
                }
		$size = $size * $units[$new_unit];
		if ($size < .1)
                {
                        $size = .1;
                }
		return round($size, 2);
	}
        
        function xml_depth($vals, &$i)
        { 
                $children = array();
                if (isset($vals[$i]['value'])) array_push($children, $vals[$i]['value']); 
                while (++$i < count($vals))
                { 
                        switch ($vals[$i]['type'])
                        { 
                                case 'cdata':
                                        array_push($children, $vals[$i]['value']); 
                                break; 
                                case 'complete': 
                                        $tagname = $vals[$i]['tag'];
                                        if (isset($children["$tagname"]))
                                        {
                                                $size = sizeof($children["$tagname"]);
                                        }
                                        else
                                        {
                                                $size = 0;
                                        }
                                        if (isset($vals[$i]['value']))
                                        {
                                                $children[$tagname][$size]["#"] = $vals[$i]['value'];
                                        }
                                        if(isset($vals[$i]["attributes"]))
                                        {
                                                $children[$tagname][$size]["@"] = $vals[$i]["attributes"];
                                        }
                                break; 
                                case 'open': 
                                        $tagname = $vals[$i]['tag'];
                                        if (isset($children["$tagname"]))
                                        {
                                                $size = sizeof($children["$tagname"]);
                                        }
                                        else
                                        {
                                                $size = 0;
                                        }
                                        if(isset($vals[$i]["attributes"]))
                                        {
                                                $children["$tagname"][$size]["@"] = $vals[$i]["attributes"];
                                                $children["$tagname"][$size]["#"] = $this->xml_depth($vals, $i);
                                        }
                                        else
                                        {
                                                $children["$tagname"][$size]["#"] = $this->xml_depth($vals, $i);
                                        }
                                break; 
                                case 'close':
                                        return $children; 
                                break;
                        }
                } 
                return $children;
        }
        
        function xmlize($data)
        {
                $vals = $index = $array = array();
                $parser = xml_parser_create();
                xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
                xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
                xml_parse_into_struct($parser, $data, $vals, $index);
                xml_parser_free($parser);
                $i = 0; 
                if (isset($vals[$i]['tag']))
                {
                        $tagname = $vals[$i]['tag'];
                        if (isset($vals[$i]["attributes"]))
                        {
                                $array[$tagname]["@"] = $vals[$i]["attributes"];
                        }
                        $array[$tagname]["#"] = $this->xml_depth($vals, $i);
                }
                return $array;
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>