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
* Tax class to perform the majority of tax functions and calculations in ILance.
*
* @package      iLance\Tax
* @version      4.0.0.8059
* @author       ILance
*/
class tax
{
        /**
        * Fetch the total amount (with taxes applied) based on a user, taxtype and an amount to calculate by
        *
        * @param 	integer         user id
        * @param        integer         amount to add taxes to (ie: 5.00)
        * @param        string          tax type logic to work with
        * @param        bool            determine if we want the returning amount formatted with currency details or not (default not)
        *
        * @return       string          Returns formatted or unformatted version of the total amount after taxes applied.
        */
        function fetch_amount($userid = 0, $total, $taxtype, $formatted = 0)
        {
                global $ilance;
                $return = '';
                $usercountry = fetch_user('country', intval($userid));
                $userstate = fetch_user('state', intval($userid));
                $sql = $ilance->db->query("
                        SELECT taxlabel, countryid, state, city, amount, invoicetypes, entirecountry
                        FROM " . DB_PREFIX . "taxes
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                // country checkup
                                if ($res['countryid'] == $usercountry)
                                {
                                        // state or province checkup
                                        if ($res['entirecountry'] OR !empty($res['state']) AND mb_strtolower($res['state']) == mb_strtolower($userstate))
                                        {
                                                // we found a match (country and state both match for this user)
                                                if (!empty($res['invoicetypes']))
                                                {
                                                        $invoicetypetax = unserialize($res['invoicetypes']);
                                                        foreach ($invoicetypetax AS $invoicetype => $value)
                                                        {
                                                                if ($invoicetype == $taxtype)
                                                                {
                                                                        if ($formatted)
                                                                        {
                                                                                $return .= $ilance->currency->format(($total * $res['amount'] / 100)) . ' ' . stripslashes($res['taxlabel']) . '<!-- @ ' . $res['amount'] . '%-->, ';
                                                                        }
                                                                        else
                                                                        {
                                                                                $return += ($total * $res['amount'] / 100);
                                                                        }
                                                                }
                                                        }
                                                }
                                        }
                                }
                        }
                        if (isset($formatted) AND $formatted)
                        {
                                $return = mb_substr($return, 0, -2);
                        }
                }
                return ($formatted == '1') ? $return : round($return, 2);
        }
        
        /**
        * Function to determine if a particular user based on a tax type is actually taxable within the system
        *
        * @param 	integer         user id
        * @param        string          tax type logic to work with
        *
        * @return       bool            Returns true if taxable, false if not.
        */
        function is_taxable($userid = 0, $taxtype)
        {
                global $ilance, $ilconfig;
                $usercountry = fetch_user('country', intval($userid));
                $userstate = fetch_user('state', intval($userid));
                $sql = $ilance->db->query("
                        SELECT countryid, state, city, invoicetypes, entirecountry
                        FROM " . DB_PREFIX . "taxes
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                // country checkup
                                if ($res['countryid'] == $usercountry)
                                {
                                        // state or province checkup
                                        if ($res['entirecountry'] OR !empty($res['state']) AND mb_strtolower($res['state']) == mb_strtolower($userstate))
                                        {
                                                // we found a match (country and state both match for this user)
                                                if (!empty($res['invoicetypes']))
                                                {
                                                        $invoicetypetax = unserialize($res['invoicetypes']);
                                                        foreach ($invoicetypetax AS $invoicetype => $value)
                                                        {
                                                                if (isset($taxtype) AND mb_strtolower($invoicetype) == mb_strtolower($taxtype))
                                                                {
                                                                        if ($taxtype == 'commission' AND $ilconfig['escrowsystem_feestaxable'] == 0)
                                                                        {
                                                                                return 0;        
                                                                        }
                                                                        else
                                                                        {
                                                                                return 1;
                                                                        }
                                                                }
                                                        }
                                                }
                                        }
                                        else
                                        {
                                                // we only found a country match (but no match for state or province)
                                                // let's apply country tax defined by admin because admin might not have prepared a state/province filter yet
                                                if (!empty($res['invoicetypes']))
                                                {
                                                        $invoicetypetax = unserialize($res['invoicetypes']);
                                                        foreach ($invoicetypetax AS $invoicetype => $value)
                                                        {
                                                                if (isset($taxtype) AND mb_strtolower($invoicetype) == mb_strtolower($taxtype))
                                                                {
                                                                        if ($taxtype == 'commission' AND $ilconfig['escrowsystem_feestaxable'] == 0)
                                                                        {
                                                                                return 0;        
                                                                        }
                                                                        else
                                                                        {
                                                                                return 1;
                                                                        }
                                                                }
                                                        }
                                                }
                                        }
                                }
                        }
                }
                return 0;
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>