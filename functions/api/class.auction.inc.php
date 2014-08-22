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
* Auction class to perform the majority of functions dealing with anything to do with auctions within ILance.
*
* @package      iLance\Auction
* @version      4.0.0.8059
* @author       ILance
*/
class auction
{
	function __construct(){}
	
        /**
        * Function to print auction icons based on the selected filters of the associated auction id.
        *
        * @param       array        auction result set
        *
        * @return      string       HTML representation of icons for the associated listing.
        */
	function auction_icons($res = array())
	{
		global $ilance, $ilconfig, $phrase, $ilpage, $show;
		$html = '';

		($apihook = $ilance->api('auction_icons_start')) ? eval($apihook) : false;

		// nonprofit supported listing
		$html .= ($res['donation'] == '1' AND $res['charityid'] > 0 AND $res['project_state'] == 'product')
			? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/ico_nonprofit.png" border="0" alt="{_nonprofit}" title="{_nonprofit}" id="" />&nbsp;'
			: '';

		// contains video?
		$html .= (!empty($res['description_videourl'])
			? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/ico_video.png" border="0" alt="{_video}" title="{_video}" id="" />&nbsp;'
			: '');

		// has a reserve price?
		$html .= ($res['reserve'])
			? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/ico_reserve.png" alt="{_reserve_price}" title="{_reserve_price}" border="0" id="" />&nbsp;'
			: '';

		// is realtime?
		$html .= ($res['project_details'] == 'realtime')
			? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/ico_clock.png" alt="{_realtime_auction}" title="{_realtime_auction}" border="0" id="" />&nbsp;'
			: '';

		// is by invite only?
		$html .= ($res['project_details'] == 'invite_only')
			? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/invite.gif" alt="{_invite_only}" title="{_invite_only}" border="0" id="" />&nbsp;'
			: '';

		// #### BID PRIVACY FILTERS ############################
		// sealed bidding
		$html .= ($res['bid_details'] == 'sealed')
			? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/sealed.gif" border="0" alt="{_sealed_bidding}" title="{_sealed_bidding}" id="" />&nbsp;'
			: '';

		// blind bidding
		$html .= ($res['bid_details'] == 'blind')
			? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blind.gif" border="0" alt="{_blind_bidding}" title="{_blind_bidding}" id="" />&nbsp;'
			: '';

		// full privacy
		$html .= ($res['bid_details'] == 'full')
			? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/sealed.gif" border="0" alt="{_sealed_bidding}" title="{_sealed_bidding}" id="" />&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blind.gif" border="0" alt="{_blind_bidding}" title="{_blind_bidding}" id="" />&nbsp;'
			: '';

		// offers secure escrow?
		if ($res['filter_escrow'] == '1')
		{
			$html .= '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/ico_escrow.png" alt="{_escrow}" title="{_escrow}" border="0" id="" />&nbsp;';
		}
		if ($res['filter_gateway'] == '1' AND !empty($res['paymethodoptions']) AND is_serialized($res['paymethodoptions']))
		{
			$paymethodoptions = unserialize($res['paymethodoptions']);
			foreach ($paymethodoptions AS $gateway => $value)
			{
				if (!empty($gateway))
				{
					$html .= '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/ico_' . $gateway . '.png" alt="{_pay_me_directly_through} {_' . $gateway .'}" title="{_pay_me_directly_through} {_' . $gateway . '}" border="0" id="" />&nbsp;';
				}
			}
		}
		if ($res['filtered_auctiontype'] == 'classified')
		{
			$html .= '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/ico_phone.png" alt="{_classified_ad}" title="{_classified_ad}" border="0" id="" />&nbsp;';
		}
		if (isset($res['buynow_qty_lot']) AND $res['buynow_qty_lot'] > 0 AND isset($res['items_in_lot']) AND $res['items_in_lot'] > 0)
		{
			$html .= '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/ico_lot.png" alt="{_listed_as_lot}" title="{_listed_as_lot}" border="0" id="" />&nbsp;';
		}
		
		($apihook = $ilance->api('auction_icons_end')) ? eval($apihook) : false;

		return $html;
	}

	/**
        * Function to fetch the type (project state) of an auction.
        *
        * @param       integer      auction id
        *
        * @return      string       auction type (project state)
        */
	function fetch_auction_type($projectid = 0)
	{
		global $ilance;
		$value = '';
		$sql = $ilance->db->query("
                        SELECT project_state
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . intval($projectid) . "'
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$value = $res['project_state'];
		}
		return $value;
	}

	/**
        * Function to calculate the exact time left based on a few date and time parameters.
        *
        * @param       integer       date starts
        * @param       integer       start time
        * @param       integer       time now
        *
        * @return      string        phrased value of the time left (eg: 1h, 3m)
        */
	function calculate_time_left($datestarts, $starttime, $mytime)
	{
		global $ilance, $ilconfig, $phrase;
		if ($datestarts > DATETIME24H)
		{
			$dif = $starttime;
			$ndays = floor($dif / 86400);
			$dif -= $ndays * 86400;
			$nhours = floor($dif / 3600);
			$dif -= $nhours * 3600;
			$nminutes = floor($dif / 60);
			$dif -= $nminutes * 60;
			$nseconds = $dif;
			$sign = '+';
			if ($starttime < 0)
			{
				$starttime = - $starttime;
				$sign = '-';
			}
			if ($sign != '-')
			{
				if ($ndays != '0')
				{
					$tl = $ndays . '{_d_shortform}, ';
					$tl .= $nhours . '{_h_shortform}+';
				}
				else if ($nhours != '0')
				{
					$tl = $nhours . '{_h_shortform}, ';
					$tl .= $nminutes . '{_m_shortform}+';
				}
				else
				{
					$tl = $nminutes . '{_m_shortform}, ';
					$tl .= $nseconds . '{_s_shortform}+';
				}
			}
			$timeleft = '<span class="gray">{_starts}:</span> <span class="black">' . $tl . '</span>';
		}
		else
		{
			$dif = $mytime;
			$ndays = floor($dif / 86400);
			$dif -= $ndays * 86400;
			$nhours = floor($dif / 3600);
			$dif -= $nhours * 3600;
			$nminutes = floor($dif / 60);
			$dif -= $nminutes * 60;
			$nseconds = $dif;
			$sign = '+';
			if ($mytime < 0)
			{
				$mytime = - $mytime;
				$sign = '-';
			}
			if ($sign == '-')
			{
				$tl = '{_ended}';
				$expiredauction = 1;
			}
			else
			{
				$expiredauction = 0;
				if ($ndays != '0')
				{
					$tl = $ndays . '{_d_shortform}, ';
					$tl .= $nhours . '{_h_shortform}+';
				}
				else if ($nhours != '0')
				{
					$tl = $nhours . '{_h_shortform}, ';
					$tl .= $nminutes . '{_m_shortform}+';
				}
				else
				{
					$tl = $nminutes . '{_m_shortform}, ';
					$tl .= $nseconds . '{_s_shortform}+';
				}
			}
			$timeleft = $tl;
		}
		return $timeleft;
	}

	/**
        * Function to calculate the exact time left based on a few date and time parameters and prints text or flash countdown applets.
        * This function is optimized to prevent a new call to the database.
        *
        * @param       boolean       show the full timeleft string? (default false) ie: 2d, 1h, 3m, 5+ vs. 2d, 1h+
        * @param       string        date starts (datetime) format
        * @param       string        timestamp of date end - now
        * @param       string        timestamp of start date - now
        * @param       boolean       hide time left/starts phrase in output (default false)
        *
        * @return      string        HTML representation of the countdown text or countdown flash applet
        */
	function auction_timeleft($showfullformat = false, $date_starts = '', $mytime = '', $starttime = '', $hidephrase = false)
	{
		global $ilance, $ilconfig, $ilconfig, $phrase;
		$html = '';
		if ($date_starts > DATETIME24H)
		{
			$dif = $starttime;
			$ndays = floor($dif / 86400);
			$dif -= $ndays * 86400;
			$nhours = floor($dif / 3600);
			$dif -= $nhours * 3600;
			$nminutes = floor($dif / 60);
			$dif -= $nminutes * 60;
			$nseconds = $dif;
			$sign = '+';
			if ($starttime < 0)
			{
				$starttime = - $starttime;
				$sign = '-';
			}
			if ($sign != '-')
			{
				if ($ndays != '0')
				{
					$timeleft = $ndays . '{_d_shortform}, ' . $nhours . '{_h_shortform}';
				}
				else if ($nhours != '0')
				{
					$timeleft = $nhours . '{_h_shortform}, ' . $nminutes . '{_m_shortform}';
				}
				else if ($nminutes != '0')
				{
					$timeleft = $nminutes . '{_m_shortform}, ' . $nseconds . '{_s_shortform}';
				}
				else
				{
					$timeleft = $nseconds . '{_s_shortform}';
				}
			}
			if ($hidephrase)
			{
				$html = $timeleft;
			}
			else
			{
				$html = '{_starts}: ' . $timeleft;
			}
		}
		else
		{
			$dif = $mytime;
			$ndays = floor($dif / 86400);
			$dif -= $ndays * 86400;
			$nhours = floor($dif / 3600);
			$dif -= $nhours * 3600;
			$nminutes = floor($dif / 60);
			$dif -= $nminutes * 60;
			$nseconds = $dif;
			$sign = '+';
			if ($mytime < 0)
			{
				$mytime = - $mytime;
				$sign = '-';
			}
			if ($sign == '-')
			{
				$timeleft = '{_ended}';
			}
			else
			{
				if ($ndays != '0')
				{
					if ($showfullformat)
					{
						$timeleft  = $ndays    . '{_d_shortform}, ';
						$timeleft .= $nhours   . '{_h_shortform}, ';
						$timeleft .= $nminutes . '{_m_shortform}, ';
						$timeleft .= $nseconds . '{_s_shortform}';
					}
					else
					{
						$timeleft = $ndays . '{_d_shortform}, ' . $nhours . '{_h_shortform}';
					}
				}
				else if ($nhours != '0')
				{
					if ($showfullformat)
					{
						$timeleft  = $nhours   . '{_h_shortform}, ';
						$timeleft .= $nminutes . '{_m_shortform}, ';
						$timeleft .= $nseconds . '{_s_shortform}';
					}
					else
					{
						$timeleft = $nhours . '{_h_shortform}, ' . $nminutes . '{_m_shortform}';
					}
				}
				else
				{
					if ($nminutes != '0')
					{
						$timeleft = '<span class="red">' . $nminutes . '{_m_shortform}, ' . $nseconds . '{_s_shortform}</span>';
					}
					else
					{
						$timeleft = '<span class="red"><strong>' . $nseconds . '{_s_shortform}</strong></span>';
					}
				}
			}
			$html = $timeleft;
		}
		return $html;
	}

	/**
        * Function to create the private message board icons within various sections of the client control panel.
        *
        * @param       integer       from user id
        * @param       integer       to user id
        * @param       integer       project id
        * @param       boolean       force admin mode (true or false)
        * @param       boolean       force inactive icon?
        *
        * @return      string        HTML representation of private message board icon
        */
	function construct_pmb_icon($fromid = 0, $toid = 0, $projectid = 0, $adminmode = 0, $forceinactive = false)
	{
		global $ilance, $ilconfig, $phrase, $ilpage, $ilconfig;
		
		$pmb = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_gray.gif" border="0" alt="" />';
		if ($forceinactive)
		{
			return $pmb;
		}
		$rand = rand(1, 99999);
		if ($ilance->permissions->check_access($fromid, 'pmb') == 'no')
		{
			return '<span title="{_upgrade_or_renew_your_subscription_to_view_or_post_private_messages}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_gray.gif" border="0" alt="" /></span>';
		}
		if ($ilance->permissions->check_access($toid, 'pmb') == 'no')
		{
			return '<span title="{_the_recipient_cannot_view_or_post_private_messages_at_this_time}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_gray.gif" border="0" alt="" /></span>';
		}
		$sql = $ilance->db->query("
                        SELECT user_id, status
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . intval($projectid) . "'
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$sql2 = $ilance->db->query("
                                SELECT id, event_id, project_id, from_id, to_id, isadmin, from_status, to_status, track_status, track_dateread, track_popup
                                FROM " . DB_PREFIX . "pmb_alerts
                                WHERE ((to_id = '" . intval($toid) . "' AND from_id = '" . intval($fromid) . "') OR (to_id = '" . intval($fromid) . "' AND from_id = '" . intval($toid) . "'))
                                    AND project_id = '" . intval($projectid) . "'
                        ", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql2) > 0)
			{
				$res2 = $ilance->db->fetch_array($sql2, DB_ASSOC);
				$crypted = array(
					'project_id' => intval($projectid),
					'event_id' => $res2['event_id'],
					'from_id' => $fromid,
					'to_id' => $toid,
					'isadmin' => $adminmode
				);
				$posts = $ilance->pmb->fetch_pmb_posts(intval($projectid), $res2['event_id']);
				$postphrase = '_post_lower';
				if ($posts == 1)
				{
					$postphrase = '_post_lower';
				}
				else if ($posts <> 1)
				{
					$postphrase = '_posts_lower';
				}
				$unread = $ilance->pmb->fetch_unread_pmb_posts(intval($projectid), $res2['event_id'], $fromid);
				if ($unread > 0)
				{
					$pmb = '<span title="' . $posts . ' {' . $postphrase . '}, ' . $unread . ' {_unread}"><a href="javascript:void()" onclick="update_pmb_crypted(\'' . encrypt_url($crypted) . '\')" onmouseover="rollovericon(\'' . md5($toid . ':' . $fromid . ':' . $projectid . ':pmb:' . $rand) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_new_open.gif\')" onmouseout="rollovericon(\'' . md5($toid . ':' . $fromid . ':' . $projectid . ':pmb:' . $rand) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_new.gif\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_new.gif" border="0" alt="' . $posts . ' {' . $postphrase . '}, ' . $unread . ' {_unread}" name="' . md5($toid . ':' . $fromid . ':' . $projectid . ':pmb:' . $rand) . '" /></a></span>';
				}
				else
				{
					$pmb = ($posts > 0)
						? '<span title="' . $posts . ' {' . $postphrase . '}, ' . $unread . ' {_unread}"><a href="javascript:void()" onclick="update_pmb_crypted(\'' . encrypt_url($crypted) . '\')" onmouseover="rollovericon(\'' . md5($fromid . ':' . $toid . ':' . $projectid . ':pmb:' . $rand) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_open.gif\')" onmouseout="rollovericon(\'' . md5($fromid . ':' . $toid . ':' . $projectid . ':pmb:' . $rand) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_active.gif\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_active.gif" border="0" alt="' . $posts . ' {' . $postphrase . '}, ' . $unread . ' {_unread}" name="' . md5($fromid . ':' . $toid . ':' . $projectid . ':pmb:' . $rand) . '" /></a></span>'
						: '<span title="' . $posts . ' {' . $postphrase . '}, ' . $unread . ' {_unread}"><a href="javascript:void()" onclick="update_pmb_crypted(\'' . encrypt_url($crypted) . '\')" onmouseover="rollovericon(\'' . md5($fromid . ':' . $toid . ':' . $projectid . ':pmb:' . $rand) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_open.gif\')" onmouseout="rollovericon(\'' . md5($fromid . ':' . $toid . ':' . $projectid . ':pmb:' . $rand) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb.gif\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb.gif" border="0" alt="' . $posts . ' {' . $postphrase . '}, ' . $unread . ' {_unread}" name="' . md5($fromid . ':' . $toid . ':' . $projectid . ':pmb:' . $rand) . '" /></a></span>';
				}
			}
			else
			{
				switch ($res['status'])
				{
					case 'wait_approval':
					case 'approval_accepted':
					case 'expired':
					case 'closed':
					case 'delisted':
					case 'finished':
					case 'archived':
					case 'open':
					{
						$crypted = array(
							'project_id' => intval($projectid),
							'from_id' => $fromid,
							'to_id' => $toid,
							'isadmin' => $adminmode
						);
						$posts = $unread = 0;
						$postphrase = '_posts_lower';

						if ($unread > 0)
						{
							$pmb = '<span title="' . $posts . ' {' . $postphrase . '}, ' . $unread . ' {_unread}"><a href="javascript:void()" onclick="update_pmb_crypted(\'' . encrypt_url($crypted) . '\')" onmouseover="rollovericon(\'' . md5($toid . ':' . $fromid . ':' . $projectid . ':pmb:' . $rand) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_new_open.gif\')" onmouseout="rollovericon(\'' . md5($toid . ':' . $fromid . ':' . $projectid . ':pmb:' . $rand) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_new.gif\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_new.gif" border="0" alt="' . $posts . ' {' . $postphrase . '}, ' . $unread . ' {_unread}" name="' . md5($toid . ':' . $fromid . ':' . $projectid . ':pmb:' . $rand) . '" /></a></span>';
						}
						else
						{
							$pmb = ($posts > 0)
								? '<span title="' . $posts . ' {' . $postphrase . '}, ' . $unread . ' {_unread}"><a href="javascript:void()" onclick="update_pmb_crypted(\'' . encrypt_url($crypted) . '\')" onmouseover="rollovericon(\'' . md5($fromid . ':' . $toid . ':' . $projectid . ':pmb:' . $rand) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_open.gif\')" onmouseout="rollovericon(\'' . md5($fromid . ':' . $toid . ':' . $projectid . ':pmb:' . $rand) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_active.gif\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_active.gif" border="0" alt="' . $posts . ' {' . $postphrase . '}, ' . $unread . ' {_unread}" name="' . md5($fromid . ':' . $toid . ':' . $projectid . ':pmb:' . $rand) . '" /></a></span>'
								: '<span title="' . $posts . ' {' . $postphrase . '}, ' . $unread . ' {_unread}"><a href="javascript:void()" onclick="update_pmb_crypted(\'' . encrypt_url($crypted) . '\')" onmouseover="rollovericon(\'' . md5($toid . ':' . $fromid . ':' . $projectid . ':pmb:' . $rand) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_open.gif\')" onmouseout="rollovericon(\'' . md5($toid . ':' . $fromid . ':' . $projectid . ':pmb:' . $rand) . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb.gif\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb.gif" border="0" alt="' . $posts . ' {' . $postphrase . '}, ' . $unread . ' {_unread}" name="' . md5($toid . ':' . $fromid . ':' . $projectid . ':pmb:' . $rand) . '" /></a></span>';
						}
						break;
					}
					default:
					{
						$pmb = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_gray.gif" border="0" alt="" />';
						break;
					}
				}
			}
		}
		return $pmb;
	}
	
	/**
        * Function to create the private message board icons within various sections of the client control panel.
        *
        * @param       integer       from user id
        * @param       integer       to user id
        * @param       integer       project id
        * @param       boolean       force admin mode (true or false)
        * @param       boolean       force inactive icon?
        *
        * @return      string        HTML representation of private message board icon
        */
	function construct_pmb_link($fromid = 0, $toid = 0, $projectid = 0, $adminmode = 0, $forceinactive = false)
	{
		global $ilance, $ilconfig, $phrase, $ilpage, $ilconfig;
		$pmb = '';
		if ($forceinactive)
		{
			return $pmb;
		}
		$rand = rand(1, 99999);
		$touser = fetch_user('username', $toid);
		if ($ilance->permissions->check_access($fromid, 'pmb') == 'no')
		{
			return '<input type="button" class="button-link button" value="{_contact} ' . $touser . '" disabled="disabled" title="{_upgrade_or_renew_your_subscription_to_view_or_post_private_messages}" />';
		}
		if ($ilance->permissions->check_access($toid, 'pmb') == 'no')
		{
			return '<input type="button" class="button-link button" value="{_contact} ' . $touser . '" disabled="disabled" title="{_the_recipient_cannot_view_or_post_private_messages_at_this_time}" />';
		}
		$sql = $ilance->db->query("
                        SELECT user_id, status
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . intval($projectid) . "'
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$sql2 = $ilance->db->query("
                                SELECT id, event_id, project_id, from_id, to_id, isadmin, from_status, to_status, track_status, track_dateread, track_popup
                                FROM " . DB_PREFIX . "pmb_alerts
                                WHERE ((to_id = '" . intval($toid) . "' AND from_id = '" . intval($fromid) . "') OR (to_id = '" . intval($fromid) . "' AND from_id = '" . intval($toid) . "'))
					AND project_id = '" . intval($projectid) . "'
                        ", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql2) > 0)
			{
				$res2 = $ilance->db->fetch_array($sql2, DB_ASSOC);
				$crypted = array(
					'project_id' => intval($projectid),
					'event_id' => $res2['event_id'],
					'from_id' => $fromid,
					'to_id' => $toid,
					'isadmin' => $adminmode
				);
				$posts = $ilance->pmb->fetch_pmb_posts(intval($projectid), $res2['event_id']);
				$postphrase = '_post_lower';
				if ($posts == 1)
				{
					$postphrase = '_post_lower';
				}
				else if ($posts <> 1)
				{
					$postphrase = '_posts_lower';
				}
				$unread = $ilance->pmb->fetch_unread_pmb_posts(intval($projectid), $res2['event_id'], $fromid);
				$pmb = '<input type="button" class="button-link button" value="{_contact} ' . $touser . '" title="' . $posts . ' {' . $postphrase . '}, ' . $unread . ' {_unread}" onclick="update_pmb_crypted(\'' . encrypt_url($crypted) . '\')" />';
			}
			else
			{
				switch ($res['status'])
				{
					case 'wait_approval':
					case 'approval_accepted':
					case 'expired':
					case 'closed':
					case 'delisted':
					case 'finished':
					case 'archived':
					case 'open':
					{
						$crypted = array(
							'project_id' => intval($projectid),
							'from_id' => $fromid,
							'to_id' => $toid,
							'isadmin' => $adminmode
						);
						$posts = $unread = 0;
						$postphrase = '_posts_lower';
						$pmb = '<input type="button" class="button-link button" value="{_contact} ' . $touser . '" title="' . $posts . ' {' . $postphrase . '}, ' . $unread . ' {_unread}" onclick="update_pmb_crypted(\'' . encrypt_url($crypted) . '\')" />';
						break;
					}
					default:
					{
						$pmb = '<input type="button" class="button-link button" value="{_contact} ' . $touser . '" title="" disabled="disabled" />';
						break;
					}
				}
			}
		}
		return $pmb;
	}

	/**
        * Function to create the invoice icon to be clicked so providers can generate new transaction to their buyers.
        *
        * @param       integer       seller id
        * @param       integer       buyer id
        * @param       integer       project id
        *
        * @return      string        HTML representation of the clickable invoice icon
        */
	function construct_invoice_icon($sellerid = 0, $buyerid = 0, $projectid = 0)
	{
		global $ilance, $ilconfig, $ilpage, $iltemplate, $ilconfig, $phrase;
		$html = '-';
		$sql = $ilance->db->query("
                        SELECT status
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . intval($projectid) . "'
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			if (($res['status'] == 'open' OR $res['status'] == 'expired' OR $res['status'] == 'wait_approval' OR $res['status'] == 'approval_accepted'))
			{
				$any_invoice_sent = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "invoices
                                        WHERE user_id = '" . intval($buyerid) . "'
                                            AND p2b_user_id = '" . intval($sellerid) . "'
                                            AND projectid = '" . intval($projectid) . "'
                                            AND invoicetype = 'p2b'
                                ", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($any_invoice_sent) == 0)
				{
					$crypted = array(
						'cmd' => '_generate-invoice',
						'buyer_id' => intval($buyerid),
						'seller_id' => intval($sellerid),
						'project_id' => intval($projectid)
					);
					$html = '<a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?crypted=' . encrypt_url($crypted) . '" title="{_generate_new_invoice_to} ' . fetch_user('username', intval($buyerid)) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/invoice.gif" border="0" alt="{_generate_new_invoice_to} ' . fetch_user('username', intval($buyerid)) . '" /></a>';
				}
				else if ($ilance->db->num_rows($any_invoice_sent) > 0)
				{
					$invpaid = $ilance->db->fetch_array($any_invoice_sent, DB_ASSOC);
					if ($invpaid['status'] == 'paid')
					{
						$crypted = array(
							'cmd' => '_generate-invoice',
							'buyer_id' => intval($buyerid),
							'seller_id' => intval($sellerid),
							'project_id' => intval($projectid)
						);
						$html = '<a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?crypted=' . encrypt_url($crypted) . '" title="{_invoice} #' . $invpaid['invoiceid'] . ' {_paid_by} ' . fetch_user('username', $buyerid) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/invoice_checkmark.gif" border="0" alt="{_invoice} #' . $invpaid['invoiceid'] . ' {_paid_by} ' . fetch_user('username', $buyerid) . '" /></a>';
					}
					else
					{
						$crypted = array(
							'cmd' => '_generate-invoice',
							'buyer_id' => intval($buyerid),
							'seller_id' => intval($sellerid),
							'project_id' => intval($projectid)
						);
						$html = '<a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?crypted=' . encrypt_url($crypted) . '" title="{_waiting_on_payment_for_invoice} #' . $invpaid['invoiceid'] . ' {_from} ' . fetch_user('username', $buyerid) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/invoice_gray.gif" border="0" alt="{_waiting_on_payment_for_invoice} #' . $invpaid['invoiceid'] . ' {_from} ' . fetch_user('username', $buyerid) . '" /></a>';
					}
				}
			}
			else
			{
				$html = '-';
			}
		}
		return $html;
	}

	/**
        * Function to fetch a clickable link to the auction winner for a particular auction id.
        *
        * @param       integer       project id
        *
        * @return      integer       clickable link with the username
        */
	function fetch_auction_winner($projectid = 0)
	{
		global $ilance, $ilconfig, $ilpage;
		$winner = '-';
		$sql = $ilance->db->query("
                        SELECT user_id
                        FROM " . DB_PREFIX . "project_bids
                        WHERE project_id = '" . intval($projectid) . "'
                            AND bidstatus = 'awarded'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$userid = $res['user_id'];
			$winner = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $userid . '">' . fetch_user('username', intval($userid)) . '</a>';
		}
		return $winner;
	}

	/**
        * Function to fetch the current reserve price of a particular auction id.
        *
        * @param       integer       auction id
        *
        * @return      integer       reserve price amount
        */
	function fetch_reserve_price($auctionid = 0)
	{
		global $ilance;
		$sql = $ilance->db->query("
                        SELECT reserve, reserve_price
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . intval($auctionid) . "'
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			if ($res['reserve'] AND $res['reserve_price'] > 0)
			{
				return $res['reserve_price'];
			}
		}
		return '0';
	}

	/**
        * Function to fetch the current reserve price bit of a particular auction id.
        *
        * @param       integer       project id
        *
        * @return      string        HTML representation of the reserve price details
        */
	function fetch_reserve_price_bit($projectid = 0)
	{
		global $ilance, $ilconfig, $phrase;
		$sql = $ilance->db->query("
                        SELECT reserve, reserve_price
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . intval($projectid) . "'
                            AND reserve > 0
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$html = '<div>{_reserve_price} <input type="text" name="reserve_price" size="10" value="' . $res['reserve_price'] . '"></div>';
		}
		else
		{
			$html = '<div>{_this_auction_is_not_using_the_reserve_price_feature}</div>';
		}
		return $html;
	}

	/**
        * Function to fetch the current transfer of ownership details
        *
        * @param       integer       project id
        *
        * @return      string        HTML representation of the auction transfer of ownership details
        */
	function fetch_transfer_ownership($projectid = 0)
	{
		global $ilance, $ilconfig, $ilpage;
		$html = '';
		$sql = $ilance->db->query("
                        SELECT transfertype, transfer_to_userid, transfer_from_userid, transfer_to_email, transfer_status, transfer_code
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . intval($projectid) . "'
                            AND transfer_to_userid > 0
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$html .= '{_this_auction_has_been_transfered_from_the_original_buyer_to_another_member}</div>';
			$html .= '<div>{_transferred_from} <a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $res['transfer_from_userid'] . '">' . fetch_user('username', $res['transfer_from_userid']) . '</a></div>';
			$html .= '<div>{_transferred_to} <a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $res['transfer_to_userid'] . '">' . fetch_user('username', $res['transfer_to_userid']) . '</a></div>';
			$html .= '<div>{_transfer_status} ' . ucfirst($res['transfer_status']) . '</div>';
		}
		else
		{
			$html = '<div>{_this_auction_has_not_been_transfered_to_any_other_member}</div>';
		}
		return $html;
	}

	/**
        * Function to create the mediashare (workspace) icon.
        *
        * @param       integer       buyer id
        * @param       integer       seller id
        * @param       integer       project id
        * @param       boolean       force if icon is active or disabled (active by default)
        *
        * @return      string        HTML representation of the clickable mediashare icon
        */
	function construct_mediashare_icon($buyerid = 0, $sellerid = 0, $projectid = 0, $active = true)
	{
		global $ilance, $phrase, $ilconfig, $ilpage, $iltemplate, $ilconfig;
		
		$html = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/share_gray.gif" border="0" alt="" id="" />';
		$viewinguserid = $_SESSION['ilancedata']['user']['userid'];
		if ($ilance->permissions->check_access($buyerid, 'workshare') == 'no')
		{
			return '<span title="{_the_recipient_cannot_use_workspace_at_this_time}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/share_gray.gif" border="0" alt="{_the_recipient_cannot_use_workspace_at_this_time}" /></span>';
		}
		if ($ilance->permissions->check_access($sellerid, 'workshare') == 'no')
		{
			return '<span title="{_the_recipient_cannot_use_workspace_at_this_time}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/share_gray.gif" border="0" alt="{_the_recipient_cannot_use_workspace_at_this_time}" /></span>';
		}
		if ($active)
		{
			$crypted = array(
				'project_id' => intval($projectid),
				'buyer_id' => $buyerid,
				'seller_id' => $sellerid,
				'returnurl' => str_replace(substr(SUB_FOLDER_ROOT, 1), '', substr(SCRIPT_URI, 1)),
			);
			$shared = $private = 0;
			$sql = $ilance->db->query("
                                SELECT tblfolder_ref, user_id
                                FROM " . DB_PREFIX . "attachment
                                WHERE attachtype = 'ws'
                                        AND project_id = '" . intval($projectid) . "'
                                        AND visible = '1'
                        ", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					$sql2 = $ilance->db->query("
                                                SELECT folder_type
                                                FROM " . DB_PREFIX . "attachment_folder
                                                WHERE project_id = '" . intval($projectid) . "'
                                                        AND id = '" . $res['tblfolder_ref'] . "'
                                        ", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql2) > 0)
					{
						$res2 = $ilance->db->fetch_array($sql2, DB_ASSOC);
						if ($res2['folder_type'] == '1' AND $res['user_id'] == $viewinguserid)
						{
							$private++;
						}
						else if ($res2['folder_type'] == '2')
						{
							$shared++;
						}
					}
				}
			}
			$html = '<span title="' . $shared . ' {_shared_lower}, ' . $private . ' {_private_lower}"><a href="' . $ilpage['workspace'] . '?crypted=' . encrypt_url($crypted) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/share.gif" border="0" alt="" id="" /></a></span>';
		}
		return $html;
	}

	/**
        * Function to print the budget pulldown details
        *
        * @param       integer       category id
        * @param       integer       selected id (optional)
        * @param       string        field name to use
        * @param       bool          are we enabling javascript (true or false)
        * @param       bool          will we show the "please select" option as well?
        * @param       bool          will we show insertion fees within the pulldown menu that is generated?
        *
        * @return      string        HTML representation of the budget pulldown values
        */
	function construct_budget_pulldown($cid, $selected = '', $fieldname = 'filtered_budgetid', $dojs = 1, $showselect = 0, $showinsertionfees = 0)
	{
		global $ilance, $phrase, $show;
		$html = '';
		if ($dojs)
		{
			$html .= '<select id="' . $fieldname . '_select" name="' . $fieldname . '" onclick="javascript: document.ilform.showbudget.checked=true;">';
		}
		else
		{
			$html .= '<select id="' . $fieldname . '_select" name="' . $fieldname . '">';
		}
		if ($showselect)
		{
			$html .= '<option value="">{_any_budget_range}</option>';
		}
		$query = $ilance->db->query("
                        SELECT budgetgroup
                        FROM " . DB_PREFIX . "categories
                        WHERE cid = '" . intval($cid) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($query) > 0)
		{
			$rquery = $ilance->db->fetch_array($query, DB_ASSOC);
			$budgetgroup = $rquery['budgetgroup'];
		}
		else
		{
			$budgetgroup = 'default';
		}
		$sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "budget
                        WHERE budgetgroup = '" . $ilance->db->escape_string($budgetgroup) . "'
                        ORDER BY budgetfrom ASC
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$show['budgetgroups'] = true;
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				if (isset($selected) AND $selected == $res['budgetid'])
				{
					$show['selectedbudgetlogic'] = $this->calculate_insertion_fee_in_budget_group($res['insertiongroup']);
					if ($res['budgetto'] == '-1')
					{
						$html .= '<option value="' . $res['budgetid'] . '" selected="selected">' . stripslashes($res['title']) . ' (' . $ilance->currency->format($res['budgetfrom']) . ' {_or_more})' . ((!empty($res['insertiongroup']) AND $showinsertionfees) ? ' - *{_insertion_fee}: ' . $ilance->currency->format($this->calculate_insertion_fee_in_budget_group($res['insertiongroup'])) : '') . '</option>';
					}
					else
					{
						$html .= '<option value="' . $res['budgetid'] . '" selected="selected">' . stripslashes($res['title']) . ' (' . $ilance->currency->format($res['budgetfrom']) . ' - ' . $ilance->currency->format($res['budgetto']) . ')'  . ((!empty($res['insertiongroup']) AND $showinsertionfees) ? ' - *{_insertion_fee}: ' . $ilance->currency->format($this->calculate_insertion_fee_in_budget_group($res['insertiongroup'])) : '') . '</option>';
					}
				}
				else
				{
					if ($res['budgetto'] == '-1')
					{
						$html .= '<option value="' . $res['budgetid'] . '">' . stripslashes($res['title']) . ' (' . $ilance->currency->format($res['budgetfrom']) . ' {_or_more})' . ((!empty($res['insertiongroup']) AND $showinsertionfees) ? ' - {_insertion_fee}: *' . $ilance->currency->format($this->calculate_insertion_fee_in_budget_group($res['insertiongroup'])) : '') . '</option>';
					}
					else
					{
						$html .= '<option value="' . $res['budgetid'] . '">' . stripslashes($res['title']) . ' (' . $ilance->currency->format($res['budgetfrom']) . ' - ' . $ilance->currency->format($res['budgetto']) . ')' . ((!empty($res['insertiongroup']) AND $showinsertionfees) ? ' - *{_insertion_fee}: ' . $ilance->currency->format($this->calculate_insertion_fee_in_budget_group($res['insertiongroup'])) : '') . '</option>';
					}
				}
			}
		}
		else
		{
			$show['budgetgroups'] = false;
			$html .= '<option value="0">--</option>';
		}
		$html .= '</select>';
		return $html;
	}

	/**
        * Function to print the budget overview details
        *
        * @param       integer       category id
        * @param       integer       selected id (optional)
        * @param       boolean       don't show range title (default false)
        * @param       boolean       don't show brackets (default false)
        * @param       boolean       force function to use raw budget id vs. the budget in place for the actual category.  This is required for the search system if subcategories are also being called
        *
        * @return      string        HTML representation of the budget values
        */
	function construct_budget_overview($cid = 0, $selected = 0, $notext = false, $nobrackets = false, $forcenocategory = false, $numberstok = true)
	{
		global $ilance, $phrase, $sqlquery;
		$html = '';
		if ($selected == 0 OR $cid == 0)
		{
			$html = '{_non_disclosed}';
			return $html;
		}
		if ($forcenocategory AND $selected > 0)
		{
			$sql = $ilance->db->query("
                                SELECT budgetid, budgetto, budgetfrom, title
                                FROM " . DB_PREFIX . "budget
                                WHERE budgetid = '" . intval($selected) . "'
                                ORDER BY budgetfrom ASC
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					if ($res['budgetto'] == '-1')
					{
						if ($nobrackets AND $notext)
						{
							$html = $ilance->currency->format($res['budgetfrom'], 0, false, false, true, $numberstok) . ' {_or_more}'; // formats: 1000 as 1k
						}
						else
						{
							$html = stripslashes($res['title']) . ' (' . $ilance->currency->format($res['budgetfrom'], 0, false, false, true, $numberstok) . ' {_or_more})';
						}
					}
					else
					{
						if ($nobrackets AND $notext)
						{
							$html = $ilance->currency->format($res['budgetfrom'], 0, false, false, true, $numberstok) . ' &ndash; ' . $ilance->currency->format($res['budgetto'], 0, false, false, true, $numberstok);
						}
						else
						{
							$html = stripslashes($res['title']) . ' (' . $ilance->currency->format($res['budgetfrom'], 0, false, false, true, $numberstok) . ' &ndash; ' . $ilance->currency->format($res['budgetto'], 0, false, false, true, $numberstok) . ')';
						}
					}
				}
			}
		}
		else
		{
			$query = $ilance->db->query("
                                SELECT budgetgroup
                                FROM " . DB_PREFIX . "categories
                                WHERE cid = '" . intval($cid) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($query) > 0)
			{
				$rquery = $ilance->db->fetch_array($query, DB_ASSOC);
				$sql = $ilance->db->query("
                                        SELECT budgetid, budgetto, budgetfrom, title
                                        FROM " . DB_PREFIX . "budget
                                        WHERE budgetgroup = '" . $ilance->db->escape_string($rquery['budgetgroup']) . "'
                                        ORDER BY budgetfrom ASC
                                ", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					while ($res = $ilance->db->fetch_array($sql))
					{
						if (isset($selected) AND $selected == $res['budgetid'])
						{
							if ($res['budgetto'] == '-1')
							{
								if ($nobrackets AND $notext)
								{
									$html = $ilance->currency->format($res['budgetfrom'], 0, false, false, true, $numberstok) . ' {_or_more}';
								}
								else
								{
									$html = stripslashes($res['title']) . ' (' . $ilance->currency->format($res['budgetfrom'], 0, false, false, true, $numberstok) . ' {_or_more})';
								}
							}
							else
							{
								if ($nobrackets AND $notext)
								{
									$html = $ilance->currency->format($res['budgetfrom'], 0, false, false, true, $numberstok) . ' &ndash; ' . $ilance->currency->format($res['budgetto'], 0, false, false, true, $numberstok);
								}
								else
								{
									$html = stripslashes($res['title']) . ' (' . $ilance->currency->format($res['budgetfrom'], 0, false, false, true, $numberstok) . ' &ndash; ' . $ilance->currency->format($res['budgetto'], 0, false, false, true, $numberstok) . ')';
								}
							}
						}
					}
				}
			}
		}
		if (empty($html))
		{
			$html = '{_non_disclosed}';
		}
		return $html;
	}

	/**
        * Function to print the bid amount types pulldown menu.
        *
        * @param       integer       selected bid amount type value
        * @param       bool          disable the bid amount type pulldown menu (true or false)
        * @param       bool          enable javascript (true or false)
        * @param       integer       selected category id (optional)
        * @param       string        category type (service / product)
        *
        * @return      string        HTML representation of the bid amount types pulldown values
        */
	function construct_bidamounttype_pulldown($selected = '', $disable = 0, $dojs = 1, $cid = '', $cattype = '')
	{
		global $ilance, $ilconfig, $phrase, $show;
		$sel1 = $sel2 = $sel3 = $sel4 = $sel5 = $sel6 = $sel7 = $sel8 = $sel9 = $dis1 = $dis2 = '';
		if (isset($selected) AND !empty($selected))
		{
			if ($selected == 'entire')
			{
				$sel1 = 'selected="selected"';
			}
			if ($selected == 'hourly')
			{
				$sel2 = 'selected="selected"';
			}
			if ($selected == 'daily')
			{
				$sel3 = 'selected="selected"';
			}
			if ($selected == 'weekly')
			{
				$sel4 = 'selected="selected"';
			}
			if ($selected == 'monthly')
			{
				$sel6 = 'selected="selected"';
			}
			if ($selected == 'lot')
			{
				$sel7 = 'selected="selected"';
			}
			if ($selected == 'weight')
			{
				$sel8 = 'selected="selected"';
			}
			if ($selected == 'item')
			{
				$sel9 = 'selected="selected"';
			}
		}
		else
		{
			$selected = 'entire';
		}
		if (isset($disable) AND $disable AND !empty($selected))
		{
			$dis1 = 'disabled="disabled"';
			$dis2 = '<input type="hidden" name="filtered_bidtype" value="' . $selected . '" />';
		}
		$html = '<select name="filtered_bidtype" style="font-family: verdana" id="bidamounttype" ';
		if (isset($dojs) AND $dojs == '1')
		{
			$html .= 'onclick="javascript: document.ilform.filter_bidtype[0].checked=true;"';
			$html .= ' ' . $dis1 . '>';
		}
		else if (isset($dojs) AND $dojs == '2')
		{
			$html .= 'onchange="javascript:
if (document.ilform.filtered_bidtype.value == \'entire\')
{
    fetch_js_object(\'measure\').innerHTML = \'{_days}\'
}
else if (document.ilform.filtered_bidtype.value == \'hourly\')
{
    fetch_js_object(\'measure\').innerHTML = \'{_hours}\'
}
else if (document.ilform.filtered_bidtype.value == \'daily\')
{
    fetch_js_object(\'measure\').innerHTML = \'{_days}\'
}
else if (document.ilform.filtered_bidtype.value == \'weekly\')
{
    fetch_js_object(\'measure\').innerHTML = \'{_weeks}\'
}
else if (document.ilform.filtered_bidtype.value == \'monthly\')
{
    fetch_js_object(\'measure\').innerHTML = \'{_months}\'
}
else if (document.ilform.filtered_bidtype.value == \'lot\')
{
    fetch_js_object(\'measure\').innerHTML = \'{_days}\'
}
else if (document.ilform.filtered_bidtype.value == \'weight\')
{
    fetch_js_object(\'measure\').innerHTML = \'{_days}\'
}
else if (document.ilform.filtered_bidtype.value == \'item\')
{
    fetch_js_object(\'measure\').innerHTML = \'{_days}\'
}
" ' . $dis1 . '>';
		}
		else if (isset($dojs) AND $dojs == 0)
		{
			$html .= '>';
		}
		if ($cattype == 'service')
		{
			if (isset($cid) AND $cid > 0)
			{
				$data = $ilance->categories->bidamounttypes($cid);
				if (!empty($data))
				{
					$show['bidamounttypes'] = true;
					$data = unserialize($data);
					if (is_array($data))
					{
						foreach ($data AS $key => $value)
						{
							if (!empty($value) AND $value == 'entire')
							{
								$html .= '<option value="entire" ' . $sel1 . '>{_for_entire_project}</option>';
							}
							if (!empty($value) AND $value == 'hourly')
							{
								$html .= '<option value="hourly" ' . $sel2 . '>{_per_hour}</option>';
							}
							if (!empty($value) AND $value == 'daily')
							{
								$html .= '<option value="daily" ' . $sel3 . '>{_per_day}</option>';
							}
							if (!empty($value) AND $value == 'weekly')
							{
								$html .= '<option value="weekly" ' . $sel4 . '>{_weekly}</option>';
							}
							if (!empty($value) AND $value == 'monthly')
							{
								$html .= '<option value="monthly" ' . $sel6 . '>{_monthly}</option>';
							}
							if (!empty($value) AND $value == 'lot')
							{
								$checked7 = 'checked="checked"';
								$html .= '<option value="lot" ' . $sel7 . '>{_per_lot}</option>';
							}
							if (!empty($value) AND $value == 'weight')
							{
								$checked8 = 'checked="checked"';
								$html .= '<option value="weight" ' . $sel8 . '>{_per_weight}</option>';
							}
							if (!empty($value) AND $value == 'item')
							{
								$checked9 = 'checked="checked"';
								$html .= '<option value="item" ' . $sel9 . '>{_per_item}</option>';
							}
						}
					}
				}
				else
				{
					$html .= '<option value="entire" ' . $sel1 . '>{_for_entire_project}</option>';
					$show['bidamounttypes'] = false;
				}
			}
		}
		else if ($cattype == 'product')
		{
			if (isset($cid) AND $cid > 0)
			{
				$data = $ilance->categories->bidamounttypes($cid);
				if (!empty($data))
				{
					$show['bidamounttypes'] = true;
					$htmlx = '';
					$data = unserialize($data);
					if (is_array($data))
					{
						$doproduct = 0;
						foreach ($data AS $key => $value)
						{
							if (!empty($value) AND $value == 'lot')
							{
								$doproduct = 1;
								$htmlx .= '<option value="lot" ' . $sel7 . '>{_per_lot}</option>';
							}
							if (!empty($value) AND $value == 'weight')
							{
								$doproduct = 1;
								$htmlx .= '<option value="weight" '.$sel8.'>'.'{_per_weight}'.'</option>';
							}
							if (!empty($value) AND $value == 'item')
							{
								$doproduct = 1;
								$htmlx .= '<option value="item" '.$sel9.'>'.'{_per_item}'.'</option>';
							}
						}
					}
					if ($doproduct > 0)
					{
						$html .= '<optgroup label="'.'{_reverse_product_auction}'.'">';
						$html .= $htmlx;
						$html .= '</optgroup>';
					}
				}
				else
				{
					$show['bidamounttypes'] = false;
				}
			}
		}
		$html .= '</select>';
		$html .= '';
		$html .= $dis2;
		return $html;
	}

	/**
        * Function to print the bid amount types overview.
        *
        * @param       string        selected bid amount type (optional)
        *
        * @return      string        HTML representation of the bid amount types
        */
	function construct_bidamounttype($selected = '')
	{
		global $ilance, $ilconfig, $phrase;
		if (isset($selected) AND !empty($selected))
		{
			if ($selected == 'entire')
			{
				$html = '{_for_entire_project}';
			}
			if ($selected == 'hourly')
			{
				$html = '{_per_hour}';
			}
			if ($selected == 'daily')
			{
				$html = '{_per_day}';
			}
			if ($selected == 'weekly')
			{
				$html = '{_weekly}';
			}
			if ($selected == 'monthly')
			{
				$html = '{_monthly}';
			}
			if ($selected == 'lot')
			{
				$html = '{_per_lot}';
			}
			if ($selected == 'weight')
			{
				$html = '{_per_weight}';
			}
			if ($selected == 'item')
			{
				$html = '{_per_item}';
			}
		}
		else
		{
			$html = '{_for_entire_project}';
		}
		return $html;
	}

	/**
        * Function to print the phrased measure values of the selected bid amount types.
        *
        * @param       string        selected bid amount type (optional)
        *
        * @return      string        HTML representation of the bid amount types
        */
	function construct_measure($selected = '')
	{
		global $ilance, $ilconfig, $phrase;
		$html = '';
		if (isset($selected) AND !empty($selected))
		{
			if ($selected == 'entire')
			{
				$html = '{_days}';
			}
			else if ($selected == 'hourly')
			{
				$html = '{_hours}';
			}
			else if ($selected == 'daily')
			{
				$html = '{_days}';
			}
			else if ($selected == 'weekly')
			{
				$html = '{_weeks}';
			}
			else if ($selected == 'monthly')
			{
				$html = '{_months}';
			}
			else if ($selected == 'lot')
			{
				$html = '{_days}';
			}
			else if ($selected == 'weight')
			{
				$html = '{_days}';
			}
			else if ($selected == 'item')
			{
				$html = '{_days}';
			}
			else
			{
				$html = '{_days}';
			}
		}
		else
		{
			$html = '{_days}';
		}
		return $html;
	}

	/**
        * Function to fetch the insertion fee amount associated within a particular budget group
        *
        * @param       string        insertion group name
        *
        * @return      string        Insertion fee amount
        */
	function calculate_insertion_fee_in_budget_group($groupname = '')
	{
		global $ilance;
		$fee = 0.00;
		$ifgroupname = $groupname;
		if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
		{
			$forceifgroupid = $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'serviceinsgroup');
			if ($forceifgroupid > 0)
			{
				$ifgroupname = $ilance->db->fetch_field(DB_PREFIX . "insertion_groups", "groupid = '" . intval($forceifgroupid) . "'", "groupname");
			}
		}
		$sql = $ilance->db->query("
                        SELECT amount
                        FROM " . DB_PREFIX . "insertion_fees
                        WHERE groupname = '" . $ilance->db->escape_string($ifgroupname) . "'
                            AND state = 'service'
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($rows = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$fee += $rows['amount'];
			}
		}
		return $fee;
	}

	/**
        * Function to return template array data for recently used keywords entered by the viewing user.
        *
        * @param       integer       user id
        * @param       integer       number of columns
        * @param       integer       number of rows
        * @param       integer       category id (optional)
        * @param       string        search keywords previously entered like "xyz" (optional)
        *
        * @return      string        Returns template array data for use with parse_loop() function
        */
	function fetch_recently_used_keywords($userid = 0, $columns = 2, $rows = 5, $cid = 0, $keywordslike = '', $cattype = 'product')
	{
		global $ilance, $ilconfig, $phrase, $show;
		if ($userid > 0)
		{
			$query = "user_id = '" . intval($userid) . "'";
		}
		else
		{
			$query = "ipaddress = '" . $ilance->db->escape_string(IPADDRESS) . "'";
		}
		$extraquery = "AND searchmode = '" . $ilance->db->escape_string($cattype) . "'";
		$limit = ($columns * $rows);
		$recentkeywords = array();
		$sql = $ilance->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "keyword, added, searchmode
                        FROM " . DB_PREFIX . "search_users
                        WHERE $query
				AND uservisible = '1'
				AND keyword != ''
				$extraquery
			GROUP BY keyword
			ORDER BY id DESC
			LIMIT $limit
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$res['added'] = print_date($res['added'], 0, 0);
				$keyword = $res['keyword'];
				$res['keyword'] = handle_input_keywords(shorten(print_string_wrap($keyword, 25), $ilconfig['keywords_tab_textcutoff']));
				$res['keywordfull'] = handle_input_keywords(print_string_wrap($keyword, 25));
				$res['keywordurlencoded'] = urlencode($keyword);
				unset($keyword);
				$recentkeywords[] = $res;
			}
		}
		return $recentkeywords;
	}

	/**
        * Function to handle the expiry of featured home page listings
        *
        * @return      nothing
        */
	function expire_featured_status_listings()
	{
		global $ilance, $ilconfig, $phrase;
		$sql = $ilance->db->query("
                        SELECT project_id, featured_date
                        FROM " . DB_PREFIX . "projects
                        WHERE featured = '1'
                                AND featured_date != '0000-00-00 00:00:00'
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$date1split = explode(' ', $res['featured_date']);
				$date2split = explode('-', $date1split[0]);
				$totaldays = $ilconfig['productupsell_featuredlength'];
				$elapsed = $ilance->datetimes->fetch_days_between($date2split[1], $date2split[2], $date2split[0], date('m'), date('d'), date('Y'));
				$days = ($totaldays - $elapsed);
				if ($days < 0)
				{
					if ($ilconfig['productupsell_featuredlength'] > 0)
					{
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "projects
							SET featured = '0',
							featured_date = '0000-00-00 00:00:00'
							WHERE project_id = '" . $res['project_id'] . "'
						", 0, null, __FILE__, __LINE__);
					}
				}
			}
		}
	}

	/**
        * Function to handle the verification of auction counters in the category system.  This function runs daily within cron.dailyrfp.php
        *
        * @return      string         Report on the number of rebuilt category counters performed.
        */
	function category_listing_count_fixer()
	{
		global $ilance, $ilconfig, $phrase, $show;
		$customquery = '';
		
		($apihook = $ilance->api('category_listing_count_fixer_start')) ? eval($apihook) : false;
		
		$cronlog = '';
		if ($ilconfig['globalauctionsettings_payperpost'])
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET status = 'frozen'
				WHERE status = 'open'
					AND ((enhancementfee > 0 AND enhancementfeeinvoiceid > 0 AND isenhancementfeepaid = '0') OR (insertionfee > 0 AND ifinvoiceid > 0 AND isifpaid = '0'))
			", 0, null, __FILE__, __LINE__);
		}
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "categories
			SET auctioncount = '0'
		", 0, null, __FILE__, __LINE__);
		$i = 0;
		$sql = $ilance->db->query("
			SELECT cid
			FROM " . DB_PREFIX . "projects
			WHERE status = 'open'
				AND visible = '1'
			$customquery
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$ilance->categories->build_category_count($res['cid'], 'add', "category_listing_count_fixer(): adding increment count category id $res[cid]", false);
				$i++;
			}
		}
		$cronlog .= 'category_listing_count_fixer(): Rebuilt category listing counters for ' . $i . ' listings, ';
		return $cronlog;
	}

	/**
        * Function to watchlist emails for relist product auction
        *
        * @return      nothing
        */
	function relist_watchitems($id = 0, $rfpid = 0)
	{
		global $ilance , $ilconfig, $ilpage;
		$sql = $ilance->db->query("
			SELECT user_id
			FROM " . DB_PREFIX . "watchlist
			WHERE watching_project_id = '" . intval($id) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{ 
			$current_bid = $ilance->currency->format(fetch_auction('currentprice', $rfpid));
			$buynow_price = $ilance->currency->format(fetch_auction('buynow_price', $rfpid));
			$url = HTTP_SERVER.$ilpage['merch'] . '?id=' . $rfpid;
			$project_title = fetch_auction('project_title', $rfpid);
			$sql1 = $ilance->db->query("
				SELECT date_starts, date_end, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . intval($rfpid) . "'
			", 0, null, __FILE__, __LINE__);
			$row2 = $ilance->db->fetch_array($sql1, DB_ASSOC);
			$enddate = print_date($row2['date_end']);	 
			$timeleft = $this->calculate_time_left($row2['date_starts'], $row2['starttime'], $row2['mytime']);			
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$username = fetch_user('username',$res['user_id']);
				
				$ilance->email->mail = fetch_user('email',$res['user_id']);
				$ilance->email->slng = fetch_user_slng($res['user_id']);
				$ilance->email->get('watchlist_relist_reminder');		
				$ilance->email->set(array(
					'{{seller}}' => $_SESSION['ilancedata']['user']['username'],
					'{{buyer}}' => $username,					
					'{{project_title}}' => $project_title,
					'{{url}}'   => $url,
					'{{currentbid}}' => $current_bid,
					'{{buynow_price}}' => $buynow_price,
					'{{enddate}}' => $enddate,
					'{{timeleft}}' => $timeleft,
				));
				$ilance->email->send();		
			}
		}
	}	
	
	/**
        * Function to rewrite photos based on orginal auction to new relisted auction 
        *
        * @return      nothing
        */
	function rewrite_photos($old_id, $new_id)
	{
		global $ilance, $ilconfig;
		$sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "attachment
                        WHERE project_id = '" . intval($old_id) . "'
                ", 0, null, __FILE__, __LINE__);
		while ($res_photo = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "attachment
                                (attachid, attachtype, user_id, project_id, category_id, date, filename, filedata, filedata_original, filedata_full, filedata_mini, filedata_search, filedata_gallery, filedata_snapshot, filetype, filetype_original, width, width_original, width_full, width_mini, width_search, width_gallery, width_snapshot, height, height_original, height_full, height_mini, height_search, height_gallery, height_snapshot, visible, counter, filesize, filesize_original, filesize_full, filesize_mini, filesize_search, filesize_gallery, filesize_snapshot, filehash, ipaddress, tblfolder_ref, exifdata, isexternal, watermarked)
                                VALUES(
                                NULL,
                                '" . $ilance->db->escape_string($res_photo['attachtype']) . "',
                                '" . $res_photo['user_id'] . "',
                                '" . intval($new_id) . "',
                                '" . intval($res_photo['category_id']) . "',
                                '" . DATETIME24H . "',
                                '" . $ilance->db->escape_string($res_photo['filename']) . "',
                                '" . $ilance->db->escape_string($res_photo['filedata']) . "',
				'" . $ilance->db->escape_string($res_photo['filedata_original']) . "',
				'" . $ilance->db->escape_string($res_photo['filedata_full']) . "',
				'" . $ilance->db->escape_string($res_photo['filedata_mini']) . "',
				'" . $ilance->db->escape_string($res_photo['filedata_search']) . "',
				'" . $ilance->db->escape_string($res_photo['filedata_gallery']) . "',
				'" . $ilance->db->escape_string($res_photo['filedata_snapshot']) . "',
                                '" . $ilance->db->escape_string($res_photo['filetype']) . "',
				'" . $ilance->db->escape_string($res_photo['filetype_original']) . "',
				'" . intval($res_photo['width']) . "',
				'" . intval($res_photo['width_original']) . "',
				'" . intval($res_photo['width_full']) . "',
				'" . intval($res_photo['width_mini']) . "',
				'" . intval($res_photo['width_search']) . "',
				'" . intval($res_photo['width_gallery']) . "',
				'" . intval($res_photo['width_snapshot']) . "',
				'" . intval($res_photo['height']) . "',
				'" . intval($res_photo['height_original']) . "',
				'" . intval($res_photo['height_full']) . "',
				'" . intval($res_photo['height_mini']) . "',
				'" . intval($res_photo['height_search']) . "',
				'" . intval($res_photo['height_gallery']) . "',
				'" . intval($res_photo['height_snapshot']) . "',
                                '" . $ilconfig['attachment_moderationdisabled'] . "',
                                '0',
                                '" . $ilance->db->escape_string($res_photo['filesize']) . "',
				'" . $ilance->db->escape_string($res_photo['filesize_original']) . "',
				'" . $ilance->db->escape_string($res_photo['filesize_full']) . "',
				'" . $ilance->db->escape_string($res_photo['filesize_mini']) . "',
				'" . $ilance->db->escape_string($res_photo['filesize_search']) . "',
				'" . $ilance->db->escape_string($res_photo['filesize_gallery']) . "',
				'" . $ilance->db->escape_string($res_photo['filesize_snapshot']) . "',
                                '" . $ilance->db->escape_string($res_photo['filehash']) . "',
                                '" . $ilance->db->escape_string($res_photo['ipaddress']) . "',
				'" . $ilance->db->escape_string($res_photo['tblfolder_ref']) . "',
				'" . $ilance->db->escape_string($res_photo['exifdata']) . "',
				'" . $ilance->db->escape_string($res_photo['isexternal']) . "',
				'" . $ilance->db->escape_string($res_photo['watermarked']) . "')
                        ", 0, null, __FILE__, __LINE__);
		}
	}
	
	/**
        * Function to handle the removal of item photos and slideshow images from deleted auctions in the database.  This function runs monthly within cron.monthly.php
        *
        * @return      string         Report on the number of images removed from unlinked projects.
        */
	function remove_photos_from_deleted_listings()
	{
		global $ilance, $ilconfig;
		$cronlog = '';
		$i = 0;
		$sql = $ilance->db->query("
                        SELECT attachid, user_id, project_id
                        FROM " . DB_PREFIX . "attachment
			WHERE attachtype = 'itemphoto' OR attachtype = 'slideshow'
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$sql2 = $ilance->db->query("
					SELECT project_id
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . $res['project_id'] . "'
					LIMIT 1
				");
				if ($ilance->db->num_rows($sql2) == 0)
				{
					if ($res['user_id'] > 0)
					{
						$ilance->attachment->remove_attachment($res['attachid'], $res['user_id']);
					}
					else
					{
						$ilance->attachment->remove_attachment($res['attachid']);
					}
					$i++;	
				}
			}
		}
		$cronlog .= 'Removed ' . $i . ' obsolete item pictures for listings that no longer exist, ';
		return $cronlog;
	}
	
	/**
        * Function to handle the removal of item question / answers from deleted listings in the database.  This function runs daily within cron.dailyrfp.php
        *
        * @return      string         Report on the number of images removed from unlinked projects.
        */
	function remove_answers_from_deleted_listings()
	{
		global $ilance, $ilconfig, $phrase;
		$cronlog = '';
		$i = 0;
		$sql = $ilance->db->query("
                        SELECT answerid, project_id
                        FROM " . DB_PREFIX . "product_answers
                        GROUP BY project_id
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				// see if this listing for the answer exists
				$sql2 = $ilance->db->query("
					SELECT project_id
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . $res['project_id'] . "'
					LIMIT 1
				");
				if ($ilance->db->num_rows($sql2) == 0)
				{
					$ilance->db->query("
						DELETE FROM " . DB_PREFIX . "product_answers
						WHERE project_id = '" . $res['project_id'] . "'
					");
					$i++;	
				}
			}
		}
		unset($sql, $sql2);
		$sql = $ilance->db->query("
                        SELECT answerid, project_id
                        FROM " . DB_PREFIX . "project_answers
                        GROUP BY project_id
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$sql2 = $ilance->db->query("
					SELECT project_id
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . $res['project_id'] . "'
					LIMIT 1
				");
				if ($ilance->db->num_rows($sql2) == 0)
				{
					$ilance->db->query("
						DELETE FROM " . DB_PREFIX . "project_answers
						WHERE project_id = '" . $res['project_id'] . "'
					");
					$i++;	
				}
			}
		}
		$cronlog .= 'Removed ' . $i . ' listings where obsolete answer specifics no longer exist, ';
		return $cronlog;
	}
	
	/**
        * Function to handle the removal of item shipping regions from deleted listings in the database.  This function runs daily within cron.dailyrfp.php
        *
        * @return      string         Report on the number of images removed from unlinked projects.
        */
	function remove_shipping_regions_from_deleted_listings()
	{
		global $ilance, $ilconfig, $phrase;
		$cronlog = '';
		$i = $x = 0;
		$sql = $ilance->db->query("
                        SELECT project_id
                        FROM " . DB_PREFIX . "projects_shipping_regions
                        GROUP BY project_id
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$sql2 = $ilance->db->query("
					SELECT project_id
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . $res['project_id'] . "'
					LIMIT 1
				");
				if ($ilance->db->num_rows($sql2) == 0)
				{
					$ilance->db->query("
						DELETE FROM " . DB_PREFIX . "projects_shipping_regions
						WHERE project_id = '" . $res['project_id'] . "'
					");
					$i++;	
				}
			}
		}
		$sql = $ilance->db->query("
                        SELECT project_id
                        FROM " . DB_PREFIX . "projects_shipping
                        GROUP BY project_id
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$sql2 = $ilance->db->query("
					SELECT project_id
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . $res['project_id'] . "'
					LIMIT 1
				");
				if ($ilance->db->num_rows($sql2) == 0)
				{
					$ilance->db->query("
						DELETE FROM " . DB_PREFIX . "projects_shipping
						WHERE project_id = '" . $res['project_id'] . "'
					");
					$x++;	
				}
			}
		}
		$cronlog .= 'Removed ' . $i . ' obsolete shipping regions for listings that no longer exist, Removed ' . $x . ' shipping details for listings that no longer exist, ';
		return $cronlog;
	}
	
	function archive_expired_auctions()
	{
		global $ilance, $ilconfig;
		$cronlog = '';
		if (is_numeric($ilconfig['globalauctionsettings_archivedays']) AND intval($ilconfig['globalauctionsettings_archivedays']) > 0)
		{
			$date = time() - (3600 * 24 * $ilconfig['globalauctionsettings_archivedays']);
			$sql = $ilance->db->query("
	                        SELECT project_id
	                        FROM " . DB_PREFIX . "projects
	                        WHERE (status = 'closed' OR status = 'expired' OR status = 'finished')
				        AND visible = '1'
		                        AND UNIX_TIMESTAMP(date_end) > 0
		                        AND UNIX_TIMESTAMP(date_end) <= $date
	                ", 0, null, __FILE__, __LINE__);
			$i = $ilance->db->num_rows($sql);
			if ($i > 0)
			{
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
				        $ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET status = 'archived'
						WHERE project_id = '" . $res['project_id'] . "'
						LIMIT 1
				        ", 0, null, __FILE__, __LINE__);
				}
			}
			$cronlog .= 'Archived ' . $i . ' listings after ' . $ilconfig['globalauctionsettings_archivedays'] . ' days, ';
		}
		return $cronlog;
	}
	
	/**
        * Function to fetch the request for proposal minimum budget and maximum budget amount for a particular project
        *
        * @param       integer       project id
        *
        * @return      array        minimum budget and maximum budget amount
        */
	function fetch_rfp_budget_low_high($projectid = 0)
	{
		global $ilance, $phrase, $ilconfig;
		$sql = $ilance->db->query("
                        SELECT budgetgroup, filter_budget, filtered_budgetid
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . intval($projectid) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$budget = array(-1, -1);
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			if ($res['filter_budget'] > 0 AND $res['filtered_budgetid'] > 0)
			{
				$sql2 = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "budget
                                        WHERE budgetid = '" . $res['filtered_budgetid'] . "'
                                ", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql2) > 0)
				{
					$res2 = $ilance->db->fetch_array($sql2, DB_ASSOC);
					if ($res2['budgetto'] == '-1')
					{
						$budget[] = array($res2['budgetfrom'], $res2['budgetto']);
					}
					else
					{
						$budget = array($res2['budgetfrom'], $res2['budgetto']);
					}
				}
				else
				{
					$budget = array(-1, -1);
				}
			}
			return $budget;
		}
	}
	
	/**
        * Function to handle the removal of item photos and slideshow images from deleted auctions in the database.  This function runs daily within cron.dailyrfp.php
        *
        * @return      string         Report on the number of images removed from unlinked projects.
        */
	function remove_bulk_temp_listings()
	{
		global $ilance, $ilconfig, $phrase;
		$cronlog = '';
		$i = 0;
		$sql = $ilance->db->query("
                        SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "bulk_tmp
			WHERE dateupload != '" . DATETODAY . "'
                ", 0, null, __FILE__, __LINE__);
		$res = $ilance->db->fetch_array($sql);
		$i = (int)$res['count'];
		$ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "bulk_tmp
			WHERE dateupload != '" . DATETODAY . "'
                ", 0, null, __FILE__, __LINE__);
		$cronlog .= 'Removed ' . number_format($i) . ' bulk temp uploaded listings from the previous day, ';
		return $cronlog;
	}
	
	/**
	* Function to parse various marketplace stats for the main home page
	*
	* @return      string       HTML formatted presentation of the stats
	*/
	function fetch_stats_overview($cid = 0, $cattype = '', $period = 0)
	{
		global $ilance, $ilconfig;
		require_once(DIR_CORE . 'functions_search.php');
		$array = array();
		$array['jobcount'] = $array['expertsearch'] = $array['expertcount'] = $array['expertsrevenue'] = $array['scheduledcount'] = $array['itemsworth'] = $array['itemcount'] = 0;
		$extra1 = '';
		if ($cid > 0)
		{
			$childrenids = $ilance->categories->fetch_children_ids($cid, $cattype);
			$subcategorylist = $cid . ',' . $childrenids;
			$extra1 = "AND (FIND_IN_SET(p.cid, '$subcategorylist'))";
			unset($subcategorylist, $childrenids);
		}
		if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
		{
			// #### jobs posted in the marketplace #################################
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "COUNT(*) AS jobcount
				FROM " . DB_PREFIX . "projects p
				WHERE p.project_state = 'service'
					AND p.status = 'open'
					AND p.visible = '1'
					$extra1
					" . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.enhancementfee = 0 OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '1')) AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
			", 0, null, __FILE__, __LINE__);
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);        
			$array['jobcount'] = $res['jobcount'];
			// #### total amount of revenue experts have earned ####################
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "SUM(bidamount) AS expertsrevenue
				FROM " . DB_PREFIX . "project_realtimebids b
				INNER JOIN " . DB_PREFIX . "projects p ON (b.project_id = p.project_id)
				WHERE b.state = 'service'
					AND b.bidstatus = 'awarded'
					$extra1
				GROUP BY b.project_id
			", 0, null, __FILE__, __LINE__);
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);  
			$array['expertsrevenue'] = $res['expertsrevenue'];
			// #### active experts displaying profile in search ####################
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "u.user_id
				FROM " . DB_PREFIX . "users u
				INNER JOIN " . DB_PREFIX . "profile_categories p ON (u.user_id = p.user_id)
				WHERE u.rateperhour >= 0
					AND u.status = 'active'
					$extra1
				GROUP BY u.user_id	
			", 0, null, __FILE__, __LINE__);
			$array['expertcount'] = $ilance->db->num_rows($sql);
			// #### active, subscribed displaying profile in search ################
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "COUNT(*) AS expertsearch
				FROM " . DB_PREFIX . "users users
				LEFT JOIN " . DB_PREFIX . "subscription_user user ON (users.user_id = user.user_id)
				INNER JOIN " . DB_PREFIX . "profile_categories p ON (users.user_id = p.user_id)
				WHERE users.status = 'active'
					AND users.displayprofile = '1'
					AND user.active = 'yes'
					$extra1
			", 0, null, __FILE__, __LINE__);
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$array['expertsearch'] = $res['expertsearch'];
		}
		if ($ilconfig['globalauctionsettings_productauctionsenabled'])
		{
			// #### total amount of items in marketplace ###########################
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "COUNT(*) AS itemcount
				FROM " . DB_PREFIX . "projects p
				WHERE p.project_state = 'product'
					AND p.status = 'open'
					AND p.visible = '1'
					$extra1
					" . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.enhancementfee = 0 OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '1')) AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
			", 0, null, __FILE__, __LINE__);
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$array['itemcount'] = $res['itemcount'];
			// #### total amount of item worth in marketplace ######################
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "SUM(bidamount) AS itemsworth
				FROM " . DB_PREFIX . "project_bids
				WHERE state = 'product'
					AND bidstatus = 'awarded'
			", 0, null, __FILE__, __LINE__);
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$array['itemsworth'] = $res['itemsworth'];
			// #### total amount of scheduled auctions in marketplace ##############
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "COUNT(*) AS scheduledcount
				FROM " . DB_PREFIX . "projects p
				WHERE p.project_state = 'product'
					AND p.project_details = 'realtime'
					AND p.status = 'open'
					AND p.date_starts > '" . DATETIME24H . "'
					AND p.visible = '1'
					" . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.enhancementfee = 0 OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '1')) AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
			", 0, null, __FILE__, __LINE__);
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$array['scheduledcount'] = $res['scheduledcount'];
		}
		return $array;
	}
	/**
	* Function to print the video applet on the listing page.
	*
	* @param       integer        listing id
	* @param       string         video url (if not specified, will connect to the db)
	* @param       integer        video width (default 290px)
	* @param       integer        video height (default 240px)
	* @param       string         additional custom script code
	*
	* @return      string         Returns the video embed code
	*/
	function print_listing_video($projectid = 0, $url = '', $videowidth = '290', $videoheight = '240')
	{
		global $ilance, $show, $ilconfig, $phrase;
		$uniqueid = rand(1, 9999);
		$html = '';
		if (empty($url))
		{
			$videourl = fetch_auction('description_videourl', $projectid);
			$videourl = parse_youtube_video_url($videourl, true);
		}
		else
		{
			$videourl = parse_youtube_video_url($url, true);
		}
		if (!empty($videourl))
		{
			$show['videodescription'] = true;
			$html = '<script type="text/javascript">
<!--
function onYouTubeIframeAPIReady()
{
	var player;
	player = new YT.Player(\'player\',
	{
		width: ' . $videowidth . ',
		height: ' . $videoheight . ',
		videoId: \'' . $videourl . '\',
		events:
		{
			\'onReady\': onPlayerReady,
			\'onPlaybackQualityChange\': onPlayerPlaybackQualityChange,
			\'onStateChange\': onPlayerStateChange,
			\'onError\': onPlayerError
		}
	});
}
function onPlayerReady(event)
{
	event.target.setVolume(100);
	event.target.playVideo();
}
//-->
</script>';
			$html .= '<iframe id="player" type="text/html" width="' . $videowidth . '" height="' . $videoheight . '" src="http://www.youtube.com/embed/' . $videourl . '?enablejsapi=1&origin=' . HTTP_SERVER . '" frameborder="0" allowfullscreen></iframe>';
		}
		else
		{
			$show['videodescription'] = false;
		}
		return $html;
	}
	
      function recently_viewed_handler($id = 0, $mode = '')
      {
		global $ilance;
		if (isset($_SESSION['ilancedata'][$mode]['list']))
		{
			$arr = explode('|', $_SESSION['ilancedata'][$mode]['list']);
			if (!in_array(intval($id), $arr))
			{
				$_SESSION['ilancedata'][$mode]['list'] = $_SESSION['ilancedata'][$mode]['list'] . "|" . $id;
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "projects
					SET views = views + 1
					WHERE project_id = '" . intval($id) . "'
					    AND status != 'draft'
					LIMIT 1
			       ", 0, null, __FILE__, __LINE__);
			}
		}
		else
		{
			$_SESSION['ilancedata'][$mode]['list'] = intval($id);
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET views = views + 1
				WHERE project_id = '" . intval($id) . "'
				    AND status != 'draft'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
		}
		if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "id
				FROM " . DB_PREFIX . "search_users
				WHERE project_id = '" . intval($id) . "'
				       AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				       AND searchmode = '" . $ilance->db->escape_string($mode) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) == 0)
			{
				$ilance->db->query("
				       INSERT INTO " . DB_PREFIX . "search_users
				       (id, user_id, project_id, cid, keyword, searchmode, added, ipaddress, uservisible)
				       VALUES (
				       NULL,
				       '" . $_SESSION['ilancedata']['user']['userid'] . "',
				       '" . intval($id) . "',
				       '0',
				       '',
				       '" . $ilance->db->escape_string($mode) . "',
				       NOW(),
				       '" . $ilance->db->escape_string(IPADDRESS) . "',
				       '1')
			      ");
			}
		}
		else
		{
			if (defined('IPADDRESS') AND IPADDRESS != '')
			{
				$sql = $ilance->db->query("
					SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "id
					FROM " . DB_PREFIX . "search_users
					WHERE project_id = '" . intval($id) . "'
						AND ipaddress = '" . $ilance->db->escape_string(IPADDRESS) . "'
						AND searchmode = '" . $ilance->db->escape_string($mode) . "'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) == 0)
				{
					$ilance->db->query("
						INSERT INTO " . DB_PREFIX . "search_users
						(id, user_id, project_id, cid, keyword, searchmode, added, ipaddress, uservisible)
						VALUES (
						NULL,
						'0',
						'" . intval($id) . "',
						'0',
						'',
						'" . $ilance->db->escape_string($mode) . "',
						NOW(),
						'" . $ilance->db->escape_string(IPADDRESS) . "',
						'1')
					");
				}
			}
		}
	}
    
	/**
	* Function to print an item photo via <img src> for a particular auction id
	*
	* @param       string         url where the photo should link to
	* @param       string         mode (thumb, thumbgallery, thumbmini, full, checkup)
	* @param       integer        auction id
	* @param       integer        border width
	* @param       string         border color (default #ffffff)
	* @param       integer        if there are mulitple photos it is a photo id to start read from
	* @param       string         attachment type
	* @param       boolean        load image src from a remote server (not relative like /images/image.gif) (default false)
	* @param       integer        limit results (default 1)
	* @param       boolean        force no css3 ribbon effect (default false)
	* @param       boolean        force clean <img> output without CSS (default false)
	* @param       boolean        force only <img src=""> string (default false)
	*
	* @return      bool           Returns HTML representation of the item photo via <img src> tag including valid href url
	*/
	function print_item_photo($url = '', $mode = '', $projectid = 0, $borderwidth = 0, $bordercolor = '#ffffff', $start_from_image = 0, $attachtype = '', $httponly = false, $limit = 1, $forcenoribbon = true, $forceplainimg = false, $forceimgsrc = false)
	{
		global $ilance, $ilconfig, $ilpage, $phrase;
		if ($attachtype == '')
		{
			$attachtype_sql = "AND (a.attachtype = 'itemphoto' OR a.attachtype = 'slideshow')";
		}
		else 
		{
			$attachtype_sql = "AND a.attachtype = '" . $ilance->db->escape_string($attachtype) . "'";
		}
		$html = $htmlx = $htmly = $htmlxx = $htmlyy = '';
		$orderby = "ORDER BY a.attachid ASC, a.attachtype ASC";
		$isitemphoto = 0;
		$imgsrc = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'];
		if ($httponly)
		{
			$imgsrc = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilconfig['template_imagesfolder'];
		}
		if ($start_from_image > 0)
		{
			$limit_sql = "LIMIT " . intval($start_from_image) . ", 1";
		}
		else
		{
			if ($limit > 1)
			{
				$limit_sql = "LIMIT $limit";
			}
			else
			{
				$limit_sql = "LIMIT 1";
			}	
		}
		$ufile = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "a.attachid, a.filename, a.filehash, a.attachtype, a.width, a.height, a.width_search, a.height_search, a.width_mini, a.height_mini, a.width_gallery, a.height_gallery, a.width_full, a.height_full, p.project_title, p.buynow_qty, p.buynow
			FROM " . DB_PREFIX . "attachment a
			LEFT JOIN " . DB_PREFIX . "projects p ON (a.project_id = p.project_id)
			WHERE a.project_id = '" . intval($projectid) . "'
				AND a.visible = '1'
				$attachtype_sql
			$orderby
			$limit_sql
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($ufile) > 0)
		{
			$pictures = 0;
			while ($rfile = $ilance->db->fetch_array($ufile, DB_ASSOC))
			{
				$rfile['project_title'] = handle_input_keywords($rfile['project_title']);
				$project_title = $rfile['project_title'];
				$show['oneleft'] = false;
				if ($rfile['attachtype'] == 'itemphoto')
				{
					$pictures++;
					$isitemphoto = 1;
					switch ($mode)
					{
						case 'thumb':
						{
							$htmlx = '<img src="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . (($ilconfig['globalauctionsettings_seourls'])
								? 'i/thumb/results/' . $rfile['filehash'] . '/' . $rfile['width_search'] . 'x' . $rfile['height_search'] . '_' . $rfile['filename']
								: $ilpage['attachment'] . '?cmd=thumb&amp;subcmd=results&amp;id=' . $rfile['filehash']) . '" alt="' . $rfile['project_title'] . '" />';
							if ($rfile['buynow'] AND $rfile['buynow_qty'] == '1' AND $ilconfig['oneleftribbonsearchresults'] AND $forcenoribbon == false AND $borderwidth > 0)
							{
								$htmlx .= '<p><span>{_one_left}</span></p>';
							}
							$htmlsrc = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . (($ilconfig['globalauctionsettings_seourls'])
								? 'i/thumb/results/' . $rfile['filehash'] . '/' . $rfile['width_search'] . 'x' . $rfile['height_search'] . '_' . $rfile['filename']
								: $ilpage['attachment'] . '?cmd=thumb&amp;subcmd=results&amp;id=' . $rfile['filehash']);
    
							($apihook = $ilance->api('foto_thumb')) ? eval($apihook) : false;
    
							break;
						}
						case 'thumbgallery':
						{
							$htmlx = '<img src="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . (($ilconfig['globalauctionsettings_seourls'])
								? 'i/thumb/resultsgallery/' . $rfile['filehash'] . '/' . $rfile['width_gallery'] . 'x' . $rfile['height_gallery'] . '_' . $rfile['filename']
								: $ilpage['attachment'] . '?cmd=thumb&amp;subcmd=resultsgallery&amp;id=' . $rfile['filehash']) . '" alt="' . $rfile['project_title'] . '" />';
							if ($rfile['buynow'] AND $rfile['buynow_qty'] == '1' AND $ilconfig['oneleftribbonsearchresults'] AND $forcenoribbon == false AND $borderwidth > 0)
							{
								$htmlx .= '<p><span>{_one_left}</span></p>';
							}
							$htmlsrc = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . (($ilconfig['globalauctionsettings_seourls'])
								? 'i/thumb/resultsgallery/' . $rfile['filehash'] . '/' . $rfile['width_gallery'] . 'x' . $rfile['height_gallery'] . '_' . $rfile['filename']
								: $ilpage['attachment'] . '?cmd=thumb&amp;subcmd=resultsgallery&amp;id=' . $rfile['filehash']);
    
							($apihook = $ilance->api('foto_thumbgallery')) ? eval($apihook) : false;
    
							break;
						}
						case 'thumbmini':
						{
							$htmlx = '<img src="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . (($ilconfig['globalauctionsettings_seourls'])
								? 'i/thumb/itemphotomini/' . $rfile['filehash'] . '/' . $rfile['width_mini'] . 'x' . $rfile['height_mini'] . '_' . $rfile['filename']
								: $ilpage['attachment'] . '?cmd=thumb&amp;subcmd=itemphotomini&amp;id=' . $rfile['filehash']) . '" alt="' . $rfile['project_title'] . '" />';
							$htmlsrc = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . (($ilconfig['globalauctionsettings_seourls'])
								? 'i/thumb/itemphotomini/' . $rfile['filehash'] . '/' . $rfile['width_mini'] . 'x' . $rfile['height_mini'] . '_' . $rfile['filename']
								: $ilpage['attachment'] . '?cmd=thumb&amp;subcmd=itemphotomini&amp;id=' . $rfile['filehash']);
							
							($apihook = $ilance->api('foto_thumbmini')) ? eval($apihook) : false;
    
							break;
						}
						case 'full':
						{
							$html = (empty($url) ? '' : '<a href="' . $url . '" title="' . $rfile['project_title'] . '">') . '<img src="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . (($ilconfig['globalauctionsettings_seourls'])
								? 'i/' . $rfile['filehash'] . '/' . $rfile['filename']
								: $ilpage['attachment'] . '?id=' . $rfile['filehash']) . '" id=""' . (($forceplainimg) ? '' : ' border="' . $borderwidth . '" style="border-color:' . $bordercolor . '') . '" alt="' . $rfile['project_title'] . '" />' . (empty($url) ? '' : '</a>');
							$htmlsrc = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . (($ilconfig['globalauctionsettings_seourls'])
								? 'i/' . $rfile['filehash'] . '/' . $rfile['filename']
								: $ilpage['attachment'] . '?id=' . $rfile['filehash']);
							
							($apihook = $ilance->api('foto_full')) ? eval($apihook) : false;
    
							break;
						}
						case 'checkup':
						{
							return '1';
							break;
						}
					}
				}
				else if ($rfile['attachtype'] == 'slideshow')
				{
					$pictures++;
					switch ($mode)
					{
						case 'thumb':
						{
							$htmlx = '<img src="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . (($ilconfig['globalauctionsettings_seourls'])
								? 'i/thumb/results/' . $rfile['filehash'] . '/' . $rfile['width_search'] . 'x' . $rfile['height_search'] . '_' . $rfile['filename']
								: $ilpage['attachment'] . '?cmd=thumb&amp;subcmd=results&amp;id=' . $rfile['filehash']) . '" alt="' . $rfile['project_title'] . '" />';
							if ($rfile['buynow'] AND $rfile['buynow_qty'] == '1' AND $ilconfig['oneleftribbonsearchresults'] AND $forcenoribbon == false AND $borderwidth > 0)
							{
								$htmlx .= '<p><span>{_one_left}</span></p>';
							}
							$htmlsrc = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . (($ilconfig['globalauctionsettings_seourls'])
								? 'i/thumb/results/' . $rfile['filehash'] . '/' . $rfile['width_search'] . 'x' . $rfile['height_search'] . '_' . $rfile['filename']
								: $ilpage['attachment'] . '?cmd=thumb&amp;subcmd=results&amp;id=' . $rfile['filehash']);
							break;
						}
						case 'thumbgallery':
						{
							$htmlx = '<img src="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . (($ilconfig['globalauctionsettings_seourls'])
								? 'i/thumb/resultsgallery/' . $rfile['filehash'] . '/' . $rfile['width_gallery'] . 'x' . $rfile['height_gallery'] . '_' . $rfile['filename']
								: $ilpage['attachment'] . '?cmd=thumb&amp;subcmd=resultsgallery&amp;id=' . $rfile['filehash']) . '" alt="' . $rfile['project_title'] . '" />';
							if ($rfile['buynow'] AND $rfile['buynow_qty'] == '1' AND $ilconfig['oneleftribbonsearchresults'] AND $forcenoribbon == false AND $borderwidth > 0)
							{
								$htmlx .= '<p><span>{_one_left}</span></p>';
							}
							$htmlsrc = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . (($ilconfig['globalauctionsettings_seourls'])
								? 'i/thumb/resultsgallery/' . $rfile['filehash'] . '/' . $rfile['width_gallery'] . 'x' . $rfile['height_gallery'] . '_' . $rfile['filename']
								: $ilpage['attachment'] . '?cmd=thumb&amp;subcmd=resultsgallery&amp;id=' . $rfile['filehash']);
							break;
						}
						case 'thumbmini':
						{
							$htmlx = '<img src="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . (($ilconfig['globalauctionsettings_seourls'])
								? 'i/thumb/itemphotomini/' . $rfile['filehash'] . '/' . $rfile['width_mini'] . 'x' . $rfile['height_mini'] . '_' . $rfile['filename']
								: $ilpage['attachment'] . '?cmd=thumb&amp;subcmd=itemphotomini&amp;id=' . $rfile['filehash']) . '" alt="' . $rfile['project_title'] . '" />';
							$htmlsrc = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . (($ilconfig['globalauctionsettings_seourls'])
								? 'i/thumb/itemphotomini/' . $rfile['filehash'] . '/' . $rfile['width_mini'] . 'x' . $rfile['height_mini'] . '_' . $rfile['filename']
								: $ilpage['attachment'] . '?cmd=thumb&amp;subcmd=itemphotomini&amp;id=' . $rfile['filehash']);
							break;
						}
						case 'full':
						{
							$html = (empty($url) ? '' : '<a href="' . $url . '" title="' . $rfile['project_title'] . '">') . '<img src="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . (($ilconfig['globalauctionsettings_seourls'])
								? 'i/' . $rfile['filehash'] . '/' . $rfile['filename']
								: $ilpage['attachment'] . '?id=' . $rfile['filehash']) . '" id=""' . (($forceplainimg) ? '' : ' border="' . $borderwidth . '" style="border-color:' . $bordercolor . '') . '" alt="' . $rfile['project_title'] . '" />' . (empty($url) ? '' : '</a>');
							$htmlsrc = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . (($ilconfig['globalauctionsettings_seourls'])
								? 'i/' . $rfile['filehash'] . '/' . $rfile['filename']
								: $ilpage['attachment'] . '?id=' . $rfile['filehash']);
							break;
						}
						case 'checkup':
						{
							return '1';
							break;
						}
					}
				}
			}
			if ($mode == 'thumb' OR $mode == 'thumbgallery' OR $mode == 'thumbmini')
			{
				if ($mode == 'thumbgallery')
				{
					if ($pictures > 0)
					{
						if ($forceplainimg)
						{
							$html = (empty($url) ? '' : '<a href="' . $url . '" title="' . $project_title . '">') . '<span class="imgb photogallery">' . $htmlx . '</span>' . (empty($url) ? '' : '</a>');
						}
						else
						{
							if ($forceimgsrc)
							{
								$html = $htmlsrc;
							}
							else
							{
								$html = '<div class="photo' . ($borderwidth > 0 ? ' side-corner-tag' : ' noborder') . '"><div class="photo_wrapper"><div class="photogallery">' . (empty($url) ? '' : '<a href="' . $url . '">') . $htmlx . (empty($url) ? '' : '</a>') . '</div></div></div>';
							}
						}
					}
					else
					{
						if ($forceplainimg)
						{
							$html = (empty($url) ? '' : '<a href="' . $url . '">') . '<span class="imgb photogallery"><img src="' . $imgsrc . 'nophoto.gif" alt="" border="0" height="' . $ilconfig['attachmentlimit_searchresultsgallerymaxheight'] . '" width="' . $ilconfig['attachmentlimit_searchresultsgallerymaxwidth'] . '" /></span>' . (empty($url) ? '' : '</a>');
						}
						else
						{
							if ($forceimgsrc)
							{
								$html = $imgsrc . 'nophoto.gif';
							}
							else
							{
								$html = (empty($url) ? '' : '<a href="' . $url . '">') . '<img src="' . $imgsrc . 'nophoto.gif" alt="" border="0" height="' . $ilconfig['attachmentlimit_searchresultsgallerymaxheight'] . '" width="' . $ilconfig['attachmentlimit_searchresultsgallerymaxwidth'] . '" />' . (empty($url) ? '' : '</a>');
							}
						}
					}
				}
				else if ($mode == 'thumbmini')
				{
					if ($pictures > 0)
					{
						if ($forceplainimg)
						{
							$html = (empty($url) ? '' : '<a href="' . $url . '" title="' . $project_title . '">') . '<span class="imgb photomini">' . $htmlx . '</span>' . (empty($url) ? '' : '</a>');
						}
						else
						{
							if ($forceimgsrc)
							{
								$html = $htmlsrc;
							}
							else
							{
								$html = '<div class="border' . ($borderwidth > 0 ? ' photomini side-corner-tag' : ' noborder') . '"><div class="' . ($borderwidth > 0 ? 'photomini ' : '') . 'photo">' . (empty($url) ? '' : '<a href="' . $url . '">') . $htmlx . (empty($url) ? '' : '</a>') . '</div></div>';
							}
						}
					}
					else
					{
						if ($forceplainimg)
						{
							$html = (empty($url) ? '' : '<a href="' . $url . '" title="' . $project_title . '">') . '<span class="imgb photomini"><img src="' . $imgsrc . 'nophoto.gif" alt="" border="0" height="' . $ilconfig['attachmentlimit_thumbnailmaxheight'] . '" width="' . $ilconfig['attachmentlimit_thumbnailmaxwidth'] . '" /></span>' . (empty($url) ? '' : '</a>');
						}
						else
						{
							if ($forceimgsrc)
							{
								$html = $imgsrc . 'nophoto.gif';
							}
							else
							{
								$html = '<div class="border' . ($borderwidth > 0 ? ' photomini' : ' noborder') . '"><div class="' . ($borderwidth > 0 ? 'photomini ' : '') . 'photo">' . (empty($url) ? '' : '<a href="' . $url . '">') . '<img src="' . $imgsrc . 'nophoto.gif" alt="" border="0" height="' . $ilconfig['attachmentlimit_thumbnailmaxheight'] . '" width="' . $ilconfig['attachmentlimit_thumbnailmaxwidth'] . '" />' . (empty($url) ? '' : '</a>') . '</div></div>';
							}
						}
					}
				}
				else
				{
					if ($pictures > 0)
					{
						if ($forceplainimg)
						{
							$html = (empty($url) ? '' : '<a href="' . $url . '" title="' . $project_title . '">') . '<span class="imgb photoresults">' . $htmlx . '</span>' . (empty($url) ? '' : '</a>');
						}
						else
						{
							if ($forceimgsrc)
							{
								$html = $htmlsrc;
							}
							else
							{
								$html = '<div class="border' . ($borderwidth > 0 ? ' photoresults side-corner-tag' : ' noborder') . '"><div class="' . ($borderwidth > 0 ? 'photoresults ' : '') . 'photo">' . (empty($url) ? '' : '<a href="' . $url . '">') . $htmlx . (empty($url) ? '' : '</a>') . '</div></div>';
							}
						}
					}
					else
					{
						if ($forceplainimg)
						{
							$html = (empty($url) ? '' : '<a href="' . $url . '" title="' . $project_title . '">') . '<span class="imgb photoresults"><img src="' . $imgsrc . 'nophoto.gif" alt="" border="0" height="' . $ilconfig['attachmentlimit_searchresultsmaxheight'] . '" width="' . $ilconfig['attachmentlimit_searchresultsmaxwidth'] . '" /></span>' . (empty($url) ? '' : '</a>');
						}
						else
						{
							if ($forceimgsrc)
							{
								$html = $imgsrc . 'nophoto.gif';
							}
							else
							{
								$html = '<div class="border' . ($borderwidth > 0 ? ' photoresults' : ' noborder') . '"><div class="' . ($borderwidth > 0 ? 'photoresults ' : '') . 'photo">' . (empty($url) ? '' : '<a href="' . $url . '">') . '<img src="' . $imgsrc . 'nophoto.gif" alt="" border="0" height="' . $ilconfig['attachmentlimit_searchresultsmaxheight'] . '" width="' . $ilconfig['attachmentlimit_searchresultsmaxwidth'] . '" />' . (empty($url) ? '' : '</a>') . '</div></div>';
							}
						}
					}
				}
			}
		}
		else 
		{
			if ($forceimgsrc)
			{
				$html = $imgsrc . 'nophoto.gif';
			}
			else
			{
				if ($mode == 'thumbgallery')
				{
					if ($forceplainimg)
					{
						$html = (empty($url) ? '' : '<a href="' . $url . '">') . '<span class="imgb photogallery"><img src="' . $imgsrc . 'nophoto.gif" alt="" style="max-width:' . $ilconfig['attachmentlimit_searchresultsgallerymaxwidth'] . 'px" /></span>' . (empty($url) ? '' : '</a>');
					}
					else
					{
						$html = '<div class="border' . ($borderwidth > 0 ? ' photogallery' : ' noborder') . '"><div class="' . ($borderwidth > 0 ? 'photogallery ' : '') . 'photo">' . (empty($url) ? '' : '<a href="' . $url . '">') . '<img src="' . $imgsrc . 'nophoto.gif" alt="" border="0" style="max-width:' . $ilconfig['attachmentlimit_searchresultsgallerymaxwidth'] . 'px" />' . (empty($url) ? '' : '</a>') . '</div></div>';
					}
				}
				else if ($mode == 'thumbmini')
				{
					if ($forceplainimg)
					{
						$html = (empty($url) ? '' : '<a href="' . $url . '">') . '<span class="imgb photomini"><img src="' . $imgsrc . 'nophoto.gif" alt="" width="' . $ilconfig['attachmentlimit_productphotothumbwidth'] . '" /></span>' . (empty($url) ? '' : '</a>');
					}
					else
					{
						$html = '<div class="border' . ($borderwidth > 0 ? ' photomini' : ' noborder') . '"><div class="' . ($borderwidth > 0 ? 'photomini ' : '') . 'photo">' . (empty($url) ? '' : '<a href="' . $url . '">') . '<img src="' . $imgsrc . 'nophoto.gif" alt="" border="0" width="' . $ilconfig['attachmentlimit_productphotothumbwidth'] . '" />' . (empty($url) ? '' : '</a>') . '</div></div>';
					}
				}
				else
				{
					if ($forceplainimg)
					{
						$html = (empty($url) ? '' : '<a href="' . $url . '">') . '<span class="imgb photoresults"><img src="' . $imgsrc . 'nophoto.gif" alt="" height="' . $ilconfig['attachmentlimit_searchresultsmaxheight'] . '" width="' . $ilconfig['attachmentlimit_searchresultsmaxwidth'] . '" /></span>' . (empty($url) ? '' : '</a>');
					}
					else
					{
						$html = '<div class="border' . ($borderwidth > 0 ? ' photoresults' : ' noborder') . '"><div class="' . ($borderwidth > 0 ? 'photoresults ' : '') . 'photo">' . (empty($url) ? '' : '<a href="' . $url . '">') . '<img src="' . $imgsrc . 'nophoto.gif" alt="" border="0" height="' . $ilconfig['attachmentlimit_searchresultsmaxheight'] . '" width="' . $ilconfig['attachmentlimit_searchresultsmaxwidth'] . '" />' . (empty($url) ? '' : '</a>') . '</div></div>';
					}
				}
			}
		}
		return $html;
	}
		
	/**
	* Function to determine if a particular auction event is by invitation only
	* 
	* @param       integer        project id
	*
	* @return      boolean        Returns true or false
	*/
	function is_inviteonly_auction($projectid = 0)
	{
		global $ilance;
		$sql = $ilance->db->query("SELECT project_details FROM " . DB_PREFIX . "projects WHERE project_id = '".intval($projectid)."' LIMIT 1", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql);
			if ($res['project_details'] == 'invite_only')
			{
				return 1;
			}
		}
		return 0;
	}
	
	/**
	* Function to print out in verbose terms the auction bit (type of auction).  This function now takes service and product into consideration.
	*
	* @param       integer        project id
	*
	* @return      string         Returns phrase based on the auction event type
	*/
	function print_auction_bit($projectid = 0, $filtered_auctiontype = '', $project_details = '', $project_state = '', $buynow = 0, $reserve = 0, $cid = 0)
	{
		global $ilance, $phrase, $show, $ilconfig;
		$html = '';
		if ($project_state == 'product')
		{
			if ($filtered_auctiontype == 'fixed')
			{
				$html = '{_fixed_price}';
			}
			else if ($filtered_auctiontype == 'regular')
			{
				$other = '';
				if ($reserve > 0)
				{
					//$other = '{_plus_reserve_price}';
				}
				if ($buynow > 0)
				{
					$other .= ' {_plus_buy_now}';
				}
				$html = '{_auction} ' . $other;
			}    
		}
		else if ($project_state == 'service')
		{
			$html = '{_reverse_auction}';        
		}

		($apihook = $ilance->api('print_auction_bit_end')) ? eval($apihook) : false;

		return $html;
	}
	
	/**
	* Function to print an auction event status phrase
	*
	* @param       string         status type (draft, open, closed, expired, delisted, wait_approval, approval_accepted, frozen, finished or archived)
	*
	* @return      string         Returns auction event status phrase
	*/
	function print_auction_status($status = '')
	{
		if ($status == 'wait_approval')
		{
			$text = '{_waiting_approval}';        
		}
		else if ($status == 'expired')
		{
			$text = '{_ended}';
		}
		else
		{
			$text = "{_" . $status . "}";
		}
		return $text;
	}
	
	/**
	* Function to determine if a project id being specified is actually a valid auction listing id
	* 
	* @param       integer        project id
	*
	* @return      boolean        Returns true or false
	*/
	function auction_exists($projectid = 0)
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT user_id
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . intval($projectid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			return 1;
		}
		return 0;
	}
	
	/**
	* Function to fetch public messages posted on a listing
	* 
	* @param       integer        project id
	*
	* @return      boolean        Returns array of messages and message count
	*/
	function fetch_public_messages($project_id = 0)
	{
		global $ilance, $show, $ilconfig;
		$messages = array();
		$msgcount = 0;
		$show['publicboard'] = 1;
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "messageid, date, message, project_id, user_id, username
			FROM " . DB_PREFIX . "messages
			WHERE project_id = '" . intval($project_id) . "'
			ORDER BY messageid ASC
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$owner_id = fetch_auction('user_id', $project_id);
			while ($message = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$message['date'] = print_date($message['date'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				$message['message'] = '<div style="line-height:17px">' . (($message['user_id'] == $owner_id) ? '<span class="green">({_seller})</span> ' . strip_vulgar_words(handle_input_keywords($message['message'])) . '' : '<span class="blue">({_buyer})</span> ' . strip_vulgar_words(handle_input_keywords($message['message']))) . '</div>';
				$message['message'] = strip_vulgar_words($message['message']);
				$message['class'] = ($msgcount % 2) ? 'alt2' : 'alt1';
				$messages[] = $message;
				$msgcount++;
			}
		}
		return array($messages, $msgcount);
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>