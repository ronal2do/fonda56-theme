<?php

class smpush_api extends smpush_controller{
  public $counter = 0;
  public $dateformat;
  public $queryorder;

  public function __construct($method, $returnValue=false){
    $auth_key = $this->get_option('auth_key');
    $this->ParseOutput = true;
    self::$returnValue = $returnValue;
    if(!empty($auth_key) && isset($auth_key)){
      if($this->get_option('complex_auth') == 1){
        $auth_keys = array();
        $minutenow = date('i');
        $minuteafter = ($minutenow+1 > 59)? 0 : $minutenow+1;
        $minutebefore = ($minutenow-1 < 0)? 59 : $minutenow-1;
        $auth_keys[] = md5(date('m/d/Y').$auth_key.date('H').$minutenow);
        $auth_keys[] = md5(date('m/d/Y').$auth_key.date('H').$minuteafter);
        $auth_keys[] = md5(date('m/d/Y').$auth_key.date('H').$minutebefore);
      }
      else{
        $auth_keys = array($auth_key);
      }
      if(!in_array($_REQUEST['auth_key'], $auth_keys))
        return $this->output(0, 'Authentication failed: Authentication key is required to proceed');
    }
    if(!isset($_REQUEST['orderby'])){
      $_REQUEST['orderby'] = '';
    }
    if(isset($_REQUEST['order'])){
      if(strtolower($_REQUEST['order']) == 'asc')
          $this->queryorder = 'ASC';
      elseif(strtolower($_REQUEST['order']) == 'desc')
          $this->queryorder = 'DESC';
      else
          $this->queryorder = false;
    }
    if(method_exists($this, $method))
        $this->$method();
    else
        return $this->output(0, 'You called unavailable method `'.$method.'`');
  }

  public function cron_job(){
    smpush_cronsend::cronStart();
  }

  public function send_notification(){
    $this->CheckParams(array('message'));
    $_REQUEST = array_map('urldecode', $_REQUEST);
    $setting = array();
    if(!empty($_REQUEST['expire'])){
      $setting['expire'] = $_REQUEST['expire'];
    }
    if(!empty($_REQUEST['ios_slide'])){
      $setting['ios_slide'] = $_REQUEST['ios_slide'];
    }
    if(!empty($_REQUEST['ios_badge'])){
      $setting['ios_badge'] = $_REQUEST['ios_badge'];
    }
    if(!empty($_REQUEST['ios_sound'])){
      $setting['ios_sound'] = $_REQUEST['ios_sound'];
    }
    if(!empty($_REQUEST['ios_cavailable'])){
      $setting['ios_cavailable'] = $_REQUEST['ios_cavailable'];
    }
    if(!empty($_REQUEST['ios_launchimg'])){
      $setting['ios_launchimg'] = $_REQUEST['ios_launchimg'];
    }
    if(!empty($_REQUEST['customparams'])){
      $setting['extra_type'] = 'json';
      $setting['extravalue'] = $_REQUEST['customparams'];
    }
    if(!empty($_REQUEST['android_customparams'])){
      $setting['and_extra_type'] = 'json';
      $setting['and_extravalue'] = $_REQUEST['android_customparams'];
    }
    if(!empty($_REQUEST['sendtime'])){
      $sendtime = strtotime($_REQUEST['sendtime'], current_time('timestamp', 1));
    }
    else{
      $sendtime = 0;
    }
    if(!empty($_REQUEST['device_token'])){
      $this->CheckParams(array('device_token','device_type'));
      smpush_sendpush::SendPushMessage($_REQUEST['device_token'], $_REQUEST['device_type'], $_REQUEST['message'], $setting, $sendtime);
      $this->output(1, 'Message sent successfully');
    }
    elseif(!empty($_REQUEST['user_id'])){
      $tokeninfo = self::$pushdb->get_row(self::parse_query("SELECT {token_name} AS device_token,{type_name} AS device_type FROM {tbname} WHERE userid='$_REQUEST[user_id]' AND {active_name}='1'"));
      if($tokeninfo){
        smpush_sendpush::SendPushMessage($tokeninfo->device_token, $tokeninfo->device_type, $_REQUEST['message'], $setting, $sendtime);
        $this->output(1, 'Message sent successfully');
      }
      else{
        $this->output(0, 'Did not find data about this user or the user is inactive');
      }
    }
    elseif(!empty($_REQUEST['channel'])){
      if($_REQUEST['channel'] == 'all'){
        smpush_sendpush::SendCronPush('all', $_REQUEST['message'], '', '', $setting, $sendtime);
      }
      else{
        smpush_sendpush::SendCronPush($_REQUEST['channel'], $_REQUEST['message'], '', 'channel', $setting, $sendtime);
      }
      $this->output(1, 'Message sent successfully');
    }
  }

  public function savetoken($printout=true){
    $this->CheckParams(array('device_token','device_type'));
    if(empty($_REQUEST['device_info'])){
      $_REQUEST['device_info'] = '';
    }
    global $wpdb;
    $tokenid = self::$pushdb->get_var(self::parse_query("SELECT {id_name} FROM {tbname} WHERE {token_name}='$_REQUEST[device_token]'"));
    if($tokenid > 0){
      self::$pushdb->get_var(self::parse_query("UPDATE {tbname} SET {active_name}='1',{info_name}='$_REQUEST[device_info]' WHERE {id_name}='$tokenid'"));
      if(!empty($_REQUEST['user_id'])){
        self::$pushdb->query(self::parse_query("UPDATE {tbname} SET userid='$_REQUEST[user_id]' WHERE {id_name}='$tokenid'"));
      }
      if(!$printout) return $tokenid;
      return $this->output(1, 'Token saved successfully');
    }
    $os_name = $wpdb->get_row("SELECT android_name,ios_name FROM ".$wpdb->prefix."push_connection WHERE id='".self::$apisetting['def_connection']."'");
    if($_REQUEST['device_type'] == 'ios'){
      $device_type = $os_name->ios_name;
    }
    else{
      $device_type = $os_name->android_name;
    }
    self::$pushdb->query(self::parse_query("INSERT INTO {tbname} ({token_name},{type_name},{info_name},{active_name}) VALUES ('$_REQUEST[device_token]','$device_type','$_REQUEST[device_info]','1')"));
    $tokenid = self::$pushdb->insert_id;
    if($tokenid === false){
      return $this->output(0, 'Push database connection error');
    }
    if(!empty($_REQUEST['user_id'])){
      self::$pushdb->query(self::parse_query("UPDATE {tbname} SET userid='$_REQUEST[user_id]' WHERE {id_name}='$tokenid'"));
    }
    $defconid = self::$apisetting['def_connection'];
    self::$pushdb->query(self::parse_query("UPDATE ".SMPUSHTBPRE."push_connection SET counter=counter+1 WHERE id='$defconid'"));
    if(!empty($_REQUEST['channels_id'])){
      $chids = explode(',', $_REQUEST['channels_id']);
      foreach($chids AS $chid){
        $wpdb->query("INSERT INTO ".SMPUSHTBPRE."push_relation (channel_id,token_id,connection_id) VALUES ('$chid','$tokenid','$defconid')");
      }
      $wpdb->query("UPDATE ".SMPUSHTBPRE."push_channels SET `count`=`count`+1 WHERE id IN($_REQUEST[channels_id])");
    }
    else{
      $defchid = $wpdb->get_var("SELECT id FROM ".SMPUSHTBPRE."push_channels WHERE `default`='1'");
      $wpdb->query("INSERT INTO ".SMPUSHTBPRE."push_relation (channel_id,token_id,connection_id) VALUES ('$defchid','$tokenid','$defconid')");
      $wpdb->query("UPDATE ".SMPUSHTBPRE."push_channels SET `count`=`count`+1 WHERE id='$defchid'");
    }
    if(!$printout) return $tokenid;
    return $this->output(1, 'Token saved successfully');
  }

  public function channels_subscribe(){
    $this->CheckParams(array('channels_id'));
    $tokenid = $this->savetoken(false);
    self::editSubscribedChannels($tokenid, $_REQUEST['channels_id']);
    return $this->output(1, 'Subscription saved successfully');
  }

  public static function editSubscribedChannels($tokenid, $newchannels){
    global $wpdb;
    $defconid = self::$apisetting['def_connection'];
    $subschans = $wpdb->get_results("SELECT channel_id FROM ".SMPUSHTBPRE."push_relation WHERE token_id='$tokenid' AND connection_id='$defconid'");
    if($subschans){
      foreach($subschans AS $subschan){
        $chids[] = $subschan->channel_id;
      }
      $chids = implode(',', $chids);
      $wpdb->query("UPDATE ".SMPUSHTBPRE."push_channels SET `count`=`count`-1 WHERE id IN($chids)");
    }
    $wpdb->query("DELETE FROM ".SMPUSHTBPRE."push_relation WHERE token_id='$tokenid' AND connection_id='$defconid'");
    $chids = explode(',', $newchannels);
    foreach($chids AS $chid){
      $wpdb->query("INSERT INTO ".SMPUSHTBPRE."push_relation (channel_id,token_id,connection_id) VALUES ('$chid','$tokenid','$defconid')");
    }
    $wpdb->query("UPDATE ".SMPUSHTBPRE."push_channels SET `count`=`count`+1 WHERE id IN($newchannels)");
  }

  public function device_channels(){
    global $wpdb;
    $token = $this->savetoken(false);
    $defconid = self::$apisetting['def_connection'];
    $subschans = $wpdb->get_results("SELECT channel_id FROM ".SMPUSHTBPRE."push_relation WHERE token_id='$token' AND connection_id='$defconid'");
    if($subschans){
      foreach($subschans AS $subschan){
        $chids[] = $subschan->channel_id;
      }
    }
    else $chids = array();
    $this->get_channels($chids);
  }

  public function get_channels($chids=false){
    global $wpdb;
    if($_REQUEST['orderby'] == 'subscribers')
        $orderby = 'push_channels.`count`';
    elseif($_REQUEST['orderby'] == 'name')
        $orderby = 'push_channels.title';
    elseif($_REQUEST['orderby'] == 'date')
        $orderby = 'push_channels.id';
    else
        $orderby = 'push_channels.id';
    $arg = array(
    'where' => array('push_channels.private'=>0),
    'orderby' => $orderby,
    'order' => ($this->queryorder) ? $this->queryorder:'ASC'
    );
    $sql = "SELECT * FROM ".$wpdb->prefix."push_channels {where} {order}";
    $sql = $this->queryBuild($sql, $arg);
    $channels = $wpdb->get_results($sql, 'ARRAY_A');
    if($channels){
      if($chids !== false){
        foreach($channels AS $channel){
          if(in_array($channel['id'], $chids))
            $channel['subscribed'] = 'yes';
          else
            $channel['subscribed'] = 'no';
          $get[] = $channel;
        }
        return $this->output(1, $get);
      }
      return $this->output(1, $channels);
    }
    else{
      return $this->output(0, 'No result found');
    }
  }

  public function debug(){
    $this->output(1, 'Push notification system is active now and work under version '.get_option('smpush_version'));
  }

  public static function delete_relw_app($user_id){
    global $wpdb;
    $wpdb->delete(SMPUSHTBPRE.'push_tokens', array('userid' => $user_id));
  }

}

?>