<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.validate.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$("form#customer").validate({
			rules: {
				associate: "required",
				fee: "required"
			}
		});
	});
</script>
<?php
	echo form_open('/cases/update_custom_associate/'.$custom_associate['id'].'/'.$case_number);
	echo form_hidden('country_id', $custom_associate['country_id']);
	$associate = array(
		'name'	=>	'associate',
		'id'	=>	'associate',
		'value' =>  $custom_associate['associate'],
		'cols'	=>	'30',
		'rows'	=>	'6'
	);
	$this -> table -> add_row('Associate', form_textarea($associate));
	$this -> table -> add_row('Email', form_input('email', $custom_associate['email']));
	$this -> table -> add_row('Professional Fee', form_input('fee', $custom_associate['fee']));
	$currencies = array(
		'usd'	=>	'usd',
		'euro'	=>	'euro'
	);
	$this -> table -> add_row('Fee Currency', form_dropdown('fee_currency', $currencies, $custom_associate['fee_currency']));
	$this -> table -> add_row('Contact Name', form_input('contact_name', $custom_associate['contact_name']));
	$this -> table -> add_row('Reference Number', form_input('reference_number', $custom_associate['reference_number']));
	$this -> table -> add_row('&nbsp;', form_submit('submit', 'Update'));
	echo $this -> table -> generate();
	echo form_close();
?>