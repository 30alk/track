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
require_once "novin-jdate.php";
if(!class_exists("novin_license")) {
    class novin_license
    {
        public static $check_url = "http://guard.zhaket.com/api/";
        public function __construct()
        {
        }
        public static function sendRequest($method, $params = [])
        {
            $param_string = http_build_query($params);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, self::$check_url . $method . "?" . $param_string);
            $content = curl_exec($ch);
            return json_decode($content);
        }
        public static function isValid($license_token)
        {
            $result = self::sendRequest("validation-license", ["token" => $license_token, "domain" => self::getHost()]);
            return $result;
        }
        public static function install($license_token, $product_token)
        {
            $result = self::sendRequest("install-license", ["product_token" => $product_token, "token" => $license_token, "domain" => self::getHost()]);
            return $result;
        }
        public static function getHost()
        {
            $possibleHostSources = ["HTTP_X_FORWARDED_HOST", "HTTP_HOST", "SERVER_NAME", "SERVER_ADDR"];
            $sourceTransformations = ["HTTP_X_FORWARDED_HOST" => function ($value) {
                $elements = explode(",", $value);
                return trim(end($elements));
            }];
            $host = "";
            foreach ($possibleHostSources as $source) {
                if(!empty($host)) {
                    $host = preg_replace("/:\\d+\$/", "", $host);
                    $host = str_ireplace("www.", "", $host);
                    return trim($host);
                }
                if(!empty($_SERVER[$source])) {
                    $host = $_SERVER[$source];
                    if(array_key_exists($source, $sourceTransformations)) {
                        $host = $sourceTransformations[$source]($host);
                    }
                }
            }
        }
    }
}
$setting = get_option("novin_track_order");
$day = isset($setting["timeintvl"]) ? esc_attr($setting["timeintvl"]) : "";
$active = isset($setting["checkintvl"]) ? esc_attr($setting["checkintvl"]) : "";
if(!empty($day) && $active == "checked" && !wp_next_scheduled("novin_change_status_order_completed_to_deliver")) {
    wp_schedule_event(time(), "daily", "novin_change_status_order_completed_to_deliver");
} elseif(empty($day)) {
    wp_clear_scheduled_hook("novin_change_status_order_completed_to_deliver");
}
if(!empty($day) && $active == "checked") {
    add_action("novin_change_status_order_completed_to_deliver", "auto_change_status_novin_deliver");
}
$setting = get_option("novin_track_order");
$calender = isset($setting["calender"]) ? esc_attr($setting["calender"]) : "";
if($calender == "checked") {
    add_action("admin_print_styles-post.php", "novin_metabox_load_plugi_js", 10, 1);
    add_action("admin_print_styles-post-new.php", "novin_metabox_load_plugi_js", 10, 1);
}
add_action("admin_enqueue_scripts", "wp_admin_order_status_novin");
$license_option = get_option("novin_order");
$setting = get_option("novin_track_order");
$license = isset($license_option["title"]) ? esc_attr($license_option["title"]) : "";
if(!empty($license)) {
    $dokan = isset($setting["dokan"]) ? esc_attr($setting["dokan"]) : "";
    if($dokan == "checked") {
        add_action("wp_ajax_novin_send_tracking_dokan", "novin_seller_doakan_save_tracking");
        add_action("dokan_order_detail_after_order_items", "novin_dokan_order_details_after_customer");
        add_filter("dokan_query_var_filter", "novin_dokan_load_document_menu");
        add_filter("dokan_get_dashboard_nav", "novin_dokan_add_help_menu");
        add_action("dokan_load_custom_template", "novin_dokan_load_template");
    }
    add_action("woocommerce_order_status_changed", "novin_track_order_change_status_tapin", 10, 4);
    add_action("woocommerce_process_shop_order_meta", "novin_change_status_checkbox_admin", 99, 1);
    add_filter("dokan_get_order_status_class", "novin_dokan_add_custom_order_status_button_class", 10, 2);
    add_filter("dokan_get_order_status_translated", "novin_dokan_add_custom_order_status_translated", 10, 2);
    add_action("dokan_order_listing_header_before_action_column", "novin_bulk_dokan_status");
    add_shortcode("order-tracking-code", "novin_order_tracking_shortcode");
    $chdev = isset($setting["ch_dev"]) ? esc_attr($setting["ch_dev"]) : "";
    $sehat_check = isset($setting["sehat_check"]) ? esc_attr($setting["sehat_check"]) : "";
    if($chdev == "checked" && $sehat_check != "checked") {
        add_filter("woocommerce_my_account_my_orders_actions", "novin_add_my_account_my_orders_deliver_actions", 10, 2);
    }
    if(class_exists("WeDevs_Dokan")) {
        add_filter("woocommerce_admin_order_actions", "baste_icon_dokan_balk", 10, 2);
        add_action("wp_ajax_dokan-mark-order-box", "novin_dokan_box_order");
        add_filter("woocommerce_admin_order_actions", "deliver_icon_dokan_balk", 10, 2);
        add_action("wp_ajax_dokan-mark-order-deliver", "novin_dokan_deliver_order");
    }
    add_filter("dokan_bulk_order_statuses", "baste_dokan_bulk_actions_edit_product");
    if($chdev == "checked") {
        add_action("init", "register_deliver_order_statuses");
        add_filter("bulk_actions-woocommerce_page_wc-orders", "deliver_bulk_actions_edit_product");
        add_filter("dokan_bulk_order_statuses", "deliver_dokan_bulk_actions_edit_product");
        if(get_option("woocommerce_custom_orders_table_enabled") != "yes") {
            add_filter("bulk_actions-edit-shop_order", "deliver_bulk_actions_edit_product", 20, 2);
        }
        add_filter("wc_order_statuses", "deliver_new_wc_order_statuses");
    }
    add_action("template_redirect", "novin_action_deliver_order_status");
    add_action("admin_bar_menu", "novin_code_toolbar_rahgir", 100);
    if(is_admin()) {
        add_filter("woocommerce_reports_order_statuses", "include_novin_order_status_to_reports", 20, 1);
    }
    $icon_user = isset($setting["icon_user"]) ? esc_attr($setting["icon_user"]) : "";
    add_action("woocommerce_admin_order_data_after_shipping_address", "novin_display_order_data_in_admin");
    $after_before = isset($setting["after_before"]) ? esc_attr($setting["after_before"]) : "";
    if($after_before == "after") {
        add_action("woocommerce_order_details_after_order_table", "view_order_novin_payment_instruction", 5, 1);
    } elseif($after_before == "before") {
        add_action("woocommerce_order_details_before_order_table", "view_order_novin_payment_instruction", 5, 1);
    } else {
        add_action("woocommerce_order_details_after_order_table", "view_order_novin_payment_instruction", 5, 1);
    }
    add_action("woocommerce_process_shop_order_meta", "novin_data_save_general_details", 10, 3);
    add_filter("persian_woo_sms_content", "novin_track_order_replace_shortcodem", 99, 4);
    add_action("woocommerce_email_after_order_table", "add_order_email_instructio_track_order", 10, 2);
    add_action("add_meta_boxes", "novin_tracking_box");
    add_filter("manage_woocommerce_page_wc-orders_columns", "novin_order_items_column");
    add_action("manage_woocommerce_page_wc-orders_custom_column", "novin_track_admin_order_items_column_icon", 20, 2);
    if(get_option("woocommerce_custom_orders_table_enabled") != "yes") {
        add_filter("manage_edit-shop_order_columns", "novin_order_items_column");
        add_action("manage_shop_order_posts_custom_column", "novin_track_admin_order_items_column_icon", 20, 2);
    }
    if($icon_user != "checked") {
        add_action("woocommerce_my_account_my_orders_column_custom-column", "novin_user_custom_column_icon");
        add_filter("woocommerce_account_orders_columns", "novin_track_add_account_orders_column", 10, 1);
    }
    add_filter("wc_order_statuses", "baste_new_wc_order_statuses");
    add_filter("bulk_actions-woocommerce_page_wc-orders", "baste_bulk_actions_edit_product");
    if(get_option("woocommerce_custom_orders_table_enabled") != "yes") {
        add_filter("bulk_actions-edit-shop_order", "baste_bulk_actions_edit_product", 20, 1);
    }
    add_action("init", "register_baste_order_statuses");
}
add_action("wp_enqueue_scripts", "novin_track_order_load_plugin_css");
$captcha_check = isset($setting["captcha"]) ? esc_attr($setting["captcha"]) : "";
if($captcha_check == "captcha") {
    add_action("init", "register_my_session_novin_captcha");
    add_action("wp_loaded", "close_my_session_novin_captcha", 30);
}
add_action("admin_init", "check_validate_license_ordertracking");
if(is_admin()) {
    $my_settings_page = new NovinOrderTracking();
}
class NovinOrderTracking
{
    protected $rahgir;
    protected $options;
    protected $produc_token = "843f49a5-4cfc-4a18-910e-a2c8ebb329b1";
    public function __construct()
    {
        $this->options = get_option("novin_order");
        $this->rahgir = get_option("novin_rahgir");
        add_action("admin_menu", [$this, "order_tracking_plugin_page"]);
        add_action("admin_init", [$this, "page_init_track_order"]);
        add_action("admin_notices", [$this, "sample_admin_notice_success"]);
    }
    public function sample_admin_notice_success()
    {
        $default_tab = NULL;
        $tab = isset($_GET["tab"]) ? $_GET["tab"] : $default_tab;
        $count = isset($this->rahgir["count"]) ? esc_attr($this->rahgir["count"]) : "";
        $updated = isset($_GET["settings-updated"]) ? $_GET["settings-updated"] : $default_tab;
        if($tab == "rahgir" && $updated && !empty($count) && $count != 0) {
            echo "    <div class=\"notice notice-success is-dismissible\">\r\n        <p>";
            _e($count . " مورد با موفقیت آپلود شد", "sample-text-domain");
            echo "</p>\r\n    </div>\r\n    ";
        }
    }
    public function novin_track_wp_admin_style()
    {
        wp_enqueue_script("novin-icon-track", order_traking_assets_url . "js/upload.js");
    }
    public function order_tracking_plugin_page()
    {
        $hook = add_menu_page("پیگیری سفارشات", "پیگیری سفارشات", "manage_options", "novin-setting-ordertracking", [$this, "create_admin_page_order_tracking"], order_traking_assets_url . "logo/truck.png", "40");
        wp_register_style("select2_css_track_order", order_traking_assets_url . "css/select2.css", true);
        wp_register_script("select2_track_order", order_traking_assets_url . "js/select2.js", ["jquery"], true);
        wp_enqueue_style("select2_css_track_order");
        wp_enqueue_script("select2_track_order");
        add_action("admin_print_styles-" . $hook, "novin_metabox_load_plugi_js");
        add_action("admin_print_scripts-" . $hook, "novin_metabox_load_plugi_js");
        add_action("admin_print_scripts-" . $hook, [$this, "novin_track_wp_admin_style"]);
    }
    public function create_admin_page_order_tracking()
    {
        $this->setting = get_option("novin_track_order");
        $this->icon = get_option("novin_icon_track");
        $default_tab = NULL;
        $tab = isset($_GET["tab"]) ? $_GET["tab"] : $default_tab;
        echo "         <div style=\"font-family: bbyekan!important\" class=\"wrap\">\r\n            \r\n            <h1>به افزونه پیگیری سفارشات ووکامرس خوش آمدید</h1>\r\n   <nav class=\"nav-tab-wrapper\">\r\n       ";
        $licensecheck = isset($this->options["title"]) ? esc_attr($this->options["title"]) : "";
        if(!empty($licensecheck)) {
            echo "      <a href=\"?page=novin-setting-ordertracking\" class=\"nav-tab ";
            if($tab === NULL) {
                echo "nav-tab-active";
            }
            echo "\">تنظیمات </a>\r\n      <div id=\"rahgir\">\r\n            <a href=\"?page=novin-setting-ordertracking&tab=rahgir&currentpage=1\"  class=\"nav-tab ";
            if($tab === "rahgir") {
                echo "nav-tab-active";
            }
            echo "\">درج کد رهگیری</a></div>\r\n      ";
        }
        echo "      <a href=\"?page=novin-setting-ordertracking&tab=license\" class=\"nav-tab ";
        if($tab === "license") {
            echo "nav-tab-active";
        }
        echo "\">لایسنس</a>\r\n                   <a href=\"?page=novin-setting-ordertracking&tab=icon\" class=\"nav-tab ";
        if($tab === "icon") {
            echo "nav-tab-active";
        }
        echo "\">تغییر آیکن</a>\r\n\r\n           <a href=\"?page=novin-setting-ordertracking&tab=product\" class=\"nav-tab ";
        if($tab === "product") {
            echo "nav-tab-active";
        }
        echo "\">دیگر محصولات ما</a>\r\n\r\n  \r\n    </nav>\r\n    <style>\r\n                          @font-face {\r\n\r\nfont-family: bbyekan;\r\nsrc: url(";
        echo order_traking_assets_url . "fonts/Yekan/Yekan.eot";
        echo ");\r\nsrc: url(";
        echo order_traking_assets_url . "fonts/Yekan/Yekan.eot?#iefix";
        echo ") format(\"embedded-opentype\"),\r\nurl(";
        echo order_traking_assets_url . "fonts/Yekan/Yekan.woff";
        echo ") format(\"woff\"),\r\nurl(";
        echo order_traking_assets_url . "fonts/Yekan/Yekan.ttf";
        echo ") format(\"truetype\"),\r\nurl(";
        echo order_traking_assets_url . "fonts/Yekan/Yekan.svg#BYekan";
        echo ") format(\"svg\");\r\n\r\nfont-style: normal\r\n}\r\n    #wpfooter p {\r\n\r\n    display: none!important;\r\n    }\r\n        #footer-thankyou {\r\n\r\n    display: none!important;\r\n}\r\nh2,h1,p,body{\r\n font-family: bbyekan!important;\r\n}\r\n    </style>\r\n    <br>\r\n    ";
        if($tab === NULL || $tab === "icon" || $tab === "rahgir") {
            echo "\r\n<div id=\"suc\" style=\"font-family: bbyekan\">\r\n<p>اطلاعات با موفقیت ذخیره شد</p></div>\r\n<style>\r\nhr{\r\n    border: 0;\r\n    border-top: 1px solid #84c0f0!important;\r\n    border-bottom: 1px solid #f1f1f1!important;\r\n}\r\n  .button {\r\n  background-color: #4CAF50; /* Green */\r\n  border: none;\r\n  color: white;\r\n  padding: 15px 32px;\r\n  text-align: center;\r\n  text-decoration: none;\r\n  display: inline-block;\r\n  font-size: 16px;\r\n  margin: 4px 2px;\r\n  cursor: pointer;\r\n}\r\n\r\n.button2 {\r\n  background-color: white; \r\n  color: black; \r\n  border: 2px solid #008CBA;\r\n}\r\n    #suc {\r\nposition:fixed;\r\ndisplay:none;\r\nleft:3px;\r\nright:auto;\r\ncolor:#fff;\r\nbottom:3px;\r\nz-index:9999;\r\nwidth:250px;\r\nheight:50px;\r\nbackground:green;\r\nfont-size: 15px;\r\n\r\n    border-radius: 15px;\r\n    text-align: center;\r\n}\r\n</style>\r\n";
        }
        if($tab === "product") {
            echo "     <style>\r\n.card {\r\n    box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2);\r\n    max-width: 300px;\r\n    margin: auto;\r\n    border-radius: 6px;\r\n    display: table-cell;\r\n    margin-left: 18px;\r\n    text-align: center;\r\n    float: right;\r\n  margin-top: 11px;\r\n}\r\n\r\n.price {\r\n    color: #F44336;\r\n    font-weight: 700;\r\n    font-size: 22px;\r\n}\r\n\r\n.card button {\r\n  border: none;\r\n  \r\n  border-radius: 7px;\r\n  outline: 0;\r\n  padding: 12px;\r\n  color: white;\r\n  background-color: #1ebf24;\r\n  text-align: center;\r\n  cursor: pointer;\r\n  width: 100%;\r\n  font-size: 18px;\r\n}\r\n.tit{\r\n     font-size: 18px!important;\r\n}\r\n.card button:hover {\r\n  opacity: 0.7;\r\n  background-color:red;\r\n}\r\nh1{\r\n  font-family: bbyekan!important;  \r\n}\r\n\r\n</style>\r\n</head>\r\n<body style=\"font-family: bbyekan!important\">\r\n\r\n<h2 style=\"font-family: bbyekan!important\" style=\"text-align:center\"></h2>\r\n<div class=\"card\">\r\n  <img src=\"https://cdn.zhaket.com/resources/5dca1ab9eaec370009207b46/61beddc3ffa14e34db06b842.jpg\" alt=\"Denim Jeans\" style=\"width:100%\">\r\n  <h1 class=\"tit\">حمل و نقل پیشرفته ووکامرس</h1>\r\n  <p class=\"price\">199000 تومان</p>\r\n  <p>افزونه کارت به کارت فراز </p>\r\n <p><a href=\"https://www.zhaket.com/web/masir-shipping-wordpress-plugin/?affid=AF-6132dc3e42913\" target=\"_blank\"><button>مشاهده محصول</button></p></a>\r\n</div>\r\n<div class=\"card\">\r\n  <img src=\"https://cdn.zhaket.com/resources/5dca1ab9eaec370009207b46/614b758c1138b04d535655ea.png\" alt=\"Denim Jeans\" style=\"width:100%\">\r\n  <h1 class=\"tit\">افزونه ثبت و پیگیری گارانتی</h1>\r\n  <p class=\"price\">159000 تومان</p>\r\n  <p>افزونه پیگیری گارانتی سریر</p>\r\n <p><a href=\"https://www.zhaket.com/web/sarir-warranty-wordpress-plugin/?affid=AF-6132dc3e42913\" target=\"_blank\"><button>مشاهده محصول</button></p></a>\r\n</div>\r\n    <div class=\"card\">\r\n  <img src=\"https://cdn.zhaket.com/resources/5dc9fbf3eaec370009205b97/6145ed02e1711702c06538f8.png\" alt=\"Denim Jeans\" style=\"width:100%\">\r\n  <h1 class=\"tit\">افزونه تحویل حضوری ووکامرس</h1>\r\n  <p class=\"price\">129000 تومان</p>\r\n  <p>افزونه تحویل حضوری نسیم </p>\r\n <p><a href=\"https://www.zhaket.com/web/nasim-in-person-delivery-woocommerce-plugin/?affid=AF-6132dc3e42913\" target=\"_blank\"><button>مشاهده محصول</button></p></a>\r\n</div>\r\n<div class=\"card\">\r\n  <img src=\"https://cdn.zhaket.com/resources/5dc9fbf3eaec370009205b97/60c5fff189c7da730d02bf1a.jpg\" alt=\"Denim Jeans\" style=\"width:100%\">\r\n  <h1 class=\"tit\">کارت به کارت پیشرفته ووکامرس</h1>\r\n  <p class=\"price\">99000 تومان</p>\r\n  <p>افزونه کارت به کارت فراز </p>\r\n <p><a href=\"https://www.zhaket.com/web/bank-transfer-payment-method-woocommerce-plugin/?affid=AF-6132dc3e42913\" target=\"_blank\"><button>مشاهده محصول</button></p></a>\r\n</div>\r\n<div class=\"card\">\r\n  <img src=\"https://cdn.zhaket.com/resources/5dc9fbf3eaec370009205b97/603dde4114c8eb193f1e72a8.jpg\" alt=\"Denim Jeans\" style=\"width:100%\">\r\n  <h1 class=\"tit\">زمان تحویل کالای ووکامرس</h1>\r\n  <p class=\"price\">129000 تومان</p>\r\n  <p >افزونه زمان تحویل کالا ماهان </p>\r\n <p><a href=\"https://www.zhaket.com/web/woocommerce-delivery-time-plugin/?affid=AF-6132dc3e42913\" target=\"_blank\"><button>مشاهده محصول</button></p></a>\r\n</div>\r\n<div class=\"card\">\r\n  <img src=\"https://cdn.zhaket.com/resources/5dc9fbf3eaec370009205b97/604dfe1d929f7f1a535cc7bb.jpg\" alt=\"پرداخت در محل\" style=\"width:100%\">\r\n  <h1 class=\"tit\">پرداخت در محل پیشرفته</h1>\r\n  <p class=\"price\">129000 تومان</p>\r\n  <p >افزونه پرداخت در محل پیشرفته </p>\r\n <p><a href=\"https://www.zhaket.com/web/woocommerce-advanced-cash-on-delivery-plugin/?affid=AF-6132dc3e42913\" target=\"_blank\"><button>مشاهده محصول</button></p></a>\r\n</div>\r\n<div class=\"card\">\r\n  <img src=\"https://cdn.zhaket.com/resources/5dca1ab9eaec370009207b46/5f7ca14c1eba3801787c0a34.png\" alt=\"Denim Jeans\" style=\"width:100%\">\r\n  <h1 class=\"tit\">کد تخفیف به ازای معرف</h1>\r\n  <p class=\"price\">99000 تومان</p>\r\n  <p >افزونه ارائه کد تخفیف در ازای معرفی کاربر </p>\r\n <p><a href=\"https://www.zhaket.com/web/novin-refferal-discount-code-plugin/?affid=AF-6132dc3e42913\" target=\"_blank\"><button>مشاهده محصول</button></p></a>\r\n</div>\r\n<div class=\"card\">\r\n  <img src=\"https://cdn.zhaket.com/resources/5dc9fbf3eaec370009205b97/5f6f3f936395b510906c11aa.png\" alt=\"Denim Jeans\" style=\"width:100%\">\r\n  <h1 class=\"tit\">تایید سفارش و استعلام قیمت</h1>\r\n  <p class=\"price\">129000 تومان</p>\r\n  <p>افزونه تایید سفارشات ووکامرس</p>\r\n  <p><a href=\"https://www.zhaket.com/web/novin-order-approval/?affid=AF-6132dc3e42913\" target=\"_blank\"><button>مشاهده محصول</button></p></a>\r\n</div>\r\n<div class=\"card\">\r\n  <img src=\"https://cdn.zhaket.com/resources/5dca1ab9eaec370009207b46/5f3f75fb58f85d0f36224065.png\" alt=\"Denim Jeans\" style=\"width:100%\">\r\n  <h1 class=\"tit\">یاد آوری پرداخت</h1>\r\n  <p class=\"price\">129000 تومان</p>\r\n  <p>افزونه یاد آوری پرداخت ووکامرس | novin</p>\r\n  <p><a href=\"https://www.zhaket.com/web/novin-woocommerce-payment-reminder/?affid=AF-6132dc3e42913\" target=\"_blank\"><button>مشاهده محصول</button></p></a>\r\n</div><br>\r\n<div class=\"card\">\r\n  <img src=\"https://cdn.zhaket.com/resources/5dca1ab9eaec370009207b46/5f7f363435201908417da4f2.png\" alt=\"Denim Jeans\" style=\"width:100%\">\r\n  <h1 class=\"tit\">تبریک تولد کاربران ووکامرس</h1>\r\n  <p class=\"price\">99000 تومان</p>\r\n  <p>افزونه تبریک تولد کاربران</p>\r\n  <p><a href=\"https://www.zhaket.com/web/novin-happy-brithday-theme/?affid=AF-6132dc3e42913\" target=\"_blank\"><button>مشاهده محصول</button></p></a>\r\n</div>\r\n<div class=\"card\">\r\n  <img src=\"https://cdn.zhaket.com/resources/5dc9fbf3eaec370009205b97/5faa8a35ac125f057514cf27.png\" alt=\"Denim Jeans\" style=\"width:100%\">\r\n  <h1 class=\"tit\">افزونه دعوت مشتریان قدیمی </h1>\r\n  <p class=\"price\">99000 تومان</p>\r\n  <p>افزونه دعوت مشتریان قدیمی ووکامرس</p>\r\n  <p><a href=\"https://www.zhaket.com/web/kankash-woocommerce-plugin/?affid=AF-6132dc3e42913\" target=\"_blank\"><button>مشاهده محصول</button></p></a>\r\n</div>\r\n<div class=\"card\">\r\n  <img src=\"https://cdn.zhaket.com/resources/5dc9fbf3eaec370009205b97/5faa86e6663c3605e5125880.png\" alt=\"Denim Jeans\" style=\"width:100%\">\r\n  <h1 class=\"tit\">ثبت نظر و امتیاز</h1>\r\n  <p class=\"price\">129000 تومان</p>\r\n  <p>افزونه ثبت نظر و امتیاز ووکامرس</p>\r\n  <p><a href=\"https://www.zhaket.com/web/arad-woocommerce-plugin/?affid=AF-6132dc3e42913\" target=\"_blank\"><button>مشاهده محصول</button></p></a>\r\n</div>\r\n<div class=\"card\">\r\n  <img src=\"https://cdn.zhaket.com/resources/5dca1ab9eaec370009207b46/5f98247105e6aa24963f3c4a.png\" alt=\"Denim Jeans\" style=\"width:100%\">\r\n  <h1 class=\"tit\">افزونه ارسال لینک پرداخت</h1>\r\n  <p class=\"price\">99000 تومان</p>\r\n  <p>افزونه ارسال لینک پرداخت ووکامرس</p>\r\n  <p><a href=\"https://www.zhaket.com/web/send-payment-link-of-woocommerce-plugin/?affid=AF-6132dc3e42913\" target=\"_blank\"><button>مشاهده محصول</button></p></a>\r\n</div>\r\n<div class=\"card\">\r\n  <img src=\"https://cdn.zhaket.com/resources/5dca1ab9eaec370009207b46/5f323123da02710547344726.jpg\" alt=\"Denim Jeans\" style=\"width:100%\">\r\n  <h1 class=\"tit\">افزونه احراز هویت کاربران</h1>\r\n  <p class=\"price\">129000 تومان</p>\r\n  <p>احراز هویت کاربران ووکامرس</p>\r\n  <p><a href=\"https://www.zhaket.com/web/authentication-of-the-woocommerce-users-plugin/?affid=AF-6132dc3e42913\" target=\"_blank\"><button>مشاهده محصول</button></p></a>\r\n</div>\r\n<div class=\"card\">\r\n  <img src=\"https://cdn.zhaket.com/resources/5dc9fbf3eaec370009205b97/5fd71bb9a336c10628013edf.jpg\" alt=\"Denim Jeans\" style=\"width:100%\">\r\n  <h1 class=\"tit\">امضای الکترونیک</h1>\r\n  <p class=\"price\">129000 تومان</p>\r\n  <p>افزونه رسید دیجیتال ووکامرس</p>\r\n  <p><a href=\"https://www.zhaket.com/web/digital-signature-wordpress-plugin/?affid=AF-6132dc3e42913\" target=\"_blank\"><button>مشاهده محصول</button></p></a>\r\n</div>\r\n<div class=\"card\">\r\n  <img src=\"https://cdn.zhaket.com/resources/5dca1ab9eaec370009207b46/5fae86d91b49b702806c51f7.png\" alt=\"Denim Jeans\" style=\"width:100%\">\r\n  <h1 class=\"tit\">تخفیف مشتریان وفادار</h1>\r\n  <p class=\"price\">99000 تومان</p>\r\n  <p>افزونه مشتریان وفادار ووکامرس</p>\r\n  <p><a href=\"https://www.zhaket.com/web/saba-woocommerce-theme/?affid=AF-6132dc3e42913\" target=\"_blank\"><button>مشاهده محصول</button></p></a>\r\n</div>\r\n<div class=\"card\">\r\n  <img src=\"https://cdn.zhaket.com/resources/5dca1ab9eaec370009207b46/5f9c32799803b636c45afc8d.png\" alt=\"Denim Jeans\" style=\"width:100%\">\r\n  <h1 class=\"tit\">استرداد سفارشات ووکامرس</h1>\r\n  <p class=\"price\">89000 تومان</p>\r\n  <p>افزونه استرداد سفارشات ووکامرس</p>\r\n  <p><a href=\"https://www.zhaket.com/web/aria-refund-woocommerce-plugin/?affid=AF-6132dc3e42913\" target=\"_blank\"><button>مشاهده محصول</button></p></a>\r\n</div>\r\n<div class=\"card\">\r\n  <img src=\"https://cdn.zhaket.com/resources/5dc9fbf3eaec370009205b97/5f9eb6df05e6aa3d70288257.jpg\" alt=\"Denim Jeans\" style=\"width:100%\">\r\n  <h1 class=\"tit\">میزان فروش ووکامرس</h1>\r\n  <p class=\"price\">79000 تومان</p>\r\n  <p>افزونه میزان فروش ووکامرس</p>\r\n  <p><a href=\"https://www.zhaket.com/web/arvand-sales-notification-woocommerce-plugin/?affid=AF-6132dc3e42913\" target=\"_blank\"><button>مشاهده محصول</button></p></a>\r\n</div>\r\n<div class=\"card\">\r\n  <img src=\"https://cdn.zhaket.com/resources/5dca1ab9eaec370009207b46/5ffe8cf0e6aa0b0c63703e0b.jpg\" alt=\"Denim Jeans\" style=\"width:100%\">\r\n  <h1 class=\"tit\">افزونه رویداد و مناسب های آوا</h1>\r\n  <p class=\"price\">99000 تومان</p>\r\n  <p>افزونه رویداد و مناسب های آوا</p>\r\n  <p><a href=\"https://www.zhaket.com/web/ava-woocommerce-plugin/?affid=AF-6132dc3e42913\" target=\"_blank\"><button>مشاهده محصول</button></p></a>\r\n</div>\r\n";
        }
        $ajax = order_traking_assets_url . "logo/ajax.gif";
        echo "        <body >\r\n    <div id=\"noivnDiv\">\r\n        <img id=\"loading-image\" src=\"";
        echo $ajax;
        echo "\" style=\" margin-right: auto;\r\n \r\n    position: fixed;\r\n    text-align: center;\r\n    top: 50%;\r\n    left: 0;\r\n    right: 0;\r\n   \r\n    margin-left: auto;\r\n    z-index: 99999999;display:none;\"/>\r\n\r\n    </div>\r\n</body>\r\n";
        if($tab === NULL || $tab === "icon") {
            echo "        <script>\r\n\r\njQuery(document).ready(function(\$){\r\n\r\n    var form = \$('#ajax-test-form');\r\n    form.submit(function(e) {\r\n        // prevent form submission\r\n     \r\n\r\n  \r\n    event.preventDefault();\r\n        // submit the form via Ajax\r\n        \$.ajax({\r\n            url: form.attr('action'),\r\n            type: form.attr('method'),\r\n            \r\n            dataType: 'html',\r\n            data:form.serialize(),\r\n         beforeSend: function() {\r\n              \$(\"#loading-image\").show();\r\n\r\n           },\r\n  \r\n            complete: function(result) {\r\n                 \$(\"#loading-image\").hide();\r\n\r\n                 \$(\"#suc\").fadeIn(1000);\r\n                 \$(\"#suc\").fadeOut(3000);\r\n\r\n            }\r\n        });\r\n    });\r\n \r\n});\r\n</script>\r\n   ";
        }
        echo "</div>\r\n            <form method=\"post\" action=\"options.php\" style=\"font-family: bbyekan\" enctype=\"multipart/form-data\" id=\"ajax-test-form\">\r\n            ";
        if($tab === "license") {
            settings_fields("novin_track_group");
            do_settings_sections("novin-setting-ordertracking");
        }
        if($tab === NULL) {
            settings_fields("novin_page_track_group");
            do_settings_sections("novin-setting-ordertracking");
            submit_button();
        }
        if($tab === "rahgir") {
            settings_fields("novin_rahgir_group");
            do_settings_sections("novin-setting-ordertracking");
            submit_button();
        }
        if($tab === "icon") {
            settings_fields("novin_icon_track_group");
            do_settings_sections("novin-setting-ordertracking");
            submit_button();
        }
        echo "            </form>\r\n        </div>\r\n        ";
    }
    // @function page_init_track_order is protected ioncube.dynamickey encoding key.
    public function page_init_track_order()
    {
    }
    public function print_method_novin()
    {
    }
    public function print_text_info()
    {
    }
    public function print_seeting_info()
    {
    }
    // @function icon_callback is protected ioncube.dynamickey encoding key.
    public function icon_callback()
    {
    }
    public function sanitize_icon_novin($input)
    {
        $new_input = [];
        if(isset($input["logon1"])) {
            $new_input["logon1"] = sanitize_text_field($input["logon1"]);
        }
        if(isset($input["logon2"])) {
            $new_input["logon2"] = sanitize_text_field($input["logon2"]);
        }
        if(isset($input["logon3"])) {
            $new_input["logon3"] = sanitize_text_field($input["logon3"]);
        }
        if(isset($input["logon4"])) {
            $new_input["logon4"] = sanitize_text_field($input["logon4"]);
        }
        if(isset($input["logon5"])) {
            $new_input["logon5"] = sanitize_text_field($input["logon5"]);
        }
        if(isset($input["logon6"])) {
            $new_input["logon6"] = sanitize_text_field($input["logon6"]);
        }
        if(isset($input["logon7"])) {
            $new_input["logon7"] = sanitize_text_field($input["logon7"]);
        }
        if(isset($input["logon8"])) {
            $new_input["logon8"] = sanitize_text_field($input["logon8"]);
        }
        if(isset($input["logon9"])) {
            $new_input["logon9"] = sanitize_text_field($input["logon9"]);
        }
        if(isset($input["logon10"])) {
            $new_input["logon10"] = sanitize_text_field($input["logon10"]);
        }
        return $new_input;
    }
    public function sanitize_setting($input)
    {
        $new_input = [];
        if(isset($input["chstatus1"])) {
            $new_input["chstatus1"] = sanitize_text_field("checked");
        }
        if(isset($input["dokan"])) {
            $new_input["dokan"] = sanitize_text_field("checked");
        }
        if(isset($input["virtual"])) {
            $new_input["virtual"] = sanitize_text_field("checked");
        }
        if(isset($input["navar_user"])) {
            $new_input["navar_user"] = sanitize_text_field("checked");
        }
        if(isset($input["navar_checkout"])) {
            $new_input["navar_checkout"] = sanitize_text_field("checked");
        }
        if(isset($input["after_before"])) {
            $new_input["after_before"] = sanitize_text_field($input["after_before"]);
        }
        if(isset($input["icon_user"])) {
            $new_input["icon_user"] = sanitize_text_field("checked");
        }
        if(isset($input["chstatus2"])) {
            $new_input["chstatus2"] = sanitize_text_field("checked");
        }
        if(isset($input["chstatus3"])) {
            $new_input["chstatus3"] = sanitize_text_field("checked");
        }
        if(isset($input["chstatus4"])) {
            $new_input["chstatus4"] = sanitize_text_field("checked");
        }
        if(isset($input["chstatus5"])) {
            $new_input["chstatus5"] = sanitize_text_field("checked");
        }
        if(isset($input["ch_process"])) {
            $new_input["ch_process"] = sanitize_text_field("checked");
        }
        if(isset($input["calender"])) {
            $new_input["calender"] = sanitize_text_field("checked");
        }
        if(isset($input["tapin"])) {
            $new_input["tapin"] = sanitize_text_field("checked");
        }
        if(isset($input["ch_hold"])) {
            $new_input["ch_hold"] = sanitize_text_field("checked");
        }
        if(isset($input["ch_box"])) {
            $new_input["ch_box"] = sanitize_text_field("checked");
        }
        if(isset($input["ch_com"])) {
            $new_input["ch_com"] = sanitize_text_field("checked");
        }
        if(isset($input["ch_dev"])) {
            $new_input["ch_dev"] = sanitize_text_field("checked");
        }
        if(isset($input["sehat_check"])) {
            $new_input["sehat_check"] = sanitize_text_field("checked");
        }
        if(isset($input["check_login"])) {
            $new_input["check_login"] = sanitize_text_field("checked");
        }
        if(isset($input["blank"])) {
            $new_input["blank"] = sanitize_text_field("checked");
        }
        if(isset($input["number"])) {
            $new_input["number"] = sanitize_text_field("checked");
        }
        if(isset($input["mobile"])) {
            $new_input["mobile"] = sanitize_text_field("checked");
        }
        if(isset($input["email"])) {
            $new_input["email"] = sanitize_text_field("checked");
        }
        if(isset($input["product_n"])) {
            $new_input["product_n"] = sanitize_text_field("checked");
        }
        if(isset($input["status1"])) {
            $new_input["status1"] = sanitize_text_field(implode(",", $input["status1"]));
        }
        if(isset($input["status2"])) {
            $new_input["status2"] = sanitize_text_field(implode(",", $input["status2"]));
        }
        if(isset($input["status3"])) {
            $new_input["status3"] = sanitize_text_field(implode(",", $input["status3"]));
        }
        if(isset($input["status4"])) {
            $new_input["status4"] = sanitize_text_field(implode(",", $input["status4"]));
        }
        if(isset($input["status5"])) {
            $new_input["status5"] = sanitize_text_field(implode(",", $input["status5"]));
        }
        if(isset($input["style_shekast"])) {
            $new_input["style_shekast"] = sanitize_text_field("checked");
        }
        if(isset($input["tow_aut"])) {
            $new_input["tow_aut"] = sanitize_text_field("checked");
        }
        if(isset($input["titr"])) {
            $new_input["titr"] = sanitize_text_field($input["titr"]);
        }
        if(isset($input["processing"])) {
            $new_input["processing"] = sanitize_text_field($input["processing"]);
        }
        if(isset($input["onhold"])) {
            $new_input["onhold"] = sanitize_text_field($input["onhold"]);
        }
        if(isset($input["box"])) {
            $new_input["box"] = sanitize_text_field($input["box"]);
        }
        if(isset($input["complete"])) {
            $new_input["complete"] = sanitize_text_field($input["complete"]);
        }
        if(isset($input["deliver"])) {
            $new_input["deliver"] = sanitize_text_field($input["deliver"]);
        }
        if(isset($input["tooltip"])) {
            $new_input["tooltip"] = sanitize_text_field($input["tooltip"]);
        }
        if(isset($input["tooldev"])) {
            $new_input["tooldev"] = sanitize_text_field($input["tooldev"]);
        }
        if(isset($input["peyk_tool"])) {
            $new_input["peyk_tool"] = sanitize_text_field($input["peyk_tool"]);
        }
        if(isset($input["peyk_tool_dev"])) {
            $new_input["peyk_tool_dev"] = sanitize_text_field($input["peyk_tool_dev"]);
        }
        if(isset($input["sms_replace"])) {
            $new_input["sms_replace"] = sanitize_text_field(nl2br($input["sms_replace"]));
        }
        if(isset($input["city_peyk"])) {
            $new_input["city_peyk"] = sanitize_text_field(implode(",", $input["city_peyk"]));
        }
        if(isset($input["shipping_peyk"])) {
            $new_input["shipping_peyk"] = sanitize_text_field(implode(",", $input["shipping_peyk"]));
        }
        if(isset($input["status_peyk"])) {
            $new_input["status_peyk"] = sanitize_text_field($input["status_peyk"]);
        }
        if(isset($input["favcolor"])) {
            $new_input["favcolor"] = sanitize_text_field($input["favcolor"]);
        }
        if(isset($input["colortd"])) {
            $new_input["colortd"] = sanitize_text_field($input["colortd"]);
        }
        if(isset($input["texttd"])) {
            $new_input["texttd"] = sanitize_text_field($input["texttd"]);
        }
        if(isset($input["postcolor"])) {
            $new_input["postcolor"] = sanitize_text_field($input["postcolor"]);
        }
        if(isset($input["navarcolor"])) {
            $new_input["navarcolor"] = sanitize_text_field($input["navarcolor"]);
        }
        if(isset($input["style"])) {
            $new_input["style"] = sanitize_text_field($input["style"]);
        }
        if(isset($input["button_text_color"])) {
            $new_input["button_text_color"] = sanitize_text_field($input["button_text_color"]);
        }
        if(isset($input["user_name"])) {
            $new_input["user_name"] = sanitize_text_field("checked");
        }
        if(isset($input["time_check"])) {
            $new_input["time_check"] = sanitize_text_field("checked");
        }
        if(isset($input["payment"])) {
            $new_input["payment"] = sanitize_text_field("checked");
        }
        if(isset($input["city"])) {
            $new_input["city"] = sanitize_text_field("checked");
        }
        if(isset($input["logcheck"])) {
            $new_input["logcheck"] = sanitize_text_field("checked");
        }
        if(isset($input["total"])) {
            $new_input["total"] = sanitize_text_field("checked");
        }
        if(isset($input["captcha"])) {
            $new_input["captcha"] = sanitize_text_field($input["captcha"]);
        }
        if(isset($input["head"])) {
            $new_input["head"] = sanitize_text_field("checked");
        }
        if(isset($input["logo"])) {
            $new_input["logo"] = sanitize_text_field($input["logo"]);
        }
        if(isset($input["site_key"])) {
            $new_input["site_key"] = $input["site_key"];
        }
        if(isset($input["secret_key"])) {
            $new_input["secret_key"] = $input["secret_key"];
        }
        if(isset($input["image"])) {
            $new_input["image"] = sanitize_text_field("checked");
        }
        if(isset($input["checkintvl"])) {
            $new_input["checkintvl"] = sanitize_text_field("checked");
        }
        if(isset($input["timeintvl"])) {
            $new_input["timeintvl"] = sanitize_text_field($input["timeintvl"]);
        }
        return $new_input;
    }
    public function captcha_callback()
    {
        $captcha = isset($this->setting["captcha"]) ? esc_attr($this->setting["captcha"]) : "";
        $site_key = isset($this->setting["site_key"]) ? esc_attr($this->setting["site_key"]) : "";
        $secret_key = isset($this->setting["secret_key"]) ? esc_attr($this->setting["secret_key"]) : "";
        echo "<select  id=\"captcha\" name=\"novin_track_order[captcha]\"  />\r\n<option value=\"no\">غیرفعال</option>\r\n<option value=\"captcha\">کپچای عددی</option>\r\n<option value=\"google\">گوگل کپچا v2</option>\r\n<option value=\"google3\">گوگل کپچا v3</option></select>";
        if(!empty($captcha)) {
            echo "<script type=\"text/javascript\">\r\ndocument.getElementById(\"captcha\").value = \"" . $captcha . "\" </script>";
        }
        echo "<br></br>\r\n\t\t<a href='' onclick='jQuery(\".novin_settings_captcha\").slideToggle(); return false;' style='text-decoration: none;'>\r\n\t\tبرای فعال سازی گوگل کپچا کلیک کنید.\r\n\t\t</a>\r\n\t\t<br>\r\n\t\t<br/>\r\n\t\t\t<div class='novin_settings_captcha' style='display: none'>\r\n\tsitkey :\t<input type='text' name='novin_track_order[site_key]' value='" . $site_key . "' style='width:70%'><br></br>\r\n\tsecret :\t<input type='text' name='novin_track_order[secret_key]' value='" . $secret_key . "' style='width:70%'><br>\r\n\t<p>برای ساخت کپچای گوگل به اکانت جیمیل خود لاگین کرده و وارد لینک زیر شوید ابتدا یک نام برای پروژه خود انتخاب نمایید سپس recaptcha v2 را انتخاب کنید از نوع tickbox و یا ریکپچا ورژن 3 را انتخاب و دامنه خود را وارد نمایید \r\n\t &nbsp; همچنین در گوگل آموزش های زیادی برای راه اندازی کپچا موجود است&nbsp;<a href='https://www.google.com/recaptcha/admin/create'target='_blank'>لینک</a></p>\r\n\t\t\t</div>";
    }
    public function get_state()
    {
        $statse = ["ABZ" => "البرز", "ADL" => "اردبیل", "EAZ" => "آذربایجان شرقی", "WAZ" => "آذربایجان غربی", "BHR" => "بوشهر", "CHB" => "چهارمحال و بختیاری", "FRS" => "فارس", "GIL" => "گیلان", "GLS" => "گلستان", "HDN" => "همدان", "HRZ" => "هرمزگان", "ILM" => "ایلام", "ESF" => "اصفهان", "KRN" => "کرمان", "KRH" => "کرمانشاه", "NKH" => "خراسان شمالی", "RKH" => "خراسان رضوی", "SKH" => "خراسان جنوبی", "KHZ" => "خوزستان", "KBD" => "کهگیلویه و بویراحمد", "KRD" => "کردستان", "LRS" => "لرستان", "MKZ" => "مرکزی", "MZN" => "مازندران", "GZN" => "قزوین", "QHM" => "قم", "SMN" => "سمنان", "SBN" => "سیستان و بلوچستان", "THR" => "تهران", "YZD" => "یزد", "ZJN" => "زنجان"];
        return $statse;
    }
    public function novin_get_all_city($state)
    {
        require_once "city.php";
        $cities = novin_custom_gateway_get_city($state);
        return $cities;
    }
    public function setting_callback()
    {
        if(!class_exists("Woocommerce")) {
            return NULL;
        }
        printf("&nbsp;\r\nفعال سازی\r\n<input type=\"checkbox\" id=\"check_login\"  name=\"novin_track_order[check_login]\" value=\"\" %s />", isset($this->setting["check_login"]) ? esc_attr($this->setting["check_login"]) : "");
    }
    public function text_callback()
    {
        printf("<input type=\"checkbox\" id=\"titr\"  name=\"novin_track_order[head]\" %s /><span>ارسال اطلاعات به صورت ایجکس . در صورتی که در قسمت فرم چیزی نمایش نداد و یا بهم ریختگی بوجود آمد این تیک را بردارید قالب شما پشتیبانی نمیکند </span><br></br>", isset($this->setting["head"]) ? esc_attr($this->setting["head"]) : "");
        printf("<input type=\"checkbox\" id=\"titr\"  name=\"novin_track_order[blank]\" %s /><span>اگر میخواهید فرم پیگیری پست یه صورت تب جدید باز شود  فعال کنید</span><br></br>", isset($this->setting["blank"]) ? esc_attr($this->setting["blank"]) : "");
        printf("<input type=\"checkbox\" id=\"titr\"  name=\"novin_track_order[virtual]\" %s /><span>غیرفعال سازی پیگیری محصولات دانلودی و مجازی</span><br></br>", isset($this->setting["virtual"]) ? esc_attr($this->setting["virtual"]) : "");
        printf("<input type=\"checkbox\" id=\"titr\"  name=\"novin_track_order[navar_user]\" %s /><span>غیرفعال سازی نوار پیشرفت در قسمت سفارشات کاربری</span><br></br>", isset($this->setting["navar_user"]) ? esc_attr($this->setting["navar_user"]) : "");
        printf("<input type=\"checkbox\" id=\"titr\"  name=\"novin_track_order[navar_checkout]\" %s /><span>غیرفعال سازی نوار پیشرفت در قسمت صفحه تشکر</span><br></br>", isset($this->setting["navar_checkout"]) ? esc_attr($this->setting["navar_checkout"]) : "");
        printf("<input type=\"checkbox\" id=\"titr\"  name=\"novin_track_order[icon_user]\" %s /><span>غیرفعال سازی ستون آیکن ها در قسمت سفارشات کاربری</span><br></br>", isset($this->setting["icon_user"]) ? esc_attr($this->setting["icon_user"]) : "");
        printf("<input type=\"checkbox\" id=\"titr\"  name=\"novin_track_order[dokan]\" %s /><span>فعال سازی درج کد رهگیری برای فروشندگان دکان</span><br></br>", isset($this->setting["dokan"]) ? esc_attr($this->setting["dokan"]) : "");
        printf("<input type=\"checkbox\" id=\"titr\"  name=\"novin_track_order[tow_aut]\" %s /><span>اگر میخواهید در فرم رهگیری کاربر شماره سفارش را با شماره موبایل یا ایمیل سفارشش  پیگیری کند فعال کنید</span><br></br>", isset($this->setting["tow_aut"]) ? esc_attr($this->setting["tow_aut"]) : "");
        printf("<input type=\"checkbox\" id=\"titr\"  name=\"novin_track_order[style_shekast]\" %s /><span>در برخی قالب ها ممکن است در مرحله تکمیل شده در نوار پیشرفت شکستگی یا بهم ریختگی به وجود آید، در این صورت این گزینه را فعال کنید</span><br></br>", isset($this->setting["style_shekast"]) ? esc_attr($this->setting["style_shekast"]) : "");
        printf("<span>متن سرتیتر فرم پیگیری</span><br></br><input type=\"text\" id=\"titr\" style=\"width:600px\"  name=\"novin_track_order[titr]\" placeholder=\"لطفا شماره سفارش یا ایمیل و یا شماره موبایل خود را وارد کنید\" value=\"%s\" /><br></br>", isset($this->setting["titr"]) ? esc_attr($this->setting["titr"]) : "");
        echo "</select>&nbsp<br></br><br><p>شما میتوانید با استفاده از لیست کشویی وضعیت های پیشفرض را تغییر دهید در صورتی که وضعیتی انتخاب ننمایید از وضعیت های پیش فرض پیروی خواهد شد برای فعال شدن وضعیت جدید چکباکس های جلوی هر وضعیت را فعال نمایید (در غیر اینصورت این مورد را رها کنید تا از وضعیت های پیش فرض پیروی شود) توجه کنید 5 وضعیت فعلی به صورت پیشفرض فعال است و نیاز به وارد کردن آنها در ردیف خودشان نیست به طور مثال مرحله اول در حال انجام ، نیاز به وارد کردن وضعیت در حال انجام در مرحله اول نیست اما میتوان در مراحل 2 تا 4 انتخابش کرد این مورد برای 4 وضعیت بعدی نیز صدق میکند</p><br></br>";
        printf("لیبل <input type=\"text\" id=\"processing\" style=\"width:110px\"  name=\"novin_track_order[processing]\" placeholder=\"درحال انجام\" value=\"%s\" />&nbsp;&nbsp;", isset($this->setting["processing"]) ? esc_attr($this->setting["processing"]) : "");
        $chstatus1 = isset($this->setting["chstatus1"]) ? esc_attr($this->setting["chstatus1"]) : "";
        $status1 = isset($this->setting["status1"]) ? explode(",", esc_attr($this->setting["status1"])) : "";
        echo "\r\n<input type=\"checkbox\"  name=\"novin_track_order[chstatus1]\" ";
        echo $chstatus1;
        echo " />\r\n<select name=\"novin_track_order[status1][]\" multiple id=\"status1\">\r\n";
        foreach (wc_get_order_statuses() as $val => $key) {
            if(!empty($status1) && in_array($val, $status1)) {
                echo "<option value=\"" . $val . "\" selected>" . $key . "</option>";
            }
            echo "<option value=\"" . $val . "\">" . $key . "</option>";
        }
        echo "</select>&nbsp;ادغام وضعیت ها در (مرحله 1)<br></br>";
        printf("لیبل <input type=\"text\" id=\"onhold\" style=\"width:110px\"  name=\"novin_track_order[onhold]\" placeholder=\"در حال بررسی\" value=\"%s\" />&nbsp;&nbsp;", isset($this->setting["onhold"]) ? esc_attr($this->setting["onhold"]) : "");
        $chstatus2 = isset($this->setting["chstatus2"]) ? esc_attr($this->setting["chstatus2"]) : "";
        $status2 = isset($this->setting["status2"]) ? explode(",", esc_attr($this->setting["status2"])) : "";
        echo "<input type=\"checkbox\" name=\"novin_track_order[chstatus2]\" ";
        echo $chstatus2;
        echo "/>\r\n<select name=\"novin_track_order[status2][]\" multiple id=\"status2\">\r\n";
        if(class_exists("WooCommerce")) {
            foreach (wc_get_order_statuses() as $val => $key) {
                if(!empty($status2) && in_array($val, $status2)) {
                    echo "<option value=\"" . $val . "\" selected>" . $key . "</option>";
                }
                echo "<option value=\"" . $val . "\">" . $key . "</option>";
            }
        }
        echo "</select>&nbsp;ادغام وضعیت ها در (مرحله 2)<br></br>";
        printf("لیبل <input type=\"text\" id=\"box\" style=\"width:110px\"  name=\"novin_track_order[box]\" placeholder=\"بسته بندی\" value=\"%s\" />&nbsp;", isset($this->setting["box"]) ? esc_attr($this->setting["box"]) : "");
        $chstatus3 = isset($this->setting["chstatus3"]) ? esc_attr($this->setting["chstatus3"]) : "";
        $status3 = isset($this->setting["status3"]) ? explode(",", esc_attr($this->setting["status3"])) : "";
        echo "<input type=\"checkbox\" name=\"novin_track_order[chstatus3]\" ";
        echo $chstatus3;
        echo " />\r\n<select name=\"novin_track_order[status3][]\" multiple id=\"status3\">\r\n";
        if(class_exists("WooCommerce")) {
            foreach (wc_get_order_statuses() as $val => $key) {
                if(!empty($status3) && in_array($val, $status3)) {
                    echo "<option value=\"" . $val . "\" selected>" . $key . "</option>";
                }
                echo "<option value=\"" . $val . "\">" . $key . "</option>";
            }
        }
        echo "</select>&nbsp;ادغام وضعیت ها در (مرحله 3)<br></br>";
        printf("لیبل <input type=\"text\" id=\"complete\" style=\"width:110px\"  name=\"novin_track_order[complete]\" placeholder=\"تکمیل شده\" value=\"%s\" />&nbsp;", isset($this->setting["complete"]) ? esc_attr($this->setting["complete"]) : "");
        $chstatus4 = isset($this->setting["chstatus4"]) ? esc_attr($this->setting["chstatus4"]) : "";
        $status4 = isset($this->setting["status4"]) ? explode(",", esc_attr($this->setting["status4"])) : "";
        echo " <input type=\"checkbox\" name=\"novin_track_order[chstatus4]\" ";
        echo $chstatus4;
        echo " />\r\n<select name=\"novin_track_order[status4][]\" multiple id=\"status4\" >\r\n";
        if(class_exists("WooCommerce")) {
            foreach (wc_get_order_statuses() as $val => $key) {
                if(!empty($status4) && in_array($val, $status4)) {
                    echo "<option value=\"" . $val . "\" selected>" . $key . "</option>";
                }
                echo "<option value=\"" . $val . "\">" . $key . "</option>";
            }
        }
        echo "</select>&nbsp;ادغام وضعیت ها در (مرحله 4)<br></br>";
        printf("لیبل <input type=\"text\" id=\"deliver\" style=\"width:110px\"  name=\"novin_track_order[deliver]\" placeholder=\"تحویل شده\" value=\"%s\" />&nbsp;", isset($this->setting["deliver"]) ? esc_attr($this->setting["deliver"]) : "");
        $chstatus5 = isset($this->setting["chstatus5"]) ? esc_attr($this->setting["chstatus5"]) : "";
        $status5 = isset($this->setting["status5"]) ? explode(",", esc_attr($this->setting["status5"])) : "";
        echo "<input type=\"checkbox\" name=\"novin_track_order[chstatus5]\" ";
        echo $chstatus5;
        echo " />\r\n<select name=\"novin_track_order[status5][]\" multiple id=\"status5\">\r\n";
        if(class_exists("WooCommerce")) {
            foreach (wc_get_order_statuses() as $val => $key) {
                if(!empty($status5) && in_array($val, $status5)) {
                    echo "<option value=\"" . $val . "\" selected>" . $key . "</option>";
                }
                echo "<option value=\"" . $val . "\">" . $key . "</option>";
            }
        }
        echo "</select>&nbsp;ادغام وضعیت ها در (مرحله 5)<br></br>\r\n<script type=\"text/javascript\">\r\n     jQuery(document).ready(function(\$){\r\n    \r\n     \$(\"#status1,#status2,#status3,#status4,#status5,#city-peyk,#shipp-peyk\").select2({width:'60%'}); \r\n     });\r\n</script>\r\n";
        printf("فعال کردن تحویل شده&nbsp;<input type=\"checkbox\" id=\"ch_dev\"  name=\"novin_track_order[ch_dev]\"  %s /><br></br>", isset($this->setting["ch_dev"]) ? esc_attr($this->setting["ch_dev"]) : "");
        printf("غیرفعال سازی صحت تایید محصول در صفحه سفارشات کاربر&nbsp;<input type=\"checkbox\"   name=\"novin_track_order[sehat_check]\"  %s /><br></br>", isset($this->setting["sehat_check"]) ? esc_attr($this->setting["sehat_check"]) : "");
        echo "\r\n\t\t<a href='' onclick='jQuery(\".novin_settings_shortcodes\").slideToggle(); return false;' style='text-decoration: none;'>\r\n\t\t\tبرای مشاهده شورتکدها کلیک کنید.\r\n\t\t</a>\r\n\t\t<br>\r\n\t\t<br/>\r\n\t\t<div class='novin_settings_shortcodes' style='display: none'>\r\n\t\t\t<code>{order_id}</code> =شماره سفارش\r\n\t\t\t<code>{marsule}</code> = کد پیگیری مرسوله قابل استفاده در پیامک و متن تولتیپ,\r\n\t\t\t<code>{hamlonaghl}</code> =نام سیستم حمل و نقل قابل استفاده در پیامک و تولتیپ ,<br><br>\r\n\t\t\t\t<code>{senddate}</code> =تاریخ ارسال مرسوله قابل استفاده در پیامک و تولتیپ ,\r\n\t\t\t\t\t<code>{deliverydate}</code> =تاریخ تحویل مرسوله قابل استفاده در پیامک و تولتیپ,<br></br>\r\n\t\t\t\t\t<code> [order-tracking-code]</code>=شورتکد برگه پیگیری سفارش\r\n\t\t\t\t\t\r\n\t\t</div><br></br>\r\n\t";
        printf("<span>متن تولتیپ تکمیل شده (یا مرحله 4)</span><br></br><input type=\"text\" id=\"tooltip\" style=\"width:650px\"  name=\"novin_track_order[tooltip]\" placeholder=\"سفارش شماره {order_id}با کد رهگیری {marsule}توسط {hamlonaghl} در تاریخ {senddate} ارسال گردید\" value=\"%s\" /><br></br>", isset($this->setting["tooltip"]) ? esc_attr($this->setting["tooltip"]) : "");
        printf("<span>متن تولتیپ تحویل شده (یا مرحله 5)</span><br></br><input type=\"text\" id=\"tooldev\" style=\"width:650px\"  name=\"novin_track_order[tooldev]\" placeholder=\"سفارش شماره {order_id} در تاریخ {deliverydate} با کد رهگیری  {marsule} تحویل مشتری گردید\" value=\"%s\" /><br></br>", isset($this->setting["tooldev"]) ? esc_attr($this->setting["tooldev"]) : "");
        printf("<span>متن تولتیپ تکمیل شده برای پیک(یا مرحله 4)</span><br></br><input type=\"text\" id=\"peyk_tool\" style=\"width:650px\"  name=\"novin_track_order[peyk_tool]\" placeholder=\"سفارش شماره {order_id} توسط  {peyk}  در تاریخ {senddate} ارسال گردید.\" value=\"%s\" /><br></br>", isset($this->setting["peyk_tool"]) ? esc_attr($this->setting["peyk_tool"]) : "سفارش شماره {order_id} توسط  {peyk}  در تاریخ {senddate} ارسال گردید.");
        printf("<span>متن تولتیپ تحویل شده برای پیک(یا مرحله 5)</span><br></br><input type=\"text\" id=\"peyk_tool_dev\" style=\"width:650px\"  name=\"novin_track_order[peyk_tool_dev]\" placeholder=\"سفارش شماره {order_id} در تاریخ {deliverydate}  تحویل مشتری گردید\" value=\"%s\" /><br></br>", isset($this->setting["peyk_tool_dev"]) ? esc_attr($this->setting["peyk_tool_dev"]) : "سفارش شماره {order_id} در تاریخ {deliverydate}  تحویل مشتری گردید");
        if(!empty($this->setting["sms_replace"])) {
            $sms = str_replace("<br />", "", $this->setting["sms_replace"]);
        } else {
            $sms = "سلام {b_first_name} {b_last_name}\r\nسفارش {order_id} توسط {peyk} ارسال شد.\r\nشماره تماس پیک : {mobile_peyk}";
        }
        echo "در این قسمت میتوانید متن پیامکی که برای پیک ارسال میشود را وارد نمایید توجه کنید این پیام جایگزین پیامک تکمیل شده یا وضعیتی که در زیر انتخاب میکنید ، برای مشتری میشود  <br></br> \r\n <select name=\"novin_track_order[status_peyk]\"  id=\"status-peyk\">\r\n\r\n \r\n ";
        $status_peyk = isset($this->setting["status_peyk"]) ? esc_attr($this->setting["status_peyk"]) : "";
        if(class_exists("WooCommerce")) {
            echo "<option>هیچکدام</option>";
            foreach (wc_get_order_statuses() as $val => $key) {
                if(!empty($status_peyk) && $val == $status_peyk) {
                    echo "<option value=\"" . $val . "\" selected>" . $key . "</option>";
                }
                echo "<option value=\"" . $val . "\">" . $key . "</option>";
            }
        }
        echo "</select>&nbsp;به صورت پیشفرض برای تکمیل شده ارسال میگردد<br></br>";
        printf("<textarea id=\"payamak\" name=\"novin_track_order[sms_replace]\" rows=\"6\" cols=\"50\" style=\"resize: vertical;\"  >" . $sms . "</textarea><br></br>");
        $get_city = isset($this->setting["city_peyk"]) ? explode(",", $this->setting["city_peyk"]) : [];
        echo "\t\t\t<p>شهر یا شهرهای پیکی خود را که میخوانید سیستم پیکی در آن فعال شود را انتخاب نمایید</p><br></br>\r\n\t\t\t<select name=\"novin_track_order[city_peyk][]\" id=\"city-peyk\"  multiple>\r\n\t\t\t\r\n\t\t\t";
        $tapin = get_option("pws_tapin");
        $masir = get_option("novin_shipping_masir_tapin");
        $tapn_masir = isset($masir["tapin"]) ? $masir["tapin"] : "";
        if(class_exists("novin_advance_shipping_masir") && $tapn_masir == "checked") {
            $statse = get_transient("novin_advance_shipping_save_city") ? get_transient("novin_advance_shipping_save_city") : get_option("novin_save_tapin_tree");
            foreach ($statse as $state) {
                echo "\t\t\t\t\t<optgroup label=\"";
                echo $state["title"];
                echo "\">\r\n\t\t\t\t\r\n\t\t\t\t";
                foreach ($state["cities"] as $city) {
                    $title = trim(str_replace("-" . $state["title"], "", $city["title"]));
                    if(!empty($get_city) && in_array($city["code"], $get_city)) {
                        echo "\r\n                       <option value=\"";
                        echo $city["code"];
                        echo "\" selected>";
                        echo $title;
                        echo "</option>\r\n\t\t\t\t\t";
                    }
                    if(empty($get_city) || !in_array($city["code"], $get_city)) {
                        echo "      <option value=\"";
                        echo $city["code"];
                        echo "\">";
                        echo $title;
                        echo "</option>";
                        if(!in_array($state["code"], $statse)) {
                            echo "     \r\n\t\t\t";
                        }
                    }
                }
                echo "</optgroup>\r\n\t\t";
            }
        } elseif(class_exists("PWS_VERSION")) {
            $active_tapin = 0;
            if(class_exists("PWS_Tapin") && isset($tapin) && isset($tapin["enable"]) && !empty($tapin) && !empty($tapin["enable"]) && $tapin["enable"] == 1) {
                $tapin = new PWS_Tapin();
                $statse = $tapin::states();
                $active_tapin = 1;
            } else {
                $statse = PWS()->states();
            }
            foreach ($statse as $state => $value) {
                echo "\t\t\t\t\t<optgroup label=\"";
                echo $value;
                echo "\">\r\n\t\t\t\t\r\n\t\t\t\t";
                if(class_exists("PWS_Tapin") && !empty($active_tapin) && $active_tapin == 1) {
                    $getcity = $tapin::cities($state);
                } else {
                    $getcity = PWS()->cities($state);
                }
                foreach ($getcity as $city => $cit) {
                    if(!empty($get_city) && in_array($city, $get_city)) {
                        echo "\r\n                       <option value=\"";
                        echo $city;
                        echo "\" selected>";
                        echo $cit;
                        echo "</option>\r\n\t\t\t\t\t";
                    }
                    if(empty($get_city) || !in_array($city, $get_city)) {
                        echo "      <option value=\"";
                        echo $city;
                        echo "\">";
                        echo $cit;
                        echo "</option>";
                        if(!in_array($value, $statse)) {
                            echo "     \r\n\t\t\t";
                        }
                    }
                }
                echo "</optgroup>\r\n\t\t";
            }
        } else {
            $statse = $this->get_state();
            $cities = [];
            foreach ($statse as $state => $value) {
                echo "\t\t\t\t\t<optgroup label=\"";
                echo $value;
                echo "\">\r\n\t\t\t\t\r\n\t\t\t\t";
                $getcity = $this->novin_get_all_city($state);
                foreach ($getcity as $city) {
                    $cities = $city[0];
                    if(!empty($get_city) && in_array($cities, $get_city)) {
                        echo "\r\n                       <option value=\"";
                        echo $cities;
                        echo "\" selected>";
                        echo $cities;
                        echo "</option>\r\n\t\t\t\t\t";
                    }
                    if(empty($get_city) || !in_array($cities, $get_city)) {
                        echo "      <option value=\"";
                        echo $cities;
                        echo "\">";
                        echo $cities;
                        echo "</option>";
                        if(!in_array($value, $statse)) {
                            echo "     \r\n\t\t\t";
                        }
                    }
                }
                echo "</optgroup>\r\n\t\t";
            }
        }
        echo "       </select>\r\n       <p>روش حمل و نقلی که میخواهید دران سیستم پیکی در آن فعال شود</p>\r\n        <br></br>\r\n   ";
        $shipping_peyk = !empty($this->setting["shipping_peyk"]) ? explode(",", $this->setting["shipping_peyk"]) : [];
        echo "        <select name=\"novin_track_order[shipping_peyk][]\"  id=\"shipp-peyk\"  multiple>\r\n   \r\n        ";
        if(class_exists("WC_Shipping_Zones")) {
            $delivery_zones = @WC_Shipping_Zones::get_zones();
            $shippings = [];
            foreach ((array) $delivery_zones as $key => $the_zone) {
                foreach ($the_zone["shipping_methods"] as $value) {
                    $shippings[] = $value;
                }
            }
        }
        if(!empty($shippings)) {
            $i = 0;
            foreach ($shippings as $shipp) {
                $i++;
                $force = $shipp->get_rate_id();
                if($shipp->id == "novin_shipping_force") {
                    $force = $shipp->id;
                }
                if($shipp->id == "novin_person_delivery") {
                    $force = $shipp->id;
                }
                if($i == 1 && !in_array("zz", $shipping_peyk)) {
                    echo "        \r\n                       <option value=\"zz\">هیچکدام</option>\r\n                       \r\n                         ";
                }
                if(!empty($shipping_peyk) && in_array($force, $shipping_peyk)) {
                    echo "                         \r\n                       <option value=\"";
                    echo $force;
                    echo "\" selected>";
                    echo $shipp->get_title();
                    echo "</option>\r\n\r\n\r\n   ";
                }
                if($i == 1 && in_array("zz", $shipping_peyk)) {
                    echo "        \r\n                       <option value=\"zz\" selected>هیچکدام</option>\r\n\r\n\r\n   ";
                }
                if(empty($shipping_peyk) || !in_array($force, $shipping_peyk)) {
                    echo "<option value=\"" . $force . "\">" . $shipp->get_title() . "</option>";
                }
            }
        }
        echo "       </select>\r\n<br></br>  \r\n      \r\n       \r\n       \r\n       ";
        printf("<span>نمایش جزییات مرسوله</span><br></br><input type=\"checkbox\" id=\"user_name\"  name=\"novin_track_order[user_name]\" value=\"\" %s />&nbsp;نام کاربر<br></br>", isset($this->setting["user_name"]) ? esc_attr($this->setting["user_name"]) : "");
        printf("<input type=\"checkbox\" id=\"payment\"  name=\"novin_track_order[payment]\" value=\"\" %s />&nbsp;روش پرداخت<br></br>", isset($this->setting["payment"]) ? esc_attr($this->setting["payment"]) : "");
        printf("<input type=\"checkbox\" id=\"city\"  name=\"novin_track_order[city]\" value=\"\" %s/>&nbsp;مقصد<br></br>", isset($this->setting["city"]) ? esc_attr($this->setting["city"]) : "");
        printf("<input type=\"checkbox\" id=\"total\"  name=\"novin_track_order[total]\" value=\"\" %s />&nbsp;مبلغ پرداخت<br></br>", isset($this->setting["total"]) ? esc_attr($this->setting["total"]) : "");
        printf("<input type=\"checkbox\" id=\"image\"  name=\"novin_track_order[image]\" value=\"\" %s />&nbsp;نمایش تصویر محصول<br></br>", isset($this->setting["image"]) ? esc_attr($this->setting["image"]) : "");
        printf("<input type=\"checkbox\" id=\"product_n\"  name=\"novin_track_order[product_n]\" value=\"\" %s />&nbsp;نمایش نام محصول<br></br>", isset($this->setting["product_n"]) ? esc_attr($this->setting["product_n"]) : "");
        printf("<input type=\"checkbox\" id=\"time_check\"  name=\"novin_track_order[time_check]\" value=\"\" %s />&nbsp;نمایش ساعت در کنار تاریخ وضعیت ها<br></br>", isset($this->setting["time_check"]) ? esc_attr($this->setting["time_check"]) : "");
        printf("<input type=\"checkbox\" id=\"time_check\"  name=\"novin_track_order[logcheck]\" value=\"\" %s />&nbsp;حذف تصویر پیشفرض متحرک<br></br>", isset($this->setting["logcheck"]) ? esc_attr($this->setting["logcheck"]) : "");
        echo "<span style=\"font-weight: 500;\">بارگزاری تصویر در فرم پیگیری</span><br></br>\r\n\r\n    \r\n <input type=\"button\"  id=\"woocommerce_cod_gateway_icon_submit\" class=\"button button2 gateway_upload_icon\"/>   <br></br>\r\n   <div  id=\"woocommerce_cod_gateway_html\"></div>\r\n    <div  id=\"gateway_html\"></div>\r\n    \r\n";
        $img = isset($this->setting["logo"]) ? esc_attr($this->setting["logo"]) : "";
        if(!empty($img)) {
            echo "<div id=\"gateway_html_icon\"><img style=\"width: 100px;height: 100px;\" src=\"" . $img . "\">";
            echo "<input  type=\"hidden\" name=\"novin_track_order[logo]\" value=\"" . $img . "\"></div>";
        }
        echo "           <p>سایز پیشنهادی برای آیکون 130 در 130 می باشد (برای حذف روی عکس کلیک کنید)</p><br>\r\n\r\n\r\n";
        printf("<input style=\"font-weight: 500;\" type=\"color\" id=\"navarcolor\" name=\"novin_track_order[navarcolor]\" value=\"%s\"> تغییر رنگ نوار پیشرفت استایل نوین<br><br>", isset($this->setting["navarcolor"]) ? esc_attr($this->setting["navarcolor"]) : "");
        printf("<input style=\"font-weight: 500;\" type=\"color\" id=\"favcolor\" name=\"novin_track_order[favcolor]\" value=\"%s\"> تغییر رنگ دکمه و فیلد فرم پیگیری<br><br>", isset($this->setting["favcolor"]) ? esc_attr($this->setting["favcolor"]) : "");
        printf("<input style=\"font-weight: 500;\" type=\"color\" id=\"colortd\" name=\"novin_track_order[colortd]\" value=\"%s\"> تغییر رنگ هدر جدول جزییات کاربر<br><br>", isset($this->setting["colortd"]) ? esc_attr($this->setting["colortd"]) : "");
        printf("<input style=\"font-weight: 500;\" type=\"color\" id=\"texttd\" name=\"novin_track_order[texttd]\" value=\"%s\"> تغییر رنگ متن هدر جدول جزییات کاربر<br><br>", isset($this->setting["texttd"]) ? esc_attr($this->setting["texttd"]) : "");
        printf("<input style=\"font-weight: 500;\" type=\"color\" id=\"postcolor\" name=\"novin_track_order[postcolor]\" value=\"%s\"> تغییر رنگ دکمه پیگیری از پست<br><br>", isset($this->setting["postcolor"]) ? esc_attr($this->setting["postcolor"]) : "");
        printf("<input style=\"font-weight: 500;\" type=\"color\" id=\"button_text_color\" name=\"novin_track_order[button_text_color]\" value=\"%s\"> تغییر رنگ متن دکمه پیگیری از پست<br><br>", isset($this->setting["button_text_color"]) ? esc_attr($this->setting["button_text_color"]) : "");
        printf("انتخاب استایل نوار پیشرفت&nbsp;<select name=\"novin_track_order[style]\" id=\"style\">\r\n    <option  value=\"novin\" >استایل نوین</option>\r\n    <option value=\"digi\">استایل دیجی نوین</option>\r\n  </select><br></br>");
        if(!empty($this->setting["style"])) {
            echo "<script type=\"text/javascript\">\r\ndocument.getElementById(\"style\").value = \"" . $this->setting["style"] . "\" </script>";
        }
        printf("انتخاب نمایش نوار پیشرفت بعد یا قبل جزییات سفارش (این مورد بستگی به قالب شما دارد که کجا نمایش دهد اگر در قبل از جزییات سفارش بهم ریختگی دارد شامل پشتیبانی نمیگردد)&nbsp;<br></br><select name=\"novin_track_order[after_before]\" id=\"after_before\">\r\n    <option  value=\"before\" >قبل از جزییات سفارش</option>\r\n    <option value=\"after\">بعد از جزییات سفارش</option>\r\n  </select><br></br>");
        if(!empty($this->setting["after_before"])) {
            echo "<script type=\"text/javascript\">\r\ndocument.getElementById(\"after_before\").value = \"" . $this->setting["after_before"] . "\" </script>";
        }
        printf("<input type=\"checkbox\"   name=\"novin_track_order[calender]\"  %s />&nbsp;فعال سازی تقویم شمسی در صفحه سفارش<br></br>در صورت تداخل تقویم شمسی با سایر افزونه های شمسی این گزینه را غیر فعال کنید<br></br>", isset($this->setting["calender"]) ? esc_attr($this->setting["calender"]) : "");
        $checkintvl = isset($this->setting["checkintvl"]) ? esc_attr($this->setting["checkintvl"]) : "";
        $timeintvl = isset($this->setting["timeintvl"]) ? esc_attr($this->setting["timeintvl"]) : "";
        printf("فعال سازی خودکارتغییر وضعیت به تحویل شده سفارشات&nbsp;&nbsp;&nbsp;<input type=\"checkbox\" id=\"checkintvl\"  name=\"novin_track_order[checkintvl]\" value=\"\" " . $checkintvl . " /><br></br>" . "از این قسمت تعیین کنید سفارشات تکمیل شده پس از چند روز به وضعیت تحویل شده تغییر وضعیت دهد.(مجموع سفارشات از الان تا یک ماه اخیر در نظر گرفته میشوند). تعداد روز&nbsp;\r\n\r\n&nbsp;\r\n\r\n<input id=\"timeintvl\" name=\"novin_track_order[timeintvl]\" value=\"" . $timeintvl . "\" style=\"width :59px;border-radius: 3px;\"  placeholder=\"مانند : 1\"><br></br><hr>");
        echo " اگر از افزونه حمل و نقل تاپین که در مخزن وردپرس موجود است استفاده میکنید و کد های رهگیری که توسط تاپین از طریق آن افزونه دریافت میشود و میخواهید در فیلد کد رهگیری سفارشات اضافه و با این افزونه هماهنگ شود فعال کنید (این مورد هنگامی که کدهای مرسوله دریافت شده باشند و بعد از آن تغییر وضعیت دهید اعمال میشوند . این مورد تاریخ ارسال را لحظه تغییر وضعیت اعمال و نوع فرم پست پیشتاز انتخاب میشود)";
        printf("<br></br><input type=\"checkbox\"   name=\"novin_track_order[tapin]\"  %s />فعال سازی<br></br>", isset($this->setting["tapin"]) ? esc_attr($this->setting["tapin"]) : "");
    }
    public function method_callback()
    {
        echo "\r\n<p style=\"color: #ed0808;font-weight: bold;\">توجه: سفارشاتی که با موبایل و ایمیل جستجو میشوند اگر بیش از 1 سفارش وجود داشته باشد مجموع 4 سفارش اخیر در فرم پیگیری نمایش داده میشوند!</p><br></br>\r\n";
        printf("&nbsp;\r\nشماره سفارش\r\n<input type=\"checkbox\" id=\"number\"  name=\"novin_track_order[number]\" value=\"\" %s />", isset($this->setting["number"]) ? esc_attr($this->setting["number"]) : "");
        printf("&nbsp;\r\nشماره موبایل\r\n<input type=\"checkbox\" id=\"mobile\"  name=\"novin_track_order[mobile]\" value=\"\" %s />", isset($this->setting["mobile"]) ? esc_attr($this->setting["mobile"]) : "");
        printf("&nbsp;\r\nایمیل\r\n<input type=\"checkbox\" id=\"email\"  name=\"novin_track_order[email]\" value=\"\" %s />", isset($this->setting["email"]) ? esc_attr($this->setting["email"]) : "");
    }
    public function print_section_info()
    {
        echo "کد لایسنسی که از قسمت دانلود ها در پنل کاربری ژاکت دریافت کرده اید در این قسمت وارد کنید";
    }
    public function sanitize($input)
    {
        if(isset($input["title"])) {
            $new_input = [];
            $license_token = $input["title"];
            $produc_token = $this->produc_token;
            $result = novin_license::install($license_token, $produc_token);
            if(isset($input["title"]) && $result->status != "successful") {
                $new_input["licenset"] = sanitize_text_field($input["title"]);
            } elseif(isset($input["title"]) && $result->status == "successful") {
                $new_input["title"] = sanitize_text_field($input["title"]);
            }
        }
        return $new_input;
    }
    // @function rahgir_callback is protected ioncube.dynamickey encoding key.
    public function rahgir_callback()
    {
    }
    public function sanitize_rahgiri_novin($input)
    {
        $new_input = [];
        if(isset($input["date_default"])) {
            $new_input["date_default"] = sanitize_text_field($input["date_default"]);
        }
        if(isset($input["status_code"])) {
            $new_input["status_code"] = sanitize_text_field($input["status_code"]);
        }
        if(isset($input["post_default"])) {
            $new_input["post_default"] = sanitize_text_field($input["post_default"]);
        }
        if(isset($input["date_default_check"])) {
            $new_input["date_default_check"] = sanitize_text_field("checked");
        }
        if(isset($input["status_sh"])) {
            $new_input["status_sh"] = sanitize_text_field($input["status_sh"]);
        }
        if(isset($input["desc_asc"])) {
            $new_input["desc_asc"] = sanitize_text_field($input["desc_asc"]);
        }
        if(isset($input["pishfarz_rahgir"])) {
            $new_input["pishfarz_rahgir"] = sanitize_text_field($input["pishfarz_rahgir"]);
        }
        if(isset($_POST["excel"]) && isset($_FILES["novin-xlsfile"]["tmp_name"]) && !empty($_FILES["novin-xlsfile"]["tmp_name"])) {
            $Filepath = WP_CONTENT_DIR . "/uploads/";
            $targets = $Filepath . basename($_FILES["novin-xlsfile"]["name"]);
            if(move_uploaded_file($_FILES["novin-xlsfile"]["tmp_name"], $targets)) {
                require_once "excel_reader2.php";
                require_once "SpreadsheetReader.php";
                $Reader = new SpreadsheetReader($targets);
                $i = 0;
                foreach ($Reader as $row) {
                    $i++;
                    $order_id = !empty($row[0]) ? esc_attr(absint($row[0])) : "";
                    $order = wc_get_order($order_id);
                    if($order) {
                        $form = $post = $date = "";
                        if(isset($input["post_default"])) {
                            $post = $input["post_default"];
                        }
                        $post = !empty($row[1]) ? esc_attr(sanitize_text_field($row[1])) : $post;
                        if(isset($input["date_default"])) {
                            $date = $input["date_default"];
                        }
                        $date = !empty($row[2]) ? esc_attr(sanitize_text_field($row[2])) : $date;
                        $forms = !empty($row[3]) ? esc_attr(sanitize_text_field($row[3])) : "";
                        $marsule = !empty($row[4]) ? esc_attr(sanitize_text_field($row[4])) : "";
                        $order->update_meta_data("shipp", esc_attr($post));
                        $order->update_meta_data("datei", esc_attr($date));
                        if(isset($input["pishfarz_rahgir"]) && $input["pishfarz_rahgir"] != "هیچکدام") {
                            $form = $input["pishfarz_rahgir"];
                        }
                        if($forms == "post") {
                            $form = "postrahgir";
                        }
                        if($forms == "chapar") {
                            $form = "chaparrahgir";
                        }
                        if($forms == "tipax") {
                            $form = "tipax";
                        }
                        $order->update_meta_data("apostrahgir", esc_attr($form));
                        $order->update_meta_data("marsule", esc_attr($marsule));
                        $rah = $order->get_meta("marsule");
                        if(!empty($rah) && !empty($input["status_code"])) {
                            $order->update_status(str_replace("wc-", "", $input["status_code"]));
                        }
                        $order->save_meta_data();
                        $order->save();
                    }
                }
                $xls_path = WP_CONTENT_DIR . "/uploads/" . $_FILES["novin-xlsfile"]["name"];
                unlink($xls_path);
            }
        }
        if(empty($_POST["excel"]) && isset($_POST["ids"]) && (isset($_POST["mobile_peyk"]) || isset($_POST["rahgirid"]) || isset($_POST["hamlnaghl"]) || isset($_POST["datesend"]) || isset($_POST["form"]))) {
            $idsd = count($_POST["ids"]);
            for ($i = 0; $i < $idsd; $i++) {
                if(!empty($_POST["mobile_peyk"][$i]) || !empty($_POST["rahgirid"][$i])) {
                    $ids = $_POST["ids"][$i];
                    $order = wc_get_order($ids);
                    if(!empty($_POST["hamlnaghl"][$i])) {
                        $order->update_meta_data("shipp", esc_attr($_POST["hamlnaghl"][$i]));
                    }
                    if(!empty($_POST["datesend"][$i])) {
                        $order->update_meta_data("datei", esc_attr($_POST["datesend"][$i]));
                    }
                    if(!empty($_POST["rahgirid"][$i])) {
                        $order->update_meta_data("marsule", esc_attr($_POST["rahgirid"][$i]));
                    }
                    if(!empty($_POST["form"][$i])) {
                        $order->update_meta_data("apostrahgir", esc_attr($_POST["form"][$i]));
                    }
                    if(!empty($_POST["mobile_peyk"][$i])) {
                        $order->update_meta_data("novin_peyk_mobile", esc_attr($_POST["mobile_peyk"][$i]));
                    }
                    if(is_a($order, "WC_Order")) {
                        $order->update_status(str_replace("wc-", "", $input["status_code"]));
                    }
                    $order->save_meta_data();
                    $order->save();
                }
            }
        }
        if(isset($i)) {
            $new_input["count"] = absint($i);
        }
        return $new_input;
    }
    public function title_callback()
    {
        $default_tab = NULL;
        $tab = isset($_GET["tab"]) ? $_GET["tab"] : $default_tab;
        if($tab === "license") {
            $li = isset($this->options["title"]) ? esc_attr($this->options["title"]) : "";
            $lo = isset($this->options["licenset"]) ? esc_attr($this->options["licenset"]) : "";
            $license_token = $li ? $li : $lo;
            $result = novin_license::install($license_token, $this->produc_token);
            if($result->status == "successful") {
                echo "<span style=\"color:#1aca1a;font-size: 13px;font-weight: 700;\" >" . $result->message . "</span>\r\n<br></br><span>" . $license_token . "</span>\r\n</br><br><button style=\"background: #1aab83;cursor: pointer;border-radius: 4px;font-size: 13px; color: #fff;border: 2px solid #54c340;    height: 30px;\" type=\"submit\" />حذف و ثبت لایسنس جدید</button>";
            } elseif(!is_object($result->message)) {
                echo "<span style=\"color:red;font-size: 13px;font-weight: 700;\" >" . $result->message . "</span></br><br><input type=\"text\" style=\"width:350px\" id=\"title\" name=\"novin_order[title]\" required  value=\"\"/><br></br>\r\n<button style =\"background: #2fdc3d;border-radius: 4px; cursor: pointer;color: #fff; border: 2px solid #54c340;\"  type=\"submit\" />ثبت لایسنس</button></br><br>";
            } else {
                foreach ($result->message as $message) {
                    foreach ($message as $msg) {
                        echo "<span style=\"color:red;font-size: 13px;font-weight: 700;\" >" . $msg . "</span></br><br><input type=\"text\" style=\"width:350px\" id=\"title\" name=\"novin_order[title]\" required  value=\"\"/><br></br>\r\n<button style =\"background: #2fdc3d;border-radius: 4px; cursor: pointer;color: #fff; border: 2px solid #54c340;    height: 30px;\"  type=\"submit\" />ثبت لایسنس</button></br><br>";
                    }
                }
            }
        }
    }
}
function novin_seller_doakan_save_tracking()
{
    if(!class_exists("WooCommerce")) {
        return NULL;
    }
    check_ajax_referer("novin-shipping-tracking-info", "security");
    if(isset($_POST["order_id"])) {
        $order_id = absint($_POST["order_id"]);
        $order = wc_get_order($order_id);
        if(isset($_POST["marsule"])) {
            $order->update_meta_data("marsule", esc_attr($_POST["marsule"]));
        }
        if(isset($_POST["send_date"])) {
            $order->update_meta_data("datei", esc_attr($_POST["send_date"]));
        }
        if(isset($_POST["deliver_date"])) {
            $order->update_meta_data("datedeliver", esc_attr($_POST["deliver_date"]));
        }
        if(isset($_POST["hamlonaghl"])) {
            $order->update_meta_data("shipp", esc_attr($_POST["hamlonaghl"]));
        }
        if(isset($_POST["post_form"])) {
            $order->update_meta_data("apostrahgir", esc_attr($_POST["post_form"]));
        }
        if(isset($_POST["mobile_peyk"])) {
            $order->update_meta_data("novin_peyk_mobile", esc_attr($_POST["mobile_peyk"]));
        }
        if(isset($_POST["complete"])) {
            $order->update_status("completed");
        }
        $setting = get_option("novin_track_order");
        $checkdev = isset($setting["ch_dev"]) ? esc_attr($setting["ch_dev"]) : "";
        if($checkdev == "checked" && isset($_POST["deliver"])) {
            $order->update_status("deliver");
        }
        $order->save_meta_data();
        $order->save();
    }
    exit;
}
function novin_dokan_order_details_after_customer($order)
{
    if(!class_exists("WeDevs_Dokan")) {
        return NULL;
    }
    $order_id = esc_attr(dokan_get_prop($order, "id"));
    $send_date = $order->get_meta("datei");
    $hamlonaghl = $order->get_meta("shipp");
    $form = $order->get_meta("apostrahgir");
    $marsule = $order->get_meta("marsule");
    $date_deliver = $order->get_meta("datedeliver");
    wp_enqueue_script("novin_dokan_script_novin", order_traking_assets_url . "js/persian-datepicker.js");
    wp_enqueue_style("novin_dokan_admin_css", order_traking_assets_url . "css/persiandate.css");
    $mobile_peyk = $order->get_meta("novin_peyk_mobile");
    $setting = get_option("novin_track_order");
    $masir = get_option("novin_shipping_masir_tapin");
    $tapn_masir = isset($masir["tapin"]) ? $masir["tapin"] : "";
    $tapin = get_option("pws_tapin");
    if(class_exists("novin_advance_shipping_masir") && $tapn_masir == "checked" || class_exists("PWS_Tapin") && isset($tapin) && isset($tapin["enable"]) && !empty($tapin) && !empty($tapin["enable"]) && $tapin["enable"] == 1 || class_exists("PWS_VERSION")) {
        $city = $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city();
        if(empty($city)) {
            $city = $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city();
        }
    } else {
        $city = $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city();
    }
    $city_peyk = !empty($setting["city_peyk"]) ? explode(",", $setting["city_peyk"]) : [];
    $shipping_peyk = !empty($setting["shipping_peyk"]) ? explode(",", $setting["shipping_peyk"]) : [];
    $ships = "";
    if(class_exists("PWS_VERSION")) {
        foreach ($order->get_items("shipping") as $item_id => $item) {
            if($item->get_method_id() == "novin_shipping_force" || $item->get_method_id() == "novin_person_delivery") {
                $ships = $item->get_method_id();
            } else {
                $ships = $item->get_method_id() . ":" . $item->get_instance_id();
            }
        }
    } else {
        foreach ($order->get_items("shipping") as $item_id => $item) {
            $ships = $item->get_method_id();
        }
    }
    echo "    \r\n              <div class=\"dokan-left dokan-order-shipping-address\">\r\n                <div class=\"dokan-panel dokan-panel-default\">\r\n                    <div class=\"dokan-panel-heading\"><strong>";
    esc_html_e("اطلاعات ارسال مرسوله", "dokan-lite");
    echo "</strong></div>\r\n                    <div class=\"dokan-panel-body\" id=\"novin-filter\">\r\n                             <div class=\"clearfix dokan-form-group\" style=\"margin-top: 10px;\">\r\n                                <!-- Trigger the modal with a button -->\r\n                                <input type=\"button\" id=\"dokan-add-tracking-number\" name=\"add_tracking_number\" class=\"dokan-btn dokan-btn-success\" value=\"";
    esc_attr_e("درج جزییات کد رهگیری", "dokan-lite");
    echo "\">\r\n\r\n                                <form id=\"add-shipping-tracking-form\" method=\"post\" class=\"dokan-hide\" style=\"margin-top: 10px;\">\r\n                                \r\n                                    ";
    if(!in_array($ships, $shipping_peyk) || !in_array($city, $city_peyk)) {
        echo "                                    \r\n                                    <div class=\"dokan-form-group\">\r\n                                        <label class=\"dokan-control-label\">";
        esc_html_e("درج کد رهگیری", "dokan-lite");
        echo "</label>\r\n                                        <input type=\"text\" name=\"tracking_number\" id=\"novin_tracking_number\" class=\"dokan-form-control\" value=\"";
        echo $marsule;
        echo "\">\r\n                                    </div>\r\n                                    \r\n                                    ";
    }
    echo "                                    \r\n                                      <div class=\"dokan-form-group\">\r\n                                        <label class=\"dokan-control-label\">";
    esc_html_e("نوع سیستم حمل و نقل", "dokan-lite");
    echo "</label>\r\n                                        <input type=\"text\" name=\"tracking_number\" id=\"novin_hamlonaghl\" class=\"dokan-form-control\" value=\"";
    echo $hamlonaghl;
    echo "\">\r\n                                    </div>\r\n                                    ";
    if(!empty($city_peyk) && in_array($ships, $shipping_peyk) && in_array($city, $city_peyk)) {
        echo "                                    \r\n                                     <div class=\"dokan-form-group\">\r\n                                        <label class=\"dokan-control-label\">";
        esc_html_e("شماره تماس پیک", "dokan-lite");
        echo "</label>\r\n                                        <input type=\"text\" name=\"peyk_number\" id=\"novin_mobile_peyk\" class=\"dokan-form-control\" value=\"";
        echo $mobile_peyk;
        echo "\">\r\n                                    </div>\r\n                                    \r\n                                    ";
    }
    echo "                                    \r\n                                    <div class=\"dokan-form-group\">\r\n                                        <label class=\"dokan-control-label\">";
    esc_html_e("تاریخ ارسال", "dokan-lite");
    echo "</label>\r\n                                        <input type=\"text\" name=\"shipped_date\" id=\"novindatesend\" class=\"dokan-form-control\" value=\"";
    echo $send_date;
    echo "\" >\r\n                                    </div>\r\n                                    \r\n                                    ";
    if(!in_array($ships, $shipping_peyk) || !in_array($city, $city_peyk)) {
        echo "                                        <div class=\"dokan-form-group\">\r\n                                             <label class=\"dokan-control-label\">";
        esc_html_e("انتخاب فرم رهگیری پست:", "dokan-lite");
        echo "</label>\r\n                                   <select name=\"formrahgir\" id=\"novin_post_form\" style= \"width:100%;font-weight: 500;cursor: pointer;\"/> \r\n                                    <option >هیچکدام</option>\r\n                                    <option  value=\"postrahgir\">فرم رهگیری از پست</option>\r\n                                    <option value=\"chaparrahgir\">فرم رهگیری از چاپار</option>\r\n                                    <option value=\"tipax\">دکمه پیگیری از تیپاکس</option>\r\n                                    </select>\r\n                                    </div>\r\n                                    ";
    }
    echo "                                    <div class=\"dokan-form-group\">\r\n                                        <label class=\"dokan-control-label\">";
    esc_html_e("تاریخ تحویل", "dokan-lite");
    echo "</label>\r\n                                        <input type=\"text\" name=\"shipped_date\" id=\"novindatedeliver\" class=\"dokan-form-control\" value=\"";
    echo $date_deliver;
    echo "\" >\r\n                                    </div>\r\n                                   \r\n                                    <input type=\"hidden\" name=\"order_id_track\" id=\"order_id_track\" value=\"";
    echo $order_id;
    echo "\">\r\n                                     <div class=\"dokan-form-group\">\r\n                                        <label class=\"dokan-control-label\">";
    esc_html_e("تغییر وضعیت سفارش به تکمیل شده", "dokan-lite");
    echo "</label>\r\n                                        <input type=\"checkbox\" name=\"tracking_number\" id=\"novin_complete\" class=\"dokan-form-control\" >\r\n                                    </div>\r\n                                     <div class=\"dokan-form-group\">\r\n                                        <label class=\"dokan-control-label\">";
    esc_html_e("تغییر وضعیت سفارش به تحویل شده", "dokan-lite");
    echo "</label>\r\n                                        <input type=\"checkbox\" name=\"tracking_number\" id=\"novin_deliver\" class=\"dokan-form-control\" >\r\n                                    </div>\r\n                                    \r\n                                    <div class=\"dokan-form-group\">\r\n                                        <input id=\"novin-tracking-details\" type=\"button\" class=\"btn btn-primary\" value=\"";
    esc_attr_e("Add Tracking Details", "dokan-lite");
    echo "\">\r\n                                        <button type=\"button\" class=\"btn btn-default\" id=\"dokan-cancel-tracking-note\">";
    esc_html_e("Close", "dokan-lite");
    echo "</button>\r\n                                    </div>\r\n                                    \r\n                                    <br>\r\n                                    \r\n                                    <div class=\"result-track-novin\"></div>\r\n                                </form>\r\n                            </div>\r\n                    </div>\r\n                </div>\r\n            </div>\r\n    <script type=\"text/javascript\">\r\njQuery(document).ready(function(\$) {\r\n    \$(\"#novindatedeliver,#novindatesend\").persianDatepicker({\r\n    initialValue: false,\r\n       cellWidth: 32,\r\n        cellHeight: 30,\r\n        fontSize: 14,\r\n    });\r\n    \r\n  \$('#novin_complete').change(function(){\r\n      \r\n    \$('#novin_deliver').prop('checked', false);\r\n    \r\n  });\r\n  \r\n   \$('#novin_deliver').change(function(){\r\n      \r\n   \$('#novin_complete').prop('checked', false);\r\n    \r\n  });\r\n \r\n              \$('#novin-tracking-details').click(function(event){\r\n                        event.preventDefault();\r\n\r\n              var order_id = \$('#order_id_track').val();\r\n              var marsule = \$('#novin_tracking_number').val();\r\n              var send_date = \$('#novindatesend').val();\r\n              var hamlonaghl = \$('#novin_hamlonaghl').val();\r\n              var deliver_date = \$('#novindatedeliver').val();\r\n              var post_form = \$('#novin_post_form').val();\r\n              var mobile_peyk = \$('#novin_mobile_peyk').val();\r\n              \r\n              \r\n              if (\$('#novin_complete').is(':checked')) { \r\n     \r\n              var complete = 1;\r\n              \r\n              \r\n              \r\n              }\r\n     \r\n\r\n                  \r\n              if (\$('#novin_deliver').is(':checked')) { \r\n\r\n              var deliver = 1;\r\n              \r\n               \r\n              \r\n              }\r\n\r\n             \r\n\r\n            \$.ajax({\r\n         type: 'POST',\r\n           url: '";
    echo admin_url("admin-ajax.php");
    echo "',\r\n           data: {\r\n             \r\n               order_id:order_id,\r\n               marsule :marsule,\r\n               send_date:send_date,\r\n               post_form:post_form,\r\n               hamlonaghl:hamlonaghl,\r\n               deliver_date:deliver_date,\r\n               deliver:deliver,\r\n               complete:complete,\r\n               mobile_peyk:mobile_peyk,\r\n               action:'novin_send_tracking_dokan',\r\n               security: '";
    echo wp_create_nonce("novin-shipping-tracking-info");
    echo "'\r\n           },\r\n            beforeSend: function() {\r\n            \r\n      \r\n            \$(\"#novin-filter\").css(\"filter\",\"opacity(0.4)\");\r\n         \r\n       \r\n           },\r\n           success: function(data) {\r\n     \r\n           \$(\"#novin-filter\").css(\"filter\",\"unset\");\r\n           \r\n           \$(\".result-track-novin\").append('<span style=\"padding:4px;background-color:green;color: #fff;border-radius: 4px;\">اطلاعات با موفقیت ذخیره شد</span>')\r\n            \r\n              \r\n            },\r\n          \r\n         })\r\n   });\r\n });\r\n</script>\r\n    ";
    if(!empty($form)) {
        echo "\r\n    <script type=\"text/javascript\">\r\ndocument.getElementById(\"novin_post_form\").value = \"" . $form . "\" </script>";
    }
}
function novin_dokan_load_document_menu($query_vars)
{
    $query_vars["code-rahgir"] = "code-rahgir";
    return $query_vars;
}
function novin_dokan_add_help_menu($urls)
{
    $urls["code-rahgir"] = ["title" => __("درج کد های رهگیری", "dokan"), "icon" => "<i class=\"fa fa-user\"></i>", "url" => dokan_get_navigation_url("code-rahgir"), "pos" => 51];
    return $urls;
}
function novin_dokan_load_template($query_vars)
{
    if(isset($query_vars["code-rahgir"])) {
        $seler_id = get_current_user_id();
        wp_enqueue_script("novin_dokan_script_rahgir", order_traking_assets_url . "js/persian-datepicker.js");
        wp_enqueue_style("novin_dokan_rahgir_css", order_traking_assets_url . "css/persiandate.css");
        require_once "dokan.php";
    }
}
function novin_tarah_track_order_basteh($hasher, $randstr)
{
    return hash($hasher, $randstr);
}
function novin_dokan_add_custom_order_status_button_class($text, $status)
{
    switch ($status) {
        case "wc-box":
        case "box":
            $text = "danger";
            break;
        case "wc-deliver":
        case "deliver":
            $text = "success";
            break;
        default:
            return $text;
    }
}
function novin_dokan_add_custom_order_status_translated($text, $status)
{
    switch ($status) {
        case "wc-box":
        case "box":
            $text = __("بسته بندی", "dokan-lite");
            break;
        case "wc-deliver":
        case "deliver":
            $text = __("تحویل شده", "dokan-lite");
            break;
        default:
            return $text;
    }
}
function auto_change_status_novin_deliver()
{
    $setting = get_option("novin_track_order");
    $day = isset($setting["timeintvl"]) ? esc_attr($setting["timeintvl"]) : "";
    $active = isset($setting["checkintvl"]) ? esc_attr($setting["checkintvl"]) : "";
    $chdev = isset($setting["ch_dev"]) ? esc_attr($setting["ch_dev"]) : "";
    if($active == "checked" && $chdev == "checked" && !empty($day) && is_numeric($day)) {
        global $wpdb;
        $today = strtotime(date("Y-m-d"));
        $days = 1728000;
        if(get_option("woocommerce_custom_orders_table_enabled") == "yes") {
            $completed_orders = $wpdb->get_col("SELECT id\r\n        FROM " . $wpdb->prefix . "wc_orders WHERE\r\n        type LIKE 'shop_order'\r\n        AND status IN ('wc-completed')\r\n        AND   date_format( date_created_gmt, '%Y-%m-%d') > DATE_SUB(date_format(NOW(), '%Y-%m-%d'), INTERVAL 30 Day) ORDER BY date_created_gmt DESC LIMIT 300");
        } else {
            $completed_orders = $wpdb->get_col("SELECT p.ID\r\n        FROM " . $wpdb->prefix . "posts as p\r\n        WHERE p.post_type LIKE 'shop_order'\r\n        AND p.post_status IN ('wc-completed')\r\n        AND   date_format( p.post_date, '%Y-%m-%d') > DATE_SUB(date_format(NOW(), '%Y-%m-%d'), INTERVAL 30 Day)\r\n        ORDER BY p.post_date DESC LIMIT 300");
        }
        if(0 < count($completed_orders)) {
            foreach ($completed_orders as $order_id) {
                $day_old = (int) $day * 24 * 3600;
                $order = new WC_Order($order_id);
                if(strtotime(date("Y-m-d", strtotime($order->get_date_completed()))) < (int) ($today - $day_old)) {
                    $order->update_status("wc-deliver");
                }
            }
        }
    }
}
function novin_add_my_account_my_orders_deliver_actions($actions, $order)
{
    if($order->has_status("completed")) {
        $action_slug = "order_confirmed";
        $actions[$action_slug] = ["url" => wp_nonce_url(add_query_arg("complete_order", $order->get_id()), "wc_complete_order"), "name" => __("صحت دریافت محصول", "woocommerce")];
    }
    return $actions;
}
function novin_action_deliver_order_status($query)
{
    if(class_exists("WooCommerce") && isset($_GET["_wpnonce"]) && wp_verify_nonce($_GET["_wpnonce"], "wc_complete_order")) {
        $order = wc_get_order(absint($_GET["complete_order"]));
        if(is_a($order, "WC_Order")) {
            $order->update_status("deliver", __("تغییر وضعیت توسط کاربر", "woocommerce"));
            $date_deliver = novinjdate("Y/m/d");
            $order->update_meta_data("datedeliver", $date_deliver);
            $order->save_meta_data();
            $order->save();
            wc_add_notice(sprintf(__("سفارش %s# به وضعیت تحویل شده بروزرسانی شد", "woocommerce"), $order->get_id()));
            wp_redirect(esc_url(remove_query_arg(["complete_order", "_wpnonce"])));
            exit;
        }
    }
}
function novin_code_toolbar_rahgir($admin_bar)
{
    $admin_bar->add_menu(["id" => "cod-rahgir", "title" => "درج کد رهگیری", "href" => home_url() . "/wp-admin/admin.php?page=novin-setting-ordertracking&tab=rahgir", "meta" => ["title" => __("درج کد رهگیری")]]);
}
function register_baste_order_statuses()
{
    $setting = get_option("novin_track_order");
    $box = isset($setting["box"]) ? esc_attr($setting["box"]) : "";
    $box = isset($setting["box"]) ? esc_attr($setting["box"]) : "";
    if(empty($box)) {
        $box = "بسته بندی";
    }
    register_post_status("wc-box", ["label" => _x($box, "Order status", "woocommerce"), "public" => true, "exclude_from_search" => false, "show_in_admin_all_list" => true, "show_in_admin_status_list" => true, "label_count" => _n_noop($box . "<span class=\"count\">(%s)</span>", "" . $box . "<span class=\"count\">(%s)</span>", "woocommerce")]);
}
function baste_bulk_actions_edit_product($actions)
{
    $setting = get_option("novin_track_order");
    $box = isset($setting["box"]) ? esc_attr($setting["box"]) : "";
    $box = isset($setting["box"]) ? esc_attr($setting["box"]) : "";
    if(empty($box)) {
        $box = "بسته بندی";
    }
    $actions["mark_box"] = __("تغییر وضعیت به " . $box . " ", "woocommerce");
    return $actions;
}
function baste_dokan_bulk_actions_edit_product($actions)
{
    $setting = get_option("novin_track_order");
    $box = isset($setting["box"]) ? esc_attr($setting["box"]) : "";
    $box = isset($setting["box"]) ? esc_attr($setting["box"]) : "";
    if(empty($box)) {
        $box = "بسته بندی";
    }
    $actions["wc-box"] = __("تغییر وضعیت به " . $box . " ", "woocommerce");
    return $actions;
}
function baste_new_wc_order_statuses($order_statuses)
{
    $setting = get_option("novin_track_order");
    $box = isset($setting["box"]) ? esc_attr($setting["box"]) : "";
    $box = isset($setting["box"]) ? esc_attr($setting["box"]) : "";
    if(empty($box)) {
        $box = "بسته بندی";
    }
    $new_order_statuses = [];
    foreach ($order_statuses as $key => $status) {
        $new_order_statuses[$key] = $status;
        if("wc-on-hold" === $key) {
            $new_order_statuses["wc-box"] = $box;
        }
    }
    return $new_order_statuses;
}
function register_deliver_order_statuses()
{
    $setting = get_option("novin_track_order");
    $deliver = isset($setting["deliver"]) ? esc_attr($setting["deliver"]) : "";
    if(empty($deliver)) {
        $deliver = "تحویل شده";
    }
    register_post_status("wc-deliver", ["label" => _x($deliver, "Order status", "woocommerce"), "public" => true, "exclude_from_search" => false, "show_in_admin_all_list" => true, "show_in_admin_status_list" => true, "label_count" => _n_noop("" . $deliver . " <span class=\"count\">(%s)</span>", "" . $deliver . "<span class=\"count\">(%s)</span>", "woocommerce")]);
}
function deliver_dokan_bulk_actions_edit_product($actions)
{
    $setting = get_option("novin_track_order");
    $deliver = isset($setting["deliver"]) ? esc_attr($setting["deliver"]) : "";
    if(empty($deliver)) {
        $deliver = "تحویل شده";
    }
    $actions["wc-deliver"] = __("تغییر وضعیت به " . $deliver . "", "woocommerce");
    return $actions;
}
function deliver_bulk_actions_edit_product($actions)
{
    $setting = get_option("novin_track_order");
    $deliver = isset($setting["deliver"]) ? esc_attr($setting["deliver"]) : "";
    if(empty($deliver)) {
        $deliver = "تحویل شده";
    }
    $actions["mark_deliver"] = __("تغییر وضعیت به " . $deliver . "", "woocommerce");
    return $actions;
}
function deliver_new_wc_order_statuses($order_statuses)
{
    $setting = get_option("novin_track_order");
    $deliver = isset($setting["deliver"]) ? esc_attr($setting["deliver"]) : "";
    if(empty($deliver)) {
        $deliver = "تحویل شده";
    }
    $new_order_statuses = [];
    foreach ($order_statuses as $key => $status) {
        $new_order_statuses[$key] = $status;
        if("wc-completed" === $key) {
            $new_order_statuses["wc-deliver"] = $deliver;
        }
    }
    return $new_order_statuses;
}
function include_novin_order_status_to_reports($statuses)
{
    return str_replace("wc-", "", ["wc-refoundone", "wc-refoundtwo", "wc-deliver", "wc-on-hold", "wc-processing", "wc-completed", "wc-box", "wc-withoutlogin", "wc-pws-packaged", "wc-pws-ready-to-ship", "wc-pws-shipping", "wc-pws-need-review", "wc-pws-in-stock", "wc-pws-packaged", "wc-pws-courier"]);
}
function novin_metabox_load_plugi_js()
{
    wp_enqueue_script("my-script-novin", order_traking_assets_url . "js/persian-datepicker.js");
    wp_enqueue_style("novin_wp_admin_css", order_traking_assets_url . "css/persiandate.css");
}
function wp_admin_order_status_novin()
{
    global $ver_num;
    $ver_num = mt_rand();
    wp_enqueue_style("wp_admin_css", order_traking_assets_url . "css/admin.css?v=" . $ver_num, "all");
    wp_enqueue_style("status_wp_admin_css", order_traking_assets_url . "css/order-status.css?v=" . $ver_num, "all");
}
function novin_track_order_load_plugin_css()
{
    if(class_exists("WooCommerce") && (is_wc_endpoint_url("orders") || is_account_page())) {
        $ver_num = mt_rand();
        wp_enqueue_style("style-form", order_traking_assets_url . "css/style-form.css?v=" . $ver_num, "all");
        $setting = get_option("novin_track_order");
        $style_shekast = isset($setting["style_shekast"]) ? esc_attr($setting["style_shekast"]) : "";
        if($style_shekast == "checked") {
            echo "\r\n<style>\r\n    \r\n    ol.progresss[data-stepss=\"3\"] li {\r\n \r\n    line-height: 0px!important;\r\n\t\r\n}\r\n\r\n.progresss .names {\r\n    \r\n     margin-bottom: 20px!important;\r\n\t\r\n}\r\n    \r\n</style>\r\n\r\n\r\n";
        }
    }
}
function novin_order_items_column($columns)
{
    $new_columns = [];
    foreach ($columns as $key => $column) {
        $new_columns[$key] = $columns[$key];
        if($key === "order_date") {
            $new_columns["ordered_products"] = __("وضعیت ارسال", "woo-custom-ec");
        }
    }
    return $new_columns;
}
function novin_track_admin_order_items_column_icon($column, $order_id)
{
    $setting = get_option("novin_track_order");
    $setting_icon = get_option("novin_icon_track");
    $order = wc_get_order($order_id);
    if($order) {
        $masir = get_option("novin_shipping_masir_tapin");
        $tapn_masir = isset($masir["tapin"]) ? $masir["tapin"] : "";
        $city_peyk = !empty($setting["city_peyk"]) ? explode(",", $setting["city_peyk"]) : [];
        $shipping_peyk = !empty($setting["shipping_peyk"]) ? explode(",", $setting["shipping_peyk"]) : [];
        $ships = "";
        if(class_exists("PWS_VERSION")) {
            foreach ($order->get_items("shipping") as $item_id => $item) {
                if($item->get_method_id() == "novin_shipping_force" || $item->get_method_id() == "novin_person_delivery") {
                    $ships = $item->get_method_id();
                } else {
                    $ships = $item->get_method_id() . ":" . $item->get_instance_id();
                }
            }
        } else {
            foreach ($order->get_items("shipping") as $item_id => $item) {
                $ships = $item->get_method_id();
            }
        }
        $tapin = get_option("pws_tapin");
        if(class_exists("novin_advance_shipping_masir") && $tapn_masir == "checked" || class_exists("PWS_Tapin") && isset($tapin) && isset($tapin["enable"]) && !empty($tapin) && !empty($tapin["enable"]) && $tapin["enable"] == 1 || class_exists("PWS_VERSION")) {
            $city = $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city();
            if(empty($city)) {
                $city = $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city();
            }
        } else {
            $city = $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city();
        }
        $tootip = !empty($setting["tooltip"]) ? esc_attr($setting["tooltip"]) : "سفارش شماره {order_id} با کد پیگیری {marsule} توسط {hamlonaghl} در تاریخ {senddate} ارسال گردید";
        $tooldev = !empty($setting["tooldev"]) ? esc_attr($setting["tooldev"]) : "سفارش شماره {order_id} در تاریخ {deliverydate} با کد پیگیری {marsule} تحویل مشتری گردید";
        if(in_array($city, $city_peyk) && in_array($ships, $shipping_peyk)) {
            $tooldev = !empty($setting["peyk_tool_dev"]) ? esc_attr($setting["peyk_tool_dev"]) : "سفارش شماره {order_id} در تاریخ {deliverydate}  تحویل مشتری گردید";
            $tootip = !empty($setting["peyk_tool"]) ? esc_attr($setting["peyk_tool"]) : "سفارش شماره {order_id} توسط {peyk} در تاریخ {senddate} ارسال گردید";
        }
        $packing = isset($setting_icon["logon3"]) ? "<img src =" . esc_attr($setting_icon["logon3"]) . ">" : "<img src =" . order_traking_assets_url . "logo/packing.png>";
        $icon = isset($setting_icon["logon4"]) ? "<img src =" . esc_attr($setting_icon["logon4"]) . ">" : "<img style = \"cursor: pointer\" id = \"p1\"  src =" . order_traking_assets_url . "/logo/order-traking.jpg" . ">";
        $deliver = isset($setting_icon["logon5"]) ? "<img src =" . esc_attr($setting_icon["logon5"]) . ">" : "<img src =" . order_traking_assets_url . "logo/deliver.png>";
        $recive = isset($setting_icon["logon1"]) ? "<img src =" . esc_attr($setting_icon["logon1"]) . ">" : "<img src =" . order_traking_assets_url . "logo/recive.png>";
        $hold = isset($setting_icon["logon2"]) ? "<img src =" . esc_attr($setting_icon["logon2"]) . ">" : "<img src =" . order_traking_assets_url . "logo/onhold.png>";
        $send_date_to = $order->get_meta("datei");
        $hamlonaghl = $order->get_meta("shipp");
        $codemi = "<mark style = \" background: none;font-weight: 600;color: #f9af08;\">" . $order->get_meta("marsule") . "</mark>";
        $ordid_id = $order->get_order_number();
        $shortcode = ["{order_id}", "{marsule}", "{hamlonaghl}", "{senddate}", "{peyk}"];
        $variable = [$ordid_id, $codemi, $hamlonaghl, $send_date_to, $hamlonaghl];
        $massg = $tootip;
        $code = str_replace($shortcode, $variable, $massg);
        if($column == "ordered_products") {
            $tooldev = $tooldev;
            $datedelivery = $order->get_meta("datedeliver");
            $short_dev = ["{order_id}", "{deliverydate}", "{marsule}", "{hamlonaghl}", "{senddate}", "{peyk}"];
            $var_dev = [$ordid_id, $datedelivery, $codemi, $hamlonaghl, $send_date_to, $hamlonaghl];
            $massg_dev = str_replace($short_dev, $var_dev, $tooldev);
            $show = "<div class=\"tooltiip\">" . $icon . "<span class=\"tooltiiptext\">" . $code . "</span></div>";
            $icondeliver = "<div class=\"tooltiip\">" . $deliver . "<span class=\"tooltiiptext\">" . $massg_dev . "</span></div>";
            $chstatus1 = isset($setting["chstatus1"]) ? esc_attr($setting["chstatus1"]) : "";
            $status1 = !empty($setting["status1"]) ? explode(",", esc_attr($setting["status1"])) : [];
            $chstatus2 = isset($setting["chstatus2"]) ? esc_attr($setting["chstatus2"]) : "";
            $status2 = !empty($setting["status2"]) ? explode(",", esc_attr($setting["status2"])) : [];
            $chstatus3 = isset($setting["chstatus3"]) ? esc_attr($setting["chstatus3"]) : "";
            $status3 = !empty($setting["status3"]) ? explode(",", esc_attr($setting["status3"])) : [];
            $chstatus4 = isset($setting["chstatus4"]) ? esc_attr($setting["chstatus4"]) : "";
            $status4 = !empty($setting["status4"]) ? explode(",", esc_attr($setting["status4"])) : [];
            $chstatus5 = isset($setting["chstatus5"]) ? esc_attr($setting["chstatus5"]) : "";
            $status5 = !empty($setting["status5"]) ? explode(",", esc_attr($setting["status5"])) : [];
            if($chstatus2 == "checked" && is_array($status2) && in_array($order->get_status(), str_replace("wc-", "", $status2)) || $order->get_status() == "on-hold") {
                echo $hold;
            } elseif($chstatus3 == "checked" && is_array($status3) && in_array($order->get_status(), str_replace("wc-", "", $status3)) || $order->get_status() == "box") {
                echo $packing;
            } elseif($chstatus5 == "checked" && is_array($status5) && in_array($order->get_status(), str_replace("wc-", "", $status5)) || $order->get_status() == "deliver") {
                echo $icondeliver;
            } elseif($chstatus1 == "checked" && is_array($status1) && in_array($order->get_status(), str_replace("wc-", "", $status1)) || $order->get_status() == "processing") {
                echo $recive;
            } elseif($chstatus4 == "checked" && is_array($status4) && in_array($order->get_status(), str_replace("wc-", "", $status4)) || $order->get_status() == "completed") {
                echo $show;
            }
        }
    } else {
        echo "-";
    }
}
function novin_display_order_data_in_admin($order)
{
    echo "    <div class=\"order_data_column\">\r\n    <h4>";
    _e("جزییات ارسال");
    echo "</h4>\r\n    ";
    $setting = get_option("novin_track_order");
    $masir = get_option("novin_shipping_masir_tapin");
    $tapn_masir = isset($masir["tapin"]) ? $masir["tapin"] : "";
    $city_peyk = !empty($setting["city_peyk"]) ? explode(",", $setting["city_peyk"]) : [];
    $shipping_peyk = !empty($setting["shipping_peyk"]) ? explode(",", $setting["shipping_peyk"]) : [];
    $ships = "";
    if(class_exists("PWS_VERSION")) {
        foreach ($order->get_items("shipping") as $item_id => $item) {
            if($item->get_method_id() == "novin_shipping_force" || $item->get_method_id() == "novin_person_delivery") {
                $ships = $item->get_method_id();
            } else {
                $ships = $item->get_method_id() . ":" . $item->get_instance_id();
            }
        }
    } else {
        foreach ($order->get_items("shipping") as $item_id => $item) {
            $ships = $item->get_method_id();
        }
    }
    $tapin = get_option("pws_tapin");
    if(class_exists("novin_advance_shipping_masir") && $tapn_masir == "checked" || class_exists("PWS_Tapin") && isset($tapin) && isset($tapin["enable"]) && !empty($tapin) && !empty($tapin["enable"]) && $tapin["enable"] == 1 || class_exists("PWS_VERSION")) {
        $city = $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city();
        if(empty($city)) {
            $city = $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city();
        }
    } else {
        $city = $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city();
    }
    $marsule = $order->get_meta("marsule");
    $shipp = $order->get_meta("shipp");
    $date = $order->get_meta("datei");
    $deliverydate = $order->get_meta("datedeliver");
    $mobile_peyk = $order->get_meta("novin_peyk_mobile");
    if(!in_array($ships, $shipping_peyk) || !in_array($city, $city_peyk)) {
        echo "<p><strong>کد رهگیری :</strong><br> ";
        echo $marsule;
        echo "</p>\r\n\r\n";
    }
    if(in_array($ships, $shipping_peyk) && in_array($city, $city_peyk)) {
        echo "\r\n<p><strong>شماره تماس پیک :</strong><br> ";
        echo $mobile_peyk;
        echo "</p>\r\n\r\n\r\n";
    }
    echo "<p><strong>توسط :</strong> ";
    echo $shipp;
    echo "</p>\r\n<p><strong>تاریخ ارسال :</strong> ";
    echo $date;
    echo "</p>\r\n<p><strong>تاریخ تحویل :</strong> ";
    echo $deliverydate;
    echo "</p>\r\n    </div>\r\n";
}
function novin_track_order_change_status_tapin($order_id, $old_status, $new_status, $order)
{
    $setting = get_option("novin_track_order");
    $tapin = isset($setting["tapin"]) ? esc_attr($setting["tapin"]) : "";
    $date = isset($setting["tapin_date"]) ? esc_attr($setting["tapin_date"]) : "";
    $post = isset($setting["tapin_post"]) ? esc_attr($setting["tapin_post"]) : "";
    $form = isset($setting["form_post"]) ? esc_attr($setting["form_post"]) : "";
    $marsule = $order->get_meta("marsule");
    $barcode = $order->get_meta("post_barcode");
    $status_peyk = !empty($setting["status_peyk"]) ? str_replace("wc-", "", $setting["status_peyk"]) : "completed";
    if($new_status == $status_peyk) {
        add_filter("persian_woo_sms_content", "novin_track_order_replace_peyk_shortcodem", 99, 4);
    }
    if($tapin == "checked" && empty($marsule) && !empty($barcode)) {
        $order->update_meta_data("marsule", esc_attr($barcode));
        $get_date = $order->get_meta("datei");
        $get_post = $order->get_meta("shipp");
        $get_form = $order->get_meta("apostrahgir");
        if(empty($get_date)) {
            $date_send = novinjdate("Y/m/d");
            $order->update_meta_data("datei", esc_attr($date_send));
        }
        if(empty($get_post)) {
            $order->update_meta_data("shipp", "پست پیشتاز");
        }
        if(empty($get_form)) {
            $order->update_meta_data("apostrahgir", "postrahgir");
        }
        $order->save_meta_data();
        $order->save();
    }
}
function novin_dokan_deliver_order()
{
    if(!is_admin()) {
        exit;
    }
    if(!current_user_can("dokandar") || "on" !== dokan_get_option("order_status_change", "dokan_selling", "on")) {
        wp_die(esc_html__("You do not have sufficient permissions to access this page.", "dokan-lite"));
    }
    if(!check_admin_referer("dokan-mark-order-deliver")) {
        wp_die(esc_html__("You have taken too long. Please go back and retry.", "dokan-lite"));
    }
    $order_id = !empty($_GET["order_id"]) ? (int) $_GET["order_id"] : 0;
    if(!$order_id) {
        exit;
    }
    if(!dokan_is_seller_has_order(dokan_get_current_user_id(), $order_id)) {
        wp_die(esc_html__("You do not have permission to change this order", "dokan-lite"));
    }
    $order = dokan()->order->get($order_id);
    $order->update_status("deliver");
    wp_safe_redirect(wp_get_referer());
    exit;
}
function deliver_icon_dokan_balk($actions, $order)
{
    if(!$order->get_status()[["deliver" => true]]) {
        $deliver = isset($setting["deliver"]) ? esc_attr($setting["delicer"]) : "";
        if(empty($deliver)) {
            $deliver = "تحویل شده";
        }
        $actions["box"] = ["url" => wp_nonce_url(admin_url("admin-ajax.php?action=dokan-mark-order-deliver&order_id=" . $order->get_id()), "dokan-mark-order-deliver"), "name" => __($deliver, "dokan-lite"), "action" => "box", "icon" => "<i class=\"fa-solid fa-truck-ramp-box\">&nbsp;</i>"];
    }
    return $actions;
}
function novin_dokan_box_order()
{
    if(!is_admin()) {
        exit;
    }
    if(!current_user_can("dokandar") || "on" !== dokan_get_option("order_status_change", "dokan_selling", "on")) {
        wp_die(esc_html__("You do not have sufficient permissions to access this page.", "dokan-lite"));
    }
    if(!check_admin_referer("dokan-mark-order-box")) {
        wp_die(esc_html__("You have taken too long. Please go back and retry.", "dokan-lite"));
    }
    $order_id = !empty($_GET["order_id"]) ? (int) $_GET["order_id"] : 0;
    if(!$order_id) {
        exit;
    }
    if(!dokan_is_seller_has_order(dokan_get_current_user_id(), $order_id)) {
        wp_die(esc_html__("You do not have permission to change this order", "dokan-lite"));
    }
    $order = dokan()->order->get($order_id);
    $order->update_status("box");
    wp_safe_redirect(wp_get_referer());
    exit;
}
function baste_icon_dokan_balk($actions, $order)
{
    if(!$order->get_status()[["box" => true]]) {
        $box = isset($setting["box"]) ? esc_attr($setting["box"]) : "";
        if(empty($box)) {
            $box = "بسته بندی";
        }
        $actions["box"] = ["url" => wp_nonce_url(admin_url("admin-ajax.php?action=dokan-mark-order-box&order_id=" . $order->get_id()), "dokan-mark-order-box"), "name" => __($box, "dokan-lite"), "action" => "box", "icon" => "<i class=\"fa-solid fa-box\">&nbsp;</i>"];
    }
    return $actions;
}
function novin_track_add_account_orders_column($columns)
{
    $columns["custom-column"] = __("جزییات ارسال", "woocommerce", 99, 1);
    return $columns;
}
function novin_user_custom_column_icon($order)
{
    $setting = get_option("novin_track_order");
    $order_items = $order->get_items();
    $virtual = isset($setting["virtual"]) ? esc_attr($setting["virtual"]) : "";
    if(!empty($order_items)) {
        foreach ($order_items as $item_id => $item) {
            $product_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
            $product = wc_get_product($product_id);
            if(empty($product)) {
                return NULL;
            }
            if($virtual == "checked" && ($product->is_downloadable() || $product->is_virtual())) {
                return NULL;
            }
        }
    }
    $setting_icon = get_option("novin_icon_track");
    $masir = get_option("novin_shipping_masir_tapin");
    $tapn_masir = isset($masir["tapin"]) ? $masir["tapin"] : "";
    $city_peyk = !empty($setting["city_peyk"]) ? explode(",", $setting["city_peyk"]) : [];
    $shipping_peyk = !empty($setting["shipping_peyk"]) ? explode(",", $setting["shipping_peyk"]) : [];
    $tapin = get_option("pws_tapin");
    if(class_exists("novin_advance_shipping_masir") && $tapn_masir == "checked" || class_exists("PWS_Tapin") && isset($tapin) && isset($tapin["enable"]) && !empty($tapin) && !empty($tapin["enable"]) && $tapin["enable"] == 1 || class_exists("PWS_VERSION")) {
        $city = $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city();
        if(empty($city)) {
            $city = $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city();
        }
    } else {
        $city = $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city();
    }
    $tootip = !empty($setting["tooltip"]) ? esc_attr($setting["tooltip"]) : "سفارش شماره {order_id} با کد پیگیری {marsule} توسط {hamlonaghl} در تاریخ {senddate} ارسال گردید";
    $ships = "";
    if(class_exists("PWS_VERSION")) {
        foreach ($order->get_items("shipping") as $item_id => $item) {
            if($item->get_method_id() == "novin_shipping_force" || $item->get_method_id() == "novin_person_delivery") {
                $ships = $item->get_method_id();
            } else {
                $ships = $item->get_method_id() . ":" . $item->get_instance_id();
            }
        }
    } else {
        foreach ($order->get_items("shipping") as $item_id => $item) {
            $ships = $item->get_method_id();
        }
    }
    if(in_array($city, $city_peyk) && in_array($ships, $shipping_peyk)) {
        $tootip = !empty($setting["peyk_tool"]) ? esc_attr($setting["peyk_tool"]) : "سفارش شماره {order_id} توسط {peyk} در تاریخ {senddate} ارسال گردید";
    }
    $hamlonaghl = $order->get_meta("shipp");
    $codemi = "<mark style = \" background: none;font-weight: 600;color: #f9af08;\">" . $order->get_meta("marsule") . "</mark>";
    $ordid_id = $order->get_order_number();
    $send_date_to = $order->get_meta("datei");
    $shortcode = ["{order_id}", "{marsule}", "{hamlonaghl}", "{senddate}", "{peyk}"];
    $variable = [$ordid_id, $codemi, $hamlonaghl, $send_date_to, $hamlonaghl];
    $massg = $tootip;
    $code = str_replace($shortcode, $variable, $massg);
    $icon = isset($setting_icon["logon4"]) ? "<img src =" . esc_attr($setting_icon["logon4"]) . ">" : "<img style = \"cursor: pointer\" id = \"p1\"  src =" . order_traking_assets_url . "/logo/order-traking.jpg" . ">";
    $show = "<div class=\"tooltiip\">" . $icon . "<span class=\"tooltiiptext\">" . $code . "</span></div>";
    $ccc = $order->get_meta("marsule");
    $packing = isset($setting_icon["logon3"]) ? "<img src =" . esc_attr($setting_icon["logon3"]) . ">" : "<img src =" . order_traking_assets_url . "logo/packing.png>";
    $deliver = isset($setting_icon["logon5"]) ? "<img src =" . esc_attr($setting_icon["logon5"]) . ">" : "<img src =" . order_traking_assets_url . "logo/deliver.png>";
    $recive = isset($setting_icon["logon1"]) ? "<img src =" . esc_attr($setting_icon["logon1"]) . ">" : "<img src =" . order_traking_assets_url . "logo/recive.png>";
    $hold = isset($setting_icon["logon2"]) ? "<img src =" . esc_attr($setting_icon["logon2"]) . ">" : "<img src =" . order_traking_assets_url . "logo/onhold.png>";
    $chstatus1 = isset($setting["chstatus1"]) ? esc_attr($setting["chstatus1"]) : "";
    $status1 = !empty($setting["status1"]) ? explode(",", esc_attr($setting["status1"])) : [];
    $chstatus2 = isset($setting["chstatus2"]) ? esc_attr($setting["chstatus2"]) : "";
    $status2 = !empty($setting["status2"]) ? explode(",", esc_attr($setting["status2"])) : [];
    $chstatus3 = isset($setting["chstatus3"]) ? esc_attr($setting["chstatus3"]) : "";
    $status3 = !empty($setting["status3"]) ? explode(",", esc_attr($setting["status3"])) : [];
    $chstatus4 = isset($setting["chstatus4"]) ? esc_attr($setting["chstatus4"]) : "";
    $status4 = !empty($setting["status4"]) ? explode(",", esc_attr($setting["status4"])) : [];
    $chstatus5 = isset($setting["chstatus5"]) ? esc_attr($setting["chstatus5"]) : "";
    $status5 = !empty($setting["status5"]) ? explode(",", esc_attr($setting["status5"])) : [];
    if($chstatus4 == "checked" && is_array($status4) && in_array($order->get_status(), str_replace("wc-", "", $status4)) || $order->get_status() == "completed") {
        echo $show;
    } elseif($chstatus2 == "checked" && is_array($status2) && in_array($order->get_status(), str_replace("wc-", "", $status2)) || $order->get_status() == "on-hold") {
        echo $hold;
    } elseif($chstatus3 == "checked" && is_array($status3) && in_array($order->get_status(), str_replace("wc-", "", $status3)) || $order->get_status() == "box") {
        echo $packing;
    } elseif($chstatus5 == "checked" && is_array($status5) && in_array($order->get_status(), str_replace("wc-", "", $status5)) || $order->get_status() == "deliver") {
        echo $deliver;
    } elseif($chstatus1 == "checked" && is_array($status1) && in_array($order->get_status(), str_replace("wc-", "", $status1)) || $order->get_status() == "processing") {
        echo $recive;
    }
}
function novin_tcg_tracking_callback($order)
{
    echo "<style>\r\n     .buttonnovinord {\r\n  background-color: #23d3cf!important;\r\n  border: none!important;\r\n  color: white!important;\r\n  padding: 12px 27px!important;\r\n  text-align: center!important;\r\n  text-decoration: none!important;\r\n  font-size: 16px!important;\r\n  margin-left: auto!important;\r\n     margin-right: auto!important;\r\n    display: block!important;\r\n  cursor: pointer!important;\r\n      border-radius: 6px!important;\r\n}\r\n</style>\r\n\r\n\r\n";
    if(get_option("woocommerce_custom_orders_table_enabled") != "yes") {
        $order = wc_get_order($order);
    }
    wp_enqueue_script("my-script-novin", order_traking_assets_url . "js/persian-datepicker.js");
    wp_enqueue_style("novin_wp_admin_css", order_traking_assets_url . "css/persiandate.css");
    $marsule = $order->get_meta("marsule");
    $shipp = $order->get_meta("shipp");
    $date = $order->get_meta("datei");
    $rahgir = $order->get_meta("apostrahgir");
    $datedeliver = $order->get_meta("datedeliver");
    $mobile_peyk = $order->get_meta("novin_peyk_mobile");
    $setting = get_option("novin_track_order");
    $masir = get_option("novin_shipping_masir_tapin");
    $tapn_masir = isset($masir["tapin"]) ? $masir["tapin"] : "";
    $tapin = get_option("pws_tapin");
    if(class_exists("novin_advance_shipping_masir") && $tapn_masir == "checked" || class_exists("PWS_Tapin") && isset($tapin) && isset($tapin["enable"]) && !empty($tapin) && !empty($tapin["enable"]) && $tapin["enable"] == 1 || class_exists("PWS_VERSION")) {
        $city = $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city();
        if(empty($city)) {
            $city = $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city();
        }
    } else {
        $city = $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city();
    }
    $city_peyk = !empty($setting["city_peyk"]) ? explode(",", $setting["city_peyk"]) : [];
    $shipping_peyk = !empty($setting["shipping_peyk"]) ? explode(",", $setting["shipping_peyk"]) : [];
    $ships = "";
    if(class_exists("PWS_VERSION")) {
        foreach ($order->get_items("shipping") as $item_id => $item) {
            if($item->get_method_id() == "novin_shipping_force" || $item->get_method_id() == "novin_person_delivery") {
                $ships = $item->get_method_id();
            } else {
                $ships = $item->get_method_id() . ":" . $item->get_instance_id();
            }
        }
    } else {
        foreach ($order->get_items("shipping") as $item_id => $item) {
            $ships = $item->get_method_id();
        }
    }
    if(!in_array($ships, $shipping_peyk) || !in_array($city, $city_peyk)) {
        echo "<p>\r\n<label\tfor=\"marsule\">درج کد رهگیری :</label>\r\n<br />\r\n <input type=\"text\" autocomplete=\"off\" style= \"width:100%; background: #dce5fb\" name=\"marsule\" id=\"shipp\" value=\"" . $marsule . "\" /></p>";
    }
    echo "<p>\r\n<label\tfor=\"shipp\">نوع سیستم حمل و نقل :</label>\r\n\t\t\t\t\t<br />\r\n<input type=\"text\" style= \"width:100%;  background: #dce5fb\" name=\"shipp\" id=\"shipp\" value=\"" . $shipp . "\" /></p>";
    if(in_array($ships, $shipping_peyk) && in_array($city, $city_peyk)) {
        echo "<label\tfor=\"mobile_peyk\">شماره تماس پیک :</label>\r\n\t\t\t\t\t<br />\r\n<input type=\"text\" style= \"width:100%;  background: #dce5fb\" name=\"mobile_peyk\" id=\"mobile-peyk\" value=\"" . $mobile_peyk . "\" /></p>";
    }
    echo "<p>\r\n<label\tfor=\"dateim\">تاریخ ارسال :</label>\r\n\t\t\t\t\t<br/>\r\n<script type=\"text/javascript\">\r\njQuery(document).ready(function(\$) {\r\n    \$(\".datepiko,.datedeliver\").persianDatepicker({\r\n    initialValue: false,\r\n       cellWidth: 32,\r\n        cellHeight: 30,\r\n        fontSize: 14,\r\n    });\r\n  });\r\n</script>\r\n<input type=\"text\" autocomplete=\"off\" style= \"width:100%; background: #dce5fb;\" class = \"datepiko\" name=\"dateim\" id=\"dateim\" value=\"" . $date . "\" /></p>";
    if(!in_array($ships, $shipping_peyk) || !in_array($city, $city_peyk)) {
        echo "<p>\r\n<label for=\"formrahgir\">انتخاب فرم رهگیری پست:</label>\r\n   </br>\r\n<select name=\"formrahgir\" id=\"formpost\" style= \"width:100%;font-weight: 500;background-color:#dce5fb\"/> \r\n<option >هیچکدام</option>\r\n<option  value=\"postrahgir\">فرم رهگیری از پست</option>\r\n<option value=\"chaparrahgir\">فرم رهگیری از چاپار</option>\r\n<option value=\"tipax\">دکمه پیگیری از تیپاکس</option>\r\n</select>";
    }
    echo "<p>\r\n<label\tfor=\"datedeliver\">تاریخ تحویل :</label>\r\n\t\t\t\t\t<br/>\r\n\r\n<input type=\"text\" autocomplete=\"off\" style= \"width:100%; background: #dce5fb;\" class = \"datedeliver\" name=\"datedeliver\" id=\"datedeliver\" value=\"" . $datedeliver . "\" /></p>\r\n<input type=\"checkbox\" name=\"check-complete\"  value=\"1\"/ >\r\n<label for=\"check-complete\">اگر میخواهید وضعیت محصول تکمیل شده و پیامک ارسال شود تیک را بزنید</label></br>\r\n<input type=\"checkbox\" name=\"deliverycheck\"  value=\"\"/ >\r\n<label for=\"deliverycheck\">اگر میخواهید وضعیت محصول تحویل شده و پیامک ارسال شود تیک را بزنید</label><br/>\r\n<label style=\"color:red\"; for=\"check-complete\">نکته:اگرچک باکس های بالا فعال باشد وضعیت سفارش را دستی انتخاب نکنید!</label></br></p>\r\n<button  class=\"buttonnovinord\">ذخیره اطلاعات</button>";
    if(!empty($rahgir)) {
        echo "\r\n    <script type=\"text/javascript\">\r\ndocument.getElementById(\"formpost\").value = \"" . $rahgir . "\" </script>";
    }
}
function novin_tracking_box()
{
    add_meta_box("tcg-tracking-modal", "اطلاعات ارسال مرسوله", "novin_tcg_tracking_callback", ["shop_order", wc_get_page_screen_id("shop-order")], "side", "high");
}
function add_order_email_instructio_track_order($order, $sent_to_admin)
{
    $marsule = $order->get_meta("marsule");
    $replacehaml = $order->get_meta("shipp");
    if(!$sent_to_admin && !empty($marsule) && !empty($replacehaml)) {
        $emailcode = "کد پیگیری مرسوله : " . $marsule . PHP_EOL . "ارسال شده با :" . $replacehaml;
        $emailcode = nl2br($emailcode);
        echo $emailcode;
    }
}
function novin_track_order_replace_peyk_shortcodem($content, $order_id, $order, $product_ids)
{
    $setting = get_option("novin_track_order");
    $masir = get_option("novin_shipping_masir_tapin");
    $tapn_masir = isset($masir["tapin"]) ? $masir["tapin"] : "";
    $city_peyk = !empty($setting["city_peyk"]) ? explode(",", $setting["city_peyk"]) : [];
    $shipping_peyk = !empty($setting["shipping_peyk"]) ? explode(",", $setting["shipping_peyk"]) : [];
    $ships = "";
    if(class_exists("PWS_VERSION")) {
        foreach ($order->get_items("shipping") as $item_id => $item) {
            if($item->get_method_id() == "novin_shipping_force" || $item->get_method_id() == "novin_person_delivery") {
                $ships = $item->get_method_id();
            } else {
                $ships = $item->get_method_id() . ":" . $item->get_instance_id();
            }
        }
    } else {
        foreach ($order->get_items("shipping") as $item_id => $item) {
            $ships = $item->get_method_id();
        }
    }
    $tapin = get_option("pws_tapin");
    if(class_exists("novin_advance_shipping_masir") && $tapn_masir == "checked" || class_exists("PWS_Tapin") && isset($tapin) && isset($tapin["enable"]) && !empty($tapin) && !empty($tapin["enable"]) && $tapin["enable"] == 1 || class_exists("PWS_VERSION")) {
        $city = $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city();
        if(empty($city)) {
            $city = $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city();
        }
    } else {
        $city = $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city();
    }
    $status4 = !empty($setting["status4"]) ? explode(",", esc_attr($setting["status4"])) : [];
    $status_peyk = !empty($setting["status_peyk"]) ? str_replace("wc-", "", $setting["status_peyk"]) : "completed";
    if(!empty($setting["sms_replace"]) && in_array($ships, $shipping_peyk) && in_array($city, $city_peyk) && $order->get_status() == $status_peyk) {
        $sms_peyk = isset($setting["sms_replace"]) ? esc_attr($setting["sms_replace"]) : "سلام {b_first_name} {b_last_name}\r\nسفارش {order_id} با محصولات  {all_items} توسط {peyk} ارسال شد.\r\nشماره تماس پیک : {mobile_peyk}";
        $name = $order->get_shipping_first_name() ? $order->get_shipping_first_name() : $order->get_billing_first_name();
        $last_name = $order->get_shipping_last_name() ? $order->get_shipping_last_name() : $order->get_billing_last_name();
        $shipp = $order->get_meta("shipp");
        $shortcode = ["{b_last_name}", "{b_first_name}", "{mobile_peyk}", "{order_id}", "{peyk}", "{all_items}"];
        $product_names = [];
        foreach ($order->get_items() as $item_id => $item) {
            $product_names[] = $item->get_name();
        }
        $product_item = implode(" و ", $product_names);
        $replace = [$name, $last_name, $order->get_meta("novin_peyk_mobile"), $order->get_id(), $shipp, $product_item];
        $setshortcode = str_replace($shortcode, $replace, $sms_peyk);
        $setshortcode = str_replace($content, $setshortcode, $content);
        return $setshortcode;
    } else {
        return $content;
    }
}
function novin_track_order_replace_shortcodem($content, $order_id, $order, $product_ids)
{
    $senddate = $order->get_meta("datei");
    $datedelivery = $order->get_meta("datedeliver");
    $marsule = $order->get_meta("marsule");
    $hamlonaghl = $order->get_meta("shipp");
    $shortcode = ["{marsule}", "{hamlonaghl}", "{deliverydate}", "{senddate}"];
    $replace = [$marsule, $hamlonaghl, $datedelivery, $senddate];
    $setshortcode = str_replace($shortcode, $replace, $content);
    return $setshortcode;
}
function novin_change_status_checkbox_admin($post_id)
{
    $order = wc_get_order($post_id);
    if(isset($_POST["check-complete"])) {
        $order->update_status("completed");
    }
    if(isset($_POST["deliverycheck"])) {
        $order->update_status("deliver");
    }
}
function novin_data_save_general_details($ord_id)
{
    $order = wc_get_order($ord_id);
    if(!empty($_POST["marsule"])) {
        $order->update_meta_data("marsule", wc_clean($_POST["marsule"]));
    } else {
        $order->delete_meta_data("marsule");
    }
    if(!empty($_POST["shipp"])) {
        $order->update_meta_data("shipp", wc_clean($_POST["shipp"]));
    } else {
        $order->delete_meta_data("shipp");
    }
    if(!empty($_POST["dateim"])) {
        $order->update_meta_data("datei", wc_clean($_POST["dateim"]));
    } else {
        $order->delete_meta_data("datei");
    }
    if(!empty($_POST["formrahgir"]) && $_POST["formrahgir"] != "هیچکدام") {
        $order->update_meta_data("apostrahgir", wc_clean($_POST["formrahgir"]));
    } elseif(!empty($_POST["formrahgir"]) && $_POST["formrahgir"] == "هیچکدام") {
        $order->delete_meta_data("apostrahgir");
    } else {
        $order->delete_meta_data("apostrahgir");
    }
    if(!empty($_POST["datedeliver"])) {
        $order->update_meta_data("datedeliver", wc_clean($_POST["datedeliver"]));
    } else {
        $order->delete_meta_data("datedeliver");
    }
    if(!empty($_POST["mobile_peyk"])) {
        $order->update_meta_data("novin_peyk_mobile", wc_clean($_POST["mobile_peyk"]));
    } else {
        $order->delete_meta_data("novin_peyk_mobile");
    }
    $order->save_meta_data();
    $order->save();
}
function view_order_novin_payment_instruction($order)
{
    $setting = get_option("novin_track_order");
    $navar_user = isset($setting["navar_user"]) ? esc_attr($setting["navar_user"]) : "";
    $navar_checkout = isset($setting["navar_checkout"]) ? esc_attr($setting["navar_checkout"]) : "";
    if(class_exists("woocommerce") && ($navar_user == "checked" && !is_checkout() || $navar_checkout == "checked" && is_checkout())) {
        return NULL;
    }
    echo "<style>\r\n ol li {\r\n    list-style: none!important;\r\n\r\n}\r\n</style>\r\n\r\n";
    $ver_num = mt_rand();
    wp_enqueue_style("style-form", order_traking_assets_url . "css/style-form.css?v=" . $ver_num, "all");
    $setting = get_option("novin_track_order");
    $style_shekast = isset($setting["style_shekast"]) ? esc_attr($setting["style_shekast"]) : "";
    if($style_shekast == "checked") {
        echo "\r\n<style>\r\n    \r\n    ol.progresss[data-stepss=\"3\"] li {\r\n \r\n    line-height: 0px!important;\r\n\t\r\n}\r\n\r\n.progresss .names {\r\n    \r\n     margin-bottom: 20px!important;\r\n\t\r\n}\r\n    \r\n</style>\r\n\r\n\r\n";
    }
    $setting_icon = get_option("novin_icon_track");
    $navar_color = isset($setting["navarcolor"]) ? esc_attr($setting["navarcolor"]) : "";
    $chdev = isset($setting["ch_dev"]) ? esc_attr($setting["ch_dev"]) : "";
    $time_check = isset($setting["time_check"]) ? esc_attr($setting["time_check"]) : "";
    $process = isset($setting["processing"]) ? esc_attr($setting["processing"]) : "";
    $hold = isset($setting["onhold"]) ? esc_attr($setting["onhold"]) : "";
    $com = isset($setting["complete"]) ? esc_attr($setting["complete"]) : "";
    $dev = isset($setting["deliver"]) ? esc_attr($setting["deliver"]) : "";
    $box = isset($setting["box"]) ? esc_attr($setting["box"]) : "";
    $masir = get_option("novin_shipping_masir_tapin");
    $tapn_masir = isset($masir["tapin"]) ? $masir["tapin"] : "";
    $city_peyk = !empty($setting["city_peyk"]) ? explode(",", $setting["city_peyk"]) : [];
    $shipping_peyk = !empty($setting["shipping_peyk"]) ? explode(",", $setting["shipping_peyk"]) : [];
    $ships = "";
    if(class_exists("PWS_VERSION")) {
        foreach ($order->get_items("shipping") as $item_id => $item) {
            if($item->get_method_id() == "novin_shipping_force" || $item->get_method_id() == "novin_person_delivery") {
                $ships = $item->get_method_id();
            } else {
                $ships = $item->get_method_id() . ":" . $item->get_instance_id();
            }
        }
    } else {
        foreach ($order->get_items("shipping") as $item_id => $item) {
            $ships = $item->get_method_id();
        }
    }
    $tapin = get_option("pws_tapin");
    if(class_exists("novin_advance_shipping_masir") && $tapn_masir == "checked" || class_exists("PWS_Tapin") && isset($tapin) && isset($tapin["enable"]) && !empty($tapin) && !empty($tapin["enable"]) && $tapin["enable"] == 1 || class_exists("PWS_VERSION")) {
        $city = $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city();
        if(empty($city)) {
            $city = $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city();
        }
    } else {
        $city = $order->get_shipping_city() ? $order->get_shipping_city() : $order->get_billing_city();
    }
    $tootip = !empty($setting["tooltip"]) ? esc_attr($setting["tooltip"]) : "سفارش شماره {order_id} با کد پیگیری {marsule} توسط {hamlonaghl} در تاریخ {senddate} ارسال گردید";
    $tooldev = !empty($setting["tooldev"]) ? esc_attr($setting["tooldev"]) : "سفارش شماره {order_id} در تاریخ {deliverydate} با کد پیگیری {marsule} تحویل مشتری گردید";
    if(in_array($city, $city_peyk) && in_array($ships, $shipping_peyk)) {
        $tooldev = !empty($setting["peyk_tool_dev"]) ? esc_attr($setting["peyk_tool_dev"]) : "سفارش شماره {order_id} در تاریخ {deliverydate}  تحویل مشتری گردید";
        $tootip = !empty($setting["peyk_tool"]) ? esc_attr($setting["peyk_tool"]) : "سفارش شماره {order_id} توسط {peyk} در تاریخ {senddate} ارسال گردید";
    }
    $processing = isset($setting_icon["logon1"]) ? "<img src =" . esc_attr($setting_icon["logon1"]) . ">" : "<img   src =" . order_traking_assets_url . "logo/recive.png>";
    $completed = isset($setting_icon["logon4"]) ? "<img src =" . esc_attr($setting_icon["logon4"]) . ">" : "<img style = \"cursor: pointer;margin-top: -3px;\"  src =" . order_traking_assets_url . "logo/truck.png>";
    $onhold = isset($setting_icon["logon2"]) ? "<img src =" . esc_attr($setting_icon["logon2"]) . ">" : "<img src =" . order_traking_assets_url . "logo/clock.png>";
    $packing = isset($setting_icon["logon3"]) ? "<img src =" . esc_attr($setting_icon["logon3"]) . ">" : "<img src =" . order_traking_assets_url . "logo/packing.png>";
    $deliver = isset($setting_icon["logon5"]) ? "<img src =" . esc_attr($setting_icon["logon5"]) . ">" : "<img src =" . order_traking_assets_url . "logo/deliver.png>";
    $send_date_to = $order->get_meta("datei");
    $hamlonaghl = $order->get_meta("shipp");
    $codemi = "<mark style = \" background: none;font-weight: 600;color: #f9af08;\">" . $order->get_meta("marsule");
    $ordid_id = $order->get_order_number();
    $shortcode = ["{order_id}", "{marsule}", "{hamlonaghl}", "{senddate}", "{peyk}"];
    $variable = [$ordid_id, $codemi, $hamlonaghl, $send_date_to, $hamlonaghl];
    $massg = $tootip;
    $code = str_replace($shortcode, $variable, $massg);
    if($chdev == "checked") {
        echo "<style>\r\n.progresss {\r\n    width: 100%;\r\n}\r\n</style>\r\n";
    } else {
        echo "<style>\r\n.progresss {\r\n    width: 87%;\r\n}\r\n</style>\r\n";
    }
    echo "<style>\r\n.progresss .dones .steps,\r\n.progresss.dones .steps:before,\r\n.progresss .dones .steps:after,\r\n.progresss .actives .steps,\r\n.progresss .actives .steps:before {\r\n    background-color: ";
    echo $navar_color;
    echo ";\r\n}\r\n</style>\r\n";
    if($time_check == "checked") {
        $timei = "H:i";
    }
    $time = isset($timei) ? esc_attr($timei) : "";
    $order_items = $order->get_items();
    $virtual = isset($setting["virtual"]) ? esc_attr($setting["virtual"]) : "";
    if(!empty($order_items)) {
        foreach ($order_items as $item_id => $item) {
            $product_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
            $product = wc_get_product($product_id);
            if(empty($product)) {
                return NULL;
            }
            if($virtual == "checked" && ($product->is_downloadable() || $product->is_virtual())) {
                return NULL;
            }
        }
    }
    $datedelivery = $order->get_meta("datedeliver");
    $dateprocessing = $order->get_date_created();
    $changestatus = $order->get_date_modified();
    $jalaliprocessing = novinjdate("Y/m/d " . $time, strtotime($dateprocessing));
    $jalalichangestatus = novinjdate("Y/m/d " . $time, strtotime($changestatus));
    $short_dev = ["{order_id}", "{deliverydate}", "{marsule}", "{hamlonaghl}", "{senddate}"];
    $var_dev = [$ordid_id, $datedelivery, $codemi, $hamlonaghl, $send_date_to];
    $massg_dev = str_replace($short_dev, $var_dev, $tooldev);
    $chstatus1 = isset($setting["chstatus1"]) ? esc_attr($setting["chstatus1"]) : "";
    $status1 = isset($setting["status1"]) ? explode(",", esc_attr($setting["status1"])) : [];
    $chstatus2 = isset($setting["chstatus2"]) ? esc_attr($setting["chstatus2"]) : "";
    $status2 = isset($setting["status2"]) ? explode(",", esc_attr($setting["status2"])) : [];
    $chstatus3 = isset($setting["chstatus3"]) ? esc_attr($setting["chstatus3"]) : "";
    $status3 = isset($setting["status3"]) ? explode(",", esc_attr($setting["status3"])) : [];
    $chstatus4 = isset($setting["chstatus4"]) ? esc_attr($setting["chstatus4"]) : "";
    $status4 = isset($setting["status4"]) ? explode(",", esc_attr($setting["status4"])) : [];
    $chstatus5 = isset($setting["chstatus5"]) ? esc_attr($setting["chstatus5"]) : "";
    $status5 = isset($setting["status5"]) ? explode(",", esc_attr($setting["status5"])) : [];
    if($chstatus4 == "checked" && is_array($status4) && in_array($order->get_status(), str_replace("wc-", "", $status4)) || $order->get_status() == "completed") {
        if($setting["style"] == "digi") {
            echo " <ol  class=\"diginovin\">\r\n     <li>\r\n\r\n <img class=\"size\" src=\"" . (isset($setting_icon["logon6"]) ? $setting_icon["logon6"] : order_traking_assets_url . "logo/barrasi.png") . "\">\r\n      <span class=\"namedigi\">" . $process . "</span>\r\n   </li>\r\n    <li>\r\n <img class=\"size\" src=\"" . (isset($setting_icon["logon7"]) ? $setting_icon["logon7"] : order_traking_assets_url . "logo/taeed.png") . "\">\r\n       <span class=\"namedigi\">" . $hold . "</span>\r\n   </li>\r\n    <li>\r\n     <img class=\"size\" src=\"" . (isset($setting_icon["logon8"]) ? $setting_icon["logon8"] : order_traking_assets_url . "logo/bastebandi.png") . "\">\r\n\r\n      <span class=\"namedigi\">" . $box . "</span>\r\n  \r\n    </li>\r\n     <li>\r\n          <span class=\"tooltioopdigi\"> <img class=\"size\" src=\"" . (isset($setting_icon["logon9"]) ? $setting_icon["logon9"] : order_traking_assets_url . "logo/trucksend.png") . "\"> <span  class=\"tooltiooptextstatusdigicom\">" . $code . "</span></span>\r\n          <span class=\"namedigi\">" . (!empty($com) ? $com : wc_get_order_status_name($order->get_status())) . "</span>\r\n    </li>";
            if($chdev == "checked") {
                echo "  <li> <img class=\"size filter\" src=\"" . (isset($setting_icon["logon10"]) ? $setting_icon["logon10"] : order_traking_assets_url . "logo/tahvil.png") . "\">\r\n  <span class=\"namedigi filter\">" . $dev . "</span>\r\n   </li>";
            }
            echo "</ol><br></br>";
        }
        if($setting["style"] == "novin") {
            echo "<ol class=\"progresss\" data-stepss=\"3\">\r\n    <li class=\"dones actives\">\r\n        <span class=\"names\">" . $process . "</span>\r\n        <span class=\"steps\"><span>" . $processing . "</span></span>\r\n    </li>\r\n    <li class=\"dones actives\">\r\n        <span class=\"names\">" . $hold . "</span>\r\n        <span class=\"steps\"><span>" . $onhold . "</span></span>\r\n    </li>\r\n    <li class=\"dones actives\">\r\n        <span class=\"names\">" . $box . "</span>\r\n        <span class=\"steps\"><span>" . $packing . "</span></span>\r\n    </li>\r\n    <li class=\"actives\" >\r\n        <span class=\"names\">" . (!empty($com) ? $com : wc_get_order_status_name($order->get_status())) . "</span>\r\n  <span class=\"steps\"><span class=\"borderi\"></span><div class=\"tooltioop\">" . $completed . "<span  class=\"tooltiooptext\">" . $code . "</span></span>\r\n  </li>";
            if($chdev == "checked") {
                echo " <li>\r\n        <span class=\"names\">" . $dev . "</span>\r\n        <span class=\"steps\">" . $deliver . "</span>\r\n    </li>";
            }
            echo "</ol><br></br>";
        }
        $rahgirm = $order->get_meta("apostrahgir");
        $button_text_color = isset($setting["button_text_color"]) ? esc_attr($setting["button_text_color"]) : "";
        $post_color = isset($setting["postcolor"]) ? esc_attr($setting["postcolor"]) : "";
        if($rahgirm == "tipax") {
            echo "<a href=\"https://tipaxco.com/tracking/?id=" . convertParsiToEnglish_novintrack($order->get_meta("marsule")) . "\" target=\"blank\"><input type=\"button\"\" id=\"post\" style= \"color:" . $button_text_color . ";\r\n    font-weight: 500;\r\n    width: 125px;\r\n    line-height: initial;\r\n    font-size: 14px!important;\r\n    padding: 6px!important;\r\n    border-radius: 10px;\r\n    margin-top: 18px;\r\n    background-color: " . $post_color . ";\" \r\n    value=\"پیگیری از تیپاکس\"/></a><br></br>";
        }
        if($rahgirm == "postrahgir") {
            echo "<a href=\"https://tracking.post.ir/?id=" . convertParsiToEnglish_novintrack($order->get_meta("marsule")) . " \"  target=\"_blank\" ><input type=\"button\"  target=\"_blank\" style= \"color: " . $button_text_color . ";\r\n    font-weight: 500;\r\n    width: 125px!important;\r\n    height: 37px!important;\r\n    line-height: initial!important;\r\n    font-size: 14px!important;\r\n    padding: 6px!important;\r\n    border-radius: 10px;\r\n    box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.1)!important;\r\n    margin-top: -4px;\r\n     position: absolute;\r\n    right: 11px;\r\n    background-color: " . $post_color . ";\"  value=\"پیگیری از پست\"/></a><br></br> ";
        }
        if($rahgirm == "chaparrahgir") {
            echo "<a href=\"https://chaparnet.com/track/" . convertParsiToEnglish_novintrack($order->get_meta("marsule")) . "\"  target=\"_blank\"><input type=\"button\"   style= \"color: black;\r\n    font-weight: 500;\r\n    width: 125px;\r\n    line-height: initial;\r\n    font-size: 14px!important;\r\n    padding: 6px!important;\r\n    border-radius: 10px;\r\n    margin-top: 18px;\r\n    background-color: " . $post_color . ";\"  value=\"پیگیری از چاپار\"/></a><br></br>";
        }
    } elseif($chstatus3 == "checked" && is_array($status3) && in_array($order->get_status(), str_replace("wc-", "", $status3)) || $order->get_status() == "box") {
        if($setting["style"] == "digi") {
            echo " <ol  class=\"diginovin\">\r\n     <li>\r\n\r\n <img class=\"size\" src=\"" . (isset($setting_icon["logon6"]) ? $setting_icon["logon6"] : order_traking_assets_url . "logo/barrasi.png") . "\">\r\n      <span class=\"namedigi\">" . $process . "</span>\r\n   </li>\r\n    <li>\r\n <img class=\"size\" src=\"" . (isset($setting_icon["logon7"]) ? $setting_icon["logon7"] : order_traking_assets_url . "logo/taeed.png") . "\">\r\n       <span class=\"namedigi\">" . $hold . "</span>\r\n   </li>\r\n    <li>\r\n        <span class=\"tooltioopdigi\">  <img class=\"size\" src=\"" . (isset($setting_icon["logon8"]) ? $setting_icon["logon8"] : order_traking_assets_url . "logo/bastebandi.png") . "\"><span  class=\"tooltiooptextstatusdigi\">سفارش شما در تاریخ<mark style = \" background: none;font-weight: 600;color: #f9af08;\">" . $jalalichangestatus . "</mark> در وضعیت " . isset($box) ? $box : wc_get_order_status_name($order->get_status()) . " قرار گرفت</span></span>\r\n      <span class=\"namedigi\">" . (!empty($box) ? $box : wc_get_order_status_name($order->get_status())) . "</span>\r\n  \r\n    </li>\r\n     <li>\r\n  <img class=\"size filter\" src=\"" . (isset($setting_icon["logon9"]) ? $setting_icon["logon9"] : order_traking_assets_url . "logo/trucksend.png") . "\">\r\n   <span class=\"namedigi filter\">" . $com . "</span>\r\n    </li>\r\n     ";
            if($chdev == "checked") {
                echo "<li> <img class=\"size filter\" src=\"" . (isset($setting_icon["logon10"]) ? $setting_icon["logon10"] : order_traking_assets_url . "logo/tahvil.png") . "\">\r\n  <span class=\"namedigi filter\">" . $dev . "</span>\r\n   </li>";
            }
            echo "</ol><br></br>";
        }
        if($setting["style"] == "novin") {
            echo "<ol class=\"progresss\" data-stepss=\"3\">\r\n    <li class=\"dones actives\">\r\n        <span class=\"names\">" . $process . "</span>\r\n        <span class=\"steps\"><span>" . $processing . "</span></span>\r\n    </li>\r\n       <li class=\"dones actives\">\r\n        <span class=\"names\">" . $hold . "</span>\r\n      \r\n        <span class=\"steps\"><span>" . $onhold . "</span></span>\r\n    </li>\r\n \r\n   <li class=\"actives\">\r\n      <span class=\"names\">" . (!empty($box) ? $box : wc_get_order_status_name($order->get_status())) . "</span>\r\n      \r\n        <span class=\"steps\"><span class=\"borderi\"></span><div class=\"tooltioop\">" . $packing . "<span  class=\"tooltiooptextbox\">سفارش شما در تاریخ <mark style = \" background: none;font-weight: 600;color: #f9af08;\">" . $jalalichangestatus . "</mark> در وضعیت " . (!empty($box) ? $box : wc_get_order_status_name($order->get_status())) . " قرار گرفت</div></span>\r\n    </li>\r\n    <li>\r\n        <span class=\"names\">" . $com . "</span>\r\n        <span class=\"steps\">" . $completed . "</span>\r\n  </li>";
            if($chdev == "checked") {
                echo " <li>\r\n        <span class=\"names\">" . $dev . "</span>\r\n        <span class=\"steps\">" . $deliver . "</span>\r\n    </li>";
            }
            echo "</ol><br></br>";
        }
    } elseif($chstatus1 == "checked" && is_array($status1) && in_array($order->get_status(), str_replace("wc-", "", $status1)) || $order->get_status() == "processing") {
        if($setting["style"] == "digi") {
            echo " <ol  class=\"diginovin\">\r\n     <li>\r\n  <span class=\"tooltioopdigi\">   <img class=\"size\" src=\"" . (isset($setting_icon["logon6"]) ? $setting_icon["logon6"] : order_traking_assets_url . "logo/barrasi.png") . "\"><span  class=\"tooltiooptextstatusdigi\">دریافت سفارش در تاریخ\t<mark style = \" background: none;font-weight: 600;color: #f9af08;\">" . $jalaliprocessing . "</mark></span></span>\r\n\r\n     <span class=\"namedigi\">" . (!empty($process) ? $process : wc_get_order_status_name($order->get_status())) . "</span>\r\n   </li>\r\n    <li>\r\n      <img class=\"size filter\"  src=\"" . (isset($setting_icon["logon7"]) ? $setting_icon["logon7"] : order_traking_assets_url . "logo/taeed.png") . "\">\r\n   <span class=\"namedigi filter\">" . $hold . "</span>\r\n   </li>\r\n    <li>\r\n   <img class=\"size filter\" src=\"" . (isset($setting_icon["logon8"]) ? $setting_icon["logon8"] : order_traking_assets_url . "logo/bastebandi.png") . "\">\r\n   <span class=\"namedigi filter\">" . $box . "</span>\r\n    </li>\r\n     <li>\r\n  <img class=\"size filter\" src=\"" . (isset($setting_icon["logon9"]) ? $setting_icon["logon9"] : order_traking_assets_url . "logo/trucksend.png") . "\">\r\n   <span class=\"namedigi filter\">" . $com . "</span>\r\n    </li>\r\n    ";
            if($chdev == "checked") {
                echo " <li><img class=\"size filter\" src=\"" . (isset($setting_icon["logon10"]) ? $setting_icon["logon10"] : order_traking_assets_url . "logo/tahvil.png") . "\">\r\n  <span class=\"namedigi filter\">" . $dev . "</span>\r\n   </li>";
            }
            echo "</ol><br></br>";
        }
        if($setting["style"] == "novin") {
            echo "<ol class=\"progresss\" data-stepss=\"3\">\r\n    <li class=\"actives\" class=\"dones\">\r\n        <span class=\"names\">" . (!empty($process) ? $process : wc_get_order_status_name($order->get_status())) . "</span>\r\n          \r\n        <span class=\"steps\"><span class=\"borderi\"></span><div class=\"tooltioop\">" . $processing . "<span  class=\"tooltiooptextstatus\">دریافت سفارش در تاریخ\t<mark style = \" background: none;font-weight: 600;color: #f9af08;\">" . $jalaliprocessing . "</mark></div></span>\r\n        \r\n     </li>\r\n     <li>\r\n        <span class=\"names\">" . $hold . "</span>\r\n        <span class=\"steps\"><span>" . $onhold . "</span></span>\r\n    </li>\r\n    <li>\r\n        <span class=\"names\">" . $box . "</span>\r\n        <span class=\"steps\"><span>" . $packing . "</span></span>\r\n    </li>\r\n    <li>\r\n        <span class=\"names\">" . $com . "</span>\r\n        <span class=\"steps\">" . $completed . "</span>\r\n  </li>";
            if($chdev == "checked") {
                echo " <li>\r\n        <span class=\"names\">" . $dev . "</span>\r\n        <span class=\"steps\">" . $deliver . "</span>\r\n    </li>";
            }
            echo "</ol><br></br>";
        }
    } elseif($chstatus2 == "checked" && is_array($status2) && in_array($order->get_status(), str_replace("wc-", "", $status2)) || $order->get_status() == "on-hold") {
        if($setting["style"] == "digi") {
            echo " <ol  class=\"diginovin\">\r\n     <li>\r\n\r\n <img class=\"size\" src=\"" . (isset($setting_icon["logon6"]) ? $setting_icon["logon6"] : order_traking_assets_url . "logo/barrasi.png") . "\">\r\n      <span class=\"namedigi\">" . $process . "</span>\r\n   </li>\r\n    <li>\r\n     <span class=\"tooltioopdigi\">  <img class=\"size\" src=\"" . (isset($setting_icon["logon7"]) ? $setting_icon["logon7"] : order_traking_assets_url . "logo/taeed.png") . "\"><span class=\"tooltiooptextstatusdigi\">سفارش شما در تاریخ\t<mark style = \" background: none;font-weight: 600;color: #f9af08;\">" . $jalaliprocessing . "</mark>در وضعیت " . (!empty($hold) ? $hold : wc_get_order_status_name($order->get_status())) . " قرار گرفت</span></span>\r\n      <span class=\"namedigi\">" . (!empty($hold) ? $hold : wc_get_order_status_name($order->get_status())) . "</span>\r\n   \r\n   </li>\r\n    <li>\r\n   <img class=\"size filter\" src=\"" . (isset($setting_icon["logon8"]) ? $setting_icon["logon8"] : order_traking_assets_url . "logo/bastebandi.png") . "\">\r\n   <span class=\"namedigi filter\">" . $box . "</span>\r\n    </li>\r\n     <li>\r\n  <img class=\"size filter\" src=\"" . (isset($setting_icon["logon9"]) ? $setting_icon["logon9"] : order_traking_assets_url . "logo/trucksend.png") . "\">\r\n   <span class=\"namedigi filter\">" . $com . "</span>\r\n    </li>\r\n    ";
            if($chdev == "checked") {
                echo "<li> <img class=\"size filter\" src=\"" . (isset($setting_icon["logon10"]) ? $setting_icon["logon10"] : order_traking_assets_url . "logo/tahvil.png") . "\">\r\n  <span class=\"namedigi filter\">" . $dev . "</span>\r\n   </li>";
            }
            echo "</ol><br></br>";
        }
        if($setting["style"] == "novin") {
            echo "<ol class=\"progresss\" data-stepss=\"3\">\r\n    <li class=\"dones actives\">\r\n        <span class=\"names\">" . $process . "</span>\r\n        <span class=\"steps\"><span>" . $processing . "</span></span>\r\n    </li>\r\n       <li class=\"actives\">\r\n        <span class=\"names\">" . (!empty($hold) ? $hold : wc_get_order_status_name($order->get_status())) . "</span>\r\n        <span class=\"steps\"><span class=\"borderi\"></span><div class=\"tooltioop\">" . $onhold . "<span  class=\"tooltiooptextbox\">سفارش شما در تاریخ <mark style = \" background: none;font-weight: 600;color: #f9af08;\">" . $jalalichangestatus . "</mark> در وضعیت " . (!empty($hold) ? $hold : wc_get_order_status_name($order->get_status())) . " قرار گرفت</div></span>\r\n    </li>\r\n  <li>\r\n        <span class=\"names\">" . $box . "</span>\r\n        <span class=\"steps\"><span>" . $packing . "</span></span>\r\n    </li>\r\n    \r\n    <li>\r\n        <span class=\"names\">" . $com . "</span>\r\n        <span class=\"steps\">" . $completed . "</span>\r\n  </li>";
            if($chdev == "checked") {
                echo " <li>\r\n        <span class=\"names\">" . $dev . "</span>\r\n        <span class=\"steps\">" . $deliver . "</span>\r\n    </li>";
            }
            echo "</ol><br></br>";
        }
    } elseif($chstatus5 == "checked" && is_array($status5) && in_array($order->get_status(), str_replace("wc-", "", $status5)) || $order->get_status() == "deliver") {
        if($setting["style"] == "digi") {
            echo " <ol  class=\"diginovin\">\r\n     <li>\r\n\r\n <img class=\"size\" src=\"" . (isset($setting_icon["logon6"]) ? $setting_icon["logon6"] : order_traking_assets_url . "logo/barrasi.png") . "\">\r\n      <span class=\"namedigi\">" . $process . "</span>\r\n   </li>\r\n    <li>\r\n <img class=\"size\" src=\"" . (isset($setting_icon["logon7"]) ? $setting_icon["logon7"] : order_traking_assets_url . "logo/taeed.png") . "\">\r\n       <span class=\"namedigi\">" . $hold . "</span>\r\n   </li>\r\n    <li>\r\n     <img class=\"size\" src=\"" . (isset($setting_icon["logon8"]) ? $setting_icon["logon8"] : order_traking_assets_url . "logo/bastebandi.png") . "\">\r\n\r\n      <span class=\"namedigi\">" . $box . "</span>\r\n  \r\n    </li>\r\n     <li>\r\n     <img class=\"size\" src=\"" . (isset($setting_icon["logon9"]) ? $setting_icon["logon9"] : order_traking_assets_url . "logo/trucksend.png") . "\">\r\n        \r\n          <span class=\"namedigi\">" . $com . "</span>\r\n    </li>\r\n     ";
            if($chdev == "checked") {
                echo " <li>  <span class=\"tooltioopdigi\"> <img class=\"size \" src=\"" . (isset($setting_icon["logon10"]) ? $setting_icon["logon10"] : order_traking_assets_url . "logo/tahvil.png") . "\"><span class=\"tooltiooptextstatusdigidev\">" . $massg_dev . "</span></span>\r\n  <span class=\"namedigi\">" . (!empty($dev) ? $dev : wc_get_order_status_name($order->get_status())) . "</span>\r\n   </li>";
            }
            echo "</ol><br></br>";
        }
        if($setting["style"] == "novin") {
            echo "<ol class=\"progresss\" data-stepss=\"3\">\r\n    <li class=\"dones actives\">\r\n        <span class=\"names\">" . $process . "</span>\r\n        <span class=\"steps\"><span>" . $processing . "</span></span>\r\n    </li>\r\n    <li class=\"dones actives\">\r\n        <span class=\"names\">" . $hold . "</span>\r\n        <span class=\"steps\"><span>" . $onhold . "</span></span>\r\n    </li>\r\n    <li class=\"dones actives\">\r\n        <span class=\"names\">" . $box . "</span>\r\n        <span class=\"steps\"><span>" . $packing . "</span></span>\r\n    </li>\r\n          <li class=\"dones actives\">\r\n        <span class=\"names\">" . $com . "</span>\r\n        <span class=\"steps\"><span>" . $completed . "</span></span>\r\n    </li>";
            if($chdev == "checked") {
                echo "  <li class=\"actives\">\r\n      <span class=\"names\">" . (!empty($dev) ? $dev : wc_get_order_status_name($order->get_status())) . "</span>\r\n        <span class=\"steps\"><span class=\"borderi\"></span><div class=\"tooltioop\">" . $deliver . "<span  class=\"tooltiooptextdeliver\">" . $massg_dev . "<div></span>\r\n    </li>";
            }
            echo "</ol><br></br>";
        }
    }
}
function convertParsiToEnglish_novintrack($string)
{
    $persian = ["۰", "۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹"];
    $english = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9"];
    $output = str_replace($persian, $english, $string);
    return $output;
}
function register_my_session_novin_captcha()
{
    if(session_status() !== PHP_SESSION_ACTIVE && !is_admin()) {
        @session_start();
    }
}
function close_my_session_novin_captcha()
{
    if(session_status() == PHP_SESSION_ACTIVE && !is_admin()) {
        session_write_close();
    }
}
// @function novin_order_tracking_shortcode is protected ioncube.dynamickey encoding key.
function novin_order_tracking_shortcode()
{
}
function check_validate_license_ordertracking()
{
    $time = strtotime("now");
    $last_times = (int) get_post_meta(908070, "time_per_km", true);
    if(empty($last_times)) {
        $last_time = (int) 1000;
    } else {
        $last_time = isset($last_times) ? esc_attr($last_times) : "";
    }
    $produc_token = "843f49a5-4cfc-4a18-910e-a2c8ebb329b1";
    $last_time2 = get_option("time_track_check_secend");
    if(empty($last_time2)) {
        $last_time2 = 0;
    }
    $delay = (int) 72 * (int) 3600;
    $delay2 = (int) 96 * (int) 3600;
    $sett = (int) $last_time + $delay;
    $sett2 = (int) $last_time2 + $delay2;
    $licensetoken = get_option("novin_order");
    $license = isset($licensetoken["title"]) ? esc_attr($licensetoken["title"]) : "";
    if(!empty($license) && $sett <= $time && $sett2 < $time) {
        $result = novin_license::install($license, $produc_token);
        if($result->status == "successful") {
            update_post_meta(908070, "time_per_km", $time);
            delete_option("time_track_check_secend");
        } elseif($result->status != "successful" && $last_time2 <= 0) {
            update_option("time_track_check_secend", $time);
        } elseif($result->status != "successful") {
            delete_option("novin_order");
        }
    }
}

?>