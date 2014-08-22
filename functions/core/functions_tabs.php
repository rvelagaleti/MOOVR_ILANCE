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
* Core Tab functions for ILance.
*
* @package      iLance\Global\Tabs
* @version      4.0.0.8059
* @author       ILance
*/

/*
* Function to print and display the main buying activity big tabs for buyer navigation and control
*
* @param        string         button identifier
* @param        string         category type (service/product)
* @param        integer        user id viewing
* @param        string         (optional)
*
* @return       string         HTML representation of the big tab navigation     
*/
function print_buying_activity_tab_options($button = '', $cattype = '', $userid = 0, $extra = '')
{
	global $ilance, $phrase, $ilconfig, $ilpage, $show;
	switch ($cattype)
	{
		// #### SERVICE AUCTIONS BUYING ACTIVITY TAB COUNT #############
		case 'service':
		{
			$activerfps = $ilance->bid_tabs->fetch_service_bidtab_sql('active', 'count', $userid, $extra);
			$endedrfps = $ilance->bid_tabs->fetch_service_bidtab_sql('expired', 'count', $userid, $extra);
			$awardedrfps = $ilance->bid_tabs->fetch_service_bidtab_sql('awarded', 'count', $userid, $extra);
			$archivedrfps = $ilance->bid_tabs->fetch_service_bidtab_sql('archived', 'count', $userid, $extra);
			$delistedrfps = $ilance->bid_tabs->fetch_service_bidtab_sql('delisted', 'count', $userid, $extra);
			$draftrfps = $ilance->bid_tabs->fetch_service_bidtab_sql('drafts', 'count', $userid, $extra);
			$pendingrfps = $ilance->bid_tabs->fetch_service_bidtab_sql('pending', 'count', $userid, $extra);
			if ($ilconfig['escrowsystem_enabled'])
			{
				$extra1 = str_replace('p.date_added', 'e.date_awarded', $extra);
				$serviceescrow = $ilance->bid_tabs->fetch_service_bidtab_sql('serviceescrow', 'count', $userid, $extra1);
			}
			unset($extra1);
			
			// display period (if applicable)
			$periodbit = (isset($ilance->GPC['period']) AND $ilance->GPC['period'] > 0) ? '&amp;period=' . intval($ilance->GPC['period']) : '';
			
			// display order (if applicable)
			$displayorderbit = (isset($ilance->GPC['displayorder']) AND !empty($ilance->GPC['displayorder'])) ? '&amp;displayorder=' . ilance_htmlentities($ilance->GPC['displayorder']) : '';
			
			switch ($button)
			{
				case 'active':
				{
					$html = '<li title="" id="" class="on"><a href="javascript:void(0)">{_active} (' . $activerfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=ended' . $periodbit . $displayorderbit . '">{_ended} (' . $endedrfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=awarded' . $periodbit . $displayorderbit . '">{_awarded} (' . $awardedrfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=archived' . $periodbit . $displayorderbit . '">{_archived} (' . $archivedrfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=delisted' . $periodbit . $displayorderbit . '">{_delisted} (' . $delistedrfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=drafts' . $periodbit . $displayorderbit . '">{_draft} (' . $draftrfps . ')</a></li>';
$html .= ($ilconfig['moderationsystem_disableauctionmoderation'] == 1 AND $ilconfig['globalauctionsettings_payperpost'] == 0)?'':'<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=rfp-pending' . $periodbit . $displayorderbit . '">{_pending} (' . $pendingrfps . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id=""class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=rfp-escrow' . $periodbit . $displayorderbit . '">{_escrow} (' . $serviceescrow . ')</a></li>';
					}
					break;
				}
				case 'ended':
				{
					$html = '<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management' . $periodbit . $displayorderbit . '">{_active} (' . $activerfps . ')</a></li>
<li title="" id="" class="on"><a href="javascript:void(0)">{_ended} (' . $endedrfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=awarded' . $periodbit . $displayorderbit . '">{_awarded} (' . $awardedrfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=archived' . $periodbit . $displayorderbit . '">{_archived} (' . $archivedrfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=delisted' . $periodbit . $displayorderbit . '">{_delisted} (' . $delistedrfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=drafts' . $periodbit . $displayorderbit . '">{_draft} (' . $draftrfps . ')</a></li>';
$html .= ($ilconfig['moderationsystem_disableauctionmoderation'] == 1 AND $ilconfig['globalauctionsettings_payperpost'] == 0)?'':'<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=rfp-pending' . $periodbit . $displayorderbit . '">{_pending} (' . $pendingrfps . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id=""class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=rfp-escrow' . $periodbit . $displayorderbit . '">{_escrow} (' . $serviceescrow . ')</a></li>';
					}
					break;
				}
				case 'awarded':
				{
					$html = '<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management' . $periodbit . $displayorderbit . '">{_active} (' . $activerfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=ended' . $periodbit . $displayorderbit . '">{_ended} (' . $endedrfps . ')</a></li>
<li title="" id="" class="on"><a href="javascript:void(0)">{_awarded} (' . $awardedrfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=archived' . $periodbit . $displayorderbit . '">{_archived} (' . $archivedrfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=delisted' . $periodbit . $displayorderbit . '">{_delisted} (' . $delistedrfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=drafts' . $periodbit . $displayorderbit . '">{_draft} (' . $draftrfps . ')</a></li>';
$html .= ($ilconfig['moderationsystem_disableauctionmoderation'] == 1 AND $ilconfig['globalauctionsettings_payperpost'] == 0)?'':'<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=rfp-pending' . $periodbit . $displayorderbit . '">{_pending} (' . $pendingrfps . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id=""class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=rfp-escrow' . $periodbit . $displayorderbit . '">{_escrow} (' . $serviceescrow . ')</a></li>';
					}
					break;
				}
				case 'archived':
				{
					$html = '<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management' . $periodbit . $displayorderbit . '">{_active} (' . $activerfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=ended' . $periodbit . $displayorderbit . '">{_ended} (' . $endedrfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=awarded' . $periodbit . $displayorderbit . '">{_awarded} (' . $awardedrfps . ')</a></li>
<li title="" id="" class="on"><a href="javascript:void(0)">{_archived} (' . $archivedrfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=delisted' . $periodbit . $displayorderbit . '">{_delisted} (' . $delistedrfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=drafts' . $periodbit . $displayorderbit . '">{_draft} (' . $draftrfps . ')</a></li>';
$html .= ($ilconfig['moderationsystem_disableauctionmoderation'] == 1 AND $ilconfig['globalauctionsettings_payperpost'] == 0)?'':'<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=rfp-pending' . $periodbit . $displayorderbit . '">{_pending} (' . $pendingrfps . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id=""class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=rfp-escrow' . $periodbit . $displayorderbit . '">{_escrow} (' . $serviceescrow . ')</a></li>';
					}
					break;
				}			
				case 'delisted':
				{
					$html = '<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management' . $periodbit . $displayorderbit . '">{_active} (' . $activerfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=ended' . $periodbit . $displayorderbit . '">{_ended} (' . $endedrfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=awarded' . $periodbit . $displayorderbit . '">{_awarded} (' . $awardedrfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=archived' . $periodbit . $displayorderbit . '">{_archived} (' . $archivedrfps . ')</a></li>
<li title="" id="" class="on"><a href="javascript:void(0)">{_delisted} (' . $delistedrfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=drafts' . $periodbit . $displayorderbit . '">{_draft} (' . $draftrfps . ')</a></li>';
$html .= ($ilconfig['moderationsystem_disableauctionmoderation'] == 1 AND $ilconfig['globalauctionsettings_payperpost'] == 0)?'':'<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=rfp-pending' . $periodbit . $displayorderbit . '">{_pending} (' . $pendingrfps . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id=""class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=rfp-escrow' . $periodbit . $displayorderbit . '">{_escrow} (' . $serviceescrow . ')</a></li>';
					}
					break;
				}			
				case 'drafts':
				{
					$html = '<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management' . $periodbit . $displayorderbit . '">{_active} (' . $activerfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=ended' . $periodbit . $displayorderbit . '">{_ended} (' . $endedrfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=awarded' . $periodbit . $displayorderbit . '">{_awarded} (' . $awardedrfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=archived' . $periodbit . $displayorderbit . '">{_archived} (' . $archivedrfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=delisted' . $periodbit . $displayorderbit . '">{_delisted} (' . $delistedrfps . ')</a></li>
<li title="" id="" class="on"><a href="javascript:void(0)">{_draft} (' . $draftrfps . ')</a></li>';
$html .= ($ilconfig['moderationsystem_disableauctionmoderation'] == 1 AND $ilconfig['globalauctionsettings_payperpost'] == 0)?'':'<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=rfp-pending' . $periodbit . $displayorderbit . '">{_pending} (' . $pendingrfps . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id=""class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=rfp-escrow' . $periodbit . $displayorderbit . '">{_escrow} (' . $serviceescrow . ')</a></li>';
					}
					break;
				}			
				case 'rfp-pending':
				{
					$html = '<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management' . $periodbit . $displayorderbit . '">{_active} (' . $activerfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=ended' . $periodbit . $displayorderbit . '">{_ended} (' . $endedrfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=awarded' . $periodbit . $displayorderbit . '">{_awarded} (' . $awardedrfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=archived' . $periodbit . $displayorderbit . '">{_archived} (' . $archivedrfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=delisted' . $periodbit . $displayorderbit . '">{_delisted} (' . $delistedrfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=drafts' . $periodbit . $displayorderbit . '">{_draft} (' . $draftrfps . ')</a></li>';
$html .= ($ilconfig['moderationsystem_disableauctionmoderation'] == 1 AND $ilconfig['globalauctionsettings_payperpost'] == 0)?'':'<li title="" id="" class="on"><a href="javascript:void(0)">{_pending} (' . $pendingrfps . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id=""class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=rfp-escrow' . $periodbit . $displayorderbit . '">{_escrow} (' . $serviceescrow . ')</a></li>';
					}
					break;
				}			
				case 'rfp-escrow':
				{
					$html = '<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management' . $periodbit . $displayorderbit . '">{_active} (' . $activerfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=ended' . $periodbit . $displayorderbit . '">{_ended} (' . $endedrfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=awarded' . $periodbit . $displayorderbit . '">{_awarded} (' . $awardedrfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=archived' . $periodbit . $displayorderbit . '">{_archived} (' . $archivedrfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=delisted' . $periodbit . $displayorderbit . '">{_delisted} (' . $delistedrfps . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=drafts' . $periodbit . $displayorderbit . '">{_draft} (' . $draftrfps . ')</a></li>';
$html .= ($ilconfig['moderationsystem_disableauctionmoderation'] == 1 AND $ilconfig['globalauctionsettings_payperpost'] == 0)?'':'<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;sub=rfp-pending' . $periodbit . $displayorderbit . '">{_pending} (' . $pendingrfps . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="on"><a href="javascript:void(0)">{_escrow} (' . $serviceescrow . ')</a></li>';
					}
					break;
				}
			}
			break;
		}
		
		// #### PRODUCT AUCTION BUYING ACTIVITY TAB COUNT ##############
		case 'product':
		{
			$activebids = $ilance->bid_tabs->fetch_product_bidtab_sql('active', 'count', 'GROUP BY b.project_id', 'ORDER BY date_end DESC', '', $userid, $extra);
			$awardedbids = $ilance->bid_tabs->fetch_product_bidtab_sql('awarded', 'count', 'GROUP BY b.project_id', 'ORDER BY date_end DESC', '', $userid, $extra);
			$invitedbids = $ilance->bid_tabs->fetch_product_bidtab_sql('invited', 'count', 'GROUP BY b.project_id', 'ORDER BY date_end DESC', '', $userid, $extra);
			$expiredbids = $ilance->bid_tabs->fetch_product_bidtab_sql('expired', 'count', 'GROUP BY b.project_id', 'ORDER BY date_end DESC', '', $userid, $extra);
			$retractedbids = $ilance->bid_tabs->fetch_product_bidtab_sql('retracted', 'count', 'GROUP BY b.project_id', 'ORDER BY date_end DESC', '', $userid, $extra);
			$extra1 = str_replace('p.date_added', 'b.orderdate', $extra);
			$extra2 = '';
			$buynowproductescrow = $ilance->bid_tabs->fetch_product_bidtab_sql('buynowproductescrow', 'count', '', '', '', $userid, $extra1);
			if ($ilconfig['escrowsystem_enabled'])
			{
				$extra2 = str_replace('p.date_added', 'e.date_awarded', $extra);
				$productescrow = $ilance->bid_tabs->fetch_product_bidtab_sql('productescrow', 'count', '', '', '', $userid, $extra2);
			}
			unset($extra1, $extra2);
			$periodbit = (isset($ilance->GPC['period2']) AND $ilance->GPC['period2'] > 0) ? '&amp;period2=' . intval($ilance->GPC['period2']) : '';
			$displayorderbit = (isset($ilance->GPC['displayorder2']) AND !empty($ilance->GPC['displayorder2'])) ? '&amp;displayorder2=' . ilance_htmlentities($ilance->GPC['displayorder2']) : '';
			$periodbit = $displayorderbit = '';
			if (isset($button))
			{
				($apihook = $ilance->api('print_buying_activity_tab_options_start_tab')) ? eval($apihook) : false;
				
				if ($button == 'active')
				{
					$html = '<li title="" id="" class="on"><a href="javascript:void(0)">{_active} (' . $activebids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=awarded' . $periodbit . $displayorderbit . '">{_i_won} (' . $awardedbids . ')</a></li>';
$html .= $ilconfig['product_invite_block'] ? '<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=invited' . $periodbit . $displayorderbit . '">{_invited} (' . $invitedbids . ')</a></li>' : '';
$html .= '<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=expired' . $periodbit . $displayorderbit . '">{_i_lost} (' . $expiredbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=retracted' . $periodbit . $displayorderbit . '">{_retracted} (' . $retractedbids . ')</a></li>
<li title="" id="" class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=buynow-escrow' . $periodbit . $displayorderbit . '">{_buy_now} (' . $buynowproductescrow . ')</a></li>';
					
					($apihook = $ilance->api('print_buying_activity_tab_options_active_tab')) ? eval($apihook) : false;
					
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id=""class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow' . $periodbit . $displayorderbit . '">{_escrow} (' . $productescrow . ')</a></li>';
					}
				}			    
				if ($button == 'awarded')
				{
					$html = '<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management' . $periodbit . $displayorderbit . '">{_active} (' . $activebids . ')</a></li>
<li title="" id="" class="on"><a href="javascript:void(0)">{_i_won} (' . $awardedbids . ')</a></li>';
$html .= $ilconfig['product_invite_block'] ? '<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=invited' . $periodbit . $displayorderbit . '">{_invited} (' . $invitedbids . ')</a></li>' : '';
$html .= '<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=expired' . $periodbit . $displayorderbit . '">{_i_lost}  (' . $expiredbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=retracted' . $periodbit . $displayorderbit . '">{_retracted} (' . $retractedbids . ')</a></li>
<li title="" id="" class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=buynow-escrow' . $periodbit . $displayorderbit . '">{_buy_now} (' . $buynowproductescrow . ')</a></li>';
					
					($apihook = $ilance->api('print_buying_activity_tab_options_awarded_tab')) ? eval($apihook) : false;
					
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id=""class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow' . $periodbit . $displayorderbit . '">{_escrow} (' . $productescrow . ')</a></li>';
					}
				}			    
				if ($button == 'invited')
				{
					$html = '<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management' . $periodbit . $displayorderbit . '">{_active} (' . $activebids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=awarded' . $periodbit . $displayorderbit . '">{_i_won} (' . $awardedbids . ')</a></li>
<li title="" id="" class="on"><a href="javascript:void(0)">{_invited} (' . $invitedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=expired' . $periodbit . $displayorderbit . '">{_i_lost} (' . $expiredbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=retracted' . $periodbit . $displayorderbit . '">{_retracted} (' . $retractedbids . ')</a></li>
<li title="" id="" class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=buynow-escrow' . $periodbit . $displayorderbit . '">{_buy_now} (' . $buynowproductescrow . ')</a></li>';
					
					($apihook = $ilance->api('print_buying_activity_tab_options_invited_tab')) ? eval($apihook) : false;
					
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id=""class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow' . $periodbit . $displayorderbit . '">{_escrow} (' . $productescrow . ')</a></li>';
					}
				}			    
				if ($button == 'expired')
				{
					$html = '<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management' . $periodbit . $displayorderbit . '">{_active} (' . $activebids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=awarded' . $periodbit . $displayorderbit . '">{_i_won} (' . $awardedbids . ')</a></li>';
$html .= $ilconfig['product_invite_block'] ? '<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=invited' . $periodbit . $displayorderbit . '">{_invited} (' . $invitedbids . ')</a></li>' : '';
$html .= '<li title="" id="" class="on"><a href="javascript:void(0)">{_i_lost} (' . $expiredbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=retracted' . $periodbit . $displayorderbit . '">{_retracted} (' . $retractedbids . ')</a></li>
<li title="" id="" class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=buynow-escrow' . $periodbit . $displayorderbit . '">{_buy_now} (' . $buynowproductescrow . ')</a></li>';
					
					($apihook = $ilance->api('print_buying_activity_tab_options_expired_tab')) ? eval($apihook) : false;
					
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id=""class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow' . $periodbit . $displayorderbit . '">{_escrow} (' . $productescrow . ')</a></li>';
					}
				}			    
				if ($button == 'retracted')
				{
					$html = '<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management' . $periodbit . $displayorderbit . '">{_active} (' . $activebids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=awarded' . $periodbit . $displayorderbit . '">{_i_won} (' . $awardedbids . ')</a></li>';
$html .= $ilconfig['product_invite_block'] ? '<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=invited' . $periodbit . $displayorderbit . '">{_invited} (' . $invitedbids . ')</a></li>' : '';
$html .= '<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=expired' . $periodbit . $displayorderbit . '">{_i_lost} (' . $expiredbids . ')</a></li>
<li title="" id="" class="on"><a href="javascript:void(0)">{_retracted} (' . $retractedbids . ')</a></li>
<li title="" id="" class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=buynow-escrow' . $periodbit . $displayorderbit . '">{_buy_now} (' . $buynowproductescrow . ')</a></li>';
					
					($apihook = $ilance->api('print_buying_activity_tab_options_retracted_tab')) ? eval($apihook) : false;
					
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id=""class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow' . $periodbit . $displayorderbit . '">{_escrow} (' . $productescrow . ')</a></li>';
					}
				}			    
				if ($button == 'buynow-escrow')
				{
					$html = '<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management' . $periodbit . $displayorderbit . '">{_active} (' . $activebids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=awarded' . $periodbit . $displayorderbit . '">{_i_won} (' . $awardedbids . ')</a></li>';
$html .= $ilconfig['product_invite_block'] ? '<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=invited' . $periodbit . $displayorderbit . '">{_invited} (' . $invitedbids . ')</a></li>' : '';
$html .= '<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=expired' . $periodbit . $displayorderbit . '">{_i_lost} (' . $expiredbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=retracted' . $periodbit . $displayorderbit . '">{_retracted} (' . $retractedbids . ')</a></li>
<li title="" id="" class="on"><a href="javascript:void(0)">{_buy_now} (' . $buynowproductescrow . ')</a></li>';

					($apihook = $ilance->api('print_buying_activity_tab_options_buynow_escrow_tab')) ? eval($apihook) : false;
					
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id=""class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow' . $periodbit . $displayorderbit . '">{_escrow} (' . $productescrow . ')</a></li>';
					}
				}
				if ($button == 'product-escrow')
				{
					$html = '<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management' . $periodbit . $displayorderbit . '">{_active} (' . $activebids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=awarded' . $periodbit . $displayorderbit . '">{_i_won} (' . $awardedbids . ')</a></li>';
$html .= $ilconfig['product_invite_block'] ? '<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=invited' . $periodbit . $displayorderbit . '">{_invited} (' . $invitedbids . ')</a></li>' : '';
$html .= '<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=expired' . $periodbit . $displayorderbit . '">{_i_lost} (' . $expiredbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['buying'] . '?cmd=management&amp;bidsub=retracted' . $periodbit . $displayorderbit . '">{_retracted} (' . $retractedbids . ')</a></li>
<li title="" id="" class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=buynow-escrow' . $periodbit . $displayorderbit . '">{_buy_now} (' . $buynowproductescrow . ')</a></li>';
					
					($apihook = $ilance->api('print_buying_activity_tab_options_product_escrow_tab')) ? eval($apihook) : false;
					
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="on"><a href="javascript:void(0)">{_escrow} (' . $productescrow . ')</a></li>';
					}
				}
				
				($apihook = $ilance->api('print_buying_activity_tab_options_custom_tab')) ? eval($apihook) : false;
			}
			break;
		}
	}
	
	($apihook = $ilance->api('print_buying_activity_tab_options_end')) ? eval($apihook) : false;
	
	return $html;
}
    
/*
* Function to print and display the main selling activity big tabs for seller navigation and control
*
* @param        string         button identifier
* @param        string         category type (service/product)
* @param        integer        user id viewing
* @param        string         (optional)      
*
* @return       string         HTML representation of the big tab navigation   
*/
function print_selling_activity_tab_options($button = '', $cattype = '', $userid = 0, $extra = '')
{
	global $ilance, $phrase, $ilconfig, $ilpage, $show;
	
	switch ($cattype)
	{
		// #### SERVICE AUCTIONS SELLING ACTIVITY TAB COUNT ############
		case 'service':
		{
			$activebids = $ilance->bid_tabs->fetch_bidtab_sql('active', 'count', 'GROUP BY b.project_id', '', '', $userid, $extra);
			$awardedbids = $ilance->bid_tabs->fetch_bidtab_sql('awarded', 'count', 'GROUP BY b.project_id', '', '', $userid, $extra);
			$archivedbids = $ilance->bid_tabs->fetch_bidtab_sql('archived', 'count', 'GROUP BY b.project_id', '', '', $userid, $extra);
			$declinedbids = $ilance->bid_tabs->fetch_bidtab_sql('delisted', 'count', 'GROUP BY b.project_id', '', '', $userid, $extra);
			$retractedbids = $ilance->bid_tabs->fetch_bidtab_sql('retracted', 'count', 'GROUP BY b.project_id', '', '', $userid, $extra);
			$invitedbids = $ilance->bid_tabs->fetch_bidtab_sql('invited', 'count', 'GROUP BY i.project_id', '', '', $userid, $extra);
			$expiredbids = $ilance->bid_tabs->fetch_bidtab_sql('expired', 'count', 'GROUP BY b.project_id', '', '', $userid, $extra);
			if ($ilconfig['escrowsystem_enabled'])
			{
			       $serviceescrow = $ilance->bid_tabs->fetch_bidtab_sql('serviceescrow', 'count', '', '', '', $userid, $extra);
			}
			
			// display period (if applicable)
			$periodbit = (isset($ilance->GPC['period2']) AND $ilance->GPC['period2'] > 0) ? '&amp;period2=' . intval($ilance->GPC['period2']) : '';
			
			// display order (if applicable)
			$displayorderbit = (isset($ilance->GPC['displayorder2']) AND !empty($ilance->GPC['displayorder2'])) ? '&amp;displayorder2=' . ilance_htmlentities($ilance->GPC['displayorder2']) : '';
			
			switch ($button)
			{
				case 'active':
				{
					$html = '<li title="" id="" class="on"><a href="javascript:void(0)">{_active} (' . $activebids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=awarded' . $periodbit . $displayorderbit . '#servicebidding">{_awarded} (' . $awardedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=archived' . $periodbit . $displayorderbit . '#servicebidding">{_archived} (' . $archivedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=delisted' . $periodbit . $displayorderbit . '#servicebidding">{_declined} (' . $declinedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=retracted' . $periodbit . $displayorderbit . '#servicebidding">{_retracted} (' . $retractedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=invited' . $periodbit . $displayorderbit . '#servicebidding">{_invited} (' . $invitedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=expired' . $periodbit . $displayorderbit . '#servicebidding">{_expired} (' . $expiredbids . ')</a></li>';                
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id=""class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=rfp-escrow' . $periodbit . $displayorderbit . '">{_escrow} (' . $serviceescrow . ')</a></li>';
					}
					break;
				}			    
				case 'awarded':
				{
					$html = '<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management' . $periodbit . $displayorderbit . '#servicebidding">{_active} (' . $activebids . ')</a></li>
<li title="" id="" class="on"><a href="javascript:void(0)">{_awarded} (' . $awardedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=archived' . $periodbit . $displayorderbit . '#servicebidding">{_archived} (' . $archivedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=delisted' . $periodbit . $displayorderbit . '#servicebidding">{_declined} (' . $declinedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=retracted' . $periodbit . $displayorderbit . '#servicebidding">{_retracted} (' . $retractedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=invited' . $periodbit . $displayorderbit . '#servicebidding">{_invited} (' . $invitedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=expired' . $periodbit . $displayorderbit . '#servicebidding">{_expired} (' . $expiredbids . ')</a></li>';                
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id=""class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=rfp-escrow' . $periodbit . $displayorderbit . '">{_escrow} (' . $serviceescrow . ')</a></li>';
					}
					break;
				}			    
				case 'archived':
				{
					$html = '<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management' . $periodbit . $displayorderbit . '#servicebidding">{_active} (' . $activebids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=awarded' . $periodbit . $displayorderbit . '#servicebidding">{_awarded} (' . $awardedbids . ')</a></li>
<li title="" id="" class="on"><a href="javascript:void(0)">{_archived} (' . $archivedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=delisted' . $periodbit . $displayorderbit . '#servicebidding">{_declined} (' . $declinedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=retracted' . $periodbit . $displayorderbit . '#servicebidding">{_retracted} (' . $retractedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=invited' . $periodbit . $displayorderbit . '#servicebidding">{_invited} (' . $invitedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=expired' . $periodbit . $displayorderbit . '#servicebidding">{_expired} (' . $expiredbids . ')</a></li>';                
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id=""class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=rfp-escrow' . $periodbit . $displayorderbit . '">{_escrow} (' . $serviceescrow . ')</a></li>';
					}
					break;
				}			    
				case 'delisted':
				{
					$html = '<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management' . $periodbit . $displayorderbit . '#servicebidding">{_active} (' . $activebids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=awarded' . $periodbit . $displayorderbit . '#servicebidding">{_awarded} (' . $awardedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=archived' . $periodbit . $displayorderbit . '#servicebidding">{_archived} ('.$archivedbids . ')</a></li>
<li title="" id="" class="on"><a href="javascript:void(0)">{_declined} (' . $declinedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=retracted' . $periodbit . $displayorderbit . '#servicebidding">{_retracted} (' . $retractedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=invited' . $periodbit . $displayorderbit . '#servicebidding">{_invited} (' . $invitedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=expired' . $periodbit . $displayorderbit . '#servicebidding">{_expired} (' . $expiredbids . ')</a></li>';                
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id=""class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=rfp-escrow' . $periodbit . $displayorderbit . '">{_escrow} (' . $serviceescrow . ')</a></li>';
					}
					break;
				}			    
				case 'invited':
				{
					$html = '<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management' . $periodbit . $displayorderbit . '#servicebidding">{_active} (' . $activebids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=awarded' . $periodbit . $displayorderbit . '#servicebidding">{_awarded} (' . $awardedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=archived' . $periodbit . $displayorderbit . '#servicebidding">{_archived} (' . $archivedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=delisted' . $periodbit . $displayorderbit . '#servicebidding">{_declined} (' . $declinedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=retracted' . $periodbit . $displayorderbit . '#servicebidding">{_retracted} (' . $retractedbids . ')</a></li>
<li title="" id="" class="on"><a href="javascript:void(0)">{_invited} (' . $invitedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=expired' . $periodbit . $displayorderbit . '#servicebidding">{_expired} (' . $expiredbids . ')</a></li>';                
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id=""class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=rfp-escrow' . $periodbit . $displayorderbit . '">{_escrow} (' . $serviceescrow . ')</a></li>';
					}
					break;
				}			    
				case 'expired':
				{
					$html = '<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management' . $periodbit . $displayorderbit . '#servicebidding">{_active} (' . $activebids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=awarded' . $periodbit . $displayorderbit . '#servicebidding">{_awarded} (' . $awardedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=archived' . $periodbit . $displayorderbit . '#servicebidding">{_archived} (' . $archivedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=delisted' . $periodbit . $displayorderbit . '#servicebidding">{_declined} (' . $declinedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=retracted' . $periodbit . $displayorderbit . '#servicebidding">{_retracted} (' . $retractedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=invited' . $periodbit . $displayorderbit . '#servicebidding">{_invited} (' . $invitedbids . ')</a></li>
<li title="" id="" class="on"><a href="javascript:void(0)">{_expired} (' . $expiredbids . ')</a></li>';                
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id=""class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=rfp-escrow' . $periodbit . $displayorderbit . '">{_escrow} (' . $serviceescrow . ')</a></li>';
					}
					break;
				}			
				case 'retracted':
				{
					$html = '<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management' . $periodbit . $displayorderbit . '#servicebidding">{_active} (' . $activebids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=awarded' . $periodbit . $displayorderbit . '#servicebidding">{_awarded} (' . $awardedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=archived' . $periodbit . $displayorderbit . '#servicebidding">{_archived} (' . $archivedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=delisted' . $periodbit . $displayorderbit . '#servicebidding">{_declined} (' . $declinedbids . ')</a></li>
<li title="" id="" class="on"><a href="javascript:void(0)">{_retracted} (' . $retractedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=invited' . $periodbit . $displayorderbit . '#servicebidding">{_invited} (' . $invitedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=expired' . $periodbit . $displayorderbit . '#servicebidding">{_expired} (' . $expiredbids . ')</a></li>';                
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id=""class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=rfp-escrow' . $periodbit . $displayorderbit . '">{_escrow} (' . $serviceescrow.')</a></li>';
					}
					break;
				}			    
				case 'escrow':
				{
					$html = '<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management' . $periodbit . $displayorderbit . '#servicebidding">{_active} (' . $activebids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=awarded' . $periodbit . $displayorderbit . '#servicebidding">{_awarded} (' . $awardedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=archived' . $periodbit . $displayorderbit . '#servicebidding">{_archived} (' . $archivedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=delisted' . $periodbit . $displayorderbit . '#servicebidding">{_declined} (' . $declinedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=retracted' . $periodbit . $displayorderbit . '#servicebidding">{_retracted} (' . $retractedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=invited' . $periodbit . $displayorderbit . '#servicebidding">{_invited} (' . $invitedbids . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=expired' . $periodbit . $displayorderbit . '#servicebidding">{_expired} (' . $expiredbids . ')</a></li>';                
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="on"><a href="javascript:void(0)">{_escrow} (' . $serviceescrow . ')</a></li>';
					}
					break;
				}
			}
			break;
		}
		// #### PRODUCT AUCTION SELLING ACTIVITY TAB COUNT #############
		case 'product':
		{
			$activeitems = $ilance->auction_tabs->product_auction_tab_sql('active', 'count', $userid, $extra);
			$archiveditems = $ilance->auction_tabs->product_auction_tab_sql('archived', 'count', $userid, $extra);
			$delisteditems = $ilance->auction_tabs->product_auction_tab_sql('delisted', 'count', $userid, $extra);
			$expireditems = $ilance->auction_tabs->product_auction_tab_sql('expired', 'count', $userid, $extra);
			$pendingitems = $ilance->auction_tabs->product_auction_tab_sql('pending', 'count', $userid, $extra);
			$draftitems = $ilance->auction_tabs->product_auction_tab_sql('drafts', 'count', $userid, $extra);
			$solditems = $ilance->auction_tabs->product_auction_tab_sql('sold', 'count', $userid, $extra);
			if ($ilconfig['escrowsystem_enabled'])
			{
			       $productescrow = $ilance->auction_tabs->product_auction_tab_sql('productescrow', 'count', $userid, $extra);
			}
			
			// display period (if applicable)
			$periodbit = '';
			if (isset($ilance->GPC['period']) AND $ilance->GPC['period'] > 0)
			{
				$periodbit = '&amp;period=' . intval($ilance->GPC['period']);	
			}
			
			// display order (if applicable)
			$displayorderbit = '';
			if (isset($ilance->GPC['displayorder']) AND !empty($ilance->GPC['displayorder']))
			{
				//$displayorderbit = '&amp;displayorder=' . htmlentities($ilance->GPC['displayorder'], ENT_QUOTES);
				$displayorderbit = '&amp;displayorder=' . ilance_htmlentities($ilance->GPC['displayorder']);	
			}
			
			($apihook = $ilance->api('print_selling_activity_tab_options_start_tab')) ? eval($apihook) : false;
			
			switch ($button)
			{
				case 'active':
				{
					$html = '<li title="" id="" class="on"><a href="javascript:void(0)">{_im_selling} (' . $activeitems . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=sold' . $periodbit . $displayorderbit . '">{_ive_sold} (' . $solditems . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=archived' . $periodbit . $displayorderbit . '">{_archived} (' . $archiveditems . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=delisted' . $periodbit . $displayorderbit . '">{_delisted} (' . $delisteditems . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=expired' . $periodbit . $displayorderbit . '">{_ended} (' . $expireditems . ')</a></li>';
$html .= $ilconfig['product_draft_block'] ? '<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=drafts' . $periodbit . $displayorderbit . '">{_draft} (' . $draftitems . ')</a></li>' : '';
$html .= ($ilconfig['moderationsystem_disableauctionmoderation'] == 1 AND $ilconfig['globalauctionsettings_payperpost'] == 0)?'':'<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=pending' . $periodbit . $displayorderbit . '">{_pending} (' . $pendingitems . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id=""class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=product-escrow' . $periodbit . $displayorderbit . '">{_escrow} (' . $productescrow . ')</a></li>';
					}
					($apihook = $ilance->api('print_selling_activity_tab_options_active_tab')) ? eval($apihook) : false;
					break;
				}
				case 'sold':
				{
					$html = '<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management' . $periodbit . $displayorderbit . '">{_im_selling} (' . $activeitems . ')</a></li>
<li title="" id="" class="on"><a href="javascript:void(0)">{_ive_sold} (' . $solditems . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=archived' . $periodbit . $displayorderbit . '">{_archived} (' . $archiveditems . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=delisted' . $periodbit . $displayorderbit . '">{_delisted} (' . $delisteditems . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=expired' . $periodbit . $displayorderbit . '">{_ended} (' . $expireditems . ')</a></li>';
$html .= $ilconfig['product_draft_block'] ? '<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=drafts' . $periodbit . $displayorderbit . '">{_draft} (' . $draftitems . ')</a></li>' : '';
$html .= ($ilconfig['moderationsystem_disableauctionmoderation'] == 1 AND $ilconfig['globalauctionsettings_payperpost'] == 0)?'':'<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=pending' . $periodbit . $displayorderbit . '">{_pending} (' . $pendingitems . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id=""class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=product-escrow' . $periodbit . $displayorderbit . '">{_escrow} (' . $productescrow . ')</a></li>';
					}
					($apihook = $ilance->api('print_selling_activity_tab_options_sold_tab')) ? eval($apihook) : false;
					break;
				}
				case 'archived':
				{
					$html = '<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management' . $periodbit . $displayorderbit . '">{_im_selling} (' . $activeitems . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=sold' . $periodbit . $displayorderbit . '">{_ive_sold} (' . $solditems . ')</a></li>
<li title="" id="" class="on"><a href="javascript:void(0)">{_archived} (' . $archiveditems . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=delisted' . $periodbit . $displayorderbit . '">{_delisted} (' . $delisteditems . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=expired' . $periodbit . $displayorderbit . '">{_ended} (' . $expireditems . ')</a></li>';
$html .= $ilconfig['product_draft_block'] ? '<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=drafts' . $periodbit . $displayorderbit . '">{_draft} (' . $draftitems . ')</a></li>' : '';
$html .= ($ilconfig['moderationsystem_disableauctionmoderation'] == 1 AND $ilconfig['globalauctionsettings_payperpost'] == 0)?'':'<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=pending' . $periodbit . $displayorderbit . '">{_pending} (' . $pendingitems . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id=""class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=product-escrow' . $periodbit . $displayorderbit . '">{_escrow} (' . $productescrow . ')</a></li>';
					}
					($apihook = $ilance->api('print_selling_activity_tab_options_archived_tab')) ? eval($apihook) : false;
					break;
				}			    
				case 'delisted':
				{
					$html = '<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management' . $periodbit . $displayorderbit . '">{_im_selling} (' . $activeitems . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=sold' . $periodbit . $displayorderbit . '">{_ive_sold} (' . $solditems . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=archived"' . $periodbit . $displayorderbit . '>{_archived} (' . $archiveditems . ')</a></li>
<li title="" id="" class="on"><a href="javascript:void(0)">{_delisted} (' . $delisteditems . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=expired"' . $periodbit . $displayorderbit . '>{_ended} (' . $expireditems . ')</a></li>';
$html .= $ilconfig['product_draft_block'] ? '<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=drafts' . $periodbit . $displayorderbit . '">{_draft} (' . $draftitems . ')</a></li>' : '';
$html .= ($ilconfig['moderationsystem_disableauctionmoderation'] == 1 AND $ilconfig['globalauctionsettings_payperpost'] == 0)?'':'<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=pending"' . $periodbit . $displayorderbit . '>{_pending} (' . $pendingitems . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id=""class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=product-escrow' . $periodbit . $displayorderbit . '">{_escrow} (' . $productescrow . ')</a></li>';
					}
					($apihook = $ilance->api('print_selling_activity_tab_options_delisted_tab')) ? eval($apihook) : false;
					break;
				}			    
				case 'expired':
				{
					$html = '<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management' . $periodbit . $displayorderbit . '">{_im_selling} (' . $activeitems . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=sold' . $periodbit . $displayorderbit . '">{_ive_sold} (' . $solditems . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=archived' . $periodbit . $displayorderbit . '">{_archived} (' . $archiveditems . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=delisted' . $periodbit . $displayorderbit . '">{_delisted} (' . $delisteditems . ')</a></li>
<li title="" id="" class="on"><a href="javascript:void(0)">{_ended} (' . $expireditems . ')</a></li>';
$html .= $ilconfig['product_draft_block'] ? '<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=drafts' . $periodbit . $displayorderbit . '">{_draft} (' . $draftitems . ')</a></li>' : '';
$html .= ($ilconfig['moderationsystem_disableauctionmoderation'] == 1 AND $ilconfig['globalauctionsettings_payperpost'] == 0)?'':'<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=pending' . $periodbit . $displayorderbit . '">{_pending} (' . $pendingitems . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id=""class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=product-escrow' . $periodbit . $displayorderbit . '">{_escrow} (' . $productescrow . ')</a></li>';
					}
					($apihook = $ilance->api('print_selling_activity_tab_options_expired_tab')) ? eval($apihook) : false;
					break;
				}
				case 'drafts':
				{
					$html = '<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management' . $periodbit . $displayorderbit . '">{_im_selling} (' . $activeitems . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=sold' . $periodbit . $displayorderbit . '">{_ive_sold} (' . $solditems . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=archived' . $periodbit . $displayorderbit . '">{_archived} (' . $archiveditems . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=delisted' . $periodbit . $displayorderbit . '">{_delisted} (' . $delisteditems . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=expired' . $periodbit . $displayorderbit . '">{_ended} (' . $expireditems . ')</a></li>
<li title="" id="" class="on"><a href="javascript:void(0)">{_draft} (' . $draftitems . ')</a></li>';
$html .= ($ilconfig['moderationsystem_disableauctionmoderation'] == 1 AND $ilconfig['globalauctionsettings_payperpost'] == 0)?'':'<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=pending' . $periodbit . $displayorderbit . '">{_pending} (' . $pendingitems . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id=""class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=product-escrow' . $periodbit . $displayorderbit . '">{_escrow} (' . $productescrow . ')</a></li>';
					}
					($apihook = $ilance->api('print_selling_activity_tab_options_drafts_tab')) ? eval($apihook) : false;
					break;
				}			    
				case 'pending':
				{
					$html = '<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management' . $periodbit . $displayorderbit . '">{_im_selling} (' . $activeitems . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=sold' . $periodbit . $displayorderbit . '">{_ive_sold} (' . $solditems . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=archived' . $periodbit . $displayorderbit . '">{_archived} (' . $archiveditems . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=delisted' . $periodbit . $displayorderbit . '">{_delisted} (' . $delisteditems . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=expired' . $periodbit . $displayorderbit . '">{_ended} (' . $expireditems . ')</a></li>';
$html .= $ilconfig['product_draft_block'] ? '<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=drafts' . $periodbit . $displayorderbit . '">{_draft} (' . $draftitems . ')</a></li>' : '';
$html .= ($ilconfig['moderationsystem_disableauctionmoderation'] == 1 AND $ilconfig['globalauctionsettings_payperpost'] == 0)?'':'<li title="" id="" class="on"><a href="javascript:void(0)">{_pending} (' . $pendingitems . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id=""class=""><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=product-escrow' . $periodbit . $displayorderbit . '">{_escrow} (' . $productescrow . ')</a></li>';
					}
					($apihook = $ilance->api('print_selling_activity_tab_options_pending_tab')) ? eval($apihook) : false;
					break;
				}			    
				case 'escrow':
				{
					$html = '<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management' . $periodbit . $displayorderbit . '">{_im_selling} (' . $activeitems . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=sold' . $periodbit . $displayorderbit . '">{_ive_sold} (' . $solditems . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=archived' . $periodbit . $displayorderbit . '">{_archived} (' . $archiveditems . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=delisted' . $periodbit . $displayorderbit . '">{_delisted} (' . $delisteditems . ')</a></li>
<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=expired' . $periodbit . $displayorderbit . '">{_ended} (' . $expireditems . ')</a></li>';
$html .= $ilconfig['product_draft_block'] ? '<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=drafts' . $periodbit . $displayorderbit . '">{_draft} (' . $draftitems . ')</a></li>' : '';
$html .= ($ilconfig['moderationsystem_disableauctionmoderation'] == 1 AND $ilconfig['globalauctionsettings_payperpost'] == 0)?'':'<li title="" id="" class=""><a href="' . $ilpage['selling'] . '?cmd=management&amp;sub=pending' . $periodbit . $displayorderbit . '">{_pending} (' . $pendingitems . ')</a></li>';
					if ($ilconfig['escrowsystem_enabled'])
					{
						$html .= '<li title="" id="" class="on"><a href="javascript:void(0)">{_escrow} (' . $productescrow . ')</a></li>';
					}
					($apihook = $ilance->api('print_selling_activity_tab_options_escrow_tab')) ? eval($apihook) : false;
					break;
				}
			}
			($apihook = $ilance->api('print_selling_activity_tab_options_custom_tab')) ? eval($apihook) : false;
			break;
		}
	}
	($apihook = $ilance->api('print_selling_activity_tab_options_end')) ? eval($apihook) : false;
	return $html;
}

/*
* Function wrapper to display the main buying activity big tabs for buyer navigation and control
*
* @param        string         button identifier
* @param        string         category type (service/product)
* @param        integer        user id viewing
* @param        string         (optional)    
*
* @return       string         HTML representation of the big tab navigation      
*/
function print_buying_activity_tabs($tab = '', $cattype = '', $userid = 0, $extra = '')
{
	global $ilance, $ilconfig, $show, $ilpage;
	$html = '<div class="bigtabs" style="padding-bottom:9px">
	<div class="bigtabsheader">
		<ul>' . print_buying_activity_tab_options($tab, $cattype, $userid, $extra) . '</ul>
	</div>
</div>
<div style="clear:both;"></div>';
	return $html;
}

/*
* Function wrapper to display the main selling activity big tabs for seller navigation and control
*
* @param        string         button identifier
* @param        string         category type (service/product)
* @param        integer        user id viewing
* @param        string         (optional)    
*
* @return       string         HTML representation of the big tab navigation
*/
function print_selling_activity_tabs($tab = '', $cattype = '', $userid = 0, $extra = '')
{
	global $ilance, $ilconfig, $show, $ilpage;
	$html = '<div class="bigtabs" style="padding-bottom:9px">
	<div class="bigtabsheader">
		<ul>' . print_selling_activity_tab_options($tab, $cattype, $userid, $extra) . '</ul>
	</div>
</div>
<div style="clear:both;"></div>';
	return $html;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>