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
if (!defined('LOCATION') OR defined('LOCATION') != 'rfp')
{
	die('<strong>Fatal:</strong> This script cannot be parsed indirectly.');
}

$area_title = '{_compare_items}';
$page_title = SITE_NAME . ' - {_compare_items}';
$navcrumb = array("$ilpage[rfp]" => $ilcrumbs["$ilpage[compare]"]);

($apihook = $ilance->api('compare_start')) ? eval($apihook) : false;

$ilance->GPC['project_id'] = isset($ilance->GPC['project_id']) ? $ilance->GPC['project_id'] : array();
$comparecount = count($ilance->GPC['project_id']);
if (!$comparecount OR $comparecount <= 0)
{
	print_notice('{_nothing_to_do}', '{_you_did_not_select_any_listings_from_the_previous_page_please_retry}', 'javascript:history.back(-1)', '{_try_again}');
	exit();
}
if ($ilance->GPC['mode'] == 'product')
{
	// columns we'll be displaying for products
	$columns = array(
		'remove' => '_remove',
		'project_title' => '_item',
		'date_end' => '_time_left',
		'bids' => '_bids',
		'username' => '_seller',
		'currentprice' => '_price',
	);
}
else if ($ilance->GPC['mode'] == 'service')
{
	// columns we'll be displaying for services
	$columns = array(
		'remove' => '_remove',
		'project_title' => '_title',
		'date_end' => '_time_left',
		'bids' => '_bids',
		'username' => '_buyer',
		'currentprice' => '_average_bid',
	);
}
else if ($ilance->GPC['mode'] == 'experts')
{
	// columns we'll be displaying for experts
	$columns = array(
		'remove' => '_remove',
		'logo' => '_logo',
		'date_end' => '_time_left',
		'bids' => '_bids',
		'username' => '_expert',
		'currentprice' => '_price',
	);
}
$ids = array();
foreach ($ilance->GPC['project_id'] AS $projectid)
{
	$ids[] = intval($projectid);
}
$class = 'alt1';
$compare_html = '';
foreach ($columns AS $column => $phrasetext)
{
	$width = 150;
	$columns['columntitle'] = "{" . $phrasetext . "}";
	$compare_html .= ($column == 'remove')
		? '<tr class="alt3"><td width="150" class="alt3">{_select}</td>'
		: '<tr class="alt1"><td width="200" valign="top" class="alt2">' . $columns['columntitle'] . '</td>';
	$sql = $ilance->db->query("
		SELECT p.*, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, u.username, u.country
		FROM " . DB_PREFIX . "projects p 
			LEFT JOIN " . DB_PREFIX . "users u ON u.user_id = p.user_id
			WHERE project_id IN (" . implode(",", $ids) . ")
	");
	while ($item = $ilance->db->fetch_array($sql, DB_ASSOC))
	{
		if ($column == 'remove')
		{
			$compare_html .= '<td class="alt3" width="' . $width . '"><input type="checkbox" name="project_id[]" value="' . $item['project_id'] . '" id="' . $ilance->GPC['mode'] . '_' . $item['project_id'] . '" /></td>';
		}
		else if ($column == 'action')
		{
			$compare_html .= '<td class="' . $class . '" width="' . $width . '"></td>';
		}
		else if ($column == 'project_title')
		{
			// auction has bold feature?
			if ($item['bold'])
			{
				if ($ilance->GPC['mode'] == 'service')
				{
					$title = '<span class="blue"><a href="' . $ilpage['rfp'] . '?id=' . $item['project_id'] . '"><strong>' . stripslashes($item['project_title']) . '</strong></a></span>';
					$sample = '';
					$height = 0;
					$align = 'left';
					if ($ilconfig['globalauctionsettings_seourls'])
					{
						$title = construct_seo_url('serviceauction', 0, $item['project_id'], $item['project_title'], $customlink = '', $bold = 1, $searchquestion = '', $questionid = 0, $answerid = 0);
					}
				}
				else if ($ilance->GPC['mode'] == 'product')
				{
					$title = '<span class="blue"><a href="'.$ilpage['merch'] . '?id=' . $item['project_id'].'"><strong>' . stripslashes($item['project_title']) . '</strong></a></span>';
					$sample = $ilance->auction->print_item_photo($ilpage['merch'] . '?id=' . $item['project_id'], 'thumb', $item['project_id']);
					$height = 25;
					$align = 'left';
					if ($ilconfig['globalauctionsettings_seourls'])
					{
						$title = construct_seo_url('productauction', 0, $item['project_id'], $item['project_title'], $customlink = '', $bold = 1, $searchquestion = '', $questionid = 0, $answerid = 0);
					}
				}
			}
			else
			{
				if ($ilance->GPC['mode'] == 'service')
				{
					$title = '<span class="blue"><a href="' . $ilpage['rfp'] . '?id=' . $item['project_id'] . '">' . stripslashes($item['project_title']) . '</a></span>';
					$sample = '';
					$height = 0;
					$align = 'left';
					if ($ilconfig['globalauctionsettings_seourls'])
					{
						$title = construct_seo_url('serviceauction', 0, $item['project_id'], $item['project_title'], $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
					}
				}
				else if ($ilance->GPC['mode'] == 'product')
				{
					$title = '<span class="blue"><a href="' . $ilpage['merch'] . '?id=' . $item['project_id'] . '">' . stripslashes($item['project_title']) . '</a></span>';
					$height = 25;
					$align = 'left';
					if ($ilconfig['globalauctionsettings_seourls'])
					{
						$url = construct_seo_url('productauctionplain', 0, $item['project_id'], stripslashes($item['project_title']), '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
						$sample = $ilance->auction->print_item_photo($url, 'thumb', $item['project_id']);
						$title = construct_seo_url('productauction', 0, $item['project_id'], $item['project_title'], $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
					}
					else
					{
						$sample = $ilance->auction->print_item_photo($ilpage['merch'] . '?id=' . $item['project_id'], 'thumb', $item['project_id']);
					}
				}
			}
			if ($item['highlite'])
			{
				$class = 'featured_highlight';
			}
			$compare_html .= '<td class="' . $class . '" width="' . $width . '" valign="top"><div align="' . $align . '">' . $sample . '</div><div style="padding-top:' . $height . 'px"><span class="blue">' . $title . '</span></div></td>';
			$class = 'alt1';
		}
		else if ($column == 'date_end')
		{
			$compare_html .= '<td class="' . $class . '" width="' . $width . '" valign="top"><strong>' . $ilance->auction->auction_timeleft(false, $item['date_starts'], $item['mytime'], $item['starttime']) . '</strong></td>';
		}
		else if ($column == 'bids')
		{
			if ($item['bids'] == 0)
			{
				$bids = '<div class="black">0 {_bids_lower}</div>';
			}
			else
			{
				$bids = '<div class="black">' . $item['bids'] . ' {_bids_lower}</div>';
			}
			$compare_html .= '<td class="' . $class . '" width="' . $width . '" valign="top">' . $bids . '</td>';
		}
		else if ($column == 'username')
		{
			//$compare_html .= '<td class="' . $class . '" width="' . $width . '" valign="top"><span class="blue">' . print_username($item['user_id'], 'href') . '</span></td>';
			$compare_html .= '<td class="' . $class . '" width="' . $width . '" valign="top">' . print_username($item['user_id'], 'plain') . '</td>';
		}
		else if ($column == 'currentprice')
		{
			if ($ilance->GPC['mode'] == 'product')
			{
				$compare_html .= '<td class="' . $class . '" width="' . $width . '" valign="top"><strong>' . $ilance->currency->format($item['currentprice'], $item['currencyid']) . '</strong></td>';
			}
			else if ($ilance->GPC['mode'] == 'service')
			{
				$average = $ilance->bid->fetch_average_bid($item['project_id'], false, $item['bid_details'], false);
				$compare_html .= '<td class="' . $class . '" width="' . $width . '" valign="top">' . $average . '</td>';
			}
		}
		else
		{
			$compare_html .= '<td class="' . $class . '" width="' . $width . '" valign="top">' . $item["$column"] . '</td>';
		}

	}
	$compare_html .= '</tr>';
}
$comparecount++;
$hidden_input_fields = print_hidden_fields(false, array('page','project_id','rfpcmd'));
$mode = $ilance->GPC['mode'];
$returnurl = isset($ilance->GPC['returnurl']) ? urldecode($ilance->GPC['returnurl']) : $ilpage['search'];
$pprint_array = array('returnurl','mode','hidden_input_fields','comparecount','compare_html','rid');

($apihook = $ilance->api('compare_end')) ? eval($apihook) : false;

$ilance->template->fetch('main', 'search_compare.html');
$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
$ilance->template->parse_loop('main', 'columns');
$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', $pprint_array);
exit();
		
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>