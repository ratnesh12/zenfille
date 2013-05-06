
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.validate.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$("form#customer").validate({
			rules: {
				associate: "required",
				fee: "required",
			}
		});
	});
</script>
<?php
	echo form_open('/cases/replace_associate/'.$case_number.'/'.$country_id."/".$associcate_id);
	echo form_hidden('country_id', $country_id);
        echo form_hidden('associcate_id', $associcate_id);
	$associate = array(
		'name'	=>	'associate',
		'id'	=>	'associate',
		'cols'	=>	'30',
		'rows'	=>	'6',
	);
	$this -> table -> add_row('Associate', form_textarea($associate));
	$this -> table -> add_row('Email', form_input('email', set_value('email')));
	$this -> table -> add_row('Professional Fee', form_input('fee', FALSE));
	$currencies = array(
		'usd'	=>	'usd',
		'euro'	=>	'euro',
	);
	$this -> table -> add_row('Fee Currency', form_dropdown('fee_currency', $currencies, FALSE));
	$this -> table -> add_row('Contact Name', form_input('contact_name', FALSE));
	$this -> table -> add_row('Reference Number', form_input('reference_number', FALSE));
	$this -> table -> add_row('&nbsp;', form_submit('submit', 'Replace'));
	echo $this -> table -> generate();
	echo form_close();
?>