<script type="text/javascript">
	function form_send(){
		$.post(
			"<?php echo base_url(); ?>cases/assign_files_to_countries/", 
			{fids:<?= json_encode($fids)?>,countries:$('select.assign_countries').val()},
			function(){
				$.facebox.close();
				get_files_table();
			}
		);
		
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

	$tmpl = array (
            'table_open'          => '<table border="0" cellpadding="4" cellspacing="0" class="data-table">',
    );
	$this -> table -> set_template($tmpl);
	foreach($files as $file){ 
		$this -> table -> add_row('FileName', $file['filename']);
	}
	$this -> table -> add_row('Countries', form_multiselect('countries[]', $dd_countries, array(), 'size="10" class="assign_countries"'));
	$this -> table -> add_row('&nbsp;', form_button('submit', 'Assign',"onclick='form_send($fids);'"));
	echo $this -> table -> generate();
?>
