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
* Attachment Tools class to perform the majority of uploading and attachment handling operations within ILance.
*
* @package      iLance\Attachment\Tools
* @version      4.0.0.8059
* @author       ILance
*/
class attachment_tools extends attachment
{
	/*
	* Function to fetch the full attachment path for the specified attachment type
	*
	* @param	string		attachment type (profile, portfolio, project, itemphoto, bid, pmb, ws, slideshow)
	* @param        boolean         determine if we're calling an original attachment (default false)
	* @param        string          attachment method (results, resultsgallery, resultssnapshot, itemphoto, itemphotomini, portfolio, portfoliofeatured)
	*
	* @return	string          attachment folder
	*/
	function fetch_attachment_path($attachtype = '', $isoriginal = false, $subcmd = '')
	{
		global $ilance, $show, $ilconfig;
		if ($attachtype == 'profile')
		{
			$filedata = DIR_PROFILE_ATTACHMENTS . ($isoriginal ? 'original/' : '');
		}
		else if ($attachtype == 'portfolio')
		{
			$filedata = DIR_PORTFOLIO_ATTACHMENTS . ($isoriginal ? 'original/' : '');
		}
		else if ($attachtype == 'itemphoto' OR $attachtype == 'slideshow' OR $attachtype == 'digital' OR $attachtype == 'project')
		{
			$filedata = DIR_AUCTION_ATTACHMENTS . ($isoriginal ? 'original/' : '');
		}
		else if ($attachtype == 'bid')
		{
			$filedata = DIR_BID_ATTACHMENTS . ($isoriginal ? 'original/' : '');
		}
		else if ($attachtype == 'pmb')
		{
			$filedata = DIR_PMB_ATTACHMENTS . ($isoriginal ? 'original/' : '');
		}
		else if ($attachtype == 'ws')
		{
			$filedata = DIR_WS_ATTACHMENTS . ($isoriginal ? 'original/' : '');
		}
		else
		{
			($apihook = $ilance->api('fetch_attachment_path_else_end')) ? eval($apihook) : false;
		}
		if (!empty($filedata))
		{
			return $filedata;
		}
		return false;
	}

	/*
	* Function fetch the full attachment path and actual file(name) for the specified attachment type
	*
	* @param	string		attachment type (profile, portfolio, project, itemphoto, bid, pmb, ws, slideshow)
	* @param        boolean         fetch original image (default false)
	* @param        string          attachment method (results, resultsgallery, resultssnapshot, itemphoto, itemphotomini, portfolio, portfoliofeatured)
	*
	* @return	string          full path to the attachment
	*/
	function fetch_attachment_file($attachtype = '', $filehash = '', $isoriginal = false, $subcmd = '')
	{
		global $ilance, $show, $ilconfig;
		$filedata = '';
		if ($attachtype == 'profile')
		{
			switch ($subcmd)
			{
				case 'results':
				{
					$filedata = DIR_PROFILE_ATTACHMENTS . ($isoriginal ? 'original/' : 'resized/search/') . $filehash . '.attach';
					break;
				}
				case 'resultsgallery':
				{
					$filedata = DIR_PROFILE_ATTACHMENTS . ($isoriginal ? 'original/' : 'resized/gallery/') . $filehash . '.attach';
					break;
				}
				case 'resultssnapshot':
				{
					$filedata = DIR_PROFILE_ATTACHMENTS . ($isoriginal ? 'original/' : 'resized/snapshot/') . $filehash . '.attach';
					break;
				}
				case 'itemphoto':
				{
					$filedata = DIR_PROFILE_ATTACHMENTS . ($isoriginal ? 'original/' : 'resized/full/') . $filehash . '.attach';
					break;
				}
				case 'itemphotomini':
				{
					$filedata = DIR_PROFILE_ATTACHMENTS . ($isoriginal ? 'original/' : 'resized/mini/') . $filehash . '.attach';
					break;
				}
				default:
				{
					$filedata = DIR_PROFILE_ATTACHMENTS . ($isoriginal ? 'original/' : '') . $filehash . '.attach';
					break;
				}
			}
		}
		else if ($attachtype == 'portfolio')
		{
			switch ($subcmd)
			{
				case 'results':
				{
					$filedata = DIR_PORTFOLIO_ATTACHMENTS . ($isoriginal ? 'original/' : 'resized/search/') . $filehash . '.attach';
					break;
				}
				case 'resultsgallery':
				{
					$filedata = DIR_PORTFOLIO_ATTACHMENTS . ($isoriginal ? 'original/' : 'resized/gallery/') . $filehash . '.attach';
					break;
				}
				case 'resultssnapshot':
				{
					$filedata = DIR_PORTFOLIO_ATTACHMENTS . ($isoriginal ? 'original/' : 'resized/snapshot/') . $filehash . '.attach';
					break;
				}
				case 'itemphoto':
				{
					$filedata = DIR_PORTFOLIO_ATTACHMENTS . ($isoriginal ? 'original/' : 'resized/full/') . $filehash . '.attach';
					break;
				}
				case 'itemphotomini':
				{
					$filedata = DIR_PORTFOLIO_ATTACHMENTS . ($isoriginal ? 'original/' : 'resized/mini/') . $filehash . '.attach';
					break;
				}
				default:
				{
					$filedata = DIR_PORTFOLIO_ATTACHMENTS . ($isoriginal ? 'original/' : '') . $filehash . '.attach';
					break;
				}
			}
		}
		else if ($attachtype == 'itemphoto' OR $attachtype == 'project' OR $attachtype == 'slideshow' OR $attachtype == 'digital')
		{
			switch ($subcmd)
			{
				case 'results':
				{
					$filedata = DIR_AUCTION_ATTACHMENTS . ($isoriginal ? 'original/' : 'resized/search/') . $filehash . '.attach';
					break;
				}
				case 'resultsgallery':
				{
					$filedata = DIR_AUCTION_ATTACHMENTS . ($isoriginal ? 'original/' : 'resized/gallery/') . $filehash . '.attach';
					break;
				}
				case 'resultssnapshot':
				{
					$filedata = DIR_AUCTION_ATTACHMENTS . ($isoriginal ? 'original/' : 'resized/snapshot/') . $filehash . '.attach';
					break;
				}
				case 'itemphoto':
				{
					$filedata = DIR_AUCTION_ATTACHMENTS . ($isoriginal ? 'original/' : 'resized/full/') . $filehash . '.attach';
					break;
				}
				case 'itemphotomini':
				{
					$filedata = DIR_AUCTION_ATTACHMENTS . ($isoriginal ? 'original/' : 'resized/mini/') . $filehash . '.attach';
					break;
				}
				default:
				{
					$filedata = DIR_AUCTION_ATTACHMENTS . ($isoriginal ? 'original/' : '') . $filehash . '.attach';
					break;
				}
			}
		}
		else if ($attachtype == 'bid')
		{
			$filedata = DIR_BID_ATTACHMENTS . $filehash . '.attach';
		}
		else if ($attachtype == 'pmb')
		{
			$filedata = DIR_PMB_ATTACHMENTS . $filehash . '.attach';
		}
		else if ($attachtype == 'ws')
		{
			$filedata = DIR_WS_ATTACHMENTS . $filehash . '.attach';
		}
		else
		{
			($apihook = $ilance->api('fetch_attachment_file_else_end')) ? eval($apihook) : false;
		}
		return $filedata;
	}

	/*
	* Function returns logic of attachment storage system and returns back required raw attachment data for a specific picture
	*
	* @param	array 	        attachment array
	* @param        boolean         fetch original picture data (default false)
	* @param        string          attachment method (results, resultsgallery, resultssnapshot, itemphoto, itemphotomini, portfolio, portfoliofeatured)
	*
	* @return	string          Returns raw attachment file data
	*/
	function fetch_attachment_rawdata($attachment, $fetchoriginal = false, $subcmd = '')
	{
		global $ilconfig, $show;
		if ($ilconfig['attachment_dbstorage'])
		{
			if ($fetchoriginal AND !empty($attachment['filedata_original']))
			{
				$show['fetchoriginal'] = true;
				return $attachment['filedata_original'];
			}
			else
			{
				$show['fetchoriginal'] = false;
				switch ($subcmd)
				{
					case 'results':
					{
						$fd = $attachment['filedata_search'];
						if (empty($fd))
						{
							return $attachment['filedata'];
						}
						return $fd;
					}
					case 'resultsgallery':
					{
						$fd = $attachment['filedata_gallery'];
						if (empty($fd))
						{
							return $attachment['filedata'];
						}
						return $fd;
					}
					case 'resultssnapshot':
					{
						$fd = $attachment['filedata_snapshot'];
						if (empty($fd))
						{
							return $attachment['filedata'];
						}
						return $fd;
					}
					case 'itemphoto':
					{
						$fd = $attachment['filedata_full'];
						if (empty($fd))
						{
							return $attachment['filedata'];
						}
						return $fd;
					}
					case 'itemphotomini':
					{
						$fd = $attachment['filedata_mini'];
						if (empty($fd))
						{
							return $attachment['filedata'];
						}
						return $fd;
					}
					default:
					{
						return $attachment['filedata'];
					}
				}
			}
		}
		else
		{
			$show['fetchoriginal'] = false;
			if ($attachment['isexternal'] <= 0)
			{
				if ($attachment['filesize_original'] <= 0)
				{
					$fetchoriginal = false;
				}
				$show['fetchoriginal'] = $fetchoriginal;
				$rawdata = $this->fetch_attachment_file($attachment['attachtype'], $attachment['filehash'], $fetchoriginal, $subcmd);
				if (file_exists($rawdata))
				{
					$attachment['filedata'] = file_get_contents($rawdata);
				}
				unset($rawdata);
			}
			return $attachment['filedata'];
		}
	}

	/*
	* Function to print out a security code captcha stored in $_SESSION also using imagecreate() and imagettftext().
	* Common letters have been eliminated from view such as "i", "l", "o" and "0"
	*
	* @param	integer 	length of captcha phrase (default 5 characters)
	*
	* @return	string          Returns image data
	*/
	function print_captcha($length = 5)
	{
		$src = 'abcdefghjkmnpqrstuvwxyz23456789';
		if (mt_rand(0, 1) == 0)
		{
			$src = mb_strtoupper($src);
		}
		$srclen = mb_strlen($src) - 1;
		$font = DIR_FONTS . 'AppleGaramond.ttf';
		$output_type = 'png';
		$min_font_size = 25;
		$max_font_size = 35;
		$min_angle = -25;
		$max_angle = 25;
		$char_padding = 1;
		$data = array ();
		$image_width = $image_height = 0;
		$_SESSION['ilancedata']['user']['captcha'] = '';
		for ($i = 0; $i < $length; $i++)
		{
			$char = mb_strtoupper(mb_substr($src, mt_rand(0, $srclen), 1));
			$_SESSION['ilancedata']['user']['captcha'] .= "$char";
			$size = mt_rand($min_font_size, $max_font_size);
			$angle = mt_rand($min_angle, $max_angle);
			$bbox = imagettfbbox($size, $angle, $font, $char);
			$char_width = max($bbox[2], $bbox[4]) - min($bbox[0], $bbox[6]);
			$char_height = max($bbox[1], $bbox[3]) - min($bbox[7], $bbox[5]);
			$image_width += $char_width + $char_padding;
			$image_height = max($image_height, $char_height);
			$data[] = array ('char' => $char, 'size' => $size, 'angle' => $angle, 'height' => $char_height, 'width' => $char_width,);
		}
		$x_padding = 6;
		$image_width += ($x_padding * 2);
		$image_height = ($image_height * 1.5) + 2;
		$im = imagecreate($image_width, $image_height);
		$r = 51 * mt_rand(4, 5);
		$g = 51 * mt_rand(4, 5);
		$b = 51 * mt_rand(4, 5);
		$color_bg = imagecolorallocate($im, 255, 255, 255);
		$r = 51 * mt_rand(3, 4);
		$g = 51 * mt_rand(3, 4);
		$b = 51 * mt_rand(3, 4);
		$color_line0 = imagecolorallocate($im, 255, 255, 255);
		$r = 51 * mt_rand(3, 4);
		$g = 51 * mt_rand(3, 4);
		$b = 51 * mt_rand(3, 4);
		$color_line1 = imagecolorallocate($im, 255, 255, 255);
		$r = 51 * mt_rand(1, 2);
		$g = 51 * mt_rand(1, 2);
		$b = 51 * mt_rand(1, 2);
		$color_text = imagecolorallocate($im, 50, 50, 50);
		$color_border = imagecolorallocate($im, 255, 255, 255);
		for ($l = 0; $l < 10; $l++)
		{
			$c = 'color_line' . ($l % 2);
			$lx = mt_rand(0, $image_width + $image_height);
			$lw = mt_rand(0, 3);
			if ($lx > $image_width)
			{
				$lx -= $image_width;
				imagefilledrectangle($im, 0, $lx, $image_width - 1, $lx + $lw, $$c);
			}
			else
			{
				imagefilledrectangle($im, $lx, 0, $lx + $lw, $image_height - 1, $$c);
			}
		}
		$pos_x = $x_padding + ($char_padding / 2);
		foreach ($data AS $d)
		{
			$pos_y = (($image_height + $d['height']) / 2);
			imagettftext($im, $d['size'], $d['angle'], $pos_x, $pos_y, $color_text, $font, $d['char']);
			$pos_x += $d['width'] + $char_padding;
		}
		imagerectangle($im, 0, 0, $image_width - 1, $image_height - 1, $color_border);
		switch ($output_type)
		{
			case 'jpg':
			case 'jpeg':
			{
				header('Content-type: image/jpeg');
				imagejpeg($im, null, 100);
				break;
			}
			case 'png':
			{
				header('Content-type: image/png');
				imagepng($im);
				break;
			}
			case 'gif':
			{
				header('Content-type: image/gif');
				imagegif($im);
				break;
			}
		}
		imagedestroy($im);
	}

	/*
	* Function for determining if a particular attachment id is associated with a private workspace folder.
	*
	* @param       integer        attachment id
	*
	* @return      bool           true or false if attachment exists to a private workspace folder
	*/
	function is_private_workspace_attachment($attachid = 0)
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT tblfolder_ref
			FROM " . DB_PREFIX . "attachment
			WHERE attachid = '" . intval($attachid) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			if ($res['tblfolder_ref'] > 0)
			{
				$sql2 = $ilance->db->query("
					SELECT folder_type
					FROM " . DB_PREFIX . "attachment_folder
					WHERE id = '" . $res['tblfolder_ref'] . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql2) > 0)
				{
					$res2 = $ilance->db->fetch_array($sql2, DB_ASSOC);
					if ($res2['folder_type'] == '1')
					{
						return 1;
					}
				}
			}
		}
		return 0;
	}

	/*
	* Function to fetch the attachment type
	*
	* @param       string         attach type
	* @param       integer        project id
	* @param       integer        custom id for custom apps
	*
	* @return      string         Returns the attachment type
	*/
	function fetch_attachment_type($type = '', $projectid = '', $otherid = '')
	{
		global $ilance, $phrase, $ilpage, $show;
		if (isset($type) AND !empty($type))
		{
			$html = '';
			if ($type == 'profile')
			{
				$sql = $ilance->db->query("
					SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "a.user_id, u.username
					FROM " . DB_PREFIX . "attachment a
					LEFT JOIN " . DB_PREFIX . "users u ON (a.user_id = u.user_id)
					WHERE a.attachid = '" . intval($otherid) . "'
					LIMIT 1
				");
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				//todo: convert member url into seo url if enabled...
				$html = '<div class="smaller gray">{_profile_logo}</div><div class="smaller blue" style="padding-top:3px"><a href="' . HTTP_SERVER . $ilpage['members'] . '?id=' . $res['user_id'] . '">' . handle_input_keywords($res['username']) . '</a></div>';
			}
			else if ($type == 'portfolio')
			{
				$html = '<div class="smaller gray">{_portfolio_item}</div>';
			}
			else if ($type == 'project')
			{
				$sql = $ilance->db->query("
					SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "project_title
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . intval($projectid) . "'
					LIMIT 1
				");
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				//todo: convert url below into sef if on
				$html = '<div class="smaller gray">{_listing_attachment}</div><div class="smaller blue" style="padding-top:3px"><a href="' . HTTP_SERVER . $ilpage['rfp'] . '?id=' . intval($projectid) . '">' . shorten(handle_input_keywords($res['project_title']), 50) . '</a></div>';
			}
			else if ($type == 'itemphoto')
			{
				$sql = $ilance->db->query("
					SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "project_title
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . intval($projectid) . "'
					LIMIT 1
				");
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				//todo: convert url below into sef if on
				$html = '<div class="smaller gray">{_item_photo}</div><div class="smaller blue" style="padding-top:3px"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?id=' . intval($projectid) . '">' . shorten(handle_input_keywords($res['project_title']), 50) . '</a></div>';
			}
			else if ($type == 'bid')
			{
				$sql = $ilance->db->query("
					SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "project_title
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . intval($projectid) . "'
					LIMIT 1
				");
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				//todo: convert url below into sef if on
				$html = '<div class="smaller gray">{_bid_attachment}</div><div class="smaller blue"><a href="' . HTTP_SERVER . $ilpage['rfp'] . '?id=' . intval($projectid) . '">' . shorten(handle_input_keywords($res['project_title']), 50) . '</a></div>';
			}
			else if ($type == 'pmb')
			{
				$html = '<div class="smaller gray">{_pmb_attachment}</div>';
			}
			else if ($type == 'ws')
			{
				$sql = $ilance->db->query("
					SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "project_title, project_state
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . intval($projectid) . "'
					LIMIT 1
				");
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$url = $ilpage['merch'];
				if ($res['project_state'] == 'service')
				{
					$url = $ilpage['rfp'];
				}
				//todo: convert url below into sef if on
				$html = '<div class="smaller gray">{_mediashare}</div><div class="smaller blue"><a href="' . HTTP_SERVER . $url . '?id=' . intval($projectid) . '">' . shorten(handle_input_keywords($res['project_title']), 50) . '</a></div>';
			}
			else if ($type == 'digital')
			{
				$sql = $ilance->db->query("
					SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "project_title
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . intval($projectid) . "'
					LIMIT 1
				");
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				//todo: convert url below into sef if on
				$html = '<div class="smaller gray">{_digital_download}</div><div class="smaller blue" style="padding-top:3px"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?id=' . intval($projectid) . '">' . shorten(handle_input_keywords($res['project_title']), 50) . '</a></div>';
			}
			else if ($type == 'slideshow')
			{
				$sql = $ilance->db->query("
					SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "project_title
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . intval($projectid) . "'
					LIMIT 1
				");
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				//todo: convert url below into sef if on
				$html = '<div class="smaller gray">{_slideshow_photo}</div><div class="smaller blue" style="padding-top:3px"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?id=' . intval($projectid) . '">' . shorten(handle_input_keywords($res['project_title']), 50) . '</a></div>';
			}
			else
			{
				($apihook = $ilance->api('fetch_attachment_type_end')) ? eval($apihook) : false;
			}
			return $html;
		}
	}

	/*
	* Function to fetch the attachment dimensions
	*
	* @param       array          attachment array
	*
	* @return      string         Returns the attachment type
	*/
	function fetch_attachment_dimensions($res = array())
	{
		$html = '';
		if ($res['width_mini'] > 0 AND $res['height_mini'] > 0)
		{
			$html .= '<div style="padding-bottom:3px" title="{_width_x_height} (' . $res['width_mini'] . ' x ' . $res['height_mini'] . ') - ' . print_filesize($res['filesize_mini']) . '" class="smaller"><strong>{_mini}:</strong> ' . $res['width_mini'] . ' x ' . $res['height_mini'] . ' (' . print_filesize($res['filesize_mini']) . ')</div>';
		}
		if ($res['width_search'] > 0 AND $res['height_search'] > 0)
		{
			$html .= '<div style="padding-bottom:3px" title="{_width_x_height} (' . $res['width_search'] . ' x ' . $res['height_search'] . ') - ' . print_filesize($res['filesize_search']) . '" class="smaller"><strong>{_search}:</strong> ' . $res['width_search'] . ' x ' . $res['height_search'] . ' (' . print_filesize($res['filesize_search']) . ')</div>';
		}
		if ($res['width_gallery'] > 0 AND $res['height_gallery'] > 0)
		{
			$html .= '<div style="padding-bottom:3px" title="{_width_x_height} (' . $res['width_gallery'] . ' x ' . $res['height_gallery'] . ') - ' . print_filesize($res['filesize_gallery']) . '" class="smaller"><strong>{_gallery}:</strong> ' . $res['width_gallery'] . ' x ' . $res['height_gallery'] . ' (' . print_filesize($res['filesize_gallery']) . ')</div>';
		}
		if ($res['width_snapshot'] > 0 AND $res['height_snapshot'] > 0)
		{
			$html .= '<div style="padding-bottom:3px" title="{_width_x_height} (' . $res['width_snapshot'] . ' x ' . $res['height_snapshot'] . ') - ' . print_filesize($res['filesize_snapshot']) . '" class="smaller"><strong>{_snapshot}:</strong> ' . $res['width_snapshot'] . ' x ' . $res['height_snapshot'] . ' (' . print_filesize($res['filesize_snapshot']) . ')</div>';
		}
		if ($res['width_full'] > 0 AND $res['height_full'] > 0)
		{
			$html .= '<div style="padding-bottom:3px" title="{_width_x_height} (' . $res['width_full'] . ' x ' . $res['height_full'] . ') - ' . print_filesize($res['filesize_full']) . '" class="smaller"><strong>{_full}:</strong> ' . $res['width_full'] . ' x ' . $res['height_full'] . ' (' . print_filesize($res['filesize_full']) . ')</div>';
		}
		if ($res['width_original'] > 0 AND $res['height_original'] > 0)
		{
			$html .= '<div style="padding-bottom:3px" title="{_width_x_height} (' . $res['width_original'] . ' x ' . $res['height_original'] . ') - ' . print_filesize($res['filesize_original']) . '" class="smaller"><strong>{_original}:</strong> ' . $res['width_original'] . ' x ' . $res['height_original'] . ' (' . print_filesize($res['filesize_original']) . ')</div>';
		}
		return $html;
	}

	/*
	* Function to move all attachments within the database to the filepath
	*
	* @return      string         Returns a notice of actions that occured
	*/
	function move_attachments_to_filepath()
	{
		@set_time_limit(0);
		global $ilance, $ilconfig, $phrase, $ilpage, $show;
		$notice = '';
		$ilance->attachment = construct_object('api.attachment');
		$sql = $ilance->db->query("
			SELECT attachid, attachtype, filedata, filedata_original, filedata_full, filedata_mini, filedata_search, filedata_gallery, filedata_snapshot, filename, filetype, filehash
			FROM " . DB_PREFIX . "attachment
			ORDER BY attachid ASC
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$notice = '<ol>';
			$count = 1;
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				if (!empty($res['filedata']))
				{
					$attachpath = $this->fetch_attachment_path($res['attachtype'], false);
					$newfilename = $attachpath . $res['filehash'] . '.attach';
					if (file_exists($newfilename))
					{
						@unlink($newfilename);
					}
					if ($fp = @fopen($newfilename, 'wb'))
					{
						@fwrite($fp, $res['filedata']);
						@fclose($fp);
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "attachment
							SET filedata = ''
							WHERE attachid = '" . $res['attachid'] . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						$notice .= '<li class="green">Moved ' . $res['filename'] . ' to ' . $newfilename . '</li>';
					}
					else
					{
						$notice .= '<li class="red">Could not move ' . $res['filename'] . ' to ' . $newfilename . ' (write permission?)</li>';
					}
				}
				if (!empty($res['filedata_original']))
				{
					$attachpath = $this->fetch_attachment_path($res['attachtype'], true); // original
					$newfilename = $attachpath . $res['filehash'] . '.attach';
					if (file_exists($newfilename))
					{
						@unlink($newfilename);
					}
					if ($fp = @fopen($newfilename, 'wb'))
					{
						@fwrite($fp, $res['filedata_original']);
						@fclose($fp);
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "attachment
							SET filedata_original = ''
							WHERE attachid = '" . $res['attachid'] . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						$notice .= '<li class="green">Moved (original) ' . $res['filename'] . ' to ' . $newfilename . '</li>';
					}
					else
					{
						$notice .= '<li class="red">Could not move (original) ' . $res['filename'] . ' to ' . $newfilename . ' (write permission?)</li>';
					}
				}
				// if image, make original from default photo
				else
				{
					// file types that accept original photos to be saved
					$originalattachtypes = array('profile', 'portfolio', 'itemphoto', 'slideshow');
		    
					($apihook = $ilance->api('move_attachments_to_filepath_no_original_else')) ? eval($apihook) : false;
		    
					// is this attachment a valid photo?
					if (in_array($res['filetype'], $ilance->attachment->mimetypes) AND in_array($res['attachtype'], $originalattachtypes))
					{
						// create original image
						$attachpath = $this->fetch_attachment_path($res['attachtype'], true); // original
						$newfilename = $attachpath . $res['filehash'] . '.attach';
						if (file_exists($newfilename))
						{
							@unlink($newfilename);
						}
						if ($fp = @fopen($newfilename, 'wb'))
						{
							@fwrite($fp, $res['filedata']);
							@fclose($fp);
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "attachment
								SET filedata_original = ''
								WHERE attachid = '" . $res['attachid'] . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							$notice .= '<li class="green">Moved (original) ' . $res['filename'] . ' to ' . $newfilename . '</li>';
						}
						else
						{
							$notice .= '<li class="red">Could not move (original) ' . $res['filename'] . ' to ' . $newfilename . ' (write permission?)</li>';
						}
					}
				}
				if (!empty($res['filedata_full']))
				{
					$attachpath = $this->fetch_attachment_path($res['attachtype'], false);
					$newfilename = $attachpath . 'resized/full/' . $res['filehash'] . '.attach';
					if (file_exists($newfilename))
					{
						@unlink($newfilename);
					}
					if ($fp = @fopen($newfilename, 'wb'))
					{
						@fwrite($fp, $res['filedata_full']);
						@fclose($fp);
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "attachment
							SET filedata_full = ''
							WHERE attachid = '" . $res['attachid'] . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						$notice .= '<li class="green">Moved (full photo) ' . $res['filename'] . ' to ' . $newfilename . '</li>';
					}
					else
					{
						$notice .= '<li class="red">Could not move (full photo) ' . $res['filename'] . ' to ' . $newfilename . ' (write permission?)</li>';
					}
				}
				if (!empty($res['filedata_mini']))
				{
					$attachpath = $this->fetch_attachment_path($res['attachtype'], false);
					$newfilename = $attachpath . 'resized/mini/' . $res['filehash'] . '.attach';
					if (file_exists($newfilename))
					{
						@unlink($newfilename);
					}
					if ($fp = @fopen($newfilename, 'wb'))
					{
						@fwrite($fp, $res['filedata_mini']);
						@fclose($fp);
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "attachment
							SET filedata_mini = ''
							WHERE attachid = '" . $res['attachid'] . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						$notice .= '<li class="green">Moved (mini photo) ' . $res['filename'] . ' to ' . $newfilename . '</li>';
					}
					else
					{
						$notice .= '<li class="red">Could not move (mini photo) ' . $res['filename'] . ' to ' . $newfilename . ' (write permission?)</li>';
					}
				}
				if (!empty($res['filedata_search']))
				{
					$attachpath = $this->fetch_attachment_path($res['attachtype'], false);
					$newfilename = $attachpath . 'resized/search/' . $res['filehash'] . '.attach';
					if (file_exists($newfilename))
					{
						@unlink($newfilename);
					}
					if ($fp = @fopen($newfilename, 'wb'))
					{
						@fwrite($fp, $res['filedata_search']);
						@fclose($fp);
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "attachment
							SET filedata_search = ''
							WHERE attachid = '" . $res['attachid'] . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						$notice .= '<li class="green">Moved (search photo) ' . $res['filename'] . ' to ' . $newfilename . '</li>';
					}
					else
					{
						$notice .= '<li class="red">Could not move (search photo) ' . $res['filename'] . ' to ' . $newfilename . ' (write permission?)</li>';
					}
				}
				if (!empty($res['filedata_gallery']))
				{
					$attachpath = $this->fetch_attachment_path($res['attachtype'], false);
					$newfilename = $attachpath . 'resized/gallery/' . $res['filehash'] . '.attach';
					if (file_exists($newfilename))
					{
						@unlink($newfilename);
					}
					if ($fp = @fopen($newfilename, 'wb'))
					{
						@fwrite($fp, $res['filedata_gallery']);
						@fclose($fp);
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "attachment
							SET filedata_gallery = ''
							WHERE attachid = '" . $res['attachid'] . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						$notice .= '<li class="green">Moved (gallery photo) ' . $res['filename'] . ' to ' . $newfilename . '</li>';
					}
					else
					{
						$notice .= '<li class="red">Could not move (gallery photo) ' . $res['filename'] . ' to ' . $newfilename . ' (write permission?)</li>';
					}
				}
				if (!empty($res['filedata_snapshot']))
				{
					$attachpath = $this->fetch_attachment_path($res['attachtype'], false);
					$newfilename = $attachpath . 'resized/snapshot/' . $res['filehash'] . '.attach';
					if (file_exists($newfilename))
					{
						@unlink($newfilename);
					}
					if ($fp = @fopen($newfilename, 'wb'))
					{
						@fwrite($fp, $res['filedata_snapshot']);
						@fclose($fp);
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "attachment
							SET filedata_snapshot = ''
							WHERE attachid = '" . $res['attachid'] . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						$notice .= '<li class="green">Moved (snapshot photo) ' . $res['filename'] . ' to ' . $newfilename . '</li>';
					}
					else
					{
						$notice .= '<li class="red">Could not move (snapshot photo) ' . $res['filename'] . ' to ' . $newfilename . ' (write permission?)</li>';
					}
				}
				if ($count % 100 == 0)
				{
					sleep(3); // give the db and hd a 3 second break..
				}
				$count++;
			}
			$notice .= '</ol>';
		}
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "configuration
			SET value = '0'
			WHERE name = 'attachment_dbstorage'
		", 0, null, __FILE__, __LINE__);
		return $notice;
	}

	/*
	* Function to move all attachments within the filepath to the database
	*
	* @return      string         Returns a notice of actions that occured
	*/
	function move_attachments_to_database($deletemoved = true)
	{
		@set_time_limit(0);
		global $ilance, $ilconfig, $phrase, $ilpage;
		$notice = '';
		$sql = $ilance->db->query("
			SELECT attachid, attachtype, filehash, filename, filesize, filesize_original, filesize_full, filesize_mini, filesize_search, filesize_gallery, filesize_snapshot
			FROM " . DB_PREFIX . "attachment
			ORDER BY attachid ASC
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$notice = '<ol>';
			$count = 1;
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				if ($res['filesize'] > 0)
				{
					$attachpath = $this->fetch_attachment_path($res['attachtype'], false);
					$filename = $attachpath . $res['filehash'] . '.attach';
					if (file_exists($filename))
					{
						$filesize = @filesize($filename);
						if ($fp = @fopen($filename, 'rb'))
						{
							$newfiledata = @fread($fp, $filesize);
							@fclose($fp);
							if (empty($newfiledata) OR $newfiledata == false)
							{
								$newfiledata = 'empty_filedata';
							}
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "attachment
								SET filedata = '" . $ilance->db->escape_string($newfiledata) . "'
								WHERE attachid = '" . $res['attachid'] . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							unset($newfiledata);
							if ($deletemoved)
							{
								@unlink($filename);
							}
							$notice .= '<li>Moved (default) ' . $filename . ' to database</li>';
						}
						else
						{
							$notice .= '<li class="red">Could not move (default) ' . $filename . ' to database</li>';
						}
					}
					else
					{
						$notice .= '<li class="red">Could not move (default) ' . $filename . ' to database (file does not exist)</li>';
					}
				}
				if ($res['filesize_original'] > 0)
				{
					$attachpath = $this->fetch_attachment_path($res['attachtype'], true); // original attachment
					$filename = $attachpath . $res['filehash'] . '.attach';
					if (file_exists($filename))
					{
						$filesize = @filesize($filename);
						if ($fp = @fopen($filename, 'rb'))
						{
							$newfiledata = @fread($fp, $filesize);
							@fclose($fp);
							if (empty($newfiledata) OR $newfiledata == false)
							{
							    $newfiledata = 'empty_filedata';
							}
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "attachment
								SET filedata_original = '" . $ilance->db->escape_string($newfiledata) . "'
								WHERE attachid = '" . $res['attachid'] . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							unset($newfiledata);
							if ($deletemoved)
							{
								@unlink($filename);
							}
							$notice .= '<li class="green">Moved (original) ' . $filename . ' to database</li>';
						}
						else
						{
							$notice .= '<li class="red">Could not move (original) ' . $filename . ' to database</li>';
						}
					}
					else
					{
						$notice .= '<li class="red">Could not move (original) ' . $filename . ' to database (file does not exist)</li>';
					}
				}
				if ($res['filesize_full'] > 0)
				{
					$attachpath = $this->fetch_attachment_path($res['attachtype'], false);
					$filename = $attachpath . 'resized/full/' . $res['filehash'] . '.attach';
					if (file_exists($filename))
					{
						$filesize = @filesize($filename);
						if ($fp = @fopen($filename, 'rb'))
						{
							$newfiledata = @fread($fp, $filesize);
							@fclose($fp);
							if (empty($newfiledata) OR $newfiledata == false)
							{
							    $newfiledata = 'empty_filedata';
							}
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "attachment
								SET filedata_full = '" . $ilance->db->escape_string($newfiledata) . "'
								WHERE attachid = '" . $res['attachid'] . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							unset($newfiledata);
							if ($deletemoved)
							{
								@unlink($filename);
							}
							$notice .= '<li class="green">Moved (full photo) ' . $filename . ' to database</li>';
						}
						else
						{
							$notice .= '<li class="red">Could not move (full photo) ' . $filename . ' to database</li>';
						}
					}
					else
					{
						$notice .= '<li class="red">Could not move (full photo) ' . $filename . ' to database (file does not exist)</li>';
					}
				}
				if ($res['filesize_mini'] > 0)
				{
					$attachpath = $this->fetch_attachment_path($res['attachtype'], false);
					$filename = $attachpath . 'resized/mini/' . $res['filehash'] . '.attach';
					if (file_exists($filename))
					{
						$filesize = @filesize($filename);
						if ($fp = @fopen($filename, 'rb'))
						{
							$newfiledata = @fread($fp, $filesize);
							@fclose($fp);
							if (empty($newfiledata) OR $newfiledata == false)
							{
								$newfiledata = 'empty_filedata';
							}
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "attachment
								SET filedata_mini = '" . $ilance->db->escape_string($newfiledata) . "'
								WHERE attachid = '" . $res['attachid'] . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							unset($newfiledata);
							if ($deletemoved)
							{
								@unlink($filename);
							}
							$notice .= '<li class="green">Moved (mini photo) ' . $filename . ' to database</li>';
						}
						else
						{
							$notice .= '<li class="red">Could not move (mini photo) ' . $filename . ' to database</li>';
						}
					}
					else
					{
						$notice .= '<li class="red">Could not move (mini photo) ' . $filename . ' to database (file does not exist)</li>';
					}
				}
				if ($res['filesize_search'] > 0)
				{
					$attachpath = $this->fetch_attachment_path($res['attachtype'], false);
					$filename = $attachpath . 'resized/search/' . $res['filehash'] . '.attach';
					if (file_exists($filename))
					{
						$filesize = @filesize($filename);
						if ($fp = @fopen($filename, 'rb'))
						{
							$newfiledata = @fread($fp, $filesize);
							@fclose($fp);
							if (empty($newfiledata) OR $newfiledata == false)
							{
								$newfiledata = 'empty_filedata';
							}
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "attachment
								SET filedata_search = '" . $ilance->db->escape_string($newfiledata) . "'
								WHERE attachid = '" . $res['attachid'] . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							unset($newfiledata);
							if ($deletemoved)
							{
								@unlink($filename);
							}
							$notice .= '<li class="green">Moved (search photo) ' . $filename . ' to database</li>';
						}
						else
						{
							$notice .= '<li class="red">Could not move (search photo) ' . $filename . ' to database</li>';
						}
					}
					else
					{
						$notice .= '<li class="red">Could not move (search photo) ' . $filename . ' to database (file does not exist)</li>';
					}
				}
				if ($res['filesize_gallery'] > 0)
				{
					$attachpath = $this->fetch_attachment_path($res['attachtype'], false);
					$filename = $attachpath . 'resized/gallery/' . $res['filehash'] . '.attach';
					if (file_exists($filename))
					{
						$filesize = @filesize($filename);
						if ($fp = @fopen($filename, 'rb'))
						{
							$newfiledata = @fread($fp, $filesize);
							@fclose($fp);
							if (empty($newfiledata) OR $newfiledata == false)
							{
								$newfiledata = 'empty_filedata';
							}
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "attachment
								SET filedata_gallery = '" . $ilance->db->escape_string($newfiledata) . "'
								WHERE attachid = '" . $res['attachid'] . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							unset($newfiledata);
							if ($deletemoved)
							{
							     @unlink($filename);
							}
							$notice .= '<li class="green">Moved (gallery photo) ' . $filename . ' to database</li>';
						}
						else
						{
							$notice .= '<li class="red">Could not move (gallery photo) ' . $filename . ' to database</li>';
						}
					}
					else
					{
						$notice .= '<li class="red">Could not move (gallery photo) ' . $filename . ' to database (file does not exist)</li>';
					}
				}
				if ($res['filesize_snapshot'] > 0)
				{
					$attachpath = $this->fetch_attachment_path($res['attachtype'], false);
					$filename = $attachpath . 'resized/snapshot/' . $res['filehash'] . '.attach';
					if (file_exists($filename))
					{
						$filesize = @filesize($filename);
						if ($fp = @fopen($filename, 'rb'))
						{
							$newfiledata = @fread($fp, $filesize);
							@fclose($fp);
							if (empty($newfiledata) OR $newfiledata == false)
							{
								$newfiledata = 'empty_filedata';
							}
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "attachment
								SET filedata_snapshot = '" . $ilance->db->escape_string($newfiledata) . "'
								WHERE attachid = '" . $res['attachid'] . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							unset($newfiledata);
							if ($deletemoved)
							{
								@unlink($filename);
							}
							$notice .= '<li class="green">Moved (snapshot photo) ' . $filename . ' to database</li>';
						}
						else
						{
							$notice .= '<li class="red">Could not move (snapshot photo) ' . $filename . ' to database</li>';
						}
					}
					else
					{
						$notice .= '<li class="red">Could not move (snapshot photo) ' . $filename . ' to database (file does not exist)</li>';
					}
				}
				if ($count % 100 == 0)
				{
					sleep(3);
				}
				$count++;
			}
			$notice .= '</ol>';
		}
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "configuration
			SET value = '1'
			WHERE name = 'attachment_dbstorage'
		");
		return $notice;
	}

	/*
	* Function to download and save an attachment from a remote url
	*
	* @param        string      image url (example: http://server.com/image.gif)
	* @param        string      full path to auction pictures save folder
	* @param        boolean     save original uncompressed picture (default true)
	* @param        string      remote connection method (curl, fgc, fsockopen)
	*
	* @return	boolean     returns true or false
	*/
	function save_url_image($img = '', $fullpath = DIR_AUCTION_ATTACHMENTS, $saveoriginal = true, $method = 'curl')
	{
		if (!empty($img))
		{
			if ($method == 'fgc')
			{
				$rawdata = file_get_contents($img);
				if (empty($rawdata))
				{
					return '302';
				}
			}
			else if ($method == 'curl' AND extension_loaded('curl'))
			{
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $img);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
				curl_setopt($ch, CURLOPT_TIMEOUT, 20);
				$rawdata = curl_exec($ch);
				curl_close($ch);
				if (empty($rawdata))
				{
					return '302';
				}
			}
			$filehash = md5(uniqid(microtime()));
			$filetype = '';
			if ($fp = fopen($fullpath . 'original/' . $filehash . '.attach', 'x'))
			{
				fwrite($fp, $rawdata);
				fclose($fp);
				if ($fp2 = fopen($fullpath . $filehash . '.attach', 'x')) // can be removed later..
				{
					fwrite($fp2, $rawdata);
					fclose($fp2);
				}
				if ($fp3 = fopen($fullpath . 'resized/full/' . $filehash . '.attach', 'x'))
				{
					fwrite($fp3, $rawdata);
					fclose($fp3);
				}
				if ($fp4 = fopen($fullpath . 'resized/mini/' . $filehash . '.attach', 'x'))
				{
					fwrite($fp4, $rawdata);
					fclose($fp4);
				}
				if ($fp5 = fopen($fullpath . 'resized/search/' . $filehash . '.attach', 'x'))
				{
					fwrite($fp5, $rawdata);
					fclose($fp5);
				}
				if ($fp6 = fopen($fullpath . 'resized/gallery/' . $filehash . '.attach', 'x'))
				{
					fwrite($fp6, $rawdata);
					fclose($fp6);
				}
				if ($fp7 = fopen($fullpath . 'resized/snapshot/' . $filehash . '.attach', 'x'))
				{
					fwrite($fp7, $rawdata);
					fclose($fp7);
				}
				unset($rawdata);
				if ($data = getimagesize($fullpath . 'original/' . $filehash . '.attach'))
				{
					if (!empty($data['mime']))
					{
						$filetype = $data['mime'];
					}
					return array (
						'fullpath' => $fullpath . $filehash . '.attach', // can be removed later..
						'fullpath_original' => $fullpath . 'original/' . $filehash . '.attach',
						'fullpath_full' => $fullpath . 'resized/full/' . $filehash . '.attach',
						'fullpath_mini' => $fullpath . 'resized/mini/' . $filehash . '.attach',
						'fullpath_search' => $fullpath . 'resized/search/' . $filehash . '.attach',
						'fullpath_gallery' => $fullpath . 'resized/gallery/' . $filehash . '.attach',
						'fullpath_snapshot' => $fullpath . 'resized/snapshot/' . $filehash . '.attach',
						'filename' => $this->fetch_url_image_filename($img, $filehash . '.attach'),
						'filehash' => $filehash,
						'filetype' => $filetype,
						'width' => $data[0],
						'height' => $data[1]
					);
				}
			}
		}
		return '';
	}

	/*
	* Function to fetch a remote image and determine it's physical file size using curl or fsockopen as the engine
	*
	* @param        string      image url (example: http://server.com/image.gif)
	* @param        boolean     return human readable output version (ie: 10KB vs 10000)? default true
	* @param        string      engine type to use (curl or fsock)
	*
	* @return	string      Returns remote file size if applicable
	*/
	function get_remote_file_size($url = '', $readable = true, $engine = 'fsock')
	{
		if ($engine == 'curl')
		{
			// Create a curl connection
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.11) Gecko/20071127 Firefox/2.0.0.11");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_NOBODY, true);
			$chGetSizeStore = curl_exec($ch);
			$return = curl_getinfo($ch);
			curl_close($ch); // Print the file size in bytes
			if (is_array($return) AND $readable AND $return['download_content_length'] != '-1')
			{
				$size = round($return['download_content_length'] / 1024, 2);
				$sz = "KB";
				if ($size > 1024)
				{
					$size = round($size / 1024, 2);
					$sz = "MB";
				}
				$html = "$size $sz";
				return $html;
			}
			return $return['download_content_length'] == '-1' ? false : $return['download_content_length'];
		}
		else if ($engine == 'fsock')
		{
			$parsed = parse_url($url);
			if (isset($parsed["host"]))
			{
				$host = $parsed["host"];
				$fp = @fsockopen($host, 80, $errno, $errstr, 5);
				if (!$fp)
				{
					return false;
				}
				else
				{
					@fputs($fp, "HEAD $url HTTP/1.1\r\n");
					@fputs($fp, "HOST: $host\r\n");
					@fputs($fp, "Connection: close\r\n\r\n");
					$headers = "";
					while (!@feof($fp))
					    $headers .= @fgets($fp, 1024);
				}
				@fclose($fp);
				$return = false;
				$arr_headers = explode("\n", $headers);
				foreach ($arr_headers AS $header)
				{
					// follow redirect
					$s = 'Location: ';
					if (substr(strtolower($header), 0, strlen($s)) == strtolower($s))
					{
						$url = trim(substr($header, strlen($s)));
						return get_remote_file_size($url, $readable, $engine);
					}
					// parse for content length
					$s = "Content-Length: ";
					if (substr(strtolower($header), 0, strlen($s)) == strtolower($s))
					{
						$return = trim(substr($header, strlen($s)));
						break;
					}
				}
				if ($return AND $readable)
				{
					$size = round($return / 1024, 2);
					$sz = "KB";
					if ($size > 1024)
					{
						$size = round($size / 1024, 2);
						$sz = "MB";
					}
					$return = "$size $sz";
				}
				return $return;
			}
		}
		return false;
	}

	/**
	* Function to fetch the actual filename of an image called over http: or https:// protocol.
	*
	* @param       string        full url including image filename (ex: http://domain.com/image1.jpg)
	* @param       string        backup image name to use if we cannot process the url version
	*
	* @return      string        Returns image filename (ex: image1.jpg)
	*/
	function fetch_url_image_filename($img = '', $backupimg = '')
	{
		if (!empty($img))
		{
			$ar = explode('/', $img);
			$filename = $ar[count($ar) - 1];
			return $filename;
		}
		return $backupimg;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>