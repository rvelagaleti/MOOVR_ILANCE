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
* Class to handle file uploading in ILance
*
* @package      iLance\Upload\Handler
* @version      4.0.0.8059
* @author       ILance
*/
class fileuploaderhandler
{
	public $options = array();
	public $thumb_filetypes = array('image/gif', 'image/jpeg', 'image/png');
	public $type = 'auction';
	function __construct()
	{
		$this->options = array (
			'param_name' => 'files',
			'access_control_allow_origin' => '*',
			'access_control_allow_credentials' => false,
			'access_control_allow_methods' => array (
				'OPTIONS',
				'HEAD',
				'GET',
				'POST',
				'DELETE'
			),
			'access_control_allow_headers' => array (
				'Content-Type',
				'Content-Range',
				'Content-Disposition'
			),
		);
	}
    
	public function init()
	{
		switch ($_SERVER['REQUEST_METHOD'])
		{
			case 'GET':
			{
				$this->get();
				break;
			}
			case 'POST':
			{
				$this->post();
				break;
			}
			case 'DELETE':
			{
				$this->delete();
				break;
			}
			default:
			{
				$this->header('HTTP/1.1 405 Method Not Allowed');
				break;
			}
		}
	}
	public function get()
	{
		global $ilance, $ilpage;
		$files = array ();
		$rfpid = isset($ilance->GPC['rfpid']) ? $ilance->GPC['rfpid'] : 0;
		if ($this->attachtype == 'storesitemphoto')
		{
			$sql = $ilance->db->query("
				SELECT a.attachid, a.filehash, a.filename, a.filesize, a.user_id, a.attachtype, a.filetype 
				FROM " . DB_PREFIX . "projects p,
				" . DB_PREFIX . "attachment a
				WHERE p.project_id = '" . intval($rfpid) . "'
					AND p.imageurl_attachid = a.attachid
					AND p.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			");
		}
		else
		{
			$sql = $ilance->db->query("
				SELECT attachid, filehash, filename, filesize, user_id, attachtype, filetype 
				FROM " . DB_PREFIX . "attachment 
				WHERE project_id = '" . intval($rfpid) . "'
					AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			");
		}
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				if (in_array($res['filetype'], $this->thumb_filetypes))
				{
					$thumbnail_url = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['attachment'] . '?cmd=thumb&subcmd=results&id=' . $res['filehash'];
				}
				else
				{
					$thumbnail_url = $ilance->attachment->print_file_extension_icon($res['filename']);
				}
				$file = new stdClass();
				$file->name = $res['filename'];
				$file->size = $res['filesize'];
				$file->url = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['attachment'] . '?id=' . $res['filehash'];
				$file->thumbnail_url = $thumbnail_url;
				$file->delete_url = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['ajax'] . '?do=fileuploader&action=remove&userid=' . $res['user_id'] . '&aid=' . $res['attachid'];
				//$file->delete_type = 'DELETE';
				$file->delete_type = 'GET';
				$files[] = $file;
			}
		}
		return $this->generate_response(array ($this->options['param_name'] => $files));
	}
    
	public function post()
	{
		if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE')
		{
			return $this->delete();
		}
		$upload = isset($_FILES[$this->options['param_name']]) ? $_FILES[$this->options['param_name']] : null;
		$file_name = isset($_SERVER['HTTP_CONTENT_DISPOSITION']) ? rawurldecode(preg_replace('/(^[^"]+")|("$)/', '', $_SERVER['HTTP_CONTENT_DISPOSITION'])) : null;
		$content_range = isset($_SERVER['HTTP_CONTENT_RANGE']) ? preg_split('/[^0-9]+/', $_SERVER['HTTP_CONTENT_RANGE']) : null;
		$size = $content_range ? $content_range[3] : null;
		$files = array ();
		if ($upload && is_array($upload['tmp_name']))
		{
			foreach ($upload['tmp_name'] as $index => $value)
			{
				$files[] = $this->handle_file_upload($upload['tmp_name'][$index], $file_name ? $file_name : $upload['name'][$index], $size ? $size : $upload['size'][$index], $upload['type'][$index], $upload['error'][$index], $index, $content_range);
			}
		}
		else
		{
			$files[] = $this->handle_file_upload(
				isset($upload['tmp_name']) ? $upload['tmp_name'] : null, 
				$file_name ? $file_name : (isset($upload['name']) ? $upload['name'] : null), 
				$size ? $size : (isset($upload['size']) ?$upload['size'] : $_SERVER['CONTENT_LENGTH']), 
				isset($upload['type']) ? $upload['type'] : $_SERVER['CONTENT_TYPE'], 
				isset($upload['error']) ? $upload['error'] : null, 
				null, 
				$content_range
			);
		}
		return $this->generate_response(array ($this->options['param_name'] => $files));
	}
    
	protected function handle_file_upload($uploaded_file, $name, $size, $type, $error, $index = null, $content_range = null)
	{
		$file = new stdClass();
		$file->tmp_name = $uploaded_file;
		$file->name = $name;
		$file->size = $size;
		$file->type = $type;
		$result = $this->upload($file);
		return $result;
	}
    
	public function upload($file)
	{
		global $ilance, $ilpage, $ilconfig;
		$attachtype = $uncrypted['attachtype'] = isset($ilance->GPC['attachtype']) ? $ilance->GPC['attachtype'] : 'itemphoto';
		$project_id = $uncrypted['project_id'] = isset($ilance->GPC['project_id']) ? intval($ilance->GPC['project_id']) : '0';
		$user_id = $uncrypted['user_id'] = isset($_SESSION['ilancedata']['user']['userid']) ? $_SESSION['ilancedata']['user']['userid'] : '';
		$uncrypted['ads_id'] = isset($uncrypted['ads_id']) ? $uncrypted['ads_id'] : '';
		$sql_file_sum = $ilance->db->query("
			SELECT SUM(filesize) AS attach_usage_total
			FROM " . DB_PREFIX . "attachment
			WHERE user_id = '" . $user_id . "'
		");
		if ($ilance->db->num_rows($sql_file_sum) > 0)
		{
			$res_file_sum = $ilance->db->fetch_array($sql_file_sum, DB_ASSOC);
			$attach_usage_total = print_filesize($res_file_sum['attach_usage_total']);
		}
		else
		{
			$res_file_sum['attach_usage_total'] = 0;
		}
		$attach_usage_left = ($ilance->permissions->check_access($user_id, 'attachlimit') - $res_file_sum['attach_usage_total']);
		$attach_usage_left = ($attach_usage_left <= 0) ? print_filesize(0) : print_filesize($attach_usage_left);
		$upload_to = $notice_message = $error_message = $upload_style = $moderated = '';
		$condition = $ilance->attachment->handle_attachtype_upload_settings($attachtype);
		$max_filesize = $condition['max_filesize'];
		$max_size = $condition['max_size'];
		$upload_to = $condition['upload_to'];
		$extensions = $condition['extensions'];
		unset($condition);
		// #### PROCESS AND SAVE ATTACHMENT ############################
		$ilance->attachment->temp_file_name = trim($file->tmp_name);
		$ilance->attachment->file_name = $file->name;
		$ilance->attachment->filetype = $file->type;
		$ilance->attachment->upload_dir = $upload_to;
		$ilance->attachment->max_file_size = $max_filesize;
		$ilance->attachment->attachtype = $attachtype;
		$ilance->attachment->filehash = md5(uniqid(microtime()));
		$ilance->attachment->user_id = $user_id;
		$ilance->attachment->project_id = $project_id;
		// #### validation #############################################
		$valid_ext = $ilance->attachment->validate_extension();
		$file_size = $ilance->attachment->get_file_size();
		$valid_size = $ilance->attachment->validate_size();
		$file_type = $ilance->attachment->get_file_type();
		// #### check if the total attachment limit permission has exceeded for this members upload
		$futuresize = ($res_file_sum['attach_usage_total'] + $file_size);
		$totalpercentage = round(($futuresize / $ilance->permissions->check_access($user_id, 'attachlimit')) * 100);
		// #### set upload error defaults ##############################
		$show['error'] = $show['notice'] = false;
		// #### rebuild attachment select list #########################
		$sql_attachments = array ();
		$condition = $ilance->attachment->handle_attachtype_rebuild_settings($attachtype, $user_id, $project_id, $ilance->attachment->filehash, $uncrypted['ads_id']);
		$maximum_files = $condition['maximum_files'];
		$max_width = $condition['max_width'];
		$max_height = $condition['max_height'];
		$max_filesize = $condition['max_filesize'];
		$max_size = $condition['max_size'];
		$extensions = $condition['extensions'];
		$query = $condition['query'];
		unset($condition);
		// #### have we exceeded our account space based on this upload?
		if ($totalpercentage > 100)
		{
			$error_message .= '{_sorry_you_do_not_have_enough_space_to_upload_new_files}. ';
		}
		// #### is the filename extension valid?
		if ($valid_ext == false)
		{
			$error_message .= '{_the_file_extension_is_invalid_or_the_width_slash_height_exceeds_our_limits}. ';
		}
		// #### is the width or height bad?
		if ((!empty($valid_size['failedwidth']) AND $valid_size['failedwidth'] OR !empty($valid_size['failedheight']) AND $valid_size['failedheight']) AND !isset($uncrypted['ads_id']))
		{
			$error_message .= '{_maximum_width}: ' . $max_width . 'px, {_maximum_height}: ' . $max_height . 'px, {_maximum_filesize}: ' . $max_size . ' {_and_your_attachment_was} ' . $valid_size['uploadwidth'] . 'px ' . mb_strtolower('{_by}') . ' ' . $valid_size['uploadheight'] . 'px, {_upload_size}: ' . print_filesize($valid_size['uploadfilesize']) . '. ';
		}
		// #### is the file size bad?
		if (!empty($valid_size['failedfilesize']) AND $valid_size['failedfilesize'])
		{
			$error_message .= '{_the_file_size_is_invalid_please_try_again_the_maximum_file_size_is}: ' . $max_size . ' {_and_your_file_was}: ' . print_filesize($file_size) . '. ';
		}
		if (!empty($error_message))
		{
			// we have upload errors so set our upload $error to true
			$show['error'] = true;
		}
		// #### begin upload ###########################################
		if ($show['error'] == false)
		{
			if ($ilance->attachment->save_attachment($valid_size))
			{
				if ($ilconfig['attachment_moderationdisabled'] == 0)
				{
					$show['notice'] = true;
					$notice_message .= '{_attachments_are_currently_being_moderated_by_our_staff}. ';
				}
				$details = 'File name: ' . $file->name . ', File type: ' . $file_type . ', File size: ' . $valid_size['uploadfilesize'] . ', Height: ' . $valid_size['uploadheight'] . ', Width: ' . $valid_size['uploadwidth'] . ', Attach type: ' . $attachtype;
				log_event($_SESSION['ilancedata']['user']['userid'], $ilpage['upload'], $attachtype, $project_id, $details);
				$sql_attachid = $ilance->db->query("SELECT attachid FROM " . DB_PREFIX . "attachment WHERE filehash = '" . $ilance->attachment->filehash . "'");
				$res_attachid = $ilance->db->fetch_array($sql_attachid, DB_ASSOC);
				$aid = $res_attachid['attachid'];
				if (in_array($file->type, $this->thumb_filetypes))
				{
					$thumbnail_url = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['attachment'] . '?cmd=thumb&subcmd=results&id=' . $ilance->attachment->filehash;
				}
				else
				{
					$thumbnail_url = $ilance->attachment->print_file_extension_icon($file->name);
				}
				$result = new stdClass();
				$result->tmp_name = $file->tmp_name;
				$result->name = $file->name;
				$result->size = $file->size;
				$result->type = $file->type;
				$result->url = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['attachment'] . '?id=' . $ilance->attachment->filehash;
				$result->thumbnail_url = $thumbnail_url;
				$result->delete_url = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['ajax'] . '?do=fileuploader&action=remove&userid=' . $user_id . '&aid=' . $aid;
				//$result->delete_type = 'DELETE';
				$result->delete_type = 'GET';
				return $result;
				exit();
			}
		}
		$show['error'] = true;
		$error_message .= '{_your_attachment_could_not_be_uploaded}. ';
		log_event($_SESSION['ilancedata']['user']['userid'], $ilpage['upload'], $uncrypted['attachtype'], $uncrypted['project_id'], $error_message);
		$ilance->template->templateregistry['error_message'] = $error_message;
		$ilance->template->parse_template_phrases('error_message');
		$result = new stdClass();
		$result->error = $ilance->template->templateregistry['error_message'];
		return $result;
		exit();
	}
    
	protected function header($str)
	{
		header($str);
	}
    
	public function head()
	{
		$this->header('Pragma: no-cache');
		$this->header('Cache-Control: no-store, no-cache, must-revalidate');
		$this->header('Content-Disposition: inline; filename="files.json"');
		$this->header('X-Content-Type-Options: nosniff');
		if ($this->options['access_control_allow_origin'])
		{
			$this->header('Vary: Accept');
			if (isset($_SERVER['HTTP_ACCEPT']) && (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false))
			{
				$this->header('Content-type: application/json');
			}
			else
			{
				$this->header('Content-type: text/plain');
			}
		}
		$this->header('Access-Control-Allow-Origin: ' . $this->options['access_control_allow_origin']);
		$this->header('Access-Control-Allow-Credentials: ' . ($this->options['access_control_allow_credentials'] ? 'true' : 'false'));
		$this->header('Access-Control-Allow-Methods: ' . implode(', ', $this->options['access_control_allow_methods']));
		$this->header('Access-Control-Allow-Headers: ' . implode(', ', $this->options['access_control_allow_headers']));
	}
    
	function generate_response($content)
	{
		$json = json_encode($content);
		$this->head();
		echo $json;
		return $content;
	}
    
	public function delete()
	{
		global $ilance;
		$userid = isset($_REQUEST['userid']) ? $_REQUEST['userid'] : 0;
		$result = false;
		if (isset($_SESSION['ilancedata']['user']['userid']) AND ($userid == $_SESSION['ilancedata']['user']['userid'] OR (isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'])))
		{
			$attachmentid = isset($_REQUEST['aid']) ? $_REQUEST['aid'] : 0;
			$ilance->attachment->remove_attachment(intval($attachmentid), $userid);
			$result = true;
		}
		return $this->generate_response(array('success' => $result));
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>