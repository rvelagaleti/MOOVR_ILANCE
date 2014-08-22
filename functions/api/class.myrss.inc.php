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
* MyRSS class to perform the majority of rss output functions within ILance
*
* @package      iLance\MyRSS
* @version      4.0.0.8059
* @author       ILance
*/
class myrss
{
	/*
	* Feed version
	*/
	var $feedVersion;
	
        /*
	* Channel title
	*/
	var $channelTitle;
        
        /*
	* Channel link
	*/
	var $channelLink;
        
        /*
	* Channel description
	*/
	var $channelDesc;
	
        /*
	* Channel image title
	*/	
	var $imageTitle;
        
        /*
	* Channel image link
	*/
	var $imageLink;
        
        /*
	* Channel image url
	*/
	var $imageURL;
	
        /*
	* Function to check the values such as the channel title, link, description, url, etc for verification
	*/
	function checkValues()
	{
		if ($this->channelTitle == '')
		{
			die("Please specify a channel title");
		}
		if (mb_ereg("http://", $this->channelLink) == false AND mb_ereg("https://", $this->channelLink) == false)
		{
			die("Please specify a channel link");
		}
                if ($this->channelDesc == '')
		{
			die("Please specify a channel description");
		}
		if ($this->imageTitle == '')
		{
			die("Please specify an image title");
		}
		if ($this->feedVersion == '')
		{
			$this->feedVersion = '0.91';
		}
	}
	
	/*
	* Function to fetch the RSS feed from a particular datasource based on a number of supplied arguments
	*
	* @param      string       database table name
	* @param      string       title
	* @param      string       description
	* @param      string       link
	* @param      string       link template
	* @param      string       sql where condition
	* @param      string       sql limit condition
	* @param      string       extra fieldname
	*
	* @return     string       Returns formatted RSS feed
	*/
        function GetRSS($tableName, $titleFieldName, $descFieldName, $linkFieldName, $linkTemplate, $WhereClause, $LimitClause, $fieldname = '')
	{
		global $ilance, $ilconfig;
                
		$this->checkValues();
		
		// Generate the heading of the RSS XML file
                $rssValue = '<?xml version="1.0" encoding="' . $ilconfig['template_charset'] . '"?>' . LINEBREAK;
		$rssValue .= "<rss version=\"" . $this->feedVersion . "\" xmlns:atom=\"http://www.w3.org/2005/Atom\">";
		
		// Build the channel tag
		$rssValue .= "<channel>";
		$rssValue .= "<title><![CDATA[" . un_htmlspecialchars($this->channelTitle) . "]]></title>";
		$rssValue .= "<link><![CDATA[" . un_htmlspecialchars($this->channelLink) . "]]></link>";
		$rssValue .= "<description><![CDATA[" . un_htmlspecialchars($this->channelDesc) . "]]></description>";
		$rssValue .= "<language>en-us</language>";
		$rssValue .= "<copyright>Copyright " . date('Y') . ", " . SITE_NAME . "</copyright>";

		// Build the feed image tag
		$rssValue .= "<image>";
		$rssValue .= "<title><![CDATA[" . un_htmlspecialchars($this->imageTitle) . "]]></title>";
		$rssValue .= "<url><![CDATA[" . $this->imageURL . "]]></url>";
		$rssValue .= "<link><![CDATA[" . $this->imageLink . "]]></link>";
		$rssValue .= "</image>";
		
		$rResult = $ilance->db->query("
			SELECT $titleFieldName,
			$descFieldName,
			$linkFieldName
			FROM $tableName
			$WhereClause
			GROUP BY project_id
			ORDER BY date_starts DESC
			$LimitClause
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($rResult) > 0)
		{
                        // handle bbcode removal
                        			
                        
			while ($rRow = $ilance->db->fetch_array($rResult, DB_ASSOC))
			{
                                $title = $ilance->bbcode->strip_bb_tags($rRow["$titleFieldName"]);
                                $description = $ilance->bbcode->strip_bb_tags($rRow["$descFieldName"]);
                                
				if (mb_strlen($title) > 200)
				{
					$title  = mb_substr(stripslashes($title), 0, 200);
					$title .= ' ...';
				}
				else
				{
					$title = stripslashes($title);
				}
				if (mb_strlen($description) > 200)
				{
					$description  = mb_substr(stripslashes($description), 0, 200);
					$description .= ' ...';
				}
				else
				{
					$description = stripslashes($description);
				}
                                
				$rssValue .= "<item>";
				$rssValue .= "<title><![CDATA[" . un_htmlspecialchars($title) . "]]></title>";
				$rssValue .= "<link><![CDATA[" . un_htmlspecialchars(str_replace("{linkId}", $rRow["$fieldname"], $linkTemplate)) . "]]></link>";
                                $rssValue .= "<pubDate><![CDATA[" . print_date($rRow['date_starts'], 'D, d M Y H:i:s O', 0, 0) . "]]></pubDate>";
				$rssValue .= "<description><![CDATA[" . un_htmlspecialchars($description) . "]]></description>";
				$rssValue .= "</item>";
			}
		}
		
		$rssValue .= "</channel>";
		$rssValue .= "</rss>";
                
		return $rssValue;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>