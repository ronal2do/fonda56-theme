<?php
unistall();

// Delete Databse
function unistall(){

  delete_option('gcm_setting');
  delete_option('px_gcm_total_msg');
  delete_option('px_gcm_suc_msg');
  delete_option('px_gcm_fail_msg');
  
  global $wpdb;
  $table_name = $wpdb->prefix .'gcm_users';
  $wpdb->query("DROP TABLE IF EXISTS $table_name");
}
?>