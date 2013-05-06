<?php
	echo anchor('/ip/create/', 'Create white IP address'); 
	if (check_array($ip_list))
	{
		$tmpl = array ('table_open' => '<table border="0" cellpadding="4" cellspacing="0" class="hor-minimalist">');
		$this -> table -> set_template($tmpl); 
		$this -> table -> set_heading('', 'IP address', 'Active', 'Description', '&nbsp;', '&nbsp;');
		$i = 1;
		foreach ($ip_list as $ip)
		{
			$this -> table -> add_row($i, 
									  $ip['ip_address'],
									  ($ip['is_active'] == '1') ? 'Yes' : 'No',
									  $ip['description'],
									  anchor('/ip/edit/'.$ip['id'], '<img src="'.base_url().'assets/images/i/edit.png" alt="Edit"/>'),
									  '<a title="Remove" href="javascript:void(0);" onclick="if(confirm(\'Do you really want to delete selected IP address?\')){ document.location.href = \''.base_url().'ip/delete/'.$ip['id'].'\';}"><img src="'.base_url().'assets/images/i/delete.png" alt="Remove"/></a>');
			$i++;
		}
		echo $this -> table -> generate();
	}
?>