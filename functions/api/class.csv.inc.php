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
* CSV class to perform the majority of importing and exporting functions within ILance.
*
* @package      iLance\CSV
* @version      4.0.0.8059
* @author       ILance
*/

class csv
{
        /*
         Constructor
        */
        function csv()
        {
        }
        
        function csv_to_db($file = '', $id = 0, $bulk_id = 0, $containsheader = false, $admincpimport = false)
        {
        	global $ilance, $ilconfig;
		$currencyfield = ($ilconfig['globalserverlocale_currencyselector']) ? "currency, " : '';
		if ($admincpimport)
		{
			$fields = 'project_title, description, keywords, attributes';
			$sql = $ilance->db->query('
				LOAD DATA LOCAL INFILE "' . $file . '" INTO TABLE ' . DB_PREFIX . 'bulk_tmp
				CHARACTER SET UTF8
				FIELDS TERMINATED BY "' . $ilconfig['globalfilters_bulkuploadcolsep'] . '" ENCLOSED BY "' . $ilance->db->escape_string($ilconfig['globalfilters_bulkuploadcolencap']) . '"
				LINES TERMINATED BY "' . LINEBREAK . '"
				' . (($containsheader) ? 'IGNORE 1 LINES' : '') . '
				(' . $fields . ')
				SET user_id = "' . $id . '", bulk_id = "' . $bulk_id . '", dateupload = "' . DATETODAY . '"
			', 0, null, __FILE__, __LINE__);
		}
		else
		{
			$fields = 'project_title, description, startprice, buynow_price, reserve_price, buynow_qty, buynow_qty_lot, project_details, filtered_auctiontype, cid, sample, ' . $currencyfield . 'city, state, zipcode, country, attributes, sku, upc, partnumber, modelnumber, ean';
			$sql = $ilance->db->query('
				LOAD DATA LOCAL INFILE "' . $file . '" INTO TABLE ' . DB_PREFIX . 'bulk_tmp
				CHARACTER SET UTF8
				FIELDS TERMINATED BY "' . $ilconfig['globalfilters_bulkuploadcolsep'] . '" ENCLOSED BY "' . $ilance->db->escape_string($ilconfig['globalfilters_bulkuploadcolencap']) . '"
				LINES TERMINATED BY "' . LINEBREAK . '"
				' . (($containsheader) ? 'IGNORE 1 LINES' : '') . '
				(' . $fields . ')
				SET user_id = "' . intval($id) . '", bulk_id = "' . intval($bulk_id) . '", dateupload = "' . DATETODAY . '"
			', 0, null, __FILE__, __LINE__);
		}
        	return $sql;
        }
	
	function category_csv_to_db($file = '', $containsheader = false, $deletecurrent = false)
        {
        	global $ilance, $ilconfig;
		$table = 'categories_test';
		$array = str_getcsv($file, LINEBREAK);
		foreach ($array AS $row)
		{
			$rows[] = str_getcsv($row, ',', '"');
		}
		unset($array, $row);
		
		if (isset($rows) AND is_array($rows))
		{
			/*[12140] => Array
			(
			    [0] => Entertainment Memorabilia
			    [1] => Music Memorabilia
			    [2] => Rock & Pop
			    [3] => Artists C
			    [4] => Cher
			    [5] => Apparel
			    [6] => 
			    [7] => 104708
			    [8] => 104707
			)*/
			print_r($rows);
			exit;
			foreach ($rows AS $key => $eachrow)
			{
				$cid = $eachrow[7];
				$pid = $eachrow[8];
				$customfields1 = $customfields2 = $customfields3 = $customfieldvalues1 = $customfieldvalues2 = $customfieldvalues3 = $insertiongroup = $finalvaluegroup = $incrementgroup = $bidamounttypes = $bidtypes = $catimage = $keywords = '';
				$canpost = $useproxybid = $usereserveprice = $useantisnipe = $visible = 1;
				$newsletter = $sort = 0;
				$cattype = 'product';
				if (!empty($eachrow[0]) AND empty($eachrow[1]))
				{
					// level 1 title
					$pid = 0;
					$title = $eachrow[0];
					$description = $eachrow[0];
					$keywords = $eachrow[0];
				}
				else if (!empty($eachrow[0]) AND !empty($eachrow[1]) AND empty($eachrow[2]))
				{
					// level 2 title
					$title = $eachrow[1];
					$description = $eachrow[1];
					$keywords = $eachrow[1] . ', ' . $eachrow[0];
				}
				else if (!empty($eachrow[0]) AND !empty($eachrow[1]) AND !empty($eachrow[2]) AND empty($eachrow[3]))
				{
					// level 3 title
					$title = $eachrow[2];
					$description = $eachrow[2];
					$keywords = $eachrow[2] . ', ' . $eachrow[1] . ', ' . $eachrow[0];
				}
				else if (!empty($eachrow[0]) AND !empty($eachrow[1]) AND !empty($eachrow[2]) AND !empty($eachrow[3]) AND empty($eachrow[4]))
				{
					// level 4 title
					$title = $eachrow[3];
					$description = $eachrow[3];
					$keywords = $eachrow[3] . ', ' . $eachrow[2] . ', ' . $eachrow[1] . ', ' . $eachrow[0];
				}
				else if (!empty($eachrow[0]) AND !empty($eachrow[1]) AND !empty($eachrow[2]) AND !empty($eachrow[3]) AND !empty($eachrow[4]) AND empty($eachrow[5]))
				{
					// level 5 title
					$title = $eachrow[4];
					$description = $eachrow[4];
					$keywords = $eachrow[4] . ', ' . $eachrow[3] . ', ' . $eachrow[2] . ', ' . $eachrow[1] . ', ' . $eachrow[0];
				}
				else if (!empty($eachrow[0]) AND !empty($eachrow[1]) AND !empty($eachrow[2]) AND !empty($eachrow[3]) AND !empty($eachrow[4]) AND !empty($eachrow[5]))
				{
					// level 6 title
					$title = $eachrow[5];
					$description = $eachrow[5];
					$keywords = $eachrow[5] . ', ' . $eachrow[4] . ', ' . $eachrow[3] . ', ' . $eachrow[2] . ', ' . $eachrow[1] . ', ' . $eachrow[0];
				}
				if ($deletecurrent)
				{
					// make backup of current categories
					$timestamp = TIMESTAMPNOW;
					$ilance->db->query("CREATE TABLE " . DB_PREFIX . $table . "_$timestamp SELECT * FROM " . DB_PREFIX . $table, 0, null, __FILE__, __LINE__);
					$ilance->db->query("TRUNCATE TABLE " . DB_PREFIX . $table, 0, null, __FILE__, __LINE__);
				}
				$titlefields = 'title_eng, ';
				$titlevalues = "'" . $ilance->db->escape_string($title) . "',";
				$descriptionfields = 'description_eng, ';
				$descriptionvalues = "'" . $ilance->db->escape_string($title) . "',";
				
				// #### get the parent record ##################
				$sql = $ilance->db->query("
					SELECT rgt
					FROM " . DB_PREFIX . $table . "
					WHERE cid = '" . intval($cid) . "'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$parent = $ilance->db->fetch_array($sql, DB_ASSOC);
					if ($parent['rgt'] > 0)
					{
						// #### prepare the table for the insert
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "categories
							SET rgt = rgt + 2 
							WHERE rgt > " . intval($parent['rgt']) . "
								AND cattype = 'product'
						", 0, null, __FILE__, __LINE__);
						
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "categories
							SET lft = lft + 2
							WHERE lft > " . intval($parent['rgt']) . "
								AND cattype = 'product'
						", 0, null, __FILE__, __LINE__);
					}
				}
				else
				{
					$parent['rgt'] = 0; 
				}
				$ilance->db->query("ALTER TABLE " . DB_PREFIX . $table . " DROP `sets`", 0, null, __FILE__, __LINE__);
				// #### insert the record ######################
				$ilance->db->query("
					INSERT INTO " . DB_PREFIX . $table . "
					(parentid, $titlefields $descriptionfields $customfields1 $customfields2 $customfields3 canpost, xml, newsletter, insertiongroup, finalvaluegroup, incrementgroup, cattype, bidamounttypes, useproxybid, usereserveprice, useantisnipe, catimage, keywords, visible, sort, lft, rgt)
					VALUES(
					'" . $pid . "',
					$titlevalues
					$descriptionvalues
					$customfieldvalues1
					$customfieldvalues2
					$customfieldvalues3
					'" . $canpost . "',
					'" . $xml . "',
					'" . $newsletter . "',
					'" . $insertiongroup . "',
					'" . $finalvaluegroup . "',
					'" . $incrementgroup . "',
					'product',
					'" . $bidtypes . "',
					'" . $useproxybid . "',
					'" . $usereserveprice . "',
					'" . $useantisnipe . "',
					'" . $catimage . "',
					'" . $keywords . "',
					'" . $visible . "',
					'" . $sort . "',
					'" . ($parent['rgt'] + 1) . "',
					'" . ($parent['rgt'] + 2) . "')
				", 0, null, __FILE__, __LINE__);
				$ilance->db->add_field_if_not_exist(DB_PREFIX . $table, 'sets', "LINESTRING NOT NULL", 'AFTER `parentid`', true);
				
				// #### update the new level bit for the category tree structure
				$ilance->categories_manager->set_levels($table);
				$ilance->categories_manager->rebuild_category_tree(0, 1, 'product', $_SESSION['ilancedata']['user']['slng'], $table);
				$ilance->categories_manager->rebuild_category_geometry($table);
			}
			return true;
		}
		return false;
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>