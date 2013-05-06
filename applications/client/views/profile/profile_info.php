<script src="https://closure-library.googlecode.com/svn/trunk/closure/goog/base.js"></script>
<script>
    goog.require('goog.dom');
    goog.require('goog.json');
    goog.require('goog.proto2.ObjectSerializer');
    goog.require('goog.string.StringBuffer');
</script>
<script src="<?php echo site_url('assets/js/phone-validation/phonemetadata.pb.js')?>"></script>
<script src="<?php echo site_url('assets/js/phone-validation/phonenumber.pb.js')?>"></script>
<script src="<?php echo site_url('assets/js/phone-validation/metadata.js')?>"></script>
<script src="<?php echo site_url('assets/js/phone-validation/phonenumberutil.js')?>"></script>
<script src="<?php echo site_url('assets/js/phone-validation/asyoutypeformatter.js')?>"></script>

<script>
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
        }

        return true;
    }
</script>



<script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=false"></script>
<script type="text/javascript">
	$(document).ready(function() {

        function hideStateDropdown() {
            var phone_country_code = $("input[name='phone_country_code']").val();
            if (phone_country_code != "US") {
                // Hide state dropdown for non-US countries
                $("#state-box").hide().find("select#state option:selected").removeAttr("selected");
            } else {
                $("#state-box").show();
            }
        }
        hideStateDropdown();

        address_processed = false;
        // Users would like to use the address they entered
        $(".own_address_box").live("click", function() {
            jQuery(document).trigger('close.facebox');
            address_processed = true;
            $("button[name='submit']").trigger("click");
        })
        $(".google_address_box").live("click", function() {
            jQuery(document).trigger('close.facebox');
            address_processed = true;
            // Populate fields using the data from google box
            var city = $(this).find("input[name='city']").val();
            var country = $(this).find("input[name='country']").val();
            var zip_code = String($(this).find("input[name='zip_code']").val());
            var state = $(this).find("input[name='state']").val();
            var address = $(this).find("input[name='address']").val();

            $("#profile-form").find("input[name='address']").val(address);
            $("#profile-form").find("input[name='city']").val(city);
            $("#profile-form").find("input[name='zip_code']").val(zip_code);
            $("#profile-form").find("select[name='state']").val(state);
            $("#profile-form").find("input[name='country']").val(country);

            $("button[name='submit']").trigger("click");
        })

        function validate_address() {
            var address = $("#address").val() + " " + $("#address2").val() + " " + $("#city").val() + " " + $("#zip_code").val() + " " + $("#country").val();
            $.post("<?php echo site_url('profile/get_google_address_data')?>", {q: address}, function(result) {
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

		$("button[name='submit']").click(function() {
            if (phoneNumberParser()) {
                if ($("#profile-form").valid()) {
                    if (address_processed == false) {
                        validate_address();
                    }
                    else if (address_processed == true) {
                        $.post("<?php echo base_url()?>profile/update/", $("#profile-form").serialize(), function() {
                            stanOverlay.setTITLE("Information!");
                            stanOverlay.setMESSAGE("Your information has been updated.");
                            stanOverlay.SHOW();

                            address_processed = false;
                        });
                    }
                }
            } else {
                stanOverlay.setTITLE("Information!");
                stanOverlay.setMESSAGE("Please provide a valid phone number.");
                stanOverlay.SHOW();
            }

			return false;
		});

		$("form#profile-form").validate({
			rules: {
				email: {
					"required": true,
					"email": true,
					"minlength": 3
				},
				firstname: "required",
				lastname: "required",
				phone_number: "required"
			}
		});

		$("#search_string").keyup(function() {
			var search_string = $(this).val();
			if (search_string.length >= 10) {
				$.post("<?php echo base_url();?>profile/get_google_data/", {search_string: search_string}, function(data) {
					$("#city").val(data.city);
					$("#state").val(data.city);
					$("#zip_code").val(data.city);
					$("#country").val(data.country);
					$("#address2").val(data.address);
				}, "json");
			}
		});

		$("#save_vcard").click(function() {
			document.location.href = "<?php echo base_url();?>profile/show_vcard";
		});

        var geocoder, map, marker;
        var defaultLatLng = new google.maps.LatLng(30,0);

        function initialize() {
            geocoder = new google.maps.Geocoder();
            var mapOptions = {
                zoom: 0,
                center: defaultLatLng,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            }
            marker = new google.maps.Marker();
        }

        function validate() {
            var valid = false;
            var address_type = '';
            var formatted_address = '';
            var initial_address = '';
            clearResults();

            geocoder.geocode({'address': address }, function(results, status) {
                switch(status) {
                    case google.maps.GeocoderStatus.OK:
                        valid = true;
                        address_type = results[0].types[0];
                        formatted_address = results[0].formatted_address;
                        console.log(address_type);
                        console.log(formatted_address);
                        console.log(results[0]);
                        //mapAddress(results[0]);
                        break;
                    case google.maps.GeocoderStatus.ZERO_RESULTS:
                        valid = false;
                        break;
                    default:
                        alert("An error occured while validating this address")
                }
            });

            return valid;
        }

        function clearResults() {
        }

        function mapAddress(result) {
            marker.setPosition(result.geometry.location);
            marker.setMap(map);
            map.fitBounds(result.geometry.viewport);
        }

        initialize();

        $("#country").autocomplete({
            source: "<?php echo site_url('profile/ajax_search_country/')?>",
            minLength: 2,
            select: function(event, ui) {
                $("input[name='phone_country_code']").val(ui.item.id);
                hideStateDropdown();
            }
        });
	});
</script>

<div class="inner_content">
	<h3>Please keep the information below current.</h3>
	<div class="clear"></div>
	<div class="">
		<form action="" name="profile-form" id="profile-form">

			<div class="p">
				<div class="label">First Name</div>
				<div class="inputs">
					<input type="text" id="first_name" name="first_name" value="<?php echo $profile['firstname']?>" placeholder="Your Name" />
				</div>
				<div class="clear"></div>
			</div>

			<div class="p">
				<div class="label">Last Name</div>
				<div class="inputs">
					<input type="text" id="last_name" name="last_name" value="<?php echo $profile['lastname']?>" placeholder="" />
				</div>
				<div class="clear"></div>
			</div>

			<div class="p">
				<div class="label">Company Name</div>
				<div class="inputs">
					<input type="text" id="company_name" name="company_name" value="<?php echo $profile['company_name']?>" placeholder="" />
				</div>
				<div class="clear"></div>
			</div>

			<div class="p">
				<div class="label">Address</div>
				<div class="inputs">
					<input type="text" id="address" name="address" value="<?php echo $profile['address']?>" placeholder="" />
				</div>
				<div class="clear"></div>
			</div>

			<div class="p">
				<div class="label">Address 2</div>
				<div class="inputs">
					<input type="text" id="address2" name="address2" value="<?php echo $profile['address2']?>" placeholder="" />
				</div>
				<div class="clear"></div>
			</div>

			<div class="p">
				<div class="label">City</div>
				<div class="inputs">
					<input type="text" id="city" name="city" value="<?php echo $profile['city']?>" placeholder="" />
				</div>
				<div class="clear"></div>
			</div>

			<div class="p">
				<div class="label">Zip Code</div>
				<div class="inputs">
					<input type="text" id="zip_code" name="zip_code" value="<?php echo $profile['zip_code']?>" placeholder="" />
				</div>
				<div class="clear"></div>
			</div>

			<div class="p">
                <div class="label">Country</div>
				<div class="inputs">
					<input type="text" id="country" name="country" value="<?php echo $profile['country']?>" placeholder="" />
                    <input type="hidden" name="phone_country_code" value="<?php echo $profile['phone_country_code']?>" />
				</div>
				<div class="clear"></div>
			</div>

			<div class="p" id="state-box">
				<div class="label">State</div>
				<div class="inputs">
					<?php
						$us_states_dd = array('' => 'Select State');
						if (check_array($us_states))
						{
							foreach ($us_states as $us_state)
							{
								$us_states_dd[$us_state['state_code']] = $us_state['state'];
							}
						}
						echo form_dropdown('state', $us_states_dd, $profile['state'], 'id="state"');
					?>

				</div>
				<div class="clear"></div>
			</div>

			<div class="p">
				<div class="label">Phone Number</div>
				<div class="inputs">
					<input type="text" name="phone_number" id="phone_number" value="<?php echo $profile['phone_number']?>" placeholder="" />
				</div>
				<div class="clear"></div>
			</div>

			<div class="p">
				<div class="label">Extension</div>
				<div class="inputs">
					<input type="text" name="ext" value="<?php echo $profile['ext']?>" placeholder="" />
				</div>
				<div class="clear"></div>
			</div>
            <div class="p">
                <div class="label">Fax</div>
                <div class="inputs">
                    <input type="text" name="fax" value="<?php echo $profile['fax']?>" placeholder="" />
                </div>
                <div class="clear"></div>
            </div>

			<div class="p">
				<div class="label">Email</div>
				<div class="inputs">
					<input type="text" id="email" name="email" value="<?php echo $profile['email']?>" placeholder="" />
				</div>
				<div class="clear"></div>
			</div>

			<div class="panel_buttons">
				<button name="submit" type="submit" class="button submit">Submit</button>
			</div>

		</form>
	</div>

	<div class="card_container">
		<?php if (isset($bdv) && ( ! is_null($bdv))):?>
			<h6>Your <?php echo $this->config->item('title_of_the_site') ?> Rep:</h6>
			<div class="card">
				<div class="logo small"></div>
				<div class="clear"></div>
				<div class="name"><?php echo $bdv['firstname']?>&nbsp;<?php echo $bdv['lastname']?></div>
				<div class="phone"><?php echo $bdv['phone']?></div>
                <div class="phone"><?php echo $bdv['extra_phone']?></div>
				<div class="email"><?php echo $bdv['email']?></div>
				<div class="address"><?php echo $bdv['address']?></div>
			</div>
			<button id="save_vcard" type="submit" class="button download">Save Contact to Outlook</button>
		<?php endif?>
		<div class="clear"></div>
	</div>
	<div class="clear"></div>
</div>