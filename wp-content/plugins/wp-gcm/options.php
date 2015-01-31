<?php

$gcm_result = "should be empty";

$version = get_bloginfo('version');
if ($version < 3.8) {
	function px_gcm_menu() {
		add_menu_page('GCM', 'GCM', 'manage_options', 'px-gcm','');
		add_submenu_page( 'px-gcm', __('New Message','px_gcm'), __('New Message','px_gcm'), 'manage_options', 'px-gcm', 'px_display_page_msg');
		add_submenu_page( 'px-gcm', __('All Devices','px_gxm'), __('All Devices','px_gxm'), 'manage_options', 'px-gcm-devices', 'px_display_devices');
		add_submenu_page( 'px-gcm', __('Stats','px_gxm'), __('Stats','px_gxm'), 'manage_options', 'px-gcm-stats', 'px_display_stats');
		add_submenu_page( 'px-gcm', __('Export','px_gxm'), __('Export','px_gxm'), 'manage_options', 'px-gcm-export', 'px_display_export');
		add_submenu_page( 'px-gcm', __('Settings','px_gcm'), __('Settings','px_gcm'), 'manage_options', 'px-gcm-settings', 'px_display_page_setting');
	}
	add_action('admin_menu', 'px_gcm_menu');
}else { 
	function px_gcm_menu() {
		add_menu_page('GCM', 'GCM', 'manage_options', 'px-gcm','','dashicons-cloud');
        add_submenu_page( 'px-gcm', __('New Message','px_gcm'), __('New Message','px_gcm'), 'manage_options', 'px-gcm', 'px_display_page_msg');
		add_submenu_page( 'px-gcm', __('All Devices','px_gxm'), __('All Devices','px_gxm'), 'manage_options', 'px-gcm-devices', 'px_display_devices');
		add_submenu_page( 'px-gcm', __('Stats','px_gxm'), __('Stats','px_gxm'), 'manage_options', 'px-gcm-stats', 'px_display_stats');
		add_submenu_page( 'px-gcm', __('Export','px_gxm'), __('Export','px_gxm'), 'manage_options', 'px-gcm-export', 'px_display_export');
        add_submenu_page( 'px-gcm', __('Settings','px_gcm'), __('Settings','px_gcm'), 'manage_options', 'px-gcm-settings', 'px_display_page_setting');  
	}
	add_action('admin_menu', 'px_gcm_menu');
}

/*
*
* All the functions for the settings page
*
*/
function px_register_settings() {
	add_settings_section('gcm_setting-section', '', 'gcm_section_callback', 'px-gcm');
	add_settings_field('api-key', __('Api Key','px_gcm'), 'api_key_callback', 'px-gcm', 'gcm_setting-section');
	add_settings_field('snpi', __('New post info','px_gcm'), 'snpi_callback', 'px-gcm', 'gcm_setting-section');
    add_settings_field('supi', __('Updated post info','px_gcm'), 'supi_callback', 'px-gcm', 'gcm_setting-section');
    add_settings_field('abd', __('Display admin bar link','px_gcm'), 'abd_callback', 'px-gcm', 'gcm_setting-section' );
	add_settings_field('debug', __('Show debug response','px_gcm'), 'debug_callback', 'px-gcm', 'gcm_setting-section' );
	register_setting( 'px-gcm-settings-group', 'gcm_setting', 'px_gcm_settings_validate' );
}
 
 // load the translations
function px_gcm_load_textdomain() {
  load_plugin_textdomain( 'px_gcm', false, basename( dirname( __FILE__ ) ) . '/lang' ); 
}

function gcm_section_callback() {
    echo __('Required settings for the plugin and the App.','px_gcm');
}

function api_key_callback() {
    $options = get_option('gcm_setting');
    ?>
<input type="text" name="gcm_setting[api-key]" size="41" value="<?php echo $options['api-key']; ?>" />
<?php
}

function snpi_callback(){
    $options = get_option('gcm_setting');
	$html = '<input type="checkbox" id="snpi" name="gcm_setting[snpi]" value="1"' . checked(1, $options['snpi'], false) . '/>';
	echo $html;
}

function supi_callback(){
    $options = get_option('gcm_setting');
	$html= '<input type="checkbox" id="supi" name="gcm_setting[supi]" value="1"' . checked(1, $options['supi'], false) . '/>';
	echo $html;
}

function abd_callback() {
    $options = get_option('gcm_setting');
    $html = '<input type="checkbox" id="abd" name="gcm_setting[abd]" value="1"' . checked(1, $options['abd'], false) . '/>';
	echo $html;
}

function debug_callback() {
    $options = get_option('gcm_setting');
    $html = '<input type="checkbox" id="debug" name="gcm_setting[debug]" value="1"' . checked(1, $options['debug'], false) . '/>';
	echo $html;
}

function px_gcm_settings_validate($arr_input) {
	$options = get_option('gcm_setting');
	
	if(isset($arr_input['api-key'])) {
		$options['api-key'] = trim($arr_input['api-key']);
	}
	if(isset($arr_input['snpi'])) {
		$options['snpi'] = trim($arr_input['snpi']);
	}
	if(isset($arr_input['supi'])) {
		$options['supi'] = trim($arr_input['supi']);
	}
	if(isset($arr_input['abd'])) {
		$options['abd'] = trim($arr_input['abd']);
	}
	if(isset($arr_input['debug'])) {
		$options['debug'] = trim($arr_input['debug']);
	}
	
    return $options;
}

/*
*
* Send notification for post update
*
*/
function px_update_notification($new_status, $old_status, $post) {
	$options = get_option('gcm_setting');
	if($options['snpi'] != false){
		if ($old_status == 'publish' && $new_status == 'publish' && 'post' == get_post_type($post)) {
			$post_title = get_the_title($post);
			$post_url = get_permalink($post);	   
			$post_id = get_the_ID($post);
			$post_author = get_the_author_meta('display_name', $post->post_author);
			$message = $post_title . ";" . $post_url . ";". $post_id . ";" . $post_author . ";";

			// Send notification
			$up = "update";
			px_sendGCM($message, $up, 010);
		}
	}
}

/*
*
* Send notification for new post
*
*/
function px_new_notification($new_status, $old_status, $post) {
	$options = get_option('gcm_setting');
	if($options['snpi'] != false){
		if ($old_status != 'publish' && $new_status == 'publish' && 'post' == get_post_type($post)) {
			$post_title = get_the_title($post);
			$post_url = get_permalink($post);
			$post_id = get_the_ID($post);
			$post_author = get_the_author_meta('display_name', $post->post_author);
			$message = $post_title . ";" . $post_url . ";". $post_id . ";" . $post_author . ";";

			// Send notification
			$np = "new_post";
			px_sendGCM($message, $np, 010);
		}
	}
}

/*
*
* Register ToolBar
*
*/
function px_gcm_toolbar() {
	$options = get_option('gcm_setting');
	if($options['abd'] != false){
		global $wp_admin_bar;
		$page = get_site_url().'/wp-admin/admin.php?page=px-gcm';
		$args = array(
			'id'     => 'px_gcm',
			'title'  => '<img class="dashicons dashicons-cloud">GCM</img>', 'px_gcm',
			'href'   =>  "$page" );
			
		$wp_admin_bar->add_menu($args);
	}
}

/*
*
* GCM Send Notification
*
*/
function px_sendGCM($message, $type, $regid) {
	global $wpdb;
	$px_table_name = $wpdb->prefix.'gcm_users';
	$options = get_option('gcm_setting');
    $apiKey = $options['api-key'];
    $url = 'https://android.googleapis.com/gcm/send';
	$result;
	$id;
	
	if($regid == 010) {
		$id = px_getIds();
	}else {
		$id = $regid;
	}
	
	if($id == 010 && $id >= 1000){
		$newId = array_chunk($id, 1000);
		foreach ($newId as $inner_id) {
			$fields = array(
        		'registration_ids' => $inner_id,
        		'data' => array($type => $message) 
			);
			
			$headers = array(
    			'Authorization' => 'key=' . $apiKey,
    			'Content-Type' => 'application/json'
			);
			
			$result = wp_remote_post($url, array(
				'method' => 'POST',
				'headers' => $headers,
				'httpversion' => '1.0',
				'sslverify' => false,
				'body' => json_encode($fields) )
			);
		}
	}else {
		$fields = array(
        	'registration_ids' => $id,
        	'data' => array($type => $message)
		);
		
		$headers = array(
    		'Authorization' => 'key=' . $apiKey,
    		'Content-Type' => 'application/json'
		);
		
		$result = wp_remote_post($url, array(
			'method' => 'POST',
			'headers' => $headers,
			'httpversion' => '1.0',
			'sslverify' => false,
			'body' => json_encode($fields))
		);
		
	}
	
    $msg = $result['body'];
    $answer = json_decode($msg);
    $cano = px_canonical($answer);
    $suc = $answer->{'success'};
    $fail = $answer->{'failure'};
	$options = get_option('gcm_setting');
    if($options['debug'] != false){
		$inf= "<div id='message' class='updated'><p><b>".__('Message sent.','px_gcm')."</b><i>&nbsp;&nbsp;($message)</i></p><p>$msg</p></div>";
	}else {
    	$inf= "<div id='message' class='updated'><p><b>".__('Message sent.','px_gcm')."</b><i>&nbsp;&nbsp;($message)</i></p><p>".__('success:','px_gcm')." $suc  &nbsp;&nbsp;".__('fail:','px_gcm')." $fail </p></div>";
    }
	
	// Updating stats
	$suc_num = get_option('px_gcm_suc_msg', 0);
	update_option('px_gcm_suc_msg', $suc_num+$suc);
	$fail_num = get_option('px_gcm_fail_msg', 0);
	update_option('px_gcm_fail_msg', $fail_num+$fail);
	$total_msg = get_option('px_gcm_total_msg', 0);
	update_option('px_gcm_total_msg', $total_msg+1);
	for($i=0; $i < count($id); $i++) {
		$temp = $id[$i];
		$send_msg = $wpdb->get_row("SELECT send_msg FROM $px_table_name WHERE gcm_regid='$temp' ");
		$new_num = $send_msg->send_msg+1;
		$upquery = "UPDATE $px_table_name SET send_msg=$new_num WHERE gcm_regid='$temp' ";
		$wpdb->query($upquery);
	}
	
	global $gcm_result;
	$gcm_result = $inf;
    return $inf;
}

function px_getIds() {
    global $wpdb;
    $px_table_name = $wpdb->prefix.'gcm_users';
    $devices = array();
    $sql = "SELECT gcm_regid FROM $px_table_name";
    $res = $wpdb->get_results($sql);
    if ($res != false) {
        foreach($res as $row){
            array_push($devices, $row->gcm_regid);
        }
    }
	
    return $devices;
}

function px_canonical($answer) {
   $allIds = px_getIds();
   $resId = array();
   $errId = array();
   $err = array();
   $can = array();
   global $wpdb;
   $px_table_name = $wpdb->prefix.'gcm_users';

   foreach($answer->results as $index=>$element) {
    if(isset($element->registration_id)) {
     $resId[] = $index;
    }
   }
   
   foreach($answer->results as $index=>$element){
    if(isset($element->error)){
      $errId[] = $index;
    }
   }

	if($resId != null) {
		for($i=0; $i<count($allIds); $i++) {
			array_push($can, $allIds[$resId[$i]]);
		}
	}

	if($errId != null) {
		for($i=0; $i<count($allIds); $i++) {
			array_push($err, $allIds[$errId[$i]]);
		}
	}

   if($err != null) {
	for($i=0; $i < count($err); $i++){
		$s = $wpdb->query($wpdb->prepare("DELETE FROM $px_table_name WHERE gcm_regid=%s",$err[$i]));
	}
   } 
   if($can != null) {
	for($i=0; $i < count($can); $i++){
		$r = $wpdb->query($wpdb->prepare("DELETE FROM $px_table_name WHERE gcm_regid=%s",$can[$i]));
	}
   }
}

/* Change the default updated message to a custom one with GCM data */

/* JUST FOR NEXT UPDATE
function px_gcm_update_msg($messages) {
  global $post, $post_ID, $gcm_result;


  $messages['post'] = array(
    0 => '',
    1 => sprintf( __('Post updated. <a href="%1$s">View post</a><br>%2$s'), esc_url(get_permalink($post_ID)), ""), // $gcm_result),
    2 => __('Custom field updated.'),
    3 => __('Custom field deleted.'),
    4 => __('Post updated.'),
    5 => isset($_GET['revision']) ? sprintf( __('Post restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
    6 => sprintf( __('Post published. <a href="%s">View post</a>'), esc_url( get_permalink($post_ID) ) ),
    7 => __('Post saved.'),
    8 => sprintf( __('Post submitted. <a target="_blank" href="%s">Preview post</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
    9 => sprintf( __('Post scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview post</a>'),
      date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
    10 => sprintf( __('Post draft updated. <a target="_blank" href="%s">Preview post</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
  );

  return $messages;
}

*/
add_action('plugins_loaded', 'px_gcm_load_textdomain');
add_action('admin_init', 'px_register_settings');
add_action('transition_post_status', 'px_update_notification',2,3);
add_action('transition_post_status', 'px_new_notification',2,3);
add_action('wp_before_admin_bar_render', 'px_gcm_toolbar');
//add_filter('post_updated_messages', 'px_gcm_update_msg');

?>