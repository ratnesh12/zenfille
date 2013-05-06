var stanOverlay={
    "overlay":null,
    "setMESSAGE":function(message){
        $(".stan_message_box_container table .message_box .info_center .info_message").html(message);
    },
    "setTITLE":function(title){
        $(".stan_message_box_container table .message_box .info_center .info_Information").html(title);
    },
    "SHOW":function(){
        $(".stan_layout").fadeTo(100,0.5,function(){
            $(".stan_message_box_container").fadeIn(400);
        });
        this.overlay = setTimeout(this.HIDE, 3000);
    },
    "HIDE":function(){

        if(this.overlay !=   null)
            clearTimeout(this.overlay);
        $(".stan_layout,.stan_message_box_container").fadeOut(400);
    }
}

$(document).ready(function() {

    $('.add_sub_line').live('click' , function(){
        var country_id = $(this).attr('href');
        var master_country_id = $(this).attr('rel');
        var sub_line = '<tr><td></td><td class=""></td><td class=""></td><td class=""><input type="text" name="sub_custom_text[]" value=""><input type="hidden" value="' + country_id + '" name="sub_parent_id[]"><input type="hidden" value="' + master_country_id + '" name="sub_country_id[]"></td><td class=""><input type="text" name="sub_filing_fee[]" value=""></td><td class=""><input type="text" name="sub_official_fee[]" value=""></td><td class=""><input type="text" name="sub_translation_fee[]" value=""></td><td class=""></td></tr>';
        var object = $(this).parent().parent().parent();
        $(object).after(sub_line);
        return false;
    });

    $('.edit_sub_line').live('click' , function(){
        var estimate_country_id = $(this).attr('href');
        var master_country_id = $(this).attr('rel');
        var parent_tr = $(this).parent().parent().parent();
        var custom_text = $(parent_tr).find('input[name=custom_text]').val();
        var filing_fee = $('input[name=filing_fee_' + master_country_id + ']').val();
        var translation_fee = $(parent_tr).find('input[name=translation_fee_' + master_country_id + ']').val();
        var official_fee = $(parent_tr).find('input[name=official_fee_' + master_country_id + ']').val();
        var sub_line = '<tr><td></td><td class=""></td><td class=""></td><td class=""><input type="text" name="update_custom_text[]" value="' + custom_text + '"><input type="hidden" value="' + estimate_country_id  + '" name="update_estimate_country_id[]"></td><td class=""><input type="text" name="update_filing_fee[]" value="' + filing_fee + '"></td><td class=""><input type="text" name="update_official_fee[]" value="' + official_fee + '"></td><td class=""><input type="text" name="update_translation_fee[]" value="' + translation_fee + '"></td><td class=""></td></tr>';
        $(parent_tr).replaceWith(sub_line);
        return false;
    });

    $('.update_fees_for_all_users').click(function() {
        
    	if(!confirm('Are you sure you want to proceed?\nDoing so will update country fees for all current users.'))return false;
    	
    	var country_id = $(this).attr('rel');
        var case_type_id = $(this).attr('href');

        $.ajax({
            type: 'POST',
            url: '/pm/cases/ajax_update_fees_for_all_users',
            data: {
                case_type_id: case_type_id ,
                country_id: country_id
            },
            success: function(){
                alert('Saved');
            },
            dataType: 'json'
        });
        return false;
    });

    $('#update_customer_data').click(function(){
        var customer_data = $('#serialize').serializeObject();
        customer_data['case_number'] = $('input[name=case_number]').val();
        console.log(customer_data);

        $.ajax({
            type: 'POST',
            url: '/pm/cases/ajax_update_customer_data_for_case',
            data: {
                customer_data: customer_data
            },
            success: function(){
                alert('Saved');
            },
            dataType: 'json'
        });
        return false;
    });

    $.fn.serializeObject = function()
    {
        var o = {};
        var a = this.serializeArray();
        $.each(a, function() {
            if (o[this.name] !== undefined) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };

});