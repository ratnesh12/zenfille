<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.validate.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$("form#create_rep_as").validate({
			rules: {
                email: {
                    required: true,
                    multiemails: true
            },
<!--                username: {-->
<!--                    required: true,-->
<!--                    remote: "--><?php //echo base_url(); ?><!--associates/ajax_username_check"-->
<!--                },-->
                name: "required",
                firm: "required",
                address: "required",
                phone: "required",
				/*fee: "required",*/
                contact_name: "required"
			},
            messages: {
//                username:{
//                    remote: "This username is already exist! Try another."
//                },
                email:{
                    multiemails: "Email is not valid or not separated by (, or ;)."
                }
            }
		});
	});
    jQuery.validator.addMethod(
        "multiemails",
        function(value, element) {
            if (this.optional(element)) // return true on optional element
                return true;
            var emails = value.split(/[;,]+/); // split element by , and ;
            valid = true;
            for (var i in emails) {
                value = emails[i];
                valid = valid &&
                    jQuery.validator.methods.email.call(this, $.trim(value), element);
            }
            return valid;
        },

        jQuery.validator.messages.multiemails
    );
</script>
<style type="text/css" >
#create_rep_as .error{
	color:#F00 !important;}
</style>
<?php
	echo form_open('/cases/create_replace_associate/'.$case_number,'id="create_rep_as"');
	echo form_hidden('country_id', $country_id);
    echo form_hidden('associcate_id', $associcate_id);
    echo form_hidden('is_replaced', $is_replaced);
    echo form_hidden('is_edit', $is_edit);
if($is_edit == '1'){
    $action = 'Edit';
}else{
    $action = 'Create';
}
$countries_dd = array();
if (check_array($countries))
{
    foreach ($countries as $country)
    {
        $countries_dd[$country['id']] = $country['country'];
    }
}
$associate_ta = array(
    'name'	=>	'associate',
    'id'	=>	'associate',
    'cols'	=>	'30',
    'rows'	=>	'10',
    'value' =>  empty($associate['associate']) ? '' : $associate['associate']
);
$this -> table -> add_row('Display', form_textarea($associate_ta));
$this -> table -> add_row('Email', form_input('email', isset($associate['email']) ? $associate['email'] : ''));
//$this -> table -> add_row('Username', form_input('username', isset($associate['username']) ? $associate['username'] : ''));
$this -> table -> add_row('Name', form_input('name', isset($associate['name']) ? $associate['name'] : ''));
$this -> table -> add_row('Firm', form_input('firm', isset($associate['firm']) ? $associate['firm'] : ''));
$this -> table -> add_row('Address', form_input('address', isset($associate['address']) ? $associate['address'] : '','id="address"'));
$this -> table -> add_row('Address2', form_input('address2', isset($associate['address2']) ? $associate['address2'] : '','id="address2"'));
$this -> table -> add_row('Phone', form_input('phone', isset($associate['phone']) ? $associate['phone'] : ''));
$this -> table -> add_row('Fax', form_input('fax', isset($associate['fax']) ? $associate['fax'] : ''));
$this -> table -> add_row('Website', form_input('website', isset($associate['website']) ? $associate['website'] : ''));
$this -> table -> add_row('Country Location', form_input('fa_country_id', isset($associate['fa_country_id']) ? $associate['fa_country_id'] : ''));
$this -> table -> add_row('City', form_input('city', empty($associate['city']) ? '' : $associate['city']));
$this -> table -> add_row('Zip Code', form_input('zip_code', empty($associate['zip_code']) ? '' : $associate['zip_code']));
$this -> table -> add_row('Professional Fee', form_input('fee',isset($associate['fee']) ? $associate['fee'] : ''));
	$currencies = array(
		'usd'	=>	'usd',
		'euro'	=>	'euro',
	);
	$this -> table -> add_row('Fee Currency', form_dropdown('fee_currency', $currencies, FALSE));
	$this -> table -> add_row('Contact Name', form_input('contact_name', isset($associate['contact_name']) ? $associate['contact_name'] : ''));
$this -> table -> add_row('Translation Required', form_checkbox('translation_required', 1,FALSE));
	$this -> table -> add_row('&nbsp;', form_submit('submit', $action));
	echo $this -> table -> generate();
	echo form_close();
?>