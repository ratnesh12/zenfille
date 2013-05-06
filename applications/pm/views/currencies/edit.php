<?php
	echo anchor('/currencies/', 'Back to list');
	echo form_open('/currencies/update_record/'.$currency['id']);
	$tmpl = array (
	        'table_open'          => '<table border="0" cellpadding="4" cellspacing="0" class="data-table">',
	);
	$this -> table -> set_template($tmpl);
	$this -> table -> add_row('Currency Code', $currency['code']);
	$this -> table -> add_row('Rate', form_input('rate', $currency['rate']));
	$this -> table -> add_row('&nbsp;', form_submit('submit', 'Update'));
	echo $this -> table -> generate();
	echo form_close();
?>