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
* Paypal class to perform the majority of functions including ipn response handling.
*
* @package      iLance\PaymentGateway\PayPal
* @version      4.0.0.8059
* @author       ILance
*/
class paypal
{
        /**
        * Array of PayPal post variables
        *
        * @var array
        * @access public
        */
        var $paypal_post_vars = array();
        /**
        * PayPal response placeholder
        *
        * @var string
        * @access public
        */
        var $paypal_response;
        /**
        * PayPalt gateway timeout placeholder
        *
        * @var integer
        * @access public
        */
        var $timeout;
        /**
        * PayPal error email address
        *
        * @var string
        * @access public
        */
        var $error_email;
        /**
        * PayPal send time placeholder
        *
        * @var string
        * @access public
        */
        var $send_time;
        /**
        * PayPal currencies accepted array
        *
        * @var array
        * @access public
        */
        var $currencies_accepted = array('USD', 'GBP', 'EUR', 'CAD');
        
        /**
        * Function for parsing incoming variables from the payment gateway
        *
        * @param       array       posted paypal keys and values
        *
        * @return      nothing
        */
        function paypal($paypal_post_vars = array())
        {
                if (!empty($paypal_post_vars))
                {
                        $this->paypal_post_vars = $paypal_post_vars;
                }
                else
                {
                        $this->paypal_post_vars = array();
                }
        }
        
        /**
        * Function for printing the payment processor custom generated form via POST method.
        *
        * @param       integer       user id
        * @param       string        payer email address
        * @param       string        amount to process
        * @param       integer       associated invoice id
        * @param       integer       associated subscription id
        * @param       string        transaction description
        * @param       string        merchant id
        * @param       string        master currency
        * @param       string        pass phrase used in some processors (usually stored with processor also)
        * @param       string        custom generated payment repsonse arguments to be decrypted by ilance payment processor
        * @param       bool          defines if this payment form should return a test - mode parameter (if available)
        *
        * @return      string        HTML representation of the form (without the ending </form>)
        */
        function print_payment_form($userid = 0, $payer_email = '', $amount = 0, $invoiceid = 0, $subscriptionid = 0, $description = '', $merchantid = '', $currency = '', $passphrase = '', $customencrypted = '', $testmode = 0)
        {
                global $ilpage, $ilconfig;
                $address = ($ilconfig['paypal_sandbox'] == '1') ? 'www.sandbox.paypal.com' : 'www.paypal.com';
                $html = '<form method="post" action="https://' . $address . '/cgi-bin/webscr" accept-charset="UTF-8" style="margin:0px">
<input type="hidden" name="cmd" value="_xclick" />
<input type="hidden" name="business" value="' . $merchantid . '" />
<input type="hidden" name="return" value="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&id=' . $invoiceid . '" />
<input type="hidden" name="custom" value="' . $customencrypted . '" />
<input type="hidden" name="undefined_quantity" value="0" />
<input type="hidden" name="item_name" value="' . $description . '" />
<input type="hidden" name="charset" value="utf-8">
<input type="hidden" name="amount" value="' . $amount . '" />
<input type="hidden" name="currency_code" value="' . $currency . '" />
<input type="hidden" name="no_shipping" value="1" />
<input type="hidden" name="cancel_return" value="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&id=' . $invoiceid . '" />
<input type="hidden" name="no_note" value="1" />
<input type="hidden" name="notify_url" value="' . HTTPS_SERVER . $ilpage['payment'] . '?do=_paypal" />
                ' . ((isset($payer_email) AND !empty($payer_email)) ? '<input type="hidden" name="payer_email" value="' . handle_input_keywords($payer_email) . '" />' : '');
                return $html;   
        }
        
        /**
        * Function for printing the recurring payment processor custom generated form via POST method.
        *
        * @param       string        payer email address
        * @param       integer       subscription id
        * @param       string        amount to process
        * @param       string        unit
        * @param       integer       length
        * @param       string        transaction title
        * @param       string        transaction description
        * @param       string        gateway currency to use
        * @param       string        custom generated payment repsonse arguments to be decrypted by ilance payment processor
        * @param       string        js onsubmit form code
        * @param       integer       ismodify subscription modify update for paypal
        *
        * @return      string        HTML representation of the form (without the ending </form>)
        */
        function print_recurring_payment_form($payer_email = '', $subscriptionid = 0, $amount = 0, $units = '', $length = 0, $title = '', $description = '', $currency = '', $customencrypted = '', $onsubmit = '', $ismodify = 0)
        {
                global $ilance, $ilpage, $ilconfig, $show;
                $address = ($ilconfig['paypal_sandbox'] == '1') ? 'www.sandbox.paypal.com' : 'www.paypal.com';
                $html = '<form name="ilform" action="https://' . $address . '/cgi-bin/webscr" method="post" accept-charset="UTF-8" onsubmit="' . $onsubmit . '" style="margin:0px">
<input type="hidden" name="cmd" value="_xclick-subscriptions" />
<input type="hidden" name="business" value="' . $payer_email . '" />
<input type="hidden" name="item_name" value="' . $description . '" />
<input type="hidden" name="charset" value="utf-8">
<input type="hidden" name="item_number" value="' . $subscriptionid . '" />
<input type="hidden" name="currency_code" value="' . mb_strtoupper($currency) . '" />
<input type="hidden" name="a3" value="' . $amount . '" />
<input type="hidden" name="p3" value="' . $length . '" />
<input type="hidden" name="t3" value="' . $units . '" />
<input type="hidden" name="src" value="1" />
<input type="hidden" name="sra" value="1" />
<input type="hidden" name="no_shipping" value="1" />
<input type="hidden" name="shipping" value="0.00" />
<input type="hidden" name="return" value="' . HTTP_SERVER . $ilpage['accounting'] . '" />
<input type="hidden" name="cancel_return" value="' . HTTP_SERVER . $ilpage['accounting'] . '" />
<input type="hidden" name="notify_url" value="' . HTTPS_SERVER . $ilpage['payment'] . '?do=_paypal" />
<input type="hidden" name="custom" value="' . $customencrypted . '" />
<input type="hidden" name="no_note" value="1" />
<input type="hidden" name="undefined_quantity" value="0" />';
                if ($ismodify)
                {
                        $html .= '<input type="hidden" name="modify" value="1" />';
                        $show['subscriptionmodify'] = 1;
                }
                else
                {
                        $show['subscriptionmodify'] = 0;
                }
                return $html;
        }
        
        /**
        * Function for printing the payment processor custom generated form via POST method.
        *
        * @param       string        amount to process
        * @param       string        transaction description
        * @param       string        sellers payment email
        * @param       string        master currency
        * @param       string        custom generated payment repsonse arguments to be decrypted by ilance payment processor
        * @param       string        return url
        * @param       array         payment data variables
        * @param       boolean       no shipping (default true)
        *
        * @return      string        HTML representation of the form (without the ending </form>)
        */
        function print_direct_payment_form($amount = 0, $description = '', $merchantid = '', $currency = '', $customencrypted = '', $returnurl = '', $vars = array(), $no_ship = 1)
        {
                global $ilpage, $ilconfig, $ilance;
                $pid = isset($vars['pid']) ? $vars['pid'] : '';
                $address = ($ilconfig['paypal_sandbox'] == '1') ? 'www.sandbox.paypal.com' : 'www.paypal.com';
		$languageiso = isset($ilance->language->cache[$_SESSION['ilancedata']['user']['languageid']]['languageiso']) ? $ilance->language->cache[$_SESSION['ilancedata']['user']['languageid']]['languageiso'] : 'EN';
                $html = '<form method="post" action="https://' . $address . '/cgi-bin/webscr" accept-charset="utf-8" style="margin:0px">
<input type="hidden" name="cmd" value="_xclick" />
<input type="hidden" name="business" value="' . handle_input_keywords($merchantid) . '" />
<input type="hidden" name="return" value="' . $returnurl . '" />
<input type="hidden" name="cancel_return" value="' . $returnurl . '" />
<input type="hidden" name="custom" value="' . $customencrypted . '" />
<input type="hidden" name="undefined_quantity" value="0" />
<input type="hidden" name="item_name" value="' . $description . '" />
<input type="hidden" name="charset" value="utf-8">
<input type="hidden" name="item_number" value="' . $pid . '">
<input type="hidden" name="amount" value="' . $amount . '" />
<input type="hidden" name="currency_code" value="' . $currency . '" />
<input type="hidden" name="no_shipping" value="' . $no_ship . '" />
<input type="hidden" name="no_note" value="1" />
<input type="hidden" name="address_override" value="0">
<input type="hidden" name="first_name" value="' . handle_input_keywords($_SESSION['ilancedata']['user']['firstname']) . '">
<input type="hidden" name="last_name" value="' . handle_input_keywords($_SESSION['ilancedata']['user']['lastname']) . '">
<input type="hidden" name="address1" value="' . handle_input_keywords($_SESSION['ilancedata']['user']['address']) . '">
<input type="hidden" name="address2" value="' . handle_input_keywords($_SESSION['ilancedata']['user']['address2']) . '">
<input type="hidden" name="city" value="' . handle_input_keywords($_SESSION['ilancedata']['user']['city']) . '">
<input type="hidden" name="state" value="' . handle_input_keywords($_SESSION['ilancedata']['user']['state']) . '">
<input type="hidden" name="zip" value="' . handle_input_keywords($_SESSION['ilancedata']['user']['postalzip']) . '">
<input type="hidden" name="country" value="' . handle_input_keywords($_SESSION['ilancedata']['user']['countryshort']) . '">
<input type="hidden" name="email" value="' . handle_input_keywords($_SESSION['ilancedata']['user']['email']) . '">
<input type="hidden" name="address_name" value="' . handle_input_keywords($_SESSION['ilancedata']['user']['firstname'] . ' ' . $_SESSION['ilancedata']['user']['lastname']) . '">
<input type="hidden" name="aaddress_street" value="' . handle_input_keywords($_SESSION['ilancedata']['user']['address'] . ' ' . $_SESSION['ilancedata']['user']['address2']) . '">
<input type="hidden" name="address_city" value="' . handle_input_keywords($_SESSION['ilancedata']['user']['city']) . '">
<input type="hidden" name="address_state" value="' . handle_input_keywords($_SESSION['ilancedata']['user']['state']) . '">
<input type="hidden" name="address_zip" value="' . handle_input_keywords($_SESSION['ilancedata']['user']['postalzip']) . '">
<input type="hidden" name="contact_phone" value="' . handle_input_keywords($_SESSION['ilancedata']['user']['phone']) . '">
<input type="hidden" name="address_country" value="' . handle_input_keywords($_SESSION['ilancedata']['user']['country']) . '">
<input type="hidden" name="address_country_code" value="' . handle_input_keywords($_SESSION['ilancedata']['user']['countryshort']) . '">
<input type="hidden" name="address_status" value="confirmed">
<input type="hidden" name="notify_url" value="' . HTTPS_SERVER . $ilpage['payment'] . '?do=_paypal" />';
                return $html;   
        }
	
	/**
        * Function for refund payment to user.
        *
        * @param       string        url params
        *
        * @return      string        paypal response array
        */
        function print_refund_payment_form($nvpStr_)
        {
		global $ilconfig, $ilance;
		$API_UserName = urlencode($ilconfig['paypal_username']);
		$API_Password = urlencode($ilconfig['paypal_password']);
		$API_Signature = urlencode($ilconfig['paypal_signature']);
		$API_Endpoint = "https://api-3t.paypal.com/nvp";
		if ($ilconfig['paypal_sandbox'] == '1') 
		{
			$API_Endpoint = "https://api-3t.sandbox.paypal.com/nvp";
		}
		$version = urlencode('51.0');
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		$nvpreq = "METHOD=RefundTransaction&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr_";
		curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);
		$httpResponse = curl_exec($ch);
		if (!$httpResponse) 
		{
			exit("$methodName_ failed: ".curl_error($ch).'('.curl_errno($ch).')');
		}
		$httpResponseAr = explode("&", $httpResponse);
		$httpParsedResponseAr = array();
		foreach ($httpResponseAr AS $i => $value) 
		{
			$tmpAr = explode("=", $value);
			if (sizeof($tmpAr) > 1) 
			{
				$httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
			}
		}
		if ((0 == sizeof($httpParsedResponseAr)) OR !array_key_exists('ACK', $httpParsedResponseAr)) 
		{
			exit("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
		}
		return $httpParsedResponseAr;
	}
        
        /**
        * Function for sending a repsonse to the payment gateway for verification of payment authentication and status.
        *
        * @return      nothing
        */
        function send_response()
        {
        	global $ilconfig;
                $address = ($ilconfig['paypal_sandbox'] == '1') ? 'www.sandbox.paypal.com' : 'www.paypal.com';
                $port = ($ilconfig['paypal_sandbox'] == '1') ? '80' : '443';
                $protocol = ($ilconfig['paypal_sandbox'] == '1') ? '' : 'ssl://';
                $req = 'cmd=_notify-validate';
                if (!empty($this->paypal_post_vars) AND is_array($this->paypal_post_vars))
                {
                        foreach ($this->paypal_post_vars AS $key => $value)
                        { 
                                $value = urlencode(stripslashes($value)); 
                                $req .= "&$key=$value"; 
                        } 
                }
                else
                {
                        $this->error_out('Warning: PayPal variables are missing or do not exist.  It is possible this payment.php script was manually executed from a browser.');
                        exit();
                }
                //post back to PayPal system to validate 
                $header = "POST /cgi-bin/webscr HTTP/1.1\r\n"; 
                $header .= "Content-Type: application/x-www-form-urlencoded\r\n"; 
                $header .= "Host: $address\r\n"; 
                $header .= "Connection: close\r\n"; 
                $header .= "Content-Length: " . strlen($req) . "\r\n\r\n"; 
                $fp = fsockopen ($protocol . "$address", $port, $errno, $errstr, 30); 
                if (!$fp)
                { 
                        $this->error_out('Warning: could not communicate with PayPal via PHP function: fsockopen() error: ' . $errstr);
                        exit();
                } 
                $this->paypal_response = '';
                if ($fp)
                { 
                        fputs ($fp, $header . $req); 
                        while (!feof($fp))
                        { 
                                $this->paypal_response .= fgets ($fp, 1024); 
                                $this->paypal_response = trim($this->paypal_response); //NEW & IMPORTANT 
                        } 
                        fclose($fp);
                        $fp2 = fopen(PAYMENTLOG . 'paypal-ipn-' . date('Y-m-d') . '.log', 'a');
                        fwrite($fp2, "BEGIN " . date('Y-m-d h:i:s') . "\n");
                        fwrite($fp2, "PAYPAL SENT: " . $req . "\n");
                        fwrite($fp2, "END " . date('Y-m-d h:i:s') . "\n");
                        fclose($fp2);
                }
        }
        
        /**
        * Function for determining (internally) if the processed transaction has been verified (true or false)
        *
        * @return      bool          true or false
        */
        function is_verified()
        {
                if (mb_ereg('VERIFIED', $this->paypal_response))
                {
                        return true;
                }
                return false;
        }
        
        /**
        * Function for storing the processed payment status for later retrevial.
        *
        * @return      string         payment status
        */
        function get_payment_status()
        {
                return $this->paypal_post_vars['payment_status'];
        }
        
        /**
        * Function for storing the processed payment type for later retrevial.
        *
        * @return      string         payment type
        */
        function get_payment_type()
        {
                // echeck - payment funded with e-check
                // instant - payment was funded with paypal balance, credit card, or instant transfer
                return $this->paypal_post_vars['payment_type'];
        }
        
        /**
        * Function for storing the processed payment transaction id
        *
        * @return      string         transaction id
        */
        function get_transaction_id()
        {
                return $this->paypal_post_vars['txn_id'];    
        }
        
        /**
        * Function for storing the processed payment parent transaction id
        *
        * @return      string         transaction id
        */
        function get_parent_transaction_id()
        {
                return $this->paypal_post_vars['parent_txn_id'];    
        }
        
        /**
        * Function for storing the processed payment transaction amount
        *
        * @return      string         transaction id
        */
        function get_transaction_amount()
        {
                return $this->paypal_post_vars['mc_gross'];
        }
        
        /**
        * Function for storing the processed payment transaction type
        *
        * @return      string         transaction type
        */
        function get_transaction_type()
        {
                return $this->paypal_post_vars['txn_type'];    
        }
        
        /**
        * Function for sending any error emails from the process to the administrator.
        *
        * @param       string         error message text
        * 
        * @return      nothing
        */
        function error_out($text = '')
        {
                $date = date("D M j G:i:s T Y", time());
                $message = $text;
                $message .= "\n" . SITE_NAME . " received the following IPN response from PayPal through the payment.php script.  Please use the following information for debug purposes only:\n\n*****************************\n";
                @reset($this->paypal_post_vars);
                while (@list($key, $value) = @each($this->paypal_post_vars))
                {
                        $message .= $key . ":" . " \t$value\n";
                }
                $message = "$date\n\n" . $message . "\n*****************************\n\nBest,\nTeam " . SITE_NAME;
                if (isset($this->error_email) AND !empty($this->error_email))
                {
			global $ilance;
			$ilance->email->mail = $this->error_email;
			$ilance->email->from = SITE_EMAIL;
			$ilance->email->subject = 'PayPal IPN Information from ' . SITE_NAME . ' (' . HTTP_SERVER . ')';
			$ilance->email->message = $message;
			$ilance->email->send();
                }
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>