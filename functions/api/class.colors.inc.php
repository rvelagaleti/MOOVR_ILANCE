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
* Colors class to perform the majority of color functionality within ILance.
*
* @package      iLance\Colors
* @version      4.0.0.8059
* @author       ILance
*/
class colors
{
	/**
        * Function to handle the building of colors from the attachment table
        *
        * @return      nothing
        */
	function build_attachment_colors()
	{
		global $ilance, $ilconfig, $show;
		$attachtypeextra = $cronlog = '';
		$colorlimit = 2; // todo: allow admin to define how many colors to extract! we set only 2 to be nice to db but less colors means less accuracy!
		
		($apihook = $ilance->api('build_attachment_colors_start')) ? eval($apihook) : false;
		
		$counter = 0;
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "attachment
			WHERE visible = '1'
				AND color = '0'
				AND (attachtype = 'itemphoto'$attachtypeextra)
				AND filehash != ''
			ORDER BY attachid ASC
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$filedata = $ilance->attachment_tools->fetch_attachment_rawdata($res, true);
				if (!empty($filedata))
				{
					$itemid = 0;
					if ($res['project_id'] > 0 AND $res['attachtype'] == 'itemphoto')
					{
						$itemid = $res['project_id'];
					}
					else
					{
						($apihook = $ilance->api('build_attachment_colors_else')) ? eval($apihook) : false;
					}
					$filename = DIR_TMP . $res['filehash'] . '.attach';
					if (file_exists($filename))
					{
						@unlink($filename);
					}
					if ($temphandle = @fopen($filename, 'wb'))
					{
						$i = 1;
						@fwrite($temphandle, $filedata);
						@fclose($temphandle);
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "attachment
							SET color = '1'
							WHERE attachid = '" . $res['attachid'] . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						$colors = $this->fetch_colors($filename);
						foreach ($colors AS $color => $count)
						{
							if ($i <= $colorlimit)
							{
								$array = $this->fetch_relative_hue_name($color);
								$ilance->db->query("
									INSERT INTO " . DB_PREFIX . "attachment_color
									(colorid, attachid, project_id, color, count, relativecolor, relativetitle, relativefont)
									VALUES(
									NULL,
									'" . $res['attachid'] . "',
									'" . $itemid . "',
									'#" . $ilance->db->escape_string(strtoupper($color)) . "',
									'" . intval($count) . "',
									'" . $ilance->db->escape_string($array[0]) . "',
									'" . $ilance->db->escape_string($array[1]) . "',
									'" . $ilance->db->escape_string($array[2]) . "')
								", 0, null, __FILE__, __LINE__);
							}
							$i++;
						}
						@unlink($filename);
						$counter++;
					}
				}
			}
		}
		return "$counter item pictures had the top $colorlimit major colors extracted and stored for searching by color";
	}
	
	/**
        * Function to handle the removal of color extracts from item photos and slideshow images from deleted auctions in the database.  This function runs daily within cron.dailyrfp.php
        *
        * @return      string         Report on the number of images removed from unlinked projects.
        */
	function remove_colors_from_deleted_listings()
	{
		global $ilance;
		$cronlog = '';
		$i = 0;
		$sql = $ilance->db->query("
                        SELECT project_id
                        FROM " . DB_PREFIX . "attachment_color
			GROUP BY project_id
			ORDER BY colorid ASC
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$sql2 = $ilance->db->query("
					SELECT project_id
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . $res['project_id'] . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql2) == 0)
				{
					$ilance->db->query("
						DELETE FROM " . DB_PREFIX . "attachment_color
						WHERE project_id = '" . $res['project_id'] . "'
					", 0, null, __FILE__, __LINE__);
					$i++;	
				}
			}
		}
		$cronlog .= 'Removed ' . $i . ' color extracts from deleted item listings that no longer exist, ';
		return $cronlog;
	}
	
	/**
        * Function to handle the removal of color extracts from item photos and slideshow images from deleted attachments in the database.  This function runs daily within cron.dailyrfp.php
        *
        * @return      string         Report on the number of images removed from unlinked projects.
        */
	function remove_colors_from_deleted_attachments()
	{
		global $ilance, $ilconfig;
		$cronlog = '';
		$i = 0;
		$sql = $ilance->db->query("
                        SELECT attachid
                        FROM " . DB_PREFIX . "attachment_color
			GROUP BY attachid
			ORDER BY colorid ASC
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$sql2 = $ilance->db->query("
					SELECT attachid
					FROM " . DB_PREFIX . "attachment
					WHERE attachid = '" . $res['attachid'] . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql2) == 0)
				{
					$ilance->db->query("
						DELETE FROM " . DB_PREFIX . "attachment_color
						WHERE attachid = '" . $res['attachid'] . "'
					", 0, null, __FILE__, __LINE__);
					$i++;	
				}
			}
		}
		$cronlog .= 'Removed ' . $i . ' color extracts from deleted attachments that no longer exist, ';
		return $cronlog;
	}
	
	/**
	* Returns the colors of the image in an array, ordered in descending order, where the keys are the colors and values are the pixel count of the color.
	* This function currently supports .gif, .png and .jpg image formats only.
	*
	* @param        string         full path to image
	* 
	* @return       array          Returns array details of the color information for the image
	*/
	function fetch_colors($filename = '')
	{
		if (!empty($filename))
		{
			// resize to obtain most significant colors
			$preview_width = 150;
			$preview_height = 150;
			$size = GetImageSize($filename);
			$scale = 1;
			if ($size[0] > 0)
			{
				$scale = min($preview_width / $size[0], $preview_height / $size[1]);
			}
			if ($scale < 1)
			{
				$width = floor($scale*$size[0]);
				$height = floor($scale*$size[1]);
			}
			else
			{
				$width = $size[0];
				$height = $size[1];
			}
			$image_resized = imagecreatetruecolor($width, $height);
			if ($size[2] == 1)
			{
				$image_orig = imagecreatefromgif($filename);
			}
			if ($size[2] == 2)
			{
				$image_orig = imagecreatefromjpeg($filename);
			}
			if ($size[2] == 3)
			{
				$image_orig = imagecreatefrompng($filename);
			}
			// we need the nearest neihbor resizing because it doesn't alter the colors
			imagecopyresampled($image_resized, $image_orig, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
			$im = $image_resized;
			$imgWidth = imagesx($im);
			$imgHeight = imagesy($im);
			for ($y = 0; $y < $imgHeight; $y++)
			{
				for ($x = 0; $x < $imgWidth; $x++)
				{
					$index = imagecolorat($im, $x, $y);
					$Colors = imagecolorsforindex($im, $index);
					// round off the colors to reduce the number of colors to prevent duplicates
					$Colors['red'] = intval((($Colors['red']) + 15) / 32) * 32;
					$Colors['green'] = intval((($Colors['green']) + 15) / 32) * 32;
					$Colors['blue'] = intval((($Colors['blue']) + 15) / 32) * 32;
					if ($Colors['red'] >= 256)
					{
						$Colors['red'] = 240;
					}
					if ($Colors['green'] >= 256)
					{
						$Colors['green'] = 240;
					}
					if ($Colors['blue'] >= 256)
					{
						$Colors['blue'] = 240;
					}
					$hexarray[] = substr("0" . dechex($Colors['red']), -2) . substr("0" . dechex($Colors['green']), -2) . substr("0" . dechex($Colors['blue']), -2);
				}
			}
			$hexarray = array_count_values($hexarray);
			natsort($hexarray);
			$hexarray = array_reverse($hexarray, true);
			return $hexarray;
		}
	}
	
	/**
        * Function modified from http://ikimashou.net/2009/03/20/projects-color-to-name/
        *
        * @param        string         color
        *
        * @return       array          Returns the relative hue name based on selected color         
        */
	function fetch_relative_hue_name($color = '') 
	{
		if (!isset($names))
		{
			global $names;
			$names = file(DIR_API . 'class.colors.inc.csv');
			array_walk($names, array('colors', 'splitter'));
		}
		$color = strtoupper($color);
		$r = hexdec(substr($color, 0, 2));
		$g = hexdec(substr($color, 2, 2));
		$b = hexdec(substr($color, 4, 2));
		$hsl = $this->hsl($color);
		$h = $hsl[0]; 
		$s = $hsl[1]; 
		$l = $hsl[2];
		$ndf1 = 0; 
		$ndf2 = 0; 
		$ndf = 0;
		$cl = -1;
		$df = -1;
		$count = count($names);
		for ($i = 0; $i < $count; $i++)
		{
			if ($color == $names[$i][0])
			{
				return array("#" . $names[$i][0], $names[$i][1], $names[$i][2]);
			}
			$name_r = hexdec(substr($names[$i][0], 0, 2));
			$name_g = hexdec(substr($names[$i][0], 2, 2));
			$name_b = hexdec(substr($names[$i][0], 4, 2));
			$name_hsl = $this->hsl($names[$i][0]);
			$name_h = $name_hsl[0]; 
			$name_s = $name_hsl[1]; 
			$name_l = $name_hsl[2];
			$ndf1 = pow($r - $name_r, 2) + pow($g - $name_g, 2) + pow($b - $name_b, 2);
			$ndf2 = abs(pow($h - $name_h, 2)) + pow($s - $name_s, 2) + abs(pow($l - $name_l, 2));
			$ndf = $ndf1 + $ndf2 * 2;
			if ($df < 0 || $df > $ndf)
			{
				$df = $ndf;
				$cl = $i;
			}
		}
		return ($cl < 0 ? array("#000000", "Invalid Color: " . $color . '|' . $cl . '|' . $df, "#000000") : array("#" . $names[$cl][0], $names[$cl][1], $names[$cl][2]));
	}
	
	/**
        * Function to extract the hsl hues based on a supplied color code
        *
        * @param        string         color
        * 
        * @return       array          Returns the hsl array based on selected color       
        */
	function hsl($color) 
	{
		$r = hexdec(substr($color, 0, 2)) / 255;
		$g = hexdec(substr($color, 2, 2)) / 255;
		$b = hexdec(substr($color, 4, 2)) / 255;
		$min = min($r, min($g, $b));
		$max = max($r, max($g, $b));
		$delta = $max - $min;
		$l = ($min + $max) / 2;
		$s = 0;
		if ($l > 0 && $l < 1)
		{
			$s = $delta / ($l < 0.5 ? (2 * $l) : (2 - 2 * $l));
		}
		$h = 0;
		if ($delta > 0)
		{
			if ($max == $r && $max != $g) $h += ($g - $b) / $delta;
			if ($max == $g && $max != $b) $h += (2 + ($b - $r) / $delta);
			if ($max == $b && $max != $r) $h += (4 + ($r - $g) / $delta);
			$h = $h/6;
		}
		return array(($h * 255), ($s * 255), ($l * 255));
	}
	/**
        * Function to extract and split an array of colors
        *
	* @param        array          color info
        * @param        string         color key
        * 
        * @return       array          Returns an exploded array
        */
	function splitter(&$info, $key)
	{
		$info = explode(',', $info);
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>