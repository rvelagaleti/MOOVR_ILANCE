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

$count = $nfoldId = $nfile_id = 0;
$query = $ilance->db->query("
	SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
	FROM " . DB_PREFIX . "attachment
	WHERE project_id = '" . intval($uncrypted['project_id']) . "'	
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($query) > 0)
{
	while ($row = $ilance->db->fetch_array($query, DB_ASSOC))
	{
		$temp = 'file' . $row['attachid'];
		if (!empty($ilance->GPC[$temp]) AND $ilance->GPC[$temp] == 'on')
		{
			$nfile_id = $row['attachid'];
			$count++;
		}
	}
}
$query2 = $ilance->db->query("
	SELECT id, name
	FROM " . DB_PREFIX . "attachment_folder
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($query2) > 0) 
{
	while ($row2 = $ilance->db->fetch_array($query2, DB_ASSOC))
	{
		$temp = 'fold' . $row2['id'];
		if (!empty($ilance->GPC[$temp]) AND $ilance->GPC[$temp] == 'on')
		{
			$nfoldId = $row2['id'];
			$count++;
		}
	}
}
if ($show['docomment'])
{
	if ($nfile_id > 0)
	{
		$cryptedws = array(
			'project_id' => $ilmedia['project_id'],
			'buyer_id' => $ilmedia['buyer_id'],
			'seller_id' => $ilmedia['seller_id'],
			'fold_id' => $ilmedia['fold_id'],
			'returnurl' => $uncrypted['returnurl'],
			'nfoldId' => $nfoldId,
			'nfile_id' => $nfile_id,
			'error_code' => '4'
		);
		header("Location: " . $ilpage['workspace'] . "?crypted=" . encrypt_url($cryptedws));
		exit();
	}
}
if (isset($oneFileCheck) AND $oneFileCheck == 1)
{
	if (isset($count) AND $count < 1)
	{
		$cryptedws = array(
			'project_id' => $ilmedia['project_id'],
			'buyer_id' => $ilmedia['buyer_id'],
			'seller_id' => $ilmedia['seller_id'],
			'fold_id' => $ilmedia['fold_id'],
			'returnurl' => $uncrypted['returnurl'],
			'nfoldId' => $nfoldId,
			'nfile_id' => $nfile_id,
			'error_code' => '1'
		);
		header("Location: " . $ilpage['workspace'] . "?crypted=" . encrypt_url($cryptedws));
		exit();
	}
}
else
{
	if (isset($count) AND $count > 1 OR isset($count) AND $count < 1)
	{
		$cryptedws = array(
			'project_id' => $ilmedia['project_id'],
			'buyer_id' => $ilmedia['buyer_id'],
			'seller_id' => $ilmedia['seller_id'],
			'fold_id' => $ilmedia['fold_id'],
			'returnurl' => $uncrypted['returnurl'],
			'nfoldId' => $nfoldId,
			'nfile_id' => $nfile_id,
			'error_code' => '1'
		);
		header("Location: " . $ilpage['workspace'] . "?crypted=" . encrypt_url($cryptedws));
		exit();
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>