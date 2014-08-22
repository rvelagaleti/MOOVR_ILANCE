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
* Auction tabs class to perform the majority of picture rebuilding functions for ILance
*
* @package      iLance\Auction\Pictures\Rebuilder
* @version      4.0.0.8059
* @author       ILance
*/
class auction_pictures_rebuilder extends auction
{
	function process_picture_rebuilder($attachid = 0)
	{
		global $ilance, $ilconfig, $show;
		$ilance->attachment = construct_object('api.attachment');
                $originalattachtypes = array('profile','portfolio','itemphoto','slideshow');
		$extraquery = "";
		if ($ilconfig['attachment_dbstorage'])
		{
			if ($attachid > 0)
			{
				$extraquery = "AND attachid = '" . intval($attachid) . "'";
			}
			$sql = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "attachment
				WHERE (filedata != '' OR filedata_original != '')
				$extraquery
				ORDER BY attachid ASC
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
                                ($apihook = $ilance->api('process_picture_rebuilder_start')) ? eval($apihook) : false;
				
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					$rawdata = '';
					if (in_array($res['filetype'], $ilance->attachment->mimetypes) AND in_array($res['attachtype'], $originalattachtypes))
					{
						if (!empty($res['filedata_original']))
						{
							$show['fetchoriginal'] = true;
							$rawdata = $res['filedata_original'];
						}
						else
						{
							$show['fetchoriginal'] = false;
							$rawdata = $res['filedata'];
						}
						$attachtype = $res['attachtype'];
						$filehash = $res['filehash'];
						$filetype = '';
						if ($attachtype == 'profile')
						{
							$fullpath = DIR_PROFILE_ATTACHMENTS;
							$maxwidth_default = $ilconfig['attachmentlimit_profilemaxwidth'];
							$maxheight_default = $ilconfig['attachmentlimit_profilemaxheight'];
							$maxwidth_full = $ilconfig['attachmentlimit_profilemaxwidth'];
							$maxheight_full = $ilconfig['attachmentlimit_profilemaxheight'];
							$maxwidth_mini = $ilconfig['attachmentlimit_productphotothumbwidth'];
							$maxheight_mini = $ilconfig['attachmentlimit_productphotothumbheight'];
							$maxwidth_search = $ilconfig['attachmentlimit_profilemaxwidth'];
							$maxheight_search = $ilconfig['attachmentlimit_profilemaxheight'];
							$maxwidth_gallery = $ilconfig['attachmentlimit_searchresultsgallerymaxwidth'];
							$maxheight_gallery = $ilconfig['attachmentlimit_searchresultsgallerymaxheight'];
							$maxwidth_snapshot = $ilconfig['attachmentlimit_searchresultssnapshotmaxwidth'];
							$maxheight_snapshot = $ilconfig['attachmentlimit_searchresultssnapshotmaxheight'];
						}
						else if ($attachtype == 'portfolio')
						{
							$fullpath = DIR_PORTFOLIO_ATTACHMENTS;
							$maxwidth_default = $ilconfig['attachmentlimit_portfoliothumbwidth'];
							$maxheight_default = $ilconfig['attachmentlimit_portfoliothumbheight'];
							$maxwidth_full = $ilconfig['attachmentlimit_productphotowidth'];
							$maxheight_full = $ilconfig['attachmentlimit_productphotoheight'];
							$maxwidth_mini = $ilconfig['attachmentlimit_productphotothumbwidth'];
							$maxheight_mini = $ilconfig['attachmentlimit_productphotothumbheight'];
							$maxwidth_search = $ilconfig['attachmentlimit_searchresultsmaxwidth'];
							$maxheight_search = $ilconfig['attachmentlimit_searchresultsmaxheight'];
							$maxwidth_gallery = $ilconfig['attachmentlimit_searchresultsgallerymaxwidth'];
							$maxheight_gallery = $ilconfig['attachmentlimit_searchresultsgallerymaxheight'];
							$maxwidth_snapshot = $ilconfig['attachmentlimit_searchresultssnapshotmaxwidth'];
							$maxheight_snapshot = $ilconfig['attachmentlimit_searchresultssnapshotmaxheight'];
						}
						else if ($attachtype == 'itemphoto')
						{
							$fullpath = DIR_AUCTION_ATTACHMENTS;
							$maxwidth_default = $ilconfig['attachmentlimit_productphotomaxwidth'];
							$maxheight_default = $ilconfig['attachmentlimit_productphotomaxheight'];
							$maxwidth_full = $ilconfig['attachmentlimit_productphotowidth'];
							$maxheight_full = $ilconfig['attachmentlimit_productphotoheight'];
							$maxwidth_mini = $ilconfig['attachmentlimit_productphotothumbwidth'];
							$maxheight_mini = $ilconfig['attachmentlimit_productphotothumbheight'];
							$maxwidth_search = $ilconfig['attachmentlimit_searchresultsmaxwidth'];
							$maxheight_search = $ilconfig['attachmentlimit_searchresultsmaxheight'];
							$maxwidth_gallery = $ilconfig['attachmentlimit_searchresultsgallerymaxwidth'];
							$maxheight_gallery = $ilconfig['attachmentlimit_searchresultsgallerymaxheight'];
							$maxwidth_snapshot = $ilconfig['attachmentlimit_searchresultssnapshotmaxwidth'];
							$maxheight_snapshot = $ilconfig['attachmentlimit_searchresultssnapshotmaxheight'];
						}
						else if ($attachtype == 'slideshow')
						{
							$fullpath = DIR_AUCTION_ATTACHMENTS;
							$maxwidth_default = $ilconfig['attachmentlimit_productphotomaxwidth'];
							$maxheight_default = $ilconfig['attachmentlimit_productphotomaxheight'];
							$maxwidth_full = $ilconfig['attachmentlimit_productphotowidth'];
							$maxheight_full = $ilconfig['attachmentlimit_productphotoheight'];
							$maxwidth_mini = $ilconfig['attachmentlimit_productphotothumbwidth'];
							$maxheight_mini = $ilconfig['attachmentlimit_productphotothumbheight'];
							$maxwidth_search = $ilconfig['attachmentlimit_searchresultsmaxwidth'];
							$maxheight_search = $ilconfig['attachmentlimit_searchresultsmaxheight'];
							$maxwidth_gallery = $ilconfig['attachmentlimit_searchresultsgallerymaxwidth'];
							$maxheight_gallery = $ilconfig['attachmentlimit_searchresultsgallerymaxheight'];
							$maxwidth_snapshot = $ilconfig['attachmentlimit_searchresultssnapshotmaxwidth'];
							$maxheight_snapshot = $ilconfig['attachmentlimit_searchresultssnapshotmaxheight'];
						}
						else
						{
							($apihook = $ilance->api('process_picture_rebuilder_loop_else')) ? eval($apihook) : false;
						}
						if (file_exists($fullpath . 'original/' . $filehash . '.attach'))
						{
							@unlink($fullpath . 'original/' . $filehash . '.attach');
						}
						if ($fp = fopen($fullpath . 'original/' . $filehash . '.attach', 'wb'))
						{
							fwrite($fp, $rawdata);
							fclose($fp);
							if (file_exists($fullpath . $filehash . '.attach'))
							{
								@unlink($fullpath . $filehash . '.attach');
							}
							if ($fp2 = fopen($fullpath . $filehash . '.attach', 'wb'))
							{
								fwrite($fp2, $rawdata);
								fclose($fp2);
							}
							if (file_exists($fullpath . 'resized/full/' . $filehash . '.attach'))
							{
								@unlink($fullpath . 'resized/full/' . $filehash . '.attach');
							}
							if ($fp3 = fopen($fullpath . 'resized/full/' . $filehash . '.attach', 'wb'))
							{
								fwrite($fp3, $rawdata);
								fclose($fp3);
							}
							if (file_exists($fullpath . 'resized/mini/' . $filehash . '.attach'))
							{
								@unlink($fullpath . 'resized/mini/' . $filehash . '.attach');
							}
							if ($fp4 = fopen($fullpath . 'resized/mini/' . $filehash . '.attach', 'wb'))
							{
								fwrite($fp4, $rawdata);
								fclose($fp4);
							}
							if (file_exists($fullpath . 'resized/search/' . $filehash . '.attach'))
							{
								@unlink($fullpath . 'resized/search/' . $filehash . '.attach');
							}
							if ($fp5 = fopen($fullpath . 'resized/search/' . $filehash . '.attach', 'wb'))
							{
								fwrite($fp5, $rawdata);
								fclose($fp5);
							}
							if (file_exists($fullpath . 'resized/gallery/' . $filehash . '.attach'))
							{
								@unlink($fullpath . 'resized/gallery/' . $filehash . '.attach');
							}
							if ($fp6 = fopen($fullpath . 'resized/gallery/' . $filehash . '.attach', 'wb'))
							{
								fwrite($fp6, $rawdata);
								fclose($fp6);
							}
							if (file_exists($fullpath . 'resized/snapshot/' . $filehash . '.attach'))
							{
								@unlink($fullpath . 'resized/snapshot/' . $filehash . '.attach');
							}
							if ($fp7 = fopen($fullpath . 'resized/snapshot/' . $filehash . '.attach', 'wb'))
							{
								fwrite($fp7, $rawdata);
								fclose($fp7);
							}
							unset($rawdata);
							if ($idata = getimagesize($fullpath . 'original/' . $filehash . '.attach'))
							{
								if (!empty($idata['mime']))
								{
									$filetype = $idata['mime'];
								}
								$data = array(
									'fullpath' => $fullpath . $filehash . '.attach', // can be removed later..
									'fullpath_original' => $fullpath . 'original/' . $filehash . '.attach',
									'fullpath_full' => $fullpath . 'resized/full/' . $filehash . '.attach',
									'fullpath_mini' => $fullpath . 'resized/mini/' . $filehash . '.attach',
									'fullpath_search' => $fullpath . 'resized/search/' . $filehash . '.attach',
									'fullpath_gallery' => $fullpath . 'resized/gallery/' . $filehash . '.attach',
									'fullpath_snapshot' => $fullpath . 'resized/snapshot/' . $filehash . '.attach',
									'filename' => $res['filename'],
									'filehash' => $filehash,
									'filetype' => $filetype,
									'width' => $idata[0],
									'height' => $idata[1]
								);
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
								$filedata = $filedata_original = $filedata_full = $filedata_mini = $filedata_search = $filedata_gallery = $filedata_snapshot = $exif = '';
								$iswatermarked = 0;
								if (!empty($newfilename) AND !empty($filename))
								{
									$extension = str_replace('/', '.', mb_strtolower(mb_strrchr($filetype, '/')));
									$allowedextensions = array('.jpg', '.jpeg', '.gif', '.png', '.bmp');
									if (in_array($extension, $allowedextensions))
									{
										$upload_file_size_original = $upload_file_size = $upload_file_size_full = $upload_file_size_mini = $upload_file_size_search = $upload_file_size_gallery = $upload_file_size_snapshot = @filesize($newfilename_original);
										if (function_exists('exif_read_data') AND ((isset($show['fetchoriginal']) AND $show['fetchoriginal']) OR (isset($show['fetchoriginal']) AND $show['fetchoriginal'] == false AND empty($res['exifdata']))))
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
											if ($fileinfo[0] > $maxwidth_default OR $fileinfo[1] > $maxheight_default)
											{
												$ilance->attachment->picture_resizer($newfilename, $maxwidth_default, $maxheight_default, $extension, $fileinfo[0], $fileinfo[1], '');
												if (((isset($show['fetchoriginal']) AND $show['fetchoriginal']) OR (isset($show['fetchoriginal']) AND $show['fetchoriginal'] == false AND $res['watermarked'] <= 0)))
												{
													//$ilance->attachment->watermark($attachtype, $newfilename, $extension, '');
													//$iswatermarked = $ilance->attachment->watermarked;
												}
												if ($ilance->attachment->pictureresized)
												{
													$width_default = $ilance->attachment->width; // 100 
													$height_default = $ilance->attachment->height; // 80
													$upload_file_size = @filesize($newfilename);
												}
											}
											if ($fileinfo[0] > $maxwidth_full OR $fileinfo[1] > $maxheight_full)
											{
												$ilance->attachment->picture_resizer($newfilename_full, $maxwidth_full, $maxheight_full, $extension, $fileinfo[0], $fileinfo[1], '');
												if (((isset($show['fetchoriginal']) AND $show['fetchoriginal']) OR (isset($show['fetchoriginal']) AND $show['fetchoriginal'] == false AND $res['watermarked'] <= 0)))
												{
													$ilance->attachment->watermark($attachtype, $newfilename_full, $extension, '');
													$iswatermarked = $ilance->attachment->watermarked;
												}
												if ($ilance->attachment->pictureresized)
												{
													$width_full = $ilance->attachment->width;
													$height_full = $ilance->attachment->height;
													$upload_file_size_full = @filesize($newfilename_full);
												}
											}
											if ($fileinfo[0] > $maxwidth_mini OR $fileinfo[1] > $maxheight_mini)
											{
												$ilance->attachment->picture_resizer($newfilename_mini, $maxwidth_mini, $maxheight_mini, $extension, $fileinfo[0], $fileinfo[1], '');
												if (((isset($show['fetchoriginal']) AND $show['fetchoriginal']) OR (isset($show['fetchoriginal']) AND $show['fetchoriginal'] == false AND $res['watermarked'] <= 0)))
												{
													//$ilance->attachment->watermark($attachtype, $newfilename_mini, $extension, '');
													//$iswatermarked = $ilance->attachment->watermarked;
												}
												if ($ilance->attachment->pictureresized)
												{
													$width_mini = $ilance->attachment->width;
													$height_mini = $ilance->attachment->height;
													$upload_file_size_mini = @filesize($newfilename_mini);
												}
											}
											if ($fileinfo[0] > $maxwidth_search OR $fileinfo[1] > $maxheight_search)
											{
												$ilance->attachment->picture_resizer($newfilename_search, $maxwidth_search, $maxheight_search, $extension, $fileinfo[0], $fileinfo[1], '');
												if (((isset($show['fetchoriginal']) AND $show['fetchoriginal']) OR (isset($show['fetchoriginal']) AND $show['fetchoriginal'] == false AND $res['watermarked'] <= 0)))
												{
													//$ilance->attachment->watermark($attachtype, $newfilename_search, $extension, '');
													//$iswatermarked = $ilance->attachment->watermarked;
												}
												if ($ilance->attachment->pictureresized)
												{
													$width_search = $ilance->attachment->width;
													$height_search = $ilance->attachment->height;
													$upload_file_size_search = @filesize($newfilename_search);
												}
											}
											if ($fileinfo[0] > $maxwidth_gallery OR $fileinfo[1] > $maxheight_gallery)
											{
												$ilance->attachment->picture_resizer($newfilename_gallery, $maxwidth_gallery, $maxheight_gallery, $extension, $fileinfo[0], $fileinfo[1], '');
												if (((isset($show['fetchoriginal']) AND $show['fetchoriginal']) OR (isset($show['fetchoriginal']) AND $show['fetchoriginal'] == false AND $res['watermarked'] <= 0)))
												{
													//$ilance->attachment->watermark($attachtype, $newfilename_gallery, $extension, '');
													//$iswatermarked = $ilance->attachment->watermarked;
												}
												if ($ilance->attachment->pictureresized)
												{
													$width_gallery = $ilance->attachment->width;
													$height_gallery = $ilance->attachment->height;
													$upload_file_size_gallery = @filesize($newfilename_gallery);
												}
											}
											if ($fileinfo[0] > $maxwidth_snapshot OR $fileinfo[1] > $maxheight_snapshot)
											{
												$ilance->attachment->picture_resizer($newfilename_snapshot, $maxwidth_snapshot, $maxheight_snapshot, $extension, $fileinfo[0], $fileinfo[1], '');
												if (((isset($show['fetchoriginal']) AND $show['fetchoriginal']) OR (isset($show['fetchoriginal']) AND $show['fetchoriginal'] == false AND $res['watermarked'] <= 0)))
												{
													//$ilance->attachment->watermark($attachtype, $newfilename_snapshot, $extension, '');
													//$iswatermarked = $ilance->attachment->watermarked;
												}
												if ($ilance->attachment->pictureresized)
												{
													$width_snapshot = $ilance->attachment->width;
													$height_snapshot = $ilance->attachment->height;
													$upload_file_size_snapshot = @filesize($newfilename_snapshot);
												}
											}
										}
										$filedata = @fread(@fopen($newfilename, 'rb'), @filesize($newfilename));
										$filedata_original = @fread(@fopen($newfilename_original, 'rb'), @filesize($newfilename_original));
										$filedata_full = @fread(@fopen($newfilename_full, 'rb'), @filesize($newfilename_full));
										$filedata_mini = @fread(@fopen($newfilename_mini, 'rb'), @filesize($newfilename_mini));
										$filedata_search = @fread(@fopen($newfilename_search, 'rb'), @filesize($newfilename_search));
										$filedata_gallery = @fread(@fopen($newfilename_gallery, 'rb'), @filesize($newfilename_gallery));
										$filedata_snapshot = @fread(@fopen($newfilename_snapshot, 'rb'), @filesize($newfilename_snapshot));
										// clean up processed and resized images
										@unlink($newfilename);
										@unlink($newfilename_original);
										@unlink($newfilename_full);
										@unlink($newfilename_mini);
										@unlink($newfilename_search); 
										@unlink($newfilename_gallery);
										@unlink($newfilename_snapshot);
										// save new attachment details for this picture
										$ilance->db->query("
											UPDATE " . DB_PREFIX . "attachment
											SET filetype_original = '" . $ilance->db->escape_string($filetype) . "',
											filedata = '" . $ilance->db->escape_string($filedata) . "',
											filedata_original = '" . $ilance->db->escape_string($filedata_original) . "',
											filedata_full = '" . $ilance->db->escape_string($filedata_full) . "',
											filedata_mini = '" . $ilance->db->escape_string($filedata_mini) . "',
											filedata_search = '" . $ilance->db->escape_string($filedata_search) . "',
											filedata_gallery = '" . $ilance->db->escape_string($filedata_gallery) . "',
											filedata_snapshot = '" . $ilance->db->escape_string($filedata_snapshot) . "',
											width = '" . intval($width_default) . "',
											width_original = '" . intval($width_original) . "',
											width_full = '" . intval($width_full) . "',
											width_mini = '" . intval($width_mini) . "',
											width_search = '" . intval($width_search) . "',
											width_gallery = '" . intval($width_gallery) . "',
											width_snapshot = '" . intval($width_snapshot) . "',
											height = '" . intval($height_default) . "',
											height_original = '" . intval($height_original) . "',
											height_full = '" . intval($height_full) . "',
											height_mini = '" . intval($height_mini) . "',
											height_search = '" . intval($height_search) . "',
											height_gallery = '" . intval($height_gallery) . "',
											height_snapshot = '" . intval($height_snapshot) . "',
											filesize_original = '" . $ilance->db->escape_string($upload_file_size_original) . "',
											filesize_full = '" . $ilance->db->escape_string($upload_file_size_full) . "',
											filesize_mini = '" . $ilance->db->escape_string($upload_file_size_mini) . "',
											filesize_search = '" . $ilance->db->escape_string($upload_file_size_search) . "',
											filesize_gallery = '" . $ilance->db->escape_string($upload_file_size_gallery) . "',
											filesize_snapshot = '" . $ilance->db->escape_string($upload_file_size_snapshot) . "',
											exifdata = '" . $ilance->db->escape_string($exif) . "',
											watermarked = '" . intval($iswatermarked) . "'
											WHERE attachid = '" . $res['attachid'] . "'
											LIMIT 1
										", 0, null, __FILE__, __LINE__);
										unset($filedata, $filedata_original, $filedata_full, $filedata_mini, $filedata_search, $filedata_gallery, $filedata_snapshot);
									}
								}
							}	
						}
					}
				}
			}
		}
		else
		{
			if ($attachid > 0)
			{
				$extraquery = "WHERE attachid = '" . intval($attachid) . "'";
			}
			$sql = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "attachment
				$extraquery
				ORDER BY attachid ASC
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
                                ($apihook = $ilance->api('process_picture_rebuilder_start_filesystem')) ? eval($apihook) : false;
				
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					$rawdata = '';
					if (in_array($res['filetype'], $ilance->attachment->mimetypes) AND in_array($res['attachtype'], $originalattachtypes))
					{
						$rawdata = $ilance->attachment_tools->fetch_attachment_rawdata($res, true); // also populates $show['fetchoriginal'] if applicable
						$attachtype = $res['attachtype'];
						$filehash = $res['filehash'];
						$filetype = '';
						if ($attachtype == 'profile')
						{
							$fullpath = DIR_PROFILE_ATTACHMENTS;
							$maxwidth_default = $ilconfig['attachmentlimit_profilemaxwidth'];
							$maxheight_default = $ilconfig['attachmentlimit_profilemaxheight'];
							$maxwidth_full = $ilconfig['attachmentlimit_profilemaxwidth'];
							$maxheight_full = $ilconfig['attachmentlimit_profilemaxheight'];
							$maxwidth_mini = $ilconfig['attachmentlimit_productphotothumbwidth'];
							$maxheight_mini = $ilconfig['attachmentlimit_productphotothumbheight'];
							$maxwidth_search = $ilconfig['attachmentlimit_profilemaxwidth'];
							$maxheight_search = $ilconfig['attachmentlimit_profilemaxheight'];
							$maxwidth_gallery = $ilconfig['attachmentlimit_searchresultsgallerymaxwidth'];
							$maxheight_gallery = $ilconfig['attachmentlimit_searchresultsgallerymaxheight'];
							$maxwidth_snapshot = $ilconfig['attachmentlimit_searchresultssnapshotmaxwidth'];
							$maxheight_snapshot = $ilconfig['attachmentlimit_searchresultssnapshotmaxheight'];
						}
						else if ($attachtype == 'portfolio')
						{
							$fullpath = DIR_PORTFOLIO_ATTACHMENTS;
							$maxwidth_default = $ilconfig['attachmentlimit_portfoliothumbwidth'];
							$maxheight_default = $ilconfig['attachmentlimit_portfoliothumbheight'];
							$maxwidth_full = $ilconfig['attachmentlimit_productphotowidth'];
							$maxheight_full = $ilconfig['attachmentlimit_productphotoheight'];
							$maxwidth_mini = $ilconfig['attachmentlimit_productphotothumbwidth'];
							$maxheight_mini = $ilconfig['attachmentlimit_productphotothumbheight'];
							$maxwidth_search = $ilconfig['attachmentlimit_searchresultsmaxwidth'];
							$maxheight_search = $ilconfig['attachmentlimit_searchresultsmaxheight'];
							$maxwidth_gallery = $ilconfig['attachmentlimit_searchresultsgallerymaxwidth'];
							$maxheight_gallery = $ilconfig['attachmentlimit_searchresultsgallerymaxheight'];
							$maxwidth_snapshot = $ilconfig['attachmentlimit_searchresultssnapshotmaxwidth'];
							$maxheight_snapshot = $ilconfig['attachmentlimit_searchresultssnapshotmaxheight'];
						}
						else if ($attachtype == 'itemphoto')
						{
							$fullpath = DIR_AUCTION_ATTACHMENTS;
							$maxwidth_default = $ilconfig['attachmentlimit_productphotomaxwidth'];
							$maxheight_default = $ilconfig['attachmentlimit_productphotomaxheight'];
							$maxwidth_full = $ilconfig['attachmentlimit_productphotowidth'];
							$maxheight_full = $ilconfig['attachmentlimit_productphotoheight'];
							$maxwidth_mini = $ilconfig['attachmentlimit_productphotothumbwidth'];
							$maxheight_mini = $ilconfig['attachmentlimit_productphotothumbheight'];
							$maxwidth_search = $ilconfig['attachmentlimit_searchresultsmaxwidth'];
							$maxheight_search = $ilconfig['attachmentlimit_searchresultsmaxheight'];
							$maxwidth_gallery = $ilconfig['attachmentlimit_searchresultsgallerymaxwidth'];
							$maxheight_gallery = $ilconfig['attachmentlimit_searchresultsgallerymaxheight'];
							$maxwidth_snapshot = $ilconfig['attachmentlimit_searchresultssnapshotmaxwidth'];
							$maxheight_snapshot = $ilconfig['attachmentlimit_searchresultssnapshotmaxheight'];
						}
						else if ($attachtype == 'slideshow')
						{
							$fullpath = DIR_AUCTION_ATTACHMENTS;
							$maxwidth_default = $ilconfig['attachmentlimit_productphotomaxwidth'];
							$maxheight_default = $ilconfig['attachmentlimit_productphotomaxheight'];
							$maxwidth_full = $ilconfig['attachmentlimit_productphotowidth'];
							$maxheight_full = $ilconfig['attachmentlimit_productphotoheight'];
							$maxwidth_mini = $ilconfig['attachmentlimit_productphotothumbwidth'];
							$maxheight_mini = $ilconfig['attachmentlimit_productphotothumbheight'];
							$maxwidth_search = $ilconfig['attachmentlimit_searchresultsmaxwidth'];
							$maxheight_search = $ilconfig['attachmentlimit_searchresultsmaxheight'];
							$maxwidth_gallery = $ilconfig['attachmentlimit_searchresultsgallerymaxwidth'];
							$maxheight_gallery = $ilconfig['attachmentlimit_searchresultsgallerymaxheight'];
							$maxwidth_snapshot = $ilconfig['attachmentlimit_searchresultssnapshotmaxwidth'];
							$maxheight_snapshot = $ilconfig['attachmentlimit_searchresultssnapshotmaxheight'];
						}
						else
						{
							($apihook = $ilance->api('process_picture_rebuilder_filesystem_loop_else')) ? eval($apihook) : false;
						}
						if (!empty($rawdata))
						{
							if (file_exists($fullpath . 'original/' . $filehash . '.attach'))
							{
								@unlink($fullpath . 'original/' . $filehash . '.attach');
							}
							if ($fp = fopen($fullpath . 'original/' . $filehash . '.attach', 'wb'))
							{
								fwrite($fp, $rawdata);
								fclose($fp);
								if (file_exists($fullpath . $filehash . '.attach'))
								{
									@unlink($fullpath . $filehash . '.attach');
								}
								if ($fp2 = fopen($fullpath . $filehash . '.attach', 'wb'))
								{
									fwrite($fp2, $rawdata);
									fclose($fp2);
								}
								if (file_exists($fullpath . 'resized/full/' . $filehash . '.attach'))
								{
									@unlink($fullpath . 'resized/full/' . $filehash . '.attach');
								}
								if ($fp3 = fopen($fullpath . 'resized/full/' . $filehash . '.attach', 'wb'))
								{
									fwrite($fp3, $rawdata);
									fclose($fp3);
								}
								if (file_exists($fullpath . 'resized/mini/' . $filehash . '.attach'))
								{
									@unlink($fullpath . 'resized/mini/' . $filehash . '.attach');
								}
								if ($fp4 = fopen($fullpath . 'resized/mini/' . $filehash . '.attach', 'wb'))
								{
									fwrite($fp4, $rawdata);
									fclose($fp4);
								}
								if (file_exists($fullpath . 'resized/search/' . $filehash . '.attach'))
								{
									@unlink($fullpath . 'resized/search/' . $filehash . '.attach');
								}
								if ($fp5 = fopen($fullpath . 'resized/search/' . $filehash . '.attach', 'wb'))
								{
									fwrite($fp5, $rawdata);
									fclose($fp5);
								}
								if (file_exists($fullpath . 'resized/gallery/' . $filehash . '.attach'))
								{
									@unlink($fullpath . 'resized/gallery/' . $filehash . '.attach');
								}
								if ($fp6 = fopen($fullpath . 'resized/gallery/' . $filehash . '.attach', 'wb'))
								{
									fwrite($fp6, $rawdata);
									fclose($fp6);
								}
								if (file_exists($fullpath . 'resized/snapshot/' . $filehash . '.attach'))
								{
									@unlink($fullpath . 'resized/snapshot/' . $filehash . '.attach');
								}
								if ($fp7 = fopen($fullpath . 'resized/snapshot/' . $filehash . '.attach', 'wb'))
								{
									fwrite($fp7, $rawdata);
									fclose($fp7);
								}
								unset($rawdata);
								if ($idata = getimagesize($fullpath . 'original/' . $filehash . '.attach'))
								{
									if (!empty($idata['mime']))
									{
										$filetype = $idata['mime']; // .png
									}
									$data = array(
										'fullpath' => $fullpath . $filehash . '.attach', // can be removed later..
										'fullpath_original' => $fullpath . 'original/' . $filehash . '.attach',
										'fullpath_full' => $fullpath . 'resized/full/' . $filehash . '.attach',
										'fullpath_mini' => $fullpath . 'resized/mini/' . $filehash . '.attach',
										'fullpath_search' => $fullpath . 'resized/search/' . $filehash . '.attach',
										'fullpath_gallery' => $fullpath . 'resized/gallery/' . $filehash . '.attach',
										'fullpath_snapshot' => $fullpath . 'resized/snapshot/' . $filehash . '.attach',
										'filename' => $res['filename'],
										'filehash' => $filehash,
										'filetype' => $filetype,
										'width' => $idata[0],
										'height' => $idata[1]
									);
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
									$filedata = $filedata_original = $filedata_full = $filedata_mini = $filedata_search = $filedata_gallery = $filedata_snapshot = $exif = '';
									$iswatermarked = 0;
									if (!empty($newfilename) AND !empty($filename))
									{
										//$extension = mb_strtolower(mb_strrchr($filename, '.')); //.jpg
										$extension = str_replace('/', '.', mb_strtolower(mb_strrchr($filetype, '/'))); // .jpeg
										$allowedextensions = array('.jpg', '.jpeg', '.gif', '.png', '.bmp');
										if (in_array($extension, $allowedextensions))
										{
											$upload_file_size_original = $upload_file_size = $upload_file_size_full = $upload_file_size_mini = $upload_file_size_search = $upload_file_size_gallery = $upload_file_size_snapshot = @filesize($newfilename_original);
											// #### fetch exif information (extended image support)
											if (function_exists('exif_read_data') AND ((isset($show['fetchoriginal']) AND $show['fetchoriginal']) OR (isset($show['fetchoriginal']) AND $show['fetchoriginal'] == false AND empty($res['exifdata']))))
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
												if ($fileinfo[0] > $maxwidth_default OR $fileinfo[1] > $maxheight_default)
												{
													$ilance->attachment->picture_resizer($newfilename, $maxwidth_default, $maxheight_default, $extension, $fileinfo[0], $fileinfo[1], '');
													// apply site watermark if it hasn't been already applied
													if (((isset($show['fetchoriginal']) AND $show['fetchoriginal']) OR (isset($show['fetchoriginal']) AND $show['fetchoriginal'] == false AND $res['watermarked'] <= 0)))
													{
														//$ilance->attachment->watermark($attachtype, $newfilename, $extension, '');
														//$iswatermarked = $ilance->attachment->watermarked;
													}
													if ($ilance->attachment->pictureresized)
													{
														$width_default = $ilance->attachment->width; // 100 
														$height_default = $ilance->attachment->height; // 80
														$upload_file_size = @filesize($newfilename);
													}
												}
												if ($fileinfo[0] > $maxwidth_full OR $fileinfo[1] > $maxheight_full)
												{
													$ilance->attachment->picture_resizer($newfilename_full, $maxwidth_full, $maxheight_full, $extension, $fileinfo[0], $fileinfo[1], '');
													// apply site watermark if it hasn't been already applied
													if (((isset($show['fetchoriginal']) AND $show['fetchoriginal']) OR (isset($show['fetchoriginal']) AND $show['fetchoriginal'] == false AND $res['watermarked'] <= 0)))
													{
														$ilance->attachment->watermark($attachtype, $newfilename_full, $extension, '');
														$iswatermarked = $ilance->attachment->watermarked;
													}
													if ($ilance->attachment->pictureresized)
													{
														$width_full = $ilance->attachment->width;
														$height_full = $ilance->attachment->height;
														$upload_file_size_full = @filesize($newfilename_full);
													}
												}
												if ($fileinfo[0] > $maxwidth_mini OR $fileinfo[1] > $maxheight_mini)
												{
													$ilance->attachment->picture_resizer($newfilename_mini, $maxwidth_mini, $maxheight_mini, $extension, $fileinfo[0], $fileinfo[1], '');
													// apply site watermark if it hasn't been already applied
													if (((isset($show['fetchoriginal']) AND $show['fetchoriginal']) OR (isset($show['fetchoriginal']) AND $show['fetchoriginal'] == false AND $res['watermarked'] <= 0)))
													{
														//$ilance->attachment->watermark($attachtype, $newfilename_mini, $extension, '');
														//$iswatermarked = $ilance->attachment->watermarked;
													}
													if ($ilance->attachment->pictureresized)
													{
														$width_mini = $ilance->attachment->width;
														$height_mini = $ilance->attachment->height;
														$upload_file_size_mini = @filesize($newfilename_mini);
													}
												}
												if ($fileinfo[0] > $maxwidth_search OR $fileinfo[1] > $maxheight_search)
												{
													$ilance->attachment->picture_resizer($newfilename_search, $maxwidth_search, $maxheight_search, $extension, $fileinfo[0], $fileinfo[1], '');
													if (((isset($show['fetchoriginal']) AND $show['fetchoriginal']) OR (isset($show['fetchoriginal']) AND $show['fetchoriginal'] == false AND $res['watermarked'] <= 0)))
													{
														//$ilance->attachment->watermark($attachtype, $newfilename_search, $extension, '');
														//$iswatermarked = $ilance->attachment->watermarked;
													}
													if ($ilance->attachment->pictureresized)
													{
														$width_search = $ilance->attachment->width;
														$height_search = $ilance->attachment->height;
														$upload_file_size_search = @filesize($newfilename_search);
													}
												}
												if ($fileinfo[0] > $maxwidth_gallery OR $fileinfo[1] > $maxheight_gallery)
												{
													$ilance->attachment->picture_resizer($newfilename_gallery, $maxwidth_gallery, $maxheight_gallery, $extension, $fileinfo[0], $fileinfo[1], '');
													if (((isset($show['fetchoriginal']) AND $show['fetchoriginal']) OR (isset($show['fetchoriginal']) AND $show['fetchoriginal'] == false AND $res['watermarked'] <= 0)))
													{
														//$ilance->attachment->watermark($attachtype, $newfilename_gallery, $extension, '');
														//$iswatermarked = $ilance->attachment->watermarked;
													}
													if ($ilance->attachment->pictureresized)
													{
														$width_gallery = $ilance->attachment->width;
														$height_gallery = $ilance->attachment->height;
														$upload_file_size_gallery = @filesize($newfilename_gallery);
													}
												}
												if ($fileinfo[0] > $maxwidth_snapshot OR $fileinfo[1] > $maxheight_snapshot)
												{
													$ilance->attachment->picture_resizer($newfilename_snapshot, $maxwidth_snapshot, $maxheight_snapshot, $extension, $fileinfo[0], $fileinfo[1], '');
													if (((isset($show['fetchoriginal']) AND $show['fetchoriginal']) OR (isset($show['fetchoriginal']) AND $show['fetchoriginal'] == false AND $res['watermarked'] <= 0)))
													{
														//$ilance->attachment->watermark($attachtype, $newfilename_snapshot, $extension, '');
														//$iswatermarked = $ilance->attachment->watermarked;
													}
													if ($ilance->attachment->pictureresized)
													{
														$width_snapshot = $ilance->attachment->width;
														$height_snapshot = $ilance->attachment->height;
														$upload_file_size_snapshot = @filesize($newfilename_snapshot);
													}
												}
											}
											$ilance->db->query("
												UPDATE " . DB_PREFIX . "attachment
												SET filetype_original = '" . $ilance->db->escape_string($filetype) . "',
												filedata = '',
												filedata_original = '',
												filedata_full = '',
												filedata_mini = '',
												filedata_search = '',
												filedata_gallery = '',
												filedata_snapshot = '',
												width = '" . intval($width_default) . "',
												width_original = '" . intval($width_original) . "',
												width_full = '" . intval($width_full) . "',
												width_mini = '" . intval($width_mini) . "',
												width_search = '" . intval($width_search) . "',
												width_gallery = '" . intval($width_gallery) . "',
												width_snapshot = '" . intval($width_snapshot) . "',
												height = '" . intval($height_default) . "',
												height_original = '" . intval($height_original) . "',
												height_full = '" . intval($height_full) . "',
												height_mini = '" . intval($height_mini) . "',
												height_search = '" . intval($height_search) . "',
												height_gallery = '" . intval($height_gallery) . "',
												height_snapshot = '" . intval($height_snapshot) . "',
												filesize_original = '" . $ilance->db->escape_string($upload_file_size_original) . "',
												filesize_full = '" . $ilance->db->escape_string($upload_file_size_full) . "',
												filesize_mini = '" . $ilance->db->escape_string($upload_file_size_mini) . "',
												filesize_search = '" . $ilance->db->escape_string($upload_file_size_search) . "',
												filesize_gallery = '" . $ilance->db->escape_string($upload_file_size_gallery) . "',
												filesize_snapshot = '" . $ilance->db->escape_string($upload_file_size_snapshot) . "',
												exifdata = '" . $ilance->db->escape_string($exif) . "',
												watermarked = '" . intval($iswatermarked) . "'
												WHERE attachid = '" . $res['attachid'] . "'
												LIMIT 1
											", 0, null, __FILE__, __LINE__);
										}
									}
								}	
							}
						}
					}
				}
			}
		}
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>