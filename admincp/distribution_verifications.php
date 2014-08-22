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

if (!defined('LOCATION') OR defined('LOCATION') != 'admin')
{
        die('<strong>Fatal:</strong> This script cannot be parsed indirectly.');
}
$area_title = '{_credential_verification_management}';
$page_title = SITE_NAME . ' - {_credential_verification_management}';

($apihook = $ilance->api('admincp_verification_management')) ? eval($apihook) : false;

$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'] . '?cmd=verifications', $_SESSION['ilancedata']['user']['slng']);
// #### VERIFICATION MANAGE ############################################
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'verification-manage' AND !empty($ilance->GPC['answerid']))
{
	foreach ($ilance->GPC['answerid'] as $value)
	{
		if (isset($ilance->GPC['delete']) AND !empty($value))
		{
			$ilance->db->query("
				DELETE FROM " . DB_PREFIX . "profile_answers
				WHERE answerid = '" . intval($value) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
		}
	}
	
	print_action_success('{_credential_verifications_selected_were_successfully_deleted_from_the_datastore}', $ilance->GPC['return']);
	exit();
}
// #### VERIFY CREDENTIAL FOR X DAYS ###################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'verify-credential' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	$expiry = date('Y-m-d H:i:s', (TIMESTAMPNOW + $ilconfig['verificationlength']*24*3600));
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "profile_answers
		SET isverified = '1',
		visible = '1',
		verifyexpiry = '" . $expiry . "'
		WHERE answerid = '" . intval($ilance->GPC['id']) . "'
		LIMIT 1
	", 0, null, __FILE__, __LINE__);
	print_action_success('{_credential_verification_was_successfully_verified}', $ilpage['distribution'] . '?cmd=verifications&amp;page='.intval($ilance->GPC['page']));
	exit();
}
// #### UN VERIFY CREDENTIAL ###########################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'unverify-credential' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "profile_answers
		SET isverified = '0',
		verifyexpiry = '0000-00-00 00:00:00'
		WHERE answerid = '" . intval($ilance->GPC['id']) . "'
		LIMIT 1
	", 0, null, __FILE__, __LINE__);
	print_action_success('{_credential_verification_details_were_successfully_unverified_an_icon}', $ilpage['distribution'] . '?cmd=verifications&amp;page='.(int)$ilance->GPC['page']);
	exit();
}
$sql1 = $sql2 = $sql3 = $isverified1 = $isverified2 = $isverified3 = $answerid = $user_id = $orderby1 = $orderby2 = '';
$number = $row_count = 0;
$status = 'total';
$sqlorderby = 'ORDER BY answers.answerid DESC';
$orderby2 = 'checked="checked"';
$show['no_verifications'] = true;
$ilance->GPC['isverified'] = isset($ilance->GPC['isverified']) ? intval($ilance->GPC['isverified']) : 0;
if ($ilance->GPC['isverified'] == '0')
{
	$sql3 = "AND answers.isverified = '0'";
}
else if ($ilance->GPC['isverified'] == '1')
{
	$sql3 = "AND answers.isverified = '1'";
}
if ($ilance->GPC['isverified'] == '-1')
{
	$isverified1 = 'selected="selected"';
}
else if ($ilance->GPC['isverified'] == '1')
{
	$isverified2 = 'selected="selected"';
	$status = 'paid';
}
else if ($ilance->GPC['isverified'] == '0')
{
	$isverified3 = 'selected="selected"';
	$status = 'pending';
}
// #### SEARCH MODE ####################################################
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'search')
{
	if (isset($ilance->GPC['answerid']) AND $ilance->GPC['answerid'] > 0)
	{
		$sql1 = "AND answers.answerid = '" . intval($ilance->GPC['answerid']) . "'";
		$answerid = intval($ilance->GPC['answerid']);
	}
	if (isset($ilance->GPC['user_id']) AND $ilance->GPC['user_id'] > 0)
	{
		$sql2 = "AND answers.user_id = '" . intval($ilance->GPC['user_id']) . "'";
		$user_id = intval($ilance->GPC['user_id']);
	}
}
$ilance->GPC['orderby'] = isset($ilance->GPC['orderby']) ? $ilance->GPC['orderby'] : 'DESC';
if (isset($ilance->GPC['orderby']) AND !empty($ilance->GPC['orderby']))
{
	if ($ilance->GPC['orderby'] == 'DESC')
	{
		$sqlorderby = 'ORDER BY answers.answerid DESC';
		$orderby1 = '';
		$orderby2 = 'checked="checked"';
	}
	else
	{
		$sqlorderby = 'ORDER BY answers.answerid ASC';
		$orderby1 = 'checked="checked"';
		$orderby2 = '';
	}
}
$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
$limit = ' LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
$sql = $ilance->db->query("
	SELECT answers.answerid, answers.questionid, answers.user_id, answers.answer, answers.date, answers.visible, answers.isverified, answers.invoiceid, answers.contactname, answers.contactnumber, answers.contactnotes, answers.verifyexpiry, questions.question, questions.verifycost
	FROM " . DB_PREFIX . "profile_answers AS answers,
	" . DB_PREFIX . "profile_questions AS questions
	WHERE answers.questionid = questions.questionid
	    AND answers.answer != ''
	    AND answers.contactname != ''
	    AND answers.contactnumber != ''
	    AND answers.contactnotes != ''
	    $sql1
	    $sql2
	    $sql3
	    $sqlorderby
	    $limit
", 0, null, __FILE__, __LINE__);
$sqltemp = $ilance->db->query("
	SELECT answers.answerid, answers.questionid, answers.user_id, answers.answer, answers.date, answers.visible, answers.isverified, answers.invoiceid, answers.contactname, answers.contactnumber, answers.contactnotes, answers.verifyexpiry, questions.question, questions.verifycost
	FROM " . DB_PREFIX . "profile_answers AS answers,
	" . DB_PREFIX . "profile_questions AS questions
	WHERE answers.questionid = questions.questionid
	    AND answers.answer != ''
	    AND answers.contactname != ''
	    AND answers.contactnumber != ''
	    AND answers.contactnotes != ''
	    $sql1
	    $sql2
	    $sql3
	    $sqlorderby
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
	$show['no_verifications'] = false;
	$number = $ilance->db->num_rows($sqltemp);
	while ($res = $ilance->db->fetch_array($sql))
	{
		$res['username'] = fetch_user('username', $res['user_id']);
		$res['email'] = fetch_user('email', $res['user_id']);
		switch ($res['isverified'])
		{
			case '0':
			{
				$res['verified'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'not_verified.gif" alt="' . '{_not_verified}' . '" border="0" />';
				$res['actions'] = '<span class="blue"><a href="' . $ilpage['distribution'] . '?cmd=verifications&amp;subcmd=verify-credential&amp;id='.$res['answerid'].'&amp;page='.intval($ilance->GPC['page']).'" onclick="return confirm_js(\'' . '{_please_take_a_moment_to_confirm_your_action}' . '\')">' . '{_verify}' . '</a></span>';
				break;
			}                                    
			case '1':
			{
				$res['verified'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'verified_icon.gif" alt="' . '{_verified}' . '" border="0" />';
				$res['actions'] = '<span class="red"><a href="' . $ilpage['distribution'] . '?cmd=verifications&amp;subcmd=unverify-credential&amp;id='.$res['answerid'].'&amp;page='.intval($ilance->GPC['page']).'" onclick="return confirm_js(\'' . '{_please_take_a_moment_to_confirm_your_action}' . '\')"><span style="color:red">' . '{_unverify}' . '</span></a></span>';
				break;
			}
		}
		$sqlinv = $ilance->db->query("
			SELECT status
			FROM " . DB_PREFIX . "invoices
			WHERE invoiceid = '" . $res['invoiceid'] . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sqlinv) > 0)
		{
			$resinv = $ilance->db->fetch_array($sqlinv);
			if ($resinv['status'] == 'paid')
			{
				$res['payout'] = '{_yes}';
			}
			else
			{
				$res['payout'] = '{_no}';
			}
		}
		else
		{
			$res['payout'] = '-';
		}
		if ($res['verifyexpiry'] == '0000-00-00 00:00:00')
		{
			$res['verifyexpiry'] = '-';
		}
		$res['answer'] = stripslashes(nl2br($res['answer']));
		$res['contactname'] = stripslashes($res['contactname']);
		$res['contactnumber'] = stripslashes($res['contactnumber']);
		$res['contactnotes'] = stripslashes(nl2br($res['contactnotes']));
		$res['verifycost'] = $ilance->currency->format($res['verifycost']);
		$res['invoiceid'] = '<span class="blue"><a href="' . $ilpage['accounting'] . '?cmd=invoices&amp;invoiceid=' . $res['invoiceid'] . '&amp;pp=10">' . $res['invoiceid'] . '</a></span>';
		$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
		$row_count++;
		$verifications[] = $res;
	}
}
$querybit = '';
foreach ($ilance->GPC as $key => $value)
{
	if ($key != 'submit' AND $key != 'cmd' AND $key != 'subcmd')
	{
		$querybit .= '&amp;' . $key . '=' . $value;
	}
}
$scriptpage = $ilpage['distribution'] . '?cmd=verifications&amp;subcmd=search' . $querybit;
$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], intval($ilance->GPC['page']), $counter, $scriptpage);
// #### VERIFICATION SETTINGS TAB ######################################
$configuration_verificationsettings = $ilance->admincp->construct_admin_input('verificationsystem', $ilpage['distribution'] . '?cmd=verifications');
$pprint_array = array('orderby1','orderby2','configuration_verificationsettings','isverified1','isverified2','isverified3','answerid','user_id','number','status','prevnext','numberpaid','numberunpaid','paidprevnext','unpaidprevnext','id');

($apihook = $ilance->api('admincp_verifications_end')) ? eval($apihook) : false;

$ilance->template->fetch('main', 'verifications.html', 1);
$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
$ilance->template->parse_loop('main', 'verifications');
$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', $pprint_array);
exit();

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>