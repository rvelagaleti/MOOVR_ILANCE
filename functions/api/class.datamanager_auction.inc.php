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

if (!class_exists('datamanager'))
{
	exit;
}

/**
* Auction data manager class to handle the majority of setting and saving routines in ILance
*
* @package      iLance\DataManager\Auction
* @version      4.0.0.8059
* @author       ILance
*/
class datamanager_auction extends datamanager
{
        var $allowfields = array(
                'id'                        => array('TYPE_INT', 'REQUIRED_AUTO'),
                'project_id'                => array('TYPE_INT', 'REQUIRED_NO'),
                'escrow_id'                 => array('TYPE_INT', 'REQUIRED_NO'),
                'cid'                       => array('TYPE_INT', 'REQUIRED_YES'),
                'description'               => array('TYPE_STR', 'REQUIRED_YES'),
                'date_added'                => array('TYPE_STR', 'REQUIRED_AUTO'),
                'date_starts'               => array('TYPE_STR', 'REQUIRED_NO'),
                'date_end'                  => array('TYPE_STR', 'REQUIRED_YES'),
                'user_id'                   => array('TYPE_INT', 'REQUIRED_YES'),
                'visible'                   => array('TYPE_INT', 'REQUIRED_NO'),
                'views'                     => array('TYPE_NUM', 'REQUIRED_NO'),
                'project_title'             => array('TYPE_STR', 'REQUIRED_YES'),
                'bids'                      => array('TYPE_NUM', 'REQUIRED_NO'),
                'budgetgroup'               => array('TYPE_STR', 'REQUIRED_NO'),
                'additional_info'           => array('TYPE_STR', 'REQUIRED_NO'),
                'status'                    => array('TYPE_STR', 'REQUIRED_NO'),
                'close_date'                => array('TYPE_STR', 'REQUIRED_NO'),
                'transfertype'              => array('TYPE_STR', 'REQUIRED_NO'),
                'transfer_to_userid'        => array('TYPE_INT', 'REQUIRED_NO'),
                'transfer_from_userid'      => array('TYPE_INT', 'REQUIRED_NO'),
                'transfer_to_email'         => array('TYPE_STR', 'REQUIRED_NO'),
                'transfer_status'           => array('TYPE_STR', 'REQUIRED_NO'),
                'transfer_code'             => array('TYPE_STR', 'REQUIRED_NO'),
                'project_details'           => array('TYPE_STR', 'REQUIRED_NO'),
                'project_type'              => array('TYPE_STR', 'REQUIRED_NO'),
                'project_state'             => array('TYPE_STR', 'REQUIRED_YES'),
                'bid_details'               => array('TYPE_STR', 'REQUIRED_NO'),
                'filter_rating'             => array('TYPE_INT', 'REQUIRED_NO'),
                'filter_country'            => array('TYPE_INT', 'REQUIRED_NO'),
                'filter_state'              => array('TYPE_INT', 'REQUIRED_NO'),
                'filter_city'               => array('TYPE_INT', 'REQUIRED_NO'),
                'filter_zip'                => array('TYPE_INT', 'REQUIRED_NO'),
                'filter_underage'           => array('TYPE_INT', 'REQUIRED_NO'),
                'filter_businessnumber'     => array('TYPE_INT', 'REQUIRED_NO'),
                'filter_bidtype'            => array('TYPE_INT', 'REQUIRED_NO'),
                'filter_budget'             => array('TYPE_INT', 'REQUIRED_NO'),
                'filter_escrow'             => array('TYPE_INT', 'REQUIRED_NO'),
                'filter_publicboard'        => array('TYPE_INT', 'REQUIRED_NO'),
                'filtered_rating'           => array('TYPE_STR', 'REQUIRED_NO'),
                'filtered_country'          => array('TYPE_STR', 'REQUIRED_NO'),
                'filtered_state'            => array('TYPE_STR', 'REQUIRED_NO'),
                'filtered_city'             => array('TYPE_STR', 'REQUIRED_NO'),
                'filtered_zip'              => array('TYPE_STR', 'REQUIRED_NO'),
                'filtered_bidtype'          => array('TYPE_STR', 'REQUIRED_NO'),
                'filtered_bidtypecustom'    => array('TYPE_STR', 'REQUIRED_NO'),
                'filtered_budgetid'         => array('TYPE_INT', 'REQUIRED_NO'),
                'filtered_auctiontype'      => array('TYPE_STR', 'REQUIRED_NO'),
                'buynow'                    => array('TYPE_INT', 'REQUIRED_NO'),
                'buynow_price'              => array('TYPE_STR', 'REQUIRED_NO'),
                'buynow_qty'                => array('TYPE_INT', 'REQUIRED_NO'),
                'reserve'                   => array('TYPE_INT', 'REQUIRED_NO'),
                'reserve_price'             => array('TYPE_STR', 'REQUIRED_NO'),
                'featured'                  => array('TYPE_INT', 'REQUIRED_NO'),
                'highlite'                  => array('TYPE_INT', 'REQUIRED_NO'),
                'bold'                      => array('TYPE_INT', 'REQUIRED_NO'),
                'startprice'                => array('TYPE_STR', 'REQUIRED_NO'),
                'paymethod'                 => array('TYPE_STR', 'REQUIRED_NO'),
                'paymethodoptions'          => array('TYPE_STR', 'REQUIRED_NO'),
                'keywords'                  => array('TYPE_STR', 'REQUIRED_NO'),
                'currentprice'              => array('TYPE_STR', 'REQUIRED_NO'),
                'insertionfee'              => array('TYPE_STR', 'REQUIRED_NO'),
		'donation'                  => array('TYPE_INT', 'REQUIRED_NO'),
		'charityid'                 => array('TYPE_INT', 'REQUIRED_NO'),
		'donationpercentage'        => array('TYPE_INT', 'REQUIRED_NO'),
		'donermarkedaspaid'         => array('TYPE_INT', 'REQUIRED_NO'),
		'donermarkedaspaiddate'     => array('TYPE_STR', 'REQUIRED_NO'),
		'currencyid'                => array('TYPE_INT', 'REQUIRED_NO'),
		'countryid'                 => array('TYPE_INT', 'REQUIRED_NO'),
		'country'                   => array('TYPE_STR', 'REQUIRED_NO'),
		'state'                     => array('TYPE_STR', 'REQUIRED_NO'),
		'city'                      => array('TYPE_STR', 'REQUIRED_NO'),
		'zipcode'                   => array('TYPE_STR', 'REQUIRED_NO'),
                'fvf'                       => array('TYPE_STR', 'REQUIRED_NO'),
                'updateid'                  => array('TYPE_INT', 'REQUIRED_NO'),
        );
        
        function datamanager_auction(&$registry)
	{
		parent::datamanager($registry);
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>