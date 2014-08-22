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
// #### load required javascript ###############################################
$jsinclude = array(
	'header' => array(
		'functions',
		'ajax',
		'inline',
		'wysiwyg'
         ),
	'footer' => array(
		'v4',
		'tooltip',
		'cron'
	)
);

// #### setup script location ##################################################
define('LOCATION', 'pmb');

// #### require backend ########################################################
require_once('./functions/config.php');
require_once(DIR_CORE . 'functions_wysiwyg.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[pmb]" => $ilcrumbs["$ilpage[pmb]"]);

$area_title = '{_posting_private_message}';
$page_title = SITE_NAME . ' - ' . '{_posting_private_message}';

$ilance->GPC['decrypted'] = isset($ilance->GPC['crypted']) ? decrypt_url($ilance->GPC['crypted']) : '';

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND !empty($ilance->GPC['decrypted']))
{
	global $headinclude;
	
	// does admin request pmb removal?
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'remove-post' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
		{
			//echo 'removing post?!?!!?';
			$ilance->pmb->remove_pmb_post(intval($ilance->GPC['id']));
			// fix and include refresh bit
		}
	}
        // fetch an existing private message event
        $pmb['noproject'] = 0;
        if (!empty($ilance->GPC['decrypted']['event_id']) AND $ilance->GPC['decrypted']['event_id'] > 0)
        {
                if ($ilance->GPC['decrypted']['project_id'] == '0')
                {
                        $pmb['noproject'] = 1;
                }
        }
        else
        {
                $ilance->GPC['decrypted']['event_id'] = $ilance->pmb->fetch_pmb_eventid($ilance->GPC['decrypted']['project_id'], $ilance->GPC['decrypted']['from_id'], $ilance->GPC['decrypted']['to_id']);
        }
        if ($pmb['noproject'])
        {
                // fake PMB into thinking a project is open
                $res['status'] = 'open';
                $res['project_state'] = 'service';
                $res['project_title'] = '{_non_auction_related}';
                $res['cid'] = '0';
        }
        else
        {
                // be sure project is not delisted, cancelled, etc
                $sql = $ilance->db->query("
                        SELECT status, project_state, project_title, cid
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . intval($ilance->GPC['decrypted']['project_id']) . "'
                        LIMIT 1
                ");
                $res = $ilance->db->fetch_array($sql);        
        }
        $cmd = isset($ilance->GPC['cmd']) ? $ilance->GPC['cmd'] : '';
        if (!empty($ilance->GPC['decrypted']['isadmin']))
        {
                $ilance->GPC['decrypted']['isadmin'] = intval($ilance->GPC['decrypted']['isadmin']);
        }
        else
        {
                $ilance->GPC['decrypted']['isadmin'] = 0;
        }
        // #### SUBMIT NEW PRIVATE MESSAGE #####################################
        if (isset($cmd) AND $cmd == '_process-pm' AND isset($ilance->GPC['submit']) AND !empty($ilance->GPC['submit']) AND !isset($ilance->GPC['preview']) AND isset($ilance->GPC['message']) AND !empty($ilance->GPC['message']))
        {
                if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
                {
                        // admin is only one viewing
                        $ilance->GPC['decrypted']['from_id'] = $_SESSION['ilancedata']['user']['userid'];
                        $ilance->GPC['decrypted']['isadmin'] = '1';
                }
                else if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '0')
                {
                        // user viewing
                        $ilance->GPC['decrypted']['from_id'] = $_SESSION['ilancedata']['user']['userid'];
                }
                $message = $ilance->GPC['message'];
                $subject = $ilance->GPC['subject'];
                if($ilconfig['globalfilters_emailfilterpmb'])
                {
                	$subject = strip_email_words($subject);
                	$message = strip_email_words($message);
                }
                if($ilconfig['globalfilters_domainfilterpmb'])
                {
                	$subject = strip_domain_words($subject);
                	$message = strip_domain_words($message);
                }
                // #### COMPOSE NEW PRIVATE MESSAGE ############
                $ilance->pmb->compose_private_message(intval($ilance->GPC['decrypted']['to_id']), $ilance->GPC['from_id'], $subject, $message, $ilance->GPC['decrypted']['project_id'], $ilance->GPC['decrypted']['event_id'], $ilance->GPC['decrypted']['isadmin']);
                if (!empty($ilance->GPC['decrypted']['isadmin']) AND $ilance->GPC['decrypted']['isadmin'] == '1')
                {
                        $ilance->GPC['decrypted']['isadmin'] = '1';
                        $ilance->GPC['decrypted']['from_id'] = $_SESSION['ilancedata']['user']['userid'];
                }
                else
                {
                        $ilance->GPC['decrypted']['isadmin'] = '0';
                        $ilance->GPC['decrypted']['from_id'] = $_SESSION['ilancedata']['user']['userid'];
                }
                $ilance->GPC['decrypted'] = array(
                        'event_id'  => intval($ilance->GPC['decrypted']['event_id']),
                        'project_id' => intval($ilance->GPC['decrypted']['project_id']),
                        'from_id' => intval($ilance->GPC['decrypted']['from_id']),
                        'to_id' => intval($ilance->GPC['decrypted']['to_id']),
                        'isadmin' => $ilance->GPC['decrypted']['isadmin']
                );
                refresh($ilpage['pmb'] . '?crypted=' . encrypt_url($ilance->GPC['decrypted']) . '&amp;noonload=1');
                exit(); 
        }
        // #### PREVIEW PRIVATE MESSAGE ########################################
        else if (isset($cmd) AND $cmd == '_process-pm' AND !isset($ilance->GPC['submit']) AND isset($ilance->GPC['preview']) AND !empty($ilance->GPC['preview']))
        {
                $area_title = '{_posting_private_message}' . ' - {_preview}<div class="smaller">{_to_upper}: <strong>' . fetch_user('username', $ilance->GPC['to_id']) . '</strong></div>';
                $page_title = SITE_NAME . ' - {_posting_private_message} - ' . '{_preview}';
                $pmb['preview_mode'] = true;
                $pmb['previewsubject'] = $pmb['subject'] = '';
                if (isset($ilance->GPC['subject']) AND !empty($ilance->GPC['subject']))
                {
                        $pmb['subject'] = strip_tags($ilance->GPC['subject']);
                        $pmb['previewsubject'] = $pmb['subject'] . '<br /><br />';
                        if ($ilconfig['globalfilters_emailfilterpmb'])
                        {
                        	$pmb['subject'] = strip_email_words($pmb['subject']);
                        }
                        if ($ilconfig['globalfilters_domainfilterpmb'])
                        {
                        	$pmb['subject'] = strip_domain_words($pmb['subject']);
                        }
                }
                // #### PROCESS PREVIEW POST ###################################
                // we assume the user has just posted his message and a preview is being requested
                // we will determine if the wysiwyg editor is enabled before we decide what to do
                if (!empty($ilance->GPC['message']))
                {
                        $message = $ilance->GPC['message'];
                        if($ilconfig['globalfilters_emailfilterpmb'])
                        {
                        	$message = strip_email_words($message);
                        }
                        if($ilconfig['globalfilters_domainfilterpmb'])
                        {
                        	$message = strip_domain_words($message);
                        }
                }  
                else 
                {
                	$message = $ilance->GPC['message'];
                }
                // #### PREVIEW IN HTML ########################################
                // our text is already converted to bbcode so for preview, we will parse it back to html
                $pmb['preview'] = $ilance->bbcode->bbcode_to_html($ilance->GPC['message']);
		// #### RELOAD INTO WYSIWYG ####################################		
		$wysiwyg_area = print_wysiwyg_editor('message', $message, 'bbeditor', $ilconfig['globalfilters_pmbwysiwyg'], $ilconfig['globalfilters_pmbwysiwyg'], false, '590', '120', '', $ilconfig['default_pmb_wysiwyg'], $ilconfig['ckeditor_pmbtoolbar']);
                $ilance->template->load_popup('popupheader', 'popup_header.html');
                $ilance->template->pprint('popupheader', array('headinclude','onload','onbeforeunload','meta_desc','meta_keyw','official_time'));
        }
        else
        {
                $area_title = '{_posting_private_message}' . '<div class="smaller">{_to_upper}: <strong>' . fetch_user('username', $ilance->GPC['decrypted']['to_id']) . '</strong></div>';
                $page_title = SITE_NAME . ' - ' . '{_posting_private_message}';
		$wysiwyg_area = print_wysiwyg_editor('message', '', 'bbeditor', $ilconfig['globalfilters_pmbwysiwyg'], $ilconfig['globalfilters_pmbwysiwyg'], false, '590', '120', '', $ilconfig['default_pmb_wysiwyg'], $ilconfig['ckeditor_pmbtoolbar']);					
                $ilance->template->load_popup('popupheader', 'popup_header.html');
                $ilance->template->pprint('popupheader', array('headinclude','onload','onbeforeunload','meta_desc','meta_keyw','official_time'));
        }
        // admin viewing access to remove pmbs?
        $isadminviewing = 0;
        if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
        {
                $isadminviewing = 1;
        }
        $headinclude .= '
<script type="text/javascript">
<!--
function closePMB(href) 
{
	window.opener.location=href
	window.close();
}
function validate_subject(f)
{
	if (window.document.ilform.subject.value == \'\')
	{
		alert_js(\'Please enter the subject to dispatch this private message.\');
		return(false);
	}
	return (true);
}
function validate_message()
{
	fetch_bbeditor_data();
	return(true);
}

function validate_all()
{	
	return validate_subject() && validate_message();
}
//-->
</script>
';
	$pmb['attachid'] = time();
	$pmbaction = '';
	if (!empty($ilance->GPC['decrypted']['status']) AND $ilance->GPC['decrypted']['status'] == 'archived') 
	{
		$pmbaction = 'disabled';
	}
	if ($res['project_state'] == 'service') 
	{
		$auctiontype = $ilpage['rfp'];
	}
	else if ($res['project_state'] == 'product') 
	{
		$auctiontype = $ilpage['merch'];
	}
	$project_id = isset($ilance->GPC['decrypted']['project_id']) ? intval($ilance->GPC['decrypted']['project_id']) : 0;   
	$from_id = isset($ilance->GPC['decrypted']['from_id']) ? intval($ilance->GPC['decrypted']['from_id']) : 0;
	$to_id = isset($ilance->GPC['decrypted']['to_id']) ? intval($ilance->GPC['decrypted']['to_id']) : 0;  
	$event_id = isset($ilance->GPC['decrypted']['event_id']) ? intval($ilance->GPC['decrypted']['event_id']) : 0;   
	$attachid = isset($pmb['attachid']) ? intval($pmb['attachid']) : 0;  
	$subject = isset($pmb['subject']) ? $pmb['subject'] : '';
	$project_title = stripslashes($res['project_title']);
	if (isset($ilance->GPC['decrypted']['isadmin']) AND $ilance->GPC['decrypted']['isadmin'] == 1)
	{
		$querymessages = $ilance->db->query("
			SELECT alert.id, alert.from_id, alert.to_id, alert.from_status, alert.to_status, alert.isadmin, pm.project_id, pm.event_id, pm.datetime, pm.message, pm.subject
			FROM " . DB_PREFIX . "pmb_alerts as alert,
			" . DB_PREFIX . "pmb as pm
			WHERE alert.id = pm.id
				AND alert.project_id = '" . $ilance->GPC['decrypted']['project_id'] . "'
				AND alert.event_id = '" . $ilance->GPC['decrypted']['event_id'] . "'
				AND alert.event_id = pm.event_id
				AND alert.project_id = pm.project_id
			ORDER BY pm.id DESC
		");
	}
	else
	{
		$querymessages = $ilance->db->query("
			SELECT alert.id, alert.from_id, alert.to_id, alert.from_status, alert.to_status, alert.isadmin, pm.project_id, pm.event_id, pm.datetime, pm.message, pm.subject
			FROM " . DB_PREFIX . "pmb_alerts as alert,
			" . DB_PREFIX . "pmb as pm
			WHERE alert.id = pm.id
				AND (alert.from_id = '" . $ilance->GPC['decrypted']['from_id'] . "' AND alert.to_id = '" . $ilance->GPC['decrypted']['to_id'] . "' OR alert.from_id = '" . $ilance->GPC['decrypted']['to_id'] . "' AND alert.to_id = '" . $ilance->GPC['decrypted']['from_id'] . "')
				AND alert.project_id = '" . $ilance->GPC['decrypted']['project_id'] . "'
				AND alert.event_id = '" . $ilance->GPC['decrypted']['event_id'] . "'
				AND alert.event_id = pm.event_id
				AND alert.project_id = pm.project_id
			ORDER BY pm.id DESC
		");
	}
	if ($ilance->db->num_rows($querymessages) > 0)
	{
		$rows = $item = 0;
		$messages = '';
		while ($resmessages = $ilance->db->fetch_array($querymessages))
		{
			$rows++;
			$item++;
			if (empty($ilance->GPC['decrypted']['isadmin']) OR $ilance->GPC['decrypted']['isadmin'] == 0)
			{
				$ilance->pmb->update_pmb_tracker($resmessages['id'], $_SESSION['ilancedata']['user']['userid']);
			}
			if (!empty($resmessages['subject']) AND $resmessages['subject'] != 'No Subject') 
			{
				$pmb['subject'] = stripslashes($resmessages['subject']);
			}
			else 
			{
				$pmb['subject'] = '';
			}
			$pmb['subject'] = strip_vulgar_words($pmb['subject']);
			if (empty($resmessages['message']))
			{
				$pmb['message'] = '({_no_message_posted})';
			}	
			else
			{
				$pmb['message'] = strip_vulgar_words($resmessages['message']);
				$pmb['message'] = $ilance->bbcode->bbcode_to_html($pmb['message']);
				$pmb['message'] = print_string_wrap($pmb['message'], 100);
				
			}
			$pmb['id'] = $resmessages['id'];
			$pmb['datetime'] = print_date($resmessages['datetime'], $ilconfig['globalserverlocale_globaltimeformat'], 1, 1);
			$pmb['username'] = fetch_user('username', $resmessages['from_id']);
			$pmb['online_status'] = print_online_status($resmessages['from_id']);
			$messages[] = $pmb;
		}
		$show['are_messages'] = true;
	}
	else
	{
		$show['are_messages'] = false;
	}

	$attachment_list = $uploadbutton = '';
	if ($ilconfig['globalfilters_pmbattachments'] > 0) 
	{
		$attachment_list = '';
		if (!empty($_SESSION['ilancedata']['user']['userid']))
		{
			$attachment_list = fetch_inline_attachment_filelist('', $ilance->GPC['decrypted']['project_id'], 'pmb');
		}
			
		$hiddeninput = array(
			'attachtype' => 'pmb',
			'pmb_id' => intval($ilance->GPC['decrypted']['event_id']),
			'project_id' => intval($ilance->GPC['decrypted']['project_id']),
			'user_id' => (!empty($_SESSION['ilancedata']['user']['userid']) ? $_SESSION['ilancedata']['user']['userid'] : '-1'),
			'category_id' => intval($res['cid']),
			'filehash' => md5(time()),
			'max_filesize' => $ilance->permissions->check_access((!empty($_SESSION['ilancedata']['user']['userid']) ? $_SESSION['ilancedata']['user']['userid'] : '-1'), 'uploadlimit')
		);
		if (isset($ilance->GPC['decrypted']['isadmin']) AND $ilance->GPC['decrypted']['isadmin'])
		{
			$uploadbutton = '<div style="padding-top:5px"></div><input name="attachment" onclick=Attach("' . $ilpage['upload'] . '?crypted=' . encrypt_url($hiddeninput) . '") type="button" value="{_upload}" class="buttons" style="font-size:15px" ' . $pmbaction . ' disabled="disabled" />';
		}
		else
		{
			$uploadbutton = '<div style="padding-top:5px"></div><input name="attachment" onclick=Attach("' . $ilpage['upload'] . '?crypted=' . encrypt_url($hiddeninput) . '") type="button" value="{_upload}" class="buttons" style="font-size:15px" ' . $pmbaction . ' />';
		}                                
	}		
	$previewsubject = isset($pmb['previewsubject']) ? $pmb['previewsubject'] : '';
	$preview = isset($pmb['preview']) ? $pmb['preview'] : '';
	$ilance->template->fetch('main', 'pmb.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', 'messages');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', array('project_id','attachid','to_id','from_id','event_id','subject','auctiontype','project_title','tr','attachment_list','uploadbutton','preview','previewsubject','wysiwyg_area','pmbaction','redirect','referer'));   
	$ilance->template->load_popup('popupfooter', 'popup_footer.html');
	$ilance->template->parse_hash('popupheader', array('ilpage' => $ilpage));
	$ilance->template->parse_hash('popupfooter', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('popupheader');
	$ilance->template->parse_if_blocks('popupfooter');
	$ilance->template->pprint('popupfooter', array('finaltime','finalqueries'));
	exit();
}
else 
{
	$area_title = '{_access_denied}';
	$page_title = SITE_NAME . ' - ' . '{_access_denied}';
	$ilance->template->load_popup('popupheader', 'popup_header.html');
	$ilance->template->load_popup('popupmain', 'popup_denied.html');
	$ilance->template->load_popup('popupfooter', 'popup_footer.html');
	$ilance->template->parse_hash('popupmain', array('ilpage' => $ilpage));
	$ilance->template->parse_hash('popupheader', array('ilpage' => $ilpage));
	$ilance->template->parse_hash('popupfooter', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('popupheader');
	$ilance->template->parse_if_blocks('popupmain');
	$ilance->template->parse_if_blocks('popupfooter');
	$ilance->template->pprint('popupheader', array('headinclude','onload','onbeforeunload','meta_desc','meta_keyw','official_time') );
	$ilance->template->pprint('popupmain', array('input_style'));
	$ilance->template->pprint('popupfooter', array('finaltime','finalqueries'));
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>