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
define('LOCATION','attachment');
@ini_set('zlib.output_compression', 'Off');
if (@ini_get('output_handler') == 'ob_gzhandler' AND @ob_get_length() !== false)
{	
	@ob_end_clean();
	header('Content-Encoding:');
}
if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) OR !empty($_SERVER['HTTP_IF_NONE_MATCH']))
{
	$sapi_name = php_sapi_name();
	if ($sapi_name == 'cgi' OR $sapi_name == 'cgi-fcgi')
	{
		header('Status: 304 Not Modified');
	}
	else
	{
		header('HTTP/1.1 304 Not Modified');
	}
	header('Content-Type:');
	header('X-Powered-By:');
	if (!empty($_REQUEST['id']))
	{
		header('Etag: "' . $_REQUEST['id'] . '"');
	}
	exit();
}
require_once('./functions/config.php');

($apihook = $ilance->api('attachment_start')) ? eval($apihook) : false;

if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'captcha')
{
        ($apihook = $ilance->api('attachment_captcha_start')) ? eval($apihook) : false;
        
        $ilance->attachment_tools->print_captcha(8);
}
else
{
        // we don't want the connection activity logging any attachment activity
        define('SKIP_SESSION', true);
}
$ilance->GPC['attachmentid'] = 0;
if (isset($ilance->GPC['crypted']) AND !empty($ilance->GPC['crypted']))
{
	$ilance->GPC['uncrypted'] = decrypt_url($ilance->GPC['crypted']);
	if ($ilance->GPC['uncrypted']['id'] > 0)
	{
		$ilance->GPC['id'] = intval($ilance->GPC['uncrypted']['id']);
	}
}
if (isset($ilance->GPC['id']) AND !empty($ilance->GPC['id']))
{
        if (strlen($ilance->GPC['id']) == 32)
        {
                $ilance->GPC['attachmentid'] = $ilance->GPC['id'];
                $wheresql = "filehash = '" . $ilance->db->escape_string($ilance->GPC['attachmentid']) . "'";
        }
        else
        {
                $ilance->GPC['attachmentid'] = intval($ilance->GPC['id']);
                $wheresql = "attachid = '" . intval($ilance->GPC['attachmentid']) . "'";
        }
}
else
{
        $sapi_name = php_sapi_name();
        if ($sapi_name == 'cgi' OR $sapi_name == 'cgi-fcgi')
        {
                header('Status: 404 Not Found');
        }
        else
        {
                header('HTTP/1.1 404 Not Found');
        }
        exit();
}
$ilance->GPC['cmd'] = isset($ilance->GPC['cmd']) ? $ilance->GPC['cmd'] : '';
$ilance->GPC['subcmd'] = isset($ilance->GPC['subcmd']) ? $ilance->GPC['subcmd'] : '';
$ilance->GPC['original'] = isset($ilance->GPC['original']) ? true : false;
$ilance->db->query("
        UPDATE " . DB_PREFIX . "attachment
        SET counter = counter + 1
        WHERE $wheresql
        LIMIT 1
", 0, null, __FILE__, __LINE__);
$sql = $ilance->db->query("
        SELECT *
        FROM " . DB_PREFIX . "attachment
        WHERE $wheresql
        LIMIT 1
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $attachment = $ilance->db->fetch_array($sql, DB_ASSOC);        
        $attachment['filedata'] = $ilance->attachment_tools->fetch_attachment_rawdata($attachment, $ilance->GPC['original'], $ilance->GPC['subcmd']);
        // thumbnail picture
        if ($ilance->GPC['cmd'] == 'thumb')
        {
                if (!empty($attachment['filetype']))
                {
                        header("Content-type: " . $attachment['filetype']);
                }
                else
                {
                        header("Content-type: unknown/unknown");        
                }
                header('Cache-control: max-age=31536000');
                header('Expires: ' . date("D, d M Y H:i:s", TIMESTAMPNOW + 31536000) . ' GMT');
                header('Last-Modified: ' . date('D, d M Y H:i:s', TIMESTAMPNOW) . ' GMT');
                header('ETag: "' . $ilance->GPC['attachmentid'] . '"');
                header('Content-disposition: inline; filename="' . rawurlencode($attachment['filename']) . '"');
                header('Content-transfer-encoding: binary');
                echo $attachment['filedata'];
		exit();
        }
        // profile picture
        else if ($ilance->GPC['cmd'] == 'profile')
        {
                $im = imagecreatefromstring($attachment['filedata']);
                $width = imagesx($im);
                $height = imagesy($im);
                if ($width > $ilconfig['attachmentlimit_profilemaxwidth'] OR $height > $ilconfig['attachmentlimit_profilemaxheight'])
                {
                        $ratio = ($width / $height);
                        if (($width / $height) > $ratio)
                        {
                                $width = ($height * $ratio);
                        }
                        else
                        {
                                $height = ($width / $ratio);
                        }
                }
                $thumb = @imagecreatetruecolor($width, $height) or die('Cannot Initialize new GD image stream');
                imagecopyresized($thumb, $im, 0, 0, 0, 0, $width, $height, imagesx($im), imagesy($im));
                if (!empty($attachment['filetype']))
                {
                        header('Content-type: ' . $attachment['filetype']);
                }
                else
                {
                        header('Content-type: unknown/unknown');        
                }
                header('Cache-control: max-age=31536000');
                header('Expires: ' . date("D, d M Y H:i:s", TIMESTAMPNOW + 31536000) . ' GMT');
                header('Last-Modified: ' . date('D, d M Y H:i:s', TIMESTAMPNOW) . ' GMT');
                header('ETag: "' . $ilance->GPC['attachmentid'] . '"');
                header('Content-disposition: inline; filename="' . rawurlencode($attachment['filename']) . '"');
                header('Content-transfer-encoding: binary');
                if (imagetypes() & IMG_GIF) 
                {
                        $out = imagegif($thumb);
                }
                else if (imagetypes() & IMG_JPG) 
                {
                        $out = imagejpeg($thumb);
                }
                else if (imagetypes() & IMG_PNG) 
                {
                        $out = imagepng($thumb);
                }
                else if (imagetypes() & IMG_WBMP) 
                {
                        $out = imagewbmp($thumb);
                }
                echo $out;
                imagedestroy($im);
                imagedestroy($thumb);
		exit();
        }
        // portfolio picture
        else if ($ilance->GPC['cmd'] == 'portfolio')
        {
                header('Cache-control: max-age=31536000');
                header('Expires: ' . date("D, d M Y H:i:s", TIMESTAMPNOW + 31536000) . ' GMT');
                header('Last-Modified: ' . date('D, d M Y H:i:s', TIMESTAMPNOW) . ' GMT');
                header('ETag: "' . $ilance->GPC['attachmentid'] . '"');
                header('Content-disposition: inline; filename="' . rawurlencode($attachment['filename']) . '"');
                header('Content-transfer-encoding: binary');
                if (!empty($attachment['filetype']))
                {
                        header('Content-type: ' . $attachment['filetype']);
                }
                else
                {
                        header('Content-type: unknown/unknown');        
                }
                echo $attachment['filedata'];
		exit();
        }
        // everything else (job bid attachments, project proposal attachments, zip files, workspace attachments, etc)
        else
        {
                $canviewattachment = true;
                // is this attachment associated with any auction bids?
                // if so we must check to see if the auction in question is sealed or blind
                // and if this is the case we deny access to the attachment if the viewer
                // does not meet necessary requirements (is not owner, is not uploader or is not admin)
                if ($attachment['project_id'] > 0)
                {
                        // fetch the project owner id
                        $attachment['project_owner_id'] = fetch_project_ownerid($attachment['project_id']);
                        if ($attachment['attachtype'] == 'ws')
                        {
                                // fetch the project winner id
                                $attachment['project_winner_id'] = fetch_project_winnerid($attachment['project_id']);
                                $canviewattachment = false;
                                // does the viewing user have access to download the attachment?
                                if ((!empty($_SESSION['ilancedata']['user']['userid'])
                                        // project owner
                                        AND $_SESSION['ilancedata']['user']['userid'] == $attachment['project_owner_id']
                                        // attachment upload user
                                        OR !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $attachment['user_id']
                                        // awarded winner (product or service)
                                        OR !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $attachment['project_winner_id']
                                        // administrator
                                        OR $_SESSION['ilancedata']['user']['isadmin'] == '1'))
                                {
                                        // attachment is a workspace attachment and we've passed the checkup
                                        // we are most likely the owner or admin viewing
                                        // so we'll allow the attachment to be downloaded
                                        $canviewattachment = true;
                                        if ($ilance->attachment_tools->is_private_workspace_attachment(intval($ilance->GPC['attachmentid'])))
                                        {
                                                // because this attachment is private we should only be
                                                // the uploader or admin!
                                                $canviewattachment = false;
                                                if ((!empty($_SESSION['ilancedata']['user']['userid'])
                                                        // attachment upload user
                                                        AND $_SESSION['ilancedata']['user']['userid'] == $attachment['user_id']
                                                        // administrator
                                                        OR $_SESSION['ilancedata']['user']['isadmin'] == '1'))
                                                {
                                                        $canviewattachment = true;
                                                }
                                        }
                                }
                        }
                        // is this a digital download attachment?
                        else if ($attachment['attachtype'] == 'digital')
                        {
                        	$canviewattachment = false;
                                // fetch the project winner id
                                $attachment['project_winner_id'] = fetch_project_winnerid($attachment['project_id']);
		                if ((!empty($_SESSION['ilancedata']['user']['userid'])
					// project winner
					AND $_SESSION['ilancedata']['user']['userid'] == $attachment['project_winner_id']
					// project owner
					OR !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $attachment['project_owner_id']
					// attachment upload user
					OR !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $attachment['user_id']
					// administrator
					OR $_SESSION['ilancedata']['user']['isadmin'] == '1'))
		                {
		                        $canviewattachment = true;
		                }
                                else 
                                {
                                	$sql_winner = $ilance->db->query("
						SELECT buyer_id
						FROM " . DB_PREFIX . "buynow_orders 
						WHERE project_id = '" . $attachment['project_id'] . "'
							AND (status = 'pending_delivery' OR status = 'delivered' OR status = 'offline_delivered')
						", 0, null, __FILE__, __LINE__);
                                	if ($ilance->db->num_rows($sql_winner) > 0)
	                                {
		                                while ($res_winner = $ilance->db->fetch_array($sql_winner))
		                                {
		                                	$attachment['project_winner_id'] = $res_winner['buyer_id'];
			                                if ((!empty($_SESSION['ilancedata']['user']['userid'])
			                                        // project winner
			                                        AND $_SESSION['ilancedata']['user']['userid'] == $attachment['project_winner_id']
			                                        // project owner
			                                        OR !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $attachment['project_owner_id']
			                                        // attachment upload user
			                                        OR !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $attachment['user_id']
			                                        // administrator
			                                        OR $_SESSION['ilancedata']['user']['isadmin'] == '1'))
			                                {
			                                        $canviewattachment = true;
			                                        break;
			                                }
		                                }
                                	}
                                }
                        }
                        // for everything else (will check if sealed and/or is invite only auction)
                        else
                        {
                                // is this an attachment uploaded by a project owner?
                                if ($attachment['project_owner_id'] > 0)
                                {
                                        // this is an attachment uploaded by a project owner
                                        // we will now check what type of security this auction has such as sealed bids and/or if auction is invite only
                                        // if the auction is invite only the attachments can only be downloaded by the owner, admin and registered invited user(s) (not email invited users)
                                        // is this a sealed or blind bid auction?
                                        if ($ilance->auction_service->is_sealed_auction($attachment['project_id']))
                                        {
                                                $canviewattachment = false;
                                                // auction event with this bid is sealed or blind
                                                // does the viewing user have access to download the attachment?
                                                if ((!empty($_SESSION['ilancedata']['user']['userid'])
							AND $_SESSION['ilancedata']['user']['userid'] == $attachment['user_id']
                                                                OR $_SESSION['ilancedata']['user']['userid'] == $attachment['project_owner_id']
                                                                OR $_SESSION['ilancedata']['user']['isadmin'] == '1'))
                                                {
                                                        // auction is sealed or blind and we've passed the checkup
                                                        // we are most likely the owner or uploader or admin viewing
                                                        // so we'll allow the attachment to be downloaded
                                                        $canviewattachment = true;
                                                }
                                        }
                                        // is this an invite only auction?
                                        else if ($ilance->auction->is_inviteonly_auction($attachment['project_id']))
                                        {
                                                $canviewattachment = false;
                                                $attachment['invitedusers'] = array();
                                                // fetch users invited for this specific auction id
                                                $invited = $ilance->db->query("
                                                        SELECT seller_user_id AS userid
                                                        FROM " . DB_PREFIX . "project_invitations
                                                        WHERE project_id = '" . $attachment['project_id'] . "'
                                                                AND seller_user_id > 0
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($invited) > 0)
                                                {
                                                        while ($resinvited = $ilance->db->fetch_array($invited))
                                                        {
                                                                // build the user invited array to compare against below
                                                                $attachment['invitedusers'][] = $resinvited['userid'];
                                                        }
                                                }
                                                unset($resinvited, $invited);
                                                // does the viewing user have access to download the attachment?
                                                if ((!empty($_SESSION['ilancedata']['user']['userid'])
							AND $_SESSION['ilancedata']['user']['userid'] == $attachment['user_id']
                                                                OR $_SESSION['ilancedata']['user']['userid'] == $attachment['project_owner_id']
                                                                OR $_SESSION['ilancedata']['user']['isadmin'] == '1'
                                                                OR in_array($_SESSION['ilancedata']['user']['userid'], $attachment['invitedusers'])))
                                                {
                                                        // auction is invite only and we've passed the checkup
                                                        // we are most likely the owner, admin or a invited user viewing
                                                        // so we'll allow the attachment to be downloaded
                                                        $canviewattachment = true;
                                                }
                                        }
                                        else
                                        {
                                                // since this bid does not appear to be placed on a sealed or blind or invite only auction we will let the attachment be downloaded
                                                $canviewattachment = true;
                                        }
                                }	
                        }
                }
                if ($canviewattachment)
                {
                        $ext = fetch_extension($attachment['filename']);
                        $isie = iif($ilance->common->is_webbrowser('ie') OR $ilance->common->is_webbrowser('opera'), true, false);
                        $filetype = ($isie) ? 'application/octetstream' : 'application/octet-stream';
                        $attachment['filename'] = ($ilance->common->is_webbrowser('mozilla')) ? "filename*=utf-8''" . rawurlencode($attachment['filename']) : 'filename="' . rawurlencode($attachment['filename']) . '"';
                        header('Content-type: ' . $filetype);
                        header('Cache-control: max-age=31536000');
                        header('Expires: ' . date("D, d M Y H:i:s", TIMESTAMPNOW + 31536000) . ' GMT');
                        header('Last-Modified: ' . date('D, d M Y H:i:s', TIMESTAMPNOW) . ' GMT');
                        header('ETag: "' . $ilance->GPC['attachmentid'] . '"');
                        header('Content-Length: ' . strlen($attachment['filedata']));
                        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                        header('Pragma: public');
                        if (in_array($ext, array('jpg', 'jpe', 'jpeg', 'gif', 'png')))
                        {
                                header('Content-disposition: inline; ' . $attachment['filename']);
                        }
                        else
                        {
                                header('Content-disposition: attachment; ' . $attachment['filename']);
                        }
                        echo $attachment['filedata'];
			exit();
                }
                else
                {
                        $attachment['filename'] = 'attachment_denied.txt';
                        $attachment['filedata'] = file_get_contents(DIR_UPLOADS . $attachment['filename']);
                        $isie = iif($ilance->common->is_webbrowser('ie') OR $ilance->common->is_webbrowser('opera'), true, false);
                        $filetype = ($isie) ? 'application/octetstream' : 'application/octet-stream';
                        $attachment['filename'] = ($ilance->common->is_webbrowser('mozilla')) ? "filename*=utf-8''" . rawurlencode($attachment['filename']) : 'filename="' . rawurlencode($attachment['filename']) . '"';
                        header('Content-type: ' . $filetype);
                        header('Cache-control: max-age=31536000');
                        header('Expires: ' . date("D, d M Y H:i:s", TIMESTAMPNOW + 31536000) . ' GMT');
                        header('Last-Modified: ' . date('D, d M Y H:i:s', TIMESTAMPNOW) . ' GMT');
                        header('ETag: "' . $ilance->GPC['attachmentid'] . '"');
                        header('Content-Length: ' . strlen($attachment['filedata']));
                        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                        header('Pragma: public');
                        header('Content-disposition: attachment; ' . $attachment['filename']);
                        echo $attachment['filedata'];
			exit();
                }        
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>