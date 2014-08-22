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
* common_listing.
*
* @package      iLance\Common\Listing
* @version      4.0.0.8059
* @author       ILance
*/
class common_listing extends common
{
	/**
	* Function to physically remove a listing from the marketplace generally used within the admin control panel.
	*
	* @param       integer        listing id to remove
	*
	* @return      boolean        Returns true
	*/
	function physically_remove_listing($value = 0)
	{
		global $ilance, $show;
		$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects WHERE project_id = '" . intval($value) . "'");
		$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_changelog WHERE project_id = '" . intval($value) . "'");
		$ilance->db->query("DELETE FROM " . DB_PREFIX . "project_answers WHERE project_id = '" . intval($value) . "'");
		$ilance->db->query("DELETE FROM " . DB_PREFIX . "product_answers WHERE project_id = '" . intval($value) . "'");
		$ilance->db->query("DELETE FROM " . DB_PREFIX . "project_bid_retracts WHERE project_id = '" . intval($value) . "'");
		$ilance->db->query("DELETE FROM " . DB_PREFIX . "project_bids WHERE project_id = '" . intval($value) . "'");
		$ilance->db->query("DELETE FROM " . DB_PREFIX . "project_realtimebids WHERE project_id = '" . intval($value) . "'");
		$ilance->db->query("DELETE FROM " . DB_PREFIX . "project_invitations WHERE project_id = '" . intval($value) . "'");
		$ilance->db->query("DELETE FROM " . DB_PREFIX . "proxybid WHERE project_id = '" . intval($value) . "'");
		$ilance->db->query("DELETE FROM " . DB_PREFIX . "attachment WHERE project_id = '" . intval($value) . "'");                                                                
		$ilance->db->query("DELETE FROM " . DB_PREFIX . "attachment_folder WHERE project_id = '" . intval($value) . "'");
		$ilance->db->query("DELETE FROM " . DB_PREFIX . "messages WHERE project_id = '" . intval($value) . "'");
		$ilance->db->query("DELETE FROM " . DB_PREFIX . "pmb WHERE project_id = '" . intval($value) . "'");
		$ilance->db->query("DELETE FROM " . DB_PREFIX . "pmb_alerts WHERE project_id = '" . intval($value) . "'");
		$ilance->db->query("DELETE FROM " . DB_PREFIX . "watchlist WHERE watching_project_id = '" . intval($value) . "'");
		$ilance->db->query("DELETE FROM " . DB_PREFIX . "profile_filter_auction_answers WHERE project_id = '" . intval($value) . "'");
		$ilance->db->query("DELETE FROM " . DB_PREFIX . "bid_fields_answers WHERE project_id = '" . intval($value) . "'");
		$ilance->db->query("DELETE FROM " . DB_PREFIX . "buynow_orders WHERE project_id = '" . intval($value) . "'");
		$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_escrow WHERE project_id = '" . intval($value) . "'");
		$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_shipping WHERE project_id = '" . intval($value) . "'");
		$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_shipping_destinations WHERE project_id = '" . intval($value) . "'");
		$ilance->db->query("DELETE FROM " . DB_PREFIX . "projects_shipping_regions WHERE project_id = '" . intval($value) . "'");
		
		($apihook = $ilance->api('physically_remove_listing_end')) ? eval($apihook) : false;
		
		return true;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>