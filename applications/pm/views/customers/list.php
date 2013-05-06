<script type="text/javascript">
	$(document).ready(function() {
		$("input[name='search_string']").click(function() {
			$(this).css("color", "#000000");
			if ($(this).val() == "Company Name") {
				$(this).val("");
			}
		});
	});
</script>

<p><?php echo anchor('/clients/create/', 'Create a client'); ?></p>
<?php
	echo form_open('/clients/');
	if (empty($search_string)) 
	{
		$search_string = 'Company Name';
	}
	echo form_input('search_string', $search_string, 'style="color: #CCCCCC;"').'&nbsp;';
	echo form_submit('do_search', 'Search', 'class="button"');
	echo form_close();

	$tmpl = array (
            'table_open'          => '<table border="0" cellpadding="4" cellspacing="0" class="data-table">',
    );
	$this -> table -> set_template($tmpl); 
	if (check_array($customers))
	{
		$this -> table -> set_heading('#', 'Username', 'First Name', 'Last Name', 'Company', 'BDV', 'Email', 'Phone', '&nbsp;', '&nbsp;');
		$i = 1;
		foreach ($customers as $customer)
		{
			if($this->session->userdata('type') == 'supervisor'){
				$delete_link = " ".anchor(
					'/clients/set_deleted/'.$customer['id'], 
					'<img src="'.base_url().'assets/images/i/delete.png" alt="Delete" title="Delete"/>', 
					'onclick="return confirm(\'Are you sure you want to delete '.$customer['username'].'?\')"'
				);
			}else{
				$delete_link = "";
			}
			
			$this -> table -> add_row($i, 
			  $customer['username'], 
			  $customer['firstname'], 
			  $customer['lastname'], 
			  $customer['company_name'], 
			  $customer['bdv'], 
			  $customer['email'], 
			  $customer['phone_number'], 
			  anchor('/clients/edit/'.$customer['id'], '<img src="'.base_url().'assets/images/i/edit.png" alt="Edit" title="Edit"/>', 'class="popup"').$delete_link,
			  anchor_popup('/clients/login/'.$customer['id'], 'Login')
		  	);
			$i++;
		}
		echo $this -> table -> generate();
	}
?>