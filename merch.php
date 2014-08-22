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
		  'inline',
		  'jquery',
		  'modal'
	 ),
	'footer' => array(
		  'v4',
		  'tooltip',
		  'autocomplete',
		  'cron'
	)
);

// #### define top header nav ##################################################
$topnavlink = array(
	'main_listings'
);

// #### setup script location ##################################################
define('LOCATION', 'merch');

// #### require backend ########################################################
require_once('./functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[merch]" => $ilcrumbs["$ilpage[merch]"]);

// #### decrypt our encrypted url ##############################################
$uncrypted = (!empty($ilance->GPC['crypted'])) ? decrypt_url($ilance->GPC['crypted']) : array();

// #### escrow return ##############################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_escrow_paid_return')
{
	 $area_title = '{_instant_purchase_to_escrow_via_online_account_complete}';
	 $page_title = SITE_NAME . ' - {_instant_purchase_to_escrow_via_online_account_complete}';
	 print_notice('{_invoice_payment_complete}', '{_your_invoice_has_been_paid_in_full}', HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&bidsub=buynow-escrow', '{_escrow_management}');
	 exit();
}
// #### order complete landing #################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'ordercomplete' AND (isset($ilance->GPC['pid']) OR isset($ilance->GPC['oid'])))
{
	 $area_title = '{_congratulations_you_purchased_this_item}';
	 $page_title = SITE_NAME . ' - {_congratulations_you_purchased_this_item}';
	 $pid = isset($ilance->GPC['pid']) ? $ilance->GPC['pid'] : 0;
	 $oid = isset($ilance->GPC['oid']) ? $ilance->GPC['oid'] : 0;
	 $pprint_array = array('pid','oid','onsubmit','orderidradios','paymethod','shipperid','days','shippingservice','shippingradios','hiddenfields','pid','qty','paymethodsradios','paymethods','returnurl','tax','paymethod','fees','digitalfile','cb_shipping_address_required1','cb_shipping_address_required0','encrypted','samount','amount_formatted','total','shipping_address_pulldown','forceredirect','payment_method_pulldown','attachment','project_id','seller_id','buyer_id','user_cookie','project_title','seller','qty','topay','amount','remote_addr','rid','category','subcategory');
  
	 ($apihook = $ilance->api('merch_ordercomplete_end')) ? eval($apihook) : false;

	 $ilance->template->fetch('main', 'listing_forward_auction_ordercomplete.html');
	 $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	 $ilance->template->parse_loop('main', 'similaritems');
	 $ilance->template->parse_if_blocks('main');
	 $ilance->template->pprint('main', $pprint_array);
	 exit();
	 
}
// #### HANDLE SELLER TOOLS FROM LISTING PAGE ##################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'sellertools' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'enhancements' AND isset($ilance->GPC['pid']) AND $ilance->GPC['pid'] > 0)
{
	 if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
	 {
		 refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['merch'] . print_hidden_fields($string = true, $excluded = array(), $questionmarkfirst = true)));
		 exit();
	 }
	 // #### HANDLE AUCTION LISTING ENHANCEMENTS ####################
	 // this will attempt to debit the acocunt of the users account balance if possible
	 $ilance->GPC['featured'] = $ilance->GPC['old']['featured'];
	 $ilance->GPC['featured_searchresults'] = $ilance->GPC['old']['featured_searchresults'];
	 $ilance->GPC['highlite'] = $ilance->GPC['old']['highlite'];
	 $ilance->GPC['bold'] = $ilance->GPC['old']['bold'];
	 $ilance->GPC['autorelist'] = $ilance->GPC['old']['autorelist'];
	 $ilance->GPC['enhancements'] = (!empty($ilance->GPC['enhancements']) ? $ilance->GPC['enhancements'] : array());
	 if (is_array($ilance->GPC['enhancements']))
	 {
		  $enhance = $ilance->auction_fee->process_listing_enhancements_transaction($ilance->GPC['enhancements'], $_SESSION['ilancedata']['user']['userid'], intval($ilance->GPC['pid']), 'update', 'product');
		  if (is_array($enhance))
		  {
			   $ilance->GPC['featured'] = (int)$enhance['featured'];
			   $ilance->GPC['featured_searchresults'] = (int)$enhance['featured_searchresults'];
			   $ilance->GPC['highlite'] = (int)$enhance['highlite'];
			   $ilance->GPC['bold'] = (int)$enhance['bold'];
			   $ilance->GPC['autorelist'] = (int)$enhance['autorelist'];
			   $ilance->GPC['featured_date'] = ($ilance->GPC['featured'] AND isset($ilance->GPC['old']['featured_date']) AND $ilance->GPC['old']['featured_date'] == '0000-00-00 00:00:00') ? DATETIME24H : '0000-00-00 00:00:00';
		  }
  
		  // #### update auction #########################################
		  $ilance->db->query("
			   UPDATE " . DB_PREFIX . "projects 
			   SET featured = '" . intval($ilance->GPC['featured']) . "',
			   featured_date = '" . $ilance->db->escape_string($ilance->GPC['featured_date']) . "',
			   featured_searchresults = '" . $ilance->db->escape_string($ilance->GPC['featured_searchresults']) . "',
			   highlite = '" . intval($ilance->GPC['highlite']) . "',
			   bold = '" . intval($ilance->GPC['bold']) . "',
			   autorelist = '" . intval($ilance->GPC['autorelist']) . "'
			   WHERE project_id = '" . intval($ilance->GPC['pid']) . "'
				   AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			   LIMIT 1
		  ", 0, null, __FILE__, __LINE__);
	 }
	 print_notice('{_listing_updated}', '{_the_options_you_selected_have_been_completed_successfully}', HTTP_SERVER . $ilpage['merch'] . '?id=' . $ilance->GPC['pid'], '{_return_to_listing}');
	 exit();
}
// #### CONTACT ##########################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'contact')
{
	 if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
	 {
		  // #### SUBMIT contact ###################################################
		  if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'submit_contact')
		  {
			   $ilance->GPC['contact_message'] = strip_vulgar_words($ilance->GPC['contact_message']);
			   $ilance->GPC['memberstart'] = print_date(fetch_user('date_added', $_SESSION['ilancedata']['user']['userid']), $ilconfig['globalserverlocale_globaldateformat']);
			   if (empty($ilance->GPC['contact_message']))
			   {
				   $area_title = '{_sending_contact_notification}';
				   $page_title = SITE_NAME . ' - {_sending_contact_notification}';
				   print_notice('{_please_enter_all_fields}', '{_please_enter_a_message_to_continue_submiting_this_contact_report_thank_you}', 'javascript:history.back(1);', '{_back}');
				   exit();
			   }
   
			   $area_title = '{_contact_notification_was_posted_menu}';
			   $page_title = SITE_NAME . ' - {_contact_notification_was_posted_menu}';
   
			   ($apihook = $ilance->api('contact_submit_start')) ? eval($apihook) : false;
   
			   $listing_url = '';
			   if(isset($ilance->GPC['id']))
			   {
					$page = (fetch_auction('project_state', $ilance->GPC['id']) == 'product') ? 'merch' : 'rfp';
					$listing_url = HTTP_SERVER . $ilpage[$page] . '?id=' . $ilance->GPC['id'];
			   }
			   
			   $ilance->email->mail = fetch_user('email', $ilance->GPC['userid']);
			   $ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
			   $ilance->email->get('submit_contact_item');
			   $ilance->email->set(array(
				   '{{subject_title}}' => $ilance->GPC['subject_title'],
				   '{{reporter}}' => $_SESSION['ilancedata']['user']['username'],
				   '{{reporteremail}}' => $_SESSION['ilancedata']['user']['email'],
				   '{{contact_message}}' => $ilance->GPC['contact_message'],
				   '{{user}}' => $ilance->GPC['username'],
				   '{{sender_email}}' => $_SESSION['ilancedata']['user']['email'],
					'{{listingurl}}' => $listing_url,

			   ));
			   $ilance->email->send();
			   if (isset($ilance->GPC['email']))
			   {
				   $ilance->email->mail = $_SESSION['ilancedata']['user']['email'];
				   $ilance->email->get('submit_contact_item_copy');
				   $ilance->email->set(array(
					   '{{subject_title}}' => $ilance->GPC['subject_title'],
					   '{{reporter}}' => $_SESSION['ilancedata']['user']['username'],
					   '{{reporteremail}}' => $_SESSION['ilancedata']['user']['email'],
					   '{{contact_message}}' => $ilance->GPC['contact_message'],
					   '{{user}}' => $ilance->GPC['username'],
					   '{{sender_email}}' => $_SESSION['ilancedata']['user']['email'],
				   ));
				   $ilance->email->send();
			   }
   
			   ($apihook = $ilance->api('contact_submit_end')) ? eval($apihook) : false;
   
			   log_event($_SESSION['ilancedata']['user']['userid'], $ilpage['merch'], $ilance->GPC['cmd'], $ilance->GPC['subcmd'], $ilance->GPC['userid']);
			   print_notice('{_your_message_was_sent}', $ilance->language->construct_phrase('{_your_message_was_sent_and_delivered_to_x}',array($ilance->GPC['username'])) , $ilpage['main'], '{_main_menu}');
			   exit();
		  }
		  else
		  {
			   $area_title = '{_sending_contact_notification}';
			   $page_title = SITE_NAME . ' - {_sending_contact_notification}';
			   $userid = isset($ilance->GPC['uid']) ? $ilance->GPC['uid'] : 0;
			   $username = fetch_user('username', $ilance->GPC['uid']);
			   $id = isset($ilance->GPC['pid']) ? $ilance->GPC['pid'] : '';
			   if (!empty($id))
			   {
				    $project_title = $ilance->db->fetch_field(DB_PREFIX . "projects", "project_id = '" . intval($ilance->GPC['pid']) . "'", "project_title");
				    $profile_title = handle_input_keywords($project_title);
				    $link = HTTP_SERVER . $ilpage['merch'] . '?id=' . $ilance->GPC['pid'];
			   }
			   else
			   {
				    $project_title = $link = '';
			   }
			   $pprint_array = array('link','project_title','userid', 'username','type','contacttype_pulldown','requesturi','url','cmd','id','input_style');
   
			   ($apihook = $ilance->api('contact_start')) ? eval($apihook) : false;
   
			   $ilance->template->fetch('main', 'contact.html');
			   $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			   $ilance->template->parse_if_blocks('main');
			   $ilance->template->pprint('main', $pprint_array);
			   exit();
		  }
	 }
	 else
	 {
		  // #### SUBMIT contact as guest ###################################################
		  if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'submit_contact_guest')
		  {
			   $ilance->GPC['contact_message'] = strip_vulgar_words($ilance->GPC['contact_message']);
			   if (empty($ilance->GPC['contact_message']))
			   {
				    $area_title = '{_sending_contact_notification}';
				    $page_title = SITE_NAME . ' - {_sending_contact_notification}';
				    print_notice('{_please_enter_all_fields}', '{_please_enter_a_message_to_continue_submiting_this_contact_report_thank_you}', 'javascript:history.back(1);', '{_back}');
				    exit();
			   }
			   $area_title = '{_contact_notification_was_posted_menu}';
			   $page_title = SITE_NAME . ' - {_contact_notification_was_posted_menu}';
   
			   ($apihook = $ilance->api('contact_submit_start')) ? eval($apihook) : false;
   
			   
			   $ilance->email->from = $ilance->GPC['email_address'];
			   $ilance->email->mail = fetch_user('email', $ilance->GPC['userid']);
			   $ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
			   $ilance->email->get('submit_contact_item_guest');
			   $ilance->email->set(array(
				    '{{subject_title}}' => $ilance->GPC['subject_title'],
				    '{{contact_message}}' => $ilance->GPC['contact_message'],
				    '{{user}}' => $ilance->GPC['username'],
				    '{{sender_email}}' => $ilance->GPC['email_address'],
			   ));
			   $ilance->email->send();
			   if (isset($ilance->GPC['email']))
			   {
				    $ilance->email->mail = $ilance->GPC['email_address'];
				    $ilance->email->get('submit_contact_item_copy_guest');
				    $ilance->email->set(array(
					    '{{subject_title}}' => $ilance->GPC['subject_title'],
					    '{{contact_message}}' => $ilance->GPC['contact_message'],
					    '{{user}}' => $ilance->GPC['username'],
					    '{{sender_email}}' => $ilance->GPC['email_address'],
				    ));
				    $ilance->email->send();
			   }
   
			   ($apihook = $ilance->api('contact_submit_end')) ? eval($apihook) : false;
   
			   print_notice('{_your_message_was_sent}', $ilance->language->construct_phrase('{_your_message_was_sent_and_delivered_to_x}',array($ilance->GPC['username'])) , $ilpage['main'], '{_main_menu}');
			   exit();
		 }
		  else
		  {
			 $area_title = '{_sending_contact_notification}';
			 $page_title = SITE_NAME . ' - {_sending_contact_notification}';
			 if(isset($ilance->GPC['user']) AND !empty($ilance->GPC['user']) AND (!isset($ilance->GPC['id']) OR empty($ilance->GPC['id'])))
			 {
				 refresh($ilpage['login'] . '?redirect=' . urlencode($ilpage['contact'] . print_hidden_fields($string = true, $excluded = array(), $questionmarkfirst = true)));
				 exit();
			 }
 
			 $userid = isset($ilance->GPC['uid']) ? $ilance->GPC['uid'] : 0;
			 $username = fetch_user('username', $ilance->GPC['uid']);
			 $id = isset($ilance->GPC['pid']) ? $ilance->GPC['pid'] : '';
			 if(!empty($id))
			 {
				 $project_title = $ilance->db->fetch_field(DB_PREFIX . "projects", "project_id = '" . intval($id) . "'", "project_title");
				 $link = HTTP_SERVER.$ilpage['merch']."?id=".$id;
			 }
			 else
			 {
				 $project_title = '';
				 $link = '';
			 }
			 $pprint_array = array('link','project_title','userid', 'username','type','contacttype_pulldown','requesturi','url','cmd','id','input_style');
 
			 ($apihook = $ilance->api('contact_start')) ? eval($apihook) : false;
 
			 $ilance->template->fetch('main', 'contact_guest.html');
			 $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			 $ilance->template->parse_if_blocks('main');
			 $ilance->template->pprint('main', $pprint_array);
			 exit();
		 }
	 }
}
// #### HANDLE DIRECT PAYMENT FROM BUYER TO SELLER #############################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'directpay' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	 include_once(DIR_SERVER_ROOT . 'merch_directpay.php');
}
// #### INSERT NEW PUBLIC MESSAGE ##############################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'insertmessage' AND isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
{
	 if (empty($ilance->GPC['message']))
	 {
		  print_notice('{_message_cannot_be_empty}', '{_please_retry_your_action}', 'javascript: history.go(-1)', '{_retry}');
		  exit();
	 }
	 $ilance->pmb->insert_public_message(intval($ilance->GPC['pid']), intval($ilance->GPC['sellerid']), $_SESSION['ilancedata']['user']['userid'], $_SESSION['ilancedata']['user']['username'], $ilance->GPC['message'], '1');
	 // todo: check for seo
	 if (!empty($ilance->GPC['returnurl']))
	 {
		  refresh(urldecode($ilance->GPC['returnurl']));
	 }
	 else
	 {
		  refresh($ilpage['merch'] . '?id=' . intval($ilance->GPC['pid']) . '&tab=messages#tabmessages');
	 }
	 exit();
}
// #### REMOVE PUBLIC MESSAGE ##################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'removemessage' AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	 if (empty($ilance->GPC['messageid']))
	 {
		  print_notice('{_message_does_not_exist}', '{_please_retry_your_action}', 'javascript: history.go(-1)', '{_retry}');
		  exit();
	 }
	 $sql = $ilance->db->query("
		  DELETE FROM " . DB_PREFIX . "messages
		  WHERE messageid = '" . intval($ilance->GPC['messageid']) . "'
		      AND project_id = '" . intval($ilance->GPC['pid']) . "'
		  LIMIT 1
	 ", 0, null, __FILE__, __LINE__);
	 // todo: check for seo
	 refresh($ilpage['merch'] . '?id=' . intval($ilance->GPC['pid']) . '&tab=messages#tabmessages');
	 exit();
}
// #### BUY NOW INSTANT PAYMENT PROCESS HANDLER ################################
else if (isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_instant-purchase-process' AND isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0 AND isset($ilance->GPC['seller_id']) AND $ilance->GPC['seller_id'] > 0 AND isset($ilance->GPC['buyer_id']) AND $ilance->GPC['buyer_id'] > 0 AND isset($ilance->GPC['qty']) AND $ilance->GPC['qty'] > 0) OR isset($uncrypted['cmd']) AND $uncrypted['cmd'] == '_instant-purchase-process' AND isset($uncrypted['project_id']) AND $uncrypted['project_id'] > 0 AND isset($uncrypted['seller_id']) AND $uncrypted['seller_id'] > 0 AND isset($uncrypted['buyer_id']) AND $uncrypted['buyer_id'] > 0 AND isset($uncrypted['qty']) AND $uncrypted['qty'] > 0)
{
	 if (!isset($uncrypted['account_id']))
	 {
		$uncrypted['account_id'] = (isset($ilance->GPC['account_id']) ? $ilance->GPC['account_id'] : 'account');  
	 }
	 if (!isset($uncrypted['shipping_address_id']))
	 {
		 $uncrypted['shipping_address_id'] = (isset($ilance->GPC['shipping_address_id']) ? $ilance->GPC['shipping_address_id'] : ''); 
	 }
	 if (!isset($uncrypted['shipping_address_required']))
	 {
		 $uncrypted['shipping_address_required'] = (isset($ilance->GPC['shipping_address_required']) ? $ilance->GPC['shipping_address_required'] : 0); 
	 }
	 if (!isset($uncrypted['ccsubmit']))
	 {
		  $uncrypted['ccsubmit'] = (isset($ilance->GPC['ccsubmit']) ? $ilance->GPC['ccsubmit'] : 0);
	 }
	 if (!isset($uncrypted['shipperid']))
	 {
		  $uncrypted['shipperid'] = (isset($ilance->GPC['shipperid']) ? $ilance->GPC['shipperid'] : 0);
	 }
	 if (!isset($uncrypted['custom']))
	 {
		  $uncrypted['custom'] = (isset($ilance->GPC['custom']) ? $ilance->GPC['custom'] : '');
	 }
	 if (!isset($uncrypted['qty']))
	 {
		  $uncrypted['qty'] = (isset($ilance->GPC['qty']) ? $ilance->GPC['qty'] : 1);
	 }
	 if (!isset($uncrypted['buyershipperid']))
	 {
		  $uncrypted['buyershipperid'] = (isset($ilance->GPC['buyershipperid']) ? $ilance->GPC['buyershipperid'] : 0);
	 }
	 if (!isset($uncrypted['project_id']))
	 {
		  $uncrypted['project_id'] = (isset($ilance->GPC['project_id']) ? $ilance->GPC['project_id'] : 0);
	 }
	 if (!isset($uncrypted['orderid']))
	 {
		  $uncrypted['orderid'] = (isset($ilance->GPC['orderid']) ? $ilance->GPC['orderid'] : 0);
	 }
	 if (!isset($uncrypted['buyer_id']))
	 {
		  $uncrypted['buyer_id'] = (isset($ilance->GPC['buyer_id']) ? $ilance->GPC['buyer_id'] : $_SESSION['ilancedata']['user']['userid']);
	 }
	 include_once(DIR_CORE . 'functions_buynow.php');
	 process_buy_now_payment($uncrypted, $_SESSION['ilancedata']['user']['slng']);
	 exit();
}

// #### BUY NOW CONFIMRATION PAGE AND ITEM ORDER DISPLAY #######################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'purchase-confirm' AND isset($ilance->GPC['pid']) AND $ilance->GPC['pid'] > 0 AND isset($ilance->GPC['qty']) AND $ilance->GPC['qty'] > 0)
{
	 if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
	 {
		 refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['merch'] . print_hidden_fields(true, array(), true)));
		 exit();
	 }
	 if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'buynow') == 'no')
	 {
		 $area_title = '{_bid_preview_denied_upgrade_subscription}';
		 $page_title = SITE_NAME . ' - {_bid_preview_denied_upgrade_subscription}';
		 print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource} <span class="blue"><a href="' . HTTP_SERVER . $ilpage['subscription'] . '"><strong>{_click_here}</strong></a>', $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('productbid'));
		 exit();
	 }
	 
	 ($apihook = $ilance->api('merch_purchase_confirm_start')) ? eval($apihook) : false;
	 
	 $project_id = intval($ilance->GPC['pid']);
	 $sql_auction = $ilance->db->query("
		  SELECT user_id, buynow_price, buynow_qty 
		  FROM " . DB_PREFIX . "projects
		  WHERE project_id = '" . $project_id . "'
	 ");
	 $res_auction = $ilance->db->fetch_array($sql_auction, DB_ASSOC);
	 $seller_id = $res_auction['user_id'];
	 $amount = $res_auction['buynow_price'];
	 $qtyleft = $res_auction['buynow_qty'];
	 $qty = (isset($ilance->GPC['qty']) AND $ilance->GPC['qty'] > 0) ? number_format(intval($ilance->GPC['qty']), 0) : 1;
	 $hiddenfields = '';
	 
	 ($apihook = $ilance->api('merch_purchase_confirm_assign_vars')) ? eval($apihook) : false;
	 
	 $hiddenfields .= '<input type="hidden" name="cmd" value="purchase-confirm" /><input type="hidden" name="pid" value="' . $project_id . '" /><input type="hidden" name="qty" value="' . $qty . '" />';
	 // #### make sure buyer is not purchasing more qty than applicable
	 if ($qty > $qtyleft)
	 {
		 $area_title = '{_access_denied}';
		 $page_title = SITE_NAME . ' - {_access_denied}';
		 print_notice($area_title, '{_it_appears_you_are_trying_to_purchase_more_quantity_than_this_seller_is_currently_offering}', 'javascript:history.back(1);', '{_back}');
		 exit();
	 }
	 // #### make sure we are not the seller of this auction! ##############
	 if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['userid'] == $seller_id)
	 {
		 $area_title = '{_access_denied}';
		 $page_title = SITE_NAME . ' - {_access_denied}';
		 print_notice($area_title, '{_it_appears_you_are_the_seller_of_this_listing_in_this_case_you_cannot_bid_or_purchase_items_from_your_own_listing}', 'javascript:history.back(1);', '{_back}');
		 exit();
	 }
	 // #### do we have anything left to purchase? #########################
	 $sql = $ilance->db->query("
		  SELECT p.filter_escrow, p.filter_gateway, p.filter_ccgateway, p.filter_offline, p.filtered_auctiontype, p.buynow_qty, p.buynow_qty_lot, p.items_in_lot, p.project_title, p.paymethod, p.paymethodcc, p.paymethodoptions, p.paymethodoptionsemail, p.currencyid, p.country, p.state, p.city, p.zipcode, s.ship_handlingtime, s.ship_method 
		  FROM " . DB_PREFIX . "projects p
		  LEFT JOIN " . DB_PREFIX . "projects_shipping s ON p.project_id = s.project_id
		  LEFT JOIN " . DB_PREFIX . "projects_shipping_destinations sd ON p.project_id = sd.project_id
		  WHERE p.project_id = '" . intval($project_id) . "'
			   AND p.buynow_qty > 0
		  LIMIT 1
	 ", 0, null, __FILE__, __LINE__);
	 if ($ilance->db->num_rows($sql) == 0)
	 {
		$area_title = '{_access_denied}';
		$page_title = SITE_NAME . ' - {_access_denied}';
		print_notice('{_this_item_has_sold_out}', '{_were_sorry_either_one_or_more_customers_have_already_purchased_this_item_and_the_qty}', $ilpage['merch'] . '?cmd=listings', '{_view_other_merchandise}');
		exit();
	 }
	 $res = $ilance->db->fetch_array($sql, DB_ASSOC);
	 $show['localpickuponly'] = $res['ship_method'] == 'localpickup' ? true : false;
	 $show['hidepaymethodchange'] = $show['digital_download_delivery'] = $show['hideshippingmethodchange'] = false;
	 $itemlocation = $ilance->common_location->print_auction_location(intval($project_id), '', $res['country'], $res['state'], $res['city'], $res['zipcode']);
	 // #### fetch the number of payment methods and shipping services available to buyer
	 $methodscount = $ilance->payment->print_payment_methods($project_id, false, true);
	 $shippercount = $ilance->shipping->print_shipping_methods($project_id, $qty, false, true, false, $_SESSION['ilancedata']['user']['countryid'], $_SESSION['ilancedata']['user']['slng']);
	 if ($res['ship_method'] == 'digital')
	 {
		  $show['digital_download_delivery'] = true;
	 }
	 // #### if we only have 1 shipping service and it hasn't been selected, auto-select it for buyer
	 if ($shippercount == 1 AND empty($ilance->GPC['shipperid']))
	 {
		  $ilance->shipping->print_shipping_methods($project_id, $qty, false, false, false, $_SESSION['ilancedata']['user']['countryid'], $_SESSION['ilancedata']['user']['slng']);
		  $ilance->GPC['shipperid'] = $ilance->db->fetch_field(DB_PREFIX . "projects_shipping_destinations", "project_id = '" . intval($project_id) . "'", "ship_service_$shipperidrow");
		  $show['hideshippingmethodchange'] = true;
	 }
	 if (($methodscount > 1 AND empty($ilance->GPC['paymethod'])) OR ($shippercount > 1 AND empty($ilance->GPC['shipperid'])) OR ($shippercount == 0 AND $show['localpickuponly'] == false AND $show['digital_download_delivery'] == false))
	 {
		  $area_title = '{_confirm_payment_method}';
		  $page_title = SITE_NAME . ' - {_confirm_payment_method}';
		  $qty = isset($ilance->GPC['qty']) ? intval($ilance->GPC['qty']) : 1;
		  $pid = $project_id;
		  $navcrumb = array();
		  if ($ilconfig['globalauctionsettings_seourls'])
		  {
			  $catmap = print_seo_url($ilconfig['productcatmapidentifier']);
			  $navcrumb["$catmap"] = '{_buy}';
			  unset($catmap);
		  }
		  else
		  {
			  $navcrumb["$ilpage[merch]?cmd=listings"] = '{_buy}';
		  }
		  $navcrumb["$ilpage[merch]?id=" . $project_id] = fetch_auction('project_title', $project_id);
		  $navcrumb[""] = '{_confirm_payment_method}';
		  // #### radio input for buyers payment & shipping decision
		  $paymentmethodradios_js = $shippingradios_js = '';
		  $paymethodsradios = $ilance->payment->print_payment_methods($pid, true);
		  $shippingradios = $ilance->shipping->print_shipping_methods($pid, $qty, true, false, false, $_SESSION['ilancedata']['user']['countryid'], $_SESSION['ilancedata']['user']['slng']);
		  $shippingradioscount = $ilance->shipping->print_shipping_methods($pid, $qty, false, true, false, $_SESSION['ilancedata']['user']['countryid'], $_SESSION['ilancedata']['user']['slng']);
		  $shippingservice = '';
		  $shipperid = 0;
		  $days = 3;
		  $ilance->shipping->print_shipping_methods($pid, $qty, false, false, false, $_SESSION['ilancedata']['user']['countryid'], $_SESSION['ilancedata']['user']['slng']);
		  if ($shippingradioscount == 1 AND $shipperidrow > 0)
		  {
			  $shipperid = $ilance->db->fetch_field(DB_PREFIX . "projects_shipping_destinations", "project_id = '$pid'", "ship_service_$shipperidrow");
			  $shippingservice = $ilance->shipping->print_shipping_partner($shipperid);
			  $days = $res['ship_handlingtime'];
		  }
		  $orderidradios = $ilance->payment->print_orderid_methods($pid, $_SESSION['ilancedata']['user']['userid']);
		  if (isset($show['multipleorders']) AND $show['multipleorders'])
		  {
			 $onsubmit = 'return validate_all()';
			 $headinclude .= '<script type="text/javascript">
function validate_order_id()
{
	 var haveerror = true;
	 for (var i = 0; i < document.ilform.orderid.length; i++)
	 {
		 if (document.ilform.orderid[i].checked) 
		 {
			 haveerror = false;
		 }
	 }
	 if (haveerror == true)
	 {
		 alert_js(phrase[\'_please_select_one_existing_order_radio_button\']);
		 return(false);
	 }
	 return(true);
}
function validate_paymethod()
{
	 return(true);
}
function validate_ship_service()
{
	 return(true);
}
function validate_all()
{
	 return validate_order_id() && validate_paymethod() && validate_ship_service();
}
</script>';
		  }
		  else
		  {
			   $onsubmit = 'return validate_all()';
			   $headinclude .= '<script type="text/javascript">
<!--
function validate_paymethod()
{
	 var haveerror = true;
' . $paymentmethodradios_js . '
	 if (haveerror == true)
	 {
		 alert_js(phrase[\'_please_select_one_payment_method_radio\']);
		 return(false);
	 }
	 return(true);
}
function validate_ship_service()
{
	 var haveerror = true;
' . $shippingradios_js . '
	 if (haveerror == true)
	 {
		 alert_js(phrase[\'_please_select_one_shipping_method_radio\']);
		 return(false);
	 }
	 return(true);
}
function validate_all()
{
	 return validate_paymethod() ' . (($shippingradioscount > 0) ? "&& validate_ship_service();" : ";") . '
}
//-->
</script>';  
		  }
 
		  $pprint_array = array('onsubmit','orderidradios','paymethod','shipperid','days','shippingservice','shippingradios','hiddenfields','pid','qty','paymethodsradios','paymethods','returnurl','tax','paymethod','fees','digitalfile','cb_shipping_address_required1','cb_shipping_address_required0','encrypted','samount','amount_formatted','total','shipping_address_pulldown','forceredirect','payment_method_pulldown','attachment','project_id','seller_id','buyer_id','user_cookie','project_title','seller','qty','topay','amount','remote_addr','rid','category','subcategory');
  
		  ($apihook = $ilance->api('listing_payment_selection_end')) ? eval($apihook) : false;
  
		  $ilance->template->fetch('main', 'listing_payment_selection.html');
		  $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		  $ilance->template->parse_loop('main', 'paymentoptions');
		  $ilance->template->parse_if_blocks('main');
		  $ilance->template->pprint('main', $pprint_array);
		  exit();
	 }
	 $area_title = '{_purchase_now_order_confirmation}';
	 $page_title = SITE_NAME . ' - {_purchase_now_order_confirmation}';
	 $navcrumb = array();
	 if ($ilconfig['globalauctionsettings_seourls'])
	 {
		  $navcrumb[HTTP_SERVER . print_seo_url($ilconfig['listingsidentifier'])] = '{_buy}';
	 }
	 else
	 {
		  $navcrumb["$ilpage[merch]?cmd=listings"] = '{_buy}';
	 }
	 $navcrumb["$ilpage[merch]?id=" . $project_id] = fetch_auction('project_title', $project_id);
	 $navcrumb[""] = '{_commit_to_purchase}';
	 // #### template conditions ###########################################
	 $show['lot'] =  $res['buynow_qty_lot'] == '1' ? true : false;
	 $show['taxbit'] = $show['sellerusingescrow'] = $show['filter_escrow'] = $show['noshipping'] = $show['digitaldownload'] = false;
	 $show['makepayment'] = ((!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0) ? true : false);
	 // #### if we only have 1 payment method and it hasn't been selected, auto-select it for buyer
	 if ($methodscount == 1 AND empty($ilance->GPC['paymethod']))
	 {
		 $show['hidepaymethodchange'] = true;
	 }
	 if ($shippercount == 1)
	 {
		  $show['hideshippingmethodchange'] = true;
	 }
	 // #### shipping address defaults #####################################
	 $cb_shipping_address_required0 = $digitalfile = '';
	 $cb_shipping_address_required1 = 'checked="checked"';
	 $dquery = $ilance->db->query("
		  SELECT filename, filesize, attachid
		  FROM " . DB_PREFIX . "attachment
		  WHERE project_id = '" . intval($project_id) . "'
			   AND attachtype = 'digital'
		  LIMIT 1
	 ", 0, null, __FILE__, __LINE__);
	 if ($ilance->db->num_rows($dquery) > 0)
	 {
		  $dfile = $ilance->db->fetch_array($dquery, DB_ASSOC);
		  $digitalfile = stripslashes($dfile['filename']) . ' (' . print_filesize($dfile['filesize']) . ')';
		  $show['noshipping'] = $show['digitaldownload'] = true;
		  $cb_shipping_address_required1 = '';
		  $cb_shipping_address_required0 = 'checked="checked"';
	 }
	 // #### seller url ####################################################
	 $seller = print_username($seller_id, 'plain');
	 // #### amounts and shipping costs formatted ##########################
	 $shippingservice = '';
	 $ilance->GPC['buyershipcost'] = 0;
	 $ilance->GPC['shipperid'] = isset($ilance->GPC['shipperid']) ? intval($ilance->GPC['shipperid']) : 0;
	 $show['freeshipping'] = false;
	 if ($ilance->GPC['shipperid'] > 0)
	 {
		  $shippingcosts = $ilance->shipping->fetch_ship_cost_by_shipperid($project_id, $ilance->GPC['shipperid'], $qty);
		  if ($shippingcosts['free'])
		  {
			   $show['freeshipping'] = true;
			   $ilance->GPC['buyershipcost'] = $shippingcosts['total'];
		  }
		  else
		  {
			   $ilance->GPC['buyershipcost'] = $shippingcosts['total'];
		  }
		  $shippingservice = $ilance->shipping->print_shipping_partner($ilance->GPC['shipperid']);
	 }
	 else if (isset($show['digitaldownload']) AND $show['digitaldownload'])
	 {
		  $shippingservice = '{_digital_download}';
	 }
	 else if (isset($show['localpickuponly']) AND $show['localpickuponly'])
	 {
		  $shippingservice = '{_local_pickup_only}';
	 }
	 $amount_formatted = $ilance->currency->format($amount, $res['currencyid']);
	 $samount = $ilance->currency->format($ilance->GPC['buyershipcost'], $res['currencyid']);
	 $topay = $ilance->currency->format(($amount * $qty) + $ilance->GPC['buyershipcost'], $res['currencyid']);
	 $total = ($amount * $qty) + $ilance->GPC['buyershipcost'];
	 $project_title = handle_input_keywords($res['project_title']);
	 $tax = $ilance->currency->format(0, $res['currencyid']);
	 $attachment = $ilance->auction->print_item_photo('javascript:void(0)', 'thumb', $project_id, 1, '#ccc');
	 $fee = $taxamount = 0;
	 // escrow checkup
	 if (($res['filter_escrow'] == '1' AND $res['filter_offline'] == '0' AND $res['filter_gateway'] == '0' AND $res['filter_ccgateway'] == '0' AND $ilconfig['escrowsystem_bidderfixedprice'] > 0) OR (isset($ilance->GPC['paymethod']) AND $ilance->GPC['paymethod'] == 'escrow' AND $ilconfig['escrowsystem_bidderfixedprice'] > 0))
	 {
		  // fixed escrow fee cost to buyer
		  $fee = sprintf("%01.2f", $ilconfig['escrowsystem_bidderfixedprice']);
	 }
	 else
	 {
		  if (($res['filter_escrow'] == '1' AND $res['filter_offline'] == '0' AND $res['filter_gateway'] == '0' AND $res['filter_ccgateway'] == '0' AND $ilconfig['escrowsystem_bidderpercentrate'] > 0) OR (isset($ilance->GPC['paymethod']) AND $ilance->GPC['paymethod'] == 'escrow' AND $ilconfig['escrowsystem_bidderpercentrate'] > 0))
		  {
			   // percentage rate of total winning bid or buy now amount
			   $fee = sprintf("%01.2f", ($total * $ilconfig['escrowsystem_bidderpercentrate']) / 100);
			   if ($ilconfig['globalserverlocale_currencyselector'] AND $res['currencyid'] != $ilconfig['globalserverlocale_defaultcurrency'])
			   {
				    //$total_site_currency = convert_currency($ilconfig['globalserverlocale_defaultcurrency'], $total, $res['currencyid']);
				    //$fee = sprintf("%01.2f", ($total_site_currency * $ilconfig['escrowsystem_bidderpercentrate']) / 100);
				    $fee = sprintf("%01.2f", ($total * $ilconfig['escrowsystem_bidderpercentrate']) / 100);
				    //unset($total_site_currency);
			   }
		  }
	 }
	 if ($fee > 0)
	 {
		  if ($ilance->tax->is_taxable($_SESSION['ilancedata']['user']['userid'], 'commission'))
		  {
			   // fetch tax amount to charge for this invoice type
			   $taxamount = $ilance->tax->fetch_amount($_SESSION['ilancedata']['user']['userid'], $fee, 'commission', 0);
			   $tax = print_currency_conversion($res['currencyid'], $taxamount, $ilconfig['globalserverlocale_defaultcurrency']);
			   $show['taxbit'] = true;
		  }
	 }
	 if ($ilconfig['globalserverlocale_currencyselector'] AND $res['currencyid'] != $ilconfig['globalserverlocale_defaultcurrency'])
	 {
		  $show['currencyconverted'] = true;
		  //$fee_converted = convert_currency($res['currencyid'], sprintf("%01.2f", ($fee + $taxamount)), $ilconfig['globalserverlocale_defaultcurrency']);
		  $fee_converted = sprintf("%01.2f", ($fee + $taxamount));
		  $total = ($total + $fee_converted);
		  unset($fee_converted);
		  //$topay = print_currency_conversion($ilconfig['globalserverlocale_defaultcurrency'], $total, $res['currencyid']);
		  $topay = $ilance->currency->format($total, $res['currencyid']);
		  $fees = (($fee + $taxamount) > 0) ? print_currency_conversion($ilconfig['globalserverlocale_defaultcurrency'], ($fee + $taxamount), $res['currencyid']) : '-';
		  $curfrom = $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['currency_abbrev'];
		  $curto = $ilance->currency->currencies[$res['currencyid']]['currency_abbrev'];
		  $curdefault = $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['currency_abbrev'];
	 }
	 else
	 {
		  $show['currencyconverted'] = false;
		  $total = ($total + $fee + $taxamount);
		  $topay = $ilance->currency->format($total, $res['currencyid']);
		  $fees = (($fee + $taxamount) > 0) ? $ilance->currency->format(($fee + $taxamount)) : '-';
		  $curfrom = '';
		  $curto = '';
		  $curdefault = '';
	 }
	 //echo $fee; // 1.55
	 //echo $taxamount; // 0.2
	 //echo $tax; // $0.20
	 //echo $fees; // $1.75 should be AU$1.68
	 //echo $topay; // AU$100.68
	 $ilance->GPC['paymethod'] = isset($ilance->GPC['paymethod']) ? $ilance->GPC['paymethod'] : '';
	 if ($methodscount == 1 AND empty($ilance->GPC['paymethod']))
	 {
		  $ilance->GPC['paymethod'] = $ilance->payment->print_payment_method_title($project_id);
	 }
	 // #### payment method pulldown #######################################
	 if (($res['filter_escrow'] == '1' AND $res['filter_offline'] == '0' AND $res['filter_gateway'] == '0' AND $res['filter_ccgateway'] == '0') OR (isset($ilance->GPC['paymethod']) AND $ilance->GPC['paymethod'] == 'escrow'))
	 {
		  $ilance->GPC['paymethod'] = 'escrow';
		  $show['sellerusingescrow'] = $show['filter_escrow'] = $show['depositlink'] = true;
		  $payment_method_pulldown = $ilance->accounting_print->print_paymethod_pulldown('account', 'account_id', $_SESSION['ilancedata']['user']['userid']);
	 }
	 else
	 {
		  $payment_method_pulldown = $ilance->payment->print_fixed_payment_method($ilance->GPC['paymethod']);
	 }
	 // #### shipping address pulldown ######################################
	 $shipping_address_pulldown = $ilance->shipping->print_shipping_address_pulldown($_SESSION['ilancedata']['user']['userid']);
	 $paymethod = isset($ilance->GPC['paymethod']) ? $ilance->GPC['paymethod'] : '';
	 $shipperid = isset($ilance->GPC['shipperid']) ? intval($ilance->GPC['shipperid']) : 0;
	 $paymethodid = isset($ilance->GPC['paymethod']) ? $ilance->GPC['paymethod'] : '';
	 $paymethod = $ilance->payment->print_fixed_payment_method($ilance->GPC['paymethod']);
	 $hiddeninput = array(
		  'cmd' => '_instant-purchase-process',
		  'project_id' => $project_id,
		  'buyer_id' => $_SESSION['ilancedata']['user']['userid'],
		  'seller_id' => $seller_id,
		  'fee' => sprintf("%01.2f", ($fee + $taxamount)),
		  'qty' => $qty,
		  'total' => sprintf("%01.2f", $total),
		  'amount' => sprintf("%01.2f", $amount),
		  'paymethod' => $ilance->GPC['paymethod'],
		  'buyershipcost' => sprintf("%01.2f", $ilance->GPC['buyershipcost']),
		  'shipperid' => $ilance->GPC['shipperid'],
		  'currencyid' => $res['currencyid'],
	 );
	 
	 ($apihook = $ilance->api('merch_purchase_confirm_append_hiddeninput')) ? eval($apihook) : false;
	 
	 $ilance->bid->bid_filter_checkup($project_id);
	 $encrypted = encrypt_url($hiddeninput);
	 $returnurl = urlencode($ilpage['merch'] . '?cmd=purchase-confirm&pid=' . intval($project_id) . '&qty=' . intval($ilance->GPC['qty']));
	 $pprint_array = array('itemlocation','curdefault','curfrom','curto','paymethodid','paymethod','shipperid','shippingservice','hiddenfields','returnurl','tax','paymethod','fees','digitalfile','cb_shipping_address_required1','cb_shipping_address_required0','encrypted','samount','amount_formatted','total','shipping_address_pulldown','forceredirect','payment_method_pulldown','attachment','project_id','seller_id','buyer_id','user_cookie','project_title','seller','qty','topay','amount','remote_addr','rid','category','subcategory');
 
	 ($apihook = $ilance->api('merch_purchase_confirm_end')) ? eval($apihook) : false;
 
	 $ilance->template->fetch('main', 'listing_forward_auction_buynow.html');
	 $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	 $ilance->template->parse_if_blocks('main');
	 $ilance->template->pprint('main', $pprint_array);
	 exit();
}
// #### ITEM CATEGORY LISTINGS #################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'listings')
{
	 if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
	 {
		$seourl = print_seo_url($ilconfig['servicecatmapidentifier']);
		$seourl = HTTP_SERVER . $seourl;
		header('Location: ' . $seourl);
		exit();
	 }
	 $cid = !empty($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
	 // if we have no children, redirect user to the appropriate result listings pages for this category
	 if (isset($cid) AND $cid > 0 AND $ilance->categories->fetch_children_ids($cid, 'product', " AND visible = '1'") == '')
	 {
		 $url = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productcatplain', $cid, 0, $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $cid), '', 0, '', 0, 0) : $ilpage['search'] . '?mode=product&cid=' . $cid;
		 header('Location: ' . $url);
		 exit();
	 }
	 // #### prevent duplicate content from search engines
	 $seoproductcategories = print_seo_url($ilconfig['productcatmapidentifier']);
	 $seoservicecategories = print_seo_url($ilconfig['servicecatmapidentifier']);
	 $seolistings = print_seo_url($ilconfig['listingsidentifier']);
	 $seocategories = print_seo_url($ilconfig['categoryidentifier']);
	 if ($ilconfig['globalauctionsettings_seourls'] AND (!isset($ilance->GPC['sef']) OR empty($ilance->GPC['sef'])))
	 {
		 $seourl = HTTP_SERVER . $seolistings;
		 header('Location: ' . $seourl);
		 exit();
	 }
	 $show['widescreen'] = false;
	 $area_title = '{_buy}<div class="smaller">{_viewing_all_categories}</div>';
	 $page_title = '{_buy} - {_viewing_all_categories} | ' . SITE_NAME;
	 // #### define top header nav ##########################################
	 $topnavlink = array(
		  'main_categories'
	 );
	 $recursivecategory = $auctioncount = $category = $description = $seeall = $popularsearch = '';
	 if (($cathtml = $ilance->cache->fetch('cathtml_' . $cid . '_product_' . $_SESSION['ilancedata']['user']['slng'])) === false)
	 {
		 $cathtml = $ilance->categories->recursive($cid, 'productcatmap', $_SESSION['ilancedata']['user']['slng'], 0, '', $ilconfig['globalauctionsettings_seourls']);
		 $ilance->cache->store('cathtml_' . $cid . '_product_' . $_SESSION['ilancedata']['user']['slng'], $cathtml);
	 }
	 $show['canpost'] = false;
	 if (!empty($cathtml))
	 {
		  $metatitle = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $cid);
		  $metadescription = $ilance->categories->description($_SESSION['ilancedata']['user']['slng'], $cid);
		  $metakeywords = $ilance->categories->keywords($_SESSION['ilancedata']['user']['slng'], $cid);
		  $metadescription = (empty($metadescription)) ? '{_find_new_and_used_items_in} ' . $metatitle : $metadescription;
		  $area_title = '{_categories}<div class="smaller">' . $metatitle . '</div>';
		  $page_title = $metadescription . ' | ' . SITE_NAME;
		  $count = $ilance->categories->auctioncount('product', $cid);
		  $auctioncount = ($ilconfig['globalfilters_enablecategorycount']) ? number_format($count) : '';
		  $show['canpost'] = ($ilance->categories->can_post($_SESSION['ilancedata']['user']['slng'], 'product', $cid)) ? true : false;
		  $show['categorycolumn'] = true;
		  unset($count);
	 }
	 $text = '{_browse_product_auctions_via_marketplace_categories}';
	 if ($cid > 0)
	 {
		  if (($featuredproductauctions = $ilance->cache->fetch('featuredproductauctions_cid_' . $cid)) === false)
		  { // featured
			  $featuredproductauctions = $ilance->auction_listing->fetch_featured_auctions('product', 20, 1, $cid, '', true);
			  $ilance->cache->store('featuredproductauctions_cid_' . $cid, $featuredproductauctions);
		  }
		  if (($productsendingsoon = $ilance->cache->fetch('productsendingsoon_cid_' . $cid)) === false)
		  { // ending soon
			  $productsendingsoon = $ilance->auction_listing->fetch_ending_soon_auctions('product', 20, 1, $cid, '', true);
			  $ilance->cache->store('productsendingsoon_cid_' . $cid, $productsendingsoon);
		  }
		  if (($categoryresults = $ilance->cache->fetch('categoryresults_4col_' . $cid . '_product_' . $ilconfig['globalfilters_enablecategorycount'] . '_' . $ilconfig['globalauctionsettings_catmapdepth'] . '_' . $_SESSION['ilancedata']['user']['slng'])) === false)
		  {
			   $categoryresults = $ilance->categories_parser->print_subcategory_columns(4, 'product', 1, $_SESSION['ilancedata']['user']['slng'], $cid, '', $ilconfig['globalfilters_enablecategorycount'], 1, 'font-weight:bold;font-size:14px;line-height:1.5', 'font-weight:normal;line-height:1.5', $ilconfig['globalauctionsettings_catmapdepth'], '', false, true);
			   $ilance->cache->store('categoryresults_4col_' . $cid . '_product_' . $ilconfig['globalfilters_enablecategorycount'] . '_' . $ilconfig['globalauctionsettings_catmapdepth'] . '_' . $_SESSION['ilancedata']['user']['slng'], $categoryresults);
		  }
		  $category = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $cid);
		  $description = $ilance->categories->description($_SESSION['ilancedata']['user']['slng'], $cid);
		  $text = $category;
		  $seeall = $ilconfig['globalauctionsettings_seourls']
			   ?((!empty($auctioncount)) ? '<span style="font-size:18px;margin-left:5px;font-weight:bold">(<span class="blue"><a href="' . construct_seo_url('productcatplain', $cid, 0, $category, '', 0, '', 0, 0) . '">' . $auctioncount . '</a></span>)</span>' : '')
			   : '<span style="font-size:18px;margin-left:5px;font-weight:bold">(<span class="blue"><a href="' . $ilpage['search'] . '?mode=product&amp;cid=' . $cid . '">' . $auctioncount . '</a></span>)</span>';
		  $navcrumb = array();
		  $ilance->categories->breadcrumb($cid, 'productcatmap', $_SESSION['ilancedata']['user']['slng']);
		  $ilance->categories->add_category_viewcount($cid);
	 }
	 else
	 {
		 if (($categoryresults = $ilance->cache->fetch('categoryresults_4col_' . $cid . '_product_' . $ilconfig['globalfilters_enablecategorycount'] . '_' . $ilconfig['globalauctionsettings_catmapdepth'] . '_' . $_SESSION['ilancedata']['user']['slng'])) === false)
		 {
			 $categoryresults = $ilance->categories_parser->print_subcategory_columns(4, 'product', 1, $_SESSION['ilancedata']['user']['slng'], $cid, '', $ilconfig['globalfilters_enablecategorycount'], 1, 'font-weight:bold;font-size:14px;line-height:1.5', 'font-weight:normal;line-height:1.5', $ilconfig['globalauctionsettings_catmapdepth'], '', false, true);
			 $ilance->cache->store('categoryresults_4col_' . $cid . '_product_' . $ilconfig['globalfilters_enablecategorycount'] . '_' . $ilconfig['globalauctionsettings_catmapdepth'] . '_' . $_SESSION['ilancedata']['user']['slng'], $categoryresults);
		 }
		 $navurl = ($ilconfig['globalauctionsettings_seourls']) ? HTTP_SERVER . $seoproductcategories : HTTP_SERVER . $ilpage['main'] . '?cmd=categories';
		 $navcrumb = array();
		 $navcrumb[""] = '{_buy}';
		 unset($navurl);
	 }
	 $pprint_array = array('seeall','popularcategories','recentlyviewed','popularsearch','seoservicecategories','seoproductcategories','seolistings','seocategories','search_category_pulldown','description','text','categorypulldown','recursivecategory','category','cid','php_self','categoryresults','three_column_subcategory_results','category','number','prevnext','keywords','search_country_pulldown','search_jobtype_pulldown','five_last_keywords_buynow','five_last_keywords_projects','five_last_keywords_providers','search_ratingrange_pulldown','search_awardrange_pulldown','search_bidrange_pulldown','search_listed_pulldown','search_closing_pulldown','search_category_pulldown','distance','subcategory_name','text','prevnext','prevnext2');
	 $ilance->template->fetch('main', 'merch_listings.html');
	 $ilance->template->parse_loop('main', 'featuredproductauctions');
	 $ilance->template->parse_loop('main', 'productsendingsoon');
	 $ilance->template->parse_loop('main', 'recentlyviewed');
	 $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	 $ilance->template->parse_if_blocks('main');
	 $ilance->template->pprint('main', $pprint_array);
	 exit();
}

// #### PRODUCT AUCTION CATEGORY LISTINGS VIA CATEGORY ID ######################
else if (!empty($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0 AND empty($ilance->GPC['cmd']))
{
	 $ilance->categories->add_category_viewcount(intval($ilance->GPC['cid']));
	 $ilance->categories->build_array('product', $_SESSION['ilancedata']['user']['slng'], 0, false);
	 if ($ilance->categories->can_post($_SESSION['ilancedata']['user']['slng'], 'product', intval($ilance->GPC['cid'])))
	 {
		  $urlbit = print_hidden_fields(true, array(), false);
		  header('Location: ' . $ilpage['search'] . '?mode=product' . $urlbit);
		  exit();
	 }
	 $urlbit = print_hidden_fields(true, array('cid'), false);
	 header('Location: ' . $ilpage['merch'] . '?cmd=listings&cid=' . intval($ilance->GPC['cid']) . $urlbit);
	 exit();
}
// #### ITEM REVISION LOG ######################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'revisionlog' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	 if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
	 {
		 refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['merch'] . print_hidden_fields(true, array(), true)));
		 exit();
	 }
	 $show['widescreen'] = true;
	 $id = isset($ilance->GPC['id']) ? intval($ilance->GPC['id']) : 0;
	 $returnurl = $ilpage['merch'] . '?id=' . $id;
	 $sql = $ilance->db->query("
		  SELECT r.datetime, r.changelog, p.project_title
		  FROM " . DB_PREFIX . "projects_changelog r
		  LEFT JOIN " . DB_PREFIX . "projects p ON (p.project_id = r.project_id)
		  WHERE r.project_id = '" . intval($id) . "'
		  ORDER BY r.id DESC
	 ", 0, null, __FILE__, __LINE__);
	 if ($ilance->db->num_rows($sql) > 0)
	 {
		  $show['revision'] = true;
		  $row_count = 0;
		  while ($rows = $ilance->db->fetch_array($sql))
		  {
			   $project_title = $rows['project_title'];
			   $rows['datetime'] = print_date($rows['datetime'], $ilconfig['globalserverlocale_globaltimeformat'], 1, 1);
			   $rows['info'] = stripslashes($rows['changelog']);
			   $rows['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			   $revisions[] = $rows;
			   $row_count++;
		  }
	 }
	 else
	 {
		  $project_title = handle_input_keywords(fetch_auction('project_title', $id));
		  $show['revision'] = false;
	 }
	 $page_title = SITE_NAME . ' - {_listing_revision_details}';
	 $area_title = '{_listing_revision_details}';
	 $navcrumb = array();
	 $navcrumb["$ilpage[merch]?id=" . $id] = $project_title; // todo: convert to seo
	 $navcrumb[""] = '{_revision_log}';
	 $ilance->template->fetch('main', 'listing_revision_log.html');
	 $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	 $ilance->template->parse_loop('main', 'revisions');
	 $ilance->template->parse_if_blocks('main');
	 $ilance->template->pprint('main', array('returnurl','project_title'));
	 exit();
}
// #### ITEM BIDDING LOG ######################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'bidlog' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	 if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
	 {
		 refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['merch'] . print_hidden_fields(true, array(), true)));
		 exit();
	 }
	 $page_title = SITE_NAME . ' - {_bid_history}';
	 $area_title = '{_bid_history}';
	 $show['widescreen'] = true;
	 $id = isset($ilance->GPC['id']) ? intval($ilance->GPC['id']) : 0;
	 $returnurl = $ilpage['merch'] . '?id=' . $id; // todo: convert to seo
	 $result = $ilance->db->query("
		 SELECT b.bid_id, b.user_id, b.project_id, b.project_user_id, b.proposal, b.bidamount, b.estimate_days, b.date_added AS bidadded, b.date_updated, b.date_awarded, b.bidstatus, b.bidstate, b.qty, p.project_id, p.escrow_id, p.cid, p.description, p.date_added, p.buynow_qty, p.date_end, p.user_id, p.views, p.project_title, p.bids, p.additional_info, p.status, p.close_date, p.project_details, p.project_type, p.bid_details, p.currencyid, p.startprice, p.date_starts, u.user_id, u.username, u.city, u.state, u.zip_code, u.feedback, u.score
		 FROM " . DB_PREFIX . "project_bids AS b,
		 " . DB_PREFIX . "projects AS p,
		 " . DB_PREFIX . "users AS u
		 WHERE b.project_id = '" . intval($id) . "'
			   AND b.project_id = p.project_id
			   AND u.user_id = b.user_id
			   AND b.bidstatus != 'declined'
			   AND b.bidstate != 'retracted'
		 ORDER by b.bidamount DESC, b.date_added ASC
	 ", 0, null, __FILE__, __LINE__);
	 if ($ilance->db->num_rows($result) > 0)
	 {
		  $row_count = 0;
		  while ($resbids = $ilance->db->fetch_array($result, DB_ASSOC))
		  {
			   $resbids['bid_datetime'] = print_date($resbids['bidadded'], 'M-d-y h:i:s', 1, 0);
			   $date_starts = print_date($resbids['date_starts'], 'M-d-y h:i:s', 1, 0);
			   $startprice = $ilance->currency->format($resbids['startprice'], $resbids['currencyid']);
			   $project_title = handle_input_keywords($resbids['project_title']);
			   $resbids['provider'] = fetch_user('username', $resbids['user_id']);
			   if ($resbids['bid_details'] == 'open' AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] != $resbids['user_id'])
			   {
				   $resbids['bidamount'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $resbids['bidamount'], $resbids['currencyid']);
			   }
			   else if ($resbids['bid_details'] == 'open' AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $resbids['user_id'])
			   {
				    if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'enablecurrencyconversion') == 'yes')
				    {
					     $resbids['bidamount'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $resbids['bidamount'], $resbids['currencyid']);
				    }
				    else
				    {
					     $resbids['bidamount'] = $ilance->currency->format($resbids['bidamount'], $resbids['currencyid']);
				    }
			   }
			   else if ($resbids['bid_details'] == 'sealed' AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] != $resbids['user_id'] AND $_SESSION['ilancedata']['user']['userid'] != $resbids['project_user_id'])
			   {
				    $resbids['bidamount'] = '<span style="text-transform:uppercase">= {_sealed} =</span>';
			   }
			   else if ($resbids['bid_details'] == 'sealed' AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $resbids['user_id'])
			   {
				    if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'enablecurrencyconversion') == 'yes')
				    {
					    $resbids['bidamount'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $resbids['bidamount'], $resbids['currencyid']);
				    }
				    else
				    {
					    $resbids['bidamount'] = $ilance->currency->format($resbids['bidamount'], $res['currencyid']);
				    }
			   }
			   else if ($resbids['bid_details'] == 'sealed' AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $resbids['project_user_id'])
			   {
				    $resbids['bidamount'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $resbids['bidamount'], $resbids['currencyid']);
			   }
			   else
			   {
				    $resbids['bidamount'] = $ilance->currency->format($resbids['bidamount'], $resbids['currencyid']);
			   }
			   if ($resbids['bidstatus'] == 'awarded' AND $resbids['status'] != 'open')
			   {
				    $awarded_vendor = handle_input_keywords($resbids['username']);
				    $resbids['bidamount'] = '<strong>' . $resbids['bidamount'] . '</strong>';
			   }
			   if (!empty($resbids['proposal']))
			   {
				    // proxy bid
				    $resbids['class'] = 'alt1'; //'featured_highlight';
				    $resbids['fontweight'] = 'normal';
				    $resbids['fontclass'] = 'litegray';
				    $resbids['provider'] = $resbids['provider'];
				    $resbids['bidamount'] = $resbids['bidamount'];
				    $resbids['bid_datetime'] = $resbids['bid_datetime'];
			   }
			   else
			   {
				    // user bid
				    $resbids['class'] = ($row_count % 2) ? 'alt1' : 'alt1';
				    $resbids['fontweight'] = 'normal';
				    $resbids['fontclass'] = 'black';
			   }
			   $resbids['bid_provider'] = (isset($ilconfig['productbid_displaybidname']) AND ($ilconfig['productbid_displaybidname'])) ? $resbids['provider'] : ((!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $resbids['user_id']) ? $resbids['provider'] : '*****') . ' (' . $resbids['feedback'] . '% / ' . $resbids['score'] . ')';
			   $row_count++;
			   if ($row_count == 1)
			   {
				    $resbids['fontweight'] = 'bold';
			   }
			   $bids[] = $resbids;
		  }
		  $navcrumb = array();
		  $navcrumb["$ilpage[merch]?id=" . $id] = $project_title; // todo: convert to seo
		  $navcrumb[""] = '{_bid_history}';
		  $ilance->template->fetch('main', 'listing_bid_log.html');
		  $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		  $ilance->template->parse_loop('main', 'bids');
		  $ilance->template->parse_if_blocks('main');
		  $ilance->template->pprint('main', array('returnurl','date_starts','startprice','project_title'));
		  exit();
	 }
	 else
	 {
		 refresh($returnurl);
		 exit();
	 }
}
// #### ITEM SALE LOG ######################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'salelog' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	 if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
	 {
		 refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['merch'] . print_hidden_fields(true, array(), true)));
		 exit();
	 }
	 $page_title = SITE_NAME . ' - {_purchase_history}';
	 $area_title = '{_purchase_history}';
	 $show['widescreen'] = true;
	 $id = isset($ilance->GPC['id']) ? intval($ilance->GPC['id']) : 0;
	 $project_title = handle_input_keywords(fetch_auction('project_title', $id));
	 $returnurl = $ilpage['merch'] . '?id=' . $id; // todo: convert to seo
	 $result_orders = $ilance->db->query("
		SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "orderid, project_id, buyer_id, owner_id, invoiceid, attachid, qty, amount, originalcurrencyid, escrowfee, escrowfeebuyer, fvf, fvfbuyer, isescrowfeepaid, isescrowfeebuyerpaid, isfvfpaid, isfvfbuyerpaid, escrowfeeinvoiceid, escrowfeebuyerinvoiceid, fvfinvoiceid, fvfbuyerinvoiceid, ship_required, ship_location, orderdate, canceldate, arrivedate, paiddate, releasedate, winnermarkedaspaidmethod, winnermarkedaspaiddate, winnermarkedaspaid, buyerpaymethod, sellermarkedasshipped, sellermarkedasshippeddate, buyershipperid, buyershipcost, buyerfeedback, sellerfeedback, status
		FROM " . DB_PREFIX . "buynow_orders
		WHERE project_id = '" . $id . "'
		ORDER BY orderid DESC
	 ");
	 if ($ilance->db->num_rows($result_orders) > 0)
	 {
		  $order_count = 0;
		  while ($orderrows = $ilance->db->fetch_array($result_orders, DB_ASSOC))
		  {
			   $orderrows['buyer'] = fetch_user('username', $orderrows['buyer_id']);
			   $orderrows['feedback'] = fetch_user('feedback', $orderrows['buyer_id']);
			   $orderrows['score'] = fetch_user('score', $orderrows['buyer_id']);
			   $orderrows['buyer'] = (isset($ilconfig['productbid_displaybidname']) AND ($ilconfig['productbid_displaybidname'])) ? $orderrows['buyer'] : ((!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $orderrows['buyer_id']) ? $orderrows['buyer'] : '*****') . ' (' . $orderrows['feedback'] . '% / ' . $orderrows['score'] . ')';
			   $orderrows['price'] = $ilance->currency->format((($orderrows['amount'] - $orderrows['buyershipcost']) / $orderrows['qty']), $orderrows['originalcurrencyid']);
			   $orderrows['orderdate'] = print_date($orderrows['orderdate'], 'M-d-y h:i:s', 1, 0);
			   $orderrows['orderqty'] = $orderrows['qty'];
			   $orderrows['buyershipcost'] = (($orderrows['buyershipcost'] > 0) ? $orderrows['buyershipcost'] : 0);
			   if ($orderrows['buyershipcost'] > 0)
			   {
				    $orderrows['shippingcost'] = '<div class="litegray" style="padding-top:4px">+' . $ilance->currency->format($orderrows['buyershipcost'], $orderrows['originalcurrencyid']) . '</div>';
			   }
			   else
			   {
				    $orderrows['shippingcost'] = '';
			   }
			   $orderrows['class'] = ($order_count % 2) ? 'alt1' : 'alt1';
			   $orderrows['fontclass'] = 'black';
			   $orderrows['fontweight'] = 'normal';
			   $sales[] = $orderrows;
			   $order_count++;
		  }
		  $navcrumb = array();
		  $navcrumb["$ilpage[merch]?id=" . $id] = $project_title; // todo: convert to seo
		  $navcrumb[""] = '{_purchase_history}';
		  $ilance->template->fetch('main', 'listing_sale_log.html');
		  $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		  $ilance->template->parse_loop('main', 'sales');
		  $ilance->template->parse_if_blocks('main');
		  $ilance->template->pprint('main', array('returnurl','project_title'));
		  exit();
	 }
	 else
	 {
		  refresh($returnurl);
		  exit();
	 }
}
// #### OTHER ##################################################################
else
{
	 $jsinclude['footer'][] = 'listing';
	 include_once(DIR_SERVER_ROOT . 'merch_viewitem' . ((defined('TEMPLATE_NEWUI') AND defined('TEMPLATE_NEWUI_MODE') AND TEMPLATE_NEWUI AND TEMPLATE_NEWUI_MODE != '') ? '_product' : '') . '.php');
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>