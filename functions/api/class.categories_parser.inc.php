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
* Category parser class to perform the majority of category parsing functions within ILance.
*
* @package      iLance\Categories\Parser
* @version      4.0.0.8059
* @author       ILance
*/
class categories_parser extends categories
{
	var $cacheid = '';
	var $showcatid = false;
	
        /**
        * Function to fetch categories recursively
        *
        * @param       integer      parent id
        * @param       integer      level (default 1)
        * @param       string       category type field
        * @param       string       category type
        * @param       string       category database table
        * @param       string       seo category type
        * @param       array        detail page to attach links
        * @param       boolean      show category counters
        * @param       string       short language identifier
        * @param       string       category id field name
        * @param       string       category title
        * @param       boolean      is category map?
        * @param       string       parent category title link style
        * @param       string       child category title link style
        * @param       integer      subcategory depth
        * @param       integer      number of columns to display
        * @param       integer      temp counter holder
        * @param       string       temp string holder for hidden links used in the more link logic
        * @param       string       temp string holder for more link
        * @param       string       category cache array
        * @param       boolean      show gray border under each category row (default true)
        *
        * @return      string       Returns HTML formatted table with category results
        */
        function fetch_recursive_categories($parentid = 0, $level = 1, $ctypefield = '', $ctype = '', $dbtable = '', $seotype = '', $detailpage = '', $showcount = 1, $slng = 'eng', $cidfield = '', $cidtitle = '', $iscatmap = 0, $parentstyle = '', $childstyle = '', $subcatdepth = 0, $displaycolumns = 3, $tempcount = 0, $hidden_html = '', $show_html = '', $categorycache = '', $showcatdivider = true)
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
		$ids_string = $ilance->categories->fetch_children_ids('all', $ctype, " AND parentid = '$parentid'");
		$ids = explode(",", $ids_string);
		if (is_array($ids) AND count($ids) > 0)
		{
			foreach ($ids as $key => $i)
			{
				if (!empty($i) AND $this->cats[$i]['visible'] AND $this->cats[$i]['parentid'] == $parentid)
				{
					$catbitcount = ($ilconfig['globalfilters_enablecategorycount'] AND isset($showcount) AND $showcount AND isset($this->cats[$i]['auctioncount'])) ? '&nbsp;<span class="smaller gray" style="direction:' . (($ilconfig['template_textalignment'] == 'left') ? 'ltr' : 'rtl') . ';unicode-bidi:embed">(' . number_format($this->cats[$i]['auctioncount']) . ')</span>' : '';
					$catimage = (empty($this->cats[$i]['catimage']) ? '' : '<img style="vertical-align:middle;padding-bottom:5px;" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'categoryicons/' . $this->cats[$i]['catimage']. '" border="0" alt="" />&nbsp;');
					$newicon = $this->print_category_newicon($this->cats[$i]['lastpost'], $this->cats[$i]['cid'], $this->cats[$i]['cattype']);
					$catid = (($this->showcatid == true AND $this->cats[$i]['canpost']) ? '<span class="smaller litegray">(#' . $this->cats[$i]['cid'] . ')</span>' : '');
					// #### main parent categories #################
					if ($level == 1)
					{
						// #### build our first table row ######
						if ($count > 0)
						{
							$recursive_html .= ($count % $displaycolumns == 0) ? (($showcatdivider) ? '<tr><td colspan="' . $displaycolumns . '"><div style="background-color:#eeeeee; height:1px; width:100%; margin-top:6px; margin-bottom:6px"></div></td></tr><tr>' : '') : '';
						}
						// #### build our first table column ###
						$recursive_html .= '<td width="25%" valign="top">';
						// #### build parent categories ########
						$recursive_html .= ($ilconfig['globalauctionsettings_seourls'])
							? '<div style="padding-top:7px;padding-left:' . $this->fetch_level_padding($level) . 'px"><span style="' . $parentstyle . '" class="blueonly">' . $catimage . construct_seo_url($seotype, $this->cats[$i]['cid'], $storeid, handle_input_keywords(stripslashes($this->cats[$i]['title'])), '', 0, '', 0, 0) . $catbitcount . ' ' . $newicon . $catid . '</span></div>'
							: '<div style="padding-top:7px;padding-left:' . $this->fetch_level_padding($level) . 'px"><span style="' . $parentstyle . '" class="blueonly">' . $catimage . '<a href="' . $detailpage . '?cid=' . $this->cats[$i]['cid'] . '">' . handle_input_keywords(stripslashes($this->cats[$i]['title'])) . '</a></span>' . $catbitcount . ' ' . $newicon . $catid . '</div>';
						if (isset($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0)
						{
							// #### build category questions #######
							$recursive_html .= ($ilconfig['globalauctionsettings_catmapgenres'] AND $this->cats[$i]['auctioncount'] > 0 AND $this->cacheid != '_topnav') ? $this->print_searchable_questions($this->cats[$i]['cid'], $showcount, $level, $level, true, $this->cats[$i]['cattype'], $this->cats[$i]['title']) : '';
						}
					}
					// #### children categories ####################
					else if ($level <= $subcatdepth)
					{
						$tempcount++;
						// #### hold and store our visible categories
						if ($tempcount <= $ilconfig['globalauctionsettings_catcutoff'])
						{
							$recursive_html .= ($ilconfig['globalauctionsettings_seourls'])
								? '<div style="padding-top:7px;padding-left:' . $this->fetch_level_padding($level) . 'px" class="blueonly">' . $catimage . construct_seo_url($seotype, $this->cats[$i]['cid'], $storeid, handle_input_keywords(stripslashes($this->cats[$i]['title'])), '', 0, '', 0, 0) . $catbitcount . ' ' . $newicon . $catid . '</div>'
								: '<div style="padding-top:7px;padding-left:' . $this->fetch_level_padding($level) . 'px" class="blueonly">' . $catimage . '<a href="' . $detailpage . '?cid=' . $this->cats[$i]['cid'] . '">' . handle_input_keywords(stripslashes($this->cats[$i]['title'])) . '</a>' . $catbitcount . ' ' . $newicon . $catid . '</div>';
						}
						// #### hold and store our hidden categories
						else
						{
							$hidden_html .= ($ilconfig['globalauctionsettings_seourls'])
								? '<div style="padding-top:7px;padding-left:' . $this->fetch_level_padding($level) . 'px" class="blueonly">' . $catimage . construct_seo_url($seotype, $this->cats[$i]['cid'], $storeid, handle_input_keywords(stripslashes($this->cats[$i]['title'])), '', 0, '', 0, 0) . $catbitcount . ' ' . $newicon . $catid . '</div>'
								: '<div style="padding-top:7px;padding-left:' . $this->fetch_level_padding($level) . 'px" class="blueonly">' . $catimage . '<a href="' . $detailpage . '?cid=' . $this->cats[$i]['cid'] . '">' . handle_input_keywords(stripslashes($this->cats[$i]['title'])) . '</a>' . $catbitcount . ' ' . $newicon . $catid . '</div>';
						}
					}
					// #### category cutoff logic ##################
					if ($tempcount > $ilconfig['globalauctionsettings_catcutoff'])
					{
						$templevel = ($level > 2) ? ($level - 1) : ($level);
						// #### build our "more/less" category linkage presentation
						$show_html = "<div id=\"showmorecats_" . $this->cats[$i]['cid'] . "\" style=\"" . (!empty($ilcollapse["showmorecats_" . $this->cats[$i]['cid'] . ""]) ? $ilcollapse["showmorecats_" . $this->cats[$i]['cid'] . ""] : 'display: none;') . "\">$hidden_html</div>" . '<div style="padding-left:' . $this->fetch_level_padding($templevel) . 'px; padding-bottom:6px;padding-top:7px"><span class="blue"><a href="javascript:void(0)" onclick="toggle_more(\'showmorecats_' . $this->cats[$i]['cid'] . '\', \'moretext_' . $this->cats[$i]['cid'] . '\', \'' . '{_more}' . '\', \'' . '{_less}' . '\', \'showmoreicon_' . $this->cats[$i]['cid'] . '\')"><span id="moretext_' . $this->cats[$i]['cid'] . '" style="font-weight:bold;text-decoration:none">' . (!empty($ilcollapse["showmorecats_" . $this->cats[$i]['cid'] . ""]) ? '{_less}' : '{_more}') . '</span></a></span>&nbsp;<img id="showmoreicon_' . $this->cats[$i]['cid'] . '" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/' . (!empty($ilcollapse["showmorecats_" . $this->cats[$i]['cid'] . ""]) ? 'arrowup2.gif' : 'arrowdown2.gif') . '" border="0" alt="" /></div>';
					}
					else
					{
						// #### reset some vars ################
						$show_html = $hidden_html = '';
					}
					// #### recursive category handler #############
					if ($level < $subcatdepth AND $subcatdepth > 1)
					{
						$this->fetch_recursive_categories($this->cats[$i]['cid'], ($level + 1), $ctypefield, $ctype, $dbtable, $seotype, $detailpage, $showcount, $slng, $cidfield, $cidtitle, $iscatmap, $parentstyle, $childstyle, $subcatdepth, $displaycolumns, $tempcount, $hidden_html, $show_html, $this->cats, $showcatdivider);
					}
					if ($level == 1)
					{
						// #### end our table column ###########
						$recursive_html .= "$show_html</td>";
						$show_html = '';
						$cols++;
						$count++;
						// #### end our table row ##############
						if ($cols == $displaycolumns)
						{
							$recursive_html .= '</tr>';
							$cols = 0;
						}
					}
				}
			}
		}
        	// #### fix any missing table columns ##########################
        	if ($cols != $displaycolumns AND $cols != 0)
        	{
        		$neededtds = ($displaycolumns - $cols);
        		for ($i = 0; $i < $neededtds; $i++)
        		{
        			$recursive_html .= '<td></td>';
        		}
        		$recursive_html .= '</tr>';
        	}
        	$ilance->timer->stop();
        }
        
        /**
	* Function to print the main subcategory columns of a particular category being viewed or selected
	*
	* @param	integer	        number of columns to display (default 1)
	* @param        string          category type (service, product, serviceprovider, portfolio, stores, wantads)
	* @param        bool            show subcategories?
	* @param        string          short language code (default is eng)
	* @param        integer         category id
	* @param        string          extra (optional)
	* @param        boolean         show category counts? (default yes)
	* @param        boolean         showing category map? (default no)
	* @param        string          style css for parent listing titles (default blank)
	* @param        string          style css for child listing titles (default blank)
	* @param        integer         subcategory depth level to display (default 0 = root)
	* @param        string          cache id (to prevent similar cache pages) (default blank)
	* @param        boolean         show the current selected category (default true)
	* @param        boolean         show gray border under each category row (default true)
	*/
        function print_subcategory_columns($columns = 1, $cattype = 'service', $dosubcats = 1, $slng = 'eng', $cid = 0, $extra = '', $showcount = 1, $iscatmap = 0, $parentstyle = '', $childstyle = '', $subcatdepth = 0, $cacheid = '', $showcurrentcat = true, $showcatdivider = true)
        {
                global $ilance, $phrase, $ilconfig, $ilpage, $show, $storeid, $storetype, $categoryfinderhtml, $sqlquery, $sqlqueryads, $recursive_html, $categorycache, $block, $blockcolor;
                $ilance->timer->start();
		if (!empty($cacheid))
		{
			$cacheid = '_' . $cacheid;
		}
		$this->cacheid = $cacheid;
		$accepted = array('service', 'portfolio', 'serviceprovider', 'product');
		$html = $extraquery = $join = $join2 = $leftjoin = $leftjoin2 = $leftjoinsubquery = $leftjoinsubquery2 = $extracount = $extracount2 = $extranodecount = $extranodecount2 = $ctypefield = $ctype = $dbtable = $seotype = $detailpage = $cidfield = $cidtitle = '';
		
		($apihook = $ilance->api('print_subcategory_columns_top_start')) ? eval($apihook) : false;
		
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
			// services
			if ($cattype == 'service')
			{
				$dbtable2 = DB_PREFIX . "projects";
				$detailpage = $ilpage['rfp'];
				$seotype = ($iscatmap) ? 'servicecatmap' : 'servicecat';
				$seotype2 = 'servicecatmap';
				$ctype = 'service';
				$blockcolor = 'blue';
				$block = '2';
				// #### root categories ########################
				if ($cid == 0)
				{
					$query = "
						SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "node.*$extracount
						FROM $dbtable node
						$leftjoin
						WHERE node.$ctypefield = '$ctype'
							$extraquery
							AND node.visible = '1'
							AND node.level <= '1'
						GROUP BY node.cid
						ORDER BY node.lft ASC";
				}
				// #### child categories #######################
				else
				{
					$query = "
						SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "node.*$extranodecount
						FROM $dbtable hp
						JOIN $dbtable node ON node.lft BETWEEN hp.lft AND hp.rgt
						JOIN $dbtable hr ON MBRWithin(Point(0, node.lft), hr.sets)
						$leftjoin
						WHERE hp.cid = '" . intval($cid) . "'
							AND hp.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
							AND hr.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
							AND node.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
							AND hp.visible = '1'
							AND hr.visible = '1'
							AND node.visible = '1'
						GROUP BY node.cid
						HAVING  COUNT(*) <=
						(
							SELECT  COUNT(*)
							FROM    $dbtable hp
							JOIN    $dbtable hrp
							$leftjoinsubquery
							ON      MBRWithin(Point(0, hp.lft), hrp.sets)
							WHERE   hp.cid = '" . intval($cid) . "'
							AND     hp.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
							AND     hrp.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
							AND     hp.visible = '1'
							AND     hrp.visible = '1'
						) + 2
						ORDER BY node.lft";	
				}
			}
			// products
			else if ($cattype == 'product')
			{
				$dbtable2 = DB_PREFIX . "projects";
				$detailpage = $ilpage['merch'];
				$seotype = ($iscatmap) ? 'productcatmap' : 'productcat';
				$seotype2 = 'productcatmap';
				$ctype = 'product';
				$blockcolor = 'yellow';
				$block = '';
				// #### root categories ################
				if ($cid == 0)
				{
					$query = "
						SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "node.*$extracount
						FROM $dbtable node
						$leftjoin
						WHERE node.$ctypefield = '$ctype'
							$extraquery
							AND node.visible = '1'
							AND node.level <= '1'
						GROUP BY node.cid
						ORDER BY node.lft ASC";
				}
				// #### child categories ###############
				else
				{
					$query = "
						SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "node.*$extranodecount
						FROM $dbtable hp
						JOIN $dbtable node ON node.lft BETWEEN hp.lft AND hp.rgt
						JOIN $dbtable hr ON MBRWithin(Point(0, node.lft), hr.sets)
						$leftjoin
						WHERE hp.cid = '" . intval($cid) . "'
							AND hp.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
							AND hr.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
							AND node.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
							AND hp.visible = '1'
							AND hr.visible = '1'
							AND node.visible = '1'
						GROUP BY node.cid
						HAVING  COUNT(*) <=
						(
							SELECT  COUNT(*)
							FROM    $dbtable hp
							JOIN    $dbtable hrp
							$leftjoinsubquery
							ON      MBRWithin(Point(0, hp.lft), hrp.sets)
							WHERE   hp.cid = '" . intval($cid) . "'
							AND     hp.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
							AND     hrp.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
							AND     hp.visible = '1'
							AND     hrp.visible = '1'
						) + 2
						ORDER BY node.lft";
				}
			}
			// experts
			else if ($cattype == 'serviceprovider')
			{
				$detailpage = $ilpage['members'];
				$seotype = 'serviceprovidercat';
				$seotype2 = 'serviceprovidercat';
				$counttype = 'counter';
				$ctype = 'service';
				$blockcolor = 'gray';
				$block = '3';
				// #### root categories ################
				if ($cid == 0)
				{
					$query = "
						SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "node.*
						FROM $dbtable node
						$leftjoin
						WHERE node.$ctypefield = '$ctype'
							$extraquery
							AND node.visible = '1'
							AND node.level <= '1'
						GROUP BY node.cid
						ORDER BY node.lft ASC";
				}
				else
				{
					$query = "
						SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "node.*
						FROM $dbtable hp
						JOIN $dbtable node ON node.lft BETWEEN hp.lft AND hp.rgt
						JOIN $dbtable hr ON MBRWithin(Point(0, node.lft), hr.sets)
						$leftjoin
						WHERE hp.cid = '" . intval($cid) . "'
							AND hp.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
							AND hr.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
							AND node.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
							AND hp.visible = '1'
							AND hr.visible = '1'
							AND node.visible = '1'
						GROUP BY node.cid
						HAVING  COUNT(*) <=
						(
							SELECT  COUNT(*)
							FROM    $dbtable hp
							JOIN    $dbtable hrp
							$leftjoinsubquery
							ON      MBRWithin(Point(0, hp.lft), hrp.sets)
							WHERE   hp.cid = '" . intval($cid) . "'
							AND     hp.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
							AND     hrp.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
							AND     hp.visible = '1'
							AND     hrp.visible = '1'
						) + 2
						ORDER BY node.lft";	
				}
			}
			// portfolios
			else if ($cattype == 'portfolio')
			{
				$dbtable2 = DB_PREFIX . "portfolio";
				$dbtable3 = DB_PREFIX . "attachment";
				$detailpage = $ilpage['portfolio'];
				$seotype = ($iscatmap) ? 'portfoliocatmap' : 'portfoliocat';
				$seotype2 = 'portfoliocat';
				$ctype = 'service';
				$blockcolor = 'gray';
				$block = '3';
				if ($cid == 0)
				{
					$query = "
						SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "parent.cid, parent.title_$slng, parent.parentid, parent.canpost, parent.lastpost, parent.views, parent.level, COUNT(attach.portfolio_id) AS auctioncount
						FROM $dbtable node
						LEFT JOIN $dbtable2 port ON (node.cid = port.category_id AND port.visible = '1')
						LEFT JOIN $dbtable3 attach ON (port.portfolio_id = attach.portfolio_id AND attach.visible = '1' AND attach.attachtype = 'portfolio')
						JOIN $dbtable parent ON (node.lft BETWEEN parent.lft AND parent.rgt)
						WHERE 
							parent.cattype = '$ctype'
							AND parent.portfolio = '1'
							AND parent.visible = '1'
							AND node.portfolio = '1'
							AND node.cattype = '$ctype'
							AND node.visible = '1'
						GROUP BY parent.cid
						ORDER BY node.lft ASC";
				}
				else
				{
					$query = "
						SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "parent.cid, parent.title_$slng, parent.parentid, parent.canpost, parent.lastpost, parent.views, parent.level, COUNT(port.portfolio_id) AS auctioncount
						FROM $dbtable AS node
						LEFT JOIN $dbtable3 AS port ON (node.cid = port.category_id AND port.attachtype = 'portfolio')
						JOIN $dbtable AS parent ON (node.lft BETWEEN parent.lft AND parent.rgt)
						AND parent.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
						AND node.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
						AND
						(
							SELECT cid
							FROM $dbtable
							WHERE lft <= node.lft
								AND rgt >= node.rgt
								AND visible = '1'
								AND portfolio = '1'
								AND cattype = '" . $ilance->db->escape_string($ctype) . "'
							LIMIT 1
						)
						GROUP BY parent.cid
						HAVING COUNT(*) <=
						(
							SELECT COUNT(*)
							FROM $dbtable hp
							JOIN $dbtable hrp ON MBRWithin(Point(0, hp.lft), hrp.sets)
							WHERE hp.cid = '" . intval($cid) . "'
								AND hp.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
								AND hrp.$ctypefield = '" . $ilance->db->escape_string($ctype) . "'
								AND hp.visible = '1'
								AND hrp.visible = '1'
								AND hp.portfolio = '1'
								AND hrp.portfolio = '1'
						) + 2
						ORDER BY parent.lft";
				}
			}
			// everything else (stores, wantads, etc)
			else
			{
				($apihook = $ilance->api('print_subcategory_columns_else_end')) ? eval($apihook) : false;
			}
                }
                switch ($columns)
                {
                        // #### SINGLE COLUMN OUTPUT ###########################
                        case '1':
                        {
				$write_html = true;
				// #### build new left nav #####################
				if ($write_html AND !empty($query))
				{
					$html = $htmlbackto = $htmlallcats = $parentcategory = '';
					$mycats = $html2 = $html3 = array();
					$count = $count2 = $thisparentid = 0;
					$templevel = $currentlevel = 1;
					$paddingtop = 5;
					$htmlstart = '';
					$htmlend = '';
					if ($show['noquery'] == false)
					{
						$result = $ilance->db->query($query, 0, null, __FILE__, __LINE__);
						while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
						{
							if ($cattype == 'serviceprovider')
							{
								// count providers opt'ed in this category
								$counter = 0;
								$count = $ilance->db->query("
									$sqlquery[select]
									$sqlquery[categories]
									$sqlquery[options]
									$sqlquery[keywords]
									$sqlquery[location]
									$sqlquery[radius]
									$sqlquery[skillsquery]
									$sqlquery[profileanswersquery]
									$sqlquery[pricerange]
									$sqlquery[groupby]
									$sqlquery[orderby]
								", 0, null, __FILE__, __LINE__);
								$counter = $ilance->db->num_rows($count);
								$row[$counttype] = $counter;
							}
							$mycats[] = array(
								'cid' => $row[$cidfield],
								'title' => $row[$cidtitle],
								'parentid' => $row[$cidparentid],
								'canpost' => $row[$cidcanpost],
								'lastpost' => (isset($row[$cidlastpost]) ? $row[$cidlastpost] : '0000-00-00 00:00:00'),
								'views' => $row[$cidviews],
								'auctioncount' => $row[$counttype],
								'level' => $row[$cidlevel],
								'catimage' => (isset($row[$catimagefield]) ? $row[$catimagefield] : '')
							);    
						}
						unset($row, $result);
					}
					else
					{
						$mycats = !empty($categorycache[$_SESSION['ilancedata']['user']['slng']]['stores']) ? $categorycache[$_SESSION['ilancedata']['user']['slng']]['stores'] : array();      
					}
					$this->cats = $mycats;
					unset($mycats);
					$catcount = count($this->cats);
					$show['leftnavcategories'] = true;
					for ($i = 0; $i < $catcount; $i++)
					{
						if (isset($ilance->GPC['cid']) AND $this->cats[$i]['cid'] == $ilance->GPC['cid'])
						{
							$currentlevel = $this->cats[$i]['level'];
							$thisparentid = $this->cats[$i]['parentid'];
							$parentcategory = ($thisparentid > 0) ? $this->title($slng, $this->cats[$i]['parentid']) : '';
							$templevel++;
							break;
						}
					}
					// #### find the parent category name for "Back To: Category"
					if ($ilconfig['globalauctionsettings_showbackto'])
					{
						// #### back-to logic for portfolios
						if ($cattype == 'portfolio')
						{
							$currentlevel++;
							if ($currentlevel > 1 AND !empty($ilance->GPC['cid']))
							{
								$auctioncount = '';
								$htmlbackto = ($ilconfig['globalauctionsettings_seourls'])
									? '<span class="blueonly"><a href="' . HTTP_SERVER . print_seo_url($ilconfig['portfolioslistingidentifier']) . '">{_gallery}</a></span>' . $auctioncount . '<div style="width:100%;height:1px;background-color:#eeeeee;margin-top:9px;margin-bottom:9px"></div>'
									: '<span class="blueonly"><a href="' . HTTP_SERVER . $ilpage['portfolio'] . '">{_gallery}</a></span>' . $auctioncount . '<div style="width:100%;height:1px;background-color:#eeeeee;margin-top:9px;margin-bottom:9px"></div>';
							}	
						}
						// #### back-to logic for everything else
						else
						{
							if (!empty($ilance->GPC['cid']))
							{
								if ($currentlevel == 1 AND $thisparentid == 0)
								{
									$scriptpage = HTTP_SERVER . $ilpage['search'] . print_hidden_fields(true, array('page','budget'), true, '', '', true, false);
									$removeurl = rewrite_url($scriptpage, $remove = 'cid=' . $ilance->GPC['cid']);
									$htmlbackto = '';
								}
								else if ($currentlevel > 1 AND $thisparentid > 0 AND !empty($parentcategory))
								{
									$auctioncount = '';
									$htmlbackto = ($ilconfig['globalauctionsettings_seourls'])
										? '<span class="blueonly">' . construct_seo_url($seotype, $thisparentid, $auctionid, $parentcategory, '', 0, '', 0, 0, 'qid') . '</span>' . $auctioncount . '<div style="margin-top:2px;margin-bottom:5px"></div>'
										: '<span class="blueonly"><a href="' . $detailpage . '?cid=' . $thisparentid . '">' . $parentcategory . '</a></span>' . $auctioncount . '<div style="margin-top:2px;margin-bottom:5px"></div>';
								}
							}
						}
						
						($apihook = $ilance->api('print_subcategory_columns_back_to_end')) ? eval($apihook) : false;
					}
					for ($i = 0; $i < $catcount; $i++)
					{
						$html3[$count2]['html'] = '';
						if ($this->cats[$i]['parentid'] == $thisparentid)
						{
							// want ads
							if (!empty($sqlqueryads) AND is_array($sqlqueryads))
							{
								// best match - uses existing search params to dig search patterns category by category
								// this variable will make a hit to the db to find results independant of the existing search being performed
								$catbitcounter = ($ilconfig['globalfilters_enablecategorycount'] AND isset($showcount) AND $showcount)
									? $this->bestmatch_auction_count($this->cats[$i]['cid'], $cattype, $sqlqueryads)
									: 0;
								$catbitcount = ($ilconfig['globalfilters_enablecategorycount'] AND isset($showcount) AND $showcount)
									? '&nbsp;<span class="smaller gray" style="direction:' . (($ilconfig['template_textalignment'] == 'left') ? 'ltr' : 'rtl') . ';unicode-bidi:embed">(' . number_format($catbitcounter) . ')</span>'
									: '';
							}
							// search results
							else if (!empty($sqlquery) AND is_array($sqlquery))
							{
								// best match - uses existing search params to dig search patterns category by category
								// this variable will make a hit to the db to find results independant of the existing search being performed
								$catbitcounter = ($ilconfig['globalfilters_enablecategorycount'] AND isset($showcount) AND $showcount)
									? $this->bestmatch_auction_count($this->cats[$i]['cid'], $cattype, $sqlquery)
									: 0;
								$catbitcount = ($ilconfig['globalfilters_enablecategorycount'] AND isset($showcount) AND $showcount)
									? '&nbsp;<span class="smaller gray" style="direction:' . (($ilconfig['template_textalignment'] == 'left') ? 'ltr' : 'rtl') . ';unicode-bidi:embed">(' . number_format($catbitcounter) . ')</span>'
									: '';
							}
							// default
							else
							{
								// regular auction count
								if ($cattype == 'stores' AND $this->cats[$i]['parentid'] == '0' AND isset($ilance->GPC['id']))
								{
									$catbitcounter = ($ilconfig['globalfilters_enablecategorycount'] AND isset($showcount) AND $showcount)
										//? number_format($this->cats[$i]['auctioncount'])
										? number_format($ilance->stores->print_total_category_parent_count($ilance->GPC['id']))
										: 0;
									$catbitcount = ($ilconfig['globalfilters_enablecategorycount'] AND isset($showcount) AND $showcount)
										? '&nbsp;<span class="smaller gray" style="direction:' . (($ilconfig['template_textalignment'] == 'left') ? 'ltr' : 'rtl') . ';unicode-bidi:embed">(' . number_format($catbitcounter) . ')</span>'
										: '';
								}
								else
								{
									$catbitcounter = $this->cats[$i]['auctioncount'];
									$catbitcount = ($ilconfig['globalfilters_enablecategorycount'] AND isset($showcount) AND $showcount)
										? '&nbsp;<span class="smaller gray" style="direction:' . (($ilconfig['template_textalignment'] == 'left') ? 'ltr' : 'rtl') . ';unicode-bidi:embed">(' . number_format($catbitcounter) . ')</span>'
										: '';
								}
							}
							// #### MAIN SELECTED CATEGORY ############################################
							if (!empty($ilance->GPC['cid']) AND $ilance->GPC['cid'] == $this->cats[$i]['cid'])
							{
								// #### CATEGORY FINDER ###########################################
								if ($cattype == 'service' OR $cattype == 'product')
								{
									// will populate $show['categoryfinder'] = true or false
									$categoryfinderhtml = $this->print_searchable_questions($this->cats[$i]['cid'], $showcount, $this->cats[$i]['level'], 0, false, '', '');
								}
								if ($ilconfig['globalauctionsettings_showcurrentcat'] AND $showcurrentcat)
								{
									$html .= '<div style="padding-top:2px" class="black"><strong>' . handle_input_keywords(stripslashes($this->cats[$i]['title'])) . '' . $catbitcount . '</strong> ' . $this->print_category_newicon($this->cats[$i]['lastpost'],$this->cats[$i]['cid']) . '</div>';
								}
								// #### SUBCATEORIES IN MAIN SELECTED CATEGORY #####################
								$html2 = array();
								if ($currentlevel >= 1)
								{
									$count = 0;                                                                
									foreach ($this->cats AS $array)
									{
										$html2[$count]['html'] = '';
										if ($array['parentid'] == $this->cats[$i]['cid'])
										{
											if (!empty($sqlqueryads) AND is_array($sqlqueryads))
											{
												// best match - uses existing search params to dig search patterns category by category
												// this variable will make a hit to the db to find results independant of the existing search being performed
												$catbitcounter2 = $this->bestmatch_auction_count($array['cid'], $cattype, $sqlqueryads);
												$catbitcount2 = ($ilconfig['globalfilters_enablecategorycount'] AND isset($showcount) AND $showcount)
													? '&nbsp;<span class="smaller gray" style="direction:' . (($ilconfig['template_textalignment'] == 'left') ? 'ltr' : 'rtl') . ';unicode-bidi:embed">(' . number_format($catbitcounter2) . ')</span>'
													: '';
											}
											else if (!empty($sqlquery) AND is_array($sqlquery))
											{
												// best match - uses existing search params to dig search patterns category by category
												// this variable will make a hit to the db to find results independant of the existing search being performed
												$catbitcounter2 = $this->bestmatch_auction_count($array['cid'], $cattype, $sqlquery);
												$catbitcount2 = ($ilconfig['globalfilters_enablecategorycount'] AND isset($showcount) AND $showcount)
													? '&nbsp;<span class="smaller gray" style="direction:' . (($ilconfig['template_textalignment'] == 'left') ? 'ltr' : 'rtl') . ';unicode-bidi:embed">(' . number_format($catbitcounter2) . ')</span>'
													: '';
											}
											else
											{
												// regular auction count
												// this variable not make another hit to the db
												$catbitcounter2 = $array['auctioncount'];
												$catbitcount2 = ($ilconfig['globalfilters_enablecategorycount'] AND isset($showcount) AND $showcount)
													? '&nbsp;<span class="smaller gray" style="direction:' . (($ilconfig['template_textalignment'] == 'left') ? 'ltr' : 'rtl') . ';unicode-bidi:embed">(' . number_format($catbitcounter2) . ')</span>'
													: '';
											}
											// if we are hiding the main selected category then set the left-padding level to 1
											if ($ilconfig['globalauctionsettings_showcurrentcat'] == false OR $showcurrentcat == false)
											{
												$templevel = 1;
											}
											// if listing counter in this category is empty don't show subcategory!
											if ($catbitcounter2 > 0)
											{
												if ($ilconfig['globalauctionsettings_seourls'])
												{
													$html2[$count]['html'] .= '<div style="padding-top:7px; padding-left:' . $this->fetch_level_padding($templevel) . 'px"><span class="blueonly">' . construct_seo_url($seotype, $array['cid'], $auctionid, handle_input_keywords(stripslashes($array['title'])), '', 0, '', 0, 0) . '</span>' . $catbitcount2 . ' ' . $this->print_category_newicon($array['lastpost'],$this->cats[$i]['cid']) . '</div>';
												}
												else
												{
													$html2[$count]['html'] .= '<div style="padding-top:7px; padding-left:' . $this->fetch_level_padding($templevel) . 'px"><span class="blueonly"><a href="' . $detailpage . '?cid=' . $array['cid'] . print_hidden_fields(true, array('page','mode','cid','cmd','state','id'), false, '', '', true, true) . '">' . handle_input_keywords(stripslashes($array['title'])) . '</a></span>' . $catbitcount2 . ' ' . $this->print_category_newicon($array['lastpost'],$this->cats[$i]['cid']) . '</div>';
												}
												$count++;
											}
										}
									}
								}
								$bit['visible'] = $bit['hidden'] = '';
								$templevel = ($ilconfig['globalauctionsettings_showcurrentcat'] == false) ? 1 : 2;
								$hidden = '<div style="padding-left:' . $this->fetch_level_padding($templevel) . 'px; padding-bottom:6px; padding-top:7px" class="blueonly"><a href="javascript:void(0)" onclick="toggle_more(\'showmoresubcats_' . $cid . '\', \'moretext_' . $cid . '\', \'' . '{_more}' . '\', \'' . '{_less}' . '\', \'showmoreicon_' . $cid . '\')"><span id="moretext_' . $cid . '" style="font-weight:bold; text-decoration:none">' . (!empty($ilcollapse["showmoresubcats_$cid"]) ? '{_less}' : '{_more}') . '</span></a> <img id="showmoreicon_' . $cid . '" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/' . (!empty($ilcollapse["showmoresubcats_$cid"]) ? 'arrowup2.gif' : 'arrowdown2.gif') . '" border="0" alt="" /></div>';
								if (!empty($html2) AND is_array($html2))
								{
									$c = 0;
									foreach ($html2 AS $key => $array)
									{
										$c++;
										if ($c <= $ilconfig['globalauctionsettings_catcutoff'])
										{
											$bit['visible'] .= $html2[$key]['html'];
										}
										else
										{
											$bit['hidden'] .= $html2[$key]['html'];
										}
									}
								}
								if ($count <= $ilconfig['globalauctionsettings_catcutoff'])
								{
									$hidden = '';
								}
								if (!empty($bit['visible']))
								{
									$html .= "$bit[visible] <div id=\"showmoresubcats_$cid\" style=\"" . (!empty($ilcollapse["showmoresubcats_$cid"]) ? $ilcollapse["showmoresubcats_$cid"] : 'display: none;') . "\">$bit[hidden]</div>$hidden";
								}
							}
							// #### MAIN UNSELECTED CATEGORIES ###################################
							else 
							{
								// prevent all other root cats from showing underneath selected category
								if (empty($ilance->GPC['cid']) OR $ilance->GPC['cid'] == 0)
								{
									if ($this->cats[$i]['parentid'] == $thisparentid)
									{
										$catimage = isset($this->cats[$i]['catimage']) ? $this->cats[$i]['catimage'] : '';
										if ($catbitcounter > 0)
										{
											if ($ilance->categories->has_children_categories($this->cats[$i]['cid'], $ctype))
											{
												if (defined('LOCATION') AND LOCATION == 'main')
												{
													$html3[$count2]['html'] .= (($ilconfig['globalauctionsettings_seourls'])
														? '<div style="padding-bottom:6px; padding-left:0px"><span class="blueonly">' . (empty($catimage)  ? '' : '<img style="vertical-align:middle;padding-bottom:4px;" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'categoryicons/' . $catimage . '" border="0" alt="" />&nbsp;') . construct_seo_url($seotype2, $this->cats[$i]['cid'], $auctionid, stripslashes($this->cats[$i]['title']), '', 0, '', 0, 0) . '</span>' . $catbitcount . ' ' . $this->print_category_newicon($this->cats[$i]['lastpost'], $this->cats[$i]['cid']) . '</div>'
														: '<div style="padding-bottom:6px; padding-left:0px"><span class="blueonly">' . (empty($catimage)  ? '' : '<img style="vertical-align:middle;padding-bottom:4px;" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'categoryicons/' . $catimage . '" border="0" alt="" />&nbsp;') . '<a href="' . $detailpage . '?cmd=listings&amp;cid=' . $this->cats[$i]['cid'] . print_hidden_fields(true, array('page','mode','cid','cmd','state','id','sort'), false, '', '', true, true) . '">' . stripslashes($this->cats[$i]['title']) . '</a></span>' . $catbitcount . ' ' . $this->print_category_newicon($this->cats[$i]['lastpost'], $this->cats[$i]['cid']) . '</div>');
													$count2++;
												}
												else
												{
													$html3[$count2]['html'] .= (($ilconfig['globalauctionsettings_seourls'])
														? '<div style="padding-bottom:6px; padding-left:0px"><span class="blueonly">' . (empty($catimage)  ? '' : '<img style="vertical-align:middle;padding-bottom:4px;" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'categoryicons/' . $catimage . '" border="0" alt="" />&nbsp;') . construct_seo_url($seotype, $this->cats[$i]['cid'], $auctionid, stripslashes($this->cats[$i]['title']), '', 0, '', 0, 0) . '</span>' . $catbitcount . ' ' . $this->print_category_newicon($this->cats[$i]['lastpost'], $this->cats[$i]['cid']) . '</div>'
														: '<div style="padding-bottom:6px; padding-left:0px"><span class="blueonly">' . (empty($catimage)  ? '' : '<img style="vertical-align:middle;padding-bottom:4px;" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'categoryicons/' . $catimage . '" border="0" alt="" />&nbsp;') . '<a href="' . $detailpage . '?cid=' . $this->cats[$i]['cid'] . print_hidden_fields(true, array('page','mode','cid','cmd','state','id'), false, '', '', true, true) . '">' . stripslashes($this->cats[$i]['title']) . '</a></span>' . $catbitcount . ' ' . $this->print_category_newicon($this->cats[$i]['lastpost'], $this->cats[$i]['cid']) . '</div>');
													$count2++;
												}
											}
											else
											{
												$html3[$count2]['html'] .= (($ilconfig['globalauctionsettings_seourls'])
													? '<div style="padding-bottom:6px; padding-left:0px"><span class="blueonly">' . (empty($catimage)  ? '' : '<img style="vertical-align:middle;padding-bottom:4px;" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'categoryicons/' . $catimage . '" border="0" alt="" />&nbsp;') . construct_seo_url($seotype, $this->cats[$i]['cid'], $auctionid, stripslashes($this->cats[$i]['title']), '', 0, '', 0, 0) . '</span>' . $catbitcount . ' ' . $this->print_category_newicon($this->cats[$i]['lastpost'], $this->cats[$i]['cid']) . '</div>'
													: '<div style="padding-bottom:6px; padding-left:0px"><span class="blueonly">' . (empty($catimage)  ? '' : '<img style="vertical-align:middle;padding-bottom:4px;" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'categoryicons/' . $catimage . '" border="0" alt="" />&nbsp;') . '<a href="' . $detailpage . '?cid=' . $this->cats[$i]['cid'] . print_hidden_fields(true, array('page','mode','cid','cmd','state','id'), false, '', '', true, true) . '">' . stripslashes($this->cats[$i]['title']) . '</a></span>' . $catbitcount . ' ' . $this->print_category_newicon($this->cats[$i]['lastpost'], $this->cats[$i]['cid']) . '</div>');
												$count2++;
											}
										}
										else
										{
											if (defined('LOCATION') AND LOCATION == 'main')
											{
												$html3[$count2]['html'] .= (($ilconfig['globalauctionsettings_seourls'])
													? '<div style="padding-bottom:6px; padding-left:0px"><span class="blueonly">' . (empty($catimage)  ? '' : '<img style="vertical-align:middle;padding-bottom:4px;" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'categoryicons/' . $catimage . '" border="0" alt="" />&nbsp;') . construct_seo_url($seotype2, $this->cats[$i]['cid'], $auctionid, stripslashes($this->cats[$i]['title']), '', 0, '', 0, 0) . '</span>' . $catbitcount . ' ' . $this->print_category_newicon($this->cats[$i]['lastpost'], $this->cats[$i]['cid']) . '</div>'
													: '<div style="padding-bottom:6px; padding-left:0px"><span class="blueonly">' . (empty($catimage)  ? '' : '<img style="vertical-align:middle;padding-bottom:4px;" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'categoryicons/' . $catimage . '" border="0" alt="" />&nbsp;') . '<a href="' . $detailpage . '?cmd=listings&amp;cid=' . $this->cats[$i]['cid'] . print_hidden_fields(true, array('page','mode','cid','cmd','state','id','sort'), false, '', '', true, true) . '">' . stripslashes($this->cats[$i]['title']) . '</a></span>' . $catbitcount . ' ' . $this->print_category_newicon($this->cats[$i]['lastpost'], $this->cats[$i]['cid']) . '</div>');
												$count2++;
											}
											else
											{
												/*$html3[$count2]['html'] .= (($ilconfig['globalauctionsettings_seourls'])
													? '<div style="padding-bottom:6px; padding-left:0px"><span class="blueonly">' . (empty($catimage)  ? '' : '<img style="vertical-align:middle;padding-bottom:4px;" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'categoryicons/' . $catimage . '" border="0" alt="" />&nbsp;') . construct_seo_url($seotype, $this->cats[$i]['cid'], $auctionid, stripslashes($this->cats[$i]['title']), '', 0, '', 0, 0) . '</span><!--' . $catbitcount . '--> ' . $this->print_category_newicon($this->cats[$i]['lastpost'], $this->cats[$i]['cid']) . '</div>'
													: '<div style="padding-bottom:6px; padding-left:0px"><span class="blueonly">' . (empty($catimage)  ? '' : '<img style="vertical-align:middle;padding-bottom:4px;" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'categoryicons/' . $catimage . '" border="0" alt="" />&nbsp;') . '<a href="' . $detailpage . '?cid=' . $this->cats[$i]['cid'] . print_hidden_fields(true, array('page','mode','cid','cmd','state','id','q'), false, '', '', true, true) . '">' . stripslashes($this->cats[$i]['title']) . '</a></span><!--' . $catbitcount . '--> ' . $this->print_category_newicon($this->cats[$i]['lastpost'], $this->cats[$i]['cid']) . '</div>');
												$count2++;*/
											}
										}
									}
								}
							}
						}
					}
					// #### determine if we're viewing categories on main menu to show all cats without user clicking "More"
					if (defined('LOCATION') AND LOCATION == 'main')
					{
						$ilconfig['globalauctionsettings_catcutoff'] = $ilconfig['globalauctionsettings_maincatcutoff'];	
					}
					$bit['visible'] = $bit['hidden'] = '';
					$templevel = ($ilconfig['globalauctionsettings_showcurrentcat'] == false OR $count2 > 0) ? 1 : 2;
					$hidden = '<div style="padding-left:' . $this->fetch_level_padding($templevel) . 'px; padding-bottom:6px; padding-top:5px"><span class="blueonly"><a href="javascript:void(0)" onclick="toggle_more(\'showmorecats_' . $cattype . '\', \'moretext_' . $cattype . '\', \'' . '{_more}' . '\', \'' . '{_less}' . '\', \'showmoreicon_' . $cattype . '\')"><span id="moretext_' . $cattype . '" style="font-weight:bold; text-decoration:none">' . (!empty($ilcollapse["showmorecats_$cattype"]) ? '{_less}' : '{_more}') . '</span></a></span> <img id="showmoreicon_' . $cattype . '" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/' . (!empty($ilcollapse["showmorecats_$cattype"]) ? 'arrowup2.gif' : 'arrowdown2.gif') . '" border="0" alt="" /></div>';
					if (!empty($html3) AND is_array($html3))
					{
						$c = 0;
						foreach ($html3 AS $key => $array)
						{
							$c++;
							if ($c <= $ilconfig['globalauctionsettings_catcutoff'])
							{
								$bit['visible'] .= $html3[$key]['html'];
							}
							else
							{
								$bit['hidden'] .= $html3[$key]['html'];
							}
						}
					}
					if ($count2 <= $ilconfig['globalauctionsettings_catcutoff'])
					{
						$hidden = '';
					}
					if ($count2 > 0)
					{
						// #### rebuild display options ########
						if (!empty($bit['visible']))
						{
							$html .= "$bit[visible] <div id=\"showmorecats_$cattype\" style=\"" . (!empty($ilcollapse["showmorecats_$cattype"])
								? $ilcollapse["showmorecats_$cattype"]
								: 'display: none;') . "\">$bit[hidden]</div>$hidden";
						}
					}
					// #### category map urls ######################
					if (defined('LOCATION') AND LOCATION == 'main')
					{
						if ($cattype == 'service')
						{
							$htmlallcats = ($ilconfig['globalauctionsettings_seourls'])
								? '<div style="padding-top:4px" class="bluecat"><a href="' . HTTP_SERVER . print_seo_url($ilconfig['servicecatmapidentifier']) . '">{_view_all_categories}</a></div>'
								: '<div style="padding-top:4px" class="bluecat"><a href="' . HTTP_SERVER . $ilpage['rfp'] . '?cmd=listings">{_view_all_categories}</a></div>';
						}
						else if ($cattype == 'product')
						{
							$htmlallcats = ($ilconfig['globalauctionsettings_seourls'])
								? '<div style="padding-top:4px" class="bluecat"><a href="' . HTTP_SERVER . print_seo_url($ilconfig['productcatmapidentifier']) . '">{_view_all_categories}</a></div>'
								: '<div style="padding-top:4px" class="bluecat"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cmd=listings">{_view_all_categories}</a></div>';
						}
					}
					$show['leftnavcategories'] = (empty($html)) ? false : true;
					
					// #### build our left nav template ####
					$html = "$htmlstart $htmlbackto $html $htmlallcats $htmlend";
				}
                                break;        
                        }
			// #### MULTIPLE COLUMN OUTPUT #########################
                        default:
                        {
				global $recursive_html;
				$recursive_html = '<table border="0" cellspacing="6" cellpadding="1" width="100%" dir="' . $ilconfig['template_textdirection'] . '">';
				$this->fetch_recursive_categories($cid, 1, $ctypefield, $ctype, $dbtable, $seotype, $detailpage, $showcount, $slng, $cidfield, $cidtitle, $iscatmap, $parentstyle, $childstyle, $subcatdepth, $columns, 0, '', '', '', $showcatdivider);                                
				$recursive_html .= '</table><div style="padding-bottom:6px"></div>';
				$html = $recursive_html;
                        }
                }
                $ilance->timer->stop();
		DEBUG("print_subcategory_columns(\$columns = $columns, \$cattype = $cattype, \$cid = $cid, \$dosubcats = $dosubcats, \$slng = $slng, \$cid = $cid, \$extra = $extra, \$showcount = $showcount, \$iscatmap = $iscatmap, \$parentstyle = $parentstyle, \$childstyle = $childstyle, \$subcatdepth = $subcatdepth, \$cacheid = $cacheid, \$showcurrentcat = $showcurrentcat, \$showcatdivider = $showcatdivider) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
                return $html;
        }
	
	/**
	* Function to print the searchable category questions auction count sum.
	* For example, if a seller posted an item under DVD and answered "Horror" as the
	* question, Horror (1) would be shown.  Additionally, this counter would
	* be affected if and when the searcher enters existing search patterns such as
	* selecting another category question at the same time or keywords being used.
	*
	* @param       integer      question id
	* @param       string       choice
	* @param       string       category type (service/product)
	* @param       array        current search sql info (optional)
	*
	* @return      integer      Returns item count
	*/
	function searchable_question_count($qid = 0, $choice = '', $cattype = '', $sqlquery = array (), $extracids = '')
	{
		global $ilance, $ilconfig, $groupcount;
		$ilance->timer->start();
		$supported = array ('service', 'product');
		if (isset($cattype) AND !in_array($cattype, $supported) OR !isset($cattype) OR empty($cattype))
		{
			return 0;
		}
		$table = ($cattype == 'service') ? 'project_answers' : 'product_answers';
		$sqlcount = count($sqlquery);
		$ilance->GPC['qid'] = isset($ilance->GPC['qid']) ? $ilance->GPC['qid'] : '';
		$cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
		$count = 0;
		$extraquery = '';
		if ($groupcount == 1)
		{
			$qid2 = explode('.', $ilance->GPC['qid']);
			if ($qid != $qid2[0])
			{
				$extraquery2 = "(SELECT COUNT(*) FROM " . DB_PREFIX . $table . " pans2 WHERE pans2.project_id = p.project_id " . str_replace("pans.", "pans2.", substr($sqlquery['genrequery'], 0, -1)) . ")";
				$sqlquery['genrequery'] = "AND (pans.questionid = '" . intval($qid) . "') AND (pans.optionid = '" . intval($choice) . "')";
			}
			// this question is in the same question group selected
			else
			{
				$extraquery2 = "COUNT(*)";
				$sqlquery['genrequery'] = "AND (pans.questionid = '" . intval($qid) . "') AND (pans.optionid = '" . intval($choice) . "')";
			}
		}
		else if ($groupcount > 1)
		{
			$extraquery2 = "(SELECT COUNT(*) FROM " . DB_PREFIX . $table . " pans2 WHERE pans2.project_id = p.project_id " . str_replace("pans.", "pans2.", substr($sqlquery['genrequery'], 0, -2)) . "))";
			$sqlquery['genrequery'] = "AND (pans.questionid = '" . intval($qid) . "') AND (pans.optionid = '" . intval($choice) . "')";
		}
		else
		{
			$extraquery2 = "COUNT(*)";
			$sqlquery['genrequery'] = "AND (pans.questionid = '" . intval($qid) . "') AND (pans.optionid = '" . intval($choice) . "')";
		}
		if ($sqlcount > 0)
		{
			$extraquery = "$sqlquery[timestamp] $sqlquery[projectstatus] $sqlquery[keywords] $sqlquery[categories] $sqlquery[projectdetails] $sqlquery[projectstate] $sqlquery[options] $sqlquery[pricerange] $sqlquery[location] $sqlquery[radius] $sqlquery[userquery] $sqlquery[hidequery] $sqlquery[genrequery] ";
		}
		else
		{
			$extraquery = "$extracids $sqlquery[genrequery]";
		}
		if (empty($sqlquery['leftjoin']))
		{
			$sqlquery['leftjoin'] = "LEFT JOIN " . DB_PREFIX . "users u ON p.user_id = u.user_id ";
		}
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "$extraquery2 AS filtergroups
			FROM " . DB_PREFIX . $table . " pans
			LEFT JOIN " . DB_PREFIX . "projects p ON (pans.project_id = p.project_id)
			$sqlquery[leftjoin]
			WHERE p.visible = '1'
				AND p.status = 'open'
				" . (($ilconfig['globalauctionsettings_payperpost']) ? "AND p.status != 'frozen' AND (p.enhancementfee = 0 OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '1')) AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
				$extraquery
			GROUP BY p.project_id
			HAVING filtergroups > 0
		", 0, null, __FILE__, __LINE__);
		$count = $ilance->db->num_rows($sql);
		$ilance->timer->stop();
		DEBUG("searchable_question_count(\$qid = $qid, \$choice = $choice, \$cattype = $cattype) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
		return $count;
	}

	/**
	* Function to print an html formatted representation of the searchable question category genres under a specific category
	* when viewing the search results
	*
	* @param       integer      category id
	* @param       bool         show category counters
	* @param       integer      current level
	* @param       integer      current show view level
	* @param       boolean      force showing of links for category maps (default false)
	* @param       string       category type (optional if forcing links)
	* @param       string       category title (optional if forcing links)
	*
	* @return      string       Returns HTML formatted searchable question output for the left nav search results
	*/
	function print_searchable_questions($cid = 0, $showcount = 1, $level = 0, $viewlevel = -1, $forcelinks = false, $cattype = '', $title = '')
	{
		global $ilance, $ilconfig, $ilpage, $phrase, $ilcollapse, $sqlquery, $show, $block, $blockcolor, $scriptpage, $page_url;
		$ilance->timer->start();
		if ($cid <= 0)
		{
			return false;
		}
		$paddingtopfirst = $paddingtop = 5;
		if ($viewlevel == -1)
		{
			$level++;
		}
		else
		{
			$level = $viewlevel;
		}
		$res = array ();
		if ($forcelinks)
		{ // forcing links via category map
			$res['cattype'] = $cattype;
			$res['title'] = $title;
		}
		else
		{
			$res['cattype'] = $this->cattype($_SESSION['ilancedata']['user']['slng'], $cid);
			$res['title'] = $this->title($_SESSION['ilancedata']['user']['slng'], $cid);
		}
		$table = ($res['cattype'] == 'service') ? 'project_questions' : 'product_questions';
		$table2 = ($res['cattype'] == 'service') ? 'project_questions_choices' : 'product_questions_choices';
		$seotype = ($res['cattype'] == 'service') ? 'servicesearchquestion' : 'productsearchquestion';
		$detailpage = $ilpage['search'];
		$pagetype = ($res['cattype'] == 'service') ? HTTP_SERVER . $ilpage['rfp'] : HTTP_SERVER . $ilpage['merch'];
		$urlbase = ((defined('LOCATION') AND (LOCATION == 'rfp' OR LOCATION == 'search' OR LOCATION == 'merch')) ? $ilpage['search'] : $pagetype);
		$formattedhtml = $catcounter = '';
		$questioncount = 0;
		$pid = $this->parentid($cid);
		$extracids = "AND (cid = '" . intval($cid) . "' OR cid = '-1')";
		$var = $this->fetch_parent_ids($cid);
		$explode = explode(',', $var);
		if (in_array($pid, $explode))
		{
			$extracids = "AND (FIND_IN_SET(cid, '$var,$cid') OR cid = '-1')";
		}
		unset($explode, $var);
		$questions = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "questionid, inputtype, cid, question_" . $_SESSION['ilancedata']['user']['slng'] . " AS question, recursive
			FROM " . DB_PREFIX . $table . "
			WHERE cansearch = '1'
				$extracids
				AND visible = '1'
			ORDER BY sort ASC
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($questions) > 0)
		{
			$count = 0;
			while ($question = $ilance->db->fetch_array($questions, DB_ASSOC))
			{
				if (($question['recursive'] == 1 AND $question['cid'] != $cid) OR $question['cid'] == $cid)
				{
					$questioncount++;
					$choices = array();
					$sql = $ilance->db->query("
						SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "optionid, choice_" . $_SESSION['ilancedata']['user']['slng'] . " AS choice
						FROM " . DB_PREFIX . $table2 . "
						WHERE questionid = '$question[questionid]'
						ORDER BY sort ASC
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						$html[$count]['htmlhead'] = '';
						$html[$count]['html'] = '';
						$html[$count]['htmlhead_end'] = '';
						$more_field = '';
						// #### start the content box for this category question
						if (defined('TEMPLATE_NEWUI_MODE') AND TEMPLATE_NEWUI_MODE != '')
						{
							$html[$count]['htmlblockstart'] = ((defined('LOCATION') AND LOCATION == 'search') ? '<div>' : '');
							$html[$count]['htmlblockend'] = ((defined('LOCATION') AND LOCATION == 'search') ? '</div>' : '');
						}
						else
						{
							$html[$count]['htmlblockstart'] = ((defined('LOCATION') AND LOCATION == 'search') ? '<div class="block' . $block . '-content alt1" id="collapseobj_leftnav_specifics_' . $question['questionid'] . '" style="{collapse[collapseobj_leftnav_specifics_' . $question['questionid'] . ']} padding:6px"><div style="padding-top:6px"></div>' : '');
							$html[$count]['htmlblockend'] = ((defined('LOCATION') AND LOCATION == 'search') ? '</div>' : '');
						}
						$i = $j = 0;
						while ($cres = $ilance->db->fetch_array($sql, DB_ASSOC))
						{
							$choice = $cres['choice'];
							if ($choice != '')
							{
								$i++;
								$itemcount = 0;
								$itemcount = $this->searchable_question_count($question['questionid'], $cres['optionid'], $res['cattype'], $sqlquery, $extracids);
								$catcounter = (($itemcount >= 0 AND $showcount > 0) ? '&nbsp;<span class="smaller gray" style="direction:' . (($ilconfig['template_textalignment'] == 'left') ? 'ltr' : 'rtl') . ';unicode-bidi:embed">(' . number_format($itemcount) . ')</span>' : '');
								// #### currently selected genre question handler
								$x = 0;
								if (isset($ilance->GPC['qid']) AND !empty($ilance->GPC['qid']))
								{
									// #### question groups selected : &qid=9.1,8.1,etc
									if (strrchr($ilance->GPC['qid'], ',') == true)
									{
										$temp = explode(',', $ilance->GPC['qid']);
										$aids = array ();
										foreach ($temp AS $key => $value)
										{
											if (strrchr($value, '.'))
											{
												$tmp = explode('.', $value);
												$aids[$tmp[0]][] = $tmp[1];
												$x++;
											}
										}
									}
									else if (strrchr($ilance->GPC['qid'], ',') == false)
									{
										if (strrchr($ilance->GPC['qid'], '.'))
										{
											$tmp = explode('.', $ilance->GPC['qid']);
											$aids = array ();
											$aids[$tmp[0]][] = $tmp[1];
											$x++;
										}
									}
								}
								//$removeurl = urldecode(PAGEURL); //&qid=9.1
								$removeurl = PAGEURL;
								$removeurl = rewrite_url($removeurl, '' . $question['questionid'] . '.' . $cres['optionid'] . ',');
								$removeurl = rewrite_url($removeurl, ',' . $question['questionid'] . '.' . $cres['optionid']);
								$removeurl = rewrite_url($removeurl, '' . $question['questionid'] . '.' . $cres['optionid']);
								// #### currently selected genre questions
								if (!empty($aids[$question['questionid']]) AND in_array($cres['optionid'], $aids[$question['questionid']]))
								{
									$removeurl = ($x == 1) ? rewrite_url($removeurl, 'qid=') : $removeurl;
									if (defined('TEMPLATE_NEWUI_MODE') AND TEMPLATE_NEWUI_MODE != '')
									{
										$html[$count]['html'] .= ((defined('LOCATION') AND LOCATION == 'search')
										? '<div style="padding:2px 0 6px 2px">
											<span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . ';padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px;padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_' . $question['questionid'] . '_' . $cres['optionid'] . '" /></span>
											<span class="blackonly"><a href="' . $removeurl . '" onMouseOver="rollovericon(\'sel_' . $question['questionid'] . '_' . $cres['optionid'] . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onMouseOut="rollovericon(\'sel_' . $question['questionid'] . '_' . $cres['optionid'] . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . handle_input_keywords($choice) . '</strong></a></span>
										   </div>'
										: '');
									}
									else
									{
										$html[$count]['html'] .= ((defined('LOCATION') AND LOCATION == 'search')
										? '<div style="padding-left:2px;padding-bottom:4px">
											<span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . ';padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px;padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png" border="0" alt="" id="" name="sel_' . $question['questionid'] . '_' . $cres['optionid'] . '" /></span>
											<span class="blackonly"><a href="' . $removeurl . '" onMouseOver="rollovericon(\'sel_' . $question['questionid'] . '_' . $cres['optionid'] . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selectedclear.png\')" onMouseOut="rollovericon(\'sel_' . $question['questionid'] . '_' . $cres['optionid'] . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/selected.png\')"><strong>' . handle_input_keywords($choice) . '</strong></a></span>
										   </div>'
										: '');
									}
								}
								// #### unselected genre questions (clickable)
								else
								{
									if (defined('LOCATION') AND LOCATION == 'search')
									{
										$qidbit = (isset($ilance->GPC['qid']) AND !empty($ilance->GPC['qid']))
											? $ilance->GPC['qid'] . ',' . $question['questionid'] . '.' . $cres['optionid']
											: $question['questionid'] . '.' . $cres['optionid'];
										$url = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url($seotype, $cid, 0, handle_input_keywords($res['title']), '', 0, ilance_htmlentities($choice), $question['questionid'], $cres['optionid'], '', 'onMouseOver="rollovericon(\'unsel_' . $question['questionid'] . '_' . $cres['optionid'] . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onMouseOut="rollovericon(\'unsel_' . $question['questionid'] . '_' . $cres['optionid'] . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')"') : '<a href="' . $detailpage . '?mode=' . $res['cattype'] . '&amp;cid=' . $cid . print_hidden_fields(true, array ('page', 'mode', 'cid', 'cmd', 'state', 'id', 'qid'), false, '', '', true, true) . '&amp;qid=' . $qidbit . '" onMouseOver="rollovericon(\'unsel_' . $question['questionid'] . '_' . $cres['optionid'] . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onMouseOut="rollovericon(\'unsel_' . $question['questionid'] . '_' . $cres['optionid'] . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . stripslashes(handle_input_keywords($choice)) . '</a>';
										if ($itemcount > 0 OR $ilconfig['globalfilters_enablecategorycount'] == 0)
										{
											if ($j == $ilconfig['globalauctionsettings_catanswerdepth'])
											{
												$html[$count]['html'] .= '<div id="showmore_q' . $qidbit . '" style="display:none;">';
												$qid = $question['questionid'];
												$more_field = '<div class="smaller litegray" style="padding-top:3px">
												    <a href="javascript:void(0)" onclick="toggle_more(\'showmore_q' . $qidbit . '\', \'moretext_q' . $qidbit . '\', \'{_more_options}\', \'{_less_options}\', \'showmoreicon_q' . $qidbit . '\')"><span id="moretext_q' . $qidbit . '"  style="text-decoration:none">' . (!empty($ilcollapse["moretext_q$qidbit"]) ? '{_less_options}' : '{_more_options}') . '</span></a> <img id="showmoreicon_q' . $qidbit . '" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/' . (!empty($ilcollapse["moretext_q$qidbit"]) ? 'arrowup2.gif' : 'arrowdown2.gif') . '" border="0" alt="" name="showmoreicon_q' . $qidbit . '" /> 
												</div>';
											}
											if (defined('TEMPLATE_NEWUI_MODE') AND TEMPLATE_NEWUI_MODE != '')
											{
												$html[$count]['html'] .= '<div style="padding:0 0 6px 2px">
													<span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px;padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_' . $question['questionid'] . '_' . $cres['optionid'] . '" /></span>
													<span class="blueonly">' . $url . '</span>' . $catcounter . '
												</div>';
											}
											else
											{
												$html[$count]['html'] .= '<div style="padding-left:2px; padding-bottom:4px">
													<span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '; padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':7px;padding-top:2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png" border="0" alt="" id="" name="unsel_' . $question['questionid'] . '_' . $cres['optionid'] . '" /></span>
													<span class="blueonly">' . $url . '</span>' . $catcounter . '
												</div>';
											}
											$j++;
										}
									}
									else
									{
										$qidbit = (isset($ilance->GPC['qid']) AND !empty($ilance->GPC['qid'])) ? $cid . ',' . $ilance->GPC['qid'] . ',' . $question['questionid'] . '.' . $cres['optionid'] : $cid . ',' . $question['questionid'] . '.' . $cres['optionid'];
										$url = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url($seotype, $cid, 0, handle_input_keywords($res['title']), '', 0, handle_input_keywords($choice), $question['questionid'], $cres['optionid'], '', 'onMouseOver="rollovericon(\'unsel_' . $question['questionid'] . '_' . $cres['optionid'] . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onMouseOut="rollovericon(\'unsel_' . $question['questionid'] . '_' . $cres['optionid'] . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')"') : '<a href="' . $detailpage . '?mode=' . $res['cattype'] . '&amp;cid=' . $cid . print_hidden_fields(true, array ('page', 'mode', 'cid', 'cmd', 'state', 'id', 'qid'), false, '', '', true, true) . '&amp;qid=' . $qidbit . '" onMouseOver="rollovericon(\'unsel_' . $question['questionid'] . '_' . $cres['optionid'] . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselectedselect.png\')" onMouseOut="rollovericon(\'unsel_' . $question['questionid'] . '_' . $cres['optionid'] . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'leftnav/unselected.png\')">' . stripslashes(handle_input_keywords($choice)) . '</a>';
										if ($itemcount > 0)
										{
											if ($j == $ilconfig['globalauctionsettings_catanswerdepth'])
											{
												$html[$count]['html'] .= '<div id="showmore_q' . $qidbit . '" style="display:none">';
												$qid = $question['questionid'];
												$more_field = '<div style="padding-top:3px">
												    <a href="javascript:void(0)" onclick="toggle_more(\'showmore_q' . $qidbit . '\', \'moretext_q' . $qidbit . '\', \'{_more_options}\', \'{_less_options}\', \'showmoreicon_q' . $qidbit . '\')"><span id="moretext_q' . $qidbit . '" class="smaller litegray" style="text-decoration:none">' . (!empty($ilcollapse["moretext_q$qidbit"]) ? '{_less_options}' : '{_more_options}') . '</span></a> <img id="showmoreicon_q' . $qidbit . '" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/' . (!empty($ilcollapse["moretext_q$qidbit"]) ? 'arrowup2.gif' : 'arrowdown2.gif') . '" border="0" alt="" name="showmoreicon_q' . $qidbit . '" /> 
												</div>';
											}
											$html[$count]['html'] .= ($itemcount > 0) ? '<div style="padding-left:0px;padding-bottom:4px"><span class="blueonly">' . $url . '</span>' . $catcounter . '</div>' : '';
											$j++;
										}
									}
									}
							}
							if (!empty($html[$count]['html']))
							{
								if (defined('LOCATION') AND LOCATION == 'search')
								{
									$clear = '';
									//$showqidurl = urldecode(PAGEURL);
									$showqidurl = PAGEURL;
									if (isset($ilance->GPC['qid']) AND strrchr($ilance->GPC['qid'], ',') == true)
									{
										$temp = explode(',', $ilance->GPC['qid']);
										$pageurl = PAGEURL;
										$gcounter = array();
										foreach ($temp AS $key => $value)
										{
											$tmp = explode('.', $value);
											if ($question['questionid'] == $tmp[0])
											{
												$gcounter[] = $tmp[0];
												$showqidurl = $pageurl;
												$showqidurl = rewrite_url($showqidurl, '' . $tmp[0] . '.' . $tmp[1] . ',');
												$showqidurl = rewrite_url($showqidurl, ',' . $tmp[0] . '.' . $tmp[1]);
												$showqidurl = rewrite_url($showqidurl, '' . $tmp[0] . '.' . $tmp[1]);
												$pageurl = $showqidurl;
											}
										}
										$groupcount = count(array_count_values($gcounter));
										if ($groupcount > 0)
										{
											$clear = '<span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . '" class="smaller blue"><a href="' . $showqidurl . '">{_clear}</a></span>';
										}
									}
									else if (isset($ilance->GPC['qid']) AND strrchr($ilance->GPC['qid'], ',') == false)
									{
										$tmp = explode('.', $ilance->GPC['qid']);
										if ($question['questionid'] == $tmp[0])
										{
											$showqidurl = PAGEURL;
											$showqidurl = rewrite_url($showqidurl, 'qid=' . $tmp[0] . '.' . $tmp[1]);
											$showqidurl = rewrite_url($showqidurl, '' . $tmp[0] . '.' . $tmp[1] . ',');
											$showqidurl = rewrite_url($showqidurl, ',' . $tmp[0] . '.' . $tmp[1]);
											$showqidurl = rewrite_url($showqidurl, '' . $tmp[0] . '.' . $tmp[1]);
											$clear = '<span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . '" class="smaller blue"><a href="' . $showqidurl . '">{_clear}</a></span>';
										}
									}
									// #### question group header
									if (defined('TEMPLATE_NEWUI_MODE') AND TEMPLATE_NEWUI_MODE != '')
									{
										$html[$count]['htmlhead'] = '<div class="filter-content">
											<div class="filter-content-block">
											<h4>' . $clear . '<span onClick="toggle(\'leftnav_specifics_' . $question['questionid'] . '\')" onMouseOver="this.style.cursor=\'pointer\'" onMouseOut="this.style.cursor=\'\'">' . handle_input_keywords($question['question']) . '</span></h4>
											<div id="collapseobj_leftnav_specifics_' . $question['questionid'] . '" style="{collapse[collapseobj_leftnav_specifics_' . $question['questionid'] . ']}">
										';
										$html[$count]['htmlhead_end'] = '</div></div><div class="clear"></div></div>';
									}
									else
									{
										$html[$count]['htmlhead'] = '<div class="block' . $block . '-content-' . $blockcolor . '" style="padding-top:9px;padding-bottom:9px" onMouseOver="this.style.cursor=\'pointer\'" onMouseOut="this.style.cursor=\'\'">' . $clear . '
											<span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . ';padding-top:5px;padding-' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . ':10px" onClick="toggle(\'leftnav_specifics_' . $question['questionid'] . '\')"><img id="collapseimg_leftnav_specifics_' . $question['questionid'] . '" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'expand{collapse[collapseimg_leftnav_specifics_' . $question['questionid'] . ']}.gif" border="0" alt="" /></span>
											<span class="gray" onClick="toggle(\'leftnav_specifics_' . $question['questionid'] . '\')"><strong>' . handle_input_keywords($question['question']) . '</strong></span>
										</div>';
										$html[$count]['htmlhead_end'] = '';
									}
								}
								else
								{
									if (defined('TEMPLATE_NEWUI_MODE') AND TEMPLATE_NEWUI_MODE != '')
									{
										$html[$count]['htmlhead'] = (defined('LOCATION') AND LOCATION == 'search')
											? '<div class="black" style="padding-top:9px; padding-bottom:4px"><strong>' . handle_input_keywords($question['question']) . '</strong></div>'
											: '<div class="litegray" style="padding-left:0px;padding-top:6px;padding-bottom:6px"><strong>' . handle_input_keywords($question['question']) . '</strong></div>';
									}
									else
									{
										$html[$count]['htmlhead'] = (defined('LOCATION') AND LOCATION == 'search')
											? '<div class="black" style="padding-top:9px; padding-bottom:4px"><strong>' . handle_input_keywords($question['question']) . '</strong></div>'
											: '<div class="litegray" style="padding-left:0px;padding-top:6px;padding-bottom:6px"><strong>' . handle_input_keywords($question['question']) . '</strong></div>';
									}
								}
								unset($clear, $showqidurl);
							}
						}
						if ($j > $ilconfig['globalauctionsettings_catanswerdepth'] OR $ilconfig['globalauctionsettings_catanswerdepth'] == '0')
						{
						    $html[$count]['html'] .= '</div>';
						    $html[$count]['html'] .= $more_field;
						}
					}
					$count++;
				}
			}
			$bit['visible'] = $bit['hidden'] = '';
			$hidden = (defined('LOCATION') AND LOCATION == 'search')
				? '<div class="block' . $block . '-content-' . $blockcolor . '" style="padding-top:9px; padding-bottom:9px"><div class="smaller"><a href="javascript:void(0)" onclick="toggle_more(\'showmore_' . $cid . '\', \'moretext_' . $cid . '\', \'' . '{_more_options}' . '\', \'' . '{_less_options}' . '\', \'showmoreicon_' . $cid . '\')"><span id="moretext_' . $cid . '" class="gray" style="font-weight:bold; text-decoration:none">' . (!empty($ilcollapse["showmore_$cid"]) ? '{_less_options}' : '{_more_options}') . '</strong></span></a> <img id="showmoreicon_' . $cid . '" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/' . (!empty($ilcollapse["showmore_$cid"]) ? 'arrowup2.gif' : 'arrowdown2.gif') . '" border="0" alt="" /></div></div>'
				: '<div style="padding-left:' . $this->fetch_level_padding($viewlevel) . 'px; padding-bottom:6px; padding-top:5px" class="smaller"><a href="javascript:void(0)" onclick="toggle_more(\'showmore_' . $cid . '\', \'moretext_' . $cid . '\', \'' . '{_more_options}' . '\', \'' . '{_less_options}' . '\', \'showmoreicon_' . $cid . '\')"><span id="moretext_' . $cid . '" class="gray" style="font-weight:bold; text-decoration:none">' . (!empty($ilcollapse["showmore_$cid"]) ? '{_less_options}' : '{_more_options}') . '</strong></span></a> <img id="showmoreicon_' . $cid . '" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/' . (!empty($ilcollapse["showmore_$cid"]) ? 'arrowup2.gif' : 'arrowdown2.gif') . '" border="0" alt="" /></div>';
			if (!empty($html) AND is_array($html))
			{
				$c = 0;
				foreach ($html AS $key => $array)
				{
					if (!empty($array['htmlhead']) AND !empty($array['html']))
					{
						$c++;
						if ($c <= $ilconfig['globalauctionsettings_catquestiondepth'])
						{
							$bit['visible'] .= $html[$key]['htmlhead'] . $html[$key]['htmlblockstart'] . $html[$key]['html'] . $html[$key]['htmlblockend'] . $html[$key]['htmlhead_end'];
						}
						else
						{
							$bit['hidden'] .= $html[$key]['htmlhead'] . $html[$key]['htmlblockstart'] . $html[$key]['html'] . $html[$key]['htmlblockend'] . $html[$key]['htmlhead_end'];
						}
					}
				}
			}
			if ($questioncount <= $ilconfig['globalauctionsettings_catquestiondepth'])
			{
				$hidden = '';
			}
			$formattedhtml = "$bit[visible] <div id=\"showmore_$cid\" style=\"" . (!empty($ilcollapse["showmore_$cid"]) ? $ilcollapse["showmore_$cid"] : 'display: none;') . "\">$bit[hidden]</div>$hidden";
			$show['categoryfinder'] = ((empty($bit['visible']) OR $bit['visible'] == '') ? false : true);
		}
		$ilance->timer->stop();
		DEBUG("print_searchable_questions(\$cid = $cid, \$showcount = $showcount, \$level = $level, \$viewlevel = $viewlevel, \$forcelinks = $forcelinks, \$cattype = $cattype, \$title = $title) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
		return $formattedhtml;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>