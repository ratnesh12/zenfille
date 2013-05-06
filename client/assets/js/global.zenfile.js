jQuery(document).ready(function($)
{


	$(".tabs_container").tabs({
		cookie: { expires: 30, path: '/', secure: true },
		select: function(){
			setTimeout(function(){
				Gear.refreshBlockings();
			},0);
		}
	});


	// crossbrowser placeholder
		$("input[placeholder], textarea[placeholder]").placeholder();

	// jquery datepicker

		$(".datepicker").datepicker({
			'showOtherMonths': true ,
            dateFormat: 'M dd, yy'
		});

//        $( "#datepicker" ).datepicker( "option", "dateFormat", $( this ).val() );

	// select
		$("select").selectBox();

	$(".notify .close").click(function(){
		var target = $(this).parents(".notify");
		target.fadeOut(500,function(){
			target.remove();
		});
		return false;
	});

});

var stanOverlay={
    "overlay":null,
    "type":"info",//info,error,warning,success
    "setMESSAGE":function(message){
        $(".stan_message_box_container table .message_box .info_center .info_message").html(message);
    },
    "setTITLE":function(title){
        $(".stan_message_box_container table .message_box .info_center .info_Information").html(title);
    },
    "setTYPE":function(type){
    	switch(type){
	    	case "error":
		 	case "success":
		 	case "warning":
				this.type = type;
				break;
		    default:
		    	this.type = "info";
    	}
    	
    },
    "SHOW":function(){
        switch(this.type){
        	case "error":
        		$(".stan_message_box_container table .message_box")
        		.addClass("message_box_error")
        		.removeClass("message_box_warning")
        		.removeClass("message_box_success");
        		break;
       	 	case "success":
        		$(".stan_message_box_container table .message_box")
        		.removeClass("message_box_warning")
        		.removeClass("message_box_error")
        		.addClass("message_box_success");
        		break;
        	case "warning":
	        	$(".stan_message_box_container table .message_box")
	        	.removeClass("message_box_error")
	        	.addClass("message_box_warning")
	        	.removeClass("message_box_success");
	    		break;
	        default:
	        	$(".stan_message_box_container table .message_box")
	        	.removeClass("message_box_warning")
	        	.removeClass("message_box_error")
	        	.removeClass("message_box_success");
        }
    	$(".stan_layout").fadeTo(100,0.5,function(){
            $(".stan_message_box_container").fadeIn(400);
        });
    	this.type = "info";
        this.overlay = setTimeout(this.HIDE, 3000);
    },
    "HIDE":function(){

        if(this.overlay !=   null)
            clearTimeout(this.overlay);
        $(".stan_layout,.stan_message_box_container").fadeOut(400);
    }
}