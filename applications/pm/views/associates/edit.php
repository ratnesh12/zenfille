<script src="https://closure-library.googlecode.com/svn/trunk/closure/goog/base.js"></script>
<script>
  goog.require('goog.dom');
  goog.require('goog.json');
  goog.require('goog.proto2.ObjectSerializer');
  goog.require('goog.string.StringBuffer');
</script>
<script type="text/javascript">
	$(document).ready(function(){
		address_processed = false;
		$(".own_address_box").live("click", function() {
            jQuery(document).trigger('close.facebox');
            address_processed = true;
            $("input[name='submit']").trigger("click");
        })
        $(".google_address_box").live("click", function() {
            jQuery(document).trigger('close.facebox');
            address_processed = true;
            // Populate fields using the data from google box
            var address = $(this).find("input#address").val() || "";
            if (address === undefined || address == 'undefined') {
                address = "";
            }

            $("input#address").val(String(address));

            $("input[name='submit']").trigger("click");
        })

		function validate_address() {
            var address = $("#address").val() + " " + $("#address2").val();
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
		$("input[name='submit']").click(function() {
//			if (address_processed == false) {
//                validate_address();
//                return false;
//            }
//            else if (address_processed == true) {
//                address_processed = false;
//            }
		});
	});
</script>
<?php
    if (isset($message))
        echo '<p style="color:red;">'.$message.'</p>';
	echo validation_errors();
	$action = isset($associate['id'])?'/associates/edit/'.$associate['id']:'/associates/edit/';
	echo form_open_multipart($action, array('id' => 'associate'));
	$countries_dd = array();
	if (check_array($countries))
	{
		foreach ($countries as $country)
		{
			$countries_dd[$country['id']] = $country['country'];
		}
	}
	$tmpl = array (
	        'table_open'          => '<table border="0" cellpadding="4" cellspacing="0" class="data-table">',
	);
	$this -> table -> set_template($tmpl); 
	$this -> table -> add_row('Filing Region', form_dropdown('country_id', $countries_dd, $associate['country_id']));
	$associate_ta = array(
		'name'	=>	'associate',
		'id'	=>	'associate',
		'cols'	=>	'80',
		'rows'	=>	'10',
		'value' =>  empty($associate['associate']) ? '' : $associate['associate']
	);
	$this -> table -> add_row('Display', form_textarea($associate_ta));
	$this -> table -> add_row('Email', form_input('email', empty($associate['email']) ? '' : $associate['email']));
    $this -> table -> add_row('Username', form_input('username', empty($associate['username']) ? '' : $associate['username']));
    $this -> table -> add_row('Name', form_input('name', empty($associate['name']) ? '' : $associate['name']));
    $this -> table -> add_row('Firm', form_input('firm', empty($associate['firm']) ? '' : $associate['firm']));
    $this -> table -> add_row('Address', form_input('address', empty($associate['address']) ? '' : $associate['address'],'id="address"'));
    $this -> table -> add_row('Address2', form_input('address2', empty($associate['address2']) ? '' : $associate['address2'],'id="address2"'));
    $this -> table -> add_row('Phone', form_input('phone', empty($associate['phone']) ? '' : $associate['phone']));
    $this -> table -> add_row('Fax', form_input('fax', empty($associate['fax']) ? '' : $associate['fax']));
    $this -> table -> add_row('Country Location', form_input('fa_country_id', $associate['fa_country_id']));
    $this -> table -> add_row('City', form_input('city', empty($associate['city']) ? '' : $associate['city']));
    $this -> table -> add_row('Zip Code', form_input('zip_code', empty($associate['zip_code']) ? '' : $associate['zip_code']));
    $this -> table -> add_row('Website', form_input('website', empty($associate['website']) ? '' : $associate['website']));
	$this -> table -> add_row('Professional Fee', form_input('fee', empty($associate['fee']) ? '' : $associate['fee']));
	$currencies = array(
		'usd'	=>	'usd',
		'euro'	=>	'euro',
	);
	$this -> table -> add_row('Fee Currency', form_dropdown('fee_currency', $currencies, empty($associate['fee_currency']) ? '' : $associate['fee_currency']));
	$this -> table -> add_row('Contact Name', form_input('contact_name', empty($associate['contact_name']) ? '' : $email = $associate['contact_name']));
	$this -> table -> add_row('Translation Required', form_checkbox('translation_required', 1, empty($associate['translation_required']) ? '' : $associate['translation_required']));
	$_30_months_checked 	= FALSE;
	$_31_months_checked 	= FALSE;
	$ep_validation_checked 	= FALSE;
    $is_direct_case_allowed = FALSE;
	if (!empty($associate['30_months']) && $associate['30_months'] == '1')
	{
		$_30_months_checked = TRUE;
	}
	if (!empty($associate['31_months']) && $associate['31_months'] == '1')
	{
		$_31_months_checked = TRUE;
	}
	if (!empty($associate['ep_validation']) && $associate['ep_validation'] == '1')
	{
		$ep_validation_checked = TRUE;
	}

    if (!empty($associate['is_direct_case_allowed']) && $associate['is_direct_case_allowed'] == '1')
    {
        $is_direct_case_allowed = TRUE;
    }

	$this -> table -> add_row('Type', form_checkbox('30_months', '1', $_30_months_checked, 'id=30month').'30 Months<br/>'.form_checkbox('31_months', '1', $_31_months_checked, 'id=31month').'31 Months<br/>'.form_checkbox('ep_validation', '1', $ep_validation_checked).'EP Validations<br/>' .form_checkbox('is_direct_case_allowed', '1', $is_direct_case_allowed).'Direct');
	if (empty($associate['path_to_gsa_agreement']))
	{
		$gsa_agreement = form_upload('agreement');
	}
	else
	{
		$gsa_agreement = anchor('associates/view_gsa/'.$associate['id'], 'GSA Agreement').'&nbsp;'.anchor('/associates/delete_gsa/'.$associate['id'], 'Remove GSA');
	}
	$this -> table -> add_row('GSA agreement', $gsa_agreement);
	$this -> table -> add_row('&nbsp;', form_submit('submit', isset($associate['id'])?'Update':'Add'));
	echo $this -> table -> generate();
	echo form_close();
?>