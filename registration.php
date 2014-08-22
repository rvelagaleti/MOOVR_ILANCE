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
		'jquery'
	),
	'footer' => array(
		'v4',
		'tooltip',
		'cron'
	)
);

// #### define top header nav ##################################################
$topnavlink = array(
        'registration'
);

// #### setup script location ##################################################
define('LOCATION', 'registration');

// #### require backend ########################################################
require_once('./functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[registration]" => $ilcrumbs["$ilpage[registration]"]);

$area_title = '{_user_registration}';
$page_title = SITE_NAME . ' - {_user_registration}';

$show['slimheader'] = true;
$show['slimfooter'] = true;

($apihook = $ilance->api('registration_start')) ? eval($apihook) : false;

// #### REDIRECTION HANDLER ####################################################
if (isset($ilance->GPC['redirect']) AND !empty($ilance->GPC['redirect']))
{
        $ilance->GPC['redirect'] = strip_tags($ilance->GPC['redirect']);
	$_SESSION['ilancedata']['user']['new_redirect'] = (!isset($_SESSION['ilancedata']['user']['new_redirect'])) ? $ilance->GPC['redirect'] : '';
}
// #### new member email verification process ##################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'activate')
{
        if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'resend')
        {
                // member requests that ilance resend their email link code verification
                if (!empty($ilance->GPC['email']))
                {
                        // resend email activation code to member
                        if ($ilance->registration->send_email_activation($ilance->GPC['email']))
                        {
                                refresh(HTTPS_SERVER . $ilpage['login'] . '?error=checkemail');
                                exit();
                        }
                        else
                        {
                                refresh(HTTPS_SERVER . $ilpage['login'] . '?error=1');
                                exit();
                        }
                }
                else
                {
                        refresh(HTTPS_SERVER . $ilpage['login'] . '?error=1');
                        exit();
                }
        }
        else
        {
                // member appears to be validating his/her registration
                if (!empty($ilance->GPC['u']))
                {
                        $ilance->GPC['u'] = $ilance->crypt->three_layer_decrypt($ilance->GPC['u'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']);
                        $sql = $ilance->db->query("
                                SELECT user_id, email, username, first_name, last_name, phone
                                FROM " . DB_PREFIX . "users 
                                WHERE user_id = '" . intval($ilance->GPC['u']) . "'
                                        AND status = 'unverified'
                                LIMIT 1
                        ");
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $user = $ilance->db->fetch_array($sql, DB_ASSOC);
                                // does admin manually verify new members before they can login?
                                $status = ($ilconfig['registrationdisplay_moderation']) ? 'moderated' : 'active';
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "users
                                        SET status = '" . $ilance->db->escape_string($status) . "'
                                        WHERE user_id = '" . intval($ilance->GPC['u']) . "'
					LIMIT 1
                                ");
                                // if we are active, send new email to user
                                if ($status == 'active')
                                {
                                        // if an account credit bonus was active we should dispatch that email to new user now
                                        // and update his account balance with new credit accordingly
                                        $registerbonus = '0.00';
                                        if ($ilconfig['registrationupsell_bonusactive'])
                                        {
                                                // lets construct a little payment bonus for new member, we will:
                                                // - create a transaction and send email to user and admin
                                                // - return the bonus amount so we can update the users account
                                                $registerbonus = $ilance->accounting->construct_account_bonus($user['user_id'], $status);
                                                if ($registerbonus > 0)
                                                {
                                                        // update register bonus credit to online account data
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "users
                                                                SET total_balance = total_balance + $registerbonus,
                                                                available_balance = available_balance + $registerbonus
                                                                WHERE user_id = '" . $user['user_id'] . "'
                                                        ");
                                                }
                                        }
					$categories = '';			
					if ($ilconfig['globalauctionsettings_productauctionsenabled'])
					{
						$getcats = $ilance->db->query("
							SELECT cid, title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title
							FROM " . DB_PREFIX . "categories
							WHERE parentid = '0'
								AND cattype = 'product'
								AND visible = '1'
							ORDER BY title_" . $_SESSION['ilancedata']['user']['slng'] . " ASC
							LIMIT 10
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($getcats) > 0)
						{
							while ($res = $ilance->db->fetch_array($getcats, DB_ASSOC))
							{
								$categories .= "$res[title]\n";
							}
						}
					}
					if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
					{
						$getcats = $ilance->db->query("
							SELECT cid, title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title
							FROM " . DB_PREFIX . "categories
							WHERE parentid = '0'
								AND cattype = 'service'
								AND visible = '1'
							ORDER BY title_" . $_SESSION['ilancedata']['user']['slng'] . " ASC
							LIMIT 10
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($getcats) > 0)
						{
							while ($res = $ilance->db->fetch_array($getcats, DB_ASSOC))
							{
								$categories .= "$res[title]\n";
							}
						}
					}
					// admin activates new members after their email link code verification
					// so in this case, let's dispatch a new welcome email to the member
					$ilance->email->mail = $user['email'];
					$ilance->email->slng = fetch_user_slng($user['user_id']);
					$ilance->email->get('register_welcome_email');		
					$ilance->email->set(array(
						    '{{username}}' => $user['username'],
						    '{{user_id}}' => $user['user_id'],
						    '{{first_name}}' => $user['first_name'],
						    '{{last_name}}' => $user['last_name'],
						    '{{phone}}' => $user['phone'],
						    '{{categories}}' => $categories
					));
					$ilance->email->send();
				}
				$geoipcity = $geoipcountry = $geoipstate = $geoipzip = '';
				if (file_exists(DIR_CORE . 'functions_geoip.php') AND file_exists(DIR_CORE . 'functions_geoip_city.dat') AND file_exists(DIR_CORE . 'functions_geoip_city.dat'))
				{
					if (!function_exists('geoip_open'))
					{
						require_once(DIR_CORE . 'functions_geoip.php');
					}
					$geoip = geoip_open(DIR_CORE . 'functions_geoip_city.dat', GEOIP_STANDARD);
					$geo = geoip_record_by_addr($geoip, IPADDRESS);
					$geoipcity = (!empty($geo->city) ? $geo->city : '-');
					$geoipcountry = (!empty($geo->country_name) ? $geo->country_name : '-');
					$geoipstate = (!empty($geo->region) ? (!empty($GEOIP_REGION_NAME[$geo->country_code][$geo->region]) ? $GEOIP_REGION_NAME[$geo->country_code][$geo->region] : '-') : '-');
					$geoipzip = (!empty($geo->postal_code) ? $geo->postal_code : '-');
				}
				// dispatch email to admin
				$ilance->email->mail = SITE_EMAIL;
				$ilance->email->slng = fetch_site_slng();
				$ilance->email->get('register_welcome_email_admin');		
				$ilance->email->set(array(
					'{{username}}' => $user['username'],
					'{{user_id}}' => $user['user_id'],
					'{{first_name}}' => $user['first_name'],
					'{{last_name}}' => $user['last_name'],
					'{{phone}}' => $user['phone'],
					'{{emailaddress}}' => $user['email'],
					'{{ipaddress}}' => IPADDRESS,
					'{{country}}' => $geoipcountry,
					'{{city}}' => $geoipcity,
					'{{state}}' => $geoipstate,
					'{{zipcode}}' => $geoipzip
                                ));
                                $ilance->email->send();
                                if ($status == 'active')
                                {
					$redirect = (isset($ilance->GPC['redirect'])) ? $ilpage['login'] . '?redirect=' . urlencode($ilance->GPC['redirect']) : $ilpage['login'];
                                        print_notice('{_registration_complete}', '{_thank_you_for_registering_we_are_glad_you_have_chosen}', HTTPS_SERVER . $redirect, '{_please_log_in}');
                                        exit();
                                }
                                else
                                {
                                        // display thanks for verifying email, admin will moderate you shortly ..
                                        // at this point the user still has not been sent the welcome to the marketplace
                                        // nor has he received any "account bonus" credit for signing up
                                        // these emails will be dispatched from the admin control panel
                                        print_notice('{_registration_complete}', '{_thanks_for_verifying_your_email_sddress_credentials_all_new_accounts_are_currently_under_moderated_review_you_will_receive_an_email_very_shortly}', HTTP_SERVER . $ilpage['search'], '{_search}');
                                        exit();
                                }
                        }
                        else
                        {
                                refresh(HTTPS_SERVER . $ilpage['login'] . '?error=1');
                                exit();
                        }
                }
                else
                {
                        refresh(HTTPS_SERVER . $ilpage['login'] . '?error=1');
                        exit();
                }
        }
}

// are we returning to registration from a previous invitation or registration attempt?
if (!empty($_COOKIE[COOKIE_PREFIX . 'invitedid']) AND $_COOKIE[COOKIE_PREFIX . 'invitedid'] > 0)
{
        $_SESSION['ilancedata']['user']['invited'] = 1;
        $_SESSION['ilancedata']['user']['invitedid'] = intval($_COOKIE[COOKIE_PREFIX . 'invitedid']);
}
else
{
        // are we being externally invited?
        if (isset($ilance->GPC['invited']) AND $ilance->GPC['invited'] AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
        {
                // member has clicked link from within auction page
                $_SESSION['ilancedata']['user']['invited'] = 1;
                $_SESSION['ilancedata']['user']['invitedid'] = intval($ilance->GPC['id']);
                set_cookie('invitedid', $_SESSION['ilancedata']['user']['invitedid']);
        }
}

// #### BEGIN REGISTRATION #####################################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'register')
{
        if (!isset($ilance->GPC['step']))
        {
                $ilance->GPC['step'] = 1;
                // disable some cookies so we don't auto-login
                set_cookie('userid', '', false);
                set_cookie('password', '', false);
        }
        // ########### STEP 1 ##################################################
        if ($ilance->GPC['step'] == '1')
        {
                $navcrumb = array();
                $navcrumb["$ilpage[registration]"] = '{_registration}';
                $navcrumb[""] = '{_account}';
                $show['error_username'] = $show['error_username2'] = $show['error_email'] = $show['error_email2'] = $show['error_email3'] = $show['error_turing'] = false;
                $username = $password = $password2 = $email = $email2 = $secretquestion = $secretanswer = $defaultroleid = '';
                if (isset($ilance->GPC['agreement']) AND $ilance->GPC['agreement'] == 1)
                {
                        $_SESSION['ilancedata']['user']['agreeterms'] = 1;
                }
                if (!empty($ilance->GPC['year']) AND !empty($ilance->GPC['month']) AND !empty($ilance->GPC['day']) AND $ilconfig['registrationdisplay_dob'])
                {
                        $_SESSION['ilancedata']['user']['dob'] = intval($ilance->GPC['year']) . '-' . intval($ilance->GPC['month']) . '-' . intval($ilance->GPC['day']);
                        if ($ilconfig['registrationdisplay_dobunder18'] == 0)
                        {
                                if ($ilance->GPC['year'] > (date('Y') - 18) OR ($ilance->GPC['year'] == (date('Y') - 18) AND $ilance->GPC['month'] < date('m')) OR ($ilance->GPC['year'] == (date('Y') - 18) AND $ilance->GPC['month'] == date('m') AND $ilance->GPC['day'] < date('d')))
                                {
                                        print_notice('{_you_must_be_over_18}', '{_were_sorry_you_must_be_over_the_age_of_18_to_register_on_this_marketplace}', $ilpage['main'], '{_main_menu}');
                                        exit();
                                }
                        }
                }
                else
                {
                        $_SESSION['ilancedata']['user']['dob'] = '0000-00-00';
                }
                if ($ilconfig['registrationdisplay_dob'] != 1)
                {
                        $_SESSION['ilancedata']['user']['dob'] = '0000-00-00';
                }
                if (!empty($_SESSION['ilancedata']['user']['agreeterms']) AND $_SESSION['ilancedata']['user']['agreeterms'] == 1)
                {
                        // captcha logic (does server support image creation)?
                        if (extension_loaded('gd'))
                        {
                                $captcha = '<img src="attachment.php?do=captcha" alt="{_please_enter_the_security_code_shown_on_the_image_to_continue_registration}" border="0" />';
                        }
                        else
                        {
                                // captcha set? reset it!
                                $_SESSION['ilancedata']['user']['captcha'] = '';
                                $src = 'abcdefghjkmnpqrstuvwxyz23456789';
                                if (mt_rand(0, 1) == 0)
                                {
                                        $src = mb_strtoupper($src);
                                }
                                $srclen = mb_strlen($src) - 1;
                                $length = 5;
                                for ($i=0; $i<$length; $i++)
                                {
                                        $char = mb_substr($src, mt_rand(0, $srclen), 1);
                                        $_SESSION['ilancedata']['user']['captcha'] .= $char;
                                }
                                $captcha = '<div class="yellowhlite" style="width:95px; float:left"><h1 style="color:#ff6600; text-align: center">' . $_SESSION['ilancedata']['user']['captcha'] . '</h1></div>';
                        }
                        $js = '<script type="text/javascript">
<!--
verify = new verifynotify();
verify.field1 = fetch_js_object(\'password\');
verify.field2 = fetch_js_object(\'password2\');
verify.result_id = "password_result";
verify.match_html = "<span style=\"color:blue\"><img src=\"' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif\" border=\"0\" alt=\"\" /></span>";
verify.nomatch_html = "";
verify.check();
//-->
</script>
';
                        $headinclude .= '<script type="text/javascript">
<!--
if (!window.XMLHttpRequest)
{
        var reqObj = 
        [
                function() {return new ActiveXObject("Msxml2.XMLHTTP");},
                function() {return new ActiveXObject("Microsoft.XMLHTTP");},
                function() {return window.createRequest();}
        ];
        for(a = 0, z = reqObj.length; a < z; a++)
        {
                try
                {
                        window.XMLHttpRequest = reqObj[a];
                        break;
                }
                catch(e)
                {
                        window.XMLHttpRequest = null;
                }
        }
}
var req = new XMLHttpRequest();
var role = 0;
function show_questions(selectobj)
{
	var role;
	role = selectobj.options[selectobj.selectedIndex].value;
	req.open(\'GET\', \'' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['ajax'] . '?do=registrationquestions&roleid=\' + role);
	req.send(null); 
}
req.onreadystatechange = function()
{
	if (req.readyState == 4 && req.status == 200)
	{
		var myString;
		myString = req.responseText;
		obj = fetch_js_object(\'customquestions\');
		obj.innerHTML = myString;
	}
}                
function verifynotify(field1, field2, result_id, match_html, nomatch_html)
{
        this.field1 = field1;
        this.field2 = field2;
        this.result_id = result_id;
        this.match_html = match_html;
        this.nomatch_html = nomatch_html;
        this.check = function() 
        {
                if (!this.result_id) 
                {	 
                        return false; 
                }
                if (!document.getElementById)
                { 
                        return false; 
                }
                r = fetch_js_object(this.result_id);
                if (!r)
                { 
                        return false; 
                }
                if (this.field1.value != "" && this.field1.value == this.field2.value) 
                {
                        r.innerHTML = this.match_html;
                } 
                else 
                {
                        r.innerHTML = this.nomatch_html;
                }
        }
}
function register1(f)
{
        haveerrors = 0;
        (f.username.value.length < 1) ? showImage("usernameerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("usernameerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
	(f.roleid.value.length < 1) ? showImage("roleerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("roleerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        (f.password.value.length < 1) ? showImage("passworderror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("passworderror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        (f.password2.value.length < 1) ? showImage("password2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("password2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        (f.secretquestion.value.length < 1) ? showImage("secretquestionerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("secretquestionerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        (f.secretanswer.value.length < 1) ? showImage("secretanswererror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("secretanswererror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        (f.email.value.search("@") == -1 || f.email.value.search("[.*]") == -1) ? showImage("emailerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("emailerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        (f.email2.value.search("@") == -1 || f.email2.value.search("[.*]") == -1) ? showImage("email2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("email2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        ' . ($ilconfig['registrationdisplay_turingimage'] ? '(f.turing.value.length < 1) ? showImage("turingerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("turingerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);' : '') . '
        return (!haveerrors);
}
//-->
</script>
';
			$slng = $_SESSION['ilancedata']['user']['slng'];
			$sqlrolepulldown = $ilance->db->query("
				SELECT r.roleid, r.purpose_$slng AS purpose, r.title_$slng AS title, r.custom, r.roletype, r.roleusertype, r.active
				FROM " . DB_PREFIX . "subscription_roles r,
				" . DB_PREFIX . "subscription s
				WHERE r.roleid = s.roleid
					AND r.active = '1'
					AND s.active = 'yes'
					AND s.visible_registration = '1'
				GROUP BY r.roleid ASC
			", 0, null, __FILE__, __LINE__);
			$rerole = $ilance->db->fetch_array($sqlrolepulldown, DB_ASSOC);			
			$rolesql = $ilance->db->num_rows($sqlrolepulldown);
			if (isset($rolesql) AND $rolesql > 1)
			{
				$show['rolescount'] = true;						
			}
			else
			{
				$show['rolescount'] = false;
				if ($rolesql == 1)
				{ // only 1 visible role available, use it as default since we don't show the pull down if there is only one available
					$ilance->GPC['roleid'] = $rerole['roleid'];
					$defaultroleid = $ilance->GPC['roleid'];
				}
				else
				{ // all plans hidden from registration..
					if (isset($ilconfig['subscriptions_defaultroleid']) AND $ilconfig['subscriptions_defaultroleid'] > 0)
					{
						$ilance->GPC['roleid'] = $ilconfig['subscriptions_defaultroleid'];
						$defaultroleid = $ilance->GPC['roleid'];
					}
					else
					{
						$ilance->GPC['roleid'] = '-1';
						$defaultroleid = $ilance->GPC['roleid'];
					}
				}
			}
                        $roleselected = isset($ilance->GPC['roleid']) ? intval($ilance->GPC['roleid']) : '';
                        $rolepulldown = $ilance->subscription_role->print_role_pulldown($roleselected, 0, 0, 0, 'onchange="show_questions(this)"');
                        $pprint_array = array('defaultroleid','rolepulldown','customquestions','password','password2','secretquestion','secretanswer','js','captcha','username','email','email2','js','captcha','first_name','last_name','input_style');
                        $ilance->template->fetch('main', 'register1.html');
                        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                        $ilance->template->parse_if_blocks('main');
                        $ilance->template->pprint('main', $pprint_array);
                        exit();
                }
                else
                {
			$yearpulldown = pulldown_year();
			$registration1 = $ilance->db->fetch_field(DB_PREFIX . "cms", "", "registrationterms");
                        $area_title = '{_registration}<div class="smaller">{_terms_and_agreements}</div>';
                        $page_title = SITE_NAME . ' - {_registration_terms_and_agreements_review}';
                        $pprint_array = array('registration1','yearpulldown','first_name','last_name','input_style');
                        $ilance->template->fetch('main', 'registration.html');
                        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                        $ilance->template->parse_if_blocks('main');
                        $ilance->template->pprint('main', $pprint_array);
                        exit();
                }
        }
        // ########### STEP 2 ##################################################
        else if ($ilance->GPC['step'] == '2')
        {
		$slng = $_SESSION['ilancedata']['user']['slng'];
		$sqlrolepulldown = $ilance->db->query("
			SELECT r.roleid, r.purpose_$slng AS purpose, r.title_$slng AS title, r.custom, r.roletype, r.roleusertype, r.active
			FROM " . DB_PREFIX . "subscription_roles r,
			" . DB_PREFIX . "subscription s
			WHERE r.roleid = s.roleid
				AND r.active = '1'
				AND s.active = 'yes'
				AND s.visible_registration = '1'
			GROUP BY r.roleid ASC
		", 0, null, __FILE__, __LINE__);
		$rerole = $ilance->db->fetch_array($sqlrolepulldown, DB_ASSOC);			
		$rolesql = $ilance->db->num_rows($sqlrolepulldown);
		if (isset($rolesql) AND $rolesql > 1)
		{
			$show['rolescount'] = true;						
		}
		else
		{
			$show['rolescount'] = false;
			if ($rolesql == 1)
			{ // only 1 visible role available, use it as default since we don't show the pull down if there is only one available
				$ilance->GPC['roleid'] = $rerole['roleid'];
				$defaultroleid = $ilance->GPC['roleid'];
			}
			else
			{ // all plans hidden from registration..
				if (isset($ilconfig['subscriptions_defaultroleid']) AND $ilconfig['subscriptions_defaultroleid'] > 0)
				{
					$ilance->GPC['roleid'] = $ilconfig['subscriptions_defaultroleid'];
					$defaultroleid = $ilance->GPC['roleid'];
				}
				else
				{
					$ilance->GPC['roleid'] = '-1';
					$defaultroleid = $ilance->GPC['roleid'];
				}
			}
		}
                // #### construct breadcrumb trail #############################
                $navcrumb = array();
                $navcrumb["$ilpage[registration]"] = '{_registration}';
                $navcrumb[""] = '{_contact}';
                // #### template if conditions #################################
		$show['error_username'] = $show['error_username2'] = $show['error_password'] = $show['error_email'] = $show['error_email2'] = $show['error_email3'] = $show['error_turing'] = $show['error_register_questions'] = $show['error_role'] = false;
                $username = $password = $password2 = $email = $email2 = $secretquestion = $secretanswer = '';
                $error_secret = 0;
                // #### handle custom questions in this section ################
                if (isset($ilance->GPC['custom']) AND is_array($ilance->GPC['custom']))
                {
                        foreach ($ilance->GPC['custom'] AS $questionid => $answerarray)
                        {
                                foreach ($answerarray AS $formname => $answer)
                                {
                                        $checkanswer = $ilance->db->query("
						SELECT formdefault, formname, questionid, inputtype, required
						FROM " . DB_PREFIX . "register_questions
						WHERE formname = '" . $ilance->db->escape_string($formname) . "'
							AND visible = '1'
					");
					if ($ilance->db->num_rows($checkanswer) > 0)
					{
						$row = $ilance->db->fetch_array($checkanswer, DB_ASSOC);
						if (empty($answer) AND $answer != '0' AND $row['required'] == '1')
						{
							$show['error_register_questions'] = true;
						}
						else 
						{
							$_SESSION['ilancedata']['questions'][$formname] = handle_input_keywords($answer);
						}
					}
                                }
                        }
                }
                // #### role selection #########################################
                if (isset($ilance->GPC['roleid']) AND $ilance->GPC['roleid'] > 0)
                {
                        $_SESSION['ilancedata']['user']['roleid'] = intval($ilance->GPC['roleid']);
                }
                else
                {
			if (isset($ilconfig['subscriptions_defaultroleid']) AND $ilconfig['subscriptions_defaultroleid'] > 0)
			{
				$_SESSION['ilancedata']['user']['roleid'] = $ilconfig['subscriptions_defaultroleid'];
				$show['error_role'] = false;
			}
			else
			{
				$_SESSION['ilancedata']['user']['roleid'] = $defaultroleid;
				$show['error_role'] = true;
			}
                }
                // #### username checkup #######################################
                if (isset($ilance->GPC['username']) AND $ilance->GPC['username'] != '')
                {
			// do we allow special characters in username?
			$unicode_name = preg_replace('/&#([0-9]+);/esiU', "convert_int2utf8('\\1')", $ilance->GPC['username']);
                        if ($ilance->common->is_username_banned($ilance->GPC['username']) OR $ilance->common->is_username_banned($unicode_name))
                        {
                                $show['error_username2'] = true;
                                $username = $ilance->GPC['username'];
                        }
                        else
                        {
                                $sqlusercheck = $ilance->db->query("
                                        SELECT user_id
                                        FROM " . DB_PREFIX . "users
                                        WHERE username IN ('" . addslashes(htmlspecialchars_uni($ilance->GPC['username'])) . "', '" . addslashes(htmlspecialchars_uni($unicode_name)) . "')
                                ");
                                if ($ilance->db->num_rows($sqlusercheck) > 0)
                                {
                                        $show['error_username'] = true;
                                        $username = stripslashes(strip_tags(trim($ilance->GPC['username'])));
                                }
                                else
                                {
                                        $_SESSION['ilancedata']['user']['username'] = trim($ilance->GPC['username']);
                                        $username = $_SESSION['ilancedata']['user']['username'];
                                }
                        }
                }
                else
                {
                        $show['error_username'] = true;
                }
                // #### password checkup #######################################
                if (isset($ilance->GPC['password']) AND $ilance->GPC['password'] != '' AND isset($ilance->GPC['password2']) AND $ilance->GPC['password2'] != '')
                {
                        if ($ilance->GPC['password'] != $ilance->GPC['password2'])
                        {
                                $show['error_password'] = true;
                                $password = trim($ilance->GPC['password']);
                                $password2 = trim($ilance->GPC['password2']);
                        }
                        else
                        {
                                // store password and salt
                                $_SESSION['ilancedata']['user']['salt'] = construct_password_salt($length = 5);
                                $_SESSION['ilancedata']['user']['password_md5'] = md5(md5($ilance->GPC['password']) . $_SESSION['ilancedata']['user']['salt']);
                                $password = trim($ilance->GPC['password']);
                                $password2 = trim($ilance->GPC['password2']);
                        }
                }
                else
                {
                        $show['error_password'] = true;
                }
                // #### secret question/answer checkup #########################
                if (isset($ilance->GPC['secretquestion']) AND $ilance->GPC['secretquestion'] != '' AND isset($ilance->GPC['secretanswer']) AND $ilance->GPC['secretanswer'] != '')
                {
                        $_SESSION['ilancedata']['user']['secretquestion'] = $ilance->GPC['secretquestion'];
			$_SESSION['ilancedata']['user']['secretanswer'] = $ilance->GPC['secretanswer'];
                        $secretquestion = $_SESSION['ilancedata']['user']['secretquestion'];
                        $secretanswer = $_SESSION['ilancedata']['user']['secretanswer'];
                }
                else
                {
                        $error_secret = 1;
                        $secretquestion = $ilance->GPC['secretquestion'];
                        $secretanswer = '';
                }
                // #### email checkup ##########################################
                if (isset($ilance->GPC['email']) AND $ilance->GPC['email'] != '' AND isset($ilance->GPC['email2']) AND $ilance->GPC['email2'] != '')
                {
                        if ($ilance->GPC['email'] != $ilance->GPC['email2'])
                        {
                                $show['error_email2'] = true;
                                $email = $ilance->GPC['email'];
                                $email2 = $ilance->GPC['email2'];
                        }
                        // final email checks (check mx record, check list of banned free emails, etc)
                        if ($ilance->common->is_email_banned(trim($ilance->GPC['email'])))
                        {
                                $show['error_email3'] = true;
                                $email = $ilance->GPC['email'];
                                $email2 = $ilance->GPC['email2'];
                        }
                        // email is good check if it's duplicate
                        $sqlemailcheck = $ilance->db->query("
                                SELECT user_id
                                FROM " . DB_PREFIX . "users
                                WHERE email = '" . $ilance->db->escape_string($ilance->GPC['email']) . "'
                        ");
                        if ($ilance->db->num_rows($sqlemailcheck) > 0)
                        {
                                $show['error_email'] = true;
                                $email = trim($ilance->GPC['email']);
                        }
                        else
                        {
                                $_SESSION['ilancedata']['user']['email'] = trim($ilance->GPC['email']);
                                $email = $ilance->GPC['email'];
                                $email2 = $ilance->GPC['email2'];
                        }
                }
                else
                {
                        $show['error_email2'] = true;
                        $email = $email2 = '';
                }
                // #### does admin use captcha? ################################
                if ($ilconfig['registrationdisplay_turingimage'])
                {
                        // user supplied turing captcha
                        if (isset($ilance->GPC['turing']) AND $ilance->GPC['turing'] != '' AND !empty($_SESSION['ilancedata']['user']['captcha']))
                        {
                                $turing = mb_strtoupper(trim($ilance->GPC['turing']));
                                if ($turing != $_SESSION['ilancedata']['user']['captcha'])
                                {
                                        $show['error_turing'] = true;
                                }
                        }
                        else
                        {
                                $show['error_turing'] = true;
                        }
                }
                // #### final checkups for step 1 ##############################
		if (isset($show['error_username']) AND $show['error_username'] OR isset($show['error_username2']) AND $show['error_username2'] OR isset($show['error_password']) AND $show['error_password'] OR isset($error_secret) AND $error_secret == 1 OR isset($show['error_email']) AND $show['error_email'] OR isset($show['error_email2']) AND $show['error_email2'] OR isset($show['error_email3']) AND $show['error_email3'] OR isset($show['error_turing']) AND $show['error_turing'] OR isset($show['error_register_questions']) AND $show['error_register_questions'] OR isset($show['error_role']) AND $show['error_role'])
                {
                        // ########### ERRORS: BACK TO STEP 1 ##############################
                        $navcrumb = array();
                        $navcrumb["$ilpage[registration]?cmd=register"] = '{_registration}';
                        $navcrumb["$ilpage[registration]"] = '{_account}';
                        $_SESSION['ilancedata']['user']['captcha'] = '';
                        $src  = '';
                        $src .= 'abcdefghjkmnpqrstuvwxyz';
                        $src .= '23456789';
                        if (mt_rand(0,1) == 0)
                        {
                                $src = mb_strtoupper($src);
                        }
                        $srclen = mb_strlen($src) - 1;
                        $length = 5;
                        for ($i = 0; $i<$length; $i++)
                        {
                                $char = mb_substr($src, mt_rand(0,$srclen), 1);
                                $_SESSION['ilancedata']['user']['captcha'] .= $char;
                        }
                        if (extension_loaded('gd'))
                        {
                                $captcha = '<img src="attachment.php?do=captcha" alt="{_please_enter_the_security_code_shown_on_the_image_to_continue_registration}" title="{_please_enter_the_security_code_shown_on_the_image_to_continue_registration}" border="0" />';
                        }
                        else
                        {
                                $captcha = '<div class="yellowhlite" style="width:95px; float:left"><h1 style="color:#ff6600; text-align: center">' . $_SESSION['ilancedata']['user']['captcha'] . '</h1></div>';
                        }
			$headinclude .= '
<script type="text/javascript">
<!--
function verifynotify(field1, field2, result_id, match_html, nomatch_html)
{
        this.field1 = field1;
        this.field2 = field2;
        this.result_id = result_id;
        this.match_html = match_html;
        this.nomatch_html = nomatch_html;
        this.check = function() 
        {
                if (!this.result_id) 
                {	 
                        return false; 
                }
                if (!document.getElementById)
                { 
                        return false; 
                }
                r = fetch_js_object(this.result_id);
                if (!r)
                { 
                        return false; 
                }

                if (this.field1.value != "" && this.field1.value == this.field2.value) 
                {
                    r.innerHTML = this.match_html;
                } 
                else 
                {
                    r.innerHTML = this.nomatch_html;
                }
        }
}
function register1(f)
{
        haveerrors = 0;
        (f.username.value.length < 1) ? showImage("usernameerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("usernameerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        (f.roleid.value.length < 1) ? showImage("roleerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("roleerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
	(f.password.value.length < 1) ? showImage("passworderror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("passworderror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        (f.password2.value.length < 1) ? showImage("password2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("password2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        (f.secretquestion.value.length < 1) ? showImage("secretquestionerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("secretquestionerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        (f.secretanswer.value.length < 1) ? showImage("secretanswererror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("secretanswererror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        (f.email.value.search("@") == -1 || f.email.value.search("[.*]") == -1) ? showImage("emailerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("emailerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        (f.email2.value.search("@") == -1 || f.email2.value.search("[.*]") == -1) ? showImage("email2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("email2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        ' . ($ilconfig['registrationdisplay_turingimage'] ? '(f.turing.value.length < 1) ? showImage("turingerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("turingerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);' : '') . '
        return (!haveerrors);
}
//-->
</script>
';

$js = '
<script type="text/javascript">
<!--
verify = new verifynotify();
verify.field1 = fetch_js_object(\'password\');
verify.field2 = fetch_js_object(\'password2\');
verify.result_id = "password_result";
verify.match_html = "<span style=\"color:blue\"><img src=\"' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif\" border=\"0\" alt=\"\" /></span>";
verify.nomatch_html = "";
verify.check();
//-->
</script>
';            
                        // custom registration questions [page 1]
                        $customquestions = $ilance->registration_questions->construct_register_questions(1, 'input', 0, $ilance->GPC['roleid']);
                        $roleselected = isset($ilance->GPC['roleid']) ? intval($ilance->GPC['roleid']) : $ilconfig['subscriptions_defaultroleid'];
                        $rolepulldown = $ilance->subscription_role->print_role_pulldown($roleselected, 0, 1, 0, 'onchange="show_questions(this)"');
                        
	$headinclude .= '
<script type="text/javascript">
<!--
if (!window.XMLHttpRequest)
{
        var reqObj = 
        [
                function() {return new ActiveXObject("Msxml2.XMLHTTP");},
                function() {return new ActiveXObject("Microsoft.XMLHTTP");},
                function() {return window.createRequest();}
        ];
        for(a = 0, z = reqObj.length; a < z; a++)
        {
                try
                {
                        window.XMLHttpRequest = reqObj[a];
                        break;
                }
                catch(e)
                {
                        window.XMLHttpRequest = null;
                }
        }
}
function show_questions(selectobj)
{
	var req = new XMLHttpRequest();
	var role = ' . intval($roleselected) . ';
	req.open(\'GET\', \'' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['ajax'] . '?do=registrationquestions&roleid=\' + role);
	req.send(null);
	var role;
	role = selectobj.options[selectobj.selectedIndex].value;
	req.open(\'GET\', \'' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['ajax'] . '?do=registrationquestions&roleid=\' + role);
	req.send(null); 
	req.onreadystatechange = function()
	{
		if (req.readyState == 4 && req.status == 200)
		{
			var myString;
			myString = req.responseText;
			obj = fetch_js_object(\'customquestions\');
			obj.innerHTML = myString;
		}
	}
}
//-->
</script>';                    
                        $pprint_array = array('defaultroleid','rolepulldown','customquestions','password','password2','secretquestion','secretanswer','js','captcha','username','email','email2','rid');
                        $ilance->template->fetch('main', 'register1.html');
                        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                        $ilance->template->parse_if_blocks('main');
                        $ilance->template->pprint('main', $pprint_array);
                        exit();
                }
                else
                {
                        // ########### STEP 3 ##################################
                        $navcrumb = array();
                        $navcrumb["$ilpage[registration]"] = '{_registration}';
                        $navcrumb[""] = '{_contact}';
                        $error_firstname = $error_lastname = $error_phone = $error_address = $error_city = $error_zipcode = 0;
                        $first_name = $last_name = $address = $address2 = $city = $zipcode = $companyname = '';
                        $jscity = $ilconfig['registrationdisplay_defaultcity'];
			$city = $ilconfig['registrationdisplay_defaultcity'];
                        $formid = 'forms[1]';
                        $countryid = fetch_country_id($ilconfig['registrationdisplay_defaultcountry'], $_SESSION['ilancedata']['user']['slng']);
                        $country_js_pulldown = $ilance->common_location->construct_country_pulldown($countryid, $ilconfig['registrationdisplay_defaultcountry'], 'country', false, 'state', false, false, false, 'stateid', false, '', '', '', '', false, false, '', 0, 'city', 'cityid');
			$state_js_pulldown = '<span id="stateid">' . $ilance->common_location->construct_state_pulldown($countryid, $ilconfig['registrationdisplay_defaultstate'], 'state', false, false, 0, '', 0, 'city', 'cityid') . '</span>';
			$city_js_pulldown = '<span id="cityid">' . $ilance->common_location->construct_city_pulldown($ilconfig['registrationdisplay_defaultstate'], 'city', $ilconfig['registrationdisplay_defaultcity'], false, false, '') . '</span>';
                        $currency_pulldown = $ilance->currency->pulldown('registration');
			$selectedtz = isset($ilance->GPC['timezone']) ? $ilance->GPC['timezone'] : $ilconfig['globalserverlocale_sitetimezone'];
                        $timezone_pulldown = $ilance->datetimes->timezone_pulldown('timezone', $selectedtz, true, true);
                        $show['us_phone_format'] = ($ilconfig['registrationdisplay_phoneformat'] == 'US') ? true : false;
                        $customquestions = $ilance->registration_questions->construct_register_questions(2, 'input', 0, $_SESSION['ilancedata']['user']['roleid']);
                        if ($ilconfig['genderactive'])
			{
				$cb_gender_undecided = 'checked="checked"';
				$cb_gender_male = $cb_gender_female = '';	
			} 
			$phone1 = $phone2 = $phone3 = $phone4 = '';    
                        $headinclude .= '
<script type="text/javascript">
<!--
function register2(f)
{
        haveerrors = 0;
        (f.first_name.value.length < 1) ? showImage("first_nameerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("first_nameerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        (f.last_name.value.length < 1) ? showImage("last_nameerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("last_nameerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        (f.address.value.length < 1) ? showImage("addresserror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("addresserror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        (f.city.value.length < 1) ? showImage("cityerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("cityerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        (f.zipcode.value.length < 1) ? showImage("zipcodeerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("zipcodeerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        ' . (($ilconfig['registrationdisplay_phoneformat'] == 'US')
		? '(f.phone1.value.length < 1 || f.phone2.value.length < 1 || f.phone3.value.length < 1 || f.phone4.value.length < 1) ? showImage("phoneerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("phoneerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);'
		: '(f.phone1.value.length < 2) ? showImage("phoneerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("phoneerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);' ) .'
        return (!haveerrors);
}
//-->
</script>
';
                        $pprint_array = array('city_js_pulldown','companyname_checkbox','cb_gender_undecided','cb_gender_male','cb_gender_female','companyname','service_newsletter','product_newsletter','customquestions','address','address2','city','zipcode','onsubmit','onload','currency_pulldown','language_pulldown','timezone_pulldown','onsubmit','dynamic_js_bodyend','state_js_pulldown','country_js_pulldown','js','first_name','last_name','input_style','remote_addr','rid','phone1','phone2','phone3','phone4');

                        ($apihook = $ilance->api('register2_end')) ? eval($apihook) : false;
                        
                        $ilance->template->fetch('main', 'register2.html');
                        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                        $ilance->template->parse_if_blocks('main');
                        $ilance->template->pprint('main', $pprint_array);
                        exit();
                }
        }
        // ########### STEP 3 ##################################################
        else if ($ilance->GPC['step'] == '3')
        {
		if (empty($_SESSION['ilancedata']['user']['username']) OR empty($_SESSION['ilancedata']['user']['agreeterms']))
		{
		       $area_title = '{_your_session_has_expired_please_login}';
		       $page_title = SITE_NAME . ' - '.'{_your_session_has_expired_please_login}';
		       $navcrumb = array("$ilpage[main]" => '{_your_session_has_expired_please_login}');
		       print_notice('{_your_session_has_expired_please_login}', '{_either_your_session_has_expired_or_you_are_a_guest_attempting_to_access_a_member_resource}', $ilpage['registration']."?cmd=register&amp;step=1", '{_register_to_login_here}');
		       exit();
		}
		$navcrumb = array();
		$navcrumb["$ilpage[registration]"] = '{_registration}';
		$navcrumb[""] = '{_user_registration}';
		$error_firstname = $error_lastname = $error_phone = $error_address = $error_city = $error_zipcode = 0;
                 // #### handle custom questions in this section ################
                if (isset($ilance->GPC['custom']) AND is_array($ilance->GPC['custom']))
                {
                        foreach ($ilance->GPC['custom'] AS $questionid => $answerarray)
                        {
                                foreach ($answerarray AS $formname => $answer)
                                {
                                        $checkanswer = $ilance->db->query("
						SELECT formdefault, formname, questionid, inputtype, required
						FROM " . DB_PREFIX . "register_questions
						WHERE formname = '" . $ilance->db->escape_string($formname) . "'
							AND visible = '1'
					");
					if ($ilance->db->num_rows($checkanswer) > 0)
					{
						$row = $ilance->db->fetch_array($checkanswer, DB_ASSOC);
						if (empty($answer) AND $answer != '0' AND $row['required'] == '1')
						{
							$show['error_register_questions'] = true;
						}
						else 
						{
							$_SESSION['ilancedata']['questions'][$formname] = handle_input_keywords($answer);
						}
					}
                                }
                        }
                }
                // set desired language for new user we'll be inserting into db as
                $_SESSION['ilancedata']['preferences']['languageid'] = $_SESSION['ilancedata']['user']['languageid'];
		if ($ilconfig['registrationdisplay_phoneformat'] == 'US')
		{
			$show['us_phone_format'] = true;
			if (empty($ilance->GPC['phone1']) OR empty($ilance->GPC['phone2']) OR empty($ilance->GPC['phone3']) OR empty($ilance->GPC['phone4']))
			{
				$error_phone = 1;
			}
			else
			{
				$_SESSION['ilancedata']['user']['phone'] = trim($ilance->GPC['phone1'] . $ilance->GPC['phone2'] . $ilance->GPC['phone3'] . $ilance->GPC['phone4']);
				$phone1 = str_replace('+','',$ilance->GPC['phone1']);
				$phone2 = $ilance->GPC['phone2'];
				$phone3 = $ilance->GPC['phone3'];
				$phone4 = $ilance->GPC['phone4'];
			}
		}
		else
		{
			$show['us_phone_format'] = false;
			if (empty($ilance->GPC['phone1']))
			{
				$error_phone = 1;
			}
			else
			{
				$_SESSION['ilancedata']['user']['phone'] = trim($ilance->GPC['phone1']);
				$phone1 = str_replace('+','',$ilance->GPC['phone1']);
			}
		}
		if (isset($ilance->GPC['first_name']) AND !empty($ilance->GPC['first_name']))
		{
		       $_SESSION['ilancedata']['user']['firstname'] = ucwords($ilance->GPC['first_name']);
		       $first_name = $_SESSION['ilancedata']['user']['firstname'];
		}
		else
		{
		       $error_firstname = 1;
		       $first_name = '';
		}
		if (isset($ilance->GPC['last_name']) AND !empty($ilance->GPC['last_name']))
		{
		       $_SESSION['ilancedata']['user']['lastname'] = ucwords($ilance->GPC['last_name']);
		       $last_name = $_SESSION['ilancedata']['user']['lastname'];
		}
		else
		{
		       $error_lastname = 1;
		       $last_name = '';
		}
		if (isset($ilance->GPC['address']) AND !empty($ilance->GPC['address']))
		{
		       $_SESSION['ilancedata']['user']['address'] = ucwords($ilance->GPC['address']);
		       $address = $_SESSION['ilancedata']['user']['address'];
		}
		else
		{
		       $error_address = 1;
		       $address = '';
		}
		if (isset($ilance->GPC['address2']) AND !empty($ilance->GPC['address2']))
		{
		       $_SESSION['ilancedata']['user']['address2'] = ucwords($ilance->GPC['address2']);
		       $address2 = $_SESSION['ilancedata']['user']['address2'];
		}
		else
		{
		       $_SESSION['ilancedata']['user']['address2'] = '';
		       $address2 = '';
		}
		if (isset($ilance->GPC['city']) AND !empty($ilance->GPC['city']))
		{
		       $_SESSION['ilancedata']['user']['city'] = ucwords($ilance->GPC['city']);
		       $city = $_SESSION['ilancedata']['user']['city'];
		}
		else
		{
		       $error_city = 1;
		       $city = $ilconfig['registrationdisplay_defaultcity'];
		}
		if (isset($ilance->GPC['zipcode']) AND !empty($ilance->GPC['zipcode']))
		{
		       $_SESSION['ilancedata']['user']['zipcode'] = trim($ilance->GPC['zipcode']);
		       $zipcode = $_SESSION['ilancedata']['user']['zipcode'];
		}
		else
		{
		       $error_zipcode = 1;
		       $zipcode = '';
		}
		if (isset($ilance->GPC['country']) AND !empty($ilance->GPC['country']))
		{
		       $_SESSION['ilancedata']['user']['country'] = trim($ilance->GPC['country']);
		       $_SESSION['ilancedata']['user']['countryid'] = fetch_country_id($_SESSION['ilancedata']['user']['country'], $_SESSION['ilancedata']['user']['slng']);
		       $country = $_SESSION['ilancedata']['user']['country'];
		}
		else
		{
		       $country = '';
		}
		if (isset($ilance->GPC['state']) AND !empty($ilance->GPC['state']))
		{
		       $_SESSION['ilancedata']['user']['state'] = $ilance->GPC['state'];
		       $state = $_SESSION['ilancedata']['user']['state'];
		}
		else
		{
		       $state = '';
		}
		// build user preferences
		$_SESSION['ilancedata']['preferences']['notifyservicescats'] = '';
		$_SESSION['ilancedata']['preferences']['notifyproductscats'] = '';
		$_SESSION['ilancedata']['preferences']['notifyservices'] = 0;
		$_SESSION['ilancedata']['preferences']['notifyproducts'] = 0;
		$notifyservicescats = $selectedcats = $notifyproductscats = $selected2cats = '';
		// currency
		if (isset($ilance->GPC['currencyid']) AND $ilance->GPC['currencyid'] > 0)
		{
		       $_SESSION['ilancedata']['preferences']['currencyid'] = intval($ilance->GPC['currencyid']);
		}
		// timezone
		if (isset($ilance->GPC['timezone']) AND $ilance->GPC['timezone'] != '')
		{
		       $_SESSION['ilancedata']['preferences']['usertimezone'] = trim($ilance->GPC['timezone']);
		}
		else
		{
		       $_SESSION['ilancedata']['preferences']['usertimezone'] = $ilconfig['globalserverlocale_sitetimezone'];
		}
		// company name
		if (isset($ilance->GPC['companyname']) AND !empty($ilance->GPC['companyname']))
		{
		       $_SESSION['ilancedata']['preferences']['companyname'] = handle_input_keywords(trim($ilance->GPC['companyname']));
		       $companyname = $_SESSION['ilancedata']['preferences']['companyname'];
		}
		else
		{
		       $_SESSION['ilancedata']['preferences']['companyname'] = '';
		       $companyname = '';
		}
		$_SESSION['ilancedata']['user']['gender'] = (isset($ilance->GPC['gender'])) ?  $ilance->GPC['gender'] : '';
		if (isset($error_firstname) AND $error_firstname OR isset($error_lastname) AND $error_lastname OR isset($error_address) AND $error_address OR isset($error_city) AND $error_city OR isset($error_zipcode) AND $error_zipcode OR isset($error_phone) AND $error_phone OR isset($show['error_register_questions']) AND $show['error_register_questions'])
		{
                        // ########### ERRORS: BACK TO STEP 2 ##################
                        $navcrumb = array();
                        $navcrumb["$ilpage[registration]"] = '{_registration}';
                        $navcrumb[""] = '{_contact}';
                        $jscity = $ilconfig['registrationdisplay_defaultcity'];
			$city = $ilconfig['registrationdisplay_defaultcity'];
                        $formid = 'forms[1]';
                        $countryid = fetch_country_id($ilance->GPC['country'], $_SESSION['ilancedata']['user']['slng']);
			$country_js_pulldown = $ilance->common_location->construct_country_pulldown($countryid, $ilconfig['registrationdisplay_defaultcountry'], 'country', false, 'state', false, false, false, 'stateid', false, '', '', '', '', false, false, '', 0, 'city', 'cityid');
			$state_js_pulldown = '<span id="stateid">' . $ilance->common_location->construct_state_pulldown($countryid, $ilconfig['registrationdisplay_defaultstate'], 'state', false, false, 0, '', 0, 'city', 'cityid') . '</span>';
			$city_js_pulldown = '<span id="cityid">' . $ilance->common_location->construct_city_pulldown($ilconfig['registrationdisplay_defaultstate'], 'city', $ilconfig['registrationdisplay_defaultcity'], false, false, '') . '</span>';
			$currency_pulldown = $ilance->currency->pulldown('registration');
			$selectedtz = isset($ilance->GPC['timezone']) ? $ilance->GPC['timezone'] : $ilconfig['globalserverlocale_sitetimezone'];
                        $timezone_pulldown = $ilance->datetimes->timezone_pulldown('timezone', $selectedtz, true, true);
                        $customquestions = $ilance->registration_questions->construct_register_questions(2, 'input', 0, $_SESSION['ilancedata']['user']['roleid']);
                        $headinclude .= '<script type="text/javascript" type="text/javascript">
<!--
function register2(f)
{
        haveerrors = 0;
        (f.first_name.value.length < 1) ? showImage("first_nameerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("first_nameerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        (f.last_name.value.length < 1) ? showImage("last_nameerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("last_nameerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        (f.address.value.length < 1) ? showImage("addresserror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("addresserror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        (f.city.value.length < 1) ? showImage("cityerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("cityerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        (f.zipcode.value.length < 1) ? showImage("zipcodeerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("zipcodeerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        '.(($ilconfig['registrationdisplay_phoneformat'] == 'US')
	? '(f.phone1.value.length < 1 || f.phone2.value.length < 3 || f.phone3.value.length < 3 || f.phone4.value.length < 4) ? showImage("phoneerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("phoneerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);'
	: '(f.phone1.value.length < 1) ? showImage("phoneerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("phoneerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);' ) . '
        return (!haveerrors);
}
//-->
</script>
';                      if ($ilconfig['genderactive'])
			{
				if ($ilance->GPC['gender'] == '')
				{
					$cb_gender_undecided = 'checked="checked"';
					$cb_gender_male = $cb_gender_female = '';
				}
				else
				{
					if ($ilance->GPC['gender'] == 'male')
					{
						$cb_gender_undecided = $cb_gender_female = '';
						$cb_gender_male = 'checked="checked"';
					}
					else if ($ilance->GPC['gender'] == 'female')
					{
						$cb_gender_undecided = $cb_gender_male = '';
						$cb_gender_female = 'checked="checked"';
					}
				}
			}
                        $pprint_array = array('city_js_pulldown','companyname','cb_gender_undecided','cb_gender_male','cb_gender_female','service_newsletter','product_newsletter','customquestions','address','address2','city','zipcode','onsubmit','onload','currency_pulldown','language_pulldown','timezone_pulldown','onsubmit','dynamic_js_bodyend','state_js_pulldown','country_js_pulldown','js','first_name','last_name','input_style','remote_addr','rid','phone1','phone2','phone3','phone4');
                        
                        ($apihook = $ilance->api('register2_end')) ? eval($apihook) : false;
                        
                        $ilance->template->fetch('main', 'register2.html');
                        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                        $ilance->template->parse_if_blocks('main');
                        $ilance->template->pprint('main', $pprint_array);
                        exit();
                 }
                 else
                 {
                        $navcrumb = array();
                        $navcrumb["$ilpage[registration]"] = '{_registration}';
                        $navcrumb[""] = '{_subscription}';
                        $slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';
                        // fetch subscription plans within selected role type customer selected on page 1
                        $result = $ilance->db->query("
                                SELECT subscriptionid, title_" . $slng . " AS title, description_" . $slng . " AS description, cost, length, units, subscriptiongroupid, roleid
                                FROM " . DB_PREFIX . "subscription
                                WHERE visible_registration = '1'
					AND roleid = '" . $_SESSION['ilancedata']['user']['roleid'] . "'
                                ORDER BY cost ASC
			");
                        if ($ilance->db->num_rows($result) > 0)
                        {
                                $row_count = 0;
                                while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
                                {
                                        if ($row['roleid'] == $_SESSION['ilancedata']['user']['roleid'])
                                        {
                                                $row['class'] = 'featured_highlight';
                                                $row['action'] = '<input type="radio" name="subscriptionid" value="' . $row['subscriptionid'] . '" checked="checked" />';
                                        }
                                        else
                                        {
                                                $row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
                                                $row['action'] = '<input type="radio" name="subscriptionid" value="' . $row['subscriptionid'] . '" />';
                                        }
                                        $row['title'] = stripslashes($row['title']);
                                        $row['description'] = stripslashes($row['description']);
                                        $row['cost'] = ($row['cost'] > 0) ? print_currency_conversion($_SESSION['ilancedata']['preferences']['currencyid'], $row['cost']) : '{_free}';
                                        $row['units'] = print_unit($row['units']);
                                        $row['access'] = '<a href="javascript:void(0)" onclick=Attach("' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['subscription'] . '?cmd=access&gid=' . $row['subscriptiongroupid'] . '&id=' . $row['subscriptionid'] . '&s=' . session_id() . '")>{_view_access}</a>';
                                        $row['roletype'] = $ilance->subscription_role->print_role($row['roleid']);
                                        $subscription[] = $row;
                                        $row_count++;
                                }
                                $show['no_subscription'] = false;
                        }
                        else
                        {
                                $show['no_subscription'] = true;
                        }
                        $customquestions = $ilance->registration_questions->construct_register_questions(3, 'input', 0, $_SESSION['ilancedata']['user']['roleid']);
                        $headinclude .= '
<script type="text/javascript">
<!--
function subscription_check()
{
        var radio_choice = false;
        if (document.forms[1].subscriptionid.length == undefined)
        {
                // single subscription plan detected
                if (document.forms[1].subscriptionid.checked == true)
                {
                        radio_choice = true;
                }
        }
        else
        {
                // multiple subscription plans to choose from
                for (counter = 0; counter < document.forms[1].subscriptionid.length; counter++)
                {
                        if (document.forms[1].subscriptionid[counter].checked)
                        {
                                radio_choice = true;
                        }
                }
        }
        if (!radio_choice)
        {
                alert_js(phrase[\'_you_did_not_select_a_subscription_plan_above\'])
                return (false);
        }
        return (true);
}
//-->
</script>
';                        
                        // expire any existing member cookies so our next page can create fresh ones
                        set_cookie('userid', '', false);
                        set_cookie('password', '', false);
                        set_cookie('username', '', false);
                        
                        ($apihook = $ilance->api('register3_end')) ? eval($apihook) : false;
                        
                        $pprint_array = array('customquestions','payment_method_pulldown','onload','js','input_style');
                        $ilance->template->fetch('main', 'register3.html');
                        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                        $ilance->template->parse_loop('main', 'subscription');
                        $ilance->template->parse_if_blocks('main');
                        $ilance->template->pprint('main', $pprint_array);
                        exit();
                }
        }
        // ########### STEP 4 ##################################################
        else if ($ilance->GPC['step'] == '4')
        {
                if (empty($_SESSION['ilancedata']['user']['username']))
                {
                        $area_title = '{_your_session_has_expired_please_login}';
                        $page_title = SITE_NAME . ' - {_your_session_has_expired_please_login}';
                        $navcrumb = array("$ilpage[main]" => '{_your_session_has_expired_please_login}');
                        print_notice('{_your_session_has_expired_please_login}', '{_either_your_session_has_expired_or_you_are_a_guest_attempting_to_access_a_member_resource}', $ilpage['registration']."?cmd=register&amp;step=1", '{_register_to_login_here}');
                        exit();
                }
                // #### handle custom questions in this section ################
                if (isset($ilance->GPC['custom']) AND is_array($ilance->GPC['custom']))
                {
                        foreach ($ilance->GPC['custom'] AS $questionid => $answerarray)
                        {
                                foreach ($answerarray AS $formname => $answer)
                                {
                                        $checkanswer = $ilance->db->query("
						SELECT formdefault, formname, questionid, inputtype, required
						FROM " . DB_PREFIX . "register_questions
						WHERE formname = '" . $ilance->db->escape_string($formname) . "'
							AND visible = '1'
					");
					if ($ilance->db->num_rows($checkanswer) > 0)
					{
						$row = $ilance->db->fetch_array($checkanswer, DB_ASSOC);
						if (empty($answer) AND $answer != '0' AND $row['required'] == '1')
						{
							$show['error_register_questions'] = true;
						}
						else 
						{
							$_SESSION['ilancedata']['questions'][$formname] = handle_input_keywords($answer);
						}
					}
                                }
                        }
                }
                if (isset($show['error_register_questions']) AND $show['error_register_questions'])
                {
                        $navcrumb = array();
                        $navcrumb["$ilpage[registration]"] = '{_registration}';
                        $navcrumb[""] = '{_subscription}';
                        $slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';  
                        $result = $ilance->db->query("
                                SELECT subscriptionid, title_" . $slng . " as title, description_" . $slng . " as description, cost, length, units, subscriptiongroupid, roleid
                                FROM " . DB_PREFIX . "subscription
                                WHERE visible_registration = '1'
					AND roleid = '" . $_SESSION['ilancedata']['user']['roleid'] . "'
                                ORDER BY cost ASC
			");
                        if ($ilance->db->num_rows($result) > 0)
                        {
                                $row_count = 0;
                                while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
                                {
                                        if ($row['roleid'] == $_SESSION['ilancedata']['user']['roleid'])
                                        {
                                                $row['class'] = 'featured_highlight';
                                                $row['action'] = '<input type="radio" name="subscriptionid" value="' . $row['subscriptionid'] . '" checked="checked" />';
                                        }
                                        else
                                        {
                                                $row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
                                                $row['action'] = '<input type="radio" name="subscriptionid" value="' . $row['subscriptionid'] . '" />';
                                        }
                                        $row['title'] = stripslashes($row['title']);
                                        $row['description'] = stripslashes($row['description']);
                                        $row['cost'] = ($row['cost'] > 0) ? print_currency_conversion($_SESSION['ilancedata']['preferences']['currencyid'], $row['cost']) : '{_free}';
                                        $row['units'] = print_unit($row['units']);
                                        $row['access'] = '<a href="javascript:void(0)" onclick=Attach("' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['subscription'] . '?cmd=access&gid=' . $row['subscriptiongroupid'] . '&id=' . $row['subscriptionid'] . '&s=' . session_id() . '")>{_view_access}</a>';
                                        $row['roletype'] = $ilance->subscription_role->print_role($row['roleid']);
                                        $subscription[] = $row;
                                        $row_count++;
                                }
                                $show['no_subscription'] = false;
                        }
                        else
                        {
                                $show['no_subscription'] = true;
                        }
                        // custom registration questions [page 3]
                        $customquestions = $ilance->registration_questions->construct_register_questions(3, 'input', 0, $_SESSION['ilancedata']['user']['roleid']);
                        $headinclude .= '
<script type="text/javascript">
<!--
function subscription_check()
{
        var radio_choice = false;
        if (document.forms[1].subscriptionid.length == undefined)
        {
                // single subscription plan detected
                if (document.forms[1].subscriptionid.checked == true)
                {
                        radio_choice = true;
                }
        }
        else
        {
                // multiple subscription plans to choose from
                for (counter = 0; counter < document.forms[1].subscriptionid.length; counter++)
                {
                        if (document.forms[1].subscriptionid[counter].checked)
                        {
                                radio_choice = true;
                        }
                }
        }
        if (!radio_choice)
        {
                alert_js(phrase[\'_you_did_not_select_a_subscription_plan_above\'])
                return (false);
        }
        return (true);
}
//-->
</script>
';                        
                        // expire any existing member cookies so our next page can create fresh ones
                        set_cookie('userid', '', false);
                        set_cookie('password', '', false);
                        set_cookie('username', '', false);
                        
                        ($apihook = $ilance->api('register3_end')) ? eval($apihook) : false;
                        
                        $pprint_array = array('customquestions','payment_method_pulldown','onload','js','input_style');
                        
                        $ilance->template->fetch('main', 'register3.html');
                        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                        $ilance->template->parse_loop('main', 'subscription');
                        $ilance->template->parse_if_blocks('main');
                        $ilance->template->pprint('main', $pprint_array);
                        exit();
                }
                else 
                {
	                // build subscription plan session
	                if (isset($ilance->GPC['subscriptionid']) AND $ilance->GPC['subscriptionid'] > 0)
	                {
	                        $_SESSION['ilancedata']['subscription']['subscriptionid'] = intval($ilance->GPC['subscriptionid']);
	                        $_SESSION['ilancedata']['subscription']['subscriptionpaymethod'] = mb_strtolower(trim($ilance->GPC['paymethod']));
	                }
	                else
	                {
	                        $_SESSION['ilancedata']['subscription']['subscriptionid'] = '1';
	                        $_SESSION['ilancedata']['subscription']['subscriptionpaymethod'] = 'account';
	                }       
	                // support promotional code feature
	                if (!empty($ilance->GPC['promocode']))
	                {
	                        $_SESSION['ilancedata']['subscription']['promocode'] = handle_input_keywords(trim($ilance->GPC['promocode']));
	                }
	                else
	                {
	                        $_SESSION['ilancedata']['subscription']['promocode'] = '';
	                }
	                $navcrumb = array();
	                $navcrumb["$ilpage[registration]"] = '{_registration}';
	                $navcrumb[""] = '{_message}';
	                // find out if we had any questions to answer
	                if (empty($_SESSION['ilancedata']['questions']))
	                {
	                        $_SESSION['ilancedata']['questions'] = array();    
	                }       
	                // notes: you may send 3 custom arguments:
	                // return_userid        : returns only the new user ID
	                // return_userstatus    : returns the new users status (login status, active, unverified, etc)
	                // return_userarray     : returns the full user array of the created member
	                $dowhat = 'return_userarray';
	                $final = $ilance->registration->build_user_datastore($_SESSION['ilancedata']['user'], $_SESSION['ilancedata']['preferences'], $_SESSION['ilancedata']['subscription'], $_SESSION['ilancedata']['questions'], $dowhat);
	                if (!empty($final))
	                {
	                        set_cookie('userid', $ilance->crypt->three_layer_encrypt($final['userid'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']));
	                        set_cookie('username', $ilance->crypt->three_layer_encrypt($final['username'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']));
	                        set_cookie('password', $ilance->crypt->three_layer_encrypt($final['password'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']));
	                        set_cookie('lastvisit', DATETIME24H);
	                        set_cookie('lastactivity', DATETIME24H);
	                        switch ($final['status'])
	                        {
	                                case 'active':
	                                {
	                                        // make sure we have a valid password session
	                                        if (!empty($_SESSION['ilancedata']['user']['password_md5']))
	                                        {
	                                                $_SESSION['ilancedata']['user']['password'] = $_SESSION['ilancedata']['user']['password_md5'];
	                                                unset($_SESSION['ilancedata']['user']['password_md5']);
	                                        }
	                                        // display final registration information
	                                        print_notice('{_registration_complete}', '{_thank_you_your_registration_is_now_complete}', (isset($final['redirect'])) ? $final['redirect'] : $ilpage['main'] . '?cmd=cp',(isset($final['redirect'])) ? '{_search}' : '{_my_cp}');
	                                        break;
	                                }                            
	                                case 'unverified':
	                                {
	                                        // display email link code information
	                                        print_notice('{_registration_not_completed}', '{_thank_you_for_registering_an_email_has_been_dispatched_to_you}',(isset($final['redirect'])) ?  $ilpage['login'].'?redirect='.urlencode($final['redirect']) :  $ilpage['login'], '{_sign_in}');
	                                        break;
	                                }
	                                case 'moderated':
	                                {
	                                        // display email link code information
	                                        print_notice('{_registration_complete}', '{_thank_you_your_registration_is_now_complete_and_is_pending_verification}', $ilpage['main'], '{_main_menu}');
	                                        break;
	                                }
	                        }
				if (isset($_SESSION['ilancedata']['user']['new_redirect']))
				{
					unset($_SESSION['ilancedata']['user']['new_redirect']);
				}
	                        exit();
	                }
	                else
	                {
	                        print_notice('{_registration_error_occured}', '{_were_sorry_we_only_allow_forms_to_be_securely_processed_via_our_web_site}', $ilpage['registration'] . '?cmd=register&step=1', '{_register_to_login_here}');
	                        exit();
	                }
                }
        }
}
else
{
	$area_title = '{_registration}<div class="smaller">{_terms_and_agreements}</div>';
        $page_title = SITE_NAME . ' - {_registration_terms_and_agreements_review}';
        $navcrumb = array();
        $navcrumb["$ilpage[registration]"] = '{_registration}';
        $navcrumb[""] = '{_terms_and_agreements}';
	$registration1 = $ilance->db->fetch_field(DB_PREFIX . "cms", "", "registrationterms");
	$yearpulldown = pulldown_year();
	$arr_day = array('' => '-');
	for ($i = 1 ; $i <= 31 ; $i++)
	{
		$val = ($i < 10) ? '0'.$i : $i;
		$arr_day[$val] = $i;
	}
	$daypulldown = construct_pulldown('day', 'day', $arr_day, '', 'style="font-family: verdana"');
	$arr_month = array('' => '-', '01' => '{_january}', '02' => '{_february}', '03' => '{_march}', '04' => '{_april}', '05' => '{_may}', '06' => '{_june}', '07' => '{_july}', '08' => '{_august}', '09' => '{_september}', '10' => '{_october}', '11' => '{_november}', '12' => '{_december}');
        $monthpulldown = construct_pulldown('month', 'month', $arr_month, '', 'style="font-family: verdana"');
	unset($arr_day, $arr_month);
	$pprint_array = array('registration1','yearpulldown', 'daypulldown', 'monthpulldown');
        $ilance->template->fetch('main', 'registration.html');
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