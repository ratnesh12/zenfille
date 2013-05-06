<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/ckeditor/adapters/jquery.js"></script>
<script type="text/javascript">
	<?php $uniq_textarea_class = uniqid()?>
	var uniq_textarea_class = "<?php echo $uniq_textarea_class ?>";
	var CKEDITOR_BASEPATH = 'assets/js/ckeditor/';
	$(document).ready(function() {
		
		if(CKEDITOR.instances["email-text-c"]) {
	    	delete CKEDITOR.instances["email-text-c"];
		}
		$("#email-text-c").ckeditor({
	        toolbar : 'Zen',
	        uiColor : '#CCCCCC'
    	});
		
		$("#delete_attachment").click(function() {
			$("input[name='estimate_exists']").val("0");
			$(this).parent("td").html("");
		});
        <?php if(!empty($case_manager)){?>
            $("#send_green_gray").css('background-color', '#CCCCCC');
    <?php }?>

	});
</script>
<?php
	echo form_open('/estimates/send_estimate_pdf/'.$case['id'].'/client');
	echo form_hidden('case_id', $case['id']);
	echo form_hidden('estimate_exists', '1');
	$tmpl = array(
    	'table_open' => '<table border="0" cellpadding="5" cellspacing="0" width="100%" class="data-table-big" style="width: 800px;">'
    );

	$this -> table -> set_template($tmpl);
	$to_email = TEST_MODE ? TEST_CLIENT_EMAIL : $client['email'];
if($client['type'] == 'firm'){
    $to_email = TEST_MODE ? TEST_FIRM_EMAIL : $client['email'];
}
	$this -> table -> add_row('To', form_input('to_email', $to_email , 'style="width: 95%"'));
	$this -> table -> add_row('&nbsp', '('.$client['firstname'].' '.$client['lastname'].')');
	$this -> table -> add_row('Subject', form_input('email_subject', $email_subject, 'style="width: 95%"'));
	$text = array(
		'cols'	=>	60,
		'rows'	=>	6,
		'name'	=>	'email_content',
		'id'	=>	'email-text-c',
		'value' =>	 $email_text,
		'class'	=>	$uniq_textarea_class
	);
	$this -> table -> add_row(array('data' => form_textarea($text), 'colspan' => 2));
	if ( ! is_null($estimate))
	{
		$this -> table -> add_row('Estimate', $estimate['filename'].'&nbsp;&nbsp;&nbsp;<a href="'.base_url().'cases/view_file/'.$estimate['id'].'"><img src="'.base_url().'assets/images/i/eye-14-14.png" alt="View"/></a><a id="delete_attachment"href="javascript:void(0);"><img src="'.base_url().'assets/images/i/delete.png" alt="Delete"/></a>');
	}
if(!empty($case_manager)){
    $this ->table -> add_row('Already sent by', $case_manager.' '.$pdf_sent_to_client_date);
}
	$this -> table -> add_row('', form_submit('submit', 'Send', 'id=send_green_gray'));
	echo $this -> table -> generate();
	echo form_close();
?>