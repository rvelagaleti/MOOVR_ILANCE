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
* Payment gateway class to perform the majority merchant gateway communications
*
* @package      iLance\PaymentGateway
* @version      4.0.0.8059
* @author       ILance
*/
class paymentgateway
{
	/**
	* hold the gateways currently supported
	*/
	var $gateway_accepted = array(
                'authnet',
                'plug_n_pay',
                'psigate',
                'eway',
                'bluepay',
                'paypal_pro'
        );
	/**
	* hold the gateways that require amounts to be converted into cents: 13.20 = 1320
	*/
	var $convert_to_cents = array('eway');
        /**
	* hold value if debug is enabled or disabled
	* this will also set the test urls for gateways to enabled
	*/
        var $debug = false;
	/**
	* hold the preferred gateway module to use
	*/
	var $gateway;
	/**
	* hold the credit card details
	*/
	var $card = array();
	/**
	* hold the ilance owner details
	*/
	var $user = array();
	/**
	* hold the customer (payer) details
	*/
	var $customer = array();
	/**
	* hold the ship to details
	*/
	var $ship_to = array();
	/**
	* hold the order details
	*/
	var $order = array();
	/**
	* hold the currency details of the marketplace
	*/
	var $currency = array();
	/**
	* hold any error output
	*/
	var $error = array();
	/**
	* hold the log details
	*/
	var $log = array();
	var $file_log = PAYMENTGATEWAYLOG;
	/**
	* hold the information received from the gateway
	*/
	var $received = array();
	var $authorization = '';
	var $transnum = '';
	var $transact_code = '';
	var $transact_code2 = '';
	/**
	* hold the curl module details
	* if you have the crt file, it can specified here also
	*/
	var $curl_path = CURLPATH;
	var $cert_file = CURLCERT;
        /**
	* hold the parser and xml data
	*/
        var $currentTag;
        var $parser;
        var $xmlData;
	/**
	* hold detailed gateway information for authorize.net
	* possible force_method: fsockopen, curl_ext, curl, curl_xml
	*/
	var $paypal_pro = array(
                'name' => 'paypal_pro',
                'cc_expyyform' => 'yy',
                'cc_expform' => 'm/y',
                'api_username' => '', 
                'api_password' => '', 
                'api_signature' => '', 
                'use_proxy' => '', 
                'proxy_host' => '', 
                'proxy_port' => '', 
                'return_url' => '', 
                'cancel_url' => ''
        );
	var $authnet = array(
                'name' => 'authnet',
                'page' => 'https://secure.authorize.net/gateway/transact.dll',
                'testpage' => 'https://secure.authorize.net/gateway/transact.dll',
                'method' => 'POST',
                'force_method' => 'fsockopen',
                'u_user' => 'x_login',
                'u_password' => 'x_password',
                'u_key' => 'x_tran_key',
                'u_userid' => 'x_cust_id',
                'o_amount' => 'x_amount',
                'o_orderID' => 'x_invoice_num',
                'o_description' => 'x_description',
                'o_authtype' => 'x_type',
                'o_mode' => 'x_trans_id',
                'o_authcode' => '',
                'o_transnum' => '',
                'v_currency' => '',
                'v_currency_symbol' => '',
                'c_company' => 'x_company',
                'c_fname' => 'x_first_name',
                'c_lname' => 'x_last_name',
                'c_address' => 'x_address',
                'c_city' => 'x_city',
                'c_state' => 'x_state',
                'c_zip' => 'x_zip',
                'c_country' => 'x_country',
                'c_phone' => 'x_phone',
                'c_fax' => 'x_fax',
                'c_ip' => 'x_customer_ip',
                'c_email' => '',
                's_fname' => 'x_ship_to_first_name',
                's_lname' => 'x_ship_to_last_name',
                's_address' => 'x_ship_to_address',
                's_city' => 'x_ship_to_city',
                's_state' => 'x_ship_to_state',
                's_zip' => 'x_ship_to_zip',
                's_country' => 'x_ship_to_country',
                's_phone' => '',
                's_fax' => '',
                'cc_name' => '',
                'cc_type' => '',
                'cc_number' => 'x_card_num',
                'cc_expmm' => '',
                'cc_expyy' => '',
                'cc_exp' => 'x_exp_date',
                'cc_cvv' => 'x_card_code',
                'cc_auth' => '',
                'cc_expyyform' => 'yyyy',
                'cc_expform' => 'my',
                'extra' => array(
                        'x_version' => '3.1',
                        'x_delim_data' => 'TRUE',
                        'x_echo_data' => '',
                        'x_delim_char' => ',',
                        'x_encap_char' => '',
                        'x_method' => 'CC', // CC or ECHECK
                        'x_customer_ip' => '',
                        'x_test_request' => 'FALSE',
                        'x_ADC_URL' => '',
                        'x_relay_response' => 'FALSE'
                )
        );
        /**
        * hold detailed gateway information for bluepay
        * possible force_method: fsockopen, curl_ext, curl, curl_xml
        */
        var $bluepay = array(
                'name' => 'bluepay',
                'page' => 'https://secure.bluepay.com/interfaces/a.net',
                'testpage' => 'https://secure.bluepay.com/interfaces/a.net',
                'method' => 'POST',
                'force_method' => 'fsockopen',
                'u_user' => 'x_login',
                'u_password' => 'x_password',
                'u_key' => 'x_tran_key',
                'u_userid' => 'x_cust_id',
                'o_amount' => 'x_amount',
                'o_orderID' => 'x_invoice_num',
                'o_description' => 'x_description',
                'o_authtype' => 'x_type',
                'o_mode' => 'x_trans_id',
                'o_authcode' => '',
                'o_transnum' => '',
                'v_currency' => '',
                'v_currency_symbol' => '',
                'c_company' => 'x_company',
                'c_fname' => 'x_first_name',
                'c_lname' => 'x_last_name',
                'c_address' => 'x_address',
                'c_city' => 'x_city',
                'c_state' => 'x_state',
                'c_zip' => 'x_zip',
                'c_country' => 'x_country',
                'c_phone' => 'x_phone',
                'c_fax' => 'x_fax',
                'c_ip' => 'x_customer_ip',
                'c_email' => '',
                's_fname' => 'x_ship_to_first_name',
                's_lname' => 'x_ship_to_last_name',
                's_address' => 'x_ship_to_address',
                's_city' => 'x_ship_to_city',
                's_state' => 'x_ship_to_state',
                's_zip' => 'x_ship_to_zip',
                's_country' => 'x_ship_to_country',
                's_phone' => '',
                's_fax' => '',
                'cc_name' => '',
                'cc_type' => '',
                'cc_number' => 'x_card_num',
                'cc_expmm' => '',
                'cc_expyy' => '',
                'cc_exp' => 'x_exp_date',
                'cc_cvv' => 'x_card_code',
                'cc_auth' => '',
                'cc_expyyform' => 'yyyy',
                'cc_expform' => 'my',
                'extra' => array(
                        'x_version' => '3.1',
                        'x_delim_data' => 'TRUE',
                        'x_echo_data' => '',
                        'x_delim_char' => ',',
                        'x_encap_char' => '',
                        'x_method' => 'CC', // CC or ECHECK
                        'x_customer_ip' => '',
                        'x_test_request' => 'FALSE',
                        'x_ADC_URL' => '',
                        'x_relay_response' => 'FALSE',
                )
        );
	/**
	* will hold detailed gateway information for psigate
	* possible force_method: fsockopen, curl_ext, curl, curl_xml
	*/
	var $psigate = array(
                'name' => 'psigate',
                'page' => 'https://secure.psigate.com:7934/Messenger/XMLMessenger',
                'testpage' => 'https://dev.psigate.com:7989/Messenger/XMLMessenger',
                'method' => 'POST',
                'force_method' => 'curl_xml',
                'u_user' => 'StoreID',
                'u_password' => 'Passphrase',
                'u_key' => '',
                'u_email' => '',
                'o_amount' => 'Subtotal',
                'o_orderID' => 'OrderID',
                'o_description' => 'ItemDescription',
                'o_mode' => '',
                'o_authtype' => 'CardAction',
                'o_authcode' => '',
                'o_transnum' => '',
                'v_currency' => '',
                'v_currency_symbol' => '',
                'c_fname' => 'Bname',
                'c_lname' => '',
                'c_address' => 'Baddress1',
                'c_city' => 'Bcity',
                'c_state' => 'Bprovince',
                'c_zip' => 'Bpostalcode',
                'c_country' => 'Bcountry',
                'c_phone' => 'Phone',
                'c_fax' => 'Fax',
                'c_ip' => 'CustomerIP',
                'c_email' => 'Email',
                's_fname' => 'Sname',
                's_lname' => '',
                's_address' => 'Saddress1',
                's_city' => 'Scity',
                's_state' => 'Sprovince',
                's_zip' => 'Spostalcode',
                's_country' => 'Scountry',
                's_phone' => 'Phone',
                's_fax' => 'Fax',
                'cc_name' => 'Bname',
                'cc_type' => '',
                'cc_number' => 'CardNumber',
                'cc_expmm' => 'CardExpMonth',
                'cc_expyy' => 'CardExpYear',
                'cc_exp' => '',
                'cc_cvv' => 'CardIDNumber',
                'cc_auth' => '',
                'cc_expyyform' => '',
                'cc_expform' => '',
                'extra' => array(
                        'PaymentType' => 'CC'
                )
        );
        /**
	* will hold detailed gateway information for eway
	* dev test: requires: 4646464646464646 as card and 87654321 as ewayCustomerID
	* additional dev notes: all amounts are to be passed in cents (ie 1050 = $10.50)
	* with no decimal points, dollar signs, etc.
	* possible force_method: fsockopen, curl_ext, curl, curl_xml
	*/
	var $eway = array(
                'name' => 'eway',
                'page' => 'https://www.eway.com.au/gateway/xmlpayment.asp',
                'testpage' => 'https://www.eway.com.au/gateway/xmltest/TestPage.asp',
                'method' => 'POST',
                'force_method' => 'curl_xml',
                'u_user' => 'ewayCustomerID',
                'u_password' => '',
                'u_key' => '',
                'u_email' => 'ewayCustomerEmail',
                'o_amount' => 'ewayTotalAmount',
                'o_orderID' => 'ewayTrxnNumber',
                'o_description' => 'ewayCustomerInvoiceDescription',
                'o_mode' => '',
                'o_authtype' => '',
                'o_authcode' => '',
                'o_transnum' => 'ewayCustomerInvoiceRef',
                'v_currency' => '',
                'v_currency_symbol' => '',
                'c_fname' => 'ewayCustomerFirstName',
                'c_lname' => 'ewayCustomerLastName',
                'c_address' => 'ewayCustomerAddress',
                'c_city' => '',
                'c_state' => '',
                'c_zip' => 'ewayCustomerPostcode',
                'c_country' => '',
                'c_phone' => '',
                'c_fax' => '',
                'c_ip' => '',
                'c_email' => 'ewayCustomerEmail',
                's_fname' => '',
                's_lname' => '',
                's_address' => '',
                's_city' => '',
                's_state' => '',
                's_zip' => '',
                's_country' => '',
                's_phone' => '',
                's_fax' => '',
                'cc_name' => 'ewayCardHoldersName',
                'cc_type' => '',
                'cc_number' => 'ewayCardNumber',
                'cc_expmm' => 'ewayCardExpiryMonth',
                'cc_expyy' => 'ewayCardExpiryYear',
                'cc_exp' => '',
                'cc_cvv' => 'ewayCVN',
                'cc_auth' => 'ewayAuthCode',
                'cc_expyyform' => '',
                'cc_expform' => '',
                'extra' => array()
        );
	/**
	* will hold detailed gateway information for plug n pay
	* possible force_method: fsockopen, curl_ext, curl, curl_xml
	*/
	var $plug_n_pay = array(
                'name' => 'plug_n_pay',
                'page' => 'https://pay1.plugnpay.com/payment/pnpremote.cgi',
                'testpage' => 'https://pay1.plugnpay.com/payment/pnpremote.cgi',
                'method' => 'POST',
                'force_method' => 'fsockopen',
                'u_user' => 'publisher-name',
                'u_password' => 'publisher-password',
                'u_key' => '',
                'u_email' => 'publisher-email',
                'o_amount' => 'card-amount',
                'o_orderID' => 'orderID',
                'o_description' => '',
                'o_mode' => 'mode',
                'o_authtype' => 'authtype',
                'o_authcode' => '',
                'o_transnum' => '',
                'v_currency' => 'currency',
                'v_currency_symbol' => 'currency_symbol',
                'c_fname' => 'card_name',
                'c_lname' => '',
                'c_address' => 'card-address1',
                'c_city' => 'card-city',
                'c_state' => 'card-state',
                'c_zip' => 'card-zip',
                'c_country' => 'card-country',
                'c_phone' => '',
                'c_fax' => '',
                'c_ip' => 'ipaddress',
                'c_email' => 'email',
                's_fname' => 'shipname',
                's_lname' => '',
                's_address' => 'address1',
                's_city' => 'city',
                's_state' => 'state',
                's_zip' => 'zip',
                's_country' => 'country',
                's_phone' => '',
                's_fax' => '',
                'cc_name' => 'card-name',
                'cc_type' => 'card-type',
                'cc_number' => 'card-number',
                'cc_expmm' => '',
                'cc_expyy' => '',
                'cc_exp' => 'card-exp',
                'cc_cvv' => 'card-cvv',
                'cc_auth' => '',
                'cc_expyyform' => 'yy',
                'cc_expform' => 'm/y',
                'extra' => array(
                        'shipinfo' => '1',
                        'easycart' => '1',
                        'dontsndmail' => 'no',
                )
        );

        /*
        * ILance Payment Gateway Constructor
        *
        * @param       string           gateway name to use
        *
        * @return      nothing
        */
	function paymentgateway($gateway = '')
	{
		$this->error['fatal'] = false;
		$this->error['number'] = 0;
		$this->error['text'] = '';
		if (in_array($gateway, $this->gateway_accepted))
		{
			$this->gateway = $gateway;
		}
		else
		{
			$this->set_error(1001, 'Gateway: ' . $gateway . ' is not in supported list, please use one of the following: ' . implode(',', $this->gateway_accepted), true);
		}
	}
	
	/*
        * Function to set the gateway user details (user, password, key, email etc)
        *
        * @param       string           username of gateway user
        * @param       string           password of gateway user
        * @param       string           gateway supplied key
        * @param       string           email of gateway user
        *
        * @return      nothing
        */
        function set_user($u_user = '', $u_password = '', $u_key = '', $u_email = '')
	{
		$this->user['u_user'] = $u_user;
		$this->user['u_password'] = $u_password;
		$this->user['u_key'] = $u_key;
		$this->user['u_email'] = $u_email;
		$this->user['u_userid'] = !empty($_SESSION['ilancedata']['user']['userid']) ? intval($_SESSION['ilancedata']['user']['userid']) : 0;
	}
	
	/*
        * Function to set the gateway currency used within the Marketplace
        *
        * @param       string           currency name
        * @param       string           currency symbol
        *
        * @return      nothing
        */
        function set_currency($currency = '', $symbol = '')
	{
		$this->currency['v_currency'] = $currency;
		$this->currency['v_currency_symbol'] = $symbol;
	}
	
	/*
        * Function to set the credit card details for gateway processing
        *
        * @param       string           fullname of credit card owner
        * @param       string           credit card type (visa, mc, disc, etc)
        * @param       integer          credit card number
        * @param       string           credit card expiry date (month)
        * @param       string           credit card expiry date (year)
        * @param       integer          credit card cvv2
        * @param       string           (optional)
        *
        * @return      nothing
        */
        function set_ccard($cc_name = '', $cc_type = '', $cc_number = '', $cc_expmm = '', $cc_expyy = '', $cc_cvv = '', $cc_auth = '')
	{
                $key = $this->gateway;
		$gateway = $this->$key;
		$this->card['cc_name'] = $cc_name;
		$this->card['cc_type'] = $cc_type;
		$this->card['cc_number'] = mb_ereg_replace("[^0-9]", "", $cc_number);
		$this->card['cc_expmm'] = $cc_expmm;
		$this->card['cc_expyy'] = $cc_expyy;
		$this->card['cc_cvv'] = $cc_cvv;
		$this->card['cc_auth'] = $cc_auth;
		$this->card['cc_expyyform'] = $gateway['cc_expyyform'];
		$this->card['cc_expform'] = $gateway['cc_expform'];
	}
	
	/*
        * Function to set the credit card customer details for gateway processing
        *
        * @param       string           username or company
        * @param       string           customer phone
        * @param       string           customer first name
        * @param       string           customer last name
        * @param       string           customer billing address
        * @param       string           customer city
        * @param       string           customer state
        * @param       string           customer postal or zip code
        * @param       string           customer country name
        *
        * @return      nothing
        */
        function set_customer($c_company = '', $c_phone = '', $c_fname = '', $c_lname = '', $c_address = '', $c_city = '', $c_state = '', $c_zip = '', $c_country = '')
	{
		$this->customer['c_company'] = $c_company;
		$this->customer['c_fname'] = $c_fname;
		$this->customer['c_lname'] = $c_lname;
		$this->customer['c_address'] = $c_address;
		$this->customer['c_city'] = $c_city;
		$this->customer['c_state'] = $c_state;
		$this->customer['c_zip'] = $c_zip;
		$this->customer['c_country'] = $c_country;
		$this->customer['c_phone'] = $c_phone;
	}

	/*
        * Function to set the shipping to address details
        *
        * @param       string           customer first name
        * @param       string           customer last name
        * @param       string           customer billing address
        * @param       string           customer city
        * @param       string           customer state
        * @param       string           customer postal or zip code
        * @param       string           customer country name
        * @param       string           customer phone
        * @param       string           customer fax
        *
        * @return      nothing
        */
        function set_ship_to($fname = '', $lname = '', $address = '', $city = '', $state = '', $zip = '', $country = '', $phone = '', $fax = '')
	{
		$this->ship_to['s_fname'] = $fname;
		$this->ship_to['s_lname'] = $lname;
		$this->ship_to['s_address'] = $address;
		$this->ship_to['s_city'] = $city;
		$this->ship_to['s_state'] = $state;
		$this->ship_to['s_zip'] = $zip;
		$this->ship_to['s_country'] = $country;
		$this->ship_to['s_phone'] = $phone;
		$this->ship_to['s_fax']	= $fax;
	}
	
	/*
        * Function to set the order details
        *
        * @param       string           order amount
        * @param       string           order id
        * @param       string           order description
        * @param       string           order authentication type
        * @param       string           order mode
        * @param       string           order authentication code
        * @param       string           order transaction number
        * @param       string           order currency
        * @param       string           order currency symbol
        *
        * @return      nothing
        */
        function set_order($o_amount = '', $o_orderID = '', $o_description = '', $o_authtype = '', $o_mode = '', $o_authcode = '', $o_transnum = '', $v_currency = '', $v_currency_symbol = '')
	{
		// some gateways require totals sent in cents only
		// lets find out if our selected gateway falls into this situation
		$key = $this->gateway;
		$gateway = $this->$key;
		if (in_array($gateway['name'], $this->convert_to_cents))
		{
			// turns 1.00 into 100, 500 into 50000, etc
			$this->order['o_amount'] = number_format($o_amount, 2, '', '');
		}
		else
		{
			$this->order['o_amount'] = number_format($o_amount, 2, '.', '');
		}
		$this->order['o_orderID'] = $o_orderID;
		$this->order['o_description'] = $o_description;
		$this->order['o_authtype'] = $o_authtype;
		$this->order['o_mode'] = $o_mode;
		$this->order['o_authcode'] = $o_authcode;
                if (empty($o_transnum))
                {
                        $this->order['o_transnum'] = $o_orderID;
                }
                else
                {
                        $this->order['o_transnum'] = $o_transnum;        
                }
		$this->order['v_currency'] = $v_currency;
		$this->order['v_currency_symbol'] = $v_currency_symbol;
	}

	/*
        * Function to set some extra details to pass to our processing gateway (optional)
        *
        * @param       array            array containing any extra details to set for this order
        *
        * @return      nothing
        */
        function set_extra($extra = array())
	{
		if (is_array($extra) AND !empty($extra))
		{
			$key = $this->gateway;
			$gateway = $this->$key;
			$gateway['extra'] = array_merge($gateway['extra'], $extra);
			$this->$key = $gateway;
		} 
	}
	
	/*
        * Function to format the year of the credit card based on the current payment gateway type we are using
        *
        * @param       string           year
        * @param       string           year format desired (yyyy = 2013 or yy = 13)
        *
        * @return      nothing
        */
        function format_year($yy = '', $yyform = '')
	{
		return $yy;
	}

	/*
        * Function to fetch the current gateway we are using
        *
        * 
        * @return      nothing
        */
        function get_gateway()
	{
                $this->set_gateway();
		$key = $this->gateway;
		return $this->$key;
	}
	
	/*
        * Function to set the current gateway we will be using
        *
        * 
        * @return      nothing
        */
        function set_gateway()
	{
		// this function set and returns the data for the selected gateway
		$key = $this->gateway;
		$gateway = $this->$key;
		if (isset($this->card['cc_expyy']) AND $this->card['cc_expmm'])
		{
			$this->card['cc_expyy'] = $this->format_year($this->card['cc_expyy'], $gateway['cc_expyyform']);
			if (!empty($this->card['cc_expmm']) AND !empty($this->card['cc_expyy']) AND !empty($this->card['cc_expform']))
			{
				$this->card['cc_exp'] = $gateway['cc_expform'];
				$this->card['cc_exp'] = str_replace('m', $this->card['cc_expmm'], $this->card['cc_exp']);
				$this->card['cc_exp'] = str_replace('y', $this->card['cc_expyy'], $this->card['cc_exp']);
			}
		}
		$data = array();
		foreach ($gateway as $key => $value)
		{
			if ($key == 'extra')
			{
				if (!empty($value))
				{
					foreach ($value as $key1 => $value1)
					{
						$data[$key1] = $value1;
					}
				}
			}
			else if (!empty($value) AND !is_null($value))
			{
				if ($key == 'o_authtype')
				{
					switch ($this->gateway)
					{
                                                case 'bluepay':
                                                case 'authnet':
                                                {
                                                        // possible authtype:
                                                        // 1 -> auth_only = check and authorize the CC
                                                        // 2 -> auth_capture = check and process the CC
                                                        // 3 -> capture_only = capture only the information
                                                        switch ($this->order[$key])
                                                        {
                                                                case 'auth':
                                                                {
                                                                        $this->order[$key] = 'AUTH_ONLY';
                                                                        break;
                                                                }
                                                                case 'charge':
                                                                {
                                                                        $this->order[$key] = 'AUTH_CAPTURE';
                                                                        break;
                                                                }
                                                                case 'capture':
                                                                {
                                                                        $this->order[$key] = 'CAPTURE_ONLY';
                                                                        break;
                                                                }
                                                                case '':
                                                                {
                                                                        unset($this->order[$key]);
                                                                        break;
                                                                }
                                                        }
                                                        break;
                                                }                                            
						case 'plug_n_pay':
                                                {
                                                        // possible authtype:
                                                        // 1 -> auth = check and authorize the CC
                                                        // 2 -> charge = check and process the CC
                                                        switch ($this->order[$key])
                                                        {
								case 'auth':
                                                                {
                                                                        $this->order[$key] = 'authonly';
                                                                        break;
                                                                }
                                                                case 'charge':
                                                                {
                                                                        $this->order[$key] = 'authpostauth';
                                                                        break;
                                                                }
                                                                case '':
                                                                {
                                                                        unset($this->order[$key]);
                                                                        break;
                                                                }
							}
                                                        break;
                                                }                                            
						case 'psigate':
                                                {
							// possible authtype:
							// 1 -> charge = check and process the CC
							switch ($this->order[$key])
							{
								case 'charge':
                                                                {
									$this->order[$key] = '0';
                                                                        break;
                                                                }
								case 'credit':
                                                                {
									$this->order[$key] = '3';
                                                                        break;
                                                                }
								case '':
                                                                {
									unset($this->order[$key]);
                                                                        break;
                                                                }
							}
                                                        break;
                                                }                                            
                                                case 'eway':
                                                {
                                                        break;
                                                }
					}
				}
				else if ($key == 'o_mode')
				{
					switch ($this->gateway)
					{
                                                case 'bluepay':                                                
						case 'authnet':
                                                {
							// possible mode:
							// 1 -> process = process a preauth card
							// 2 -> void = void the transaction
							// 3 -> credit = return / refund the transaction
							switch ($this->order[$key])
							{
								case 'process':
                                                                {
									$this->order[$key] = 'PRIOR_AUTH_CAPTURE';
                                                                        break;
                                                                }
								case 'void':
                                                                {
									$this->order[$key] = 'VOID';
                                                                        break;
                                                                }
								case 'credit':
                                                                {
									$this->order[$key] = 'CREDIT';
                                                                        break;
                                                                }
								case '':
                                                                {
									unset($this->order[$key]);
                                                                        break;
                                                                }
							}
                                                        break;
                                                }
						case 'plug_n_pay':
                                                {
							// possible mode:
							// 1 -> process = process a preauth card
							// 2 -> void = void the transaction
							// 3 -> credit = return / refund the transaction
							switch ($this->order[$key])
							{
								case 'process':
                                                                {
									$this->order[$key] = 'mark';
                                                                        break;
                                                                }
								case 'void':
                                                                {
									$this->order[$key] = 'void';
                                                                        break;
                                                                }
								case 'credit':
                                                                {
									$this->order[$key] = 'return';
                                                                        break;
                                                                }
								case '':
                                                                {
									unset($this->order[$key]);
                                                                        break;
                                                                }
							}
                                                        break;
                                                }
						case 'psigate':
                                                {
                                                        break;
                                                }
                                                case 'eway':
                                                {
                                                        break;
                                                }
					}
				}
				if (mb_substr($key, 0, 2) == 'o_' AND isset($this->order[$key]))
				{
					$data[$value] = $this->order[$key];
				}
				else if (mb_substr($key, 0, 2) == 'u_' AND isset($this->user[$key]))
				{
					$data[$value] = $this->user[$key];
				}
				else if (mb_substr($key, 0, 2) == 'c_' AND isset($this->customer[$key]))
				{
					$data[$value] = $this->customer[$key];
				}
				else if (mb_substr($key, 0, 2) == 's_' AND isset($this->ship_to[$key]))
				{
					$data[$value] = $this->ship_to[$key];
				}
				else if (mb_substr($key, 0, 3) == 'cc_' AND isset($this->card[$key]))
				{
					$data[$value] = $this->card[$key];
				}
				else if (mb_substr($key, 0, 2) == 'v_' AND isset($this->currency[$key]))
				{
					$data[$value] = $this->currency[$key];
				}
			}
		}
		return $data;
	}
	
	/*
        * Function to process the credit card on the merchant gateway server the admin has defined within the AdminCP
        * and to ultimately receive and process our transaction response for the ILance scripts
        *
        * 
        * @return      nothing
        */
        function process()
	{
		global $ilconfig;
                $key = $this->gateway;
                $gateway = $this->$key;
                if ($this->error['fatal'] == true)
                {
                        return false;
                }
                $err = array();              
                $data = $this->set_gateway();
                if ($this->error['fatal'] == true)
                {
                        return false;
                }
                if ($gateway['name'] == 'paypal_pro')
                {
                        $err = $this->process_paypal_pro();
                        if ($err == '1')
                        {
                                return true;
                        }
                }
                else 
                {
                        // #### RECEIVE PAYMENT GATEWAY RESPONSE #######################
                        $err = $this->fetch_gateway_response($data, $gateway['page'], $gateway['testpage'], $gateway['method'], $gateway['force_method']);
                        $return = false;
                }
                if (!empty($err) AND is_array($err))
                {
                        // no errors, I can parse and see if everything was ok
                        $this->received = $err;
                        switch ($gateway['name'])
                        {
                                case 'bluepay':
				case 'authnet':
                                {
                                        /*
                                        $err = Array (
                                                [0] => 1,0,1,Approved Sale,ODIST,_,100023159881,200497306,Account Deposit Credit via Visa : #1111 into Online Account Balance,13.00,CC,AUTH_CAPTURE,1,Peter,Salzmann,ILance,123 Anywhere St.,Grafton,Ontario,K0K-2G0,Canada,+1-905-349-2944,,,,,,,,,,,,,,,,6a47aac36818e6d657c6a4296c5a2a2e,_,
                                        )*/
                                        $tmp = explode(',', $err[0]);
                                        // there will be an array with at least 69 fields
                                        // #### 1 = approved ###################
                                        if (!empty($tmp[0]) AND $tmp[0] == '1')
                                        {
                                                $this->authorization = $tmp[4];
                                                $this->order['o_orderID'] = $tmp[6];
                                                $this->transact_code = $tmp[6]; 
                                                $return = true;
                                        }
                                        // #### 2 = declined ###################
                                        else if (!empty($tmp[0]) AND $tmp[0] == '2')
                                        {
                                                
                                                $this->transact_code = '';
                                                $this->authorization = '';
                                                $this->set_error($tmp[2], $tmp[3], true, $tmp[0]);
                                        }
                                        // #### 3 = error ######################
                                        else if (!empty($tmp[0]) AND $tmp[0] == '3')
                                        {
                                                
                                                $this->transact_code = '';
                                                $this->authorization = '';
                                                $this->set_error($tmp[2], $tmp[3], true, $tmp[0]);
                                        }
                                        // #### other/unknown ##################
                                        else
                                        {
                                                $this->transact_code = '';
                                                $this->authorization = '';
                                                $this->set_error($tmp[2], $tmp[3], true, $tmp[0]);
                                        }
                                        break;
                                }
                                case 'paypal_pro':
                                {
                                }
				case 'plug_n_pay':
                                {
                                        // decode the result page and put it into transaction_array
                                        $results = implode("\n", $err);
                                        $decoded = urldecode($results);
                                        $temparray = mb_split('&', $decoded);
                                        $returnarray = array();
                                        foreach ($temparray AS $entry)
                                        {
                                                list($name, $value) = mb_split('=', $entry);
                                                $returnarray[$name] = $value;
                                        }
                                        if (empty($returnarray['FinalStatus']))
                                        {
                                                $returnarray['FinalStatus'] = '';
                                        }
                                        if ($returnarray['FinalStatus'] == 'success')
                                        {
                                                $return = true;
                                                if (isset($returnarray['orderID']))
                                                {
                                                        // used for cc authentication
                                                        $this->transact_code = $returnarray['orderID'];
                                                }
                                                if (isset($returnarray['auth-code']))
                                                {
                                                        $this->authorization = $returnarray['auth-code'];
                                                }
                                        }
                                        else if ($returnarray['FinalStatus'] == 'badcard')
                                        {
                                                $this->transact_code = '';
                                                $this->authorization = '';
                                                $this->set_error(2001, $returnarray['MErrMsg'], true, $returnarray['FinalStatus']);
                                        }
                                        else if ($returnarray['FinalStatus'] == 'fraud')
                                        {
                                                $this->transact_code = '';
                                                $this->authorization = '';
                                                $this->set_error(2002, $returnarray['MErrMsg'], true, $returnarray['FinalStatus']);
                                        }
                                        else if ($returnarray['FinalStatus'] == 'problem')
                                        {
                                                $this->transact_code = '';
                                                $this->authorization = '';
                                                $this->set_error(2003, $returnarray['MErrMsg'], true, $returnarray['FinalStatus']);
                                        }
                                        else
                                        {
                                                // this should not happen
                                                $this->transact_code = '';
                                                $this->authorization = '';
                                                $this->set_error(2101, $returnarray['FinalStatus'] . "\n" . $returnarray['MErrMsg'], true);
                                        }
                                        break;
                                }                            
				case 'psigate':
                                {
                                        $retval = $err['ReturnCode'];
                                        $retval = explode(':', $retval);
                                        if (isset($err['Approved']) AND $err['Approved'] == 'APPROVED' AND isset($retval[0]) AND $retval[0] == 'Y')
                                        {
                                                $return = true;
                                                if (isset($err['OrderID']))
                                                {
                                                        // used for cc authentication
                                                        $this->transact_code = $err['OrderID'];
                                                }
                                                if (isset($err['CardAuthNumber']))
                                                {
                                                        $this->authorization = $err['CardAuthNumber'];
                                                }
                                        }
                                        else
                                        {
                                                if (empty($err['ErrMsg']))
                                                {
                                                        $err['ErrMsg'] = 'The transaction was rejected by the payment gateway.  Please contact support for further information.';
                                                }
                                                $this->transact_code = '';
                                                $this->authorization = '';
                                                $this->set_error(2101, $err['ErrMsg'], true);
                                        }
                                        break;
                                }
                                case 'eway':
                                {
                                        if (isset($err['ewayTrxnStatus']) AND $err['ewayTrxnStatus'] == 'True')
                                        {
                                                $return = true;
                                                if (isset($err['ewayTrxnNumber']))
                                                {
                                                        // used for cc authentication
                                                        $this->transact_code = $err['ewayTrxnNumber'];
                                                }
                                                if (isset($err['ewayAuthCode']))
                                                {
                                                        $this->authorization = $err['ewayAuthCode'];
                                                }                
                                        }
                                        else
                                        {
                                                // this should not happen
                                                if (empty($err['ewayTrxnError']))
                                                {
                                                        $err['ewayTrxnError'] = 'The transaction was rejected by the payment gateway.  Please contact support for further information.';
                                                }
                                                $this->transact_code = '';
                                                $this->authorization = '';
                                                $this->set_error(2101, $err['ewayTrxnError'], true);
                                        }
                                        break;
                                }
			}
			if ($this->error['fatal'] == true)
			{
				return false;
			}
			else
			{
				return $return;
			}
		}
	}
	
	/*
        * Function to set our error array details
        *
        * @param       string         error number
        * @param       string         error text message
        * @param       bool           fatal error (true or false)
        * @param       string         gateway error message
        * @param       string         gateway error number
        * 
        * @return      nothing
        */
        function set_error($e_num, $e_text, $e_type = false, $gateway_err = '', $gateway_num = '')
	{
		$this->error['fatal'] = $e_type;
		$this->error['number'] = $e_num;
		$this->error['text'] = $e_text;
		$this->error['gerror'] = $gateway_err;
		$this->error['gnumber'] = $gateway_num;
	}
	
	/*
        * Function to set the log file based on text being sent as an argument
        *
        * @param       string         text message to log
        * 
        * @return      nothing
        */
        function set_log($text)
	{
		$this->log[] = $text;
		if (!empty($this->file_log))
		{
			if (file_exists($this->file_log))
			{
				if ($fp = @fopen($this->file_log, 'a'))
				{
					fwrite($fp, $text);
					fclose($fp);
				} 
				else
				{
					$this->set_error(1003, 'Impossible to write to the payment log file.', true);
				}
			} 
			else
			{
				$this->set_error(1004, 'Impossible to find the payment log file. The file has been deleted during the page elaboration', true);
			}
		}
	}

	/*
        * Function to get the log file for reading
        *
        * 
        * @return      nothing
        */
        function get_log()
	{
		return $this->log;
	}

	/*
        * Function to get the log file data for reading
        *
        * 
        * @return      nothing
        */
        function get_log_all()
	{
		if (file_exists($this->file_log))
		{
			if ($return  = @file($this->file_log))
			{
				$this->set_error(0003, 'Impossible to read the payment log file', false);
				return array();
			} 
			else
			{
				return $return;
			}
		} 
		else
		{
			$this->set_error(0004, 'The payment log file does not exist', false);
			return array();
		}
	}
	
	/*
        * Function to get the transaction number from the merchant gateway server after the card has been processed
        *
        * 
        * @return      nothing
        */
        function get_transactionnum()
	{
		// after that a card has been processed,
		// sometime it is possible retrieve the transaction number (often is the same than the authorization code).
		return $this->transnum;
	}
	
	/*
        * Function to get the transaction order id number from the merchant gateway server after the card has been processed
        *
        * @return      nothing
        */
        function get_order_id()
	{
		// after that a card has been processed,
		// sometime it is possible retrieve the order id if assign by the gateway.
		return $this->order['o_orderID'];
	}
	
	/*
        * Function to get the transaction code number from the merchant gateway server after the card has been processed
        *
        * @return      nothing
        */
        function get_transact_code()
	{
		// after that a card has been processed,
		// retrieve the transaction code.
		return $this->transact_code;
	}
	
	/*
        * Function to get the transaction response message from the merchant gateway server after the card has been processed
        *
        * @return      nothing
        */
        function get_response_message()
	{
		// before or after a card is processed,
		// retrieve the gateway response message.
		// return $this->response_message;
		$response = $this->get_error();
		return $response['text'];
	}
	
	/*
        * Function to get the transaction authorization message from the merchant gateway server after the card has been processed
        *
        * @return      nothing
        */
        function get_authorization()
	{
		// after that a card has been processed,
		// sometime it is possible retrieve the authorization code.
		return $this->authorization;
	}
	
	/*
        * Function to get the transaction response answer
        *
        * @return      nothing
        */
        function get_answer()
	{
		return implode('', $this->received);
	}
	
	/*
        * Function to get the transaction response error message
        *
        * @return      nothing
        */
        function get_error()
	{
		return $this->error;
	}
	
	/*
        * Function to save the transaction log response to the log file
        *
        * @param       string           log filename
        * 
        * @return      nothing
        */
        function save_log($file)
	{
		if (!empty($file))
		{
			if (file_exists($file))
			{
				if ($fp = @fopen($file, 'a'))
				{
					$this->set_error(0002, 'Impossible to write to the payment log file', false);
				}
				else
				{
					fclose($fp);
					$this->file_log = $file;
				}
			}
			else
			{
				$fp = @fopen($file, 'w');
				@fwrite($fp, 'paymentgateway log');
				@fclose($fp);
			}
			if (!file_exists($file))
			{
				$this->set_error(0001, 'Impossible to create the payment log file', false);
			}
			else
			{
				$this->file_log = $file;
			}
		}
		else
		{
			$this->file_log = '';
		}
	}
	
	/*
        * Function to set the curl binary and path details for the curl connection method
        *
        * @param       string           path to curl binary
        * 
        * @return      nothing
        */
        function set_curl($path)
	{
		$this->curl_path = $path;
	}

	/*
        * Function to set the curl certificate and path details for the curl connection method
        *
        * @param       string           path to curl certificate
        * 
        * @return      nothing
        */
        function set_crt($path)
	{
		$this->cert_file = $path;
	}
	
        /*
        * Function to set element start parser details used with curl_xml connection methods
        *
        * @param       string           parser
        * @param       string           tag
        * @param       string           attributes
        * 
        * @return      nothing
        */
        function element_start($parser, $tag, $attributes)
        {
                $this->currentTag = $tag;
        }

        /*
        * Function to set element end parser details used with curl_xml connection methods
        *
        * @param       string           parser
        * @param       string           tag
        * 
        * @return      nothing
        */
        function element_end($parser, $tag)
        {
                $this->currentTag = '';
        }

        /*
        * Function to set element character data end parser details used with curl_xml connection methods
        *
        * @param       string           parser
        * @param       string           character data
        * 
        * @return      nothing
        */
        function character_data($parser, $cdata)
        {
                $this->xmlData[$this->currentTag] = $cdata;
        }
        
	/*
        * Function to fetch the actual gateway server response message based on supplied argument data
        *
        * @param       array            transaction data array to send to gateway server
        * @param       string           url of gateway server
        * @param       string           url2 (optional test server url)
        * @param       string           method of communication response (get or post)
        * @param       string           force a particular communication type (curl_xml, fsockopen, etc)
        * 
        * @return      string           Returns the gateway response based on the transaction details provided
        */
        function fetch_gateway_response($data = array(), $url = '', $url2 = '', $method = 'POST', $force = '')
	{
                if ($this->debug)
                {
                        // set communication method to test mode
                        // this will use the perferred gateway "testpage" url instead of the real url
                        $url = $url2;
                }
                //print_r($data); exit;
                $parsed = parse_url($url);
                if (mb_strtolower($parsed['scheme']) == 'http')
                {
                        $https = 'http://';
                        $port = '80';
                }
                else
                {
                        $https = 'ssl://';
                        $port = '443';
                }
                if (isset($parsed['port']) AND $parsed['port'] > 0)
                {
                        $port = $parsed['port'];
                }
                $host = $parsed['host'];
                $uri = $parsed['path'] . (isset($parsed['query']) ? '?' . $parsed['query'] : '') . (isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '');
                //print_r($data); exit;
                $reqbody = '';
                if ($force == 'curl_xml')
                {
                        // construct xml data to send to payment gateway
                        // dertermine gateway for opening xml request tags
                        switch ($this->gateway)
			{
                                case 'psigate':
                                {
                                        $reqbody .= "<Order>\n";
                                        break;
                                }
                                case 'eway':
                                {
                                        $reqbody .= "<ewaygateway>\n";
                                        break;
                                }
                        }
                        foreach ($data AS $key => $val)
                        {
                                $reqbody .= "<" . $key . ">" . urldecode($val) . "</" . $key . ">\n";
                        }
                        // dertermine gateway for closing xml request tags
                        switch ($this->gateway)
			{
                                case 'psigate':
                                {
                                        $reqbody .= "</Order>\n";
                                        break;
                                }
                                case 'eway':
                                {
                                        // eway requires even blank keys to be submitted..
                                        $reqbody .= "<ewayOption1>0</ewayOption1>\n<ewayOption2>0</ewayOption2>\n<ewayOption3>0</ewayOption3>\n</ewaygateway>\n";
                                        break;
                                }
                        }
                }
                else
                {
                        foreach ($data AS $key => $val)
                        {
                                if ($val != '' AND $val != 'yyyy' AND $val != 'my')
                                {
                                        if ($reqbody == '')
                                        {
                                                $reqbody .= $key . '=' . urlencode($val);
                                        }
                                        else
                                        {
                                                $reqbody .= '&' . $key . '=' . urlencode($val);
                                        }
                                }
                        }        
                }
		$contentlength = mb_strlen($reqbody);
		if ($force == 'fsockopen')
		{
			//$this->set_log('fetch_gateway_response(): fsockopen');
			$in = fsockopen($https . $host, $port, $errno, $errstr, 60);
			if (!$in)
			{
				$this->set_error(9201, 'Impossible to connect to host via fsockopen(): ' . $host, true);
				return array();
			}
			else
			{
				$reqheader = $method . ' ' . $uri . " HTTP/1.0\n" .
				'Host: ' . $host . "\n" .
				"User-Agent: " . SITE_NAME . " (" . HTTP_SERVER . ")\n" .
				"Content-Type: application/x-www-form-urlencoded\n" .
				'Content-Length: ' . $contentlength . "\n" .
				"Connection: close\n\n" .
				$reqbody . "\n";
                                //echo $reqheader; exit;
				fputs($in, $reqheader);
			}
			$line = '';
			while (fgets($in, 4096) != "\r\n");
			while ($line = @fgets($in, 4096))
			{
				$return[] = $line;
			}
			fclose($in);
                        /*
                        Array
                        (
                            [0] => 1,0,1,Approved Sale,STGRS,_,100023159610,283242481,Account Deposit Credit via Visa : #1111 into Online Account Balance,13.00,CC,AUTH_CAPTURE,1,Peter,Salzmann,ILance,123 Anywhere St.,Grafton,Ontario,K0K-2G0,Canada,+1-905-349-2944,,,,,,,,,,,,,,,,a59df388a6b2568176feffe00d82a913,_,
                        )
                        */
			return $return;
		}
                // #### CURL VIA EXEC() ########################################
		else if ($force == 'curl')
		{
			//$this->set_log('fetch_gateway_response(): curl via exec()');
			if ($https == 'ssl://')
			{
				$https = 'https://';
			}
			if (mb_strtolower($method) == 'post')
			{
				$exec_str = $this->curl_path . ' -m 120 -d "' . $reqbody . '" "' . $https . $host . ':' . $port . $uri . '" -L';
			}
			else
			{
				$exec_str = $this->curl_path . ' -m 120 "' . $https . $host . ':' . $port . $uri . '?' . $reqbody . '" -L';
			}
			if (!empty($this->cert_file))
			{
				$exec_str .= ' --cert "' . $this->cert_file . '"';
			}
			exec($exec_str, $ret_arr, $ret_num);
			if ($ret_num != 0)
			{
				$this->set_error(9301, 'Error while executing: ' . $exec_str, true);
				return array();
			}
			if (!is_array($ret_arr))
			{
				$this->set_error(9302, 'Error while executing: ' . $exec_str . ' - ' . '$ret_arr is not an array', true);
				return array();
			}
			return $ret_arr;
		}
                // #### CURL PHP EXTENSION #####################################
		else if ($force == 'curl_ext')
		{
			//$this->set_log('fetch_gateway_response(): curl extension');
                        if (!extension_loaded('curl'))
			{
				$this->set_error(9101, 'curl php extension has not been enabled.', true);
				return array();
			}
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url); 
			curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_setopt($ch, CURLOPT_USERAGENT, SITE_NAME . ' (' . HTTP_SERVER . ')');
			curl_setopt($ch, CURLOPT_POST, 1); 
			curl_setopt($ch, CURLOPT_POSTFIELDS, $reqbody);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0); 
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			//if (!extension_loaded('openssl'))
			//{
                                // this allows curl to process without a certification key
                        //       curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			//}
			if (!empty($this->cert_file) AND $this->cert_file != '')
			{
                                // if you have the crt file, it can be specified here
                                curl_setopt($ch, CURLOPT_CAINFO, $this->cert_file); 
			}
			$tmp = curl_exec($ch);
			if (curl_errno($ch) > 0)
			{
				$this->set_error(9101, curl_error($ch), true);
				return array();
			}
			curl_close($ch);
			$lines = explode("\n", $tmp);
                        /*
                        Array
                        (
                            [0] => 1,0,1,Approved Sale,STGRS,_,100023159610,283242481,Account Deposit Credit via Visa : #1111 into Online Account Balance,13.00,CC,AUTH_CAPTURE,1,Peter,Salzmann,ILance,123 Anywhere St.,Grafton,Ontario,K0K-2G0,Canada,+1-905-349-2944,,,,,,,,,,,,,,,,a59df388a6b2568176feffe00d82a913,_,
                        )
                        */
			return $lines;
		}
                // #### CURL VIA XML ###########################################
		else if ($force == 'curl_xml')
		{
                        //$this->set_log('fetch_gateway_response(): curl xml');
                        if (!extension_loaded('curl'))
			{
				$this->set_error(9101, 'curl php extension has not been enabled.', true);
				return array();
			}
                        $ret_arr = '';
                        $ch = curl_init($url);
                        curl_setopt($ch, CURLOPT_USERAGENT, SITE_NAME . ' (' . HTTP_SERVER . ')');
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $reqbody);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 240);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                        $xmlResponse = curl_exec($ch);
                        if (curl_errno($ch) > 0)
			{
				$this->set_error(9101, curl_error($ch), true);
				return array();
			}
                        else
                        {
                                $this->parser = xml_parser_create();
                                xml_parser_set_option ($this->parser, XML_OPTION_CASE_FOLDING, FALSE);
                                xml_set_object($this->parser, $this);
                                xml_set_element_handler($this->parser, 'element_start', 'element_end');
                                xml_set_character_data_handler($this->parser, 'character_data');
                                xml_parse($this->parser, $xmlResponse, TRUE);
                                $ret_arr = $this->xmlData;
                                xml_parser_free($this->parser);  
                        }
                        curl_close($ch);
                        /*
                        Array
                        (
                            [0] => 1,0,1,Approved Sale,STGRS,_,100023159610,283242481,Account Deposit Credit via Visa : #1111 into Online Account Balance,13.00,CC,AUTH_CAPTURE,1,Peter,Salzmann,ILance,123 Anywhere St.,Grafton,Ontario,K0K-2G0,Canada,+1-905-349-2944,,,,,,,,,,,,,,,,a59df388a6b2568176feffe00d82a913,_,
                        )
                        */
                        return $ret_arr;
		}
	}
	
	/*
        * Function to process a PayPal Payment Pro payment
        *
        * @return      string           Returns 1 on success or $this->error array on failure
        */
        function process_paypal_pro()
	{
		global $ilance, $ilconfig;
		$this->paypal_pro['api_username'] = trim($ilconfig['paypal_pro_username']);
		$this->paypal_pro['api_password'] = trim($ilconfig['paypal_pro_password']);
		$this->paypal_pro['api_signature'] = trim($ilconfig['paypal_pro_signature']);
		$ilance->paypal_pro = construct_object('api.paypal_pro', $this->paypal_pro, $ilconfig['paypal_pro_sandbox']);
		$ilance->paypal_pro->ip_address = $_SERVER['REMOTE_ADDR'];
		// Order Totals (amount_total is required)
		$ilance->paypal_pro->amount_total = $this->order['o_amount'];//$total;
		$ilance->paypal_pro->amount_shipping = 0.00;
		//$ilance->paypal_pro->currency_code = $ilance->currency->currencies[$auction_currency]['code'];
		// Credit Card Information (required)
		$ilance->paypal_pro->credit_card_number = $this->card['cc_number'];
		$ilance->paypal_pro->credit_card_type = ucfirst($this->card['cc_type']);
		$ilance->paypal_pro->cvv2_code = $this->card['cc_cvv'];
		$ilance->paypal_pro->expire_date = $this->card['cc_expmm'] . $this->card['cc_expyy'];
		// Billing Details (required)
		$ilance->paypal_pro->first_name = $this->customer['c_fname'];
		$ilance->paypal_pro->last_name = $this->customer['c_lname'];
		$ilance->paypal_pro->address1 = $this->customer['c_address'];
		$ilance->paypal_pro->address2 = $this->customer['c_address'];
		$ilance->paypal_pro->city = $this->customer['c_city'];
		$ilance->paypal_pro->state = $this->customer['c_state'];
		$ilance->paypal_pro->postal_code = $this->customer['c_zip'];
		$ilance->paypal_pro->phone_number = $this->customer['c_phone'];
		$ilance->paypal_pro->country_code = $_SESSION['ilancedata']['user']['countryshort'];
		// Shipping Details (NOT required)
		$ilance->paypal_pro->email = $_SESSION['ilancedata']['user']['email'];
		$ilance->paypal_pro->shipping_name = $_SESSION['ilancedata']['user']['firstname'] . $_SESSION['ilancedata']['user']['lastname'];
		$ilance->paypal_pro->shipping_address1 = !empty($_SESSION['ilancedata']['user']['address']) ? $_SESSION['ilancedata']['user']['address'] : $this->customer['c_address'];
		$ilance->paypal_pro->shipping_address2 = !empty($_SESSION['ilancedata']['user']['address2']) ? $_SESSION['ilancedata']['user']['address2'] : $this->customer['c_address'];
		$ilance->paypal_pro->shipping_city = !empty($_SESSION['ilancedata']['user']['city']) ? $_SESSION['ilancedata']['user']['city'] : $this->customer['c_city'];
		$ilance->paypal_pro->shipping_state = $_SESSION['ilancedata']['user']['state'];
		$ilance->paypal_pro->shipping_postal_code = $_SESSION['ilancedata']['user']['postalzip'];
		$ilance->paypal_pro->shipping_phone_number = $_SESSION['ilancedata']['user']['phone'];
		$ilance->paypal_pro->shipping_country_code = $_SESSION['ilancedata']['user']['countryshort'];
		$ilance->paypal_pro->addItem($this->order['o_description'], $this->order['o_transnum'], '1', 0, $this->order['o_amount']);
		// Perform the payment
		$response = $ilance->paypal_pro->DoDirectPayment();
		if ($response AND isset($ilance->paypal_pro->Response['ACK']) AND strtoupper($ilance->paypal_pro->Response['ACK']) == 'SUCCESS')
		{
			$this->error['fatal'] == false;
			return 1;
		}
		else 
		{
			$ilance->paypal_pro->Response['L_ERRORCODE0'] = isset($ilance->paypal_pro->Response['L_ERRORCODE0']) ? $ilance->paypal_pro->Response['L_ERRORCODE0'] : '';
			$ilance->paypal_pro->Response['L_LONGMESSAGE0'] = isset($ilance->paypal_pro->Response['L_LONGMESSAGE0']) ? $ilance->paypal_pro->Response['L_LONGMESSAGE0'] : '';
			$ilance->paypal_pro->Response['L_SHORTMESSAGE0'] = isset($ilance->paypal_pro->Response['L_SHORTMESSAGE0']) ? $ilance->paypal_pro->Response['L_SHORTMESSAGE0'] : '';
			$ilance->paypal_pro->Response['L_SERVERITYCODE0'] = isset($ilance->paypal_pro->Response['L_SERVERITYCODE0']) ? $ilance->paypal_pro->Response['L_SERVERITYCODE0'] : '';
			$this->transact_code = isset($ilance->paypal_pro->Response['L_ERRORCODE0']) ? $ilance->paypal_pro->Response['L_ERRORCODE0'] : '';
                        $this->authorization = isset($ilance->paypal_pro->Response['L_LONGMESSAGE0']) ? $ilance->paypal_pro->Response['L_LONGMESSAGE0'] : '';
                        $this->set_error($ilance->paypal_pro->Response['L_ERRORCODE0'], $ilance->paypal_pro->Response['L_LONGMESSAGE0'], $ilance->paypal_pro->Response['L_SHORTMESSAGE0'], $ilance->paypal_pro->Response['L_SERVERITYCODE0'], $ilance->paypal_pro->Response['L_ERRORCODE0']);
			$this->error['fatal'] == true;
		}
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>