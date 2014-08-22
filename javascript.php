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
$expires = 60 * 60 * 24 * 7; // 1 week cache
header('Pragma: public');
header('Cache-Control: max-age=' . $expires);
header('Expires: ' . date('D, d M Y H:i:s', time() + $expires) . ' GMT');
header('Content-type: application/x-javascript');
$html = "function ilance_require(jspath)
{
	document.write('<script type=\"text\/javascript\" src=\"' + jspath + '\" charset=\"utf-8\"><\/script>');
}
";
$js = array();
if (isset($_REQUEST['dojs']) AND !empty($_REQUEST['dojs']))
{
	$js = explode(',', $_REQUEST['dojs']);
	if (isset($js) AND is_array($js) AND count($js) > 0)
	{
		foreach ($js AS $jsfile)
		{
			if (!empty($jsfile))
			{
				switch ($jsfile)
				{
					case 'mootools':
					{
						$html .= "ilance_require(\"//ajax.googleapis.com/ajax/libs/mootools/1.4.5/mootools-yui-compressed.js\");\n";
						break;
					}
					case 'jquery':
					{
						$html .= "ilance_require(\"//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js\");\n";
						break;
					}
					case 'functions':
					{
						$html .= "ilance_require(JSBASE + \"$jsfile\" + ((MINIFY == '1') ? \".min\" : \"\") + \".js\");\n";
						break;
					}
					case 'ckeditor':
					{
						$html .= "ilance_require(JSBASE + \"ckeditor/$jsfile.js\");\n";
						break;
					}
					default:
					{
						$html .= "ilance_require(JSBASE + \"functions_$jsfile\" + ((MINIFY == '1') ? \".min\" : \"\") + \".js\");\n";
						break;
					}
				}
			}
		}
	}
}
if (!empty($html))
{
	echo $html;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>