<?php
	echo anchor('/users/create/', 'Create a user'); 
	if (check_array($users))
	{
		$tmpl = array ('table_open'          => '<table border="0" cellpadding="4" cellspacing="0" class="data-table" id="active-cases-table">');
		$this -> table -> set_template($tmpl); 
		$this -> table -> set_heading('', 'Username', 'Firstname', 'Lastname', 'Email', 'Type','Deleted', '', '');
		$i = 1;
		foreach ($users as $user)
		{
            $type = $user['type'];
            if($user['type'] =='customer'){
                $type = 'client';
            }
			$this -> table -> add_row($i, 
									  $user['username'],
									  $user['firstname'],
									  $user['lastname'],
									  $user['email'],
                                      $type,
									  $user['is_deleted']?'yes':'no',
									  anchor('/users/edit/'.$user['id'].'/'.$user['type'], '<img src="'.base_url().'assets/images/i/edit.png" alt="Edit"/>'),
									  '<a title="Remove" href="javascript:void(0);" onclick="if(confirm(\'Do you really want to delete selected user?\')){ document.location.href = \''.base_url().'users/delete/'.$user['id'].'/'.$user['type'].'\';}"><img src="'.base_url().'assets/images/i/delete.png" alt="Remove"/></a>');
			$i++;
		}
		echo $this -> table -> generate();
	}
?>