<?php

/* ==========================================================================*\
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
  \*========================================================================== */

if (!defined('LOCATION') OR defined('LOCATION') != 'admin')
{
    die('<strong>Fatal:</strong> This script cannot be parsed indirectly.');
}

$area_title = '{_report_management}';
$page_title = SITE_NAME . ' - ' . '{_report_management}';

($apihook = $ilance->api('admincp_report_settings')) ? eval($apihook) : false;

$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['accounting'], $ilpage['accounting'] . '?cmd=reports', $_SESSION['ilancedata']['user']['slng']);

if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_do-reports')
{
    $show['showreportoutput'] = true;
    $action = $ilance->GPC['action'];
    if (isset($action) AND $action != '')
    {
	if (empty($ilance->GPC['doshow']))
	{
	    print_action_failed('{_you_did_not_select_a_desired_report_type_please_go_back_and_retry}', $ilpage['accounting'] . '?cmd=reports');
	}

	$sql = "
				SELECT *
				FROM " . DB_PREFIX . "invoices
				WHERE ";
	$searchquery = "";

	// #### generate custom reporting sql
	switch ($ilance->GPC['doshow'])
	{
	    case 'subscription':
		{
		    $sql .= "invoicetype = '" . $ilance->db->escape_string($ilance->GPC['doshow']) . "'";
		    break;
		}
	    case 'credential':
		{
		    $sql .= "invoicetype = '" . $ilance->db->escape_string($ilance->GPC['doshow']) . "'";
		    break;
		}
	    case 'portfolio':
		{
		    $sql .= "invoicetype = 'debit' AND isportfoliofee = '1'";
		    break;
		}
	    case 'enhancements':
		{
		    $sql .= "invoicetype = 'debit' AND isenhancementfee = '1'";
		    break;
		}
	    case 'fvf':
		{
		    $sql .= "invoicetype = 'debit' AND isfvf = '1'";
		    break;
		}
	    case 'insfee':
		{
		    $sql .= "invoicetype = 'debit' AND isif = '1'";
		    break;
		}
	    case 'escrow':
		{
		    $sql .= "invoicetype = 'debit' AND isescrowfee = '1'";
		    break;
		}
	    case 'withdraw':
		{
		    $sql .= "invoicetype = 'debit' AND iswithdrawfee = '1'";
		    break;
		}
	    case 'p2b':
		{
		    $sql .= "invoicetype = 'debit' AND isp2bfee = '1'";
		    break;
		}
	    // expenses
	    case 'tax':
		{
		    $sql .= "istaxable = '1' AND taxamount > 0";
		    break;
		}
	    case 'registerbonus':
		{
		    $sql .= "invoicetype = 'credit' AND isregisterbonus = '1'";
		    break;
		}
	    // loses
	    case 'refund':
		{
		    $sql .= "invoicetype = '" . $ilance->db->escape_string($ilance->GPC['doshow']) . "'";
		    break;
		}
	    case 'cancelled':
		{
		    $sql .= "status = '" . $ilance->db->escape_string($ilance->GPC['doshow']) . "'";
		    break;
		}
	    // disputed
	    case 'disputed':
		{
		    $sql .= "indispute = '1'";
		    break;
		}
	    // nonprofit donation fees collected
	    case 'donationfee':
		{
		    $sql .= "isdonationfee = '1'";
		    break;
		}
	}
	$searchquery .= 'doshow=' . $ilance->GPC['doshow'];

	($apihook = $ilance->api('admincp_reports_generate_doshow_condition')) ? eval($apihook) : false;

	// ensure every transaction shown has a cost to tween out the freebie/waived transactions
	$sql .= " AND totalamount > 0";
	$searchquery .= (isset($ilance->GPC['useridconvert']) AND $ilance->GPC['useridconvert'] == 'generate') ? '&useridconvert=' . $ilance->GPC['useridconvert'] : '';
	$searchquery .= (isset($ilance->GPC['projectidconvert']) AND $ilance->GPC['projectidconvert'] == 'generate') ? '&projectidconvert=' . $ilance->GPC['projectidconvert'] : '';

	$fields = array (
	    array ('invoiceid', 'ID'),
	    array ('transactionid', '{_transaction_id}'),
	    array ('status', '{_status}'),
	    array ('invoicetype', '{_type}'),
	    array ('amount', '{_amount}'),
	    array ('taxamount', '{_tax}'),
	    array ('totalamount', '{_total}'),
	    array ('paid', '{_paid}'),
	    array ('description', '{_description}'),
	    array ('user_id', 'UID'),
	    array ('projectid', 'PID'),
	    array ('createdate', '{_created}'),
	    array ('duedate', '{_due}'),
	    array ('paiddate', '{_paid_date}'),
	    array ('custommessage', '{_message}')
	);

	foreach ($fields AS $column)
	{
	    if (isset($ilance->GPC[$column[0]]) AND $ilance->GPC[$column[0]] == 'generate')
	    {
		$fieldsToGenerate[] = $column[0];
		$headings[] = $column[1];
		$searchquery .= '&' . $column[0] . "=" . $ilance->GPC[$column[0]];
	    }
	}

	// #### date range in the past
	if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == 'past')
	{
	    $startDate = print_datetime_from_timestamp(print_convert_to_timestamp($ilance->GPC['rangepast']));
	    $endDate = print_datetime_from_timestamp(time());
	    $searchquery .= "&range=" . $ilance->GPC['range'];
	}
	// #### date range exactly as entered
	else if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == 'exact')
	{
	    $startDate = print_array_to_datetime($ilance->GPC['range_start']);
	    $startDate = substr($startDate, 0, -9);

	    $endDate = print_array_to_datetime($ilance->GPC['range_end'], TIMENOW);
	    $endDate = substr($endDate, 0, -9);
	    $searchquery .= "&range=" . $ilance->GPC['range'] . "&range_start[0]=" . $ilance->GPC['range_start'][0] . "&range_start[1]=" . $ilance->GPC['range_start'][1] . "&range_start[2]=" . $ilance->GPC['range_start'][2] . "&range_end[0]=" . $ilance->GPC['range_end'][0] . "&range_end[1]=" . $ilance->GPC['range_end'][1] . "&range_end[2]=" . $ilance->GPC['range_end'][2];
	}

	$sql .= " AND (createdate <= '" . $endDate . "' AND createdate >= '" . $startDate . "')";

	// #### display order
	if (isset($ilance->GPC['order']) AND $ilance->GPC['order'] == 'ascending')
	{
	    $sql .= " ORDER BY invoiceid ASC";
	}
	else
	{
	    $sql .= " ORDER BY invoiceid DESC";
	}
	$searchquery .= "&order=" . $ilance->GPC['order'];

	$sql4 = $sql;
	$ilconfig['globalfilters_maxrowsdisplay'] = (!isset($ilance->GPC['limit']) OR isset($ilance->GPC['limit']) AND $ilance->GPC['limit'] <= 50) ? 50 : intval($ilance->GPC['limit']);
	$searchquery .= "&limit=" . intval($ilconfig['globalfilters_maxrowsdisplay']);
	$ilance->GPC['page'] = (!isset($ilance->GPC['p2']) OR isset($ilance->GPC['p2']) AND $ilance->GPC['p2'] <= 0) ? 1 : intval($ilance->GPC['p2']);
	$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
	$sql .= ' LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];

	//echo $sql;
	$data = $ilance->admincp->fetch_reporting_fields($sql, $fieldsToGenerate);
	switch ($action)
	{
	    case 'csv':
		{
		    $reportoutput = $ilance->admincp->construct_csv_data($data, $headings);
		    break;
		}
	    case 'tsv':
		{
		    $reportoutput = $ilance->admincp->construct_tsv_data($data, $headings);
		    break;
		}
	    case 'list':
	    default:
		{
		    $reportoutput = $ilance->admincp->construct_html_table($data, $headings);
		    break;
		}
	}

	$ilance->template->templateregistry['reportoutput'] = $reportoutput;
	$reportoutput = $ilance->template->parse_template_phrases('reportoutput');

	$searchquery .= "&action=" . $ilance->GPC['action'];
	$searchquery .= "&rangepast=" . $ilance->GPC['rangepast'];

	$timeStamp = date("Y-m-d-H-i-s");
	$fileName = "reports-$timeStamp";
	if ($action == 'csv')
	{
	    header("Pragma: cache");
	    header('Content-type: text/comma-separated-values; charset="' . $ilconfig['template_charset'] . '"');
	    header("Content-Disposition: attachment; filename=" . $fileName . ".csv");
	    echo $reportoutput;
	    die();
	}
	else if ($action == 'tsv')
	{
	    header("Pragma: cache");
	    header('Content-type: text/comma-separated-values; charset="' . $ilconfig['template_charset'] . '"');
	    header("Content-Disposition: attachment; filename=" . $fileName . ".txt");
	    echo $reportoutput;
	    die();
	}
    }
    $range = $ilance->GPC['range'];
    $rangepast = $ilance->GPC['rangepast'];
}
else
{
    $show['showreportoutput'] = false;
}

// #### reporting action #######################################
$reportaction = '<select name="action" style="font-family: verdana"><option value="list"';
if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'list')
{
    $reportaction .= ' selected="selected"';
}
$reportaction .= '>' . '{_show_report_listings}' . '</option>';
$reportaction .= '<option value="csv"';
if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'csv')
{
    $reportaction .= ' selected="selected"';
}
$reportaction .= '>' . '{_download_comma_delimited_file}' . '</option>';
$reportaction .= '<option value="tsv"';
if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'tsv')
{
    $reportaction .= ' selected="selected"';
}
$reportaction .= '>' . '{_download_tab_delimited_file}' . '</option></select>';

// #### reporting columns output for search menu ###############
$reportcolumns = '<table width="100%" border="0" cellspacing="0" cellpadding="0" dir="' . $ilconfig['template_textdirection'] . '">';

// invoice id
$reportcolumns .= '<tr><td width="6%"><input type="checkbox" name="invoiceid" value="generate"';
if (!isset($ilance->GPC['action']) OR isset($ilance->GPC['invoiceid']) AND $ilance->GPC['invoiceid'] == "generate")
{
    $reportcolumns .= ' checked="checked"';
}
$reportcolumns .= '></td><td width="17%">Invoice ID</td>';

// user id
$reportcolumns .= '<td width="6%"><input type="checkbox" name="user_id" value="generate"';
if (!isset($ilance->GPC['action']) OR isset($ilance->GPC['user_id']) AND $ilance->GPC['user_id'] == "generate")
{
    $reportcolumns .= ' checked="checked"';
}
$reportcolumns .= '></td><td width="21%">User ID</td>';

// description
$reportcolumns .= '<td width="6%"><input type="checkbox" name="description" value="generate"';
if (!isset($ilance->GPC['action']) OR isset($ilance->GPC['description']) AND $ilance->GPC['description'] == "generate")
{
    $reportcolumns .= ' checked="checked"';
}
$reportcolumns .= '></td><td width="23%">Description</td>';
$reportcolumns .= '<td>&nbsp;</td><td>&nbsp;</td></tr>';

// amount
$reportcolumns .= '<tr><td><input type="checkbox" name="amount" value="generate"';
if (!isset($ilance->GPC['action']) OR isset($ilance->GPC['amount']) AND $ilance->GPC['amount'] == "generate")
{
    $reportcolumns .= ' checked="checked"';
}
$reportcolumns .= '></td><td>Invoice Amount</td>';

// total amount
$reportcolumns .= '<td><input type="checkbox" name="totalamount" value="generate"';
if (!isset($ilance->GPC['action']) OR isset($ilance->GPC['totalamount']) AND $ilance->GPC['totalamount'] == "generate")
{
    $reportcolumns .= ' checked="checked"';
}
$reportcolumns .= '></td><td>Total Amount</td>';

// tax amount
$reportcolumns .= '<td><input type="checkbox" name="taxamount" value="generate"';
if (!isset($ilance->GPC['action']) OR isset($ilance->GPC['taxamount']) AND $ilance->GPC['taxamount'] == "generate")
{
    $reportcolumns .= ' checked="checked"';
}
$reportcolumns .= '></td><td>Tax Amount</td>';
$reportcolumns .= '<td>&nbsp;</td><td>&nbsp;</td></tr>';

// project id
$reportcolumns .= '<tr><td><input type="checkbox" name="projectid" value="generate"';
if (!isset($ilance->GPC['action']) OR isset($ilance->GPC['projectid']) AND $ilance->GPC['projectid'] == "generate")
{
    $reportcolumns .= ' checked="checked"';
}
$reportcolumns .= '></td><td>Project ID</td>';

// invoice status
$reportcolumns .= '<td><input type="checkbox" name="status" value="generate"';
if (!isset($ilance->GPC['action']) OR isset($ilance->GPC['status']) AND $ilance->GPC['status'] == "generate")
{
    $reportcolumns .= ' checked="checked"';
}
$reportcolumns .= '></td><td>Invoice Status</td>';

// invoice type
$reportcolumns .= '<td><input type="checkbox" name="invoicetype" value="generate"';
if (!isset($ilance->GPC['action']) OR isset($ilance->GPC['invoicetype']) AND $ilance->GPC['invoicetype'] == "generate")
{
    $reportcolumns .= ' checked="checked"';
}
$reportcolumns .= '></td><td>Invoice Type</td>';
$reportcolumns .= '<td>&nbsp;</td><td>&nbsp;</td></tr>';

// create date
$reportcolumns .= '<td><input type="checkbox" name="createdate" value="generate"';
if (!isset($ilance->GPC['action']) OR isset($ilance->GPC['createdate']) AND $ilance->GPC['createdate'] == "generate")
{
    $reportcolumns .= ' checked="checked"';
}
$reportcolumns .= '></td><td>Create Date</td>';

// due date
$reportcolumns .= '<td><input type="checkbox" name="duedate" value="generate"';
if (!isset($ilance->GPC['action']) OR isset($ilance->GPC['duedate']) AND $ilance->GPC['duedate'] == "generate")
{
    $reportcolumns .= ' checked="checked"';
}
$reportcolumns .= '></td><td>Due Date</td>';

// paid date
$reportcolumns .= '<td><input type="checkbox" name="paiddate" value="generate"';
if (!isset($ilance->GPC['action']) OR isset($ilance->GPC['paiddate']) AND $ilance->GPC['paiddate'] == "generate")
{
    $reportcolumns .= ' checked="checked"';
}
$reportcolumns .= '></td><td>Paid Date</td>';
$reportcolumns .= '<td>&nbsp;</td><td>&nbsp;</td>';

// custom message
$reportcolumns .= '<tr><td><input type="checkbox" name="custommessage" value="generate"';
if (!isset($ilance->GPC['action']) OR isset($ilance->GPC['custommessage']) AND $ilance->GPC['custommessage'] == "generate")
{
    //$reportcolumns .= ' checked="checked"';
    $reportcolumns .= '';
}
$reportcolumns .= '></td><td>Custom Message</td>';

// transaction id
$reportcolumns .= '<td><input type="checkbox" name="transactionid" value="generate"';
if (!isset($ilance->GPC['action']) OR isset($ilance->GPC['transactionid']) AND $ilance->GPC['transactionid'] == "generate")
{
    //$reportcolumns .= ' checked="checked"';
    $reportcolumns .= '';
}
$reportcolumns .= '></td><td>Transaction ID</td>';

// amount paid
$reportcolumns .= '<td><input type="checkbox" name="paid" value="generate"';
if (!isset($ilance->GPC['action']) OR isset($ilance->GPC['paid']) AND $ilance->GPC['paid'] == "generate")
{
    $reportcolumns .= ' checked="checked"';
}
$reportcolumns .= '></td><td>Amount Paid</td>';
$reportcolumns .= '<td>&nbsp;</td><td>&nbsp;</td></tr></table>';

// #### date range #############################################
$radiopast = '<input type="radio" name="range" value="past"';
if ((!isset($ilance->GPC['action']) OR (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == "past")))
{
    $radiopast .= ' checked="checked"';
}
$radiopast .= '>';
$radioexact = '<input type="radio" name="range" value="exact"';
if ((!isset($ilance->GPC['action']) OR (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == "exact")))
{
    $radioexact .= ' checked="checked"';
}
$radioexact .= '>';
$reportrange = '<select name="rangepast" style="font-family: verdana"><option value="-1 day"';
if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == "past" AND isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 day")
{
    $reportrange .= ' selected="selected"';
}
$reportrange .= '>The Past Day</option><option value="-1 week"';
if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == "past" AND isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 week")
{
    $reportrange .= ' selected="selected"';
}
$reportrange .= '>The Past Week</option><option value="-1 month"';
if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == "past" AND isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 month")
{
    $reportrange .= ' selected="selected"';
}
$reportrange .= '>The Past Month</option><option value="-1 year"';
if (isset($ilance->GPC['range']) AND $ilance->GPC['range'] == "past" AND isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 year")
{
    $reportrange .= ' selected="selected"';
}
$reportrange .= '>The Past Year</option></select>';

// #### advanced reporting from range ##########################
$reportfromrange = $ilance->admincp->print_from_to_date_range();

// #### order by ascending / desending #########################
$reportorderby = '<input type="radio" name="order" value="ascending"';
if (!isset($ilance->GPC['action']) OR $ilance->GPC['order'] == "ascending")
{
    $reportorderby .= ' checked="checked"';
}
$reportorderby .= '>' . '{_ascending}' . ' &nbsp;&nbsp;&nbsp; <input type="radio" name="order" value="descending"';
if (isset($ilance->GPC['order']) AND $ilance->GPC['order'] == "descending")
{
    $reportorderby .= ' checked="checked"';
}
$reportorderby .= '>' . '{_descending}';
if (isset($sql4))
{
    $number2 = $ilance->db->num_rows($ilance->db->query($sql4, 0, null, __FILE__, __LINE__));
    $page1 = $ilance->GPC['page'] + 1;
    $customprevnext = print_pagnation($number2, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $ilpage['accounting'] . '?cmd=' . $ilance->GPC['cmd'] . '&subcmd=' . $ilance->GPC['subcmd'] . '&page=' . $page1 . '&' . $searchquery, 'p2');
}

$pprint_array = array ('reportorderby', 'reportfromrange', 'reportrange', 'radiopast', 'radioexact', 'reportcolumns', 'reportaction', 'reportshow', 'customprevnext', 'reportoutput');

($apihook = $ilance->api('admincp_accounting_reports_end')) ? eval($apihook) : false;

$ilance->template->fetch('main', 'reports.html', 1);
$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
$ilance->template->parse_loop('main', array ('v3nav', 'subnav_settings'), false);
$ilance->template->parse_loop('main', array ('reports'));
$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', $pprint_array);
exit();

/* ======================================================================*\
  || ####################################################################
  || # Downloaded: Thu, Jul 31st, 2014
  || ####################################################################
  \*====================================================================== */
?>
