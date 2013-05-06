<?php 
if($form_errors = validation_errors()){
	echo('<div class="form_errors">'.validation_errors().'</div>');
}
?>
<form action="" method="post">
	<table border="0" cellpadding="4" cellspacing="0" class="data-table">
		<tr>
			<td>Case number:</td>
			<td>
				<input type="text" name="case_number" value="<?= set_value('case_number')?>"/>
			</td>
		</tr>
		<tr>
			<td>User ID:</td>
			<td>
				<input type="text" name="user_id" value="<?= set_value('user_id')?>"/>
			</td>
		</tr>
		<tr>
			<td colspan="2" align="center">
				<input type="submit" value="Create">
			</td>
		</tr>
	</table>
</form>