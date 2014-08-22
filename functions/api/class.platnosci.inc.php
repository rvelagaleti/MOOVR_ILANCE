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
* Platnosci class to perform the majority of functions including ipn response handling.
*
* @package      iLance\PaymentGateway\Platnosci
* @version      4.0.0.8059
* @author       ILance
*/
class platnosci
{
        var $platnosci_post_vars = array();
        var $platnosci_response;
        var $timeout;
        var $error_email;
        var $send_time;
        var $currencies_accepted = array('PLN');
        var $response = '';
        
        /**
        * Function for parsing incoming variables from the payment gateway
        *
        * @param       array       posted platnosci keys and values
        *
        * @return      array
        */
        function platnosci($platnosci_post_vars = array())
        {
                if (!empty($platnosci_post_vars))
                {
                        $this->platnosci_post_vars = $platnosci_post_vars;
                }
                else
                {
                        $this->platnosci_post_vars = array();
                }
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
        *
        * @return      string        HTML representation of the form (without the ending </form>)
        */
        function print_direct_payment_form($amount = 0, $description = '', $currency = '', $customencrypted = '', $returnurl = '')
        {
                global $ilance, $ilconfig, $headinclude;
		$ts = time();
		$sess_id = md5(rand());
		$_SESSION['ilancedata']['user']['ts'] = $ts;
		$order_id = $customencrypted;
		$amount = str_replace(array(',', '.'), '', sprintf("%01.2f", $ilance->currency->string_to_number($amount)));
		$sig = md5($ilconfig['platnosci_pos_id'] . $sess_id . $ilconfig['platnosci_pos_auth_key'] . $amount . $description . $order_id . $_SESSION['ilancedata']['user']['firstname'] . $_SESSION['ilancedata']['user']['lastname'] . $_SESSION['ilancedata']['user']['address'] . $_SESSION['ilancedata']['user']['city'] . $_SESSION['ilancedata']['user']['postalzip'] . $_SESSION['ilancedata']['user']['email'] . IPADDRESS . $ts . $ilconfig['platnosci_pos_key1']);
		$html = '<form action="https://www.platnosci.pl/paygw/ISO/NewPayment/xml" method="POST" name="payform">
<input type="hidden" name="first_name" value="' . $_SESSION['ilancedata']['user']['firstname'] . '">
<input type="hidden" name="last_name" value="' . $_SESSION['ilancedata']['user']['lastname'] . '">
<input type="hidden" name="email" value="' . $_SESSION['ilancedata']['user']['email'] . '">
<input type="hidden" name="street" value="' . $_SESSION['ilancedata']['user']['address'] . '">
<input type="hidden" name="city" value="' . $_SESSION['ilancedata']['user']['city'] . '">
<input type="hidden" name="post_code" value="' . $_SESSION['ilancedata']['user']['postalzip'] . '">
<input type="hidden" name="order_id" value="' . $customencrypted . '">
<input type="hidden" name="pos_id" value="' . $ilconfig['platnosci_pos_id'] . '">
<input type="hidden" name="pos_auth_key" value="' . $ilconfig['platnosci_pos_auth_key'] . '">
<input type="hidden" name="session_id" value="' . $sess_id . '">
<input type="hidden" name="amount" value="' . $amount . '">
<input type="hidden" name="desc" value="' . $description . '">
<input type="hidden" name="client_ip" value="' . IPADDRESS . '">
<input type="hidden" name="js" value="1">';
				/*
				 * <input type="hidden" name="ts" value="'.$ts.'">
				<input type="hidden" name="sig" value="'.$sig.'">
				 */
				$headinclude .= '<script type="text/javascript">
<!--
document.forms[\'payform\'].js.value=1;
-->
</script>';
                return $html;   
        }
        function make_sig($ts, $sess_id, $key = '1')
        {
        	global $ilconfig;
        	$k = ($key == '1') ? $ilconfig['platnosci_pos_key1'] : $ilconfig['platnosci_pos_key2']; 
        	//			pos id, 					pay type, session id, pos auth key, 					amount, 	desc, 		desc2, order id, 	first name, 									last name, 							payback login, 		street, 						street hn, street an, 		city, 							post code, 								country, 			email, 						phone, language, client ip, ts, key1)
        	//return md5($ilconfig['platnosci_pos_id'] . '' . $sess_id . $ilconfig['platnosci_pos_auth_key'] . $amount . $description . '' . $order_id . $_SESSION['ilancedata']['user']['firstname'] . $_SESSION['ilancedata']['user']['lastname'] . '' . $_SESSION['ilancedata']['user']['address'] . '' . '' . $_SESSION['ilancedata']['user']['city'] . $_SESSION['ilancedata']['user']['postalzip'] . '' . $_SESSION['ilancedata']['user']['email'] . '' . '' . IPADDRESS . $ts . $ilconfig['platnosci_pos_key1']);
        	//return md5($ilconfig['platnosci_pos_id'] . $sess_id . $ilconfig['platnosci_pos_auth_key'] . $amount . $description . $order_id . $_SESSION['ilancedata']['user']['firstname'] . $_SESSION['ilancedata']['user']['lastname'] . $_SESSION['ilancedata']['user']['address'] . $_SESSION['ilancedata']['user']['city'] . $_SESSION['ilancedata']['user']['postalzip'] . $_SESSION['ilancedata']['user']['email'] . IPADDRESS . $ts . $k);
        	return md5($ilconfig['platnosci_pos_id'] . $sess_id . $ts . $k);
        }
        
        function get_status($parts)
        {
        	global $ilconfig;
		if ($parts[1] != $ilconfig['platnosci_pos_id'])
		{
			return array('code' => false,'message' => 'bledny numer POS');
		}
		$sig = md5($parts[1] . $parts[2] . $parts[3] . $parts[5] . $parts[4] . $parts[6] . $parts[7] . $ilconfig['platnosci_pos_key2']);
		if ($parts[8] != $sig)
		{
			return array('code' => false,'message' => 'bledny podpis');
		}
		switch ($parts[5])
		{
			case 1: return array('code' => $parts[5], 'message' => 'nowa'); break;
			case 2: return array('code' => $parts[5], 'message' => 'anulowana'); break;
			case 3: return array('code' => $parts[5], 'message' => 'odrzucona'); break;
			case 4: return array('code' => $parts[5], 'message' => 'rozpoczeta'); break;
			case 5: return array('code' => $parts[5], 'message' => 'oczekuje na odbior'); break;
			case 6: return array('code' => $parts[5], 'message' => 'autoryzacja odmowna'); break;
			case 7: return array('code' => $parts[5], 'message' => 'platnosc odrzucona'); break;
			case 99: return array('code' => $parts[5], 'message' => 'platnosc odebrana - zakonczona'); break;
			case 888: return array('code' => $parts[5], 'message' => 'bledny status'); break;
			default: return array('code' => false, 'message' => 'brak statusu'); break;
		}
	}
        
        /**
        * Function for sending a repsonse to the payment gateway for verification of payment authentication and status.
        *
        * @return      nothing
        */
        function send_response($sess_id)
        {
		global $ilconfig, $ilance;
		$server = 'www.platnosci.pl';
		$server_script = '/paygw/UTF/Payment/get';
		$key1 = trim($ilconfig['platnosci_pos_key1']);
		$ts = time();
		$sig = md5($ilconfig['platnosci_pos_id'] . $sess_id . $ts . $key1);		
		$parameters = "pos_id=" . trim($ilconfig['platnosci_pos_id']) . "&session_id=" . $sess_id . "&ts=" . $ts . "&sig=" . $sig;			
		$fsocket = false;
		$curl = false;
		$result = false;
		if ((PHP_VERSION >= 4.3) AND ($fp = @fsockopen('ssl://' . $server, 443, $errno, $errstr, 30))) 
		{
			$fsocket = true;
		} 
		else if (function_exists('curl_exec')) 
		{
			$curl = true;
		}
		if ($fsocket == true) 
		{
			$header = 'POST ' . $server_script . ' HTTP/1.0' . "\r\n" .
'Host: ' . $server . "\r\n" .
'Content-Type: application/x-www-form-urlencoded' . "\r\n" .
'Content-Length: ' . strlen($parameters) . "\r\n" .
'Connection: close' . "\r\n\r\n";
			@fputs($fp, $header . $parameters);
			$platnosci_response = '';
			while (!@feof($fp)) 
			{
				$res = @fgets($fp, 1024);
				$platnosci_response .= $res;
			}
			@fclose($fp);
		}
		else if ($curl == true) 
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://" . $server . $server_script);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_TIMEOUT, 20);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $parameters);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$platnosci_response = curl_exec($ch);
			curl_close($ch);
		} 
		else 
		{
			die("ERROR: No connect method ...\n");
		}
		if (eregi("<trans>.*<pos_id>([0-9]*)</pos_id>.*<session_id>(.*)</session_id>.*<order_id>(.*)</order_id>.*<amount>([0-9]*)</amount>.*<status>([0-9]*)</status>.*<desc>(.*)</desc>.*<ts>([0-9]*)</ts>.*<sig>([a-z0-9]*)</sig>.*</trans>", $platnosci_response, $parts))  
		{
			$result = $this->get_status($parts);
			$pos_id = $parts[1];
			$session_id = $parts[2];
			$order_id = $parts[3];
			$amount = $parts[4];
			$status = $parts[5];
			$desc = $parts[6];
			$ts = $parts[7];
			$sig = $parts[8];
			$this->response = $parts;
			return $this->response;
		}
		else 
		{
			return array('5' => '888');
		}
        }
        
        /**
        * Function for determining (internally) if the processed transaction has been verified (true or false)
        *
        * @return      bool          true or false
        */
        function is_verified()
        {
                return false;
        }
        
        /**
        * Function for storing the processed payment status for later retrevial.
        *
        * @return      string         payment status
        */
        function get_payment_status()
        {
                return $this->platnosci_post_vars['payment_status'];
        }
        
        /**
        * Function for storing the processed payment type for later retrevial.
        *
        * @return      string         payment type
        */
        function get_payment_type()
        {
                // echeck - payment funded with e-check
                // instant - payment was funded with platnosci balance, credit card, or instant transfer
                return $this->platnosci_post_vars['payment_type'];
        }
        
        /**
        * Function for storing the processed payment transaction id for later retrevial.
        *
        * @return      string         transaction id
        */
        function get_transaction_id()
        {
                return $this->platnosci_post_vars['txn_id'];    
        }
        
        /**
        * Function for storing the processed payment transaction amount for later retrevial.
        *
        * @return      string         transaction id
        */
        function get_transaction_amount()
        {
                // payment_gross depreciated: https://www.x.com/message/168279;jsessionid=194758CBB355AB9E942D506519951253.node0
                //return $this->platnosci_post_vars['payment_gross'];
                return $this->platnosci_post_vars['mc_gross'];
        }
        
        /**
        * Function for storing the processed payment transaction type for later retrevial.
        *
        * @return      string         transaction type
        */
        function get_transaction_type()
        {
                return $this->platnosci_post_vars['txn_type'];    
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
                $message .= "\n\n" . SITE_NAME . " received the following IPN response from Platnosci.  Please use the following information for debug purposes only:\n\n*****************************\n";
                @reset($this->platnosci_post_vars);
                while (@list($key, $value) = @each($this->platnosci_post_vars))
                {
                        $message .= $key . ":" . " \t$value\n";
                }
                $message = "$date\n\n" . $message . "\n*****************************\n\n";
                if ($this->error_email)
                {
			global $ilance;
			$ilance->email->mail = $this->error_email;
			$ilance->email->from = SITE_EMAIL;
			$ilance->email->subject = 'Platnosci IPN Gateway Error';
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