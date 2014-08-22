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
* Moneybookers class to perform the majority of functions including ipn response handling
*
* @package      iLance\PaymentGateway\MoneyBookers
* @version      4.0.0.8059
* @author       ILance
*/
class moneybookers
{
        var $moneybookers_post_vars = array();
        var $moneybookers_response;
        var $timeout;
        var $error_email;
        var $send_time;
        var $currencies_accepted = array('USD', 'GBP', 'EUR', 'CAD', 'HKD', 'SGD', 'JPY', 'AUD', 'CHF', 'DKK', 'SEK', 'NOK', 'ILS', 'MYR', 'NZD', 'TRY', 'TWD', 'THB', 'CZK', 'HUF', 'SKK', 'EEK', 'BGN', 'PLN', 'ISK', 'INR', 'LVL', 'KRW', 'ZAR', 'RON', 'HRK', 'LTL');
        
        /**
        * Function for parsing incoming variables from the payment gateway
        *
        * @param       array       posted moneybookers keys and values
        *
        * @return      array
        */
        function moneybookers($moneybookers_post_vars = array())
        {
                if (!empty($moneybookers_post_vars))
                {
                        $this->moneybookers_post_vars = $moneybookers_post_vars;
                }
                else
                {
                        $this->moneybookers_post_vars = array();
                }
        }
        
        function generate_md5_digest()
        {
                global $ilconfig;
                $md5 = mb_strtoupper(md5($ilconfig['moneybookers_secret_code']));
                $digest = $this->get_transaction_merchant_id() . $this->get_transaction_id() . $md5 . $this->get_transaction_amount() . $this->get_transaction_currency() . $this->get_raw_payment_status();
                $digest = md5($digest);
                $digest = mb_strtoupper($digest);
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
                global $ilpage, $phrase;
                $extra = '';
                if ($invoiceid > 0)
                {
                        $extra = '<input type="hidden" name="transaction_id" value="' . $invoiceid . '" />';
                }
                $html = '<form method="post" action="https://www.moneybookers.com/app/payment.pl" accept-charset="UTF-8" style="margin:0px">
<input type="hidden" name="pay_to_email" value="' . $merchantid . '" />
<input type="hidden" name="pay_from_email" value="' . $payer_email . '" />
<input type="hidden" name="detail1_description" value="' . $description . '" />
<input type="hidden" name="detail1_text" value="' . $description . '" />
<input type="hidden" name="amount" value="' . $amount . '">
<input type="hidden" name="currency" value="' . $currency . '">
<input type="hidden" name="language" value="EN">
<input type="hidden" name="merchant_fields" value="' . $customencrypted . '" />
' . $extra . '
<input type="hidden" name="status_url" value="' . HTTPS_SERVER . $ilpage['payment'] . '?do=_moneybookers" />
<input type="hidden" name="return_url" value="' . HTTPS_SERVER . $ilpage['accounting'] . '" /> 
<input type="hidden" name="cancel_url" value="' . HTTPS_SERVER . $ilpage['accounting'] . '" />';
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
                $html = '<form method="post" action="https://www.moneybookers.com/app/payment.pl" accept-charset="UTF-8" style="margin:0px">
<input type="hidden" name="pay_to_email" value="' . $merchantid . '" />
<input type="hidden" name="detail1_description" value="' . $description . '" />
<input type="hidden" name="detail1_text" value="' . $description . '" />
<input type="hidden" name="amount" value="' . $amount . '">
<input type="hidden" name="currency" value="' . $currency . '">
<input type="hidden" name="language" value="EN">
<input type="hidden" name="merchant_fields" value="' . $customencrypted . '" />
<input type="hidden" name="status_url" value="' . HTTPS_SERVER . $ilpage['payment'] . '?do=_moneybookers" />
<input type="hidden" name="return_url" value="' . $returnurl . '" /> 
<input type="hidden" name="cancel_url" value="' . $returnurl . '" />';
                return $html;   
        }
        
        /**
        * Function for printing the payment processor custom generated form via POST method.
        *
        * @param       string        payer email address
        * @param       string        amount to process
        * @param       string        units
        * @param       integer       length
        * @param       string        transaction description
        * @param       string        master currency
        * @param       string        custom generated payment repsonse arguments to be decrypted by ilance payment processor
        * @param       string        onsubmit form submit code
        *
        * @return      string        HTML representation of the form (without the ending </form>)
        */
        function print_recurring_payment_form($merchantid = '', $amount = 0, $units = '', $length = 0, $description = '', $currency = '', $customencrypted = '', $onsubmit = '')
        {
                global $ilance, $ilpage, $phrase;
                $html = '<form method="post" action="https://www.moneybookers.com/app/payment.pl" onsubmit="' . $onsubmit . '" accept-charset="UTF-8" style="margin:0px">
<input type="hidden" name="pay_to_email" value="' . $merchantid . '" />
<input type="hidden" name="detail1_description" value="' . $description . '" />
<input type="hidden" name="detail1_text" value="' . $description . ' (' . $length . ' ' . $units . ')" />
<input type="hidden" name="rec_amount" value="' . $amount . '" />
<input type="hidden" name="rec_period" value="' . $ilance->subscription->subscription_length($units, $length) . '" />
<input type="hidden" name="rec_cycle" value="day" />
<input type="hidden" name="rec_status_url" value="' . HTTPS_SERVER . $ilpage['payment'] . '?do=_moneybookers" />
<input type="hidden" name="currency" value="' . mb_strtoupper($currency) . '">
<input type="hidden" name="language" value="EN" />
<input type="hidden" name="merchant_fields" value="' . $customencrypted . '" />
<input type="hidden" name="status_url" value="' . HTTPS_SERVER . $ilpage['payment'] . '?do=_moneybookers" />
<input type="hidden" name="return_url" value="' . HTTPS_SERVER . $ilpage['accounting'] . '" /> 
<input type="hidden" name="cancel_url" value="' . HTTPS_SERVER . $ilpage['accounting'] . '" />';
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
                if ($this->get_payment_status() == 'SUCCESS')
                {
                        $calc_hash = $this->generate_md5_digest();
                        $recv_hash = rawurldecode($this->get_transaction_md5_digest());
                        if ($calc_hash === $recv_hash)
                        {
                                return true;
                        }
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
                $html = 'FAILED';
                if ($this->moneybookers_post_vars['status'] == '2')
                {
                        $html = 'SUCCESS';
                }
                else if ($this->moneybookers_post_vars['status'] == '-3')
                {
                        $html = 'CHARGEBACK';
                }
                return $html;
        }
        
        /**
        * Function for storing the processed payment status for later retrevial.
        *
        * @return      string         payment status
        */
        function get_raw_payment_status()
        {                
                return $this->moneybookers_post_vars['status'];
        }
        
        /**
        * Function for storing the processed payment transaction id for later retrevial.
        *
        * @return      string         transaction id
        */
        function get_transaction_id()
        {
                return $this->moneybookers_post_vars['mb_transaction_id'];    
        }
        
        /**
        * Function for storing the processed payment transaction amount for later retrevial.
        *
        * @return      string         transaction id
        */
        function get_transaction_amount()
        {
                return $this->moneybookers_post_vars['mb_amount'];    
        }
        
        /**
        * Function for storing the processed payment currency for later retrevial.
        *
        * @return      string         transaction id
        */
        function get_transaction_currency()
        {
                return $this->moneybookers_post_vars['mb_currency'];    
        }
        
        /**
        * Function for storing the processed payment transaction type for later retrevial.
        *
        * @return      string         transaction type
        */
        function get_transaction_type()
        {
                // WLT - wallet payments
                // PBT - payments via bank transfer
                // MBD - moneybookers direct payment
                return $this->moneybookers_post_vars['payment_type'];
        }
        
        /**
        * Function for storing the processed recurring payment transaction type for later retrevial.
        *
        * @return      string         transaction type
        */
        function get_recurring_transaction_type()
        {
                // recurring
                // ondemand
                return $this->moneybookers_post_vars['rec_payment_type'];
        }
        
        /**
        * Function for fetching the md5sig sent to us in a response from the payment gateway
        *
        * @return      string         transaction type
        */
        function get_transaction_md5_digest()
        {
                return $this->moneybookers_post_vars['md5sig'];
        }
        
        /**
        * Function for fetching merchant id once it's provided to us from the payment gateway
        *
        * @return      string         transaction type
        */
        function get_transaction_merchant_id()
        {
                return $this->moneybookers_post_vars['merchant_id'];
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
                $message .= "\n\n" . SITE_NAME . " received the following IPN response from MoneyBookers.  Please use the following information for debug purposes only:\n\n*****************************\n";
                @reset($this->moneybookers_post_vars);
                while (@list($key, $value) = @each($this->moneybookers_post_vars))
                {
                        $message .= $key . ":" . " \t$value\n";
                }
                $message = "$date\n\n" . $message . "\n*****************************\n\n";
                if ($this->error_email)
                {
			global $ilance;
			$ilance->email->mail = $this->error_email;
			$ilance->email->from = SITE_EMAIL;
			$ilance->email->subject = 'MoneyBookers IPN Gateway Error';
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