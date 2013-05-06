function site_url(url) {
	return base_url+'/'+url;
}

/**
* @function: getBytesWithUnit()
* @purpose: Converts bytes to the most simplified unit.
* @param: (number) bytes, the amount of bytes
* @returns: (string)
*/
var getBytesWithUnit = function( bytes ){
	if( isNaN( bytes ) ){ return; }
	var units = [ ' bytes', ' KB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB' ];
	var amountOf2s = Math.floor( Math.log( +bytes )/Math.log(2) );
	if( amountOf2s < 1 ){
		amountOf2s = 0;
	}
	var i = Math.floor( amountOf2s / 10 );
	bytes = +bytes / Math.pow( 2, 10*i );

	// Rounds to 3 decimals places.
        if( bytes.toString().length > bytes.toFixed(0).toString().length ){
            bytes = bytes.toFixed(0);
        }
	return bytes + units[i];
}

function IsEmail(email) {

	if (/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email))
		return true;
    else
		return false;
}




var Gear = {
	blockedElements: [],
	show: function()
	{
		$(".gear").fadeIn(500);
		$(".gear_background").fadeIn(300);
	},
	hide: function()
	{
		$(".gear").fadeOut(500);
		$(".gear_background").fadeOut(300);
	},
	blockElement: function($el)
	{
		var width = $el.outerWidth();
		var height = $el.outerHeight();
		var top = $el.position().top;
		var left = $el.position().left;


		if ($.inArray($el,Gear.blockedElements) == -1)
			Gear.blockedElements.push($el);

		if ($el.is(':visible') && !$el.next(".gear-blocking-tag").size())
		{
			$el.after('<div class="gear-blocking-tag"></div>');
			$el.next(".gear-blocking-tag")
				.css({
					'width': width,
					'height': height,
					'top': top,
					'left': left
				}).show();
		}
	},
	unblockElement: function($el)
	{
		$el.next(".gear-blocking-tag").remove();
	},
	refreshBlockings: function()
	{
		$.each(Gear.blockedElements,function(i){
			Gear.blockElement(Gear.blockedElements[i]);
		});
	}
};



var Loader = {
	show: function()
	{
		$(".loader").fadeIn(500);
		$(".gear_background").fadeIn(300);
	},
	hide: function()
	{
		$(".loader").fadeOut(500);
		$(".gear_background").fadeOut(300);
	}
};



function getElementFromFuture(selector, callback)
{
	var el = $(selector);
	el.wrap('<div class="temporary_holder" />')
	var parent = el.parent();
	parent.fadeOut(100,function(){
		el.remove();
	});

	parent.load(window.location+" "+selector, {}, function(){

		var el = parent.find(selector);

		parent.fadeIn(100,function(){
			el.unwrap();

			if (typeof (callback) != 'undefined')
				callback();
		});


	});
}
var pm_assoc    =   {
    "ENABLE":function(ID,CASE_ID){
        Loader.show();
        $.ajax({
            type: "POST",
            url: BASE_URL+"cases/enable_disable_assoc",
            data: { "ACTION":"ENABLE", "ASSOC_ID":ID, "CASE_ID": CASE_ID },
            dataType: "json",
            success: function(json){
                if(json.error){
                     alert(json.error_text);
                }
                else{
                   var str = "#ENABLE_"+ID+"_"+CASE_ID;
                    var button = $(str);
                    var td = button.parent("td");
                    td.children(".REPLACEMENT").show();
                    var tr = td.parent("tr");
                    tr.children("td").each(function(){
                        $(this).removeClass("strike");
                    });
                    button.attr("id","DISABLE_"+ID+"_"+CASE_ID).removeClass('ENABLE').addClass("DISABLE").children("span").text("DISABLE");
                }
                Loader.hide();
            }
        });
    },
    "DISABLE":function(ID,CASE_ID){
        Loader.show();
        $.ajax({
            type: "POST",
            url: BASE_URL+"cases/enable_disable_assoc",
            data: { "ACTION":"DISABLE", "ASSOC_ID":ID, "CASE_ID": CASE_ID },
            dataType: "json",
            success: function(json){
               if(json.error){
                     alert(json.error_text);
                }
                else{
                    var str = "#DISABLE_"+ID+"_"+CASE_ID;
                    var button = $(str);
                    var td = button.parent("td");
                    td.children(".REPLACEMENT").hide();
                    var tr = td.parent("tr");
                    tr.children("td").each(function(){
                        $(this).addClass("strike");
                    });
                    button.attr("id","ENABLE_"+ID+"_"+CASE_ID).removeClass('DISABLE').addClass("ENABLE").children("span").text("ENABLE");
                }
                Loader.hide();
            }
        });
    }
}
