<?php
/**
* Plugin Name: رهگیری سفارشات
* Plugin URI: https://www.tarahenovin.com/
* Description: افزونه ای برای رهگیری سفارشات ووکامرس . شورت کد صفحه پیگیری سفارشات [order-tracking-code] و شورت کد پیامک {deliverydate} و {senddate} و {marsule} و {hamlonaghl}
* Version: 3.1.37
* Author: طراح نوین
* Author URI: http://tarahenovin.com/
**/
$min_loader_version="12.0";
$min_php_version="7.4";
$ioncube_error_checker=[];

if (!extension_loaded('ionCube Loader')){
    $ioncube_error_checker[]=sprintf('خطا : We detect you do not have ionCube loader , please call to your host service to install ionCube loader version to upper than %s',$min_loader_version);
    
}elseif (!function_exists('ioncube_loader_version') || version_compare(ioncube_loader_version(),$min_loader_version,'<')){
    
    $ioncube_error_checker[]=sprintf('خطا : We detect your ionCube loader is too old , please call to your host service to update ionCube loader version to upper than %s',$min_loader_version);
}
if(!version_compare(phpversion(),$min_php_version,'>=')) {
    $ioncube_error_checker[] = sprintf(
        'خطا : We detect your server php version is to old, this plugin need php version %s to up.  please call to your host service to update php',
        $min_php_version
    );
}
if (!empty($ioncube_error_checker)){
    
    add_action('admin_notices', function () use ($ioncube_error_checker){printf('<div class="notice notice-warning is-dismissible"> <p>%s</p> </div>',implode('<hr>',$ioncube_error_checker));},1);
    
    return;
}
register_activation_hook(__FILE__, 'novin_track_order_activate');
add_action('admin_init', 'novin_track_order_plugin_redirect');
function novin_track_order_activate() {
add_option('novin_track_order_license_activation_redirect', true);
}
function novin_track_order_plugin_redirect() {
if (get_option('novin_track_order_license_activation_redirect', false)) {
delete_option('novin_track_order_license_activation_redirect');
wp_redirect(admin_url('admin.php?page=novin-setting-ordertracking&tab=license'));
	exit;
	}
}
include_once("include/track-order.php");
//include_once("include/test.php");
if( !defined( 'order_traking_url' ) ) {
    define( 'order_traking_url', plugin_dir_url( __FILE__ ) );
}
if( !defined( 'order_traking_assets_url' ) ) {
    define( 'order_traking_assets_url', order_traking_url . 'assets/' );
}