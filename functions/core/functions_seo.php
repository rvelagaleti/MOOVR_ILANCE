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
* SEO - Search Engine Optimization functions for iLance
*
* @package      iLance\Global\SEO
* @version	4.0.0.8059
* @author       ILance
*/

/**
* Function to parse a valid SEO (search engine optimized) url
*
* @param       string         text
*
* @return      integer        Returns the url
*/
function print_seo_url($string = '')
{
        global $ilconfig;
        if ($ilconfig['seourls_lowercase'])
        {
                $string = mb_strtolower($string);        
        }
        return $string;
}

/**
* Function to generate a valid search engine friendly url
*
* @param       string         url logic type
* @param       integer        category id
* @param       integer        auction id
* @param       string         title of link (ie: Need design done for a project)
* @param       string         (optional) custom link
* @param       bool           force bold titles
* @param       string         search question
* @param       integer        search question id
* @param       integer        search answer id
* @param       string         additional variables to exclude from a link (optional), eg: " ,'qid' "
* @param       string         additional a href include (optional)
* @param       string         short string of the link title (ie: need-design-done-for...)
*
* @return      integer        Returns search engine friendly url (ie: itemc9-domain-names-1-year.html)
*/
function construct_seo_url($type = '', $catid = 0, $auctionid = 0, $name = '', $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0, $removevar = '', $extrahref = '', $cutoffname = '')
{
	global $ilance, $ilconfig, $show, $php_self;
        $url = '';
	if (isset($type))
	{
		if (!empty($customlink))
		{
			if (isset($bold) AND $bold > 0)
			{
				$urlname = $name;
				$urlname = preg_replace("/[\n\r]/", "", $urlname);
				if ($cutoffname != '')
				{
					$lnkname = '<strong>' . $cutoffname . '</strong>';
				}
				else
				{
					$lnkname = '<strong>' . $customlink . '</strong>';
				}
			}
			else
			{
				$urlname = $name;
				$urlname = preg_replace("/[\n\r]/", "", $urlname);
				if ($cutoffname != '')
				{
					$lnkname = $cutoffname;
				}
				else
				{
					$lnkname = $customlink;
				}
			}
		}
		else
		{
			if (isset($bold) AND $bold > 0)
			{
				$urlname = $name;
				$urlname = preg_replace("/[\n\r]/", "", $urlname);
				if ($cutoffname != '')
				{
					$lnkname = '<strong>' . $cutoffname . '</strong>';
				}
				else
				{
					$lnkname = '<strong>' . $name . '</strong>';
				}
			}
			else
			{
				$urlname = $name;
				$urlname = preg_replace("/[\n\r]/", "", $urlname);
				if ($cutoffname != '')
				{
					$lnkname = $cutoffname;
				}
				else
				{
					$lnkname = $name;
				}
			}
		}
		$name = handle_input_keywords($name);
		$keywords = '';
		if (!empty($ilance->GPC['q']))
		{
			//$keywords = preg_replace('/[^A-Za-z0-9- ]+/', '', $ilance->GPC['q']);
			$keywords = (!empty($keywords)) ? construct_seo_url_name($keywords, $forcenolowercase = true) . '_' : '';
		}
		switch ($type)
		{
			case 'servicecat':
			{
				$schema = '<a href="' . $ilconfig['servicecatschema'] . '" ' . $extrahref . ' title="' . $name . '">{LINKNAME}</a>';
				$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
				$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
				$schema = str_replace('{KEYWORDS}', $keywords, $schema);
				$schema = str_replace('{CATEGORY}', construct_seo_url_name($urlname), $schema);
				$schema = str_replace('{CATEGORYLOWERCASE}', construct_seo_url_name(mb_strtolower($urlname)), $schema);
				$schema = str_replace('{IDENTIFIER}', print_seo_url($ilconfig['servicecatidentifier']), $schema);
				$schema = str_replace('{CID}', $catid, $schema);
				$urlbit = (isset($show['nourlbit']) AND $show['nourlbit']) ? '' : print_hidden_fields(true, array('do','page','mode','cid','cmd','state','id','' . $removevar . ''), true, '', '', $htmlentities = true, $urldecode = true);
				$schema = str_replace('{URLBIT}', $urlbit, $schema);
				$schema = str_replace('{LINKNAME}', $lnkname, $schema);
				$url = $schema;
				unset($schema);
				break;
			}
			case 'servicecatplain':
			{
				$schema = $ilconfig['servicecatschema'];
				$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
				$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
				$schema = str_replace('{KEYWORDS}', $keywords, $schema);
				$schema = str_replace('{CATEGORY}', construct_seo_url_name($urlname), $schema);
				$schema = str_replace('{CATEGORYLOWERCASE}', construct_seo_url_name(mb_strtolower($urlname)), $schema);
				$schema = str_replace('{IDENTIFIER}', print_seo_url($ilconfig['servicecatidentifier']), $schema);
				$schema = str_replace('{CID}', $catid, $schema);
				$urlbit = (isset($show['nourlbit']) AND $show['nourlbit']) ? '' : print_hidden_fields(true, array('do','page','mode','cid','cmd','state','id','' . $removevar . ''), true, '', '', $htmlentities = true, $urldecode = true); 
				$schema = str_replace('{URLBIT}', $urlbit, $schema);
				$url = $schema;
				unset($schema);
				break;
			}
			case 'productcat':
			{
				$schema = '<a href="' . $ilconfig['productcatschema'] . '" ' . $extrahref . ' title="' . $name . '">{LINKNAME}</a>';
				$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
				$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
				$schema = str_replace('{KEYWORDS}', $keywords, $schema);
				$schema = str_replace('{CATEGORY}', construct_seo_url_name($urlname), $schema);
				$schema = str_replace('{CATEGORYLOWERCASE}', construct_seo_url_name(mb_strtolower($urlname)), $schema);
				$schema = str_replace('{IDENTIFIER}', print_seo_url($ilconfig['productcatidentifier']), $schema);
				$schema = str_replace('{CID}', $catid, $schema);
				$urlbit = (isset($show['nourlbit']) AND $show['nourlbit']) ? '' : print_hidden_fields(true, array('do','page','mode','cid','cmd','state','id','' . $removevar . ''), true, '', '', $htmlentities = true, $urldecode = true);
				$schema = str_replace('{URLBIT}', $urlbit, $schema);
				$schema = str_replace('{LINKNAME}', $lnkname, $schema);
				$url = $schema;
				unset($schema);
				break;
			}                
			case 'productcatplain':
			{
				$schema = $ilconfig['productcatschema'];
				$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
				$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
				$schema = str_replace('{KEYWORDS}', $keywords, $schema);
				$schema = str_replace('{CATEGORY}', construct_seo_url_name($urlname), $schema);
				$schema = str_replace('{CATEGORYLOWERCASE}', construct_seo_url_name(mb_strtolower($urlname)), $schema);
				$schema = str_replace('{IDENTIFIER}', print_seo_url($ilconfig['productcatidentifier']), $schema);
				$schema = str_replace('{CID}', $catid, $schema);
				$urlbit = (isset($show['nourlbit']) AND $show['nourlbit']) ? '' : print_hidden_fields(true, array('do','page','mode','cid','cmd','state','id','' . $removevar . ''), true, '', '', $htmlentities = true, $urldecode = true);
				$schema = str_replace('{URLBIT}', $urlbit, $schema);
				$url = $schema;
				unset($schema);
				break;
			}                
			case 'servicecatmap':
			{
				$schema = '<a href="' . $ilconfig['servicecatschema'] . '" ' . $extrahref . ' title="' . $name . '">{LINKNAME}</a>';
				$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
				$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
				$schema = str_replace('{KEYWORDS}', $keywords, $schema);
				$schema = str_replace('{CATEGORY}', construct_seo_url_name($urlname), $schema);
				$schema = str_replace('{CATEGORYLOWERCASE}', construct_seo_url_name(mb_strtolower($urlname)), $schema);
				$schema = str_replace('{IDENTIFIER}', print_seo_url($ilconfig['servicecatmapidentifier']), $schema);
				$schema = str_replace('{CID}', $catid, $schema);
				$urlbit = (isset($show['nourlbit']) AND $show['nourlbit']) ? '' : '';
				$schema = str_replace('{URLBIT}', $urlbit, $schema);
				$schema = str_replace('{LINKNAME}', $lnkname, $schema);
				$url = $schema;
				unset($schema);
				break;
			}                
			case 'productcatmap':
			{
				$schema = '<a href="' . $ilconfig['productcatschema'] . '" ' . $extrahref . ' title="' . $name . '">{LINKNAME}</a>'; 
				$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
				$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
				$schema = str_replace('{KEYWORDS}', $keywords, $schema);
				$schema = str_replace('{CATEGORY}', construct_seo_url_name($urlname), $schema);
				$schema = str_replace('{CATEGORYLOWERCASE}', construct_seo_url_name(mb_strtolower($urlname)), $schema);
				$schema = str_replace('{IDENTIFIER}', print_seo_url($ilconfig['productcatmapidentifier']), $schema);
				$schema = str_replace('{CID}', $catid, $schema);
				$urlbit = (isset($show['nourlbit']) AND $show['nourlbit']) ? '' : '';
				$schema = str_replace('{URLBIT}', $urlbit, $schema);
				$schema = str_replace('{LINKNAME}', $lnkname, $schema);
				$url = $schema;
				unset($schema);
				break;
			}                
			case 'servicecatmapplain':
			{
				$schema = $ilconfig['servicecatschema'];
				$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
				$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
				$schema = str_replace('{KEYWORDS}', $keywords, $schema);
				$schema = str_replace('{CATEGORY}', construct_seo_url_name($urlname), $schema);
				$schema = str_replace('{CATEGORYLOWERCASE}', construct_seo_url_name(mb_strtolower($urlname)), $schema);
				$schema = str_replace('{IDENTIFIER}', print_seo_url($ilconfig['servicecatmapidentifier']), $schema);
				$schema = str_replace('{CID}', $catid, $schema);
				$urlbit = (isset($show['nourlbit']) AND $show['nourlbit']) ? '' : '';
				$schema = str_replace('{URLBIT}', $urlbit, $schema);
				$url = $schema;
				unset($schema);
				break;
			}
			case 'productcatmapplain':
			{
				$schema = $ilconfig['productcatschema'];
				$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
				$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
				$schema = str_replace('{KEYWORDS}', $keywords, $schema);
				$schema = str_replace('{CATEGORY}', construct_seo_url_name($urlname), $schema);
				$schema = str_replace('{CATEGORYLOWERCASE}', construct_seo_url_name(mb_strtolower($urlname)), $schema);
				$schema = str_replace('{IDENTIFIER}', print_seo_url($ilconfig['productcatmapidentifier']), $schema);
				$schema = str_replace('{CID}', $catid, $schema);
				$urlbit = (isset($show['nourlbit']) AND $show['nourlbit']) ? '' : '';
				$schema = str_replace('{URLBIT}', $urlbit, $schema);
				$url = $schema;
				unset($schema);
				break;
			}		    
			case 'servicesearchquestion':
			{
				$schema = '<a href="' . $ilconfig['servicecatschema'] . '" ' . $extrahref . '>{LINKNAME}</a>';
				$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
				$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
				$schema = str_replace('{KEYWORDS}', $keywords, $schema);
				$schema = str_replace('{CATEGORY}', construct_seo_url_name($urlname), $schema);
				$schema = str_replace('{CATEGORYLOWERCASE}', construct_seo_url_name(mb_strtolower($urlname)), $schema);
				$schema = str_replace('{IDENTIFIER}', print_seo_url($ilconfig['servicecatidentifier']), $schema);
				$schema = str_replace('{CID}', $catid, $schema);
				$urlbit = (isset($show['nourlbit']) AND $show['nourlbit']) ? '' : print_hidden_fields(true, array('qid','do','page','mode','cid','cmd','' . $removevar . ''), true, '', '', $htmlentities = true, $urldecode = true);
				$qidbit = '';
				$qidbit = (isset($ilance->GPC['qid']) AND !empty($ilance->GPC['qid'])) ? $ilance->GPC['qid'] . ',' . $questionid . '.' . $answerid : $questionid . '.' . $answerid;
				if (empty($urlbit))
				{
					$urlbit = '?qid=' . $qidbit;
				}
				else
				{
					$urlbit .= '&amp;qid=' . $qidbit;
				}
				$schema = str_replace('{URLBIT}', $urlbit, $schema);
				$schema = str_replace('{LINKNAME}', $searchquestion, $schema);
				$url = $schema;
				unset($schema);
				break;
			}
			case 'productsearchquestion':
			{
				$schema = '<a href="' . $ilconfig['productcatschema'] . '" ' . $extrahref . '>{LINKNAME}</a>';
				$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
				$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
				$schema = str_replace('{KEYWORDS}', $keywords, $schema);
				$schema = str_replace('{CATEGORY}', construct_seo_url_name($urlname), $schema);
				$schema = str_replace('{CATEGORYLOWERCASE}', construct_seo_url_name(mb_strtolower($urlname)), $schema);
				$schema = str_replace('{IDENTIFIER}', print_seo_url($ilconfig['productcatidentifier']), $schema);
				$schema = str_replace('{CID}', $catid, $schema);
				$urlbit = (isset($show['nourlbit']) AND $show['nourlbit']) ? '' : print_hidden_fields(true, array('qid','do','page','mode','cid','cmd','' . $removevar . ''), true, '', '', $htmlentities = true, $urldecode = true);
				$qidbit = '';
				$qidbit = (isset($ilance->GPC['qid']) AND !empty($ilance->GPC['qid'])) ? $ilance->GPC['qid'] . ',' . $questionid . '.' . $answerid : $questionid . '.' . $answerid;
				if (empty($urlbit))
				{
					$urlbit = '?qid=' . $qidbit;
				}
				else
				{
					$urlbit .= '&amp;qid=' . $qidbit;
				}
				$schema = str_replace('{URLBIT}', $urlbit, $schema);
				$schema = str_replace('{LINKNAME}', $searchquestion, $schema);
				$url = $schema;
				unset($schema);
				break;
			}
			case 'serviceprovidercat':
			{
				$schema = '<a href="' . $ilconfig['servicecatschema'] . '" ' . $extrahref . ' title="' . $name . '">{LINKNAME}</a>';
				$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
				$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
				$schema = str_replace('{KEYWORDS}', $keywords, $schema);
				$schema = str_replace('{CATEGORY}', construct_seo_url_name($urlname), $schema);
				$schema = str_replace('{CATEGORYLOWERCASE}', construct_seo_url_name(mb_strtolower($urlname)), $schema);
				$schema = str_replace('{IDENTIFIER}', print_seo_url($ilconfig['expertslistingidentifier']), $schema);
				$schema = str_replace('{CID}', $catid, $schema);
				$urlbit = (isset($show['nourlbit']) AND $show['nourlbit']) ? '' : print_hidden_fields(true, array('do','page','mode','cid','cmd','' . $removevar . ''), true, '', '', $htmlentities = true, $urldecode = true);
				$schema = str_replace('{URLBIT}', $urlbit, $schema);
				$schema = str_replace('{LINKNAME}', $lnkname, $schema);
				$url = $schema;
				unset($schema);
				break;
			}
			case 'serviceprovidercatplain':
			{
				$schema = $ilconfig['servicecatschema'];
				$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
				$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
				$schema = str_replace('{KEYWORDS}', $keywords, $schema);
				$schema = str_replace('{CATEGORY}', construct_seo_url_name($urlname), $schema);
				$schema = str_replace('{CATEGORYLOWERCASE}', construct_seo_url_name(mb_strtolower($urlname)), $schema);
				$schema = str_replace('{IDENTIFIER}', print_seo_url($ilconfig['expertslistingidentifier']), $schema);
				$schema = str_replace('{CID}', $catid, $schema);
				$urlbit = (isset($show['nourlbit']) AND $show['nourlbit']) ? '' : print_hidden_fields(true, array('do','page','mode','cid','cmd','' . $removevar . ''), true, '', '', $htmlentities = true, $urldecode = true);
				$schema = str_replace('{URLBIT}', $urlbit, $schema);
				$url = $schema;
				unset($schema);
				break;
			}
			case 'serviceauction':
			{
				$schema = '<a href="' . $ilconfig['servicelistingschema'] . '" ' . $extrahref . ' title="' . $name . '">{LINKNAME}</a>';
				$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
				$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
				$schema = str_replace('{KEYWORDS}', '', $schema);
				$schema = str_replace('{CATEGORY}', construct_seo_url_name($urlname), $schema);
				$schema = str_replace('{CATEGORYLOWERCASE}', construct_seo_url_name(mb_strtolower($urlname)), $schema);
				$schema = str_replace('{IDENTIFIER}', print_seo_url($ilconfig['servicelistingidentifier']), $schema);
				$schema = str_replace('{CID}', $catid, $schema);
				$schema = str_replace('{ID}', $auctionid, $schema);
				$urlbit = (isset($show['nourlbit']) AND $show['nourlbit']) ? '' : '';
				$schema = str_replace('{URLBIT}', $urlbit, $schema);
				$schema = str_replace('{LINKNAME}', $lnkname, $schema);
				$url = $schema;
				unset($schema);
				break;
			}
			case 'serviceauctionplain':
			{
				$schema = $ilconfig['servicelistingschema'];
				$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
				$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
				$schema = str_replace('{KEYWORDS}', '', $schema);
				$schema = str_replace('{CATEGORY}', construct_seo_url_name($urlname), $schema);
				$schema = str_replace('{CATEGORYLOWERCASE}', construct_seo_url_name(mb_strtolower($urlname)), $schema);
				$schema = str_replace('{IDENTIFIER}', print_seo_url($ilconfig['servicelistingidentifier']), $schema);
				$schema = str_replace('{CID}', $catid, $schema);
				$schema = str_replace('{ID}', $auctionid, $schema);
				$urlbit = (isset($show['nourlbit']) AND $show['nourlbit']) ? '' : '';
				$schema = str_replace('{URLBIT}', $urlbit, $schema);
				$url = $schema;
				unset($schema);
				break;
			}
			case 'productauction':
			{
				$schema = '<a href="' . $ilconfig['productlistingschema'] . '" ' . $extrahref . ' title="' . $name . '">{LINKNAME}</a>';
				$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
				$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
				$schema = str_replace('{KEYWORDS}', '', $schema);
				$schema = str_replace('{CATEGORY}', construct_seo_url_name($urlname), $schema);
				$schema = str_replace('{CATEGORYLOWERCASE}', construct_seo_url_name(mb_strtolower($urlname)), $schema);
				$schema = str_replace('{IDENTIFIER}', print_seo_url($ilconfig['productlistingidentifier']), $schema);
				$schema = str_replace('{CID}', $catid, $schema);
				$schema = str_replace('{ID}', $auctionid, $schema);
				$urlbit = (isset($show['nourlbit']) AND $show['nourlbit']) ? '' : '';
				$schema = str_replace('{URLBIT}', $urlbit, $schema);
				$schema = str_replace('{LINKNAME}', $lnkname, $schema);
				$url = $schema;
				unset($schema);
				break;
			}
			case 'productauctionplain':
			{
				$schema = $ilconfig['productlistingschema'];
				$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
				$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
				$schema = str_replace('{KEYWORDS}', '', $schema);
				$schema = str_replace('{CATEGORY}', construct_seo_url_name($urlname), $schema);
				$schema = str_replace('{CATEGORYLOWERCASE}', construct_seo_url_name(mb_strtolower($urlname)), $schema);
				$schema = str_replace('{IDENTIFIER}', print_seo_url($ilconfig['productlistingidentifier']), $schema);
				$schema = str_replace('{CID}', $catid, $schema);
				$schema = str_replace('{ID}', $auctionid, $schema);
				$urlbit = (isset($show['nourlbit']) AND $show['nourlbit']) ? '' : '';
				$schema = str_replace('{URLBIT}', $urlbit, $schema);
				$url = $schema;
				unset($schema);
				break;
			}
			case 'wantadscatlistings':
			{
				$schema = '<a href="' . $ilconfig['servicecatschema'] . '" ' . $extrahref . ' title="' . $name . '">{LINKNAME}</a>';
				$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
				$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
				$schema = str_replace('{KEYWORDS}', $keywords, $schema);
				$schema = str_replace('{CATEGORY}', construct_seo_url_name($urlname), $schema);
				$schema = str_replace('{CATEGORYLOWERCASE}', construct_seo_url_name(mb_strtolower($urlname)), $schema);
				$schema = str_replace('{IDENTIFIER}', print_seo_url('WantAds/Categories'), $schema);
				$schema = str_replace('{CID}', $catid, $schema);
				$urlbit = (isset($show['nourlbit']) AND $show['nourlbit']) ? '' : print_hidden_fields($string = true, $excluded = array('do','page','mode','cid','cmd','state','id','' . $removevar . ''), $questionmarkfirst = true, $prepend_text = '', $append_text = '', $htmlentities = true, $urldecode = true);
				$schema = str_replace('{URLBIT}', $urlbit, $schema);
				$schema = str_replace('{LINKNAME}', $lnkname, $schema);
				$url = $schema;
				unset($schema);
				break;
			}
			case 'wantadscatlistingsplain':
			{
				$schema = $ilconfig['servicecatschema'];
				$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
				$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
				$schema = str_replace('{KEYWORDS}', $keywords, $schema);
				$schema = str_replace('{CATEGORY}', construct_seo_url_name($urlname), $schema);
				$schema = str_replace('{CATEGORYLOWERCASE}', construct_seo_url_name(mb_strtolower($urlname)), $schema);
				$schema = str_replace('{IDENTIFIER}', print_seo_url('WantAds/Categories'), $schema);
				$schema = str_replace('{CID}', $catid, $schema);
				$urlbit = (isset($show['nourlbit']) AND $show['nourlbit']) ? '' : print_hidden_fields($string = true, $excluded = array('do','page','mode','cid','cmd','state','id','' . $removevar . ''), $questionmarkfirst = true, $prepend_text = '', $append_text = '', $htmlentities = true, $urldecode = true);
				$schema = str_replace('{URLBIT}', $urlbit, $schema);
				$url = $schema;
				unset($schema);
				break;
			}
			case 'store':
			{
				if ($catid > 0)
				{
					$schema = '<a href="{HTTP_SERVER}{IDENTIFIER}/{ID}/{STORENAME}/{CATIDENTIFIER}/{CID}/{CATEGORY}{URLBIT}" ' . $extrahref . ' title="' . $name . '">{LINKNAME}</a>';
				}
				else
				{
					$schema = '<a href="{HTTP_SERVER}{IDENTIFIER}/{ID}/{STORENAME}{URLBIT}" ' . $extrahref . ' title="' . $name . '">{LINKNAME}</a>';
				}
				$storename = $ilance->db->fetch_field(DB_PREFIX . 'stores', "storeid = '" . intval($auctionid) . "'", 'storename');
				$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
				$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
				$schema = str_replace('{KEYWORDS}', $keywords, $schema);
				$schema = str_replace('{CATEGORY}', construct_seo_url_name($urlname), $schema);
				$schema = str_replace('{CATEGORYLOWERCASE}', construct_seo_url_name(mb_strtolower($urlname)), $schema);
				$schema = str_replace('{IDENTIFIER}', print_seo_url('Stores'), $schema);
				$schema = str_replace('{CATIDENTIFIER}', print_seo_url($ilconfig['categoryidentifier']), $schema);
				$schema = str_replace('{STORENAME}', construct_seo_url_name($storename), $schema);
				$schema = str_replace('{CID}', $catid, $schema);
				$schema = str_replace('{ID}', $auctionid, $schema);
				$urlbit = (isset($show['nourlbit']) AND $show['nourlbit']) ? '' : print_hidden_fields($string = true, $excluded = array('do','page','mode','cid','cmd','state','id','' . $removevar . ''), $questionmarkfirst = true, $prepend_text = '', $append_text = '', $htmlentities = true, $urldecode = true);
				$schema = str_replace('{URLBIT}', $urlbit, $schema);
				$schema = str_replace('{LINKNAME}', $lnkname, $schema);
				$url = $schema;
				unset($schema);
				break;
			}
			case 'storehomepage':
			{
				$storename = $ilance->db->fetch_field(DB_PREFIX . 'stores', "storeid = '" . intval($auctionid) . "'", 'storename');
				$schema = '<a href="{HTTP_SERVER}{IDENTIFIER}/{ID}/{STORENAME}{URLBIT}" ' . $extrahref . ' title="' . $storename . '">{LINKNAME}</a>';
				$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
				$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
				$schema = str_replace('{KEYWORDS}', $keywords, $schema);
				$schema = str_replace('{CATEGORY}', construct_seo_url_name($urlname), $schema);
				$schema = str_replace('{CATEGORYLOWERCASE}', construct_seo_url_name(mb_strtolower($urlname)), $schema);
				$schema = str_replace('{IDENTIFIER}', print_seo_url('Stores'), $schema);
				$schema = str_replace('{CATIDENTIFIER}', print_seo_url($ilconfig['categoryidentifier']), $schema);
				$schema = str_replace('{STORENAME}', construct_seo_url_name($storename) . '/' . print_seo_url('Home'), $schema);
				$schema = str_replace('{CID}', $catid, $schema);
				$schema = str_replace('{ID}', $auctionid, $schema);
				$urlbit = (isset($show['nourlbit']) AND $show['nourlbit']) ? '' : print_hidden_fields($string = true, $excluded = array('do','page','mode','cid','cmd','state','id','' . $removevar . ''), $questionmarkfirst = true, $prepend_text = '', $append_text = '', $htmlentities = true, $urldecode = true);
				$schema = str_replace('{URLBIT}', $urlbit, $schema);
				$schema = str_replace('{LINKNAME}', $lnkname, $schema);
				$url = $schema;
				unset($schema);
				break;
			}
			case 'storeplain':
			{
				if ($catid > 0)
				{
					$schema = '{HTTP_SERVER}{IDENTIFIER}/{ID}/{STORENAME}/{CATIDENTIFIER}/{CID}/{CATEGORY}{URLBIT}';
				}
				else
				{
					$schema = '{HTTP_SERVER}{IDENTIFIER}/{ID}/{STORENAME}{URLBIT}';
				}
				$storename = $ilance->db->fetch_field(DB_PREFIX . 'stores', "storeid = '" . intval($auctionid) . "'", 'storename');
				$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
				$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
				$schema = str_replace('{KEYWORDS}', $keywords, $schema);
				$schema = str_replace('{CATEGORY}', construct_seo_url_name($urlname), $schema);
				$schema = str_replace('{CATEGORYLOWERCASE}', construct_seo_url_name(mb_strtolower($urlname)), $schema);
				$schema = str_replace('{IDENTIFIER}', print_seo_url('Stores'), $schema);
				$schema = str_replace('{CATIDENTIFIER}', print_seo_url($ilconfig['categoryidentifier']), $schema);
				$schema = str_replace('{CID}', $catid, $schema);
				$schema = str_replace('{STORENAME}', construct_seo_url_name($storename), $schema);
				$schema = str_replace('{ID}', $auctionid, $schema);
				$urlbit = (isset($show['nourlbit']) AND $show['nourlbit']) ? '' : print_hidden_fields($string = true, $excluded = array('do','page','mode','cid','cmd','state','id','' . $removevar . ''), $questionmarkfirst = true, $prepend_text = '', $append_text = '', $htmlentities = true, $urldecode = true);       
				$schema = str_replace('{URLBIT}', $urlbit, $schema);
				$url = $schema;
				unset($schema);
				break;
			}
			case 'storescatlistings':
			{
				$schema = '<a href="' . $ilconfig['servicecatschema'] . '" ' . $extrahref . ' title="' . $name . '">{LINKNAME}</a>';
				$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
				$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
				$schema = str_replace('{KEYWORDS}', $keywords, $schema);
				$schema = str_replace('{CATEGORY}', construct_seo_url_name($urlname), $schema);
				$schema = str_replace('{CATEGORYLOWERCASE}', construct_seo_url_name(mb_strtolower($urlname)), $schema);
				$schema = str_replace('{IDENTIFIER}', print_seo_url('Stores/Categories'), $schema);
				$schema = str_replace('{CID}', $catid, $schema);
				$urlbit = (isset($show['nourlbit']) AND $show['nourlbit']) ? '' : print_hidden_fields($string = true, $excluded = array('do','page','mode','cid','cmd','state','id','' . $removevar . ''), $questionmarkfirst = true, $prepend_text = '', $append_text = '', $htmlentities = true, $urldecode = true);
				$schema = str_replace('{URLBIT}', $urlbit, $schema);
				$schema = str_replace('{LINKNAME}', $lnkname, $schema);
				$url = $schema;
				unset($schema);
				//$url = '<a href="' . HTTP_SERVER . print_seo_url('Stores') . '/' . $catid . '/' . construct_seo_url_name($urlname) . '">' . $lnkname . '</a>';
				break;
			}
			case 'storescatlistingsplain':
			{
				$schema = $ilconfig['servicecatschema'];
				$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
				$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
				$schema = str_replace('{KEYWORDS}', $keywords, $schema);
				$schema = str_replace('{CATEGORY}', construct_seo_url_name($urlname), $schema);
				$schema = str_replace('{CATEGORYLOWERCASE}', construct_seo_url_name(mb_strtolower($urlname)), $schema);
				$schema = str_replace('{IDENTIFIER}', print_seo_url('Stores/Categories'), $schema);
				$schema = str_replace('{CID}', $catid, $schema);
				$urlbit = (isset($show['nourlbit']) AND $show['nourlbit']) ? '' : print_hidden_fields($string = true, $excluded = array('do','page','mode','cid','cmd','state','id','' . $removevar . ''), $questionmarkfirst = true, $prepend_text = '', $append_text = '', $htmlentities = true, $urldecode = true);
				$schema = str_replace('{URLBIT}', $urlbit, $schema);
				$url = $schema;
				unset($schema);
				break;
			}
			case 'storescat':
			{
				$schema = '<a href="' . $ilconfig['servicecatschema'] . '" ' . $extrahref . ' title="' . $name . '">{LINKNAME}</a>';
				$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
				$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
				$schema = str_replace('{KEYWORDS}', $keywords, $schema);
				$schema = str_replace('{CATEGORY}', construct_seo_url_name($urlname), $schema);
				$schema = str_replace('{CATEGORYLOWERCASE}', construct_seo_url_name(mb_strtolower($urlname)), $schema);
				$schema = str_replace('{IDENTIFIER}', print_seo_url('Stores/Categories'), $schema);
				$schema = str_replace('{CID}', $catid, $schema);
				$urlbit = (isset($show['nourlbit']) AND $show['nourlbit']) ? '' : print_hidden_fields($string = true, $excluded = array('do','page','mode','cid','cmd','state','id','' . $removevar . ''), $questionmarkfirst = true, $prepend_text = '', $append_text = '', $htmlentities = true, $urldecode = true);
				$schema = str_replace('{URLBIT}', $urlbit, $schema);
				$schema = str_replace('{LINKNAME}', $lnkname, $schema);
				$url = $schema;
				unset($schema);
				break;
			}
			case 'storelisting':
			{
				$schema = '<a href="{HTTP_SERVER}{IDENTIFIER}/{ITEMID}/{CATEGORY}{URLBIT}" ' . $extrahref . ' title="' . $name . '">{LINKNAME}</a>';
				$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
				$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
				$schema = str_replace('{KEYWORDS}', '', $schema);
				$schema = str_replace('{CATEGORY}', construct_seo_url_name($urlname), $schema);
				$schema = str_replace('{CATEGORYLOWERCASE}', construct_seo_url_name(mb_strtolower($urlname)), $schema);
				$schema = str_replace('{IDENTIFIER}', print_seo_url('Stores/Item'), $schema);
				$schema = str_replace('{ITEMID}', $catid, $schema);
				$schema = str_replace('{ID}', $auctionid, $schema);
				$urlbit = (isset($show['nourlbit']) AND $show['nourlbit']) ? '' : '';
				$schema = str_replace('{URLBIT}', $urlbit, $schema);
				$schema = str_replace('{LINKNAME}', $lnkname, $schema);
				$url = $schema;
				unset($schema);
				break;
			}
			case 'storelistingplain':
			{
				$schema = '{HTTP_SERVER}{IDENTIFIER}/{ITEMID}/{CATEGORY}{URLBIT}';
				$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
				$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
				$schema = str_replace('{KEYWORDS}', '', $schema);
				$schema = str_replace('{CATEGORY}', construct_seo_url_name($urlname), $schema);
				$schema = str_replace('{CATEGORYLOWERCASE}', construct_seo_url_name(mb_strtolower($urlname)), $schema);
				$schema = str_replace('{IDENTIFIER}', print_seo_url('Stores/Item'), $schema);
				$schema = str_replace('{ITEMID}', $catid, $schema);
				$schema = str_replace('{ID}', $auctionid, $schema);
				$urlbit = (isset($show['nourlbit']) AND $show['nourlbit']) ? '' : '';
				$schema = str_replace('{URLBIT}', $urlbit, $schema);
				$url = $schema;
				unset($schema);
				break;
			}
			case 'portfoliocat':
			{
				$url = '<a href="' . HTTP_SERVER . print_seo_url($ilconfig['portfolioslistingidentifier']) . '/' . $catid . '/' . construct_seo_url_name($urlname) . '" ' . $extrahref . ' title="' . $name . '">' . $lnkname . '</a>';
				break;
			}                
			case 'portfoliocatplain':
			{
				$url = HTTP_SERVER . print_seo_url($ilconfig['portfolioslistingidentifier']) . '/' . $catid . '/' . construct_seo_url_name($urlname);
				break;
			}                
			case 'portfoliocatmap':
			{
				$url = '<a href="' . HTTP_SERVER . print_seo_url('Categories/Portfolios') . '/' . $catid . '/' . construct_seo_url_name($urlname) . '" title="' . $name . '">' . $lnkname . '</a>';
				break;
			}                
			case 'portfoliocatmapplain':
			{
				$url = HTTP_SERVER . print_seo_url('Categories/Portfolios') . '/' . $catid . '/' . construct_seo_url_name($urlname);
				break;
			}
		}
                
		($apihook = $ilance->api('construct_seo_url_end')) ? eval($apihook) : false;
	}
	return str_replace(LINEBREAK, '', $url);
}

function utf8_seo_url($string, $separator = '-', $extra = null)
{
	return trim(preg_replace('~[^0-9a-zA-Z' . preg_quote($extra, '~') . ']+~i', $separator, html_entity_decode(preg_replace('~&([a-zA-Z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), $separator);
}

/**
* Function to generate a valid search engine friendly url name (replaces spaces with underscores, etc)
*
* @param       string         text
* @param       boolean        force preventing text from being converted to lower case? (default false)
*
* @return      integer        Returns the url text formatted for any web browser url bar
*/
function construct_seo_url_name($text = '', $forcenolowercase = false)
{
        global $ilance, $phrase, $ilconfig, $show;
        $text = &$text;
        $replacements = $ilance->language->fetch_seo_replacements($_SESSION['ilancedata']['user']['languageid']);
        if (!empty($replacements))
        {
                $replacement = explode(', ', $replacements);
                foreach ($replacement AS $set)
                {
                        if (!empty($set))
                        {
                                $value = explode('|', $set);
                                if (!empty($value[0]) AND !empty($value[1]))
                                {
                                        $text = str_replace($value[0], $value[1], $text);
                                }
                        }
                }
        }
	if ($ilconfig['seourls_utf8'])
	{
		$text = str_replace('quot', '', $text);
		$text = str_replace(' ', '-', $text);
		$text = str_replace('?', '', $text);
		$text = str_replace('%', '', $text);
		$text = str_replace('/', '-', $text);
		$text = str_replace('\\', '', $text);
		$text = str_replace('|', '-', $text);
		$text = str_replace('~', '-', $text);
		$text = str_replace(']', '', $text);
		$text = str_replace('[', '', $text);
		$text = str_replace('}', '', $text);
		$text = str_replace('{', '', $text);
		$text = str_replace('*', '', $text);
		$text = str_replace('$', '', $text);
		$text = str_replace('!', '', $text);
		$text = str_replace('~', '', $text);
		$text = str_replace('#', '', $text);
		$text = str_replace('@', '', $text);
		$text = str_replace('^', '', $text);
		$text = str_replace('(', '', $text);
		$text = str_replace(')', '', $text);
		$text = str_replace('+', '', $text);
		$text = str_replace('"', '', $text);
		$text = str_replace("'", '', $text);
		$text = str_replace(',', '', $text);
		$text = str_replace('=', '', $text);
		$text = str_replace('>', '', $text);
		$text = str_replace('<', '', $text);
		$text = str_replace('&amp;', '', $text);
		$text = str_replace('&', '', $text);
		$text = str_replace(';', '', $text);
		$text = str_replace(':', '', $text);
		$text = str_replace('.', '', $text);
		$text = str_replace('--', '-', $text);
		$text = str_replace('---', '-', $text);
		$text = str_replace('----', '-', $text);
		$text = str_replace('-----', '-', $text);
		$text = str_replace('------', '-', $text);
		$text = str_replace('-------', '-', $text);
		$text = str_replace('--------', '-', $text);
	}
	else
	{
		$text = str_replace('quot', '', $text);
		$text = str_replace('&amp;', '', $text);
		$text = str_replace('&', '', $text);
		$text = utf8_seo_url($text);
		if (empty($text))
		{
			$text = '--';
		}
		
	}
	$last = substr($text, -1);
	if ($last == '-')
	{
		$text = substr($text, 0, -1);
	}
        if (empty($text))
        {
                $show['emptyurltext'] = true;
        }
        else
        {
                $show['emptyurltext'] = false;
        }
        if ($ilconfig['seourls_lowercase'] AND $forcenolowercase == false)
        {
                $text = mb_strtolower($text);
        }
        return $text;
}

/**
* Function to rewrite a url by providing a text to remove out of the url
*
* @param       string         search string text
* @param       string         replace string text
* @param       array          array holding multiple vars to be removed
*
* @return      string         Returns the replaced text
*/
function rewrite_url($string = '', $removetext = '', $removearray = array())
{
	global $ilance;
	$unaccept = array('select','search','submit','pp','token','sef','do','cmd','ld','rl');
        $unaccepted = array_merge($unaccept, $removearray);
	$unaccepted = array_unique($unaccepted);
	$removetext = str_replace(' ', '+', $removetext);
	if (!empty($removetext))
	{
		$find1 = "?$removetext&";
		$repl1 = "?";
		$string = str_replace($find1, $repl1, $string);
		$find2 = "?$removetext";
		$repl2 = "";
		$string = str_replace($find2, $repl2, $string);
		$find3 = "&$removetext";
		$repl3 = "";
		$string = str_replace($find3, $repl3, $string);
		$find4 = "$removetext";
		$repl4 = "";
		$string = str_replace($find4, $repl4, $string);
	}
	foreach ($unaccepted AS $removetext)
	{
		if (isset($ilance->GPC["$removetext"]) AND !empty($ilance->GPC["$removetext"]))
		{
			$find1 = "?$removetext=" . $ilance->GPC["$removetext"] . "&";
			$repl1 = "?";
			$string = str_replace($find1, $repl1, $string);
			$find2 = "?$removetext=" . $ilance->GPC["$removetext"] . "";
			$repl2 = "";
			$string = str_replace($find2, $repl2, $string);
			$find3 = "&$removetext=" . $ilance->GPC["$removetext"] . "";
			$repl3 = "";
			$string = str_replace($find3, $repl3, $string);
			$find4 = "$removetext=" . $ilance->GPC["$removetext"] . "";
			$repl4 = "";
			$string = str_replace($find4, $repl4, $string);
		}
	}
	$string = urldecode($string);
	$string = str_replace(' ', '+', $string);
        return $string;
}

/**
* Function to print any hidden $ilance->GPC elements into a url string or hidden input fields.  All fields values will be wrapped in
* urlencode.
*
* @param       bool           use input fields (default true)
* @param       array          excluded array keys (ie: 'cmd','cid','project_id')
* @param       bool           print a ? question mark before any url text (default false)
* @param       string         prepend text to hidden input field names (example: old[)
* @param       string         append text to hidden input field names (example: ])
* @param       boolean        convert text using htmlentities() (default true)
* @param       boolean        return urldecoded() string? (default false & urlencoded())
* @param       boolean        show sid[x]=true in url bit? (default false)
*
* @return      integer        Returns HTML representation of the url string or hidden input fields.
*/
function print_hidden_fields($string = false, $excluded = array(), $questionmarkfirst = false, $prepend_text = '', $append_text = '', $htmlentities = true, $urldecode = false, $showsid = false)
{
        global $ilance;
        if ($showsid == false)
        {
                $excludedtmp = array('sid');
                $excluded = array_merge($excludedtmp, $excluded);
        }
        $unaccepted = array('select','search','submit','pp','token','sef','do','searchid','radiuszip','list');
        $unaccepted = array_merge($unaccepted, $excluded);
	$unaccepted = array_unique($unaccepted);
        $html = '';
        foreach ($ilance->GPC AS $key => $value)
        {
                if (!in_array($key, $unaccepted))
                {
                        if (is_array($value))
                        {
                                if ($string)
                                {
                                        foreach ($value AS $key2 => $value2)
                                        {
                                                if (empty($html) AND $questionmarkfirst)
                                                {
                                                        if (isset($value2) AND $value2 != '')
                                                        {
                                                                if (is_array($value2))
                                                                {
                                                                        foreach ($value2 AS $key3 => $value3)
                                                                        {
                                                                                if (isset($value3) AND $value3 != '')
                                                                                {
                                                                                        if (is_array($value3))
                                                                                        {
                                                                                                foreach ($value3 AS $key4 => $value4)
                                                                                                {
                                                                                                        if (!empty($value4))
                                                                                                        {
                                                                                                                $html .= '?' . $key . '[' . $key2 . '][' . $key3 . '][' . $key4 . ']=' . urlencode(handle_input_keywords(str_replace(' ', '+', $value4)));
                                                                                                        }
                                                                                                }
                                                                                        }
                                                                                        else
                                                                                        {
                                                                                                if (!empty($value3))
                                                                                                {
                                                                                                        $html .= '?' . $key . '[' . $key2 . '][' . $key3 . ']=' . urlencode(handle_input_keywords(str_replace(' ', '+', $value3)));
                                                                                                }
                                                                                        }
                                                                                }
                                                                        }        
                                                                }
                                                                else
                                                                {
                                                                        if (!empty($value2))
                                                                        {
                                                                                $html .= '?' . $key . '[' . $key2 . ']=' . urlencode(handle_input_keywords(str_replace(' ', '+', $value2)));
                                                                        }
                                                                }
                                                        }
                                                }
                                                else
                                                {
                                                        if (isset($value2) AND $value2 != '')
                                                        {
                                                                if (is_array($value2))
                                                                {
                                                                        foreach ($value2 AS $key3 => $value3)
                                                                        {
                                                                                if (isset($value3) AND $value3 != '')
                                                                                {
                                                                                        if (is_array($value3))
                                                                                        {
                                                                                                foreach ($value3 AS $key4 => $value4)
                                                                                                {
                                                                                                        if (!empty($value4))
                                                                                                        {
                                                                                                                $html .= '&' . $key . '[' . $key2 . '][' . $key3 . '][' . $key4 . ']=' . urlencode(handle_input_keywords(str_replace(' ', '+', $value4)));
                                                                                                        }
                                                                                                }
                                                                                        }
                                                                                        else
                                                                                        {
                                                                                                if (!empty($value3))
                                                                                                {
                                                                                                        $html .= '&' . $key . '[' . $key2 . '][' . $key3 . ']=' . urlencode(handle_input_keywords(str_replace(' ', '+', $value3)));
                                                                                                }
                                                                                        }
                                                                                }
                                                                        }  
                                                                }
                                                                else
                                                                {
                                                                        if (!empty($value2))
                                                                        {
                                                                                $html .= '&' . $key . '[' . $key2 . ']=' . urlencode(handle_input_keywords(str_replace(' ', '+', $value2)));
                                                                        }
                                                                }
                                                        }
                                                }
                                        }
                                }
                                else
                                {
                                        foreach ($value AS $key2 => $value2)
                                        {
                                                if (isset($value2) AND $value2 != '')
                                                {
                                                        if (is_array($value2))
                                                        {
                                                                foreach ($value2 AS $key3 => $value3)
                                                                {
                                                                        if (isset($value3) AND $value3 != '')
                                                                        {
                                                                                if (is_array($value3))
                                                                                {
                                                                                        foreach ($value3 AS $key4 => $value4)
                                                                                        {
                                                                                                if (!empty($value4))
                                                                                                {
													$html .= '<input type="hidden" name="' . $prepend_text . $key . $append_text . '[' . $key2 . '][' . $key3 . '][' . $key4 . ']" value="' . (($htmlentities) ? ilance_htmlentities($value4) : urlencode(handle_input_keywords(str_replace(' ', '+', $value4)))) . '" />' . "\n";
                                                                                                }
                                                                                        }
                                                                                }
                                                                                else
                                                                                {
                                                                                        if (!empty($value3))
                                                                                        {
												$html .= '<input type="hidden" name="' . $prepend_text . $key . $append_text . '[' . $key2 . '][' . $key3 . ']" value="' . (($htmlentities) ? ilance_htmlentities($value3) : urlencode(handle_input_keywords(str_replace(' ', '+', $value3)))) . '" />' . "\n";
                                                                                        }
                                                                                }
                                                                        }
                                                                }
                                                        }
                                                        else
                                                        {
                                                                if (!empty($value2))
                                                                {
									$html .= '<input type="hidden" name="' . $prepend_text . $key . $append_text . '[' . $key2 . ']" value="' . (($htmlentities) ? ilance_htmlentities($value2) : urlencode(handle_input_keywords(str_replace(' ', '+', $value2)))) . '" />' . "\n";
                                                                }
                                                        }
                                                                
                                                }
                                        }
                                }
                        }
                        else
                        {
                                if ($string)
                                {
                                        if (empty($html) AND $questionmarkfirst)
                                        {
                                                if (isset($value) AND $value != '')
                                                {
                                                        $html .= '?' . $key . '=' . urlencode(handle_input_keywords(str_replace(' ', '+', $value)));
                                                }
                                        }
                                        else
                                        {
                                                if (isset($value) AND $value != '')
                                                {
                                                        $html .= '&' . $key . '=' . urlencode(handle_input_keywords(str_replace(' ', '+', $value)));
                                                }
                                        }
                                }
                                else
                                {
                                        if (isset($value) AND $value != '')
                                        {
						$html .= '<input type="hidden" name="' . $prepend_text . $key . $append_text . '" value="' . handle_input_keywords($value, false) . '" />' . "\n";
                                        }
                                }
                        }                                        
                }                
        }        
        if ($urldecode)
        {
                $html = urldecode($html);
        }
        return $html;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>