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
	echo form_open('/currencies/');
	if (empty($search_string)) 
	{
		$search_string = 'Country';
	}
	echo form_input('search_string', $search_string, 'style="color: #CCCCCC;"').'&nbsp;';
	echo form_submit('do_search', 'Search', 'class="button"');
	echo form_close();

	if (check_array($currencies))
	{
		$tmpl = array (
		        'table_open'          => '<table border="0" cellpadding="4" cellspacing="0" class="data-table">',
		);
		$this -> table -> set_template($tmpl); 
		$this -> table -> set_heading('#', 'Country', 'Currency Code', 'Exchange Rate', 'Last Update', '&nbsp;');
		$i = 1;
		foreach ($currencies as $currency)
		{
			$this -> table -> add_row($i, 
									  $currency['country'], 
									  $currency['code'], 
									  $currency['rate'], 
									  $currency['last_update'], 
									  anchor('/currencies/edit/'.$currency['cur_id'], '<img src="'.base_url().'assets/images/i/edit.png" title="Edit" alt="Edit" />'));
			$i++;
		}
		echo $this -> table -> generate();
	}
?>