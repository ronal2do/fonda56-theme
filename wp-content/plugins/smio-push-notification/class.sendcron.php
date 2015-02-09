<?php

class smpush_cronsend extends smpush_controller {
  private static $startTime;
  private static $totalSent;
  private static $iosCounter;
  private static $andCounter;
  private static $iosDelIDS;
  private static $andDelIDS;
  private static $iosDevices;
  private static $andDevices;
  private static $tempunique;
  private static $message;
  private static $sendoptions;

  public function __construct() {
    parent::__construct();
  }

  public static function destruct() {
    smpush_sendpush::connectFeedback(0, true);
    delete_transient('smpush_cron_stats');
  }
  
  public static function finishQueue() {
    if(self::$totalSent > 0){
      global $wpdb;
      smpush_sendpush::updateStats('totalsend', self::$totalSent, true);
      $report = smpush_sendpush::updateStats('all', 0, true);
      $wpdb->update($wpdb->prefix.'push_archive', array('endtime' => date('Y-m-d H:i:s'), 'report' => serialize($report)), array('transient' => self::$tempunique));
      smpush_sendpush::updateStats('', 0, true);
      self::$totalSent = 0;
    }
  }

  public static function writeLog($log) {
    global $wpdb;
    $wpdb->insert($wpdb->prefix.'push_archive', array('message' => 'Cron job error log', 'report' => serialize(array('error' => $log)), 'starttime' => self::$startTime, 'endtime' => date('Y-m-d H:i:s')));
  }

  public static function resetIOS() {
    self::$iosDevices = array();
    self::$iosDelIDS = array();
    self::$iosCounter = 0;
  }

  public static function resetAND() {
    self::$andDevices = array();
    self::$andDelIDS = array();
    self::$andCounter = 0;
  }

  public static function cronStart() {
    @set_time_limit(0);
    global $wpdb;
    register_shutdown_function(array('smpush_cronsend', 'destruct'));
    self::$startTime = date('Y-m-d H:i:s');
    self::$totalSent = 0;
    self::$tempunique = '';
    self::resetIOS();
    self::resetAND();
    $TIMENOW = current_time('timestamp', 1);
    if(!session_id()) {
      session_start();
    }
    smpush_sendpush::updateStats('', 0, true);
    $ios_name = $wpdb->get_var("SELECT ios_name FROM ".$wpdb->prefix."push_connection WHERE id='".self::$apisetting['def_connection']."'");
    $queue = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."push_cron_queue WHERE $TIMENOW>sendtime ORDER BY sendoptions ASC");
    if($queue) {
      foreach($queue AS $queueone) {
        if(empty(self::$tempunique)){
          self::$tempunique = $queueone->sendoptions;
        }
        if(self::$tempunique != $queueone->sendoptions){
          if(self::$iosCounter > 0)
            self::sendPushCron('ios');
          if(self::$andCounter > 0)
            self::sendPushCron('android');
          self::finishQueue();
          self::$tempunique = $queueone->sendoptions;
        }
        if(self::$iosCounter >= 1000){
          self::sendPushCron('ios');
        }
        if(self::$andCounter >= 1000){
          self::sendPushCron('android');
        }
        if($queueone->device_type == $ios_name) {
          self::$iosDelIDS[] = $queueone->id;
          self::$iosDevices[self::$iosCounter]['token'] = $queueone->token;
          self::$iosDevices[self::$iosCounter]['id'] = $queueone->id;
          self::$iosCounter++;
        }
        else{
          self::$andDelIDS[] = $queueone->id;
          self::$andDevices['token'][self::$andCounter] = $queueone->token;
          self::$andDevices['id'][self::$andCounter] = $queueone->id;
          self::$andCounter++;
        }
        self::$totalSent++;
      }
      if(self::$iosCounter > 0){
        self::sendPushCron('ios');
      }
      if(self::$andCounter > 0){
        self::sendPushCron('android');
      }
    }
    self::finishQueue();
    die();
  }

  public static function sendPushCron($type) {
    global $wpdb;
    self::$sendoptions = get_transient('smpush_cronsend_'.self::$tempunique);
    if(self::$sendoptions === false){
      $wpdb->query("DELETE FROM ".$wpdb->prefix."push_cron_queue WHERE sendoptions='".self::$tempunique."'");
      self::writeLog('System did not find the related data for some cron sending: operation cancelled');
      die();
    }
    if($type == 'ios'){
      $DelIDS = implode(',', self::$iosDelIDS);
      $wpdb->query("DELETE FROM ".$wpdb->prefix."push_cron_queue WHERE id IN($DelIDS)");
      smpush_sendpush::connectPush(self::$sendoptions['message'], self::$iosDevices, 'ios', self::$sendoptions, true, 0, true);
      self::resetIOS();
    }
    else{
      $DelIDS = implode(',', self::$andDelIDS);
      $wpdb->query("DELETE FROM ".$wpdb->prefix."push_cron_queue WHERE id IN($DelIDS)");
      smpush_sendpush::connectPush(self::$sendoptions['message'], self::$andDevices, 'android', self::$sendoptions, true, 0, true);
      self::resetAND();
    }
  }

}

?>