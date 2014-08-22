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
* Core RSS feed functions for iLance
*
* @package      iLance\Global\Feed
* @version      4.0.0.8059
* @author       ILance
*/

/**
* Function to process and construct a valid RSS feed
*
* @param       string         feed url
* @param       boolean        show details (default true)
* @param       string         headline title css style
* @param       string         detailed style css
* @param       integer        max number of items to capture from rss feed
*
* @return      nothing
*/
function construct_feed($feed_url, $showdetail = true, $headlinestyle, $detailstyle, $max = 10) 
{
        global $show_detail, $headline_style, $detail_style, $max, $count, $insideitem, $insideimage, $code2;
        $insideitem = false;
        $insideimage = false;
        $count = 0;
        $show_detail = $showdetail;
        $headline_style = $headlinestyle;
        $detail_style = $detailstyle;
        $xml_parser = xml_parser_create();
        xml_set_element_handler($xml_parser, 'construct_feed_start_element', 'construct_feed_end_element');
        xml_set_character_data_handler($xml_parser, 'construct_feed_character_data');
        // fopen method
        $fp = @fopen($feed_url, 'r') or die('Error reading RSS data.');
        if ($fp)
        {
                while ($data = fread($fp, 4096))
                xml_parse($xml_parser, $data, feof($fp)) or die(sprintf("XML error: %s at line %d", xml_error_string(xml_get_error_code($xml_parser)), xml_get_current_line_number($xml_parser)));
                fclose($fp);
        }
        else
        {
                $code2 .= '<span class="' . $detail_style . '">Syndicated content not available</span>';
        }
        xml_parser_free($xml_parser);
}

/**
* Callback function to process rss item start tag element data for the construct_feed() function
*
* @param       object         xml parser object
* @param       string         xml tag name (ITEM or IMAGE)
* @param       string         xml tag attributes
*
* @return      nothing
*/
function construct_feed_start_element($parser, $name, $attrs)
{
        global $insideitem, $tag, $title, $description, $link, $image, $insideimage, $code2;
        if ($insideitem OR $insideimage)
        {
                $tag = $name;
        }
        if ($name == 'ITEM')
        {
                $insideitem = true;
        }
        if ($name == 'IMAGE')
        {
                $insideimage = true;
        }
}

/**
* Callback function to process rss item end tag element data for the construct_feed() function
*
* @param       object         xml parser object
* @param       string         xml tag name (URL OR ITEM)
*
* @return      nothing
*/
function construct_feed_end_element($parser, $name)
{
        global $insideitem, $tag, $title, $description, $link, $image, $insideimage, $show_detail, $headline_style, $detail_style, $count, $max, $code2;
        if ($name == 'URL')
        {
                $code2 .= '<img src="' . trim($image) . '" border="0" /><br /><br />';
                $insideimage = false;
                $image = '';
        }
        else if ($name == 'ITEM' AND $count < $max)
        {
                $count++;
                $code2 .= '<div style="padding-bottom:3px"><span class="blue" style="font-size:18px"><a href="' . $link . '" target="_blank"><strong>' . trim($title) . '</strong></a></span></div>';
                if ($show_detail)
                { 
                        $code2 .= '<div style="padding-bottom:9px;line-height:1.4em">' . trim($description) . '</div><div style="height:1px; background-color:#ccc; width:100%;margin-top:12px;margin-bottom:12px"></div>';
                }
                else
                {
                        $code2 .= '<br />';
                }
                $title = $description = $link = '';
                $insideitem = false;
        }
        else if ($count >= $max)
        {
                $title = $description = $link = '';
                $insideitem = false;
        }
}

/**
* Callback function to process rss item character data for the construct_feed() function
*
* @param       object         xml parser object
* @param       string         xml tag name
*
* @return      nothing
*/
function construct_feed_character_data($parser, $data)
{
        global $insideitem, $tag, $title, $description, $link, $image, $insideimage, $code2;
        if ($insideimage)
        {
                switch ($tag)
                {
                        case 'URL':
                        {
                                $image .= $data;
                                break;
                        }
                }
        }        
        if ($insideitem)
        {
                switch ($tag)
                {
                        case 'TITLE':
                        {
                                $title .= trim($data);
                                break;
                        }                    
                        case 'DESCRIPTION':
                        {
                                $description .= trim($data);
                                break;
                        }                    
                        case 'LINK':
                        {
                                if (!is_string($link))
                                {
                                    $link = '';
                                }
                                $link .= trim($data);
                                break;
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