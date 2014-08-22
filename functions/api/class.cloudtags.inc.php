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
* Class for handling all functions related to keyword cloud generation in ILance
*
* @package      iLance\CloudTags
* @version      4.0.0.8059
* @author       ILance
*/
class cloudtags
{
    /**
    * Function to compile and process ascending sorting based on a keywords array
    *
    * @param       array          first tag array
    * @param       array          second tag array
    *
    * @return      string         tag count
    */
    function cloud_tags_asort($tag1, $tag2)
    {
	if ($tag1['tag_count'] == $tag2['tag_count'])
	{
	    return 0;
	}
	return ($tag1['tag_count'] < $tag2['tag_count']) ? -1 : 1;
    }

    /**
    * Function to compile and process alpha sorting based on a keywords array
    *
    * @param       array          first tag array
    * @param       array          second tag array
    *
    * @return      string         Returns HTML formatted top search keywords cloud
    */
    function cloud_tags_alphasort($tag1, $tag2)
    {
	if ($tag1['tag_name'] == $tag2['tag_name'])
	{
	    return 0;
	}
	return ($tag1['tag_name'] < $tag2['tag_name']) ? -1 : 1;
    }

    /**
    * Function to compile and process an array with top keywords for presentation
    *
    * @param       array          keyword tags array
    *
    * @return      string         Returns HTML formatted top search keywords cloud
    */
    function process_cloud_tags($tags)
    {
	$tag_sizes = 7;
	usort($tags, array($this, 'cloud_tags_asort'));
	if (count($tags) > 0)
	{
	    $total_tags = count($tags);
	    $min_tags = $total_tags / $tag_sizes;
	    $bucket_count = 1;
	    $bucket_items = $tags_set = 0;
	    foreach ($tags AS $key => $tag)
	    {
		$tag_count = $tag['tag_count'];
		if (($bucket_items >= $min_tags) and $last_count != $tag_count AND $bucket_count < $tag_sizes)
		{
		    $bucket_count++;
		    $bucket_items = 0;
		    $remaining_tags = $total_tags - $tags_set;
		    $min_tags = $remaining_tags / $bucket_count;
		}
		$tags[$key]['tag_class'] = 'tag' . $bucket_count;
		$bucket_items++;
		$tags_set++;
		$last_count = $tag_count;
	    }
	    usort($tags, array($this, 'cloud_tags_alphasort'));
	}
	return $tags;
    }

    /**
    * Function to print a HTML formatted top search keywords tag cloud with the most searched in various font sizes and attributes
    *
    * @param       integer        category id
    * @param       string         category mode (service or product)
    *
    * @return      string         Returns HTML formatted top search keywords cloud
    */
    function print_tag_cloud($cid = 0, $mode = '')
    {
	global $ilance, $ilconfig, $ilpage, $show;
	if ($ilconfig['enablepopulartags'] == false OR isset($ilconfig['popular_tab']) AND $ilconfig['popular_tab'] == false)
	{
	    return;
	}
	$extra = '';
	if ($cid > 0 AND !empty($mode))
	{
	    $childrenids = $ilance->categories->fetch_children_ids($cid, $mode);
	    $subcategorylist = (!empty($childrenids)) ? $cid . ',' . $childrenids : $cid . ',';
	    $extra = "AND (FIND_IN_SET(cid, '$subcategorylist'))";
	    unset($childrenids, $subcategorylist);
	}
	$show['tagcloud'] = false;
	$badwords = explode(', ', $ilconfig['globalfilters_vulgarpostfilterlist']);
	$tags = array ();
	$counter = 0;
	$html = '';
	$sql = $ilance->db->query("
                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "keyword AS tag_name, count AS tag_count, searchmode, cid
                FROM " . DB_PREFIX . "search
                WHERE count > " . $ilconfig['populartagcount'] . "
			AND visible = 1
			$extra
		GROUP BY tag_name
                ORDER BY count
		LIMIT " . $ilconfig['populartaglimit'] . "
        ", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
	    while ($res = $ilance->db->fetch_assoc($sql))
	    {
		$tags[] = $res;
	    }
	}
	$newtags = $this->process_cloud_tags($tags);
	if (!empty($newtags))
	{
	    $show['tagcloud'] = true;
	    foreach ($newtags AS $array)
	    {
		$counter++;
		if ($counter < 30)
		{
		    if (!in_array(stripslashes(mb_strtolower($array['tag_name'])), $badwords))
		    {
			$html .= '<span title="' . handle_input_keywords(stripslashes($array['tag_name'])) . '"><a href="' . $ilpage['search'] . '?mode=' . urlencode($array['searchmode']) . '&amp;q=' . urlencode(stripslashes(html_entity_decode($array['tag_name']))) . '&amp;cid=' . intval($array['cid']) . '&amp;nkw=1" class="' . $array['tag_class'] . '" rel="nofollow">' . shorten(print_string_wrap(handle_input_keywords(stripslashes($array['tag_name'])), 15), $ilconfig['globalfilters_auctiontitlecutoff']) . '</a></span> &nbsp;&nbsp; ';
		    }
		}
	    }
	    $show['tagcloud'] = 1;
	}
	else
	{
	    $show['tagcloud'] = 0;
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