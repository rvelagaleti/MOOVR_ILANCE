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
* BBCode class to perform the majority of BBcode functions within ILance
*
* @package      iLance\BBCode
* @version      4.0.0.8059
* @author       ILance
*/
class bbcode
{
        /**
        * BBCode Constructor.
        */
        function bbcode()
        {            
        }
        
        /**
        * Function to convert a string formatted in BBCode [B]xx[/B] into HTML format <strong>xx</strong>.
        *
        * @param       string       text
        * @param       boolean      do amperstand & conversion? ie: & = &amp; (default false)
        *
        * @return      string       Converted BBcode [B]xx[/B] to HTML -> <strong>xx</strong>
        */
        function bbcode_to_html($text = '', $doamperstand = false, $replaceltgt = true)
        {
                global $ilance, $ilconfig;
                if ($doamperstand)
                {
                        // if we don't do this, auction descriptions show like &amp;#1239; vs. &#1239;
                        $pattern[] = "#&#";
                        $replace[] = '&amp;';
                }
                
                ($apihook = $ilance->api('bbcode_to_html_start')) ? eval($apihook) : false;
                
                if ($replaceltgt)
                {
                        $pattern[] = "#<#";
                        $replace[] = '&lt;';
                        $pattern[] = "#>#";
                        $replace[] = '&gt;';
                        $pattern[] = "#\r\n#si";
                        $replace[] = "<br />";
                        $pattern[] = "#\n#si";
                        $replace[] = "<br />";
                }
                $pattern[] = "#  #si";
                $replace[] = '&nbsp;&nbsp;';
                $pattern[] = "#\t#si";
                $replace[] = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                
                $pattern[] = "#\[hr\]#si";
                $replace[] = '<hr style="height:1px;width:100%;background-color:#cccccc" />';	
                $pattern[] = "#\[table\]#si";
                $replace[] = '<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">';
                $pattern[] = "#\[\/table\]#si";
                $replace[] = '</table>';
                $pattern[] = "#\[td\]#si";
                $replace[] = '<td>';
                $pattern[] = "#\[\/td\]#si";
                $replace[] = '</td>';
                $pattern[] = "#\[tr\]#si";
                $replace[] = '<tr>';
                $pattern[] = "#\[\/tr\]#si";
                $replace[] = '</tr>';
                $pattern[] = "#\[p\]#si";
                $replace[] = "<p>";
                $pattern[] = "#\[\/p\]#si";
                $replace[] = "</p>";
                $pattern[] = "#\[(indent|blockquote)\]#si";
                $replace[] = "<blockquote>";
                $pattern[] = "#\[\/(indent|blockquote)\]#si";
                $replace[] = "</blockquote>";
                $pattern[] = "#\[(\/|)sub\]#si";
                $replace[] = "<$1sub>";
                $pattern[] = "#\[(\/|)sup\]#si";
                $replace[] = "<$1sup>";
                $pattern[] = "#\[(\/|)strike\]#si";
                $replace[] = "<$1strike>";
                $pattern[] = "#\[(\/|)u\]#si";
                $replace[] = "<$1u>";
                $pattern[] = "#\[(\/|)b\]#si";
                $replace[] = "<$1strong>";
                $pattern[] = "#\[(\/|)i\]#si";
                $replace[] = "<$1em>";
                $pattern[] = "#\[size=1\]#si";
                $replace[] = '<span style="font-size: 8pt">';
                $pattern[] = "#\[size=2\]#si";
                $replace[] = '<span style="font-size: 10pt">';
                $pattern[] = "#\[size=3\]#si";
                $replace[] = '<span style="font-size: 12pt">';
                $pattern[] = "#\[size=4\]#si";
                $replace[] = '<span style="font-size: 14pt">';
                $pattern[] = "#\[size=5\]#si";
                $replace[] = '<span style="font-size: 18pt">';
                $pattern[] = "#\[size=6\]#si";
                $replace[] = '<span style="font-size: 24pt">';
                $pattern[] = "#\[size=7\]#si";
                $replace[] = '<span style="font-size: 36pt">';
                $pattern[] = "#\[font=(.*?)\]#si";
                $replace[] = '<span style="font-family: $1">';
                $pattern[] = "#\[color=(.*?)\]#si";
                $replace[] = '<span style="color: $1">';
                $pattern[] = "#\[highlight=(.*?)\]#si";
                $replace[] = '<span style="background-color: $1">';
                $pattern[] = "#\[padding=(.*?)\]#si";
                $replace[] = '<span style="padding: $1">';
                $pattern[] = "#\[\/(font|color|size|highlight|padding)\]#si";
                $replace[] = '</span>';
                $pattern[] = "#\[(center|left|right|justify)\]#si";
                $replace[] = "<div align=\"$1\">";
                $pattern[] = "#\[\/(center|left|right|justify)\]#si";
                $replace[] = "</div>";
                $pattern[] = "#\[email=(.*?)\]#si";
                $replace[] = '<span class="blue"><a href="mailto:$1">';
                $pattern[] = "#\[email\](.*?)\[\/email\]#si";
                $replace[] = '<span class="blue"><a href="mailto:$1">$1[/email]';
                $pattern[] = "#\[url=(.*?)\]#si";
                $replace[] = '<span class="blue"><a href="$1" target="_blank">';
                $pattern[] = "#\[url\](.*?)\[\/url\]#si";
                $replace[] = '<span class="blue"><a href="$1" target="_blank">$1[/url]';
                $pattern[] = "#\[\/(email|url)\]#si";
                $replace[] = "</a></span>";
                $pattern[] = "#\[img style=\"(.*)\"\](.*)\[\/img\]#Usi";
                $replace[] = '<img src="$2" alt="$2" style="$1" />';
                $pattern[] = "#\[img\](.*)\[\/img\]#Usi";
                $replace[] = '<img src="$1" />';
                $pattern[] = "#\[list=1\]#si";
                $replace[] = '<ol style="list-style-type:decimal">';
                $pattern[] = "#\[list\]#si";
                $replace[] = '<ul style="list-style-type:circle">';
                $pattern[] = "#\[\*\]#si";
                $replace[] = '<li style="margin-left:18px">';
                $pattern[] = "#<br[^>]*><li>#si";
                $replace[] = "<li>";
                $pattern[] = "#<br[^>]*> <li>#si";
                $replace[] = "<li>";
                $pattern[] = "#<br[^>]*><\/li>#si";
                $replace[] = "</li>";
                $pattern[] = "#\[\/list\]#si";
                $replace[] = '</list>';
                $pattern[] = "#\[FLASH=(.*?),(.*?)\](.*?)\[\/FLASH\]#si";
                $replace[] = '<object width="$1" height="$2"><param name="movie" value="$3"></param><param name="wmode" value="transparent"></param><embed src="$3" type="application/x-shockwave-flash" wmode="transparent" width="$1" height="$2"></embed></object>';
                $pattern[] = "#\[CODE\](.*?)\[\/CODE\]#si";
                $replace[] = '<div class="quotereply"><div><span style="font-family: verdana; color:#555">$1</span></div></div>';
                $text = preg_replace($pattern, $replace, $text);
                if (preg_match("/<ol/si", $text) OR preg_match("/<ul/si", $text))
                {
                        $array = mb_split("<", $text);
                        $output = "";
                        $x = 0;
                        foreach ($array AS $line)
                        {
                                if ($x > 0)
                                {
                                        $line = "<" . $line;
                                }
                                if (preg_match("/<ol/i", $line))
                                {
                                        $temp = "</ol>";
                                }
                                else if (preg_match("/<ul/i", $line))
                                {
                                        $temp = "</ul>";
                                }
                                if (preg_match("/<\/list>/i", $line))
                                {
                                        $line = str_replace("</list>", $temp, $line);
                                }
                                $output .= $line;
                                $x++;
                        }
                }
                else
                {
                        $output = $text;
                }
                $output = str_replace("<li>", "</li><li>", $output);
                $output = str_replace("<ul></li>", "<ul>", $output);
                $output = str_replace("<ol></li>", "<ol>", $output);
                $output = str_replace("</ul>", "</li></ul>", $output);
                $output = str_replace("</ol>", "</li></ol>", $output);
                $this->bbcode_quote_handler($output);
                
                ($apihook = $ilance->api('bbcode_to_html_end')) ? eval($apihook) : false;
                
                return $output;
        }
        
        /**
        * Function to strip out any special tags we provide the function and then have them replaced at the very end.  This is useful
        * for stripping out [PHP]xxxx[/PHP] tags so we don't process anything within the [PHP]..[/PHP] tags and other situations that may arise.
        *
        * @param       string       tag to strip (example: PHP)
        * @param       string       text
        * @param       array        matches text
        *
        * @return      string       HTML formatted string
        */
        function strip_special_codes($tag = '', &$text, &$matches)
        {
                preg_match_all("'\[$tag.*\](.*)\[/$tag\]'isU", $text, $matches);
                for ($i = 0; $i < count($matches[0]); $i++)
                {
                        $text = str_replace($matches[0][$i], "~~$tag~~$i~~$tag~~", $text);
                }
        }
        
        /**
        * Function to restore any stripped out special tags.
        *
        * @param       string       tag to restore (example: PHP)
        * @param       array        text
        * @param       array        matches text
        * 
        *
        * @return      string       HTML formatted string
        */
        function restore_special_codes($tag = '', &$text, &$matches)
        {
                for ($i = 0; $i < count($matches[0]); $i++)
                {
                        $text = str_replace("~~$tag~~$i~~$tag~~", $matches[0][$i], $text);
                }
        }
        
        /**
        * Function to prepare special tags supplied to the function along with any text string.
        *
        * @param       string       tag to match
        * @param       string       text
        *
        * @return      string       HTML formatted string
        */
        function prepare_special_codes($tag = '', $html = '')
        {
                preg_match_all("'\[$tag.*\](.*)\[/$tag\]'isU", $html, $matches);
                for ($i = 0; $i < count($matches[0]); $i++)
                {
                        $html = str_replace($matches[0][$i], "***$i***", $html);
                        $matches[0][$i] = nl2br(htmlspecialchars(htmlspecialchars($matches[0][$i])));
                        $html = str_replace("***$i***", $matches[0][$i], $html);
                }
                return($html);
        }
        
        /**
        * Function to strip out any HTML tags within the supplied text string.
        *
        * @param       array        matches text
        *
        * @return      string       HTML formatted string
        */
        function strip_html_tags($matches = array())
        {
                foreach ($matches AS $key => $value)
                {
                        $matches["$key"] = preg_replace("'<[^\?](.*)>'siU", "\n", $value);
                }
        }
        
        /**
        * Function to convert HTML code into BBCode.
        * {@source 16}
        *
        * @param       string       text
        *
        * @return      string       HTML formatted string
        */
        function html_to_bbcode($text = '')
        {
                global $ilance, $ilconfig;
                $this->strip_special_codes('PHP', $text, $php_matches);
                $this->strip_special_codes('CODE', $text, $code_matches);
                $this->strip_special_codes('HTML', $text, $html_matches);
                $this->strip_special_codes('QUOTE', $text, $quote_matches);
                $this->strip_html_tags($php_matches);
                $this->strip_html_tags($code_matches);
                $this->strip_html_tags($html_matches);
                $this->strip_html_tags($quote_matches);
                $search = array(
                        "'<pre>(.*)</pre>'siU",
                        "'<b>(.*)</b>'siU",
                        "'<strong>(.*)</strong>'siU",
                        "'<i>(.*)</i>'siU",
                        "'<em>(.*)</em>'siU",
                        "'<u>(.*)</u>'siU",
                        "'<strike>(.*)</strike>'siU",
                        "'<p align=\"justify\">(.*)</p>'siU",
                        "'<div style=\"text-align: left;\">(.*)</div>'siU",
                        "'<p align=\"left\">(.*)</p>'siU",
                        "'<div style=\"text-align: center;\">(.*)</div>'siU",
                        "'<p align=\"center\">(.*)</p>'siU",
                        "'<div style=\"text-align: right;\">(.*)</div>'siU",
                        "'<p align=\"right\">(.*)</p>'siU",
                        "'<blockquote dir=\"ltr\" style=\"margin-right: 0px\">(.*)</blockquote>'siU",
                        "'<div style=\"margin-left: 40px;\">(.*)</div>'siU",
                        "'<ol>(.*)</ol>'siU",
                        "'<ul>(.*)</ul>'siU",
                        "'<li>(.*)</li>'siU",
                        "'<img(.*)src=\"([^\"]+)\"(.*)style=\"(.*)\"(.*)>'siU",
                        "'<a href=\"mailto:(.*)\">(.*)</a>'siU",
                        "'<a href=(.*)>(.*)</a>'siU",
                        "'<font size=(.*)>(.*)</font>'siU",
                        "'<font face=(.*) size=(.*)>(.*)</font>'siU",
                        "'<font face=(.*)>(.*)</font>'siU",
                        "'<font color=(.*) size=(.*)>(.*)</font>'siU",
                        "'<font color=(.*)>(.*)</font>'siU",
                        "'<p>(.*)</p>'siU",
                        "'<div>(.*)</div>'siU",
                        "'<p />'siU",
                        "'<(br|br /)>'si",
                        "'<hr />'siU",
                        "'<hr style=\"(.*)\">(.*)'siU",
                        "'<div class=(.*)>(.*)'siU",
                        "'<div class=\"(.*)\">(.*)'siU",
                        "'<h(.*)>(.*)</h(.*)>'siU",
                );
                $replace = array(
                        "[PHP]$1[/PHP]",
                        "[B]$1[/B]",
                        "[B]$1[/B]",
                        "[I]$1[/I]",
                        "[I]$1[/I]",
                        "[U]$1[/U]",
                        "[STRIKE]$1[/STRIKE]",
                        "[JUSTIFY]$1[/JUSTIFY]",
                        "[LEFT]$1[/LEFT]",
                        "[LEFT]$1[/LEFT]",
                        "[CENTER]$1[/CENTER]",
                        "[CENTER]$1[/CENTER]",
                        "[RIGHT]$1[/RIGHT]",
                        "[RIGHT]$1[/RIGHT]",
                        "[INDENT]$1[/INDENT]",
                        "[INDENT]$1[/INDENT]",
                        "[list=1]$1[/list]",
                        "[list=a]$1[/list]",
                        "[*]$1",
                        "[IMG style=\"$4\"]$2[/IMG]",
                        "[EMAIL=\"$1\"]$2[/EMAIL]",
                        "[URL=\"$1\"]$2[/URL]",
                        "[SIZE=\"$1\"]$2[/SIZE]",
                        "[FONT=\"$1\" SIZE=\"$2\"]$3[/FONT]",
                        "[FONT=\"$1\"]$2[/FONT]",
                        "[COLOR=\"$1\" SIZE=\"$2\"]$3[/FONT]",
                        "[COLOR=\"$1\"]$2[/COLOR]",
                        "[P]$1[/P]",
                        "[P]$1[/P]",
                        "",
                        "[BR]",
                        "[HR]",
                        "[HR]",
                        "",
                        "",
                        "[P][B]$2[/B][/P]",
                );
                
                ($apihook = $ilance->api('html_to_bbcode_start')) ? eval($apihook) : false;
                
                $text = preg_replace($search, $replace, $text);
                unset($search, $replace);
                $search = array(
                        "[P][/P]",
                        "[B][/B]",
                        "[I][/I]",
                        "[U][/U]",
                        "[STRIKE][/STRIKE]",
                        "[JUSTIFY][/JUSTIFY]",
                        "[LEFT][/LEFT]",
                        "[CENTER][/CENTER]",
                        "[RIGHT][/RIGHT]",
                        "[INDENT][/INDENT]",
                        "[TABLE][/TABLE]",
                );
                $replace = array(
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                );
                
                ($apihook = $ilance->api('html_to_bbcode_end')) ? eval($apihook) : false;
                
                $text = str_replace($search, $replace, $text);
                unset($search, $replace);
                $this->restore_special_codes('QUOTE', $text, $quote_matches);
                $this->restore_special_codes('HTML', $text, $html_matches);
                $this->restore_special_codes('CODE', $text, $code_matches);
                $this->restore_special_codes('PHP', $text, $php_matches);
                return $text;
        }
        
        /**
        * Function to strip out any BBCode tags within the supplied text string.
        * 
        * @param       string       text
        * 
        * @return      string       HTML formatted string formatted without any BBCode tags
        * 
        */
        function strip_bb_tags($text = '')
        {
                global $ilconfig;
                $this->strip_special_codes('PHP', $text, $php_matches);
                $this->strip_special_codes('CODE', $text, $code_matches);
                $this->strip_special_codes('HTML', $text, $html_matches);
                $this->strip_special_codes('QUOTE', $text, $quote_matches);
                $search = array(
                        "'\[TABLE\](.*)\[/TABLE\]'siU",
                        "'\[TR\](.*)\[/TR\]'siU",
                        "'\[TD\](.*)\[/TD\]'siU",
                        "'\[TR\]'si",
                        "'\[TD\]'si",
                        "'\[/TR\]'si",
                        "'\[/TD\]'si",
                        "'\[TABLE\]'si",
                        "'\[/TABLE\]'si",
                        "'\[B\](.*)\[/B\]'siU",
                        "'\[I\](.*)\[/I\]'siU",
                        "'\[U\](.*)\[/U\]'siU",
                        "'\[STRIKE\](.*)\[/STRIKE\]'siU",
                        "'\[JUSTIFY\](.*)\[/JUSTIFY\]'siU",
                        "'\[LEFT\](.*)\[/LEFT\]'siU",
                        "'\[CENTER\](.*)\[/CENTER\]'siU",
                        "'\[RIGHT\](.*)\[/RIGHT\]'siU",
                        "'\[INDENT\](.*)\[/INDENT\]'siU",
                        "'\[list=1\](.*)\[/list\]'siU",
                        "'\[list=a\](.*)\[/list\]'siU",
                        "'\[LIST\](.*)\[/LIST\]'siU",
                        "'\[\*\]'siU",
                        "'\[IMG(.*)\](.*)\[/IMG\]'siU",
                        "'\[EMAIL=(.*)\](.*)\[/EMAIL\]'siU",
                        "'\[URL=(.*)\](.*)\[/URL\]'siU",
                        "'\[SIZE=(.*)\](.*)\[/SIZE\]'siU",
                        "'\[FONT=(.*) SIZE=(.*)\](.*)\[/FONT\]'siU",
                        "'\[FONT=(.*)\](.*)\[/FONT\]'siU",
                        "'\[COLOR=(.*)\](.*)\[/COLOR\]'siU",
                        "'\[P\](.*)\[/P\]'siU",
                        "'\[BR\]'si",
                        "'\[HR\]'si",
                        "'\[BLOCKQUOTE\](.*)\[/BLOCKQUOTE\]'siU",
                        "'\[HIGHLIGHT=(.*)\](.*)\[/HIGHLIGHT\]'siU",
                        "'\[URL\](.*)\[/URL\]'siU",
                        "'\[-WEBKIT-AUTO\](.*)\[/-WEBKIT-AUTO\]'siU",
                );
                $replace = array(
                        "$1",
                        "$1",
                        "$1",
                        "$1",
                        "$1",
                        "$1",
                        "$1",
                        "$1",
                        "$1",
                        "$1",
                        "$1",
                        "$1",
                        "$1",
                        "$1",
                        "$1",
                        "$1",
                        "$1",
                        "$1",
                        "$1",
                        "$1",
                        "$1",
                        "\n",
                        "$2",
                        "mailto: $1",
                        "$2 ($1)",
                        "$2",
                        "$3",
                        "$2",
                        "$2",
                        "$1\n",
                        "\n",
                        "**************************************************\n",
                        "$1",
                        "$2",
                        "$1",
                        "$1",
                );
                $text = preg_replace($search, $replace, $text);
                $text = preg_replace($search, $replace, $text);
                unset($search, $replace);
                $this->restore_special_codes('QUOTE', $text, $quote_matches);
                $this->restore_special_codes('HTML', $text, $html_matches);
                $this->restore_special_codes('CODE', $text, $code_matches);
                $this->restore_special_codes('PHP', $text, $php_matches);
                return($text);
        }
        
        /**
        * Function to handle the processing of nested bbcode [quote] tags within a string.
        * 
        * @param     string     $str Passed by reference
        * @return    void       Working with the actual string, not a copy.
        */
        function bbcode_quote_handler(&$output)
        {
                // #### pre-process some custom quotes #########################
                $pattern[] = "#\[QUOTE=(.*?);(.*?)\](.*?)\[\/QUOTE\]#si";
                $replace[] = '<div class="quotereply"><div style="padding-bottom:6px" class="smaller gray">{_originally_posted_by} $1:</div><div><span style="font-family: verdana; color:#555"><em>$3</em></span></div></div>';
                $output = preg_replace($pattern, $replace, $output);
                $pattern[] = "#\[QUOTE=(.*?)\](.*?)\[\/QUOTE\]#si";
                $replace[] = '<div class="quotereply"><div style="padding-bottom:6px" class="smaller gray">{_originally_posted_by} $1:</div><div><span style="font-family: verdana; color:#555"><em>$2</em></span></div></div>';
                $output = preg_replace($pattern, $replace, $output);
                $pattern[] = "#\[QUOTE\](.*?)\[\/QUOTE\]#si";
                $replace[] = '<div>$1</div>';
                $output = preg_replace($pattern, $replace, $output);
                // no point proceeding if there are no bb code tags for QUOTE
                if (false !== strpos($output, '[quote]') OR false !== strpos($output, '[QUOTE]'))
                {
                        $len = strlen($output);
                        $pos = 0;
                        $new_str = null;
                        $tag = null;
                        $in_quote = false;
                        $nested = array();
                        while ($pos < $len)
                        {
                                $c = $output{$pos}; // get the current character
                                if ($tag)
                                {
                                        $tag .= $c;
                                        if ($c == ']')
                                        {
                                                if ($tag == '[quote]' OR $tag == '[QUOTE]')
                                                {
                                                        if ($in_quote)
                                                        {
                                                                $nested[] = true;
                                                                $in_quote .= '<div>';
                                                        }
                                                        else
                                                        {
                                                                $in_quote = '<div title="Quoted Text">';
                                                        }
                                                }
                                                else if ($tag == '[/quote]' OR $tag == '[/QUOTE]')
                                                {
                                                        if ($nested)
                                                        {
                                                                array_pop($nested);
                                                                $in_quote .= '</div>';
                                                        }     
                                                        else
                                                        {
                                                                $new_str = $in_quote . '</div>';
                                                                $in_quote = null;
                                                        }
                                                }
                                                else
                                                {
                                                        // this is some other tag, let it go
                                                        if ($in_quote)
                                                        {
                                                                $in_quote .= $tag;
                                                        }
                                                        else
                                                        {
                                                                $new_str .= $tag;
                                                        }
                                                }
                                                $tag = null;    
                                        }
                                }
                                else if ($in_quote)
                                {
                                        if ($c == '[')
                                        {
                                                $tag = $c;    
                                        }
                                        else
                                        {
                                                $in_quote .= $c;
                                        }
                                }
                                else if ($c == '[')
                                {
                                        $tag .= $c;
                                }
                                else
                                $new_str .= $c;
                                ++$pos;
                        }
                        $output = &$new_str;
                }
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>