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
if (!defined('LOCATION') OR defined('LOCATION') != 'selling')
{
	die('<strong>Fatal:</strong> This script cannot be parsed indirectly.');
}
// #### define top header nav ##########################################
$topnavlink = array(
	'mycp',
	'selling'
);
// #### default to widescreen template hack ############################
$show['widescreen'] = false;
// #### set-up web page meta tag information ###########################
$area_title = '{_selling_activity_menu}';
$page_title = SITE_NAME . ' - {_selling_activity_menu}';
// #### set-up default breadcrumb bit ##################################
$navcrumb = array();
$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
$navcrumb[""] = '{_selling_activity}';
$number = $number2 = $number3 = $number4 = $counter = $counter2 = $counter3 = $counter4 = 0;
$ilance->GPC['page']  = (!isset($ilance->GPC['page'])  OR isset($ilance->GPC['page'])  AND $ilance->GPC['page'] <= 0)  ? 1 : intval($ilance->GPC['page']);
$ilance->GPC['p2'] = (!isset($ilance->GPC['p2']) OR isset($ilance->GPC['p2']) AND $ilance->GPC['p2'] <= 0) ? 1 : intval($ilance->GPC['p2']);
$ilance->GPC['p3'] = (!isset($ilance->GPC['p3']) OR isset($ilance->GPC['p3']) AND $ilance->GPC['p3'] <= 0) ? 1 : intval($ilance->GPC['p3']);
$ilance->GPC['p4'] = (!isset($ilance->GPC['p4']) OR isset($ilance->GPC['p4']) AND $ilance->GPC['p4'] <= 0) ? 1 : intval($ilance->GPC['p4']);
$counter3 = ($ilance->GPC['p3'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
$counter4 = ($ilance->GPC['p4'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
$ilance->GPC['orderby'] = (isset($ilance->GPC['orderby']) ? $ilance->GPC['orderby'] : 'date_end');
$ilance->GPC['displayorder'] = (isset($ilance->GPC['displayorder']) ? $ilance->GPC['displayorder'] : 'desc');
$ilance->GPC['pp'] = (isset($ilance->GPC['pp']) ? intval($ilance->GPC['pp']) : $ilconfig['globalfilters_maxrowsdisplay']);
$ilconfig['globalfilters_maxrowsdisplay'] = (isset($ilance->GPC['pp']) ? intval($ilance->GPC['pp']) : $ilconfig['globalfilters_maxrowsdisplay']);
$ilance->GPC['pics'] = (isset($ilance->GPC['pics']) ? intval($ilance->GPC['pics']) : '1');
$orderby = 'ORDER BY b.bid_id DESC';
$orderby2 = 'ORDER BY p.project_id DESC';
// #### require some backend power
require_once(DIR_CORE . 'functions_search.php');
require_once(DIR_CORE . 'functions_tabs.php');
// #### listing period logic
$ilance->GPC['period'] = (isset($ilance->GPC['period']) ? intval($ilance->GPC['period']) : -1);
$periodsql = fetch_startend_sql($ilance->GPC['period'], 'DATE_SUB', 'date_added', '>=');
$period_options = array(
	'-1' => '{_any_date}',
	'1' => '{_last_hour}',
	'6' => '{_last_12_hours}',
	'7' => '{_last_24_hours}',
	'13' => '{_last_7_days}',
	'14' => '{_last_14_days}',
	'15' => '{_last_30_days}',
	'16' => '{_last_60_days}',
	'17' => '{_last_90_days}');
$orderby_options = array(
	'project_title' => '{_title}',
	'date_added' => '{_date_added}',
	'date_end' => '{_date_ending}',
	'bids' => '{_bids}',
	'insertionfee' => '{_insertion_fee}',
	'buynow_purchases' => '{_purchases}',
	'buynow_qty' => '{_qty}',
	'bids,buynow_purchases' => '{_bids}, {_purchases}',
	'buynow_purchases,bids' => '{_purchases}, {_bids}');

($apihook = $ilance->api('selling_activity_product_pulldown_options')) ? eval($apihook) : false;

$period_pulldown = construct_pulldown('period_pull_id', 'period', $period_options, $ilance->GPC['period'], 'class="smaller" style="font-family: verdana"');
$orderby_pulldown = construct_pulldown('orderby_pull_id', 'orderby', $orderby_options, $ilance->GPC['orderby'], 'class="smaller" style="font-family: verdana"');
$pics_pulldown = construct_pulldown('pics_pull_id', 'pics', array('1' => '{_include_pictures}', '0' => '{_exclude_pictures}'), $ilance->GPC['pics'], 'class="smaller" style="font-family: verdana"');
$displayorder_pulldown = construct_pulldown('displayorder_pull_id', 'displayorder', array('desc' => '{_descending}', 'asc' => '{_ascending}'), $ilance->GPC['displayorder'], 'class="smaller" style="font-family: verdana"');
$pp_pulldown = construct_pulldown('pp_pull_id', 'pp', array('10' => '10', '50' => '50', '100' => '100', '500' => '500', '1000' => '1000'), $ilance->GPC['pp'], 'class="smaller" style="font-family: verdana"');
unset($period_options, $orderby_options);

($apihook = $ilance->api('selling_activity_top_start')) ? eval($apihook) : false;

if (isset($ilance->GPC['sub']) AND $ilance->GPC['sub'] == 'expired' AND !isset($ilance->GPC['orderby']) )
{
    $ilance->GPC['orderby'] = 'date_end';
}
$extra = '&amp;period=' . intval($ilance->GPC['period']);
$extra .= (!empty($ilance->GPC['sub'])) ? '&amp;sub=' . $ilance->GPC['sub'] : '';
// #### display order defaults 
$ilance->GPC['displayorder'] = isset($ilance->GPC['displayorder']) ? $ilance->GPC['displayorder'] : 'desc';
$displayorder = $currentdisplayorder = '&amp;displayorder=desc';
$displayordersql = 'desc';
if (isset($ilance->GPC['displayorder']) AND $ilance->GPC['displayorder'] == 'asc')
{
	$displayorder = '&amp;displayorder=desc';
	$currentdisplayorder = '&amp;displayorder=asc';
}
else if (isset($ilance->GPC['displayorder']) AND $ilance->GPC['displayorder'] == 'desc')
{
	$displayorder = '&amp;displayorder=asc';
	$currentdisplayorder = '&amp;displayorder=desc';
}
$displayorderfields = array('asc', 'desc');
if (isset($ilance->GPC['displayorder']) AND in_array($ilance->GPC['displayorder'], $displayorderfields))
{
	$displayordersql = mb_strtoupper($ilance->GPC['displayorder']);
}
// #### order by fields defaults #######################################
$orderbyfields = array('project_title', 'date_added', 'date_end', 'bids', 'insertionfee', 'buynow_purchases', 'buynow_qty', 'bids,buynow_purchases', 'buynow_purchases,bids');

($apihook = $ilance->api('selling_activity_product_orderbyfields_array')) ? eval($apihook) : false;

if (isset($ilance->GPC['orderby']) AND in_array($ilance->GPC['orderby'], $orderbyfields))
{
	$ilance->GPC['orderby'] = mb_strtolower($ilance->GPC['orderby']);
	$orderbysql = $ilance->GPC['orderby'];
	if (stristr($orderbysql, ','))
	{
		$orderbytmp = explode(',', $orderbysql);
		$orderbysql = '';
		foreach ($orderbytmp AS $orderfield)
		{
			$orderbysql .= "$orderfield " . mb_strtoupper($displayordersql) . ", ";
		}
		unset($orderbytmp);
		$orderbysql = substr($orderbysql, 0, -2);
	}
	else
	{
		$orderbysql = "$orderbysql " . mb_strtoupper($displayordersql);        
	}
}
else
{
	$ilance->GPC['orderby'] = 'bids,buynow_purchases';
	$orderbysql = $ilance->GPC['orderby'];
	if (stristr($orderbysql, ','))
	{
		$orderbytmp = explode(',', $orderbysql);
		$orderbysql = '';
		foreach ($orderbytmp AS $orderfield)
		{
			$orderbysql .= "$orderfield " . mb_strtoupper($displayordersql) . ", ";
		}
		unset($orderbytmp);
		$orderbysql = substr($orderbysql, 0, -2);
	}
	else
	{
		$orderbysql = "$orderbysql " . mb_strtoupper($displayordersql);        
	}
}
$orderby = '&amp;orderby=' . $ilance->GPC['orderby'];
// #### database ordering and limit logic ##############################
$pp = (isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] >= 0)  ? intval($ilance->GPC['pp']) : $ilconfig['globalfilters_maxrowsdisplay'];
$limit  = 'ORDER BY ' . $orderbysql . ' LIMIT ' . (($ilance->GPC['page'] - 1) * $pp) . ',' . $pp;
$limit2 = 'LIMIT ' . (($ilance->GPC['p2'] - 1) * $pp) . ',' . $pp;
$limit4 = 'ORDER BY b.status DESC LIMIT ' . (($ilance->GPC['p4'] - 1) * $pp) . ',' . $pp;
// #### used within templates ##########################################
$php_self = $ilpage['selling'] . '?cmd=management' . $displayorder . $extra;
$keyw = isset($ilance->GPC['keyw']) ? $ilance->common->xss_clean(handle_input_keywords($ilance->GPC['keyw'])) : '';
$keywx = '&keyw=' . $keyw;
$scriptpage = $ilpage['selling'] . '?cmd=management' . $currentdisplayorder . $orderby . $extra . $keywx;
// #### does admin enable product auction support?
if ($show['product_selling_activity'])
{
	($apihook = $ilance->api('selling_activity_bidsub_condition_start')) ? eval($apihook) : false;

	$ilance->GPC['sub'] = isset($ilance->GPC['sub']) ? $ilance->GPC['sub'] : '';
	$keyw_sell = (isset($ilance->GPC['search']) AND ($ilance->GPC['search'] == 'sell')) ? $keyw : '' ;
	// #### DELISTED AUCTION RESULTS ###############################
	if (isset($ilance->GPC['sub']) AND $ilance->GPC['sub'] == 'delisted')
	{
		// #### build our breadcrumb nav #######################
		$navcrumb = array();
		$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
		$navcrumb["$ilpage[selling]?cmd=management"] = '{_selling_activity}';
		$navcrumb[""] = '{_delisted_items}';
		$sub_delisted = 1;
		
		// #### build our sql statements ###############
		$SQL = $ilance->auction_tabs->product_auction_tab_sql('delisted', 'string', $_SESSION['ilancedata']['user']['userid'], $periodsql, $keyw_sell) . " " . $limit;
		$SQL2 = $ilance->auction_tabs->product_auction_tab_sql('delisted', 'string', $_SESSION['ilancedata']['user']['userid'], $periodsql, $keyw_sell);
		
		// #### build our selling activity tabs ########
		$producttabs = print_selling_activity_tabs('delisted', 'product', $_SESSION['ilancedata']['user']['userid'], $periodsql);
	}
	// #### ARCHIVED AUCTION RESULTS ###############################
	else if (isset($ilance->GPC['sub']) AND $ilance->GPC['sub'] == 'archived')
	{
		// #### build our breadcrumb nav #######################
		$navcrumb = array();
		$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
		$navcrumb["$ilpage[selling]?cmd=management"] = '{_selling_activity}';
		$navcrumb[""] = '{_archived_items}';
		$sub_archived = 1;
		
		// #### build our sql statements ###############
		$SQL = $ilance->auction_tabs->product_auction_tab_sql('archived', 'string', $_SESSION['ilancedata']['user']['userid'], $periodsql, $keyw_sell) . " " . $limit;
		$SQL2 = $ilance->auction_tabs->product_auction_tab_sql('archived', 'string', $_SESSION['ilancedata']['user']['userid'], $periodsql, $keyw_sell);
		
		// #### build our selling activity tabs ########
		$producttabs = print_selling_activity_tabs('archived', 'product', $_SESSION['ilancedata']['user']['userid'], $periodsql);
	}
	// #### EXPIRED AUCTION RESULTS ################################
	else if (isset($ilance->GPC['sub']) AND $ilance->GPC['sub'] == 'expired')
	{
		// #### build our breadcrumb nav #######################
		$navcrumb = array();
		$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
		$navcrumb["$ilpage[selling]?cmd=management"] = '{_selling_activity}';
		$navcrumb[""] = '{_expired_items}';
		$sub_expired = 1;
		
		// #### build our sql statements ###############
		$SQL2 = $ilance->auction_tabs->product_auction_tab_sql('expired', 'string', $_SESSION['ilancedata']['user']['userid'], $periodsql, $keyw_sell);
		$SQL = $SQL2 . " " . $limit;			
		
		// #### build our selling activity tabs ########
		$producttabs = print_selling_activity_tabs('expired', 'product', $_SESSION['ilancedata']['user']['userid'], $periodsql);
	}
	// #### DRAFTS AUCTION RESULTS #################################
	else if (isset($ilance->GPC['sub']) AND $ilance->GPC['sub'] == 'drafts')
	{
		// #### process and open selected draft items in inventory
		if (isset($ilance->GPC['rfpcmd']) AND $ilance->GPC['rfpcmd'] == 'createitem')
		{
			$ilance->categories->build_array($cattype = 'product', $_SESSION['ilancedata']['user']['slng'], $categorymode = 0, $propersort = false);
			if (isset($ilance->GPC['rfp']) AND is_array($ilance->GPC['rfp']))
			{
				foreach ($ilance->GPC['rfp'] AS $key => $value)
				{
					if ($ilconfig['moderationsystem_disableauctionmoderation'])
					{
						$sql = $ilance->db->query("
							SELECT user_id, cid, project_title, description, project_state, project_details, bid_details, date_starts, date_end,  bold, highlite, featured, autorelist, buynow, buynow_price, reserve, featured, currencyid, autorelist, description_videourl, startprice, reserve_price, buynow_qty, reserve_price, UNIX_TIMESTAMP('" . DATETIME24H . "') - UNIX_TIMESTAMP(date_added) AS seconds
							FROM " . DB_PREFIX . "projects
							WHERE project_id = '" . intval($value) . "'
						");
						if ($ilance->db->num_rows($sql) > 0)
						{
							$res = $ilance->db->fetch_array($sql, DB_ASSOC);
							$cid1 = $res['cid'];
							// seconds that have past since the listing was posted
							$secondspast = $res['seconds'];
							// fetch the new future date end based on elapsed seconds
							$sqltime = $ilance->db->query("SELECT DATE_ADD('$res[date_end]', INTERVAL $secondspast SECOND) AS new_date_end");
							$restime = $ilance->db->fetch_array($sqltime, DB_ASSOC);
							$category_name = $ilance->categories->title(fetch_site_slng(),$res['cid']);
							// new date end 
							$new_date_end = $restime['new_date_end'];
							$datenow = DATETIME24H;
							// email admin
							$ilance->email->mail = SITE_EMAIL;
							$ilance->email->slng = fetch_site_slng();
							$ilance->email->get('product_auction_posted_admin');		
							$ilance->email->set(array(
								'{{buyer}}' => fetch_user('username', $res['user_id']),
								'{{project_title}}' => $res['project_title'],
								'{{description}}' => $res['description'],
								'{{bids}}' => '0',
								'{{category}}' => $category_name,
								'{{minimum_bid}}' => $ilance->currency->format($res['startprice'], $res['currencyid']),
								'{{p_id}}' => $value,
								'{{details}}' => ucfirst($res['project_details']),
								'{{privacy}}' => ucfirst($res['bid_details']),
								'{{closing_date}}' => print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
							));
							$ilance->email->send();								
							$enhancements_invoices = $ilance->db->query("SELECT invoiceid FROM " . DB_PREFIX . "invoices WHERE projectid = '" . $value . "' AND isenhancementfee = '1'");
							$count_enhancements_invoices = $ilance->db->num_rows($enhancements_invoices);
							if ($count_enhancements_invoices == 0)
							{
								if ($res['featured'])
								{
									$ilance->GPC['enhancements']['featured'] = 1;
								}
								if ($res['highlite'])
								{
									$ilance->GPC['enhancements']['highlite'] = 1;
								}
								if ($res['bold'])
								{
									$ilance->GPC['enhancements']['bold'] = 1;
								}
								if ($res['autorelist'])
								{
									$ilance->GPC['enhancements']['autorelist'] = 1;
								}
								if ($res['buynow'] > 0)
								{
									$ilance->GPC['enhancements']['buynow'] = 1;
								}				
								if ($res['reserve'] > 0)
								{
								   $ilance->GPC['enhancements']['reserve'] = 1;
								} 
								$ilance->GPC['description_videourl']  = $res['description_videourl'];
								$ilance->GPC['enhancements'] = (!empty($ilance->GPC['enhancements']) ? $ilance->GPC['enhancements'] : array());
								$enhance = $ilance->auction_fee->process_listing_enhancements_transaction($ilance->GPC['enhancements'], $_SESSION['ilancedata']['user']['userid'], intval($value), 'insert', 'product');
							}
							$insertion_invoices = $ilance->db->query("SELECT invoiceid FROM " . DB_PREFIX . "invoices WHERE projectid = '" . $value . "' AND isif = '1'");
							$count_insertion_invoices = $ilance->db->num_rows($insertion_invoices);
							if ($count_insertion_invoices == 0)
							{		
								// #### INSERTION FEES IN THIS CATEGORY ################
								// this will generate insertion fee to be paid by the auction owner before listing is live
								// if seller has no funds in their account the auction will go into pending auction queue
								$start_price = $res['startprice'];
								$reserve  = $res['reserve'];
								$reserve_price  = $res['reserve_price'];
								$buynow  = $res['buynow'];
								$buynow_price  = $res['buynow_price'];
								$buynow_qty  = $res['buynow_qty'];
								$ifbaseamount = 0;
								if ($start_price > 0)
								{
									$ifbaseamount = $start_price;
									if ($reserve AND $reserve_price > 0)
									{
										if ($reserve_price > $start_price)
										{
											$ifbaseamount = $reserve_price;
										}
									}
								}
								// if seller is supplying a buy now price, check to see if it's higher than our current
								// insertion fee amount, if so, use this value for the insertion fee base amount
								if ($buynow AND $buynow_price > 0 AND $buynow_qty > 0)
								{
									$totalbuynow = ($buynow_price * $buynow_qty);
									if ($totalbuynow > $ifbaseamount)
									{
										$ifbaseamount = $totalbuynow;
									}
								}
								$insertion = $ilance->auction_fee->process_insertion_fee_transaction($res['cid'], 'product', $ifbaseamount, $value, $res['user_id'], 0, 0, false, array(), intval($res['currencyid']));                            
							}
							if ($res['project_details'] == 'realtime')
							{
								if ($datenow > $res['date_starts'])
								{
									$new_date_start = $datenow;
								}
								else
								{
									$new_date_start = $res['date_starts'];	
								}
							}
							else
							{
								$new_date_start = DATETIME24H;
							}
							// set auction to open state
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "projects
								SET status = 'open',
								visible = '1',
								date_starts = '" . $ilance->db->escape_string($new_date_start) . "',
								date_end = '" . $ilance->db->escape_string($new_date_end) . "'				
								WHERE project_id = '" . intval($value) . "'
									AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
									AND status = 'draft'
							", 0, null, __FILE__, __LINE__);
							
							($apihook = $ilance->api('seller_draft_action_validate_foreach')) ? eval($apihook) : false;
							
							// rebuild category count
							$ilance->categories->build_category_count($cid1, 'add', "post item draft listing public: adding increment count category id $cid1");
							$ilance->email->mail = $_SESSION['ilancedata']['user']['email'];
							$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
							$ilance->email->get('new_product_auction_open_for_bids');		
							$ilance->email->set(array(
								'{{username}}' => $_SESSION['ilancedata']['user']['username'],
								'{{projectname}}' => strip_tags($res['project_title']),
								'{{description}}' => strip_tags($res['description']),
								'{{bids}}' => '0',
								'{{category}}' => $ilance->categories->recursive($cid1, 'product', $_SESSION['ilancedata']['user']['slng'], 1, '', 0),
								'{{budget}}' => $ilance->auction_rfp->construct_budget_overview($cid1, fetch_auction('filtered_budgetid', intval($value))),
								'{{p_id}}' => intval($value),
								'{{details}}' => ucfirst($res['project_details']),
								'{{privacy}}' => ucfirst($res['bid_details']),
								'{{closing_date}}' => print_date(fetch_auction('date_end', intval($value)), $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
							));
							$ilance->email->send();
							// did this seller manually enter email addresses to invite users to bid on this item?
							$ilance->auction_rfp->dispatch_external_members_email('product', fetch_auction('project_id', intval($value)), $_SESSION['ilancedata']['user']['userid'], strip_tags(fetch_auction('project_title', intval($value))), fetch_auction('bid_details', intval($value)), fetch_auction('date_end', intval($value)), '', '', $skipemailprocess = 0);
							// #### REFERRAL SYSTEM TRACKER ############################
							$ilance->referral->update_referral_action('postauction', $_SESSION['ilancedata']['user']['userid']);
						}
					}
					else
					{
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "projects
							SET status = 'open',
							visible = '0',
							date_starts = '" . DATETIME24H . "'
							WHERE project_id = '" . intval($value) . "'
								AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
								AND status = 'draft'
						", 0, null, __FILE__, __LINE__);	
					}
				}
			}
			if ($ilconfig['moderationsystem_disableauctionmoderation'])
			{
				// moderation disabled
				$area_title = '{_new_forward_auctions_posted_menu}';
				$page_title = SITE_NAME . ' - {_new_forward_auctions_posted_menu}';
				$url = '';
				$pprint_array = array('url','session_project_title','session_description','session_additional_info','session_budget','country_pulldown','category','subcategory','filehash','max_filesize','attachment_style','user_id','state','catid','subcatid','currency','datetime_now','project_id','category_id');
				$ilance->template->fetch('main', 'listing_forward_auction_complete.html');
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->pprint('main', $pprint_array);
				exit();
			}
			else
			{
				// show auction under moderation notice
				$area_title = '{_new_forward_auctions_posted_menu}';
				$page_title = SITE_NAME . ' - {_new_forward_auctions_posted_menu}';
				$ilance->email->mail = SITE_EMAIL;
				$ilance->email->slng = fetch_site_slng();
				$ilance->email->get('new_auction_pending_moderation');		
				$ilance->email->set(array());
				$ilance->email->send();
				$url = '<a href="' . HTTP_SERVER . $ilpage['selling'] . '?cmd=management&amp;sub=rfp-pending"><strong>{_pending_auctions_menu}</strong></a>';
				$pprint_array = array('url','session_project_title','session_description','session_additional_info','session_budget','country_pulldown','category','subcategory','filehash','max_filesize','attachment_style','user_id','state','catid','subcatid','currency','datetime_now','project_id','category_id');
				$ilance->template->fetch('main', 'listing_forward_auction_moderation.html');
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->pprint('main', $pprint_array);
				exit();
			} 						
		}
		// #### process and open all draft items in inventory
		else if (isset($ilance->GPC['rfpcmd']) AND $ilance->GPC['rfpcmd'] == 'createallitem')
		{
			$ilance->categories->build_array('product', $_SESSION['ilancedata']['user']['slng'], 0, false);
			$sql = $ilance->db->query("
				SELECT project_id, user_id, cid, project_title, description, project_state, project_details, bid_details, date_starts, date_end, bold, highlite, featured, autorelist, buynow, reserve, featured, autorelist, buynow_price, description_videourl, startprice, reserve_price, buynow_qty, reserve_price, currencyid, UNIX_TIMESTAMP('" . DATETIME24H . "') - UNIX_TIMESTAMP(date_added) AS seconds
				FROM " . DB_PREFIX . "projects
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND status = 'draft'
				ORDER BY id ASC
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$i = 0;
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					if ($ilconfig['moderationsystem_disableauctionmoderation'])
					{
						$cid1 = $res['cid'];
						// seconds that have past since the listing was posted
						$secondspast = $res['seconds'];
						// fetch the new future date end based on elapsed seconds
						$sqltime = $ilance->db->query("
							SELECT DATE_ADD('$res[date_end]', INTERVAL $secondspast SECOND) AS new_date_end
						");
						$restime = $ilance->db->fetch_array($sqltime, DB_ASSOC);
						// new date end 
						$new_date_end = $restime['new_date_end'];
						$datenow = DATETIME24H;
						if ($res['project_details'] == 'realtime')
						{
							if ($datenow > $res['date_starts'])
							{
								$new_date_start = $datenow;
							}
							else
							{
								$new_date_start = $res['date_starts'];	
							}
						}
						else
						{
							$new_date_start = DATETIME24H;
						}
						$category_name = $ilance->categories->title(fetch_site_slng(),$res['cid']);
						$ilance->email->mail = SITE_EMAIL;
						$ilance->email->slng = fetch_site_slng();
						$ilance->email->get('product_auction_posted_admin');		
						$ilance->email->set(array(
							'{{buyer}}' => fetch_user('username', $res['user_id']),
							'{{project_title}}' => $res['project_title'],
							'{{description}}' => $res['description'],
							'{{bids}}' => '0',
							'{{category}}' => $category_name,
							'{{minimum_bid}}' => $ilance->currency->format($res['startprice'], $res['currencyid']),
							'{{p_id}}' => $res['project_id'],
							'{{details}}' => ucfirst($res['project_details']),
							'{{privacy}}' => ucfirst($res['bid_details']),
							'{{closing_date}}' => print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
						));
						$ilance->email->send();				
						$ilance->email->mail = fetch_user('email', $res['user_id']);
						$ilance->email->slng = fetch_user_slng($res['user_id']);
						$ilance->email->get('new_product_auction_open_for_bids');		
						$ilance->email->set(array(
							'{{username}}' => fetch_user('username', $res['user_id']),
							'{{projectname}}' => stripslashes($res['project_title']),
							'{{description}}' => $res['description'],
							'{{category}}' => $category_name,
							'{{p_id}}' => $res['project_id'],
							'{{closing_date}}' => print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
							
						));
						$ilance->email->send();							
						$enhancements_invoices = $ilance->db->query("SELECT invoiceid FROM " . DB_PREFIX . "invoices WHERE projectid = '" . $res['project_id'] . "' AND isenhancementfee = '1'");
						$count_enhancements_invoices = $ilance->db->num_rows($enhancements_invoices);
						if($count_enhancements_invoices == 0)
						{
							if ($res['featured'])
							{
								$ilance->GPC['enhancements']['featured'] = 1;
							}
							if ($res['highlite'])
							{
								$ilance->GPC['enhancements']['highlite'] = 1;
							}
							if ($res['bold'])
							{
								$ilance->GPC['enhancements']['bold'] = 1;
							}
							if ($res['autorelist'])
							{
								$ilance->GPC['enhancements']['autorelist'] = 1;
							}
							if ($res['buynow'] > 0)
							{
								$ilance->GPC['enhancements']['buynow'] = 1;
							}
							if ($res['reserve'] > 0)
							{
								$ilance->GPC['enhancements']['reserve'] = 1;
							}
							$ilance->GPC['description_videourl']  = $res['description_videourl'];
							$ilance->GPC['enhancements'] = (!empty($ilance->GPC['enhancements']) ? $ilance->GPC['enhancements'] : array());
							$enhance = $ilance->auction_fee->process_listing_enhancements_transaction($ilance->GPC['enhancements'], $_SESSION['ilancedata']['user']['userid'], intval($res['project_id']), 'insert', 'product');
						}
						$insertion_invoices = $ilance->db->query("SELECT invoiceid FROM " . DB_PREFIX . "invoices WHERE projectid = '" . $res['project_id'] . "' AND isif = '1'");
						$count_insertion_invoices = $ilance->db->num_rows($insertion_invoices);
						if ($count_insertion_invoices == 0)
						{		
							// #### INSERTION FEES IN THIS CATEGORY ################
							// this will generate insertion fee to be paid by the auction owner before listing is live
							// if seller has no funds in their account the auction will go into pending auction queue
							$start_price = $res['startprice'];
							$reserve  = $res['reserve'];
							$reserve_price  = $res['reserve_price'];
							$buynow  = $res['buynow'];
							$buynow_price  = $res['buynow_price'];
							$buynow_qty  = $res['buynow_qty'];
							$ifbaseamount = 0;
							if ($start_price > 0)
							{
								$ifbaseamount = $start_price;
								if ($reserve AND $reserve_price > 0)
								{
									if ($reserve_price > $start_price)
									{
										$ifbaseamount = $reserve_price;
									}
								}
							}
							// if seller is supplying a buy now price, check to see if it's higher than our current
							// insertion fee amount, if so, use this value for the insertion fee base amount
							if ($buynow AND $buynow_price > 0 AND $buynow_qty > 0)
							{
								$totalbuynow = ($buynow_price * $buynow_qty);
								if ($totalbuynow > $ifbaseamount)
								{
									$ifbaseamount = $totalbuynow;
								}
							}
							$insertion = $ilance->auction_fee->process_insertion_fee_transaction($res['cid'], 'product', $ifbaseamount, $res['project_id'], $res['user_id'], 0, 0, false, array(), intval($res['currencyid']));                            
						}
						// set auction to open state
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "projects
							SET status = 'open',
							visible = '1',
							date_starts = '" . $ilance->db->escape_string($new_date_start) . "',
							date_end = '" . $ilance->db->escape_string($new_date_end) . "'				
							WHERE project_id = '" . $res['project_id'] . "'
								AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
								AND status = 'draft'
						", 0, null, __FILE__, __LINE__);
						// rebuild category count
						$ilance->categories->build_category_count($cid1, 'add', "post item draft listing public: adding increment count category id $cid1");
						// did this seller manually enter email addresses to invite users to bid on their items?
						$ilance->auction_rfp->dispatch_external_members_email('product', $res['project_id'], $_SESSION['ilancedata']['user']['userid'], strip_tags(fetch_auction('project_title', $res['project_id'])), fetch_auction('bid_details', $res['project_id']), fetch_auction('date_end', $res['project_id']), '', '', $skipemailprocess = 0);
						// #### REFERRAL SYSTEM TRACKER ############################
						$ilance->referral->update_referral_action('postauction', $_SESSION['ilancedata']['user']['userid']);
					}
					else
					{
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "projects
							SET status = 'open',
							date_starts = '" . DATETIME24H . "',
							visible = '0'
							WHERE project_id = '" . $res['project_id'] . "'
						", 0, null, __FILE__, __LINE__);
					}
					$i++;
				}
			}
		}
		// #### delete selected draft items in bulk ############
		else if (isset($ilance->GPC['rfpcmd']) AND $ilance->GPC['rfpcmd'] == 'deleteitem')
		{
			// use delist function..
			if (isset($ilance->GPC['rfp']) AND is_array($ilance->GPC['rfp']) AND count($ilance->GPC['rfp']) > 0)
			{
				foreach ($ilance->GPC['rfp'] AS $key => $value)
				{
					$sql = $ilance->db->query("
						SELECT project_id
						FROM " . DB_PREFIX . "projects
						WHERE visible = '1'
							AND project_id = '" . intval($value) . "'
							AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
							AND status = 'draft'
					");
					if ($ilance->db->num_rows($sql) == 1)
					{
						while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
						{
							$ilance->common_listing->physically_remove_listing(intval($res['project_id']));
						}
					}
				}
				print_notice('{_action_completed}', '{_the_selected_listings_were_removed}', $ilpage['selling'] . '?cmd=management&sub=drafts', '{_return_to_the_previous_menu}');
				exit();	
			}
		}
		// #### delete all draft items in bulk #################
		else if (isset($ilance->GPC['rfpcmd']) AND $ilance->GPC['rfpcmd'] == 'deleteallitem')
		{
			$sql = $ilance->db->query("
				SELECT project_id
				FROM " . DB_PREFIX . "projects
				WHERE visible = '1'
					AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND status = 'draft'
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					$ilance->common_listing->physically_remove_listing(intval($res['project_id']));
				}
				print_notice('{_action_completed}', '{_the_selected_listings_were_removed}', $ilpage['selling'] . '?cmd=management&sub=drafts', '{_return_to_the_previous_menu}');
				exit();	
			}
		}
		// #### build our breadcrumb nav ###############
		$navcrumb = array();
		$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
		$navcrumb["$ilpage[selling]?cmd=management"] = '{_selling_activity}';
		$navcrumb[""] = '{_draft_auctions}';
		$sub_drafts = 1;
		// #### build our sql statements ###############
		$SQL = $ilance->auction_tabs->product_auction_tab_sql('drafts', 'string', $_SESSION['ilancedata']['user']['userid'], $periodsql, $keyw) . " " . $limit;
		$SQL2 = $ilance->auction_tabs->product_auction_tab_sql('drafts', 'string', $_SESSION['ilancedata']['user']['userid'], $periodsql, $keyw);
		// #### build our selling activity tabs ########
		$producttabs = print_selling_activity_tabs('drafts', 'product', $_SESSION['ilancedata']['user']['userid'], $periodsql);
		$ilance->categories->build_array($cattype = 'product', $_SESSION['ilancedata']['user']['slng'], $categorymode = 0, $propersort = false);
		$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
		$limit = ' ORDER BY p.date_added DESC LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
		$ilance->GPC['period'] = (isset($ilance->GPC['period']) ? intval($ilance->GPC['period']) : -1);
		$periodsql = fetch_startend_sql($ilance->GPC['period'], 'DATE_SUB', 'p.date_added', '>=');
		$extra = '&amp;period=' . $ilance->GPC['period'];
		$servicetabs = print_selling_activity_tabs('drafts', 'product', $_SESSION['ilancedata']['user']['userid'], $periodsql);
		$numberrows = $ilance->db->query("
			SELECT p.*, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime
			FROM " . DB_PREFIX . "projects AS p
			WHERE p.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				$periodsql
				AND p.status = 'draft'
				AND p.visible = '1'
				AND p.project_state = 'product' AND p.project_title like '%" . $ilance->db->escape_string($keyw) . "%'
		", 0, null, __FILE__, __LINE__);
		$number = $ilance->db->num_rows($numberrows);
		$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
		$area_title = '{_draft_rfps}';
		$page_title = SITE_NAME . ' - {_draft_rfps}';
		$navcrumb = array();
		$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
		$navcrumb["$ilpage[selling]?cmd=management"] = '{_buying_activity}';
		$navcrumb[""] = '{_draft_auctions}';
		$condition = $condition2 = '';
		$row_count = 0;
		$result = $ilance->db->query("
			SELECT p.*, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime
			FROM " . DB_PREFIX . "projects AS p
			WHERE p.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			$periodsql
				AND p.status = 'draft'
				AND p.visible = '1'
				AND p.project_state = 'product' AND p.project_title like '%" . $ilance->db->escape_string($keyw) . "%'
			$limit
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($result) > 0)
		{
			while ($row = $ilance->db->fetch_array($result))
			{
				// check for auction attachments
				$row['attach'] = '-';                                
				$sql_attachments = $ilance->db->query("
					SELECT attachid, filename, filehash
					FROM " . DB_PREFIX . "attachment
					WHERE project_id = '" . $row['project_id'] . "'
						AND user_id = '" . $row['user_id'] . "'
						AND visible = '1'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql_attachments) > 0)
				{
					while ($res = $ilance->db->fetch_array($sql_attachments, DB_ASSOC))
					{
						$row['attach'] .= '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'paperclip.gif"><span class="smaller"><a href="' . $ilpage['attachment'] . '?id=' . $res['filehash'] . '" target="_blank">' . $res['filename'] . '</a></span> ';
					}
				}
				$row['added'] = print_date($row['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				$row['starts'] = print_date($row['date_starts'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				$row['ends'] = print_date($row['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				$row['job_title'] = print_string_wrap(stripslashes($row['project_title']), '45');
				$row['type'] = ucfirst($row['project_state']);
				$row['state'] = $row['project_state'];
				$row['description'] = short_string(stripslashes($row['description']), 100);				
				$row['category'] = stripslashes($ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $row['cid']));
				$row['actions'] = '<input type="checkbox" name="rfp[]" value="'.$row['project_id'].'" id="'.$row['project_state'].'_'.$row['project_id'].'" />';
				$row['status'] = '{_pending}';
				$row['revisions'] = $row['updateid'];
				$row['invitecount'] = $ilance->auction_rfp->fetch_invited_users_count($row['project_id']);
				if ($row['insertionfee'] > 0 AND $row['ifinvoiceid'] > 0)
				{
					$row['insfee'] = ($row['isifpaid'])
						? '<div class="smaller blue"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['ifinvoiceid'] . '">(' . $ilance->currency->format($row['insertionfee']) . ')</a></div>'
						: '<div class="smaller red"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['ifinvoiceid'] . '">(' . $ilance->currency->format($row['insertionfee']) . ')</a></div>';
				}
				else
				{
					$row['insfee'] = '-';
				}
				$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				$project_results_rows[] = $row;
				$row_count++;
			}
			$show['no_project_rows_returned'] = false;
			$show['rfppulldownmenu'] = true;
		}
		else
		{
			$show['no_project_rows_returned'] = true;
			$show['rfppulldownmenu'] = false;
		}
		$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $ilpage['selling'] . '?cmd=management&amp;sub=drafts&amp;keyw=' . $keyw);
		$ilance->template->fetch('main', 'selling_drafts.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', 'project_results_rows');
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', array('servicetabs','prevnext','keyw','period_pulldown','displayorder_pulldown','pp_pulldown'));
		exit();
	}
	// #### PENDING AUCTION RESULTS ################################
	else if (isset($ilance->GPC['sub']) AND $ilance->GPC['sub'] == 'pending')
	{
		// #### build our breadcrumb navigation ########
		$navcrumb = array();
		$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
		$navcrumb["$ilpage[selling]?cmd=management"] = '{_selling_activity}';
		$navcrumb[""] = '{_pending_items}';
		$sub_pending = 1;
		// #### build our sql statements ###############
		$SQL = $ilance->auction_tabs->product_auction_tab_sql('pending', 'string', $_SESSION['ilancedata']['user']['userid'], $periodsql, $keyw_sell) . " " . $limit;
		$SQL2 = $ilance->auction_tabs->product_auction_tab_sql('pending', 'string', $_SESSION['ilancedata']['user']['userid'], $periodsql, $keyw_sell);
		// #### build our selling activity tabs ########
		$producttabs = print_selling_activity_tabs('pending', 'product', $_SESSION['ilancedata']['user']['userid'], $periodsql);
	}
	// #### ITEMS I'VE SOLD ########################################
	else if (isset($ilance->GPC['sub']) AND $ilance->GPC['sub'] == 'sold')
	{
		// #### define top header nav ##########################
		$topnavlink = array(
			'mycp',
			'selling_sold'
		);
		// #### build our nav crumb ############################
		$navcrumb = array();
		$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
		$navcrumb["$ilpage[selling]?cmd=management"] = '{_selling_activity}';
		$navcrumb[""] = '{_items_ive_sold}';
		$sub_sold = 1;
		// #### build our sql statements #######################
		$SQL = $ilance->auction_tabs->product_auction_tab_sql('sold', 'string', $_SESSION['ilancedata']['user']['userid'], $periodsql, $keyw_sell) . " " . $limit;
		$SQL2 = $ilance->auction_tabs->product_auction_tab_sql('sold', 'string', $_SESSION['ilancedata']['user']['userid'], $periodsql, $keyw_sell);
		// #### build our selling activity tabs ################
		$producttabs = print_selling_activity_tabs('sold', 'product', $_SESSION['ilancedata']['user']['userid'], $periodsql);
	}
	// #### ACTIVE AUCTION RESULTS #################################
	else
	{
		$sub_active = 1;
		// #### build our sql statements ###############
		$SQL = $ilance->auction_tabs->product_auction_tab_sql('active', 'string', $_SESSION['ilancedata']['user']['userid'], $periodsql, $keyw_sell) . " " . $limit;
		$SQL2 = $ilance->auction_tabs->product_auction_tab_sql('active', 'string', $_SESSION['ilancedata']['user']['userid'], $periodsql, $keyw_sell);
		// #### build our selling activity tabs ########
		$producttabs = print_selling_activity_tabs('active', 'product', $_SESSION['ilancedata']['user']['userid'], $periodsql);
	}
	$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
	$row_count = $fvfs = 0;
	$numberrows = $ilance->db->query($SQL2, 0, null, __FILE__, __LINE__);
	$number = $ilance->db->num_rows($numberrows);
	$result = $ilance->db->query($SQL, 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($result) > 0)
	{
		while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
		{
			// #### checkbox action- archived, delisted and drafts tabs do not have action control
			if (isset($sub_delisted) AND $sub_delisted)
			{
				$disabled = ' disabled="disabled"';
			}
			else if (isset($sub_archived) AND $sub_archived)
			{
				$disabled = ($ilconfig['globalauctionsettings_deletearchivedlistings']) ? '' : ' disabled="disabled"';
			}
			else 
			{
				$disabled = '';
			}
			$row['actions'] = '<input type="checkbox" name="rfp[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '"' . $disabled . ' />';
			$row['auctionbit'] = '<span class="gray">' . $ilance->auction->print_auction_bit($row['project_id'], $row['filtered_auctiontype'], $row['project_details'], $row['project_state'], $row['buynow'], $row['reserve'], $row['cid']) . '</span>';
			$row['winningbidsbit'] = '';
			$row['reserveprice'] = ($row['reserve']) ? $ilance->currency->format($row['reserve_price'], $row['currencyid']) : '-';	
			$row['sales'] = $row['buynow_purchases'];
			$row['icons'] = $ilance->auction->auction_icons($row);
			$row['photo'] = $ilance->auction->print_item_photo($ilpage['merch'] . '?id=' . $row['project_id'], 'thumb', $row['project_id'], 1);
			$row['item_title'] = print_string_wrap(stripslashes($row['project_title']), '45');
			$row['pricex'] = (fetch_auction('filtered_auctiontype', $row['project_id']) == 'fixed') ? $ilance->currency->format($row['buynow_price'], $row['currencyid']) : $ilance->currency->format($row['startprice'], $row['currencyid']);
			$watchers = $ilance->db->query("
				SELECT watchlistid
				FROM " . DB_PREFIX . "watchlist
				WHERE watching_project_id = '" . $row['project_id'] . "'
			", 0, null, __FILE__, __LINE__);
			$row['viewx'] = '<div class="smaller">' . $row['views'] . ' {_views_lower}</div><div style="padding-top:3px" class="smaller">' . $ilance->db->num_rows($watchers) . ' {_watching_lower}</div>';
			// #### auction format #########################
			if ($row['filtered_auctiontype'] == 'regular' AND $row['buynow'] == '1')
			{				  
				$row['formatx'] = '{_fixed_price} + {_auction}';
			}
			else if ($row['filtered_auctiontype'] == 'regular' AND $row['buynow'] == '0')
			{
				$row['formatx'] = '{_auction}';
			}
			else
			{				  
				$row['formatx'] = '{_fixed_price}';
			}
			$iteminfo = '';
			if (!empty($row['sku']))
			{
				$iteminfo .= '<div style="padding-top:3px" class="smaller"><strong>{_sku}:</strong> ' . handle_input_keywords($row['sku']) . '</div>';
			}
			if (!empty($row['upc']))
			{
				$iteminfo .= '<div style="padding-top:3px" class="smaller"><strong>{_upc}:</strong> ' . handle_input_keywords($row['upc']) . '</div>';
			}
			if (!empty($row['ean']))
			{
				$iteminfo .= '<div style="padding-top:3px" class="smaller"><strong>{_ean}:</strong> ' . handle_input_keywords($row['ean']) . '</div>';
			}
			if (!empty($row['partnumber']))
			{
				$iteminfo .= '<div style="padding-top:3px" class="smaller"><strong>{_part_number}:</strong> ' . handle_input_keywords($row['partnumber']) . '</div>';
			}
			if (!empty($row['modelnumber']))
			{
				$iteminfo .= '<div style="padding-top:3px" class="smaller"><strong>{_model_number}:</strong> ' . handle_input_keywords($row['modelnumber']) . '</div>';
			}
			$row['iteminfo'] = $iteminfo;
			unset($iteminfo);
			
			($apihook = $ilance->api('selling_activity_infobit')) ? eval($apihook) : false;
			
			$row['timeleft'] = ($row['haswinner'] == '1' OR $row['status'] == 'expired' OR $row['buynow'] AND $row['buynow_qty'] == '0') ? '<span class="black">{_ended}</span>' : $ilance->auction->calculate_time_left($row['date_starts'], $row['starttime'], $row['mytime']);
			$row['insfee'] = (($row['insertionfee'] > 0)
				? ($row['isifpaid'])
					? '<div class="smaller" style="padding-top:3px"><strong><span onmouseover="Tip(phrase[\'_if_stands_for_insertion_fee_and_is_a_fee_that_is_applied_when_you_list\'], BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()">{_if_short}:</span></strong> <span class="blue"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['ifinvoiceid'] . '">' . $ilance->currency->format($row['insertionfee']) . '</a></span></div>'
					: '<div class="smaller" style="padding-top:3px"><strong><span onmouseover="Tip(phrase[\'_if_stands_for_insertion_fee_and_is_a_fee_that_is_applied_when_you_list\'], BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()">{_if_short}:</span></strong> <span class="red"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['ifinvoiceid'] . '">' . $ilance->currency->format($row['insertionfee']) . '</a></span></div>'
				: '');
			$row['insfeepay'] = '';	
			if (isset($ilance->GPC['sub']) AND $ilance->GPC['sub'] == 'pending')
			{
				if ($row['insertionfee'] > 0 AND $row['enhancementfee'] > 0)
				{
					if ($row['isifpaid'] == 0 OR $row['isenhancementfeepaid'] == 0)
					{
						$row['insfeepay'] = '<a href="' . HTTPS_SERVER . $ilpage['accounting'] .'?cmd=transactions&amp;subcmd=directmasspayment&amp;projectid='.$row['project_id'].'" class="buttons"><strong>{_pay_now}</strong></a>';
					}
				}
				else if ($row['insertionfee'] == 0 AND $row['enhancementfee'] > 0)
				{
					if($row['isenhancementfeepaid'] == 0)
					{
						$row['insfeepay'] = '<a href="' . HTTPS_SERVER . $ilpage['accounting'] .'?cmd=transactions&amp;subcmd=directmasspayment&amp;projectid='.$row['project_id'].'" class="buttons"><strong>{_pay_now}</strong></a>';
					}
				}
				else if($row['enhancementfee'] == 0 AND $row['insertionfee'] > 0)
				{
					if($row['isifpaid'] == 0)
					{
						$row['insfeepay'] = '<a href="' . HTTPS_SERVER . $ilpage['accounting'] .'?cmd=transactions&amp;subcmd=directmasspayment&amp;projectid='.$row['project_id'].'" class="buttons"><strong>{_pay_now}</strong></a>';
					}
				}
			}
			// #### show template conditional for escrow activity
			$GLOBALS['show_escrow' . $row['project_id']] = ($ilconfig['escrowsystem_enabled'] AND $row['filter_escrow'] == '1') ? 1 : 0;
			// #### purchase now order activity
			if ($row['buynow_price'] > 0)
			{
				$GLOBALS['show_purchase_now' . $row['project_id']] = ($row['sales'] != '0') ? 1 : 0;
			}
			else
			{
				$GLOBALS['show_purchase_now' . $row['project_id']] = 0;
			}
			$GLOBALS['show_regular_bid' . $row['project_id']] = ($row['bids'] > 0) ? 1 : 0;
			// #### highest bidders logic
			$highbidamount = '0.00';
			$highbidder = $highbidderid = '';
			$row['highbidamountx'] = '';
			$sql_highest_bidder = $ilance->db->query("
				SELECT user_id
				FROM " . DB_PREFIX . "project_bids
				WHERE project_id = '" . $row['project_id'] . "'
				ORDER BY bidamount DESC, date_added ASC
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_highest_bidder) > 0)
			{
				$res_highest_bidder = $ilance->db->fetch_array($sql_highest_bidder, DB_ASSOC);
				$sel_bids_av = $ilance->db->query("
					SELECT AVG(bidamount) AS average, MIN(bidamount) AS lowest, MAX(bidamount) AS highest
					FROM " . DB_PREFIX . "project_bids
					WHERE project_id = '" . $row['project_id'] . "'
						AND bidstate != 'retracted'
						AND bidstatus != 'declined'
					ORDER BY highest
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sel_bids_av) > 0)
				{
					$res_bids_av = $ilance->db->fetch_array($sel_bids_av, DB_ASSOC);
					$row['highbidderid'] = $res_highest_bidder['user_id'];
					$row['highbidder'] = print_username($row['highbidderid'], 'href', 1, '', '');
					$highbidderid = $row['highbidderid'];
					$highbidamount = $res_bids_av['highest'];
					if ($row['status'] == 'open')
					{
						$row['highbidamountx'] = '<span class="smaller black" title="{_current_bid}">' . $ilance->currency->format($res_bids_av['highest'], $row['currencyid']) . '</span>';
					}
					else
					{
						if (isset($ilance->GPC['sub']) AND $ilance->GPC['sub'] == 'sold')
						{
							$row['highbidamountx'] = '<span class="smaller black" title="{_ending_bid}"><strong>' . $ilance->currency->format($res_bids_av['highest'], $row['currencyid']) . '</strong></span>';
						}
						else
						{
							$row['highbidamountx'] = '<span class="smaller black" title="{_highest_bid}"><strong>' . $ilance->currency->format($res_bids_av['highest'], $row['currencyid']) . '</strong></span>';
						}
					}
				}
			}
			// #### purchase now order tab activity for this expanded auction
			$sqlbit = (isset($ilance->GPC['cancelled']) AND $ilance->GPC['cancelled']) ? '' : "AND status != 'cancelled'";
			$result_orders = $ilance->db->query("
				SELECT orderid, project_id, buyer_id, owner_id, invoiceid, attachid, qty, amount, escrowfee, escrowfeebuyer, fvf, fvfbuyer, isescrowfeepaid, isescrowfeebuyerpaid, isfvfpaid, isfvfbuyerpaid, escrowfeeinvoiceid, escrowfeebuyerinvoiceid, fvfinvoiceid, fvfbuyerinvoiceid, ship_required, ship_location, orderdate, canceldate, arrivedate, paiddate, releasedate, winnermarkedaspaidmethod, winnermarkedaspaiddate, winnermarkedaspaid, buyerpaymethod, sellermarkedasshipped, sellermarkedasshippeddate, buyershipperid, buyershipcost, buyerfeedback, sellerfeedback, status, shiptracknumber, originalcurrencyid, originalcurrencyidrate, convertedtocurrencyid, convertedtocurrencyidrate
				FROM " . DB_PREFIX . "buynow_orders
				WHERE owner_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND project_id = '" . $row['project_id'] . "'
					$sqlbit
				ORDER BY orderid DESC
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($result_orders) > 0)
			{
				$order_count = 0;
				while ($orderrows = $ilance->db->fetch_array($result_orders, DB_ASSOC))
				{
					$currencyid = $ilconfig['globalserverlocale_defaultcurrency'];
					$orderrows['orderbuyer'] = fetch_user('username', $orderrows['buyer_id']);
					$orderrows['orderphone'] = fetch_user('phone', $orderrows['buyer_id']);
					$orderrows['orderemail'] = fetch_user('email', $orderrows['buyer_id']);
					$res2['amount'] = stripslashes($ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . intval($orderrows['invoiceid']) . "'", "amount"));
					if ($orderrows['status'] == 'delivered')
					{
						$orderamount = $res2['amount'];    
						$orderamount = $orderrows['amount'] = $res2['amount'] - $orderrows['buyershipcost']; 
					}
					else
					{
						$orderamount = $orderrows['amount'];
						$orderamount = $orderrows['amount'] = $orderrows['amount'] - $orderrows['buyershipcost'];
					}
					$orderrows['total'] = $orderrows['orderamount'] =  $ilance->currency->format($orderamount, $currencyid);
					$orderrows['total_plain'] = $ilance->currency->format($orderamount, $currencyid, false, false, false);
					$escrowamount = $orderrows['amount'];
					$orderrows['escrowamount'] = '<div class="smaller">' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $escrowamount, $ilconfig['globalserverlocale_defaultcurrency']) . '</div>';
					$orderrows['orderdate'] = print_date($orderrows['orderdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
					$orderrows['orderqty'] = $orderrows['qty'];
					$orderrows['orderinvoiceid'] = $orderrows['invoiceid'];
					$orderrows['buyerpaymethod'] = $ilance->payment->print_fixed_payment_method($orderrows['buyerpaymethod'], false);
					$orderrows['buynow_qty_lot'] = fetch_auction('buynow_qty_lot', $orderrows['project_id']);
					if ($orderrows['buynow_qty_lot'] == '1')
					{
						$row['qtylot'] = '{_in} {_lot}';	
					}
					else
					{
						$row['qtylot'] = '';
					}
					// #### seller escrow fee
					if ($orderrows['escrowfee'] > 0 AND $orderrows['isescrowfeepaid'] == 1 AND $orderrows['escrowfeeinvoiceid'] > 0)
					{
						$orderrows['escrowfee'] = '<div class="smaller blue"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $orderrows['escrowfeeinvoiceid'] . '">' . $ilance->currency->format($orderrows['escrowfee']) . '</a></div>';
					}
					else
					{
						$orderrows['escrowfee'] = (($orderrows['escrowfee'] > 0 AND $orderrows['isescrowfeepaid'] == 0 AND $orderrows['escrowfeeinvoiceid'] > 0)
							? '<div class="smaller red"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $orderrows['escrowfeeinvoiceid'] . '">' . $ilance->currency->format($orderrows['escrowfee']) . '</a></div>'
							: '-');
					}
					// ##### donation details for buy-now orders
					$projectrows = array();
					$project_orders = $ilance->db->query("
						SELECT charityid, donation, donationpercentage, donermarkedaspaid, donermarkedaspaiddate, donationinvoiceid 
						FROM " . DB_PREFIX . "projects 
					        WHERE project_id = '" . $orderrows['project_id'] . "'
					", 0, null, __FILE__, __LINE__);
					$projectrows = $ilance->db->fetch_array($project_orders, DB_ASSOC);
					if ($projectrows['charityid'] > 0 AND $ilconfig['enablenonprofits'] AND $projectrows['donation'] > 0)
					{
						$charity = fetch_charity_details($projectrows['charityid']);
						$show['show_donation_row' . $orderrows['project_id'] . '_' . $orderrows['orderid']] = true;
						$orderrows['donationpayment2'] = ($projectrows['donermarkedaspaid'] AND $projectrows['donermarkedaspaiddate'] != '0000-00-00 00:00:00')
							? '<span title="Donation payment marked as paid on ' . print_date($projectrows['donermarkedaspaiddate']) . '"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $projectrows['donationinvoiceid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" /></a></span>'
							: '<span title="{_pay_now}"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $projectrows['donationinvoiceid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy_gray.gif" border="0" /></a></span>';
						$orderrows['nonprofits2'] = '<span title="{_nonprofit}: ' . handle_input_keywords($charity['title']) . ' - ' . $projectrows['donationpercentage'] . '%"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/nonprofits.png" border="0" /></span>';
					}
					else
					{
						$show['show_donation_row' . $orderrows['project_id'] . '_' . $orderrows['orderid']] = false;
						$orderrows['donationpayment2'] = '<span title="{_not_in_use}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy_gray.gif" border="0" /></span>';
						$orderrows['nonprofits2'] = '<span title="{_not_in_use}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/nonprofits_litegray.png" border="0" /></span>';
					}
					// #### final value fee ########
					if ($orderrows['fvf'] > 0 AND $orderrows['isfvfpaid'] == 1 AND $orderrows['fvfinvoiceid'] > 0)
					{
						$orderrows['sellerfvf'] = '<span class="smaller blue"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $orderrows['fvfinvoiceid'] . '">' . $ilance->currency->format($orderrows['fvf']) . '</a></span>';
						$fvfs += $orderrows['fvf'];
					}
					else
					{
						$orderrows['sellerfvf'] = (($orderrows['fvf'] > 0 AND $orderrows['isfvfpaid'] == 0 AND $orderrows['fvfinvoiceid'] > 0)
							? '<span class="smaller red"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $orderrows['fvfinvoiceid'] . '">' . $ilance->currency->format($orderrows['fvf']) . '</a></span>'
							: '-');
							
						$fvfs += $orderrows['fvf'];
					}
					$orderrows['salestax'] = '';
					$orderrows['paystatus2'] = '<span title="{_not_in_use}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy_gray.gif" border="0" alt="" /></span>';
					$orderrows['shipstatus2'] = '<span title="{_not_in_use}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox_litegray.png" border="0" alt="" /></span>';
					$orderrows['escrowtotal2'] = '<span title="{_not_in_use}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/escrow_litegray.png" border="0" alt="" /></span>';
					$primaryaction = '';
					$feedbackaction2 = '';
					$paymentactions2 = '';
					$orderactions2 = '';
					$shippingactions2 = '';
					$leftfeedback = 0;
					if ($ilance->feedback->has_left_feedback($orderrows['buyer_id'], $orderrows['owner_id'], $orderrows['project_id'], 'buyer', $orderrows['orderid']))
					{
						$leftfeedback = 1;
						$orderrows['feedback2'] = '<span title="{_feedback_left}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_complete.gif" border="0" alt="" /></span>';
					}
					else
					{
						$orderrows['feedback2'] =  '<span title="{_feedback_not_left}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback.gif" border="0" alt="" /></span>';
						$feedbackaction2 = '<span title="{_submit_feedback_for} ' . fetch_user('username', $orderrows['buyer_id']) . '" class="blueonly"><a href="' . HTTP_SERVER . $ilpage['feedback'] . '?cmd=_leave-feedback&amp;view=2&amp;returnurl={pageurl_urlencoded}&amp;pid=' . $orderrows['project_id'] . '#' . $orderrows['project_id'] . '_' . $_SESSION['ilancedata']['user']['userid'] . '_' . $orderrows['buyer_id'] . '_buyer' . $orderrows['orderid'] . '">{_leave_feedback}</a></span>';
					}
					if ($ilance->feedback->has_left_feedback($orderrows['owner_id'], $orderrows['buyer_id'], $orderrows['project_id'], 'seller', $orderrows['orderid']))
					{
						$orderrows['feedbackreceived2'] = '<div><span title="{_feedback_received}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_received.gif" border="0" alt="" /></span></div>';
					}
					else
					{
						$orderrows['feedbackreceived2'] = '<div><span title="{_feedback_not_received}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_received_litegray.gif" border="0" alt="" /></span></div>';
					}
					// #### buy now order status
					if ($orderrows['status'] == 'paid')
					{
						$show['escrowcolumn']['project_id'] = 1;
						$orderrows['shipping'] = '';
						$orderrows['escrowtotal2'] = '<span title="{_funds_secured_in_escrow}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/escrow_green.png" border="0" alt="" /></span>';
						$orderrows['paystatus2'] = '<span title="{_escrow_account_funded_on} ' . print_date($orderrows['winnermarkedaspaiddate']) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" /></span>';
						$orderrows['pmb2'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $orderrows['buyer_id'], $orderrows['project_id']);
						$orderrows['work2'] = $ilance->auction->construct_mediashare_icon($orderrows['buyer_id'], $_SESSION['ilancedata']['user']['userid'], $orderrows['project_id'], $active = true);
						if ($orderrows['buyershipperid'] > 0)
						{
							$shiptemp = $ilance->shipping->fetch_ship_cost_by_shipperid($orderrows['project_id'], $orderrows['buyershipperid'], $orderrows['qty'], $orderrows['buyershipcost']);
							$orderrows['buyershipcost'] = $shiptemp['total'];
							if ($orderrows['buyershipcost'] > 0)
							{
								$orderrows['shipping'] = '<span title="{_shipping}">+ ' . $ilance->currency->format($orderrows['buyershipcost'], $currencyid) . '</span>';
							}
							if ($orderrows['sellermarkedasshipped'] AND $orderrows['sellermarkedasshippeddate'] != '0000-00-00 00:00:00')
							{
								$orderrows['shipstatus2'] = '<div><span title="{_marked_as_shipped_on} ' . print_date($orderrows['sellermarkedasshippeddate']) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox.png" border="0" alt="" /></span></div>';
								$crypted = array(
									'cmd' => 'management',
									'subcmd' => 'markasunshipped',
									'mode' => 'buynow',
									'pid' => $orderrows['project_id'],
									'sellerid' => $_SESSION['ilancedata']['user']['userid'],
									'buyerid' => $orderrows['buyer_id'],
									'bid' => $orderrows['orderid']
								);
								$date1split = explode(' ', $orderrows['sellermarkedasshippeddate']);
								$date2split = explode('-', $date1split[0]);
								$totaldays = 14;
								$elapsed = $ilance->datetimes->fetch_days_between($date2split[1], $date2split[2], $date2split[0], date('m'), date('d'), date('Y'));
								$daysleft = ($totaldays - $elapsed);
								if ($elapsed <= 14)
								{
									$shippingactions2 = '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
										<td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['selling'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}">{_mark_as_unshipped}</a></span></div></td>
									</tr>';
								}
								unset($crypted, $date1split, $date2split, $totaldays, $elapsed, $daysleft);
							}
							else
							{
								if ($orderrows['buyershipperid'] > 0)
								{
									$orderrows['shipstatus2'] = '<span title="{_the_seller_has_not_yet_marked_your_shipment_as_delivered}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox_litegray.png" border="0" alt="" /></span>';
									$crypted = array(
										'cmd' => 'management',
										'sub' => 'buynow-escrow',
										'subcmd' => '_confirm-delivery',
										'id' => $orderrows['orderid']
									);
									if (empty($primaryaction))
									{
										$primaryaction = '<div style="padding-bottom:3px" class="blue"><a href="javascript:void(0)" onclick="if (confirm_js(phrase[\'_confirm_delivery_the_items_have_already_been_shipped_to_buyers_location_additionally_js\'])) location.href=\'' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}\'"><strong>{_mark_as_shipped}</strong></a></div>';
									}
									else
									{
										$paymentactions2 = '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
											<td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $orderrows['project_id'] . '&amp;oid=' . $orderrows['orderid'] . '&amp;returnurl={pageurl_urlencoded}">{_view_order_details}</a></span></div></td>
										</tr>
										<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
											<td><div><span class="blueonly"><a href="javascript:void(0)" onclick="if (confirm_js(phrase[\'_confirm_delivery_the_items_have_already_been_shipped_to_buyers_location_additionally_js\'])) location.href=\'' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}\'">{_mark_as_shipped}</a></span></div></td>
										</tr>';
									}
									unset($crypted);
								}
								else
								{
									$rows['shipstatus2'] = '<span title="{_not_in_use}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox_litegray.png" border="0" alt="" /></span>';
								}
							}
							$crypted = array(
								'cmd' => 'management',
								'sub' => 'buynow-escrow',
								'subcmd' => '_cancel-delivery',
								'id' => $orderrows['orderid']
							);
							$shippingactions2 .= '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
								<td><div><span class="blueonly"><a href="javascript:void(0)" onclick="if (confirm_js(\'_cancel_delivery_return_funds_in_escrow_back_to_this_buyer_continue\')) location.href=\'' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}\'">{_cancel_order}</a></span></div></td>
							</tr>';
							unset($crypted);
						}
						else
						{
							$orderrows['buyershipcost'] = 0;
							$orderrows['shippingpartner'] = '{_none}';
							// digital download
							$digitalfile = '{_contact_seller}';
							$dquery = $ilance->db->query("
								SELECT filename, counter, filesize, attachid
								FROM " . DB_PREFIX . "attachment
								WHERE project_id = '" . $orderrows['project_id'] . "'
									AND attachtype = 'digital'
									AND user_id = '" . $orderrows['owner_id'] . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							if ($ilance->db->num_rows($dquery) > 0)
							{
								$dfile = $ilance->db->fetch_array($dquery, DB_ASSOC);
								$crypted = array('id' => $dfile['attachid']);
								$digitalfile = handle_input_keywords($dfile['filename']) . ' (' . print_filesize($dfile['filesize']) . ')';
								$digitaldownloadaction = '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
									<td><div><span class="blueonly" title="' . $digitalfile . '"><span style="float:left;margin-top:-3px;padding-right:6px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'paperclip.gif" alt="" border="0" /></span><a href="' . HTTPS_SERVER . $ilpage['attachment'] . '?crypted=' . encrypt_url($crypted) . '">{_download_digital_attachment}</a></span></div></td>
								</tr>';
								$orderrows['shipstatus2'] = '<div><span title="{_digital_delivery}: ' . handle_input_keywords($digitalfile) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox.png" border="0" alt="" /></span></div>';
								$orderrows['shipping'] = '{_digital_delivery}';
							}
							// no shipping local pickup only
							else
							{
								$orderrows['shipping'] = '{_local_pickup_only}';
								$orderrows['shipstatus2'] = '<div><span title="{_not_in_use} ({_local_pickup_only})"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox_litegray.png" border="0" alt="" /></span></div>';
							}
						}
					}
					else if ($orderrows['status'] == 'pending_delivery')
					{
						$show['escrowcolumn']['project_id'] = 1;
						$orderrows['shipping'] = '';
						$orderrows['escrowtotal2'] = '<span title="{_funds_secured_in_escrow}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/escrow_yellow.png" border="0" alt="" /></span>';
						$orderrows['paystatus2'] = '<span title="{_escrow_account_funded_on} ' . print_date($orderrows['winnermarkedaspaiddate']) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" /></span>';
						$orderrows['pmb2'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $orderrows['buyer_id'], $orderrows['project_id']);
						$orderrows['work2'] = $ilance->auction->construct_mediashare_icon($orderrows['buyer_id'], $_SESSION['ilancedata']['user']['userid'], $orderrows['project_id'], true);
						$primaryaction = '<div style="padding-bottom:3px" class="blue"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $orderrows['project_id'] . '&amp;oid=' . $orderrows['orderid'] . '&amp;returnurl={pageurl_urlencoded}"><strong>{_view_order_details}</strong></a></div>';
						// shipping logic
						if ($orderrows['buyershipperid'] > 0)
						{
							$shiptemp = $ilance->shipping->fetch_ship_cost_by_shipperid($orderrows['project_id'], $orderrows['buyershipperid'], $orderrows['qty'], $orderrows['buyershipcost']);
							$orderrows['buyershipcost'] = $shiptemp['total'];
							if ($orderrows['buyershipcost'] > 0)
							{
								$orderrows['shipping'] = '<span title="{_shipping}">+ ' . $ilance->currency->format($orderrows['buyershipcost'], $currencyid) . '</span>';
							}
							if ($orderrows['sellermarkedasshipped'] AND $orderrows['sellermarkedasshippeddate'] != '0000-00-00 00:00:00')
							{
								$orderrows['shipstatus2'] = '<div><span title="{_marked_as_shipped_on} ' . print_date($orderrows['sellermarkedasshippeddate']) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox.png" border="0" alt="" /></span></div>';
								$crypted = array(
									'cmd' => 'management',
									'subcmd' => 'markasunshipped',
									'mode' => 'buynow',
									'pid' => $orderrows['project_id'],
									'sellerid' => $_SESSION['ilancedata']['user']['userid'],
									'buyerid' => $orderrows['buyer_id'],
									'bid' => $orderrows['orderid']
								);
								$date1split = explode(' ', $orderrows['sellermarkedasshippeddate']);
								$date2split = explode('-', $date1split[0]);
								$totaldays = 14;
								$elapsed = $ilance->datetimes->fetch_days_between($date2split[1], $date2split[2], $date2split[0], date('m'), date('d'), date('Y'));
								$daysleft = ($totaldays - $elapsed);
								if ($elapsed <= 14)
								{
									$shippingactions2 = '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
										<td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['selling'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}">{_mark_as_unshipped}</a></span></div></td>
									</tr>';
								}
								unset($crypted, $date1split, $date2split, $totaldays, $elapsed, $daysleft);
							}
							else
							{
								if ($orderrows['buyershipperid'] > 0)
								{
									$orderrows['shipstatus2'] = '<span title="{_the_seller_has_not_yet_marked_your_shipment_as_delivered}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox_litegray.png" border="0" alt="" /></span>';
									$crypted = array(
										'cmd' => 'management',
										'sub' => 'buynow-escrow',
										'subcmd' => '_confirm-delivery',
										'id' => $orderrows['orderid']
									);
									if (empty($primaryaction))
									{
										$primaryaction = '<div style="padding-bottom:3px" class="blue"><a href="javascript:void(0)" onclick="return show_prompt_shipping(\'' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}\')"><strong>{_mark_as_shipped}</strong></a></div>';
									}
									else
									{
										$paymentactions2 = '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
											<td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $orderrows['project_id'] . '&amp;oid=' . $orderrows['orderid'] . '&amp;returnurl={pageurl_urlencoded}">{_view_order_details}</a></span></div></td>
										</tr>
										<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
											<td><div><span class="blueonly"><a href="javascript:void(0)" onclick="return show_prompt_shipping(\'' . HTTP_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}\')">{_mark_as_shipped}</a></span></div></td>
										</tr>';
									}
									unset($crypted);
								}
								else
								{
									$rows['shipstatus2'] = '<span title="{_not_in_use}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox_litegray.png" border="0" alt="" /></span>';
								}
							}
						}
						else
						{
							$orderrows['buyershipcost'] = 0;
							$orderrows['shippingpartner'] = '{_none}';
							// digital download
							$digitalfile = '{_contact_seller}';
							$dquery = $ilance->db->query("
								SELECT filename, counter, filesize, attachid
								FROM " . DB_PREFIX . "attachment
								WHERE project_id = '" . $orderrows['project_id'] . "'
									AND attachtype = 'digital'
									AND user_id = '" . $orderrows['owner_id'] . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							if ($ilance->db->num_rows($dquery) > 0)
							{
								$dfile = $ilance->db->fetch_array($dquery, DB_ASSOC);
								$crypted = array('id' => $dfile['attachid']);
								$digitalfile = handle_input_keywords($dfile['filename']) . ' (' . print_filesize($dfile['filesize']) . ')';
								$digitaldownloadaction = '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
									<td><div><span class="blueonly" title="' . $digitalfile . '"><span style="float:left;margin-top:-3px;padding-right:6px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'paperclip.gif" alt="" border="0" /></span><a href="' . HTTPS_SERVER . $ilpage['attachment'] . '?crypted=' . encrypt_url($crypted) . '">{_download_digital_attachment}</a></span></div></td>
								</tr>';
								$orderrows['shipstatus2'] = '<div><span title="{_digital_delivery}: ' . handle_input_keywords($digitalfile) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox.png" border="0" alt="" /></span></div>';
								$orderrows['shipping'] = '{_digital_delivery}';
							}
							// no shipping local pickup only
							else
							{
								$orderrows['shipping'] = '{_local_pickup_only}';
								$orderrows['shipstatus2'] = '<div><span title="{_not_in_use} ({_local_pickup_only})"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox_litegray.png" border="0" alt="" /></span></div>';
							}
						}
					}
					else if ($orderrows['status'] == 'delivered')
					{
						$show['escrowcolumn']['project_id'] = 1;
						$orderrows['shipping'] = '';
						$orderrows['escrowtotal2'] = '<div><span title="{_funds_released_by_buyer}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/escrow_blue.png" border="0" alt="" id="" /></span></div>';
						$orderrows['paystatus2'] = '<span title="{_escrow_funds_released_on} ' . print_date($orderrows['releasedate']) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" /></span>';
						$orderrows['pmb2'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $orderrows['buyer_id'], $orderrows['project_id']);
						$orderrows['work2'] = $ilance->auction->construct_mediashare_icon($orderrows['buyer_id'], $_SESSION['ilancedata']['user']['userid'], $orderrows['project_id'], true);
						$primaryaction = '<div style="padding-bottom:3px" class="blue"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $orderrows['project_id'] . '&amp;oid=' . $orderrows['orderid'] . '&amp;returnurl={pageurl_urlencoded}"><strong>{_view_order_details}</strong></a></div>';
						// shipping logic
						if ($orderrows['buyershipperid'] > 0)
						{
							$shiptemp = $ilance->shipping->fetch_ship_cost_by_shipperid($orderrows['project_id'], $orderrows['buyershipperid'], $orderrows['qty'], $orderrows['buyershipcost']);
							$orderrows['buyershipcost'] = $shiptemp['total'];
							if ($orderrows['buyershipcost'] > 0)
							{
								$orderrows['shipping'] = '<span title="{_shipping}">+ ' . $ilance->currency->format($orderrows['buyershipcost'], $currencyid) . '</span>';
							}
							if ($orderrows['sellermarkedasshipped'] AND $orderrows['sellermarkedasshippeddate'] != '0000-00-00 00:00:00')
							{
								$orderrows['shipstatus2'] = '<div><span title="{_marked_as_shipped_on} ' . print_date($orderrows['sellermarkedasshippeddate']) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox.png" border="0" alt="" /></span></div>';
								$crypted = array(
									'cmd' => 'management',
									'subcmd' => 'markasunshipped',
									'mode' => 'buynow',
									'pid' => $orderrows['project_id'],
									'sellerid' => $_SESSION['ilancedata']['user']['userid'],
									'buyerid' => $orderrows['buyer_id'],
									'bid' => $orderrows['orderid']
								);
								$date1split = explode(' ', $orderrows['sellermarkedasshippeddate']);
								$date2split = explode('-', $date1split[0]);
								$totaldays = 14;
								$elapsed = $ilance->datetimes->fetch_days_between($date2split[1], $date2split[2], $date2split[0], date('m'), date('d'), date('Y'));
								$daysleft = ($totaldays - $elapsed);
								if ($elapsed <= 14)
								{
									$shippingactions2 = '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
										<td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['selling'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}">{_mark_as_unshipped}</a></span></div></td>
									</tr>';
								}
								unset($crypted, $date1split, $date2split, $totaldays, $elapsed, $daysleft);
							}
							else
							{
								if ($orderrows['buyershipperid'] > 0)
								{
									$orderrows['shipstatus2'] = '<span title="{_the_seller_has_not_yet_marked_your_shipment_as_delivered}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox_litegray.png" border="0" alt="" /></span>';
									$crypted = array(
										'cmd' => 'management',
										'sub' => 'buynow-escrow',
										'subcmd' => '_confirm-delivery',
										'id' => $orderrows['orderid']
									);
									if (empty($primaryaction))
									{
										$primaryaction = '<div style="padding-bottom:3px" class="blue"><a href="javascript:void(0)" onclick="return show_prompt_shipping(\'' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}\')"><strong>{_mark_as_shipped}</strong></a></div>';
									}
									else
									{
										$paymentactions2 = '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
											<td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $orderrows['project_id'] . '&amp;oid=' . $orderrows['orderid'] . '&amp;returnurl={pageurl_urlencoded}">{_view_order_details}</a></span></div></td>
										</tr>
										<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
											<td><div><span class="blueonly"><a href="javascript:void(0)" onclick="return show_prompt_shipping(\'' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}\')">{_mark_as_shipped}</a></span></div></td>
										</tr>';
									}
									unset($crypted);
								}
								else
								{
									$rows['shipstatus2'] = '<span title="{_not_in_use}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox_litegray.png" border="0" alt="" /></span>';
								}
							}
						}
						else
						{
							$orderrows['buyershipcost'] = 0;
							$orderrows['shippingpartner'] = '{_none}';
							// digital download
							$digitalfile = '{_contact_seller}';
							$dquery = $ilance->db->query("
								SELECT filename, counter, filesize, attachid
								FROM " . DB_PREFIX . "attachment
								WHERE project_id = '" . $orderrows['project_id'] . "'
									AND attachtype = 'digital'
									AND user_id = '" . $orderrows['owner_id'] . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							if ($ilance->db->num_rows($dquery) > 0)
							{
								$dfile = $ilance->db->fetch_array($dquery, DB_ASSOC);
								$crypted = array('id' => $dfile['attachid']);
								$digitalfile = handle_input_keywords($dfile['filename']) . ' (' . print_filesize($dfile['filesize']) . ')';
								$digitaldownloadaction = '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
									<td><div><span class="blueonly" title="' . $digitalfile . '"><span style="float:left;margin-top:-3px;padding-right:6px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'paperclip.gif" alt="" border="0" /></span><a href="' . HTTPS_SERVER . $ilpage['attachment'] . '?crypted=' . encrypt_url($crypted) . '">{_download_digital_attachment}</a></span></div></td>
								</tr>';
								$orderrows['shipstatus2'] = '<div><span title="{_digital_delivery}: ' . handle_input_keywords($digitalfile) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox.png" border="0" alt="" /></span></div>';
								$orderrows['shipping'] = '{_digital_delivery}';
							}
							// no shipping local pickup only
							else
							{
								$orderrows['shipping'] = '{_local_pickup_only}';
								$orderrows['shipstatus2'] = '<div><span title="{_not_in_use} ({_local_pickup_only})"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox_litegray.png" border="0" alt="" /></span></div>';
							}
						}
					}
					else if ($orderrows['status'] == 'cancelled')
					{
						$show['escrowcolumn']['project_id'] = 1;
						$orderrows['escrowamount'] = '-';
						$orderrows['shipping'] = '';
						$orderrows['shipstatus2'] = '';
						$orderrows['orderactions'] = '{_cancelled}';
						$orderrows['pmb2'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $orderrows['buyer_id'], $orderrows['project_id']);
						$orderrows['work2'] = $ilance->auction->construct_mediashare_icon($orderrows['buyer_id'], $_SESSION['ilancedata']['user']['userid'], $orderrows['project_id'], $active = false);
					}
					else if ($orderrows['status'] == 'offline')
					{
						$orderrows['shipping'] = '';
						$noprimaryaction = false;
						if ($orderrows['paiddate'] == '0000-00-00 00:00:00')
						{
							$crypted = array(
								'cmd' => 'management',
								'sub' => 'buynow-escrow',
								'subcmd' => '_confirm-offline-delivery',
								'id' => $orderrows['orderid']
							);
							$primaryaction = '<div style="padding-bottom:3px" class="blue"><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}" onclick="return confirm_js(phrase[\'_confirm_delivery_the_items_have_already_been_shipped_to_buyers_location_additionally_js\'])"><strong>{_mark_payment_as_received}</strong></a></span></div>';
							$primaryaction .= ($ilconfig['invoicesystem_sendinvoice']) ? '<div style="padding-bottom:3px"><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?do=invoice&amp;cmd=orderdetail&amp;pid=' . $orderrows['project_id'] . '&amp;oid=' . $orderrows['orderid'] . '&amp;returnurl={pageurl_urlencoded}">{_send_invoice}</a></span></div>' : '';
							$paymentactions2 .= '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
								<td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $orderrows['project_id'] . '&amp;oid=' . $orderrows['orderid'] . '&amp;returnurl={pageurl_urlencoded}">{_view_order_details}</a></span></div></td>
							</tr>';
							$orderrows['paystatus2'] = '<div><span title="{_payment} {_unpaid_lower}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy_gray.gif" border="0" alt="" /></span></div>';
						}
						else
						{
							$crypted = array(
								'cmd' => 'management',
								'subcmd' => 'markorderasunpaid',
								'id' => $orderrows['project_id'],
								'orderid' => $orderrows['orderid'],
								'status' => 'offline'
							);
							// digital download
							$digitalfile = '{_contact_seller}';
							$dquery = $ilance->db->query("
								SELECT filename, counter, filesize, attachid
								FROM " . DB_PREFIX . "attachment
								WHERE project_id = '" . $orderrows['project_id'] . "'
									AND attachtype = 'digital'
									AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							if ($ilance->db->num_rows($dquery) > 0)
							{
								$crypted = array(
									'cmd' => 'management',
									'sub' => 'buynow-escrow',
									'subcmd' => '_confirm-offline-delivery',
									'id' => $orderrows['orderid']
								);
								$primaryaction = '<div style="padding-bottom:3px" class="blue"><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}" onclick="return confirm_js(phrase[\'_confirm_release_digital_download_js\'])"><strong>{_release_digital_download}</strong></a></span></div>';
								$paymentactions2 .= '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
									<td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}">{_unmark_payment_as_received}</a></span></div></td>
								</tr>
								<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
									<td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $orderrows['project_id'] . '&amp;oid=' . $orderrows['orderid'] . '&amp;returnurl={pageurl_urlencoded}">{_view_order_details}</a></span></div></td>
								</tr>';
								unset($crypted);
							}
							else
							{
								$noprimaryaction = true;
								$primaryaction = '<div style="padding-bottom:3px" class="blue"><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $orderrows['project_id'] . '&amp;oid=' . $orderrows['orderid'] . '&amp;returnurl={pageurl_urlencoded}"><strong>{_view_order_details}</strong></a></span></div>';
								$paymentactions2 .= '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
									<td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}">{_unmark_payment_as_received}</a></span></div></td>
								</tr>';
							}
							$orderrows['paystatus2'] = '<div><span title="{_marked_as_paid_on} ' . print_date($orderrows['paiddate']) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" /></span></div>';
						}
						unset($crypted);
						$orderrows['pmb2'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $orderrows['buyer_id'], $orderrows['project_id']);
						$orderrows['work2'] = $ilance->auction->construct_mediashare_icon($orderrows['buyer_id'], $_SESSION['ilancedata']['user']['userid'], $orderrows['project_id'], $active = true);
						// shipping logic
						if ($orderrows['buyershipperid'] > 0)
						{
							$shiptemp = $ilance->shipping->fetch_ship_cost_by_shipperid($orderrows['project_id'], $orderrows['buyershipperid'], $orderrows['qty'], $orderrows['buyershipcost']);
							$orderrows['buyershipcost'] = $shiptemp['total'];
							if ($orderrows['buyershipcost'] > 0)
							{
								$orderrows['shipping'] = '<span title="{_shipping}">+ ' . $ilance->currency->format($orderrows['buyershipcost'], $currencyid) . '</span>';
							}
							if ($orderrows['sellermarkedasshipped'] AND $orderrows['sellermarkedasshippeddate'] != '0000-00-00 00:00:00')
							{
								$orderrows['shipstatus2'] = '<div><span title="{_marked_as_shipped_on} ' . print_date($orderrows['sellermarkedasshippeddate']) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox.png" border="0" alt="" /></span></div>';
								$crypted = array(
									'cmd' => 'management',
									'subcmd' => 'markasunshipped',
									'mode' => 'buynow',
									'pid' => $orderrows['project_id'],
									'sellerid' => $_SESSION['ilancedata']['user']['userid'],
									'buyerid' => $orderrows['buyer_id'],
									'bid' => $orderrows['orderid']
								);
								$date1split = explode(' ', $orderrows['sellermarkedasshippeddate']);
								$date2split = explode('-', $date1split[0]);
								$totaldays = 14;
								$elapsed = $ilance->datetimes->fetch_days_between($date2split[1], $date2split[2], $date2split[0], date('m'), date('d'), date('Y'));
								$daysleft = ($totaldays - $elapsed);
								if ($elapsed <= 14)
								{
									$shippingactions2 = '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
										<td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['selling'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}">{_mark_as_unshipped}</a></span></div></td>
									</tr>';
								}
								unset($crypted, $date1split, $date2split, $totaldays, $elapsed, $daysleft);
							}
							else
							{
								if ($orderrows['buyershipperid'] > 0)
								{
									$orderrows['shipstatus2'] = '<span title="{_the_seller_has_not_yet_marked_your_shipment_as_delivered}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox_litegray.png" border="0" alt="" /></span>';
									$crypted = array(
										'cmd' => 'management',
										'subcmd' => 'markasshipped',
										'mode' => 'buynow',
										'pid' => $orderrows['project_id'],
										'sellerid' => $_SESSION['ilancedata']['user']['userid'],
										'buyerid' => $orderrows['buyer_id'],
										'bid' => $orderrows['orderid']
										
									);
									if (empty($primaryaction) OR $noprimaryaction)
									{
										$primaryaction = '<div style="padding-bottom:3px" class="blue"><a href="javascript:void(0)" onclick="return show_prompt_shipping(\'' . HTTP_SERVER . $ilpage['selling'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}\')"><strong>{_mark_as_shipped}</strong></a></div>';
										$paymentactions2 = '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
											<td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $orderrows['project_id'] . '&amp;oid=' . $orderrows['orderid'] . '&amp;returnurl={pageurl_urlencoded}">{_view_order_details}</a></span></div></td>
										</tr>';
									}
									else
									{
										$paymentactions2 = '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
											<td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $orderrows['project_id'] . '&amp;oid=' . $orderrows['orderid'] . '&amp;returnurl={pageurl_urlencoded}">{_view_order_details}</a></span></div></td>
										</tr>
										<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
											<td><div><span class="blueonly"><a href="javascript:void(0)" onclick="return show_prompt_shipping(\'' . HTTP_SERVER . $ilpage['selling'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}\')">{_mark_as_shipped}</a></span></div></td>
										</tr>';
									}
									unset($crypted);
								}
								else
								{
									$rows['shipstatus2'] = '<span title="{_not_in_use}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox_litegray.png" border="0" alt="" /></span>';
								}
							}
						}
						else
						{
							$orderrows['buyershipcost'] = 0;
							$orderrows['shippingpartner'] = '{_none}';
							// digital download
							$digitalfile = '{_contact_seller}';
							$dquery = $ilance->db->query("
								SELECT filename, counter, filesize, attachid
								FROM " . DB_PREFIX . "attachment
								WHERE project_id = '" . $orderrows['project_id'] . "'
									AND attachtype = 'digital'
									AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							if ($ilance->db->num_rows($dquery) > 0)
							{
								$dfile = $ilance->db->fetch_array($dquery, DB_ASSOC);
								$crypted = array('id' => $dfile['attachid']);
								$digitalfile = handle_input_keywords($dfile['filename']) . ' (' . print_filesize($dfile['filesize']) . ')';
								$orderrows['shipstatus2'] = '<div><span title="{_digital_delivery}: ' . handle_input_keywords($digitalfile) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox.png" border="0" alt="" /></span></div>';
								$orderrows['shipping'] = '{_digital_delivery}';
							}
							// no shipping local pickup only
							else
							{
								$orderrows['shipping'] = '{_local_pickup_only}';
								$orderrows['shipstatus2'] = '<div><span title="{_not_in_use} ({_local_pickup_only})"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox_litegray.png" border="0" alt="" /></span></div>';
							}
						}
					}
					else if ($orderrows['status'] == 'offline_delivered')
					{
						$orderrows['shipping'] = '';
						if ($orderrows['paiddate'] == '0000-00-00 00:00:00')
						{
							$crypted = array(
								'cmd' => 'management',
								'subcmd' => 'markorderaspaid',
								'id' => $orderrows['project_id'],
								'orderid' => $orderrows['orderid'],
								'status' => 'offline_delivered'
							);
							$orderrows['paystatus2'] = '<div><span title="{_payment} {_unpaid_lower}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy_gray.gif" border="0" alt="" /></span></div>';
							$primaryaction = ($ilconfig['invoicesystem_sendinvoice']) ? '<div style="padding-bottom:3px" class="blue"><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}"><strong>{_mark_payment_as_received}</strong></a></span></div><div style="padding-bottom:3px"><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?do=invoice&amp;cmd=orderdetail&amp;pid=' . $orderrows['project_id'] . '&amp;oid=' . $orderrows['orderid'] . '&amp;returnurl={pageurl_urlencoded}">{_send_invoice}</a></span></div>' : '';
							$paymentactions2 .= '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
								<td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $orderrows['project_id'] . '&amp;oid=' . $orderrows['orderid'] . '&amp;returnurl={pageurl_urlencoded}">{_view_order_details}</a></span></div></td>
							</tr>';
						}
						else
						{
							$crypted = array(
								'cmd' => 'management',
								'subcmd' => 'markorderasunpaid',
								'id' => $orderrows['project_id'],
								'orderid' => $orderrows['orderid'],
								'status' => 'offline'
							);
							$orderrows['paystatus2'] = '<div><span title="{_marked_as_paid_on} ' . print_date($orderrows['paiddate']) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" /></span></div>';
							$primaryaction = '<div style="padding-bottom:3px" class="blue"><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $orderrows['project_id'] . '&amp;oid=' . $orderrows['orderid'] . '&amp;returnurl={pageurl_urlencoded}"><strong>{_view_order_details}</strong></a></span></div>';
							$paymentactions2 .= '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
								<td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}">{_unmark_payment_as_received}</a></span></div></td>
							</tr>';
							unset($crypted);
						}
						$orderrows['pmb2'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $orderrows['buyer_id'], $orderrows['project_id']);
						$orderrows['work2'] = $ilance->auction->construct_mediashare_icon($orderrows['buyer_id'], $_SESSION['ilancedata']['user']['userid'], $orderrows['project_id'], $active = true);
						// shipping logic
						if ($orderrows['buyershipperid'] > 0)
						{
							$orderrows['shipping'] = '';
							$shiptemp = $ilance->shipping->fetch_ship_cost_by_shipperid($orderrows['project_id'], $orderrows['buyershipperid'], $orderrows['qty'], $orderrows['buyershipcost']);
							$orderrows['buyershipcost'] = $shiptemp['total'];
							if ($orderrows['buyershipcost'] > 0)
							{
								$orderrows['shipping'] = '<span title="{_shipping}">+ ' . $ilance->currency->format($orderrows['buyershipcost'], $currencyid) . '</span>';
							}
							if ($orderrows['sellermarkedasshipped'] AND $orderrows['sellermarkedasshippeddate'] != '0000-00-00 00:00:00')
							{
								$orderrows['shipstatus2'] = '<div><span title="{_marked_as_shipped_on} ' . print_date($orderrows['sellermarkedasshippeddate']) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox.png" border="0" alt="" /></span></div>';
								$crypted = array(
									'cmd' => 'management',
									'subcmd' => 'markasunshipped',
									'mode' => 'buynow',
									'pid' => $orderrows['project_id'],
									'sellerid' => $_SESSION['ilancedata']['user']['userid'],
									'buyerid' => $orderrows['buyer_id'],
									'bid' => $orderrows['orderid']
								);
								$date1split = explode(' ', $orderrows['sellermarkedasshippeddate']);
								$date2split = explode('-', $date1split[0]);
								$totaldays = 14;
								$elapsed = $ilance->datetimes->fetch_days_between($date2split[1], $date2split[2], $date2split[0], date('m'), date('d'), date('Y'));
								$daysleft = ($totaldays - $elapsed);
								if ($elapsed <= 14)
								{
									$shippingactions2 = '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
										<td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['selling'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}">{_mark_as_unshipped}</a></span></div></td>
									</tr>';
								}
								unset($crypted, $date1split, $date2split, $totaldays, $elapsed, $daysleft);
							}
							else
							{
								if ($orderrows['buyershipperid'] > 0)
								{
									$orderrows['shipstatus2'] = '<span title="{_the_seller_has_not_yet_marked_your_shipment_as_delivered}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox_litegray.png" border="0" alt="" /></span>';
									$crypted = array(
										'cmd' => 'management',
										'subcmd' => 'markasshipped',
										'mode' => 'buynow',
										'pid' => $orderrows['project_id'],
										'sellerid' => $_SESSION['ilancedata']['user']['userid'],
										'buyerid' => $orderrows['buyer_id'],
										'bid' => $orderrows['orderid']
										
									);
									$primaryaction = '<div style="padding-bottom:3px" class="blue"><a href="javascript:void(0)" onclick="return show_prompt_shipping(\'' . HTTP_SERVER . $ilpage['selling'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}\')"><strong>{_mark_as_shipped}</strong></a></div>';
									$paymentactions2 = '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
										<td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $orderrows['project_id'] . '&amp;oid=' . $orderrows['orderid'] . '&amp;returnurl={pageurl_urlencoded}">{_view_order_details}</a></span></div></td>
									</tr>';
									unset($crypted);
								}
								else
								{
									$rows['shipstatus2'] = '<span title="{_not_in_use}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox_litegray.png" border="0" alt="" /></span>';
								}
							}
						}
						else
						{
							$orderrows['shipping'] = '';
							$orderrows['buyershipcost'] = 0;
							$orderrows['shippingpartner'] = '{_none}';
							// digital download
							$digitalfile = '{_contact_seller}';
							$dquery = $ilance->db->query("
								SELECT filename, counter, filesize, attachid
								FROM " . DB_PREFIX . "attachment
								WHERE project_id = '" . $orderrows['project_id'] . "'
									AND attachtype = 'digital'
									AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							if ($ilance->db->num_rows($dquery) > 0)
							{
								$dfile = $ilance->db->fetch_array($dquery, DB_ASSOC);
								$crypted = array('id' => $dfile['attachid']);
								$digitalfile = handle_input_keywords($dfile['filename']) . ' (' . print_filesize($dfile['filesize']) . ')';
								$orderrows['shipstatus2'] = '<div><span title="{_digital_delivery}: ' . handle_input_keywords($digitalfile) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox.png" border="0" alt="" /></span></div>';
								$orderrows['shipping'] = '{_digital_delivery}';
							}
							// no shipping local pickup only
							else
							{
								$orderrows['shipping'] = '{_local_pickup_only}';
								$orderrows['shipstatus2'] = '<div><span title="{_not_in_use} ({_local_pickup_only})"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox_litegray.png" border="0" alt="" /></span></div>';
							}
						}
					}
					if (!empty($orderrows['shiptracknumber']))
					{
						$orderrows['shiptracking'] = '<div style="padding-bottom:3px" class="smaller"><strong>{_ship_tracking}:</strong> ' . $ilance->shipping->print_tracking_url($orderrows['buyershipperid'], $orderrows['shiptracknumber']) . '</div>';
					}
					else
					{
						$orderrows['shiptracking'] = '';
					}
					$orderrows['currencyinfo'] = '';
					if ($orderrows['originalcurrencyid'] != $orderrows['convertedtocurrencyid'])
					{
						$orderrows['currencyinfo'] = '<div style="padding-bottom:3px" class="smaller"><strong>{_currency}:</strong> <span title="' . $ilance->currency->currencies[$orderrows['convertedtocurrencyid']]['code'] . ' {_on} ' . $orderrows['orderdate'] . ' = ' . $orderrows['convertedtocurrencyidrate'] . ', ' . $ilance->currency->currencies[$orderrows['originalcurrencyid']]['code'] . ' = ' . $orderrows['originalcurrencyidrate'] . '">' . $ilance->currency->currencies[$orderrows['originalcurrencyid']]['code'] . ' {_to} ' . $ilance->currency->currencies[$orderrows['convertedtocurrencyid']]['code'] . '</span></div>';
					}
					
					($apihook = $ilance->api('selling_management_buy_now_end')) ? eval($apihook) : false;
					
					//$orderrows['total'] = $ilance->currency->format(($orderamount), $orderrows['currencyid']);
					$orderrows['orderactions2'] = $primaryaction . $feedbackaction2 . '<div style="padding-top:3px" class="blue"><a href="javascript:void(0)" onmouseover="show_actions_popup(\'' . $orderrows['orderid'] . '\')" onmouseout="hide_actions_popup(\'' . $orderrows['orderid'] . '\')">{_more_actions}</a> <span style="padding-left:3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/expand_hover.png" border="0" alt="" /></span></div>
<div style="display:none;position:absolute;z-index:5000;background-color:#fff;margin-top:1px;margin-left:-90px;background-color:#fff;border:1px solid #ccc;-webkit-box-shadow: #ddd 3px 3px 6px;-moz-box-shadow: #ddd 3px 3px 6px" id="actions_popup_' . $orderrows['orderid'] . '" onmouseover="show_actions_popup_links(\'' . $orderrows['orderid'] . '\');" onmouseout="hide_actions_popup(\'' . $orderrows['orderid'] . '\');"><div class="n"><div class="e"><div class="w"></div></div></div><div>
<table border="0" cellpadding="9" cellspacing="0">
' . $paymentactions2 . '
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
<td><div><span class="blueonly"><a href="' . $ilpage['selling'] . '?cmd=new-item&amp;cid=' . fetch_auction('cid', $orderrows['project_id']) . '">{_sell_a_similar_item}</a></span></div></td>
</tr>
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
<td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=packingslip&amp;pid=' . $orderrows['project_id'] . '&amp;amount=' . $orderrows['total_plain'] . '&amp;qty=' . $orderrows['qty'] . '&amp;buyerid=' . $orderrows['buyer_id'] . '&amp;returnurl={pageurl_urlencoded}">{_print_packing_slip}</a></span></div></td>
</tr>
' . $orderactions2 . '
' . $shippingactions2 . '
</table></div><div class="s"><div class="e"><div class="w"></div></div></div>';
					$orderrows['class'] = ($order_count % 2) ? 'alt2' : 'alt1';
					$GLOBALS['purchase_now_activity' . $row['project_id']][] = $orderrows;
					$GLOBALS['no_purchase_now_activity' . $row['project_id']][] = false;
					$order_count++;
				}
			}
			else
			{
				$GLOBALS['no_purchase_now_activity' . $row['project_id']][] = true;
			}
			// #### expanded bidding activity
			$resulttmp = $ilance->db->query("
				SELECT b.bid_id
				FROM " . DB_PREFIX . "project_bids AS b,
				" . DB_PREFIX . "projects AS p,
				" . DB_PREFIX . "projects_shipping AS s,
				" . DB_PREFIX . "users AS u
				WHERE b.project_id = '" . $row['project_id'] . "'
					AND p.project_id = b.project_id
					AND p.project_id = s.project_id
					AND b.project_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND b.user_id = u.user_id
					AND b.bidstatus != 'declined'
					AND b.bidstate != 'retracted' and p.project_title LIKE '%" . $ilance->db->escape_string($keyw) . "%'
				ORDER BY b.bidamount DESC, b.date_added DESC
			", 0, null, __FILE__, __LINE__);
			$result2 = $ilance->db->query("
				SELECT b.bid_id, b.user_id AS bidder_id, b.project_id, b.project_user_id, b.bidamount, b.date_added, b.date_updated, b.date_awarded, b.bidstatus, b.bidstate, b.qty, b.buyerpaymethod, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.buyershipcost, b.buyershipperid, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, b.shiptracknumber, p.project_id, p.status AS project_status, p.buynow, p.buynow_price, p.donation, p.charityid, p.donationpercentage, b.fvf, p.donermarkedaspaid, p.donationinvoiceid, p.donermarkedaspaiddate, p.currencyid, p.salestaxstate, p.salestaxrate, p.salestaxshipping, u.username, u.city, u.state, u.zip_code, u.country, s.ship_method, s.ship_handlingfee, s.ship_handlingtime
				FROM " . DB_PREFIX . "project_bids AS b,
				" . DB_PREFIX . "projects AS p,
				" . DB_PREFIX . "projects_shipping AS s,
				" . DB_PREFIX . "users AS u
				WHERE b.project_id = '" . $row['project_id'] . "'
					AND p.project_id = b.project_id
					AND p.project_id = s.project_id
					AND b.project_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND b.user_id = u.user_id
					AND b.bidstatus != 'declined'
					AND b.bidstate != 'retracted' and p.project_title LIKE '%" . $ilance->db->escape_string($keyw) . "%'
				ORDER BY b.bidamount DESC, b.date_added DESC
			", 0, null, __FILE__, __LINE__);
			$number3 = $ilance->db->num_rows($resulttmp);
			if ($ilance->db->num_rows($result2) > 0)
			{
				$rows_count = 0;
				while ($rows = $ilance->db->fetch_array($result2, DB_ASSOC))
				{
					$project_status = $rows['project_status'];
					$shiptracking = '{_doubleclick_to_edit}';
					if (!empty($rows['shiptracknumber']))
					{
						$rows['shiptracking'] = '<div style="padding-bottom:3px" class="smaller"><strong>{_ship_tracking}:</strong> ' . $ilance->shipping->print_tracking_url($rows['buyershipperid'], $rows['shiptracknumber']) . '</div>';
					}
					else
					{
						$rows['shiptracking'] = '';
					}
					if ($rows['salestaxrate'] > 0 AND !empty($rows['salestaxstate']))
					{
						$rows['salestax'] = '<div style="padding-bottom:3px" class="smaller"><strong>{_sales_tax}:</strong> x</div>';
					}
					else
					{
						$rows['salestax'] = '';
					}
					$p_id = $row['project_id'];
					$rows['bid'] = $rows['bid_id'];
					$rows['isonline'] = print_online_status($rows['bidder_id']);
					$rows['bidder'] = print_username($rows['bidder_id'], 'plain', 0, '', '');
					$rows['city'] = ucfirst($rows['city']);
					$rows['state'] = ucfirst($rows['state']);
					$rows['zip'] = trim(mb_strtoupper($rows['zip_code']));
					$rows['location'] = ucfirst($rows['state']) . ' &gt; ' . $ilance->common_location->print_user_country($rows['bidder_id'], $_SESSION['ilancedata']['user']['slng']);
					$rows['awarded'] = print_username($rows['bidder_id'], 'custom', 0, '', '', fetch_user('productawards', $rows['bidder_id']) . ' ' . '{_awards}');
					$rows['reviews'] = print_username($rows['bidder_id'], 'custom', 0, '', '', $ilance->auction_service->fetch_service_reviews_reported($rows['bidder_id']) . ' ' . '{_reviews}'); 
					$rows['pmb2'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $rows['bidder_id'], $rows['project_id']);
					$rows['shiptrack'] = $rows['payment'] = $rows['delivery'] = '-';
					$rows['windate'] = print_date($rows['date_awarded'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
					$rows['nonprofits2'] = '';
					$rows['donationpayment2'] = '';
					$rows['paystatus2'] = '';
					// #### nonprofit logic
					if ($rows['charityid'] > 0 AND $rows['donation'] > 0)
					{
						$charity = fetch_charity_details($rows['charityid']);
						$show['show_donation_row' . $row['project_id'] . '_' . $rows['bid']] = true;
						if ($rows['bidstatus'] == 'awarded')
						{
							$rows['donationpayment2'] = ($rows['donermarkedaspaid'] AND $rows['donermarkedaspaiddate'] != '0000-00-00 00:00:00')
								? '<span title="Donation payment marked as paid on ' . print_date($rows['donermarkedaspaiddate']) . '"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $rows['donationinvoiceid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" /></a></span>'
								: '<span title="{_pay_now}"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $rows['donationinvoiceid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy_gray.gif" border="0" /></a></span>';
						}
						else
						{
							$rows['donationpayment2'] = '<span title="Bid not yet awarded"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy_gray.gif" border="0" /></span>';
						}
						$rows['nonprofits2'] = '<span title="{_nonprofit}: ' . handle_input_keywords($charity['title']) . ' - ' . $rows['donationpercentage'] . '%"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/nonprofits.png" border="0" /></span>';
					}
					else
					{
						$show['show_donation_row' . $row['project_id'] . '_' . $rows['bid']] = false;
						$rows['donationpayment2'] = '<span title="{_not_in_use}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy_gray.gif" border="0" /></span>';
						$rows['nonprofits2'] = '<span title="{_not_in_use}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/nonprofits_litegray.png" border="0" /></span>';
					}
					$rows['work2'] = $rows['award'] = '-';
					// #### does seller need to give feedback to buyer for this buy now escrow?
					if ($ilance->feedback->has_left_feedback($rows['bidder_id'], $rows['project_user_id'], $rows['project_id'], 'buyer'))
					{
						$rows['feedback2'] = '<span title="{_feedback_submitted__thank_you}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_complete.gif" border="0" alt="{_feedback_submitted__thank_you}" /></span>';
					}
					else
					{
						$rows['feedback2'] = '<span title="{_submit_feedback_for} ' . $rows['bidder'] . '"><a href="' . HTTP_SERVER . $ilpage['feedback'] . '?cmd=_leave-feedback&amp;view=2&amp;returnurl={pageurl_urlencoded}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback.gif" border="0" alt="{_submit_feedback_for} ' . fetch_user('username', $rows['bidder_id']) . '" /></a></span>';
					}
					// #### seller fvf logic
					if ($rows['fvf'] > 0)
					{
						$rows['sellerfvf'] = '<span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['accounting'] . '?cmd=transactions&amp;invoicetype=debit&amp;isfvf=1">' . $ilance->currency->format($rows['fvf']) . '</a></span>';
					}
					else
					{
						$rows['sellerfvf'] = '{_none}';	
					}
					$fvfs += $rows['fvf'];
					// #### handle listing status to hide controls like feedback or pmb if bidder didn't win or buy this item
					switch ($project_status)
					{
						case 'open':
						case 'expired':
						case 'archived':
						case 'closed':
						case 'delisted':
						case 'finished':
						{
							// #### awarded bid
							if ($rows['bidstatus'] == 'awarded')
							{
								$awarded_vendor = stripslashes($rows['username']);
								$shiptracking = $paymentactions2 = $shippingactions2 = $primaryaction = $orderactions2 = $feedbackaction2 = '';
								$row['winningbidsbit'] = ' 1 {_winner_lower}';
								$rows['award'] = '<span title="{_winner}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_small.gif" border="0" alt="" /></span>';
								$rows['shiptrack'] = '<div id="shiptracking_' . $row['project_id'] . '"><span id="phrase' . $row['project_id'] . 'inline"><span ondblclick="do_inline_edit(' . $row['project_id'] . ', this);">' . $shiptracking . '</span></span></div><span class="smaller gray">{_doubleclick_to_edit}</span>';
								$rows['escrowtotal2'] = '<span title="{_not_in_use}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/escrow_litegray.png" border="0" alt="" /></span>';
								$methodscount = $ilance->payment->print_payment_methods($row['project_id'], false, true);
								$shippercount = $ilance->shipping->print_shipping_methods($row['project_id'], $rows['qty'], false, true, false, $rows['country'], $_SESSION['ilancedata']['user']['slng']);
								if ($rows['buyerpaymethod'] == 'escrow')
								{
									$rows['escrowstatus'] = $rows['fee2invoiceid'] = $rows['isfee2paid'] = $rows['fee2'] = $rows['escrow_id'] = '';
									$sql_escrow = $ilance->db->query("
											SELECT pe.status, pe.fee2invoiceid, pe.isfee2paid, pe.fee2, pe.escrow_id 
											FROM " . DB_PREFIX . "projects_escrow pe
											WHERE pe.project_id = '" . intval($row['project_id']) . "'
									");
									if ($ilance->db->num_rows($sql_escrow) > 0)
									{
										while ($res_escrow = $ilance->db->fetch_array($sql_escrow))
										{
											$rows['escrowstatus'] = $res_escrow['status'];
											$rows['fee2invoiceid'] = $res_escrow['fee2invoiceid'];
											$rows['isfee2paid'] = $res_escrow['isfee2paid'];
											$rows['fee2'] = $res_escrow['fee2'];
											$rows['escrow_id'] = $res_escrow['escrow_id'];
										}
									}
									if ($rows['fee2invoiceid'] > 0)
									{
										$rows['escrowfee'] = ($rows['isfee2paid'])
										? '<span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $rows['fee2invoiceid'] . '">' . $ilance->currency->format($rows['fee2']) . '</a></span>'
										: '<span class="red"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $rows['fee2invoiceid'] . '">' . $ilance->currency->format($rows['fee2']) . '</a></span>';
									}
									else
									{
										$rows['escrowfee'] = ($rows['isfee2paid'])
										? '<span class="black">' . $ilance->currency->format($rows['fee2']) . '</span>'
										: '<span class="red">' . $ilance->currency->format($rows['fee2']) . '</span>';
									}
									if ($rows['escrowstatus'] == 'pending')
									{
										$rows['escrowtotal2'] = '<span title="{_waiting_for} ' . handle_input_keywords($awarded_vendor) . ' {_to_fund_this_escrow_account}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/escrow_yellow.png" border="0" alt="" /></span>';
										$rows['paystatus2'] = '<span title="{_waiting_for} ' . handle_input_keywords($awarded_vendor) . ' {_to_fund_this_escrow_account}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy_gray.gif" border="0" alt="" /></span>';
										$primaryaction = '<div class="blue"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $row['project_id'] . '&amp;bid=' . $rows['bid_id'] . '&amp;returnurl={pageurl_urlencoded}"><strong>{_view_order_details}</strong></a></div>';
										if ($rows['buyershipperid'] > 0 AND $rows['buyershipcost'] > 0)
										{
											$shippingactions2 .= '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
												<td><div><span class="gray" title="{_cannot_mark_item_delivered_until_buyer_escrow_fund}">{_mark_as_shipped}</span></div></td>
											</tr>';
										}
										else
										{
											$shippingactions2 .= '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
												<td><div><span class="gray" title="{_cannot_mark_item_delivered_until_buyer_escrow_fund}">{_mark_item_delivered}</span></div></td>
											</tr>';
										}
									}
									else if ($rows['escrowstatus'] == 'started')
									{
										$rows['escrowtotal2'] = '<span title="{_funds_secured_in_escrow}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/escrow_green.png" border="0" alt="" /></span>';
										$rows['paystatus2'] = '<span title="{_escrow_account_funded_on} ' . print_date($rows['winnermarkedaspaiddate']) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" /></span>';
										// mark as shipped or delivered to buyer
										$crypted = array(
											'cmd' => 'management',
											'sub' => 'product-escrow',
											'subcmd' => '_confirm-delivery',
											'id' => $rows['escrow_id']
										);
										if ($rows['buyershipperid'] > 0 AND $rows['buyershipcost'] > 0)
										{
											$primaryaction .= '<div class="blue"><span title="{_use_this_option_to_mark_items_purchased_release_escrow}"><a href="javascript:void(0)" onclick="if (confirm_js(\'{_confirm_the_product_has_been_shipped_or_delivered_to_the_highest_bidder}\')) location.href=\'' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}\'"><strong>{_mark_as_shipped}</strong></a></span></div>';
										}
										else
										{
											$primaryaction .= '<div class="blue"><span title="{_use_this_option_to_mark_items_purchased_release_escrow}"><a href="javascript:void(0)" onclick="if (confirm_js(\'{_confirm_the_product_has_been_shipped_or_delivered_to_the_highest_bidder}\')) location.href=\'' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}\'"><strong>{_mark_item_delivered}</strong></a></span></div>';
										}
										unset($crypted);
										// seller control to return buyer funds held in escrow
										$crypted = array(
											'cmd' => 'management',
											'sub' => 'product-escrow',
											'subcmd' => '_cancel-delivery',
											'id' => $row['escrow_id']
										);
										$paymentactions2 .= '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
											<td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $rows['project_id'] . '&amp;bid=' . $rows['bid_id'] . '&amp;returnurl={pageurl_urlencoded}">{_view_order_details}</a></span></div></td>
										</tr>
										<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
											<td><div><span class="blueonly" title=""><a href="javascript:void(0)" onclick="if (confirm_js(\'{_return_funds_in_escrow_back_to_highest_bidder}\')) location.href=\'' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}\'">{_return_escrow_funds_to_buyer}</a></span></div></td>
										</tr>';
										unset($crypted);
									}
									else if ($rows['escrowstatus'] == 'confirmed')
									{
										$rows['escrowtotal2'] = '<span title="{_funds_secured_in_escrow}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/escrow_green.png" border="0" alt="" /></span>';
										$rows['paystatus2'] = '<span title="{_escrow_account_funded_on} ' . print_date($rows['winnermarkedaspaiddate']) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" /></span>';
										$shippingactions2 = '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
											<td><div><span class="blueonly"><span class="gray">{_mark_as_shipped}</span></span></div></td>
										</tr>';
										// mark as shipped
										// mark as unshipped
										// return buyer funds
									}
									else if ($rows['escrowstatus'] == 'finished')
									{
										$rows['date_released'] = $ilance->db->fetch_field(DB_PREFIX . "projects_escrow", "project_id = '" . intval($row['project_id']) . "'", "date_released");
										$rows['escrowtotal2'] = '<span title="{_funds_released_to_vendor}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/escrow_blue.png" border="0" alt="" /></span>';
										$rows['paystatus2'] = '<span title="{_funds_in_escrow_released_to} ' . handle_input_keywords($awarded_vendor) . ' {_on} ' . print_date($rows['date_released']) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" /></span>';
										$primaryaction = '<div class="blue"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $row['project_id'] . '&amp;bid=' . $rows['bid_id'] . '&amp;returnurl={pageurl_urlencoded}"><strong>{_view_order_details}</strong></a></div>';
									}
								}
								else
								{
									$rows['paymethod'] = $ilance->payment->print_fixed_payment_method($rows['buyerpaymethod'], false);
									$crypted = array(
										'cmd' => 'management',
										'subcmd' => 'markaspaid',
										'pid' => $row['project_id'],
										'bid' => $rows['bid_id']
									);
									$crypted2 = array(
										'cmd' => 'management',
										'subcmd' => 'markasunpaid',
										'pid' => $row['project_id'],
										'bid' => $rows['bid_id']
									);
									if (strchr($rows['buyerpaymethod'], 'gateway'))
									{
										$rows['paystatus2'] = ($rows['winnermarkedaspaid'] == '0')
											? '<span title="{_waiting_for} ' . handle_input_keywords($awarded_vendor) . ' {_to_mark_payment_as_sent}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy_gray.gif" border="0" alt="" /></span>'
											: '<span title="{_marked_as_paid_on} ' . print_date($rows['winnermarkedaspaiddate']) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" /></span>';
										
										$primaryaction = ($rows['winnermarkedaspaid'] == '0')
											? (($ilconfig['invoicesystem_sendinvoice']) ? '<div class="blue"><a href="' . HTTPS_SERVER . $ilpage['selling'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}"><strong>{_mark_payment_as_received}</strong></a></div><div style="padding-top:3px"><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?do=invoice&amp;cmd=orderdetail&amp;pid=' . $row['project_id'] . '&amp;bid=' . $rows['bid'] . '&amp;returnurl={pageurl_urlencoded}">{_send_invoice}</a></span></div>' : '')
											: '<div class="blue"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $row['project_id'] . '&amp;bid=' . $rows['bid_id'] . '&amp;returnurl={pageurl_urlencoded}"><strong>{_view_order_details}</strong></a></div>';
											
										$paymentactions2 .= ($rows['winnermarkedaspaid'] == '0')
											? '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
												<td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $row['project_id'] . '&amp;bid=' . $rows['bid_id'] . '&amp;returnurl={pageurl_urlencoded}">{_view_order_details}</a></span></div></td>
											</tr>'
											: '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
												<td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['selling'] . '?crypted=' . encrypt_url($crypted2) . '&amp;returnurl={pageurl_urlencoded}">{_unmark_payment_as_received}</a></span></div></td>
											</tr>';
										unset($crypted, $crypted2);
									}
									else if (strchr($rows['buyerpaymethod'], 'offline'))
									{
										$rows['paystatus2'] = ($rows['winnermarkedaspaid'] == '0')
											? '<span title="{_waiting_for} ' . handle_input_keywords($awarded_vendor) . ' {_to_mark_payment_as_sent}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy_gray.gif" border="0" alt="" /></span>'
											: '<span title="{_marked_as_paid_on} ' . print_date($rows['winnermarkedaspaiddate']) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" /></span>';
										
										$primaryaction = ($rows['winnermarkedaspaid'] == '0')
											? (($ilconfig['invoicesystem_sendinvoice']) ? '<div class="blue"><a href="javascript:void(0)" onclick="return show_prompt_payment_seller(\'' . HTTPS_SERVER . $ilpage['selling'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}\')"><strong>{_mark_payment_as_received}</strong></a></div><div style="padding-top:3px"><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?do=invoice&amp;cmd=orderdetail&amp;pid=' . $row['project_id'] . '&amp;bid=' . $rows['bid_id'] . '&amp;returnurl={pageurl_urlencoded}">{_send_invoice}</a></span></div>' : '')
											: '<div class="blue"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $row['project_id'] . '&amp;bid=' . $rows['bid_id'] . '&amp;returnurl={pageurl_urlencoded}"><strong>{_view_order_details}</strong></a></div>';
											
										$paymentactions2 .= ($rows['winnermarkedaspaid'] == '0')
											? '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
												<td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $row['project_id'] . '&amp;bid=' . $rows['bid_id'] . '&amp;returnurl={pageurl_urlencoded}">{_view_order_details}</a></span></div></td>
											</tr>'
											: '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
												<td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['selling'] . '?crypted=' . encrypt_url($crypted2) . '&amp;returnurl={pageurl_urlencoded}">{_unmark_payment_as_received}</a></span></div></td>
											</tr>';
										unset($crypted, $crypted2);	
									}
									else
									{
										$rows['paystatus2'] = ($rows['winnermarkedaspaid'] == '0')
											? '<span title="{_waiting_for_buyer_to_choose_a_payment_method}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy_gray.gif" border="0" alt="" /></span>'
											: '<span title="{_marked_as_paid_on} ' . print_date($rows['winnermarkedaspaiddate']) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" /></span>';
										$primaryaction = ($rows['winnermarkedaspaid'] == '0')
											? (($ilconfig['invoicesystem_sendinvoice']) ? '<div class="blue"><a href="javascript:void(0)" onclick="return show_prompt_payment_seller(\'' . HTTPS_SERVER . $ilpage['selling'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}\')"><strong>{_mark_payment_as_received}</strong></a></div><div style="padding-top:3px"><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?do=invoice&amp;cmd=orderdetail&amp;pid=' . $row['project_id'] . '&amp;bid=' . $rows['bid_id'] . '&amp;returnurl={pageurl_urlencoded}">{_send_invoice}</a></span></div>' : '')
											: '<div class="blue"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $row['project_id'] . '&amp;bid=' . $rows['bid_id'] . '&amp;returnurl={pageurl_urlencoded}"><strong>{_view_order_details}</strong></a></div>';
											
										$paymentactions2 .= ($rows['winnermarkedaspaid'] == '0')
											? '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
												<td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $row['project_id'] . '&amp;bid=' . $rows['bid_id'] . '&amp;returnurl={pageurl_urlencoded}">{_view_order_details}</a></span></div></td>
											</tr>'
											: '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
												<td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['selling'] . '?crypted=' . encrypt_url($crypted2) . '&amp;returnurl={pageurl_urlencoded}">{_unmark_payment_as_received}</a></span></div></td>
											</tr>';
									}
								}
								// #### awarded bid shipping details ##################################
								if ($rows['ship_method'] == 'flatrate' OR $rows['ship_method'] == 'calculated')
								{
									if ($shippercount == 1)
									{
										$rows['orderlocation'] = $ilance->shipping->print_shipping_address_text($rows['bidder_id']);
										$ilance->shipping->print_shipping_methods($rows['project_id'], $rows['qty'], false, false, false, $rows['country'], $_SESSION['ilancedata']['user']['slng']);
										if ($shipperidrow > 0)
										{
											$shipperid = $ilance->db->fetch_field(DB_PREFIX . "projects_shipping_destinations", "project_id = '" . $rows['project_id'] . "'", "ship_service_$shipperidrow");
											$shippingcosts = $ilance->shipping->fetch_ship_cost_by_shipperid($rows['project_id'], $shipperid, $rows['qty'], $rows['buyershipcost']);
											$rows['shipping'] = '<span title="{_shipping}">+ ' . $ilance->currency->format($shippingcosts['total'], $rows['currencyid']) . '</span>';
											unset($shipperid);
										}
									}
									else
									{
										if ($shippercount > 1)
										{
											if (empty($rows['buyershipperid']))
											{
												$shippingcosts['total'] = 0;
												$rows['orderlocation'] = $ilance->shipping->print_shipping_address_text($rows['bidder_id']);
												$rows['shipping'] = '';
											}
											else
											{
												$shippingcosts['total'] = $rows['buyershipcost'];
												$rows['orderlocation'] = $ilance->shipping->print_shipping_address_text($rows['bidder_id']);
												$ilance->shipping->print_shipping_methods($rows['project_id'], $rows['qty'], false, false, false, $rows['country'], $_SESSION['ilancedata']['user']['slng']);
												if ($shipperidrow > 0)
												{
													$shipperid = $rows['buyershipperid'];
													$shippingcosts = $ilance->shipping->fetch_ship_cost_by_shipperid($rows['project_id'], $shipperid, $rows['qty'], $rows['buyershipcost']);
													$rows['shipping'] = '<span title="{_shipping}">+ ' . $ilance->currency->format($shippingcosts['total'], $rows['currencyid']) . '</span>';
													unset($shipperid);
												}
											}
										}
										else
										{
											$shippingcosts['total'] = 0;
											$rows['orderlocation'] = '';
											$rows['shipping'] = '';
										}
									}
								}
								else
								{
									$shippingcosts['total'] = 0;
									$rows['orderlocation'] = '{_local_pickup_only}';
									$rows['shipping'] = '{_local_pickup_only}';
									$rows['delivery'] = !empty($rows['delivery']) ? $rows['delivery'] : '';
								}
								$show['show_isawarded' . $row['project_id'] . '_' . $rows['bid_id']] = true;
								$rows['total'] = $ilance->currency->format(($rows['bidamount'] + $shippingcosts['total']) * $rows['qty'], $rows['currencyid']);
								$rows['work2'] = $ilance->auction->construct_mediashare_icon($rows['bidder_id'], $rows['project_user_id'], $row['project_id'], true);
								$rows['bidamount_plain'] = $ilance->currency->format($rows['bidamount'], $rows['currencyid'], false, false, false);
								$rows['bidamount'] = '<strong>' . $ilance->currency->format($rows['bidamount'], $rows['currencyid']) . '</strong>';
								$unpaid_invoices2 = $ilance->db->fetch_field(DB_PREFIX . "invoices", "projectid = '" . intval($rows['project_id']) . "' AND user_id = '" . intval($rows['project_user_id']) . "' AND status = 'unpaid'", "invoiceid");
								if ($unpaid_invoices2 != '')
								{
									$rows['feedback2'] = '<div><span title="{_cannot_leave_feedback_at_the_moment}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback.gif" border="0" alt="" /></span></div>';
									$feedbackaction2 = '<div style="padding-top:3px"><span title="{_cannot_leave_feedback_at_the_moment}" class="litegray">{_leave_feedback}</span></div>';
								}
								else
								{
									if ($ilance->feedback->has_left_feedback($rows['bidder_id'], $_SESSION['ilancedata']['user']['userid'], $rows['project_id'], 'buyer'))
									{
										$leftfeedback = 1;
										$rows['feedback2'] = '<div><span title="{_feedback_left}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_complete.gif" border="0" alt="" /></span></div>';
									}
									else
									{
										$rows['feedback2'] = '<div><span title="{_feedback_not_left}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback.gif" border="0" alt="" /></span></div>';
										$feedbackaction2 = '<div style="padding-top:3px"><span title="{_submit_feedback_for} ' . fetch_user('username', $rows['bidder_id']) . '" class="blueonly"><a href="' . HTTP_SERVER . $ilpage['feedback'] . '?cmd=_leave-feedback&amp;view=2&amp;returnurl={pageurl_urlencoded}&amp;pid=' . $rows['project_id'] . '#' . $rows['project_id'] . '_' . $_SESSION['ilancedata']['user']['userid'] . '_' . $rows['bidder_id'] . '_buyer' . $rows['bid_id'] . '">{_leave_feedback}</a></span></div>';
									}
								}
								if ($ilance->feedback->has_left_feedback($rows['project_user_id'], $rows['bidder_id'], $rows['project_id'], 'seller'))
								{
									$rows['feedbackreceived2'] = '<div><span title="{_feedback_received}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_received.gif" border="0" alt="" /></span></div>';
								}
								else
								{
									$rows['feedbackreceived2'] = '<div><span title="{_feedback_not_received}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_received_litegray.gif" border="0" alt="" /></span></div>';
								}
								// #### handle shipping links handler
								if ($rows['sellermarkedasshipped'] AND $rows['sellermarkedasshippeddate'] != '0000-00-00 00:00:00')
								{
									$rows['shipstatus2'] = '<span title="{_marked_as_shipped_on} ' . print_date($rows['sellermarkedasshippeddate']) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox.png" border="0" alt="" /></span>';
									$crypted = array(
										'cmd' => 'management',
										'subcmd' => 'markasunshipped',
										'mode' => 'bid',
										'pid' => $row['project_id'],
										'sellerid' => $_SESSION['ilancedata']['user']['userid'],
										'buyerid' => $rows['bidder_id'],
										'bid' => $rows['bid_id']
									);
									$date1split = explode(' ', $rows['sellermarkedasshippeddate']);
									$date2split = explode('-', $date1split[0]);
									$totaldays = 14;
									$elapsed = $ilance->datetimes->fetch_days_between($date2split[1], $date2split[2], $date2split[0], date('m'), date('d'), date('Y'));
									$daysleft = ($totaldays - $elapsed);
									if ($elapsed <= 14)
									{
										$shippingactions2 = '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
											<td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['selling'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}" onclick="return confirm_js(\'{_under_certain_circumstances_you_may_need_to_mark_an_item_that_has}\')">{_mark_as_unshipped}</a></span></div></td>
										</tr>';
									}
									unset($crypted, $date1split, $date2split, $totaldays, $elapsed, $daysleft);
								}
								else
								{
									if ($rows['buyershipperid'] > 0)
									{
										$rows['shipstatus2'] = '<span title="{_the_seller_has_not_yet_marked_your_shipment_as_delivered}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox_litegray.png" border="0" alt="" /></span>';
										$crypted = array(
											'cmd' => 'management',
											'subcmd' => 'markasshipped',
											'mode' => 'bid',
											'pid' => $row['project_id'],
											'sellerid' => $_SESSION['ilancedata']['user']['userid'],
											'buyerid' => $rows['bidder_id'],
											'bid' => $rows['bid_id']
											
										);
										if (!empty($primaryaction))
										{
											$shippingactions2 = '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
												<td><div><span class="blueonly"><a href="javascript:void(0)" onclick="return show_prompt_shipping(\'' . HTTP_SERVER . $ilpage['selling'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}\')">{_mark_as_shipped}</a></span></div></td>
											</tr>';
										}
										else
										{
											$primaryaction = '<div class="blue"><a href="javascript:void(0)" onclick="return show_prompt_shipping(\'' . HTTP_SERVER . $ilpage['selling'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}\')"><strong>{_mark_as_shipped}</strong></a></div>';
										}
										unset($crypted);
									}
									else
									{
										$rows['shipstatus2'] = '<span title="{_not_in_use}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox_litegray.png" border="0" alt="" /></span>';
									}
								}
								$rows['orderactions2'] = $primaryaction . $feedbackaction2 . '<div style="padding-top:3px" class="blue"><a href="javascript:void(0)" onmouseover="show_actions_popup(\'' . $rows['project_id'] . '\')" onmouseout="hide_actions_popup(\'' . $rows['project_id'] . '\')">{_more_actions}</a> <span style="padding-left:3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/expand_hover.png" border="0" alt="" /></span></div>
<div style="display:none;position:absolute;z-index:5000;background-color:#fff;margin-top:1px;margin-left:-90px;background-color:#fff;border:1px solid #ccc;-webkit-box-shadow: #ddd 3px 3px 6px;-moz-box-shadow: #ddd 3px 3px 6px" id="actions_popup_' . $rows['project_id'] . '" onmouseover="show_actions_popup_links(\'' . $rows['project_id'] . '\');" onmouseout="hide_actions_popup(\'' . $rows['project_id'] . '\');"><div class="n"><div class="e"><div class="w"></div></div></div><div>
<table border="0" cellpadding="9" cellspacing="0">
' . $paymentactions2 . '
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
<td><div><span class="blueonly"><a href="' . $ilpage['selling'] . '?cmd=new-item&amp;cid=' . fetch_auction('cid', $rows['project_id']) . '">{_sell_a_similar_item}</a></span></div></td>
</tr>
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
<td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=packingslip&amp;pid=' . $rows['project_id'] . '&amp;amount=' . $rows['bidamount_plain'] . '&amp;bid=' . $rows['bid_id'] . '&amp;returnurl={pageurl_urlencoded}">{_print_packing_slip}</a></span></div></td>
</tr>
' . $orderactions2 . '
' . $shippingactions2 . '
</table></div><div class="s"><div class="e"><div class="w"></div></div></div>';
								unset($orderactions2);
							}
							// #### no awarded bid
							else
							{
								$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/bid_awarded_small_gray.gif" border="0" alt="" />';
								$show['show_isawarded' . $row['project_id'] . '_' . $rows['bid_id']] = false;
								$rows['total'] = $ilance->currency->format($rows['bidamount'], $rows['currencyid']);
								$rows['bidamount'] = $ilance->currency->format($rows['bidamount'], $rows['currencyid']);
								$rows['shipstatus2'] = '<span title="{_not_in_use}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox_litegray.png" border="0" alt="" /></span>';
								$rows['paystatus2'] = '<span title="{_not_in_use}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy_gray.gif" border="0" alt="" /></span>';
								$rows['escrowtotal2'] = '<span title="{_not_in_use}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/escrow_litegray.png" border="0" alt="" /></span>';
								$rows['feedback2'] = '<span title="{_not_in_use}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback.gif" border="0" alt="" /></span>';
								$rows['feedbackreceived2'] = '<span title="{_not_in_use}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_received_litegray.gif" border="0" alt="" /></span>';
								$rows['orderactions2'] = '<span class="litegray">{_none}</span>';
								$rows['windate'] = print_date($rows['date_added']);
								$rows['pmb2'] = '<span title="{_not_in_use}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_gray.gif" border="0" alt="" /></span>';
								$rows['work2'] = '<span title="{_not_in_use}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/share_litegray.gif" border="0" alt="" /></span>';
								$rows['shipping'] = '';
							}
							break;
						}                                                                
					}
					$GLOBALS['product_selling_bidding_activity' . $row['project_id']][] = $rows;
					$GLOBALS['no_product_selling_bidding_activity' . $row['project_id']] = false;
					$rows_count++;
				}
			}
			else
			{
				$GLOBALS['no_product_selling_bidding_activity' . $row['project_id']] = true;
			}
			$row['fvf'] = ($fvfs > 0)
				? '<div class="smaller" style="padding-top:3px"><strong><span onmouseover="Tip(phrase[\'_fvf_stands_for_final_value_fee_and_is_a_fee_that_is_applied_when_one_or_more_of_your_items_have_been_sold_via_auction_format_or_buy_now_feature\'], BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()">{_fvf}:</span></strong> <span class="blue"><a href="' . HTTPS_SERVER . $ilpage['accounting'] . '?cmd=transactions&amp;invoicetype=debit&amp;isfvf=1">' . $ilance->currency->format($fvfs) . '</a></span></div>'
				: '';
			$fvfs = 0;
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$show['no_product_selling_activity'] = false;
			$product_selling_activity[] = $row;
			if (isset($ilance->GPC['sub']) AND $ilance->GPC['sub'] == 'archived')
			{
				$show['itempulldownmenu'] = $ilconfig['globalauctionsettings_deletearchivedlistings'] ? true : false;
			}
			else 
			{
				$show['itempulldownmenu'] = true;
			}
			$row_count++;
		}
	}
	else
	{
		$show['no_product_selling_activity'] = true;
		$show['itempulldownmenu'] = false;
	}
	unset($SQL, $SQL2, $result, $row);
	// js to handle updating the shipping/tracking number inline edit
	$headinclude .= '<script type="text/javascript">
<!--
var urlBase = AJAXURL + \'?do=inlineedit&action=sellerpaymethod&id=\';
//-->
</script>';
}
// #### enable service auction support #########################
if ($show['service_selling_activity'])
{
	$keyw = isset($ilance->GPC['keyw2']) ? $ilance->common->xss_clean(handle_input_keywords($ilance->GPC['keyw2'])) : '';
	$keywx = '&keyw=' . $keyw;
	// #### database ordering and limit logic ##############################
	$ilance->GPC['pp2'] = (isset($ilance->GPC['pp2']) ? intval($ilance->GPC['pp2']) : $ilconfig['globalfilters_maxrowsdisplay']);
	$pp2 = (isset($ilance->GPC['pp2']) AND $ilance->GPC['pp2'] >= 0)  ? intval($ilance->GPC['pp2']) : $ilconfig['globalfilters_maxrowsdisplay'];
	$limit  = 'ORDER BY ' . $orderbysql . ' LIMIT ' . (($ilance->GPC['page'] - 1) * $pp2) . ',' . $pp2;
	$limit2 = 'LIMIT ' . (($ilance->GPC['p2'] - 1) * $pp2) . ',' . $pp2;
	$limit4 = 'ORDER BY b.status DESC LIMIT ' . (($ilance->GPC['p4'] - 1) * $pp2) . ',' . $pp2;
	// #### LISTING PERIOD SERVICE BIDS RESULTS ####################
	$extra = '';
	$ilance->GPC['period2'] = (isset($ilance->GPC['period2']) ? intval($ilance->GPC['period2']) : -1);
	$periodsql = fetch_startend_sql($ilance->GPC['period2'], 'DATE_SUB', 'p.date_added', '>=');
	$extra .= '&amp;period2=' . $ilance->GPC['period2'];
	$keyw_bid = (isset($ilance->GPC['search']) AND ($ilance->GPC['search'] == 'bid')) ? $keyw : '' ;
	$ilance->GPC['orderby2'] = (isset($ilance->GPC['orderby2']) ? $ilance->GPC['orderby2'] : 'date_end');
	$ilance->GPC['displayorder2'] = (isset($ilance->GPC['displayorder2']) ? $ilance->GPC['displayorder2'] : 'desc');
	$ilance->GPC['pp2'] = (isset($ilance->GPC['pp2']) ? intval($ilance->GPC['pp2']) : $ilconfig['globalfilters_maxrowsdisplay']);
	$period2_options = array(
		'-1' => '{_any_date}',
		'1' => '{_last_hour}',
		'6' => '{_last_12_hours}',
		'7' => '{_last_24_hours}',
		'13' => '{_last_7_days}',
		'14' => '{_last_14_days}',
		'15' => '{_last_30_days}',
		'16' => '{_last_60_days}',
		'17' => '{_last_90_days}');
	$period2_pulldown = construct_pulldown('period2_pull_id', 'period2', $period2_options, $ilance->GPC['period2'], 'class="smaller" style="font-family: verdana"');
	$orderby2_pulldown = construct_pulldown('orderby2_pull_id', 'orderby2', array('date_end' => '{_date_ending}', 'bids' => '{_bids}'), $ilance->GPC['orderby2'], 'class="smaller" style="font-family: verdana"');
	$displayorder2_pulldown = construct_pulldown('displayorder2_pull_id', 'displayorder2', array('desc' => '{_descending}', 'asc' => '{_ascending}'), $ilance->GPC['displayorder2'], 'class="smaller" style="font-family: verdana"');
	$pp2_pulldown = construct_pulldown('pp2_pull_id', 'pp2', array('10' => '10', '50' => '50', '100' => '100', '500' => '500', '1000' => '1000'), $ilance->GPC['pp2'], 'class="smaller" style="font-family: verdana"');
	// #### DECLINED/DELISTED RESULTS ######################
	if (isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] == 'delisted')
	{
		$bids_delisted = 1;
		$SQL3 = $ilance->bid_tabs->fetch_bidtab_sql('delisted', 'string', 'GROUP BY b.project_id', '', $limit2, $_SESSION['ilancedata']['user']['userid'], '', $keyw_bid);
		$SQL4 = $ilance->bid_tabs->fetch_bidtab_sql('delisted', 'string', 'GROUP BY b.project_id', '', '', $_SESSION['ilancedata']['user']['userid'], '', $keyw_bid);
		$servicetabs = print_selling_activity_tabs('delisted', 'service', $_SESSION['ilancedata']['user']['userid'], $periodsql);
	}
	// #### AWARDED SERVICE BID RESULTS ####################
	else if (isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] == 'awarded')
	{
		// #### define top header nav ##################################
		$topnavlink = array(
			'mycp',
			'selling_awarded'
		);
		$bids_awarded = 1;
		$SQL3 = $ilance->bid_tabs->fetch_bidtab_sql('awarded', 'string', 'GROUP BY b.project_id', '', $limit2, $_SESSION['ilancedata']['user']['userid'], '', $keyw_bid);
		$SQL4 = $ilance->bid_tabs->fetch_bidtab_sql('awarded', 'string', 'GROUP BY b.project_id', '', '', $_SESSION['ilancedata']['user']['userid'], '', $keyw_bid);
		$servicetabs = print_selling_activity_tabs('awarded', 'service', $_SESSION['ilancedata']['user']['userid'], $periodsql);
	}
	// #### INVITED TO BID SERVICE RESULTS #################
	else if (isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] == 'invited')
	{
		$bids_invited = 1;
		$SQL3 = $ilance->bid_tabs->fetch_bidtab_sql('invited', 'string', 'GROUP BY i.project_id', '', $limit2, $_SESSION['ilancedata']['user']['userid'], '', $keyw_bid);
		$SQL4 = $ilance->bid_tabs->fetch_bidtab_sql('invited', 'string', 'GROUP BY i.project_id', '', '', $_SESSION['ilancedata']['user']['userid'], '', $keyw_bid);
		$servicetabs = print_selling_activity_tabs('invited', 'service', $_SESSION['ilancedata']['user']['userid'], $periodsql);
	}
	// #### EXPIRED RESULTS ################################
	else if (isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] == 'expired')
	{
		$bids_expired = 1;
		$SQL3 = $ilance->bid_tabs->fetch_bidtab_sql('expired', 'string', 'GROUP BY b.project_id', '', $limit2, $_SESSION['ilancedata']['user']['userid'], '', $keyw_bid);
		$SQL4 = $ilance->bid_tabs->fetch_bidtab_sql('expired', 'string', 'GROUP BY b.project_id', '', '', $_SESSION['ilancedata']['user']['userid'], '', $keyw_bid);
		$servicetabs = print_selling_activity_tabs('expired', 'service', $_SESSION['ilancedata']['user']['userid'], $periodsql);
	}
	// #### ARCHIVED RESULTS ###############################
	else if (isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] == 'archived')
	{
		$bids_archived = 1;
		$SQL3 = $ilance->bid_tabs->fetch_bidtab_sql('archived', 'string', 'GROUP BY b.project_id', '', $limit2, $_SESSION['ilancedata']['user']['userid'], '', $keyw_bid);
		$SQL4 = $ilance->bid_tabs->fetch_bidtab_sql('archived', 'string', 'GROUP BY b.project_id', '', '', $_SESSION['ilancedata']['user']['userid'], '', $keyw_bid);
		$servicetabs = print_selling_activity_tabs('archived', 'service', $_SESSION['ilancedata']['user']['userid'], $periodsql);
	}
	// #### RETRACTED RESULTS ##############################
	else if (isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] == 'retracted')
	{
		$bids_retracted = 1;
		$SQL3 = $ilance->bid_tabs->fetch_bidtab_sql('retracted', 'string', 'GROUP BY b.project_id', '', $limit2, $_SESSION['ilancedata']['user']['userid'], '', $keyw_bid);
		$SQL4 = $ilance->bid_tabs->fetch_bidtab_sql('retracted', 'string', 'GROUP BY b.project_id', '', '', $_SESSION['ilancedata']['user']['userid'], '', $keyw_bid);
		$servicetabs = print_selling_activity_tabs('retracted', 'service', $_SESSION['ilancedata']['user']['userid'], $periodsql);
	}
	// #### ACTIVE RESULTS #################################
	else
	{
		$bids_active = 1;
		$SQL3 = $ilance->bid_tabs->fetch_bidtab_sql('active', 'string', 'GROUP BY b.project_id', '', $limit2, $_SESSION['ilancedata']['user']['userid'], '', $keyw_bid);
		$SQL4 = $ilance->bid_tabs->fetch_bidtab_sql('active', 'string', 'GROUP BY b.project_id', '', '', $_SESSION['ilancedata']['user']['userid'], '', $keyw_bid);
		$servicetabs = print_selling_activity_tabs('active', 'service', $_SESSION['ilancedata']['user']['userid'], $periodsql);
	}
	$counter2 = ($ilance->GPC['p2'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
	$number2 = $ilance->db->num_rows($ilance->db->query($SQL4, 0, null, __FILE__, __LINE__));
	$result2 = $ilance->db->query($SQL3, 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($result2) > 0)
	{
		$row_count2 = 0;
		while ($row2 = $ilance->db->fetch_array($result2, DB_ASSOC))
		{
			$cid = fetch_auction('cid', $row2['project_id']);
			$bidgrouping = $ilance->categories->bidgrouping($cid);
			$table = ($bidgrouping == true) ? 'project_bids' : 'project_realtimebids';
			if(isset($bids_invited))
			{
				$row2['bid_id'] = $row2['id'] = '';
			}
			$row2['bid_id'] = ($bidgrouping == true) ? $row2['bid_id'] : $row2['id'];
			$field = ($bidgrouping == true) ? "bid_id" : "id";
			$avgamount = $ilance->db->fetch_field(DB_PREFIX . $table, "bidstate != 'retracted' AND bidstate != 'archived' AND bidstatus = 'placed' OR bidstatus = 'awarded' AND project_id = '" . $row2['project_id'] . "'", "AVG(bidamount)");
			$row2['avgamount'] = empty($avgamount) ? '' : $ilance->currency->format($avgamount, $row2['currencyid']);
			if ($row2['bids'] <= 0)
			{
				$row2['bids'] = '-';        
			}
			// final value fee for awarded provider
			if ($row2['fvf'] > 0)
			{
				// final value bit
				if ($row2['isfvfpaid'])
				{
					$row2['fvf'] = '<div class="smaller blue">' . $ilance->currency->format($row2['fvf']). '</div>';
				}
				else
				{
					$row2['fvf'] = '<div class="smaller red"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row2['fvfinvoiceid'] . '">' . $ilance->currency->format($row2['fvf']). '</a></div>';
				}
			}
			else
			{
				// final value bit
				$row2['fvf'] = '-';
			}
			$isawarded = 0;
			$sqleli = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . $table . "
				WHERE bidstatus = 'awarded'
					AND bidstate != 'retracted'
					AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND project_id = '" . $row2['project_id'] . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sqleli) > 0)
			{
				$isawarded = 1;
			}
			$row2['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $row2['user_id'], $row2['project_id']);
			$highbidderid = isset($highbidderid) ? intval($highbidderid) : 0;
			$row['project_id'] = isset($row['project_id']) ? $row['project_id'] : 0;
			$row2['work'] = $ilance->auction->construct_mediashare_icon($_SESSION['ilancedata']['user']['userid'], $highbidderid, $row2['project_id'], false);
			$row2['invoice'] = $row2['feedback'] = '-';
			###########################
			## INVITED RESULT LISTINGS?
			if (isset($bids_invited) AND $bids_invited)
			{
				$row2['bid_id'] = $row2['project_id'];
				$row2['actions'] = '<input type="checkbox" name="bidid[]" value="' . $row2['bid_id'] . '" disabled="disabled" />';
				$row2['buyer'] = fetch_user('username', $row2['user_id']);
				$row2['buyer_id'] = $row2['user_id'];
				$row2['job_title'] = print_string_wrap(stripslashes($row2['project_title']), '45');
				if ($ilconfig['globalauctionsettings_seourls'])
				{
					$row2['job_title'] = construct_seo_url('serviceauction', 0, $row2['bid_id'], $row2['project_title'], '', 0, '', 0, 0, '', '', $row2['job_title']);
				}
				else
				{
					$row2['job_title'] = '<a href="' . $ilpage['rfp'] . '?id=' . $row2['bid_id'] . '">' . $row2['job_title'] . '</a>';
				}
				$row2['realtime_icon'] = '';
				if ($row2['project_details'] == 'realtime')
				{
					$row2['realtime_icon'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/ico_clock.png" alt="{_realtime_auction}" border="0" />';
				}
				$row2['featured_icon'] = '';
				if ($row2['featured'])
				{
					$row2['featured_icon'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/dollarsign.gif" alt="{_featured}" border="0" />';
				}
				$row2['icons'] = $ilance->auction->auction_icons($row2);
				$row2['timeleft'] = (($row2['close_date'] != '0000-00-00 00:00:00' AND $row2['close_date'] < DATETIME24H)) ? '{_ended}' : $ilance->auction->calculate_time_left($row2['date_starts'], $row2['starttime'], $row2['mytime']);
				$rfpstatus = $row2['status'];
				$row2['bidamounttype'] = $row2['estimate_days'] = '';
				$row2['measure'] = '<a href="'.$ilpage['rfp'].'?id='.$row2['project_id'].'">'.'{_place_a_bid}'.'</a>';
				if ($rfpstatus == 'open')
				{
					$row2['status'] = '{_open_for_bids}';
					$row2['bidamount'] = '';
					if ($ilconfig['globalauctionsettings_seourls'])
					{
						$row2['nextstep'] = construct_seo_url('serviceauction', 0, $row2['bid_id'], $row2['project_title'], '{_place_a_bid}', 0, '', 0, 0);
					}
					else
					{
						$row2['nextstep'] = '<a href="' . $ilpage['rfp'] . '?id=' . $row2['bid_id'] . '">{_place_a_bid}</a>';
					}
					$sqlinviteamount = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . $table . "
						WHERE project_id = '" . $row2['project_id'] . "'
							AND user_id ='".$_SESSION['ilancedata']['user']['userid']."'
						ORDER BY bidamount DESC, date_added ASC
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					$days = $res_inv = $ilance->db->fetch_array($sqlinviteamount, DB_ASSOC);
					$invitednum = $ilance->db->num_rows($sqlinviteamount);
					if ($invitednum > 0)
					{
						$row2['estimate_days'] = $days['estimate_days'].' '.$ilance->auction->construct_measure($days['bidamounttype']).'<br/>';
						if ($ilconfig['globalauctionsettings_seourls'])
						{
							$row2['nextstep'] = '<span class="blue">' . construct_seo_url('serviceauction', 0, $row2['project_id'], $row2['project_title'], '{_rebid}', 0, '', 0, 0) . '</span>';
						}
						else
						{
							$row2['nextstep'] = '<span class="blue"><a href="' . $ilpage['rfp'] . '?id=' . $row2['project_id'] . '">{_rebid}</a></span>';
						}
						$invite_currencyid = fetch_user('currencyid',$_SESSION['ilancedata']['user']['userid']);
						$row2['bidamount'] = $ilance->currency->format($days['bidamount'],$invite_currencyid);
					}
					else
					{
						if ($ilconfig['globalauctionsettings_seourls'])
						{
							$row2['nextstep'] = construct_seo_url('serviceauction', 0, $row2['project_id'], $row2['project_title'], '{_place_a_bid}', 0, '', 0, 0);
						}
						else
						{
							$row2['nextstep'] = '<a href="' . $ilpage['rfp'] . '?id=' . $row2['project_id'] . '">{_place_a_bid}</a>';
						}
					}
				}
				else if ($rfpstatus == 'closed')
				{
					$row2['status'] = '{_event_closed}';
					$row2['bidamount'] = '';
					$row2['nextstep'] = '-';
				}
				else if ($rfpstatus == 'expired')
				{
					$row2['status'] = '{_event_closed}';
					$row2['bidamount'] = '';
					$row2['nextstep'] = '-';
				}
				else if ($rfpstatus == 'wait_approval')
				{
					$row2['status'] = '{_event_closed}';
					$row2['bidamount'] = '';
					$row2['nextstep'] = '-';
				}
				else if ($rfpstatus == 'approval_accepted')
				{
					$row2['status'] = '{_event_closed}';
					$row2['bidamount'] = '';
					$row2['nextstep'] = '-';
				}
				else if ($rfpstatus == 'frozen')
				{
					$row2['status'] = '{_frozen}';
					$row2['bidamount'] = '';
					$row2['nextstep'] = '-';
				}
				else if ($rfpstatus == 'closed')
				{
					$row2['status'] = '{_event_closed}';
					$row2['bidamount'] = '';
					$row2['nextstep'] = '-';
				}
				else if ($rfpstatus == 'finished')
				{
					$row2['status'] = '{_event_closed}';
					$row2['bidamount'] = '';
					$row2['nextstep'] = '-';
				}
				else if ($rfpstatus == 'archived')
				{
					$row2['status'] = '{_event_closed}';
					$row2['bidamount'] = '';
					$row2['nextstep'] = '-';
				}
			}
			else
			{
				if (isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] == 'expired'
					OR isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] == 'invited'
					OR isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] == 'delisted'
					OR isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] == 'archived')
				{
					$row2['actions'] = '<input type="checkbox" name="bidid[]" id="' . $row2['project_state'] . 'bid_' . $row2['bid_id'] . '" value="' . $row2['bid_id'] . '|' . $row2['project_id'] . '" />';
					$row2['nextstep'] = $row2['invoice'] = '-';
					$row2['work'] = $ilance->auction->construct_mediashare_icon(-1, -1, -1, 0);
				}
				else
				{
					if(!isset($bids_active))
					{
						$row2['actions'] = '<input type="checkbox" name="bidid[]" id="' . $row2['project_state'] . 'bid_' . $row2['bid_id'] . '" value="' . $row2['bid_id'] . '|' . $row2['project_id'] . '" />';
					}
				}
				$row2['buyer'] = print_username($row2['user_id'], 'href', '', '');
				$row2['buyer_id'] = $row2['user_id'];
				if ($row2['bidstatus'] == 'placed' AND $row2['bidstate'] != 'wait_approval' AND (isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] != 'archived'))
				{
					$row2['status'] = '{_placed}';
					$row2['nextstep'] = '-';
				}
				else if ($row2['bidstatus'] == 'placed' AND $row2['bidstate'] == 'wait_approval' AND (isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] != 'archived'))
				{
					$row2['status'] = '{_waiting_approval}';
					$enc['acceptcrypted'] = array('cmd' => 'bid-management', 'sub' => '_accept-award', 'id' => $row2['bid_id'], 'pid' => $row2['project_id']);
					$enc['declinecrypted'] = array('cmd' => 'bid-management', 'sub' => '_decline-award', 'id' => $row2['bid_id'], 'pid' => $row2['project_id']);
					$row2['fvf_cal'] = ($row2['bidamounttype'] == 'entire') ? '1' :  $row2['estimate_days'];
					$row2['nextstep'] = '<div><input type="button" value="{_accept_award}" onclick="if (confirm_js(\'{_accept_this_service_auction_award_for} ' . $row2['bidamount']*$row2['fvf_cal']  . '?\')) location.href=\'' . HTTPS_SERVER . $ilpage['selling'] . '?crypted=' . encrypt_url($enc['acceptcrypted']) . '\'" class="buttons" style="font-size:10px" /></div><div style="padding-top:3px"><input type="button" value="{_decline_award}" onclick="if (confirm_js(\'{_decline_this_service_auction_award_for} ' . $row2['bidamount'] . '?\')) location.href=\'' . HTTPS_SERVER . $ilpage['selling'] . '?crypted=' . encrypt_url($enc['declinecrypted']) . '\'" class="buttons" style="font-size:10px" /></div>';
					$row2['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $row2['user_id'], $row2['project_id']);
				}
				else if ($row2['bidstatus'] == 'awarded' AND (isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] != 'archived'))
				{
					$row2['status'] = '{_awarded}';
					$row2['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $row2['user_id'], $row2['project_id']);
					$row2['invoice'] = $ilance->auction->construct_invoice_icon($_SESSION['ilancedata']['user']['userid'], $row2['user_id'], $row2['project_id']);
					$row2['work'] = $ilance->auction->construct_mediashare_icon($row2['user_id'], $_SESSION['ilancedata']['user']['userid'], $row2['project_id'], $active = true);
					// escrow system enabled?
					if ($ilconfig['escrowsystem_enabled'] AND $row2['filter_escrow'] == '1')
					{
						// next step: leave feedback if escrow is paid
						$sql_escrowchk = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "projects_escrow
							WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
								AND project_user_id = '" . $row2['user_id'] . "'
								AND project_id = '" . $row2['project_id'] . "'
								AND status = 'pending'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql_escrowchk) > 0)
						{
							// we are waiting for escrow payment
							$row2['nextstep'] = '{_pending_escrow}' . ' <a href="javascript:void(0)" onmouseover="Tip(phrase[\'_pending_means_the_buyer_has_not_forwarded_funds_for_the_awarded_bid_amount\'], BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a>';
						}
						else
						{
							// does auction have pending provider generated invoices that are unpaid?
							$sql_invchk = $ilance->db->query("
								SELECT *
								FROM " . DB_PREFIX . "invoices
								WHERE user_id = '" . $row2['user_id'] . "'
									AND p2b_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
									AND projectid = '" . $row2['project_id'] . "'
									AND status = 'unpaid' 
							", 0, null, __FILE__, __LINE__);
							if ($ilance->db->num_rows($sql_invchk) > 0)
							{
								$pendinvoices = '';
								while ($res_inv = $ilance->db->fetch_array($sql_invchk, DB_ASSOC))
								{
									$crypted = array(
										'cmd' => 'view',
										'txn' => $res_inv['transactionid']
									);
									
									$pendinvoices .= '<a href="' . $ilpage['invoicepayment'] . '?crypted=' . encrypt_url($crypted) . '" title="{_this_invoice_has_not_been_paid_yet}">#' . $res_inv['invoiceid'] . '</a> ';
								}
								$unpaid_invoices = $ilance->db->fetch_field(DB_PREFIX . "invoices", "projectid = '" . intval($row2['project_id']) . "' AND user_id = '" . intval($row2['buyer_id']) . "' AND status = 'unpaid'", "transactionid");
								$row2['feedback'] = '<div><span title="{_please_pay_your_fvf_for_this_listing_before_leaving_feedback}"><a href="invoicepayment.php?cmd=view&txn=' . $unpaid_invoices . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_gray.gif" border="0" alt="{_please_pay_your_fvf_for_this_listing_before_leaving_feedback}" /></a></span></div>';
								$row2['nextstep'] = '<span class="gray">{_unpaid}:</span> <span class="blue">' . $pendinvoices . '</span>';
							}
							else
							{
								$provider_rated_buyer = $buyer_rated_provider = 0;
								// has the service provider provided any feedback to the buyer?
								if ($ilance->feedback->has_left_feedback($row2['user_id'], $_SESSION['ilancedata']['user']['userid'], $row2['project_id'], 'buyer'))
								{
									$provider_rated_buyer = 1;
									$row2['feedback'] = '<div align="center"><span title="{_you_have_already_provided_feedback_and_ratings}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_complete.gif" border="0" alt="{_you_have_already_provided_feedback_and_ratings}" /></span></div>';        
								}
								else
								{
									$row2['feedback'] = '<div align="center"><span title="{_submit_feedback_for} ' . fetch_user('username', $row2['user_id']) . '"><a href="' . HTTP_SERVER . $ilpage['feedback'] . '?cmd=_leave-feedback&amp;view=2&amp;returnurl={pageurl_urlencoded}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback.gif" border="0" alt="{_submit_feedback_for} ' . fetch_user('username', $row2['user_id']) . '" /></a></span></div>';
									$row2['nextstep'] = '{_leave_feedback}';
								}
								// did the buyer give feedback to the service provider?
								if ($ilance->feedback->has_left_feedback($_SESSION['ilancedata']['user']['userid'], $row2['user_id'], $row2['project_id'], 'seller'))
								{
									$buyer_rated_provider = 1;
								}
								else
								{
									$row2['nextstep'] = '<span class="smaller gray">{_pending_feedback}</span>';
								}
								if ($row2['fvf'] != "-")
								{
									//$row2['feedback'] = '<div align="center"><span title="{_please_pay_your_fvf_for_this_listing_before_leaving_feedback}"><a href="invoicepayment.php?cmd=view&id=' . $row2['fvfinvoiceid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_gray.gif" border="0" alt="{_please_pay_your_fvf_for_this_listing_before_leaving_feedback}" /></a></span></div>';
									//$row2['nextstep'] = '<span class="smaller gray">{_unpaid}: FVF</span>';
								}		
								if ($provider_rated_buyer AND $buyer_rated_provider)
								{
									// feedback exists: set as finished
									$row2['nextstep'] = '{_completed}';
								}
							}
						}
					}
					// #### ESCROW SYSTEM DISABLED
					else
					{
						// does auction have pending provider generated invoices unpaid?
						$sql_invchk = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "invoices
							WHERE user_id = '" . $row2['user_id'] . "'
								AND p2b_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
								AND projectid = '" . $row2['project_id'] . "'
								AND status = 'unpaid'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql_invchk) > 0)
						{
							$pendinvoices = '';
							while ($res_inv = $ilance->db->fetch_array($sql_invchk, DB_ASSOC))
							{
								$crypted = array('cmd' => 'view', 'txn' => $res_inv['transactionid']);
								$pendinvoices .= '<span class="blue"><a href="' . $ilpage['invoicepayment'] . '?crypted=' . encrypt_url($crypted) . '" title="{_this_invoice_has_not_been_paid_yet}">#' . $res_inv['invoiceid'] . '</a></span> ';
							}
							$unpaid_invoices = $ilance->db->fetch_field(DB_PREFIX . "invoices", "projectid = '" . intval($row2['project_id']) . "' AND user_id = '" . intval($row2['buyer_id']) . "' AND status = 'unpaid'", "transactionid");
							$row2['feedback'] = '<div><span title="{_please_pay_your_fvf_for_this_listing_before_leaving_feedback}"><a href="invoicepayment.php?cmd=view&txn=' . $unpaid_invoices . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_gray.gif" border="0" alt="{_please_pay_your_fvf_for_this_listing_before_leaving_feedback}" /></a></span></div>';
							$row2['nextstep'] = '{_unpaid}' . ': ' . $pendinvoices;
						}
						else
						{
							$provider_rated_buyer = $buyer_rated_provider = 0;
							// did service provider provide feedback to buyer?
							if ($ilance->feedback->has_left_feedback($row2['user_id'], $_SESSION['ilancedata']['user']['userid'], $row2['project_id'], 'buyer'))
							{
								$provider_rated_buyer = 1;
								if ($ilance->feedback->is_feedback_complete($row2['project_id']))
								{
									$row2['feedback'] = '<div align="center"><span title="{_feedback_complete}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="{_feedback_submitted__thank_you}" /></span></div>';
								}
								else
								{
									$row2['feedback'] = '<div align="center"><span title="{_feedback_submitted__thank_you}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_complete.gif" border="0" alt="{_feedback_submitted__thank_you}" /></span></div>';
								}
							}
							else
							{
								$row2['feedback'] = '<div align="center"><span title="{_submit_feedback_for} ' . fetch_user('username', $row2['user_id']) . '"><a href="' . HTTP_SERVER . $ilpage['feedback'] . '?cmd=_leave-feedback&amp;view=2&amp;returnurl={pageurl_urlencoded}" onmouseover="rollovericon(\'' . md5($row2['user_id'] . ':' . $_SESSION['ilancedata']['user']['userid'] . ':' . $row2['project_id'] . ':feedback') . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_rate.gif\')" onmouseout="rollovericon(\'' . md5($row2['user_id'] . ':' . $_SESSION['ilancedata']['user']['userid'] . ':' . $row2['project_id'] . ':feedback') . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback.gif\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback.gif" border="0" alt="" name="' . md5($row2['user_id'] . ':' . $_SESSION['ilancedata']['user']['userid'] . ':' . $row2['project_id'] . ':feedback') . '"/></a></span></div>';
								$row2['nextstep'] = '{_leave_feedback}';
							}
							// has service buyer provided feedback to the service provider?
							if ($ilance->feedback->has_left_feedback($for_user_id = $_SESSION['ilancedata']['user']['userid'], $from_user_id = $row2['user_id'], $project_id = $row2['project_id'], $type = 'seller'))
							{
								$buyer_rated_provider = 1;
							}
							else
							{
								$row2['nextstep'] = '<span class="smaller">{_pending_feedback}</span>';
							}
							if ($provider_rated_buyer AND $buyer_rated_provider)
							{
								// feedback exists: set as finished
								$row2['nextstep'] = '{_completed}';
							}
						}
					}
				}
				else if ($row2['bidstatus'] == 'declined' AND $ilance->GPC['bidsub'] != 'archived')
				{
					$row2['status'] = '{_declined}';
					$row2['nextstep'] = '-';
				}                                                                
				if (!isset($row2['nextstep']))
				{
					// show rebid link
					if ($row2['status'] == 'open')
					{
						$row2['nextstep'] = '<span class="blue"><a href="' . $ilpage['rfp'] . '?cmd=bid&amp;id=' . $row2['project_id'] . '">{_rebid}</a></span>';
					}
					else
					{
						$row2['nextstep'] = '-';
					}
				}
				$row2['bidamount'] = $ilance->currency->format($ilance->db->fetch_field(DB_PREFIX . $table, $field ." = '" . $row2['bid_id'] . "'", "bidamount"), $row2['currencyid']);
				$row2['measure'] = $ilance->auction->construct_measure($row2['bidamounttype']);
				$row2['bidamounttype'] = $ilance->auction->construct_bidamounttype($row2['bidamounttype']);                                                                
				$row2['job_title'] = '<a href="' . HTTP_SERVER . $ilpage['rfp'] . '?id=' . $row2['project_id'] . '" title="{_the_title_of_the_service_auction_you_are_bidding_on}">' . print_string_wrap(stripslashes($row2['project_title']), '45') . '</a>';
				$row2['icons'] = $ilance->auction->auction_icons($row2);
				$row2['date_starts'] = isset($row2['date_starts']) ? print_date($row2['date_starts']) : '';
				$row2['timeleft'] = (($row2['close_date'] != '0000-00-00 00:00:00' AND $row2['close_date'] < DATETIME24H)) ? '{_ended}' : $ilance->auction->calculate_time_left($row2['date_starts'], $row2['starttime'], $row2['mytime']);
				$row2['paymethod'] = $ilance->payment->print_fixed_payment_method($row2['winnermarkedaspaidmethod'], false);
			}
			$row2['class'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
			$show['no_service_bidding_activity'] = false;
			$show['bidpulldownmenu'] = true;
			//BIDS
			if (isset($bids_active))
			{
				$where = " AND bidstate != 'retracted' AND bidstate != 'archived' AND bidstatus != 'declined'";
			}
			else if (isset($bids_delisted))
			{
				$where = " AND bidstate != 'retracted' AND bidstate != 'archived' AND bidstatus = 'declined'";
			}
			else if (isset($bids_retracted))
			{
				$where = " AND bidstate = 'retracted' AND bidstatus != 'declined'";
			}
			else if (isset($bids_expired))
			{
				$where = " AND bidstate != 'retracted' AND bidstate != 'archived' AND bidstatus != 'declined'";
			}
			else if (isset($bids_archived))
			{
				$where = " AND bidstate = 'archived'";
			}
			else if (isset($bids_invited))
			{
				$show['bidpulldownmenu'] = false;
				$where = '';
			}
			else
			{
				$where = '';
				$show['bidpulldownmenu'] = false;
			}
			$sqlbids = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . $table . "
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND project_id = '" . $row2['project_id'] . "'
					$where
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sqlbids) > 0)
			{
				$row_count_bids = 0;
				while ($rows = $ilance->db->fetch_array($sqlbids, DB_ASSOC))
				{
					$rows['bids_id'] = ($bidgrouping == true) ? $rows['bid_id'] : $rows['id'];
					$rows['amount'] = $ilance->currency->format($rows['bidamount'], $row2['currencyid']);
					$rows['measure'] = $ilance->auction->construct_measure($rows['bidamounttype']);
					$rows['delivery'] = $rows['estimate_days'] . ' ' . $ilance->auction->construct_measure($rows['bidamounttype']);
					$rows['bidamounttype'] = $ilance->auction->construct_bidamounttype($rows['bidamounttype']);
					$rows['bid_datetime'] = print_date($rows['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
					$rows['custom_bid_fields'] = $ilance->bid_fields->construct_bid_fields($cid, $row2['project_id'], 'output1', 'service', $rows['bids_id'], false);
					$rows['paymethod2'] = $ilance->payment->print_fixed_payment_method($rows['winnermarkedaspaidmethod'], false);
					if ($rows['paymethod2'] == '')
					{
						$rows['paymethod2'] = '<span class="smaller">-</span>';
					}
					$rows['actions'] = '<input type="checkbox" name="bidid[]" id="' . $row2['project_state'] . 'bid_' . $rows['bids_id'] . '" value="' . $rows['bids_id'] . '|' . $row2['project_id'] . '" />';
					$rows['bidattach'] = '-';
					$sql_attachments = $ilance->db->query("
						SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref,DATE_ADD(date, INTERVAL + '2' MINUTE) as date1
						FROM " . DB_PREFIX . "attachment
						WHERE attachtype = 'bid'
							AND project_id = '" . $row2['project_id'] . "'
							AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
							AND visible = '1'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql_attachments) > 0)
					{
						$bidattach = '';
						$c = 1;
						while ($res = $ilance->db->fetch_array($sql_attachments, DB_ASSOC))
						{
							if ($res['date'] < $rows['date_added'] AND $res['date1'] > $rows['date_added'])
							{
								$bidattach .= '<div class="smaller blue" style="padding-bottom:3px" title="' . handle_input_keywords($res['filename']) . '">' . $c . '. <a href="' . HTTP_SERVER . $ilpage['attachment'] . '?id=' . $res['filehash'] . '" target="_blank">' . print_string_wrap(handle_input_keywords($res['filename'])) . '</a></div>';
								$c++;
							}
						}
						$rows['bidattach'] = $bidattach;
					}
					$rows['class2'] = ($row_count_bids % 2) ? 'alt2' : 'alt1';
					$GLOBALS['service_selling_bidding_activity' . $row2['project_id']][] = $rows;
					$row_count_bids++;
				}
				$row2['bids'] = !empty($where) ? $row_count_bids : $row2['bids'];	
			}
			else
			{
				$GLOBALS['no_service_buying_bidding_activity' . $row['project_id']] = 1;				
			}
			$row_count2++;
			$service_bidding_activity[] = $row2;
		}
	}
	else
	{
		$show['no_service_bidding_activity'] = true;
		$show['bidpulldownmenu'] = false;
	}
	unset($SQL3, $SQL4, $result2, $row2);
}
// #### build query string for prev / next controls
if (!empty($ilance->GPC) AND is_array($ilance->GPC))
{
	foreach ($ilance->GPC as $key => $value)
	{
		if ($key != 'page' AND $key != 'pp')
		{
			if (!isset($searchquery))
			{
				$searchquery = '?' . $key . '=' . $value;
			}
			else
			{
				$searchquery .= '&amp;' . $key . '=' . $value;
			}
		}        
	}
	$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], intval($ilance->GPC['page']), $counter, $ilpage['selling'] . $searchquery);
}
if (!empty($ilance->GPC) AND is_array($ilance->GPC))
{
	foreach ($ilance->GPC as $key => $value)
	{
		if ($key != 'p2' AND $key != 'pp')
		{
			if (!isset($searchquery))
			{
				$searchquery = '?' . $key . '=' . $value;
			}
			else
			{
				$searchquery .= '&amp;' . $key . '=' . $value;
			}
		}        
	}
	$prevnext2 = print_pagnation($number2, $ilconfig['globalfilters_maxrowsdisplay'], intval($ilance->GPC['p2']), $counter2, $ilpage['selling'] . $searchquery, 'p2');                        
}
if (!empty($ilance->GPC) AND is_array($ilance->GPC))
{
	foreach ($ilance->GPC AS $key => $value)
	{
		if ($key != 'p4' AND $key != 'pp')
		{
			if (!isset($searchquery))
			{
				$searchquery = '?' . $key . '=' . $value;
			}
			else
			{
				$searchquery .= '&amp;' . $key . '=' . $value;
			}
		}        
	}
}
$page = isset($ilance->GPC['page']) ? intval($ilance->GPC['page']) : 1;
$sub = isset($ilance->GPC['sub']) ? $ilance->GPC['sub'] : '';
$bidsub = isset($ilance->GPC['bidsub']) ? $ilance->GPC['bidsub'] : '';
if (empty($ilance->GPC['bidsub']) OR isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] != 'retracted')
{
	$headinclude .= '<script type="text/javascript">
<!--
function showPrompt()
{
	if (document.servicebids.bidcmd[1].selected == true)
	{
		var prompttext = ilance_prompt(phrase[\'_please_enter_the_reason_for_retracting_the_selected_bids\']);
		if (prompttext != null && prompttext != false && prompttext != \'\')
		{
			fetch_js_object(\'bidretractreason\').value = prompttext;
			return true;   
		}
		else
		{
			if (prompttext == null || prompttext == false)
			{
				alert_js(phrase[\'_in_order_to_retract_one_or_more_bids_placed_you_must_provide\']);
			}
			
			return false;
		}
	}
}
//-->
</script>';
}
if (isset($ilance->GPC['bidsub']) AND ($ilance->GPC['bidsub'] == 'retracted' OR $ilance->GPC['bidsub'] == 'archived'))
{
	$show['bidpulldownmenu'] = false;
}
$pprint_array = array('pp2_pulldown','displayorder2_pulldown','orderby2_pulldown','period2_pulldown','pp_pulldown','period_pulldown','displayorder_pulldown','pics_pulldown','orderby_pulldown','sub','page','bidsub','producttabs','servicetabs','bidprevnext','activebids','awardedbids','archivedbids','declinedbids','invitedbids','expiredbids','serviceescrow','activeitems','archiveditems','delisteditems','expireditems','pendingitems','productescrow','highbidder','highbidderid','highest','php_self','searchquery','p_id','rfpvisible','countdeclined','prevnext','prevnext2','redirect','referer','keyw','viewx','formatx','pricex','highbidamountx','project_idx','keyw_sell','keyw_bid');

($apihook = $ilance->api('selling_activity_end')) ? eval($apihook) : false;

$ilance->template->fetch('main', 'selling_activity.html');
$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
$ilance->template->parse_loop('main', 'product_selling_activity', true);
$ilance->template->parse_loop('main', 'service_bidding_activity', true);
if (!isset($product_selling_activity))
{
	$product_selling_activity = array();
}
@reset($product_selling_activity);
while ($i = @each($product_selling_activity))
{
	$ilance->template->parse_loop('main', 'product_selling_bidding_activity' . $i['value']['project_id'], true);
	$ilance->template->parse_loop('main', 'purchase_now_activity' . $i['value']['project_id'], true);
	
	($apihook = $ilance->api('while_selling_activity_end')) ? eval($apihook) : false;
}
if (!isset($service_bidding_activity))
{
	$service_bidding_activity = array();
}
@reset($service_bidding_activity);
while ($i = @each($service_bidding_activity))
{
	$ilance->template->parse_loop('main', 'service_selling_bidding_activity' . $i['value']['project_id'], true);
}
$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', $pprint_array);
exit();

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>