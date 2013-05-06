<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/jquery-ui/jquery-ui-1.8.16.custom.css" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/jquery-ui/jquery.ui.tabs.css" />
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-ui/jquery.ui.widget.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery-ui/jquery.ui.tabs.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.cookie.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$("input[name='search_string']").click(function() {
			$(this).css("color", "#000000");
			if ($(this).val() == "Country") {
				$(this).val("");
			}
		});
		$("#tabs").tabs({cookie: { expires: 30}  });
	});
</script>
<?php
    if (isset($message))
        echo $message.'<br />';
	echo anchor('/associates/edit/', 'Create an Associate', 'class="popup"');
	echo form_open('/associates/');
	if (empty($search_string)) 
	{
		$search_string = 'Country';
	}
	echo form_input('search_string', $search_string, 'style="color: #CCCCCC;"').'&nbsp;';
	echo form_submit('do_search', 'Search', 'class="button"');
	echo form_close();
	
?>
	<div id="tabs">
	<ul>
		<li><a href="#active-associates">Active</a></li>
		<li><a href="#replaced-associates">Replaced</a></li>
	</ul>
	<div id="active-associates" class="associate-tab">
<?php
	
	if (check_array($associates))
	{
		$tmpl = array (
		        'table_open'          => '<table border="0" cellpadding="4" cellspacing="0" class="data-table fees-table bluelines"> ',
            'row_start'           => '<tr class="blue">',
            'row_end'             => '</tr>',
            'row_alt_start'       => '<tr>',
            'row_alt_end'         => '</tr>'
		);
		$this -> table -> set_template($tmpl); 
		$this -> table -> set_heading('#', 'Country', 'Associate', 'Fee', 'Currency', 'Contact Name', 'Translation Required', 'Filing Type', 'GSA', '&nbsp;', '&nbsp;');
		$i = 1;
		foreach ($associates as $associate)
		{
            $filing = '';
            if($associate['30_months'] == '1'){
                $filing .= 'PCT 30 month<br/>';
            }
            if ($associate['31_months'] == '1'){
                $filing .= 'PCT 31 month<br/>';
            }
            if ($associate['ep_validation'] == '1'){
                $filing .= 'EP<br/>';
            }
            if($associate['is_direct_case_allowed'] == '1') {
                $filing .= 'Direct<br/>';
            }
//            $fa_data ='';
//            if( !empty($associate['name'])){
//                $fa_data = $associate['name'].'</br>';
//            }
//            if(!empty($associate['firm'])){
//                $fa_data .= $associate['firm'].'</br>';
//            }
//            if(!empty($associate['address'])){
//                $fa_data .= $associate['address'].'</br>';
//            }
//            if(!empty($associate['address2'])){
//                $fa_data .= $associate['address2'].'</br>';
//            }if(!empty($associate['phone'])){
//            $fa_data .= $associate['phone'].'</br>';
//        }
//            if(!empty($associate['fax'])){
//                $fa_data .= $associate['fax'].'</br>';
//            }
//            if(!empty($associate['email'])){
//                $fa_data .= $associate['email'].'</br>';
//            }
//            if(!empty($associate['website'])){
//                $fa_data .= $associate['website'].'</br>';
//            }
            $cell = array('data' => nl2br($associate['associate']), 'id' => 'blueleft');
			$this -> table -> add_row($i,
									  $associate['country'],
                                      $cell,
									  $associate['fee'],
									  $associate['fee_currency'],
									  $associate['contact_name'], 
									  ($associate['translation_required'] == 1) ? 'Yes' : 'No',
									  $filing,
									  (empty($associate['path_to_gsa_agreement'])) ? '' : 'YES',
									  anchor('/associates/edit/'.$associate['id'], '<img src="'.base_url().'assets/images/i/edit.png" title="Edit" alt="Edit" />'),
									  '<a href="javascript:void(0);" onclick=\'if(confirm("Are you sure want to delete selected associate?")){ document.location.href="'.base_url().'associates/delete/'.$associate['id'].'"}\'><img src="'.base_url().'assets/images/i/delete.png" alt="Delete" title="Delete" /></a>');
			$i++;
		}
		echo $this -> table -> generate();
	}
?>
</div>
	<div id="replaced-associates"  class="associate-tab">
<?php 	
	if (check_array($replaced_associates))
	{
		$tmpl = array (
		        'table_open'          => '<table border="0" cellpadding="4" cellspacing="0" class="data-table fees-table bluelines"> ',
            'row_start'           => '<tr class="blue">',
            'row_end'             => '</tr>',
            'row_alt_start'       => '<tr>',
            'row_alt_end'         => '</tr>'
		);
		$this -> table -> set_template($tmpl); 
		$this -> table -> set_heading('#', 'Country', 'Associate', 'Fee', 'Currency', 'Contact Name', 'Translation Required', 'Filing Type', 'GSA', '&nbsp;', '&nbsp;');
		$i = 1;
		foreach ($replaced_associates as $associate)
		{
            $filing = '';
            if($associate['30_months'] == '1'){
                $filing .= 'PCT 30 month<br/>';
            }
            if ($associate['31_months'] == '1'){
                $filing .= 'PCT 31 month<br/>';
            }
            if ($associate['ep_validation'] == '1'){
                $filing .= 'EP<br/>';
            }
            if($associate['is_direct_case_allowed'] == '1') {
                $filing .= 'Direct<br/>';
            }
//            $fa_data ='';
//            if( !empty($associate['name'])){
//                $fa_data = $associate['name'].'</br>';
//            }
//            if(!empty($associate['firm'])){
//                $fa_data .= $associate['firm'].'</br>';
//            }
//            if(!empty($associate['address'])){
//                $fa_data .= $associate['address'].'</br>';
//            }
//            if(!empty($associate['address2'])){
//                $fa_data .= $associate['address2'].'</br>';
//            }if(!empty($associate['phone'])){
//            $fa_data .= 'Tel:'.$associate['phone'].'</br>';
//        }
//            if(!empty($associate['fax'])){
//                $fa_data .= 'Fax:'.$associate['fax'].'</br>';
//            }
//            if(!empty($associate['email'])){
//                $fa_data .= 'Email:'.$associate['email'].'</br>';
//            }
//            if(!empty($associate['website'])){
//                $fa_data .= $associate['website'].'</br>';
//            }
//            $cell = array('data' => nl2br($fa_data), 'id' => 'blueleft');
            $cell = array('data' => nl2br($associate['associate']), 'id' => 'blueleft');
			$this -> table -> add_row($i,
									  $associate['country'],
                                      $cell,
									  $associate['fee'],
									  $associate['fee_currency'],
									  $associate['contact_name'], 
									  ($associate['translation_required'] == 1) ? 'Yes' : 'No',
									  $filing,
									  (empty($associate['path_to_gsa_agreement'])) ? '' : 'YES',
									  anchor('/associates/edit/'.$associate['id'], '<img src="'.base_url().'assets/images/i/edit.png" title="Edit" alt="Edit" />'),
									  '<a href="javascript:void(0);" onclick=\'if(confirm("Are you sure want to delete selected associate?")){ document.location.href="'.base_url().'associates/delete/'.$associate['id'].'"}\'><img src="'.base_url().'assets/images/i/delete.png" alt="Delete" title="Delete" /></a>');
			$i++;
		}
		echo $this -> table -> generate();
	}
?>
</div>