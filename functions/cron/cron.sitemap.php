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

if (!isset($GLOBALS['ilance']->db))
{
        die('<strong>Warning:</strong> This script cannot be loaded indirectly.  Operation aborted.');
}
$ilance->timer->start();
global $ilpage, $ilconfig;
$show['nourlbit'] = true;
$cronlog = '';
$text = '<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="' . HTTP_SERVER . DIR_FUNCT_NAME . '/' . DIR_CSS_NAME . '/sitemap.xsl"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . LINEBREAK;
$text .= "\t<url>
\t\t<loc>" . HTTP_SERVER . "</loc>
\t\t<changefreq>always</changefreq>
\t\t<priority>1.00</priority>
\t</url>" . LINEBREAK;
$sql = $ilance->db->query("
	SELECT p.project_id, p.project_title, p.project_state
	FROM " . DB_PREFIX . "projects p
	WHERE p.visible = '1' 
		AND p.project_details != 'invite_only'
		AND p.status = 'open'
	" . (($ilconfig['globalauctionsettings_payperpost'])
		? "AND p.status != 'frozen' AND (p.enhancementfee = 0 OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '1')) AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))"
		: "") . "
", 0, null, __FILE__, __LINE__);
$num_rows1 = $ilance->db->num_rows($sql);
if ($num_rows1 > 0)
{
	$num_rows1 = 0;
	while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
	{
		if ($res['project_state'] == 'product' AND TEMPLATE_NEWUI_MODE == 'PRODUCT')
		{
			$num_rows1++;
			$url = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productauctionplain', 0, $res['project_id'], $res['project_title'], '', 0, '', 0, 0) : HTTP_SERVER . $ilpage['merch'] . '?id=' . $res['project_id'];
			$text .= "\t<url>
\t\t<loc>" . $url . "</loc>
\t\t<changefreq>always</changefreq>
\t\t<priority>1.00</priority>
\t</url>" . LINEBREAK;
		}
		else if ($res['project_state'] == 'service' AND TEMPLATE_NEWUI_MODE == 'SERVICE')
		{
			$num_rows1++;
			$url = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('serviceauctionplain', 0, $res['project_id'], $res['project_title'], '', 0, '', 0, 0) :  HTTP_SERVER . $ilpage['rfp'] . '?id=' . $res['project_id'];        
			$text .= "\t<url>
\t\t<loc>" . $url . "</loc>
\t\t<changefreq>always</changefreq>
\t\t<priority>1.00</priority>
\t</url>" . LINEBREAK;
		}
	}	
}
unset($sql, $res, $url);
$sql = $ilance->db->query("
	SELECT u.user_id
	FROM " . DB_PREFIX . "users u
	WHERE u.status = 'active'
		AND u.isadmin = '0' 
", 0, null, __FILE__, __LINE__);
$num_rows2 = $ilance->db->num_rows($sql);
if ($num_rows2 > 0)
{
	while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
	{
		$url = print_username($res['user_id'], 'url', 0, '', '');
		$text .= "\t<url>
\t\t<loc>" . $url . "</loc>
\t\t<changefreq>always</changefreq>
\t\t<priority>0.50</priority>
\t</url>" . LINEBREAK;
	}	
}
unset($sql, $res, $url);
$sql = $ilance->db->query("
	SELECT c.cid, c.cattype, c.title_" . fetch_site_slng(). " AS title
	FROM " . DB_PREFIX . "categories c
	WHERE c.visible = '1'
", 0, null, __FILE__, __LINE__);
$num_rows3 = $ilance->db->num_rows($sql);
if ($num_rows3 > 0)
{
	$num_rows3 = 0;
	while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
	{
		if ($res['cattype'] == 'product' AND TEMPLATE_NEWUI_MODE == 'PRODUCT')
		{
			$num_rows3++;
			$url = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productcatplain', $res['cid'], 0, $res['title'], '', 0, '', 0, 0) : HTTP_SERVER . $ilpage['merch'] . '?cid=' . $res['cid'];
			$text .= "\t<url>
\t\t<loc>" . $url . "</loc>
\t\t<changefreq>always</changefreq>
\t\t<priority>0.90</priority>
\t</url>" . LINEBREAK;
		}
		else if ($res['cattype'] == 'service' AND TEMPLATE_NEWUI_MODE == 'SERVICE')
		{
			$num_rows3++;
			$url = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('servicecatplain', $res['cid'], 0, $res['title'], '', 0, '', 0, 0) : HTTP_SERVER . $ilpage['rfp'] . '?cid=' . $res['cid'];
			$text .= "\t<url>
\t\t<loc>" . $url . "</loc>
\t\t<changefreq>always</changefreq>
\t\t<priority>0.90</priority>
\t</url>" . LINEBREAK;
		}
	}	
}
unset($sql, $res, $url);
$text .= "</urlset>";
if (file_put_contents(DIR_SERVER_ROOT . 'sitemap.xml', $text))
{
	$cronlog .= 'Indexed: ' . $num_rows1 . ' {_auctions}, ' . $num_rows2 . ' {_users}, ' . $num_rows3 . ' {_categories}';
}
else
{
	$cronlog .= 'Could not write to ' . DIR_SERVER_ROOT . 'sitemap.xml.  Check file permissions (CHMOD 777)';
}
$ilance->timer->stop();
log_cron_action('{_the_sitemap_tasks_were_executed} ' . $cronlog, $nextitem, $ilance->timer->get());

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>