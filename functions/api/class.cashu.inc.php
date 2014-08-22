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
* Cashu class to perform the majority of functions including ipn response handling within ILance.
*
* @package      iLance\PaymentGateway\CashU
* @version      4.0.0.8059
* @author       ILance
*/
class cashu
{
        var $cashu_post_vars = array();
        var $cashu_response;
        var $timeout;
        var $error_email;
        var $send_time;
        var $currencies_accepted = array('USD', 'CSH', 'AED', 'EUR', 'JOD', 'EGP', 'SAR');
        /**
        * Function for parsing incoming variables from the payment gateway
        *
        * @param       array       posted cashu keys and values
        *
        * @return      array
        */
        function cashu($cashu_post_vars = array())
        {
                if (!empty($cashu_post_vars))
                {
                        $this->cashu_post_vars = $cashu_post_vars;
                }
                else
                {
                        $this->cashu_post_vars = array();
                }
        }
        
        function generate_md5_digest($total = 0)
        {
                global $ilconfig;
                $digest = mb_strtolower($ilconfig['cashu_business_email']) . ':' . $total . ':' . mb_strtolower($ilconfig['cashu_master_currency']) . ':' . mb_strtolower($ilconfig['cashu_secret_code']);
                $digest = md5($digest);
                return $digest;
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
                $html = '<form method="post" action="https://www.cashu.com/cgi-bin/pcashu.cgi" accept-charset="UTF-8" style="margin:0px">
<input type="hidden" name="merchant_id" value="' . $merchantid . '" />
<input type="hidden" name="token" value="' . $this->generate_md5_digest($amount) . '" />
<input type="hidden" name="display_text" value="' . $description . '" />
<input type="hidden" name="currency" value="' . $currency . '" />
<input type="hidden" name="amount" value="' . $amount . '" />
<input type="hidden" name="language" value="en" />
<input type="hidden" name="email" value="' . $payer_email . '" />
<input type="hidden" name="txt1" value="' . $description . '" />
<input type="hidden" name="test_mode" value="' . $testmode . '" />';
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
        *
        * @return      string        HTML representation of the form (without the ending </form>)
        */
        function print_direct_payment_form($amount = 0, $description = '', $merchantid = '', $currency = '', $customencrypted = '', $returnurl = '')
        {
                global $ilpage;
                $html = '<form method="post" action="https://www.cashu.com/cgi-bin/pcashu.cgi" accept-charset="UTF-8" style="margin:0px">
<input type="hidden" name="merchant_id" value="' . $merchantid . '" />
<input type="hidden" name="token" value="' . $this->generate_md5_digest($amount) . '" />
<input type="hidden" name="display_text" value="' . $description . '" />
<input type="hidden" name="currency" value="' . $currency . '" />
<input type="hidden" name="amount" value="' . $amount . '" />
<input type="hidden" name="language" value="en" />
<input type="hidden" name="txt1" value="' . $description . '" />
<input type="hidden" name="txt2" value="' . $customencrypted . '" />
<input type="hidden" name="test_mode" value="' . $testmode . '" />';
                return $html;   
        }
        
        /**
        * Function for determining (internally) if the processed transaction has been verified (true or false)
        *
        * @return      bool          true or false
        */
        function is_verified()
        {
                global $ilconfig;
                $calc_hash = $this->generate_md5_digest($this->get_transaction_amount());
                $recv_hash = rawurldecode($this->get_transaction_md5_digest());
                if ($calc_hash === $recv_hash)
                {
                        return true;
                }
                return false;
        }
        
        /**
        * Function for storing the processed payment currency for later retrevial.
        *
        * @return      string         transaction id
        */
        function get_transaction_currency()
        {
                return $this->cashu_post_vars['currency'];    
        }
        
        /**
        * Function for fetching the md5sig sent to us in a response from the payment gateway
        *
        * @return      string         transaction type
        */
        function get_transaction_md5_digest()
        {
                return $this->cashu_post_vars['token'];
        }
        
        /**
        * Function for storing the processed payment transaction id for later retrevial.
        *
        * @return      string         transaction id
        */
        function get_transaction_id()
        {
                return $this->cashu_post_vars['trn_id'];    
        }
        
        /**
        * Function for storing the processed payment transaction amount for later retrevial.
        *
        * @return      string         transaction id
        */
        function get_transaction_amount()
        {
                return $this->cashu_post_vars['amount'];    
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
                $message .= "\n\n" . SITE_NAME . " received the following IPN response from CashU.  Please use the following information for debug purposes only:\n\n*****************************\n";
                @reset($this->cashu_post_vars);
                while (@list($key, $value) = @each($this->cashu_post_vars))
                {
                        $message .= $key . ":" . " \t$value\n";
                }
                $message = "$date\n\n" . $message . "\n*****************************\n\n";
                if ($this->error_email)
                {
			global $ilance;
			$ilance->email->mail = $this->error_email;
			$ilance->email->from = SITE_EMAIL;
			$ilance->email->subject = 'CashU IPN Gateway Error';
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