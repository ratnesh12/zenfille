<script type="text/javascript">
	$(document).ready(function() {
		$("input[name='search_string']").click(function() {
			$(this).css("color", "#000000");
			if ($(this).val() == "Country") {
				$(this).val("");
			}
		});
	});
</script>
<?php
	echo anchor('/fees/create/', 'Create Fee').'  '.anchor('/fees/footnote_list/', 'Footnotes');
	echo form_open('/fees/');
	if (empty($search_string)) 
	{
		$search_string = 'Country';
	}
	echo form_input('search_string', $search_string, 'style="color: #CCCCCC;"').'&nbsp;';
	echo form_submit('do_search', 'Search', 'class="button"');
	echo form_close();
	if (check_array($fees))
	{
		$tmpl = array ('table_open' => '<table border="0" cellpadding="4" cellspacing="0" class="data-table">');
		$this -> table -> set_template($tmpl); 
		$this -> table -> set_heading('#', 'Country', '&nbsp;', '&nbsp;');
		$index = 1;
		foreach ($fees as $fee)
		{
			$this -> table -> add_row($index,
									  $fee['country'],
									  anchor('/fees/edit/'.$fee['country_id'], '<img src="'.base_url().'assets/images/i/edit.png" alt="Edit"/>'),
									  anchor(
									  		'/fees/delete/'.$fee['country_id'], 
									  		'<img src="'.base_url().'assets/images/i/delete.png" alt="Delete"/>',
									  		'onclick="return confirm(\'Are you sure you want to delete '.$fee['country'].' fee?\')"'
									  )
								  );
			$index++;
		}
		echo $this -> table -> generate();
	}
?>