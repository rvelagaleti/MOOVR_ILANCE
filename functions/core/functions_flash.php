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
* Flash/SWF functions for iLance
*
* @package      iLance\Global\Flash
* @version      4.0.0.8059
* @author       ILance
*/

/**
* Fetches the flash gallery xml config to be processed by the flash gallery applet
*
* @param        string       configuration type to pre-load (recentlyviewed, portfolio, favoriteseller)
*
* @return	string
*/
function fetch_flash_gallery_xml_items($mode = 'recentlyviewed', $userid = 0)
{
        global $ilance, $ilconfig, $phrase, $ilpage;
        $xml = '';
        
        switch ($mode)
        {
                case 'portfolio':
                {
                        //$sql = $ilance->db->query("");
                        break;
                }        
                case 'favoriteseller':
                {
                        $sql = $ilance->db->query("
                                SELECT p.user_id, p.project_id, p.project_title, p.currentprice, p.currencyid, p.date_starts, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, a.attachid, a.filehash
                                FROM " . DB_PREFIX . "projects p
                                LEFT JOIN " . DB_PREFIX . "attachment a ON p.project_id = a.project_id
                                WHERE p.visible = '1'
                                        AND a.visible = '1'
                                        AND p.user_id = '" . intval($userid) . "'
                                        AND p.status = 'open'
                                        AND a.attachtype = 'itemphoto'
                                        " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.enhancementfee = 0 OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '1')) AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
                                LIMIT 20
                        ", 0, null, __FILE__, __LINE__);
                        break;
                }
                case 'recentlyviewed':
                {
                        if (!empty($_COOKIE[COOKIE_PREFIX . 'productauctions']))
                        {
                                $productsarr = explode('|', $_COOKIE[COOKIE_PREFIX . 'productauctions']);
                                for ($i = 0; $i < count($productsarr); $i++)
                                {
                                        if (isset($pcookiesql))
                                        {
                                                if (count($productsarr) == $i)
                                                {
                                                        $pcookiesql .= " OR p.project_id = '" . intval($productsarr[$i]) . "'  ";
                                                }
                                                else
                                                {
                                                        $pcookiesql .= " OR p.project_id = '" . intval($productsarr[$i]) . "' ";
                                                }
                                        }
                                        else
                                        {
                                                if (count($productsarr) == 1)
                                                {
                                                        $pcookiesql = " AND p.project_id = '" . intval($productsarr[$i]) . "' ";
                                                }
                                                else
                                                {
                                                        $pcookiesql = " AND p.project_id = '" . intval($productsarr[$i]) . "' ";
                                                }
                                        }
                                }
                                
                                $sql = $ilance->db->query("
                                        SELECT p.user_id, p.project_id, p.project_title, p.currentprice, p.currencyid, p.date_starts, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, a.attachid, a.filehash
                                        FROM " . DB_PREFIX . "projects p
                                        LEFT JOIN " . DB_PREFIX . "attachment a ON p.project_id = a.project_id
                                        WHERE p.visible = '1'
                                                AND a.visible = '1'
                                                $pcookiesql
                                                AND p.status = 'open'
                                                AND a.attachtype = 'itemphoto'
                                                " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.enhancementfee = 0 OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '1')) AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
                                        LIMIT 20
                                ", 0, null, __FILE__, __LINE__);
                        }
                        break;
                }
        }
        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
        {
$xml .= '<item>
<thumb>' . (($res['attachid'] > 0) ? $ilpage['attachment'] . '?cmd=thumb&amp;id=' . $res['filehash'] . '&amp;subcmd=portfolio' : $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif') . '</thumb>
<image>' . (($res['attachid'] > 0) ? $ilpage['attachment'] . '?id=' . $res['filehash'] . '&amp;subcmd=portfolio' : $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif') . '</image>
<price>' . $ilance->currency->format($res['currentprice'], $res['currencyid']) . '</price>
<title><![CDATA[<a href="' . HTTP_SERVER . $ilpage['merch'] . '?id=' . $res['project_id'] . '">' . stripslashes($res['project_title']) . '</a>]]></title>
<product_url><![CDATA[' . HTTP_SERVER . $ilpage['merch'] . '?id=' . $res['project_id'] . ']]></product_url>
<watch_url><![CDATA[' . HTTP_SERVER . $ilpage['watchlist'] . '?id=' . $res['project_id'] . '&action=watch]]></watch_url>
<stop_watch_url><![CDATA[' . HTTP_SERVER . $ilpage['watchlist'] . '?id=' . $res['project_id'] . '&action=unwatch]]></stop_watch_url>
<time_left><![CDATA[' . $ilance->auction->auction_timeleft(false, $res['date_starts'], $res['mytime'], $res['starttime']) . ']]></time_left>
<watch_status>1</watch_status>
</item>
';
        }
        
        return $xml;
}

/**
* Produces the flash based applet picture gallery
*
* @param        string       configuration type to preload
*
* @return	string
*/
function print_flash_gallery($config = 'recentlyviewed', $userid = 0)
{
        global $ilance, $phrase, $ilconfig;
        
        return '';
        $uniqueid = rand(1, 9999);
        
        $html = '
<div id="galleryapplet-' . $uniqueid . '"></div>
<script type="text/javascript">
<!--
var fo = new FlashObject("' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . DIR_FUNCT_NAME . '/' . DIR_SWF_NAME . '/gallery.swf", "galleryapplet-' . $uniqueid . '", "600", "200", "8,0,0,0", "#ffffff");
fo.addParam("quality", "high");
fo.addParam("allowScriptAccess", "sameDomain");
fo.addParam("swLiveConnect", "true");
fo.addParam("flashvars", "config_file=' . urlencode(AJAXURL . '?do=flashgallery&config=' . $config . '&userid=' . $userid . '&s=' . session_id() . '&token=' . TOKEN) . '");
fo.addParam("menu", "false");
fo.write("galleryapplet-' . $uniqueid . '");
//-->
</script>';
        
        return $html;
}

/**
* Fetches the flash gallery xml config to be processed by the flash gallery applet
*
* @param        string       configuration type to pre-load (recentlyviewed, portfolio, favoriteseller)
* @param        string       start date (used for some flash stats)
* @param        string       end date (used for some flash stats)
* @param        string       custom argument 1 for future development
* @param        string       custom argument 2 for future development
* @param        string       custom argument 3 for future development
* @return	string       Formatted XML for Flash Applet
*/
function fetch_flash_stats_xml_items($mode = 'connections', $startdate = '', $enddate = '', $custom1 = '', $custom2 = '', $custom3 = '')
{
        global $ilance, $show, $ilconfig;
        $xml = '';
        switch ($mode)
        {
                case 'referrals':
                {
                        $xml = '
<values_count type="number">2</values_count>

<v1_bg_color type="hex">0x006699</v1_bg_color>
<v1_alpha type="number">30</v1_alpha>
<v1_line_thickness type="number">1</v1_line_thickness>
<v1_line_color type="hex">0x000000</v1_line_color>
<v1_value_pointer_color type="hex">0x000000</v1_value_pointer_color>
<v1_value_pointer_alpha type="number">30</v1_value_pointer_alpha>
<v2_bg_color type="hex">0xFF9966</v2_bg_color>
<v2_alpha type="number">30</v2_alpha>
<v2_line_thickness type="number">1</v2_line_thickness>
<v2_line_color type="hex">0x000000</v2_line_color>
<v2_value_pointer_color type="hex">0x000000</v2_value_pointer_color>
<v2_value_pointer_alpha type="number">30</v2_value_pointer_alpha>
</config>
<items start_date="' . date('Y') . '-' . date('m') . '-1">
        <item value1="10000" value2="13321" pin="" />
</items>
<items start_date="' . date('Y') . '-' . date('m') . '-1">
        <item value1="50000" value2="23321" pin="" />
</items>
<items start_date="' . date('Y') . '-' . date('m') . '-1">
        <item value1="60000" value2="23321" pin="" />
</items>
<items start_date="' . date('Y') . '-' . date('m') . '-1">
        <item value1="80000" value2="23321" pin="" />
</items>
<items start_date="' . date('Y') . '-' . date('m') . '-1">
        <item value1="90000" value2="23321" pin="" />
</items>
</chart>';
                        break;
                }
                case 'connections':
                {
                        $xml = '
<values_count type="number">2</values_count>

<v1_bg_color type="hex">0x999999</v1_bg_color>
<v1_alpha type="number">30</v1_alpha>
<v1_line_thickness type="number">1</v1_line_thickness>
<v1_line_color type="hex">0x000000</v1_line_color>
<v1_value_pointer_color type="hex">0x000000</v1_value_pointer_color>
<v1_value_pointer_alpha type="number">30</v1_value_pointer_alpha>

<v2_bg_color type="hex">0xFF9900</v2_bg_color>
<v2_alpha type="number">30</v2_alpha>
<v2_line_thickness type="number">1</v2_line_thickness>
<v2_line_color type="hex">0x000000</v2_line_color>
<v2_value_pointer_color type="hex">0x000000</v2_value_pointer_color>
<v2_value_pointer_alpha type="number">30</v2_value_pointer_alpha>
</config>
<items start_date="' . date('Y') . '-' . date('m') . '-' . date('d') . '">
<item value1="11223" value2="23321" pin="" />
<item value1="13223" value2="41421" pin="test" />
</items>
<items start_date="' . date('Y') . '-' . date('m') . '-' . date('d') . '">
<item value1="11223" value2="23321" pin="" />
<item value1="13223" value2="41421" pin="test" />
</items>
<items start_date="' . date('Y') . '-' . date('m') . '-' . date('d') . '">
<item value1="51223" value2="63321" pin="" />
<item value1="1323" value2="41321" pin="test" />
</items>
</chart>';
                        $guestcount = $membercount = $adminscount = $spidercount = 0;
                        
                        $guestscount = $ilance->db->query_fetch("SELECT COUNT(*) AS count FROM " . DB_PREFIX . "sessions WHERE userid = '0' AND isrobot = '0'");
                        $membercount = $ilance->db->query_fetch("SELECT COUNT(*) AS count FROM " . DB_PREFIX . "sessions WHERE userid > 0 AND isrobot = '0' AND isadmin = '0'");
                        $adminscount = $ilance->db->query_fetch("SELECT COUNT(*) AS count FROM " . DB_PREFIX . "sessions WHERE userid > 0 AND isadmin > 0");
                        $spidercount = $ilance->db->query_fetch("SELECT COUNT(*) AS count FROM " . DB_PREFIX . "sessions WHERE userid = '0' AND isrobot = '1'");
                        
                        $xml .= '<item value="' . (int)$guestscount['count'] . '" label="Guests" />' . "\n";
                        $xml .= '<item value="' . (int)$membercount['count'] . '" label="Members" />' . "\n";
                        $xml .= '<item value="' . (int)$adminscount['count'] . '" label="Admins" />' . "\n";
                        $xml .= '<item value="' . (int)$spidercount['count'] . '" label="Crawlers" />';
                        
                        break;
                }
                case 'totalusers':
                {
                        $xml = '
<values_count type="number">2</values_count>

<v1_bg_color type="hex">0x999999</v1_bg_color>
<v1_alpha type="number">30</v1_alpha>
<v1_line_thickness type="number">1</v1_line_thickness>
<v1_line_color type="hex">0x000000</v1_line_color>
<v1_value_pointer_color type="hex">0x000000</v1_value_pointer_color>
<v1_value_pointer_alpha type="number">30</v1_value_pointer_alpha>

<v2_bg_color type="hex">0xFF9900</v2_bg_color>
<v2_alpha type="number">30</v2_alpha>
<v2_line_thickness type="number">1</v2_line_thickness>
<v2_line_color type="hex">0x000000</v2_line_color>
<v2_value_pointer_color type="hex">0x000000</v2_value_pointer_color>
<v2_value_pointer_alpha type="number">30</v2_value_pointer_alpha>
</config>
<items start_date="' . date('Y') . '-' . date('m') . '-' . date('d') . '">
<item value1="11223" value2="23321" pin="" />
<item value1="13223" value2="41421" pin="test" />
</items>
<items start_date="' . date('Y') . '-' . date('m') . '-' . date('d') . '">
<item value1="11223" value2="23321" pin="" />
<item value1="13223" value2="41421" pin="test" />
</items>
<items start_date="' . date('Y') . '-' . date('m') . '-' . date('d') . '">
<item value1="51223" value2="63321" pin="" />
<item value1="1323" value2="41321" pin="test" />
</items>
</chart>';
                        
                        //print '<item value1="" value2=""  value3="" value4="" pin="" />\n";
                        
                        break;
                }
        }
        
        ($apihook = $ilance->api('fetch_flash_stats_xml_items_end')) ? eval($apihook) : false;
        
        return $xml;
}

/**
* Produces the flash based applet stats component
*
* @param        string       configuration type to preload
* @param        string       specific flash applet to load
* @param        string       start date (used for some flash stats)
* @param        string       end date (used for some flash stats)
* @param        string       custom 1 argument for future development
* @param        string       custom 2 argument for future development
* @param        string       custom 3 argument for future development
* @param        string       chart width (default 100%)
* @param        string       chart height (default 260)
*
* @return	string       Returns SWF object ready for printing in HTML template
*/
function print_flash_stats($config = 'connections', $applet = 'stats', $startdate = '', $enddate = '', $custom1 = '', $custom2 = '', $custom3 = '', $chartwidth = '100%', $chartheight = '260')
{
        $uniqueid = rand(1, 9999);
        $html = '<div id="' . $applet . 'applet-' . $uniqueid . '"></div>
<script type="text/javascript">
<!--
var fo = new FlashObject("' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . DIR_FUNCT_NAME . '/' . DIR_SWF_NAME . '/' . $applet . '.swf", "' . $applet . 'applet-' . $uniqueid . '", "' . $chartwidth . '", "' . $chartheight . '", "9", "#ffffff");
fo.addParam("quality", "high");
fo.addParam("allowScriptAccess", "sameDomain");
fo.addParam("swLiveConnect", "true");
fo.addParam("flashvars", "config_file=' . urlencode(AJAXURL . '?do=' . $applet . '&config=' . $config . '&startdate=' . $startdate . '&enddate=' . $enddate . '&custom1=' . $custom1 . '&custom2=' . $custom2 . '&custom3=' . $custom3 . '&s=' . session_id() . '&token=' . TOKEN) . '");
fo.addParam("menu", "false");
fo.write("' . $applet . 'applet-' . $uniqueid . '");
//-->
</script>';
        return $html;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>