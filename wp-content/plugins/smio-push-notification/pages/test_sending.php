<div class="wrap" id="smpush-dashboard">
<div id="smpush-icon-push" class="icon32"><br></div>
<h2><?php echo get_admin_page_title();?></h2>
<form action="<?php echo $pageurl;?>" method="post">
  <div id="col-container">
  <div class="metabox-holder">
     <div class="postbox-container" style="width:60%;">
        <div class="meta-box-sortables">
           <div class="postbox">
              <div class="handlediv" title="Click to toggle"><br></div>
              <h3><label>Sending Options</label></h3>
              <div class="inside">
                 <table class="form-table">
                    <tbody>
                       <tr valign="top">
                          <td class="first">Message</td>
                          <td><textarea name="message" cols="40" rows="6" class="large-text">Test push notification functionality !</textarea></td>
                       </tr>
                       <tr valign="top">
                          <td class="first">iOS Device Token</td>
                          <td>
                             <input name="ios_token" type="text" class="regular-text" />
                          </td>
                       </tr>
                       <tr valign="top">
                          <td class="first">Android Device Token</td>
                          <td>
                             <input name="android_token" type="text" class="regular-text" />
                          </td>
                       </tr>
                    </tbody>
                 </table>
              </div>
           </div>
        </div>
     </div>
  </div>
  <div class="metabox-holder">
     <div class="postbox-container" style="width:60%;">
         <div class="postbox">
             <table class="form-table" style="margin-top: 0;">
                <tbody>
                   <tr valign="top">
                      <td>
                        <input type="submit" class="button button-primary" value="Send a test message">
                      </td>
                   </tr>
                </tbody>
             </table>
         </div>
     </div>
  </div>
  </div>
</form>
</div>
<script type="text/javascript">
jQuery(document).ready(function() {
 if(typeof postboxes !== 'undefined')
   postboxes.add_postbox_toggles( 'dashboard_page_stats' );
});
</script>