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
if(isset($_POST["novin_seller"])) {
    $array_keys = array_keys($_POST["novin_seller"]);
    foreach ($array_keys as $key) {
        if(isset($_POST["novin_seller"][$key])) {
            $term_meta[$key] = esc_attr($_POST["novin_seller"][$key]);
        }
    }
    update_option("save_data_seller_post." . $seler_id, $term_meta);
}
$dokan = get_option("save_data_seller_post." . $seler_id);
$status_code = isset($dokan["status_code"]) ? esc_attr($dokan["status_code"]) : "";
$pishfarz_rahgir = isset($dokan["pishfarz_rahgir"]) ? esc_attr($dokan["pishfarz_rahgir"]) : "";
$date_default = isset($dokan["date_default"]) ? esc_attr($dokan["date_default"]) : "";
$post_default = isset($dokan["post_default"]) ? esc_attr($dokan["post_default"]) : "";
if(isset($_FILES["novin-xlsfile"]["tmp_name"]) && !empty($_FILES["novin-xlsfile"]["tmp_name"])) {
    $Filepath = WP_CONTENT_DIR . "/uploads/";
    $targets = $Filepath . basename($_FILES["novin-xlsfile"]["name"]);
    if(move_uploaded_file($_FILES["novin-xlsfile"]["tmp_name"], $targets)) {
        require_once "excel_reader2.php";
        require_once "SpreadsheetReader.php";
        $Reader = new SpreadsheetReader($targets);
        $i = 0;
        foreach ($Reader as $row) {
            $seler_id_by_order = dokan_get_seller_id_by_order(absint($row[0]));
            if($seler_id_by_order == $seler_id) {
                $i++;
                $order_id = !empty($row[0]) ? esc_attr(absint($row[0])) : "";
                $form = $post = $date = "";
                $post = !empty($row[1]) ? esc_attr(sanitize_text_field($row[1])) : $post_default;
                $date = !empty($row[2]) ? esc_attr(sanitize_text_field($row[2])) : $date_default;
                $forms = !empty($row[3]) ? esc_attr(sanitize_text_field($row[3])) : "";
                $marsule = !empty($row[4]) ? esc_attr(sanitize_text_field($row[4])) : "";
                $order = wc_get_order($order_id);
                $order->update_meta_data("shipp", esc_attr($post));
                $order->update_meta_data("datei", esc_attr($date));
                if(!empty($pishfarz_rahgir) && $pishfarz_rahgir != "هیچکدام") {
                    $form = $pishfarz_rahgir;
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
                if(!empty($rah) && !empty($status_code)) {
                    $order->update_status(str_replace("wc-", "", $status_code));
                }
            }
        }
        $xls_path = WP_CONTENT_DIR . "/uploads/" . $_FILES["novin-xlsfile"]["name"];
        unlink($xls_path);
    }
}
echo "<div class=\"dokan-dashboard-wrap\">\r\n    ";
do_action("dokan_dashboard_content_before");
echo "\r\n    <div class=\"dokan-dashboard-content\">\r\n\r\n        ";
do_action("dokan_help_content_inside_before");
echo "\r\n        <article class=\"novin-content-area\" >\r\n            \r\n            <br></br><hr>\r\n<span>&nbsp;وضعیتی که میخواهید پس از درج کد رهگیری تبدیل شوند </span><br></br>\r\n <form method=\"post\" action=\"\" enctype=\"multipart/form-data\">\r\n\r\n<select  name=\"novin_seller[status_code]\" id=\"status_code\" >\r\n";
foreach (wc_get_order_statuses() as $val => $key) {
    echo "<option value=\"" . $val . "\">" . $key . "</option>";
}
echo "</select><br></br><br></br>";
if(!empty($status_code)) {
    echo "<script type=\"text/javascript\">\r\ndocument.getElementById(\"status_code\").value = \"" . $status_code . "\" </script>";
}
echo "<hr>\r\n<span>پیشفرض انتخاب فرم رهگیری پست </span><br></br>\r\n\r\n<select  id=\"pishfarz_rahgir\" name=\"novin_seller[pishfarz_rahgir]\" id=\"desc_asc\" >\r\n<option  >هیچکدام</option>\r\n<option  value=\"postrahgir\">فرم رهگیری از پست</option>\r\n<option value=\"chaparrahgir\">فرم رهگیری از چاپار</option>\r\n<option value=\"tipax\">دکمه پیگیری از تیپاکس</option>\r\n\r\n  </select><br></br> <hr>\r\n  <span>پیشفرض تاریخ ارسال </span><br></br>\r\n     <input autocomplete=\"off\" type=\"text\" style=\"width: 151px;\" id=\"date_send_default\"  name=\"novin_seller[date_default]\"  value=\"";
echo $date_default;
echo "\" placeholder=\"پیشفرض تاریخ ارسال\">\r\n\r\n  <br><hr>\r\n    <span>پیشفرض سیستم حمل و نقل</span><br></br>\r\n     <input  type=\"text\" style=\"width: 151px;\"   name=\"novin_seller[post_default]\"  value=\"";
echo $post_default;
echo "\" placeholder=\"مانند : پست پیشتاز\">\r\n\r\n  <hr>\r\n       ";
if(!empty($pishfarz_rahgir)) {
    echo "<script type=\"text/javascript\">\r\ndocument.getElementById(\"pishfarz_rahgir\").value = \"" . $pishfarz_rahgir . "\" </script>";
}
echo "        <h2 style=\"text-align: center;\"> .فایل اکسل کد های رهگیری سفارشات را بارگذاری کنید این قسمت فقط برای کد های مرسوله به کار میرود</h2>\r\n           \r\n          \r\n          \r\n            <input style=\"margin: auto;background-color: #d7e7ea;border: 1px solid #d7e7ea;\" type=\"file\" id=\"novin-xls-activeed\" name=\"novin-xlsfile\" accept=\".xls,.xlsx,.csv,.ods\"><br></br>\r\n           \r\n  \r\n\r\n\r\n<button style=\"margin: auto;\r\n    background-color: #21bf76;\r\n    border: none;\r\n    color: white;\r\n    padding: 12px 27px;\r\n    width: 24%;\r\n    text-align: center;\r\n    text-decoration: none;\r\n    font-size: 13px;\r\n    display: block;\r\n    cursor: pointer;\r\n    border-radius: 6px;\">ارسال</button>\r\n</form>\r\n        </article><!-- .dashboard-content-area -->\r\n\r\n         ";
do_action("dokan_dashboard_content_inside_after");
echo "\r\n\r\n    </div><!-- .dokan-dashboard-content -->\r\n\r\n    ";
do_action("dokan_dashboard_content_after");
echo "\r\n</div><!-- .dokan-dashboard-wrap -->\r\n <script type=\"text/javascript\">\r\njQuery(document).ready(function(\$) {\r\n    \$(\"#date_send_default\").persianDatepicker({\r\n    initialValue: false,\r\n       cellWidth: 32,\r\n        cellHeight: 30,\r\n        fontSize: 14,\r\n    });\r\n  });\r\n</script>";

?>