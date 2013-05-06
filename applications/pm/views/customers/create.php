<link href="<?php echo base_url('assets/css/jquery-ui/jquery-ui-1.8.16.custom.css')?>" media="screen" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url()?>assets/css/jquery.password.strength.css" media="screen" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/additional.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.validate.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.validate.additional_methods.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jquery.password.strength.js"></script>

<script src="https://closure-library.googlecode.com/svn/trunk/closure/goog/base.js"></script>
<script>
    goog.require('goog.dom');
    goog.require('goog.json');
    goog.require('goog.proto2.ObjectSerializer');
    goog.require('goog.string.StringBuffer');
</script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/phone-validation/phonemetadata.pb.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/phone-validation/phonenumber.pb.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/phone-validation/metadata.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/phone-validation/phonenumberutil.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/phone-validation/asyoutypeformatter.js"></script>
<script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=false"></script>

<script type="text/javascript">
	$(document).ready(function() {

        function phoneNumberParser() {
            //var $ = goog.dom.getElement;
            var phoneNumber = $('#phone_number').val();
            var regionCode = $("input[name='phone_country_code']").val();
            var carrierCode = $('#carrierCode').val() || "";
            var output = new goog.string.StringBuffer();
            var international_format = "";
            try {
                var phoneUtil = i18n.phonenumbers.PhoneNumberUtil.getInstance();
                var number = phoneUtil.parseAndKeepRawInput(phoneNumber, regionCode);
                var isPossible = phoneUtil.isPossibleNumber(number);

                var reason = "";
                if ( ! isPossible) {
                    var PNV = i18n.phonenumbers.PhoneNumberUtil.ValidationResult;
                    switch (phoneUtil.isPossibleNumberWithReason(number)) {
                        case PNV.INVALID_COUNTRY_CODE:
                            reason = 'INVALID_COUNTRY_CODE';
                            break;
                        case PNV.TOO_SHORT:
                            reason = 'TOO_SHORT';
                            break;
                        case PNV.TOO_LONG:
                            reason = 'TOO_LONG';
                            break;
                    }
                    // IS_POSSIBLE shouldn't happen, since we only call this if _not_
                    // possible.
                    reason = "An unknown region, and are considered invalid";
                } else {
                    var isNumberValid = phoneUtil.isValidNumber(number);
                    output.append(isNumberValid);
                    if (isNumberValid && regionCode && regionCode != 'ZZ') {
                        output.append(phoneUtil.isValidNumberForRegion(number, regionCode));
                    }
                    var region_code = phoneUtil.getRegionCodeForNumber(number);
                    var PNT = i18n.phonenumbers.PhoneNumberType;
                    switch (phoneUtil.getNumberType(number)) {
                        case PNT.FIXED_LINE:
                            //output.append('FIXED_LINE');
                            break;
                        case PNT.MOBILE:
                            // output.append('MOBILE');
                            break;
                        case PNT.FIXED_LINE_OR_MOBILE:
                            //output.append('FIXED_LINE_OR_MOBILE');
                            break;
                        case PNT.TOLL_FREE:
                            //output.append('TOLL_FREE');
                            break;
                        case PNT.PREMIUM_RATE:
                            //output.append('PREMIUM_RATE');
                            break;
                        case PNT.SHARED_COST:
                            //output.append('SHARED_COST');
                            break;
                        case PNT.VOIP:
                            //output.append('VOIP');
                            break;
                        case PNT.PERSONAL_NUMBER:
                            //output.append('PERSONAL_NUMBER');
                            break;
                        case PNT.PAGER:
                            //output.append('PAGER');
                            break;
                        case PNT.UAN:
                            //output.append('UAN');
                            break;
                        case PNT.UNKNOWN:
                            //output.append('UNKNOWN');
                            break;
                    }
                }
                var PNF = i18n.phonenumbers.PhoneNumberFormat;
                if (isNumberValid) {
                    international_format =  phoneUtil.format(number, PNF.INTERNATIONAL);

                    $("#phone_number").val(international_format);
                    return true;
                } else {

                    return false;
                }
            } catch (e) {
                // output.append('\n' + e);
            }
            //alert(output.toString());
            //$('output').value = output.toString();
            return true;
        }

        function hideStateDropdown() {
            var phone_country_code = $("input[name='phone_country_code']").val();
            if (phone_country_code != "US") {
                // Hide state dropdown for non-US countries
                $(".state-row").hide().find("select#state option:selected").removeAttr("selected");
            } else {
                $(".state-row").show();
            }
        }
        hideStateDropdown();

        address_processed = false;
        // Users would like to use the address they entered
        $(".own_address_box").live("click", function() {
            jQuery(document).trigger('close.facebox');
            address_processed = true;
            $("input[name='submit']").trigger("click");
        })
        $(".google_address_box").live("click", function() {
            jQuery(document).trigger('close.facebox');
            address_processed = true;
            // Populate fields using the data from google box
            var city = $(this).find("input[name='city']").val() || "";
            var country = $(this).find("input[name='country']").val()  || "";
            var zip_code = $(this).find("input[name='zip_code']").val() || "";
            var state = $(this).find("input[name='state']").val()  || "";
            var address = $(this).find("input[name='address']").val()  || "";

            var city = $(this).find("input[name='city']").val();
            if (city === undefined  || city == 'undefined') {
                city = "";
            }
            var country = $(this).find("input[name='country']").val();
            if (country === undefined  || country == 'undefined') {
                country = "";
            }
            var zip_code = String($(this).find("input[name='zip_code']").val());
            if (zip_code === undefined || zip_code == 'undefined') {
                zip_code = "";
            }
            var state = $(this).find("input[name='state']").val();
            if (state === undefined || state == 'undefined') {
                state = "";
            }
            var address = $(this).find("input[name='address']").val();
            if (address === undefined || address == 'undefined') {
                address = "";
            }

            $("input[name='submit']").trigger("click");
        })

        $("#country").autocomplete({
            source: "<?php echo site_url('clients/ajax_search_country/')?>",
            minLength: 2,
            select: function(event, ui) {
                $("input[name='phone_country_code']").val(ui.item.id);
                hideStateDropdown();
            }
        });

        $.validator.addMethod('advanced_phone_number', function(value) {
                return phoneNumberValidator();
            }, 'Please enter a valid phone number'
        );

		$(".validation_field").bind('click',function(){
			$('span.testresult').hide();
		});

        $.validator.addMethod('username_check', function(value) {
                return username_check(value);
            }, 'Username should not have spaces.'
        );

        function username_check(value) {
            var trimmed_value = $.trim(value);
            if(trimmed_value.indexOf(' ') === -1)
            {
                return true;
            }
            return false;
        }

		$("form#customer").validate({
			rules: {
				username: {
					"required": true,
					"minlength": 6 ,
                    "username_check": true
				},
				email: {
					"required": true,
					"email": true,
					"minlength": 3
				},
				password: {
					"required": true,
					"minlength": 7
				},
				bdv: "required",
				phone_number: {
                    "required": true,
                    "advanced_phone_number": true
                }
			}
		});
		
		$("#password").passStrength({
			userid:	"#username",
			messageloc: "1"
		});

        $('#type').click(function(){
            if($("option:selected",this).val() == 'customer'){
                $('#parent_firm').css('display', 'block');
            }else{
                $('#parent_firm').css('display', 'none');
            }
        });
		
		$("#generate_password").click(function() {
			var password = generate_password(8, true);
			$("#password").val(password);
			$("#password").change();
		});

        function checkPasswordStrength() {

            var myElement = $(".testresult");
            var classes = myElement.attr('class').split(/\s+/);
            var match = "foo";

            for(var i = 0; i < classes.length; i++){
                var className = classes[i];

                if (className == "goodPass" || className == "strongPass") {
                    return true;
                }
            }

            stanOverlay.setTITLE("Password strength");
            stanOverlay.setMESSAGE("Please create a stronger password");
            stanOverlay.SHOW();
            return false;

        }
        $("input[name='submit']").click(function() {
            // Check password strength
            if ( ! checkPasswordStrength()) {
                return false;
            }
            if (phoneNumberParser()) {
                if ($("form#customer").valid()) {
                    if (address_processed == false) {
                        validate_address();
                        return false;
                    }
                    else if (address_processed == true) {
                        address_processed = false;
                    }
                }
            }
        });
	});
</script>
<?php
	echo validation_errors();
	$tmpl = array (
            'table_open'          => '<table border="0" cellpadding="4" cellspacing="0" class="data-table">',
    );
	$this -> table -> set_template($tmpl); 
	echo form_open('/clients/insert/', array('id' => 'customer'));
    $this -> table -> add_row('Type', form_dropdown('type', array('customer' => 'Client', 'firm' => 'Firm'), set_value('type'), 'id="type"'));
    $this -> table -> add_row('Parent Firm', form_dropdown('parent_firm_id', $firms, set_value('type'), 'id="parent_firm"'));
    $this -> table -> add_row('Project Manager', form_dropdown('manager_id', $managers, '', 'id="parent_firm"'));
	$this -> table -> add_row('Username', form_input('username', set_value('username'), 'id="username" class="validation_field"'));
	$this -> table -> add_row('Password', form_input('password', set_value('password'), 'id="password" class="validation_field" autocomplete="off"').'&nbsp;<a id="generate_password" href="javascript:void(0);">Generate</a>');
	$bdv_dd = array('' => '');
	if (check_array($bdv))
	{
		foreach ($bdv as $item)
		{
			$bdv_dd[$item['id']] = $item['firstname'].' '.$item['lastname'];
		}
	}
	// A list of countries to validate phone number
	$phone_countries_dd = array();
	if (check_array($phone_countries))
	{
		foreach ($phone_countries as $phone_country)
		{
			$phone_countries_dd[$phone_country['code']] = $phone_country['country'];
		}
	}

    $us_states_dd = array('' => 'Select State');
    if (check_array($us_states))
    {
        foreach ($us_states as $us_state)
        {
            $us_states_dd[$us_state['state_code']] = $us_state['state'];
        }
    }

	$this -> table -> add_row('BDV', form_dropdown('bdv', $bdv_dd, set_value('bdv'), 'id="bdv"'));
	$this -> table -> add_row('First Name', form_input('firstname', set_value('firstname'), 'id="firstname"'));
	$this -> table -> add_row('Last Name', form_input('lastname', set_value('lastname'), 'id="lastname"'));
	$this -> table -> add_row('Company Name', form_input('company_name', set_value('company_name'), 'id="company_name"'));
	$this -> table -> add_row('Email', form_input('email', set_value('email'), 'id="email"'));
    $this -> table -> add_row('Ext', form_input('ext', set_value('ext'), 'id="ext"'));
    $this -> table -> add_row('Fax', form_input('fax', set_value('fax'), 'id="fax"'));
    $this -> table -> add_row('Address', form_input('address', set_value('address'), 'id="address"'));
    $this -> table -> add_row('Address2', form_input('address2', set_value('address2'), 'id="address2"'));
    $this -> table -> add_row('City', form_input('city', set_value('city'), 'id="city"'));
    echo form_hidden('phone_country_code', '');
    $this -> table -> add_row(
        array('data' => 'State', 'class' => 'state-row'),
        array('data' => form_dropdown('state', $us_states_dd, '', 'id="state"'), 'class' => 'state-row')
    );
    $this -> table -> add_row('Zip Code', form_input('zip_code', set_value('zip_code'), 'id="zip_code"'));
    $this -> table -> add_row('Country', form_input('country', set_value('country'), 'id="country"'));
    $this -> table -> add_row('Phone Number', form_input('phone_number', FALSE, 'id="phone_number"'));
    $this -> table -> add_row('Send automatic emails', form_dropdown('allow_email', array('yes'=>'yes','no'=>'no'), set_value('no')));
	$this -> table -> add_row('&nbsp;', form_submit('submit', 'Submit'));
    echo form_hidden('phone_country_code', '');
	echo $this -> table -> generate();
	echo form_close();
?>