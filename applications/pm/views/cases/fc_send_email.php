<html>
<head>
<title>Send Email</title>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-1.7.1.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/ckeditor/adapters/jquery.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery_notification_v.1.js"></script>

<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/jquery_notification.css"/>
<link href="<?php echo base_url(); ?>assets/css/skin.css?v=<?php echo get_file_modification_time('assets/css/skin.css')?>" media="screen" rel="stylesheet" type="text/css" />
</head>

<body>
	<script type="text/javascript">
		$(document).ready(function () {
			var CKEDITOR_BASEPATH = 'assets/js/ckeditor/';
			$("#fc_email_textarea").ckeditor({
				toolbar:'Zen',
				uiColor:'#CCCCCC'
			});
			var country_id = "<?php echo $country['id'] ?>";
			var extension_needed = "";
	        var translation_needed = "<?php echo $translation; ?>";
	        var case_id = "<?php echo $case['id'] ?>";
	        var case_number = "<?php echo $case['case_number'] ?>";
	        var case_type_id = "<?php echo $case['case_type_id'] ?>";
	        var email_type = "filing-confirmation";
	        $.post("<?php echo base_url(); ?>cases/get_email_text/", {case_id:case_id, case_number:case_number, country_id:country_id, case_type_id:case_type_id, email_type:email_type, extension_needed:extension_needed, translation_needed:translation_needed}, function (result) {
	            $("#fc_email_textarea").val(result.text);
	            $("#fc_email_subject").val(result.subject);
                $("#zip_hash").val(result.zip_hash);
	        }, "json");

			$("#fc_email_send_button").click(function () {
		        var text = $("#fc_email_textarea").val();
		        if (text == "") {
		            alert("Your email is empty!");
		            return false;
		        }
		        var case_number = "<?=$case['case_number'];?>";
		        var country_id = "<?=$country['id'];?>";
		        var to = $("input#fc_email_to").val();
		        var cc = $("input#fc_email_cc").val();
		        var subject = $("input#fc_email_subject").val();
                var zip_hash = $("#zip_hash").val();

		        $.post("<?php echo base_url(); ?>cases/send_notification_email/", {cc:cc, case_number:case_number, text:text, to:to, subject:subject, country_id:country_id, zip_hash:zip_hash}, function (result) {
		            showNotification({
		                message:result.text,
		                type:result.type,
		                autoClose:true,
		                duration:5
		            });
		            if(result.type != 'error'){
		            	window.opener.$("#country_file_row_<?=$country['id']?>").find('td.fc_send_email').attr('class','fc_send_email_done');
		            	window.opener.$('button[id='+<?=$country['id']?> + '][name="fr_sent"]').removeClass('tracker_inactive').addClass('tracker_required').val('<?=date('m/d/y');?>');
		            	setTimeout('window.close()', 2000);
		            }
		            result = null;
		        }, 'json');
		    });
			
		});
	</script>
	
	
	<div id="notification_email_filing_confirmation">
		<?php
		$tmpl = array(
			'table_open' => '<table border="0" cellpadding="2" cellspacing="0" width="100%" class="data-table pm-assoc">'
		);
		$this->table->set_template($tmpl);
		
		echo form_hidden('fc_email_country_id', FALSE);
		$this->table->add_row('To:', form_input('fc_email_to', $customer['email'], 'id="fc_email_to" style="width: 90%;"'));
		$this->table->add_row('CC:', form_input('fc_email_cc', $contacts, 'id="fc_email_cc" style="width: 90%;"'));
		$this->table->add_row('Subject:', form_input('fc_email_subject', FALSE, 'id="fc_email_subject" style="width: 90%;"'));
		$this->table->add_row(array('data' => form_textarea('fc_email_textarea', FALSE, 'id = "fc_email_textarea"'), 'colspan' => 2));
		$this->table->add_row(form_button('fc_email_send_button', 'Send email', 'id="fc_email_send_button"'), '&nbsp;');
        $this->table->add_row('<input type="hidden" id="zip_hash" name="zip_hash" value="">');
		echo $this->table->generate();
		$this->table->clear();
		?>
	</div>
</body>
</html>