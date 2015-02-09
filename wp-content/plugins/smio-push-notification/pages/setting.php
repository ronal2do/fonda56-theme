<div class="wrap">
<div id="smpush-icon-devsetting" class="icon32"><br></div>
<h2>Push Notification Setting</h2>

<form action="<?php  echo $page_url;?>" id="smpush_jform" method="post">
  <table class="form-table">
    <tbody>
      <tr valign="top">
        <th scope="row"><label>Authentication Key</label></th>
        <td>
          <input name="auth_key" type="text" value="<?php echo self::$apisetting['auth_key'];?>" class="regular-text">
          <p class="description">Send this key with any request to prevent access to API from outside.</p>
          <p class="description">Leave it empty to disable this feature.</p>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">Complex Authentication</th>
        <td>
            <label><input name="complex_auth" type="checkbox" value="1" <?php if(self::$apisetting['complex_auth']==1){?>checked="checked"<?php }?>> Put the authentication key into an encrypted string</label>
            <p class="description">The encrypted string will be in the following format <a href="http://en.wikipedia.org/wiki/MD5" target="_blank">MD5</a>(Date in m/d/y - Your auth key - Time in H:m)</p>
            <p class="description">For example <a href="http://en.wikipedia.org/wiki/MD5" target="_blank">MD5</a>(<?php echo date('m/d/Y').self::$apisetting['auth_key'].date('H:i');?>)</p>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row"><label>API Base Name</label></th>
        <td>
          <input name="push_basename" type="text" value="<?php echo self::$apisetting['push_basename'];?>" class="regular-text">
          <p class="description"><span><code><?php echo site_url();?>/</code><abbr>API_BASE_NAME<code>/</code></abbr></span></p>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row"><label>Default Connection</label></th>
        <td>
          <select name="def_connection" class="postform">
          <?php foreach($params AS $connection){?>
              <option value="<?php echo $connection->id;?>" <?php if($connection->id == self::$apisetting['def_connection']){?>selected=""<?php }?>><?php echo $connection->title;?></option>
          <?php }?>
           </select>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row">Apple Sandbox</th>
        <td><label><input name="apple_sandbox" type="checkbox" value="1" <?php if(self::$apisetting['apple_sandbox']==1){?>checked="checked"<?php }?>> Enable apple sandbox server</label></td>
      </tr>
      <tr valign="top">
        <th scope="row"><label>Apple Password Phrase</label></th>
        <td>
          <input name="apple_passphrase" type="text" value="<?php echo self::$apisetting['apple_passphrase'];?>" class="regular-text">
          <p class="description">Apple password phrase for send push notification.</p>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row"><label>Upload Certification File</label></th>
        <td>
          <input name="apple_cert_upload" type="file" class="regular-text">
          <p class="description">Upload Apple certification file or enter the absolute path below .</p>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row"><label>Apple Certification Path</label></th>
        <td>
          <input name="apple_cert_path" type="text" value="<?php echo self::$apisetting['apple_cert_path'];?>" class="regular-text">
          <p class="description">Apple certification full path in your server like <?php echo str_replace('/wp-content/plugins/smio-push-notification/pages', '', realpath(dirname(__FILE__)));?>/cert_file.pem</p>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row"><label>Google API Key</label></th>
        <td>
          <input name="google_apikey" type="text" value="<?php echo self::$apisetting['google_apikey'];?>" class="regular-text">
          <p class="description">Google API key for send Android push notification.</p>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row" colspan="2"><h2>Titanium Compatability</h2></th>
      </tr>
      <tr valign="top">
        <td colspan="2">
          <label><input name="ios_titanium_payload" type="checkbox" value="1" <?php if(self::$apisetting['ios_titanium_payload']==1){?>checked="checked"<?php }?>> Make the iOS payload compatible with Titanium application</label>
        </td>
      </tr>
      <tr valign="top">
        <td colspan="2">
          <label><input name="android_titanium_payload" type="checkbox" value="1" <?php if(self::$apisetting['android_titanium_payload']==1){?>checked="checked"<?php }?>> Make the Android payload compatible with Titanium application</label>
        </td>
      </tr>
      <tr valign="top">
        <th scope="row" colspan="2"><h2>Push Notification Events</h2></th>
      </tr>
      <tr valign="top">
        <td colspan="2">
          <label><input name="e_newpost" type="checkbox" value="1" <?php if(self::$apisetting['e_newpost']==1){?>checked="checked"<?php }?>> Notify all members when administrator published a new post</label>
          <input name="e_newpost_body" type="text" value='<?php echo self::$apisetting['e_newpost_body'];?>' class="regular-text">
          <p class="description">Notice: To use this feature first please enable the cron-job service, Look <a href="http://smartiolabs.com/product/push-notification-system/documentation#cron-job" target="_blank">here</a> for further information</p>
        </td>
      </tr>
      <tr valign="top">
        <td colspan="2">
          <label><input name="e_post_chantocats" type="checkbox" value="1" <?php if(self::$apisetting['e_post_chantocats']==1){?>checked="checked"<?php }?>> Notify only members which subscribed in a channel name equivalent with the post category name</label>
        </td>
      </tr>
      <tr valign="top">
        <td colspan="2">
          <label><input name="e_apprpost" type="checkbox" value="1" <?php if(self::$apisetting['e_apprpost']==1){?>checked="checked"<?php }?>> Notify author when administrator approved and published his post</label>
          <input name="e_apprpost_body" type="text" value='<?php echo self::$apisetting['e_apprpost_body'];?>' class="regular-text">
        </td>
      </tr>
      <tr valign="top">
        <td colspan="2">
          <label><input name="e_appcomment" type="checkbox" value="1" <?php if(self::$apisetting['e_appcomment']==1){?>checked="checked"<?php }?>> Notify user when administrator approved on his comment</label>
          <input name="e_appcomment_body" type="text" value='<?php echo self::$apisetting['e_appcomment_body'];?>' class="regular-text">
        </td>
      </tr>
      <tr valign="top">
        <td colspan="2">
          <label><input name="e_newcomment" type="checkbox" value="1" <?php if(self::$apisetting['e_newcomment']==1){?>checked="checked"<?php }?>> Notify author when added new comment on his post</label>
          <input name="e_newcomment_body" type="text" value='<?php echo self::$apisetting['e_newcomment_body'];?>' class="regular-text">
        </td>
      </tr>
      <tr valign="top">
        <td colspan="2">
          <label><input name="e_usercomuser" type="checkbox" value="1" <?php if(self::$apisetting['e_usercomuser']==1){?>checked="checked"<?php }?>> Notify user when someone comment on his comment</label>
          <input name="e_usercomuser_body" type="text" value='<?php echo self::$apisetting['e_usercomuser_body'];?>' class="regular-text">
        </td>
      </tr>
      <tr valign="top">
        <td colspan="2">
          <label><input name="e_postupdated" type="checkbox" value="1" <?php if(self::$apisetting['e_postupdated']==1){?>checked="checked"<?php }?>> Notify all users subscribed in a post when has got a new update</label>
          <input name="e_postupdated_body" type="text" value='<?php echo self::$apisetting['e_postupdated_body'];?>' class="regular-text">
          <p class="description">Notice: System will replace {subject},{comment} words with the subject of topic or comment content.</p>
          <p class="description">Notice: System will send the topic ID with the push notification message as name `relatedvalue`.</p>
        </td>
      </tr>
    </tbody>
  </table>
  <p class="submit">
    <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
    <img src="<?php echo smpush_imgpath;?>/wpspin_light.gif" class="smpush_process" alt="" />
  </p>
</form>
</div>