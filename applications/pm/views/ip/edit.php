<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.validate.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$("#edit_ip_form").validate({
			rules: {
				ip_address: "required"
			}
		});
	});
</script>
<?php
	$tmpl = array ('table_open' => '<table border="0" cellpadding="4" cellspacing="0" class="hor-minimalist">');
	$this -> table -> set_template($tmpl); 
	echo validation_errors(); 
	echo form_open('/ip/update/'.$ip['id'], array('id' => 'edit_ip_form'));
	$this -> table -> add_row('IP address', form_input('ip_address', $ip['ip_address'], 'id="ip_address"'));
	$this -> table -> add_row('Active', form_checkbox('is_active', '1', $ip['is_active'], 'id="is_active"'));
	$this -> table -> add_row('Description', form_input('description', $ip['description'], 'id="description"'));
	$this -> table -> add_row('', form_submit('submit', 'Update'));
	echo $this -> table -> generate();
	echo form_close();
?>