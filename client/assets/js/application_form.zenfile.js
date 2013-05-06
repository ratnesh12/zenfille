var stanlider={
    "Show":function(slide,id){

        if (id == 11) {
            get_selected_flags();
        }

        if (!related && id == 5) {
            change_flags_reference_numbers();
        }

        if (!related && id == 5) {
            change_flags_reference_numbers();
        } else if(related && id == 3) {
            change_flags_reference_numbers();
        }

        switch(slide){
            case "next":
                var current_id =  $(".step.step-current").attr('id');
                var num = current_id.substr(4);

                $(".step").hide().removeClass("step-current");

                var cur = $("#step"+num).hide("slide",{
                    direction: "left"
                },300,function()
                {
                    /* hide all steps*/
                    $("#step"+id).show("slide",{
                        direction: "right"
                    },300).addClass("step-current");


                }
                ).removeClass("step-current");
                break;
            case "back":
                var current_id =  $(".step.step-current").attr('id');
                var num = current_id.substr(4);

                var cur = $("#step"+num).hide("slide",{
                    direction: "right"
                },300,function()
                {
                    $("#step"+id).show("slide",{
                        direction: "left"
                    },300).addClass("step-current");
                }
                ).removeClass("step-current");

                break;
            case "success":
                $(".step").removeClass("step-current").hide();
                $("#"+id).show("slide",{
                    direction: "right"
                },300).addClass("step-current");
                break;
            case "fail":
                $(".step").removeClass("step-current").hide();
                $("#"+id).show("slide",{
                    direction: "right"
                },300).addClass("step-current");
                break;
        }
    },
    "anyPage":function(slide,id_cur,id_next){
        switch(slide){
            case "next":
                var cur = $("#step"+id_cur).hide("slide",{
                    direction: "left"
                },300,function()
                {
                    $(".step").hide().removeClass("step-current");
                    $("#step"+id_next).show("slide",{
                        direction: "right"
                    },300).addClass("step-current");
                }
                ).removeClass("step-current");
                break;
            case "back":
                var cur = $("#step"+id_cur).hide("slide",{
                    direction: "left"
                },1,function()
                {
                    $("#step"+id_next).children(".bottom_nav").children(".back_to_review").show();
                    $("#step"+id_next).show("slide",{
                        direction: "right"
                    },300).addClass("step-current");
                }).removeClass("step-current");
                break;
        }
    }
}


// validate the contacts

function validate_contacts(){

	var at_least_one_contact_not_valid = false;
    $('input',"#step5").each(function(){
        if ($(this).val() && !IsEmail($(this).val()))
            at_least_one_contact_not_valid = true;
    });


    if (at_least_one_contact_not_valid)
    {
        stanOverlay.setTITLE("Information!");
        stanOverlay.setMESSAGE("Please use valid email addresses when adding case contacts.");
        stanOverlay.SHOW();
        $("#step5").addClass('holdSlide');
        return false;
    }
    else{
    	$("#step5").removeClass('holdSlide');
    	return true;
    }
        
}


// function will be called after file upload
function parseDate(input) {
    var parts = input.match(/(\d+)/g);
    return new Date(parts[0], parts[1]-1, parts[2]);
}

function gotCompleteUpload(id, fileName, json)
{
	$(".file-size",'.file-'+id).remove();
    $(".file-progress-"+id,'.file-'+id).remove();
    $(".file-category",'.file-'+id).show();
    $(".file-remove",'.file-'+id).show();
    makeTooltip($(".file-remove",'.file-'+id));
    $('.file-info','.file-'+id).append('<input type="hidden" name="attachments['+id+']" value="'+json.attachment.file+'" />');
    $('.file-info','.file-'+id).append('<input type="hidden" name="case_files['+id+']" value="'+json.lastinsert+'" />');
    // select
    $("select",'.file-'+id).selectBox();

    stanOverlay.setTITLE("File uploaded");
    var fileName = json.attachment.name;
    if(fileName.length>23)fileName = fileName.substring(0,22)+'...';
    stanOverlay.setMESSAGE(""+fileName+'.'+json.attachment.ext+" uploaded");
    stanOverlay.SHOW();

    autoResize();
}

function gotCompleteMultiUpload(json,id){
	$('.file-'+id).remove();
	for(i in json.files){
		var file = json.files[i];
		file.id = file.id?file.id:id+'_'+i
		
		$.tmpl( $("#templateUploadedFile").html(), {
	            "id" : file.id,
	            "ext": file.ext,
	            "fileName": file.name,
	            "total": ""
	    }).appendTo( ".files_table" );
		$(".file-remove",'.file-'+file.id).show();
		$('.file-info','.file-'+file.id).append('<input type="hidden" name="attachments['+file.id+']" value="'+file.file+'" />');
	    $('.file-info','.file-'+file.id).append('<input type="hidden" name="case_files['+file.id+']" value="'+file.id+'" />');
	    $("select",'.file-'+file.id).selectBox();
	}
	
	stanOverlay.setTITLE("File uploaded");
    stanOverlay.setMESSAGE("File uploaded and unziped");
    stanOverlay.SHOW();

    autoResize();
}



function showParts(val)
{
    switch(val)
    {
        case 'direct':
        case '3':
            $(".common_regions").show();
            $(".select_all_regions").hide();

            $(".application_number_example.direct").show();
            $(".application_number_example.pct").hide();
            $(".application_number_example.ep").hide();

            $(".show_on_ep").hide();
            $(".hide_on_ep").show();

            $(".show_on_direct").show();
            $(".hide_on_direct").hide();

            $(".show_on_pct").hide();
            $(".hide_on_pct").show();
            break;

        case 'ep':
        case '2':
            $(".common_regions").show();
            $(".select_all_regions").show();

            $(".application_number_example.direct").hide();
            $(".application_number_example.pct").hide();
            $(".application_number_example.ep").show();

            $(".show_on_ep").show();
            $(".hide_on_ep").hide();

            $(".show_on_direct").hide();
            $(".hide_on_direct").show();

            $(".show_on_pct").hide();
            $(".hide_on_pct").show();
            break;

        case 'pct':
        case '1':
            $(".common_regions").show();
            $(".select_all_regions").hide();

            $(".application_number_example.direct").hide();
            $(".application_number_example.pct").show();
            $(".application_number_example.ep").hide();

            $(".show_on_ep").hide();
            $(".hide_on_ep").show();

            $(".show_on_direct").hide();
            $(".hide_on_direct").show();

            $(".show_on_pct").show();
            $(".hide_on_pct").hide();
            break;
    }
}


function autoResize()
{
    if ($.browser.msie)
        $(".bx-window").animate({
            "height":$(".step-current").outerHeight()
        },500);
    else
        $(".bx-window").animate({
            "height":$(".step-current").outerHeight()
        },300);

}

jQuery(document).ready(function($)
{
    var selected_countries;


    $(".validateThisForm").validate();

    // scroll
    $("#form_loadholder").hide(0);
    $(".applicationForm").fadeIn(2000);

    $.scrollTo( $(".content_header").offset().top - 15, 2000 );


    if (!$("html").hasClass("js"))
        $("html").addClass("js");

    // blockin enter keypress
    $("input,select",".applicationForm").keypress(function(evt)
    {
        var key=(evt.charCode) ? evt.charCode: ((evt.keyCode)?evt.keyCode:((evt.which)?evt.which:0));
        if( key == $.ui.keyCode.ENTER )
            return false;
    });


    // manipulating the slider with keybord
    $(window).keypress(function(evt)
    {
        var next = $(".next",".step-current");
        var prev = $(".back",".step-current");


        var key = (evt.charCode) ? evt.charCode: ((evt.keyCode)?evt.keyCode:((evt.which)?evt.which:0));

        if (evt.ctrlKey)
        {
            switch(key)
            {
                case $.ui.keyCode.LEFT:
                    prev.trigger("click");
                    break;

                case $.ui.keyCode.RIGHT:
                    next.trigger("click");
                    break;
            }
        }
    });

    if($(".is_intake").size() && $(".is_intake").val())
    {
        var val = $(".is_intake").val();

        $(".in",".intake_estimate_activator").removeClass("checked");
        $(".in",".intake_estimate_activator[data-value='"+val+"']").addClass("checked");
    }
    $(".intake_estimate_activator").click(function()
    {
        selected_countries = getCountriesList($(".application_type").val());
        $( ".flags_autocomplete" ).autocomplete('option','source',selected_countries);
        var val =   parseInt($(this).attr("data-value"));
        $(".is_intake").val(val);
        $(".in",".intake_estimate_activator").removeClass("checked");
        $(".in",".intake_estimate_activator[data-value='"+val+"']").addClass("checked");
        if (!related ) {
            stanlider.Show("next",1);
        }else{
            stanlider.Show("next",2);
        }

        return false;
    });

    // selecting countries list
    function getCountriesList(tp)
    {
        switch(tp)
        {
            case "ep":
            case '2':
                countries = epCountries;
                break;

            case "pct":
            case '1':
                countries = pctCountries;
                break;

            default:
                countries = directCountries;
                break;
        }

        return countries;
    }
    var parse ="";
    $(".countries_activator").click(function()
    {
        selected_countries = getCountriesList($(this).attr("data-jslist"));
        $( ".flags_autocomplete" ).autocomplete('option','source',selected_countries);
        $(".application_type").val($(this).attr("data-jslist"));

        parse = $(this).attr("data-jslist") == 'pct' ? true : false;
        $(".in",".countries_activator").removeClass("checked");
        $(".in",$(this)).addClass("checked");
        showParts($(this).attr("data-jslist"));
        stanlider.Show("next",2);
        switch($(this).attr("data-jslist")){
            case "pct":
                $(".common_regions").children("div").children(".show_on_pct").children(".flag-box").fadeIn();
                $("span.for_direct").css('display','none');
                break;
            case "direct":
                $(".common_regions").children("div").children(".show_on_direct").children(".flag-box").fadeIn();
                $("span.for_direct").css('display','inline');
                break;
            case "ep":
                $(".common_regions").children("div").children(".show_on_ep").children(".flag-box").fadeIn();
                $("span.for_direct").css('display','none');
                break;
        }
        $(".flags.selected .flag-box").remove();
        return false;
    });
    // first initialization

    if ($(".application_type").val())
    {
        selected_countries = getCountriesList($(".application_type").val());
        $( ".flags_autocomplete" ).autocomplete('option','source',selected_countries);

        showParts($(".application_type").val());
    }


    /* ADD ALL COUNTRIES TO SELECTED LIST */
    $(".select_all_countries_activator").click(function(){
    	$('.show_on_ep .flag-box').each(function(){
    		var src = $(this).find('img').attr('src');
    		var value = $(this).find('div:last').html();
    		var id = $(this).find('a.flag_add_activator').attr('data-id');
    		var Est_Type = ($(".show_on_pct").is(':visible')) ? "pct" : ($(".show_on_direct").is(':visible')) ? "direct" : "ep";
    		value = value.replace(/\s/,'<br />');
    		if (!$('.flags.selected').find('img[src="'+src+'"]').size())
            {
    			$.tmpl( $("#templateSelectedFlag").html(),
                {
                    "type":Est_Type,
                    "flag_src":src,
                    "value": value,
                    "id": id
                }).appendTo( ".flags.selected" );
    			$(this).fadeOut(400);
    			
    			setTimeout(function() {
                    autoResize();
                },300);
    			$(".flags.selected .flag-box-noflag").fadeOut(400);
            }
    	});
    	previewRegions();
        return false;
    });
    
    $(".select_all_countries_activator_").click(function()
    {
        $.each(epCountries,function(index,data)
        {
            var were_wrap = '';
            var dynamic_style = '';
            if (!$('.flags.selected').find('img[src="/client/'+data.flag+'"]').size())
            {
                var str = data.value;
                var value = str.replace(/\s/,'<br />');
                //if (str != value)
                {
                    were_wrap = 'wrap';
                }
                var Est_Type = ($(".show_on_pct").is(':visible')) ? "pct" : ($(".show_on_direct").is(':visible')) ? "direct" : "ep";
                //$(this).fadeOut(400);
                $.tmpl( $("#templateSelectedFlag").html(),
                {
                    "type":Est_Type,
                    "flag_src":'/client/'+data.flag,
                    "cssClass" : "flag-"+data.cssClass,
                    "were_wrap": were_wrap,
                    "value": value,
                    "id": data.id
                }).appendTo( ".flags.selected" );
                //    	                previewRegions();
                setTimeout(function() {
                    autoResize();
                },300);

                $(".flags.selected .flag-box-noflag").fadeOut(400);
            }
        });
        previewRegions();
        return false;
    });
    /* AUTOCOMPLETER COUNTRIES FOR INPUT FIELD */
    $( ".flags_autocomplete" ).autocomplete({
        minLength: 0,
        autoFocus: true,
        source: selected_countries ? selected_countries : getCountriesList(Zenfile.case_type==1?'pct':(Zenfile.case_type==2?'ep':'direct')),
        focus: function( event, ui ) {
        },
        search: function( oEvent, oUi ) {
        var selected_countries_in = selected_countries ? selected_countries : getCountriesList(Zenfile.case_type==1?'pct':(Zenfile.case_type==2?'ep':'direct'));
        // get current input value
        var sValue = $(oEvent.target).val();
        // init new search array
        var aSearch = [];
        // for each element in the main array ...
        $.each(selected_countries_in,function(iIndex, sElement) {
            // ... if element starts with input value

            if (sElement.value.substr(0, sValue.length).toLowerCase() == sValue.toLowerCase()) {
            // add element
            aSearch.push(sElement);
            }
            });
        // change search array
        $(this).autocomplete('option', 'source', aSearch);
        },
        select: function( event, ui )
        {
        var searched    =   "";
        var pct         =   $(".show_on_pct").is(':visible');
        var direct      =   $(".show_on_direct").is(':visible');
        var ep          =   $(".show_on_ep").is(":visible");
        if(pct)
        {
        searched = $("#selected-pct-" + ui.item.id);/* PCT */
        }
        else
        {
        if(direct)
        {
        searched = $("#selected-direct-" + ui.item.id);/* DIRECT */
        }
        else
        {
        if(ep)
        {
        searched = $("#selected-ep-" + ui.item.id);/* EP */
        }
        else
        {
        searched = $("#selected-store-" + ui.item.id);/* REESTIMATE */
        }
        }
        }

        if (searched.size()){
        stanOverlay.setTITLE("Selecting Countries");
        stanOverlay.setMESSAGE(ui.item.value+" already in the list!");
        stanOverlay.SHOW();
        }
        else
        {
//        var str = $.trim(ui.item.value);
//        ui.item.value = str.replace(/\s/,'<br />');
        //if (str != ui.item.value)
        {
        var were_wrap = 'wrap';
        }
        if (!$(".flag-img.flag-"+ui.item.cssClass,".flags.selected").size())
        {
        var Est_Type    =   "";
        if(pct)
        {
        $(".show_on_pct").children("#pct-" + ui.item.id + ".flag-box").fadeOut(400);
        Est_Type = "pct";
        }
        else
        {
        if(direct)
        {
        $(".show_on_direct").children("#direct-" + ui.item.id + ".flag-box").fadeOut(400);
        Est_Type = "direct";
        }
        else
        {
        if(ep)
        {
        $(".show_on_ep").children("#ep-" + ui.item.id + ".flag-box").fadeOut(400);
        Est_Type = "ep";
        }
        else
        {
        $(".store").children("#store-" + ui.item.id + ".flag-box").fadeOut(400);
        Est_Type = "store";
        }
        }
        }

        $.tmpl( $("#templateSelectedFlag").html(),
        {
            "type":Est_Type,
            "flag_src":'/client/'+ui.item.flag,
            "cssClass" : 'flag-img flag-'+ui.item.cssClass,
            "were_wrap": were_wrap,
            "value": ui.item.value,
            "id": ui.item.id
            }).appendTo( ".flags.selected" );
        previewRegions();
        autoResize();
        }

        $(".flags.selected .flag-box-noflag").hide();
        $( ".flags_autocomplete" ).val("");
        }
        return false;
        }
        }).data( "autocomplete" )._renderItem = function( ul, item ) {
        return $( '<li></li>' )
        .data( "item.autocomplete", item )
        .append( '<a>' + item.value + '</a>' )
        .appendTo( ul );
    };

    /* ADD FLAG TO SELECTED COUNTRY  */
    $(".flag_add_activator").live("click",function()
    {
        $(".flags.selected .flag-box-noflag").fadeOut(400);
        var parent = $(this).parents();
        var flag_src = parent.find('img').attr('src');
        var cssClass = parent.attr("class");
        var value = parent.parents(".flag-box").text();
        $(parent.parents(".flag-box")).fadeOut(400);
        var data_id = $(this).attr("data-id");
        var str = $.trim(value);
        value = str.replace(/\s/,'<br />');
        //if (str != value)
        {
            var were_wrap = 'wrap';
        }
        if (!$('.flags.selected').find('img[src="'+flag_src+'"]').size())
        {
            var pct         =   $(".show_on_pct").is(':visible');
            var direct      =   $(".show_on_direct").is(':visible');
            var ep          =   $(".show_on_ep").is(":visible");

            var Est_Type    =   "";
            if(pct)
            {
                $(".show_on_pct").children("#pct-" + data_id + ".flag-box").fadeOut(400);
                Est_Type = "pct";
            }
            else
            {
                if(direct)
                {
                    $(".show_on_direct").children("#direct-" + data_id + ".flag-box").fadeOut(400);
                    Est_Type = "direct";
                }
                else
                {
                    if(ep)
                    {
                        $(".show_on_ep").children("#ep-" + data_id + ".flag-box").fadeOut(400);
                        Est_Type = "ep";
                    }
                    else
                    {
                        $(".store").children("#store-" + data_id + ".flag-box").fadeOut(400);
                        Est_Type = "store";
                    }
                }
            }
            $.tmpl( $("#templateSelectedFlag").html(),
            {
                "type":Est_Type,
                "cssClass" : cssClass,
                "flag_src" :flag_src,
                "were_wrap": were_wrap,
                "value": value,
                "id": data_id
            }).appendTo( ".flags.selected" );
            previewRegions();
            setTimeout(function() {
                autoResize();
            },0);
        }
        return false;
    });

    /* HACK CLIKING ON THE IMAGE DIV */
    $(".flags.common .flag-box,.flags.store .flag-box").live("click",function()
    {
        $(".flag_add_activator",$(this)).click();
        return false;
    });

    /* REMOVING COUNTRY FROM SELECTED */
    $(".flag_remove_activator").live("click",function()
    {
        var parent = $(this).parents(".flag-box");
        parent.fadeOut(400);
        /* RESTORE REMOVED ELEMENT IN LIST ON COUNTIRES */
        var restore_elem    =   $(parent).attr("id").replace("selected-", "");
        if($("#"+restore_elem).size()){
            $("#"+restore_elem).fadeIn(400);
        }
        else{
            /*
             * it could be only REESTIMATE Page
             * crappy way =(
             * */
            var papa                =   $(this).parent().parent();
            var tmp                 = papa.attr("id").replace("selected-","");
            var split               = tmp.split("-");
            var country_id          =   split[1];
            var childrens           =   papa.children("div");
            var src                 =   $.trim(childrens.eq(0).children("img").attr("src"));

            var CountryName         =   $.trim(childrens.eq(1).text());
            var flag_box =  "<div class='flag flag-box common-country' id='"+tmp+"'>"+
            "<div class='flag-img'>"+
            "<a class='add flag_add_activator'  href='#' data-id='"+country_id+"'></a>"+
            "<img src='"+src+"'>"+
            "</div>"+
            "<div>"+CountryName+"</div>"+
            "</div>";
            $(".store").prepend(flag_box);
        }

        setTimeout(function() {
            parent.remove();

            if (!$(".flags.selected .flag-box:visible").size())
            {
                $(".flags.selected .flag-box-noflag").show();

            }
            previewRegions();
            autoResize();

        },400);

        return false;
    });
    /* ЗАГЛУШКА ДЛЯ УДАЛЕНИЯ */
    $(".flags.selected .flag-box").live("click",function()
    {
        $(".flag_remove_activator",$(this)).click();
        return false;
    });

    $(".flag-box").live({
        mouseenter: function(){
            $(this).find(".remove_country_box, .add_country_box").css("display", "block");
        },
        mouseleave: function(){
            $(this).find(".remove_country_box, .add_country_box").css("display", "none");
        }
    });



    // multiple contact fields
    $(".add_additional_contact_activator").click(function(){

        var this_contact = $(this).parents(".additional_contact");
        if ($(".additional_contact").size() <= 6)
        {
            if ($(".additional_contact").size() == 6)
                $(".additional_contact.adder").hide(500);

            $.tmpl( $("#templateAdditionalContacts").html(), {
                "contact" : this_contact.find("input").val()
            }).appendTo( ".additional_contacts" );
            $(".additional_contact:hidden").slideDown(500);
            this_contact.find("input").val("");
        }
        else
        {
            stanOverlay.setTITLE("Information!");
            stanOverlay.setMESSAGE("There is a limit of "+$(".additional_contact").size()+' contacts available');
            stanOverlay.SHOW();
        }

        setTimeout(function(){
            autoResize();
        },500);

        return false;
    });


    $(".remove_additional_contact_activator").live("click",function(){

        var this_contact = $(this).parents(".additional_contact");

        if ($(".additional_contact").size() == 7)
            $(".additional_contact.adder").show(500);

        this_contact.slideUp(500).delay(500,function(){
            this_contact.remove();
        });

        setTimeout(function(){
            autoResize();
        },500);

        return false;
    });




    


    // radio buttons at step 6
    $(".applicationFormRadio").click(function(){

        $(".applicationFormRadio").removeClass("pressed");
        $(this).addClass("pressed");

        if ($(this).hasClass("yes"))
            $("#notification_each_time").val("yes");
        else
            $("#notification_each_time").val("no");

        //slider.goToNextSlide();
        stanlider.Show('next', 7);
        autoResize();
        return false;
    });





    function checkForm() {
//        console.log('check form');
        var result = true;

        var step6_is_valid = true;
        var step1_is_valid = true;
        var redirect_slide = '';

        var required_fields = [];

        $("input",".applicationForm").each(function()
        {

            if ( typeof(required_fields[$(this).attr("name")]) != 'undefined')
            {
                if (!$(this).val())
                {
                    step6_is_valid = false;
                    redirect_slide = required_fields[$(this).attr("name")].slide;
                }

                if($(this).attr("name") == 'email')
                {
                    if (!IsEmail($(this).val()))
                    {
                        step6_is_valid = false;
                        redirect_slide = required_fields[$(this).attr("name")].slide;
                        stanOverlay.setTITLE("Error");
                        stanOverlay.setMESSAGE("Please, enter a valid email.");
                        stanOverlay.SHOW();
                    }
                }
            }
        });


        var required_fields = [];
        if($('#PREVIEW_application_type').text()=='Direct Filing'){
        	required_fields['applicant'] = { slide: 3 };
            required_fields['deadline'] = { slide: 3 };
        }
    	
        

        if ($("input[name='application_number']").size())
            required_fields['application_number'] = {
                slide: 2
            };

        $("input",".applicationForm").each(function()
        {

            if ( typeof(required_fields[$(this).attr("name")]) != 'undefined')
            {
                if (!$(this).val())
                {
                    step1_is_valid = false;
                    redirect_slide = required_fields[$(this).attr("name")].slide;
                }
            }
        });


        if (step1_is_valid && !step6_is_valid)
        {
            result = false;
            stanOverlay.setTITLE("Information!");
            stanOverlay.setMESSAGE("Please be sure to fill out all fields marked with an <b style='color:red;'>*</b>.");
            stanOverlay.SHOW();
        }
        else if (!step1_is_valid && step6_is_valid)
        {

            //slider.goToSlide(redirect_slide);
            stanlider.Show("back", redirect_slide);
            result = false;
            stanOverlay.setTITLE("Information!");
            stanOverlay.setMESSAGE("Please be sure to fill out all fields marked with an <b style='color:red;'>*</b>.");
            stanOverlay.SHOW();
        }
        else if (!step1_is_valid && !step6_is_valid)
        {
            $(".next","#step2").unbind();
            $(".next","#step2").click(function()
            {
                //slider.goToSlide(9);
                stanlider.Show("back", 9);
                return false;
            });
            stanlider.Show("back", redirect_slide);
            //slider.goToSlide(redirect_slide);

            result = false;
            stanOverlay.setTITLE("Information!");
            stanOverlay.setMESSAGE("Please be sure to fill out all fields marked with an <b style='color:red;'>*</b>.");
            stanOverlay.SHOW();

        }else if($('#PREVIEW_application_type').text()=='Direct Filing' && $('#PREVIEW_regions').text()==''){
        	stanlider.Show("back", "7");
            result = false;
            stanOverlay.setTITLE("Information!");
            stanOverlay.setMESSAGE("Please be sure to fill out all fields marked with an <b style='color:red;'>*</b>.");
            stanOverlay.SHOW();
        }
        if(result == false){
        	$("#step"+redirect_slide).children(".bottom_nav").children(".back_to_review").show();
        }
        return result;
    }


    // submitting form
    $(".applicationForm").live("submit",function()
    {

        if (!checkForm())
            return false;

        Loader.show();

        $.post($(this).attr("action"),$(this).serialize(),function(data)
        {
            Loader.hide();
            try {
                var json = $.parseJSON(data);
                if (notifyIsSuccess(json))
                {
                    $(".new_case_id").text(json.data.case_number);
                    $.ajax({
                        type: "POST",
                        url: base_url_pm+"estimates/get_estimate_table/"+json.data.case_number
                    });
                    stanlider.Show("success","final");
                    autoResize();
                }
                else
                {
                    stanlider.Show("fail","fail");
                    autoResize();
                }
            } catch(e) {
                stanlider.Show("fail","fail");
                autoResize();
            }

            return false;
        });





        return false;
    });



    // parsing remote
    var parsingTimer = null;
    var parseRemoteRequest = null;
    $(".pleaseStandByNotify").click(function(){
        parseRemoteRequest.abort();
    });
    $(".parse_application_activator").blur(function(){

        var activator = $(this);
        var application_number = $(this).val();
        parsingTimer = setTimeout(function(){
            if($(".pleaseStandByNotify"))
            {

                $(".pleaseStandByNotify").fadeIn("fast",function() {
                    autoResize();
                });
            }
        }, 1500);


        parseRemoteRequest = $.ajax({
            url: base_url + 'wp_engine/light_parsing' ,
            type: "POST",
            dataType: "json",
            data: {
                "application_number": application_number ,
                "parse":parse
            },
            success: function(json) {

                clearTimeout(parsingTimer);
                if($(".pleaseStandByNotify"))
                {
                    $(".pleaseStandByNotify").fadeOut("fast", function() {
                        autoResize();
                    });
                }

                $( ".additionalCaseInfo" ).empty();
                if(json['title'])
                    $(".application_title_field").val(json['title']);

                if(json['applicant'])
                    $(".applicant_field").val(json['applicant']);

                var deadline = new Date();

                if(json['filing_deadline'])
                {
//                    deadline.setTime(parseDate(json['filing_deadline']));
                    $(".filing_deadline_field").val(json['filing_deadline']);
                }



                return false;
            },
            error: function() {
                clearTimeout(parsingTimer);
                $(".pleaseStandByNotify").fadeOut(500, function() {
                    autoResize();
                });
            }
        });

        return false;
    });


});


/* APPLICATION REGIONS */
function previewRegions(){
    var Stak        =   '';
    var regions     =   '';
    var separator   =   " , ";
    var deda   =   $(".flags.selected .flag-box:visible");
    if(deda.size()){
        $(deda).each(function(i)
        {
            var pole = $(this).children().eq(1);
            regions +=  pole.text();
            if(i!=deda.size()-1){
                regions += separator;
            }
        });
        $("#PREVIEW_regions").text(regions);
    }
}
$(function(){
    /***************************************/
    /* APPLICATION NUMBER */
    $('input[name="reference_number"]').live("change",function(){
        $("#PREVIEW_application_number").text($(this).val());
    });
    $("#PREVIEW_application_number").ready(function(){
        $("#PREVIEW_application_number").text($('input[name="reference_number"]').val());
    });
    /***************************************/
    /* APPLICATION TYPE */
    $(".intake_estimate_activator").click(function()
    {
        var val =   parseInt($(this).attr("data-value"));
        switch(val){
            case 1:
                val = "Immediate Processing";
                break;
            default:
                val = "Estimate";
                break;
        }
        $("#PREVIEW_application_type").text(val);
    });

    $("#PREVIEW_application_type").ready(function(){
        var val = parseInt($('input[name="is_intake"]').val());
        switch(val){
            case 1:
                val = "Immediate Processing";
                break;
            default:
                val = "Estimate";
                break;
        }
        $("#PREVIEW_application_type").text(val);
    });

    /* spike */
    $(".back_to_review").live("click",function(){
        $(this).hide();
    });
    $(".next,.back").live("click",function(){
        $(".back_to_review").hide();
    });
/* end spike */
});