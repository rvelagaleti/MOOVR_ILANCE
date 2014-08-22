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
* RSS class to perform the majority rss functions within ILance
*
* @package      iLance\RSS
* @version      4.0.0.8059
* @author       ILance
*/
class rss
{
	/*
	* Function to fetch the RSS feed
	*
	* @param       string        rss url       
	*
	* @return      string        Returns RSS feed 
	*/
	function Get($rss_url)
	{
		if ($this->cache_dir != '')
		{
			$cache_file = $this->cache_dir . '/rss[' . $this->cache_time . 's][' . md5($rss_url) . '].cache';
			$timedif = @(TIMESTAMPNOW - filemtime($cache_file));
			
			if ($timedif < $this->cache_time)
			{
				// cached file is fresh enough, return cached array
				$result = unserialize(join('', file($cache_file)));
				
				// set 'cached' to 1 only if cached file is correct
				if ($result) $result['cached'] = 1;
			} 
			else
			{
				// cached file is too old, create new
				$result = $this->Parse($rss_url);
				$serialized = serialize($result);
				
				if ($f = @fopen($cache_file, 'w'))
				{
					fwrite ($f, $serialized, mb_strlen($serialized));
					fclose($f);
				}
				if ($result) $result['cached'] = 0;
			}
		}
		else
		{
			$result = $this->Parse($rss_url);
			if ($result) $result['cached'] = 0;
		}
		return $result;
	}
	
	/*
	* Modification of preg_match(), that returns field with index 1 from the Matches array
	*
	* @param       string        pattern
	* @param       string        subject
	*
	* @return      string        Returns the index from the matches array      
	*/
	function my_preg_match($pattern, $subject)
	{
		preg_match($pattern, $subject, $out);
		if (!empty($out[1]))
		{
			return $out[1];
		}
	}

	/*
	* Parse() is private method used by Get() to load and parse RSS file. Don't use Parse() in your scripts - use Get($rss_file) instead.
	*
	* @param       string        rss url      
	*
	* @return      
	*/
	function Parse($rss_url)
	{
		// Open and load RSS file
		if ($f = @fopen($rss_url, 'r'))
		{
			$rss_content = '';
			
			while (!feof($f))
			{
				$rss_content .= fgets($f, 4096);
			}
			fclose($f);

			// Parse CHANNEL info
			$result['title']          = $this->my_preg_match("'<channel>.+?<title>(.*?)</title>.+?</channel>'si", $rss_content);
			$result['link']           = $this->my_preg_match("'<channel>.+?<link>(.*?)</link>.+?</channel>'si", $rss_content);
			$result['description']    = $this->my_preg_match("'<channel>.+?<description>(.*?)</description>.+?</channel>'si", $rss_content);
			$result['language']       = $this->my_preg_match("'<channel>.+?<language>(.*?)</language>.+?</channel>'si", $rss_content);
			$result['encoding']       = $this->my_preg_match("'encoding=\"(.*?)\"'si", $rss_content);
			
			// Parse OPTIONAL info
			$result['copyright']      = $this->my_preg_match("'<channel>.+?<copyright>(.*?)</copyright>.+?</channel>'si", $rss_content);
			$result['managingEditor'] = $this->my_preg_match("'<channel>.+?<managingEditor>(.*?)</managingEditor>.+?</channel>'si", $rss_content);
			$result['webMaster']      = $this->my_preg_match("'<channel>.+?<webMaster>(.*?)</webMaster>.+?</channel>'si", $rss_content);
			$result['rating']         = $this->my_preg_match("'<channel>.+?<rating>(.*?)</rating>.+?</channel>'si", $rss_content);
			$result['pubDate']        = $this->my_preg_match("'<channel>.+?<pubDate>(.*?)</pubDate>.+?</channel>'si", $rss_content);
			$result['lastBuildDate']  = $this->my_preg_match("'<channel>.+?<lastBuildDate>(.*?)</lastBuildDate>.+?</channel>'si", $rss_content);
			$result['pubDate']        = $this->my_preg_match("'<channel>.+?<docs>(.*?)</docs>.+?</channel>'si", $rss_content);
			
			// Parse TEXTINPUT info
			preg_match("'<textinput>(.*?)</textinput>'si", $rss_content, $out_textinfo);
			
			if (!empty($out_textinfo[1]))
			{
				$result['textinput_title']          = $this->my_preg_match("'<title>(.*?)</title>'si", $out_textinfo[1]);
				$result['textinput_description']    = $this->my_preg_match("'<description>(.*?)</description>'si", $out_textinfo[1]);
				$result['textinput_name']           = $this->my_preg_match("'<name>(.*?)</name>'si", $out_textinfo[1]);
				$result['textinput_link']           = $this->my_preg_match("'<link>(.*?)</link>'si", $out_textinfo[1]);
			}
			
			// Parse IMAGE info
			preg_match("'<image>(.*?)</image>'si", $rss_content, $out_imageinfo);
			if (!empty($out_imageinfo[1]))
			{
				$result['image_title']              = $this->my_preg_match("'<title>(.*?)</title>'si", $out_imageinfo[1]);
				$result['image_url']                = $this->my_preg_match("'<url>(.*?)</url>'si", $out_imageinfo[1]);
				$result['image_link']               = $this->my_preg_match("'<link>(.*?)</link>'si", $out_imageinfo[1]);
				$result['image_width']              = $this->my_preg_match("'<width>(.*?)</width>'si", $out_imageinfo[1]);
				$result['image_height']             = $this->my_preg_match("'<height>(.*?)</height>'si", $out_imageinfo[1]);
			}
			
			// Parse ITEMS
			preg_match_all("'<item>(.*?)</item>'si", $rss_content, $items);
			$rss_items = !empty($items[1]) ? $items[1] : 0;
			
			$result['items_count'] = count($items[1]);
			$i = 0;
			$result['items'] = array(); // create array even if there are no items
			
			if ($rss_items != '')
			{
				foreach ($rss_items as $rss_item)
				{
					$result['items'][$i]['title'] = $this->my_preg_match("'<title>(.*?)</title>'si", $rss_item);
					$result['items'][$i]['link'] = $this->my_preg_match("'<link>(.*?)</link>'si", $rss_item);
					$result['items'][$i]['description'] = $this->my_preg_match("'<description>(.*?)</description>'si", $rss_item);
					$i++;
				}
			}
			return $result;
		}
		else
		{
			return false;
		}
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>