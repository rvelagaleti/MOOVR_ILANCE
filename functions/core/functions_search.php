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
* Core Search functions for iLance.
*
* @package      iLance\Global\Search
* @version	4.0.0.8059
* @author       ILance
*/

/*
* Function to store our search verbose string in a global variable for later retrieval
*
* @param       string         keywords
*
* @return      nothing
*/
function handle_search_verbose($text = '')
{
	$GLOBALS['verbose'][] = $text;
}

/*
* Function to store our search verbose filters string in a global variable for later retrieval
*
* @param       string         keywords
* @param       boolean        wrap <li> tag around string (default false)
*
* @return      nothing
*/
function handle_search_verbose_filters($text = '', $dolist = false)
{
	$GLOBALS['verbose_filter'][] = (($dolist) ? "<li>$text</li>" : $text);
}

/*
* Function to store our search verbose for save this search string in a global variable for later retrieval
*
* @param       string         keywords
*
* @return      nothing
*/
function handle_search_verbose_save($text = '')
{
	$GLOBALS['verbose_save'][] = $text;
}

/*
* Function to print the saved search verbose text based on a filter being supplied to this function 
*
* @param       string         filter
*
* @return      string         Returns HTML representation of the saved search verbose string
*/
function print_search_verbose_saved($filter = '')
{
        $html = '';
        if (!empty($GLOBALS["$filter"]))
        {
                foreach ($GLOBALS["$filter"] AS $key => $text)
                {
                        $html .= $text;
                }
        }
        return $html;
}

/*
* Function to save search keywords inputted by users from the search menu input boxes throughout the marketplace into the db.
* Additionally, this function will work with multiple keywords separated via comma (ie: keyword1, keyword2, etc)
*
* @param       string         keywords
* @param       string         search mode (service, product, experts)
* $param       integer        category id user is search in
*
* @return      nothing
*/
function handle_search_keywords($keywords = '', $mode = '', $cid = 0)
{
        global $ilance, $show;
	$minlength = 2;
	$maxlengthwithoutspace = 16;
        $staticmodes = array('service','product','experts','stores');
        if (!in_array($mode, $staticmodes))
        {
                $mode = '';
        }
	if (mb_strlen($keywords) < $minlength)
	{
		return false;
	}
	// don't allow integer only keywords
	if (is_numeric($keywords))
	{
		return false;
	}
	// don't capture search bot queries
	if (isset($show['searchengine']) AND $show['searchengine'])
	{
		return false;
	}
	// don't allow equal symbol = 
        if (!empty($keywords) AND strchr($keywords, '='))
        {
		return false;
	}
	// don't allow keywords longer than 20 characters without a space
	if (!empty($keywords) AND !strchr($keywords, ' '))
        {
		if (mb_strlen($keywords) > $maxlengthwithoutspace)
		{
			return false;
		}
	}
	
        // use api hook below if you need to update $staticmodes for your custom code
        ($apihook = $ilance->api('handle_search_keywords_start')) ? eval($apihook) : false;
        
        if (!empty($keywords) AND strchr($keywords, ','))
        {
                $keywords = explode(',', $keywords);
                if (sizeof($keywords) > 1)
                {
                        for ($i = 0; $i < sizeof($keywords); $i++)
                        {
                                $sql = $ilance->db->query("
                                        SELECT id
                                        FROM " . DB_PREFIX . "search
                                        WHERE keyword = '" . trim($ilance->db->escape_string($keywords[$i])) . "'
                                                AND searchmode = '" . $ilance->db->escape_string($mode) . "'
						AND cid = '" . intval($cid) . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) == 0)
                                {
                                        $ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "search
                                                (id, keyword, searchmode, cid, count)
                                                VALUES(
                                                NULL,
                                                '" . trim($ilance->db->escape_string($keywords[$i])) . "',
                                                '" . $ilance->db->escape_string($mode) . "',
						'" . intval($cid) . "',
                                                '0')
                                        ", 0, null, __FILE__, __LINE__);
                                }
                                else
                                {
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "search
                                                SET count = count + 1
                                                WHERE keyword = '" . trim($ilance->db->escape_string($keywords[$i])) . "'
                                                        AND searchmode = '" . $ilance->db->escape_string($mode) . "'
							AND cid = '" . intval($cid) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                }
                                
                                // keep history of a registered users search queries for dating mining later on
                                if (!empty($_SESSION['ilancedata']['user']['userid']))
                                {
                                        $ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "search_users
                                                (id, user_id, cid, keyword, searchmode, added, ipaddress, uservisible)
                                                VALUES(
                                                NULL,
                                                '" . $_SESSION['ilancedata']['user']['userid'] . "',
						'" . intval($cid) . "',
                                                '" . trim($ilance->db->escape_string($keywords[$i])) . "',
                                                '" . $ilance->db->escape_string($mode) . "',
                                                '" . DATETIME24H . "',
						'" . $ilance->db->escape_string(IPADDRESS) . "',
						'1')
                                        ", 0, null, __FILE__, __LINE__);        
                                }
				// keep history of what guests used for search queries
				else if (defined('IPADDRESS') AND IPADDRESS != '')
				{
					$ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "search_users
                                                (id, user_id, cid, keyword, searchmode, added, ipaddress, uservisible)
                                                VALUES(
                                                NULL,
                                                '0',
						'" . intval($cid) . "',
                                                '" . trim($ilance->db->escape_string($keywords[$i])) . "',
                                                '" . $ilance->db->escape_string($mode) . "',
                                                '" . DATETIME24H . "',
						'" . $ilance->db->escape_string(IPADDRESS) . "',
						'1')
                                        ", 0, null, __FILE__, __LINE__);
				}
                        }
                }
        }
        else 
        {
                if (!empty($keywords))
                {
                        $sql = $ilance->db->query("
                                SELECT id
                                FROM " . DB_PREFIX . "search
                                WHERE keyword = '" . trim($ilance->db->escape_string($keywords)) . "'
					AND cid = '" . intval($cid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) == 0)
                        {
                                $ilance->db->query("
                                        INSERT INTO " . DB_PREFIX . "search
                                        (id, keyword, searchmode, cid, count)
                                        VALUES(
                                        NULL,
                                        '" . trim($ilance->db->escape_string($keywords)) . "',
                                        '" . $ilance->db->escape_string($mode) . "',
					'" . intval($cid) . "',
                                        '0')
                                ", 0, null, __FILE__, __LINE__);
                        }
                        else
                        {
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "search
                                        SET count = count + 1
                                        WHERE keyword = '" . trim($ilance->db->escape_string($keywords)) . "'
                                                AND searchmode = '" . $ilance->db->escape_string($mode) . "'
						AND cid = '" . intval($cid) . "'
                                ", 0, null, __FILE__, __LINE__);
                        }
			
			// keep history of a registered users search patterns
			if (!empty($_SESSION['ilancedata']['user']['userid']))
			{
				$ilance->db->query("
					INSERT INTO " . DB_PREFIX . "search_users
					(id, user_id, cid, keyword, searchmode, added, ipaddress, uservisible)
					VALUES(
					NULL,
					'" . $_SESSION['ilancedata']['user']['userid'] . "',
					'" . intval($cid) . "',
					'" . trim($ilance->db->escape_string($keywords)) . "',
					'" . $ilance->db->escape_string($mode) . "',
					'" . DATETIME24H . "',
					'" . $ilance->db->escape_string(IPADDRESS) . "',
					'1')
				", 0, null, __FILE__, __LINE__);        
			}
			// keep history of what guests used for search queries
			else if (defined('IPADDRESS') AND IPADDRESS != '')
			{
				$ilance->db->query("
					INSERT INTO " . DB_PREFIX . "search_users
					(id, user_id, cid, keyword, searchmode, added, ipaddress, uservisible)
					VALUES(
					NULL,
					'0',
					'" . intval($cid) . "',
					'" . trim($ilance->db->escape_string($keywords)) . "',
					'" . $ilance->db->escape_string($mode) . "',
					'" . DATETIME24H . "',
					'" . $ilance->db->escape_string(IPADDRESS) . "',
					'1')
				", 0, null, __FILE__, __LINE__);
			}
                }
        }
}

/*
* Function to print the active countries pulldown menu
*
* @param       string         fieldname
* @param       string         selected option value (if applicable)
* @param       string         short form language code (eng, ger, pol, etc)
* @param       boolean        show world wide an an option? (default true)
* @param       string         select id="" parameter (default blank)
* @param       boolean        show country names as option values (default false, show locationid as values instead)
*
* @return      string         Returns HTML representation of the pulldown menu
*/
function print_active_countries_pulldown($fieldname = '', $selected = '', $slng = 'eng', $showworldwide = true, $id = '', $shownames = false)
{
        global $ilance;
	$options = array();
        if ($showworldwide)
        {
		$options['all'] = '{_worldwide}';
        }
	$field = ($shownames) ? 'location' : 'locationid';
        $sql = $ilance->db->query("
                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.countryid, c.locationid, c.location_$slng AS location
                FROM " . DB_PREFIX . "projects AS p,
                " . DB_PREFIX . "locations AS c
                WHERE p.countryid = c.locationid
			AND c.visible = '1'
                GROUP BY p.countryid
        ", 0, null, __FILE__, __LINE__);
        while ($crow = $ilance->db->fetch_array($sql, DB_ASSOC))
        {
		$options[$crow["$field"]] = stripslashes($crow['location']);
        }
        return construct_pulldown($id, $fieldname, $options, $selected, ' class="select"');
}

/*
* Function to print the regions (continents)
*
* @param       string         fieldname
* @param       string         selected option value (if applicable)
* @param       string         short form language code (eng, ger, pol, etc)
* @param       string         element object id (id="")
* @param       string         display type to print (pulldown or links)
* @param       boolean        determine if we want to handle onchange on pulldowns to disable distance bit when only a region contains no country id.
* @param       integer        search form id (<form id="xx">..)
*
* @return      string         Returns HTML representation of the pull down menu
*/
function print_regions($fieldname = '', $selected = '', $slng = 'eng', $id = '', $displaytype = 'pulldown', $onchange = false, $searchformid = '0')
{
        global $ilance, $ilconfig, $phrase, $scriptpage, $php_self, $ilregions;
	$html = '';
	$regioncount = 0;
	$showonlycountryid = fetch_country_id($ilconfig['registrationdisplay_defaultcountry'], $_SESSION['ilancedata']['user']['slng']);
	$sql = $ilance->db->query("
		SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "region_" . $slng . " AS region
		FROM " . DB_PREFIX . "locations_regions
		GROUP BY region_" . $slng . "
		ORDER BY region_" . $slng . " ASC
	", 0, null, __FILE__, __LINE__);
	if ($displaytype == 'pulldown')
	{
		if ($onchange AND $ilconfig['globalserver_enabledistanceradius'])
		{
			if ($searchformid == '1')
			{
				$distancediv1 = 'if (DISTANCE == 1){fetch_js_object(\'cb_servicedistance\').disabled = false; fetch_js_object(\'cb_servicedistance\').checked = false; toggle_show(\'toggleradiusservice\');}';
				$distancediv2 = 'if (DISTANCE == 1){fetch_js_object(\'cb_servicedistance\').disabled = true; fetch_js_object(\'serviceradius\').disabled = true; fetch_js_object(\'serviceradiuszip\').disabled = true; toggle_hide(\'toggleradiusservice\');}';
			}
			else if ($searchformid == '2')
			{
				$distancediv1 = 'if (DISTANCE == 1){fetch_js_object(\'cb_productdistance\').disabled = false; fetch_js_object(\'cb_productdistance\').checked = false; toggle_show(\'toggleradiusproduct\');}';
				$distancediv2 = 'if (DISTANCE == 1){fetch_js_object(\'cb_productdistance\').disabled = true; fetch_js_object(\'productradius\').disabled = true; fetch_js_object(\'productradiuszip\').disabled = true; toggle_hide(\'toggleradiusproduct\');}';
			}
			else if ($searchformid == '3')
			{
				$distancediv1 = 'if (DISTANCE == 1){fetch_js_object(\'cb_expertdistance\').disabled = false; fetch_js_object(\'cb_expertdistance\').checked = false; toggle_show(\'toggleradiusexperts\');}';
				$distancediv2 = 'if (DISTANCE == 1){fetch_js_object(\'cb_expertdistance\').disabled = true; fetch_js_object(\'expertradius\').disabled = true; fetch_js_object(\'expertradiuszip\').disabled = true; toggle_hide(\'toggleradiusexperts\');}';
			}
		}
		$onchangejs = ($onchange AND $ilconfig['globalserver_enabledistanceradius'])
			? ' onchange="javascript:
			if (DISTANCE == 1)
			{
				var idselected = fetch_js_object(\'' . $id . '\').value
				if (idselected.indexOf(\'.\') == \'-1\')
				{
					' . $distancediv2 . '
				}
				else
				{
					' . $distancediv1 . '
				}
			}"'
			: '';
			
		$html .= '<select name="' . $fieldname . '" id="' . $id . '"' . $onchangejs . ' class="select">';
		// #### show option to only show country of installed site #####
		//$html .= ($showonlycountryid > 0) ? '<option value="' . $ilance->shipping->fetch_region_by_countryid($showonlycountryid) . '.' . $showonlycountryid . '">{_only} ' . handle_input_keywords($ilconfig['registrationdisplay_defaultcountry']) . '</option>' : '';
		// #### show option to show results worldwide ##################
		$html .= ($ilconfig['worldwideshipping'] == '1') ? '<option value="worldwide">{_worldwide}</option><option value="" disabled="disabled">-----------------</option>' : '';
		// #### loop through accepted regions of the installed site ####
		while ($crow = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$region = strtolower(str_replace(' ', '_', $crow['region']));
			if (isset($ilregions["$region"]) AND $ilregions["$region"])
			{
				$html .= '<option value="' . $region . '">' . handle_input_keywords($crow['region']) . '</option>';
			}
		}
		$html .= '</select>';
	}
	else if ($displaytype == 'links')
	{
		$html .= '<div style="padding-top:6px"></div>';
		$selected2 = $countryid = '';
		if (!empty($selected) AND strrchr($selected, '.'))
		{
			$regtemp = explode('.', $selected);
			if (!empty($regtemp[0]))
			{
				$selected = $regtemp[0];
			}
			if (!empty($regtemp[1]))
			{
				$selected2 = '.' . $regtemp[1];
				$countryid = $regtemp[1]; 
			}
			unset($regtemp);
		}
		else if (!empty($selected))
		{
			$regionname = $selected;
		}
		// make sure our php_self string contains a ?
		$php_self = (strrchr($php_self, "?") == false) ? $php_self . '?mode=' . $ilance->GPC['mode'] : $php_self;
		$removeurl = rewrite_url($php_self, 'region=' . $selected);
		//$removeurl = rewrite_url($php_self, 'region=' . $selected . $selected2);
		$removeurl = ($countryid > 0) ? rewrite_url($removeurl, 'countryid=' . $countryid) : $removeurl;
		$removeurl = (isset($ilance->GPC['country'])) ? rewrite_url($removeurl, 'country=' . urlencode($ilance->GPC['country'])) : $removeurl;
		$removeurl = (isset($ilance->GPC['state'])) ? rewrite_url($removeurl, 'state=' . urlencode($ilance->GPC['state'])) : $removeurl;
		$removeurl = (isset($ilance->GPC['city'])) ? rewrite_url($removeurl, 'city=' . urlencode($ilance->GPC['city'])) : $removeurl;
		$removeurl = (isset($ilance->GPC['radiuszip'])) ? rewrite_url($removeurl, 'radiuszip=' . urlencode($ilance->GPC['radiuszip'])) : $removeurl;
		$removeurl = (isset($ilance->GPC['radius'])) ? rewrite_url($removeurl, 'radius=' . $ilance->GPC['radius']) : $removeurl;
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		// #### worldwide ##############################################
		/*if ($ilregions['worldwide'])
		{
			$regioncount++;
			$html .= ($selected == 'worldwide' OR (empty($ilance->GPC['region'])))
				? '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png" border="0" alt="" id="" name="sel_worldwide" /></span><span class="blueonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_worldwide\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')" onmouseout="rollovericon(\'sel_worldwide\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')"><strong>{_worldwide}</strong></a></span></div>'
				: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_worldwide" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;region=worldwide" onmouseover="rollovericon(\'unsel_worldwide\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_worldwide\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_worldwide}</a></span></div>';
		}*/	
		// #### show option to only show country of installed site #####
		/*if ($showonlycountryid > 0)
		{
			if (empty($ilance->GPC['region']) OR strrchr($ilance->GPC['region'], '.') == false)
			{
				$html .= (!empty($ilance->GPC['country']) AND urldecode($ilance->GPC['country']) == $ilconfig['registrationdisplay_defaultcountry'])
					? '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="unsel_worldwide2" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'unsel_worldwide2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'unsel_worldwide2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_only} ' . handle_input_keywords($ilconfig['registrationdisplay_defaultcountry']) . '</strong></a></span></div>'
					: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="sel_worldwide2" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;country=' . urlencode(handle_input_keywords($ilconfig['registrationdisplay_defaultcountry'])) . '" onmouseover="rollovericon(\'sel_worldwide2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'sel_worldwide2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_only} ' . handle_input_keywords($ilconfig['registrationdisplay_defaultcountry']) . '</a></span></div>';
			}
		}*/			
		unset($removeurl);
		while ($crow = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$removeurl = rewrite_url($php_self, 'region=' . $selected);
			$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
			$currentregion = strtolower(str_replace(' ', '_', $crow['region']));
			if ($currentregion == $selected)
			{
				if (isset($ilregions["$currentregion"]) AND $ilregions["$currentregion"])
				{
					$regioncount++;
					//$removeurl = rewrite_url($php_self, 'region=' . $selected . $selected2);
					$removeurl = rewrite_url($php_self, 'region=' . $selected);
					$removeurl = ($countryid > 0) ? rewrite_url($removeurl, 'countryid=' . $countryid) : $removeurl;
					$removeurl = (isset($ilance->GPC['country'])) ? rewrite_url($removeurl, 'country=' . urlencode($ilance->GPC['country'])) : $removeurl;
					$removeurl = (isset($ilance->GPC['state'])) ? rewrite_url($removeurl, 'state=' . urlencode($ilance->GPC['state'])) : $removeurl;
					$removeurl = (isset($ilance->GPC['city'])) ? rewrite_url($removeurl, 'city=' . urlencode($ilance->GPC['city'])) : $removeurl;
					$removeurl = (isset($ilance->GPC['radiuszip'])) ? rewrite_url($removeurl, 'radiuszip=' . urlencode($ilance->GPC['radiuszip'])) : $removeurl;
					$removeurl = (isset($ilance->GPC['radius'])) ? rewrite_url($removeurl, 'radius=' . $ilance->GPC['radius']) : $removeurl;
					$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
					$html .= '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . ';padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px;padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_' . strtolower(str_replace(' ', '_', $crow['region'])). '" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_' . strtolower(str_replace(' ', '_', $crow['region'])). '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_' . strtolower(str_replace(' ', '_', $crow['region'])). '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $crow['region'] . '</strong></a></span></div>';
					if (!empty($countryid) AND !empty($selected2))
					{
						$removeurl = rewrite_url($php_self, $selected2);
						$removeurl = ($countryid > 0) ? rewrite_url($removeurl, 'countryid=' . $countryid) : $removeurl;
						$removeurl = (isset($ilance->GPC['country'])) ? rewrite_url($removeurl, 'country=' . urlencode($ilance->GPC['country'])) : $removeurl;
						$removeurl = (isset($ilance->GPC['state'])) ? rewrite_url($removeurl, 'state=' . urlencode($ilance->GPC['state'])) : $removeurl;
						$removeurl = (isset($ilance->GPC['city'])) ? rewrite_url($removeurl, 'city=' . urlencode($ilance->GPC['city'])) : $removeurl;
						$removeurl = (isset($ilance->GPC['radiuszip'])) ? rewrite_url($removeurl, 'radiuszip=' . urlencode($ilance->GPC['radiuszip'])) : $removeurl;
						$removeurl = (isset($ilance->GPC['radius'])) ? rewrite_url($removeurl, 'radius=' . $ilance->GPC['radius']) : $removeurl;
						$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
						//$html .= '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_' . strtolower(str_replace(' ', '_', $crow['region'])) . $selected2 . '" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_' . strtolower(str_replace(' ', '_', $crow['region'])). $selected2 . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_' . strtolower(str_replace(' ', '_', $crow['region'])) . $selected2 . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $ilance->common_location->print_country_name($countryid, $_SESSION['ilancedata']['user']['slng'], false) . '</strong></a></span></div>';
					}
				}
			}
			else
			{
				if (isset($ilregions["$currentregion"]) AND $ilregions["$currentregion"])
				{
					$regioncount++;
					//$removeurl = rewrite_url($php_self, 'region=' . $selected . $selected2);
					$removeurl = rewrite_url($php_self, 'region=' . $selected);
					$removeurl = ($countryid > 0) ? rewrite_url($removeurl, 'countryid=' . $countryid) : $removeurl;
					$removeurl = (isset($ilance->GPC['country'])) ? rewrite_url($removeurl, 'country=' . urlencode($ilance->GPC['country'])) : $removeurl;
					$removeurl = (isset($ilance->GPC['state'])) ? rewrite_url($removeurl, 'state=' . urlencode($ilance->GPC['state'])) : $removeurl;
					$removeurl = (isset($ilance->GPC['city'])) ? rewrite_url($removeurl, 'city=' . urlencode($ilance->GPC['city'])) : $removeurl;
					$removeurl = (isset($ilance->GPC['radiuszip'])) ? rewrite_url($removeurl, 'radiuszip=' . urlencode($ilance->GPC['radiuszip'])) : $removeurl;
					$removeurl = (isset($ilance->GPC['radius'])) ? rewrite_url($removeurl, 'radius=' . $ilance->GPC['radius']) : $removeurl;
					$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
					$html .= '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_' . strtolower(str_replace(' ', '_', $crow['region'])). '" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;region=' . strtolower(str_replace(' ', '_', $crow['region'])). '" onmouseover="rollovericon(\'unsel_' . strtolower(str_replace(' ', '_', $crow['region'])). '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_' . strtolower(str_replace(' ', '_', $crow['region'])). '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $crow['region'] . '</a></span></div>';
				}
			}
			unset($removeurl);
		}
	}
	if ($regioncount == 1)
	{
		$html = '';
		$ilconfig['search_location_tab'] = 0;
	}
        return $html;
}

/*
* ...
*
* @param       
*
* @return      
*/
function print_buying_formats($mode = 'links')
{
	global $ilance, $ilconfig, $phrase, $scriptpage, $php_self, $show, $clear_listtype_url;
	$html = '';
	$auction = (isset($ilance->GPC['auction']) AND $ilance->GPC['auction'] > 0) ? intval($ilance->GPC['auction']) : '';
	$buynow = (isset($ilance->GPC['buynow']) AND $ilance->GPC['buynow'] > 0) ? intval($ilance->GPC['buynow']) : '';
	$inviteonly = (isset($ilance->GPC['inviteonly']) AND $ilance->GPC['inviteonly'] > 0) ? intval($ilance->GPC['inviteonly']) : '';
	$scheduled = (isset($ilance->GPC['scheduled']) AND $ilance->GPC['scheduled'] > 0) ? intval($ilance->GPC['scheduled']) : '';
	$classified = (isset($ilance->GPC['classified']) AND $ilance->GPC['classified'] > 0) ? intval($ilance->GPC['classified']) : '';
	$page = (isset($ilance->GPC['page']) AND $ilance->GPC['page'] > 0) ? intval($ilance->GPC['page']) : '1';
	$php_self = rewrite_url($php_self, 'page=' . $page);
	$removeurlall = rewrite_url($php_self, 'auction=' . $auction);
	$removeurlall = rewrite_url($removeurlall, 'buynow=' . $buynow);
	$removeurlall = rewrite_url($removeurlall, 'inviteonly=' . $inviteonly);
	$removeurlall = rewrite_url($removeurlall, 'scheduled=' . $scheduled);
	$removeurlall = rewrite_url($removeurlall, 'classified=' . $classified);
	$clear_listtype_url = $removeurlall;
	switch ($mode)
	{
		case 'links':
		{
			// all
			$html .= ($show['allbuyingformats'])
				? '<div style="padding-bottom:6px;padding-top:2px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png" border="0" alt="" id="" name="sel_auctiontype0" /></span><span class="blueonly"><a href="' . $removeurlall . '" onmouseover="rollovericon(\'sel_auctiontype0\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')" onmouseout="rollovericon(\'sel_auctiontype0\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')"><strong>{_any}</strong></a></span></div>'
				: '<div style="padding-bottom:6px;padding-top:2px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_auctiontype0" /></span><span class="blueonly"><a href="' . $removeurlall . '" onmouseover="rollovericon(\'unsel_auctiontype0\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_auctiontype0\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_any}</a></span></div>';
			// make sure our php_self string contains a ?
			if (strrchr($php_self, "?") == false)
			{
				// we'll include our master variable which should rewrite our urls nice and friendly
				$php_self = $php_self . '?mode=' . $ilance->GPC['mode'];
			}
			
			($apihook = $ilance->api('print_buying_formats_start')) ? eval($apihook) : false;
			
			if ($ilance->GPC['mode'] == 'product')
			{
				($apihook = $ilance->api('print_buying_formats_product_start')) ? eval($apihook) : false;
				
				// forward auction
				if ($ilconfig['enableauctiontab'])
				{
					$removeurl = rewrite_url($php_self, 'auction=' . $auction);
					$html .= (isset($ilance->GPC['auction']) AND $ilance->GPC['auction'] == '1')
						? '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_auctiontype1" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_auctiontype1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_auctiontype1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_auction}</strong></a></span></div>'
						: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_auctiontype1" /></span><span class="blueonly"><a href="' . $php_self . '&amp;auction=1" onmouseover="rollovericon(\'unsel_auctiontype1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_auctiontype1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_auction}</a></span></div>';
				}
				// fixed price
				if ($ilconfig['enablefixedpricetab'])
				{
					$removeurl = rewrite_url($php_self, 'buynow=' . $buynow);
					$html .= (isset($ilance->GPC['buynow']) AND $ilance->GPC['buynow'] == '1')
						? '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_auctiontype5" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_auctiontype5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_auctiontype5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_fixed_price}</strong></a></span></div>'
						: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_auctiontype5" /></span><span class="blueonly"><a href="' . $php_self . '&amp;buynow=1" onmouseover="rollovericon(\'unsel_auctiontype5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_auctiontype5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_fixed_price}</a></span></div>';
				}
				// classified ads
				if ($ilconfig['enableclassifiedtab'])
				{
					$removeurl = rewrite_url($php_self, 'classified=' . $classified);
					$html .= (isset($ilance->GPC['classified']) AND $ilance->GPC['classified'] == '1')
						? '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_auctiontype6" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_auctiontype6\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_auctiontype6\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_classified_ads}</strong></a></span></div>'
						: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_auctiontype6" /></span><span class="blueonly"><a href="' . $php_self . '&amp;classified=1" onmouseover="rollovericon(\'unsel_auctiontype6\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_auctiontype6\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_classified_ads}</a></span></div>';
				}
				
				($apihook = $ilance->api('print_buying_formats_product_end')) ? eval($apihook) : false;
			}
			else if ($ilance->GPC['mode'] == 'service')
			{
				($apihook = $ilance->api('print_buying_formats_service_start')) ? eval($apihook) : false;
				
				// reverse auction
				$removeurl = rewrite_url($php_self, 'auction=' . $auction);
				$html .= (isset($ilance->GPC['auction']) AND $ilance->GPC['auction'] == '1')
					? '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_auctiontype1" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_auctiontype1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_auctiontype1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_reverse_auction}</strong></a></span></div>'
					: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_auctiontype1" /></span><span class="blueonly"><a href="' . $php_self . '&amp;auction=1" onmouseover="rollovericon(\'unsel_auctiontype1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_auctiontype1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_reverse_auction}</a></span></div>';
				
				// invite only
				$removeurl = rewrite_url($php_self, 'inviteonly=' . $inviteonly);		
				$html .= (isset($ilance->GPC['inviteonly']) AND $ilance->GPC['inviteonly'] == '1')
					? '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_auctiontype2" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_auctiontype2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_auctiontype2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_invite_only}</strong></a></span></div>'
					: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_auctiontype2" /></span><span class="blueonly"><a href="' . $php_self . '&amp;inviteonly=1" onmouseover="rollovericon(\'unsel_auctiontype2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_auctiontype2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_invite_only}</a></span></div>';
					
				($apihook = $ilance->api('print_buying_formats_service_end')) ? eval($apihook) : false;
					
			}
			
			// upcoming scheduled
			if ($ilconfig['product_scheduled_bidding_block'])
			{
				$removeurl = rewrite_url($php_self, 'scheduled=' . $scheduled);
				$html .= (isset($ilance->GPC['scheduled']) AND $ilance->GPC['scheduled'] == '1')
					? '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_auctiontype3" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_auctiontype3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_auctiontype3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_upcoming}</strong></a></span></div>'
					: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_auctiontype3" /></span><span class="blueonly"><a href="' . $php_self . '&amp;scheduled=1" onmouseover="rollovericon(\'unsel_auctiontype3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_auctiontype3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_upcoming}</a></span></div>';
			}
			
			($apihook = $ilance->api('print_buying_formats_end')) ? eval($apihook) : false;
			break;
		}
	}
	return $html;
}

/*
* ...
*
* @param       
*
* @return      
*/
function print_options($mode = 'product')
{
	global $ilance, $ilconfig, $phrase, $scriptpage, $php_self, $show, $clear_options, $clear_options_all;
	$html = '<div style="padding-top:7px"></div>';
	if ($mode == 'service' OR $mode == 'product')
	{
		$custom1 = $custom2 = $custom3 = $custom4 = $custom5 = '';
		$images = (isset($ilance->GPC['images'])) ? handle_input_keywords($ilance->GPC['images']) : '';
		$publicboard = (isset($ilance->GPC['publicboard']) AND $ilance->GPC['publicboard'] > 0) ? intval($ilance->GPC['publicboard']) : '';
		$freeshipping = (isset($ilance->GPC['freeshipping']) AND $ilance->GPC['freeshipping'] > 0) ? intval($ilance->GPC['freeshipping']) : '';
		$listedaslots = (isset($ilance->GPC['listedaslots']) AND $ilance->GPC['listedaslots'] > 0) ? intval($ilance->GPC['listedaslots']) : '';
		$escrow = (isset($ilance->GPC['escrow']) AND $ilance->GPC['escrow'] > 0 AND $ilconfig['escrowsystem_enabled']) ? intval($ilance->GPC['escrow']) : '';
		$budget = (isset($ilance->GPC['budget']) AND $ilance->GPC['budget'] > 0) ? intval($ilance->GPC['budget']) : '';
		$donation = (isset($ilance->GPC['donation']) AND $ilance->GPC['donation'] > 0) ? intval($ilance->GPC['donation']) : '';
		$completed = (isset($ilance->GPC['completed']) AND $ilance->GPC['completed'] > 0) ? intval($ilance->GPC['completed']) : '';
		$classifieds = (isset($ilance->GPC['classifieds']) AND $ilance->GPC['classifieds'] > 0) ? intval($ilance->GPC['classifieds']) : '';
		$urgent = (isset($ilance->GPC['urgent']) AND $ilance->GPC['urgent'] > 0) ? intval($ilance->GPC['urgent']) : '';
		
		($apihook = $ilance->api('print_options_end_urlbits')) ? eval($apihook) : false;
		
		$removeurlall = rewrite_url($php_self, 'images=' . $images);
		$removeurlall = rewrite_url($removeurlall, 'publicboard=' . $publicboard);
		$removeurlall = rewrite_url($removeurlall, 'freeshipping=' . $freeshipping);
		$removeurlall = rewrite_url($removeurlall, 'listedaslots=' . $listedaslots);
		$removeurlall = rewrite_url($removeurlall, 'escrow=' . $escrow);
		$removeurlall = rewrite_url($removeurlall, 'budget=' . $budget);
		$removeurlall = rewrite_url($removeurlall, 'donation=' . $donation);
		$removeurlall = rewrite_url($removeurlall, 'completed=' . $completed);
		$removeurlall = rewrite_url($removeurlall, 'classifieds=' . $classifieds);
		$removeurlall = rewrite_url($removeurlall, 'urgent=' . $urgent);
		
		($apihook = $ilance->api('print_options_end_rewritebits')) ? eval($apihook) : false;
		
		$clear_options = $removeurlall;
		$clear_options_all = (empty($custom1) AND empty($custom2) AND empty($custom3) AND empty($custom4) AND empty($custom5) AND empty($images) AND empty($publicboard) AND empty($freeshipping) AND empty($listedaslots) AND empty($escrow) AND empty($donation) AND empty($completed) AND empty($classifieds) AND empty($urgent))
			? ''
			: $removeurlall;
			
		($apihook = $ilance->api('print_options_start')) ? eval($apihook) : false;
	
		// make sure our php_self string contains a ?
		if (strrchr($php_self, "?") == false)
		{
			// we'll include our master variable which should rewrite our urls nice and friendly
			$php_self = $php_self . '?mode=' . strip_tags($ilance->GPC['mode']);
		}
			
		// show with message board
		$removeurl = rewrite_url($php_self, 'publicboard=' . $publicboard);
		if (isset($ilconfig['search_product_publicboards']) AND $ilconfig['search_product_publicboards'] == 1)
		$html .= (isset($ilance->GPC['publicboard']) AND $ilance->GPC['publicboard'] == '1')
			? '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options1" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_show_listings_with_active_public_message_boards}</strong></a></span></div>'
			: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options1" /></span><span class="blueonly"><a href="' . $php_self . '&amp;publicboard=1" onmouseover="rollovericon(\'unsel_options1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_show_listings_with_active_public_message_boards}</a></span></div>';
		
		if ($ilance->GPC['mode'] == 'product')
		{
			// show with images only
			if ($images == '1')
			{
				$removeurl = rewrite_url($php_self, 'images=1');
				$html .= (isset($ilance->GPC['images']) AND $ilance->GPC['images'] == '1')
					? '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options2" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_show_only_with_images}</strong></a></span></div>'
					: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options2" /></span><span class="blueonly"><a href="' . $php_self . '&amp;images=1" onmouseover="rollovericon(\'unsel_options2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_show_only_with_images}</a></span></div>';
				
				// show with no images only
				$html .= '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png" border="0" alt="" id="" name="sel_options7" /></span><span class="litegray"><strong>{_show_only_with_no_images}</strong></span></div>';
			}
			else if ($images == '-1')
			{
				$removeurl = rewrite_url($php_self, 'images=-1');
				$html .= '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png" border="0" alt="" id="" name="sel_options2" /></span><span class="litegray"><strong>{_show_only_with_images}</strong></span></div>';
				
				$html .= (isset($ilance->GPC['images']) AND $ilance->GPC['images'] == '-1')
					? '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options7" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options7\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options7\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_show_only_with_no_images}</strong></a></span></div>'
					: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options7" /></span><span class="blueonly"><a href="' . $php_self . '&amp;images=-1" onmouseover="rollovericon(\'unsel_options7\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options7\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_show_only_with_no_images}</a></span></div>';
			}
			else
			{
				$removeurl = rewrite_url($php_self, 'images=1');
				
				if (isset($ilconfig['search_product_images']) AND $ilconfig['search_product_images'] == 1)
				$html .= (isset($ilance->GPC['images']) AND $ilance->GPC['images'] == '1')
					? '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options2" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_show_only_with_images}</strong></a></span></div>'
					: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options2" /></span><span class="blueonly"><a href="' . $php_self . '&amp;images=1" onmouseover="rollovericon(\'unsel_options2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_show_only_with_images}</a></span></div>';
					
				if (isset($ilconfig['search_product_noimages']) AND $ilconfig['search_product_noimages'] == 1)
				$html .= (isset($ilance->GPC['images']) AND $ilance->GPC['images'] == '-1')
					? '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options7" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options7\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options7\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_show_only_with_no_images}</strong></a></span></div>'
					: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options7" /></span><span class="blueonly"><a href="' . $php_self . '&amp;images=-1" onmouseover="rollovericon(\'unsel_options7\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options7\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_show_only_with_no_images}</a></span></div>';
			}
				
			// show only with free shipping
			$removeurl = rewrite_url($php_self, 'freeshipping=' . $freeshipping);	

			if (isset($ilconfig['search_product_freeship']) AND $ilconfig['search_product_freeship'] == 1)
			$html .= (isset($ilance->GPC['freeshipping']) AND $ilance->GPC['freeshipping'] == '1')
				? '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options3" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_show_items_with_free_shipping}</strong></a></span></div>'
				: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options3" /></span><span class="blueonly"><a href="' . $php_self . '&amp;freeshipping=1" onmouseover="rollovericon(\'unsel_options3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_show_items_with_free_shipping}</a></span></div>';
			
			// show items listed as lots
			$removeurl = rewrite_url($php_self, 'listedaslots=' . $listedaslots);
			
			if (isset($ilconfig['search_product_lots']) AND $ilconfig['search_product_lots'] == 1)
			$html .= (isset($ilance->GPC['listedaslots']) AND $ilance->GPC['listedaslots'] == '1')
				? '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options4" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_show_items_listed_as_lots}</strong></a></span></div>'
				: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options4" /></span><span class="blueonly"><a href="' . $php_self . '&amp;listedaslots=1" onmouseover="rollovericon(\'unsel_options4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_show_items_listed_as_lots}</a></span></div>';
			
			if ($ilconfig['escrowsystem_enabled'] AND isset($ilconfig['search_product_escrow']) AND $ilconfig['search_product_escrow'] == 1)
			{
				// items being sold via escrow
				$removeurl = rewrite_url($php_self, 'escrow=' . $escrow);
				$html .= (isset($ilance->GPC['escrow']) AND $ilance->GPC['escrow'] == '1')
					? '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options5" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_show_items_that_sellers_require_secure_escrow}</strong></a></span></div>'
					: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options5" /></span><span class="blueonly"><a href="' . $php_self . '&amp;escrow=1" onmouseover="rollovericon(\'unsel_options5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_show_items_that_sellers_require_secure_escrow}</a></span></div>';
			}
			
			// include nonprofit selling items
			$removeurl = rewrite_url($php_self, 'donation=' . $donation);
			
			if (isset($ilconfig['search_product_donation']) AND $ilconfig['search_product_donation'] == 1)
			$html .= (isset($ilance->GPC['donation']) AND $ilance->GPC['donation'] == '1')
				? '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options6" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options6\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options6\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_show_donation_items}</strong></a></span></div>'
				: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options6" /></span><span class="blueonly"><a href="' . $php_self . '&amp;donation=1" onmouseover="rollovericon(\'unsel_options6\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options6\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_show_donation_items}</a></span></div>';
			
			// classified ads
			if ($ilconfig['enableclassifiedtab'])
			{
				$removeurl = rewrite_url($php_self, 'classifieds=' . $classifieds);
				$html .= (isset($ilance->GPC['classifieds']) AND $ilance->GPC['classifieds'] == '1')
					? '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_classifieds" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_classifieds\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_classifieds\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_show_only_classified_ads}</strong></a></span></div>'
					: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_classifieds" /></span><span class="blueonly"><a href="' . $php_self . '&amp;classifieds=1" onmouseover="rollovericon(\'unsel_classifieds\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_classifieds\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_show_only_classified_ads}</a></span></div>';
			
				// urgent ads
				$removeurl = rewrite_url($php_self, 'urgent=' . $urgent);
				$html .= (isset($ilance->GPC['urgent']) AND $ilance->GPC['urgent'] == '1')
					? '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_urgent" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_urgent\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_urgent\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_show_only_flagged_urgent}</strong></a></span></div>'
					: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_urgent" /></span><span class="blueonly"><a href="' . $php_self . '&amp;urgent=1" onmouseover="rollovericon(\'unsel_urgent\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_urgent\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_show_only_flagged_urgent}</a></span></div>';
			}
			
			($apihook = $ilance->api('print_options_product_end')) ? eval($apihook) : false;
		}
		else if ($ilance->GPC['mode'] == 'service')
		{
			if ($ilconfig['escrowsystem_enabled'] AND isset($ilconfig['search_work_escrow']) AND $ilconfig['search_work_escrow'] == 1)
			{
				// items being sold via escrow
				$removeurl = rewrite_url($php_self, 'escrow=' . $escrow);
				$html .= (isset($ilance->GPC['escrow']) AND $ilance->GPC['escrow'] == '1')
					? '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options5" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_show_projects_that_use_secure_escrow}</strong></a></span></div>'
					: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options5" /></span><span class="blueonly"><a href="' . $php_self . '&amp;escrow=1" onmouseover="rollovericon(\'unsel_options5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_show_projects_that_use_secure_escrow}</a></span></div>';
			}
			
			// show with specific budget range
			$removeurl = rewrite_url($php_self, 'budget=' . $budget);
			
			if (isset($ilconfig['search_work_nondisclosed']) AND $ilconfig['search_work_nondisclosed'] == 1)
			$html .= (isset($ilance->GPC['budget']) AND $ilance->GPC['budget'] == '1')
				? '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options6" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options6\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options6\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_only_show_projects_with_nondisclosed_budgets}</strong></a></span></div>'
				: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options6" /></span><span class="blueonly"><a href="' . $php_self . '&amp;budget=1" onmouseover="rollovericon(\'unsel_options6\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options6\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_only_show_projects_with_nondisclosed_budgets}</a></span></div>';
		}
		
		// completed listings
		$removeurl = rewrite_url($php_self, 'completed=' . $completed);
		
		if (isset($ilconfig['search_product_completed']) AND $ilconfig['search_product_completed'] == 1)
		$html .= (isset($ilance->GPC['completed']) AND $ilance->GPC['completed'] == '1')
			? '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_completed" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_completed\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_completed\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_show_completed_listings}</strong></a></span></div>'
			: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_completed" /></span><span class="blueonly"><a href="' . $php_self . '&amp;completed=1" onmouseover="rollovericon(\'unsel_completed\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_completed\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_show_completed_listings}</a></span></div>';
	}
	else if ($mode == 'experts')
	{
		$images = (isset($ilance->GPC['images']) AND $ilance->GPC['images'] > 0) ? intval($ilance->GPC['images']) : '';
		$isonline = (isset($ilance->GPC['isonline']) AND $ilance->GPC['isonline'] > 0) ? intval($ilance->GPC['isonline']) : '';
		$business = (isset($ilance->GPC['business']) AND $ilance->GPC['business'] > 0) ? intval($ilance->GPC['business']) : '';
		$individual = (isset($ilance->GPC['individual']) AND $ilance->GPC['individual'] > 0) ? intval($ilance->GPC['individual']) : '';
		$removeurlall = rewrite_url($php_self, 'images=' . $images);
		$removeurlall = rewrite_url($removeurlall, 'isonline=' . $isonline);
		$removeurlall = rewrite_url($removeurlall, 'business=' . $business);
		$removeurlall = rewrite_url($removeurlall, 'individual=' . $individual);
		$clear_options = $removeurlall;
		$clear_options_all = $removeurlall;	
		if (empty($images) AND empty($isonline))
		{
			$clear_options_all = '';
		}
		// make sure our php_self string contains a ?
		if (strrchr($php_self, "?") == false)
		{
			// we'll include our master variable which should rewrite our urls nice and friendly
			$php_self = $php_self . '?mode=' . $ilance->GPC['mode'];
		}
		// show only businesses
		$removeurl = rewrite_url($php_self, 'business=' . $business);
		$html .= (isset($ilance->GPC['business']) AND $ilance->GPC['business'] == '1')
			? '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_business" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_business\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_business\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_show_only_businesses}</strong></a></span></div>'
			: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_business" /></span><span class="blueonly"><a href="' . $php_self . '&amp;business=1" onmouseover="rollovericon(\'unsel_business\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_business\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_show_only_businesses}</a></span></div>';
		
		// show only individuals
		$removeurl = rewrite_url($php_self, 'individual=' . $individual);
		$html .= (isset($ilance->GPC['individual']) AND $ilance->GPC['individual'] == '1')
			? '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_individual" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_individual\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_individual\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_show_only_individuals}</strong></a></span></div>'
			: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_individual" /></span><span class="blueonly"><a href="' . $php_self . '&amp;individual=1" onmouseover="rollovericon(\'unsel_individual\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_individual\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_show_only_individuals}</a></span></div>';
		
		// showing only experts online right now
		$removeurl = rewrite_url($php_self, 'isonline=' . $isonline);
		$html .= (isset($ilance->GPC['isonline']) AND $ilance->GPC['isonline'] == '1')
			? '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options1" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_only_show_members_that_are_online_and_logged_in}</strong></a></span></div>'
			: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options1" /></span><span class="blueonly"><a href="' . $php_self . '&amp;isonline=1" onmouseover="rollovericon(\'unsel_options1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_only_show_members_that_are_online_and_logged_in}</a></span></div>';
			
		// show with images only
		$removeurl = rewrite_url($php_self, 'images=' . $images);
		$html .= (isset($ilance->GPC['images']) AND $ilance->GPC['images'] == '1')
			? '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_options2" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_options2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_options2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_show_only_profile_logos}</strong></a></span></div>'
			: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_options2" /></span><span class="blueonly"><a href="' . $php_self . '&amp;images=1" onmouseover="rollovericon(\'unsel_options2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_options2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_show_only_profile_logos}</a></span></div>';
	}
	return $html;
}

/*
* ...
*
* @param       
*
* @return      
*/
function print_currencies($dbtable = '', $fieldname = '', $selected = '', $maxcurrencies = 5, $sqlextra = '', $joinextra = '')
{
	global $ilance, $ilconfig, $phrase, $scriptpage, $php_self, $show, $clear_currencies, $clear_currencies_all, $ilcollapse;
	$html = '';
	$sql = $ilance->db->query("
                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "$fieldname, c.currency_id, c.currency_abbrev
                FROM " . DB_PREFIX . "$dbtable,
                " . DB_PREFIX . "currency AS c
		$joinextra
                WHERE $fieldname = c.currency_id
		$sqlextra
                GROUP by $fieldname
		ORDER BY currency_abbrev ASC
        ", 0, null, __FILE__, __LINE__);
	// make sure our php_self string contains a ?
	$selected = urldecode($selected);
	$php_self = (strrchr($php_self, "?") == false)
		? $php_self . '?sort=' . intval($ilance->GPC['sort'])
		: $php_self;
	$removeurl = rewrite_url($php_self, 'cur=' . $selected);
	$removeurlall = rewrite_url($php_self, 'cur=' . $selected);
	$clear_currencies_all = (empty($ilance->GPC['cur']))
		? ''
		: $removeurlall;
	// #### all currencies #################################################
	$html .= (empty($selected))
		? '<div style="padding-bottom:6px;padding-top:2px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png" border="0" alt="" id="" name="sel_allcurrencies" /></span><span class="blueonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_allcurrencies\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')" onmouseout="rollovericon(\'sel_allcurrencies\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')"><strong>{_any}</strong></a></span></div>'
		: '<div style="padding-bottom:6px;padding-top:2px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_allcurrencies" /></span><span class="blueonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'unsel_allcurrencies\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_allcurrencies\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_any}</a></span></div>';
	unset($removeurl);
	// #### handle currency url input ######################################
	$currentlyselected = '';
	$tmp = array();
	if ($selected != '' AND strrchr($selected, ',') == true)
	{
		$temp = explode(',', $selected);
		foreach ($temp AS $key => $value)
		{
			if ($value != '')
			{
				$tmp[] = intval($value);
			}
		}
		unset($temp);
	}
	else if ($selected != '' AND strrchr($selected, ',') == false)
	{
		$tmp[] = intval($selected);
	}
	foreach ($tmp AS $key => $value)
	{
		$currentlyselected .= ',' . $value;
	}
	$show_html_l = "<!--left begin--><div id=\"showmorecurrencies\" style=\"" . (!empty($ilcollapse["showmorecurrencies"]) ? $ilcollapse["showmorecurrencies"] : 'display: none;') . "\"><!--left end-->";
	$show_html_r = '<!--right begin--></div><div>
<span class="smaller blueonly">
	<a href="javascript:void(0)" onclick="toggle_more(\'showmorecurrencies\', \'moretext_currencies\', \'{_more}\', \'{_less}\', \'showmoreicon_currencies\')">
		<span id="moretext_currencies">' . (!empty($ilcollapse["showmorecurrencies"]) ? '{_less}' : '{_more}') . '</span>
	</a>
</span>
&nbsp;<img id="showmoreicon_currencies" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/' . (!empty($ilcollapse["showmorecurrencies"]) ? 'arrowup2.gif' : 'arrowdown2.gif') . '" border="0" alt="" />
</div><!--right end-->';
	$selected_html = $unselected_html = array();
	// #### loop through all currencies ####################################
	while ($crow = $ilance->db->fetch_array($sql, DB_ASSOC))
	{	
		// #### currently selected #####################################
		if ($selected != '' AND in_array($crow['currency_id'], $tmp))
		{
			$newcur = '';
			if (count($tmp) == 1)
			{
				$removeurl = rewrite_url($php_self, 'cur=' . $selected);
			}
			else if (count($tmp) > 1)
			{
				foreach ($tmp AS $key => $value)
				{
					if ($value != $crow['currency_id'])
					{
						$newcur .= $value . ',';
					}
				}
				if ($newcur != '')
				{
					$newcur = substr($newcur, 0, -1);
					$removeurl = rewrite_url($php_self, 'cur=' . $selected);
					$removeurl = (strrchr($removeurl, "?") == false)
						? $removeurl . '?cur=' . $newcur
						: $removeurl . '&amp;cur=' . $newcur;
				}
			}
			$selected_html[] .= '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_currencyid_' . $crow['currency_id'] . '" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_currencyid_' . $crow['currency_id'] . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_currencyid_' . $crow['currency_id'] . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . $crow['currency_abbrev'] . '</strong></a></span></div>';
		}
		// #### unselected #############################################
		else
		{
			$removeurl = rewrite_url($php_self, 'cur=' . $selected);
			$removeurl = (strrchr($removeurl, "?") == false)
				? $removeurl . '?sort=' . $ilance->GPC['sort']
				: $removeurl;
				
			$unselected_html[] .= '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_currencyid_' . $crow['currency_id'] . '" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;cur=' . $crow['currency_id'] . $currentlyselected . '" onmouseover="rollovericon(\'unsel_currencyid_' . $crow['currency_id'] . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_currencyid_' . $crow['currency_id'] . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . $crow['currency_abbrev'] . '</a></span></div>';
		}
		unset($removeurl);
	}
	unset($tmp);
	$count = 0;
	foreach($selected_html AS $key => $value)
	{
		$count++;
		$html .= ($count == $maxcurrencies + 1) ? $show_html_l . $value : $value;
	}
	foreach($unselected_html AS $key => $value)
	{
		$count++;
		$html .= ($count == $maxcurrencies + 1) ? $show_html_l . $value : $value;
	}
	$html .= ($count > $maxcurrencies) ? $show_html_r : '';
	return $html;
}

/*
* ...
*
* @param       
*
* @return      
*/
function fetch_region_title($region = '')
{
	global $ilance, $phrase;
	$region = str_replace('_', ' ', $region);
	$region = ucwords($region);
	$slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';
	$sql = $ilance->db->query("
		SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "region_" . $slng . " AS region
		FROM " . DB_PREFIX . "locations_regions
		GROUP BY region_" . $slng . "
		ORDER BY region_" . $slng . " ASC
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			if ($res['region'] == $region)
			{
				return $res['region'];
			}
		}
	}
	return false;
}

/*
* ...
*
* @param       
*
* @return      
*/
function fetch_country_ids_by_region($region = '')
{
	global $ilance, $phrase;
	$slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';
	$query = (mb_strtolower($region) == 'worldwide' OR empty($region)) ? "" : " AND r.region_" . $slng . " = '" . $ilance->db->escape_string($region) . "'";
	if (empty($query))
	{
		return false;
	}
	$ids = '';
	$sql = $ilance->db->query("
		SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "locationid
		FROM " . DB_PREFIX . "locations l
		LEFT JOIN " . DB_PREFIX . "locations_regions r ON (r.regionid = l.regionid)
		WHERE l.visible = '1'
		$query
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$ids .= $res['locationid'] . ',';
		}
	}
	$ids = (!empty($ids) AND strrchr($ids, ',')) ? substr($ids, 0, -1) : $ids;
	return $ids;
}

/*
* Function to print the bid range pulldown menu.
*
* @param       string         selected option value (if applicable)
* @param       string         fieldname
* @param       string         element id (id="")
* @param       string         display type (pulldown or links) (default pulldown)
*
* @return      string         Returns HTML representation of the pulldown or links menu
*/
function print_bid_range_pulldown($selected = '', $fieldname = 'bidrange', $id = '', $displaytype = 'pulldown')
{
        global $ilance, $ilconfig, $phrase, $php_self;
	$html = '';
	if ($displaytype == 'pulldown')
	{
		$html .= '<select name="' . $fieldname . '" id="' . $id . '" class="select">';
		$html .= (empty($selected)) ? '<option value="-1" selected="selected">' : '<option value="-1">';
		$html .= '{_any_number_of_bids_upper}</option><option value="4"';
		$html .= (isset($selected) AND $selected == '4') ? 'selected="selected"' : '';
		$html .= '>{_no_bids_placed}</option><option value="1"';
		$html .= (isset($selected) AND $selected == '1') ? 'selected="selected"' : '';
		$html .= '>{_less_than_10_upper}</option><option value="2"';
		$html .= (isset($selected) AND $selected == '2') ? 'selected="selected"' : '';
		$html .= '>{_between_10_and_20_upper}</option><option value="3"';
		$html .= (isset($selected) AND $selected == '3') ? 'selected="selected"' : '';
		$html .= '>{_more_than_20_upper}</option></select>';
	}
	else if ($displaytype == 'links')
	{
		$html .= '<div style="padding-top:2px"></div>';
		// make sure our php_self string contains a ?
		$php_self = (strrchr($php_self, "?") == false) ? $php_self . '?mode=' . $ilance->GPC['mode'] : $php_self;
		$bidrange = isset($ilance->GPC['bidrange']) AND !empty($ilance->GPC['bidrange']) ? intval($ilance->GPC['bidrange']) : '-1';
		
		// any number of bids
		$removeurl = rewrite_url($php_self, 'bidrange=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= ($selected == '-1' OR empty($ilance->GPC['bidrange']))
			? '<div style="padding-bottom:6px" class="gray"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png" border="0" alt="" id="" name="sel_bidrange" /></span><span class="blueonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_bidrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')" onmouseout="rollovericon(\'sel_bidrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')"><strong>{_any_number_of_bids_upper}</strong></a></span></div>'
			: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_bidrange" /></span><span class="blueonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'unsel_bidrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_bidrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_any_number_of_bids_upper}</a></span></div>';
		
		// no bids placed
		$removeurl = rewrite_url($php_self, 'bidrange=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['bidrange']) AND $ilance->GPC['bidrange'] == '4')
			? '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_bidrange4" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_bidrange4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_bidrange4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_no_bids_placed}</strong></a></span></div>'
			: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_bidrange4" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;bidrange=4" onmouseover="rollovericon(\'unsel_bidrange4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_bidrange4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_no_bids_placed}</a></span></div>';
			
		// less than 10 bids
		$removeurl = rewrite_url($php_self, 'bidrange=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['bidrange']) AND $ilance->GPC['bidrange'] == '1')
			? '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_bidrange1" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_bidrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_bidrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_less_than_10_upper}</strong></a></span></div>'
			: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_bidrange1" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;bidrange=1" onmouseover="rollovericon(\'unsel_bidrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_bidrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_less_than_10_upper}</a></span></div>';
			
		// between 10 and 20 bids
		$removeurl = rewrite_url($php_self, 'bidrange=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['bidrange']) AND $ilance->GPC['bidrange'] == '2')
			? '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_bidrange2" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_bidrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_bidrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_between_10_and_20_upper}</strong></a></span></div>'
			: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_bidrange2" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;bidrange=2" onmouseover="rollovericon(\'unsel_bidrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_bidrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_between_10_and_20_upper}</a></span></div>';
		
		// more than 20 bids
		$removeurl = rewrite_url($php_self, 'bidrange=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['bidrange']) AND $ilance->GPC['bidrange'] == '3')
			? '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_bidrange3" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_bidrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_bidrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_more_than_20_upper}</strong></a></span></div>'
			: '<div style="padding-bottom:6px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_bidrange3" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;bidrange=3" onmouseover="rollovericon(\'unsel_bidrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_bidrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_more_than_20_upper}</a></span></div>';
			
		unset($removeurl, $bidrange);
	}
        return $html;
}

/*
* Function to print the bid range pulldown menu.
*
* @param       string         selected option value (if applicable)
* @param       string         fieldname
* @param       string         element id (id="")
* @param       string         display type (pulldown or links) (default pulldown)
*
* @return      string         Returns HTML representation of the pulldown or links menu
*/
function print_color_pulldown($selected = '', $fieldname = 'color', $id = '', $displaytype = 'pulldown')
{
        global $ilance, $ilconfig, $phrase, $php_self;
	$html = '';
	if ($displaytype == 'pulldown')
	{
		$html .= '<select name="' . $fieldname . '" id="' . $id . '" class="select">';
		$html .= (empty($selected)) ? '<option value="-1" selected="selected">' : '<option value="-1">';
		$html .= '{_any_color}</option></select>';
	}
	else if ($displaytype == 'links')
	{
		$html .= '<div style="padding-top:4px"></div>';
		$php_self = (strrchr($php_self, "?") == false) ? $php_self . '?mode=' . $ilance->GPC['mode'] : $php_self;
		$color = $selected;
		$selectedcolors = explode(' ', $color);
		if (count($selectedcolors) == 0)
		{
			$selectedcolors = array();
		}
		$removeurl = rewrite_url($php_self, 'color=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= ($selected == '-1' OR empty($selected))
			? '<div style="padding-bottom:4px"><table border="0" cellpadding="0" cellspacing="1">
<tr>
    <td style="width:19px;height:19px;background-color:white;border:1px solid #ccc" onclick="top.location.href=\'' . $removeurl . '&amp;color=White\'" onmouseover="this.style.cursor=\'pointer\';" onmouseout="this.style.cursor=\'default\';" title="{_white}"></td>
    <td style="width:19px;height:19px;background-color:grey" onclick="top.location.href=\'' . $removeurl . '&amp;color=Grey\'" onmouseover="this.style.cursor=\'pointer\';" onmouseout="this.style.cursor=\'default\';" title="{_grey}"></td>
    <td style="width:19px;height:19px;background-color:black" onclick="top.location.href=\'' . $removeurl . '&amp;color=Black\'" onmouseover="this.style.cursor=\'pointer\';" onmouseout="this.style.cursor=\'default\';" title="{_black}"></td>
    <td style="width:19px;height:19px;background-color:brown" onclick="top.location.href=\'' . $removeurl . '&amp;color=Brown\'" onmouseover="this.style.cursor=\'pointer\';" onmouseout="this.style.cursor=\'default\';" title="{_brown}"></td>
    <td style="width:19px;height:19px;background-color:orange" onclick="top.location.href=\'' . $removeurl . '&amp;color=Orange\'" onmouseover="this.style.cursor=\'pointer\';" onmouseout="this.style.cursor=\'default\';" title="{_orange}"></td>
    <td style="width:19px;height:19px;background-color:yellow" onclick="top.location.href=\'' . $removeurl . '&amp;color=Yellow\'" onmouseover="this.style.cursor=\'pointer\';" onmouseout="this.style.cursor=\'default\';" title="{_yellow}"></td>
    <td style="width:19px;height:19px;background-color:blue" onclick="top.location.href=\'' . $removeurl . '&amp;color=Blue\'" onmouseover="this.style.cursor=\'pointer\';" onmouseout="this.style.cursor=\'default\';" title="{_blue}"></td>
    <td style="width:19px;height:19px;background-color:green" onclick="top.location.href=\'' . $removeurl . '&amp;color=Green\'" onmouseover="this.style.cursor=\'pointer\';" onmouseout="this.style.cursor=\'default\';" title="{_green}"></td>
    <td style="width:19px;height:19px;background-color:red" onclick="top.location.href=\'' . $removeurl . '&amp;color=Red\'" onmouseover="this.style.cursor=\'pointer\';" onmouseout="this.style.cursor=\'default\';" title="{_red}"></td>
    <td style="width:19px;height:19px;background-color:violet" onclick="top.location.href=\'' . $removeurl . '&amp;color=Violet\'" onmouseover="this.style.cursor=\'pointer\';" onmouseout="this.style.cursor=\'default\';" title="{_purple}"></td>
</tr>
</table></div>'
			: '<div style="padding-bottom:4px"><table border="0" cellpadding="0" cellspacing="1">
<tr>
    <td style="width:19px;height:19px;background-color:white;border:1px solid #ccc" onclick="top.location.href=\'' . (in_array('White', $selectedcolors) ? $removeurl : $removeurl . '&amp;color=White') . '\'" onmouseover="this.style.cursor=\'pointer\';" onmouseout="this.style.cursor=\'default\';" title="{_white}">' . (in_array('White', $selectedcolors) ? '<div style="height:19px;width:19px;overflow:hidden;border-radius: 75px;-moz-border-radius: 75px;-webkit-border-radius: 75px;border: 1px solid #ffffff;box-shadow: 0 0 3px black;-moz-box-shadow: 0 0 3px black;-webkit-box-shadow: 0 0 3px black;">&nbsp;</div>' : '') . '</td>
    <td style="width:19px;height:19px;background-color:grey" onclick="top.location.href=\'' . (in_array('Grey', $selectedcolors) ? $removeurl : $removeurl . '&amp;color=Grey') . '\'" onmouseover="this.style.cursor=\'pointer\';" onmouseout="this.style.cursor=\'default\';" title="{_grey}">' . (in_array('Grey', $selectedcolors) ? '<div style="height:19px;width:19px;overflow:hidden;border-radius: 75px;-moz-border-radius: 75px;-webkit-border-radius: 75px;border: 1px solid #ffffff;box-shadow: 0 0 3px black;-moz-box-shadow: 0 0 3px black;-webkit-box-shadow: 0 0 3px black;">&nbsp;</div>' : '') . '</td>
    <td style="width:19px;height:19px;background-color:black" onclick="top.location.href=\'' . (in_array('Black', $selectedcolors) ? $removeurl : $removeurl . '&amp;color=Black') . '\'" onmouseover="this.style.cursor=\'pointer\';" onmouseout="this.style.cursor=\'default\';" title="{_black}">' . (in_array('Black', $selectedcolors) ? '<div style="height:19px;width:19px;overflow:hidden;border-radius: 75px;-moz-border-radius: 75px;-webkit-border-radius: 75px;border: 1px solid #ffffff;box-shadow: 0 0 3px black;-moz-box-shadow: 0 0 3px black;-webkit-box-shadow: 0 0 3px black;">&nbsp;</div>' : '') . '</td>
    <td style="width:19px;height:19px;background-color:brown" onclick="top.location.href=\'' . (in_array('Brown', $selectedcolors) ? $removeurl : $removeurl . '&amp;color=Brown') . '\'" onmouseover="this.style.cursor=\'pointer\';" onmouseout="this.style.cursor=\'default\';" title="{_brown}">' . (in_array('Brown', $selectedcolors) ? '<div style="height:19px;width:19px;overflow:hidden;border-radius: 75px;-moz-border-radius: 75px;-webkit-border-radius: 75px;border: 1px solid #ffffff;box-shadow: 0 0 3px black;-moz-box-shadow: 0 0 3px black;-webkit-box-shadow: 0 0 3px black;">&nbsp;</div>' : '') . '</td>
    <td style="width:19px;height:19px;background-color:orange" onclick="top.location.href=\'' . (in_array('Orange', $selectedcolors) ? $removeurl : $removeurl . '&amp;color=Orange') . '\'" onmouseover="this.style.cursor=\'pointer\';" onmouseout="this.style.cursor=\'default\';" title="{_orange}">' . (in_array('Orange', $selectedcolors) ? '<div style="height:19px;width:19px;overflow:hidden;border-radius: 75px;-moz-border-radius: 75px;-webkit-border-radius: 75px;border: 1px solid #ffffff;box-shadow: 0 0 3px black;-moz-box-shadow: 0 0 3px black;-webkit-box-shadow: 0 0 3px black;">&nbsp;</div>' : '') . '</td>
    <td style="width:19px;height:19px;background-color:yellow" onclick="top.location.href=\'' . (in_array('Yellow', $selectedcolors) ? $removeurl : $removeurl . '&amp;color=Yellow') . '\'" onmouseover="this.style.cursor=\'pointer\';" onmouseout="this.style.cursor=\'default\';" title="{_yellow}">' . (in_array('Yellow', $selectedcolors) ? '<div style="height:19px;width:19px;overflow:hidden;border-radius: 75px;-moz-border-radius: 75px;-webkit-border-radius: 75px;border: 1px solid #ffffff;box-shadow: 0 0 3px black;-moz-box-shadow: 0 0 3px black;-webkit-box-shadow: 0 0 3px black;">&nbsp;</div>' : '') . '</td>
    <td style="width:19px;height:19px;background-color:blue" onclick="top.location.href=\'' . (in_array('Blue', $selectedcolors) ? $removeurl : $removeurl . '&amp;color=Blue') . '\'" onmouseover="this.style.cursor=\'pointer\';" onmouseout="this.style.cursor=\'default\';" title="{_blue}">' . (in_array('Blue', $selectedcolors) ? '<div style="height:19px;width:19px;overflow:hidden;border-radius: 75px;-moz-border-radius: 75px;-webkit-border-radius: 75px;border: 1px solid #ffffff;box-shadow: 0 0 3px black;-moz-box-shadow: 0 0 3px black;-webkit-box-shadow: 0 0 3px black;">&nbsp;</div>' : '') . '</td>
    <td style="width:19px;height:19px;background-color:green" onclick="top.location.href=\'' . (in_array('Green', $selectedcolors) ? $removeurl : $removeurl . '&amp;color=Green') . '\'" onmouseover="this.style.cursor=\'pointer\';" onmouseout="this.style.cursor=\'default\';" title="{_green}">' . (in_array('Green', $selectedcolors) ? '<div style="height:19px;width:19px;overflow:hidden;border-radius: 75px;-moz-border-radius: 75px;-webkit-border-radius: 75px;border: 1px solid #ffffff;box-shadow: 0 0 3px black;-moz-box-shadow: 0 0 3px black;-webkit-box-shadow: 0 0 3px black;">&nbsp;</div>' : '') . '</td>
    <td style="width:19px;height:19px;background-color:red" onclick="top.location.href=\'' . (in_array('Red', $selectedcolors) ? $removeurl : $removeurl . '&amp;color=Red') . '\'" onmouseover="this.style.cursor=\'pointer\';" onmouseout="this.style.cursor=\'default\';" title="{_red}">' . (in_array('Red', $selectedcolors) ? '<div style="height:19px;width:19px;overflow:hidden;border-radius: 75px;-moz-border-radius: 75px;-webkit-border-radius: 75px;border: 1px solid #ffffff;box-shadow: 0 0 3px black;-moz-box-shadow: 0 0 3px black;-webkit-box-shadow: 0 0 3px black;">&nbsp;</div>' : '') . '</td>
    <td style="width:19px;height:19px;background-color:violet" onclick="top.location.href=\'' . (in_array('Violet', $selectedcolors) ? $removeurl : $removeurl . '&amp;color=Violet') . '\'" onmouseover="this.style.cursor=\'pointer\';" onmouseout="this.style.cursor=\'default\';" title="{_purple}">' . (in_array('Violet', $selectedcolors) ? '<div style="height:19px;width:19px;overflow:hidden;border-radius: 75px;-moz-border-radius: 75px;-webkit-border-radius: 75px;border: 1px solid #ffffff;box-shadow: 0 0 3px black;-moz-box-shadow: 0 0 3px black;-webkit-box-shadow: 0 0 3px black;">&nbsp;</div>' : '') . '</td>
</tr>
</table></div>';
		
		unset($removeurl, $bidrange);
	}
        return $html;
}

/*
* Function to print the award range pulldown menu.
*
* @param       string         selected option value (if applicable)
* @param       string         fieldname
*
* @return      string         Returns HTML representation of the pulldown menu
*/
function print_award_range_pulldown($selected = '', $fieldname = 'projectrange', $id = '', $displaytype = 'pulldown')
{
        global $ilance, $ilconfig, $phrase, $php_self;
	if ($displaytype == 'pulldown')
	{
		$html = '<select name="' . $fieldname . '" id="' . $id . '" style="font-family: verdana">';
		$html .= (empty($selected)) ? '<option value="-1" selected="selected">{_any}</option>' : '<option value="-1">{_any}</option>';
		$html .= '<option value="1"';
		$html .= (isset($selected) AND $selected == '1') ? 'selected="selected"' : '';
		$html .= '>{_less_than_10_upper}</option>';
		$html .= '<option value="2"';
		$html .= (isset($selected) AND $selected == '2') ? 'selected="selected"' : '';
		$html .= '>{_between_10_and_20_upper}</option>';
		$html .= '<option value="3"';
		$html .= (isset($selected) AND $selected == '3') ? 'selected="selected"' : '';
		$html .= '>{_more_than_20_upper}</option></select>';	
	}
	else if ($displaytype == 'links')
	{
		$html = '';
		// make sure our php_self string contains a ?
		$php_self = (strrchr($php_self, "?") == false) ? $php_self . '?mode=' . $ilance->GPC['mode'] : $php_self;
		$projectrange = isset($ilance->GPC['projectrange']) AND !empty($ilance->GPC['projectrange']) ? intval($ilance->GPC['projectrange']) : '-1';
		
		// any number of bids
		$removeurl = rewrite_url($php_self, 'projectrange=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= ($selected == '-1' OR empty($ilance->GPC['projectrange']))
			? '<div style="padding-bottom:4px" class="gray"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png" border="0" alt="" id="" name="sel_projectrange" /></span><span class="blueonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_projectrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')" onmouseout="rollovericon(\'sel_projectrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')"><strong>{_any}</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_projectrange" /></span><span class="blueonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'unsel_projectrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_projectrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_any}</a></span></div>';
			
		// less than 10 bids
		$removeurl = rewrite_url($php_self, 'projectrange=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['projectrange']) AND $ilance->GPC['projectrange'] == '1')
			? '<div style="padding-bottom:4px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_projectrange1" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_projectrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_projectrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_less_than_10_upper}</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_projectrange1" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;projectrange=1" onmouseover="rollovericon(\'unsel_projectrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_projectrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_less_than_10_upper}</a></span></div>';
			
		// between 10 and 20 bids
		$removeurl = rewrite_url($php_self, 'projectrange=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['projectrange']) AND $ilance->GPC['projectrange'] == '2')
			? '<div style="padding-bottom:4px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_projectrange2" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_projectrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_projectrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_between_10_and_20_upper}</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_projectrange2" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;projectrange=2" onmouseover="rollovericon(\'unsel_projectrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_projectrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_between_10_and_20_upper}</a></span></div>';
		
		// more than 20 bids
		$removeurl = rewrite_url($php_self, 'projectrange=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['projectrange']) AND $ilance->GPC['projectrange'] == '3')
			? '<div style="padding-bottom:4px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_projectrange3" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_projectrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_projectrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_more_than_20_upper}</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_projectrange3" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;projectrange=3" onmouseover="rollovericon(\'unsel_projectrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_projectrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_more_than_20_upper}</a></span></div>';
			
		unset($removeurl, $bidrange);
	}
        return $html;
}

/*
* Function to print the rating range pulldown menu.
*
* @param       string         selected option value (if applicable)
* @param       string         fieldname
* @param       string         fieldname id
* @param       string         display type [pulldown or links] (default pulldown)
*
* @return      string         Returns HTML representation of the pulldown menu
*/
function print_rating_range_pulldown($selected = '', $fieldname = 'rating', $id = 'rating', $displaytype = 'pulldown')
{
        global $ilance, $ilconfig, $phrase, $php_self;
	if ($displaytype == 'pulldown')
	{
		$html = '<select name="' . $fieldname . '" id="' . $id . '" style="font-family: verdana">';
		$html .= empty($selected) ? '<option value="0" selected="selected">{_all_ratings_upper}</option>' : '<option value="0">{_all_ratings_upper}</option>';
		$html .= '<option value="5"';
		$html .= (isset($selected) AND $selected == 5) ? 'selected="selected"' : '';
		$html .= '>{_five_stars_upper}</option>';
		$html .= '<option value="4"';
		$html .= (isset($selected) AND $selected == 4) ? 'selected="selected"' : '';
		$html .= '>{_at_least_four_stars_upper}</option>';
		$html .= '<option value="3"';
		$html .= (isset($selected) AND $selected == 3) ? 'selected="selected"' : '';
		$html .= '>{_at_least_three_stars_upper}</option>';
		$html .= '<option value="2"';
		$html .= (isset($selected) AND $selected == 2) ? 'selected="selected"' : '';
		$html .= '>{_at_least_two_stars_upper}</option>';
		$html .= '<option value="1"';
		$html .= (isset($selected) AND $selected == 1) ? 'selected="selected"' : '';
		$html .= '>{_one_star_upper}</option></select>';
	}
	else if ($displaytype == 'links')
	{
		$html = '';
		// make sure our php_self string contains a ?
		$php_self = (strrchr($php_self, "?") == false) ? $php_self . '?mode=' . $ilance->GPC['mode'] : $php_self;
		$rating = isset($ilance->GPC['rating']) AND !empty($ilance->GPC['rating']) ? intval($ilance->GPC['rating']) : '0';
		
		// any ratings
		$removeurl = rewrite_url($php_self, 'rating=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= ($selected == '0' OR empty($ilance->GPC['rating']))
			? '<div style="padding-bottom:4px" class="gray"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png" border="0" alt="" id="" name="sel_ratingrange" /></span><span class="blueonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_ratingrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')" onmouseout="rollovericon(\'sel_ratingrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')"><strong>{_all_ratings_upper}</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_ratingrange" /></span><span class="blueonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'unsel_ratingrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_ratingrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_all_ratings_upper}</a></span></div>';
			
		// at least 1 stars
		$removeurl = rewrite_url($php_self, 'rating=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['rating']) AND $ilance->GPC['rating'] == '1')
			? '<div style="padding-bottom:4px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_ratingrange1" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_ratingrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_ratingrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_one_star_upper}</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_ratingrange1" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;rating=1" onmouseover="rollovericon(\'unsel_ratingrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_ratingrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_one_star_upper}</a></span></div>';
			
		// at least 2 stars
		$removeurl = rewrite_url($php_self, 'rating=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['rating']) AND $ilance->GPC['rating'] == '2')
			? '<div style="padding-bottom:4px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_ratingrange2" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_ratingrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_ratingrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_at_least_two_stars_upper}</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_ratingrange2" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;rating=2" onmouseover="rollovericon(\'unsel_ratingrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_ratingrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_at_least_two_stars_upper}</a></span></div>';
		
		// at least 3 stars
		$removeurl = rewrite_url($php_self, 'rating=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['rating']) AND $ilance->GPC['rating'] == '3')
			? '<div style="padding-bottom:4px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_ratingrange3" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_ratingrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_ratingrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_at_least_three_stars_upper}</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_ratingrange3" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;rating=3" onmouseover="rollovericon(\'unsel_ratingrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_ratingrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_at_least_three_stars_upper}</a></span></div>';
	
		// at least 4 stars
		$removeurl = rewrite_url($php_self, 'rating=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['rating']) AND $ilance->GPC['rating'] == '4')
			? '<div style="padding-bottom:4px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_ratingrange4" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_ratingrange4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_ratingrange4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_at_least_four_stars_upper}</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_ratingrange4" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;rating=4" onmouseover="rollovericon(\'unsel_ratingrange4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_ratingrange4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_at_least_four_stars_upper}</a></span></div>';
		
		// at least 5 stars
		$removeurl = rewrite_url($php_self, 'rating=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['rating']) AND $ilance->GPC['rating'] == '5')
			? '<div style="padding-bottom:4px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_ratingrange5" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_ratingrange5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_ratingrange5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_five_stars_upper}</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_ratingrange5" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;rating=5" onmouseover="rollovericon(\'unsel_ratingrange5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_ratingrange5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_five_stars_upper}</a></span></div>';
			
		unset($removeurl, $rating);
	}
        return $html;
}

/*
* ...
*
* @param       
*
* @return      
*/
function print_feedback_range_pulldown($selected = '', $fieldname = 'feedback', $id = 'feedback', $displaytype = 'pulldown')
{
        global $ilance, $ilconfig, $phrase, $php_self;
	if ($displaytype == 'pulldown')
	{
		$html = '<select name="' . $fieldname . '" id="' . $id . '" style="font-family: verdana">';
		$html .= empty($selected) ? '<option value="0" selected="selected">{_all}</option>' : '<option value="0">{_all}</option>';
		$html .= '<option value="5"';
		$html .= (isset($selected) AND $selected == 5) ? 'selected="selected"' : '';
		$html .= '>{_above_95_positive}</option>';
		$html .= '<option value="4"';
		$html .= (isset($selected) AND $selected == 4) ? 'selected="selected"' : '';
		$html .= '>{_above_90_positive}</option>';
		$html .= '<option value="3"';
		$html .= (isset($selected) AND $selected == 3) ? 'selected="selected"' : '';
		$html .= '>{_above_85_positive}</option>';
		$html .= '<option value="2"';
		$html .= (isset($selected) AND $selected == 2) ? 'selected="selected"' : '';
		$html .= '>{_above_75_positive}</option>';
		$html .= '<option value="1"';
		$html .= (isset($selected) AND $selected == 1) ? 'selected="selected"' : '';
		$html .= '>{_above_50_positive}</option></select>';
	}
	else if ($displaytype == 'links')
	{
		$html = '';
		// make sure our php_self string contains a ?
		$php_self = (strrchr($php_self, "?") == false) ? $php_self . '?mode=' . $ilance->GPC['mode'] : $php_self;
		$feedback = isset($ilance->GPC['feedback']) AND !empty($ilance->GPC['feedback']) ? intval($ilance->GPC['feedback']) : '0';
		
		// any feedback rating
		$removeurl = rewrite_url($php_self, 'feedback=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= ($selected == '0' OR empty($ilance->GPC['feedback']))
			? '<div style="padding-bottom:4px" class="gray"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png" border="0" alt="" id="" name="sel_feedbackrange" /></span><span class="blueonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_feedbackrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')" onmouseout="rollovericon(\'sel_feedbackrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedinclude.png\')"><strong>{_any}</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_feedbackrange" /></span><span class="blueonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'unsel_feedbackrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_feedbackrange\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_any}</a></span></div>';
			
		$removeurl = rewrite_url($php_self, 'feedback=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['feedback']) AND $ilance->GPC['feedback'] == '1')
			? '<div style="padding-bottom:4px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_feedbackrange1" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_feedbackrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_feedbackrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_above_50_positive}</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_feedbackrange1" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;feedback=1" onmouseover="rollovericon(\'unsel_feedbackrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_feedbackrange1\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_above_50_positive}</a></span></div>';
		
		$removeurl = rewrite_url($php_self, 'feedback=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['feedback']) AND $ilance->GPC['feedback'] == '2')
			? '<div style="padding-bottom:4px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_feedbackrange2" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_feedbackrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_feedbackrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_above_75_positive}</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_feedbackrange2" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;feedback=2" onmouseover="rollovericon(\'unsel_feedbackrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_feedbackrange2\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_above_75_positive}</a></span></div>';
			
		$removeurl = rewrite_url($php_self, 'feedback=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['feedback']) AND $ilance->GPC['feedback'] == '3')
			? '<div style="padding-bottom:4px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_feedbackrange3" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_feedbackrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_feedbackrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_above_85_positive}</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_feedbackrange3" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;feedback=3" onmouseover="rollovericon(\'unsel_feedbackrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_feedbackrange3\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_above_85_positive}</a></span></div>';
		
		$removeurl = rewrite_url($php_self, 'feedback=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['feedback']) AND $ilance->GPC['feedback'] == '4')
			? '<div style="padding-bottom:4px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_feedbackrange4" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_feedbackrange4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_feedbackrange4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_above_90_positive}</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_feedbackrange4" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;feedback=4" onmouseover="rollovericon(\'unsel_feedbackrange4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_feedbackrange4\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_above_90_positive}</a></span></div>';
		
		$removeurl = rewrite_url($php_self, 'feedback=' . $selected);
		$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
		$html .= (isset($ilance->GPC['feedback']) AND $ilance->GPC['feedback'] == '5')
			? '<div style="padding-bottom:4px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_feedbackrange5" /></span><span class="blackonly"><a href="' . $removeurl . '" onmouseover="rollovericon(\'sel_feedbackrange5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onmouseout="rollovericon(\'sel_feedbackrange5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>{_above_95_positive}</strong></a></span></div>'
			: '<div style="padding-bottom:4px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px; padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_feedbackrange5" /></span><span class="blueonly"><a href="' . $removeurl . '&amp;feedback=5" onmouseover="rollovericon(\'unsel_feedbackrange5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onmouseout="rollovericon(\'unsel_feedbackrange5\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">{_above_95_positive}</a></span></div>';
			
		unset($removeurl, $rating);
	}
        return $html;
}

/*
* Function for fetching the state date / end date SQL condition for the search system.
*
* @param       integer        filter that is selected (-1 = any date), 1 = 1 hour, 2 = 2 hours, etc.
* @param       string         MySQL function to use (DATEADD, DATESUB), etc
* @param       string         field name in the database table to use
* @param       string         operator (>, <, =, etc)
*
* @return      string         Valid SQL condition code to include in main SQL code to parse
*/
function fetch_startend_sql($endstart_filter, $mysqlfunction, $field, $operator)
{
	global $ilance;
        $sql = '';
	switch ($endstart_filter)
	{
		case '-1':
                {
                        $sql = "";
                        break;
                }	    
		case '1':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL \"01:00\" HOUR_MINUTE) ";
                        break;
                }	    
		case '2':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL \"02:00\" HOUR_MINUTE) ";
                        break;
                }	    
		case '3':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL \"03:00\" HOUR_MINUTE) ";
                        break;
                }	    
		case '4':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL \"04:00\" HOUR_MINUTE) ";
                        break;
                }	    
		case '5':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL \"05:00\" HOUR_MINUTE) ";
                        break;
                }	    
		case '6':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL \"12:00\" HOUR_MINUTE) ";
                        break;
                }	    
		case '7':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL \"24:00\" HOUR_MINUTE) ";
                        break;
                }	    
		case '8':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL 2 DAY) ";
                        break;
                }	    
		case '9':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL 3 DAY) ";
                        break;
                }	    
		case '10':
                {
                	$sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL 4 DAY) ";
                	break;
                }	    
		case '11':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL 5 DAY) ";
                        break;
                }	    
		case '12':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL 6 DAY) ";
                        break;
                }	    
		case '13':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL 7 DAY) ";
                        break;
                }	    
		case '14':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL 14 DAY) ";
                        break;
                }	    
		case '15':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL 1 MONTH) ";
                        break;
                }
                case '16':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL 2 MONTH) ";
                        break;
                }
                case '17':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL 3 MONTH) ";
                        break;
                }
                case '18':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL 6 MONTH) ";
                        break;
                }
                case '19':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL 1 YEAR) ";
                        break;
                }
		case '20':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL 2 YEAR) ";
                        break;
                }
		case '21':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETIME24H . "', INTERVAL 3 YEAR) ";
                        break;
                }
	}
	return $sql;
}

/*
* Function for fetching the state date / end date phrase.
*
* @param       integer        filter that is selected (-1 = any date), 1 = 1 hour, 2 = 2 hours, etc.
*
* @return      string         HTML representation of the question title
*/
function fetch_startend_phrase($endstart_filter)
{
	global $ilance, $phrase;
	switch ($endstart_filter)
	{
		case '-1':
		$sql = '{_any_date}';
		break;
	    
		case '1':
		$sql = '1 {_hour}';
		break;
	    
		case '2':
		$sql = '2 {_hours}';
		break;
	    
		case '3':
		$sql = '3 {_hours}';
		break;
	    
		case '4':
		$sql = '4 {_hours}';
		break;
	    
		case '5':
		$sql = '5 {_hours}';
		break;
	    
		case '6':
		$sql = '12 {_hours}';
		break;
	    
		case '7':
		$sql = '24 {_hours}';
		break;
	    
		case '8':
		$sql = '2 {_days}';
		break;
	    
		case '9':
		$sql = '3 {_days}';
		break;
	    
		case '10':
		$sql = '4 {_days}';
		break;
	    
		case '11':
		$sql = '5 {_days}';
		break;
	    
		case '12':
		$sql = '6 {_days}';
		break;
	    
		case '13':
		$sql = '7 {_days}';
		break;
	    
		case '14':
		$sql = '2 {_weeks}';
		break;
	    
		case '15':
		$sql = '1 {_month}';
		break;
	}
	return $sql;
}


/*
* Function to update default search options for a particular user who is registered and logged in.  This function will also update the existing session of the
* logged in user so it's realtime.
*
* @param        integer      user id
* @param        array        array with default search options
*
* @return	nothing
*/
function update_default_searchoptions($userid = 0, $defaultoptions = '')
{
        global $ilance;
        if (isset($userid) AND $userid > 0)
        {
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "users
                        SET searchoptions = '" . $ilance->db->escape_string($defaultoptions) . "'
                        WHERE user_id = '" . intval($userid) . "'
                ");
        }
        $_SESSION['ilancedata']['user']['searchoptions'] = $defaultoptions;
}

/*
* Function to update default search options for all guests and visitors connecting to the marketplace
*
* @param        integer      user id
* @param        array        array with default search options
*
* @return	nothing
*/
function update_default_searchoptions_guests($defaultoptions = '')
{
        global $ilance;
        if (isset($defaultoptions))
        {
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "configuration
                        SET value = '" . $ilance->db->escape_string($defaultoptions) . "'
                        WHERE name = 'searchdefaultcolumns'
                ");
        }
}

/*
* Function to update default search options for all members in the system
*
* @param        array        array with default search options
*
* @return	nothing
*/
function update_default_searchoptions_users($defaultoptions = '')
{
        global $ilance;
        if (isset($defaultoptions))
        {
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "users
                        SET searchoptions = '" . $ilance->db->escape_string($defaultoptions) . "'
                ");
        }
}

/*
* Function to print the per-page search option logic
*
* @param        string        css class to use
*
* @return	string        Returns HTML per page pulldown menu
*/
function print_perpage_searchoption($class = '')
{
        global $phrase;
        if (!empty($_SESSION['ilancedata']['user']['searchoptions']))
        {
                $pptemp = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                $perpagevalue = (int)$pptemp['perpage'];
                $perpage = '<select name="perpage" class="' . $class . '">
<optgroup label="{_list}">
<option value="5" ' . (($perpagevalue == 5) ? 'selected="selected"' : '') . '>5</option>
<option value="10" ' . (($perpagevalue == 10) ? 'selected="selected"' : '') . '>10</option>
<option value="25" ' . (($perpagevalue == 25) ? 'selected="selected"' : '') . '>25</option>
<option value="50" ' . (($perpagevalue == 50) ? 'selected="selected"' : '') . '>50</option>
<option value="100" ' . (($perpagevalue == 100) ? 'selected="selected"' : '') . '>100</option>
<option value="200" ' . (($perpagevalue == 200) ? 'selected="selected"' : '') . '>200</option>
</optgroup>
<optgroup label="{_gallery}">
<option value="4" ' . (($perpagevalue == 4) ? 'selected="selected"' : '') . '>4</option>
<option value="12" ' . (($perpagevalue == 12) ? 'selected="selected"' : '') . '>12</option>
<option value="24" ' . (($perpagevalue == 24) ? 'selected="selected"' : '') . '>24</option>
<option value="48" ' . (($perpagevalue == 48) ? 'selected="selected"' : '') . '>48</option>
<option value="96" ' . (($perpagevalue == 96) ? 'selected="selected"' : '') . '>96</option>
<option value="192" ' . (($perpagevalue == 192) ? 'selected="selected"' : '') . '>192</option>
</optgroup>
</select>';
        }
        else
        {
                $perpage = '<select name="perpage" class="' . $class . '">
<option value="5">5</option>
<option value="10" selected="selected">10</option>
<option value="25">25</option>
<option value="50">50</option>
<option value="100">100</option>
<option value="200">200</option>
</select>';
        }
        return $perpage;
}

/*
* Function to print the per-page search option logic
*
* @param        string        css class to use
*
* @return	string        Returns HTML per page pulldown menu
*/
function print_colsperrow_searchoption($class = '')
{
        global $phrase;
        if (!empty($_SESSION['ilancedata']['user']['searchoptions']))
        {
                $pptemp = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                $colsperrowvalue = isset($pptemp['colsperrow']) ? (int)$pptemp['colsperrow'] : 4;
                $colsperrow = '<select name="colsperrow" id="colsperrow" class="' . $class . '">
<optgroup label="{_gallery}">
<option value="4" ' . (($colsperrowvalue == 4) ? 'selected="selected"' : '') . '>4</option>
</optgroup>
</select>';
        }
        else
        {
                $colsperrow = '<select name="colsperrow" id="colsperrow" class="' . $class . '">
<option value="4" selected="selected">4</option>
</select>';
        }
        return $colsperrow;
}

/*
* ...
*
* @param       
*
* @return      
*/
function print_checkbox_status($cbname)
{
        $cb = '';
        if (!empty($_SESSION['ilancedata']['user']['searchoptions']))
        {
                $cbtemp = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                if (isset($cbtemp[$cbname]))
                {
                        $cb = $cbtemp[$cbname];
                }
                if (isset($cb) AND $cb == 'true')
                {
                        $cb = 'checked="checked"';
                }
                else if (isset($cb) AND $cb == 'false')
                {
                        $cb = '';        
                }
        }
        return $cb;        
}

/*
* ...
*
* @param       
*
* @return      
*/
function print_time_static_radiobox_status()
{
        $rb = 'checked="checked"';
        if (!empty($_SESSION['ilancedata']['user']['searchoptions']))
        {
                $rbtemp = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                if (isset($rbtemp['showtimeas']))
                {
                        $rb = $rbtemp['showtimeas'];
                        if (isset($rb) AND $rb == 'static')
                        {
                                $rb = 'checked="checked"';
                        }
                }
        }
        return $rb;
}

/*
* ...
*
* @param       
*
* @return      
*/
function print_list_gallery_radiobox_status()
{
        $rb = '';
        if (!empty($_SESSION['ilancedata']['user']['searchoptions']))
        {
                $rbtemp = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                if (isset($rbtemp['list']))
                {
                        $rb = $rbtemp['list'];
                }
                if (isset($rb) AND $rb == 'gallery')
                {
                        $rb = 'checked="checked"';
                }
                else
                {
                        $rb = '';
                }
        }
        return $rb;
}

/*
* ...
*
* @param       
*
* @return      
*/
function print_list_list_radiobox_status()
{
        $rb = 'checked="checked"';
        if (!empty($_SESSION['ilancedata']['user']['searchoptions']))
        {
                $rbtemp = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                if (isset($rbtemp['list']))
                {
                        $rb = $rbtemp['list'];
                        if (isset($rb) AND $rb == 'list')
                        {
                                $rb = 'checked="checked"';
                        }
                }
        }
        return $rb;
}

/*
* Function to handle the default display order used within the main ILance search system.
*
* @param       string         listing mode (experts, listings; default `listings`)
*
* @return      array          Returns formatted array with proper key => value pair array
*/
function sortable_array_handler($mode = 'listings')
{
        global $ilance, $ilconfig, $show;
        // #### defaults #######################################################
        $array = array(
                // time_ending_soonest
                '01' => array(
                        'field' => 'p.date_end',
                        'sort' => 'ASC',
                        'extra' => ''
                ),
                // time_newly_listed
                '02' => array(
                        'field' => 'p.date_starts',
                        'sort' => 'DESC',
                        'extra' => ''
                ),
                // price_lowest_first
                '11' => array(
                        'field' => 'p.currentprice',
                        'sort' => 'ASC',
                        'extra' => ''
                ),
                // price_highest_first
                '12' => array(
                        'field' => 'p.currentprice',
                        'sort' => 'DESC',
                        'extra' => ''
                ),
                // bids_sort_up
                '21' => array(
                        'field' => 'p.bids',
                        'sort' => 'ASC',
                        'extra' => ''
                ),
                // bids_sort_down
                '22' => array(
                        'field' => 'p.bids',
                        'sort' => 'DESC',
                        'extra' => ''
                ),
                // category
                '31' => array(
                        'field' => 'p.cid',
                        'sort' => 'ASC',
                        'extra' => ''
                ),
                // category
                '32' => array(
                        'field' => 'p.cid',
                        'sort' => 'DESC',
                        'extra' => ''
                ),
                // feedback_lowest_first
                '41' => array(
                        'field' => 'u.feedback',
                        'sort' => 'ASC',
                        'extra' => ''
                ),
                // feedback_highest_first
                '42' => array(
                        'field' => 'u.feedback',
                        'sort' => 'DESC',
                        'extra' => ''
                ),
                // rated_lowest_first
                '51' => array(
                        'field' => 'u.rating',
                        'sort' => 'ASC',
                        'extra' => ''
                ),
                // rated_highest_first
                '52' => array(
                        'field' => 'u.rating',
                        'sort' => 'DESC',
                        'extra' => ''
                ),
                // expert_sort_up
                '61' => array(
                        'field' => 'u.username',
                        'sort' => 'ASC',
                        'extra' => ''
                ),
                // expert_sort_down
                '62' => array(
                        'field' => 'u.username',
                        'sort' => 'DESC',
                        'extra' => ''
                ),
                // city_sort_up
                '71' => array(
                        'field' => 'u.city',
                        'sort' => 'ASC',
                        'extra' => ''
                ),
                // city_sort_down
                '72' => array(
                        'field' => 'u.city',
                        'sort' => 'DESC',
                        'extra' => ''
                ),
                // country_sort_up
                '81' => array(
                        'field' => 'u.country',
                        'sort' => 'ASC',
                        'extra' => ''
                ),
                // country_sort_down
                '82' => array(
                        'field' => 'u.country',
                        'sort' => 'DESC',
                        'extra' => ''
                ),
                // zip_sort_up
                '91' => array(
                        'field' => 'u.zip_code',
                        'sort' => 'ASC',
                        'extra' => ''
                ),
                // zip_sort_down
                '92' => array(
                        'field' => 'u.zip_code',
                        'sort' => 'DESC',
                        'extra' => ''
                ),
                // earnings_lowest_first
                '101' => array(
                        'field' => 'u.income_reported',
                        'sort' => 'ASC',
                        'extra' => ''
                ),
                // earnings_highest_first
                '102' => array(
                        'field' => 'u.income_reported',
                        'sort' => 'DESC',
                        'extra' => ''
                ),
                // awards_lowest_first
                '111' => array(
                        'field' => 'u.serviceawards',
                        'sort' => 'ASC',
                        'extra' => ''
                ),
                // awards_highest_first
                '112' => array(
                        'field' => 'u.serviceawards',
                        'sort' => 'DESC',
                        'extra' => ''
                ),
                // distance
                '121' => array (
                        'field' => 'distance',
                        'sort' => 'ASC',
                        'extra' => ''
                ),
                '122' => array(
                        'field' => 'distance',
                        'sort' => 'DESC',
                        'extra' => ''
                ),
		// relevance lowest first
                '123' => array (
                        'field' => 'relevance',
                        'sort' => 'ASC',
                        'extra' => ''
                ),
		// relevance highest first
                '124' => array(
                        'field' => 'relevance',
                        'sort' => 'DESC',
                        'extra' => ''
                ),
        );
        if ($mode == 'listings')
        {
                $array = array(
                        // time_ending_soonest
                        '01' => array(
                                'field' => 'p.date_end',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // time_newly_listed
                        '02' => array(
                                'field' => 'p.date_starts',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // price_lowest_first
                        '11' => array(
                                'field' => 'p.currentprice',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // price_highest_first
                        '12' => array(
                                'field' => 'p.currentprice',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // bids_sort_up
                        '21' => array(
                                'field' => 'p.bids',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // bids_sort_down
                        '22' => array(
                                'field' => 'p.bids',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // category
                        '31' => array(
                                'field' => 'p.cid',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // category
                        '32' => array(
                                'field' => 'p.cid',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // feedback_lowest_first
                        '41' => array(
                                'field' => 'p.date_end',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // feedback_highest_first
                        '42' => array(
                                'field' => 'p.date_end',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // rated_lowest_first
                        '51' => array(
                                'field' => 'p.date_end',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // rated_highest_first
                        '52' => array(
                                'field' => 'p.date_end',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // expert_sort_up
                        '61' => array(
                                'field' => 'p.date_end',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // expert_sort_down
                        '62' => array(
                                'field' => 'p.date_end',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // city_sort_up
                        '71' => array(
                                'field' => 'p.city',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // city_sort_down
                        '72' => array(
                                'field' => 'p.city',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // country_sort_up
                        '81' => array(
                                'field' => 'p.countryid',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // country_sort_down
                        '82' => array(
                                'field' => 'p.countryid',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // zip_sort_up
                        '91' => array(
                                'field' => 'p.zipcode',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // zip_sort_down
                        '92' => array(
                                'field' => 'p.zipcode',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // earnings_lowest_first
                        '101' => array(
                                'field' => 'p.date_end',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // earnings_highest_first
                        '102' => array(
                                'field' => 'p.date_end',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // awards_lowest_first
                        '111' => array(
                                'field' => 'p.date_end',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // awards_highest_first
                        '112' => array(
                                'field' => 'p.date_end',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // distance
                        '121' => array (
                                'field' => 'distance',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        '122' => array(
                                'field' => 'distance',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
			// relevance lowest first
			'123' => array (
				'field' => 'relevance',
				'sort' => 'ASC',
				'extra' => ''
			),
			// relevance highest first
			'124' => array(
				'field' => 'relevance',
				'sort' => 'DESC',
				'extra' => ''
			),
                );        
        }
        else if ($mode == 'experts')
        {
                $array = array(
                        // time_ending_soonest
                        '01' => array(
                                'field' => 'u.income_reported',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // time_newly_listed
                        '02' => array(
                                'field' => 'u.income_reported',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // price_lowest_first
                        '11' => array(
                                'field' => 'u.income_reported',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // price_highest_first
                        '12' => array(
                                'field' => 'u.income_reported',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // bids_sort_up
                        '21' => array(
                                'field' => 'u.income_reported',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // bids_sort_down
                        '22' => array(
                                'field' => 'u.income_reported',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // category
                        '31' => array(
                                'field' => 'p.cid',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // category
                        '32' => array(
                                'field' => 'p.cid',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // feedback_lowest_first
                        '41' => array(
                                'field' => 'u.feedback',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // feedback_highest_first
                        '42' => array(
                                'field' => 'u.feedback',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // rated_lowest_first
                        '51' => array(
                                'field' => 'u.rating',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // rated_highest_first
                        '52' => array(
                                'field' => 'u.rating',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // expert_sort_up
                        '61' => array(
                                'field' => 'u.username',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // expert_sort_down
                        '62' => array(
                                'field' => 'u.username',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // city_sort_up
                        '71' => array(
                                'field' => 'u.city',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // city_sort_down
                        '72' => array(
                                'field' => 'u.city',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // country_sort_up
                        '81' => array(
                                'field' => 'u.country',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // country_sort_down
                        '82' => array(
                                'field' => 'u.country',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // zip_sort_up
                        '91' => array(
                                'field' => 'u.zip_code',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // zip_sort_down
                        '92' => array(
                                'field' => 'u.zip_code',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // earnings_lowest_first
                        '101' => array(
                                'field' => 'u.income_reported',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // earnings_highest_first
                        '102' => array(
                                'field' => 'u.income_reported',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // awards_lowest_first
                        '111' => array(
                                'field' => 'u.serviceawards',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        // awards_highest_first
                        '112' => array(
                                'field' => 'u.serviceawards',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
                        // distance
                        '121' => array (
                                'field' => 'distance',
                                'sort' => 'ASC',
                                'extra' => ''
                        ),
                        '122' => array(
                                'field' => 'distance',
                                'sort' => 'DESC',
                                'extra' => ''
                        ),
			// relevance lowest first
			'123' => array (
				'field' => 'relevance',
				'sort' => 'ASC',
				'extra' => ''
			),
			// relevance highest first
			'124' => array(
				'field' => 'relevance',
				'sort' => 'DESC',
				'extra' => ''
			),
                );
        }
	// #### $show['radiussearch'] is generated from search.php
	if ($ilconfig['globalserver_enabledistanceradius'] == false AND isset($show['radiussearch']) AND $show['radiussearch'])
        {
                if ($mode == 'listings')
                {
                        $array['121'] = array(
                                 'field' => 'p.date_end',
                                 'sort' => 'ASC',
                                 'extra' => ''
                        );
                        $array['122'] = array(
                                 'field' => 'p.date_end',
                                 'sort' => 'DESC',
                                 'extra' => ''
                        );
                }
                else if ($mode == 'experts')
                {
                        $array['121'] = array(
                                 'field' => 'u.income_reported',
                                 'sort' => 'ASC',
                                 'extra' => ''
                        );
                        $array['122'] = array(
                                 'field' => 'u.income_reported',
                                 'sort' => 'DESC',
                                 'extra' => ''
                        );        
                }
        }
	if ($ilconfig['fulltextsearch'])
	{
		if ($mode == 'listings')
                {
                        $array['123'] = array(
                                 'field' => 'relevance',
                                 'sort' => 'ASC',
                                 'extra' => ''
                        );
                        $array['124'] = array(
                                 'field' => 'relevance',
                                 'sort' => 'DESC',
                                 'extra' => ''
                        );
                }
                else if ($mode == 'experts')
                {
                        $array['123'] = array(
                                 'field' => 'relevance',
                                 'sort' => 'ASC',
                                 'extra' => ''
                        );
                        $array['124'] = array(
                                 'field' => 'relevance',
                                 'sort' => 'DESC',
                                 'extra' => ''
                        );        
                }
	}
        return $array;
}

/*
* ...
*
* @param       
*
* @return      
*/
function fetch_sort_options($mode = 'service')
{
        global $ilance, $ilconfig;
        if ($mode == 'experts')
        {
                $sortoptions = array(
                        '41' => '_feedback_lowest_first',
                        '42' => '_feedback_highest_first',
                        '51' => '_rated_lowest_first',
                        '52' => '_rated_highest_first',
                        '61' => '_expert_sort_up',
                        '62' => '_expert_sort_down',
                        '71' => '_city_sort_up',
                        '72' => '_city_sort_down',
                        '81' => '_country_sort_up',
                        '82' => '_country_sort_down',
                        '91' => '_zip_sort_up',
                        '92' => '_zip_sort_down',
                        '101' => '_earnings_lowest_first',
                        '102' => '_earnings_highest_first',
                        '111' => '_awards_lowest_first',
                        '112' => '_awards_highest_first'
                );
                if ($ilconfig['globalserver_enabledistanceradius'])
                {
                        $sortoptions['121'] = '_distance_closest_first';
                        $sortoptions['122'] = '_distance_furthest_first';
                }
		if ($ilconfig['fulltextsearch'])
		{
			$sortoptions['123'] = '_relevance_lowest_first';
			$sortoptions['124'] = '_relevance_highest_first';
		}
        }
        else if ($mode == 'service')
        {
                $sortoptions = array(
                        '01' => '_time_ending_soonest',
                        '02' => '_time_newly_listed',
                        '21' => '_bids_sort_up',
                        '22' => '_bids_sort_down',
                        '71' => '_city_sort_up',
                        '72' => '_city_sort_down',
                        '81' => '_country_sort_up',
                        '82' => '_country_sort_down',
                        '91' => '_zip_sort_up',
                        '92' => '_zip_sort_down',
                        '31' => '_group_category'
                );
                if ($ilconfig['globalserver_enabledistanceradius'])
                {
                        $sortoptions['121'] = '_distance_closest_first';
                        $sortoptions['122'] = '_distance_furthest_first';
                }
		if ($ilconfig['fulltextsearch'])
		{
			$sortoptions['123'] = '_relevance_lowest_first';
			$sortoptions['124'] = '_relevance_highest_first';
		}
        }
        else if ($mode == 'product')
        {
                $sortoptions = array(
                        '01' => '_time_ending_soonest',
                        '02' => '_time_newly_listed',
                        '11' => '_price_lowest_first',
                        '12' => '_price_highest_first',
                        '21' => '_bids_sort_up',
                        '22' => '_bids_sort_down',
                        '71' => '_city_sort_up',
                        '72' => '_city_sort_down',
                        '81' => '_country_sort_up',
                        '82' => '_country_sort_down',
                        '91' => '_zip_sort_up',
                        '92' => '_zip_sort_down',
                        '31' => '_group_category'
                );
                if ($ilconfig['globalserver_enabledistanceradius'])
                {
                        $sortoptions['121'] = '_distance_closest_first';
                        $sortoptions['122'] = '_distance_furthest_first';
                }
		if ($ilconfig['fulltextsearch'])
		{
			$sortoptions['123'] = '_relevance_lowest_first';
			$sortoptions['124'] = '_relevance_highest_first';
		}
        }
        
        ($apihook = $ilance->api('print_sort_options_end')) ? eval($apihook) : false;
        
        return $sortoptions;
}

/*
* Function to print the display order by pull down menu from the search results page
*
* @param       string        selected pull down value
* @param       string        fieldname of select menu
* @param       string        category mode (service or product)
* @param       boolean       javascript auto-submit active (default false)
*
* @return      
*/
function print_sort_pulldown($selected = '', $fieldname = 'sort', $mode = 'service', $js = false)
{
	$jshtml = ($js == false) ? '' : $js;
	$options = array();
	$options = fetch_sort_options($mode);
	foreach ($options AS $key => $value)
	{
		$options[$key] = '{' . $value . '}';
	}
	return construct_pulldown($fieldname, $fieldname, $options, $selected, ' class="select"' . $jshtml);
}

/*
* ...
*
* @param       
*
* @return      
*/
function fetch_perpage()
{
        global $ilance, $ilconfig;
        $perpage = $ilconfig['globalfilters_maxrowsdisplay'];
        if (!empty($_SESSION['ilancedata']['user']['searchoptions']))
        {
                if (empty($ilance->GPC['pp']))
                {
                        $pptemp = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                        $perpage = (int)$pptemp['perpage'];
                }
                else
                {
                        $perpage = intval($ilance->GPC['pp']);
                        if ($perpage <= 0)
                        {
                                $perpage = $ilconfig['globalfilters_maxrowsdisplay'];
                        }
                }
        }
        else
        {
                if (!empty($ilance->GPC['pp']) AND $ilance->GPC['pp'] > 0)
                {
                        $perpage = intval($ilance->GPC['pp']);
                        if ($perpage <= 0)
                        {
                                $perpage = $ilconfig['globalfilters_maxrowsdisplay'];
                        }
                }
        }
        if ($perpage <= 0)
        {
                $perpage = $ilconfig['globalfilters_maxrowsdisplay'];
        }
        return $perpage;
}

/*
* Function to return the opposite value of the perpage result limit when switching between list view and gallery view.  This function
* will prevent "blocks" in the search results (when viewing gallery mode) from being blank and will fill up with all results available.
*
* @param        string       list viewing type currently selected (list/gallery)
* @param        integer      actual per page value being used
*
* @return       integer      Returns integer with opposite per page value
*/
function fetch_proper_perpage($listview = '')
{
        global $ilance;
        $pp = fetch_perpage();
        $array = array();        
        if ($listview == 'gallery')
        {
                $array = array(
                        '1' => '4',
                        '2' => '4',
                        '3' => '4',
                        '4' => '4',
                        '5' => '4',
                        '8' => '8',
                        '10' => '12',
                        '12' => '12',
                        '24' => '24',
                        '25' => '24',
                        '48' => '48',
                        '50' => '48',
                        '96' => '96',
                        '100' => '96',
                        '192' => '192',
                        '200' => '192'
                );
        }
        else if ($listview == 'list')
        {
                $array = array(
                        '1' => '5',
                        '2' => '5',
                        '3' => '5',
                        '4' => '5',
                        '5' => '5',
                        '6' => '5',
                        '10' => '10',
                        '12' => '10',
                        '24' => '25',
                        '25' => '25',
                        '48' => '50',
                        '50' => '50',
                        '96' => '100',
                        '100' => '100',
                        '192' => '200',
                        '200' => '200'
                );
        }
        return $array["$pp"];
}

/*
* Function responsible for printing the main search results table within the search system.  This function handles
* all logic for building custom searchable display columns, gallery view, list view and more.
*
* @param        array        search results array
* @param        string       category type (service/product)
* @param        string       constructed pagnation output
*
* @return      
*/
function print_search_results_table($searchresults = array(), $mode = 'service', $prevnext = '')
{
        global $ilance, $ilconfig, $phrase, $show, $textgenre, $ilpage, $php_self_urlencoded;
        $tdclass = $tdfooterclass = '';
        if ($mode == 'service')
        {
                $tdphrase = '{_jobs}';
        }
        else if ($mode == 'product')
        {
                $tdphrase = '{_items}';
        }
        else if ($mode == 'experts')
        {
                $tdphrase = '{_experts}';
        }
        $scriptpage = $ilpage['search'] . print_hidden_fields(true, array('budget','list'), true, '', '', true);
        // user is overriding his/her list preference for a moment.. 
        if (isset($ilance->GPC['list']) AND $ilance->GPC['list'] == 'list')
        {
                $listviewtype = (($ilconfig['template_textalignment'] == 'left')
			? '<span style="float:right" class="smaller"><a href="' . $scriptpage . '&amp;list=list" title="{_list_view}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list_current.gif" border="0" alt="{_list_view}" /></a><a href="' . $scriptpage . '&amp;list=gallery" title="{_gallery_view}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery.gif" border="0" alt="{_gallery_view}" /></a></span>'
			: '<span style="float:left" class="smaller"><a href="' . $scriptpage . '&amp;list=gallery" title="{_gallery_view}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery.gif" border="0" alt="{_gallery_view}" /></a><a href="' . $scriptpage . '&amp;list=list" title="{_list_view}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list_current.gif" border="0" alt="{_list_view}" /></a></span>');
                $forcepp = fetch_proper_perpage('list');
                $_SESSION['ilancedata']['user']['searchoptions'] = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                $_SESSION['ilancedata']['user']['searchoptions']['list'] = 'list';
                $_SESSION['ilancedata']['user']['searchoptions']['perpage'] = $forcepp;
                $_SESSION['ilancedata']['user']['searchoptions'] = serialize($_SESSION['ilancedata']['user']['searchoptions']);
        }
        else if (isset($ilance->GPC['list']) AND $ilance->GPC['list'] == 'gallery')
        {
                $listviewtype = (($ilconfig['template_textalignment'] == 'left')
			? '<span style="float:right" class="smaller"><a href="' . $scriptpage . '&amp;list=list" title="{_list_view}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list.gif" border="0" alt="{_list_view}" /></a><a href="' . $scriptpage . '&amp;list=gallery" title="{_gallery_view}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery_current.gif" border="0" alt="{_gallery_view}" /></a></span>'
			: '<span style="float:left" class="smaller"><a href="' . $scriptpage . '&amp;list=gallery" title="{_gallery_view}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery_current.gif" border="0" alt="{_gallery_view}" /></a><a href="' . $scriptpage . '&amp;list=list" title="{_list_view}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list.gif" border="0" alt="{_list_view}" /></a></span>');
                $forcepp = fetch_proper_perpage('gallery');
                $_SESSION['ilancedata']['user']['searchoptions'] = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                $_SESSION['ilancedata']['user']['searchoptions']['list'] = 'gallery';
                $_SESSION['ilancedata']['user']['searchoptions']['perpage'] = $forcepp;
                $_SESSION['ilancedata']['user']['searchoptions'] = serialize($_SESSION['ilancedata']['user']['searchoptions']);
        }
        $opts = array();
        if (!empty($_SESSION['ilancedata']['user']['searchoptions']))
        {
                $opts = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                if (isset($opts['list']) AND $opts['list'] == 'list')
                {
                        $forcepp = fetch_proper_perpage('gallery');
                        $listviewtype = (($ilconfig['template_textalignment'] == 'left')
				? '<span style="float:right" class="smaller"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list_current.gif" border="0" alt="{_list_view}" /><a href="' . $scriptpage . '&amp;list=gallery&amp;pp=' . $forcepp . '" title="{_gallery_view}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery.gif" border="0" alt="{_gallery_view}" /></a></span>'
				: '<span style="float:left" class="smaller"><a href="' . $scriptpage . '&amp;list=gallery&amp;pp=' . $forcepp . '" title="{_gallery_view}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery.gif" border="0" alt="{_gallery_view}" /></a><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list_current.gif" border="0" alt="{_list_view}" /></span>');
                }
                else if (isset($opts['list']) AND $opts['list'] == 'gallery')
                {
                        $forcepp = fetch_proper_perpage('list');
                        $listviewtype = (($ilconfig['template_textalignment'] == 'left')
				? '<span style="float:right" class="smaller"><a href="' . $scriptpage . '&amp;list=list&amp;pp=' . $forcepp . '" title="{_list_view}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list.gif" border="0" alt="{_list_view}" /></a><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery_current.gif" border="0" alt="{_gallery_view}" /></span>'
				: '<span style="float:left" class="smaller"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery_current.gif" border="0" alt="{_gallery_view}" /><a href="' . $scriptpage . '&amp;list=list&amp;pp=' . $forcepp . '" title="{_list_view}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list.gif" border="0" alt="{_list_view}" /></a></span>');
                }
        }
        else
        {
                $opts = fetch_default_searchoptions();
                $opts = unserialize($opts);
                $listviewtype = (($ilconfig['template_textalignment'] == 'left')
			? '<span style="float:right" class="smaller"><a href="' . $scriptpage . '&amp;list=list" title="{_list_view}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list_current.gif" border="0" alt="{_list_view}" /></a><a href="' . $scriptpage . '&amp;list=gallery" title="{_gallery_view}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery.gif" border="0" alt="{_gallery_view}" /></a></span>'
			: '<span style="float:left" class="smaller"><a href="' . $scriptpage . '&amp;list=gallery" title="{_gallery_view}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery.gif" border="0" alt="{_gallery_view}" /></a><a href="' . $scriptpage . '&amp;list=list" title="{_list_view}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list_current.gif" border="0" alt="{_list_view}" /></a></span>');
                if (!isset($ilance->GPC['list']))
                {
			$opts['list'] = 'list';
                }
                else
                {
			$opts['list'] = $ilance->GPC['list'];
                }
                if ($opts['list'] == 'list')
                {
                        $forcepp = fetch_proper_perpage('gallery');
                        $listviewtype = (($ilconfig['template_textalignment'] == 'left')
				? '<span style="float:right" class="smaller"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list_current.gif" border="0" alt="{_list_view}" /> <a href="' . $scriptpage . '&amp;list=gallery&amp;pp=' . $forcepp . '" title="{_gallery_view}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery.gif" border="0" alt="{_gallery_view}" /></a></span>'
				: '<span style="float:left" class="smaller"><a href="' . $scriptpage . '&amp;list=gallery&amp;pp=' . $forcepp . '" title="{_gallery_view}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery.gif" border="0" alt="{_gallery_view}" /></a><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list_current.gif" border="0" alt="{_list_view}" /></span>');
                }
                else if ($opts['list'] == 'gallery')
                {
                        $forcepp = fetch_proper_perpage('list');
                        $listviewtype = (($ilconfig['template_textalignment'] == 'left')
				? '<span style="float:right" class="smaller"><a href="' . $scriptpage . '&amp;list=list&amp;pp=' . $forcepp . '" title="{_list_view}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list.gif" border="0" alt="{_list_view}" /></a><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery_current.gif" border="0" alt="{_gallery_view}" /></span>'
				: '<span style="float:left" class="smaller"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/gallery_current.gif" border="0" alt="{_gallery_view}" /><a href="' . $scriptpage . '&amp;list=list&amp;pp=' . $forcepp . '" title="{_list_view}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/list.gif" border="0" alt="{_list_view}" /></a></span>');
                }        
        }
        // fetch html columns for printing along with the colspan info
        $data = $ilance->template_columns->print_table_head_columns($searchresults, $mode, $opts['list']);
        $tablecolumns = $data['columns'];
        $tablerows = $data['rows'];
        $colspan = $data['colspan'];
        unset($data);
        // #### SEARCH HEADER TABS #############################################
        $html = '';
        if ($mode == 'product')
        {
                $blockcss = '';
                $blockclass = 'block-content-yellow';
        }
	else if ($mode == 'service')
	{
		$blockcss = '4';
		$blockclass = 'block2-content-white';	
	}
	else if ($mode == 'experts')
	{
		$blockcss = '4';
		$blockclass = 'block2-content-white';	
	}
	$value['mode'] = $mode;
	$value['colspan'] = $colspan;
	$value['tdfooterclass'] = $tdfooterclass;
 	$value['blockcss'] = $blockcss;
	$value['blockclass'] = $blockclass;
	$value['listviewtype'] = $listviewtype;
	$value['tdphrase'] = $tdphrase;
	$value['table_cellpadding'] = $ilconfig['table_cellpadding'];
	$value['table_cellspacing'] = $ilconfig['table_cellspacing'];
	$value['template_textdirection'] = $ilconfig['template_textdirection'];
	$value['prevnext'] = $prevnext;
        
	($apihook = $ilance->api('print_search_results_header_tab_condition')) ? eval($apihook) : false;
	
        // #### SEARCH HEADER TABLE ############################################
	if ($mode == 'product')
        {
                $html1 = $ilance->template->fetch_template('search_results_table_header_product.html');
		$html1 = $ilance->template->parse_hash('search_results_table_header_product.html', array('ilconfig' => $ilconfig, 'ilpage' => $ilpage, 'value' => $value), 0, $html1);
		$html1 = $ilance->template->parse_if_blocks('search_results_table_header_product.html', $html1, true);
        }
	else if ($mode == 'service')
	{
		$html1 = $ilance->template->fetch_template('search_results_table_header_service.html');
		$html1 = $ilance->template->parse_hash('search_results_table_header_service.html', array('ilconfig' => $ilconfig, 'ilpage' => $ilpage, 'value' => $value), 0, $html1);
		$html1 = $ilance->template->parse_if_blocks('search_results_table_header_service.html', $html1, true);
	}
	else if ($mode == 'experts')
	{
		$html1 = $ilance->template->fetch_template('search_results_table_header_experts.html');
		$html1 = $ilance->template->parse_hash('search_results_table_header_experts.html', array('ilconfig' => $ilconfig, 'ilpage' => $ilpage, 'value' => $value), 0, $html1);
		$html1 = $ilance->template->parse_if_blocks('search_results_table_header_experts.html', $html1, true);
	}
	$html1 = stripslashes($html1);
	$html1 = addslashes($html1);
	$html1 = str_replace('$', '\$', $html1);
	eval('$html .= "' . $html1 . '";');
	unset($html1);
	$html = stripslashes($html);
        $html .= $tablecolumns;
        $html .= $tablerows;
        // determine if we need to display " no results found "
        if (isset($show['no_rows_returned']) AND $show['no_rows_returned'])
        {
                $html .= '<tr class="alt1"><td colspan="' . $colspan . '" align="center"><div style="padding-top:8px; padding-bottom:8px">{_no_results_found}</div></td></tr>';
				
		($apihook = $ilance->api('print_search_results_table_no_results_end')) ? eval($apihook) : false;
				
		$html .= '<tr class="alt2_top"><td colspan="' . $colspan . '" align="center">';
		$helpsearchurl = ($ilconfig['globalauctionsettings_seourls'])
			? HTTP_SERVER . 'search-help'
			: HTTP_SERVER . $ilpage['search'] . '?cmd=help';
                $html .= ((isset($ilconfig['fulltextsearch']) AND $ilconfig['fulltextsearch'])
			  ? '<div align="left"><span style="font-size:13px; font-weight:bold">{_learn_more_about_searching}:</span> <span class="blue"><a href="' . $helpsearchurl . '" rel="nofollow">{_advanced_search_commands}</a></span></div>'
			  : '');
                $html .= '</td></tr>';
        }
        
        // #### SEARCH PRE FOOTER ACTIONS ######################################
	$html1 = $ilance->template->fetch_template('search_results_table_footer_actions.html');
	$html1 = $ilance->template->parse_hash('search_results_table_footer_actions.html', array('ilconfig' => $ilconfig, 'ilpage' => $ilpage, 'value' => $value), 1, $html1);
	$html1 = $ilance->template->parse_if_blocks('search_results_table_footer_actions.html', $html1, true);
	$html1 = stripslashes($html1);
	$html1 = addslashes($html1);
	$html1 = str_replace('$', '\$', $html1);
	eval('$html .= "' . $html1 . '";');
	unset($html1);
	$html = stripslashes($html);

        // #### SEARCH FOOTER PAGE NAV #########################################
	$html1 = $ilance->template->fetch_template('search_results_table_footer_pagenav.html');
	$html1 = $ilance->template->parse_hash('search_results_table_footer_pagenav.html', array('ilconfig' => $ilconfig, 'ilpage' => $ilpage, 'value' => $value), 1, $html1);
	$html1 = $ilance->template->parse_if_blocks('search_results_table_footer_pagenav.html', $html1, true);
	$html1 = stripslashes($html1);
	$html1 = addslashes($html1);
	$html1 = str_replace('$', '\$', $html1);
	eval('$html .= "' . $html1 . '";');
	unset($html1);
	$html = stripslashes($html);
               
        ($apihook = $ilance->api('print_search_results_table_end')) ? eval($apihook) : false;
        
	// #### SEARCH FOOTER TABLE ############################################
	if ($mode == 'product')
        {
                $html1 = $ilance->template->fetch_template('search_results_table_footer_product.html');
		$html1 = $ilance->template->parse_hash('search_results_table_footer_product.html', array('ilconfig' => $ilconfig, 'ilpage' => $ilpage, 'value' => $value), 0, $html1);
		$html1 = $ilance->template->parse_if_blocks('search_results_table_footer_product.html', $html1, true);
        }
	else if ($mode == 'service')
	{
		$html1 = $ilance->template->fetch_template('search_results_table_footer_service.html');
		$html1 = $ilance->template->parse_hash('search_results_table_footer_service.html', array('ilconfig' => $ilconfig, 'ilpage' => $ilpage, 'value' => $value), 0, $html1);
		$html1 = $ilance->template->parse_if_blocks('search_results_table_footer_service.html', $html1, true);
	}
	else if ($mode == 'experts')
	{
		$html1 = $ilance->template->fetch_template('search_results_table_footer_experts.html');
		$html1 = $ilance->template->parse_hash('search_results_table_footer_experts.html', array('ilconfig' => $ilconfig, 'ilpage' => $ilpage, 'value' => $value), 0, $html1);
		$html1 = $ilance->template->parse_if_blocks('search_results_table_footer_experts.html', $html1, true);
	}
	$html1 = stripslashes($html1);
	$html1 = addslashes($html1);
	$html1 = str_replace('$', '\$', $html1);
	eval('$html .= "' . $html1 . '";');
	unset($html1);
	$html = stripslashes($html);
        return $html;
}

/*
* Function responsible for printing the main search results table within the search system.  This function handles
* all logic for building custom searchable display columns, gallery view, list view and more.
*
* @param        array        search results array
* @param        string       category type (service/product)
* @param        string       constructed pagnation output
*
* @return      
*/
function print_search_results_table_v4($searchresults = array(), $mode = 'product', $prevnext = '')
{
        global $ilance, $ilconfig, $phrase, $show, $textgenre, $ilpage, $php_self_urlencoded, $php_self, $searchlistactive, $searchgalleryactive, $searchlisturl, $searchgalleryurl, $selected;
	$scriptpage = $php_self;
	$opts = array();
	$html = '';
	/*
	SERVICE ======================
	*[featured] => 1
	*[featured_searchresults] => 1
	*[bold] => 1
	*[highlite] => 1
	*[project_id] => 23962197
	*[distance] => -
	*[distance_plain] => n/a
	*[username] => user
	*[city] => Here
	*[zipcode] => M5V2Z5
	*[state] => Ontario
	*[country] => Canada
	*[location] => Ontario, Canada<span>&nbsp;~&nbsp;<span class="black">72.2 mi {_from_lowercase}</span> <span class="blue"><a href="javascript:void(0)" onclick="javascript:jQuery('#zipcode_nag_modal').jqm({modal: false}).jqmShow();">K0K2G0<!--, Canada--></a></span></span>
	*[views] => 132
	*[date_starts] => 2013-03-22 10:26:52
	*[project_state] => service
	*[project_details] => public
	*[show] => 1
	*[title] => <a href="http://www.ilance.ca/x/d/r/project/23962197/need-a-mobile-app-developer"  title="Need a Mobile App Developer "><strong>Need a Mobile App Developer </strong></a>
	*[title_plain] => Need a Mobile App Developer
	*[description] => Please bid if you are serious and have .....
	*[additional_info] =>
	*[category] => <span class="blue"><a href="http://www.ilance.ca/x/d/r/projects/397/mobile-applications?sort=01&country=Canada"  title="Mobile Applications">Mobile Applications</a></span>
	*[averagebid_plain] => = {_sealed} =
	*[averagebid] => = {_sealed} =
	*[sel] => <input type="checkbox" name="project_id[]" value="23962197" id="service_23962197" />
	*[class] => featured_highlight
	*[timeleft] => <strong>3{_d_shortform}, 10{_h_shortform}</strong>
	*[icons] => <img src="/x/d/r/images/default/icons/sealed.gif" border="0" alt="{_sealed_bidding}" id="" />&nbsp;<img src="/x/d/r/images/default/icons/blind.gif" border="0" alt="{_blind_bidding}" id="" />&nbsp;
	*[bids] => <div class="smaller">1 {_bids_lower}</div>
        *[budget] => <div>US $25,000.00 {_to} US $100,000.00</div>
	*
	PRODUCT ======================
	*[featured] => 0
	*[featured_searchresults] => 0
	*[bold] => 0
	*[highlite] => 0
	*[project_id] => 99643167
	*[distance] => n/a
	*[distance_plain] => n/a
	*[username] => ilance
	*[city] => Grafton
	*[zipcode] => K0K2G0
	*[state] => Ontario
	*[country] => Canada
	*[location] => Ontario, Canada
	*[views] => 11
	*[date_starts] => 2013-05-27 04:28:38
	*[project_state] => product
	*[project_details] => public
	*[description] => .....
	*[category] => Mobile Phones
	*[category_plain] => Mobile Phones
	*[buynowtxt] =>
	*[buynowimg] =>
	*[buynow] =>
	*[proxybit] =>
	*[filtered_auctiontype] => regular
	*[title] => Apple iPhone 4 - 16GB - Black (Verizon) Smartphone Clean ESN 4/5...
	*[title_plain] => Apple iPhone 4 - 16GB - Black (Verizon) Smartphone Clean ESN 4/5 Condition
	*[sel] =>
	*[class] => alt1
	*[timeleft] => 17{_d_shortform}, 13{_h_shortform}
	*[timeleft_clean] => 17{_d_shortform}, 13{_h_shortform}
	*[timeleft_verbose] => Wed, Jun 26, 2013 04:28 AM
	*[icons] =>
	*[shipping_plain] => US$45.00
	*[shipping] => US$45.00
	*[mytime] => 1517455
	*[starttime] => -1074545
	*[endtime] => Wed, Jun 26, 2013 04:28 AM
	*[sample] =>
	*[sample_plain] =>
	*[price] => US$169.95
	*[currentbid_plain] => US$169.95
	*[price_plain] => n/a
	*[sold] =>
	*[bids] => 0 {_bids_lower}*/
	$isfeatured = '0';
	switch ($mode)
	{
		case 'service':
		{
			$forcepp = fetch_proper_perpage('list');
			$searchlistactive = ' active';
			$searchgalleryactive = '';
			$searchlisturl = 'javascript:;';
			$searchgalleryurl = '' . $scriptpage . '&amp;list=gallery&amp;pp=' . $forcepp;
			$_SESSION['ilancedata']['user']['searchoptions'] = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
			$_SESSION['ilancedata']['user']['searchoptions']['list'] = 'list';
			$_SESSION['ilancedata']['user']['searchoptions']['perpage'] = $forcepp;
			$_SESSION['ilancedata']['user']['searchoptions'] = serialize($_SESSION['ilancedata']['user']['searchoptions']);
			foreach ($searchresults AS $key => $value)
			{
				if ($key == '0' AND isset($searchresults[$key]['featured_searchresults']) AND $searchresults[$key]['featured_searchresults'] == '1')
				{
					$isfeatured = '1';
				}
				if ($isfeatured == '1' AND isset($searchresults[$key]['featured_searchresults']) AND $searchresults[$key]['featured_searchresults'] == '0')
				{
					$html2 = ''; //'<div class="smaller gray" style="background-color:#fff;line-height:27px;border-top: 1px dotted #D9D9D9">{_optimize_your_sales} <span class="blue"><a href="' . (($ilconfig['globalauctionsettings_seourls']) ? HTTP_SERVER . 'search-promotion?returnurl=' . urlencode(PAGEURL) : HTTP_SERVER . $ilpage['search'] . '?cmd=promotion&amp;returnurl=' . urlencode(PAGEURL)) . '" rel="nofollow">{_promote_your_items}</a></span></div>';   
					$html .= $html2;
					$isfeatured = '0';
				}
				$timeleftphrase = '{_ends}';
				$timeleft = $value['timeleft'];
				if ($value['date_starts'] > DATETIME24H)
				{
					$timeleftphrase = '{_starts}';
					$timeleft = $value['timeleft_plain'];
				}
				$html .= '<div class="product-list4' . (($value['featured_searchresults']) ? ' blueborder' : '') . '"' . ((isset($searchresults[($key-1)]['featured_searchresults']) AND $searchresults[($key-1)]['featured_searchresults']) ? ' style="margin-top:-3px"' : '') . '>
	<div class="txtb">
	    <h3><span style="float:right">' . $value['sel'] . '</span>' . $value['title'] . '' . ((can_display_element('icons')) ? ' ' . $value['icons'] . '' : '') . '</h3>
	    <h2><strong>{_budget}: ' . $value['budget'] . '</strong>&nbsp;&nbsp;<span class="litegray">|</span>&nbsp;&nbsp;{_posted}: ' . $value['posted'] . '&nbsp;&nbsp;<span class="litegray">|</span>&nbsp;&nbsp;' . $timeleftphrase . ': ' . $timeleft . ' <!--(' . $value['timeleft_verbose'] . ')-->&nbsp;&nbsp;<span class="litegray">|</span>&nbsp;&nbsp;' . $value['bids'] . '' . (!empty($value['distance']) ? '&nbsp;&nbsp;<span class="litegray">|</span>&nbsp;&nbsp;' . $value['distance'] . ' {_away_lower}' : '') . '</h2>
	    <h2 style="line-height:17px">' . $value['description'] . '</h2>
	    <h2 style="float:left;min-width:200px;margin-right:10px;overflow:hidden;white-space:nowrap;"><span class="gray">{_category}:</span> ' . $value['category'] . '</h2>
	    <h2 style="float:left;min-width:300px;"><span class="gray">{_required_skills}:</span> ' . $value['skills'] . '</h2>
	    <br class="clear"></div>
	    <div class="clear"></div>
	    <h2><!--xxxxxxxx&nbsp;&nbsp;<span class="litegray">|</span>&nbsp;&nbsp;--><span class="blue">' . ((can_display_element('username')) ? $value['username'] . '' : '*******') . '</span>&nbsp;&nbsp;<span class="litegray">|</span>&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'flags/' . strtolower($value['countrycode']) . '.png" border="0" alt="" id="" />&nbsp;&nbsp;' . $value['country'] . '</h2>
	    
	</div>
	<div class="clear"></div>
';
			}
			break;
		}
		case 'product':
		{
			if ($selected['list'] == 'list' OR isset($ilance->GPC['list']) AND $ilance->GPC['list'] == 'list')
			{
				$forcepp = fetch_proper_perpage('list');
				$searchlistactive = ' active';
				$searchgalleryactive = '';
				$searchlisturl = 'javascript:;';
				$searchgalleryurl = '' . $scriptpage . '&amp;list=gallery&amp;pp=' . $forcepp;
				$_SESSION['ilancedata']['user']['searchoptions'] = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
				$_SESSION['ilancedata']['user']['searchoptions']['list'] = 'list';
				$_SESSION['ilancedata']['user']['searchoptions']['perpage'] = $forcepp;
				$_SESSION['ilancedata']['user']['searchoptions'] = serialize($_SESSION['ilancedata']['user']['searchoptions']);
				foreach ($searchresults AS $key => $value)
				{
					if ($key == '0' AND isset($searchresults[$key]['featured_searchresults']) AND $searchresults[$key]['featured_searchresults'] == '1')
					{
						$isfeatured = '1';
					}
					if ($isfeatured == '1' AND isset($searchresults[$key]['featured_searchresults']) AND $searchresults[$key]['featured_searchresults'] == '0')
					{
						$html2 = '<div class="smaller gray" style="background-color:#fff;line-height:27px;border-top: 1px dotted #D9D9D9">{_optimize_your_sales} <span class="blue"><a href="' . (($ilconfig['globalauctionsettings_seourls']) ? HTTP_SERVER . 'search-promotion?returnurl=' . urlencode(PAGEURL) : HTTP_SERVER . $ilpage['search'] . '?cmd=promotion&amp;returnurl=' . urlencode(PAGEURL)) . '" rel="nofollow">{_promote_your_items}</a></span></div>';   
						$html .= $html2;
						$isfeatured = '0';
					}
					$timeleftphrase = '{_time_left}';
					$timeleft = $value['timeleft'];
					if ($value['date_starts'] > DATETIME24H)
					{
						$timeleftphrase = '{_starts}';
						$timeleft = $value['timeleft_plain'];
					}
					$html .= '<div class="product-list2' . (($value['featured_searchresults']) ? ' blueborder' : '') . (($value['highlite']) ? ' ' . $ilconfig['productupsell_highlightcolor'] : '') . '"' . ((isset($searchresults[($key-1)]['featured_searchresults']) AND $searchresults[($key-1)]['featured_searchresults']) ? ' style="margin-top:-3px"' : '') . '>
	<div class="imgb">
		' . $value['sample'] . '
	</div>
	<div class="txtb">
	    <h3>' . $value['title'] . '' . ((can_display_element('icons')) ? ' ' . $value['icons'] . '' : '') . '</h3>
	    <p>' . ((can_display_element('listinglocation')) ? '<span>' . $value['location'] . '</span>' : '') . ' ' . ((can_display_element('username')) ? '<span>{_seller}: ' . $value['username'] . '' . ((can_display_element('latestfeedback')) ? ' ' . $value['feedback'] : '') . '</span>' : '') . '</p>
	    <a href="javascript:;" title="{_more_info}" class="info info-link1" id="' . $value['project_id'] . '"><span>{_more_info}</span></a> 
	    
	    <!-- start product info modal -->
	    <div class="product-info2 info-box1-' . $value['project_id'] . '" style="display:none">
		<div class="arrrow"></div>
		<a href="javascript:;" title="{_close}" class="close info-box1-close" id="' . $value['project_id'] . '">{_close}</a>
		<div class="info-imgb" id="ibigphoto_' . $value['project_id'] . '"><span style="float:left;margin:105px 0 0 100px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'working.gif" border="0" alt="" id="" /></span></div>
		<div class="info-txtb">
		    <h4>' . $value['title_plain'] . '</h4>
		    <p style="margin-bottom:-5px">
			    <span><strong>{_location}:</strong> ' . $value['location'] . '</span>
			    <span><strong>{_seller}:</strong> ' . $value['username'] . ' ' . $value['feedback'] . '</span>
		    </p>
		    <div class="row">
			    <span class="label" id="ipricelabel_' . $value['project_id'] . '">{_search_results_price}:</span> <span class="value" id="ipricevalue_' . $value['project_id'] . '"><strong>' . $value['price'] . '</strong></span>
			    <span class="label" id="ibidlabel_' . $value['project_id'] . '" style="display:none">{_bid_history}:</span> <span class="value" id="ibidvalue_' . $value['project_id'] . '" style="display:none">' . $value['bids'] . '</span>
			    <span class="label">{_shipping}:</span> <span class="value">' . $value['shipping'] . '</span>
			    <span class="label">' . $timeleftphrase . ':</span> <span class="value" id="itimeleft_' . $value['project_id'] . '">' . $timeleft . ' (' . $value['timeleft_verbose'] . ')</span>
		    </div>
		    <div class="row">
			    <span style="display:none" id="ispecifics_' . $value['project_id'] . '"></span>
			    <span class="label">{_quantity}:</span> <span class="value">' . (($value['quantity'] <= 0) ? '<strong class="red">{_out_of_stock}</strong>' : '<strong class="green">{_in_stock}</strong>') . ' (' . number_format($value['quantity']) . ' {_left_lower})</span>
			    ' . (!empty($value['distance']) ? '<span class="label">{_distance}:</span> <span class="value" title="' . $value['distance_verbose'] . '">' . $value['distance'] . '</span>' : '') . '
			    <span class="label">{_return_policy}:</span> <span class="value">' . $value['returnpolicy'] . '</span>
		    </div>
		</div>
		<div class="bottom-bar">
		    <!--<ul>
			<li><a href="#" title="{_buy_now}">{_buy_now}</a></li>
			<li><a href="#" title="{_add_to_watchlist}">{_add_to_watchlist}</a></li>
		    </ul>-->
		    <a href="' . $value['url'] . '" title="{_view_item}" class="blue-button">{_view_item}</a>
		</div>
	    </div>
	    <!-- end product info modal -->
	    
	</div>
	<div class="price-col">
	    <div class="col1">
		   ' . (!empty($value['price']) ? '<span>{_search_results_price}:</span>' : '') . '
		   ' . (!empty($value['currentbid']) ? '<span>{_current_bid}:</span>' : '') . '
		   <span>{_shipping}:</span>
		   ' . (!empty($value['distance']) ? '<span>{_distance}:</span>' : '') . '
	    </div>
	    <div class="col2">
		    ' . (!empty($value['price']) ? '<span><strong>' . $value['price'] . '</strong></span>' : '') . '
		    ' . (!empty($value['currentbid']) ? '<span>' . $value['currentbid'] . '</span>' : '') . '
		    <span>' . $value['shipping'] . '</span>
		    ' . (!empty($value['distance']) ? '<span title="' . $value['distance_verbose'] . '">' . $value['distance'] . '</span>' : '') . '
	    </div>
	    <div class="col3">
		    <span title="' . $value['timeleft_verbose'] . '"><span style="float:right;margin-top:-3px">' . $value['sel'] . '</span>' . $value['timeleft'] . ' |  ' . $value['bids'] . '</span>
	    </div>
	</div>
	<div class="clear"></div>
    
</div>';
				}
			}
			else if ($selected['list'] == 'gallery' OR isset($ilance->GPC['list']) AND $ilance->GPC['list'] == 'gallery')
			{
				$forcepp = fetch_proper_perpage('gallery');
				$searchlistactive = '';
				$searchgalleryactive = ' active';
				$searchlisturl = '' . $scriptpage . '&amp;list=list&amp;pp=' . $forcepp;
				$searchgalleryurl = 'javascript:;';
				$_SESSION['ilancedata']['user']['searchoptions'] = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
				$_SESSION['ilancedata']['user']['searchoptions']['list'] = 'gallery';
				$_SESSION['ilancedata']['user']['searchoptions']['perpage'] = $forcepp;
				$_SESSION['ilancedata']['user']['searchoptions'] = serialize($_SESSION['ilancedata']['user']['searchoptions']);
				$count = 0;
				$cols = 4;
				$html .= '<div id="product-list3" class="product-list3"><ul>';
				foreach ($searchresults AS $key => $value)
				{
					$class = '';
					if ($key == '0' AND isset($searchresults[$key]['featured_searchresults']) AND $searchresults[$key]['featured_searchresults'] == '1')
					{
						$isfeatured = '1';
					}
					if ($isfeatured == '1' AND isset($searchresults[$key]['featured_searchresults']) AND $searchresults[$key]['featured_searchresults'] == '0')
					{
						$html2 = '';
						//$html2 = '<div class="smaller gray" style="background-color:#fff;line-height:27px;border-top: 1px dotted #D9D9D9">{_optimize_your_sales} <span class="blue"><a href="' . (($ilconfig['globalauctionsettings_seourls']) ? HTTP_SERVER . 'search-promotion?returnurl=' . urlencode(PAGEURL) : HTTP_SERVER . $ilpage['search'] . '?cmd=promotion&amp;returnurl=' . urlencode(PAGEURL)) . '" rel="nofollow">{_promote_your_items}</a></span></div>';   
						$html .= $html2;
						$isfeatured = '0';
					}
					$timeleftphrase = '{_time_left}';
					$timeleft = $value['timeleft'];
					if ($value['date_starts'] > DATETIME24H)
					{
						$timeleftphrase = '{_starts}';
						$timeleft = $value['timeleft_plain'];
					}
					$count++;
					if ($count == 1)
					{
						$class = 'gap-info1';
					}
					else if ($count == 4)
					{
						$count = 0;
					}
					$html .= '<li class="' . $class . '">
	' . $value['sel'] . '
	<a href="javascript:;" title="{_more_info}" class="info info-link1 zoom" id="' . $value['project_id'] . '">{_more_info}</a>
	<div class="overlay' . (($value['highlite']) ? ' ' . $ilconfig['productupsell_highlightcolor'] : '') . '">
		<div class="imgb">
			<div class="imgb-in' . (($value['featured_searchresults']) ? ' blueborder-gallery' : '') . '">
				<div>' . $value['sample'] . '</div>
			</div>
		</div>
		<div class="txtb">
			<h3>' . $value['title'] . '</h3>
			<span class="price">' . $value['price'] . '</span>
			<!--<span class="shipping">{_shipping}: ' . $value['shipping'] . '</span>-->
			' . ((can_display_element('listinglocation')) ? '<span class="location">' . $value['location'] . '</span>' : '') . ' ' . ((can_display_element('username')) ? '<span class="seller">{_seller}: ' . $value['username'] . '' . ((can_display_element('latestfeedback')) ? ' ' . $value['feedback'] : '') . '</span>' : '') . '
			' . (!empty($value['distance']) ? '<span class="distance">{_distance}:</span> <span title="' . $value['distance_verbose'] . '">' . $value['distance'] . '</span>' : '') . '
			<!--<span class="timeleft">' . $value['timeleft'] . '</span>-->
		</div>
	</div>
	
	<!-- start product info modal -->
	<div class="product-info2 info-box1-' . $value['project_id'] . '" style="display:none">
	    <div class="arrrow"></div>
	    <a href="javascript:;" title="{_close}" class="close info-box1-close" id="' . $value['project_id'] . '">{_close}</a>
	    <div class="info-imgb" id="ibigphoto_' . $value['project_id'] . '"><span style="float:left;margin:105px 0 0 100px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'working.gif" border="0" alt="" id="" /></span></div>
	    <div class="info-txtb">
		<h4>' . $value['title_plain'] . '</h4>
		<p style="margin-bottom:-15px">
			<span><strong>{_location}:</strong> ' . $value['location'] . '</span>
		</p>
		<p>
			<span><strong>{_seller}:</strong> ' . $value['username'] . ' ' . $value['feedback'] . '</span>
		</p>
		<div class="row">
			<span class="label" id="ipricelabel_' . $value['project_id'] . '">{_search_results_price}:</span> <span class="value" id="ipricevalue_' . $value['project_id'] . '"><strong>' . $value['price'] . '</strong></span>
			<span class="label" id="ibidlabel_' . $value['project_id'] . '" style="display:none">{_bid_history}:</span> <span class="value" id="ibidvalue_' . $value['project_id'] . '" style="display:none">' . $value['bids'] . '</span>
			<span class="label">{_shipping}:</span> <span class="value">' . $value['shipping'] . '</span>
			<span class="label">' . $timeleftphrase . ':</span> <span class="value" id="itimeleft_' . $value['project_id'] . '">' . $timeleft . ' (' . $value['timeleft_verbose'] . ')</span>
		</div>
		<div class="row">
			<span style="display:none" id="ispecifics_' . $value['project_id'] . '"></span>
			<span class="label">{_quantity}:</span> <span class="value">' . (($value['quantity'] <= 0) ? '<strong class="red">{_out_of_stock}</strong>' : '<strong class="green">{_in_stock}</strong>') . ' (' . number_format($value['quantity']) . ' {_left_lower})</span>
			' . (!empty($value['distance']) ? '<span class="label">{_distance}:</span> <span class="value" title="' . $value['distance_verbose'] . '">' . $value['distance'] . '</span>' : '') . '
			<span class="label">{_return_policy}:</span> <span class="value">' . $value['returnpolicy'] . '</span>
		</div>
	    </div>
	    <div class="bottom-bar">
		<!--<ul>
		    <li><a href="#" title="{_buy_now}">{_buy_now}</a></li>
		    <li><a href="#" title="{_add_to_watchlist}">{_add_to_watchlist}</a></li>
		</ul>-->
		<a href="' . $value['url'] . '" title="{_view_item}" class="blue-button">{_view_item}</a>
	    </div>
	</div>
	<!-- end product info modal -->
</li>';
				}
				$html .= '</ul><div class="clear"></div></div>';
			}
			if (!empty($_SESSION['ilancedata']['user']['searchoptions']))
			{
				$opts = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
				if (isset($opts['list']) AND $opts['list'] == 'list')
				{
					$forcepp = fetch_proper_perpage('gallery');
					$searchlistactive = ' active';
					$searchgalleryactive = '';
					$searchlisturl = 'javascript:;';
					$searchgalleryurl = '' . $scriptpage . '&amp;list=gallery&amp;pp=' . $forcepp;
				}
				else if (isset($opts['list']) AND $opts['list'] == 'gallery')
				{
					$forcepp = fetch_proper_perpage('list');
					$searchlistactive = '';
					$searchgalleryactive = ' active';
					$searchlisturl = '' . $scriptpage . '&amp;list=list&amp;pp=' . $forcepp;
					$searchgalleryurl = 'javascript:;';
				}
			}
			else
			{
				$opts = fetch_default_searchoptions();
				$opts = unserialize($opts);
				if (!isset($ilance->GPC['list']))
				{
					$opts['list'] = 'list';
				}
				else
				{
					$opts['list'] = $ilance->GPC['list'];
				}
				if ($opts['list'] == 'list')
				{
					$forcepp = fetch_proper_perpage('gallery');
					$searchlistactive = ' active';
					$searchgalleryactive = '';
					$searchlisturl = 'javascript:;';
					$searchgalleryurl = '' . $scriptpage . '&amp;list=gallery&amp;pp=' . $forcepp;
				}
				else if ($opts['list'] == 'gallery')
				{
					$forcepp = fetch_proper_perpage('list');
					$searchlistactive = '';
					$searchgalleryactive = ' active';
					$searchlisturl = '' . $scriptpage . '&amp;list=list&amp;pp=' . $forcepp;
					$searchgalleryurl = 'javascript:;';
				}        
			}
			break;
		}
	}
	// determine if we need to display " no results found "
        if (isset($show['no_rows_returned']) AND $show['no_rows_returned'])
        {
		$html .= '<h2 class="litegray" style="margin-top:15px;margin-bottom:15px">{_no_results_found}</h2>';
		
		($apihook = $ilance->api('print_search_results_table_no_results_end')) ? eval($apihook) : false;
        }
        return $html;
}

/*
* Function to determine if a logged in member is displaying a certain column or information bit
* within their search result.  This will ultimately hide or show that bit from their selected search
* options from the advanced search menu
*
* @param        string        display option name
*
* @return       boolean       Returns true or false if we can display the element     
*/
function can_display_element($option = '')
{
	if (!empty($_SESSION['ilancedata']['user']['searchoptions']))
	{
		$temp = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
		if (isset($temp[$option]) AND $temp[$option] == 'true')
		{
			return true;
		}
		
		return false;
	}
	else
	{
		// default everything enabled (just in case)
		return true;
	}
}

/*
* Function to print skills used within the provider search results.
*
* @param       integer       user id
* @param       integer       maximum number of skills to display (default 100)
* @param       boolean       no url links (default false)
*
* @return      
*/
function print_skills($userid = 0, $showmaxskills = 100, $nourls = false)
{
        global $ilance, $phrase, $ilpage, $ilconfig;
        $html = '{_pending}';
        $sql = $ilance->db->query("
                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "a.cid, s.title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title
                FROM " . DB_PREFIX . "skills_answers a
                LEFT JOIN " . DB_PREFIX . "skills s ON s.cid = a.cid
                WHERE a.user_id = '" . intval($userid) . "'
                ORDER BY a.cid ASC
                LIMIT $showmaxskills
        ");
        if ($ilance->db->num_rows($sql) > 0)
        {
                $html = '';
                if (defined('PHP_SELF'))
                {
                        $scriptpage = PHP_SELF;
                }
                else
                {
                        $scriptpage = $ilpage['search'];
                }
                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                {
                        $title = stripslashes($res['title']);
                        if (!empty($ilance->GPC['sid'][$res['cid']]))
                        {
                                $removeurl = rewrite_url($scriptpage, $remove = 'sid[' . $ilance->GPC['sid'][$res['cid']] . ']=true');
                        }
                        else
                        {
                                $removeurl = $scriptpage;
                        }
                        if ($nourls == false)
                        {
                                if (isset($ilance->GPC['sid']) AND !empty($ilance->GPC['sid'][$res['cid']]))
                                {
                                        $html .= '<span style="color:#000"><strong>' . $title . '</strong></span>, ';
                                }
                                else
                                {
                                        $html .= '<a href="' . $removeurl  . '&amp;sid[' . $res['cid'] . ']=true" title="{_show_only_providers_skilled_in} ' . $title . '" class="gray">' . $title . '</a>, ';
                                }
                        }
                        else
                        {
                                $html .= $title . ', ';
                        }
                }
                if (!empty($html))
                {
                        $html = mb_substr($html, 0, -2);
                }
        }
        return $html;
}

/*
* Function to print out the skill title based on a particular category id.
*
* @param       integer         category id
*
* @return      
*/
function print_skill_title($sid = 0)
{
        global $ilance, $phrase;
	$sql = $ilance->db->query("
		SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title
		FROM " . DB_PREFIX . "skills
		WHERE cid = '" . intval($sid) . "'
		LIMIT 1
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		return $res['title'];
	}
        return '';
}

/*
* Function to actually do all the independent searching based on a user entering many keywords for their search pattern.
* Additionally, this function will output a formatted <li></li> result set allowing the user to click those links to refine
* their search.
*
* @param       array         keywords being used (array)
* @param       string        category type (service/product)
* @param       integer       results found     
*
* @return      
*/
function fetch_fewer_keyword_links($keywords = array(), $cattype = '', $limit = 4)
{
        global $ilance, $ilpage, $phrase;
        $html = array('html' => '', 'count' => '4');
        if (isset($keywords) AND is_array($keywords))
        {
                // ie: "50 Double Dual Layer Blank Disk"
                // 1. "Double Dual Layer Blank Disk"
                // 2. "50 Double Dual Layer"
                // 3. "Double Dual Layer Blank"
                // 4. "Double Dual Blank Disk"
                $html['html'] = '<ul>';
                $num = 0;
                foreach ($keywords AS $keyword)
                {
                        if ($num <= $limit)
                        {
                                $html['html'] .= '<li><span><a href="' . $ilpage['search'] .'?mode=' . $cattype . '&amp;q=' . urlencode($keyword) . '"><strong>4 items</strong></a> found for ' . $keyword . ' <b>50</b> <b>Double</b> <b>Dual</b> <b>Layer</b> <strike>Blank</strike> <strike>Disk</strike></li>';
                                //$html['html'] = '<li><span><a href="#" style="text-decoration:none"><strong>1 items</strong></a> found for <b>50</b> <b>Double</b> <b>Dual</b> <b>Layer</b> <strike>Blank</strike> <strike>Disk</strike> </span></li><li><span><a href="#" style="text-decoration:none"><strong>4 items</strong></a> found for <strike>50</strike> <b>Double</b> <b>Dual</b> <b>Layer</b> <b>Blank</b> <strike>Disk</strike> </span></li><li><span><a href="#" style="text-decoration:none"><strong>4 items</strong></a> found for <strike>50</strike> <b>Double</b> <b>Dual</b> <strike>Layer</strike> <b>Blank</b> <b>Disk</b> </span></li>';
                                $num++;
                        }
                        
                }
                $html['html'] .= '</ul>';
                /*
                <li><span><a href="#" style="text-decoration:none"><strong>1 items</strong></a> found for <b>50</b> <b>Double</b> <b>Dual</b> <b>Layer</b> <strike>Blank</strike> <strike>Disk</strike> </span></li>
                <li><span><a href="#" style="text-decoration:none"><strong>4 items</strong></a> found for <strike>50</strike> <b>Double</b> <b>Dual</b> <b>Layer</b> <b>Blank</b> <strike>Disk</strike> </span></li>
                <li><span><a href="#" style="text-decoration:none"><strong>4 items</strong></a> found for <strike>50</strike> <b>Double</b> <b>Dual</b> <strike>Layer</strike> <b>Blank</b> <b>Disk</b> </span></li>                 
                */
        }
        return $html;
}

/*
* This function will display a few links if the user entered many keywords allowing them to refine their search.
* For example, if a user searches for "50 Double Dual Layer Blank Disk", the search system will attempt to independently
* find other results (with count > 0) based on various mixing and matching of the various keywords.
*
* Example keyword: "50 Double Dual Layer Blank Disk"
*
* 1. "Double Dual Layer Blank Disk"
* 2. "50 Double Dual Layer"
* 3. "Double Dual Layer Blank"
* 4. "Double Dual Blank Disk"
*
* @param       array         keywords being used (array)
* @param       string        category type (service/product)
* @param       integer       results found
*
* @return      
*/
function print_fewer_keywords_search($keywords = array(), $cattype = '', $resultsfound = 0)
{
        global $ilance, $phrase, $show;
        $show['showfewerkeywords'] = false;
        return; // not ready
        $html = '';
        // checks if keywords array is not empty, if more than 2 keywords and results equal to zero or results less than 5
        // we will search and find more matches for user based on fewer keywords
        if (isset($keywords) AND is_array($keywords) AND count($keywords) >= 2 AND ($resultsfound == 0 OR $resultsfound < 5))
        {
                $temp = fetch_fewer_keyword_links($keywords, $cattype, $limit = 4);
                // html of the links returned from other fewer results found
                $fewerhtml = $temp['html'];
                // number of fewer result links found
                $count = $temp['count'];
                if ($count > 0)
                {
                        $show['showfewerkeywords'] = true;
                        $html = '<div class="bluehlite"><div><strong>{_get_more_results_with_fewer_keywords}</strong></div>' . $fewerhtml . '</div>';
                }
                else
                {
                        $show['showfewerkeywords'] = false;
                }
                unset($temp);
        }
        return $html;
}

/*
* ...
*
* @param       
*
* @return      
*/
function fetch_filtered_searchoptions()
{
        global $ilance;
	$filtercolumns = array();
        $sql = $ilance->db->query("
                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "questionid, question, filtertype
                FROM " . DB_PREFIX . "profile_questions
                WHERE isfilter = '1'
                ORDER BY sort ASC
        ");
        if ($ilance->db->num_rows($sql) > 0)
        {
                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                {
                        $filtercolumns['profile_' . $res['questionid']] = stripslashes($res['question']);
                }
        }
        return $filtercolumns;
}

/*
* ...
*
* @param       
*
* @return      
*/
function build_expert_search_exclusion_sql($fieldidentifier = '', $permission = '')
{
        global $ilance, $ilconfig, $phrase;
        $html = '';
        $sql = $ilance->db->query("
                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "user_id
                FROM " . DB_PREFIX . "users
                WHERE status != 'active' AND displayprofile = '0' OR status = 'active' AND displayprofile = '0'
        ");
        if ($ilance->db->num_rows($sql) > 0)
        {
                $excluded = 0;
                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                {
                        $ids[] = $res['user_id'];
                }
                
                if (isset($ids) AND count($ids) > 0)
                {
			$html = "AND " . $fieldidentifier . "user_id NOT IN (" . implode(',', $ids) . ") ";
                }
        }
        return $html;
}

/*
* ...
*
* @param       
*
* @return      
*/
function build_skills_inclusion_sql($fieldidentifier = '', $keywords = '')
{
        global $ilance, $ilconfig;
        if (empty($keywords))
        {
                return '';
        }
        $html = '';
        $sql = $ilance->db->query("
                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "cid
                FROM " . DB_PREFIX . "skills
                WHERE MATCH (title_" . $_SESSION['ilancedata']['user']['slng'] . ") AGAINST ('" . $ilance->db->escape_string($keywords) . "' IN BOOLEAN MODE)
        ");
        if ($ilance->db->num_rows($sql) > 0)
        {
                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                {
                        $ids[] = $res['cid'];
                }
                
                if (isset($ids) AND count($ids) > 0)
                {
                        $html = "AND (FIND_IN_SET(" . $fieldidentifier . "cid, '" . implode(',', $ids) . ",'))";
                }
        }
        return $html;
}

/**
* Function for determining if a keyword entered is incorrect and supplying an alternative if available
*
* @param       string         keyword
* @param       string         category type (service, product, experts)
*
* @return      string         HTML representation of the correct word/phrase if applicable
*/
function print_did_you_mean($query = '', $mode = 'service')
{
        global $ilance, $ilpage, $ilconfig, $phrase, $number;
        if (isset($ilconfig['didyoumeancorrection']) AND $ilconfig['didyoumeancorrection'] AND isset($number) AND $number <= 0)
	{
		$correctword = $ilance->didyoumean->correct($query);
		if (mb_strtolower($correctword) != mb_strtolower($query))
		{
			switch ($mode)
			{
				case 'service':
				{
					return '<div style="margin-top:3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'trail.gif" border="0" alt="" id="" /> <span class="gray"><strong>{_did_you_mean}</strong> <span class="blue"><a href="' . HTTP_SERVER . $ilpage['search'] . '?mode=service&amp;q=' . ucwords(handle_input_keywords($correctword)) . '"><span><strong><em>' . ucwords(handle_input_keywords($correctword)) . '?</em></strong></span></a></span></span></div>';
					break;
				}
				case 'product':
				{
					return '<div style="margin-top:3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'trail.gif" border="0" alt="" id="" /> <span class="gray"><strong>{_did_you_mean}</strong> <span class="blue"><a href="' . HTTP_SERVER . $ilpage['search'] . '?mode=product&amp;q=' . ucwords(handle_input_keywords($correctword)) . '"><span><strong><em>' . ucwords(handle_input_keywords($correctword)) . '?</em></strong></span></a></span></span></div>';
					break;
				}
				case 'experts':
				{
					return '<div style="margin-top:3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'trail.gif" border="0" alt="" id="" /> <span class="gray"><strong>{_did_you_mean}</strong> <span class="blue"><a href="' . HTTP_SERVER . $ilpage['search'] . '?mode=experts&amp;q=' . ucwords(handle_input_keywords($correctword)) . '"><span><strong><em>' . ucwords(handle_input_keywords($correctword)) . '?</em></strong></span></a></span></span></div>';
					break;
				}
			}
			
			($apihook = $ilance->api('print_did_you_mean_end')) ? eval($apihook) : false;
		}
		unset($result, $correctword);
	}
        return false;
}

/**
* Function for fetching the answer title for the searchable listing answers logic via urls.
*
* @param       integer        question id
* @param       mixed          answer id (could be integer or string)
* @param       string         category type (service or product)
*
* @return      string         HTML representation of the answer title
*/
function fetch_searchable_answer_title($qid = 0, $aid = '', $cattype = '')
{
        global $ilance;
        $html = '';
        $table = ($cattype == 'service') ? "project_questions_choices" : "product_questions_choices";
	$sql = $ilance->db->query("
		SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "choice_" . $_SESSION['ilancedata']['user']['slng'] . " AS choice
		FROM " . DB_PREFIX . $table . "
		WHERE questionid = '" . intval($qid) . "'
			AND optionid = '" . intval($aid) . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		$html = handle_input_keywords($res['choice']);
	}
        return $html;
}

/**
* Function for fetching the question title for the searchable listing answers logic via urls.
*
* @param       integer        question id
* @param       string         category type (service or product)
*
* @return      string         HTML representation of the question title
*/
function fetch_searchable_question_title($qid = 0, $cattype = '')
{
        global $ilance;
        $html = '';
        $table = ($cattype == 'service') ? "project_questions" : "product_questions";
        $sql = $ilance->db->query("
                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "question_" . $_SESSION['ilancedata']['user']['slng'] . " AS question
                FROM " . DB_PREFIX . $table . "
                WHERE questionid = '" . intval($qid) . "'
                LIMIT 1
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                $html = handle_input_keywords($res['question']);
        }
        return $html;
}

/*
* Function for fetching the state date / end date SQL condition for the search system.
*
* @param       integer        filter that is selected (-1 = any date), 1 = 1 hour, 2 = 2 hours, etc.
* @param       string         MySQL function to use (DATEADD, DATESUB), etc
* @param       string         field name in the database table to use
* @param       string         operator (>, <, =, etc)
*
* @return      string         Valid SQL condition code to include in main SQL code to parse
*/
function fetch_startenddate_sql($endstart_filter, $mysqlfunction, $field, $operator)
{
	global $ilance;
        $sql = '';
	switch ($endstart_filter)
	{
		case '-1':
                {
                        $sql = "";
                        break;
                }	    
		case '7':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETODAY. "', INTERVAL 1 DAY) ";
                        break;
                }	    
		case '8':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETODAY . "', INTERVAL 2 DAY) ";
                        break;
                }	    
		case '9':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETODAY . "', INTERVAL 3 DAY) ";
                        break;
                }	    
		case '10':
                {
                	$sql = " AND $field $operator $mysqlfunction('" . DATETODAY . "', INTERVAL 4 DAY) ";
                	break;
                }	    
		case '11':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETODAY . "', INTERVAL 5 DAY) ";
                        break;
                }	    
		case '12':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETODAY . "', INTERVAL 6 DAY) ";
                        break;
                }	    
		case '13':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETODAY . "', INTERVAL 7 DAY) ";
                        break;
                }	    
		case '14':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETODAY . "', INTERVAL 14 DAY) ";
                        break;
                }	    
		case '15':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETODAY . "', INTERVAL 1 MONTH) ";
                        break;
                }
                case '16':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETODAY . "', INTERVAL 2 MONTH) ";
                        break;
                }
                case '17':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETODAY . "', INTERVAL 3 MONTH) ";
                        break;
                }
                case '18':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETODAY . "', INTERVAL 6 MONTH) ";
                        break;
                }
                case '19':
                {
                        $sql = " AND $field $operator $mysqlfunction('" . DATETODAY . "', INTERVAL 1 YEAR) ";
                        break;
                }
	}
	return $sql;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>