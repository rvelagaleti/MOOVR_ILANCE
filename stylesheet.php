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

// disable time limit for running scripts
@ignore_user_abort(1);
@set_time_limit(0);

// #### setup script location ##################################################
define('LOCATION', 'stylesheet');
define('SKIP_SESSION', true);

// #### require backend ########################################################
require_once('functions/config.php');

$html = "";

$css = array();

($apihook = $ilance->api('stylesheet_start')) ? eval($apihook) : false;

// #### determine what stylesheet's we want to load ############################
if (isset($ilance->GPC['do']) AND !empty($ilance->GPC['do']))
{
	$css = explode(',', $ilance->GPC['do']);
	if (isset($css) AND is_array($css) AND count($css) > 0)
	{
		foreach ($css AS $cssfile)
		{
			if (!empty($cssfile))
			{
				$html .= "";
			}
		}
	}
}

($apihook = $ilance->api('stylesheet_end')) ? eval($apihook) : false;

// #### print our client javascript ############################################
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