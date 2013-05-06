<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>assets/css/jquery.autocomplete.css" />
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.validate.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
        $('#parent_sv').parent().parent("tr").css('display', 'none');
        $('#parent_bdv').parent().parent("tr").css('display', 'none');
        $('#parent_pm').parent().parent("tr").css('display', 'none');
        $('#type').click(function(){
            if($("option:selected",this).val() == 'pm'){
                $('#parent_fr').parent().parent("tr").css('display', 'none');
                $('#parent_bdv').parent().parent("tr").css('display', 'none');
                $('#parent_pm').parent().parent("tr").css('display', 'none');
                $('#parent_sv').parent().parent("tr").css('display', 'table-row');
            }else if($("option:selected",this).val() == 'customer'){
                $('#parent_bdv').parent().parent("tr").css('display', 'none');
                $('#parent_pm').parent().parent("tr").css('display', 'none');
                $('#parent_sv').parent().parent("tr").css('display', 'none');
                $('#parent_fr').parent().parent("tr").css('display', 'table-row');
            }else if($("option:selected",this).val() == 'firm'){
                $('#parent_sv').parent().parent("tr").css('display', 'none');
                $('#parent_fr').parent().parent("tr").css('display', 'none');
                $('#parent_bdv').parent().parent("tr").css('display', 'table-row');
                $('#parent_pm').parent().parent("tr").css('display', 'table-row');
            }
            else{
                $('#parent_sv').parent().parent("tr").css('display', 'none');
                $('#parent_fr').parent().parent("tr").css('display', 'none');
                $('#parent_bdv').parent().parent("tr").css('display', 'none');
                $('#parent_pm').parent().parent("tr").css('display', 'none');
            }
        });
		$("#new_user_form").validate({
			rules: {
				firstname: "required",
				lastname: "required",
				username: {
					required: true,
					minlength: 6
				},
				password: {
					required: true,
					minlength: 6
				},
				confirm_password: {
					required: true,
					minlength: 6,
					equalTo: "#password"
				},
				email: {
					required: true,
					email: true
				}
			},
			messages: {
				firstname: "Please enter your firstname",
				lastname: "Please enter your lastname",
				username: {
					required: "Please enter a username",
					minlength: "Your username must consist of at least 6 characters"
				},
				password: {
					required: "Please provide a password",
					minlength: "Your password must be at least 6 characters long"
				},
				confirm_password: {
					required: "Please provide a password",
					minlength: "Your password must be at least 6 characters long",
					equalTo: "Please enter the same password as above"
				},
				email: "Please enter a valid email address"
			}
		});
	});
</script>
<?php
	$tmpl = array (
            'table_open' => '<table border="0" cellpadding="4" cellspacing="0" class="data-table">',
    );
	$this -> table -> set_template($tmpl);
	echo validation_errors();
	echo form_open('/users/insert/', array('id' => 'new_user_form'));
	$this -> table -> add_row('Username', form_input('username', set_value('username'), 'id="username"'));
	$this -> table -> add_row('Password', form_password('password', set_value('password'), 'id="password"'));
	$this -> table -> add_row('Confirm Password', form_password('confirm_password', set_value('confirm_password'), 'id="confirm_password"'));
	$this -> table -> add_row('Firstname', form_input('firstname', set_value('firstname'), 'id="firstname"'));
	$this -> table -> add_row('Lastname', form_input('lastname', set_value('lastname'), 'id="lastname"'));
	$this -> table -> add_row('Email', form_input('email', set_value('email'), 'id="email"'));
	$this -> table -> add_row('Type', form_dropdown('type', array('customer' => 'Client', 'firm' => 'Firm', 'sales' => 'Sales', 'pm' => 'Project Manager', 'supervisor' => 'Supervisor'), 'customer', 'id="type"'));
    $this -> table -> add_row('Select Supervisor', form_dropdown('supervisor_id', $supervisors, '0', 'id="parent_sv"'));
    $this -> table -> add_row('Select Firm', form_dropdown('parent_firm_id', $firms, '0', 'id="parent_fr"'));
    $this -> table -> add_row('Select BDV', form_dropdown('parent_sales_id', $sales, '0', 'id="parent_bdv"'));
    $this -> table -> add_row('Select Manager', form_dropdown('parent_manager_id', $managers, '0', 'id="parent_pm"'));
	$this -> table -> add_row('', form_submit('submit', 'Create'));
	echo $this -> table -> generate();
	echo form_close();
?>