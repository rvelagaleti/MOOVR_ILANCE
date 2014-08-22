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
* Core Search Preferences functions for ILance.
*
* @package      iLance\Global\Search\Prefs
* @version	4.0.0.8059
* @author       ILance
*/

/*
* Function to fetch the default search options from ILance core configuration $ilconfig array
*
* @return       string          Returns a serailzed() string array of default search options      
*/
function fetch_default_searchoptions()
{
        global $ilconfig;
        return $ilconfig['searchdefaultcolumns'];
}

// #### LOAD SEARCH PREFERENCES ################################################
$serviceselectedhidden = $expertselectedhidden = $productselectedhidden = '';
if (!empty($_SESSION['ilancedata']['user']['userid']))
{
	if (empty($_SESSION['ilancedata']['user']['searchoptions']))
	{
	    // registered user's first time here.  Let's build his searchoptions array and save it for him.
	    update_default_searchoptions($_SESSION['ilancedata']['user']['userid'], fetch_default_searchoptions());       
	}
	else
	{
	    $arr = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
	    foreach ($arr['productselected'] as $key => $value)
	    {
		if ($value == 'location')
		{
		    unset($arr['productselected'][$key]);
		    update_default_searchoptions($_SESSION['ilancedata']['user']['userid'], serialize($arr));
		}
	    }
	}
}
else if (empty($_SESSION['ilancedata']['user']['userid']))
{
        // guest is viewing.. do we have a temp search preference for this user?
        if (empty($_SESSION['ilancedata']['user']['searchoptions']))
        {
                // let's build a proper search options array for the guest with default options
                $_SESSION['ilancedata']['user']['searchoptions'] = fetch_default_searchoptions();
        }
	else
	{
	    $arr = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
	    foreach ($arr['productselected'] as $key => $value)
	    {
		if ($value == 'location')
		{
		    unset($arr['productselected'][$key]);
		    $_SESSION['ilancedata']['user']['searchoptions'] = serialize($arr);
		}
	    }
	}
}

// #### LOAD DEFAULT COLUMN SETTINGS ###########################################
$servicecolumns = array(
        'title' => '_title',
        'bids' => '_bids',
        'averagebid' => '_average_bid',
        'timeleft' => '_time_left',
        'started' => '_posted',
        'skills' => '_skills',
        'category' => '_category',
        //'views' => '_views',
        'budget' => '_budget',
        //'city' => '_city',
        //'zipcode' => '_zip_slash_postal_code',
        //'state' => '_state',
        //'country' => '_country',
        'location' => '_location',
        'sel' => '_sel'
);
if ($ilconfig['globalserver_enabledistanceradius'])
{
        $servicecolumns['distance'] = '_distance';        
}
// service columns that cannot be removed from search
$servicecolumns_static = array(
        'title',
        'sel'
);
$productcolumns = array(
        'sample' => '_sample',
        'title' => '_title',
        'price' => '_price',
        'bids' => '_bids',
        'timeleft' => '_time_left',
        'started' => '_started',
        'category' => '_category',
        'shipping' => '_shipping',
        //'views' => '_views',
        //'city' => '_city',
        //'zipcode' => '_zip_slash_postal_code',
        //'state' => '_state',
        //'country' => '_country',
        //'location' => '_location',
        'sel' => '_sel'
);
if ($ilconfig['globalserver_enabledistanceradius'])
{
        $productcolumns['distance'] = '_distance';        
}
// product columns that cannot be removed from search
$productcolumns_static = array(
        'title',
        'sel'
);
$expertcolumns = array(
        'profilelogo' => '_logo',
        'expert' => '_expert',
        'rateperhour' => '_hourly_rate',
        'credentials' => '_credentials',
        'feedback' => '_feedback',
        'rated' => '_rated',
        'earnings' => '_earnings',
        'awards' => '_awards',
        'portfolio' => '_portfolio',
        //'city' => '_city',
        //'zipcode' => '_zip_slash_postal_code',
        //'state' => '_state_or_province',
        'country' => '_country',
        'location' => '_location',
        'sel' => '_sel'
);
if ($ilconfig['globalserver_enabledistanceradius'])
{
        $expertcolumns['distance'] = '_distance';        
}
// expert columns that cannot be removed from search
$expertcolumns_static = array(
        'expert',
        'sel'
);
// determine if we have any profile questions acting as filters as well
$filtercolumns = fetch_filtered_searchoptions();
$expertcolumns = array_merge($expertcolumns, $filtercolumns);

($apihook = $ilance->api('search_options_columns_end')) ? eval($apihook) : false;

// #### PARSE COLUMN SELECT MENUS ##############################################
$selected = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
if (isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] > 0)
{
        $selected['perpage'] = intval($ilance->GPC['pp']);
}
if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
{
        // service columns that can still be selected
        $serviceavailable = '<select name="serviceavailable" size="8" style="width:280px" class="input" multiple="multiple">';
        foreach ($servicecolumns AS $key => $phrasename)
        {
                if (isset($selected['serviceselected']) AND !in_array($key, $selected['serviceselected']))
                {
                        $serviceavailable .= '<option value="' . $key . '">{' . $phrasename . '}</option>';
                }
        }
        $serviceavailable .= '</select>';
        // service selected columns
        $serviceselected = '<select id="serviceselected" name="serviceselected[]" size="8" style="width:280px" class="input" multiple="multiple">';
        if (isset($selected['serviceselected']))
        {
                foreach ($selected['serviceselected'] AS $key)
                {
                        if ($key == 'distance')
                        {
                                if ($ilconfig['globalserver_enabledistanceradius'])
                                {
                                        $serviceselected .= (in_array($key, $servicecolumns_static))
                                                ? '<option value="' . $key . '">{' . $servicecolumns[$key] . '}*</option>'
                                                : '<option value="' . $key . '">{' . $servicecolumns[$key] . '}</option>';
                                        
                                }
                        }
                        else
                        {
                                $serviceselected .= (in_array($key, $servicecolumns_static))
                                        ? '<option value="' . $key . '">{' . $servicecolumns[$key] . '}*</option>'
                                        : '<option value="' . $key . '">{' . $servicecolumns[$key] . '}</option>';
                        }
                }
        }
        $serviceselected .= '</select>';
        // expert columns that can still be selected
        $expertavailable = '<select name="expertavailable" size="8" style="width:280px" class="input" multiple="multiple">';
        foreach ($expertcolumns AS $key => $phrasename)
        {
                if (isset($selected['expertselected']) AND !in_array($key, $selected['expertselected']))
                {
                	$expertavailable .= '<option value="' . $key . '">' . (strchr($phrasename, '_') ? '{' . $phrasename . '}' : $phrasename) . '</option>';
                }
        }
        $expertavailable .= '</select>';
        // expert selected columns
        $expertselected = '<select id="expertselected" name="expertselected[]" size="8" style="width:280px" class="input" multiple="multiple">';
        if (isset($selected['expertselected']))
        {
                foreach ($selected['expertselected'] AS $key)
                {
                        if ($key == 'distance')
                        {
                                if ($ilconfig['globalserver_enabledistanceradius'])
                                {
                                        if (isset($phrase[$expertcolumns[$key]]))
                                        {
                                                $expertselected .= (in_array($key, $expertcolumns_static))
                                                        ? '<option value="' . $key . '">{' . $expertcolumns[$key] . '}*</option>'
                                                        : '<option value="' . $key . '">{' . $expertcolumns[$key] . '}</option>';
                                        }
                                        else
                                        {
                                                $expertselected .= (in_array($key, $expertcolumns_static))
                                                        ? '<option value="' . $key . '">' . $expertcolumns[$key] . '*</option>'
                                                        : '<option value="' . $key . '">' . $expertcolumns[$key] . '</option>';
                                        }
                                }
                        }
                        else
                        {
                                if (isset($phrase[$expertcolumns[$key]]))
                                {
                                        $expertselected .= (in_array($key, $expertcolumns_static))
                                                ? '<option value="' . $key . '">{' . $expertcolumns[$key] . '}*</option>'
                                                : '<option value="' . $key . '">{' . $expertcolumns[$key] . '}</option>';
                                }
                                else
                                {
                                        $expertselected .= (in_array($key, $expertcolumns_static))
                                                ? '<option value="' . $key . '">{' . $expertcolumns[$key] . '}*</option>'
                                                : '<option value="' . $key . '">{' . $expertcolumns[$key] . '}</option>';
                                }        
                        }
                }       
        }
        $expertselected .= '</select>';
}
if ($ilconfig['globalauctionsettings_productauctionsenabled'])
{
        // product columns still available
        $productavailable = '<select name="productavailable" size="8" style="width:280px" class="input" multiple="multiple">';
        if (isset($productcolumns) AND !empty($productcolumns) AND isset($selected['productselected']) AND !empty($selected['productselected']))
        {
                foreach ($productcolumns AS $key => $phrasename)
                {
                        if (!in_array($key, $selected['productselected']))
                        {
                                $productavailable .= '<option value="' . $key . '">{' . $phrasename . '}</option>';
                        }
                }
        }
        $productavailable .= '</select>';
        // product selected columns
        $productselected  = '<select id="productselected" name="productselected[]" size="8" style="width:280px" class="input" multiple="multiple">';
        if (isset($selected['productselected']) AND !empty($selected['productselected']))
        {
                foreach ($selected['productselected'] AS $key)
                {
                        if ($key == 'distance')
                        {
                                if ($ilconfig['globalserver_enabledistanceradius'])
                                {
                                        $productselected .= (in_array($key, $productcolumns_static))
                                                ? '<option value="' . $key . '">{' . $productcolumns[$key] . '}*</option>'
                                                : '<option value="' . $key . '">{' . $productcolumns[$key] . '}</option>';
                                }
                        }
                        else
                        {
                                $productselected .= (in_array($key, $productcolumns_static))
                                        ? '<option value="' . $key . '">{' . $productcolumns[$key] . '}*</option>'
                                        : '<option value="' . $key . '">{' . $productcolumns[$key] . '}</option>';
                        }
                }
        }
        $productselected .= '</select>';
}
if ($ilconfig['globalauctionsettings_productauctionsenabled'] AND $ilconfig['globalauctionsettings_serviceauctionsenabled'] == false)
{
        if (isset($selected['serviceselected']))
        {
                foreach ($selected['serviceselected'] AS $key)
                {
                        if ($key == 'distance')
                        {
                                if ($ilconfig['globalserver_enabledistanceradius'])
                                {
                                        $serviceselectedhidden .= '<input type="hidden" name="serviceselected[]" value="' . $key . '" id="serviceselected_' . $key . '" />';
                                        
                                }
                        }
                        else
                        {
                                $serviceselectedhidden .= '<input type="hidden" name="serviceselected[]" value="' . $key . '" id="serviceselected_' . $key . '" />';
                        }
                }
        }
        if (isset($selected['expertselected']))
        {
                foreach ($selected['expertselected'] AS $key)
                {
                        if ($key == 'distance')
                        {
                                if ($ilconfig['globalserver_enabledistanceradius'])
                                {
                                        if (isset($phrase[$expertcolumns[$key]]))
                                        {
                                                $expertselectedhidden .= '<input type="hidden" name="expertselected[]" value="' . $key . '" id="expertselected_' . $key . '" />';
                                        }
                                        else
                                        {
                                                $expertselectedhidden .= '<input type="hidden" name="expertselected[]" value="' . $key . '" id="expertselected_' . $key . '" />';
                                        }
                                }
                        }
                        else
                        {
                                if (isset($phrase[$expertcolumns[$key]]))
                                {
                                        $expertselectedhidden .= '<input type="hidden" name="expertselected[]" value="' . $key . '" id="expertselected_' . $key . '" />';
                                }
                                else
                                {
                                        $expertselectedhidden .= '<input type="hidden" name="expertselected[]" value="' . $key . '" id="expertselected_' . $key . '" />';
                                }        
                        }
                }       
        }
}
if ($ilconfig['globalauctionsettings_productauctionsenabled'] == false AND $ilconfig['globalauctionsettings_serviceauctionsenabled'])
{
        if (isset($selected['productselected']) AND !empty($selected['productselected']))
        {
                foreach ($selected['productselected'] AS $key)
                {
                        if ($key == 'distance')
                        {
                                if ($ilconfig['globalserver_enabledistanceradius'])
                                {
                                        $productselectedhidden .= '<input type="hidden" name="producteselected[]" value="' . $key . '" id="productselected_' . $key . '" />';
                                }
                        }
                        else
                        {
                                $productselectedhidden .= '<input type="hidden" name="productselected[]" value="' . $key . '" id="productselected_' . $key . '" />';
                        }
                }
        }
}
/*
* Function to print and display the search options javascript logic
*
* @return       string          Returns formatted javascript <script> code within the <body>  
*/
function print_searchoptions_js()
{
        $html = '
<script type="text/javaScript">
<!--
function move_right(mode) 
{
        if (mode == \'service\')
        {
                var m1 = opt1;
                var m2 = opt2;
        }
        else if (mode == \'product\')
        {
                var m1 = opt3;
                var m2 = opt4;
        }
        else if (mode == \'expert\')
        {
                var m1 = opt5;
                var m2 = opt6;
        }
        m1len = m1.length;
        for (i = 0; i < m1len; i++)
        {
                if (m1.options[i].selected == true) 
                {
                        m2len = m2.length;
                        m2.options[m2len] = new Option(m1.options[i].text, m1.options[i].value);
                }
        }	
        for (i = (m1len -1); i >= 0; i--)
        {
                if (m1.options[i].selected == true) 
                {
                        m1.options[i] = null;
                }
        }
}
function move_left(mode) 
{
        if (mode == \'service\')
        {
                var m1 = opt1;
                var m2 = opt2;
        }
        else if (mode == \'product\')
        {
                var m1 = opt3;
                var m2 = opt4;
        }
        else if (mode == \'expert\')
        {
                var m1 = opt5;
                var m2 = opt6;
        }
        m2len = m2.length ;
        for (i = 0; i < m2len ; i++)
        {
                if (m2.options[i].selected == true) 
                {
                        if (m2.options[i].value == \'title\' || m2.options[i].value == \'expert\' || m2.options[i].value == \'sel\')
                        {
                                alert_js(phrase[\'_this_is_a_static_column_and_cannot_be_removed\']);
                        }
                        else
                        {
                                m1len = m1.length;
                                m1.options[m1len] = new Option(m2.options[i].text, m2.options[i].value);
                        }
                }
        }
        for (i = (m2len - 1); i >= 0; i--) 
        {
                if (m2.options[i].selected == true) 
                {
                        if (m2.options[i].value == \'title\' || m2.options[i].value == \'expert\' || m2.options[i].value == \'sel\')
                        {
                        
                        }
                        else
                        {
                                m2.options[i] = null;
                        }
                }
        }
}
function move_up(list) 
{
        if (! list || ! list.options || list.options.length == 0)  return;
        var saved = new Object;
        var i,j,k;
        for (i = list.options.length - 1; i >= 0; i--) 
        {
                if (list.options[i].selected) 
                {
                        for (j = i-1; j >= 0; j--) 
                        {
                                if (!list.options[j].selected) break;
                        }
                        if (j >= 0) 
                        {
                                // save current selection
                                cpAttr(list.options[j], saved);
                                
                                // add the item above selection
                                for (k = j; k < i; k++) 
                                {
                                        cpAttr(list.options[k+1], list.options[k]);
                                }
                                
                                cpAttr(saved,list.options[i]);
                                i = j;
                        }
                }
        }
}
function move_down(list) 
{
        if (! list || ! list.options || list.options.length == 0)  return;
        var saved = new Object;
        var i,j,k;
        for (i = 0; i < list.options.length; i++) 
        {
                if (list.options[i].selected) 
                {
                        // find next unselected item
                        for (j = i+1; j < list.options.length; j++) 
                        {
                                if (! list.options[j].selected) break;
                        }
                        if (j < list.options.length) 
                        {
                                // save current selection
                                cpAttr(list.options[j], saved);
                                // add the item above selection
                                for (k = j; k > i; k--) 
                                {
                                        cpAttr(list.options[k-1], list.options[k]);
                                }
                                cpAttr(saved, list.options[i]);
                                i = j;
                        }
                }
        }
}
function cpAttr(src, dest) 
{
        dest.text = src.text;
        dest.value = src.value;
        dest.selected = src.selected;
        dest.defaultSelected = src.defaultSelected;
}
function select_all_lists() 
{
        sourceList1 = fetch_js_object(\'serviceselected\');
        if (sourceList1 != null)
        {
                for (var i = 0; i < sourceList1.options.length; i++) 
                {
                        if (sourceList1.options[i] != null) 
                        {
                                sourceList1.options[i].selected = true;
                        }
                }
        }
        sourceList2 = fetch_js_object(\'productselected\');
        if (sourceList2 != null)
        {
                for (var i = 0; i < sourceList2.options.length; i++) 
                {
                        if (sourceList2.options[i] != null) 
                        {
                                sourceList2.options[i].selected = true;
                        }
                }
        }
        sourceList3 = fetch_js_object(\'expertselected\');
        if (sourceList3 != null)
        {
                for (var i = 0; i < sourceList3.options.length; i++) 
                {
                        if (sourceList3.options[i] != null) 
                        {
                                sourceList3.options[i].selected = true;
                        }
                }
        }
        return true;
}
//-->
</script>
';
        
        return $html;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>