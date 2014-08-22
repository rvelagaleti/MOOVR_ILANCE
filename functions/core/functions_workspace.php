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
* Workspace functions for iLance
*
* @package      iLance\Global\Workspace
* @version      4.0.0.8059
* @author       ILance
*/

/**
* Function to print fixed string
*
* @param      string      string or content
*
* @return     string      Returns formatted string
*/
function print_fixed_string($str = '')
{
	$str = trim($str);
	$str = str_replace("'", "''", $str);
	$str = str_replace("\'", "'", $str);
	$str = str_replace("", ",", $str);
	$str = str_replace("\\", "", $str);
	$str = str_replace("\"", "&#34;", $str);
	$str = str_replace('\"', '"', $str);
	return $str;
}

/**
* Function to fetch the maximum `id` from a particlar database table
*
* @param      string      database table name
*
* @return     string      Returns maximum `id` from database table
*/
function fetch_max_id($tblName = '')
{
	global $con_id, $ilance;
	$rs = $ilance->db->query("SELECT MAX(id) AS mx FROM $tblName");
        $rr = $ilance->db->fetch_array($rs, DB_ASSOC);
	return $rr['mx'];
}

/**
* Function to insert a valid database record
*
* @param      string      database table name
* @param      array       table field name arrays
*
* @return     string      Returns maximum `id` from database table
*/
function insert_record($strTable, $arrValue)
{
        global $ilance;
	$strQuery = " INSERT INTO $strTable (";
	if (!isset($arrValue))
        {
                $arrValue = array();
        }
	@reset($arrValue);
	while (list ($strKey, $strVal) = each($arrValue))
        {
		$strQuery .= $strKey . ",";
	}
	// remove last comma
	$strQuery = mb_substr($strQuery, 0, mb_strlen($strQuery) - 1);
	$strQuery .= ") VALUES (";
	if (!isset($arrValue))
        {
                $arrValue = array();
        }
	@reset($arrValue);
	while(list ($strKey, $strVal) = each($arrValue))
        {
		$strQuery .= "'" . print_fixed_string($strVal) . "',";
	}
	// remove last comma
	$strQuery = mb_substr($strQuery, 0, mb_strlen($strQuery) - 1);
	$strQuery .= ");";
	$ilance->db->query($strQuery);
	return fetch_max_id($strTable);
}
	
/**
* Function to update a database record
*
* @param      string      database table name
* @param      string      sql where clause
* @param      array       table field name arrays
*
* @return     string      Returns maximum `id` from database table
*/				
function update_record($strTable, $strWhere, $arrValue)
{
        global $ilance;
        $strQuery = "UPDATE $strTable SET ";
	if (!isset($arrValue))
        {
                $arrValue = array();
        }
	@reset($arrValue);
	while (list ($strKey, $strVal) = each($arrValue))
        {
		$strQuery .= $strKey . "= '" . print_fixed_string($strVal) . "',";
	}
	$strQuery = mb_substr($strQuery, 0, mb_strlen($strQuery) - 1);
	$strQuery .= " WHERE " . $strWhere;
	$ilance->db->query($strQuery);
}

function fetch_field_count($tbl, $criteria)
{
	global $con_id, $ilance;
	if (!empty($criteria))
        {
		$where = "WHERE $criteria";
	}
	$query = "SELECT COUNT(*) AS cnt FROM $tbl $where";
	$result = $ilance->db->query($query);
	$res = $ilance->db->fetch_array($result, DB_ASSOC);
	return $res['cnt'];
}

function construct_attachment_folder($folder_name = '', $folder_comments = '', $folder_parent = -1, $folder_project = 0, $user_id = 0, $folder_buyer = 0, $folder_seller = 0, $folder_size = 0, $folder_type = 2)
{
	$folderId = insert_record(DB_PREFIX . "attachment_folder", array(
		'name' => $folder_name,
		'comments' => $folder_comments,
		'p_id' => $folder_parent,
		'project_id' => $folder_project,
		'user_id' => $user_id,
		'buyer_id' => $folder_buyer,
		'seller_id' => $folder_seller,
		'folder_size' => $folder_size,
		'folder_type' => $folder_type,
		'create_date' => DATETODAY)
	);
	return $folderId;
}

function fetch_folder_parent_id($folderid = 0)
{
	global $ilance, $ilmedia, $ilpage;
	$parentid = 0;
	$rs = $ilance->db->query("
		SELECT p_id
		FROM " . DB_PREFIX . "attachment_folder
		WHERE id = '" . intval($folderid) . "'
	", 0, null, __FILE__, __LINE__);
	while ($rows = $ilance->db->fetch_array($rs, DB_ASSOC))
	{
		$parentid = $rows['p_id'];
	}
	if ($parentid == '-1')
	{
		$parentid = 0;
	}
	return $parentid;
}

function print_current_folder_title($fold_id, $parentid = 0, $strString)
{
	global $ilance, $ilmedia, $ilpage, $ilcrumbs, $project_title, $cryptedws, $uncrypted, $cond, $navcrumb, $itemurl;
	if ($fold_id == '-1' OR empty($fold_id))
	{
		return '';
	}
	$rs = $ilance->db->query("
		SELECT p_id, name
		FROM " . DB_PREFIX . "attachment_folder
		WHERE id = '" . intval($fold_id) . "'
	");
	while ($rows = $ilance->db->fetch_array($rs, DB_ASSOC))
	{
		$strName = $rows['name'];
		if ($rows['p_id'] == '-1')
		{
			$crypted = array(
                                'project_id' => $ilmedia['project_id'],
                                'buyer_id' => $ilmedia['buyer_id'],
                                'seller_id' => $ilmedia['seller_id'],
                                'fold_id' => $fold_id,
				'returnurl' => $uncrypted['returnurl'],
				'cond' => $cond,
                                'p_id' => $rows['p_id']
                        );
			$strString = '<span class="blue"><a href="' . $ilpage['workspace'] . '?crypted=' . encrypt_url($crypted) . '">' . handle_input_keywords($strName) . '</a></span> &gt; ' . $strString;
			$navcrumb[$ilpage['workspace'] . '?crypted=' . encrypt_url($crypted)] = handle_input_keywords($strName);
			return $strString;
		}
		else
		{
			$crypted = array(
				'project_id' => $ilmedia['project_id'],
				'buyer_id' => $ilmedia['buyer_id'],
				'seller_id' => $ilmedia['seller_id'],
				'fold_id' => $fold_id,
				'returnurl' => $uncrypted['returnurl'],
				'cond' => $cond,
				'p_id' => $rows['p_id']
			);
			$strString = '<span class="blue"><a href="' . $ilpage['workspace'] . '?crypted=' . encrypt_url($crypted) .'">' . handle_input_keywords($strName) . '</a></span> &gt; ' . $strString;
			$strString = print_current_folder_title($rows['p_id'], $fold_id, $strString);
			$navcrumb[$ilpage['workspace'] . '?crypted=' . encrypt_url($crypted)] = handle_input_keywords($strName);
			return $strString;
		}
	}
}

function print_hidden_input_folders()
{
	global $arrFolder;
	$html = '';
	for ($i = 0; $i < count($arrFolder); $i++)
        {
		$html .= '<input type="hidden" name="fold' . $arrFolder["$i"] . '" value="on" />';
	}
	return $html;
}

function print_hidden_input_files()
{
	global $arrFiles;
	$html = '';
	for ($i = 0; $i < count($arrFiles); $i++)
        {
		$html .= '<input type="hidden" name="file' . $arrFiles["$i"] . '" value="on" />';
	}
	return $html;
}

function print_arrow($nNo)
{
	$html = '';
	for ($i = 0; $i < $nNo; $i++)
	{
		$html .= "&nbsp;&nbsp;&nbsp;&nbsp;";
	}
	return $html;
}

function print_folder_option_values($root_id, $cfold_id)
{
	global $ilance, $gPath;
	if ($root_id == $cfold_id)
        {
		//return 0;
	}
	$html = '';
	$rs = $ilance->db->query("
                SELECT id, name
                FROM " . DB_PREFIX . "attachment_folder
                WHERE p_id = '" . intval($root_id) . "'
        ", 0, null, __FILE__, __LINE__);
	while ($rows = $ilance->db->fetch_array($rs, DB_ASSOC))
        {
		$html .= '<option value="' . $rows['id'] . '">' . print_arrow($gPath) . '&nbsp;' . stripslashes(handle_input_keywords($rows['name'])) . '</option>';
		if (fetch_field_count(DB_PREFIX . "attachment_folder", "p_id = " . $rows['id']) > 0)
                {
			$gPath++;
			$html .= print_folder_option_values($rows['id'], $cfold_id);
		}
	}
	$gPath--;
	return $html;
}

function construct_date($strDate)
{
	global $phrase;
        if (empty($strDate))
        {
                return '{_never}';
        }
	$strTemp = explode(' ', $strDate);
	$strTemp = explode('-', $strTemp[0]);
	$strDate = $strTemp[2] . '-' . $strTemp[1] . '-' . $strTemp[0];
	return $strDate;			
}

function copy_files($nFold = 0, $newFolderId = 0)
{
	global $ilmedia, $ilance;
	$query = $ilance->db->query("
		SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, filedata, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
		FROM " . DB_PREFIX . "attachment
		WHERE tblfolder_ref = '" . intval($nFold) . "'
	", 0, null, __FILE__, __LINE__);
	while ($row = $ilance->db->fetch_array($query, DB_ASSOC))
	{
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "attachment
			(attachid, attachtype, user_id, project_id, date, filename, filedata, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref)
			VALUES(
			NULL,
			'ws',
			'" . $_SESSION['ilancedata']['user']['userid'] . "',
			'" . intval($ilmedia['project_id']) . "',
			'" . DATETIME24H . "',
			'" . addslashes($row['filename']) . "',
			'" . addslashes($row['filedata']) . "',
			'" . addslashes($row['filetype']) . "',
			'1',
			'0',
			'" . $row['filesize'] . "',
			'" . $row['filehash'] . "',
			'" . addslashes($_SERVER['REMOTE_ADDR']) . "',
			'" . intval($newFolderId) . "')
		", 0, null, __FILE__, __LINE__);
		
		$nSize = $ilance->db->fetch_field(DB_PREFIX . "attachment_folder", "id = '" . intval($newFolderId) . "'", "folder_size") + $row['filesize'];
		update_record(DB_PREFIX . "attachment_folder", "id = '" . intval($newFolderId) . "'", array('folder_size' => $nSize,));
	}
	return true;
}

function copy_folders_recursive($nFold, $newParent)
{
	global $ilance;
	$query = $ilance->db->query("
		SELECT id, name, p_id, project_id, user_id, buyer_id, seller_id, folder_size, folder_type, create_date
		FROM " . DB_PREFIX . "attachment_folder
		WHERE p_id = '" . intval($nFold) . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($query) > 0)
	{
		while ($row = $ilance->db->fetch_array($query, DB_ASSOC))
		{
			$created_folder_id = construct_attachment_folder($row['name'], $row['comments'], $newParent, $row['project_id'], $row['user_id'], $row['buyer_id'], $row['seller_id'], $row['folder_size'], $row['folder_type']);
			copy_files($row['id'], $created_folder_id);
			if (fetch_field_count(DB_PREFIX . "attachment_folder", "p_id = '" . $row['id'] . "'") > 0)
			{
				copy_folders_recursive($row['id'], $created_folder_id);
			}
		}					
	}
	return true;
}

function delete_folders_recursive($nFold)
{
        global $ilance, $ilconfig;
        $query = $ilance->db->query("
		SELECT id
		FROM " . DB_PREFIX . "attachment_folder
		WHERE p_id = '" . intval($nFold) . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($query) > 0)
        {
                while ($row = $ilance->db->fetch_array($query, DB_ASSOC))
                {
			$count = fetch_field_count(DB_PREFIX . "attachment_folder", "p_id = '" . $row['id'] . "'");
			if ($count > 0)
                        {
				delete_folders_recursive($row['id']);
			}
			$ilance->db->query("
				DELETE FROM " . DB_PREFIX . "attachment_folder
				WHERE id = '" . $row['id'] . "'
			", 0, null, __FILE__, __LINE__);
			$query2 = $ilance->db->query("
				SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
				FROM " . DB_PREFIX . "attachment
				WHERE tblfolder_ref = '" . $row['id'] . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($query2) > 0)
                        {
				while ($row2 = $ilance->db->fetch_array($query2, DB_ASSOC))
                                {
					$ilance->db->query("
						DELETE FROM " . DB_PREFIX . "attachment
						WHERE attachid = '" . $row2['attachid'] . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilconfig['attachment_dbstorage'] == 0)
                                        {
						@unlink(DIR_WS_ATTACHMENTS . $row2['filehash'] . '.attach');
					}
				}
			}
		}
	}
}

function check_private_folder($sellerid = 0, $buyerid = 0, $project_id = 0)
{
	global $ilance, $ilmedia;
	if ($project_id <= 0)
	{
		return false;
	}
	if ($sellerid > 0 AND $sellerid == $_SESSION['ilancedata']['user']['userid'])
	{
		$value = fetch_field_count(DB_PREFIX . "attachment_folder", "project_id = '" . intval($project_id) . "' AND seller_id = '" . intval($sellerid) . "' AND buyer_id = '0' AND p_id = '-1' AND folder_type = '1'");
		if (empty($value))
		{
			// create private folder for seller
			construct_attachment_folder("Private", "Private folder not accessible to the buyer.", -1, intval($project_id), intval($sellerid), 0, intval($sellerid), 0, 1);
		}
	}
	if ($buyerid > 0 AND $buyerid == $_SESSION['ilancedata']['user']['userid'])
	{
		$value = fetch_field_count(DB_PREFIX . "attachment_folder", "project_id = '" . intval($project_id) . "' AND buyer_id = '" . intval($buyerid) . "' AND seller_id = '0' AND p_id = '-1' AND folder_type = '1'");
		if (empty($value))
		{
			// create private folder for buyer
			construct_attachment_folder("Private", "Private folder not accessible to the seller.", -1, intval($project_id), intval($buyerid), intval($buyerid), 0, 0, 1);
		}
	}
	return true;
}

function check_shared_folder($sellerid = 0, $buyerid = 0, $project_id = 0)
{
	global $ilance, $ilmedia;
	if ($project_id <= 0)
	{
		return false;
	}
	$value = fetch_field_count(DB_PREFIX . "attachment_folder", "project_id = '" . intval($project_id) . "' AND folder_type = '2'");
	if (empty($value))
	{
		// create shared folder for a specific auction
		construct_attachment_folder("Shared", "Shared folder accessible to the buyer and seller.", -1, intval($project_id), -1, intval($buyerid), intval($sellerid), 0, 2);
	}
	return true;
}

function showFolder($fold_id, $disFlag)
{
	global $ilance, $ilconfig, $ilmedia, $uncrypted, $arrFolder, $ilpage, $nfoldId;
	$html = '';
	$query = $ilance->db->query("
		SELECT id, name, p_id, folder_type, comments, user_id
		FROM " . DB_PREFIX . "attachment_folder
		WHERE p_id = '" . intval($fold_id) . "'
			AND project_id = '" . intval($uncrypted['project_id']) . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($query) > 0)
	{
		while ($row = $ilance->db->fetch_array($query, DB_ASSOC))
		{
			$temp = 'fold' . $row['id'];
			if ($disFlag == 1)
			{
				$html .= (in_array($row['id'], $arrFolder))
					? '<tr><td width="10"></td><td width="10"><input type="checkbox" name="' . $temp . '" disabled="disabled" checked="checked" /></td>'
					: '<tr><td width="10"></td><td width="10"><input type="checkbox" name="' . $temp . '" disabled="disabled" /></td>';
			}
			else
			{
				$html .= '<tr><td width="10"></td><td width="10"><input type="checkbox" name="' . $temp . '" /></td>';
			}
			$cryptedws = array(
				'project_id' => $ilmedia['project_id'],
				'buyer_id' => $ilmedia['buyer_id'],
				'seller_id' => $ilmedia['seller_id'],
				'fold_id' => $row['id'],
				'returnurl' => $uncrypted['returnurl'],
				'nfoldId' => $nfoldId,
				'p_id' => $row['p_id']
			);
			if ($row['folder_type'] == '2')
			{
				$icon = 'folder.gif';
			}
			else
			{
				$icon = 'rfolder.gif';
			}
			$html .= '<td width="25" align="center"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . $icon . '" width="16" height="13"></td><td><span class="blue" title="' . handle_input_keywords($row['comments']) . '"><a href="' . $ilpage['workspace'] . '?crypted=' . encrypt_url($cryptedws) . '">' . stripslashes(handle_input_keywords($row['name'])) . '</a></span> <span class="smaller litegray">(created by ' . fetch_user('username', $row['user_id']) . ')</span></td></tr><tr><td height="1" colspan="4"></td></tr>';
		}
	}
	return $html;
}

function showFiles($fold_id, $disFlag)
{
	global $ilance, $ilmedia, $arrFiles, $ilconfig, $ilpage;
	$html = '';
	$query = $ilance->db->query("
		SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
		FROM " . DB_PREFIX . "attachment
		WHERE tblfolder_ref = '" . intval($fold_id) . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($query) > 0)
	{
		while ($row = $ilance->db->fetch_array($query, DB_ASSOC))
		{
			$temp = 'file' . $row['attachid'];
			if ($disFlag == 1)
			{
				$html .= (in_array($row['attachid'], $arrFiles))
					? '<tr><td width="12"></td><td width="10"><input type="checkbox" name="' . $temp . '" disabled="disabled" checked="checked" /></td>'
					: '<tr><td width="12"></td><td width="10"><input type="checkbox" name="' . $temp . '" disabled="disabled" /></td>';
			}
			else
			{
				$html .= '<tr><td width="12"></td><td width="10"><input type="checkbox" name="' . $temp . '" /></td>';
			}
			$attachextension = fetch_extension($row['filename']) . '.gif';
			$attachextension = (file_exists(DIR_SERVER_ROOT . $ilconfig['template_imagesfolder'] . 'icons/' . $attachextension))
				? fetch_extension($row['filename']) . '.gif'
				: 'attach.gif';
			$html .= '<td width="25" align="center"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/' . $attachextension . '" width="16" height="16" /></td><td><span class="blue"><a href="' . HTTP_SERVER . $ilpage['attachment'] . '?id=' . $row['filehash'] . '" class="top_link" target="_new">' . $row['filename'] . '</a></span> <span class="smaller black">(' . print_filesize($row['filesize']) . ')</span> <span class="smaller litegray">(upload by ' . fetch_user('username', $row['user_id']) . ')</span></td></tr><tr><td colspan="4"></td></tr>';
		}
	}
	return $html;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>