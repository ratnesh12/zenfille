<?php
	echo form_open('/profile/update/');
	$this -> table -> add_row('First Name', form_input('firstname', $profile['firstname']));
	$this -> table -> add_row('Last Name', form_input('lastname', $profile['lastname']));
	$this -> table -> add_row('Email', form_input('email', $profile['email']));
	$this -> table -> add_row('Phone', form_input('phone', $profile['phone']));
	$this -> table -> add_row('&nbsp;', form_submit('submit', 'Submit'));
	echo $this -> table -> generate();
	echo form_close();
?>