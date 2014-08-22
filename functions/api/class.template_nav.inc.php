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

if (!class_exists('template'))
{
    exit;
}

/**
* Template navigation class responsible for building and constructing the xml navigational menus in iLance
*
* @package      iLance\Template\Nav
* @version      4.0.0.8059
* @author       ILance
*/
class template_nav extends template
{
	/*
	* Function to print the left side nav that holds service, product, experts and portfolio category logic
	*
	* @param	string	        nav type (service, product, serviceprovider, portfolio)
	* @param        integer         category id
	* @param        boolean         show sub-cats under main cats? (default true)
	* @param        boolean         show both (service and product) categories under one another?
	* @param        boolean         show category count?
	* @param        boolean         show category filters?
	*/
	function print_left_nav($navtype = 'service', $cid = 0, $dosubcats = 1, $displayboth = 0, $showcount = 1, $showfilters = false)
	{
		global $ilconfig, $ilpage, $ilance, $phrase, $show, $categoryfinderhtml, $block, $blockcolor, $legend;
	
		($apihook = $ilance->api('print_left_nav_start')) ? eval($apihook) : false;
	
		$html = $categorytitle = $block = '';
		if (isset($displayboth) AND $displayboth)
		{
			if ($ilconfig['categoryboxorder'])
			{
				if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
				{
					$title = '{_services}';
					if ($cid > 0)
					{
						$categorytitle = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $cid);
					}
					$nav = $ilance->categories_parser->print_subcategory_columns(1, 'service', $dosubcats, $_SESSION['ilancedata']['user']['slng'], $cid, '', $showcount, 0, '', '', 1, '', true, true);
					$html1 = $this->fetch_template('leftnav_service.html');
					$html1 = $this->parse_hash('leftnav_service.html', array ('ilpage' => $ilpage), 0, $html1);
					$html1 = $this->parse_if_blocks('leftnav_service.html', $html1, true);
					$html1 = stripslashes($html1);
					$html1 = addslashes($html1);
					eval('$html .= "' . $html1 . '";');
					$html = stripslashes($html);
				}
				if ($ilconfig['globalauctionsettings_productauctionsenabled'])
				{
					$title = '{_items}';
					if ($cid > 0)
					{
						$categorytitle = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $cid);
					}
					$nav = $ilance->categories_parser->print_subcategory_columns(1, 'product', $dosubcats, $_SESSION['ilancedata']['user']['slng'], $cid, '', $showcount, 0, '', '', 1, '', true, true);
					$html2 = $this->fetch_template('leftnav_product.html');
					$html2 = $this->parse_hash('leftnav_product.html', array ('ilpage' => $ilpage), 0, $html2);
					$html2 = $this->parse_if_blocks('leftnav_product.html', $html2, true);
					$html2 = stripslashes($html2);
					$html2 = addslashes($html2);
					eval('$html .= "' . $html2 . '";');
					$html = stripslashes($html);
				}
			}
			else
			{
				if ($ilconfig['globalauctionsettings_productauctionsenabled'])
				{
					$title = '{_items}';
					if ($cid > 0)
					{
						$categorytitle = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $cid);
					}
					$nav = $ilance->categories_parser->print_subcategory_columns(1, 'product', $dosubcats, $_SESSION['ilancedata']['user']['slng'], $cid, '', $showcount, 0, '', '', 1, '', true, true);
					$html2 = $this->fetch_template('leftnav_product.html');
					$html2 = $this->parse_hash('leftnav_product.html', array ('ilpage' => $ilpage), 0, $html2);
					$html2 = $this->parse_if_blocks('leftnav_product.html', $html2, true);
					$html2 = stripslashes($html2);
					$html2 = addslashes($html2);
					eval('$html .= "' . $html2 . '";');
					$html = stripslashes($html);
				}
				if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
				{
					$title = '{_services}';
					if ($cid > 0)
					{
						$categorytitle = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $cid);
					}
					$nav = $ilance->categories_parser->print_subcategory_columns(1, 'service', $dosubcats, $_SESSION['ilancedata']['user']['slng'], $cid, '', $showcount, 0, '', '', 1, '', true, true);
					$html1 = $this->fetch_template('leftnav_service.html');
					$html1 = $this->parse_hash('leftnav_service.html', array ('ilpage' => $ilpage), 0, $html1);
					$html1 = $this->parse_if_blocks('leftnav_service.html', $html1, true);
					$html1 = stripslashes($html1);
					$html1 = addslashes($html1);
					eval('$html .= "' . $html1 . '";');
					$html = stripslashes($html);
				}
			}
		}
		else
		{
			$blockcolor = 'yellow';
			$block = '';
			if ($navtype == 'service' OR $navtype == 'serviceprovider' OR $navtype == 'portfolio' OR $navtype == 'wantads' OR $navtype == 'stores' OR $navtype == 'storesmain')
			{
				$nav = $ilance->categories_parser->print_subcategory_columns(1, $navtype, $dosubcats, $_SESSION['ilancedata']['user']['slng'], $cid, '', $showcount, 0, '', '', 1, '', true, true);
				if ($navtype == 'portfolio' OR $navtype == 'wantads' OR $navtype == 'serviceprovider')
				{
					$blockcolor = 'gray';
					$block = '3';
				}
				else if ($navtype != 'stores' AND $navtype != 'storesmain')
				{
					$blockcolor = 'gray';
					$block = '3';
				}
			}
			else
			{
				$nav = $ilance->categories_parser->print_subcategory_columns(1, $navtype, $dosubcats, $_SESSION['ilancedata']['user']['slng'], $cid, '', $showcount, 0, '', '', 1, '', true, true);
			}
			$title = '{_categories}';
			$categorytitle = '';
			if ($cid > 0)
			{
				$categorytitle = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $cid);
			}
			$html = $this->fetch_template('leftnav_categories.html');
			$html = $this->parse_hash('leftnav_categories.html', array ('ilpage' => $ilpage), 0, $html);
			$html = $this->parse_if_blocks('leftnav_categories.html', $html, true);
			$html = stripslashes($html);
			$html = addslashes($html);
			$searchfilters = ($showfilters) ? $this->print_search_nav($navtype, $cid) : '';
			eval('$html = "' . $html . '";');
			$html = stripslashes($html);
		}
	
		($apihook = $ilance->api('print_left_nav_end')) ? eval($apihook) : false;
	
		return $html;
	}
    
	/*
	* Function to print the left side search options nav that holds service, product and experts category logic
	*
	* @param	string	        nav type (service, product, serviceprovider, portfolio)
	* @param        integer         category id
	*/
	function print_search_nav($navtype = '', $cid = 0)
	{
		global $ilance, $ilpage, $ilconfig, $show;
		$html = '';
		$d = $ilconfig['globalserver_distanceresults'];
		$values = array('' => '-', '5' => '5 '.$d, '10' => '10 '.$d, '20' => '20 '.$d, '50' => '50 '.$d, '100' => '100 '.$d, '250' => '250 '.$d, '500' => '500 '.$d, '1000' => '1000 '.$d, '2000' => '2000 '.$d, '5000' => '5000 '.$d, '10000' => '10000 '.$d);
		$radius = (isset($ilance->GPC['radius']) AND !empty($ilance->GPC['radius'])) ? $ilance->GPC['radius'] : '';
		$vars['radius_pulldown'] = construct_pulldown('radius', 'radius', $values, $radius, 'style="font-family: verdana" title="{_radius}"');
		if ($navtype == 'product')
		{
			$vars['currency_symbol_left'] = $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_left'];
			$vars['currency_symbol_right'] = $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_right'];
			$html = $this->fetch_template('leftnav_searchoptions_product.html');
			$html = $this->parse_hash('leftnav_searchoptions_product.html', array ('ilpage' => $ilpage, 'vars' => $vars), $parseglobals = 0, $html);
			$html = $this->handle_template_hooks('leftnav_searchoptions_product.html', $html);
			$html = $this->parse_if_blocks('leftnav_searchoptions_product.html', $html, $addslashes = true);
		}
		else if ($navtype == 'service')
		{
			$html = $this->fetch_template('leftnav_searchoptions_service.html');
			$html = $this->parse_hash('leftnav_searchoptions_service.html', array ('ilpage' => $ilpage, 'vars' => $vars), $parseglobals = 0, $html);
			$html = $this->handle_template_hooks('leftnav_searchoptions_service.html', $html);
			$html = $this->parse_if_blocks('leftnav_searchoptions_service.html', $html, $addslashes = true);
		}
		else if ($navtype == 'serviceprovider')
		{
			$html = $this->fetch_template('leftnav_searchoptions_experts.html');
			$html = $this->parse_hash('leftnav_searchoptions_experts.html', array ('ilpage' => $ilpage, 'vars' => $vars), $parseglobals = 0, $html);
			$html = $this->handle_template_hooks('leftnav_searchoptions_experts.html', $html);
			$html = $this->parse_if_blocks('leftnav_searchoptions_experts.html', $html, $addslashes = true);
		}
		$html = stripslashes($html);
		return $html;
	}
    
	/*
	* Function for processing an xml nav menu within ILance
	*
	* @param       array
	* @param       array
	* @param       string       xml nav type to process (ADMIN/CLIENT/CLIENT_TOPNAV)
	*/
	function process_cpnav_xml($a, $e, $type = 'CLIENT')
	{
		$lang_code = $current_nav_group = $version = '';
		$navgroupdata = $navoptions = array();
		$counter = count($a);
		for ($i = 0; $i < $counter; $i++)
		{
			// #### ADMIN TOP NAV ##############
			if ($type == 'ADMIN')
			{
				if ($a[$i]['tag'] == $type . 'NAVGROUPS')
				{
					if ($a[$i]['type'] == 'complete' OR $a[$i]['type'] == 'open')
					{
						$lang_code = $a[$i]['attributes']['LANGUAGECODE'];
					}
				}
				else if ($a[$i]['tag'] == $type . 'NAVGROUP')
				{
					if ($a[$i]['type'] == 'open' OR $a[$i]['type'] == 'complete')
					{
						if ($type == 'ADMIN')
						{
							$current_nav_group = mb_strtolower(str_replace(' ', '_', trim($a[$i]['attributes']['PHRASE'])));
							$navgroupdata[] = array (
							    $current_nav_group, // 0
							    trim($a[$i]['attributes']['PHRASE']), // 1
							    trim($a[$i]['attributes']['LINK']), // 2
							    trim($a[$i]['attributes']['SEOLINK']), // 3
							    trim($a[$i]['attributes']['CONFIG']), // 4
							    trim($a[$i]['attributes']['PERMISSION1']), // 5
							    trim($a[$i]['attributes']['PERMISSION2']), // 6
							    trim($a[$i]['attributes']['SORT']), // 7
							);
						}
					}
				}
				else if ($a[$i]['tag'] == 'OPTION')
				{
					if ($a[$i]['type'] == 'open' OR $a[$i]['type'] == 'complete')
					{
						if ($type == 'ADMIN')
						{
							$navoptions[] = array (
								$current_nav_group, // 0
								trim($a[$i]['attributes']['PHRASE']), // 1
								trim($a[$i]['attributes']['LINK']), // 2
								trim($a[$i]['attributes']['SEOLINK']), // 3
								trim($a[$i]['attributes']['CONFIG']), // 4
								trim($a[$i]['attributes']['PERMISSION1']), // 5
								trim($a[$i]['attributes']['PERMISSION2']), // 6
								trim($a[$i]['attributes']['SORT']), // 7
							);
						}
					}
				}
			}
		}
		$result = array (
			'lang_code' => $lang_code,
			'navarray' => $navgroupdata,
			'navoptions' => $navoptions,
			'version' => $version,
		);
		return $result;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>