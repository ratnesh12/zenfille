<?php
	echo anchor('/park_fees/create_edit/', 'Create a Fee');
	echo form_open('/park_fees/');
	if(!isset($search_string)){
		$search_string = '';
	}
	echo form_input('search_string', $search_string, 'style="color: #CCCCCC; width: 300px;" placeholder="Language"').'&nbsp;';
	echo form_submit('do_search', 'Search', 'class="button"');
	echo form_close();
	if (check_array($park_fees))
	{
		$tmpl = array ('table_open' => '<table border="0" cellpadding="4" cellspacing="0" class="data-table" id="park_fees_table">');
		$this -> table -> set_template($tmpl); 
		$this -> table -> set_heading(
			'#', 
			'Target Language', 
			'Park Client Rate', 
			'Zenfile Client Rate',
			'Zenfile Rate',
			'Cost', 
			'Park Rate Into English', 
			'Zenfile Rate Into English',
			'&nbsp;',
			'&nbsp;'
		);
		$index = 1;
		foreach ($park_fees as $fee)
		{
			$this -> table -> add_row(
				$index,
				$fee['target_language'],
				$fee['standart_rate'],
				$fee['zenfile_client_rate'],
				$fee['zenfile_rate'],
				$fee['cost'],
				$fee['into_english'],
				$fee['zenfile_into_english'],
				anchor('/park_fees/create_edit/'.$fee['id'], '<img src="'.base_url().'assets/images/i/edit.png" alt="Edit"/>'),
				anchor(
					'/park_fees/delete/'.$fee['id'], 
					'<img src="'.base_url().'assets/images/i/delete.png" alt="Delete"/>',
					'onclick="return confirm(\'Are you sure you want to delete '.$fee['target_language'].' fee?\')"'
				)
			);
			$index++;
		}
		echo $this -> table -> generate();
	}else{?>
		<div style="text-align: center;">Fees not found.</div>
	<?php }
?>