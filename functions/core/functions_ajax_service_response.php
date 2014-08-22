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
* Global AJAX service response functions for iLance
*
* @package      iLance\Global\AJAX\Service
* @version      4.0.0.8059
* @author       ILance
*/

/*
* Function to fetch a javascript response for AJAX output
*
* @param        integer        	listing id
*
* @return       string		Returns HTML formatted output for AJAX response
*/
function fetch_service_response($id = 0)
{
	global $ilance, $ilpage, $ilconfig, $phrase, $show;
	$ilance->auction_expiry->listings();
	$id = intval($id);
	$sql = $ilance->db->query("
		SELECT date_starts, bid_details, winner_user_id, cid, bids, user_id, status, date_end, close_date, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
		FROM " . DB_PREFIX . "projects
		WHERE project_id = '" . intval($id) . "'
	");
	$res = $ilance->db->fetch_array($sql, DB_ASSOC);
	$timeleft = $ilance->auction->auction_timeleft(true, $res['date_starts'], $res['mytime'], $res['starttime']);
	$bid_details = $res['bid_details'];
	$winner_user_id = $res['winner_user_id'];
	$table = ($ilance->categories->bidgrouping($res['cid'])) ? 'project_bids' : 'project_realtimebids';
	$sql = $ilance->db->query("SELECT bid_id FROM " . DB_PREFIX . $table . " WHERE project_id = '" . $id . "'");
	$res['bids'] = $ilance->db->num_rows($sql);
	$declinedbids = $ilance->bid->fetch_declined_bids($id);
	$retractedbids = $ilance->bid->fetch_retracted_bids($id);
	$fetchbidstuff = $ilance->bid->fetch_average_lowest_highest_bid_amounts($bid_details, $id, $res['user_id']);
	$bidprivacy = $fetchbidstuff['bidprivacy'];
	$average = $ilance->bid->fetch_average_bid($id, false, $bid_details, false);
	$lowest = $fetchbidstuff['lowest'];
	$highest = $fetchbidstuff['highest'];
	unset($fetchbidstuff);
	$showplacebidrow = 1;
	$showawardedbidderrow = $showendedrow = $showlowestactivebidder = 0;
	$lowestbiddertext = $highestbiddertext = $awardedbiddertext = '';
	if ($res['bids'] > 0)
	{
		// fetch lowest bidder details (will populate $show['lowbidder_active'] also)..
		$lowbidtemp = $ilance->bid->fetch_lowest_bidder_info($id, $res['user_id'], $bid_details);
		$lowestbiddertext = $lowbidtemp['lowbidder'];
		if ($show['lowbidder_active'])
		{
			$showlowestactivebidder = 1;
		}

		// awarded bidder username
		$awardedbiddertext = '';
		if ($winner_user_id > 0)
		{
			$showawardedbidderrow = 1;
			$awardedbiddertext = fetch_user('username', $winner_user_id);
			if ($bid_details == 'blind' OR $bid_details == 'full')
			{
				$awardedbiddertext = '= {_blind_bidder} =';
			}
		}
		// highest bidder username
		$highestbiddertext = '';
		$highbidderuserid = $ilance->bid->fetch_highest_bidder($id);
		if ($highbidderuserid > 0)
		{
			$highestbiddertext = fetch_user('username', $highbidderuserid);
			if ($bid_details == 'blind' OR $bid_details == 'full')
			{
				$highestbiddertext = '= {_blind_bidder} =';
			}
		}
		if ($bid_details == 'sealed' OR $bid_details == 'full')
		{
			$lowest = '= {_sealed} =';
			$highest = '= {_sealed} =';
			$lowestbiddertext = '= {_sealed} =';
		}
		unset($highbidderuserid, $lowbidtemp);
	}
	if ($res['status'] != 'open')
	{
		$showplacebidrow = 0;
		$showendedrow = 1;
		$timeleft = '{_ended}';
		$date_end = $res['date_end'];
		switch ($res['status'])
		{
			case 'closed':
			{
				$projectstatus = '{_closed_since} ' . print_date($date_end, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				break;
			}
			case 'expired':
			{
				$projectstatus = '{_expired_since} ' . print_date($date_end, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				break;
			}
			case 'delisted':
			{
				$projectstatus = '{_delisted_since} ' . print_date($date_end, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				break;
			}
			case 'approval_accepted':
			{
				$projectstatus = '{_vendor_awarded_bidding_for_event_closed}';
				break;
			}
			case 'wait_approval':
			{
				// fetch days since the provider has been awarded giving more direction to the viewer
				$close_date = $res['close_date'];
				$date1split = explode(' ', $close_date);
				$date2split = explode('-', $date1split[0]);
				$days = $ilance->datetimes->fetch_days_between($date2split[1], $date2split[2], $date2split[0], date('m'), date('d'), date('Y'));
				if ($days == 0)
				{
					$days = 1;
				}
				$projectstatus = '{_waiting_for_awarded_provider_to_accept_the_project} <span class="gray">({_day} ' . $days . ' {_of} ' . $ilconfig['servicebid_awardwaitperiod'] . ')</span>';
				break;
			}
			case 'frozen':
			{
				$projectstatus = '{_frozen_event_temporarily_closed}';
				break;
			}
			case 'finished':
			{
				$projectstatus = '{_vendor_awarded_event_is_finished}';
				break;
			}
			case 'archived':
			{
				$projectstatus = '{_archived_event}';
				break;
			}
			case 'draft':
			{
				$projectstatus = '{_draft_mode_pending_post_by_owner}';
				break;
			}
		}
	}
	else
	{
		$projectstatus = '{_event_open_for_bids}';
	}
	// myString[0]  = timeleft text
	// myString[1]  = bids text
	// myString[2]  = lowest bidder name
	// myString[3]  = highest bidder name
	// myString[4]  = awarded bidder name
	// myString[5]  = average bid amount
	// myString[6]  = project status text
	// myString[7]  = number of declined bids
	// myString[8]  = number of retracted bids
	// myString[9]  = SHOW place a bid row?
	// myString[10] = SHOW awarded bidder row?
	// myString[11] = SHOW block header as ended listing?
	// myString[12] = lowest bid amount
	// myString[13] = highest bid amount
	// myString[14] = SHOW lowest active bidder row?
	$response = $timeleft . '|' . $res['bids'] . '|' . $lowestbiddertext . '|' . $highestbiddertext . '|' . $awardedbiddertext . '|' . $average . '|' . $projectstatus . '|' . $declinedbids . '|' . $retractedbids . '|' . $showplacebidrow . '|' . $showawardedbidderrow . '|' . $showendedrow . '|' . $lowest . '|' . $highest . '|' . $showlowestactivebidder . '|' . $res['date_starts'] . '|' . $res['date_end'] . '|' . DATETIME24H;
	$ilance->template->templateregistry['response'] = $response;
	$response = $ilance->template->parse_template_phrases('response');
	return $response;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>