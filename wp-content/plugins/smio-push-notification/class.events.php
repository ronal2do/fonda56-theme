<?php

class smpush_events extends smpush_controller{

  public function __construct(){
    parent::__construct();
  }

  private static function processNotifBody($type, $subject){
    $type = $type.'_body';
    $message = str_replace(array('{subject}','{comment}'), $subject, stripslashes(self::$apisetting[$type]));
    return $message;
  }

  public static function post_approved($postid){
    if(!empty($_POST['post_status']) && !empty($_POST['original_post_status'])){
      if(($_POST['post_status'] == 'publish') && ($_POST['original_post_status'] != 'publish')){
        if(self::$apisetting['e_newpost'] == 1){
          if(self::$apisetting['e_post_chantocats'] == 1){
            $authorid = self::PushUsersInPostCat($postid);
          }
          else{
            $authorid = 'all';
          }
          if($authorid !== false){
            $subject = self::ShortString(get_the_title($postid), 60);
            $message = self::processNotifBody('e_newpost', $subject);
            smpush_sendpush::SendCronPush($authorid, $message, $postid, 'tokenid');
          }
        }
        if(self::$apisetting['e_apprpost'] == 1){
          $authorid = self::UsersRelatedPost($postid);
          if($authorid !== false){
            $subject = self::ShortString(get_the_title($postid), 60);
            $message = self::processNotifBody('e_apprpost', $subject);
            smpush_sendpush::SendCronPush($authorid, $message, $postid);
          }
        }
      }
      elseif(($_POST['post_status'] == 'publish') && ($_POST['original_post_status'] == 'publish')){
        if(self::$apisetting['e_postupdated'] == 0) return;
        $authorid = self::UsersRelatedPost($postid, true);
        if($authorid !== false){
          $subject = self::ShortString(get_the_title($postid), 60);
          $message = self::processNotifBody('e_postupdated', $subject);
          smpush_sendpush::SendCronPush($authorid, $message, $postid);
        }
      }
    }
  }

  public static function comment_approved($nowcomment){
    global $wpdb;
    if(self::$apisetting['e_appcomment'] == 1){
      $subject = self::ShortString($nowcomment->comment_content, 60);
      $message = self::processNotifBody('e_appcomment', $subject);
      smpush_sendpush::SendCronPush(array(0=>$nowcomment->user_id), $message, $nowcomment->comment_post_ID);
    }
    self::new_comment($nowcomment->comment_ID, $nowcomment);
  }

  public static function new_comment($commid, $nowcomment){
    global $wpdb;
    if($nowcomment->comment_approved == 1){
      if(self::$apisetting['e_usercomuser'] == 1){
        if($nowcomment->comment_parent > 0){
          $comment = $wpdb->get_row("SELECT comment_post_ID,user_id FROM ".$wpdb->prefix."comments WHERE comment_ID='".$nowcomment->comment_parent."' AND user_id>0", 'ARRAY_A');
          if(!$comment) return false;
          $commentcount = $wpdb->get_var("SELECT COUNT(comment_ID) AS commcount FROM ".$wpdb->prefix."comments WHERE comment_parent='".$nowcomment->comment_parent."' AND comment_approved='1'");
          if($commentcount>0 AND ($commentcount==1 OR $commentcount%5==0)){
            $subject = self::ShortString($nowcomment->comment_content, 60);
            $message = self::processNotifBody('e_usercomuser', $subject);
            smpush_sendpush::SendCronPush(array(0=>$comment['user_id']), $message, $comment['comment_post_ID']);
          }
        }
      }
      if(self::$apisetting['e_newcomment'] == 1){
        $postid = $nowcomment->comment_post_ID;
        $commentcount = $wpdb->get_var("SELECT COUNT(comment_ID) AS commcount FROM ".$wpdb->prefix."comments WHERE comment_post_ID='$postid' AND comment_approved='1'");
        if($commentcount>0 AND ($commentcount==1 OR $commentcount%10==0)){
          $post = $wpdb->get_row("SELECT post_title,post_author FROM ".$wpdb->prefix."posts WHERE ID='$postid'", 'ARRAY_A');
          $subject = self::ShortString($post['post_title'], 60);
          $message = self::processNotifBody('e_newcomment', $subject);
          smpush_sendpush::SendCronPush(array(0=>$post['post_author']), $message, $postid);
        }
      }
    }
  }

  private static function UserRelatedComment($commid){
    global $wpdb;
    $userid = $wpdb->get_var("SELECT user_id FROM ".$wpdb->prefix."comments WHERE comment_ID='$commid'");
    if(!$userid) return false;
    return $userid;
  }
  
  private static function PushUsersInPostCat($postid){
    global $wpdb;
    $ids = array();
    $channelids = array();
    $post_categories = wp_get_post_categories($postid);
    foreach($post_categories as $catobject){
      $category = get_category($catobject);
      $channelid = $wpdb->get_var("SELECT id FROM ".$wpdb->prefix."push_channels WHERE title LIKE '$category->name'");
      if($channelid){
        $channelids[] = $channelid;
      }
    }
    if(!empty($channelids)){
      $channelids = implode(',', $channelids);
      $tokenids = $wpdb->get_results("SELECT DISTINCT(token_id) FROM ".$wpdb->prefix."push_relation WHERE channel_id IN($channelids) AND connection_id='".self::$apisetting['def_connection']."'");
      if(!$tokenids) return false;
      foreach($tokenids AS $tokenid){
        $ids[] = $tokenid->token_id;
      }
    }
    return $ids;
  }

  private static function AllPushUsers(){
    $ids = array();
    $authorids = self::$pushdb->get_results(self::parse_query("SELECT userid FROM {tbname} WHERE userid>0 AND {active_name}='1'"));
    if(!$authorids) return false;
    foreach($authorids AS $authorid){
      $ids[] = $authorid->userid;
    }
    return $ids;
  }

  private static function UsersRelatedPost($postid, $allrealted=false){
    global $wpdb;
    $ids = array();
    $authorid = $wpdb->get_var("SELECT post_author FROM ".$wpdb->prefix."posts WHERE ID='$postid' AND post_status='publish' AND post_type='post' AND post_password=''");
    if(!$authorid) return false;
    $ids[] = $authorid;
    if($allrealted){
      $sql = "SELECT user_id FROM ".$wpdb->prefix."comments WHERE comment_post_ID='$postid' AND user_id>0 GROUP BY user_id";
      $gets = $wpdb->get_results($sql, 'ARRAY_A');
      if($gets){
        foreach($gets AS $get){
          $ids[] = $get['user_id'];
        }
      }
    }
    return $ids;
  }

}

?>