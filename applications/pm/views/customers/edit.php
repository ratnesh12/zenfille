<link href="<?php echo base_url('assets/css/jquery-ui/jquery-ui-1.8.16.custom.css')?>" media="screen" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url(); ?>assets/css/jquery.password.strength.css" media="screen" rel="stylesheet" type="text/css" />
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
        return true;
    }

	$(document).ready(function() {

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
            if (city === undefined  || city == 'undefined') {
                city = "";
            }
            var country = $(this).find("input[name='country']").val() || "";
            if (country === undefined  || country == 'undefined') {
                country = "";
            }
            var zip_code = $(this).find("input[name='zip_code']").val() || "" ;
            if (zip_code === undefined || zip_code == 'undefined') {
                zip_code = "";
            }
            var state = $(this).find("input[name='state']").val() || "";
            if (state === undefined || state == 'undefined') {
                state = "";
            }
            var address = $(this).find("input[name='address']").val() || "";
            if (address === undefined || address == 'undefined') {
                address = "";
            }

            $("input[name='address']").val(String(address));
            $("input[name='city']").val(String(city));
            $("input[name='zip_code']").val(String(zip_code));
            $("select[name='state']").val(String(state));
            $("input[name='country']").val(String(country));

            $("input[name='submit']").trigger("click");
        })

        function validate_address() {
            var address = $("#address").val() + " " + $("#address2").val() + " " + $("#city").val() + " " + $("#zip_code").val() + " " + $("#country").val();

            $.post("<?php echo site_url('clients/get_google_address_data')?>", {q: address}, function(result) {
                var content = "<div class='google_suggests_box_header'>Are you sure?</div><div class='google_suggests_box'><div>You've entered:</div>" +
                        "<div class='own_address_box'>"+address+"</div>";
                var google_results = "";
                var values = "";

                if (parseInt(result.length) > 1) {

                    content = content + "<div class='right_panel'>We found the following similar addresses.<br/>Please click on the one that best matches your original entry. If you believe you're seeing this message as a result of error, click on your original entry.</div><div class='clear'></div>";
                    $(result).each(function(index, item) {
                        values = "<input type='hidden' name='address' value='"+String(item.fnumber.long_name) + " " + String(item.street_name.long_name) + " " + String(item.street_number.long_name)+"' />";
                        values += "<input type='hidden' name='city' value='"+String(item.city.long_name)+"' />";
                        values += "<input type='hidden' name='country' value='"+String(item.country.long_name)+"' />";
                        values += "<input type='hidden' name='zip_code' value='"+String(item.postal_code.long_name)+"' />";
                        values += "<input type='hidden' name='state' value='"+String(item.region_short)+"' />";

                        google_results += "<div class='google_address_box'>"+
                        String(item.formatted_address)+
                        values+
                        "<div class='clear'></div></div>";

                    });
                    content = content +"<div class='google_address_results'>" + google_results + "</div><div class='clear'></div></div>";
                    jQuery.facebox(content);
                } else if (parseInt(result.length) <= 1) {
                    content = content + "<div class='right_panel'>We could not find your address in our world records database. Please check your entry above. If you believe you're seeing this message as a result of error, click on your original entry.</div>";
                    content = content +  google_results + "<div class='clear'></div></div>";
                    jQuery.facebox(content);
                } else {
                    address_processed = true;
                    $("button[name='submit']").trigger("click");
                }
            }, "json");
        }

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




		$("form#customer").validate({
			rules: {
				username: {
					required: true,
					minlength: 6
				},
				email: {
					"required": true,
					"email": true,
					"minlength": 3
				},
				password: {
					"minlength": 7
				},
				bdv: "required",
				phone_number: {
                    "required": true,
                    "advanced_phone_number": true
                }
			}
		});
        $('#type').click(function(){
            if($("option:selected",this).val() == 'customer'){
                $('#parent_firm').css('display', 'block');
            }else{
                $('#parent_firm').css('display', 'none');
            }
        });

		$("#password").passStrength({
			userid:	"#username",
			messageloc: "1"
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
            if ($("#password").val() != "") {
                if ( ! checkPasswordStrength()) {
                    return false;
                }
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

        <?php if($customer['type'] =='firm'){?>
        $('#parent_firm').css('display', 'none');
  <?php }?>
	});
</script>
<?php
	echo validation_errors();
	$tmpl = array (
            'table_open'          => '<table border="0" cellpadding="4" cellspacing="0" class="data-table" width="60%">',
    );
	$this -> table -> set_template($tmpl);
	echo form_open('/clients/update/'.$customer['id'], array('id' => 'customer'));
    $this -> table -> add_row('Display tooltips', form_dropdown('is_disable_tooltips', array('1'=>'No','0'=>'Yes'), $customer['is_disable_tooltips']));
	$this -> table -> add_row('Type', form_dropdown('type', array('customer' => 'Client', 'firm' => 'Firm', 'fa' => 'FA'), $customer['type'], 'id="type"'));
    $this -> table -> add_row('Parent Firm', form_dropdown('parent_firm_id', $firms, $customer['parent_firm_id'], 'id="parent_firm"'));
    $this -> table -> add_row('Project Manager', form_dropdown('manager_id', $managers, $customer['manager_id'], 'id="parent_firm"'));

    $bdv_dd = array('' => '');
	if (check_array($bdv))
	{
		foreach ($bdv as $item)
		{
			$bdv_dd[$item['id']] = $item['firstname'].' '.$item['lastname'];
		}
	}

    $this -> table -> add_row('BDV', form_dropdown('bdv', $bdv_dd, $customer['bdv_id'], 'id="bdv"'));
    $this -> table -> add_row('Username', form_input('username', $customer['username'], 'id="username"'));
	$this -> table -> add_row('Password', form_input('password', FALSE, 'id="password" autocomplete="off"').'&nbsp;<a id="generate_password" href="javascript:void(0);">Generate</a>');


	// A list of countries to validate phone number
	$phone_countries_dd = array();
	if (check_array($phone_countries))
	{
		foreach ($phone_countries as $phone_country)
		{
			$phone_countries_dd[$phone_country['code']] = $phone_country['country'];
		}
	}
	$this -> table -> add_row('First Name', form_input('firstname', $customer['firstname'], 'id="firstname"'));
	$this -> table -> add_row('Last Name', form_input('lastname', $customer['lastname'], 'id="lastname"'));
	$this -> table -> add_row('Company Name', form_input('company_name', $customer['company_name'], 'id="company_name"'));
	$this -> table -> add_row('Email', form_input('email', $customer['email'], 'id="email"'));

	$this -> table -> add_row('Address', form_input('address', $customer['address'], 'id="address"'));
	$this -> table -> add_row('Address2', form_input('address2', $customer['address2'], 'id="address2"'));
	$this -> table -> add_row('City', form_input('city', $customer['city'], 'id="city"'));
    $this -> table -> add_row('Zip Code', form_input('zip_code', $customer['zip_code'], 'id="zip_code"'));

    $us_states_dd = array('' => 'Select State');
    if (check_array($us_states))
    {
        foreach ($us_states as $us_state)
        {
            $us_states_dd[$us_state['state_code']] = $us_state['state'];
        }
    }


    echo form_hidden('phone_country_code', $customer['phone_country_code']);
    $this -> table -> add_row(
        array('data' => 'State', 'class' => 'state-row'),
        array('data' => form_dropdown('state', $us_states_dd, $customer['state'], 'id="state"'), 'class' => 'state-row')
    );

    $this -> table -> add_row('Country', form_input('country', $customer['country'], 'id="country"'));
    $this -> table -> add_row('Phone Number', form_input('phone_number', $customer['phone_number'], 'id="phone_number"'));
    $this -> table -> add_row('Extension', form_input('ext', $customer['ext'], 'id="ext"'));
    $this -> table -> add_row('Fax', form_input('fax', $customer['fax'], 'id="fax"'));
	$this -> table -> add_row('Blocked', form_checkbox('blocked', '1', $customer['blocked']));
	$this -> table -> add_row('Last login', $customer['last_login']);
    $this -> table -> add_row('Send automatic emails', form_dropdown('allow_email', array('yes'=>'yes','no'=>'no'), $customer['allow_email']));
	$this -> table -> add_row('&nbsp;', form_submit('submit', 'Update'));
	echo $this -> table -> generate();
	echo form_close();
?>
<h4>Client notes:</h4>
<?php
	$this -> table -> clear();
	if (check_array($client_notes))
	{
		$tmpl = array (
	                    'table_open'          => '<table border="0" cellpadding="5" cellspacing="0" width="100%" id="estimates-notes" class="data-table">',
	    );

		$this -> table -> set_template($tmpl);
		$this -> table -> set_heading('Note', 'Username', 'Date');
		foreach ($client_notes as $note)
		{
			$this -> table -> add_row(array('data' => nl2br($note['note'])),
								      array('data' => $note['username'], 'class' => 'username'),
									  array('data' => $note['created_at'], 'class' => 'created_at'));
		}
		echo $this -> table -> generate();
	}

?>