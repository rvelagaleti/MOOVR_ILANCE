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
* Core pagnation functions for iLance
*
* @package      iLance\Global\Pagnation
* @version      4.0.0.8059
* @author       ILance
*/

/**
* Function to show page results within the pagnation function like showing results [first] to [last] of [total] pages emulation
*
* @param	integer	     page number we are currently viewing
* @param        string       per page limit
* @param	string       total pages
*
* @return	array        Returns an array with [first] and [last] page number results
*/
function construct_start_end_array($pagenum = 0, $perpage = 0, $total = 0)
{
        $first = $perpage * ($pagenum - 1);
        $last = $first + $perpage;
        if ($last > $total)
        {
                $last = $total;
        }
        $first++;
        return array('first' => number_format($first), 'last' => number_format($last));
}

/**
* Function for printing the prev and next links to allow users to navigate through result listings.
*
* @param       integer        total number of rows
* @param       integer        row limit (per page)
* @param       integer        current page number
* @param       integer        (depreciated)
* @param       string         current page url
* @param       string         custom &page= name
* @param       boolean        include a question mark ? after the $scriptpage url?
*
* @return      string         HTML representation of the page navigator
*/
function print_pagnation($number = 0, $rowlimit = 10, $page = 0, $counter = 0, $scriptpage = '', $custompagename = 'page', $questionmarkfirst = false)
{
        global $ilance, $phrase, $ilconfig;
        $html = '';
        if (empty($custompagename))
        {
                $custompagename = 'page';
        }
	$startend = construct_start_end_array($page, $rowlimit, $number);
        $totalpages = ceil(($number / $rowlimit));
        if ($totalpages == 0)
        {
                $totalpages = 1;
        }
	$html .= '<div style="margin-top:6px"><table cellpadding="4" cellspacing="0" border="0" width="100%" align="center" dir="' . $ilconfig['template_textdirection'] . '"><tr><td style="padding:4px"><span style="float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '" title="' . $ilance->language->construct_phrase('{_showing_results_x_to_x_of_x}', array('' . $startend['first'] . '', '' . $startend['last'] . '', '' . number_format($number) . '')) . '"><strong>{_page} ' . number_format($page) . '</strong> {_of} <strong>' . number_format($totalpages) . '</strong></span></td><td width="1" style="padding-left:12px"></td>';
	if ($page > 1)
	{
		$html .= '<td width="1" align="' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '" style="width:1px"><a href="' . $scriptpage . '&amp;' . $custompagename . '=1&amp;pp=' . $rowlimit . '" title="{_goto_first_page}" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pagenav_' . (($ilconfig['template_textalignment'] == 'left') ? 'left_first.gif' : 'right_last.gif') . '" border="0" alt="{_goto_first_page}" /></a></td><td width="1" align="' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '" style="width:1px"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . ($page - 1) . '&amp;pp=' . $rowlimit . '" title="{_prev_page}: ' . ($page - 1) . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pagenav_' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '.gif" border="0" alt="{_prev_page}: ' . ($page - 1) . '" /></a></td><td width="1" style="padding-right:12px"></td>';
	}
	// page number 10 and higher
	if ($page > 10)
	{
		$inc = floor(($page - 3) / 3);
		for ($i = 1; $i < ($page - 3); $i += $inc)
		{
			$startend = construct_start_end_array($i, $rowlimit, $number);
			$html .= '<td class="alt1" align="center" width="1" style="width:1px"><span class="blue"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit . '" title="' . $ilance->language->construct_phrase('{_show_results_x_to_x_of_x}', array($startend['first'], $startend['last'], number_format($number))) . '" rel="nofollow">' . $i . '</a></span></td>';
		}
	}
	// page number 1 through 9
	else
	{
		for ($i = 1; $i < $page - 3; $i++)
		{
			$startend = construct_start_end_array($i, $rowlimit, $number);
			$html .= '<td class="alt1" align="center" width="1" style="width:1px"><span class="blue"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit . '" title="' . $ilance->language->construct_phrase('{_show_results_x_to_x_of_x}', array($startend['first'], $startend['last'], number_format($number))) . '" rel="nofollow">' . $i . '</a></span></td>';
		}
	}
	// 3 links before current selected page
	for ($i = $page - 3; $i < $page; $i++)
	{
		if ($i > 0)
		{
			$startend = construct_start_end_array($i, $rowlimit, $number);
			$html .= '<td class="alt1" align="center" width="1" style="width:1px"><span class="blue"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit . '" title="' . $ilance->language->construct_phrase('{_show_results_x_to_x_of_x}', array($startend['first'], $startend['last'], number_format($number))) . '" rel="nofollow">' . $i . '</a></span></td>';
		}
	}
	$html .= '<td class="alt1" align="center" width="1" style="width:1px"><strong>' . $page . '</strong></td>';
	for ($i = $page + 1; $i <= $page + 3; $i++)
	{
		if ($i > 0 AND $i <= $totalpages)
		{
			$startend = construct_start_end_array($i, $rowlimit, $number);
			$html .= '<td class="alt1" align="center" width="1" style="width:1px"><span class="blue"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit . '" title="' . $ilance->language->construct_phrase('{_show_results_x_to_x_of_x}', array($startend['first'], $startend['last'], number_format($number))) . '" rel="nofollow">' . $i . '</a></span></td>';
		}
	}
	if (($totalpages - $page) > 10)
	{
		$temp = '';
		$inc = floor(($totalpages - ($page + 3)) / 3);
		for ($i = $totalpages; $i > $page + 3; $i -= $inc)
		{
			$startend = construct_start_end_array($i, $rowlimit, $number);
			$temp = '<td class="alt1" align="center" width="1" style="width:1px"><span class="blue"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit . '" title="' . $ilance->language->construct_phrase('{_show_results_x_to_x_of_x}', array($startend['first'], $startend['last'], number_format($number))) . '" rel="nofollow">' . $i . '</a></span></td>';
		}
		$html .= $temp;
	}
	else if ($totalpages - $page > 3)
	{
		for ($i = $page + 4; $i <= $totalpages; $i++)
		{
			$startend = construct_start_end_array($i, $rowlimit, $number);
			$html .= '<td class="alt1" align="center" width="1" style="width:1px"><span class="blue"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit . '" title="' . $ilance->language->construct_phrase('{_show_results_x_to_x_of_x}', array($startend['first'], $startend['last'], number_format($number))) . '" rel="nofollow">' . $i . '</a></span></td>';
		}
	}
	if ($page < $totalpages)
	{
		$html .= '<td class="" align="right" width="1" style="padding-left:12px"></td><td class="" align="right" width="1" style="width:1px"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . ($page + 1) . '&amp;pp=' . $rowlimit . '" title="' . '{_next_page}' . ': ' . number_format(($page + 1)) . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pagenav_' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . '.gif" border="0" alt="' . '{_next_page}' . ': ' . number_format(($page + 1)) . '" /></a></td><td class="" align="' . (($ilconfig['template_textalignment'] == 'left') ? 'right' : 'left') . '" width="1" style="width:1px"><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . ($totalpages) . '&amp;pp=' . $rowlimit . '" title="' . '{_goto_last_page}' . ': ' . number_format($totalpages) . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pagenav_' . (($ilconfig['template_textalignment'] == 'left') ? 'right_last.gif' : 'left_first.gif') . '" border="0" alt="' . '{_goto_last_page}' . ': ' . number_format($totalpages) . '" /></a></td>';
	}
	$html .= '</tr></table></div>';
        return $html;
}

/**
* Function for printing the prev and next links to allow users to navigate through result listings for iLance 4.1+
*
* @param       integer        total number of rows
* @param       integer        row limit (per page)
* @param       integer        current page number
* @param       integer        (depreciated)
* @param       string         current page url
* @param       string         custom &page= name
* @param       boolean        include a question mark ? after the $scriptpage url?
*
* @return      string         HTML representation of the page navigator
*/
function print_pagnation_v4($number = 0, $rowlimit = 10, $page = 0, $counter = 0, $scriptpage = '', $custompagename = 'page', $questionmarkfirst = false, $hideperpageinput = false, $hidegotopage = false)
{
        global $ilance, $phrase, $ilconfig, $php_self, $hiddenfields_leftnav, $selected;
        $html = $html2 = '';
        if (empty($custompagename))
        {
                $custompagename = 'page';
        }
	$startend = construct_start_end_array($page, $rowlimit, $number);
        $totalpages = ceil(($number / $rowlimit));
        if ($totalpages == 0)
        {
                $totalpages = 1;
        }
	$html .= '<!-- start product-control -->
<div class="product-control">
	<div class="txt-left" title="' . $ilance->language->construct_phrase('{_showing_results_x_to_x_of_x}', array('' . $startend['first'] . '', '' . $startend['last'] . '', '' . number_format($number) . '')) . '">{_page} <strong>' . number_format($page) . '</strong> {_of} ' . number_format($totalpages) . '</div>';
	
	if ($hideperpageinput == false)
	{
		$html .= '<!-- start view-counter -->
<div class="view-counter">
	<span>{_per_page}:</span>
	<ul>';
		$html .= (($selected['list'] == 'gallery' OR isset($ilance->GPC['list']) AND $ilance->GPC['list'] == 'gallery')
			// 12, 24, 48, 96
			? '<li' . ((isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] == '12' OR !isset($ilance->GPC['pp'])) ? ' class="active"' : '') . '>' . ((isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] == '12') ? '12' : '<a href="' . $php_self . '&amp;pp=12&amp;' . $custompagename . '=1">12</a>') . '</li>
<li' . ((isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] == '24') ? ' class="active"' : '') . '>' . ((isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] == '24') ? '24' : '<a href="' . $php_self . '&amp;pp=24&amp;' . $custompagename . '=1">24</a>') . '</li>
<li' . ((isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] == '48') ? ' class="active"' : '') . '>' . ((isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] == '48') ? '48' : '<a href="' . $php_self . '&amp;pp=48&amp;' . $custompagename . '=1">48</a>') . '</li>
<li' . ((isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] == '96') ? ' class="active"' : '') . '>' . ((isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] == '96') ? '96' : '<a href="' . $php_self . '&amp;pp=96&amp;' . $custompagename . '=1">96</a>') . '</li>'
			// 10, 25, 50, 100
			: '<li' . ((isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] == '10' OR !isset($ilance->GPC['pp'])) ? ' class="active"' : '') . '>' . ((isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] == '10') ? '10' : '<a href="' . $php_self . '&amp;pp=10&amp;' . $custompagename . '=1">10</a>') . '</li>
<li' . ((isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] == '25') ? ' class="active"' : '') . '>' . ((isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] == '25') ? '25' : '<a href="' . $php_self . '&amp;pp=25&amp;' . $custompagename . '=1">25</a>') . '</li>
<li' . ((isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] == '50') ? ' class="active"' : '') . '>' . ((isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] == '50') ? '50' : '<a href="' . $php_self . '&amp;pp=50&amp;' . $custompagename . '=1">50</a>') . '</li>
<li' . ((isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] == '100') ? ' class="active"' : '') . '>' . ((isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] == '100') ? '100' : '<a href="' . $php_self . '&amp;pp=100&amp;' . $custompagename . '=1">100</a>') . '</li>');
		$html .= '</ul>
</div><!-- end view-counter -->';
	}
	$html .= '<!-- start control-box -->
	<div class="control-box">
	
		<!-- start pagination -->
		<div class="pagination">';
	if ($page == 1)
	{
		if ($totalpages == 1)
		{
			$html .= '<a href="javascript:;" title="" rel="nofollow" class="prev first">{_prev}</a><a href="javascript:;" title="" rel="nofollow" class="next last">{_next}</a><ul>';
			$html2 = '<!-- start mini page navigator -->
<div class="numb">
    <a href="javascript:;" title="{_prev}" class="prev first">{_prev}</a>
    <span>{_page} <strong>' . number_format($page) . '</strong> {_of} ' . number_format($totalpages) . '</span> 
    <a href="javascript:;" title="{_next}" class="next last">{_next}</a>
</div><!-- end mini page navigator -->';
		}
		else
		{
			$html .= '<a href="javascript:;" title="" rel="nofollow" class="prev first">{_prev}</a><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . ($page + 1) . '&amp;pp=' . $rowlimit . '" title="' . '{_next_page}' . ': ' . number_format(($page + 1)) . '" rel="nofollow" class="next">{_next}</a><ul>';
			$html2 = '<!-- start mini page navigator -->
<div class="numb">
    <a href="javascript:;" title="{_prev}" class="prev first">{_prev}</a>
    <span>{_page} <strong>' . number_format($page) . '</strong> {_of} ' . number_format($totalpages) . '</span> 
    <a href="' . $scriptpage . '&amp;' . $custompagename . '=' . ($page + 1) . '&amp;pp=' . $rowlimit . '" title="' . '{_next_page}' . ': ' . number_format(($page + 1)) . '" rel="nofollow" class="next">{_next}</a>
</div><!-- end mini page navigator -->';
		}
	}
	else if ($page > 1)
	{
		if ($page == $totalpages)
		{
			$html .= '<a href="' . $scriptpage . '&amp;' . $custompagename . '=' . ($page - 1) . '&amp;pp=' . $rowlimit . '" title="{_prev_page}: ' . ($page - 1) . '" rel="nofollow" class="prev">{_prev}</a><a href="javascript:;" title="" rel="nofollow" class="next last">{_next}</a><ul>';
			$html2 = '<!-- start mini page navigator -->
<div class="numb">
    <a href="' . $scriptpage . '&amp;' . $custompagename . '=' . ($page - 1) . '&amp;pp=' . $rowlimit . '" title="{_prev_page}: ' . ($page - 1) . '" rel="nofollow" class="prev">{_prev}</a>
    <span>{_page} <strong>' . number_format($page) . '</strong> {_of} ' . number_format($totalpages) . '</span> 
    <a href="javascript:;" title="" rel="nofollow" class="next last">{_next}</a>
</div><!-- end mini page navigator -->';
		}
		else
		{
			$html .= '<a href="' . $scriptpage . '&amp;' . $custompagename . '=' . ($page - 1) . '&amp;pp=' . $rowlimit . '" title="{_prev_page}: ' . ($page - 1) . '" rel="nofollow" class="prev">{_prev}</a><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . ($page + 1) . '&amp;pp=' . $rowlimit . '" title="' . '{_next_page}' . ': ' . number_format(($page + 1)) . '" rel="nofollow" class="next">{_next}</a><ul>';
			$html2 = '<!-- start mini page navigator -->
<div class="numb">
    <a href="' . $scriptpage . '&amp;' . $custompagename . '=' . ($page - 1) . '&amp;pp=' . $rowlimit . '" title="{_prev_page}: ' . ($page - 1) . '" rel="nofollow" class="prev">{_prev}</a>
    <span>{_page} <strong>' . number_format($page) . '</strong> {_of} ' . number_format($totalpages) . '</span> 
    <a href="' . $scriptpage . '&amp;' . $custompagename . '=' . ($page + 1) . '&amp;pp=' . $rowlimit . '" title="' . '{_next_page}' . ': ' . number_format(($page + 1)) . '" rel="nofollow" class="next">{_next}</a>
</div><!-- end mini page navigator -->';
		}
	}
	// page number 10 and higher
	if ($page > 10)
	{
		$inc = floor(($page - 3) / 3);
		for ($i = 1; $i < ($page - 3); $i += $inc)
		{
			$startend = construct_start_end_array($i, $rowlimit, $number);
			$html .= '<li><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit . '" title="' . $ilance->language->construct_phrase('{_show_results_x_to_x_of_x}', array($startend['first'], $startend['last'], number_format($number))) . '" rel="nofollow">' . $i . '</a></li>';
		}
	}
	// page number 1 through 9
	else
	{
		for ($i = 1; $i < $page - 3; $i++)
		{
			$startend = construct_start_end_array($i, $rowlimit, $number);
			$html .= '<li><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit . '" title="' . $ilance->language->construct_phrase('{_show_results_x_to_x_of_x}', array($startend['first'], $startend['last'], number_format($number))) . '" rel="nofollow">' . $i . '</a></li>';
		}
	}
	// 3 links before current selected page
	for ($i = $page - 3; $i < $page; $i++)
	{
		if ($i > 0)
		{
			$startend = construct_start_end_array($i, $rowlimit, $number);
			$html .= '<li><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit . '" title="' . $ilance->language->construct_phrase('{_show_results_x_to_x_of_x}', array($startend['first'], $startend['last'], number_format($number))) . '" rel="nofollow">' . $i . '</a></li>';
		}
	}
	//$html .= '<td class="alt1" align="center" width="1" style="width:1px"><strong>' . $page . '</strong></td>';
	$html .= '<li class="active"><a href="javascript:;">' . $page . '</a></li>';
	for ($i = $page + 1; $i <= $page + 3; $i++)
	{
		if ($i > 0 AND $i <= $totalpages)
		{
			$startend = construct_start_end_array($i, $rowlimit, $number);
			$html .= '<li><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit . '" title="' . $ilance->language->construct_phrase('{_show_results_x_to_x_of_x}', array($startend['first'], $startend['last'], number_format($number))) . '" rel="nofollow">' . $i . '</a></li>';
		}
	}
	if (($totalpages - $page) > 10)
	{
		$temp = '';
		$inc = floor(($totalpages - ($page + 3)) / 3);
		for ($i = $totalpages; $i > $page + 3; $i -= $inc)
		{
			$startend = construct_start_end_array($i, $rowlimit, $number);
			$temp = '<li><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit . '" title="' . $ilance->language->construct_phrase('{_show_results_x_to_x_of_x}', array($startend['first'], $startend['last'], number_format($number))) . '" rel="nofollow">' . $i . '</a></li>';
		}
		$html .= $temp;
	}
	else if ($totalpages - $page > 3)
	{
		for ($i = $page + 4; $i <= $totalpages; $i++)
		{
			$startend = construct_start_end_array($i, $rowlimit, $number);
			$html .= '<li><a href="' . $scriptpage . '&amp;' . $custompagename . '=' . $i . '&amp;pp=' . $rowlimit . '" title="' . $ilance->language->construct_phrase('{_show_results_x_to_x_of_x}', array($startend['first'], $startend['last'], number_format($number))) . '" rel="nofollow">' . $i . '</a></li>';
		}
	}
	$html .= '</ul></div><!-- end pagination -->';
	if ($hidegotopage == false)
	{
		$html .= '<!-- start jump page -->
<div class="jump-page">
	<form action="' . $scriptpage . '" method="get">
	' . $hiddenfields_leftnav . '
	<label>{_go_to_page}:</label>
	<input name="page" type="text" class="input" autocomplete="off" />
	<input type="submit" value="{_go}" class="button" />
	</form>
</div><!-- end jump page -->';
	}
	$html .= '
	</div><!-- end control-box -->
</div><!-- end product-control -->';
        return array($html, $html2);
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>