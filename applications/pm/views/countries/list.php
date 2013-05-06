<?php
	echo anchor('/countries/edit/', 'Create a country');
	if (check_array($countries))
	{
		$tmpl = array (
		        'table_open'          => '<table border="0" cellpadding="4" cellspacing="0" class="data-table country_align">',
		);
		$this -> table -> set_template($tmpl); 
		$this -> table -> set_heading('#', 'Country', 'PCT Language', 'EP Language', 'Direct Filing Language', 'Target Language', '&nbsp;', '&nbsp;');
		$i = 1;
		foreach ($countries as $country)
		{
			$this -> table -> add_row($i, 
									  $country['country'], 
									  $country['pct_language'], 
									  $country['ep_language'], 
									  $country['direct_language'],
                                      $country['target_language'],
									  '<img class="flag_image" src="'.str_replace('pm', 'client', base_url()).$country['flag_image'].'" />', 
									  anchor('/countries/edit/'.$country['id'], '<img src="'.base_url().'assets/images/i/edit.png" title="Edit" alt="Edit" />'),
						                anchor(
						                	'/countries/delete/'.$country['id'], 
						                	'<img src="'.base_url().'assets/images/i/delete.png" title="Delete" alt="Delete" />',
						                	'onclick="return confirm(\'Are you sure you want to delete '.$country['country'].'?\')"'
						                )
                );
			$i++;
		}
		echo $this -> table -> generate();
	}
?>