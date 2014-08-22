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
* Core fetch functions for iLance
*
* @package      iLance\Global\Fetch
* @version      4.0.0.8059
* @author       ILance
*/

/**
* Function to fetch the short form language identifier used by the marketplace as default (english = eng)
*
* @return      string       Short form language identifier
*/
function fetch_site_slng()
{
        global $ilance, $ilconfig;
        $sql = $ilance->db->query("
                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "languagecode
                FROM " . DB_PREFIX . "language
                WHERE languageid = '" . $ilconfig['globalserverlanguage_defaultlanguage'] . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $lcode = $ilance->db->fetch_array($sql, DB_ASSOC);
                return mb_substr($lcode['languagecode'], 0, 3);
        }
        return 'eng';
}

/**
* Function to fetch the short form language identifier used by the marketplace as default (english = eng)
*
* @param       integer      user id
* 
* @return      string       Short form language identifier
*/
function fetch_user_slng($userid = 0)
{
        global $ilance;
        $sql = $ilance->db->query("
                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "languageid
                FROM " . DB_PREFIX . "users
                WHERE user_id = '" . intval($userid) . "' 
                LIMIT 1
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $lang = $ilance->db->fetch_array($sql, DB_ASSOC);
                $sql2 = $ilance->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "languagecode
                        FROM " . DB_PREFIX . "language
                        WHERE languageid = '" . $lang['languageid'] . "' 
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql2) > 0)
                {
                        $lcode = $ilance->db->fetch_array($sql2, DB_ASSOC);
                        return mb_substr($lcode['languagecode'], 0, 3);
                }
        }
        return 'eng';
}

/**
* Function to fetch the extension of a filename being passed as the argument
*
* @param        string        filename including the file extension
*
* @return	string        Returns the file extension (ie: gif) without the period
*/
function fetch_extension($filename = '')
{
        $dot = mb_substr(mb_strrchr($filename, '.'), 1);
        return $dot;
}

/**
* Function to fetch and print a subscription's permission name
*
* @param        string      permission variable to process
*
* @return	string      Returns the membership permission name and description
*/
function fetch_permission_name($variable = '')
{
        global $ilance;
	$arr['_' . $variable . '_text'] = $arr['_' . $variable . '_desc'] = '';
	$slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : fetch_site_slng();
	$sql = $ilance->db->query("
                SELECT text_" . $slng . " AS text, varname
                FROM " . DB_PREFIX . "language_phrases
                WHERE varname LIKE '_" . $ilance->db->escape_string($variable) . "_text' AND varname LIKE '_" . $ilance->db->escape_string($variable) . "_desc'
                LIMIT 2
	");
	while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
	{
                $arr[$res['varname']] = $res['text'];
	}
        return array('text' => $arr['_' . $variable . '_text'], 'description' => $arr['_' . $variable . '_desc']);
}

/**
* Function to fetch any field from the user table based on a number of access methods such as by user id, username or email address
*
* @param        string      field to fetch from the user table
* @param        integer     user id (optional; default)
* @param        string      username (optional)
* @param        string      email (optional)
* @param        boolean     show unknown phrase (default true)
*
* @return	string      Returns field value from the user table
*/
function fetch_user($field = '', $userid = 0, $whereusername = '', $whereemail = '', $showunknown = true)
{
        global $ilance, $phrase, $show;
        $validfields = array('user_id','ipaddress','iprestrict','username','password','salt','secretquestion','secretanswer','email','first_name','last_name','address','address2','city','state','zip_code','phone','country','date_added','subcategories','status','serviceawards','productawards','servicesold','productsold','rating','feedback','score','bidstoday','bidsthismonth','auctiondelists','bidretracts','lastseen','warnings','warning_level','warning_bans','dob','rid','account_number','available_balance','total_balance','income_reported','income_spent','startpage','styleid','project_distance','currency_calculation','languageid','currencyid','timezone','notifyservices','notifyproducts','notifyservicescats','notifyproductscats','displayprofile','emailnotify','displayfinancials','vatnumber','regnumber','dnbnumber','companyname','usecompanyname','rateperhour','profilevideourl','profileintro','autopayment','gender','password_lastchanged','username_history', 'posthtml');
        
	($apihook = $ilance->api('functions_fetch_user_start')) ? eval($apihook) : false;
	
        if (!empty($whereusername))
        {
                $sql = $ilance->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "$field
                        FROM " . DB_PREFIX . "users
                        WHERE username = '" . $ilance->db->escape_string($whereusername) . "' 
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        return handle_input_keywords($res[$field]);
                }
        }
        else if (!empty($whereemail))
        {
                $sql = $ilance->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "$field
                        FROM " . DB_PREFIX . "users
                        WHERE email = '" . $ilance->db->escape_string($whereemail) . "' 
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        return handle_input_keywords($res[$field]);
                }
        }
        else if ($field == 'fullname')
        {
                $sql = $ilance->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "first_name, last_name
                        FROM " . DB_PREFIX . "users
                        WHERE user_id = '" . intval($userid) . "' 
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        return handle_input_keywords(stripslashes($res['first_name']) . ' ' . stripslashes($res['last_name']));
                }       
        }
        else
        {
                if (in_array($field, $validfields))
                {
                        $sql = $ilance->db->query("
                                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "$field
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . intval($userid) . "' 
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                                if ($field == 'username_history')
                                {
                                        return $res["$field"];
                                }
                                return handle_input_keywords($res[$field]);
                        }        
                }
        }
        if ($showunknown)
        {
                return '{_guest}';
        }
        return '';
}

/**
* Function to fetch any field from the invoice table
*
* @param        string      field to fetch from the user table
* @param        integer     invoice id
*
* @return	string      Returns field value from the user table
*/
function fetch_invoice($field = '', $invoiceid = 0)
{
        global $ilance, $phrase;
        $validfields = array('invoiceid','parentid','currency_id','currency_rate','subscriptionid','projectid','buynowid','user_id','p2b_user_id','p2b_paymethod','p2b_markedaspaid','storeid','orderid','description','amount','paid','totalamount','istaxable','taxamount','taxinfo','status','invoicetype','paymethod','paymentgateway','ipaddress','referer','createdate','duedate','paiddate','custommessage','transactionid','archive','ispurchasorder','isdeposit','depositcreditamount','iswithdraw','withdrawinvoiceid','withdrawdebitamount','isfvf','isif','isportfoliofee','isenhancementfee','isescrowfee','iswithdrawfee','isp2bfee','isdonationfee','ischaritypaid','charityid','isregisterbonus','indispute');
	if (in_array($field, $validfields))
	{
		$sql = $ilance->db->query("
			SELECT $field
			FROM " . DB_PREFIX . "invoices
			WHERE invoiceid = '" . intval($invoiceid) . "'
                        LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			return stripslashes($res[$field]);
		}        
	}
        return '';
}

/**
* Function to fetch any field from the auction table based on the main auction listing number identifier
*
* @param        string      field to fetch from the auction table
* @param        integer     auction id
*
* @return	string      Returns field value from the auction table
*/
function fetch_auction($field = '', $auctionid = 0)
{
        global $ilance;
        $sql = $ilance->db->query("
                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "$field
                FROM " . DB_PREFIX . "projects
                WHERE project_id = '" . intval($auctionid) . "' 
                LIMIT 1
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                return stripslashes(handle_input_keywords($res["$field"]));
        }
        return '{_unknown}';
}

/**
* Function for fetching the total bid count today for a particular user id.
*
* @param       integer        user id
*
* @return      integer        total bid count today
*/
function fetch_bidcount_today($userid = 0)
{
        global $ilance;
        $bids = fetch_user('bidstoday', intval($userid));
        return (int)$bids;
}

/**
* Function for fetching the winning user id based on a particular project id.
*
* @param       integer        project id
*
* @return      integer        winning user id
*/
function fetch_project_winnerid($projectid = 0)
{
        global $ilance;
        $sql = $ilance->db->query("
                SELECT user_id
                FROM " . DB_PREFIX . "project_bids
                WHERE project_id = '" . intval($projectid) . "'
                        AND bidstatus = 'awarded'
                LIMIT 1
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                    $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                    return $res['user_id'];
        }
        return 0;
}

/**
* Function for fetching data from a url based on the curl library extention in php
*
* @param       string         url
*
* @return      string         HTML representation of the data requested
*/
function fetch_curl_string($url)
{
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        ob_start();
        curl_exec($ch);
        curl_close($ch);
        $string = ob_get_contents();
        ob_end_clean();
        return $string;    
}

/**
* Function to fetch the business numbers for a user (VAT or Business Reg #)
*
* @param       integer        user id
* @param       bool           force no formatting
* 
* @return      string         Returns business number(s) for display
*/
function fetch_business_numbers($userid = 0, $noformatting = '')
{
        global $ilance, $phrase;
        $sql = $ilance->db->query("
                SELECT regnumber, vatnumber, companyname, dnbnumber
                FROM " . DB_PREFIX . "users
                WHERE user_id = '" . intval($userid) . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                $html = '';
                if (!empty($res['companyname']))
                {
                        $html .= '<div>{_company_name}: <strong>' . stripslashes(handle_input_keywords($res['companyname'])) . '</strong></div>';
                }
                if (!empty($res['regnumber']))
                {
                        $html .= '<div>{_company_registration_number}: <strong>' . handle_input_keywords($res['regnumber']) . '</strong></div>';
                }
                if (!empty($res['vatnumber']))
                {
                        $html .= '<div>{_vat_registration_number}: <strong>' . handle_input_keywords($res['vatnumber']) . '</strong></div>';
                }
                if (empty($html))
                {
                        $html .= '<div>{_no_company_registration_numbers_submitted_to_marketplace}</div>';
                }
        }
        else
        {
                $html = '--';
        }
        return $html;
}

/**
* Function to fetch a valid country id from the datastore based on an actual country name along with a short language identifier
*
* @param       string         country name
* @param       string         short language identifier
*
* @return      integer        Returns the country id
*/
function fetch_country_id($countryname = '', $slng = 'eng')
{
        global $ilance;
        $sql = $ilance->db->query("
                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "locationid
                FROM " . DB_PREFIX . "locations
                WHERE location_" . $ilance->db->escape_string($slng) . " = '" . $ilance->db->escape_string($countryname) . "' OR location_eng = '" . $ilance->db->escape_string($countryname) . "' OR cc = '" . $ilance->db->escape_string($countryname) . "'
                LIMIT 1
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                return $res['locationid'];
        }
        return '500';
}

/**
* Function to fetch a user's id from the datastore based on an actual username
*
* @param       string         user name
*
* @return      integer        Returns the user id
*/
function fetch_userid($username)
{
        global $ilance;
        $sql = $ilance->db->query("
                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "user_id
                FROM " . DB_PREFIX . "users
                WHERE username = '" . $ilance->db->escape_string($username) . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $user = $ilance->db->fetch_array($sql, DB_ASSOC);
                return $user['user_id'];
        }
        return 0;
}

/**
* Function to fetch an admin's username from the datastore based on an actual user/admin id
*
* @param       integer        user/admin id
*
* @return      string         Returns the admin user name
*/
function fetch_adminname($adminid)
{
        global $ilance, $phrase, $phrase;
        $sql = $ilance->db->query("
                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "username
                FROM " . DB_PREFIX . "users
                WHERE user_id = '" . intval($adminid) . "'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                return stripslashes($res['username']);
        }
        return '{_unknown}';
}

/**
* Function to fetch buyers invited to a product auction event.
*
* @param       integer        project id
*
* @return      string         Returns usernames separated by a line break
*/
function fetch_member_invite_list($projectid = 0)
{
        global $ilance;
        $html = '';
        $sql = $ilance->db->query("
                SELECT i.buyer_user_id, u.username
                FROM " . DB_PREFIX . "project_invitations AS i,
                " . DB_PREFIX . "users AS u
                WHERE i.buyer_user_id = u.user_id
                        AND project_id = '" . intval($projectid) . "'
                GROUP BY buyer_user_id
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {            
                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                {
                        $html .= $res['username'] . "\n";   
                }
        }
        return $html;
}

/**
* Function to fetch a project's owner user id
* 
* @param       integer        project id
*
* @return      boolean        Returns user id identifier (or zero if cannot be found)
*/
function fetch_project_ownerid($projectid = 0)
{
        global $ilance;
        $sql = $ilance->db->query("
                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "user_id
                FROM " . DB_PREFIX . "projects
                WHERE project_id = '" . intval($projectid) . "'
                LIMIT 1
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                return $res['user_id'];
        }
        return 0;
}

/**
* Function to print the ending javascript for the dynamic country and states pulldown menu.
*
* @param       integer        user id
* @param       integer        project id
* @param       string         attachment type (default is 'project')
*
* @return      string         Returns the file list
*/
function fetch_inline_attachment_filelist($userid = 0, $projectid = 0, $attachtype = 'project', $printimage = false)
{
        global $ilance, $ilconfig, $ilpage, $phrase;
        $html = '<table cellpadding="3" cellspacing="0"><tr valign="middle">';
        $sql = $ilance->db->query("
                SELECT attachid, visible, filename, filesize, filehash, width_mini, height_mini, width_search, height_search
                FROM " . DB_PREFIX . "attachment
                WHERE attachtype = '" . $ilance->db->escape_string($attachtype) . "'
                        AND project_id = '" . intval($projectid) . "'
                        " . (($userid > 0) ? "AND user_id = '" . intval($userid) . "'" : '') . "
        ", 0, null, __FILE__, __LINE__);
        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
        {
                $moderated = '';
                if ($res['visible'] == 0)
                {
                        $moderated = ' ({_review_in_progress})';
                        $attachment_link = $res['filename'];
                }
                else
                {
                        if ($printimage)
                        {
                                $attachment_link = '<img src="' . $ilpage['attachment'] . '?cmd=thumb&amp;subcmd=itemphotomini&amp;id=' . $res['filehash'] . '" border="0" alt="" id="" style="" />';
                        }
                        else
                        {
                                $attachment_link = '<span class="blue"><a href="' . $ilpage['attachment'] . '?cmd=thumb&amp;subcmd=itemphotomini&amp;id=' . $res['filehash'] . '" target="_blank">' . $res['filename'] . '</a></span>';
                        }
                }
                $html .= '<td style="border:1px solid #ECECEC"><div title="' . $res['filename'] . ' (' . print_filesize($res['filesize']) . ')' . $moderated . '">' . $attachment_link . '  </div></td>';
        }
        $html .= '</tr></table>';
        return $html;
}

/**
* Function to fetch specific information about a charity based on a supplied charity id
*
* @return      array        returns an array of stats
*/
function fetch_charity_details($charityid = 0)
{
        global $ilance, $phrase, $ilconfig, $ilpage;
        $array = array();
        $sql = $ilance->db->query("
                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "title, description, url, visible
                FROM " . DB_PREFIX . "charities
                WHERE visible = '1'
                        AND charityid = '" . intval($charityid) . "'
                LIMIT 1
        ", 0, null, __FILE__, __LINE__);                
        if ($ilance->db->num_rows($sql) > 0)
        {
                $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                $array['title'] = stripslashes(handle_input_keywords($res['title']));
                $array['description'] = stripslashes(handle_input_keywords($res['description']));
                $array['url'] = $res['url'];
        }
        else
        {
                $array['title'] = '{_unknown}';
                $array['description'] = 'n/a';
                $array['url'] = $ilpage['nonprofits'];
        }
        return $array;
}

/**
* Function to fetch a particular role id for a user
*
* @param       string        (month, day)
* @param       integer        user id
*
* @return      integer       returns bids value
*/
function fetch_user_bidcount_per($filter, $userid)
{
        global $ilance;
        if ($filter == 'day')
        {
        	$sql = $ilance->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "bidstoday
                        FROM " . DB_PREFIX . "users
                        WHERE user_id = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $value = $ilance->db->fetch_array($sql, DB_ASSOC);
                        return $value['bidstoday'];
                }
                return 0;
        }
        else if ($filter == 'month')
        {
        	$sql = $ilance->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "bidsthismonth
                        FROM " . DB_PREFIX . "users
                        WHERE user_id = '" . intval($userid) . "'
	        ", 0, null, __FILE__, __LINE__);
	        if ($ilance->db->num_rows($sql) > 0)
	        {
                        $value = $ilance->db->fetch_array($sql, DB_ASSOC);
		        return $value['bidsthismonth'];
	        }
	        return 0;
        }
        return 0;
}

function fetch_bought_count($userid = 0, $what = 'service')
{
	global $ilance, $ilpage, $phrase, $ilconfig;
	$count = 0;
	if ($what == 'service' AND $userid > 0)
	{
		$count = $ilance->db->fetch_field(DB_PREFIX . "users", "user_id = '" . intval($userid) . "'", "serviceawards");
	}
	else if ($what == 'product' AND $userid > 0)
	{
		$count = $ilance->db->fetch_field(DB_PREFIX . "users", "user_id = '" . intval($userid) . "'", "productawards");
	}
	if ($count < 0)
	{
		$count = 0;
	}
	return $count;
}

function fetch_sold_count($userid = 0, $what = 'service')
{
	global $ilance, $ilpage, $phrase, $ilconfig;
	$count = 0;
	if ($what == 'service' AND $userid > 0)
	{
		$count = $ilance->db->fetch_field(DB_PREFIX . "users", "user_id = '" . intval($userid) . "'", "servicesold");
	}
	else if ($what == 'product' AND $userid > 0)
	{
		$count = $ilance->db->fetch_field(DB_PREFIX . "users", "user_id = '" . intval($userid) . "'", "productsold");
	}
	if ($count < 0)
	{
		$count = 0;
	}
	return $count;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>