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
* Template columns class to generate user selectable table headers and display results when searching in the marketplace.
*
* @package      iLance\Template\Columns
* @version      4.0.0.8059
* @author       ILance
*/
class template_columns extends template
{
        /**
        * Default table column info
        */
        var $columninfo = array
        (
                'default' => array
                (
                        'width' => '5%',
                        'rowwidth' => '5%',
                        'class' => 'alt2',
                        'rowclass' => '',
                        'colspan' => '',
                        'nowrap' => '',
                        'align' => 'left',
                        'valign' => 'top',
                        'id' => '',
                        'rowalign' => 'left',
                        'extra' => ''
                ),
                'title' => array
                (
                        'width' => '155',
                        'rowwidth' => '155',
                        'class' => 'alt2',
                        'rowclass' => '',
                        'colspan' => '',
                        'nowrap' => '',
                        'align' => 'left',
                        'valign' => 'top',
                        'id' => '',
                        'rowalign' => 'left',
                        'extra' => ''
                ),
                'bids' => array
                (
                        'width' => '2%',
                        'rowwidth' => '2%',
                        'class' => 'alt2',
                        'rowclass' => '',
                        'colspan' => '',
                        'nowrap' => 'nowrap="nowrap"',
                        'align' => 'left',
                        'valign' => 'top',
                        'id' => '',
                        'rowalign' => 'left',
                        'extra' => ''
                ),
                'averagebid' => array
                (
                        'width' => '11%',
                        'rowwidth' => '11%',
                        'class' => 'alt2',
                        'rowclass' => '',
                        'colspan' => '',
                        'nowrap' => 'nowrap="nowrap"',
                        'align' => 'left',
                        'valign' => 'top',
                        'id' => '',
                        'rowalign' => 'left',
                        'extra' => ''
                ),
                'timeleft' => array
                (
                        'width' => '5%',
                        'rowwidth' => '5%',
                        'class' => 'alt2',
                        'rowclass' => '',
                        'colspan' => '',
                        'nowrap' => 'nowrap="nowrap"',
                        'align' => 'left',
                        'valign' => 'top',
                        'id' => '',
                        'rowalign' => 'left',
                        'extra' => ''
                ),
                'category' => array
                (
                        'width' => '15%',
                        'rowwidth' => '15%',
                        'class' => 'alt2',
                        'rowclass' => '',
                        'colspan' => '',
                        'nowrap' => 'nowrap="nowrap"',
                        'align' => 'left',
                        'valign' => 'top',
                        'id' => '',
                        'rowalign' => 'left',
                        'extra' => ''
                ),
                'views' => array
                (
                        'width' => '2%',
                        'rowwidth' => '2%',
                        'class' => 'alt2',
                        'rowclass' => '',
                        'colspan' => '',
                        'nowrap' => '',
                        'align' => 'left',
                        'valign' => 'top',
                        'id' => '',
                        'rowalign' => 'left',
                        'extra' => ''
                ),
                'budget' => array
                (
                        'width' => '15%',
                        'rowwidth' => '5%',
                        'class' => 'alt2',
                        'rowclass' => '',
                        'colspan' => '',
                        'nowrap' => '',
                        'align' => 'left',
                        'valign' => 'top',
                        'id' => '',
                        'rowalign' => 'left',
                        'extra' => ''
                ),
                'distance' => array
                (
                        'width' => '12%',
                        'rowwidth' => '12%',
                        'class' => 'alt2',
                        'rowclass' => '',
                        'colspan' => '',
                        'nowrap' => 'nowrap="nowrap"',
                        'align' => 'left',
                        'valign' => 'top',
                        'id' => '',
                        'rowalign' => 'left',
                        'extra' => ''
                ),
                'sample' => array
                (
                        'width' => '',
                        'rowwidth' => '',
                        'class' => 'alt2',
                        'rowclass' => '',
                        'rowclass' => '',
                        'colspan' => '',
                        'nowrap' => '',
                        'align' => 'center',
                        'valign' => 'top',
                        'id' => '',
                        'rowalign' => 'right',
                        'extra' => ''
                ),
                'price' => array
                (
                        'width' => 'auto',
                        'rowwidth' => 'auto',
                        'class' => 'alt2',
                        'rowclass' => '',
                        'colspan' => '',
                        'nowrap' => 'nowrap="nowrap"',
                        'align' => 'left',
                        'valign' => 'top',
                        'id' => '',
                        'rowalign' => 'left',
                        'extra' => ''
                ),
                'shipping' => array
                (
                        'width' => '8%',
                        'rowwidth' => '8%',
                        'class' => 'alt2',
                        'rowclass' => '',
                        'colspan' => '',
                        'nowrap' => 'nowrap="nowrap"',
                        'align' => 'left',
                        'valign' => 'top',
                        'id' => '',
                        'rowalign' => 'left',
                        'extra' => ''
                ),
                'expert' => array
                (
                        'width' => '120',
                        'rowwidth' => '120',
                        'class' => 'alt2',
                        'rowclass' => '',
                        'colspan' => '',
                        'nowrap' => 'nowrap="nowrap"',
                        'align' => 'left',
                        'valign' => 'top',
                        'id' => '',
                        'rowalign' => 'left',
                        'extra' => ''
                ),
                'profilelogo' => array
                (
                        'width' => '1%',
                        'rowwidth' => '1%',
                        'class' => 'alt2',
                        'rowclass' => 'alt1',
                        'colspan' => '',
                        'nowrap' => 'nowrap="nowrap"',
                        'align' => 'center',
                        'valign' => 'top',
                        'id' => '',
                        'rowalign' => 'center',
                        'extra' => ''
                ),
                'credentials' => array
                (
                        'width' => '3%',
                        'rowwidth' => '3%',
                        'class' => 'alt2',
                        'rowclass' => '',
                        'colspan' => '',
                        'nowrap' => 'nowrap="nowrap"',
                        'align' => 'left',
                        'valign' => 'top',
                        'id' => '',
                        'rowalign' => 'left',
                        'extra' => ''
                ),
                'rated' => array
                (
                        'width' => '10%',
                        'rowwidth' => '8%',
                        'class' => 'alt2',
                        'rowclass' => '',
                        'colspan' => '',
                        'nowrap' => 'nowrap="nowrap"',
                        'align' => 'left',
                        'valign' => 'top',
                        'id' => '',
                        'rowalign' => 'left',
                        'extra' => ''
                ),
                'feedback' => array
                (
                        'width' => '12%',
                        'rowwidth' => '12%',
                        'class' => 'alt2',
                        'rowclass' => '',
                        'colspan' => '',
                        'nowrap' => 'nowrap="nowrap"',
                        'align' => 'left',
                        'valign' => 'top',
                        'id' => '',
                        'rowalign' => 'left',
                        'extra' => ''
                ),
                'awards' => array
                (
                        'width' => '1%',
                        'rowwidth' => '1%',
                        'class' => 'alt2',
                        'rowclass' => '',
                        'colspan' => '',
                        'nowrap' => 'nowrap="nowrap"',
                        'align' => 'left',
                        'valign' => 'top',
                        'id' => '',
                        'rowalign' => 'left',
                        'extra' => ''
                ),
                'earnings' => array
                (
                        'width' => '8%',
                        'rowwidth' => '8%',
                        'class' => 'alt2',
                        'rowclass' => '',
                        'colspan' => '',
                        'nowrap' => 'nowrap="nowrap"',
                        'align' => 'left',
                        'valign' => 'top',
                        'id' => '',
                        'rowalign' => 'left',
                        'extra' => ''
                ),
                'rateperhour' => array
                (
                        'width' => '5%',
                        'rowwidth' => '5%',
                        'class' => 'alt2',
                        'rowclass' => '',
                        'colspan' => '',
                        'nowrap' => 'nowrap="nowrap"',
                        'align' => 'left',
                        'valign' => 'top',
                        'id' => '',
                        'rowalign' => 'left',
                        'extra' => ''
                ),
                'portfolio' => array
                (
                        'width' => '5%',
                        'rowwidth' => '5%',
                        'class' => 'alt2',
                        'rowclass' => '',
                        'colspan' => '',
                        'nowrap' => 'nowrap="nowrap"',
                        'align' => 'left',
                        'valign' => 'top',
                        'id' => '',
                        'rowalign' => 'left',
                        'extra' => ''
                ),
                'city' => array
                (
                        'width' => '5%',
                        'rowwidth' => '5%',
                        'class' => 'alt2',
                        'rowclass' => '',
                        'colspan' => '',
                        'nowrap' => 'nowrap="nowrap"',
                        'align' => 'left',
                        'valign' => 'top',
                        'id' => '',
                        'rowalign' => 'left',
                        'extra' => ''
                ),
                'zipcode' => array
                (
                        'width' => '5%',
                        'rowwidth' => '5%',
                        'class' => 'alt2',
                        'rowclass' => '',
                        'colspan' => '',
                        'nowrap' => 'nowrap="nowrap"',
                        'align' => 'left',
                        'valign' => 'top',
                        'id' => '',
                        'rowalign' => 'left',
                        'extra' => ''
                ),
                'state' => array
                (
                        'width' => '5%',
                        'rowwidth' => '5%',
                        'class' => 'alt2',
                        'rowclass' => '',
                        'colspan' => '',
                        'nowrap' => 'nowrap="nowrap"',
                        'align' => 'left',
                        'valign' => 'top',
                        'id' => '',
                        'rowalign' => 'left',
                        'extra' => ''
                ),
                'country' => array
                (
                        'width' => '5%',
                        'rowwidth' => '5%',
                        'class' => 'alt2',
                        'rowclass' => '',
                        'colspan' => '',
                        'nowrap' => 'nowrap="nowrap"',
                        'align' => 'left',
                        'valign' => 'top',
                        'id' => '',
                        'rowalign' => 'left',
                        'extra' => ''
                ),
                'sel' => array
                (
                        'width' => '1%',
                        'rowwidth' => '1%',
                        'class' => 'alt2',
                        'rowclass' => '',
                        'colspan' => '',
                        'nowrap' => 'nowrap="nowrap"',
                        'align' => 'center',
                        'valign' => 'top',
                        'id' => '',
                        'rowalign' => 'center',
                        'extra' => ''
                ),
        );
        
        /**
        * Default table column phrase identifiers
        */
        var $columnphrases = array
        (
                'title' => '_title',
                'bids' => '_bids',
                'averagebid' => '_average_bid',
                'timeleft' => '_time_left',
                'category' => '_category',
                'views' => '_views',
                'budget' => '_budget',
                'distance' => '_distance',
                'sample' => '_sample',
                'price' => '_price',
                'shipping' => '_shipping',
                'expert' => '_expert',
                'profilelogo' => '_logo',
                'credentials' => '_credentials',
                'rated' => '_rated',
                'feedback' => '_feedback',
                'awards' => '_awards',
                'earnings' => '_earnings',
                'rateperhour' => '_hourly_rate',
                'portfolio' => '_portfolio',
                'city' => '_city',
                'country' => '_country',
                'zipcode' => '_zip',
                'state' => '_state',
                'sel' => '_sel',
		'location' => '_location',
        );
        
        /*
        * ...
        *
        * @param       
        *
        * @return      
        */   
        function print_td_list_head($tableheader = '', $mode = '')
        {
                global $ilance, $phrase, $show, $ilconfig, $ilpage;
                $html = '';
                if ($mode == 'service' AND $ilconfig['servicesearchheadercolumns'] <= 0)
                {
                        return '';        
                }
                else if ($mode == 'product' AND $ilconfig['productsearchheadercolumns'] <= 0)
                {
                        return '';        
                }
                else if ($mode == 'experts' AND $ilconfig['expertssearchheadercolumns'] <= 0)
                {
                        return '';        
                }
                $value['colspan'] = ((isset($tableheader) AND !empty($this->columninfo["$tableheader"]['colspan']))
                        ? 'colspan="' . $this->columninfo["$tableheader"]['colspan'] . '"'
                        : 'colspan="' . $this->columninfo['default']['colspan'] . '"');
                $value['nowrap'] = ((isset($tableheader) AND !empty($this->columninfo["$tableheader"]['nowrap']))
                        ? 'nowrap="nowrap"'
                        : $this->columninfo['default']['nowrap']);
                $value['extra'] = ((isset($tableheader) AND !empty($this->columninfo["$tableheader"]['extra']))
                        ? $this->columninfo["$tableheader"]['extra']
                        : $this->columninfo['default']['extra']);
                $value['class'] = ((isset($tableheader) AND !empty($this->columninfo["$tableheader"]['class']))
                        ? $this->columninfo["$tableheader"]['class']
                        : $this->columninfo['default']['class']);
                /*$value['align'] = ((isset($tableheader) AND !empty($this->columninfo["$tableheader"]['align']))
                        ? $this->columninfo["$tableheader"]['align']
                        : $this->columninfo['default']['align']);*/
                $value['align'] = ((isset($tableheader) AND !empty($this->columninfo["$tableheader"]['align']))
                        ? (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right')
                        : (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right'));
                $value['width'] = ((isset($tableheader) AND !empty($this->columninfo["$tableheader"]['width']))
                        ? $this->columninfo["$tableheader"]['width']
                        : $this->columninfo['default']['width']);
                if (isset($tableheader) AND isset($this->columnphrases[$tableheader]))
                {
                        $value['tableheader'] = '{' . $this->columnphrases[$tableheader] . '}';
                }
                else
                {
                        $value['tableheader'] = '&nbsp;';
                }
                // #### distance calculation output ############################
                if ($tableheader == 'distance' AND isset($show['distancecolumn']) AND $show['distancecolumn'] AND $ilconfig['globalserver_enabledistanceradius'])
                {
                        $html1 = $this->fetch_template('search_results_list_col_data_distance.html');
                        $html1 = $this->parse_hash('search_results_list_col_data_distance.html', array('ilconfig' => $ilconfig, 'ilpage' => $ilpage, 'value' => $value), 0, $html1);
                        $html1 = $this->parse_if_blocks('search_results_list_col_data_distance.html', $html1, true);
                        $html1 = stripslashes($html1);
                        $html1 = addslashes($html1);
                        $html1 = str_replace('$', '\$', $html1);
                        eval('$html .= "' . $html1 . '";');
                        unset($html1);
                }
                // #### custom profile questions ###############################
                else if (mb_ereg('profile_', $tableheader))
                {
                        $profileqid = explode('_', $tableheader);
                        if (isset($profileqid[1]))
                        {
                                $question = $ilance->db->fetch_field(DB_PREFIX . "profile_questions", "questionid = '" . intval($profileqid[1]) . "'", "question");
                                $value['tableheader'] = ((isset($question) AND $question != '') ? $question : '&nbsp;');
                                unset($question);
                        }
                        else
                        {
                                $value['tableheader'] = '&nbsp;';
                        }
                        $value['nowrap'] = 'nowrap="nowrap"';
                        $html1 = $this->fetch_template('search_results_list_col_data_profilefilter.html');
                        $html1 = $this->parse_hash('search_results_list_col_data_profilefilter.html', array('ilconfig' => $ilconfig, 'ilpage' => $ilpage, 'value' => $value), 0, $html1);
                        $html1 = $this->parse_if_blocks('search_results_list_col_data_profilefilter.html', $html1, true);
                        $html1 = stripslashes($html1);
                        $html1 = addslashes($html1);
                        $html1 = str_replace('$', '\$', $html1);
                        eval('$html .= "' . $html1 . '";');
                        unset($html1);
                }
                // #### all other columns ######################################
                else
                {
                        $html1 = $this->fetch_template('search_results_list_col_data.html');
                        $html1 = $this->parse_hash('search_results_list_col_data.html', array('ilconfig' => $ilconfig, 'ilpage' => $ilpage, 'value' => $value), 0, $html1);
                        $html1 = $this->parse_if_blocks('search_results_list_col_data.html', $html1, true);
                        $html1 = stripslashes($html1);
                        $html1 = addslashes($html1);
                        $html1 = str_replace('$', '\$', $html1);
                        eval('$html .= "' . $html1 . '";');
                        unset($html1);
                }
                if ($html != '')
                {
                        $html = stripslashes($html);
                }
                return $html;
        }
        
        /*
        * This function is responsible for printing out each "block" or column in the search results when a user is viewing
        * "View Mode: Gallery".  As per example, if you were showing 3 columns per row, this function generates each "column" and
        * the necessary HTML markup language which is now served from the template folder.
        *
        * @param     array      array with $data['vars'] for use like {data[vars]} in the html template
        * @param     string     the type of column to generate (service, product or experts)
        *
        * @return    string     HTML representation of the templated fully parsed ready for browser display
        */
        function print_td_gallery_head($data = array(), $type = 'service')
        {
                global $phrase, $show, $ilconfig, $ilpage, $php_self_urlencoded, $auctiontype;
                $html = $html1 = '';
                $bold = $highlite = false;
                if (!empty($data) AND is_array($data) AND count($data) > 0)
                {
                        if ($type == 'service')
                        {
                                // grab all columns
                                // run foreach on each column and build $show[xx]
                                $bold = $data['bold'];
                                $highlite = $data['highlite'];
                                $bids = $data['bids'];
                                $html1 = $this->fetch_template('search_results_service_gallery_column.html');
                                $html1 = $this->parse_hash('search_results_service_gallery_column.html', array('ilconfig' => $ilconfig, 'ilpage' => $ilpage, 'data' => $data), 0, $html1);
                                $html1 = $this->parse_if_blocks('search_results_service_gallery_column.html', $html1, true);
                                $html1 = stripslashes($html1);
                                $html1 = addslashes($html1);
                                $html1 = str_replace('$', '\$', $html1);
                                eval('$html .= "' . $html1 . '";');
                        }
                        else if ($type == 'product')
                        {
                                // grab all columns
                                // run foreach on each column and build $show[xx]
                                $auctiontype = $data['filtered_auctiontype'];
                                $bold = $data['bold'];
                                $highlite = $data['highlite'];
                                $bids = $data['bids'];
                                $html1 = $this->fetch_template('search_results_product_gallery_column.html');
                                $html1 = $this->parse_hash('search_results_product_gallery_column.html', array('ilconfig' => $ilconfig, 'ilpage' => $ilpage, 'data' => $data), 0, $html1);
                                $html1 = $this->parse_if_blocks('search_results_product_gallery_column.html', $html1, true);
                                $html1 = stripslashes($html1);
                                $html1 = addslashes($html1);
                                $html1 = str_replace('$', '\$', $html1);
                                eval('$html .= "' . $html1 . '";');
                        }
                        else if ($type == 'experts')
                        {
                                // grab all columns
                                // run foreach on each column and build $show[xx]
                                $bold = (isset($data['bold']) ? $data['bold'] : '');
                                $highlite = '';
                                $html1 = $this->fetch_template('search_results_experts_gallery_column.html');
                                $html1 = $this->parse_hash('search_results_experts_gallery_column.html', array('ilconfig' => $ilconfig, 'ilpage' => $ilpage, 'data' => $data), 0, $html1);
                                $html1 = $this->parse_if_blocks('search_results_experts_gallery_column.html', $html1, true);
                                $html1 = stripslashes($html1);
                                $html1 = addslashes($html1);
                                $html1 = str_replace('$', '\$', $html1);
                                eval('$html .= "' . $html1 . '";');
                        }
                        $html = stripslashes($html);
                }
                return $html;
        }
        
        /*
        * ...
        *
        * @param       
        *
        * @return      
        */
        function print_table_head_columns($searchresults = array(), $mode = 'service', $listview = 'list')
        {
                global $ilance, $ilconfig, $ilpage;
                // #### CUSTOM USER DESIGNED COLUMN HEADERS ####################
                if (!empty($_SESSION['ilancedata']['user']['searchoptions']))
                {
                        $columnstemp = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                        switch ($listview)
                        {
                                // #### list view ##############################
                                case 'list':
                                {
                                        switch ($mode)
                                        {
                                                case 'service':
                                                {
                                                        $columns = isset($columnstemp['serviceselected']) ? $columnstemp['serviceselected'] : array();
                                                        break;
                                                }
                                                case 'product':
                                                {
                                                        $columns = isset($columnstemp['productselected']) ? $columnstemp['productselected'] : array();
                                                        break;
                                                }
                                                case 'experts':
                                                {
                                                        $columns = isset($columnstemp['expertselected']) ? $columnstemp['expertselected'] : array();
                                                        break;
                                                }
                                        }
                                        $title_row = '';
                                        if (count($searchresults) > 0 AND isset($searchresults['0']['featured_searchresults']) AND $searchresults['0']['featured_searchresults'] == '1' AND $mode == 'product')
                                        {
                                                //$title_row  .= '<td colspan="100%" style="font-weight:bold" class="smaller black">{_featured}</td>';
                                        }
                                        // #### parse template cosmetics: list column header
                                        $html1 = $this->fetch_template('search_results_list_col_header.html');
                                        $html1 = $this->parse_hash('search_results_list_col_header.html', array('ilconfig' => $ilconfig, 'ilpage' => $ilpage), 0, $html1);
                                        $html1 = $this->parse_if_blocks('search_results_list_col_header.html', $html1, true);
                                        $html1 = stripslashes($html1);
                                        $html1 = addslashes($html1);
                                        $html1 = str_replace('$', '\$', $html1);
                                        $html2 = '';
                                        foreach ($columns AS $td)
                                        {
                                                if (isset($td) AND $td == 'distance')
                                                {
                                                        $html2 .= ($ilconfig['globalserver_enabledistanceradius'])
                                                                ? $this->print_td_list_head($td, $mode)
                                                                : '';
                                                }
                                                else
                                                {
                                                        $html2 .= $this->print_td_list_head($td, $mode);
                                                }
                                        }
                                        // #### parse template cosmetics: list column footer
                                        $html3 = $this->fetch_template('search_results_list_col_footer.html');
                                        $html3 = $this->parse_hash('search_results_list_col_footer.html', array('ilconfig' => $ilconfig, 'ilpage' => $ilpage), 0, $html1);
                                        $html3 = $this->parse_if_blocks('search_results_list_col_footer.html', $html1, true);
                                        $html3 = stripslashes($html1);
                                        $html3 = addslashes($html1);
                                        $html3 = str_replace('$', '\$', $html1);
                                        $table_head = $html1 . $html2 . $html3;
                                        $html = $table_head . $title_row;
                                        break;
                                }
                                // #### gallery view ###########################
                                case 'gallery':
                                {
                                        $html = '';
                                        $cols = 0;
                                        $columns = isset($columnstemp['colsperrow']) ? $columnstemp['colsperrow'] : 3;
                                        $columnwidth = round((100 / $columns), 2);
                                        foreach ($searchresults AS $key => $value)
                                        {
                                                $data['colums'] = $columns;
                                                $class = (isset($value['highlite']) AND $value['highlite'] == '1') ? ' featured_highlight alt1_' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') : ' alt1_' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right');
                                                if ($cols == 0)
                                                {
                                                	/*
                                                        $html1 = $this->fetch_template('search_results_gallery_separator.html');
                                                        $html1 = $this->parse_hash('search_results_gallery_separator.html', array('data' => $data, 'ilconfig' => $ilconfig, 'ilpage' => $ilpage), $parseglobals = 0, $html1);
                                                        $html1 = $this->parse_if_blocks('search_results_gallery_separator.html', $html1, $addslashes = true);
                                                        
                                                        $html1 = stripslashes($html1);
                                                        $html1 = addslashes($html1);
                                                        $html1 = str_replace('$', '\$', $html1);
                                                        
                                                        eval('$html .= "' . $html1 . '";');
                                                        unset($html1);
                                                        */
                                                        $class = (isset($value['highlite']) AND $value['highlite'] == '1') ? ' featured_highlight' : ' alt1';
                                                }
                                                else
                                                {
                                                        $class = (isset($value['highlite']) AND $value['highlite'] == '1') ? ' featured_highlight alt1_' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') : ' alt1_' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right');
                                                }
                                                $data['html'] = $this->print_td_gallery_head($value, $mode);
                                                $data['columnwidth'] = $columnwidth;
                                                $data['class'] = $class;
                                                $html1 = $this->fetch_template('search_results_gallery_row_data.html');
                                                $html1 = $this->parse_hash('search_results_gallery_row_data.html', array('data' => $data, 'ilconfig' => $ilconfig, 'ilpage' => $ilpage), 0, $html1);
                                                $html1 = $this->parse_if_blocks('search_results_gallery_row_data.html', $html1, true);
                                                $html1 = stripslashes($html1);
                                                $html1 = addslashes($html1);
                                                $html1 = str_replace('$', '\$', $html1);
                                                eval('$html .= "' . $html1 . '";');
                                                unset($html1);
                                                $cols++;
                                                if ($cols == $columns)
                                                {
                                                        $html1 = $this->fetch_template('search_results_gallery_separator_end.html');
                                                        $html1 = $this->parse_hash('search_results_gallery_separator_end.html', array('data' => $data, 'ilconfig' => $ilconfig, 'ilpage' => $ilpage), 0, $html1);
                                                        $html1 = $this->parse_if_blocks('search_results_gallery_separator_end.html', $html1, true);
                                                        $html1 = stripslashes($html1);
                                                        $html1 = addslashes($html1);
                                                        $html1 = str_replace('$', '\$', $html1);
                                                        eval('$html .= "' . $html1 . '";');
                                                        unset($html1);
                                                        $cols = 0;
                                                }
                                        }
                                        if ($cols != $columns && $cols != 0)
                                        {
                                                $neededtds = $columns - $cols;
                                                for ($i = 0; $i < $neededtds; $i++)
                                                {
                                                        $html1 = $this->fetch_template('search_results_gallery_col_filler.html');
                                                        $html1 = $this->parse_hash('search_results_gallery_col_filler.html', array('data' => $data, 'ilconfig' => $ilconfig, 'ilpage' => $ilpage), 0, $html1);
                                                        $html1 = $this->parse_if_blocks('search_results_gallery_col_filler.html', $html1, true);
                                                        $html1 = stripslashes($html1);
                                                        $html1 = addslashes($html1);
                                                        $html1 = str_replace('$', '\$', $html1);
                                                        eval('$html .= "' . $html1 . '";');
                                                        unset($html1);
                                                }
                                                $html1 = $this->fetch_template('search_results_gallery_row_footer.html');
                                                $html1 = $this->parse_hash('search_results_gallery_row_footer.html', array('data' => $data, 'ilconfig' => $ilconfig, 'ilpage' => $ilpage), 0, $html1);
                                                $html1 = $this->parse_if_blocks('search_results_gallery_row_footer.html', $html1, true);
                                                $html1 = stripslashes($html1);
                                                $html1 = addslashes($html1);
                                                $html1 = str_replace('$', '\$', $html1);
                                                eval('$html .= "' . $html1 . '";');
                                                unset($html1);
                                        }
                                        // #### parse template cosmetics #######
                                        break;
                                }
                        }
                }
                $table_head = (isset($table_head) AND $listview == 'list') ? $table_head : '';
                $rows = $this->print_td_rows($columns, $searchresults, $listview, $table_head);
                if ($listview != 'gallery')
                {
                        $columns = count($columns);
                }
                if ($html != '')
                {
                        $html = stripslashes($html);
                }
                // #### return html and the number of columns generated (for colspan callback)
                $return = array('columns' => $html, 'rows' => $rows, 'colspan' => $columns);
                return $return;
        }
        
        /*
        * Function to handle special column output like title, distance and average bid
        *
        * @param       
        *
        * @return      
        */
        function print_td_rows($columns, $searchresults = array(), $listview = 'list', $table_head = '')
        {
                global $ilance, $show, $ilconfig, $phrase, $ilpage;
                if ($listview == 'gallery')
                {
                        return '';
                }
                $rows = $html_head = '';
                $isfeatured = '0';
                if (!empty($searchresults) AND is_array($searchresults))
                {
                        foreach ($searchresults AS $key => $value)
                        {
                                $html1 = '';
                                if ($listview == 'list')
                                {
                                        if ($key == '0' AND isset($searchresults[$key]['featured_searchresults']) AND $searchresults[$key]['featured_searchresults'] == '1')
                                        {
                                                $isfeatured = '1';
                                        }
                                        if ($isfeatured == '1' AND isset($searchresults[$key]['featured_searchresults']) AND $searchresults[$key]['featured_searchresults'] == '0' AND isset($ilance->GPC['mode']) AND $ilance->GPC['mode'] == 'product')
                                        {
                                                $html2 = '<td colspan="100%" class="smaller black alt2">{_optimize_your_sales} <span class="blueonly"><a href="' . (($ilconfig['globalauctionsettings_seourls']) ? HTTP_SERVER . 'search-promotion?returnurl=' . urlencode(PAGEURL) : HTTP_SERVER . $ilpage['search'] . '?cmd=promotion&amp;returnurl=' . urlencode(PAGEURL)) . '" rel="nofollow">{_promote_your_items}</a></span></td>';   
						$html1 = $html2;
                                                $isfeatured = '0';
                                                $html_head = '';
                                        }
                                        else if ($isfeatured == '1' AND isset($searchresults[$key]['featured_searchresults']) AND $searchresults[$key]['featured_searchresults'] == '0' AND isset($ilance->GPC['mode']) AND $ilance->GPC['mode'] == 'service')
                                        {
                                                $html2 = '';
                                                $html1 = $html2;
                                                $isfeatured = '0';
                                                $html_head = '';
                                        }
                                }
                                $html1 .= $this->fetch_template('search_results_list_row_header.html');
                                $html1 = $this->parse_hash('search_results_list_row_header.html', array('value' => $value), 0, $html1);
                                //$html1 = $this->parse_if_blocks('search_results_list_row_header.html', $html1, true);
                                $html1 = stripslashes($html1);
                                $html1 = addslashes($html1);
                                $html1 = str_replace('$', '\$', $html1);
                                eval('$rows .= "' . $html1 . '";');
                                unset($html1);
                                foreach ($columns AS $column)
                                {
                                        if (isset($column) AND $column != '')
                                        {
                                                $value['rowclass'] = ((!empty($this->columninfo["$column"]['rowclass']))
                                                        ? 'class="' . $this->columninfo["$column"]['rowclass'] . '"'
                                                        : 'class="' . $this->columninfo['default']['rowclass'] . '"');
                                                $value['colspan'] = ((!empty($this->columninfo["$column"]['colspan']))
                                                        ? 'colspan="' . $this->columninfo["$column"]['colspan'] . '"'
                                                        : 'colspan="' . $this->columninfo['default']['colspan'] . '"');
                                                $value['nowrap'] = ((!empty($this->columninfo["$column"]['nowrap']))
                                                        ? 'nowrap="nowrap"'
                                                        : $this->columninfo['default']['nowrap']);
                                                $value['extra'] = ((!empty($this->columninfo["$column"]['extra']))
                                                        ? $this->columninfo["$column"]['extra']
                                                        : $this->columninfo['default']['extra']);
                                                $value['class'] = ((!empty($this->columninfo["$column"]['class']))
                                                        ? $this->columninfo["$column"]['class']
                                                        : $this->columninfo['default']['class']);
                                                /*$value['align'] = ((!empty($this->columninfo["$column"]['align']))
                                                        ? $this->columninfo["$column"]['align']
                                                        : $this->columninfo['default']['align']);*/
                                                $value['align'] = ((!empty($this->columninfo["$column"]['align']))
                                                        ? (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right')
                                                        : (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right'));
                                                $value['valign'] = ((!empty($this->columninfo["$column"]['valign']))
                                                        ? $this->columninfo["$column"]['valign']
                                                        : $this->columninfo['default']['valign']);
                                                $value['width'] = ((!empty($this->columninfo["$column"]['width']))
                                                        ? $this->columninfo["$column"]['width']
                                                        : $this->columninfo['default']['width']);
                                                // #### custom service provider profile question column
                                                if (mb_ereg('profile_', $column))
                                                {
                                                        $profileqid = explode('_', $column);
                                                        if (isset($profileqid) AND $profileqid > 0)
                                                        {
                                                                $sql = $ilance->db->query("
                                                                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "answer, isverified
                                                                        FROM " . DB_PREFIX . "profile_answers
                                                                        WHERE questionid = '" . intval($profileqid[1]) . "'
                                                                                AND user_id = '" . intval($value['user_id']) . "'
                                                                ");
                                                                if ($ilance->db->num_rows($sql) > 0)
                                                                {
                                                                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                                                                        $isverified = (($res['isverified'])
                                                                                ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'verified_icon.gif" alt="" border="0" />'
                                                                                : '');
                                                                        $value['isverified'] = $isverified;
                                                                        unset($isverified);
                                                                        $value['align'] = $this->columninfo['default']['rowalign'];
                                                                        $value['valign'] = $this->columninfo['default']['valign'];
                                                                        $value['answer'] = $res['answer'];
                                                                }
                                                                else
                                                                {
                                                                        $value['isverified'] = '';
                                                                        $value['align'] = $this->columninfo['default']['rowalign'];
                                                                        $value['valign'] = $this->columninfo['default']['valign'];
                                                                        $value['answer'] = '-';
                                                                }
                                                                $html1 = $this->fetch_template('search_results_list_row_data_profilefilter.html');
                                                                $html1 = $this->parse_hash('search_results_list_row_data_profilefilter.html', array('value' => $value), 0, $html1);
                                                                $html1 = $this->parse_if_blocks('search_results_list_row_data_profilefilter.html', $html1, true);
                                                                $html1 = stripslashes($html1);
                                                                $html1 = addslashes($html1);
                                                                $html1 = str_replace('$', '\$', $html1);
                                                                eval('$rows .= "' . $html1 . '";');
                                                                unset($html1);
                                                        }
                                                }
                                                // #### listing title column
                                                else if ($column == 'title')
                                                {
                                                        $value['column'] = $value["$column"];
                                                        $value['icons'] = (($this->can_display_element('icons'))
                                                                ? $value['icons']
                                                                : '');
                                                        $value['description'] = (($this->can_display_element('description') AND isset($value['description']))
                                                                ? $value['description']
                                                                : '');
                                                        $value['user'] = (($this->can_display_element('username') AND !empty($value['username']))
                                                                ? ((($value['project_state'] == 'service') ? '{_buyer}: ' : '{_seller}: ') . $value['username'])
                                                                : '');
                                                        $value['proxybit'] = (($value['project_state'] == 'product' AND $this->can_display_element('proxybit') AND !empty($value['proxybit']))
                                                                ? $value['proxybit']
                                                                : '');
                                                        $value['location'] = $value['location'];
                                                        $value['feedback'] = '';
                                                        $html1 = $this->fetch_template('search_results_list_row_data_title.html');
                                                        $html1 = $this->parse_hash('search_results_list_row_data_title.html', array('value' => $value), 0, $html1);
                                                        $html1 = $this->parse_if_blocks('search_results_list_row_data_title.html', $html1, true);
                                                        $html1 = stripslashes($html1);
                                                        $html1 = addslashes($html1);
                                                        $html1 = str_replace('$', '\$', $html1);
                                                        eval('$rows .= "' . $html1 . '";');
                                                        unset($html1);
                                                }
                                                // #### distance column
                                                else if ($column == 'distance' AND isset($show['distancecolumn']) AND $show['distancecolumn'] AND $ilconfig['globalserver_enabledistanceradius'])
                                                {
                                                        $value['column'] = $value["$column"];
                                                        $html1 = $this->fetch_template('search_results_list_row_data_distance.html');
                                                        $html1 = $this->parse_hash('search_results_list_row_data_distance.html', array('value' => $value), 0, $html1);
                                                        $html1 = $this->parse_if_blocks('search_results_list_row_data_distance.html', $html1, true);
                                                        $html1 = stripslashes($html1);
                                                        $html1 = addslashes($html1);
                                                        $html1 = str_replace('$', '\$', $html1);
                                                        eval('$rows .= "' . $html1 . '";');
                                                        unset($html1);
                                                }
                                                // #### average bid column
                                                else if ($column == 'averagebid')
                                                {
                                                        $value['average'] = (($this->can_display_element('currencyconvert'))
                                                                ? $value['averagebid']
                                                                : $value['averagebid_plain']);
                                                        $html1 = $this->fetch_template('search_results_list_row_data_average.html');
                                                        $html1 = $this->parse_hash('search_results_list_row_data_average.html', array('value' => $value), 0, $html1);
                                                        $html1 = $this->parse_if_blocks('search_results_list_row_data_average.html', $html1, true);
                                                        $html1 = stripslashes($html1);
                                                        $html1 = addslashes($html1);
                                                        $html1 = str_replace('$', '\$', $html1);
                                                        eval('$rows .= "' . $html1 . '";');
                                                        unset($html1);
                                                }
                                                // #### expert title column
                                                else if ($column == 'expert')
                                                {
                                                        $value['online'] = (($this->can_display_element('online')) ? $value['isonline'] : '');
                                                        $value['latestfeedback'] = (($this->can_display_element('latestfeedback') AND !empty($value['latestfeedback']))
                                                                ? $value['latestfeedback']
                                                                : '');
                                                        $value['skills'] = (($ilconfig['enableskills'])
                                                                ? $value['skills']
                                                                : '');
                                                        $value['profileintro'] = $value['profileintro'];
                                                        $value['location'] = $value['location'];
                                                        $html1 = $this->fetch_template('search_results_list_row_data_expert.html');
                                                        $html1 = $this->parse_hash('search_results_list_row_data_expert.html', array('value' => $value), 0, $html1);
                                                        $html1 = $this->parse_if_blocks('search_results_list_row_data_expert.html', $html1, true);
                                                        $html1 = stripslashes($html1);
                                                        $html1 = addslashes($html1);
                                                        $html1 = str_replace('$', '\$', $html1);
                                                        eval('$rows .= "' . $html1 . '";');
                                                        unset($html1);
                                                }
                                                // #### everything else column
                                                else
                                                {
                                                        $value['column'] = isset($value["$column"]) ? $value["$column"] : '';
                                                        $html1 = $this->fetch_template('search_results_list_row_data.html');
                                                        $html1 = $this->parse_hash('search_results_list_row_data.html', array('value' => $value), 0, $html1);
                                                        $html1 = $this->parse_if_blocks('search_results_list_row_data.html', $html1, true);
                                                        $html1 = stripslashes($html1);
                                                        $html1 = addslashes($html1);
                                                        $html1 = str_replace('$', '\$', $html1);
                                                        eval('$rows .= "' . $html1 . '";');
                                                        unset($html1);
                                                }
                                        }
                                }
                                $html1 = $this->fetch_template('search_results_list_row_footer.html');
                                //$html1 = $this->parse_hash('search_results_list_row_footer.html', array('ilconfig' => $ilconfig, 'ilpage' => $ilpage, 'value' => $value), 0, $html1);
                                //$html1 = $this->parse_if_blocks('search_results_list_row_footer.html', $html1, true);
                                $html1 = stripslashes($html1);
                                $html1 = addslashes($html1);
                                $html1 = str_replace('$', '\$', $html1);
                                eval('$rows .= "' . $html1 . '";');
                                unset($html1);
                        }
                        $rows = stripslashes($rows);
                }
                return $rows;
        }
        
        /*
        * Function to determine if a logged in member is displaying a certain column or information bit
        * within their search result.  This will ultimately hide or show that bit from their selected search
        * options from the advanced search menu
        *
        * @param        string        display option name
        *
        * @return       boolean       Returns true or false if we can display the element     
        */
        function can_display_element($option = '')
        {
                if (!empty($_SESSION['ilancedata']['user']['searchoptions']))
                {
                        $temp = unserialize($_SESSION['ilancedata']['user']['searchoptions']);
                        if (isset($temp[$option]) AND $temp[$option] == 'true')
                        {
                                return true;
                        }
                        
                        return false;
                }
                else
                {
                        // default everything enabled (just in case)
                        return true;
                }
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>