<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/ckeditor/adapters/jquery.js"></script>
<script type="text/javascript">
	var CKEDITOR_BASEPATH = 'assets/js/ckeditor/';
	$(document).ready(function() {
		if(CKEDITOR.instances["email-text-c"]) {
	    	delete CKEDITOR.instances["email-text-c"];
		}
		$("#email-text-c").ckeditor({
	        toolbar : 'Zen',
	        uiColor : '#CCCCCC'
    	});
	});
</script>
<?php
	echo form_open('/estimates/send_estimate_pdf/'.$case['id'].'/bdv');
	echo form_hidden('case_id', $case['id']);
	echo form_hidden('estimate_exists', '1');
	$tmpl = array(
    	'table_open' => '<table border="0" cellpadding="5" cellspacing="0" width="100%" class="data-table-big" style="width: 800px;">'
    );

	$this -> table -> set_template($tmpl);
	$this -> table -> add_row('To', form_input('to_email', $bdv['email'], 'style="width: 75%"').' ('.$bdv['firstname'].' '.$bdv['lastname'].')');
	$this -> table -> add_row('Subject', form_input('email_subject', $email_subject, 'style="width: 95%"'));
	$text = array(
		'cols'	=>	60,
		'rows'	=>	6,
		'name'	=>	'email_content',
		'id'	=>	'email-text-c',
		'value' =>	 $email_text,
	);
	$this -> table -> add_row(array('data' => form_textarea($text), 'colspan' => 2));
	if ( ! is_null($estimate))
	{
		$this -> table -> add_row('Estimate', $estimate['filename'].'&nbsp;&nbsp;&nbsp;<a href="'.base_url().'cases/view_file/'.$estimate['id'].'"><img src="'.base_url().'assets/images/i/eye-14-14.png" alt="View"/></a>');
	}
	$this -> table -> add_row('', form_submit('submit', 'Send'));
	echo $this -> table -> generate();
	echo form_close();
?>