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
* Global censory functions for iLance.
*
* @package      iLance\Global\Censor
* @version      4.0.0.8059
* @author       ILance
*/

/**
* Function to strip out any email phrases from a string such as a message or comment.
*
* @param       string       message
* 
* @return      string       Message with email phrases blocked
*/
function strip_email_words($message = '')
{
        global $phrase, $ilpage;
        $siteemail = SITE_EMAIL;
        if (!mb_ereg($siteemail, $message))
        {
                $message = preg_replace("'<a href=\"mailto:(.*)\">(.*)</a>'siU", '[{_email_blocked}]', $message);
                $message = preg_replace("![a-z0-9_.-]+@[a-z0-9-]+(\.[a-z]{2,6})+!i", '[{_email_blocked}]', $message);
        }
        return $message;
}

/**
* Function to strip out any domain name phrases from a string such as a message or comment.
*
* @param       string       message
* 
* @return      string       Message with domain phrases blocked
*/
function strip_domain_words($message = '')
{
        $sitedomain = HTTP_SERVER;
        if (!mb_ereg($sitedomain, $message))
        {        
                $message = preg_replace("'<a href=\"(.*)\">(.*)</a>'siU", '[{_domain_blocked}]', $message);
		$message = preg_replace("/(((http(s?)(:\/\/))?([w]{3}\.)?)([a-z|0-9])+\.(com(\.au)?|org|(uk(\.))?com|me|net|ly|be|gl|info|(co(\.))?uk|ca|co|us|de|li|im|nz|tv|edu|gov)((\/[^\s]+)*)+)/", '[{_domain_blocked}]', $message);
        }
        return $message;
}

/**
* Function to strip out any vulgar words based on a selection of words created in the admin cp.
*
* @param       string       message
* 
* @return      string       Message with domain phrases blocked
*/
function strip_vulgar_words($message = '', $stripurls = true)
{
        global $ilance, $ilconfig;
        // avoid breaking [IMG] bbcode tags and cut them out for a minute..
        $ilance->bbcode->strip_special_codes('IMG', $message, $php_matches);
        if ($ilconfig['globalfilters_vulgarpostfilter'])
        {
                $words_blacklist = array();
                $words = mb_split(', ', $ilconfig['globalfilters_vulgarpostfilterlist']);
                if (is_array($words) AND !empty($words))
                {
                        foreach ($words AS $vulgarword)
                        {
                                if (isset($vulgarword) OR !empty($vulgarword))
                                {
                                        $vulgarword = trim($vulgarword);
                                        $message = preg_replace("/\b$vulgarword\b/", "&nbsp;" . $ilconfig['globalfilters_vulgarpostfilterreplace'] . "&nbsp;", $message);
                                }
                        }
                }
        }
	if ($stripurls)
	{
		$message = ($ilconfig['globalfilters_emailfilterrfp']) ? strip_email_words($message) : $message;
		$message = ($ilconfig['globalfilters_domainfilterrfp']) ? strip_domain_words($message) : $message;
	}
        // restore our [IMG] bbcode tag back into the string
        $ilance->bbcode->restore_special_codes('IMG', $message, $php_matches);
        return $message;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>