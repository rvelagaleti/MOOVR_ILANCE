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
* common_location.
*
* @package      iLance\Common\Location
* @version      4.0.0.8059
* @author       ILance
*/
class common_location extends common
{
	/**
	* Function to print the user location bit based on a particular user id.
	* 
	* @param       integer        user id
	* @param       integer        user short language identifier (i.e.: eng)
	* @param       string         supplied country name
	* @param       string         supplied state name
	* @param       string         supplied city name
	* @param       string         supplied zip code name
	*
	* @return      string         Returns HTML representation of the user location bit
	*/
	function print_user_location($uid = 0, $slng = 'eng', $country = '', $state = '', $city = '', $zip = '')
	{
		global $ilance, $ilconfig;
		if (empty($slng))
		{
			$slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';
		}
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "u.address, u.address2, u.city, u.state, u.zip_code, l.location_" . $slng . " as country
			FROM " . DB_PREFIX . "users u
			LEFT JOIN " . DB_PREFIX . "locations l ON (l.locationid = u.country)
			WHERE u.user_id = '" . intval($uid) . "'
				AND u.status = 'active'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$country = empty($country) ? $res['country'] : $country;
			$state = empty($state) ? $res['state'] : $state;
			$city = empty($city) ? $res['city'] : $city;
			$zip = empty($zip) ? $res['zip_code'] : $zip;
			$zip = (mb_strtolower($zip) == '{_unknown}') ? mb_strtolower($zip) : $zip;
			$address = $res['address'];
			$address2 = $res['address2'];
			$search = array('[address]', '[address2]', '[country]', '[state]', '[city]', '[zip]');
			$replace = array($address, $address2, $country, $state, $city, $zip);
			$output = str_replace($search, $replace, $ilconfig['globalfilters_locationformat']);
			$trim_output = trim($ilconfig['globalfilters_locationformat']);
			$collecting_special_charcter = substr($trim_output, (stripos($trim_output, ']') + 1), 1);
			$out = explode($collecting_special_charcter, $output);
			foreach ($out AS $key => $value)
			{
				$value = trim($value);
				if (!empty($value))
				{
					$out1[] = $value;
				}
			}
			return implode($collecting_special_charcter . ' ', $out1);
		}
		return '';
	}
    
	/**
	* Function to print the listing location bit based on a particular listing id.
	* 
	* @param       integer        listing id
	* @param       integer        short language identifier (i.e.: eng)
	* @param       string         supplied country name
	* @param       string         supplied state name
	* @param       string         supplied city name
	* @param       string         supplied zip code name
	*
	* @return      string         Returns HTML representation of the listing location bit
	*/
	function print_auction_location($pid = 0, $slng = 'eng', $country = '', $state = '', $city = '', $zip = '')
	{
		global $ilance, $ilconfig, $show;
		if (empty($slng))
		{
			$slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';
		}
		if ($pid > 0)
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.city, p.state, p.zipcode, l.location_" . $slng . " AS country
				FROM " . DB_PREFIX . "projects p
				LEFT JOIN " . DB_PREFIX . "locations l ON (l.locationid = p.countryid)
				WHERE p.project_id = '" . intval($pid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$country = empty($country) ? $res['country'] : $country;
				$state = empty($state) ? $res['state'] : $state;
				$city = empty($city) ? $res['city'] : $city;
				$zip = empty($zip) ? $res['zipcode'] : $zip;
				$zip = (mb_strtolower($zip) == '{_unknown}') ? mb_strtolower($zip) : $zip;
				$search = array ('[country]', '[state]', '[city]', '[zip]');
				$replace = array ($country, $state, $city, $zip);
				$output = str_replace($search, $replace, $ilconfig['globalfilters_locationformat']);
				$trim_output = trim($ilconfig['globalfilters_locationformat']);
				$collecting_special_charcter = substr($trim_output, (stripos($trim_output, ']') + 1), 1);
				$out = explode($collecting_special_charcter, $output);
				$out1 = array ();
				foreach ($out AS $key => $value)
				{
					$value = trim($value);
					if (!empty($value))
					{
						$out1[] = $value;
					}
				}
				return implode($collecting_special_charcter . ' ', $out1);
			}
		}
		else if (!empty($country) AND !empty($state) AND !empty($city) AND !empty($zip))
		{
			$search = array ('[country]', '[state]', '[city]', '[zip]');
			$replace = array ($country, $state, $city, $zip);
			$output = str_replace($search, $replace, $ilconfig['globalfilters_locationformat']);
			$trim_output = trim($ilconfig['globalfilters_locationformat']);
			$collecting_special_charcter = substr($trim_output, (stripos($trim_output, ']') + 1), 1);
			$out = explode($collecting_special_charcter, $output);
			$out1 = array ();
			foreach ($out AS $key => $value)
			{
				$value = trim($value);
				if (!empty($value))
				{
					$out1[] = $value;
				}
			}
			return implode($collecting_special_charcter . ' ', $out1);
		}
		return '';
	}
    
	/**
	* Function to print cities in a column table format
	* 
	* @param       string         state/provice name
	* @param       integer        number of columns to display (default 4)
	*
	* @return      string         Returns HTML representation of the listing location bit
	*/
	function print_cities($country = '', $state = '', $columns = 4)
	{
		global $ilance, $ilpage, $ilconfig;
		$html = '';
		$count = 0;
		$locationid = fetch_country_id($country, $_SESSION['ilancedata']['user']['slng']);
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "c.city
			FROM " . DB_PREFIX . "locations_cities c
			WHERE c.state = '" . $ilance->db->escape_string($state) . "'
				AND c.locationid = '" . intval($locationid) . "'
			GROUP BY c.city
			ORDER by c.city ASC
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$count++;
				$res['itemcount'] = 0;
				$cities[] = $res;
			}
		}
		$cols = 0;
		$counter = $count;
		$divideby = ceil($count / $columns);
		for ($i = 0; $i < $count; $i++)
		{
			$html .= '<td><div style="font-size:15px;" class="bluecat"><a href="' . HTTP_SERVER . $ilpage['search'] . '?mode=product&amp;sort=01&amp;country=' . handle_input_keywords($country) . '&amp;state=' . handle_input_keywords($state) . '&amp;city=' . handle_input_keywords($cities[$i]['city']) . '&amp;classifieds=1" nofollow="nofollow">' . handle_input_keywords($cities[$i]['city']) . '</a>&nbsp;<span class="litegray">(' . $cities[$i]['itemcount'] . ')</span></div></td>';
			if (($counter % $columns) == $divideby)
			{
				$html .= '</tr>';
			}
			$cols++;
			$counter++;
			if ($cols == $columns)
			{
				$html .= '</tr>';
				$cols = 0;
			}
		}
		return $html;
	}
    
	/**
	* Function to construct a country pull down menu
	*
	* @param       integer      country id
	* @param       string       country title
	* @param       string       country fieldname
	* @param       boolean      disable states pulldown (default false)
	* @param       string       states field name
	* @param       boolean      show worldwide as an option (default false)
	* @param       boolean      show usa/canada at top of list (default false)
	* @param       boolean      output option code as regions instead of countries (default false)
	* @param       string       states pull down container id
	* @param       boolean      only output states ISO codes (default false)
	* @param       string       
	* @param       string       
	* @param       string
	* @param       string       extra css for pull down menu
	* @param       boolean      show please select country name (default false)
	* @param       string       only show countries from this region (default all)
	* @param       integer      region id (if applicable)
	* @param       boolean      disable cities pulldown (default true)
	* @param       string       cities field name
	* @param       string       cities div id
	*
	* @return      string       HTML formatted country pulldown menu
	*/
	function construct_country_pulldown($countryid = 0, $countryname = '', $fieldname = 'country', $disablestates = false, $statesfieldname = 'state', $showworldwide = false, $usacanadafirst = false, $regionsonly = false, $statesdivid = 'stateid', $onlyiso = false, $statesfieldname2 = '', $fieldname2 = '', $statesdivid2 = '', $extracss = '', $showpleaseselect = false, $groupbyregion = false, $regionid = '', $disablecities = 1, $citiesfieldname = 'city', $citiesdivid = 'cityid')
	{
		global $ilance, $ilconfig, $phrase;
		$html = '<select style="' . $extracss . '" name="' . $fieldname . '" id="' . $fieldname . '"';
		$html .= (($disablestates == false)
			? ' onchange="print_states(\'' . $statesfieldname . '\', \'' . $fieldname . '\', \'' . $statesdivid . '\', \'' . intval($onlyiso) . '\', \'' . $extracss . '\', \'' . intval($disablecities) . '\', \'' . $citiesfieldname . '\', \'' . $citiesdivid . '\');' . ((!empty($statesfieldname2) AND !empty($fieldname2) AND !empty($statesdivid2)) ? 'print_states(\'' . $statesfieldname2 . '\', \'' . $fieldname2 . '\', \'' . $statesdivid2 . '\', \'' . intval($onlyiso) . '\', \'' . $extracss . '\', \'' . intval($disablecities) . '\', \'' . $citiesfieldname . '\', \'' . $citiesdivid . '\');' // this changes tax state pull down also when new country is selected  
			: '') . '"' : '') . ' class="select">';
		$extraquery = ($usacanadafirst) ? " AND locationid != '500' AND locationid != '330'" : '';
		$extraquery = ($regionsonly) ? "" : $extraquery;
		$regionquery = "";
		if ($groupbyregion)
		{
			$regionquery = "AND r.region_" . $_SESSION['ilancedata']['user']['slng'] . " = '" . $ilance->db->escape_string($regionid) . "'";
		}
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "l.locationid, l.location_" . $_SESSION['ilancedata']['user']['slng'] . " AS location, r.region_" . $_SESSION['ilancedata']['user']['slng'] . " AS region, l.cc
			FROM " . DB_PREFIX . "locations l
			LEFT JOIN " . DB_PREFIX . "locations_regions r ON (r.regionid = l.regionid)
			WHERE l.visible = '1'
			$extraquery
			$regionquery
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			if ($showpleaseselect)
			{
				$html .= '<option value="" disabled="disabled">{_country}</option><option value="">{_any_country}</option>';
			}
			if ($regionsonly == false)
			{
				$html .= ($showworldwide) ? '<option value=""></option><option value="{_worldwide}">{_worldwide}</option><option value="{_worldwide}">-------------------------------</option>' : '';
				$html .= ($usacanadafirst) ? '<option value="' . (($onlyiso == false) ? 'Canada' : 'CA') . '">Canada</option><option value="' . (($onlyiso == false) ? 'United States' : 'US') . '">United States</option><option value="" disabled="disabled">-------------------------------</option>' : '';
			}
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				if ($onlyiso == false)
				{
					$html .= ($regionsonly) ? '<option value="' . mb_strtolower(str_replace(' ', '_', $res['region'])) . '.' . $res['locationid'] . '"' : '<option value="' . $res['location'] . '"';
					$html .= (mb_strtolower(str_replace(' ', '_', $res['region']) . '.' . $res['locationid']) == $countryname) ? ' selected="selected"' : '';
					$html .= ($res['locationid'] == $countryid) ? ' selected="selected"' : '';
				}
				else
				{
					$html .= '<option value="' . $res['cc'] . '"';
					$html .= ($res['locationid'] == $countryid) ? ' selected="selected"' : '';
				}
				$html .= '>' . handle_input_keywords($res['location']) . '</option>';
			}
		}
		unset($sql);
		$html .= '</select>';
		return $html;
	}
    
	/**
	* Function to construct a state or province pull down menu
	*
	* @param       integer      country id
	* @param       string       state or province
	* @param       string       fieldname and/or id name
	* @param       boolean      disabled (default false)
	* @param       boolean      show please select as an option (default false)
	* @param       boolean      short form state codes only (default false)
	* @param       string       extra css to apply to pull down menu
	* @param       boolean      disable cities pulldown (default true)
	* @param       string       cities field name
	* @param       string       cities div id
	*
	* @return      string       HTML formatted state pulldown menu
	*/
	function construct_state_pulldown($locationid = '', $statename = '', $fieldname = 'state', $disabled = false, $showpleaseselect = false, $shortformonly = 0, $extracss = '', $disablecities = 1, $citiesfieldname = 'city', $citiesdivid = 'cityid')
	{
		global $ilance, $ilconfig, $phrase;
		$html = '<select style="' . $extracss . '" name="' . $fieldname . '" id="' . $fieldname . '"';
		$html .= (($disablecities == 0)
			? ' onchange="print_cities(\'' . $citiesfieldname . '\', \'' . $fieldname . '\', \'' . $citiesdivid . '\', \'' . $extracss . '\');"' : '') . ' class="select"' . ($disabled ? ' disabled="disabled"' : '') . '>';
		$defaultstate = '';
		if (!empty($locationid) AND !empty($statename))
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "locationid, state, sc
				FROM " . DB_PREFIX . "locations_states
				WHERE locationid = '" . intval($locationid) . "'
					AND (state = '" . $ilance->db->escape_string($statename) . "' OR sc = '" . $ilance->db->escape_string($statename) . "')
					AND visible = '1'
				ORDER BY state ASC
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$defaultstate = (($shortformonly AND !empty($res['sc'])) ? $res['sc'] : $res['state']);
			}
			unset($res);
		}
		else
		{
			if (defined('LOCATION') AND LOCATION == 'admin')
			{
				$defaultstate = (isset($statename) AND !empty($statename)) ? $statename : $ilconfig['registrationdisplay_defaultstate'];
			}
			else
			{
				$defaultstate = (isset($statename) AND !empty($statename)) ? $statename : '';
			}
		}
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "state, sc
			FROM " . DB_PREFIX . "locations_states
			WHERE locationid = '" . intval($locationid) . "'
			    AND visible = '1'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			if ($showpleaseselect)
			{
				$html .= '<option value="" disabled="disabled">{_state_or_province}</option><option value="">{_any_state_province}</option>';
			}
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$html .= '<option value="' . (($shortformonly AND !empty($res['sc'])) ? $res['sc'] : $res['state']) . '"';
				$html .= (isset($defaultstate) AND !empty($defaultstate) AND ($res['state'] == $defaultstate OR $res['sc'] == $defaultstate)) ? ' selected="selected"' : '';
				$html .= '>' . stripslashes(handle_input_keywords($res['state'])) . '</option>';
			}
		}
		else
		{
			$html .= '<option value="" disabled="disabled">{_state_or_province}</option>';
		}
		$html .= '</select>';
		return $html;
	}
	
	/**
	* Function to construct a city pulldown menu
	*
	* @param       string       state or province
	* @param       string       city fieldname and/or id name
	* @param       string       currently selected city name (optional)
	* @param       boolean      disabled select menu (default false)
	* @param       boolean      show please select as an option (default false)
	* @param       string       extra css to apply to pull down menu
	* @param       boolean      switch to input field if pull down has no values (default true)
	*
	* @return      string       HTML formatted city pull down menu
	*/
	function construct_city_pulldown($statename = '', $fieldname = 'city', $selected = '', $disabled = false, $showpleaseselect = false, $extracss = '', $switchtoinputfield = true)
	{
		global $ilance, $ilconfig, $phrase;
		if (mb_strlen($statename) == 2)
		{
			$statename = $this->fetch_state_from_abbreviation($statename);
		}
		$html = '<select style="' . $extracss . '" name="' . $fieldname . '" id="' . $fieldname . '" class="select"' . ($disabled ? ' disabled="disabled"' : '') . '>';
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "city
			FROM " . DB_PREFIX . "locations_cities
			WHERE state = '" . $ilance->db->escape_string($statename) . "'
				AND state != ''
				AND visible = '1'
			ORDER BY city ASC
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			if ($showpleaseselect)
			{
				$html .= '<option value="" disabled="disabled">{_city_or_village}</option><option value="">{_any_city_village}</option>';
			}
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$html .= '<option value="' . handle_input_keywords($res['city']) . '"';
				$html .= (($res['city'] == $selected)) ? ' selected="selected"' : '';
				$html .= '>' . stripslashes(handle_input_keywords($res['city'])) . '</option>';
			}
		}
		else
		{
			if ($switchtoinputfield)
			{
				$html = '<input type="text" name="' . $fieldname . '" value="' . handle_input_keywords($selected) . '" id="' . $fieldname . '" class="input" />';
				return $html;
			}
			else
			{
				$html .= '<option value="" disabled="disabled">{_city_or_village}</option>';
			}
		}
		$html .= '</select>';
		return $html;
	}
	
	/**
	* Function to print a user's country based on a supplied user id and a short language identifier to display the proper country name in the appropriate language
	*
	* @param       integer        user id
	* @param       string         short language identifier (default eng)
	*
	* @return      string         Returns the user's country name
	*/
	function print_user_country($userid, $slng = 'eng')
	{
		global $ilance, $phrase;
		$countryid = fetch_user('country', intval($userid));
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "location_$slng AS countryname
			FROM " . DB_PREFIX . "locations
			WHERE locationid = '" . $countryid . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			return stripslashes(handle_input_keywords($res['countryname']));
		}
		return '{_unknown}';
	}
    
	/**
	* Function to print a country name based on a supplied country id and a short language identifier to display the proper country name in the appropriate language
	*
	* @param       integer        country id
	* @param       string         short language identifier (default eng)
	* @param       boolean        short form output? (default false)
	*
	* @return      string         Returns the user's country name
	*/
	function print_country_name($countryid, $slng = 'eng', $shortform = false, $countryname = '')
	{
		global $ilance, $phrase;
		if (empty($slng))
		{
			$slng = 'eng';
		}
		$condition = "locationid = '" . intval($countryid) . "'";
		if (!empty($countryname))
		{
			$condition = "location_$slng = '" . $ilance->db->escape_string($countryname) . "'";
		}
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "location_$slng AS countryname, cc
			FROM " . DB_PREFIX . "locations
			WHERE $condition
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			if ($shortform)
			{
				return $res['cc'];
			}
			return $res['countryname'];
		}
		return '{_unknown}';
	}
    
	/**
	* Function to fetch a valid country id from the datastore based on a country code
	*
	* @param       string         country code
	*
	* @return      integer        Returns the country id
	*/
	function fetch_country_id_by_code($code = '')
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "locationid
			FROM " . DB_PREFIX . "locations
			WHERE cc = '" . $ilance->db->escape_string(strtoupper($code)) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			return $res['locationid'];
		}
		return '500';
	}
	
	/**
	* Function to convert a valid state or province into it's short form abbreviation
	*
	* @param       string         state name (ie: Florida)
	*
	* @return      string         Returns the short form abbreviation of a state or province
	*/
	function fetch_state_abbreviation($state = '')
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "sc
			FROM " . DB_PREFIX . "locations_states
			WHERE state = '" . $ilance->db->escape_string($state) . "'
				AND visible = '1'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			return $res['sc'];
		}
		return false;
	}
	
	/**
	* Function to convert a valid state or province into it's short form abbreviation
	*
	* @param       string         state name (ie: Florida)
	*
	* @return      string         Returns the short form abbreviation of a state or province
	*/
	function fetch_state_from_abbreviation($state = '')
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "state
			FROM " . DB_PREFIX . "locations_states
			WHERE sc = '" . $ilance->db->escape_string(mb_strtoupper($state)) . "'
				AND visible = '1'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			return $res['state'];
		}
		return false;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>