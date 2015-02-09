<?php
/*
Plugin Name: Push Notification System
Plugin URI: http://smartiolabs.com/product/push-notification-system
Description: Provides a complete solution to send mobile push notification messages to platforms IOS and Android.
Author: Smart IO Labs
Version: 3.9.2
Author URI: http://smartiolabs.com
*/

define('smpush_dir', plugin_dir_path(__FILE__));
define('smpush_imgpath', plugins_url('/images', __FILE__));
define('smpush_csspath', plugins_url('/css', __FILE__));
define('smpush_jspath', plugins_url('/js', __FILE__));
define('SMPUSHVERSION', 3.92);

date_default_timezone_set(get_option('timezone_string'));

include(smpush_dir.'/class.helper.php');
include(smpush_dir.'/class.controller.php');
include(smpush_dir.'/class.sendpush.php');
include(smpush_dir.'/class.sendcron.php');
include(smpush_dir.'/class.events.php');
include(smpush_dir.'/class.modules.php');
include(smpush_dir.'/class.api.php');

register_activation_hook(__FILE__, 'smpush_install');
register_uninstall_hook(__FILE__, 'smpush_uninstall');

add_action('init', 'smpush_start');
add_filter('cron_schedules', array('smpush_controller', 'register_cron'));

//Push notification for custom events
add_action('publish_post', array('smpush_events', 'post_approved'));
add_action('wp_insert_comment', array('smpush_events', 'new_comment'), 99, 2);
add_action('comment_unapproved_to_approved', array('smpush_events', 'comment_approved'));

function smpush_start(){
  global $wpdb;
  define('SMPUSHTBPRE', $wpdb->prefix);
  $smpush_controller = new smpush_controller();

  $smpush_version = get_option('smpush_version');
  if($smpush_version != SMPUSHVERSION){
    smpush_upgrade($smpush_version);
  }

  add_action('template_redirect', array($smpush_controller, 'start_fetch_method'));
  add_action('deleted_user', array('smpush_api', 'delete_relw_app'));
  add_action('admin_menu', array($smpush_controller, 'build_menus'));
  add_action('admin_enqueue_scripts', 'smpush_scripts');
  add_action('wp_loaded', 'smpush_flush_rules');
  add_action('smpush_cron_fewdays', array($smpush_controller, 'check_update_notify'));
  add_action('smpush_update_counters', array('smpush_controller', 'update_all_counters'));

  add_filter('query_vars', array($smpush_controller, 'register_vars'));
}

function smpush_scripts(){
  wp_register_script('smpush-progbarscript', smpush_jspath.'/jquery.progressbar.js', array('jquery'), SMPUSHVERSION);
  wp_register_script('smpush-mainscript', smpush_jspath.'/smio-function.js', array('jquery'), SMPUSHVERSION);
  wp_register_script('smpush-plugins', smpush_jspath.'/smio-plugins.js', array('jquery'), SMPUSHVERSION);
  wp_register_style('smpush-mainstyle', smpush_csspath.'/autoload-style.css', array(), SMPUSHVERSION);
  wp_register_style('smpush-style', smpush_csspath.'/smio-style.css', array(), SMPUSHVERSION);
  wp_register_style('smpush-progbarstyle', smpush_csspath.'/smio-progressbar.css', array(), SMPUSHVERSION);
  wp_enqueue_style('smpush-mainstyle');
  if(is_rtl()){
    wp_register_style('smpush-rtl', smpush_csspath.'/smio-style-rtl.css', array(), SMPUSHVERSION);
  }
  if(get_bloginfo('version') > 3.7){
    wp_register_style('smpush-fix38', smpush_csspath.'/autoload-style38.css', array(), SMPUSHVERSION);
    wp_enqueue_style('smpush-fix38');
  }
}

function smpush_flush_rules(){
  global $wp_rewrite;
  $wp_rewrite->flush_rules();
}

function smpush_install(){
  if(get_option('smpush_version') > 0){
    return;
  }
  global $wpdb;
  $wpdb->hide_errors();
  require_once(ABSPATH.'wp-admin/includes/upgrade.php');
  $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_archive` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` mediumtext NOT NULL,
  `starttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `endtime` datetime DEFAULT NULL,
  `report` mediumtext NOT NULL,
  `transient` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
  dbDelta($sql);
  $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_feedback` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `tokens` longtext NOT NULL,
  `feedback` longtext NOT NULL,
  `device_type` set('ios','android','ios_invalid') NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
  dbDelta($sql);
  $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(255) NOT NULL,
  `device_type` varchar(10) NOT NULL,
  `feedback` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `device_type` (`device_type`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
  dbDelta($sql);
  $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_cron_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` varchar(255) NOT NULL,
  `device_type` varchar(10) NOT NULL,
  `sendtime` varchar(50) NOT NULL,
  `sendoptions` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sendtime` (`sendtime`),
  KEY `device_type` (`device_type`),
  KEY `sendoptions` (`sendoptions`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
  dbDelta($sql);
  $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `device_token` varchar(255) NOT NULL,
  `device_type` enum('ios','android') NOT NULL,
  `information` TINYTEXT NOT NULL,
  `active` BOOLEAN NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userid`,`device_token`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
  dbDelta($sql);
  $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_channels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `description` mediumtext NOT NULL,
  `private` tinyint(1) NOT NULL,
  `default` tinyint(1) NOT NULL,
  `count` int(11) NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
  dbDelta($sql);
  $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_relation` (
  `channel_id` int(11) NOT NULL,
  `token_id` int(11) NOT NULL,
  KEY `channel_id` (`channel_id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
  dbDelta($sql);
  $wpdb->query("ALTER TABLE  `".$wpdb->prefix."push_relation` ADD  `connection_id` INT NOT NULL");
  $wpdb->query("UPDATE `".$wpdb->prefix."push_relation` SET `connection_id`='1'");
  $chancount = $wpdb->get_var("SELECT id FROM `".$wpdb->prefix."push_channels` WHERE id='1'");
  if(!$chancount){
    $wpdb->query("INSERT INTO `".$wpdb->prefix."push_channels` (`id`, `title`, `private`, `default`) VALUES (1, 'Main Channel', 0, 1);");
  }
  $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_connection` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` mediumtext NOT NULL,
  `dbtype` enum('localhost','remote') NOT NULL,
  `dbhost` varchar(50) NOT NULL DEFAULT 'localhost',
  `dbname` varchar(50) NOT NULL,
  `dbuser` varchar(50) NOT NULL,
  `dbpass` varchar(50) NOT NULL,
  `tbname` varchar(50) NOT NULL,
  `id_name` varchar(50) NOT NULL,
  `token_name` varchar(50) NOT NULL,
  `type_name` varchar(50) NOT NULL,
  `ios_name` varchar(20) NOT NULL,
  `android_name` varchar(20) NOT NULL,
  `info_name` VARCHAR(50) NOT NULL,
  `active_name` VARCHAR( 20 ) NOT NULL,
  `counter` int(11) NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
  dbDelta($sql);
  $wpdb->query("INSERT INTO `".$wpdb->prefix."push_connection` VALUES (1, 'Default Connection', 'Plugin default connection', 'localhost', '', '', '', '', '{wp_prefix}push_tokens', 'id', 'device_token', 'device_type', 'ios', 'android', 'information', 'active', 0)");
  $setting = array(
  'auth_key' => '',
  'complex_auth' => 0,
  'push_basename' => 'push',
  'def_connection' => 1,
  'apple_sandbox' => 0,
  'apple_passphrase' => '',
  'apple_cert_path' => '',
  'google_apikey' => '',
  'ios_titanium_payload' => 0,
  'android_titanium_payload' => 0,
  'e_post_chantocats' => 0,
  'e_apprpost' => 0,
  'e_appcomment' => 0,
  'e_newcomment' => 0,
  'e_usercomuser' => 0,
  'e_postupdated' => 0,
  'e_newpost' => 0,
  'e_apprpost_body' => 'Your post "{subject}" is approved and published',
  'e_appcomment_body' => 'Your comment "{comment}" is approved and published now',
  'e_newcomment_body' => 'Your post "{subject}" have new comments, Keep in touch with your readers',
  'e_usercomuser_body' => 'Someone reply on your comment "{comment}"',
  'e_postupdated_body' => 'The post you subscribed in "{subject}" got updated',
  'e_newpost_body' => 'We have published a new topic "{subject}"'
  );
  add_option('smpush_options', $setting);
  add_option('smpush_version', SMPUSHVERSION);
  add_option('smpush_history', '');
}

function smpush_upgrade($version){
  require_once(ABSPATH.'wp-admin/includes/upgrade.php');
  global $wpdb;
  $wpdb->hide_errors();
  if($version < 2.0){
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_feedback` (
    `id` tinyint(4) NOT NULL AUTO_INCREMENT,
    `tokens` longtext NOT NULL,
    `feedback` longtext NOT NULL,
    `device_type` set('ios','android','ios_invalid') NOT NULL,
    PRIMARY KEY (`id`)
    )";
    dbDelta($sql);
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."push_queue` ADD `expire` SMALLINT NOT NULL ,
    ADD `ios_slide` VARCHAR( 40 ) NOT NULL ,
    ADD `feedback` BOOLEAN NOT NULL");
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."push_tokens` ADD `information` TINYTEXT NOT NULL,
    ADD `active` BOOLEAN NOT NULL");
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."push_queue` CHANGE `device_type` `device_type` VARCHAR( 10 ) NOT NULL");
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."push_connection` ADD `info_name` VARCHAR(50) NOT NULL AFTER `android_name`,
    ADD `active_name` VARCHAR(20) NOT NULL AFTER `info_name`");
    $wpdb->query("UPDATE `".$wpdb->prefix."push_connection` SET active_name='active',info_name='information' WHERE id='1'");
    $wpdb->query("UPDATE `".$wpdb->prefix."push_tokens` SET `active`='1'");
    $version = 2.0;
  }
  if($version == 2.0){
    $version = 2.1;
  }
  if($version == 2.1){
    $version = 2.2;
  }
  if($version == 2.2){
    $wpdb->query("TRUNCATE `".$wpdb->prefix."push_queue`");
    $wpdb->query("ALTER TABLE  `".$wpdb->prefix."push_queue` DROP  `extravalue` ,
    DROP  `extra_type` ,
    DROP  `expire` ,
    DROP  `ios_slide`");
    $wpdb->query("ALTER TABLE  `".$wpdb->prefix."push_queue` ADD  `options` MEDIUMTEXT NOT NULL");
    $version = 2.3;
  }
  if($version == 2.3){
    $setting = get_option('smpush_options');
    update_option('smpush_options', unserialize($setting));
    $version = 2.4;
  }
  if($version == 2.4){
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_archive` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `message` mediumtext NOT NULL,
    `starttime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `endtime` datetime DEFAULT NULL,
    `report` mediumtext NOT NULL,
    PRIMARY KEY (`id`)
    )";
    dbDelta($sql);
    $wpdb->query("ALTER TABLE  `".$wpdb->prefix."push_queue` DROP  `message` ,DROP  `options`");
    add_option('smpush_history', '');
    $version = 2.5;
  }
  if($version == 2.5){
    $version = 2.6;
  }
  if($version == 2.6){
    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."push_cron_queue` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `token` varchar(255) NOT NULL,
    `device_type` varchar(10) NOT NULL,
    `sendtime` varchar(50) NOT NULL,
    `sendoptions` varchar(50) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `sendtime` (`sendtime`),
    KEY `device_type` (`device_type`)
    )";
    dbDelta($sql);
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."push_queue` ADD INDEX (`device_type`)");
    $setting = get_option('smpush_options');
    $setting['e_apprpost'] = 0;
    $setting['e_appcomment'] = 0;
    $setting['e_newcomment'] = 0;
    $setting['e_usercomuser'] = 0;
    $setting['e_postupdated'] = 0;
    $setting['e_newpost'] = 0;
    $setting['e_apprpost_body'] = 'Your post "{subject}" is approved and published';
    $setting['e_appcomment_body'] = 'Your comment "{comment}" is approved and published now';
    $setting['e_newcomment_body'] = 'Your post "{subject}" have new comments, Keep in touch with your readers';
    $setting['e_usercomuser_body'] = 'Someone reply on your comment "{comment}"';
    $setting['e_postupdated_body'] = 'The post you subscribed in "{subject}" got updated';
    $setting['e_newpost_body'] = 'We have published a new topic "{subject}"';
    update_option('smpush_options', $setting);
    $version = 3;
  }
  if($version == 3){
    $version = 3.1;
  }
  if($version == 3.1){
    $version = 3.2;
  }
  if($version == 3.2){
    $version = 3.3;
  }
  if($version == 3.3){
    $setting = get_option('smpush_options');
    $setting['ios_titanium_payload'] = 0;
    $setting['android_titanium_payload'] = 0;
    update_option('smpush_options', $setting);
    $version = 3.4;
  }
  if($version == 3.4){
    $version = 3.5;
  }
  if($version == 3.5){
    $setting = get_option('smpush_options');
    $setting['complex_auth'] = 0;
    update_option('smpush_options', $setting);
    $version = 3.6;
  }
  if($version == 3.6){
    $setting = get_option('smpush_options');
    $setting['e_post_chantocats'] = 0;
    update_option('smpush_options', $setting);
    $version = 3.7;
  }
  if($version == 3.7){
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."push_archive` ADD  `transient` VARCHAR( 50 ) NOT NULL");
    $wpdb->query("ALTER TABLE `".$wpdb->prefix."push_cron_queue` ADD INDEX (`sendoptions`)");
    $version = 3.8;
  }
  if($version == 3.8){
    $version = 3.9;
  }
  if($version == 3.9){
    $version = 3.91;
  }
  if($version == 3.91){
    $version = 3.92;
  }
  update_option('smpush_version', $version);
}

function smpush_uninstall(){
  global $wpdb;
  if(is_multisite()){
    $blogs = $wpdb->get_results("SELECT blog_id FROM $wpdb->blogs");
    if($blogs){
      foreach($blogs as $blog){
        switch_to_blog($blog->blog_id);
        smpush_uninstall_code();
      }
      restore_current_blog();
    }
  }
  else{
    smpush_uninstall_code();
  }
}

function smpush_uninstall_code(){
  global $wpdb;
  global $wp_rewrite;
  $wpdb->hide_errors();
  $wp_rewrite->flush_rules();
  $wpdb->query("DROP TABLE `".$wpdb->prefix."push_queue`");
  $wpdb->query("DROP TABLE `".$wpdb->prefix."push_tokens`");
  $wpdb->query("DROP TABLE `".$wpdb->prefix."push_channels`");
  $wpdb->query("DROP TABLE `".$wpdb->prefix."push_relation`");
  $wpdb->query("DROP TABLE `".$wpdb->prefix."push_connection`");
  $wpdb->query("DROP TABLE `".$wpdb->prefix."push_feedback`");
  $wpdb->query("DROP TABLE `".$wpdb->prefix."push_archive`");
  $wpdb->query("DROP TABLE `".$wpdb->prefix."push_cron_queue`");
  delete_option('smpush_options');
  delete_option('smpush_version');
  delete_option('smpush_history');
  wp_clear_scheduled_hook('smpush_update_counters');
  wp_clear_scheduled_hook('smpush_cron_fewdays');
}

?>
