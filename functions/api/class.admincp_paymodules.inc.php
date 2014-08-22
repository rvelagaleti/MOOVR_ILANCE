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
* AdminCP Paymodules class to perform the majority of functions within the ILance Admin Control Panel
*
* @package      iLance\AdminCP\PayModules
* @version      4.0.0.8059
* @author       ILance
*/
class admincp_paymodules extends admincp
{
	/**
	* Function to print the payment modules configuration input template menus.
	*
	* @param       string       config group
	*
	* @return      string       HTML representation of the configuration template
	*/
	function construct_paymodules_input($configgroup = '', $returnurl = '')
	{
		global $ilance, $phrase, $page_title, $area_title, $ilpage, $ilconfig;
		$html = '';
		$sqlgrp = $ilance->db->query("
			SELECT groupname, description, help
			FROM " . DB_PREFIX . "payment_groups
			WHERE parentgroupname = '" . $ilance->db->escape_string($configgroup) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sqlgrp) > 0)
		{
			while ($resgrpties = $ilance->db->fetch_array($sqlgrp, DB_ASSOC))
			{
				$html .= '<form method="post" action="' . HTTPS_SERVER_ADMIN . $ilpage['settings'] . '" name="updatesettings" accept-charset="UTF-8" style="margin: 0px;">
<input type="hidden" name="cmd" value="paymodulesupdate" />
<input type="hidden" name="subcmd" value="_update-config-settings" />
<input type="hidden" name="return" value="' . $returnurl . '" />
<div class="block-wrapper">
<div class="block">
<div class="block-top">
	<div class="block-right">
			<div class="block-left"></div>
	</div>
</div>
<div class="block-header" onclick="return toggle(\'admincp_paymodule_' . $configgroup . '\')" onmouseover="this.style.cursor=\'pointer\'" onmouseout="this.style.cursor=\'\'"><span style="float:left; padding-right:7px; padding-top:3px"><span style="padding-right:5px"><img id="collapseimg_admincp_paymodule_' . $configgroup . '" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'expand{collapse[collapseimg_admincp_paymodule_' . $configgroup . ']}.gif" border="0" alt="" /></span></span>' . stripslashes($resgrpties['description']) . '</div>
<div class="block-content" id="collapseobj_admincp_paymodule_' . $configgroup . '" style="{collapse[collapseobj_admincp_paymodule_' . $configgroup . ']} padding:0px">
<table width="100%" border="0" align="center" cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" dir="' . $ilconfig['template_textdirection'] . '">';
		$html .= (!empty($resgrpties['help'])) ? '<tr class="alt2"><td colspan="2">' . stripslashes($resgrpties['help']) . '</td></tr>' : '';
		$html .= '<tr class="alt2">
	<td>{_description}</td>
	<td align="right">{_value}</td>
	<td>{_sort}</td>
</tr>';
				$sql = $ilance->db->query("
					SELECT id, inputtype, inputcode, name, value, sort, description, help
					FROM " . DB_PREFIX . "payment_configuration
					WHERE configgroup = '" . $resgrpties['groupname'] . "'
					ORDER BY sort ASC
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$rowcount = 0;
					while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
					{
						$res['class'] = ($rowcount % 2) ? 'alt1' : 'alt1';
						$rowcount++;
						if ($res['inputtype'] == 'yesno')
						{
							$html .= $this->construct_paymodules_parent_yesno_input($res['id'], $res['value'], stripslashes($res['description']), $res['inputtype'], $res['class'], $res['name'], stripslashes($res['help']), $res['sort']);
						}
						else if ($res['inputtype'] == 'int')
						{
							$html .= $this->construct_paymodules_parent_int_input($res['id'], $res['value'], stripslashes($res['description']), $res['inputtype'], $res['class'], $res['name'], $res['help'], $res['sort']);
						}
						else if ($res['inputtype'] == 'text')
						{
							$html .= $this->construct_paymodules_parent_text_input($res['id'], $res['value'], stripslashes($res['description']), $res['inputtype'], $res['class'], $res['name'], $res['help'], $res['sort']);
						}
						else if ($res['inputtype'] == 'pass')
						{
							$html .= $this->construct_paymodules_parent_pass_input($res['id'], $res['value'], stripslashes($res['description']), $res['inputtype'], $res['class'], $res['name'], $res['help'], $res['sort']);
						}
						else if ($res['inputtype'] == 'textarea')
						{
							$html .= $this->construct_paymodules_parent_textarea_input($res['id'], $res['value'], stripslashes($res['description']), $res['inputtype'], $res['class'], $res['name'], $res['help'], $res['sort']);
						}
						else if ($res['inputtype'] == 'pulldown')
						{
							$html .= $this->construct_paymodules_parent_pulldown_input($res['id'], $res['value'], stripslashes($res['description']), $res['inputtype'], $res['class'], $res['name'], $res['inputcode'], $res['help'], $res['sort']);
						}
					}
					$html .= '<tr class="alt2_top"><td colspan="2"><input type="submit" name="save" value=" {_save} " onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')" class="buttons" style="font-size:15px" /></td><td></td></tr></table></div>
<div class="block-footer">
	<div class="block-right">
			<div class="block-left"></div>
	</div>
</div>

</div>
</div></form><div style="padding-bottom:4px"></div>';
				}
			}
		}
		return $html;
	}

	/**
	* Function to print yes or no type radio boxes for yes / no type settings.
	*
	* @param       integer      config id
	* @param       string       value
	* @param       string       description
	* @param       string       input type
	* @param       string       css class
	* @param       string       $ilconfig[] name
	*
	* @return      string       HTML representation of the configuration template
	*/
	function construct_paymodules_parent_yesno_input($id = 0, $value = '', $description = '', $inputtype = '', $class = 'alt1', $variableinfo = '', $help = '', $sort = 0)
	{
		global $ilance, $ilconfig, $phrase, $ilpage;
		$html = '<tr valign="top" class="' . $class . '"><td align="left"><div style="color:#444"><span style="float:right; padding-left:10px"><a href="javascript:void(0)" onclick="window.clipboardData.setData(\'text\', \'$ilconfig[\x27' . $variableinfo . '\x27]\') && alert_js(\'This variable: $ilconfig[\x27' . $variableinfo . '\x27] has been copied to your clipboard\')" onmouseover="Tip(\'<strong>PHP</strong> variable: $ilconfig[\x27<span style=color:blue>' . $variableinfo . '</span>\x27]<div class=smaller gray>Click this icon to copy the PHP variable to your clipboard</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/ico-info.png" hspace="0" vspace="0" border="0" alt="" /></a></span><strong>' . ucfirst($description) . '</strong></div><div class="gray" style="font-size:10px; font-family: verdana; padding-top:3px">' . stripslashes($help) . '</div></td><td align="right"><span style="white-space:nowrap"><label for="rb_1[' . $id . ']"> <input type="radio" name="config[' . $id . ']" value="1" id="rb_1[' . $id . ']" ';
		if ($value == '1')
		{
			$html .= 'checked="checked" ';
		}
		$html .= '>{_yes}</label> <label for="rb_0[' . $id . ']"> <input type="radio" name="config[' . $id . ']" value="0" id="rb_0[' . $id . ']" ';
		if ($value == '0')
		{
			$html .= 'checked="checked" ';
		}
		$html .= '>{_no}</label></span></td>
<td align="center" class="' . $class . '">
<input type="text" name="sort[' . $id . ']" value="' . $sort . '" style="width:30px; text-align:center" class="input" />
</td></tr>';
		return $html;
	}

	/**
	* Function to print integer type settings.
	*
	* @param       integer      config id
	* @param       string       value
	* @param       string       description
	* @param       string       input type
	* @param       string       background color
	* @param       string       $ilconfig[] name
	*
	* @return      string       HTML representation of the configuration template
	*/
	function construct_paymodules_parent_int_input($id = 0, $value = '', $description = '', $inputtype = '', $class = 'alt1', $variableinfo = '', $help = '', $sort = 0)
	{
		global $ilance, $ilconfig;
		$html = '<tr class="' . $class . '" valign="top"><td align="left"><div style="color:#444"><span style="float:right; padding-left:10px"><a href="javascript:void(0)" onclick="window.clipboardData.setData(\'text\', \'$ilconfig[\x27' . $variableinfo . '\x27]\') && alert_js(\'This variable: $ilconfig[\x27' . $variableinfo . '\x27] has been copied to your clipboard\')" onmouseover="Tip(\'<strong>PHP</strong> variable: $ilconfig[\x27<span style=color:blue>' . $variableinfo . '</span>\x27]<div class=smaller gray>Click this icon to copy the PHP variable to your clipboard</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/ico-info.png" hspace="0" vspace="0" border="0" alt="" /></a></span><strong>' . stripslashes($description) . '</strong></div><div class="smaller gray" style="line-height:15px; padding-top:3px">' . stripslashes($help) . '</div></td><td align="right"><span style="white-space:nowrap"><input type="text" name="config[' . $id . ']" value="' . $value . '" style="width:50px" class="input" /></span></td><td align="center" class="' . $class . '">
<input type="text" name="sort[' . $id . ']" value="' . $sort . '" style="width:30px; text-align:center" class="input" />
</td></tr>';
		return $html;
	}

	/**
	* Function to print text field type settings.
	*
	* @param       integer      config id
	* @param       string       value
	* @param       string       description
	* @param       string       input type
	* @param       string       background color
	* @param       string       $ilconfig[] name
	*
	* @return      string       HTML representation of the configuration template
	*/
	function construct_paymodules_parent_text_input($id = 0, $value = '', $description = '', $inputtype = '', $class = 'alt1', $variableinfo = '', $help = '', $sort = 0)
	{
		global $ilance, $ilconfig;
		$html = '<tr class="' . $class . '" valign="top"><td valign="top" align="left"><div style="color:#444"><span style="float:right; padding-left:10px"><a href="javascript:void(0)" onclick="window.clipboardData.setData(\'text\', \'$ilconfig[\x27' . $variableinfo . '\x27]\') && alert_js(\'This variable: $ilconfig[\x27' . $variableinfo . '\x27] has been copied to your clipboard\')" onmouseover="Tip(\'<strong>PHP</strong> variable: $ilconfig[\x27<span style=color:blue>' . $variableinfo . '</span>\x27]<div class=smaller gray>Click this icon to copy the PHP variable to your clipboard</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/ico-info.png" hspace="0" vspace="0" border="0" alt="" /></a></span><strong>' . stripslashes($description) . '</strong></div><div class="gray" style="line-height:15px; padding-top:3px">' . stripslashes($help) . '</div></td><td align="right"><span style="white-space:nowrap"><input type="text" name="config[' . $id . ']" style="width: 200px" class="input" value="' . $value . '" /></span></td><td align="center" class="' . $class . '">
<input type="text" name="sort[' . $id . ']" value="' . $sort . '" style="width:30px; text-align:center" class="input" />
</td></tr>';
		return $html;
	}

	/**
	* Function to print password field type settings.
	*
	* @param       integer      config id
	* @param       string       value
	* @param       string       description
	* @param       string       input type
	* @param       string       background color
	* @param       string       $ilconfig[] name
	*
	* @return      string       HTML representation of the configuration template
	*/
	function construct_paymodules_parent_pass_input($id = 0, $value = '', $description = '', $inputtype = '', $class = 'alt1', $variableinfo = '', $help = '', $sort = 0)
	{
		global $ilance, $ilconfig;
		$html = '<tr class="' . $class . '" valign="top"><td valign="top" align="left"><div style="color:#444"><span style="float:right; padding-left:10px"><a href="javascript:void(0)" onclick="window.clipboardData.setData(\'text\', \'$ilconfig[\x27' . $variableinfo . '\x27]\') && alert_js(\'This variable: $ilconfig[\x27' . $variableinfo . '\x27] has been copied to your clipboard\')" onmouseover="Tip(\'<strong>PHP</strong> variable: $ilconfig[\x27<span style=color:blue>' . $variableinfo . '</span>\x27]<div class=smaller gray>Click this icon to copy the PHP variable to your clipboard</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/ico-info.png" hspace="0" vspace="0" border="0" alt="" /></a></span><strong>' . stripslashes($description) . '</strong></div><div class="gray" style="line-height:15px; padding-top:3px">' . stripslashes($help) . '</div></td><td align="right" valign="top"><span style="white-space:nowrap"><input type="password" name="config[' . $id . ']" style="width: 200px" class="input" value="' . $value . '" /></span></td><td align="center" class="' . $class . '">
<input type="text" name="sort[' . $id . ']" value="' . $sort . '" style="width:30px; text-align:center" class="input" />
</td></tr>';
		return $html;
	}

	/**
	* Function to print textarea field type settings.
	*
	* @param       integer      config id
	* @param       string       value
	* @param       string       description
	* @param       string       input type
	* @param       string       background color
	* @param       string       $ilconfig[] name
	*
	* @return      string       HTML representation of the configuration template
	*/
	function construct_paymodules_parent_textarea_input($id = 0, $value = '', $description = '', $inputtype = '', $class = 'alt1', $variableinfo = '', $help = '', $sort = 0)
	{
		global $ilance, $ilconfig;
		$html = '<tr class="' . $class . '" valign="top"><td valign="top" align="left"><div style="color:#444"><span style="float:right; padding-left:10px"><a href="javascript:void(0)" onclick="window.clipboardData.setData(\'text\', \'$ilconfig[\x27' . $variableinfo . '\x27]\') && alert_js(\'This variable: $ilconfig[\x27' . $variableinfo . '\x27] has been copied to your clipboard\')" onmouseover="Tip(\'<strong>PHP</strong> variable: $ilconfig[\x27<span style=color:blue>' . $variableinfo . '</span>\x27]<div class=smaller gray>Click this icon to copy the PHP variable to your clipboard</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/ico-info.png" hspace="0" vspace="0" border="0" alt="" /></a></span><strong>' . stripslashes($description) . '</strong></div><div class="gray" style="line-height:15px; padding-top:3px">' . stripslashes($help) . '</div></td><td align="right" valign="top"><span style="white-space:nowrap"><textarea name="config[' . $id . ']" style="width: 325px; height: 84px" class="input" wrap="physical">' . $value . '</textarea></span></td><td align="center" class="' . $class . '">
<input type="text" name="sort[' . $id . ']" value="' . $sort . '" style="width:30px; text-align:center" class="input" />
</td></tr>';
		return $html;
	}

	/**
	* Function to print pulldown selection menu field type settings.
	*
	* @param       integer      config id
	* @param       string       value
	* @param       string       description
	* @param       string       input type
	* @param       string       background color
	* @param       string       $ilconfig[] name
	* @param       string       pulldown menu input code
	*
	* @return      string       HTML representation of the configuration template
	*/
	function construct_paymodules_parent_pulldown_input($id = 0, $value = '', $description = '', $inputtype = '', $class = 'alt1', $variableinfo = '', $inputcode = '', $help = '', $sort = 0)
	{
		global $ilance, $ilconfig;
		$html = '<tr class="' . $class . '" valign="top"><td valign="top" align="left"><div style="color:#444"><span style="float:right; padding-left:10px"><a href="javascript:void(0)" onclick="window.clipboardData.setData(\'text\', \'$ilconfig[\x27' . $variableinfo . '\x27]\') && alert_js(\'This variable: $ilconfig[\x27' . $variableinfo . '\x27] has been copied to your clipboard\')" onmouseover="Tip(\'<strong>PHP</strong> variable: $ilconfig[\x27<span style=color:blue>' . $variableinfo . '</span>\x27]<div class=smaller gray>Click this icon to copy the PHP variable to your clipboard</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/ico-info.png" hspace="0" vspace="0" border="0" alt="" /></a></span><strong>' . stripslashes($description) . '</strong></div><div class="gray" style="line-height:15px; padding-top:3px">' . stripslashes($help) . '</div></td><td align="right" valign="top"><span style="white-space:nowrap">' . stripslashes($inputcode) . '</span></td><td align="center" class="' . $class . '">
<input type="text" name="sort[' . $id . ']" value="' . $sort . '" style="width:30px; text-align:center" class="input" />
</td></tr>';
		return $html;
	}

	/**
	* Function to print the default payment gateway within a pulldown menu.
	*
	* @param       string       config value
	* @param       string       config key
	*
	* @return      string       HTML representation of the pulldown menu
	*/
	function default_gateway_pulldown($value = '', $key = 'use_internal_gateway')
	{
		global $ilance, $phrase;
		$writepulldown = '<select name="config[' . $key . ']" class="select">';
		$sql = $ilance->db->query("
			SELECT groupname
			FROM " . DB_PREFIX . "payment_groups
			WHERE moduletype = 'gateway'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$sql2 = $ilance->db->query("
					SELECT a.value, a.configgroup
					FROM " . DB_PREFIX . "payment_configuration as a,
					" . DB_PREFIX . "payment_groups as b
					WHERE a.name = 'paymodulename'
						AND a.inputname != 'defaultgateway'
						AND b.moduletype = 'gateway'
						AND a.configgroup = '" . $res['groupname'] . "'
					GROUP BY a.configgroup
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql2) > 0)
				{
					while ($res2 = $ilance->db->fetch_array($sql2, DB_ASSOC))
					{
						$sql3 = $ilance->db->query("
							SELECT value
							FROM " . DB_PREFIX . "payment_configuration
							WHERE name = 'use_internal_gateway'
						", 0, null, __FILE__, __LINE__);
						$res3 = $ilance->db->fetch_array($sql3, DB_ASSOC);
						$writepulldown .= '<option value="' . $res2['configgroup'] . '"';
						if ($value == $res2['configgroup'])
						{
							$writepulldown .= ' selected="selected"';
						}
						$writepulldown .= '>' . $res2['value'] . '</option>';
					}
				}
			}
			if ($value == 'none')
			{
				$writepulldown .= '<option value="none" selected="selected">{_disable_credit_card_support}</option>';
			}
			else
			{
				$writepulldown .= '<option value="none">{_disable_credit_card_support}</option>';
			}
		}
		$writepulldown .= '</select>';
		return $writepulldown;
	}

	function count_offline_payment_types($id = 0)
	{
		global $ilance, $show, $ilconfig;
		$count = $totalcount = 0;
		$varname = $ilance->db->fetch_field(DB_PREFIX . "payment_methods", "id = '" . intval($id) . "'", "title");
		$sql = $ilance->db->query("
			SELECT id, paymethod, status
			FROM " . DB_PREFIX . "projects
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0 AND !empty($varname))
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				if ($res['status'] == 'open')
				{
					if (!empty($res['paymethod']) AND is_serialized($res['paymethod']))
					{
						if (strchr($res['paymethod'], $varname))
						{
							$array = unserialize($res['paymethod']);
							foreach ($array AS $paymethod)
							{
								if (!empty($paymethod) AND $paymethod == $varname)
								{
									$count++;
									$totalcount++;
								}
							}
						}
					}
				}
				else
				{
					if (!empty($res['paymethod']) AND is_serialized($res['paymethod']))
					{
						if (strchr($res['paymethod'], $varname))
						{
							$array = unserialize($res['paymethod']);
							if (is_array($array))
							{
								foreach ($array AS $paymethod)
								{
									if (!empty($paymethod) AND $paymethod == $varname)
									{
										$totalcount++;
									}
								}
							}
						}
					}
				}
			}
		}
		return array ('count' => $count, 'totalcount' => $totalcount);
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>