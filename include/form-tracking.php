<?php
/*
 * @ https://EasyToYou.eu - IonCube v11 Decoder Online
 * @ PHP 7.4
 * @ Decoder version: 1.0.2
 * @ Release: 10/08/2022
 */

// Decoded file for php version 74.
if(!defined("ABSPATH")) {
    exit;
}
$setting = get_option("novin_track_order");
$titr = isset($setting["titr"]) ? esc_attr($setting["titr"]) : "";
if(empty($titr)) {
    $titr = "لطفا شماره موبایل ایمیل یا شماره سفارش خود را وارد کنید";
}
$head = isset($setting["head"]) ? esc_attr($setting["head"]) : "";
$chdev = isset($setting["ch_dev"]) ? esc_attr($setting["ch_dev"]) : "";
$color = isset($setting["favcolor"]) ? esc_attr($setting["favcolor"]) : "";
$color_td = isset($setting["colortd"]) ? esc_attr($setting["colortd"]) : "";
$text_td = isset($setting["texttd"]) ? esc_attr($setting["texttd"]) : "";
$navar_color = isset($setting["navarcolor"]) ? esc_attr($setting["navarcolor"]) : "";
$logcheck = isset($setting["logcheck"]) ? esc_attr($setting["logcheck"]) : "";
$image_logo = isset($setting["logo"]) ? esc_attr($setting["logo"]) : "";
$site_key = isset($setting["site_key"]) ? esc_attr($setting["site_key"]) : "";
$place = isset($setting["tow_aut"]) ? esc_attr("شماره موبایل یا ایمیل سفارش") : "";
$place_number = isset($setting["tow_aut"]) ? esc_attr("شماره سفارش") : "";
if(empty($image_logo) && $logcheck != "checked") {
    $logo = order_traking_assets_url . "logo/order-t.gif";
} else {
    $logo = $image_logo;
}
$captcha_check = isset($setting["captcha"]) ? esc_attr($setting["captcha"]) : "";
$captcha = order_traking_url . "include/captcha.php";
$refresh = order_traking_assets_url . "logo/refresh.png";
$ajax = order_traking_assets_url . "logo/loading.gif";
echo "\n        <img id=\"loading-image\" src=\"";
echo $ajax;
echo "\" style=\" margin-right: auto;\n \n    position: fixed;\n    text-align: center;\n    top: 40%;\n    left: 0;\n    right: 0;\n   \n    margin-left: auto;\n    z-index: 99999999;display:none;\"/>\n\n\n\n<div id=\"form-result\"></div>\n    ";
if($captcha_check == "google") {
    $disabled = "disabled";
    echo "     <script src=\"https://www.google.com/recaptcha/api.js?hl=fa\"></script>\n<script>\n    function novinenableBtn(){\n        document.getElementById(\"submit_nosend\").disabled = false;\n    }\n</script>\n\n";
} else {
    $disabled = "";
}
if($captcha_check == "google3") {
    echo "\n<script src=\"https://www.google.com/recaptcha/api.js?hl=fa&render=";
    echo $site_key;
    echo "\"></script>\n\n<script>\nfunction grecaptcha_execute3(){\n\n    // do request for recaptcha token\n    // response is promise with passed token\n        grecaptcha.execute('";
    echo $site_key;
    echo "', {action:'validate_captcha3'})\n                  .then(function(token) {\n            // add token value to form\n            document.getElementById('g-recaptcha-response-track').value = token;\n        });\n   \n}   \n    grecaptcha.ready(function() {\n    grecaptcha_execute3();\n});\n</script>\n";
}
echo "<img style=\"margin-top: 144px!important;\" class=\"logo-o\" src=\"";
if($logcheck != "checked" && !empty($logo)) {
    echo $logo;
}
echo "\"/><br><br>\n     <form  action=\"\" method=\"post\" id=\"ajax-test-form\">\n    <div class=\"login-pagenovin\">\n  <div class=\"formnovin\">\n<p class=\"centnovin\">";
esc_html_e($titr, "woocommerce");
echo "</p>\n<input  type=\"text\" name=\"orderid\" id=\"orderid\"  placeholder=\"";
echo $place_number;
echo "\" autocompleted=\"off\" required oninvalid=\"setCustomValidity('این فیلد نباید خالی باشد')\" oninput=\"setCustomValidity('')\" >\n\n\n\n";
if(!empty($setting["tow_aut"]) && $setting["tow_aut"] == "checked") {
    echo "\n<input  type=\"text\" name=\"mobile-email\"  placeholder=\"";
    echo $place;
    echo "\" autocompleted=\"off\" required oninvalid=\"setCustomValidity('این فیلد نباید خالی باشد')\" oninput=\"setCustomValidity('')\" >\n\n\n";
}
if($captcha_check == "google") {
    echo "    <div class=\"g-recaptcha\" style=\"margin-right: -8px;\"data-sitekey=\"";
    echo $site_key;
    echo "\" data-callback=\"novinenableBtn\"></div>\n    <br>\n    ";
}
if($captcha_check == "google3") {
    echo "\n <input type=\"hidden\" id=\"g-recaptcha-response-track\" name=\"g-recaptcha-response\">\n";
}
if($captcha_check == "captcha") {
    echo "<p>کد امنیتی را وارد نمایید<br><img id=\"captcha\" src=\"";
    echo $captcha;
    echo "\" width=\"120!important\" height=\"30!important\" border=\"1\" style=\"border-radius: 3px\"; >\n<input id=\"captcha_code_input\" style= \"width:90px!important ;height: auto!important ;display: inline!important;border:1px solid #b5b5b5!important;border-radius: 5px;\" type=\"text\" name=\"captcha\" size=\"6\" maxlength=\"5\" onkeyup=\"this.value=this.value.replace(/[^0-9.]/g, '');\" required oninvalid=\"setCustomValidity('کد امنیتی را وارد کنید')\" oninput=\"setCustomValidity('')\">\n\n<small><a href=\"#n\" id=\"capchanovin\" onclick=\"\n  document.getElementById('captcha').src = '";
    echo $captcha;
    echo "?' + Math.random();\n  document.getElementById('captcha_code_input').value = '';\n  return false;\"><img src=\"";
    echo $refresh;
    echo "\"></a></small></p>\n  ";
}
echo "<button class=\"button-click\" id=\"submit_nosend\" ";
echo $disabled;
echo ">رهگیری</button><br>\n\n\n";
wp_nonce_field("form-order_tracking", "send-order-tracking");
echo "</form>\n  </div>\n</div>\n<div class=\"novintrack\"></div>\n";
if($head == "checked") {
    echo "\n<script>\n\n  \njQuery(document).ready(function(\$){\n\n\n var form = \$('#ajax-test-form');\n\n    form.submit(function(e) {\n\n  \n    event.preventDefault();\n        // submit the form via Ajax\n        \$.ajax({\n            url: '?',\n            type: form.attr('method'),\n           dataType: 'html',\n            data:form.serialize(),\n            processData: false,\n\n         beforeSend: function() {\n              \$(\"#loading-image\").show();\n          \n        \n           },\n            success: function(result) {\n                ";
    if($captcha_check == "google") {
        echo "           grecaptcha.reset();\n ";
    }
    if($captcha_check == "google3") {
        echo "grecaptcha_execute3();\n";
    }
    echo "      \$('.novintrack').html(\$(result).find(\".novintrackorder\"));\n      //  \$('body').html(result);\n    \$('#capchanovin').click();\n            },\n            complete: function(result) {\n                 \$(\"#loading-image\").hide();\n                \$('html, body').animate({\n                    scrollTop: \$(\"#form-result\").offset().top\n                }, 500);   \n            }\n        });\n    });\n return false;\n});\n</script>\n\n";
}
echo "\n     <style>\n     ol li {\n    list-style: none!important;\n \n}\n.login-pagenovin {\n  width: auto!important;\n  padding: 1% 0 0!important;\n  margin: auto!important;\n}\n.formnovin {\n  position: relative!important;\n  z-index: 1;\n  background: #FFFFFF;\n  max-width: 350px!important;\n  border-radius: 14px!important;\n  margin: 0 auto 100px!important;\n  padding: 28px!important;\n  text-align: center!important;\n  box-shadow: 0 0 20px 0 rgba(0, 0, 0, 0.2), 0 5px 5px 0 rgba(0, 0, 0, 0.24);\n}\n.formnovin input {\n  outline: 0!important;\n  background: #f2f2f2!important;\n  width: 100%!important;\n  border: 0;\n  margin: 0 0 15px!important;\n  padding: 7px!important;\n  box-sizing: border-box!important;\n  font-size: 14px!important;\n}\n.formnovin button {\nborder-radius: 5px!important;\n  outline: 0!important;\n  background:";
echo $color;
echo "!important;;\n  width: 100%!important;\n  border: 0!important;\n  padding: 20px!important;\n  color: #FFFFFF!important;\n  font-size: 14px!important;\n  -webkit-transition: all 0.4 ease;\n  transition: all 0.4 ease;\n  cursor: pointer;\n}\n@media only screen and (min-width : 900px) and (max-width : 4900px) {\n    .formnovin {\n  max-width: 350px!important;\n    }\n    \n}\n.formnovin button:hover,.form button:active,.form button:focus {\n  background: #43A047;\n}\n\n\n \n    .product_counti {\nz-index: 1;\n    top: 3px;\n    right: 3px;\n    border-radius: 6px;\n    background-color: #00bfd6;\n    color: #fff;\n    width: 20px;\n    height: 20px;\n    padding: 0px 6px 0px 7px;\n    font-size: 15px;\n  \n}\n table, td, th {\n  border: 1px solid #ddd!important;\n  text-align: center!important;\n  font-size:14px;\n     \n}\n.progresss .dones .steps,\n.progresss.dones .steps:before,\n.progresss .dones .steps:after,\n.progresss .actives .steps,\n.progresss .actives .steps:before {\n    background-color: ";
echo $navar_color;
echo "!important;\n}\n@media only screen and (min-width : 100px) and (max-width : 900px) {\n    .centnovin{\n        text-align: center!important;\n    }\n    table, td, th {  \nfont-size: 10px!important;\n}\n}\ntable {\n  border-collapse: collapse!important;\n  width: 100%!important;\n}\n td {\n  padding: 6px!important;\n}\nth {\n        width: 1%;\n        background: ";
echo $color_td;
echo "!important;\n  padding: 15px!important;\n  color: ";
echo $text_td;
echo "!important;\n}\n.button-click {\n     background-color: ";
echo $color;
echo "!important;}\n     .text-new{\n         border: 2px solid ";
echo $color;
echo "!important;\n     }\n";
if($chdev == "checked") {
    echo ".progresss {\n    width: 100%!important;\n}\n\n       .iframei{\n        display:none;\n    margin-right: auto!important;\n    margin-left: auto!important;\n    }\n}\n        .block{\n          \n            margin-right: auto!important;\n    margin-left: auto!important;\n        display:block!important;\n    }\n";
} else {
    echo "@media only screen and (min-width : 900px) and (max-width : 4900px) {\n    \n  \n     .iframei{\n        display:none;\n    margin-right: auto!important;\n    margin-left: auto!important;\n    }\n}\n@media only screen and (min-width : 100px) and (max-width : 900px) {\n         .iframei{\n        display:none;\n    margin-right: auto!important;\n    margin-left: auto!important;\n    }\n}\n.progresss {\n    width: 87%;\n}\n\n";
}
echo "</style>\n</html>";
// @function novin_tarah_track_order_form_basteh is protected ioncube.dynamickey encoding key.
function novin_tarah_track_order_form_basteh()
{
}

?>