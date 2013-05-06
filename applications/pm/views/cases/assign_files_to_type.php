<?php 

$file_types_dd = array('' => '');
	if (check_array($file_types))
	{
	    foreach ($file_types as $file_type)
	    {
	        $file_types_dd[$file_type['id']] = $file_type['name'];
	    }
	}

?>
<script type="text/javascript">
	function form_type_send(){
		if(!$('select.assign_type').val()){
			alert('Please, select the file type');
			return false;
		}
		$.post(
			"<?php echo base_url(); ?>cases/set_file_type/", 
			{file_id:<?= json_encode(explode(',', $fids))?>,file_type_id:$('select.assign_type').val()},
			function(data){
				var file_types = <?=json_encode($file_types_dd)?>;
				var file_type_id = $('select.assign_type').val();
				
				if(data.need_to_assign){
	            	showNotification({
	                    message:'Please make sure to assign a country to this file type: "'+file_types[file_type_id]+'"',
	                    type:"information",
	                    autoClose:true,
	                    duration:5
	                });
	            	jQuery.facebox({ ajax: '<?= base_url()?>cases/assign_files_to_countries_form/<?= $case_number; ?>/?fids=<?=$fids?>' });
	            }else{
	            	$.facebox.close();
	            }
				get_files_table(data.block_title);
			},
			"json"
		);
		
	}
</script>
<?php

	$tmpl = array (
            'table_open'          => '<table border="0" cellpadding="4" cellspacing="0" class="data-table">',
    );
	$this -> table -> set_template($tmpl);
	foreach($files as $file){ 
		$this -> table -> add_row('FileName', $file['filename']);
	}
	$this -> table -> add_row('Type', form_dropdown('type', $file_types_dd, array(), 'class="assign_type"'));
	$this -> table -> add_row('&nbsp;', form_button('submit', 'Assign',"onclick='form_type_send($fids);'"));
	echo $this -> table -> generate();
?>
