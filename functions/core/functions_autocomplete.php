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
* Global auto complete functions for iLance
*
* @package      iLance\Global\AutoComplete
* @version      4.0.0.8059
* @author       ILance
*/

function mb_trim($string) 
{ 
	$string = preg_replace("/(^\s+)|(\s+$)/us", "", $string); 
	return $string; 
}

/**
* Function prevent a string from containing words passed to it through the argument
*
* @param       string       string
* @param       string       stop words array
*/
function stop_words($text = '', $stopwords = '')
{
	$stopwords = array_map("mb_strtolower", $stopwords);
	$stopwords = array_map("mb_trim", $stopwords);
	$pattern = '/[^\w]/u'; //'/[\W]/';
	$text = preg_replace($pattern, ',', $text);
	$text = mb_strtolower($text);
	$text_array = explode(",", $text);
	$text_array = array_map("mb_trim", $text_array);
	$html = '';
	foreach ($text_array AS $term)
	{
		if (!empty($term))
		{
			if (!in_array($term, $stopwords))
			{
				$html .= "$term ";
			}
		}
	}
	return mb_trim($html);
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>