<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # Facebook Bridge 1.0.2 Build 52
|| # -------------------------------------------------------------------- # ||
|| # Customer License # V93VJ4rAz1cCyXo
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000-2014 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/

/**
* Facebook Bridge class to perform the majority of authenication and login functions for ILance.
*
* @package      iLance
* @version      1.0.2.52
* @author       ILance
*/
class fbbridge
{
	var $config = array();	
	var $modgroup = 'fbbridge';
	var $conn;
	var $session;
	var $uid;
	var $me;
	var $fbmlButton = '';
	function fbbridge()
	{
		global $ilance, $show;	
		$query = $ilance->db->query("
			SELECT configtable, version
			FROM " . DB_PREFIX . "modules_group
			WHERE modulegroup = '" . $ilance->db->escape_string($this->modgroup) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($query) > 0)
		{
			$table = $ilance->db->fetch_array($query, DB_ASSOC);
			if (!empty($table['configtable']))
			{
				$sql = $ilance->db->query("
					SELECT name, value
					FROM " . DB_PREFIX . $table['configtable'],
				0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
					{
						$this->config[$res['name']] = $res['value'];
					}
					unset($res);
					$this->config['version'] = $table['version'];
				}
			}
		}
		if (isset($this->config['enabled']) AND $this->config['enabled'])
		{
			require_once(DIR_CORE . 'functions_facebook.php');
			$this->conn = new Facebook(array(  
				'appId'  => $this->config['appId'],  
				'secret' => $this->config['secret'],
				'cookie' => true)
			);
			$this->initialize();
		}
	}
	
	function initialize()
	{
		$this->uid = $this->conn->getUser();
		if (isset($this->uid) AND $this->uid) 
		{  
			try 
			{    				
				$this->me = $this->conn->api('/me');
			} 
			catch (FacebookApiException $e) 
			{   
				error_log($e);  
			}
		}
	}
	
	function login_via_facebookid($id = '')
	{
		global $ilance, $ilconfig;
		$userinfo['roleid'] = -1;
		$userinfo['subscriptionid'] = $userinfo['cost'] = 0;
		$userinfo['active'] = 'no';
		$sql = $ilance->db->query("
			SELECT u.*, su.roleid, su.subscriptionid, su.active, sp.cost, c.currency_name, c.currency_abbrev, l.languagecode
			FROM " . DB_PREFIX . "users AS u
			LEFT JOIN " . DB_PREFIX . "subscription_user su ON u.user_id = su.user_id
			LEFT JOIN " . DB_PREFIX . "subscription sp ON su.subscriptionid = sp.subscriptionid
			LEFT JOIN " . DB_PREFIX . "currency c ON u.currencyid = c.currency_id
			LEFT JOIN " . DB_PREFIX . "language l ON u.languageid = l.languageid
			WHERE u.facebookid = '" . $ilance->db->escape_string($id) . "'
			GROUP BY u.facebookid
			LIMIT 1
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$userinfo = $ilance->db->fetch_array($sql, DB_ASSOC);
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "users
				SET lastseen = '" . DATETIME24H . "'
				WHERE user_id = '" . $userinfo['user_id'] . "'
				LIMIT 1
			");
			if ($userinfo['status'] == 'active')
			{
				if ($userinfo['iprestrict'] AND !empty($userinfo['ipaddress']))
				{
					if (IPADDRESS != $userinfo['ipaddress'])
					{
						refresh(HTTPS_SERVER . (($ilconfig['globalauctionsettings_seourls']) ? 'signin' : $ilpage['login']) . '?error=iprestrict');	
						exit();	
					}
				}
				$ilance->sessions->build_user_session($userinfo);
				set_cookie('userid', $ilance->crypt->three_layer_encrypt($userinfo['user_id'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']));
				set_cookie('password', $ilance->crypt->three_layer_encrypt($userinfo['password'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']));
				set_cookie('username', $ilance->crypt->three_layer_encrypt($userinfo['username'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']));
				set_cookie('lastvisit', DATETIME24H);
				set_cookie('lastactivity', DATETIME24H);
			}
			return true;
		}
		return false;
	}
	
	function print_fbbutton()
	{
		global $ilance, $show, $ilconfig, $ilpage, $headinclude;
		$this->fbmlButton = '<button id="fb-auth" style=""></button>';
		$url = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER);
		$motd = $ilance->db->fetch_field(DB_PREFIX . "motd", "date = '" . DATETODAY . "'", "content");
		if ((!empty($_COOKIE[COOKIE_PREFIX . 'motd']) AND $_COOKIE[COOKIE_PREFIX . 'motd'] != DATETODAY AND !empty($motd) AND $motd != '') OR (empty($_COOKIE[COOKIE_PREFIX . 'motd']) AND !empty($motd) AND $motd != ''))
		{
			set_cookie('motd', DATETODAY);
			$ilance->bbcode = construct_object('api.bbcode');
			$motd = stripslashes($motd);
			$motd = strip_tags($motd);
		}
		$html = $ilance->fb->fbmlButton . '<div id="fb-root"></div>
<script type="text/javascript">
<!--
jQuery(\'document\').ready(function()
{
	var motd = \'' . addslashes($motd) . '\';
	window.fbAsyncInit = function()
	{
		FB.init({appId: \'' . handle_input_keywords($ilance->fb->config['appId']) . '\', status: true, cookie: true, xfbml: true, oauth: true});
		FB.getLoginStatus(fb_status, true);
		FB.Event.subscribe(\'auth.statusChange\', fb_status);
	};
	(function(d)
	{
		var js, id = \'facebook-jssdk\'; if (d.getElementById(id)) {return;}
		js = d.createElement(\'script\');
		js.id = id;
		js.async = true;
		js.src = document.location.protocol + \'//connect.facebook.net/en_US/all.js\';
		d.getElementsByTagName(\'head\')[0].appendChild(js);
        }(document));
	function fb_status(response)
	{
		if (response.status === \'connected\' && parseInt(UID) > 0)
		{
			var uid = response.authResponse.userID;
			var accessToken = response.authResponse.accessToken;
			jQuery(\'#fb-auth\').replaceWith(\'<button id="fb-logout" style="margin-right:10px;"></button>\');
			button = document.getElementById(\'fb-logout\');
			button.onclick = function()
			{
				FB.logout(function(response)
				{
					window.location.href = \'' . $url . (($ilconfig['globalauctionsettings_seourls']) ? 'signout?' . TIMESTAMPNOW : $ilpage['login'] . '?cmd=_logout') . '\';
				});
			};
		}
		else
		{
			jQuery(\'#fb-logout\').replaceWith(\'<button id="fb-auth" style="margin-right:10px"></button>\');
			button = document.getElementById(\'fb-auth\');
			button.onclick = function()
			{
				FB.login(function(response)
				{
					if (response.authResponse)
					{
						toggle_show(\'fblogin_working\');
						fetch_js_object(\'fb-auth\').disabled = true;
						FB.api(\'/me\', function(response)
						{
							$.ajax({
								data: {operation : \'login\'}, dataType: \'json\', global: false, url: \'' . $url . (($ilconfig['globalauctionsettings_seourls']) ? 'ajax' : $ilpage['ajax']) . '?cmd=fbbridge&subcmd=login\', type: \'POST\', success: function(data, status)
								{
									if (data.message != \'\')
									{
										alert_js(data.message);	
									}
									if (data.success)
									{
										if (motd != \'\')
										{
											alert_js(motd);
										}
										if (data.redirect != \'\')
										{
											window.location.href = data.redirect;
										}
									}
									else
									{
										if (data.redirect != \'\')
										{
											window.location.href = data.redirect;
										}
									}
									
								}
							});				   
						});
					} 
				}, {scope:\'email,publish_stream,user_birthday,user_hometown,user_location\'});  	
			}
		}
	};
});
//-->
</script>
';
		return $html;
	}

	function construct_settings()
	{
		global $ilance, $show, $ilpage, $ilconfig;
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "fbbridge_configuration
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$html = '<tr class="alt2"><td>{_description}</td><td align="right">{_setting}</td></tr>';
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				switch ($res['inputtype'])
                                {
                                        case 'yesno':
                                        {
                                                if ($res['value'])
                                                {
                                                        $check1 = 'checked="checked"';
                                                        $check2 = '';
                                                }
                                                else
                                                {
                                                        $check2 = 'checked="checked"';
                                                        $check1 = '';
                                                }
                                                $html .= '<tr class="alt1" valign="top"><td><div><strong>' . stripslashes($res['description']) . '</strong></div><div style="padding-top:3px" class="smaller gray">' . stripslashes($res['comment']) . '</div></td><td nowrap="nowrap"><div><input type="radio" name="' . $res['name'] . '" id="' . $res['name'] . '1" value="1" ' . $check1 . ' /><label for="' . $res['name'] . '1"> {_yes}</label>&nbsp;&nbsp;&nbsp;<input name="' . $res['name'] . '" id="' . $res['name'] . '2" type="radio" value="0" ' . $check2 . ' /><label for="' . $res['name'] . '2"> {_no}</label></div></td></tr>';
                                                break;
                                        }				
                                        case 'int':
                                        {
                                                $html .= '<tr class="alt1" valign="top"><td><div><strong>' . stripslashes($res['description']) . '</strong></div><div style="padding-top:3px" class="smaller gray">' . stripslashes($res['comment']) . '</div></td><td nowrap="nowrap"><div><input type="text" name="' . $res['name'] . '" value="' . $res['value'] . '" style="width: 50px;"></div></td></tr>';
                                                break;
                                        }				
                                        case 'text':
                                        {
                                                $html .= '<tr class="alt1" valign="top"><td><div><strong>'.stripslashes($res['description']).'</strong></div><div style="padding-top:3px" class="smaller gray">' . stripslashes($res['comment']) . '</div></td><td nowrap="nowrap"><div><input type="text" name="' . $res['name'] . '" value="' . $res['value'] . '" style="width: 500px;" /></div></td></tr>';
                                                break;
                                        }				
                                        case 'textarea':
                                        {
                                                $html .= '';
                                                break;
                                        }				
                                        case 'password':
                                        {
                                                $html .= '<tr class="alt1" valign="top"><td><div><strong>'.stripslashes($res['description']).'</strong></div><div style="padding-top:3px" class="smaller gray">' . stripslashes($res['comment']) . '</div></td><td nowrap="nowrap"><div><input type="password" name="' . $res['name'] . '" value="' . $res['value'] . '" style="width: 500px;" /></div></td></tr>';
                                                break;
                                        }
                                }
			}
		}
		$template = '<form method="post" action="' . $ilpage['components'] . '" accept-charset="UTF-8" style="margin: 0px;">
<input type="hidden" name="cmd" value="components" />
<input type="hidden" name="subcmd" value="_update-fbbridge-settings"  />
<input type="hidden" name="module" value="fbbridge" />
<div class="block-wrapper">
<div class="block">
	<div class="block-top">
		<div class="block-right">
			<div class="block-left"></div>
		</div>
	</div>
	<div class="block-header">{_settings}</div>
	<div class="block-content" style="padding:0px">
		<table width="100%" border="0" cellspacing="0" cellpadding="12" dir="' . $ilconfig['template_textdirection'] . '">
		' . $html . '
		<tr class="alt2_top">
			<td colspan="2"><input type="submit" name="{_apply}" value="{_save}" class="buttons" style="font-size:15px" /></td>
		</tr>
		</table>
	</div>
	<div class="block-footer">
		<div class="block-right">
			<div class="block-left"></div>
		</div>
	</div>
</div>
</div>
</form>';
                return $template;
	}
}
?>