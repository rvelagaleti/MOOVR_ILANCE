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
		'tabfx',
		'jquery',
		'modal'
	),
	'footer' => array(
		'v4',
		'tooltip',
		'autocomplete',
		'cron'
	)
);

// #### setup script location ##################################################
define('LOCATION','workspace');

// #### require backend ########################################################
require_once('./functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[workspace]" => $ilcrumbs["$ilpage[workspace]"]);

// #### handle encrypted url variables #########################################
$uncrypted = (!empty($ilance->GPC['crypted'])) ? decrypt_url($ilance->GPC['crypted']) : array();

if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] <= 0)
{
	refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode(HTTPS_SERVER . $ilpage['workspace'] . print_hidden_fields(true, array(), true)));
	exit();
}

// #### require share backend ##################################################
require_once(DIR_CORE . 'functions_workspace.php');
$ilance->workspace = construct_object('api.workspace');

// #### user attachment gauge ##################################################
$attachmentgauge = $ilance->workspace->print_attachment_gauge($_SESSION['ilancedata']['user']['userid']);

// #### upload files ###########################################################
if (isset($ilance->GPC['doupload']) AND $ilance->GPC['doupload'])
{
	
	$ilmedia['project_state'] = fetch_auction('project_state', $uncrypted['project_id']);
	
	if (!empty($_FILES['ufile']['size']) AND $_FILES['ufile']['size'] > $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'uploadlimit'))
	{
		// filesize exceeds upload permission limit
		$crypted = array(
			'project_id' => intval($uncrypted['project_id']),
			'buyer_id' => intval($uncrypted['buyer_id']),
			'seller_id' => intval($uncrypted['seller_id']),
			'fold_id' => intval($uncrypted['fold_id']),
			'returnurl' => $uncrypted['returnurl'],
			'error_code' => '3'
		);
		header("Location: " . $ilpage['workspace'] . "?crypted=" . encrypt_url($crypted));
		exit();
	}
	
	// check if the total attachment limit for this subscription group has exceeded for this users upload
	$futuresize = $_FILES['ufile']['size'];
	$sqlsum = $ilance->db->query("
		SELECT SUM(filesize) AS attach_usage_total
		FROM " . DB_PREFIX . "attachment 
		WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
	");
	if ($ilance->db->num_rows($sqlsum) > 0)
	{
		$res_file_sum = $ilance->db->fetch_array($sqlsum, DB_ASSOC);
		$futuresize = ($res_file_sum['attach_usage_total'] + $_FILES['ufile']['size']);
	}
	
	$totalpercentage = round(($futuresize / $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'attachlimit')) * 100);
	if ($totalpercentage > 100)
	{
		// we've gone over our subscription plan limit do not let the upload continue
		$crypted = array(
			'project_id' => intval($uncrypted['project_id']),
			'buyer_id' => intval($uncrypted['buyer_id']),
			'seller_id' => intval($uncrypted['seller_id']),
			'fold_id' => intval($uncrypted['fold_id']),
			'returnurl' => $uncrypted['returnurl'],
			'error_code' => '3'
		);
		header("Location: " . $ilpage['workspace'] . "?crypted=" . encrypt_url($crypted));
		exit();
	}
	else 
	{
		$filehash = md5(uniqid(microtime()));
		$filetype = $_FILES['ufile']['type'];
		$newfilename = DIR_WS_ATTACHMENTS . $filehash . '.attach';
		
		if (is_uploaded_file($_FILES['ufile']['tmp_name'])) 
		{
			if (move_uploaded_file($_FILES['ufile']['tmp_name'], $newfilename))
			{
				$upload_file_size = @filesize($newfilename);
				$filedata = '';
				if ($ilconfig['attachment_dbstorage'])
				{
					// we are storing media in the database let's remove the uploaded file
					// and store the guts of the file in $filedata
					$filedata = addslashes(fread(fopen($newfilename, 'rb'), filesize($newfilename)));
					@unlink($newfilename);
				}
				
				// construct the attachment within the database
				$ilance->db->query("
					INSERT INTO " . DB_PREFIX . "attachment
					(attachid, attachtype, user_id, project_id, category_id, date, filename, filedata, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref)
					VALUES(
					NULL,
					'ws',
					'" . intval($_SESSION['ilancedata']['user']['userid']) . "',
					'" . intval($uncrypted['project_id']) . "',
					'0',
					'" . DATETIME24H . "',
					'" . $ilance->db->escape_string($_FILES['ufile']['name']) . "',
					'" . $filedata . "',
					'" . $ilance->db->escape_string($_FILES['ufile']['type']) . "',
					'" . $ilconfig['attachment_mediasharemoderationdisabled'] . "',
					'0',
					'" . $ilance->db->escape_string($_FILES['ufile']['size']) . "',
					'" . $ilance->db->escape_string($filehash) . "',
					'" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
					'" . intval($uncrypted['fold_id']) . "')
				");
				
				$fileId = $ilance->db->insert_id();
				$nSize = $ilance->db->fetch_field(DB_PREFIX . "attachment_folder", "id = '" . intval($uncrypted['fold_id']) . "'", "folder_size");
				$nSize = ($nSize + $_FILES['ufile']['size']);
				update_record(DB_PREFIX . "attachment_folder", "id = '" . intval($uncrypted['fold_id']) . "'", array('folder_size' => $nSize,));
				
				// dispatch email
				
				if ($ilconfig['attachment_mediasharemoderationdisabled'] == 0)
				{
					if (isset($ilance->GPC['sendmail']) AND $ilance->GPC['sendmail'] AND $ilance->db->fetch_field(DB_PREFIX . "attachment_folder", "id = '" . intval($uncrypted['fold_id']) . "'", "folder_type") == '2')
					{
						if ($_SESSION['ilancedata']['user']['userid'] == $uncrypted['seller_id'])
						{
							$ilance->email->mail = fetch_user('email', $uncrypted['buyer_id']);
							$ilance->email->slng = fetch_user_slng($uncrypted['buyer_id']);
						}
						else
						{
							$ilance->email->mail = fetch_user('email', $uncrypted['seller_id']);
							$ilance->email->slng = fetch_user_slng($uncrypted['seller_id']);
						}
						
						$ilance->email->get('mediashare_pickup_alert');		
						$ilance->email->set(array(
							'{{username}}' => $_SESSION['ilancedata']['user']['username'],
							'{{filename}}' => $_FILES['ufile']['name'],					  
							'{{filesize}}' => $_FILES['ufile']['size'],
							'{{filetype}}' => $_FILES['ufile']['type'],
							'{{project_title}}' => fetch_auction('project_title', $uncrypted['project_id']),
						));
						$ilance->email->send();
					}
					
					$ilance->email->mail = SITE_EMAIL;
					$ilance->email->slng = fetch_site_slng();
					$ilance->email->get('mediashare_pickup_alert_admin');		
					$ilance->email->set(array(
						'{{username}}' => $_SESSION['ilancedata']['user']['username'],
						'{{filename}}' => $_FILES['ufile']['name'],					  
						'{{filesize}}' => $_FILES['ufile']['size'],
						'{{filetype}}' => $_FILES['ufile']['type'],
						'{{project_title}}' => fetch_auction('project_title', $uncrypted['project_id']),
					));
					$ilance->email->send();
				}
				else 
				{
					// email uploader
					$ilance->email->mail = $_SESSION['ilancedata']['user']['email'];
					$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
					$ilance->email->get('mediashare_pickup_alert_nomoderation');		
					$ilance->email->set(array(
						'{{username}}' => $_SESSION['ilancedata']['user']['username'],
						'{{filename}}' => $_FILES['ufile']['name'],					  
						'{{filesize}}' => $_FILES['ufile']['size'],
						'{{filetype}}' => $_FILES['ufile']['type'],
						'{{project_title}}' => fetch_auction('project_title', $uncrypted['project_id']),
					));
					$ilance->email->send();
					
					if ($_SESSION['ilancedata']['user']['userid'] == $uncrypted['seller_id'])
					{
						$receiver = fetch_user('username', $uncrypted['buyer_id']);
					}
					else
					{
						$receiver = fetch_user('username', $uncrypted['seller_id']);
					}
					
					// email admin
					$ilance->email->mail = SITE_EMAIL;
					$ilance->email->slng = fetch_site_slng();
					$ilance->email->get('mediashare_pickup_alert_admin');		
					$ilance->email->set(array(
						'{{username}}' => $_SESSION['ilancedata']['user']['username'],
						'{{receiver}}' => $receiver,
						'{{sender}}' => $_SESSION['ilancedata']['user']['username'],
						'{{filename}}' => $_FILES['ufile']['name'],					  
						'{{filesize}}' => $_FILES['ufile']['size'],
						'{{filetype}}' => $_FILES['ufile']['type'],
						'{{project_title}}' => fetch_auction('project_title', $uncrypted['project_id']),
					));
					$ilance->email->send();
					
					if (isset($ilance->GPC['sendmail']) AND $ilance->GPC['sendmail'] AND $ilance->db->fetch_field(DB_PREFIX . "attachment_folder", "id = '" . intval($uncrypted['fold_id']) . "'", "folder_type") == '2') 
					{
						// email receiver (if uploader decides to allow that)
						if ($_SESSION['ilancedata']['user']['userid'] == $uncrypted['seller_id'])
						{
							$username = fetch_user('username', $uncrypted['buyer_id']);
							$ilance->email->mail = fetch_user('email', $uncrypted['buyer_id']);
							$ilance->email->slng = fetch_user_slng($uncrypted['buyer_id']);
						}
						else
						{
							$username = fetch_user('username', $uncrypted['seller_id']);
							$ilance->email->mail = fetch_user('email', $uncrypted['seller_id']);
							$ilance->email->slng = fetch_user_slng($uncrypted['seller_id']);
						}
						
						$ilance->email->get('mediashare_pickup_alert_receiver');		
						$ilance->email->set(array(
							'{{username}}' => $username,
							'{{sender}}' => $_SESSION['ilancedata']['user']['username'],
							'{{filename}}' => $_FILES['ufile']['name'],					  
							'{{filesize}}' => $_FILES['ufile']['size'],
							'{{filetype}}' => $_FILES['ufile']['type'],
							'{{project_title}}' => fetch_auction('project_title', $uncrypted['project_id']),
						));
						$ilance->email->send();
					}
				}
				
				$crypted = array(
					'project_id' => intval($uncrypted['project_id']),
					'buyer_id' => intval($uncrypted['buyer_id']),
					'seller_id' => intval($uncrypted['seller_id']),
					'fold_id' => intval($uncrypted['fold_id']),
					'returnurl' => $uncrypted['returnurl']
				);
				header("Location: " . $ilpage['workspace'] . "?crypted=" . encrypt_url($crypted));
				exit();
			}
			else
			{
				$crypted = array(
					'project_id' => intval($uncrypted['project_id']),
					'buyer_id' => intval($uncrypted['buyer_id']),
					'seller_id' => intval($uncrypted['seller_id']),
					'fold_id' => intval($uncrypted['fold_id']),
					'returnurl' => $uncrypted['returnurl'],
					'error_code' => '3'
				);
				header("Location: " . $ilpage['workspace'] . "?crypted=" . encrypt_url($crypted));
				exit();
			}
		}
		else 
		{
			// temp file was not uploaded
			$crypted = array(
				'project_id' => intval($uncrypted['project_id']),
				'buyer_id' => intval($uncrypted['buyer_id']),
				'seller_id' => intval($uncrypted['seller_id']),
				'fold_id' => intval($uncrypted['fold_id']),
				'returnurl' => $uncrypted['returnurl'],
				'error_code' => '3'
			);
			header("Location: " . $ilpage['workspace'] . "?crypted=" . encrypt_url($crypted));	
			exit();
		}
	}
}
// #### move files or folders ##################################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_move-file-folder')
{
	if (isset($ilance->GPC['newFolderId']) AND $ilance->GPC['newFolderId'] > 0)
	{
		$rs = $ilance->db->query("
			SELECT id, folder_type
			FROM " . DB_PREFIX . "attachment_folder
			WHERE project_id = '" . intval($uncrypted['project_id']) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($rs) > 0)
		{
			while ($row = $ilance->db->fetch_array($rs, DB_ASSOC))
			{
				$temp = 'fold' . $row['id'];
				if (!empty($ilance->GPC[$temp]) AND $ilance->GPC[$temp] == 'on')
				{
					$folder_type = $ilance->db->fetch_field(DB_PREFIX . "attachment_folder", "id = '" . intval($ilance->GPC['newFolderId']) . "'", "folder_type");
					if ($folder_type == '1')
					{
						// seller moving folder into private
						if ($_SESSION['ilancedata']['user']['userid'] == $uncrypted['seller_id'])
						{
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "attachment_folder
								SET p_id = '" . intval($ilance->GPC['newFolderId']) . "',
								buyer_id = '0',
								seller_id = '" . $_SESSION['ilancedata']['user']['userid'] . "',
								folder_type = '1'
								WHERE id = '" . $row['id'] . "'
							", 0, null, __FILE__, __LINE__);
						}
						
						// buyer moving folder into private
						else
						{
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "attachment_folder
								SET p_id = '" . intval($ilance->GPC['newFolderId']) . "',
								buyer_id = '" . $_SESSION['ilancedata']['user']['userid'] . "',
								seller_id = '0',
								folder_type = '1'
								WHERE id = '" . $row['id'] . "'
							", 0, null, __FILE__, __LINE__);
						}
					}
					else if ($folder_type == '2')
					{
						// user folder into shared
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "attachment_folder
							SET p_id = '" . intval($ilance->GPC['newFolderId']) . "',
							buyer_id = '" . intval($uncrypted['buyer_id']) . "',
							seller_id = '" . intval($uncrypted['seller_id']) . "',
							folder_type = '2'
							WHERE id = '" . $row['id'] . "'
						", 0, null, __FILE__, __LINE__);
					}
				}
			}
			
			$rs = $ilance->db->query("
				SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
				FROM " . DB_PREFIX . "attachment
				WHERE project_id = '" . intval($uncrypted['project_id']) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($rs) > 0)
			{
				while ($row = $ilance->db->fetch_array($rs, DB_ASSOC))
				{
					$temp = 'file' . $row['attachid'];
					if (!empty($ilance->GPC[$temp]) AND $ilance->GPC[$temp] == 'on')
					{
						$nSize = $ilance->db->fetch_field(DB_PREFIX . "attachment_folder", "id = '" . $row["tblfolder_ref"] . "'", "folder_size") - $row['filesize'];
						update_record(DB_PREFIX . "attachment_folder", "id = '" . $row["tblfolder_ref"] . "'", array("folder_size" => $nSize,));
						
						$nSize = $ilance->db->fetch_field(DB_PREFIX . "attachment_folder", "id = '" . intval($ilance->GPC['newFolderId']) . "'", "folder_size") + $row['filesize'];
						update_record(DB_PREFIX . "attachment_folder", "id = '" . intval($ilance->GPC['newFolderId']) . "'", array("folder_size" => $nSize,));
						update_record(DB_PREFIX . "attachment", "attachid = '" . $row["attachid"] . "'", array("tblfolder_ref" => intval($ilance->GPC['newFolderId']),));
					}
				}					
			}
		}
		
		if (isset($ilance->GPC['movetofolder']) AND $ilance->GPC['movetofolder'])
		{
			$cryptedws = array(
				'project_id' => $ilmedia['project_id'],
				'buyer_id' => $ilmedia['buyer_id'],
				'seller_id' => $ilmedia['seller_id'],
				'fold_id' => intval($ilance->GPC['newFolderId']),
				'returnurl' => $uncrypted['returnurl'],
				'nfoldId' => $nfoldId,
				'p_id' => $row['p_id']
			);
			//header("Location: " .  $ilpage['workspace'] . "?crypted=" . encrypt_url($cryptedws));
			//exit();
		}
	}
	
	$cryptedws = array(
		'project_id' => intval($uncrypted['project_id']),
		'buyer_id' => intval($uncrypted['buyer_id']),
		'seller_id' => intval($uncrypted['seller_id']),
		'fold_id' => intval($uncrypted['fold_id']),
		'returnurl' => $uncrypted['returnurl']
	);
	header("Location: " .  $ilpage['workspace'] . "?crypted=" . encrypt_url($cryptedws));
	exit();
}

// #### save folder comments ###################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_save-comments')
{
	update_record(DB_PREFIX . "attachment_folder", "id = '" . $uncrypted['nfoldId'] . "'", array('comments' => addslashes($ilance->GPC['folder_comments']),));
	header("Location: " . $ilpage['workspace'] . "?crypted=" . encrypt_url($uncrypted));
	exit();
}

// #### create and save new folder #############################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_save-folder')
{
	$ilance->GPC['folder_comments'] = isset($ilance->GPC['folder_comments']) ? $ilance->GPC['folder_comments'] : '';
	$ilance->GPC['folder_name'] = isset($ilance->GPC['folder_name']) ? $ilance->GPC['folder_name'] : '{_new_folder}';
	
	$folder_type = $ilance->db->fetch_field(DB_PREFIX . "attachment_folder", "id = '" . $uncrypted['fold_id'] . "'", "folder_type");
	if ($folder_type == '1')
	{
		if ($uncrypted['seller_id'] == $_SESSION['ilancedata']['user']['userid'])
		{
			construct_attachment_folder($ilance->GPC['folder_name'], $ilance->GPC['folder_comments'], $uncrypted['fold_id'], $uncrypted['project_id'], $_SESSION['ilancedata']['user']['userid'], 0, $uncrypted['seller_id'], 0, 1);
		}
		else
		{
			construct_attachment_folder($ilance->GPC['folder_name'], $ilance->GPC['folder_comments'], $uncrypted['fold_id'], $uncrypted['project_id'], $_SESSION['ilancedata']['user']['userid'], $uncrypted['buyer_id'], 0, 0, 1);
		}
		
	}
	else if ($folder_type == '2')
	{
		construct_attachment_folder($ilance->GPC['folder_name'], $ilance->GPC['folder_comments'], $uncrypted['fold_id'], $uncrypted['project_id'], $_SESSION['ilancedata']['user']['userid'], $uncrypted['buyer_id'], $uncrypted['seller_id'], 0, 2);
	}
	
	$cryptedws = array(
		'project_id' => $uncrypted['project_id'],
		'buyer_id' => $uncrypted['buyer_id'],
		'seller_id' => $uncrypted['seller_id'],
		'fold_id' => $uncrypted['fold_id'],
		'returnurl' => $uncrypted['returnurl']
	);
	header("Location: " . $ilpage['workspace'] . "?crypted=" . encrypt_url($cryptedws));
	exit();
}

// #### copy files from one folder to another ##################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_save-copy-files')
{
	if (isset($ilance->GPC['newFolderId']) AND $ilance->GPC['newFolderId'] > 0)
	{
		$query = $ilance->db->query("
			SELECT id, name, p_id, project_id, user_id, buyer_id, seller_id, folder_size, create_date
			FROM " . DB_PREFIX . "attachment_folder
			WHERE project_id = '" . intval($uncrypted['project_id']) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($query) > 0)
		{
			while ($row = $ilance->db->fetch_array($query, DB_ASSOC))
			{
				$foldtemp = 'fold' . $row['id'];
				if (!empty($ilance->GPC[$foldtemp]) AND $ilance->GPC[$foldtemp] == 'on')
				{
					$created_folder_id = construct_attachment_folder($row['name'], $row['comments'], $ilance->GPC['newFolderId'], $row['project_id'], $_SESSION['ilancedata']['user']['userid'], $row['buyer_id'], $row['seller_id'], $row['folder_size'], $row['folder_type']);
					copy_files($row['id'], $created_folder_id);
					copy_folders_recursive($row['id'], $created_folder_id);
				}
			}
		}
		$query = $ilance->db->query("
			SELECT attachid, filename, filedata, filetype, filesize, filehash, 
			FROM " . DB_PREFIX . "attachment
			WHERE project_id = '" . intval($uncrypted['project_id']) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($query) > 0)
		{
			while ($row = $ilance->db->fetch_array($query, DB_ASSOC))
			{
				$filetemp = 'file' . $row['attachid'];
				if (!empty($ilance->GPC[$filetemp]) AND $ilance->GPC[$filetemp] == 'on')
				{
					$ilance->db->query("
						INSERT INTO " . DB_PREFIX . "attachment
						(attachid, attachtype, user_id, project_id, date, filename, filedata, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref)
						VALUES(
						NULL,
						'ws',
						'" . intval($_SESSION['ilancedata']['user']['userid']) . "',
						'" . intval($uncrypted['project_id']) . "',
						'" . DATETIME24H . "',
						'" . $ilance->db->escape_string($row['filename']) . "',
						'" . $ilance->db->escape_string($row['filedata']) . "',
						'" . $ilance->db->escape_string($row['filetype']) . "',
						'1',
						'0',
						'" . $ilance->db->escape_string($row['filesize']) . "',
						'" . $ilance->db->escape_string($row['filehash']) . "',
						'" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
						'" . intval($ilance->GPC['newFolderId']) . "')
					", 0, null, __FILE__, __LINE__);
					$nSize = $ilance->db->fetch_field(DB_PREFIX . "attachment_folder", "id = '" . intval($ilance->GPC['newFolderId']) . "'", "folder_size") + $row['filesize'];
					update_record(DB_PREFIX . "attachment_folder", "id = '" . intval($ilance->GPC['newFolderId']) . "'", array('folder_size' => $nSize,));
				}
			}
		}
	}
	$cryptedws = array(
		'project_id' => intval($uncrypted['project_id']),
		'buyer_id' => intval($uncrypted['buyer_id']),
		'seller_id' => intval($uncrypted['seller_id']),
		'fold_id' => intval($uncrypted['fold_id']),
		'returnurl' => $uncrypted['returnurl']
	);
	header("Location: " . $ilpage['workspace'] . "?crypted=" . encrypt_url($cryptedws));
	exit();
}

// #### rename folder ##########################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_save-rename' AND !empty($ilance->GPC['re_name']))
{
	// #### we are renaming a folder #######################################
	if (!empty($ilance->GPC['rfoldId']))
	{
		update_record(DB_PREFIX . "attachment_folder", "id=" . intval($ilance->GPC['rfoldId']), array('name' => addslashes($ilance->GPC['re_name']),));
	}
	
	// #### we are renaming a file #########################################
	else
	{ 
		update_record(DB_PREFIX . "attachment", "attachid=" . intval($ilance->GPC['nfile_id']), array('filename' => addslashes($ilance->GPC['re_name']),));
	}
	
	$cryptedws = array(
		'project_id' => intval($uncrypted['project_id']),
		'buyer_id' => intval($uncrypted['buyer_id']),
		'seller_id' => intval($uncrypted['seller_id']),
		'fold_id' => intval($uncrypted['fold_id']),
		'returnurl' => $uncrypted['returnurl']
	);
	header("Location: " . $ilpage['workspace'] . "?crypted=" . encrypt_url($cryptedws));
	exit();
}


// #### workspace landing page #################################################
else
{
	$area_title = '{_workspace}';
	$page_title = SITE_NAME . ' - {_workspace}';
	
	$show['doupload'] = $show['donewfolder'] = $show['docomment'] = $show['dodelete'] = $show['dorename'] = $show['domove'] = $show['docopy'] = false;
	$currentfolder = $filesfolders = $defaultfilesfolders = $foldername = $lastmodified = $foldersize = $foldercomment = $comments = $old_name = $folder_pulldown = $hiddenfieldsmove = $sendtype = '';
	$fold_id = $file_id = $p_id = $icounter = $disFlag = $oneFileCheck = $nfoldId = 0;
	$arrFolder = $arrFiles = array();
	
	if (!empty($uncrypted['fold_id']))
	{
		$fold_id = $uncrypted['fold_id'];
	}
	
	if (!empty($uncrypted['file_id']))
	{
		$file_id = $uncrypted['file_id'];
	}
	
	if (!empty($uncrypted['p_id']))
	{
		$p_id = $uncrypted['p_id'];
	}
	
	if (isset($uncrypted['error_code'])) 
	{
		$error_code = $uncrypted['error_code'];
	}
	
	$ilmedia['project_id'] = isset($uncrypted['project_id']) ? intval($uncrypted['project_id']) : 0;
	$ilmedia['buyer_id'] = isset($uncrypted['buyer_id']) ? intval($uncrypted['buyer_id']) : 0;
	$ilmedia['seller_id'] = isset($uncrypted['seller_id']) ? intval($uncrypted['seller_id']) : 0;
	$ilmedia['fold_id'] = isset($uncrypted['fold_id']) ? intval($uncrypted['fold_id']) : 0;
	$ilmedia['p_id'] = isset($uncrypted['p_id']) ? intval($uncrypted['p_id']) : 0;
	
	$project_title = fetch_auction('project_title', $ilmedia['project_id']);
	$project_state = fetch_auction('project_state', $ilmedia['project_id']);
	if ($project_state == 'product')
	{
		$itemurl = $ilpage['merch'];
	}
	else
	{
		$itemurl = $ilpage['rfp'];
	}
	
	$cryptedws = array(
		'project_id' => $ilmedia['project_id'],
		'buyer_id' => $ilmedia['buyer_id'],
		'seller_id' => $ilmedia['seller_id'],
		'returnurl' => $uncrypted['returnurl']
	);
	
	if ($_SESSION['ilancedata']['user']['userid'] == $ilmedia['seller_id'])
	{
		$navcrumb = array("$ilpage[main]?cmd=cp" => "{_my_cp}", "$uncrypted[returnurl]" => "{_selling_activity}", "$itemurl?id=$ilmedia[project_id]" => handle_input_keywords($project_title), "$ilpage[workspace]?crypted=" . encrypt_url($cryptedws) => $ilcrumbs["$ilpage[workspace]"]);
		$cond = "seller_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'";
	}
	else
	{
		$navcrumb = array("$ilpage[main]?cmd=cp" => "{_my_cp}", "$uncrypted[returnurl]" => "{_buying_activity}", "$itemurl?id=$ilmedia[project_id]" => handle_input_keywords($project_title), "$ilpage[workspace]?crypted=" . encrypt_url($cryptedws) => $ilcrumbs["$ilpage[workspace]"]);
		$cond = "buyer_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'";
	}
	
	if ($fold_id > 0)
	{
		$uncrypted['p_id'] = fetch_folder_parent_id($fold_id);
		$p_id = $uncrypted['p_id'];
		$currentfolder = '&gt;&nbsp;' . mb_substr(print_current_folder_title($fold_id, $p_id, ''), 0, -6);
		
		$rs = $ilance->db->query("
			SELECT id
			FROM " . DB_PREFIX . "attachment_folder
			WHERE project_id = '" . intval($ilmedia['project_id']) . "'
		", 0, null, __FILE__, __LINE__);
		while ($row = $ilance->db->fetch_array($rs, DB_ASSOC))
		{
			$temp = 'fold' . $row['id']; 
			if (isset($ilance->GPC[$temp]) AND $ilance->GPC[$temp] == 'on')
			{
				$arrFolder[$icounter] = $row['id'];
				$icounter++;
			}
		}
		
		$icounter = 0;
		$query = $ilance->db->query("
			SELECT attachid
			FROM " . DB_PREFIX . "attachment
			WHERE project_id = '" . intval($ilmedia['project_id']) . "'
		", 0, null, __FILE__, __LINE__);
		while ($row = $ilance->db->fetch_array($query, DB_ASSOC))
		{
			$temp = 'file' . $row['attachid'];
			if (isset($ilance->GPC[$temp]) AND $ilance->GPC[$temp] == 'on')
			{
				$arrFiles[$icounter] = $row['attachid'];
				$icounter++;
			}
		}
		
		if (isset($ilance->GPC['newfolder']))
		{
			$show['donewfolder'] = true;
			$disFlag = 1;
		}
		else if (isset($ilance->GPC['upload']))
		{
			$show['doupload'] = true;
			$disFlag = 1;
			
			$folder_type = $ilance->db->fetch_field(DB_PREFIX . "attachment_folder", "id = '" . intval($fold_id) . "'", "folder_type");
			if ($_SESSION['ilancedata']['user']['userid'] == $ilmedia['seller_id'])
			{
				$sendtype = '{_buyer}';
			}
			else
			{
				$sendtype = '{_seller}';
			}
		}
		else if (isset($ilance->GPC['comment']))
		{
			$show['docomment'] = true;
			include(DIR_CORE . 'functions_workspace_check.php');
			$comments = $ilance->db->fetch_field(DB_PREFIX . "attachment_folder", "id = '" . intval($nfoldId) . "'", "comments");
			$disFlag = 1;
		}
		else if (isset($ilance->GPC['delete']))
		{
			$show['dodelete'] = true;
			$oneFileCheck = 1;
			include(DIR_CORE . 'functions_workspace_check.php');
			
			// #### remove selected folders ########################
			$query = $ilance->db->query("
				SELECT id
				FROM " . DB_PREFIX . "attachment_folder
				WHERE project_id = '" . intval($ilmedia['project_id']) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($query) > 0)
			{
				while ($row = $ilance->db->fetch_array($query, DB_ASSOC))
				{
					$temp = 'fold' . $row['id'];
					if (!empty($ilance->GPC[$temp]) AND $ilance->GPC[$temp] == 'on')
					{
						$query2 = $ilance->db->query("
							SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
							FROM " . DB_PREFIX . "attachment
							WHERE tblfolder_ref = '" . $row['id'] . "'
								AND project_id = '" . intval($ilmedia['project_id']) . "'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($query2) > 0)
						{
							while ($row2 = $ilance->db->fetch_array($query2, DB_ASSOC))
							{
								$ilance->db->query("
									DELETE FROM " . DB_PREFIX . "attachment
									WHERE attachid = '" . $row2['attachid'] . "'
										AND project_id = '" . intval($ilmedia['project_id']) . "'
								", 0, null, __FILE__, __LINE__);
								if ($ilconfig['attachment_dbstorage'] == 0)
								{
									@unlink(DIR_WS_ATTACHMENTS . $row2['filehash'] . '.attach');
								}
							}
						}
						delete_folders_recursive($row['id']);
						
						$ilance->db->query("
							DELETE FROM " . DB_PREFIX . "attachment_folder
							WHERE id = '" . $row['id'] . "'
								AND project_id = '" . intval($ilmedia['project_id']) . "'
						", 0, null, __FILE__, __LINE__);
					}
				}
			}
			
			// #### remove selected files ##########################
			$query3 = $ilance->db->query("
				SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
				FROM " . DB_PREFIX . "attachment
				WHERE project_id = '" . intval($ilmedia['project_id']) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($query3) > 0)
			{
				while ($row3 = $ilance->db->fetch_array($query3, DB_ASSOC))
				{
					$temp = 'file' . $row3['attachid'];
					if (!empty($ilance->GPC[$temp]) AND $ilance->GPC[$temp] == 'on')
					{
						$query2 = $ilance->db->query("
							DELETE FROM " . DB_PREFIX . "attachment
							WHERE attachid = '" . $row3['attachid'] . "'
								AND project_id = '" . intval($ilmedia['project_id']) . "'
						", 0, null, __FILE__, __LINE__);
						if ($ilconfig['attachment_dbstorage'] == 0)
						{
							@unlink(DIR_WS_ATTACHMENTS . $row3['filehash'] . '.attach');
						}
						
						$nSize = $ilance->db->fetch_field(DB_PREFIX . "attachment_folder", "id = '" . $row3['tblfolder_ref'] . "' AND project_id = '" . intval($ilmedia['project_id']) . "'", "folder_size");
						$nSize = ($nSize - $row3['filesize']);
						if ($nSize < 0)
						{
							$nSize = 0;
						}
						update_record(DB_PREFIX . "attachment_folder", "id = '" . $row3['tblfolder_ref'] . "' AND project_id = '" . intval($ilmedia['project_id']) . "'", array('folder_size' => $nSize,));
					}
				}
			}
		}
		else if (isset($ilance->GPC['rename']))
		{
			$show['dorename'] = true;
			include(DIR_CORE . 'functions_workspace_check.php');
			$disFlag = 1;
			
			if (!empty($nfoldId))
			{
				$old_name = handle_input_keywords($ilance->db->fetch_field(DB_PREFIX . "attachment_folder", "id = '" . intval($nfoldId) . "'", "name"));
			}
			else if (!empty($nfile_id))
			{
				$old_name = trim($ilance->db->fetch_field(DB_PREFIX . "attachment", "attachid = '" . intval($nfile_id) . "'", "filename"));
			}
			
			$nfile_id = isset($nfile_id) ? intval($nfile_id) : 0;
		}
		else if (isset($ilance->GPC['moveitems']))
		{
			$show['domove'] = true;
			$oneFileCheck = 1;
			include(DIR_CORE . 'functions_workspace_check.php');
			$disFlag = 1;
			
			$hiddenfieldsmove = print_hidden_input_folders() . print_hidden_input_files();
			
			$pRootId = $ilance->db->fetch_field(DB_PREFIX . "attachment_folder", "$cond AND project_id = '" . intval($uncrypted['project_id']) . "' AND folder_type = '1' AND p_id = '-1'", "id");
			$pRootName = $ilance->db->fetch_field(DB_PREFIX . "attachment_folder", "$cond AND project_id = '" . intval($uncrypted['project_id']) . "' AND folder_type = '1' AND p_id = '-1'", "name");
			$sRootId = $ilance->db->fetch_field(DB_PREFIX . "attachment_folder", "project_id = '" . intval($uncrypted['project_id']) . "' AND folder_type = '2' AND p_id = '-1'", "id");
			$sRootName = $ilance->db->fetch_field(DB_PREFIX . "attachment_folder", "project_id = '" . intval($uncrypted['project_id']) . "' AND folder_type = '2' AND p_id = '-1'", "name");
			
			$folder_pulldown = '<select name="newFolderId" style="font-family: verdana" id="newFolderId"><option value="' . $pRootId . '">' . $pRootName . '</option>';
			
			$gPath = 1;
			$html = '';
			$folder_pulldown .= print_folder_option_values($pRootId, $uncrypted['fold_id']) . '<option value="' . $sRootId . '">' . $sRootName . '</option>';
			
			$gPath = 1;
			$html = '';
			$folder_pulldown .= print_folder_option_values($sRootId, $uncrypted['fold_id']) . '</select>';
		}
		else if (isset($ilance->GPC['copyitems']))
		{
			$show['docopy'] = true;
			$oneFileCheck = 1;
			include(DIR_CORE . 'functions_workspace_check.php');
			$disFlag = 1;
			
			$hiddenfieldscopy = print_hidden_input_folders() . print_hidden_input_files();
			
			$pRootId = $ilance->db->fetch_field(DB_PREFIX . "attachment_folder", "$cond AND project_id = '" . intval($uncrypted['project_id']) . "' AND folder_type = '1' AND p_id = '-1'", "id");
			$pRootName = $ilance->db->fetch_field(DB_PREFIX . "attachment_folder", "$cond AND project_id = '" . intval($uncrypted['project_id']) . "' AND folder_type = '1' AND p_id = '-1'", "name");
			$sRootId = $ilance->db->fetch_field(DB_PREFIX . "attachment_folder", "project_id = '" . intval($uncrypted['project_id']) . "' AND folder_type = '2' AND p_id = '-1'", "id");
			$sRootName = $ilance->db->fetch_field(DB_PREFIX . "attachment_folder", "project_id = '" . intval($uncrypted['project_id']) . "' AND folder_type = '2' AND p_id = '-1'", "name");
			
			$folder_pulldown = '<select name="newFolderId" style="font-family: verdana" id="newFolderId"><option value="' . $pRootId . '">' . $pRootName . '</option>';
			
			$gPath = 1;
			$html = '';
			$folder_pulldown .= print_folder_option_values($pRootId, $uncrypted['fold_id']) . '<option value="' . $sRootId . '">' . $sRootName . '</option>';
			
			$gPath = 1;
			$html = '';
			$folder_pulldown .= print_folder_option_values($sRootId, $uncrypted['fold_id']) . '</select>';
		}
		
		$foldername = handle_input_keywords($ilance->db->fetch_field(DB_PREFIX . "attachment_folder", "id = '" . intval($fold_id) . "'", "name"));
		$lastmodified = construct_date($ilance->db->fetch_field(DB_PREFIX . "attachment_folder", "id = '" . intval($fold_id) . "'", "create_date"));
		$foldersize = print_filesize($ilance->db->fetch_field(DB_PREFIX . "attachment_folder", "id = '" . intval($fold_id) . "'", "folder_size"));
		$foldercomment = handle_input_keywords($ilance->db->fetch_field(DB_PREFIX."attachment_folder", "id = '" . intval($fold_id) . "'", "comments"));
	}
	
	$filesfolders = showFolder($fold_id, $disFlag) . showFiles($fold_id, $disFlag);
	
	$cryptedws = array(
		'project_id' => $ilmedia['project_id'],
		'buyer_id' => $ilmedia['buyer_id'],
		'seller_id' => $ilmedia['seller_id'],
		'fold_id' => $ilmedia['fold_id'],
		'returnurl' => $uncrypted['returnurl'],
		'nfoldId' => intval($nfoldId),
		'p_id' => $ilmedia['p_id']
	);
	$encodedws = serialize($cryptedws);
	
	// form hidden input variable
	$cryptedws = encrypt_url($cryptedws);
	
	// home folder navigation link
	$cryptedhome = array(
		'project_id' => $ilmedia['project_id'],
		'buyer_id' => $ilmedia['buyer_id'],
		'seller_id' => $ilmedia['seller_id'],
		'returnurl' => $uncrypted['returnurl']
	);
	$cryptedhome = encrypt_url($cryptedhome);
	
	$buyer_id = $ilmedia['buyer_id'];
	$seller_id = $ilmedia['seller_id'];
	$project_id = $ilmedia['project_id'];
	
	if (!empty($fold_id) AND $p_id != '-1')
	{
		if (!isset($file_id))
		{
			$file_id = 0;
		}
	    
		$cryptedws = array(
			'project_id' => $ilmedia['project_id'],
			'buyer_id' => $ilmedia['buyer_id'],
			'seller_id' => $ilmedia['seller_id'],
			'fold_id' => $fold_id,
			'returnurl' => $uncrypted['returnurl'],
			'nfoldId' => intval($nfoldId),
			'file_id' => $file_id
		);

		// form hidden input variable
		$cryptedws = encrypt_url($cryptedws);
	}
	
	$errormessage = '';
	if (isset($error_code) AND !empty($error_code)) 
	{
		switch ($error_code) 
		{
			case 1:
			{
				$errormessage = '{_you_can_use_this_feature_when_you_have_selected_one_or_more_checkboxes}';
				break;
			}
			case 2:
			{
				$errormessage = '{_you_can_only_select_one_checkbox_at_a_time_for_the_current_action}';
				break;
			}
			case 3:
			{
				$errormessage = '{_the_uploaded_filesize_is_more_than_your_account_limit_or_maximum_storage_space_exceeds_your_limit_please_reupload}';
				break;
			}
			case 4:
			{
				$errormessage = 'You can only leave comments on folders';
				break;
			}
		}
	}
        	
	// check our folders (create if need be)
	check_private_folder($ilmedia['seller_id'], $ilmedia['buyer_id'], $ilmedia['project_id']);
	check_shared_folder($ilmedia['seller_id'], $ilmedia['buyer_id'], $ilmedia['project_id']);
	
	$cryptedwsprivate = array(
		'project_id' => $ilmedia['project_id'],
		'buyer_id' => $ilmedia['buyer_id'],
		'seller_id' => $ilmedia['seller_id'],
		'fold_id' => $ilance->db->fetch_field(DB_PREFIX . "attachment_folder", "project_id = '" . intval($ilmedia['project_id']) . "' AND folder_type = '1' AND $cond AND p_id = '-1'", 'id'),
		'returnurl' => $uncrypted['returnurl'],
		'nfoldId' => intval($nfoldId),
		'cond' => $cond
	);
	$cryptedwsshared = array(
		'project_id' => $ilmedia['project_id'],
		'buyer_id' => $ilmedia['buyer_id'],
		'seller_id' => $ilmedia['seller_id'],
		'fold_id' => $ilance->db->fetch_field(DB_PREFIX . "attachment_folder", "project_id = '" . intval($ilmedia['project_id']) . "' AND folder_type = '2' AND $cond AND p_id = '-1'", 'id'),
		'returnurl' => $uncrypted['returnurl'],
		'nfoldId' => intval($nfoldId),
		'cond' => $cond
	);
	$cryptedwsprivate = encrypt_url($cryptedwsprivate);
	$cryptedwsshared = encrypt_url($cryptedwsshared);

	$pprint_array = array('sendtype','hiddenfieldscopy','hiddenfieldsmove','folder_pulldown','nfoldId','old_name','nfile_id','comments','foldercomment','foldersize','lastmodified','foldername','attachmentgauge','filesfolders','currentfolder','cryptedwsshared','cryptedwsprivate','fold_id','file_id','seller_id','project_id','buyer_id','cryptedws','cryptedhome','errormessage','php_self','project_title','prevnext','redirect','referer', 'keyw');

	$ilance->template->fetch('main', 'workspace.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>