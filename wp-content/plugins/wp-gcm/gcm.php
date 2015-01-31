<?php
/*
Plugin Name: WP GCM
Description: Google Cloud Messaging Plugin for WordPress.
Version: 1.5.1
Author: Deniz Celebi & Pixelart
Author URI: http://codecanyon.net/user/PixelartDev
*/

$dir = px_gcm_dir();

@include_once "$dir/options.php";
@include_once "$dir/page/settings.php";
@include_once "$dir/page/write.php";
@include_once "$dir/page/list.php";
@include_once "$dir/page/view.php";
@include_once "$dir/page/stats.php";
@include_once "$dir/page/export.php";
@include_once "$dir/register.php";


// create db tables and register settings and so on after activation
function px_gcm_activated() {
   	global $wpdb;
  	$px_table_name = $wpdb->prefix.'gcm_users';

	if($wpdb->get_var("show tables like '$px_table_name'") != $px_table_name) {
		$sql = "CREATE TABLE " . $px_table_name . " (
		`id` int(11) NOT NULL AUTO_INCREMENT,
        `gcm_regid` text,
		`os` text,
		`model` text,
		`send_msg` int,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
		);";
	}
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
	
	px_gcm_db_upgrade();
	
	add_option('px_gcm_total_msg', 0);
	add_option('px_gcm_fail_msg', 0);
	add_option('px_gcm_suc_msg', 0);
	add_option('px_gcm_do_activation_redirect', true);
}

// redirect to settings after activation
function px_gcm_setting_redirect() {
    if (get_option('px_gcm_do_activation_redirect', false)) {
        delete_option('px_gcm_do_activation_redirect');
        if(!isset($_GET['activate-multi'])) {
            wp_redirect(get_site_url().'/wp-admin/admin.php?page=px-gcm-settings');
        }
    }
}

// do an upgrade on the db
function px_gcm_db_upgrade() {
	if(isset($_GET['gcm-upgrade']) ){
		global $wpdb;  
		$px_table_name = $wpdb->prefix.'gcm_users';
		
		$queryO = "UPDATE $px_table_name SET `os`= \'not set\' WHERE `os` = \'\' ";
		$wpdb->query($queryO);
		
		$queryM = "UPDATE $px_table_name SET `model`= \'not set\' WHERE `model` = \'\' ";
		$wpdb->query($queryM);
	}
}

// register the scripts
function px_gcm_view_scripts($hook) {
	wp_enqueue_script('Chart', plugins_url( 'js/Chart.js', __FILE__ ));
	wp_enqueue_script('CountUp', plugins_url( 'js/countUp.min.js', __FILE__ ));
}


function px_gcm_dir() {
  if(defined('PX_GCM_DIR') && file_exists(PX_GCM_DIR)) {
    return PX_GCM_DIR;
  }else {
    return dirname(__FILE__);
  }
}

register_activation_hook("$dir/gcm.php", 'px_gcm_activated');
add_action('admin_init', 'px_gcm_setting_redirect');
add_action('admin_enqueue_scripts', 'px_gcm_view_scripts');
add_action('init','px_gcm_register');

?>