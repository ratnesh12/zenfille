<?php
	$message = $this -> session -> flashdata('message');
	if ( ! empty($message))
	{
		echo '<p class="message">'.$message.'</p>';
	}
?>

<?php echo form_open('/admin/delete_user_cases/')?>
<p>Delete user's cases</p>
<?php echo form_label('SQL User ID: ', 'user_Id').form_input('user_id','','class="admin"')?>
<?php echo form_submit('submit', 'Delete Cases', "class='more_visible'")?>
<?php echo form_close()?>

<?php echo form_open('/admin/draft_case/')?>
<p>Delete a case</p>
<?php echo form_label('Start case number: ', 'start_case_number').form_input('start_case_number','','class="admin"')?>
<?php echo form_label('End case number: ', 'end_case_number').form_input('end_case_number','','class="admin"')?>
<?php echo form_submit('submit', 'Delete', "class='more_visible'")?>
<?php echo form_close()?>

<?php echo form_open('/admin/assign_client_to_case/')?>
<p>Reassign a case client</p>
<?php echo form_label('Case number: ', 'case_number').form_input('case_number','','class="admin"')?>
<?php echo form_label('SQL User ID: ', 'user_Id').form_input('user_id','','class="admin"')?>
<?php echo form_submit('submit', 'Reassign', "class='more_visible'")?>
<?php echo form_close()?>

<?php echo form_open('/admin/create_case/')?>
<p>Create new case</p>
<?php echo form_label('Case number: ', 'case_number').form_input('case_number','','class="admin"')?>
<?php echo form_label('SQL User ID: ', 'user_Id').form_input('user_id','','class="admin"')?>
<?php echo form_submit('submit', 'Create Case', "class='more_visible'")?>
<?php echo form_close()?>