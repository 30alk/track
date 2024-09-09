jQuery(function ($) {
$(document).ready(function () {
    //logo
    var gateway_icon=$('#woocommerce_cod_gateway_icon').val();
    var gateway_parent=$('#woocommerce_cod_gateway_html').parent();
    gateway_parent.append('<div id="gateway_html"></div>');
    if(gateway_icon != ""){
      //  $('#gateway_html').html('<img style="width: 100px;height: 100px;" src="'+gateway_icon+'">');
    }
    $('#woocommerce_cod_gateway_icon_submit').val('تغییر لوگو');
    var media_uploader = null;
    $('.gateway_upload_icon').on('click',function () {
        media_uploader = wp.media({
            frame:    "post",
            state:    "insert",
            multiple: false
        });

        media_uploader.on("insert", function(){
            var json = media_uploader.state().get("selection").first().toJSON();

            var image_url = json.url;
            var image_caption = json.caption;
            var image_title = json.title;
            $('#woocommerce_cod_gateway_icon').val(image_url);
            $('#gateway_html').html('<img style="width: 100px;height: 100px;" src="'+image_url+'"><input type="hidden"  name="novin_track_order[logo]" value="'+image_url+'">');
        });
        media_uploader.open();
    });
    $('#gateway_html,#woocommerce_cod_gateway_html').on('click',function () {
         
        if(confirm('از حذف آیکون مطمئن هستید؟')){
            $('#gateway_html').remove();
             $('#woocommerce_cod_gateway_html').append('<div id="gateway_html"></div>');
            $('#woocommerce_cod_gateway_icon').val('');
        }
    });
        $('#gateway_html_icon').on('click',function () {
         
        if(confirm('از حذف آیکون مطمئن هستید؟')){
            $('#gateway_html_icon').remove();
             $('#woocommerce_cod_gateway_html').append('<div id="gateway_html"></div>');
            $('#woocommerce_cod_gateway_icon').val('');
        }
    });
});
//logon1
    var gateway_icon=$('#woocommerce_cod_gateway_icon1').val();
    var gateway_parent=$('#woocommerce_cod_gateway_html1').parent();
    gateway_parent.append('<div id="gateway_html1"></div>');
    if(gateway_icon != ""){
      //  $('#gateway_html').html('<img style="width: 100px;height: 100px;" src="'+gateway_icon+'">');
    }
    $('#woocommerce_cod_gateway_icon_submit1').val('آیکون مرحله 1');
    var media_uploader = null;
    $('.gateway_upload_icon1').on('click',function () {
        media_uploader = wp.media({
            frame:    "post",
            state:    "insert",
            multiple: false
        });

        media_uploader.on("insert", function(){
            var json = media_uploader.state().get("selection").first().toJSON();

            var image_url = json.url;
            var image_caption = json.caption;
            var image_title = json.title;
            $('#woocommerce_cod_gateway_icon1').val(image_url);
            $('#gateway_html1').html('<img style="width: 24px;height: 24px;" src="'+image_url+'"><input type="hidden"  name="novin_icon_track[logon1]" value="'+image_url+'">');
        });
        media_uploader.open();
    });
    $('#gateway_html1,#woocommerce_cod_gateway_html1').on('click',function () {
         
        if(confirm('از حذف آیکون مطمئن هستید؟')){
            $('#gateway_html1').remove();
             $('#woocommerce_cod_gateway_html1').append('<div id="gateway_html1"></div>');
            $('#woocommerce_cod_gateway_icon1').val('');
        }
    });
        $('#gateway_html_icon1').on('click',function () {
         
        if(confirm('از حذف آیکون مطمئن هستید؟')){
            $('#gateway_html_icon1').remove();
             $('#woocommerce_cod_gateway_html1').append('<div id="gateway_html1"></div>');
            $('#woocommerce_cod_gateway_icon1').val('');
        }
    });
    //logon2
      var gateway_icon=$('#woocommerce_cod_gateway_icon2').val();
    var gateway_parent=$('#woocommerce_cod_gateway_html2').parent();
    gateway_parent.append('<div id="gateway_html2"></div>');
    if(gateway_icon != ""){
      //  $('#gateway_html').html('<img style="width: 100px;height: 100px;" src="'+gateway_icon+'">');
    }
    $('#woocommerce_cod_gateway_icon_submit2').val('آیکون مرحله 2');
    var media_uploader = null;
    $('.gateway_upload_icon2').on('click',function () {
        media_uploader = wp.media({
            frame:    "post",
            state:    "insert",
            multiple: false
        });

        media_uploader.on("insert", function(){
            var json = media_uploader.state().get("selection").first().toJSON();

            var image_url = json.url;
            var image_caption = json.caption;
            var image_title = json.title;
            $('#woocommerce_cod_gateway_icon2').val(image_url);
            $('#gateway_html2').html('<img style="width: 24px;height: 24px;" src="'+image_url+'"><input type="hidden"  name="novin_icon_track[logon2]" value="'+image_url+'">');
        });
        media_uploader.open();
    });
    $('#gateway_html2,#woocommerce_cod_gateway_html2').on('click',function () {
         
        if(confirm('از حذف آیکون مطمئن هستید؟')){
            $('#gateway_html2').remove();
             $('#woocommerce_cod_gateway_html2').append('<div id="gateway_html2"></div>');
            $('#woocommerce_cod_gateway_icon2').val('');
        }
    });
        $('#gateway_html_icon2').on('click',function () {
         
        if(confirm('از حذف آیکون مطمئن هستید؟')){
            $('#gateway_html_icon2').remove();
             $('#woocommerce_cod_gateway_html2').append('<div id="gateway_html2"></div>');
            $('#woocommerce_cod_gateway_icon2').val('');
        }
    });
    //logon3
          var gateway_icon=$('#woocommerce_cod_gateway_icon3').val();
    var gateway_parent=$('#woocommerce_cod_gateway_html3').parent();
    gateway_parent.append('<div id="gateway_html3"></div>');
    if(gateway_icon != ""){
      //  $('#gateway_html').html('<img style="width: 100px;height: 100px;" src="'+gateway_icon+'">');
    }
    $('#woocommerce_cod_gateway_icon_submit3').val('آیکون مرحله 3');
    var media_uploader = null;
    $('.gateway_upload_icon3').on('click',function () {
        media_uploader = wp.media({
            frame:    "post",
            state:    "insert",
            multiple: false
        });

        media_uploader.on("insert", function(){
            var json = media_uploader.state().get("selection").first().toJSON();

            var image_url = json.url;
            var image_caption = json.caption;
            var image_title = json.title;
            $('#woocommerce_cod_gateway_icon3').val(image_url);
            $('#gateway_html3').html('<img style="width: 24px;height: 24px;" src="'+image_url+'"><input type="hidden"  name="novin_icon_track[logon3]" value="'+image_url+'">');
        });
        media_uploader.open();
    });
    $('#gateway_html3,#woocommerce_cod_gateway_html3').on('click',function () {
         
        if(confirm('از حذف آیکون مطمئن هستید؟')){
            $('#gateway_html3').remove();
             $('#woocommerce_cod_gateway_html3').append('<div id="gateway_html3"></div>');
            $('#woocommerce_cod_gateway_icon3').val('');
        }
    });
        $('#gateway_html_icon3').on('click',function () {
         
        if(confirm('از حذف آیکون مطمئن هستید؟')){
            $('#gateway_html_icon3').remove();
             $('#woocommerce_cod_gateway_html3').append('<div id="gateway_html3"></div>');
            $('#woocommerce_cod_gateway_icon3').val('');
        }
    });
    //logon4
              var gateway_icon=$('#woocommerce_cod_gateway_icon4').val();
    var gateway_parent=$('#woocommerce_cod_gateway_html4').parent();
    gateway_parent.append('<div id="gateway_html4"></div>');
    if(gateway_icon != ""){
      //  $('#gateway_html').html('<img style="width: 100px;height: 100px;" src="'+gateway_icon+'">');
    }
    $('#woocommerce_cod_gateway_icon_submit4').val('آیکون مرحله 4');
    var media_uploader = null;
    $('.gateway_upload_icon4').on('click',function () {
        media_uploader = wp.media({
            frame:    "post",
            state:    "insert",
            multiple: false
        });

        media_uploader.on("insert", function(){
            var json = media_uploader.state().get("selection").first().toJSON();

            var image_url = json.url;
            var image_caption = json.caption;
            var image_title = json.title;
            $('#woocommerce_cod_gateway_icon4').val(image_url);
            $('#gateway_html4').html('<img style="width: 24px;height: 24px;" src="'+image_url+'"><input type="hidden"  name="novin_icon_track[logon4]" value="'+image_url+'">');
        });
        media_uploader.open();
    });
    $('#gateway_html4,#woocommerce_cod_gateway_html4').on('click',function () {
         
        if(confirm('از حذف آیکون مطمئن هستید؟')){
            $('#gateway_html4').remove();
             $('#woocommerce_cod_gateway_html4').append('<div id="gateway_html4"></div>');
            $('#woocommerce_cod_gateway_icon4').val('');
        }
    });
        $('#gateway_html_icon4').on('click',function () {
         
        if(confirm('از حذف آیکون مطمئن هستید؟')){
            $('#gateway_html_icon4').remove();
             $('#woocommerce_cod_gateway_html4').append('<div id="gateway_html4"></div>');
            $('#woocommerce_cod_gateway_icon4').val('');
        }
    });
    //logon5
              var gateway_icon=$('#woocommerce_cod_gateway_icon5').val();
    var gateway_parent=$('#woocommerce_cod_gateway_html5').parent();
    gateway_parent.append('<div id="gateway_html5"></div>');
    if(gateway_icon != ""){
      //  $('#gateway_html').html('<img style="width: 100px;height: 100px;" src="'+gateway_icon+'">');
    }
    $('#woocommerce_cod_gateway_icon_submit5').val('آیکون مرحله 5');
    var media_uploader = null;
    $('.gateway_upload_icon5').on('click',function () {
        media_uploader = wp.media({
            frame:    "post",
            state:    "insert",
            multiple: false
        });

        media_uploader.on("insert", function(){
            var json = media_uploader.state().get("selection").first().toJSON();

            var image_url = json.url;
            var image_caption = json.caption;
            var image_title = json.title;
            $('#woocommerce_cod_gateway_icon5').val(image_url);
            $('#gateway_html5').html('<img style="width: 24px;height: 24px;" src="'+image_url+'"><input type="hidden"  name="novin_icon_track[logon5]" value="'+image_url+'">');
        });
        media_uploader.open();
    });
    $('#gateway_html5,#woocommerce_cod_gateway_html5').on('click',function () {
         
        if(confirm('از حذف آیکون مطمئن هستید؟')){
            $('#gateway_html5').remove();
             $('#woocommerce_cod_gateway_html5').append('<div id="gateway_html5"></div>');
            $('#woocommerce_cod_gateway_icon5').val('');
        }
    });
        $('#gateway_html_icon5').on('click',function () {
         
        if(confirm('از حذف آیکون مطمئن هستید؟')){
            $('#gateway_html_icon5').remove();
             $('#woocommerce_cod_gateway_html5').append('<div id="gateway_html5"></div>');
            $('#woocommerce_cod_gateway_icon5').val('');
        }
    });
    //diginovin1
     var gateway_icon=$('#woocommerce_cod_gateway_icon6').val();
    var gateway_parent=$('#woocommerce_cod_gateway_html6').parent();
    gateway_parent.append('<div id="gateway_html6"></div>');
    if(gateway_icon != ""){
      //  $('#gateway_html').html('<img style="width: 100px;height: 100px;" src="'+gateway_icon+'">');
    }
    $('#woocommerce_cod_gateway_icon_submit6').val('آیکون مرحله 1');
    var media_uploader = null;
    $('.gateway_upload_icon6').on('click',function () {
        media_uploader = wp.media({
            frame:    "post",
            state:    "insert",
            multiple: false
        });

        media_uploader.on("insert", function(){
            var json = media_uploader.state().get("selection").first().toJSON();

            var image_url = json.url;
            var image_caption = json.caption;
            var image_title = json.title;
            $('#woocommerce_cod_gateway_icon6').val(image_url);
            $('#gateway_html6').html('<img style="width: 120px;height: 120px;" src="'+image_url+'"><input type="hidden"  name="novin_icon_track[logon6]" value="'+image_url+'">');
        });
        media_uploader.open();
    });
    $('#gateway_html6,#woocommerce_cod_gateway_html6').on('click',function () {
         
        if(confirm('از حذف آیکون مطمئن هستید؟')){
            $('#gateway_html6').remove();
             $('#woocommerce_cod_gateway_html6').append('<div id="gateway_html6"></div>');
            $('#woocommerce_cod_gateway_icon6').val('');
        }
    });
        $('#gateway_html_icon6').on('click',function () {
         
        if(confirm('از حذف آیکون مطمئن هستید؟')){
            $('#gateway_html_icon6').remove();
             $('#woocommerce_cod_gateway_html6').append('<div id="gateway_html6"></div>');
            $('#woocommerce_cod_gateway_icon6').val('');
        }
    });
    //diginovin2
                    var gateway_icon=$('#woocommerce_cod_gateway_icon7').val();
    var gateway_parent=$('#woocommerce_cod_gateway_html7').parent();
    gateway_parent.append('<div id="gateway_html7"></div>');
    if(gateway_icon != ""){
      //  $('#gateway_html').html('<img style="width: 100px;height: 100px;" src="'+gateway_icon+'">');
    }
    $('#woocommerce_cod_gateway_icon_submit7').val('آیکون مرحله 2');
    var media_uploader = null;
    $('.gateway_upload_icon7').on('click',function () {
        media_uploader = wp.media({
            frame:    "post",
            state:    "insert",
            multiple: false
        });

        media_uploader.on("insert", function(){
            var json = media_uploader.state().get("selection").first().toJSON();

            var image_url = json.url;
            var image_caption = json.caption;
            var image_title = json.title;
            $('#woocommerce_cod_gateway_icon7').val(image_url);
            $('#gateway_html7').html('<img style="width: 120px;height: 120px;" src="'+image_url+'"><input type="hidden"  name="novin_icon_track[logon7]" value="'+image_url+'">');
        });
        media_uploader.open();
    });
    $('#gateway_html7,#woocommerce_cod_gateway_html7').on('click',function () {
         
        if(confirm('از حذف آیکون مطمئن هستید؟')){
            $('#gateway_html7').remove();
             $('#woocommerce_cod_gateway_html7').append('<div id="gateway_html7"></div>');
            $('#woocommerce_cod_gateway_icon7').val('');
        }
    });
        $('#gateway_html_icon7').on('click',function () {
         
        if(confirm('از حذف آیکون مطمئن هستید؟')){
            $('#gateway_html_icon7').remove();
             $('#woocommerce_cod_gateway_html7').append('<div id="gateway_html7"></div>');
            $('#woocommerce_cod_gateway_icon7').val('');
        }
    });
    //digi3
                        var gateway_icon=$('#woocommerce_cod_gateway_icon8').val();
    var gateway_parent=$('#woocommerce_cod_gateway_html8').parent();
    gateway_parent.append('<div id="gateway_html8"></div>');
    if(gateway_icon != ""){
      //  $('#gateway_html').html('<img style="width: 100px;height: 100px;" src="'+gateway_icon+'">');
    }
    $('#woocommerce_cod_gateway_icon_submit8').val('آیکون مرحله 3');
    var media_uploader = null;
    $('.gateway_upload_icon8').on('click',function () {
        media_uploader = wp.media({
            frame:    "post",
            state:    "insert",
            multiple: false
        });

        media_uploader.on("insert", function(){
            var json = media_uploader.state().get("selection").first().toJSON();

            var image_url = json.url;
            var image_caption = json.caption;
            var image_title = json.title;
            $('#woocommerce_cod_gateway_icon8').val(image_url);
            $('#gateway_html8').html('<img style="width: 120px;height: 120px;" src="'+image_url+'"><input type="hidden"  name="novin_icon_track[logon8]" value="'+image_url+'">');
        });
        media_uploader.open();
    });
    $('#gateway_html8,#woocommerce_cod_gateway_html8').on('click',function () {
         
        if(confirm('از حذف آیکون مطمئن هستید؟')){
            $('#gateway_html8').remove();
             $('#woocommerce_cod_gateway_html8').append('<div id="gateway_html8"></div>');
            $('#woocommerce_cod_gateway_icon8').val('');
        }
    });
        $('#gateway_html_icon8').on('click',function () {
         
        if(confirm('از حذف آیکون مطمئن هستید؟')){
            $('#gateway_html_icon8').remove();
             $('#woocommerce_cod_gateway_html8').append('<div id="gateway_html8"></div>');
            $('#woocommerce_cod_gateway_icon8').val('');
        }
    });
    //diginovin4
                        var gateway_icon=$('#woocommerce_cod_gateway_icon9').val();
    var gateway_parent=$('#woocommerce_cod_gateway_html9').parent();
    gateway_parent.append('<div id="gateway_html9"></div>');
    if(gateway_icon != ""){
      //  $('#gateway_html').html('<img style="width: 100px;height: 100px;" src="'+gateway_icon+'">');
    }
    $('#woocommerce_cod_gateway_icon_submit9').val('آیکون مرحله 4');
    var media_uploader = null;
    $('.gateway_upload_icon9').on('click',function () {
        media_uploader = wp.media({
            frame:    "post",
            state:    "insert",
            multiple: false
        });

        media_uploader.on("insert", function(){
            var json = media_uploader.state().get("selection").first().toJSON();

            var image_url = json.url;
            var image_caption = json.caption;
            var image_title = json.title;
            $('#woocommerce_cod_gateway_icon9').val(image_url);
            $('#gateway_html9').html('<img style="width: 120px;height: 120px;" src="'+image_url+'"><input type="hidden"  name="novin_icon_track[logon9]" value="'+image_url+'">');
        });
        media_uploader.open();
    });
    $('#gateway_html9,#woocommerce_cod_gateway_html9').on('click',function () {
         
        if(confirm('از حذف آیکون مطمئن هستید؟')){
            $('#gateway_html9').remove();
             $('#woocommerce_cod_gateway_html9').append('<div id="gateway_html9"></div>');
            $('#woocommerce_cod_gateway_icon9').val('');
        }
    });
        $('#gateway_html_icon9').on('click',function () {
         
        if(confirm('از حذف آیکون مطمئن هستید؟')){
            $('#gateway_html_icon9').remove();
             $('#woocommerce_cod_gateway_html9').append('<div id="gateway_html9"></div>');
            $('#woocommerce_cod_gateway_icon9').val('');
        }
    });
    //diginovin 5
                            var gateway_icon=$('#woocommerce_cod_gateway_icon10').val();
    var gateway_parent=$('#woocommerce_cod_gateway_html10').parent();
    gateway_parent.append('<div id="gateway_html10"></div>');
    if(gateway_icon != ""){
      //  $('#gateway_html').html('<img style="width: 100px;height: 100px;" src="'+gateway_icon+'">');
    }
    $('#woocommerce_cod_gateway_icon_submit10').val('آیکون مرحله 5');
    var media_uploader = null;
    $('.gateway_upload_icon10').on('click',function () {
        media_uploader = wp.media({
            frame:    "post",
            state:    "insert",
            multiple: false
        });

        media_uploader.on("insert", function(){
            var json = media_uploader.state().get("selection").first().toJSON();

            var image_url = json.url;
            var image_caption = json.caption;
            var image_title = json.title;
            $('#woocommerce_cod_gateway_icon10').val(image_url);
            $('#gateway_html10').html('<img style="width: 120px;height: 120px;" src="'+image_url+'"><input type="hidden"  name="novin_icon_track[logon10]" value="'+image_url+'">');
        });
        media_uploader.open();
    });
    $('#gateway_html10,#woocommerce_cod_gateway_html10').on('click',function () {
         
        if(confirm('از حذف آیکون مطمئن هستید؟')){
            $('#gateway_html10').remove();
             $('#woocommerce_cod_gateway_html10').append('<div id="gateway_html10"></div>');
            $('#woocommerce_cod_gateway_icon10').val('');
        }
    });
        $('#gateway_html_icon10').on('click',function () {
         
        if(confirm('از حذف آیکون مطمئن هستید؟')){
            $('#gateway_html_icon10').remove();
             $('#woocommerce_cod_gateway_html10').append('<div id="gateway_html10"></div>');
            $('#woocommerce_cod_gateway_icon10').val('');
        }
    });
});
