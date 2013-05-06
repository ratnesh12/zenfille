<?php 
$file_types_dd = array();
if (check_array($file_types))
{
    foreach ($file_types as $file_type)
    {
        $file_types_dd[$file_type['id']] = $file_type['name'];
    }
}
?>
<div class="file_controllers">
	<a href="#" class="assign_type" id="control_assign_type"></a>
	<a href="#" class="assign_country" id="control_assign_country"></a>
	<div class="clear"></div>
</div>

<div class="case-files-header-light tb_switch" ref="files_table">
	Case Files
</div>
<table border="0" cellpadding="4" cellspacing="0" id="files_table" class="data-table case_files">
<thead>
	<tr>
		<th class="cf_filename">File Name</th>
		<th class="cf_visibility">Visible to client</th>
		<th class="cf_filetype">File Type</th>
		<th class="cf_countries">Countries</th>
		<th class="cf_delete">Delete</th>
	</tr>
</thead>
<tbody>
	<?php
	if (check_array($files)){
		foreach ($files as $k => $file){
			$ft = $this->cases->get_file_types();
			if (!in_array($file['file_type_id'], $ft[1]) && strlen($file['countries']) > 0){
				continue;
			}
			$row_class = $k & 1 ? 'odd' : 'even';
			$assign_to_countries_link = 'Assign to countries';
			if (check_array($files_countries_output)){
				if (isset($files_countries_output[$file['id']])){
					$assign_to_countries_link = implode(', ', $files_countries_output[$file['id']]);
				}
			}
			$file['file_type_id'] = ($file['file_type_id'] == '0') ? '' : $file['file_type_id'];
			$fdata['extension'] = '';
			if (!empty($file['filename'])){
				$fdata = pathinfo($file['filename']);
			}
	?>
	<tr class="<?= $row_class ?>" id="file_row_<?= $file['id'] ?>">
		<td class="file_title">
			<div class="file_control">
				<input type="checkbox" name="case_files[]" value="<?= $file['id'] ?>">
			</div>
			<div class="dl_icon"><a href='<?= base_url() ?>cases/view_file/<?= $file['id'] ?>'></a></div>
			<div class="fname">
				<span class="filename" id="<?= $file['id'] ?>">
					<?= $file['filename'] ?>
				</span>
				<?= form_input('filename', $fdata['filename'], 'class="filename_input" id="inp' . $file['id'] . '"style="display: none;"'); ?>
				<?= form_hidden('ext' . $file['id'], $fdata['extension']) ?>
				<a href="javascript:void(0);" class="rename_link_ok" id="rename_<?= $file['id'] ?>" style="display: none;" >
					OK
				</a>
				&nbsp;
				<a href="javascript:void(0);" class="rename_link_cancel" id="cancel_<?= $file['id'] ?>" style="display: none;">
				Cancel
				</a>
			</div>
			<div class="clear"></div>
		</td>
		<td>
            <?php $disable = ''; if( $file['file_type_id'] == '7'){$disable = 'disabled="disabled"';} ?>
			<?= form_checkbox('visibility', '1', $file['visibility'], 'class="file_visibility" id="' . $file['id'] . '", '.$disable.'') ?>
		</td>
		<td class="cf_filetype">
			<?=
			form_dropdown(
			'file_type', $file_types_dd, $file['file_type_id'], 'class="file_type" id="ft' . $file['id'] . '" autocomplete="off"'
			)
			?>
		</td>
		<td>
			<a href="<?= base_url() ?>cases/assign_file_to_countries_form/<?= $file['id'] ?>/<?= $case['case_number'] ?>" class="popup">
			<?= $assign_to_countries_link ?>
			</a>
		</td>
		<td>
			<a
				title="Remove"
				href="javascript:void(0);"
				id="delete_link_<?= $file['id'] ?>"
				onclick="if(confirm('Do you really want to delete selected file?')){ remove_file(<?= $file['id'] ?>);}"
			>
				<img src="<?= base_url() ?>assets/images/i/delete.png" alt="Remove"/>
			</a>
		</td>
	</tr>
	<?php
		}
	}
	?>
</tbody>
</table>
<script id="templateUploadedFile" type="text/x-jquery-tmpl">
<tr  class="file file-${id} ${ext}">
<td class="file_title">
${fileName}
</td>
<td>-||-</td>
<td  class="cf_filetype">-||-</td>
<td>
<div class="file-progress-${id}"></div>
<div class="file-size">${total}</div>
</td>
<td>-||-</td>
</tr>
</script>
<div class="case-files-header-light tb_switch" ref="documents">
	Documents
</div>
<table class="data-table case_files"  id="documents">
	<thead>
		<tr>
			<th class="cf_filename">File Name</th>
			<th class="cf_visibility">Visible to client</th>
			<th class="cf_visibility">Visible to FA</th>
			<th class="cf_filetype">File Type</th>
			<th class="cf_countries">Countries</th>
			<th class="cf_delete">View more</th>
			<!--th class="cf_delete">Delete</th-->
		</tr>
	</thead>
	<?php
	if ($document_files){
	?>
	<tbody>
		<?php
			foreach ($document_files as $k => $file){
				$row_class = $k & 1 ? 'odd' : 'even';
				$assign_to_countries_link = 'Assign to countries';
				if (check_array($files_countries_output)){
					if (isset($files_countries_output[$file['id']])){
						$assign_to_countries_link = implode(', ', $files_countries_output[$file['id']]);
					}
				}
				$file['file_type_id'] = ($file['file_type_id'] == '0') ? '' : $file['file_type_id'];
				$fdata['extension'] = '';
				if (!empty($file['filename'])){
					$fdata = pathinfo($file['filename']);
				}
		?>
		<tr class="<?= $row_class ?>" id="file_row_<?= $file['id'] ?>">
			<td class="file_title">
				<div class="file_control">
					<input type="checkbox" name="case_files[]" value="<?= $file['id'] ?>">
				</div>
				<div class="dl_icon"><a href='<?= base_url() ?>cases/view_file/<?= $file['id'] ?>'></a></div>
				<div class="fname">
					<span class="filename" id="<?= $file['id'] ?>">
						<?= $file['filename'] ?>
					</span>
					<?= form_input('filename', $fdata['filename'], 'class="filename_input" id="inp' . $file['id'] . '"style="display: none;"'); ?>
					<?= form_hidden('ext' . $file['id'], $fdata['extension']) ?>
					<a href="javascript:void(0);" class="rename_link_ok" id="rename_<?= $file['id'] ?>" style="display: none;" >
						OK
					</a>
					&nbsp;
					<a href="javascript:void(0);" class="rename_link_cancel" id="cancel_<?= $file['id'] ?>" style="display: none;">
						Cancel
					</a>
				</div>
				<div class="clear"></div>
			</td>
			<td>
				<?= form_checkbox('visibility', '1', $file['visibility'], 'class="file_visibility" id="' . $file['id'] . '"') ?>
			</td>
			<td>
				<?= form_checkbox('visibility', '1', $file['visible_to_fa'], 'class="file_fa_visibility" id="' . $file['id'] . '"') ?>
			</td>
			<td class="cf_filetype">
				<?=
				form_dropdown(
				'file_type', $file_types_dd, $file['file_type_id'], 'class="file_type" id="ft' . $file['id'] . '" autocomplete="off"'
				)
				?>
			</td>
			<td>
				<a href="<?= base_url() ?>cases/assign_file_to_countries_form/<?= $file['id'] ?>/<?= $case['case_number'] ?>" class="popup">
					<?= $assign_to_countries_link ?>
				</a>
			</td>
			<!--td>
				<a
					title="View more"
					href="javascript:void(0);"
					onclick="file_view_more('<?= $file['id'] ?>');"
				>
					<img src="<?= base_url() ?>assets/images/i/eye-14-14.png" alt="View more"/>
				</a>
			</td-->
			<td>
				<a
					title="Remove"
					href="javascript:void(0);"
					id="delete_link_<?= $file['id'] ?>"
					onclick="if(confirm('Do you really want to delete selected file?')){ remove_file(<?= $file['id'] ?>);}"
				>
					<img src="<?= base_url() ?>assets/images/i/delete.png" alt="Remove"/>
				</a>
			</td>
		</tr>
	<?php } ?>
	</tbody>
	<?php } ?>
</table>
<div class="case-files-header-light tb_switch" ref="signed_documents">
	Signed Documents
</div>
<table class="data-table case_files" id="signed_documents">
	<thead>
		<tr>
			<th class="cf_filename">File Name</th>
			<th class="cf_visibility">Visible to client</th>
			<th class="cf_filetype">File Type</th>
			<th class="cf_countries">Countries</th>
			<th class="cf_delete">Delete</th>
		</tr>
	</thead>
	<?php
	if ($signed_document_files){
	?>
	<tbody>
		<?php
		foreach ($signed_document_files as $k => $file){
			$row_class = $k & 1 ? 'odd' : 'even';
			$assign_to_countries_link = 'Assign to countries';
			if (check_array($files_countries_output)){
				if (isset($files_countries_output[$file['id']])){
					$assign_to_countries_link = implode(', ', $files_countries_output[$file['id']]);
				}
			}
			$file['file_type_id'] = ($file['file_type_id'] == '0') ? '' : $file['file_type_id'];
			$fdata['extension'] = '';
			if (!empty($file['filename'])){
				$fdata = pathinfo($file['filename']);
			}
		?>
		<tr id="file_row_<?= $file['id'] ?>" class="<?= $row_class ?>">
			<td class="file_title">
				<div class="file_control">
					<input type="checkbox" name="case_files[]" value="<?= $file['id'] ?>">
				</div>
				<div class="dl_icon"><a href='<?= base_url() ?>cases/view_file/<?= $file['id'] ?>'></a></div>
				<div class="fname">
					<span class="filename" id="<?= $file['id'] ?>">
						<?= $file['filename'] ?>
					</span>
					<?= form_input('filename', $fdata['filename'], 'class="filename_input" id="inp' . $file['id'] . '"style="display: none;"'); ?>
					<?= form_hidden('ext' . $file['id'], $fdata['extension']) ?>
					<a href="javascript:void(0);" class="rename_link_ok" id="rename_<?= $file['id'] ?>" style="display: none;" >
						OK
					</a>
					&nbsp;
					<a href="javascript:void(0);" class="rename_link_cancel" id="cancel_<?= $file['id'] ?>" style="display: none;">
						Cancel
					</a>
				</div>
				<div class="clear"></div>
			</td>
			<td>
				<?= form_checkbox('visibility', '1', $file['visibility'], 'class="file_visibility" id="' . $file['id'] . '"') ?>
			</td>
			<td class="cf_filetype">
				<?=
				form_dropdown(
				'file_type', $file_types_dd, $file['file_type_id'], 'class="file_type" id="ft' . $file['id'] . '" autocomplete="off"'
				)
				?>
			</td>
			<td>
				<a href="<?= base_url() ?>cases/assign_file_to_countries_form/<?= $file['id'] ?>/<?= $case['case_number'] ?>" class="popup">
					<?= $assign_to_countries_link ?>
				</a>
			</td>
			<td>
				<a
					title="Remove"
					href="javascript:void(0);"
					id="delete_link_<?= $file['id'] ?>"
					onclick="if(confirm('Do you really want to delete selected file?')){ remove_file(<?= $file['id'] ?>);}"
				>
					<img src="<?= base_url() ?>assets/images/i/delete.png" alt="Remove"/>
				</a>
			</td>
		</tr>
		<?php } ?>
	</tbody>
	<?php } ?>
</table>

<div class="case-files-header-light" >
	<span class="tb_switch tb_header" ref="filling_confirmation_tbl">Filing Confirmation</span>
	<span class="file_download">
		<a href="/pm/cases/create_zip/<? echo $case['case_number']; ?> ">Download all confirmation reports</a>
	</span>
</div>
<table id="filling_confirmation_tbl" class="table countries">
	<tbody >
		<?php
		$approved_countries = array();
		if (check_array($list_estimate_countries)){
			foreach ($list_estimate_countries as $approved_country){
				if ($approved_country['is_approved'] == '1' || $approved_country['pm_approved_after_client'] == '1'){
					$approved_countries[] = $approved_country['country_id'];
				}
			}
		}
		$sent_emails = $this->cases->get_sent_emails($case['id'],$approved_countries);
		$missed = 0;
		if (check_array($filing_countries)):
			foreach ($filing_countries as $k => $country):
				if(!in_array($country['id'],$approved_countries)){
					$missed++;
					continue;
				}
				$row_class = (($k+$missed) & 1) ? 'odd' : 'even';
				if($country['files']){
					$switch_class = "tb_switch";
					$ref = "ref='con_files_" . $country['id'] . "'";
				}else{
					$switch_class = $ref = "";
				}
		?>
		<tr id="country_file_row_<?= $country['id'] ?>" class="<?php echo $row_class ?>">
			<td class="flag">
				<img src = "/client/<?= $country['flag_image'] ?>">
			</td>
			<td class="con_name <?= $switch_class ?>" <?= $ref ?>><? echo $country['country']; ?></td>
			
			<td class="<?php echo($country['files'] ? 'file_download' : 'file_download_not_ready') ?>">
				<a href="/pm/cases/create_zip/<? echo $case['case_number']; ?>/<? echo $country['id']; ?> ">&nbsp;</a>
			</td>
			<td class="<?=in_array($country['id'],$sent_emails)?'fc_send_email_done':'fc_send_email'?>">
				<a  href="#" onclick="window.open('/pm/cases/fc_send_email/<?=$case['case_number'];?>/<?=$country['id'];?>','_blank','width=700,height=500'); return false;">&nbsp;</a>
			</td>
		</tr>
		<?php if ($country['files']): ?>
		<tr class="attachments">
		<td id="con_files_<? echo $country['id']; ?>" colspan="4">
			<table class="table files country_files">
				<tbody>
					<?php
					foreach ($country['files'] as $f_index => $file){
						$file_row_class = $f_index & 1 ? 'odd' : 'even';
						$ext = strtolower(substr($file['filename'], strrpos($file['filename'], '.') + 1));
						if (file_exists(FCPATH . 'assets/images/file_types/type_' . $ext . '.png')){
							$type_class = 'type_' . $ext;
						}else{
							$type_class = 'type_def';
						}
					?>
					<tr class="<?= $file_row_class ?>">
						<td class="p90 <?= $type_class ?>">
							<a href="<?= base_url() ?>cases/view_file/<?php echo $file['id']; ?>"><?php echo $file['filename'] ?></a>
						</td>
						<td class="" >
					<?=
							form_dropdown(
							'file_type', $file_types_dd, $file['file_type_id'], 'class="file_type no_move" id="ft' . $file['id'] . '" autocomplete="off"'
							);
							?>
						</td>
						<td class="file_download">
							<a href="<?= base_url() ?>cases/view_file/<?php echo $file['id']; ?>">
							<?php echo format_bytes($file['filesize']); ?>
							</a>
						</td>
					</tr>
					<?php } ?>
				</tbody>
			</table>
		</td>
		</tr>
		<?php endif ?>
		<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>

