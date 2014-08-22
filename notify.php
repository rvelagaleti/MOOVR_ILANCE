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
// #### load required javascript ###############################################
$jsinclude = array(
	'header' => array(
		'functions',
		'ajax',
		'inline'
	),
	'footer' => array(
		'v4',
		'tooltip',
		'autocomplete',
		'cron'
	)
);

// #### setup script location ##################################################
define('LOCATION', 'notify');

// #### require backend ########################################################
require_once('./functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[notify]" => $ilcrumbs["$ilpage[notify]"]);

if (isset($ilance->GPC['crypted']))
{
	$uncrypted = decrypt_url($ilance->GPC['crypted']);
}
// #### contact us handler #####################################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_submit-contactus')
{
	if (isset($ilance->GPC['subject']) AND $ilance->GPC['subject'] == 'other')
	{
		$subject = un_htmlspecialchars($ilance->GPC['subject_other']);	
	}
	else if (isset($ilance->GPC['subject']) AND $ilance->GPC['subject'] == '1')
	{
		$subject = '{_registration}';
	}
	else
	{
		$subject = '{_feedback}';	
	}
        if (!empty($ilance->GPC['message']))
        {
                $message = un_htmlspecialchars($ilance->GPC['message']);
        }
        else
        {
		$url = ($ilconfig['globalauctionsettings_seourls']) ? HTTPS_SERVER . 'main-contact' : HTTPS_SERVER . $ilpage['main'] . '?cmd=contact';
                print_notice('{_invalid_message}', '{_sorry_in_order_to_send_a_contact_us_message_you_must_type_your_message_please_try_again}', $url, '{_contact_us}');
                exit();
        }
        if (!empty($ilance->GPC['email']))
        {
                $email = un_htmlspecialchars($ilance->GPC['email']);
        }
        else
        {
		$url = ($ilconfig['globalauctionsettings_seourls']) ? HTTPS_SERVER . 'main-contact' : HTTPS_SERVER . $ilpage['main'] . '?cmd=contact';
                print_notice('{_invalid_email_address}', '{_were_sorry_in_order_to_send_a_contact_us_message_you_must_specify_your_email_address_please_try_again}', $url, '{_contact_us}');
                exit();
        }
	$name = (!empty($ilance->GPC['name'])) ? un_htmlspecialchars($ilance->GPC['name']) : 'Guest';
	
	$ilance->email->mail = SITE_EMAIL;
	$ilance->email->from = $email;
	$ilance->email->fromname = $name;
	$ilance->email->subject = $subject;
	$ilance->email->message = $message;
	$ilance->email->dohtml = false;
	$ilance->email->logtype = 'alert';
	$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];

	$ilance->email->send();
	
	$userid = isset($_SESSION['ilancedata']['user']['userid']) ? $_SESSION['ilancedata']['user']['userid'] : '';    
	log_event($userid, $ilpage['notify'], $ilance->GPC['cmd'], '', $email);
	print_notice('{_your_message_was_sent}', '{_your_message_was_sent_and_delivered_to_customer_support}', $ilpage['main'], '{_main_menu}');
	exit();
}
else
{
	refresh($ilpage['login'] . '?redirect=' . urlencode($ilpage['notify'] . print_hidden_fields(true, array(), true)));
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>