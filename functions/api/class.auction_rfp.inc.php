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
if (!class_exists('auction'))
{
	exit;
}

/**
* Class to handle inserting new product or service auctions within the database.
*
* @package      iLance\Auction\RFP
* @version      4.0.0.8059
* @author       ILance
*/
class auction_rfp extends auction
{
	/**
	* Function to create a new randomly generated auction id number.
	*
	* @return      integer       auction id number
	*/
	function construct_new_auctionid()
	{
		global $ilance, $ilconfig;
		$rfpid = rand(1, 9) . mb_substr(time(), -7, 10);
		$sql = $ilance->db->query("
			SELECT project_id
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . intval($rfpid) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$rfpid = rand(1, 9) . mb_substr(time(), -6, 10);
			$sql = $ilance->db->query("
				SELECT project_id
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . intval($rfpid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$rfpid = rand(1, 9) . mb_substr(time(), -8, 10);
				$sql = $ilance->db->query("
					SELECT project_id
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . intval($rfpid) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$rfpid = rand(1, 9) . mb_substr(time(), -8, 10);
					return $rfpid;
				}
				else
				{
					return $rfpid;
				}
			}
			else
			{
				return $rfpid;
			}
		}
		else
		{
			return $rfpid;
		}
	}
    
	/**
	* Function to create a new randomly generated auction id number for bulk upload.
	*
	* @return      integer       auction id number
	*/
	function construct_new_auctionid_bulk()
	{
		global $ilance, $ilconfig;
		do
		{
			$rfpid = mt_rand(1, 999) . substr(microtime(), 2, 5);
			$sql = $ilance->db->query("
				SELECT project_id
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . intval($rfpid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
		}
		while ($ilance->db->num_rows($sql) > 0);
		return $rfpid;
	}
    
	/**
	* Function to insert a new service auction
	*
	* @param       integer       user id
	* @param       string        project type (reverse/forward)
	* @param       string        status (open/draft)
	* @param       string        auction state (service/product)
	* @param       integer       category id
	* @param       integer       rfp id (custom auction id)
	* @param       string        auction title
	* @param       string        auction description
	* @param       string        auction description video embed
	* @param       string        auction additional info
	* @param       string        auction keywords/tags
	* @param       array         array holding custom questions answered
	* @param       array         array holding profile answers
	* @param       bool          filter option: is bidding privacy enabled
	* @param       string        filter answer: bidding privacy filter answer
	* @param       bool          filter option: is budget filter is enalbed
	* @param       string        filter answer: budget filter answer
	* @param       string        filtered auction type
	* @param       bool          filter option: is escrow being used for this project?
	* @param       bool          filter option: are direct gateway payments being used for this project? (default 0)
	* @param       bool          filter option: are offline payment methods being used for this project?
	* @param       string        payment method defined by the project buyer
	* @param       string        payment method gateway selected by the project buyer
	* @param       string        payment method gateway email address entered by the project buyer
	* @param       string        project details (public, realtime, invite_only)
	* @param       string        bid details
	* @param       array         invitation list (external emailed users)
	* @param       string        invitation message
	* @param       array         invited registered members array
	* @param       integer       year (used for start of year for realtime auction only)
	* @param       integer       month (used for start of month for realtime auction only)
	* @param       integer       day (used for start of day for realtime auction only)
	* @param       integer       hour (used for start of hour for realtime auction only)
	* @param       integer       min (used for start of min for realtime auction only)
	* @param       integer       sec (used for start of sec for realtime auction only)
	* @param       integer       duration (1 - 30) usually handling (days, hours or minutes) answer
	* @param       string        duration unit (D, H, M)
	* @param       integer       filtered rating answer
	* @param       string        filtered country answer
	* @param       string        filtered state answer
	* @param       string        filtered city answer
	* @param       string        filtered zip code answer
	* @param       integer       filtered rating by answer
	* @param       bool          filter by country?
	* @param       bool          filter by state?
	* @param       bool          filter by city?
	* @param       bool          filter by zip code?
	* @param       bool          filter by underage disabled?
	* @param       bool          filter by business number requirement?
	* @param       bool          filter public board enabled?
	* @param       array         auction upsell enhancements array
	* @param       bool          saving auction in draft mode? (default no)
	* @param       string        service location city
	* @param       string        service location state/province
	* @param       string        service location zip or postal code
	* @param       string        service location country 
	* @param       bool          skip all email process? (default no) - useful to use this function as API and to not send 1000's of emails if 1000's of auctions are added.
	* @param       mixed         api custom hooks
	* @param       bool          is bulk upload? (default false)
	* @param       integer       currency id
	*
	* @return      nothing
	*/
	function insert_service_auction($userid = 0, $project_type = 'reverse', $status = 'open', $project_state = 'service', $cid = 0, $rfpid = 0, $project_title = '', $description = '', $description_videourl = '', $additional_info = '', $keywords = '', $custom = array (), $profileanswer = array (), $filter_bidtype, $filtered_bidtype, $filter_budget, $filtered_budgetid, $filtered_auctiontype, $filter_escrow, $filter_gateway = 0, $filter_offline = 0, $paymethod = array (), $paymethodoptions = array (), $paymethodoptionsemail = array (), $project_details, $bid_details, $invitelist, $invitemessage, $invitedmember, $year, $month, $day, $hour, $min, $sec, $duration, $duration_unit, $filtered_rating = 1, $filtered_country = '', $filtered_state = '', $filtered_city = '', $filtered_zip = '', $filter_rating, $filter_country, $filter_state, $filter_city, $filter_zip, $filter_bidlimit, $filtered_bidlimit, $filter_underage, $filter_businessnumber, $filter_publicboard = 0, $enhancements = array (), $saveasdraft = 0, $city = '', $state = '', $zipcode = '', $country = '', $skipemailprocess = 0, $apihookcustom = array (), $isbulkupload = false, $currencyid = 0)
	{
		global $ilance, $ilconfig, $ilpage, $phrase, $url, $area_title, $page_title;
		$sql = $ilance->db->query("
			SELECT user_id
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . intval($rfpid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) == 0)
		{
			if ($isbulkupload == false)
			{
				// #### PROCESS CUSTOM PROJECT ANSWERS #########
				if (isset($custom) AND is_array($custom))
				{
					// process our answer input and store them into the datastore
					$ilance->auction_post->process_custom_questions($custom, $rfpid, 'service');
				}
				// #### PROCESS CUSTOM PROFILE ANSWER FILTERS
				if (isset($profileanswer) AND is_array($profileanswer))
				{
					// process our answer input and store them into the datastore
					$ilance->profile_questions->insert_profile_answers($profileanswer, $rfpid);
				}
			}
			$featured = isset($enhancements['featured']) ? (int) $enhancements['featured'] : 0;
			$featured_searchresults = isset($enhancements['featured_searchresults']) ? (int) $enhancements['featured_searchresults'] : 0;
			$highlite = isset($enhancements['highlite']) ? (int) $enhancements['highlite'] : 0;
			$bold = isset($enhancements['bold']) ? (int) $enhancements['bold'] : 0;
			$autorelist = isset($enhancements['autorelist']) ? (int) $enhancements['autorelist'] : 0;
			$featured_date = ($featured) ? DATETIME24H : '0000-00-00 00:00:00';
			$gtc = 0;
			if ($duration == 'GTC')
			{
				$duration = 30;
				$gtc = 1;
			}
			else
			{
				$duration = intval($duration);
			}
			switch ($duration_unit)
			{
				case 'D':
				{
					$moffset = $duration * 86400;
					break;
				}
				case 'H':
				{
					$moffset = $duration * 3600;
					break;
				}
				case 'M':
				{
					$moffset = $duration * 60;
					break;
				}
			}
			if ($project_details == 'public' OR $project_details == 'invite_only' OR empty($project_details))
			{
				// starts now
				$start_date = DATETIME24H;
			}
			else if ($project_details == 'realtime')
			{
				// starts now or sometime in the future (scheduled by user)
				$start_date = intval($year) . '-' . intval($month) . '-' . intval($day) . ' ' . intval($hour) . ':' . intval($min) . ':' . intval($sec);
			}
			$end_date = date("Y-m-d H:i:s", (strtotime($start_date) + $moffset));
			// #### HANDLE AUCTION MODERATION LOGIC ################
			// even though auction moderation might be enabled, we'll make this
			// visible so the user will not see his/her auction in the pending rfp queue                    
			if ($ilconfig['moderationsystem_disableauctionmoderation'])
			{
				$visible = '1';
			}
			else
			{
				$visible = '0';
				if ($status == 'draft')
				{
					$visible = '1';
				}
			}
			$budgetgroup = $this->fetch_category_budgetgroup($cid);
			$project_title = handle_input_keywords(strip_tags($project_title));
			$additional_info = handle_input_keywords(strip_tags($additional_info));
			$description_videourl = handle_input_keywords(strip_tags($description_videourl));
			$keywords = handle_input_keywords(strip_tags($keywords));
			if ($currencyid == 0)
			{
				$currencyid = $ilconfig['globalserverlocale_defaultcurrency'];
			}
			$countryid = ($country != '') ? fetch_country_id($country, $_SESSION['ilancedata']['user']['slng']) : fetch_country_id($ilconfig['registrationdisplay_defaultcountry'], $_SESSION['ilancedata']['user']['slng']);
			$ishtml = can_post_html($userid) ? 1 : 0;
			// api hook usage only
			$query_field_info = $query_field_data = '';
	    
			($apihook = $ilance->api('insert_service_auction_query_fields')) ? eval($apihook) : false;
	    
			// build the service auction in the datastore
			$ilance->db->query("
				INSERT INTO " . DB_PREFIX . "projects
				(id,
				project_id,
				cid,
				project_title,
				description,
				ishtml,
				description_videourl,
				additional_info,
				date_added,
				date_starts,
				date_end,
				gtc,
				user_id,
				visible,
				budgetgroup,
				status,
				project_details,
				project_type,
				project_state,
				bid_details,
				filter_rating,
				filter_country,
				filter_state,
				filter_city,
				filter_zip,
				filter_underage,
				filter_businessnumber,
				filter_bidtype,
				filter_budget,
				filter_escrow,
				filter_gateway,
				filter_offline,
				filter_publicboard,
				filtered_rating,
				filtered_country,
				filtered_state,
				filtered_city,
				filtered_zip,
				filter_bidlimit,
				filtered_bidlimit,
				filtered_bidtype,
				filtered_budgetid,
				filtered_auctiontype,
				featured,
				featured_date,
				featured_searchresults,
				highlite,
				bold,
				autorelist,
				paymethod,
				paymethodoptions,
				paymethodoptionsemail,
				keywords,
				countryid,
				country,
				state,
				city,
				zipcode,
				currencyid,
				$query_field_info
				updateid)
				VALUES(
				NULL,
				'" . intval($rfpid) . "',
				'" . intval($cid) . "',
				'" . $ilance->db->escape_string($project_title) . "',
				'" . $ilance->db->escape_string($description) . "',
				'" . $ilance->db->escape_string($ishtml) . "',
				'" . $ilance->db->escape_string($description_videourl) . "',
				'" . $ilance->db->escape_string($additional_info) . "',
				'" . DATETIME24H . "',
				'" . $ilance->db->escape_string($start_date) . "',
				'" . $ilance->db->escape_string($end_date) . "',
				'" . $gtc . "',
				'" . intval($userid) . "',
				'" . $visible . "',
				'" . $ilance->db->escape_string($budgetgroup) . "',
				'" . $ilance->db->escape_string($status) . "',
				'" . $ilance->db->escape_string($project_details) . "',
				'" . $ilance->db->escape_string($project_type) . "',
				'" . $ilance->db->escape_string($project_state) . "',
				'" . $ilance->db->escape_string($bid_details) . "',
				'" . intval($filter_rating) . "',
				'" . intval($filter_country) . "',
				'" . intval($filter_state) . "',
				'" . intval($filter_city) . "',
				'" . intval($filter_zip) . "',
				'" . intval($filter_underage) . "',
				'" . intval($filter_businessnumber) . "',
				'" . intval($filter_bidtype) . "',
				'" . intval($filter_budget) . "',
				'" . intval($filter_escrow) . "',
				'" . intval($filter_gateway) . "',
				'" . intval($filter_offline) . "',
				'" . intval($filter_publicboard) . "',
				'" . $ilance->db->escape_string($filtered_rating) . "',
				'" . $ilance->db->escape_string($filtered_country) . "',
				'" . $ilance->db->escape_string($filtered_state) . "',
				'" . $ilance->db->escape_string($filtered_city) . "',
				'" . $ilance->db->escape_string($filtered_zip) . "',
				'" . intval($filter_bidlimit) . "',
				'" . intval($filtered_bidlimit) . "',
				'" . $ilance->db->escape_string($filtered_bidtype) . "',
				'" . $ilance->db->escape_string($filtered_budgetid) . "',
				'" . $ilance->db->escape_string($filtered_auctiontype) . "',
				'" . intval($featured) . "',
				'" . $ilance->db->escape_string($featured_date) . "',
				'" . $ilance->db->escape_string($featured_searchresults) . "',
				'" . intval($highlite) . "',
				'" . intval($bold) . "',
				'" . intval($autorelist) . "',
				'" . $ilance->db->escape_string(serialize($paymethod)) . "',
				'" . $ilance->db->escape_string(serialize($paymethodoptions)) . "',
				'" . $ilance->db->escape_string(serialize($paymethodoptionsemail)) . "',
				'" . $ilance->db->escape_string($keywords) . "',
				'" . intval($countryid) . "',
				'" . $ilance->db->escape_string($country) . "',
				'" . $ilance->db->escape_string($state) . "',
				'" . $ilance->db->escape_string($city) . "',
				'" . $ilance->db->escape_string($zipcode) . "',
				'" . intval($currencyid) . "',
				$query_field_data
				'0')
			", 0, null, __FILE__, __LINE__);
			if ($isbulkupload == false AND $status != 'draft')
			{
				// #### INSERTION FEES IN THIS CATEGORY ################
				// if this fee cannot be paid from online account balance, it will end up in rfp pending area waiting to be paid
				$ilance->auction_fee->process_insertion_fee_transaction($cid, 'service', '', $rfpid, $userid, $filter_budget, $filtered_budgetid);
				$ilance->auction_fee->process_listing_enhancements_transaction($enhancements, $userid, $rfpid, 'insert', 'service');
				$ilance->auction_fee->process_listing_duration_transaction($cid, $duration, $duration_unit, $userid, $rfpid, 'service', false, $gtc);
			}
			$sql_attach = $ilance->db->query("SELECT attachtype FROM " . DB_PREFIX . "attachment WHERE project_id = '" . $rfpid . "'");
			if ($ilance->db->num_rows($sql_attach) > 0)
			{
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "projects 
					SET hasimage = '1'
					WHERE project_id = '" . $rfpid . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
			}
			// #### OTHER DETAILS ##################################
			$category = strip_tags($ilance->categories->recursive($cid, 'service', $_SESSION['ilancedata']['user']['slng'], 1, '', 0));
			$budget = $this->construct_budget_overview($cid, $filtered_budgetid);
			$status = fetch_auction('status', $rfpid);
			($apihook = $ilance->api('auction_submit_end')) ? eval($apihook) : false;
	    
			// #### AUCTION MODERATION #############################
			if ($ilconfig['moderationsystem_disableauctionmoderation'])
			{
				if ($status == 'frozen')
				{
					$area_title = '{_new_service_auctions_posted_menu}';
					$page_title = SITE_NAME . ' - {_new_service_auctions_posted_menu}';
					if ($isbulkupload == false)
					{
						// handle invitation logic for non moderated draft auctions (this will not send email out yet, only log it)
						$this->dispatch_invited_members_email($invitedmember, 'service', $rfpid, $userid, 1);
						// did this buyer manually enter email addresses to invite users to bid?
						$this->dispatch_external_members_email('service', $rfpid, $userid, $project_title, $bid_details, $end_date, $invitelist, $invitemessage, 1);
					}
					if ($skipemailprocess == 0)
					{
						$pprint_array = array ('url', 'login_include');
						$ilance->template->fetch('main', 'listing_reverse_auction_complete_frozen.html');
						$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
						$ilance->template->parse_if_blocks('main');
						$ilance->template->pprint('main', $pprint_array);
						exit();
					}
				}
				// #### OPEN STATUS (NOT DRAFT) ################
				else if ($status == 'open')
				{
					// #### REFERRAL SYSTEM TRACKER ########
					// we'll track that this user has posted a valid auction from the AdminCP.
					$ilance->referral->update_referral_action('postauction', intval($userid));
					// are we constructing new auction from an API call?
					if ($skipemailprocess == 0)
					{
						// no api being used, proceed to dispatching email
						$ilance->email->mail = fetch_user('email', $userid);
						$ilance->email->slng = fetch_user_slng($userid);
						$ilance->email->get('new_auction_open_for_bids');
						$ilance->email->set(array (
							'{{username}}' => fetch_user('username', $userid),
							'{{projectname}}' => $project_title,
							'{{project_title}}' => $project_title,
							'{{description}}' => $description,
							'{{bids}}' => '0',
							'{{category}}' => $category,
							'{{budget}}' => $budget,
							'{{p_id}}' => intval($rfpid),
							'{{details}}' => ucfirst($project_details),
							'{{privacy}}' => ucfirst($bid_details),
							'{{closing_date}}' => print_date($end_date, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
						));
						$ilance->email->send();
					}
					$area_title = '{_new_service_auctions_posted_menu}';
					$page_title = SITE_NAME . ' - {_new_service_auctions_posted_menu}';
					if ($isbulkupload == false)
					{
						// did this buyer actually visit the search pages and profile menus for providers and invited them to this project?
						if (is_array($invitedmember) AND count($invitedmember) > 0)
						{
							$this->dispatch_invited_members_email($invitedmember, 'service', $rfpid, $userid);
						}
						// did this buyer manually enter email addresses to invite users outside the marketplace to bid?
						$this->dispatch_external_members_email('service', $rfpid, $userid, $project_title, $bid_details, $end_date, $invitelist, $invitemessage);
					}
					// rebuild category count
					$ilance->categories->build_category_count($cid, 'add', "insert_service_auction(): adding increment count cid $cid");
					$url = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('serviceauction', 0, $rfpid, $project_title, '{_view_auction_here}', 0, '', 0, 0) : '<a href="' . $ilpage['rfp'] . '?id=' . $rfpid . '">{_view_auction_here}</a>';
					// are we constructing new auction from an API call?
					if ($skipemailprocess == 0)
					{
						$ilance->email->mail = SITE_EMAIL;
						$ilance->email->slng = fetch_site_slng();
						$ilance->email->get('service_auction_posted_admin');
						$ilance->email->set(array (
							'{{buyer}}' => fetch_user('username', $userid),
							'{{project_title}}' => $project_title,
							'{{description}}' => strip_tags($description),
							'{{bids}}' => '0',
							'{{category}}' => $category,
							'{{budget}}' => $budget,
							'{{p_id}}' => intval($rfpid),
							'{{details}}' => ucfirst($project_details),
							'{{privacy}}' => ucfirst($bid_details),
							'{{closing_date}}' => print_date($end_date, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
							'{{rfp_url}}' => HTTP_SERVER . $ilpage['rfp'] . '?id=' . intval($rfpid)
						));
						$ilance->email->send();
						$ilance->template->fetch('main', 'listing_reverse_auction_complete.html');
						$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
						$ilance->template->parse_if_blocks('main');
						$ilance->template->pprint('main', array('url'));
						exit();
					}
				}
				// #### DRAFT MODE #############################
				else if ($status == 'draft')
				{
					$area_title = '{_new_service_auctions_posted_menu}';
					$page_title = SITE_NAME . ' - {_new_service_auctions_posted_menu}';
					if ($isbulkupload == false)
					{
						// handle invitation logic for non moderated draft auctions (this will not send email out yet, only log it)
						$this->dispatch_invited_members_email($invitedmember, 'service', $rfpid, $userid, 1);
						// did this buyer manually enter email addresses to invite users to bid?
						$this->dispatch_external_members_email('service', $rfpid, $userid, $project_title, $bid_details, $end_date, $invitelist, $invitemessage, 1);
					}
					$url = '<a href="' . HTTP_SERVER . $ilpage['buying'] . '?cmd=management&amp;sub=drafts">{_view_draft_auctions_here}</a>';
					// are we constructing new auction from an API call?
					if ($skipemailprocess == 0)
					{
						// no api being used, proceed to dispatching email
						$ilance->template->fetch('main', 'listing_reverse_auction_draft.html');
						$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
						$ilance->template->parse_if_blocks('main');
						$ilance->template->pprint('main', array('url'));
						exit();
					}
				}
			}
			// #### AUCTIONS ARE BEING MODERATED ###################
			else
			{
				$area_title = '{_new_service_auctions_posted_menu}';
				$page_title = SITE_NAME . ' - {_new_service_auctions_posted_menu}';
				if ($isbulkupload == false)
				{
					// handle invitation logic for moderated draft auctions (this will not send email out yet)
					$this->dispatch_invited_members_email('service', $rfpid, $userid, $dontsendemail = 1);
					// did this buyer manually enter email addresses to invite users to bid?
					$this->dispatch_external_members_email('service', $rfpid, $userid, $project_title, $bid_details, $end_date, $invitelist, $invitemessage, $dontsendemail = 1);
				}
				// do not send email if this is a draft as the user doesn't want it posted right now anyways
				// it will resend this email when they decide to make it public manually on their own
				if ($status == 'draft')
				{
					// todo: make url use seo if enabled
					$url = '<a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=drafts"><strong>{_view_draft_auctions_here}</strong></a>';
					// are we constructing new auction from an API call?
					if ($skipemailprocess == 0)
					{
						// no api being used, proceed to dispatching email
						$ilance->template->fetch('main', 'listing_reverse_auction_draft.html');
						$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
						$ilance->template->parse_if_blocks('main');
						$ilance->template->pprint('main', array('url'));
						exit();
					}
				}
				else
				{
					// are we constructing new auction from an API call?
					if ($skipemailprocess == 0)
					{
						// no api being used, proceed to dispatching email
						$ilance->email->mail = SITE_EMAIL;
						$ilance->email->slng = fetch_site_slng();
						$ilance->email->get('auction_moderation_admin');
						$ilance->email->set(array (
							'{{buyer}}' => $_SESSION['ilancedata']['user']['username'],
							'{{project_title}}' => $project_title,
							'{{description}}' => $description,
							'{{category}}' => $category,
							'{{budget}}' => $budget,
							'{{p_id}}' => intval($rfpid),
							'{{details}}' => ucfirst($project_details),
							'{{privacy}}' => ucfirst($bid_details),
							'{{closing_date}}' => print_date($end_date, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
							'{{moderated_tab_url}}' => HTTPS_SERVER_ADMIN . $ilpage['distribution']
						));
						$ilance->email->send();
					}
				}
				// todo: make url use seo if enabled
				$url = '<a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=rfp-pending"><strong>{_pending_auctions_menu}</strong></a>';
				// are we constructing new auction from an API call?
				if ($skipemailprocess == 0)
				{
					// no api being used, proceed to dispatching email
					$ilance->template->fetch('main', 'listing_reverse_auction_moderation.html');
					$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
					$ilance->template->parse_if_blocks('main');
					$ilance->template->pprint('main', array('url'));
					exit();
				}
			}
		}
	}
    
	/**
	* Function to insert a new product auction
	*
	* @param       integer       user id
	* @param       string        project type (reverse/forward)
	* @param       string        status (open/draft)
	* @param       string        auction state (service/product)
	* @param       integer       category id
	* @param       integer       rfp id (custom auction id)
	* @param       string        auction title
	* @param       string        auction description
	* @param       string        auction description video embed
	* @param       string        auction additional info
	* @param       string        auction keywords/tags
	* @param       array         array holding custom questions answered
	* @param       array         array holding profile answers
	* @param       string        filtered auction type
	* @param       string        start price
	* @param       string        project details (public, realtime, invite_only)
	* @param       string        bid details
	* @param       bool          filter by rating?
	* @param       bool          filter by country?
	* @param       bool          filter by state?
	* @param       bool          filter by city?
	* @param       bool          filter by zip code?
	* @param       integer       filtered rating answer
	* @param       string        filtered country answer
	* @param       string        filtered state answer
	* @param       string        filtered city answer
	* @param       string        filtered zip code answer
	* @param       string        item location city
	* @param       string        item location state/province
	* @param       string        item location zip or postal code
	* @param       string        item location country
	* @param       array         shipping information array
	* @param       bool          buy now?
	* @param       string        buy now price
	* @param       string        buy now qty available
	* @param       integer       buy now qty LOT (can only purchase entire buynow_qty not less)
	* @param       array         auction upsell enhancements array
	* @param       bool          using reserve price?
	* @param       string        reserve price
	* @param       bool          filter by underage disabled?
	* @param       bool          filter option: is escrow being used for this listing?
	* @param       bool          filter option: are gateway payment methods being used for this listing?
	* @param       bool          filter option: are offline payment methods being used for this listing?
	* @param       bool          filter public board enabled?
	* @param       array         invitation list (external emailed users)
	* @param       string        invitation message
	* @param       integer       year (used for start of year for realtime auction only)
	* @param       integer       month (used for start of month for realtime auction only)
	* @param       integer       day (used for start of day for realtime auction only)
	* @param       integer       hour (used for start of hour for realtime auction only)
	* @param       integer       min (used for start of min for realtime auction only)
	* @param       integer       sec (used for start of sec for realtime auction only)
	* @param       string        duration (1 - 30 and GTC) usually handling (days, hours or minutes) answer
	* @param       string        duration unit (D, H, M)
	* @param       string        payment method defined by the project buyer
	* @param       string        payment method gateway option selected by the seller
	* @param       string        payment method gateway email address selected by the seller
	* @param       bool          saving auction in draft mode? (default no)
	* @param       bool          return policy: returns accepted?
	* @param       integer       return policy: return within days
	* @param       string        return policy: return given as (default none)
	* @param       string        return policy: return shipping paid by
	* @param       string        return policy text
	* @param       bool          is donation associated (default 0 - false)
	* @param       integer       charity id of the doner associated (default 0)
	* @param       integer       donation percentage (default 0)
	* @param       bool          skip all email process? (default no) - useful to use this function as API and to not send 1000's of emails if 1000's of auctions are added.
	* @param       array         custom api hook (optional)
	* @param       bool          is bulk upload? (default false) - note: if true, insertion fees and enhancements will not be created
	* @param       string        string url to the sample bulk item photo
	* @param       integer       currency id
	* @param       integer       classified ads price
	* @param       string        classified ads phone number
	* @param       string        sku code
	* @param       string        upc code
	* @param       string        ean code
	* @param       string        part number
	* @param       string        model number
	* @param       string        sales tax state or province
	* @param       string        sales tax rate
	* @param       boolean       apply sales tax to shipping costs (default false)
	* 
	* @return      bool          Returns true or false when (skip all email process = true) otherwise this function returns HTML formatted text of actions occured.
	*/
	function insert_product_auction($userid = 0, $project_type = 'forward', $status = 'open', $project_state = 'product', $cid = 0, $rfpid = 0, $project_title = '', $description = '', $description_videourl = '', $additional_info = '', $keywords = '', $custom = array (), $profileanswer = array (), $filtered_auctiontype = '', $start_price = 0, $project_details = '', $bid_details = '', $filter_rating = 0, $filter_country = 0, $filter_state = 0, $filter_city = 0, $filter_zip = 0, $filter_businessnumber = '', $filtered_rating = '', $filtered_country = '', $filtered_state = '', $filtered_city = '', $filtered_zip = '', $city = '', $state = '', $zipcode = '', $country = '', $shipping = array (), $buynow = 0, $buynow_price = 0, $buynow_qty = 1, $buynow_qty_lot = 0, $items_per_lot = 1, $enhancements = array (), $reserve = 0, $reserve_price = 0, $filter_underage = 0, $filter_escrow = 0, $filter_gateway = 0, $filter_ccgateway = 0, $filter_offline = 0, $filter_publicboard = 0, $invitelist, $invitemessage, $year, $month, $day, $hour, $min, $sec, $duration, $duration_unit, $paymethod = array (), $paymethodcc = array (), $paymethodoptions = array (), $paymethodoptionsemail = array (), $saveasdraft = 0, $returnaccepted = 0, $returnwithin = 0, $returngivenas = 'none', $returnshippaidby = 'buyer', $returnpolicy = '', $donation = '', $charityid = 0, $donationpercentage = 0, $skipemailprocess = 0, $apihookcustom = array (), $isbulkupload = false, $sample = '', $currencyid = 0, $classified_price = 0, $classified_phone = '', $sku = '', $upc = '', $ean = '', $partnumber = '', $modelnumber = '', $salestaxstate = '', $salestaxrate = '0', $salestaxshipping = '0')
	{
		global $ilance, $ilconfig, $ilconfig, $ilpage, $phrase, $show;
		$sql = $ilance->db->query("
			SELECT user_id
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . intval($rfpid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) == 0)
		{
			// #### PROCESS CUSTOM SPECIFICS #######################                        
			if (isset($custom) AND is_array($custom))
			{
				// process our answer input and store them into the datastore
				$ilance->auction_post->process_custom_questions($custom, $rfpid, 'product');
			}
			if ($isbulkupload == false)
			{
				// #### PROCESS CUSTOM PROFILE FILTERS #################                        
				if (isset($profileanswer) AND is_array($profileanswer))
				{
					// process our answer input and store them into the datastore
					$ilance->auction_post->process_custom_profile_questions($profileanswer, $rfpid, $userid, 'product');
				}
			}
			if ($buynow > 0)
			{
				$enhancements['buynow'] = 1;
			}
			if ($reserve > 0)
			{
				$enhancements['reserve'] = 1;
			}
			$featured = isset($enhancements['featured']) ? (int) $enhancements['featured'] : 0;
			$highlite = isset($enhancements['highlite']) ? (int) $enhancements['highlite'] : 0;
			$bold = isset($enhancements['bold']) ? (int) $enhancements['bold'] : 0;
			$autorelist = isset($enhancements['autorelist']) ? (int) $enhancements['autorelist'] : 0;
			$featured_date = ($featured) ? DATETIME24H : '0000-00-00 00:00:00';
			$featured_searchresults = isset($enhancements['featured_searchresults']) ? (int) $enhancements['featured_searchresults'] : 0;
			$gtc = 0;
			if ($duration == 'GTC')
			{
				$duration = 30;
				$gtc = 1;
			}
			else
			{
				$duration = intval($duration);
			}
			switch ($duration_unit)
			{
				// days
				case 'D':
				{
					$moffset = $duration * 86400;
					break;
				}
				// hours
				case 'H':
				{
					$moffset = $duration * 3600;
					break;
				}
				// minutes
				case 'M':
				{
					$moffset = $duration * 60;
					break;
				}
			}
			// starts now or sometime in the future (scheduled by user)
			$start_date = ($project_details == 'realtime') ? intval($year) . '-' . intval($month) . '-' . intval($day) . ' ' . intval($hour) . ':' . intval($min) . ':' . intval($sec) : DATETIME24H;
			$end_date = date("Y-m-d H:i:s", (strtotime($start_date) + $moffset));
			// #### HANDLE AUCTION MODERATION LOGIC ################
			// even though auction moderation might be enabled, we'll make this
			// visible so the user will not see his/her auction in the pending rfp queue                    
			if ($ilconfig['moderationsystem_disableauctionmoderation'])
			{
				// moderation is disabled, make listing visible
				$visible = '1';
			}
			else
			{
				// moderation is enabled, make listing not visible
				$visible = '0';
				if ($status == 'draft')
				{
					$visible = '1';
				}
			}
			// strip unwanted <script> tags to prevent XSS
			$project_title = handle_input_keywords(strip_tags($project_title));
			$returnpolicy = handle_input_keywords(strip_tags($returnpolicy));
			$description_videourl = handle_input_keywords(strip_tags($description_videourl));
			$keywords = handle_input_keywords(strip_tags($keywords));
			$filtered_zip = !empty($filtered_zip) ? format_zipcode($filtered_zip) : '';
			// if we do not have a start price then start the bid at 1 cent
			if ($start_price <= 0)
			{
				$start_price = 0.01;
			}
			if ($filtered_auctiontype == 'fixed')
			{
				$start_price = $buynow_price;
			}
			$currentprice = $start_price;
			if ($currencyid == 0)
			{
				$currencyid = $ilconfig['globalserverlocale_defaultcurrency'];
			}
			$countryid = ($country != '') ? fetch_country_id($country, $_SESSION['ilancedata']['user']['slng']) : fetch_country_id($ilconfig['registrationdisplay_defaultcountry'], $_SESSION['ilancedata']['user']['slng']);
			$ishtml = can_post_html($userid) ? 1 : 0;
			$query_field_info = $query_field_data = '';
	    
			($apihook = $ilance->api('insert_product_auction_query_fields')) ? eval($apihook) : false;
	    
			$ilance->db->query("
				INSERT INTO " . DB_PREFIX . "projects
				(id,
				project_id,
				cid,
				description,
				ishtml,
				description_videourl,
				keywords,
				date_added,
				date_starts,
				date_end,
				gtc,
				user_id,
				visible,
				views,
				project_title,
				status,
				project_details,
				project_type,
				project_state,
				bid_details,
				filter_escrow,
				filter_gateway,
				filter_ccgateway,
				filter_offline,
				filter_rating,
				filter_country,
				filter_state,
				filter_city,
				filter_zip,
				filtered_rating,
				filtered_country,
				filtered_state,
				filtered_city,
				filtered_zip,
				filter_businessnumber,
				filter_underage,
				filter_publicboard,
				filtered_auctiontype,
				classified_phone,
				classified_price,
				buynow,
				buynow_price,
				buynow_qty,
				buynow_qty_lot,
				items_in_lot,
				reserve,
				reserve_price,
				bold,
				featured,
				featured_date,
				featured_searchresults,
				highlite,
				autorelist,
				startprice,
				paymethod,
				paymethodcc,
				paymethodoptions,
				paymethodoptionsemail,
				currentprice,
				returnaccepted,
				returnwithin,
				returngivenas,
				returnshippaidby,
				returnpolicy,
				donation,
				charityid,
				donationpercentage,
				countryid,
				country,
				state,
				city,
				zipcode,
				currencyid,
				sku,
				upc,
				ean,
				partnumber,
				modelnumber,
				salestaxstate,
				salestaxrate,
				salestaxshipping,
				$query_field_info
				updateid)
				VALUES(
				NULL,
				'" . intval($rfpid) . "',
				'" . intval($cid) . "',
				'" . $ilance->db->escape_string($description) . "',
				'" . $ilance->db->escape_string($ishtml) . "',
				'" . $ilance->db->escape_string($description_videourl) . "',
				'" . $ilance->db->escape_string($keywords) . "',
				'" . DATETIME24H . "',
				'" . $ilance->db->escape_string($start_date) . "',
				'" . $ilance->db->escape_string($end_date) . "',
				'" . intval($gtc) . "',
				'" . intval($userid) . "',
				'" . $ilconfig['moderationsystem_disableauctionmoderation'] . "',
				'0',
				'" . $ilance->db->escape_string($project_title) . "',
				'" . $ilance->db->escape_string($status) . "',
				'" . $ilance->db->escape_string($project_details) . "',
				'" . $ilance->db->escape_string($project_type) . "',
				'" . $ilance->db->escape_string($project_state) . "',
				'" . $ilance->db->escape_string($bid_details) . "',
				'" . intval($filter_escrow) . "',
				'" . intval($filter_gateway) . "',
				'" . intval($filter_ccgateway) . "',
				'" . intval($filter_offline) . "',
				'" . intval($filter_rating) . "',
				'" . intval($filter_country) . "',
				'" . intval($filter_state) . "',
				'" . intval($filter_city) . "',
				'" . intval($filter_zip) . "',
				'" . $ilance->db->escape_string($filtered_rating) . "',
				'" . $ilance->db->escape_string($filtered_country) . "',
				'" . $ilance->db->escape_string($filtered_state) . "',
				'" . $ilance->db->escape_string($filtered_city) . "',
				'" . $ilance->db->escape_string($filtered_zip) . "',
				'" . $ilance->db->escape_string($filter_businessnumber) . "',
				'" . $ilance->db->escape_string($filter_underage) . "',
				'" . $ilance->db->escape_string($filter_publicboard) . "',
				'" . $ilance->db->escape_string($filtered_auctiontype) . "',
				'" . $ilance->db->escape_string($classified_phone) . "',
				'" . $ilance->db->escape_string($classified_price) . "',
				'" . intval($buynow) . "',
				'" . $ilance->db->escape_string($buynow_price) . "',
				'" . intval($buynow_qty) . "',
				'" . intval($buynow_qty_lot) . "',
				'" . intval($items_per_lot) . "',
				'" . intval($reserve) . "',
				'" . $ilance->db->escape_string($reserve_price) . "',
				'" . intval($bold) . "',
				'" . intval($featured) . "',
				'" . $ilance->db->escape_string($featured_date) . "',
				'" . intval($featured_searchresults) . "',
				'" . intval($highlite) . "',
				'" . intval($autorelist) . "',
				'" . $ilance->db->escape_string($start_price) . "',
				'" . $ilance->db->escape_string(serialize($paymethod)) . "',
				'" . $ilance->db->escape_string(serialize($paymethodcc)) . "',
				'" . $ilance->db->escape_string(serialize($paymethodoptions)) . "',
				'" . $ilance->db->escape_string(serialize($paymethodoptionsemail)) . "',
				'" . $ilance->db->escape_string($currentprice) . "',
				'" . intval($returnaccepted) . "',
				'" . intval($returnwithin) . "',
				'" . $ilance->db->escape_string($returngivenas) . "',
				'" . $ilance->db->escape_string($returnshippaidby) . "',
				'" . $ilance->db->escape_string($returnpolicy) . "',
				'" . $ilance->db->escape_string($donation) . "',
				'" . intval($charityid) . "',
				'" . $ilance->db->escape_string($donationpercentage) . "',
				'" . intval($countryid) . "',
				'" . $ilance->db->escape_string($country) . "',
				'" . $ilance->db->escape_string($state) . "',
				'" . $ilance->db->escape_string($city) . "',
				'" . $ilance->db->escape_string($zipcode) . "',
				'" . intval($currencyid) . "',
				'" . $ilance->db->escape_string($sku) . "',
				'" . $ilance->db->escape_string($upc) . "',
				'" . $ilance->db->escape_string($ean) . "',
				'" . $ilance->db->escape_string($partnumber) . "',
				'" . $ilance->db->escape_string($modelnumber) . "',
				'" . $ilance->db->escape_string($salestaxstate) . "',
				'" . $ilance->db->escape_string($salestaxrate) . "',
				'" . intval($salestaxshipping) . "',
				$query_field_data
				'0')
			", 0, null, __FILE__, __LINE__);
			// #### SAVE SHIPPING DETAILS ##########################
			if (isset($shipping) AND is_array($shipping))
			{
				$ilance->shipping->save_item_shipping_logic($rfpid, $shipping);
			}
			// #### RELIST WATCHITEMS ##########################
			if (isset($ilance->GPC['relist_id']) AND !empty($ilance->GPC['relist_id']))
			{
				$this->relist_watchitems($ilance->GPC['relist_id'], $rfpid);
			}
			// #### INSERTION FEES IN THIS CATEGORY ################
			// this will generate insertion fee to be paid by the auction owner before listing is live
			// if seller has no funds in their account the auction will go into pending auction queue
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
			if ($isbulkupload == false AND $status != 'draft')
			{
				// #### INSERTION FEES IN THIS CATEGORY ################
				$ilance->auction_fee->process_insertion_fee_transaction($cid, 'product', $ifbaseamount, $rfpid, $userid, 0, 0, false, array (), intval($currencyid));
				$ilance->auction_fee->process_listing_enhancements_transaction($enhancements, $userid, $rfpid, 'insert', 'product');
				$ilance->auction_fee->process_listing_duration_transaction($cid, $duration, $duration_unit, $userid, $rfpid, 'product', false, $gtc);
			}
			$sql_attach = $ilance->db->query("SELECT attachtype FROM " . DB_PREFIX . "attachment WHERE project_id = '" . $rfpid . "'");
			if ($ilance->db->num_rows($sql_attach) > 0)
			{
				$hasimage = $hasimageslideshow = $hasdigitalfile = 0;
				while ($res_attach = $ilance->db->fetch_array($sql_attach, DB_ASSOC))
				{
					if ($res_attach['attachtype'] == 'itemphoto')
					{
						$hasimage = 1;
					}
					else if ($res_attach['attachtype'] == 'slideshow')
					{
						$hasimageslideshow = 1;
					}
					else if ($res_attach['attachtype'] == 'digital')
					{
						$hasdigitalfile = 1;
					}
				}
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "projects 
					SET hasimage = '" . $hasimage . "',
					hasimageslideshow = '" . $hasimageslideshow . "',
					hasdigitalfile = '" . $hasdigitalfile . "'
					WHERE project_id = '" . $rfpid . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
			}
			// #### OTHER DETAILS ##################################
			$category = strip_tags($ilance->categories->recursive($cid, 'product', fetch_user_slng($userid), 1, '', 0));
			$status = fetch_auction('status', $rfpid);
	    
			($apihook = $ilance->api('product_auction_submit_end')) ? eval($apihook) : false;
	    
			// #### AUCTION MODERATION #############################
			if ($ilconfig['moderationsystem_disableauctionmoderation'])
			{
				if ($status == 'frozen')
				{
					if ($isbulkupload == false)
					{
						// did this buyer actually visit the search pages and profile menus for providers and added them for this project?
						$this->dispatch_invited_members_email('product', $rfpid, $userid, $dontsendemail = 1);
						// did this buyer manually enter email addresses to invite users to bid?
						$this->dispatch_external_members_email('product', $rfpid, $userid, $project_title, $bid_details, $end_date, $invitelist, $invitemessage, $dontsendemail = 1);
					}
					// are we constructing new auction from an API call?
					if ($skipemailprocess == 0)
					{
						// email admin
						$ilance->email->mail = SITE_EMAIL;
						$ilance->email->slng = fetch_site_slng();
						$ilance->email->get('product_auction_posted_admin_frozen');
						$ilance->email->set(array (
							'{{buyer}}' => fetch_user('username', $userid),
							'{{project_title}}' => $project_title,
							'{{description}}' => $description,
							'{{bids}}' => '0',
							'{{category}}' => $category,
							'{{minimum_bid}}' => $ilance->currency->format($start_price, $currencyid),
							'{{p_id}}' => $rfpid,
							'{{details}}' => ucfirst($project_details),
							'{{privacy}}' => ucfirst($bid_details),
							'{{closing_date}}' => print_date($end_date, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
							'{{site_name}}' => SITE_NAME,
						));
						$ilance->email->send();
						// email user
						$ilance->email->mail = fetch_user('email', $userid);
						$ilance->email->slng = fetch_user_slng($userid);
						$ilance->email->get('new_product_auction_open_for_bids_frozen');
						$ilance->email->set(array (
							'{{username}}' => fetch_user('username', $userid),
							'{{projectname}}' => stripslashes($project_title),
							'{{project_title}}' => $project_title,
							'{{description}}' => $description,
							'{{category}}' => $category,
							'{{p_id}}' => $rfpid,
							'{{closing_date}}' => print_date($end_date, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
							'{{site_name}}' => SITE_NAME,
						));
						$ilance->email->send();
						$ilance->template->fetch('main', 'listing_forward_auction_complete_frozen.html');
						$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
						$ilance->template->parse_if_blocks('main');
						$ilance->template->pprint('main', array('url'));
						exit();
					}
				}
				else
				{
					// are we constructing new auction from an API call?
					if ($skipemailprocess == 0)
					{
						if ($status == 'draft')
						{
							// email admin
							$ilance->email->mail = SITE_EMAIL;
							$ilance->email->slng = fetch_site_slng();
							$ilance->email->get('product_auction_posted_admin_saved');
							$ilance->email->set(array (
								'{{buyer}}' => fetch_user('username', $userid),
								'{{project_title}}' => $project_title,
								'{{description}}' => $description,
								'{{bids}}' => '0',
								'{{category}}' => $category,
								'{{minimum_bid}}' => $ilance->currency->format($start_price, $currencyid),
								'{{p_id}}' => $rfpid,
								'{{details}}' => ucfirst($project_details),
								'{{privacy}}' => ucfirst($bid_details),
								'{{closing_date}}' => print_date($end_date, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
								'{{site_name}}' => SITE_NAME,
							));
							$ilance->email->send();
							// email user
							$ilance->email->mail = fetch_user('email', $userid);
							$ilance->email->slng = fetch_user_slng($userid);
							$ilance->email->get('new_product_auction_open_for_bids_saved');
							$ilance->email->set(array (
								'{{username}}' => fetch_user('username', $userid),
								'{{projectname}}' => stripslashes($project_title),
								'{{project_title}}' => $project_title,
								'{{description}}' => $description,
								'{{category}}' => $category,
								'{{p_id}}' => $rfpid,
								'{{closing_date}}' => print_date($end_date, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
								'{{site_name}}' => SITE_NAME,
							));
							$ilance->email->send();
						}
						else
						{
							// email admin
							$ilance->email->mail = SITE_EMAIL;
							$ilance->email->slng = fetch_site_slng();
							$ilance->email->get('product_auction_posted_admin');
							$ilance->email->set(array (
								'{{buyer}}' => fetch_user('username', $userid),
								'{{project_title}}' => $project_title,
								'{{description}}' => $description,
								'{{bids}}' => '0',
								'{{category}}' => $category,
								'{{minimum_bid}}' => $ilance->currency->format($start_price, $currencyid),
								'{{p_id}}' => $rfpid,
								'{{details}}' => ucfirst($project_details),
								'{{privacy}}' => ucfirst($bid_details),
								'{{closing_date}}' => print_date($end_date, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
							));
							$ilance->email->send();
							// email user
							$ilance->email->mail = fetch_user('email', $userid);
							$ilance->email->slng = fetch_user_slng($userid);
							$ilance->email->get('new_product_auction_open_for_bids');
							$ilance->email->set(array (
								'{{username}}' => fetch_user('username', $userid),
								'{{projectname}}' => stripslashes($project_title),
								'{{description}}' => $description,
								'{{category}}' => $category,
								'{{p_id}}' => $rfpid,
								'{{closing_date}}' => print_date($end_date, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
							));
							$ilance->email->send();
						}
					}
					// #### OPEN STATUS (NOT DRAFT) ################
					if ($status == 'open')
					{
						// #### REFERRAL SYSTEM TRACKER ########
						// we'll track that this user has posted a valid auction from the AdminCP.
						$ilance->referral->update_referral_action('postauction', $userid);
						if ($isbulkupload == false)
						{
							// did this buyer actually visit the search pages and profile menus for providers and added them for this project?
							$this->dispatch_invited_members_email('product', $rfpid, $userid);
							// did this buyer manually enter email addresses to invite users to bid?
							$this->dispatch_external_members_email('product', $rfpid, $userid, $project_title, $bid_details, $end_date, $invitelist, $invitemessage);
						}
						// rebuild category count
						$ilance->categories->build_category_count($cid, 'add', "insert_product_auction(): adding increment count cid $cid");
						// are we constructing new auction from an API call?
						if ($skipemailprocess == 0)
						{
							$url = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productauction', 0, $rfpid, $project_title, '{_view_auction_here}', 0, '', 0, 0) : '<a href="' . $ilpage['merch'] . '?id=' . $rfpid . '">{_view_auction_here}</a>';
							$ilance->template->fetch('main', 'listing_forward_auction_complete.html');
							$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
							$ilance->template->parse_if_blocks('main');
							$ilance->template->pprint('main', array('url'));
							exit();
						}
					}
					// #### DRAFT MODE #############################
					else if ($status == 'draft')
					{
						if ($isbulkupload == false)
						{
							// handle invitation logic for non moderated draft auctions (this will not send email out yet, only log it)
							$this->dispatch_invited_members_email('product', $rfpid, $userid, $dontsendemail = 1);
							// did this buyer manually enter email addresses to invite users to bid?
							$this->dispatch_external_members_email('product', $rfpid, $userid, $project_title, $bid_details, $end_date, $invitelist, $invitemessage, $dontsendemail = 1);
						}
						$url = '<a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=drafts">{_view_draft_auctions_here}</a>';
						// are we constructing new auction from an API call?
						if ($skipemailprocess == 0)
						{
							// no api being used, proceed to dispatching email
							$ilance->template->fetch('main', 'listing_forward_auction_draft.html');
							$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
							$ilance->template->parse_if_blocks('main');
							$ilance->template->pprint('main', array('url'));
							exit();
						}
					}
				}
			}
			// #### AUCTIONS ARE BEING MODERATED ###################
			else
			{
				// are we constructing new auction from an API call?
				if ($skipemailprocess == 0)
				{
					// auctions require moderation
					$ilance->email->mail = SITE_EMAIL;
					$ilance->email->slng = fetch_site_slng();
					$ilance->email->get('auction_moderation_admin');
					$ilance->email->set(array (
						'{{buyer}}' => fetch_user('username', $userid),
						'{{project_title}}' => stripslashes($project_title),
						'{{description}}' => $description,
						'{{category}}' => $category,
						'{{minimum_bid}}' => $ilance->currency->format($start_price, $currencyid),
						'{{p_id}}' => $rfpid,
						'{{closing_date}}' => print_date($end_date, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
						'{{details}}' => ucfirst($project_details),
						'{{privacy}}' => ucfirst($bid_details),
						'{{moderated_tab_url}}' => HTTPS_SERVER_ADMIN . $ilpage['distribution']
					));
					$ilance->email->send();
				}
				if ($isbulkupload == false)
				{
					// handle invitation logic for moderated auctions
					$this->dispatch_invited_members_email('product', $rfpid, $userid, $dontsendemail = 1);
					// did this buyer manually enter email addresses to invite users to bid?
					$this->dispatch_external_members_email('product', $rfpid, $userid, $project_title, $bid_details, $end_date, $invitelist, $invitemessage, $dontsendemail = 1);
				}
				// are we constructing new auction from an API call?
				if ($skipemailprocess == 0)
				{
					// no api being used, proceed to dispatching email
					$url = '<a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=rfp-pending"><strong>{_pending_auctions_menu}</strong></a>';
					$ilance->template->fetch('main', 'listing_forward_auction_moderation.html');
					$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
					$ilance->template->parse_if_blocks('main');
					$ilance->template->pprint('main', array('url'));
					exit();
				}
			}
		}
	}
    
	/**
	* Function to send invite email after admin validate service auction
	*
	* @param       array       invited users array
	* @param       string      category type (service or product)
	* @param       integer     listing id
	* @param       integer     user id
	* @param       boolean     don't send email? (default false)
	*
	* @return      nothing
	*/
	function dispatch_invited_members_email_afteradminvalidate($invitedusers = array (), $cattype = 'service', $rfpid = 0, $userid = 0, $dontsendemail = 0)
	{
		global $ilance, $ilconfig, $phrase, $ilpage;
		// check if we have any invited users to dispatch email to
		$sql3 = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "project_invitations
			WHERE project_id = '" . intval($rfpid) . "'
				AND buyer_user_id > 0
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql3) > 0)
		{
			while ($res3 = $ilance->db->fetch_array($sql3, DB_ASSOC))
			{
				// are we constructing new auction from an API call?
				if ($dontsendemail == 0)
				{
					$comment = '{_no_message_was_provided}';
					if (!empty($res3['invite_message']))
					{
						$comment = strip_tags($res3['invite_message']);
					}
					if ($cattype == 'service')
					{
						// service auction
						$email = ($res3['seller_user_id'] > 0) ? fetch_user('email', $res3['seller_user_id']) : $res3['email'];
						$invitee = fetch_user('username', $res3['seller_user_id']);
					}
					else
					{
						// product auction
						$email = fetch_user('email', $res3['buyer_user_id']);
						$invitee = fetch_user('username', $res3['buyer_user_id']);
					}
					// todo: detect if seo is enabled
					$url = HTTP_SERVER . $ilpage['rfp'] . '?rid=' . $_SESSION['ilancedata']['user']['ridcode'] . '&id=' . intval($rfpid) . '&invited=1&e=' . $email;
					$firstname = fetch_user('first_name', $userid);
					$lastname = fetch_user('last_name', $userid);
					$username = fetch_user('username', $userid);
					$ridcode = fetch_user('rid', $userid);
					$invitee = $res3["name"];
					$slng = fetch_user_slng(intval($userid));
					$ilance->email->mail = $email;
					$ilance->email->slng = $slng;
					$ilance->email->get('invited_to_place_bid');
					$url = HTTP_SERVER . $ilpage['rfp'] . '?rid=' . $ridcode . '&id=' . intval($rfpid) . '&invited=1&e=' . $email;
					$ilance->email->set(array (
						'{{invitee}}' => $invitee,
						'{{firstname}}' => $firstname,
						'{{lastname}}' => $lastname,
						'{{username}}' => $username,
						'{{projectname}}' => stripslashes(fetch_auction('project_title', $rfpid)),
						'{{bidprivacy}}' => ucfirst(fetch_auction('bid_details', $rfpid)),
						'{{bidends}}' => print_date(fetch_auction('date_end', $rfpid), $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
						'{{message}}' => $comment,
						'{{url}}' => $url,
						'{{ridcode}}' => $ridcode,
					));
					$ilance->email->send();
				}
			}
		}
	}
    
	/**
	* Function to handle invitation logic for moderated and non-moderated auctions.  This function will
	* only send any "members" that were invited to this auction when the buyer was using the search provider
	* menus and/or inviting a user directly from their profile menu.  The logic will detect if any invite
	* logic has been stored in a session and if so, dispatch any invitation email to the end users.
	*
	* @param       array       invited users array
	* @param       string      category type (service/product)
	* @param       integer     project id
	* @param       integer     user id
	* @param       bool        do not send email? (default false)
	*
	* @return      nothing
	*/
	function dispatch_invited_members_email($invitedusers = array (), $cattype = 'service', $rfpid = 0, $userid = 0, $dontsendemail = 0)
	{
		global $ilance, $ilconfig, $phrase, $ilpage;
		if (!empty($_SESSION['ilancedata']['tmp']['invitations']) AND is_serialized($_SESSION['ilancedata']['tmp']['invitations']))
		{
			$invited = unserialize($_SESSION['ilancedata']['tmp']['invitations']);
			$count = count($invited);
			if ($count > 0)
			{
				for ($i = 0; $i < $count; $i++)
				{
					$this->insert_auction_invitation($userid, $invited[$i], $rfpid, $dontsendemail, $cattype);
				}
			}
			// remove temp invitation session data so we don't attach same users to new auctions created after this one
			$_SESSION['ilancedata']['tmp']['invitations'] = '';
			unset($_SESSION['ilancedata']['tmp']['invitations']);
		}
		else
		{
			// invite list is empty (we must be opening a previously saved draft)
			// check if we have any invited users to dispatch email to
			$sql3 = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "project_invitations
				WHERE project_id = '" . intval($rfpid) . "'
					AND buyer_user_id > 0
					AND seller_user_id > 0
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql3) > 0)
			{
				while ($res3 = $ilance->db->fetch_array($sql3, DB_ASSOC))
				{
					// are we constructing new auction from an API call?
					if ($dontsendemail == 0)
					{
						$comment = '{_no_message_was_provided}';
						if (!empty($res3['invite_message']))
						{
							$comment = strip_tags($res3['invite_message']);
						}
						if ($cattype == 'service')
						{
							// service auction
							$email = fetch_user('email', $res3['seller_user_id']);
							$invitee = fetch_user('username', $res3['seller_user_id']);
						}
						else
						{
							// product auction
							$email = fetch_user('email', $res3['buyer_user_id']);
							$invitee = fetch_user('username', $res3['buyer_user_id']);
						}
						// todo: detect if seo is enabled
						$url = HTTP_SERVER . $ilpage['rfp'] . '?rid=' . $_SESSION['ilancedata']['user']['ridcode'] . '&id=' . intval($rfpid) . '&invited=1&e=' . $email;
						$ilance->email->mail = $email;
						$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
						$ilance->email->get('invited_to_place_bid');
						$ilance->email->set(array (
							'{{invitee}}' => $invitee,
							'{{firstname}}' => ucfirst($_SESSION['ilancedata']['user']['firstname']),
							'{{lastname}}' => ucfirst($_SESSION['ilancedata']['user']['lastname']),
							'{{username}}' => $_SESSION['ilancedata']['user']['username'],
							'{{projectname}}' => stripslashes(fetch_auction('project_title', $rfpid)),
							'{{bidprivacy}}' => ucfirst(fetch_auction('bid_details', $rfpid)),
							'{{bidends}}' => print_date(fetch_auction('date_end', $rfpid), $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
							'{{message}}' => $comment,
							'{{url}}' => $url,
							'{{ridcode}}' => $_SESSION['ilancedata']['user']['ridcode'],
						));
						$ilance->email->send();
					}
				}
			}
		}
	}
    
	/**
	* Function to handle invitation logic for moderated and non-moderated auctions for externally invited users.
	* This function will also be called when a user is setting a draft auction to open and any invted users will
	* then get email invitation notice.
	*
	* @param       string       category type (service/product)
	* @param       integer      project id
	* @param       integer      user id
	* @param       string       project title
	* @param       string       bid details
	* @param       string       end date
	* @param       array        invitation list
	* @param       string       invitation message
	* @param       bool         do not send email? (default false)
	*
	* @return      nothing
	*/
	function dispatch_external_members_email($cattype = 'service', $rfpid = 0, $userid = 0, $project_title = '', $bid_details = '', $end_date = '', $invitelist = array (), $invitemessage = '', $skipemailprocess = 0, $mode = 'insert')
	{
		global $ilance, $ilpage, $ilconfig, $phrase;
		// send out invitations based on any email address and first names supplied on the auction posting interface
		if (isset($invitelist) AND !empty($invitelist) AND is_array($invitelist))
		{
			if ($mode = 'update')
			{
				$ilance->db->query("
					DELETE
					FROM " . DB_PREFIX . "project_invitations
					WHERE project_id = '" . intval($rfpid) . "'
				", 0, null, __FILE__, __LINE__);
			}
			foreach ($invitelist['email'] AS $key => $email)
			{
				$name = $invitelist['name']["$key"];
				if (!empty($email) AND !empty($name))
				{
					if ($email == $_SESSION['ilancedata']['user']['email'] OR is_valid_email($email) == false)
					{
						// don't send if:
						// 1. if user is sending invitation to himself
						// 2. if user email is email being used to send invitation to himself
						// 3. if is_valid_email() determines that email is bad or not formatted properly
					}
					else
					{
						$inv_user_id = (fetch_user('user_id', '', '', $email) > 0) ? fetch_user('user_id', '', '', $email) : '-1';
						// todo: detect is seo is enabled
						$url = ($cattype == 'service') ? HTTP_SERVER . $ilpage['rfp'] . '?rid=' . $_SESSION['ilancedata']['user']['ridcode'] . '&id=' . intval($rfpid) . '&invited=1&e=' . $email : HTTP_SERVER . $ilpage['merch'] . '?rid=' . $_SESSION['ilancedata']['user']['ridcode'] . '&id=' . intval($rfpid) . '&invited=1&e=' . $email;
						$comment = '{_no_message_was_provided}';
						if (isset($invitemessage) AND !empty($invitemessage))
						{
							$comment = strip_tags($invitemessage);
						}
						$sql3 = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "project_invitations
							WHERE email = '" . trim($ilance->db->escape_string($email)) . "'
							    AND project_id = '" . intval($rfpid) . "'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql3) == 0)
						{
							// invited users don't exist for this auction.. invite them
							if ($cattype == 'service')
							{
								$ilance->db->query("
									INSERT INTO " . DB_PREFIX . "project_invitations
									(id, project_id, buyer_user_id, seller_user_id, email, name, invite_message, date_of_invite, bid_placed)
									VALUES(
									NULL,
									'" . intval($rfpid) . "',
									'" . intval($userid) . "',
									'" . intval($inv_user_id) . "',
									'" . $ilance->db->escape_string($email) . "',
									'" . $ilance->db->escape_string($name) . "',
									'" . $ilance->db->escape_string($comment) . "',
									'" . DATETIME24H . "',
									'no')
								", 0, null, __FILE__, __LINE__);
							}
							else if ($cattype == 'product')
							{
								$ilance->db->query("
									INSERT INTO " . DB_PREFIX . "project_invitations
									(id, project_id, buyer_user_id, seller_user_id, email, name, invite_message, date_of_invite, bid_placed)
									VALUES(
									NULL,
									'" . intval($rfpid) . "',
									'" . intval($inv_user_id) . "',
									'" . intval($userid) . "',
									'" . $ilance->db->escape_string($email) . "',
									'" . $ilance->db->escape_string($name) . "',
									'" . $ilance->db->escape_string($comment) . "',
									'" . DATETIME24H . "',
									'no')
								", 0, null, __FILE__, __LINE__);
							}
							// are we constructing new auction from an API call?
							if ($skipemailprocess == 0)
							{
								// no api being used, proceed to dispatching email
								$ilance->email->mail = $email;
								$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
								$ilance->email->get('invited_to_place_bid');
								$ilance->email->set(array (
									'{{invitee}}' => ucfirst($name),
									'{{firstname}}' => ucfirst($_SESSION['ilancedata']['user']['firstname']),
									'{{lastname}}' => ucfirst($_SESSION['ilancedata']['user']['lastname']),
									'{{username}}' => $_SESSION['ilancedata']['user']['username'],
									'{{projectname}}' => $project_title,
									'{{bidprivacy}}' => ucfirst($bid_details),
									'{{bidends}}' => print_date($end_date, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
									'{{message}}' => $comment,
									'{{url}}' => $url,
									'{{ridcode}}' => $_SESSION['ilancedata']['user']['ridcode'],
								));
								$ilance->email->send();
							}
						}
						else
						{
							// this invited user was already invited.. send email..
							if ($skipemailprocess == 0)
							{
								// no api being used, proceed to dispatching email
								$ilance->email->mail = $email;
								$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
								$ilance->email->get('invited_to_place_bid');
								$ilance->email->set(array (
									'{{invitee}}' => ucfirst($name),
									'{{firstname}}' => ucfirst($_SESSION['ilancedata']['user']['firstname']),
									'{{lastname}}' => ucfirst($_SESSION['ilancedata']['user']['lastname']),
									'{{username}}' => $_SESSION['ilancedata']['user']['username'],
									'{{projectname}}' => $project_title,
									'{{bidprivacy}}' => ucfirst($bid_details),
									'{{bidends}}' => print_date($end_date, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
									'{{message}}' => $comment,
									'{{url}}' => $url,
									'{{ridcode}}' => $_SESSION['ilancedata']['user']['ridcode'],
								));
								$ilance->email->send();
							}
						}
					}
				}
			}
		}
		else
		{
			// invite list is empty (we must be opening a previously saved draft)
			// check if we have any externally invited users to dispatch email to
			$sql3 = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "project_invitations
				WHERE project_id = '" . intval($rfpid) . "'
					AND email != ''
					AND name != ''
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql3) > 0)
			{
				while ($res3 = $ilance->db->fetch_array($sql3))
				{
					// are we constructing new auction from an API call?
					if ($skipemailprocess == 0)
					{
						$comment = '{_no_message_was_provided}';
						if (!empty($res3['invite_message']))
						{
							$comment = strip_tags($res3['invite_message']);
						}
						// todo: detect is seo is enabled
						$url = HTTP_SERVER . $ilpage['rfp'] . '?rid=' . $_SESSION['ilancedata']['user']['ridcode'] . '&amp;id=' . intval($rfpid) . '&amp;invited=1&amp;e=' . $res3['email'];
						$ilance->email->mail = $res3['email'];
						$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
						$ilance->email->get('invited_to_place_bid');
						$ilance->email->set(array (
							'{{invitee}}' => ucfirst($res3['name']),
							'{{firstname}}' => ucfirst($_SESSION['ilancedata']['user']['firstname']),
							'{{lastname}}' => ucfirst($_SESSION['ilancedata']['user']['lastname']),
							'{{username}}' => $_SESSION['ilancedata']['user']['username'],
							'{{projectname}}' => $project_title,
							'{{bidprivacy}}' => ucfirst($bid_details),
							'{{bidends}}' => print_date($end_date, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
							'{{message}}' => $comment,
							'{{url}}' => $url,
							'{{ridcode}}' => $_SESSION['ilancedata']['user']['ridcode'],
						));
						$ilance->email->send();
					}
				}
			}
		}
	}
    
	/**
	* Function to insert a new user into the auction invitation table.
	*
	* @param       integer      owner id
	* @param       integer      user id
	* @param       integer      project id
	* @param       bool         no email flag (true or false)
	* @param       string       invitation type (service or product)
	*
	* @return      nothing
	*/
	function insert_auction_invitation($ownerid = 0, $userid = 0, $projectid = 0, $noemail = 0, $invitetype = 'service')
	{
		global $ilance, $phrase, $page_title, $area_title, $ilpage, $ilconfig, $tstart, $finaltime;
		if ($invitetype == 'service')
		{
			$field1 = 'buyer_user_id';
			$field2 = 'seller_user_id';
		}
		else
		{
			$field1 = 'seller_user_id';
			$field2 = 'buyer_user_id';
		}
		if ($ownerid > 0 AND $userid > 0 AND $projectid > 0)
		{
			$presql = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "project_invitations
				WHERE project_id = '" . intval($projectid) . "'
					AND $field1 = '" . intval($ownerid) . "'
					AND $field2 = '" . intval($userid) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($presql) == 0)
			{
				$sqlauction = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . intval($projectid) . "'
						AND (status = 'draft' OR status = 'open')
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sqlauction) > 0)
				{
					// invite member
					$ilance->db->query("
						INSERT INTO " . DB_PREFIX . "project_invitations
						(id, project_id, $field1, $field2, date_of_invite, bid_placed)
						VALUES(
						NULL,
						'" . intval($projectid) . "',
						'" . intval($ownerid) . "',
						'" . intval($userid) . "',
						'" . DATETIME24H . "',
						'no')
					", 0, null, __FILE__, __LINE__);
					if ($noemail == 0)
					{
						$sql_project = $ilance->db->query("
							SELECT cid, filtered_budgetid, project_title, bids, description, project_id
							FROM " . DB_PREFIX . "projects
							WHERE project_id = '" . intval($projectid) . "'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql_project) > 0)
						{
							$project = $ilance->db->fetch_array($sql_project, DB_ASSOC);
							$budget = $this->construct_budget_overview($project['cid'], $project['filtered_budgetid']);
							// email potential service provider
							$ilance->email->mail = fetch_user('email', $userid);
							$ilance->email->slng = fetch_user_slng($userid);
							$ilance->email->get('invite_message_from_buyer');
							$ilance->email->set(array (
								'{{provider}}' => fetch_user('username', $userid),
								'{{buyer}}' => fetch_user('username', $ownerid),
								'{{project_title}}' => strip_vulgar_words(stripslashes($project['project_title'])),
								'{{bids}}' => $project['bids'],
								'{{project_budget}}' => $budget,
								'{{project_description}}' => strip_vulgar_words(stripslashes($project['description'])),
								'{{p_id}}' => $project['project_id'],
							));
							$ilance->email->send();
						}
					}
				}
			}
		}
	}
    
	/**
	* Function to fetch total number of invited users to a particular auction
	*
	* @param       integer       category id
	*
	* @return      integer       Returns the number of invitees
	*/
	function fetch_invited_users_count($projectid = 0)
	{
		global $ilance;
		$count = 0;
		$sql = $ilance->db->query("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "project_invitations
			WHERE project_id = '" . intval($projectid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql);
			$count = $res['count'];
		}
		return $count;
	}
    
	/**
	* Function to fetch the the budgetgroup name for a particular category
	*
	* @param       integer       category id
	*
	* @return      string        Budget group name
	*/
	function fetch_category_budgetgroup($cid = 0)
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT budgetgroup
			FROM " . DB_PREFIX . "categories
			WHERE cid = '" . intval($cid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			return $res['budgetgroup'];
		}
		return 'default';
	}
    
	/**
	* Function to print inline all invited users for a particular service auction
	*
	* @param       integer       project id
	* @param       integer       owner id
	* @param       string        bid privacy details (open, sealed, blind, full)
	*
	* @return      string        Returns HTML formatted invited users list
	*/
	function print_invited_users($projectid = 0, $ownerid = 0, $bid_details)
	{
		global $ilance, $ilconfig, $phrase;
		$invite_list = '';
		$externalbidders = $registeredbidders = 0;
		$sql = $ilance->db->query("
			SELECT seller_user_id, bid_placed, date_of_bid 
			FROM " . DB_PREFIX . "project_invitations
			WHERE project_id = '" . intval($projectid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				if ($res['seller_user_id'] != '-1')
				{
					// inviting registered members only
					$sqlvendor = $ilance->db->query("
						SELECT user_id
						FROM " . DB_PREFIX . "users
						WHERE user_id = '" . $res['seller_user_id'] . "'
							AND status = 'active'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sqlvendor) > 0)
					{
						$resvendor = $ilance->db->fetch_array($sqlvendor, DB_ASSOC);
						$invite_list .= ($res['date_of_bid'] == '0000-00-00 00:00:00') ? '<span class="blue">' . fetch_user('username', $resvendor['user_id']) . '</span> <span class="smaller gray">[ <em>{_not_placed}</em> ]</span>, ' : '<span class="blue">' . fetch_user('username', $resvendor['user_id']) . '</span> <span class="smaller gray">[ <strong>{_placed}</strong> ]</span>, ';
						$registeredbidders++;
					}
				}
				else
				{
					// this bidder appears to be an external bidder
					// so we only have their email address to work with...
					$externalbidders++;
				}
			}
		}
		if (!empty($invite_list))
		{
			$invite_list = mb_substr($invite_list, 0, -2);
		}
		if ($externalbidders > 0 OR $registeredbidders > 0)
		{
			// viewing as admin
			if (isset($_SESSION['ilancedata']['user']['isadmin']) AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
			{
				// formatted invited users list display
				$invite_list = $invite_list . '<ul style="margin:18px; padding:0px;"><li>' . $externalbidders . ' {_bidders_invited_via_email}</li><li>' . $registeredbidders . ' {_registered_members_invited}</li></ul>';
			}
			// viewing as owner
			else if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $ownerid)
			{
				// formatted invited users list display
				$invite_list = $invite_list . '<ul style="margin:18px; padding:0px;"><li>' . $externalbidders . ' {_bidders_invited_via_email}</li><li>' . $registeredbidders . ' {_registered_members_invited}</li></ul>';
			}
			// viewing as guest
			else if (empty($_SESSION['ilancedata']['user']['userid']))
			{
				$invite_list = '= {_sealed} =';
			}
			else if (!empty($_SESSION['ilancedata']['user']['userid']))
			{
				// viewing as member
				$invite_list = '= {_sealed} =';
			}
		}
		else
		{
			$invite_list = '{_none}';
		}
		return $invite_list;
	}
    
	function convert_bulk_attributes_into_custom($attributes = '')
	{
		global $ilance, $ilconfig, $show;
		$return = array ();
		// create item's attributes - format: question=answer or question=answer, question2=answer2
		//"86=Carl S. Warren, James M. Reeve, ...|34=New|88=0324662963|87=9780324662962|39=Hardcover, 2008|89=23rd"
		if (!empty($attributes))
		{
			if (strchr($attributes, "|"))
			{
				// contains more than 1 attribute: q1=a|q2=a|q3=a
				$split = explode("|", $attributes);
				foreach ($split AS $attribute)
				{
					if (strchr($attribute, "="))
					{
						$split2 = explode("=", $attribute);
						$qid = $split2[0];
						$answer = $split2[1];
						if ($qid > 0)
						{
							$sql = $ilance->db->query("
								SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "inputtype, formname
								FROM " . DB_PREFIX . "product_questions
								WHERE questionid = '" . intval($qid) . "'
							", 0, null, __FILE__, __LINE__);
							if ($ilance->db->num_rows($sql) > 0)
							{
								$res = $ilance->db->fetch_array($sql, DB_ASSOC);
								if ($res['inputtype'] == 'pulldown' OR $res['inputtype'] == 'multiplechoice')
								{
									$ilance->GPC['custom'][$qid]["$res[formname]"][] = handle_input_keywords($answer);
								}
								else
								{
									$ilance->GPC['custom'][$qid]["$res[formname]"] = handle_input_keywords($answer);
								}
							}
						}
					}
				}
			}
			else
			{
				if (strchr($attributes, "="))
				{
					$split = explode("=", $attributes);
					$qid = $split[0];
					$answer = $split[1];
					if ($qid > 0)
					{
						$sql = $ilance->db->query("
							SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "inputtype, formname
							FROM " . DB_PREFIX . "product_questions
							WHERE questionid = '" . intval($qid) . "'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							$res = $ilance->db->fetch_array($sql, DB_ASSOC);
							if ($res['inputtype'] == 'pulldown' OR $res['inputtype'] == 'multiplechoice')
							{
								$ilance->GPC['custom'][$qid]["$res[formname]"][] = handle_input_keywords($answer);
							}
							else
							{
								$ilance->GPC['custom'][$qid]["$res[formname]"] = handle_input_keywords($answer);
							}
						}
					}
				}
			}
		}
		return $ilance->GPC['custom'];
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>