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
* Auction tabs class to perform the majority of printing and displaying of auction tabs within the MyCP areas of the front end.
*
* @package      iLance\Auction\Pictures
* @version      4.0.0.8059
* @author       ILance
*/
class auction_pictures extends auction
{
	/**
	* Function for handling a remote image url through a bulk file upload..
	* This will fetch the file from the remote server via curl, check if the file is valid (is an image),
	* extract it's contents and save to the filepath or the database.
	*
	* @param          string       image url (full url with image, ie: http://www.domain.com/image1.jpg
	* @param          integer      listing id we're assigning this image to
	* @param          integer      user id
	* @param          integer      category id
	* @param          integer      bulk upload set id
	* @param          string       attachtype (default itemphoto)
	* @param          integer      custom field stored in tblfolder_ref (mainly for add-ons)
	*
	* @return         array        Returns array of image information from save_url_image()
	*/
	function handle_remote_image_url($imgurl = '', $project_id = 0, $user_id = 0, $cid = 0, $bulk_id = 0, $attachtype = 'itemphoto', $tblfolder_ref = 0)
	{
                global $ilance, $ilconfig;
		$upload_file_size = $uploaded = $iswatermarked = $upload_file_size_original = $height_original = $width_original = $pictureresized = $width_default = $height_default = $width_full = $height_full = $width_mini = $height_mini = $width_gallery = $height_gallery = $width_search = $height_search = $width_snapshot = $height_snapshot = $upload_file_size_full = $upload_file_size_mini = $upload_file_size_search = $upload_file_size_gallery = $upload_file_size_snapshot = 0;
                $exif = $filedata = $data = $newfilename = $filename = $filehash = $filetype = $filetype_original = $filedata_original = $filedata_full = $filedata_mini = $filedata_search = $filedata_gallery = $filedata_snapshot = '';
                $sql = false;
			 
		($apihook = $ilance->api('handle_remote_image_url_start')) ? eval($apihook) : false;
				                
                $data = $ilance->attachment_tools->save_url_image($imgurl); // saves remote image to image.gif and original/image.gif..
                if (!empty($data) AND is_array($data))
                {
                        $newfilename = $data['fullpath'];
			$newfilename_original = $data['fullpath_original'];
			$newfilename_full = $data['fullpath_full'];
			$newfilename_mini = $data['fullpath_mini'];
			$newfilename_search = $data['fullpath_search'];
			$newfilename_gallery = $data['fullpath_gallery'];
			$newfilename_snapshot = $data['fullpath_snapshot'];
                        $filename = $data['filename'];
                        $filehash = $data['filehash'];
                        $filetype = $filetype_original = $data['filetype'];
			$width_original = $width_default = $width_full = $width_mini = $width_search = $width_gallery = $width_snapshot = $data['width'];
			$height_original = $height_default = $height_full = $height_mini = $height_search = $height_gallery = $height_snapshot = $data['height'];
			if (!empty($newfilename) AND !empty($filename))
			{
				//$extension = mb_strtolower(mb_strrchr($filename, '.'));
				$extension = str_replace('/', '.', mb_strtolower(mb_strrchr($filetype, '/')));
				$allowedextensions = array('.jpg', '.jpeg', '.gif', '.png', '.bmp');
				if (in_array($extension, $allowedextensions))
				{
					$upload_file_size_original = $upload_file_size = $upload_file_size_full = $upload_file_size_mini = $upload_file_size_search = $upload_file_size_gallery = $upload_file_size_snapshot = @filesize($newfilename_original);
					// #### fetch exif information (extended image support)
					if (function_exists('exif_read_data'))
					{
						$exifdata = @exif_read_data($newfilename_original, 0, true);
						if (!empty($exifdata))
						{
							$exif = serialize($exifdata);
						}
						unset($exifdata);
					}
					// #### scale images down if need be
					$fileinfo = getimagesize($newfilename);
					if (!empty($fileinfo) AND is_array($fileinfo))
					{
						// original picture to be resized..
						$ilance->attachment->file_name = $filename; // image.gif
						if ($fileinfo[0] > $ilconfig['attachmentlimit_productphotomaxwidth'] OR $fileinfo[1] > $ilconfig['attachmentlimit_productphotomaxheight'])
						{
							$ilance->attachment->picture_resizer($newfilename, $ilconfig['attachmentlimit_productphotomaxwidth'], $ilconfig['attachmentlimit_productphotomaxheight'], $extension, $fileinfo[0], $fileinfo[1], '');
							if ($ilance->attachment->pictureresized)
							{
								$filetype = $ilance->attachment->filetype; // image/jpeg
								$filename = $ilance->attachment->file_name; // image.jpg
								$width_default = $ilance->attachment->width; // 100 
								$height_default = $ilance->attachment->height; // 80
								$upload_file_size = @filesize($newfilename);
							}
						}
						if ($fileinfo[0] > $ilconfig['attachmentlimit_productphotowidth'] OR $fileinfo[1] > $ilconfig['attachmentlimit_productphotoheight'])
						{
							$ilance->attachment->picture_resizer($newfilename_full, $ilconfig['attachmentlimit_productphotowidth'], $ilconfig['attachmentlimit_productphotoheight'], $extension, $fileinfo[0], $fileinfo[1], '');
							$ilance->attachment->watermark($attachtype, $newfilename_full, $extension, '');
							if ($ilance->attachment->pictureresized)
							{
								$filetype = $ilance->attachment->filetype;
								$filename = $ilance->attachment->file_name;
								$width_full = $ilance->attachment->width;
								$height_full = $ilance->attachment->height;
								$upload_file_size_full = @filesize($newfilename_full);
							}
						}
						if ($fileinfo[0] > $ilconfig['attachmentlimit_productphotothumbwidth'] OR $fileinfo[1] > $ilconfig['attachmentlimit_productphotothumbheight'])
						{
							$ilance->attachment->picture_resizer($newfilename_mini, $ilconfig['attachmentlimit_productphotothumbwidth'], $ilconfig['attachmentlimit_productphotothumbheight'], $extension, $fileinfo[0], $fileinfo[1], '');
							if ($ilance->attachment->pictureresized)
							{
								$filetype = $ilance->attachment->filetype;
								$filename = $ilance->attachment->file_name;
								$width_mini = $ilance->attachment->width;
								$height_mini = $ilance->attachment->height;
								$upload_file_size_mini = @filesize($newfilename_mini);
							}
						}
						if ($fileinfo[0] > $ilconfig['attachmentlimit_searchresultsmaxwidth'] OR $fileinfo[1] > $ilconfig['attachmentlimit_searchresultsmaxheight'])
						{
							$ilance->attachment->picture_resizer($newfilename_search, $ilconfig['attachmentlimit_searchresultsmaxwidth'], $ilconfig['attachmentlimit_searchresultsmaxheight'], $extension, $fileinfo[0], $fileinfo[1], '');
							if ($ilance->attachment->pictureresized)
							{
								$filetype = $ilance->attachment->filetype;
								$filename = $ilance->attachment->file_name;
								$width_search = $ilance->attachment->width;
								$height_search = $ilance->attachment->height;
								$upload_file_size_search = @filesize($newfilename_search);
							}
						}
						if ($fileinfo[0] > $ilconfig['attachmentlimit_searchresultsgallerymaxwidth'] OR $fileinfo[1] > $ilconfig['attachmentlimit_searchresultsgallerymaxheight'])
						{
							$ilance->attachment->picture_resizer($newfilename_gallery, $ilconfig['attachmentlimit_searchresultsgallerymaxwidth'], $ilconfig['attachmentlimit_searchresultsgallerymaxheight'], $extension, $fileinfo[0], $fileinfo[1], '');
							if ($ilance->attachment->pictureresized)
							{
								$filetype = $ilance->attachment->filetype;
								$filename = $ilance->attachment->file_name;
								$width_gallery = $ilance->attachment->width;
								$height_gallery = $ilance->attachment->height;
								$upload_file_size_gallery = @filesize($newfilename_gallery);
							}
						}
						if ($fileinfo[0] > $ilconfig['attachmentlimit_searchresultssnapshotmaxwidth'] OR $fileinfo[1] > $ilconfig['attachmentlimit_searchresultssnapshotmaxheight'])
						{
							$ilance->attachment->picture_resizer($newfilename_snapshot, $ilconfig['attachmentlimit_searchresultssnapshotmaxwidth'], $ilconfig['attachmentlimit_searchresultssnapshotmaxheight'], $extension, $fileinfo[0], $fileinfo[1], '');
							if ($ilance->attachment->pictureresized)
							{
								$filetype = $ilance->attachment->filetype;
								$filename = $ilance->attachment->file_name;
								$width_snapshot = $ilance->attachment->width;
								$height_snapshot = $ilance->attachment->height;
								$upload_file_size_snapshot = @filesize($newfilename_snapshot);
							}
						}
					}
					// #### determine if we're using the database to save
					if ($ilconfig['attachment_dbstorage'])
					{
						$filedata = @fread(@fopen($newfilename, 'rb'), @filesize($newfilename));
						$filedata_original = @fread(@fopen($newfilename_original, 'rb'), @filesize($newfilename_original));
						$filedata_full = @fread(@fopen($newfilename_full, 'rb'), @filesize($newfilename_full));
						$filedata_mini = @fread(@fopen($newfilename_mini, 'rb'), @filesize($newfilename_mini));
						$filedata_search = @fread(@fopen($newfilename_search, 'rb'), @filesize($newfilename_search));
						$filedata_gallery = @fread(@fopen($newfilename_gallery, 'rb'), @filesize($newfilename_gallery));
						$filedata_snapshot = @fread(@fopen($newfilename_snapshot, 'rb'), @filesize($newfilename_snapshot));
						@unlink($newfilename);
						@unlink($newfilename_original);
						@unlink($newfilename_full);
						@unlink($newfilename_mini);
						@unlink($newfilename_search); 
						@unlink($newfilename_gallery);
						@unlink($newfilename_snapshot);
					}
					$ilance->db->query("
						INSERT INTO " . DB_PREFIX . "attachment
						(attachid, attachtype, user_id, project_id, category_id, bulk_id, date, filename, filedata, filedata_original, filedata_full, filedata_mini, filedata_search, filedata_gallery, filedata_snapshot, filetype, filetype_original, width, width_original, width_full, width_mini, width_search, width_gallery, width_snapshot, height, height_original, height_full, height_mini, height_search, height_gallery, height_snapshot, visible, counter, filesize, filesize_original, filesize_full, filesize_mini, filesize_search, filesize_gallery, filesize_snapshot, filehash, ipaddress, exifdata, tblfolder_ref, watermarked)
						VALUES(
						NULL,
						'" . $ilance->db->escape_string($attachtype) . "',
						'" . intval($user_id) . "',
						'" . intval($project_id) . "',
						'" . intval($cid) . "',
						'" . intval($bulk_id) . "',
						'" . DATETIME24H . "',
						'" . $ilance->db->escape_string($filename) . "',
						'" . $ilance->db->escape_string($filedata) . "',
						'" . $ilance->db->escape_string($filedata_original) . "',
						'" . $ilance->db->escape_string($filedata_full) . "',
						'" . $ilance->db->escape_string($filedata_mini) . "',
						'" . $ilance->db->escape_string($filedata_search) . "',
						'" . $ilance->db->escape_string($filedata_gallery) . "',
						'" . $ilance->db->escape_string($filedata_snapshot) . "',
						'" . $ilance->db->escape_string($filetype) . "',
						'" . $ilance->db->escape_string($filetype_original) . "',
						'" . intval($width_default) . "',
						'" . intval($width_original) . "',
						'" . intval($width_full) . "',
						'" . intval($width_mini) . "',
						'" . intval($width_search) . "',
						'" . intval($width_gallery) . "',
						'" . intval($width_snapshot) . "',
						'" . intval($height_default) . "',
						'" . intval($height_original) . "',
						'" . intval($height_full) . "',
						'" . intval($height_mini) . "',
						'" . intval($height_search) . "',
						'" . intval($height_gallery) . "',
						'" . intval($height_snapshot) . "',
						'" . intval($ilconfig['attachment_moderationdisabled']) . "',
						'0',
						'" . $ilance->db->escape_string($upload_file_size) . "',
						'" . $ilance->db->escape_string($upload_file_size_original) . "',
						'" . $ilance->db->escape_string($upload_file_size_full) . "',
						'" . $ilance->db->escape_string($upload_file_size_mini) . "',
						'" . $ilance->db->escape_string($upload_file_size_search) . "',
						'" . $ilance->db->escape_string($upload_file_size_gallery) . "',
						'" . $ilance->db->escape_string($upload_file_size_snapshot) . "',
						'" . $ilance->db->escape_string($filehash) . "',
						'" . $ilance->db->escape_string(IPADDRESS) . "',
						'" . $ilance->db->escape_string($exif) . "',
						'" . $ilance->db->escape_string($tblfolder_ref) . "',
						'" . intval($iswatermarked) . "')
					", 0, null, __FILE__, __LINE__);
					switch ($attachtype)
					{
						case 'itemphoto':
						case 'slideshow':
						{
							$itemphotocount = $ilance->attachment->fetch_listing_photo_count($project_id);
							if ($itemphotocount <= 0)
							{
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "projects
									SET hasimage = '0', hasimageslideshow = '0'
									WHERE project_id = '" . intval($project_id) . "'
								", 0, null, __FILE__, __LINE__);
							}
							else if ($itemphotocount == 1)
							{
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "projects
									SET hasimage = '1', hasimageslideshow = '0'
									WHERE project_id = '" . intval($project_id) . "'
								", 0, null, __FILE__, __LINE__);
							}
							else if ($itemphotocount > 1)
							{
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "projects
									SET hasimage = '1', hasimageslideshow = '1'
									WHERE project_id = '" . intval($project_id) . "'
								", 0, null, __FILE__, __LINE__);
							}
							break;
						}
						case 'digital':
						{
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "projects
								SET hasdigitalfile = '1'
								WHERE project_id = '" . intval($project_id) . "'
							", 0, null, __FILE__, __LINE__);
							break;
						}
					}
					
					($apihook = $ilance->api('handle_remote_image_url_conditions')) ? eval($apihook) : false;
				}
			}
                }
		
		($apihook = $ilance->api('handle_remote_image_url_end')) ? eval($apihook) : false;
		
                return $data;
        }
	
	/**
        * Function to insert photos from a previous bulk upload session
        *
        * @return         string       Returns string to send into cron log performed for this task
        */
        function process_bulk_upload_photos()
        {
		global $ilance, $ilconfig, $show;
		$a = $b = 0;
		$notice = '';
		$sql = $ilance->db->query("
			SELECT rfpid, sample, id, user_id, cid, bulk_id
			FROM " . DB_PREFIX . "bulk_tmp
			WHERE sample_uploaded = '0'
				AND correct = '1'
				AND sample != ''
				AND rfpid > 0
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{	
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$sample = $res['sample'];
				$pos = strpos($sample, '|');
				if ($pos)
				{
					$pictures = explode('|', $sample);
					$i = 0;
					foreach ($pictures AS $picture)
					{
						if (!empty($picture) AND $i <= $ilconfig['attachmentlimit_slideshowmaxfiles'])
						{
							//$attachtype = ($i == 0) ? 'itemphoto' : 'slideshow';
							$attachtype = 'slideshow';
							$result = $this->handle_remote_image_url(trim($picture), $res['rfpid'], $res['user_id'], $res['cid'], $res['bulk_id'], $attachtype);
							if (is_array($result))
							{
								$a++;
								$i++;
							}
							else 
							{
								$notice .= $result;
							}
						}
					}
					if ($i > 0)
					{
						$ilance->db->query("
							DELETE FROM " . DB_PREFIX . "bulk_tmp
							WHERE id = '" . $res['id'] . "'
						", 0, null, __FILE__, __LINE__);
					}
					else 
					{
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "bulk_tmp
							SET sample_uploaded = '4'
							WHERE bulk_id = '" . intval($res['bulk_id']) . "'
						", 0, null, __FILE__, __LINE__);
					}
				}
				// single item photo only
				else
				{
					$result = $this->handle_remote_image_url(trim($res['sample']), $res['rfpid'], $res['user_id'], $res['cid'], $res['bulk_id'], 'slideshow');
					if (is_array($result))
					{
						$a++;
						$ilance->db->query("
							DELETE FROM " . DB_PREFIX . "bulk_tmp
							WHERE id = '" . $res['id'] . "'
						", 0, null, __FILE__, __LINE__);
					}
					else 
					{
						$notice .= $result;
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "bulk_tmp
							SET sample_uploaded = '4'
							WHERE bulk_id = '" . intval($res['bulk_id']) . "'
						", 0, null, __FILE__, __LINE__);
					}
				}
			}
		}
		else 
		{
			$sql2 = $ilance->db->query("
				SELECT rfpid, sample, id, user_id
				FROM " . DB_PREFIX . "bulk_tmp
				WHERE (correct = '0' OR rfpid = '0' OR sample_uploaded = '2' OR sample_uploaded = '3')
					AND dateupload != '" . DATETODAY . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql2) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql2, DB_ASSOC))
				{
					$ilance->db->query("
						DELETE FROM " . DB_PREFIX . "bulk_tmp
						WHERE id = '" . $res['id'] . "'
					", 0, null, __FILE__, __LINE__);
					$b++;
				}
			}
		}
		
		($apihook = $ilance->api('process_bulk_upload_photos_end')) ? eval($apihook) : false;
		
		return "added $a photos to items uploaded from CSV, deleted $b incorrect listings from the previous day from bulk upload. Notice: $notice";
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>