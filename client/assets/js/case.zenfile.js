$(document).ready(function() {

    $("#edit-estimate").click(function() {
        $("#estimate-form").hide();
        $("#reestimate-form").show();
    });

    $("#back-estimate").click(function() {
        $("#reestimate-form").hide();
        $("#estimate-form").show();
    });

    $("#reestimate-submit, #estimate-submit").click(function()
    {
        // A list of approved countries
        var countries = [];
        var reestimate = "";
        var elem_id =   $(this).attr("id");
        if ($(this).attr("id") == "reestimate-submit") {
            reestimate = "1";
            var by_user = 0;
            $("#reestimate-form input[name='countries\\[\\]']").each(function() {
                countries.push($(this).val());
            });
            if (countries.length < 1) {
                stanOverlay.setTITLE("Approve Estimate");
                stanOverlay.setMESSAGE("Please select a country or region to approve");
                stanOverlay.SHOW();
                return false;
            }
        }
        else
        {
            var by_user = 1;
            reestimate = "0";
            $(".estimate_entry").each(function() {
                if ($(this).parent("div").hasClass("ez-checked-blue")) {
                    countries.push($(this).val());
                }
            });
            if (countries.length < 1) {
                stanOverlay.setTITLE("Approve Estimate");
                stanOverlay.setMESSAGE("Please select a country or region to approve");
                stanOverlay.SHOW();
                return false;
            }
        }


        if(elem_id == "estimate-submit"){
            // blocking
            var msg = '<div class="notify success inline"><h4>Approved!</h4><div class="message">The above estimate was approved</div></div>';
        }
        else
        {
             // blocking
            var msg = '<div class="notify warning inline"><h4>Processing</h4><div class="message">We are reviewing your application. Your ' + title_of_the_site + ' case # is '+Zenfile.case_number+'. You will be notified when the estimate is completed.</div></div>';

        }

        $("#estimate").append(msg);$("#reestimate-form").hide();
        $("#estimate-form").show();
        Gear.blockElement($("#estimate-form"));

        $.post(Zenfile.base_url + "cases/approve_estimate_form_submit/" + Zenfile.case_number, {approved_countries: countries, reestimate: reestimate , by_user: by_user}, function() {
            /* updating tab html from the future */
            getElementFromFuture("#estimate-form", function(){
                Gear.unblockElement($("#estimate-form"));
                if(elem_id != "estimate-submit"){
                Gear.blockElement($("#estimate-form"));
                }
            });
           window.location = '/client/cases/view/' + Zenfile.case_number;
        });

    });

    //Start Checkbox Plugin
    $('input[type="checkbox"].gray').ezMark({checkboxCls: 'ez-checkbox-gray', checkedCls: 'ez-checked-gray'});
    $('input[type="checkbox"].blue').ezMark({checkboxCls: 'ez-checkbox-blue', checkedCls: 'ez-checked-blue'});
    //End Checkbox Plugin

    $(".estimate_entry").each(function() {
        if ($(this).attr("disabled") == "disabled") {
            $(this).parent(".ez-checkbox-gray").addClass("ez-checked-gray");
        }
    });

});
