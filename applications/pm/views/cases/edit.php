<?php
if ($case['common_status'] == 'active' || $case['common_status'] == 'completed') {
    if($this->session->userdata('type') != 'supervisor'){
        $not_editable = true;
    }

}

$file_types_dd = array('' => '');
if (check_array($file_types))
{
    foreach ($file_types as $file_type)
    {
        $file_types_dd[$file_type['id']] = $file_type['name'];
    }
}

?>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/jquery-ui/jquery.ui.datepicker.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/jquery-ui/jquery-ui-1.8.16.custom.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/jquery-ui/jquery.ui.dialog.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/tipTip.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/jquery-ui/jquery.ui.tabs.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/token-input.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/jquery_notification.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/msgBoxLight.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/jquery.selectbox.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/case_view.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/countries_flags.css"/>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-ui/jquery.ui.core.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-ui/jquery.ui.position.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-ui/jquery.ui.widget.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-ui/jquery.ui.tabs.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-ui/jquery.ui.mouse.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-ui/jquery.ui.draggable.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-ui/jquery.ui.resizable.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-ui/jquery.ui.dialog.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-ui/jquery.ui.datepicker.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-ui/jquery.ui.progressbar.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.tipTip.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/functions.zenfile.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.tmpl.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.scrollto.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.validate.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/ajaxupload.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.cookie.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/ckeditor/adapters/jquery.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery_notification_v.1.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.tokeninput.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.msgBox.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/additional.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.selectbox.js"></script>
<script type="text/javascript">

    $(document).ready(function () {

    	$('a#attach_files_activator').live('click',function(){
			$(".files_to_attach input:checked").each(function(){
				if(!$('#attached-files span#attached_'+$(this).val()).length){
					$.tmpl( $("#templateUploadedFile_Info"), {
		                "id" : $(this).val(),
		                "fileName": $(this).attr('ref')
		            }).appendTo( "#attached-files" );			
				}
			});
			$.facebox.close();
			return false;
		});


        need_calculations = 0;
        $(window).scroll(function(){
    		if($("#facebox").is(":visible") && parseInt($('#facebox').css('height'))<400){
    			$('#facebox').css('top',window.pageYOffset+50);
    		}
    	});
        $("table.case_files select").selectBox();
        jQuery(".tb_switch").each(function(){
            jQuery('#'+jQuery(this).attr('ref')).hide();
        });
        
        /* Message for actions that require refresh */
<?php
$message = $this->session->flashdata('message');
if (is_array($message_array = json_decode($message)))
{
    ?>
                stanOverlay.setTITLE("<?php echo $message_array['title'] ?>");
                stanOverlay.setMESSAGE("<?php echo $message_array['text'] ?>");
                stanOverlay.SHOW();
    <?php
}
?>
        jQuery(document).trigger('close.facebox');
        $(".popup-link").facebox();
        $(".popup-link-facebox").facebox();
        $("#send-estimate-pdf-to-bdv").facebox({div:"#send-estimate-pdf-to-bdv-content"});

<?php
$pm_case_view_opened = $this->session->userdata('pm_case_view_opened');
if (($pm_case_view_opened != '1') && ($case['is_active'] == '1')):
    $this->session->set_userdata('pm_case_view_opened', '1');
    ?>
                $("#tabs").tabs({ selected:4 });
    <?php
else:
    ?>
                $("#tabs").tabs({cookie:{ expires:30}  });
<?php
endif;
?>
        $("#estimate-tabs").tabs({
            selected:0,
            /*cookie: { expires: 30},*/
            ajaxOptions:{
                error:function (xhr, status, index, anchor) {
                    $(anchor.hash).html(
                    "Couldn't load this tab. We'll try to fix this as soon as possible. " +
                        "If this wouldn't be a demo.");
                }
            }
        });
        $("#tracker-tabs").tabs({
            selected:0,
            /*cookie: { expires: 30},*/
            ajaxOptions:{
                error:function (xhr, status, index, anchor) {
                    $(anchor.hash).html(
                    "Couldn't load this tab. We'll try to fix this as soon as possible. " +
                        "If this wouldn't be a demo.");
                }
            }
        });
        $("#finance-tabs").tabs({
            selected:0,
            /*cookie: { expires: 30},*/
            ajaxOptions:{
                error:function (xhr, status, index, anchor) {
                    $(anchor.hash).html(
                    "Couldn't load this tab. We'll try to fix this as soon as possible. " +
                        "If this wouldn't be a demo.");
                }
            }
        });
        var CKEDITOR_BASEPATH = 'assets/js/ckeditor/';
        $("#email-textarea").ckeditor({
            toolbar:'Zen',
            uiColor:'#CCCCCC'
        });

        $("#notification_email_textarea").ckeditor({
            toolbar:'Zen',
            uiColor:'#CCCCCC'
        });

        $('#load_data_from_case_with_same_number').click(function(){
            var case_number = $(this).attr('href');
            $.ajax({
                type: "POST",
                cache: false,
                url: '<?php echo base_url() ?>' + "cases/ajax_get_data_for_case" ,
                data: {
                    case_number: case_number
                } ,
                datatype: 'JSON' ,
                success: (function(data) {
                    var object = jQuery.parseJSON(data);
                    $('input[name=title]').val(object['case']['application_title']);
                    $('input[name=applicant]').val(object['case']['applicant']);
                    // CC skipped

                    $('input[name=first_priority_date]').val(object['case']['first_priority_date']);
                    $('input[name=international_filing_date]').val(object['case']['international_filing_date']);
                    $('input[name=' + 30 + '_month_filing_deadline]').val(object['case']['30_month_filing_deadline']);
                    $('input[name=' + 31 + '_month_filing_deadline]').val(object['case']['31_month_filing_deadline']);
                    $('input[name=publication_date]').val(object['case']['publication_date']);
                    $('input[name=filing_deadline]').val(object['case']['filing_deadline']);
                    $('input[name=number_priorities_claimed]').val(object['case']['number_priorities_claimed']);
                    $('select[name=search_location]').val(object['case']['search_location']);
                    $('input[name=number_claims]').val(object['case']['number_claims']);
                    $('input[name=number_reduced_claims]').val(object['case']['number_reduced_claims']);
                    $('input[name=number_pages_drawings]').val(object['case']['number_pages_drawings']);
                    $('input[name=number_pages]').val(object['case']['number_pages']);

                    $('input[name=number_pages_sequence]').val(object['case']['number_pages_sequence']);
                    $('select[name=sequence_listing]').val(object['case']['sequence_listing']);

                    $('input[name=number_words]').val(object['case']['number_words']);
                    $('input[name=publication_language]').val(object['case']['publication_language']);

                })
            })
            return false;
        });
        shadow_on();
        get_files_table();

        $('.reference_number_input').parent().parent().hide();

        $('#show_references').click(function(){
            $('.reference_number_input').parent().parent().toggle();
            return false;
        });
    });
</script>
<script type="text/javascript">
	function get_files_table(block_name){
		$.post(
	        "<?php echo base_url(); ?>cases/get_files_table/<?= $case['case_number']?>", {},
	        function(html){
	        	$("table.case_files select").selectBox('destroy');
	            $('#case-files-content').html(html);
	            $('#case-files-content').ready(function(){
	            	$("table.case_files select").selectBox();
		            jQuery(".tb_switch").each(function(){
		                jQuery('#'+jQuery(this).attr('ref')).hide();
		            });
		            jQuery(".tb_switch").click(function(){
		                jQuery('#'+jQuery(this).attr('ref')).toggle('slow');
		            });
		            $("a.popup").facebox();
		            if(block_name) {
			            $('#'+block_name).show();
			            $.scrollTo('#'+block_name,800);
		            }
	            });
	            shadow_off();
                $('#save_estimate_form').keypress(function(e){
                    if ( e.which == 13 ) return false;
                    //or...
                    if ( e.which == 13 ) e.preventDefault();
                });


	            $('a#control_assign_type').click( function () {
					var files_ids = [];
					if($('#case-files input[name="case_files[]"]:checked').length<1){
						alert('Please, choose the files');
						return false;
					}
					$('#case-files input[name="case_files[]"]:checked').each(function(){
						files_ids[files_ids.length] = $(this).val();

					});
					jQuery.facebox({ ajax: '<?= base_url() ?>cases/assign_files_to_type_form/<?= $case['case_number'] ?>/?fids='+files_ids });
					return false;
	            });

	            $('a#control_assign_country').live('click', function () {
	            	var files_ids = [];
					if($('#case-files input[name="case_files[]"]:checked').length<1){
						alert('Please, choose the files');
						return false;
					}
					$('#case-files input[name="case_files[]"]:checked').each(function(){
						files_ids[files_ids.length] = $(this).val();

					});
					jQuery.facebox({ ajax: '<?= base_url() ?>cases/assign_files_to_countries_form/<?= $case['case_number'] ?>/?fids='+files_ids });
					
					return false;
	            });
	        },
	        "text"
	    );
	}

	function shadow_on(){
		$('#ajax-shadow').css('display','block');
	}

	function shadow_off(){
		$('#ajax-shadow').css('display','none');
	}

	function remove_file(file_id) {
        $.post("<?php echo base_url(); ?>cases/remove_file/", {file_id:file_id});
        var parent_row = $("a#delete_link_" + file_id).parent("td").parent("tr");
        parent_row.find("select").selectBox('destroy');
        parent_row.remove();

    }

	function file_view_more(file_id){
    	jQuery.facebox({ ajax: '<?= base_url() ?>cases/file_view_more/'+file_id });
    }
    $('#merge_associates').live('click',function(){
       // alert("hello");
        merge_associates();
    });
    function merge_associates(){
        var associates = [];
        if($('input[name="associates[]"]:checked').length<1){
            alert('Please, choose associates');
            return false;
        }
        $('input[name="associates[]"]:checked').each(function(){
            associates [associates .length] = $(this).val();
        });
        jQuery.facebox({ ajax: '<?= base_url() ?>cases/merge_associates_form/<?= $case['id'] ?>/?associates='+associates });

        return false;
    }
    
    function remove_note_from_case(note_id) {
        $.post("<?php echo base_url(); ?>cases/remove_note_from_case/", {note_id:note_id});
        $("a#delete_note_link_" + note_id).parent("td").parent("tr").remove();
    }
    function remove_file_from_email(file_id) {
        if (confirm("Delete selected file from email?")) {
            $("span#attached_" + file_id).remove();

           // $("div#email-countries div.selected").click();
        }
    }
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
            $.tmpl( $("#templateUploadedFile").html(), {
                "id" : id,
                "ext": ext,
                "fileName": fileName,
                "total": getBytesWithUnit(total)
            }).appendTo( "#files_table" );
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

    function gotCompleteMultiUpload(id, fileName, json){
        $("#files_table tr:last").remove();
        for(k in json.rows){
            $("#files_table").append('<tr id="file_row_'+json.rows[k].file_id+'"><td class="file_title">'+
                json.rows[k].file+
                '</td><td>'+
                json.rows[k].visibility+
                '</td><td class="cf_filetype">'+
                json.rows[k].file_type_dropdown+
                '</td><td class="assign_to_countries">'+
                json.rows[k].assign_to_countries_link+
                '</td><td>'+
                json.rows[k].delete_link+
                '</td></tr>');
            $("#files_table tr:last td.assign_to_countries a").facebox();
        }
        $("table.case_files select").selectBox();
        stanOverlay.setTITLE("File uploaded");
        stanOverlay.setMESSAGE("File uploaded");
        stanOverlay.SHOW();
    }

    function gotCompleteUpload(id, fileName, json)
    {

        $(".file-size",'.file-'+id).remove();
        $(".file-progress-"+id,'.file-'+id).remove();
        $(".file-countries",'.file-'+id).show();
        $("#files_table tr.file-"+id+" td").parent('tr').attr('id','file_row_'+json.file_id);
        $("#files_table tr.file-"+id+" td").each(function(index){
            switch(index){
                case 0://filename
                    $(this).html(json.file);
                    break;
                case 1://visibility
                    $(this).html(json.visibility);
                    break;
                case 2://types
                    $(this).html(json.file_type_dropdown);
                    break;
                case 3://countries
                    $(this).html(json.assign_to_countries_link);
                    break;
                case 4://delete
                    $(this).html(json.delete_link);
                    break;
            }
        });
        $("table.case_files select").selectBox();
        $("." + json["class_hash"]).facebox();
        stanOverlay.setTITLE("File uploaded");
        stanOverlay.setMESSAGE("File uploaded");
        stanOverlay.SHOW();
    }


    $(document).ready(function () {
        calculate_estimate_total();
        var associated_visible = $('input[name=is_associates_visible_to_client]');
        associated_visible.change(function(){

            $.ajax({
                type: "POST",
                cache: false,
                url:  "<?php echo base_url() ?>cases/ajax_is_associates_visible_to_client/<?php echo $case['id']; ?>" ,
                data: {
                    is_associates_visible_to_client: $('input[name=is_associates_visible_to_client]:checked').val()

                } ,
                datatype: 'json' ,
                success: (function(html) {

                })
            })
        });


        var email_uploader = new qq.FileUploader({
            element:$("#email-uploader")[0],
            // path to server-side upload script
            action:"<?php echo base_url(); ?>cases/upload_file/<?php echo $case['id']; ?>",
            template: $("#templateUploaderEmail").html(),
            allowedExtensions:['msg','jpg', 'jpeg', 'sql', 'png', 'gif', 'txt', 'doc', 'zip', 'pdf', 'docx', 'xls', 'pptx', 'xlsx', 'ppt','rtf'],
            params:{
                customer_id:"<?php echo $case['user_id']; ?>",
                case_number:"<?php echo $case['case_number']; ?>",
                file_type_id:20
            },
            onSubmit:function (){
                $('.email-uploader-position .qq-upload-list').show();
            },
            onComplete:function (id, fileName, json) {
                if (json.success)
                {
                    $(".qq-upload-list li:last").remove();
                    var ext = fileName.split(".");
                    $.tmpl( $("#templateUploadedFile_Info"), {
                        "id" : json.file_id,
                        "ext": ext,
                        "fileName": fileName,
                        "delete_link": json.delete_link
                    }).appendTo( "#attached-files" );
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
<?php if($case['common_status']!='active' || $this->session->userdata('type') == 'supervisor'){?>
        $("#filing_deadline").datepicker({
            "dateFormat":"mm/dd/y",
            "changeMonth":true,
            "changeYear":true
        });
        $("#first_priority_date").datepicker({
            "dateFormat":"mm/dd/y",
            "changeMonth":true,
            "changeYear":true
        });
            $(".date").datepicker({
            "dateFormat":"mm/dd/y",
            "changeMonth":true,
            "changeYear":true
            });
<?php }?>
        $(".tiptip").tipTip();
<?
if ($case['common_status'] != 'hidden')
{
    ?>
    var uploader = new qq.FileUploader({
                    element:$("#file-uploader")[0],
                    template: $("#templateUploader").html(),
                    // path to server-side upload script
                    action:"<?php echo base_url(); ?>cases/upload_file/<?php echo $case['id']; ?>",
                    allowedExtensions:['msg','jpg', 'jpeg', 'png', 'gif', 'txt', 'doc', 'zip', 'pdf', 'docx', 'xls', 'pptx', 'xlsx', 'ppt','rtf'],
                    params:{
                        customer_id:"<?php echo $case['user_id']; ?>",
                        case_number:"<?php echo $case['case_number']; ?>",
                        file_type_id:19
                    },
                    onSubmit:function(id, fileName){
                        $("#tabs").tabs({ selected:2 });
                        $("#files_table").show('slow');
                        if ($.browser.msie  && (parseInt($.browser.version, 10) === 8 || parseInt($.browser.version, 10) === 9)) {
                            var ext = fileName.split(".");
                            ext = ext[1];
                            $.tmpl( $("#templateUploadedFile").html(), {
                                "id" : id,
                                "ext": ext,
                                "fileName": fileName,
                                "total": ""
                            }).appendTo( "#files_table" );
                            //trackProgress(id, fileName, 0, 100);
                        }
                    },
                    onProgress: function(id, fileName, loaded, total){

                        trackProgress(id, fileName, loaded, total);

                    },
                    onComplete:function (id, fileName, json) {
                        if (json.success)
                        {
                            if(json.rows){
                                gotCompleteMultiUpload(id, fileName, json);
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
                    }
                });
<? } ?>
        $("#generate-pdf-associates-list").click(function () {
            $.post("<?php echo base_url() ?>cases/create_pdf_associates_list/<?php echo $case['case_number'] ?>", {}, function (result) {
                if (result.result == '1') {
                    stanOverlay.setTITLE("Information!");
                    stanOverlay.setMESSAGE("The PDF has been generated!");
                    stanOverlay.SHOW();
                }
				get_files_table();
				get_associate_pdf();
        }, 'json');
        return false;
    });
        $("#escalate_case_info").click(function () {
            var message_type = $(this).attr("id")
            send_notification_to_super_visor(message_type);
        });
        $("#escalate_esimate").click(function () {
            var message_type = $(this).attr("id")
            send_notification_to_super_visor(message_type);
            return false;
        });
        $("#escalate_related").click(function () {
            var message_type = $(this).attr("id")
            send_notification_to_super_visor(message_type);
            return false;
        });

    $(".extension_needed").live('click',(function () {
        var case_id = <?php echo $case['id'] ?>;
        var country_id = $(this).attr("id");
        var extension_needed = 0;
        if ($(this).attr("checked") == "checked") {
            extension_needed = 1;
        }
        ;
        var is_checked = jQuery(this).is(':checked');
        $.post(
        "<?php echo base_url(); ?>cases/set_extension_needed/", {country_id:country_id, case_id:case_id, extension_needed:extension_needed},
        function(){
            jQuery('input.extension_needed').each(function(){
                if(jQuery(this).attr('id')==country_id){
                    jQuery(this).attr('checked',is_checked?true:false);
                }
            });

        }
    );
    }));
    $('.file_visibility').live('click', function () {
        var file_id = $(this).attr("id");
        var visibility = 0;
        if ($(this).attr("checked") == "checked") {
            visibility = 1;
        }
        ;

        $.post("<?php echo base_url(); ?>cases/set_file_visibility/", {file_id:file_id, visibility:visibility});
    });
    $('.file_fa_visibility').live('click', function () {
        var file_id = $(this).attr("id");
        var visibility = 0;
        if ($(this).attr("checked") == "checked") {
            visibility = 1;
        }
        ;

        $.post("<?php echo base_url(); ?>cases/set_file_fa_visibility/", {file_id:file_id, visibility:visibility});
    });
    
    $('.file_type').live('change', function () {
		shadow_on();
        var file_id = $(this).attr("id");
        var real_file_id = file_id.replace('ft','');
        is_move = $(this).hasClass('no_move')?false:true;
        var block_title = 'files_table';
        var parent_table = $(this).parents('table.case_files').attr('id');
        var file_type_id = $(this).val();
        $.post(
        "<?php echo base_url(); ?>cases/set_file_type/",
        {file_id:file_id, file_type_id:file_type_id, parent_table: parent_table},
        function(data){
            get_files_table(data.block_title);
            var file_types = <?=json_encode($file_types_dd)?>;
            if(data.need_to_assign){
            	showNotification({
                    message:'Please make sure to assign a country to this file type: "'+file_types[file_type_id]+'"',
                    type:"information",
                    autoClose:true,
                    duration:5
                });
            	jQuery.facebox({ ajax: '<?= base_url() ?>cases/assign_file_to_countries_form/'+real_file_id+'/<?= $case['case_number']; ?>' });
            }
        },
        "json"
    );
    });

    $(".filename").live("click", function () {
        // Hide all opened inputs before
        $(".filename").show();
        $(".rename_link_ok").hide();
        $(".rename_link_cancel").hide();
        $(".filename_input").hide();
        var filename = $(this).html();
        var span_id = $(this).attr("id");
        $(this).hide();
        $("input#inp" + span_id).css("display", "");
        $("a#rename_" + span_id).css("display", "");
        $("a#cancel_" + span_id).css("display", "");
        return false;
    });

    $("#sow_include").click(function () {
		$(this).removeClass('light-red').addClass('dark-grey');
    });

    $("#generate_sow_pdf").click(function () {
    	$.post(
        	"<?php echo base_url(); ?>cases/generate_sow_pdf/<?= $case['case_number']; ?>", 
        	{},
        	function(result){
				alert(result.result);
            },
			"json"
        );
    });

    $("#sow_text_line2").keyup(function () {
    	$("#sow_include").removeClass('dark-grey').addClass('light-red');
    });

    $(".rename_link_cancel").live("click", function () {
        var file_id = $(this).attr("id").substr(7);
        $("span#" + file_id).show();
        $("input#inp" + file_id).css("display", "none");
        $(".rename_link_ok").css("display", "none");
        $(".rename_link_cancel").css("display", "none");
        $(this).hide();
    });

    $(".rename_link_ok").live("click", function () {
        var file_id = $(this).attr("id").substr(7);
        var filename = $("input#inp" + file_id).val();
        $.post("<?php echo base_url(); ?>cases/rename_file/" + file_id, {filename:filename});
        $("span#" + file_id).show();
        var extension = $("input[name='ext" + file_id + "']").val();
        $("span#" + file_id).html(filename + "." + extension);
        $("input#inp" + file_id).hide();
        $(".rename_link_cancel").css("display", "none");
        $(this).hide();
    });

    $("button#add_new_note, button#add_new_note_est, button#add_client_note, button#add_client_note_est, button.tracker_add_note").click(function () {
        var is_client_note = "0";
        var button_class = jQuery(this).attr('class');
        if ($(this).attr("id") == "add_new_note") {
            var note_text = $("#new_note").val();
        } else if ($(this).attr("id") == "add_client_note") {
            is_client_note = "1";
            var note_text = $("#new_note").val();
        }
        else if ($(this).attr("id") == "add_client_note_est") {
            is_client_note = "1";
            var note_text = $("#new_note_est").val();
        }
        else if ($(this).hasClass('tracker_add_note')) {
            var note_text = $(this).prevAll('.tracker_note').val();
        }
        else {
            var note_text = $("#new_note_est").val();
        }
        if(note_text == ''){
            stanOverlay.setTITLE("Error");
            stanOverlay.setMESSAGE("Please, type the note text.");
            stanOverlay.SHOW();
            return;
        }
        var client_user_id = "<?php echo $case['user_id'] ?>";
        $.post("<?php echo base_url(); ?>cases/add_note_for_case/<?php echo $case['case_number']; ?>", {note_text:note_text, is_client_note:is_client_note, client_user_id:client_user_id}, function (result) {
            var row_class = "";
            var new_row = "<tr class='" + row_class + "'><td>" + result['note'] + "</td><td class='username'>" + result['username'] + "</td><td class='created_at'>" + result['created_at'] + "</td><td>" + result['delete_link'] + "</td></tr>";
            if (result.is_client_note == "1") {
                row_class = "client-note-row";
                var new_row = "<tr class='" + row_class + "'><td>" + result['note'] + "</td><td class='username'>" + result['username'] + "</td><td class='created_at'>" + result['created_at'] + "</td><td>" + result['delete_link'] + "</td></tr>";
                $('#notes_table, #estimates-notes tbody').prepend(new_row);
            }
            else {
                if ($('#notes_table, #estimates-notes tbody').find("td.case-note").length > 0) {
                    $('#notes_table, #estimates-notes tbody').find("td.case-note:first").parent("tr").before(new_row);
                } else {
                    $('#notes_table, #estimates-notes tbody').append(new_row);
                }
            }

            if(button_class == 'tracker_add_note'){
                jQuery('.notice_form').hide();
            }
            stanOverlay.setTITLE("Note added");
            stanOverlay.setMESSAGE("Note added");
            stanOverlay.SHOW();
            result = null;
        }, 'json');
    });

    /* Select email type */

    $("div.item").click(function () {
        /* Clear email text in the textarea and another fields */
        $("#email-textarea").val("");
        $("#to").val("");
        $("#cc").val("");
        $("#subject").val("");


        $("#attached-files").html("<img src='<?php echo base_url(); ?>assets/images/i/loading.gif' />");

        $("div.item").removeClass("selected");
        $(this).addClass("selected");

        /* AJAX code goes here */

        /* A list of Attached Files */
        var email_type = $(this).children("a").attr("id");
        var case_id = <?php echo $case['id']; ?>;
        var case_number = "<?php echo $case['case_number']; ?>";
        var case_type_id = <?php echo $case['case_type_id']; ?>;
        var cc = "<?php echo $contacts; ?>>";

        $("#to-box").hide();
        if (email_type != "new-email") {
            $.post("<?php echo base_url(); ?>cases/get_list_files_by_email_type/", {case_number:case_number, email_type:email_type}, function (result) {
                $("#attached-files").html(result.attached_files);
                $("#zip_hash").val('');
                $("#zip_hash").val(result.zip_hash);
                if (email_type != "translation_request") {
                    $("#email-countries").html(result.countries);
                }
                var zip_hash = $("#zip_hash").val();
                result = null;

                if (email_type == "translation_request") {
                    $("#email-countries").html("");
                    $("#to-box").show();
                    $("#email-textarea").val("");

                    $.post("<?php echo base_url(); ?>cases/get_email_text/", {translation_needed:"0", extension_needed:"0", case_type_id:case_type_id, country_id:0, email_type:email_type, case_id:case_id, case_number:case_number, cc:cc, zip_hash:zip_hash}, function (nresult) {
                        $("#email-textarea").val(nresult.text);
                        $("input#to").val(nresult.to);
                        $("input#subject").val(nresult.subject);
                        $("input#cc").val(nresult.cc);
                        nresult = null;
                    }, 'json');
                }
                else if (email_type == "document-instruction") {

                    $("#email-countries").html("");
                    $("#to-box").show();
                    $("#email-textarea").val("");

                    $.post("<?php echo base_url(); ?>cases/get_email_text/", {translation_needed:"0", extension_needed:"0", case_type_id:case_type_id, country_id:0, email_type:email_type, case_id:case_id, case_number:case_number, cc:cc, zip_hash:zip_hash}, function (nresult) {
                        $("#email-textarea").val(nresult.text);
                        $("input#to").val(nresult.to);
                        $("input#subject").val(nresult.subject);
                        $("input#cc").val(nresult.cc);
                        nresult = null;
                    }, 'json');
                } else if (email_type == "filing-confirmation") {
                    $("#email-countries").html("");
                    $("#to-box").show();
                    $("#email-textarea").val("");

                    $.post("<?php echo base_url(); ?>cases/get_email_text/", {translation_needed:"0", extension_needed:"0", case_type_id:case_type_id, country_id:0, email_type:email_type, case_id:case_id, case_number:case_number, cc:cc, zip_hash:zip_hash}, function (nresult) {
                        $("#email-textarea").val(nresult.text);
                        $("input#to").val(nresult.to);
                        $("input#subject").val(nresult.subject);
                        $("input#cc").val(nresult.cc);
                        nresult = null;
                    }, 'json');
                } else if (email_type = 'fa-intake') {
                    $("input#to").val('fa@zenfile.com');
                }
            }, 'json');
        }
        if (email_type == "new-email") {
            $("#to-box").show();
            $("#email-textarea").val("");
            $("#attached-files").html("");
            $("#email-countries").html("");
        }
    });

    $("div.country-box").live('click', function () {
        $("div.country-box").removeClass("selected");
        $(this).addClass("selected");
        var case_id = <?php echo $case['id']; ?>;
        var case_number = "<?php echo $case['case_number']; ?>";
        var country_id = $(this).children("a").attr("id");
        var email_type = $("div#type-emails div.selected a").attr("id");
        var case_type_id = <?php echo $case['case_type_id']; ?>;
        var extension_needed = $("input[name='extension_needed_" + country_id + "']").val();
        var translation_needed = $("input[name='translation_needed_" + country_id + "']").val();
        var country_association_id = $(this).children("input[name='country_association_id_" + country_id + "']").val();

        var files = new Array();
        $("#attached-files input:hidden").each(function (index) {
            files.push($(this).val());
        });

        /* AJAX code goes here */
        $.post("<?php echo base_url(); ?>cases/get_email_text/",
        {
            translation_needed:translation_needed,
            extension_needed:extension_needed,
            case_type_id:case_type_id,
            country_id:country_id,
            email_type:email_type,
            case_id:case_id,
            case_number:case_number,
            files:files,
            country_association_id:country_association_id
        },
        function (result) {
            $("#email-textarea").val(result.text);
            $("input#to").val(result.to);
            if (email_type == "fa-request") {
                $("input#cc").val("");
                $(".attachtocountry").remove();
                $("#attached-files").append(result.attached_files);
            }
            $("input#subject").val(result.subject);
            $("#zip_hash").val(result.zip_hash);
            $("#to-box").show();

            result = null;
        }, 'json');
    });

    $("#attach-from-case-button").click(function () {
    	jQuery.facebox({ ajax: '<?= base_url() ?>cases/attach_files_from_case/<?= $case['id']; ?>' });
    });


    $("a#send-email").click(function () {

        var text = $("#email-textarea").val();
        if (text == "") {
            alert("Your email is empty!");
            return false;
        }
        var case_number = "<?php echo $case['case_number']; ?>";
        var country_id = $("div#email-countries div.selected a").attr("id");
        var to = $("input#to").val();
        var cc = $("input#cc").val();
        var subject = $("input#subject").val();
        var email_type = $("div#type-emails div.selected a").attr("id");
        var zip_hash = $("input#zip_hash").val();
        var files = new Array();
        $("#attached-files input:hidden").each(function (index) {
            files.push($(this).val());
        });
        $('.email-uploader-position .qq-upload-list').html('');
        $.post(
			"<?php echo base_url(); ?>cases/send_email/",
			{
    			cc:cc,
    			case_number:case_number,
    			text:text,
    			to:to,
    			files:files,
    			subject:subject,
    			email_type:email_type,
                zip_hash: zip_hash,
    			country_id:country_id
    		},
    		function (result) {
	            showNotification({
	                message:result.text,
	                type:"information",
	                autoClose:true,
	                duration:5
	            });
	            if(result.result =='filing-confirmation'){
					for(index in result.countries){
						$('button[id='+result.countries[index].id + '][name="fr_sent"]').removeClass('tracker_inactive').addClass('tracker_required').val(result.countries[index].date);
					}
	            }
	            if(result.result =='fa-request'){
	                $('button[id='+result.country_id + '][name="fi_requests_sent_fa"]').removeClass('tracker_inactive').addClass('tracker_required').val(result.date);
	            }
	            result = null;
	            $("div#email-countries a#" + country_id).parent("div.country-box").addClass("sent");
			},
			"json"
		);
    });

    $("a#case-info-view-more").click(function () {
        if ($("td.unnecessary").hasClass("unnecessary")) {
            $("td.unnecessary").addClass("necessary");
            $("td.unnecessary").removeClass("unnecessary");
        } else {
            $("td.necessary").addClass("unnecessary");
            $("td.unnecessary").removeClass("necessary");
        }

    });

    /* Facebook Like AutoComplete Input */
    $("#new_country_id").tokenInput(<?php echo json_encode($countries_by_case_type_output); ?>, {
        propertyToSearch:"country",
        preventDuplicates:true
    });

    /* Send notification email for "Filing Confirmation" tab*/
    $("#send_notification_email_filing_confirmation").click(function () {
        $("input[name='notification_email_country_id']").val("all");
        $("#notification_email_filing_confirmation").show();
    });
    $(".notification_email_open").click(function () {
        var country_id = $(this).attr("id");
        $("input[name='notification_email_country_id']").val(country_id);
        $("span.folder").removeClass("current");
        $(this).parent("span.folder").addClass("current");


        var extension_needed = "";
        var translation_needed = "";
        var case_id = "<?php echo $case['id'] ?>";
        var case_number = "<?php echo $case['case_number'] ?>";
        var case_type_id = "<?php echo $case['case_type_id'] ?>";
        var email_type = "filing-confirmation";

        $.post("<?php echo base_url(); ?>cases/get_email_text/", {case_id:case_id, case_number:case_number, country_id:country_id, case_type_id:case_type_id, email_type:email_type, extension_needed:extension_needed, translation_needed:translation_needed}, function (result) {
            $("#notification_email_textarea").val(result.text);
            $("#notification_email_subject").val(result.subject);
        }, "json");

        $("#notification_email_filing_confirmation").show();
    });

    $("#notification_email_send_button").click(function () {
        var text = $("#notification_email_textarea").val();
        if (text == "") {
            alert("Your email is empty!");
            return false;
        }
        var case_number = "<?php echo $case['case_number']; ?>";
        var country_id = $("input[name='notification_email_country_id']").val();
        var to = $("input#notification_email_to").val();
        var cc = $("input#notification_email_cc").val();
        var subject = $("input#notification_email_subject").val();

        $.post("<?php echo base_url(); ?>cases/send_notification_email/", {cc:cc, case_number:case_number, text:text, to:to, subject:subject, country_id:country_id}, function (result) {
            showNotification({
                message:result.text,
                type:result.type,
                autoClose:true,
                duration:5
            });
            if (result.type != "error") {
                $("span.folder a#" + country_id + " img").attr("src", "<?php echo base_url(); ?>assets/images/i/mail-sent.png");
            }
            result = null;
        }, 'json');
    });

    /* An array of common footnotes */
    var common_footnotes = new Array();
<?php
$footnotes_dropdown = '';

if (check_array($common_footnotes))
{
    $footnotes_dropdown = '<ul style="display: none;">';
    foreach ($common_footnotes as $key => $footnote)
    {

        $footnotes_dropdown .= '<li>' . $footnote["text"] . '</li>';
        ?>
                    common_footnotes.push("<?php echo $footnote['text'] ?>");
        <?php

    }
    $footnotes_dropdown .= '</ul>';
}
if ($case['is_intake'])
{
    $related_type = 'active';
} else
{
    $related_type = 'estimating-estimate';
}
?>
<?php if($case['common_status']!='active' || $this->session->userdata('type') == 'supervisor'){?>
    /* Delete country from estimate */
    $(".estimate_delete_country").live("click", function () {
        $(this).closest("tr").remove();

        var country_record_id = $(this).attr("id");
        $.post("<?php echo base_url(); ?>estimates/delete_country_from_estimate/<?php echo $case['case_number'] ?>", {country_record_id:country_record_id}, function (result) {
            result = null;
        })

        calculate_estimate_total();
    });
        <?php }?>
    var case_currency = "<?php echo ($case['case_currency'] == 'usd') ? '$' : '€' ?>";
    $(".cancel_change_fee").live("click", function () {
        var previous_fee_value = $(this).attr("id");
        $(this).closest(".change-fee-box").parent("td").children(".fee").html(case_currency + previous_fee_value);
        $(this).closest(".change-fee-box").parent("td").children(".fee").show();
        $(this).parent(".change-fee-box").remove();
    });

    $(".lock-fee").live("click", function () {
        if ($(this).hasClass('unlock_fee') == true) {

            var unlock = 1;
        } else {
            var unlock = 0;
        }
        var fee_value = $(this).closest(".change-fee-box").children("input[name='fee_value']").val();

        if ($(this).parent().parent().parent().find(".delete_sub_country").length > 0) {
            var estimate_country_id = $(this).parent().parent().parent().find(".delete_sub_country").attr('rel');
        } else {
            var estimate_country_id = $(this).parent().parent().parent().find(".estimate_delete_country").attr('rel');
        }


        var country_id = $(this).closest(".change-fee-box").parent("td").children("input[type='hidden']").attr("name").replace("locked_", "").replace("_filing", "").replace("_translation", "").replace("_official", "");
        var fee_type = $(this).closest(".change-fee-box").parent("td").children(".fee").attr("id");
        console.log(fee_type);
        console.log(country_id);
        $(this).closest(".change-fee-box").parent("td").children("input[type='hidden'][name='" + fee_type + "_fee_" + country_id + "']").val(fee_value);

        /* Save filing fee changes in `customers_fees` table */

        var case_type_id = "<?php echo $case['case_type_id'] ?>";
        var fee_level = $("select#fee_level option:selected").val();
        var case_id = "<?php echo $case['id'] ?>";
        if (fee_type == "filing") {
            $.post("<?php echo base_url() ?>estimates/save_filing_fee_for_customer/<?php echo $case['user_id'] ?>", {estimate_country_id: estimate_country_id , unlock: unlock, case_id: case_id , fee_value:fee_value, country_id:country_id, case_type_id:case_type_id, fee_level:fee_level}, function (result) {
            });
            $(this).closest(".change-fee-box").parent("td").children(".fee").html(case_currency + fee_value).show();
        } else if (fee_type == "translation") {
            var case_number = "<?php echo $case['case_number'] ?>";
            var number_words = $("input[name='number_words']").val();
            var estimate_currency = $("select#estimate_currency option:selected").val();
            $.post("<?php echo base_url() ?>estimates/get_translation_fee/", {unlock: unlock, case_number:case_number, country_id:country_id, estimate_fee_level:fee_level, translation_rate:fee_value, number_words:number_words, estimate_currency:estimate_currency}, function (new_fee_value) {
                $("input[name='translation_rate_" + country_id + "']").closest("td").children("span.fee").html(new_fee_value).show();
                var new_fee_value_w = new_fee_value.replace("$", "").replace("€", "");
                $("input[name='translation_fee_" + country_id + "']").val(new_fee_value_w);
            });
            $.post("<?php echo base_url() ?>estimates/save_translation_fee_for_customer/<?php echo $case['user_id'] ?>", {estimate_country_id: estimate_country_id ,unlock: unlock, case_id:case_id, fee_value:fee_value, country_id:country_id, case_type_id:case_type_id, fee_level:fee_level}, function (result) {
            });
        } else {
            $.post("<?php echo base_url() ?>estimates/save_locked_official_fee_for_country/<?php echo $case['user_id'] ?>", {estimate_country_id: estimate_country_id , unlock: unlock, case_id: case_id , fee_value:fee_value, country_id:country_id, case_type_id:case_type_id, fee_level:fee_level}, function (result) {
            });
            $(this).closest(".change-fee-box").parent("td").children(".fee").html(case_currency + fee_value).show();
        }
        $(this).closest(".change-fee-box").parent("td").children(".fee").after("<span class='locked-value'>&nbsp;<img src='<?php echo base_url(); ?>assets/images/i/lock.png'/></span>");


        $(this).parent(".change-fee-box").remove();

        calculate_estimate_total();
    });

<?php if(empty($not_editable)){?>
    $(".fee").single_double_click(function (e) {

        // If there is an existing SUP tag inside then return false
        if ($(this).closest("td").children("sup").length != 0) {
            stanOverlay.setTITLE("Ooops");
            stanOverlay.setMESSAGE("There is a footnote for this price!");
            stanOverlay.SHOW();
            return false;
        }
        var count_footnotes = parseInt($("input[name='count_footnotes']").val());
        var new_count_footnotes = parseInt(count_footnotes + 1);
        var country_id_temp = $(this).closest("td").children("input[type='hidden']").attr("name");
        country_id = country_id_temp.substr(-3);
        country_id = country_id.replace("_", "");
        var fee_type = $(this).attr("id");

        $(this).after("<sup id='" + country_id + "-" + fee_type + "'>" + new_count_footnotes + "</sup>");
        var v_new_footnote = "<div class='footnote-row'><div class='footnote-number'>" + new_count_footnotes + "</div><div class='delete-footnote-box'><a href='javascript:void(0);' class='delete-footnote'><img src='<?php echo base_url() ?>assets/images/i/delete.png' title='Remove footnote' class='tiptip' /></a></div><div class='footnote-text'><input type='hidden' name='fee_type[]' value='" + fee_type + "' /><input type='hidden' name='country_id[]' value='" + country_id + "' /><textarea name='footnotes[]' cols='50' rows='5'>" + common_footnotes[0] + "</textarea></div><a href='javascript:void(0);' class='footnote-arrow' id='" + new_count_footnotes + "'>&nbsp;</a><div class='footnotes-dropdown-list footnotes-dropdown-" + new_count_footnotes + "'></div></div>";
        $("#footnotes-box").append(v_new_footnote);
        /*var first_list = $(".footnotes-dropdown-list").html();*/
        var first_list = '<?php echo $footnotes_dropdown; ?>';
        $("div.footnotes-dropdown-" + new_count_footnotes).html(first_list);
        $("input[name='count_footnotes']").val(count_footnotes + 1);
    }, function () {
        // If there is an existing SPAN.locked-value tag inside then remove lock
        if ($(this).closest("td").children(".locked-value").length != 0) {
            $(this).closest("td").children(".locked-value").remove();
            $(this).closest("td").children("input.locked").remove();
            return false;
        }
        /* Fee type */
        var fee_type = $(this).attr("id");

        $(this).closest("td").children(".locked").remove();
        var current_fee_value = parseFloat($(this).html().replace("$", "").replace("€", ""));
        var country_id = $(this).closest("td").children("input[type='hidden']").attr("name").substr(-3);
        country_id = country_id.replace("_", "");
        var fee_type = $(this).attr("id");

        if (fee_type == 'translation') {
            show_fee_value = $(this).closest("td").children("input[type='hidden'][name='translation_rate_" + country_id + "']").val();
        } else {
            show_fee_value = current_fee_value;
        }

        $(this).after("<span class='change-fee-box'><input type='text' name='fee_value' value='" + show_fee_value + "' style='width: 35px;'/>&nbsp;<a class='lock-fee' href=javascript:void(0);>Lock</a>&nbsp;|&nbsp;<a href=javascript:void(0); class='cancel_change_fee' id='" + current_fee_value + "'>Cancel</a> | <a href=javascript:void(0); class='lock-fee unlock_fee' id='" + current_fee_value + "'>Unlock</a></span>");
        $(this).after("<input type='hidden' class='locked' name='locked_" + parseInt(country_id) + "_" + fee_type + "' value='1' />");
        $(this).hide();
    });
        <?php }?>
    $(".footnote-arrow").live("click", function () {
        /* Close all another dropdowns */

        var current_footnote_index = parseInt($(this).attr("id"));
        if ($(".footnotes-dropdown-" + current_footnote_index).children("ul").css("display") == "block") {
            $(".footnotes-dropdown-" + current_footnote_index).children("ul").hide();
        } else {
            $(".footnotes-dropdown-list").children("ul").css("display", "none");
            $(".footnotes-dropdown-" + current_footnote_index).children("ul").css("display", "block");
        }

    });

    $(".footnotes-dropdown-list li").live("click", function () {
        $(this).closest(".footnotes-dropdown-list").parent(".footnote-row").children(".footnote-text").children("textarea").val($(this).text());
        $(this).parent("ul").css("display", "none");
    });

    /* Removing footnote */
    $(".delete-footnote").live("click", function () {

        var country_id = $(this).closest(".footnote-row").children(".footnote-text").children("input[name='country_id\[\]']").val();
        var fee_type = $(this).closest(".footnote-row").children(".footnote-text").children("input[name='fee_type\[\]']").val();
        $("sup#" + country_id + "-" + fee_type).remove();

        $(this).parent(".delete-footnote-box").parent(".footnote-row").remove();

        var count_footnotes = $("input[name='count_footnotes']").val();
        count_footnotes = count_footnotes - 1;
        $("input[name='count_footnotes']").val(count_footnotes);

        /* Recalculate numbers of footnote */
        var footnote_number = 1;
        $(".footnote-number").each(function () {

            var country_id = $(this).closest(".footnote-row").children(".footnote-text").children("input[name='country_id\[\]']").val();
            var fee_type = $(this).closest(".footnote-row").children(".footnote-text").children("input[name='fee_type\[\]']").val();
            $("sup#" + country_id + "-" + fee_type).html(footnote_number);

            $(this).html(footnote_number);
            footnote_number = footnote_number + 1;
        });
    });

    /* Recalculate button */
    $("a#recalculate_estimate").click(function () {
        if (need_calculations == 1) {
            need_calculations = 0;
        } else {
            need_calculations = 1;
        }
        $("input[name='claims']").change();
        return false;
    });
    $(".price-adjust").change(function () {
        calculate_estimate_total();
    });
    function calculate_estimate_total() {
        $("div#fees-table-box").html("<center><img src='<?php echo base_url() ?>assets/images/i/loading-blue.gif'/></center>");

        var estimate_fee_level = $("select[name='estimate_fee_level'] option:selected").val();
        var inflate_official_fee = $("input[name='inflate_official_fee']").val();
        var claims = $("input[name='claims']").val();
        var number_words = $("input[name='number_words']").val();
        var pages = $("input[name='pages']").val();
        var pages_sequence_listing = $("input[name='pages_sequence_listing']").val();
        var search_report_location = $("select[name='search_report_location']").val();
        var estimate_currency = $("select[name='estimate_currency']").val();
        var entity = $("select[name='entity']").val();
        var number_priorities_claimed = $("input[name='number_priorities_claimed']").val();
        var number_pages_drawings = $("input[name='number_pages_drawings']").val();
        var number_words_in_claims = $("input[name='number_words_in_claims']").val();

        $.post("<?php echo base_url() ?>estimates/get_estimate_table/<?php echo $case['case_number'] ?>", {need_calculations: need_calculations , estimate_fee_level:estimate_fee_level, inflate_official_fee:inflate_official_fee, claims:claims, pages:pages, pages_sequence_listing:pages_sequence_listing, search_report_location:search_report_location, number_words:number_words, estimate_currency:estimate_currency, entity:entity, number_priorities_claimed:number_priorities_claimed, number_pages_drawings:number_pages_drawings, number_words_in_claims:number_words_in_claims}, function (result) {
            $("div#fees-table-box").html(result);
            $("div#fees-table-box .date").datepicker({
                            "dateFormat":"mm/dd/y",/*mm-dd-yy*/
                            "changeMonth":true,
                            "changeYear":true
                        });
        <?php if(empty($not_editable)) { ?>
            $(".fee").single_double_click(function (e) {

                // If there is an existing SUP tag inside then return false
                if ($(this).closest("td").children("sup").length != 0) {
                    stanOverlay.setTITLE("Ooops");
                    stanOverlay.setMESSAGE("There is a footnote for this price!");
                    stanOverlay.SHOW();
                    return false;
                }
                var count_footnotes = parseInt($("input[name='count_footnotes']").val());
                var new_count_footnotes = parseInt(count_footnotes + 1);
                var country_id_temp = $(this).closest("td").children("input[type='hidden']").attr("name");
                country_id = country_id_temp.substr(-3);
                country_id = country_id.replace("_", "");
                var fee_type = $(this).attr("id");

                $(this).after("<sup id='" + country_id + "-" + fee_type + "'>" + new_count_footnotes + "</sup>");
                var v_new_footnote = "<div class='footnote-row'><div class='footnote-number'>" + new_count_footnotes + "</div><div class='delete-footnote-box'><a href='javascript:void(0);' class='delete-footnote'><img src='<?php echo base_url() ?>assets/images/i/delete.png' title='Remove footnote' class='tiptip' /></a></div><div class='footnote-text'><input type='hidden' name='fee_type[]' value='" + fee_type + "' /><input type='hidden' name='country_id[]' value='" + country_id + "' /><textarea name='footnotes[]' cols='50' rows='5'>" + common_footnotes[0] + "</textarea></div><a href='javascript:void(0);' class='footnote-arrow' id='" + new_count_footnotes + "'>&nbsp;</a><div class='footnotes-dropdown-list footnotes-dropdown-" + new_count_footnotes + "'></div></div>";
                $("#footnotes-box").append(v_new_footnote);
                var first_list = '<?php echo $footnotes_dropdown; ?>';
                $("div.footnotes-dropdown-" + new_count_footnotes).html(first_list);
                $("input[name='count_footnotes']").val(count_footnotes + 1);
            }, function () {

                // If there is an existing SPAN.locked-value tag inside then remove lock

                $(this).closest("td").children(".locked").remove();
                var current_fee_value = parseFloat($(this).html().replace("$", "").replace("€", ""));
                var country_id = $(this).closest("td").children("input[type='hidden']").attr("name").substr(-3);
                country_id = country_id.replace("_", "");
                var fee_type = $(this).attr("id");
                $(this).after("<span class='change-fee-box'><input type='text' name='fee_value' value='" + current_fee_value + "' style='width: 35px;'/>&nbsp;<a class='lock-fee' href=javascript:void(0);>Lock</a>&nbsp;|&nbsp;<a href=javascript:void(0); class='cancel_change_fee' id='" + current_fee_value + "'>Cancel</a> | <a class='lock-fee unlock_fee' href=javascript:void(0);>Unlock</a></span>");
                $(this).after("<input type='hidden' class='locked' name='locked_" + parseInt(country_id) + "_" + fee_type + "' value='1' />");
                $(this).hide();
            });
            <?php } ?>
            result = null;
        });
    }

    $("#generate_estimate_pdf").click(function () {
        var estimate_saved_by_pm = "<?php echo $case['estimate_saved_by_pm'] ?>";
        if (estimate_saved_by_pm != "")
        {
            $.post("<?php echo base_url() ?>cases/generate_estimate_pdf/<?php echo $case['case_number'] ?>", {}, function (result) {
                if (result.result == '1') {
                    stanOverlay.setTITLE("Information!");
                    stanOverlay.setMESSAGE("PDF has been generated.");
                    stanOverlay.SHOW();
                }
                $("#estimate-control a.button").removeClass("disabled");

                get_estimate_pdf();
                result = null;
            }, 'json');
            return false;
        }
        else
        {
            stanOverlay.setTITLE("Information!");
            stanOverlay.setMESSAGE("Please save an estimate first.");
            stanOverlay.SHOW();

            return false;
        }
    });

	function get_associate_pdf() {
            $.post("<?php echo base_url() ?>cases/get_associate_pdf/<?php echo $case['case_number'] ?>", {case_id: <?php echo $case['id'] ?>}, function (result) {
                if (result.result == "ok") {
                    window.location.href = "<?php echo base_url() ?>cases/view_file/" + result.file_id;
                } else {
                    stanOverlay.setTITLE("Information!");
                    stanOverlay.setMESSAGE("There is no associate PDF for the current case!");
                    stanOverlay.SHOW();
                }
                result = null;
            }, 'json');
            return false;
        }


    function get_estimate_pdf() {
        $.post("<?php echo base_url() ?>cases/get_estimate_pdf/<?php echo $case['case_number'] ?>", {case_id: <?php echo $case['id'] ?>}, function (result) {
            if (result.result == "ok") {
                window.location.href = "<?php echo base_url() ?>cases/view_file/" + result.file_id;
            } else {
                stanOverlay.setTITLE("Information!");
                stanOverlay.setMESSAGE("There is no estimate PDF for the current case!");
                stanOverlay.SHOW();

            }
            result = null;

        }, 'json');
        return false;
    }

    function send_notification_to_super_visor(message_type) {
            $.post("<?php echo base_url() ?>cases/send_notification_to_supervisor/<?php echo $case['case_number'] ?>", {message_type: message_type}, function (back) {
                if (back.result == "ok") {
                    stanOverlay.setTITLE("Information!");
                    stanOverlay.setMESSAGE("your request was sent to Supervisor");
                    stanOverlay.SHOW();
                }else{
                    stanOverlay.setTITLE("Information!");
                    stanOverlay.setMESSAGE("You have no Supervisor yet!");
                    stanOverlay.SHOW();
                }
                back = null;
            }, 'json');
            return false;
        }
    $("#save_customer_fees").click(function () {
        var customer_countries = new Array();
        $("input[name='customer_countries\[\]']").each(function () {
            customer_countries.push($(this).val());
        });
        var case_types = new Array();
        $("input[name='case_types\[\]']").each(function () {
            case_types.push($(this).val());
        });
        var filing_fee_level_1 = new Array();
        $("input[name='filing_fee_level_1\[\]']").each(function () {
            filing_fee_level_1.push($(this).val());
        });
        var filing_fee_level_2 = new Array();
        $("input[name='filing_fee_level_2\[\]']").each(function () {
            filing_fee_level_2.push($(this).val());
        });
        var filing_fee_level_3 = new Array();
        $("input[name='filing_fee_level_3\[\]']").each(function () {
            filing_fee_level_3.push($(this).val());
        });
        var translation_rate_level_1 = new Array();
        $("input[name='translation_rate_level_1\[\]']").each(function () {
            translation_rate_level_1.push($(this).val());
        });
        var translation_rate_level_2 = new Array();
        $("input[name='translation_rate_level_2\[\]']").each(function () {
            translation_rate_level_2.push($(this).val());
        });
        var translation_rate_level_3 = new Array();
        $("input[name='translation_rate_level_3\[\]']").each(function () {
            translation_rate_level_3.push($(this).val());
        });
        var official_fee = new Array();
        $("input[name='official_fee\[\]']").each(function () {
            official_fee.push($(this).val());
        });
        var extension_needed_fee = new Array();
        $("input[name='extension_needed_fee\[\]']").each(function () {
            extension_needed_fee.push($(this).val());
        });
        var sequence_listing_fee = new Array();
        $("input[name='sequence_listing_fee\[\]']").each(function () {
            sequence_listing_fee.push($(this).val());
        });

        var request_examination = new Array();
        $("input[name='request_examination\[\]']").each(function () {
            request_examination.push($(this).val());
        });
        var number_claims_above_additional_fees = new Array();
        $("input[name='number_claims_above_additional_fees\[\]']").each(function () {
            number_claims_above_additional_fees.push($(this).val());
        });
        var fee_additional_claims = new Array();
        $("input[name='fee_additional_claims\[\]']").each(function () {
            fee_additional_claims.push($(this).val());
        });
        var additional_fee_for_claims = new Array();
        $("input[name='additional_fee_for_claims\[\]']").each(function () {
            additional_fee_for_claims.push($(this).val());
        });
        var number_pages_above_additional_fees = new Array();
        $("input[name='number_pages_above_additional_fees\[\]']").each(function () {
            number_pages_above_additional_fees.push($(this).val());
        });
        var fee_additional_pages = new Array();
        $("input[name='fee_additional_pages\[\]']").each(function () {
            fee_additional_pages.push($(this).val());
        });
        var number_priorities_claimed_with_no_additional_charge = new Array();
        $("input[name='number_priorities_claimed_with_no_additional_charge\[\]']").each(function () {
            number_priorities_claimed_with_no_additional_charge.push($(this).val());
        });
        var charge_per_additional_claimed = new Array();
        $("input[name='charge_per_additional_claimed\[\]']").each(function () {
            charge_per_additional_claimed.push($(this).val());
        });
        var number_free_pages_drawing = new Array();
        $("input[name='number_free_pages_drawing\[\]']").each(function () {
            number_free_pages_drawing.push($(this).val());
        });
        var charge_per_additional_pages_of_drawing = new Array();
        $("input[name='charge_per_additional_pages_of_drawing\[\]']").each(function () {
            charge_per_additional_pages_of_drawing.push($(this).val());
        });
        var claim_number_threshold_for_additional_fee = new Array();
        $("input[name='claim_number_threshold_for_additional_fee\[\]']").each(function () {
            claim_number_threshold_for_additional_fee.push($(this).val());
        });
        var page_number_treshold_for_additional_fee = new Array();
        $("input[name='page_number_treshold_for_additional_fee\[\]']").each(function () {
            page_number_treshold_for_additional_fee.push($(this).val());
        });
        var translation_rates_for_claims = new Array();
        $("input[name='translation_rates_for_claims\[\]']").each(function () {
            translation_rates_for_claims.push($(this).val());
        });


        $.post("<?php echo base_url() ?>cases/update_customer_fees/<?php echo $case['user_id'] ?>",
        {
            case_number: '<?php echo $case['case_number'] ?>' ,
            customer_countries:customer_countries,
            case_types:case_types,
            filing_fee_level_1:filing_fee_level_1,
            filing_fee_level_2:filing_fee_level_2,
            filing_fee_level_3:filing_fee_level_3,
            translation_rate_level_1:translation_rate_level_1,
            translation_rate_level_2:translation_rate_level_2,
            translation_rate_level_3:translation_rate_level_3}, function (result) {
            showNotification({
                message:result.text,
                type:result.type,
                autoClose:true,
                duration:5
            });
            result = null;
            window.location = '<?php echo base_url() ?>cases/view/<?php echo $case['case_number'] ?>';
        }, 'json');

    });

    $("#save_estimate_link").click(function () {
        $("form#save_estimate_form").submit();
    });

    $("#empty-div").dialog({ autoOpen:false, width:"740px" });

    $("#make_available_to_bdv").click(function () {
        var available = $(this).attr("checked") ? "1" : "0";
        $.post("<?php echo base_url() ?>estimates/make_available_for_bdv/<?php echo $case['case_number'] ?>", {available:available});
    });

    $("#make_available_to_client").click(function () {
        var available = $(this).attr("checked") ? "1" : "0";
        $.post("<?php echo base_url() ?>estimates/make_available_for_client/<?php echo $case['case_number'] ?>", {available:available});
    });
    $("#ignore_country").click(function () {
        var status = "1";
        var case_id = "<?php echo $case['case_number'] ?>";
        var type = "<?php echo $related_type ?>";
        var is_intake = "<?php echo $case['is_intake'] ?>";
        $.post("<?php echo base_url() ?>cases/change_related_hidden", {status:status,case_id:case_id,type:type, is_intake: is_intake},function (result) {
            showNotification({
                message:result.text,
                type:result.type,
                autoClose:true,
                duration:5

            });
            result = null;
            setTimeout(redirect_delete , 5000);
            function redirect_delete() {
                window.location = '<?php echo base_url() ?>dashboard/';
            }
        }, 'json');

    });
    $("#approve_country").click(function () {
        var status = "0";
        var case_id = "<?php echo $case['case_number'] ?>";
        var type = "<?php echo $related_type ?>";
        var is_intake = "<?php echo $case['is_intake'] ?>";
        $.post("<?php echo base_url() ?>cases/change_related_hidden", {status:status,case_id:case_id,type:type, is_intake: is_intake},function (result) {

            showNotification({
                message:result.text,
                type:result.type,
                autoClose:true,
                duration:5
            });
            result = null;
            setTimeout(redirect_approve , 5000);
            function redirect_approve() {
                window.location = "<?php echo base_url() ?>cases/view/" + case_id;
            }
        }, 'json');

    });
    $('.parse_data').click(function(){
        Loader.show();
        $.ajax({
            type: "POST",
            cache: false,
            url: "/client/wp_engine/load_case_data" ,
            data: {
                application_number: $('input[name=application_number]').val() ,
                wo_number: $('input[name=wo_number]').val() ,
                parse: true ,
                reparse: true
            } ,
            datatype: 'json' ,
            success: (function(data) {
                data_json = jQuery.parseJSON(data);
                if (data_json[0]['isError'] == 1) {
                    showNotification({
                        message:'No Record with This Application Number Exist on WIPO',
                        autoClose:true,
                        duration:5
                    });
                    Loader.hide();
                    return false;
                }

                var object_full = jQuery.parseJSON(data);
                var object = object_full['data']['case_data'];

                $('input[name=title]').val(object['title']);
                $('input[name=applicant]').val(object['applicant']);
                $('input[name=application_number]').val(object['pct_number']);

                // CC skipped
                $('input[name=wo_number]').val(object['wo_number']);
                $('input[name=first_priority_date]').val(object['first_priority_date']);
                $('input[name=international_filing_date]').val(object['international_filing_date']);
                $('input[name=' + 30 + '_month_filing_deadline]').val(object['30_month_filing_deadline']);
                $('input[name=' + 31 + '_month_filing_deadline]').val(object['31_month_filing_deadline']);
                $('input[name=publication_date]').val(object['publication_date']);
                $('input[name=filing_deadline]').val(object['filing_deadline']);
                $('input[name=reference_number]').val(object['reference_number']);
                $('input[name=number_priorities_claimed]').val(object['number_priorities_claimed']);
                $('select[name=search_location]').val(object['search_location']);
                $('input[name=number_claims]').val(object['number_claims']);
                $('input[name=number_reduced_claims]').val(object['number_reduced_claims']);
                $('input[name=number_pages_drawings]').val(object['number_pages_drawings']);
                $('input[name=number_pages]').val(object['number_pages']);

                $('input[name=number_pages_sequence]').val(object['number_pages_sequence']);
                $('select[name=sequence_listing]').val(object['sequence_listing']);

                $('input[name=number_words]').val(object['number_words']);
                $('input[name=publication_language]').val(object['publication_language']);
                Loader.hide();
                return false;
            })

        });

        return false;
    });

});
</script>
<div id="empty-div"></div>

<div class="file_uploader_block">
        <div id="file-uploader">
            <noscript>
            <p>Please enable JavaScript to use file uploader.</p>
            </noscript>
        </div>
    </div>

<div class="header_actions">
    <?php
if($case['common_status'] !='hidden'){
    if (($case["is_intake"] == '1' || $case['common_status'] == 'pending-intake') && $case['common_status'] != 'active')
    {
        if($case['common_status'] != 'completed'){
        echo '&nbsp;&nbsp;&nbsp;';
        echo form_open('/cases/intake/' . $case['case_number'], array('style' => 'float: left; margin-left: 10px;'));
        echo form_submit('submit', 'Intake');
        echo form_close('&nbsp;');
    }
    }
}
    ?>

    <?php
    if ($case['common_status'] != 'completed' && $case['common_status'] != 'hidden')
    { // Complete Case button
        echo form_open('/cases/complete/' . $case['case_number'], array('style' => 'float: right;'));
        echo form_submit('submit', 'Complete Case', 'class="button yellow"');
        echo form_close('&nbsp;');
    } else
    {
        if($case["common_status"] != 'hidden'){
        echo '<div class="red-notice">This case is completed</div>';
        }
    }

    if ($is_related && $case['common_status'] == 'hidden')
    {
        if($this->session->userdata('type') == 'supervisor'){
        ?> <input class="button red" type="submit" id="ignore_country" value="Delete Related Countries" name="submit">
        <input class="button new_green" type="submit" id="approve_country" value="Approve Related Countries" name="submit"><?php
}else
        {
            ?>
            <input class="button" type="button" id="escalate_related" value="Escalate" name="submit">
            <?php
        }
}
    ?>
</div>
<?php
// Output case status
$case_status = '';
$case_statues_array = array(
    'active' => 'Active',
    'pending-approval' => 'Pending Approval',
    'estimating' => 'Estimating',
    'pending-intake' => 'Pending Intake',
    'completed' => 'Completed',
    'hidden' => 'Hidden for Client',
    'estimating-estimate' => 'Estimating Estimate',
    'estimating-reestimate' => 'Estimating Reestimate'
);
$case_status = (isset($case_statues_array[$case['common_status']])) ? $case_statues_array[$case['common_status']] : '';

echo '<p>Current status: <b>' . $case_status . '</b></p>';
?>
<div id="tabs">
    <ul>
        <li><a href="#client-info">Client Info</a></li>
        <li><a href="#case-info">Case Info</a></li>
        <li><a href="#case-files">Case Files</a></li>
        <li><a href="#instruction-emails">Emails</a></li>
        <li><a href="#case-tracker">Tracker</a></li>
        <li><a href="#additional-info">Notes</a></li>
        <li><a href="#associates">Associates</a></li>
        <li><a href="#estimate">Estimate</a></li>
        <li><a href="#finance">Finance</a></li>
    </ul>
    <!-- Client Info -->
    <div id="client-info">
        <form id="serialize">
        <?php
        $tmpl = array(
            'table_open' => '<table border="0" cellpadding="4" cellspacing="0" class="data-table">',
        );
        $this->table->set_template($tmpl);
        $this->table->add_row('Username', anchor('/clients/edit/' . $customer['id'], $customer['username']) . '&nbsp;' . anchor_popup('/clients/login/' . $customer['id'], 'Login', FALSE, 'class="button"'));
        $this->table->add_row('Email', $customer['email']);
        $this->table->add_row('First Name', '<input name="customer_firstname" type="text" value="' . $customer['firstname'] . '">');
        $this->table->add_row('Last Name', '<input name="customer_lastname" type="text" value="' . $customer['lastname'] . '">');
        $this->table->add_row('Company', '<input name="customer_company_name" type="text" value="' . $customer['company_name'] . '">');
        $this->table->add_row('Address', '<input name="customer_address" type="text" value="' . $customer['address'] . '">');
        $this->table->add_row('Address 2', '<input name="customer_address2" type="text" value="' . $customer['address2'] . '">');
        $this->table->add_row('City', '<input name="customer_city" type="text" value="' . $customer['city'] . '">');
        $this->table->add_row('State', '<input name="customer_state" type="text" value="' . $customer['state'] . '">');
        $this->table->add_row('Zip Code', '<input name="customer_zip_code" type="text" value="' . $customer['zip_code'] . '">');
        $this->table->add_row('Country', '<input name="customer_country" type="text" value="' . $customer['country'] . '">');
        $this->table->add_row('Phone', '<input name="customer_phone_number" type="text" value="' . $customer['phone_number'] . '">');
        $this->table->add_row('Ext', '<input name="customer_ext" type="text" value="' . $customer['ext'] . '">');
        $this->table->add_row('Fax', '<input name="customer_fax" type="text" value="' . $customer['fax'] . '">');
        $this->table->add_row('Update', '<input type="submit" value="UPDATE" id="update_customer_data">');
        $this->table->add_row('Last Login', $customer['last_login']);
        $this->table->add_row('Estimates', $customer['count_estimates']);
        $this->table->add_row('Intakes', $customer['count_intakes']);
        echo $this->table->generate();
        $this->table->clear();
        ?>
        </form>
    </div>
    <!-- Client Info End -->
    <!-- Case Info -->
    <?php
    $related_cases_links = array();
    if (check_array($related_cases))
    {
        foreach ($related_cases as $related_case)
        {
            $related_cases_links[] = anchor('/cases/view/' . $related_case['case_number'], $related_case['case_number'], 'class="related_case_link"');
        }
    }
    $related_cases_string = '';
    if (count($related_cases_links) > 0)
    {
        $related_cases_string = '(' . implode(', ', $related_cases_links) . ')';
    }
    $disable = '';
    $readonly = '';

    if($case['common_status'] =='active' || $case['common_status'] =='completed'){
        if($this->session->userdata('type') == 'supervisor'){
            $disable = '';
            $readonly = '';
        }else{
            $disable = 'disabled';
            $readonly = 'readonly';
        }
    }
    ?>

    <div id="case-info">
        <div id="case-info-column">
            <?php
            echo form_open('/cases/update/' . $case['case_number'], array('id' => 'myform'));
            $tmpl = array(
                'table_open' => '<table border="0" cellpadding="4" cellspacing="0" class="data-table">',
            );
            $this->table->set_template($tmpl);
            echo form_hidden('case_number', $case['case_number']);
            if ($match_with_existant_case) {
                if(($case['common_status']!='active' && $case['common_status']!='completed') || $this->session->userdata('type') == 'supervisor'){
                $this->table->add_row('Application Matches' , '<a target="_blank" href="' . base_url() . 'cases/view/' . $match_with_existant_case->case_number . '">' . $match_with_existant_case->case_number . '</a> <input type="submit" id="load_data_from_case_with_same_number"  href="' . $match_with_existant_case->case_number . '" value="LOAD DATA FROM ' . $match_with_existant_case->case_number . '">');
            }else{
                    $this->table->add_row('Application Matches' , '<a target="_blank" href="' . base_url() . 'cases/view/' . $match_with_existant_case->case_number . '">' . $match_with_existant_case->case_number . '</a>');
                }
            }

            $this->table->add_row('Case Number', $case['case_number'] . " " . $related_cases_string);
            $this->table->add_row('PARKIP Case Number', form_input('parkip_case_number', $case['parkip_case_number'], $readonly));
            $this->table->add_row('Case Type', form_dropdown('case_type_id', $case_types_output, $case['case_type_id'], $disable));
            $this->table->add_row('Original Case Type', ($case['is_intake'] == '0') ? 'Estimate' : 'Intake');
            if (!empty($case['wipo_pct_number'])) {
                $application_number = $case['wipo_pct_number'];
            } else {
                $application_number = $case['application_number'];
            }
        if($case['common_status']=='active'  || $case['common_status']=='completed'){

            if($this->session->userdata('type') == 'supervisor'){
                $this->table->add_row(
                    'Application Number', form_input('application_number', $application_number) .
                    (($case['case_type_id'] == "1" ) ? form_submit('parse_wipo', 'Load Data From WIPO.INT', 'class="button parse_data"') : "") /* Allow this button only for PCT Cases PS. Stan 29.11.2012 */

                );
            }else{
                $this->table->add_row(
                    'Application Number', form_input('application_number', $application_number, $readonly));
            }

        }else{
            $this->table->add_row(
                'Application Number', form_input('application_number', $application_number) .
                (($case['case_type_id'] == "1" ) ? form_submit('parse_wipo', 'Load Data From WIPO.INT', 'class="button parse_data"') : "") /* Allow this button only for PCT Cases PS. Stan 29.11.2012 */

            );
        }
            if ($case['case_type_id'] == '1')
            {
                $this->table->add_row(array('data' => 'WO number'), array('data' => form_input('wo_number', $case['wipo_wo_number'], $readonly)));
                $this->table->add_row('Direct Link', $wipo_direct_link);
            }
            $this->table->add_row('Application Title', form_input('title', $case['application_title'], 'class="full-width" '.$readonly));
            $this->table->add_row('Applicant', form_input('applicant', $case['applicant'], 'class="full-width" '.$readonly));
            $this->table->add_row('CC (separated by comma)', form_input('cc', $contacts, 'class="full-width" '.$readonly));

            if ($case['case_type_id'] != '2')
            {
                $date_value = ($case['first_priority_date'] == '00/00/00' || empty($case['first_priority_date'])) ? 'N/A' : $case['first_priority_date'];
                $this->table->add_row('First Priority Date', form_input('first_priority_date', $date_value, 'id="first_priority_date" '.$readonly));
            }
            echo form_hidden('client_filing_deadline', $case['filing_deadline']);

            /*
              PCT: 30 and 31 months deadlines
              EP: just filing_deadline
              Direct: 12 month deadline
             */

            if ($case['case_type_id'] == '1')
            {

                $date_value = ($case['international_filing_date'] == '00/00/00' || empty($case['international_filing_date'])) ? 'N/A' : $case['international_filing_date'];

                $this->table->add_row('International Filing Date', form_input('international_filing_date', $date_value, 'id="international_filing_date" class="date" '.$readonly));
                $date_value = ($case['30_month_filing_deadline'] == '00/00/00' || empty($case['30_month_filing_deadline'])) ? 'N/A' : $case['30_month_filing_deadline'];

                $this->table->add_row('30 month filing deadline', form_input('30_month_filing_deadline', $date_value, 'class="date" '.$readonly));
                echo form_hidden('30_month_filing_deadline_orig', $date_value);
                $date_value = ($case['31_month_filing_deadline'] == '00/00/00' || empty($case['31_month_filing_deadline'])) ? 'N/A' : $case['31_month_filing_deadline'];
                $this->table->add_row('31 month filing deadline', form_input('31_month_filing_deadline', $date_value, 'class="date" '.$readonly));
                echo form_hidden('31_month_filing_deadline_orig', $date_value);
            }
            if ($case['case_type_id'] == '2' || $case['case_type_id'] == '1')
            {
                $date_value = ($case['publication_date'] == '00/00/00' || empty($case['publication_date'])) ? 'N/A' : $case['publication_date'];
                $this->table->add_row('Publication date', form_input('publication_date', $date_value, 'id="publication_date" class="date" '.$readonly));
                if ($case['case_type_id'] == '2')
                {
                $date_value = ($case['filing_deadline'] == '00/00/00' || empty($case['filing_deadline'])) ? 'N/A' : $case['filing_deadline'];
                $this->table->add_row('Filing deadline', form_input('filing_deadline', $date_value, 'id="filing_deadline" class="date" '.$readonly));
                echo form_hidden('filing_deadline_orig', $date_value);
                }
            } elseif ($case['case_type_id'] == '3')
            {
                $date_value = ($case['filing_deadline'] == '00/00/00' || empty($case['filing_deadline'])) ? 'N/A' : $case['filing_deadline'];
                $this->table->add_row('Filing deadline', form_input('filing_deadline', $date_value, 'id="filing_deadline" class="date" '.$readonly));
                echo form_hidden('filing_deadline_orig', $date_value);
            }
            $this->table->add_row('Client Reference Number', form_input('reference_number', $case['reference_number'], 'class="" '.$readonly) . '<a href="#" id="show_references"><img style="display:inline; vertical-align:middle; margin-bottom: 5px;" width="25px" src="/client/assets/img/Nuvola_Green_Plus.svg.png"></a>');

            foreach($countries as $ref_country) {
                $this->table->add_row('=== ' . $ref_country['country'] . ' Reference Number', form_input('reference_number_for_country_' . $ref_country['primary_id'] , $ref_country['reference_number'], 'class="reference_number_input" '.$readonly));
            }

            // If there is a BDV assigned to current client and there is no BDV assigned to current case
            $bdv_id = ($case['sales_manager_id'] > 0) ? $case['sales_manager_id'] : $customer_bdv_id;
            $this->table->add_row('Project Manager', form_dropdown('manager_id', $managers, $case['manager_id'], $disable));
            $this->table->add_row('Sales Manager', form_dropdown('sales_manager_id', $sales_managers_output, $bdv_id, $disable) . '&nbsp;<a href="javascript:void(0);" id="case-info-view-more">View More</a>');
            if ($case['case_type_id'] == '3')
            {
                $this->table->add_row('List Priorities Number (Direct Only)', form_input('list_priorities_number', $case['list_priorities_number'],$readonly));
            }
            $this->table->add_row('Separate Confirmation Reports', form_dropdown('email_notification', array("0" => "no", "1" => "yes"), $case['email_notification'], $disable));

            if ($case['case_type_id'] == '1' || $case['case_type_id'] == '3')
            {
                $this->table->add_row(array('data' => 'Number of Priorites Claimed', 'class' => 'unnecessary'), array('data' => form_input('number_priorities_claimed', $case['number_priorities_claimed'],$readonly), 'class' => 'unnecessary'));
            }
            $search_report_location_values = array(
                '' => '',
                'ep' => 'Europe',
                'us' => 'USA',
                'jp_ru_au_cn_kr' => 'JP, RU, AU, CN, KR',
                'ca' => 'Canada',
            );
            if ($case['case_type_id'] == '1' || $case['case_type_id'] == '3')
            {
                $this->table->add_row(array('data' => 'Search Location', 'class' => 'unnecessary'), array('data' => form_dropdown('search_location', $search_report_location_values, $case['search_location'],$disable), 'class' => 'unnecessary'));
            }
            if (empty($case['number_claims']))
            {
                $case_number_claims = 'N/A';
            } else
            {
                $case_number_claims = $case['number_claims'];
            }

            $this->table->add_row(array('data' => 'Number of Claims', 'class' => 'unnecessary'), array('data' => form_input('number_claims', $case_number_claims, $readonly), 'class' => 'unnecessary'));
            if ($case['case_type_id'] == '1')
            {
                $this->table->add_row(array('data' => 'Number of Reduced Claims', 'class' => 'unnecessary'), array('data' => form_input('number_reduced_claims', $case['number_reduced_claims'],$readonly), 'class' => 'unnecessary'));
            }
            $this->table->add_row(array('data' => 'Number of Pages of Drawings', 'class' => 'unnecessary'), array('data' => form_input('number_pages_drawings', $case['number_pages_drawings'],$readonly), 'class' => 'unnecessary'));
            if ($case['case_type_id'] == '2')
            {
                $this->table->add_row(array('data' => 'Number of Pages of Claims (EP Only)', 'class' => 'unnecessary'), array('data' => form_input('number_pages_claims', $case['number_pages_claims'],$readonly), 'class' => 'unnecessary'));
            }
            $this->table->add_row(array('data' => 'Total Number of Pages', 'class' => 'unnecessary'), array('data' => form_input('number_pages', $case['number_pages'],$readonly), 'class' => 'unnecessary'));
            if ($case['case_type_id'] == '1' || $case['case_type_id'] == '3')
            {
                $this->table->add_row(array('data' => 'Number of Pages of Sequence', 'class' => 'unnecessary'), array('data' => form_input('number_pages_sequence', $case['number_pages_sequence'],$readonly), 'class' => 'unnecessary'));
                $this->table->add_row(array('data' => 'Sequence Listing', 'class' => 'unnecessary'), array('data' => form_dropdown('sequence_listing', array('1' => 'Yes', '0' => 'No'), $case['sequence_listing'], $disable), 'class' => 'unnecessary'));
            }
            $this->table->add_row(array('data' => 'Number of Words in Application', 'class' => 'unnecessary'), array('data' => form_input('number_words', $case['number_words'],$readonly), 'class' => 'unnecessary'));
            if ($case['case_type_id'] == '2')
            {
                $this->table->add_row(array('data' => 'Number of Words in Claims (EP Only)', 'class' => 'unnecessary'), array('data' => form_input('number_words_in_claims', $case['number_words_in_claims'],$readonly), 'class' => 'unnecessary'));
            }

            $this->table->add_row(array('data' => 'Publication language', 'class' => 'unnecessary'), array('data' => form_input('publication_language', $case['publication_language'],$readonly), 'class' => 'unnecessary'));

            $this->table->add_row(array('data' => 'Created', 'class' => 'unnecessary'), array('data' => $case['created_at'], 'class' => 'unnecessary'));
            $this->table->add_row(array('data' => 'Last Update', 'class' => 'unnecessary'), array('data' => $case['last_update'], 'class' => 'unnecessary'));
            echo $this->table->generate();
            $this->table->clear();
            ?>
        </div>
    <?php if($case['common_status']=='active' || $case['common_status']=='completed'){
        if($this->session->userdata('type') == 'supervisor'){?>
        <div id="huge-submit-button-column">
<?php echo form_submit('submit', 'Update', 'class="huge"') ?>
        </div>
<?php }else{ ?>
            <div id="huge-submit-button-column">
                <?php echo form_button('escalate_case_info', 'Escalate', 'class="huge_grey" id = "escalate_case_info"') ?>
            </div>
       <?php }
    }else{?>
        <div id="huge-submit-button-column">
            <?php echo form_submit('submit', 'Update', 'class="huge"') ?>
        </div>
        <?php } echo form_close(); ?>
    </div>
    <!-- Case Info End -->
    <!-- Case Files -->
    <div id="case-files">
    	<div id="ajax-shadow">
    		<div id="ajax-loader"></div>
    	</div>
    	<div id="case-files-content">
    	</div>
    </div>
   <!-- Case Files End -->

        <!-- Instruction Emails -->
        <div id="instruction-emails">
            <div id="type-emails">
                <div class="item"><a href="javascript:void(0);" class="type-emails" id="translation_request">Translation Request To Park</a></div>
                <div class="item"><a href="javascript:void(0);" class="type-emails" id="fa-request">FA Request</a></div>
                <div class="item"><a href="javascript:void(0);" class="type-emails" id="document-instruction">Document
                        Instruction</a></div>
                <div class="item"><a href="javascript:void(0);" class="type-emails" id="filing-confirmation">Filing
                        Confirmation</a></div>
                <div class="item"><a href="javascript:void(0);" class="type-emails" id="new-email">New Email</a></div>
                <div><?php echo anchor_popup('/emails/open_cases_email_box/' . $case['case_number'], '<img src="' . base_url() . 'assets/images/i/inbox-email.png" title="Open email box for current case" alt="Open email box for current case" />') ?></div>
                <!--<div><?php echo anchor('/emails/variables/', 'Available Variables', 'id="available-variables" class="modal-dialog"'); ?></div>-->
            </div>
            <div id="email-content">
                <div id="to-box" style="display:none;">
                    <table border="0" cellpadding="5" width="100%" class="data-table">
                        <tr>
                            <td>To:</td>
                            <td><input type="text" id="to" name="to" style="width: 95%;"/></td>
                        </tr>
                        <tr>
                            <td>CC:</td>
                            <td><input  type="text" id="cc" name="cc" style="width: 95%;"/></td>
                        </tr>
                        <tr>
                            <td>Subject:</td>
                            <td><input type="text" id="subject" name="subject" style="width: 95%;"/></td>
                        </tr>
                    </table>
                </div>
                <div id="email-text">
<?php
echo form_textarea('email-textarea', FALSE, 'id = "email-textarea"');
?>
                </div>
                <div class="email_actions" id="send-button-box">
                    <div class="send-email-button-position">
                        <a id="send-email" class="send-email-button">&nbsp;</a>
                    </div>
                    <div class="attach-from-case-button-position">
                        <a id="attach-from-case-button" class="attach-from-case-button">&nbsp;</a>
                    </div>
                    <div class="email-uploader-position">
                        <div id="email-uploader">
                            <noscript>
                            <p>Please enable JavaScript to use file uploader.</p>
                            </noscript>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
                <div id="attached-files">

                </div>
                <input type="hidden" id="zip_hash" name="zip_hash" value="">
            </div>
            <div id="email-countries">
            </div>

            <script id="templateUploaderEmail" type="text/x-jquery-tmpl">
                <div class="qq-uploader">
                    <div class="qq-upload-drop-area"><span>Drop files here to upload</span></div>
                    <div class="qq-upload-button">
                    </div>
                    <table class="qq-upload-list"></table>
                </div>
                </script>

                <script id="templateUploadedFile_Info" type="text/x-jquery-tmpl">
					<span id="attached_${id}">
						<input type="hidden" name="attached_files[]" value="${id}"/>
						<a href="javascript:void(0);" onclick="remove_file_from_email(${id})">
							<img src="<?=base_url()?>assets/images/i/delete.png" title="Remove file" />
						</a>
						<a title="View file" href="<?=base_url()?>cases/view_file/${id}">${fileName}</a>
						<br/>
					</span>
				</script>

                    <script id="templateUploader" type="text/x-jquery-tmpl">
                        <style type="text/css">
                            #qq_upload_stan{
                                padding-bottom: 14px;
                                padding-left: 10px;
                                padding-top: 4px;
                            }
                        </style>
                        <div class="qq-uploader">
                            <div class="qq-upload-drop-area"><span>Drop files here to upload</span></div>
                            <div class="qq-upload-button" id="qq_upload_stan">
                                <div class="middle">Upload</div>
                                <div class="b small">Additional Files</div>
                            </div>
                            <ul class="qq-upload-list"></ul>
                        </div>
                        </script>

                    </div>

                    <!-- Instruction Emails End -->

                    <!-- Case Tracker -->
                    <script>
                    $().ready(function(){
                        $('.add_a_note').click(function() {
                            $(this).nextAll('.notice_form').toggle();
                        }
                    );
                        $('.translation_expected_delivery_date, .fr_filing_date').datepicker({
                            "dateFormat":"mm/dd/y",
                            "changeMonth":true,
                            "changeYear":true
                        });
                        $('.tracker_ajax_state').click(function(){
                            obj = $(this);
                            country_id = obj.attr('id');
                            action = obj.attr('name');
                            url = '<?= base_url('cases/save_tracker') ?>';
                            data = {'case_id': '<?= $case['id'] ?>', 'country_id': country_id, 'action': action,'data':false}

                            if (obj.hasClass('tracker_inactive')) {
                                data.value = '1';
                                class_to_del = 'tracker_inactive';
                                class_to_add = 'tracker_required';
                            } else if (obj.hasClass('tracker_required')) {
                                data.value = '2';
                                class_to_del = 'tracker_required';
                                class_to_add = 'tracker_not_required';
                            }  else if (obj.hasClass('tracker_not_required')) {
                                data.value = '0';
                                class_to_del = 'tracker_not_required';
                                class_to_add = 'tracker_inactive';
                            }

                            if(data.value){
                                $.ajax({
                                    url: url,
                                    type: 'post',
                                    data: data,
                                    success: function(data) {
                                        if (data){
                                            if(action == 'doc_required'){
                                                $(".tracker_ajax_state[name='doc_required']").each(function(){
                                                    if($(this).attr('id')==country_id){
                                                        $(this).removeClass(class_to_del).addClass(class_to_add);
                                                    }
                                                });
                                            }else{
                                                obj.removeClass(class_to_del).addClass(class_to_add);
                                            }
                                        }
                                    }
                                });
                            }
                        });
                        $('.tracker_ajax_date').click(function(){
                            obj = $(this);
                            country_id = obj.attr('id');
                            action = obj.attr('name');
                            url = '<?= base_url('cases/save_tracker') ?>';
                            data = {'case_id': '<?= $case['id'] ?>', 'country_id': country_id, 'action': action}
                            if ($(this).hasClass('tracker_inactive')) {
                                data.value = 'current_date';
                                $.ajax({
                                    url: url,
                                    type: 'post',
                                    data: data,
                                    success: function(data) {
                                        if (data) {
                                            if (data != '1') {
                                                tmp = data.split('-');
                                                data = tmp[1] + '/' + tmp[2].substr(0, 2) + '/' + tmp[0].substr(2, 2);
                                            }

                                            obj.removeClass('tracker_inactive').addClass('tracker_required').attr('value', data);
                                        }
                                    }
                                });
                            } else {
                                data.value = '0';
                                $.ajax({
                                    url: url,
                                    type: 'post',
                                    data: data,
                                    success: function(data) {
                                        if (data)
                                            obj.removeClass('tracker_required').addClass('tracker_inactive').attr('value', '');
                                    }
                                });
                            }
                        });
                        $('.translation_expected_delivery_date, .fr_filing_date').change(function(){
                            obj = $(this);
                            country_id = obj.attr('rel');
                            action = obj.attr('name');
                            url = '<?= base_url('cases/save_tracker') ?>';
                            date = obj.val().split('/');
                            date = '20' + date[2] + '-' + date[0] + '-' + date[1];
                            data = {'case_id': '<?= $case['id'] ?>', 'country_id': country_id, 'action': action, 'value': date}
                            $.ajax({
                                url: url,
                                type: 'post',
                                data: data,
                                success: function(data) {

                                }
                            });
                        });
                        $('.tracker_ajax_date, .auto_date').hover(function(){
                            coords = $(this).position();
                            hint_top = Math.round(coords.top) + 60 + 'px';
                            hint_left = Math.round(coords.left) - 26 + 'px';
                            date = $(this).attr('value');
                            if (date) {
                                $('.date_hint').css({'top':hint_top, 'left': hint_left}).html(date).fadeIn(300);
                            }

                        }, function(){
                            $('.date_hint').fadeOut(300);
                        });
                    });
                    </script>
                    <?php
                    $unapproved_countries = array();
                      if (check_array($list_estimate_countries))
                      {
                          foreach ($list_estimate_countries as $item)
                          {
                              if ($item['is_approved'] == '0' && $item['pm_approved_after_client'] == '0')
                              {
                                  $unapproved_countries[] = $item['country_id'];
                              }
                          }
                      }
                    ?>
                    <div class="date_hint"></div>
                    <div id="case-tracker">
                        <div id="tracker-tabs">
                            <ul>
                                <li><a href="#tracker-translation">Translation</a></li>
                                <li><a href="#tracker-filing-instructions">Filing instructions</a></li>
                                <li><a href="#tracker-document-instructions">Document instructions</a></li>
                                <li><a href="#tracker-filing-receipts">Filing receipts</a></li>
                            </ul>
                            <div id="tracker-translation">
                                <div id="tracker-table">
                                    <table>
                                        <tr>
                                            <th>Extension Needed</th>
                                            <th>Countries</th>
                                            <th>Filing Deadline</th>
                                            <th>Required</th>
                                            <th>Request sent to Park</th>
                                            <th>Expected Delivery Date</th>
                                            <th>Delivered</th>
                                            <th rowspan="100" class="last-td">
                                                <button class="add_a_note">Add a Note</button><br />
                                                <span class="notice_form">
                                                    <textarea class="tracker_note"></textarea><br />
                                                    <button class="tracker_add_note">Add</button>
                                                </span>
                                            </th>
                                        </tr>
                                        <?php
                                        if ($countries)
                                        {
                                            foreach ($countries as $country):
                                                ?>
                                                <?php
                                                $case_countries[] = $country['id'];
                                                if (!in_array($country['id'], $unapproved_countries))
                                                {
                                                    if ($case['case_type_id'] == '1')
                                                    {
                                                        if ($country['country_filing_deadline'] == '30')
                                                        {
                                                            $filing_deadline = $case['30_month_filing_deadline'];
                                                        } else
                                                        {
                                                            $filing_deadline = $case['31_month_filing_deadline'];
                                                        }
                                                    } else
                                                    {
                                                        $filing_deadline = $case['filing_deadline'];
                                                    }
                                                    if ($tracker[$country['id']]['translation_required'] == 1)
                                                        $translation_required = 'tracker_required';
                                                    elseif ($tracker[$country['id']]['translation_required'] == 2)
                                                        $translation_required = 'tracker_not_required';
                                                    else
                                                        $translation_required = 'tracker_inactive';
                                                    $time = strtotime($tracker[$country['id']]['translation_request_sent_to_park']);
                                                    $translation_request_sent_to_park = ($time > 0) ? 'tracker_required' : 'tracker_inactive';
                                                    $translation_request_sent_to_park_date = ($time > 0) ? date('m/d/y', $time) : '';

                                                    $time = strtotime($tracker[$country['id']]['translation_delivered']);
                                                    $translation_delivered = ($time > 0) ? 'tracker_required' : 'tracker_inactive';
                                                    $translation_delivered_date = ($time > 0) ? date('m/d/y', $time) : '';

                                                    $time = strtotime($tracker[$country['id']]['translation_expected_delivery_date']);
                                                    $expected_delivery_date = ($time > 0) ? date('m/d/y', $time) : '';
                                                    ?>
                                                    <tr>
                                                        <td><?= form_checkbox('extension_needed', $country['id'], $country['extension_needed'], 'id="' . $country['id'] . '" class="extension_needed"') ?></td>
                                                        <td><?= $country['country'] ?></td>
                                                        <td class="red-text"><?= $filing_deadline ?></td>
                                                        <td><button class="<?= $translation_required ?> tracker_ajax_state" id="<?= $country['id'] ?>" name="translation_required"></button></td>
                                                        <td><button class="<?= $translation_request_sent_to_park ?> tracker_ajax_date" id="<?= $country['id'] ?>" name="translation_request_sent_to_park" value="<?= $translation_request_sent_to_park_date ?>"></button></td>
                                                        <td><input type="text" class="translation_expected_delivery_date" value="<?= $expected_delivery_date ?>" name="translation_expected_delivery_date" rel="<?= $country['id'] ?>" /></td>
                                                        <td><button class="<?= $translation_delivered ?> tracker_ajax_date" id="<?= $country['id'] ?>" name="translation_delivered" value="<?= $translation_delivered_date ?>"></button></td>
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
        <?php
    endforeach;
}
?>
                                    </table>
                                </div>
                            </div>
                            <div id="tracker-filing-instructions">
                                <div id="tracker-table">
                                    <table>
                                        <tr>
                                            <th>Extension Needed</th>
                                            <th>Countries</th>
                                            <th>Filing Deadline</th>
                                            <th>Request sent to FA</th>
                                            <th>Request Received by FA</th>
                                            <th>Documents Required</th>
                                            <th rowspan="100" class="last-td">
                                                <button class="add_a_note">Add a Note</button><br />
                                                <span class="notice_form">
                                                    <textarea class="tracker_note"></textarea><br />
                                                    <button class="tracker_add_note">Add</button>
                                                </span>
                                            </th>
                                        </tr>
                                        <?php
                                        if ($countries)
                                        {
                                            foreach ($countries as $country):
                                                $case_countries[] = $country['id'];
                                                if (!in_array($country['id'], $unapproved_countries))
                                                {
                                                    if ($case['case_type_id'] == '1')
                                                    {
                                                        if ($country['country_filing_deadline'] == '30')
                                                        {
                                                            $filing_deadline = $case['30_month_filing_deadline'];
                                                        } else
                                                        {
                                                            $filing_deadline = $case['31_month_filing_deadline'];
                                                        }
                                                    } else
                                                    {
                                                        $filing_deadline = $case['filing_deadline'];
                                                    }
                                                    if ($tracker[$country['id']]['doc_required'] == 1)
                                                        $doc_required = 'tracker_required';
                                                    elseif ($tracker[$country['id']]['doc_required'] == 2)
                                                        $doc_required = 'tracker_not_required';
                                                    else
                                                        $doc_required = 'tracker_inactive';
                                                    $time = strtotime($tracker[$country['id']]['fi_requests_sent_fa']);
                                                    $fi_requests_sent_fa = ($time > 0) ? 'tracker_required' : 'tracker_inactive';
                                                    $fi_requests_sent_fa_date = ($time > 0) ? date('m/d/y', $time) : '';

                                                    $time = strtotime($tracker[$country['id']]['fi_requests_received_fa']);
                                                    $fi_requests_received_fa = ($time > 0) ? 'tracker_required' : 'tracker_inactive';
                                                    $fi_requests_received_fa_date = ($time > 0) ? date('m/d/y', $time) : '';
                                                    ?>
                                                    <tr>
                                                        <td><?= form_checkbox('extension_needed', $country['id'], $country['extension_needed'], 'id="' . $country['id'] . '" class="extension_needed"') ?></td>
                                                        <td><?= $country['country'] ?></td>
                                                        <td class="red-text"><?= $filing_deadline ?></td>
                                                        <td><button class="<?= $fi_requests_sent_fa ?> auto_date" id="<?= $country['id'] ?>" name="fi_requests_sent_fa" value="<?= $fi_requests_sent_fa_date ?>"></button></td>
                                                        <td><button class="<?= $fi_requests_received_fa ?> tracker_ajax_date" id="<?= $country['id'] ?>" name="fi_requests_received_fa" value="<?= $fi_requests_received_fa_date ?>"></button></td>
                                                        <td><button class="<?= $doc_required ?> doc_required tracker_ajax_state" id="<?= $country['id'] ?>" name="doc_required"></button></td>
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
        <?php
    endforeach;
}
?>
                                    </table>
                                </div>
                            </div>
                            <div id="tracker-document-instructions">
                                <div id="tracker-table">
                                    <table>
                                        <tr>
                                            <th>Extension Needed</th>
                                            <th>Countries</th>
                                            <th>Filing Deadline</th>
                                            <th>Required</th>
                                            <th>Sent to Client</th>
                                            <th>Forms Received</th>
                                            <th>Signed</th>
                                            <th>Sent to FA</th>
                                            <th rowspan="100" class="last-td">
                                                <button class="add_a_note">Add a Note</button><br />
                                                <span class="notice_form">
                                                    <textarea class="tracker_note"></textarea><br />
                                                    <button class="tracker_add_note">Add</button>
                                                </span>
                                            </th>
                                        </tr>
                                        <?php
                                        if ($countries)
                                        {
                                            foreach ($countries as $country):
                                                $case_countries[] = $country['id'];
                                                if (!in_array($country['id'], $unapproved_countries))
                                                {
                                                    if ($case['case_type_id'] == '1')
                                                    {
                                                        if ($country['country_filing_deadline'] == '30')
                                                        {
                                                            $filing_deadline = $case['30_month_filing_deadline'];
                                                        } else
                                                        {
                                                            $filing_deadline = $case['31_month_filing_deadline'];
                                                        }
                                                    } else
                                                    {
                                                        $filing_deadline = $case['filing_deadline'];
                                                    }
                                                    if ($tracker[$country['id']]['doc_required'] == 1)
                                                        $doc_required = 'tracker_required';
                                                    elseif ($tracker[$country['id']]['doc_required'] == 2)
                                                        $doc_required = 'tracker_not_required';
                                                    else
                                                        $doc_required = 'tracker_inactive';

                                                    $time = strtotime($tracker[$country['id']]['doc_sent_client']);
                                                    $doc_sent_client = ($time > 0) ? 'tracker_required' : 'tracker_inactive';
                                                    $doc_sent_client_date = ($time > 0) ? date('m/d/y', $time) : '';

                                                    $time = strtotime($tracker[$country['id']]['doc_forms_received']);
                                                    $doc_forms_received = ($time > 0) ? 'tracker_required' : 'tracker_inactive';
                                                    $doc_forms_received_date = ($time > 0) ? date('m/d/y', $time) : '';

                                                    $time = strtotime($tracker[$country['id']]['doc_signed']);
                                                    $doc_signed = ($time > 0) ? 'tracker_required' : 'tracker_inactive';
                                                    $doc_signed_date = ($time > 0) ? date('m/d/y', $time) : '';

                                                    $time = strtotime($tracker[$country['id']]['doc_sent_fa']);
                                                    $doc_sent_fa = ($time > 0) ? 'tracker_required' : 'tracker_inactive';
                                                    $doc_sent_fa_date = ($time > 0) ? date('m/d/y', $time) : '';
                                                    ?>
                                                    <tr>
                                                        <td><?= form_checkbox('extension_needed', $country['id'], $country['extension_needed'], 'id="' . $country['id'] . '" class="extension_needed"') ?></td>
                                                        <td><?= $country['country'] ?></td>
                                                        <td class="red-text"><?= $filing_deadline ?></td>
                                                        <td><button class="<?= $doc_required ?> doc_required tracker_ajax_state" id="<?= $country['id'] ?>" name="doc_required"></button></td>
                                                        <td><button class="<?= $doc_sent_client ?> tracker_ajax_date" id="<?= $country['id'] ?>" name="doc_sent_client" value="<?= $doc_sent_client_date ?>"></button></td>
                                                        <td><button class="<?= $doc_forms_received ?> auto_date" id="<?= $country['id'] ?>" name="doc_forms_received" value="<?= $doc_forms_received_date ?>"></button></td>
                                                        <td><button class="<?= $doc_signed ?> tracker_ajax_date" id="<?= $country['id'] ?>" name="doc_signed" value="<?= $doc_signed_date ?>"></button></td>
                                                        <td><button class="<?= $doc_sent_fa ?> tracker_ajax_date" id="<?= $country['id'] ?>" name="doc_sent_fa" value="<?= $doc_sent_fa_date ?>"></button></td>
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
        <?php
    endforeach;
}
?>
                                    </table>
                                </div>
                            </div>
                            <div id="tracker-filing-receipts">
                                <div id="tracker-table">
                                    <table>
                                        <tr>
                                            <th>Extension Needed</th>
                                            <th>Countries</th>
                                            <th>Filing Deadline</th>
                                            <th>Received</th>
                                            <th>Filing Date</th>
                                            <th>Sent</th>
                                            <th>Client Received</th>
                                            <th>Completed</th>
                                            <th rowspan="100" class="last-td">
                                                <button class="add_a_note">Add a Note</button><br />
                                                <span class="notice_form">
                                                    <textarea class="tracker_note"></textarea><br />
                                                    <button class="tracker_add_note">Add</button>
                                                </span>
                                            </th>
                                        </tr>
                                        <?php
                                        if ($countries)
                                        {
                                            foreach ($countries as $country):
                                                ?>
                                                <?php
                                                $case_countries[] = $country['id'];
                                                if (!in_array($country['id'], $unapproved_countries))
                                                {
                                                    if ($case['case_type_id'] == '1')
                                                    {
                                                        if ($country['country_filing_deadline'] == '30')
                                                        {
                                                            $filing_deadline = $case['30_month_filing_deadline'];
                                                        } else
                                                        {
                                                            $filing_deadline = $case['31_month_filing_deadline'];
                                                        }
                                                    } else
                                                    {
                                                        $filing_deadline = $case['filing_deadline'];
                                                    }
                                                    $time = strtotime($tracker[$country['id']]['fr_received']);
                                                    $fr_received = ($time > 0) ? 'tracker_required' : 'tracker_inactive';
                                                    $fr_received_date = ($time > 0) ? date('m/d/y', $time) : '';

                                                    $time = strtotime($tracker[$country['id']]['fr_sent']);
                                                    $fr_sent = ($time > 0) ? 'tracker_required' : 'tracker_inactive';
                                                    $fr_sent_date = ($time > 0) ? date('m/d/y', $time) : '';

                                                    $time = strtotime($tracker[$country['id']]['fr_client_received']);
                                                    $fr_client_received = ($time > 0) ? 'tracker_required' : 'tracker_inactive';
                                                    $fr_client_received_date = ($time > 0) ? date('m/d/y', $time) : '';

                                                    $time = strtotime($tracker[$country['id']]['fr_completed']);
                                                    $fr_completed = ($time > 0) ? 'tracker_required' : 'tracker_inactive';
                                                    $fr_completed_date = ($time > 0) ? date('m/d/y', $time) : '';

                                                    $time = strtotime($tracker[$country['id']]['fr_filing_date']);
                                                    $fr_filing_date = ($time > 0) ? date('m/d/y', $time) : '';
                                                    ?>
                                                    <tr>
                                                        <td><?= form_checkbox('extension_needed', $country['id'], $country['extension_needed'], 'id="' . $country['id'] . '" class="extension_needed"') ?></td>
                                                        <td><?= $country['country'] ?></td>
                                                        <td class="red-text"><?= $filing_deadline ?></td>
                                                        <td><button class="<?= $fr_received ?> tracker_ajax_date" id="<?= $country['id'] ?>" name="fr_received" value="<?= $fr_received_date ?>"></button></td>
                                                        <td><input type="text" class="fr_filing_date" value="<?= $fr_filing_date ?>" name="fr_filing_date" rel="<?= $country['id'] ?>" /></td>
                                                        <td><button class="<?= $fr_sent ?> auto_date" id="<?= $country['id'] ?>" name="fr_sent" value="<?= $fr_sent_date ?>"></button></td>
                                                        <td><button class="<?= $fr_client_received ?> auto_date" id="<?= $country['id'] ?>" name="fr_client_received" value="<?= $fr_client_received_date ?>"></button></td>
                                                        <td><button class="<?= $fr_completed ?> tracker_ajax_date" id="<?= $country['id'] ?>" name="fr_completed" value="<?= $fr_completed_date ?>"></button></td>
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
        <?php
    endforeach;
}
?>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Case Tracker End -->

                    <!-- Additional Info -->
                    <div id="additional-info">
                        <p>
                            <span class="outer-square">
                                <span class="inner-square" style="background-color: #CCFFCC;">&nbsp;</span>
                            </span>
                            <strong>- Client Instructions</strong>
                        </p>

                        <p>
                            <span class="outer-square">
                                <span class="inner-square" style="background-color: #FFCCCC;">&nbsp;</span>
                            </span>
                            <strong>- Client notes</strong>
                        </p>
                        <p>
                            <span class="outer-square">
                                <span class="inner-square" style="background-color: #E9D578;">&nbsp;</span>
                            </span>
                            <strong>- FA notes</strong>
                        </p>

                        <p>
                            <span class="outer-square">
                                <span class="inner-square" style="background-color: #CCCCCC;">&nbsp;</span>
                            </span>
                            <strong>- Case notes</strong>
                        </p>
                        <?php
                        $new_note = array(
                            'id' => 'new_note',
                            'name' => 'new_note',
                            'rows' => 3,
                            'cols' => 80,
                            'style' => 'width: 100%'
                        );
                        echo form_textarea($new_note);
                        echo '<br/>';
                        echo form_button('submit', 'Add a case note', 'id="add_new_note" class="light-red"');
                        echo '&nbsp;';
                        echo form_button('submit', 'Add a client note', 'id="add_client_note" class="light-red"');
                        $tmpl = array(
                            'table_open' => '<table border="0" cellpadding="5" cellspacing="0" width="100%" id="notes_table" class="data-table">',
                        );
                        $this->table->set_heading('Note', 'Username', 'Date', '&nbsp;');
                        $this->table->set_template($tmpl);
						$columns_exist = false;

						if (check_array($client_notes))
                        {
                            foreach ($client_notes as $client_note)
                            {
                                $row_class = 'client-note-row';
                                $this -> table -> add_row(
                                        array('data' => nl2br($client_note['note']), 'class' => $row_class),
                                        array('data' => $client_note['username'], 'class' => 'username ' . $row_class),
                                        array('data' => $client_note['created_at'], 'class' => 'created_at ' . $row_class),
                                        array('data' => '<a title="Remove" href="javascript:void(0);" id="delete_note_link_' . $client_note['id'] . '" onclick="if(confirm(\'Do you really want to delete the selected note?\')){ remove_note_from_case(' . $client_note['id'] . ');}"><img src="' . base_url() . 'assets/images/i/delete.png" alt="Remove"/></a>', 'class' => $row_class)
                                );
                                $columns_exist = true;
                            }
                        }

                        if (check_array($fa_notes))
                        {
                            foreach ($fa_notes as $fa_note)
                            {
                                $row_class = 'fa-note-row';
                                $this -> table -> add_row(
                                        array('data' => nl2br($fa_note['note']), 'class' => $row_class),
                                        array('data' => $fa_note['username'], 'class' => 'username ' . $row_class),
                                        array('data' => $fa_note['created_at'], 'class' => 'created_at ' . $row_class),
                                        array('data' => '<a title="Remove" href="javascript:void(0);" id="delete_note_link_' . $fa_note['id'] . '" onclick="if(confirm(\'Do you really want to delete the selected note?\')){ remove_note_from_case(' . $fa_note['id'] . ');}"><img src="' . base_url() . 'assets/images/i/delete.png" alt="Remove"/></a>', 'class' => $row_class)
                                );
                                $columns_exist = true;
                            }
                        }


						if ( ! empty($case['additional'])){  // Insert case instructions
							$row_class = 'client-instructions-row';
 							$this -> table -> add_row(
								array('data' => nl2br($case['additional']), 'class' => $row_class),
								array('data' => $customer['username'], 'class' => 'username ' . $row_class),
								array('data' => $case['created_at'], 'class' => 'created_at ' . $row_class),
								array('data' => '', 'class' => $row_class)
							);
							$columns_exist = true;
						}
                        if (check_array($case_notes))
                        {
                            foreach ($case_notes as $case_note)
                            {
                                $row_class = 'case-note';
                                $this -> table -> add_row(
                                        array('data' => nl2br($case_note['note']), 'class' => $row_class),
                                        array('data' => $case_note['username'], 'class' => 'username ' . $row_class),
                                        array('data' => $case_note['created_at'], 'class' => 'created_at ' . $row_class),
                                        array('data' => '<a title="Remove" href="javascript:void(0);" id="delete_note_link_' . $case_note['id'] . '" onclick="if(confirm(\'Do you really want to delete the selected note?\')){ remove_note_from_case(' . $case_note['id'] . ');}"><img src="' . base_url() . 'assets/images/i/delete.png" alt="Remove"/></a>', 'class' => $row_class)
                                );
                                $columns_exist = true;
                            }
                        }
                        if(!$columns_exist)
                        {
                            $this -> table -> add_row(
                                array('data' => ''),
                                array('data' => '', 'class' => 'username'),
                                array('data' => '', 'class' => 'created_at'),
                                ''
                            );
                        }
                        echo $this -> table -> generate();
                        $this -> table -> clear();
                        ?>
                    </div>
                    <!-- Additional Info End -->

                    <!-- Associates -->
                    <div id="associates">
                        <div id="associates-left-column">
                            <p>
                                <span class="outer-square">
                                    <span class="inner-square" style="background-color: #CCCCCC;">&nbsp;</span>
                                </span>
                                <strong>- Original Filing Associates</strong>
                            </p>

                            <p>
                                <span class="outer-square">
                                    <span class="inner-square" style="background-color: #e2c2e9;">&nbsp;</span>
                                </span>
                                <strong>- Countries without filing associates</strong></p>

                            <p>
                                <span class="outer-square">
                                    <span class="inner-square" style="background-color: #c2e9cc;">&nbsp;</span>
                                </span>
                                <strong>- Replacement Filing Associate</strong>
                            </p>
                            <p>
                                <input type="checkbox" name="is_associates_visible_to_client" value="1" <?php
                        if ($case['is_associates_visible_to_client'] == '1')
                        {
                            echo 'checked="TRUE"';
                        }
                        ?>> Associates visible to client
                            </p>
                            <p><a href="javascript:void(0);" class="button" id="merge_associates"><button class="blue 100px">Merge Associates</button></a></p>
                        </div>
                        <div id="associates-rigth-column">
                            <button class="light-red" id="generate-pdf-associates-list">Generate PDF Associates List</button>
                        </div>
                        <?php


                            $fa_countries_id = array();

                            if (check_array($associates))
                            {

                                $tmpl = array(
                                    'table_open' => '<table border="0" cellpadding="2" cellspacing="0" width="100%" class="data-table pm-assoc">'
                                );

                                $this->table->set_template($tmpl);
                                $this->table->set_heading('','Country', 'Associate', 'Fee', 'Reference Number', 'Action');

                                foreach ($associates as $associate)
                                {

                                    // Reference number
                                    $reference_number = anchor('/cases/associate_reference_form/' . $case['id'] . '/' . $case['case_number'] . '/' . $associate['id'], 'Add reference number', 'class="popup"');

                                    $fa_countries_id[] = $associate['country_id'];
                                    $replace_link = anchor('/associates/search_replaced_associates/' . $case['case_number'] . '/' . $associate['country_id'] . "/" . $associate['associate_id'], 'Replace', 'class="popup REPLACEMENT"');
                                    $replace_link_hidden = anchor('/associates/search_replaced_associates/' . $case['case_number'] . '/' . $associate['country_id'] . "/" . $associate['associate_id'], 'Replace', 'class="popup REPLACEMENT" style="display:none;"');
                                    $associate_class = '';
                                    $fa_currency_sign = '$';
//                                    $associate_class = 'strike';
//                                    $associate_class = 'custom-fa-only';

                                    if ($associate['fee_currency'] == 'euro')
                                    {
                                        $fa_currency_sign = '€';
                                    }
                                    $reference_number_link_text = $associate['reference_number'];
                                    if (empty($reference_number_link_text))
                                    {
                                        $reference_number_link_text = 'Add reference number';
                                    }
                                    $check_box = '<input id="associates_for_merge" type="checkbox" name="associates[]" value="'. $associate['associate_id'] .'">';
                                    if($associate['is_replaced'] == '1'){
                                        $associate_class = "ReplacementFA";
                                        $link = array(
                                            'data' => anchor('/cases/edit_replaced_associate/' . $associate['associate_id'] . '/' . $case['case_number'] . '/' . $associate['country_id'] . "/", 'Edit', 'class="popup"') . '&nbsp;' . anchor('/cases/delete_custom_associate/' . $associate['associate_id'] . '/' . $case['id']. '/' .$case['case_number'], 'Remove'),
                                            'class' => 'custom-fa-only'
                                        );

                                    }else{

                                        $ENABLE_DISABLE_BUTTON = "";
                                        if($associate['is_active'] == '0'){
                                            $associate_class = 'strike';
                                            $ENABLE_DISABLE_BUTTON = "<span class='button ENABLE' id=ENABLE_" . $associate['associate_id'] . "_" . $case['id'] . "><span>ENABLE</span></span>";
                                            $associate_class = 'strike';
                                            $replace_link = $replace_link_hidden;
                                            $check_box = '';
                                        }else{
                                            $associate_class = '';
                                            $ENABLE_DISABLE_BUTTON = "<span class='button DISABLE' id=DISABLE_" . $associate['associate_id'] . "_" . $case['id'] . "><span>DISABLE</span></span>";
                                        }
                                        $link = array(
                                            'data' => $replace_link . $ENABLE_DISABLE_BUTTON,
                                            'class' => $associate_class
                                        );
                                    }
                                                    $this->table->add_row(
                                                        array('data'=>$check_box,
                                                            'class' => $associate_class),
                                                            array(
                                                        'data' => $associate['country'],
                                                        'class' => $associate_class
                                                            ), array(
                                                        'data' => nl2br($associate['associate'] . '<br/>Reference #: ' . $reference_number_link_text),
//                                                        'data' => $fa_data,
                                                        'class' => $associate_class
                                                            ), array(
                                                        'data' => $fa_currency_sign . $associate['fee'],
                                                        'class' => $associate_class
                                                            ), array(
                                                        'data' => anchor('/cases/associate_reference_form/' . $case['id'] . '/' . $case['case_number'] . '/' . $associate['associate_id'], $reference_number_link_text, 'class="popup"'),
                                                        'class' => $associate_class
                                                            ), $link
                                                    );

                            }
                            // Output countries without FA
                                if (check_array($countries))
                                {
                            foreach ($countries as $country)
                            {
                                if (!in_array($country['id'], $unapproved_countries))
                                {
                                    if (!in_array($country['id'], $fa_countries_id))
                                    {
                                        $this->table->add_row('',
                                                array(
                                            'data' => $country['country'],
                                            'colspan' => 4,
                                            'class' => 'country-without-fa'
                                                ), array(
                                            'data' => anchor('/cases/create_associate_form/' . $case['case_number'] . '/' . $country['id'], 'Add FA', 'class="popup ADD"'),
                                            'class' => 'country-without-fa'
                                                )
                                        );
                                    }
                                }
                            }

                        }
                                echo $this->table->generate();
                                $this->table->clear();
                        }?>
                        <script type="text/javascript">
                        $(".ENABLE").live("click",function(){
                            var TMP_ENABLE  =   $(this).attr("id").replace("ENABLE_","").split("_");
                            var ENABLE_ID   =   TMP_ENABLE[0];
                            var CASE_ID     =   TMP_ENABLE[1];
                            pm_assoc.ENABLE(ENABLE_ID,CASE_ID);
                        });
                        $(".DISABLE").live("click",function(){
                            var TMP_DISABLE =   $(this).attr("id").replace("DISABLE_","").split("_");
                            var DISABLE_ID  =   TMP_DISABLE[0];
                            var CASE_ID     =   TMP_DISABLE[1];
                            pm_assoc.DISABLE(DISABLE_ID,CASE_ID);
                        });
                        </script>
                    </div>
                    <!-- Associates End -->

                    <!-- Estimate -->
                    <div id="estimate">
                        <div id="estimate-tabs">
                            <ul>
                                <li><a href="#estimate-main">Estimate</a></li>

                                 <li><a href="#fee-schedule">Fee Schedule</a></li>

                                <li><a href="#estimate-notes">Notes</a></li>
                                
								<!--<li><a href="#estimate-sow">SOW</a></li>-->
                            </ul>
                                    <?php echo form_open('/estimates/save/' . $case['case_number'], array('id' => 'save_estimate_form')); ?>
                            <div id="estimate-main">
                                <div id="estimate-content">
                                    <?php


                                    // Fees Table
                                    echo '<div id="fees-table-box">';

                                    echo $estimate_table;
                                    unset($estimate_table);
                                    echo '</div>';

                                    $estimate_footnotes_count = 0;
                                    if (check_array($estimate_footnotes))
                                    {
                                        $estimate_footnotes_count = count($estimate_footnotes);
                                    }
                                    echo form_hidden('count_footnotes', $estimate_footnotes_count);

                                    ?>

                                    <p>&nbsp;</p>

                                    <div id="top-footnote">
                                        <textarea name="top_footnote" cols="50" rows="3"><?php echo $case['top_footnote'] ?></textarea>
                                    </div>
                                    <p>&nbsp;</p>

                                    <div id="footnotes-box">
                                        <?php
                                        $count = 1;
                                        if (check_array($estimate_footnotes))
                                        {
                                            foreach ($estimate_footnotes as $footnote)
                                            {
                                                echo '<div class="footnote-row">
										<div class="footnote-number">' . $count . '</div>
										<div class="delete-footnote-box"><a class="delete-footnote" href="javascript:void(0);"><img src="' . base_url() . 'assets/images/i/delete.png" title="Remove footnote" class="tiptip" /></a></div>
										<div class="footnote-text">
											<input type="hidden" name="fee_type[]" value="' . $footnote['fee_type'] . '" />
											<input type="hidden" name="country_id[]" value="' . $footnote['country_id'] . '" />
											<textarea name="footnotes[]" cols="50" rows="5">' . strip_tags($footnote['footnote']) . '</textarea>
										 </div>
										 <a href="javascript:void(0);" class="footnote-arrow" id="' . $count . '">&nbsp;</a>
										 <div class="footnotes-dropdown-list footnotes-dropdown-' . $count . '">' . $footnotes_dropdown . '</div>
								  </div>';
                                                $count++;
                                            }
                                        }
                        echo '<p>
					<span class="outer-square">
						<span class="inner-square" style="background-color: #eeeeee;">&nbsp;</span>
					</span>
						<strong>- Countries entered by client</strong>
				</p>';
                        echo '<p>
					<span class="outer-square">
						<span class="inner-square" style="background-color: #6594fd;">&nbsp;</span>
					</span>
					<strong>- Countries added by client during re-estimate</strong>
			     </p>';
                        echo '<p>
					<span class="outer-square">
						<span class="inner-square" style="background-color: #928a9f;">&nbsp;</span>
					</span>
						<strong>- Countries disabled by client</strong>
				</p>';
                        echo '<p>
					<span class="outer-square">
						<span class="inner-square" style="background-color: #FF9966;">&nbsp;</span>
					</span>
						<strong>- Countries added by PM</strong>
				</p>';
                        echo '<p>
					<span class="outer-square">
						<span class="inner-square" style="background-color: #91d4ba;">&nbsp;</span>
					</span>
					<strong>- Countries approved by client</strong>
			     </p>';
                        echo '<p>
					<span class="outer-square">
						<span class="inner-square" style="background-color: #cbcece;">&nbsp;</span>
					</span>
						<strong>- Past approval deadline</strong>
				</p>';
                                        ?>
                                    </div>
                                </div>

                                <div id="estimate-control">
                                    <h3>Price Adjustment Control</h3>
                                    <?php
                                    $fee_levels = array(
                                        '1' => 'First Level',
                                        '2' => 'Second Level',
                                        '3' => 'Third Level',
                                        '4' => 'Fourth Level',
                                    );
                                    $this->table->clear();
                                    $this->table->add_row('Levels', form_dropdown('estimate_fee_level', $fee_levels, $case['estimate_fee_level'], 'id="fee_level" class="price-adjust"'.$disable));
                                    $inflate_official_fee = array(
                                        '0' => 'by 0%',
                                        '5' => 'by 5%',
                                        '10' => 'by 10%',
                                        '15' => 'by 15%',
                                    );
                                    $this->table->add_row('Inflate Official Fee (%)', form_input('inflate_official_fee', $case['estimate_inflate_official_fee'], 'class="price-adjust"'.$readonly));
                                    $this->table->add_row('Number of Priorities Claimed', form_input('number_priorities_claimed', $case['number_priorities_claimed'], 'class="price-adjust"'.$readonly));
                                    $this->table->add_row('Number Pages of Drawings', form_input('number_pages_drawings', $case['number_pages_drawings'], 'class="price-adjust"'.$readonly));
                                    $this->table->add_row('Claims', form_input('claims', $case['number_claims'], 'class="price-adjust"'.$readonly));
                                    $this->table->add_row('Pages', form_input('pages', $case['number_pages'], 'class="price-adjust"'.$readonly));
                                    $this->table->add_row('Number of words', form_input('number_words', $case['number_words'], 'class="price-adjust"'.$readonly));
                                    if ($case['case_type_id'] == '2'){
                                    	$this->table->add_row('Number Words in Claims (EP Only)', form_input('number_words_in_claims', $case['number_words_in_claims'], 'class="price-adjust"'.$readonly));
                                    }
                                    $this->table->add_row('Pages of sequence listing', form_input('pages_sequence_listing', $case['number_pages_sequence'], 'class="price-adjust"'.$readonly));
                                    $search_report_location_values = array(
                                        'ep' => 'Europe',
                                        'us' => 'USA',
                                        'jp_ru_au_cn_kr' => 'JP, RU, AU, CN, KR',
                                        'ca' => 'Canada',
                                    );
                                    $this->table->add_row('Search report location', form_dropdown('search_report_location', $search_report_location_values, $case['search_location'], 'class="price-adjust"'.$disable));
                                    $currencies = array(
                                        'usd' => 'USD',
                                        'euro' => 'EURO'
                                    );
                                    $this->table->add_row('Display in', form_dropdown('estimate_currency', $currencies, $case['case_currency'], 'class="price-adjust"'.$disable));
                                    $entities = array(
                                        'small' => 'Small Entity',
                                        'large' => 'Large Entity',
                                        'individual' => 'Individual Entity',
                                    );
                                    $this->table->add_row('Entity', form_dropdown('entity', $entities, $case['entity'], 'class="price-adjust"'.$disable));
                                    echo $this->table->generate();
                                    if($case['common_status']=='active' || $case['common_status']=='completed'){
                                    if($this->session->userdata('type') == 'supervisor'){
                                    echo '<br/><a href="' . base_url() . 'estimates/add_country_to_estimate_form/' . $case['case_number'] . '" class="popup"><button class="orange 200px">Add a country</button></a>';
                                    ?>

                                    <p><a class="button" href="javascript:void(0);" id="recalculate_estimate">
                                            <button class="light-red 200px">Display calculations</button>
                                        </a>
                                    </p>
                                    <p><a class="button" href="javascript:void(0);" id="save_estimate_link">
                                            <button class="green 200px">Save an Estimate</button>
                                        </a>
                                    </p>
                                    <?php }else{?>
                                    <p><a class="button" href="" id="escalate_esimate">
                                     <button class="grey 200px">Escalate</button>
                                        </a>
                                    </p>
                                    <?php }} else{
                                     echo '<br/><a href="' . base_url() . 'estimates/add_country_to_estimate_form/' . $case['case_number'] . '" class="popup"><button class="orange 200px">Add a country</button></a>';
                                    ?>

                                    <p><a class="button" href="javascript:void(0);" id="recalculate_estimate">
                                    <button class="light-red 200px">Display calculations</button>
                                        </a>
                                    </p>
                                    <p><a class="button" href="javascript:void(0);" id="save_estimate_link">
                                            <button class="green 200px">Save an Estimate</button>
                                        </a>
                                    </p>
                                    <?php } ?>
                                    <p>

                                        <?php if($case['is_intake'] =='0'){?>
                                        <br/>
										<?php echo form_checkbox('is_visible_to_client', '1', $case['estimate_available_for_client'], 'id="make_available_to_client"'); ?>
                                        - Visible to client
                                        <?php }?>
                                    </p>

                                    <p><a class="button" id="generate_estimate_pdf" href="javascript:void(0);">
                                            <button class="blue 200px">Generate a PDF</button>
                                        </a></p>
                                    <!-- <p><a class="button" id="view_estimate_pdf" href="javascript:void(0);">
                                            <button class="blue 200px">View PDF</button>
                                        </a></p> -->
                                    <?php
                                    $link_class = '';
                                    if ($case['estimate_sent_to_client'] == '1')
                                    {
                                        $link_class = 'disabled';
                                    }
                                    ?>
                                    <p>
                                        <a class="popup button <?php echo $link_class ?>"
                                           href="<?php echo base_url(); ?>estimates/send_estimate_pdf_to_client_form/<?php echo $case['case_number'] ?>">
                                            <button class="blue 200px">Send PDF to Client</button>
                                        </a>
                                    </p>
                                    <?php
                                    $link_class = '';
                                    if ($case['estimate_available_for_client'] == '1')
                                    {
                                        $link_class = 'disabled';
                                    }
                                    ?>
                                    <!--<p>
                                                                <a class="button <?php echo $link_class ?>" href="javascript:void(0);" id="estimate-make-available-to-client">
                                                                        <button class="blue 200px">Make available to client</button>
                                                                </a>
                                                        </p>-->
                                    <?php
                                    $link_class = '';
                                    if ($case['estimate_sent_to_bdv'] == '1')
                                    {
                                        $link_class = 'disabled';
                                    }
                                    ?>
                                    <p><a class="popup button <?php echo $link_class ?>"
                                          href="<?php echo base_url() ?>estimates/send_estimate_pdf_to_bdb_form/<?php echo $case['case_number'] ?>">
                                            <button class="blue 200px">Send PDF to BDV</button>
                                        </a></p>

                                    <!-- Unhighlight button just for active cases -->
                                    <?php if ($case['common_status'] == 'active'): ?>
                                        <?php if ($case['highlight'] == '1'): ?>
                                            <br/>
                                            <p><a class="button dark-grey 200px" id="unhighlight-button"
                                                  href="<?php echo base_url() ?>cases/unhighlight/<?php echo $case['case_number'] ?>">Unhighlight</a> -
                                                Removes Green, Yellow & Red highlight in an active case</p>
                                    <?php endif; ?>
                                <?php endif; ?>
                                </div>
                                <?php
                                echo form_close();
                                ?>
                            </div>


                            <div id="fee-schedule">
                                <div id="wide-box">
                                    <?php
                                    echo form_open('/cases/reload_customer_fees/' . $case['user_id'] . '/' . $case['case_number']);
                                    echo form_submit('submit', 'Sync with master fee table');
                                    echo form_close();

                                    $this->table->clear();
                                    $tmpl = array(
                                        'table_open' => '<table border="0" cellpadding="5" cellspacing="0" width="100%" id="customer-fees-table" class="data-table">',
                                    );
                                    $this->table->set_template($tmpl);
                                    $this->table->set_heading('Country', 'Filing Fee-Level 1', 'Filing Fee-Level 2', 'Filing Fee-Level 3', 'Translation Rate-Level 1', 'Translation Rate-Level 2', 'Translation Rate-Level 3');

                                    if (check_array($customer_countries))
                                    {
                                        foreach ($customer_countries as $country_item)
                                        {
                                            if (is_array($case_countries) && in_array($country_item['country_id'], $case_countries))
                                            {
                                                echo form_hidden('customer_countries[]', $country_item['country_id']);
                                                echo form_hidden('case_types[]', $country_item['case_type_id']);
                                                $this->table->add_row($country_item['country'], form_input('filing_fee_level_1[]', $country_item['filing_fee_level_1']), form_input('filing_fee_level_2[]', $country_item['filing_fee_level_2']), form_input('filing_fee_level_3[]', $country_item['filing_fee_level_3']), form_input('translation_rate_level_1[]', $country_item['translation_rate_level_1']), form_input('translation_rate_level_2[]', $country_item['translation_rate_level_2']), form_input('translation_rate_level_3[]', $country_item['translation_rate_level_3']));
                                            }
                                        }
                                        echo $this->table->generate();
                                        $this->table->clear();
                                    }
                                    ?>
                                </div>
                                <?php
                                echo '<p>&nbsp;</p>';
                                echo form_submit('submit', 'Save', 'id="save_customer_fees"');
                                echo form_close();
                                ?>
                            </div>

                            <div id="estimate-notes">
                                <p>
                                    <span class="outer-square">
                                        <span class="inner-square" style="background-color: #FFCCCC;">&nbsp;</span>
                                    </span>
                                    <strong>- Client notes</strong>
                                </p>
                                <p>
                                    <span class="outer-square">
                                        <span class="inner-square" style="background-color: #E9D578;">&nbsp;</span>
                                    </span>
                                    <strong>- FA notes</strong>
                                </p>
                                <p>
                                    <span class="outer-square">
                                        <span class="inner-square" style="background-color: #CCCCCC;">&nbsp;</span>
                                    </span>
                                    <strong>- Case notes</strong>
                                </p>
                                <?php
                                $new_note = array(
                                    'id' => 'new_note_est',
                                    'name' => 'new_note_est',
                                    'rows' => 3,
                                    'cols' => 80,
                                    'style' => 'width: 100%'
                                );
                                echo form_textarea($new_note);
                                echo '<br/>';
                                echo form_button('submit', 'Add a case note', 'id="add_new_note_est" class="light-red"');
                                echo '&nbsp;';
                                echo form_button('submit', 'Add a client note', 'id="add_client_note_est" class="light-red"');
                                $tmpl = array(
                                    'table_open' => '<table border="0" cellpadding="5" cellspacing="0" width="100%" id="estimates-notes" class="data-table">',
                                );

                                $this->table->set_template($tmpl);
                                $this->table->set_heading('Note', 'Username', 'Date', '&nbsp;');
                                $columns_exist = false;
                                if (check_array($client_notes)){
                                    foreach ($client_notes as $client_note){
                                        $row_class = 'client-note-row';
                                        $this -> table -> add_row(
                                        array('data' => nl2br($client_note['note']), 'class' => $row_class),
                                        array('data' => $client_note['username'], 'class' => 'username ' . $row_class),
                                        array('data' => $client_note['created_at'], 'class' => 'created_at ' . $row_class),
                                        array('data' => '<a title="Remove" href="javascript:void(0);" id="delete_note_link_' . $client_note['id'] . '" onclick="if(confirm(\'Do you really want to delete the selected note?\')){ remove_note_from_case(' . $client_note['id'] . ');}"><img src="' . base_url() . 'assets/images/i/delete.png" alt="Remove"/></a>', 'class' => $row_class));
                                        $columns_exist = true;
                                    }
                                }
                                if (check_array($fa_notes))
                                {
                                foreach ($fa_notes as $fa_note)
                                {
                                $row_class = 'fa-note-row';
                                $this -> table -> add_row(
                                array('data' => nl2br($fa_note['note']), 'class' => $row_class),
                                array('data' => $fa_note['username'], 'class' => 'username ' . $row_class),
                                array('data' => $fa_note['created_at'], 'class' => 'created_at ' . $row_class),
                                array('data' => '<a title="Remove" href="javascript:void(0);" id="delete_note_link_' . $fa_note['id'] . '" onclick="if(confirm(\'Do you really want to delete the selected note?\')){ remove_note_from_case(' . $fa_note['id'] . ');}"><img src="' . base_url() . 'assets/images/i/delete.png" alt="Remove"/></a>', 'class' => $row_class)
                                );
                                $columns_exist = true;
                                }
                                }
                                if ( ! empty($case['additional'])){  // Insert case instructions
                                    $row_class = 'client-instructions-row';
                                    $this -> table -> add_row(
                                    array('data' => nl2br($case['additional']), 'class' => $row_class),
                                    array('data' => $customer['username'], 'class' => 'username ' . $row_class),
                                    array('data' => $case['created_at'], 'class' => 'created_at ' . $row_class),
                                    array('data' => '', 'class' => $row_class));
                                    $columns_exist = true;
                                }
                                if (check_array($case_notes)){
                                    foreach ($case_notes as $case_note){
                                        $row_class = 'case-note';
                                        $this -> table -> add_row(
                                        array('data' => nl2br($case_note['note']), 'class' => $row_class),
                                        array('data' => $case_note['username'], 'class' => 'username ' . $row_class),
                                        array('data' => $case_note['created_at'], 'class' => 'created_at ' . $row_class),
                                        array('data' => '<a title="Remove" href="javascript:void(0);" id="delete_note_link_' . $case_note['id'] . '" onclick="if(confirm(\'Do you really want to delete the selected note?\')){ remove_note_from_case(' . $case_note['id'] . ');}"><img src="' . base_url() . 'assets/images/i/delete.png" alt="Remove"/></a>', 'class' => $row_class));
                                        $columns_exist = true;
                                    }
                                }
                                if(!$columns_exist){
                                    $this -> table -> add_row(
                                    array('data' => ''),
                                    array('data' => '', 'class' => 'username'),
                                    array('data' => '', 'class' => 'created_at'),
                                    '');
                                }
                                echo $this->table->generate();
                                $this->table->clear();
                                ?>
                            </div>
                          <!--  <div id="estimate-sow">
                            	<table width="100%" cellspacing="0" cellpadding="5" border="0" class="data-table">
                            	<tr>
                            		<td width="20%">SOW Text Line 2</td>
                            		<td><input id="sow_text_line2" type="text"/></td>
                            		<td  width="30%"><button id="sow_include" class="light-red">INCLUDE</button></td>
                            	</tr>
                            	<tr>
                            		<td>Company</td>
                            		<td><input type="text"/></td>
                            		<td>&nbsp;</td>
                            		
                            	</tr>
                            	<tr>
                            		<td>Contact</td>
                            		<td><input type="text"/></td>
                            		<td>&nbsp;</td>
                            	</tr>
                            	<tr>
                            		<td>Phone Number</td>
                            		<td><input type="text"/></td>
                            		<td>&nbsp;</td>
                            	</tr>
                            	<tr>
                            		<td>Address</td>
                            		<td><input type="text"/></td>
                            		<td>&nbsp;</td>
                            	</tr>
                            	<tr>
                            		<td>&nbsp;</td>
                            		<td><input type="text"/></td>
                            		<td>&nbsp;</td>
                            	</tr>
                            	<tr>
                            		<td>Email</td>
                            		<td><input type="text"/></td>
                            		<td>&nbsp;</td>
                            	</tr>
                            	<tr>
                            		<td>CC (separated by comma)</td>
                            		<td><input type="text"/></td>
                            		<td>&nbsp;</td>
                            	</tr>
                            	<tr>
                            		<td>&nbsp;</td>
                            		<td>&nbsp;</td>
                            		<td><button id="generate_sow_pdf" class="blue">Generate SOW PDF</button></td>
                            	</tr>
                            	<tr>
                            		<td>&nbsp;</td>
                            		<td>&nbsp;</td>
                            		<td><button id="modify_sow_pdf" class="light-red">MODIFY</button> *Case Level and affects SOW PDF</td>
                            	</tr>
                            	</table>
                            </div>
                        </div>
                    </div>-->
                        </div></div>
                    <!-- Estimate End -->
                    
                    <!-- Finance -->

                        <div id="finance">
                        <div id="finance-tabs">
                            <ul>
                                <li><a href="#finance-filing-costs">Filing Costs</a></li>
                                <li><a href="#finance-translation-costs">Translation Costs</a></li>
                                <li><a href="#finance-profitability">Profitability</a></li>
                            </ul>
                                    <?php $this->load->view('cases/finance/filing_costs') ?>
                            <div id="finance-translation-costs">Translation Costs</div>
                            <div id="finance-profitability">Profitability</div>
						</div>
					</div>
                    <!-- Finance End -->
                    
                </div>
