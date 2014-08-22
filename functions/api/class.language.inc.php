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
* Language class to perform the majority of language functions in ILance.
*
* @package      iLance\Language
* @version      4.0.0.8059
* @author       ILance
*/
class language
{
        /**
        * array holding language cache
        */
        var $cache = array();
        
        /**
        * Constructor
        */
        function language()
        {
                global $ilance, $ilconfig;
                $sql = $ilance->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "languageid, title, languagecode, charset, locale, author, textdirection, languageiso, canselect, installdate, replacements
                        FROM " . DB_PREFIX . "language
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                $this->cache[$res['languageid']] = $res;
                        }
                        unset($res);
                }
        }
        
	/**
	* Function to return the language phrases cache array from the datastore.
	* This function is called just after session_start() within global.php
	*
	* @return      array       $phrase array
	*/
	function init_phrases()
	{
		global $ilance, $ilconfig, $phrase, $show;
		$ilance->timer->start();
		$phrase = array();
		$varnamein = $query = '';
		$slng = (isset($_SESSION['ilancedata']['user']['slng']) AND !empty($_SESSION['ilancedata']['user']['slng'])) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';
		$phrasesearch = array('{{site_name}}', '{{max_payment_days}}');
		$phrasereplace = array(SITE_NAME, $ilconfig['invoicesystem_maximumpaymentdays']);
		$ajax_phrases = array('_continue', '_you_have_selected_the_following_category', '_youve_selected_a_category_click_continue_button', '_no_category_specifics_exist_in_this_category', '_no_parent_category', '_assign_to_all_categories', '_remove', '_you_can', '_add_another_category_to_your_list');
		$watchlist_phrases = array('_sorry_to_track_higher_bid_amounts_you_will_need_to_place_a_bid_on_this_auction_first', '_sorry_to_track_lower_bid_amounts_you_will_need_to_place_a_bid_on_this_auction_first');
		$ws_phrases = array('_mediashare_for', '_navigation', '_shared', '_private', '_folder_name', '_folder_space', '_folder_comment', '_last_modified', '_supply_new_folder_information_below', '_folder_name', '_comments', '_save', '_upload', '_cancel', '_browse_file', '_upload_a_new_file_attachment_below', '_enter_any_comments_for_this_file_or_folder_below');
		$payment_phrases = array('_paypal', '_master_card', '_money_order' , '_personal_check', '_visa', '_see_description_for_my_accepted_payment_methods');
		
		($apihook = $ilance->api('init_phrases_start')) ? eval($apihook) : false;
		
		$varnames = array_merge($ajax_phrases, $watchlist_phrases, $ws_phrases, $payment_phrases);
		$varnames = array_values(array_unique($varnames));
                foreach($varnames AS $key => $value)
                {
                	$varnamein .= "'" . $value . "', ";
                }
                $varnamein = !empty($varnamein) ? 'varname IN (' . substr($varnamein, 0, strlen($varnamein) - 2) . ')' : '';
		if (!empty($varnamein))
		{
			$query = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.varname, p.text_" . $slng . " AS text
				FROM " . DB_PREFIX . "language_phrases p
				WHERE " . $varnamein . "
			", 0, null, __FILE__, __LINE__);
		}
		if ($ilance->db->num_rows($query) > 0)
		{
			while ($cache = $ilance->db->fetch_array($query, DB_ASSOC))
			{
				$phrase[$cache['varname']] = str_replace($phrasesearch, $phrasereplace, stripslashes(un_htmlspecialchars($cache['text'])));
			}
			unset($cache);
		}  
		unset($query, $varnamein, $queryextra, $cacheid);
                $ilance->timer->stop();
                DEBUG("init_phrases() in " . $ilance->timer->get() . " seconds", 'FUNCTION');
                return $phrase;
        }
    
        /**
        * Function to construct a phrase using replacement phrases
        *
        * @param       string      phrase string containing [x]'s
        * @param       mixed       array or string containing our replacements
        *
        * @return      array       $phrase array
        */
        function construct_phrase($var, $replacements)
        {
                global $ilance;
                $slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';	
                $var = str_replace(array('{', '}'), array('', ''), $var);
                $var = $ilance->db->fetch_field(DB_PREFIX . "language_phrases", "varname = '" . $var . "'", "text_" . $slng . "", "1");
                $result = $result2 = '';
                if (is_array($replacements))
                {
                        $k = 0;
                        $max = count($replacements);
                        for ($i = 0; $i < mb_strlen($var); $i++)
                        {
                                if (mb_substr($var, $i, 3) == '[x]')
                                {
                                        $result .= '' . $replacements[$k++] . '';
                                        if ($k > $max)
                                        {
                                                return '{_incorrect_number_of_replacements_provided_to_construct_phrase_function}';
                                        }
                                        $i+=2;
                                }
                                else
                                {
                                        $result .= mb_substr($var, $i, 1);
                                }
                        }
                }
                else
                {
                        for ($i = 0; $i < mb_strlen($var); $i++)
                        {
                                if (mb_substr($var, $i, 3) == '[x]')
                                {
                                        $result .= '' . $replacements . '';
                                        $i+=2;
                                }
                                else
                                {
                                        $result .= mb_substr($var, $i, 1);
                                }
                        }
                }
                return $result;
        }
        
        /**
        * Function to construct a language pulldown menu
        *
        * @return      string       HTML formatted language pulldown menu
        */
        function construct_language_pulldown($fieldname = 'languageid', $selected = 0)
        {
                global $ilance, $ilconfig;
                $html = '<select name="' . $fieldname . '" class="select">';
                $sql = $ilance->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "languageid, title
                        FROM " . DB_PREFIX . "language
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
				if (defined('LOCATION') AND LOCATION == 'admin' AND $selected > 0 AND !empty($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] > 0)
                                {
					// update user profile admin form
                                        $html .= '<option value="' . $res['languageid'] . '"';
                                        if ($res['languageid'] == $selected)
                                        {
                                                $html .= ' selected="selected"';
                                        }
                                        $html .= '>' . stripslashes($res['title']) . '</option>';
				}
                                else if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
                                {
                                        $sql2 = $ilance->db->query("
                                                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "languageid
                                                FROM " . DB_PREFIX . "users
                                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        $res2 = $ilance->db->fetch_array($sql2, DB_ASSOC);
                                        $html .= '<option value="' . $res['languageid'] . '"';
                                        if ($res['languageid'] == $res2['languageid'])
                                        {
                                                $html .= ' selected="selected"';
                                        }
                                        $html .= '>' . stripslashes($res['title']) . '</option>';
                                }
                                else
                                {
                                        // register form
                                        $html .= '<option value="' . $res['languageid'] . '"';
                                        if ($res['languageid'] == $ilconfig['globalserverlanguage_defaultlanguage'])
                                        {
                                                $html .= ' selected="selected"';
                                        }
                                        $html .= '>' . stripslashes($res['title']) . '</option>';
                                }
                        }
                }
                $html .= '</select>';
                return $html;
        }
        
        /**
        * Function to print a language code like english or german, etc
        *
        * @param       integer      (optional) language id
        * @return      string       HTML formatted language pulldown menu
        */
        function print_language_code($languageid = '')
        {
                global $ilance, $ilconfig, $ilance;
                $langid = !empty($languageid) ? intval($languageid) : $ilconfig['globalserverlanguage_defaultlanguage'];
                $sql = $ilance->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "languagecode
                        FROM " . DB_PREFIX . "language
                        WHERE languageid = '" . intval($langid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        return $res['languagecode'];
                }
                return 'english';
        }
        
        /**
        * Function to print a short version of the language code like eng or ger, etc
        *
        * @return      string       HTML formatted default language pulldown menu
        */
        function print_short_language_code()
        {
                global $ilance, $ilconfig;
                if (!empty($ilconfig['globalserverlanguage_defaultlanguage']) AND $ilconfig['globalserverlanguage_defaultlanguage'] > 0)
                {
                        $sql = $ilance->db->query("
                                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "languagecode
                                FROM " . DB_PREFIX . "language
                                WHERE languageid = '" . $ilconfig['globalserverlanguage_defaultlanguage'] . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                                return mb_substr($res['languagecode'], 0, 3);
                        }
                }
                return 'eng';
        }
    
        /**
        * Function to count the number of phrases within a particular phrase group
        *
        * @param       integer      phrase group
        * 
        * @return      integer      Returns the number of phrases in the phrasegroup
        */
        function count_phrases_in_phrasegroup($phrasegroup = '')
        {
                global $ilance;
                $sql = $ilance->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "COUNT(*) AS count
                        FROM " . DB_PREFIX . "language_phrases
                        WHERE phrasegroup = '" . $ilance->db->escape_string($phrasegroup) . "'
                ", 0, null, __FILE__, __LINE__);
                $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                return (int)$res['count'];
        }
        
        /**
        * Function to count the number of un-phrased phrases within a particular phrase group
        *
        * @param       integer      phrase group
        * @param       string       short language code
        * 
        * @return      integer      Returns the number of un-phrased phrases in the phrasegroup
        */
        function count_unphrased_in_phrasegroup($phrasegroup = '', $slng)
        {
                global $ilance;
                
                if (isset($slng) AND $slng != 'eng')
                {
                        $sql = $ilance->db->query("
                                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "COUNT(*) AS count
                                FROM " . DB_PREFIX . "language_phrases
                                WHERE phrasegroup = '" . $ilance->db->escape_string($phrasegroup) . "'
                                    AND text_$slng = text_eng
                        ", 0, null, __FILE__, __LINE__);
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        return ', ' . (int)$res['count'] . ' untranslated';
                }
                return '';
        }
        
        /**
        * Function to print the phrase group pulldown menu
        *
        * @param       integer      (optional) phrase group
        * @param       bool         enable auto-submit?
        * @param       string       short language code
        * 
        * @return      integer      HTML formatted phrase group pulldown menu
        */
        function print_phrase_groups_pulldown($selected = '', $autosubmit = '', $slng)
        {
                global $ilance, $phrase;
                
                $html = '';
                $sql = $ilance->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "groupname, description
                        FROM " . DB_PREFIX . "language_phrasegroups
                        ORDER BY description ASC
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        if ($autosubmit)
                        {
                                $html = '<select name="phrasegroup" id="phrasegroup" onchange="urlswitch(this, \'dostyle\')" class="select"><optgroup label="{_choose_phrase_group}">';
                        }
                        else
                        {
                                $html = '<select name="phrasegroup" id="phrasegroup" class="select"><optgroup label="{_choose_phrase_group}">';
                        }
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                if (isset($selected) AND $res['groupname'] == $selected)
                                {
                                        $html .= '<option value="' . $res['groupname'] . '" selected="selected">' . stripslashes($res['description']) . ' (' . $this->count_phrases_in_phrasegroup($res['groupname']) . ' phrases' . $this->count_unphrased_in_phrasegroup($res['groupname'], $slng) . ')</option>';
                                }
                                else
                                {
                                        $html .= '<option value="' . $res['groupname'] . '">' . stripslashes($res['description']) . ' (' . $this->count_phrases_in_phrasegroup($res['groupname']) . ' phrases' . $this->count_unphrased_in_phrasegroup($res['groupname'], $slng) . ')</option>';
                                }
                        }
                        $html .= '</optgroup></select>';
                }
                return $html;
        }
        
        /**
        * Function to print the site's default language id
        *
        * @return      integer       Returns default language id
        */
        function fetch_default_languageid()
        {
                global $ilconfig;
                if ($ilconfig['globalserverlanguage_defaultlanguage'] > 0)
                {
                        return intval($ilconfig['globalserverlanguage_defaultlanguage']);
                }
        }
        
        /**
        * Function to return site language selection pulldown menu on footer pages
        *
        * @param       string 	     selected language
        * @param       bool          enable auto-submit once new value is selected?
        * @param       string        fieldname of pulldown menu
        * @param       string        (optional) optgroup title
        */
        function print_language_pulldown($selected = '', $autosubmit = '', $selectname = '', $optgrouptitle = '')
        {
                global $ilance, $phrase;
                $html = '';
                $sql = $ilance->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "languageid, languagecode, title, canselect
                        FROM " . DB_PREFIX . "language
                ", 0, null, __FILE__, __LINE__);
                if ($autosubmit)
                {
                        $html = '<select name="language" id="language" onchange="urlswitch(this, \'dolanguage\')" class="select">';
                        $html .= '<optgroup label="{_choose_language}">';
                        $languagecount = 0;
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                if ($res['canselect'] OR !empty($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'])
                                {
                                        $html .= (isset($selected) AND $selected == $res['languageid']) ? '<option value="' . $res['languagecode'] . '" selected="selected">' . stripslashes($res['title']) . '</option>' : '<option value="' . $res['languagecode'] . '">' . stripslashes($res['title']) . '</option>';
                                        $languagecount++;
                                }
                        }
                        unset($res);
                        $html .= '</optgroup></select>';
                }
                else
                {
                        // custom select name title
                        $html = (isset($selectname) AND !empty($selectname)) ? '<select name="' . $selectname . '" id="' . $selectname . '" class="select">' : '<select name="languageid" id="languageid" class="select">';
                        $html .= (isset($optgrouptitle) AND !empty($optgrouptitle)) ? '<optgroup label="' . $optgrouptitle . '">' : '<optgroup label="' . '{_choose_language}' . '">';
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                $html .= (isset($selected) AND $selected == $res['languageid']) ? '<option value="' . $res['languageid'] . '" selected="selected">' . stripslashes($res['title']) . '</option>' : '<option value="' . $res['languageid'] . '">' . stripslashes($res['title']) . '</option>';
                        }
                        $html .= '</optgroup></select>';
                }
                // if we're viewing this pulldown menu from the footer page, and have only 1 language, hide the pulldown menu
                if ($autosubmit AND $languagecount == 1 AND defined('LOCATION') AND (LOCATION != 'admin' OR LOCATION != 'registration'))
                {
                        $html = '';
                }
                return $html;
        }
        
        /**
        * Function to fetch the seo replacement characters for the seo urls based on the currently selected viewing language
        *
        * @param       integer       language id
        * 
        * @return      integer       Returns the phrase group id number
        */
        function fetch_seo_replacements($languageid = 0)
        {
                return $this->cache[$languageid]['replacements'];
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>