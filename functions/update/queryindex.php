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
if ($i == '745' AND !isset($show['upgrade_mode']))
{
	$query['745']['error'] = 1;
}
$error = 0;
while (isset($query[$i + 1]) AND $error != '1')
{
	if (!empty($query[$i + 1]))
	{
		$query[$i + 1]['error'] = $ilance->db->query($query[$i + 1], 1, null, __FILE__, __LINE__, true, array('1091'));
		if ($i == '752')
		{
			$sql = $ilance->db->query("SELECT languagecode FROM " . DB_PREFIX . "language WHERE languagecode != 'english'");
			if ($ilance->db->num_rows($sql) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					$ilance->db->query("
						ALTER TABLE " . DB_PREFIX . "categories
						ADD seourl_" . strtolower(substr($res['languagecode'], 0, 3)) . " MEDIUMTEXT NOT NULL
						AFTER `level`
					");
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "categories
						SET seourl_" . strtolower(substr($res['languagecode'], 0, 3)) . " = seourl_eng
					");
				}
			}
		}
		else if ($i == '845')
		{
			$sql = $ilance->db->query("SELECT languagecode FROM " . DB_PREFIX . "language WHERE languagecode != 'english'");
			if ($ilance->db->num_rows($sql) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					$ilance->db->query("
						ALTER TABLE " . DB_PREFIX . "skills
						ADD seourl_" . strtolower(substr($res['languagecode'], 0, 3)) . " MEDIUMTEXT NOT NULL
						AFTER `rootcid`
					");
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "skills
						SET seourl_" . strtolower(substr($res['languagecode'], 0, 3)) . " = seourl_eng
					");
				}
			}
		}
		else if ($i == '883')
		{ // configuration_groups
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'maintenance'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'service' WHERE groupname = 'servicerating'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'service' WHERE groupname = 'serviceupsell_bold'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'service' WHERE groupname = 'serviceupsell_featured'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'service' WHERE groupname = 'serviceupsell_highlight'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'service' WHERE groupname = 'serviceupsell_autorelist'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'service' WHERE groupname = 'serviceupsell_featured_searchresults'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'product' WHERE groupname = 'productupsell_bold'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'product' WHERE groupname = 'productupsell_featured'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'product' WHERE groupname = 'productupsell_highlight'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'product' WHERE groupname = 'productupsell_autorelist'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'product' WHERE groupname = 'productupsell_featured_searchresults'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'product' WHERE groupname = 'productaward_pmb'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'product' WHERE groupname = 'productaward_mediashare'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'product' WHERE groupname = 'productbid_limits'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'service' WHERE groupname = 'portfoliodisplay'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'service' WHERE groupname = 'portfolioupsell'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'registrationdisplay'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'registrationupsell'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'referalsystem'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'attachmentsystem'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'attachmentmoderation'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'attachmentlimit_profileextensions'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'attachmentlimit_defaultextensions'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'escrowsystem'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'attachmentlimit_portfolioextensions'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'invoicesystem'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'globalserversettings'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'globalserverlocalecurrency'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'globalserverlocale'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'globalserverdistanceapi'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'globalfilterspmb'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'globalsecuritysettings'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'globalsecurity'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'globalsecuritymime'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'globalfiltersrfp'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'globalfiltersbid'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'globalfiltersvulgar'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'globalfiltersipblacklist'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'globalfilterresults'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'globalauctionsettings'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'globalfilterspsp'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'template'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'product' WHERE groupname = 'productupsell_fees'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'service' WHERE groupname = 'verificationsystem'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'search'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'service' WHERE groupname = 'servicebid_limits'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'language'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'service' WHERE groupname = 'skills'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'product' WHERE groupname = 'nonprofits'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'globalseo'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'service' WHERE groupname = 'serviceupsell_fees'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'globalcategorysettings'");	
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'product' WHERE groupname = 'attachmentlimit_productphotosettings'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'product' WHERE groupname = 'attachmentlimit_productslideshowsettings'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'product' WHERE groupname = 'attachmentlimit_productdigitalsettings'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'attachmentlimit_searchresultsettings'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'service' WHERE groupname = 'attachmentlimit_bidsettings'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'attachmentlimit_pmbsettings'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'attachmentlimit_workspacesettings'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'product' WHERE groupname = 'shippingsettings'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'product' WHERE groupname = 'shippingapiservices_fedex'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'product' WHERE groupname = 'shippingapiservices_ups'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'product' WHERE groupname = 'shippingapiservices_usps'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'subscriptions_settings'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'globaltabvisibility'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'pmb_wysiwygsettings'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'listingdescription_wysiwygsettings'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'proposal_wysiwygsettings'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'profileintro_wysiwygsettings'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'product' WHERE groupname = 'productblocks'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'globalserversmtp'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'globalservercache'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'globalserversession'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration_groups SET type = 'global' WHERE groupname = 'emailssettings'");
		}
		else if ($i == '884')
		{ // configuration
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'maintenance_mode'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'maintenance_excludeips'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'maintenance_excludeurls'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'maintenance_message'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'serviceupsell_highlightactive'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'serviceupsell_highlightfees'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'serviceupsell_highlightcolor'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'serviceupsell_highlightfee'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'serviceupsell_autorelistactive'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'serviceupsell_autorelistfees'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'serviceupsell_autorelistfee'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'serviceupsell_autorelistmaxdays'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'serviceupsell_featured_searchresultsactive'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'serviceupsell_featured_searchresultsfees'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'serviceupsell_featured_searchresultsfee'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productupsell_boldactive'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productupsell_boldfees'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productupsell_boldfee'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productupsell_featuredactive'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productupsell_featuredfees'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productupsell_featuredfee'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productupsell_highlightactive'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productupsell_highlightcolor'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productupsell_highlightfee'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productupsell_highlightfees'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productupsell_autorelistactive'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productupsell_autorelistfees'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productupsell_autorelistfee'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productupsell_autorelistmaxdays'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productupsell_featured_searchresultsactive'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productupsell_featured_searchresultsfees'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productupsell_featured_searchresultsfee'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productaward_pmbafterend'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productaward_mediashareafterend'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'portfolioupsell_featuredactive'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'portfolioupsell_featuredfee'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'portfolioupsell_featureditemname'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'portfoliodisplay_thumbsperpage'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'portfoliodisplay_imagetypes'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'registrationdisplay_turingimage'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'registrationupsell_bonusactive'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'registrationdisplay_phoneformat'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'registrationdisplay_quickregistration'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'registrationupsell_amount'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'registrationupsell_bonusitemname'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'genderactive'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'referalsystem_active'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'referalsystem_payout'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'attachment_dbstorage'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'watermark'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'watermark_text'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'watermark_image'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'watermark_textsize'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'watermark_imageopacity'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'watermark_textfont'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'watermark_quality'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'watermark_angle'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'watermark_position'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'watermark_padding'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'watermark_profiles'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'watermark_portfolios'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'watermark_itemphoto'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'watermark_storesitemphoto'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'attachment_moderationdisabled'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'attachment_mediasharemoderationdisabled'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'attachmentlimit_profileextensions'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'attachmentlimit_defaultextensions'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'attachmentlimit_portfolioextensions'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'attachmentlimit_profilemaxwidth'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'attachmentlimit_profilemaxheight'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'attachmentlimit_profilemaxsize'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'attachmentlimit_projectmaxwidth'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'attachmentlimit_projectmaxheight'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'attachmentlimit_projectmaxsize'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'attachmentlimit_portfoliomaxwidth'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'attachmentlimit_portfoliomaxheight'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'attachmentlimit_portfoliomaxsize'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'attachmentlimit_portfoliothumbwidth'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'attachmentlimit_portfoliothumbheight'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'attachmentlimit_bidmaxwidth'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'attachmentlimit_bidmaxheight'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'attachmentlimit_bidmaxsize'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'attachmentlimit_pmbmaxwidth'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'attachmentlimit_pmbmaxheight'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'attachmentlimit_pmbmaxsize'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'attachmentlimit_mediasharemaxwidth'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'attachmentlimit_mediasharemaxheight'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'attachmentlimit_mediasharemaxsize'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'attachmentlimit_searchresultsmaxwidth'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'attachmentlimit_searchresultsmaxheight'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'attachmentlimit_searchresultsgallerymaxwidth'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'attachmentlimit_searchresultsgallerymaxheight'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'attachmentlimit_searchresultssnapshotmaxwidth'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'attachmentlimit_searchresultssnapshotmaxheight'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'attachmentlimit_thumbnailmaxwidth'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'attachmentlimit_thumbnailmaxheight'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'registrationdisplay_defaultcountry'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'registrationdisplay_defaultstate'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'escrowsystem_payercancancelfunds'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'escrowsystem_payercancancelfundsafterrelease'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'invoicesystem_enablep2btransactionfees'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'invoicesystem_transactionidlength'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'moderationsystem_disableauctionmoderation'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalsecurity_emailonfailedlogins'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalsecurity_numfailedloginattempts'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalsecurity_extensionmime'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_emailfilterpmb'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_domainfilterpmb'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_enablepmbspy'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_emailfilterrfp'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_domainfilterrfp'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_enablerfpcancellation'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'globalfilters_emailfilterbid'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'globalfilters_domainfilterbid'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_emailfilterpsp'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_domainfilterpsp'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_vulgarpostfilter'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_vulgarpostfilterlist'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_vulgarpostfilterreplace'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_blockips'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_enablecategorycount'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_maxrowsdisplay'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserver_enabledistanceradius'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserverlocale_sitetimezone'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserverlocale_defaultcurrency'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserverlocale_defaultcurrencyxml'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserverlocale_currencyselector'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserversmtp_enabled'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserversmtp_usetls'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserversmtp_host'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserversmtp_port'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserversmtp_user'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserversmtp_pass'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserversettings_companyname'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserversettings_sitename'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserversettings_siteaddress'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserversettings_siteemail'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserversettings_sitephone'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserverlanguage_defaultlanguage'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalauctionsettings_productauctionsenabled'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalauctionsettings_serviceauctionsenabled'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalauctionsettings_auctionstypeenabled'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_pmbpopupwidth'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_pmbpopupheight'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_pmbwysiwyg'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_pmbattachments'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_enablebbcode'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_enablewysiwyg'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_changeauctiontitle'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'globalfilters_maxcharactersdescriptionbulk'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_maxcharacterstitle'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_clientcpnag'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_whitespacestripper'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_countdowndelayms'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_categorydelayms'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_categorynextdelayms'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_auctiondescriptioncutoff'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_auctiontitlecutoff'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'globalfilters_specialshomepage'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_ajaxrefresh'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_regionmodal'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'globalfilters_listinginventory'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserverlocale_globaltimeformat'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserverlocale_globaldateformat'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserverlocale_yesterdaytodayformat'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalsecurity_blockregistrationproxies'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserversettings_accountsabbrev'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalauctionsettings_seourls'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'seourls_lowercase'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'invoicesystem_maximumpaymentdays'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'invoicesystem_mindepositamount'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'invoicesystem_maxdepositamount'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'invoicesystem_minwithdrawamount'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'invoicesystem_maxwithdrawamount'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'invoicesystem_minofflinedepositamount'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'invoicesystem_maxofflinedepositamount'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'portfoliodisplay_thumbsperrow'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'defaultstyle'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'registrationdisplay_dob'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'registrationdisplay_emailverification'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'registrationdisplay_emailban'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'registrationdisplay_userban'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'escrowsystem_enabled'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserver_distanceformula'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserver_distanceresults'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'registrationdisplay_dobunder18'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productbid_enablesniping'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productbid_displaybidname'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productbid_snipeduration'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productbid_enableproxybid'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productupsell_finalvaluefeesactive'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'escrowsystem_escrowcommissionfees'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserversettings_registrationnumber'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserversettings_vatregistrationnumber'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserversettings_vatregistrationoption'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserversettings_dunsoption'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserversettings_ilanceaid'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserversettings_facebookurl'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserversettings_twitterurl'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserversettings_googleplusurl'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'globalserversettings_homepageadurl'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'registrationdisplay_moderation'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'invoicesystem_unpaidreminders'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'invoicesystem_resendfrequency'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'invoicesystem_daysafterfirstreminder'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'portfoliodisplay_popups'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'escrowsystem_servicebuyerfixedprice'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'escrowsystem_servicebuyerpercentrate'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'escrowsystem_providerfixedprice'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'escrowsystem_providerpercentrate'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'attachmentlimit_slideshowmaxfiles'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'attachmentlimit_slideshowextensions'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'attachmentlimit_slideshowmaxwidth'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'attachmentlimit_slideshowmaxheight'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'attachmentlimit_slideshowmaxsize'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'attachmentlimit_digitalfilemaxsize'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productupsell_insertionfeesactive'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productupsell_slideshowcost'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productupsell_reservepricecost'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productupsell_buynowcost'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productupsell_videodescriptioncost'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productupsell_classifiedcost'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'attachmentlimit_productphotoextensions'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'attachmentlimit_productphotomaxwidth'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'attachmentlimit_productphotomaxheight'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'attachmentlimit_productphotomaxsize'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'attachmentlimit_productphotowidth'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'attachmentlimit_productphotoheight'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'attachmentlimit_productphotothumbwidth'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'attachmentlimit_productphotothumbheight'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'attachmentlimit_digitalfileextensions'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'escrowsystem_merchantfixedprice'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'escrowsystem_merchantpercentrate'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'escrowsystem_bidderfixedprice'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'escrowsystem_bidderpercentrate'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'serviceupsell_boldactive'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'serviceupsell_boldfees'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'serviceupsell_boldfee'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'serviceupsell_featuredactive'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'serviceupsell_featuredfees'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'serviceupsell_featuredfee'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'serviceupsell_videodescriptioncost'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'portfolioupsell_featuredlength'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productupsell_featuredlength'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'serviceupsell_featuredlength'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'invoicesystem_p2bfeesfixed'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'invoicesystem_p2bfee'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productbid_snipedurationcount'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'portfoliodisplay_enabled'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalsecurity_cookiename'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'verificationlength'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'verificationupdateafter'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'verificationmoderation'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_maxrowsdisplaysubscribers'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_refresh'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_cansendpms'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'invoicesystem_showlivedepositfees'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'invoicesystem_enableoffsitedepositpayment'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'invoicesystem_enableoffsitepaymenttypes'"); 	
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'invoicesystem_sendinvoice'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'searchfloodprotect'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'searchflooddelay'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'fulltextsearch'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'savedsearches'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'didyoumeancorrection'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalauctionsettings_maincatcutoff'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'globalfilters_contactform_listing'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productbid_countdownresets'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'searchdefaultcolumns'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'current_version'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'current_sql_version'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productbid_bidretract'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productbid_awardbidretract'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'min_1_stars_value'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'max_1_stars_value'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'min_2_stars_value'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'max_2_stars_value'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'min_3_stars_value'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'max_3_stars_value'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'min_4_stars_value'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'max_4_stars_value'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'min_5_stars_value'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'max_5_stars_value'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'servicebid_bidretract'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'servicebid_awardbidretract'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'servicebid_awardwaitperiod'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'servicebid_buyerunaward'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalauctionsettings_payperpost'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalauctionsettings_showfees'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalauctionsettings_endsoondays'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalauctionsettings_archivedays'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalauctionsettings_catmapgenres'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalauctionsettings_newicondays'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalauctionsettings_catmapdepth'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalauctionsettings_catquestiondepth'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalauctionsettings_catanswerdepth'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalauctionsettings_catmapgenredepth'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalauctionsettings_showcurrentcat'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalauctionsettings_catcutoff'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalauctionsettings_showbackto'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'portfoliodisplay_popups_width'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'portfoliodisplay_popups_height'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'registrationdisplay_defaultcity'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'escrowsystem_feestaxable'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'clean_old_log_entries'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'showfeaturedlistings'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'showendingsoonlistings'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'showlatestlistings'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'categorymapcache'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'categorymapcachetimeout'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'serveroverloadlimit'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'multilevelpulldown'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'enableskills'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'enablepopulartags'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'resetpopulartags'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'populartagcount'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'populartaglimit'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'showadmincpnews'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'enablenonprofits'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'globalfilters_bulkupload'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'globalfilters_bulkuploadlimit'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'globalfilters_bulkuploadpreviewlimit'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'globalfilters_bulkuploadcolsep'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'globalfilters_bulkuploadcolencap'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'durationdays'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'durationhours'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'durationminutes'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'servicecatschema'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productcatschema'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'servicecatidentifier'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productcatidentifier'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'servicecatmapidentifier'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productcatmapidentifier'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'categoryidentifier'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'listingsidentifier'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'servicelistingschema'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productlistingschema'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'servicelistingidentifier'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productlistingidentifier'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'expertslistingidentifier'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'memberslistingidentifier'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'portfolioslistingidentifier'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'categorylinkheaderpopup'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'categorylinkheaderpopuptype'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'categorymainsingleleftnavcount'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'worldwideshipping'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'shipping_regions'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'maxshipservices'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'shippingapi'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'shippingapi_debug'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'digitaldownload'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'ups_access_id'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'ups_username'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'ups_password'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'ups_server'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'usps_login'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'usps_password'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'usps_server'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'fedex_account'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'fedex_access_id'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'fedex_password'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'fedex_developer_key'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'enableauctiontab'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'enablefixedpricetab'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'enableclassifiedtab'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'publicfacing'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalauctionsettings_deletearchivedlistings'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserverlocale_currencycatcutoff'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_locationformat'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_gzhandler'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalfilters_jsminify'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'footercronjob'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'subscriptions_emailexpiryreminder'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'subscriptions_defaultroleid'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'post_request_whitelist'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'attachment_forceproductupload'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'automation_removewatchlist'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'globalfilters_slideshowimages'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'globalfilters_rowsslideshowimages'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'search_product_publicboards'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'search_product_images'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'search_product_noimages'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'search_product_freeship'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'search_product_lots'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'search_product_escrow'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'search_product_donation'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'search_product_completed'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'search_work_escrow'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'search_work_nondisclosed'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'search_work_completed'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'servicesearchheadercolumns'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'productsearchheadercolumns'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'expertssearchheadercolumns'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'oneleftribbonsearchresults'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'registration_allow_special'"); 
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'trend_tab'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'legend_tab'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'legend_tab_search_results'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'popular_tab'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'keywords_tab'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'keywords_tab_textcutoff'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'lowestpricecombined'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'timeleftblocks'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'search_price_tab'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'search_currency_tab'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'search_seller_tab'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'search_location_tab'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'search_options_tab'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'search_colors_tab'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'search_bidrange_tab'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'search_radius_tab'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'search_localsearch_tab'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'categoryboxorder'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'default_pmb_wysiwyg'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'ckeditor_pmbtoolbar'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'ckeditor_listingdescriptiontoolbar'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'default_proposal_wysiwyg'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'service' WHERE name = 'ckeditor_proposaltoolbar'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'default_profileintro_wysiwyg'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'ckeditor_profileintrotoolbar'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'product_draft_block'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'product_invite_block'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'product_restrictions_block'");  
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'product_publicboard_block'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'product_returnpolicy_block'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'product_scheduled_bidding_block'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'product' WHERE name = 'product_videodescription_block'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalservercache_engine'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalservercache_prefix'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalservercache_expiry'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserversession_guesttimeout'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserversession_membertimeout'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserversession_admintimeout'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'globalserversession_crawlertimeout'");
			$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET type = 'global' WHERE name = 'emailssettings_queueenabled'");
		}
		else if ($i == '890')
		{ // cat choices multi language
			$sql = $ilance->db->query("SELECT languagecode FROM " . DB_PREFIX . "language WHERE languagecode = 'english'");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "product_questions_choices
					SET choice_eng = choice
				");
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "project_questions_choices
					SET choice_eng = choice
				");
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "product_questions_choices DROP `choice`
				");
				$ilance->db->query("
					ALTER TABLE " . DB_PREFIX . "project_questions_choices DROP `choice`
				");
				$sql = $ilance->db->query("SELECT languagecode FROM " . DB_PREFIX . "language WHERE languagecode != 'english'");
				if ($ilance->db->num_rows($sql) > 0)
				{
					while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
					{
						$ilance->db->query("
							ALTER TABLE " . DB_PREFIX . "product_questions_choices
							ADD choice_" . strtolower(substr($res['languagecode'], 0, 3)) . " MEDIUMTEXT NOT NULL
							AFTER `questionid`
						");
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "product_questions_choices
							SET choice_" . strtolower(substr($res['languagecode'], 0, 3)) . " = choice_eng
						");
						$ilance->db->query("
							ALTER TABLE " . DB_PREFIX . "project_questions_choices
							ADD choice_" . strtolower(substr($res['languagecode'], 0, 3)) . " MEDIUMTEXT NOT NULL
							AFTER `questionid`
						");
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "project_questions_choices
							SET choice_" . strtolower(substr($res['languagecode'], 0, 3)) . " = choice_eng
						");
					}
				}
			}
		}
		else if ($i == '892')
		{ // watchlist listings per site basis
			$sql = $ilance->db->query("
				SELECT watchlistid, watching_project_id
				FROM " . DB_PREFIX . "watchlist
				WHERE watching_project_id > 0
					AND state = 'auction'
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					$project_state = fetch_auction('project_state', $res['watching_project_id']);
					if ($project_state == 'service' OR $project_state == 'product')
					{
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "watchlist
							SET mode = '" . $ilance->db->escape_string($project_state) . "'
							WHERE watchlistid = '" . $res['watchlistid'] . "'
							LIMIT 1
						");
					}
				}
			}
		}
	}
	else
	{
		$query[$i + 1]['error'] = '2';
	}
	if ($query[$i + 1]['error'] != '2' OR isset($show['upgrade_mode']))
	{
		$i++;
	}
	else
	{
		$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET value = '" . intval($i) . "' WHERE name = 'current_sql_version'");
		$error = 1;
		if ($i > 745)
		{
			$j = $i + 1;
			$sql = $ilance->db->query("
				SELECT log_id
				FROM " . DB_PREFIX . "error_log
				WHERE name = 'plugin_ilance'
					AND error_id = '" . intval($j) . "'
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "error_log
					SET info = '" . $ilance->db->escape_string($query[$i + 1]['error']) . "'
					WHERE log_id = '" . $res['log_id'] . "'
				");
			}
			else
			{
				$ilance->db->query("
					INSERT INTO " . DB_PREFIX . "error_log
					(`log_id`, `error_id`, `name`, `info`, `value`)
					VALUES ('', '" . intval($j) . "', 'plugin_ilance', '" . $ilance->db->escape_string($query[$i + 1]['error']) . "', '0')
				");
				log_event('0', 'plugin_ilance.xml', 'update_error', '', 'Error SQL Version: ' . $j);
			}
		}
	}
}
if ($sql_version != $i)
{
	$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET value = '" . $i . "' WHERE name = 'current_sql_version'");
}
unset($query);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>