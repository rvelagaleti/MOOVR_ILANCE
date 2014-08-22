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

if (!class_exists('accounting'))
{
	exit;
}

/**
* Class to handle credit card logic related functions within ILance
*
* @package      iLance\Accounting\CreditCard
* @version      4.0.0.8059
* @author       ILance
*/
class accounting_creditcard extends accounting
{
	/**
        * Functions for processing a deposit payment from a credit card payment
        *
        * @param       integer          credit card id
        * @param       integer          user id
        * @param       string           deposit amount to process
        * @param       string           deposit account credit amount
        * @param       array            custom array argument (used for sending credit card details of user's supplied card info)
        *
        * @return      mixed            Returns 1 for true, 2 for card not authenticated via account menu or false for gateway transaction error response
        */
        function process_creditcard_deposit($ccid = 0, $userid = 0, $deposit = 0, $credit = 0, $custom = array())
        {
                global $ilance, $ilconfig, $ilpage, $phrase, $show;
                
                if ($userid > 0 AND $ccid > 0)
                {
                        // use customer's credit card in the databasefile
                        
                        $sql = $ilance->db->query("
                                SELECT user_id, creditcard_type, creditcard_number, name_on_card, card_billing_address1, card_billing_address2, card_city, card_state, card_postalzip, card_country, phone_of_cardowner, email_of_cardowner, cc_id, creditcard_expiry, cvv2
                                FROM " . DB_PREFIX . "creditcards
                                WHERE user_id = '" . intval($userid) . "'
                                    AND cc_id = '" . intval($ccid) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $resultcc = $ilance->db->fetch_array($sql);
                                
                                if ($result['authorized'] == 'no')
                                {
                                        return '2';
                                }
                                
                                $resultcc['card_country'] = stripslashes($ilance->db->fetch_field(DB_PREFIX . "locations", "locationid = " . $ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '" . intval($ccid) . "'", "card_country"), "location_" . fetch_user_slng(intval($userid))));
                                $resultcc['creditcard_number'] = $ilance->crypt->three_layer_decrypt($resultcc['creditcard_number'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']);
                        }
                }
                else if ($userid > 0 AND $ccid == 'ccform' AND is_array($custom))
                {
                        // use custom supplied card details from the form just submitted
                        
                        $resultcc['card_country'] = $_SESSION['ilancedata']['user']['country'];
                        $resultcc['creditcard_number'] = $custom['creditcard_number'];
                        $resultcc['name_on_card'] = $custom['creditcard_name'];
                        $resultcc['card_billing_address1'] = $custom['creditcard_billing'];
                        $resultcc['card_billing_address2'] = '';
                        $resultcc['card_postalzip'] = $custom['creditcard_postal'];
                        $resultcc['creditcard_type'] = $custom['creditcard_type'];
                        $resultcc['cvv2'] = $custom['creditcard_cvv2'];
                        $resultcc['creditcard_expiry'] = $custom['creditcard_month'] . $custom['creditcard_year'];
                        $resultcc['phone_of_cardowner'] = fetch_user('phone', intval($userid));
                        $resultcc['card_city'] = fetch_user('city', intval($userid));
                        $resultcc['card_state'] = fetch_user('state', intval($userid));
                }
                
                $ccnum_hidden = substr_replace($resultcc['creditcard_number'], '', 0, (mb_strlen($resultcc['creditcard_number']) - 4));
                $nameoncard = ucwords(stripslashes($resultcc['name_on_card']));
                
                $namesplit = explode(' ', $nameoncard);
                if (empty($namesplit[0]))
                {
                        $namesplit[0] = '';
                }
                if (empty($namesplit[1]))
                {
                        $namesplit[1] = '';
                }
                
                $transa = rand(100,999) . rand(100,999) . rand(100,999);
                
                $ilance->paymentgateway = construct_object('api.paymentgateway', $ilconfig['use_internal_gateway']);
                
				switch($ilconfig['use_internal_gateway']) {
                	case 'bluepay':                	
                	$ilance->paymentgateway->set_user(
                        $ilconfig['bluepay_accountid'],
                        $ilconfig['bluepay_secretkey'],
                        $ilconfig['bluepay_secretkey'],
                        SITE_EMAIL
                	);
                	break;
                	case 'paypal_pro':                	
                	$ilance->paymentgateway->set_user(
                        $ilconfig['paypal_pro_username'],
                        $ilconfig['paypal_pro_password'],
                        $ilconfig['paypal_pro_signature'],
                        SITE_EMAIL
                	);
                	break;
                	default:
					$ilance->paymentgateway->set_user(
                        $ilconfig['cc_login'],
                        $ilconfig['cc_password'],
                        $ilconfig['cc_key'],
                        SITE_EMAIL
                	);
                	break;
        		}
                
                $ilance->paymentgateway->set_customer(
                        fetch_user('username', intval($userid)),
                        $resultcc['phone_of_cardowner'],
                        $namesplit[0],
                        $namesplit[1],
                        stripslashes($resultcc['card_billing_address1']) . ' ' . stripslashes($resultcc['card_billing_address2']),
                        ucfirst($resultcc['card_city']),
                        ucfirst($resultcc['card_state']),
                        mb_strtoupper($resultcc['card_postalzip']),
                        $resultcc['card_country']
                );
                
                $ilance->paymentgateway->set_ccard(
                        $resultcc['name_on_card'],
                        $resultcc['creditcard_type'],
                        $resultcc['creditcard_number'],
                        mb_substr($resultcc['creditcard_expiry'], 0, 2),
                        mb_substr($resultcc['creditcard_expiry'], -4),
                        $resultcc['cvv2']
                );
                
                $ilance->paymentgateway->set_currency(
                        $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['code'],
                        $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_left']
                );
                
                $ilance->paymentgateway->set_order(
                        $deposit,
                        $transa,
                        '{_account_deposit_credit_via}' . ' ' . ucfirst($resultcc['creditcard_type']) . ' : #' . $ccnum_hidden . ' ' . '{_into_online_account}',
                        $ilconfig['authentication_capture'],
                        null,
                        null,
                        null
                );
                
                $gatewayinfo = array();
				if($ilconfig['use_internal_gateway'] == 'bluepay' AND $ilconfig['bluepay_test'] == TRUE)
                {
                	$gatewayinfo = array('x_test_request' => 'TRUE');
                }
                $ilance->paymentgateway->set_extra($gatewayinfo);
                
                // #### PROCESS CC FOR DEPOSIT TRANSACTION #####################
                
                if ($ilance->paymentgateway->process())
                {
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "users
                                SET available_balance = available_balance + $credit,
                                total_balance = total_balance + $credit
                                WHERE user_id = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
        
                        // insert deposit transaction for customer
                        
                        
                        $deposit_invoice_id = $ilance->accounting->insert_transaction(
                                0,
                                0,
                                0,
                                $userid,
                                0,
                                0,
                                0,
                                '{_account_deposit_credit_via}' . ' ' . ucfirst($resultcc['creditcard_type']) . ' : #' . $ccnum_hidden . ' ' . '{_into_online_account}' . ': ' . $ilance->currency->format($credit),
                                $deposit,
                                $deposit,
                                'paid',
                                'credit',
                                $resultcc['creditcard_type'],
                                DATETIME24H,
                                DATETIME24H,
                                DATETIME24H,
                                $transa,
                                0,
                                0,
                                1,
                                '',
                                1,
                                0
                        );
                        
                        // update the transaction with the acual amount we're crediting this user for
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "invoices
                                SET depositcreditamount = '" . sprintf("%01.2f", $credit) . "',
                                paymentgateway = '" . $ilance->db->escape_string($ilconfig['use_internal_gateway']) . "'
                                WHERE user_id = '" . intval($userid) . "'
                                        AND invoiceid = '" . intval($deposit_invoice_id) . "'
                        ");
                        
                        
        
                        $existing = array(
                                '{{username}}' => fetch_user('username', intval($userid)),
                                '{{ip}}' => IPADDRESS,
                                '{{amount}}' => $ilance->currency->format($credit),
                                '{{cost}}' => $ilance->currency->format($deposit),
                                '{{invoiceid}}' => $deposit_invoice_id,
                                '{{paymethod}}' => mb_strtoupper($resultcc['creditcard_type']) . ' : #' . $ccnum_hidden,
                                '{{gateway}}' => '{_' . $ilconfig['use_internal_gateway'] . '}',
                        );
        
                        $ilance->email->mail = fetch_user('email', intval($userid));
                        $ilance->email->slng = fetch_user_slng(intval($userid));
                        
                        $ilance->email->get('member_deposit_funds_creditcard');		
                        $ilance->email->set($existing);
                        
                        $ilance->email->send();
                        
                        $ilance->email->mail = SITE_EMAIL;
                        $ilance->email->slng = fetch_site_slng();
                        
                        $ilance->email->get('member_deposit_funds_creditcard_admin');		
                        $ilance->email->set($existing);
                        
                        $ilance->email->send();
                        
                        return '1';
                }
                
                return false;
        }
	
	/**
        * Function for processing a credit card authentication step 1 through a major creditcard gateway.
        *
        * This function will connect to the pre-configured payment gateway, send the card details, charge the credit card 2 amounts both under 2.00 each.  The user
        * would have to wait for the billing statement to complete this process (or they can view online debits using their merchant web site to learn amounts) which
        * is then handled in the creditcard_authentication_step_two() function.
        *
        * @param       integer      user id
        * @param       integer      credit card id
        * @param       string       customer first name
        * @param       string       customer last name
        * @param       string       customer address
        * @param       string       customer city
        * @param       string       customer state
        * @param       string       customer zip / postal code
        * @param       integer      customer country code
        *
        * @return      bool         returns true or false
        */
        function creditcard_authentication_step_one($userid = 0, $v3customer_ccid = 0, $v3customer_fname = '', $v3customer_lname = '', $v3customer_address = '', $v3customer_city = '', $v3customer_state = '', $v3customer_zip = '', $v3customer_country = '')
        {
                global $ilance, $phrase, $page_title, $area_title, $ilconfig, $ilpage, $SCRIPT_URL;
                
                $sql_cc_url = $ilance->db->query("
                        SELECT cc_id, date_added, date_updated, user_id, creditcard_number, creditcard_expiry, cvv2, name_on_card, phone_of_cardowner, email_of_cardowner, card_billing_address1, card_billing_address2, card_city, card_state, card_postalzip, card_country, creditcard_status, default_card, creditcard_type, authorized, auth_amount1, auth_amount2, attempt_num, trans1_id, trans2_id
                        FROM " . DB_PREFIX . "creditcards
                        WHERE user_id = '" . intval($userid) . "'
                            AND cc_id = '" . intval($v3customer_ccid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql_cc_url) > 0)
                {
                        $sql_cc_arr = $ilance->db->fetch_array($sql_cc_url, DB_ASSOC);
                }
                else
                {
                        return false;
                }
        
                // generate two random amounts both under 2.00
                $amount1 = (rand(1, 199)/100);
                $amount2 = (rand(1, 199)/100);
        
                // some payment gateways require a transaction id / order id
                $transa1 = (rand(100, 999) . rand(100, 999) . rand(100, 999));
                $transa2 = (rand(100, 999) . rand(100, 999) . rand(100, 999));
        
                // decrypt credit card
                $decrypted_card_no = $ilance->crypt->three_layer_decrypt($sql_cc_arr['creditcard_number'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']);
                $decrypted_card_no = str_replace(' ', '', $decrypted_card_no);
        
                // hide credit card using unique string
                $decrypted_card_no_hidden = str_replace(' ', '', $decrypted_card_no);
                $ccnum_hidden = substr_replace($decrypted_card_no_hidden, 'XX XXXX XXXX ', 2, (mb_strlen($decrypted_card_no_hidden) - 6));
        
                // authentication amount 1
                $ilance->paymentgateway = construct_object('api.paymentgateway', $ilconfig['use_internal_gateway']);
                
                $ilance->paymentgateway->set_user(
                        $ilconfig['cc_login'],
                        $ilconfig['cc_password'],
                        $ilconfig['cc_key'],
                        SITE_EMAIL
                );
                
                $ilance->paymentgateway->set_customer(
                        fetch_user('username', $ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '".$v3customer_ccid."'", "user_id")),
                        $sql_cc_arr['phone_of_cardowner'],
                        $v3customer_fname,
                        $v3customer_lname,
                        $v3customer_address,
                        $v3customer_city,
                        $v3customer_state,
                        $v3customer_zip,
                        $v3customer_country
                );
                
                $ilance->paymentgateway->set_ccard(
                        $sql_cc_arr['name_on_card'],
                        $sql_cc_arr['creditcard_type'],
                        $decrypted_card_no,
                        mb_substr($sql_cc_arr['creditcard_expiry'], 0, 2),
                        mb_substr($sql_cc_arr['creditcard_expiry'], -4),
                        $sql_cc_arr['cvv2']
                );
                
                $ilance->paymentgateway->set_currency(
                        $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['code'],
                        $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_left']
                );
                
                $ilance->paymentgateway->set_order(
                        $amount1,
                        $transa1,
                        '{_credit_card_authentication_amount_one}',
                        $ilconfig['authentication_capture'],
                        null,
                        null,
                        null
                );
                
                $gatewayinfo = array();
                $ilance->paymentgateway->set_extra($gatewayinfo);
                
                if (!$ilance->paymentgateway->process())
                {
                        $v3process_success = 0;
                        $transaction_messageT = $ilance->paymentgateway->get_response_message();
                        $date_timeT = DATETIME24H;
            
                        global $transaction_message, $date_time;
                        $transaction_message = $transaction_messageT;
                        $date_time = $date_timeT;
            
                        $area_title = $area_title;
                        $page_title = $page_title;
                        $date_time = DATETIME24H;
                        if (empty($transaction_message))
                        {
                                $transaction_message = 'Could not communicate with merchant server / no response.';
                        }
                        
                        
                
                        $ilance->email->mail = SITE_EMAIL;
                        $ilance->email->slng = fetch_site_slng();
                        
                        $ilance->email->get('creditcard_processing_error');		
                        $ilance->email->set(array(
                                '{{gatewayresponse}}' => $ilance->paymentgateway->get_answer(),
                                '{{gatewaymessage}}' => $ilance->paymentgateway->get_response_message(),
						  '{{gatewayerrorcode}}' => $ilance->paymentgateway->error['number'],
                                '{{ipaddress}}' =>IPADDRESS,
                                '{{location}}' => LOCATION,
                                '{{scripturi}}' => SCRIPT_URI,
                                '{{gateway}}' => '{_' . $ilconfig['use_internal_gateway'] . '}',
                                '{{member}}' => $_SESSION['ilancedata']['user']['username'],
                                '{{memberemail}}' => $_SESSION['ilancedata']['user']['email'],
                        ));
                        
                        $ilance->email->send();
                        
                        $ilance->template->fetch('main', 'print_notice_payment_gateway.html');
                        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                        $ilance->template->parse_if_blocks('main');
                        $ilance->template->pprint('main', array('date_time','transaction_message','transaction_code'));
                        exit();
                }
                else
                {
                        $v3process_success = 1;
                        
                        // gateway transaction id
                        $gatewaytransact1 = $ilance->paymentgateway->get_transact_code();
                }
        
                // authentication amount 2
                $ilance->paymentgateway2 = construct_object('api.paymentgateway', $ilconfig['use_internal_gateway']);
                
                $ilance->paymentgateway2->set_user(
                        $ilconfig['cc_login'],
                        $ilconfig['cc_password'],
                        $ilconfig['cc_key'],
                        SITE_EMAIL
                );
                
                $ilance->paymentgateway2->set_customer(
                        $_SESSION['ilancedata']['user']['username'],
                        $sql_cc_arr['phone_of_cardowner'],
                        $v3customer_fname,
                        $v3customer_lname,
                        $v3customer_address,
                        $v3customer_city,
                        $v3customer_state,
                        $v3customer_zip,
                        $v3customer_country
                );
                
                $ilance->paymentgateway2->set_ccard(
                        $sql_cc_arr['name_on_card'],
                        $sql_cc_arr['creditcard_type'],
                        $decrypted_card_no,
                        mb_substr($sql_cc_arr['creditcard_expiry'], 0, 2),
                        mb_substr($sql_cc_arr['creditcard_expiry'], -4),
                        $sql_cc_arr['cvv2']
                );
                
                $ilance->paymentgateway2->set_currency(
                        $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['code'],
                        $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_left']
                );
                
                $ilance->paymentgateway2->set_order(
                        $amount2,
                        $transa2,
                        '{_credit_card_authentication_amount_two}',
                        $ilconfig['authentication_capture'],
                        NULL,
                        NULL,
                        NULL
                );// the last is an extra field for some gateways (mode)
                
                $gatewayinfo = array();
                $ilance->paymentgateway2->set_extra($gatewayinfo);
                
                if (!$ilance->paymentgateway2->process())
                {
                        $v3process_success = 0;
                        $transaction_messageT = $ilance->paymentgateway2->get_response_message();
                        $date_timeT = DATETIME24H;
            
                        global $transaction_message, $date_time;
                        $transaction_message = $transaction_messageT;
                        $date_time = $date_timeT;
            
                        $area_title = $area_title;
                        $page_title = $page_title;
            
                        $date_time = DATETIME24H;
                        if (empty($transaction_message))
                        {
                                $transaction_message = 'Could not communicate with merchant server / no response.';
                        }
                        
                        
                
                        $ilance->email->mail = SITE_EMAIL;
                        $ilance->email->slng = fetch_site_slng();
                        
                        $ilance->email->get('creditcard_processing_error');		
                        $ilance->email->set(array(
                                '{{gatewayresponse}}' => $ilance->paymentgateway2->get_answer(),
                                '{{gatewaymessage}}' => $ilance->paymentgateway2->get_response_message(),
                                '{{ipaddress}}' =>IPADDRESS,
                                '{{location}}' => LOCATION,
                                '{{scripturi}}' => SCRIPT_URI,
                                '{{gateway}}' => $ilconfig['use_internal_gateway'],
                                '{{member}}' => $_SESSION['ilancedata']['user']['username'],
                                '{{memberemail}}' => $_SESSION['ilancedata']['user']['email'],
                        ));
                        
                        $ilance->email->send();
                        
                        $ilance->template->fetch('main', 'print_notice_payment_gateway.html');
                        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                        $ilance->template->parse_if_blocks('main');
                        $ilance->template->pprint('main', array('date_time','transaction_message','transaction_code'));
                        exit();
                }
                else
                {
                        // add authentication 1 amounts secretly into the members card table
                        // also -- set this card as default card to use
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "creditcards
                                SET auth_amount1 = '" . $ilance->db->escape_string($amount1) . "',
                                trans1_id = '" . $ilance->db->escape_string($gatewaytransact1) . "',
                                default_card = 'yes'
                                WHERE user_id = '" . intval($userid) . "'
                                    AND cc_id = '" . intval($v3customer_ccid) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);    
                }
        
                if ($v3process_success)
                {
                        $gatewaytransact2 = $ilance->paymentgateway2->get_transact_code();
                        
                        // set all current cards as no default card (reset)
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "creditcards
                                SET default_card = 'no'
                                WHERE user_id = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
            
                        // and set credit card as the default card to use and add these amounts in the users card table
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "creditcards
                                SET auth_amount2 = '" . $ilance->db->escape_string($amount2) . "',
                                trans2_id = '" . $ilance->db->escape_string($gatewaytransact2) . "',
                                default_card = 'yes'
                                WHERE user_id = '" . intval($userid) . "'
                                        AND cc_id = '" . intval($v3customer_ccid) . "'
                        ", 0, null, __FILE__, __LINE__);
            
                        
                
                        $ilance->email->mail = SITE_EMAIL;
                        $ilance->email->slng = fetch_site_slng();
                        
                        $ilance->email->get('creditcard_authentication_admin');		
                        $ilance->email->set(array(
                                '{{provider}}' => $_SESSION['ilancedata']['user']['username'],
                                '{{amount1}}' => $amount1,
                                '{{amount2}}' => $amount2,
                                '{{trans1}}' => $gatewaytransact1,
                                '{{trans2}}' => $gatewaytransact2,
                                '{{cc_id}}' => $v3customer_ccid,
                        ));
                        
                        $ilance->email->send();
            
                        return true;
                }
                else
                {
                        $v3process_success = 0;
                        $transaction_messageT = $ilance->paymentgateway2->get_response_message();
                        $date_timeT = DATETIME24H;
            
                        global $transaction_message, $date_time;
                        $transaction_message = $transaction_messageT;
                        $date_time = $date_timeT;
            
                        $area_title = $area_title;
                        $page_title = $page_title;
                        if (empty($transaction_message))
                        {
                                $transaction_message = 'Could not communicate with merchant server / no response.';
                        }
                        
                        
                
                        $ilance->email->mail = SITE_EMAIL;
                        $ilance->email->slng = fetch_site_slng();
                        
                        $ilance->email->get('creditcard_processing_error');		
                        $ilance->email->set(array(
                                '{{gatewayresponse}}' => $ilance->paymentgateway2->get_answer(),
                                '{{gatewaymessage}}' => $ilance->paymentgateway2->get_response_message(),
                                '{{ipaddress}}' =>IPADDRESS,
                                '{{location}}' => LOCATION,
                                '{{scripturi}}' => SCRIPT_URI,
                                '{{gateway}}' => $ilconfig['use_internal_gateway'],
                                '{{member}}' => $_SESSION['ilancedata']['user']['username'],
                                '{{memberemail}}' => $_SESSION['ilancedata']['user']['email'],
                        ));
                        
                        $ilance->email->send();
                        
                        $ilance->template->fetch('main', 'print_notice_payment_gateway.html');
                        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                        $ilance->template->parse_if_blocks('main');
                        $ilance->template->pprint('main', array('date_time','transaction_message','transaction_code'));
                        exit();
                }
        }
        
        /**
        * Function to update a customers credit card in the database as active based on the authentication amounts taken during the authentication process step 1.
        *
        * @param       integer      authentication amount sum
        * @param       integer      credit card id
        * @param       string       customer first name
        * @param       string       customer last name
        * @param       string       customer address
        * @param       string       customer city
        * @param       string       customer state
        * @param       string       customer zip / postal code
        * @param       integer      customer country code
        *
        * @return      bool         returns true or false
        */
        function creditcard_authentication_step_two($input_auth = '', $v3customer_ccid = '', $v3customer_fname = '', $v3customer_lname = '', $v3customer_address = '', $v3customer_city = '', $v3customer_state = '', $v3customer_zip = '', $v3customer_country = '')
        {
                global $ilance, $phrase, $page_title, $area_title, $ilconfig, $ilpage;
        
                $sql_cc_url = $ilance->db->query("
                        SELECT cc_id, date_added, date_updated, user_id, creditcard_number, creditcard_expiry, cvv2, name_on_card, phone_of_cardowner, email_of_cardowner, card_billing_address1, card_billing_address2, card_city, card_state, card_postalzip, card_country, creditcard_status, default_card, creditcard_type, authorized, auth_amount1, auth_amount2, attempt_num, trans1_id, trans2_id
                        FROM " . DB_PREFIX . "creditcards
                        WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                            AND cc_id = '" . intval($v3customer_ccid) . "'
                ", 0, null, __FILE__, __LINE__);
                $sql_cc_arr = $ilance->db->fetch_array($sql_cc_url);
        
                // decrypt credit card number
                $decrypted_card_no = $ilance->crypt->three_layer_decrypt($sql_cc_arr['creditcard_number'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']);
                $decrypted_card_no = str_replace(' ', '', $decrypted_card_no);
        
                // hide cc number
                $decrypted_card_no_hidden = str_replace(' ', '', $decrypted_card_no);
                $ccnum_hidden = substr_replace($decrypted_card_no_hidden, 'XX XXXX XXXX ', 2 , (mb_strlen($decrypted_card_no_hidden) - 6));
        
                $server_sum = ($sql_cc_arr['auth_amount1'] + $sql_cc_arr['auth_amount2']);
                if ($server_sum == $input_auth)
                {
                        // amounts match!
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "creditcards
                                SET authorized = 'yes',
                                creditcard_status = 'active'
                                WHERE cc_id = '" . intval($v3customer_ccid) . "'
                                        AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
            
                        // refund mode?
                        if ($ilconfig['authentication_refund_on_max_cc_attempts'])
                        {
                                $v3customer_fname = stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id=" . $v3customer_ccid,"name_on_card"));
                                $v3customer_lname = '';
                                $v3customer_address = stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id=" . $v3customer_ccid,"card_billing_address1")) . " " . stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id=" . $v3customer_ccid, "card_billing_address2"));
                                $v3customer_city = stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id=" . $v3customer_ccid,"card_city"));
                                $v3customer_state = stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id=" . $v3customer_ccid,"card_state"));
                                $v3customer_zip = stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id=" . $v3customer_ccid,"card_postalzip"));
                                $v3customer_country = stripslashes($ilance->db->fetch_field(DB_PREFIX . "locations", "locationid=" . $ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id=" . $v3customer_ccid, "card_country"), "location_" . $_SESSION['ilancedata']['user']['slng']));
                                $input_auth = $ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id=" . $v3customer_ccid, "auth_amount1") + $ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id=" . $v3customer_ccid, "auth_amount2");
                                
                                $refundsuccess = $this->creditcard_authentication_refund($server_sum, $v3customer_ccid, $v3customer_fname, $v3customer_lname, $v3customer_address, $v3customer_city, $v3customer_state, $v3customer_zip, $v3customer_country);
                                if ($refundsuccess)
                                {
                                        return true;
                                }
                                else
                                {
                                        $header_text = '{_credit_card_refund_to_card_failure}';
                                        $body_text = '{_were_sorry_there_seems_to_have_been_a_problem_during_the_recredit_operation_to_your_credit_card}';
                                        
                                        print_notice($header_text, $body_text, $ilpage['accounting'], '{_accounting}');
                                }
                        }
                        else
                        {
                                $account_balance_res = $ilance->db->query("
                                        SELECT total_balance, available_balance
                                        FROM " . DB_PREFIX . "users
                                        WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                ", 0, null, __FILE__, __LINE__);
                                $account_balance_array = $ilance->db->fetch_array($account_balance_res);
                
                                // deposit refund into online account
                                $new_tbalance = $account_balance_array['total_balance'] + $sql_cc_arr['auth_amount1'];
                                $new_abalance = $account_balance_array['available_balance'] + $sql_cc_arr['auth_amount1'];
                                $new_tbalance += $sql_cc_arr['auth_amount2'];
                                $new_abalance += $sql_cc_arr['auth_amount2'];
                
                                $ilance->db->query("
                                        INSERT INTO " . DB_PREFIX . "invoices
                                        (invoiceid, user_id, description, amount, paid, totalamount, status, invoicetype, paymethod, referer, ipaddress, createdate, paiddate, custommessage, transactionid, archive)
                                        VALUES(
                                        NULL,
                                        '" . $_SESSION['ilancedata']['user']['userid'] . "',
                                        '" . $ilance->db->escape_string('{_credit_card_authentication_refund_credit_into_online_account}') . "',
                                        '" . ($sql_cc_arr['auth_amount1']+$sql_cc_arr['auth_amount2']) . "',
                                        '" . ($sql_cc_arr['auth_amount1']+$sql_cc_arr['auth_amount2']) . "',
                                        '" . ($sql_cc_arr['auth_amount1']+$sql_cc_arr['auth_amount2']) . "',
                                        'paid',
                                        'credit',
                                        'account',
                                        '" . $ilance->db->escape_string(REFERRER) . "',
                                        '" . $ilance->db->escape_string(IPADDRESS) . "',
                                        '" . DATETIME24H . "',
                                        '" . DATETIME24H . "',
                                        '" . $ilance->db->escape_string('{_credit_card_authentication_refund_credit_into_online_account}') . "',
                                        '" . $ilance->accounting_payment->construct_transaction_id() . "',
                                        '0')
                                ", 0, null, __FILE__, __LINE__);
                
                                // update account balances
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "users
                                        SET total_balance = '" . $new_tbalance . "',
                                        available_balance = '" . $new_abalance . "'
                                        WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                ", 0, null, __FILE__, __LINE__);
                                
                                return true;
                        }
                }
                
                return false;
        }
	
	/**
        * Function for processing a credit card refund for the credit card authentication step process.
        *
        * @param       string       amount to refund
        * @param       integer      credit card id
        * @param       string       credit card first name
        * @param       string       credit card last name
        * @param       string       customer address
        * @param       string       customer city
        * @param       string       customer state
        * @param       string       customer zip code
        * @param       string       customer country
        * 
        * @return      string       Returns formatted template with success or failure message.
        */
        function creditcard_authentication_refund($input_auth = '', $v3customer_ccid = '', $v3customer_fname = '', $v3customer_lname = '', $v3customer_address = '', $v3customer_city = '', $v3customer_state = '', $v3customer_zip = '', $v3customer_country = '')
        {
                global $ilance, $phrase, $page_title, $area_title, $ilconfig, $ilpage;
                
                $sql = $ilance->db->query("
                        SELECT cc_id, date_added, date_updated, user_id, creditcard_number, creditcard_expiry, cvv2, name_on_card, phone_of_cardowner, email_of_cardowner, card_billing_address1, card_billing_address2, card_city, card_state, card_postalzip, card_country, creditcard_status, default_card, creditcard_type, authorized, auth_amount1, auth_amount2, attempt_num, trans1_id, trans2_id
                        FROM " . DB_PREFIX . "creditcards
                        WHERE cc_id = '" . intval($v3customer_ccid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql_cc_url) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                }
                else
                {
                        return false;
                }
        
                $decrypted_card_no = $ilance->crypt->three_layer_decrypt($res['creditcard_number'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']);
                $decrypted_card_no = str_replace(' ', '', $decrypted_card_no);
        
                $ilance->paymentgateway = construct_object('api.paymentgateway', $ilconfig['use_internal_gateway']);
                
                $ilance->paymentgateway->set_user(
                        $ilconfig['cc_login'],
                        $ilconfig['cc_password'],
                        $ilconfig['cc_key'],
                        SITE_EMAIL
                );
                
                $ilance->paymentgateway->set_customer(
                        fetch_user('username', $ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '".$v3customer_ccid."'", "user_id")),
                        $res['phone_of_cardowner'],
                        $v3customer_fname,
                        $v3customer_lname,
                        $v3customer_address,
                        $v3customer_city,
                        $v3customer_state,
                        $v3customer_zip,
                        $v3customer_country
                );
                
                $ilance->paymentgateway->set_ccard(
                        $res['name_on_card'],
                        $res['creditcard_type'],
                        $decrypted_card_no,
                        mb_substr($res['creditcard_expiry'], 0, 2),
                        mb_substr($res['creditcard_expiry'], -4),
                        $res['cvv2']
                );
                
                $ilance->paymentgateway->set_currency(
                        $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['code'],
                        $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_left']
                );
                
                $ilance->paymentgateway->set_order(
                        $res['auth_amount1'],
                        $res['trans1_id'],
                        '{_credit_card_authentication_failure_refund_one_of_two}',
                        $ilconfig['authentication_refund'],
                        $ilconfig['authentication_refund']
                );
                
                $gatewayinfo = array();
                $ilance->paymentgateway->set_extra($gatewayinfo);
                
                if (!$ilance->paymentgateway->process())
                {
                        $v3process_success = 0;
                        $transaction_messageT = $ilance->paymentgateway->get_response_message();
                        $date_timeT = DATETIME24H;
            
                        global $transaction_message, $date_time;
                        $transaction_message = $transaction_messageT;
                        $date_time = $date_timeT;
            
                        $date_time = DATETIME24H;
            
                        
                
                        $ilance->email->mail = SITE_EMAIL;
                        $ilance->email->slng = fetch_site_slng();
                        
                        $ilance->email->get('creditcard_processing_error');		
                        $ilance->email->set(array(
                                '{{gatewayresponse}}' => $ilance->paymentgateway->get_answer(),
                                '{{gatewaymessage}}' => $ilance->paymentgateway->get_response_message(),
                                '{{ipaddress}}' =>IPADDRESS,
                                '{{location}}' => LOCATION,
                                '{{scripturi}}' => SCRIPT_URI,
                                '{{gateway}}' => $ilconfig['use_internal_gateway'],
                                '{{member}}' => $_SESSION['ilancedata']['user']['username'],
                                '{{memberemail}}' => $_SESSION['ilancedata']['user']['email'],
                        ));
                        
                        $ilance->email->send();
                        
                        if (defined('LOCATION') AND LOCATION == 'cron')
                        {
                                return false;    
                        }
                        
                        $ilance->template->fetch('main', 'print_notice_payment_gateway.html');
                        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                        $ilance->template->parse_if_blocks('main');
                        $ilance->template->pprint('main', array('date_time','transaction_message','transaction_code'));
                        exit();
                }
                else
                {
                        $v3process_success = 1;
                        $ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "invoices
                                (invoiceid, user_id, description, amount, paid, totalamount, status, invoicetype, paymethod, paymentgateway, referer, ipaddress, createdate, duedate, paiddate, custommessage, transactionid, archive)
                                VALUES(
                                NULL,
                                '" . $ilance->db->fetch_field(DB_PREFIX . "creditcards","cc_id=" . $v3customer_ccid,"user_id") . "',
                                '" . $ilance->db->escape_string('{_credit_card_authentication_refund_one_of_two}') . "',
                                '" . $res['auth_amount1'] . "',
                                '" . $res['auth_amount1'] . "',
                                '" . $res['auth_amount1'] . ",
                                'paid',
                                'credit',
                                '" . $ilance->db->escape_string($res['creditcard_type']) . "',
                                '" . $ilance->db->escape_string($ilconfig['use_internal_gateway']) . "',
                                '" . $ilance->db->escape_string(REFERRER) . "',
                                '" . $ilance->db->escape_string(IPADDRESS) . "',
                                '" . DATETIME24H . "',
                                '" . DATEINVOICEDUE . "',
                                '" . DATETIME24H . "',
                                '" . $ilance->db->escape_string('{_credit_card_authentication_refund_credit_to_credit_card}') . "',
                                '" . $ilance->accounting_payment->construct_transaction_id() . "',
                                '0')
                        ", 0, null, __FILE__, __LINE__);
                }
        
                if ($v3process_success)
                {
                        $ilance->paymentgateway = construct_object('api.paymentgateway', $ilconfig['use_internal_gateway']);
                        
                        $ilance->paymentgateway->set_user(
                                $ilconfig['cc_login'],
                                $ilconfig['cc_password'],
                                $ilconfig['cc_key'],
                                SITE_EMAIL
                        );
                        
                        $ilance->paymentgateway->set_customer(
                                fetch_user('username', $ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '".$v3customer_ccid."'", "user_id")),
                                $res['phone_of_cardowner'],
                                $v3customer_fname,
                                $v3customer_lname,
                                $v3customer_address,
                                $v3customer_city,
                                $v3customer_state,
                                $v3customer_zip,
                                $v3customer_country
                        );
                        
                        $ilance->paymentgateway->set_ccard(
                                $res['name_on_card'],
                                $res['creditcard_type'],
                                $decrypted_card_no,
                                mb_substr($res['creditcard_expiry'], 0, 2),
                                mb_substr($res['creditcard_expiry'], -4),
                                $res['cvv2']
                        );
                        
                        $ilance->paymentgateway->set_currency(
                                $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['code'],
                                $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_left']
                        );
                        
                        $ilance->paymentgateway->set_order(
                                $res['auth_amount2'],
                                $res['trans2_id'],
                                '{_credit_card_authentication_failure_refund_two_of_two}',
                                $ilconfig['authentication_refund'],
                                $ilconfig['authentication_refund']
                        );
                        
                        $gatewayinfo = array();
                        $ilance->paymentgateway->set_extra($gatewayinfo);
                        
                        if (!$ilance->paymentgateway->process())
                        {
                                $v3process_success = 0;
                                $transaction_messageT = $ilance->paymentgateway->get_response_message();
                                $date_timeT = DATETIME24H;
                
                                global $transaction_message, $date_time;
                                
                                $transaction_message = $transaction_messageT;
                                $date_time = $date_timeT;
                                $date_time = DATETIME24H;
                
                                
                
                                $ilance->email->mail = SITE_EMAIL;
                                $ilance->email->slng = fetch_site_slng();
                                
                                $ilance->email->get('creditcard_processing_error');		
                                $ilance->email->set(array(
                                        '{{gatewayresponse}}' => $ilance->paymentgateway->get_answer(),
                                        '{{gatewaymessage}}' => $ilance->paymentgateway->get_response_message(),
                                        '{{ipaddress}}' =>IPADDRESS,
                                        '{{location}}' => LOCATION,
                                        '{{scripturi}}' => SCRIPT_URI,
                                        '{{gateway}}' => $ilconfig['use_internal_gateway'],
                                        '{{member}}' => $_SESSION['ilancedata']['user']['username'],
                                        '{{memberemail}}' => $_SESSION['ilancedata']['user']['email'],
                                ));
                                
                                $ilance->email->send();
                                
                                if (defined('LOCATION') AND LOCATION == 'cron')
                                {
                                        return false;    
                                }
                                
                                $ilance->template->fetch('main', 'print_notice_payment_gateway.html');
                                $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                                $ilance->template->parse_if_blocks('main');
                                $ilance->template->pprint('main', array('date_time','transaction_message','transaction_code'));
                                exit();
                        }
                        else
                        {
                                $v3process_success = 1;
                                
                                $ilance->db->query("
                                        INSERT INTO " . DB_PREFIX . "invoices
                                        (invoiceid, user_id, description, amount, paid, totalamount, status, invoicetype, paymethod, paymentgateway, referer, ipaddress, createdate, paiddate, custommessage, transactionid, archive)
                                        VALUES(
                                        NULL,
                                        '" . $ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id=" . $v3customer_ccid, "user_id") . "',
                                        '" . $ilance->db->escape_string('{_credit_card_authentication_refund_two_of_two}') . "',
                                        '" . $ilance->db->escape_string($res['auth_amount2']) . "',
                                        '" . $ilance->db->escape_string($res['auth_amount2']) . "',
                                        '" . $ilance->db->escape_string($res['auth_amount2']) . "',
                                        'paid',
                                        'credit',
                                        '" . $ilance->db->escape_string($res['creditcard_type']) . "',
                                        '" . $ilance->db->escape_string($ilconfig['use_internal_gateway']) . "',
                                        '" . $ilance->db->escape_string(REFERRER) . "',
                                        '" . $ilance->db->escape_string(IPADDRESS) . "',
                                        '" . DATETIME24H . "',
                                        '" . DATETIME24H . "',
                                        '" . $ilance->db->escape_string('{_credit_card_authentication_refund_credit_to_credit_card}') . "',
                                        '" . $ilance->accounting_payment->construct_transaction_id() . "',
                                        '0')
                                ", 0, null, __FILE__, __LINE__);
                                
                                return true;
                        }
                }
                else
                {
                        $v3process_success = 0;
                        $transaction_messageT = $ilance->paymentgateway->get_response_message();
                        $date_timeT = DATETIME24H;
            
                        global $transaction_message, $date_time;
                        $transaction_message = $transaction_messageT;
                        $date_time = $date_timeT;
            
                        $area_title = $area_title;
                        $page_title = $page_title;
                        $date_time = DATETIME24H;
            
                        
                
                        $ilance->email->mail = SITE_EMAIL;
                        $ilance->email->slng = fetch_site_slng();
                        
                        $ilance->email->get('creditcard_processing_error');		
                        $ilance->email->set(array(
                                '{{gatewayresponse}}' => $ilance->paymentgateway->get_answer(),
                                '{{gatewaymessage}}' => $ilance->paymentgateway->get_response_message(),
                                '{{ipaddress}}' =>IPADDRESS,
                                '{{location}}' => LOCATION,
                                '{{scripturi}}' => SCRIPT_URI,
                                '{{gateway}}' => $ilconfig['use_internal_gateway'],
                                '{{member}}' => $_SESSION['ilancedata']['user']['username'],
                                '{{memberemail}}' => $_SESSION['ilancedata']['user']['email'],
                        ));
                        
                        $ilance->email->send();
                        
                        if (defined('LOCATION') AND LOCATION == 'cron')
                        {
                                return false;    
                        }
                        
                        $ilance->template->fetch('main', 'print_notice_payment_gateway.html');
                        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                        $ilance->template->parse_if_blocks('main');
                        $ilance->template->pprint('main', array('date_time','transaction_message','transaction_code'));
                        exit();
                }
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>