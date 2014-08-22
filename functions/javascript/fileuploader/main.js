/*
* jQuery File Upload Plugin 7.0
* https://github.com/blueimp/jQuery-File-Upload
*
* Copyright 2010, Sebastian Tschan
* https://blueimp.net
*
* Licensed under the MIT license:
* http://www.opensource.org/licenses/MIT
*/
$(function ()
{
	'use strict';
	var listingid = 0;
	var maxfiles = 1;
	var attachtype = 'slideshow';
	if ($("#project_id"))
	{
		listingid = $("#project_id").val();
	}
	if ($("#attachtype"))
	{
		attachtype = $("#attachtype").val();
	}
	if ($("#maxfiles"))
	{
		maxfiles = parseInt($("#maxfiles").val());
	}
	$('#fileupload').fileupload({
		maxNumberOfFiles: maxfiles,
		url: 'ajax.php?do=fileuploader&rfpid=' + listingid + '&attachtype=' + attachtype
	})
	$('#fileupload').fileupload(
		'option',
		'redirect',
		window.location.href.replace(
			/\/[^\/]*$/,
			'/cors/result.html?%s'
	));
	$.ajax(
	{
		url: $('#fileupload').fileupload('option', 'url'),
		dataType: 'json',
		context: $('#fileupload')[0]
	}).done(function (result)
	{
		$(this).fileupload('option', 'done').call(this, null, {result: result});
	});
});