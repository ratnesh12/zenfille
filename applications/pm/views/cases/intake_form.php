<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/ckeditor/adapters/jquery.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/case_view.css"/>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.tmpl.js"></script>
<script type="text/javascript">
    function remove_file(file_id){
        $.post("<?php echo base_url(); ?>cases/remove_file/", {file_id:file_id});
        var parent_row = $("a#delete_link_" + file_id).parent("td").parent("tr");
        parent_row.remove();

    }

    function remove_file_from_email(file_id) {
        /*$("input[name='attached_file_" + file_id + "']").remove();*/
        if (confirm("Delete selected file from email?")) {
            $("span#attached_" + file_id).remove();

            $("div#email-countries div.selected").click();
        }
    }
    
	$(document).ready(function() {
		var CKEDITOR_BASEPATH = 'assets/js/ckeditor/';
		$("#template_content").ckeditor( {
	        toolbar : 'Zen',
	        uiColor : '#CCCCCC'
    	});

		$("#attach-from-case-button").click(function () {
	    	jQuery.facebox({ ajax: '<?= base_url() ?>cases/attach_files_from_case/<?= $case['id']; ?>' });
	    });
		
		$("#remove-file").click(function() {
			$("input[name='file_id']").val("");
			$("#sow-box").remove();
		});
        var email_uploader = new qq.FileUploader({
            element:$("#email-uploader")[0],
            // path to server-side upload script
            action:"<?php echo base_url(); ?>cases/upload_file/<?php echo $case['id'];?>",
            template: $("#templateUploaderEmail").html(),
            allowedExtensions:['jpg', 'jpeg', 'sql', 'png', 'gif', 'txt', 'doc', 'zip', 'pdf', 'docx', 'xls', 'pptx', 'xlsx', 'ppt','rtf'],
            params:{
                customer_id:"<?php echo $case['user_id']; ?>",
                case_number:"<?php echo $case['case_number']; ?>",
                file_type_id:20
            },
            onComplete:function (id, fileName, json) {
                if (json.success){
                    $(".qq-upload-list li:last").remove();
                    var ext = fileName.split(".");
                    $.tmpl( $("#templateUploadedFile_Info"), {
                        "id" : id,
                        "ext": ext,
                        "fileName": fileName,
                        "delete_link": json.delete_link
                    }).appendTo( ".qq-upload-list" );
                }else if (json.error){
                    stanOverlay.setTITLE("Information!");
                    stanOverlay.setMESSAGE(json.error);
                    stanOverlay.SHOW();
                }else{
                    stanOverlay.setTITLE("Information!");
                    stanOverlay.setMESSAGE('Unknown upload error');
                    stanOverlay.SHOW();
                }
            }
        });
	});

	function submit_form(){
		$('#attached-files input[type=hidden]').appendTo('#mail_attached_files');
	}
</script>

<script id="templateUploadedFile_Info" type="text/x-jquery-tmpl">
<tr id="file_row_${id}">
    <td>
        <div class="file file-${id} ${ext}" >
            <div class="file-info">
                <span class="file-icon"></span>
                ${fileName}
            </div>
            <div class="clear"></div>
        </div>
    </td>
    <td>
        {{html delete_link}}
    </td>
</tr>
</script>

<form action="<?= base_url().'/cases/submit_intake/'.$case['case_number'] ?>" method="post">
<table border="0" cellpadding="4" cellspacing="0" class="data-table">
<?php 
	$to_email = TEST_MODE ? TEST_CLIENT_EMAIL : $customer['email'];
    if($customer['type'] == 'firm'){
        $to_email = TEST_MODE ? TEST_FIRM_EMAIL : $customer['email'];
    }
    
    $sow_link = '';
	if ( ! is_null($sow))
	{
		$sow_link = anchor('/cases/view_file/'.$sow['id'], $sow['filename']).'&nbsp;<a href="javascript:void(0);" id="remove-file" title="Remove file"><img title="Remove file" src="'.base_url().'assets/images/i/delete.png"></a>';
		echo form_hidden('file_id', $sow['id']);
	}
?>
<tr>
	<td width="50">To</td>
	<td><?= form_input('to', $to_email, 'style="width: 95%;"');?></td>
</tr>
<tr>
	<td>Cc</td>
	<td><?= form_input('cc', $cc, 'style="width: 95%;"');?></td>
</tr>
<tr>
	<td>Subject</td>
	<td><?= form_input('subject', $email_content['subject'], 'style="width: 95%;"');?></td>
</tr>
<tr>
	<td colspan="2"><?= form_textarea('template_content', $email_content['text'], 'id="template_content"');?></td>
</tr>
<tr>
	<td>SOW</td>
	<td><span id="sow-box"><?= $sow_link?></span></td>
</tr>

<tr>
	<td><?= form_submit('send', 'Send and Intake', 'onclick="submit_form();"');?></td>
	<td>&nbsp;</td>
</tr>

</table>
</form>
<div>
    <div class="email-uploader-position" style="margin-left: 0px;">
        <div id="email-uploader">
            <noscript>
                <p>Please enable JavaScript to use file uploader.</p>
            </noscript>
        </div>
    </div>
    <div class="attach-from-case-button-position">
		<a id="attach-from-case-button" class="attach-from-case-button">&nbsp;</a>
		<div id="attached-files"></div>
	</div>
    <div class="clear"></div>
   
    <script id="templateUploaderEmail" type="text/x-jquery-tmpl">
        <div class="qq-uploader">
            <div class="qq-upload-drop-area"><span>Drop files here to upload</span></div>
            <div class="qq-upload-button email-upload-button">
                
            </div>
            <table class="qq-upload-list"></table>
        </div>
    </script></div>