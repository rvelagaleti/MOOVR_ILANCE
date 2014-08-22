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
if (!class_exists('auction'))
{
	exit;
}

/**
* Auction posting class to perform the majority of printing and displaying of filters and other form elements
* for service and product auctions.
*
* @package      iLance\Auction\Post
* @version	4.0.0.8059
* @author       ILance
*/
class auction_post extends auction
{
        /**
        * Function to print bid filters controlled via radio button and multiple selective options
        * during the post of a new service or product auction.
        *
        * @return      string        HTML representation of the bid filters permitted
        */
        function print_bid_filters()
        {
                global $ilance, $ilconfig, $phrase;
                $filter_rating1 = $filtered_rating1 = $filtered_rating2 = $filtered_rating3 = $filtered_rating4 = $filtered_rating5 = $filter_country1 = $filter_state1 = $filter_city1 = $filtered_city1 = $filter_zip1 = $filtered_zip1 = $businessnumber = $underage = $filter_bidlimit = $filtered_bidlimit = '';
                if (!empty($ilance->GPC['filter_rating']) AND $ilance->GPC['filter_rating'] == '1')
                {
                        $filter_rating1 = 'checked="checked"';
                        if (isset($ilance->GPC['filtered_rating']) AND $ilance->GPC['filtered_rating'] == '1')
			{
				$filtered_rating1 = ' selected="selected"';
			}
			else if (isset($ilance->GPC['filtered_rating']) AND $ilance->GPC['filtered_rating'] == '2')
			{
				$filtered_rating2 = ' selected="selected"';
			}
			else if (isset($ilance->GPC['filtered_rating']) AND $ilance->GPC['filtered_rating'] == '3')
			{
				$filtered_rating3 = ' selected="selected"';
			}
			else if (isset($ilance->GPC['filtered_rating']) AND $ilance->GPC['filtered_rating'] == '4')
			{
				$filtered_rating4 = ' selected="selected"';
			}
			else if (isset($ilance->GPC['filtered_rating']) AND $ilance->GPC['filtered_rating'] == '5')
			{
				$filtered_rating5 = ' selected="selected"';
			}
                }
                if (!empty($ilance->GPC['filter_country']) AND $ilance->GPC['filter_country'] == '1')
                {
                        $filter_country1 = 'checked="checked"';
                }
                if (!empty($ilance->GPC['filter_state']) AND $ilance->GPC['filter_state'] == '1')
                {
                        $filter_state1 = 'checked="checked"';
                }
                if (!empty($ilance->GPC['filter_city']) AND $ilance->GPC['filter_city'] == '1')
                {
                        $filter_city1 = 'checked="checked"';
                        if (isset($ilance->GPC['filtered_city']) AND $ilance->GPC['filtered_city'] != '')
			{
				$filtered_city1 = stripslashes(strip_tags($ilance->GPC['filtered_city']));
				$filter_city1 = 'checked="checked"';
			}
                }
                if (!empty($ilance->GPC['filter_zip']) AND $ilance->GPC['filter_zip'] == '1')
                {
                        $filter_zip1 = 'checked="checked"';
                        if (isset($ilance->GPC['filtered_zip']) AND $ilance->GPC['filtered_zip'] != '')
			{
				$filtered_zip1 = str_replace(' ', '', stripslashes(strip_tags($ilance->GPC['filtered_zip'])));
				$filter_zip1 = 'checked="checked"';
			}
                }
                if (!empty($ilance->GPC['filtered_bidlimit']))
                {
			$filtered_bidlimit = $ilance->GPC['filtered_bidlimit']; 
		}
                if (isset($ilance->GPC['filter_underage']) AND $ilance->GPC['filter_underage'] > 0)
                {
                        $underage = 'checked="checked"';
                }
		if (isset($ilance->GPC['filter_bidlimit']) AND $ilance->GPC['filter_bidlimit'] > 0)
                {
                        $filter_bidlimit = 'checked="checked"';
                }
                if (isset($ilance->GPC['filter_businessnumber']) AND $ilance->GPC['filter_businessnumber'] > 0)
                {
                        $businessnumber = 'checked="checked"';
                }
                $html = '<table cellpadding="0" cellspacing="0" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
<tr valign="top">
	<td width="50%">
		<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
		<tr valign="top" class="alt1">
			<td width="4%" valign="top"><input type="checkbox" name="filter_rating" id="filter_rating" value="1" ' . $filter_rating1 . ' /></td>
			<td><div style="padding-bottom:3px"><label for="filter_rating">{_requires_bidders_have_at_least_an_overall}&nbsp; </label></div>
			<div><select name="filtered_rating" class="select">
			<option value="1"' . $filtered_rating1 . '>{_at_least} 1.0 / 5.0</option>
			<option value="2"' . $filtered_rating2 . '>{_at_least} 2.0 / 5.0</option>
			<option value="3"' . $filtered_rating3 . '>{_at_least} 3.0 / 5.0</option>
			<option value="4"' . $filtered_rating4 . '>{_at_least} 4.0 / 5.0</option>
			<option value="5"' . $filtered_rating5 . '>{_at_least} 5.0 / 5.0</option>
			</select></div></td>
			<td valign="top"><input type="checkbox" name="filter_country" id="filter_country" value="1" ' . $filter_country1 . ' /></td>
			<td valign="top"><div style="padding-bottom:3px"><label for="filter_country"> {_requires_bidders_reside_or_do_business_country}&nbsp; </label></div><div>{country_js_pulldown}</div></td>
		</tr>
		<tr valign="top" class="alt1">
			<td valign="top"><input type="checkbox" name="filter_city" id="filter_city" value="1" ' . $filter_city1 . ' /></td>
			<td nowrap="nowrap"><div style="padding-bottom:3px"><label for="filter_city"> {_requires_bidders_reside_or_do_business_city}&nbsp; </label></div><div><input type="text" id="filtered_city" class="input" name="filtered_city" value="' . $filtered_city1 . '" onkeypress="return noenter()" title="" style="width:175px" /></div></td>
			<td valign="top"><input type="checkbox" name="filter_state" id="filter_state" value="1" ' . $filter_state1 . ' /></td>
			<td><div style="padding-bottom:3px"><label for="filter_state"> {_requires_bidders_reside_or_do_business_state}&nbsp; </label></div><div style="padding-bottom:3px">{state_js_pulldown}</div></td>
		</tr>
		<tr valign="top" class="alt1">
			<td><input type="checkbox" name="filter_businessnumber" id="filter_businessnumber" value="1" ' . $businessnumber . ' /></td>
			<td nowrap="nowrap"><span><label for="filter_businessnumber"> {_prevent_bidders_that_have_not_supplied_a_valid_business_or_vat_number}</label></span></td>';
		if ($ilconfig['registrationdisplay_dob'])
		{
			$html .= '<td><input type="checkbox" name="filter_underage" id="filter_underage" value="1" ' . $underage . ' /></td>
			<td nowrap="nowrap"><span><label for="filter_underage"> {_prevent_under_age_bidders_18_years_and_younger}</label></span></td>';
		}
		else
		{
			$html .= '<td colspan="2"><input type="hidden" name="filter_underage" value="0" /></td>';       
		}
		$html .= '
		<tr valign="top">
			<td valign="top"><input type="checkbox" name="filter_zip" id="filter_zip" value="1" ' . $filter_zip1 . ' /></td>
			<td nowrap><div style="padding-bottom:3px"><label for="filter_zip"> {_requires_bidders_reside_or_do_business_zip}&nbsp; </label></div><div><input type="text" id="filtered_zip" class="input" name="filtered_zip" value="' . $filtered_zip1 . '" onkeypress="return noenter()" title="" style="width:100px" /> <span class="gray">({_no_spaces_or_dashes})</span></div></td>';
		
		if (isset($ilance->GPC['cmd']) AND ($ilance->GPC['cmd'] == 'new-rfp' OR $ilance->GPC['cmd'] == 'rfp-management'))
		{
			$html .= '<td valign="top"><input type="checkbox" name="filter_bidlimit" id="filter_bidlimit" value="1" ' . $filter_bidlimit . '  /></td>
			<td nowrap><div style="padding-bottom:3px"><label for="filter_bidlimit"> {_limit_bid_proposals}&nbsp; </label></div><div><input type="text" id="filtered_bidlimit" class="input" name="filtered_bidlimit" value="' . $filtered_bidlimit . '" onkeypress="return noenter()" title="" style="width:100px" /> <span class="gray">({_no_spaces_or_dashes})</span></div></td>';	
		}
		$html .= '</tr>
		</tr></table>
	</td>
</tr>
</table>';
                return $html;
        }
        
        /**
        * Function to print profile bid filters controlled via radio button and multiple selective options
        * during the post of a new service or product auction such as ranges (from / to values) and pulldown
        * menu options (including multiple choice selection values).
        *
        * This function is called in the advanced search menu as well as when a user is posting a new listing.
        *
        * @param       integer       category id
        * @param       string        display mode (input, preview, output)
        * @param       string        category type (service)
        * @param       integer       project id (for updating listing)
        * 
        * @return      string        HTML representation of the bid filters permitted
        */
        function print_profile_bid_filters($cid = 0, $displaymode = '', $catype = 'service', $projectid = 0)
        {
                global $ilance, $ilconfig, $phrase, $show;
                $show['profile_filters'] = false;
                $html = '';
                if ($cid == 0)
                {
	                $sql = $ilance->db->query("
	                        SELECT questionid, description, question, isfilter, filtertype, filtercategory, inputtype, multiplechoice
	                        FROM " . DB_PREFIX . "profile_questions
	                        WHERE isfilter = '1'
                                        AND filtercategory = '-1'
	                        	AND (inputtype = 'int' OR inputtype = 'multiplechoice' OR inputtype = 'pulldown')
	                ");                	
                }
                else 
                {
	                $sql = $ilance->db->query("
	                        SELECT questionid, description, question, isfilter, filtertype, filtercategory, inputtype, multiplechoice
	                        FROM " . DB_PREFIX . "profile_questions
	                        WHERE isfilter = '1'
                                        AND (filtercategory = '" . intval($cid) . "' OR filtercategory = '-1')
                                        AND (inputtype = 'int' OR inputtype = 'multiplechoice' OR inputtype = 'pulldown')
	                ");
                }
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $show['profile_filters'] = true;
                        $html = '<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '"><tr>';
                        $i = $q = $m = $n = $g = 0;  
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                $d = $i % 2;
                                // #### INPUT MODE #############################
                                if ($displaymode == 'input')
                                {
                                        switch ($res['inputtype'])
                                        {
                                        	case 'int':
                                                {
                                                        $html .= '<td width="25%" valign="top"><div><strong>' . $res['question'] . '</strong></div><div class="gray">{_select_range_pattern}</div><div style="padding-top:4px"><strong>{_between_upper}</strong> &nbsp;<input class="input" name="pa[range][' . $res['questionid'] . '][from]" value="" style="width:40px" /> &nbsp;&nbsp;<strong>{_and}</strong> &nbsp;<input name="pa[range][' . $res['questionid'] . '][to]" value="" class="input" style="width:40px" /></div></td>';
                                                        $q++;
                                                        break;
                                                }
                                                case 'multiplechoice':
                                        	case 'pulldown':
                                                {
                                                        $questions = $res['multiplechoice'];
                                                        $choices = explode('|', $questions);
                                                        $html_choice = array();
                                                        $k = 0;
                                                        foreach ($choices AS $choice)
                                                        {
                                                               $html_choice["$k"] = stripslashes($choice);
                                                               $k++;
                                                        }
                                                        $html .= '<td width="25%" valign="top"><strong>' . $res['question'] . '</strong><div class="gray" style="padding-bottom:6px">{_select_multiple_choices}</div>';
                                                        $j = 0;
                                                        $km = $k + $n;
                                                        while ($n < $km)
                                                        {
                                                                $html .= '<div><label for="pulldown_' . str_replace(' ', '_', mb_strtolower($html_choice["$j"])) . '_' . $j . '"><input type="checkbox" name="pa[choice_' . str_replace(' ', '_', mb_strtolower($html_choice["$j"])) . '][' . $res['questionid'] . '][custom]" value="' . $html_choice["$j"] . '" id="pulldown_' . str_replace(' ', '_', mb_strtolower($html_choice["$j"])) . '_' . $j . '" /> ' . $html_choice["$j"] . '</label></div>';
                                                                $n++;
                                                                $j++;
                                                        }
                                                        $html .= '</td>';
                                                        break;
                                                }
                                        }    
                                }
                                
                                // #### UPDATE INPUT MODE ######################
                                else if ($displaymode == 'update')
                                {
                                        switch ($res['inputtype'])
                                        {
                                        	case 'int':
                                                {
                                                        $from = $to = '';
                                                        $fromto = $ilance->db->fetch_field(DB_PREFIX . "profile_filter_auction_answers", "questionid = '" . $res['questionid'] . "'", "answer");
                                                        if (!empty($fromto))
                                                        {
                                                                $fromto = explode('|', $fromto);
                                                                $from = $fromto[0];
                                                                $to = $fromto[1];
                                                        }
                                                        $html .= '<td width="25%" valign="top"><div><strong>' . $res['question'] . '</strong></div><div class="gray">{_select_range_pattern}</div><div style="padding-top:4px"><strong>{_between_upper}</strong> &nbsp;<input class="input" name="pa[range][' . $res['questionid'] . '][from]" value="' . $from . '" style="width:40px" /> &nbsp;&nbsp;<strong>{_and}</strong> &nbsp;<input name="pa[range][' . $res['questionid'] . '][to]" value="' . $to . '" class="input" style="width:40px" /></div></td>';
                                                        $q++;
                                                        break;
                                                }
                                                case 'multiplechoice':
                                        	case 'pulldown':
                                                {
                                                        $questions = $res['multiplechoice'];
                                                        $choices = explode('|', $questions);
                                                        $existing = $ilance->db->fetch_field(DB_PREFIX . "profile_filter_auction_answers", "questionid = '" . $res['questionid'] . "'", "answer");
                                                        if (!empty($existing))
                                                        {
                                                                $existing = explode('|', $existing);        
                                                        }
                                                        $html_choice = array();
                                                        $k = 0;
                                                        foreach ($choices as $choice)
                                                        {
                                                               $html_choice["$k"] = stripslashes($choice);
                                                               $k++;
                                                        }
                                                        $html .= '<td width="25%" valign="top"><strong>' . $res['question'] . '</strong><div class="gray" style="padding-bottom:6px">{_select_multiple_choices}</div>';
                                                        $j = 0;
                                                        $km = $k + $n;
                                                        while ($n < $km)
                                                        {
                                                                $checked = '';
                                                                if (!empty($existing) AND is_array($existing) AND in_array($html_choice["$j"], $existing))
                                                                {
                                                                        $checked = 'checked="checked"';
                                                                }
                                                                $html .= '<div><label for="pulldown_' . str_replace(' ', '_', mb_strtolower($html_choice["$j"])) . '_' . $j . '"><input type="checkbox" name="pa[choice_' . str_replace(' ', '_', mb_strtolower($html_choice["$j"])) . '][' . $res['questionid'] . '][custom]" value="' . $html_choice["$j"] . '" id="pulldown_' . str_replace(' ', '_', mb_strtolower($html_choice["$j"])) . '_' . $j . '" ' . $checked . ' /> ' . $html_choice["$j"] . '</label></div>';
                                                                $n++;
                                                                $j++;
                                                        }
                                                        $html .= '</td>';
                                                        break;
                                                }
                                        }    
                                }
                                // check if the current column is dividable by two
                                if ($d == 4 && $i != 0)
                                {
                                        $html .= '</tr><tr>';
                                }
                                $i++;
                        }
                        $html .= '</tr>
</table>';
                }
                else
                {
                        $html = '<div>{_there_are_currently_no_filters_that_exist_within_the_selected_category}</div>';
                }
		return $html;
        }
		
	/**
	* Function to print the hourly rate price javascript details for the yahoo slider widget
        */
        function print_price_logic_type_js()
        {
                global $ilance, $ilconfig, $phrase, $show;
		$htmla = "new Array(";
		$htmlb = "new Array(";
                $hourlyranges = array('1' => '10', '10' => '20', '20' => '30', '30' => '40', '40' => '50', '50' => '60', '60' => '70', '70' => '80', '80' => '90', '90' => '100', '100' =>'110', '110' => '120', '120' => '130', '130' => '140', '140' => '150', '150' => '160', '160' => '170', '170' => '180', '180' => '190', '190' => '200', '200' => '-1');
		foreach ($hourlyranges AS $key => $value)
		{
			if ($value == '-1')
			{
				$htmla .= "'" . $ilance->currency->format($key, '', false, true) .  " " . '{_or_more}' . "',";
				$htmlb .= "'" . $key . "',";	
			}
			else
			{
				$htmla .= "'" . $ilance->currency->format($key, '', false, true) . " " . '{_to}' . " " . $ilance->currency->format($value, '', false, true) . "',";
				$htmlb .= "'" . $key . "',";
			}
		}
		$htmla = mb_substr($htmla, 0, -1);
		$htmlb = mb_substr($htmlb, 0, -1);
		$htmla .= ");";
                $htmlb .= ");";
                return array($htmla, $htmlb);
        }
        
        /**
        * Function to print the available bid amount types for the auction poster to select based on the admin
        * defined bid types for this particular category.
        *
        * @param       integer       category id
        * @param       string        category type
        * 
        * @return      string        HTML representation of the bid amount type form elements
        */
        function print_bid_amount_type($cid = 0, $cattype = '')
        {
                global $ilance, $ilconfig, $phrase, $show;
                $bidtype1 = '';
                $bidtype2 = 'checked="checked"';
                if (!empty($ilance->GPC['filter_bidtype']) AND $ilance->GPC['filter_bidtype'])
                {
                        $bidtype1 = 'checked="checked"';
                        $bidtype2 = '';
                }
                $bidamounttype = isset($ilance->GPC['filtered_bidtype']) ? $ilance->GPC['filtered_bidtype'] : '';
                $pulldown = $this->construct_bidamounttype_pulldown($bidamounttype, 0, 1, $cid, $cattype);
                $html = '<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">';
                if ($show['bidamounttypes'])
                {
                        $html .= '<tr class="alt1">
	<td width="1%"><input type="radio" id="biddingtype" name="filter_bidtype" ' . $bidtype1 . ' value="1" /></td>
	<td align="left">' . $pulldown . '</td>
</tr>';    
                }
		unset($bidamounttype_pulldown);
                $html .= '<tr>
	<td><input type="radio" id="biddingtype0" name="filter_bidtype" ' . $bidtype2 . ' value="0" /></td>
	<td align="left"><label for="biddingtype0"><strong>{_i_will_accept_various_bidding_types_no_restriction}</strong></label></td>
</tr>
</table>';
                return $html;
        }
        
        /**
        * Function to print the budget logic selectable options.  For example, the poster could select
        * "I do not wish to disclose my budget" or he/she can select the appropriate budget range to select.
        * Additionally, admins can assign "insertion fees" to any budget group.  Insertion fees will also be
        * shown (their value) if they have been assigned to this category (based on the level of budget).  EX:
        * - Small Project ($100-$500) - Insertion Fee: $3.00
        * - Medium Project ($500-$1000) - Insertion Fee: $5.00
        * etc.
        *
        * @param       integer       category id
        * @param       string        category type (service)
        * @param       bool          do javascript (default true)
        * @param       bool          show insertion fees (default false)
        * 
        * @return      string        HTML representation of the bid amount type form elements
        */
        function print_budget_logic_type($cid = 0, $cattype = 'service', $dojs = 1, $showinsertionfees = false)
        {
                global $ilance, $ilconfig, $phrase, $show;
                $budget1 = '';
                $budget2 = 'checked="checked"';
                if (!empty($ilance->GPC['filter_budget']) AND $ilance->GPC['filter_budget'])
                {
                        $budget1 = 'checked="checked"';
                        $budget2 = '';
                }
                $selected = isset($ilance->GPC['filtered_budgetid']) ? intval($ilance->GPC['filtered_budgetid']) : '';
                // budget pulldown also sets $show['budgetgroups'] to true or false for logic below
                $show['selectedbudgetlogic'] = 0;
                $budget_pulldown = $this->construct_budget_pulldown($cid, $selected, 'filtered_budgetid', $dojs, $showselect = 0, $showinsertionfees);
                $hidden = '';
                $html = '<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">';                
                if ($show['budgetgroups'])
                {
                        $html .= '<tr class="alt1">
	<td width="1%">
		<input type="radio" id="showbudget" name="filter_budget" ' . $budget1 . ' value="1" />
	</td>
	<td align="left">' . $budget_pulldown . '</td>
</tr>';
                }
                else
                {
                        $hidden = '<input type="hidden" name="filtered_budgetid" value="0" />';
                }
                $nondisclosefeeamount = '{_free}';
                $amount = $ilance->categories->nondisclosefeeamount($cid);
                if (!empty($amount) AND $amount > 0)
                {
                        $nondisclosefeeamount = $ilance->currency->format($amount);
                }
                if (empty($selected))
                {
                        $show['selectedbudgetlogic'] = $amount;
                }
                $html .= '<tr>
	<td width="1%"><input type="radio" id="showbudget0" name="filter_budget" ' . $budget2 . ' value="0" />' . $hidden . '</td>
	<td align="left"><label for="showbudget0"><strong>{_i_prefer_not_to_disclose_my_budget}</strong> &nbsp;<span class="smaller gray">(' . $nondisclosefeeamount . ')</span></label></td>
</tr>
</table>';
                return $html;
        }
        
        /**
        * Function to print the budget links
        *
        * @param       integer       category id
        * @param       string        category type (service/product)
        * @param       integer       selected budget id
        *
        * @return      string        HTML representation of the budget as a link
        */
        function print_budget_logic_type_links($cid = 0, $cattype = 'service', $selected = '')
        {
                global $ilance, $ilconfig, $phrase, $show;
                $html = $budgetgroup = '';
                $url = PHP_SELF;
                if ($cid == 0)
                {
                        $show['budgetgroups'] = false;
                        return;
                }
                $query = $ilance->db->query("
                        SELECT budgetgroup
                        FROM " . DB_PREFIX . "categories
                        WHERE cid = '" . intval($cid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($query) > 0)
                {
                        $res = $ilance->db->fetch_array($query, DB_ASSOC);
                        $budgetgroup = $res['budgetgroup'];
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "budget
                                WHERE budgetgroup = '" . $ilance->db->escape_string($budgetgroup) . "'
                                ORDER BY budgetfrom ASC
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $show['budgetgroups'] = true;
                                $counter = 0;
                                // give user choice to select "any"
                                if (isset($selected) AND $selected == '0')
                                {
                                        $html .= '<div style="padding-bottom:3px"><strong>{_any_budget}</strong></div>';
                                }
                                else
                                {
                                        $html .= '<div style="padding-bottom:3px"><a href="' . $url . '&amp;budget=0">{_any_budget}</a></div>';
                                }
                                if (isset($selected) AND $selected == '-1')
                                {
                                        $html .= '<div style="padding-bottom:3px"><strong>{_non_disclosed}</strong></div>';
                                }
                                else
                                {
                                        $html .= '<div style="padding-bottom:3px"><a href="' . $url . '&amp;budget=-1">{_non_disclosed}</a></div>';
                                }
                                unset($counter);
                                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                                {
                                        $counter = 0;
                                        if (isset($selected) AND $selected == $res['budgetid'])
                                        {
                                                if ($res['budgetto'] == '-1')
                                                {
                                                        $html .= '<div style="padding-bottom:3px"><strong>' . $ilance->currency->format($res['budgetfrom']) . ' {_or_more}</strong></div>';    
                                                }
                                                else
                                                {
                                                        $html .= '<div style="padding-bottom:3px"><strong>' . $ilance->currency->format($res['budgetfrom']) . ' - ' . $ilance->currency->format($res['budgetto']) . '</strong></div>';
                                                }
                                        }
                                        else 
                                        {
                                                if ($res['budgetto'] == '-1')
                                                {
                                                        $html .= '<div style="padding-bottom:3px"><a href="' . $url . '&amp;budget=' . $res['budgetid'] . '">' . $ilance->currency->format($res['budgetfrom']) . ' {_or_more}</a></div>';    
                                                }
                                                else
                                                {
                                                        $html .= '<div style="padding-bottom:3px"><a href="' . $url . '&amp;budget=' . $res['budgetid'] . '">' . $ilance->currency->format($res['budgetfrom']) . ' - ' . $ilance->currency->format($res['budgetto']) . '</a></div>';
                                                }
                                        }
                                }
                                unset($counter);
                        }
                }
                return $html;
        }
        
        /**
        * Function to print the budget javascript details for the yahoo slider widget
        *
        * @param       integer       category id
        * @param       string        category type (service/product)
        * @param       integer       selected budget id
        *
        * @return      string        array of budgets for yahoo slider widget
        */
        function print_budget_logic_type_js($cid = 0, $cattype = 'service', $selected = '')
        {
                global $ilance, $ilconfig, $phrase, $show;
                $htmla = "new Array(";
                $htmlb = "new Array(";
                $budgetgroup = '';
                if ($cid == 0 OR $cattype != 'service')
                {
                        $show['budgetgroups'] = false;
                        return;
                }
                $query = $ilance->db->query("
                        SELECT budgetgroup
                        FROM " . DB_PREFIX . "categories
                        WHERE cid = '" . intval($cid) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($query) > 0)
                {
                        $res = $ilance->db->fetch_array($query, DB_ASSOC);
                        $show['budgetgroups'] = true;
                        $htmla .= "'" . '{_any_budget}' . "',";
                        $htmlb .= "'0',";
                        $htmla .= "'" . '{_non_disclosed_budget}' . "',";
                        $htmlb .= "'-1',";
                        $budgetgroup = $res['budgetgroup'];
                        $sql2 = $ilance->db->query("
                                SELECT budgetid, budgetgroup, title, fieldname, budgetfrom, budgetto, insertiongroup
                                FROM " . DB_PREFIX . "budget
                                WHERE budgetgroup = '" . $ilance->db->escape_string($budgetgroup) . "'
                                ORDER BY budgetfrom ASC
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql2) > 0)
                        {
                                while ($res2 = $ilance->db->fetch_array($sql2, DB_ASSOC))
                                {
                                        if ($res2['budgetto'] == '-1')
                                        {
                                                $htmla .= "'" . $ilance->currency->format($res2['budgetfrom'], '', false, true) . " " . '{_or_more}' . "',";
                                        }
                                        else
                                        {
                                                $htmla .= "'" . $ilance->currency->format($res2['budgetfrom'], '', false, true) . " " . '{_to}' . " " . $ilance->currency->format($res2['budgetto'], '', false, true) . "',";
                                        }
                                        $htmlb .= "'" . $res2['budgetid'] . "',";
                                }
                        }
                        $htmla = mb_substr($htmla, 0, -1);
                        $htmlb = mb_substr($htmlb, 0, -1);
                }
                $htmla .= ");";
                $htmlb .= ");";
                return array($htmla, $htmlb);
        }
        
        /**
        * Function to print the escrow filter option so that the auction poster can enable or disable
        * escrow to secure any funds for mentioned services or products during the transaction/delivery
        * process.
        *
        * @param       integer       category id
        * @param       string        category type (service)
        * @param       string        fee type (service, servicebuyer, serviceprovider, productmerchant, productbuyer)
        * 
        * @return      string        HTML representation of the filter form elements
        */
        function print_escrow_filter($cid = 0, $cattype = '', $feetype = '', $disabled = false)
        {
                global $ilance, $ilconfig, $ilpage, $phrase, $onload, $show;
                
		// #### using escrow ###########################################
		$escrowfee = '';
                $escrow1 = $escrow3 = $escrow2 = $escrow4 = '';
                if (!empty($ilance->GPC['filter_escrow']) AND $ilance->GPC['filter_escrow'] == '1')
                {
                        $escrow1 = 'checked="checked"';
                }
		// #### using direct payment gateway ###########################
                if (!empty($ilance->GPC['filter_gateway']) AND $ilance->GPC['filter_gateway'] == '1' AND $cattype == 'product')
                {
                        // using direct payment gateway
                        $escrow3 = 'checked="checked"';
                        $onload .= 'toggle_show(\'gatewaymethods\'); ';
                }
                // #### using direct payment gateway ###########################
                if (!empty($ilance->GPC['filter_ccgateway']) AND $ilance->GPC['filter_ccgateway'] == '1' AND $cattype == 'product')
                {
                        // using direct payment gateway
                        $escrow4 = 'checked="checked"';
                        $onload .= 'toggle_show(\'gatewayccmethods\'); ';
                }
		// #### using offline payment method ###########################
                if (!empty($ilance->GPC['filter_offline']) AND $ilance->GPC['filter_offline'] == '1')
                {
                        // default show offline outside marketplace payment options
			$escrow2 = 'checked="checked"';
                        $onload .= 'toggle_show(\'paymentmethods\'); ';
                }
                if ($cattype == 'service' AND $ilconfig['escrowsystem_enabled'] AND $ilconfig['escrowsystem_escrowcommissionfees'])
                {
			if ($ilconfig['escrowsystem_servicebuyerfixedprice'] != '0')
			{
				$escrowfee = '<span class="smaller gray">(' . $ilance->currency->format($ilconfig['escrowsystem_servicebuyerfixedprice']) . ' {_final_value_commission_fee})</span>';
			}
			else if ($ilconfig['escrowsystem_servicebuyerpercentrate'] != '0.0')
			{
				$escrowfee = '<span class="smaller gray">(' . $ilconfig['escrowsystem_servicebuyerpercentrate'] . '% {_final_value_commission_fee})</span>';
			}
                }
                else if ($cattype == 'product' AND $ilconfig['escrowsystem_enabled'] AND $ilconfig['escrowsystem_escrowcommissionfees'])
                {
			if ($ilconfig['escrowsystem_merchantfixedprice'] != '0')
			{
				$escrowfee = '<span class="smaller gray">(' . $ilance->currency->format($ilconfig['escrowsystem_merchantfixedprice']) . ' {_final_value_commission_fee})</span>';
			}
			else if ($ilconfig['escrowsystem_merchantpercentrate'] != '0.0')
			{
				$escrowfee = '<span class="smaller gray">(' . $ilconfig['escrowsystem_merchantpercentrate'] . '% {_final_value_commission_fee})</span>';
			}
                }
		// #### rebuild seller selected ipn gateway payment emails #####
                $paymethodoptionsemail = (isset($ilance->GPC['paymethodoptionsemail']) AND !empty($ilance->GPC['paymethodoptionsemail']) AND is_serialized($ilance->GPC['paymethodoptionsemail'])) ? unserialize($ilance->GPC['paymethodoptionsemail']) : array();
		$paymethodoptions = (isset($ilance->GPC['paymethodoptions']) AND !empty($ilance->GPC['paymethodoptions']) AND is_serialized($ilance->GPC['paymethodoptions'])) ? unserialize($ilance->GPC['paymethodoptions']) : array();
                $show['nodirectpaymentgateways'] = false;
		$gatewaypulldowns = '';
                $sql = $ilance->db->query("
                        SELECT groupname
                        FROM " . DB_PREFIX . "payment_groups
                        WHERE moduletype = 'ipn'
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $num = 0;
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                if (isset($ilconfig[$res['groupname'] . '_directpayment']) AND $ilconfig[$res['groupname'] . '_directpayment'])
                                {
					if (isset($ilance->GPC['cmd']) AND ($ilance->GPC['cmd'] == 'new-rfp') OR ($ilance->GPC['cmd'] == 'new-item' OR $ilance->GPC['cmd'] == 'sell' AND $ilance->GPC['mode'] == 'bulk'))
					{
						if ($res['groupname'] == 'platnosci')
						{
							$gatewaypulldowns .= '<div style="padding-top:3px; padding-bottom:3px"><label for="paymethodoptions_' . $res['groupname'] . '"><input type="checkbox" name="paymethodoptions[' . $res['groupname'] . ']" id="paymethodoptions_' . $res['groupname'] . '" value="1" /> ' . ucfirst($res['groupname']) . '</label> </div>';
						}
						else 
						{
							$gatewaypulldowns .= '<div style="padding-top:3px; padding-bottom:3px"><label for="paymethodoptions_' . $res['groupname'] . '"><input type="checkbox" name="paymethodoptions[' . $res['groupname'] . ']" id="paymethodoptions_' . $res['groupname'] . '" value="1" onclick="toggle_tr(\'cb_paymethodoptionsemail_' . $res['groupname'] . '\')" /> ' . ucfirst($res['groupname']) . '</label> <span style="display:none" id="cb_paymethodoptionsemail_' . $res['groupname'] . '">&nbsp;&nbsp;&nbsp;<span class="smaller blue">{_and_my_email_for_accepting_payments_through_this_gateway_is}</span> &nbsp;&nbsp;<input type="text" name="paymethodoptionsemail[' . $res['groupname'] . ']" id="paymethodoptionsemail_' . $res['groupname'] . '" class="input" size="35" value="' . (isset($paymethodoptionsemail[$res['groupname']]) ? $paymethodoptionsemail[$res['groupname']] : '') . '" /></span></div>';
						}
					}
					else
					{
						if (isset($ilance->GPC['paymethodoptions']) AND is_serialized($ilance->GPC['paymethodoptions']))
						{
							$paymethodopts = unserialize($ilance->GPC['paymethodoptions']);
							if (isset($paymethodopts["$res[groupname]"]) AND $paymethodopts["$res[groupname]"])
							{
								if ($res['groupname'] == 'platnosci')
								{
									$gatewaypulldowns .= '<div style="padding-top:3px; padding-bottom:3px"><label for="paymethodoptions_' . $res['groupname'] . '"><input type="checkbox" name="paymethodoptions[' . $res['groupname'] . ']" id="paymethodoptions_' . $res['groupname'] . '" value="1" checked="checked" onclick="toggle_tr(\'cb_paymethodoptionsemail_' . $res['groupname'] . '\')" /> ' . ucfirst($res['groupname']) . '</label> </div>';
								}
								else 
								{
									$onload .= 'toggle_show(\'cb_paymethodoptionsemail_' . $res['groupname'] . '\'); ';
									$gatewaypulldowns .= '<div style="padding-top:3px; padding-bottom:3px"><label for="paymethodoptions_' . $res['groupname'] . '"><input type="checkbox" name="paymethodoptions[' . $res['groupname'] . ']" id="paymethodoptions_' . $res['groupname'] . '" value="1" checked="checked" onclick="toggle_tr(\'cb_paymethodoptionsemail_' . $res['groupname'] . '\')" /> ' . ucfirst($res['groupname']) . '</label> <span style="display:none" id="cb_paymethodoptionsemail_' . $res['groupname'] . '">&nbsp;&nbsp;&nbsp;<span class="smaller blue">{_and_my_email_for_accepting_payments_through_this_gateway_is}</span> &nbsp;&nbsp;<input type="text" name="paymethodoptionsemail[' . $res['groupname'] . ']" id="paymethodoptionsemail_' . $res['groupname'] . '" class="input" size="35" value="' . (isset($paymethodoptionsemail[$res['groupname']]) ? $paymethodoptionsemail[$res['groupname']] : '') . '" /></span></div>';
								}
							}
							else
							{
								$gatewaypulldowns .= '<div style="padding-top:3px; padding-bottom:3px"><label for="paymethodoptions_' . $res['groupname'] . '"><input type="checkbox" name="paymethodoptions[' . $res['groupname'] . ']" id="paymethodoptions_' . $res['groupname'] . '" value="1" onclick="toggle_tr(\'cb_paymethodoptionsemail_' . $res['groupname'] . '\')" /> ' . ucfirst($res['groupname']) . '</label> <span style="display:none" id="cb_paymethodoptionsemail_' . $res['groupname'] . '">&nbsp;&nbsp;&nbsp;<span class="smaller blue">{_and_my_email_for_accepting_payments_through_this_gateway_is}</span> &nbsp;&nbsp;<input type="text" name="paymethodoptionsemail[' . $res['groupname'] . ']" id="paymethodoptionsemail_' . $res['groupname'] . '" class="input" size="35" value="' . (isset($paymethodoptionsemail[$res['groupname']]) ? $paymethodoptionsemail[$res['groupname']] : '') . '" /></span></div>';
							}
						}
					}
                                        $num++;
                                }
                        }
                        if ($num == 0)
                        {
                                $show['nodirectpaymentgateways'] = true;
                        }
                }
                $show['noccpaymentgateways'] = false;
                $ccgatewaypulldowns = $ccgatewayname = '';
                $num_cc = 0;
                if ($cattype == 'product' AND $ilconfig['paypal_pro_directpayment'] == '1' AND $ilconfig['use_internal_gateway'] == 'paypal_pro' AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
                {
	                $sql_cc = $ilance->db->query("
	                        SELECT groupname, description
	                        FROM " . DB_PREFIX . "payment_groups
	                        WHERE moduletype = 'gateway'
	                        	AND groupname = '" . $ilconfig['use_internal_gateway'] . "'
	                ");
	                if ($ilance->db->num_rows($sql_cc) > 0)
	                {
	                        while ($res_cc = $ilance->db->fetch_array($sql_cc, DB_ASSOC))
	                        {
	                        	if (isset($ilance->GPC['cmd']) AND ($ilance->GPC['cmd'] == 'new-rfp') OR ($ilance->GPC['cmd'] == 'new-item' OR $ilance->GPC['cmd'] == 'sell' AND $ilance->GPC['mode'] == 'bulk'))
					{
						$ccgatewaypulldowns .= '<div style="padding-top:3px; padding-bottom:3px"><label for="paymethodcc_' . $res_cc['groupname'] . '"><input type="checkbox" name="paymethodcc[' . $res_cc['groupname'] . ']" id="paymethodcc_' . $res_cc['groupname'] . '" value="1" /> ' . $ilconfig['paymodulename'] . '</label> </div>';//ucfirst($res_cc['description'])
						$ccgatewayname = $res_cc['description'];
					}
					else
					{
						if (isset($ilance->GPC['paymethodcc']) AND is_serialized($ilance->GPC['paymethodcc']))
						{
							$paymethodopts = unserialize($ilance->GPC['paymethodcc']);
							if (isset($paymethodopts["$res_cc[groupname]"]) AND $paymethodopts["$res_cc[groupname]"])
							{
								$ccgatewaypulldowns .= '<div style="padding-top:3px; padding-bottom:3px"><label for="paymethodcc_' . $res_cc['groupname'] . '"><input type="checkbox" name="paymethodcc[' . $res_cc['groupname'] . ']" id="paymethodcc_' . $res_cc['groupname'] . '" value="1" checked="checked" /> ' . ucfirst($res_cc['description']) . '</label> </div>';
							}
							else
							{
								$ccgatewaypulldowns .= '<div style="padding-top:3px; padding-bottom:3px"><label for="paymethodcc_' . $res_cc['groupname'] . '"><input type="checkbox" name="paymethodcc[' . $res_cc['groupname'] . ']" id="paymethodcc_' . $res_cc['groupname'] . '" value="1" /> ' . ucfirst($res_cc['description']) . '</label> </div>';
							}
						}
					}
					$num_cc++;
	                        }
	                }
                }
                if ($num_cc == 0)
                {
                        $show['noccpaymentgateways'] = true;
                }
                $html = '<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">';
                if ($ilconfig['escrowsystem_enabled'])
                {
                        $html .= '<tr id="enableescrowrow" class="alt1">
	<td width="1%" valign="top"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="checkbox" id="enableescrow1" name="filter_escrow" ' . $escrow1 . ' value="1" /></td>
	<td align="left"><label for="enableescrow1">{_enable_secure_escrow_trading_for_this_project} ' . $escrowfee . '</label></td>
</tr>';
                }
                if ($show['nodirectpaymentgateways'] == false AND $cattype == 'product')
                {
                        $html .= '<tr class="alt1">
	<td width="1%" valign="top"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="checkbox" id="enableescrow3" name="filter_gateway" ' . $escrow3 . ' value="1" onclick="toggle_tr(\'gatewaymethods\');" /></td>
	<td align="left" valign="top"><label for="enableescrow3">{_i_would_like_winning_bidders_or_buyers_to_pay_immediately}</label><div id="gatewaymethods" style="display:none"><div style="padding-top:12px">' . $gatewaypulldowns . '</div></div></td>
</tr>';
                }
                if ($show['noccpaymentgateways'] == false AND $cattype == 'product')
                {
                	$html .= '<tr class="alt1">
	<td width="1%" valign="top"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="checkbox" id="enableescrow4" name="filter_ccgateway" ' . $escrow4 . ' value="1" onclick="toggle_tr(\'gatewayccmethods\');" /></td>
	<td align="left" valign="top"><label for="enableescrow4">{_i_would_like_winning_bidders_or_buyers_to_pay_immediately_using_the_following_credit_cards_payment_gateways}</label><div id="gatewayccmethods" style="display:none"><div style="padding-top:12px">' . $ccgatewaypulldowns . '</div></div></td>
</tr>';
                }
                if ($ilconfig['invoicesystem_enableoffsitepaymenttypes'] == '1')
                {
                	$html .= '<tr><td valign="top"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="checkbox" id="enableescrow2" name="filter_offline" ' . $escrow2 . ' value="1" onclick="toggle_tr(\'paymentmethods\');';
                	$html .= '" /></td><td align="left" valign="top"><label for="enableescrow2">' . (($cattype == 'product')
					? '{_i_prefer_not_to_use_secure_escrow_trading_for_this_project_payments_made_outside_marketplace}'
					: '{_payments_to_awarded_bidders_will_be_conducted_offline}') . '</label><div id="paymentmethods" style="display:none"><div style="padding-top:12px">' . $this->print_payment_method('paymethod', 'paymethod', true) . '</div></div></td>
</tr>';
			}
$html .= '			
</table>
<div style="padding-bottom:7px"></div>';
                
                ($apihook = $ilance->api('print_escrow_filter_end')) ? eval($apihook) : false;
                
                return $html;
        }
        
        /**
        * Function to print the payment method options for a select box
        *
        * @param       integer       selected option
        * @param       string        checkbox fieldname
        * @param       string        show checkboxes for output? (default false)
        * 
        * @return      string        HTML representation of the pulldown <option>
        */
	function print_payment_method_options($selected = '', $cbfieldname = '', $checkboxes = false)
	{
                global $ilance, $phrase;
                $html = '';
                $sql = $ilance->db->query("
                        SELECT id, title
                        FROM " . DB_PREFIX . "payment_methods
                        ORDER BY sort ASC
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				if ($checkboxes == false)
				{
					$bit = (isset($selected) AND $selected == $res['title']) ? 'selected="selected"' : '';
					$html .= '<option value="' . $res['title'] . '" ' . $bit . '>{' . $res['title'] . '}</option>';
				}
				else
				{
					$bit = (isset($selected) AND $selected == $res['title']) ? 'checked="checked"' : '';
					if (isset($ilance->GPC['cmd']) AND ($ilance->GPC['cmd'] == 'new-rfp') OR ($ilance->GPC['cmd'] == 'categories' AND $ilance->GPC['mode'] == 'bulk') OR ($ilance->GPC['cmd'] == 'new-item' OR $ilance->GPC['cmd'] == 'sell' AND $ilance->GPC['mode'] == 'bulk'))
					{
						$html .= '<div style="padding-top:4px"><label for="cb_' . $res['title'] . '"><input type="checkbox" id="cb_' . $res['title'] . '" name="' . $cbfieldname . '[]" value="' . $res['title'] . '" ' . $bit . ' /> {' . $res['title'] . '}</label></div>';
					}
					else
					{
						if (isset($ilance->GPC['paymethod']) AND is_serialized($ilance->GPC['paymethod']))
						{
							$paymethodopts = unserialize($ilance->GPC['paymethod']);
							if (isset($paymethodopts) AND is_array($paymethodopts) AND in_array($res['title'], $paymethodopts))
							{
								$html .= '<div style="padding-top:4px"><label for="cb_' . $res['title'] . '"><input type="checkbox" id="cb_' . $res['title'] . '" name="' . $cbfieldname . '[]" value="' . $res['title'] . '" checked="checked" /> {' . $res['title'] . '}</label></div>';
							}
							else
							{
								$html .= '<div style="padding-top:4px"><label for="cb_' . $res['title'] . '"><input type="checkbox" id="cb_' . $res['title'] . '" name="' . $cbfieldname . '[]" value="' . $res['title'] . '" /> {' . $res['title'] . '}</label></div>';
							}
						}	
					}
				}
			}
                }
                return $html;        
        }
        
        /**
        * Function to print the payment method input field to allow the poster to enter delivery instructions.
        *
        * @return      string        HTML representation of the payment method form elements
        */
        function print_payment_method($fieldname = 'paymethod', $id = 'paymethod', $checkboxes = false)
        {
                global $ilance, $ilconfig, $phrase;
               	$selected = '';
		if ($checkboxes == false)
		{
			if (isset($ilance->GPC["$fieldname"]) AND !empty($ilance->GPC["$fieldname"]))
			{
				$selected = $ilance->GPC["$fieldname"];
			}
			$html = '<select name="' . $fieldname . '" id="' . $id . '" style="font-family: verdana">';
			$html .= $this->print_payment_method_options($selected);
			$html .= '</select>';
		}
		else
		{
			$html = $this->print_payment_method_options($selected, $fieldname, $checkboxes);	
		}
                return $html;
        }
        
        /**
        * Function to print the auction event type form filtering options.
        *
        * @return      string        HTML representation of the payment method form elements
        */
        function print_event_type_filter($cattype = '', $fieldname = 'project_details', $disabled = false)
        {
                global $ilance, $ilconfig, $phrase, $onload, $js, $invitelist, $memberinvitelist, $show;
                $event1 = $event3 = $event4 = $event5 = '';
                if (!empty($ilance->GPC['project_details']) AND $ilance->GPC['project_details'] == 'public')
                {
                        $event1 = 'checked="checked"';
                }
                else if (!empty($ilance->GPC['project_details']) AND $ilance->GPC['project_details'] == 'invite_only')
                {
                        $event3 = 'checked="checked"';
                        $onload .= 'duration_switch(3); ';
                        // at this point, the auction poster could be adding new members
                        // to the currently selected invitation list so lets find out
                        $invitemessage = isset($ilance->GPC['invitemessage']) ? handle_input_keywords($ilance->GPC['invitemessage']) : '';
                }
                else if (!empty($ilance->GPC['project_details']) AND $ilance->GPC['project_details'] == 'realtime')
                {
                        $event4 = 'checked="checked"';
                        $onload .= 'duration_switch(2); toggle_show(\'scheduledate\'); ';
                }
                else
                {
                        $event1 = 'checked="checked"';
                        $event3 = $event4 = $event5 = '';
                }
                // populate realtime bidding scheduled date pulldown data
                $ilance->GPC['year'] = isset($ilance->GPC['year']) ? intval($ilance->GPC['year']) : '';
                $ilance->GPC['month'] = isset($ilance->GPC['month']) ? intval($ilance->GPC['month']) : '';
                $ilance->GPC['day'] = isset($ilance->GPC['day']) ? intval($ilance->GPC['day']) : '';
                $ilance->GPC['hour'] = isset($ilance->GPC['hour']) ? intval($ilance->GPC['hour']) : '';
                $ilance->GPC['min'] = isset($ilance->GPC['min']) ? intval($ilance->GPC['min']) : '';
                $ilance->GPC['sec'] = isset($ilance->GPC['sec']) ? intval($ilance->GPC['sec']) : '';
		$html = '<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
<tr class="alt1">
	<td width="3%" valign="top"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="radio" name="' . $fieldname . '" id="public" value="public" ' . $event1 . ' onclick="duration_switch(1); fetch_js_object(\'showsellingformat\').style.display=\'\'; toggle_show(\'enableescrowrow\'); toggle_show(\'donations\');" /></td>
	<td width="97%"><label for="public"><strong><span id="public_title">{_public_event}</span></strong> : <span class="gray"><span id="public_help">{_publically_available_auction}</span></span></label></td>
</tr>';
                if ($cattype == 'service')
                {
                        $html .= '<tr class="alt1">
	<td valign="top"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="radio" name="' . $fieldname . '" id="invite_only" value="invite_only" ' . $event3 . ' onclick="duration_switch(3); toggle_tr(\'ivnitetog\'); toggle_show(\'enableescrowrow\'); toggle_show(\'donations\');" /></td>
	<td width="100%"><label for="invite_only"><strong>{_invitation_event}</strong> : <!-- <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/invite.gif" border="0" alt="" />--><span class="gray">{_invite_vendors_to_place_bids_on_your_auction}</span></label>
	<div id="ivnitetog" style="display:none">
		<div style="padding-top:7px"></div>
		<div>
		<table width="100%" border="0" cellpadding="0" cellspacing="0" style="padding:0px 2px 15px 0px;" dir="' . $ilconfig['template_textdirection'] . '">
		<tr>
		<td>
		<div class="grayborder" style="background:url(' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/bg_gradient_gray_1x1000.gif) repeat-x;"><div class="n"><div class="e"><div class="w"></div></div></div><div id="tempinvite" style="margin-left:20px;">
		
		</div><div class="s"><div class="e"><div class="w"></div></div></div></div>
		</td>
		</tr>
		</table>
		</div>
		</div>

	</td>
</tr>';
                }
                if ($ilconfig['product_scheduled_bidding_block'])
		{
			$year = $month = $day = $hour = $min = $sec = '';
			if (isset($ilance->GPC['date_starts']))
			{
			       $arr = explode(" ", $ilance->GPC['date_starts']);
			       $date = explode("-", $arr[0]);
			       $time = explode(":", $arr[1]);
			       $year = isset($date[0]) ? intval($date[0]) : '';
			       $month = isset($date[1]) ? intval($date[1]) : '';
			       $day = isset($date[2]) ? intval($date[2]) : '';
			       $hour = isset($time[0]) ? intval($time[0]) : '';
			       $min = isset($time[1]) ? intval($time[1]) : '';
			       $sec = isset($time[2]) ? intval($time[2]) : '';
			}
			$html .= '<tr>';
			$html .= '<td valign="top"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="radio" name="' . $fieldname . '" id="realtime" value="realtime" ' . $event4 . ' onclick="duration_switch(2); toggle_tr(\'scheduledate\'); fetch_js_object(\'showsellingformat\').style.display=\'\'; toggle_show(\'enableescrowrow\'); toggle_show(\'donations\');" /></td>
	<td><label for="realtime"><div style="height:20px;margin-top:4px"><strong><span id="realtime_title">{_invitation_event_realtime}</span></strong> : <span class="gray"><span id="realtime_help">{_invite_vendors_to_place_bids_on_your}</span></span></div></label>
		<div id="scheduledate" style="display:none">
		<table style="width:400px;margin-left:-10px" border="0" cellspacing="3" cellpadding="2" dir="' . $ilconfig['template_textdirection'] . '">
		<tr>
			<td>
				<table width="100%" border="0" cellspacing="0" cellpadding="0" dir="' . $ilconfig['template_textdirection'] . '">
				<tr>
					<td align="center" nowrap="nowrap">' . $this->year($year) . '&nbsp;</td>
					<td align="center" nowrap="nowrap">' . $this->month($month) . '&nbsp;</td>
					<td align="center" nowrap="nowrap">' . $this->day($day) . '</td>
					<td align="center" nowrap="nowrap">&nbsp;&nbsp;&nbsp;&nbsp;</td>
					<td align="center" nowrap="nowrap">' . $this->hour($hour) . '&nbsp;</td>
					<td align="center" nowrap="nowrap">' . $this->min($min) . '&nbsp;</td>
					<td align="center" nowrap="nowrap">' . $this->sec($sec) . '</td>
				</tr>
				<tr>
					<td align="center" class="smaller litegray" nowrap="nowrap">{_year}</td>
					<td align="center" class="smaller litegray" nowrap="nowrap">{_month}</td>
					<td align="center" class="smaller litegray" nowrap="nowrap">{_day}</td>
					<td align="center" class="smaller litegray" nowrap="nowrap">&nbsp;</td>
					<td align="center" class="smaller litegray" nowrap="nowrap">{_hour}</td>
					<td align="center" class="smaller litegray" nowrap="nowrap">{_min}</td>
					<td align="center" class="smaller litegray" nowrap="nowrap">{_sec}</td>
				</tr>
				</table>
			</td>
		</tr>
		</table>
		</div>
	</td>
</tr>';
		}
                
                $html .= '</table>';
                
                ($apihook = $ilance->api('print_event_type_filter')) ? eval($apihook) : false;
                
                return $html;
        }
        
	function fetch_category_duration($cid = 0, $unittype = 'D')
	{
		global $ilance, $ilconfig;
		switch ($unittype)
		{
			case 'D':
			{
				$field = 'durationdays';
				break;
			}
			case 'H':
			{
				$field = 'durationhours';
				break;
			}
			case 'M':
			{
				$field = 'durationminutes';
				break;
			}
		}
		$sql = $ilance->db->query("
			SELECT $field AS value
			FROM " . DB_PREFIX . "categories
			WHERE cid = '" . intval($cid) . "'
			LIMIT 1
		");
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		if (empty($res['value']))
		{
			return $ilconfig[$field];
		}
		return $res['value'];
	}
	
        /**
        * Function to print a duration pulldown menu.
        *
        * @param       string        selected duration value (optional)
        * @param       string        fieldname
        * @param       boolean       disable pull down (default false)
        * @param       string        default unit type (default D = days)
        * @param       boolean       determine if we should force hiding prices in pull down (usually on listing update)
        * @param       integer       category id override (so we can collect it's own duration logic vs globally)
        * @param       boolean       show or hide the good until cancelled option within the pull down menu (default show)
        *
        * @return      string        HTML representation of the duration pulldown menu
        */
        function duration($duration = '', $fieldname = 'duration', $disabled = false, $unittype = 'D', $showprices = true, $cid = 0, $showgtc = true)
        {
                global $ilance, $ilconfig;
		$options = '';
		switch ($unittype)
		{
			case 'D':
			{
				$unitnumbers = ($cid > 0 ? explode(',', $this->fetch_category_duration($cid, $unittype)) : explode(',', $ilconfig['durationdays']));
				foreach ($unitnumbers AS $days)
				{
					if (strchr($days, ':'))
					{
						$daybits = explode(':', $days);
						$pricebit = '';
						if ($showprices)
						{
							$pricebit = ' (' . $ilance->currency->format($daybits[1]) . ')';
						}
						if ($daybits[0] == 'GTC' AND $showgtc)
						{
							if (isset($duration) AND $duration == $daybits[0])
							{
								$options .= '<option value="' . $daybits[0] . '" selected="selected">{_' . strtolower($daybits[0]) . '}' . $pricebit . '</option>';
							}
							else
							{
								$options .= '<option value="' . $daybits[0] . '">{_' . strtolower($daybits[0]) . '}' . $pricebit . '</option>';
							}
						}
						else
						{
							if (isset($duration) AND $duration == $daybits[0])
							{
								$options .= '<option value="' . ($daybits[0] < 10 ? '0' : '') . $daybits[0] . '" selected="selected">' . ($daybits[0] < 10 ? '&nbsp;&nbsp;' : '') . $daybits[0] . ' ' . ($days == 1 ? '{_day_lower}' : '{_days_lower}') . $pricebit . '</option>';
							}
							else
							{
								$options .= '<option value="' . ($daybits[0] < 10 ? '0' : '') . $daybits[0] . '">' . ($daybits[0] < 10 ? '&nbsp;&nbsp;' : '') . $daybits[0] . ' ' . ($days == 1 ? '{_day_lower}' : '{_days_lower}') . $pricebit . '</option>';
							}
						}
					}
					else
					{
						if ($days == 'GTC' AND $showgtc)
						{
							$options .= '<option value="GTC">{_gtc}</option>';
						}
						else
						{
							if (isset($duration) AND $duration == $days)
							{
								$options .= '<option value="' . ($days < 10 ? '0' : '') . $days . '" selected="selected">' . ($days < 10 ? '&nbsp;&nbsp;' : '') . $days . ' ' . ($days == 1 ? '{_day_lower}' : '{_days_lower}') . '</option>';
							}
							else
							{
								$options .= '<option value="' . ($days < 10 ? '0' : '') . $days . '">' . ($days < 10 ? '&nbsp;&nbsp;' : '') . $days . ' ' . ($days == 1 ? '{_day_lower}' : '{_days_lower}') . '</option>';
							}
						}

					}
				}
				break;
			}
			case 'H':
			{
				$unitnumbers = ($cid > 0 ? explode(',', $this->fetch_category_duration($cid, $unittype)) : explode(',', $ilconfig['durationhours']));
				foreach ($unitnumbers AS $hours)
				{
					if (strchr($hours, ':'))
					{
						$hourbits = explode(':', $hours);
						$pricebit = '';
						if ($showprices)
						{
							$pricebit = ' (' . $ilance->currency->format($hourbits[1]) . ')';
						}
						if (isset($duration) AND $duration == $hourbits[0])
						{
							$options .= '<option value="' . ($hourbits[0] < 10 ? '0' : '') . $hourbits[0] . '" selected="selected">' . ($hourbits[0] < 10 ? '&nbsp;&nbsp;' : '') . $hourbits[0] . ' ' . ($hours == 1 ? '{_hour_lower}' : '{_hours_lower}') . $pricebit . '</option>';
						}
						else
						{
							$options .= '<option value="' . ($hourbits[0] < 10 ? '0' : '') . $hourbits[0] . '">' . ($hourbits[0] < 10 ? '&nbsp;&nbsp;' : '') . $hourbits[0] . ' ' . ($hours == 1 ? '{_hour_lower}' : '{_hours_lower}') . $pricebit . '</option>';
						}
					}
					else
					{
						if (isset($duration) AND $duration == $hours)
						{
							$options .= '<option value="' . ($hours < 10 ? '0' : '') . $hours . '" selected="selected">' . ($hours < 10 ? '&nbsp;&nbsp;' : '') . $hours . ' ' . ($hours == 1 ? '{_hour_lower}' : '{_hours_lower}') . '</option>';
						}
						else
						{
							$options .= '<option value="' . ($hours < 10 ? '0' : '') . $hours . '">' . ($hours < 10 ? '&nbsp;&nbsp;' : '') . $hours . ' ' . ($hours == 1 ? '{_hour_lower}' : '{_hours_lower}') . '</option>';
						}
					}
				}
				break;
			}
			case 'M':
			{
				$unitnumbers = ($cid > 0 ? explode(',', $this->fetch_category_duration($cid, $unittype)) : explode(',', $ilconfig['durationminutes']));
				foreach ($unitnumbers AS $minutes)
				{
					if (strchr($minutes, ':'))
					{
						$minutebits = explode(':', $minutes);
						$pricebit = '';
						if ($showprices)
						{
							$pricebit = ' (' . $ilance->currency->format($minutebits[1]) . ')';
						}
						if (isset($duration) AND $duration == $minutebits[0])
						{
							$options .= '<option value="' . ($minutebits[0] < 10 ? '0' : '') . $minutebits[0] . '" selected="selected">' . ($minutebits[0] < 10 ? '&nbsp;&nbsp;' : '') . $minutebits[0] . ' ' . ($minutes == 1 ? '{_minute_lower}' : '{_minutes_lower}') . $pricebit . '</option>';
						}
						else
						{
							$options .= '<option value="' . ($minutebits[0] < 10 ? '0' : '') . $minutebits[0] . '">' . ($minutebits[0] < 10 ? '&nbsp;&nbsp;' : '') . $minutebits[0] . ' ' . ($minutes == 1 ? '{_minute_lower}' : '{_minutes_lower}') . $pricebit . '</option>';
						}
					}
					else
					{
						if (isset($duration) AND $duration == $minutes)
						{
							$options .= '<option value="' . ($minutes < 10 ? '0' : '') . $minutes . '" selected="selected">' . ($minutes < 10 ? '&nbsp;&nbsp;' : '') . $minutes . ' ' . ($minutes == 1 ? '{_minute_lower}' : '{_minutes_lower}') . '</option>';
						}
						else
						{
							$options .= '<option value="' . ($minutes < 10 ? '0' : '') . $minutes . '">' . ($minutes < 10 ? '&nbsp;&nbsp;' : '') . $minutes . ' ' . ($minutes == 1 ? '{_minute_lower}' : '{_minutes_lower}') . '</option>';
						}
					}
				}
				break;
			}
		}
                $html = '<select ' . ($disabled ? 'disabled="disabled"' : '') . ' name="' . $fieldname . '" id="' . $fieldname . '" class="select" onchange="if($(\'#duration\').val()==\'GTC\'){$(\'#enhancements_tr_autorelist\').hide();}else{$(\'#enhancements_tr_autorelist\').show();}" >' . $options . '</select>';
                return $html;
        }
        
        /**
        * Function to print a duration radio buttons logic menu.  When a radio button is selected the duration pull down menu will re-load new values.
        *
        * @param       string        selected duration value (optional)
        * @param       string        fieldname
        * @param       boolean       determine if we should disable the radio buttons
        * @param       string        duration pull down menu id (so javascript reloading is possible for d/h/m/ unit parts) default duration
        * @param       boolean       show prices in pull down options (default true)
        * @param       integer       category id
        *
        * @return      string        HTML representation of the duration logic radio button form elements
        */
        function print_duration_logic($duration_unit = 'D', $fieldname = 'duration_unit', $disabled = false, $durationfieldname = 'duration', $showprices = true, $cid = 0)
        {
                global $ilance, $ilconfig, $phrase, $headinclude;
                $duration1 = 'checked="checked"';
                $duration2 = $duration3 = '';
                $preheadinclude = 'document.ilform.' . $fieldname . '[1].checked=true';
                if ($duration_unit == 'D')
                {
                        $duration1 = 'checked="checked"';
                }
                else if ($duration_unit == 'H')
                {
                        $duration2 = 'checked="checked"';
                }
                else if ($duration_unit == 'M')
                {
                        $duration3 = 'checked="checked"';
                }
                if (LOCATION == 'buying')
		{
			// specific javascript includes for realtime duration features
			$headinclude .= '<script type="text/javascript">
<!--
function duration_switch(val)
{
        if (val == \'1\')
        {
                toggle_free(\'scheduledate\')
		toggle_free(\'ivnitetog\')
		invite_swap(\'invitecontain\', \'orginvite\', \'0\')
        }        
        if (val == \'2\')
        {
                toggle_free(\'scheduledate\')
		toggle_free(\'ivnitetog\')
		invite_swap(\'invitecontain\', \'orginvite\', \'0\')
        }        
        if (val == \'3\')
        {
                toggle_free(\'scheduledate\')
		toggle_free(\'ivnitetog\')
		invite_swap(\'invitecontain\', \'orginvite\', \'1\')
        }
}
//-->
</script>';

		}
		else
		{
			$headinclude .= '<script type="text/javascript">
<!--
function duration_switch(val)
{
        if (val == \'1\')
        {
                toggle_free(\'scheduledate\');
	}        
        if (val == \'2\')
        {
                toggle_free(\'scheduledate\');
	}        
        if (val == \'3\')
        {
                toggle_free(\'scheduledate\');
        }
}
function unitpart_switch(val, fieldname)
{
        if (val == \'D\')
        {
		print_duration(duration, fieldname, disabled, \'\', showprices, cid)
	}        
        if (val == \'H\')
        {
		print_duration(divcontainer, fieldname, domestic, international, shipperid, disabled)
	}        
        if (val == \'M\')
        {
		print_duration(divcontainer, fieldname, domestic, international, shipperid, disabled)
        }
}
//-->
</script>';
				
		}
		$html = '<table width="1%" border="0" align="left" cellpadding="0" cellspacing="2" dir="' . $ilconfig['template_textdirection'] . '">
<tr>
	<td width="6%"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' id="rb_days" type="radio" name="' . $fieldname . '" value="D" ' . $duration1 . ' onclick="print_duration(\'\', \'' . $durationfieldname . '\', \'' . intval($disabled) . '\', \'D\', \'' . intval($showprices) . '\', \'' . intval($cid) . '\')" /></td>
	<td width="17%"><label for="rb_days"><strong>{_days}'.'</strong></label>&nbsp;&nbsp;</td>
	<td width="2%"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' id="rb_hours" type="radio" name="' . $fieldname . '" value="H" ' . $duration2 . ' onclick="print_duration(\'\', \'' . $durationfieldname . '\', \'' . intval($disabled) . '\', \'H\', \'' . intval($showprices) . '\', \'' . intval($cid) . '\')" /></td>
	<td width="19%"><label for="rb_hours"><strong>{_hours}</strong></label>&nbsp;&nbsp;</td>
	<td width="6%"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' id="rb_mins" type="radio" name="' . $fieldname . '" value="M" ' . $duration3 . ' onclick="print_duration(\'\', \'' . $durationfieldname . '\', \'' . intval($disabled) . '\', \'M\', \'' . intval($showprices) . '\', \'' . intval($cid) . '\')" /></td>
	<td width="50%"><label for="rb_mins"><strong>{_minutes}</strong></label></td>
</tr>
</table>';                
                return $html;
        }
        
        /**
        * Function to print the invitation boxes and special javascript to let users add more than one row for
        * multiple email addresses
        *
        * @param       string        
        *
        * @return      string        HTML representation of the bid privacy radio options
        */
        function print_invitation_controls($cattype = 'service')
        {
                global $ilance, $ilconfig, $phrase, $headinclude;
                $invitemessage = $sendinvites = '';
                $headinclude .= '
<script type="text/javascript">
<!--
function emailcheck(str)
{
        var at = "@"
        var dot = "."
        var lat = str.value.indexOf(at)
        var lstr = str.value.length
        var ldot = str.value.indexOf(dot)
        if (str.value.indexOf(at) == -1)
        {
                alert_js(phrase[\'_invalid_email\']);
                str.value = \'\';
                return false;
        }
        if (str.value.indexOf(at) == -1 || str.value.indexOf(at) == 0 || str.value.indexOf(at) == lstr)
        {
                alert_js(phrase[\'_invalid_email\']);
                str.value = \'\';
                return false;
        }
        if (str.value.indexOf(dot) == -1 || str.value.indexOf(dot) == 0 || str.value.indexOf(dot) == lstr)
        {
                alert_js(phrase[\'_invalid_email\']);
                str.value = \'\';
                return false
        }
        if (str.value.indexOf(at,(lat+1)) != -1)
        {
                alert_js(phrase[\'_invalid_email\']);
                str.value = \'\';
                return false;
        }
        if (str.value.substring(lat-1,lat) == dot || str.value.substring(lat+1, lat+2) == dot)
        {
                alert_js(phrase[\'_invalid_email\']);
                str.value = \'\';
                return false;
        }
        if (str.value.indexOf(dot,(lat+2)) == -1)
        {
                alert_js(phrase[\'_invalid_email\']);
                str.value = \'\';
                return false;
        }
        if (str.value.indexOf(" ") != -1)
        {
                alert_js(phrase[\'_invalid_email\']);
                str.value = \'\';
                return false;
        }        
        return true					
}
function rem_input_field()
{
        var i = fetch_js_object(\'invite_emails\');
        if (i.rows.length > 1)
        {
                i.removeChild(i.lastChild);
        }
}
function add_input_field() 
{
    var ctr = $(\'#invite_emails input\').length;
    ctr = ctr/2;
    ctr = ctr+1;
    if (ctr > 5) 
    {
	    alert_js(phrase[\'_the_maximum_number_of_people_you_are_sending_this_auction_event_to_has_been_reached\']);
    }
    else
    {
	    $(\'#invite_emails\').append(\'<tr id="inviterow_\'+ctr+\'"><td><div style="padding-top:3px">\'+phrase[\'_email\']+\'</div><input name="invitelist[email][]" onblur="javascript: emailcheck(this)" size="25" type="text" class="input" id="inviteemails_\'+ctr+\'" /></td><td>&nbsp;&nbsp;</td><td><div style="padding-top:3px">\'+phrase[\'_first_name\']+\'</div><input name="invitelist[name][]" size="25" type="text" class="input" id="invitenames_\'+ctr+\'" /></td></tr>\');
    }
}
//-->
</script>';
        
                // invitation message
                $invitemessage = ((isset($ilance->GPC['invitemessage']) AND !empty($ilance->GPC['invitemessage'])) ? strip_vulgar_words($ilance->GPC['invitemessage']) : '');
                $invitelist_row = '';
                $count = 1;
                // re-populate invitation list for reverse auctions only         
                if (!empty($ilance->GPC['invitelist']) AND is_array($ilance->GPC['invitelist']))
                {
                        foreach ($ilance->GPC['invitelist']['email'] AS $key => $emailaddress)
                        {
                                if (!empty($emailaddress) AND is_valid_email($emailaddress))
                                {
                                    	$invitelist_row .= '<tr id="inviterow_' . $count . '"><td><div style="padding-bottom:3px">{_email}</div><input size="25" name="invitelist[email][]" value="' . $emailaddress . '" type="text" class="input" id="inviteemails_' . $count . '" /></td><td>&nbsp;&nbsp;</td><td><div style="padding-bottom:3px">{_first_name}</div><input name="invitelist[name][]" value="' . $ilance->GPC['invitelist']['name']["$key"] . '" type="text" class="input" id="invitenames_' . $count . '" /></td></tr>';
                                        $count++;
                                }
                        }
                }
                // additionally, we'll display any invited users previously selected from the search results page and/or single member invtations
                $inviteduserlist = '';
                if (!empty($_SESSION['ilancedata']['tmp']['invitations']) AND is_serialized($_SESSION['ilancedata']['tmp']['invitations']) AND $ilconfig['globalauctionsettings_serviceauctionsenabled'] AND $cattype == 'service')
                {
                        $invitedusers = unserialize($_SESSION['ilancedata']['tmp']['invitations']);
                        $invitedcount = count($invitedusers);
                        if ($invitedcount > 0)
                        {
                                foreach ($invitedusers AS $userid)
                                {
                                        $inviteduserlist .= '<span class="blue" style="font-size:13px"><label><input type="checkbox" name="invitedmember[]" value="' . $userid . '" checked="checked" /> <strong>' . fetch_user('username', $userid) . '</strong></label></span>, ';
                                }
                                if (!empty($inviteduserlist))
                                {
                                        $inviteduserlist = '<div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div><div class="black"><strong>{_users_from_the_marketplace_you_are_invited_will_appear_below}</strong></div><div style="padding-top:1px" class="smaller gray">{_use_the_checkboxes_to_confirm_or_remove_invited_experts}</div><div style="padding-top:6px"><div style="padding-right:12px; padding-top:9px; padding-bottom:6px">' . mb_substr($inviteduserlist, 0, -2) . '</div></div>';
                                }
                        }
                }
                $invitelist_row .= (empty($invitelist_row)) ? '<tr id="inviterow_' . $count . '"><td><div style="padding-bottom:3px">{_email}</div><input onblur="javascript: emailcheck(this)" size="25" name="invitelist[email][]" type="text" class="input" id="inviteemails_1" /></td><td>&nbsp;&nbsp;</td><td><div style="padding-bottom:3px">{_first_name}</div><input name="invitelist[name][]" size="25" type="text" class="input" id="invitenames_1" /></td></tr>' : '';
		$html = '<table border="0" cellspacing="0" cellpadding="0" dir="' . $ilconfig['template_textdirection'] . '"><tbody id="invite_emails">' . $invitelist_row . '</tbody></table>
<div style="padding-bottom:7px; padding-top:12px" class="smaller gray">
	<span class="smaller blue"><a href="javascript:void(0)" onclick="add_input_field();" id="add">{_add_new_email_contact}</a></span>&nbsp;&nbsp;&nbsp;<span class="smaller gray">|</span>&nbsp;&nbsp;&nbsp;<span class="blue"><a href="javascript:void(0)" onclick="rem_input_field();" id="rem">{_remove_last_contact}</a></span></div>
	<div style="padding-top:6px"></div>' . $inviteduserlist . '
	
	<input name="count" type="hidden" id="count" value="1" />
	<div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>
	
	<div style="padding-top:3px; padding-bottom:3px"><strong>{_enter_invitation_message_to_bidders}</strong></div>
	<div>
	<table cellpadding="0" cellspacing="0" border="0" dir="' . $ilconfig['template_textdirection'] . '">
	<tr>
		<td align="right" height="25">
			<table cellpadding="0" cellspacing="0" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
			<tr>
				<td align="left" class="smaller">{_plain_text_only_bbcode_is_currently_not_in_use_for_this_field}</td>
				<td align="right">
					<div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'invitemessage\', -100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_0.gif" width="21" height="9" alt="{_decrease_size}" border="0" /></a></div>
					<div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'invitemessage\', 100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_1.gif" width="21" height="9" alt="{_increase_size}" border="0" /></a></div>
				</td>
			</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td><textarea name="invitemessage" id="invitemessage" style="width:380px; height:50px; padding:3px;" wrap="physical" class="wysiwyg">' . $invitemessage . '</textarea></td>
	</tr>
	</table>
</div>';
                return $html;        
        }
        
        /**
        * Function to print the auction's keywords / tags used for search engine optimization
        *
        * @param       string        field name
        * @param       boolean       disable field (default false)
        *
        * @return      string        HTML representation of the keywords input
        */
        function print_keywords_input($fieldname = 'keywords', $disabled = false)
        {
                global $ilance, $ilconfig, $phrase;
                $keywords = '';
                if (isset($ilance->GPC['keywords']) AND !empty($ilance->GPC['keywords']))
                {
                        $keywords = $ilance->GPC['keywords'];
                }
                $html = '<input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="text" class="input" name="' . $fieldname . '" id="' . $fieldname . '" maxsize="75" value="' . handle_input_keywords($keywords) . '" title="" style="width:580px" />';
                return $html;
        }
	
	/**
        * Function to print the auction's item identification fields (sku, upc, part number and ean)
        *
        * @param       string        field name
        * @param       boolean       disable field (default false)
        *
        * @return      string        HTML representation of the keywords input
        */
        function print_itemid_input($fieldname = '', $disabled = false)
        {
                global $ilance, $ilconfig, $phrase;
                $value = (isset($ilance->GPC["$fieldname"]) AND !empty($ilance->GPC["$fieldname"])) ? $ilance->GPC["$fieldname"] : '';
                $html = '<input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="text" class="input" name="' . $fieldname . '" id="' . $fieldname . '" maxsize="50" value="' . handle_input_keywords($value) . '" title="" />';
                return $html;
        }
        
        /**
        * Function to print the auction's title input 
        *
        * @param       string        form fieldname
        *
        * @return      string        HTML representation of the keywords input
        */
        function print_title_input($fieldname = 'project_title', $disabled = false)
        {
                global $ilance, $ilconfig;
                $project_title = (isset($ilance->GPC['project_title']) AND !empty($ilance->GPC['project_title'])) ? $ilance->GPC['project_title'] : '';
                if ($ilconfig['globalfilters_maxcharacterstitle'] == 0)
		{
			$html = '<input ' . ($disabled ? 'disabled="disabled"' : '') . ' id="' . $fieldname . '" type="text" class="input" name="' . $fieldname . '" maxsize="75" value="' . $project_title . '" style="width:580px" /> <img name="' . $fieldname . 'error" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif" width="21" height="13" border="0" alt="" />';
		}
		else
		{
			$html = '<input ' . ($disabled ? 'disabled="disabled"' : '') . ' id="' . $fieldname . '" type="text" class="input" name="' . $fieldname . '" maxsize="75" value="' . $project_title . '" style="width:580px" maxlength="'. $ilconfig['globalfilters_maxcharacterstitle'] .'" /> <img name="' . $fieldname . 'error" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif" width="21" height="13" border="0" alt="" />';
		}
                return $html;
        }
        
        /**
        * Function to print the auction's title input 
        *
        * @param       string        form fieldname
        *
        * @return      string        HTML representation of the keywords input
        */
        function print_video_description_input($fieldname = 'description_videourl', $disabled = false)
        {
                global $ilance, $ilconfig, $phrase;
                $description_videourl = '';
                if (isset($ilance->GPC['description_videourl']) AND !empty($ilance->GPC['description_videourl']))
                {
                        $description_videourl = $ilance->GPC['description_videourl'];
                }
                $html = '<input ' . ($disabled ? 'disabled="disabled"' : '') . ' id="' . $fieldname . '" type="text" class="input" name="' . $fieldname . '" maxsize="75" value="' . $description_videourl . '" style="width:580px" />';
                return $html;
        }
        
        /**
        * Function to print the auction's extend features after the listing was posted (update mode)
        *
        * @param       string        form fieldname
        *
        * @return      string        HTML representation of the keywords input
        */
        function print_extend_auction($fieldname = 'extend')
        {
                global $ilance, $ilconfig, $phrase, $headinclude;
                $html = '<div style="padding-top:6px"></div>
<table cellpadding="0" cellspacing="3" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
<tr> 
	<td width="1%" valign="top"><input type="radio" name="' . $fieldname . '" id="extend1" value="0" checked="checked" /></td>
	<td><label for="extend1"><strong>{_keep_current_duration_as_is}</strong></label></td>
	<td width="1%" valign="top"><input type="radio" name="' . $fieldname . '" id="extend3" value="3" /></td>
	<td><label for="extend3">{_extend_for_3_days}</label></td>
</tr>
<tr> 
	<td width="1%" valign="top"><input type="radio" name="' . $fieldname . '" id="extend2" value="1" /></td>
	<td><label for="extend2">{_extend_for_1_day}</label></td>
	<td width="1%" valign="top"><input type="radio" name="' . $fieldname . '" id="extend4" value="7" /></td>
	<td><label for="extend4">{_extend_for_7_days}</label></td>
</tr> 
</table>';
                return $html;
        }
        
        /**
        * Function to print the auction's additional information box
        *
        * @param       string        form fieldname
        *
        * @return      string        HTML representation of the additional info textarea
        */
        function print_additional_info_input($fieldname = 'additional_info', $disabled = false)
        {
                global $ilance, $ilconfig, $phrase;
                $additional_info = (isset($ilance->GPC['additional_info']) AND !empty($ilance->GPC['additional_info'])) ? strip_vulgar_words($ilance->GPC['additional_info']) : '';
                $html = '<div class="ilance_wysiwyg">
<table cellpadding="0" cellspacing="0" border="0" width="580" dir="' . $ilconfig['template_textdirection'] . '">
<tr>
<td class="wysiwyg_wrapper" align="right" height="25">

	<table cellpadding="0" cellspacing="0" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
	<tr>
		<td width="100%" align="left" class="smaller">{_plain_text_only_bbcode_is_currently_not_in_use_for_this_field}</td>
		<td>
			<div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $fieldname . '\', -100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_0.gif" width="21" height="9" alt="{_decrease_size}" border="0" /></a></div>
			<div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'' . $fieldname . '\', 100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_1.gif" width="21" height="9" alt="{_increase_size}" border="0" /></a></div>
		</td>
		<td style="padding-right:15px"></td>
	</tr>
	</table>
</td>
</tr>
<tr>
	<td><textarea ' . ($disabled ? 'disabled="disabled"' : '') . ' name="' . $fieldname . '" id="' . $fieldname . '" style="width:580px; height:84px; padding:8px; font-family: verdana;" wrap="physical" class="wysiwyg">' . $additional_info . '</textarea></td>
</tr>
</table>
</div>';
                return $html;
        }
        
        /**
        * Function to print a the bid privacy form options (open, sealed, blind or full)
        *
        * @param       string        selected duration value (optional)
        *
        * @return      string        HTML representation of the bid privacy radio options
        */
        function print_bid_privacy($fieldname = 'bid_details', $disabled = false)
        {
                global $ilance, $ilconfig, $phrase;
                $privacy1 = 'checked="checked"';
		$privacy2 = $privacy3 = $privacy4 = '';
		if (!empty($ilance->GPC['bid_details']) AND $ilance->GPC['bid_details'] == 'open')
		{
			$privacy1 = 'checked="checked"';
		}
		else if (!empty($ilance->GPC['bid_details']) AND $ilance->GPC['bid_details'] == 'sealed')
		{
			$privacy2 = 'checked="checked"';
		}
		else if (!empty($ilance->GPC['bid_details']) AND $ilance->GPC['bid_details'] == 'blind')
		{
			$privacy3 = 'checked="checked"';
		}
		else if (!empty($ilance->GPC['bid_details']) AND $ilance->GPC['bid_details'] == 'full')
		{
			$privacy4 = 'checked="checked"';
		}
                // subscription permission checkup for setting filter privacy
                $disabled1 = $disabled2 = $disabled3 = false;
                if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'cansealbids') == 'no')
                {
                        $disabled1 = true;        
                }
                if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'canblindbids') == 'no')
                {
                        $disabled2 = true;        
                }
                if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'canfullprivacybids') == 'no')
                {
                        $disabled3 = true;        
                }
                
                if(isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
                {
                	$disabled1 = $disabled2 = $disabled3 = false;
                }
		$html = '<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
<tr class="alt1">
      <td width="3%" valign="top"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="radio" name="' . $fieldname . '" id="open" value="open" ' . $privacy1 . ' ' . ($disabled ? 'disabled="disabled"' : '') . ' /></td>
      <td width="97%"><label for="open"><strong>{_no_bid_privacy_enabled}</strong> : <span class="gray">{_bidder_names_are_shown_bid_amounts_are_shown_and_listing_available_to_search_engines}</span></label></td>
</tr>
<tr class="alt1">
      <td valign="top"><input ' . (($disabled OR $disabled1) ? 'disabled="disabled"' : '') . ' type="radio" name="' . $fieldname . '" id="sealed" value="sealed" ' . $privacy2 . ' ' . ($disabled ? 'disabled="disabled"' : '') . ' /></td>
      <td><label for="sealed"><strong>{_sealed_bidding}</strong> : <span class="gray">{_bidder_names_are_shown_bid_amounts_are_hidden_optional}</span></label></td>
</tr>
<tr class="alt1">
      <td valign="top"><input ' . (($disabled OR $disabled2) ? 'disabled="disabled"' : '') . ' type="radio" id="blind" name="' . $fieldname . '" value="blind" ' . $privacy3 . ' ' . ($disabled ? 'disabled="disabled"' : '') . ' /></td>
      <td><label for="blind"><strong>{_blind_bidding}</strong> : <span class="gray">{_bid_amounts_are_shown_bidder_names_are_hidden_optional}</span></label></td>
</tr>
<tr>
      <td valign="top"><input ' . (($disabled OR $disabled3) ? 'disabled="disabled"' : '') . ' type="radio" id="full" name="' . $fieldname . '" value="full" ' . $privacy4 . ' ' . ($disabled ? 'disabled="disabled"' : '') . ' /></td>
      <td><label for="full"><strong>{_full_privacy_sealed_blind_bidding}</strong> : <span class="gray">{_full_privacy_bidder_names_are_hidden_bid_amounts_are_sealed}</span></label></td>
</tr>
</table>';
                return $html;
        }
        
        /**
        * Function to print the public message board visibility or hidden options for the auction poster.
        *
        * @return      string        HTML representation of the public board form elements
        */
        function print_public_board($fieldname = 'filter_publicboard', $disabled = false)
        {
                global $ilance, $ilconfig, $phrase;
                $publicboard1 = '';
                $publicboard2 = 'checked="checked"';
                if (!empty($ilance->GPC['filter_publicboard']) AND $ilance->GPC['filter_publicboard'])
                {
                        $publicboard1 = 'checked="checked"';
                        $publicboard2 = '';
                }
                $html = '<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
<tr class="alt1">
	<td width="1%" valign="top"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="radio" name="' . $fieldname . '" id="' . $fieldname . '1" value="1" '.$publicboard1.' /></td>
	<td width="100%"><label for="' . $fieldname . '1"><strong>{_public_message_board_enabled}</strong> : <span class="gray">{_select_this_option_if_you_will_allow_a_public_message_board_environment_on_your_auction_listing_page}</span></label></td>
</tr>
<tr>
	<td valign="top"><input type="radio" ' . ($disabled ? 'disabled="disabled"' : '') . ' name="' . $fieldname . '" id="' . $fieldname . '0" value="0" '.$publicboard2.' /></td>
	<td><label for="' . $fieldname . '0"><strong>{_public_message_board_disabled}</strong> : <span class="gray">{_if_you_do_not_want_a_public_message_board_on_your_auction_listing_page_select_this_option}</span></label></td>
</tr>
</table>';
                return $html;
        }
        
        /**
        * Function to print the available auction enhancement upsell options like bold, highlight background, featured, etc.
        *
        * @param       string        category type (service or product)
        * 
        * @return      string        HTML representation of the listing enhancement form options
        */
        function print_listing_enhancements($cattype = 'service', $extra = '')
        {
                global $ilance, $ilconfig, $phrase, $show;
                $sumfees = 0;
		$boldprice_fee = '{_free}';
		$highlite_fee = '{_free}';
		$featured_fee = '{_free}';
                $autorelist_fee = '{_free}';
                $featured_searchresults_fee = '{_free}';
		$showbold = $showhlite = $showfeatured = $showautorelist = $showfeatured_searchresults = 1;
                
                ($apihook = $ilance->api('print_listing_enhancements_start')) ? eval($apihook) : false;
                
                if ($ilconfig["{$cattype}upsell_boldactive"])
                {
                        if ($ilconfig["{$cattype}upsell_boldfees"])
                        {
                                $boldprice_fee = $ilance->currency->format($ilconfig["{$cattype}upsell_boldfee"]);
                        }
                }
		else
		{
			$showbold = 0;
		}
		if ($ilconfig["{$cattype}upsell_highlightactive"])
                {
			if ($ilconfig["{$cattype}upsell_highlightfees"])
                        {
				$highlite_fee = $ilance->currency->format($ilconfig["{$cattype}upsell_highlightfee"]);
			}
		}
		else
		{
			$showhlite = 0;
		}
		if ($ilconfig["{$cattype}upsell_featuredactive"])
		{
			if ($ilconfig["{$cattype}upsell_featuredfees"])
			{
				$featured_fee = $ilance->currency->format($ilconfig["{$cattype}upsell_featuredfee"]);
			}
		}
		else
		{
			$showfeatured = 0;
		}
                if ($ilconfig["{$cattype}upsell_autorelistactive"])
		{
			if ($ilconfig["{$cattype}upsell_autorelistfees"])
			{
				$autorelist_fee = $ilance->currency->format($ilconfig["{$cattype}upsell_autorelistfee"]);
			}
		}
		else
		{
			$showautorelist = 0;
		}
		if ($ilconfig["{$cattype}upsell_featured_searchresultsactive"])
                {
			if ($ilconfig["{$cattype}upsell_featured_searchresultsfees"])
		        {
				$featured_searchresults_fee = $ilance->currency->format($ilconfig["{$cattype}upsell_featured_searchresultsfee"]);
			}
                }
		else
		{
			$showfeatured_searchresults = 0;
		}
		                
		$cb_bold = $cb_highlight = $cb_featured = $cb_autorelist = $cb_featured_searchresults = '';
                
                ($apihook = $ilance->api('print_listing_enhancements_middle')) ? eval($apihook) : false;
                
		if (isset($ilance->GPC['enhancements']))
		{
			foreach ($ilance->GPC['enhancements'] AS $enhancement => $value)
			{
		                // #### BOLD ###################################
				if (isset($enhancement) AND $enhancement == 'bold')
				{
					if ($ilconfig["{$cattype}upsell_boldfees"])
					{
						$boldprice_fee = (($ilconfig["{$cattype}upsell_boldfee"] > 0) ? $ilance->currency->format($ilconfig["{$cattype}upsell_boldfee"]) : '{_free}');
						$sumfees += $ilconfig["{$cattype}upsell_boldfee"];
					}
					if (isset($show['disableselectedenhancements']) AND $show['disableselectedenhancements'])
					{
						$cb_bold = 'checked="checked" disabled="disabled"';
					}
					else
					{
						$cb_bold = 'checked="checked"';        
					}
				}
		                // #### BACKGROUND HIGHLIGHT ###################
				if (isset($enhancement) AND $enhancement == 'highlite')
				{
					if ($ilconfig["{$cattype}upsell_highlightfees"])
					{
						$highlite_fee = (($ilconfig["{$cattype}upsell_highlightfee"] > 0) ? $ilance->currency->format($ilconfig["{$cattype}upsell_highlightfee"]) : '{_free}');
						$sumfees += $ilconfig["{$cattype}upsell_highlightfee"];
					}
					if (isset($show['disableselectedenhancements']) AND $show['disableselectedenhancements'])
					{
						$cb_highlight = 'checked="checked" disabled="disabled"';
					}
					else
					{
						$cb_highlight = 'checked="checked"';        
					}
				}
		                // #### FEATURED ###############################
				if (isset($enhancement) AND $enhancement == 'featured')
				{
					if ($ilconfig["{$cattype}upsell_featuredfees"])
					{
						$featured_fee = (($ilconfig["{$cattype}upsell_featuredfee"] > 0) ? $ilance->currency->format($ilconfig["{$cattype}upsell_featuredfee"]) : '{_free}');
						$sumfees += $ilconfig["{$cattype}upsell_featuredfee"];
					}
					if (isset($show['disableselectedenhancements']) AND $show['disableselectedenhancements'])
					{
						$cb_featured = 'checked="checked" disabled="disabled"';
					}
					else
					{
						$cb_featured = 'checked="checked"';
					}
				}
		                // #### AUTO-RELIST ############################
				if (isset($enhancement) AND $enhancement == 'autorelist')
				{
					if ($ilconfig["{$cattype}upsell_autorelistfees"])
					{
						$autorelist_fee = (($ilconfig["{$cattype}upsell_autorelistfee"] > 0) ? $ilance->currency->format($ilconfig["{$cattype}upsell_autorelistfee"]) : '{_free}');
						$sumfees += $ilconfig["{$cattype}upsell_autorelistfee"];
					}
					if (isset($show['disableselectedenhancements']) AND $show['disableselectedenhancements'])
					{
						$cb_autorelist = 'checked="checked" disabled="disabled"';
					}
					else
					{
						$cb_autorelist = 'checked="checked"';        
					}
				}
				// #### FEATURED ON SEARCH RESULTS ##############
				if (isset($enhancement) AND $enhancement == 'featured_searchresults')
				{
					if ($ilconfig["{$cattype}upsell_featuredfees"])
					{
						$featured_searchresults_fee = (($ilconfig["{$cattype}upsell_featured_searchresultsfee"] > 0) ? $ilance->currency->format($ilconfig["{$cattype}upsell_featured_searchresultsfee"]) : '{_free}');
						$sumfees += $ilconfig["{$cattype}upsell_featured_searchresultsfee"];
					}
					if (isset($show['disableselectedenhancements']) AND $show['disableselectedenhancements'])
					{
						$cb_featured_searchresults = 'checked="checked" disabled="disabled"';
					}
					else
					{
						$cb_featured_searchresults = 'checked="checked"';
					}
				}
		                                
		                ($apihook = $ilance->api('print_listing_enhancements_foreach_end')) ? eval($apihook) : false;
			}
		}

                $jsonclick = 'single';
                if (!empty($extra) AND $extra == 'bulk')
                {
                        $jsonclick = 'bulk';
                }
		$upsell_featuredlength = $cattype == 'service' ? $ilconfig['serviceupsell_featuredlength'] : $ilconfig['productupsell_featuredlength'];
		// #### listing enhancements html display ######################
		$html = '';
		if ($showbold)
		{
			$html .= '<tr class="alt1">
	<td width="1%" valign="top" align="center"><input type="checkbox" name="enhancements[bold]" id="bold" value="1" ' . $cb_bold . ' onclick="return livefeecalculator(\'' . $ilconfig["{$cattype}upsell_boldfee"] . '\', \'' . $jsonclick . '\', \'bold\')" /></td>
	<td width="99%" ><label for="bold">{_bold_title_via_search_results} &nbsp;&nbsp;<span class="smaller gray">(' . $boldprice_fee . ')</span></label></td>
</tr>';
		}
		if ($showhlite)
		{
			$html .= '<tr class="alt1">
	<td width="1%" valign="top" align="center"><input type="checkbox" name="enhancements[highlite]" id="highlite" value="1" ' . $cb_highlight . ' onclick="return livefeecalculator(\'' . $ilconfig["{$cattype}upsell_highlightfee"] . '\', \'' . $jsonclick . '\', \'highlite\')" /></td>
	<td width="99%"><label for="highlite">{_highlight_listing_via_search_results} &nbsp;&nbsp;<span class="smaller gray">(' . $highlite_fee . ')</span></label></td>
</tr>';
		}
		if ($showfeatured)
		{
			$html .= '<tr class="alt1">
	<td width="1%" valign="top" align="center"><input type="checkbox" id="featured" name="enhancements[featured]" value="1" ' . $cb_featured . ' onclick="return livefeecalculator(\'' . $ilconfig["{$cattype}upsell_featuredfee"] . '\', \'' . $jsonclick . '\', \'featured\')" /></td>
	<td width="99%"><label for="featured">{_featured_item_presence} (' . $upsell_featuredlength . ' {_days_lower}) &nbsp;&nbsp;<span class="smaller gray">(' . $featured_fee . ')</span></label></td>
</tr>';
		}     
		if ($showautorelist)
		{
			$hideautorelisttr = ($cattype == 'product' AND (isset($ilance->GPC['gtc']) AND $ilance->GPC['gtc'] == '1') OR (isset($ilance->GPC['id']) AND fetch_auction('gtc', $ilance->GPC['id']) == '1')) ? 'style="display:none;"' : '';
			$html .= '<tr class="alt1" id="enhancements_tr_autorelist" ' . $hideautorelisttr . '>
	<td width="1%" valign="top" align="center"><input type="checkbox" id="autorelist" name="enhancements[autorelist]" value="1" ' . $cb_autorelist . ' onclick="return livefeecalculator(\'' . $ilconfig["{$cattype}upsell_autorelistfee"] . '\', \'' . $jsonclick . '\', \'autorelist\')" /></td>
	<td width="99%"><label for="autorelist">{_automatic_relist_if_listing_receives_no_bids} (' . $ilconfig['productupsell_autorelistmaxdays'] . ' {_days_lower}) &nbsp;&nbsp;<span class="smaller gray">(' . $autorelist_fee . ')</span></label></td>
</tr>';
		}
		if ($showfeatured_searchresults)
		{
			$html .= '<tr>
	<td width="1%" valign="top" align="center"><input type="checkbox" id="featured_searchresults" name="enhancements[featured_searchresults]" value="1" ' . $cb_featured_searchresults . ' onclick="return livefeecalculator(\'' . $ilconfig["{$cattype}upsell_featured_searchresultsfee"] . '\', \'' . $jsonclick . '\', \'featured_searchresults\')" /></td>
	<td width="99%"><label for="featured_searchresults">{_featured_in_search_results} <span class="smaller gray">(' . $featured_searchresults_fee . ')</span></label></td>
</tr>';
		} 
			
		($apihook = $ilance->api('print_listing_enhancements_end')) ? eval($apihook) : false;
			
		$html = !empty($html) ? ('<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">' . $html . '</table>') : '';
		$show['selectedenhancements'] = 0;
		if (isset($sumfees) AND $sumfees > 0)
		{
			$totalfees = number_format($sumfees, 2);
			$show['selectedenhancements'] = $totalfees;
			$totalfees_preview = $ilance->currency->format($sumfees);
		}
		return $html;
        }
        
        /**
        * Function to print any applicable insertion fees during the posting of an auction.  This function takes into consideration if the viewing user is exempt from insertion fees.
        *
        * @param       integer       category id
        * @param       string        category type (service/product)
        * @param       integer       user id
        * 
        * @return      string        HTML representation of the insertion fee table
        */
        function print_insertion_fees($cid = 0, $cattype = '', $userid = 0)
        {
                global $ilance, $phrase, $show, $ilconfig;
                $htmlinsertionfees = '';
                $show['insertionfees'] = 1;
                if ($cattype == 'product')
                {
			$ifgroupname = $ilance->categories->insertiongroup($cid);
			$forceifgroupid = $ilance->permissions->check_access($userid, "{$cattype}insgroup");
			if ($forceifgroupid > 0)
			{
				$ifgroupname = $ilance->db->fetch_field(DB_PREFIX . "insertion_groups", "groupid = '" . intval($forceifgroupid) . "'", "groupname");
			}
                        $sqlinsertions = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "insertion_fees
                                WHERE groupname = '" . $ilance->db->escape_string($ifgroupname) . "'
                                        AND state = '" . $ilance->db->escape_string($cattype) . "'
                                ORDER BY sort ASC
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sqlinsertions) > 0)
                        {
                                while ($rows = $ilance->db->fetch_array($sqlinsertions, DB_ASSOC))
                                {
                                        $from = $ilance->currency->format($rows['insertion_from']);
                                        $to =  ' &ndash; ' . $ilance->currency->format($rows['insertion_to']);
                                        $amount = $ilance->currency->format($rows['amount']);
                                        $show['insertionfeeamount'] = $rows['amount'];
                                        if ($rows['insertion_to'] == '-1')
                                        {
                                                $to = '{_or_more}';
                                        }
                                        $htmlinsertionfees .= '<tr class="alt1"><td valign="top">' . $from . ' ' . $to . '</td><td valign="top"><b>' . $amount . '</b></td></tr>';
                                }
                                $htmlinsertionfees .= '<tr><td valign="top" colspan="2"><span class="gray">{_depending_on_start_price_or_reserve_price_amount_the_greater}</span></td></tr>';
                        }
                        else 
                        {
                                $show['insertionfees'] = $show['insertionfeeamount'] = 0;
                                $htmlinsertionfees .= '<tr><td valign="top" colspan="2"><span class="gray">{_no_insertion_fees_within_this_category}</span></td></tr>';
                        }
                        // check for subscription insertion fee exemption
                        if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'insexempt') == 'yes')
                        {
                                $htmlinsertionfees = '<tr><td valign="top" colspan="2"><span class="gray">{_you_are_exempt_from_insertion_fees}</span></td></tr>';
                        }
                        // product listing fees output display
                        $listingfees = '<div class="block-wrapper">
<div class="block">
	<div class="block-top">
		<div class="block-right">
			<div class="block-left"></div>
		</div>
	</div>
	<div class="block-header">{_insertion_listing_fees}</div>
	<div class="block-content" style="padding:0px">
	<table border="0" cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
	</tr>
	<tr class="alt2">
		<td valign="top"><strong>{_start_price_or_reserve_amount}</strong></td>
		<td valign="top"><strong>{_insertion_fee_amount}</strong></td>
	</tr>
	' . $htmlinsertionfees . '
	</table></div>
	<div class="block-footer">
		<div class="block-right">
			<div class="block-left"></div>
		</div>
	</div>
</div>
</div>';
                }
                
                else if ($cattype == 'service')
                {
			$ifgroupname = $ilance->categories->insertiongroup($cid);
			$forceifgroupid = $ilance->permissions->check_access($userid, "{$cattype}insgroup");
			if ($forceifgroupid > 0)
			{
				$ifgroupname = $ilance->db->fetch_field(DB_PREFIX . "insertion_groups", "groupid = '" . intval($forceifgroupid) . "'", "groupname");
			}
                        $sqlinsertions = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "insertion_fees
                                WHERE groupname = '" . $ilance->db->escape_string($ifgroupname) . "'
                                    AND state = '" . $ilance->db->escape_string($cattype) . "'
                                ORDER BY sort ASC
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sqlinsertions) > 0)
                        {
                                while ($rows = $ilance->db->fetch_array($sqlinsertions, DB_ASSOC))
                                {
                                        $amount = $ilance->currency->format($rows['amount']);
                                        $show['insertionfeeamount'] = $rows['amount'];
                                        if ($rows['insertion_to'] == '-1')
                                        {
                                                $to = '{_or_more}';
                                        }
                                        $htmlinsertionfees .= '<tr class="alt1"><td valign="top">' . stripslashes($ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $cid)) . '</td><td valign="top"><strong>' . $amount . '</strong></td></tr>';
                                }
                                $htmlinsertionfees .= '<tr><td valign="top" colspan="2"><span class="gray">{_you_may_be_required_to_pay_this_fee_in_full_before_public_visibility}</span></td></tr>';
                        }
                        else 
                        {
                                $show['insertionfees'] = $show['insertionfeeamount'] = 0;
                                $htmlinsertionfees .= '<tr><td valign="top" colspan="2"><span class="gray">{_no_insertion_fees_within_this_category}</span></td></tr>';	
                        }
                        // check for subscription insertion fee exemption
                        if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'insexempt') == 'yes')
                        {
                                $htmlinsertionfees = '<tr><td valign="top" colspan="2"><span class="gray">{_you_are_exempt_from_insertion_fees}</span></td></tr>';
                        }
                        $listingfees = '<div class="block-wrapper">
<div class="block2">
	<div class="block2-top">
		<div class="block2-right">
			<div class="block2-left"></div>
		</div>
	</div>
	<div class="block2-header">{_insertion_listing_fees}</div>
	<div class="block2-content" style="padding:0px">
	<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
</tr>
<tr class="alt2">
<td valign="top"><strong>{_category}</strong></td>
<td valign="top"><strong>{_insertion_fee_amount}</strong></td>
</tr>
' . $htmlinsertionfees . '
</table></div>
	<div class="block2-footer">
		<div class="block2-right">
			<div class="block2-left"></div>
		</div>
	</div>
</div>
</div>';
                }
                return $listingfees;
        }
        
        /**
        * Function to print any applicable service budget insertion fees during the posting of an auction.
        *
        * @param       integer       category id
        * 
        * @return      string        HTML representation of the insertion fee table
        */
        function print_budget_insertion_fees($cid = 0)
        {
                global $ilance, $phrase, $show, $ilconfig;
                $htmlinsertionfees = '';
                $show['budgetinsertionfees'] = 1;
                $sqlinsertions = $ilance->db->query("
                        SELECT budgetid, budgetgroup, title, fieldname, budgetfrom, budgetto, insertiongroup, sort
                        FROM " . DB_PREFIX . "budget
                        WHERE budgetgroup = '" . $ilance->db->escape_string($ilance->categories->budgetgroup($cid)) . "'
                        ORDER BY budgetfrom ASC
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sqlinsertions) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sqlinsertions))
                        {
                                if ($res['budgetto'] == '-1')
                                {
                                        $thefee = $this->calculate_insertion_fee_in_budget_group($res['insertiongroup']);
                                        if ($thefee == 0)
                                        {
                                                $thefee = '{_free}';
                                        }
                                        else
                                        {
                                                $thefee = $ilance->currency->format($this->calculate_insertion_fee_in_budget_group($res['insertiongroup']));
                                        }
                                        $htmlinsertionfees .= '<tr class="alt1"><td valign="top">' . stripslashes($res['title']) . ' (' . $ilance->currency->format($res['budgetfrom']) . ' {_or_more})</td><td valign="top"><strong>' . $thefee . '</strong></td></tr>';
                                }
                                else
                                {
                                        $thefee = $this->calculate_insertion_fee_in_budget_group($res['insertiongroup']);
                                        if ($thefee == 0)
                                        {
                                                $thefee = '{_free}';
                                        }
                                        else
                                        {
                                                $thefee = $ilance->currency->format($this->calculate_insertion_fee_in_budget_group($res['insertiongroup']));
                                        }
                                        $htmlinsertionfees .= '<tr class="alt1"><td valign="top">' . stripslashes($res['title']) . ' (' . $ilance->currency->format($res['budgetfrom']) . ' - ' . $ilance->currency->format($res['budgetto']) . ')</td><td valign="top"><strong>' . $thefee . '</strong></td></tr>';
                                }                                        
                        }
                        $htmlinsertionfees .= '<tr><td valign="top" colspan="2"><span class="gray">{_you_may_be_required_to_pay_this_fee_in_full_before_public_visibility}</span></td></tr>';
                }
                else 
                {
                        $show['budgetinsertionfees'] = 0;
                        $htmlinsertionfees .= '<tr><td valign="top" colspan="2"><span class="gray">{_no_insertion_fees_within_this_category}</span></td></tr>';	
                }
		if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'insexempt') == 'yes')
		{
			$htmlinsertionfees = '<tr><td valign="top" colspan="2"><span class="gray">{_you_are_exempt_from_insertion_fees}</span></td></tr>';
		}
                $listingfees = '<div class="block-wrapper">
<div class="block2">
	<div class="block2-top">
		<div class="block2-right">
			<div class="block2-left"></div>
		</div>
	</div>
	<div class="block2-header">{_budget} {_insertion_listing_fees}</div>
	<div class="block2-content" style="padding:0px">
	<table border="0" cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
	<tr class="alt2">
		<td valign="top"><strong>{_budget}</strong></td>
		<td valign="top" nowrap="nowrap"><strong>{_insertion_fee_amount}</strong></td>
	</tr>
	' . $htmlinsertionfees . '
	</table></div>
	<div class="block2-footer">
		<div class="block2-right">
				<div class="block2-left"></div>
		</div>
	</div>
	</div>
</div>';
                return $listingfees;
        }
        
        /**
        * Function to print final value fees formatted in a html table.  This function takes into consideration if the viewing user is exempt from final value fees.
        *
        * @param       integer        category id number
        * @param       string         category type (service/product)
        * @param       string         bid amount type
        *
        * @return      string         Returns category id's in comma separate values (ie: 1,3,4,6)
        */
        function print_final_value_fees($cid = 0, $cattype = 'service', $bidamounttype = '')
        {
                global $ilance, $phrase, $show, $ilconfig;
                
                $htmlfinalvaluefees = '';
                // first check if admin uses fixed fees in this category
                if ($ilance->categories->usefixedfees($cid) AND isset($bidamounttype) AND !empty($bidamounttype))
                {
                        // admin charges a fixed fee within this category to service providers
                        // let's determine if the bid amount type logic is configured
                        if ($bidamounttype != 'entire' AND $bidamounttype != 'item' AND $bidamounttype != 'lot')
                        {
                                // bid amount type passes accepted commission types
                                // let's output our final value fee table
                                if ($cattype == 'service')
                                {
                                        $htmlfinalvaluefees .= '<tr><td class="alt1">{_no_awarded_provider}</td><td class="alt1"><strong>{_no_fee}</strong></td></tr>';
                                }
                                else
                                {
                                        $htmlfinalvaluefees .= '<tr><td class="alt1">{_no_winning_bid}</td><td class="alt1"><strong>{_no_fee}</strong></td></tr>';
                                }
                                $htmlfinalvaluefees .= '<tr><td valign="top" nowrap="nowrap" class="alt1">' . $ilance->currency->format(0.01) . ' {_or_more}</td><td valign="top" class="alt1">' . $ilance->currency->format($ilance->categories->fixedfeeamount($cid)) . ' ({_fixed})</td></tr>';
                        }
                        else
                        {
                                $htmlfinalvaluefees .= '<tr><td valign="top" colspan="2" class="alt1"><span class="gray">{_no_final_value_fees_within_this_category}</span></td></tr>';	    
                        }
                }
                else
                {
                        $show['finalvaluefees'] = 1;
			$fvfgroupname = $ilance->categories->finalvaluegroup($cid);
			$forcefvfgroupid = $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], "{$cattype}fvfgroup");
			if ($forcefvfgroupid > 0)
			{
				$fvfgroupname = $ilance->db->fetch_field(DB_PREFIX . "finalvalue_groups", "groupid = '" . intval($forcefvfgroupid) . "'", "groupname");
			}
                        $sqlfinalvalues = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "finalvalue
                                WHERE groupname = '" . $ilance->db->escape_string($fvfgroupname) . "'
					AND state = '" . $ilance->db->escape_string($cattype) . "'
                                ORDER BY sort ASC
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sqlfinalvalues) > 0)
                        {
                                $tier = 1;
                                while ($rows = $ilance->db->fetch_array($sqlfinalvalues, DB_ASSOC))
                                {
                                        $from = $ilance->currency->format($rows['finalvalue_from']);
                                        $to =  ' &ndash; ' . $ilance->currency->format($rows['finalvalue_to']);
                                        if ($rows['amountfixed'] > 0)
                                        {
                                                $amountraw = $rows['amountfixed'];
                                                $amount = '<strong>' . $ilance->currency->format($rows['amountfixed']) . '</strong> {_fixed_price}';
                                        }
                                        else 
                                        {
                                                $amountraw = $rows['amountpercent'];
                                                if ($tier == 1)
                                                {
                                                        $amount = '<strong>' . $rows['amountpercent'] . '%</strong> {_of_the_closing_value}';
                                                }
                                                else 
                                                {
                                                        $amount = '<strong>' . $rows['amountpercent'] . '%</strong> {_of_the_remaining_balance_plus_tier_above}';
                                                }
                                        }
                                        if ($rows['finalvalue_to'] == '-1')
                                        {
                                                $to = '{_or_more}';
                                        }
                                        $htmlfinalvaluefees .= '<tr><td valign="top" nowrap="nowrap" class="alt1">' . $from . ' ' . $to . '</td><td valign="top" class="alt1">' . $amount . '</td></tr>';
                                        $tier++;
                                }
                                if ($cattype == 'service')
                                {
                                        $htmlfinalvaluefees .= '<tr><td><span class="gray">{_no_awarded_provider}</span></td><td><span class="gray"><strong>{_no_fee}</strong></span></td></tr>';
                                }
                                else 
                                {
                                        $htmlfinalvaluefees .= '<tr><td><span class="gray">{_no_winning_bid}</span></td><td><span class="gray"><strong>{_no_fee}</strong></span></td></tr>';	
                                }
                        }
                        else 
                        {
                                $show['finalvaluefees'] = 0;
                                $htmlfinalvaluefees .= '<tr><td valign="top" colspan="2"><span class="gray">{_no_final_value_fees_within_this_category}</span></td></tr>';	
                        }
                }
                // check for subscription fvf exemption
                if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'fvfexempt') == 'yes')
                {
                        $htmlfinalvaluefees = '<tr><td valign="top" colspan="2" class="alt1"><span class="gray">{_you_are_exempt_from_final_value_fees}</span></td></tr>';
                }
                $listingfees = '<div class="block-wrapper">
<div class="block">
<div class="block-top">
	<div class="block-right">
		<div class="block-left"></div>
	</div>
</div>
<div class="block-header">{_final_value_fees}</div>
<div class="block-content" style="padding:0px">
<table border="0" width="100%" cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" dir="' . $ilconfig['template_textdirection'] . '">';
		if ($cattype == 'service')
		{
			$listingfees .= '<tr>
	<td valign="top" class="alt2"><strong>{_awarded_price}</strong></td>
	<td valign="top" class="alt2"><strong>{_final_value_fee}</strong></td>
</tr>' . $htmlfinalvaluefees;
		}
		else 
		{
			$listingfees .= '<tr>
	<td valign="top" class="alt2"><strong>{_closing_price}</strong></td>
	<td valign="top" class="alt2"><strong>{_final_value_fee}</strong></td>
</tr>' . $htmlfinalvaluefees;	
		}
		$listingfees .= '</table>
	</div>
	<div class="block-footer">
		<div class="block-right">
			<div class="block-left"></div>
		</div>
	</div>
	</div>
</div>';
                return $listingfees;
        }
        
        /**
        * Function to process the custom auction questions
        *
        * @param       array         custom array
        * @param       integer       project id
        * @param       string        category mode (service or product)
        *
        * @return      null
        */
        function process_custom_questions($custom = array(), $projectid = 0, $type = '', $mode = 'insert')
        {
                global $ilance;
		$table1 = (($type == 'service') ? 'project_answers' : 'product_answers');
		$table2 = (($type == 'service') ? 'project_questions_choices' : 'product_questions_choices');
                if (isset($custom) AND !empty($custom) AND is_array($custom))
                {
                        foreach ($custom AS $questionid => $answerarray)
                        {
                                foreach ($answerarray AS $formname => $answer)
                                {
					if ($mode == 'update')
					{
						$ilance->db->query("
							DELETE FROM " . DB_PREFIX . $table1 . "
							WHERE questionid = '" . intval($questionid) . "'
							    AND project_id = '" . intval($projectid) . "'
						", 0, null, __FILE__, __LINE__);
					}
					if (is_array($answer))
					{
						foreach ($answer AS $key => $value)
						{
							$optionid = 0;
							$sql = $ilance->db->query("
								SELECT optionid, choice_" . $_SESSION['ilancedata']['user']['slng'] . " AS choice
								FROM " . DB_PREFIX . $table2 . "
								WHERE questionid = '" . intval($questionid) . "'
								ORDER BY sort ASC
							", 0, null, __FILE__, __LINE__);
							if ($ilance->db->num_rows($sql) > 0)
							{
								while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
								{
                                                                        if ($value == $res['choice'])        
									{
										$optionid = $res['optionid'];
										$value = $res['choice'];
										break 1;
									}
								}
								
							}
							$answer_text = handle_input_keywords(strip_tags($value));
							if (!empty($answer_text) OR $answer_text == '0')
							{
								$ilance->db->query("
									INSERT INTO " . DB_PREFIX . $table1 . "
									(answerid, questionid, project_id, answer, optionid, date, visible)
									VALUES(
									NULL,
									'" . intval($questionid) . "',
									'" . intval($projectid) . "',
									'" . $ilance->db->escape_string(trim($answer_text)) . "',
									'" . intval($optionid) . "',
									'" . DATETIME24H . "',
									'1')
								", 0, null, __FILE__, __LINE__);    
							}
						}
					}
					else 
					{
						$optionid = 0;
						$sql = $ilance->db->query("
							SELECT optionid, choice_" . $_SESSION['ilancedata']['user']['slng'] . " AS choice
							FROM " . DB_PREFIX . $table2 . "
							WHERE questionid = '" . intval($questionid) . "'
							ORDER BY sort ASC
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
							{
                                                                if ($answer == $res['choice'])        
								{
									$optionid = $res['optionid'];
									$answer = $res['choice'];
									break 1;
								}
							}
						}
						$answer = handle_input_keywords(strip_tags($answer));
						$sql2 = $ilance->db->query("
							SELECT answerid, questionid, answer, optionid, date
							FROM " . DB_PREFIX . $table1 . "
							WHERE questionid = '" . intval($questionid) . "'
								AND project_id = '" . intval($projectid) . "'
								AND visible = '1'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql2) > 0 AND (!empty($answer) OR $answer == '0'))
						{
							$ilance->db->query("
								UPDATE " . DB_PREFIX . $table1 . "
								SET answer = '" . $ilance->db->escape_string(trim($answer)) . "',
								optionid = '" . intval($optionid) . "'
								WHERE questionid = '" . intval($questionid) . "'
									AND project_id = '" . intval($projectid) . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
						}
						else
						{
							if ((!empty($answer) OR $answer == '0'))
							{
								$ilance->db->query("
									INSERT INTO " . DB_PREFIX . $table1 . "
									(answerid, questionid, project_id, answer, optionid, date, visible)
									VALUES(
									NULL,
									'" . intval($questionid) . "',
									'" . intval($projectid) . "',
									'" . $ilance->db->escape_string(trim($answer)) . "',
									'" . intval($optionid) . "',
									'" . DATETIME24H . "',
									'1')
								", 0, null, __FILE__, __LINE__);    
							}
						}
					}
                                }
                        }
                }
        }
        
        /**
        * Function to process the custom auction profile filter questions
        *
        * @param       array         custom array
        * @param       integer       project id
        * @param       string        category mode (service or product)
        *
        * @return      null
        */
        function process_custom_profile_questions($custom = array(), $projectid = 0, $userid = 0, $mode = '')
        {
                global $ilance;
                
                if (isset($custom) AND !empty($custom))
                {
                        foreach ($custom as $questionid => $answer)
                        {
                                $sql2 = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "profile_filter_auction_answers
                                        WHERE questionid = '" . intval($questionid) . "'
						AND project_id = '" . intval($projectid) . "'
						AND user_id = '" . intval($userid) . "'
                                ", 0, null, __FILE__, __LINE__);                    
                                if ($ilance->db->num_rows($sql2) > 0 AND !empty($answer))
                                {
                                        if (is_array($answer))
                                        {
                                                $answer = serialize($answer);
                                        }
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "profile_filter_auction_answers
                                                SET answer = '".$ilance->db->escape_string($answer) . "'
                                                WHERE questionid = '" . intval($questionid) . "'
							AND project_id = '" . intval($projectid) . "'
							AND user_id = '" . intval($userid) . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);                            
                                }
                                else
                                {
                                        if (!empty($answer))
                                        {
                                                if (is_array($answer))
                                                {
                                                        $answer = serialize($answer);
                                                }
                                                $ilance->db->query("
                                                        INSERT INTO " . DB_PREFIX . "profile_filter_auction_answers
                                                        (answerid, questionid, project_id, user_id, answer, date, visible)
                                                        VALUES(
                                                        NULL,
                                                        '" . intval($questionid) . "',
                                                        '" . intval($projectid) . "',
                                                        '" . intval($userid) . "',
                                                        '" . $ilance->db->escape_string($answer) . "',
                                                        '" . DATETIME24H . "',
                                                        '1')
                                                ", 0, null, __FILE__, __LINE__);    
                                        }
                                }
                        }
                }
        }
        
        /**
        * Function to obtain the email invitation email list line by line using \n as line seperator.
        *
        * @param       integer      auction id
        *
        * @return      string       line by line email list
        */
        function fetch_email_invites($projectid = 0)
        {
                global $ilance;
                $html = '';
                $sql = $ilance->db->query("
                        SELECT email, name
                        FROM " . DB_PREFIX . "project_invitations
                        WHERE project_id = '".intval($projectid)."'
                            AND email != ''
                            AND buyer_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                $html .= $res['email'] . LINEBREAK;
                        }
                }
                return $html;
        }
        
        /**
        * Function to obtain the username invitation list line by line using \n as line seperator.
        *
        * @param       integer      auction id
        *
        * @return      string       line by line username list
        */
        function fetch_member_invites($projectid = 0)
        {
                global $ilance;
                $html = '';
                $sql = $ilance->db->query("
                        SELECT seller_user_id
                        FROM " . DB_PREFIX . "project_invitations
                        WHERE project_id = '".intval($projectid)."'
                            AND buyer_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                            AND seller_user_id != '-1'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                $html .= fetch_user('username', $res['seller_user_id']) . LINEBREAK;
                        }
                }
                return $html;
        }
        
        /**
        * Function to print a year pulldown menu.
        *
        * @param       string        selected year value (optional)
        * @param       string        fieldname
        * @param       string        id name
        * @param       boolean       show the first option in pulldown as "year" phrase?
        * @param       boolean       show the next year in the pulldown (default false which only shows current year)
        *
        * @return      string        HTML representation of the year pulldown menu
        */
        function year($year = '', $fieldname = 'year', $idname = 'year', $showtitle = false, $allownextyear = false)
        {
                global $ilconfig;
                
                $html = '<select name="' . $fieldname . '" id="' . $idname . '" style="font-family: verdana">';
		if ($showtitle)
		{
			$html .= '<option value="">{_year}</option>';
		}
                $html .= '<option value="' . date('Y') . '">' . date('Y') . '</option>';
                if (date('m') == '11' OR date('m') == '12' OR $allownextyear)
                {
			if ($allownextyear)
			{
				$datee = (date('Y') + 1);
				$datey = date('Y');
				if ($datee == $year)
				{
					$html .= '<option value="' . $datee . '" selected="selected">' . $datee . '</option>';
				}
				else if ($datey == $year)
				{
					$html .= '<option value="' . $datey . '" selected="selected">' . $datey . '</option>';
				}
				else
				{
					$html .= '<option value="' . $datee . '">' . $datee . '</option>';
				}
			}
			else
			{
				$html .= '<option value="' . (date('Y') + 1) . '">' . (date('Y') + 1) . '</option>';
			}
                }
                $html .= '</select>';
                return $html;
        }
        
        /**
        * Function to print a month pulldown menu.
        *
        * @param       string        selected month value (optional)
        * @param       string        fieldname
        * @param       string        id name
        * @param       boolean       show the first option in pulldown as "month" phrase?
        *
        * @return      string        HTML representation of the month pulldown menu
        */
        function month($month = '', $fieldname = 'month', $idname = 'month', $showtitle = false)
        {
                global $ilconfig;
		$months = array('01' => '{_january}', '02' => '{_february}', '03' => '{_march}', '04' => '{_april}', '05' => '{_may}', '06' => '{_june}', '07' => '{_july}', '08' => '{_august}', '09' => '{_september}', '10' => '{_october}', '11' => '{_november}', '12' => '{_december}');
                $html = '<select name="' . $fieldname . '" id="' . $idname . '" style="font-family: verdana">';
                if ($showtitle)
		{
			$html .= '<option value="">{_month}</option>';
		}
                for ($i = 1; $i <= 12; $i++)
                {
                        if ($i < 10)
                        {
                                $i = "0$i";
                        }
                        if (isset($month) AND $month == $i)
                        {
                                $html .= '<option value="' . $i . '" selected="selected">' . $i . ' - ' . $months[$i] . '</option>';
                        }
                        else
                        {
                                $html .= '<option value="' . $i . '"';
                                if (empty($month) AND $i == date('m'))
                                {
                                        $html .= ' selected="selected"';
                                }
                                $html .= '>' . $i . ' - ' . $months[$i] . '</option>';				
                        }
                }
                $html .= '</select>';
                return $html;
        }
        
        /**
        * Function to print a day pulldown menu.
        *
        * @param       string        selected day value (optional)
        * @param       string        fieldname
        * @param       string        id name
        * @param       boolean       show the first option in pulldown as "day" phrase?
        * 
        * @return      string        HTML representation of the day pulldown menu
        */
        function day($day = '', $fieldname = 'day', $idname = 'day', $showtitle = false)
        {
                global $ilconfig;
                $html = '<select name="' . $fieldname . '" id="' . $idname . '" style="font-family: verdana">';
		if ($showtitle)
		{
			$html .= '<option value="">{_day}</option>';
		}
                for ($i = 1; $i <= 31; $i++)
                {
                        if ($i < 10)
                        {
                                $i = "0$i";
                        }
                        if (isset($day) AND $day == $i)
                        {
                                $html .= '<option value="' . $i . '" selected="selected">' . $i . '</option>';
                        }
                        else 
                        {
                                $html .= '<option value="' . $i . '"';
                                if (empty($day) AND $i == date('d'))
                                {
                                        $html .= ' selected="selected"';
                                }
                                $html .= '>' . $i . '</option>';
                        }
                }
                $html .= '</select>';
                return $html;
        }
        
        /**
        * Function to print an hour pulldown menu.
        *
        * @param       string        selected hour value (optional)
        * @param       string        fieldname
        * @param       string        id name
        * @param       boolean       show the first option in pulldown as "hour" phrase?
        * 
        * @return      string        HTML representation of the hour pulldown menu
        */
        function hour($hour = '', $fieldname = 'hour', $idname = 'hour', $showtitle = false)
        {
                global $ilconfig;
                $html = '<select name="' . $fieldname . '" id="' . $idname . '" style="font-family: verdana">';
		if ($showtitle)
		{
			$html .= '<option value="">{_hour}</option>';
		}
                for ($i = 0; $i <= 23; $i++)
                {
                        if ($i < 10)
                        {
                                $i = "0$i";
                        }                                    
                        if (isset($hour) AND $i == $hour)
                        {
                                $html .= '<option value="' . $i . '" selected="selected">' . $i . '</option>';
                        }
                        else 
                        {
                                $html .= '<option value="' . $i . '"';
                                if (empty($hour) AND $i == date('H'))
                                {
                                        $html .= ' selected="selected"';
                                }
                                $html .= '>' . $i . '</option>';
                        }
                }
                $html .= '</select>';
                return $html;
        }
        
        /**
        * Function to print a minute pulldown menu.
        *
        * @param       string        selected minute value (optional)
        * @param       string        fieldname
        * @param       string        id name
        * @param       boolean       show the first option in pulldown as "min" phrase?
        * 
        * @return      string        HTML representation of the minute pulldown menu
        */
        function min($min = '', $fieldname = 'min', $idname = 'min', $showtitle = false)
        {
                global $ilconfig;
                $html = '<select name="' . $fieldname . '" id="' . $idname . '" style="font-family: verdana">';
		if ($showtitle)
		{
			$html .= '<option value="">{_min}</option>';
		}
                for ($i = 0; $i <= 59; $i++)
                {
                        if ($i < 10)
                        {
                                $i = "0$i";
                        }                                    
                        if (isset($min) AND $i == $min)
                        {
                                $html .= '<option value="' . $i . '" selected="selected">' . $i . '</option>';
                        }
                        else 
                        {
                                $html .= '<option value="' . $i . '"';
                                if (empty($min) AND $i == date('i'))
                                {
                                        $html .= ' selected="selected"';
                                }
                                $html .= '>' . $i . '</option>';
                        }
                }
                $html .= '</select>';
                return $html;
        }
        
        /**
        * Function to print a seconds pulldown menu.
        *
        * @param       string        selected seconds value (optional)
        * @param       string        fieldname
        * @param       string        id name
        * @param       boolean       show the first option in pulldown as "sec" phrase?
        * 
        * @return      string        HTML representation of the seconds pulldown menu
        */
        function sec($sec = '', $fieldname = 'sec', $idname = 'sec', $showtitle = false)
        {
                global $ilconfig;
                $html = '<select name="' . $fieldname . '" id="' . $idname . '" style="font-family: verdana">';
		if ($showtitle)
		{
			$html .= '<option value="">{_sec}</option>';
		}
                for ($i = 0; $i <= 59; $i++)
                {
                        if ($i < 10)
                        {
                                $i = "0$i";
                        }
                        if (isset($sec) AND $i == $sec)
                        {
                                $html .= '<option value="' . $i . '" selected="selected">' . $i . '</option>';
                        }
                        else 
                        {
                                $html .= '<option value="' . $i . '"';
                                if (empty($sec) AND $i == date('s'))
                                {
                                        $html .= ' selected="selected"';
                                }
                                $html .= '>' . $i . '</option>';
                        }
                }
                $html .= '</select>';
                return $html;
        }
        
        /**
        * Function to print the selling format logic for a product auction.
        *
        * @return      string        HTML representation of the selling format selection menus
        */
        function print_selling_format_logic($disabled = false)
        {
                global $ilance, $ilconfig, $phrase, $tab, $headinclude, $onload, $show;
                if ($ilconfig['enablefixedpricetab'])
                {
			$headinclude .= '<script type="text/javascript">
<!--
function update_price_fixed()
{
        price = fetch_js_object("buynow_price").value;
        document.ilform.buynow_price_fixed.value = price;
}
function update_buynow_price_fixed()
{
        setTimeout("update_price_fixed()", 500);
}
function update_price()
{
        price = fetch_js_object("buynow_price_fixed").value;
        document.ilform.buynow_price.value = price;
}
function update_buynow_price()
{
        setTimeout("update_price()", 500);
}
function update_qty_fixed()
{
        qty = fetch_js_object("buynow_qty").value;
        document.ilform.buynow_qty_fixed.value = qty;
}
function update_shipping_next_cost()
{
        if (fetch_js_object("buynow_qty_fixed").value > 1 && fetch_js_object(\'filtered_auctiontype\').checked == false)
        {';	    
			for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
			{
				$headinclude .= '
		if (fetch_js_object("ship_service_css_cost_next"))
        	{
        		fetch_js_object("ship_service_css_cost_next").style.display=\'\';
        	}
        	if (fetch_js_object("next_ship_service_' . $i . '_cost"))
        	{
        		fetch_js_object("next_ship_service_' . $i . '_cost").style.display=\'\';
        	}';
			}	
			$headinclude .= '	
	}
	else
	{';	    
			for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
			{
				$headinclude .= '
		if (fetch_js_object("ship_service_css_cost_next"))
        	{
        		fetch_js_object("ship_service_css_cost_next").style.display=\'none\';
        	}
        	if (fetch_js_object("next_ship_service_' . $i . '_cost"))
        	{
        		fetch_js_object("next_ship_service_' . $i . '_cost").style.display=\'none\';
        	}';
			}
			$headinclude .= '	
        }
}
function update_buynow_qty_fixed()
{
        setTimeout("update_qty_fixed()", 500);
}
function update_qty()
{
        qty = fetch_js_object("buynow_qty_fixed").value;
        document.ilform.buynow_qty.value = qty;
}
function update_buynow_qty()
{
        setTimeout("update_qty()", 500);
}
//-->
</script>';
		}
		else 
		{
			$headinclude .= '
<script type="text/javascript">
<!--
function update_price_fixed()
{
        return(true);
}
function update_buynow_price_fixed()
{
        return(true);
}
function update_price()
{
        return(true);
}
function update_buynow_price()
{
        return(true);
}
function update_qty_fixed()
{
        return(true);
}
function update_shipping_next_cost()
{
	return(true);
}
function update_buynow_qty_fixed()
{
        return(true);
}
function update_qty()
{
        return(true);
}
function update_buynow_qty()
{
        return(true);
}
//-->
</script>';
		}
		$ilance->categories->build_array('product', $_SESSION['ilancedata']['user']['slng'], 0, true, '', '', 0, -1, 1, $ilance->GPC['cid']);
		$cb_auctiontype1 = 'checked="checked"';
		$cb_auctiontype2 = $cb_auctiontype3 = '';
		$tab = 0;
		if (isset($ilance->GPC['filtered_auctiontype']) AND $ilance->GPC['filtered_auctiontype'] == 'regular' AND $ilconfig['enableauctiontab'])
		{
			$cb_auctiontype1 = 'checked="checked"';
			$cb_auctiontype2 = $cb_auctiontype3 = '';
			$tab = 0;
		}
		else if (isset($ilance->GPC['filtered_auctiontype']) AND $ilance->GPC['filtered_auctiontype'] == 'fixed' AND $ilconfig['enablefixedpricetab'])
		{
			$cb_auctiontype1 = $cb_auctiontype3 = '';
			$cb_auctiontype2 = 'checked="checked"';
			$tab = 1;
		}
		else if (isset($ilance->GPC['filtered_auctiontype']) AND $ilance->GPC['filtered_auctiontype'] == 'classified' AND $ilconfig['enableclassifiedtab'])
		{
			$cb_auctiontype1 = $cb_auctiontype2 = '';
			$cb_auctiontype3 = 'checked="checked"';
			$tab = 2;
		}
		// #### REGULAR AUCTION ########################################
		// starting bid price
		$startprice = '';
		if (!empty($ilance->GPC['startprice']))
		{
			$startprice = sprintf("%01.2f", $ilance->GPC['startprice']);
		}
		// reserve price
		$show['usereserveprice'] = $ilance->categories->usereserveprice($ilance->GPC['cid']);
		$reserve_price = '';
		$reserve = 0;
		if (!empty($ilance->GPC['reserve_price']) AND $ilance->GPC['reserve_price'] > 0 AND $show['usereserveprice'])
		{
			$reserve_price = sprintf("%01.2f", $ilance->GPC['reserve_price']);
			$reserve = 1;
		}
		// reserve price fee
		$reservefee = 0;
		$reservefeeformatted = '{_free}';;
		if ($ilconfig['productupsell_reservepricecost'] > 0)
		{
			$reservefee = $ilconfig['productupsell_reservepricecost'];
			$reservefeeformatted = $ilance->currency->format($reservefee);
		}
		// buynow price
		$buynow_price = '';
		$buynow_price_fixed = '';
		if (!empty($ilance->GPC['buynow_price']) AND $ilance->GPC['buynow_price'] > 0)
		{
			$buynow_price = sprintf("%01.2f", $ilance->GPC['buynow_price']);
			$buynow_price_fixed = sprintf("%01.2f", $ilance->GPC['buynow_price']);
		}
		// buynow qty
		$buynow_qty = 1;
		$buynow_qty_fixed = 1;
		if (!empty($ilance->GPC['buynow_qty']))
		{
			$buynow_qty = intval($ilance->GPC['buynow_qty']);
			 $buynow_qty_fixed = intval($ilance->GPC['buynow_qty']);
		}
		// #### FIXED PRICED ONLY ######################################
		// buynow price
		if (!empty($ilance->GPC['buynow_price_fixed']) AND $ilance->GPC['buynow_price_fixed'] > 0)
		{
			$buynow_price_fixed = sprintf("%01.2f", $ilance->GPC['buynow_price_fixed']);
		}
		// buynow qty
		if (!empty($ilance->GPC['buynow_qty_fixed']))
		{
			$buynow_qty_fixed = intval($ilance->GPC['buynow_qty_fixed']);
		}
		// buy now fee
		$buynowfee = 0;
		$buynowfeeformatted = '{_free}';
		if ($ilconfig['productupsell_buynowcost'] > 0)
		{
		    $buynowfee = $ilconfig['productupsell_buynowcost'];
		    $buynowfeeformatted = $ilance->currency->format($buynowfee);
		}
		// classified price
		$classified_price = '';
		if (!empty($ilance->GPC['classified_price']) AND $ilance->GPC['classified_price'] > 0)
		{
			$classified_price = sprintf("%01.2f", $ilance->GPC['classified_price']);
		}
		// classified phone
		$classified_phone = $_SESSION['ilancedata']['user']['phone'];
		if (!empty($ilance->GPC['classified_phone']))
		{
			$classified_phone = handle_input_keywords($ilance->GPC['classified_phone']);
		}
		// classified ad fee
		$classifiedfee = 0;
		$classifiedformatted = '{_free}';
		if ($ilconfig['productupsell_classifiedcost'] > 0)
		{
		    $classifiedfee = $ilconfig['productupsell_classifiedcost'];
		    $classifiedfeeformatted = $ilance->currency->format($classifiedfee);
		}
		// buy now quantity in LOT format only
		$cb_qty_lot = '';
		if (!empty($ilance->GPC['buynow_qty_lot']))
		{
			$cb_qty_lot = 'checked="checked"';
		}
		// determine what the admin has set for selling logic formatting
		if ($ilconfig['enableauctiontab'] == 0 AND $ilconfig['enablefixedpricetab'] == 0)
		{
			// some guy in the admin disabled everything! re-enable it!
			$ilconfig['enableauctiontab'] = 1;
			$ilconfig['enablefixedpricetab'] = 1;
		}
		else if ($ilconfig['enableauctiontab'] == 0 AND $ilconfig['enablefixedpricetab'])
		{
			$onload .= 'fetch_js_object(\'filtered_auctiontype\').checked = true; ';
		}
		$html = '<div class="tab-pane" id="sellingformat">';
		$buynow_qty_lot_selected_regular = $items_in_lot_regular = $buynow_qty_lot_selected_fixed = $items_in_lot_fixed = '';
		$buynow_qty_lot_selected = (isset($ilance->GPC['buynow_qty_lot']) AND $ilance->GPC['buynow_qty_lot'] == '1') ? 'selected="selected"' : '';
		$items_in_lot = (isset($ilance->GPC['items_in_lot']) AND !empty($ilance->GPC['items_in_lot'])) ? $ilance->GPC['items_in_lot'] : '';
		if (isset($ilance->GPC['filtered_auctiontype']) AND $ilance->GPC['filtered_auctiontype'] == 'regular')
		{
			$buynow_qty_lot_selected_regular = $buynow_qty_lot_selected;
			$items_in_lot_regular = $items_in_lot;
		}
		else 
		{
			$buynow_qty_lot_selected_fixed = $buynow_qty_lot_selected;
			$items_in_lot_fixed = $items_in_lot;
		}
		
		($apihook = $ilance->api('print_selling_format_logic_pretab_start')) ? eval($apihook) : false;
		
		if (isset($ilconfig['enableauctiontab']) AND $ilconfig['enableauctiontab'])
		{
			$html .= '<div class="tab-page">
<h2 class="tab" id="0"><a href="javascript:void(0)" onclick="javascript:document.ilform.filtered_auctiontype[0].checked=true;toggle_show(\'showeventtype\');update_shipping_next_cost();fetch_js_object(\'public_title\').innerHTML=\'{_public_event}\';fetch_js_object(\'public_help\').innerHTML=\'{_publically_available_auction}\';fetch_js_object(\'realtime_title\').innerHTML=\'{_invitation_event_realtime}\';fetch_js_object(\'realtime_help\').innerHTML=\'{_invite_vendors_to_place_bids_on_your}\';">{_auction}</a></h2>
<div><strong><input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="radio" name="filtered_auctiontype" id="filtered_auctiontype" value="regular" ' . $cb_auctiontype1 . ' />&nbsp;<label for="filtered_auctiontype">{_auction}</label></strong> : <span class="gray">{_this_format_allow_bidding_to_take_place_including_the_ablility}</span></div>
<table border="0" cellpadding="2" cellspacing="0" dir="' . $ilconfig['template_textdirection'] . '">
<tr>
	<td width="52%" valign="top">
		<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="10" dir="' . $ilconfig['template_textdirection'] . '">
		<tr>
			<td width="1%" nowrap="nowrap">{_starting_price}</td>
			<td width="1%">&nbsp;</td> 
			<td width="1%" nowrap="nowrap"><span id="startprice_currency">' . $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_left'] . '</span>&nbsp;<input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="text" id="startprice" name="startprice" value="' . $startprice . '" onblur="calculate_insertionfees()" onkeypress="return noenter()" onclick="fetch_js_object(\'filtered_auctiontype\').checked = true;toggle_show(\'showeventtype\');fetch_js_object(\'public_title\').innerHTML=\'{_public_event}\';fetch_js_object(\'public_help\').innerHTML=\'{_publically_available_auction}\';fetch_js_object(\'realtime_title\').innerHTML=\'{_invitation_event_realtime}\';fetch_js_object(\'realtime_help\').innerHTML=\'{_invite_vendors_to_place_bids_on_your}\';" style="width:60px" class="input" /> <span id="startprice_currency_right">' . $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_right'] . '</span>&nbsp;&nbsp;<a href="javascript:void(0)" onmouseover="Tip(phrase[\'_the_starting_price_is_the_amount_you_set_the_starting_bid_amount_in_your_auction_event_bidders_will_need_to_begin_the_bid_amounts\'], BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a></td>
		</tr>
		<tr>
			<td width="1%">{_quantity}</td>
			<td width="1%" align="right"nowrap="nowrap">&nbsp;</td>
			<td width="1%" nowrap="nowrap" colspan="2">
			1 <strong>x</strong>&nbsp;<select id="buynow_qty_lot_regular" name="buynow_qty_lot_regular" ' . ($disabled ? 'disabled="disabled"' : '') . ' onChange="javascript:lot_field()" class="select-75">
				<option value="0" ' . (empty($buynow_qty_lot_selected_regular) ? 'selected="selected"' : '') . '>{_item}</option>
				<option value="1" ' . $buynow_qty_lot_selected_regular . '>{_lot}</option>
			</select>&nbsp;&nbsp;<span id="lot_qty_regular" ' . (!empty($buynow_qty_lot_selected_regular) ? '' : 'style="display:none"') . '><span class="black">{_items_in_this_lot_lower}</span>&nbsp;<input type="text" id="items_in_lot_regular" name="items_in_lot_regular" ' . ($disabled ? 'disabled="disabled"' : '') . ' value="' . $items_in_lot_regular . '" size="3" class="input" /></span>&nbsp;&nbsp;<a href="javascript:void(0)" onmouseover="Tip(\'<div>\' + phrase[\'_selling_lots_via_auction_by_default\'] + \'</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a>
			</td>
		</tr>
		</table>
		
	</td>
	<td width="48%" valign="top">
		<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="10" dir="' . $ilconfig['template_textdirection'] . '">
		<tr>
			<td width="1%" nowrap="nowrap">{_buy_now_price} &nbsp;&nbsp;<span class="smaller gray">(' . $buynowfeeformatted . ')</span></td> 
			<td width="1%">&nbsp;</td>
			<td width="1%" nowrap="nowrap" align="left"><span id="buynowprice_currency">' . $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_left'] . '</span>&nbsp;<input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="text" id="buynow_price" name="buynow_price" onblur="calculate_insertionfees()" onkeypress="return update_buynow_price_fixed()" value="' . $buynow_price . '" onclick="fetch_js_object(\'filtered_auctiontype\').checked = true;toggle_show(\'showeventtype\');fetch_js_object(\'public_title\').innerHTML=\'{_public_event}\';fetch_js_object(\'public_help\').innerHTML=\'{_publically_available_auction}\';fetch_js_object(\'realtime_title\').innerHTML=\'{_invitation_event_realtime}\';fetch_js_object(\'realtime_help\').innerHTML=\'{_invite_vendors_to_place_bids_on_your}\';" style="width:60px" class="input" /> <span id="buynowprice_currency_right">' . $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_right'] . '</span>&nbsp;&nbsp;<a href="javascript:void(0)" onmouseover="Tip(phrase[\'_buy_now_price_optional_if_you_would_like_to_offer_buyers_the_chance\'], BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a></td>
		</tr>';
		
			if ($show['usereserveprice'])
			{
				$html .= '<tr>
			<td align="right" width="1%" nowrap="nowrap">{_reserve_price} &nbsp;&nbsp;<span class="smaller gray">(' . $reservefeeformatted . ')</span></td>
			<td align="right">&nbsp;</td> 
			<td nowrap="nowrap"><span id="reserveprice_currency">' . $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_left'] . '</span>&nbsp;<input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="text" id="reserve_price" name="reserve_price" value="' . $reserve_price . '" onblur="calculate_insertionfees()" onkeypress="return noenter()" style="width:60px" onclick="fetch_js_object(\'filtered_auctiontype\').checked = true;toggle_show(\'showeventtype\');fetch_js_object(\'public_title\').innerHTML=\'{_public_event}\';fetch_js_object(\'public_help\').innerHTML=\'{_publically_available_auction}\';fetch_js_object(\'realtime_title\').innerHTML=\'{_invitation_event_realtime}\';fetch_js_object(\'realtime_help\').innerHTML=\'{_invite_vendors_to_place_bids_on_your}\';" class="input" /> <span id="reserveprice_currency_right">' . $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_right'] . '</span>&nbsp;&nbsp;<a href="javascript:void(0)" onmouseover="Tip(phrase[\'_a_reserve_price_is_a_hidden_amount_you_can_set_which_no_bidder_can_see\'], BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a></td>
		</tr>';
			}
			else
			{
				$html .= '<tr>
			<td align="right" width="1%" nowrap="nowrap"></td>
			<td align="right"></td> 
			<td nowrap="nowrap"><input type="hidden" id="reserve_price" name="reserve_price" value="" /></td>
		</tr>';        
			}
			$html .= '</table>       
	</td>
</tr>
</table>                                
</div>';
		}
		if (isset($ilconfig['enablefixedpricetab']) AND $ilconfig['enablefixedpricetab'])
		{
			($apihook = $ilance->api('print_selling_format_logic_fixed_buynow_end_disable')) ? eval($apihook) : false;
			$html .= '<div class="tab-page">
<h2 class="tab" id="1"><a href="javascript:void(0)" onclick="javascript:document.ilform.filtered_auctiontype[1].checked=true;toggle_show(\'showeventtype\');update_shipping_next_cost();fetch_js_object(\'public_title\').innerHTML=\'{_immediate_buying}\';fetch_js_object(\'public_help\').innerHTML=\'{_listing_open_for_buying_immediately}\';fetch_js_object(\'realtime_title\').innerHTML=\'{_scheduled_buying}\';fetch_js_object(\'realtime_help\').innerHTML=\'{_listing_to_start_buying_in_future}\';">{_fixed_price}</a></h2>
<table border="0" cellpadding="2" cellspacing="0" dir="' . $ilconfig['template_textdirection'] . '">
<tr>
	<td valign="top">
		<div><strong><input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="radio" name="filtered_auctiontype" id="filtered_auctiontype0" value="fixed" ' . $cb_auctiontype2 . ' />&nbsp;<label for="filtered_auctiontype0">{_fixed_price}</label></strong> : <span class="gray">{_this_format_does_not_allow_bidding_to_take_place}</span></div>
		<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
		<tr>
			<td width="25%" nowrap="nowrap">{_buy_now_price}&nbsp;&nbsp;&nbsp;<span class="smaller gray">(' . $buynowfeeformatted . ')</span></td> 
			<td width="75%" nowrap="nowrap" align="left"><span id="buynowpricefixed_currency">' . $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_left'] . '</span>&nbsp;<input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="text" id="buynow_price_fixed" name="buynow_price_fixed" onblur="calculate_insertionfees()" onkeypress="return update_buynow_price()" value="' . $buynow_price_fixed . '" onclick="fetch_js_object(\'filtered_auctiontype0\').checked = true;toggle_show(\'showeventtype\');fetch_js_object(\'public_title\').innerHTML=\'{_immediate_buying}\';fetch_js_object(\'public_help\').innerHTML=\'{_listing_open_for_buying_immediately}\';fetch_js_object(\'realtime_title\').innerHTML=\'{_scheduled_buying}\';fetch_js_object(\'realtime_help\').innerHTML=\'{_listing_to_start_buying_in_future}\';" style="width:60px" class="input" /> <span id="buynowpricefixed_currency_right">' . $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_right'] . '</span>&nbsp;&nbsp;<a href="javascript:void(0)" onmouseover="Tip(phrase[\'_buy_now_price_refers_to_a_fixed_cost_for_items_you_are_selling_for_example\'], BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a></td>
			<td width="25%">{_quantity}</td>
			<td width="75%" nowrap="nowrap" colspan="2"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="text" id="buynow_qty_fixed" name="buynow_qty_fixed" value="' . $buynow_qty_fixed . '" onchange="return update_shipping_next_cost();" onkeypress="return update_buynow_qty()" onclick="fetch_js_object(\'filtered_auctiontype0\').checked=true;toggle_show(\'showeventtype\');fetch_js_object(\'public_title\').innerHTML=\'{_immediate_buying}\';fetch_js_object(\'public_help\').innerHTML=\'{_listing_open_for_buying_immediately}\';fetch_js_object(\'realtime_title\').innerHTML=\'{_scheduled_buying}\';fetch_js_object(\'realtime_help\').innerHTML=\'{_listing_to_start_buying_in_future}\';" size="3" class="input" /> &nbsp;<strong>x</strong>&nbsp;
			<select id="buynow_qty_lot_fixed" name="buynow_qty_lot_fixed" ' . ($disabled ? 'disabled="disabled"' : '') . ' onChange="javascript: lot_field();" class="select-75">
				<option value="0" ' . (empty($buynow_qty_lot_selected_fixed) ? 'selected="selected"' : '') . '>{_items}</option>
				<option value="1" ' . $buynow_qty_lot_selected_fixed . '>{_lots}</option>
			</select>
			<span id="lot_qty_fixed" ' . (!empty($buynow_qty_lot_selected_fixed) ? '' : 'style="display:none"') . '>&nbsp;<span class="black" >{_items_in_each_lot}</span>&nbsp;<input type="text" id="items_in_lot_fixed" name="items_in_lot_fixed" ' . ($disabled ? 'disabled="disabled"' : '') . ' value="' . $items_in_lot_fixed . '"  size="3" class="input" /></span>&nbsp;<a href="javascript:void(0)" onmouseover="Tip(\'<div>\' + phrase[\'_if_you_are_selling_a_lot_with_500_items\'] + \'</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a></span>
			</td>
		</tr>';
		
		($apihook = $ilance->api('print_selling_format_logic_fixed_buynow_end')) ? eval($apihook) : false;
		
		$html .= '</table>
	</td>
</tr>
</table>
</div>';
		}
		if (isset($ilconfig['enableclassifiedtab']) AND $ilconfig['enableclassifiedtab'])
		{
			$html .= '<div class="tab-page">
<h2 class="tab" id="2"><a href="javascript:void(0)" onclick="javascript:document.ilform.filtered_auctiontype[2].checked=true;toggle_hide(\'showeventtype\')">{_classified_ad}</a></h2>
<table border="0" cellpadding="2" cellspacing="0" dir="' . $ilconfig['template_textdirection'] . '">
<tr>
	<td valign="top">
		<div><strong><input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="radio" name="filtered_auctiontype" id="filtered_auctiontype1" value="classified" ' . $cb_auctiontype3 . ' />&nbsp;<label for="filtered_auctiontype1">{_classified_ad}</label></strong> : <span class="gray">{_this_format_classified_ad}</span></div>
		<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
		<tr>
			<td width="25%" nowrap="nowrap">{_advertised_price}&nbsp;&nbsp;&nbsp;<span class="smaller gray">(' . $classifiedformatted . ')</span></td> 
			<td width="75%" nowrap="nowrap" align="left"><span id="classifiedprice_currency">' . $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_left'] . '</span>&nbsp;<input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="text" id="classified_price" name="classified_price" value="' . $classified_price . '" onclick="fetch_js_object(\'filtered_auctiontype1\').checked = true;toggle_hide(\'showeventtype\')" style="width:60px; font-family: verdana" /> <span id="classifiedprice_currency_right">' . $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_right'] . '</span>&nbsp;&nbsp;<a href="javascript:void(0)" onmouseover="Tip(phrase[\'_advertised_price_will_be_amount_buyers_see\'], BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a></td>
		</tr>';
		
		($apihook = $ilance->api('print_selling_format_logic_classified_end')) ? eval($apihook) : false;
		
		$html .= '<tr>
			<td width="25%">{_phone_number}</td>
			<td width="75%" nowrap="nowrap" colspan="2"><input ' . ($disabled ? 'disabled="disabled"' : '') . ' type="text" id="classified_phone" name="classified_phone" value="' . $classified_phone . '" onclick="fetch_js_object(\'filtered_auctiontype1\').checked=true;toggle_hide(\'showeventtype\')" style="width:150px; font-family: verdana" />&nbsp;&nbsp;<a href="javascript:void(0)" onmouseover="Tip(phrase[\'_this_phone_number_will_be_displayed_to_buyers\'], BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a></td>
		</tr>
		</table>
	</td>
</tr>
</table>
</div>';
		}
		$onload .= 'lot_field();update_shipping_next_cost();';
		$headinclude .= '<script type="text/javascript">
<!--	
function lot_field()
{
	if (fetch_js_object(\'buynow_qty_lot_regular\'))
	{
		if (fetch_js_object(\'buynow_qty_lot_regular\').value == \'1\') 
		{
			toggle_show(\'lot_qty_regular\');
		}
		else
		{
			toggle_hide(\'lot_qty_regular\');
		}
	}
	if (fetch_js_object(\'buynow_qty_lot_fixed\').value == \'1\') 
	{
		toggle_show(\'lot_qty_fixed\');
		';	    
		for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
		{
			$headinclude .= '
			if (fetch_js_object(\'ship_service_css_cost_next_type_lot\'))
			{
				fetch_js_object(\'ship_service_css_cost_next_type_lot\').style.display=\'\';
			}
			if (fetch_js_object(\'ship_service_css_cost_next_type_item\'))
			{
				fetch_js_object(\'ship_service_css_cost_next_type_item\').style.display=\'none\';
			}
		';
		}
		$headinclude .= '
	}
	else
	{
		toggle_hide(\'lot_qty_fixed\');
		';	    
		for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
		{
			$headinclude .= '
			if (fetch_js_object(\'ship_service_css_cost_next_type_lot\'))
			{
				fetch_js_object(\'ship_service_css_cost_next_type_lot\').style.display=\'none\';
			}
			if (fetch_js_object(\'ship_service_css_cost_next_type_item\'))
			{
				fetch_js_object(\'ship_service_css_cost_next_type_item\').style.display=\'\';
			}
		';
		}
		$headinclude .= '
	}
}
//-->
</script>';
                        
                ($apihook = $ilance->api('print_selling_format_logic_tab_end')) ? eval($apihook) : false;
                        
                $html .= '</div>';
                
                ($apihook = $ilance->api('print_selling_format_logic_end')) ? eval($apihook) : false;
                
                return $html;
        }
        
        /**
        * Function to print the shipping logic within a product auction.
        *
        * @param       boolean        disabled (default false)       
        *
        * @return      nothing
        */
        function print_shipping_logic($disabled = false)
        {
                global $ilance, $ilconfig, $phrase, $onload, $project_id, $cid, $ilpage, $attachment_style, $currencysymbol, $headinclude, $ilregions;
		$headinclude .= '<script type="text/javascript">
<!--	
function hide_shipp(i)
{
	if (jQuery(\'#freeshipping_\' + i).is(\':checked\'))
	{
		jQuery(\'#ship_service_\' + i + \'_css_costsymbol\').hide();
		jQuery(\'#ship_fee_\' + i).hide();
		jQuery(\'#ship_fee_\' + i).parent().append(\'<div id="ship_fee_\' + i + \'_empty">-</div>\');
		jQuery(\'#next_ship_service_\' + i + \'_cost\').hide();
		if (jQuery(\'#buynow_qty_fixed\').val() > 1 && jQuery(\'#filtered_auctiontype\').is(\':checked\') == false)
		{
			jQuery(\'#next_ship_service_\' + i + \'_cost\').parent().append(\'<div id="next_ship_service_\' + i + \'_empty">-</div>\');
		}
	}
	else
	{
		jQuery(\'#ship_service_\' + i + \'_css_costsymbol\').show();
		jQuery(\'#ship_fee_\' + i).show();
		jQuery(\'#ship_fee_\' + i + \'_empty\').remove();
		if (jQuery(\'#buynow_qty_fixed\').val() > 1 && jQuery(\'#filtered_auctiontype\').is(\':checked\') == false)
		{
			jQuery(\'#next_ship_service_\' + i + \'_cost\').show();
			jQuery(\'#next_ship_service_\' + i + \'_empty\').remove();
		}
	}
}
//-->
</script>';
		// #### updating listing #######################################
		$shippercount = 1;
		if (empty($ilance->GPC['project_id']) AND !empty($ilance->GPC['id']))
		{
			$ilance->GPC['project_id'] = intval($ilance->GPC['id']);
		}
		if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'product-management' AND isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0)
		{
			$shippercount = $ilance->shipping->fetch_shipping_services_count($ilance->GPC['project_id']);
			if (isset($ilance->GPC['ship_method']) AND !empty($ilance->GPC['ship_method']))
			{
				switch ($ilance->GPC['ship_method'])
				{
					case 'flatrate':
					{
						$onload .= 'fetch_js_object(\'ship_method\').options[fetch_js_object(\'ship_method\').selectedIndex = 0];toggle_show(\'showshipping\');toggle_show(\'ship_method_service_options\');toggle_hide(\'ship_method_calculated_options\');toggle_show(\'handlingfeeheader\');toggle_show(\'handlingfeerow\');';
						for ($i = 1; $i <= $shippercount; $i++)
						{
							// #### domestic ######
							if (isset($ilance->GPC['ship_options_' . $i]) AND $ilance->GPC['ship_options_' . $i] == 'domestic' AND isset($ilance->GPC['ship_service_' . $i]))
							{
								$onload .= 'fetch_js_object(\'ship_options_' . $i . '\').options[fetch_js_object(\'ship_options_' . $i . '\').selectedIndex = 1];';
								$onload .= 'toggle_hide(\'ship_options_custom_regionnav_' . $i . '\');';
								$onload .= 'print_shipping_services(\'ship_service_' . $i . '_container\', \'ship_service_' . $i . '\', true, false, ' . $ilance->GPC['ship_service_' . $i] . ($disabled ? ', true' : ', false') . ', 1, \'ship_packagetype_' . $i . '_container\', \'ship_packagetype_' . $i . '\', \'' . $ilance->GPC['ship_packagetype_' . $i] . '\', \'ship_pickuptype_' . $i . '_container\', \'ship_pickuptype_' . $i . '\', \'' . $ilance->GPC['ship_pickuptype_' . $i] . '\', \'' . $ilance->GPC['ship_method'] . '\');';
								$onload .= 'toggle_hide(\'ship_service_css_packagetype\');toggle_hide(\'ship_service_css_pickuptype\');toggle_hide(\'ship_packagetype_' . $i . '\');toggle_hide(\'ship_pickuptype_' . $i . '\');';
							}
							// #### worldwide ######
							if (isset($ilance->GPC['ship_options_' . $i]) AND $ilance->GPC['ship_options_' . $i] == 'worldwide' AND isset($ilance->GPC['ship_service_' . $i]))
							{
								$onload .= 'fetch_js_object(\'ship_options_' . $i . '\').options[fetch_js_object(\'ship_options_' . $i . '\').selectedIndex = 2];';
								$onload .= 'toggle_hide(\'ship_options_custom_regionnav_' . $i . '\');';
								$onload .= 'print_shipping_services(\'ship_service_' . $i . '_container\', \'ship_service_' . $i . '\', false, true, ' . $ilance->GPC['ship_service_' . $i] . ($disabled ? ', true' : ', false') . ', 1, \'ship_packagetype_' . $i . '_container\', \'ship_packagetype_' . $i . '\', \'' . $ilance->GPC['ship_packagetype_' . $i] . '\', \'ship_pickuptype_' . $i . '_container\', \'ship_pickuptype_' . $i . '\', \'' . $ilance->GPC['ship_pickuptype_' . $i] . '\', \'' . $ilance->GPC['ship_method'] . '\');';
								$onload .= 'toggle_hide(\'ship_service_css_packagetype\');toggle_hide(\'ship_service_css_pickuptype\');toggle_hide(\'ship_packagetype_' . $i . '\');toggle_hide(\'ship_pickuptype_' . $i . '\');';
							}
							// #### custom location
							if (isset($ilance->GPC['ship_options_' . $i]) AND $ilance->GPC['ship_options_' . $i] == 'custom' AND isset($ilance->GPC['ship_service_' . $i]))
							{
								$onload .= 'fetch_js_object(\'ship_options_' . $i . '\').options[fetch_js_object(\'ship_options_' . $i . '\').selectedIndex = 3];';
								$onload .= 'toggle_show(\'ship_options_custom_regionnav_' . $i . '\');';
								$onload .= 'print_shipping_services(\'ship_service_' . $i . '_container\', \'ship_service_' . $i . '\', false, true, ' . $ilance->GPC['ship_service_' . $i] . ($disabled ? ', true' : ', false') . ', 1, \'ship_packagetype_' . $i . '_container\', \'ship_packagetype_' . $i . '\', \'' . $ilance->GPC['ship_packagetype_' . $i] . '\', \'ship_pickuptype_' . $i . '_container\', \'ship_pickuptype_' . $i . '\', \'' . $ilance->GPC['ship_pickuptype_' . $i] . '\', \'' . $ilance->GPC['ship_method'] . '\');';
								$onload .= 'toggle_hide(\'ship_service_css_packagetype\');toggle_hide(\'ship_service_css_pickuptype\');toggle_hide(\'ship_packagetype_' . $i . '\');toggle_hide(\'ship_pickuptype_' . $i . '\');';
								$regions = $ilance->shipping->fetch_listing_shipping_regions($ilance->GPC['project_id'], $i);
								if (is_array($regions) AND count($regions) > 0)
								{
									foreach ($regions AS $row => $regionarray)
									{
										if ($row == $i)
										{
											foreach ($regionarray AS $region)
											{
												$region = strtolower(str_replace(' ', '_', $region));
												switch ($region)
												{
													case 'north_america':
													{
														$onload .= 'fetch_js_object(\'ship_options_custom_region_' . $i . '_1\').checked=true;';
														break;	
													}
													case 'south_america':
													{
														$onload .= 'fetch_js_object(\'ship_options_custom_region_' . $i . '_4\').checked=true;';
														break;	
													}
													case 'oceania':
													{
														$onload .= 'fetch_js_object(\'ship_options_custom_region_' . $i . '_6\').checked=true;';
														break;	
													}
													case 'europe':
													{
														$onload .= 'fetch_js_object(\'ship_options_custom_region_' . $i . '_2\').checked=true;';
														break;	
													}
													case 'asia':
													{
														$onload .= 'fetch_js_object(\'ship_options_custom_region_' . $i . '_5\').checked=true;';
														break;	
													}
													case 'antarctica':
													{
														$onload .= 'fetch_js_object(\'ship_options_custom_region_' . $i . '_7\').checked=true;';
														break;	
													}
													case 'africa':
													{
														$onload .= 'fetch_js_object(\'ship_options_custom_region_' . $i . '_3\').checked=true;';
														break;	
													}
												}
											}
										}
									}
								}
								unset($regions);
							}
							if (isset($ilance->GPC['ship_fee_' . $i]) AND $ilance->GPC['ship_fee_' . $i] > 0)
							{
								$onload .= 'fetch_js_object(\'ship_fee_' . $i . '\').value = \'' . $ilance->GPC['ship_fee_' . $i] . '\'; ';
							}
							if (isset($ilance->GPC['ship_fee_next_' . $i]) AND $ilance->GPC['ship_fee_next_' . $i] > 0)
							{
								$onload .= 'fetch_js_object(\'ship_fee_next_' . $i . '\').value = \'' . $ilance->GPC['ship_fee_next_' . $i] . '\'; ';
							}
							if (isset($ilance->GPC['freeshipping_' . $i]) AND $ilance->GPC['freeshipping_' . $i] == '1')
							{
								$onload .= 'fetch_js_object(\'freeshipping_' . $i . '\').checked = true; $(\'#ship_service_' . $i . '_css_costsymbol\').hide(); $(\'#ship_fee_' . $i . '\').hide(); $(\'#next_ship_service_' . $i . '_cost\').hide();';
							}
							$onload .= ($disabled ? '' : 'fetch_js_object(\'ship_fee_' . $i . '\').disabled = false;');
							$onload .= ($disabled ? '' : 'fetch_js_object(\'freeshipping_' . $i . '\').disabled = false;'); 
							$onload .= (($disabled AND $i > 1) ? 'fetch_js_object(\'remove_link_' . $i . '\').style.display=\'none\';' : ''); 
							$onload .= 'toggle_show(\'ship_method_service_options_' . $i . '\');fetch_js_object(\'ship_service_css_cost\').className=\'black\';fetch_js_object(\'ship_service_' . $i . '_css_costsymbol\').className=\'black\';fetch_js_object(\'next_ship_service_' . $i . '_css_costsymbol\').className=\'black\';fetch_js_object(\'ship_service_css_freeshipping\').className=\'black\';fetch_js_object(\'ship_service_' . $i . '_css_freeshippinganswer\').className=\'black\'; ';
						}
						break;
					}
					case 'calculated':
					{
						$onload .= ($ilconfig['shippingapi'])
							? 'fetch_js_object(\'ship_method\').options[fetch_js_object(\'ship_method\').selectedIndex=1];'
							: 'fetch_js_object(\'ship_method\').options[fetch_js_object(\'ship_method\').selectedIndex=0];';
						$onload .= 'toggle_show(\'showshipping\');toggle_show(\'ship_method_calculated_options\');toggle_show(\'handlingfeeheader\');toggle_show(\'handlingfeerow\');';
						for ($i = 1; $i <= $shippercount; $i++)
						{
							// #### domestic #######
							if (isset($ilance->GPC['ship_options_' . $i]) AND $ilance->GPC['ship_options_' . $i] == 'domestic' AND isset($ilance->GPC['ship_service_' . $i]))
							{
								$onload .= 'fetch_js_object(\'ship_options_' . $i . '\').options[fetch_js_object(\'ship_options_' . $i . '\').selectedIndex = 1];';
								$onload .= 'toggle_hide(\'ship_options_custom_regionnav_' . $i . '\');';
								$onload .= 'print_shipping_services(\'ship_service_' . $i . '_container\', \'ship_service_' . $i . '\', true, true, ' . $ilance->GPC['ship_service_' . $i] . ($disabled ? ', true' : ', false') . ', 1, \'ship_packagetype_' . $i . '_container\', \'ship_packagetype_' . $i . '\', \'' . $ilance->GPC['ship_packagetype_' . $i] . '\', \'ship_pickuptype_' . $i . '_container\', \'ship_pickuptype_' . $i . '\', \'' . $ilance->GPC['ship_pickuptype_' . $i] . '\', \'' . $ilance->GPC['ship_method'] . '\');';
								$onload .= 'toggle_show(\'ship_packagetype_' . $i . '\');toggle_show(\'ship_pickuptype_' . $i . '\');';
								$onload .= 'toggle_show(\'ship_service_css_packagetype\');toggle_show(\'ship_service_css_pickuptype\');print_ship_package_types(\'ship_packagetype_' . $i . '_container\', \'ship_packagetype_' . $i . '\', \'' . $ilance->GPC['ship_packagetype_' . $i] . '\', ' . ($disabled ? 'true' : 'false') . ', \'' . $ilance->GPC['ship_service_' . $i] . '\');print_ship_pickup_types(\'ship_pickuptype_' . $i . '_container\', \'ship_pickuptype_' . $i . '\', \'' . $ilance->GPC['ship_pickuptype_' . $i] . '\', ' . ($disabled ? 'true' : 'false') . ', \'' . $ilance->GPC['ship_service_' . $i] . '\');';
							}
							// #### worldwide ######
							if (isset($ilance->GPC['ship_options_' . $i]) AND $ilance->GPC['ship_options_' . $i] == 'worldwide' AND isset($ilance->GPC['ship_service_' . $i]))
							{
								$onload .= 'fetch_js_object(\'ship_options_' . $i . '\').options[fetch_js_object(\'ship_options_' . $i . '\').selectedIndex = 2];';
								$onload .= 'toggle_hide(\'ship_options_custom_regionnav_' . $i . '\');';
								$onload .= 'print_shipping_services(\'ship_service_' . $i . '_container\', \'ship_service_' . $i . '\', true, true, ' . $ilance->GPC['ship_service_' . $i] . ($disabled ? ', true' : ', false') . ', 1, \'ship_packagetype_' . $i . '_container\', \'ship_packagetype_' . $i . '\', \'' . $ilance->GPC['ship_packagetype_' . $i] . '\', \'ship_pickuptype_' . $i . '_container\', \'ship_pickuptype_' . $i . '\', \'' . $ilance->GPC['ship_pickuptype_' . $i] . '\', \'' . $ilance->GPC['ship_method'] . '\');';
								$onload .= 'toggle_show(\'ship_packagetype_' . $i . '\');toggle_show(\'ship_pickuptype_' . $i . '\');';
								$onload .= 'toggle_show(\'ship_service_css_packagetype\');toggle_show(\'ship_service_css_pickuptype\');print_ship_package_types(\'ship_packagetype_' . $i . '_container\', \'ship_packagetype_' . $i . '\', \'' . $ilance->GPC['ship_packagetype_' . $i] . '\', ' . ($disabled ? 'true' : 'false') . ', \'' . $ilance->GPC['ship_service_' . $i] . '\');print_ship_pickup_types(\'ship_pickuptype_' . $i . '_container\', \'ship_pickuptype_' . $i . '\', \'' . $ilance->GPC['ship_pickuptype_' . $i] . '\', ' . ($disabled ? 'true' : 'false') . ', \'' . $ilance->GPC['ship_service_' . $i] . '\');';
							}
							// #### custom locations
							if (isset($ilance->GPC['ship_options_' . $i]) AND $ilance->GPC['ship_options_' . $i] == 'custom' AND isset($ilance->GPC['ship_service_' . $i]))
							{
								$onload .= 'fetch_js_object(\'ship_options_' . $i . '\').options[fetch_js_object(\'ship_options_' . $i . '\').selectedIndex = 3];';
								$onload .= 'toggle_show(\'ship_options_custom_regionnav_' . $i . '\');';
								$onload .= 'print_shipping_services(\'ship_service_' . $i . '_container\', \'ship_service_' . $i . '\', true, true, ' . $ilance->GPC['ship_service_' . $i] . ($disabled ? ', true' : ', false') . ', 1, \'ship_packagetype_' . $i . '_container\', \'ship_packagetype_' . $i . '\', \'' . $ilance->GPC['ship_packagetype_' . $i] . '\', \'ship_pickuptype_' . $i . '_container\', \'ship_pickuptype_' . $i . '\', \'' . $ilance->GPC['ship_pickuptype_' . $i] . '\', \'' . $ilance->GPC['ship_method'] . '\');';
								$onload .= 'toggle_show(\'ship_packagetype_' . $i . '\');toggle_show(\'ship_pickuptype_' . $i . '\');';
								$onload .= 'toggle_show(\'ship_service_css_packagetype\');toggle_show(\'ship_service_css_pickuptype\');print_ship_package_types(\'ship_packagetype_' . $i . '_container\', \'ship_packagetype_' . $i . '\', \'' . $ilance->GPC['ship_packagetype_' . $i] . '\', ' . ($disabled ? 'true' : 'false') . ', \'' . $ilance->GPC['ship_service_' . $i] . '\');print_ship_pickup_types(\'ship_pickuptype_' . $i . '_container\', \'ship_pickuptype_' . $i . '\', \'' . $ilance->GPC['ship_pickuptype_' . $i] . '\', ' . ($disabled ? 'true' : 'false') . ', \'' . $ilance->GPC['ship_service_' . $i] . '\');';
								$regions = $ilance->shipping->fetch_listing_shipping_regions($ilance->GPC['project_id'], $i);
								if (is_array($regions) AND count($regions) > 0)
								{
									foreach ($regions AS $row => $regionarray)
									{
										if ($row == $i)
										{
											foreach ($regionarray AS $region)
											{
												$region = strtolower(str_replace(' ', '_', $region));
												switch ($region)
												{
													case 'north_america':
													{
														$onload .= 'fetch_js_object(\'ship_options_custom_region_' . $i . '_1\').checked=true;';
														break;	
													}
													case 'south_america':
													{
														$onload .= 'fetch_js_object(\'ship_options_custom_region_' . $i . '_4\').checked=true;';
														break;	
													}
													case 'oceania':
													{
														$onload .= 'fetch_js_object(\'ship_options_custom_region_' . $i . '_6\').checked=true;';
														break;	
													}
													case 'europe':
													{
														$onload .= 'fetch_js_object(\'ship_options_custom_region_' . $i . '_2\').checked=true;';
														break;	
													}
													case 'asia':
													{
														$onload .= 'fetch_js_object(\'ship_options_custom_region_' . $i . '_5\').checked=true;';
														break;	
													}
													case 'antartica':
													{
														$onload .= 'fetch_js_object(\'ship_options_custom_region_' . $i . '_7\').checked=true;';
														break;	
													}
													case 'africa':
													{
														$onload .= 'fetch_js_object(\'ship_options_custom_region_' . $i . '_3\').checked=true;';
														break;	
													}
												}
											}
										}
									}
								}
								unset($regions);
							}
							if (isset($ilance->GPC['ship_fee_' . $i]) AND $ilance->GPC['ship_fee_' . $i] > 0)
							{
								$onload .= 'fetch_js_object(\'ship_fee_' . $i . '\').value = \'' . $ilance->GPC['ship_fee_' . $i] . '\';';
							}
							if (isset($ilance->GPC['ship_fee_next_' . $i]) AND $ilance->GPC['ship_fee_next_' . $i] > 0)
							{
								$onload .= 'fetch_js_object(\'ship_fee_next_' . $i . '\').value = \'' . $ilance->GPC['ship_fee_next_' . $i] . '\';';
							}
							if (isset($ilance->GPC['freeshipping_' . $i]) AND $ilance->GPC['freeshipping_' . $i] == '1')
							{
								$onload .= 'fetch_js_object(\'freeshipping_' . $i . '\').checked = true;';
							}
							$onload .= 'toggle_show(\'ship_method_service_options_' . $i . '\');fetch_js_object(\'ship_fee_' . $i . '\').disabled = true;fetch_js_object(\'ship_fee_next_' . $i . '\').disabled = true;fetch_js_object(\'freeshipping_' . $i . '\').disabled = true;fetch_js_object(\'ship_service_css_cost\').className=\'litegray\';fetch_js_object(\'ship_service_' . $i . '_css_costsymbol\').className=\'litegray\';fetch_js_object(\'ship_service_css_freeshipping\').className=\'litegray\';fetch_js_object(\'ship_service_' . $i . '_css_freeshippinganswer\').className=\'litegray\';';
						}
						break;
					}
					case 'localpickup':
					{
						$onload .= ($ilconfig['shippingapi'])
							? 'fetch_js_object(\'ship_method\').options[fetch_js_object(\'ship_method\').selectedIndex=2];'
							: 'fetch_js_object(\'ship_method\').options[fetch_js_object(\'ship_method\').selectedIndex=1];';
						$onload .= 'toggle_hide(\'showshipping\');toggle_hide(\'handlingfeeheader\');toggle_hide(\'handlingfeerow\');';
						break;
					}
					case 'digital':
					{
						if ($ilconfig['digitaldownload'])
						{
							$onload .= ($ilconfig['shippingapi'])
								? 'fetch_js_object(\'ship_method\').options[fetch_js_object(\'ship_method\').selectedIndex=3];'
								: 'fetch_js_object(\'ship_method\').options[fetch_js_object(\'ship_method\').selectedIndex=2];';
							$onload .= 'toggle_hide(\'showshipping\');toggle_hide(\'handlingfeeheader\');toggle_hide(\'handlingfeerow\');';	
						}
						break;
					}
				}
			}
		}
		// default shipping logic is "flat rate" so we hide package and pickup types
		else
		{
			$onload .= 'toggle_hide(\'ship_service_css_packagetype\');toggle_hide(\'ship_service_css_pickuptype\');';
			for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
			{
				$onload .= 'toggle_hide(\'ship_packagetype_' . $i . '\');toggle_hide(\'ship_pickuptype_' . $i . '\');';
			}
		}
		$html = '<div style="padding-top:' . $ilconfig['table_cellpadding'] . 'px; padding-left:' . $ilconfig['table_cellpadding'] . 'px"><select id="ship_method" name="ship_method" class="select-250" onchange="javascript:
if (fetch_js_object(\'ship_method\').value == \'flatrate\')
{
        toggle_show(\'showshipping\');
        toggle_show(\'ship_method_service_options\');
        toggle_hide(\'ship_method_calculated_options\');
	toggle_hide(\'ship_service_css_packagetype\');
	toggle_hide(\'ship_service_css_pickuptype\');
	toggle_show(\'handlingfeeheader\');
	toggle_show(\'handlingfeerow\');
	toggle_hide(\'digitalfile\');
';
for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
{
	$html .= '
	toggle_show(\'ship_fee_' . $i . '\');
	toggle_show(\'ship_fee_next_' . $i . '\');
	toggle_show(\'freeshipping_' . $i . '\');
	toggle_show(\'ship_service_css_cost\');
	toggle_show(\'ship_service_' . $i . '_css_costsymbol\');
	toggle_show(\'next_ship_service_' . $i . '_css_costsymbol\');
	toggle_show(\'ship_service_css_freeshipping\');
	toggle_show(\'ship_service_' . $i . '_css_freeshippinganswer\');
	fetch_js_object(\'ship_fee_' . $i . '\').disabled=false;
        fetch_js_object(\'ship_fee_next_' . $i . '\').disabled=false;
        fetch_js_object(\'freeshipping_' . $i . '\').disabled=false;
        fetch_js_object(\'ship_service_css_cost\').className=\'black\';
        fetch_js_object(\'ship_service_' . $i . '_css_costsymbol\').className=\'black\';
	fetch_js_object(\'next_ship_service_' . $i . '_css_costsymbol\').className=\'black\';
        fetch_js_object(\'ship_service_css_freeshipping\').className=\'black\';
        fetch_js_object(\'ship_service_' . $i . '_css_freeshippinganswer\').className=\'black\';
	toggle_hide(\'ship_packagetype_' . $i . '\');
	toggle_hide(\'ship_pickuptype_' . $i . '\');
';
}
$html .= '}';
if ($ilconfig['shippingapi'] AND ((!empty($ilconfig['ups_access_id']) AND !empty($ilconfig['ups_username']) AND !empty($ilconfig['ups_password'])) OR (!empty($ilconfig['usps_login']) AND !empty($ilconfig['usps_password'])) OR (!empty($ilconfig['fedex_account']) AND !empty($ilconfig['fedex_access_id']))))
{
    $html .= '
    else if (fetch_js_object(\'ship_method\').value == \'calculated\')
    {
	    toggle_show(\'showshipping\');
	    toggle_show(\'ship_method_calculated_options\');
	    toggle_show(\'ship_service_css_packagetype\');
	    toggle_show(\'ship_service_css_pickuptype\');
	    toggle_show(\'handlingfeeheader\');
	    toggle_show(\'handlingfeerow\');
	    toggle_hide(\'digitalfile\');
    ';
    for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
    {
	    $html .= 'fetch_js_object(\'ship_fee_' . $i . '\').value=\'\';
	fetch_js_object(\'ship_fee_' . $i . '\').disabled=true;
	fetch_js_object(\'ship_fee_next_' . $i . '\').value=\'\';
	fetch_js_object(\'ship_fee_next_' . $i . '\').disabled=true;
	fetch_js_object(\'freeshipping_' . $i . '\').disabled=true;
	fetch_js_object(\'ship_service_css_cost\').className=\'litegray\';
	fetch_js_object(\'ship_service_' . $i . '_css_costsymbol\').className=\'litegray\';
	fetch_js_object(\'next_ship_service_' . $i . '_css_costsymbol\').className=\'litegray\';
	fetch_js_object(\'ship_service_css_freeshipping\').className=\'litegray\';
	fetch_js_object(\'ship_service_' . $i . '_css_freeshippinganswer\').className=\'litegray\';
	toggle_show(\'ship_packagetype_' . $i . '\');
	toggle_show(\'ship_pickuptype_' . $i . '\');
	toggle_hide(\'ship_fee_' . $i . '\');
	toggle_hide(\'ship_fee_next_' . $i . '\');
	toggle_hide(\'freeshipping_' . $i . '\');
	toggle_hide(\'ship_service_css_cost\');
	toggle_hide(\'ship_service_' . $i . '_css_costsymbol\');
	toggle_hide(\'next_ship_service_' . $i . '_css_costsymbol\');
	toggle_hide(\'ship_service_css_freeshipping\');
	toggle_hide(\'ship_service_' . $i . '_css_freeshippinganswer\');
    ';
    }
    $html .= '}';
}
$html .= '
else if (fetch_js_object(\'ship_method\').value == \'digital\')
{
        toggle_hide(\'showshipping\');
	toggle_hide(\'handlingfeeheader\');
	toggle_hide(\'handlingfeerow\');
	toggle_show(\'digitalfile\');
}
else if (fetch_js_object(\'ship_method\').value == \'localpickup\')
{
        toggle_hide(\'showshipping\');
	toggle_hide(\'handlingfeeheader\');
	toggle_hide(\'handlingfeerow\');
	toggle_hide(\'digitalfile\');
}" ' . ($disabled ? 'disabled="disabled"' : '') . '>
<option value="flatrate">{_flat_rate_same_cost_to_all_buyers}</option>';
if ($ilconfig['shippingapi'] AND ((!empty($ilconfig['ups_access_id']) AND !empty($ilconfig['ups_username']) AND !empty($ilconfig['ups_password'])) OR (!empty($ilconfig['usps_login']) AND !empty($ilconfig['usps_password'])) OR (!empty($ilconfig['fedex_account']) AND !empty($ilconfig['fedex_access_id']))))
{
	$html .= '<option value="calculated">{_auto_calculated_cost_varies_by_buyer_location}</option>';
}
$html .= '<option value="localpickup">{_no_shipping_local_pickup}</option>
';
if ($ilconfig['digitaldownload'])
{
	$html .= '<option value="digital">{_digital_download_delivery}</option>';
}
$html .= '</select>
</div>                    
	<div id="showshipping">		
		<div id="ship_method_calculated_options" style="display:none">
			<div style="background-color:#cccccc; margin-top:12px; margin-bottom:12px; width:100%; height:1px"></div>
			<span style="float:left;margin-right:20px"><img id="shippingbox" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'shippingbox.png" border="0" alt="" id="" /></span>
			<div>{_dimentions} <span class="smaller litegray">&nbsp;&nbsp;({_length_x_width_x_height})</span></div>
			<div style="padding-top:3px"><input type="text" id="ship_length" name="ship_length" value="' . ((isset($ilance->GPC['ship_length'])) ? intval($ilance->GPC['ship_length']) : '') . '" class="input" size="5" maxlength="10" ' . ($disabled ? 'disabled="disabled"' : '') . '>&nbsp;{_inches_shortform}&nbsp;&nbsp;&nbsp;X&nbsp;<input type="text" id="ship_width" name="ship_width" value="' . ((isset($ilance->GPC['ship_width'])) ? intval($ilance->GPC['ship_width']) : '') . '" class="input" size="5" maxlength="10" ' . ($disabled ? 'disabled="disabled"' : '') . '>&nbsp;{_inches_shortform}&nbsp;&nbsp;&nbsp;X&nbsp;<input type="text" id="ship_height" name="ship_height" value="' . ((isset($ilance->GPC['ship_height'])) ? intval($ilance->GPC['ship_height']) : '') . '" class="input" size="5" maxlength="10" ' . ($disabled ? 'disabled="disabled"' : '') . '>&nbsp;{_inches_shortform}</div>
			<div style="margin-top:12px"></div>
			<div>{_package_weight}</div>
			<div style="padding-top:3px"><input type="text" id="ship_weightlbs" name="ship_weightlbs" value="' . ((isset($ilance->GPC['ship_weightlbs'])) ? intval($ilance->GPC['ship_weightlbs']) : '') . '" class="input" size="5" maxlength="10" ' . ($disabled ? 'disabled="disabled"' : '') . '>&nbsp;&nbsp;{_lbs}&nbsp;&nbsp;&nbsp;<input type="text" id="ship_weightoz" name="ship_weightoz" value="' . ((isset($ilance->GPC['ship_weightoz'])) ? intval($ilance->GPC['ship_weightoz']) : '') . '" class="input" size="5" maxlength="10" ' . ($disabled ? 'disabled="disabled"' : '') . '>&nbsp;&nbsp;{_oz}</div>
			
		</div>
		<div style="margin-top:12px; margin-bottom:12px"></div>	
		<div id="ship_method_service_options"><input type="hidden" value="' . $shippercount . '" id="shippercount" name="shippercount" />
		<div>				
		<table cellpadding="12" cellspacing="0" border="0" width="100%">
		<tr class="alt2">
			<td nowrap="nowrap"><div class="smaller">{_ship_to}</div></td>
			<td nowrap="nowrap"><div class="smaller">{_services}</div></td>
			<td nowrap="nowrap"><div id="ship_service_css_packagetype" class="smaller">{_package_type}</div></td>
			<td nowrap="nowrap"><div id="ship_service_css_pickuptype" class="smaller">{_pickup_type}</div></td>
			<td nowrap="nowrap"><div id="ship_service_css_cost"><div class="smaller">{_cost}</div></div></td>
			<td nowrap="nowrap"><div id="ship_service_css_cost_next" style="display:none"><div class="smaller">{_cost_for_every_next} <span class="smaller" id="ship_service_css_cost_next_type_item" style="display:none">{_item_lower}</span><span class="smaller" id="ship_service_css_cost_next_type_lot" style="display:none">{_lot}</span></div></div></td>
			<td nowrap="nowrap"><div id="ship_service_css_freeshipping"><div class="smaller">{_free_shipping}?</div></div></td>
			<td nowrap="nowrap"><div class="smaller">{_actions}</div></td>
		</tr>';	    
		for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
		{
			$html .= '<tbody id="ship_method_service_options_' . $i . '"' . (($i > 1) ? ' style="display:none"' : '') . '>
		<tr class="alt1">
			<td valign="top"><select id="ship_options_' . $i . '" name="ship_options_' . $i . '" class="select" onchange="ship_to_options_onchange(' . $i . ', ' . intval($disabled) . ')"' . ($disabled ? ' disabled="disabled"' : '') . '>
<option value="" selected="selected">{_please_select}</option>
<option value="domestic">' . handle_input_keywords($ilconfig['registrationdisplay_defaultcountry']) . ' {_only_lower}</option>
' . (($ilconfig['worldwideshipping'] == '1') ? '<option value="worldwide">{_worldwide}</option>' : '') . '
<option value="custom">{_choose_locations} ↓</option>
</select>
			</td>
			<td valign="top">
				<div>
					<div id="ship_service_' . $i . '_container">
						<select id="ship_service_' . $i . '" name="ship_service_' . $i . '" class="select" ' . ($disabled ? 'disabled="disabled"' : '') . '>
						<option value="0" selected="selected">-</option>
						</select>
					</div>
				</div>
			</td>
			<td valign="top">
				<div>
					<div id="ship_packagetype_' . $i . '_container">
						<select id="ship_packagetype_' . $i . '" name="ship_packagetype_' . $i . '" class="select" ' . ($disabled ? 'disabled="disabled"' : '') . '>
						<option value="" selected="selected">-</option>
						</select>
					</div>
				</div>
			</td>
			<td valign="top">
				<div>
					<div id="ship_pickuptype_' . $i . '_container">
						<select id="ship_pickuptype_' . $i . '" name="ship_pickuptype_' . $i . '" class="select" ' . ($disabled ? 'disabled="disabled"' : '') . '>
						<option value="" selected="selected">-</option>
						</select>
					</div>
				</div>
			</td>
			<td valign="top" nowrap="nowrap"><div><div><span id="ship_service_' . $i . '_css_costsymbol">' . $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_left'] . '</span> <input type="text" id="ship_fee_' . $i . '" name="ship_fee_' . $i . '" value="" class="input" autocomplete="off" size="6" maxlength="10" ' . ($disabled ? 'disabled="disabled"' : '') . '> <span id="ship_service_' . $i . '_css_costsymbol_right">' . $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_right'] . '</span></div></div></td>
			<td valign="top" nowrap="nowrap"><div id="next_ship_service_' . $i . '_cost" style="display:none"><div><span id="next_ship_service_' . $i . '_css_costsymbol">' . $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_left'] . '</span> <input type="text" id="ship_fee_next_' . $i . '" name="ship_fee_next_' . $i . '" value="" class="input" autocomplete="off" size="6" maxlength="10" ' . ($disabled ? 'disabled="disabled"' : '') . '> <span id="next_ship_service_' . $i . '_css_costsymbol_right">' . $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_right'] . '</span></div></div></td>
			<td valign="top" nowrap="nowrap"><div><label for=""><input id="freeshipping_' . $i . '" type="checkbox" name="freeshipping_' . $i . '" onclick="hide_shipp(' . $i . ');" value="1" ' . ($disabled ? 'disabled="disabled"' : '') . ' />&nbsp;<span id="ship_service_' . $i . '_css_freeshippinganswer"><span class="smaller">{_yes}</span></span></label></div></td>
			<td valign="top" nowrap="nowrap">' . (($i > 1) ? '<div class="smaller blue" id="remove_link_' . $i . '"><a href="javascript:void(0)" onclick="ship_service_remove(\'' . $i . '\')" style="text-decoration:underline">{_remove}</a></div>' : '') . (($ilconfig['shippingapi']) ? 
			    '<div class="smaller blue" style="padding-top:3px">
				<a href="javascript:void(0)" onclick="javascript:jQuery(\'#shippingcalculator_modal\').jqm({modal: false}).jqmShow();
				print_shipping_services(\'modal_shipperid_container\', \'modal_shipperid\', true, false, ' . (isset($ilance->GPC['ship_service_' . $i] ) ? $ilance->GPC['ship_service_' . $i] : 'fetch_js_object(\'ship_service_' . $i . '\').value') . ', false, 1, \'modal_packagetype_container\', \'modal_packagetype\', \'' . (isset($ilance->GPC['ship_packagetype_' . $i] ) ? $ilance->GPC['ship_packagetype_' . $i] : '') . '\', \'modal_pickuptype_container\', \'modal_pickuptype\', \'' . (isset($ilance->GPC['ship_pickuptype_' . $i] ) ? $ilance->GPC['ship_pickuptype_' . $i] : '') . '\', \'\');
				print_ship_package_types(\'modal_packagetype_container\', \'modal_packagetype\', ' . (isset($ilance->GPC['ship_packagetype_' . $i]) ? ('\'' . $ilance->GPC['ship_packagetype_' . $i] . '\'')  : 'fetch_js_object(\'ship_packagetype_' . $i . '\').value') . ', ' . ($disabled ? 'true' : 'false') . ', ' . (isset($ilance->GPC['ship_service_' . $i]) ? ('\'' . $ilance->GPC['ship_service_' . $i] . '\'') : 'fetch_js_object(\'ship_service_' . $i . '\').value') . ');
				print_ship_pickup_types(\'modal_pickuptype_container\', \'modal_pickuptype\', ' . (isset($ilance->GPC['ship_pickuptype_' . $i]) ? ('\'' . $ilance->GPC['ship_pickuptype_' . $i] . '\'') : 'fetch_js_object(\'ship_pickuptype_' . $i . '\').value') . ', ' . ($disabled ? 'true' : 'false') . ', ' . (isset($ilance->GPC['ship_service_' . $i]) ? ('\'' . $ilance->GPC['ship_service_' . $i] . '\'') : 'fetch_js_object(\'ship_service_' . $i . '\').value') . ');
				update_dimentions()" style="text-decoration:underline">{_research}</a></div>
			    ' : '') . '</td>
		</tr>
	</tbody>
	<tbody id="ship_options_custom_regionnav_' . $i . '" style="display:none">
		<tr class="alt2_top">
			<td colspan="8">
				<div style="margin-left:7px">
					<table cellpadding="0" cellspacing="0" border="0" width="100%">
					<tr valign="top">
					    ' . (($ilregions['north_america']) ? '<td><input type="checkbox" id="ship_options_custom_region_' . $i . '_1" name="ship_options_custom_region_' . $i . '[]" value="north_america" ' . ($disabled ? 'disabled="disabled"' : '') . '> <label for="ship_options_custom_region_' . $i . '_1"><span id="ship_options_custom_region_' . $i . '_1_label">{_north_america}</span></label><span id="ship_options_custom_region_' . $i . '_1_exclude" style="display:none" class="smaller blue">&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" onclick="" style="text-decoration:underline">{_exclude}..</a></span></td>' : '') . '
					    ' . (($ilregions['europe']) ? '<td><input type="checkbox" id="ship_options_custom_region_' . $i . '_2" name="ship_options_custom_region_' . $i . '[]" value="europe" ' . ($disabled ? 'disabled="disabled"' : '') . '> <label for="ship_options_custom_region_' . $i . '_2"><span id="ship_options_custom_region_' . $i . '_2_label">{_europe}</span></label> <span id="ship_options_custom_region_' . $i . '_2_exclude" style="display:none" class="smaller blue">&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" onclick="" style="text-decoration:underline">{_exclude}..</a></span></td>' : '') . '
					    ' . (($ilregions['africa']) ? '<td><input type="checkbox" id="ship_options_custom_region_' . $i . '_3" name="ship_options_custom_region_' . $i . '[]" value="africa" ' . ($disabled ? 'disabled="disabled"' : '') . '> <label for="ship_options_custom_region_' . $i . '_3"><span id="ship_options_custom_region_' . $i . '_3_label">{_africa}</span></label> <span id="ship_options_custom_region_' . $i . '_3_exclude" style="display:none" class="smaller blue">&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" onclick="" style="text-decoration:underline">{_exclude}..</a></span></td>' : '') . '
					    ' . (($ilregions['south_america']) ? '<td><input type="checkbox" id="ship_options_custom_region_' . $i . '_4" name="ship_options_custom_region_' . $i . '[]" value="south_america" ' . ($disabled ? 'disabled="disabled"' : '') . '> <label for="ship_options_custom_region_' . $i . '_4"><span id="ship_options_custom_region_' . $i . '_4_label">{_south_america}</span></label> <span id="ship_options_custom_region_' . $i . '_4_exclude" style="display:none" class="smaller blue">&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" onclick="" style="text-decoration:underline">{_exclude}..</a></span></td>' : '') . '
					    ' . (($ilregions['asia']) ? '<td><input type="checkbox" id="ship_options_custom_region_' . $i . '_5" name="ship_options_custom_region_' . $i . '[]" value="asia" ' . ($disabled ? 'disabled="disabled"' : '') . '> <label for="ship_options_custom_region_' . $i . '_5"><span id="ship_options_custom_region_' . $i . '_5_label">{_asia}</span></label> <span id="ship_options_custom_region_' . $i . '_5_exclude" style="display:none" class="smaller blue">&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" onclick="" style="text-decoration:underline">{_exclude}..</a></span></td>' : '') . '
					    ' . (($ilregions['oceania']) ? '<td><input type="checkbox" id="ship_options_custom_region_' . $i . '_6" name="ship_options_custom_region_' . $i . '[]" value="oceania" ' . ($disabled ? 'disabled="disabled"' : '') . '> <label for="ship_options_custom_region_' . $i . '_6"><span id="ship_options_custom_region_' . $i . '_6_label">{_oceania}</span></label> <span id="ship_options_custom_region_' . $i . '_6_exclude" style="display:none" class="smaller blue">&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" onclick="" style="text-decoration:underline">{_exclude}..</a></span></td>' : '') . '
					    ' . (($ilregions['antarctica']) ? '<td><input type="checkbox" id="ship_options_custom_region_' . $i . '_7" name="ship_options_custom_region_' . $i . '[]" value="antarctica" ' . ($disabled ? 'disabled="disabled"' : '') . '> <label for="ship_options_custom_region_' . $i . '_7"><span id="ship_options_custom_region_' . $i . '_7_label">{_antarctica}</span></label> <span id="ship_options_custom_region_' . $i . '_7_exclude" style="display:none" class="smaller blue">&nbsp;&nbsp;&nbsp;<a href="javascript:void(0)" onclick="" style="text-decoration:underline">{_exclude}..</a></span></td>' : '') . '
					</tr>
					</table>
				</div>
			</td>
		</tr>
	</tbody>';
		}
		$html .= '</tr></table></div></div><!-- END service options --><div class="smaller blue" style="padding-top:' . $ilconfig['table_cellpadding'] . 'px;padding-left:' . $ilconfig['table_cellpadding'] . 'px;padding-bottom:' . $ilconfig['table_cellpadding'] . 'px"><a href="javascript:void(0)" onclick="ship_service_add()">{_offer_additional_service}</a></div></div>';
		return $html;
        }
	
	/**
        * Function to print the shipping and handling duration and length in days it will take the seller to ship the item
        *
        * @param       boolean       disabled? (default false)
        *
        * @return      string        HTML representation of the shipping and handling form elements
        */
	function print_ship_handling_logic($disabled = false)
	{
		global $ilance, $ilconfig, $phrase, $onload, $project_id, $cid, $ilpage, $currencysymbol, $headinclude;
		$html = '<table border="0" cellspacing="0" cellpadding="0">
<tr valign="top">
	<td nowrap="nowrap"><div>{_handling_time} <div style="padding-top:3px">
	<select name="ship_handlingtime" id="ship_handlingtime" class="select-75" ' . ($disabled ? 'disabled="disabled"' : '') . '>
	<option value="1" ' . ((isset($ilance->GPC['ship_handlingtime']) AND $ilance->GPC['ship_handlingtime'] == '1') ? 'selected="selected"' : '') . '>1 {_day_lower}</option>
	<option value="2" ' . ((isset($ilance->GPC['ship_handlingtime']) AND $ilance->GPC['ship_handlingtime'] == '2') ? 'selected="selected"' : '') . '>2 {_days_lower}</option>
	<option value="3" ' . ((isset($ilance->GPC['ship_handlingtime']) AND $ilance->GPC['ship_handlingtime'] == '3') ? 'selected="selected"' : '') . '>3 {_days_lower}</option>
	<option value="4" ' . ((isset($ilance->GPC['ship_handlingtime']) AND $ilance->GPC['ship_handlingtime'] == '4') ? 'selected="selected"' : '') . '>4 {_days_lower}</option>
	<option value="5" ' . ((isset($ilance->GPC['ship_handlingtime']) AND $ilance->GPC['ship_handlingtime'] == '5') ? 'selected="selected"' : '') . '>5 {_days_lower}</option>
	<option value="10" ' . ((isset($ilance->GPC['ship_handlingtime']) AND $ilance->GPC['ship_handlingtime'] == '10') ? 'selected="selected"' : '') . '>10 {_days_lower}</option>
	<option value="15" ' . ((isset($ilance->GPC['ship_handlingtime']) AND $ilance->GPC['ship_handlingtime'] == '15') ? 'selected="selected"' : '') . '>15 {_days_lower}</option>
	<option value="30" ' . ((isset($ilance->GPC['ship_handlingtime']) AND $ilance->GPC['ship_handlingtime'] == '30') ? 'selected="selected"' : '') . '>30 {_days_lower}</option>
	</select></div></div></td>
	<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td nowrap="nowrap"><div>{_handling_fee} <div style="padding-top:3px"><span id="ship_handlingfee_currency">' . $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_left'] . '</span> <input type="text" name="ship_handlingfee" value="' . ((isset($ilance->GPC['ship_handlingfee'])) ? handle_input_keywords($ilance->GPC['ship_handlingfee']) : '') . '" id="ship_handlingfee" style="width:50px" class="input" ' . ($disabled ? 'disabled="disabled"' : '') . ' /> <span id="ship_handlingfee_currency_right">' . $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_right'] . '</span></div></div></td>
</tr>
</table>';
		return $html;
	}
	
	/**
        * Function to print the sales tax information bit for a new item being sold
        *
        * @param       integer       country id
        * @param       string        state or province (ie: Ontario)
        * @param       string        state fieldname / id name
        * @param       boolean       disabled? (default false)
        *
        * @return      string        HTML representation of the shipping and handling form elements
        */
	function print_sales_tax_logic($countryid = 0, $state = '', $stateid = '', $disabled = false)
	{
		global $ilance, $ilconfig, $phrase, $onload, $project_id, $cid, $ilpage, $currencysymbol, $headinclude;
		$html = '<table border="0" cellspacing="0" cellpadding="0">
<tr valign="top">
	<td nowrap="nowrap"><div>{_state_or_province} <div style="padding-top:3px">
	<div id="stateid3">' . $ilance->common_location->construct_state_pulldown($countryid, $state, $stateid, $disabled, true) . '</div></div></div></td>
	<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td nowrap="nowrap"><div>{_percentage}<div style="padding-top:3px"><input type="text" name="salestaxrate" value="' . ((isset($ilance->GPC['salestaxrate'])) ? handle_input_keywords($ilance->GPC['salestaxrate']) : '') . '" id="salestaxrate" style="font-family: verdana; width:50px" ' . ($disabled ? 'disabled="disabled"' : '') . ' /> %</div></div></td>
</tr>
<tr>
	<td colspan="2"><div style="padding-top:8px"><input onclick="if (fetch_js_object(\'salestaxstate\').disabled==true){fetch_js_object(\'salestaxstate\').disabled=false;}else{fetch_js_object(\'salestaxstate\').disabled=true;}" type="checkbox" name="salestaxentirecountry" value="1" id="salestaxentirecountry"' . ((isset($ilance->GPC['salestaxentirecountry']) AND $ilance->GPC['salestaxentirecountry'] == '1') ? ' checked="checked"' : '') . ' /> {_apply_tax_to_country_of} <span id="hidden_salestaxentirecountry_text">' . handle_input_keywords($ilance->GPC['country']) . '</span></div></td>
</tr>
<tr>
	<td colspan="2"><div style="padding-top:4px"><input type="checkbox" name="salestaxshipping" value="1" id="salestaxshipping"' . ((isset($ilance->GPC['salestaxshipping']) AND $ilance->GPC['salestaxshipping'] == '1') ? ' checked="checked"' : '') . ' /> {_apply_tax_to_shipping_handling_costs}</div></td>
</tr>
</table>';
		return $html;
	}
        
        /**
        * Function to print the list of shipping partners
        *
        * @return      string      
        */
        function print_return_policy($disabled = false)
        {
                global $ilance, $phrase, $ilconfig;
                $html = $returnpolicy = '';
                if (isset($ilance->GPC['returnaccepted']) AND $ilance->GPC['returnaccepted'] == '1')
                {
                        $return1 = 'checked="checked"';
                        $return0 = '';
                        $returnstyle = '';
                }
                else
                {
                        $return1 = '';
                        $return0 = 'checked="checked"';
                        $returnstyle = 'display:none';
                }
                if (isset($ilance->GPC['returnshippaidby']) AND $ilance->GPC['returnshippaidby'] == 'seller')
                {
                        $returnship1 = '';
                        $returnship2 = 'checked="checked"';
                }
                else
                {
                        $returnship1 = 'checked="checked"';
                        $returnship2 = '';
                }
                if (!empty($ilance->GPC['returnpolicy']))
                {
                        $returnpolicy = $ilance->GPC['returnpolicy'];        
                }
                $d0 = $d3 = $d7 = $d14 = $d30 = $d60 = '';
                if (empty($ilance->GPC['returnwithin']))
                {
                        $d0 = 'selected="selected"';
                }
                else
                {
                        if ($ilance->GPC['returnwithin'] == '3')
                        {
                                $d3 = 'selected="selected"';
                        }
                        if ($ilance->GPC['returnwithin'] == '7')
                        {
                                $d7 = 'selected="selected"';
                        }
                        if ($ilance->GPC['returnwithin'] == '14')
                        {
                                $d14 = 'selected="selected"';
                        }
                        if ($ilance->GPC['returnwithin'] == '30')
                        {
                                $d30 = 'selected="selected"';
                        }
                        if ($ilance->GPC['returnwithin'] == '60')
                        {
                                $d60 = 'selected="selected"';
                        }
                }
                $e0 = $e1 = $e2 = $e3 = '';
                if (empty($ilance->GPC['returngivenas']))
                {
                        $e0 = 'selected="selected"';
                }
                else
                {
                        if ($ilance->GPC['returngivenas'] == 'exchange')
                        {
                                $e1 = 'selected="selected"';
                        }
                        if ($ilance->GPC['returngivenas'] == 'credit')
                        {
                                $e2 = 'selected="selected"';
                        }
                        if ($ilance->GPC['returngivenas'] == 'moneyback')
                        {
                                $e3 = 'selected="selected"';
                        }
                }
                $dis = '';
                if ($disabled)
                {
                        $dis = 'disabled="disabled"';
                }
                $html = '<div>
<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
<tr>
	<td width="1%" valign="top"><input type="radio" name="returnaccepted" id="returnaccepted0" value="0" ' . $return0 . ' onclick="toggle_hide_tr(\'returnpolicies\');" ' . $dis . ' /></td>
	<td><div><label for="returnaccepted0"> <strong>{_returns_not_accepted}</strong></label></div></td>
</tr>
<tr>
	<td width="1%" valign="top"><input type="radio" name="returnaccepted" id="returnaccepted1" value="1" ' . $return1 . ' onclick="toggle_show_tr(\'returnpolicies\');" ' . $dis . ' /></td>
	<td><div><label for="returnaccepted1"> <strong>{_returns_accepted}</strong></label></div></td>
</tr>
		</table>
		<table>
<tr id="returnpolicies" style="' . $returnstyle . '">
	<td width="1%"></td>
	<td>
	<div style="margin-top:-10px">
		<table width="100%" border="0" cellpadding="0" cellspacing="0" style="padding:0px 0px 0px 29px" dir="' . $ilconfig['template_textdirection'] . '">
		<tr>
			<td>
			<div style="padding:' . $ilconfig['table_cellpadding'] . 'px">
				<div style="padding-bottom:3px"><strong>{_item_must_be_returned_within}</strong><div class="gray" style="padding-top:3px">{_specify_your_return_policy_in_days_a_buyer_has_to_return_the_item}</div></div>
				<select name="returnwithin" id="returnwithin" class="select-75" ' . $dis . '>
					<option value="0" ' . $d0 . '>-</option>
					<option value="3" ' . $d3 . '>3 {_days}</option>
					<option value="7" ' . $d7 . '>7 {_days}</option>
					<option value="14" ' . $d14 . '>14 {_days}</option>
					<option value="30" ' . $d30 . '>30 {_days}</option>
					<option value="60" ' . $d60 . '>60 {_days}</option>
				</select>
				<div style="padding-top:8px"><div style="height:1px;width:100%;background-color:#cccccc;margin-top:6px;margin-bottom:14px"></div><strong>{_refund_will_be_provided_as}</strong><div class="gray" style="padding-top:3px;padding-bottom:3px">{_specify_the_type_of_refund_type_the_buyer_will_receive}</div></div>
				<div>
					<select name="returngivenas" id="returngivenas" class="select" ' . $dis . '>
						<option value="" ' . $e0 . '>-</option>
						<option value="exchange" ' . $e1 . '>{_exchange}</option>
						<option value="credit" ' . $e2 . '>{_credit}</option>
						<option value="moneyback" ' . $e3 . '>{_moneyback}</option>
					</select>
				</div>
				<div style="padding-top:8px"><div style="height:1px;width:100%;background-color:#cccccc;margin-top:6px;margin-bottom:14px"></div><strong>{_return_shipping_paid_by}</strong><div class="gray" style="padding-top:3px;padding-bottom:3px">{_please_decide_who_will_pay_for_return_shipping_costs}</div></div>
				<div style="padding-bottom:3px"><label for="returnshippaidby1"><input type="radio" name="returnshippaidby" id="returnshippaidby1" value="buyer" ' . $returnship1 . ' ' . $dis . ' /> {_buyer}</label></div>
				<div><label for="returnshippaidby2"><input type="radio" name="returnshippaidby" id="returnshippaidby2" value="seller" ' . $returnship2 . ' ' . $dis . ' /> {_seller}</label></div>
				<div style="padding-top:8px; padding-bottom:3px"><div style="height:1px;width:100%;background-color:#cccccc;margin-top:8px;margin-bottom:14px"></div><strong>{_return_policy_details}</strong></div>
			
				<div class="ilance_wysiwyg">
				<table cellpadding="0" cellspacing="0" border="0" width="580" dir="' . $ilconfig['template_textdirection'] . '">
				<tr>
				<td class="wysiwyg_wrapper" align="right" height="25">
		
					<table cellpadding="0" cellspacing="0" border="0" width="100%" dir="' . $ilconfig['template_textdirection'] . '">
					<tr>
						<td width="100%" align="left" class="smaller">{_plain_text_only_bbcode_is_currently_not_in_use_for_this_field}</td>
						<td>
								<div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'returnpolicy\', -100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_0.gif" width="21" height="9" alt="{_decrease_size}" border="0" /></a></div>
								<div class="wysiwygbutton"><a href="javascript:void(0)" onclick="return construct_textarea_height(\'returnpolicy\', 100)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'wysiwyg/resize_1.gif" width="21" height="9" alt="{_increase_size}" border="0" /></a></div>
						</td>
						<td style="padding-right:15px"></td>
					</tr>
					</table>
				</td>
				</tr>
					<tr>
						<td><textarea name="returnpolicy" id="returnpolicy" style="width:580px; height:84px; padding:8px; font-family: verdana;" wrap="physical" class="wysiwyg" ' . $dis . '>' . $returnpolicy . '</textarea></td>
					</tr>
				</table>
				</div>
			</div>
			</td>
		</tr>
		</table>
	</div>
</td>
</tr>
</table></div>';
                return $html;
        }
        
	/**
        * Function to print the list of shipping services
        *
        * @param       string      fieldname
        * @param       boolean     hide shipper id? (default false)
        * @parma       boolean     show domestic shippers? (default true)
        * @param       boolean     show intl. shippers? (default false)
        * @param       integer     selected shipper id
        * @param       boolean     pull down menu disabled (default false)
        * @param       boolean     determine if we pre-load shipping package type pull down menu during sell item page
        * @param       string      package type div content id
        * @param       string      package type fieldname
        * @param       string      package type selected value
        * @param       string      pickup type div content id
        * @param       string      pickup type fieldname
        * @param       string      pickup type selected value
        * @param       string      width of the pull down menu (in pixels) default 150
        *
        * @return      string      HTML representation of our pull down menu  
        */
        function print_shipping_partners($fieldname = 'shipperid', $hideshipperid = false, $domestic = 'true', $international = 'false', $shipperid = 0, $disabled = 'false', $jspackagetype = 0, $packagedivcontent = '', $packagefieldname = '', $packagevalue = '', $pickupdivcontent = '', $pickupfieldname = '', $pickupvalue = '', $width = '150', $ship_method = '')
        {
                global $ilance, $phrase, $ilconfig;
                $html = $sqlextra = '';
		if ($domestic == 'true' AND $international == 'false')
		{
			$sqlextra = "WHERE domestic = '1' ";
		}
		else if ($domestic == 'false' AND $international == 'true')
		{
			$sqlextra = "WHERE international = '1' ";
		}
		else if ($domestic == 'true' AND $international == 'true')
		{
			$sqlextra = "WHERE international = '1' OR domestic = '1' ";
		}
		else 
		{
			$sqlextra = "WHERE international = '1' ";
		}
		$sqlextra = ($shipperid > 0) ? '' : $sqlextra;
		if ($ship_method == 'calculated')
		{
			if (empty($ilconfig['ups_access_id']) OR empty($ilconfig['ups_username']) OR empty($ilconfig['ups_password']))
			{
				$sqlextra .= " AND carrier != 'ups' ";
			}
			if (empty($ilconfig['usps_login']) OR empty($ilconfig['usps_password']))
			{
				$sqlextra .= " AND carrier != 'usps' ";
			}
			if (empty($ilconfig['fedex_account']) OR empty($ilconfig['fedex_access_id']))
			{
				$sqlextra .= " AND carrier != 'fedex' ";
			}
		}
		//echo ($jspackagetype ? ' onchange="print_ship_package_types(\'' . $packagedivcontent . '\', \'' . $packagefieldname . '\', \'' . $packagevalue . '\', ' . ($disabled == 'true' ? 'true' : 'false') . ', this);print_ship_pickup_types(\'' . $pickupdivcontent . '\', \'' . $pickupfieldname . '\', \'' . $pickupvalue . '\', ' . ($disabled == 'true' ? 'true' : 'false') . ', this);"' : 'nie');
                $sql = $ilance->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "shipperid, title, carrier
                        FROM " . DB_PREFIX . "shippers
			$sqlextra
                        ORDER BY sort ASC
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $html .= '<select name="' . $fieldname . '" id="' . $fieldname . '"' . ($disabled == 'true' ? ' disabled="disabled"' : '') .
				($jspackagetype 
					? ' onchange="print_ship_package_types(\'' . $packagedivcontent . '\', \'' . $packagefieldname . '\', \'' . $packagevalue . '\', ' . ($disabled == 'true' ? 'true' : 'false') . ', this); print_ship_pickup_types(\'' . $pickupdivcontent . '\', \'' . $pickupfieldname . '\', \'' . $pickupvalue . '\', ' . ($disabled == 'true' ? 'true' : 'false') . ', this);"' 
					: '') . ' class="select" style="width:' . $width . 'px">';
                        $html .= ($hideshipperid == false) ? '<option value="0">-</option>' : '<option value="">-</option>';
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
				switch ($res['carrier'])
				{
					case 'fedex':
					{
						$res['carrier'] = '{_fedex}';
						break;
					}
					case 'usps':
					{
						$res['carrier'] = '{_usps}';
						break;
					}
					case 'ups':
					{
						$res['carrier'] = '{_ups}';
						break;
					}
					default:
					{
						$res['carrier'] = (!empty($res['carrier']) ? $res['carrier'] : '');
						break;
					}
				}
				if ($res['shipperid'] == $shipperid)
				{
					$html .= ($hideshipperid == false)
						? '<option value="' . $res['shipperid'] . '" selected="selected">' . $res['carrier'] . ' ' . handle_input_keywords($res['title']) . '</option>'
						: '<option value="' . $res['carrier'] . ' ' . handle_input_keywords($res['title']) . '" selected="selected">' . $res['carrier'] . ' ' . handle_input_keywords($res['title']) . '</option>';
				}
				else
				{
					$html .= ($hideshipperid == false)
						? '<option value="' . $res['shipperid'] . '">' . $res['carrier'] . ' ' . handle_input_keywords($res['title']) . '</option>'
						: '<option value="' . $res['carrier'] . ' ' . handle_input_keywords($res['title']) . '">' . $res['carrier'] . ' ' . handle_input_keywords($res['title']) . '</option>';
				}
                        }
                        $html .= '</select>';
                }
		$ilance->template->templateregistry['shipping_partners'] = $html;
		return $ilance->template->parse_template_phrases('shipping_partners');
        }
	
	/**
        * Function to print the list of shipping packages
        *
        * @param       string      fieldname
        * @param       string      package identifier
        * @param       boolean     pull down menu disabled (default false)
        * @param       integer     shipper id
        * @param       string      width of the pull down menu (in pixels) default 150
        *
        * @return      string      HTML representation of our pull down menu  
        */
        function print_shipping_packages($fieldname = 'ship_packagetype', $packageid = '', $disabled = 'false', $shipperid = 0, $width = '150')
        {
                global $ilance, $phrase, $ilconfig;
                $html = '<select name="' . $fieldname . '" id="' . $fieldname . '"' . ($disabled == 'true' ? ' disabled="disabled"' : '') . ' class="select" style="width:' . $width . 'px">';
		$sql = $ilance->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "shipcode, carrier
                        FROM " . DB_PREFIX . "shippers
			WHERE shipperid = '" . intval($shipperid) . "'
			LIMIT 1
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$packages = $ilance->shipcalculator->packagetypes($res['carrier'], $res['shipcode']);
                        if (is_array($packages) AND count($packages > 0))
			{
				foreach ($packages AS $key => $value)
				{
					if (!empty($packageid) AND $key == $packageid)
					{
						$html .= '<option value="' . $key . '" selected="selected">' . handle_input_keywords($value) . '</option>';
					}
					else
					{
						$html .= '<option value="' . $key . '">' . handle_input_keywords($value) . '</option>';
					}
				}
			}
			else
			{
				return '';
			}
                }
		else
		{
			$html .= '<option value="">None</option>';
		}
		$html .= '</select>';
                return $html;
        }
	
	/**
        * Function to print the list of shipping pickup and drop off choices
        *
        * @param       string      fieldname
        * @param       string      pickup type identifier
        * @param       boolean     pull down menu disabled (default false)
        * @param       integer     shipper id
        * @param       string      width of the pull down menu (in pixels) default 150
        *
        * @return      string      HTML representation of our pull down menu  
        */
        function print_shipping_pickupdropoff($fieldname = 'ship_pickuptype', $pickupid = '', $disabled = false, $shipperid = 0, $width = '150')
        {
                global $ilance, $phrase, $ilconfig;
                $html = '<select name="' . $fieldname . '" id="' . $fieldname . '"' . ($disabled == 'true' ? ' disabled="disabled"' : '') . ' class="select" style="width:' . $width . 'px">';
		$sql = $ilance->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "shipcode, carrier
                        FROM " . DB_PREFIX . "shippers
			WHERE shipperid = '" . intval($shipperid) . "'
			LIMIT 1
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$pickups = $ilance->shipcalculator->pickuptypes($res['carrier']);
			if (is_array($pickups) AND count($pickups > 0))
			{
				foreach ($pickups AS $key => $value)
				{
					if (!empty($pickupid) AND $key == $pickupid)
					{
						$html .= '<option value="' . $key . '" selected="selected">' . handle_input_keywords($value) . '</option>';
					}
					else
					{
						$html .= '<option value="' . $key . '">' . handle_input_keywords($value) . '</option>';
					}
				}
			}
			else
			{
				return '';
			}
                }
		else
		{
			$html .= '<option value="">None</option>';
		}
		$html .= '</select>';
                return $html;
        }
	
        /**
        * Function to fetch the number of filters available for displaying within the auction posting interface.
        *
        * @param       integer      category id   
        *
        * @return      integer      return the count amount
        */
        function get_filters_quantity($cid = 0)
        {
                global $ilance, $show;
                $qty = 0;
                $show['advanced_profile_filters'] = false;
        	$sql = $ilance->db->query("
                        SELECT questionid
                        FROM " . DB_PREFIX . "profile_questions
                        WHERE filtercategory = '" . intval($cid) . "'
                                AND visible = '1'
                                AND isfilter = '1'
                ");
                if ($ilance->db->num_rows($sql) > 0)
                {
                	$qty = 1;
                        $show['advanced_profile_filters'] = true;
                }
		return $qty;
        }
        
        /**
        * Function to print the auction preview
        *
        * @return      string      
        */
        function print_auction_preview()
        {
                global $ilance;
                $html = '<div style="padding-bottom:7px"><iframe name="preview_iframe" id="preview_iframe" width="99.5%" scrolling-bottom="yes" border="0" frameborder="0" class="" style="height:320px; border-top:1px solid blue; border-left:1px solid blue; border-right:1px solid blue; border-bottom:1px solid blue" src="' . AJAXURL  . '?do=previewlisting&mode=service"></iframe></div>';
                $html = '';
                return $html;
        }
        
        /**
        * Function to print the javascript on this form
        *
        * @return      string      
        */
        function print_js($cattype = '', $isbulkupload = false)
        {
                global $ilconfig, $show, $phrase, $onload, $ilance;
                $cid = ($isbulkupload == false) ? intval($ilance->GPC['cid']) : 0;
                $js_start = '';
                if ($cattype == 'service')
                {
                	$sql = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "project_questions
				WHERE cid = '" . $cid . "'
					AND visible = '1'
					AND required = '1'
			");
                	$js_start = '
<script type="text/javascript">
<!--
function category_question()
{
';
                	if ($ilance->db->num_rows($sql));
                	{
                		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                		{
                			$js_start .= 'if (fetch_js_object(\'' . $res['formname'] . '\'))
	{
		if (fetch_js_object(\'' . $res['formname'] . '\').value == \'\')
		{
			alert_js(phrase[\'_please_fill_all_required_category_questions\']);
			return(false);
		}
	}
';
                		}
                	}
                	
                	$js_start .= '
	return(true);
}
function validateCB(theName)
{
	var counter = 0;
	var cb = document.getElementsByName(theName)
	for (i=0; i<cb.length; i++)
	{
		if ((cb[i].tagName == \'INPUT\') && (cb[i].type == \'checkbox\'))
		{
			if (cb[i].checked)
			counter++;
		}
	}	
	if (counter == 0)
	{  
		return false;
	}	
	return true;
}
function validate_title(f)
{
        haveerrors = 0;        
        if (fetch_js_object(\'project_title\').value == \'\')
        {
                haveerrors = 1;                
                showImage("project_titleerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true);
                alert_js(phrase[\'_please_enter_a_title_for_this_listing\']);
                return(false);
        }
        else
        {
                showImage("project_titleerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);                
                return(true);
        }
}
function validate_message()
{
        fetch_bbeditor_data();
        if (fetch_js_object(\'description_id\').value == \'\')
        {
                alert_js(phrase[\'_please_enter_a_description_for_your_listing\']);
                return(false);        
        }
        return(true);
}
function validate_payment_method()
{
	var total = \'\';
	var total2 = \'\';
	if (fetch_js_object(\'enableescrow2\')) 
	{
		if (fetch_js_object(\'enableescrow2\').checked == true) 
		{
			if (!validateCB(\'paymethod[]\'))
			{
				alert_js(phrase[\'_you_have_selected_that_you_will_do_business_outside_the_marketplace\']);
				return(false);
			}
			total = \'1\';
		}
	}
	if (fetch_js_object(\'enableescrow1\'))
	{
		if (fetch_js_object(\'enableescrow1\').checked == true)
		{ 
			total2 = \'1\';
		}
	}
	if (total == \'\' && total2 == \'\')
	{
		alert_js(phrase[\'_in_order_for_providers_to_know_how_you_will_pay_them_for_services\']);
		return(false);	
	} 
	
	return(true);
}
function validate_all()
{	
        return validate_title() && validate_message() && validatecustomform() && validate_payment_method() && category_question();
}
//-->
</script>';        
                }
                else if ($cattype == 'product')
                {
			$sql = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "product_questions
				WHERE cid = '" . $cid . "'
					AND visible = '1'
					AND required = '1'
			");
			$check_attachment = ($ilconfig['attachment_forceproductupload'] == 1) ? ' && validate_attachment()' : ''; 
                	$js_start = '<script type="text/javascript">
<!--
function category_question()
{
';
                	if ($ilance->db->num_rows($sql));
                	{
                		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                		{
                			$js_start .= 'if (fetch_js_object(\'' . $res['formname'] . '\'))
	{
		if (fetch_js_object(\'' . $res['formname'] . '\').value == \'\')
		{
			alert_js(phrase[\'_please_fill_all_required_category_questions\']);
			return(false);
		}
	}
';
                		}
                	}
                	$js_start .= '
	return(true);
}
function validateCB(theName)
{
	var counter = 0;
	var cb = document.getElementsByName(theName)
	for (i=0; i<cb.length; i++)
	{
		if ((cb[i].tagName == \'INPUT\') && (cb[i].type == \'checkbox\'))
		{
			if (cb[i].checked)
			counter++;
		}
	}	
	if (counter == 0)
	{  
		return false;
	}	
	return true;
}
function validate_title()
{
        haveerrors = 0;        
        if (fetch_js_object(\'project_title\').value == \'\')
        {
                haveerrors = 1;                
                showImage("project_titleerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true);
                alert_js(phrase[\'_please_enter_a_title_for_this_listing\']);
                return(false);
        }
        else
        {
                showImage("project_titleerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);                
                return(true);
        }
}
function validate_message()
{
	if (typeof fetch_bbeditor_data === \'function\')
	{
		fetch_bbeditor_data();
		if (fetch_js_object(\'description_id\').value == \'\')
		{
			alert_js(phrase[\'_please_enter_a_description_for_your_listing\']);
			return(false);        
		}
	}
	else
	{
		CKEDITOR.instances[\'description_id\'].updateElement();
		var editor_val = CKEDITOR.instances[\'description_id\'].getData();
		if (editor_val == \'\')
		{
			alert_js(phrase[\'_please_enter_a_description_for_your_listing\']);
			return(false);
		}
	}
        return(true);
}
function validate_selling_format()
{
        var Chars = "0123456789.,";
        haveerrors = 0;       
        if (fetch_js_object(\'public\').checked || fetch_js_object(\'realtime\').checked)
        {
        	if (fetch_js_object(\'filtered_auctiontype\'))
        	{
			if (fetch_js_object(\'filtered_auctiontype\').checked && fetch_js_object(\'filtered_auctiontype\').value == \'regular\') 
			{
				if (fetch_js_object(\'startprice\').value == \'\' || fetch_js_object(\'startprice\').value <= 0)
				{
					alert_js(phrase[\'_you_must_enter_the_starting_bid_price_for_your_item\']);
					return(false);
				}    
				if (fetch_js_object(\'startprice\').value > 0 && fetch_js_object(\'reserve_price\').value != \'\' && fetch_js_object(\'reserve_price\').value > 0)
				{
					if (parseFloat(fetch_js_object(\'startprice\').value) >= parseFloat(fetch_js_object(\'reserve_price\').value))
					{
						alert_js(phrase[\'_your_reserve_price_cannot_be_less_or_equal\']);
						return(false);
					}
				}   
				if (fetch_js_object(\'buynow_price\').value != \'\' && fetch_js_object(\'buynow_price\').value <= 0)
				{
					alert_js(phrase[\'_you_entered_a_buy_now_price_but_the_value_must_be_a_penny_or_more\']);
					return(false);
				}
				if (parseFloat(fetch_js_object(\'buynow_price\').value) > 0 && parseFloat(fetch_js_object(\'reserve_price\').value) != \'\' && parseFloat(fetch_js_object(\'reserve_price\').value) > parseFloat(fetch_js_object(\'buynow_price\').value))
				{
					alert_js(phrase[\'_your_reserve_price_cannot_exceed_your_buy_now_price\']);
					return(false);
				}
				if (parseFloat(fetch_js_object(\'buynow_price\').value) != \'\' && parseFloat(fetch_js_object(\'buynow_price\').value) > 0 && parseFloat(fetch_js_object(\'startprice\').value) != \'\' && parseFloat(fetch_js_object(\'startprice\').value) > 0 && parseFloat(fetch_js_object(\'buynow_price\').value) < parseFloat(fetch_js_object(\'startprice\').value))
				{
					alert_js(phrase[\'_your_buy_now_price_cannot_be_less_or_equal_to_the_starting_bid_amount\']);
					return(false);
				}
				if (fetch_js_object(\'buynow_qty_lot_regular\').value == \'1\' && (fetch_js_object(\'items_in_lot_regular\').value == \'\' || fetch_js_object(\'items_in_lot_regular\').value < 1) )
				{
					alert_js(phrase[\'_if_you_selected_lot_you_have_to_define_how_many_items_is_in_lot\']);
					return(false);
				}
				for (var i = 0; i < fetch_js_object(\'startprice\').value.length; i++)
				{
					if (Chars.indexOf(fetch_js_object(\'startprice\').value.charAt(i)) == -1)
					{
						alert_js(phrase[\'_invalid_currency_characters_only_numbers_and_a_period_are_allowed_in_this_field\']);
						haveerrors = 1;
					}
				}   
				return(!haveerrors);                                
			}
		}   
		if (fetch_js_object(\'filtered_auctiontype0\'))
		{    
			if (fetch_js_object(\'filtered_auctiontype0\').checked && fetch_js_object(\'filtered_auctiontype0\').value == \'fixed\') 
			{
				if (fetch_js_object(\'buynow_price_fixed\').value == \'\' || fetch_js_object(\'buynow_price_fixed\').value <= \'0\' || parseFloat(fetch_js_object(\'buynow_price_fixed\').value) <= \'0\')
				{
					alert_js(phrase[\'_you_must_enter_a_buy_now_price_for_your_item\']);
					return(false);
				}                        
				if (fetch_js_object(\'buynow_qty_fixed\').value == \'\' || fetch_js_object(\'buynow_qty_fixed\').value <= \'0\' || parseInt(fetch_js_object(\'buynow_qty_fixed\').value) <= \'0\')
				{
					alert_js(phrase[\'_you_must_enter_the_qty_available_for_your_buy_now_item\']);
					return(false);
				} 
				if (fetch_js_object(\'buynow_qty_lot_fixed\').value == \'1\' && (fetch_js_object(\'items_in_lot_fixed\').value == \'\' || fetch_js_object(\'items_in_lot_fixed\').value < 1) )
				{
					alert_js(phrase[\'_if_you_selected_lot_you_have_to_define_how_many_items_is_in_lot\']);
					return(false);
				}                       
				for (var i = 0; i < fetch_js_object(\'buynow_price_fixed\').value.length; i++)
				{
					if (Chars.indexOf(fetch_js_object(\'buynow_price_fixed\').value.charAt(i)) == -1)
					{
						alert_js(phrase[\'_invalid_currency_characters_only_numbers_and_a_period_are_allowed_in_this_field\']);
						haveerrors = 1;
					}
				}                        
				return(!haveerrors);
			}
		}
        }
';
$js_start .= 'return(true);
}
function validate_bid_filters()
{	
        if (fetch_js_object(\'filter_city\').checked && fetch_js_object(\'filtered_city\').value == \'\') 
        {
                alert_js(phrase[\'_you_currently_have_city_town_filter_enabled_checkbox_is_on\']);
                return(false);
        }       
        if (fetch_js_object(\'filter_zip\').checked && fetch_js_object(\'filtered_zip\').value == \'\') 
        {
                alert_js(phrase[\'_you_currently_have_zip_postal_code_filter_enabled_checkbox_is_on\']);
                return(false);
        }        
        return(true);
}
function validate_shipping()
{
';
	$js_start .= '
	if (fetch_js_object(\'ship_method\').value == \'flatrate\')
	{';	
	for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
	{
		$js_start .= '
		if (fetch_js_object(\'ship_method_service_options_' . $i . '\').style.display == \'\')
		{
			if (fetch_js_object(\'ship_options_' . $i . '\').value == \'\')
			{
				alert_js(phrase[\'_you_selected_flat_rate_shipping_but_forgot_to_include_locations_where\']);
				return(false);
			}			
			if (fetch_js_object(\'ship_options_' . $i . '\').value == \'custom\')
			{
				if (fetch_js_object(\'ship_options_custom_region_' . $i . '_1\').checked == false && fetch_js_object(\'ship_options_custom_region_' . $i . '_2\').checked == false && fetch_js_object(\'ship_options_custom_region_' . $i . '_3\').checked == false && fetch_js_object(\'ship_options_custom_region_' . $i . '_4\').checked == false && fetch_js_object(\'ship_options_custom_region_' . $i . '_5\').checked == false && fetch_js_object(\'ship_options_custom_region_' . $i . '_6\').checked == false && fetch_js_object(\'ship_options_custom_region_' . $i . '_7\').checked == false)
				{
					alert_js(phrase[\'_you_selected_custom_locations_for_shipping_service\'] + ' . $i . ' + phrase[\'_but_did_not_select_any_location_please_use_the_checkboxes\']);
					return(false);
				}
			}
			if (fetch_js_object(\'ship_service_' . $i . '\').value == \'0\' || fetch_js_object(\'ship_service_' . $i . '\').value == \'\')
			{
				alert_js(phrase[\'_you_selected_flat_rate_shipping_but_did_not_select_any_shipping_service\']);
				return(false);
			}
			if (fetch_js_object(\'freeshipping_' . $i . '\').checked == false && parseFloat(fetch_js_object(\'ship_fee_' . $i . '\').value) <= 0 || fetch_js_object(\'freeshipping_' . $i . '\').checked == false && fetch_js_object(\'ship_fee_' . $i . '\').value == \'\')
			{
				alert_js(phrase[\'_you_selected_flat_rate_shipping_but_did_not_include_your_shipping_cost\']);
				return(false);
			}
			if (fetch_js_object(\'freeshipping_' . $i . '\').checked == false && fetch_js_object(\'ship_fee_next_' . $i . '\') && fetch_js_object(\'filtered_auctiontype0\').checked && fetch_js_object(\'buynow_qty_fixed\').value > 1 && parseFloat(fetch_js_object(\'ship_fee_next_' . $i . '\').value) < 0 || fetch_js_object(\'freeshipping_' . $i . '\').checked == false && fetch_js_object(\'ship_fee_next_' . $i . '\') && fetch_js_object(\'filtered_auctiontype0\').checked && fetch_js_object(\'buynow_qty_fixed\').value > 1 && fetch_js_object(\'ship_fee_next_' . $i . '\').value == \'\')
			{
				alert_js(phrase[\'_you_selected_flat_rate_shipping_but_did_not_include_your_shipping_cost\']);
				return(false);
			}
		}
		';
	}
	$js_start .= '
	}
	else if (fetch_js_object(\'ship_method\').value == \'calculated\')
	{
	}
	else if (fetch_js_object(\'ship_method\').value == \'localpickup\')
	{	
	}
	else if (fetch_js_object(\'ship_method\').value == \'digital\')
	{
		if (fetch_js_object(\'digital_attachmentlist\').innerHTML == \'\')
		{
			alert_js(phrase[\'_you_decided_the_method_to_ship_is_digital_but_no_upload_was_found\']);
			return(false);
		}
	}
	return(true);
}
function validate_return_policy()
{
        if (fetch_js_object(\'returnaccepted1\').checked == true && fetch_js_object(\'returnwithin\').value <= \'0\') 
        {
                alert_js(phrase[\'_you_have_enabled_a_return_policy_for_this_listing_but_did_not_specify\']);
                return(false);
        }        
        if (fetch_js_object(\'returnaccepted1\').checked == true && fetch_js_object(\'returngivenas\').value == \'\') 
        {
                alert_js(phrase[\'_you_have_enabled_a_return_policy_for_this_listing_but_did_not_specify_the_refund\']);
                return(false);
        }        
        if (fetch_js_object(\'returnaccepted1\').checked == true && fetch_js_object(\'returnpolicy\').value == \'\') 
        {
                alert_js(phrase[\'_you_have_enabled_a_return_policy_for_this_listing_but_did_not_provide_your_return_policy_instruction\']);
                return(false);
        }        
        return(true);
}
function validate_payment_method(formobj)
{
	var total = \'\';
	var total2 = \'\';
	var total3 = \'\';	
	var total4 = \'\';	
	var e = 0;
	if (fetch_js_object(\'enableescrow2\'))
	{	
		if (fetch_js_object(\'enableescrow2\').checked == true)
		{		
			if (!validateCB(\'paymethod[]\'))
			{
				alert_js(phrase[\'_you_have_selected_that_you_will_do_business_outside_the_marketplace\']);
				return(false);
			}
			total = \'1\';
		}	
	}
	if (fetch_js_object(\'enableescrow3\'))
	{
		if (fetch_js_object(\'enableescrow3\').checked == true)
		{
			formobj = formobj.elements;
			for (var c = 0, i = formobj.length - 1; i > -1; --i)
			{
				if (formobj[i].name && /^paymethodoptions\[\w+\]$/.test(formobj[i].name) && formobj[i].checked)
				{
					++c;
					if (formobj[i].name == \'paymethodoptions[platnosci]\')
					{
						e++;
					}
				}
			}
			if (c < 1)
			{
				alert_js(phrase[\'_you_have_selected_that_you_will_offer_buyers_a_direct_method_of_payment\']);
				return(false);
			}
			for (var d = e, i = formobj.length - 1; i > -1; --i)
			{
				if (formobj[i].name && /^paymethodoptionsemail\[\w+\]$/.test(formobj[i].name) && formobj[i].value != \'\')
				{
					++d;
				}
			}
			if (d != c)
			{
				alert_js(phrase[\'_you_are_offering_direct_payment_gateway_for_buyers_but_did_not_enter\']);
				return(false);
			}		
			total2 = \'1\';
		}	
	}
	';
	$js_start .= ($ilconfig['escrowsystem_enabled'] == '0') ? '' : 'if (fetch_js_object(\'enableescrow1\').checked == true)
	{
		total3 = \'1\';
	}	
	';
	$js_start .= ($ilconfig['paypal_pro_directpayment'] == '1' AND $ilconfig['use_internal_gateway'] == 'paypal_pro' AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1') ? ' 
	if (fetch_js_object(\'enableescrow4\').checked == true)
	{
		total4 = \'1\';
	}	
	' : '';
	$js_start .= 'if (total == \'\' && total2 == \'\' && total3 == \'\' && total4 == \'\')
	{
		alert_js(phrase[\'_in_order_to_sell_your_items_sucessfully_buyers_will_need_to_know_how\']);
		return(false);	
	}        
        return(true);
}
function validate_attachment()
{
	if ($(\'#fileuploader_iframe\').contents().find(\'tbody.files tr.template-download td.preview\').html() == undefined)
	{
	    alert_js(phrase[\'_please_upload_atleast_one_image\']);
	    return (false);
	}	
	return(true);
}
function validate_all(formobj)
{
	return validatecustomform() && validate_selling_format() && validate_payment_method(formobj)'  . $check_attachment . ' && validate_title() && validate_message() && validate_shipping() && validate_bid_filters() && validate_return_policy();
}
function validate_all_bulk(formobj)
{	
        return validate_payment_method() && validate_shipping() && validate_bid_filters() && validate_return_policy() && disable_submit_button(formobj, \'{_your_action_is_being_processed_allow_some_time}\', 1, \'working_icon\');
}
//-->
</script>
';        
                }
                return $js_start;
        }
        
        /**
        * Function to handle the revision log changes made to an auction event
        *
        * @param       string        category type (service or product)
        * @param       string        extra data to append
        *
        * @return      string      
        */
        function handle_revision_log_changes($cattype = 'service', $appendextra = '')
        {
                global $ilance, $phrase;
                $slng = $_SESSION['ilancedata']['user']['slng'];
                $fieldphrases = array(
                        'project_title' => '{_title}',
                        'description' => '{_description}',
                        'additional_info' => '{_additional_information}',
                        'keywords' => '{_keywords}',
                        'bold' => '{_bold}',
                        'highlite' => '{_listing_highlight}',
                        'featured' => '{_featured}',
                        'featured_searchresults' => '{_featured_in_search_results}',
                        'filter_escrow' => '{_escrow}',
                        'paymethod' => '{_offline_payment}',
                        'paymethodoptions' => '{_direct_payment}',
                        'project_details' => '{_event_access}',
                        'bid_details' => '{_bidding_privacy}',
                        'filter_publicboard' => '{_public_message_board}',
                        'filtered_budgetid' => '{_budget}',
                        'filtered_auctiontype' => '{_selling_format}',
                        'reserve' => '{_reserve}',
                        'reserve_price' => '{_reserve_price}',
                        'startprice' => '{_starting_price}',
                        'buynow_price_fixed' => '{_buy_now_price}',
                        'buynow_qty' => '{_qty}',
                        'buynow_price' => '{_buy_now_price}',
                        'buynow_qty_fixed' => '{_qty}',
                        'returngivenas' => '{_refund_will_be_provided_as}',
                        'returnaccepted' => '{_returns_accepted}',
                        'returnwithin' => '{_item_must_be_returned_within}',
                        'returnshippaidby' => '{_return_shipping_paid_by}',
                        'returnpolicy' => '{_return_policy}',
                        'buynow_qty_lot' => '{_qty}',
                        'attachmentlist' => '{_attachments}',
                        'cid' => '{_category}'
                );
                if ($cattype == 'service')
                {
                        $updatefields = array('cid','attachmentlist','custom','project_title','description','additional_info','keywords','bold','highlite','featured','filter_escrow','paymethod','project_details','bid_details','filter_publicboard','filtered_budgetid');
                }
                else if ($cattype == 'product')
                {
                        $updatefields = array('cid','attachmentlist','custom','startprice','reserve','reserve_price','buynow_price_fixed','buynow_price','buynow_qty_fixed','buynow_qty','project_title','description','keywords','bold','highlite','featured','featured_searchresults','filter_escrow','paymethod','paymethodoptions','project_details','bid_details','filter_publicboard','filtered_auctiontype','returnaccepted','returnwithin','returngivenas','returnshippaidby','returnpolicy','buynow_qty_lot');
                }
                $log = '';
                foreach ($updatefields AS $key)
                {
                	if ($cattype == 'product')
                	{
				if ($ilance->GPC['filtered_auctiontype'] == 'fixed' AND ($key == 'reserve_price' OR $key == 'reserve' OR $key == 'startprice' OR $key == 'buynow_price' OR $key == 'buynow_qty'))
				{
					continue;
				}
                	}
			if ($key == 'description')
			{
	                        if (isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC['description']) AND trim($ilance->GPC['old'][$key]) != trim($ilance->GPC['description']))
	                        {
	                                $log .= '<div><strong>' . $fieldphrases[$key] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray">' . $ilance->bbcode->bbcode_to_html($ilance->GPC['old'][$key]) . '</span> <br /><br />{_to_upper}: ' . $ilance->bbcode->bbcode_to_html($ilance->GPC['description']) . '</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
	                        }
			}
			else if ($key == 'cid')
			{
	                        if (isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC[$key]) AND $ilance->GPC['old'][$key] != $ilance->GPC[$key])
	                        {
		                        $new_category = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $ilance->GPC[$key]);
		                        $old_category = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $ilance->GPC['old'][$key]);
	                                $log .= '<div><strong>' . $fieldphrases[$key] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray">' . $old_category . '</span> <br /><br />{_to_upper}: ' . $new_category . '</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
	                        }
			}
			else if ($key == 'attachmentlist')
			{
				if (!isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC[$key]) AND !empty($ilance->GPC[$key]))
				{
					$log .= '<div><strong>' . $fieldphrases[$key] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray">{_no_attachments_available}</span> {_to_upper}: ' . $ilance->GPC[$key] . '</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
				}
				else if (isset($ilance->GPC['old'][$key]) AND (!isset($ilance->GPC[$key]) OR empty($ilance->GPC[$key])))
				{
					$log .= '<div><strong>' . $fieldphrases[$key] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray">' . $ilance->GPC['old'][$key] . '</span> {_to_upper}: {_no_attachments_available}</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
				}
				else if (isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC[$key]) AND $ilance->GPC['old'][$key] != $ilance->GPC[$key])
				{
					$log .= '<div><strong>' . $fieldphrases[$key] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray">' . $ilance->GPC['old'][$key] . '</span> {_to_upper}: ' . $ilance->GPC[$key] . '</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
				}
							
			}
			else if ($key == 'buynow_price')
			{
				if (isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC['buynow_price']))
				{
					$ilance->GPC['buynow_price'] = (empty($ilance->GPC['buynow_price'])) ? '0.00' : $ilance->GPC['buynow_price'];
					if ($ilance->GPC['old'][$key] != $ilance->GPC['buynow_price'])
					{
						$log .= '<div><strong>' . $fieldphrases[$key] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray">' . $ilance->GPC['old'][$key] . '</span> {_to_upper}: ' . $ilance->GPC['buynow_price'] . '</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
					}
				}
			}
			else if ($key == 'buynow_price_fixed')
			{
				if (isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC['buynow_price_fixed']))
				{
					$ilance->GPC['buynow_price_fixed'] = (empty($ilance->GPC['buynow_price_fixed'])) ? 0.00 : $ilance->GPC['buynow_price_fixed'];
					if ($ilance->GPC['old'][$key] != $ilance->GPC['buynow_price_fixed'])
					{
						$log .= '<div><strong>' . $fieldphrases[$key] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray">' . $ilance->GPC['old'][$key] . '</span> <br /><br />{_to_upper}: ' . $ilance->GPC['buynow_price_fixed'] . '</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
					}
				}
			}
			else if ($key == 'buynow_qty_fixed')
			{
				if (isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC['buynow_qty']) AND $ilance->GPC['old'][$key] != $ilance->GPC['buynow_qty'])
				{
					$log .= '<div><strong>' . $fieldphrases[$key] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray">' . $ilance->GPC['old'][$key] . '</span> {_to_upper}: ' . $ilance->GPC['buynow_qty'] . '</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
				}
			}
			else if ($key == 'filter_escrow')
			{
				if (isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC[$key]) AND $ilance->GPC['old'][$key] != $ilance->GPC[$key])
				{
					$log .= '<div><strong>' . $fieldphrases[$key] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray">' . print_boolean($ilance->GPC['old'][$key]) . '</span> {_to_upper}: ' . print_boolean($ilance->GPC[$key]) . '</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
				}        
			}
			else if ($key == 'filter_publicboard')
			{
				if (isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC[$key]) AND $ilance->GPC['old'][$key] != $ilance->GPC[$key])
				{
					$log .= '<div><strong>' . $fieldphrases[$key] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray">' . print_boolean($ilance->GPC['old'][$key]) . '</span> {_to_upper}: ' . print_boolean($ilance->GPC[$key]) . '</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
				}        
			}
                	else if ($key == 'returnwithin')
			{
				if (isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC[$key]) AND $ilance->GPC['old'][$key] != $ilance->GPC[$key])
				{
					$log .= '<div><strong>' . $fieldphrases[$key] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray">' . intval($ilance->GPC['old'][$key]) . ' {_days}</span> {_to_upper}: ' . intval($ilance->GPC[$key]) . ' {_days}</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
				}        
			}
			else if ($key == 'returngivenas' OR $key == 'returnshippaidby')
			{
				if (isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC[$key]) AND $ilance->GPC['old'][$key] != $ilance->GPC[$key])
				{
					if($ilance->GPC['old'][$key] != 'none' AND $ilance->GPC[$key] != '')
					{
						$log .= '<div><strong>' . $fieldphrases[$key] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray">' . $ilance->GPC['old']["$key"] . '</span> {_to_upper}: ' . $ilance->GPC["$key"] . '</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
					}
				}        
			}                        
			else if ($key == 'returnaccepted')
			{
				if (isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC[$key]) AND $ilance->GPC['old'][$key] != $ilance->GPC[$key])
				{
					$log .= '<div><strong>' . $fieldphrases[$key] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray">' . print_boolean($ilance->GPC['old']["$key"]) . '</span> {_to_upper}: ' . print_boolean($ilance->GPC["$key"]) . '</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
				}        
			}
			else if ($key == 'reserve_price')
			{
				if (isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC[$key]) AND $ilance->currency->format($ilance->GPC['old'][$key]) != $ilance->currency->format($ilance->GPC[$key]))
				{
					$log .= '<div><strong>' . $fieldphrases[$key] . '</strong></div><div style="padding-top:3px"></div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
				}        
			}
			else if ($key == 'buynow_qty_lot')
			{
				if (isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC[$key]) AND $ilance->GPC['old'][$key] != $ilance->GPC[$key])
				{
					$opt1 = $ilance->GPC['old'][$key] == '1' ? '{_lot}' : '{_item}';
					$opt2 = $ilance->GPC[$key] == '1' ? '{_lot}' : '{_item}'; 
					$log .= '<div><strong>' . $fieldphrases[$key] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray">' . $opt1 . '</span> {_to_upper}: ' . $opt2 . '</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
				}        
			}
			else if ($key == 'filtered_budgetid')
			{
				if (isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC[$key]) AND $ilance->GPC['old'][$key] != $ilance->GPC[$key])
				{
                            		if ($ilance->GPC['old'][$key] == '0')
                            		{
                            			$opt1 = '{_i_prefer_not_to_disclose_my_budget}';
                            		}
                            		else 
                            		{
                            			$sql1 = $ilance->db->query("SELECT title FROM " . DB_PREFIX . "budget WHERE budgetid = '" . $ilance->GPC['old'][$key] . "'");
                            			if($ilance->db->num_rows($sql1) > 0)
                            			{
	                            			$res1 = $ilance->db->fetch_array($sql1, DB_ASSOC);
	                            			$opt1 = $res1['title'];
                            			}
                            			else 
                            			{
                            				$opt1 = '';
                            			}
                            		}
                            		if ($ilance->GPC[$key] == '0')
                            		{
                            			$opt2 = '{_i_prefer_not_to_disclose_my_budget}';
                            		}
                            		else 
                            		{
                            			$sql2 = $ilance->db->query("SELECT title FROM " . DB_PREFIX . "budget WHERE budgetid = '" . $ilance->GPC[$key] . "'");
                            			if($ilance->db->num_rows($sql2) > 0)
                            			{
	                            			$res2 = $ilance->db->fetch_array($sql2, DB_ASSOC);
	                            			$opt2 = $res2['title'];
                            			}
                            			else 
                            			{
                            				$opt2 = '';
                            			}
                            		}
					$log .= '<div><strong>' . $fieldphrases[$key] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray">' . $opt1 . '</span> {_to_upper}: ' . $opt2 . '</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
				}        
			}
			else if ($key == 'paymethodoptions')
			{
				if (isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC[$key]) AND $ilance->GPC['old'][$key] != $ilance->GPC[$key])
				{
					$old = (!is_array($ilance->GPC['old'][$key]) AND is_serialized($ilance->GPC['old'][$key])) ? unserialize($ilance->GPC['old'][$key]) : $ilance->GPC['old'][$key];
					$opt_old = $opt = '';
					if(is_array($old))
					{
						foreach ($old AS $key2 => $value)
						{
							$value = '{_' . $key2 . '}';
							$opt_old .= (empty($opt_old)) ? '{_' . $key2 . '}' : ', {_' . $key2 . '}';
						}
					}
					else
					{
						$old = '{_' . $old . '}';
						$opt_old .= (empty($opt_old)) ? '{_' . $old . '}' : ', {_' . $old . '}';
					}
					$new = (is_serialized($ilance->GPC[$key])) ? unserialize($ilance->GPC[$key]) : $ilance->GPC[$key];
					if (is_array($new))
					{
						foreach ($new AS $key2 => $value)
						{
							$value = '{_' . $key2 . '}';
							$opt .= (empty($opt)) ? '{_' . $key2 . '}' : ', {_' . $key2 . '}';
						}
					}
					else
					{
						$new = '{_' . $new . '}';
						$opt .= (empty($opt)) ? '{_' . $new . '}' : ', {_' . $new . '}';
					}
					$log .= '<div><strong>' . $fieldphrases[$key] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray">' . (empty($opt_old) ? '{_none}' : $opt_old) . '</span> {_to_upper}: ' . (empty($opt) ? '{_none}' : $opt) . '</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
					break;
				}
			}
			else if ($key == 'paymethod')
			{
				if (isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC[$key]))
				{
					$old = (!is_array($ilance->GPC['old'][$key]) AND is_serialized($ilance->GPC['old'][$key])) ? unserialize($ilance->GPC['old'][$key]) : $ilance->GPC['old'][$key];
					$opt_old = $opt = '';
					if (is_array($old))
					{
						foreach ($old AS $key2 => $value)
						{
							$value = mb_substr($value, 0, 1) == '_' ? '{' . $value . '}' : $value;
            			 			$opt_old .= (empty($opt_old)) ? $value : ', '.$value;
						}
					}
					else
					{
						$old = mb_substr($old, 0, 1) == '_' ? '{' . $old . '}' : $old;
						$opt_old .= (empty($opt_old)) ? $old : ', '. $old;
					}
					$new = (is_serialized($ilance->GPC[$key])) ? unserialize($ilance->GPC[$key]) : $ilance->GPC[$key];
					if (is_array($new))
					{
						foreach ($new AS $key2 => $value)
						{
                		   			$value = mb_substr($value, 0, 1) == '_' ? '{' . $value . '}' : $value;
							$opt .= (empty($opt)) ? $value : ', '.$value;
						}
					}
					else
					{
						$new = mb_substr($new, 0, 1) == '_' ? '{' . $new . '}' : $new;
						$opt .= (empty($opt)) ? $new : ', '. $new;
					}
					for ($i = 0; $i < 10; $i++)
					{
						if (isset($old[$i]))
						{
							if (isset($new[$i]))
							{
								if ($old[$i] != $new[$i])
								{
									$log .= '<div><strong>' . $fieldphrases[$key] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray">' . (empty($opt_old) ? '{_none}' : $opt_old) . '</span> {_to_upper}: ' . (empty($opt) ? '{_none}' : $opt) . '</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
									break;
								}
							}
							else
							{
								$log .= '<div><strong>' . $fieldphrases[$key] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray">' . (empty($opt_old) ? '{_none}' : $opt_old) . '</span> {_to_upper}: ' . (empty($opt) ? '{_none}' : $opt) . '</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
								break;	
							}
						}
						else 
						{
							if (isset($new[$i]))
							{
								$log .= '<div><strong>' . $fieldphrases[$key] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray">' . (empty($opt_old) ? '{_none}' : $opt_old) . '</span> {_to_upper}: ' . (empty($opt) ? '{_none}' : $opt) . '</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
								break;	
							}
							else 
							{
								break;
							}
						}
					}
				}
			}
			else if ($key == 'keywords')
			{
				if (isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC[$key]) AND $ilance->GPC['old'][$key] != $ilance->GPC[$key])
				{
					$log .= '<div><strong>' . $fieldphrases[$key] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray">' . $ilance->GPC['old'][$key] . '</span> {_to_upper}: ' . $ilance->GPC[$key] . '</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
				}        
			}
			else
			{
				if (isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC[$key]) AND $ilance->GPC['old'][$key] != $ilance->GPC[$key])
				{
					$old = $ilance->GPC['old'][$key];
					$new = $ilance->GPC[$key];
					if (($ilance->GPC['old'][$key] == '0' OR $ilance->GPC['old'][$key] == '1') AND ($ilance->GPC[$key] == '0' OR $ilance->GPC[$key] == '1'))
					{
						$old = print_boolean($ilance->GPC['old'][$key]);
						$new = print_boolean($ilance->GPC[$key]);
					}
					$log .= '<div><strong>' . $fieldphrases[$key] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray">' . $old . '</span> {_to_upper}: ' . $new . '</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
				}
			}
                }
                if ($cattype == 'product')
                {
			// handle shipping changes 
	                $ship_fieldphrases = array(
	                        'ship_fee_' => '{_shipping_costs}', 
	                        'ship_options_' => '{_shipto}',
	                        'ship_service_' => '{_shipping_partner}', 
	                        'freeshipping_' => '{_free_shipping}',
	                        'ship_handlingfee' => '{_handling_fee}', 
	                        'ship_handlingtime' => '{_handling_time}', 
	                        'ship_height' => '{_height}',
	                        'ship_length' => '{_length}', 
	                        'ship_method' => '{_method}',
	                        'ship_weightlbs' => '{_package_weight}',
	                        'ship_weightoz' => '{_package_weight}', 
	                        'ship_width' => '{_width}'
	                ); 
	                $ship_updatefields = array('ship_fee_', 'ship_options_', 'ship_service_', 'ship_packagetype_', 'ship_pickuptype_', 'freeshipping_', 'ship_handlingfee', 'ship_handlingtime', 'ship_height', 'ship_length', 'ship_method','ship_weightlbs', 'ship_weightoz', 'ship_width');
	                foreach ($ship_updatefields AS $key)
	                {
	                	if (($key == 'ship_fee_')) 
	                	{
	                		for ($i=1; $i <= 10; $i++)
	                		{
	                			if (isset($ilance->GPC['old'][$key.$i]) AND isset($ilance->GPC[$key.$i]))
						{
							$ilance->GPC[$key.$i] = (empty($ilance->GPC[$key.$i])) ? '0.00' : $ilance->GPC[$key.$i];
							if ($ilance->GPC['old'][$key.$i] != $ilance->GPC[$key.$i])
							{
								$log .= '<div><strong>' . $ship_fieldphrases[$key] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray">' . $ilance->GPC['old'][$key.$i] . '</span> {_to_upper}: ' . $ilance->GPC[$key.$i] . '</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
							}
						}
	                		}
	                	}
	                	else if ($key == 'ship_options_')
	                	{
	                		for ($i=1; $i <= 10; $i++)
	                		{
	                			if (isset($ilance->GPC['old'][$key.$i]) AND isset($ilance->GPC[$key.$i]))
						{
							$ilance->GPC[$key.$i] = (empty($ilance->GPC[$key.$i])) ? '0.00' : $ilance->GPC[$key.$i];
							if ($ilance->GPC['old'][$key.$i] != $ilance->GPC[$key.$i])
							{
								$log .= '<div><strong>' . $ship_fieldphrases[$key] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray">' . $ilance->GPC['old'][$key.$i] . '</span> {_to_upper}: ' . $ilance->GPC[$key.$i] . '</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
							}
						}
	                		}
	                	}
	                	else if ($key == 'ship_service_')
	                	{
	                		for ($i=1; $i<=10; $i++)
	                		{
	                			if (isset($ilance->GPC['old'][$key.$i]) AND isset($ilance->GPC[$key.$i]))
						{
							$ilance->GPC[$key.$i] = (empty($ilance->GPC[$key.$i])) ? '0.00' : $ilance->GPC[$key.$i];
							if ($ilance->GPC['old'][$key.$i] != $ilance->GPC[$key.$i])
							{
								$old_service_sql = $ilance->db->query("SELECT title FROM " . DB_PREFIX . "shippers WHERE shipperid='" . $ilance->GPC['old'][$key.$i] . "'");
								if ($ilance->db->num_rows($old_service_sql) > 0)
								{
									$old_service_res = $ilance->db->fetch_array($old_service_sql);
								}
								else 
								{
									$old_service_res['title'] = '-';
								}
								$new_service_sql = $ilance->db->query("SELECT title FROM " . DB_PREFIX . "shippers WHERE shipperid='" . $ilance->GPC[$key.$i] . "'");
								if ($ilance->db->num_rows($new_service_sql) > 0)
								{
									$new_service_res = $ilance->db->fetch_array($new_service_sql);
								}
								else 
								{
									$new_service_res['title'] = '-';
								}
								$log .= '<div><strong>' . $ship_fieldphrases[$key] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray">' . $old_service_res['title'] . '</span> {_to_upper}: ' . $new_service_res['title'] . '</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
							}
						}
	                		}
	                	}
				else if ($key == 'ship_packagetype_')
	                	{
	                		// todo: package type for revision log
	                	}
				else if ($key == 'ship_pickuptype_')
	                	{
	                		// todo: pickup type for revision log
	                	}
	                	else if ($key == 'freeshipping_')
	                	{
	                		for ($i=1; $i <= 10; $i++)
	                		{
	                			if (isset($ilance->GPC['old'][$key.$i]) AND isset($ilance->GPC[$key.$i]))
						{
							$ilance->GPC[$key.$i] = (empty($ilance->GPC[$key.$i])) ? '0.00' : $ilance->GPC[$key.$i];
							if ($ilance->GPC['old'][$key.$i] != $ilance->GPC[$key.$i])
							{
								$log .= '<div><strong>' . $ship_fieldphrases[$key] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray">' . $ilance->GPC['old'][$key.$i] . '</span> {_to_upper}: ' . $ilance->GPC[$key.$i] . '</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
							}
						}
	                		}
	                	}
	                	else 
	                	{
	                		if (isset($ilance->GPC['old'][$key]) AND isset($ilance->GPC[$key]))
					{
						$ilance->GPC[$key] = (empty($ilance->GPC[$key])) ? '0.00' : $ilance->GPC[$key];
						if ($ilance->GPC['old'][$key] != $ilance->GPC[$key])
						{
							$log .= '<div><strong>' . $ship_fieldphrases[$key] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray">' . $ilance->GPC['old'][$key] . '</span> {_to_upper}: ' . $ilance->GPC[$key] . '</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
						}
					}
	                	}
	                }
                }
                if (isset($ilance->GPC['custom']) AND is_array($ilance->GPC['custom']) AND !empty($ilance->GPC['custom']))
                {
                	$table = ($cattype == 'service') ? 'project' : 'product';
                	$sql_old_custom = $ilance->db->query("SELECT * FROM " . DB_PREFIX . $table . "_answers WHERE project_id = '" . intval($ilance->GPC['rfpid']) . "'");
                	if ($ilance->db->num_rows($sql_old_custom) > 0)
                	{
                		while ($res_old_custom = $ilance->db->fetch_array($sql_old_custom, DB_ASSOC))
                		{
                			$sql_question = $ilance->db->query("SELECT formname, question_$slng, inputtype FROM " . DB_PREFIX . $table . "_questions WHERE questionid = '" . $res_old_custom['questionid'] . "'");
                			$res_question = $ilance->db->fetch_array($sql_question, DB_ASSOC);
                			foreach ($ilance->GPC['custom'] AS $key => $value)
                			{
                				foreach ($value AS $formname => $value2)
                				{
                					if ($formname == $res_question['formname'])
                					{
                						if (is_array($value[$formname]))
                						{
                							if (is_serialized($res_old_custom['answer']))
                							{
                								$answer = unserialize($res_old_custom['answer']);
                								if ($res_question['inputtype'] == 'multiplechoice')
                								{
	                								$before = $now = '';
	                								foreach ($answer AS $answer_key => $answer_value)
	                								{
	                									$before .= (empty($before)) ? $answer_value : ', '.$answer_value; 
	                								}
	                								foreach ($value2 AS $key3 => $value3)
	                								{
	                									$now .= (empty($now)) ? $value3 : ', '.$value3;
	                								}
	                								if ($before != $now)
				                					{
				                						$log .= '<div><strong>' . $res_question['question_' . $slng] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray">' . $before . '</span> {_to_upper}: ' . $now . '</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
				                					}
                								}
                								else 
                								{
                									foreach ($value2 AS $key3 => $value3)
		                							{
		                								foreach ($answer AS $answer_key => $answer_value)
		                								{
		                									if ($value3 != $answer_value)
					                						{
					                							$log .= '<div><strong>' . $res_question['question_' . $slng] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray">' . $answer_value . '</span> {_to_upper}: ' . $value3 . '</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
					                						}
		                								}
		                							}
                								}
                							}
                						}
                						else 
                						{
                							if ($value2 != $res_old_custom['answer'])
                							{
                								$log .= '<div><strong>' . $res_question['question_eng'] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray">' . $res_old_custom['answer'] . '</span> {_to_upper}: ' . $value2 . '</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
                							}
                						}
                					}
                				}
                			}
                		}
                	}
                	else 
                	{
            			foreach ($ilance->GPC['custom'] AS $key => $value)
            			{
            				foreach ($value AS $formname => $value2)
            				{
            					$sql_question = $ilance->db->query("SELECT formname, question_$slng as question, inputtype FROM " . DB_PREFIX . $table . "_questions WHERE formname = '" . $ilance->db->escape_string($formname) . "'");
            					$res_question = $ilance->db->fetch_array($sql_question, DB_ASSOC);

    							if (is_array($value2))
    							{
								$before = $now = '';
								foreach ($value2 AS $key3 => $value3)
								{
									$now .= (empty($now)) ? $value3 : ', '.$value3;
								}
                						$log .= '<div><strong>' . $res_question['question'] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray"></span> {_to_upper}: ' . $now . '</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
    							}
            					else 
            					{
            						$log .= '<div><strong>' . $res_question['question'] . '</strong></div><div style="padding-top:3px">{_from}: <span class="gray"></span> {_to_upper}: ' . $value2 . '</div><div style="height:1px;background-color:#cccccc;width:100%;margin-top:12px;margin-bottom:12px"></div>';
            					}
            				}
            			}
                	}
                }
                if (!empty($log))
                {
                        // remove trailing <hr>
                        $log = mb_substr($log, 0, -101);
                        $ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "projects_changelog
                                (id, project_id, datetime, changelog)
                                VALUES(
                                NULL,
                                '" . intval($ilance->GPC['rfpid']) . "',
                                '" . DATETIME24H . "',
                                '" . $ilance->db->escape_string($log) . "')
                        ");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects 
				SET updateid = updateid + 1
				WHERE project_id = '" . intval($ilance->GPC['rfpid']) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
                }        
        }
	
	/**
	* Function to print the various charities within a pulldown menu
	*
	* @param       integer      (optional) charity id to be as default selection
	*
	* @return      string       Returns HTML formatted presentation of a pulldown menu
	*/
	function print_charities_pulldown($charityid = 0)
	{
		global $ilance, $phrase, $ilconfig, $ilpage;
		$html = '<select name="charityid" style="font-family: verdana">';
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "charityid, title
			FROM " . DB_PREFIX . "charities
			WHERE visible = '1'
			ORDER BY title ASC
		", 0, null, __FILE__, __LINE__);                
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				if (isset($charityid) AND $charityid > 0 AND $charityid == $res['charityid'])
				{
					$html .= '<option value="' . $res['charityid'] . '" selected="selected">' . stripslashes(handle_input_keywords($res['title'])) . '</option>';
				}
				else
				{
					$html .= '<option value="' . $res['charityid'] . '">' . stripslashes(handle_input_keywords($res['title'])) . '</option>';
				}
			}
		}
		$html .= '</select>';
		return $html;
	}

	/**
	* Function to print the donation percentage pulldown menu
	*
	* @return      string       Returns HTML formatted presentation of a pulldown menu
	*/
	function print_donation_percentage($percentage = 0)
	{
		for ($i = 5; $i <= 100; $i = $i + 5)
		{
			$values[$i] = $i . '%';
		}
		return construct_pulldown('donationpercentage', 'donationpercentage', $values, $percentage, 'style="font-family: verdana"');
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>