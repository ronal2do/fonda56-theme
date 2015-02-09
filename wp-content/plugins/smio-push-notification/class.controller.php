<?php

class smpush_controller extends smpush_helper{
  public static $apisetting;
  public static $defconnection;
  public static $pushdb;
  public static $history;

  public function __construct(){
    $this->get_api_setting();
    $this->set_def_connection();
    $this->add_rewrite_rules();
    $this->cron_setup();
    if(self::$defconnection['dbtype'] == 'remote'){
      self::$pushdb = new wpdb(self::$defconnection['dbuser'], self::$defconnection['dbpass'], self::$defconnection['dbname'], self::$defconnection['dbhost']);
      if(!self::$pushdb){
        $this->output(0, 'Connecting with the remote push notification database is failed');
      }
    }
    else{
      global $wpdb;
      self::$pushdb = $wpdb;
    }
    self::$pushdb->hide_errors();
  }

  public function set_def_connection(){
    global $wpdb;
    self::$defconnection = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."push_connection WHERE id='".self::$apisetting['def_connection']."'", 'ARRAY_A');
  }

  public static function parse_query($query){
    if(preg_match_all("/{([a-zA-Z0-9_]+)}/", $query, $matches)){
      foreach($matches[1] AS $match){
        if($match == 'ios_name' OR $match == 'android_name')
          $query = str_replace('{'.$match.'}', self::$defconnection[$match] , $query);
        elseif($match == 'tbname_temp')
          $query = str_replace('{'.$match.'}', '`'.self::$defconnection['tbname'].'_temp'.'`' , $query);
        else
          $query = str_replace('{'.$match.'}', '`'.self::$defconnection[$match].'`' , $query);
      }
    }
    $query = str_replace('{wp_prefix}', SMPUSHTBPRE, $query);
    return $query;
  }

  public static function setting(){
    if($_POST){
      self::saveOptions();
    }
    else{
      global $wpdb;
      $connections = $wpdb->get_results("SELECT id,title FROM ".$wpdb->prefix."push_connection ORDER BY id ASC");
      self::loadpage('setting', 1, $connections);
    }
  }

  public static function documentation(){
    include(smpush_dir.'/class.documentation.php');
    self::load_jsplugins();
    $document = new smpush_documentation();
    $document = $document->build();
    $smpushexurl['auth_key'] = (self::$apisetting['complex_auth']==1)?md5(date('m/d/Y').self::$apisetting['auth_key'].date('H:i')):self::$apisetting['auth_key'];
    $smpushexurl['push_basename'] = site_url().'/'.self::$apisetting['push_basename'];
    include(smpush_dir.'/pages/documentation.php');
  }

  public static function loadpage($template, $noheader=0, $params=0){
    self::load_jsplugins();
    $noheader = ($noheader == 0)?'':'&noheader=1';
    $page_url = admin_url().'admin.php?page=smpush_'.$template.$noheader;
    include(smpush_dir.'/pages/'.$template.'.php');
  }

  public static function load_jsplugins(){
    wp_enqueue_style('smpush-style');
    if(is_rtl()){
      wp_enqueue_style('smpush-rtl');
    }
    wp_enqueue_script('smpush-mainscript');
    wp_enqueue_script('smpush-plugins');
  }

  public static function saveOptions(){
    $newsetting = array();
    foreach($_POST AS $key=>$value){
      if(!in_array($key, array('submit','apple_cert_upload'))){
        $newsetting[$key] = $value;
        unset(self::$apisetting[$key]);
      }
    }
    $checkbox = array('complex_auth','apple_sandbox','android_titanium_payload','ios_titanium_payload','e_post_chantocats','e_apprpost','e_appcomment','e_newcomment','e_usercomuser','e_postupdated','e_newpost');
    foreach($checkbox AS $inptname){
      if(!isset($_POST[$inptname]))
          self::$apisetting[$inptname] = 0;
    }
    if(!empty($_FILES['apple_cert_upload']['tmp_name'])){
      if(strtolower(substr($_FILES['apple_cert_upload']['name'], strrpos($_FILES['apple_cert_upload']['name'], '.') + 1)) == 'pem'){
        $target_path = realpath(dirname(__FILE__)).'/cert_connection_'.$newsetting['def_connection'].'.pem';
        if(move_uploaded_file($_FILES['apple_cert_upload']['tmp_name'], $target_path)){
          unset(self::$apisetting['apple_cert_path']);
          $newsetting['apple_cert_path'] = addslashes($target_path);
        }
      }
    }
    self::$apisetting = array_map('addslashes', self::$apisetting);
    self::$apisetting = array_merge($newsetting, self::$apisetting);
    update_option('smpush_options', self::$apisetting);
    echo 1;
    die();
  }

  public static function loadHistory($field, $index=false){
    if($index === false){
      if(isset(self::$history[$field])){
        return self::$history[$field];
      }
    }
    else{
      if(isset(self::$history[$field][$index])){
        return self::$history[$field][$index];
      }
    }
    return '';
  }

  public function build_menus(){
    add_menu_page('Setting', 'Push Notification', 'activate_plugins', 'smpush_setting', array('smpush_controller', 'setting'), 'div', 4);
    add_submenu_page('smpush_setting', 'Send Push Notification', 'Sending Dashboard', 'activate_plugins', 'smpush_send_notification', array('smpush_sendpush', 'send_notification'));
    add_submenu_page('smpush_setting', 'Starting Feedback Service', 'Feedback Service', 'activate_plugins', 'smpush_start_feedback', array('smpush_sendpush', 'start_feedback'));
    add_submenu_page('smpush_setting', 'Message Archive', 'Message Archive', 'activate_plugins', 'smpush_archive', array('smpush_modules', 'archive'));
    add_submenu_page('smpush_setting', 'Manage Connections', 'Manage Connections', 'activate_plugins', 'smpush_connections', array('smpush_modules', 'connections'));
    add_submenu_page('smpush_setting', 'Manage Device Token', 'Manage Device Token', 'activate_plugins', 'smpush_tokens', array('smpush_modules', 'tokens'));
    add_submenu_page('smpush_setting', 'Push Notification Channels', 'Manage Channels', 'activate_plugins', 'smpush_channel', array('smpush_modules', 'push_channel'));
    add_submenu_page('smpush_setting', 'Test Dashboard', 'Test Dashboard', 'activate_plugins', 'smpush_test_sending', array('smpush_modules', 'testing'));
    add_submenu_page('smpush_setting', 'Developer Documentation', 'Documentation', 'activate_plugins', 'smpush_documentation', array('smpush_controller', 'documentation'));
    add_submenu_page(NULL, 'Sending Push Notification', 'Sending Push Notification', 'activate_plugins', 'smpush_send_process', array('smpush_sendpush', 'send_process'));
    add_submenu_page(NULL, 'Queue Push', 'Queue Push', 'activate_plugins', 'smpush_runqueue', array('smpush_sendpush', 'RunQueue'));
    add_submenu_page(NULL, 'Cancel Queue Push', 'Cancel Queue Push', 'activate_plugins', 'smpush_cancelqueue', array('smpush_sendpush', 'smpush_cancelqueue'));
    add_submenu_page(NULL, 'Active invalid tokens', 'Active invalid tokens', 'activate_plugins', 'smpush_active_tokens', array('smpush_sendpush', 'activateTokens'));
  }

  public static function register_cron($schedules){
    $schedules['smpush_few_days'] = array(
      'interval' => 259200,
      'display' => __('Once every 3 days')
    );
    return $schedules;
  }

  public function cron_setup(){
    if(!wp_next_scheduled('smpush_update_counters')){
      wp_schedule_event(mktime(3,0,0,date('m'),date('d'),date('Y')), 'daily', 'smpush_update_counters');
	}
    if(! wp_next_scheduled('smpush_cron_fewdays')){
      wp_schedule_event(mktime(15,0,0,date('m'),date('d'),date('Y')), 'smpush_few_days', 'smpush_cron_fewdays');
	}
    if(get_transient('smpush_update_notice') !== false){
      add_action('admin_notices', array('smpush_controller', 'update_notice'));
    }
  }

  public function check_update_notify(){
    if(function_exists('curl_init')){
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, "http://smartiolabs.com/update/push_notification");
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
      $data = json_decode(curl_exec($ch));
      curl_close($ch);
      if($data !== NULL){
        if($data->version > SMPUSHVERSION){
          set_transient('smpush_update_notice', $data, 60);
        }
      }
    }
  }

  public static function update_notice(){
    $data = get_transient('smpush_update_notice');
    delete_transient('smpush_update_notice');
    echo '<div class="update-nag"><p><a href="'.$data->link.'" target="_blank">'.$data->plugin.' '.$data->version.'</a> is available! Please download your free update from your Envato account and update now.</p></div>';
  }

  public static function update_counters(){
    global $wpdb;
    $defconid = self::$apisetting['def_connection'];
    $counter = self::$pushdb->get_var(self::parse_query("SELECT COUNT({id_name}) FROM {tbname} WHERE {active_name}='1'"));
    $wpdb->query("UPDATE ".$wpdb->prefix."push_connection SET `counter`='$counter' WHERE id='$defconid'");
  }

  public static function update_all_counters(){
    global $wpdb;
    self::update_counters();
    $channels = $wpdb->get_results("SELECT id FROM ".$wpdb->prefix."push_channels");
    if($channels){
      foreach($channels as $channel){
        $count = $wpdb->get_var("SELECT COUNT(token_id) FROM ".$wpdb->prefix."push_relation WHERE channel_id='$channel->id'");
        $wpdb->query("UPDATE ".$wpdb->prefix."push_channels SET `count`='$count' WHERE id='$channel->id'");
      }
    }
  }

  public function get_option($index){
    return self::$apisetting[$index];
  }

  public function get_api_setting(){
    self::$apisetting = get_option('smpush_options');
    self::$apisetting = array_map('stripslashes', self::$apisetting);
  }

  public function add_rewrite_rules(){
    $apiname = self::$apisetting['push_basename'];
    add_rewrite_rule($apiname.'/?$', 'index.php?smpushcontrol=debug', 'top');
    add_rewrite_rule($apiname.'/(.+)$', 'index.php?smpushcontrol=$matches[1]', 'top');
  }

  public function start_fetch_method(){
    $method = get_query_var('smpushcontrol');
    if(!empty($method))
        $smpush_method = new smpush_api($method);
  }

  public function register_vars($vars){
      $vars[] = 'smpushcontrol';
      return $vars;
  }

}

?>