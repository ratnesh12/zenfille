<script type="text/javascript">
	function clear_countries(){
		$('div.popup option:selected').removeAttr('selected');
	}

</script>


<?php
	$approved_countries = array();
	if (check_array($list_estimate_countries))
	{
		foreach ($list_estimate_countries as $approved_country)
		{
			if ($approved_country['is_approved'] == '1' || $approved_country['pm_approved_after_client'] == '1')
			{
				$approved_countries[] = $approved_country['country_id'];
			}
		}
	}
	$dd_countries = array();
	if (check_array($countries))
	{
		foreach ($countries as $country)
		{
			if (in_array($country['id'], $approved_countries))
			{				
				$dd_countries[$country['id']] = $country['country'];
			}
		}
	}
	$file_countries_id = array();

	if (check_array($file_countries))
	{
		foreach ($file_countries as $file_country)
		{
			$file_countries_id[] = $file_country['country_id'];
		}
	}
	echo form_open('/cases/assign_file_to_countries/'.$file['id'].'/'.$case_number);
	$tmpl = array (
            'table_open'          => '<table border="0" cellpadding="4" cellspacing="0" class="data-table">',
    );
	$this -> table -> set_template($tmpl); 
	$this -> table -> add_row('FileName', $file['filename']);
	$this -> table -> add_row('Filesize', $file['filesize']);
	$this -> table -> add_row('Created at', $file['created_at']);
	$this -> table -> add_row('Countries', form_multiselect('countries[]', $dd_countries, $file_countries_id, 'size="10"'));
	$this -> table -> add_row('&nbsp;', form_submit('submit', 'Assign').' '.form_button(array('class'=>'red_button'),'Clear','onclick="clear_countries();"'));
	echo $this -> table -> generate();
	echo form_close();
?>