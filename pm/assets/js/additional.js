jQuery.fn.single_double_click = function(single_click_callback, double_click_callback, timeout) {
  return this.each(function(){
    var clicks = 0, self = this;
    jQuery(this).click(function(event){
      clicks++;
      if (clicks == 1) {
        setTimeout(function(){
          if(clicks == 1) {
            single_click_callback.call(self, event);
          } else {
            double_click_callback.call(self, event);
          }
          clicks = 0;
        }, timeout || 300);
      }
    });
  });
}

function generate_password(length, special) {
    var iteration = 0;
    var password = "";
    var randomNumber;
    if (special == undefined){
        var special = false;
    }
    while(iteration < length){
        randomNumber = (Math.floor((Math.random() * 100)) % 94) + 33;
        if ( ! special){
            if ((randomNumber >=33) && (randomNumber <=47)) { continue; }
            if ((randomNumber >=58) && (randomNumber <=64)) { continue; }
            if ((randomNumber >=91) && (randomNumber <=96)) { continue; }
            if ((randomNumber >=123) && (randomNumber <=126)) { continue; }
        }
        iteration++;
        password += String.fromCharCode(randomNumber);
    }
    return password;
}

function phoneNumberValidator() {
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
        //console.log(e);
    }
    //$('output').value = output.toString();
    return true;
}