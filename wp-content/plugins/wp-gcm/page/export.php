<?php

function px_display_export() {
?>
	<div class="wrap">
		<h2 class=""><?php _e('Export','px_gcm'); ?></h2>
		<div id="poststuff">
			<?php if( isset($_POST['ok']) ) {
				$li = export();
				?>
				<div id="message" class="updated">
					<p><strong><?php _e('GCM export finished','px_gcm'); ?></strong></p>
					<p><?php printf( __('%1$s click here %2$s to download','px_gcm'),'<a href='.$li.' download>' ,'</a>'); ?></p>
				</div>
			<?php } ?>
			
			<div id="post-body" class="metabox-holder columns-1"> 
				<!-- Inhalt beginnt hier -->
				<div id="post-body-content">
					<div class="postbox">
						<div class="inside">
							<form method="post" action="#">
								<p>
									<input type="hidden" name="ok" value="ok">
									<?php _e('Export the whole Database into an excel readable file.','px_gcm'); ?>
								</p>
								
								<p>
									<?php submit_button(__('Export','px_gcm')); ?>
								</p>
							</form>
						</div> 
					</div>
				</div>
			</div> 
			<br class="clear">
		</div>
	</div> 	
<?php
}

function export() {
	global $wpdb;
	$px_table_name = $wpdb->prefix.'gcm_users';
	$query = "SELECT * FROM $px_table_name";
	$datas = $wpdb->get_results($query);
	
	
	$url = wp_nonce_url('admin.php?page=px-gcm-export','px-gcm-export');
	if (false === ($creds = request_filesystem_credentials($url, '', false, false, null)) ) {
		return true;
	}
	
	if (!WP_Filesystem($creds)) {
		// our credentials were not good, ask the user for them again
		request_filesystem_credentials($url, '', true, false, null);
		return true;
	}
	
	
	global $wp_filesystem;
	$contentdir = trailingslashit($wp_filesystem->wp_content_dir());
	
	$in = "Databse ID;GCM Registration ID;Device OS;Device Model;Created At;Messages sent to this Device;\n";
	foreach($datas as $data) {
		$in .=  $data->id.";".$data->gcm_regid.";".$data->os.";".$data->model.";".$data->created_at.";".$data->send_msg."\n";
	}
	mb_convert_encoding($in, "ISO-8859-1", "UTF-8");
	
	if(!$wp_filesystem->put_contents($contentdir.'GCM-Export.csv', $in, FS_CHMOD_FILE)) {
		echo 'Failed saving file';
	}
	return content_url()."/GCM-Export.csv"; 
}

?>