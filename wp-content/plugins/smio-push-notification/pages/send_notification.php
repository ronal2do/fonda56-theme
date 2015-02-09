<div class="wrap" id="smpush-dashboard">
<div id="smpush-icon-push" class="icon32"><br></div>
<h2><?php echo get_admin_page_title();?><a href="<?php echo admin_url();?>admin.php?page=smpush_active_tokens&noheader=1" data-confirm="Are you sure you want to activate all invalid device tokens?" class="smio-delete add-new-h2">Active All Tokens</a></h2>
<form action="<?php echo $page_url;?>" method="post" id="smpush_histform">
  <div id="col-container">
  <div id="col-left" style="width: 60%">
  <div class="metabox-holder">
     <div class="postbox-container" style="width:100%;">
        <div class="meta-box-sortables">
           <div class="postbox">
              <div class="handlediv" title="Click to toggle"><br></div>
              <h3><label>Message</label></h3>
              <div class="inside">
                 <table class="form-table">
                    <tbody>
                       <tr valign="top">
                          <td class="first">Message</td>
                          <td><textarea name="message" cols="50" rows="10" id="smpush-message" class="large-text"><?php echo self::loadHistory('message')?></textarea></td>
                       </tr>
                       <tr valign="top">
                          <td class="first">Expire Time</td>
                          <td>
                             <input name="expire" value="<?php echo self::loadHistory('expire')?>" type="number" size="10" step="1" /> Hour
                             <p class="description">Time in hours from now to keep the message alive</p>
                             <p class="description">Leave it empty to set to the longest default time</p>
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
     <div class="postbox-container" style="width:100%;">
        <div class="meta-box-sortables">
           <div class="postbox">
              <div class="handlediv" title="Click to toggle"><br></div>
              <h3><label>Message Payload</label></h3>
              <div class="inside">
                 <table class="form-table">
                    <tbody>
                       <tr valign="top">
                          <td class="first">Type</td>
                          <td>
                             <select name="extra_type" class="smpush-payload">
                                <option value="multi">Multi Values</option>
                                <option value="normal" <?php if(self::loadHistory('extra_type') == 'normal'){echo 'selected="selected"';}?>>Normal Text</option>
                                <option value="json" <?php if(self::loadHistory('extra_type') == 'json'){echo 'selected="selected"';}?>>JSON</option>
                             </select>
                          </td>
                       </tr>
                       <tr valign="top" class="smpush-payload-multi" <?php if(self::loadHistory('extra_type') != 'multi' && self::loadHistory('extra_type') != ''){echo 'style="display:none;"';}?>>
                          <td class="first">Payload</td>
                          <td>
                             <input name="key[]" value="<?php echo self::loadHistory('key', 0)?>" type="text" placeholder="key" size="10" /> <input placeholder="value" value="<?php echo self::loadHistory('value', 0)?>" name="value[]" type="text" size="20" /><br />
                             <input name="key[]" value="<?php echo self::loadHistory('key', 1)?>" type="text" placeholder="key" size="10" /> <input placeholder="value" value="<?php echo self::loadHistory('value', 1)?>" name="value[]" type="text" size="20" /><br />
                             <input name="key[]" value="<?php echo self::loadHistory('key', 2)?>" type="text" placeholder="key" size="10" /> <input placeholder="value" value="<?php echo self::loadHistory('value', 2)?>" name="value[]" type="text" size="20" /><br />
                             <input name="key[]" value="<?php echo self::loadHistory('key', 3)?>" type="text" placeholder="key" size="10" /> <input placeholder="value" value="<?php echo self::loadHistory('value', 3)?>" name="value[]" type="text" size="20" /><br />
                             <input name="key[]" value="<?php echo self::loadHistory('key', 4)?>" type="text" placeholder="key" size="10" /> <input placeholder="value" value="<?php echo self::loadHistory('value', 4)?>" name="value[]" type="text" size="20" /><br />
                             <input name="key[]" value="<?php echo self::loadHistory('key', 5)?>" type="text" placeholder="key" size="10" /> <input placeholder="value" value="<?php echo self::loadHistory('value', 5)?>" name="value[]" type="text" size="20" />
                             <p class="description">Keys with empty values will ignore.</p>
                          </td>
                       </tr>
                       <tr valign="top" class="smpush-payload-normal" <?php if(self::loadHistory('extra_type') == 'multi' || self::loadHistory('extra_type') == ''){echo 'style="display:none;"';}?>>
                          <td class="first">Payload</td>
                          <td>
                             <input name="extra" value="<?php echo self::loadHistory('extra')?>" type="text" class="regular-text" />
                             <p class="description">Send with push message as name `relatedvalue`</p>
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
     <div class="postbox-container" style="width:100%;">
        <div class="meta-box-sortables">
           <div class="postbox">
              <div class="handlediv" title="Click to toggle"><br></div>
              <h3><label>Send Setting</label></h3>
              <div class="inside">
                 <table class="form-table">
                    <tbody>
                       <tr valign="top">
                          <td class="first">Send Time</td>
                          <td>
                             <div id="timestampdiv"><?php self::touch_time(); ?></div>
                             <p class="description">You must first add a cron job in your Cpanel for the scheduled sending, A tutorial <a href="http://smartiolabs.com/product/push-notification-system/documentation#cron-job" target="_blank">here</a></p>
                          </td>
                       </tr>
                       <tr valign="top">
                          <td class="first">Device type</td>
                          <td>
                             <select name="type">
                                <option value="all">All devices (<?php echo $params['all'];?>)</option>
                                <option value="ios" <?php if(self::loadHistory('type') == 'ios'){echo 'selected="selected"';}?>>IOS only (<?php echo $params['ios'];?>)</option>
                                <option value="android" <?php if(self::loadHistory('type') == 'android'){echo 'selected="selected"';}?>>Android only (<?php echo $params['all']-$params['ios'];?>)</option>
                             </select>
                          </td>
                       </tr>
                       <?php if($params['dbtype'] == 'localhost'){?>
                       <tr valign="top">
                          <td class="first">Channels</td>
                          <td>
                             <?php $chanhistory=self::loadHistory('channel');foreach($params['channels'] AS $channel){?>
                             <label><input name="channel[]" type="checkbox" value="<?php echo $channel->id;?>" <?php if(!empty($chanhistory)){if(in_array($channel->id, $chanhistory)){echo 'checked="checked"';}}?>> <?php echo $channel->title;?> (<?php echo $channel->count;?>)</label><br />
                             <?php }?>
                          </td>
                       </tr>
                       <?php }?>
                       <tr valign="top">
                          <td class="first">Feedback Service</td>
                          <td><label><input name="feedback" type="checkbox" <?php if(self::loadHistory('feedback') != ''){echo 'checked="checked"';}?> /> Enable feedback will find and deactivate the invalid devices tokens</label></td>
                       </tr>
                       <tr valign="top">
                          <td class="first">iOS Test Mode</td>
                          <td>
                             <label><input name="iostestmode" type="checkbox" <?php if(self::loadHistory('iostestmode') != ''){echo 'checked="checked"';}?> /> Enable iOS test mode if you try to test push notification in your app</label>
                             <p class="description">What happen that system will feedback every token to prevent Apple cancel connection</p>
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
     <div class="postbox-container" style="width:100%;">
         <div class="postbox">
             <table class="form-table" style="margin-top: 0;">
                <tbody>
                   <tr valign="top">
                      <td>
                        <input type="submit" name="sendnow" class="button button-primary" value="Start Sending Now">
                        <input type="submit" name="cronsend" class="button button-primary" value="Automatic Scheduled Sending">
                      </td>
                   </tr>
                </tbody>
             </table>
         </div>
     </div>
  </div>
  </div>
  <div id="col-right" style="width: 39%">
  <div class="metabox-holder">
     <div class="postbox-container" style="width:100%;">
        <div class="meta-box-sortables">
           <div class="postbox">
              <div class="handlediv" title="Click to toggle"><br></div>
              <h3><label>iOS Adjustments</label></h3>
              <div class="inside">
                 <table class="form-table">
                    <tbody>
                       <tr valign="middle">
                          <td class="first">Lock Key</td>
                          <td>
                             <input name="ios_slide" type="text" value="<?php echo self::loadHistory('ios_slide')?>" />
                             <p class="description">Change (view) sentence in (Slide to view)</p>
                          </td>
                       </tr>
                       <tr valign="middle">
                          <td class="first">Badge</td>
                          <td>
                             <input name="ios_badge" type="text" value="<?php echo self::loadHistory('ios_badge')?>" />
                             <p class="description">The number to display as the badge of the application icon.</p>
                          </td>
                       </tr>
                       <tr valign="middle">
                          <td class="first">Sound</td>
                          <td>
                             <input name="ios_sound" type="text" value="<?php echo (self::loadHistory('ios_sound') == '')?'default':self::loadHistory('ios_sound');?>" />
                             <p class="description">The name of a sound file in the application bundle.</p>
                          </td>
                       </tr>
                       <tr valign="middle">
                          <td class="first">Content Available</td>
                          <td>
                             <input name="ios_cavailable" type="text" value="<?php echo self::loadHistory('ios_cavailable')?>" />
                             <p class="description">Provide this key with a value of 1 to indicate that new content is available.</p>
                          </td>
                       </tr>
                       <tr valign="middle">
                          <td class="first">Launch Image</td>
                          <td>
                             <input name="ios_launchimg" type="text" value="<?php echo self::loadHistory('ios_launchimg')?>" />
                             <p class="description">The filename of an image file in the application bundle.</p>
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
     <div class="postbox-container" style="width:100%;">
        <div class="meta-box-sortables">
           <div class="postbox">
              <div class="handlediv" title="Click to toggle"><br></div>
              <h3><label>Customize Android Payload</label></h3>
              <div class="inside">
                 <table class="form-table">
                    <tbody>
                       <tr valign="top">
                          <td class="first">Type</td>
                          <td>
                             <select name="and_extra_type" class="and_smpush-payload">
                                <option value="multi">Multi Values</option>
                                <option value="normal" <?php if(self::loadHistory('and_extra_type') == 'normal'){echo 'selected="selected"';}?>>Normal Text</option>
                                <option value="json" <?php if(self::loadHistory('and_extra_type') == 'json'){echo 'selected="selected"';}?>>JSON</option>
                             </select>
                          </td>
                       </tr>
                       <tr valign="top" class="and_smpush-payload-multi" <?php if(self::loadHistory('and_extra_type') != 'multi' && self::loadHistory('and_extra_type') != ''){echo 'style="display:none;"';}?>>
                          <td class="first">Payload</td>
                          <td>
                             <input name="and_key[]" value="<?php echo self::loadHistory('and_key', 0)?>" type="text" placeholder="key" size="10" /> <input placeholder="value" value="<?php echo self::loadHistory('and_value', 0)?>" name="and_value[]" type="text" size="20" /><br />
                             <input name="and_key[]" value="<?php echo self::loadHistory('and_key', 1)?>" type="text" placeholder="key" size="10" /> <input placeholder="value" value="<?php echo self::loadHistory('and_value', 1)?>" name="and_value[]" type="text" size="20" /><br />
                             <input name="and_key[]" value="<?php echo self::loadHistory('and_key', 2)?>" type="text" placeholder="key" size="10" /> <input placeholder="value" value="<?php echo self::loadHistory('and_value', 2)?>" name="and_value[]" type="text" size="20" /><br />
                             <input name="and_key[]" value="<?php echo self::loadHistory('and_key', 3)?>" type="text" placeholder="key" size="10" /> <input placeholder="value" value="<?php echo self::loadHistory('and_value', 3)?>" name="and_value[]" type="text" size="20" /><br />
                             <input name="and_key[]" value="<?php echo self::loadHistory('and_key', 4)?>" type="text" placeholder="key" size="10" /> <input placeholder="value" value="<?php echo self::loadHistory('and_value', 4)?>" name="and_value[]" type="text" size="20" /><br />
                             <input name="and_key[]" value="<?php echo self::loadHistory('and_key', 5)?>" type="text" placeholder="key" size="10" /> <input placeholder="value" value="<?php echo self::loadHistory('and_value', 5)?>" name="and_value[]" type="text" size="20" />
                             <p class="description">Keys with empty values will ignore.</p>
                          </td>
                       </tr>
                       <tr valign="top" class="and_smpush-payload-normal" <?php if(self::loadHistory('and_extra_type') == 'multi' || self::loadHistory('and_extra_type') == ''){echo 'style="display:none;"';}?>>
                          <td class="first">Payload</td>
                          <td>
                             <input name="and_extra" value="<?php echo self::loadHistory('and_extra')?>" type="text" class="regular-text" />
                             <p class="description">Send with push message as name `relatedvalue`</p>
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
     <div class="postbox-container" style="width:100%;">
         <div class="postbox">
             <table class="form-table" style="margin-top: 0;">
                <tbody>
                   <tr valign="top">
                      <td>
                        <input type="button" id="smpush-save-hisbtn" class="button" value="Save Current Setting">
                        <input type="button" id="smpush-clear-hisbtn" class="button" value="Clear History">
                        <img src="<?php echo smpush_imgpath;?>/wpspin_light.gif" class="smpush_process" alt="" />
                      </td>
                   </tr>
                </tbody>
             </table>
         </div>
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