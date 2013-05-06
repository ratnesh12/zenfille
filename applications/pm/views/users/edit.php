<link href="<?php echo base_url(); ?>assets/css/jquery.password.strength.css" media="screen" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.validate.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.password.strength.js"></script>
<script type="text/javascript">
	function generate_password(length, special) {
	    var iteration = 0;
	    var password = "";
	    var randomNumber;
	    if (special == undefined){
	        var special = false;
	    }
	    while(iteration < length){
	        randomNumber = (Math.floor((Math.random() * 100)) % 94) + 33;
	        if ( ! special){
	            if ((randomNumber >=33) && (randomNumber <=47)) { continue; }
	            if ((randomNumber >=58) && (randomNumber <=64)) { continue; }
	            if ((randomNumber >=91) && (randomNumber <=96)) { continue; }
	            if ((randomNumber >=123) && (randomNumber <=126)) { continue; }
	        }
	        iteration++;
	        password += String.fromCharCode(randomNumber);
	    }
	    return password;
	}
	$(document).ready(function() {
        <?php if($user['type'] == 'pm'){?>
        $('#parent_sv').parent().parent("tr").css('display', 'table-row');
        $('#parent_fr').parent().parent("tr").css('display', 'none');
        <?php }elseif($user['type'] == 'customer'){?>
        $('#parent_fr').parent().parent("tr").css('display', 'table-row');
        $('#parent_sv').parent().parent("tr").css('display', 'none');
        <?php }else{?>
        $('#parent_fr').parent().parent("tr").css('display', 'none');
        $('#parent_sv').parent().parent("tr").css('display', 'none');
        <?php } ?>

        $('#type').click(function(){
            if($("option:selected",this).val() == 'pm'){
                $('#parent_fr').parent().parent("tr").css('display', 'none');
                $('#parent_sv').parent().parent("tr").css('display', 'table-row');
            }else if($("option:selected",this).val() == 'customer'){
                $('#parent_sv').parent().parent("tr").css('display', 'none');
                $('#parent_fr').parent().parent("tr").css('display', 'table-row');
            }
            else{
                $('#parent_sv').parent().parent("tr").css('display', 'none');
                $('#parent_fr').parent().parent("tr").css('display', 'none');
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
					minlength: 6
				},
				confirm_password: {
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

		$("#password").passStrength({
			userid: "input[name=username]",
			messageloc: "1"
		});

		$("#generate_password").click(function() {
			var password = generate_password(8, true);
			$("#password").val(password);
			$("#confirm_password").val(password);
			$("#password").change();
		});
	});
</script>
<?php
	$message = $this -> session -> flashdata('message');
	if ( ! empty($message))
	{
		echo '<p class="message">'.$message.'</p>';
	}
?>
<?php
	$tmpl = array (
            'table_open' => '<table border="0" cellpadding="4" cellspacing="0" class="data-table">',
    );
	$this -> table -> set_template($tmpl);
	echo validation_errors();
	echo form_open('/users/update/'.$user['id'], array('id' => 'new_user_form'));
	echo form_hidden('username', $user['username']);
	$this -> table -> add_row('Username', $user['username']);
	$this -> table -> add_row('Password', form_input('password', FALSE, 'id="password" autocomplete="off"').'&nbsp;<a id="generate_password" href="javascript:void(0);">Generate</a>');
	$this -> table -> add_row('Confirm Password', form_input('confirm_password', set_value('confirm_password'), 'id="confirm_password" autocomplete="off"'));
	$this -> table -> add_row('Firstname', form_input('firstname', $user['firstname'], 'id="firstname"'));
	$this -> table -> add_row('Lastname', form_input('lastname', $user['lastname'], 'id="lastname"'));
	$this -> table -> add_row('Email', form_input('email', $user['email'], 'id="email"'));

if($user['type'] == 'firm' || $user['type'] =='customer'){
    $types = array(
        'customer' 	=> 'Client',
        'firm' => 'Firm'
    );
}else{
    $types = array(
        'sales' 	=> 'Sales',
        'pm' 		=> 'Project Manager',
        'supervisor' => 'Supervisor'
    );
}
$this -> table -> add_row('Type', form_dropdown('type', $types, $user['type'], 'id="type"'));
$this -> table -> add_row('Select Parent User', form_dropdown('supervisor_id', $supervisors, isset($user['supervisor_id']) ? $user['supervisor_id'] : '' , 'id="parent_sv"'));
$this -> table -> add_row('Select Parent User', form_dropdown('parent_firm_id',$firms, isset($user['parent_firm_id']) ? $user['parent_firm_id'] :'', 'id="parent_fr"'));

	if ($user['type'] == 'customer')
	{
		$this -> table -> add_row('Blocked', form_checkbox('blocked', '1', $user['blocked']));
		$this -> table -> add_row('Login attempts', $user['login_attempts']);
	}
	$this -> table -> add_row('Last login', $user['last_login']);
	$this -> table -> add_row('', form_submit('submit', 'Update'));
	echo $this -> table -> generate();
	echo form_close();
?>