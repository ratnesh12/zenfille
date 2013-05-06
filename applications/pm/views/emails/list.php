<?php
	//echo anchor('/emails/create/', 'Create a template', 'class="popup"');
	echo anchor_popup('/emails/available_variables/', 'Available Variables');
    echo '<p>'.anchor('/emails/create/', 'Create New Email Template').'</p>';
	if (check_array($emails))
	{
		$tmpl = array (
                'table_open'          => '<table border="0" cellpadding="4" cellspacing="0" class="data-table">',
        );
		$this -> table -> set_template($tmpl); 
		$this -> table -> set_heading('#', 'Title', 'Description', '&nbsp;');
		$i = 1;
		foreach ($emails as $email)
		{
			$this -> table -> add_row($i, 
									  $email['title'], 
									  $email['description'], 
									  anchor('/emails/edit/'.$email['id'], '<img src="'.base_url().'assets/images/i/edit.png" alt="Edit" title="Edit" />'));
			$i++;
		}
		echo $this -> table -> generate();
	}
?>