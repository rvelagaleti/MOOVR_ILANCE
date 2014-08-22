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

if (!class_exists('bid'))
{
	exit;
}

/**
* Function to handle bid permissions
*
* @package      iLance\Bid\Permissions
* @version      4.0.0.8059
* @author       ILance
*/
class bid_permissions extends bid
{
	/**
        * Function for printing any bid filter permissions.
        *
        * @param       string       filter type
        * @param       integer      project id
        */
        function print_filters($filtertype = '', $id = 0)
        {
                global $ilance, $ilconfig, $phrase, $iltemplate, $area_title, $page_title, $parts, $official_time, $phrase, $ilpage;
                if ($filtertype == 'service')
                {
                        $result_bidtop = $ilance->db->query("
                                SELECT filter_rating, filtered_rating, filter_country, filtered_country, filter_state, filtered_state, filter_city, filtered_city, filter_zip, filtered_zip, filter_underage, filter_businessnumber, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
                                FROM " . DB_PREFIX . "projects
                                WHERE project_id = '" . intval($id) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($result_bidtop) > 0)
                        {
                                $filter_permissions = '';
                                while ($row = $ilance->db->fetch_array($result_bidtop, DB_ASSOC))
                                {
                                        ($apihook = $ilance->api('construct_bidfilter_permissions_service_start')) ? eval($apihook) : false;
                                        
                                        if (!empty($_SESSION['ilancedata']['user']['userid']))
                                        {
                                                if ($row['filter_rating'])
                                                {
                                                        $memberinfo = array();
                                                        $memberinfo = $ilance->feedback->datastore($_SESSION['ilancedata']['user']['userid']);
                                                        if ($memberinfo['rating'] >= intval($row['filtered_rating']))
                                                        {
                                                                $filter_permissions .= '<div>{_overall_rating_of_at_least} <strong>' . $row['filtered_rating'] . '  {_stars}</strong> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="{_you_meet_this_requirement}" /></div>';
                                                        }
                                                        else
                                                        {
                                                                $filter_permissions .= '<div>{_overall_rating_of_at_least} <strong>' . $row['filtered_rating'] . '  {_stars}</strong> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="{_you_do_not_meet_this_requirement}" /></div>';
                                                        }
                                                }
                                                if ($row['filter_country'] AND !empty($row['filtered_country']))
                                                {
                                                        $cfiltered = mb_strtolower(stripslashes($row['filtered_country']));
                                                        $countryname = mb_strtolower($ilance->common_location->print_user_country($_SESSION['ilancedata']['user']['userid'], $_SESSION['ilancedata']['user']['slng']));
                                                        if ($cfiltered == $countryname)
                                                        {
                                                                $filter_permissions .= '<div>{_bidders_country_must_be_located_in} <strong>' . ucwords($cfiltered) . ' </strong> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="{_you_meet_this_requirement}" /></div>';
                                                        }
                                                        else
                                                        {
                                                                $filter_permissions .= '<div>{_bidders_country_must_be_located_in} <strong>' . ucwords($cfiltered) . ' </strong> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="{_you_do_not_meet_this_requirement}" /></div>';
                                                        }
                                                }
                                                if ($row['filter_state'] AND !empty($row['filtered_state']))
                                                {
                                                        $sfiltered = mb_strtolower($row['filtered_state']);
                                                        $cstate = mb_strtolower($_SESSION['ilancedata']['user']['state']);
                                                        if ($cstate == $sfiltered)
                                                        {
                                                                $filter_permissions .= '<div>{_bidders_state_province_must_be_located_in} <strong>' . ucwords($sfiltered) . ' </strong> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="{_you_meet_this_requirement}" /></div>';
                                                        }
                                                        else
                                                        {
                                                                $filter_permissions .= '<div>{_bidders_state_province_must_be_located_in} <strong>' . ucwords($sfiltered) . ' </strong> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="{_you_do_not_meet_this_requirement}" /></div>';
                                                        }
                                                }
                                                if ($row['filter_city'] AND !empty($row['filtered_city']))
                                                {
                                                        $cityfiltered = mb_strtolower($row['filtered_city']);
                                                        $ccity = mb_strtolower($_SESSION['ilancedata']['user']['city']);
                                                        if ($ccity == $cityfiltered)
                                                        {
                                                                $filter_permissions .= '<div>{_bidders_city_must_be_located_in} <strong>' . ucwords($cityfiltered) . ' </strong> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="{_you_meet_this_requirement}" /></div>';
                                                        }
                                                        else
                                                        {
                                                                $filter_permissions .= '<div>{_bidders_city_must_be_located_in} <strong>' . ucwords($cityfiltered) . ' </strong> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="{_you_do_not_meet_this_requirement}" /></div>';
                                                        }
                                                }
                                                if ($row['filter_zip'] AND !empty($row['filtered_zip']))
                                                {
                                                        $zipfiltered = mb_strtolower($row['filtered_zip']);
                                                        $czip = mb_strtolower($_SESSION['ilancedata']['user']['postalzip']);
                                                        if ($czip == $zipfiltered)
                                                        {
                                                                $filter_permissions .= '<div>{_bidders_zip_postal_code_must_be_located_in} <strong>' . mb_strtoupper($zipfiltered) . ' </strong> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="{_you_meet_this_requirement}" /></div>';
                                                        }
                                                        else
                                                        {
                                                                $filter_permissions .= '<div>{_bidders_zip_postal_code_must_be_located_in} <strong>' . mb_strtoupper($zipfiltered) . ' </strong> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></div>';
                                                        }
                                                }
                                                if ($row['filter_underage'])
                                                {
                                                        
                                                }
                                                if ($row['filter_businessnumber'])
                                                {
                                                        
                                                }
                                        }
                                }
                        }
                }
                else if ($filtertype == 'product')
                {
                        $result_bidtop = $ilance->db->query("
                                SELECT filter_rating, filtered_rating, filter_country, filtered_country, filter_state, filtered_state, filter_city, filtered_city, filter_zip, filtered_zip, filter_underage, filter_businessnumber, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
                                FROM " . DB_PREFIX . "projects
                                WHERE project_id = '" . intval($id) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($result_bidtop) > 0)
                        {
                                $filter_permissions = '';
                                while ($row = $ilance->db->fetch_array($result_bidtop, DB_ASSOC))
                                {
                                        ($apihook = $ilance->api('construct_bidfilter_permissions_product_start')) ? eval($apihook) : false;
                                        
                                        if (!empty($_SESSION['ilancedata']['user']['userid']))
                                        {
                                                if ($row['filter_rating'])
                                                {
                                                        $memberinfo = array();
                                                        $memberinfo = $ilance->feedback->datastore($_SESSION['ilancedata']['user']['userid']);
                                                        if ($memberinfo['rating'] >= intval($row['filtered_rating']))
                                                        {
                                                                $filter_permissions .= '<div>{_overall_rating_of_at_least} <strong>' . $row['filtered_rating'] . '  {_stars}</strong> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="{_you_meet_this_requirement}" /></div>';
                                                        }
                                                        else
                                                        {
                                                                $filter_permissions .= '<div>{_overall_rating_of_at_least} <strong>' . $row['filtered_rating'] . '  {_stars}</strong> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="{_you_do_not_meet_this_requirement}" /></div>';
                                                        }
                                                }
                                                if ($row['filter_country'] AND !empty($row['filtered_country']))
                                                {
                                                        $cfiltered = mb_strtolower(stripslashes($row['filtered_country']));
                                                        $countryname = mb_strtolower($ilance->common_location->print_user_country($_SESSION['ilancedata']['user']['userid'], $_SESSION['ilancedata']['user']['slng']));
                                                        if ($cfiltered == $countryname)
                                                        {
                                                                $filter_permissions .= '<div>{_bidders_country_must_be_located_in} <strong>' . ucwords($cfiltered) . ' </strong> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="{_you_meet_this_requirement}" /></div>';
                                                        }
                                                        else
                                                        {
                                                                $filter_permissions .= '<div>{_bidders_country_must_be_located_in} <strong>' . ucwords($cfiltered) . ' </strong> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="{_you_do_not_meet_this_requirement}" /></div>';
                                                        }
                                                }
                                                if ($row['filter_state'] AND !empty($row['filtered_state']))
                                                {
                                                        $sfiltered = mb_strtolower($row['filtered_state']);
                                                        $cstate = mb_strtolower($_SESSION['ilancedata']['user']['state']);
                                                        if ($cstate == $sfiltered)
                                                        {
                                                                $filter_permissions .= '<div>{_bidders_state_province_must_be_located_in} <strong>' . ucwords($sfiltered) . ' </strong> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="{_you_meet_this_requirement}" /></div>';
                                                        }
                                                        else
                                                        {
                                                                $filter_permissions .= '<div>{_bidders_state_province_must_be_located_in} <strong>' . ucwords($sfiltered) . ' </strong> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="{_you_do_not_meet_this_requirement}" /></div>';
                                                        }
                                                }
                                                if ($row['filter_city'] AND !empty($row['filtered_city']))
                                                {
                                                        $cityfiltered = mb_strtolower($row['filtered_city']);
                                                        $ccity = mb_strtolower($_SESSION['ilancedata']['user']['city']);
                                                        if ($ccity == $cityfiltered)
                                                        {
                                                                $filter_permissions .= '<div>{_bidders_city_must_be_located_in} <strong>' . ucwords($cityfiltered) . ' </strong> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="{_you_meet_this_requirement}" /></div>';
                                                        }
                                                        else
                                                        {
                                                                $filter_permissions .= '<div>{_bidders_city_must_be_located_in} <strong>' . ucwords($cityfiltered) . ' </strong> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="{_you_do_not_meet_this_requirement}" /></div>';
                                                        }
                                                }
                                                if ($row['filter_zip'] AND !empty($row['filtered_zip']))
                                                {
                                                        $zipfiltered = mb_strtolower($row['filtered_zip']);
                                                        $czip = mb_strtolower($_SESSION['ilancedata']['user']['postalzip']);
                                                        if ($czip == $zipfiltered)
                                                        {
                                                                $filter_permissions .= '<div>{_bidders_zip_postal_code_must_be_located_in} <strong>' . mb_strtoupper($zipfiltered) . ' </strong> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="{_you_meet_this_requirement}" /></div>';
                                                        }
                                                        else
                                                        {
                                                                $filter_permissions .= '<div>{_bidders_zip_postal_code_must_be_located_in} <strong>' . mb_strtoupper($zipfiltered) . ' </strong> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></div>';
                                                        }
                                                }
                                                if ($row['filter_underage'])
                                                {
                                                }
                                                if ($row['filter_businessnumber'])
                                                {
                                                }
                                        }
                                }
                        }
                }
                if (!empty($filter_permissions))
                {
                        return $filter_permissions;
                }
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>