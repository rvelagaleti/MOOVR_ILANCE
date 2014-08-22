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
* Attachment class to perform the majority of uploading and attachment handling operations within ILance.
*
* @package      iLance\Attachment
* @version      4.0.0.8059
* @author       ILance
*/
class attachment
{
        /**
        * Total attachments counter
        * @var integer
        * @access public
        */
        var $totalattachments = null;
        /**
        * Total diskspace counter
        * @var integer
        * @access public
        */
        var $totaldiskspace = null;
        /**
        * Total downloads counter
        * @var integer
        * @access public
        */
        var $totaldownloads = null;
        /**
        * Storage type method
        * @var string
        * @access public
        */
        var $storagetype = null;
        /**
        * Temp filename placeholder
        * @var string
        * @access public
        */
        var $temp_file_name = null;
        /**
        * Real filename placeholder
        * @var string
        * @access public
        */
        var $file_name = null;
        /**
        * Upload folder
        * @var string
        * @access public
        */
        var $upload_dir = null;
        /**
        * Maximum filesize placeholder
        * @var integer
        * @access public
        */
        var $max_file_size = null;
        /**
        * File extensions array placeholder
        * @var array
        * @access public
        */
        var $ext_array = array();
        /**
        * File data placeholder
        * @var string
        * @access public
        */
        var $filedata = null;
        /**
        * Filetype placeholder
        * @var string
        * @access public
        */
        var $filetype = null;
        /**
        * Datetime placeholder
        * @var string
        * @access public
        */
        var $date_time = null;
        /**
        * Picture width placeholder
        * @var integer
        * @access public
        */
        var $width = null;
        /**
        * Picture height placeholder
        * @var integer
        * @access public
        */
        var $height = null;
        /**
        * Original width placeholder
        * @var integer
        * @access public
        */
        var $width_original = null;
        /**
        * Original height placeholder
        * @var integer
        * @access public
        */
        var $height_original = null;
        /**
        * Full width placeholder
        * @var integer
        * @access public
        */
        var $width_full = null;
        /**
        * Full height placeholder
        * @var integer
        * @access public
        */
        var $height_full = null;
        /**
        * Mini width placeholder
        * @var integer
        * @access public
        */
        var $width_mini = null;
        /**
        * Mini height placeholder
        * @var integer
        * @access public
        */
        var $height_mini = null;
        /**
        * Search width placeholder
        * @var integer
        * @access public
        */
        var $width_search = null;
        /**
        * Search height placeholder
        * @var integer
        * @access public
        */
        var $height_search = null;
        /**
        * Gallery width placeholder
        * @var integer
        * @access public
        */
        var $width_gallery = null;
        /**
        * Gallery height placeholder
        * @var integer
        * @access public
        */
        var $height_gallery = null;
        /**
        * Snapshot width placeholder
        * @var integer
        * @access public
        */
        var $width_snapshot = null;
        /**
        * Snapshot height placeholder
        * @var integer
        * @access public
        */
        var $height_snapshot = null;
        /**
        * Filesize placeholder
        * @var integer
        * @access public
        */
        var $filesize = null;
        /**
        * Original filesize placeholder
        * @var integer
        * @access public
        */
        var $filesize_original = null;
        /**
        * Full filesize placeholder
        * @var integer
        * @access public
        */
        var $filesize_full = null;
        /**
        * Mini filesize placeholder
        * @var integer
        * @access public
        */
        var $filesize_mini = null;
        /**
        * Search filesize placeholder
        * @var integer
        * @access public
        */
        var $filesize_search = null;
        /**
        * Gallery filesize placeholder
        * @var integer
        * @access public
        */
        var $filesize_gallery = null;
        /**
        * Snapshot filesize placeholder
        * @var integer
        * @access public
        */
        var $filesize_snapshot = null;
        /**
        * Original filedata placeholder
        * @var string
        * @access public
        */
        var $filedata_original = null;
        /**
        * Full filedata placeholder
        * @var string
        * @access public
        */
        var $filedata_full = null;
        /**
        * Mini filedata placeholder
        * @var string
        * @access public
        */
        var $filedata_mini = null;
        /**
        * Search filedata placeholder
        * @var string
        * @access public
        */
        var $filedata_search = null;
        /**
        * Gallery filedata placeholder
        * @var string
        * @access public
        */
        var $filedata_gallery = null;
        /**
        * Snapshot filedata placeholder 
        * @var string
        * @access public
        */
        var $filedata_snapshot = null;
        /**
        * Original filetype placeholder
        * @var string
        * @access public
        */
        var $filetype_original = null;
        /**
        * Original filename placeholder
        * @var string
        * @access public
        */
        var $file_name_original = null;
        /**
        * Picture was resized placeholder
        * @var boolean
        * @access public
        */
        var $pictureresized = false;
        /**
        * Picture was watermarked placeholder
        * @var boolean
        * @access public
        */
        var $watermarked = false;
        /**
        * Exif filedata placeholder
        * @var boolean
        * @access public
        */
        var $exif = null;
        /**
        * File mimetypes placeholder
        * @var array
        * @access public
        */
        var $mimetypes = array(
                'image/gif',
                'image/jpeg',
                'image/pjpeg',
                'image/png',
                'image/psd',
                'image/bmp',
                'image/tiff',
                'image/jp2',
                'image/iff',
                'image/fif',
                'image/florian',
                'image/g3fax',
                'image/xbm',
                'image/ief',
                'image/jutvision',
                'image/naplps',
                'image/vnd.wap.wbmp',
                'image/vnd.microsoft.icon',
                'image/vnd.fpx',
                'image/vnd.net-fpx',
                'image/vnd.djvu',
                'image/vnd.dwg',
                'image/vnd.xiff',
                'image/vnd.rn-realflash',
                'image/vnd.rn-realpix',
                'image/cmu-raster',
                'image/x-icon',
                'image/x-dwg',
                'image/x-cmu-raster',
                'image/x-cmu-raster',
                'image/x-portable-anymap',
                'image/x-portable-bitmap',
                'image/x-portable-graymap',
                'image/x-portable-pixmap',
                'image/x-xwindowdump',
                'image/x-rgb',
                'image/x-xbitmap',
                'image/x-xpixmap',
                'image/x-xwindowdump',
                'image/x-png',
                'image/x-jps',
                'image/x-pict',
                'image/x-pcx',
                'image/x-xbm',
                'image/x-xpixmap',
                'image/x-quicktime',
                'image/x-niff',
                'image/x-tiff'
        );
        /**
        * Attachment type placeholder
        * 
        * @var string
        * @access public
        */
	public $attachtype = '';
        /**
        * Attachment filehash placeholder
        * 
        * @var string
        * @access public
        */
	public $filehash = '';
        /**
        * Attachment user id
        * 
        * @var integer
        * @access public
        */
	public $user_id = 0;
        /**
        * Portfolio id
        * 
        * @var integer
        * @access public
        */
	public $portfolio_id = 0;
        /**
        * Listing id
        * 
        * @var integer
        * @access public
        */
	public $project_id = 0;
        /**
        * Category id
        * 
        * @var integer
        * @access public
        */
	public $category_id = 0;

        /***
        * Constructor
        */
        function attachment(){}
        
        /**
        * Function for printing the innerhtml javascript code in the templates
        *
        * @param       string       attachment div id
        * @param       string       attachment list html contents
        *
        * @return      string       Returns javascript code
        */
        function print_innerhtml_js($attachmentlist = 'attachmentlist', $attachment_list_html = '', $attachmentlist_hide = '')
        {
                global $ilance, $show;
                $js = '<script type="text/javascript">
<!--
switch (DOMTYPE)
{
        case "std":
        {
                var ' . $attachmentlist . ' = window.opener.document.getElementById("' . $attachmentlist . '");';
                $js .= (!empty($attachmentlist_hide) ? 'var ' . $attachmentlist_hide . ' = window.opener.document.getElementById(\'' . $attachmentlist_hide . '\');' : '');
$js .= '
        }
        break;
        case "ie4":
        {
                var ' . $attachmentlist . ' = window.opener.document.all["' . $attachmentlist . '"];';
                $js .= (!empty($attachmentlist_hide) ? 'var ' . $attachmentlist_hide . ' = window.opener.document.all[\'' . $attachmentlist_hide . '\'];' : '');
$js .= '
        }
}
' . $attachmentlist . '.innerHTML = \'' . $attachment_list_html . '\';';
$js .= (!empty($attachmentlist_hide) ? $attachmentlist_hide . '.innerHTML = \'\';' : '');
$js .= '
//-->
</script>';
                ($apihook = $ilance->api('print_innerhtml_js_end')) ? eval($apihook) : false;
                return $js;
        }

        /**
        * Function for returning the total amount of attachments in the system
        *
        * @return      integer      total amount of attachments
        */
        function totalattachments()
        {
                global $ilance;
                $sql = $ilance->db->query("
                        SELECT COUNT(*) AS totalattachments
                        FROM " . DB_PREFIX . "attachment
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        return $res['totalattachments'];
                }
                return '0';
        }

        /**
        * Function for returning the total amount of disk space used by attachments in the system
        *
        * @return      integer      total amount of attachments
        */
        function totaldiskspace()
        {
                global $ilance;
                $sql = $ilance->db->query("
                        SELECT SUM(filesize) AS totaldiskspace
                        FROM " . DB_PREFIX . "attachment
                        WHERE (filesize != '' OR filesize != '0')
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        return print_filesize($res['totaldiskspace']);
                }
                return print_filesize(0);
        }

        /**
        * Function for returning the total downloads based on attachments in the system
        *
        * @return      integer      total number of downloads
        */
        function totaldownloads()
        {
                global $ilance;
                $sql = $ilance->db->query("
                        SELECT SUM(counter) AS totaldownloads
                        FROM " . DB_PREFIX . "attachment
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        return $res['totaldownloads'];
                }
                return '0';
        }

        /**
        * Function for returning the method of storage used by the attachment system
        *
        * @param       string       action of function to return
        *
        * @return      mixed
        */
        function storagetype($action = '')
        {
                global $ilance, $phrase;
                if (isset($action) AND $action == 'type')
                {
                        $sql = $ilance->db->query("
                                SELECT value
                                FROM " . DB_PREFIX . "configuration
                                WHERE name = 'attachment_dbstorage'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                                if ($res['value'] > 0)
                                {
                                        return '{_attachments_are_currently_being_stored_in_the_database}';
                                }
                                return '{_attachments_are_currently_being_stored_in_the_filepath_system}';
                        }
                        return '{_attachments_are_currently_being_stored_in_the_database}';
                }
                else if (isset($action) AND $action == 'formaction')
                {
                        $sql = $ilance->db->query("
                                SELECT value
                                FROM " . DB_PREFIX . "configuration
                                WHERE name = 'attachment_dbstorage'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                                if ($res['value'] > 0)
                                {
                                        return '<div><input type="radio" name="action" id="action1" value="movetofilepath" checked="checked" /> <label for="action1">{_move} <strong>' . number_format($this->totalattachments()) . '</strong> {_attachments_from_the_database_to_the_filesystem}</label></div><div style="padding-top:5px"><input type="radio" name="action" id="action2" value="rebuildpictures" /> <label for="action2">{_rebuild_all_pictures_to_adhere}</label></div>';
                                }
                                return '<div><input type="radio" name="action" id="action1" value="movetodatabase" checked="checked" /> <label for="action1">{_move} <strong>' . number_format($this->totalattachments()) . '</strong> {_attachments_from_the_filesystem_to_the_database}</label></div><div style="padding-top:5px"><input type="radio" name="action" id="action2" value="rebuildpictures" /> <label for="action2">{_rebuild_all_pictures_to_adhere}</label></div>';
                        }
                        return '<div><input type="radio" name="action" id="action1" value="movetofilepath" checked="checked" /> <label for="action1">{_move_attachments_from_the_database_to_the_filesystem}</label></div><div style="padding-top:5px"><input type="radio" name="action" id="action2" value="rebuildpictures" /> <label for="action2">{_rebuild_all_pictures_to_adhere}</label></div>';
                }
        }

        /**
        * Function for validating the filename extention based on the file being uploaded
        *
        * @return      bool         true or false if extension is valid
        */
        function validate_extension()
        {
                $extension = mb_strtolower(mb_strrchr($this->file_name, '.'));
                if (!$this->file_name)
                {
                        return false;
                }
                if (!$this->ext_array)
                {
                        return true;
                }
                $extensions = array();
                foreach ($this->ext_array AS $value)
                {
                        $first_char = mb_substr($value, 0, 1);
                        $extensions[] = (($first_char <> '.') ? '.' . mb_strtolower($value) : mb_strtolower($value));
                }
                foreach ($extensions AS $accepted)
                {
                        if ($accepted == $extension)
                        {
                                return true;
                        }
                }
        }

        /**
        * Function to return the actual file type of a file being uploaded
        *
        * @return      string       file type
        */
        function get_file_type()
        {
                $file_type = trim($this->filetype);
                $file_type = ($file_type) ? $file_type : '';
                return $file_type;
        }

        /**
        * Function to return the actual file size of a file being uploaded
        *
        * @return      string       file type
        */
        function get_file_size()
        {
                $this->temp_file_name = trim($this->temp_file_name);
                $size = (!empty($this->temp_file_name)) ? filesize($this->temp_file_name) : 0;
                return $size;
        }

        /**
        * Function to return the maximum size permitted for upload (should already be assigned)
        *
        * @return      string       maximum file size
        */
        function get_max_size()
        {
                $kb = 1024;
                $mb = 1024 * $kb;
                $gb = 1024 * $mb;
                $tb = 1024 * $gb;
                if (!empty($this->max_file_size))
                {
                        if ($this->max_file_size < $kb)
                        {
                                $this->max_file_size = "{_max_file_size_bytes}";
                        }
                        else if ($this->max_file_size < $mb)
                        {
                                $final = round($this->max_file_size / $kb, 2);
                                $this->max_file_size = "$final";
                        }
                        else if ($this->max_file_size < $gb)
                        {
                                $final = round($this->max_file_size / $mb, 2);
                                $this->max_file_size = "$final";
                        }
                        else if ($this->max_file_size < $tb)
                        {
                                $final = round($this->max_file_size / $gb, 2);
                                $this->max_file_size = "$final";
                        }
                        else
                        {
                                $final = round($this->max_file_size / $tb, 2);
                                $this->max_file_size = "$final";
                        }
                }
                else
                {
                        $this->max_file_size = '{_error_no_size_passed}';
                }
                return $this->max_file_size;
        }

        /**
        * Function to return the full upload directory (should already be assigned)
        *
        * @return      string       full folder path
        */
        function get_upload_directory()
        {
                $upload_dir = trim($this->upload_dir);
                if ($upload_dir)
                {
                        $ud_len = mb_strlen($upload_dir);
                        $last_slash = mb_substr($upload_dir, $ud_len - 1, 1);
                        if ($last_slash <> '/')
                        {
                                $upload_dir = $upload_dir . '/';
                        }
                        else
                        {
                                $upload_dir = $upload_dir;
                        }
                        $handle = @opendir($upload_dir);
                        if ($handle)
                        {
                                $upload_dir = $upload_dir;
                                closedir($handle);
                        }
                        else
                        {
                                $upload_dir = 'ERROR';
                        }
                }
                else
                {
                        $upload_dir = 'ERROR';
                }
                return $upload_dir;
        }

        /**
        * Function to handle the attachment type settings for the current upload
        *
        * @param       string       attachment type
        * @param       integer      user id
        * @param       integer      listing id
        * @param       string       filehash
        * @param       integer      ads id
        *
        * @return      array        Returns array with rebuilt attachment settings
        */
        function handle_attachtype_rebuild_settings($attachtype = '', $userid = 0, $projectid = 0, $filehash = '', $ads_id = 0)
        {
                global $ilance, $ilconfig, $show, $area_title, $page_title, $phrase;
                $array = array();
                $maximum_files = $max_width = $max_height = $max_filesize = $max_size = $extensions = $query = '';
                if ($attachtype == 'profile')
                {
                        $area_title = '{_uploading_profile_attachments}';
                        $page_title = SITE_NAME . ' - {_uploading_profile_attachments}';
                        $maximum_files = $ilance->permissions->check_access($userid, 'maxprofileattachments');
                        $max_filesize = $ilconfig['attachmentlimit_profilemaxsize'];
                        $max_size = print_filesize($ilconfig['attachmentlimit_profilemaxsize']);
                        $max_width = $ilconfig['attachmentlimit_profilemaxwidth'];
                        $max_height = $ilconfig['attachmentlimit_profilemaxheight'];
                        $show['ifextensions'] = true;
                        $extensions = '';
                        $permittedext = explode(',', $ilconfig['attachmentlimit_profileextensions']);
                        foreach ($permittedext AS $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        $query = "SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, width, height, width_original, height_original, width_full, height_full, width_mini, height_mini, width_search, height_search, width_gallery, height_gallery, width_snapshot, height_snapshot, filesize_original, visible, counter, filesize, filesize_full, filesize_mini, filesize_search, filesize_gallery, filesize_snapshot, filehash, ipaddress, tblfolder_ref
FROM " . DB_PREFIX . "attachment
WHERE attachtype = '" . $ilance->db->escape_string($attachtype) . "'
AND user_id = '" . intval($userid) . "'";
                }
                else if ($attachtype == 'portfolio')
                {
                        $area_title = '{_uploading_portfolio_attachments}';
                        $page_title = SITE_NAME . ' - {_uploading_portfolio_attachments}';
                        $maximum_files = $ilance->permissions->check_access($userid, 'maxportfolioattachments');
                        $max_filesize = $ilconfig['attachmentlimit_portfoliomaxsize'];
                        $max_size = print_filesize($ilconfig['attachmentlimit_portfoliomaxsize']);
                        $max_width = $ilconfig['attachmentlimit_portfoliomaxwidth'];
                        $max_height = $ilconfig['attachmentlimit_portfoliomaxheight'];
                        $show['portfolio_manage'] = true;
                        $show['ifextensions'] = true;
                        $extensions = '';
                        $permittedext = explode(',', $ilconfig['attachmentlimit_portfolioextensions']);
                        foreach ($permittedext AS $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        $query = "SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, width, height, width_original, height_original, width_full, height_full, width_mini, height_mini, width_search, height_search, width_gallery, height_gallery, width_snapshot, height_snapshot, filesize_original, visible, counter, filesize, filesize_full, filesize_mini, filesize_search, filesize_gallery, filesize_snapshot, filehash, ipaddress, tblfolder_ref
FROM " . DB_PREFIX . "attachment
WHERE attachtype = '" . $ilance->db->escape_string($attachtype) . "'
AND user_id = '" . intval($userid) . "'";
                }
                else if ($attachtype == 'project')
                {
                        $area_title = '{_uploading_auction_attachments}';
                        $page_title = SITE_NAME . ' - {_uploading_auction_attachments}';
                        $maximum_files = $ilance->permissions->check_access($userid, 'maxprojectattachments');
                        $max_size = print_filesize($ilconfig['attachmentlimit_projectmaxsize']);
                        $max_filesize = $ilconfig['attachmentlimit_projectmaxsize'];
                        $max_width = $ilconfig['attachmentlimit_projectmaxwidth'];
                        $max_height = $ilconfig['attachmentlimit_projectmaxheight'];
                        $show['ifextensions'] = true;
                        $extensions = '';
                        $permittedext = explode(',', $ilconfig['attachmentlimit_defaultextensions']);
                        foreach ($permittedext AS $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        $query = "SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, width, height, width_original, height_original, width_full, height_full, width_mini, height_mini, width_search, height_search, width_gallery, height_gallery, width_snapshot, height_snapshot, filesize_original, visible, counter, filesize, filesize_full, filesize_mini, filesize_search, filesize_gallery, filesize_snapshot, filehash, ipaddress, tblfolder_ref
FROM " . DB_PREFIX . "attachment
WHERE attachtype = '" . $ilance->db->escape_string($attachtype) . "'
AND user_id = '" . intval($userid) . "'
AND project_id = '" . intval($projectid) . "'";
                }
                else if ($attachtype == 'itemphoto')
                {
                        $area_title = '{_uploading_auction_attachments}';
                        $page_title = SITE_NAME . ' - {_uploading_auction_attachments}';
                        $maximum_files = 1;
                        $max_size = print_filesize($ilconfig['attachmentlimit_productphotomaxsize']);
                        $max_filesize = $ilconfig['attachmentlimit_productphotomaxsize'];
                        $max_width = $ilconfig['attachmentlimit_productphotomaxwidth'];
                        $max_height = $ilconfig['attachmentlimit_productphotomaxheight'];
                        $show['ifextensions'] = true;
                        $extensions = '';
                        $permittedext = explode(',', $ilconfig['attachmentlimit_productphotoextensions']);
                        foreach ($permittedext AS $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        $query = "SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, width, height, width_original, height_original, width_full, height_full, width_mini, height_mini, width_search, height_search, width_gallery, height_gallery, width_snapshot, height_snapshot, filesize_original, visible, counter, filesize, filesize_full, filesize_mini, filesize_search, filesize_gallery, filesize_snapshot, filehash, ipaddress, tblfolder_ref
FROM " . DB_PREFIX . "attachment
WHERE attachtype = '" . $ilance->db->escape_string($attachtype) . "'
AND user_id = '" . intval($userid) . "'
AND project_id = '" . intval($projectid) . "'";
                }
                else if ($attachtype == 'bid')
                {
                        $area_title = '{_uploading_bid_proposal_attachments}';
                        $page_title = SITE_NAME . ' - {_uploading_bid_proposal_attachments}';
                        $maximum_files = $ilance->permissions->check_access($userid, 'maxbidattachments');
                        $max_filesize = $ilconfig['attachmentlimit_bidmaxsize'];
                        $max_size = print_filesize($ilconfig['attachmentlimit_bidmaxsize']);
                        $max_width = $ilconfig['attachmentlimit_bidmaxwidth'];
                        $max_height = $ilconfig['attachmentlimit_bidmaxheight'];
                        $show['ifextensions'] = true;
                        $extensions = '';
                        $permittedext = explode(',', $ilconfig['attachmentlimit_defaultextensions']);
                        foreach ($permittedext AS $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        $query = "SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, width, height, width_original, height_original, width_full, height_full, width_mini, height_mini, width_search, height_search, width_gallery, height_gallery, width_snapshot, height_snapshot, filesize_original, visible, counter, filesize, filesize_full, filesize_mini, filesize_search, filesize_gallery, filesize_snapshot, filehash, ipaddress, tblfolder_ref
FROM " . DB_PREFIX . "attachment
WHERE attachtype = '" . $ilance->db->escape_string($attachtype) . "'
AND user_id = '" . intval($userid) . "'
AND project_id = '" . intval($projectid) . "'";
                }
                else if ($attachtype == 'pmb')
                {
                        $area_title = '{_uploading_pmb_attachments}';
                        $page_title = SITE_NAME . ' - {_uploading_pmb_attachments}';
                        $maximum_files = $ilance->permissions->check_access($userid, 'maxpmbattachments');
                        $max_filesize = $ilconfig['attachmentlimit_pmbmaxsize'];
                        $max_size = print_filesize($ilconfig['attachmentlimit_pmbmaxsize']);
                        $max_width = $ilconfig['attachmentlimit_pmbmaxwidth'];
                        $max_height = $ilconfig['attachmentlimit_pmbmaxheight'];
                        $show['ifextensions'] = true;
                        $extensions = '';
                        $permittedext = explode(',', $ilconfig['attachmentlimit_defaultextensions']);
                        foreach ($permittedext AS $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        $query = "SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, width, height, width_original, height_original, width_full, height_full, width_mini, height_mini, width_search, height_search, width_gallery, height_gallery, width_snapshot, height_snapshot, filesize_original, visible, counter, filesize, filesize_full, filesize_mini, filesize_search, filesize_gallery, filesize_snapshot, filehash, ipaddress, tblfolder_ref
FROM " . DB_PREFIX . "attachment
WHERE attachtype = '" . $ilance->db->escape_string($attachtype) . "'
AND user_id = '" . intval($userid) . "'
AND project_id = '" . intval($projectid) . "'";
                }
                else if ($attachtype == 'digital')
                {
                        $area_title = '{_uploading_auction_attachments}';
                        $page_title = SITE_NAME . ' - {_uploading_auction_attachments}';
                        $maximum_files = 1;
                        $max_size = print_filesize($ilconfig['attachmentlimit_digitalfilemaxsize']);
                        $max_filesize = $ilconfig['attachmentlimit_digitalfilemaxsize'];
                        $max_width = 0;
                        $max_height = 0;
                        $show['ifextensions'] = true;
                        $extensions = '';
                        $permittedext = explode(',', $ilconfig['attachmentlimit_digitalfileextensions']);
                        foreach ($permittedext AS $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        $query = "SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, width, height, width_original, height_original, width_full, height_full, width_mini, height_mini, width_search, height_search, width_gallery, height_gallery, width_snapshot, height_snapshot, filesize_original, visible, counter, filesize, filesize_full, filesize_mini, filesize_search, filesize_gallery, filesize_snapshot, filehash, ipaddress, tblfolder_ref
FROM " . DB_PREFIX . "attachment
WHERE attachtype = '" . $ilance->db->escape_string($attachtype) . "'
AND user_id = '" . intval($userid) . "'
AND project_id = '" . intval($projectid) . "'";
                }
                else if ($attachtype == 'slideshow')
                {
                        $area_title = '{_uploading_auction_attachments}';
                        $page_title = SITE_NAME . ' - {_uploading_auction_attachments}';
                        $maximum_files = $ilconfig['attachmentlimit_slideshowmaxfiles'];
                        $max_width = $ilconfig['attachmentlimit_slideshowmaxwidth'];
                        $max_height = $ilconfig['attachmentlimit_slideshowmaxheight'];
                        $max_filesize = $ilconfig['attachmentlimit_slideshowmaxsize'];
                        $max_size = print_filesize($ilconfig['attachmentlimit_slideshowmaxsize']);
                        $show['ifextensions'] = true;
                        $extensions = '';
                        $permittedext = explode(',', $ilconfig['attachmentlimit_slideshowextensions']);
                        foreach ($permittedext AS $value)
                        {
                                $extensions .= $value.'&nbsp;';
                        }
                        $query = "SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, width, height, width_original, height_original, width_full, height_full, width_mini, height_mini, width_search, height_search, width_gallery, height_gallery, width_snapshot, height_snapshot, filesize_original, visible, counter, filesize, filesize_full, filesize_mini, filesize_search, filesize_gallery, filesize_snapshot, filehash, ipaddress, tblfolder_ref
FROM " . DB_PREFIX . "attachment
WHERE attachtype = '" . $ilance->db->escape_string($attachtype) . "'
AND user_id = '" . intval($userid) . "'
AND project_id = '" . intval($projectid) . "'";
                }
                
                ($apihook = $ilance->api('handle_attachtype_rebuild_settings_end')) ? eval($apihook) : false;

                $array = array(
                        'maximum_files' => $maximum_files,
                        'max_width' => $max_width,
                        'max_height' => $max_height,
                        'max_filesize' => $max_filesize,
                        'max_size' => $max_size,
                        'extensions' => $extensions,
                        'query' => $query
                );
                return $array;
        }

        /**
        * Function to handle the uploaded file settings parsing based on the attachment type
        *
        * @param        string       attach type
        *
        * @return       string       
        */
        function handle_attachtype_upload_settings($attachtype = '')
        {
                global $ilance, $ilconfig, $phrase, $show;
                $array = array();
                if ($attachtype == 'profile')
                {
                        $max_filesize = $ilconfig['attachmentlimit_profilemaxsize'];
                        $max_size = $ilconfig['attachmentlimit_profilemaxsize'];
                        $upload_to = DIR_PROFILE_ATTACHMENTS;
                        $permittedext = explode(',', $ilconfig['attachmentlimit_profileextensions']);
                        $extensions = '';
                        foreach ($permittedext AS $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        $this->ext_array = explode(', ', $ilconfig['attachmentlimit_profileextensions']);
                }
                else if ($attachtype == 'portfolio')
                {
                        $max_filesize = $ilconfig['attachmentlimit_portfoliomaxsize'];
                        $max_size = $ilconfig['attachmentlimit_portfoliomaxsize'];
                        $upload_to = DIR_PORTFOLIO_ATTACHMENTS;
                        $permittedext = explode(',', $ilconfig['attachmentlimit_portfolioextensions']);
                        $extensions = '';
                        foreach ($permittedext AS $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        $this->ext_array = explode(', ', $ilconfig['attachmentlimit_portfolioextensions']);
                }
                else if ($attachtype == 'project')
                {
                        $max_filesize = $ilconfig['attachmentlimit_projectmaxsize'];
                        $max_size = $ilconfig['attachmentlimit_projectmaxsize'];
                        $upload_to = DIR_AUCTION_ATTACHMENTS;
                        $permittedext = explode(',', $ilconfig['attachmentlimit_defaultextensions']);
                        $extensions = '';
                        foreach ($permittedext AS $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        $this->ext_array = explode(', ', $ilconfig['attachmentlimit_defaultextensions']);
                }
                else if ($attachtype == 'itemphoto')
                {
                        $max_filesize = $ilconfig['attachmentlimit_productphotomaxsize'];
                        $max_size = $ilconfig['attachmentlimit_productphotomaxsize'];
                        $upload_to = DIR_AUCTION_ATTACHMENTS;
                        $permittedext = explode(',', $ilconfig['attachmentlimit_productphotoextensions']);
                        $extensions = '';
                        foreach ($permittedext AS $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        $this->ext_array = explode(', ', $ilconfig['attachmentlimit_productphotoextensions']);
                }
                else if ($attachtype == 'bid')
                {
                        $max_filesize = $ilconfig['attachmentlimit_bidmaxsize'];
                        $max_size = $ilconfig['attachmentlimit_bidmaxsize'];
                        $upload_to = DIR_BID_ATTACHMENTS;
                        $permittedext = explode(',', $ilconfig['attachmentlimit_defaultextensions']);
                        $extensions = '';
                        foreach ($permittedext AS $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        $this->ext_array = explode(', ', $ilconfig['attachmentlimit_defaultextensions']);
                }
                else if ($attachtype == 'pmb')
                {
                        $max_filesize = $ilconfig['attachmentlimit_pmbmaxsize'];
                        $max_size = $ilconfig['attachmentlimit_pmbmaxsize'];
                        $upload_to = DIR_PMB_ATTACHMENTS;
                        $permittedext = explode(',', $ilconfig['attachmentlimit_defaultextensions']);
                        $extensions = '';
                        foreach ($permittedext AS $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        $this->ext_array = explode(', ', $ilconfig['attachmentlimit_defaultextensions']);
                }
                else if ($attachtype == 'ws')
                {
                        $max_filesize = $ilconfig['attachmentlimit_mediasharemaxsize'];
                        $max_size = $ilconfig['attachmentlimit_mediasharemaxsize'];
                        $upload_to = DIR_WS_ATTACHMENTS;
                        $permittedext = explode(',', $ilconfig['attachmentlimit_defaultextensions']);
                        $extensions = '';
                        foreach ($permittedext AS $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        $this->ext_array = explode(', ', $ilconfig['attachmentlimit_defaultextensions']);
                }
                else if ($attachtype == 'digital')
                {
                        $max_filesize = $ilconfig['attachmentlimit_digitalfilemaxsize'];
                        $max_size = $ilconfig['attachmentlimit_digitalfilemaxsize'];
                        $upload_to = DIR_AUCTION_ATTACHMENTS;
                        $permittedext = explode(',', $ilconfig['attachmentlimit_digitalfileextensions']);
                        $extensions = '';
                        foreach ($permittedext AS $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        $this->ext_array = explode(', ', $ilconfig['attachmentlimit_digitalfileextensions']);
                }
                else if ($attachtype == 'slideshow')
                {
                        $max_filesize = $ilconfig['attachmentlimit_slideshowmaxsize'];
                        $max_size = $ilconfig['attachmentlimit_slideshowmaxsize'];
                        $upload_to = DIR_AUCTION_ATTACHMENTS;
                        $permittedext = explode(',', $ilconfig['attachmentlimit_slideshowextensions']);
                        $extensions = '';
                        foreach ($permittedext AS $value)
                        {
                                $extensions .= $value . '&nbsp;';
                        }
                        $this->ext_array = explode(', ', $ilconfig['attachmentlimit_slideshowextensions']);
                }

                ($apihook = $ilance->api('handle_attachtype_upload_settings_end')) ? eval($apihook) : false;

                $array = array(
                        'max_filesize' => $max_filesize,
                        'max_size'     => $max_size,
                        'upload_to'    => $upload_to,
                        'extensions'   => $extensions
                );
                return $array;
        }

        /**
        * Function to create a watermarked stamped image (from a text string or source image).  This function will overwrite the source file if no destination folder/file.jpg is specified.
        *
        * @param        string       attachment type
        * @param        string       full server path to the picture that you are going to watermark
        * @param        string       file extension of real picture being passed to this function
        * @param        string       blank (to process current source file only) or full server path to a new file which will be the source file with watermark text on it
        *
        * @return       string       
        */
        function watermark($attachtype = '', $src = '', $srcextension = '', $dsrc = '')
        {
                global $ilconfig;
                if ($ilconfig['watermark'] == 0)
                {
                        $this->watermarked = false;
                        return false;
                }
                $wsrc = !empty($ilconfig['watermark_image']) ? DIR_SERVER_ROOT . 'images/default/' . $ilconfig['watermark_image'] : '';
                $mode = 'image';
                if (empty($wsrc) AND !empty($ilconfig['watermark_text']))
                {
                        $mode = 'text';
                }
                $text = $ilconfig['watermark_text'];
                $font = DIR_FONTS . $ilconfig['watermark_textfont'];
                $font_size = $ilconfig['watermark_textsize'];
                $quality = $ilconfig['watermark_quality'];   
                $font_angle = $ilconfig['watermark_angle'];
                $markposition = $ilconfig['watermark_position']; 
                $markpadding = $ilconfig['watermark_padding'];
                $opacity = $ilconfig['watermark_imageopacity'];
                if ($attachtype == 'profile')
                {
                        if ($ilconfig['watermark_profiles'] == 0)
                        {
                                $this->watermarked = false;
                                return false;
                        }
                }
                else if ($attachtype == 'portfolio')
                {
                        if ($ilconfig['watermark_portfolios'] == 0)
                        {
                                $this->watermarked = false;
                                return false;
                        }
                }
                else if ($attachtype == 'itemphoto' OR $attachtype == 'slideshow' OR $attachtype == 'storesitemphoto')
                {
                        if ($ilconfig['watermark_itemphoto'] == 0)
                        {
                                $this->watermarked = false;
                                return false;
                        }
                }
                else
                {
                        $this->watermarked = false;
                        return false;
                }
                $r = 1;
                $e = strtolower(substr($srcextension, strrpos($srcextension, '.') + 1, 3));
                if (($e == 'jpg') OR ($e == 'peg') OR ($e == 'jpe'))
                {
                        $oldimage = imagecreatefromjpeg($src) OR $r = 0;
			imagealphablending($oldimage, true);
                }
                else if ($e == 'gif')
                {
                        $oldimage = imagecreatefromgif($src) OR $r = 0;
                }
                else if ($e == 'bmp')
                {
                        $oldimage = $this->imagecreatefrombmp($src) OR $r = 0;
                }
                else if ($e == 'png')
                {
                        $oldimage = imagecreatefrompng($src) OR $r = 0;
                }
                else
                {
                        $r = 0;
                }
                if ($r)
                {
                        list($source_image_width, $source_image_height) = @getimagesize($src);
                        if ($mode == 'text')
                        {
                                $newthumb = imagecreatetruecolor($source_image_width, $source_image_height);
                                imagecopyresampled($newthumb, $oldimage, 0, 0, 0, 0, $source_image_width, $source_image_height, $source_image_width, $source_image_height); 
                                $font_color = imagecolorallocate($newthumb, 0, 0, 0); // black
                                $box = imagettfbbox($font_size, 0, $font, $text);
                                $textwidth = abs($box[4] - $box[0]);
                                $textheight = abs($box[5] - $box[1]);
                                switch ($markposition)
                                {
                                        case 'TOPLEFT':
                                        {
                                                $xcord = $markpadding;
                                                $ycord = ($fontsize + $markpadding);
                                                break;
                                        }
                                        case 'TOPCENTER':
                                        {
                                                $xcord = (($source_image_width - $textwidth) / 2);
                                                $ycord = ($font_size + $markpadding);
                                                break;
                                        }
                                        case 'TOPRIGHT':
                                        {
                                                $xcord = ($source_image_width - $textwidth) - $markpadding;
                                                $ycord = ($fontsize + $markpadding);
                                                break;
                                        }
                                        case 'MIDLEFT':
                                        {
                                                $xcord = $markpadding;
                                                $ycord = (($source_image_height - $textheight) / 2) + ($font_size / 2);
                                                break;
                                        }
                                        case 'MIDCENTER':
                                        {
                                                $xcord = (($source_image_width - $textwidth) / 2);
                                                $ycord = (($source_image_height - $textheight) / 2) + ($font_size / 2);
                                                break;
                                        }
                                        case 'MIDRIGHT':
                                        {
                                                $xcord = ($source_image_width - $textwidth) - $markpadding;
                                                $ycord = (($source_image_height - $textheight) / 2) + ($font_size / 2);
                                                break;
                                        }
                                        case 'BOTLEFT':
                                        {
                                                $xcord = $markpadding;
                                                $ycord = ($source_image_height - $textheight) + ($fontsize - $markpadding);
                                                break;
                                        }
                                        case 'BOTCENTER':
                                        {
                                                $xcord = (($source_image_width - $textwidth) / 2);
                                                $ycord = ($source_image_height - $textheight) + ($font_size - $markpadding);
                                                break;
                                        }
                                        case 'BOTRIGHT':
                                        {
                                                $xcord = ($source_image_width - $textwidth) - $markpadding;
                                                $ycord = ($source_image_height - $textheight) + ($fontsize - $markpadding);
                                                break;
                                        }
                                }
                                imagettftext($newthumb, $font_size, $font_angle, $xcord, $ycord, $font_color, $font, $text);
                                if (!empty($dsrc))
                                {
                                        if (($e == 'jpg') OR ($e == 'peg') OR ($e == 'jpe'))
                                        {
                                                imagejpeg($newthumb, $dsrc, $quality); 
                                        }
                                        else if ($e == 'gif')
                                        {
                                                imagegif($newthumb, $dsrc);
                                        }
                                        else if ($e == 'bmp')
                                        {
                                                imagewbmp($newthumb, $dsrc);
                                        }
                                        else if ($e == 'png')
                                        {
                                                imagepng($newthumb, $dsrc, (int)$quality/10);
                                        }
                                }
                                else
                                {
                                        if (($e == 'jpg') OR ($e == 'peg') OR ($e == 'jpe'))
                                        {
                                                imagejpeg($newthumb, $src, $quality); 
                                        }
                                        else if ($e == 'gif')
                                        {
                                                imagegif($newthumb, $src);
                                        }
                                        else if ($e == 'bmp')
                                        {
                                                imagewbmp($newthumb, $src);
                                        }
                                        else if ($e == 'png')
                                        {
                                                imagepng($newthumb, $src, (int)$quality/10); // always best output
                                        }
                                }
                                imagedestroy($newthumb);
                                imagedestroy($oldimage);
                                $this->watermarked = true;
                        }
                        else if ($mode == 'image' AND !empty($wsrc))
                        {
                                $wext = substr($wsrc, -3);
				list($source_watermark_width, $source_watermark_height) = @getimagesize($wsrc);
				$original_source_watermark_width = $source_watermark_width; // 275px 
				$original_source_watermark_height = $source_watermark_height; // 78px
				if ($source_watermark_width > ($source_image_width * 0.3) && false)
				{
                                        $a = ($source_watermark_width < $source_watermark_height) ? $source_watermark_width / $source_watermark_height : $source_watermark_height / $source_watermark_width;
                                        $source_watermark_width = (int)$source_image_width * 0.3;
                                        $source_watermark_height = (int)$source_watermark_width * $a;
				}
                                if ($wext == 'gif')
				{
                                        $newthumb = imagecreatefromgif($wsrc);
				}
				else if ($wext == 'png')
				{
                                        $newthumb = imagecreatefrompng($wsrc);
                                        imagealphablending($newthumb, true);
                                        imagesavealpha($newthumb, true);
				}
				else
                                {
                                        $this->watermarked = false;
                                        return false;
				}
				$x = $source_image_width - $source_watermark_width;
				$x = (int)$x - ($x / 100);
				$y = $source_image_height - $source_watermark_height;
				$y = (int)$y - ($y / 100);
                                /*
                                * ALIGN TOP, LEFT      : 0, 0, 0, 0
                                * $xcord = 0
                                * $ycord = 0
                                * 
                                * ALIGN TOP RIGHT      : $source_image_width - $source_watermark_width, 0, 0, 0
                                * $xcord = ($source_image_width - $source_watermark_width)
                                * $ycord = 0
                                * 
                                * ALIGN BOTTOM RIGHT   : $source_image_width - $source_watermark_width, $source_image_height - $source_watermark_height, 0, 0
                                * $xcord = ($source_image_width - $source_watermark_width)
                                * $ycord = ($source_image_height - $source_watermark_height)
                                * 
                                * ALIGN BOTTOM LEFT    : 0, $source_image_height - $source_watermark_height, 0, 0
                                * $xcord = 0
                                * $ycord = ($source_image_height - $source_watermark_height)
                                * 
                                * ALIGN CENTER CENTER  : floor(($source_image_width - $source_watermark_width) / 2), floor(($source_image_height - $source_watermark_height) / 2), 0, 0
                                * $xcord = floor(($source_image_width - $source_watermark_width) / 2)
                                * $ycord = floor(($source_image_height - $source_watermark_height) / 2)
                                */
                                // xcord = x-coordinate of destination point.
                                // ycord = y-coordinate of destination point.
				imagecopy($oldimage, $newthumb, $x, $y, 0, 0, $original_source_watermark_width, $original_source_watermark_height);
                                if (!empty($dsrc))
                                {
                                        if (($e == 'jpg') OR ($e == 'peg') OR ($e == 'jpe'))
                                        {
                                                imagejpeg($oldimage, $dsrc, $quality); 
                                        }
                                        else if ($e == 'gif')
                                        {
                                                imagegif($oldimage, $dsrc);
                                        }
                                        else if ($e == 'bmp')
                                        {
                                                imagewbmp($oldimage, $dsrc);
                                        }
                                        else if ($e == 'png')
                                        {
                                                imagepng($oldimage, $dsrc, (int)$quality/10);
                                        }
                                }
                                else
                                {
                                        if (($e == 'jpg') OR ($e == 'peg') OR ($e == 'jpe'))
                                        {
                                                imagejpeg($oldimage, $src, $quality);
                                        }
                                        else if ($e == 'gif')
                                        {
                                                imagegif($oldimage, $src);
                                        }
                                        else if ($e == 'bmp')
                                        {
                                                imagewbmp($oldimage, $src);
                                        }
                                        else if ($e == 'png')
                                        {
                                                imagepng($oldimage, $src, (int)$quality/10);
                                        }
                                }
                                imagedestroy($newthumb);
                                imagedestroy($oldimage);
                                $this->watermarked = true;
                        }
                        else
                        {
                                $this->watermarked = false;
                        }
                }
                else
                {
                        $this->watermarked = false;
                }
        }
        
        /**
        * Function for validating the uploaded file and returning the upload information via array.
        * This function is also responsible for determining if an uploaded picture is larger than max values
        * and will attempt to scale the picture down keeping the aspect ratio.
        *
        * @return      array         Returns array format with information about the file upload details (height, width, if failed, if success, etc.)
        */
        function validate_size()
        {
                global $ilconfig, $uncrypted, $ilance, $show;
		$newfilename = $newfilename_original = $newfilename_full = $newfilename_mini = $newfilename_search = $newfilename_gallery = $newfilename_snapshot = '';
                $this->pictureresized = $this->watermarked = false;
                $this->exif = '';
                $this->file_name_original = trim($this->file_name);
                $this->filetype_original = $this->filetype;
                $this->max_file_size = trim($this->max_file_size);
		$uncrypted['attachtype'] = $this->attachtype = (isset($uncrypted['attachtype']) AND empty($this->attachtype)) ? $uncrypted['attachtype'] : $this->attachtype;
		$this->user_id = (isset($uncrypted['user_id'])AND empty($this->user_id)) ? $uncrypted['user_id'] : $this->user_id;
                $extension = mb_strtolower(mb_strrchr($this->file_name_original, '.'));
                $valid_filesize = true;
                $failedwidth = $failedheight = $failedfilesize = false;
                $allowedextensions = array('.jpg', '.jpeg', '.gif', '.png', '.bmp');
                $this->filehash = $filehash = (empty($this->filehash) AND isset($uncrypted['filehash']) AND !empty($uncrypted['filehash'])) ? $uncrypted['filehash'] : md5(uniqid(microtime()));
                $attachid = $ilance->db->fetch_field(DB_PREFIX . "attachment", "filehash = '" . $ilance->db->escape_string($this->filehash) . "' AND user_id = '" . $this->user_id . "'", "attachid");
                $this->filehash = $filehash = ($attachid > 0) ? md5(uniqid(microtime())) : $this->filehash;
                if (isset($this->temp_file_name) AND is_uploaded_file($this->temp_file_name))
                {
                        $this->filesize_original = $this->filesize_full = $this->filesize_mini = $this->filesize_search = $this->filesize_gallery = $this->filesize_snapshot = $this->filesize = @filesize($this->temp_file_name);
                        $this->filedata_original = $this->filedata_full = $this->filedata_mini = $this->filedata_search = $this->filedata_gallery = $this->filedata_snapshot = $this->filedata = @fread(@fopen($this->temp_file_name, 'rb'), @filesize($this->temp_file_name));
                        foreach ($this->ext_array AS $value)
                        {
                                $first_char = mb_substr($value, 0, 1);
                                if ($first_char <> '.')
                                {
                                        $extensions[] = '.' . mb_strtolower($value);
                                }
                                else
                                {
                                        $extensions[] = mb_strtolower($value);
                                }
                        }
                        if (in_array($extension, $extensions))
                        {
                                if (!empty($this->attachtype))
                                {
                                        if ($this->attachtype == 'profile')
                                        {
                                                $fullpath = DIR_PROFILE_ATTACHMENTS;
                                                $this->maxwidth_default = $ilconfig['attachmentlimit_profilemaxwidth'];
                                                $this->maxheight_default = $ilconfig['attachmentlimit_profilemaxheight'];
                                                $this->maxwidth_full = $ilconfig['attachmentlimit_profilemaxwidth'];
                                                $this->maxheight_full = $ilconfig['attachmentlimit_profilemaxheight'];
                                                $this->maxwidth_mini = $ilconfig['attachmentlimit_productphotothumbwidth'];
                                                $this->maxheight_mini = $ilconfig['attachmentlimit_productphotothumbheight'];
                                                $this->maxwidth_search = $ilconfig['attachmentlimit_profilemaxwidth'];
                                                $this->maxheight_search = $ilconfig['attachmentlimit_profilemaxheight'];
                                                $this->maxwidth_gallery = $ilconfig['attachmentlimit_searchresultsgallerymaxwidth'];
                                                $this->maxheight_gallery = $ilconfig['attachmentlimit_searchresultsgallerymaxheight'];
                                                $this->maxwidth_snapshot = $ilconfig['attachmentlimit_searchresultssnapshotmaxwidth'];
                                                $this->maxheight_snapshot = $ilconfig['attachmentlimit_searchresultssnapshotmaxheight'];
                                        }
                                        else if ($this->attachtype == 'portfolio')
                                        {
                                                $fullpath = DIR_PORTFOLIO_ATTACHMENTS;
                                                $this->maxwidth_default = $ilconfig['attachmentlimit_portfoliothumbwidth'];
                                                $this->maxheight_default = $ilconfig['attachmentlimit_portfoliothumbheight'];
                                                $this->maxwidth_full = $ilconfig['attachmentlimit_productphotowidth'];
                                                $this->maxheight_full = $ilconfig['attachmentlimit_productphotoheight'];
                                                $this->maxwidth_mini = $ilconfig['attachmentlimit_productphotothumbwidth'];
                                                $this->maxheight_mini = $ilconfig['attachmentlimit_productphotothumbheight'];
                                                $this->maxwidth_search = $ilconfig['attachmentlimit_searchresultsmaxwidth'];
                                                $this->maxheight_search = $ilconfig['attachmentlimit_searchresultsmaxheight'];
                                                $this->maxwidth_gallery = $ilconfig['attachmentlimit_searchresultsgallerymaxwidth'];
                                                $this->maxheight_gallery = $ilconfig['attachmentlimit_searchresultsgallerymaxheight'];
                                                $this->maxwidth_snapshot = $ilconfig['attachmentlimit_searchresultssnapshotmaxwidth'];
                                                $this->maxheight_snapshot = $ilconfig['attachmentlimit_searchresultssnapshotmaxheight'];
                                        }
                                        else if ($this->attachtype == 'itemphoto' OR $this->attachtype == 'slideshow')
                                        {
                                                $fullpath = DIR_AUCTION_ATTACHMENTS;
                                                $this->maxwidth_default = $ilconfig['attachmentlimit_productphotomaxwidth'];
                                                $this->maxheight_default = $ilconfig['attachmentlimit_productphotomaxheight'];
                                                $this->maxwidth_full = $ilconfig['attachmentlimit_productphotowidth'];
                                                $this->maxheight_full = $ilconfig['attachmentlimit_productphotoheight'];
                                                $this->maxwidth_mini = $ilconfig['attachmentlimit_productphotothumbwidth'];
                                                $this->maxheight_mini = $ilconfig['attachmentlimit_productphotothumbheight'];
                                                $this->maxwidth_search = $ilconfig['attachmentlimit_searchresultsmaxwidth'];
                                                $this->maxheight_search = $ilconfig['attachmentlimit_searchresultsmaxheight'];
                                                $this->maxwidth_gallery = $ilconfig['attachmentlimit_searchresultsgallerymaxwidth'];
                                                $this->maxheight_gallery = $ilconfig['attachmentlimit_searchresultsgallerymaxheight'];
                                                $this->maxwidth_snapshot = $ilconfig['attachmentlimit_searchresultssnapshotmaxwidth'];
                                                $this->maxheight_snapshot = $ilconfig['attachmentlimit_searchresultssnapshotmaxheight'];
                                        }
                                        else if ($this->attachtype == 'project')
                                        {
                                                $fullpath = DIR_AUCTION_ATTACHMENTS;
                                                $this->maxwidth_default = $ilconfig['attachmentlimit_projectmaxwidth'];
                                                $this->maxheight_default = $ilconfig['attachmentlimit_projectmaxheight'];
                                                $this->maxwidth_full = $ilconfig['attachmentlimit_productphotowidth'];
                                                $this->maxheight_full = $ilconfig['attachmentlimit_productphotoheight'];
                                                $this->maxwidth_mini = $ilconfig['attachmentlimit_productphotothumbwidth'];
                                                $this->maxheight_mini = $ilconfig['attachmentlimit_productphotothumbheight'];
                                                $this->maxwidth_search = $ilconfig['attachmentlimit_searchresultsmaxwidth'];
                                                $this->maxheight_search = $ilconfig['attachmentlimit_searchresultsmaxheight'];
                                                $this->maxwidth_gallery = $ilconfig['attachmentlimit_searchresultsgallerymaxwidth'];
                                                $this->maxheight_gallery = $ilconfig['attachmentlimit_searchresultsgallerymaxheight'];
                                                $this->maxwidth_snapshot = $ilconfig['attachmentlimit_searchresultssnapshotmaxwidth'];
                                                $this->maxheight_snapshot = $ilconfig['attachmentlimit_searchresultssnapshotmaxheight'];
                                        }
                                        else if ($this->attachtype == 'bid')
                                        {
                                                $fullpath = DIR_BID_ATTACHMENTS;
                                                $this->maxwidth_default = $ilconfig['attachmentlimit_bidmaxwidth'];
                                                $this->maxheight_default = $ilconfig['attachmentlimit_bidmaxheight'];
                                                $this->maxwidth_full = $ilconfig['attachmentlimit_productphotowidth'];
                                                $this->maxheight_full = $ilconfig['attachmentlimit_productphotoheight'];
                                                $this->maxwidth_mini = $ilconfig['attachmentlimit_productphotothumbwidth'];
                                                $this->maxheight_mini = $ilconfig['attachmentlimit_productphotothumbheight'];
                                                $this->maxwidth_search = $ilconfig['attachmentlimit_searchresultsmaxwidth'];
                                                $this->maxheight_search = $ilconfig['attachmentlimit_searchresultsmaxheight'];
                                                $this->maxwidth_gallery = $ilconfig['attachmentlimit_searchresultsgallerymaxwidth'];
                                                $this->maxheight_gallery = $ilconfig['attachmentlimit_searchresultsgallerymaxheight'];
                                                $this->maxwidth_snapshot = $ilconfig['attachmentlimit_searchresultssnapshotmaxwidth'];
                                                $this->maxheight_snapshot = $ilconfig['attachmentlimit_searchresultssnapshotmaxheight'];
                                        }
                                        else if ($this->attachtype == 'pmb')
                                        {
                                                $fullpath = DIR_PMB_ATTACHMENTS;
                                                $this->maxwidth_default = $ilconfig['attachmentlimit_pmbmaxwidth'];
                                                $this->maxheight_default = $ilconfig['attachmentlimit_pmbmaxheight'];
                                                $this->maxwidth_full = $ilconfig['attachmentlimit_productphotowidth'];
                                                $this->maxheight_full = $ilconfig['attachmentlimit_productphotoheight'];
                                                $this->maxwidth_mini = $ilconfig['attachmentlimit_productphotothumbwidth'];
                                                $this->maxheight_mini = $ilconfig['attachmentlimit_productphotothumbheight'];
                                                $this->maxwidth_search = $ilconfig['attachmentlimit_searchresultsmaxwidth'];
                                                $this->maxheight_search = $ilconfig['attachmentlimit_searchresultsmaxheight'];
                                                $this->maxwidth_gallery = $ilconfig['attachmentlimit_searchresultsgallerymaxwidth'];
                                                $this->maxheight_gallery = $ilconfig['attachmentlimit_searchresultsgallerymaxheight'];
                                                $this->maxwidth_snapshot = $ilconfig['attachmentlimit_searchresultssnapshotmaxwidth'];
                                                $this->maxheight_snapshot = $ilconfig['attachmentlimit_searchresultssnapshotmaxheight'];
                                        }
                                        else if ($this->attachtype == 'ws')
                                        {
                                                $fullpath = DIR_WS_ATTACHMENTS;
                                                $this->maxwidth_default = $ilconfig['attachmentlimit_mediasharemaxwidth'];
                                                $this->maxheight_default = $ilconfig['attachmentlimit_mediasharemaxheight'];
                                                $this->maxwidth_full = $ilconfig['attachmentlimit_productphotowidth'];
                                                $this->maxheight_full = $ilconfig['attachmentlimit_productphotoheight'];
                                                $this->maxwidth_mini = $ilconfig['attachmentlimit_productphotothumbwidth'];
                                                $this->maxheight_mini = $ilconfig['attachmentlimit_productphotothumbheight'];
                                                $this->maxwidth_search = $ilconfig['attachmentlimit_searchresultsmaxwidth'];
                                                $this->maxheight_search = $ilconfig['attachmentlimit_searchresultsmaxheight'];
                                                $this->maxwidth_gallery = $ilconfig['attachmentlimit_searchresultsgallerymaxwidth'];
                                                $this->maxheight_gallery = $ilconfig['attachmentlimit_searchresultsgallerymaxheight'];
                                                $this->maxwidth_snapshot = $ilconfig['attachmentlimit_searchresultssnapshotmaxwidth'];
                                                $this->maxheight_snapshot = $ilconfig['attachmentlimit_searchresultssnapshotmaxheight'];
                                        }
                                        else if ($this->attachtype == 'digital')
                                        {
                                                $fullpath = DIR_AUCTION_ATTACHMENTS;
                                                $this->maxwidth_default = $this->maxheight_default = $this->maxwidth_full = $this->maxheight_full = $this->maxwidth_mini = $this->maxheight_mini = $this->maxwidth_search = $this->maxheight_search = $this->maxwidth_gallery = $this->maxheight_gallery = $this->maxwidth_snapshot = $this->maxheight_snapshot = '';
                                        }
                                        else
                                        {
                                                ($apihook = $ilance->api('attachment_validate_size_else_end')) ? eval($apihook) : false;
                                        }
                                }
                                if (!in_array($this->filetype, $this->mimetypes)) // ensure mime of uploaded file is actual image formats we accept
                                {
                                        // might be zipfile so we only want uploaded file nothing more.. reset everything
                                        $this->filedata_original = $this->filedata_full = $this->filedata_mini = $this->filedata_search = $this->filedata_gallery = $this->filedata_snapshot = '';
                                        $this->filesize_original = $this->filesize_full = $this->filesize_mini = $this->filesize_search = $this->filesize_gallery = $this->filesize_snapshot = 0;
                                        $this->width_original = $this->width_full = $this->width_mini = $this->width_search = $this->width_gallery = $this->width_snapshot = $this->width = 0;
                                        $this->height_original = $this->height_full = $this->height_mini = $this->height_search = $this->height_gallery = $this->height_snapshot = $this->height = 0;
                                }
                                else
                                {
                                        if ($fileinfo = @getimagesize($this->temp_file_name)) // original uploaded file details
                                        {
                                                $rawdata = @fread(@fopen($this->temp_file_name, 'rb'), @filesize($this->temp_file_name)); // should be replaced with file_get_contents().. ?
                                                $newfilename = $fullpath . $filehash . '.attach'; // ILance 4.x backward compatability
                                                $newfilename_original = $fullpath . 'original/' . $filehash . '.attach';
                                                $newfilename_full = $fullpath . 'resized/full/' . $filehash . '.attach';
                                                $newfilename_mini = $fullpath . 'resized/mini/' . $filehash . '.attach';
                                                $newfilename_search = $fullpath . 'resized/search/' . $filehash . '.attach';
                                                $newfilename_gallery = $fullpath . 'resized/gallery/' . $filehash . '.attach';
                                                $newfilename_snapshot = $fullpath . 'resized/snapshot/' . $filehash . '.attach';
                                                if (move_uploaded_file($this->temp_file_name, $newfilename_original))
                                                {
                                                        // set all width's and heights the same (based on original picture uploaded)..
                                                        $this->width_original = $this->width_full = $this->width_mini = $this->width_search = $this->width_gallery = $this->width_snapshot = $this->width = $fileinfo[0];
                                                        $this->height_original = $this->height_full = $this->height_mini = $this->height_search = $this->height_gallery = $this->height_snapshot = $this->height = $fileinfo[1];
                                                        $this->filetype_original = empty($fileinfo['mime']) ? $this->filetype : $fileinfo['mime'];
                                                        // make duplicates of the original and save into respective folders..
                                                        if (file_exists($newfilename))
                                                        {
                                                                @unlink($newfilename);
                                                        }
                                                        if ($fp2 = fopen($newfilename, 'wb'))
                                                        {
                                                                fwrite($fp2, $rawdata);
                                                                fclose($fp2);
                                                        }
                                                        if (file_exists($newfilename_full))
                                                        {
                                                                @unlink($newfilename_full);
                                                        }
                                                        if ($fp3 = fopen($newfilename_full, 'wb'))
                                                        {
                                                                fwrite($fp3, $rawdata);
                                                                fclose($fp3);
                                                        }
                                                        if (file_exists($newfilename_mini))
                                                        {
                                                                @unlink($newfilename_mini);
                                                        }
                                                        if ($fp4 = fopen($newfilename_mini, 'wb'))
                                                        {
                                                                fwrite($fp4, $rawdata);
                                                                fclose($fp4);
                                                        }
                                                        if (file_exists($newfilename_search))
                                                        {
                                                                @unlink($newfilename_search);
                                                        }
                                                        if ($fp5 = fopen($newfilename_search, 'wb'))
                                                        {
                                                                fwrite($fp5, $rawdata);
                                                                fclose($fp5);
                                                        }
                                                        if (file_exists($newfilename_gallery))
                                                        {
                                                                @unlink($newfilename_gallery);
                                                        }
                                                        if ($fp6 = fopen($newfilename_gallery, 'wb'))
                                                        {
                                                                fwrite($fp6, $rawdata);
                                                                fclose($fp6);
                                                        }
                                                        if (file_exists($newfilename_snapshot))
                                                        {
                                                                @unlink($newfilename_snapshot);
                                                        }
                                                        if ($fp7 = fopen($newfilename_snapshot, 'wb'))
                                                        {
                                                                fwrite($fp7, $rawdata);
                                                                fclose($fp7);
                                                        }
                                                        unset($rawdata);
                                                        // fetch exif information (extended image support) before we resize based on original picture
                                                        if (function_exists('exif_read_data'))
                                                        {
                                                                $exifdata = @exif_read_data($newfilename_original, 0, true);
                                                                if (!empty($exifdata))
                                                                {
                                                                        $this->exif = serialize($exifdata);
                                                                }
                                                                unset($exifdata);
                                                        }
                                                        $fileinfo = getimagesize($newfilename_original);
                                                        if (!empty($fileinfo) AND is_array($fileinfo))
                                                        {
                                                                if ($fileinfo[0] > $this->maxwidth_default OR $fileinfo[1] > $this->maxheight_default)
                                                                {
                                                                        $this->picture_resizer($newfilename, $this->maxwidth_default, $this->maxheight_default, $extension, $fileinfo[0], $fileinfo[1], '');
                                                                        if ($this->pictureresized)
                                                                        {
                                                                                $this->width_default = $this->width;
                                                                                $this->height_default = $this->height;
                                                                                $this->filesize = @filesize($newfilename);
                                                                                $this->filedata = @fread(@fopen($newfilename, 'rb'), @filesize($newfilename));
                                                                        }
                                                                }
                                                                if ($fileinfo[0] > $this->maxwidth_full OR $fileinfo[1] > $this->maxheight_full)
                                                                {
                                                                        $this->picture_resizer($newfilename_full, $this->maxwidth_full, $this->maxheight_full, $extension, $fileinfo[0], $fileinfo[1], '');
                                                                        $this->watermark($uncrypted['attachtype'], $newfilename_full, $extension, '');
                                                                        if ($this->pictureresized)
                                                                        {
                                                                                $this->width_full = $this->width;
                                                                                $this->height_full = $this->height;
                                                                                $this->filesize_full = @filesize($newfilename_full);
                                                                                $this->filedata_full = @fread(@fopen($newfilename_full, 'rb'), @filesize($newfilename_full));
                                                                        }
                                                                }
                                                                if ($fileinfo[0] > $this->maxwidth_mini OR $fileinfo[1] > $this->maxheight_mini)
                                                                {
                                                                        $this->picture_resizer($newfilename_mini, $this->maxwidth_mini, $this->maxheight_mini, $extension, $fileinfo[0], $fileinfo[1], '');
                                                                        if ($this->pictureresized)
                                                                        {
                                                                                $this->width_mini = $this->width;
                                                                                $this->height_mini = $this->height;
                                                                                $this->filesize_mini = @filesize($newfilename_mini);
                                                                                $this->filedata_mini = @fread(@fopen($newfilename_mini, 'rb'), @filesize($newfilename_mini));
                                                                        }
                                                                }
                                                                if ($fileinfo[0] > $this->maxwidth_search OR $fileinfo[1] > $this->maxheight_search)
                                                                {
                                                                        $this->picture_resizer($newfilename_search, $this->maxwidth_search, $this->maxheight_search, $extension, $fileinfo[0], $fileinfo[1], '');
                                                                        if ($this->pictureresized)
                                                                        {
                                                                                $this->width_search = $this->width;
                                                                                $this->height_search = $this->height;
                                                                                $this->filesize_search = @filesize($newfilename_search);
                                                                                $this->filedata_search = @fread(@fopen($newfilename_search, 'rb'), @filesize($newfilename_search));
                                                                        }
                                                                }
                                                                if ($fileinfo[0] > $this->maxwidth_gallery OR $fileinfo[1] > $this->maxheight_gallery)
                                                                {
                                                                        $this->picture_resizer($newfilename_gallery, $this->maxwidth_gallery, $this->maxheight_gallery, $extension, $fileinfo[0], $fileinfo[1], '');
                                                                        if ($this->pictureresized)
                                                                        {
                                                                                $this->width_gallery = $this->width;
                                                                                $this->height_gallery = $this->height;
                                                                                $this->filesize_gallery = @filesize($newfilename_gallery);
                                                                                $this->filedata_gallery = @fread(@fopen($newfilename_gallery, 'rb'), @filesize($newfilename_gallery));
                                                                        }
                                                                }
                                                                if ($fileinfo[0] > $this->maxwidth_snapshot OR $fileinfo[1] > $this->maxheight_snapshot)
                                                                {
                                                                        $this->picture_resizer($newfilename_snapshot, $this->maxwidth_snapshot, $this->maxheight_snapshot, $extension, $fileinfo[0], $fileinfo[1], '');
                                                                        if ($this->pictureresized)
                                                                        {
                                                                                $this->width_snapshot = $this->width;
                                                                                $this->height_snapshot = $this->height;
                                                                                $this->filesize_snapshot = @filesize($newfilename_snapshot);
                                                                                $this->filedata_snapshot = @fread(@fopen($newfilename_snapshot, 'rb'), @filesize($newfilename_snapshot));
                                                                        }
                                                                }
                                                        }
                                                }
                                        }
                                        if (isset($this->pictureresized) AND $this->pictureresized)
                                        {
                                                $this->filesize = @filesize($newfilename);
                                        }
                                }
                                // ensure the filesize of uploaded image is still lower than our acceptable uploaded file size defined by admin..
                                if ($this->filesize > $this->max_file_size) 
                                {
                                        $valid_filesize = false;
                                        if (file_exists($newfilename))
                                        {
                                                @unlink($newfilename);
                                        }
                                        if (file_exists($newfilename_original))
                                        {
                                                @unlink($newfilename_original);
                                        }
                                        if (file_exists($newfilename_full))
                                        {
                                                @unlink($newfilename_full);
                                        }
                                        if (file_exists($newfilename_mini))
                                        {
                                                @unlink($newfilename_mini);
                                        }
                                        if (file_exists($newfilename_search))
                                        {
                                                @unlink($newfilename_search);
                                        }
                                        if (file_exists($newfilename_gallery))
                                        {
                                                @unlink($newfilename_gallery);
                                        }
                                        if (file_exists($newfilename_snapshot))
                                        {
                                                @unlink($newfilename_snapshot);
                                        }
                                }
                                if (empty($this->filedata_original)) 
                                { // non image type file (zip file perhaps..)
                                        if ($valid_filesize)
                                        {
                                                return array(
                                                        'success' => '1',
                                                        'badwidth' => '0',
                                                        'badheight' => '0',
                                                        'uploadwidth' => '0',
                                                        'uploadheight' => '0',
                                                        'failedextension' => '0',
                                                        'failedfilesize' => '0',
                                                        'uploadfilesize' => $this->filesize,
                                                        'uploadfiletype' => $this->filetype,
                                                        'uploadfilename' => $this->file_name,
                                                        'uploadfilename_original' => '',
                                                        'uploadfiletype_original' => '',
                                                        'uploadwidth_original' => '0',
                                                        'uploadheight_original' => '0',
                                                        'uploadwidth_full' => '0',
                                                        'uploadheight_full' => '0',
                                                        'uploadwidth_mini' => '0',
                                                        'uploadheight_mini' => '0',
                                                        'uploadwidth_search' => '0',
                                                        'uploadheight_search' => '0',
                                                        'uploadwidth_gallery' => '0',
                                                        'uploadheight_gallery' => '0',
                                                        'uploadwidth_snapshot' => '0',
                                                        'uploadheight_snapshot' => '0',
                                                        'uploadfilesize_original' => '0',
                                                        'uploadfilesize_full' => '0',
                                                        'uploadfilesize_mini' => '0',
                                                        'uploadfilesize_search' => '0',
                                                        'uploadfilesize_gallery' => '0',
                                                        'uploadfilesize_snapshot' => '0',
                                                        'filedata' => $this->filedata,
                                                        'filedata_original' => '',
                                                        'filedata_full' => '',
                                                        'filedata_mini' => '',
                                                        'filedata_search' => '',
                                                        'filedata_gallery' => '',
                                                        'filedata_snapshot' => '',
                                                        'filehash' => $filehash
                                                );
                                        }
                                        else
                                        {
                                                return array(
                                                        'success' => '0',
                                                        'badwidth' => '0',
                                                        'badheight' => '0',
                                                        'uploadwidth' => '0',
                                                        'uploadheight' => '0',
                                                        'failedextension' => '0',
                                                        'failedfilesize' => $this->filesize,
                                                        'uploadfilesize' => $this->filesize,
                                                        'uploadfiletype' => $this->filetype,
                                                        'uploadfilename' => $this->file_name,
                                                        'uploadfilename_original' => '',
                                                        'uploadfiletype_original' => '',
                                                        'uploadwidth_original' => '0',
                                                        'uploadheight_original' => '0',
                                                        'uploadwidth_full' => '0',
                                                        'uploadheight_full' => '0',
                                                        'uploadwidth_mini' => '0',
                                                        'uploadheight_mini' => '0',
                                                        'uploadwidth_search' => '0',
                                                        'uploadheight_search' => '0',
                                                        'uploadwidth_gallery' => '0',
                                                        'uploadheight_gallery' => '0',
                                                        'uploadwidth_snapshot' => '0',
                                                        'uploadheight_snapshot' => '0',
                                                        'uploadfilesize_original' => '0',
                                                        'uploadfilesize_full' => '0',
                                                        'uploadfilesize_mini' => '0',
                                                        'uploadfilesize_search' => '0',
                                                        'uploadfilesize_gallery' => '0',
                                                        'uploadfilesize_snapshot' => '0',
                                                        'filedata' => '',
                                                        'filedata_original' => '',
                                                        'filedata_full' => '',
                                                        'filedata_mini' => '',
                                                        'filedata_search' => '',
                                                        'filedata_gallery' => '',
                                                        'filedata_snapshot' => '',
                                                        'filehash' => $filehash
                                                );
                                        }
                                }
                                else
                                {
                                        $valid_extension_w = $valid_extension_h = true; // we want the biggest possible picture to resize!!
                                        if (!$fileinfo = @getimagesize($newfilename_original))
                                        {
                                                $valid_extension_w = $valid_extension_h = false; // we couldn't get width or height from original uploaded image..!
                                                if (isset($newfilename) AND file_exists($newfilename))
                                                {
                                                        @unlink($newfilename);
                                                }
                                                if (isset($newfilename_original) AND file_exists($newfilename_original))
                                                {
                                                        @unlink($newfilename_original);
                                                }
                                                if (isset($newfilename_full) AND file_exists($newfilename_full))
                                                {
                                                        @unlink($newfilename_full);
                                                }
                                                if (isset($newfilename_mini) AND file_exists($newfilename_mini))
                                                {
                                                        @unlink($newfilename_mini);
                                                }
                                                if (isset($newfilename_search) AND file_exists($newfilename_search))
                                                {
                                                        @unlink($newfilename_search);
                                                }
                                                if (isset($newfilename_gallery) AND file_exists($newfilename_gallery))
                                                {
                                                        @unlink($newfilename_gallery);
                                                }
                                                if (isset($newfilename_snapshot) AND file_exists($newfilename_snapshot))
                                                {
                                                        @unlink($newfilename_snapshot);
                                                }
                                        }
                                        if ($valid_extension_w AND $valid_extension_h AND $valid_filesize)
                                        {
                                                return array(
                                                        'success' => '1',
                                                        'badwidth' => '0',
                                                        'badheight' => '0',
                                                        'failedextension' => '0',
                                                        'failedfilesize' => '0',
                                                        'uploadwidth' => $fileinfo[0],
                                                        'uploadheight' => $fileinfo[1],
                                                        'uploadfilesize' => $this->filesize,
                                                        'uploadfiletype' => $this->filetype,
                                                        'uploadfilename' => $this->file_name,
                                                        'uploadfilename_original' => $this->file_name_original,
                                                        'uploadfiletype_original' => $this->filetype_original,
                                                        'uploadwidth_original' => $this->width_original,
                                                        'uploadheight_original' => $this->height_original,
                                                        'uploadwidth_full' => $this->width_full,
                                                        'uploadheight_full' => $this->height_full,
                                                        'uploadwidth_mini' => $this->width_mini,
                                                        'uploadheight_mini' => $this->height_mini,
                                                        'uploadwidth_search' => $this->width_search,
                                                        'uploadheight_search' => $this->height_search,
                                                        'uploadwidth_gallery' => $this->width_gallery,
                                                        'uploadheight_gallery' => $this->height_gallery,
                                                        'uploadwidth_snapshot' => $this->width_snapshot,
                                                        'uploadheight_snapshot' => $this->height_snapshot,
                                                        'uploadfilesize_original' => $this->filesize_original,
                                                        'uploadfilesize_full' => $this->filesize_full,
                                                        'uploadfilesize_mini' => $this->filesize_mini,
                                                        'uploadfilesize_search' => $this->filesize_search,
                                                        'uploadfilesize_gallery' => $this->filesize_gallery,
                                                        'uploadfilesize_snapshot' => $this->filesize_snapshot,
                                                        'filedata' => $this->filedata,
                                                        'filedata_original' => $this->filedata_original,
                                                        'filedata_full' => $this->filedata_full,
                                                        'filedata_mini' => $this->filedata_mini,
                                                        'filedata_search' => $this->filedata_search,
                                                        'filedata_gallery' => $this->filedata_gallery,
                                                        'filedata_snapshot' => $this->filedata_snapshot,
                                                        'filehash' => $filehash,
                                                        'newfilename' => $newfilename, // ILance 4.0.0 backward compatability
                                                        'newfilename_original' => $newfilename_original,
                                                        'newfilename_full' => $newfilename_full,
                                                        'newfilename_mini' => $newfilename_mini,
                                                        'newfilename_search' => $newfilename_search,
                                                        'newfilename_gallery' => $newfilename_gallery,
                                                        'newfilename_snapshot' => $newfilename_snapshot
                                                );
                                        }
                                        else
                                        {
                                                $failedwidth = $failedheight = $failedfilesize = '0';
                                                if ($valid_extension_w == false)
                                                {
                                                        $failedwidth = '1';
                                                }
                                                if ($valid_extension_h == false)
                                                {
                                                        $failedheight = '1';
                                                }
                                                if ($valid_filesize == false)
                                                {
                                                        $failedfilesize = '1';
                                                }
                                                return array(
                                                        'success' => '0',
                                                        'failedwidth' => $failedwidth,
                                                        'failedheight' => $failedheight,
                                                        'failedfilesize' => $failedfilesize,
                                                        'failedextension' => '0',
                                                        'uploadwidth' => $fileinfo[0],
                                                        'uploadheight' => $fileinfo[1],
                                                        'uploadfilesize' => $this->filesize,
                                                        'uploadfiletype' => $this->filetype,
                                                        'uploadfilename' => $this->file_name,
                                                        'uploadfilename_original' => '',
                                                        'uploadfiletype_original' => '',
                                                        'uploadwidth_original' => '0',
                                                        'uploadheight_original' => '0',
                                                        'uploadwidth_full' => '0',
                                                        'uploadheight_full' => '0',
                                                        'uploadwidth_mini' => '0',
                                                        'uploadheight_mini' => '0',
                                                        'uploadwidth_search' => '0',
                                                        'uploadheight_search' => '0',
                                                        'uploadwidth_gallery' => '0',
                                                        'uploadheight_gallery' => '0',
                                                        'uploadwidth_snapshot' => '0',
                                                        'uploadheight_snapshot' => '0',
                                                        'uploadfilesize_original' => '0',
                                                        'uploadfilesize_full' => '0',
                                                        'uploadfilesize_mini' => '0',
                                                        'uploadfilesize_search' => '0',
                                                        'uploadfilesize_gallery' => '0',
                                                        'uploadfilesize_snapshot' => '0',
                                                        'filedata' => '',
                                                        'filedata_original' => '',
                                                        'filedata_full' => '',
                                                        'filedata_mini' => '',
                                                        'filedata_search' => '',
                                                        'filedata_gallery' => '',
                                                        'filedata_snapshot' => '',
                                                        'filehash' => $filehash
                                                );
                                        }
                                }
                        }
                        else
                        {
                                return array(
                                        'success' => '0',
                                        'failedwidth' => '0',
                                        'failedheight' => '0',
                                        'failedfilesize' => '0',
                                        'failedextension' => '1',
                                        'uploadwidth' => '0',
                                        'uploadheight' => '0',
                                        'uploadfilesize' => $this->filesize,
                                        'uploadfiletype' => $this->filetype,
                                        'uploadfilename' => $this->file_name,
                                        'uploadfilename_original' => $this->file_name_original,
                                        'uploadfiletype_original' => $this->filetype_original,
                                        'uploadwidth_original' => '0',
                                        'uploadheight_original' => '0',
                                        'uploadwidth_full' => '0',
                                        'uploadheight_full' => '0',
                                        'uploadwidth_mini' => '0',
                                        'uploadheight_mini' => '0',
                                        'uploadwidth_search' => '0',
                                        'uploadheight_search' => '0',
                                        'uploadwidth_gallery' => '0',
                                        'uploadheight_gallery' => '0',
                                        'uploadwidth_snapshot' => '0',
                                        'uploadheight_snapshot' => '0',
                                        'uploadfilesize_original' => '0',
                                        'uploadfilesize_full' => '0',
                                        'uploadfilesize_mini' => '0',
                                        'uploadfilesize_search' => '0',
                                        'uploadfilesize_gallery' => '0',
                                        'uploadfilesize_snapshot' => '0',
                                        'filedata' => '',
                                        'filedata_original' => '',
                                        'filedata_full' => '',
                                        'filedata_mini' => '',
                                        'filedata_search' => '',
                                        'filedata_gallery' => '',
                                        'filedata_snapshot' => '',
                                        'filehash' => $filehash
                                );
                        }
                }
                else
                {
                        return array(
                                'success' => '0',
                                'failedwidth' => '1',
                                'failedheight' => '1',
                                'failedfilesize' => '1',
                                'failedextension' => '1',
                                'uploadwidth' => '0',
                                'uploadheight' => '0',
                                'uploadfilesize' => '0',
                                'uploadfiletype' => $this->filetype,
                                'uploadfilename' => $this->file_name,
                                'uploadfilename_original' => '',
                                'uploadfiletype_original' => '',
                                'uploadwidth_original' => '0',
                                'uploadheight_original' => '0',
                                'uploadwidth_full' => '0',
                                'uploadheight_full' => '0',
                                'uploadwidth_mini' => '0',
                                'uploadheight_mini' => '0',
                                'uploadwidth_search' => '0',
                                'uploadheight_search' => '0',
                                'uploadwidth_gallery' => '0',
                                'uploadheight_gallery' => '0',
                                'uploadwidth_snapshot' => '0',
                                'uploadheight_snapshot' => '0',
                                'uploadfilesize_original' => '0',
                                'uploadfilesize_full' => '0',
                                'uploadfilesize_mini' => '0',
                                'uploadfilesize_search' => '0',
                                'uploadfilesize_gallery' => '0',
                                'uploadfilesize_snapshot' => '0',
                                'filedata' => '',
                                'filedata_original' => '',
                                'filedata_full' => '',
                                'filedata_mini' => '',
                                'filedata_search' => '',
                                'filedata_gallery' => '',
                                'filedata_snapshot' => '',
                                'filehash' => $filehash
                        );
                }
        }
        
        /**
        * Function to save the uploaded file attachment to the file system or database
        *
        * @return      boolean      true or false based on successful attachment upload
        */
        function save_attachment($valid_size = array())
        {
                global $ilance, $ilconfig, $uncrypted, $show, $phrase, $ilpage;
                $upload_dir = $this->get_upload_directory();
                
                ($apihook = $ilance->api('save_attachment_start')) ? eval($apihook) : false;

                if ($upload_dir == 'ERROR' OR $valid_size['success'] == '0' OR $this->validate_extension() == false)
                {
                        return false;
                }
                $uncrypted['user_id'] = isset($uncrypted['user_id']) ? $uncrypted['user_id'] : $this->user_id;
		$uncrypted['attachtype'] = isset($uncrypted['attachtype']) ? $uncrypted['attachtype'] : $this->attachtype;
                $valid_size['uploadfilename'] = $valid_size['uploadfilename_original'] = trim(mb_strtolower($valid_size['uploadfilename']));
                if (!empty($valid_size['filedata']))
                {
                        $newfilename = $upload_dir . $valid_size['filehash'] . '.attach';
                        if (empty($valid_size['uploadfilesize']))
                        {
                                $valid_size['uploadfilesize'] = filesize($newfilename);    
                        }
                        if ($ilconfig['attachment_dbstorage'])
                        {
                                if (file_exists($newfilename))
                                {
                                        @unlink($newfilename);
                                }
                        }
                        else
                        {
                                $valid_size['filedata'] = '';
                        }
                }
                if (!empty($valid_size['filedata_original']))
                {
                        $newfilename_original = $upload_dir . 'original/' . $valid_size['filehash'] . '.attach';
                        if (empty($valid_size['uploadfilesize_original']))
                        {
                                $valid_size['uploadfilesize_original'] = filesize($newfilename_original);    
                        }
                        if ($ilconfig['attachment_dbstorage'])
                        {
                                if (file_exists($newfilename_original))
                                {
                                        @unlink($newfilename_original);
                                }
                        }
                        else
                        {
                                $valid_size['filedata_original'] = '';
                        }
                }
                if (!empty($valid_size['filedata_full']))
                {
                        $newfilename_full = $upload_dir . 'resized/full/' . $valid_size['filehash'] . '.attach';
                        if (empty($valid_size['uploadfilesize_full']))
                        {
                                $valid_size['uploadfilesize_full'] = filesize($newfilename_full);    
                        }
                        if ($ilconfig['attachment_dbstorage'])
                        {
                                if (file_exists($newfilename_full))
                                {
                                        @unlink($newfilename_full);
                                }
                        }
                        else
                        {
                                $valid_size['filedata_full'] = '';
                        }
                }
                if (!empty($valid_size['filedata_gallery']))
                {
                        $newfilename_gallery = $upload_dir . 'resized/gallery/' . $valid_size['filehash'] . '.attach';
                        if (empty($valid_size['uploadfilesize_gallery']))
                        {
                                $valid_size['uploadfilesize_gallery'] = filesize($newfilename_gallery);    
                        }
                        if ($ilconfig['attachment_dbstorage'])
                        {
                                if (file_exists($newfilename_gallery))
                                {
                                        @unlink($newfilename_gallery);
                                }
                        }
                        else
                        {
                                $valid_size['filedata_gallery'] = '';
                        }
                }
                if (!empty($valid_size['filedata_mini']))
                {
                        $newfilename_mini = $upload_dir . 'resized/mini/' . $valid_size['filehash'] . '.attach';
                        if (empty($valid_size['uploadfilesize_mini']))
                        {
                                $valid_size['uploadfilesize_mini'] = filesize($newfilename_mini);    
                        }
                        if ($ilconfig['attachment_dbstorage'])
                        {
                                if (file_exists($newfilename_mini))
                                {
                                        @unlink($newfilename_mini);
                                }
                        }
                        else
                        {
                                $valid_size['filedata_mini'] = '';
                        }
                }
                if (!empty($valid_size['filedata_search']))
                {
                        $newfilename_search = $upload_dir . 'resized/search/' . $valid_size['filehash'] . '.attach';
                        if (empty($valid_size['uploadfilesize_search']))
                        {
                                $valid_size['uploadfilesize_search'] = filesize($newfilename_search);    
                        }
                        if ($ilconfig['attachment_dbstorage'])
                        {
                                if (file_exists($newfilename_search))
                                {
                                        @unlink($newfilename_search);
                                }
                        }
                        else
                        {
                                $valid_size['filedata_search'] = '';
                        }
                }
                if (!empty($valid_size['filedata_snapshot']))
                {
                        $newfilename_snapshot = $upload_dir . 'resized/snapshot/' . $valid_size['filehash'] . '.attach';
                        if (empty($valid_size['uploadfilesize_snapshot']))
                        {
                                $valid_size['uploadfilesize_snapshot'] = filesize($newfilename_snapshot);    
                        }
                        if ($ilconfig['attachment_dbstorage'])
                        {
                                if (file_exists($newfilename_snapshot))
                                {
                                        @unlink($newfilename_snapshot);
                                }
                        }
                        else
                        {
                                $valid_size['filedata_snapshot'] = '';
                        }
                }
                
                // #### if we have portfolio upload, upload first then assign the attachid
                if ($uncrypted['attachtype'] == 'portfolio')
                {
                        ($apihook = $ilance->api('save_attachment_portfolio_start')) ? eval($apihook) : false;

                        $catid = (isset($ilance->GPC['cid'])) ? $ilance->GPC['cid'] : 0;
                        $ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "portfolio
                                (portfolio_id, user_id, caption, description, category_id, featured, visible)
                                VALUES(
                                NULL,
                                '" . $uncrypted['user_id'] . "',
                                '" . $ilance->db->escape_string(ilance_htmlentities($ilance->GPC['caption'])) . "',
                                '" . $ilance->db->escape_string(ilance_htmlentities($ilance->GPC['description'])) . "',
                                '" . intval($catid) . "',
                                '0',
                                '1')
                        ", 0, null, __FILE__, __LINE__);
                        $newattachid = $ilance->db->insert_id();
                        $ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "attachment
                                (attachid, attachtype, user_id, portfolio_id, category_id, date, filename, filedata, filedata_original, filedata_full, filedata_mini, filedata_search, filedata_gallery, filedata_snapshot, filetype, filetype_original, width, width_original, width_full, width_mini, width_search, width_gallery, width_snapshot, height, height_original, height_full, height_mini, height_search, height_gallery, height_snapshot, visible, counter, filesize, filesize_original, filesize_full, filesize_mini, filesize_search, filesize_gallery, filesize_snapshot, filehash, ipaddress, exifdata, watermarked)
                                VALUES(
                                NULL,
                                '" . $ilance->db->escape_string($uncrypted['attachtype']) . "',
                                '" . $uncrypted['user_id'] . "',
                                '" . intval($newattachid) . "',
                                '" . intval($catid) . "',
                                '" . DATETIME24H . "',
                                '" . $ilance->db->escape_string($valid_size['uploadfilename']) . "',
                                '" . $ilance->db->escape_string($valid_size['filedata']) . "',
                                '" . $ilance->db->escape_string($valid_size['filedata_original']) . "',
                                '" . $ilance->db->escape_string($valid_size['filedata_full']) . "',
                                '" . $ilance->db->escape_string($valid_size['filedata_mini']) . "',
                                '" . $ilance->db->escape_string($valid_size['filedata_search']) . "',
                                '" . $ilance->db->escape_string($valid_size['filedata_gallery']) . "',
                                '" . $ilance->db->escape_string($valid_size['filedata_snapshot']) . "',
                                '" . $ilance->db->escape_string($valid_size['uploadfiletype']) . "',
                                '" . $ilance->db->escape_string($valid_size['uploadfiletype_original']) . "',
                                '" . intval($valid_size['uploadwidth']) . "',
                                '" . intval($valid_size['uploadwidth_original']) . "',
                                '" . intval($valid_size['uploadwidth_full']) . "',
                                '" . intval($valid_size['uploadwidth_mini']) . "',
                                '" . intval($valid_size['uploadwidth_search']) . "',
                                '" . intval($valid_size['uploadwidth_gallery']) . "',
                                '" . intval($valid_size['uploadwidth_snapshot']) . "',
                                '" . intval($valid_size['uploadheight']) . "',
                                '" . intval($valid_size['uploadheight_original']) . "',
                                '" . intval($valid_size['uploadheight_full']) . "',
                                '" . intval($valid_size['uploadheight_mini']) . "',
                                '" . intval($valid_size['uploadheight_search']) . "',
                                '" . intval($valid_size['uploadheight_gallery']) . "',
                                '" . intval($valid_size['uploadheight_snapshot']) . "',
                                '" . intval($ilconfig['attachment_moderationdisabled']) . "',
                                '0',
                                '" . $ilance->db->escape_string($valid_size['uploadfilesize']) . "',
                                '" . $ilance->db->escape_string($valid_size['uploadfilesize_original']) . "',
                                '" . $ilance->db->escape_string($valid_size['uploadfilesize_full']) . "',
                                '" . $ilance->db->escape_string($valid_size['uploadfilesize_mini']) . "',
                                '" . $ilance->db->escape_string($valid_size['uploadfilesize_search']) . "',
                                '" . $ilance->db->escape_string($valid_size['uploadfilesize_gallery']) . "',
                                '" . $ilance->db->escape_string($valid_size['uploadfilesize_snapshot']) . "',
                                '" . $ilance->db->escape_string($valid_size['filehash']) . "',
                                '" . $ilance->db->escape_string(IPADDRESS) . "',
                                '" . $ilance->db->escape_string($this->exif) . "',
                                '" . intval($this->watermarked) . "')
                        ", 0, null, __FILE__, __LINE__);
                        $newattachid = $ilance->db->insert_id();

                        ($apihook = $ilance->api('save_attachment_portfolio_end')) ? eval($apihook) : false;
                }
                // #### regular attachment upload ##############################
                else
                {
                        ($apihook = $ilance->api('save_attachment_else_start')) ? eval($apihook) : false;

                        $uncrypted['portfolio_id'] = isset($uncrypted['portfolio_id']) ? $uncrypted['portfolio_id'] : $this->portfolio_id;
                        $uncrypted['project_id'] = isset($uncrypted['project_id']) ? $uncrypted['project_id'] : $this->project_id;
                        $uncrypted['pmb_id'] = isset($uncrypted['pmb_id']) ? $uncrypted['pmb_id'] : 0;
                        $uncrypted['ads_id'] = isset($uncrypted['ads_id']) ? $uncrypted['ads_id'] : 0;
                        $uncrypted['category_id'] = isset($uncrypted['category_id']) ? (int)$uncrypted['category_id'] : $this->category_id;
                        // #### is admin uploading or managing auction attachments via admincp?
                        if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1' AND defined('LOCATION') AND LOCATION == 'admin')
                        {
                                ($apihook = $ilance->api('save_attachment_admin_user_start')) ? eval($apihook) : false;
                                                                                
                                $ilance->db->query("
                                        INSERT INTO " . DB_PREFIX . "attachment
                                        (attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filedata, filedata_original, filedata_full, filedata_mini, filedata_search, filedata_gallery, filedata_snapshot, filetype, filetype_original, width, width_original, width_full, width_mini, width_search, width_gallery, width_snapshot, height, height_original, height_full, height_mini, height_search, height_gallery, height_snapshot, visible, counter, filesize, filesize_original, filesize_full, filesize_mini, filesize_search, filesize_gallery, filesize_snapshot, filehash, ipaddress, exifdata, watermarked)
                                        VALUES(
                                        NULL,
                                        '" . $ilance->db->escape_string($uncrypted['attachtype']) . "',
                                        '" . intval($uncrypted['user_id']) . "',
                                        '" . intval($uncrypted['portfolio_id']) . "',
                                        '" . intval($uncrypted['project_id']) . "',
                                        '" . intval($uncrypted['pmb_id']) . "',
                                        '" . intval($uncrypted['category_id']) . "',
                                        '" . DATETIME24H . "',
                                        '" . $ilance->db->escape_string($valid_size['uploadfilename']) . "',
                                        '" . $ilance->db->escape_string($valid_size['filedata']) . "',
                                        '" . $ilance->db->escape_string($valid_size['filedata_original']) . "',
                                        '" . $ilance->db->escape_string($valid_size['filedata_full']) . "',
                                        '" . $ilance->db->escape_string($valid_size['filedata_mini']) . "',
                                        '" . $ilance->db->escape_string($valid_size['filedata_search']) . "',
                                        '" . $ilance->db->escape_string($valid_size['filedata_gallery']) . "',
                                        '" . $ilance->db->escape_string($valid_size['filedata_snapshot']) . "',
                                        '" . $ilance->db->escape_string($valid_size['uploadfiletype']) . "',
                                        '" . $ilance->db->escape_string($valid_size['uploadfiletype_original']) . "',
                                        '" . intval($valid_size['uploadwidth']) . "',
                                        '" . intval($valid_size['uploadwidth_original']) . "',
                                        '" . intval($valid_size['uploadwidth_full']) . "',
                                        '" . intval($valid_size['uploadwidth_mini']) . "',
                                        '" . intval($valid_size['uploadwidth_search']) . "',
                                        '" . intval($valid_size['uploadwidth_gallery']) . "',
                                        '" . intval($valid_size['uploadwidth_snapshot']) . "',
                                        '" . intval($valid_size['uploadheight']) . "',
                                        '" . intval($valid_size['uploadheight_original']) . "',
                                        '" . intval($valid_size['uploadheight_full']) . "',
                                        '" . intval($valid_size['uploadheight_mini']) . "',
                                        '" . intval($valid_size['uploadheight_search']) . "',
                                        '" . intval($valid_size['uploadheight_gallery']) . "',
                                        '" . intval($valid_size['uploadheight_snapshot']) . "',
                                        '" . intval($ilconfig['attachment_moderationdisabled']) . "',
                                        '0',
                                        '" . $ilance->db->escape_string($valid_size['uploadfilesize']) . "',
                                        '" . $ilance->db->escape_string($valid_size['uploadfilesize_original']) . "',
                                        '" . $ilance->db->escape_string($valid_size['uploadfilesize_full']) . "',
                                        '" . $ilance->db->escape_string($valid_size['uploadfilesize_mini']) . "',
                                        '" . $ilance->db->escape_string($valid_size['uploadfilesize_search']) . "',
                                        '" . $ilance->db->escape_string($valid_size['uploadfilesize_gallery']) . "',
                                        '" . $ilance->db->escape_string($valid_size['uploadfilesize_snapshot']) . "',
                                        '" . $ilance->db->escape_string($valid_size['filehash']) . "',
                                        '" . $ilance->db->escape_string(IPADDRESS) . "',
                                        '" . $ilance->db->escape_string($this->exif) . "',
                                        '" . intval($this->watermarked) . "')
                                ", 0, null, __FILE__, __LINE__);
                                $newattachid = $ilance->db->insert_id();
                                ($apihook = $ilance->api('save_attachment_admin_user_end')) ? eval($apihook) : false;
                        }
                        // #### regular user uploading attachment
                        else
                        {
                                ($apihook = $ilance->api('save_attachment_regular_user_start')) ? eval($apihook) : false;

                                $ilance->db->query("
                                        INSERT INTO " . DB_PREFIX . "attachment
                                        (attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filedata, filedata_original, filedata_full, filedata_mini, filedata_search, filedata_gallery, filedata_snapshot, filetype, filetype_original, width, width_original, width_full, width_mini, width_search, width_gallery, width_snapshot, height, height_original, height_full, height_mini, height_search, height_gallery, height_snapshot, visible, counter, filesize, filesize_original, filesize_full, filesize_mini, filesize_search, filesize_gallery, filesize_snapshot, filehash, ipaddress, exifdata, watermarked)
                                        VALUES(
                                        NULL,
                                        '" . $ilance->db->escape_string($uncrypted['attachtype']) . "',
                                        '" . $uncrypted['user_id'] . "',
                                        '" . intval($uncrypted['portfolio_id']) . "',
                                        '" . intval($uncrypted['project_id']) . "',
                                        '" . intval($uncrypted['pmb_id']) . "',
                                        '" . intval($uncrypted['category_id']) . "',
                                        '" . DATETIME24H . "',
                                        '" . $ilance->db->escape_string($valid_size['uploadfilename']) . "',
                                        '" . $ilance->db->escape_string($valid_size['filedata']) . "',
                                        '" . $ilance->db->escape_string($valid_size['filedata_original']) . "',
                                        '" . $ilance->db->escape_string($valid_size['filedata_full']) . "',
                                        '" . $ilance->db->escape_string($valid_size['filedata_mini']) . "',
                                        '" . $ilance->db->escape_string($valid_size['filedata_search']) . "',
                                        '" . $ilance->db->escape_string($valid_size['filedata_gallery']) . "',
                                        '" . $ilance->db->escape_string($valid_size['filedata_snapshot']) . "',
                                        '" . $ilance->db->escape_string($valid_size['uploadfiletype']) . "',
                                        '" . $ilance->db->escape_string($valid_size['uploadfiletype_original']) . "',
                                        '" . intval($valid_size['uploadwidth']) . "',
                                        '" . intval($valid_size['uploadwidth_original']) . "',
                                        '" . intval($valid_size['uploadwidth_full']) . "',
                                        '" . intval($valid_size['uploadwidth_mini']) . "',
                                        '" . intval($valid_size['uploadwidth_search']) . "',
                                        '" . intval($valid_size['uploadwidth_gallery']) . "',
                                        '" . intval($valid_size['uploadwidth_snapshot']) . "',
                                        '" . intval($valid_size['uploadheight']) . "',
                                        '" . intval($valid_size['uploadheight_original']) . "',
                                        '" . intval($valid_size['uploadheight_full']) . "',
                                        '" . intval($valid_size['uploadheight_mini']) . "',
                                        '" . intval($valid_size['uploadheight_search']) . "',
                                        '" . intval($valid_size['uploadheight_gallery']) . "',
                                        '" . intval($valid_size['uploadheight_snapshot']) . "',
                                        '" . intval($ilconfig['attachment_moderationdisabled']) . "',
                                        '0',
                                        '" . $ilance->db->escape_string($valid_size['uploadfilesize']) . "',
                                        '" . $ilance->db->escape_string($valid_size['uploadfilesize_original']) . "',
                                        '" . $ilance->db->escape_string($valid_size['uploadfilesize_full']) . "',
                                        '" . $ilance->db->escape_string($valid_size['uploadfilesize_mini']) . "',
                                        '" . $ilance->db->escape_string($valid_size['uploadfilesize_search']) . "',
                                        '" . $ilance->db->escape_string($valid_size['uploadfilesize_gallery']) . "',
                                        '" . $ilance->db->escape_string($valid_size['uploadfilesize_snapshot']) . "',
                                        '" . $ilance->db->escape_string($valid_size['filehash']) . "',
                                        '" . $ilance->db->escape_string(IPADDRESS) . "',
                                        '" . $ilance->db->escape_string($this->exif) . "',
                                        '" . intval($this->watermarked) . "')
                                ", 0, null, __FILE__, __LINE__);
                                $newattachid = $ilance->db->insert_id();
                                if ($ilconfig['attachment_moderationdisabled'] == 0 AND ($uncrypted['attachtype'] == 'slideshow' OR $uncrypted['attachtype'] == 'itemphoto' OR $uncrypted['attachtype'] == 'project' OR $uncrypted['attachtype'] == 'bid'))
                                {
                                        $page = (fetch_auction('project_state', $uncrypted['project_id']) == 'product') ? $ilpage['merch'] : $ilpage['rfp'];
                                        $ilance->email->mail = SITE_EMAIL;
                                        $ilance->email->slng = fetch_site_slng();
                                        $ilance->email->get('attachment_moderation_mail');
                                        $ilance->email->set(array(
                                                '{{ownername}}' => 'Admin',
                                                '{{provider}}' => fetch_user('username', $uncrypted['user_id']),
                                                '{{project_title}}' => fetch_auction('project_title', intval($uncrypted['project_id'])),
                                                '{{attachment}}' => HTTP_SERVER . $ilpage['attachment'] . '?id=' . $valid_size['filehash'] . ' - ' . print_string_wrap(handle_input_keywords($valid_size['uploadfilename'])),
                                                '{{p_id}}' => $uncrypted['project_id'],
                                                '{{url}}' => HTTP_SERVER . $page . '?id=' . $uncrypted['project_id'],
                                                '{{type}}' => $valid_size['uploadfiletype'],
                                        ));
                                        $ilance->email->send();
                                }

                                ($apihook = $ilance->api('save_attachment_regular_user_end')) ? eval($apihook) : false;
                        }
                        switch ($uncrypted['attachtype'])
                        {
                                case 'itemphoto':
                                case 'slideshow':
                                {
                                        $itemphotocount = $this->fetch_listing_photo_count($uncrypted['project_id']);
                                        if ($itemphotocount <= 0)
                                        {
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "projects
                                                        SET hasimage = '0', hasimageslideshow = '0'
                                                        WHERE project_id = '" . intval($uncrypted['project_id']) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                        }
                                        else if ($itemphotocount == 1)
                                        {
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "projects
                                                        SET hasimage = '1', hasimageslideshow = '0'
                                                        WHERE project_id = '" . intval($uncrypted['project_id']) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                        }
                                        else if ($itemphotocount > 1)
                                        {
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "projects
                                                        SET hasimage = '1', hasimageslideshow = '1'
                                                        WHERE project_id = '" . intval($uncrypted['project_id']) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                        }
                                        break;
                                }
                                case 'digital':
                                {
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "projects
                                                SET hasdigitalfile = '1'
                                                WHERE project_id = '" . intval($uncrypted['project_id']) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        break;
                                }
                        }
                }

                ($apihook = $ilance->api('save_attachment_end')) ? eval($apihook) : false;

                return true;
        }
        
        /**
        * Function to count the number of uploaded photos to any given listing id
        *
        * @param       integer      listing id
        *
        * @return      integer      Returns the number (count) of photos found
        */
        function fetch_listing_photo_count($listingid = 0)
        {
                global $ilance, $ilconfig;
                $count = 0;
                $sql = $ilance->db->query("
                        SELECT attachtype
                        FROM " . DB_PREFIX . "attachment
                        WHERE project_id = '" . intval($listingid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                if ($res['attachtype'] == 'itemphoto' OR $res['attachtype'] == 'slideshow')
                                {
                                        $count++;
                                }
                        }
                }
                return $count;
        }

        /**
        * Function to remove a file attachment from the system for a specified user
        *
        * @param       integer      attachment id
        * @param       integer      user id (optional)
        *
        * @return      nothing
        */
        function remove_attachment($attachid = 0, $userid = 0)
        {
                global $ilance, $ilconfig, $phrase, $show, $ilpage;
                $sqluserid = '';
                if ($userid > 0)
                {
                        $sqluserid = "AND user_id = '" . intval($userid) . "'";
                }
                $sql = $ilance->db->query("
                        SELECT attachtype, filesize, tblfolder_ref, project_id, filehash
                        FROM " . DB_PREFIX . "attachment
                        WHERE attachid = '" . intval($attachid) . "'
                        $sqluserid
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);

                        ($apihook = $ilance->api('attachment_remove_attachment_start')) ? eval($apihook) : false;

                        if ($res['attachtype'] == 'ws')
                        {
                                $ilance->db->query("
                                        DELETE FROM " . DB_PREFIX . "attachment
                                        WHERE attachid = '" . intval($attachid) . "'
                                        $sqluserid
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "attachment_folder
                                        SET folder_size = folder_size - $res[filesize]
                                        WHERE id = '" . $res['tblfolder_ref'] . "'
                                ", 0, null, __FILE__, __LINE__);

                                ($apihook = $ilance->api('attachment_remove_attachment_ws_attachtype_end')) ? eval($apihook) : false;
                        }
                        else
                        {
                                $ilance->db->query("
                                        DELETE FROM " . DB_PREFIX . "attachment
                                        WHERE attachid = '" . intval($attachid) . "'
                                        $sqluserid
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("
                                        DELETE FROM " . DB_PREFIX . "attachment_color
                                        WHERE attachid = '" . intval($attachid) . "'
                                ", 0, null, __FILE__, __LINE__);
                                switch ($res['attachtype'])
                                {
                                        case 'itemphoto':
                                        case 'slideshow':
                                        {
                                                $itemphotocount = $this->fetch_listing_photo_count($res['project_id']);
                                                if ($itemphotocount <= 0)
                                                {
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "projects
                                                                SET hasimage = '0',
								hasimageslideshow = '0'
                                                                WHERE project_id = '" . intval($res['project_id']) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                }
                                                else if ($itemphotocount == 1)
                                                {
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "projects
                                                                SET hasimage = '1',
								hasimageslideshow = '0'
                                                                WHERE project_id = '" . intval($res['project_id']) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                }
                                                else if ($itemphotocount > 1)
                                                {
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "projects
                                                                SET hasimage = '1',
								hasimageslideshow = '1'
                                                                WHERE project_id = '" . intval($res['project_id']) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                }
                                                break;
                                        }
                                        case 'digital':
                                        {
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "projects
                                                        SET hasdigitalfile = '0'
                                                        WHERE project_id = '" . intval($res['project_id']) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                break;
                                        }
                                }

                                ($apihook = $ilance->api('attachment_remove_attachment_attachtype_end')) ? eval($apihook) : false;
                        }
                        // #### remove physical file on filesystem #############
                        if ($ilconfig['attachment_dbstorage'] == 0)
                        {
                                $attachpath = $ilance->attachment_tools->fetch_attachment_path($res['attachtype'], false);
                                $attachpath2 = $ilance->attachment_tools->fetch_attachment_path($res['attachtype'], true); // original
                                $attachpath3 = $ilance->attachment_tools->fetch_attachment_path($res['attachtype'], false);
                                $attachpath4 = $ilance->attachment_tools->fetch_attachment_path($res['attachtype'], false);
                                $attachpath5 = $ilance->attachment_tools->fetch_attachment_path($res['attachtype'], false);
                                $attachpath6 = $ilance->attachment_tools->fetch_attachment_path($res['attachtype'], false);
                                $attachpath7 = $ilance->attachment_tools->fetch_attachment_path($res['attachtype'], false);
                                $filename = $attachpath . $res['filehash'] . '.attach';
                                $filename2 = $attachpath2 . $res['filehash'] . '.attach'; // original
                                $filename3 = $attachpath3 . 'resized/full/' . $res['filehash'] . '.attach';
                                $filename4 = $attachpath4 . 'resized/mini/' . $res['filehash'] . '.attach';
                                $filename5 = $attachpath5 . 'resized/search/' . $res['filehash'] . '.attach';
                                $filename6 = $attachpath6 . 'resized/gallery/' . $res['filehash'] . '.attach';
                                $filename7 = $attachpath7 . 'resized/snapshot/' . $res['filehash'] . '.attach';
                                if (file_exists($filename))
                                {
                                        @unlink($filename); // remove default attachment (ILance 4.0.0 backward compat)
                                }
                                if (file_exists($filename2))
                                {
                                        @unlink($filename2); // remove original attachment
                                }
                                if (file_exists($filename3))
                                {
                                        @unlink($filename3); // remove full photo
                                }
                                if (file_exists($filename4))
                                {
                                        @unlink($filename4); // remove mini photo
                                }
                                if (file_exists($filename5))
                                {
                                        @unlink($filename5); // remove search photo
                                }
                                if (file_exists($filename6))
                                {
                                        @unlink($filename6); // remove gallery photo
                                }
                                if (file_exists($filename7))
                                {
                                        @unlink($filename7); // remove snapshot photo
                                }

                                ($apihook = $ilance->api('attachment_remove_attachment_remove_filesystem_end')) ? eval($apihook) : false;
                        }
                        
                        // todo: audit log should be triggerd here
                        ($apihook = $ilance->api('attachment_remove_attachment_end')) ? eval($apihook) : false;
                }
        }

        /**
        * Function to to create a ImageCreateBMP equiv for GD
        *
        * @param       string       image source location + name (ie: /home/images/image.jpg)
        * @param       boolean      
        *
        * @return      boolean      Returns true or false if the bmp to be converted
        */
        function imagecreatebmp2gd($src, $dest = false)
        {
        	if (!($src_f = fopen($src, "rb")))
                {
        		return false;
        	}
        	if (!($dest_f = fopen($dest, "wb")))
                {
        		return false;
        	}
        	$header = unpack("vtype/Vsize/v2reserved/Voffset", fread($src_f, 14));
        	$info = unpack("Vsize/Vwidth/Vheight/vplanes/vbits/Vcompression/Vimagesize/Vxres/Vyres/Vncolor/Vimportant", fread($src_f, 40));
        	extract($info);
        	extract($header);
        	if ($type != 0x4D42)
                {
        		return false;
        	}
        	$palette_size = $offset - 54;
        	$ncolor = $palette_size / 4;
        	$gd_header = "";
        	$gd_header .= ($palette_size == 0) ? "\xFF\xFE" : "\xFF\xFF";
        	$gd_header .= pack("n2", $width, $height);
        	$gd_header .= ($palette_size == 0) ? "\x01" : "\x00";
        	if ($palette_size)
                {
        		$gd_header .= pack("n", $ncolor);
        	}
        	// no transparency
        	$gd_header .= "\xFF\xFF\xFF\xFF";
        	fwrite($dest_f, $gd_header);
        	if ($palette_size)
                {
        		$palette = fread($src_f, $palette_size);
        		$gd_palette = "";
        		$j = 0;
        		while ($j < $palette_size)
                        {
        			$b = $palette{$j++};
        			$g = $palette{$j++};
        			$r = $palette{$j++};
        			$a = $palette{$j++};
        			$gd_palette .= "$r$g$b$a";
        		}
        		$gd_palette .= str_repeat("\x00\x00\x00\x00", 256 - $ncolor);
        		fwrite($dest_f, $gd_palette);
        	}
        	$scan_line_size = (($bits * $width) + 7) >> 3;
        	$scan_line_align = ($scan_line_size & 0x03) ? 4 - ($scan_line_size & 0x03) : 0;
        	for ($i = 0, $l = $height - 1; $i < $height; $i++, $l--)
                {
        		fseek($src_f, $offset + (($scan_line_size + $scan_line_align) * $l));
        		$scan_line = fread($src_f, $scan_line_size);
        		if ($bits == 24)
                        {
        			$gd_scan_line = "";
        			$j = 0;
        			while ($j < $scan_line_size)
                                {
        				$b = $scan_line{$j++};
        				$g = $scan_line{$j++};
        				$r = $scan_line{$j++};
        				$gd_scan_line .= "\x00$r$g$b";
        			}
        		}
        		else if ($bits == 8)
                        {
        			$gd_scan_line = $scan_line;
        		}
        		else if ($bits == 4)
                        {
        			$gd_scan_line = "";
        			$j = 0;
        			while ($j < $scan_line_size)
                                {
        				$byte = ord($scan_line{$j++});
        				$p1 = chr($byte >> 4);
        				$p2 = chr($byte & 0x0F);
        				$gd_scan_line .= "$p1$p2";
        			}
                                $gd_scan_line = substr($gd_scan_line, 0, $width);
        		}
        		else if ($bits == 1)
                        {
        			$gd_scan_line = "";
        			$j = 0;
        			while ($j < $scan_line_size)
                                {
        				$byte = ord($scan_line{$j++});
        				$p1 = chr((int) (($byte & 0x80) != 0));
        				$p2 = chr((int) (($byte & 0x40) != 0));
        				$p3 = chr((int) (($byte & 0x20) != 0));
        				$p4 = chr((int) (($byte & 0x10) != 0));
        				$p5 = chr((int) (($byte & 0x08) != 0));
        				$p6 = chr((int) (($byte & 0x04) != 0));
        				$p7 = chr((int) (($byte & 0x02) != 0));
        				$p8 = chr((int) (($byte & 0x01) != 0));
        				$gd_scan_line .= "$p1$p2$p3$p4$p5$p6$p7$p8";
        			}
                                $gd_scan_line = substr($gd_scan_line, 0, $width);
        		}
        		fwrite($dest_f, $gd_scan_line);
        	}
        	fclose($src_f);
        	fclose($dest_f);
        	return true;
        }

        /**
        * Function to to create an image from a .bmp picture
        *
        * @param       string       image source location + name (ie: /home/images/image.jpg)
        *
        * @return      mixed        Returns image resource from imagecreatefromgd() or false if cannot be completed
        */
        function imagecreatefrombmp($filename)
        {
        	$tmp_name = tempnam(sys_get_temp_dir(), "GD");
        	if ($this->imagecreatebmp2gd($filename, $tmp_name))
                {
        		$img = imagecreatefromgd($tmp_name);
        		@unlink($tmp_name);
        		return $img;
                }
                return false;
        }

        /**
        * Function to resize an uploaded picture by keeping it's aspect ratio based on the max width and height defined by the admin within the Attachment Manager of the Admin CP.
        *
        * @param       string      source file
        * @param       integer     max width
        * @param       integer     max height
        * @param       string      file extension of original image
        * @param       integer     width from original image getimagesize()
        * @param       integer     height from original image getimagesize()
        * @param       string      destination source file (default blank)
        * @param       integer     resized image quality (default 100)
        *
        * @return      boolean     Returns true or false
        */
        function picture_resizer($src, $maxwidth, $maxheight, $extension, $picturewidth, $pictureheight, $dsrc = '', $quality = 100)
        {
                error_reporting(0);
                $r = 1;
                $e = strtolower(substr($extension, strrpos($extension, '.') + 1, 3));
                if (($e == 'jpg') OR ($e == 'peg') OR ($e == 'jpe'))
                {
                        $oldimage = imagecreatefromjpeg($src) OR $r = 0;
                }
                else if ($e == 'gif')
                {
                        $oldimage = imagecreatefromgif($src) OR $r = 0;
                }
                else if ($e == 'bmp')
                {
                        $oldimage = $this->imagecreatefrombmp($src) OR $r = 0;
                }
                else if ($e == 'png')
                {
                        $oldimage = imagecreatefrompng($src) OR $r = 0;
                }
                else
                {
                        $r = 0;
                }
                if ($r)
                {
                        if ($picturewidth > $maxwidth OR $pictureheight > $maxheight)
                        {
                                if ($picturewidth > $pictureheight)
                                { // landscape picture or rectangular
                                        $maxheight = ($maxwidth / $picturewidth) * $pictureheight;
                                }
                                else if ($pictureheight > $picturewidth)
                                { // potrait picture
                                        $maxwidth = ($maxheight / $pictureheight) * $picturewidth;
                                }
                        }
                        else
                        {
                                $maxwidth = $picturewidth;
                                $maxheight = $pictureheight;
                        }
			$newthumb = imagecreatetruecolor($maxwidth, $maxheight);
                        $bgcolor = imagecolorallocate($newthumb, 255, 255, 255);
                        imagefill($newthumb, 0, 0, $bgcolor);
                        imagecopyresampled($newthumb, $oldimage, 0, 0, 0, 0, $maxwidth, $maxheight, $picturewidth, $pictureheight);
                        $newname = substr($this->file_name, 0, -4) . '.jpg';
                        $this->file_name = $this->file_name_original = $newname;
                        $this->filetype = 'image/jpeg';
                        $this->width = $maxwidth;
                        $this->height = $maxheight;
                        if (!empty($dsrc))
                        {
                                imagejpeg($newthumb, $dsrc, $quality); 
                        }
                        else
                        {
                                imagejpeg($newthumb, $src, $quality);
                        }
                        $this->pictureresized = true;
                        imagedestroy($newthumb);
                        imagedestroy($oldimage);
                        return true;
                }
                else
                {
                        $this->pictureresized = false;
                }
                return false;
        }
	
	/**
        * Function to print the file's extension icon
        *
        * @param       string      filename
        *
        * @return      string      Returns HTML formatted img srg tag icon
        */
        function print_file_extension_icon($filename)
	{
                global $ilconfig;
                $attachextension = fetch_extension($filename) . '.gif';
                if (file_exists(DIR_SERVER_ROOT . $ilconfig['template_imagesfolder'] . 'icons/' . $attachextension))
                {
                        $attachextension = fetch_extension($filename) . '.gif';
                }
                else
                {
                        $attachextension = 'attach.gif';
                }
                return $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/' . $attachextension;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>