<?php

function px_display_page_msg() {
  global $wpdb;
  $px_table_name = $wpdb->prefix.'gcm_users';
  $result = $wpdb->get_var( "SELECT COUNT(*) FROM $px_table_name" );
  
  if($result != false) {
  $num_rows = $result;
  }else {
  $num_rows = 0;
  }
  $info = sprintf(__('currently %s users are registered','px_gcm'),$num_rows);
  
?>

<div class="wrap">
	<h2 class="">Google Cloud Messaging</h2>
	<div id="poststuff">
		<div id="post-body" class="metabox-holder columns-2"> 
			<!-- main content -->
			<div id="post-body-content">
					<div class="postbox">
					 <h3><?php _e('New Message','px_gcm'); ?></h3>
					 <h3>Please go and buy this awesome plugin on CodeCanyon fur support and latest version. <a href="http://codecanyon.net/item/wp-gcm/9942568?ref=PixelartDev">click here</a></h3>
						<div class="inside">
							 <form method="post" action="#">
							   <p><?php _e('Enter here your message','px_gcm'); ?></p>
					           <textarea id="message" name="message" type="text" cols="20" rows="5" ></textarea>
							   <p><?php _e('*Please don\'t use HTML','px_gcm'); ?></p>
	                              <?php submit_button(__('Send','px_gcm')); ?>
	                         </form>
						</div> 
					</div>
					<p><b><?php _e('Info','px_gcm'); ?> &nbsp;&nbsp;</b> <?php echo $info ?></p>
					<p></p>
			</div>
                        <div id="postbox-container-1" class="postbox-container">
                           <div class="inside">
                              <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="2YCTDL7AFRHHG">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/de_DE/i/scr/pixel.gif" width="1" height="1">
</form>

                           </div>
                        </div>
		</div> 
		<br class="clear">
	</div>
</div> 
<?php
}

if(isset($_POST['message'])) {
    $message = $_POST["message"];
    px_sendGCM($message, "message");
}
?>