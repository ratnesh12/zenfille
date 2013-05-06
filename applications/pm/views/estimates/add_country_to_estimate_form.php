<script type="text/javascript">
	$(document).ready(function() {
		$("#new_estimate_country_id").tokenInput(<?php echo json_encode($countries); ?>, {
			propertyToSearch: "country",
			preventDuplicates: true
		});
	});
</script>
<p>Add country to estimate</p>
<form name="add_country_to_estimate" method="post" action="<?php echo base_url()?>estimates/add_country_to_estimate/<?php echo $case_number?>">
	<input type="text" id="new_estimate_country_id" name="new_estimate_country_id" /><br/><br/>
	<input type="submit" value="add" name="submit"/>
</form>