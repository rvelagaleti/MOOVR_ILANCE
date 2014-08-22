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
* User data manager class to handle the majority of setting and saving routines in ILance.
* Ultimately this will also provide developers with a usable api system to add, update and remove users.
*
* @package      iLance\DataManager\Users
* @version      4.0.0.8059
* @author       ILance
*/
class datamanager_users extends datamanager
{
        /**
        * var           $allowfields          contains all fields available from the user table
        */
        var $allowfields = array(
                'user_id'                   => array('TYPE_INT', 'REQUIRED_AUTO'),
                'ipaddress'                 => array('TYPE_STR', 'REQUIRED_NO'),
                'iprestrict'                => array('TYPE_INT', 'REQUIRED_NO'),
                'username'                  => array('TYPE_STR', 'REQUIRED_YES'),
                'password'                  => array('TYPE_STR', 'REQUIRED_YES'),
                'salt'                      => array('TYPE_STR', 'REQUIRED_AUTO'),
                'secretquestion'            => array('TYPE_STR', 'REQUIRED_NO'),
                'secretanswer'              => array('TYPE_STR', 'REQUIRED_NO'),
                'email'                     => array('TYPE_STR', 'REQUIRED_YES'),
                'first_name'                => array('TYPE_STR', 'REQUIRED_NO'),
                'last_name'                 => array('TYPE_STR', 'REQUIRED_NO'),
                'address'                   => array('TYPE_STR', 'REQUIRED_NO'),
                'address2'                  => array('TYPE_STR', 'REQUIRED_NO'),
                'city'                      => array('TYPE_STR', 'REQUIRED_NO'),
                'state'                     => array('TYPE_STR', 'REQUIRED_NO'),
                'zip_code'                  => array('TYPE_STR', 'REQUIRED_NO'),
                'phone'                     => array('TYPE_STR', 'REQUIRED_NO'),
                'country'                   => array('TYPE_NUM', 'REQUIRED_NO'),
                'date_added'                => array('TYPE_STR', 'REQUIRED_AUTO'),
                'subcategories'             => array('TYPE_STR', 'REQUIRED_NO'),
                'status'                    => array('TYPE_STR', 'REQUIRED_NO'),
                'serviceawards'             => array('TYPE_INT', 'REQUIRED_NO'),
                'productawards'             => array('TYPE_INT', 'REQUIRED_NO'),
		'servicesold'               => array('TYPE_INT', 'REQUIRED_NO'),
                'productsold'               => array('TYPE_INT', 'REQUIRED_NO'),
                'rating'                    => array('TYPE_STR', 'REQUIRED_NO'),
                'score'                     => array('TYPE_STR', 'REQUIRED_NO'),
                'bidstoday'                 => array('TYPE_NUM', 'REQUIRED_NO'),
                'bidsthismonth'             => array('TYPE_NUM', 'REQUIRED_NO'),
                'auctiondelists'            => array('TYPE_NUM', 'REQUIRED_NO'),
                'bidretracts'               => array('TYPE_NUM', 'REQUIRED_NO'),
                'lastseen'                  => array('TYPE_STR', 'REQUIRED_NO'),
                'dob'                       => array('TYPE_STR', 'REQUIRED_NO'),
                'rid'                       => array('TYPE_STR', 'REQUIRED_NO'),
                'account_number'            => array('TYPE_STR', 'REQUIRED_AUTO'),
                'available_balance'         => array('TYPE_STR', 'REQUIRED_NO'),
                'total_balance'             => array('TYPE_STR', 'REQUIRED_NO'),
                'income_reported'           => array('TYPE_STR', 'REQUIRED_NO'),
                'income_spent'              => array('TYPE_STR', 'REQUIRED_NO'),
                'startpage'                 => array('TYPE_STR', 'REQUIRED_NO'),
                'styleid'                   => array('TYPE_INT', 'REQUIRED_NO'),
                'project_distance'          => array('TYPE_INT', 'REQUIRED_NO'),
                'currency_calculation'      => array('TYPE_INT', 'REQUIRED_NO'),
                'languageid'                => array('TYPE_INT', 'REQUIRED_NO'),
                'currencyid'                => array('TYPE_INT', 'REQUIRED_NO'),
                'timezone'                  => array('TYPE_STR', 'REQUIRED_NO'),
                'notifyservices'            => array('TYPE_INT', 'REQUIRED_NO'),
                'notifyproducts'            => array('TYPE_INT', 'REQUIRED_NO'),
                'notifyservicescats'        => array('TYPE_STR', 'REQUIRED_NO'),
                'notifyproductscats'        => array('TYPE_STR', 'REQUIRED_NO'),
		'lastemailservicecats'      => array('TYPE_STR', 'REQUIRED_NO'),
		'lastemailproductcats'      => array('TYPE_STR', 'REQUIRED_NO'),
                'displayprofile'            => array('TYPE_INT', 'REQUIRED_NO'),
                'emailnotify'               => array('TYPE_INT', 'REQUIRED_NO'),
                'displayfinancials'         => array('TYPE_INT', 'REQUIRED_NO'),
                'vatnumber'                 => array('TYPE_STR', 'REQUIRED_NO'),
                'regnumber'                 => array('TYPE_STR', 'REQUIRED_NO'),
                'dnbnumber'                 => array('TYPE_STR', 'REQUIRED_NO'),
                'companyname'               => array('TYPE_STR', 'REQUIRED_NO'),
                'usecompanyname'            => array('TYPE_INT', 'REQUIRED_NO'),
                'timeonsite'                => array('TYPE_INT', 'REQUIRED_NO'),
                'daysonsite'                => array('TYPE_INT', 'REQUIRED_NO'),
		'isadmin'                   => array('TYPE_INT', 'REQUIRED_NO'),
		'permissions'               => array('TYPE_STR', 'REQUIRED_NO'),
		'searchoptions'             => array('TYPE_STR', 'REQUIRED_NO'),
		'rateperhour'               => array('TYPE_STR', 'REQUIRED_NO'),
		'profilevideourl'           => array('TYPE_STR', 'REQUIRED_NO'),
		'profileintro'              => array('TYPE_STR', 'REQUIRED_NO'),
		'gender'                    => array('TYPE_STR', 'REQUIRED_NO'),
		'freelancing'               => array('TYPE_INT', 'REQUIRED_NO'),
		'autopayment'               => array('TYPE_INT', 'REQUIRED_NO'),
        );
        
        function datamanager_users(&$registry)
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