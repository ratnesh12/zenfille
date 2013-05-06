
var last_procent = [];
var progress = [];

function trackProgress(id, fileName, loaded, total, type)
{

	var percent = Math.round((loaded / total) * 100);

	if (!$('.file-'+id).size())
	{

		var ext = fileName.split(".");
		ext = ext[1];
		jQuery("#switch_client_files").show('slow');
		if(fileName.length>23)fileName = fileName.substring(0,22)+'...';
		$.tmpl( $("#templateUploadedFile").html(), {
			"id" : id,
			"ext": ext,
			"fileName": fileName,
			"total": getBytesWithUnit(total)
		}).appendTo( ".files_table" );

		progress[id] = $('.file-progress-'+id).progressbar({
			value: percent
		});


		if (typeof(autoResize) != 'undefined')
		{
			setTimeout(function(){
				autoResize();
			},0);
		}
	}
	else
	{
		if (percent != last_procent[id])
		{
			progress[id].progressbar( "option", "value", percent );
			last_procent[id] = percent;
		}
	}

}

jQuery(document).ready(function($){


	if ( $.browser.flash && $.browser.msie )
	{
		var uploader = new SWFUpload({
			file_post_name: 'qqfile',
			flash_url : site_url("assets/js/swfupload.swf"),
			upload_url: Zenfile.uploadUrl,
			file_size_limit : "100 MB",
			file_types : "*.*",
			file_types_description : "All Files",
			file_upload_limit : 100,
			file_queue_limit : 0,
			prevent_swf_caching : false,
			post_params : {
				'uploader': 'swfupload',
				'customer_token': $(".customer_token").val(),
				'files_hash': (Math.random(0,10000) * Math.random(0,10000))
			},

			// Button Settings

			button_image_url : Zenfile.flashUploadButtonImage,
			button_placeholder_id : "file-uploader",
			button_width: 160,
			button_height: 45,

			debug: true,

			upload_complete_handler: uploadComplete,
			file_dialog_complete_handler : fileDialogComplete,
			upload_progress_handler: function uploadProgress(file, bytesLoaded, bytesTotal) {

				try {

					trackProgress(file.index, file.name, bytesLoaded, bytesTotal, 'foreign');

				} catch (ex) {
					console.log(ex);
				}
			},
			upload_error_handler : function(fileName, errorCode, message) {
				if(fileName.length>23)fileName = fileName.substring(0,22)+'...';
				$('.file-progress-'+fileName).addClass("error");
                                stanOverlay.setTITLE("Information!");
                                stanOverlay.setMESSAGE("Upload "+fileName+" error: "+message);
                                stanOverlay.SHOW();

			},
			upload_success_handler : function(file, data) {
				var json = $.parseJSON(data);

				if (file && json.success)
				{
					gotCompleteUpload(file.index, file.name, json);
				}
				else
				{
					$('.file-progress-'+file.index).addClass('error');
                                        stanOverlay.setTITLE("Information!");
                                        stanOverlay.setMESSAGE(json.error);
                                        stanOverlay.SHOW();
				}

			},
			minimum_flash_version : "9.0.28"
		});


	}
	else
	{
		var uploader = new qq.FileUploader({

			element: document.getElementById('file-uploader'),
			'name' : 'upload_file',
			action: Zenfile.uploadUrlSecure,
			debug: false,
			params: {
				'uploader':'stream',
				'customer_token': $(".customer_token").val(),
				'files_hash': (Math.random(0,10000) * Math.random(0,10000))
			},
			template: $("#templateUploader").html(),
			onComplete: function(id, fileName, json) {
				if (json.success)
				{
					if(json.files){
						gotCompleteMultiUpload(json,id);
					}else{
						gotCompleteUpload(id, fileName, json);
					}
					
				}
				else if (json.error)
				{
					$('.file-progress-'+id).addClass('error');
                                        stanOverlay.setTITLE("Information!");
                                        stanOverlay.setMESSAGE(json.error);
                                        stanOverlay.SHOW();
					$('.file-progress-'+id).addClass('error');
				}
				else
				{
					$('.file-progress-'+id).addClass('error');
                                        stanOverlay.setTITLE("Information!");
                                        stanOverlay.setMESSAGE('Unknown upload error');
                                        stanOverlay.SHOW();
					$('.file-progress-'+id).addClass('error');
				}


			},
			onProgress: function(id, fileName, loaded, total){

				trackProgress(id, fileName, loaded, total);

			},
            onSubmit:function(id, fileName){
                if ($.browser.msie  && (parseInt($.browser.version, 10) === 8 || parseInt($.browser.version, 10) === 9)) {
                    var ext = fileName.split(".");
                    ext = ext[1];
                    $.tmpl( $("#templateUploadedFile").html(), {
                            "id" : id,
                            "ext": ext,
                            "fileName": fileName,
                            "total": ""
                    }).appendTo( ".files_table" );
                  }
            },
			onCancel: function(id, fileName) {
				$('.file-progress-'+id).addClass('error');
                                stanOverlay.setTITLE("Information!");
                                stanOverlay.setMESSAGE("File upload canceled");
                                stanOverlay.SHOW();

			},
			showMessage: function(message){
                                stanOverlay.setTITLE("Information!");
                                stanOverlay.setMESSAGE("Uploading Error: "+message);
                                stanOverlay.SHOW();

				$('.file-progress-'+id).addClass('error');
			}
		});

	}

	$(".remove_file_activator").live("click",function()
	{
		var parent = $(this).parents(".file");
		parent.find('select.file-category').selectBox('destroy');
		$.post(Zenfile.uploadedRemoveUrl,{ file: $("input",parent).val() },function()
		{
			parent.fadeOut(400);

			setTimeout(function() {
				parent.remove();

				if (typeof(autoResize) != 'undefined')
					autoResize();
			},600);

			return false;
		});

		return false;
	});

});
