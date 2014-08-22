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
\*========================================================================== */

/**
* Category class to perform the majority of category functions in ILance.
*
* @package      iLance\Categories
* @version	4.0.0.8059
* @author       ILance
*/
class categories
{
	var $cats = array();
	var $fetch = array();
	var $fetchsimple = array();
	var $custom = array();
	var $subs = null;
	var $cat_map = array();
	var $buildarray = false;
    
	/**
	* Constructor
	*
	* @param       bool         build the array on load? default = false
	* @param       string       category type (service, product, experts, portfolio, serviceprovider)
	* @param       string       short language identifier? default = user session lang.
	* @param       integer      category mode (0 = all,  1 = portfolio, 2 = rss, 3 = newsletters)
	* @param       boolean      enable proper category sorting (builds $this->cat & $this->fetch internally) (default true)
	*/
	function categories($buildarray = false, $cattype = 'service', $slng = 'eng', $categorymode = 0, $propersort = true)
	{
		if ($buildarray)
		{
			$this->build_array($cattype, $slng, $categorymode, $propersort);
		}
	}
    
	/**
	* Function to fetch and build the array of the category structure.  This will internally build our $ilance->categories->fetch[] array.
	* Additionally this function can sort the array using an internal sorting method if required.
	*
	* @param       string       category type (service/product)
	* @param       string       short language identifier (default eng)
	* @param       string       category mode (0 = all,  1 = portfolio, 2 = rss, 3 = newsletters)
	* @param       bool         enable proper category/parent/child sorting on the fly? (default yes)
	* @param       string       extra 1
	* @param       string       extra 2
	* @param       integer      page counter (default 0)
	* @param       integer      per page limit (default 10)
	* @param       integer      category id level depth selector (default 10)
	* @param       integer      category id selector (extra logic)
	* @param       string       category title we're searching for (extra logic)
	* @param       integer      category visibility (admincp usage mainly) (default 1)
	*
	* @return      array        Returns category array structure
	*/
	function build_array($cattype = 'service', $slng = 'eng', $categorymode = 0, $propersort = true, $extra = '', $extra2 = '', $counter = 0, $limit = -1, $level = 10, $cid = 0, $title = '', $visible = 1, $extraquery = '')
	{
		global $ilance, $ilconfig, $show;
		$ilance->timer->start();
		// #### let other scripts know we've built the array cache already
		$this->buildarray = true;
		$extracount = $extranodecount = $leftjoin = '';
		$slng = (!empty($slng)) ? $slng : fetch_site_slng();
		if ($cattype == 'stores')
		{
			$query = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "cid, storeid, category_name AS title, parentid, type, canpost, lastpost, views, itemcount AS auctioncount, visible, sort, level
				FROM " . DB_PREFIX . "stores_category
				WHERE (storeid = '" . intval($extra) . "' OR cid = '" . intval($extra2) . "')        
				ORDER BY cid, sort ASC
			", 0, null, __FILE__, __LINE__);
			while ($categories = $ilance->db->fetch_array($query, DB_ASSOC))
			{
				$this->fetch["$slng"]["$cattype"][] = $categories;
			}
			unset($categories);
		}
		else if ($cattype == 'storesmain')
		{
			$query = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "cid, storeid, category_name AS title, parentid, type, canpost, lastpost, views, itemcount AS auctioncount, visible, sort, level
				FROM " . DB_PREFIX . "stores_category
				WHERE storeid = '-1'
				ORDER BY sort ASC
			", 0, null, __FILE__, __LINE__);
			while ($categories = $ilance->db->fetch_array($query, DB_ASSOC))
			{
				$this->fetch["$slng"]["stores"][] = $categories;
			}
			unset($categories);
		}
		else if ($cattype == 'productcategorymap' OR $cattype == 'servicecategorymap')
		{
			$cattype = ($cattype == 'productcategorymap') ? 'product' : 'service';
			// #### find the root nodes ############################
			$result = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "node.cid, node.parentid, node.title_$slng AS title, node.lastpost, node.auctioncount, node.cattype, node.catimage, node.catimagehero, node.catimageherourl, node.visible, node.level, node.insertiongroup, node.finalvaluegroup, node.canpost, node.canpostclassifieds, node.lft, node.rgt, node.description_$slng AS description, node.seourl_$slng AS seourl, node.incrementgroup, node.useproxybid, node.usereserveprice, node.useantisnipe, node.views, node.budgetgroup, node.portfolio$extracount
				FROM " . DB_PREFIX . "categories node
				$leftjoin
				WHERE node.cattype = '" . $ilance->db->escape_string($cattype) . "'
				" . ((isset($level) AND $level <= 0) ? '' : "AND node.level <= '" . intval($level) . "'") . "
				" . ((isset($title) AND !empty($title)) ? "AND node.title_$slng LIKE '%" . $ilance->db->escape_string($title) . "%'" : '') . "
				" . (($cid == 0) ? '' : "AND node.cid = '" . intval($cid) . "'") . "
					AND node.visible = '1'
					$extraquery
				ORDER BY node.lft ASC
				" . (($limit == -1 OR empty($limit)) ? '' : " LIMIT " . $counter . ", " . $limit) . "
			", 0, null, __FILE__, __LINE__);
			while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
			{
				// #### add next category to our fetch array
				$this->fetch["$slng"]["$row[cattype]"]["$row[cid]"] = $row;
				$this->fetchsimple["$row[cid]"] = $row;
			}
			if ($propersort)
			{
				$ilance->timer->stop();
				DEBUG("build_array(\$cattype = $cattype, \$slng = $slng, \$categorymode = $categorymode, \$propersort = $propersort, \$extra = $extra, \$extra2 = $extra2, \$counter = $counter, \$limit = $limit, \$level = $level, \$cid = $cid, \$title = $title, \$visible = $visible) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
				return $this->propersort($slng, $cattype, $categorymode, $counter);
			}
			$ilance->timer->stop();
			DEBUG("build_array(\$cattype = $cattype, \$slng = $slng, \$categorymode = $categorymode, \$propersort = $propersort, \$extra = $extra, \$extra2 = $extra2, \$counter = $counter, \$limit = $limit, \$level = $level, \$cid = $cid, \$title = $title, \$visible = $visible) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
			return $this->fetchsimple;
		}
		else
		{
			$cattype = ($cattype == 'service' OR $cattype == 'servicecatmap' OR $cattype == 'experts' OR $cattype == 'portfolio' OR $cattype == 'serviceprovider') ? 'service' : 'product';
	    
			($apihook = $ilance->api('categories_build_array_start_top')) ? eval($apihook) : false;
	    
			// #### find the root nodes ########################################
			if ($cid == 0 OR empty($cid))
			{
				$result = $ilance->db->query("
					SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "node.cid, node.parentid, node.level, node.title_$slng AS title, node.description_$slng AS description, node.seourl_$slng AS seourl, node.canpost, node.canpostclassifieds, node.lastpost, node.views, node.xml, node.portfolio, node.newsletter, node.auctioncount, node.budgetgroup, node.insertiongroup, node.finalvaluegroup, node.incrementgroup, node.cattype, node.bidamounttypes, node.usefixedfees, node.fixedfeeamount, node.nondisclosefeeamount, node.multipleaward, node.bidgrouping, node.bidgroupdisplay, node.useproxybid, node.usereserveprice, node.useantisnipe, node.bidfields, node.catimage, node.catimagehero, node.catimageherourl, node.keywords_$slng AS keywords, node.visible, node.sort, node.lft, node.rgt$extracount
					FROM " . DB_PREFIX . "categories node
					$leftjoin
					WHERE node.cattype = '" . $ilance->db->escape_string($cattype) . "'
						" . ((isset($level) AND $level <= 0) ? '' : "AND node.level <= '" . intval($level) . "'") . "
						" . ((isset($title) AND !empty($title)) ? "AND node.title_$slng LIKE '%" . $ilance->db->escape_string($title) . "%'" : '') . "
						" . (($cid == 0) ? '' : "AND node.cid = '" . intval($cid) . "'") . "
					    $extraquery
					ORDER BY node.lft ASC
					" . (($limit == -1 OR empty($limit)) ? '' : " LIMIT " . $counter . ", " . $limit) . "
				", 0, null, __FILE__, __LINE__);
			}
			// #### find the immediate subordinates of a node ##################
			else
			{
				$result = $ilance->db->query("
					SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "node.cid, node.parentid, node.level, node.title_$slng AS title, node.description_$slng AS description, node.seourl_$slng AS seourl, node.canpost, node.canpostclassifieds, node.lastpost, node.views, node.xml, node.portfolio, node.newsletter, node.auctioncount, node.budgetgroup, node.insertiongroup, node.finalvaluegroup, node.incrementgroup, node.cattype, node.bidamounttypes, node.usefixedfees, node.fixedfeeamount, node.nondisclosefeeamount, node.multipleaward, node.bidgrouping, node.bidgroupdisplay, node.useproxybid, node.usereserveprice, node.useantisnipe, node.bidfields, node.catimage, node.catimagehero, node.catimageherourl, node.keywords_$slng AS keywords, node.visible, node.sort, node.lft, node.rgt$extranodecount
					FROM " . DB_PREFIX . "categories hp
					JOIN " . DB_PREFIX . "categories node ON node.lft BETWEEN hp.lft AND hp.rgt
					JOIN " . DB_PREFIX . "categories hr ON MBRWithin(Point(0, node.lft), hr.sets)
					$leftjoin
					WHERE hp.cid = '" . intval($cid) . "'
						AND hp.cattype = '" . $ilance->db->escape_string($cattype) . "'
						AND hr.cattype = '" . $ilance->db->escape_string($cattype) . "'
						AND node.cattype = '" . $ilance->db->escape_string($cattype) . "'
						$extraquery
					GROUP BY node.cid
					HAVING  COUNT(*) <=
					(
						SELECT  COUNT(*)
						FROM    " . DB_PREFIX . "categories hp
						JOIN    " . DB_PREFIX . "categories hrp
						ON      MBRWithin(Point(0, hp.lft), hrp.sets)
						WHERE   hp.cid = '" . intval($cid) . "'
						AND     hp.cattype = '" . $ilance->db->escape_string($cattype) . "'
						AND     hrp.cattype = '" . $ilance->db->escape_string($cattype) . "' 
					) + 2
					ORDER BY node.lft
				", 0, null, __FILE__, __LINE__);
			}
			while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
			{
				// #### add next category to our fetch array
				$this->fetch["$slng"]["$row[cattype]"]["$row[cid]"] = $row;
				$this->fetchsimple["$row[cid]"] = $row;
			}
		}
		if ($propersort)
		{
			$ilance->timer->stop();
			DEBUG("build_array(\$cattype = $cattype, \$slng = $slng, \$categorymode = $categorymode, \$propersort = $propersort, \$extra = $extra, \$extra2 = $extra2, \$counter = $counter, \$limit = $limit, \$level = $level, \$cid = $cid, \$title = $title, \$visible = $visible) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
			return $this->propersort($slng, $cattype, $categorymode, $counter);
		}
		$ilance->timer->stop();
		DEBUG("build_array(\$cattype = $cattype, \$slng = $slng, \$categorymode = $categorymode, \$propersort = $propersort, \$extra = $extra, \$extra2 = $extra2, \$counter = $counter, \$limit = $limit, \$level = $level, \$cid = $cid, \$title = $title, \$visible = $visible) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
		return $this->fetch;
	}
    
	/**
	* Function to fetch the entire category cache and make it a proper formatted result set for various areas within ILance
	*
	* @param       string       short language identifier
	* @param       string       category type (service or product)
	* @param       integer      category mode (0 = all,  1 = portfolio, 2 = rss, 3 = newsletters)
	*/
	function propersort($slng = 'eng', $cattype = 'service', $categorymode = 0, $counter = 0)
	{
		global $ilance;
		$ilance->timer->start();
		$result = array ();
		if (!empty($this->fetch["$slng"]["$cattype"]))
		{
			foreach ($this->fetch["$slng"]["$cattype"] AS $cid => $array)
			{
				if (isset($categorymode))
				{
					// portfolio
					if ($categorymode == '1')
					{
						if ($array['portfolio'])
						{
							$result[] = $array;
						}
					}
					// rss feeds
					else if ($categorymode == '2')
					{
						if ($array['xml'])
						{
							$result[] = $array;
						}
					}
					// newsletters
					else if ($categorymode == '3')
					{
						if ($array['newsletter'])
						{
							$result[] = $array;
						}
					}
					else
					{
						$result[] = $array;
					}
				}
				else
				{
					$result[] = $array;
				}
			}
			$this->cats = $result;
			unset($result);
		}
		$ilance->timer->stop();
		DEBUG("propersort(\$slng = $slng, \$cattype = $cattype, \$categorymode = $categorymode, \$counter = $counter) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
		return $this->cats;
	}
    
	/**
	* Function to process and fetch categories
	*
	* @param       array        category results array
	* @param       integer      parent id
	* @param       integer      category level
	*
	* @return      nothing
	*/
	function get_cats($result, $parentid = 0, $level = 1, $counter = 0)
	{
		global $ilance;
		$ilance->timer->start();
		$this->cats = $this->tmp_cats = array ();
		$this->get_cats_recursive($result, $parentid, $level, $counter);
		$ilance->timer->stop();
		DEBUG("get_cats(\$parentid = $parentid, \$level = $level) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
	}
    
	/**
	* Function to process and fetch categories recusively
	*
	* @param       array        category results array
	* @param       integer      category parent id
	* @param       integer      category level
	* @param       integer      counter (default 0)
	*
	* @return      nothing
	*/
	function get_cats_recursive($result, $parentid = 0, $level = 1, $counter = 0)
	{
		global $ilance;
		$ilance->GPC['pp'] = isset($ilance->GPC['pp']) ? intval($ilance->GPC['pp']) : 10;
		for ($i = $counter; $i < ($ilance->GPC['pp'] + $counter); $i++)
		{
			if ($result[$i]['parentid'] == $parentid)
			{
				$result[$i]['level'] = $level;
				$this->cats[] = $result[$i];
				$this->get_cats_recursive($result, $result[$i]['cid'], $level + 1, $i);
			}
		}
	}
    
	/**
	* Function to fetch the title of a category.
	*
	* @param       string       short language identifier (default eng)
	* @param       integer      category id
	*
	* @return      mixed        Returns category array structure (or All Categories) text otherwise
	*/
	function title($slng = 'eng', $cid = 0)
	{
		global $ilance, $phrase;
		$html = '';
		if ($this->buildarray AND is_array($this->fetch) AND isset($this->fetchsimple["$cid"]['cattype']))
		{
			$cattype = $this->fetchsimple["$cid"]['cattype'];
			$html = $this->fetch["$slng"]["$cattype"]["$cid"]['title'];
		}
		else
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "title_$slng AS title
				FROM " . DB_PREFIX . "categories
				WHERE cid = '" . intval($cid) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$html = $res['title'];
			}
		}
		if (!empty($html))
		{
			return $html;
		}
		return '{_unknown}';
	}
	
	/**
	* Function to fetch the search engine optimized URL title of a category.
	*
	* @param       string       short language identifier (default eng)
	* @param       integer      category id
	*
	* @return      mixed        Returns category title like "arts-collectables"
	*/
	function seourl($slng = 'eng', $cid = 0)
	{
		global $ilance, $phrase;
		$html = '';
		if ($this->buildarray AND is_array($this->fetch) AND isset($this->fetchsimple["$cid"]['cattype']))
		{
			$cattype = $this->fetchsimple["$cid"]['cattype'];
			$html = $this->fetch["$slng"]["$cattype"]["$cid"]['seourl'];
		}
		else
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "seourl_$slng AS seourl
				FROM " . DB_PREFIX . "categories
				WHERE cid = '" . intval($cid) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$html = $res['seourl'];
			}
		}
		if (!empty($html))
		{
			return $html;
		}
		return false;
	}
    
	/**
	* Function to fetch the visibility of a category.
	*
	* @param       string       short language identifier (default eng)
	* @param       string       category type (service/product)
	* @param       integer      category id
	*
	* @return      mixed        Returns true or false based on visibility
	*/
	function visible($cid = 0)
	{
		global $ilance;
		$html = 1;
		if ($this->buildarray AND is_array($this->fetchsimple))
		{
			$html = isset($this->fetchsimple["$cid"]['visible']) ? $this->fetchsimple["$cid"]['visible'] : 0;
		}
		else
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "visible
				FROM " . DB_PREFIX . "categories
				WHERE cid = '" . intval($cid) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$html = $res['visible'];
			}
		}
		return $html;
	}
    
	/**
	* Function to fetch the true level of a category within the category structure.
	*
	* @param       string       short language identifier (default eng)
	* @param       string       category type (service/product)
	* @param       integer      category id
	*
	* @return      mixed        Returns category level for the selected category
	*/
	function level($slng = 'eng', $cattype = '', $cid = 0)//never used
	{
		global $ilance;
		$html = 0;
		if ($this->buildarray AND is_array($this->fetchsimple))
		{
			$html = $this->fetchsimple["$cid"]['level'];
		}
		else
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "level
				FROM " . DB_PREFIX . "categories
				WHERE cid = '" . intval($cid) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$html = $res['level'];
			}
		}
		return $html;
	}
    
	/**
	* Function to determine if a specific product category has proxy bidding enabled.
	*
	* @param       string       short language identifier (default eng)
	* @param       integer      category id
	*
	* @return      mixed        Returns category array structure (or All Categories) text otherwise
	*/
	function useproxybid($slng = 'eng', $cid = 0)
	{
		global $ilance;
		$html = 0;
		if ($this->buildarray AND isset($this->fetchsimple["$cid"]['useproxybid']))
		{
			$html = $this->fetchsimple["$cid"]['useproxybid'];
		}
		else
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "useproxybid
				FROM " . DB_PREFIX . "categories
				WHERE cid = '" . intval($cid) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$html = $res['useproxybid'];
			}
		}
		return $html;
	}
    
	/**
	* Function to fetch the description text of a category.
	*
	* @param       string       short language identifier (default eng)
	* @param       integer      category id
	*
	* @return      mixed        Returns category array structure (or All Categories) text otherwise
	*/
	function description($slng = 'eng', $cid = 0)
	{
		global $ilance;
		$html = '';
		if ($this->buildarray AND is_array($this->fetch) AND isset($this->fetchsimple["$cid"]['cattype']))
		{
			$html = $this->fetch["$slng"][$this->fetchsimple["$cid"]['cattype']]["$cid"]['description'];
		}
		else
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "description_$slng AS description
				FROM " . DB_PREFIX . "categories
				WHERE cid = '" . intval($cid) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$html = $res['description'];
			}
		}
		return $html;
	}
    
	/**
	* Function to fetch the meta tag keywords text of a category.
	*
	* @param       string       short language identifier (default eng)
	* @param       integer      category id
	* @param       boolean      add comma after? (default false)
	* @param       boolean      show input keywords? (default false)
	*
	* @return      mixed        Returns category array structure (or All Categories) text otherwise
	*/
	function keywords($slng = 'eng', $cid = 0, $commaafter = false, $showinputkeywords = false)
	{
		global $ilance;
		$keywordbit = $text = $bit = $html = '';
		if ($this->buildarray AND is_array($this->fetch) AND isset($this->fetchsimple["$cid"]['cattype']))
		{
			$cattype = $this->fetchsimple["$cid"]['cattype'];
			$html = $this->fetch["$slng"]["$cattype"]["$cid"]['keywords'];
		}
		else
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "keywords_$slng AS keywords
				FROM " . DB_PREFIX . "categories
				WHERE cid = '" . intval($cid) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$html = $res['keywords'];
			}
		}
		if (!empty($html))
		{
			if ($commaafter)
			{
				$bit = ', ';
			}
			$text = $html . $bit;
		}
		if ($showinputkeywords)
		{
			if (!empty($ilance->GPC['q']))
			{
				$keywordbit = htmlspecialchars($ilance->GPC['q']) . ', ';
			}
		}
		if (!empty($text) AND $commaafter)
		{
			$text = mb_substr($text, 0, -2);
		}
		return $keywordbit . $text;
	}
    
	/**
	* Function to fetch the parentid of a category.
	*
	* @param       integer      category id
	*
	* @return      integer      Returns parentid of a category or 0 otherwise
	*/
	function parentid($cid = 0)
	{
		global $ilance;
		$html = 0;
		if ($this->buildarray AND is_array($this->fetchsimple) AND isset($this->fetchsimple["$cid"]['parentid']))
		{
			$html = $this->fetchsimple["$cid"]['parentid'];
		}
		else
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "parentid
				FROM " . DB_PREFIX . "categories
				WHERE cid = '" . intval($cid) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$html = $res['parentid'];
			}
		}
		if ($html > 0)
		{
			return $html;
		}
		return 0;
	}
    
	/**
	* Function to fetch the auction count for a category.
	*
	* @param       string       category type (service/product)
	* @param       integer      category id
	*
	* @return      integer      Returns parentid of a category or 0 otherwise
	*/
	function auctioncount($cattype = '', $cid = 0)
	{
		global $ilance, $show;
		if ($this->buildarray AND is_array($this->fetch) AND isset($this->fetch[$_SESSION['ilancedata']['user']['slng']]["$cattype"]["$cid"]['auctioncount']))
		{
			return $this->fetch[$_SESSION['ilancedata']['user']['slng']]["$cattype"]["$cid"]['auctioncount'];
		}
		else
		{
			$extracount = $leftjoin = $extraquery = '';
	    
			($apihook = $ilance->api('categories_auctioncount_top')) ? eval($apihook) : false;
	    
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "node.auctioncount$extracount
				FROM " . DB_PREFIX . "categories node
				$leftjoin
				WHERE node.cid = '" . intval($cid) . "'
				    AND node.cattype = '" . $ilance->db->escape_string($cattype) . "'
				    $extraquery
				    LIMIT 1
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				return $res['auctioncount'];
			}
		}
		return 0;
	}
    
	/**
	* Function to fetch the category type of a category.
	*
	* @param       string        short language identifier (default eng)
	* @param       integer       category id
	*
	* @return      string        Returns the category type (service/product)
	*/
	function cattype($slng = 'eng', $cid = 0)
	{
		global $ilance;
		$html = '';
		if ($this->buildarray AND is_array($this->fetchsimple) AND isset($this->fetchsimple["$cid"]['cattype']))
		{
			$html = $this->fetchsimple["$cid"]['cattype'];
		}
		else
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "cattype
				FROM " . DB_PREFIX . "categories
				WHERE cid = '" . intval($cid) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$html = $res['cattype'];
			}
		}
		return $html;
	}
    
	/**
	* Function to fetch the category increment group for a category.
	*
	* @param       integer      category id
	*
	* @return      string       Returns the category increment group name
	*/
	function incrementgroup($cid = 0)
	{
		global $ilance;
		$html = '';
		if ($this->buildarray AND is_array($this->fetchsimple))
		{
			return $this->fetchsimple["$cid"]['incrementgroup'];
		}
		else
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "incrementgroup
				FROM " . DB_PREFIX . "categories
				WHERE cid = '" . intval($cid) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				return $res['incrementgroup'];
			}
		}
		return $html;
	}
    
	/**
	* Function to determine if a category bid grouping logic is enabled or disabled
	*
	* @param       integer      category id
	*
	* @return      string       Returns true or false
	*/
	function bidgrouping($cid = 0)
	{
		global $ilance;
		$html = false;
		if ($this->buildarray AND is_array($this->fetchsimple) AND isset($this->fetchsimple["$cid"]['bidgrouping']))
		{
			return $this->fetchsimple["$cid"]['bidgrouping'];
		}
		else
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "bidgrouping
				FROM " . DB_PREFIX . "categories
				WHERE cid = '" . intval($cid) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				return $res['bidgrouping'];
			}
		}
		return $html;
	}
    
	/**
	* Function to fetch the category bid group display logic for a category
	*
	* @param       integer      category id
	*
	* @return      string       Returns the category bid group display (lowest or highest)
	*/
	function bidgroupdisplay($cid = 0)
	{
		global $ilance;
		$html = '';
		if ($this->buildarray AND is_array($this->fetchsimple))
		{
			return $this->fetchsimple["$cid"]['bidgroupdisplay'];
		}
		else
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "bidgroupdisplay
				FROM " . DB_PREFIX . "categories
				WHERE cid = '" . intval($cid) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				return $res['bidgroupdisplay'];
			}
		}
		return $html;
	}
    
	/**
	* Function to determine if a category uses fixed fees
	*
	* @param       integer      category id
	*
	* @return      string       Returns true or false
	*/
	function usefixedfees($cid = 0)
	{
		global $ilance;
		$html = 0;
		if ($this->buildarray AND is_array($this->fetchsimple))
		{
			return $this->fetchsimple["$cid"]['usefixedfees'];
		}
		else
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "usefixedfees
				FROM " . DB_PREFIX . "categories
				WHERE cid = '" . intval($cid) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				return $res['usefixedfees'];
			}
		}
		return $html;
	}
    
	/**
	* Function to fetch the fixed fee amount of a particular category
	*
	* @param       integer      category id
	*
	* @return      string       Returns fee amount
	*/
	function fixedfeeamount($cid = 0)
	{
		global $ilance;
		$html = 0;
		if ($this->buildarray AND is_array($this->fetchsimple))
		{
			return $this->fetchsimple["$cid"]['fixedfeeamount'];
		}
		else
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "fixedfeeamount
				FROM " . DB_PREFIX . "categories
				WHERE cid = '" . intval($cid) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				return $res['fixedfeeamount'];
			}
		}
		return $html;
	}
    
	/**
	* Function to fetch the bid amount types for a category
	*
	* @param       integer      category id
	*
	* @return      string       Returns a serialized string holding array with information on bid types enabled for the category.
	*/
	function bidamounttypes($cid = 0)
	{
		global $ilance;
		$html = '';
		if ($this->buildarray AND is_array($this->fetchsimple))
		{
			return $this->fetchsimple["$cid"]['bidamounttypes'];
		}
		else
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "bidamounttypes
				FROM " . DB_PREFIX . "categories
				WHERE cid = '" . intval($cid) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				return $res['bidamounttypes'];
			}
		}
		return $html;
	}
    
	function nondisclosefeeamount($cid = 0)
	{
		global $ilance;
		$html = 0;
		if ($this->buildarray AND is_array($this->fetchsimple))
		{
			return $this->fetchsimple["$cid"]['nondisclosefeeamount'];
		}
		else
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "nondisclosefeeamount
				FROM " . DB_PREFIX . "categories
				WHERE cid = '" . intval($cid) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				return $res['nondisclosefeeamount'];
			}
		}
		return $html;
	}
    
	function insertiongroup($cid = 0)
	{
		global $ilance;
		$html = '';
		if ($this->buildarray AND is_array($this->fetchsimple))
		{
			return $this->fetchsimple["$cid"]['insertiongroup'];
		}
		else
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "insertiongroup
				FROM " . DB_PREFIX . "categories
				WHERE cid = '" . intval($cid) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				return $res['insertiongroup'];
			}
		}
		return $html;
	}
    
	function budgetgroup($cid = 0)
	{
		global $ilance;
		$html = '';
		if ($this->buildarray AND is_array($this->fetchsimple))
		{
			return $this->fetchsimple["$cid"]['budgetgroup'];
		}
		else
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "budgetgroup
				FROM " . DB_PREFIX . "categories
				WHERE cid = '" . intval($cid) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				return $res['budgetgroup'];
			}
		}
		return $html;
	}
    
	function finalvaluegroup($cid = 0)
	{
		global $ilance;
		$html = '';
		if ($this->buildarray AND is_array($this->fetchsimple))
		{
			return $this->fetchsimple["$cid"]['finalvaluegroup'];
		}
		else
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "finalvaluegroup
				FROM " . DB_PREFIX . "categories
				WHERE cid = '" . intval($cid) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				return $res['finalvaluegroup'];
			}
		}
		return $html;
	}
    
	function usereserveprice($cid = 0)
	{
		global $ilance;
		$html = 0;
		if ($this->buildarray AND is_array($this->fetchsimple) AND isset($this->fetchsimple["$cid"]))
		{
			return $this->fetchsimple["$cid"]['usereserveprice'];
		}
		else
		{
		    $sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "usereserveprice
				FROM " . DB_PREFIX . "categories
				WHERE cid = '" . intval($cid) . "'
				LIMIT 1
		    ");
		    if ($ilance->db->num_rows($sql) > 0)
		    {
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				return $res['usereserveprice'];
		    }
		}
		return $html;
	}
	
	function catimageherourl($cid = 0)
	{
		global $ilance;
		$html = 0;
		if ($this->buildarray AND is_array($this->fetchsimple) AND isset($this->fetchsimple["$cid"]))
		{
			return $this->fetchsimple["$cid"]['catimageherourl'];
		}
		else
		{
		    $sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "catimageherourl
				FROM " . DB_PREFIX . "categories
				WHERE cid = '" . intval($cid) . "'
				LIMIT 1
		    ");
		    if ($ilance->db->num_rows($sql) > 0)
		    {
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				return $res['catimageherourl'];
		    }
		}
		return $html;
	}
    
	/**
	* Function to determine if a category is a postable auction category.
	*
	* @param       string       short language identifier (default eng)
	* @param       string       category type (service/product)
	* @param       integer      category id
	*
	* @return      string       Returns true or false response
	*/
	function can_post($slng = 'eng', $cattype = '', $cid = 0)
	{
		global $ilance, $show;
		$html = 0;
	
		($apihook = $ilance->api('categories_can_post_start')) ? eval($apihook) : false;
	
		if ($this->buildarray AND is_array($this->fetchsimple) AND isset($this->fetchsimple["$cid"]['canpost']))
		{
			return $this->fetchsimple["$cid"]['canpost'];
		}
		else
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "canpost
				FROM " . DB_PREFIX . "categories
				WHERE cid = '" . intval($cid) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				return $res['canpost'];
			}
		}
		return $html;
	}
    
	/**
	* Function to determine if a category is a postable auction category.
	*
	* @param       string       short language identifier (default eng)
	* @param       string       category type (service/product)
	* @param       integer      category id
	*
	* @return      string       Returns true or false response
	*/
	function can_post_classified($slng = 'eng', $cattype = '', $cid = 0)
	{
		global $ilance, $show;
		$html = 0;
	
		($apihook = $ilance->api('categories_can_post_classified_start')) ? eval($apihook) : false;
	
		if ($this->buildarray AND is_array($this->fetchsimple) AND isset($this->fetchsimple["$cid"]['canpostclassifieds']))
		{
			return $this->fetchsimple["$cid"]['canpostclassifieds'];
		}
		else
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "canpostclassifieds
				FROM " . DB_PREFIX . "categories
				WHERE cid = '" . intval($cid) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				return $res['canpostclassifieds'];
			}
		}
		return $html;
	}
    
	/**
	* Function to determine if a category is proxy bid ready (if proxy bidding is enabled)
	*
	* @param       string       short language identifier (default eng)
	* @param       string       category type (service/product)
	* @param       integer      category id
	*
	* @return      string       Returns true or false response
	*/
	function proxy_bid_ready($slng = 'eng', $cattype = '', $cid = 0)
	{
		global $ilance;
		$useproxybid = false;
		$incrementgroup = '';
		$useproxybid = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "useproxybid", "1");
		if ($useproxybid)
		{
			$incrementgroup = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "incrementgroup", "1");
			if ($incrementgroup != '0' AND !empty($incrementgroup) AND $incrementgroup != '')
			{
				return true;
			}
			return false;
		}
		return true;
	}
    
	/**
	* Function to fetch a category count based on a set of search options per specific category.
	* For example, say Web Design normally by itself has 300 auctions.  If a user enters a search for keyword
	* "template" and 35 results were found, "35" would be that category's counter: Web Design (35) even though
	* the current category selected is "Programming".  This can be considered a mini-internal search engine for
	* category listing counts.
	*
	* @param       string       category id
	* @param       string       category type (service/product/serviceprovider)
	* @param       integer      search sql query (array)
	*
	* @return      integer      Returns category counter number
	*/
	function bestmatch_auction_count($cid = 0, $cattype = 'service', $sqlquery = array ())
	{
		global $ilance;
		$ilance->timer->start();
		if ($cattype == 'wantads')
		{
			$sqlquery['cidfield'] = 'cid';
			if (isset($ilance->GPC['wantads_data']) AND $ilance->GPC['wantads_data'])
			{
				$sqlquery['cidfield'] = 'p.cid';
			}
		}
		else
		{
			$sqlquery['cidfield'] = 'p.cid';
		}
		$count = 0;
		if (is_array($sqlquery))
		{
			if ($cattype == 'serviceprovider')
			{
				$cattype = 'service';
			}
			if ($cattype == 'wantads')
			{
				$cids = $ilance->wantads->fetch_children_ids($cid);
			}
			else
			{
				$cids = $this->fetch_children_ids($cid, $cattype);
			}
			if (!empty($cids))
			{
				$subcategorylist = $cid . ',' . $cids;
				$sqlquery['categories'] = "AND (FIND_IN_SET($sqlquery[cidfield], '" . $subcategorylist . "'))";
			}
			else
			{
				$sqlquery['categories'] = "AND (FIND_IN_SET($sqlquery[cidfield], '" . $cid . "'))";
			}
			if ($cattype == 'serviceprovider')
			{
				$sql = "$sqlquery[select] $sqlquery[keywords] $sqlquery[categories] $sqlquery[options] $sqlquery[location] $sqlquery[radius] $sqlquery[userquery] $sqlquery[hidequery] $sqlquery[pricerange] $sqlquery[genrequery] $sqlquery[profileanswersquery] $sqlquery[skillsquery] $sqlquery[groupby] $sqlquery[orderby]";
			}
			else if ($cattype == 'wantads')
			{
				$sql = "$sqlquery[select] $sqlquery[keywords] $sqlquery[categories] $sqlquery[options] $sqlquery[groupby] $sqlquery[orderby]";
			}
			else
			{
				$sql = "$sqlquery[select] $sqlquery[timestamp] $sqlquery[projectstatus] $sqlquery[keywords] $sqlquery[categories] $sqlquery[projectdetails] $sqlquery[projectstate] $sqlquery[options] $sqlquery[pricerange] $sqlquery[location] $sqlquery[radius] $sqlquery[userquery] $sqlquery[hidequery] $sqlquery[genrequery] $sqlquery[groupby] $sqlquery[orderby]";
			}
			$rows = $ilance->db->query($sql);
			$count = $ilance->db->num_rows($rows);
		}
		$ilance->timer->stop();
		DEBUG("bestmatch_auction_count(\$cid = $cid, \$cattype = $cattype) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
		return $count;
	}
    
	/**
	* Function to print a recursive category breadcrumb trail.
	*
	* @param       integer      category id
	* @param       string       category type (service/product)
	* @param       string       short language identifier (default eng)
	* @param       boolean      no urls flag (default off)
	* @param       string       custom url (optional)
	* @param       boolean      enable seo urls (default off)
	*
	* @return      string       Returns HTML formatted version of the breadcrumb trail
	*/
	function recursive($cid = 0, $cattype = 'service', $slng = 'eng', $nourls = 0, $customurl = '', $seourls = 0)
	{
		global $ilance, $ilconfig, $navcrumb, $ilpage, $phrase, $htmlx, $show, $storeid;
		$ilance->timer->start();
		$sid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
		if ((empty($cattype) OR $cattype == '') OR ($cattype == 'service' OR $cattype == 'product') OR ($cattype == 'servicecatmap' OR $cattype == 'productcatmap'))
		{
			$dbtable = DB_PREFIX . "categories";
			$cidname = 'cid';
			$titlefield = "title_$slng";
		}
    
		($apihook = $ilance->api('categories_recursive_start')) ? eval($apihook) : false;
    
		$htmlx = '';
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "$cidname AS cid, $titlefield AS title, parentid
			FROM $dbtable
			WHERE $cidname = '" . intval($cid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$this->recursive($res['parentid'], $cattype, $slng, $nourls, $customurl, $seourls);
			if ($cattype == 'service' OR $cattype == 'servicecatmap')
			{
				if ($nourls)
				{
					$htmlx .= stripslashes($res['title']) . ' > ';
				}
				else
				{
					if (!empty($customurl))
					{
						if ($sid == $cid)
						{
							$htmlx .= stripslashes($res['title']) . ' > ';
						}
						else
						{
							$htmlx .= '<a href="' . $customurl . '&amp;cid=' . $res['cid'] . '">' . stripslashes($res['title']) . '</a> > ';
						}
					}
					else
					{
						if ($seourls)
						{
							if ($sid == $cid)
							{
								$htmlx .= stripslashes($res['title']) . ' > ';
							}
							else
							{
								if ($cattype == 'service')
								{
									$seotype = 'servicecat';
								}
								else if ($cattype == 'servicecatmap')
								{
									$seotype = 'servicecatmap';
								}
								$show['nourlbit'] = true;
								$htmlx .= construct_seo_url($seotype, $res['cid'], 0, stripslashes($res['title']), '', 0, '', 0, 0) . ' > ';
							}
						}
						else
						{
							if ($sid == $cid)
							{
								$htmlx .= stripslashes($res['title']) . ' > ';
							}
							else
							{
								$htmlx .= '<a href="' . $ilpage['rfp'] . '?cid=' . $res['cid'] . '">' . stripslashes($res['title']) . '</a> > ';
							}
						}
					}
				}
			}
			if ($cattype == 'product' OR $cattype == 'productcatmap')
			{
				if ($nourls)
				{
					$htmlx .= stripslashes($res['title']) . ' > ';
				}
				else
				{
					if (!empty($customurl))
					{
						if ($sid == $cid)
						{
							$htmlx .= stripslashes($res['title']) . ' > ';
						}
						else
						{
							$htmlx .= '<a href="' . $customurl . '&amp;cid=' . $res['cid'] . '">' . stripslashes($res['title']) . '</a> > ';
						}
					}
					else
					{
						if ($seourls)
						{
							if ($sid == $cid)
							{
								$htmlx .= stripslashes($res['title']) . ' > ';
							}
							else
							{
								if ($cattype == 'product')
								{
									$seotype = 'productcat';
								}
								else if ($cattype == 'productcatmap')
								{
									$seotype = 'productcatmap';
								}
								$show['nourlbit'] = true;
								$htmlx .= construct_seo_url($seotype, $res['cid'], 0, stripslashes($res['title']), '', 0, '', 0, 0) . ' > ';
							}
						}
						else
						{
							if ($sid == $cid)
							{
								$htmlx .= stripslashes($res['title']) . ' > ';
							}
							else
							{
								$htmlx .= '<a href="' . $ilpage['merch'] . '?cid=' . $res['cid'] . '">' . stripslashes($res['title']) . '</a> > ';
							}
						}
					}
				}
			}
	    
			($apihook = $ilance->api('categories_recursive_conditions')) ? eval($apihook) : false;
		}
		$html = ($nourls) ? mb_substr($htmlx, 0, -3) : mb_substr($htmlx, 0, -3);
		$html .= (isset($show['submit']) AND $show['submit']) ? ' <img vspace="-2" hspace="3" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="" width="16" height="16" />' : '';
	
		($apihook = $ilance->api('categories_recursive_end')) ? eval($apihook) : false;
	
		$ilance->timer->stop();
		DEBUG("recursive(\$cid = $cid, \$cattype = $cattype) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
		return $html;
	}
    
	/**
	* Function to generate the main header breadcrumb category trail.
	*
	* @param       integer      category id
	* @param       string       category type (service/product)
	* @param       string       short language identifier (default eng)
	*
	* @return      array        Returns array $navcrumb breadcrumb trail
	*/
	function breadcrumb($cid = 0, $cattype = 'service', $slng = 'eng')
	{
		global $ilance, $ilconfig, $ilpage, $phrase, $navcrumb, $show;
	
		$ilance->timer->start();
	
		// #### handle category type for database ######################
		switch ($cattype)
		{
			case 'service':
			case 'servicecatmap':
			case 'experts':
			case 'portfolio':
			{
				$ctype = 'service';
				break;
			}
			case 'product':
			case 'productcatmap':
			{
				$ctype = 'product';
				break;
			}
			default:
			{
				$ctype = $cattype;
			}
		}
	
		($apihook = $ilance->api('breadcrumb_start')) ? eval($apihook) : false;
	
		// #### fetch our nested breadcrumb bit for this category ######
		$result = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "parent.*, parent.title_$slng AS title, parent.description_$slng AS description
			FROM " . DB_PREFIX . "categories AS child,
			" . DB_PREFIX . "categories AS parent
			WHERE child.lft BETWEEN parent.lft AND parent.rgt
				AND parent.cattype = '" . $ilance->db->escape_string($ctype) . "'
				AND child.cattype = '" . $ilance->db->escape_string($ctype) . "'
				AND child.cid = '" . intval($cid) . "'
			ORDER BY parent.lft
		");
		$resultscount = $ilance->db->num_rows($result);
		if ($resultscount > 0)
		{
			while ($results = $ilance->db->fetch_array($result, DB_ASSOC))
			{
				if ($cid == 0 AND defined('LOCATION') AND LOCATION == 'search')
				{
					if (!empty($ilance->GPC['q']))
					{
						$navcrumb["$ilpage[search]?q=" . htmlspecialchars($ilance->GPC['q'])] = '{_keywords}' . ': ' . htmlspecialchars($ilance->GPC['q']);
					}
					return $navcrumb;
				}
				if ($cattype == 'service')
				{
					if ($ilconfig['globalauctionsettings_seourls'])
					{
						$url = construct_seo_url('servicecatplain', $results['cid'], 0, $results['title'], '', 0, '', 0, 0);
						$navcrumb["$url"] = $results['title'];
						unset($url);
					}
					else
					{
						$navcrumb["$ilpage[rfp]?cid=" . $results['cid']] = $results['title'];
					}
				}
				else if ($cattype == 'servicecatmap')
				{
					if ($ilconfig['globalauctionsettings_seourls'])
					{
						$url = construct_seo_url('servicecatmapplain', $results['cid'], 0, $results['title'], '', 0, '', 0, 0);
						$catmap = print_seo_url($ilconfig['servicecatmapidentifier']);
						$catmap2 = print_seo_url($ilconfig['categoryidentifier']);
						$navcrumb["$catmap"] = '{_browse}';
						$navcrumb["$url"] = $results['title'];
						unset($catmap, $catmap2, $url);
					}
					else
					{
						$navcrumb["$ilpage[rfp]?cmd=listings"] = '{_browse}';
						$navcrumb["$ilpage[rfp]?cmd=listings&amp;cid=" . $results['cid']] = $results['title'];
					}
				}
				else if ($cattype == 'product')
				{
					if ($ilconfig['globalauctionsettings_seourls'])
					{
						$url = construct_seo_url('productcatplain', $results['cid'], 0, $results['title'], '', 0, '', 0, 0);
						$navcrumb["$url"] = $results['title'];
						unset($url);
					}
					else
					{
						$navcrumb["$ilpage[merch]?cid=" . $results['cid']] = $results['title'];
					}
				}
				else if ($cattype == 'productcatmap')
				{
					if ($ilconfig['globalauctionsettings_seourls'])
					{
						$url = construct_seo_url('productcatmapplain', $results['cid'], 0, $results['title'], '', 0, '', 0, 0);
						$catmap = print_seo_url($ilconfig['productcatmapidentifier']);
						$catmap2 = print_seo_url($ilconfig['categoryidentifier']);
						$navcrumb["$catmap"] = '{_buy}';
						$navcrumb["$url"] = $results['title'];
						unset($catmap, $catmap2, $url);
					}
					else
					{
						$navcrumb["$ilpage[main]?cmd=categories"] = '{_categories}';
						$navcrumb["$ilpage[merch]?cmd=listings"] = '{_buy}';
						$navcrumb["$ilpage[merch]?cmd=listings&amp;cid=" . $results['cid']] = $results['title'];
					}
				}
				else if ($cattype == 'experts')
				{
					if ($ilconfig['globalauctionsettings_seourls'])
					{
						$url = construct_seo_url('serviceprovidercatplain', $results['cid'], 0, $results['title'], '', 0, '', 0, 0);
						$navcrumb["$ilpage[search]?mode=experts"] = '{_browse}' . ' ' . '{_experts}';
						$navcrumb["$url"] = $results['title'];
						unset($url);
					}
					else
					{
						$navcrumb["$ilpage[search]?mode=experts"] = '{_browse}' . ' ' . '{_experts}';
						$navcrumb["$ilpage[search]?mode=experts&cid=" . $results['cid']] = $results['title'];
					}
				}
				else if ($cattype == 'portfolio')
				{
					if ($ilconfig['globalauctionsettings_seourls'])
					{
						$url = construct_seo_url('portfoliocatplain', $results['cid'], 0, $results['title'], '', 0, '', 0, 0);
						$navcrumb["$url"] = $results['title'];
						unset($url);
					}
					else
					{
						$navcrumb["$ilpage[portfolio]?cid=" . $results['cid']] = $results['title'];
					}
				}
			}
		}
	
		($apihook = $ilance->api('breadcrumb_end')) ? eval($apihook) : false;
	
		$ilance->timer->stop();
		DEBUG("breadcrumb(\$cid = $cid, \$cattype = $cattype) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
	}
    
	function fetch_category_question_answer_count($ctype = '', $qid = 0)
	{
		global $ilance, $ilconfig;
		$table = ($ctype == 'service') ? "project_answers" : "product_answers";
		$sql = $ilance->db->query("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . $table . "
			WHERE questionid = '" . intval($qid) . "'
		");
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		return number_format($res['count']);
	}
    
	/**
	* Function to fetch the padding based on a particular category level we're currently viewing
	*
	* @param       integer      level
	*
	* @return      string       Returns HTML formatted table with category results
	*/
	function fetch_level_padding($level = 0)
	{
		$padding = array (
		    '0' => '0',
		    '1' => '0',
		    '2' => '15',
		    '3' => '30',
		    '4' => '45',
		    '5' => '60',
		    '6' => '75',
		    '7' => '90',
		    '8' => '105',
		);
		return $padding["$level"];
	}
    
	/**
	* Function to print a category's "new" icon representing any new auctions posted within that category.
	* This functin has been updated to use a better method of displaying new category information without a database query.
	*
	* @param       string       date string (YYYY-MM-DD HH:MM:SS)
	*
	* @return      string       Returns false OR HTML representation of new category notice bit
	*/
	function print_category_newicon($lastpost = '', $cid = '', $cattype = '')
	{
		global $ilance, $ilconfig, $show;
		$ilance->timer->start();
		if ($ilconfig['globalauctionsettings_newicondays'] <= 0 OR $lastpost == '0000-00-00 00:00:00' OR $lastpost == '')
		{
			return false;
		}
		if (defined('LOCATION') AND (LOCATION == 'portfolio' OR LOCATION == 'search'))
		{
			return false;
		}
		$html = '';
		$explode = explode(' ', $lastpost);
		$lastpostdate = $explode[0];
		$today = DATETODAY;
		$lastpostpieces = explode('-', $lastpostdate);
		$todaypeices = explode('-', $today);
		$daysago = $ilance->datetimes->fetch_days_between($lastpostpieces[1], $lastpostpieces[2], $lastpostpieces[0], $todaypeices[1], $todaypeices[2], $todaypeices[0]); //mm1, dd1, yyyy1, mm2, dd2, yyyy2
		if (!empty($cid))
		{
			if (empty($cattype))
			{
				$cattype = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . $cid . "'", "cattype");
			}
			$child_cid = $this->fetch_children_ids($cid, $cattype);
			$child_cid = (empty($child_cid)) ? $cid : $child_cid . ',' . $cid;
			$sql1 = $ilance->db->query("
			    SELECT project_id
			    FROM " . DB_PREFIX . "projects
			    WHERE status = 'open'
			    AND DATE_ADD(date_starts , INTERVAL '" . $ilconfig['globalauctionsettings_newicondays'] . "' DAY) > '" . DATETIME24H . "'
			    AND visible = '1'
			    AND cid IN (" . $ilance->db->escape_string($child_cid) . ")
			");
			if ($ilance->db->num_rows($sql1) > 0)
			{
				if ($daysago <= $ilconfig['globalauctionsettings_newicondays'])
				{
					$html = '<span title="{_new_projects_have_been_posted}: {_past} ' . $ilconfig['globalauctionsettings_newicondays'] . ' {_days_lower}"><em><sup style="color:red; font-size:9px">{_new}</sup></em></span>';
				}
			}
		}
		$ilance->timer->stop();
		DEBUG("print_category_newicon(\$lastpost = $lastpost) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
		return $html;
	}
    
	/**
	* Function to bridge the category system for usage with Want Ads and other add on products like Stores
	*
	* @param       string       category mode
	* @param       integer      columns (default 3)
	* @param       integer      category id
	* @param       integer      store id (optional)
	*
	* @return      string       Returns HTML formatted category output display
	*/
	function construct_categories($mode = '', $columns = 3, $cid = 0, $storeid = '')
	{
		global $ilance, $phrase, $ilconfig, $ilpage, $show;
	
		($apihook = $ilance->api('categories_construct_categories_start')) ? eval($apihook) : false;
		($apihook = $ilance->api('categories_construct_categories_end')) ? eval($apihook) : false;
	
		return $html;
	}
    
	/**
	* Function to bridge the category system with different name for usage with Want Ads and other add on products like Stores
	*
	* @param       string       category mode
	* @param       integer      columns (default 3)
	* @param       integer      category id
	* @param       integer      store id (optional)
	*
	* @return      string       Returns HTML formatted category output display
	*/
	function construct_categories_array($mode = '', $columns = 3, $cid = 0, $storeid = '', $name = 'cid')
	{
		global $ilance, $phrase, $ilconfig, $ilpage, $show;
	
		($apihook = $ilance->api('categories_construct_categories_start_array')) ? eval($apihook) : false;
		($apihook = $ilance->api('categories_construct_categories_start_array')) ? eval($apihook) : false;
	
		return $html;
	}
    
	/**
	* Function to
	*
	* @param       integer      parent category id
	* @param       integer      current level
	* @param       string       category type (service/product)
	* @param       string       option groupns
	* @param       string       add spaces (optional)
	*
	* @return      string       Returns xxx
	*/
	function display_children($parentid = 0, $level = 0, $cattype = '', $optgroups = '', $addspaces = '')
	{
		global $ilance, $show;
		$ilance->timer->start();
		$html = '';
		if ($cattype == 'service' OR $cattype == 'product')
		{
			$result = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "cid, parentid, title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title, canpost
				FROM " . DB_PREFIX . "categories
				WHERE parentid = '" . intval($parentid) . "'
					AND visible = '1'
					AND cattype = '$cattype'
			", 0, null, __FILE__, __LINE__);
		}
	
		($apihook = $ilance->api('categories_display_children_condition')) ? eval($apihook) : false;
	
		// #### display each child #####################################
		while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
		{
			$html .= ($row['parentid'] == '-1' OR $row['parentid'] == '0' OR $row['parentid'] == '') ? '<option value="' . $row['cid'] . '">' . stripslashes($row['title']) . '</option>' : '<option value="' . $row['cid'] . '">' . str_repeat('&nbsp;&nbsp;&nbsp;', $level) . stripslashes($row['title']) . '</option>';
			$html .= (isset($cattype) AND !empty($cattype)) ? $this->display_children($row['cid'], ($level + 1), $cattype) : '';
		}
	
		// #### other external addon product category logic ############
		($apihook = $ilance->api('categories_display_children_end')) ? eval($apihook) : false;
	
		$ilance->timer->stop();
		DEBUG("display_children(\$parentid = $parentid) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
		return $html;
	}
    
	/**
	* Function to provide add-on products with a useable API bridge allowing the generation of multi-select fields such as categories.
	*
	* @param       string       category type
	* @param       string       selected category
	* @param       bool         single pulldown mode?
	* @param       bool         hide all categories? (default false)
	* @param       string       select field name
	* @param       bool         show "Please Select: " option in the pulldown (default false)
	* @param       integer      category level (used for pulldown option spacing) (default 1)
	*
	* @return      string       Returns HTML formatted pulldown/select menu
	*/
	function api_multicategory_select($cattype = '', $selected = '', $singlepulldown = false, $hideallcats = false, $selectname = 'cid', $pleaseselect = false, $level = 0)
	{
		global $ilance, $phrase, $show;
		$ilance->timer->start();
		$html = '';
		// #### single pulldown select menu ############################
		if ($singlepulldown)
		{
			// #### begin select menu ##############################
			$html .= '<select name="' . $selectname . '" style="font-family: verdana">';
			$html .= ($hideallcats == false) ? '<option value="">{_best_matching}</option><option value="">----------------------------------</option>' : '';
			$html .= ($pleaseselect) ? '<option value="">' . '{_please_select}' . '</option>' : '';
			// #### handler for core ilance service/product categories
			$html .= ($cattype == 'service' OR $cattype == 'product') ? $this->display_children($selected, ($level + 1), $cattype) : '';
	    
			// #### handler for other addon products (lancebb, wantads, etc)
			($apihook = $ilance->api('categories_api_multicategory_select_single_condition')) ? eval($apihook) : false;
	    
			// #### end select menu ################################
			$html .= '</select>';
		}
		// #### multiple pulldown select menu ##########################
		else
		{
			// #### handler for other addon products (lancebb, wantads, etc)
			($apihook = $ilance->api('categories_api_multicategory_select_multiple_condition')) ? eval($apihook) : false;
		}
		$ilance->timer->stop();
		DEBUG("api_multicategory_select(\$cattype = $cattype) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
		return $html;
	}
    
	/**
	* Function to fetch all children category id numbers recursivly in comma separated values based on a parent category id number.
	* This function is useful because it reads from the cache and does not hit the database.
	*
	* @param       string         category id number (or all)
	* @param       string         category type (service or product)
	*
	* @return      string         Returns category id's in comma separate values (ie: 1,3,4,6)
	*/
	function fetch_children_ids($cid = 'all', $cattype = 'service', $extraquery = '')
	{
		global $ilance, $show;
	
		if (empty($cattype))
		{
			return false;
		}
	
		($apihook = $ilance->api('fetch_children_ids_start')) ? eval($apihook) : false;
	
		$c = 0;
		$ids = '';
		$cattypex = empty($cattype) ? '' : '_' . $cattype;
		$queryname = empty($extraquery) ? '' : '_' . md5($extraquery);
		if (($ids = $ilance->cache->fetch("fetch_children_ids_" . $cid . $cattypex . $queryname)) === false)
		{
			if ($cid == 'all')
			{
				$sql = $ilance->db->query("
					SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "cid
					FROM " . DB_PREFIX . "categories
					WHERE cattype = '" . $ilance->db->escape_string($cattype) . "'
						AND visible = '1'
						$extraquery
					ORDER BY lft ASC
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
					{
						$ids .= $res['cid'] . ',';
					}
				}
			}
			else
			{
				$sql = $ilance->db->query("
				    SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "lft, rgt
				    FROM " . DB_PREFIX . "categories
				    WHERE cid = '" . intval($cid) . "'
					AND cattype = '" . $ilance->db->escape_string($cattype) . "'
					$extraquery
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$res = $ilance->db->fetch_array($sql, DB_ASSOC);
					$sql2 = $ilance->db->query("
						SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "cid
						FROM " . DB_PREFIX . "categories
						WHERE lft >= '" . $res['lft'] . "'
							AND rgt <= '" . $res['rgt'] . "'
							AND cattype = '" . $ilance->db->escape_string($cattype) . "'
							$extraquery
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql2) > 0)
					{
						while ($res2 = $ilance->db->fetch_array($sql2, DB_ASSOC))
						{
							if ($res2['cid'] != $cid)
							{
								$ids .= $res2['cid'] . ',';
							}
						}
					}
				}
			}
			$ids = !empty($ids) ? rtrim($ids, ',') : '';
			$ilance->cache->store("fetch_children_ids_" . $cid . $cattypex . $queryname, $ids);
		}
	
		($apihook = $ilance->api('fetch_children_ids_end')) ? eval($apihook) : false;
	
		return $ids;
	}
    
	/**
	* Function to fetch all parent category id numbers recursivly in comma separated values based on a child category id number.
	* This function is useful because it reads from the cache and does not hit the database.
	*
	* @param       string         category id number (or all)
	* @param       string         category type (service or product)
	*
	* @return      string         Returns category id's in comma separate values (ie: 1,3,4,6)
	*/
	function fetch_parent_ids($cid = 0, $extraquery = '')
	{
		global $ilance;
	
		($apihook = $ilance->api('fetch_parent_ids_start')) ? eval($apihook) : false;
	
		$ids = '';
		$queryname = empty($extraquery) ? '' : '_' . md5($extraquery);
		if (($ids = $ilance->cache->fetch("fetch_parent_ids_" . $cid . $queryname)) === false)
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "parent.cid
				FROM " . DB_PREFIX . "categories AS node,
				" . DB_PREFIX . "categories AS parent
				WHERE node.lft BETWEEN parent.lft AND parent.rgt
					AND node.cid = '" . intval($cid) . "'
					$extraquery
				ORDER BY parent.lft
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					$ids .= $res['cid'] . ',';
				}
			}
			$ids = !empty($ids) ? rtrim($ids, ',') : '';
			$ilance->cache->store("fetch_parent_ids_" . $cid . $queryname, $ids);
		}
	
		($apihook = $ilance->api('fetch_parent_ids_end')) ? eval($apihook) : false;
	
		return $ids;
	}
    
	/**
	* Function to fetch all category id numbers recursivly in comma separated values based on a child category id number.
	* This function is useful because it reads from the cache and does not hit the database.
	*
	* @param       string         category id number (or all)
	* @param       string         category type (service or product)
	*
	* @return      string         Returns category id's in comma separate values (ie: 1,3,4,6)
	*/
	function fetch_cat_ids($cid = 0, $extraquery = '', $cattype = 'service')
	{
		global $ilance;
	
		($apihook = $ilance->api('fetch_parent_ids_start')) ? eval($apihook) : false;
	
		$queryname = empty($extraquery) ? '' : '_' . md5($extraquery);
		$ids = '';
		$name = "fetch_cat_ids_" . $cid . $queryname;
		if (($ids = $ilance->cache->fetch($name)) === false)
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "parent.cid, parent.parentid
				FROM " . DB_PREFIX . "categories AS node,
				" . DB_PREFIX . "categories AS parent
				WHERE node.lft BETWEEN parent.lft AND parent.rgt
					AND node.cid = '" . $cid . "'
					AND parent.cattype = '" . $cattype . "'
					$extraquery
				ORDER BY parent.lft DESC
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					$ids .= $res['cid'] . ',';
					if ($res['parentid'] == 0)
					{
						return rtrim($ids, ',');
					}
				}
			}
			$ilance->cache->store($name, $ids);
		}
	
		($apihook = $ilance->api('fetch_parent_ids_end')) ? eval($apihook) : false;
	}
    
	/**
	* Function to fetch all children category id numbers returns in comma separated values.
	*
	* @param       integer        category id number (or all)
	* @param       string         category type (service/product)
	*
	* @return      string         Returns category id's in comma separate values (ie: 1,3,4,6)
	*/
	function fetch_children($cid = 0, $cattype = 'service')
	{
		global $ilance;
		$ilance->timer->start();
		$ids = $this->fetch_children_ids($cid, $cattype);
		if (empty($ids))
		{
			$ids = $cid;
		}
		else
		{
			$ids = $cid . ',' . $ids;
		}
		$ilance->timer->stop();
		DEBUG("fetch_children(\$cid = $cid, \$cattype = $cattype) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
		return rtrim($ids, ',');
	}
    
	/**
	* Function to determine if a category contains any child categories
	*
	* @param       integer        category id number (or all)
	* @param       string         category type (service/product)
	*
	* @return      string         Returns true or false
	*/
	function has_children_categories($cid = 0, $cattype = 'service')
	{
		global $ilance;
		$ids = $this->fetch_children_ids($cid, $cattype);
		if (empty($ids))
		{
			return false;
		}
		return true;
	}
    
	/**
	* Function to move a listing from one category to another category within the marketplace.
	*
	* @param       integer        listing id
	* @param       integer        old category id
	* @param       integer        new category id
	* @param       string         new category type
	* @param       string         old listing status
	* @param       string         new listing status
	*
	* @return      string         Returns true or false if listing was moved to the new category
	*/
	function move_listing_category_from_to($pid = 0, $old_catid = 0, $cid = 0, $ctype = 'service', $old_status = '', $status = '')
	{
		global $ilance, $ilconfig, $phrase;
		if ($ctype == 'service')
		{
			$table1 = 'project_questions';
			$table2 = 'project_answers';
		}
		else if ($ctype == 'product')
		{
			$table1 = 'product_questions';
			$table2 = 'product_answers';
		}
		if ($old_catid != $cid AND $pid > 0)
		{
			// remove listing answers for this category
			$sql = $ilance->db->query("
				SELECT questionid
				FROM " . DB_PREFIX . $table1 . "
				WHERE cid = '" . $old_catid . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				while ($questions = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					$ilance->db->query("
						DELETE FROM " . DB_PREFIX . $table2 . "
						WHERE questionid = '" . $questions['questionid'] . "'
							AND project_id = '" . intval($pid) . "'
					", 0, null, __FILE__, __LINE__);
				}
			}
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "attachment
				SET category_id = '" . intval($cid) . "'
				WHERE project_id = '" . intval($pid) . "'
			", 0, null, __FILE__, __LINE__);
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET cid = '" . intval($cid) . "'
				WHERE project_id = '" . intval($pid) . "'
			", 0, null, __FILE__, __LINE__);
			if (fetch_auction('visible', $pid) == '1')
			{
				$this->build_category_count($cid, 'add', "adding category count to #$cid");
			}
			$this->build_category_count($old_catid, 'subtract', "subtracting category count from $old_catid");
		}
		if ($status != $old_status AND $pid > 0)
		{
			if ($status == 'open')
			{
				$this->build_category_count($cid, 'add', "adding category count to #$cid and setting status of listing to $status from $old_status");
			}
			else if ($status == 'closed' OR $status == 'expired' OR $status == 'delisted' OR $status == 'frozen' OR $status == 'finished' OR $status == 'archived')
			{
				if ($old_status == 'open')
				{
					$this->build_category_count($old_catid, 'subtract', "setting status of listing to $status from $old_status: subtracting increment count from old category id $old_catid");
				}
			}
		}
		else if (fetch_auction('visible', $pid) == '0')
		{
			$this->build_category_count($old_catid, 'subtract');
		}
	}
    
	/**
	* Function to increment a category's view count + 1.  This function now supports recursively tracking all views
	* within a parent and child relationship.
	*
	* @param       integer        category id
	* @param       string         mode (add or subtract) default add
	*
	* @return      nothing
	*/
	function add_category_viewcount($cid = 0, $mode = 'add')
	{
		global $ilance, $ilconfig, $phrase, $show, $ilpage;
		$categorytable = DB_PREFIX . "categories";
	
		($apihook = $ilance->api('add_category_viewcount_start')) ? eval($apihook) : false;
	
		$sql = $ilance->db->query("
			SELECT views, parentid
			FROM " . $categorytable . "
			WHERE cid = '" . intval($cid) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$total = (int) $res['views'];
			if ($mode == 'add')
			{
				$total = ($res['views'] + 1);
			}
			else if ($mode == 'subtract')
			{
				$total = ($res['views'] - 1);
				if ($total < 0)
				{
					$total = 0;
				}
			}
			$ilance->db->query("
				UPDATE " . $categorytable . "
				SET views = '" . intval($total) . "'
				WHERE cid = '" . intval($cid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			// if we have subcategories within this parent lets count the logic recursively
			if ($res['parentid'] > 0)
			{
				$this->add_category_viewcount($res['parentid'], $mode);
			}
		}
	}
    
	/**
	* Function to handle the auction counts within category logic.  This function is usually called after a new auction is added or removed from the system.
	* Additionally, this function works recursively. Update: This function also updates the `lastpost` field with a date/time for faster application reads from cache.
	*
	* @param       integer        category id
	* @param       string         mode (add or subtract)
	* @param       string         notes to determine where this function was called from exactly
	* @param       boolean        determine if we update the last post date when rebuilding category counters (default true)
	*
	* @return      nothing
	*/
	function build_category_count($cid = 0, $mode = 'add', $notes = '', $updatelastpost = true)
	{
		global $ilance, $show;
		$customquery = $lastpostquery = "";
	
		($apihook = $ilance->api('build_category_count_start')) ? eval($apihook) : false;
	
		$sql = $ilance->db->query("
			SELECT auctioncount, parentid, lastpost
			FROM " . DB_PREFIX . "categories
			WHERE cid = '" . intval($cid) . "'
			$customquery
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$total = $res['auctioncount'];
			if ($mode == 'add')
			{
				$total = ($res['auctioncount'] + 1);
				if ($updatelastpost)
				{
					$lastpostquery = ", lastpost = '" . $ilance->db->escape_string(DATETIME24H) . "'";
				}
			}
			else if ($mode == 'subtract')
			{
				$total = ($res['auctioncount'] - 1);
				$lastpostquery = "";
				if ($total <= 0)
				{
					$total = 0;
					// in this situation we should find the most recent listing date one before this one to add the date so it's not always blank!
					$lastpostquery = '';
				}
			}
			if ($res['parentid'] > 0)
			{
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "categories
					SET auctioncount = '" . intval($total) . "'$lastpostquery
					WHERE cid = '" . intval($cid) . "'
				", 0, null, __FILE__, __LINE__);
				$this->build_category_count($res['parentid'], $mode, "build_category_count(): $mode increment count cid $res[parentid]");
			}
			else
			{
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "categories
					SET auctioncount = '" . intval($total) . "'$lastpostquery
					WHERE cid = '" . intval($cid) . "'
				", 0, null, __FILE__, __LINE__);
			}
		}
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>