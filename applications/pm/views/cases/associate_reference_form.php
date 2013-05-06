<?php
	$reference_number_val = '';
	if ( ! is_null($reference_number))
	{
		$reference_number_val = $reference_number['reference_number'];
	}
	echo form_open('cases/associate_reference_form_submit/'.$case_id.'/'.$case_number.'/'.$associate_id);
	echo form_input('reference_number', $reference_number_val);
	echo form_submit('submit', 'Submit');
	echo form_close();
?>