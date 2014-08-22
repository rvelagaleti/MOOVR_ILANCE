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
if (!class_exists('categories'))
{
	exit;
}

/**
* Category parser class v4 to perform the majority of category parsing functions within ILance.
*
* @package      iLance\Categories\ParserV4
* @version      4.0.0.8059
* @author       ILance
*/
class categories_parser_v4 extends categories
{
	var $cacheid = '';
	var $showcatid = false;
        /**
	* Function to print the main subcategory columns of a particular category being viewed or selected
	*
	* @param	integer	        number of columns to display (default 1)
	* @param        string          category type (service, product, serviceprovider, portfolio, stores, wantads)
	* @param        string          short language code (default is eng)
	* @param        integer         category id
	*/
        function print_category_columns($columns = 1, $cattype = 'product', $slng = 'eng', $cid = 0)
        {
                global $ilance, $phrase, $ilconfig, $ilpage, $show, $headinclude;
                $ilance->timer->start();
		$url = ($ilconfig['globalauctionsettings_seourls']) ? print_seo_url($ilconfig['productcatmapidentifier']) : $ilpage['merch'] . '?cmd=listings';
		$html = '<li><a class="first-select" href="' . $url . '" data-catid="">{_all_categories}</a></li>';
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "cid, title_$slng AS title
			FROM " . DB_PREFIX . "categories
			WHERE cattype = '$cattype'
				AND visible = '1'
				AND level <= '1'
			ORDER BY sort ASC
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$html .= '<li><a href="#" data-catid="' . $res['cid'] . '" title="' . handle_input_keywords($res['title']) . '">' . handle_input_keywords($res['title']) . '</a></li>';
			}
		}
                $ilance->timer->stop();
		DEBUG("print_category_columns(\$columns = $columns, \$cattype = $cattype, \$slng = $slng, \$cid = $cid) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
                return $html;
        }
	function print_category_navigation($cattype = 'product', $slng = 'eng', $cid = 0, $hideafter = 9, $columns = 3, $columndividerafter = 12)
        {
                global $ilance, $phrase, $ilconfig, $ilpage, $show;
                $ilance->timer->start();
		$html = '';
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "cid, title_$slng AS title, catimagehero, auctioncount
			FROM " . DB_PREFIX . "categories
			WHERE cattype = '$cattype'
				AND visible = '1'
				AND level <= '1'
			ORDER BY sort ASC, title_$slng ASC
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$items = 0;
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$items++;
				$catbitcount = ''; //($ilconfig['globalfilters_enablecategorycount'] AND $ilconfig['categorymainsingleleftnavcount'] AND isset($res['auctioncount'])) ? '&nbsp;<span class="smaller gray" style="direction:' . (($ilconfig['template_textalignment'] == 'left') ? 'ltr' : 'rtl') . ';unicode-bidi:embed">(' . number_format($res['auctioncount']) . ')</span>' : '';
				$res['categorynavdropdown'] = $this->print_subcategory_columns($columns, $columndividerafter, $cattype, 0, $_SESSION['ilancedata']['user']['slng'], $res['cid'], '', $ilconfig['categorymainsingleleftnavcount'], 0, 0, '', false, false, false, $res['catimagehero']);
				$res['class'] = ($items > $hideafter) ? ' class="hide"' : '';
				$html .= '<li' . $res['class'] . ' aria-haspopup="true"><a href="' . (($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productcatmapplain', $res['cid'], 0, $res['title'], '', 0, '', 0, 0) : $ilpage['merch'] . '?cmd=listings&cid=' . $res['cid']) . '" title="' . handle_input_keywords($res['title']) . '" role="menuitem" class="nav-menu-link">' . handle_input_keywords($res['title']) . '</a>' . $catbitcount . $res['categorynavdropdown'] . '</li>';
			} 
		}
                $ilance->timer->stop();
		DEBUG("print_category_navigation(\$cattype = $cattype, \$slng = $slng, \$cid = $cid) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
                return $html;
        }
	/**
        * Function to fetch categories recursively
        *
        * @param       	integer      	parent id
        * @param       	integer      	level (default 1)
        * @param       	string       	category type field
        * @param       	string       	category type
        * @param       	string       	category database table
        * @param       	string       	seo category type
        * @param       	array        	detail page to attach links
        * @param       	boolean      	show category counters
        * @param       	string       	short language identifier
        * @param       	string       	category id field name
        * @param       	string       	category title
        * @param       	boolean      	is category map?
        * @param       	integer      	subcategory depth
        * @param       	integer      	number of columns to display
        * @param       	integer         number of items per column (default 11)
        * @param       	integer      	temp counter holder
        * @param       	string       	temp string holder for hidden links used in the more link logic
        * @param       	string       	temp string holder for more link
        * @param       	string       	category cache array
        * @param       	boolean      	show gray border under each category row (default true)
        * @param        boolean         show new listings icon (default false)
        *
        * @return      	string       	Returns HTML formatted table with category results
        */
        function fetch_recursive_categories($parentid = 0, $level = 1, $ctypefield = '', $ctype = '', $dbtable = '', $seotype = '', $detailpage = '', $showcount = 1, $slng = 'eng', $cidfield = '', $cidtitle = '', $iscatmap = 0, $subcatdepth = 0, $displaycolumns = 3, $itemspercolumn = 12, $tempcount = 0, $hidden_html = '', $show_html = '', $categorycache = '', $showcatdivider = true, $shownewlistingicon = false)
        {
        	global $ilance, $recursive_html, $hidden_html, $show_html, $ilconfig, $storeid, $phrase;
        	$ilance->timer->start();
		$cattype = ($ctype == 'product') ? 'productcategorymap' : 'servicecategorymap';
		if (empty($categorycache))
		{
			$max_level = 0;
			if ($parentid == 0)
			{
				$max_level = $subcatdepth;
			}
			$this->cats = $this->build_array($cattype, $slng, 0, false, '', '', 0, -1, $max_level, 0); 
		}
		else
		{
			$this->cats = $categorycache;
		}
		$cols = 0;
        	$numrows = count($this->cats);
        	$divideby = ceil($numrows / $displaycolumns);
        	$html = array();
        	if ($level == 1)
        	{
        		$count = 0;
        	}
		$ids_string = $this->fetch_children_ids('all', $ctype, " AND parentid = '$parentid'");
		$ids = explode(",", $ids_string);
		if (is_array($ids) AND count($ids) > 0)
		{
			$recursive_html .= '<div class="col"><ul>';
			foreach ($ids AS $key => $i)
			{
				if (!empty($i) AND $this->cats[$i]['visible'] AND $this->cats[$i]['parentid'] == $parentid)
				{
					$catbitcount = ($ilconfig['globalfilters_enablecategorycount'] AND isset($showcount) AND $showcount AND isset($this->cats[$i]['auctioncount'])) ? '&nbsp;<span class="smaller gray" style="direction:' . (($ilconfig['template_textalignment'] == 'left') ? 'ltr' : 'rtl') . ';unicode-bidi:embed">(' . number_format($this->cats[$i]['auctioncount']) . ')</span>' : '';
					$catimage = (empty($this->cats[$i]['catimage']) ? '' : '<img style="vertical-align:middle;padding-bottom:5px;" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'categoryicons/' . $this->cats[$i]['catimage']. '" border="0" alt="" />&nbsp;');
					$newicon = ($shownewlistingicon) ? $this->print_category_newicon($this->cats[$i]['lastpost'], $this->cats[$i]['cid'], $this->cats[$i]['cattype']) : '';
					$catid = ($this->showcatid == true) ? '#' . $this->cats[$i]['cid'] : '';
					$url = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productcatmapplain', $this->cats[$i]['cid'], 0, handle_input_keywords($this->cats[$i]['title']), '', 0, '', 0, 0) : $detailpage . '?cid=' . $this->cats[$i]['cid'];
					$recursive_html .= '<li><a href="' . $url . '" title="' . handle_input_keywords($this->cats[$i]['title']) . '">' . $catimage . handle_input_keywords($this->cats[$i]['title']) . '</a></li>';
					$count++;
					if ($count % $itemspercolumn == 0)
					{
						$cols++;
						if ($cols >= $displaycolumns)
						{
							$recursive_html .= '</ul><div class="clear"></div></div><div class="col hide"><ul>';
						}
						else
						{
							$recursive_html .= '</ul><div class="clear"></div></div><div class="col"><ul>';
						}
					}
				}
			}
			if ($parentid <= 0)
			{
				$url = ($ilconfig['globalauctionsettings_seourls']) ? HTTP_SERVER . 'categories/items' : $detailpage . '?cmd=listings';
				$recursive_html .= '</ul>' . ((!empty($this->cats[$parentid]['catimagehero'])) ? '<i><a href="' . (empty($this->cats[$parentid]['catimageherourl']) ? 'javascript:;' : $this->cats[$parentid]['catimageherourl']) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'categoryheros/' . $this->cats[$parentid]['catimagehero'] . '" /></a></i>' : '') . '</div><div class="clear"></div><div class="row-link"><a href="' . $url . '" title="{_all_categories}">{_all_categories}</a></div>';
			}
			else
			{
				$url = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productcatmapplain', $this->cats[$parentid]['cid'], 0, handle_input_keywords($this->cats[$parentid]['title']), '', 0, '', 0, 0) : $detailpage . '?cid=' . $this->cats[$parentid]['cid'];
				$recursive_html .= '</ul>' . ((!empty($this->cats[$parentid]['catimagehero'])) ? '<i><a href="' . (empty($this->cats[$parentid]['catimageherourl']) ? 'javascript:;' : $this->cats[$parentid]['catimageherourl']) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'categoryheros/' . $this->cats[$parentid]['catimagehero'] . '" /></a></i>' : '') . '</div><div class="clear"></div><div class="row-link"><a href="' . $url . '" title="{_all_categories} {_in} ' . handle_input_keywords($this->cats[$parentid]['title']) . '">{_all_categories} {_in} ' . handle_input_keywords($this->cats[$parentid]['title']) . '</a></div>';
			}
		}
        	$ilance->timer->stop();
        }
	
	/**
	* Function to print the main subcategory columns of a particular category being viewed or selected
	*
	* @param	integer	        number of columns to display (default 1)
	* @param        integer         number of items per column (default 11)
	* @param        string          category type (service, product, serviceprovider, portfolio, stores, wantads)
	* @param        bool            show subcategories?
	* @param        string          short language code (default is eng)
	* @param        integer         category id
	* @param        string          extra (optional)
	* @param        boolean         show category counts? (default yes)
	* @param        boolean         showing category map? (default no)
	* @param        integer         subcategory depth level to display (default 0 = root)
	* @param        string          cache id (to prevent similar cache pages) (default blank)
	* @param        boolean         show the current selected category (default true)
	* @param        boolean         show gray border under each category row (default true)
	* @param        boolean         show new listings icon status (default false)
	*/
        function print_subcategory_columns($columns = 1, $itemspercolumn = 12, $cattype = 'product', $dosubcats = 1, $slng = 'eng', $cid = 0, $extra = '', $showcount = 1, $iscatmap = 0, $subcatdepth = 0, $cacheid = '', $showcurrentcat = true, $showcatdivider = true, $shownewlistingicon = false, $catimagehero = '')
        {
                global $ilance, $phrase, $ilconfig, $ilpage, $show, $categoryfinderhtml, $sqlquery, $recursive_html, $categorycache;
                $ilance->timer->start();
		if (!empty($cacheid))
		{
			$cacheid = '_' . $cacheid . '_' . $itemspercolumn;
		}
		$this->cacheid = $cacheid;
		$accepted = array('product');
		$html = $extraquery = $join = $join2 = $leftjoin = $leftjoin2 = $leftjoinsubquery = $leftjoinsubquery2 = $extracount = $extracount2 = $extranodecount = $extranodecount2 = $ctypefield = $ctype = $dbtable = $seotype = $detailpage = $cidfield = $cidtitle = '';
		
		($apihook = $ilance->api('print_subcategory_columns_v4_top_start')) ? eval($apihook) : false;
		
                if (isset($cattype) AND in_array($cattype, $accepted))
                {
			// #### defaults #######################################
			$dbtable = DB_PREFIX . 'categories';
			$cidfield = 'cid';
			$cidtitle = "title_$slng";
			$cidparentid = 'parentid';
			$ctypefield = 'cattype';
			$cidcanpost = 'canpost';
			$cidlastpost = 'lastpost';
			$cidviews = 'views';
			$cidlevel = 'level';
			$counttype = 'auctioncount';
			$catimagefield = 'catimage';
			$auctionid = 0;
			$show['noquery'] = false;
			$dbtable2 = DB_PREFIX . "projects";
			$detailpage = $ilpage['merch'];
			$seotype = ($iscatmap) ? 'productcatmap' : 'productcat';
			$seotype2 = 'productcatmap';
			$ctype = 'product';
                }
		// #### category map caching enabled ###########################
		if ($ilconfig['categorymapcache'])
		{
			// #### cache default ##################################
			$cache['filename'] = $cattype . '_' . $columns . '_cols' . $cacheid . '_catmap_dropdown_cid_' . $cid . '_' . $slng . '.html';
			$cache['filepath'] = DIR_TMP . DIR_DATASTORE_NAME . '/' . $cache['filename'];
			$cache['unique_name'] = '';
			// #### check if we need to rewrite cache template
			$write_html = false;
			if (file_exists($cache['filepath']))
			{
				$lastmod = filemtime($cache['filepath']);
				if (($lastmod + $ilconfig['categorymapcachetimeout'] * 60) < time())
				{
					// #### cache template is outdated
					$write_html = true;
				}
			}
			else 
			{
				// #### the cache template file does not exist! we need to generate something!
				$write_html = true;
			}
			if ($write_html)
			{
				include_once(DIR_CORE . 'functions_categories_ajax.php');
				$cache['unique_name'] = rand(0, 100000);
				while (file_exists(DIR_TMP . $cache['unique_name']));
				{                                    
					$f = fopen(DIR_TMP . $cache['unique_name'], 'w');
					if ($f === false)
					{
						@unlink(DIR_TMP . $cache['unique_name']);
					}
					else 
					{
						global $recursive_html;
						$recursive_html = '';
						if (is_last_category($cid) == false)
						{
							$recursive_html = '<div class="dropdown' . ((!empty($catimagehero)) ? ' increase' : '') . '">';
							$this->fetch_recursive_categories($cid, 1, $ctypefield, $ctype, $dbtable, $seotype, $detailpage, $showcount, $slng, $cidfield, $cidtitle, $iscatmap, $subcatdepth, $columns, $itemspercolumn, 0, '', '', '', $showcatdivider, $shownewlistingicon);
							$recursive_html .= '</div>';
						}
						$html = $recursive_html;
						fwrite($f, $html);
						fclose($f);
						@unlink(DIR_TMP . $cache['filename']);
						@rename(DIR_TMP . $cache['unique_name'], DIR_TMP . DIR_DATASTORE_NAME . '/' . $cache['filename']);
						@unlink(DIR_TMP . $cache['unique_name']);
					}
				}
			}
			else
			{
				// #### template cache exists - read it
				$html = file_get_contents($cache['filepath']);
			}
		}
		// #### category map caching disabled ##########################
		else
		{
			include_once(DIR_CORE . 'functions_categories_ajax.php');
			global $recursive_html;
			$recursive_html = '';
			if (is_last_category($cid) == false)
			{
				$recursive_html = '<div class="dropdown' . ((!empty($catimagehero)) ? ' increase' : '') . '">';
				$this->fetch_recursive_categories($cid, 1, $ctypefield, $ctype, $dbtable, $seotype, $detailpage, $showcount, $slng, $cidfield, $cidtitle, $iscatmap, $subcatdepth, $columns, $itemspercolumn, 0, '', '', '', $showcatdivider, $shownewlistingicon);                                
				$recursive_html .= '</div>';
			}
			$html = $recursive_html;
		}
                $ilance->timer->stop();
		DEBUG("print_subcategory_columns(\$columns = $columns, \$itemspercolumn = $itemspercolumn, \$cattype = $cattype, \$cid = $cid, \$dosubcats = $dosubcats, \$slng = $slng, \$cid = $cid, \$extra = $extra, \$showcount = $showcount, \$iscatmap = $iscatmap, \$subcatdepth = $subcatdepth, \$cacheid = $cacheid, \$showcurrentcat = $showcurrentcat, \$showcatdivider = $showcatdivider) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
                return $html;
        }
	
	function print_root_categories_ul($limit = 15, $breakafter = 5, $cattype = 'service')
	{
		global $ilance, $ilconfig, $ilpage;
		$html = '';
		$slng = $_SESSION['ilancedata']['user']['slng'];
		$sql = $ilance->db->query("
			SELECT cid, title_$slng AS title, catimage
			FROM " . DB_PREFIX . "categories
			WHERE parentid = '0'
				AND cattype = '" . $ilance->db->escape_string($cattype) . "'
				AND visible = '1'
			ORDER BY title_$slng ASC
			LIMIT $limit
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$counter = 1;
			while ($row = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$html .= '<ul>';
				if ($counter % $breakafter == 0)
				{
					$counter = 1;
					$html .= '</ul><ul>';
				}
				$detailpage = (($cattype == 'product') ? $ilpage['merch'] : $ilpage['rfp']);
				$url = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('servicecatmapplain', $row['cid'], 0, handle_input_keywords($row['title']), '', 0, '', 0, 0) : $detailpage . '?cid=' . $row['cid'];
				$catimage = (empty($row['catimage']) ? '' : '<img style="vertical-align:middle;padding-bottom:5px;" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'categoryicons/' . $row['catimage']. '" border="0" alt="" />&nbsp;');
				$html .= '<li>' . $catimage . '<a href="' . $url . '" title="' . handle_input_keywords($row['title']) . '">' . $row['title'] . '</a></li>';
				$counter++;
				$html .= '</ul>';
			}
		}
		return $html;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>