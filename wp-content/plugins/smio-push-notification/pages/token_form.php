<form action="<?php echo $pageurl;?>&noheader=1" method="post" id="smpush_jform" class="validate">
<input type="hidden" name="id" value="<?php echo $token['id'];?>">
   <div id="post-body" class="metabox-holder columns-2">
      <div id="post-body-content" class="edit-form-section">
         <div id="namediv" class="stuffbox">
            <h3><label><?php echo ($token['id']==0)?'Add New Device':'Edit Device Info';?></label></h3>
            <div class="inside">
               <table class="form-table">
                <tbody>
                  <tr valign="top" class="form-required">
                     <td class="first">Device Token</td>
                     <td>
                     <textarea name="device_token" rows="5" cols="40" aria-required="true"><?php echo $token['device_token'];?></textarea>
                     </td>
                  </tr>
                  <tr valign="top">
                     <td class="first">Information</td>
                     <td>
                     <textarea name="information" rows="5" cols="40"><?php echo $token['information'];?></textarea>
                     </td>
                  </tr>
                  <tr valign="top">
                     <td class="first">Type</td>
                     <td>
                     <select name="device_type">
                        <option value="<?php echo $types_name->ios_name;?>">iOS</option>
                        <option value="<?php echo $types_name->android_name;?>" <?php if($token['device_type'] == $types_name->android_name){?>selected="selected"<?php }?>>Android</option>
                     </select>
                     </td>
                  </tr>
                  <tr valign="top">
                     <td class="first">Channels</td>
                     <td>
                     <select name="channels[]" multiple>
                     <?php foreach($channels as $channel){?>
                        <option value="<?php echo $channel->id;?>" <?php if(in_array($channel->id, $token['channels'])){?>selected="selected"<?php }?>><?php echo $channel->title;?></option>
                     <?php }?>
                     </select>
                     </td>
                  </tr>
                  <tr valign="top">
                     <td class="first">Active</td>
                     <td>
                     <select name="active">
                        <option value="1">Yes</option>
                        <option value="0" <?php if($token['active'] == 0){?>selected="selected"<?php }?>>No</option>
                     </select>
                     </td>
                  </tr>
                  <tr valign="top">
                    <td colspan="2"><input type="submit" name="submit" id="smio-submit" class="button button-primary" style="width: 120px;" value="Save Changes">
                    <img src="<?php echo smpush_imgpath;?>/wpspin_light.gif" class="smpush_process" alt="" /></td>
                 </tr>
                </tbody>
              </table>
            </div>
         </div>
      </div>
   </div>
</form>