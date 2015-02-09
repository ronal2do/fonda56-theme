<?php

class smpush_sendpush extends smpush_controller {
  const TIME_BINARY_SIZE = 4;
  const TOKEN_LENGTH_BINARY_SIZE = 2;
  const DEVICE_BINARY_SIZE = 32;
  const ERROR_RESPONSE_SIZE = 6;
  const ERROR_RESPONSE_COMMAND = 8;
  const STATUS_CODE_INTERNAL_ERROR = 999;
  public static $cronSendOperation = false;
  protected static $_aErrorResponseMessages = array(
    0   => 'No errors encountered',
    1   => 'Processing error',
    2   => 'Missing device token',
    3   => 'Missing topic',
    4   => 'Missing payload',
    5   => 'Invalid token size',
    6   => 'Invalid topic size',
    7   => 'Invalid payload size',
    8   => 'Invalid token',
    self::STATUS_CODE_INTERNAL_ERROR => 'Internal error'
  );
  protected static $sendoptions = array(
    'message'        => '',
    'iostestmode'    => 0,
    'feedback'       => 0,
    'expire'         => 0,
    'ios_slide'      => '',
    'ios_badge'      => 0,
    'ios_sound'      => 'default',
    'ios_cavailable' => 0,
    'ios_launchimg'  => '',
    'extra_type'     => '',
    'extravalue'     => '',
    'and_extra_type' => '',
    'and_extravalue' => ''
  );

  public function __construct() {
    parent::__construct();
  }

  private static function archiveMsgLog($message, $sendtime, $transient) {
    global $wpdb;
    $sendtime = date('Y-m-d H:i:s', $sendtime);
    $wpdb->insert($wpdb->prefix.'push_archive', array('message' => $message, 'starttime' => $sendtime, 'transient' => $transient));
    return;
  }
  
  public static function SendPush($ids, $message, $extravalue) {
    return;
  }

  public static function SendPushMessage($device_token, $device_type, $message, $sendsetting=array(), $sendtime=0) {
    global $wpdb;
    $token = array();
    $ios_name = $wpdb->get_var("SELECT ios_name FROM ".$wpdb->prefix."push_connection WHERE id='".self::$apisetting['def_connection']."'");
    self::$sendoptions['message'] = $message;
    if($device_type == 'ios' OR $device_type == $ios_name){
      $token[0]['token'] = $device_token;
      $device_type = 'ios';
    }
    else{
      $token['token'][0] = $device_token;
      $device_type = 'android';
    }
    self::$sendoptions = array_merge(self::$sendoptions, $sendsetting);
    
    if($sendtime > 0){
      $transient_unique = rand(1000,3000).current_time('timestamp', 1);
      set_transient('smpush_cronsend_'.$transient_unique, self::$sendoptions, (date('d', $sendtime-current_time('timestamp', 1))+86400));
      $crondata = array(
      'token' => $device_token,
      'device_type' => $device_type,
      'sendtime' => $sendtime,
      'sendoptions' => $transient_unique
      );
      $wpdb->insert($wpdb->prefix.'push_cron_queue', $crondata);
      self::archiveMsgLog($message, $sendtime, $transient_unique);
    }
    else{
      self::$returnValue = true;
      self::updateStats();
      self::connectPush($message, $token, $device_type, self::$sendoptions, false);
      self::updateStats('all');
      self::$returnValue = false;
    }
  }

  public static function SendCronPush($ids, $message, $extravalue, $gettype='userid', $sendsetting=array(), $sendtime=0) {
    global $wpdb;
    if($ids == 'all'){
      $queue = self::$pushdb->get_results(self::parse_query("SELECT {token_name} AS device_token,{type_name} AS device_type FROM {tbname} WHERE {active_name}='1'"));
    }
    elseif($gettype == 'userid'){
      $ids = implode(',', $ids);
      $queue = self::$pushdb->get_results(self::parse_query("SELECT {token_name} AS device_token,{type_name} AS device_type FROM {tbname} WHERE userid IN($ids) AND {active_name}='1'"));
    }
    elseif($gettype == 'tokenid'){
      $ids = implode(',', $ids);
      $queue = self::$pushdb->get_results(self::parse_query("SELECT {token_name} AS device_token,{type_name} AS device_type FROM {tbname} WHERE {id_name} IN($ids) AND {active_name}='1'"));
    }
    elseif($gettype == 'channel'){
      $defconid = self::$apisetting['def_connection'];
      $tablename = $wpdb->prefix.'push_relation';
      $queue = self::$pushdb->get_results(self::parse_query("SELECT {tbname}.{token_name} AS device_token,{tbname}.{type_name} AS device_type FROM $tablename
      INNER JOIN {tbname} ON({tbname}.{id_name}=$tablename.token_id AND {tbname}.{active_name}='1')
      WHERE $tablename.channel_id IN($ids) AND $tablename.connection_id='$defconid' GROUP BY $tablename.token_id"));
    }
    if(!$queue)
      return false;
    self::$sendoptions['message'] = $message;
    
    if(!empty($sendsetting)){
      self::$sendoptions = array_merge(self::$sendoptions, $sendsetting);
    }
    elseif(!empty($extravalue)){
      self::$sendoptions['extra_type'] = 'normal';
      self::$sendoptions['extravalue'] = $extravalue;
    }
    
    $transient_unique = rand(1000,3000).current_time('timestamp', 1);
    $transientexpire = ($sendtime > 0)? (date('d', $sendtime-current_time('timestamp', 1))+86400) : 86400;
    set_transient('smpush_cronsend_'.$transient_unique, self::$sendoptions, $transientexpire);
    foreach($queue AS $queueone) {
      $crondata = array(
      'token' => $queueone->device_token,
      'device_type' => $queueone->device_type,
      'sendtime' => $sendtime,
      'sendoptions' => $transient_unique
      );
      $wpdb->insert($wpdb->prefix.'push_cron_queue', $crondata);
    }
    if($sendtime > 0){
      self::archiveMsgLog($message, $sendtime, $transient_unique);
    }
    else{
      $sendtime = current_time('timestamp', 1);
      self::archiveMsgLog($message, $sendtime, $transient_unique);
    }
  }

  public static function activateTokens() {
    self::$pushdb->query(self::parse_query("UPDATE {tbname} SET {active_name}='1'"));
    self::update_counters();
    wp_redirect(admin_url().'admin.php?page=smpush_send_notification');
  }

  public static function smpush_cancelqueue() {
    global $wpdb;
    $wpdb->query("TRUNCATE `".$wpdb->prefix."push_queue`");
    $wpdb->query("TRUNCATE `".$wpdb->prefix."push_feedback`");
    delete_transient('smpush_lastid');
    delete_transient('smpush_post');
    delete_transient('smpush_send_options');
    delete_transient('smpush_query');
    delete_transient('smpush_resum');
    self::updateStats();
    wp_redirect(admin_url().'admin.php?page=smpush_send_notification');
  }

  public static function send_process($resumsend, $allcount = 0, $increration = 0) {
    self::load_jsplugins();
    wp_enqueue_style('smpush-progbarstyle');
    wp_enqueue_script('smpush-progbarscript');
    include (smpush_dir.'/pages/send_process.php');
  }

  public static function start_feedback() {
    global $wpdb;
    $wpdb->query("TRUNCATE `".$wpdb->prefix."push_queue`");
    $wpdb->query("TRUNCATE `".$wpdb->prefix."push_feedback`");
    self::$sendoptions['message'] = 'Feedback Service';
    self::updateStats();
    $wpdb->insert($wpdb->prefix.'push_feedback', array('device_type' => 'ios'));
    self::send_process(true, -1);
  }

  public static function send_notification() {
    global $wpdb;
    if(!empty ($_GET['savehistory'])) {
      update_option('smpush_history', $_POST);
      echo 1;
      exit;
    }
    if(!empty ($_GET['clearhistory'])) {
      update_option('smpush_history', '');
      echo 1;
      exit;
    }
    if(get_transient('smpush_resum') !== false && !isset ($_GET['lastid'], $_GET['increration'])) {
      $_POST = get_transient('smpush_post');
    }
    if($_POST) {
      if(isset ($_POST['message'], $_POST['type'])) {
        $wpdb->query("TRUNCATE `".$wpdb->prefix."push_queue`");
        $wpdb->query("TRUNCATE `".$wpdb->prefix."push_feedback`");
        delete_transient('smpush_send_options');
        self::$sendoptions['message'] = $_POST['message'];
        self::updateStats();
        if($_POST['type'] == 'ios') {
          $where = "AND {tbname}.{type_name}='{ios_name}'";
        }
        elseif($_POST['type'] == 'android') {
          $where = "AND {tbname}.{type_name}='{android_name}'";
        }
        else{
          $where = '';
        }
        if(isset ($_POST['channel'])) {
          $defconid = self::$apisetting['def_connection'];
          $channelids = implode(',', $_POST['channel']);
          $tablename = $wpdb->prefix.'push_relation';
          $smpush_query = self::parse_query("SELECT {tbname}.{id_name} AS token_id,{tbname}.{token_name} AS device_token,{tbname}.{type_name} AS device_type FROM $tablename
          INNER JOIN {tbname} ON({tbname}.{id_name}=$tablename.token_id AND {tbname}.{active_name}='1' $where)
          WHERE $tablename.channel_id IN($channelids) AND $tablename.connection_id='$defconid' AND $tablename.token_id>[lastid] GROUP BY $tablename.token_id ASC LIMIT 0,[limit]");
          $alltokens = $wpdb->get_var(self::parse_query("SELECT COUNT($tablename.token_id) FROM $tablename
          INNER JOIN {tbname} ON({tbname}.{id_name}=$tablename.token_id AND {tbname}.{active_name}='1' $where)
          WHERE $tablename.channel_id IN($channelids) AND $tablename.connection_id='$defconid'"));
        }
        else{
          $smpush_query = self::parse_query("SELECT {id_name} AS token_id,{token_name} AS device_token,{type_name} AS device_type FROM {tbname} WHERE {active_name}='1' $where AND {id_name}>[lastid] ORDER BY {id_name} ASC LIMIT 0,[limit]");
          $alltokens = self::$pushdb->get_var(self::parse_query("SELECT COUNT({id_name}) FROM {tbname} WHERE {active_name}='1' $where"));
          if($alltokens === null) {
            wp_die('Please reconfig the default push notification database connection <a href="'.admin_url().'admin.php?page=smpush_connections">here</a>');
          }
        }
        $feedback = (isset ($_POST['feedback']))?1:0;
        $iostestmode = (isset ($_POST['iostestmode']))?1:0;
        if(isset ($_POST['feedback']) && ($_POST['type'] == 'ios' OR $_POST['type'] == 'all')) {
          $wpdb->insert($wpdb->prefix.'push_feedback', array('device_type' => 'ios'));
        }
        $_POST['feedback'] = $feedback;
        $_POST['iostestmode'] = $iostestmode;
        if($_POST['extra_type'] == 'multi') {
          $json = array();
          foreach($_POST['key'] as $loop => $key) {
            if(!empty ($key) && !empty ($_POST['value'][$loop])) {
              $json[$key] = $_POST['value'][$loop];
            }
          }
          if(empty ($json)) {
            $_POST['extra'] = '';
            $_POST['extra_type'] = '';
          }
          else{
            $_POST['extra'] = json_encode($json);
            $_POST['extra_type'] = 'json';
          }
        }
        if($_POST['and_extra_type'] == 'multi') {
          $json = array();
          foreach($_POST['and_key'] as $loop => $key) {
            if(!empty ($key) && !empty ($_POST['and_value'][$loop])) {
              $json[$key] = $_POST['and_value'][$loop];
            }
          }
          if(empty ($json)) {
            $_POST['and_extra'] = '';
            $_POST['and_extra_type'] = '';
          }
          else{
            $_POST['and_extra'] = json_encode($json);
            $_POST['and_extra_type'] = 'json';
          }
        }
        $options = array('message' => $_POST['message'], 'iostestmode' => $_POST['iostestmode'], 'feedback' => $_POST['feedback'], 'expire' => $_POST['expire'], 'ios_slide' => $_POST['ios_slide'], 'ios_badge' => $_POST['ios_badge'], 'ios_sound' => $_POST['ios_sound'], 'ios_cavailable' => $_POST['ios_cavailable'], 'ios_launchimg' => $_POST['ios_launchimg'], 'extra_type' => $_POST['extra_type'], 'extravalue' => $_POST['extra'], 'and_extra_type' => $_POST['and_extra_type'], 'and_extravalue' => $_POST['and_extra']);
        $sendtimeformat = $_POST['mm'].'/'.$_POST['jj'].'/'.$_POST['aa'].' '.$_POST['hh'].':'.$_POST['mn'].':00';
        $options['sendtime'] = strtotime($sendtimeformat, current_time('timestamp', 1));
        $options['sendtype'] = (isset($_POST['sendnow']))? 'sendnow' : 'cronsend';
        if($options['sendtype'] == 'cronsend'){
          $transient_unique = rand(1000,3000).current_time('timestamp', 1);
          set_transient('smpush_cronsend_'.$transient_unique, $options, ($options['sendtime']+864000));
          $options['uniqueoperid'] = $transient_unique;
        }
        else{
          self::updateStats('totalsend', $alltokens);
        }
        set_transient('smpush_send_options', $options, 2592000);
        set_transient('smpush_post', $_POST, 43200);
        set_transient('smpush_query', $smpush_query, 43200);
        if($alltokens == 0)
          $increration = 0;
        else
          $increration = ceil($alltokens/20);
        self::send_process(false, $alltokens, $increration, $feedback);
      }
      else{
        wp_redirect(admin_url().'admin.php?page=smpush_send_notification');
      }
    }
    elseif(isset ($_GET['lastid'], $_GET['increration'])) {
      $lstid_trans = get_transient('smpush_lastid');
      if($lstid_trans !== false) {
        $_GET['lastid'] = $lstid_trans;
      }
      $smpush_query = get_transient('smpush_query');
      $options = get_transient('smpush_send_options');
      $query = str_replace(array('[lastid]', '[limit]'), array($_GET['lastid'], $_GET['increration']), $smpush_query);
      $tokens = self::$pushdb->get_results($query);
      if(!empty (self::$pushdb->last_error)) {
        self::jsonPrint(0, '<p class="error">Please reconfig the default push notification database connection</p>');
      }
      if($tokens) {
        if($options['sendtype'] == 'sendnow'){
          foreach($tokens AS $token) {
            $wpdb->insert($wpdb->prefix.'push_queue', array('token' => $token->device_token, 'device_type' => $token->device_type));
            $lastid = $token->token_id;
          }
        }
        else{
          foreach($tokens AS $token) {
            $wpdb->insert($wpdb->prefix.'push_cron_queue', array('token' => $token->device_token, 'device_type' => $token->device_type, 'sendtime' => $options['sendtime'], 'sendoptions' => $options['uniqueoperid']));
            $lastid = $token->token_id;
          }
        }
        set_transient('smpush_lastid', $lastid, 43200);
        set_transient('smpush_resum', 1, 43200);
        self::jsonPrint(1, $lastid);
      }
      delete_transient('smpush_lastid');
      delete_transient('smpush_post');
      delete_transient('smpush_query');
      delete_transient('smpush_resum');
      if($options['sendtype'] == 'sendnow'){
        self::jsonPrint(-1, '');
      }
      else{
        self::archiveMsgLog($options['message'], $options['sendtime'], $options['uniqueoperid']);
        self::jsonPrint(-2, '');
      }
    }
    else{
      $queuecount = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."push_queue");
      if($queuecount > 0) {
        self::send_process(true, $queuecount);
      }
      else{
        $params = array();
        $params['all'] = self::$defconnection['counter'];
        $params['ios'] = self::$pushdb->get_var(self::parse_query("SELECT COUNT({id_name}) FROM {tbname} WHERE {type_name}='{ios_name}'"));
        $params['channels'] = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."push_channels ORDER BY id ASC");
        $params['dbtype'] = $wpdb->get_var("SELECT dbtype FROM ".$wpdb->prefix."push_connection WHERE id='".self::$apisetting['def_connection']."'");
        wp_enqueue_script('postbox');
        self::$history = get_option('smpush_history', $_POST);
        self::loadpage('send_notification', 0, $params);
      }
    }
  }

  public static function RunQueue() {
    global $wpdb;
    $iphone_devices = array();
    $android_devices = array();
    $icounter = 0;
    $counter = 0;
    $options = get_transient('smpush_send_options');
    $all_count = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix."push_queue");
    $os_name = $wpdb->get_row("SELECT android_name,ios_name FROM ".$wpdb->prefix."push_connection WHERE id='".self::$apisetting['def_connection']."'");
    if($options['iostestmode'] == 1) {
      $limit = 1;
    }
    else{
      $limit = 1000;
    }
    $queue = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."push_queue WHERE device_type='$os_name->ios_name' LIMIT 0,$limit");
    $queue2 = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."push_queue WHERE device_type='$os_name->android_name' LIMIT 0,$limit");
    if(!$queue && !$queue2) {
      self::connectFeedback($all_count);
      delete_transient('smpush_send_options');
      self::updateStats('all');
    }
    foreach($queue AS $queueone) {
      $iphone_devices[$icounter]['token'] = $queueone->token;
      $iphone_devices[$icounter]['id'] = $queueone->id;
      $icounter++;
    }
    foreach($queue2 AS $queueone) {
      $android_devices['token'][$counter] = $queueone->token;
      $android_devices['id'][$counter] = $queueone->id;
      $counter++;
    }
    $message = $options['message'];
    if(!session_id()) {
      session_start();
    }
    if($icounter > 0)
      self::connectPush($message, $iphone_devices, 'ios', $options, true, $all_count);
    if($counter > 0)
      self::connectPush($message, $android_devices, 'android', $options, true, $all_count);
    self::jsonPrint(1, array('message' => '', 'all_count' => $all_count));
  }

  public static function connectPush($message, $device_token, $device_type, $options, $showerror = true, $all_count = 0, $cronjob=false) {
    global $wpdb;
    self::$cronSendOperation = $cronjob;
    if($cronjob === true){
      smpush_helper::$returnValue = 'cronjob';
    }
    $message = str_replace('"', '', trim(stripslashes($message)));
    self::$sendoptions = $options;
    if($device_type == 'ios') {
      $payload = self::getPayload($message);
      $ctx = stream_context_create();
      stream_context_set_option($ctx, 'ssl', 'local_cert', self::$apisetting['apple_cert_path']);
      stream_context_set_option($ctx, 'ssl', 'passphrase', self::$apisetting['apple_passphrase']);
      if(self::$apisetting['apple_sandbox'] == 1) {
        $appleserver = 'tls://gateway.sandbox.push.apple.com:2195';
      }
      else{
        $appleserver = 'tls://gateway.push.apple.com:2195';
      }
      @$fpssl = stream_socket_client($appleserver, $err, $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);
      if(!$fpssl && $showerror) {
        if(empty ($errstr))
          $errstr = 'Apple Certification error or problem with Password phrase';
        if($err == 111)
          $errstr .= ' - Contact your host to enable outgoing ports';
        elseif($errstr == 'Connection timed out') {
          @fclose($fpssl);
          sleep(10);
          return self::jsonPrint(2, array('message' => '<p class="error">Connection timed out or your host blocked the outgoing port 2195...System trying reconnect now</p>', 'all_count' => $all_count));
        }
        self::jsonPrint(0, '<p class="error">Could not establish connection with SSL server: '.$errstr.'</p>');
      }
      elseif(!$fpssl) return;
      elseif(!empty($_GET['firstrun'])) {
        $_SESSION['smpush_firstrun'] = 1;
        @fclose($fpssl);
        self::jsonPrint(2, array('message' => '<p>Connection With Apple server established successfully</p>', 'all_count' => $all_count));
      }
      if(self::$sendoptions['expire'] > 0) {
        $expiry = current_time('timestamp', 1)+(self::$sendoptions['expire']*3600);
      }
      else{
        $expiry = 0;
      }
      foreach($device_token AS $key => $sDevice) {
        $sDevice['token'] = str_replace(array(' ', '-'), '', $sDevice['token']);
        if(isset($sDevice['id']) && $cronjob === false) {
          $wpdb->query("DELETE FROM ".$wpdb->prefix."push_queue WHERE id='".$sDevice['id']."'");
        }
        unset($device_token[$key]);
        if(preg_match('~^[a-f0-9]{64}$~i', $sDevice['token'])) {
          if($expiry > 0) {
            @$sslwrite = chr(1).pack("N", $sDevice['id']).pack("N", $expiry).pack("n", 32).pack('H*', $sDevice['token']).pack("n", strlen($payload)).$payload;
          }
          else{
            @$sslwrite = chr(0).pack('n', 32).pack('H*', $sDevice['token']).pack('n', strlen($payload)).$payload;
          }
          $sslwriteLen = strlen($sslwrite);
          if($sslwriteLen !== (int) @fwrite($fpssl, $sslwrite)) {
            @fclose($fpssl);
            sleep(3);
            return self::jsonPrint(2, array('message' => '', 'all_count' => $all_count));
          }
          if(!empty($_SESSION['smpush_firstrun']) OR (self::$sendoptions['iostestmode'] == 1 AND $cronjob === false)) {
            stream_set_blocking($fpssl, 0);
            stream_set_write_buffer($fpssl, 0);
            $read = array($fpssl);
            $null = NULL;
            $nChangedStreams = stream_select($read, $null, $null, 0, 1000000);
            if($nChangedStreams !== false && $nChangedStreams > 0) {
              $status = @ord(fread($fpssl, 1));
              if(in_array($status, array(3, 4, 6, 7))) {
                @fclose($fpssl);
                self::jsonPrint(0, '<p class="error">Apple server error: '.self::$_aErrorResponseMessages[$status].'</p>');
              }
              if($status == 8) {
                $wpdb->insert($wpdb->prefix.'push_feedback', array('tokens' => $sDevice['token'], 'device_type' => 'ios_invalid'));
                @fclose($fpssl);
                if(self::$sendoptions['iostestmode'] == 1) {
                  $_SESSION['smpush_firstrun'] = 0;
                  self::jsonPrint(2, array('message' => '<p>Apple server accepts the payload and start working</p>', 'all_count' => $all_count));
                }
                else{
                  self::connectPush($message, $device_token, $device_type, $options, true, $all_count, $cronjob);
                }
              }
            }
          }
          if(!empty($_SESSION['smpush_firstrun'])) {
            $_SESSION['smpush_firstrun'] = 0;
            @fclose($fpssl);
            self::jsonPrint(2, array('message' => '<p>Apple server accepts the payload and start working</p>', 'all_count' => $all_count));
          }
        }
        else{
          $wpdb->insert($wpdb->prefix.'push_feedback', array('tokens' => $sDevice['token'], 'device_type' => 'ios_invalid'));
        }
      }
      fclose($fpssl);
    }
    else{
      $baseurl = 'https://android.googleapis.com/gcm/send';
      if(self::$apisetting['android_titanium_payload'] == 1){
        $data = array();
        $data['android']['alert'] = $message;
      }
      else{
        $data = array('message' => $message);
      }
      if(!empty (self::$sendoptions['and_extravalue'])) {
        if(self::$sendoptions['and_extra_type'] == 'normal') {
          $data['relatedvalue'] = stripslashes(self::$sendoptions['and_extravalue']);
        }
        else{
          $extravalue = json_decode(self::$sendoptions['and_extravalue']);
          if($extravalue) {
            foreach($extravalue AS $key => $value) {
              $data[$key] = stripslashes($value);
            }
          }
        }
      }
      elseif(!empty (self::$sendoptions['extravalue'])) {
        if(self::$sendoptions['extra_type'] == 'normal') {
          $data['relatedvalue'] = stripslashes(self::$sendoptions['extravalue']);
        }
        else{
          $extravalue = json_decode(self::$sendoptions['extravalue']);
          if($extravalue) {
            foreach($extravalue AS $key => $value) {
              $data[$key] = stripslashes($value);
            }
          }
        }
      }
      $fields = array('registration_ids' => $device_token['token'], 'data' => $data);
      if(self::$sendoptions['expire'] > 0) {
        $fields['time_to_live'] = self::$sendoptions['expire']*3600;
      }
      $headers = array('Authorization: key='.self::$apisetting['google_apikey'], 'Content-Type: application/json');
      if(!function_exists('curl_init') && $showerror)
        self::jsonPrint(0, '<p class="error">Google: CURL Library is not support in your host</p>');
      elseif(!function_exists('curl_init'))
        return;
      $chandle = curl_init();
      curl_setopt($chandle, CURLOPT_URL, $baseurl);
      curl_setopt($chandle, CURLOPT_POST, true);
      curl_setopt($chandle, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($chandle, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($chandle, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($chandle, CURLOPT_POSTFIELDS, json_encode($fields));
      $result = curl_exec($chandle);
      $httpcode = curl_getinfo($chandle, CURLINFO_HTTP_CODE);
      if($result === FALSE && $showerror) {
        self::jsonPrint(0, '<p class="error">Google push notification server not responding</p>');
      }
      elseif($httpcode == 503 && $showerror) {
        self::jsonPrint(0, '<p class="error">Google push notification server not responding</p>');
      }
      elseif($httpcode == 401 && $showerror) {
        $result = json_decode($result);
        if(!empty ($result->results[0]->error))
          self::jsonPrint(0, '<p class="error">'.$result->results[0]->error.'</p>');
        else
          self::jsonPrint(0, '<p class="error">Invalid Google API key</p>');
      }
      if(isset ($device_token['id'])) {
        self::updateStats('androidsend', count($device_token['id']), $cronjob);
        if($cronjob === false){
          $ids = implode(',', $device_token['id']);
          $wpdb->query("DELETE FROM ".$wpdb->prefix."push_queue WHERE id IN($ids)");
        }
        if(self::$sendoptions['feedback'] == 1) {
          $wpdb->insert($wpdb->prefix.'push_feedback', array('tokens' => serialize($device_token['token']), 'feedback' => $result, 'device_type' => 'android'));
        }
      }
      curl_close($chandle);
      if(!empty($_GET['google_notify'])) {
        self::jsonPrint(3, array('message' => '<p>Connection With Google server established successfully</p><p>Google server accepts the payload and start working</p>', 'all_count' => $all_count));
      }
    }
  }

  public static function connectFeedback($all_count, $cronjob=false) {
    global $wpdb;
    self::$cronSendOperation = $cronjob;
    if($cronjob === true){
      smpush_helper::$returnValue = 'cronjob';
    }
    $fail = $androidfail = 0;
    $feedbacks = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."push_feedback");
    foreach($feedbacks AS $feedback) {
      if($feedback->device_type == 'ios_invalid') {
        self::$pushdb->query(self::parse_query("UPDATE {tbname} SET {active_name}='0' WHERE {token_name}='".$feedback->tokens."'"));
        self::updateStats('iosfail', 1);
      }
      elseif($feedback->device_type == 'ios') {
        if(!empty($_GET['feedback_open'])) {
          self::jsonPrint(4, '<p>Start connection and reading with Apple feedback server, Maybe takes some time</p>');
        }
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', self::$apisetting['apple_cert_path']);
        stream_context_set_option($ctx, 'ssl', 'passphrase', self::$apisetting['apple_passphrase']);
        if(self::$apisetting['apple_sandbox'] == 1) {
          $appleserver = 'tls://feedback.sandbox.push.apple.com:2196';
        }
        else{
          $appleserver = 'tls://feedback.push.apple.com:2196';
        }
        @$fpssl = stream_socket_client($appleserver, $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);
        if(!$fpssl) {
          if(empty ($errstr))
            $errstr = 'Apple certification error or problem with Password phrase';
          if($err == 111)
            $errstr .= ' - Contact your host to enable outgoing ports';
          self::jsonPrint(0, '<p class="error">Could not establish connection with SSL server: '.$errstr.'</p>');
        }
        $nFeedbackTupleLen = self::TIME_BINARY_SIZE+self::TOKEN_LENGTH_BINARY_SIZE+self::DEVICE_BINARY_SIZE;
        $sBuffer = '';
        while(!feof($fpssl)) {
          $sBuffer .= $sCurrBuffer = fread($fpssl, 8192);
          $nCurrBufferLen = strlen($sCurrBuffer);
          unset ($sCurrBuffer, $nCurrBufferLen);
          $nBufferLen = strlen($sBuffer);
          if($nBufferLen >= $nFeedbackTupleLen) {
            $nFeedbackTuples = floor($nBufferLen/$nFeedbackTupleLen);
            for($i = 0; $i < $nFeedbackTuples; $i++) {
              $sFeedbackTuple = substr($sBuffer, 0, $nFeedbackTupleLen);
              $sBuffer = substr($sBuffer, $nFeedbackTupleLen);
              $aFeedback = self::_parseBinaryTuple($sFeedbackTuple);
              self::$pushdb->query(self::parse_query("UPDATE {tbname} SET {active_name}='0' WHERE {token_name}='$aFeedback[deviceToken]'"));
              $fail++;
              unset ($aFeedback);
            }
          }
          $read = array($fpssl);
          $null = NULL;
          $nChangedStreams = stream_select($read, $null, $null, 0, 1000000);
          if($nChangedStreams === false) {
            break;
          }
        }
        self::updateStats('iosfail', $fail);
        if($fail > 0) {
          self::jsonPrint(2, array('message' => '<p>Reading from Apple feedback is finised, try to read again for more</p>', 'all_count' => $all_count));
        }
      }
      else{
        if(!empty($_GET['feedback_google'])) {
          self::jsonPrint(5, '<p>Start processing Google feedback queries</p>');
        }
        $tokens = unserialize($feedback->tokens);
        $result = json_decode($feedback->feedback);
        foreach($result->results AS $key => $status) {
          if(isset ($status->error)) {
            if($status->error == 'InvalidRegistration' || $status->error == 'NotRegistered' || $status->error == 'MismatchSenderId') {
              self::$pushdb->query(self::parse_query("UPDATE {tbname} SET {active_name}='0' WHERE {token_name}='$tokens[$key]'"));
              $androidfail++;
            }
          }
        }
        self::updateStats('androidfail', $androidfail);
      }
      $wpdb->query("DELETE FROM ".$wpdb->prefix."push_feedback WHERE id='".$feedback->id."'");
    }
  }

  protected static function _getPayload($message) {
    if(self::$apisetting['ios_titanium_payload'] == 1){
      $aPayload['aps'] = array();
      $aPayload['aps']['alert'] = $message;
      if(!empty (self::$sendoptions['ios_sound'])) {
        $aPayload['aps']['sound'] = stripslashes(self::$sendoptions['ios_sound']);
      }
      if(!empty (self::$sendoptions['ios_badge'])) {
        $aPayload['aps']['badge'] = self::$sendoptions['ios_badge'];
      }
      if(!empty (self::$sendoptions['extravalue'])) {
        if(self::$sendoptions['extra_type'] == 'normal') {
          $aPayload['aps']['relatedvalue'] = stripslashes(self::$sendoptions['extravalue']);
        }
        else{
          $extravalue = json_decode(self::$sendoptions['extravalue']);
          if($extravalue) {
            foreach($extravalue AS $key => $value) {
              $aPayload['aps'][$key] = stripslashes($value);
            }
          }
        }
      }
    }
    else{
      $aPayload['aps'] = array();
      if(!empty (self::$sendoptions['ios_slide']) OR !empty (self::$sendoptions['ios_launchimg'])){
        $aPayload['aps']['alert']['body'] = $message;
        if(!empty (self::$sendoptions['ios_slide'])) {
          $aPayload['aps']['alert']['action-loc-key'] = stripslashes(self::$sendoptions['ios_slide']);
        }
        if(!empty (self::$sendoptions['ios_launchimg'])) {
          $aPayload['aps']['alert']['launch-image'] = stripslashes(self::$sendoptions['ios_launchimg']);
        }
      }
      else{
        $aPayload['aps']['alert'] = $message;
      }
      if(!empty (self::$sendoptions['ios_sound'])) {
        $aPayload['aps']['sound'] = stripslashes(self::$sendoptions['ios_sound']);
      }
      if(!empty (self::$sendoptions['ios_cavailable'])) {
        $aPayload['aps']['content-available'] = self::$sendoptions['ios_cavailable'];
      }
      if(!empty (self::$sendoptions['ios_badge'])) {
        $aPayload['aps']['badge'] = self::$sendoptions['ios_badge'];
      }
      if(!empty (self::$sendoptions['extravalue'])) {
        if(self::$sendoptions['extra_type'] == 'normal') {
          $aPayload['aps']['relatedvalue'] = stripslashes(self::$sendoptions['extravalue']);
        }
        else{
          $extravalue = json_decode(self::$sendoptions['extravalue']);
          if($extravalue) {
            foreach($extravalue AS $key => $value) {
              $aPayload['aps'][$key] = stripslashes($value);
            }
          }
        }
      }
    }
    return $aPayload;
  }

  protected static function getPayload($message) {
    if(phpversion() < 5.4){
      return json_encode(self::_getPayload($message));
    }
    @$sJSON = json_encode(self::_getPayload($message), defined('JSON_UNESCAPED_UNICODE')?JSON_UNESCAPED_UNICODE:0);
    if(!defined('JSON_UNESCAPED_UNICODE') && function_exists('mb_convert_encoding')) {
      $sJSON = preg_replace_callback('~\\\\u([0-9a-f]{4})~i', create_function('$aMatches', 'return mb_convert_encoding(pack("H*", $aMatches[1]), "UTF-8", "UTF-16");'), $sJSON);
    }
    $sJSONPayload = str_replace('"aps":[]', '"aps":{}', $sJSON);
    $nJSONPayloadLen = strlen($sJSONPayload);
    if($nJSONPayloadLen > 256) {
      $nMaxTextLen = $nTextLen = strlen($message)-($nJSONPayloadLen-256);
      if($nMaxTextLen > 0) {
        while(strlen($message = mb_substr($message, 0,--$nTextLen, 'UTF-8')) > $nMaxTextLen);
        return self::getPayload($message);
      }
      else{
        self::jsonPrint(0, '<p class="error">Apple notification message is too long: '.$nJSONPayloadLen.' bytes. Maximum size is 256 bytes</p>');
      }
    }
    return $sJSONPayload;
  }

  protected static function _parseBinaryTuple($sBinaryTuple) {
    return unpack('Ntimestamp/ntokenLength/H*deviceToken', $sBinaryTuple);
  }

  public static function updateStats($index = '', $value = 0, $cronjob=false) {
    if(self::$cronSendOperation === true OR $cronjob === true){
      $transient = 'smpush_cron_stats';
    }
    else{
      $transient = 'smpush_stats';
    }
    if(empty ($index)) {
      if(self::$cronSendOperation === false AND $cronjob === false AND !empty(self::$sendoptions['message'])){
        global $wpdb;
        $wpdb->insert($wpdb->prefix.'push_archive', array('message' => self::$sendoptions['message']));
        $archiveid = $wpdb->insert_id;
      }
      else{
        $archiveid = 0;
      }
      $stats = array('totalsend' => 0, 'iosfail' => 0, 'androidsend' => 0, 'androidfail' => 0, 'archiveid' => $archiveid);
      set_transient($transient, $stats, 43200);
      return;
    }
    $stats = get_transient($transient);
    if($index == 'all') {
      $stats['iossend'] = $stats['totalsend']-$stats['androidsend'];
      $stats['totalfail'] = $stats['iosfail']+$stats['androidfail'];
      $archid = $stats['archiveid'];
      unset($stats['archiveid']);
      $result = self::printReport($stats);
      if(self::$cronSendOperation === true){
        return $stats;
      }
      global $wpdb;
      $wpdb->update($wpdb->prefix.'push_archive', array('endtime' => date('Y-m-d H:i:s'), 'report' => serialize($stats)), array('id' => $archid));
      return self::jsonPrint(-1, $result);
    }
    if($index == 'totalsend') {
      if($stats[$index] > 0)
        return;
    }
    $stats[$index] = $stats[$index]+$value;
    set_transient($transient, $stats, 43200);
  }

  public static function printReport($stats) {
    if(isset($stats['error'])){
      return '<p><strong>'.$stats['error'].'</strong></p>';
    }
    $result = '<p><strong>IOS Report:</strong></p>';
    $result .= '<p>Total sent messages: '.$stats['iossend'].' message</p>';
    $result .= '<p>Failure to deliver or invalid tokens: '.$stats['iosfail'].' device token</p>';
    $result .= '<p><strong>Android Report:</strong></p>';
    $result .= '<p>Total sent messages: '.$stats['androidsend'].' message</p>';
    $result .= '<p>Successful delivered: '.($stats['androidsend']-$stats['androidfail']).' message</p>';
    $result .= '<p>Failure to deliver and invalid tokens: '.$stats['androidfail'].' message</p>';
    $result .= '<p><strong>Total Report:</strong></p>';
    $result .= '<p>Total sent: '.$stats['totalsend'].' message</p>';
    $result .= '<p>Failure to deliver or invalid tokens: '.$stats['totalfail'].' device token</p>';
    return $result;
  }

}

?>