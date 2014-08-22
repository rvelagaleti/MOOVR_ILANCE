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
* BluePay class to perform the majority of functions for handling payment gateway logic
*
* @package      iLance\PaymentGateway\BluePay
* @version      4.0.0.8059
* @author       ILance
*/
class bluepay
{
        /* merchant supplied parameters */
        var $accountId = ''; // ACCOUNT_ID
        var $userId = ''; // USER_ID (optional)
        var $tps = ''; // TAMPER_PROOF_SEAL
        var $transType = ''; // TRANS_TYPE (AUTH, SALE, REFUND, or CAPTURE)
        var $payType = ''; // PAYMENT_TYPE (CREDIT or ACH)
        var $mode = ''; // MODE (TEST or LIVE)
        var $masterId = ''; // MASTER_ID (optional)
        var $secretKey = ''; // used to generate the TPS
        /* customer supplied fields, (not required if
        MASTER_ID is set) */
        var $account = ''; // PAYMENT_ACCOUNT (i.e. credit card number)
        var $cvv2 = ''; // CARD_CVVS
        var $expire = ''; // CARD_EXPIRE 
        var $ssn = ''; // SSN (Only required for ACH)
        var $birthdate = ''; // BIRTHDATE (only required for ACH)
        var $custId = ''; // CUST_ID (only required for ACH)
        var $custIdState = ''; // CUST_ID_STATE (only required for ACH)
        var $amount = ''; // AMOUNT
        var $name1 = ''; // NAME1
        var $name2 = ''; // NAME2
        var $addr1 = ''; // ADDR1
        var $addr2 = ''; // ADDR2 (optional)
        var $city = ''; // CITY
        var $state = ''; // STATE
        var $zip = ''; // ZIP
        var $country = ''; // COUNTRY
        var $memo = ''; // MEMO (optinal)
        /* feilds for level 2 qualification */
        var $orderId = ''; // ORDER_ID
        var $invoiceId = ''; // INVOICE_ID
        var $tip = ''; // AMOUNT_TIP
        var $tax = ''; // AMOUNT_TAX
        /* rebilling (only with trans type of SALE or AUTH) */
        var $doRebill = ''; // DO_REBILL
        var $rebDate = ''; // REB_FIRST_DATE
        var $rebExpr = ''; // REB_EXPR
        var $rebCycles = ''; // REB_CYCLES
        var $rebAmount = ''; // REB_AMOUNT
        /* additional fraud scrubbing for an AUTH */
        var $doAutocap = ''; // DO_AUTOCAP
        var $avsAllowed = ''; // AVS_ALLOWED
        var $cvv2Allowed = ''; // CVV2_ALLOWED
        /* bluepay response output */
        var $response = '';
        /* parsed response values */
        var $transId = '';
        var $status = '';
        var $avsResp = '';
        var $cvv2Resp = '';
        var $authCode = '';
        var $message = '';
        var $rebid = '';
        var $error_email = '';
        var $approvedUrl = '';
        var $declinedUrl = '';
        var $missingUrl = '';
    
        /***
        * __construct()
        *
        * Constructor method, sets the account, secret key, 
        * and the mode properties. These will default to 
        * the constant values if not specified.
        */
        function bluepay($bluepay_post_vars = array())
        {
                global $ilconfig, $ilpage;
                        
                if (!empty($bluepay_post_vars))
                {
                        $this->bluepay_post_vars = $bluepay_post_vars;
                }
                else
                {
                        $this->bluepay_post_vars = array();
                }
                $this->accountId = trim($ilconfig['bluepay_accountid']);
                $this->secretKey = trim($ilconfig['bluepay_secretkey']);
                $this->mode = ($ilconfig['bluepay_test'] == 1) ? 'TEST' : 'LIVE';
                $this->transType = 'AUTH';
                $this->masterId = '';
                $this->payType = 'CREDIT';
                $this->approvedUrl = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['payment'] . '?do=_bluepay&approved';
                $this->declinedUrl = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['payment'] . '?do=_bluepay&declined';
                $this->missingUrl = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['payment'] . '?do=_bluepay&missing';
        }
        
        function make_hash()
        {
                return md5($this->secretKey . $this->accountId . $this->transType . $this->amount . $this->doRebill . $this->rebDate . $this->rebExpr . $this->rebCycles . $this->rebAmount . $this->avsAllowed . $this->doAutocap . $this->mode);
        }
        
        /**
        * Helper function for do_http_build_query
        */
        function toString($string)
        {
                if (preg_match('/ /', $string))
                {
                        $elements = explode(' ', $string);
                        $string = '';
                        $f = true;
                        foreach ($elements AS $elem)
                        {
                                if ($f)
                                {
                                        $string .= $elem;
                                        $f = false;
                                }
                                else
                                {
                                        $string .= '+' . $elem;
                                }
                        }
                }
                return $string;
        }
        
        /**
        * Performs the exact same operations as the PHP5 'http_build_query' function
        */
        function do_http_build_query(&$data)
        {
                $keys = array_keys($data);
                $string = '';
                $f = true;
                foreach ($keys AS $key)
                {
                        if ($f)
                        {
                                $string .= $key . '=' . $this->toString($data[$key]);
                                $f = false;
                        }
                        else
                        {
                                $string .= '&' . $key . '=' . $this->toString($data[$key]);
                        }
                }
                return $string;
        }
    
        
        /***
        * rebAdd()
        *
        * Will add a rebilling cycle.
        */
        function rebAdd()
        {
                $this->bluepay_post_vars['unit'] = mb_strtoupper($this->bluepay_post_vars['unit']);
                $this->doRebill = '1';
                $this->rebAmount = $this->formatAmount($this->bluepay_post_vars['amount']);
                $this->rebDate = print_date(DATETIME24H, 'Y-m-d');
                $this->rebExpr = $this->bluepay_post_vars['length'] . ' ' . $this->bluepay_post_vars['unit'];
                $this->rebCycles = '';
        }
        
        /***
        * setCustInfo()
        *
        * Sets the customer specified info.
        */
        function setCustInfo()
        {
                $this->account = $this->bluepay_post_vars['cardNumber'];
                $this->amount = $this->formatAmount($this->bluepay_post_vars['amount']);
                $this->payType = 'CREDIT';
                $this->cvv2 = $this->bluepay_post_vars['cvv2'];
                $this->expire = $this->bluepay_post_vars['creditcard_month'] . mb_strcut($this->bluepay_post_vars['creditcard_year'], 2, 4);
                $this->name = $this->bluepay_post_vars['firstName'] . ' ' . $this->bluepay_post_vars['lastName'];
                $this->name1 = $this->bluepay_post_vars['firstName'];
                $this->name2 = $this->bluepay_post_vars['lastName'];
                $this->addr1 = $_SESSION['ilancedata']['user']['address'];
                $this->addr2 = $_SESSION['ilancedata']['user']['address2'];
                $this->city = $_SESSION['ilancedata']['user']['city'];
                $this->state = $_SESSION['ilancedata']['user']['state'];
                $this->zip = $_SESSION['ilancedata']['user']['postalzip'];
                $this->country = $_SESSION['ilancedata']['user']['country'];
                $this->phone = '';
                $this->email = $_SESSION['ilancedata']['user']['email'];
                $this->customid1 = '';
                $this->customid2 = '';
                $this->memo = '';
                $this->orderId = $this->invoiceId = time();
        }
        
        /***
        * formatAmount()
        *
        * Will format an amount value to be in the
        * expected format for the POST.
        */
        function formatAmount($amount)
        {
               return sprintf("%01.2f", (float)$amount);
        }
        
        /***
        * process()
        *
        * Will first generate the tamper proof seal, then populate the POST query, then send it, and store the response, and finally parse the response.
        */
        function process()
        {
                /* calculate the tamper proof seal */
                $tps = $this->make_hash();
                $fields = array (
                        'MERCHANT' => $this->accountId,
                        'ACCOUNT_ID' => $this->accountId,
                        //'USER_ID' => $this->userId,
                        'TAMPER_PROOF_SEAL' => $tps,
                        'TRANSACTION_TYPE' => $this->transType,
                        'PAYMENT_TYPE' => $this->payType,
                        'MODE' => $this->mode,
                        'MASTER_ID' => $this->masterId,
                        'CC_NUM' => $this->account,
                        'CARD_CVV2' => $this->cvv2,
                        'CC_EXPIRES' => $this->expire,
                        'CARD_EXPIRE' => $this->expire,
                        'SSN' => $this->ssn,
                        'BIRTHDATE' => $this->birthdate,
                        'CUST_ID' => $this->custId,
                        'CUST_ID_STATE' => $this->custIdState,
                        'AMOUNT' => $this->amount,
                        'NAME' => $this->name,
                        'NAME1' => $this->name1,
                        'NAME2' => $this->name2,
                        'ADDR1' => $this->addr1,
                        'ADDR2' => $this->addr2,
                        'CITY' => $this->city,
                        'STATE' => $this->state,
                        'ZIP' => $this->zip,
                        'PHONE' => $this->phone,
                        'EMAIL' => $this->email,
                        'COUNTRY' => $this->country,
                        'MEMO' => $this->memo,
                        'CUSTOM_ID' => $this->customid1,
                        'CUSTOM_ID2' => $this->customid2,
                        'ORDER_ID' => $this->orderId,
                        'INVOICE_ID' => $this->invoiceId,
                        'AMOUNT_TIP' => $this->tip,
                        'AMOUNT_TAX' => $this->tax,
                        'REBILLING' => $this->doRebill,
                        'DO_REBILL' => $this->doRebill,
                        'REB_FIRST_DATE' => $this->rebDate,
                        'REB_EXPR' => $this->rebExpr,
                        'REB_CYCLES' => $this->rebCycles,
                        'REB_AMOUNT' => $this->rebAmount,
                        'DO_AUTOCAP' => $this->doAutocap,
                        'AVS_ALLOWED' => $this->avsAllowed,
                        'CVV2_ALLOWED' => $this->cvv2Allowed,
                        'APPROVED_URL' => $this->approvedUrl,
                        'DECLINED_URL' => $this->declinedUrl,
                        'MISSING_URL' => $this->missingUrl
                );
                /* perform the transaction */
                // Set the URL//https://secure.bluepay.com/interfaces/bp20post
                //https://secure.bluepay.com/interfaces/bp10emu
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://secure.bluepay.com/interfaces/bp10emu'); 
                curl_setopt($ch, CURLOPT_USERAGENT, "BluepayPHP SDK/2.0"); // Cosmetic
                curl_setopt($ch, CURLOPT_POST, 1); // Perform a POST
                // curl_setopt($ch, CURLOPT_CAINFO, "c:\\windows\\ca-bundle.crt"); // Name of the file to verify the server's cert against
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // Turns off verification of the SSL certificate.
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // If not set, curl prints output to the browser
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->do_http_build_query($fields));
                $this->response = curl_exec($ch);
                curl_close($ch); 
                return $this->parseResponse();
        }
        
        /***
        * parseResponse()
        *
        * This method will parse the response parameter values
        * into the respective properties.
        */
        function parseResponse()
        {
                parse_str($this->response, $array);
                foreach($array AS $key => $value)
                {
                        $key = str_replace('amp;', '', $key);
                        $array2[$key] = $value;
                }
                return $array2;
        }
        
        /**
        * Function for printing the recurring payment processor custom generated form via POST method.
        *
        * @param       string        subscription start date (default now/today) format: YYYY-MM-DD
        * @param       integer       subscription id
        * @param       string        amount to process
        * @param       integer       total occurrences (max 9999) default 9999 (no end date until user cancels themselve)
        * @param       string        trial amount to process (default 0)
        * @param       integer       trial occurrences (max 99) default 0
        * @param       string        unit (format: months or days)
        * @param       integer       length (format: can be 1 - 12 or 7 - 365)
        * @param       string        transaction description
        * @param       string        js onsubmit form code
        * @param       integer       ismodify subscription modify update for authorize.net (default 0 = no)
        * @param       integer       iscancel subscription cancellation request (default 0 = no)
        * @param       string        custom subscription info
        *
        * @return      string        HTML representation of the form (without the ending </form>)
        */
        function print_recurring_payment_form($startdate = '', $subscriptionid = 0, $roleid = 0, $amount = 0, $totaloccurrences = 9999, $trialamount = 0, $trialoccurrences = 0, $units = '', $length = 0, $description = '', $onsubmit = '', $ismodify = 0, $iscancel = 0)
        {
                global $ilance, $ilpage, $ilconfig, $show;
                $html = '<form name="ilform" action="' . HTTPS_SERVER . $ilpage['payment'] . '" method="post" accept-charset="UTF-8" onsubmit="' . $onsubmit . '" style="margin:0px">
<input type="hidden" name="do" value="_bluepay" />
<input type="hidden" name="subscriptionid" value="' . $subscriptionid . '" />
<input type="hidden" name="roleid" value="' . $roleid . '" />
<input type="hidden" name="name" value="' . $description . '" />
<input type="hidden" name="length" value="' . $this->format_length($length, $units) . '" />
<input type="hidden" name="unit" value="' . $this->format_unit($length, $units) . '" />
<input type="hidden" name="units" value="' . $units . '" />
<input type="hidden" name="startDate" value="' . $startdate . '" />
<input type="hidden" name="totalOccurrences" value="' . $totaloccurrences . '" />
<input type="hidden" name="trialOccurrences" value="' . $trialoccurrences . '" />
<input type="hidden" name="amount" value="' . $amount . '" />
<input type="hidden" name="trialAmount" value="' . $trialamount . '" />';
                if ($ismodify)
                {
                        $html .= '<input type="hidden" name="mode" value="update" /><input type="hidden" name="subscriptionId" value="' . $subscriptionid . '" />';
                }
                else if ($iscancel)
                {
                        $html .= '<input type="hidden" name="mode" value="cancel" /><input type="hidden" name="subscriptionId" value="' . $subscriptionid . '" />';
                }
                else
                {
                        $html .= '<input type="hidden" name="mode" value="create" />';
                }
                return $html;
        }
        
        function format_length($length = 0, $unit = '')
        {
                if ($unit == 'Y')
                {
                       $length = ($length * 12);
                }
                return $length;
        }
        
        function format_unit($length = 0, $unit = '')
        {
                if ($unit == 'Y')
                {
                       $unit = 'months';
                }
                else if ($unit == 'M')
                {
                       $unit = 'months'; 
                }
                else if ($unit == 'D')
                {
                        $unit = 'days';
                }
                return $unit;
        } 
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>