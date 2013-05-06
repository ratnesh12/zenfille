<?php
	echo form_open('/auth/login/');
	echo form_input('username', FALSE).'&nbsp;'.form_password('password', FALSE);
	echo form_submit('submit', 'Submit');
	echo form_close();
?>