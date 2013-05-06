<div class="files_to_attach">
	<div class="attach_header">
		<div class="attach_file_attach">Attach</div>
		<div class="attach_file_name">File Name</div>
		<div class="attach_file_type">File Type</div>
		<div class="clear"></div>
	</div>
	<div class="files_rows">
<?php 
foreach($file_types as $file_type){
	$types[$file_type['id']] = $file_type['name'];
}
if(check_array($files)){
	foreach($files as $file){
?>		
		<div class="attach_row">
			<div class="attach_file_attach"><input type="checkbox" ref="<?=$file['filename']?>" name="attach_files[]" value="<?=$file['id'] ?>"></div>
			<div class="attach_file_name"><?=$file['filename'] ?></div>
			<div class="attach_file_type"><?=isset($types[$file['file_type_id']])?$types[$file['file_type_id']]:'undefined'?></div>
			<div class="clear"></div>
		</div>
<?php 
	}
}

?>
	</div>
	<div class="attach_actions">
		<a id="attach_files_activator" href="#"></a>
		<div class="clear"></div>
	</div>
</div>